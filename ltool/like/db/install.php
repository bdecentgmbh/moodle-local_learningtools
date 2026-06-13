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
 * Define install function.
 *
 * @package    ltool_like
 * @copyright  2026, bdecent gmbh bdecent.de
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * ltool_like install function: register the tool with the parent plugin.
 *
 * @return void
 */
function xmldb_ltool_like_install() {
    global $CFG;
    require_once($CFG->dirroot . '/local/learningtools/lib.php');
    local_learningtools_add_learningtools_plugin('like');
}
