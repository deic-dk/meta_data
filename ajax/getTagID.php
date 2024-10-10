<?php

OCP\JSON::checkLoggedIn();
$tagname = isset( $_GET['tagname'] ) ? $_GET['tagname'] : '';

$tagID = \OCA\Meta_data\Tags::getTagID($tagname, \OCP\User::getUser());

if(!empty($tagID)){
	OCP\JSON::encodedPrint($tagID);
}
else{
	OCP\JSON::error(array('message' => 'Could not find tag ID', 'tagname' => $tagname));
}

