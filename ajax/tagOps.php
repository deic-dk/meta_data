<?php

OCP\JSON::callCheck();
OCP\JSON::checkAppEnabled('meta_data');
OCP\User::checkLoggedIn();

$owner = OC_User::getUser();

$ctags = new \OCA\meta_data\tags();

switch($_POST['tagOp']) {
    case 'new': {
        $result = $ctags->newTag($_POST['tagName'], $owner, $_POST['tagState'], $_POST['tagColor']);
        break;
    }
    
    case 'new_key': {
        $result = $ctags->newKey($_POST['tagId'], $_POST['keyName']);
        break;
    }

    case 'rename_tag': {
        $result = $ctags->alterTag($_POST['tagId'], $_POST['tagName'], $owner, $_POST['tagState']);
        break;
    }
    
    case 'rename_key': {
        $result = $ctags->alterKey($_POST['tagId'],$_POST['keyId'], $_POST['newName'], $owner);
        break;
    }

    case 'delete': {
        $result = $ctags->deleteTag($_POST['tagId'], $owner);
        break;
    }

    case 'delete_key': {
        $result = $ctags->removeFileKey($_POST['tagId'],"%", $_POST['keyId']);
        $result = $ctags->deleteKeys($_POST['tagId'], $_POST['keyId']);
        break;
    }

    case 'update_file_key': {
        $result = $ctags->updateFileKeys($_POST['fileId'], $_POST['tagId'], $_POST['keyId'], $_POST['newName']);
        break;
    }
}

// Publish the op result
if($result === FALSE) {
    $result = array(
        'result' => 'KO, result is false',
    );
} else {
    $result = array(
        'result' => 'OK',
        'tagid' => $result[0]['tagid'],
        'tagname' => $result[0]['descr'],
        'keyid' => $result[0]['keyid'],
        'result' => $result,
    );
}

echo json_encode($result);
