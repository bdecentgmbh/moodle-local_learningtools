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
 * tool plugin "Learning Tools Note" - library file
 *
 * @package    ltool_note
 * @copyright  2021 lmsace
 */
use core_user\output\myprofile\tree;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot. '/local/learningtools/lib.php');

class editorform extends moodleform {

    public function definition() {
        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $contextlevel = $this->_customdata['contextlevel'];
        $pagetype = $this->_customdata['pagetype'];
        $pageurl = $this->_customdata['pageurl'];
        $user = $this->_customdata['user'];
        
        $mform->addElement('editor', 'ltnoteeditor', get_string('note', 'local_learningtools'), array('autosave' => false));

        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);
        $mform->setDefault('course', $course);
        
        $mform->addElement('hidden', 'contextlevel');
        $mform->setDefault('contextlevel', $contextlevel);
        $mform->setType('contextlevel', PARAM_INT);

        $mform->addElement('hidden', 'pagetype');
        $mform->setDefault('pagetype', $pagetype);
        $mform->setType('pagetype', PARAM_TEXT);

        $mform->addElement('hidden', 'pageurl');
        $mform->setDefault('pageurl', $pageurl);
        $mform->setType('pageurl', PARAM_RAW);

        $mform->addElement('hidden', 'user');
        $mform->setDefault('user', $user);
        $mform->setType('user', PARAM_INT);
    }
}

class edit_noteinfo extends moodleform {

    public function definition() {
        global $DB;

        $mform = $this->_form;
        $noteid = $this->_customdata['id'];
        $courseid = $this->_customdata['courseid'];
        $note = $DB->get_record('learningtools_note', array('id' => $noteid));
        $usernote = !empty($note->note) ? $note->note : '';
        $mform->addElement('editor', 'noteeditor', get_string('note', 'local_learningtools'))->setValue( array('text' => $usernote));
        $mform->setType('noteeditor', PARAM_RAW);

        $mform->addElement('hidden', 'edit');
        $mform->setType('edit', PARAM_INT);
        $mform->setDefault('edit', $noteid);
        
        if ($courseid) {
            $mform->addElement('hidden', 'courseid');
            $mform->setType('courseid', PARAM_INT);
            $mform->setDefault('courseid', $courseid);
        }
        
        $mform->addElement('hidden', 'sesskey');
        $mform->setType('sesskey', PARAM_RAW);
        $mform->setDefault('sesskey', sesskey());
        
        $this->add_action_buttons();
    }   
}

function ltool_note_myprofile_navigation(tree $tree, $user, $iscurrentuser, $course) {
    global $PAGE, $USER;

    $context = context_system::instance();
   if ($iscurrentuser) {
       if(!empty($course)) {
            $coursecontext = context_course::instance($course->id);
            if (has_capability('ltool/note:viewnote', $coursecontext)) {
                $noteurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', array('courseid' => $course->id));
                $notenode = new core_user\output\myprofile\node('learningtools', 'note',
                    get_string('note', 'local_learningtools'), null, $noteurl);
                $tree->add_node($notenode);
            }
       } else {
            if (has_capability('ltool/note:viewownnote', $context)) {
                $noteurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php');
                $notenode = new core_user\output\myprofile\node('learningtools', 'note',
                    get_string('note', 'local_learningtools'), null, $noteurl);
                $tree->add_node($notenode);
            }
        }
    } else {
        if (is_parentforchild($user->id, 'ltool/note:viewnote')) {
            $params = ['userid' => $user->id];
            if (!empty($course)) {
                $params['selectcourse'] = $course->id;
            }

            $noteurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $params);
            $notenode = new core_user\output\myprofile\node('learningtools', 'note',
                get_string('note', 'local_learningtools'), null, $noteurl);
            $tree->add_node($notenode);
            // exit;
            return true;
        }
    }
   return true;
}

function ltool_note_output_fragment_get_note_form($args) {

    global $PAGE, $COURSE, $USER;
    $mform = new editorform(null, $args);
    ob_start();
    $mform->display();
    $o .= ob_get_contents();
    ob_end_clean();
    return $o;
}


function user_save_notes($contextid, $data) {
    global $DB, $PAGE;
    $context = context_system::instance();
    $PAGE->set_context($context);

    if (confirm_sesskey()) {
        $record = new stdClass;
        $record->user = $data['user'];
        $record->course = $data['course'];
        $record->contextlevel = $data['contextlevel'];
        $record->contextid = $contextid;
        if ($record->contextlevel == 70) {
            $record->coursemodule = get_coursemodule_id($record);
        } else {
            $record->coursemodule = 0;
        }
        
        $record->pagetype = $data['pagetype'];
        $record->pageurl = $data['pageurl'];
        $record->note = format_text($data['ltnoteeditor']['text'], FORMAT_HTML);
        $record->timecreated = time();
        
         // add event to user create the bookmark
        $event = \ltool_note\event\ltnote_created::create([
            'context' => $context,
            'other' => [
                'courseid' => $record->course,
                'pagetype' => $record->pagetype,
            ]
        ]);
        $event->trigger();
        return $DB->insert_record('learningtools_note', $record);

    }
}


function check_note_instanceof_block($record) {
    $data = new stdClass;
    if ($record->contextlevel == 10) { // system level
        $data->instance = 'system';
    } else if($record->contextlevel == 30) { // user level
        $data->instance = 'user';
    } else if($record->contextlevel == 50) {  // course level
        $data->instance = 'course';
        $data->courseid = $record->course;
        $data->contextid = $record->contextid;

    } else if($record->contextlevel == 70) { // mod level
        $data->instance = 'mod';
        $data->courseid = $record->course;
        $data->contextid = $record->contextid;
        $data->coursemodule = get_coursemodule_id($record);

    } else if($record->contextlevel == 80) { // context blocklevel
        $data->instance = 'block';
    }
    return $data;
}



