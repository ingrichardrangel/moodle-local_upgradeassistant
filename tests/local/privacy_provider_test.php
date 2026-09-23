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

namespace local_upgradeassistant\privacy;

use context_system;
use core_privacy\local\request\approved_contextlist;
use local_upgradeassistant\privacy\provider;

/**
 * Tests for the Smart Upgrade Assistant privacy provider.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\privacy\provider
 */
final class privacy_provider_test extends \advanced_testcase {
    /**
     * The privacy delete flow anonymises checklist ownership and clears notes.
     *
     * @return void
     */
    public function test_delete_data_for_user_clears_checklist_note(): void {
        global $DB;

        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $context = context_system::instance();

        $reportid = $DB->insert_record('local_ua_reports', (object)[
            'uuid' => 'privacy-test-report',
            'userid' => $user->id,
            'currentrelease' => '4.5',
            'currentbranch' => 405,
            'targetrelease' => '5.2',
            'targetbranch' => 502,
            'targetpath' => '/var/www/moodle52',
            'phpversion' => PHP_VERSION,
            'dbtype' => 'mariadb',
            'dbversion' => '11.4',
            'serverprofile' => 'Linux',
            'riskscore' => 24,
            'risklevel' => 'low',
            'summary' => '{}',
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $checklistid = $DB->insert_record('local_ua_checklist', (object)[
            'reportid' => $reportid,
            'stepkey' => 'privacy_note',
            'title' => 'Privacy note',
            'description' => 'Privacy note test',
            'status' => 'completed',
            'required' => 1,
            'completedby' => $user->id,
            'completedat' => time(),
            'note' => 'Reviewed /var/www/moodle52/config.php by the user.',
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $approved = new approved_contextlist(
            $user,
            'local_upgradeassistant',
            [$context->id]
        );
        provider::delete_data_for_user($approved);

        $report = $DB->get_record('local_ua_reports', ['id' => $reportid], '*', MUST_EXIST);
        $checklist = $DB->get_record('local_ua_checklist', ['id' => $checklistid], '*', MUST_EXIST);

        $this->assertSame(0, (int)$report->userid);
        $this->assertSame(0, (int)$checklist->completedby);
        $this->assertSame(0, (int)$checklist->completedat);
        $this->assertSame('', (string)$checklist->note);
    }
}
