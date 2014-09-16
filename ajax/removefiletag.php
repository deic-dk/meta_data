<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$tagid = $_POST['tag'];
$fileid = $_POST['file'];

$ctags->removeFileTag($tagid,$fileid);


