<?php


function build_sorter_desc($key) {
  return function ($a, $b) use ($key) {
	return strnatcmp($b[$key], $a[$key]);
  };
}


OCP\JSON::checkLoggedIn();
\OC::$session->close();
$files = isset($_POST['files']) ? $_POST['files'] : '';

foreach ($files as $i => $file){

	$tagids = \OCA\Meta_data\Tags::getFileTags($file['id']);

	$tags=[];
	foreach ($tagids as $index => $tag){
		$result = \OCA\Meta_data\Tags::getTagName($tag);
		$tags[$index]['id'] = $tag;
		$tags[$index]['name'] = $result[0];
		$tags[$index]['color'] = $result[1];
	}

	if($tags != null){
		usort($tags,build_sorter_desc('color')); 
		$files[$i]['tags'] = $tags;
	}
}

OCP\JSON::success(array('files' => $files));

