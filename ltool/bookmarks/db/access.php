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
 * Learning Tool Bookmarks
 *
 * @package   tool
 * @package  tool__bookmarks
 * @copyright 2021 lmsace
 * 
 */

$capabilities = array(
    
    'ltool/bookmarks:createbookmarks' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'ltool/bookmarks:viewownbookmarks' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),
    'ltool/bookmarks:manageownbookmarks' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_SYSTEM,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'user' => CAP_ALLOW
        )
    ),

    'ltool/bookmarks:viewbookmarks' => array(
        'riskbitmask' => RISK_SPAM,
        'captype'      => 'read',
        'contextlevel' => CONTEXT_COURSE,
        'archetypes'   => array(
            'manager'  => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'teacher' => CAP_ALLOW,
        )
    ),

    'ltool/bookmarks:managebookmarks' => array(
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