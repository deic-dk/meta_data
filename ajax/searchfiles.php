<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$cfiles = new OC_meta_data_mainview();
$tagid    = $_POST['tagid'];

$files = $cfiles->searchFiles($tagid);

$result="";

foreach($files as $file){
  $result .= "<tr><td id=\"".$file['fileid'] ."\">".$file['name']."</td></tr>";
}

echo $result;
