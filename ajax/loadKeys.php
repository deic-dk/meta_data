<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');


$ctags = new \OCA\meta_data\tags();
$keyData = $ctags->searchKey($_POST['tagid'],"%");


OCP\JSON::success(array('data' => $keyData));
