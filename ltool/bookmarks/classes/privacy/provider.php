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
 * Privacy implementation for bookmarks learning tools subplugin.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_bookmarks\privacy;

use context;

use core_privacy\local\metadata\collection;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\userlist;
use \core_privacy\local\request\approved_userlist;
use \core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\helper;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
/**
 * The ltool_note modules data export and deletion options.
 */
class provider implements
    \core_privacy\local\metadata\provider,
    \core_privacy\local\request\core_userlist_provider,
    \core_privacy\local\request\plugin\provider {

    /**
     * List of summary for the stored data.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $bookmarksmetadata = [
            'userid' => 'privacy:metadata:bookmarks:userid',
            'course' => 'privacy:metadata:bookmarks:course',
            'coursemodule' => 'privacy:metadata:bookmarks:coursemodule',
            'contextlevel' => 'privacy:metadata:bookmarks:contextlevel',
            'contextid' => 'privacy:metadata:bookmarks:contextid',
            'pagetype' => 'privacy:metadata:bookmarks:pagetype',
            'pagetitle' => 'privacy:metadata:bookmarks:pagetitle',
            'pageurl' => 'privacy:metadata:bookmarks:pageurl',
            'timemodified' => 'privacy:metadata:bookmarks:timemodified'
        ];
        $collection->add_database_table('ltool_bookmarks_data', $bookmarksmetadata, 'privacy:metadata:bookmarksmetadata');

        return $collection;
    }

    /**
     * Check the context user has any bookmarks.
     *
     * @param int $userid
     * @return bool
     */
    public static function user_has_bookmark_data($userid) {
        global $DB;

        if ($DB->count_records('ltool_bookmarks_data', ['userid' => $userid])) {
            return true;
        }
        return false;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param  int         $userid      The user to search.
     * @return contextlist $contextlist The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();

        if (self::user_has_bookmark_data($userid)) {
            $contextlist->add_user_context($userid);
        }

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if (!$context instanceof \context_user) {
            return;
        }

        if (self::user_has_bookmark_data($context->instanceid)) {
            $userlist->add_user($context->instanceid);
        }
    }

    /**
     * Delete multiple users within a single context.
     *
     * @param approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;
        $context = $userlist->get_context();
        if ($context instanceof \context_user) {
            list($userinsql, $userinparams) = $DB->get_in_or_equal($userlist->get_userids(), SQL_PARAMS_NAMED);
            if (!empty($userinparams)) {
                $sql = "userid {$userinsql}";
                $DB->delete_records_select('ltool_bookmarks_data', $sql, $userinparams);
            }
        }
    }

    /**
     * Delete user completion data for multiple context.
     *
     * @param approved_contextlist $contextlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

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
     * Delete all completion data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel == CONTEXT_USER) {
            self::delete_user_data($context->instanceid);
        }
    }

    /**
     * This does the deletion of user data given a userid.
     *
     * @param int $userid The user ID
     */
    private static function delete_user_data(int $userid) {
        global $DB;
        if ($DB->delete_records('ltool_bookmarks_data', ['userid' => $userid])) {
            return true;
        }
        return false;
    }

    /**
     * Export all user data for the specified user, in the specified contexts, using the supplied exporter instance.
     *
     * @param approved_contextlist $contextlist The approved contexts to export information for.
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        if (empty($contextlist->count())) {
            return;
        }
        // Context user.
        $user = $contextlist->get_user();

        $records = $DB->get_records('ltool_bookmarks_data', ['userid' => $user->id]);

        if (empty($records)) {
            return;
        }

        $exportdata = array_map(function($record) {

            $modulename = ($record->coursemodule) ? get_coursemodule_from_id('', $record->coursemodule)->name : '-';

            return [
                'contextlevel' => $record->contextlevel,
                'contextid' => $record->contextid,
                'course' => ($record->course == 1) ? 'system' : format_string(get_course($record->course)->fullname),
                'coursemodule' => format_string($modulename),
                'pagetitle' => $record->pagetitle,
                'pagetype' => $record->pagetype,
                'pageurl' => $record->pageurl,
                'timecreated' => ($record->timecreated) ? transform::datetime($record->timecreated) : '-',
            ];
        }, $records);

        if (!empty($exportdata)) {
            $context = \context_user::instance($user->id);
            // Fetch the generic module data for the bookmarks.
            $contextdata = helper::get_context_data($context, $user);
            $contextdata = (object)array_merge((array)$contextdata, $exportdata);
            writer::with_context($context)->export_data(
                [get_string('privacybookmarks', 'ltool_bookmarks').' '.$user->id],
                $contextdata
            );
        }
    }
}
