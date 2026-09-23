<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

namespace local_upgradeassistant\local;

/**
 * Tests for evaluation-round fixes.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\report_builder
 * @covers     \local_upgradeassistant\local\checklist_manager
 */
final class evaluation_fixes_test extends \advanced_testcase {
    /**
     * Reset deletion removes only the current user's reports and dependencies.
     *
     * @return void
     */
    public function test_delete_reports_for_user_preserves_other_administrators(): void {
        global $DB;

        $this->resetAfterTest();
        $userone = $this->getDataGenerator()->create_user();
        $usertwo = $this->getDataGenerator()->create_user();
        $reportone = $this->create_report((int)$userone->id, 'user-one-report');
        $reporttwo = $this->create_report((int)$usertwo->id, 'user-two-report');

        $DB->insert_record('local_upgradeassistant_item', (object)[
            'reportid' => $reportone,
            'category' => 'checklist',
            'code' => 'test_item',
            'title' => 'Test item',
            'description' => '',
            'severity' => 'info',
            'status' => 'open',
            'recommendation' => '',
            'evidence' => '{}',
            'sortorder' => 10,
            'timecreated' => time(),
        ]);

        $this->assertSame(1, report_builder::delete_reports_for_user((int)$userone->id));
        $this->assertFalse($DB->record_exists('local_upgradeassistant_rep', ['id' => $reportone]));
        $this->assertFalse($DB->record_exists('local_upgradeassistant_item', ['reportid' => $reportone]));
        $this->assertTrue($DB->record_exists('local_upgradeassistant_rep', ['id' => $reporttwo]));
    }

    /**
     * A pending maintenance row in an existing report is reconciled automatically.
     *
     * @return void
     */
    public function test_existing_report_detects_active_maintenance_mode(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        set_config('maintenance_enabled', 1);
        $reportid = $this->create_report((int)$USER->id, 'maintenance-sync-report');
        $checklistid = $DB->insert_record('local_upgradeassistant_check', (object)[
            'reportid' => $reportid,
            'stepkey' => 'maintenance',
            'title' => 'Maintenance mode',
            'description' => 'Maintenance mode test',
            'required' => 1,
            'status' => 'pending',
            'completedby' => 0,
            'completedat' => 0,
            'note' => '',
            'sortorder' => 10,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        checklist_manager::synchronise_environment_steps($reportid);
        $row = $DB->get_record('local_upgradeassistant_check', ['id' => $checklistid], '*', MUST_EXIST);
        $this->assertSame('completed', $row->status);
        $this->assertGreaterThan(0, (int)$row->completedat);
    }


    /**
     * A written plugin review closes the finding for scoring and preserves evidence.
     *
     * @return void
     */
    public function test_plugin_finding_can_be_reviewed_with_written_rationale(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $reportid = $this->create_report((int)$USER->id, 'manual-plugin-review');
        $findingid = (int)$DB->insert_record('local_upgradeassistant_item', (object)[
            'reportid' => $reportid,
            'category' => 'plugins',
            'code' => 'plugin_review_local_example',
            'title' => 'Review plugin local_example',
            'description' => 'Manual review required',
            'severity' => 'medium',
            'status' => 'open',
            'recommendation' => 'Verify it',
            'evidence' => json_encode([
                'component' => 'local_example',
                'compatibility' => 'missing',
                'reviewable' => true,
            ]),
            'sortorder' => 10,
            'timecreated' => time(),
        ]);
        $report = $DB->get_record('local_upgradeassistant_rep', ['id' => $reportid], '*', MUST_EXIST);
        $report->riskscore = 12;
        $report->risklevel = 'low';
        $DB->update_record('local_upgradeassistant_rep', $report);

        report_builder::review_plugin_finding(
            $reportid,
            $findingid,
            'Verified that the component is not used and is intentionally excluded from the target.'
        );

        $finding = $DB->get_record('local_upgradeassistant_item', ['id' => $findingid], '*', MUST_EXIST);
        $updatedreport = $DB->get_record('local_upgradeassistant_rep', ['id' => $reportid], '*', MUST_EXIST);

        $this->assertSame('reviewed', $finding->status);
        $this->assertSame(0, (int)$updatedreport->riskscore);
        $audit = $DB->get_record('local_upgradeassistant_audit', [
            'reportid' => $reportid,
            'action' => 'finding_reviewed',
            'targetid' => $findingid,
        ], '*', MUST_EXIST);
        $this->assertSame(
            'Verified that the component is not used and is intentionally excluded from the target.',
            $audit->note
        );
    }

    /**
     * Create the minimum valid report record used by these tests.
     *
     * @param int $userid Owner user ID.
     * @param string $uuid Report UUID.
     * @return int Report ID.
     */
    private function create_report(int $userid, string $uuid): int {
        global $DB;

        return (int)$DB->insert_record('local_upgradeassistant_rep', (object)[
            'uuid' => $uuid,
            'userid' => $userid,
            'currentrelease' => '4.5',
            'currentbranch' => '405',
            'targetrelease' => '5.2',
            'targetbranch' => '502',
            'targetpath' => '/tmp/moodle-5.2',
            'phpversion' => PHP_VERSION,
            'dbtype' => 'mariadb',
            'dbversion' => '11.4',
            'serverprofile' => 'Test',
            'riskscore' => 0,
            'risklevel' => 'low',
            'status' => 'generated',
            'summary' => '{}',
            'timecreated' => time(),
            'timemodified' => time(),
        ]);
    }
}
