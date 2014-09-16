<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$tagname = $_POST['term'];

$result = $ctags->searchTag($tagname,OC_User::getUser(),"%");


echo json_encode($result);
