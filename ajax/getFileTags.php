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

foreach ($files as $i => $file){
	
	$owner = $dirowner;
	if(!empty($fileowners) && isset($fileowners[$i]['owner'])){
		$owner = $fileowners[$i]['owner'];
	}

	// Get tags from the server of the file-owner
	$tagids = \OCA\Meta_data\Tags::getFileTags($file['id'], $owner);
	$alltags = \OCA\Meta_data\Tags::getTags($tagids);
	$tags = [];
	foreach ($alltags as $tag){
		// For shared files, display only public tags to non-owners
		if($owner!=$user && $tag['public']==0){
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

