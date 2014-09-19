<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkAppEnabled('meta_data');
\OCP\User::checkLoggedIn();

// Revert parameters from ajax
$filePath = filter_input(INPUT_POST, 'filePath', FILTER_SANITIZE_STRING);

// Check if multiple file has been choosen
if(substr($filePath, -1) === '/') {
    
    $infos = '<strong>' . $l->t('Multiple files selected') . '</strong>';

    $result = array('infos' => $infos, 'fileid' => -1);

    print json_encode($result);
    die();
}

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
$fileInfos = \OC\Files\Filesystem::getFileInfo($filePath);


//$infos = array();
//$infos[] = '<strong>Filename: </strong>' . $fileInfos['name'];
//$infos[] = '<strong>MIME: </strong>' . $fileInfos['mimetype'];
//$htmlInfos = implode('<br />', $infos);

$htmlInfos  = '<strong>Filename: </strong>' . $fileInfos['name'] . "<br>";
$htmlInfos .= '<strong>MIME: </strong>' . $fileInfos['mimetype'];
if($fileInfos['mimetype'] == "audio/mpeg"){
  $htmlInfos .= '<input type="button" id="importTags" class="MP3" value="Import MP3 tags"><br>';
} else {
  $htmlInfos .= '<br>';
}


$result = array('infos' => $htmlInfos, 'fileid' => $fileInfos['fileid']);

print json_encode($result);
