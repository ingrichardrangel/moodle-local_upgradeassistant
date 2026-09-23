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
 * Handles state-changing assistant actions.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class action_controller {
    /**
     * Execute a posted action and redirect back to the requested view.
     *
     * @param string $action Action name.
     * @param \context_system $context System context.
     * @param \moodle_url $baseurl Assistant base URL.
     * @param array $allowedroots Allowed filesystem roots.
     * @param array $params Sanitised action parameters.
     * @return void
     */
    public static function handle(
        string $action,
        \context_system $context,
        \moodle_url $baseurl,
        array $allowedroots,
        array $params
    ): void {
        global $CFG, $USER;

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            throw new \moodle_exception('invalidrequest', 'error');
        }
        require_sesskey();
        self::require_capabilities($action, $context);

        $returnurl = self::return_url(
            $baseurl,
            (string)($params['returnview'] ?? 'wizard'),
            (int)($params['returnstep'] ?? 1)
        );

        switch ($action) {
            case 'reset':
                $deletedreports = report_builder::delete_reports_for_user((int)($USER->id ?? 0));
                state::reset();
                self::success(
                    self::return_url($baseurl, 'wizard', 1),
                    get_string('statecleared', 'local_upgradeassistant', $deletedreports)
                );
                break;

            case 'maintenanceon':
                set_config('maintenance_enabled', 1);
                \local_upgradeassistant\event\maintenance_mode_enabled::create(['context' => $context])->trigger();
                state::complete_step('maintenance');
                checklist_manager::complete_latest_step_by_key(
                    'maintenance',
                    get_string('auditedfromwizard', 'local_upgradeassistant')
                );
                self::success($returnurl, get_string('maintenancemodeon', 'local_upgradeassistant'));
                break;

            case 'maintenanceoff':
                set_config('maintenance_enabled', 0);
                \local_upgradeassistant\event\maintenance_mode_disabled::create(['context' => $context])->trigger();
                self::success($returnurl, get_string('maintenancemodeoff', 'local_upgradeassistant'));
                break;

            case 'switchtoboosttheme':
                set_config('theme', 'boost');
                theme_reset_all_caches();
                \local_upgradeassistant\event\theme_switched_to_boost::create(['context' => $context])->trigger();
                state::complete_step('boosttheme');
                checklist_manager::complete_latest_step_by_key(
                    'boosttheme',
                    get_string('boostthemeswitchednote', 'local_upgradeassistant')
                );
                self::resolve_theme_finding(get_string('boostthemeswitchednote', 'local_upgradeassistant'));
                self::success($returnurl, get_string('boostthemeswitched', 'local_upgradeassistant'));
                break;

            case 'confirmboosttheme':
                $currenttheme = (string)get_config('core', 'theme');
                if ($currenttheme === '') {
                    $currenttheme = (string)($CFG->theme ?? 'boost');
                }
                if ($currenttheme !== 'boost') {
                    self::error(
                        $returnurl,
                        get_string('boostthemenotactive', 'local_upgradeassistant', $currenttheme)
                    );
                }
                state::complete_step('boosttheme');
                checklist_manager::complete_latest_step_by_key(
                    'boosttheme',
                    get_string('boostthemeverifiednote', 'local_upgradeassistant')
                );
                self::resolve_theme_finding(get_string('boostthemeverifiednote', 'local_upgradeassistant'));
                self::success($returnurl, get_string('boostthemeverified', 'local_upgradeassistant'));
                break;

            case 'purgecaches':
                purge_all_caches();
                state::complete_step('purgecaches');
                checklist_manager::complete_latest_step_by_key(
                    'purgecaches',
                    get_string('auditedfromwizard', 'local_upgradeassistant')
                );
                self::success($returnurl, get_string('cachespurged', 'local_upgradeassistant'));
                break;

            case 'completestep':
                $stepid = (string)($params['stepid'] ?? '');
                if ($stepid === '') {
                    throw new \moodle_exception('invalidstep', 'local_upgradeassistant');
                }
                state::complete_step($stepid);
                checklist_manager::complete_latest_step_by_key(
                    $stepid,
                    get_string('auditedfromwizard', 'local_upgradeassistant')
                );
                self::success($returnurl, get_string('stepcompleted', 'local_upgradeassistant'));
                break;

            case 'generatereport':
                $currentstate = state::get();
                if (
                    empty($currentstate['targetpath'])
                    || !detector::is_allowed_path($currentstate['targetpath'], $allowedroots)
                ) {
                    self::error(
                        self::return_url($baseurl, 'wizard', 2),
                        get_string('selecttargetfirst', 'local_upgradeassistant')
                    );
                }
                $targetinfo = detector::read_moodle_version($currentstate['targetpath']);
                if ($targetinfo === null) {
                    self::error(
                        self::return_url($baseurl, 'wizard', 2),
                        get_string('targetnotfound', 'local_upgradeassistant')
                    );
                }
                $env = detector::environment();
                $analysis = upgrade_path::analyze($env['branch'], $targetinfo['branch'], $targetinfo, $env['release']);
                $targetcoderoot = !empty($targetinfo['haspublic']) && !empty($targetinfo['publicpath'])
                    ? $targetinfo['publicpath'] : $targetinfo['path'];
                $plugins = plugin_analyser::compare($CFG->dirroot, $targetcoderoot, $targetinfo['branch']);
                $reportid = report_builder::create_report($targetinfo, $analysis, $plugins, $currentstate);
                self::success(
                    self::return_url($baseurl, 'wizard', 4),
                    get_string('reportgenerated', 'local_upgradeassistant', $reportid)
                );
                break;

            case 'reviewfinding':
                $findingid = (int)($params['findingid'] ?? 0);
                $reportid = (int)($params['reportid'] ?? 0);
                $auditnote = trim((string)($params['auditnote'] ?? ''));
                if ($findingid <= 0 || $reportid <= 0) {
                    throw new \moodle_exception('invalidfinding', 'local_upgradeassistant');
                }
                if ($auditnote === '') {
                    if (!empty($params['ajaxreview'])) {
                        self::review_response(false, get_string('reviewnoterequired', 'local_upgradeassistant'));
                    }
                    self::error(
                        self::return_url($baseurl, 'reports', 1, $reportid),
                        get_string('reviewnoterequired', 'local_upgradeassistant')
                    );
                }
                report_builder::review_plugin_finding($reportid, $findingid, $auditnote);
                if (!empty($params['ajaxreview'])) {
                    self::review_response(true, get_string('findingreviewed', 'local_upgradeassistant'));
                }
                self::success(
                    self::return_url($baseurl, 'reports', 1, $reportid),
                    get_string('findingreviewed', 'local_upgradeassistant')
                );
                break;

            case 'completeauditstep':
                $checklistid = (int)($params['checklistid'] ?? 0);
                $reportid = (int)($params['reportid'] ?? 0);
                if ($checklistid <= 0 || $reportid <= 0) {
                    throw new \moodle_exception('invalidstep', 'local_upgradeassistant');
                }
                checklist_manager::complete_step(
                    $checklistid,
                    (string)($params['auditnote'] ?? ''),
                    $reportid
                );
                self::success(
                    self::return_url($baseurl, 'reports', 1, $reportid),
                    get_string('auditstepcompleted', 'local_upgradeassistant')
                );
                break;

            case 'settarget':
                $targetpath = (string)($params['targetpath'] ?? '');
                if ($targetpath === '' || !detector::is_allowed_path($targetpath, $allowedroots)) {
                    self::error(
                        self::return_url($baseurl, 'wizard', 2),
                        get_string('accessdeniedpath', 'local_upgradeassistant')
                    );
                }
                $info = detector::read_moodle_version($targetpath);
                if ($info === null) {
                    self::error(
                        self::return_url($baseurl, 'wizard', 2),
                        get_string('targetnotfound', 'local_upgradeassistant')
                    );
                }
                $canonicaltargetpath = realpath((string)($info['approot'] ?? $info['path'] ?? $targetpath));
                if (
                    $canonicaltargetpath === false
                    || !detector::is_allowed_path($canonicaltargetpath, $allowedroots)
                ) {
                    self::error(
                        self::return_url($baseurl, 'wizard', 2),
                        get_string('accessdeniedpath', 'local_upgradeassistant')
                    );
                }
                state::set_target($canonicaltargetpath, $info);
                state::complete_step('confirmtarget');
                \local_upgradeassistant\event\target_selected::create([
                    'context' => $context,
                    'other' => ['branch' => $info['branch'] ?? ''],
                ])->trigger();
                self::success(
                    self::return_url($baseurl, 'wizard', 3),
                    get_string('targetselected', 'local_upgradeassistant')
                );
                break;

            case 'setscanpath':
            case 'rescan':
                $scanpath = (string)($params['scanpath'] ?? '');
                if ($scanpath === '' || !detector::is_allowed_path($scanpath, $allowedroots)) {
                    self::error(
                        self::return_url($baseurl, 'wizard', 2),
                        get_string('accessdeniedpath', 'local_upgradeassistant')
                    );
                }
                $realpath = realpath($scanpath) ?: $scanpath;
                state::set_scanpath($realpath);
                $cache = \cache::make('local_upgradeassistant', 'scanresults');
                $cache->delete(sha1($realpath));
                self::success(
                    self::return_url($baseurl, 'wizard', 2),
                    get_string('scanrefreshed', 'local_upgradeassistant')
                );
                break;

            case 'synclifecycle':
                if (get_config('local_upgradeassistant', 'enablelifecyclesync') === '0') {
                    redirect(
                        $returnurl,
                        get_string('lifecyclesyncdisabled', 'local_upgradeassistant'),
                        null,
                        \core\output\notification::NOTIFY_WARNING
                    );
                }
                lifecycle_manager::refresh_dataset();
                \local_upgradeassistant\event\lifecycle_synced::create(['context' => $context])->trigger();
                self::success($returnurl, get_string('lifecyclerefreshed', 'local_upgradeassistant'));
                break;

            default:
                throw new \moodle_exception('invalidaction', 'local_upgradeassistant');
        }
    }

    /**
     * Ensure the action is permitted for the current user.
     *
     * @return void
     */
    private static function require_capabilities(string $action, \context_system $context): void {
        switch ($action) {
            case 'maintenanceon':
            case 'maintenanceoff':
            case 'switchtoboosttheme':
            case 'purgecaches':
                require_capability('moodle/site:config', $context);
                require_capability('local/upgradeassistant:configure', $context);
                break;
            case 'synclifecycle':
                require_capability('local/upgradeassistant:configure', $context);
                break;
            case 'reset':
            case 'confirmboosttheme':
            case 'completestep':
            case 'completeauditstep':
            case 'reviewfinding':
                require_capability('local/upgradeassistant:manage', $context);
                break;
            case 'settarget':
            case 'setscanpath':
            case 'rescan':
                require_capability('local/upgradeassistant:manage', $context);
                require_capability('local/upgradeassistant:viewsensitive', $context);
                break;
            case 'generatereport':
                require_capability('local/upgradeassistant:generatereport', $context);
                require_capability('local/upgradeassistant:viewsensitive', $context);
                break;
            default:
                throw new \moodle_exception('invalidaction', 'local_upgradeassistant');
        }
    }

    /**
     * Build the URL to return to after an action.
     *
     * @return \moodle_url
     */
    private static function return_url(
        \moodle_url $baseurl,
        string $view,
        int $step,
        int $reportid = 0
    ): \moodle_url {
        $params = [];
        if ($view === 'reports') {
            $params['view'] = 'reports';
            if ($reportid > 0) {
                $params['report'] = $reportid;
            }
        } else {
            $params['step'] = max(1, min(4, $step));
        }
        return new \moodle_url($baseurl, $params);
    }

    /**
     * Resolve the finding about the active theme.
     *
     * @return void
     */
    private static function resolve_theme_finding(string $note): void {
        $latestreport = report_builder::latest_report_for_template();
        if (!empty($latestreport['available'])) {
            report_builder::resolve_finding((int)$latestreport['id'], 'theme_not_boost', $note);
            report_builder::refresh_score((int)$latestreport['id']);
        }
    }

    /**
     * Redirect with a success notification.
     *
     * @return void
     */
    private static function success(\moodle_url $url, string $message): void {
        redirect($url, $message, null, \core\output\notification::NOTIFY_SUCCESS);
    }

    /**
     * Redirect with an error notification.
     *
     * @return void
     */
    private static function error(\moodle_url $url, string $message): void {
        redirect($url, $message, null, \core\output\notification::NOTIFY_ERROR);
    }

    /**
     * Return an unambiguous response to an in-page review submission.
     *
     * @param bool $success Whether the review was saved.
     * @param string $message Response message.
     * @return void
     */
    private static function review_response(bool $success, string $message): void {
        header('Content-Type: application/json; charset=utf-8');
        if (!$success) {
            http_response_code(422);
        }
        echo json_encode(['success' => $success, 'message' => $message]);
        exit;
    }
}
