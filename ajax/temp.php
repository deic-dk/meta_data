<?php

function build_sorter_desc($key) {
		return function ($a, $b) use ($key) {
				return strnatcmp($b[$key], $a[$key]);
		};
}

function build_sorter_asc($key) {
		return function ($a, $b) use ($key) {
				return strnatcmp($a[$key], $b[$key]);
		};
}


OCP\JSON::checkLoggedIn();
$key = isset( $_GET['sortValue'] ) ? $_GET['sortValue'] : 'color';
$dir = isset( $_GET['direction'] ) ? $_GET['direction'] : 'desc';
$fileId = isset( $_GET['file_id'] ) ? $_GET['file_id'] : null;

$allTags = \OCA\Meta_data\Helper::searchTag('%',\OCP\User::getUser());

// If $fileId is set, we exclude tags og the given file_id
if($fileId){
	$fileTags = \OCA\meta_data\helper::getFileTags($fileId);
	$tags = array();
	foreach($allTags as $index => $tag){
		$addTag = true;
		foreach($fileTags as $fileTagIndex){
			if($fileTagIndex==$tag['tagid']){
				$addTag = false;
				break;
			}
		}
		if($addTag){
			$tags[$index] = $tag;
		}
	}
}
else{
	$tags = $allTags;
}

$total = 0; 
if(isset( $_GET['fileCount'] )){
	foreach($tags as $index => $tag){
			$result = \OCA\meta_data\helper::getTaggedFiles($tag['tagid'], "");
			if($result){
				$tags[$index]['size'] = count($result);
			}
			else {
				$tags[$index]['size']=0;
			}
	}
}

if($dir=='desc'){
	usort($tags,build_sorter_desc($key)); 
}
else {
	usort($tags,build_sorter_asc($key)); 
}

if($tags != null){
		OCP\JSON::success(array('tags' => $tags));
}





