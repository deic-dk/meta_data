<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$rawFilesData = filter_input(INPUT_POST, 'id', FILTER_SANITIZE_URL);
$filesData = json_decode($rawFilesData);

//if(is_array($filesData)) {
//    $tagCodes = \OCA\OCLife\hTags::getCommonTagsForFiles($filesData);
//} else {
//    $tagCodes = \OCA\OCLife\hTags::getAllTagsForFile($filesData);
//}

//$tags = new \OCA\OCLife\hTags();

$result = array();
//foreach($tagCodes as $tagID) {
//    $tagData = $tags->searchTagFromID($tagID);
//    $result[] = new \OCA\OCLife\tag($tagID, $tagData['xx']);
//}

$jsonTagData = json_encode((array) $result);
echo $jsonTagData;
