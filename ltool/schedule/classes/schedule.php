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
 * The class defines the Schedule ltool.
 *
 * @package   ltool_schedule
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_schedule;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');

require_once(dirname(__DIR__).'/lib.php');

/**
 *  The class defines the schedule ltool
 */
class schedule extends \local_learningtools\learningtools {

    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'schedule';

    /**
     * Tool context level
     * @var string
     */
    public $contextlevel = 'course';

    /**
     * schedule name
     * @return string name
     *
     */
    public function get_tool_name() {
        return get_string('schedule', 'local_learningtools');
    }

    /**
     * schedule icon
     */
    public function get_tool_icon() {

        return 'fa fa-calendar-plus-o';
    }

    /**
     * schedule icon background color
     */
    public function get_tool_iconbackcolor() {

        return '#343a40';
    }

    /**
     * Get the schedule tool  content.
     *
     * @return string display tool schedule plugin html.
     */
    public function get_tool_records() {
        $data = [];
        $data['name'] = $this->get_tool_name();
        $data['icon'] = $this->get_tool_icon();
        $data['ltoolschedule'] = true;
        $data['schedulehovername'] = get_string('schedule', 'local_learningtools');
        $data['iconbackcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconbackcolor");
        $data['iconcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconcolor");
        return $data;
    }

    /**
     * Return the template of schedule fab button.
     *
     * @return string schedule tool fab button html.
     */
    public function render_template() {
        global $PAGE, $SITE;
        if (local_learningtools_can_visible_tool_incourse()) {
            $coursecontext = \context_course::instance($PAGE->course->id);
            if (has_capability("ltool/schedule:createschedule", $coursecontext)) {
                $data = $this->get_tool_records();
                return ltool_schedule_render_template($data);
            }
        }
    }

    /**
     * Load the required javascript files for schedule.
     *
     * @return void
     */
    public function load_js() {
        // Load schedule tool js configuration.
        ltool_schedule_load_js_config();
    }
}

