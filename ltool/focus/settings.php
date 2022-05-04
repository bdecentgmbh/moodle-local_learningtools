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
 * tool plugin "Learning Tools Resume course" - settings file.
 * @package   ltool_focus
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // Define icon background color.
    $name = "ltool_focus/focusiconbackcolor";
    $title = get_string('iconbackcolor', 'local_learningtools', "focus");
    $focusinfo = new \ltool_focus\focus();
    $default = $focusinfo->get_tool_iconbackcolor();
    $setting = new admin_setting_configcolourpicker($name, $title, '', $default);
    $page->add($setting);

    // Define icon color.
    $name = "ltool_focus/focusiconcolor";
    $title = get_string('iconcolor', 'local_learningtools', "focus");
    $default = '#fff';
    $setting = new admin_setting_configcolourpicker($name, $title, '', $default);
    $page->add($setting);

    // Define the Focus mode css.
    $name = "ltool_focus/focusmodecss";
    $title = get_string('focusmodecss', 'local_learningtools');
    $default = '#page-footer, .navbar, #page.drawers {
        display: none;
    }
    .header-maxwidth, .secondary-navigation {
    max-width: none;
    }
    #page.drawers {
        margin-top: 0;
    }
    .pagelayout-standard #page.drawers .main-inner, body.limitedwidth #page.drawers .main-inner {
        max-width: none;
    }
    #page {
        margin-top: 0px;
        transition: margin-top .5s;
    }
    #region-main-settings-menu.has-blocks, #region-main.has-blocks {
        width: 100%;
        margin: auto;
        transition: width .5s;
    }
    @media (min-width: 768px ) {
        body.drawer-open-left {
            transition: margin-left .5s;
            margin-left: 0;
        }
    }';
    $setting = new admin_setting_configtextarea($name, $title, '', $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Define Sticky.
    $name = "ltool_focus/sticky";
    $title = get_string('sticky', 'local_learningtools');
    $default = 0;
    $setting = new admin_setting_configcheckbox($name, $title, '', $default);
    $page->add($setting);
}
