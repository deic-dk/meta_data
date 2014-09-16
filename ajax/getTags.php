<?php

\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();
$tagData = $ctags->getAllTags(OC_User::getUser(),"%");
$jsonTagData = json_encode($tagData);
echo $jsonTagData;
