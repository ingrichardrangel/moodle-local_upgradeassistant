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
 * Privacy provider for Upgrade Assistant.
 *
 * @package    local_upgradeassistant
 * @copyright  2026 Richard Rangel
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider,
    user_preference_provider {
    /**
     * Describe stored user preferences and database data.
     *
     * @param collection $collection Metadata collection.
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $collection->add_database_table(
            'local_upgradeassistant_rep',
            [
                'userid' => 'privacy:metadata:local_upgradeassistant_rep:userid',
                'currentrelease' => 'privacy:metadata:local_upgradeassistant_rep:currentrelease',
                'currentbranch' => 'privacy:metadata:local_upgradeassistant_rep:currentbranch',
                'targetrelease' => 'privacy:metadata:local_upgradeassistant_rep:targetrelease',
                'targetbranch' => 'privacy:metadata:local_upgradeassistant_rep:targetbranch',
                'targetpath' => 'privacy:metadata:local_upgradeassistant_rep:targetpath',
                'phpversion' => 'privacy:metadata:local_upgradeassistant_rep:phpversion',
                'dbtype' => 'privacy:metadata:local_upgradeassistant_rep:dbtype',
                'dbversion' => 'privacy:metadata:local_upgradeassistant_rep:dbversion',
                'serverprofile' => 'privacy:metadata:local_upgradeassistant_rep:serverprofile',
                'summary' => 'privacy:metadata:local_upgradeassistant_rep:summary',
                'timecreated' => 'privacy:metadata:local_upgradeassistant_rep:timecreated',
            ],
            'privacy:metadata:local_upgradeassistant_rep'
        );
        $collection->add_database_table(
            'local_upgradeassistant_check',
            [
                'completedby' => 'privacy:metadata:local_upgradeassistant_check:completedby',
                'completedat' => 'privacy:metadata:local_upgradeassistant_check:completedat',
                'note' => 'privacy:metadata:local_upgradeassistant_check:note',
            ],
            'privacy:metadata:local_upgradeassistant_check'
        );
        $collection->add_database_table(
            'local_upgradeassistant_item',
            [
                'reportid' => 'privacy:metadata:local_upgradeassistant_item:reportid',
                'category' => 'privacy:metadata:local_upgradeassistant_item:category',
                'code' => 'privacy:metadata:local_upgradeassistant_item:code',
                'title' => 'privacy:metadata:local_upgradeassistant_item:title',
                'description' => 'privacy:metadata:local_upgradeassistant_item:description',
                'severity' => 'privacy:metadata:local_upgradeassistant_item:severity',
                'status' => 'privacy:metadata:local_upgradeassistant_item:status',
                'recommendation' => 'privacy:metadata:local_upgradeassistant_item:recommendation',
                'evidence' => 'privacy:metadata:local_upgradeassistant_item:evidence',
                'timecreated' => 'privacy:metadata:local_upgradeassistant_item:timecreated',
            ],
            'privacy:metadata:local_upgradeassistant_item'
        );
        $collection->add_database_table(
            'local_upgradeassistant_audit',
            [
                'userid' => 'privacy:metadata:local_upgradeassistant_audit:userid',
                'action' => 'privacy:metadata:local_upgradeassistant_audit:action',
                'targettype' => 'privacy:metadata:local_upgradeassistant_audit:targettype',
                'targetid' => 'privacy:metadata:local_upgradeassistant_audit:targetid',
                'oldvalue' => 'privacy:metadata:local_upgradeassistant_audit:oldvalue',
                'newvalue' => 'privacy:metadata:local_upgradeassistant_audit:newvalue',
                'note' => 'privacy:metadata:local_upgradeassistant_audit:note',
                'ip' => 'privacy:metadata:local_upgradeassistant_audit:ip',
                'useragent' => 'privacy:metadata:local_upgradeassistant_audit:useragent',
                'timecreated' => 'privacy:metadata:local_upgradeassistant_audit:timecreated',
            ],
            'privacy:metadata:local_upgradeassistant_audit'
        );
        $collection->add_database_table(
            'local_upgradeassistant_plug',
            [
                'reportid' => 'privacy:metadata:local_upgradeassistant_plug:reportid',
                'component' => 'privacy:metadata:local_upgradeassistant_plug:component',
                'plugintype' => 'privacy:metadata:local_upgradeassistant_plug:plugintype',
                'pluginname' => 'privacy:metadata:local_upgradeassistant_plug:pluginname',
                'version' => 'privacy:metadata:local_upgradeassistant_plug:version',
                'releaseinfo' => 'privacy:metadata:local_upgradeassistant_plug:releaseinfo',
                'requires' => 'privacy:metadata:local_upgradeassistant_plug:requires',
                'targetversion' => 'privacy:metadata:local_upgradeassistant_plug:targetversion',
                'compatibility' => 'privacy:metadata:local_upgradeassistant_plug:compatibility',
                'statuslabel' => 'privacy:metadata:local_upgradeassistant_plug:statuslabel',
                'dependencyjson' => 'privacy:metadata:local_upgradeassistant_plug:dependencyjson',
                'evidence' => 'privacy:metadata:local_upgradeassistant_plug:evidence',
                'timecreated' => 'privacy:metadata:local_upgradeassistant_plug:timecreated',
            ],
            'privacy:metadata:local_upgradeassistant_plug'
        );
        $collection->add_database_table(
            'local_upgradeassistant_rules',
            [
                'rulesetversion' => 'privacy:metadata:local_upgradeassistant_rules:rulesetversion',
                'source' => 'privacy:metadata:local_upgradeassistant_rules:source',
                'moodlebranch' => 'privacy:metadata:local_upgradeassistant_rules:moodlebranch',
                'targetbranch' => 'privacy:metadata:local_upgradeassistant_rules:targetbranch',
                'ruletype' => 'privacy:metadata:local_upgradeassistant_rules:ruletype',
                'rulekey' => 'privacy:metadata:local_upgradeassistant_rules:rulekey',
                'severity' => 'privacy:metadata:local_upgradeassistant_rules:severity',
                'rulesjson' => 'privacy:metadata:local_upgradeassistant_rules:rulesjson',
                'recommendation' => 'privacy:metadata:local_upgradeassistant_rules:recommendation',
                'enabled' => 'privacy:metadata:local_upgradeassistant_rules:enabled',
            ],
            'privacy:metadata:local_upgradeassistant_rules'
        );
        $collection->add_database_table(
            'local_upgradeassistant_expt',
            [
                'userid' => 'privacy:metadata:local_upgradeassistant_expt:userid',
                'reportid' => 'privacy:metadata:local_upgradeassistant_expt:reportid',
                'exporttype' => 'privacy:metadata:local_upgradeassistant_expt:exporttype',
                'contenthash' => 'privacy:metadata:local_upgradeassistant_expt:contenthash',
                'timecreated' => 'privacy:metadata:local_upgradeassistant_expt:timecreated',
            ],
            'privacy:metadata:local_upgradeassistant_expt'
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
                   AND (EXISTS (SELECT 1 FROM {local_upgradeassistant_rep} r WHERE r.userid = :reportuserid)
                    OR EXISTS (SELECT 1 FROM {local_upgradeassistant_check} c WHERE c.completedby = :checkuserid)
                    OR EXISTS (SELECT 1 FROM {local_upgradeassistant_audit} a WHERE a.userid = :audituserid)
                    OR EXISTS (SELECT 1 FROM {local_upgradeassistant_expt} e WHERE e.userid = :exportuserid))";
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
        $reports = $DB->get_records('local_upgradeassistant_rep', ['userid' => $userid]);
        $checklist = $DB->get_records('local_upgradeassistant_check', ['completedby' => $userid]);
        $audit = $DB->get_records('local_upgradeassistant_audit', ['userid' => $userid]);
        $exports = $DB->get_records('local_upgradeassistant_expt', ['userid' => $userid]);
        $items = [];
        $plugins = [];
        $reportids = array_keys($reports);
        if (!empty($reportids)) {
            [$insql, $params] = $DB->get_in_or_equal($reportids, SQL_PARAMS_NAMED);
            $items = $DB->get_records_select('local_upgradeassistant_item', "reportid $insql", $params);
            $plugins = $DB->get_records_select('local_upgradeassistant_plug', "reportid $insql", $params);
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
        foreach (
            ['local_upgradeassistant_expt', 'local_upgradeassistant_plug', 'local_upgradeassistant_audit',
            'local_upgradeassistant_check', 'local_upgradeassistant_item', 'local_upgradeassistant_rep'] as $table
        ) {
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
        $DB->set_field('local_upgradeassistant_rep', 'userid', 0, ['userid' => $userid]);
        $DB->execute(
            "UPDATE {local_upgradeassistant_check}
                SET completedby = 0,
                    completedat = 0,
                    note = ''
              WHERE completedby = :userid",
            ['userid' => $userid]
        );
        $DB->delete_records('local_upgradeassistant_audit', ['userid' => $userid]);
        $DB->delete_records('local_upgradeassistant_expt', ['userid' => $userid]);
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

        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_upgradeassistant_rep} WHERE userid > 0', []);
        $userlist->add_from_sql(
            'userid',
            'SELECT completedby AS userid FROM {local_upgradeassistant_check} WHERE completedby > 0',
            []
        );
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_upgradeassistant_audit} WHERE userid > 0', []);
        $userlist->add_from_sql('userid', 'SELECT userid FROM {local_upgradeassistant_expt} WHERE userid > 0', []);
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

        [$insql, $params] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
        // Preserve institutional upgrade evidence while removing the personal link.
        $DB->execute(
            "UPDATE {local_upgradeassistant_rep}
                SET userid = 0
              WHERE userid $insql",
            $params
        );

        $DB->execute(
            "UPDATE {local_upgradeassistant_check}
                SET completedby = 0,
                    completedat = 0,
                    note = ''
              WHERE completedby $insql",
            $params
        );
        $DB->delete_records_select('local_upgradeassistant_audit', "userid $insql", $params);
        $DB->delete_records_select('local_upgradeassistant_expt', "userid $insql", $params);
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
