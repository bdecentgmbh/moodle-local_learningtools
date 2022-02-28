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
 * ltool plugin "Learning Tools Resume course" - library file.
 *
 * @package   ltool_focus
 * @copyright bdecent GmbH 2021
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot. '/local/learningtools/lib.php');

/**
 * Learning tools focus template function.
 * @param array $templatecontent template content
 * @return string display html content.
 */
function ltool_focus_render_template($templatecontent) {
    global $OUTPUT;
    return $OUTPUT->render_from_template('ltool_focus/focus', $templatecontent);
}

/**
 * Load focus configuration.
 * @return void
 */
function ltool_focus_load_focus_config() {
    global $PAGE;
    $disableclass = 'disable-focus d-none';
    $focuscsshtml = '<link id="ltool-focuscss" rel="stylesheet" type="text/css" href="">';
    $PAGE->add_header_action($focuscsshtml);
    $focusdisable = html_writer::start_tag('div', array('id' => 'disable-focusmode', 'class' => $disableclass));
    $focusdisable .= html_writer::start_tag('button', array('class' => 'btn btn-primary'));
    $focusdisable .= html_writer::tag('i', '', array('class' => 'fa fa-close'));
    $focusdisable .= html_writer::end_tag('button');
    $focusdisable .= html_writer::end_tag('div');
    $PAGE->add_header_action($focusdisable);
}

/**
 * Get the focus tool implemented css file url.
 * @return string file url.
 */
function ltool_focus_get_focus_css_url() {
    $url = '';
    $fs = get_file_storage();
    $fileinfo = ltool_focus_get_focus_css_fileinfo();
    $filename = $fileinfo['filename'];
    if ($files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
    $fileinfo['itemid'])) {
        foreach ($files as $file) {
            $filename = $file->get_filename();
        }
    }
    // TODO: FILE EXISTS CHECK.
    if ($fs->file_exists($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
    $fileinfo['itemid'], $fileinfo['filepath'], $filename)) {
        $url = moodle_url::make_pluginfile_url($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
            $fileinfo['itemid'], $fileinfo['filepath'], $filename, false);
    }
    return $url;
}

/**
 * Implemented the focus tool js files.
 *
 * @return void
 */
function ltool_focus_load_js_config() {
    global $PAGE, $SESSION;
    if (isset($SESSION->focusmode)) {
        $focusmode = $SESSION->focusmode;
        $PAGE->requires->js_call_amd('ltool_focus/focus', 'init', array('focusmode' => $focusmode));
    }
}

/**
 * Import the focus styles into the file.
 *
 * @return stored_file
 */
function ltool_focus_create_focus_temp_cssfile() {
    $fs = get_file_storage();
    $fileinfo = ltool_focus_get_focus_css_fileinfo();
    $focusmodecss = get_config('ltool_focus', 'focusmodecss');
    if ($files = $fs->get_area_files($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'],
        $fileinfo['itemid'])) {
        foreach ($files as $file) {
            if ($file) {
                $file->delete();
            }
        }
    }
    return $fs->create_file_from_string($fileinfo, $focusmodecss);
}

/**
 * Get focus tool file info
 *
 * @return array file info
 */
function ltool_focus_get_focus_css_fileinfo() {
    $fileinfo = array(
        'contextid' => context_system::instance()->id,
        'component' => 'ltool_focus',
        'filearea' => 'focuscss',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => 'focus_'.time().'.css'
    );
    return $fileinfo;
}

/**
 * Serves the focus tool style file settings.
 *
 * @param   stdClass $course course object
 * @param   stdClass $cm course module object
 * @param   stdClass $context context object
 * @param   string $filearea file area
 * @param   array $args extra arguments
 * @param   bool $forcedownload whether or not force download
 * @param   array $options additional options affecting the file serving
 * @return  bool false|void
 */
function ltool_focus_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = []) {

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    if ($filearea !== 'focuscss') {
        return false;
    }
    // Extract the filename / filepath from the $args array.
    $filename = array_pop($args);

    // Retrieve the file from the Files API.
    $itemid = 0;
    $fs = get_file_storage();
    $file = $fs->get_file($context->id, 'ltool_focus', $filearea, $itemid, '/', $filename);
    if (!$file) {
        return false; // The file does not exist.
    }

    send_stored_file($file, null, 0, $forcedownload, $options);
}

/**
 * Load the user page notes form
 * @param array $args page arguments
 * @return string Display the html note editor form.
 */
function ltool_focus_output_fragment_load_focus_mode($args) {
    global $SESSION;
    $status = $args['status'];
    $SESSION->focusmode = $status;
    return $status;
}

/**
 * Implemented the focus mode.
 *
 * @return void
 */
function ltool_focus_focusmode_actions() {
    global $SESSION;
    if (!isset($SESSION->focusmode)) {
        $SESSION->focusmode = 0;
    }
    ltool_focus_load_focus_config();
}

