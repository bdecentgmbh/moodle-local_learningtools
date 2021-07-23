<?php 


$functions = array(
    'ltool_note_save_usernote' => array(
        'classname'   => 'ltool_note\external',
        'methodname'  => 'save_usernote',
        'description' => 'Save the user note',
        'type'        => 'write',
        'ajax'          => true,
        'loginrequired' => true,
    ),
);