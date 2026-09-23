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
 * Advanced plugin inventory and comparison service.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class plugin_analyser {
    /** Plugin inventory table. */
    private const TABLE = 'local_upgradeassistant_plug';

    /**
     * Compare current plugins with a target Moodle code tree.
     *
     * @param string $currentroot Current Moodle root.
     * @param string $targetroot Target Moodle root.
     * @param int|string|null $targetbranch Target branch.
     * @return array
     */
    public static function compare(string $currentroot, string $targetroot, $targetbranch = null): array {
        $targetbranchint = upgrade_path::branch_to_int($targetbranch);
        $current = self::inventory($currentroot);
        $target = self::inventory($targetroot);
        $targetbycomponent = [];
        foreach ($target as $plugin) {
            $targetbycomponent[$plugin['component']] = $plugin;
        }

        $rows = [];
        foreach ($current as $plugin) {
            $targetplugin = $targetbycomponent[$plugin['component']] ?? null;
            $rows[] = self::classify($plugin, $targetplugin, $targetbranchint, $targetbycomponent);
        }

        usort($rows, static function (array $a, array $b): int {
            $weight = [
                'incompatible' => 0,
                'dependency_missing' => 1,
                'dependency_outdated' => 2,
                'missing' => 3,
                'dependency_review' => 4,
                'review' => 5,
                'core_change_review' => 6,
                'core_removed' => 7,
                'compatible' => 8,
            ];
            return ($weight[$a['compatibility']] ?? 9) <=> ($weight[$b['compatibility']] ?? 9)
                ?: strcmp($a['component'], $b['component']);
        });

        return $rows;
    }

    /**
     * Build inventory from a Moodle root.
     *
     * @param string $root Moodle root.
     * @return array
     */
    public static function inventory(string $root): array {
        $plugins = [];
        $standardcomponents = self::standard_components($root);
        foreach (self::plugin_types() as $type => $relative) {
            $directory = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (!is_dir($directory) || !is_readable($directory)) {
                continue;
            }
            $items = scandir($directory);
            if ($items === false) {
                continue;
            }
            foreach ($items as $item) {
                if ($item === '.' || $item === '..' || $item === 'CVS' || $item === 'tests') {
                    continue;
                }
                $path = $directory . DIRECTORY_SEPARATOR . $item;
                if (!is_dir($path) || is_link($path)) {
                    continue;
                }
                $info = self::read_plugin_version($path);
                if (empty($info['hasversionfile'])) {
                    continue;
                }
                $component = $info['component'] ?: $type . '_' . $item;
                $plugins[$component] = [
                    'type' => $type,
                    'name' => $item,
                    'component' => $component,
                    'path' => $path,
                    'version' => $info['version'],
                    'requires' => $info['requires'],
                    'release' => $info['release'],
                    'dependencies' => $info['dependencies'],
                    'isstandard' => isset($standardcomponents[$component]),
                ];
            }
        }
        return array_values($plugins);
    }

    /**
     * Persist report plugin rows.
     *
     * @param int $reportid Report ID.
     * @param array $rows Rows from compare().
     * @return void
     */
    public static function persist_for_report(int $reportid, array $rows): void {
        global $DB;

        if (!$DB->get_manager()->table_exists(self::TABLE)) {
            return;
        }

        $now = time();
        foreach ($rows as $row) {
            $record = (object)[
                'reportid' => $reportid,
                'component' => substr((string)$row['component'], 0, 100),
                'plugintype' => substr((string)$row['type'], 0, 40),
                'pluginname' => substr((string)$row['name'], 0, 100),
                'version' => substr((string)$row['version'], 0, 100),
                'releaseinfo' => substr((string)$row['release'], 0, 255),
                'requires' => substr((string)$row['requires'], 0, 100),
                'targetversion' => substr((string)$row['targetversion'], 0, 100),
                'compatibility' => substr((string)$row['compatibility'], 0, 40),
                'statuslabel' => substr((string)$row['status'], 0, 255),
                'dependencyjson' => self::json($row['dependencies'] ?? []),
                'evidence' => self::json($row),
                'timecreated' => $now,
            ];
            $DB->insert_record(self::TABLE, $record);
        }
    }

    /**
     * Return plugin rows for a report.
     *
     * @param int $reportid Report ID.
     * @return array
     */
    public static function rows_for_report(int $reportid): array {
        global $DB;

        if (!$DB->get_manager()->table_exists(self::TABLE)) {
            return [];
        }
        $records = $DB->get_records(self::TABLE, ['reportid' => $reportid], 'compatibility ASC, component ASC');
        $rows = [];
        foreach ($records as $record) {
            $rows[] = [
                'component' => format_string($record->component),
                'type' => s($record->plugintype),
                'name' => s($record->pluginname),
                'version' => s($record->version),
                'release' => s($record->releaseinfo),
                'requires' => s($record->requires),
                'targetversion' => s($record->targetversion),
                'compatibility' => s($record->compatibility),
                'status' => s($record->statuslabel),
                'statusclass' => self::status_class($record->compatibility),
            ];
        }
        return $rows;
    }

    /**
     * Classify one plugin.
     *
     * @param array $plugin Current plugin.
     * @param array|null $targetplugin Matching target plugin.
     * @param int $targetbranchint Target branch.
     * @return array
     */
    private static function classify(
        array $plugin,
        ?array $targetplugin,
        int $targetbranchint,
        array $targetbycomponent
    ): array {
        $metadata = $targetplugin ?? $plugin;
        $requires = (int)($metadata['requires'] ?? 0);
        $requiresbranch = self::version_to_approx_branch($requires);
        $dependencies = (array)($metadata['dependencies'] ?? []);

        $row = $plugin + [
            'source' => $plugin['path'] ?? '',
            'destination' => $targetplugin['path'] ?? '',
            'existsintarget' => $targetplugin !== null,
            'targetversion' => $targetplugin['version'] ?? '',
            'targetrelease' => $targetplugin['release'] ?? '',
            'requireswarning' => false,
            'compatibility' => 'review',
            'status' => '',
            'statusclass' => 'ua-badge-warn',
            'dependencies' => $dependencies,
            'dependencychecks' => [],
            'manualreview' => false,
            'autoclosed' => false,
            'officialremoval' => [],
        ];

        if ($targetplugin === null) {
            $removal = self::expected_core_removal((string)$plugin['component'], $targetbranchint);
            if ($removal !== null) {
                $row['compatibility'] = 'core_removed';
                $row['status'] = get_string('pluginofficiallyremoved', 'local_upgradeassistant', (object)[
                    'component' => $plugin['component'],
                    'version' => upgrade_path::label((int)$removal['since']),
                ]);
                $row['statusclass'] = 'ua-badge-ok';
                $row['autoclosed'] = true;
                $row['officialremoval'] = $removal;
                return $row;
            }

            if (!empty($plugin['isstandard'])) {
                $row['compatibility'] = 'core_change_review';
                $row['status'] = get_string('plugincorechangeinreview', 'local_upgradeassistant');
                $row['statusclass'] = 'ua-badge-info';
                $row['manualreview'] = true;
                return $row;
            }

            $row['compatibility'] = 'missing';
            $row['status'] = get_string('pluginmissingintarget', 'local_upgradeassistant');
            $row['statusclass'] = 'ua-badge-warn';
            $row['manualreview'] = true;
            return $row;
        }

        if ($targetbranchint > 0 && $requiresbranch > $targetbranchint) {
            $row['compatibility'] = 'incompatible';
            $row['requireswarning'] = true;
            $row['status'] = get_string('pluginrequiresfuturemoodle', 'local_upgradeassistant');
            $row['statusclass'] = 'ua-badge-danger';
            $row['manualreview'] = true;
            return $row;
        }

        if (!empty($dependencies)) {
            $dependencyresult = self::evaluate_dependencies($dependencies, $targetbycomponent);
            $row['dependencychecks'] = $dependencyresult['checks'];
            $row['manualreview'] = !empty($dependencyresult['needsmanualreview']);

            if (!empty($dependencyresult['missing'])) {
                $row['compatibility'] = 'dependency_missing';
                $row['status'] = get_string(
                    'plugindependenciesmissing',
                    'local_upgradeassistant',
                    implode(', ', $dependencyresult['missing'])
                );
                $row['statusclass'] = 'ua-badge-danger';
                $row['manualreview'] = true;
                return $row;
            }

            if (!empty($dependencyresult['outdated'])) {
                $row['compatibility'] = 'dependency_outdated';
                $row['status'] = get_string(
                    'plugindependenciesoutdated',
                    'local_upgradeassistant',
                    implode(', ', $dependencyresult['outdated'])
                );
                $row['statusclass'] = 'ua-badge-danger';
                $row['manualreview'] = true;
                return $row;
            }

            if (!empty($dependencyresult['unknown'])) {
                $row['compatibility'] = 'dependency_review';
                $row['status'] = get_string('plugindependenciesreview', 'local_upgradeassistant');
                $row['statusclass'] = 'ua-badge-info';
                $row['manualreview'] = true;
                return $row;
            }

            $row['compatibility'] = 'compatible';
            $row['status'] = get_string('plugindependenciesverified', 'local_upgradeassistant');
            $row['statusclass'] = 'ua-badge-ok';
            return $row;
        }

        $row['compatibility'] = 'compatible';
        $row['status'] = get_string('pluginpresentintarget', 'local_upgradeassistant');
        $row['statusclass'] = 'ua-badge-ok';
        return $row;
    }

    /**
     * Evaluate declared plugin dependencies against the selected target tree.
     *
     * @param array $dependencies Component => minimum version map.
     * @param array $targetbycomponent Target inventory keyed by component.
     * @return array
     */
    public static function evaluate_dependencies(array $dependencies, array $targetbycomponent): array {
        $result = [
            'checks' => [],
            'missing' => [],
            'outdated' => [],
            'unknown' => [],
            'needsmanualreview' => false,
        ];

        foreach ($dependencies as $component => $requiredversion) {
            $component = clean_param((string)$component, PARAM_COMPONENT);
            $requiredversion = (int)$requiredversion;
            $target = $targetbycomponent[$component] ?? null;
            $targetversion = $target !== null ? (int)($target['version'] ?? 0) : 0;
            $state = 'satisfied';

            if ($target === null) {
                $state = 'missing';
                $result['missing'][] = $component;
            } else if ($requiredversion > 0 && $targetversion <= 0) {
                $state = 'unknown';
                $result['unknown'][] = $component;
                $result['needsmanualreview'] = true;
            } else if ($requiredversion > 0 && $targetversion < $requiredversion) {
                $state = 'outdated';
                $result['outdated'][] = $component;
            }

            $result['checks'][] = [
                'component' => $component,
                'requiredversion' => $requiredversion,
                'targetversion' => $targetversion,
                'state' => $state,
            ];
        }

        return $result;
    }

    /**
     * Return official removals that are expected to be absent from the target code tree.
     *
     * These components were part of Moodle core in the source branches and are removed
     * by the official Moodle upgrade path. Their absence must not be treated as a custom
     * plugin omission or as a pre-upgrade blocker.
     *
     * @param string $component Plugin component.
     * @param int $targetbranchint Target branch.
     * @return array|null
     */
    public static function expected_core_removal(string $component, int $targetbranchint): ?array {
        if ($targetbranchint < 500) {
            return null;
        }

        $removedin500 = [
            'auth_mnet' => 'MDL-84307',
            'block_mnet_hosts' => 'MDL-84309',
            'enrol_mnet' => 'MDL-84310',
            'mnetservice_enrol' => 'MDL-84311',
        ];

        if (!isset($removedin500[$component])) {
            return null;
        }

        return [
            'since' => 500,
            'issue' => $removedin500[$component],
            'component' => $component,
        ];
    }

    /**
     * Return plugin type directories.
     *
     * @return array
     */
    private static function plugin_types(): array {
        return [
            'antivirus' => 'lib/antivirus',
            'assignfeedback' => 'mod/assign/feedback',
            'assignsubmission' => 'mod/assign/submission',
            'atto' => 'lib/editor/atto/plugins',
            'auth' => 'auth',
            'availability' => 'availability/condition',
            'block' => 'blocks',
            'booktool' => 'mod/book/tool',
            'cachelock' => 'cache/locks',
            'cachestore' => 'cache/stores',
            'calendartype' => 'calendar/type',
            'communication' => 'communication',
            'contenttype' => 'contentbank/contenttype',
            'coursereport' => 'course/report',
            'customfield' => 'customfield/field',
            'datafield' => 'mod/data/field',
            'dataformat' => 'dataformat',
            'datapreset' => 'mod/data/preset',
            'editor' => 'lib/editor',
            'enrol' => 'enrol',
            'factor' => 'admin/tool/mfa/factor',
            'fileconverter' => 'files/converter',
            'filter' => 'filter',
            'format' => 'course/format',
            'forumreport' => 'mod/forum/report',
            'gradeexport' => 'grade/export',
            'gradeimport' => 'grade/import',
            'gradereport' => 'grade/report',
            'gradingform' => 'grade/grading/form',
            'h5plib' => 'h5p/h5plib',
            'local' => 'local',
            'logstore' => 'admin/tool/log/store',
            'ltiservice' => 'mod/lti/service',
            'media' => 'media/player',
            'message' => 'message/output',
            'mlbackend' => 'lib/mlbackend',
            'mnetservice' => 'mnet/service',
            'mod' => 'mod',
            'paygw' => 'payment/gateway',
            'plagiarism' => 'plagiarism',
            'portfolio' => 'portfolio',
            'profilefield' => 'user/profile/field',
            'qbank' => 'question/bank',
            'qbehaviour' => 'question/behaviour',
            'qformat' => 'question/format',
            'qtype' => 'question/type',
            'quiz' => 'mod/quiz/report',
            'quizaccess' => 'mod/quiz/accessrule',
            'report' => 'report',
            'repository' => 'repository',
            'scormreport' => 'mod/scorm/report',
            'search' => 'search/engine',
            'theme' => 'theme',
            'tiny' => 'lib/editor/tiny/plugins',
            'tool' => 'admin/tool',
            'webservice' => 'webservice',
            'workshopallocation' => 'mod/workshop/allocation',
            'workshopeval' => 'mod/workshop/eval',
            'workshopform' => 'mod/workshop/form',
        ];
    }

    /**
     * Read the standard component list shipped with a Moodle code tree.
     *
     * @param string $root Moodle code root.
     * @return array Component map.
     */
    private static function standard_components(string $root): array {
        $path = $root . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'plugins.json';
        if (!is_readable($path)) {
            return [];
        }
        $content = file_get_contents($path);
        $data = $content !== false ? json_decode($content, true) : null;
        if (!is_array($data) || !isset($data['standard']) || !is_array($data['standard'])) {
            return [];
        }

        $components = [];
        foreach ($data['standard'] as $type => $names) {
            if (!is_array($names)) {
                continue;
            }
            foreach ($names as $name) {
                $components[$type . '_' . $name] = true;
            }
        }
        return $components;
    }

    /**
     * Read plugin metadata from version.php without including it.
     *
     * @param string $plugindir Plugin directory.
     * @return array
     */
    private static function read_plugin_version(string $plugindir): array {
        $versionfile = $plugindir . DIRECTORY_SEPARATOR . 'version.php';
        if (!is_readable($versionfile)) {
            return [
                'component' => '',
                'version' => '',
                'requires' => '',
                'release' => '',
                'dependencies' => [],
                'hasversionfile' => false,
            ];
        }

        $content = file_get_contents($versionfile);
        if ($content === false) {
            $content = '';
        }

        return [
            'component' => self::match_plugin_assignment($content, 'component'),
            'version' => self::match_plugin_assignment($content, 'version'),
            'requires' => self::match_plugin_assignment($content, 'requires'),
            'release' => self::match_plugin_assignment($content, 'release'),
            'dependencies' => self::match_dependencies($content),
            'hasversionfile' => true,
        ];
    }

    /**
     * Extract plugin assignment value.
     *
     * @param string $content PHP content.
     * @param string $name Property name.
     * @return string
     */
    private static function match_plugin_assignment(string $content, string $name): string {
        $pattern = '/\\$plugin->' . preg_quote($name, '/') . '\\s*=\\s*([\'\"]?)([^;\'\"]+)\\1\\s*;/';
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[2]);
        }
        return '';
    }

    /**
     * Approximate dependencies from version.php.
     *
     * @param string $content PHP content.
     * @return array
     */
    private static function match_dependencies(string $content): array {
        $pattern = '/\$plugin->dependencies\s*=\s*(?:array\s*\((.*?)\)|\[(.*?)\])\s*;/s';
        if (!preg_match($pattern, $content, $matches)) {
            return [];
        }

        $body = !empty($matches[1]) ? $matches[1] : ($matches[2] ?? '');
        preg_match_all(
            '/[\'\"]([a-z0-9_]+)[\'\"]\s*=>\s*(ANY_VERSION|[0-9]+)/i',
            $body,
            $pairs,
            PREG_SET_ORDER
        );
        $dependencies = [];
        foreach ($pairs as $pair) {
            $dependencies[$pair[1]] = strtoupper($pair[2]) === 'ANY_VERSION' ? 0 : (int)$pair[2];
        }
        return $dependencies;
    }

    /**
     * Approximate Moodle branch from version timestamp.
     *
     * @param int $requires Requires value.
     * @return int
     */
    private static function version_to_approx_branch(int $requires): int {
        if ($requires >= 2026042000) {
            return 502;
        }
        if ($requires >= 2025041400) {
            return 500;
        }
        if ($requires >= 2024100700) {
            return 405;
        }
        if ($requires >= 2024042200) {
            return 404;
        }
        if ($requires >= 2023100900) {
            return 403;
        }
        if ($requires >= 2023042400) {
            return 402;
        }
        if ($requires >= 2022112800) {
            return 401;
        }
        return 0;
    }

    /**
     * Return CSS class for compatibility.
     *
     * @param string $compatibility Compatibility.
     * @return string
     */
    private static function status_class(string $compatibility): string {
        if (in_array($compatibility, ['incompatible', 'dependency_missing', 'dependency_outdated'], true)) {
            return 'ua-badge-danger';
        }
        if (in_array($compatibility, ['compatible', 'core_removed'], true)) {
            return 'ua-badge-ok';
        }
        if (in_array($compatibility, ['dependency_review', 'core_change_review'], true)) {
            return 'ua-badge-info';
        }
        return 'ua-badge-warn';
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
