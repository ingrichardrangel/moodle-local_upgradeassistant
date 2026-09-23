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
 * Tests for upgrade path analysis.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\upgrade_path
 */
final class upgrade_path_test extends \advanced_testcase {
    /**
     * Test branch conversion.
     *
     * @return void
     */
    public function test_branch_to_int(): void {
        $this->assertSame(405, upgrade_path::branch_to_int('405'));
        $this->assertSame(401, upgrade_path::branch_to_int('4.1'));
        $this->assertSame(500, upgrade_path::branch_to_int('5.0'));
        $this->assertSame(502, upgrade_path::branch_to_int('5.2'));
        $this->assertSame(502, upgrade_path::branch_to_int('Moodle 5.2'));
        $this->assertSame(503, upgrade_path::branch_to_int('Moodle 5.3 beta'));
        $this->assertSame(0, upgrade_path::branch_to_int(null));
    }

    /**
     * Test downgrade detection.
     *
     * @return void
     */
    public function test_downgrade_is_blocked(): void {
        $result = upgrade_path::analyze(502, 405);
        $this->assertSame('error', $result['status']);
        $this->assertFalse($result['directallowed']);
    }

    /**
     * Test official minimum-from route.
     *
     * @return void
     */
    public function test_minimum_from_route_recommends_required_base_branch(): void {
        $result = upgrade_path::analyze(401, 502, null, '4.1.9');
        $this->assertSame('warning', $result['status']);
        $this->assertSame([404, 502], $result['route']);
        $this->assertSame(404, $result['next']);
    }

    /**
     * Test next step acceptance.
     *
     * @return void
     */
    public function test_next_step_is_allowed(): void {
        $result = upgrade_path::analyze(405, 500);
        $this->assertSame('success', $result['status']);
        $this->assertTrue($result['directallowed']);

        $result = upgrade_path::analyze(405, 502);
        $this->assertSame('success', $result['status']);
        $this->assertTrue($result['directallowed']);
    }

    /**
     * Moodle 5.3 accepts 4.5 as its minimum source branch.
     *
     * @return void
     */
    public function test_moodle_53_route_and_requirements(): void {
        $rule = rule_engine::get_rule(503);
        $this->assertSame(405, $rule['minimumfrombranch']);
        $this->assertSame('8.3.0', $rule['minimumphp']);
        $this->assertSame('16.0', $rule['dbminimums']['pgsql']);

        $direct = upgrade_path::analyze(405, 503);
        $this->assertSame('success', $direct['status']);
        $this->assertTrue($direct['directallowed']);

        $intermediate = upgrade_path::analyze(404, 503);
        $this->assertSame('warning', $intermediate['status']);
        $this->assertSame([405, 503], $intermediate['route']);
    }

    /**
     * The Moodle 4 family has recognised intermediate rules and precise minimum patch releases.
     *
     * @return void
     */
    public function test_all_moodle_4_sources_have_valid_routes_to_moodle_5(): void {
        foreach ([402 => '4.2.3', 403 => '4.3.0',
                404 => '4.4.0', 405 => '4.5.0'] as $branch => $release) {
            $target = upgrade_path::analyze($branch, 500, null, $release);
            $this->assertSame('success', $target['status'], $release);
            $this->assertTrue($target['directallowed'], $release);
        }
        $this->assertSame([402, 500], upgrade_path::analyze(401, 500, null, '4.1.9')['route']);
        $this->assertSame([404, 502], upgrade_path::analyze(402, 502, null, '4.2.3')['route']);
        $this->assertSame([405, 503], upgrade_path::analyze(403, 503, null, '4.3.0')['route']);
    }

    /**
     * Missing or insufficient patch information must not be treated as an approved direct jump.
     *
     * @return void
     */
    public function test_patch_release_and_chained_intermediate_route(): void {
        $outdated = upgrade_path::analyze(401, 405, null, '4.1.0');
        $this->assertFalse($outdated['directallowed']);
        $this->assertSame('4.1.2', $outdated['nextrelease']);
        $this->assertSame('4.1 → 4.1.2 → 4.5', upgrade_path::route_text(401, $outdated['route'], '4.1.2'));

        $chained = upgrade_path::analyze(401, 503, null, '4.1.0');
        $this->assertSame([401, 405, 503], $chained['route']);
        $this->assertSame('4.1.2', $chained['nextrelease']);
        $this->assertSame('4.1 → 4.1.2 → 4.5 → 5.3',
            upgrade_path::route_text(401, $chained['route'], $chained['nextrelease']));

        $this->assertFalse(upgrade_path::analyze(402, 500, null, '4.2.0')['directallowed']);
        $this->assertTrue(upgrade_path::analyze(402, 500, null, '4.2.3')['directallowed']);
        $unknown = upgrade_path::analyze(402, 501);
        $this->assertSame('warning', $unknown['status']);
        $this->assertFalse($unknown['directallowed']);
        $this->assertSame([], $unknown['route']);
    }

    /**
     * Server validation must use the same source release comparison as the route analysis.
     *
     * @return void
     */
    public function test_server_validation_checks_source_patch_release(): void {
        $baseenv = ['branch' => '401', 'dbtype' => 'mysqli', 'dbinfo' => 'MySQL 8.4.0'];
        $checks = requirements_validator::validate(405, $baseenv + ['release' => '4.1.0']);
        $source = array_values(array_filter($checks, static function(array $check): bool {
            return $check['key'] === 'moodle_source_branch';
        }))[0];
        $this->assertSame('fail', $source['status']);
        $this->assertSame('4.1.2', $source['required']);

        $checks = requirements_validator::validate(405, $baseenv + ['release' => '4.1.2+ (Build: 2023)']);
        $source = array_values(array_filter($checks, static function(array $check): bool {
            return $check['key'] === 'moodle_source_branch';
        }))[0];
        $this->assertSame('pass', $source['status']);

        $checks = requirements_validator::validate(405, $baseenv);
        $source = array_values(array_filter($checks, static function(array $check): bool {
            return $check['key'] === 'moodle_source_branch';
        }))[0];
        $this->assertSame('warning', $source['status']);
    }
}
