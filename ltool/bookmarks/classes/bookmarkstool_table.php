<?php

namespace ltool_bookmarks;
use table_sql;
use stdclass;
use moodle_url;
use html_writer;
use context_system;
use context_course;
use context_user;
require_once($CFG->dirroot.'/local/learningtools/lib.php');
require_once($CFG->libdir. '/tablelib.php');

class bookmarkstool_table extends \table_sql {

	public function __construct($tableid, $courseid, $child, $teacher, $urlparams) {
       
        parent::__construct($tableid);

        $this->courseid = $courseid;
        $this->child = $child;
        $this->urlparams = $urlparams;
        $this->teacher = $teacher;

        $columns = array();
        $headers = array();


        $columns[]= 'icon';
        $headers[]= get_string('bookicon','local_learningtools');

        $columns[] = 'course';
        $headers[] = get_string('bookmarks', 'local_learningtools');

        $columns[]= 'bookmarkinfo';
        $headers[]= get_string('bookmarkinfo','local_learningtools');

        $columns[]= 'timecreated';
        $headers[]= get_string('time','local_learningtools');

        $columns[]= 'delete';
        $headers[]= get_string('delete','local_learningtools');

        $columns[]= 'view';
        $headers[]= get_string('view','local_learningtools');

        $this->define_columns($columns);
        $this->define_headers($headers); 
        $this->no_sorting('icon');
        $this->no_sorting('bookmarkinfo');
        $this->no_sorting('delete');
        $this->no_sorting('view');
        ///$this->define_baseurl();    
    }

    public function col_icon(stdclass $row) {

    	return '<i class="fa fa-bookmark">';
    }

    public function col_course(stdclass $row) {

    	$data = check_instanceof_block($row);
        return $this->get_instance_bookmark($data);
    }

    public function get_instance_bookmark($data) {
        $bookmark = '';
        if ($data->instance == 'course') {
            $bookmark = get_course_name($data->courseid);
        } else if ($data->instance == 'user') {
            $bookmark = 'user';
        } else if ($data->instance == 'mod') {
            $bookmark = get_module_name($data);
        } else if ($data->instance == 'system') {
             $bookmark = 'system';
        } else if ($data->instance == 'block') {
             $bookmark = 'block';
        }
        return $bookmark;
    }


    public function col_bookmarkinfo(stdclass $row) {
    	$data = check_instanceof_block($row);
    	return $this->get_instance_bookmarkinfo($data);
    }	

    public function get_instance_bookmarkinfo($data) {
         $bookmarkinfo = '';
        if ($data->instance == 'course') {
            $bookmarkinfo = get_course_categoryname($data->courseid);
        } else if ($data->instance == 'user') {
            $bookmarkinfo = 'user';
        } else if ($data->instance == 'mod') {
            $bookmarkinfo = get_module_coursesection($data);
        } else if ($data->instance == 'system') {
             $bookmarkinfo = 'system';
        } else if ($data->instance == 'block') {
             $bookmarkinfo = 'block';
        }
        return $bookmarkinfo;
    }

    public function col_timecreated(stdclass $row) {
    	return userdate($row->timecreated, '%B %d, %Y, %I:%M %p', '', false);
    }	

    public function col_delete(stdclass $row) {
		global $OUTPUT, $USER;
        $context = context_system::instance();
        $particularuser = null;

        if($this->courseid || $this->child) {
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
                $returnurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php');
                $deleteparams = array('delete'=>$row->id, 'sesskey'=>sesskey(), 'courseid' => $this->courseid);
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
            	$returnurl = new moodle_url('/local/learningtools/ltool/bookmarks/ltbookmarks_list.php');
                $deleteparams = array('delete'=>$row->id, 'sesskey'=>sesskey());
                $deleteparams = array_merge($deleteparams, $this->urlparams);
            	$url = new moodle_url($returnurl, $deleteparams);;
                $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
                $buttonhtml = implode(' ', $buttons);
                return $buttonhtml;
            }
        }

        return '';
    }


    public function col_view(stdclass $row) {
        global $OUTPUT;
        $data = check_instanceof_block($row);
    	$viewurl = '';
         if ($data->instance == 'course') {
            $courseurl = new moodle_url('/course/view.php',array('id' => $data->courseid));
            $viewurl = $OUTPUT->single_button($courseurl, get_string('viewcourse', 'local_learningtools'),'get');
        } else if ($data->instance == 'user') {
            $viewurl = 'user';
        } else if ($data->instance == 'mod') {
           $modname = get_module_name($data, true);
           $modurl = new moodle_url("/mod/$modname/view.php", array('id' => $data->coursemodule));
           $viewurl = $OUTPUT->single_button($modurl, get_string('viewactivity', 'local_learningtools'),'get');
        } else if ($data->instance == 'system') {
             $viewurl = 'system';
        } else if ($data->instance == 'block') {
             $viewurl = 'block';
        }
        return $viewurl;
    }

}