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
 * tool plugin "Learning Tools Like" - settings file.
 *
 * @package   ltool_like
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // Choose which reactions are shown.
    foreach (['dislike', 'like', 'superlike'] as $reactiontype) {
        $page->add(new admin_setting_configcheckbox(
            "ltool_like/enable{$reactiontype}",
            get_string("enable{$reactiontype}", 'ltool_like'),
            get_string("enable{$reactiontype}_desc", 'ltool_like'),
            1
        ));
    }

    // Define icon background colour.
    $likeinfo = new \ltool_like\like();
    $page->add(new admin_setting_configcolourpicker(
        "ltool_like/likeiconbackcolor",
        get_string('iconbackcolor', 'local_learningtools', "like"),
        '',
        $likeinfo->get_tool_iconbackcolor()
    ));

    // Define icon colour.
    $page->add(new admin_setting_configcolourpicker(
        "ltool_like/likeiconcolor",
        get_string('iconcolor', 'local_learningtools', "like"),
        '',
        '#fff'
    ));

    // Define sticky.
    $page->add(new admin_setting_configcheckbox(
        "ltool_like/sticky",
        get_string('sticky', 'local_learningtools'),
        '',
        0
    ));
}
