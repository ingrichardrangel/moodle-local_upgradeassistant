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
 * Stores the wizard state in Moodle user preferences.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class state {
    /** User preference key. */
    public const PREF = 'local_upgradeassistant_state';

    /** Maximum serialized preference length kept intentionally conservative. */
    private const MAX_STATE_LENGTH = 12000;

    /** Allowed checklist step identifiers. */
    public const ALLOWED_STEPS = [
        'selecttarget',
        'maintenance',
        'boosttheme',
        'backupdb',
        'backupcode',
        'backupdata',
        'purgecaches',
        'confirmtarget',
        'servercompat',
    ];

    /**
     * Default wizard state.
     *
     * @return array
     */
    public static function defaults(): array {
        return [
            'targetpath' => '',
            'targetinfo' => null,
            'completed' => [],
            'lastscanpath' => '',
            'activereportid' => 0,
            'updatedat' => time(),
        ];
    }

    /**
     * Get current user wizard state.
     *
     * @return array
     */
    public static function get(): array {
        $raw = get_user_preferences(self::PREF, '');
        if ($raw === '') {
            return self::defaults();
        }

        $decoded = json_decode($raw, true);
        if (!is_array($decoded)) {
            return self::defaults();
        }

        $state = array_merge(self::defaults(), $decoded);
        if (!is_array($state['completed'])) {
            $state['completed'] = [];
        }

        foreach (array_keys($state['completed']) as $stepid) {
            if (!self::is_valid_step((string)$stepid)) {
                unset($state['completed'][$stepid]);
            }
        }

        return $state;
    }

    /**
     * Save wizard state.
     *
     * @param array $state State data.
     * @return void
     */
    public static function set(array $state): void {
        $state['updatedat'] = time();

        $json = json_encode($state);
        if ($json === false || strlen($json) > self::MAX_STATE_LENGTH) {
            throw new \coding_exception('Invalid Smart Upgrade Assistant state payload.');
        }

        set_user_preference(self::PREF, $json);
    }

    /**
     * Reset wizard state.
     *
     * @return void
     */
    public static function reset(): void {
        unset_user_preference(self::PREF);
    }

    /**
     * Mark a checklist step as completed.
     *
     * @param string $stepid Step identifier.
     * @return void
     */
    public static function complete_step(string $stepid): void {
        if (!self::is_valid_step($stepid)) {
            throw new \moodle_exception('invalidstep', 'local_upgradeassistant');
        }

        $state = self::get();
        $state['completed'][$stepid] = time();
        self::set($state);
    }

    /**
     * Save selected target installation.
     *
     * @param string $path Absolute path.
     * @param array $info Target Moodle information.
     * @return void
     */
    public static function set_target(string $path, array $info): void {
        $state = self::get();
        $targetchanged = (string)($state['targetpath'] ?? '') !== $path
            || (string)($state['targetinfo']['branch'] ?? '') !== (string)($info['branch'] ?? '');
        if ($targetchanged) {
            unset($state['completed']['confirmtarget'], $state['completed']['servercompat']);
            $state['activereportid'] = 0;
        }
        $state['targetpath'] = $path;
        $state['targetinfo'] = [
            'path' => $info['path'] ?? $path,
            'folder' => $info['folder'] ?? basename($path),
            'release' => $info['release'] ?? '',
            'branch' => $info['branch'] ?? '',
            'branchlabel' => $info['branchlabel'] ?? '',
            'version' => $info['version'] ?? '',
            'haspublic' => !empty($info['haspublic']),
            'publicpath' => $info['publicpath'] ?? '',
            'approot' => $info['approot'] ?? ($info['path'] ?? $path),
            'configpath' => $info['configpath'] ?? '',
        ];
        $state['completed']['selecttarget'] = time();
        self::set($state);
    }

    /**
     * Save last scan path.
     *
     * @param string $path Absolute path.
     * @return void
     */
    public static function set_scanpath(string $path): void {
        $state = self::get();
        $state['lastscanpath'] = $path;
        self::set($state);
    }

    /**
     * Save the active report for checklist actions linked to the current user.
     *
     * @param int $reportid Report ID.
     * @return void
     */
    public static function set_active_reportid(int $reportid): void {
        $state = self::get();
        $state['activereportid'] = max(0, $reportid);
        self::set($state);
    }

    /**
     * Get the active report ID for the current user.
     *
     * @return int Report ID, or 0 when no report is active.
     */
    public static function get_active_reportid(): int {
        $state = self::get();
        return max(0, (int)($state['activereportid'] ?? 0));
    }

    /**
     * Validate a checklist step.
     *
     * @param string $stepid Step identifier.
     * @return bool
     */
    public static function is_valid_step(string $stepid): bool {
        return in_array($stepid, self::ALLOWED_STEPS, true);
    }
}
