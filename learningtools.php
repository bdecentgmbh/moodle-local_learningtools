<?php
require_once(__DIR__ . '/../../config.php');
abstract class learningtools {

    abstract public function get_tool_name(); 

    abstract public function get_tool_icon();

    public function get_tool_info() {
        global $OUTPUT;

        $data = [];
        $data['name'] = $this->get_tool_name();
        $data['icon'] = $this->get_tool_icon();
        return $data;
    }
}