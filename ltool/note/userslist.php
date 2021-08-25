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
 * list of the students to view Notes.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once(dirname(__FILE__).'/../../../../config.php');
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_once(dirname(__FILE__).'/lib.php');
require_login();
require_note_status();
$title = get_string('courseparticipants', 'local_learningtools');
$courseid = required_param('courseid', PARAM_INT);
$context = context_course::instance($courseid);
require_capability('ltool/note:viewnote', $context);
$PAGE->set_context($context);
$PAGE->set_url('/local/learningtools/ltool/note/userslist.php');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);

// Participants table filterset.
$filterset = new \core_user\table\participants_filterset;
$filterset->add_filter(
    new \core_table\local\filter\integer_filter('courseid', \core_table\local\filter\filter::JOINTYPE_DEFAULT, [(int) $courseid])
);
// Approver user table - pariticipants table wrapper.
$participanttable = new \local_learningtools\table\courseparticipants("user-index-participants-note");
$participanttable->define_baseurl($CFG->wwwroot.'/local/learningtools/ltool/note/userslist.php');
$participanttable->set_filterset($filterset);

echo $OUTPUT->header();
if (isset($participanttable)) {
    echo $participanttable->out(10, true);
}
echo $OUTPUT->footer();
