<?php

OCP\JSON::checkLoggedIn();

$id = isset( $_GET['id'] ) ? $_GET['id'] : '';
$color = isset( $_GET['color'] ) ? $_GET['color'] : '';
$name = isset( $_GET['name'] ) ? $_GET['name'] : '';
$visible = isset( $_GET['visible'] ) ? $_GET['visible'] : '';
$public = isset( $_GET['public'] ) ? $_GET['public'] : '';

$result = \OCA\Meta_data\Tags::updateTag($id, $name, $color, $visible, $public);

if($result != null){
	OCP\JSON::success();
}
else{
	OCP\JSON::error(array('result'=>$result));
}