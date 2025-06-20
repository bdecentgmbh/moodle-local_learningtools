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
 * Main view page for Learning Tools
 *
 * @package    ltool_note
 * @copyright  bdecent GmbH 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/local/learningtools/lib.php');

$id = required_param('id', PARAM_INT); // Course ID.
$sectionid = optional_param('sectionid', 0, PARAM_INT); // Section ID.
$activity = optional_param('activity', 0, PARAM_INT);
$filter = optional_param('filter', '', PARAM_ALPHA);
$action = optional_param('action', '', PARAM_ALPHA); // Action to perform, e.g., 'hide' or 'show'.
$noteid = optional_param('noteid', 0, PARAM_INT); // Note ID for specific actions.

// Get course and context information.
$course = $DB->get_record('course', ['id' => $id], '*', MUST_EXIST);
$context = context_course::instance($course->id);

// Check login and capability.
require_login($course);

$pageurl = new moodle_url('/local/learningtools/ltool/note/view.php', ['id' => $id]);

// Set up page.
$PAGE->set_url($pageurl);
$PAGE->set_context($context);
$PAGE->set_course($course);
$PAGE->set_heading($course->fullname);
$PAGE->set_title(get_string('learningtools', 'local_learningtools'));
$PAGE->set_pagelayout('course');

// Set up secondary navigation.
$PAGE->set_secondary_active_tab('learningtools');

// Create the notes renderer.
$output = $PAGE->get_renderer('local_learningtools');

// Render the page.
echo $OUTPUT->header();

if ($action == 'hide' && (!empty($noteid))) {
    $DB->set_field('ltool_note_data', 'printstatus', 1, ['id' => $noteid]);
} else if ($action == 'show' && (!empty($noteid))) {
    $DB->set_field('ltool_note_data', 'printstatus', 0, ['id' => $noteid]);
}

$actionbar = new \local_learningtools\output\general_action_bar($context, $pageurl, 'learningtools', 'notes', $course->id,
    $sectionid, $activity);
$renderer = $PAGE->get_renderer('local_learningtools');

echo $renderer->render_action_bar($actionbar);

$noteslist = new \ltool_note\output\notes_list($course->id, $sectionid, $activity, '', $filter);
echo $output->render($noteslist);

echo $OUTPUT->footer();
