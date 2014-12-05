<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');


$ctags = new \OCA\meta_data\tags();
$keyData = $ctags->searchKey($_POST['tagid'],"%");

if($keyData){
  $result  = "<ul>";
  for($i=0;$i<count($keyData);$i++){
    $result .= "<li id=\"". $keyData[$i]['keyid']  . "\">";
    $result .= "<span class=\"keyname\">".$keyData[$i]['descr']."</span>";
    $result .= "<input class=\"edit hidden\" type=\"text\" value=\"".$keyData[$i]['descr']."\"><span class=\"deletetag\">&#10006;</span>";
    $result .= "<input class=\"" . $keyData[$i]['keyid'] . " value hidden\" type=\"text\" value=\"\"></li>";
  }
  $result .= "</ul>";
} else {
  $result = "<div id=\"emptysearch\">No meta data defined</div>";
;
}
echo $result;
