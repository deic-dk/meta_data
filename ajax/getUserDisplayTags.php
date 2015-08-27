<?php


function build_sorter_desc($key) {
  return function ($a, $b) use ($key) {
	return strnatcmp($b[$key], $a[$key]);
  };
}


OCP\JSON::checkLoggedIn();
\OC::$session->close();

$tagids = \OCA\Meta_data\Tags::getUserDisplayTags();
$tags=[];
$i = 0;
foreach ($tagids as $id){
	$result = \OCA\Meta_data\Tags::getTagName($id);
	$tags[$i]['id'] = $id;
	$tags[$i]['name'] = $result[0];
	$tags[$i]['color'] = $result[1];
	++$i;
}

OCP\JSON::success(array('tags' => $tags));
