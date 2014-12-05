<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');


$ctags = new \OCA\meta_data\tags();
$owner = OC_User::getUser();
$data = explode(":", $_POST['data']);

if($data[0] == 'tag'){
  //search tags;
  $result[] = 'tag';
  $tags=$ctags->searchTag($data[1],$owner, "%");
  foreach($tags as $tag){
    $result[] = $tag['tagid'];
  }
} else if ($data[0] == 'key'){
  //search keys;
  $result[] = 'key';
  $tags=$ctags->searchTag("%",$owner, "%");
  foreach($tags as $tag){
    $key=$ctags->searchKey($tag['tagid'],$data[1]);
    if($key){
      $result[] = $tag['tagid'];
      $key=FALSE;
    }
  }
} else {
  //search everything;
}


echo json_encode($result);




