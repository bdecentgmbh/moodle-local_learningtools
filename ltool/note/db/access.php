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
 * Define plugin capabilities.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
    'ltool/note:createnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'user' => CAP_ALLOW
        )
    ),
    'ltool/note:viewownnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'user' => CAP_ALLOW
        )
    ),
    'ltool/note:manageownnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'user' => CAP_ALLOW
        )
    ),
    'ltool/note:viewnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
        )
    ),

    'ltool/note:managenote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
    ),

    // Add more capabilities here ...
);
