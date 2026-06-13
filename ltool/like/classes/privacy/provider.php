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
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Privacy implementation for the like learning tool.
 *
 * @package   ltool_like
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_like\privacy;

use context;
use core_privacy\local\metadata\collection;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;

/**
 * Data export and deletion for the ltool_like_data table.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {
    /**
     * Describe the stored data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $metadata = [
            'userid' => 'privacy:metadata:like:userid',
            'course' => 'privacy:metadata:like:course',
            'coursemodule' => 'privacy:metadata:like:coursemodule',
            'contextlevel' => 'privacy:metadata:like:contextlevel',
            'contextid' => 'privacy:metadata:like:contextid',
            'pagetype' => 'privacy:metadata:like:pagetype',
            'pagetitle' => 'privacy:metadata:like:pagetitle',
            'pageurl' => 'privacy:metadata:like:pageurl',
            'reaction' => 'privacy:metadata:like:reaction',
            'timecreated' => 'privacy:metadata:like:timecreated',
            'timemodified' => 'privacy:metadata:like:timemodified',
        ];
        $collection->add_database_table('ltool_like_data', $metadata, 'privacy:metadata:likemetadata');
        return $collection;
    }

    /**
     * Whether the user has any reaction data.
     *
     * @param int $userid
     * @return bool
     */
    public static function user_has_like_data($userid) {
        global $DB;
        return (bool) $DB->count_records('ltool_like_data', ['userid' => $userid]);
    }

    /**
     * Contexts containing the user's data.
     *
     * @param int $userid
     * @return contextlist
     */
    public static function get_contexts_for_userid(int $userid): contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();
        if (self::user_has_like_data($userid)) {
            $contextlist->add_user_context($userid);
        }
        return $contextlist;
    }

    /**
     * Users with data within a context.
     *
     * @param userlist $userlist
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();
        if (!$context instanceof \context_user) {
            return;
        }
        if (self::user_has_like_data($context->instanceid)) {
            $userlist->add_user($context->instanceid);
        }
    }

    /**
     * Delete data for multiple users within a single context.
     *
     * @param approved_userlist $userlist
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context instanceof \context_user) {
            [$userinsql, $userinparams] = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            if (!empty($userinparams)) {
                $DB->delete_records_select('ltool_like_data', "userid {$userinsql}", $userinparams);
            }
        }
    }

    /**
     * Delete data for the user across the given contexts.
     *
     * @param approved_contextlist $contextlist
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER) {
                self::delete_user_data($context->instanceid);
            }
        }
    }

    /**
     * Delete all data for all users in a context.
     *
     * @param context $context
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        if ($context->contextlevel == CONTEXT_USER) {
            self::delete_user_data($context->instanceid);
        }
    }

    /**
     * Delete a user's reaction data.
     *
     * @param int $userid
     * @return bool
     */
    private static function delete_user_data(int $userid) {
        global $DB;
        return (bool) $DB->delete_records('ltool_like_data', ['userid' => $userid]);
    }

    /**
     * Export the user's reaction data.
     *
     * @param approved_contextlist $contextlist
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;
        if (empty($contextlist->count())) {
            return;
        }
        $user = $contextlist->get_user();
        $records = $DB->get_records('ltool_like_data', ['userid' => $user->id]);
        if (empty($records)) {
            return;
        }
        $exportdata = array_map(function ($record) {
            return [
                'contextlevel' => $record->contextlevel,
                'contextid' => $record->contextid,
                'course' => ($record->course == 1) ? 'system' : format_string(get_course($record->course)->fullname),
                'pagetitle' => $record->pagetitle,
                'pagetype' => $record->pagetype,
                'pageurl' => $record->pageurl,
                'reaction' => $record->reaction,
                'timecreated' => ($record->timecreated) ? transform::datetime($record->timecreated) : '-',
            ];
        }, $records);

        $context = \context_user::instance($user->id);
        $contextdata = helper::get_context_data($context, $user);
        $contextdata = (object) array_merge((array) $contextdata, $exportdata);
        writer::with_context($context)->export_data(
            [get_string('privacylike', 'ltool_like') . ' ' . $user->id],
            $contextdata
        );
    }
}
