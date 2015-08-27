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
$dir = isset( $_GET['direction'] ) ? $_GET['direction'] : '';
$fileId = isset( $_GET['fileId'] ) ? $_GET['fileId'] : null;

$allTags = \OCA\Meta_data\Tags::searchTags('%',\OCP\User::getUser());

// If $fileId is set, we exclude tags of the given file id
$ii = 0;
if($fileId){
	$fileTags = \OCA\meta_data\Tags::getFileTags($fileId);
	$tags = array();
	foreach($allTags as $i => $tag){
		$addTag = true;
		foreach($fileTags as $fileTagIndex){
			if($fileTagIndex==$tag['id']){
				$addTag = false;
				break;
			}
		}
		if($addTag){
			$tags[$ii] = $tag;
			++$ii;
		}
	}
}
else{
	$tags = $allTags;
}

foreach($tags as $i => $tag){
	if(isset( $_GET['fileCount'] )){
		$total = 0; 
		$result = \OCA\meta_data\Tags::getTaggedFiles($tag['id'], "");
		if($result){
			$tags[$i]['size'] = count($result);
		}
		else {
			$tags[$i]['size'] = 0;
		}
	}
	if(isset( $_GET['display'] )){
		$userDisplayTags = OCA\meta_data\Tags::getUserDisplayTags();
		$tags[$i]['display'] = 0;
		foreach($userDisplayTags as $displayTag){
			if($displayTag==$tag['id']){
				$tags[$i]['display'] = 1;
				break;
			}
		}
	}
}

if($dir){
	if($dir=='desc'){
		usort($tags,build_sorter_desc($key));
	}
	else{
		usort($tags,build_sorter_asc($key));
	}
}

if($tags != null){
	OCP\JSON::success(array('tags' => $tags));
}

