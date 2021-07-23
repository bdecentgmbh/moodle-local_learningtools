<?php
namespace ltool_note;

require_once($CFG->libdir.'/externallib.php');

class external extends \external_api {

    public static function save_usernote_parameters() {

        return new \external_function_parameters(
            array(
                'contextid' => new \external_value(PARAM_INT, 'The context id for the course'),
                'formdata' => new \external_value(PARAM_RAW, 'The data from the create group form, encoded as a json array')
            )
        );
    }

    public static function save_usernote($contextid, $formdata) {
        global $CFG;
		require_once($CFG->dirroot.'/local/learningtools/ltool/note/lib.php');
		// Parse serialize form data.
		parse_str($formdata, $data);
        return user_save_notes($contextid, $data);
        
    }

    public static function save_usernote_returns() {
        return new \external_value(PARAM_INT, 'Save note status');
    }
}