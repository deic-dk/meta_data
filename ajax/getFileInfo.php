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


$infos = array();
$infos[] = '<strong>Filename: </strong>' . $fileInfos['name'];
$infos[] = '<strong>MIME: </strong>' . $fileInfos['mimetype'];
//$infos[] = '<strong>etag: </strong>' . $fileInfos['etag'];
//$infos[] = '<strong>Size: </strong>' . \OCA\OCLife\utilities::formatBytes($fileInfos['size'], 2, TRUE);
//$infos[] = '<strong>' . $l->t('When added') . ': </strong>' . \OCP\Util::formatDate($fileInfos['storage_mtime']);
//$infos[] = '<strong>' . $l->t('Encrypted? ') . '</strong>' . (($fileInfos['encrypted'] === TRUE) ? $l->t('Yes') : $l->t('No'));

//if($fileInfos['encrypted']) {
//    $infos[] = '<strong>' . $l->t('Unencrypted size') . ': </strong>' . \OCA\OCLife\utilities::formatBytes($fileInfos['unencrypted_size'], 2, TRUE);
//}

$htmlInfos = implode('<br />', $infos);

$result = array('infos' => $htmlInfos, 'fileid' => $fileInfos['fileid']);

print json_encode($result);
