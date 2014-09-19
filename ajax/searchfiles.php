<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();
$tagname    = $_POST['tagName'];

$tagid  = $ctags->searchTag($tagname, OC_User::getUser(), "%");
$files = $ctags->searchFiles($tagid[0]['tagid']);

$result="";

$result .= "<table><legend>Files tagged with: ".$tagname."</legend>";
foreach($files as $file){
  $result .= "<tr><td>".$file['name']."</td></tr>";
}
$result .= "</table>";

echo $result;
