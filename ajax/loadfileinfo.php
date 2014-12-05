<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$type     = $_POST['type'];
$fileID   = $_POST['fileid'];


if($type == 'key'){
  $result = $ctags->loadFileKeys($fileID);
} else if($type == 'tag') {
  $result = $ctags->loadFileTags($fileID);
} else {
  $result = "";
}

echo  json_encode((array) $result);
