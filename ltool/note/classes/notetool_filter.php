<?php
namespace ltool_note;
use moodle_url;
use html_writer;
use stdclass;
use context_system;
use context_course;
use context_user;

require_once($CFG->dirroot. '/local/learningtools/lib.php');

class notetool_filter {

    public function __construct($userid) {
		$this->userid = $userid;
	}

	public function get_user_sql($courseid, $childid) {
		global $DB;

		$usersql = '';
		$userparams = [];

		if ($courseid) {
			if (!$childid) {
				$students = get_students_incourse($courseid);
				if (!empty($students)) {
					list($studentsql, $userparams) = $DB->get_in_or_equal($students, SQL_PARAMS_NAMED);
					$usersql .= 'user '. $studentsql;
				}
			} else {
				$usersql = 'user = :userid';
				$userparams = ['userid' => $childid];
			}
		} elseif($childid) {
			$usersql = 'user = :userid';
			$userparams = ['userid' => $childid];
		} else {
			$usersql = 'user = :userid';
			$userparams = ['userid' => $this->userid];
		}
		return ['sql' => $usersql, 'params' => $userparams];
	}

    public function get_course_selector($selectcourse, $courseid, $childid) {

		global $DB, $OUTPUT;
		$template = [];
		$courses = [];
		$usercondition = $this->get_user_sql($courseid, $childid);
		$usersql = $usercondition['sql'];
		$userparams = $usercondition['params'];
		$records = $DB->get_records_sql("SELECT * FROM {learningtools_note} WHERE $usersql", $userparams);
		if (!empty($records)) {
			foreach($records as $record) {
				$instanceblock = check_note_instanceof_block($record);
				if (isset($instanceblock->instance) && $instanceblock->instance == 'course' || $instanceblock->instance == 'mod') {
					$courses[] = $instanceblock->courseid;
				}
			}
		}

		$courses = get_courses_name(array_unique($courses), '/local/learningtools/ltool/note/ltnote_list.php',$selectcourse, $childid, $courseid);
		
		$template['courses'] = $courses;
		$template['coursefilter'] = true;
		$urlparams = [];
		if ($childid) {
			$urlparams['userid'] = $childid;
		}
		if ($courseid) {
			$urlparams['courseid'] = $courseid;
		}
		
		$pageurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $urlparams);
		$template['pageurl'] = $pageurl->out(false);
		return $template;
	}

	
	public function get_activity_selector($course, $activity, $courseid, $childid, $teacher) {
		global $DB, $OUTPUT;

		$usercondition = $this->get_user_sql($courseid, $childid);
		$usersql = $usercondition['sql'];
		$userparams = $usercondition['params'];
		$sql = "SELECT * FROM {learningtools_note} 
			WHERE $usersql AND course = :course AND coursemodule != 0 GROUP BY coursemodule";
		$params = [
			'course' => $course,
		];

		$params = array_merge($params, $userparams);
		$records = $DB->get_records_sql($sql, $params);
		$data = [];

		if (!empty($records)) {
			foreach ($records as $record) {
				$record->courseid = $record->course;
				$list['mod'] = get_module_name($record);
				$urlparams = [
					'selectcourse' => $record->course, 
					'activity' => $record->coursemodule
				];
				if ($childid) {
					$urlparams['userid'] = $childid;
				}
				if ($courseid) {
					$urlparams['courseid'] = $courseid;
				}
				if ($teacher) {
					$urlparams['teacher'] = $teacher;
				}
				
				$filterurl = new moodle_url("/local/learningtools/ltool/note/ltnote_list.php", $urlparams);
				$list['filterurl'] = $filterurl->out(false);
				if ($record->coursemodule == $activity) {
					$list['selected'] = "selected";
				} else {
					$list['selected'] = "";
				}
				$data[] = $list;
			}
			
		}
		return $data;
	}

	public function get_sort_instance($activity, $course, $sort, $childid, $courseid, $teacher) {

		global $OUTPUT;
		$template = [];
		$coursesortparams = ['sort' => 'course'];
		$datesortparams = ['sort' => 'date'];
		$activitysortparams = array('sort' => 'activity');

		$dateselect = '';
		$courseselect = '';
		$activityselect = '';

		if ($sort == 'date') {
			$dateselect = "selected";
		} else if ($sort =='course') {
			$courseselect = "selected";
		} else if ($sort == 'activity') {
			$activityselect = "selected";
		}

		if ($course) {
			$coursesortparams['selectcourse'] = $course;
			$datesortparams['selectcourse'] = $course;
			$activitysortparams['selectcourse'] = $course;
		}

		if ($activity) {
			$coursesortparams['activity'] = $activity;
			$datesortparams['activity'] = $activity;
		}
		
		if ($childid) {
			$coursesortparams['userid'] = $childid;
			$datesortparams['userid'] = $childid;
			$activitysortparams['userid'] = $childid;
		}
		if ($courseid) {
			$coursesortparams['courseid'] = $courseid;
			$datesortparams['courseid'] = $courseid;
			$activitysortparams['courseid'] = $courseid;
		}

		if ($teacher) {
			$datesortparams['teacher'] = $teacher;
			$coursesortparams['teacher'] = $teacher;
			$activitysortparams['teacher'] = $teacher;
		}

		$coursesort = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $coursesortparams);
		$datesort = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $datesortparams);
		$activitysort = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $activitysortparams);

		if ($course) {
			$template['activitysort'] = $activitysort->out(false);
		} else {
			$template['coursesort'] = $coursesort->out(false);
		}
		$template['dateselect'] = $dateselect;
		$template['courseselect'] = $courseselect;
		$template['activityselect'] = $activityselect;
		$template['datesort'] = $datesort->out(false);
		$template['sortfilter'] = true;

		return $template;
	}

	public function get_note_records($course, $sort, $activity, $courseid, $childid) {
		global $DB;

		$coursesql = '';
		$sortsql = '';
		$usersql = '';

		if ($courseid) {
			if (!$childid) {
				$students = get_students_incourse($courseid);
				if (!empty($students)) {
					list($studentsql, $params) = $DB->get_in_or_equal($students, SQL_PARAMS_NAMED);
					$usersql .= 'user '. $studentsql;
				}
			} elseif($childid) {
				$usersql = 'user = :userid';
				$params = ['userid' => $childid];
			}
		} elseif($childid) {
			$usersql = 'user = :userid';
			$params = ['userid' => $childid];
		} else {
			$usersql = 'user = :userid';
			$params = ['userid' => $this->userid];
		}	

		if ($sort == 'date') {
			$select = 'FLOOR(timecreated/86400) AS date,';
			$sortsql .= "GROUP BY FLOOR(timecreated/86400) ORDER BY timecreated";
		} else if ($sort == 'course') {
			$select = 'course,';
			$sortsql .= "AND course != 1  GROUP BY course ORDER BY course";
		} else if ($sort == 'activity') {
			$select = 'coursemodule,';
			$sortsql .= " AND coursemodule != 0 GROUP BY coursemodule ORDER BY coursemodule";
		}


		if ($course) {
			$coursesql .= 'AND course = :course';
			$params['course'] = $course;
			if ($activity) {
				$coursesql .= 'AND coursemodule = :activity';
				$params['activity'] = $activity;
			}
		}

		$sql = "SELECT  $select GROUP_CONCAT(id) AS notesgroup  
			FROM {learningtools_note} WHERE $usersql $coursesql 
			$sortsql";	
		$records = $DB->get_records_sql($sql, $params);
		return $records;
	}

	public function get_main_body($course, $sort, $activity, $courseid, $childid, $teacher, $urlparams) {	
	
		global $OUTPUT, $DB;
	
		$template = [];
		$reports = [];
		$records = $this->get_note_records($course, $sort, $activity, $courseid, $childid);
		$data = [];
		if (!empty($records)) {
			foreach($records as $record) {
				$res = [];
				if (isset($record->notesgroup)) {
					list($dbsql, $dbparam) = $DB->get_in_or_equal(explode(",", $record->notesgroup), SQL_PARAMS_NAMED);
					$list = $DB->get_records_sql("SELECT * FROM {learningtools_note} WHERE id $dbsql", $dbparam);
					$res['notes'] = $list;
					if ($sort == 'date') {
						$head = userdate(($record->date * 86400), '%B, %dth %Y','',false);
					} else if($sort == 'course') {
						$head = get_course_name($record->course);
					} else if ($sort == 'activity') {
						$module = new stdclass;
						$module->coursemodule = $record->coursemodule;
						$module->courseid = $course;
						$head = get_module_name($module);
					}
					$res['title'] = $head;
				}
				$reports[] = $res;
			}
		}

		$cnt = 1;
		if (!empty($reports)) {
			foreach ($reports as $report) {
				$info = [];
				if (isset($report['notes'])) {
					$notes = $this->get_speater_plug($report['notes'], $courseid, $childid, $teacher, $urlparams);
					$info['notes'] =  $notes;
					$info['title'] = isset($report['title']) ? $report['title'] : '';
					$info['range'] = $cnt.'-block';
					$info['active'] = ($cnt == 1) ? true: false;
				}
				$cnt++;
				$data[] = $info;

			}
		}

		$template['records'] = $data;
		$template['ltnotes'] = true;

		if (!$activity) {
			$template['sortfilter'] = $this->get_sort_instance($activity, $course, $sort, $childid, $courseid, $teacher);
		}
		if (!$courseid) {
			$template['coursefilter'] = $this->get_course_selector($course, $courseid, $childid);
		}

		$template['enableactivityfilter'] = !empty($course) ? true : false;
		

		if ($course) {
			$coursefilterparams =  array('selectcourse' => $course);
			if ($childid) {
				$coursefilterparams['userid'] = $childid;
			}
			if ($teacher) {
				$coursefilterparams['teacher'] = $teacher;
			}
			if ($courseid) {
				$coursefilterparams['courseid'] = $courseid;
			}

			$coursefilterurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php', $coursefilterparams);
			$template['coursefilterurl'] = $coursefilterurl->out(false);
			$template['activityfilter'] = $this->get_activity_selector($course, $activity, $courseid, $childid, $teacher);
		}
		
		return $OUTPUT->render_from_template('ltool_note/ltnote', $template);

	}

	public function get_speater_plug($records, $courseid, $childid, $teacher, $urlparams) {
		global $USER;

		$report = [];
		$context = context_system::instance();
		if (!empty($records)) {
			foreach ($records as $record) {
				$data = check_instanceof_block($record);
				$list['id'] = $record->id;
				$list['instance'] = $this->get_instance_note($data);
				$list['base'] = $this->get_title_note($data);
				$list['note'] = !empty($record->note) ? $record->note : '';
				$list['time'] = userdate($record->timecreated, '%B %d, %Y, %I:%M', '', false);
				$list['viewurl'] = $this->get_view_url($record);

				if (!empty($courseid) && !$childid) {
					$coursecontext = context_course::instance($courseid);
					if (has_capability('ltool/note:managenote', $coursecontext)) {
						$list['delete'] = $this->delete_note_info($record, $urlparams, $courseid);
						$list['edit'] = $this->edit_note_info($record, $urlparams, $courseid);
					}

				} else if ($childid) {
					if ($teacher) {
						$coursecontext = context_course::instance($courseid);
						if (has_capability('ltool/note:managenote', $coursecontext)) {
							$list['delete'] = $this->delete_note_info($record, $urlparams, $courseid);
							$list['edit'] = $this->edit_note_info($record, $urlparams, $courseid);
						}

					} else {
						$usercontext = context_user::instance($childid);
						if (has_capability('ltool/note:managenote', $usercontext, $USER->id)) {
							$list['delete'] = $this->delete_note_info($record, $urlparams);
							$list['edit'] = $this->edit_note_info($record, $urlparams);
						}
					}

				} else {
					if (has_capability('ltool/note:manageownnote', $context)) {
						$list['delete'] = $this->delete_note_info($record, $urlparams);
						$list['edit'] = $this->edit_note_info($record, $urlparams);
					}
				}

				$report[] = $list;
			}
		}
		return $report;
	}

	public function edit_note_info($row, $urlparams, $courseid = 0) {
		global $OUTPUT;
		$stredit = get_string('edit'); 
    	$buttons = [];
    	$returnurl = new moodle_url('/local/learningtools/ltool/note/ltnote_editlist.php');
		$optionyes = array('edit'=>$row->id, 'sesskey'=>sesskey());
		if ($courseid) {
			$optionyes['courseid'] = $courseid;
		}
		$optionyes = array_merge($optionyes, $urlparams);
    	$url = new moodle_url($returnurl,$optionyes);
        $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/edit', $stredit));
        $buttonhtml = implode(' ', $buttons);
        return $buttonhtml;

	}

	public function delete_note_info($row, $urlparams, $courseid = 0) {
		global $OUTPUT;

    	$strdelete = get_string('delete');
    	$buttons = [];
    	$returnurl = new moodle_url('/local/learningtools/ltool/note/ltnote_list.php');
		$optionyes = array('delete'=>$row->id, 'sesskey'=>sesskey());
		if ($courseid) {
			$optionyes['courseid'] = $courseid;
		}
		$optionyes = array_merge($optionyes, $urlparams);
    	$url = new moodle_url($returnurl, $optionyes);
        $buttons[] = html_writer::link($url, $OUTPUT->pix_icon('t/delete', $strdelete));
        $buttonhtml = implode(' ', $buttons);
        return $buttonhtml;
    }

	public function get_view_url($row) {
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

	public function get_instance_note($data) {
        $instance = '';
        if ($data->instance == 'course') {
            $instance = get_course_name($data->courseid);
        } else if ($data->instance == 'user') {
            $instance = 'user';
        } else if ($data->instance == 'mod') {
			$instance = get_course_name($data->courseid);
        } else if ($data->instance == 'system') {
             $instance = 'system';
        } else if ($data->instance == 'block') {
             $instance = 'block';
        }
        return $instance;
    }

	public function get_title_note($data) {

		$title = '';
		if ($data->instance == 'course') {
            $title = get_course_name($data->courseid);
        } else if ($data->instance == 'user') {
            $title = 'user';
        } else if ($data->instance == 'mod') {
			$title = get_module_coursesection($data, 'note');
        } else if ($data->instance == 'system') {
             $title = 'system';
        } else if ($data->instance == 'block') {
             $title = 'block';
        }
        return $title;
	}


} 