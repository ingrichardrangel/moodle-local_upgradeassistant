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
 * Tests for checklist note redaction.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\checklist_manager
 */
final class checklist_redaction_test extends \advanced_testcase {
    /**
     * Checklist notes hide Windows and Linux paths when rows are redacted.
     *
     * @return void
     */
    public function test_template_rows_redact_windows_and_linux_paths(): void {
        global $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        $reportid = $DB->insert_record('local_ua_reports', (object)[
            'uuid' => 'checklist-redaction-test',
            'userid' => 0,
            'currentrelease' => '4.5',
            'currentbranch' => '405',
            'targetrelease' => '5.2',
            'targetbranch' => '502',
            'targetpath' => '/redaction/not/directly/used',
            'phpversion' => PHP_VERSION,
            'dbtype' => 'mariadb',
            'dbversion' => '11.4',
            'serverprofile' => 'Linux',
            'riskscore' => 24,
            'risklevel' => 'low',
            'status' => 'generated',
            'summary' => '{}',
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $DB->insert_record('local_ua_checklist', (object)[
            'reportid' => $reportid,
            'stepkey' => 'redaction_paths',
            'title' => 'Redaction paths',
            'description' => 'Redaction paths test',
            'required' => 1,
            'status' => 'completed',
            'completedby' => 0,
            'completedat' => time(),
            'note' => 'Reviewed C:\\xampp\\htdocs\\moodle\\config.php and /var/www/moodle/config.php.',
            'sortorder' => 10,
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        $rows = checklist_manager::get_template_rows($reportid, true);
        $this->assertCount(1, $rows);
        $note = (string)$rows[0]['note'];

        $this->assertStringNotContainsString('C:\\xampp', $note);
        $this->assertStringNotContainsString('/var/www/moodle', $note);
        $this->assertStringContainsString(get_string('redacted', 'local_upgradeassistant'), $note);
    }
}
