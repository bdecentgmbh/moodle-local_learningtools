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
 * Privacy implementation for notes learning tools subplugin.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_note\privacy;

use stdClass;
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
     * Get list of the data privacy summary meta strings.
     *
     * @param collection $collection
     * @return collection
     */
    public static function get_metadata(collection $collection): collection {
        $notemetadata = [
            'userid' => 'privacy:metadata:note:userid',
            'course' => 'privacy:metadata:note:course',
            'coursemodule' => 'privacy:metadata:note:coursemodule',
            'contextlevel' => 'privacy:metadata:note:contextlevel',
            'contextid' => 'privacy:metadata:note:contextid',
            'pagetype' => 'privacy:metadata:note:pagetype',
            'pagetitle' => 'privacy:metadata:note:pagetitle',
            'pageurl' => 'privacy:metadata:note:pageurl',
            'note' => 'privacy:metadata:note:note',
            'timecreated' => 'privacy:metadata:note:timecreated',
            'timemodified' => 'privacy:metadata:note:timemodified'
        ];
        $collection->add_database_table('ltool_note_data', $notemetadata, 'privacy:metadata:notemetadata');

        return $collection;
    }

    /**
     * Check the context user has any created note.
     *
     * @param int $userid
     * @return bool
     */
    public static function user_has_note_data($userid): bool {
        global $DB;

        if ($DB->count_records('ltool_note_data', ['userid' => $userid])) {
            return true;
        }
        return false;
    }

    /**
     * Get the list of contexts that contain user information for the specified user.
     *
     * @param   int           $userid       The user to search.
     * @return  contextlist   $contextlist  The list of contexts used in this plugin.
     */
    public static function get_contexts_for_userid(int $userid) : contextlist {
        $contextlist = new \core_privacy\local\request\contextlist();
        // Check user has stored any notes.
        if (self::user_has_note_data($userid)) {
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
        // Check user has stored any notes.
        if (self::user_has_note_data($context->instanceid)) {
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
                $DB->delete_records_select('ltool_note_data', $sql, $userinparams);
            }
        }
    }

    /**
     * Delete user notes data for multiple context.
     *
     * @param approved_contextlist $contextlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        if (empty($contextlist->count())) {
            return;
        }
        foreach ($contextlist->get_contexts() as $context) {
            if ($context->contextlevel == CONTEXT_USER) {
                // Delete stored user notes.
                self::delete_user_notedata($context->instanceid);
            }
        }
    }

    /**
     * Delete all notes data for all users in the specified context.
     *
     * @param context $context Context to delete data from.
     */
    public static function delete_data_for_all_users_in_context(\context $context) {
        global $DB;
        if ($context->contextlevel == CONTEXT_USER) {
            // Delete all users notes.
            self::delete_user_notedata($context->instanceid);
        }
    }

    /**
     * This does the deletion of user notes data given a userid.
     *
     * @param int $userid The user ID
     */
    private static function delete_user_notedata(int $userid) {
        global $DB;
        if ($DB->delete_records('ltool_note_data', ['userid' => $userid])) {
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

        // List of user notes stored in table.
        $notes = $DB->get_records('ltool_note_data', ['userid' => $user->id]);

        if (empty($notes)) {
            return;
        }
        // Generate the notes list to export.
        $exportdata = array_map(function($note) {
            $modulename = ($note->coursemodule) ? get_coursemodule_from_id('', $note->coursemodule)->name : '-';
            return [
                'contextlevel' => $note->contextlevel,
                'contextid' => $note->contextid,
                'course' => ($note->course == 1) ? 'system' : format_string(get_course($note->course)->fullname),
                'coursemodule' => format_string($modulename),
                'pagetitle' => $note->pagetitle,
                'pagetype' => $note->pagetype,
                'pageurl' => $note->pageurl,
                'note' => $note->note,
                'timecreated' => ($note->timecreated) ? transform::datetime($note->timecreated) : '-',
                'timemodified' => ($note->timemodified) ? transform::datetime($note->timemodified) : '-',
            ];
        }, $notes);

        if (!empty($exportdata)) {
            $context = \context_user::instance($user->id);
            // Fetch the generic module data for the note.
            $contextdata = helper::get_context_data($context, $user);
            $contextdata = (object)array_merge((array)$contextdata, $exportdata);
            writer::with_context($context)->export_data([get_string('privacynote', 'ltool_note').' '.$user->id], $contextdata);
        }

    }

}
