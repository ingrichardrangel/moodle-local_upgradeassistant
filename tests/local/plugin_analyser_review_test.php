<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_upgradeassistant\local;

/**
 * Tests for plugin dependency and official-removal analysis.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\plugin_analyser
 * @covers     \local_upgradeassistant\local\risk_assessor
 */
final class plugin_analyser_review_test extends \advanced_testcase {
    /**
     * Declared dependencies are verified against target versions.
     *
     * @return void
     */
    public function test_dependencies_are_checked_against_target_inventory(): void {
        $dependencies = [
            'local_ready' => 2026010100,
            'local_old' => 2026010100,
            'local_missing' => 2026010100,
        ];
        $target = [
            'local_ready' => ['version' => 2026020100],
            'local_old' => ['version' => 2025010100],
        ];

        $result = plugin_analyser::evaluate_dependencies($dependencies, $target);

        $this->assertSame(['local_missing'], $result['missing']);
        $this->assertSame(['local_old'], $result['outdated']);
        $this->assertEmpty($result['unknown']);
    }

    /**
     * auth_mnet is an expected core removal for Moodle 5.x targets.
     *
     * @return void
     */
    public function test_auth_mnet_is_expected_core_removal_for_moodle_5(): void {
        $removal = plugin_analyser::expected_core_removal('auth_mnet', 502);

        $this->assertNotNull($removal);
        $this->assertSame(500, $removal['since']);
        $this->assertSame('MDL-84307', $removal['issue']);
        $this->assertNull(plugin_analyser::expected_core_removal('auth_mnet', 404));
    }

    /**
     * Reviewed findings no longer contribute to the risk score.
     *
     * @return void
     */
    public function test_reviewed_findings_do_not_add_risk(): void {
        $risk = risk_assessor::assess([
            ['severity' => 'high', 'status' => 'reviewed'],
            ['severity' => 'medium', 'status' => 'open'],
        ]);

        $this->assertSame(12, $risk['score']);
    }
}
