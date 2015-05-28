<?php


function build_sorter_desc($key) {
  return function ($a, $b) use ($key) {
	return strnatcmp($b[$key], $a[$key]);
  };
}


OCP\JSON::checkLoggedIn();
\OC::$session->close();
//$l = OC_L10N::get('files');

// Load the files
$dir = isset($_GET['dir']) ? $_GET['dir'] : '';
$dir = \OC\Files\Filesystem::normalizePath($dir);

try {
  $dirInfo = \OC\Files\Filesystem::getFileInfo($dir);
  if (!$dirInfo || !$dirInfo->getType() === 'dir') {
	header("HTTP/1.0 404 Not Found");
	exit();
  }

  $data = array();
  $baseUrl = OCP\Util::linkTo('files', 'index.php') . '?dir=';

  $permissions = $dirInfo->getPermissions();

//  $sortAttribute = isset($_GET['sort']) ? $_GET['sort'] : 'name';
//  $sortDirection = isset($_GET['sortdirection']) ? ($_GET['sortdirection'] === 'desc') : false;

  // make filelist

//  $files = \OCA\Files\Helper::getFiles($dir, $sortAttribute, $sortDirection);
//  $data['directory'] = $dir;
//  $data['files'] = \OCA\Files\Helper::formatFileInfos($files);
//  $data['permissions'] = $permissions;

  $files = isset($_GET['fileData']) ? $_GET['fileData'] : '';

  foreach ($files as $nindex => $file){
    $tagids = \OCA\Meta_data\Helper::getFileTags($file['id']);
    $tags=[];
    foreach ($tagids as $index => $tag){
	  $result = \OCA\Meta_data\Helper::getTagName($tag);
	  $tags[$index]['tagid']=$tag;
	  $tags[$index]['descr']=$result[0];
	  $tags[$index]['color']=$result[1];

	}


	if($tags != null){
	  usort($tags,build_sorter_desc("color")); 
	  $files[$nindex]['tags']=$tags;
	}
  }

  $data = array('files' => $files);
  $data['directory'] = $dir;
  $data['permissions'] = $permissions;

  OCP\JSON::success(array('data' => $data));
} catch (\OCP\Files\StorageNotAvailableException $e) {
  OCP\JSON::error(array(
	'data' => array(
	  'exception' => '\OCP\Files\StorageNotAvailableException',
	  'message' => $l->t('Storage not available')
	)
  ));
} catch (\OCP\Files\StorageInvalidException $e) {
  OCP\JSON::error(array(
	'data' => array(
	  'exception' => '\OCP\Files\StorageInvalidException',
	  'message' => $l->t('Storage invalid')
	)
  ));
} catch (\Exception $e) {
  OCP\JSON::error(array(
	'data' => array(
	  'exception' => '\Exception',
	  'message' => $l->t('Unknown error')
	)
  ));
}