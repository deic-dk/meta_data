<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

// Read post data
$fileID   = $_POST['fileid'];
$tagID    = $_POST['tagid'];
$keyID    = $_POST['keyid'];
$value    = $_POST['val'];

// if $value and $keyid is set, this call needs to update keys 
if(!empty($keyID)){
  $result = \OCA\meta_data\Tags::updateFileKeys($fileID,$tagID,$keyID,$value);
}
else {
// otherwise it should update tags
  $result = \OCA\meta_data\Tags::updateFileTags($tagID,OC_User::getUser(),$fileID);
}

// In either case, return $result
echo json_encode($result);
