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
 * Moodle lifecycle intelligence based on official Moodle HQ release support data.
 *
 * The manager uses a cached snapshot from Moodle Developer Resources and falls
 * back to a bundled dataset so the assistant and Pro reports continue working
 * when the production server has no outbound internet access.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lifecycle_manager {
    /** Official Moodle release support page. */
    public const SOURCE_URL = 'https://moodledev.io/general/releases';

    /** Seconds before the cached official source is considered stale. */
    private const REFRESH_AFTER = 604800;

    /** Config key used to persist the last official lifecycle snapshot. */
    private const CONFIG_KEY = 'lifecycledata';

    /**
     * Return lifecycle data for dashboard/report usage.
     *
     * @param bool $refresh Whether to force a remote refresh.
     * @return array
     */
    public static function get_dataset(bool $refresh = false): array {
        $stored = self::stored_dataset();

        if ($refresh) {
            $remote = self::fetch_remote_dataset();
            if (!empty($remote['versions'])) {
                set_config(self::CONFIG_KEY, self::json($remote), 'local_upgradeassistant');
                return self::normalise_dataset($remote);
            }
        }

        if (!empty($stored['versions'])) {
            return self::normalise_dataset($stored);
        }

        return self::fallback_dataset();
    }

    /**
     * Force a remote lifecycle refresh.
     *
     * @return array
     */
    public static function refresh_dataset(): array {
        $remote = self::fetch_remote_dataset();
        if (!empty($remote['versions'])) {
            set_config(self::CONFIG_KEY, self::json($remote), 'local_upgradeassistant');
            return self::normalise_dataset($remote);
        }

        return self::get_dataset(false);
    }

    /**
     * Return dashboard template data.
     *
     * @param string $currentbranch Current Moodle branch.
     * @param string $targetbranch Optional target branch.
     * @return array
     */
    public static function template_data(string $currentbranch, string $targetbranch = ''): array {
        $dataset = self::get_dataset(false);
        $current = self::version_for_branch($dataset, $currentbranch);
        $target = $targetbranch !== '' ? self::version_for_branch($dataset, $targetbranch) : null;
        $recommendation = self::recommendation($current, $target, $dataset);

        return [
            'available' => !empty($dataset['versions']),
            'sourceurl' => $dataset['sourceurl'],
            'source' => $dataset['source'],
            'lastsync' => !empty($dataset['fetchedat']) ? userdate((int)$dataset['fetchedat']) : get_string('notavailable', 'local_upgradeassistant'),
            'usingfallback' => !empty($dataset['usingfallback']),
            'usingfallbacklabel' => !empty($dataset['usingfallback']) ? get_string('lifecyclefallbackmode', 'local_upgradeassistant') : '',
            'current' => $current ? self::version_to_template($current) : self::empty_version(),
            'target' => $target ? self::version_to_template($target) : self::empty_version(false),
            'hastarget' => $target !== null,
            'recommendation' => $recommendation,
            'rows' => self::timeline_rows($dataset),
        ];
    }

    /**
     * Return lifecycle snapshot for report persistence.
     *
     * @param string $currentbranch Current branch.
     * @param string $targetbranch Target branch.
     * @return array
     */
    public static function report_snapshot(string $currentbranch, string $targetbranch): array {
        $dataset = self::get_dataset(false);
        $current = self::version_for_branch($dataset, $currentbranch);
        $target = self::version_for_branch($dataset, $targetbranch);

        return [
            'sourceurl' => $dataset['sourceurl'],
            'source' => $dataset['source'],
            'fetchedat' => $dataset['fetchedat'],
            'usingfallback' => !empty($dataset['usingfallback']),
            'current' => $current,
            'target' => $target,
            'recommendation' => self::recommendation($current, $target, $dataset),
            'versions' => $dataset['versions'],
            'reportgeneratedat' => time(),
        ];
    }

    /**
     * Build lifecycle findings to include in Pro risk reports.
     *
     * @param string $currentbranch Current branch.
     * @param string $targetbranch Target branch.
     * @return array
     */
    public static function findings(string $currentbranch, string $targetbranch): array {
        $dataset = self::get_dataset(false);
        $current = self::version_for_branch($dataset, $currentbranch);
        $target = self::version_for_branch($dataset, $targetbranch);
        $findings = [];

        if ($current === null) {
            $findings[] = self::finding('lifecycle_current_unknown', 'medium',
                get_string('findinglifecyclecurrentunknown', 'local_upgradeassistant'),
                get_string('findinglifecyclecurrentunknowndesc', 'local_upgradeassistant', $currentbranch),
                get_string('findinglifecycleunknownrec', 'local_upgradeassistant'));
        } else if ($current['statuskey'] === 'unsupported') {
            if (self::target_mitigates_unsupported_current($current, $target)) {
                $findings[] = self::finding('lifecycle_current_unsupported', 'info',
                    get_string('findinglifecyclecurrentunsupportedmitigated', 'local_upgradeassistant'),
                    get_string('findinglifecyclecurrentunsupportedmitigateddesc', 'local_upgradeassistant', (object)[
                        'current' => $current['label'],
                        'target' => $target['label'],
                    ]),
                    get_string('findinglifecyclecurrentunsupportedmitigatedrec', 'local_upgradeassistant'));
            } else {
                $findings[] = self::finding('lifecycle_current_unsupported', 'high',
                    get_string('findinglifecyclecurrentunsupported', 'local_upgradeassistant'),
                    get_string('findinglifecyclecurrentunsupporteddesc', 'local_upgradeassistant', $current['label']),
                    get_string('findinglifecyclecurrentunsupportedrec', 'local_upgradeassistant'));
            }
        } else if ($current['statuskey'] === 'security') {
            $findings[] = self::finding('lifecycle_current_security', 'info',
                get_string('findinglifecyclecurrentsecurity', 'local_upgradeassistant'),
                get_string('findinglifecyclecurrentsecuritydesc', 'local_upgradeassistant', (object)[
                    'version' => $current['label'],
                    'date' => self::format_date($current['securityend']),
                ]),
                get_string('findinglifecyclecurrentsecurityrec', 'local_upgradeassistant'));
        }

        if ($target === null) {
            $findings[] = self::finding('lifecycle_target_unknown', 'medium',
                get_string('findinglifecycletargetunknown', 'local_upgradeassistant'),
                get_string('findinglifecycletargetunknowndesc', 'local_upgradeassistant', $targetbranch),
                get_string('findinglifecycleunknownrec', 'local_upgradeassistant'));
        } else if ($target['statuskey'] === 'unsupported') {
            $findings[] = self::finding('lifecycle_target_unsupported', 'high',
                get_string('findinglifecycletargetunsupported', 'local_upgradeassistant'),
                get_string('findinglifecycletargetunsupporteddesc', 'local_upgradeassistant', $target['label']),
                get_string('findinglifecycletargetunsupportedrec', 'local_upgradeassistant'));
        } else if ($target['statuskey'] === 'future') {
            $findings[] = self::finding('lifecycle_target_future', 'high',
                get_string('findinglifecycletargetfuture', 'local_upgradeassistant'),
                get_string('findinglifecycletargetfuturedesc', 'local_upgradeassistant', (object)[
                    'version' => $target['label'],
                    'date' => self::format_date($target['releasedate']),
                ]),
                get_string('findinglifecycletargetfuturerec', 'local_upgradeassistant'));
        } else if (empty($target['lts'])) {
            $findings[] = self::finding('lifecycle_target_non_lts', 'low',
                get_string('findinglifecycletargetnonlts', 'local_upgradeassistant'),
                get_string('findinglifecycletargetnonltsdesc', 'local_upgradeassistant', $target['label']),
                get_string('findinglifecycletargetnonltsrec', 'local_upgradeassistant'));
        } else {
            $findings[] = self::finding('lifecycle_target_lts', 'info',
                get_string('findinglifecycletargetlts', 'local_upgradeassistant'),
                get_string('findinglifecycletargetltsdesc', 'local_upgradeassistant', (object)[
                    'version' => $target['label'],
                    'date' => self::format_date($target['securityend']),
                ]),
                get_string('findinglifecycletargetltsrec', 'local_upgradeassistant'));
        }

        return $findings;
    }

    /**
     * Decide whether the selected destination already mitigates an unsupported source branch.
     *
     * An unsupported source is the reason for running the upgrade, not a preparation task that
     * can be completed beforehand. It only remains an open risk when the selected destination is
     * missing, older, future, or also unsupported.
     *
     * @param array $current Current lifecycle row.
     * @param array|null $target Target lifecycle row.
     * @return bool
     */
    private static function target_mitigates_unsupported_current(array $current, ?array $target): bool {
        if ($target === null || !in_array($target['statuskey'] ?? '', ['general', 'security'], true)) {
            return false;
        }

        return (int)($target['branch'] ?? 0) > (int)($current['branch'] ?? 0);
    }

    /**
     * Return formatted data from a persisted report summary.
     *
     * @param object $report Report record.
     * @return array
     */
    public static function report_lifecycle_from_summary(object $report): array {
        $summary = json_decode((string)($report->summary ?? ''), true);
        if (!is_array($summary) || empty($summary['lifecycle'])) {
            return self::template_data((string)$report->currentbranch, (string)$report->targetbranch);
        }

        $lifecycle = $summary['lifecycle'];
        $dataset = [
            'sourceurl' => $lifecycle['sourceurl'] ?? self::SOURCE_URL,
            'source' => $lifecycle['source'] ?? 'Moodle HQ',
            'fetchedat' => (int)($lifecycle['fetchedat'] ?? 0),
            'usingfallback' => !empty($lifecycle['usingfallback']),
            'versions' => $lifecycle['versions'] ?? [],
        ];
        $dataset = self::normalise_dataset($dataset);

        $current = !empty($lifecycle['current']) && is_array($lifecycle['current']) ? self::normalise_version($lifecycle['current']) : null;
        $target = !empty($lifecycle['target']) && is_array($lifecycle['target']) ? self::normalise_version($lifecycle['target']) : null;

        return [
            'available' => !empty($dataset['versions']),
            'sourceurl' => $dataset['sourceurl'],
            'source' => $dataset['source'],
            'lastsync' => !empty($dataset['fetchedat']) ? userdate((int)$dataset['fetchedat']) : get_string('notavailable', 'local_upgradeassistant'),
            'usingfallback' => !empty($dataset['usingfallback']),
            'usingfallbacklabel' => !empty($dataset['usingfallback']) ? get_string('lifecyclefallbackmode', 'local_upgradeassistant') : '',
            'current' => $current ? self::version_to_template($current) : self::empty_version(),
            'target' => $target ? self::version_to_template($target) : self::empty_version(false),
            'hastarget' => $target !== null,
            'recommendation' => $lifecycle['recommendation'] ?? self::recommendation($current, $target, $dataset),
            'rows' => self::timeline_rows($dataset),
        ];
    }

    /**
     * Get persisted dataset from plugin config.
     *
     * @return array
     */
    private static function stored_dataset(): array {
        $raw = (string)get_config('local_upgradeassistant', self::CONFIG_KEY);
        if ($raw === '') {
            return [];
        }
        $data = json_decode($raw, true);
        return is_array($data) ? self::normalise_dataset($data) : [];
    }

    /**
     * Fetch and parse the Moodle HQ releases page.
     *
     * @return array
     */
    private static function fetch_remote_dataset(): array {
        global $CFG;

        if (!class_exists('curl')) {
            require_once($CFG->libdir . '/filelib.php');
        }

        try {
            $curl = new \curl();
            $options = [];
            if (defined('CURLOPT_CONNECTTIMEOUT')) {
                $options[CURLOPT_CONNECTTIMEOUT] = 6;
            }
            if (defined('CURLOPT_TIMEOUT')) {
                $options[CURLOPT_TIMEOUT] = 12;
            }
            if (defined('CURLOPT_USERAGENT')) {
                $options[CURLOPT_USERAGENT] = 'Smart Upgrade Assistant Moodle lifecycle sync';
            }
            if (!empty($options)) {
                $curl->setopt($options);
            }
            $html = $curl->get(self::SOURCE_URL);
            $info = $curl->get_info();
            $status = (int)($info['http_code'] ?? 0);
            if ($html === false || $html === '' || $status >= 400) {
                return [];
            }

            $versions = self::parse_versions_from_html((string)$html);
            if (empty($versions)) {
                return [];
            }

            return self::normalise_dataset([
                'sourceurl' => self::SOURCE_URL,
                'source' => 'Moodle HQ / Moodle Developer Resources',
                'fetchedat' => time(),
                'usingfallback' => false,
                'versions' => $versions,
            ]);
        } catch (\Throwable $e) {
            return [];
        }
    }

    /**
     * Parse support rows from the official Moodle releases HTML.
     *
     * @param string $html Source HTML.
     * @return array
     */
    private static function parse_versions_from_html(string $html): array {
        if (!class_exists('DOMDocument')) {
            return [];
        }

        $dom = new \DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $dom->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        $versions = [];
        foreach ($dom->getElementsByTagName('tr') as $row) {
            $cells = [];
            foreach ($row->childNodes as $cell) {
                if (!in_array($cell->nodeName, ['td', 'th'], true)) {
                    continue;
                }
                $text = preg_replace('/\s+/', ' ', trim($cell->textContent));
                $cells[] = $text ?? '';
            }
            if (count($cells) < 5 || !preg_match('/^(\d+)\.(\d+)/', $cells[0], $matches)) {
                continue;
            }

            $releasedate = self::parse_date($cells[2]);
            $generalend = self::parse_date($cells[3]);
            $securityend = self::parse_date($cells[4]);
            if ($releasedate === '' || $generalend === '' || $securityend === '') {
                continue;
            }

            $major = (int)$matches[1];
            $minor = (int)$matches[2];
            $versions[] = [
                'branch' => (string)(($major * 100) + $minor),
                'version' => $major . '.' . $minor,
                'label' => 'Moodle ' . $major . '.' . $minor . (stripos($cells[0], 'LTS') !== false ? ' LTS' : ''),
                'lts' => stripos($cells[0], 'LTS') !== false,
                'sourcestatus' => $cells[1],
                'releasedate' => $releasedate,
                'generalend' => $generalend,
                'securityend' => $securityend,
            ];
        }

        return $versions;
    }

    /**
     * Return fallback lifecycle dataset.
     *
     * @return array
     */
    private static function fallback_dataset(): array {
        return self::normalise_dataset([
            'sourceurl' => self::SOURCE_URL,
            'source' => 'Bundled fallback based on Moodle HQ release support data',
            'fetchedat' => 0,
            'usingfallback' => true,
            'versions' => [
                ['branch' => '401', 'version' => '4.1', 'label' => 'Moodle 4.1 LTS', 'lts' => true, 'sourcestatus' => 'Unsupported', 'releasedate' => '2022-11-28', 'generalend' => '2023-12-11', 'securityend' => '2025-12-08'],
                ['branch' => '404', 'version' => '4.4', 'label' => 'Moodle 4.4', 'lts' => false, 'sourcestatus' => 'Unsupported', 'releasedate' => '2024-04-22', 'generalend' => '2025-04-21', 'securityend' => '2025-12-08'],
                ['branch' => '405', 'version' => '4.5', 'label' => 'Moodle 4.5 LTS', 'lts' => true, 'sourcestatus' => 'Current security', 'releasedate' => '2024-10-07', 'generalend' => '2025-10-06', 'securityend' => '2027-10-04'],
                ['branch' => '500', 'version' => '5.0', 'label' => 'Moodle 5.0', 'lts' => false, 'sourcestatus' => 'Current security', 'releasedate' => '2025-04-14', 'generalend' => '2026-04-20', 'securityend' => '2026-10-05'],
                ['branch' => '501', 'version' => '5.1', 'label' => 'Moodle 5.1', 'lts' => false, 'sourcestatus' => 'Current stable', 'releasedate' => '2025-10-06', 'generalend' => '2026-10-05', 'securityend' => '2027-04-19'],
                ['branch' => '502', 'version' => '5.2', 'label' => 'Moodle 5.2', 'lts' => false, 'sourcestatus' => 'Current stable', 'releasedate' => '2026-04-20', 'generalend' => '2027-04-19', 'securityend' => '2027-10-04'],
                ['branch' => '503', 'version' => '5.3', 'label' => 'Moodle 5.3 LTS', 'lts' => true, 'sourcestatus' => 'Future release', 'releasedate' => '2026-10-05', 'generalend' => '2027-10-04', 'securityend' => '2029-10-01'],
            ],
        ]);
    }

    /**
     * Normalise complete dataset.
     *
     * @param array $dataset Dataset.
     * @return array
     */
    private static function normalise_dataset(array $dataset): array {
        $versions = [];
        foreach (($dataset['versions'] ?? []) as $version) {
            if (!is_array($version)) {
                continue;
            }
            $normalised = self::normalise_version($version);
            if ($normalised !== null) {
                $versions[] = $normalised;
            }
        }
        usort($versions, static function(array $a, array $b): int {
            return (int)$a['branch'] <=> (int)$b['branch'];
        });

        return [
            'sourceurl' => $dataset['sourceurl'] ?? self::SOURCE_URL,
            'source' => $dataset['source'] ?? 'Moodle HQ / Moodle Developer Resources',
            'fetchedat' => (int)($dataset['fetchedat'] ?? 0),
            'usingfallback' => !empty($dataset['usingfallback']),
            'versions' => $versions,
        ];
    }

    /**
     * Normalise one release branch row.
     *
     * @param array $version Version row.
     * @return array|null
     */
    private static function normalise_version(array $version): ?array {
        $branch = (string)($version['branch'] ?? '');
        if ($branch === '' && !empty($version['version'])) {
            $branch = self::branch_from_version((string)$version['version']);
        }
        if ($branch === '') {
            return null;
        }
        $label = (string)($version['label'] ?? 'Moodle ' . self::version_from_branch($branch));
        $normalised = [
            'branch' => $branch,
            'version' => (string)($version['version'] ?? self::version_from_branch($branch)),
            'label' => $label,
            'lts' => !empty($version['lts']) || stripos($label, 'LTS') !== false,
            'sourcestatus' => (string)($version['sourcestatus'] ?? ''),
            'releasedate' => (string)($version['releasedate'] ?? ''),
            'generalend' => (string)($version['generalend'] ?? ''),
            'securityend' => (string)($version['securityend'] ?? ''),
        ];
        $status = self::computed_status($normalised);
        $normalised['statuskey'] = $status['key'];
        $normalised['statuslabel'] = $status['label'];
        $normalised['statusclass'] = $status['class'];

        return $normalised;
    }

    /**
     * Convert one lifecycle row to Mustache-safe template data.
     *
     * @param array $version Version row.
     * @return array
     */
    private static function version_to_template(array $version): array {
        return [
            'available' => true,
            'branch' => s($version['branch']),
            'version' => s($version['version']),
            'label' => s($version['label']),
            'lts' => !empty($version['lts']),
            'ltslabel' => !empty($version['lts']) ? get_string('lifecyclelts', 'local_upgradeassistant') : '',
            'sourcestatus' => s($version['sourcestatus']),
            'releasedate' => self::format_date($version['releasedate']),
            'generalend' => self::format_date($version['generalend']),
            'securityend' => self::format_date($version['securityend']),
            'statuskey' => s($version['statuskey']),
            'statuslabel' => s($version['statuslabel']),
            'statusclass' => s($version['statusclass']),
        ];
    }

    /**
     * Return an empty version row for templates.
     *
     * @param bool $available Available flag.
     * @return array
     */
    private static function empty_version(bool $available = true): array {
        return [
            'available' => $available,
            'branch' => '',
            'version' => '',
            'label' => get_string('notavailable', 'local_upgradeassistant'),
            'lts' => false,
            'ltslabel' => '',
            'sourcestatus' => '',
            'releasedate' => '',
            'generalend' => '',
            'securityend' => '',
            'statuskey' => 'unknown',
            'statuslabel' => get_string('notavailable', 'local_upgradeassistant'),
            'statusclass' => 'ua-badge-warn',
        ];
    }

    /**
     * Build timeline rows.
     *
     * @param array $dataset Lifecycle dataset.
     * @return array
     */
    private static function timeline_rows(array $dataset): array {
        $versions = $dataset['versions'] ?? [];
        if (empty($versions)) {
            return [];
        }

        $min = null;
        $max = null;
        foreach ($versions as $version) {
            foreach (['releasedate', 'securityend'] as $field) {
                $ts = strtotime($version[$field] ?? '');
                if ($ts === false) {
                    continue;
                }
                $min = $min === null ? $ts : min($min, $ts);
                $max = $max === null ? $ts : max($max, $ts);
            }
        }
        if ($min === null || $max === null || $max <= $min) {
            return [];
        }

        $range = max(1, $max - $min);
        $rows = [];
        foreach ($versions as $version) {
            $release = strtotime($version['releasedate']);
            $general = strtotime($version['generalend']);
            $security = strtotime($version['securityend']);
            if ($release === false || $general === false || $security === false) {
                continue;
            }

            $generalleft = max(0, (($release - $min) / $range) * 100);
            $generalwidth = max(1, (($general - $release) / $range) * 100);
            $securityleft = max(0, (($general - $min) / $range) * 100);
            $securitywidth = max(1, (($security - $general) / $range) * 100);

            $row = self::version_to_template($version);
            $row['generalstyle'] = 'left:' . round($generalleft, 2) . '%;width:' . round($generalwidth, 2) . '%;';
            $row['securitystyle'] = 'left:' . round($securityleft, 2) . '%;width:' . round($securitywidth, 2) . '%;';
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Find lifecycle row by Moodle branch.
     *
     * @param array $dataset Dataset.
     * @param string $branch Moodle branch.
     * @return array|null
     */
    private static function version_for_branch(array $dataset, string $branch): ?array {
        $branch = self::normalise_branch($branch);
        foreach (($dataset['versions'] ?? []) as $version) {
            if ((string)$version['branch'] === $branch) {
                return $version;
            }
        }
        return null;
    }

    /**
     * Build lifecycle recommendation text.
     *
     * @param array|null $current Current version.
     * @param array|null $target Target version.
     * @param array $dataset Dataset.
     * @return string
     */
    private static function recommendation(?array $current, ?array $target, array $dataset): string {
        $supportedlts = self::latest_supported_lts($dataset);
        $futurelts = self::next_future_lts($dataset);

        if ($current === null) {
            return get_string('lifecyclerecommendunknown', 'local_upgradeassistant');
        }
        if ($current['statuskey'] === 'unsupported') {
            $name = $supportedlts['label'] ?? get_string('lifecyclelts', 'local_upgradeassistant');
            return get_string('lifecyclerecommendunsupported', 'local_upgradeassistant', $name);
        }
        if ($target !== null && $target['statuskey'] === 'future') {
            return get_string('lifecyclerecommendfuturetarget', 'local_upgradeassistant', (object)[
                'target' => $target['label'],
                'date' => self::format_date($target['releasedate']),
            ]);
        }
        if ($target !== null && empty($target['lts']) && !empty($supportedlts)) {
            return get_string('lifecyclerecommendnonlts', 'local_upgradeassistant', (object)[
                'target' => $target['label'],
                'lts' => $supportedlts['label'],
            ]);
        }
        if (!empty($current['lts']) && $current['statuskey'] === 'security') {
            $future = $futurelts ? $futurelts['label'] : get_string('lifecyclefuturelts', 'local_upgradeassistant');
            return get_string('lifecyclerecommendltssecurity', 'local_upgradeassistant', (object)[
                'current' => $current['label'],
                'date' => self::format_date($current['securityend']),
                'future' => $future,
            ]);
        }
        if (!empty($current['lts'])) {
            return get_string('lifecyclerecommendltsok', 'local_upgradeassistant', $current['label']);
        }

        $name = $supportedlts['label'] ?? get_string('lifecyclelts', 'local_upgradeassistant');
        return get_string('lifecyclerecommendstable', 'local_upgradeassistant', $name);
    }

    /**
     * Compute current support status.
     *
     * @param array $version Version row.
     * @return array
     */
    private static function computed_status(array $version): array {
        $now = time();
        $release = strtotime($version['releasedate'] ?? '');
        $general = strtotime($version['generalend'] ?? '');
        $security = strtotime($version['securityend'] ?? '');

        if ($release !== false && $now < $release) {
            return ['key' => 'future', 'label' => get_string('lifecyclestatusfuture', 'local_upgradeassistant'), 'class' => 'ua-badge-warn'];
        }
        if ($general !== false && $now <= $general) {
            return ['key' => 'general', 'label' => get_string('lifecyclestatusgeneral', 'local_upgradeassistant'), 'class' => 'ua-badge-ok'];
        }
        if ($security !== false && $now <= $security) {
            return ['key' => 'security', 'label' => get_string('lifecyclestatussecurity', 'local_upgradeassistant'), 'class' => 'ua-badge'];
        }

        return ['key' => 'unsupported', 'label' => get_string('lifecyclestatusunsupported', 'local_upgradeassistant'), 'class' => 'ua-badge-danger'];
    }

    /**
     * Find the latest already released LTS with active security support.
     *
     * @param array $dataset Dataset.
     * @return array|null
     */
    private static function latest_supported_lts(array $dataset): ?array {
        $winner = null;
        foreach (($dataset['versions'] ?? []) as $version) {
            if (empty($version['lts']) || !in_array($version['statuskey'], ['general', 'security'], true)) {
                continue;
            }
            if ($winner === null || (int)$version['branch'] > (int)$winner['branch']) {
                $winner = $version;
            }
        }
        return $winner;
    }

    /**
     * Find the next future LTS branch.
     *
     * @param array $dataset Dataset.
     * @return array|null
     */
    private static function next_future_lts(array $dataset): ?array {
        foreach (($dataset['versions'] ?? []) as $version) {
            if (!empty($version['lts']) && $version['statuskey'] === 'future') {
                return $version;
            }
        }
        return null;
    }

    /**
     * Build one lifecycle finding.
     *
     * @param string $code Finding code.
     * @param string $severity Severity.
     * @param string $title Title.
     * @param string $description Description.
     * @param string $recommendation Recommendation.
     * @return array
     */
    private static function finding(
        string $code,
        string $severity,
        string $title,
        string $description,
        string $recommendation
    ): array {
        return [
            'category' => 'lifecycle',
            'code' => $code,
            'severity' => $severity,
            'status' => $severity === 'info' ? 'closed' : 'open',
            'title' => $title,
            'description' => $description,
            'recommendation' => $recommendation,
            'evidence' => ['source' => self::SOURCE_URL],
        ];
    }

    /**
     * Normalise a Moodle branch value.
     *
     * @param string $branch Branch.
     * @return string
     */
    private static function normalise_branch(string $branch): string {
        $branch = trim($branch);
        if (preg_match('/^(\d+)\.(\d+)/', $branch, $matches)) {
            return (string)(((int)$matches[1] * 100) + (int)$matches[2]);
        }
        return $branch;
    }

    /**
     * Convert version text to Moodle branch.
     *
     * @param string $version Version.
     * @return string
     */
    private static function branch_from_version(string $version): string {
        if (preg_match('/^(\d+)\.(\d+)/', $version, $matches)) {
            return (string)(((int)$matches[1] * 100) + (int)$matches[2]);
        }
        return '';
    }

    /**
     * Convert a Moodle branch to user-facing version.
     *
     * @param string $branch Branch.
     * @return string
     */
    private static function version_from_branch(string $branch): string {
        $branch = self::normalise_branch($branch);
        $branchint = (int)$branch;
        if ($branchint < 100) {
            return $branch;
        }
        return floor($branchint / 100) . '.' . ($branchint % 100);
    }

    /**
     * Parse an English Moodle date into Y-m-d.
     *
     * @param string $value Date text.
     * @return string
     */
    private static function parse_date(string $value): string {
        $value = trim(preg_replace('/\s+/', ' ', $value));
        $time = strtotime($value);
        if ($time === false) {
            return '';
        }
        return gmdate('Y-m-d', $time);
    }

    /**
     * Format date for the current user.
     *
     * @param string $date Date in Y-m-d.
     * @return string
     */
    private static function format_date(string $date): string {
        $time = strtotime($date);
        return $time === false ? get_string('notavailable', 'local_upgradeassistant') : userdate($time, get_string('strftimedatefullshort'));
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
