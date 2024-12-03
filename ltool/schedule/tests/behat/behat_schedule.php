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
 * Behat Schedule Tool related steps definitions.
 *
 * @package   ltool_schedule
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

/**
 * Test cases custom function for schedule tool.
 *
 * @package   ltool_schedule
 * @category   test
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_schedule extends behat_base {

    /**
     * Check that the schedule event.
     *
     * @Given /^I check schedule event$/
     *
     */
    public function i_check_schedule_event(): void {
        global $CFG;

        if ($CFG->branch <= 403) {
            $this->execute("behat_general::click_link", "Full calendar");
        } else {
            $this->execute("behat_general::click_link", "Course calendar");
        }
    }
}
