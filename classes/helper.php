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

namespace local_learningtools;

/**
 * Helper for the Learning Tools drawer launcher.
 *
 * @package   local_learningtools
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {
    /**
     * Render the Learning Tools drawer.
     *
     * Shows the Notes tool expanded (lazy loaded by JS) and every other usable tool as a button,
     * reusing each tool's existing render_template() output.
     *
     * @return string The drawer HTML.
     */
    public static function render_drawer(): string {
        global $OUTPUT, $PAGE;

        $tools = \local_learningtools_get_drawer_tools();
        // The note editor only belongs in the drawer when the note tool is enabled and permitted.
        $shownotes = isset($tools['note']);
        $toolbuttons = '';
        foreach ($tools as $shortname => $toolobj) {
            // Notes are shown expanded in their own region, not as a button.
            if ($shortname === 'note') {
                continue;
            }
            if (method_exists($toolobj, 'render_template')) {
                $toolbuttons .= $toolobj->render_template();
            }
        }

        return $OUTPUT->render_from_template('local_learningtools/drawer', [
            'contextid' => $PAGE->context->id,
            'toolbuttons' => $toolbuttons,
            'hastools' => $toolbuttons !== '',
            'shownotes' => $shownotes,
            'autosave' => (bool) get_config('local_learningtools', 'autosavenotes'),
        ]);
    }
}
