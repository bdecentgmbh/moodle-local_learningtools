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
 * @package   ltool_focus
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_focus;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/externallib.php');

/**
 * define external class.
 */
class external extends \external_api {
    /**
     * Parameters defintion to preference of focus mode.
     *
     * @return array list of option parameters
     */
    public static function save_userfocusmode_parameters() {

        return new \external_function_parameters(
            array(
                'status' => new \external_value(PARAM_INT, 'The user focus mode status'),
            )
        );
    }

    /**
     * Save the user preference of focus mode.
     * @param int $status
     * @return int focus mode status.
     */
    public static function save_userfocusmode($status) {
        global $SESSION;
        require_login();
        $context = \context_system::instance();
        require_capability('ltool/focus:createfocus', $context);
        $params = self::validate_parameters(self::save_userfocusmode_parameters(),
            array('status' => $status));
        $SESSION->focusmode = $params['status'];
        return $status;
    }

    /**
     * Return parameters define for preference of focus mode status.
     */
    public static function save_userfocusmode_returns() {
        return new \external_value(PARAM_BOOL, 'save focus status');
    }
}
