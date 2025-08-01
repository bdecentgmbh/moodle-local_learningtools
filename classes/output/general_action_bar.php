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

namespace local_learningtools\output;

use moodle_url;
use core\output\select_menu;
use core\output\comboboxsearch;

/**
 * Renderable class for the general action bar in the gradebook pages.
 *
 * This class is responsible for rendering the general navigation select menu in the gradebook pages.
 *
 * @package    local_learningtools
 * @copyright  2021 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class general_action_bar {

    /** @var moodle_url $activeurl The URL that should be set as active in the URL selector element. */
    protected $activeurl;

    /**
     * The type of the current gradebook page (report, settings, import, export, scales, outcomes, letters).
     *
     * @var string $activetype
     */
    protected $activetype;

    /** @var string $activeplugin The plugin of the current gradebook page (grader, fullview, ...). */
    protected $activeplugin;

    /**
     * Summary of context
     * @var \context
     */
    protected $context;

    /**
     * Course id.
     * @var int
     */
    protected $courseid;

    /**
     * Section id.
     * @var int
     */
    protected $sectionid;

    /**
     * Activity
     * @var int
     */
    protected $activity;

    /**
     * The class constructor.
     *
     * @param \context $context The context object.
     * @param moodle_url $activeurl The URL that should be set as active in the URL selector element.
     * @param string $activetype The type of the current gradebook page (report, settings, import, export, scales,
     *                           outcomes, letters).
     * @param string $activeplugin The plugin of the current gradebook page (grader, fullview, ...).
     * @param int $courseid Course ID.
     * @param int $sectionid Section ID.
     * @param int $activity Activity ID.
     */
    public function __construct(\context $context, moodle_url $activeurl, string $activetype, string $activeplugin, int $courseid,
        int $sectionid, int $activity) {
        $this->activeurl = $activeurl;
        $this->activetype = $activetype;
        $this->activeplugin = $activeplugin;
        $this->context = $context;
        $this->courseid = $courseid;
        $this->sectionid = $sectionid;
        $this->activity = $activity;
    }

    /**
     * Export the data for the mustache template.
     *
     * @param \renderer_base $output renderer to be used to render the action bar elements.
     * @return array
     */
    public function export_for_template(\renderer_base $output): array {
        global $USER, $DB;
        $selectmenu = $this->get_action_selector();

        if (is_null($selectmenu)) {
            return [];
        }

        $collapsemenudirection = right_to_left() ? 'dropdown-menu-left' : 'dropdown-menu-right';

        $collapse = new comboboxsearch(
                true,
                get_string('collapsedcolumns', 'gradereport_grader', 0),
                null,
                'collapse-columns',
                'collapsecolumn',
                'collapsecolumndropdown p-3 flex-column ' . $collapsemenudirection,
                null,
                true,
                get_string('aria:dropdowncolumns', 'gradereport_grader'),
                'collapsedcolumns'
            );

        $course = get_course($this->courseid);

        $sections = $this->get_sections();
        $activities = $this->get_activities();

        $viewpageurl = new moodle_url('/local/learningtools/ltool/note/view.php', ['id' => $this->courseid]);

        $collapsedcolumns = [
            'classes' => 'd-none',
            'content' => $collapse->export_for_template($output),
            'viewpageurl' => $viewpageurl->out(false),
            'sections' => !empty($sections) ? $sections : [],
            'activities' => !empty($activities) ? $activities : [],
        ];

        return [
            'generalnavselector' => $selectmenu->export_for_template($output),
            'collapsedcolumns' => $collapsedcolumns,
        ];
    }

    /**
     * Returns the template for the action bar.
     *
     * @return string
     */
    public function get_template(): string {
        return 'local_learningtools/tertiary_navigation';
    }

    /**
     * Returns the URL selector object.
     *
     * @return \select_menu|null The URL select object.
     */
    private function get_action_selector(): ?select_menu {
        if ($this->context->contextlevel !== CONTEXT_COURSE) {
            return null;
        }

        $courseid = $this->context->instanceid;
        $menus = [];

        // Get all available learning tool subplugins.
        $subplugins = local_learningtools_get_subplugins();

        // Add each subplugin to the menus.
        foreach ($subplugins as $shortname => $toolobj) {

            // Check if user has capability to view this tool.
            if ($shortname == 'note') {
                // Get tool-specific URL if the tool has a navigation method.
                if (method_exists($toolobj, 'get_navigation_url')) {
                    $toolurl = $toolobj->get_navigation_url($courseid);
                } else {
                    // Default URL pattern for tools.
                    $toolurl = new moodle_url('/local/learningtools/ltool/'.$shortname.'/view.php',
                        ['id' => $courseid]);
                }

                // Get tool name.
                $toolname = get_string('toolname', 'ltool_'.$shortname);

                // Add to menus.
                $menus[$toolurl->out(false)] = $toolname;
            }
        }

        $selectmenu = new select_menu('learningtoolsselect', $menus, $this->activeurl->out(false));
        $selectmenu->set_label(get_string('learningtools', 'local_learningtools'), ['class' => 'sr-only']);

        return $selectmenu;
    }

    /**
     * Get the related sections.
     *
     * @return array
     */
    public function get_sections() {
        global $USER, $DB;
        $course = get_course($this->courseid);
        $data = [];

        $sql = "SELECT n.*, cm.id as cmid, m.name as modulename
                FROM {ltool_note_data} n
                LEFT JOIN {course_modules} cm ON cm.id = n.coursemodule
                LEFT JOIN {modules} m ON m.id = cm.module
                WHERE n.course = :courseid AND n.pagetype = :pagetype
                AND n.userid = :userid
                ORDER BY n.timecreated DESC";
        $params = [
            'courseid' => $this->courseid,
            'userid' => $USER->id,
            'pagetype' => 'course-view-section-' . $course->format,
        ];

        $notes = $DB->get_records_sql($sql, $params);

        foreach ($notes as $note) {
            $sectionurl = new \moodle_url($note->pageurl);
            $sectionid = $sectionurl->get_param('id');

            if (!isset($data[$sectionid])) {
                $section = $DB->get_record('course_sections', ['course' => $this->courseid, 'id' => $sectionid]);
                if ($section) {
                    $sectionname = $section->name ?: (
                        $section->section == 0
                            ? get_string('general')
                            : get_string('section', 'local_learningtools') . ' ' . $section->section
                    );

                    $filterurl = new \moodle_url('/local/learningtools/ltool/note/view.php', [
                        'id' => $this->courseid,
                        'sectionid' => $section->id,
                        'filter' => 'section',
                    ]);

                    $data[$sectionid] = [
                        'sectionname' => $sectionname,
                        'sectionid' => $section->id,
                        'filterurl' => $filterurl->out(false),
                        'selected' => ($section->id == $this->sectionid) ? "selected" : "",
                    ];
                }
            }
        }

        return array_values($data); // Re-index to return a clean list.
    }

    /**
     * Gets the activity selected record.
     * @return array course activity selector records.
     */
    public function get_activities() {
        global $DB, $USER;

        $sql = "SELECT cm.id as coursemodule, cm.course, m.name as modname
            FROM {course_modules} cm
            JOIN {modules} m ON m.id = cm.module
            WHERE cm.deletioninprogress = 0 AND cm.course = :course";

        $params = [
            'course' => $this->courseid,
            'userid' => $USER->id,
        ];

        if (!empty($this->sectionid)) {
            $sql .= " AND cm.section = :sectionid";
            $params['sectionid'] = $this->sectionid;
        }

        $sql .= " GROUP BY cm.id, cm.course";

        $records = $DB->get_records_sql($sql, $params);
        $data = [];

        if (!empty($records)) {
            foreach ($records as $record) {
                $record->courseid = $record->course;
                $list['mod'] = local_learningtools_get_module_name($record);
                $filterurl = new moodle_url('/local/learningtools/ltool/note/view.php', ['id' => $this->courseid,
                        'activity' => $record->coursemodule,
                        'filter' => 'activity']);
                $list['filterurl'] = $filterurl->out(false);
                if ($record->coursemodule == $this->activity) {
                    $list['selected'] = "selected";
                } else {
                    $list['selected'] = "";
                }
                $data[] = $list;
            }

        }
        return $data;
    }
}
