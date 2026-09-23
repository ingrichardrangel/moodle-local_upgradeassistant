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
 * Validates target upgrade paths and recommended intermediate branches.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class upgrade_path {
    /**
     * Convert Moodle branch to integer.
     *
     * @param string|int|null $branch Moodle branch.
     * @return int
     */
    public static function branch_to_int($branch): int {
        $branch = trim((string)($branch ?? ''));

        if ($branch === '') {
            return 0;
        }

        if (preg_match('/^(\d{3})$/', $branch, $matches)) {
            return (int)$matches[1];
        }

        if (preg_match('/(\d+)\.(\d+)/', $branch, $matches)) {
            return ((int)$matches[1] * 100) + (int)$matches[2];
        }

        $digits = preg_replace('/[^0-9]/', '', $branch);
        return $digits === '' ? 0 : (int)$digits;
    }

    /**
     * Label for branch integer.
     *
     * @param int $branch Moodle branch.
     * @return string
     */
    public static function label(int $branch): string {
        $rule = rule_engine::get_rule($branch);
        if ($rule !== null && !empty($rule['label'])) {
            return (string)$rule['label'];
        }

        return self::label_without_rule($branch);
    }

    /**
     * Label a branch without consulting the rule engine.
     *
     * @param int $branch Branch number.
     * @return string
     */
    public static function label_without_rule(int $branch): string {
        if ($branch >= 100 && $branch <= 999) {
            $major = (int)floor($branch / 100);
            $minor = $branch % 100;
            return $major . '.' . $minor;
        }

        return (string)$branch;
    }

    /**
     * Analyze current and target upgrade path.
     *
     * @param string|int|null $currentbranch Current Moodle branch.
     * @param string|int|null $targetbranch Target Moodle branch.
     * @param array|null $targetinfo Selected target installation.
     * @param string|null $currentrelease Exact installed Moodle release (including patch number).
     * @return array
     */
    public static function analyze(
        $currentbranch,
        $targetbranch,
        ?array $targetinfo = null,
        ?string $currentrelease = null
    ): array {
        $current = self::branch_to_int($currentbranch);
        $target = self::branch_to_int($targetbranch);

        if ($current === 0 || $target === 0) {
            return [
                'status' => 'warning',
                'message' => get_string('analysisunknownbranch', 'local_upgradeassistant'),
                'route' => [],
                'next' => null,
                'directallowed' => false,
            ];
        }

        if ($target < $current) {
            return [
                'status' => 'error',
                'message' => get_string('analysisdowngrade', 'local_upgradeassistant'),
                'route' => [],
                'next' => null,
                'directallowed' => false,
            ];
        }

        if ($target === $current) {
            return [
                'status' => 'warning',
                'message' => get_string('analysissamebranch', 'local_upgradeassistant'),
                'route' => [],
                'next' => null,
                'directallowed' => false,
            ];
        }

        $rule = rule_engine::get_rule($target, $targetinfo);
        if ($rule !== null) {
            $minimumfrom = (int)($rule['minimumfrombranch'] ?? 0);
            $minimumrelease = (string)($rule['minimumfrom'] ?? self::label_without_rule($minimumfrom));
            $sourcevalid = self::source_meets_minimum($current, $currentrelease, $rule);
            if ($minimumfrom > 0 && $sourcevalid === true) {
                return [
                    'status' => 'success',
                    'message' => get_string('analysisvalidnext', 'local_upgradeassistant'),
                    'route' => [$target],
                    'next' => $target,
                    'directallowed' => true,
                ];
            }

            if ($minimumfrom > 0 && $sourcevalid === null) {
                return [
                    'status' => 'warning',
                    'message' => get_string('analysisunknownpatch', 'local_upgradeassistant', $minimumrelease),
                    'route' => [],
                    'next' => null,
                    'directallowed' => false,
                ];
            }

            if ($minimumfrom > 0) {
                $route = [$minimumfrom, $target];
                $nextrelease = $minimumrelease;
                if ($current < $minimumfrom) {
                    $intermediate = rule_engine::get_rule($minimumfrom);
                    if (
                        $intermediate !== null
                        && self::source_meets_minimum($current, $currentrelease, $intermediate) !== true
                        && (int)$intermediate['minimumfrombranch'] === $current
                    ) {
                        array_unshift($route, $current);
                        $nextrelease = (string)$intermediate['minimumfrom'];
                    }
                }
                return [
                    'status' => 'warning',
                    'message' => get_string('analysisnotnext', 'local_upgradeassistant', $nextrelease),
                    'route' => array_values(array_unique($route)),
                    'next' => $route[0],
                    'nextrelease' => $nextrelease,
                    'directallowed' => false,
                ];
            }
        }

        return [
            'status' => 'warning',
            'message' => get_string('analysisunverifiedroute', 'local_upgradeassistant'),
            'route' => [],
            'next' => null,
            'directallowed' => false,
        ];
    }

    /**
     * Check the exact minimum source version when the target requires a patch release.
     *
     * @param int $currentbranch Installed branch.
     * @param string|null $currentrelease Installed release, if known.
     * @param array $rule Target rule.
     * @return bool|null Null when a required patch cannot be verified.
     */
    public static function source_meets_minimum(int $currentbranch, ?string $currentrelease, array $rule): ?bool {
        $minimumbranch = (int)($rule['minimumfrombranch'] ?? 0);
        if ($minimumbranch <= 0) {
            return null;
        }
        if ($currentbranch !== $minimumbranch) {
            return $currentbranch > $minimumbranch;
        }
        $minimumrelease = (string)($rule['minimumfrom'] ?? '');
        if (!preg_match('/^\d+\.\d+\.(\d+)$/', $minimumrelease, $required) || (int)$required[1] === 0) {
            return true;
        }
        if (!preg_match('/^\s*(\d+\.\d+\.\d+)(?!\d)/', (string)$currentrelease, $installed)) {
            return null;
        }
        return version_compare($installed[1], $minimumrelease, '>=');
    }

    /**
     * Human route.
     *
     * @param int $current Current branch.
     * @param array $route Route branches.
     * @param string|null $nextrelease Required intermediate patch release, if any.
     * @return string
     */
    public static function route_text(int $current, array $route, ?string $nextrelease = null): string {
        $labels = [self::label($current)];
        foreach ($route as $index => $branch) {
            $labels[] = $index === 0 && $nextrelease !== null ? $nextrelease : self::label((int)$branch);
        }
        return implode(' → ', $labels);
    }

    /**
     * Return environment compatibility rows for the known target branches.
     *
     * @param string $phpversion PHP version.
     * @return array
     */
    public static function compatibility_rows(string $phpversion): array {
        $rows = [];
        foreach (rule_engine::builtin_rules() as $branch => $rule) {
            $minphp = (string)($rule['minimumphp'] ?? '');
            $phpok = $minphp === '' || version_compare($phpversion, $minphp, '>=');
            $rows[] = [
                'branch' => $branch,
                'label' => $rule['label'],
                'minphp' => $minphp,
                'minimumrelease' => $rule['minimumfrom'],
                'status' => $phpok
                    ? get_string('compatible', 'local_upgradeassistant')
                    : get_string('needsreview', 'local_upgradeassistant'),
                'statusclass' => $phpok ? 'ua-badge-ok' : 'ua-badge-warn',
            ];
        }
        return $rows;
    }
}
