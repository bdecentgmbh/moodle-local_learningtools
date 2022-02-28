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
 * List of the user bookmarks.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_once(dirname(__FILE__).'/lib.php');
require_once($CFG->dirroot. '/course/classes/list_element.php');
require_login();
ltool_bookmarks_require_bookmarks_status();

$selectcourse = optional_param('selectcourse', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', '', PARAM_ALPHANUM);
$page = optional_param('page', 0, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$childid = optional_param('userid', 0, PARAM_INT);
$teacher = optional_param('teacher', 0, PARAM_INT);
$sort = optional_param('sort', 'date', PARAM_ALPHA);
$sorttype = optional_param('sorttype', 'asc', PARAM_ALPHA);
$context = context_system::instance();

if ($delete) {
    $deleterecord = $DB->get_record('ltool_bookmarks_data', ['id' => $delete]);
    if (empty($deleterecord)) {
        redirect(new moodle_url('/'));
    }
}

$coursebase = false;
$userbase = false;

if ($courseid || $selectcourse) {
    if ($courseid) {
        $coursebase = $courseid;
    } else if ($selectcourse) {
        $coursebase = $selectcourse;
    }

    if ($childid) {
        $userbase = $childid;
    } else {
        $userbase = $USER->id;
    }
}

if ($courseid) {
     $selectcourse = $courseid;
}
if ($coursebase) {

    $title = get_string('coursebookmarks', 'local_learningtools');
    $setcontext = context_course::instance($coursebase);
    $courseelement = get_course($coursebase);
    $courselistelement = new core_course_list_element($courseelement);
    $PAGE->set_course($courseelement);
    $PAGE->set_heading($courselistelement->get_formatted_name());
} else if ($childid) {
    $setcontext = context_user::instance($childid);
    $title = get_string('bookmarks', 'local_learningtools');
} else {
    $setcontext = context_user::instance($USER->id);
    $title = get_string('bookmarks', 'local_learningtools');
}

$PAGE->set_context($setcontext);
$PAGE->set_url('/local/learningtools/ltool/bookmarks/list.php');
$PAGE->set_title($title);

// If user is logged in, then use profile navigation in breadcrumbs.
if ($profilenode = $PAGE->settingsnav->find('myprofile', null)) {
    $profilenode->make_active();
}

$PAGE->navbar->add($title);
$urlparams = [];

if ($courseid && !$childid) {
    $urlparams['courseid'] = $courseid;
    $coursecontext = context_course::instance($courseid);
    require_capability('ltool/bookmarks:viewbookmarks', $coursecontext);
    if ($delete) {
        require_capability('ltool/bookmarks:managebookmarks', $coursecontext);
    }
} else if ($childid) {

     $urlparams['courseid'] = $courseid;
     $urlparams['userid'] = $childid;
    if ($teacher) {
        $urlparams['teacher'] = true;
        $coursecontext = context_course::instance($courseid);
        require_capability('ltool/bookmarks:viewbookmarks', $coursecontext);
        if ($delete) {
            require_capability('ltool/bookmarks:managebookmarks', $coursecontext);
        }
    } else {
        if ($childid != $USER->id) {
            $usercontext = context_user::instance($childid);
            require_capability('ltool/bookmarks:viewbookmarks', $usercontext, $USER->id);
            if ($delete) {
                require_capability('ltool/bookmarks:managebookmarks', $usercontext, $USER->id);
            }
        } else {
            require_capability('ltool/bookmarks:viewownbookmarks', $context);
            if ($delete) {
                if ($deleterecord->userid == $USER->id) {
                    require_capability('ltool/bookmarks:manageownbookmarks', $context);
                } else {
                    require_capability('ltool/bookmarks:managebookmarks', $context);
                }
            }
        }
    }
} else {
    require_capability('ltool/bookmarks:viewownbookmarks', $context);
    if ($delete) {
        if ($deleterecord->userid == $USER->id) {
            require_capability('ltool/bookmarks:manageownbookmarks', $context);
        } else {
            require_capability('ltool/bookmarks:managebookmarks', $context);
        }
    }
}

if ($selectcourse) {
    $urlparams['selectcourse'] = $selectcourse;
}

if ($sort) {
    $urlparams['sort'] = $sort;
}

if ($sorttype) {
    $urlparams['sorttype'] = $sorttype;
}

$baseurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php', $urlparams);

if ($delete && confirm_sesskey()) {
    if ($confirm != md5($delete)) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('deletemessage', 'local_learningtools'));
        $optionsyes = array('delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey());
        $optionsyes = array_merge($optionsyes, $urlparams);
        $deleteurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php', $optionsyes);
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletemsgcheckfullbookmarks', 'local_learningtools'), $deletebutton, $baseurl);
        echo $OUTPUT->footer();
        die;
    } else if (data_submitted()) {
        $deleterecord = $DB->get_record('ltool_bookmarks_data', ['id' => $delete]);
        $deleteeventcontext = context::instance_by_id($deleterecord->contextid, MUST_EXIST);
        if ($DB->delete_records('ltool_bookmarks_data', ['id' => $delete])) {
            $eventcourseid = local_learningtools_get_eventlevel_courseid($deleteeventcontext, $deleterecord->course);
            $deleteeventparams = [
                'objectid' => $deleterecord->id,
                'courseid' => $eventcourseid,
                'context' => $deleteeventcontext,
                'other' => [
                    'pagetype' => $deleterecord->pagetype,
                ]
            ];

            if ($childid) {
                $deleteeventparams = array_merge($deleteeventparams, ['relateduserid' => $childid]);
            }
            // Add event to user delete the bookmark.
            $event = \ltool_bookmarks\event\ltbookmarks_deleted::create($deleteeventparams);

            $event->trigger();
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($baseurl, get_string('successdeletemessage', 'local_learningtools'),
                null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($baseurl, get_string('deletednotmessage', 'local_learningtools'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}

echo $OUTPUT->header();

if ($userbase) {

    $usercontext = context_user::instance($userbase);
    $userinfo = $DB->get_record('user', array('id' => $userbase));
    $headerinfo = array('heading' => fullname($userinfo), 'user' => $userinfo, 'usercontext' => $usercontext);
    echo $OUTPUT->context_header($headerinfo, 2);
}

echo $OUTPUT->heading($title);

$sqlconditions = '';
$sqlparams = [];
$templatecontent = [];
if (!empty($courseid) && !$childid) {
    $students = local_learningtools_get_students_incourse($courseid);
    if (!empty($students)) {
        list($studentcondition, $sqlparams) = $DB->get_in_or_equal($students, SQL_PARAMS_NAMED);
        $sqlconditions .= 'userid '. $studentcondition;
    }
} else if ($childid) {
    $sqlconditions .= 'userid = :childid';
    $sqlparams['childid'] = $childid;
} else {
    $sqlconditions .= 'userid = :userid';
    $sqlparams['userid'] = $USER->id;
}


$blockinstance = new \ltool_bookmarks\bookmarkstool_filter($USER->id, $courseid, $childid, $teacher, $urlparams, $baseurl);

if (!$courseid) {
    $templatecontent['coursefilter'] = $blockinstance->get_course_selector($selectcourse, $sqlconditions, $sqlparams);
}

if ($selectcourse) {
    $courseconditions = ' AND course = :courseid';
    $courseparams['courseid'] = $selectcourse;
    $sqlconditions .= $courseconditions;
    $sqlparams = array_merge($sqlparams, $courseparams);
    $urlparams['selectcourse'] = $selectcourse;
}


$templatecontent['sortfilter'] = $blockinstance->get_sort_instance();
$maindata = $blockinstance->get_main_body($sqlconditions, $sqlparams, $sort, $sorttype, $page, $perpage);
$templatecontent['showbookmarks'] = true;
$templatecontent = array_merge($templatecontent, $maindata);

echo $OUTPUT->render_from_template('ltool_bookmarks/ltbookmarks', $templatecontent);
echo $OUTPUT->footer();

$createeventparams = [
    'context' => $context,
];

if ($childid) {
    $createeventparams = array_merge($createeventparams, ['relateduserid' => $childid]);
}
// Add event to user view the bookmark.
$event = \ltool_bookmarks\event\ltbookmarks_viewed::create($createeventparams);

$event->trigger();
