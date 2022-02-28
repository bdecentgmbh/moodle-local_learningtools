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
 * ltool_note deletes the notes
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
$PAGE->set_url('/local/learningtools/ltool/note/deletelist.php');
$PAGE->set_title($title);
$PAGE->set_heading($SITE->fullname);

$delete      = optional_param('delete', 0, PARAM_INT);
$returnurl  = optional_param('returnurl', '', PARAM_URL);
$confirm    = optional_param('confirm', '', PARAM_ALPHANUM);
// Require access the page.
ltool_note_require_deletenote_cap($delete);
// If user is logged in, then use profile navigation in breadcrumbs.
if ($profilenode = $PAGE->settingsnav->find('myprofile', null)) {
    $profilenode->make_active();
}
$PAGE->navbar->add($title);

$pageurl = new moodle_url('/local/learningtools/ltool/note/deletelist.php');

if ($delete && confirm_sesskey()) {

    if ($confirm != md5($delete)) {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('deletemessage', 'local_learningtools'));

        $deleteoptions = array('delete' => $delete, 'confirm' => md5($delete), 'sesskey' => sesskey());
        $deleteoptions = array_merge($deleteoptions, ['returnurl' => $returnurl]);
        $deletenoteurl = new moodle_url($pageurl, $deleteoptions);
        $deletenotebutton = new single_button($deletenoteurl, get_string('delete'), 'post');

        echo $OUTPUT->confirm(get_string('deletemsgcheckfull', 'local_learningtools'), $deletenotebutton, $pageurl);
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

            // Add event to user delete the bookmark.
            $event = \ltool_note\event\ltnote_deleted::create($deleteeventparams);
            $event->trigger();

            \core\session\manager::gc(); // Remove stale sessions.
            redirect($returnurl, get_string('successdeletemessage', 'local_learningtools'),
             null, \core\output\notification::NOTIFY_SUCCESS);
        } else {
            \core\session\manager::gc(); // Remove stale sessions.
            redirect($returnurl, get_string('deletednotmessage', 'local_learningtools'),
            null, \core\output\notification::NOTIFY_ERROR);
        }
    }
}
