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

define('BOOKMARK_SHORTNAME', 'bookmarks');

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
                    $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/userslist.php',
                        array('courseid' => $course->id));
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks',
                    get_string('coursebookmarks', 'local_learningtools'), null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                } else {
                    $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php', array('courseid' => $course->id,
                        'userid' => $userid));
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools',
                        'bookmarks', get_string('coursebookmarks', 'local_learningtools'),
                    null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                }

            } else {
                if (has_capability('ltool/bookmarks:viewownbookmarks', $context)) {
                    $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php');
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
            } else if (!empty($course)) {
                $coursecontext = context_course::instance($course->id);
                if (has_capability('ltool/bookmarks:viewbookmarks', $coursecontext)) {
                    $bookmarksurl = new moodle_url('/local/learningtools/ltool/bookmarks/userslist.php',
                        array('courseid' => $course->id));
                    $bookmarksnode = new core_user\output\myprofile\node('learningtools', 'bookmarks',
                    get_string('coursebookmarks', 'local_learningtools'), null, $bookmarksurl);
                    $tree->add_node($bookmarksnode);
                }
            }
        }
    }
    return true;
}

/**
 * Save the user bookmarks function.
 * @param int $contextid page contextid
 * @param mixed $data user data
 * @return array bookmarks save info details.
 */
function user_save_bookmarks($contextid, $data) {
    global $DB, $PAGE;
    $context = context::instance_by_id($contextid, MUST_EXIST);
    $PAGE->set_context($context);
    if (confirm_sesskey()) {

        if (!$DB->record_exists('learningtools_bookmarks', array('contextid' =>
            $contextid, 'pagetype' => $data['pagetype'], 'userid' => $data['user']))) {

            $record = new stdclass();
            $record->userid = $data['user'];
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
            $bookmarksrecord = $DB->insert_record('learningtools_bookmarks', $record);
            // Add event to user create the bookmark.
            $event = \ltool_bookmarks\event\ltbookmarks_created::create([
                'objectid' => $bookmarksrecord,
                'courseid' => $data['course'],
                'context' => $context,
                'other' => [
                    'pagetype' => $data['pagetype'],
                ]
            ]);

            $event->trigger();
            $bookmarksmsg = get_string('successbookmarkmessage', 'local_learningtools');
            $bookmarksstatus = !empty($bookmarksrecord) ? true : false;
            $notificationtype = 'success';
        } else {
            $deleterecord = $DB->get_record('learningtools_bookmarks', array('contextid' => $contextid));
            $DB->delete_records('learningtools_bookmarks', array('contextid' => $contextid));
             // Add event to user delete the bookmark.
            $event = \ltool_bookmarks\event\ltbookmarks_deleted::create([
                'objectid' => $deleterecord->id,
                'courseid' => $data['course'],
                'context' => $context,
                'other' => [
                    'pagetype' => $data['pagetype'],
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
/**
 *
 * Check capability to show bookmarks.
 * @return bool bookmarks status
 */
function check_view_bookmarks() {

    $viewbookmarks = false;
    $context = context_system::instance();
    if (has_capability('ltool/bookmarks:viewownbookmarks', $context) && is_bookmarks_status()) {
        $viewbookmarks = true;
    }

    return $viewbookmarks;
}

/**
 * Load bookmarks js files.
 * @param array $data bookmarks info data
 * @return void
 */
function load_bookmarks_js_config($data) {
    global $PAGE, $USER;
    $pagebookmarks = check_page_bookmarks_exist($PAGE->context->id, $PAGE->pagetype, $USER->id);
    $PAGE->requires->data_for_js('pagebookmarks', $pagebookmarks, true);
    $PAGE->requires->js_call_amd('ltool_bookmarks/learningbookmarks', 'init', array($PAGE->context->id, $data));
}

/**
 * Learning tools template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_bookmarks_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_bookmarks/bookmarks', $templatecontent);
}

/**
 * Check the page bookmarks exists or not.
 * @param int $contextid page context id
 * @param string $pagetype page type
 * @param int $userid user id
 * @return bool page bookmarks status
 */
function check_page_bookmarks_exist($contextid, $pagetype, $userid) {
    global $DB;

    $pagebookmarks = false;
    if ($DB->record_exists('learningtools_bookmarks', array('contextid' => $contextid,
        'pagetype' => $pagetype, 'userid' => $userid))) {
        $pagebookmarks = true;
    }
    return $pagebookmarks;
}


/**
 * Check the bookmarks status.
 * @return bool
 */
function is_bookmarks_status() {
    global $DB;
    $bookmarksrecord = $DB->get_record('local_learningtools_products', array('shortname' => 'bookmarks'));
    if (isset($bookmarksrecord->status) && !empty($bookmarksrecord->status)) {
        return true;
    }
    return false;
}

/**
 * Check the bookmarks view capability
 * @return bool|redirect status
 */
function require_bookmarks_status() {
    if (!is_bookmarks_status()) {
        $url = new moodle_url('/my');
        redirect($url);
    }
    return true;
}

/**
 * Delete the course bookmarks
 * @param int $courseid course id.
 */
function delete_course_bookmarks($courseid) {
    global $DB;
    if ($DB->record_exists('learningtools_bookmarks', array('course' => $courseid))) {
        $DB->delete_records('learningtools_bookmarks', array('course' => $courseid));
    }
}



/**
 * Delete the course bookmarks.
 * @param int $module course module id.
 */
function delete_module_bookmarks($module) {
    global $DB;

    if ($DB->record_exists('learningtools_bookmarks', array('coursemodule' => $module))) {
        $DB->delete_records('learningtools_bookmarks', array('coursemodule' => $module));
    }
}
