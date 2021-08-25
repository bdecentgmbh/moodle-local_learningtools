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
 * Behat Learning Tools related steps definitions.
 *
 * @package   local_learningtools
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../lib/behat/behat_base.php');

use Behat\Gherkin\Node\TableNode as TableNode,
    Behat\Mink\Exception\ExpectationException as ExpectationException,
    Behat\Mink\Exception\DriverException as DriverException,
    Behat\Mink\Exception\ElementNotFoundException as ElementNotFoundException;

/**
 * Test cases custom function for learning tool FAB buttons.
 *
 * @package   local_learningtools
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_learningtool extends behat_base {

    /**
     * Check that the fab button exist condition.
     *
     * @Given the FAB button should exist
     */
    public function fab_button_should_exist(): void {
        $this->execute("behat_general::should_exist", ['#tool-action-button', 'css_element']);
    }

    /**
     * Check that click the fab button condition.
     *
     * @Given I click on FAB button
     *
     */
    public function click_fab_button(): void {
        $this->execute("behat_general::i_click_on", ['#tool-action-button', 'css_element']);
    }
}
