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
 * External functions for the report learning tool.
 *
 * @package   ltool_report
 * @copyright 2026, bdecent gmbh bdecent.de
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace ltool_report;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External API for submitting an issue report.
 */
class external extends \external_api {
    /**
     * Parameters for submit_report.
     *
     * @return \external_function_parameters
     */
    public static function submit_report_parameters() {
        return new \external_function_parameters(
            [
                'contextid' => new \external_value(PARAM_INT, 'The page context id'),
                'formdata' => new \external_value(PARAM_RAW, 'JSON encoded page identity, issue type and description'),
            ]
        );
    }

    /**
     * Submit an issue report and notify the resolved recipients.
     *
     * @param int $contextid page context id
     * @param string $formdata JSON page identity, issue type and description
     * @return array|string result, or '' when the request is rejected
     */
    public static function submit_report($contextid, $formdata) {
        global $CFG, $USER;
        require_login();
        require_once($CFG->dirroot . '/local/learningtools/ltool/report/lib.php');
        $context = \context_system::instance();
        require_capability('ltool/report:createreport', $context);
        $params = self::validate_parameters(
            self::submit_report_parameters(),
            ['contextid' => $contextid, 'formdata' => $formdata]
        );
        $data = (array) json_decode($params['formdata']);
        if ($USER->id == $data['user']) {
            return ltool_report_user_submit_report($params['contextid'], $data);
        }
        return '';
    }

    /**
     * Return structure for submit_report.
     *
     * @return \external_single_structure
     */
    public static function submit_report_returns() {
        return new \external_single_structure(
            [
                'success' => new \external_value(PARAM_BOOL, 'Whether the report was submitted'),
                'message' => new \external_value(PARAM_TEXT, 'Notification message'),
                'notificationtype' => new \external_value(PARAM_TEXT, 'Notification type'),
            ]
        );
    }
}
