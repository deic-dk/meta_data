<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$list = $_POST['list'];

$result = "";
foreach($list as $tag){
  $keyData = $ctags->searchKey($tag['value'], "%");
  
  foreach($keyData as $key) {
    $result .=  "<tr data-tag=\"" . $tag['value'] . "\" class=\"keyRow hidden\"><td>" . $key['descr'] . "</td><td><input data-keyid=\"" . $key['keyid'] . "\" data-key=\"" . $key['descr'] . "\" type=\"text\" value=\"\"></td></tr>" ;
  }
}

echo $result;
