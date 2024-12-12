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
 * List of the user bookmarks filter action.
 *
 * @package   ltool_bookmarks
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_bookmarks;
use moodle_url;
use context_user;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/local/learningtools/lib.php');
/**
 * List of the user bookmarks filter action.
 */
class bookmarkstool_filter {

    /**
     * @var int
     */
    public $userid;

    /**
     * @var int
     */
    public $courseid;

    /**
     * @var int
     */
    public $child;

    /**
     * @var array
     */
    public $urlparams;

    /**
     * @var int
     */
    public $teacher;

    /**
     * @var string
     */
    public $baseurl;

    /**
     * @var string
     */
    public $pageurl;

    /**
     * Loads bookmarks tools info.
     * @param int $userid current userid
     * @param int $courseid course id
     * @param int $childid child user or other user
     * @param int $teacher teacher view stauts
     * @param array $urlparams page url parameters
     * @param string $baseurl base url
     */
    public function __construct($userid, $courseid, $childid, $teacher, $urlparams, $baseurl) {
        $this->userid = $userid;
        $this->courseid = $courseid;
        $this->child = $childid;
        $this->urlparams = $urlparams;
        $this->teacher = $teacher;
        $this->baseurl = $baseurl;

        $pageurlparams = [];
        if ($teacher) {
            $pageurlparams['teacher'] = $teacher;
        }
        if ($courseid) {
            $pageurlparams['courseid'] = $courseid;
        }

        if ($childid) {
            $pageurlparams['userid'] = $childid;
        }
        $this->pageurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php', $pageurlparams);
    }

    /**
     * Displays the course selector info.
     * @param int $selectcourse select course id.
     * @param string $usercondition user select sql
     * @param array $userparams user params
     * @return string course selector html.
     */
    public function get_course_selector($selectcourse, $usercondition, $userparams) {
        global $DB, $OUTPUT;

        $template = [];
        $courses = [];
        $urlparams = [];
        $records = $DB->get_records_sql("SELECT * FROM {ltool_bookmarks_data} WHERE $usercondition", $userparams);

        if (!empty($records)) {
            foreach ($records as $record) {
                $instanceblock = local_learningtools_check_instanceof_block($record);
                if (isset($instanceblock->instance)) {
                    if ($instanceblock->instance == 'course' || $instanceblock->instance == 'mod') {
                        if ($instanceblock->courseid > 1) {
                            $courses[] = $instanceblock->courseid;
                        }
                    }
                }
            }
        }
        // Get courses.
        $courses = local_learningtools_get_courses_name(array_unique($courses), $this->baseurl, $selectcourse, $this->child);
        $template['courses'] = $courses;
        $template['pageurl'] = $this->pageurl->out(false);

        return $template;
    }


    /**
     * Displays the Bookmarks sort selector info.
     * @return string bookmarks sort html.
     */
    public function get_sort_instance() {
        global $OUTPUT;

        $template = [];
        $coursesortparams = array('sort' => 'course');
        $coursesortparams = array_merge($this->urlparams, $coursesortparams);

        $datesortparams = array('sort' => 'date');
        $datesortparams = array_merge($this->urlparams, $datesortparams);

        $dateselect = '';
        $courseselect = '';
        if (isset($this->urlparams['sort'])) {
            $sort = $this->urlparams['sort'];
            if ($sort == 'date') {
                $dateselect = "selected";
            } else if ($sort == 'course') {
                $courseselect = "selected";
            }
        }

        if (isset($this->urlparams['sorttype'])) {
            $sorttype = $this->urlparams['sorttype'];
            if ($sorttype == 'desc') {
                $iclass = 'fa fa-sort-amount-desc';
            } else {
                $iclass = 'fa fa-sort-amount-asc';
            }
        } else {
            $iclass = 'fa fa-sort-amount-asc';
            $sorttype = 'asc';
        }

        $coursesort = new moodle_url('/local/learningtools/ltool/bookmarks/list.php', $coursesortparams);
        $datesort = new moodle_url('/local/learningtools/ltool/bookmarks/list.php', $datesortparams);
        $template['coursesort'] = $coursesort->out(false);
        $template['datesort'] = $datesort->out(false);
        $template['dateselect'] = $dateselect;
        $template['courseselect'] = $courseselect;
        $template['iclass'] = $iclass;
        $template['sorttype'] = $sorttype;
        return $template;
    }

    /**
     * bookmarks filter main function
     * @param string $sqlconditions condition query
     * @param array $sqlparams record conditon params
     * @param string $sort sort order
     * @param int $sorttype sort type
     * @param int $page current records page count
     * @param int $perpage display perpage info
     * @return array available records to display bookmarks list data.
     */
    public function get_main_body($sqlconditions, $sqlparams, $sort, $sorttype, $page, $perpage) {
        global $DB, $OUTPUT;

        $orderconditions  = '';
        $filtersort = '';
        if ($sorttype == 'asc') {
            $filtersort = 'ASC';
        } else if ($sorttype == 'desc') {
            $filtersort = 'DESC';
        }

        if ($sort == 'course') {
            $orderconditions .= "ORDER BY c.fullname $filtersort, coursemodule";
        } else {
            $orderconditions .= "ORDER BY timecreated $filtersort";
        }

        $sql = "SELECT b.*, c.fullname
        FROM {ltool_bookmarks_data} b
        LEFT JOIN {course} c ON c.id = b.course
        WHERE $sqlconditions $orderconditions";
        $records = $DB->get_records_sql($sql, $sqlparams);
        $totalbookmarks = $DB->count_records_sql("SELECT count(*) FROM {ltool_bookmarks_data}
            WHERE $sqlconditions", $sqlparams);
        $pageingbar = $OUTPUT->paging_bar($totalbookmarks, $page, $perpage, $this->baseurl);

        $res = [];
        $reports = [];
        if (!empty($records)) {
            foreach ($records as $row) {
                $list = [];
                $data = local_learningtools_check_instanceof_block($row);
                $list['instance'] = $row->pagetitle;
                $list['instanceinfo'] = $this->get_instance_bookmarkinfo($data);
                $list['courseinstance'] = ($data->instance == 'course') ? true : false;
                $list['time'] = $this->get_bookmark_time($row);
                $list['delete'] = $this->get_bookmark_deleteinfo($row);
                $list['view'] = $this->get_bookmark_viewinfo($row);
                $list['course'] = $row->course;
                $reports[] = $list;
            }
        }
        $res['pageingbar'] = $pageingbar;
        $res['bookmarks'] = $reports;
        return $res;
    }

    /**
     * List of the bookmarks get the instance name column.
     * @param object $data
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
     * List of the bookmarks get info column.
     * @param \stdclass $data instance data
     * @return string result
     */
    public function get_instance_bookmarkinfo($data) {
         $bookmarkinfo = '';
        if ($data->instance == 'course') {
            $bookmarkinfo = local_learningtools_get_course_categoryname($data->courseid);
        } else if ($data->instance == 'user') {
            $bookmarkinfo = '';
        } else if ($data->instance == 'mod') {
            $bookmarkinfo = ltool_bookmarks_get_bookmarks_module_coursesection($data);
        } else if ($data->instance == 'system') {
             $bookmarkinfo = '';
        } else if ($data->instance == 'block') {
             $bookmarkinfo = '';
        }
        return $bookmarkinfo;
    }

    /**
     * List of the bookmarks get the started time.
     * @param \stdclass $record
     * @return string result
     */
    public function get_bookmark_time($record) {
        return userdate($record->timecreated, get_string("baseformat", "local_learningtools"), '', false);
    }

    /**
     * List of the bookmarks get the delete action.
     * @param  stdclass $row
     * @return string result
     */
    public function get_bookmark_deleteinfo($row) {
        global $OUTPUT, $USER;
        $context = \context_system::instance();
        $particularuser = null;

        if ($this->courseid || $this->child) {
            $capability = "ltool/bookmarks:managebookmarks";

            if ($this->courseid && !$this->child) {
                $context = \context_course::instance($this->courseid);
            } else if ($this->child) {

                if ($this->teacher) {
                    $context = \context_course::instance($this->courseid);
                } else {
                    if ($this->child != $USER->id) {
                        $context = context_user::instance($this->child);
                        $particularuser = $USER->id;
                    } else {
                        $capability = 'ltool/bookmarks:manageownbookmarks';
                        $context = \context_system::instance();
                    }
                }
            }

            if (has_capability($capability, $context, $particularuser)) {
                $buttons = [];
                $returnurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php');
                $deleteparams = array('delete' => $row->id, 'sesskey' => sesskey(),
                'courseid' => $this->courseid);
                $deleteparams = array_merge($deleteparams, $this->urlparams);
                $url = new moodle_url($returnurl, $deleteparams);
                $strdelete = get_string('delete');
                $buttons[] = \html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
                $buttonhtml = implode(' ', $buttons);
                return $buttonhtml;
            }

        } else {
            if (has_capability('ltool/bookmarks:manageownbookmarks', $context)) {
                $buttons = [];
                $returnurl = new moodle_url('/local/learningtools/ltool/bookmarks/list.php');
                $deleteparams = array('delete' => $row->id, 'sesskey' => sesskey());
                $deleteparams = array_merge($deleteparams, $this->urlparams);
                $url = new moodle_url($returnurl, $deleteparams);;
                $strdelete = get_string('delete');
                $buttons[] = \html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
                $buttonhtml = implode(' ', $buttons);
                return $buttonhtml;
            }
        }
        return '';
    }

    /**
     * list of the bookmarks get the view action.
     * @param  mixed $row
     * @return mixed result
     */
    public function get_bookmark_viewinfo($row) {
        return local_learningtools_get_instance_tool_view_url($row);
    }

}
