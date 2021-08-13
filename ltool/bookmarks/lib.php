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
 * ltool plugin "Learning Tools Bookmarks" - library file.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
use core_user\output\myprofile\tree;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot. '/local/learningtools/lib.php');

/**
 * Defines ltool bookmarks nodes for my profile navigation tree.
 *
 * @param \core_user\output\myprofile\tree $tree Tree object
 * @param stdClass $user user object
 * @param bool $iscurrentuser is the user viewing profile, current user ?
 * @param stdClass $course course object
 *
 * @return bool
 */
function ltool_bookmarks_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE, $USER, $DB;

    $context = context_system::instance();
    $userid = optional_param('id', 0, PARAM_INT);
    if (is_bookmarks_status()) {
        if ($iscurrentuser) {
            if (!empty($course)) {
                $coursecontext = context_course::instance($course->id);
                if (has_capability('ltool/bookmarks:viewbookmarks', $coursecontext)) {
                    $bookmarksurl = new moodle_url('/local/learningtools/
                        ltool/bookmarks/userslist.php', array('courseid' => $course->id));
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks',
                    get_string('coursebookmarks', 'local_learningtools'), null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                } else {
                    $bookmarksurl = new moodle_url('/local/learningtools/
                        ltool/bookmarks/list.php', array('courseid' => $course->id,
                        'userid' => $userid));
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools',
                        'bookmarks', get_string('coursebookmarks', 'local_learningtools'),
                    null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                }

            } else {
                if (has_capability('ltool/bookmarks:viewownbookmarks', $context)) {
                    $bookmarksurl = new moodle_url('/local/learningtools/
                        ltool/bookmarks/list.php');
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks',
                        get_string('bookmarks', 'local_learningtools'), null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                }
            }
        } else {

            if (is_parentforchild($user->id, 'ltool/bookmarks:viewbookmarks')) {

                $params = ['userid' => $user->id];
                $title = get_string('bookmarks', 'local_learningtools');
                if (!empty($course)) {
                    $params['courseid'] = $course->id;
                    $title = get_string('coursebookmarks', 'local_learningtools');
                }

                $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php', $params);
                $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks', $title, null, $bookmarksurl);
                $tree->add_node($bookmarksnode);
                return true;
            }
        }
    }
    return true;
}

/**
 * Save the user bookmarks function.
 * @param int page context
 * @param mixed user data
 * @return array bookmarks save info details.
 */
function user_save_bookmarks($contextid, $data) {
    global $DB, $PAGE;
    $context = context_system::instance();
    $PAGE->set_context($context);

    if (confirm_sesskey()) {
        if (!$DB->record_exists('learningtools_bookmarks', array('contextid' =>
            $contextid, 'pagetype' => $data['pagetype'], 'user' => $data['user']))) {

            $record = new stdClass;
            $record->user = $data['user'];
            $record->course = $data['course'];
            $record->coursemodule = $data['coursemodule'];
            $record->contextlevel = $data['contextlevel'];
            $record->contextid = $contextid;
            if ($record->contextlevel == 70) {
                $record->coursemodule = get_coursemodule_id($record);
            } else {
                $record->coursemodule = 0;
            }
            $record->pagetype = $data['pagetype'];
            $record->pageurl = $data['pageurl'];
            $record->timecreated = time();
            // Add event to user create the bookmark.
            $event = \ltool_bookmarks\event\ltbookmarks_created::create([
                'context' => $context,
                'other' => [
                    'courseid' => $course,
                    'pagetype' => $pagetype,
                ]
            ]);
            $event->trigger();
            $bookmarksrecord = $DB->insert_record('learningtools_bookmarks', $record);
            $bookmarksmsg = get_string('successbookmarkmessage', 'local_learningtools');
            $bookmarksstatus = !empty($bookmarksrecord) ? true : false;
            $notificationtype = 'success';
        } else {

            $DB->delete_records('learningtools_bookmarks', array('contextid' => $contextid));
             // Add event to user delete the bookmark.
            $event = \ltool_bookmarks\event\ltbookmarks_deleted::create([
                'context' => $context,
                'other' => [
                    'courseid' => $course,
                    'pagetype' => $pagetype,
                ]
            ]);

            $event->trigger();
            $bookmarksstatus = false;
            $bookmarksmsg = get_string('removebookmarkmessage', 'local_learningtools');
            $notificationtype = 'info';
        }

        return ['bookmarksstatus' => $bookmarksstatus, 'bookmarksmsg' => $bookmarksmsg, 'notificationtype' => $notificationtype];

    }
}

