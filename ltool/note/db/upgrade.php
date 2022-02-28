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
 * Define upgrade function
 * @package    ltool_note
 * @copyright  bdecent GmbH 2021
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * ltool_note upgrade function.
 * @param int $oldversion old plugin version
 * @return bool
 */
function xmldb_ltool_note_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();
    if ($oldversion < 2021102700) {
        $table = new xmldb_table('learningtools_note');
        $field = new xmldb_field('pagetitle', XMLDB_TYPE_CHAR, '500', null,
        null, null, null, 'pagetype');
        if ($dbman->table_exists($table)) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
            $pageurlfield = new xmldb_field('pageurl', XMLDB_TYPE_TEXT, null, null, null, null, null,
                    'pagetitle');
            $dbman->change_field_type($table, $pageurlfield);
        }
        upgrade_plugin_savepoint(true, 2021102700, 'ltool', 'note');
    }
    if ($oldversion < 2022022600) {
        $oldtable = new xmldb_table('learningtools_note');
        if ($dbman->table_exists($oldtable)) {
            $dbman->rename_table($oldtable, 'ltool_note_data');
        }
        upgrade_plugin_savepoint(true, 2022022600, 'ltool', 'note');
    }
    return true;
}

