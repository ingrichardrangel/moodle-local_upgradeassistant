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
 * Exports pre-upgrade reports as professional PDF documents.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class pdf_exporter {
    /** Reports table. */
    private const REPORTS_TABLE = 'local_upgradeassistant_rep';

    /** Findings table. */
    private const ITEMS_TABLE = 'local_upgradeassistant_item';

    /** Checklist table. */
    private const CHECKLIST_TABLE = 'local_upgradeassistant_check';

    /** Audit table. */
    private const AUDIT_TABLE = 'local_upgradeassistant_audit';

    /**
     * Stream a report PDF to the browser.
     *
     * @param int $reportid Report ID.
     * @return void
     */
    public static function download(int $reportid, bool $redacted = false): void {
        global $CFG;

        require_once($CFG->libdir . '/pdflib.php');

        $data = self::get_report_data($reportid, $redacted);
        $title = get_string('pdfreporttitle', 'local_upgradeassistant');
        $filename = clean_filename('smart-upgrade-assistant-pre-upgrade-report-' . $data['report']->id
            . ($redacted ? '-redacted' : '') . '.pdf');

        $pdf = new \pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('Smart Upgrade Assistant');
        $pdf->SetAuthor($data['generatedby']);
        $pdf->SetTitle($title . ' #' . $data['report']->id);
        $pdf->SetSubject(get_string('pdfsubject', 'local_upgradeassistant'));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetMargins(12, 14, 12);
        $pdf->SetAutoPageBreak(true, 16);
        $pdf->SetFont('helvetica', '', 9);
        $pdf->AddPage();
        $pdf->writeHTML(self::build_html($data), true, false, true, false, '');

        self::record_export($reportid, $redacted ? 'pdf_redacted' : 'pdf', $filename);
        audit_logger::log(
            $reportid,
            $redacted ? 'report_pdf_redacted_downloaded' : 'report_pdf_downloaded',
            'report',
            $reportid
        );
        \local_upgradeassistant\event\report_exported::create([
            'context' => \context_system::instance(),
            'objectid' => $reportid,
            'other' => ['type' => $redacted ? 'pdf_redacted' : 'pdf'],
        ])->trigger();
        $pdf->Output($filename, 'D');
    }

    /**
     * Stream a report as standalone HTML.
     *
     * @param int $reportid Report ID.
     * @return void
     */
    public static function download_html(int $reportid, bool $redacted = false): void {
        $data = self::get_report_data($reportid, $redacted);
        $filename = clean_filename('smart-upgrade-assistant-pre-upgrade-report-' . $data['report']->id
            . ($redacted ? '-redacted' : '') . '.html');
        $html = '<!doctype html><html><head><meta charset="utf-8"><title>' .
            s(get_string('pdfreporttitle', 'local_upgradeassistant')) . '</title>' .
            self::styles() . '</head><body>' . self::build_html($data) . '</body></html>';

        self::record_export($reportid, $redacted ? 'html_redacted' : 'html', sha1($html));
        audit_logger::log(
            $reportid,
            $redacted ? 'report_html_redacted_downloaded' : 'report_html_downloaded',
            'report',
            $reportid
        );
        \local_upgradeassistant\event\report_exported::create([
            'context' => \context_system::instance(),
            'objectid' => $reportid,
            'other' => ['type' => $redacted ? 'html_redacted' : 'html'],
        ])->trigger();
        // Moodle does not provide a send_content() helper. The report is already
        // rendered in memory, so stream it as an attachment without a temp file.
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=utf-8');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: private, no-store, max-age=0');
            header('Pragma: no-cache');
            header('X-Content-Type-Options: nosniff');
        }
        echo $html;
    }

    /**
     * Record an export action.
     *
     * @param int $reportid Report ID.
     * @param string $type Export type.
     * @param string $hash Hash or filename.
     * @return void
     */
    private static function record_export(int $reportid, string $type, string $hash): void {
        global $DB, $USER;

        if (!$DB->get_manager()->table_exists('local_upgradeassistant_expt')) {
            return;
        }
        $record = (object)[
            'reportid' => $reportid,
            'userid' => (int)($USER->id ?? 0),
            'exporttype' => $type,
            'contenthash' => substr(sha1($hash), 0, 64),
            'timecreated' => time(),
        ];
        $DB->insert_record('local_upgradeassistant_expt', $record);
    }

    /**
     * Load report data for PDF export.
     *
     * @param int $reportid Report ID.
     * @return array
     */
    private static function get_report_data(int $reportid, bool $redacted = false): array {
        global $DB, $SITE;

        $report = $DB->get_record(self::REPORTS_TABLE, ['id' => $reportid], '*', MUST_EXIST);
        $items = $DB->get_records(self::ITEMS_TABLE, ['reportid' => $reportid], 'sortorder ASC, id ASC');
        $checklist = $DB->get_records(self::CHECKLIST_TABLE, ['reportid' => $reportid], 'sortorder ASC, id ASC');
        $audit = $DB->get_records(self::AUDIT_TABLE, ['reportid' => $reportid], 'timecreated ASC, id ASC');

        $userids = [(int)$report->userid => (int)$report->userid];
        foreach ($checklist as $step) {
            if (!empty($step->completedby)) {
                $userids[(int)$step->completedby] = (int)$step->completedby;
            }
        }
        foreach ($audit as $entry) {
            if (!empty($entry->userid)) {
                $userids[(int)$entry->userid] = (int)$entry->userid;
            }
        }
        $users = self::load_users(array_values($userids));
        $reportuser = $users[(int)$report->userid] ?? null;

        return [
            'report' => $report,
            'items' => $items,
            'checklist' => $checklist,
            'audit' => $audit,
            'plugins' => plugin_analyser::rows_for_report($reportid),
            'lifecycle' => lifecycle_manager::report_lifecycle_from_summary($report),
            'generatedby' => $reportuser ? fullname($reportuser) : get_string('notavailable', 'local_upgradeassistant'),
            'sitename' => format_string($SITE->fullname),
            'users' => $users,
            'redacted' => $redacted,
        ];
    }

    /**
     * Build the PDF HTML document.
     *
     * @param array $data Export data.
     * @return string
     */
    private static function build_html(array $data): string {
        $report = $data['report'];
        $items = $data['items'];
        $checklist = $data['checklist'];
        $audit = $data['audit'];
        $counts = self::severity_counts($items);
        $risklabel = get_string('risklevel' . $report->risklevel, 'local_upgradeassistant');

        $html = '';
        $html .= self::styles();
        $html .= self::cover($data, $counts, $risklabel);
        $html .= self::executive_summary($report, $items, $counts, !empty($data['redacted']));
        $html .= self::technical_profile($data);
        $html .= self::lifecycle_section($data['lifecycle']);
        $html .= self::findings_table($items, !empty($data['redacted']), $report, $audit, $data['users']);
        $html .= self::plugins_table($data['plugins']);
        $html .= self::checklist_table($checklist, $data['users'], !empty($data['redacted']), $report);
        $html .= self::audit_table($audit, $data['users'], !empty($data['redacted']), $report);
        $html .= self::closing_notes($report);

        return $html;
    }

    /**
     * Return inline CSS supported by TCPDF.
     *
     * @return string
     */
    private static function styles(): string {
        return '<style>
            body { color: #1f2d3d; font-size: 9pt; }
            h1 { color: #1B2C54; font-size: 22pt; font-weight: bold; }
            h2 { color: #1B2C54; font-size: 15pt; font-weight: bold; border-bottom: 1px solid #D8E2EF; }
            h3 { color: #1B2C54; font-size: 11pt; font-weight: bold; }
            p { line-height: 1.35; }
            .muted { color: #5f6f82; }
            .small { font-size: 8pt; }
            .cover { border: 1px solid #D8E2EF; background-color: #F6FAFE; padding: 16px; }
            .brand { color: #3984C4; font-size: 11pt; font-weight: bold; letter-spacing: .5px; }
            .meta { background-color: #FFFFFF; border: 1px solid #D8E2EF; padding: 8px; }
            .riskbox { border: 1px solid #D8E2EF; background-color: #F8FBFF; padding: 10px; }
            .risknumber { color: #1B2C54; font-size: 24pt; font-weight: bold; }
            .badge { font-weight: bold; padding: 4px 6px; }
            .critical { color: #9F1239; }
            .high { color: #B45309; }
            .medium { color: #92400E; }
            .low { color: #166534; }
            .info { color: #1D4ED8; }
            table { border-collapse: collapse; width: 100%; }
            th { background-color: #1B2C54; color: #FFFFFF; font-weight: bold; border: 1px solid #1B2C54; padding: 6px; }
            td { border: 1px solid #D8E2EF; padding: 5px; vertical-align: top; }
            .soft th { background-color: #3984C4; border: 1px solid #3984C4; }
            .okrow { background-color: #F0FDF4; }
            .warnrow { background-color: #FFF7ED; }
            .timelinecell { background-color: #F8FBFF; }
            .timelinelegend { font-size: 7pt; color: #1B2C54; }
            .timelinesegment { border: 1px solid #D8E2EF; font-size: 4pt; line-height: 4pt; }
            .pagebreak { page-break-before: always; }
        </style>';
    }

    /**
     * Build cover section.
     *
     * @param array $data Export data.
     * @param array $counts Severity counts.
     * @param string $risklabel Risk label.
     * @return string
     */
    private static function cover(array $data, array $counts, string $risklabel): string {
        global $CFG;

        $report = $data['report'];
        $html = '<div class="cover">';
        $html .= '<div class="brand">SMART UPGRADE ASSISTANT</div>';
        $html .= '<h1>' . self::clean(get_string('pdfreporttitle', 'local_upgradeassistant')) . '</h1>';
        $html .= '<p class="muted">' . self::clean(get_string('pdfreportintro', 'local_upgradeassistant')) . '</p>';
        $html .= '<br />';
        $html .= '<table class="meta" cellpadding="4">';
        $html .= '<tr><td width="30%"><strong>' . self::clean(get_string('site')) . '</strong></td><td width="70%">'
            . self::clean($data['sitename']) . '</td></tr>';
        $html .= '<tr><td><strong>' . self::clean(get_string('wwwroot', 'local_upgradeassistant'))
            . '</strong></td><td>' . self::clean(!empty($data['redacted']) ? self::redacted() : ($CFG->wwwroot ?? ''))
            . '</td></tr>';
        $html .= '<tr><td><strong>' . self::clean(get_string('reportid', 'local_upgradeassistant'))
            . '</strong></td><td>#' . (int)$report->id . ' / ' . self::clean($report->uuid) . '</td></tr>';
        $html .= '<tr><td><strong>' . self::clean(get_string('generatedon', 'local_upgradeassistant'))
            . '</strong></td><td>' . self::clean(userdate($report->timecreated)) . '</td></tr>';
        $html .= '<tr><td><strong>' . self::clean(get_string('generatedby', 'local_upgradeassistant'))
            . '</strong></td><td>' . self::clean($data['generatedby']) . '</td></tr>';
        $html .= '</table>';
        $html .= '<br />';
        $html .= '<table cellpadding="6">';
        $html .= '<tr>';
        $html .= '<td width="50%"><h3>' . self::clean(get_string('pdfcurrentplatform', 'local_upgradeassistant'))
            . '</h3><strong>' . self::clean($report->currentrelease) . '</strong><br />'
            . self::clean(get_string('branch', 'local_upgradeassistant')) . ': ' . self::clean($report->currentbranch) . '</td>';
        $html .= '<td width="50%"><h3>' . self::clean(get_string('pdftargetplatform', 'local_upgradeassistant'))
            . '</h3><strong>' . self::clean($report->targetrelease) . '</strong><br />'
            . self::clean(get_string('branch', 'local_upgradeassistant')) . ': ' . self::clean($report->targetbranch) . '</td>';
        $html .= '</tr>';
        $html .= '</table>';
        $html .= '<br />';
        $html .= '<div class="riskbox">';
        $html .= '<table cellpadding="5"><tr>';
        $html .= '<td width="38%"><span class="muted">' . self::clean(get_string(
            'riskscore',
            'local_upgradeassistant'
        )) . '</span><br /><span class="risknumber">' . (int)$report->riskscore
            . '/100</span><br /><strong class="' . self::risk_class($report->risklevel) . '">'
            . self::clean($risklabel) . '</strong></td>';
        $html .= '<td width="62%"><strong>' . self::clean(get_string('pdfseveritysummary', 'local_upgradeassistant'))
            . '</strong><br />' .
            self::clean(get_string('severitycritical', 'local_upgradeassistant')) . ': ' . $counts['critical'] . ' | ' .
            self::clean(get_string('severityhigh', 'local_upgradeassistant')) . ': ' . $counts['high'] . ' | ' .
            self::clean(get_string('severitymedium', 'local_upgradeassistant')) . ': ' . $counts['medium'] . ' | ' .
            self::clean(get_string('severitylow', 'local_upgradeassistant')) . ': ' . $counts['low'] . ' | ' .
            self::clean(get_string('severityinfo', 'local_upgradeassistant')) . ': ' . $counts['info'] . '</td>';
        $html .= '</tr></table>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<p class="small muted">' . self::clean(get_string('pdfdisclaimer', 'local_upgradeassistant')) . '</p>';
        if (!empty($data['redacted'])) {
            $html .= '<p class="small muted"><strong>' . self::clean(get_string(
                'redactedexportnotice',
                'local_upgradeassistant'
            )) . '</strong></p>';
        }

        return $html;
    }

    /**
     * Build executive summary section.
     *
     * @param object $report Report record.
     * @param array $items Findings.
     * @param array $counts Severity counts.
     * @return string
     */
    private static function executive_summary(object $report, array $items, array $counts, bool $redacted): string {
        $critical = $counts['critical'];
        $high = $counts['high'];
        $summary = get_string('pdfsummarylow', 'local_upgradeassistant');
        if ($critical > 0) {
            $summary = get_string('pdfsummarycritical', 'local_upgradeassistant');
        } else if ($high > 0 || $report->riskscore >= 50) {
            $summary = get_string('pdfsummaryhigh', 'local_upgradeassistant');
        } else if ($report->riskscore >= 25) {
            $summary = get_string('pdfsummarymedium', 'local_upgradeassistant');
        }

        $html = '<h2>' . self::clean(get_string('pdfexecutivesummary', 'local_upgradeassistant')) . '</h2>';
        $html .= '<p>' . self::clean($summary) . '</p>';
        $html .= '<p class="muted">' . self::clean(get_string('riskscoreexplanation', 'local_upgradeassistant')) . '</p>';
        $snapshot = json_decode((string)$report->summary, true);
        if (!empty($snapshot['lastcheckedat'])) {
            $html .= '<p class="muted">' . self::clean(get_string('reportlastchecked', 'local_upgradeassistant')) . ': '
                . self::clean(userdate((int)$snapshot['lastcheckedat'])) . '</p>';
        }
        $html .= '<table cellpadding="5">';
        $html .= '<tr><th width="35%">' . self::clean(get_string('indicator', 'local_upgradeassistant'))
            . '</th><th width="65%">' . self::clean(get_string('result', 'local_upgradeassistant')) . '</th></tr>';
        $html .= '<tr><td>' . self::clean(get_string('riskscore', 'local_upgradeassistant')) . '</td><td><strong>'
            . (int)$report->riskscore . '/100 - ' . self::clean(get_string(
                'risklevel' . $report->risklevel,
                'local_upgradeassistant'
            )) . '</strong></td></tr>';
        $html .= '<tr><td>' . self::clean(get_string('reportfindings', 'local_upgradeassistant')) . '</td><td>'
            . count($items) . '</td></tr>';
        $serverprofile = $redacted ? self::redacted() : $report->serverprofile;
        $html .= '<tr><td>' . self::clean(get_string('serverprofile', 'local_upgradeassistant')) . '</td><td>'
            . self::clean($serverprofile) . '</td></tr>';
        $html .= '<tr><td>' . self::clean(get_string('status', 'local_upgradeassistant')) . '</td><td>'
            . self::clean($report->status) . '</td></tr>';
        $html .= '</table>';

        return $html;
    }

    /**
     * Build technical profile section.
     *
     * @param array $data Export data.
     * @return string
     */
    private static function technical_profile(array $data): string {
        global $CFG;

        $report = $data['report'];
        $html = '<h2>' . self::clean(get_string('pdftechnicalprofile', 'local_upgradeassistant')) . '</h2>';
        $html .= '<table class="soft" cellpadding="5">';
        $html .= '<tr><th width="32%">' . self::clean(get_string('field', 'local_upgradeassistant'))
            . '</th><th width="68%">' . self::clean(get_string('value', 'local_upgradeassistant')) . '</th></tr>';
        $summary = json_decode($report->summary ?? '', true);
        if (!is_array($summary)) {
            $summary = [];
        }
        $targetsummary = $summary['target'] ?? [];

        $redacted = !empty($data['redacted']);
        $rows = [
            get_string('current', 'local_upgradeassistant') => $report->currentrelease . ' (' . $report->currentbranch . ')',
            get_string('targetversion', 'local_upgradeassistant') => $report->targetrelease . ' (' . $report->targetbranch . ')',
            get_string('targetpathlabel', 'local_upgradeassistant') => $redacted ? self::redacted() : $report->targetpath,
            get_string('targetpublicstructure', 'local_upgradeassistant') => !empty($targetsummary['haspublic'])
                ? get_string('detected', 'local_upgradeassistant') : get_string('notdetected', 'local_upgradeassistant'),
            get_string('targetpublicpath', 'local_upgradeassistant')
                => $redacted ? self::redacted() : ($targetsummary['publicpath'] ?? ''),
            get_string('targetconfigpath', 'local_upgradeassistant')
                => $redacted ? self::redacted() : ($targetsummary['configpath'] ?? ''),
            get_string('phpversion', 'local_upgradeassistant') => $report->phpversion,
            get_string('database', 'local_upgradeassistant') => $redacted ? self::redacted() : ($report->dbtype . ' - '
                . $report->dbversion),
            get_string('serverprofile', 'local_upgradeassistant') => $redacted ? self::redacted() : $report->serverprofile,
            get_string('moodledataroot', 'local_upgradeassistant') => $redacted ? self::redacted() : ($CFG->dataroot ?? ''),
        ];
        foreach ($rows as $label => $value) {
            $html .= '<tr><td><strong>' . self::clean($label) . '</strong></td><td>' . self::clean($value) . '</td></tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Build Moodle lifecycle section.
     *
     * @param array $lifecycle Lifecycle report data.
     * @return string
     */
    private static function lifecycle_section(array $lifecycle): string {
        if (empty($lifecycle['available'])) {
            return '';
        }

        $html = '<h2>' . self::clean(get_string('lifecyclepdfsectiontitle', 'local_upgradeassistant')) . '</h2>';
        $html .= '<p class="muted">' . self::clean(get_string('lifecyclepdfsectiondesc', 'local_upgradeassistant')) . '</p>';
        $html .= '<table class="soft" cellpadding="5">';
        $html .= '<tr><th width="25%">' . self::clean(get_string('indicator', 'local_upgradeassistant'))
            . '</th><th width="75%">' . self::clean(get_string('result', 'local_upgradeassistant')) . '</th></tr>';
        $html .= '<tr><td><strong>' . self::clean(get_string('current', 'local_upgradeassistant')) . '</strong></td><td>' .
            self::clean($lifecycle['current']['label'] ?? '') . ' - '
                . self::clean($lifecycle['current']['statuslabel'] ?? '') . '</td></tr>';
        if (!empty($lifecycle['hastarget'])) {
            $html .= '<tr><td><strong>' . self::clean(get_string('targetversion', 'local_upgradeassistant'))
                . '</strong></td><td>' .
                self::clean($lifecycle['target']['label'] ?? '') . ' - '
                    . self::clean($lifecycle['target']['statuslabel'] ?? '') . '</td></tr>';
        }
        $html .= '<tr><td><strong>' . self::clean(get_string('recommendation', 'local_upgradeassistant')) . '</strong></td><td>' .
            self::clean($lifecycle['recommendation'] ?? '') . '</td></tr>';
        $html .= '<tr><td><strong>' . self::clean(get_string('lifecyclesource', 'local_upgradeassistant')) . '</strong></td><td>' .
            self::clean(($lifecycle['source'] ?? '') . ' - ' . ($lifecycle['sourceurl'] ?? '')) . '</td></tr>';
        $html .= '<tr><td><strong>' . self::clean(get_string('lifecyclelastsync', 'local_upgradeassistant'))
            . '</strong></td><td>' .
            self::clean($lifecycle['lastsync'] ?? '') . '</td></tr>';
        $html .= '</table>';

        $html .= '<h3>' . self::clean(get_string('lifecycletimeline', 'local_upgradeassistant')) . '</h3>';
        $html .= '<table cellpadding="4">';
        $html .= '<tr><th width="15%">' . self::clean(get_string('moodleversion', 'local_upgradeassistant')) . '</th>' .
            '<th width="15%">' . self::clean(get_string('status', 'local_upgradeassistant')) . '</th>' .
            '<th width="17%">' . self::clean(get_string('lifecyclerelease', 'local_upgradeassistant')) . '</th>' .
            '<th width="17%">' . self::clean(get_string('lifecyclegeneraluntil', 'local_upgradeassistant')) . '</th>' .
            '<th width="17%">' . self::clean(get_string('lifecyclesecurityuntil', 'local_upgradeassistant')) . '</th>' .
            '<th width="19%">' . self::clean(get_string('lifecycletimeline', 'local_upgradeassistant')) . '</th></tr>';
        foreach (($lifecycle['rows'] ?? []) as $row) {
            $timeline = self::timeline_cell((string)($row['statuskey'] ?? ''));
            $html .= '<tr>';
            $html .= '<td><strong>' . self::clean($row['label'] ?? '') . '</strong></td>';
            $html .= '<td>' . self::clean($row['statuslabel'] ?? '') . '</td>';
            $html .= '<td>' . self::clean($row['releasedate'] ?? '') . '</td>';
            $html .= '<td>' . self::clean($row['generalend'] ?? '') . '</td>';
            $html .= '<td>' . self::clean($row['securityend'] ?? '') . '</td>';
            $html .= '<td class="timelinecell">' . $timeline . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Render a PDF-safe lifecycle bar without Unicode block characters.
     *
     * TCPDF default fonts can render Unicode block characters as question marks.
     * This method uses nested HTML tables, ASCII labels and background colours
     * supported by the TCPDF HTML renderer.
     *
     * @param string $statuskey Status key.
     * @return string
     */
    private static function timeline_cell(string $statuskey): string {
        $generalwidth = 75;
        $securitywidth = 55;

        if ($statuskey === 'future') {
            $generalwidth = 8;
            $securitywidth = 8;
        } else if ($statuskey === 'unsupported') {
            $generalwidth = 35;
            $securitywidth = 55;
        } else if ($statuskey === 'security') {
            $generalwidth = 100;
            $securitywidth = 80;
        }

        return self::timeline_bar(
            get_string('lifecyclegeneralsupport', 'local_upgradeassistant'),
            $generalwidth,
            '#28A745'
        ) . self::timeline_bar(
            get_string('lifecyclesecuritysupport', 'local_upgradeassistant'),
            $securitywidth,
            '#17C3B2'
        );
    }

    /**
     * Render a single PDF-safe lifecycle bar row.
     *
     * @param string $label Bar label.
     * @param int $width Filled width percentage.
     * @param string $colour Fill colour.
     * @return string
     */
    private static function timeline_bar(string $label, int $width, string $colour): string {
        $width = max(1, min(100, $width));
        $remaining = 100 - $width;
        $html = '<table cellpadding="1" cellspacing="0" width="100%">';
        $html .= '<tr><td colspan="2" class="timelinelegend">' . self::clean($label) . '</td></tr>';
        $html .= '<tr>';
        $html .= '<td width="' . $width . '%" bgcolor="' . $colour . '" class="timelinesegment">&nbsp;</td>';
        if ($remaining > 0) {
            $html .= '<td width="' . $remaining . '%" bgcolor="#EEF2F7" class="timelinesegment">&nbsp;</td>';
        }
        $html .= '</tr></table>';

        return $html;
    }

    /**
     * Build findings section.
     *
     * @param array $items Findings records.
     * @param bool $redacted Whether sensitive values must be redacted.
     * @param object $report Report record.
     * @param array $audit Audit records.
     * @param array $users Users keyed by ID.
     * @return string
     */
    private static function findings_table(
        array $items,
        bool $redacted,
        object $report,
        array $audit,
        array $users
    ): string {
        $html = '<div class="pagebreak"></div>';
        $html .= '<h2>' . self::clean(get_string('reportfindings', 'local_upgradeassistant')) . '</h2>';
        $html .= '<p class="muted">' . self::clean(get_string('pdffindingsintro', 'local_upgradeassistant')) . '</p>';
        if (empty($items)) {
            return $html . '<p>' . self::clean(get_string('nodifferences', 'local_upgradeassistant')) . '</p>';
        }

        $reviewauditbyfinding = [];
        foreach (array_reverse($audit, true) as $entry) {
            if (
                $entry->action === 'finding_reviewed' && (int)$entry->targetid > 0
                && !isset($reviewauditbyfinding[(int)$entry->targetid])
            ) {
                $reviewauditbyfinding[(int)$entry->targetid] = $entry;
            }
        }

        $html .= '<table cellpadding="4">';
        $html .= '<tr><th width="14%">' . self::clean(get_string('category', 'local_upgradeassistant')) .
            '</th><th width="12%">' . self::clean(get_string('severity', 'local_upgradeassistant')) .
            '</th><th width="12%">' . self::clean(get_string('status', 'local_upgradeassistant')) .
            '</th><th width="32%">' . self::clean(get_string('finding', 'local_upgradeassistant')) .
            '</th><th width="30%">' . self::clean(get_string('recommendation', 'local_upgradeassistant')) . '</th></tr>';
        foreach ($items as $item) {
            $description = (string)$item->description;
            $recommendation = (string)$item->recommendation;
            if ($redacted) {
                $description = self::redact_sensitive_text($description, $report);
                $recommendation = self::redact_sensitive_text($recommendation, $report);
            }
            $html .= '<tr>';
            $html .= '<td>' . self::clean(get_string('reportcategory' . $item->category, 'local_upgradeassistant')) . '</td>';
            $html .= '<td><strong class="' . self::risk_class($item->severity) . '">'
                . self::clean(get_string('severity' . $item->severity, 'local_upgradeassistant')) . '</strong></td>';
            $evidence = json_decode((string)$item->evidence, true);
            if (!is_array($evidence)) {
                $evidence = [];
            }
            $isofficialremoval = ($evidence['compatibility'] ?? '') === 'core_removed'
                && $item->status === 'closed';
            $verified = $item->status === 'closed' ? ($evidence['verifiedresolution'] ?? []) : [];
            $reason = is_array($verified) ? ($verified['reason'] ?? '') : '';
            $reasontext = in_array($reason, [
                'notdetected', 'sourceabsent', 'sourceabsenttargetadded', 'targetadded', 'targetcompatible',
            ], true) ? get_string(
                'findingresolution' . $reason,
                'local_upgradeassistant',
                (string)($verified['component'] ?? '')
            ) : '';
            if ($item->status === 'reviewed') {
                $statuslabel = get_string(
                    $item->severity === 'info' ? 'findingstatusreviewed' : 'findingstatusaccepted',
                    'local_upgradeassistant'
                );
            } else if ($item->code === 'lifecycle_current_unsupported' && $item->status === 'closed') {
                $statuslabel = get_string('findingstatusmitigated', 'local_upgradeassistant');
            } else if ($isofficialremoval) {
                $statuslabel = get_string('findingstatusofficialremoval', 'local_upgradeassistant');
            } else {
                $statuslabel = get_string('findingstatus' . $item->status, 'local_upgradeassistant');
            }

            $reviewtext = '';
            $reviewmeta = '';
            $reviewaudit = $item->status === 'reviewed' ? ($reviewauditbyfinding[(int)$item->id] ?? null) : null;
            if ($reviewaudit !== null && trim((string)$reviewaudit->note) !== '') {
                $reviewtext = (string)$reviewaudit->note;
                if ($redacted) {
                    $reviewtext = self::redact_sensitive_text($reviewtext, $report);
                }
                $reviewer = $users[(int)$reviewaudit->userid] ?? null;
                if ($reviewer !== null) {
                    $reviewmeta = fullname($reviewer) . ' · ' . userdate((int)$reviewaudit->timecreated);
                } else {
                    $reviewmeta = userdate((int)$reviewaudit->timecreated);
                }
            }

            $html .= '<td>' . self::clean($statuslabel) . '</td>';
            $html .= '<td><strong>' . self::clean($item->title) . '</strong>';
            if ($reasontext !== '') {
                $html .= '<br /><strong>' . self::clean(get_string('findingresolutionlabel', 'local_upgradeassistant'))
                    . ':</strong> ' . self::clean($reasontext);
            } else {
                $html .= '<br /><span class="small muted">' . self::clean($description) . '</span>';
            }
            if ($reviewtext !== '') {
                $html .= '<br /><br /><strong>' . self::clean(get_string(
                    'administratorcriterion',
                    'local_upgradeassistant'
                )) . ':</strong> ' . self::clean($reviewtext);
                if ($reviewmeta !== '') {
                    $html .= '<br /><span class="small muted">' . self::clean($reviewmeta) . '</span>';
                }
            }
            $html .= '</td>';
            $html .= '<td>' . ($reasontext !== '' ? '' : self::clean($recommendation)) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Build plugin matrix section.
     *
     * @param array $plugins Plugin rows.
     * @return string
     */
    private static function plugins_table(array $plugins): string {
        $html = '<h2>' . self::clean(get_string('advancedpluginmatrix', 'local_upgradeassistant')) . '</h2>';
        if (empty($plugins)) {
            return $html . '<p>' . self::clean(get_string('notavailable', 'local_upgradeassistant')) . '</p>';
        }
        $html .= '<table cellpadding="4">';
        $html .= '<tr><th width="31%">' . self::clean(get_string('component', 'local_upgradeassistant'))
            . '</th><th width="12%">' . self::clean(get_string('type', 'local_upgradeassistant'))
            . '</th><th width="17%">' . self::clean(get_string('version', 'local_upgradeassistant'))
            . '</th><th width="17%">' . self::clean(get_string('targetversion', 'local_upgradeassistant'))
            . '</th><th width="23%">' . self::clean(get_string('compatibility', 'local_upgradeassistant')) . '</th></tr>';
        foreach ($plugins as $plugin) {
            $html .= '<tr>';
            $html .= '<td><strong>' . self::clean($plugin['component']) . '</strong><br /><span class="small muted">'
                . self::clean($plugin['release']) . '</span></td>';
            $html .= '<td>' . self::clean($plugin['type']) . '</td>';
            $html .= '<td>' . self::clean($plugin['version']) . '</td>';
            $html .= '<td>' . self::clean($plugin['targetversion']) . '</td>';
            $html .= '<td>' . self::clean($plugin['status']) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
        return $html;
    }

    /**
     * Build auditable checklist section.
     *
     * @param array $checklist Checklist records.
     * @return string
     */
    private static function checklist_table(array $checklist, array $users, bool $redacted, object $report): string {
        $html = '<h2>' . self::clean(get_string('auditablechecklist', 'local_upgradeassistant')) . '</h2>';
        $html .= '<p class="muted">' . self::clean(get_string('pdfchecklistintro', 'local_upgradeassistant')) . '</p>';
        $html .= '<table cellpadding="4">';
        $html .= '<tr><th width="28%">' . self::clean(get_string('action', 'local_upgradeassistant'))
            . '</th><th width="14%">' . self::clean(get_string('status', 'local_upgradeassistant'))
            . '</th><th width="22%">' . self::clean(get_string('completedby', 'local_upgradeassistant'))
            . '</th><th width="18%">' . self::clean(get_string('date')) . '</th><th width="18%">'
            . self::clean(get_string('note', 'local_upgradeassistant')) . '</th></tr>';
        foreach ($checklist as $step) {
            $completed = $step->status === 'completed';
            $classname = $completed ? 'okrow' : 'warnrow';
            $completedby = '';
            if ($completed && !empty($step->completedby) && !empty($users[(int)$step->completedby])) {
                $completedby = fullname($users[(int)$step->completedby]);
            }
            $html .= '<tr class="' . $classname . '">';
            $html .= '<td><strong>' . self::clean($step->title) . '</strong><br /><span class="small muted">'
                . self::clean($step->description) . '</span></td>';
            $html .= '<td>' . self::clean(get_string('auditstatus' . $step->status, 'local_upgradeassistant')) . '</td>';
            $html .= '<td>' . self::clean($completedby) . '</td>';
            $html .= '<td>' . ($completed && !empty($step->completedat)
                ? self::clean(userdate($step->completedat)) : '-') . '</td>';
            $note = (string)$step->note;
            if ($redacted) {
                $note = self::redact_sensitive_text($note, $report);
            }
            $html .= '<td>' . self::clean($note) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Build audit trail section.
     *
     * @param array $audit Audit records.
     * @return string
     */
    private static function audit_table(array $audit, array $users, bool $redacted, object $report): string {
        $html = '<h2>' . self::clean(get_string('pdfaudittrail', 'local_upgradeassistant')) . '</h2>';
        $html .= '<p class="muted">' . self::clean(get_string('pdfauditintro', 'local_upgradeassistant')) . '</p>';
        if (empty($audit)) {
            return $html . '<p>' . self::clean(get_string('notavailable', 'local_upgradeassistant')) . '</p>';
        }

        $html .= '<table cellpadding="4">';
        $html .= '<tr><th width="25%">' . self::clean(get_string('date')) . '</th><th width="25%">'
            . self::clean(get_string('user')) . '</th><th width="22%">' . self::clean(get_string(
                'action',
                'local_upgradeassistant'
            )) . '</th><th width="28%">' . self::clean(get_string(
                'note',
                'local_upgradeassistant'
            )) . '</th></tr>';
        foreach ($audit as $entry) {
            $html .= '<tr>';
            $html .= '<td>' . self::clean(userdate($entry->timecreated)) . '</td>';
            $user = !empty($users[(int)$entry->userid]) ? fullname($users[(int)$entry->userid]) : '';
            $html .= '<td>' . self::clean($user) . '</td>';
            $html .= '<td>' . self::clean($entry->action) . '</td>';
            $note = (string)$entry->note;
            if ($redacted) {
                $note = $note !== '' ? self::redact_sensitive_text($note, $report) : self::redacted();
            } else {
                $note = $note !== '' ? $note : (string)$entry->ip;
            }
            $html .= '<td>' . self::clean($note) . '</td>';
            $html .= '</tr>';
        }
        $html .= '</table>';

        return $html;
    }

    /**
     * Build final recommendations section.
     *
     * @param object $report Report record.
     * @return string
     */
    private static function closing_notes(object $report): string {
        $html = '<h2>' . self::clean(get_string('pdfnextsteps', 'local_upgradeassistant')) . '</h2>';
        $html .= '<ol>';
        $html .= '<li>' . self::clean(get_string('pdfnextstepbackup', 'local_upgradeassistant')) . '</li>';
        $html .= '<li>' . self::clean(get_string('pdfnextstepcritical', 'local_upgradeassistant')) . '</li>';
        $html .= '<li>' . self::clean(get_string('pdfnextstepplugins', 'local_upgradeassistant')) . '</li>';
        $html .= '<li>' . self::clean(get_string('pdfnextstepstaging', 'local_upgradeassistant')) . '</li>';
        $html .= '<li>' . self::clean(get_string('pdfnextstepnative', 'local_upgradeassistant')) . '</li>';
        $html .= '</ol>';
        $html .= '<p class="small muted">' . self::clean(get_string(
            'pdffinalnote',
            'local_upgradeassistant',
            $report->uuid
        )) . '</p>';

        return $html;
    }

    /**
     * Count findings by severity.
     *
     * @param array $items Findings.
     * @return array
     */
    private static function severity_counts(array $items): array {
        $counts = [
            'critical' => 0,
            'high' => 0,
            'medium' => 0,
            'low' => 0,
            'info' => 0,
        ];
        foreach ($items as $item) {
            if (($item->status ?? 'open') === 'closed') {
                continue;
            }
            $severity = $item->severity ?? 'info';
            if (!array_key_exists($severity, $counts)) {
                $severity = 'info';
            }
            $counts[$severity]++;
        }
        return $counts;
    }

    /**
     * Return a risk CSS class.
     *
     * @param string $level Risk or severity key.
     * @return string
     */
    private static function risk_class(string $level): string {
        if ($level === 'critical') {
            return 'critical';
        }
        if ($level === 'high') {
            return 'high';
        }
        if ($level === 'medium') {
            return 'medium';
        }
        if ($level === 'low') {
            return 'low';
        }
        return 'info';
    }


    /**
     * Load users needed by the export without issuing one query per row.
     *
     * @param array $userids User IDs.
     * @return array
     */
    private static function load_users(array $userids): array {
        global $DB;

        $userids = array_values(array_filter(array_unique(array_map('intval', $userids))));
        if (empty($userids)) {
            return [];
        }

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        return $DB->get_records_select(
            'user',
            "id $insql",
            $params,
            '',
            'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename, email'
        );
    }

    /**
     * Redact sensitive paths and diagnostics from free-text export content.
     *
     * @param string $text Text to redact.
     * @param object $report Report record.
     * @return string Redacted text.
     */
    private static function redact_sensitive_text(string $text, object $report): string {
        global $CFG;

        $redacted = self::redacted();
        $values = [
            (string)($CFG->dirroot ?? ''),
            (string)($CFG->dataroot ?? ''),
            (string)($CFG->wwwroot ?? ''),
            (string)($report->targetpath ?? ''),
            (string)($report->dbtype ?? ''),
            (string)($report->dbversion ?? ''),
        ];

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

        $values = array_filter(array_unique($values), static function (string $value): bool {
            return $value !== '' && strlen($value) > 2;
        });
        usort($values, static function (string $a, string $b): int {
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
     * Return the redacted placeholder.
     *
     * @return string
     */
    private static function redacted(): string {
        return get_string('redacted', 'local_upgradeassistant');
    }

    /**
     * Escape text for TCPDF HTML.
     *
     * @param mixed $value Value.
     * @return string
     */
    private static function clean($value): string {
        return s(strip_tags((string)$value));
    }
}
