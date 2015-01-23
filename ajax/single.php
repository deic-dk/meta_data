<?php

OCP\JSON::checkLoggedIn();

$fileid = isset( $_GET['fileid'] ) ? $_GET['fileid'] : '';


$tags = \OCA\Meta_data\Helper::getFileTags($fileid);

foreach ($tags as $tag){
		$result = \OCA\Meta_data\Helper::getTagName($tag);
		$tagname[]=$result[0];
		$tagcolor[]=$result[1];
	
}


$tagids  =implode(',',$tags);


OCP\JSON::success(array('tagids' => $tagids, 'tagnames' => $tagname, 'tagcolor' => $tagcolor));
