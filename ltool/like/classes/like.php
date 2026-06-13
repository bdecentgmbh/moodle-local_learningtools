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
 * The class defines the Like ltool.
 *
 * @package   ltool_like
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_like;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/learningtools/lib.php');
require_once(dirname(__DIR__) . '/lib.php');

/**
 * The Like ltool: lets a user react to the current page.
 */
class like extends \local_learningtools\learningtools {
    /**
     * Tool shortname.
     *
     * @var string
     */
    public $shortname = 'like';

    /**
     * Tool context level.
     *
     * @var string
     */
    public $contextlevel = 'system';

    /**
     * Tool name.
     *
     * @return string
     */
    public function get_tool_name() {
        return get_string('like', 'ltool_like');
    }

    /**
     * Tool icon.
     *
     * @return string
     */
    public function get_tool_icon() {
        return 'fa fa-thumbs-up';
    }

    /**
     * Tool icon background colour.
     *
     * @return string
     */
    public function get_tool_iconbackcolor() {
        return '#343a40';
    }

    /**
     * Build the tool data for the page (page identity, own reaction, capability-gated counts).
     *
     * @return array
     */
    public function get_tool_records() {
        global $USER, $COURSE, $PAGE;
        $pageurl = local_learningtools_clean_mod_assign_userlistid($PAGE->url->out(false), $PAGE->cm);
        $cancount = has_capability('ltool/like:viewcount', $PAGE->context);
        $data = [
            'name' => $this->get_tool_name(),
            'icon' => $this->get_tool_icon(),
            'id' => $this->shortname,
            'user' => $USER->id,
            'course' => $COURSE->id,
            'pagetype' => $PAGE->pagetype,
            'pagetitle' => $PAGE->title,
            'coursemodule' => local_learningtools_get_moduleid($PAGE->context->id, $PAGE->context->contextlevel),
            'contextlevel' => $PAGE->context->contextlevel,
            'contextid' => $PAGE->context->id,
            'sesskey' => sesskey(),
            'pageurl' => $pageurl,
            'reactionhovername' => get_string('ratethispage', 'ltool_like'),
            'iconbackcolor' => get_config('ltool_like', 'likeiconbackcolor'),
            'iconcolor' => get_config('ltool_like', 'likeiconcolor'),
            'currentreaction' => (string) ltool_like_get_user_reaction($PAGE->context->id, $pageurl, $USER->id),
            'cancount' => $cancount,
            'canviewusers' => has_capability('ltool/like:viewlikes', $PAGE->context),
        ];
        if ($cancount) {
            $data['counts'] = ltool_like_get_counts($PAGE->context->id, $pageurl);
        }
        return $data;
    }

    /**
     * Queue the like tool javascript.
     *
     * @return void
     */
    public function load_js() {
        ltool_like_load_like_js_config($this->get_tool_records());
    }

    /**
     * Render the inline reaction button group.
     *
     * @return string
     */
    public function render_template() {
        return ltool_like_render_template($this->get_tool_records());
    }

    /**
     * Show the tool in the "active tools" area when the user already reacted.
     *
     * @return string|void
     */
    public function tool_active_condition() {
        global $PAGE, $USER;
        $pageurl = local_learningtools_clean_mod_assign_userlistid($PAGE->url->out(false), $PAGE->cm);
        if (ltool_like_get_user_reaction($PAGE->context->id, $pageurl, $USER->id)) {
            return $this->render_template();
        }
    }
}
