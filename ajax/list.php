<?php

OCP\JSON::checkLoggedIn();

// Load the files
$tagid = isset( $_GET['tagid'] ) ? $_GET['tagid'] : '';
$dir = isset( $_GET['dir'] ) ? $_GET['dir'] : '';
$sortAttribute = isset( $_GET['sort'] ) ? $_GET['sort'] : 'name';
$sortDirection = isset( $_GET['sortdirection'] ) ? ($_GET['sortdirection'] === 'desc') : false;
$data = array();

// make filelist
try {
	$files = \OCA\Meta_data\Helper::getTaggedFiles($tagid, \OCP\User::getUser(), $sortAttribute, $sortDirection);	
} catch (Exception $e) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

foreach ($files as $file){
		$tags = \OCA\Meta_data\Helper::getFileTags($file['fileid']);
		$file['tags']=implode(',',$tags);
}

$encodedDir = \OCP\Util::encodePath($dir);
//$data['permissions'] = 0;
$data['directory'] = $dir;
$data['files'] = \OCA\Meta_data\Helper::formatFileInfos($files);

OCP\JSON::success(array('data' => $data));
