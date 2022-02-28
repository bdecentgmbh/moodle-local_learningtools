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
 * ltool_note edit notes
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__).'/../../../../config.php');
require_login();
require_once(dirname(__FILE__).'/lib.php');

$context = context_system::instance();
$title = get_string('note', 'local_learningtools');
$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/note/editlist.php');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);

$edit = optional_param('edit', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$childid = optional_param('userid', 0, PARAM_INT);
$teacher = optional_param('teacher', 0, PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_URL);

if ($edit) {
    $editedrecord = $DB->get_record('ltool_note_data', array('id' => $edit));
    if (empty($editedrecord)) {
        redirect(new moodle_url('/'));
    }
}
$urlparams = [];
if ($courseid && !$childid) {
    $urlparams['courseid'] = $courseid;
    $coursecontext = context_course::instance($courseid);
    require_capability('ltool/note:managenote', $coursecontext);
} else if ($childid) {
    $urlparams['userid'] = $childid;
    if ($teacher) {
        $urlparams['courseid'] = $courseid;
        $urlparams['teacher'] = true;
        $coursecontext = context_course::instance($courseid);
        require_capability('ltool/note:managenote', $coursecontext);
    } else {
        $usercontext = context_user::instance($childid);
        require_capability('ltool/note:managenote', $usercontext, $USER->id);
    }
} else {
    if ($USER->id == $editedrecord->userid) {
        require_capability('ltool/note:manageownnote', $context);
    } else {
        redirect(new moodle_url('/'));
    }
}


$baseurl = new moodle_url('/local/learningtools/ltool/note/list.php', $urlparams);
$baseurl = !empty($returnurl) ? $returnurl : $baseurl;
$pageurl = new moodle_url('/local/learningtools/ltool/note/editlist.php', $urlparams);

// If user is logged in, then use profile navigation in breadcrumbs.
if ($profilenode = $PAGE->settingsnav->find('myprofile', null)) {
    $profilenode->make_active();
}
$PAGE->navbar->add($title);

// Edit action in note.
if ($edit && confirm_sesskey()) {
    $params['id'] = $edit;
    $params['courseid'] = $courseid;
    $params['returnurl'] = $returnurl;
    $editorform = new ltool_note_info($pageurl->out(false), $params);
    if ($editorform->is_cancelled()) {
        redirect($baseurl);
    } else if ($fromdata = $editorform->get_data()) {
        $usernote = $fromdata->noteeditor['text'];
        $exitnote = $DB->get_record('ltool_note_data', array('id' => $edit));
        if ($exitnote) {
            if ($usernote != $exitnote->note) {
                    $DB->set_field('ltool_note_data', 'note', $usernote, array('id' => $edit));
                    $DB->set_field('ltool_note_data', 'timemodified', time(), array('id' => $edit));
                    $editeventcontext = context::instance_by_id($exitnote->contextid, MUST_EXIST);
                    $eventcourseid = local_learningtools_get_eventlevel_courseid($editeventcontext, $exitnote->course);
                    // Add event to user edit the note.
                    $editeventparams = [
                        'objectid' => $exitnote->id,
                        'courseid' => $eventcourseid,
                        'context' => $editeventcontext,
                        'other' => [
                            'pagetype' => $exitnote->pagetype,
                        ]
                    ];

                    if ($childid) {
                        $editeventparams = array_merge($editeventparams, ['relateduserid' => $childid]);
                    }
                    $event = \ltool_note\event\ltnote_edited::create($editeventparams);
                    $event->trigger();
                    redirect($baseurl, get_string('successeditnote', 'local_learningtools'),
                        null, \core\output\notification::NOTIFY_SUCCESS);
            }
        }
        redirect($baseurl);
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('editnote', 'local_learningtools'));
        $editorform->display();
        echo $OUTPUT->footer();
    }

}

