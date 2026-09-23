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
 * Detects server profile and returns upgrade recommendations.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class server_recommendation_engine {
    /**
     * Detect server profile.
     *
     * @return string
     */
    public static function detect_profile(): string {
        global $CFG;

        $path = strtolower($CFG->dirroot ?? '');
        $software = strtolower($_SERVER['SERVER_SOFTWARE'] ?? '');
        $documentroot = strtolower($_SERVER['DOCUMENT_ROOT'] ?? '');

        if (PHP_OS_FAMILY === 'Windows' && (strpos($path, 'xampp') !== false || strpos($documentroot, 'xampp') !== false)) {
            return 'xampp';
        }
        if (strpos($path, '/home/') === 0 || strpos($software, 'litespeed') !== false || getenv('CPANEL') !== false) {
            return 'cpanel';
        }
        if (PHP_OS_FAMILY === 'Windows') {
            return 'windows';
        }
        if (strpos($path, '/var/www') === 0 || strpos($path, '/srv/') === 0) {
            return 'vpslinux';
        }
        return 'linux';
    }

    /**
     * Return human readable profile label.
     *
     * @param string|null $profile Profile key.
     * @return string
     */
    public static function profile_label(?string $profile = null): string {
        $profile = $profile ?? self::detect_profile();
        $labels = [
            'xampp' => get_string('profilexampp', 'local_upgradeassistant'),
            'cpanel' => get_string('profilecpanel', 'local_upgradeassistant'),
            'windows' => get_string('profilewindows', 'local_upgradeassistant'),
            'vpslinux' => get_string('profilevpslinux', 'local_upgradeassistant'),
            'linux' => get_string('profilelinux', 'local_upgradeassistant'),
        ];
        return $labels[$profile] ?? $profile;
    }

    /**
     * Return recommendation rows.
     *
     * @param string|null $profile Profile key.
     * @return array
     */
    public static function rows_for_template(?string $profile = null): array {
        $profile = $profile ?? self::detect_profile();
        $recommendations = self::recommendations($profile);
        $rows = [];
        foreach ($recommendations as $recommendation) {
            $rows[] = ['text' => $recommendation];
        }
        return $rows;
    }

    /**
     * Return recommendation texts.
     *
     * @param string $profile Profile key.
     * @return array
     */
    public static function recommendations(string $profile): array {
        if ($profile === 'xampp') {
            return [
                get_string('recxampp1', 'local_upgradeassistant'),
                get_string('recxampp2', 'local_upgradeassistant'),
                get_string('recxampp3', 'local_upgradeassistant'),
                get_string('recxampp4', 'local_upgradeassistant'),
            ];
        }
        if ($profile === 'cpanel') {
            return [
                get_string('reccpanel1', 'local_upgradeassistant'),
                get_string('reccpanel2', 'local_upgradeassistant'),
                get_string('reccpanel3', 'local_upgradeassistant'),
                get_string('reccpanel4', 'local_upgradeassistant'),
            ];
        }
        if ($profile === 'windows') {
            return [
                get_string('recwindows1', 'local_upgradeassistant'),
                get_string('recwindows2', 'local_upgradeassistant'),
                get_string('recwindows3', 'local_upgradeassistant'),
            ];
        }
        if ($profile === 'vpslinux') {
            return [
                get_string('recvps1', 'local_upgradeassistant'),
                get_string('recvps2', 'local_upgradeassistant'),
                get_string('recvps3', 'local_upgradeassistant'),
                get_string('recvps4', 'local_upgradeassistant'),
            ];
        }
        return [
            get_string('reclinux1', 'local_upgradeassistant'),
            get_string('reclinux2', 'local_upgradeassistant'),
            get_string('reclinux3', 'local_upgradeassistant'),
        ];
    }
}
