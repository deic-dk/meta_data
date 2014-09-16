<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$fileID   = $_POST['fileid'];


$result = $ctags->loadFileTags($fileID);

$result1 = array();

foreach($result as $key=>$value){
  $result1[] = $value;
} 


echo  json_encode((array) $result);
