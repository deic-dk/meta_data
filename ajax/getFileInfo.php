<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkAppEnabled('meta_data');
\OCP\User::checkLoggedIn();

// Begin to collect files informations
/*
 *  $fileInfos contains:
 * Array ( [fileid] => 30 
 * [storage] => home::qsecofr 
 * [path] => files/Immagini/HungryIla.png 
 * [parent] => 18 
 * [name] => HungryIla.png 
 * [mimetype] => image/png 
 * [mimepart] => image 
 * [size] => 3981786 
 * [mtime] => 1388521137 
 * [storage_mtime] => 1388521137 
 * [encrypted] => 1 
 * [unencrypted_size] => 3981786 
 * [etag] => 52c326b169ba4
 * [permissions] => 27 ) 
 */

$filepath = \OC\Files\Filesystem::getpath($_POST['fileId']);
$fileInfo = \OC\Files\Filesystem::getFileInfo($filepath);



$units = array('B', 'KB', 'MB', 'GB', 'TB'); 
$bytes = $fileInfo['size'];
$bytes = max($bytes, 0); 
$pow = floor(($bytes ? log($bytes) : 0) / log(1024)); 
$pow = min($pow, count($units) - 1); 
$bytes /= pow(1024, $pow);
$size=round($bytes, 2) . ' ' . $units[$pow];

$result = "<div id='infopath'>https://data.deic.dk/".$fileInfo['path']."</div>";
$result.= "<div id='minsize'>".$fileInfo['mimetype']." - ".$size."</div>";
$result.= "<div id='modified'><b>Modified</b> ".gmdate("d-m-Y H:i:s", $fileInfo['mtime'])."</div>";
$result.= "<div id='taginfo'>";
$ctags = new \OCA\meta_data\tags();
$tagData = $ctags->loadFileTags($_POST['fileId']);
foreach($tagData as $tag){
  if(!$tag['color']) $tag['color']="tc_white";
  $result.= "<i class=\"icon-tag ". $tag['color'] ."\"></i>".$tag['descr']. " ";
}
$result.= "<div id=\"addNewTag\"><a>add tag</a><div id=\"test\"></div></div></div>";






print json_encode($result);
