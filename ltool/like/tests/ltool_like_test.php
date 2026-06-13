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
 * Like ltool test cases.
 *
 * @package   ltool_like
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_like;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/learningtools/ltool/like/lib.php');

/**
 * Tests for the like learning tool.
 *
 * @runTestsInSeparateProcesses
 */
final class ltool_like_test extends \advanced_testcase {
    /**
     * The test page.
     * @var \moodle_page
     */
    public $page;

    /**
     * The course context.
     * @var \context
     */
    public $context;

    /**
     * The course.
     * @var \stdClass
     */
    public $course;

    /**
     * Create a course page and log in as admin.
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $this->course = $this->getDataGenerator()->create_course();
        $this->context = \context_course::instance($this->course->id);
        $page = new \moodle_page();
        $page->set_context($this->context);
        $page->set_course($this->course);
        $page->set_pagelayout('standard');
        $page->set_pagetype('course-view');
        $page->set_title('Course: Course 1');
        $page->set_url(new \moodle_url('/course/view.php', ['id' => $this->course->id]));
        $this->page = $page;
    }

    /**
     * Build the page identity data for a given reaction.
     *
     * @param string $reaction
     * @param int|null $userid
     * @return array
     */
    protected function get_like_info(string $reaction, ?int $userid = null): array {
        global $USER;
        return [
            'user' => $userid ?? $USER->id,
            'course' => $this->course->id,
            'pagetype' => $this->page->pagetype,
            'pagetitle' => $this->page->title,
            'coursemodule' => 0,
            'contextlevel' => $this->context->contextlevel,
            'contextid' => $this->context->id,
            'pageurl' => $this->page->url->out(false),
            'reaction' => $reaction,
        ];
    }

    /**
     * Adding a reaction inserts a row and fires the created event.
     *
     * @covers ::ltool_like_user_save_like
     */
    public function test_save_insert(): void {
        global $USER;
        $sink = $this->redirectEvents();
        $result = ltool_like_user_save_like($this->context->id, $this->get_like_info('like'));
        $events = $this->filter_like_events($sink);
        $event = reset($events);

        $this->assertEquals('like', $result['reaction']);
        $this->assertEquals('like', ltool_like_get_user_reaction($this->context->id, $this->page->url->out(false), $USER->id));
        $this->assertInstanceOf('\ltool_like\event\ltlike_created', $event);
        $this->assertEquals($this->context, $event->get_context());
    }

    /**
     * Choosing a different reaction updates the single row.
     *
     * @covers ::ltool_like_user_save_like
     */
    public function test_switch_update(): void {
        global $USER, $DB;
        ltool_like_user_save_like($this->context->id, $this->get_like_info('like'));
        $sink = $this->redirectEvents();
        $result = ltool_like_user_save_like($this->context->id, $this->get_like_info('dislike'));
        $events = $this->filter_like_events($sink);
        $event = reset($events);

        $this->assertEquals('dislike', $result['reaction']);
        $this->assertEquals('dislike', ltool_like_get_user_reaction($this->context->id, $this->page->url->out(false), $USER->id));
        $this->assertEquals(1, $DB->count_records('ltool_like_data', ['userid' => $USER->id]));
        $this->assertInstanceOf('\ltool_like\event\ltlike_updated', $event);
    }

    /**
     * Clicking the active reaction removes it.
     *
     * @covers ::ltool_like_user_save_like
     */
    public function test_same_delete(): void {
        global $USER, $DB;
        ltool_like_user_save_like($this->context->id, $this->get_like_info('like'));
        $sink = $this->redirectEvents();
        $result = ltool_like_user_save_like($this->context->id, $this->get_like_info('like'));
        $events = $this->filter_like_events($sink);
        $event = reset($events);

        $this->assertEquals('', $result['reaction']);
        $this->assertNull(ltool_like_get_user_reaction($this->context->id, $this->page->url->out(false), $USER->id));
        $this->assertEquals(0, $DB->count_records('ltool_like_data', ['userid' => $USER->id]));
        $this->assertInstanceOf('\ltool_like\event\ltlike_deleted', $event);
    }

    /**
     * Counts aggregate across users.
     *
     * @covers ::ltool_like_get_counts
     */
    public function test_counts_aggregation(): void {
        $student1 = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $student2 = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $student3 = $this->getDataGenerator()->create_and_enrol($this->course, 'student');

        foreach (['like' => $student1, 'superlike' => $student2, 'dislike' => $student3] as $reaction => $user) {
            $this->setUser($user);
            ltool_like_user_save_like($this->context->id, $this->get_like_info($reaction, $user->id));
        }

        $counts = ltool_like_get_counts($this->context->id, $this->page->url->out(false));
        $this->assertEquals(1, $counts['like']);
        $this->assertEquals(1, $counts['superlike']);
        $this->assertEquals(1, $counts['dislike']);
    }

    /**
     * The external save returns counts only for users who may see them.
     *
     * @covers \ltool_like\external::save_userlike
     */
    public function test_external_save_counts_capability_gated(): void {
        global $PAGE;
        $PAGE->set_context($this->context);

        // A manager sees the counts.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $this->getDataGenerator()->create_role(['archetype' => 'manager']);
        role_assign($managerrole, $manager->id, $this->context->id);
        $this->setUser($manager);
        $result = \ltool_like\external::save_userlike(
            $this->context->id,
            json_encode($this->get_like_info('like', $manager->id))
        );
        $this->assertTrue($result['cancount']);
        $this->assertArrayHasKey('counts', $result);

        // A student does not.
        $student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $this->setUser($student);
        $result = \ltool_like\external::save_userlike(
            $this->context->id,
            json_encode($this->get_like_info('like', $student->id))
        );
        $this->assertFalse($result['cancount']);
        $this->assertArrayNotHasKey('counts', $result);
    }

    /**
     * The manager fragment lists users; a student is denied.
     *
     * @covers ::ltool_like_output_fragment_get_like_users
     */
    public function test_fragment_users_capability(): void {
        $student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        $this->setUser($student);
        ltool_like_user_save_like($this->context->id, $this->get_like_info('like', $student->id));

        // Manager can render the list.
        $manager = $this->getDataGenerator()->create_user();
        $managerrole = $this->getDataGenerator()->create_role(['archetype' => 'manager']);
        role_assign($managerrole, $manager->id, $this->context->id);
        $this->setUser($manager);
        $html = ltool_like_output_fragment_get_like_users(
            ['contextid' => $this->context->id, 'pageurl' => $this->page->url->out(false)]
        );
        $this->assertStringContainsString(fullname($student), $html);

        // Student is denied.
        $this->setUser($student);
        $this->expectException(\required_capability_exception::class);
        ltool_like_output_fragment_get_like_users(
            ['contextid' => $this->context->id, 'pageurl' => $this->page->url->out(false)]
        );
    }

    /**
     * Keep only the ltool_like events from a sink.
     *
     * @param \core\event\manager_redirect $sink
     * @return array
     */
    protected function filter_like_events($sink): array {
        $events = [];
        foreach ($sink->get_events() as $event) {
            if (strpos(get_class($event), 'ltool_like\\event\\') !== false) {
                $events[] = $event;
            }
        }
        return $events;
    }
}
