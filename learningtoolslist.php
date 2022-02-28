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
 * List of the Available learningtools actions.
 *
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir.'/tablelib.php');
require_login();
$context = context_system::instance();
$PAGE->set_context($context);
require_capability("moodle/site:config", $context);
$pageurl = new moodle_url("/local/learningtools/learningtoolslist.php");
$PAGE->set_url($pageurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('learningtools', 'local_learningtools'));
$PAGE->set_heading(get_string('learningtools', 'local_learningtools'));

$action = optional_param('action', '', PARAM_ALPHANUMEXT);
$tool = optional_param('tool', '', PARAM_ALPHANUMEXT);

// Strings.
$strenable    = get_string('enable');
$strdisable   = get_string('disable');
$strup        = get_string('up');
$strdown      = get_string('down');
$strname      = get_string('name');
$strversion   = get_string('version');
$uninstallplug = get_string('uninstallplugin', 'core_admin');
$strname      = get_string('name');

// Show/hide tools.
if (!empty($action) && !empty($tool)) {
    require_sesskey();
    if ($action == 'disable') {
        $DB->set_field('local_learningtools_products', 'status', 0, array('shortname' => $tool));
    } else if ($action == 'enable') {
        $DB->set_field('local_learningtools_products', 'status', 1, array('shortname' => $tool));
    } else if ($action == 'up') {
        $curtool = $DB->get_record('local_learningtools_products', array('shortname' => $tool));
        $prevtool = $DB->get_record('local_learningtools_products', array('sort' => $curtool->sort - 1));
        $DB->set_field('local_learningtools_products', 'sort', $prevtool->sort, array('shortname' => $curtool->shortname));
        $DB->set_field('local_learningtools_products', 'sort', $curtool->sort, array('shortname' => $prevtool->shortname));
    } else if ($action = "down") {
        $basetool = $DB->get_record('local_learningtools_products', array('shortname' => $tool));
        $nexttool = $DB->get_record('local_learningtools_products', array('sort' => $basetool->sort + 1));
        $DB->set_field('local_learningtools_products', 'sort', $nexttool->sort, array('shortname' => $basetool->shortname));
        $DB->set_field('local_learningtools_products', 'sort', $basetool->sort, array('shortname' => $nexttool->shortname));
    }
    redirect($pageurl);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('learningtools', 'local_learningtools'));

// Print the table of all installed ltools plugins.
$table = new flexible_table('learningtool_products_info');
$table->define_columns(array('name', 'version', 'status', 'updown', 'uninstall'));
$table->define_headers(array($strname, $strversion, $strenable.'/'.$strdisable,
$strup.'/'.$strdown, $uninstallplug));
$table->define_baseurl($PAGE->url);
$table->set_attribute('id', 'learningtool-products');
$table->set_attribute('class', 'learningtool generaltable');
$table->setup();

$plugins = array();
$pluginman = core_plugin_manager::instance();

$spacer = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'iconsmall'));
$cnt = 0;
$learningtools = $DB->get_records('local_learningtools_products', null, 'sort');

foreach ($learningtools as $tool) {
    $plugin = $tool->shortname;
    $uninstall = '';
    if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('ltool_'.$plugin, 'manage')) {
        $uninstall = html_writer::link($uninstallurl, get_string('uninstallplugin', 'core_admin'));
    }
    // Plugin version.
    $version = get_config('ltool_' . $plugin);
    if (!empty($version->version)) {
        $version = $version->version;
    } else {
        $version = '?';
    }

    // Plugin enable/disable.
    $status = '-';
    $lttool = $DB->get_record('local_learningtools_products', array('shortname' => $plugin));
    if ($lttool->status) {
        $aurl = new moodle_url($PAGE->url, array('action' => 'disable', 'tool' => $plugin, 'sesskey' => sesskey()));
        $status = "<a href=\"$aurl\">";
        $status .= $OUTPUT->pix_icon('t/hide', $strdisable) . '</a>';
        $enabled = true;
    } else {
        $aurl = new moodle_url($PAGE->url, array('action' => 'enable', 'tool' => $plugin, 'sesskey' => sesskey()));
        $status = "<a href=\"$aurl\">";
        $status .= $OUTPUT->pix_icon('t/show', $strenable) . '</a>';
        $enabled = false;
    }

    // Plugin sort option.
    $updown = '';
    if ($cnt) {
        $updown .= html_writer::link($PAGE->url->out(false, array('action' => 'up', 'tool' => $plugin, 'sesskey' => sesskey())),
            $OUTPUT->pix_icon('t/up', $strup, 'moodle', array('class' => 'iconsmall'))). '';
    } else {
        $updown .= $spacer;
    }
    if ($cnt < count($learningtools) - 1) {
        $updown .= '&nbsp;'.html_writer::link($PAGE->url->out(false, array('action' => 'down', 'tool' => $plugin,
        'sesskey' => sesskey())),
            $OUTPUT->pix_icon('t/down', $strdown, 'moodle', array('class' => 'iconsmall')));
    } else {
        $updown .= $spacer;
    }
    $cnt++;
    $table->add_data(array($tool->name, $version, $status, $updown, $uninstall));
}
// Print the ltool plugins table.
$table->print_html();
echo $OUTPUT->footer();
