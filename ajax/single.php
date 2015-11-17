<?php

function build_sorter_desc($key) {
		return function ($a, $b) use ($key) {
				return strnatcmp($b[$key], $a[$key]);
		};
}

OCP\JSON::checkLoggedIn();

$fileid = isset( $_GET['fileid'] ) ? $_GET['fileid'] : '';
$owner = isset( $_GET['owner'] ) ? $_GET['owner'] : '';
$user = \OCP\USER::getUser();

// Get file tags from the server of the file-owner
$tagidsArr = \OCA\Meta_data\Tags::getFileTags(array($fileid), $owner);
$tagids = isset($tagidsArr[$fileid])?$tagidsArr[$fileid]:[];
// Now get full tags from master
$alltags = \OCA\Meta_data\Tags::getTags($tagids);
$tags = [];
foreach($alltags as $tag){
	if($tag['owner']!=$user && $tag['public']==0){
		continue;
	}
	$tags[] = $tag;
}

usort($tags,build_sorter_desc('color')); 

if($tags != null){
		OCP\JSON::success(array('tags' => $tags));
}
