<?php


function build_sorter_desc($key) {
  return function ($a, $b) use ($key) {
	return strnatcmp($b[$key], $a[$key]);
  };
}


OCP\JSON::checkLoggedIn();
\OC::$session->close();
$files = isset($_POST['files']) ? $_POST['files'] : '';
$dirowner = isset($_POST['owner']) ? $_POST['owner'] : '';
$fileowners = isset($_POST['fileowners']) ? $_POST['fileowners'] : '';
$user = \OCP\USER::getUser();

// Get file tags from the server of the file-owner
$fileids = array_map(function($file){ return $file['id']; }, $files);
$tagidsArr = \OCA\Meta_data\Tags::getFileTags($fileids, $dirowner);
$tagids = array();
foreach($tagidsArr as $fileid=>$filetags){
	$tagids = array_merge($tagids, $filetags);
}
$alltagids = array_unique($tagids);
\OCP\Util::writeLog('meta_data', 'File tags: '.implode(', ', $fileids).'-->'.implode($alltagids), \OC_Log::WARN);
// Get full tags from master
$alltags = \OCA\Meta_data\Tags::getTags($alltagids);
$tagsArr = array();
foreach($tagidsArr as $fileid=>$tagids){
	foreach($tagids as $tagid){
		foreach($alltags as $tag){
			\OCP\Util::writeLog('meta_data', 'matching '.$tag['id'].'<->'.$tagid, \OC_Log::WARN);
			if($tag['id']==$tagid){
				\OCP\Util::writeLog('meta_data', 'File tag: '.serialize($tag), \OC_Log::WARN);
				if(isset($tagsArr[$fileid])){
					$tagsArr[$fileid][] = $tag;
				}
				else{
					$tagsArr[$fileid] = array($tag);
				}
				break;
			}
		}
	}
}
\OCP\Util::writeLog('meta_data', 'All file tags: '.serialize($tagsArr), \OC_Log::WARN);
foreach($files as $i => $file){
	$owner = $dirowner;
	if(!empty($fileowners) && isset($fileowners[$i]['owner'])){
		$owner = $fileowners[$i]['owner'];
	}
	$tags = [];
	$alltags = isset($tagsArr[$file['id']])?$tagsArr[$file['id']]:[];
	foreach($alltags as $tag){
		// For shared files, display only public tags to non-owners
		if($tag['owner']!=$user && /*$owner!=$user &&*/ $tag['public']==0){
			continue;
		}
		$tags[] = $tag;
	}
	if(!empty($tags)){
		usort($tags,build_sorter_desc('color')); 
		$files[$i]['tags'] = $tags;
	}
}

OCP\JSON::success(array('files' => $files));

