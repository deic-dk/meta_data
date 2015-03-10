<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');


$tagid =  isset( $_GET['tagid'] ) ? $_GET['tagid'] : '';

$result = \OCA\meta_data\tags::deleteTag($tagid, \OCP\User::getUser());

//$ctags->removeFileKey($tagid,$fileid);


