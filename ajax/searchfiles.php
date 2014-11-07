<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

function compare($val1, $val2){
  return strcmp($val1['fileid'], $val2['fileid']);
}

$cfiles = new OC_meta_data_mainview();

$owner = OC_User::getUser(); 


if( isset($_POST['tagids'])){
  $tags = json_decode(stripslashes($_POST['tagids']));
  $files = $cfiles->searchFiles($owner, $tags[0]);
  foreach($tags as $tag){
    $temp = $cfiles->searchFiles($owner, $tag);
    $files = array_uintersect($files, $temp, 'compare');
  }
} else {
  $files = $cfiles->searchFiles($owner);
}

if($files){
  $result="<ul>";
  foreach($files as $file){
    $result .= "<li id=\"". $file['fileid']  . "\" data-original=\"".$file['name']."\"></li>" ;
  }
  $result .= "</ul>";
} else {
  $result="<div id=\"emptysearch\">No files found</div>";
}
echo $result;
