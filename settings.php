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
 * Local plugin "Learning Tools" - settings file.
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__.'/lib.php');
global $CFG;

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_learningtools',
            get_string('pluginname', 'local_learningtools', null, true)));

    $page = new admin_settingpage('local_learningtools_settings',
            get_string('learningtoolssettings', 'local_learningtools', null, true));

    if ($ADMIN->fulltree) {

        $page->add(new admin_setting_configtext(
            'local_learningtools/notificationdisapper',
            new lang_string('notificationdisappertitle', 'local_learningtools'),
            new lang_string('notificationdisapperdesc', 'local_learningtools'),
            0
        ));

        // Visiability of fab button.
        $name = "local_learningtools/fabbuttonvisible";
        $title = get_string('visiblelearningtools', 'local_learningtools');
        $desc = get_string('fabbuttonvisible_desc', 'local_learningtools');
        $choices = array(
            'all' => get_string('everywhere', 'local_learningtools'),
            'allcourses' => get_string('allcourses', 'local_learningtools'),
            'specificcate' => get_string('specificcate', 'local_learningtools')
        );
        $default = 1;
        $setting = new admin_setting_configselect($name, $title, $desc, 'all', $choices);
        $page->add($setting);

        // Define show when active.
        $name = "local_learningtools/showactive";
        $title = get_string('alwaysactive', 'local_learningtools');
        $default = 0;
        $setting = new admin_setting_configcheckbox($name, $title, '', $default);
        $page->add($setting);

        // Select categories.
        $categories = \core_course_category::make_categories_list();
        $name = "local_learningtools/visiblecategories";
        $title = get_string('visiblecategories', 'local_learningtools');
        $setting = new admin_setting_configmultiselect($name, $title, '', null, $categories);
        $page->add($setting);

        // Disable specific activity types.
        $modules = $DB->get_records_menu('modules', array('visible' => 1), '', 'id,name');
        $modules[0] = get_string('none');
        ksort($modules);
        if (!empty($modules)) {
            $name = "local_learningtools/disablemod";
            $title = get_string('disablemodules', 'local_learningtools');
            $setting = new admin_setting_configmultiselect($name, $title, '', null, $modules);
            $page->add($setting);
        }

        // Fab button icon background color.
        $name = "local_learningtools/fabiconbackcolor";
        $title = get_string('fabiconbackcolor', 'local_learningtools');
        $default = "#0f6fc5";
        $setting = new admin_setting_configcolourpicker($name, $title, '', $default);
        $page->add($setting);

        // Fab button icon color.
        $name = "local_learningtools/fabiconcolor";
        $title = get_string('fabiconcolor', 'local_learningtools');
        $default = "#fff";
        $setting = new admin_setting_configcolourpicker($name, $title, '', $default);
        $page->add($setting);
        $page->add(new admin_setting_heading('learningtoolsusermenu',
            new lang_string('ltoolsusermenu', 'local_learningtools'),
            new lang_string('ltoolusermenu_help', 'local_learningtools')));
        $page->add(new admin_setting_description('bookmarksusermenu',
            new lang_string('bookmarksusermenu', 'local_learningtools'),
            new lang_string('bookmarksusermenu_help', 'local_learningtools')));
        $page->add(new admin_setting_description('notesusermenu',
            new lang_string('notesusermenu', 'local_learningtools'),
            new lang_string('notesusermenu_help', 'local_learningtools')));

    }
    $ADMIN->add('local_learningtools', $page);
    unset($page);
    $ltools = core_plugin_manager::instance()->get_plugins_of_type('ltool');
    if (!empty($ltools)) {
        foreach ($ltools as $plugin) {
            $plugin->load_settings($ADMIN, 'local_learningtools', $hassiteconfig);
        }
    }
    $page = null;
    $ADMIN->add('local_learningtools', new admin_externalpage('local_learningtools_lttool',
        get_string('learningtoolsltool', 'local_learningtools'),
        "$CFG->wwwroot/local/learningtools/learningtoolslist.php"));
}
