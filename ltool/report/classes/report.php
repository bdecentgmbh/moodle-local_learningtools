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
 * The class defines the Report ltool.
 *
 * @package   ltool_report
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_report;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/learningtools/lib.php');
require_once(dirname(__DIR__) . '/lib.php');

/**
 * The Report ltool: lets a user report an issue on the current page.
 */
class report extends \local_learningtools\learningtools {
    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'report';

    /**
     * Tool context level.
     *
     * @var string
     */
    public $contextlevel = 'system';

    /**
     * Tool name.
     *
     * @return string
     */
    public function get_tool_name() {
        return get_string('report', 'ltool_report');
    }

    /**
     * Tool icon.
     *
     * @return string
     */
    public function get_tool_icon() {
        return 'fa fa-flag';
    }

    /**
     * Tool icon background colour.
     *
     * @return string
     */
    public function get_tool_iconbackcolor() {
        return '#343a40';
    }

    /**
     * Build the tool data for the page (page identity + colours for the launcher and form).
     *
     * @return array
     */
    public function get_tool_records() {
        global $USER, $COURSE, $PAGE;
        $pageurl = local_learningtools_clean_mod_assign_userlistid($PAGE->url->out(false), $PAGE->cm);
        return [
            'name' => $this->get_tool_name(),
            'icon' => $this->get_tool_icon(),
            'id' => $this->shortname,
            'user' => $USER->id,
            'course' => $COURSE->id,
            'pagetype' => $PAGE->pagetype,
            'pagetitle' => $PAGE->title,
            'coursemodule' => local_learningtools_get_moduleid($PAGE->context->id, $PAGE->context->contextlevel),
            'contextlevel' => $PAGE->context->contextlevel,
            'contextid' => $PAGE->context->id,
            'sesskey' => sesskey(),
            'pageurl' => $pageurl,
            'reporthovername' => get_string('reporthovername', 'ltool_report'),
            'iconbackcolor' => get_config('ltool_report', 'reporticonbackcolor'),
            'iconcolor' => get_config('ltool_report', 'reporticoncolor'),
        ];
    }

    /**
     * Queue the report tool javascript.
     *
     * @return void
     */
    public function load_js() {
        ltool_report_load_report_js_config($this->get_tool_records());
    }

    /**
     * Render the report tool launcher button.
     *
     * @return string
     */
    public function render_template() {
        return ltool_report_render_template($this->get_tool_records());
    }
}
