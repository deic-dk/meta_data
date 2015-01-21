<?php

OCP\JSON::checkLoggedIn();

$fileid = isset( $_GET['fileid'] ) ? $_GET['fileid'] : '';


try {
		$tags = \OCA\Meta_data\Helper::getFileTags($fileid);
} catch (Exception $e) {
		header("HTTP/1.0 404 Not Found");
		exit();
}

$data=implode(',',$tags);


OCP\JSON::success(array('data' => $data));
