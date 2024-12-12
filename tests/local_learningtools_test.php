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
 * Learning tools lib test cases defined.
 *
 * @package   local_learningtools
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_learningtools;

/**
 * local learning tools main primary plugin phpunit test cases defined.
 */
class local_learningtools_test extends \advanced_testcase {

    /**
     * Summary of course
     * @var object
     */
    public $course;

    /**
     * Summary of mod
     * @var object
     */
    public $mod;

    /**
     * Summary of generator
     * @var object
     */
    public $generator;

    /**
     * Set the admin user as User.
     *
     * @return void
     */
    public function setup(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        $this->generator = $this->getDataGenerator();
    }

    /**
     * Create course and module for test.
     *
     * @return void
     */
    public function create_course(): void {
        $this->course = $this->generator->create_course();
        $this->mod = $this->generator->create_module('page', [
            'course' => $this->course->id,
            'title' => 'Test page',
            'content' => 'Test page content'
        ]);
    }

    /**
     * Test the get_module id function returns the module id from module context id.
     * @covers ::local_learningtools_get_moduleid
     * @return void
     */
    public function test_local_learningtools_get_moduleid(): void {
        global $DB;
        // Create modules.
        $this->create_course();
        // Fetch module context id.
        $modulecontext = \context_module::instance($this->mod->cmid);
        $moduleid = local_learningtools_get_moduleid($modulecontext->id, $modulecontext->contextlevel);
        $this->assertEquals($this->mod->cmid, $moduleid);
        // Test local_learningtools_get_module_name.
        $params = (object) ['coursemodule' => $this->mod->cmid, 'courseid' => $this->course->id];
        $modulename = local_learningtools_get_module_name($params);
        $this->assertEquals($this->mod->name, $modulename);
        $modulename = local_learningtools_get_module_name($params, true);
        $this->assertEquals('page', $modulename);
    }

    /**
     * Test function local_learningtools_get_courses_name which returns list of course names from list of course ids.
     * @covers ::local_learningtools_get_courses_name
     * @return void
     */
    public function test_local_learningtools_get_courses_name(): void {
        // Create multiple courses.
        foreach (range(0, 3) as $count) {
            $course  = $this->generator->create_course();
            $courseids[] = $course->id;
            $courses[$course->id] = $course;
        }

        $courseinfo = local_learningtools_get_courses_name($courseids);
        foreach ($courseinfo as $courseid => $coursename) {
            $this->assertEquals($courses[$courseid]->fullname, $coursename);
        }
    }

    /**
     * Test the function get_students_incouser which returns the list of enroled users in course.
     * @covers ::local_learningtools_get_students_incourse
     * @return void
     */
    public function test_local_learningtools_get_students_incourse(): void {
        $this->create_course();
        $teacher = $this->generator->create_and_enrol(
            $this->course,
            'editingteacher',
            ['username' => 'teacher1', 'teacher1@test.com']
        );
        $firstuser = $this->generator->create_and_enrol($this->course, 'student', ['username' => 'studnet1', 'student1@test.com']);
        $this->generator->create_and_enrol($this->course, 'student', ['username' => 'studnet2', 'student2@test.com']);
        $this->generator->create_and_enrol($this->course, 'student', ['username' => 'studnet3', 'student3@test.com']);
        // Test count of enrolled students.
        $students = local_learningtools_get_students_incourse($this->course->id);
        $this->assertCount(3, $students);
    }
}
