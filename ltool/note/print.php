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
 * Print notes page for learning tools.
 *
 * @package   ltool_note
 * @copyright 2021 bdecent gmbh <https://bdecent.de>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot.'/local/learningtools/lib.php');
require_once($CFG->dirroot.'/local/learningtools/ltool/note/lib.php');

$courseid = optional_param('courseid', 0, PARAM_INT);
$contextid = optional_param('contextid', 0, PARAM_INT);
$search = optional_param('search', '', PARAM_TEXT);
$sectionid = optional_param('sectionid', 0, PARAM_INT);
$activity = optional_param('activity', 0, PARAM_INT);
$filter = optional_param('filter', '', PARAM_TEXT);

require_login();
ltool_note_require_note_status();

$context = context_system::instance();
if ($courseid) {
    $context = context_course::instance($courseid);
}

require_capability('ltool/note:viewownnote', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/note/print.php');
$PAGE->set_title(get_string('printnotes', 'ltool_note'));
$PAGE->set_heading(get_string('printnotes', 'ltool_note'));
$PAGE->set_pagelayout('popup');

// Add CSS and JS.
$PAGE->requires->css('/local/learningtools/ltool/note/styles.css');
$PAGE->requires->js_call_amd('ltool_note/print', 'init');

echo $OUTPUT->header();

// Get notes using the existing fragment system.
$output = $PAGE->get_renderer('local_learningtools');
$noteslist = new \ltool_note\output\notes_list($courseid, $sectionid, $activity, $search, $filter, true);
$notescontent = $output->render($noteslist);

// Prepare template context.
$templatecontext = [
    'printtitle' => get_string('printnotes', 'ltool_note'),
    'printbuttontext' => get_string('print', 'ltool_note'),
    'returnbuttontext' => get_string('returntonotes', 'ltool_note'),
    'notescontent' => $notescontent,
];

echo $OUTPUT->render_from_template('ltool_note/print_notes', $templatecontext);

echo $OUTPUT->footer();
