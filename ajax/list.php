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
$sortDirection = isset( $_GET['sortdirection'] ) ? ($_GET['sortdirection'] === 'desc') : false;
$data = array();

// make filelist
try {
	$files = \OCA\Meta_data\Helper::getTaggedFiles($tagid, \OCP\User::getUser(), $sortAttribute, $sortDirection);	
} catch (Exception $e) {
	header("HTTP/1.0 404 Not Found");
	exit();
}

$encodedDir = \OCP\Util::encodePath($dir);
//$data['permissions'] = 0;
$data['directory'] = $dir;
$data['files'] = \OCA\Meta_data\Helper::formatFileInfos($files);




foreach ($data['files'] as $nindex => $file){
    $tagids = \OCA\Meta_data\Helper::getFileTags($file['id']);
    $tags=[];
    foreach ($tagids as $index => $tag){
	  $result = \OCA\Meta_data\Helper::getTagName($tag);
	  $tags[$index]['tagid']=$tag;
	  $tags[$index]['descr']=$result[0];
	  $tags[$index]['color']=$result[1];

	}


	if($tags != null){
	  usort($tags,build_sorter_desc("color")); 
	  $data['files'][$nindex]['tags']=$tags;
	}
  }


foreach ($files as $file){
		$tags = \OCA\Meta_data\Helper::getFileTags($file['fileid']);
		$file['tags']=implode(',',$tags);
}

OCP\JSON::success(array('data' => $data));
