<?php

\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();
$tagData = $ctags->searchTag("%", OC_User::getUser(),"%");

$result  = "<ul>";
for($i=0;$i<count($tagData);$i++){
  if(!$tagData[$i]['color']) $tagData[$i]['color'] = "tc_white";
  $result .= "<li id=\"". $tagData[$i]['tagid']  . "\" data-id=\"tag_".$tagData[$i]['descr']."\"><a href=\"#\">";
  $result .= "<span class=\"tagcolor\"><i class=\"fa fa-tag " . $tagData[$i]['color'] ."\"></i></span>";
  $result .= "<span id=\"tagname\">".$tagData[$i]['descr']."</span>";
  if(!isset($_POST['type'])){
    $result .= "<input class=\"hidden\" type=\"text\" value=\"".$tagData[$i]['descr']."\"><span class=\"deletetag\">&#10006;</span>";
  }
  $result .= "</a></li>";
}
$result .= "</ul>";
echo $result;
