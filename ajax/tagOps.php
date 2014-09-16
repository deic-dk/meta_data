<?php

OCP\JSON::callCheck();
OCP\JSON::checkAppEnabled('meta_data');
OCP\User::checkLoggedIn();

// Check for a valid operation to perform
$tagOp = filter_input(INPUT_POST, 'tagOp', FILTER_SANITIZE_STRING);
$validOps = array('new', 'rename', 'delete');

if(array_search($tagOp, $validOps) === FALSE) {
    $result = array(
        'result' => 'KO, options invalid',
        'title' => '',
        'key' => '',
        'class' => ''
    );
    
    die(json_encode($result));
}

// Check for valid input parameters
$descr = filter_input(INPUT_POST, 'tagName', FILTER_SANITIZE_STRING);
$oldtagname = filter_input(INPUT_POST, 'oldtagname', FILTER_SANITIZE_STRING);
$owner = OC_User::getUser();
$public = filter_input(INPUT_POST, 'tagState', FILTER_SANITIZE_STRING);
$keylist = filter_input(INPUT_POST, 'keyList', FILTER_SANITIZE_STRING);


if($descr === FALSE) {
    $result = array(
        'result' => 'KO, descr is false',
        'title' => '',
        'key' => '',
        'class' => ''
    );
    
    die(json_encode($result));
}

// Switch between possible operations
$ctags = new \OCA\meta_data\tags();

switch($tagOp) {
case 'new': {
        $result = $ctags->newTag($descr, $owner, $public,$keylist);
        break;
    }
    
    case 'rename': {
        $result = $ctags->alterTag($oldtagname, $descr, $owner, $public, $keylist);
        break;
    }
    
    case 'delete': {
        $result = $ctags->deleteTag($descr, $owner);
        break;
    }
}

// Publish the op result
if($result === FALSE) {
    $result = array(
        'result' => 'KO, result is false',
        'title' => '',
        'key' => '',
        'class' => ''
    );
} else {
    $result = array(
        'result' => 'OK',
        'title' => $descr,
        'key' => $result,
        'class' => 'global'
    );
}

echo json_encode($result);
