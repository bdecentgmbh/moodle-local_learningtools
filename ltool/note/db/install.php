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
 * Define install function
 * @category   Install
 * @package    lttool_note
 * @copyright  bdecent GmbH 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * lttool_note install function.
 *
 * @return void
 */

function xmldb_ltool_note_install() {
    global $DB;

    $plugin = 'note';
    $strpluginname = get_string('pluginname', 'ltool_' . $plugin);
    if (!$DB->record_exists('learningtools_products', array('shortname' => $plugin)) ) {
        $lasttool = $DB->get_record_sql(' SELECT id FROM {learningtools_products} ORDER BY id DESC LIMIT 1', null);
        $record = new stdClass;
        $record->shortname = $plugin;
        $record->name = $strpluginname;
        $record->status = 1;
        $record->sort = (!empty($lasttool)) ? $lasttool->id + 1 : 1;
        $record->timecreated = time();
        $DB->insert_record('learningtools_products', $record);
    }
}
