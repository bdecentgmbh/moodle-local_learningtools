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
 * The class defines the Resume course ltool.
 *
 * @package   ltool_focus
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_focus;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');

require_once(dirname(__DIR__).'/lib.php');

/**
 *  The class defines the focus ltool
 */
class focus extends \local_learningtools\learningtools {

    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'focus';

    /**
     * Tool context level
     * @var string
     */
    public $contextlevel = 'system';

    /**
     * focus name
     * @return string name
     *
     */
    public function get_tool_name() {
        return get_string('focus', 'local_learningtools');
    }

    /**
     * focus icon
     */
    public function get_tool_icon() {

        return 'fa fa-bullseye';
    }

    /**
     * focus icon background color
     */
    public function get_tool_iconbackcolor() {

        return '#343a40';
    }

    /**
     * Get the focus tool  content.
     *
     * @return string display tool focus plugin html.
     */
    public function get_tool_records() {
        global $SESSION;
        $focusmode = false;
        if (isset($SESSION->focusmode)) {
            if ($SESSION->focusmode) {
                $focusmode = true;
            }
        }
        $data = [];
        $data['name'] = $this->get_tool_name();
        $data['icon'] = $this->get_tool_icon();
        $data['ltoolfocus'] = true;
        $data['focushovername'] = get_string('focusmode', 'local_learningtools');
        $data['iconbackcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconbackcolor");
        $data['iconcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconcolor");
        $data['focusmode'] = $focusmode;
        $data['focuscssurl'] = ltool_focus_get_focus_css_url();
        ltool_focus_focusmode_actions();
        return $data;
    }

    /**
     * Return the template of focus fab button.
     *
     * @return string focus tool fab button html.
     */
    public function render_template() {
        $data = $this->get_tool_records();
        return ltool_focus_render_template($data);
    }

    /**
     * Load the required javascript files for focus.
     *
     * @return void
     */
    public function load_js() {
        // Load schedule tool js configuration.
        ltool_focus_load_js_config();
    }

    /**
     * Focus active tool status.
     * @return string Focus tool fab button html.
     */
    public function tool_active_condition() {
        global $SESSION;
        if (isset($SESSION->focusmode)) {
            if ($SESSION->focusmode) {
                return $this->render_template();
            }
        }
    }
}

