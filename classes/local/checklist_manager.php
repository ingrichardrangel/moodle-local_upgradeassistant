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
 * Manages auditable checklist records for Pro reports.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class checklist_manager {
    /** Checklist table name. */
    private const TABLE = 'local_ua_checklist';

    /**
     * Create default checklist rows for a report.
     *
     * @param int $reportid Report ID.
     * @param array $wizardstate Current Community wizard state.
     * @return void
     */
    public static function seed_for_report(int $reportid, array $wizardstate): void {
        global $DB, $USER;

        $now = time();
        $sort = 10;
        foreach (self::default_steps() as $step) {
            $completed = !empty($wizardstate['completed'][$step['stepkey']]);
            $completedtime = $completed ? (int)$wizardstate['completed'][$step['stepkey']] : 0;
            $note = $completed ? get_string('auditedfromwizard', 'local_upgradeassistant') : '';

            if (!$completed && $step['stepkey'] === 'maintenance' && self::is_maintenance_mode_active()) {
                $completed = true;
                $completedtime = $now;
                $note = get_string('maintenanceverifiednote', 'local_upgradeassistant');
            }

            if (!$completed && $step['stepkey'] === 'boosttheme' && self::is_boost_theme_active()) {
                $completed = true;
                $completedtime = $now;
                $note = get_string('boostthemeverifiednote', 'local_upgradeassistant');
            }

            $record = (object)[
                'reportid' => $reportid,
                'stepkey' => $step['stepkey'],
                'title' => $step['title'],
                'description' => $step['description'],
                'required' => $step['required'],
                'status' => $completed ? 'completed' : 'pending',
                'completedby' => $completed ? (int)($USER->id ?? 0) : 0,
                'completedat' => $completed ? $completedtime : 0,
                'note' => $completed ? $note : '',
                'sortorder' => $sort,
                'timecreated' => $now,
                'timemodified' => $now,
            ];
            $id = (int)$DB->insert_record(self::TABLE, $record);
            audit_logger::log($reportid, 'checklist_created', 'checklist', $id, null, $record);
            $sort += 10;
        }
    }


    /**
     * Reconcile auditable checklist rows with verifiable live site states.
     *
     * Existing reports may have been generated before the administrator opened
     * the assistant or before this automatic detection was available. Only
     * objective states that Moodle can verify directly are completed here.
     *
     * @param int $reportid Report ID.
     * @return void
     */
    public static function synchronise_environment_steps(int $reportid): void {
        global $DB;

        if ($reportid <= 0
                || !has_capability('local/upgradeassistant:manage', \context_system::instance())) {
            return;
        }

        $checks = [];
        if (self::is_maintenance_mode_active()) {
            $checks['maintenance'] = get_string('maintenanceverifiednote', 'local_upgradeassistant');
        }
        if (self::is_boost_theme_active()) {
            $checks['boosttheme'] = get_string('boostthemeverifiednote', 'local_upgradeassistant');
        }

        foreach ($checks as $stepkey => $note) {
            $record = $DB->get_record(self::TABLE, [
                'reportid' => $reportid,
                'stepkey' => $stepkey,
            ]);
            if ($record && $record->status !== 'completed') {
                self::complete_step((int)$record->id, $note, $reportid);
            }
        }
    }

    /**
     * Mark a checklist row as completed.
     *
     * @param int $checklistid Checklist row ID.
     * @param string $note Optional note.
     * @param int $reportid Report ID that owns the checklist row.
     * @return int Report ID.
     */
    public static function complete_step(int $checklistid, string $note = '', int $reportid = 0): int {
        global $DB, $USER;

        if ($reportid <= 0) {
            throw new \moodle_exception('invalidstep', 'local_upgradeassistant');
        }

        $record = $DB->get_record(self::TABLE, [
            'id' => $checklistid,
            'reportid' => $reportid,
        ], '*', MUST_EXIST);
        $oldvalue = clone($record);
        $record->status = 'completed';
        $record->completedby = (int)($USER->id ?? 0);
        $record->completedat = time();
        $record->note = $note;
        $record->timemodified = time();
        $DB->update_record(self::TABLE, $record);

        audit_logger::log((int)$record->reportid, 'checklist_completed', 'checklist', $checklistid, $oldvalue, $record, $note);
        \local_upgradeassistant\event\checklist_completed::create([
            'context' => \context_system::instance(),
            'objectid' => $checklistid,
            'other' => [
                'reportid' => (int)$record->reportid,
                'stepkey' => (string)$record->stepkey,
            ],
        ])->trigger();
        // Keep the operational wizard and the auditable report checklist in sync.
        state::complete_step((string)$record->stepkey);
        report_builder::resolve_checklist_finding((int)$record->reportid, (string)$record->stepkey, $note);
        report_builder::refresh_score((int)$record->reportid);

        return (int)$record->reportid;
    }

    /**
     * Mark a checklist row in the latest report as completed using its step key.
     *
     * @param string $stepkey Checklist step key.
     * @param string $note Optional note.
     * @return int|null Report ID, or null when no matching pending step exists.
     */
    public static function complete_latest_step_by_key(string $stepkey, string $note = ''): ?int {
        global $DB, $USER;

        $activeid = state::get_active_reportid();
        if ($activeid > 0) {
            $record = $DB->get_record(self::TABLE, [
                'reportid' => $activeid,
                'stepkey' => $stepkey,
            ]);
            if ($record && $record->status !== 'completed') {
                return self::complete_step((int)$record->id, $note, $activeid);
            }
            return null;
        }

        $reports = $DB->get_records('local_ua_reports', ['userid' => (int)($USER->id ?? 0)],
            'timecreated DESC, id DESC', 'id', 0, 1);
        $report = reset($reports);
        if (!$report) {
            return null;
        }

        $record = $DB->get_record(self::TABLE, [
            'reportid' => (int)$report->id,
            'stepkey' => $stepkey,
        ]);
        if (!$record || $record->status === 'completed') {
            return null;
        }

        return self::complete_step((int)$record->id, $note, (int)$report->id);
    }

    /**
     * Get checklist rows formatted for Mustache.
     *
     * @param int $reportid Report ID.
     * @return array
     */
    public static function get_template_rows(int $reportid, bool $redacted = false): array {
        global $DB;

        $report = $DB->get_record('local_ua_reports', ['id' => $reportid], '*');
        $records = $DB->get_records(self::TABLE, ['reportid' => $reportid], 'sortorder ASC, id ASC');
        $userids = [];
        foreach ($records as $record) {
            if (!empty($record->completedby)) {
                $userids[(int)$record->completedby] = (int)$record->completedby;
            }
        }

        $users = [];
        if (!empty($userids)) {
            list($insql, $params) = $DB->get_in_or_equal(array_values($userids), SQL_PARAMS_NAMED);
            $users = $DB->get_records_select(
                'user',
                "id $insql",
                $params,
                '',
                'id, firstname, lastname, firstnamephonetic, lastnamephonetic, middlename, alternatename, email'
            );
        }

        $rows = [];
        foreach ($records as $record) {
            $completed = $record->status === 'completed';
            $completedby = '';
            if ($completed && !empty($record->completedby) && !empty($users[(int)$record->completedby])) {
                $completedby = fullname($users[(int)$record->completedby]);
            }

            $rows[] = [
                'id' => $record->id,
                'reportid' => $record->reportid,
                'title' => format_string($record->title),
                'description' => format_string($record->description),
                'status' => get_string('auditstatus' . $record->status, 'local_upgradeassistant'),
                'statusclass' => $completed ? 'ua-badge-ok' : 'ua-badge-warn',
                'completed' => $completed,
                'completedby' => $completedby,
                'completedat' => $completed && !empty($record->completedat) ? userdate($record->completedat) : '',
                'note' => format_text(self::redact_note((string)$record->note, $report, $redacted), FORMAT_PLAIN),
            ];
        }

        return $rows;
    }


    /**
     * Redact sensitive paths from checklist notes for non-sensitive viewers.
     *
     * @param string $note Checklist note.
     * @param object|null $report Report record.
     * @param bool $redacted Whether sensitive details must be redacted.
     * @return string Redacted note.
     */
    private static function redact_note(string $note, ?object $report, bool $redacted): string {
        global $CFG;

        if (!$redacted || $note === '') {
            return $note;
        }

        $placeholder = get_string('redacted', 'local_upgradeassistant');
        $values = [
            (string)($CFG->dirroot ?? ''),
            (string)($CFG->dataroot ?? ''),
            (string)($CFG->wwwroot ?? ''),
            (string)($report->targetpath ?? ''),
            (string)($report->dbtype ?? ''),
            (string)($report->dbversion ?? ''),
        ];

        if ($report && !empty($report->summary)) {
            $summary = json_decode($report->summary, true);
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
            $note = str_replace($value, $placeholder, $note);
        }

        $note = preg_replace("#[A-Za-z]:\\\\[^\\s<>\"']+#", $placeholder, $note) ?? $note;
        $note = preg_replace("#/(?:var|home|usr|srv|opt|mnt|xampp|Users|Applications)/[^\\s<>\"']+#", $placeholder, $note) ?? $note;

        return $note;
    }


    /**
     * Check whether Moodle maintenance mode is currently enabled.
     *
     * @return bool
     */
    public static function is_maintenance_mode_active(): bool {
        global $CFG;

        return !empty($CFG->maintenance_enabled)
            || !empty(get_config('core', 'maintenance_enabled'));
    }

    /**
     * Check whether the site is currently using the Boost theme.
     *
     * @return bool
     */
    private static function is_boost_theme_active(): bool {
        global $CFG;

        $theme = (string)get_config('core', 'theme');
        if ($theme === '') {
            $theme = (string)($CFG->theme ?? 'boost');
        }

        return $theme === 'boost';
    }

    /**
     * Default Pro checklist steps.
     *
     * @return array
     */
    private static function default_steps(): array {
        return [
            [
                'stepkey' => 'backupdb',
                'title' => get_string('checkbackupdb', 'local_upgradeassistant'),
                'description' => get_string('checkbackupdbdesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
            [
                'stepkey' => 'backupcode',
                'title' => get_string('checkbackupcode', 'local_upgradeassistant'),
                'description' => get_string('checkbackupcodedesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
            [
                'stepkey' => 'backupdata',
                'title' => get_string('checkbackupdata', 'local_upgradeassistant'),
                'description' => get_string('checkbackupdatadesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
            [
                'stepkey' => 'maintenance',
                'title' => get_string('checkmaintenance', 'local_upgradeassistant'),
                'description' => get_string('checkmaintenancedesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
            [
                'stepkey' => 'boosttheme',
                'title' => get_string('checkboosttheme', 'local_upgradeassistant'),
                'description' => get_string('checkboostthemeauditdesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
            [
                'stepkey' => 'confirmtarget',
                'title' => get_string('checkconfirmtarget', 'local_upgradeassistant'),
                'description' => get_string('checkconfirmtargetdesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
            [
                'stepkey' => 'servercompat',
                'title' => get_string('checkservercompat', 'local_upgradeassistant'),
                'description' => get_string('checkservercompatprodesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
            [
                'stepkey' => 'purgecaches',
                'title' => get_string('checkpurgecaches', 'local_upgradeassistant'),
                'description' => get_string('checkpurgecachesdesc', 'local_upgradeassistant'),
                'required' => 1,
            ],
        ];
    }
}
