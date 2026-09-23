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
 * Tests for the version rule engine.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\rule_engine
 */
final class rule_engine_test extends \advanced_testcase {
    /**
     * Test that built-in rules include expected 5.2 requirements.
     *
     * @return void
     */
    public function test_builtin_rule_for_moodle_52(): void {
        $rules = rule_engine::builtin_rules();

        $this->assertArrayHasKey(502, $rules);
        $this->assertSame(404, $rules[502]['minimumfrombranch']);
        $this->assertSame('8.3.0', $rules[502]['minimumphp']);
    }

    /**
     * Test upgrade path uses rule engine minimum-from data.
     *
     * @return void
     */
    public function test_upgrade_path_uses_rule_engine_minimum_from(): void {
        $result = upgrade_path::analyze(401, 502, null, '4.1.9');

        $this->assertSame('warning', $result['status']);
        $this->assertSame([404, 502], $result['route']);
        $this->assertSame(404, $result['next']);
    }

    /**
     * All published intermediate branches have official server requirements.
     *
     * @return void
     */
    public function test_rules_cover_every_branch_from_41_to_53(): void {
        $rules = rule_engine::builtin_rules();
        $this->assertSame([401, 402, 403, 404, 405, 500, 501, 502, 503], array_keys($rules));
        $this->assertSame('4.1.2', $rules[404]['minimumfrom']);
        $this->assertSame('4.2.3', $rules[501]['minimumfrom']);
        $this->assertSame('15.0', $rules[501]['dbminimums']['pgsql']);
        $this->assertSame('2017', $rules[500]['dbminimums']['sqlsrv']);
    }

    /**
     * Previous installs must read corrected built-in rules without losing custom overrides.
     *
     * @return void
     */
    public function test_existing_builtin_rule_is_refreshed_but_custom_rule_is_preserved(): void {
        global $DB;

        $this->resetAfterTest();
        $oldrule = rule_engine::builtin_rules()[401];
        $oldrule['dbminimums']['mariadb'] = '10.2.29';
        $record = $DB->get_record('local_ua_rules', ['targetbranch' => '401',
            'ruletype' => 'requirements', 'rulekey' => 'moodle_401']);
        $this->assertNotFalse($record);
        $record->source = 'builtin';
        $record->rulesjson = json_encode($oldrule);
        $DB->update_record('local_ua_rules', $record);

        $this->assertSame('10.4.0', rule_engine::get_rule(401)['dbminimums']['mariadb']);

        $record->source = 'custom';
        $DB->update_record('local_ua_rules', $record);
        $this->assertSame('10.2.29', rule_engine::get_rule(401)['dbminimums']['mariadb']);
    }

    /**
     * A future branch can use a selected package's XML without inventing an upgrade route.
     *
     * @return void
     */
    public function test_future_branch_reads_target_environment_without_guessing_route(): void {
        $directory = make_temp_directory('upgradeassistant_future_rule');
        if (!is_dir($directory . '/admin')) {
            mkdir($directory . '/admin');
        }
        file_put_contents($directory . '/version.php', "<?php \$branch = '504';");
        file_put_contents($directory . '/admin/environment.xml', '<?xml version="1.0"?>'
            . '<COMPATIBILITY_MATRIX><MOODLE version="5.4">'
            . '<PHP version="8.4.0" level="required" />'
            . '<DATABASE level="required"><VENDOR name="mariadb" version="11.4.0" />'
            . '<VENDOR name="postgres" version="17.0" /></DATABASE>'
            . '</MOODLE></COMPATIBILITY_MATRIX>');
        $target = ['branch' => '504', 'versionfile' => $directory . '/version.php'];

        $rule = rule_engine::get_rule(504, $target);
        $this->assertSame('8.4.0', $rule['minimumphp']);
        $this->assertSame('11.4.0', $rule['dbminimums']['mariadb']);
        $this->assertSame(0, $rule['minimumfrombranch']);

        $route = upgrade_path::analyze(503, 504, $target);
        $this->assertSame('warning', $route['status']);
        $this->assertFalse($route['directallowed']);
        $this->assertSame([], $route['route']);
    }
}
