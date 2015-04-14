<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$fileID   = $_POST['fileid'];
$tagID	  = $_POST['tagid'];
$result = $ctags->loadFileKeys($fileID,$tagID);

OCP\JSON::success(array('data' => $result));
