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
 * List of course participants table.
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_learningtools\table;

use moodle_url;

/**
 * List of course participants table.
 */
class courseparticipants extends \core_user\table\participants {

    /**
     * Fetch completions users list.
     *
     * @param mixed $tableid
     * @return void
     */
    public function __construct($tableid) {
        $expuniqueid = explode('-', $tableid);
        $ltool = (string) end($expuniqueid);
        $this->ltool = $ltool;
        parent::__construct($tableid);
    }

    /**
     * Print the course participants students table.
     *
     * @param  mixed $pagesize
     * @param  mixed $useinitialsbar
     * @param  mixed $downloadhelpbutton
     * @return void
     */
    public function out($pagesize, $useinitialsbar, $downloadhelpbutton = '') {

        // Define the headers and columns.
        $headers = [];
        $columns = [];

        $headers[] = get_string('fullname');
        $columns[] = 'fullname';

        $headers[] = get_string('email');
        $columns[] = 'email';

        $headers[] = get_string('reports');
        $columns[] = 'reports';

        $this->sortable(true, 'lastname');
        $this->no_sorting('reports');
        $this->define_columns($columns);
        $this->define_headers($headers);
        $this->set_attribute('id', 'participants');
        \table_sql::out($pagesize, $useinitialsbar, $downloadhelpbutton);

    }


    /**
     * Generate the fullname column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_fullname($data) {
        global $OUTPUT;

        if ($this->is_downloading()) {
            return fullname($data);
        }
        return $OUTPUT->user_picture($data, array('size' => 35, 'courseid' => $this->course->id, 'includefullname' => true));
    }

    /**
     * Generate the email column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_email($data) {
        global $OUTPUT;

        return $data->email;
    }


    /**
     * Generate the reports column.
     *
     * @param \stdClass $data
     * @return string
     */
    public function col_reports($data) {
        $viewreportbutton = $this->get_viewreport_url($data->id);
        return $viewreportbutton;
    }

    /**
     * Bookmarks instance view url.
     * @param int $userid user id
     * @return string HTML fragment
     */
    public function get_viewreport_url($userid) {
        global $OUTPUT;

        if ($this->ltool == 'bookmarks') {
            $url = '/local/learningtools/ltool/bookmarks/list.php';
        } else if ($this->ltool == 'note') {
            $url = '/local/learningtools/ltool/note/list.php';
        }
        $viewreporturl = new moodle_url($url, array('userid' => $userid, 'courseid' => $this->course->id, 'teacher' => true));
        $viewreportbutton = $OUTPUT->single_button($viewreporturl, get_string('viewreports', 'local_learningtools'), 'get');
        return $viewreportbutton;
    }

    /**
     * Query the database for results to display in the table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        list($twhere, $tparams) = $this->get_sql_where();

        $psearch = new courseparticipants_search($this->course, $this->context, $this->filterset);

        $total = $psearch->get_total_participants_count($twhere, $tparams);

        $this->pagesize($pagesize, $total);

        $sort = $this->get_sql_sort();
        if ($sort) {
            $sort = 'ORDER BY ' . $sort;
        }

        $rawdata = $psearch->get_participants($twhere, $tparams, $sort, $this->get_page_start(), $this->get_page_size());

        $this->rawdata = [];
        foreach ($rawdata as $user) {
            $this->rawdata[$user->id] = $user;
        }

        if ($this->rawdata) {
            $this->allroleassignments = get_users_roles($this->context, array_keys($this->rawdata),
                    true, 'c.contextlevel DESC, r.sortorder ASC');
        } else {
            $this->allroleassignments = [];
        }

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars(true);
        }
    }
}
