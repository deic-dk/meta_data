<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

function compare($val1, $val2){
  return strcmp($val1['fileid'], $val2['fileid']);
}

$cfiles = new OC_meta_data_mainview();
$ctags = new \OCA\meta_data\tags();

$owner = OC_User::getUser(); 


if( isset($_POST['tagids'])){
  // Retrieve only selected files
  $tags = json_decode(stripslashes($_POST['tagids']));
  $files = $cfiles->searchFiles($owner, $tags[0]);
  foreach($tags as $tag){
    $temp = $cfiles->searchFiles($owner, $tag);
    $files = array_uintersect($files, $temp, 'compare');
  }
} else {
  // Retrieve all files
  $files = $cfiles->searchFiles($owner);
  foreach($files as $key=>$file){
    $data=$ctags->getMimeType($file['mimetype']);
    $temp = explode('/', $data['mimetype']);
    if($temp[0] == 'audio' or $temp[0] == 'image' or $temp[0] == 'text' or $temp[0] == 'video'){
      $files[$key]['mimetype']=$temp[0];
    } else if ($temp[0] == 'application'){
      if($temp[1] == 'msexcel' or $temp[1] == 'mspowerpoint' or $temp[1] == 'msword' or $temp[1] == 'pdf' or $temp[1] == 'postscript' or $temp[1] == 'zip' or $temp[1] == 'xml'){
        $files[$key]['mimetype']=$temp[1];
      } else if ($temp[1] =='x-bzip2' or $temp[1] == 'x-gzip' or $temp[1] == 'x-tar' or $temp[1] == 'x-tex'){
        $temp2 = explode('-',$temp[1]);
        $files[$key]['mimetype'] = $temp2[1];
      } else {
        $files[$key]['mimetype'] = $temp[0];
      }
    } else {
      $files[$key]['mimetype'] = 'other';
    }
  }
  $sortArray = array();
  foreach($files as $file){
    foreach($file as $key=>$value){
      if(!isset($sortArray[$key])){
        $sortArray[$key] = array();
      }
      $sortArray[$key][] = $value;
    }
  }
  $orderby = "mimetype"; 
  array_multisort($sortArray[$orderby],SORT_ASC,$files);
}
$mtype = null;

if($files){
  $result="<ul>";
  foreach($files as $file){
    if($file['mimetype'] != $mtype and isset($file['mimetype'])){
      $mtype = $file['mimetype'];
      $result .= "</ul><div id=\"mtype\">". $file['mimetype'] ."</div><ul>";
    }
    $result .= "<li id=\"". $file['fileid']  . "\" data-original=\"".$file['name']."\">";
    $result .= "<span id=\"name\"></span><span id=\"tags\" style=\"float:right;margin-right:5px \">";
    $tags = $ctags->loadFileTags($file['fileid']);
    if($tags){
      foreach($tags as $tag){
        $color = $ctags->searchTagbyID($tag['tagid']);
        $result .= "<span style=\"margin: 0 0 0 -0.5em\">";
        $result .= "<i class=\"icon-tag ".$color['color']."\"></i></span>";    
      }
    }
    $result .= "</span></li>";
  }
  $result .= "</ul>";
} else {
  $result="<div id=\"emptysearch\">No files found</div>";
}
echo $result;
