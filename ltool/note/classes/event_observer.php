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
 * Event observer function  definition and returns.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_note;
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__DIR__).'/lib.php');

/**
 * Event observer class define.
 */
class event_observer {

    /**
     * Callback function will delete the course in the table.
     * @param object $event event data
     * @return void course notes deleted records action.
     */
    public static function note_coursedata_deleteaction($event) {
        $eventdata = $event->get_data();
        $courseid = $eventdata['objectid'];
        ltool_note_delete_course_note($courseid);
    }

    /**
     * Callback function will delete the course module in the table.
     * @param object $event event data
     * @return void
     */
    public static function note_moduledata_deleteaction($event) {
        $eventdata = $event->get_data();
        $coursemodule = $eventdata['objectid'];
        ltool_note_delete_module_note($coursemodule);
    }
}
