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
 * Local version-rule engine used by Upgrade Assistant.
 *
 * The first release ships with local rules so the assistant remains useful
 * without an external SaaS API. Future paid plans can synchronise these rows
 * from a signed compatibility API while keeping this class as the offline
 * fallback.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class rule_engine {
    /** Rule table. */
    private const TABLE = 'local_upgradeassistant_rules';

    /**
     * Built-in Moodle rules.
     *
     * @return array
     */
    public static function builtin_rules(): array {
        return [
            401 => [
                'branch' => 401,
                'label' => '4.1',
                'minimumfrom' => '3.9',
                'minimumfrombranch' => 309,
                'minimumphp' => '7.4.0',
                'sodium' => 'recommended',
                'maxinputvars' => 5000,
                'bit64' => false,
                'dbminimums' => [
                    'mariadb' => '10.4.0',
                    'mysqli' => '5.7.0',
                    'pgsql' => '12.0',
                    'sqlsrv' => '2017',
                ],
            ],
            402 => [
                'branch' => 402,
                'label' => '4.2',
                'minimumfrom' => '3.11.8',
                'minimumfrombranch' => 311,
                'minimumphp' => '8.0.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.6.7',
                    'mysqli' => '8.0.0',
                    'pgsql' => '13.0',
                    'sqlsrv' => '2017',
                ],
            ],
            403 => [
                'branch' => 403,
                'label' => '4.3',
                'minimumfrom' => '3.11.8',
                'minimumfrombranch' => 311,
                'minimumphp' => '8.0.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.6.7',
                    'mysqli' => '8.0.0',
                    'pgsql' => '13.0',
                    'sqlsrv' => '2017',
                ],
            ],
            404 => [
                'branch' => 404,
                'label' => '4.4',
                'minimumfrom' => '4.1.2',
                'minimumfrombranch' => 401,
                'minimumphp' => '8.1.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.6.7',
                    'mysqli' => '8.0.0',
                    'pgsql' => '13.0',
                    'sqlsrv' => '2017',
                ],
            ],
            405 => [
                'branch' => 405,
                'label' => '4.5',
                'minimumfrom' => '4.1.2',
                'minimumfrombranch' => 401,
                'minimumphp' => '8.1.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.6.7',
                    'mysqli' => '8.0.0',
                    'pgsql' => '13.0',
                    'sqlsrv' => '2017',
                ],
            ],
            500 => [
                'branch' => 500,
                'label' => '5.0',
                'minimumfrom' => '4.2.3',
                'minimumfrombranch' => 402,
                'minimumphp' => '8.2.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.11.0',
                    'mysqli' => '8.4.0',
                    'pgsql' => '14.0',
                    'sqlsrv' => '2017',
                ],
            ],
            501 => [
                'branch' => 501,
                'label' => '5.1',
                'minimumfrom' => '4.2.3',
                'minimumfrombranch' => 402,
                'minimumphp' => '8.2.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.11.0',
                    'mysqli' => '8.4.0',
                    'pgsql' => '15.0',
                    'sqlsrv' => '2017',
                ],
            ],
            502 => [
                'branch' => 502,
                'label' => '5.2',
                'minimumfrom' => '4.4',
                'minimumfrombranch' => 404,
                'minimumphp' => '8.3.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.11.0',
                    'mysqli' => '8.4.0',
                    'pgsql' => '16.0',
                    'sqlsrv' => '2019',
                ],
            ],
            503 => [
                'branch' => 503,
                'label' => '5.3',
                'minimumfrom' => '4.5',
                'minimumfrombranch' => 405,
                'minimumphp' => '8.3.0',
                'sodium' => 'required',
                'maxinputvars' => 5000,
                'bit64' => true,
                'dbminimums' => [
                    'mariadb' => '10.11.0',
                    'mysqli' => '8.4.0',
                    'pgsql' => '17.0',
                    'sqlsrv' => '2019',
                ],
            ],
        ];
    }

    /**
     * Return a rule by Moodle branch.
     *
     * @param int|string $branch Moodle branch.
     * @param array|null $targetinfo Selected target installation, if available.
     * @return array|null
     */
    public static function get_rule($branch, ?array $targetinfo = null): ?array {
        global $DB;

        $branch = upgrade_path::branch_to_int($branch);
        if ($branch <= 0) {
            return null;
        }

        if ($DB->get_manager()->table_exists(self::TABLE)) {
            $record = $DB->get_record(self::TABLE, [
                'targetbranch' => (string)$branch,
                'ruletype' => 'requirements',
                'rulekey' => 'moodle_' . $branch,
                'enabled' => 1,
            ]);
            if ($record && !empty($record->rulesjson)) {
                $decoded = json_decode($record->rulesjson, true);
                if (is_array($decoded) && $record->source !== 'builtin') {
                    return $decoded;
                }
            }
        }

        $builtin = self::builtin_rules();
        return $builtin[$branch] ?? self::rule_from_target($branch, $targetinfo);
    }

    /**
     * Read declared core requirements from the selected Moodle package without executing its code.
     * An unknown minimum source branch is deliberately left unknown.
     *
     * @param int $branch Expected branch.
     * @param array|null $targetinfo Version information returned by detector.
     * @return array|null
     */
    private static function rule_from_target(int $branch, ?array $targetinfo): ?array {
        if (
            empty($targetinfo['versionfile']) || !is_file($targetinfo['versionfile'])
            || upgrade_path::branch_to_int($targetinfo['branch'] ?? '') !== $branch
        ) {
            return null;
        }
        $file = dirname($targetinfo['versionfile']) . '/admin/environment.xml';
        if (!is_file($file) && !empty($targetinfo['haspublic']) && !empty($targetinfo['publicpath'])) {
            $file = $targetinfo['publicpath'] . '/admin/environment.xml';
        }
        if (!is_file($file) || !is_readable($file) || filesize($file) > 2097152) {
            return null;
        }
        $content = file_get_contents($file);
        if ($content === false || stripos($content, '<!DOCTYPE') !== false) {
            return null;
        }
        $old = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($old);
        }
        if ($xml === false || $xml->getName() !== 'COMPATIBILITY_MATRIX') {
            return null;
        }
        foreach ($xml->MOODLE as $entry) {
            if (upgrade_path::branch_to_int((string)$entry['version']) !== $branch) {
                continue;
            }
            $php = isset($entry->PHP) && (string)$entry->PHP['level'] === 'required'
                ? (string)$entry->PHP['version'] : '';
            $vendors = ['mariadb' => 'mariadb', 'mysql' => 'mysqli', 'mysqli' => 'mysqli',
                'postgres' => 'pgsql', 'pgsql' => 'pgsql', 'mssql' => 'sqlsrv', 'sqlsrv' => 'sqlsrv'];
            $dbminimums = [];
            if (isset($entry->DATABASE) && (string)$entry->DATABASE['level'] === 'required') {
                foreach ($entry->DATABASE->VENDOR as $vendor) {
                    $type = $vendors[strtolower((string)$vendor['name'])] ?? null;
                    if ($type !== null && (string)$vendor['version'] !== '') {
                        $dbminimums[$type] = (string)$vendor['version'];
                    }
                }
            }
            if ($php === '' && !$dbminimums) {
                return null;
            }
            return [
                'branch' => $branch,
                'label' => upgrade_path::label_without_rule($branch),
                'minimumfrom' => '',
                'minimumfrombranch' => 0,
                'minimumphp' => $php,
                'sodium' => 'unknown',
                'maxinputvars' => 0,
                'bit64' => false,
                'dbminimums' => $dbminimums,
                'source' => 'target_environment',
            ];
        }
        return null;
    }

    /**
     * Return all rules for the UI.
     *
     * @return array
     */
    public static function get_rules_for_template(): array {
        $rows = [];
        foreach (self::builtin_rules() as $rule) {
            $rows[] = [
                'label' => $rule['label'],
                'targetbranch' => $rule['branch'],
                'minimumfrom' => $rule['minimumfrom'],
                'minimumphp' => $rule['minimumphp'],
                'sodium' => get_string('rule' . $rule['sodium'], 'local_upgradeassistant'),
                'maxinputvars' => $rule['maxinputvars'],
                'source' => get_string('rulesourcebuiltin', 'local_upgradeassistant'),
            ];
        }
        return $rows;
    }

    /**
     * Seed local rule rows if the table exists and is empty.
     *
     * @return void
     */
    public static function seed_builtin_rules(): void {
        global $DB;

        if (!$DB->get_manager()->table_exists(self::TABLE)) {
            return;
        }
        if ($DB->record_exists(self::TABLE, [])) {
            return;
        }

        $now = time();
        foreach (self::builtin_rules() as $rule) {
            $record = (object)[
                'rulesetversion' => '2026.06-local',
                'source' => 'builtin',
                'moodlebranch' => (string)$rule['branch'],
                'targetbranch' => (string)$rule['branch'],
                'ruletype' => 'requirements',
                'rulekey' => 'moodle_' . $rule['branch'],
                'severity' => 'critical',
                'rulesjson' => self::json($rule),
                'recommendation' => get_string('rulesbuiltinrecommendation', 'local_upgradeassistant'),
                'enabled' => 1,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $DB->insert_record(self::TABLE, $record);
        }
    }

    /**
     * Encode JSON safely.
     *
     * @param mixed $value Value.
     * @return string
     */
    private static function json($value): string {
        $json = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $json === false ? '' : $json;
    }
}
