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
 * @package   ltool_focus
 * @copyright bdecent GmbH 2021
 * @category  event
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_focus;
defined('MOODLE_INTERNAL') || die();
require_once(dirname(__DIR__).'/lib.php');

/**
 * Event observer class define.
 */
class event_observer {

    /**
     * Callback function will delete the course in the table.
     * @param object $event event data
     * @return void course focus deleted records action.
     */
    public static function ltool_focus_changeconfig($event) {
        $data = $event->get_data();
        if (isset($data['other']['plugin'])) {
            $pluginconfig = $data['other']['plugin'];
            if ($pluginconfig == 'ltool_focus') {
                ltool_focus_create_focus_temp_cssfile();
            }
        }
    }
}
