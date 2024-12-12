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
 * External functions definition and returns.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_note;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/externallib.php');

/**
 * Define external class.
 */
class external extends \external_api {
    /**
     * Parameters defintion to get added user note.
     *
     * @return array list of option parameters
     */
    public static function save_usernote_parameters() {

        return new \external_function_parameters(
            array(
                'contextid' => new \external_value(PARAM_INT, 'The context id for the course'),
                'formdata' => new \external_value(PARAM_RAW, 'The data from the user notes')
            )
        );
    }

    /**
     * Save the user notes.
     * @param int $contextid context id
     * @param mixed $formdata user data
     * @return int page user notes details.
     */
    public static function save_usernote($contextid, $formdata) {
        global $CFG, $USER;
        require_once($CFG->dirroot.'/local/learningtools/ltool/note/lib.php');
        require_login();
        $validparams = self::validate_parameters(self::save_usernote_parameters(),
            array('contextid' => $contextid, 'formdata' => $formdata));
        $context = \context_system::instance();
        require_capability("ltool/note:createnote", $context);
        // Parse serialize form data.
        parse_str($validparams['formdata'], $data);
        if ($USER->id == $data['user']) {
            return ltool_note_user_save_notes($validparams['contextid'], $data);
        }
    }

    /**
     * Return parameters define for save notes status.
     */
    public static function save_usernote_returns() {
        return new \external_value(PARAM_INT, 'Count of Page user notes');
    }
}
