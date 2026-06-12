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
 * ltool plugin "Learning Tools notes" - email popout form.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Define notes form.
 */
class ltool_email_popoutform extends moodleform {
    /**
     * Adds element to form
     */
    public function definition() {
        $mform = $this->_form;
        $course = $this->_customdata['course'];
        $contextlevel = $this->_customdata['contextlevel'];
        $pagetype = $this->_customdata['pagetype'];
        $pageurl = $this->_customdata['pageurl'];
        $user = $this->_customdata['user'];
        $pagetitle = $this->_customdata['pagetitle'];
        $itemtype = $this->_customdata['itemtype'];
        $itemid = $this->_customdata['itemid'];
        $popoutaction = isset($this->_customdata['popoutaction']) ?
        $this->_customdata['popoutaction'] : '';

        $mform->addElement('editor', 'ltnoteeditor', '', ['autosave' => false]);
        $mform->addElement('hidden', 'course');
        $mform->setType('course', PARAM_INT);
        $mform->setDefault('course', $course);
        $mform->addElement('hidden', 'contextlevel');
        $mform->setDefault('contextlevel', $contextlevel);
        $mform->setType('contextlevel', PARAM_INT);

        $mform->addElement('hidden', 'pagetype');
        $mform->setDefault('pagetype', $pagetype);
        $mform->setType('pagetype', PARAM_TEXT);

        $mform->addElement('hidden', 'pagetitle');
        $mform->setDefault('pagetitle', $pagetitle);
        $mform->setType('pagetitle', PARAM_TEXT);

        $mform->addElement('hidden', 'pageurl');
        $mform->setDefault('pageurl', $pageurl);
        $mform->setType('pageurl', PARAM_URL);

        $mform->addElement('hidden', 'user');
        $mform->setDefault('user', $user);
        $mform->setType('user', PARAM_INT);

        $mform->addElement('hidden', 'itemtype');
        $mform->setDefault('itemtype', $itemtype);
        $mform->setType('itemtype', PARAM_TEXT);

        $mform->addElement('hidden', 'itemid');
        $mform->setDefault('itemid', $itemid);
        $mform->setType('itemid', PARAM_INT);

        if ($popoutaction) {
            $this->add_action_buttons();
        }
    }
}
