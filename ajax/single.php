<?php

function build_sorter_desc($key) {
		return function ($a, $b) use ($key) {
				return strnatcmp($b[$key], $a[$key]);
		};
}

OCP\JSON::checkLoggedIn();

$fileid = isset( $_GET['fileid'] ) ? $_GET['fileid'] : '';

$tagids = \OCA\Meta_data\Tags::getFileTags($fileid);

$tags=[];
foreach ($tagids as $index => $tag){
		$result = \OCA\Meta_data\Tags::getTagName($tag);
		$tags[$index]['id']=$tag;
		$tags[$index]['name']=$result[0];
		$tags[$index]['color']=$result[1];
	
}

usort($tags,build_sorter_desc("color")); 

if($tags != null){
		OCP\JSON::success(array('tags' => $tags));
}
