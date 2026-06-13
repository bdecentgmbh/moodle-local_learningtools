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
 * Event observer for the report learning tool.
 *
 * @package   ltool_report
 * @category  event
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_report;
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__DIR__) . '/lib.php');

/**
 * Removes report data when a course or module it belongs to is deleted.
 */
class event_observer {
    /**
     * Delete a deleted course's report data.
     *
     * @param \core\event\base $event event data
     * @return void
     */
    public static function report_coursedata_deleteaction($event) {
        $eventdata = $event->get_data();
        ltool_report_delete_course_reports($eventdata['objectid']);
    }

    /**
     * Delete a deleted module's report data.
     *
     * @param \core\event\base $event event data
     * @return void
     */
    public static function report_moduledata_deleteaction($event) {
        $eventdata = $event->get_data();
        ltool_report_delete_module_reports($eventdata['objectid']);
    }
}
