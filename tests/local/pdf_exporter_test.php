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
 * Tests for report export redaction.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\pdf_exporter
 */
final class pdf_exporter_test extends \advanced_testcase {
    /**
     * Test free-text export redaction removes paths in notes.
     *
     * @return void
     */
    public function test_redact_sensitive_text_removes_report_paths_from_notes(): void {
        $this->resetAfterTest();

        $report = (object)[
            'targetpath' => '/var/www/moodle52',
            'dbtype' => 'mariadb',
            'dbversion' => '11.4.12',
            'summary' => json_encode([
                'target' => [
                    'path' => '/var/www/moodle52',
                    'publicpath' => '/var/www/moodle52/public',
                    'configpath' => '/var/www/moodle52/config.php',
                ],
            ]),
        ];
        $note = 'Reviewed /var/www/moodle52/config.php and C:\\xampp\\htdocs\\moodle before export.';

        $method = new \ReflectionMethod(pdf_exporter::class, 'redact_sensitive_text');
        $method->setAccessible(true);
        $redacted = $method->invoke(null, $note, $report);

        $this->assertStringNotContainsString('/var/www/moodle52', $redacted);
        $this->assertStringNotContainsString('C:\\xampp\\htdocs\\moodle', $redacted);
        $this->assertStringContainsString(get_string('redacted', 'local_upgradeassistant'), $redacted);
    }

    /**
     * Test lifecycle timeline renderer avoids Unicode block characters.
     *
     * @return void
     */
    public function test_lifecycle_timeline_uses_pdf_safe_html_bars(): void {
        $method = new \ReflectionMethod(pdf_exporter::class, 'timeline_cell');
        $method->setAccessible(true);
        $html = $method->invoke(null, 'security');

        $this->assertStringContainsString('bgcolor=', $html);
        $this->assertStringNotContainsString('█', $html);
        $this->assertStringNotContainsString('░', $html);
        $this->assertStringNotContainsString('?', $html);
    }

    /**
     * Both HTML exports render and register an audit record; the redacted one hides paths.
     *
     * @return void
     */
    public function test_complete_and_redacted_html_exports(): void {
        global $DB, $USER;

        $this->resetAfterTest();
        $this->setAdminUser();
        $reportid = (int)$DB->insert_record('local_ua_reports', (object)[
            'uuid' => 'f23992a1-474e-4a33-b310-b3a195f392a1',
            'userid' => $USER->id,
            'currentrelease' => '4.5.0',
            'currentbranch' => '405',
            'targetrelease' => '5.2.0',
            'targetbranch' => '502',
            'targetpath' => '/var/www/private-moodle-52',
            'phpversion' => PHP_VERSION,
            'dbtype' => 'mariadb',
            'dbversion' => '11.4',
            'serverprofile' => 'Linux',
            'riskscore' => 0,
            'risklevel' => 'low',
            'status' => 'generated',
            'summary' => json_encode(['target' => ['path' => '/var/www/private-moodle-52']]),
            'timecreated' => time(),
            'timemodified' => time(),
        ]);

        foreach ([false => 'html', true => 'html_redacted'] as $redacted => $exporttype) {
            ob_start();
            try {
                pdf_exporter::download_html($reportid, (bool)$redacted);
                $html = ob_get_contents();
            } finally {
                ob_end_clean();
            }
            $this->assertStringContainsString('<!doctype html>', $html);
            $this->assertStringContainsString('</html>', $html);
            if ($redacted) {
                $this->assertStringNotContainsString('/var/www/private-moodle-52', $html);
            } else {
                $this->assertStringContainsString('/var/www/private-moodle-52', $html);
            }
            $this->assertTrue($DB->record_exists('local_ua_exports', [
                'reportid' => $reportid,
                'exporttype' => $exporttype,
            ]));
            $this->assertTrue($DB->record_exists('local_ua_audit', [
                'reportid' => $reportid,
                'action' => $redacted ? 'report_html_redacted_downloaded' : 'report_html_downloaded',
            ]));
        }
    }

}
