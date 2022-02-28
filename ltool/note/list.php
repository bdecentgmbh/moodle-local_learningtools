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
 * List of the user notes.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_once(dirname(__FILE__).'/lib.php');

require_login();
ltool_note_require_note_status();

$context = context_system::instance();

$selectcourse = optional_param('selectcourse', 0, PARAM_INT);
$activity     = optional_param('activity', 0, PARAM_INT);
$sort         = optional_param('sort', 'date', PARAM_ALPHA);
$sorttype     = optional_param('sorttype', 'asc', PARAM_ALPHA);
$delete       = optional_param('delete', 0, PARAM_INT);
$confirm      = optional_param('confirm', '', PARAM_ALPHANUM);
$courseid     = optional_param('courseid', 0, PARAM_INT);
$childid      = optional_param('userid', 0, PARAM_INT);
$teacher      = optional_param('teacher', 0, PARAM_INT);
$page         = optional_param('page', 0, PARAM_INT);
$perpage      = optional_param('perpage', 10, PARAM_INT);

$coursebase = false;
$userbase = false;

if ($delete) {
    $deleterecord = $DB->get_record('ltool_note_data', ['id' => $delete]);
    if (empty($deleterecord)) {
        redirect(new moodle_url('/'));
    }
}

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


if ($coursebase) {

    $title = get_string('coursenotes', 'local_learningtools');
    $setcontext = context_course::instance($coursebase);
    $courseelement = get_course($coursebase);
    $courselistelement = new core_course_list_element($courseelement);
    $PAGE->set_course($courseelement);
    $PAGE->set_heading($courselistelement->get_formatted_name());
} else if ($childid) {
    $setcontext = context_user::instance($childid);
    $title = get_string('note', 'local_learningtools');
} else {

    $setcontext = context_user::instance($USER->id);
    $title = get_string('note', 'local_learningtools');
}

$PAGE->set_context($setcontext);
$PAGE->set_pagelayout('base');
$PAGE->set_url('/local/learningtools/ltool/note/list.php');
$PAGE->set_title($title);


$urlparams = [];
$pageparams = [];

if ($courseid) {
    $selectcourse = $courseid;
    $urlparams['courseid'] = $courseid;
}

if ($selectcourse) {
    $urlparams['selectcourse'] = $selectcourse;
}
// Activity.
if ($activity) {
    $urlparams['activity'] = $activity;
}

if ($courseid && !$childid) {

    $coursecontext = context_course::instance($courseid);
    $urlparams['courseid'] = $courseid;
    require_capability('ltool/note:viewnote', $coursecontext);
    if ($delete) {
        require_capability('ltool/note:managenote', $coursecontext);
    }

} else if ($childid) {
    $urlparams['courseid'] = $courseid;
    $urlparams['userid'] = $childid;
    if ($teacher) {
        $urlparams['teacher'] = true;
        $coursecontext = context_course::instance($courseid);
        require_capability('ltool/note:viewnote', $coursecontext);
        if ($delete) {
            require_capability('ltool/note:managenote', $coursecontext);
        }
    } else {
        if ($childid != $USER->id) {
            $usercontext = context_user::instance($childid);
            require_capability('ltool/note:viewnote', $usercontext, $USER->id);
            if ($delete) {
                require_capability('ltool/note:managenote', $usercontext, $USER->id);
            }
        } else {
            require_capability('ltool/note:viewownnote', $context);
            if ($delete) {
                if ($deleterecord->userid == $USER->id) {
                    require_capability('ltool/note:manageownnote', $context);
                } else {
                    redirect(new moodle_url('/'));
                }
            }
        }
    }

} else {
    require_capability('ltool/note:viewownnote', $context);
    if ($delete) {
        if ($deleterecord->userid == $USER->id) {
            require_capability('ltool/note:manageownnote', $context);
        } else {
            redirect(new moodle_url('/'));
        }
    }
}

// Page  params.
$urlparams['page'] = $page;
$urlparams['perpage'] = $perpage;
$urlparams['sorttype'] = $sorttype;
$urlparams['sort'] = $sort;

$pageurl = new moodle_url('/local/learningtools/ltool/note/list.php', $urlparams);
// Delete action in note.

if ($delete && confirm_sesskey()) {
    if ($confirm != md5($delete)) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('deletemessage', 'local_learningtools'));

        $optionsyes = array('delete' => $delete, 'confirm' => md5($delete),
            'sesskey' => sesskey());
        $optionsyes = array_merge($optionsyes, $urlparams);
        $deleteurl = new moodle_url($pageurl, $optionsyes);
        $deletebutton = new single_button($deleteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletemsgcheckfull', 'local_learningtools'), $deletebutton, $pageurl);
        echo $OUTPUT->footer();
        die;

    } else if (data_submitted()) {
        $deleterecord = $DB->get_record('ltool_note_data', array('id' => $delete));
        $deleteeventcontext = context::instance_by_id($deleterecord->contextid, MUST_EXIST);
        if ($DB->delete_records('ltool_note_data', ['id' => $delete])) {
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
            $event = \ltool_note\event\ltnote_deleted::create($deleteeventparams);
            $event->trigger();

            \core\session\manager::gc(); // Remove stale sessions.
            redirect($pageurl, get_string('successdeletemessage', 'local_learningtools'),
                null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($pageurl, get_string('deletednotmessage', 'local_learningtools'),
                null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}

// If user is logged in, then use profile navigation in breadcrumbs.
if ($profilenode = $PAGE->settingsnav->find('myprofile', null)) {
    $profilenode->make_active();
}

// Page title.
$PAGE->navbar->add($title);

echo $OUTPUT->header();

if ($userbase) {
    $usercontext = context_user::instance($userbase);
    $userinfo = $DB->get_record('user', array('id' => $userbase));
    $headerinfo = array('heading' => fullname($userinfo), 'user' => $userinfo, 'usercontext' => $usercontext);
    echo $OUTPUT->context_header($headerinfo, 2);
}
echo $OUTPUT->heading($title);

$blockinstance = new \ltool_note\ltool_note_filter($USER->id, $selectcourse,
    $sort, $activity, $courseid, $childid, $teacher, $urlparams, $pageurl);

echo $blockinstance->get_main_body();
echo $OUTPUT->footer();

$createeventparams = [
    'context' => $context,
];

if ($childid) {
    $createeventparams = array_merge($createeventparams, ['relateduserid' => $childid]);
}
// Add event to user view the note.
$event = \ltool_note\event\ltnote_viewed::create($createeventparams);
$event->trigger();
