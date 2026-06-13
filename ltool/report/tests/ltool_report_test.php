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
 * Report ltool test cases.
 *
 * @package   ltool_report
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_report;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/learningtools/ltool/report/lib.php');

/**
 * Tests for the report learning tool.
 *
 * @runTestsInSeparateProcesses
 */
final class ltool_report_test extends \advanced_testcase {
    /**
     * The course.
     * @var \stdClass
     */
    public $course;

    /**
     * The course context.
     * @var \context
     */
    public $context;

    /**
     * The url of the page a report is submitted from.
     * @var string
     */
    public $pageurl;

    /**
     * Create a course context and log in as admin.
     *
     * @return void
     */
    public function setUp(): void {
        parent::setUp();
        $this->resetAfterTest(true);
        $this->setAdminUser();
        $this->course = $this->getDataGenerator()->create_course();
        $this->context = \context_course::instance($this->course->id);
        $this->pageurl = (new \moodle_url('/course/view.php', ['id' => $this->course->id]))->out(false);
    }

    /**
     * The id of a core role by shortname.
     *
     * @param string $shortname
     * @return int
     */
    protected function role_id(string $shortname): int {
        global $DB;
        return (int) $DB->get_field('role', 'id', ['shortname' => $shortname], MUST_EXIST);
    }

    /**
     * Build the page identity data for a report.
     *
     * @param string $issuetype
     * @param string $description
     * @param int|null $userid
     * @return array
     */
    protected function get_report_info(string $issuetype, string $description, ?int $userid = null): array {
        global $USER;
        return [
            'user' => $userid ?? $USER->id,
            'course' => $this->course->id,
            'pagetype' => 'course-view',
            'pagetitle' => 'Course 1',
            'coursemodule' => 0,
            'contextlevel' => $this->context->contextlevel,
            'contextid' => $this->context->id,
            'pageurl' => $this->pageurl,
            'issuetype' => $issuetype,
            'description' => $description,
        ];
    }

    /**
     * A configured role with holders in the context resolves to those holders.
     *
     * @covers ::ltool_report_get_recipients
     */
    public function test_recipients_role_holders(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        set_config('rolestechnical', $this->role_id('editingteacher'), 'ltool_report');

        $recipients = ltool_report_get_recipients($this->context, 'technical');
        $support = \core_user::get_support_user();

        $this->assertArrayHasKey($teacher->id, $recipients);
        $this->assertArrayNotHasKey($support->id, $recipients);
    }

    /**
     * No role configured for a type falls back to the support user.
     *
     * @covers ::ltool_report_get_recipients
     */
    public function test_recipients_no_role_fallback_support(): void {
        $recipients = ltool_report_get_recipients($this->context, 'question');
        $support = \core_user::get_support_user();

        $this->assertEquals([$support->id], array_keys($recipients));
    }

    /**
     * A role with no holder in the context also falls back to the support user.
     *
     * @covers ::ltool_report_get_recipients
     */
    public function test_recipients_role_without_holder_fallback(): void {
        // The coursecreator role exists, but nobody holds it in this course.
        set_config('rolesaccessibility', $this->role_id('coursecreator'), 'ltool_report');

        $recipients = ltool_report_get_recipients($this->context, 'accessibility');
        $support = \core_user::get_support_user();

        $this->assertEquals([$support->id], array_keys($recipients));
    }

    /**
     * The "also send to support user" toggle adds the support user on top of the roles.
     *
     * @covers ::ltool_report_get_recipients
     */
    public function test_recipients_also_support(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        set_config('rolestechnical', $this->role_id('editingteacher'), 'ltool_report');
        set_config('alsosupport', 1, 'ltool_report');

        $recipients = ltool_report_get_recipients($this->context, 'technical');
        $support = \core_user::get_support_user();

        $this->assertArrayHasKey($teacher->id, $recipients);
        $this->assertArrayHasKey($support->id, $recipients);
    }

    /**
     * The "specific user" setting adds the chosen user.
     *
     * @covers ::ltool_report_get_recipients
     */
    public function test_recipients_specific_user(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $extra = $this->getDataGenerator()->create_user();
        set_config('rolestechnical', $this->role_id('editingteacher'), 'ltool_report');
        set_config('specificuser', $extra->id, 'ltool_report');

        $recipients = ltool_report_get_recipients($this->context, 'technical');

        $this->assertArrayHasKey($teacher->id, $recipients);
        $this->assertArrayHasKey($extra->id, $recipients);
    }

    /**
     * A user holding two configured roles is only notified once.
     *
     * @covers ::ltool_report_get_recipients
     */
    public function test_recipients_dedupe(): void {
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        role_assign($this->role_id('teacher'), $teacher->id, $this->context->id);
        set_config('rolestechnical', $this->role_id('editingteacher') . ',' . $this->role_id('teacher'), 'ltool_report');

        $recipients = ltool_report_get_recipients($this->context, 'technical');

        $this->assertCount(1, $recipients);
        $this->assertArrayHasKey($teacher->id, $recipients);
    }

    /**
     * Submitting a report inserts a row, fires the event and notifies the recipients.
     *
     * @covers ::ltool_report_user_submit_report
     */
    public function test_submit_inserts_event_and_messages(): void {
        global $DB;
        $teacher = $this->getDataGenerator()->create_and_enrol($this->course, 'editingteacher');
        $student = $this->getDataGenerator()->create_and_enrol($this->course, 'student');
        set_config('rolestechnical', $this->role_id('editingteacher'), 'ltool_report');

        $this->setUser($student);
        $eventsink = $this->redirectEvents();
        $messagesink = $this->redirectMessages();

        $result = ltool_report_user_submit_report(
            $this->context->id,
            $this->get_report_info('technical', 'The video will not play.', $student->id)
        );

        $this->assertTrue($result['success']);
        $this->assertEquals(1, $DB->count_records('ltool_report_data', ['userid' => $student->id, 'issuetype' => 'technical']));

        $events = $this->filter_report_events($eventsink);
        $this->assertInstanceOf('\ltool_report\event\ltreport_submitted', reset($events));

        $messages = $messagesink->get_messages();
        $this->assertCount(1, $messages);
        $this->assertEquals($teacher->id, reset($messages)->useridto);
    }

    /**
     * A disabled issue type is rejected.
     *
     * @covers ::ltool_report_user_submit_report
     */
    public function test_submit_rejects_disabled_type(): void {
        set_config('enabletechnical', 0, 'ltool_report');
        $this->expectException(\moodle_exception::class);
        ltool_report_user_submit_report($this->context->id, $this->get_report_info('technical', 'Broken.'));
    }

    /**
     * An unknown issue type is rejected.
     *
     * @covers ::ltool_report_user_submit_report
     */
    public function test_submit_rejects_invalid_type(): void {
        $this->expectException(\moodle_exception::class);
        ltool_report_user_submit_report($this->context->id, $this->get_report_info('bogus', 'Broken.'));
    }

    /**
     * Only enabled issue types are offered.
     *
     * @covers ::ltool_report_get_enabled_issue_types
     */
    public function test_enabled_issue_types(): void {
        $this->assertEquals(ltool_report_get_issue_types(), ltool_report_get_enabled_issue_types());
        set_config('enablecontent', 0, 'ltool_report');
        $this->assertNotContains('content', ltool_report_get_enabled_issue_types());
        $this->assertContains('technical', ltool_report_get_enabled_issue_types());
    }

    /**
     * Keep only the ltool_report events from a sink.
     *
     * @param \core\event\manager_redirect $sink
     * @return array
     */
    protected function filter_report_events($sink): array {
        $events = [];
        foreach ($sink->get_events() as $event) {
            if (strpos(get_class($event), 'ltool_report\\event\\') !== false) {
                $events[] = $event;
            }
        }
        return $events;
    }
}
