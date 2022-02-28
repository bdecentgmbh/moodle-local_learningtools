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
 * Table info class in the Bookmarks.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_bookmarks;
use table_sql;
use stdclass;
use moodle_url;
use html_writer;
use context_system;
use context_course;
use context_user;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');
require_once($CFG->libdir. '/tablelib.php');

/**
 * Bookmarks table.
 */
class bookmarkstool_table extends \table_sql {

    /**
     * Fetch bookmarks list.
     * @param mixed $tableid idendifiy unique table id
     * @param int $courseid courseid
     * @param int $child userid
     * @param bool $teacher teacher status
     * @param array $urlparams url params
     * @return void
     */
    public function __construct($tableid, $courseid, $child, $teacher, $urlparams) {
        parent::__construct($tableid);
        $this->courseid = $courseid;
        $this->child = $child;
        $this->urlparams = $urlparams;
        $this->teacher = $teacher;

        $columns = array();
        $headers = array();

        $columns[] = 'icon';
        $headers[] = get_string('bookicon', 'local_learningtools');

        $columns[] = 'course';
        $headers[] = get_string('bookmarks', 'local_learningtools');

        $columns[] = 'bookmarkinfo';
        $headers[] = get_string('bookmarkinfo', 'local_learningtools');

        $columns[] = 'timecreated';
        $headers[] = get_string('time', 'local_learningtools');

        $columns[] = 'delete';
        $headers[] = get_string('delete', 'local_learningtools');

        $columns[] = 'view';
        $headers[] = get_string('view', 'local_learningtools');

        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->no_sorting('icon');
        $this->no_sorting('bookmarkinfo');
        $this->no_sorting('delete');
        $this->no_sorting('view');
    }

    /**
     * The icon column displays in the list of the bookmarks.
     * @param \stdclass $row
     * @return string icon of bookmarks tool
     */
    public function col_icon(stdclass $row) {
        return '<i class="fa fa-bookmark">';
    }

    /**
     * The instance name column displays in the list of the bookmarks.
     * @param \stdclass $row
     * @return string instance bookmark
     */
    public function col_course(stdclass $row) {
        $data = local_learningtools_check_instanceof_block($row);
        return $this->get_instance_bookmark($data);
    }
    /**
     * List of the bookmarks get the instance name column.
     * @param object $data instance of bookmark data
     * @return string result
     */
    public function get_instance_bookmark($data) {
        $bookmark = '';
        if ($data->instance == 'course') {
            $bookmark = local_learningtools_get_course_name($data->courseid);
        } else if ($data->instance == 'user') {
            $bookmark = 'user';
        } else if ($data->instance == 'mod') {
            $bookmark = local_learningtools_get_module_name($data);
        } else if ($data->instance == 'system') {
             $bookmark = 'system';
        } else if ($data->instance == 'block') {
             $bookmark = 'block';
        }
        return $bookmark;
    }

    /**
     * The instance info column displays in the list of the bookmarks.
     * @param \stdclass $row
     * @return string display the bookmark instance info
     */
    public function col_bookmarkinfo(stdclass $row) {
        $data = local_learningtools_check_instanceof_block($row);
        return $this->get_instance_bookmarkinfo($data);
    }
    /**
     * Get the instance info details.
     * @param object $data instance data
     */
    public function get_instance_bookmarkinfo($data) {
         $bookmarkinfo = '';
        if ($data->instance == 'course') {
            $bookmarkinfo = local_learningtools_get_course_categoryname($data->courseid);
        } else if ($data->instance == 'user') {
            $bookmarkinfo = 'user';
        } else if ($data->instance == 'mod') {
            $bookmarkinfo = ltool_bookmarks_get_bookmarks_module_coursesection($data);
        } else if ($data->instance == 'system') {
             $bookmarkinfo = 'system';
        } else if ($data->instance == 'block') {
             $bookmarkinfo = 'block';
        }
        return $bookmarkinfo;
    }
    /**
     * The started time column displays in the list of the bookmarks.
     * @param \stdclass $row
     * @return mixed display the bookmarks date
     */
    public function col_timecreated(stdclass $row) {
        return userdate($row->timecreated, get_string("baseformat", "local_learningtools"), '', false);
    }

    /**
     * The delete action column displays in the list of the bookmarks.
     * @param \stdclass $row
     * @return string Display the delete action bookmarks
     */
    public function col_delete(stdclass $row) {
        global $OUTPUT, $USER;
        $context = context_system::instance();
        $particularuser = null;

        if ($this->courseid || $this->child) {
            $capability = "ltool/bookmarks:managebookmarks";

            if ($this->courseid && !$this->child) {
                $context = context_course::instance($this->courseid);
            } else if ($this->child) {

                if ($this->teacher) {
                    $context = context_course::instance($this->courseid);
                } else {
                    $context = context_user::instance($this->child);
                    $particularuser = $USER->id;
                }
            }

            if (has_capability($capability, $context, $particularuser)) {
                $strdelete = get_string('delete');
                $buttons = [];
                $returnurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php');
                $deleteparams = array('delete' => $row->id, 'sesskey' => sesskey(),
                    'courseid' => $this->courseid);
                $deleteparams = array_merge($deleteparams, $this->urlparams);
                $url = new moodle_url($returnurl, $deleteparams);
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
                $buttonhtml = implode(' ', $buttons);
                return $buttonhtml;
            }

        } else {
            if (has_capability('ltool/bookmarks:manageownbookmarks', $context)) {
                $strdelete = get_string('delete');
                $buttons = [];
                $returnurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php');
                $deleteparams = array('delete' => $row->id, 'sesskey' => sesskey());
                $deleteparams = array_merge($deleteparams, $this->urlparams);
                $url = new moodle_url($returnurl, $deleteparams);;
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
                $buttonhtml = implode(' ', $buttons);
                return $buttonhtml;
            }
        }
        return '';
    }

    /**
     * The view action column displays in the list of the bookmarks.
     * @param \stdclass $row
     * @return string view the bookmarks url
     */
    public function col_view(stdclass $row) {
        return local_learningtools_get_instance_tool_view_url($row);
    }
}
