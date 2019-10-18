<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');


$fileid = isset( $_GET['fileid'] ) ? $_GET['fileid'] : '';
$tagid =  isset( $_GET['tagid'] ) ? $_GET['tagid'] : '';

$result = \OCA\meta_data\Tags::removeFileTag($tagid,$fileid);

OCP\JSON::success($result);
