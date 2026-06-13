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
 * tool plugin "Learning Tools Report" - settings file.
 *
 * @package   ltool_report
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {
    // The supported issue types (mirrors ltool_report_get_issue_types(); settings.php runs
    // without $CFG in scope, so the tool lib cannot be required here).
    $issuetypes = ['technical', 'question', 'accessibility', 'content'];

    $reportinfo = new \ltool_report\report();

    // Heading for the issue types and their recipient roles.
    $page->add(new admin_setting_heading(
        'ltool_report/issuetypesheading',
        get_string('issuetypesheading', 'ltool_report'),
        get_string('issuetypesheading_desc', 'ltool_report')
    ));

    // Role choices for the recipient selectors (unavailable during the very first install).
    $roleoptions = [];
    if (!during_initial_install()) {
        $roleoptions = role_fix_names(get_all_roles(), null, ROLENAME_ORIGINALANDSHORT, true);
    }

    // For each issue type: enable toggle + the role(s) whose holders receive it.
    foreach ($issuetypes as $type) {
        $label = get_string('issuetype_' . $type, 'ltool_report');
        $page->add(new admin_setting_configcheckbox(
            "ltool_report/enable{$type}",
            get_string('settingenable', 'ltool_report', $label),
            get_string('settingenable_desc', 'ltool_report', $label),
            1
        ));
        $page->add(new admin_setting_configmultiselect(
            "ltool_report/roles{$type}",
            get_string('settingroles', 'ltool_report', $label),
            get_string('settingroles_desc', 'ltool_report', $label),
            [],
            $roleoptions
        ));
    }

    // Additional recipients for every report.
    $page->add(new admin_setting_configcheckbox(
        "ltool_report/alsosupport",
        get_string('alsosupport', 'ltool_report'),
        get_string('alsosupport_desc', 'ltool_report'),
        0
    ));
    $page->add(new admin_setting_users_with_capability(
        "ltool_report/specificuser",
        get_string('specificuser', 'ltool_report'),
        get_string('specificuser_desc', 'ltool_report'),
        '',
        'ltool/report:receivereport'
    ));

    // Define icon background colour.
    $page->add(new admin_setting_configcolourpicker(
        "ltool_report/reporticonbackcolor",
        get_string('iconbackcolor', 'local_learningtools', "report"),
        '',
        $reportinfo->get_tool_iconbackcolor()
    ));

    // Define icon colour.
    $page->add(new admin_setting_configcolourpicker(
        "ltool_report/reporticoncolor",
        get_string('iconcolor', 'local_learningtools', "report"),
        '',
        '#fff'
    ));

    // Define sticky.
    $page->add(new admin_setting_configcheckbox(
        "ltool_report/sticky",
        get_string('sticky', 'local_learningtools'),
        '',
        0
    ));
}
