<?php
function build_sorter_desc($key) {
  return function ($a, $b) use ($key) {
	return strnatcmp($b[$key], $a[$key]);
  };
}

OCP\JSON::checkLoggedIn();

// Load the files
$tagid = isset( $_GET['tagid'] ) ? $_GET['tagid'] : '';
$dir = isset( $_GET['dir'] ) ? $_GET['dir'] : '';
$sortAttribute = isset( $_GET['sort'] ) ? $_GET['sort'] : 'name';
$sortDescending = isset( $_GET['sortdirection'] ) ? ($_GET['sortdirection'] === 'desc') : false;
$data = array();

$user = \OCP\USER::getUser();
// make filelist
try {
	$files = \OCA\Meta_data\Tags::getTaggedFiles($tagid, $user, $sortAttribute, $sortDescending);	
}
catch (Exception $e) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$encodedDir = \OCP\Util::encodePath($dir);
//$data['permissions'] = 0;
$data['directory'] = $dir;
$data['files'] = \OCA\Meta_data\Tags::formatFileInfos($files);

foreach ($data['files'] as $i => $file){
	//$tagids = \OCA\Meta_data\Tags::getFileTags($file['id']);
	$tagids = $file['tags'];
	$alltags = \OCA\Meta_data\Tags::getTags($tagids);
	$tags = [];
	foreach($alltags as $tag){
		if($tag['owner']!=$user && $tag['public']==0){
			continue;
		}
		$tags[] = $tag;
	}
	
	if(!empty($tags)){
		usort($tags,build_sorter_desc('color')); 
		$data['files'][$i]['tags'] = $tags;
	}
}

OCP\JSON::success(array('data' => $data));
