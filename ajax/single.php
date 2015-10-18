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

$tagids = \OCA\Meta_data\Tags::getFileTags($fileid, $owner);
$alltags = \OCA\Meta_data\Tags::getTags($tagids);
$tags=[];
foreach ($alltags as $tag){
	if($owner!=$user && $tag['public']==0){
		continue;
	}
	$tags[] = $tag;
}

usort($tags,build_sorter_desc('color')); 

if($tags != null){
		OCP\JSON::success(array('tags' => $tags));
}
