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
 * Provides an overview of installed realated learning tools plugins
 *
 * Displays the list of found local plugins, their version (if found) and
 * a link to delete the local plugin.
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

$action = optional_param('action', '', PARAM_TEXT);
$tool = optional_param('tool', '', PARAM_TEXT);

//strings
$strenable    = get_string('enable');
$strdisable   = get_string('disable');
$strup        = get_string('up');
$strdown      = get_string('down');
$strname      = get_string('name');
$strversion   = get_string('version');
$uninstallplug= get_string('uninstallplugin', 'core_admin');
$strname      = get_string('name');


// show/hide tools
if (!empty($action) && !empty($tool)) {
    if ($action == 'disable') {
        $DB->set_field('learningtools_products', 'status', 0, array('shortname' => $tool));
    } else if ($action == 'enable') {
        $DB->set_field('learningtools_products', 'status', 1, array('shortname' => $tool));
    } else if ($action == 'up') {
        $curtool = $DB->get_record('learningtools_products', array('shortname' => $tool));
        $prevtool = $DB->get_record('learningtools_products', array('sort' => $curtool->sort - 1));
        $DB->set_field('learningtools_products', 'sort', $prevtool->sort, array('shortname' => $curtool->shortname));
        $DB->set_field('learningtools_products', 'sort', $curtool->sort, array('shortname' => $prevtool->shortname));
    } else if  ($action = "down") {
        $basetool = $DB->get_record('learningtools_products', array('shortname' => $tool));
        $nexttool = $DB->get_record('learningtools_products', array('sort' => $basetool->sort + 1));
        $DB->set_field('learningtools_products', 'sort', $nexttool->sort, array('shortname' => $basetool->shortname));
        $DB->set_field('learningtools_products', 'sort', $basetool->sort, array('shortname' => $nexttool->shortname));
    }
    redirect($pageurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('learningtools', 'local_learningtools'));
/// Print the table of all installed admin tool plugins
$table = new flexible_table('learningtools_products_info');
$table->define_columns(array('name', 'version', 'status', 'updown', 'uninstall'));

$table->define_headers(array($strname, $strversion, $strenable.'/'.$strdisable, 
$strup.'/'.$strdown, $uninstallplug));

$table->define_baseurl($PAGE->url);
$table->set_attribute('id', 'learningtool-products');
$table->set_attribute('class', 'learningtool generaltable');
$table->setup();

$plugins = array();
$pluginman = core_plugin_manager::instance();

foreach (core_component::get_plugin_list('ltool') as $plugin => $plugindir) {

        $location = "$CFG->dirroot/local/learningtools/$plugin";
        $class = "learningtool_{$plugin}_instance";

        if (file_exists("$location/lib.php")) {
            include_once("$location/lib.php");
            if (class_exists($class)) {

                if (get_string_manager()->string_exists('pluginname', 'tool_' . $plugin)) {
                    $strpluginname = get_string('pluginname', 'tool_' . $plugin);
                } else {
                    $strpluginname = $plugin;
                }

                //record update 
                if (!$DB->record_exists('learningtools_products', array('shortname' => $plugin)) ) {

                    $lasttool = $DB->get_record_sql(' SELECT id FROM {learningtools_products} ORDER BY id DESC LIMIT 1', null);
                    $record =  new stdClass;
                    $record->shortname = $plugin;
                    $record->name = $strpluginname;
                    $record->status = 1;
                    $record->sort = (!empty($lasttool)) ? $lasttool->id + 1 : 1;
                    $record->timecreated = time();
                    $DB->insert_record('learningtools_products', $record);
                }
                $plugins[$plugin] = $strpluginname;
            }
        }
}

//core_collator::asort($plugins);

$spacer = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'iconsmall'));
$cnt = 0;
$learningtools = $DB->get_records('learningtools_products', null, 'sort');

foreach ($learningtools as $tool) {
    $plugin = $tool->shortname;
    $uninstall = '';
    $PluginInfo = $pluginman->get_plugin_info('ltool_'.$plugin);
    
    if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('ltool_'.$plugin, 'manage')) {
        $uninstall = html_writer::link($uninstallurl, get_string('uninstallplugin', 'core_admin'));
    }
    // plugin version
    $version = get_config('ltool_' . $plugin);
    if (!empty($version->version)) {
        $version = $version->version;
    } else {
        $version = '?';
    }

    //plugin enable/disable
    $status = '-';
    $lt_tool = $DB->get_record('learningtools_products', array('shortname' => $plugin));
    if ($lt_tool->status) {
        $aurl = new moodle_url($PAGE->url, array('action'=>'disable', 'tool'=>$plugin));
        $status = "<a href=\"$aurl\">";
        $status .= $OUTPUT->pix_icon('t/hide', $strdisable) . '</a>';
        $enabled = true;
        //$displayname = $name;
    } else {
        $aurl = new moodle_url($PAGE->url, array('action'=>'enable', 'tool'=>$plugin));
        $status = "<a href=\"$aurl\">";
        $status .= $OUTPUT->pix_icon('t/show', $strenable) . '</a>';
        $enabled = false;
        //$displayname = $name;
        //$class = 'dimmed_text';
    }

    // plugin sort option 
    $updown = '';
    if ($cnt) {
        $updown .= html_writer::link($PAGE->url->out(false, array('action' => 'up', 'tool' => $plugin)),
            $OUTPUT->pix_icon('t/up', $strup, 'moodle', array('class' => 'iconsmall'))). '';
    } else {
        $updown .= $spacer;
    }
    
    if ($cnt < count($learningtools) - 1) {
        $updown .= '&nbsp;'.html_writer::link($PAGE->url->out(false, array('action' => 'down', 'tool' => $plugin)),
            $OUTPUT->pix_icon('t/down', $strdown, 'moodle', array('class' => 'iconsmall')));
    } else {
        $updown .= $spacer;
    }
    $cnt++;

    $table->add_data(array($tool->name, $version, $status, $updown, $uninstall));
}

$table->print_html();



echo $OUTPUT->footer();
