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
 * Detects Moodle installations and version information.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class detector {
    /** Maximum number of directories visited during scan. */
    private const MAX_VISITED_DIRECTORIES = 350;

    /**
     * Return reasonable roots that are safe to scan.
     *
     * @param string $currentdirroot Current Moodle dirroot.
     * @return array
     */
    public static function default_scan_roots(string $currentdirroot): array {
        $roots = [];
        $currentdirroot = realpath($currentdirroot) ?: $currentdirroot;
        $roots[] = dirname($currentdirroot);
        $roots[] = $currentdirroot;

        if (!empty($_SERVER['DOCUMENT_ROOT'])) {
            $docroot = realpath($_SERVER['DOCUMENT_ROOT']);
            if ($docroot !== false) {
                $roots[] = $docroot;
                $roots[] = dirname($docroot);
            }
        }

        $clean = [];
        foreach ($roots as $root) {
            $real = realpath($root);
            if ($real !== false && is_dir($real)) {
                $clean[$real] = $real;
            }
        }

        return array_values($clean);
    }

    /**
     * Check whether a path is within allowed scan roots.
     *
     * @param string $path Path to validate.
     * @param array $allowedroots Allowed root directories.
     * @return bool
     */
    public static function is_allowed_path(string $path, array $allowedroots): bool {
        $real = realpath($path);
        if ($real === false) {
            return false;
        }

        foreach ($allowedroots as $root) {
            $rootreal = realpath($root);
            if ($rootreal === false) {
                continue;
            }
            if ($real === $rootreal || strpos($real, $rootreal . DIRECTORY_SEPARATOR) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scan for Moodle installations up to a small depth to avoid heavy traversal.
     *
     * @param string $root Root directory.
     * @param int $maxdepth Max traversal depth.
     * @param int $limit Max installations returned.
     * @return array
     */
    public static function scan(string $root, int $maxdepth = 2, int $limit = 80): array {
        $root = realpath($root);
        if ($root === false || !is_dir($root) || !is_readable($root)) {
            return [];
        }

        $found = [];
        $visited = 0;
        self::scan_recursive($root, 0, $maxdepth, $limit, $found, $visited);

        usort($found, static function(array $a, array $b): int {
            return strnatcasecmp($a['path'], $b['path']);
        });

        return $found;
    }

    /**
     * Recursive scanner.
     *
     * @param string $path Current path.
     * @param int $depth Current depth.
     * @param int $maxdepth Maximum depth.
     * @param int $limit Maximum found installations.
     * @param array $found Found installations.
     * @param int $visited Number of visited directories.
     * @return void
     */
    private static function scan_recursive(
        string $path,
        int $depth,
        int $maxdepth,
        int $limit,
        array &$found,
        int &$visited
    ): void {
        if (count($found) >= $limit || $visited >= self::MAX_VISITED_DIRECTORIES) {
            return;
        }

        $visited++;
        $info = self::read_moodle_version($path);
        if ($info !== null) {
            $found[$info['path']] = $info;
            return; // Avoid scanning inside a full Moodle tree.
        }

        if ($depth >= $maxdepth || !is_readable($path)) {
            return;
        }

        $items = scandir($path);
        if ($items === false) {
            return;
        }

        $skipnames = ['.', '..', '.git', 'node_modules', 'vendor', 'cache', 'localcache', 'temp', 'trashdir', 'sessions'];

        foreach ($items as $item) {
            if (in_array($item, $skipnames, true)) {
                continue;
            }
            $child = $path . DIRECTORY_SEPARATOR . $item;
            if (!is_dir($child) || is_link($child) || !is_readable($child)) {
                continue;
            }
            self::scan_recursive($child, $depth + 1, $maxdepth, $limit, $found, $visited);
        }
    }

    /**
     * Read Moodle version information from a directory without including PHP.
     *
     * @param string $path Candidate Moodle root.
     * @return array|null
     */
    public static function read_moodle_version(string $path): ?array {
        $path = realpath($path) ?: $path;
        $approot = $path;
        $versionroot = $path;
        $haspublic = false;
        $publicpath = '';
        $configpath = $path . DIRECTORY_SEPARATOR . 'config.php';

        $publicbasename = strtolower(self::portable_basename($path)) === 'public';
        if ($publicbasename) {
            $parent = dirname($path);
            $parentversionfile = $parent . DIRECTORY_SEPARATOR . 'version.php';
            $publicversionfile = $path . DIRECTORY_SEPARATOR . 'version.php';
            $parentconfigpath = $parent . DIRECTORY_SEPARATOR . 'config.php';
            $publicconfigpath = $path . DIRECTORY_SEPARATOR . 'config.php';

            $approot = $parent;
            $haspublic = true;
            $publicpath = $path;
            $configpath = is_file($parentconfigpath) ? $parentconfigpath : $publicconfigpath;

            if (is_file($parentversionfile)) {
                $versionroot = $parent;
            } else if (is_file($publicversionfile)) {
                $versionroot = $path;
            }
        } else {
            $childpublic = $path . DIRECTORY_SEPARATOR . 'public';
            $rootversionfile = $path . DIRECTORY_SEPARATOR . 'version.php';
            $publicversionfile = $childpublic . DIRECTORY_SEPARATOR . 'version.php';

            if (is_dir($childpublic)) {
                $haspublic = true;
                $publicpath = $childpublic;

                if (!is_file($rootversionfile) && is_file($publicversionfile)) {
                    $versionroot = $childpublic;
                }
            }
        }

        $versionfile = $versionroot . DIRECTORY_SEPARATOR . 'version.php';
        if (!is_file($versionfile) || !is_readable($versionfile)) {
            return null;
        }

        $content = file_get_contents($versionfile);
        if ($content === false) {
            return null;
        }

        $hasrelease = strpos($content, '$release') !== false;
        $hasbranch = strpos($content, '$branch') !== false;
        $hasversion = strpos($content, '$version') !== false;

        if (!$hasrelease && !$hasbranch && !$hasversion) {
            return null;
        }

        $release = self::match_php_assignment($content, 'release') ?: get_string('notdetected', 'local_upgradeassistant');
        $branch = self::match_php_assignment($content, 'branch') ?: self::branch_from_release($release);
        $version = self::match_php_assignment($content, 'version') ?: '';

        if ($branch === '' && $version === '') {
            return null;
        }

        return [
            'path' => $approot,
            'folder' => basename($approot),
            'release' => $release,
            'branch' => $branch,
            'branchlabel' => self::branch_label($branch, $release),
            'version' => $version,
            'haspublic' => $haspublic,
            'publicpath' => $publicpath,
            'approot' => $approot,
            'configpath' => $configpath,
            'versionfile' => $versionfile,
        ];
    }

    /**
     * Read Moodle plugin version information from a plugin directory.
     *
     * @param string $path Plugin directory.
     * @return array
     */
    public static function read_plugin_version(string $path): array {
        $versionfile = $path . DIRECTORY_SEPARATOR . 'version.php';
        if (!is_file($versionfile) || !is_readable($versionfile)) {
            return [
                'component' => '',
                'version' => '',
                'requires' => '',
                'release' => '',
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
            'hasversionfile' => true,
        ];
    }

    /**
     * Portable basename that handles Windows paths during tests and local XAMPP usage.
     *
     * @param string $path Path to inspect.
     * @return string Last path segment.
     */
    private static function portable_basename(string $path): string {
        return basename(str_replace('\\', '/', rtrim($path, "\\/")));
    }

    /**
     * Extract core variable assignment value from a PHP file.
     *
     * @param string $content PHP file content.
     * @param string $name Variable name.
     * @return string
     */
    private static function match_php_assignment(string $content, string $name): string {
        $pattern = '/\\$' . preg_quote($name, '/') . '\\s*=\\s*([\'\"]?)([^;\'\"]+)\\1\\s*;/';
        if (preg_match($pattern, $content, $matches)) {
            return trim($matches[2]);
        }
        return '';
    }

    /**
     * Extract plugin assignment value from $plugin->property.
     *
     * @param string $content PHP file content.
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
     * Infer branch from release text.
     *
     * @param string $release Release text.
     * @return string
     */
    public static function branch_from_release(string $release): string {
        if (preg_match('/(\\d+)\\.(\\d+)/', $release, $matches)) {
            return $matches[1] . str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        }
        return '';
    }

    /**
     * Human label for a branch.
     *
     * @param string $branch Branch number.
     * @param string $release Release text.
     * @return string
     */
    public static function branch_label(string $branch, string $release = ''): string {
        if (preg_match('/^(\\d)(\\d{2})$/', $branch, $matches)) {
            return $matches[1] . '.' . ((int)$matches[2]);
        }
        if (preg_match('/(\\d+\\.\\d+)/', $release, $matches)) {
            return $matches[1];
        }
        return $branch ?: get_string('notdetected', 'local_upgradeassistant');
    }

    /**
     * Common system information.
     *
     * @return array
     */
    public static function environment(): array {
        global $CFG, $DB;

        try {
            $dbinfo = $DB->get_server_info();
        } catch (\Throwable $exception) {
            $dbinfo = ['description' => get_string('notavailable', 'local_upgradeassistant')];
        }

        $current = self::read_moodle_version($CFG->dirroot) ?: [];

        $theme = (string)get_config('core', 'theme');
        if ($theme === '') {
            $theme = (string)($CFG->theme ?? 'boost');
        }

        return [
            'dirroot' => $CFG->dirroot,
            'dataroot' => $CFG->dataroot,
            'wwwroot' => $CFG->wwwroot,
            'release' => $CFG->release ?? ($current['release'] ?? get_string('notdetected', 'local_upgradeassistant')),
            'branch' => $CFG->branch ?? ($current['branch'] ?? ''),
            'branchlabel' => self::branch_label($CFG->branch ?? ($current['branch'] ?? ''), $CFG->release ?? ''),
            'version' => $CFG->version ?? ($current['version'] ?? ''),
            'phpversion' => PHP_VERSION,
            'dbtype' => $CFG->dbtype ?? get_string('notdetected', 'local_upgradeassistant'),
            'dbinfo' => $dbinfo,
            'theme' => $theme,
            'haspublic' => !empty($current['haspublic'])
                || is_dir($CFG->dirroot . DIRECTORY_SEPARATOR . 'public'),
        ];
    }

    /**
     * Detect likely custom plugins by comparing current and target plugin directories.
     *
     * @param string $currentroot Current Moodle root.
     * @param string $targetroot Target Moodle root.
     * @param string|int|null $targetbranch Target branch.
     * @return array
     */
    public static function detect_custom_plugins(string $currentroot, string $targetroot, $targetbranch = null): array {
        $types = self::plugin_types();
        $plugins = [];
        $targetbranchint = upgrade_path::branch_to_int($targetbranch);

        foreach ($types as $type => $relative) {
            $currentdir = $currentroot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            $targetdir = $targetroot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relative);
            if (!is_dir($currentdir) || !is_readable($currentdir)) {
                continue;
            }

            $items = scandir($currentdir);
            if ($items === false) {
                continue;
            }

            foreach ($items as $item) {
                if ($item === '.' || $item === '..' || $item === 'CVS' || $item === 'tests') {
                    continue;
                }

                $source = $currentdir . DIRECTORY_SEPARATOR . $item;
                if (!is_dir($source) || is_link($source)) {
                    continue;
                }

                $sourceinfo = self::read_plugin_version($source);
                if ($sourceinfo['hasversionfile'] === false) {
                    continue;
                }

                $dest = $targetdir . DIRECTORY_SEPARATOR . $item;
                $destinfo = is_dir($dest) ? self::read_plugin_version($dest) : null;
                $requires = (int)($sourceinfo['requires'] ?: 0);
                $requiresbranch = self::version_to_approx_branch($requires);
                $requireswarning = $targetbranchint > 0 && $requiresbranch > 0 && $requiresbranch > $targetbranchint;

                if (!is_dir($dest) || $requireswarning) {
                    $plugins[] = [
                        'type' => $type,
                        'name' => $item,
                        'component' => $sourceinfo['component'] ?: $type . '_' . $item,
                        'source' => $source,
                        'destination' => $dest,
                        'version' => $sourceinfo['version'],
                        'requires' => $sourceinfo['requires'],
                        'release' => $sourceinfo['release'],
                        'existsintarget' => is_dir($dest),
                        'requireswarning' => $requireswarning,
                        'status' => !is_dir($dest)
                            ? get_string('pluginmissingintarget', 'local_upgradeassistant')
                            : get_string('pluginrequiresreview', 'local_upgradeassistant'),
                        'statusclass' => !is_dir($dest) ? 'ua-badge-warn' : 'ua-badge-danger',
                        'targetversion' => $destinfo['version'] ?? '',
                    ];
                }
            }
        }

        return $plugins;
    }

    /**
     * Return plugin type relative directories.
     *
     * @return array
     */
    private static function plugin_types(): array {
        return [
            'local' => 'local',
            'mod' => 'mod',
            'block' => 'blocks',
            'theme' => 'theme',
            'auth' => 'auth',
            'enrol' => 'enrol',
            'qtype' => 'question/type',
            'report' => 'report',
            'tool' => 'admin/tool',
            'format' => 'course/format',
            'availability' => 'availability/condition',
            'assignfeedback' => 'mod/assign/feedback',
            'assignsubmission' => 'mod/assign/submission',
        ];
    }

    /**
     * Approximate Moodle branch from a version.php timestamp.
     *
     * @param int $requires Moodle version timestamp.
     * @return int
     */
    private static function version_to_approx_branch(int $requires): int {
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
}
