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
 * Basic local licensing helper for Pro features.
 *
 * This release intentionally uses a local/basic check only. It prepares the
 * product for commercial flows without blocking evaluation in development.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class license_manager {
    /**
     * Return license status for the UI.
     *
     * @return array
     */
    public static function status_for_template(): array {
        $key = trim((string)get_config('local_upgradeassistant', 'licensekey'));
        $email = trim((string)get_config('local_upgradeassistant', 'licenseemail'));
        $valid = self::is_valid_key($key);

        return [
            'keyconfigured' => $key !== '',
            'email' => s($email),
            'maskedkey' => $key === '' ? get_string('notconfigured', 'local_upgradeassistant') : self::mask($key),
            'status' => $valid ? get_string('licensestatusactive', 'local_upgradeassistant') : get_string('licensestatusdev', 'local_upgradeassistant'),
            'statusclass' => $valid ? 'ua-badge-ok' : 'ua-badge-warn',
            'mode' => $valid ? 'pro' : 'developer',
        ];
    }

    /**
     * Check if the configured key looks like a Pro key.
     *
     * @param string $key Key.
     * @return bool
     */
    public static function is_valid_key(string $key): bool {
        return (bool)preg_match('/^SUA-PRO-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}$/', trim($key));
    }

    /**
     * Mask a key for display.
     *
     * @param string $key Key.
     * @return string
     */
    private static function mask(string $key): string {
        $key = trim($key);
        if (strlen($key) <= 8) {
            return '********';
        }
        return substr($key, 0, 8) . '********' . substr($key, -4);
    }
}
