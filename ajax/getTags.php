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
$onlyFileId = isset( $_GET['onlyFileId'] ) ? $_GET['onlyFileId'] : null;
$name = isset( $_GET['name'] ) ? $_GET['name'] : '';

$allTags = \OCA\Meta_data\Tags::searchTags($name.'%',\OCP\User::getUser());

$ii = 0;
// If $onlyFileId is set, we only include tags of the given file ids
if($onlyFileId){
	$onlyFileIds = explode(':', $onlyFileId);
	$tags = array();
	$fileTagsArr = \OCA\meta_data\Tags::getFileTags($onlyFileIds);
	foreach($onlyFileIds as $fileid){
		$fileTags = isset($fileTagsArr[$fileid])?$fileTagsArr[$fileid]:[];
		if(empty($filesTags)){
			$filesTags = $fileTags;
		}
		else{
			$filesTags = array_merge($filesTags, $fileTags);
		}
	}
	\OCP\Util::writeLog('meta_data', 'File tags : '.serialize($filesTags), \OC_Log::WARN);
	foreach($allTags as $i => $tag){
		foreach($filesTags as $fileTag){
			if($fileTag==$tag['id'] && !in_array($tag, $tags)){
				$tags[$ii] = $tag;
				++$ii;
				
			}
		}
	}
}
// If $fileId is set, we exclude tags of the given file ids
elseif($fileId){
	// This is somewhat hacky: if $fileId is of the form a:b:c, it's three ids.
	if($fileId && strpos($fileId, ':')>0){
		$fileIds = explode(':', $fileId);
	}
	else{
		$fileIds = array($fileId);
	}
	$fileTagsArr = \OCA\meta_data\Tags::getFileTags($fileIds);
	foreach($fileIds as $fileid){
		\OCP\Util::writeLog('meta_data', 'File tags: '.serialize(', ', $fileTagsArr[$fileid]), \OC_Log::WARN);
		$fileTags = isset($fileTagsArr[$fileid])?$fileTagsArr[$fileid]:[];
		if(empty($filesTags)){
			$filesTags = $fileTags;
		}
		else{
			$filesTags = array_intersect($filesTags, $fileTags);
		}
	}
	$tags = array();
	foreach($allTags as $i => $tag){
		$addTag = true;
		foreach($filesTags as $fileTag){
			if($fileTag==$tag['id']){
				$addTag = false;
				break;
			}
		}
		if($addTag && !in_array($tag, $tags)){
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
		$result = \OCA\meta_data\Tags::getTaggedFiles($tag['id'], \OCP\User::getUser());
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
	// This is for ui-autocomplete
	$tags[$i]['label'] = $tags[$i]['name'];
	$tags[$i]['value'] = $tags[$i]['id'];
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

