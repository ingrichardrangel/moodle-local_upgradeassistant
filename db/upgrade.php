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
 * Upgrade script for Upgrade Assistant.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Upgrade local_upgradeassistant database.
 *
 * @param int $oldversion Old plugin version.
 * @return bool
 */
function xmldb_local_upgradeassistant_upgrade(int $oldversion): bool {
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2026061600) {
        $table = new xmldb_table('local_ua_reports');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('uuid', XMLDB_TYPE_CHAR, '36', null, XMLDB_NOTNULL, null, null);
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('currentrelease', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('currentbranch', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            $table->add_field('targetrelease', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('targetbranch', XMLDB_TYPE_CHAR, '20', null, null, null, null);
            $table->add_field('targetpath', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('phpversion', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('dbtype', XMLDB_TYPE_CHAR, '40', null, null, null, null);
            $table->add_field('dbversion', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('serverprofile', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('riskscore', XMLDB_TYPE_INTEGER, '3', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('risklevel', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'low');
            $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'draft');
            $table->add_field('summary', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_index('uuid_uix', XMLDB_INDEX_UNIQUE, ['uuid']);
            $table->add_index('userid_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);
            $table->add_index('created_ix', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_ua_items');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('category', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
            $table->add_field('code', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('severity', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'info');
            $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'open');
            $table->add_field('recommendation', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('evidence', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('report_fk', XMLDB_KEY_FOREIGN, ['reportid'], 'local_ua_reports', ['id']);
            $table->add_index('report_ix', XMLDB_INDEX_NOTUNIQUE, ['reportid']);
            $table->add_index('severity_ix', XMLDB_INDEX_NOTUNIQUE, ['severity']);
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_ua_checklist');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('stepkey', XMLDB_TYPE_CHAR, '60', null, XMLDB_NOTNULL, null, null);
            $table->add_field('title', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, null);
            $table->add_field('description', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('required', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('status', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'pending');
            $table->add_field('completedby', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('completedat', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('note', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('sortorder', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('report_fk', XMLDB_KEY_FOREIGN, ['reportid'], 'local_ua_reports', ['id']);
            $table->add_index('report_ix', XMLDB_INDEX_NOTUNIQUE, ['reportid']);
            $table->add_index('status_ix', XMLDB_INDEX_NOTUNIQUE, ['status']);
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_ua_audit');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('action', XMLDB_TYPE_CHAR, '60', null, XMLDB_NOTNULL, null, null);
            $table->add_field('targettype', XMLDB_TYPE_CHAR, '60', null, XMLDB_NOTNULL, null, null);
            $table->add_field('targetid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('oldvalue', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('newvalue', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('note', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('ip', XMLDB_TYPE_CHAR, '45', null, null, null, null);
            $table->add_field('useragent', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('report_fk', XMLDB_KEY_FOREIGN, ['reportid'], 'local_ua_reports', ['id']);
            $table->add_index('report_ix', XMLDB_INDEX_NOTUNIQUE, ['reportid']);
            $table->add_index('userid_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);
            $table->add_index('created_ix', XMLDB_INDEX_NOTUNIQUE, ['timecreated']);
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026061600, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026061604) {
        $table = new xmldb_table('local_ua_rules');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('rulesetversion', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
            $table->add_field('source', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, 'builtin');
            $table->add_field('moodlebranch', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
            $table->add_field('targetbranch', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
            $table->add_field('ruletype', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
            $table->add_field('rulekey', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('severity', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'critical');
            $table->add_field('rulesjson', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('recommendation', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('enabled', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '1');
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_index('branch_ix', XMLDB_INDEX_NOTUNIQUE, ['targetbranch']);
            $table->add_index('enabled_ix', XMLDB_INDEX_NOTUNIQUE, ['enabled']);
            $dbman->create_table($table);
        }

        $table = new xmldb_table('local_ua_plugins');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('component', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('plugintype', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, null);
            $table->add_field('pluginname', XMLDB_TYPE_CHAR, '100', null, XMLDB_NOTNULL, null, null);
            $table->add_field('version', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('releaseinfo', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('requires', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('targetversion', XMLDB_TYPE_CHAR, '100', null, null, null, null);
            $table->add_field('compatibility', XMLDB_TYPE_CHAR, '40', null, XMLDB_NOTNULL, null, 'unknown');
            $table->add_field('statuslabel', XMLDB_TYPE_CHAR, '255', null, null, null, null);
            $table->add_field('dependencyjson', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('evidence', XMLDB_TYPE_TEXT, null, null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('report_fk', XMLDB_KEY_FOREIGN, ['reportid'], 'local_ua_reports', ['id']);
            $table->add_index('report_ix', XMLDB_INDEX_NOTUNIQUE, ['reportid']);
            $table->add_index('component_ix', XMLDB_INDEX_NOTUNIQUE, ['component']);
            $table->add_index('compat_ix', XMLDB_INDEX_NOTUNIQUE, ['compatibility']);
            $dbman->create_table($table);
        }
        $table = new xmldb_table('local_ua_exports');
        if (!$dbman->table_exists($table)) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
            $table->add_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_field('exporttype', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, null);
            $table->add_field('contenthash', XMLDB_TYPE_CHAR, '64', null, null, null, null);
            $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
            $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
            $table->add_key('report_fk', XMLDB_KEY_FOREIGN, ['reportid'], 'local_ua_reports', ['id']);
            $table->add_index('report_ix', XMLDB_INDEX_NOTUNIQUE, ['reportid']);
            $table->add_index('type_ix', XMLDB_INDEX_NOTUNIQUE, ['exporttype']);
            $dbman->create_table($table);
        }

        upgrade_plugin_savepoint(true, 2026061604, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026061605) {
        $table = new xmldb_table('local_ua_support');
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        upgrade_plugin_savepoint(true, 2026061605, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026061607) {
        upgrade_plugin_savepoint(true, 2026061607, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026061608) {
        $table = new xmldb_table('local_ua_checklist');
        if ($dbman->table_exists($table)) {
            $index = new xmldb_index('completedby_ix', XMLDB_INDEX_NOTUNIQUE, ['completedby']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }

            $index = new xmldb_index('report_step_ix', XMLDB_INDEX_NOTUNIQUE, ['reportid', 'stepkey']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }

        $table = new xmldb_table('local_ua_exports');
        if ($dbman->table_exists($table)) {
            $index = new xmldb_index('userid_ix', XMLDB_INDEX_NOTUNIQUE, ['userid']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }

        $table = new xmldb_table('local_ua_rules');
        if ($dbman->table_exists($table)) {
            $index = new xmldb_index('target_enabled_ix', XMLDB_INDEX_NOTUNIQUE, ['targetbranch', 'enabled']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
            \local_upgradeassistant\local\rule_engine::seed_builtin_rules();
        }

        upgrade_plugin_savepoint(true, 2026061608, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026061609) {
        // No schema changes. This step documents the review-hardening release that
        // tightened capability-aware UI, redacted exports, privacy metadata and user lookup performance.
        upgrade_plugin_savepoint(true, 2026061609, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026061610) {
        foreach (['local_ua_items', 'local_ua_checklist', 'local_ua_audit', 'local_ua_plugins', 'local_ua_exports'] as $tablename) {
            $table = new xmldb_table($tablename);
            $field = new xmldb_field('reportid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
            if ($dbman->table_exists($table) && $dbman->field_exists($table, $field)) {
                $dbman->change_field_default($table, $field);
            }
        }

        $table = new xmldb_table('local_ua_rules');
        if ($dbman->table_exists($table)) {
            $duplicates = $DB->get_records_sql(
                "SELECT MIN(id) AS keepid, targetbranch, ruletype, rulekey, COUNT(1) AS duplicatecount
                   FROM {local_ua_rules}
               GROUP BY targetbranch, ruletype, rulekey
                 HAVING COUNT(1) > 1"
            );
            foreach ($duplicates as $duplicate) {
                $DB->delete_records_select(
                    'local_ua_rules',
                    'targetbranch = :targetbranch AND ruletype = :ruletype AND rulekey = :rulekey AND id <> :keepid',
                    [
                        'targetbranch' => $duplicate->targetbranch,
                        'ruletype' => $duplicate->ruletype,
                        'rulekey' => $duplicate->rulekey,
                        'keepid' => $duplicate->keepid,
                    ]
                );
            }

            $index = new xmldb_index('target_type_key_uix', XMLDB_INDEX_UNIQUE, ['targetbranch', 'ruletype', 'rulekey']);
            if (!$dbman->index_exists($table, $index)) {
                $dbman->add_index($table, $index);
            }
        }

        upgrade_plugin_savepoint(true, 2026061610, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026071000) {
        // No schema changes. This release reorganises the interface into a four-step
        // assistant and keeps the operational and auditable checklists synchronised.
        upgrade_plugin_savepoint(true, 2026071000, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026071003) {
        // No schema changes. Plugin findings now distinguish official core removals,
        // verify declared dependencies and support auditable administrator reviews.
        upgrade_plugin_savepoint(true, 2026071003, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026071004) {
        // No schema changes. Public-directory targets now use a production cutover flow:
        // preserve the old root, rename the prepared root and update the web DocumentRoot.
        upgrade_plugin_savepoint(true, 2026071004, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026092306) {
        // Preserve reports and audit history while adopting the Moodle plugin table prefix.
        $tablemap = [
            'local_ua_reports' => 'local_upgradeassistant_rep',
            'local_ua_items' => 'local_upgradeassistant_item',
            'local_ua_checklist' => 'local_upgradeassistant_check',
            'local_ua_audit' => 'local_upgradeassistant_audit',
            'local_ua_rules' => 'local_upgradeassistant_rules',
            'local_ua_plugins' => 'local_upgradeassistant_plug',
            'local_ua_exports' => 'local_upgradeassistant_expt',
        ];
        foreach ($tablemap as $oldname => $newname) {
            $oldtable = new xmldb_table($oldname);
            $newtable = new xmldb_table($newname);
            if ($dbman->table_exists($oldtable) && !$dbman->table_exists($newtable)) {
                $dbman->rename_table($oldtable, $newname);
            }
        }
        upgrade_plugin_savepoint(true, 2026092306, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026092400) {
        // Recalculate stored risk without treating an administrator's review as remediation.
        $offset = 0;
        do {
            $reports = $DB->get_records(
                'local_upgradeassistant_rep',
                null,
                'id ASC',
                'id, riskscore, risklevel, summary',
                $offset,
                100
            );
            foreach ($reports as $report) {
                $items = $DB->get_records(
                    'local_upgradeassistant_item',
                    ['reportid' => $report->id],
                    '',
                    'id, severity, status'
                );
                $findings = [];
                foreach ($items as $item) {
                    $findings[] = ['severity' => $item->severity, 'status' => $item->status];
                }
                $risk = \local_upgradeassistant\local\risk_assessor::assess($findings);
                $summary = json_decode((string)$report->summary, true);
                $changed = (int)$report->riskscore !== $risk['score'] || $report->risklevel !== $risk['level'];
                $update = (object)['id' => $report->id, 'riskscore' => $risk['score'], 'risklevel' => $risk['level']];
                if (is_array($summary) && isset($summary['risk']) && $summary['risk'] !== $risk) {
                    $summary['risk'] = $risk;
                    $update->summary = json_encode($summary, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    $changed = true;
                }
                if ($changed) {
                    $DB->update_record('local_upgradeassistant_rep', $update);
                }
            }
            $offset += count($reports);
        } while (count($reports) === 100);
        upgrade_plugin_savepoint(true, 2026092400, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026092401) {
        // No schema changes. Existing reports can now be rechecked in place.
        upgrade_plugin_savepoint(true, 2026092401, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026092402) {
        // No schema changes. Rechecked findings now explain verified resolution.
        upgrade_plugin_savepoint(true, 2026092402, 'local', 'upgradeassistant');
    }

    if ($oldversion < 2026092403) {
        // Update the visible product name while retaining the existing component and data.
        upgrade_plugin_savepoint(true, 2026092403, 'local', 'upgradeassistant');
    }

    return true;
}
