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
 * Subplugin info class.
 *
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learningtools\plugininfo;

use core\plugininfo\base;

defined('MOODLE_INTERNAL') || die();

/**
 * Ltools subplugin define classes.
 */
class ltool extends base {

    /**
     * Returns the information about plugin availability
     *
     * True means that the plugin is enabled. False means that the plugin is
     * disabled. Null means that the information is not available, or the
     * plugin does not support configurable availability or the availability
     * can not be changed.
     *
     * @return null|bool
     */
    public function is_enabled() {
        return true;
    }

    /**
     * Should there be a way to uninstall the plugin via the administration UI.
     *
     * By default uninstallation is not allowed, plugin developers must enable it explicitly!
     *
     * @return bool
     */
    public function is_uninstall_allowed() {
        return true;
    }
}
