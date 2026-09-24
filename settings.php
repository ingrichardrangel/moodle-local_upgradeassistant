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

/**
 * Admin settings entry for Upgrade Assistant.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$systemcontext = context_system::instance();
$canviewassistant = has_capability('local/upgradeassistant:view', $systemcontext);
$canconfigureassistant = has_capability('local/upgradeassistant:configure', $systemcontext);

if ($hassiteconfig || $canviewassistant || $canconfigureassistant) {
    $ADMIN->add('server', new admin_externalpage(
        'local_upgradeassistant',
        get_string('pluginname', 'local_upgradeassistant'),
        new moodle_url('/local/upgradeassistant/index.php'),
        'local/upgradeassistant:view'
    ));

    $settings = new admin_settingpage(
        'local_upgradeassistant_settings',
        get_string('settingspage', 'local_upgradeassistant'),
        'local/upgradeassistant:configure'
    );

    $settings->add(new admin_setting_configcheckbox(
        'local_upgradeassistant/enablelifecyclesync',
        get_string('enablelifecyclesync', 'local_upgradeassistant'),
        get_string('enablelifecyclesync_desc', 'local_upgradeassistant'),
        1
    ));
    if ($hassiteconfig) {
        $ADMIN->add('localplugins', $settings);
    }
}
