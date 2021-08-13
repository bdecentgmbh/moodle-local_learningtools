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
 * Notes ltool defined class.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @category  defined
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../../../config.php');
require_once($CFG->dirroot.'/local/learningtools/learningtools.php');
require_login();

/**
 *  Note ltool define class
 */
class note extends learningtools {

    /**
     * Note name
     * @return string name
     *
     */
    public function get_tool_name() {
        return get_string('note', 'local_learningtools');
    }
    /**
     * Note icon
     * @return string icon
     */
    public function get_tool_icon() {

        return 'fa fa-pencil';
    }

}
