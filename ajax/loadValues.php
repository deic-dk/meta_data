<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$fileID = $_POST['fileid'];
$fileOwner = $_POST['fileowner'];
$tagID = $_POST['tagid'];
$result = \OCA\meta_data\Tags::loadFileKeys($fileID, $tagID, $fileOwner);

OCP\JSON::success(array('data' => $result));
