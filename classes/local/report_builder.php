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
 * Builds and persists Pro pre-upgrade reports.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class report_builder {
    /** Reports table. */
    private const REPORTS_TABLE = 'local_ua_reports';

    /** Items table. */
    private const ITEMS_TABLE = 'local_ua_items';


    /**
     * Create a persistent pre-upgrade report.
     *
     * @param array $targetinfo Selected target Moodle installation.
     * @param array|null $analysis Upgrade path analysis.
     * @param array $customplugins Plugin comparison rows.
     * @param array $wizardstate Current wizard state.
     * @return int Report ID.
     */
    public static function create_report(array $targetinfo, ?array $analysis, array $customplugins, array $wizardstate): int {
        global $CFG, $DB, $USER;

        $env = detector::environment();
        $findings = self::build_findings($env, $targetinfo, $analysis, $customplugins, $wizardstate);
        $risk = risk_assessor::assess($findings);
        $dbdescription = self::database_description($env['dbinfo']);
        $now = time();

        $summary = [
            'current' => [
                'release' => $env['release'],
                'branch' => $env['branch'],
                'path' => $CFG->dirroot,
            ],
            'target' => [
                'release' => $targetinfo['release'] ?? '',
                'branch' => $targetinfo['branch'] ?? '',
                'path' => $targetinfo['path'] ?? '',
                'haspublic' => !empty($targetinfo['haspublic']),
                'publicpath' => $targetinfo['publicpath'] ?? '',
                'configpath' => $targetinfo['configpath'] ?? '',
            ],
            'risk' => $risk,
            'plugins' => [
                'reviewcount' => count(array_filter($customplugins, static function(array $plugin): bool {
                    return !in_array(($plugin['compatibility'] ?? ''), ['compatible', 'core_removed'], true);
                })),
                'inventorycount' => count($customplugins),
            ],
            'rules' => rule_engine::get_rule($targetinfo['branch'] ?? '', $targetinfo),
            'serverrecommendations' => server_recommendation_engine::recommendations(server_recommendation_engine::detect_profile()),
            'analysis' => $analysis,
            'lifecycle' => lifecycle_manager::report_snapshot($env['branch'], $targetinfo['branch'] ?? ''),
            'theme' => $env['theme'] ?? '',
        ];

        $report = (object)[
            'uuid' => self::uuid(),
            'userid' => (int)($USER->id ?? 0),
            'currentrelease' => (string)($env['release'] ?? ''),
            'currentbranch' => (string)($env['branch'] ?? ''),
            'targetrelease' => (string)($targetinfo['release'] ?? ''),
            'targetbranch' => (string)($targetinfo['branch'] ?? ''),
            'targetpath' => (string)($targetinfo['path'] ?? ''),
            'phpversion' => PHP_VERSION,
            'dbtype' => (string)($env['dbtype'] ?? ''),
            'dbversion' => $dbdescription,
            'serverprofile' => server_recommendation_engine::profile_label(),
            'riskscore' => $risk['score'],
            'risklevel' => $risk['level'],
            'status' => 'generated',
            'summary' => self::json($summary),
            'timecreated' => $now,
            'timemodified' => $now,
        ];

        $transaction = $DB->start_delegated_transaction();
        $reportid = (int)$DB->insert_record(self::REPORTS_TABLE, $report);

        $sort = 10;
        foreach ($findings as $finding) {
            $record = (object)[
                'reportid' => $reportid,
                'category' => $finding['category'],
                'code' => $finding['code'],
                'title' => $finding['title'],
                'description' => $finding['description'],
                'severity' => $finding['severity'],
                'status' => $finding['status'] ?? 'open',
                'recommendation' => $finding['recommendation'] ?? '',
                'evidence' => self::json($finding['evidence'] ?? []),
                'sortorder' => $sort,
                'timecreated' => $now,
            ];
            $DB->insert_record(self::ITEMS_TABLE, $record);
            $sort += 10;
        }

        plugin_analyser::persist_for_report($reportid, $customplugins);
        checklist_manager::seed_for_report($reportid, $wizardstate);
        audit_logger::log($reportid, 'report_created', 'report', $reportid, null, $summary);
        $transaction->allow_commit();

        \local_upgradeassistant\event\report_created::create([
            'context' => \context_system::instance(),
            'objectid' => $reportid,
        ])->trigger();
        state::set_active_reportid($reportid);

        return $reportid;
    }


    /**
     * Delete all reports and dependent audit data generated by one user.
     *
     * This is used when the user explicitly restarts the assistant so a new
     * evaluation begins without inheriting report history from the previous run.
     * Reports created by other administrators are preserved.
     *
     * @param int $userid User ID.
     * @return int Number of deleted reports.
     */
    public static function delete_reports_for_user(int $userid): int {
        global $DB;

        if ($userid <= 0) {
            return 0;
        }

        $reports = $DB->get_records(self::REPORTS_TABLE, ['userid' => $userid], '', 'id');
        $reportids = array_map('intval', array_keys($reports));
        if (empty($reportids)) {
            return 0;
        }

        $transaction = $DB->start_delegated_transaction();
        foreach ([
            'local_ua_exports',
            'local_ua_plugins',
            'local_ua_audit',
            'local_ua_checklist',
            self::ITEMS_TABLE,
        ] as $table) {
            $DB->delete_records_list($table, 'reportid', $reportids);
        }
        $DB->delete_records_list(self::REPORTS_TABLE, 'id', $reportids);
        $transaction->allow_commit();

        return count($reportids);
    }

    /**
     * Build findings for a report.
     *
     * @param array $env Environment data.
     * @param array $targetinfo Target installation information.
     * @param array|null $analysis Upgrade path analysis.
     * @param array $customplugins Custom plugin rows.
     * @param array $wizardstate Current wizard state.
     * @return array
     */
    private static function build_findings(
        array $env,
        array $targetinfo,
        ?array $analysis,
        array $customplugins,
        array $wizardstate
    ): array {
        $findings = [];
        $currentbranch = upgrade_path::branch_to_int($env['branch'] ?? '');
        $targetbranch = upgrade_path::branch_to_int($targetinfo['branch'] ?? '');

        if ($analysis === null) {
            $findings[] = self::finding('path', 'path_unknown', 'high',
                get_string('findingpathunknown', 'local_upgradeassistant'),
                get_string('findingpathunknowndesc', 'local_upgradeassistant'),
                get_string('findingpathunknownrec', 'local_upgradeassistant'));
        } else if (($analysis['status'] ?? '') === 'error') {
            $findings[] = self::finding('path', 'path_blocked', 'critical',
                get_string('findingpathblocked', 'local_upgradeassistant'),
                $analysis['message'],
                get_string('findingpathblockedrec', 'local_upgradeassistant'));
        } else if (($analysis['status'] ?? '') === 'warning') {
            $pathrule = rule_engine::get_rule($targetbranch, $targetinfo);
            $pathrecommendation = empty($pathrule['minimumfrombranch'])
                ? get_string('findingpathunverifiedrec', 'local_upgradeassistant')
                : get_string('findingpathreviewrec', 'local_upgradeassistant');
            $findings[] = self::finding('path', 'path_review', 'medium',
                get_string('findingpathreview', 'local_upgradeassistant'),
                $analysis['message'],
                $pathrecommendation);
        } else {
            $findings[] = self::finding('path', 'path_ok', 'info',
                get_string('findingpathok', 'local_upgradeassistant'),
                $analysis['message'],
                get_string('findingpathokrec', 'local_upgradeassistant'), ['currentbranch' => $currentbranch, 'targetbranch' => $targetbranch]);
        }

        $findings = array_merge($findings, self::validation_findings($targetbranch, $env, $targetinfo));
        if (preg_match('/(?:alpha|beta|rc|dev|preview)/i', (string)($targetinfo['release'] ?? ''))) {
            $findings[] = self::finding('path', 'target_prerelease', 'medium',
                get_string('findingtargetprerelease', 'local_upgradeassistant'),
                get_string('findingtargetprereleasedesc', 'local_upgradeassistant'),
                get_string('findingtargetprereleaserec', 'local_upgradeassistant'));
        }
        if (!empty($targetinfo['haspublic'])) {
            $findings[] = self::finding('server', 'target_public_structure', 'info',
                get_string('findingtargetpublicstructure', 'local_upgradeassistant'),
                get_string('findingtargetpublicstructuredesc', 'local_upgradeassistant', $targetinfo['publicpath'] ?? ''),
                get_string('findingtargetpublicstructurerec', 'local_upgradeassistant'));
        }
        $findings = array_merge($findings, self::plugin_findings($customplugins));
        $findings = array_merge($findings, self::theme_findings($env));
        $findings = array_merge($findings, self::checklist_findings($wizardstate, $env));
        $findings = array_merge($findings, lifecycle_manager::findings($env['branch'] ?? '', $targetinfo['branch'] ?? ''));

        if (empty($customplugins)) {
            $findings[] = self::finding('plugins', 'plugins_no_missing', 'info',
                get_string('findingpluginsok', 'local_upgradeassistant'),
                get_string('findingpluginsokdesc', 'local_upgradeassistant'),
                get_string('findingpluginsokrec', 'local_upgradeassistant'));
        }

        return $findings;
    }

    /**
     * Build rule-based validation findings.
     *
     * @param int $targetbranch Target branch.
     * @param array $env Environment data.
     * @param array $targetinfo Selected target installation.
     * @return array
     */
    private static function validation_findings(int $targetbranch, array $env, array $targetinfo): array {
        $findings = [];
        foreach (requirements_validator::validate($targetbranch, $env, $targetinfo) as $check) {
            if ($check['status'] === 'pass') {
                continue;
            }
            $description = in_array($check['key'], ['rule_unknown', 'rule_partial'], true)
                ? $check['message']
                : get_string('validationfindingdesc', 'local_upgradeassistant', (object)[
                    'current' => $check['current'],
                    'required' => $check['required'],
                ]);
            $findings[] = self::finding('requirements', $check['key'], $check['severity'],
                $check['label'],
                $description,
                get_string('validationfindingrec', 'local_upgradeassistant'));
        }
        return $findings;
    }

    /**
     * Build theme readiness findings.
     *
     * @param array $env Environment data.
     * @return array
     */
    private static function theme_findings(array $env): array {
        $theme = (string)($env['theme'] ?? '');
        if ($theme === 'boost') {
            return [self::finding('theme', 'theme_boost_active', 'info',
                get_string('findingboostthemeok', 'local_upgradeassistant'),
                get_string('findingboostthemeokdesc', 'local_upgradeassistant'),
                get_string('findingboostthemeokrec', 'local_upgradeassistant'), ['theme' => $theme])];
        }

        return [self::finding('theme', 'theme_not_boost', 'medium',
            get_string('findingboostthemepending', 'local_upgradeassistant'),
            get_string('findingboostthemependingdesc', 'local_upgradeassistant', $theme ?: get_string('notdetected', 'local_upgradeassistant')),
            get_string('findingboostthemependingrec', 'local_upgradeassistant'), ['theme' => $theme])];
    }

    /**
     * Build plugin findings.
     *
     * @param array $customplugins Plugin rows.
     * @return array
     */
    private static function plugin_findings(array $customplugins): array {
        $findings = [];
        foreach ($customplugins as $plugin) {
            $compatibility = $plugin['compatibility'] ?? 'unknown';
            if ($compatibility === 'compatible') {
                continue;
            }

            $component = (string)($plugin['component'] ?? '');
            $code = 'plugin_review_' . clean_param($component, PARAM_ALPHANUMEXT);
            $evidence = $plugin;
            $evidence['reviewable'] = !empty($plugin['manualreview']);

            if ($compatibility === 'core_removed') {
                $removal = (array)($plugin['officialremoval'] ?? []);
                $issue = (string)($removal['issue'] ?? '');
                $finding = self::finding(
                    'plugins',
                    $code,
                    'info',
                    get_string('findingpluginofficialremoval', 'local_upgradeassistant', $component),
                    get_string('findingpluginofficialremovaldesc', 'local_upgradeassistant', (object)[
                        'component' => $component,
                        'target' => upgrade_path::label((int)($removal['since'] ?? 500)),
                        'issue' => $issue,
                    ]),
                    get_string('findingpluginofficialremovalrec', 'local_upgradeassistant'),
                    $evidence
                );
                $finding['status'] = 'closed';
                $findings[] = $finding;
                continue;
            }

            if ($compatibility === 'core_change_review') {
                $finding = self::finding(
                    'plugins',
                    $code,
                    'info',
                    get_string('findingplugincorechangereview', 'local_upgradeassistant', $component),
                    get_string('findingplugincorechangereviewdesc', 'local_upgradeassistant'),
                    get_string('findingplugincorechangereviewrec', 'local_upgradeassistant'),
                    $evidence
                );
                $finding['status'] = 'open';
                $finding['evidence']['reviewable'] = true;
                $findings[] = $finding;
                continue;
            }

            if ($compatibility === 'dependency_review') {
                $finding = self::finding(
                    'plugins',
                    $code,
                    'info',
                    get_string('findingplugindependencyreview', 'local_upgradeassistant', $component),
                    get_string('findingplugindependencyreviewdesc', 'local_upgradeassistant'),
                    get_string('findingplugindependencyreviewrec', 'local_upgradeassistant'),
                    $evidence
                );
                // This is an auditable review task, but it does not add risk by itself.
                $finding['status'] = 'open';
                $finding['evidence']['reviewable'] = true;
                $findings[] = $finding;
                continue;
            }

            if ($compatibility === 'dependency_missing') {
                $findings[] = self::finding(
                    'plugins',
                    $code,
                    'high',
                    get_string('findingplugindependencymissing', 'local_upgradeassistant', $component),
                    get_string('findingplugindependencymissingdesc', 'local_upgradeassistant', $plugin['status']),
                    get_string('findingplugindependencymissingrec', 'local_upgradeassistant'),
                    $evidence
                );
                continue;
            }

            if ($compatibility === 'dependency_outdated') {
                $findings[] = self::finding(
                    'plugins',
                    $code,
                    'high',
                    get_string('findingplugindependencyoutdated', 'local_upgradeassistant', $component),
                    get_string('findingplugindependencyoutdateddesc', 'local_upgradeassistant', $plugin['status']),
                    get_string('findingplugindependencyoutdatedrec', 'local_upgradeassistant'),
                    $evidence
                );
                continue;
            }

            $severity = $compatibility === 'incompatible' ? 'high' : 'medium';
            $finding = self::finding(
                'plugins',
                $code,
                $severity,
                get_string('findingpluginreview', 'local_upgradeassistant', $component),
                get_string('findingpluginreviewdesc', 'local_upgradeassistant', $plugin['status']),
                get_string('findingpluginreviewrec', 'local_upgradeassistant'),
                $evidence
            );
            $finding['evidence']['reviewable'] = true;
            $findings[] = $finding;
        }
        return $findings;
    }

    /**
     * Build checklist readiness findings.
     *
     * @param array $wizardstate Current wizard state.
     * @return array
     */
    private static function checklist_findings(array $wizardstate, array $env = []): array {
        $findings = [];
        $criticalsteps = ['backupdb', 'backupcode', 'backupdata'];
        foreach ($criticalsteps as $step) {
            if (empty($wizardstate['completed'][$step])) {
                $findings[] = self::finding('checklist', 'checklist_' . $step, 'high',
                    get_string('findingchecklistpending', 'local_upgradeassistant', get_string('check' . $step, 'local_upgradeassistant')),
                    get_string('findingchecklistpendingdesc', 'local_upgradeassistant'),
                    get_string('findingchecklistpendingrec', 'local_upgradeassistant'));
            }
        }

        if (empty($wizardstate['completed']['maintenance'])
                && !checklist_manager::is_maintenance_mode_active()) {
            $findings[] = self::finding('checklist', 'checklist_maintenance', 'medium',
                get_string('findingmaintenancepending', 'local_upgradeassistant'),
                get_string('findingmaintenancependingdesc', 'local_upgradeassistant'),
                get_string('findingmaintenancependingrec', 'local_upgradeassistant'));
        }

        $boostactive = (($env['theme'] ?? '') === 'boost');
        if (empty($wizardstate['completed']['boosttheme']) && !$boostactive) {
            $findings[] = self::finding('checklist', 'checklist_boosttheme', 'medium',
                get_string('findingboostthemecheckpending', 'local_upgradeassistant'),
                get_string('findingboostthemecheckpendingdesc', 'local_upgradeassistant'),
                get_string('findingboostthemecheckpendingrec', 'local_upgradeassistant'));
        }

        return $findings;
    }

    /**
     * Whether the current user may view sensitive diagnostics.
     *
     * @return bool
     */
    private static function can_view_sensitive(): bool {
        $context = \context_system::instance();
        return has_capability('local/upgradeassistant:viewsensitive', $context);
    }

    /**
     * Hide a sensitive diagnostic value when the user lacks permission.
     *
     * @param string|null $value Raw value.
     * @return string
     */
    private static function sensitive_value(?string $value): string {
        if (self::can_view_sensitive()) {
            return s((string)$value);
        }

        return get_string('sensitivehidden', 'local_upgradeassistant');
    }

    /**
     * Redact sensitive paths and diagnostics from free-text findings.
     *
     * @param string $text Text to redact.
     * @param object|null $report Report record with summary data.
     * @return string Redacted text.
     */
    private static function redact_sensitive_text(string $text, ?object $report = null): string {
        global $CFG;

        $redacted = get_string('redacted', 'local_upgradeassistant');
        $values = [
            (string)($CFG->dirroot ?? ''),
            (string)($CFG->dataroot ?? ''),
            (string)($CFG->wwwroot ?? ''),
        ];

        if ($report !== null) {
            $values[] = (string)($report->targetpath ?? '');
            $values[] = (string)($report->dbtype ?? '');
            $values[] = (string)($report->dbversion ?? '');
            $summary = json_decode($report->summary ?? '', true);
            if (is_array($summary)) {
                foreach (['current', 'target'] as $section) {
                    foreach (['path', 'publicpath', 'configpath'] as $key) {
                        if (!empty($summary[$section][$key])) {
                            $values[] = (string)$summary[$section][$key];
                        }
                    }
                }
            }
        }

        $values = array_filter(array_unique($values), static function(string $value): bool {
            return $value !== '' && strlen($value) > 2;
        });
        usort($values, static function(string $a, string $b): int {
            return strlen($b) <=> strlen($a);
        });

        foreach ($values as $value) {
            $text = str_replace($value, $redacted, $text);
        }

        $text = preg_replace('#[A-Za-z]:\\\\[^\s<>"\']+#', $redacted, $text) ?? $text;
        $text = preg_replace('#/(?:var|home|usr|srv|opt|mnt|xampp|Users|Applications)/[^\s<>"\']+#', $redacted, $text) ?? $text;

        return $text;
    }

    /**
     * Return latest report data for Mustache.
     *
     * @return array
     */
    public static function latest_report_for_template(): array {
        global $DB;

        $activereportid = state::get_active_reportid();
        if ($activereportid > 0 && $DB->record_exists(self::REPORTS_TABLE, ['id' => $activereportid])) {
            return self::report_for_template($activereportid);
        }

        $records = $DB->get_records(self::REPORTS_TABLE, null, 'timecreated DESC, id DESC', 'id', 0, 1);
        $report = reset($records);
        if (!$report) {
            return ['available' => false];
        }

        return self::report_for_template((int)$report->id);
    }

    /**
     * Return report data for Mustache.
     *
     * @param int $reportid Report ID.
     * @return array
     */
    public static function report_for_template(int $reportid): array {
        global $DB;

        checklist_manager::synchronise_environment_steps($reportid);
        $report = $DB->get_record(self::REPORTS_TABLE, ['id' => $reportid], '*', MUST_EXIST);
        self::synchronise_lifecycle_context($report);
        self::synchronise_plugin_context($report);
        $report = $DB->get_record(self::REPORTS_TABLE, ['id' => $reportid], '*', MUST_EXIST);
        $items = $DB->get_records(self::ITEMS_TABLE, ['reportid' => $reportid], 'sortorder ASC, id ASC');
        $user = $DB->get_record('user', ['id' => $report->userid], 'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename');

        $reviewaudits = $DB->get_records('local_ua_audit', [
            'reportid' => (int)$report->id,
            'action' => 'finding_reviewed',
        ], 'timecreated DESC, id DESC');
        $reviewauditbyfinding = [];
        $reviewerids = [];
        foreach ($reviewaudits as $audit) {
            if (!isset($reviewauditbyfinding[(int)$audit->targetid])) {
                $reviewauditbyfinding[(int)$audit->targetid] = $audit;
                if ((int)$audit->userid > 0) {
                    $reviewerids[(int)$audit->userid] = (int)$audit->userid;
                }
            }
        }
        $reviewers = empty($reviewerids) ? [] : $DB->get_records_list(
            'user',
            'id',
            array_values($reviewerids),
            '',
            'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename'
        );

        $rows = [];
        $canviewsensitive = self::can_view_sensitive();
        foreach ($items as $item) {
            $description = format_text($item->description, FORMAT_PLAIN);
            $recommendation = format_text($item->recommendation, FORMAT_PLAIN);
            $evidence = json_decode((string)$item->evidence, true);
            if (!is_array($evidence)) {
                $evidence = [];
            }

            $reviewaudit = $reviewauditbyfinding[(int)$item->id] ?? null;
            $reviewnote = $reviewaudit !== null ? (string)$reviewaudit->note : '';
            $reviewer = $reviewaudit !== null && isset($reviewers[(int)$reviewaudit->userid])
                ? $reviewers[(int)$reviewaudit->userid] : null;

            if (!$canviewsensitive) {
                $description = self::redact_sensitive_text($description, $report);
                $recommendation = self::redact_sensitive_text($recommendation, $report);
                $reviewnote = self::redact_sensitive_text($reviewnote, $report);
            }

            $isresolved = in_array($item->status, ['closed', 'resolved', 'reviewed'], true);
            $ismitigatedlifecycle = $item->code === 'lifecycle_current_unsupported' && $item->status === 'closed';
            $isofficialremoval = ($evidence['compatibility'] ?? '') === 'core_removed' && $item->status === 'closed';

            if ($item->status === 'reviewed') {
                $statuslabel = get_string('findingstatusreviewed', 'local_upgradeassistant');
            } else if ($ismitigatedlifecycle) {
                $statuslabel = get_string('findingstatusmitigated', 'local_upgradeassistant');
            } else if ($isofficialremoval) {
                $statuslabel = get_string('findingstatusofficialremoval', 'local_upgradeassistant');
            } else {
                $statuslabel = get_string('findingstatus' . $item->status, 'local_upgradeassistant');
            }

            $rows[] = [
                'id' => (int)$item->id,
                'reportid' => (int)$report->id,
                'category' => get_string('reportcategory' . $item->category, 'local_upgradeassistant'),
                'title' => format_string($item->title),
                'description' => $description,
                'severity' => get_string('severity' . $item->severity, 'local_upgradeassistant'),
                'severityclass' => $isresolved ? 'ua-badge-ok' : self::severity_class($item->severity),
                'status' => $statuslabel,
                'statusclass' => $isresolved ? 'ua-badge-ok' : 'ua-badge-warn',
                'recommendation' => $recommendation,
                'canreview' => $item->status === 'open'
                    && (($item->category === 'plugins' && !empty($evidence['reviewable']))
                        || in_array($item->category, ['path', 'requirements'], true)
                        || $item->code === 'lifecycle_target_future'),
                'reviewed' => $item->status === 'reviewed',
                'hasreviewnote' => $reviewnote !== '',
                'reviewnote' => $reviewnote,
                'reviewedby' => $reviewer ? fullname($reviewer) : '',
                'reviewedat' => $reviewaudit !== null ? userdate((int)$reviewaudit->timecreated) : '',
            ];
        }

        $pluginrows = plugin_analyser::rows_for_report((int)$report->id);

        return [
            'available' => true,
            'id' => $report->id,
            'uuid' => $report->uuid,
            'currentrelease' => format_string($report->currentrelease),
            'currentbranch' => format_string($report->currentbranch),
            'targetrelease' => format_string($report->targetrelease),
            'targetbranch' => format_string($report->targetbranch),
            'targetpath' => self::sensitive_value($report->targetpath),
            'phpversion' => s($report->phpversion),
            'dbtype' => self::sensitive_value($report->dbtype),
            'dbversion' => self::sensitive_value($report->dbversion),
            'serverprofile' => self::sensitive_value($report->serverprofile),
            'riskscore' => $report->riskscore,
            'risklevel' => get_string('risklevel' . $report->risklevel, 'local_upgradeassistant'),
            'riskclass' => self::risk_class($report->risklevel),
            'status' => s($report->status),
            'timecreated' => userdate($report->timecreated),
            'generatedby' => $user ? fullname($user) : '',
            'pdfurl' => '',
            'htmlurl' => '',
            'items' => array_values($rows),
            'hasitems' => !empty($rows),
            'checklist' => checklist_manager::get_template_rows((int)$report->id, !$canviewsensitive),
            'pluginrows' => $pluginrows,
            'haspluginrows' => !empty($pluginrows),
            'lifecycle' => lifecycle_manager::report_lifecycle_from_summary($report),
        ];
    }

    /**
     * Return recent report history rows.
     *
     * @param int $limit Number of rows.
     * @return array
     */
    public static function history_for_template(int $limit = 8): array {
        global $DB;

        $records = $DB->get_records(
            self::REPORTS_TABLE,
            null,
            'timecreated DESC, id DESC',
            'id, uuid, currentrelease, targetrelease, riskscore, risklevel, timecreated',
            0,
            $limit
        );
        $rows = [];
        foreach ($records as $report) {
            $rows[] = [
                'id' => $report->id,
                'uuid' => s($report->uuid),
                'currentrelease' => format_string($report->currentrelease),
                'targetrelease' => format_string($report->targetrelease),
                'riskscore' => (int)$report->riskscore,
                'risklevel' => get_string('risklevel' . $report->risklevel, 'local_upgradeassistant'),
                'riskclass' => self::risk_class($report->risklevel),
                'timecreated' => userdate($report->timecreated),
                'pdfurl' => '',
                'htmlurl' => '',
            ];
        }
        return $rows;
    }

    /**
     * Reconcile lifecycle findings in reports created before the contextual risk fix.
     *
     * @param object $report Report record.
     * @return void
     */
    private static function synchronise_lifecycle_context(object $report): void {
        global $DB;

        $desired = lifecycle_manager::findings((string)$report->currentbranch, (string)$report->targetbranch);
        $desiredbycode = [];
        foreach ($desired as $finding) {
            $desiredbycode[$finding['code']] = $finding;
        }

        $records = $DB->get_records(self::ITEMS_TABLE, [
            'reportid' => (int)$report->id,
            'category' => 'lifecycle',
        ]);
        $changed = false;
        foreach ($records as $record) {
            if (!isset($desiredbycode[$record->code])) {
                continue;
            }
            $expected = $desiredbycode[$record->code];
            $newvalues = [
                'title' => (string)$expected['title'],
                'description' => (string)$expected['description'],
                'severity' => (string)$expected['severity'],
                'status' => $record->status === 'reviewed'
                    ? 'reviewed' : (string)($expected['status'] ?? 'open'),
                'recommendation' => (string)($expected['recommendation'] ?? ''),
                'evidence' => self::json($expected['evidence'] ?? []),
            ];
            $needsupdate = false;
            foreach ($newvalues as $field => $value) {
                if ((string)$record->{$field} !== $value) {
                    $record->{$field} = $value;
                    $needsupdate = true;
                }
            }
            if ($needsupdate) {
                $DB->update_record(self::ITEMS_TABLE, $record);
                $changed = true;
            }
        }

        if ($changed) {
            self::refresh_score((int)$report->id);
            audit_logger::log((int)$report->id, 'lifecycle_context_reconciled', 'report',
                (int)$report->id, null, ['currentbranch' => $report->currentbranch, 'targetbranch' => $report->targetbranch]);
        }
    }

    /**
     * Reconcile plugin findings created by earlier plugin versions.
     *
     * @param object $report Report record.
     * @return void
     */
    private static function synchronise_plugin_context(object $report): void {
        global $DB;

        $records = $DB->get_records(self::ITEMS_TABLE, [
            'reportid' => (int)$report->id,
            'category' => 'plugins',
        ]);
        if (empty($records)) {
            return;
        }

        $targetbranch = upgrade_path::branch_to_int((string)$report->targetbranch);
        $changed = false;
        foreach ($records as $record) {
            $evidence = json_decode((string)$record->evidence, true);
            if (!is_array($evidence)) {
                $evidence = [];
            }
            $component = (string)($evidence['component'] ?? '');
            $compatibility = (string)($evidence['compatibility'] ?? '');
            $dependencies = isset($evidence['dependencies']) && is_array($evidence['dependencies'])
                ? $evidence['dependencies'] : [];

            $newvalues = [];
            $removal = $component !== ''
                ? plugin_analyser::expected_core_removal($component, $targetbranch) : null;
            if ($removal !== null) {
                $evidence['compatibility'] = 'core_removed';
                $evidence['officialremoval'] = $removal;
                $evidence['reviewable'] = false;
                $newvalues = [
                    'title' => get_string('findingpluginofficialremoval', 'local_upgradeassistant', $component),
                    'description' => get_string('findingpluginofficialremovaldesc', 'local_upgradeassistant', (object)[
                        'component' => $component,
                        'target' => upgrade_path::label((int)$removal['since']),
                        'issue' => (string)$removal['issue'],
                    ]),
                    'severity' => 'info',
                    'status' => 'closed',
                    'recommendation' => get_string('findingpluginofficialremovalrec', 'local_upgradeassistant'),
                    'evidence' => self::json($evidence),
                ];
                self::update_persisted_plugin_status(
                    (int)$report->id,
                    $component,
                    'core_removed',
                    get_string('pluginofficiallyremoved', 'local_upgradeassistant', (object)[
                        'component' => $component,
                        'version' => upgrade_path::label((int)$removal['since']),
                    ])
                );
            } else if ($compatibility === 'review' && !empty($dependencies)) {
                // Older reports treated every dependency declaration as risk. Convert
                // those entries to a zero-point auditable review until re-analysed.
                $evidence['compatibility'] = 'dependency_review';
                $evidence['reviewable'] = true;
                $newvalues = [
                    'title' => get_string('findingplugindependencyreview', 'local_upgradeassistant', $component),
                    'description' => get_string('findingplugindependencyreviewdesc', 'local_upgradeassistant'),
                    'severity' => 'info',
                    'status' => in_array($record->status, ['closed', 'resolved', 'reviewed'], true)
                        ? $record->status : 'open',
                    'recommendation' => get_string('findingplugindependencyreviewrec', 'local_upgradeassistant'),
                    'evidence' => self::json($evidence),
                ];
                self::update_persisted_plugin_status(
                    (int)$report->id,
                    $component,
                    'dependency_review',
                    get_string('plugindependenciesreview', 'local_upgradeassistant')
                );
            }

            if (empty($newvalues)) {
                continue;
            }
            $needsupdate = false;
            foreach ($newvalues as $field => $value) {
                if ((string)$record->{$field} !== (string)$value) {
                    $record->{$field} = $value;
                    $needsupdate = true;
                }
            }
            if ($needsupdate) {
                $DB->update_record(self::ITEMS_TABLE, $record);
                $changed = true;
            }
        }

        if ($changed) {
            self::refresh_score((int)$report->id);
            audit_logger::log((int)$report->id, 'plugin_context_reconciled', 'report',
                (int)$report->id, null, ['targetbranch' => $report->targetbranch]);
        }
    }

    /**
     * Update the persisted plugin matrix row when a finding is reconciled.
     *
     * @param int $reportid Report ID.
     * @param string $component Plugin component.
     * @param string $compatibility Compatibility state.
     * @param string $statuslabel Human-readable state.
     * @return void
     */
    private static function update_persisted_plugin_status(
        int $reportid,
        string $component,
        string $compatibility,
        string $statuslabel
    ): void {
        global $DB;

        $record = $DB->get_record('local_ua_plugins', [
            'reportid' => $reportid,
            'component' => $component,
        ]);
        if (!$record) {
            return;
        }
        $record->compatibility = substr($compatibility, 0, 40);
        $record->statuslabel = substr($statuslabel, 0, 255);
        $DB->update_record('local_ua_plugins', $record);
    }

    /**
     * Record an administrator's written review of a plugin finding.
     *
     * @param int $reportid Report ID.
     * @param int $findingid Finding ID.
     * @param string $note Written criterion or evidence.
     * @return void
     */
    public static function review_plugin_finding(int $reportid, int $findingid, string $note): void {
        global $DB;

        $note = trim($note);
        if ($note === '') {
            throw new \moodle_exception('reviewnoterequired', 'local_upgradeassistant');
        }

        $record = $DB->get_record(self::ITEMS_TABLE, [
            'id' => $findingid,
            'reportid' => $reportid,
        ], '*', MUST_EXIST);
        $evidence = json_decode((string)$record->evidence, true);
        if (!is_array($evidence)) {
            $evidence = [];
        }

        $reviewable = ($record->category === 'plugins' && !empty($evidence['reviewable']))
            || in_array($record->category, ['path', 'requirements'], true)
            || $record->code === 'lifecycle_target_future';
        if (!$reviewable || $record->status !== 'open') {
            throw new \moodle_exception('findingnotreviewable', 'local_upgradeassistant');
        }

        $transaction = $DB->start_delegated_transaction();
        $oldvalue = clone($record);
        $record->status = 'reviewed';
        $record->evidence = self::json($evidence);
        $DB->update_record(self::ITEMS_TABLE, $record);
        audit_logger::log($reportid, 'finding_reviewed', 'finding', (int)$record->id,
            $oldvalue, $record, $note);
        self::refresh_score($reportid);
        $transaction->allow_commit();
    }

    /**
     * Mark a checklist-related finding as resolved and recalculate the report score.
     *
     * @param int $reportid Report ID.
     * @param string $stepkey Checklist step key.
     * @param string $note Optional audit note.
     * @return void
     */
    public static function resolve_checklist_finding(int $reportid, string $stepkey, string $note = ''): void {
        self::resolve_finding($reportid, 'checklist_' . $stepkey, $note);
    }

    /**
     * Mark a finding as resolved.
     *
     * @param int $reportid Report ID.
     * @param string $code Finding code.
     * @param string $note Optional audit note.
     * @return void
     */
    public static function resolve_finding(int $reportid, string $code, string $note = ''): void {
        global $DB;

        $records = $DB->get_records(self::ITEMS_TABLE, [
            'reportid' => $reportid,
            'code' => $code,
        ]);
        foreach ($records as $record) {
            if ($record->status === 'closed') {
                continue;
            }
            $oldvalue = clone($record);
            $record->status = 'closed';
            $DB->update_record(self::ITEMS_TABLE, $record);
            audit_logger::log($reportid, 'finding_resolved', 'finding', (int)$record->id, $oldvalue, $record, $note);
        }
    }

    /**
     * Recalculate the persisted risk score for a report using only open findings.
     *
     * @param int $reportid Report ID.
     * @return array New risk data.
     */
    public static function refresh_score(int $reportid): array {
        global $DB;

        $report = $DB->get_record(self::REPORTS_TABLE, ['id' => $reportid], '*', MUST_EXIST);
        $items = $DB->get_records(self::ITEMS_TABLE, ['reportid' => $reportid], 'sortorder ASC, id ASC');
        $findings = [];
        foreach ($items as $item) {
            $findings[] = [
                'severity' => $item->severity,
                'status' => $item->status,
            ];
        }

        $risk = risk_assessor::assess($findings);
        $oldvalue = clone($report);
        $report->riskscore = $risk['score'];
        $report->risklevel = $risk['level'];
        $report->timemodified = time();
        $DB->update_record(self::REPORTS_TABLE, $report);
        audit_logger::log($reportid, 'risk_recalculated', 'report', $reportid, $oldvalue, $report);

        return $risk;
    }

    /**
     * Create a finding definition.
     *
     * @param string $category Category.
     * @param string $code Finding code.
     * @param string $severity Severity.
     * @param string $title Title.
     * @param string $description Description.
     * @param string $recommendation Recommendation.
     * @param array $evidence Evidence payload.
     * @return array
     */
    private static function finding(
        string $category,
        string $code,
        string $severity,
        string $title,
        string $description,
        string $recommendation,
        array $evidence = []
    ): array {
        return [
            'category' => $category,
            'code' => substr($code, 0, 100),
            'severity' => $severity,
            'status' => $severity === 'info' ? 'closed' : 'open',
            'title' => $title,
            'description' => $description,
            'recommendation' => $recommendation,
            'evidence' => $evidence,
        ];
    }

    /**
     * Return database info text.
     *
     * @param mixed $dbinfo Database info.
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
            return self::json($dbinfo);
        }
        return (string)$dbinfo;
    }

    /**
     * Normalise Moodle DB type.
     *
     * @param string $dbtype Moodle DB type.
     * @param string $description Database description.
     * @return string
     */
    private static function normalise_dbtype(string $dbtype, string $description): string {
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
     * Extract first semantic version from text.
     *
     * @param string $text Source text.
     * @return string
     */
    private static function extract_version(string $text): string {
        if (preg_match('/(\d+(?:\.\d+){0,3})/', $text, $matches)) {
            return $matches[1];
        }
        return '';
    }

    /**
     * Detect a simple server profile.
     *
     * @return string
     */
    private static function detect_server_profile(): string {
        return server_recommendation_engine::profile_label();
    }

    /**
     * Return severity CSS class.
     *
     * @param string $severity Severity.
     * @return string
     */
    private static function severity_class(string $severity): string {
        if ($severity === 'critical') {
            return 'ua-badge-danger';
        }
        if ($severity === 'high') {
            return 'ua-badge-danger';
        }
        if ($severity === 'medium') {
            return 'ua-badge-warn';
        }
        if ($severity === 'low') {
            return 'ua-badge-warn';
        }
        return 'ua-badge-ok';
    }

    /**
     * Return risk CSS class.
     *
     * @param string $level Risk level.
     * @return string
     */
    private static function risk_class(string $level): string {
        if ($level === 'critical') {
            return 'ua-risk-critical';
        }
        if ($level === 'high') {
            return 'ua-risk-high';
        }
        if ($level === 'medium') {
            return 'ua-risk-medium';
        }
        return 'ua-risk-low';
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

    /**
     * Generate UUID v4.
     *
     * @return string
     */
    private static function uuid(): string {
        $data = random_bytes(16);
        $data[6] = chr((ord($data[6]) & 0x0f) | 0x40);
        $data[8] = chr((ord($data[8]) & 0x3f) | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
