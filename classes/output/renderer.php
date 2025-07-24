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
 * Renderer for learning tools
 *
 * @package    local_learningtools
 * @copyright  2023 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learningtools\output;

use plugin_renderer_base;
use stdClass;

/**
 * Renderer class for learning tools.
 */
class renderer extends plugin_renderer_base {
    /**
     * Renders the action bar for a given page.
     *
     * @param general_action_bar $actionbar
     * @return string The HTML output
     */
    public function render_action_bar(general_action_bar $actionbar): string {
        $data = $actionbar->export_for_template($this);
        return $this->render_from_template($actionbar->get_template(), $data);
    }
}
