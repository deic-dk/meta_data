<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$keyData = \OCA\meta_data\Tags::searchKey($_POST['tagid'],"%");

OCP\JSON::success(array('data' => $keyData));
