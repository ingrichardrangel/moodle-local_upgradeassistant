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

namespace local_upgradeassistant\privacy;

use context;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\user_preference_provider;
use core_privacy\local\request\writer;
use local_upgradeassistant\local\state;

/**
 * Privacy provider for Smart Upgrade Assistant.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
        \core_privacy\local\metadata\provider,
        \core_privacy\local\request\plugin\provider,
        \core_privacy\local\request\core_userlist_provider,
        user_preference_provider {

    /**
     * Describe stored user preferences and database data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_ua_reports',
            [
                'userid' => 'privacy:metadata:local_ua_reports:userid',
                'currentrelease' => 'privacy:metadata:local_ua_reports:currentrelease',
                'currentbranch' => 'privacy:metadata:local_ua_reports:currentbranch',
                'targetrelease' => 'privacy:metadata:local_ua_reports:targetrelease',
                'targetbranch' => 'privacy:metadata:local_ua_reports:targetbranch',
                'targetpath' => 'privacy:metadata:local_ua_reports:targetpath',
                'phpversion' => 'privacy:metadata:local_ua_reports:phpversion',
                'dbtype' => 'privacy:metadata:local_ua_reports:dbtype',
                'dbversion' => 'privacy:metadata:local_ua_reports:dbversion',
                'serverprofile' => 'privacy:metadata:local_ua_reports:serverprofile',
                'summary' => 'privacy:metadata:local_ua_reports:summary',
                'timecreated' => 'privacy:metadata:local_ua_reports:timecreated',
            ],
            'privacy:metadata:local_ua_reports'
        );
        $collection->add_database_table(
            'local_ua_checklist',
            [
                'completedby' => 'privacy:metadata:local_ua_checklist:completedby',
                'completedat' => 'privacy:metadata:local_ua_checklist:completedat',
                'note' => 'privacy:metadata:local_ua_checklist:note',
            ],
            'privacy:metadata:local_ua_checklist'
        );
        $collection->add_database_table(
            'local_ua_items',
            [
                'reportid' => 'privacy:metadata:local_ua_items:reportid',
                'category' => 'privacy:metadata:local_ua_items:category',
                'code' => 'privacy:metadata:local_ua_items:code',
                'title' => 'privacy:metadata:local_ua_items:title',
                'description' => 'privacy:metadata:local_ua_items:description',
                'severity' => 'privacy:metadata:local_ua_items:severity',
                'status' => 'privacy:metadata:local_ua_items:status',
                'recommendation' => 'privacy:metadata:local_ua_items:recommendation',
                'evidence' => 'privacy:metadata:local_ua_items:evidence',
                'timecreated' => 'privacy:metadata:local_ua_items:timecreated',
            ],
            'privacy:metadata:local_ua_items'
        );
        $collection->add_database_table(
            'local_ua_audit',
            [
                'userid' => 'privacy:metadata:local_ua_audit:userid',
                'action' => 'privacy:metadata:local_ua_audit:action',
                'targettype' => 'privacy:metadata:local_ua_audit:targettype',
                'targetid' => 'privacy:metadata:local_ua_audit:targetid',
                'oldvalue' => 'privacy:metadata:local_ua_audit:oldvalue',
                'newvalue' => 'privacy:metadata:local_ua_audit:newvalue',
                'note' => 'privacy:metadata:local_ua_audit:note',
                'ip' => 'privacy:metadata:local_ua_audit:ip',
                'useragent' => 'privacy:metadata:local_ua_audit:useragent',
                'timecreated' => 'privacy:metadata:local_ua_audit:timecreated',
            ],
            'privacy:metadata:local_ua_audit'
        );
        $collection->add_database_table(
            'local_ua_plugins',
            [
                'reportid' => 'privacy:metadata:local_ua_plugins:reportid',
                'component' => 'privacy:metadata:local_ua_plugins:component',
                'plugintype' => 'privacy:metadata:local_ua_plugins:plugintype',
                'pluginname' => 'privacy:metadata:local_ua_plugins:pluginname',
                'version' => 'privacy:metadata:local_ua_plugins:version',
                'releaseinfo' => 'privacy:metadata:local_ua_plugins:releaseinfo',
                'requires' => 'privacy:metadata:local_ua_plugins:requires',
                'targetversion' => 'privacy:metadata:local_ua_plugins:targetversion',
                'compatibility' => 'privacy:metadata:local_ua_plugins:compatibility',
                'statuslabel' => 'privacy:metadata:local_ua_plugins:statuslabel',
                'dependencyjson' => 'privacy:metadata:local_ua_plugins:dependencyjson',
                'evidence' => 'privacy:metadata:local_ua_plugins:evidence',
                'timecreated' => 'privacy:metadata:local_ua_plugins:timecreated',
            ],
            'privacy:metadata:local_ua_plugins'
        );
        $collection->add_database_table(
            'local_ua_rules',
            [
                'rulesetversion' => 'privacy:metadata:local_ua_rules:rulesetversion',
                'source' => 'privacy:metadata:local_ua_rules:source',
                'moodlebranch' => 'privacy:metadata:local_ua_rules:moodlebranch',
                'targetbranch' => 'privacy:metadata:local_ua_rules:targetbranch',
                'ruletype' => 'privacy:metadata:local_ua_rules:ruletype',
                'rulekey' => 'privacy:metadata:local_ua_rules:rulekey',
                'severity' => 'privacy:metadata:local_ua_rules:severity',
                'rulesjson' => 'privacy:metadata:local_ua_rules:rulesjson',
                'recommendation' => 'privacy:metadata:local_ua_rules:recommendation',
                'enabled' => 'privacy:metadata:local_ua_rules:enabled',
            ],
            'privacy:metadata:local_ua_rules'
        );
        $collection->add_database_table(
            'local_ua_exports',
            [
                'userid' => 'privacy:metadata:local_ua_exports:userid',
                'reportid' => 'privacy:metadata:local_ua_exports:reportid',
                'exporttype' => 'privacy:metadata:local_ua_exports:exporttype',
                'contenthash' => 'privacy:metadata:local_ua_exports:contenthash',
                'timecreated' => 'privacy:metadata:local_ua_exports:timecreated',
            ],
            'privacy:metadata:local_ua_exports'
        );
        $collection->add_user_preference(state::PREF, 'privacy:metadata:preference:state');

        return $collection;
    }

    /**
     * Return contexts containing user data.
     *
     * @param int $userid User ID.
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new contextlist();
        $sql = "SELECT ctx.id
                  FROM {context} ctx
                 WHERE ctx.contextlevel = :contextlevel
                   AND (EXISTS (SELECT 1 FROM {local_ua_reports} r WHERE r.userid = :reportuserid)
                    OR EXISTS (SELECT 1 FROM {local_ua_checklist} c WHERE c.completedby = :checkuserid)
                    OR EXISTS (SELECT 1 FROM {local_ua_audit} a WHERE a.userid = :audituserid)
                    OR EXISTS (SELECT 1 FROM {local_ua_exports} e WHERE e.userid = :exportuserid))";
        $contextlist->add_from_sql($sql, [
            'contextlevel' => CONTEXT_SYSTEM,
            'reportuserid' => $userid,
            'checkuserid' => $userid,
            'audituserid' => $userid,
            'exportuserid' => $userid,
        ]);
        return $contextlist;
    }

    /**
     * Export user data.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function export_user_data(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->get_contextids())) {
            return;
        }

        $userid = $contextlist->get_user()->id;
        $context = context_system::instance();
        $reports = $DB->get_records('local_ua_reports', ['userid' => $userid]);
        $checklist = $DB->get_records('local_ua_checklist', ['completedby' => $userid]);
        $audit = $DB->get_records('local_ua_audit', ['userid' => $userid]);
        $exports = $DB->get_records('local_ua_exports', ['userid' => $userid]);
        $items = [];
        $plugins = [];
        $reportids = array_keys($reports);
        if (!empty($reportids)) {
            list($insql, $params) = $DB->get_in_or_equal($reportids, SQL_PARAMS_NAMED);
            $items = $DB->get_records_select('local_ua_items', "reportid $insql", $params);
            $plugins = $DB->get_records_select('local_ua_plugins', "reportid $insql", $params);
        }

        writer::with_context($context)->export_data([
            get_string('pluginname', 'local_upgradeassistant'),
        ], (object)[
            'reports' => array_values($reports),
            'reportitems' => array_values($items),
            'plugininventory' => array_values($plugins),
            'completedchecklistitems' => array_values($checklist),
            'auditentries' => array_values($audit),
            'exports' => array_values($exports),
        ]);
    }

    /**
     * Delete all data for all users in a context.
     *
     * @param context $context Context.
     * @return void
     */
    public static function delete_data_for_all_users_in_context(context $context): void {
        global $DB;

        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }
        foreach (['local_ua_exports', 'local_ua_plugins', 'local_ua_audit', 'local_ua_checklist', 'local_ua_items', 'local_ua_reports'] as $table) {
            if ($DB->get_manager()->table_exists($table)) {
                $DB->delete_records($table);
            }
        }
    }

    /**
     * Delete data for one approved user.
     *
     * @param approved_contextlist $contextlist Approved contexts.
     * @return void
     */
    public static function delete_data_for_user(approved_contextlist $contextlist): void {
        global $DB;

        if (empty($contextlist->get_contextids())) {
            return;
        }
        $userid = $contextlist->get_user()->id;
        // Reports are institutional upgrade evidence. Anonymise ownership instead of deleting
        // the report and its technical evidence when a user is removed for privacy purposes.
        $DB->set_field('local_ua_reports', 'userid', 0, ['userid' => $userid]);
        $DB->execute(
            "UPDATE {local_ua_checklist}
                SET completedby = 0,
                    completedat = 0,
                    note = ''
              WHERE completedby = :userid",
            ['userid' => $userid]
        );
        $DB->delete_records('local_ua_audit', ['userid' => $userid]);
        $DB->delete_records('local_ua_exports', ['userid' => $userid]);
        unset_user_preference(state::PREF, $userid);
    }


    /**
     * Add users with data in the system context to the supplied userlist.
     *
     * @param userlist $userlist Userlist.
     * @return void
     */
    public static function get_users_in_context(userlist $userlist): void {
        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ua_reports} WHERE userid > 0', []);
        $userlist->add_from_sql('userid', 'SELECT completedby AS userid FROM {local_ua_checklist} WHERE completedby > 0', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ua_audit} WHERE userid > 0', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_ua_exports} WHERE userid > 0', []);
    }

    /**
     * Delete data for multiple approved users.
     *
     * @param approved_userlist $userlist Approved userlist.
     * @return void
     */
    public static function delete_data_for_users(approved_userlist $userlist): void {
        global $DB;

        $context = $userlist->get_context();
        if ($context->contextlevel !== CONTEXT_SYSTEM) {
            return;
        }

        $userids = $userlist->get_userids();
        if (empty($userids)) {
            return;
        }

        list($insql, $params) = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        // Preserve institutional upgrade evidence while removing the personal link.
        $DB->execute(
            "UPDATE {local_ua_reports}
                SET userid = 0
              WHERE userid $insql",
            $params
        );

        $DB->execute(
            "UPDATE {local_ua_checklist}
                SET completedby = 0,
                    completedat = 0,
                    note = ''
              WHERE completedby $insql",
            $params
        );
        $DB->delete_records_select('local_ua_audit', "userid $insql", $params);
        $DB->delete_records_select('local_ua_exports', "userid $insql", $params);
        foreach ($userids as $userid) {
            unset_user_preference(state::PREF, $userid);
        }
    }

    /**
     * Export user preferences.
     *
     * @param int $userid User ID.
     * @return void
     */
    public static function export_user_preferences(int $userid): void {
        $value = get_user_preferences(state::PREF, null, $userid);

        if ($value !== null) {
            writer::export_user_preference(
                'local_upgradeassistant',
                state::PREF,
                $value,
                get_string('privacy:metadata:preference:state', 'local_upgradeassistant')
            );
        }
    }
}
