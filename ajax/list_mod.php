<?php


function build_sorter_desc($key) {
  return function ($a, $b) use ($key) {
	return strnatcmp($b[$key], $a[$key]);
  };
}


OCP\JSON::checkLoggedIn();
\OC::$session->close();
$files = isset($_POST['fileData']) ? $_POST['fileData'] : '';

foreach ($files as $nindex => $file){

  $tagids = \OCA\Meta_data\Helper::getFileTags($file['id']);

  $tags=[];
  foreach ($tagids as $index => $tag){
	$result = \OCA\Meta_data\Helper::getTagName($tag);
	$tags[$index]['tagid']=$tag;
	$tags[$index]['descr']=$result[0];
	$tags[$index]['color']=$result[1];

  }

  if($tags != null){
	usort($tags,build_sorter_desc("color")); 
	$files[$nindex]['tags']=$tags;
  }
}


OCP\JSON::success(array('files' => $files));

