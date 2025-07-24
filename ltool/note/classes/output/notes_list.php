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
 * Notes listing page.
 *
 * @package   ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace ltool_note\output;

use renderable;
use templatable;
use renderer_base;
use stdclass;

/**
 * Class to display notes list with search functionality.
 *
 * @package    ltool_note
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class notes_list implements renderable, templatable {
    /** @var int $courseid The course id */
    private $courseid;
    /** @var string $search The search term */
    private $search;

    /**
     * Section ID to filter notes by a specific section.
     * @var int|null
     */
    protected $sectionid;

    /**
     * Activity ID to filter notes by a specific activity.
     * @var int|null
     */
    protected $activity;

    /**
     * Filter.
     * @var string
     */
    protected $filter;

    /**
     * print.
     * @var bool
     */
    protected $print;

    /**
     * Constructor.
     *
     * @param int $courseid The course ID
     * @param int $sectionid The section ID
     * @param int $activity The activity ID
     * @param string $search The search term
     * @param string $filter Filter
     * @param bool $print
     */
    public function __construct($courseid, $sectionid, $activity, $search = '', $filter = '', $print = false) {
        $this->courseid = $courseid;
        $this->sectionid = $sectionid;
        $this->activity = $activity;
        $this->search = $search;
        $this->filter = $filter;
        $this->print = $print;
    }

    /**
     * Export data for template.
     *
     * @param renderer_base $output The renderer
     * @return array Data for template
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $USER;

        $params = ['courseid' => $this->courseid, 'userid' => $USER->id];
        $searchconditions = [];
        $searchjoins = '';

        // Build advanced search conditions.
        if (!empty($this->search)) {
            $searchterm = $this->search;

            // Search in note content and title.
            $searchconditions[] = $DB->sql_like('n.note', ':note', false, false);
            $searchconditions[] = $DB->sql_like('n.pagetitle', ':pagetitle', false, false);
            $params['note'] = '%' . $DB->sql_like_escape($searchterm) . '%';
            $params['pagetitle'] = '%' . $DB->sql_like_escape($searchterm) . '%';

            // Search by activity type and name.
            $searchjoins .= " LEFT JOIN {course_modules} cm ON cm.id = n.coursemodule ";
            $searchjoins .= " LEFT JOIN {modules} m ON m.id = cm.module ";
            $searchjoins .= " LEFT JOIN {course_sections} cs ON cs.id = cm.section ";

            // Search by module name.
            $searchconditions[] = $DB->sql_like('m.name', ':modname', false, false);
            $params['modname'] = '%' . $DB->sql_like_escape($searchterm) . '%';

            // Search by section name.
            $searchconditions[] = $DB->sql_like('cs.name', ':sectionname', false, false);
            $params['sectionname'] = '%' . $DB->sql_like_escape($searchterm) . '%';

            // Search by date (month name, year, or month/year format).
            $months = [
                'january' => 1, 'february' => 2, 'march' => 3, 'april' => 4, 'may' => 5, 'june' => 6,
                'july' => 7, 'august' => 8, 'september' => 9, 'october' => 10, 'november' => 11, 'december' => 12,
                'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4, 'jun' => 6, 'jul' => 7, 'aug' => 8, 'sep' => 9,
                'oct' => 10, 'nov' => 11, 'dec' => 12,
            ];

            $searchlower = strtolower($searchterm);

            // Check if search is a month name.
            foreach ($months as $monthname => $monthnumber) {
                if (strpos($searchlower, $monthname) !== false) {
                    $startofmonth = strtotime("1 $monthname");
                    $endofmonth = strtotime("+1 month", $startofmonth) - 1;

                    // If year is also specified (e.g., "January 2023").
                    if (preg_match('/\b(19|20)\d{2}\b/', $searchterm, $matches)) {
                        $year = $matches[0];
                        $startofmonth = strtotime("1 $monthname $year");
                        $endofmonth = strtotime("+1 month", $startofmonth) - 1;
                    }

                    $searchconditions[] = "(n.timecreated BETWEEN :startdate AND :enddate)";
                    $params['startdate'] = $startofmonth;
                    $params['enddate'] = $endofmonth;
                    break;
                }
            }

            // Check if search is a year (4 digits).
            if (preg_match('/^(19|20)\d{2}$/', $searchterm)) {
                $year = $searchterm;
                $startofyear = strtotime("January 1, $year");
                $endofyear = strtotime("December 31, $year 23:59:59");

                $searchconditions[] = "(n.timecreated BETWEEN :startyear AND :endyear)";
                $params['startyear'] = $startofyear;
                $params['endyear'] = $endofyear;
            }

            // Check if search is in MM/YYYY format.
            if (preg_match('/^(0?[1-9]|1[0-2])\/((19|20)\d{2})$/', $searchterm, $matches)) {
                $month = $matches[1];
                $year = $matches[2];

                $startofmonth = strtotime("$year-$month-01");
                $endofmonth = strtotime("+1 month", $startofmonth) - 1;

                $searchconditions[] = "(n.timecreated BETWEEN :startmonthyear AND :endmonthyear)";
                $params['startmonthyear'] = $startofmonth;
                $params['endmonthyear'] = $endofmonth;
            }
        }

        if (!empty($this->filter)) {
            // Filter by section.
            if ($this->filter === 'section' && $this->sectionid) {
                $searchconditions[] = 'n.pagetype = :pagetype AND n.pageurl LIKE :pageurl';
                $params['pagetype'] = 'course-view-section-' . get_course($this->courseid)->format;
                $params['pageurl'] = '%id=' . $this->sectionid . '%';
            } else if ($this->filter === 'activity' && $this->activity) {
                $searchconditions[] = 'n.coursemodule = :cmid';
                $params['cmid'] = $this->activity;
            }
        }

        // Build the SQL query.
        $sql = "SELECT n.*, cm.id as cmid, m.name as modulename
                FROM {ltool_note_data} n
                LEFT JOIN {course_modules} cm ON cm.id = n.coursemodule
                LEFT JOIN {modules} m ON m.id = cm.module";

        // Only add the section join if we're searching.
        if (!empty($this->search)) {
            $sql .= " LEFT JOIN {course_sections} cs ON cs.id = cm.section";
        }

        $sql .= " WHERE n.course = :courseid
                AND n.userid = :userid";

        if (!empty($searchconditions)) {
            $sql .= " AND (" . implode(" OR ", $searchconditions) . ")";
        }

        if ($this->print) {
            // Only show notes that are not hidden.
            $sql .= " AND n.printstatus = 0";
        }

        $sql .= " ORDER BY n.timecreated DESC";
        $notes = $DB->get_records_sql($sql, $params);

        $course = get_course($this->courseid);

        // Process notes for template.
        $notedata = [];
        foreach ($notes as $note) {
            $editstr = ($note->timemodified != null) ?
                ' (' . get_string('edited', 'local_learningtools') . ' ' . $this->get_relative_time($note->timemodified) . ')' : '';
            $list = [
                'id' => $note->id,
                'content' => $note->note,
                'timecreated' => userdate($note->timecreated),
                'editurl' => new \moodle_url('/local/learningtools/ltool/note/editlist.php', ['edit' => $note->id,
                    'sesskey' => sesskey(), 'view' => $this->courseid]),
                'deleteurl' => new \moodle_url('/local/learningtools/ltool/note/deletelist.php', ['delete' => $note->id,
                    'sesskey' => sesskey(), 'view' => $this->courseid]),
                'time' => $this->get_relative_time($note->timecreated) . $editstr,
                'hideurl' => new \moodle_url('/local/learningtools/ltool/note/view.php', ['id' => $this->courseid,
                    'action' => 'hide', 'noteid' => $note->id]),
                'showurl' => new \moodle_url('/local/learningtools/ltool/note/view.php', ['id' => $this->courseid,
                    'action' => 'show', 'noteid' => $note->id]),
                'printstatus' => $note->printstatus ? true : false,
            ];

            if (!empty($note->cmid)) {
                $module = new stdclass;
                $module->coursemodule = $note->cmid;
                $module->courseid = $note->course;
                $list['name'] = local_learningtools_get_module_name($module);
                $cmid = $note->cmid;
                $url = new \moodle_url('/mod/' . $note->modulename . '/view.php', ['id' => $cmid]);
                $list['contexturl'] = $url->out(false);
                $list['contextname'] = get_string('module:', 'local_learningtools') . get_string('modulename', $note->modulename);
            } else {
                $courseformat = $course->format;
                if ($note->pagetype == 'course-view-section-' . $courseformat) {
                    $sectionurl = new \moodle_url($note->pageurl);
                    $sectionid = $sectionurl->get_param('id');
                    $section = $DB->get_record('course_sections', ['course' => $this->courseid, 'id' => $sectionid]);
                    $sectionname = $section->name;
                    if (!$sectionname) {
                        $sectionname = ($section->section == 0) ? get_string('general') :
                            get_string('section', 'local_learningtools') . ' ' . $section->section;
                    } else {
                        $sectionname = $section->name;
                    }
                    $list['contexturl'] = $note->pageurl;
                    $list['contextname'] = get_string('section:', 'local_learningtools')  . $sectionname;
                    $list['name'] = $sectionname;
                } else {
                    $list['contexturl'] = new \moodle_url('/course/view.php', ['id' => $this->courseid]);
                    $list['contextname'] = get_string('course:', 'local_learningtools')  . format_string($course->fullname);
                }
            }
            $notedata[] = $list;
        }

        return [
            'notes' => $notedata,
            'courseurl' => new \moodle_url('/course/view.php', ['id' => $this->courseid]),
            'hasnotes' => !empty($notedata),
            'search' => $this->search,
            'hassearch' => !empty($this->search),
            'courseid' => $this->courseid,
            'hasprint' => $this->print,
        ];
    }

    /**
     * Returns a human-readable relative time string (e.g., "2 minutes ago")
     *
     * @param int $timestamp The timestamp to format
     * @return string Formatted relative time
     */
    private function get_relative_time($timestamp) {
        $diff = time() - $timestamp;

        if ($diff < 60) {
            // Seconds.
            $count = $diff;
            $stringid = ($count == 1) ? 'secondago' : 'secondsago';
            return get_string($stringid, 'local_learningtools', $count);
        } else if ($diff < 3600) {
            // Minutes.
            $count = floor($diff / 60);
            $stringid = ($count == 1) ? 'minuteago' : 'minutesago';
            return get_string($stringid, 'local_learningtools', $count);
        } else if ($diff < 86400) {
            // Hours.
            $count = floor($diff / 3600);
            $stringid = ($count == 1) ? 'hourago' : 'hoursago';
            return get_string($stringid, 'local_learningtools', $count);
        } else if ($diff < 604800) {
            // Days.
            $count = floor($diff / 86400);
            $stringid = ($count == 1) ? 'dayago' : 'daysago';
            return get_string($stringid, 'local_learningtools', $count);
        } else if ($diff < 2592000) {
            // Weeks.
            $count = floor($diff / 604800);
            $stringid = ($count == 1) ? 'weekago' : 'weeksago';
            return get_string($stringid, 'local_learningtools', $count);
        } else if ($diff < 31536000) {
            // Months.
            $count = floor($diff / 2592000);
            $stringid = ($count == 1) ? 'monthago' : 'monthsago';
            return get_string($stringid, 'local_learningtools', $count);
        } else {
            // Years.
            $count = floor($diff / 31536000);
            $stringid = ($count == 1) ? 'yearago' : 'yearsago';
            return get_string($stringid, 'local_learningtools', $count);
        }
    }
}
