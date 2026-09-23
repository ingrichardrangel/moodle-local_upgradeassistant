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

namespace local_upgradeassistant\task;

use local_upgradeassistant\local\lifecycle_manager;

/**
 * Scheduled task to refresh official Moodle lifecycle support data.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class sync_lifecycle_task extends \core\task\scheduled_task {
    /**
     * Return task name.
     *
     * @return string
     */
    public function get_name(): string {
        return get_string('synclifecycletask', 'local_upgradeassistant');
    }

    /**
     * Execute task.
     *
     * @return void
     */
    public function execute(): void {
        if (get_config('local_upgradeassistant', 'enablelifecyclesync') === '0') {
            return;
        }

        lifecycle_manager::refresh_dataset();
        \local_upgradeassistant\event\lifecycle_synced::create([
            'context' => \context_system::instance(),
        ])->trigger();
    }
}
