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
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @category  event observer
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learningtools;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/local/learningtools/lib.php');

class event_observer {

    /**
     * Callback function will delete the course in the table.
     * @params object event data
     * @return void
     */
    public static function learningtools_coursedata_deleteaction($event) {
        $eventdata = $event->get_data();
        $courseid = $eventdata['objectid'];
        // Delete the bookmarks and notes in the course.
        delete_course_bookmarks($courseid);
        delete_course_notes($courseid);
    }

    /**
     * Callback function will delete the course module in the table.
     * @params object event data
     * @return void
     */
    public static function learningtools_moduledata_deleteaction($event) {
        $eventdata = $event->get_data();
        $coursemodule = $eventdata['objectid'];
        // Delete the bookmarks and notes in the course.
        delete_module_bookmarks($coursemodule);
        delete_module_notes($coursemodule);
    }
}
