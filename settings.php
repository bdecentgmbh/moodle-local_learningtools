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
 * Local plugin "Learning Tools" - settings file
 *
 * @package    local_learningtools
 * @copyright  2021 lmsace
 */

defined('MOODLE_INTERNAL') || die();

 global $CFG;

 if ($hassiteconfig) {
    
 	$ADMIN->add('localplugins', new admin_category('local_learningtools',
            get_string('pluginname', 'local_learningtools', null, true)));

 	$page = new admin_settingpage('local_learningtools_settings',
            get_string('pluginname', 'local_learningtools', null, true));

 	 if ($ADMIN->fulltree) {

 	 	$page->add(new admin_setting_configtext(
            'local_learningtools/notificationdisapper',
            new lang_string('notificationdisappertitle', 'local_learningtools'),
            new lang_string('notificationdisapperdesc', 'local_learningtools'),
            0
        ));
 	 }
 	$ADMIN->add('localplugins', $page);

    $ADMIN->add('localplugins', new admin_externalpage('local_learningtools_lttool', 
        get_string('learningtoolsltool', 'local_learningtools'), "$CFG->wwwroot/local/learningtools/learningtoolslist.php"));

 }