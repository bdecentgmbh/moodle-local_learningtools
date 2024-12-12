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
 * Behat Focus Tool related steps definitions.
 *
 * @package   ltool_focus
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// NOTE: no MOODLE_INTERNAL test here, this file may be required by behat before including /config.php.

require_once(__DIR__ . '/../../../../../../lib/behat/behat_base.php');

use Behat\Mink\Exception\ExpectationException as ExpectationException;

/**
 * Test cases custom function for focus tool Focus-mode.
 *
 * @package   ltool_focus
 * @category   test
 * @copyright 2021, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class behat_focus extends behat_base {

    /**
     * Check that the focus mode enable.
     *
     * @Given /^I check focus mode enable$/
     *
     * @throws ExpectationException
     */
    public function i_check_focus_mode_enable(): void {
        $footerjs = "
            return (
                Y.one('#page-footer') &&
                Y.one('#page-footer').getComputedStyle('display') == 'none'
            )
        ";

        if (!$this->evaluate_script($footerjs)) {
            throw new ExpectationException("Doesn't enable the focus mode", $this->getSession());
        }
    }

    /**
     * Check that the focus mode disable.
     *
     * @Given /^I check focus mode disable$/
     *
     * @throws ExpectationException
     */
    public function i_check_focus_mode_disable(): void {
        $footerjs = "
            return (
                Y.one('#page-footer') &&
                Y.one('#page-footer').getComputedStyle('display') !== 'none'
            )
        ";
        if (!$this->evaluate_script($footerjs)) {
            throw new ExpectationException("Doesn't disable the focus mode", $this->getSession());
        }
    }
}
