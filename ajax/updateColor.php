<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkAppEnabled('meta_data');
\OCP\User::checkLoggedIn();

$ctags = new \OCA\meta_data\tags();

$params = explode(" ", $_POST['color']);
$color=$params[2];

$ctags->updateColor($_POST['tagid'],$color);
echo json_encode(array($color, $_POST['tagid']));
