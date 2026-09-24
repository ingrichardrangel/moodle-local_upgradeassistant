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
 * Tests for production cutover instructions involving the /public structure.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\wizard_service
 * @covers     \local_upgradeassistant\local\server_recommendation_engine
 */
final class production_cutover_test extends \advanced_testcase {
    /**
     * Public targets use a production folder cutover and keep standard config.php loading.
     *
     * @return void
     */
    public function test_public_target_generates_production_cutover_paths(): void {
        global $CFG;

        $this->resetAfterTest();

        $targetroot = \make_temp_directory('local_upgradeassistant_cutover_target');
        mkdir($targetroot . DIRECTORY_SEPARATOR . 'public');

        $selectedtarget = [
            'path' => $targetroot,
            'approot' => $targetroot,
            'publicpath' => $targetroot . DIRECTORY_SEPARATOR . 'public',
            'haspublic' => true,
        ];
        $analysis = [
            'status' => 'success',
            'directallowed' => true,
        ];

        $instructions = wizard_service::instructions($selectedtarget, $analysis);
        $values = [];
        foreach ($instructions['steps'] as $step) {
            foreach ($step['codes'] as $code) {
                $values[] = $code['value'];
            }
        }
        $output = implode("\n", $values);

        $currentapproot = realpath($CFG->dirroot) ?: $CFG->dirroot;
        if (strtolower(basename($currentapproot)) === 'public') {
            $currentapproot = dirname($currentapproot);
        }
        $expectedpublic = $currentapproot . DIRECTORY_SEPARATOR . 'public';

        $this->assertTrue($instructions['available']);
        $this->assertContains($targetroot, $values);
        $this->assertContains($targetroot . DIRECTORY_SEPARATOR . 'public', $values);
        $this->assertContains($currentapproot, $values);
        $this->assertContains($expectedpublic, $values);
        $this->assertStringContainsString(
            'php ' . $expectedpublic . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'cli'
                . DIRECTORY_SEPARATOR . 'upgrade.php --non-interactive',
            $output
        );
        $this->assertStringContainsString("require_once(__DIR__ . '/lib/setup.php');", $output);
        $this->assertStringNotContainsString('$CFG->dirroot =', $output);
        $this->assertStringNotContainsString('/public/lib/setup.php', $output);
    }

    /**
     * Apache on XAMPP and a hosted domain require different configuration steps.
     *
     * @return void
     */
    public function test_public_document_root_recommendation_uses_server_profile(): void {
        $xampp = server_recommendation_engine::target_public_recommendation('xampp');
        $cpanel = server_recommendation_engine::target_public_recommendation('cpanel');
        $vps = server_recommendation_engine::target_public_recommendation('vpslinux');

        $this->assertStringContainsString('httpd.conf', $xampp);
        $this->assertStringContainsString('<Directory>', $xampp);
        $this->assertStringContainsString('cPanel', $cpanel);
        $this->assertStringContainsString('Document Root', $cpanel);
        $this->assertStringContainsString('Nginx', $vps);
        $this->assertNotSame($xampp, $cpanel);
    }
}
