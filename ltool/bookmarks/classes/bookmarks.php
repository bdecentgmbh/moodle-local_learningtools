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
 * The class defines the Bookmarks ltool.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_bookmarks;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');

require_once(dirname(__DIR__).'/lib.php');

/**
 *  The class defines the Bookmarks ltool
 */
class bookmarks extends \local_learningtools\learningtools {

    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'bookmarks';

    /**
     * Tool context level
     *
     * @var string
     */
    public $contextlevel = 'system';

    /**
     * Bookmarks name
     * @return string name
     *
     */
    public function get_tool_name() {
        return get_string('bookmarks', 'local_learningtools');
    }

    /**
     * Bookmarks icon
     */
    public function get_tool_icon() {

        return 'fa fa-bookmark';
    }

    /**
     * Bookmarks icon background color
     */
    public function get_tool_iconbackcolor() {

        return '#343a40';
    }

    /**
     * Get the bookmarks content.
     *
     * @return string display tool bookmarks plugin html.
     */
    public function get_tool_records() {
        global $CFG, $USER, $COURSE, $PAGE;
        $data = [];
        $data['name'] = $this->get_tool_name();
        $data['icon'] = $this->get_tool_icon();
        $data['toolurl'] = "$CFG->wwwroot/local/learningtools/ltool/".$this->shortname."/".$this->shortname."_info.php";
        $data['id'] = $this->shortname;
        $data['user'] = $USER->id;
        $data['course'] = $COURSE->id;
        $data['pagetype'] = $PAGE->pagetype;
        $data['pagetitle'] = $PAGE->title;
        $data['coursemodule'] = local_learningtools_get_moduleid($PAGE->context->id, $PAGE->context->contextlevel);
        $data['contextlevel'] = $PAGE->context->contextlevel;
        $data['contextid'] = $PAGE->context->id;
        $data['sesskey'] = sesskey();
        $data['ltbookmark'] = true;
        $data['bookmarkhovername'] = get_string('addbookmark', 'local_learningtools');
        $pageurl = local_learningtools_clean_mod_assign_userlistid($PAGE->url->out(false), $PAGE->cm);
        $data['pageurl'] = $pageurl;
        $data['pagebookmarks'] = ltool_bookmarks_check_page_bookmarks_exist($PAGE->context->id, $pageurl, $USER->id);
        $data['iconbackcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconbackcolor");
        $data['iconcolor'] = get_config("ltool_{$this->shortname}", "{$this->shortname}iconcolor");
        return $data;
    }

    /**
     * Load the required javascript files for bookmarks.
     *
     * @return void
     */
    public function load_js() {
        $data = $this->get_tool_records();
        // Load bookmarks tool js configuration.
        ltool_bookmarks_load_bookmarks_js_config($data);
    }

    /**
     * Return the template of Bookmark fab button.
     *
     * @return string Bookmark tool fab button html.
     */
    public function render_template() {
        $data = $this->get_tool_records();
        return ltool_bookmarks_render_template($data);
    }

    /**
     * Bookmarks active tool status.
     * @return string Bookmark tool fab button html.
     */
    public function tool_active_condition() {
        global $PAGE, $USER;
        $pageurl = local_learningtools_clean_mod_assign_userlistid($PAGE->url->out(false), $PAGE->cm);
        $pagebookmarks = ltool_bookmarks_check_page_bookmarks_exist($PAGE->context->id, $pageurl, $USER->id);
        if ($pagebookmarks) {
            return $this->render_template();
        }
    }

}
