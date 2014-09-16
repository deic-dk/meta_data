<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$tagData = $ctags->searchTag("%",OC_User::getUser(),"%");

$searchKey = filter_input(INPUT_GET, 'term', FILTER_SANITIZE_STRING);

$result = array();

foreach($tagData as $tag) {
  if(is_null($searchKey) || $searchKey === FALSE || $searchKey === '') {
    $result[] = $tag['descr'];
  } else {
    if(strpos($tag['descr'], $searchKey) !== FALSE) {
      $result[] = $tag['descr'];
    }
  }
}

$jsonTagData = json_encode((array) $result);
echo $jsonTagData;
