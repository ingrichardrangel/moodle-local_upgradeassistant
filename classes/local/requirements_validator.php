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
 * Validates local server requirements for a target Moodle branch.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class requirements_validator {
    /**
     * Validate current environment against target branch rules.
     *
     * @param int|string $targetbranch Target branch.
     * @param array|null $env Optional environment payload.
     * @param array|null $targetinfo Selected target installation.
     * @return array
     */
    public static function validate($targetbranch, ?array $env = null, ?array $targetinfo = null): array {
        $targetbranch = upgrade_path::branch_to_int($targetbranch);
        $env = $env ?? detector::environment();
        $rule = rule_engine::get_rule($targetbranch, $targetinfo);
        $checks = [];

        if ($rule === null) {
            return [[
                'key' => 'rule_unknown',
                'label' => get_string('validationruleunknown', 'local_upgradeassistant'),
                'status' => 'warning',
                'severity' => 'medium',
                'current' => $targetbranch > 0 ? upgrade_path::label_without_rule($targetbranch)
                    : get_string('notdetected', 'local_upgradeassistant'),
                'required' => get_string('notavailable', 'local_upgradeassistant'),
                'message' => get_string('validationruleunknowndesc', 'local_upgradeassistant'),
            ]];
        }

        if (!empty($rule['minimumphp'])) {
            $checks[] = self::build_check(
                'php_min_version',
                get_string('validationphpversion', 'local_upgradeassistant'),
                PHP_VERSION,
                $rule['minimumphp'],
                version_compare(PHP_VERSION, $rule['minimumphp'], '>=')
            );
        }
        if (($rule['source'] ?? '') === 'target_environment') {
            $checks[] = [
                'key' => 'rule_partial',
                'label' => get_string('validationpartialrule', 'local_upgradeassistant'),
                'status' => 'warning',
                'severity' => 'medium',
                'current' => upgrade_path::label_without_rule($targetbranch),
                'required' => '',
                'message' => get_string('validationpartialruledesc', 'local_upgradeassistant'),
            ];
        }

        if (($rule['sodium'] ?? '') === 'required') {
            $checks[] = self::build_check(
                'php_extension_sodium',
                get_string('validationsodium', 'local_upgradeassistant'),
                extension_loaded('sodium') ? get_string('detected', 'local_upgradeassistant') : get_string('notdetected', 'local_upgradeassistant'),
                get_string('required', 'local_upgradeassistant'),
                extension_loaded('sodium')
            );
        }

        $maxinputvars = (int)ini_get('max_input_vars');
        if (!empty($rule['maxinputvars'])) {
            $checks[] = self::build_check(
                'php_max_input_vars',
                get_string('validationmaxinputvars', 'local_upgradeassistant'),
                (string)$maxinputvars,
                (string)$rule['maxinputvars'],
                $maxinputvars <= 0 || $maxinputvars >= (int)$rule['maxinputvars'],
                $maxinputvars <= 0 || ($targetbranch === 401 && PHP_MAJOR_VERSION < 8 && $maxinputvars < 5000)
                    ? 'warning' : null
            );
        }

        if (!empty($rule['bit64'])) {
            $checks[] = self::build_check(
                'php_64bit',
                get_string('validationphp64bit', 'local_upgradeassistant'),
                PHP_INT_SIZE >= 8 ? '64-bit' : '32-bit',
                '64-bit',
                PHP_INT_SIZE >= 8
            );
        }

        $dbtext = self::database_description($env['dbinfo'] ?? '');
        $dbtype = self::normalise_dbtype((string)($env['dbtype'] ?? ''), $dbtext);
        $dbversion = self::extract_version($dbtext);
        $dbminimum = $rule['dbminimums'][$dbtype] ?? null;
        if ($dbminimum === null) {
            $checks[] = self::build_check(
                'db_rule_unknown',
                get_string('validationdatabase', 'local_upgradeassistant'),
                $dbtype . ' ' . $dbtext,
                get_string('notavailable', 'local_upgradeassistant'),
                false,
                'warning'
            );
        } else {
            $checks[] = self::build_check(
                'db_min_version',
                get_string('validationdatabase', 'local_upgradeassistant'),
                $dbtype . ' ' . ($dbversion ?: $dbtext),
                $dbminimum,
                $dbversion !== '' && version_compare($dbversion, $dbminimum, '>='),
                $dbversion === '' ? 'warning' : null
            );
        }

        $currentbranch = upgrade_path::branch_to_int($env['branch'] ?? '');
        if (!empty($rule['minimumfrombranch'])) {
            $currentrelease = (string)($env['release'] ?? '');
            $meetsminimum = upgrade_path::source_meets_minimum($currentbranch, $currentrelease, $rule);
            $checks[] = self::build_check(
                'moodle_source_branch',
                get_string('validationmoodlesource', 'local_upgradeassistant'),
                $currentrelease !== '' ? $currentrelease : upgrade_path::label_without_rule($currentbranch),
                (string)($rule['minimumfrom'] ?? upgrade_path::label_without_rule((int)$rule['minimumfrombranch'])),
                $meetsminimum === true,
                $meetsminimum === null ? 'warning' : null
            );
        }

        return $checks;
    }

    /**
     * Convert checks into Mustache rows.
     *
     * @param int|string $targetbranch Target branch.
     * @param array|null $env Optional environment.
     * @param array|null $targetinfo Selected target installation.
     * @return array
     */
    public static function rows_for_template($targetbranch, ?array $env = null, ?array $targetinfo = null): array {
        $rows = [];
        foreach (self::validate($targetbranch, $env, $targetinfo) as $check) {
            $rows[] = $check + [
                'statuslabel' => get_string('validationstatus' . $check['status'], 'local_upgradeassistant'),
                'statusclass' => self::status_class($check['status']),
            ];
        }
        return $rows;
    }

    /**
     * Build one validation row.
     *
     * @param string $key Key.
     * @param string $label Label.
     * @param string $current Current value.
     * @param string $required Required value.
     * @param bool $passed Whether it passed.
     * @param string|null $forcedstatus Optional status.
     * @return array
     */
    private static function build_check(
        string $key,
        string $label,
        string $current,
        string $required,
        bool $passed,
        ?string $forcedstatus = null
    ): array {
        $status = $forcedstatus ?? ($passed ? 'pass' : 'fail');
        return [
            'key' => $key,
            'label' => $label,
            'status' => $status,
            'severity' => $status === 'fail' ? 'critical' : ($status === 'warning' ? 'medium' : 'info'),
            'current' => $current,
            'required' => $required,
            'message' => '',
        ];
    }

    /**
     * Return CSS class for validation status.
     *
     * @param string $status Status.
     * @return string
     */
    private static function status_class(string $status): string {
        if ($status === 'fail') {
            return 'ua-badge-danger';
        }
        if ($status === 'warning') {
            return 'ua-badge-warn';
        }
        return 'ua-badge-ok';
    }

    /**
     * Return database description text.
     *
     * @param mixed $dbinfo DB info.
     * @return string
     */
    private static function database_description($dbinfo): string {
        if (is_array($dbinfo)) {
            if (!empty($dbinfo['description'])) {
                return (string)$dbinfo['description'];
            }
            if (!empty($dbinfo['version'])) {
                return (string)$dbinfo['version'];
            }
            $json = json_encode($dbinfo);
            return $json === false ? '' : $json;
        }
        return (string)$dbinfo;
    }

    /**
     * Normalise Moodle DB type.
     *
     * @param string $dbtype DB type.
     * @param string $description Description.
     * @return string
     */
    public static function normalise_dbtype(string $dbtype, string $description): string {
        $dbtype = strtolower($dbtype);
        $description = strtolower($description);
        if ($dbtype === 'mariadb' || strpos($description, 'mariadb') !== false) {
            return 'mariadb';
        }
        if ($dbtype === 'mysqli' || $dbtype === 'mysql') {
            return 'mysqli';
        }
        if ($dbtype === 'pgsql' || $dbtype === 'postgres') {
            return 'pgsql';
        }
        if ($dbtype === 'sqlsrv' || strpos($description, 'sql server') !== false) {
            return 'sqlsrv';
        }
        return $dbtype;
    }

    /**
     * Extract semantic version.
     *
     * @param string $text Text.
     * @return string
     */
    public static function extract_version(string $text): string {
        if (preg_match('/(\d+(?:\.\d+){0,3})/', $text, $matches)) {
            return $matches[1];
        }
        return '';
    }
}
