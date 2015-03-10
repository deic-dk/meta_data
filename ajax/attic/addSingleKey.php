<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$tagid = $_POST['tagName'];
$key   = $_POST['keyName'];

$ctags->newKey($tagid,$key);
$keyid=$ctags->searchKey($tagid,$key);

echo json_encode($keyid);
