<?php

function build_sorter_desc($key) {
		return function ($a, $b) use ($key) {
				return strnatcmp($b[$key], $a[$key]);
		};
}

OCP\JSON::checkLoggedIn();

$fileid = isset( $_GET['fileid'] ) ? $_GET['fileid'] : '';


$tagids = \OCA\Meta_data\Helper::getFileTags($fileid);

$tags=[];
foreach ($tagids as $index => $tag){
		$result = \OCA\Meta_data\Helper::getTagName($tag);
		$tags[$index]['tagid']=$tag;
		$tags[$index]['descr']=$result[0];
		$tags[$index]['color']=$result[1];
	
}

usort($tags,build_sorter_desc("color")); 




if($tags != null){
		OCP\JSON::success(array('tags' => $tags));
}

