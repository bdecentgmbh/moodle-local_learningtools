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
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_bookmarks;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/externallib.php');

/**
 * define external class.
 */
class external extends \external_api {
    /**
     * Parameters defintion to get added user bookmarks.
     *
     * @return array list of option parameters
     */
    public static function save_userbookmarks_parameters() {

        return new \external_function_parameters(
            array(
                'contextid' => new \external_value(PARAM_INT, 'The context id for the course'),
                'formdata' => new \external_value(PARAM_RAW, 'The data from the user bookmarks')
            )
        );
    }

    /**
     * Save the user bookmarks.
     * @param int $contextid contextid
     * @param mixed $formdata user data
     * @return array Bookmarks save info details.
     */
    public static function save_userbookmarks($contextid, $formdata) {
        global $CFG, $USER;
        require_login();
        require_once($CFG->dirroot.'/local/learningtools/ltool/bookmarks/lib.php');
        $context = \context_system::instance();
        require_capability('ltool/bookmarks:createbookmarks', $context);
        $params = self::validate_parameters(self::save_userbookmarks_parameters(),
                        array('contextid' => $contextid, 'formdata' => $formdata));
        // Parse serialize form data.
        $data = json_decode($params['formdata']);
        $data = (array) $data;
        if ($USER->id == $data['user']) {
            return ltool_bookmarks_user_save_bookmarks($params['contextid'], $data);
        }
    }

    /**
     * Return parameters define for save bookmars status.
     */
    public static function save_userbookmarks_returns() {

        return new \external_single_structure(
            array(
                'bookmarksstatus' => new \external_value(PARAM_BOOL, 'save bookmarks status'),
                'bookmarksmsg' => new \external_value(PARAM_TEXT, 'bookmarks message'),
                'notificationtype' => new \external_value(PARAM_TEXT, 'Notification type')
            )
        );
    }
}
