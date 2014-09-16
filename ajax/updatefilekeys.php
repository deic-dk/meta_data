<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$fileID   = $_POST['fileid'];
$tagID    = $_POST['tagid'];
$keyID    = $_POST['keyid'];
$value    = $_POST['val'];


$result = $ctags->updateFileKeys($fileID,$tagID,$keyID,$value);


echo json_encode($result);
