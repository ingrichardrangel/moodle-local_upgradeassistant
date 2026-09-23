<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.

namespace local_upgradeassistant\local;

/**
 * Tests lifecycle findings as upgrade context rather than circular blockers.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\lifecycle_manager
 * @covers     \local_upgradeassistant\local\risk_assessor
 */
final class lifecycle_context_test extends \advanced_testcase {
    /**
     * Unsupported source does not penalise a valid supported upgrade target.
     *
     * @return void
     */
    public function test_supported_target_mitigates_unsupported_source(): void {
        $this->resetAfterTest();
        set_config('lifecycledata', json_encode([
            'sourceurl' => 'https://moodledev.io/general/releases',
            'source' => 'Test data',
            'fetchedat' => time(),
            'versions' => [
                ['branch' => '404', 'version' => '4.4', 'label' => 'Moodle 4.4', 'lts' => false,
                    'releasedate' => '2024-04-22', 'generalend' => '2025-04-21', 'securityend' => '2025-12-08'],
                ['branch' => '502', 'version' => '5.2', 'label' => 'Moodle 5.2', 'lts' => false,
                    'releasedate' => '2026-04-20', 'generalend' => '2027-04-19', 'securityend' => '2027-10-04'],
            ],
        ]), 'local_upgradeassistant');

        $findings = lifecycle_manager::findings('404', '502');
        $source = array_values(array_filter($findings, static function(array $finding): bool {
            return $finding['code'] === 'lifecycle_current_unsupported';
        }));

        $this->assertCount(1, $source);
        $this->assertSame('info', $source[0]['severity']);
        $this->assertSame('closed', $source[0]['status']);
        $this->assertSame(0, risk_assessor::assess($source)['score']);
    }

    /**
     * Unsupported destination remains a real open risk.
     *
     * @return void
     */
    public function test_unsupported_target_remains_open_risk(): void {
        $this->resetAfterTest();
        set_config('lifecycledata', json_encode([
            'sourceurl' => 'https://moodledev.io/general/releases',
            'source' => 'Test data',
            'fetchedat' => time(),
            'versions' => [
                ['branch' => '401', 'version' => '4.1', 'label' => 'Moodle 4.1 LTS', 'lts' => true,
                    'releasedate' => '2022-11-28', 'generalend' => '2023-12-11', 'securityend' => '2025-12-08'],
                ['branch' => '404', 'version' => '4.4', 'label' => 'Moodle 4.4', 'lts' => false,
                    'releasedate' => '2024-04-22', 'generalend' => '2025-04-21', 'securityend' => '2025-12-08'],
            ],
        ]), 'local_upgradeassistant');

        $findings = lifecycle_manager::findings('401', '404');
        $target = array_values(array_filter($findings, static function(array $finding): bool {
            return $finding['code'] === 'lifecycle_target_unsupported';
        }));

        $this->assertCount(1, $target);
        $this->assertSame('high', $target[0]['severity']);
        $this->assertSame('open', $target[0]['status']);
    }
}
