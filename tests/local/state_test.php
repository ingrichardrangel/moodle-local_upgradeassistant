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
 * Tests for wizard state handling.
 *
 * @package    local_upgradeassistant
 * @category   test
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \local_upgradeassistant\local\state
 */
final class state_test extends \advanced_testcase {
    /**
     * Test allowed step whitelist.
     *
     * @return void
     */
    public function test_allowed_step_whitelist(): void {
        $this->assertTrue(state::is_valid_step('backupdb'));
        $this->assertFalse(state::is_valid_step('arbitrary-step'));
    }

    /**
     * Test complete step persists state.
     *
     * @return void
     */
    public function test_complete_step(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        state::complete_step('backupdb');
        $state = state::get();
        $this->assertArrayHasKey('backupdb', $state['completed']);
    }
}
