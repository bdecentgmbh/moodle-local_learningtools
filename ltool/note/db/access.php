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
 * Learning Tool Note
 *
 * @package   tool
 * @package  tool__note
 * @copyright 2021 lmsace
 * 
 */

$capabilities = array(
    'ltool/note:createnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),
    'ltool/note:viewownnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),
    'ltool/note:manageownnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),
    'ltool/note:viewnote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        )
    ),

    'ltool/note:managenote' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        )
    ),

    // Add more capabilities here ...
);