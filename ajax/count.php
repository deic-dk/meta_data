<?php

OCP\JSON::checkLoggedIn();

$tagid =  isset( $_GET['tagid'] ) ? $_GET['tagid'] : '';

$result = \OCA\meta_data\helper::getTaggedFiles($tagid, "");

if($result != null){
	OCP\JSON::success(count($result));
} else {
	OCP\JSON::success(0);
}

