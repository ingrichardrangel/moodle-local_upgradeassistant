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

/**
 * Smart Upgrade Assistant main controller.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

use local_upgradeassistant\local\action_controller;
use local_upgradeassistant\local\dashboard_service;
use local_upgradeassistant\local\detector;
use local_upgradeassistant\local\lifecycle_manager;
use local_upgradeassistant\local\plugin_analyser;
use local_upgradeassistant\local\report_builder;
use local_upgradeassistant\local\requirements_validator;
use local_upgradeassistant\local\rule_engine;
use local_upgradeassistant\local\server_recommendation_engine;
use local_upgradeassistant\local\state;
use local_upgradeassistant\local\upgrade_path;
use local_upgradeassistant\local\wizard_service;
use local_upgradeassistant\output\main_page;

admin_externalpage_setup('local_upgradeassistant');

$context = context_system::instance();
require_capability('local/upgradeassistant:view', $context);

$baseurl = new moodle_url('/local/upgradeassistant/index.php');
$view = optional_param('view', 'wizard', PARAM_ALPHA);
$view = $view === 'reports' ? 'reports' : 'wizard';
$requestedstep = optional_param('step', 0, PARAM_INT);
$selectedreportid = optional_param('report', 0, PARAM_INT);

$canviewsensitive = has_capability('local/upgradeassistant:viewsensitive', $context);
$capabilities = [
    'canmanage' => has_capability('local/upgradeassistant:manage', $context),
    'canconfigure' => has_capability('local/upgradeassistant:configure', $context),
    'cangeneratereport' => has_capability('local/upgradeassistant:generatereport', $context),
    'canviewreports' => has_capability('local/upgradeassistant:viewreports', $context),
    'canviewsensitive' => $canviewsensitive,
    'canexportredacted' => has_capability('local/upgradeassistant:export', $context)
        && has_capability('local/upgradeassistant:viewreports', $context),
    'canexportcomplete' => has_capability('local/upgradeassistant:export', $context)
        && has_capability('local/upgradeassistant:viewsensitive', $context)
        && has_capability('moodle/site:config', $context),
];
$capabilities['cangeneratecompletereport'] = $capabilities['cangeneratereport'] && $canviewsensitive;
$capabilities['canexport'] = $capabilities['canexportredacted'] || $capabilities['canexportcomplete'];
$capabilities['canlifecyclesync'] = $capabilities['canconfigure']
    && get_config('local_upgradeassistant', 'enablelifecyclesync') !== '0';

$currentparams = [];
if ($view === 'reports') {
    $currentparams['view'] = 'reports';
    if ($selectedreportid > 0) {
        $currentparams['report'] = $selectedreportid;
    }
} else if ($requestedstep > 0) {
    $currentparams['step'] = max(1, min(4, $requestedstep));
}

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url($baseurl, $currentparams));
$PAGE->set_title(get_string('pluginname', 'local_upgradeassistant'));
$PAGE->set_heading(get_string('pluginname', 'local_upgradeassistant'));
$PAGE->requires->css(new moodle_url('/local/upgradeassistant/styles.css'));
if ($view === 'reports') {
    $PAGE->requires->js(new moodle_url('/local/upgradeassistant/js/report_review.js', ['v' => 2026092303]));
}

$allowedroots = detector::default_scan_roots($CFG->dirroot);
$action = optional_param('action', '', PARAM_ALPHAEXT);
if ($action !== '') {
    action_controller::handle($action, $context, $baseurl, $allowedroots, [
        'stepid' => optional_param('stepid', '', PARAM_ALPHANUMEXT),
        'checklistid' => optional_param('checklistid', 0, PARAM_INT),
        'findingid' => optional_param('findingid', 0, PARAM_INT),
        'auditnote' => optional_param('auditnote', '', PARAM_TEXT),
        'ajaxreview' => optional_param('ajaxreview', 0, PARAM_BOOL),
        'reportid' => optional_param('reportid', 0, PARAM_INT),
        'targetpath' => optional_param('targetpath', '', PARAM_RAW_TRIMMED),
        'scanpath' => optional_param('scanpath', '', PARAM_RAW_TRIMMED),
        'returnview' => optional_param('returnview', 'wizard', PARAM_ALPHA),
        'returnstep' => optional_param('returnstep', 1, PARAM_INT),
    ]);
}

$currentstate = state::get();
$env = detector::environment();
$selectedtarget = null;
if (
    !empty($currentstate['targetpath'])
    && detector::is_allowed_path($currentstate['targetpath'], $allowedroots)
) {
    $selectedtarget = detector::read_moodle_version($currentstate['targetpath']);
}

$scanroot = $currentstate['lastscanpath'] ?: ($allowedroots[0] ?? dirname($CFG->dirroot));
if (!detector::is_allowed_path($scanroot, $allowedroots)) {
    $scanroot = $allowedroots[0] ?? dirname($CFG->dirroot);
}
$scanroot = realpath($scanroot) ?: $scanroot;

$cache = cache::make('local_upgradeassistant', 'scanresults');
$cachekey = sha1($scanroot);
$installations = $cache->get($cachekey);
if ($installations === false) {
    $installations = detector::scan($scanroot);
    $cache->set($cachekey, $installations);
}

$currentbranchint = upgrade_path::branch_to_int($env['branch']);
$analysis = null;
$customplugins = [];
if ($selectedtarget !== null) {
    $analysis = upgrade_path::analyze($env['branch'], $selectedtarget['branch'], $selectedtarget, $env['release']);
    $targetcoderoot = !empty($selectedtarget['haspublic']) && !empty($selectedtarget['publicpath'])
        ? $selectedtarget['publicpath'] : $selectedtarget['path'];
    $customplugins = plugin_analyser::compare($CFG->dirroot, $targetcoderoot, $selectedtarget['branch']);
}

$dbdesc = is_array($env['dbinfo'])
    ? ($env['dbinfo']['description'] ?? json_encode($env['dbinfo']))
    : (string)$env['dbinfo'];

$sensitivevalue = static function (?string $value) use ($canviewsensitive): string {
    return $canviewsensitive ? (string)$value : get_string('sensitivehidden', 'local_upgradeassistant');
};

$installationrows = [];
foreach ($installations as $installation) {
    $iscurrent = realpath($installation['path']) === realpath($CFG->dirroot);
    $rowanalysis = !$iscurrent
        ? upgrade_path::analyze($env['branch'], $installation['branch'], $installation, $env['release']) : null;
    $status = $rowanalysis['status'] ?? 'info';
    $installationrows[] = [
        'folder' => $installation['folder'],
        'release' => $installation['release'],
        'branch' => $installation['branch'],
        'branchlabel' => $installation['branchlabel'],
        'path' => $sensitivevalue($installation['path']),
        'pathvalue' => $canviewsensitive ? $installation['path'] : '',
        'haspublic' => !empty($installation['haspublic']),
        'iscurrent' => $iscurrent,
        'canselect' => !$iscurrent && $canviewsensitive,
        'statusclass' => $status === 'error' ? 'ua-badge-danger'
            : ($status === 'warning' ? 'ua-badge-warn' : 'ua-badge-ok'),
        'statuslabel' => $iscurrent
            ? get_string('currentinstallation', 'local_upgradeassistant')
            : get_string('compatiblestatus' . $status, 'local_upgradeassistant'),
        'route' => $rowanalysis && !empty($rowanalysis['route'])
            ? upgrade_path::route_text($currentbranchint, $rowanalysis['route'], $rowanalysis['nextrelease'] ?? null) : '',
        'hasroute' => $rowanalysis && !empty($rowanalysis['route']),
    ];
}

$analysisdata = ['available' => false];
if ($analysis !== null && $selectedtarget !== null) {
    $analysisdata = [
        'available' => true,
        'alertclass' => $analysis['status'] === 'error' ? 'alert-danger'
            : ($analysis['status'] === 'warning' ? 'alert-warning'
                : ($analysis['status'] === 'success' ? 'alert-success' : 'alert-info')),
        'statusclass' => $analysis['status'] === 'error' ? 'ua-badge-danger'
            : ($analysis['status'] === 'warning' ? 'ua-badge-warn' : 'ua-badge-ok'),
        'statuslabel' => get_string('compatiblestatus' . ($analysis['status'] ?? 'info'), 'local_upgradeassistant'),
        'message' => $analysis['message'],
        'hasroute' => !empty($analysis['route']),
        'route' => !empty($analysis['route'])
            ? upgrade_path::route_text($currentbranchint, $analysis['route'], $analysis['nextrelease'] ?? null) : '',
        'directallowed' => !empty($analysis['directallowed']),
        'nextlabel' => $analysis['nextrelease'] ?? (!empty($analysis['next']) ? upgrade_path::label((int)$analysis['next']) : ''),
        'notnextwarning' => !empty($analysis['next'])
            ? get_string(
                'notnextwarning',
                'local_upgradeassistant',
                $analysis['nextrelease'] ?? upgrade_path::label((int)$analysis['next'])
            )
            : '',
    ];
}

$validationrows = $selectedtarget !== null
    ? requirements_validator::rows_for_template($selectedtarget['branch'] ?? '', $env, $selectedtarget)
    : [];
$validationpasses = count(array_filter($validationrows, static function (array $row): bool {
    return ($row['status'] ?? '') === 'pass';
}));
$validationwarnings = count(array_filter($validationrows, static function (array $row): bool {
    return ($row['status'] ?? '') === 'warning';
}));
$validationfailures = count(array_filter($validationrows, static function (array $row): bool {
    return ($row['status'] ?? '') === 'fail';
}));

$serverprofile = server_recommendation_engine::detect_profile();
$serverrecommendations = server_recommendation_engine::rows_for_template($serverprofile);
$lifecycledata = lifecycle_manager::template_data($env['branch'], $selectedtarget['branch'] ?? '');

$lateststoredreport = $capabilities['canviewreports']
    ? report_builder::latest_report_for_template()
    : ['available' => false];
$latestreport = $lateststoredreport;
if (!empty($latestreport['available'])) {
    $sametarget = $selectedtarget !== null
        && (string)($latestreport['targetbranch'] ?? '') === (string)($selectedtarget['branch'] ?? '');
    $samecurrent = (string)($latestreport['currentbranch'] ?? '') === (string)($env['branch'] ?? '');
    if (!$sametarget || !$samecurrent) {
        $latestreport = ['available' => false];
    }
}
$proreportdata = $view === 'reports' ? $lateststoredreport : $latestreport;
if ($capabilities['canviewreports'] && $selectedreportid > 0) {
    $proreportdata = report_builder::report_for_template($selectedreportid);
}
$reporthistoryrows = $capabilities['canviewreports'] ? report_builder::history_for_template() : [];
foreach ($reporthistoryrows as $key => $row) {
    $reporthistoryrows[$key]['viewurl'] = (new moodle_url($baseurl, [
        'view' => 'reports',
        'report' => $row['id'],
    ]))->out(false);
}

$displayplugins = array_map(static function (array $plugin) use ($canviewsensitive): array {
    if (!$canviewsensitive) {
        $plugin['source'] = get_string('sensitivehidden', 'local_upgradeassistant');
        $plugin['destination'] = get_string('sensitivehidden', 'local_upgradeassistant');
    }
    $plugin['needsreview'] = !in_array(($plugin['compatibility'] ?? ''), ['compatible', 'core_removed'], true);
    return $plugin;
}, $customplugins);

$safeenv = array_merge($env, [
    'dirroot' => $sensitivevalue($env['dirroot']),
    'dataroot' => $sensitivevalue($env['dataroot']),
    'dbtype' => $sensitivevalue($env['dbtype']),
]);
$checklist = wizard_service::checklist($currentstate, $safeenv, $capabilities);
$nexttask = null;
foreach ($checklist as $item) {
    if (empty($item['completed'])) {
        $nexttask = $item;
        break;
    }
}

$dashboard = dashboard_service::build(
    $env,
    $selectedtarget,
    $checklist,
    $validationrows,
    $customplugins,
    $analysis,
    $latestreport,
    $requestedstep,
    $baseurl
);

$instructions = $canviewsensitive
    ? wizard_service::instructions($selectedtarget, $analysis)
    : [
        'available' => false,
        'blocked' => false,
        'warning' => '',
        'message' => get_string('sensitivehidden', 'local_upgradeassistant'),
        'steps' => [],
        'targethaspublic' => false,
    ];

$data = [
    'actionurl' => $baseurl->out(false),
    'exporturl' => (new moodle_url('/local/upgradeassistant/export.php'))->out(false),
    'settingsurl' => (new moodle_url('/admin/settings.php', [
        'section' => 'local_upgradeassistant_settings',
    ]))->out(false),
    'wizardurl' => (new moodle_url($baseurl, ['step' => $dashboard['activestep']]))->out(false),
    'reportsurl' => (new moodle_url($baseurl, ['view' => 'reports']))->out(false),
    'sesskey' => sesskey(),
    'view' => [
        'wizard' => $view === 'wizard',
        'reports' => $view === 'reports',
    ],
    'capabilities' => $capabilities,
    'dashboard' => $dashboard,
    'env' => [
        'release' => $env['release'],
        'branch' => $env['branch'],
        'branchlabel' => $env['branchlabel'],
        'dirroot' => $sensitivevalue($env['dirroot']),
        'dataroot' => $sensitivevalue($env['dataroot']),
        'wwwroot' => $sensitivevalue($env['wwwroot']),
        'phpversion' => $env['phpversion'],
        'dbtype' => $sensitivevalue($env['dbtype']),
        'dbdesc' => $sensitivevalue($dbdesc),
        'theme' => $env['theme'] ?? '',
        'haspublic' => !empty($env['haspublic']),
    ],
    'scan' => [
        'roots' => $canviewsensitive ? array_map(static function (string $root) use ($scanroot): array {
            return ['path' => $root, 'selected' => $root === $scanroot];
        }, $allowedroots) : [],
        'installations' => $canviewsensitive ? $installationrows : [],
        'hasinstallations' => $canviewsensitive && !empty($installationrows),
        'scanroot' => $canviewsensitive ? $scanroot : '',
        'sensitivehidden' => !$canviewsensitive,
    ],
    'selectedtarget' => $selectedtarget ? [
        'available' => true,
        'path' => $sensitivevalue($selectedtarget['path']),
        'release' => $selectedtarget['release'],
        'branchlabel' => $selectedtarget['branchlabel'],
        'branch' => $selectedtarget['branch'],
        'haspublic' => !empty($selectedtarget['haspublic']),
        'publicpath' => $sensitivevalue($selectedtarget['publicpath'] ?? ''),
        'configpath' => $sensitivevalue($selectedtarget['configpath'] ?? ''),
    ] : ['available' => false],
    'analysis' => $analysisdata,
    'checklist' => $checklist,
    'nexttask' => $nexttask ? ['available' => true] + $nexttask : ['available' => false],
    'plugins' => [
        'hascustomplugins' => !empty($customplugins),
        'rows' => $displayplugins,
        'hastarget' => $selectedtarget !== null,
        'reviewcount' => count(array_filter($customplugins, static function (array $plugin): bool {
            return !in_array(($plugin['compatibility'] ?? ''), ['compatible', 'core_removed'], true);
        })),
        'totalcount' => count($customplugins),
    ],
    'instructions' => $instructions,
    'lifecycle' => $lifecycledata,
    'proreport' => $proreportdata,
    'reporthistory' => [
        'rows' => $reporthistoryrows,
        'hasrows' => !empty($reporthistoryrows),
    ],
    'rules' => ['rows' => rule_engine::get_rules_for_template()],
    'validation' => [
        'available' => $selectedtarget !== null,
        'targetlabel' => $selectedtarget['branchlabel'] ?? '',
        'rows' => $validationrows,
        'hasrows' => !empty($validationrows),
        'passes' => $validationpasses,
        'warnings' => $validationwarnings,
        'failures' => $validationfailures,
        'allpass' => !empty($validationrows) && $validationfailures === 0 && $validationwarnings === 0,
    ],
    'serverrecommendations' => [
        'profile' => server_recommendation_engine::profile_label($serverprofile),
        'rows' => $serverrecommendations,
    ],
];

/** @var \local_upgradeassistant\output\renderer $renderer */
$renderer = $PAGE->get_renderer('local_upgradeassistant');

echo $OUTPUT->header();
echo $renderer->render_main_page(new main_page($data));
echo $OUTPUT->footer();
