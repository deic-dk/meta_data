<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

// Read post data
$fileID   = isset($_POST['fileid'])?$_POST['fileid']:'';
$tagID    = isset($_POST['tagid'])?$_POST['tagid']:'';
$keyID    = isset($_POST['keyid'])?$_POST['keyid']:'';
$val      = isset($_POST['val'])?$_POST['val']:'';

// if $val and $keyid is set, this call needs to update keys 
if(!empty($keyID)){
  $result = \OCA\meta_data\Tags::updateFileKeyVal($fileID, $tagID, $keyID, $val);
}
else {
// otherwise it should update tags
  $result = \OCA\meta_data\Tags::updateFileTag($tagID, OC_User::getUser(), $fileID);
}

// In either case, return $result
echo json_encode($result);
