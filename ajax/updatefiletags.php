<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$tagname = $_POST['tag'];
$fileID   = $_POST['fileid'];

$result = $ctags->updateFileTags($tagname,OC_User::getUser(),$fileID);


echo json_encode($result);
