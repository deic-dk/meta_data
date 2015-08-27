<?php

OCP\JSON::callCheck();
OCP\JSON::checkAppEnabled('meta_data');
OCP\User::checkLoggedIn();

$owner = OC_User::getUser();

switch($_POST['tagOp']) {
    case 'new': {
        $result = \OCA\meta_data\Tags::newTag($_POST['tagName'], $owner, $_POST['tagVisibleState'], $_POST['tagColor'], $_POST['tagPublicState']);
        break;
    }
    case 'new_key': {
        $result = \OCA\meta_data\Tags::newKey($_POST['tagId'], $_POST['keyName']);
        break;
    }
    case 'rename_key': {
        $result = \OCA\meta_data\Tags::alterKey($_POST['tagId'],$_POST['keyId'], $_POST['newName'], $owner);
        break;
    }

    case 'delete': {
        $result = \OCA\meta_data\Tags::deleteTag($_POST['tagId'], $owner);
        break;
    }
    case 'delete_key': {
        $result = \OCA\meta_data\Tags::removeFileKey($_POST['tagId'],"%", $_POST['keyId']);
        $result = \OCA\meta_data\Tags::deleteKeys($_POST['tagId'], $_POST['keyId']);
        break;
    }
    case 'update_file_key': {
        $result = \OCA\meta_data\Tags::updateFileKeys($_POST['fileId'], $_POST['tagId'], $_POST['keyId'], $_POST['value']);
        break;
    }
}

// Publish the op result
if(empty($result === false) {
    $result = array(
        'result' => 'KO, result is false',
    );
}
else {
    $result = array(
        'result' => 'OK',
        'tagid' => $result[0]['tagid'],
        'tagname' => $result[0]['name'],
        'keyid' => isset($result[0]['keyid'])?$result[0]['keyid']:null,
        'result' => $result,
    );
}

echo json_encode($result);
