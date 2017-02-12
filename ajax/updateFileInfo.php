<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

// Read post data
$fileID   = isset($_POST['fileid'])?$_POST['fileid']:'';
$tagID    = isset($_POST['tagid'])?$_POST['tagid']:'';
$tagName    = isset($_POST['tagname'])?$_POST['tagname']:'';
$keyID    = isset($_POST['keyid'])?$_POST['keyid']:'';
$val      = isset($_POST['val'])?$_POST['val']:'';

// if $val and $keyid is set, this call needs to update keys 
if(!empty($keyID)){
	$result = \OCA\meta_data\Tags::updateFileKeyVal($fileID, $tagID, $keyID, $val);
}
else {
	$userid = OC_User::getUser();
	if(empty($tagID) && !empty($tagName)){
		$tagID = \OCA\meta_data\Tags::getTagID($tagName, $userid);
	}
// otherwise it should update tags
  $result = !empty($tagID) && \OCA\meta_data\Tags::updateFileTag($tagID, $userid, $fileID);
}

if($result && !empty($tagID)){
	// The Zenodo app needs the tag ID.
	echo $tagID;
}
else{
	echo $result;
}
