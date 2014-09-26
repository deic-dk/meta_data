<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$cfiles = new OC_meta_data_mainview();
$tagid    = $_POST['tagid'];

$files = $cfiles->searchFiles($tagid);



$result="";

foreach($files as $file){
  $result .= "<tr><td id=\"".$file['fileid'] ."\">";
//  $result .= "  <a class=\"name\" href=\"https://data.deic.dk/index.php/apps/files?dir=".$path."\">";
  $result .= "    <span class=\"nametext\">".$file['name']."</span>";
  $result .= "    <span class=\"path\" id=\"".str_replace("files","",$file['path'])."\"></span>";
  $result .= "    <span class=\"fileactions\">";
  $result .= "      <a class=\"action\" data-action=\"Tags\" href=\"#\" original-title=\"\">";
  $result .= "        <img class=\"svg\" src=\"/apps/meta_data/img/icon_info.svg\">";
  $result .= "        <span> Tags</span>";
  $result .= "      </a>";
  $result .= "    </span>";
//  $result .= "  </a>";
  $result .= "</td></tr>";
}

echo $result;
