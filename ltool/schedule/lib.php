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
 * ltool plugin "Learning Tools Schedule" - library file.
 *
 * @package   ltool_schedule
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/local/learningtools/lib.php');
require_once($CFG->libdir.'/formslib.php');

/**
 * Define user edit the schedulebox form.
 */
class ltool_schedule_editbox extends moodleform {
    /**
     * Adds element to form
     */
    public function definition() {
        global $DB;

        $user = $this->_customdata['user'];
        $course = $this->_customdata['course'];
        $pageurl = $this->_customdata['pageurl'];
        $pagetitle = $this->_customdata['pagetitle'];
        $cm = $this->_customdata['cm'];

        $mform = $this->_form;
        // Title.
        $mform->addElement('text', 'schedulename', get_string('eventname', 'calendar'), 'size="50"');
        $mform->setDefault('schedulename', $pagetitle);
        $mform->addRule('schedulename', get_string('required'), 'required', null, 'client');
        $mform->setType('schedulename', PARAM_TEXT);

        // Event time start field.
        $mform->addElement('date_time_selector', 'scheduletimestart', get_string('date'), ['defaulttime' => time()]);

        // Description.
        $mform->addElement('textarea', 'scheduledesc', get_string('description'), 'wrap="virtual" rows="8" cols="60"');
        $mform->setType('scheduledesc', PARAM_TEXT);

        // Duration.
        $mform->addElement('text', 'scheduleduration', get_string('durationminutes', 'calendar'));
        $mform->setType('scheduleduration', PARAM_INT);

        $mform->addElement('hidden', 'user');
        $mform->setDefault('user', $user);
        $mform->setType('user', PARAM_INT);

        $mform->addElement('hidden', 'course');
        $mform->setDefault('course', $course);
        $mform->setType('course', PARAM_INT);

        $mform->addElement('hidden', 'cm');
        $mform->setDefault('cm', $cm);
        $mform->setType('cm', PARAM_INT);

        $mform->addElement('hidden', 'pageurl');
        $mform->setDefault('pageurl', $pageurl);
        $mform->setType('pageurl', PARAM_URL);
    }
}

/**
 * Learning tools schedule template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_schedule_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_schedule/schedule', $templatecontent);
}

/**
 * Implemented the schedule tool js.
 *
 * @return void
 */
function ltool_schedule_load_js_config() {
    global $PAGE, $USER;
    $params['pagetitle'] = $PAGE->title;
    $params['contextid'] = $PAGE->context->id;
    $params['user'] = $USER->id;
    $params['pageurl'] = $PAGE->url->out(false);
    $params['course'] = $PAGE->course->id;
    $params['cm'] = !empty($PAGE->cm->id) ? $PAGE->cm->id : 0;
    $PAGE->requires->js_call_amd('ltool_schedule/schedule', 'init', array($params));
}

/**
 * Load the schedule form in display to the modal.
 *
 * @param array $args
 * @return string display form
 */
function ltool_schedule_output_fragment_get_schedule_form($args) {
    $schedulebox = html_writer::start_tag('div', array('id' => 'ltoolschedule-editorbox'));
    $mform = new ltool_schedule_editbox(null, $args);
    $schedulebox .= $mform->render();
    $schedulebox .= html_writer::end_tag('div');
    return $schedulebox;
}

/**
 * Implemente the calendar event for user event.
 *
 * @param array $args
 * @return void
 */
function ltool_schedule_output_fragment_set_calendar_event($args) {
    global $CFG, $USER;
    require_once($CFG->dirroot.'/calendar/lib.php');
    parse_str($args['formdata'], $formdata);
    if (!empty($formdata)) {
        if ($formdata['schedulename']) {
            // Create a calender event.
            if (!empty($formdata['scheduledesc'])) {
                $visitpage = html_writer::link($formdata['pageurl'], get_string('visitpage', 'local_learningtools'));
                $scheduledesc = $formdata['scheduledesc'] .' '. $visitpage;
            } else {
                $scheduledesc = '';
            }
            $event = new stdClass();
            $event->type = CALENDAR_EVENT_TYPE_STANDARD;
            $event->name = $formdata['schedulename'];
            $event->description = $scheduledesc;
            $event->format = FORMAT_HTML;
            $event->groupid = 0;
            $event->userid = $USER->id;
            $timestart = $formdata['scheduletimestart'];
            $event->timestart = make_timestamp($timestart['year'], $timestart['month'], $timestart['day'],
            $timestart['hour'], $timestart['minute']);
            $event->timeduration = !empty($formdata['scheduleduration']) ? $formdata['scheduleduration'] * MINSECS : 0;
            $event->visible = 1;
            calendar_event::create($event);
        }
    }
}

