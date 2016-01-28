<?php

/**
* ownCloud files_sharding app
*
* @author Frederik Orellana
* @copyright 2014 Frederik Orellana
* 
* This library is free software; you can redistribute it and/or
* modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
* License as published by the Free Software Foundation; either 
* version 3 of the License, or any later version.
* 
* This library is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU AFFERO GENERAL PUBLIC LICENSE for more details.
*  
* You should have received a copy of the GNU Lesser General Public 
* License along with this library.  If not, see <http://www.gnu.org/licenses/>.
* 
*/

OCP\JSON::checkAppEnabled('files_sharding');
//OCP\JSON::checkLoggedIn();
if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

//\OC_Util::setupFS($user_id);

$user_id = $_GET['user'];
$baseurl = $_GET['url'];

function getRow($array, $key, $val) {
	foreach($array as $row){
		if($row[$key]===$val){
			return $row;
		}
	}
	return null;
}

// Get map fileID->[tag1, tag2, ...] with file owned by user
$fileTagsArr = \OCA\FilesSharding\Lib::ws('getUserFileTags', array('user_id'=>$user_id), false, false,
		$baseurl, 'meta_data');
// Get all files owned by user from old server
$oldUserFiles = \OCA\FilesSharding\Lib::ws('get_user_files', array('user_id'=>$user_id), false, true, $baseurl);
// Get all files owned by user locally/on new server
$newUserFiles = \OCA\FilesSharding\Lib::dbGetUserFiles($user_id);

// Fix up file tags with new fileid instead of old one
foreach($fileTagsArr as $fileTags){
	$oldFileID = $fileTags->fileid;
	$oldFile = getRow($oldUserFiles, 'fileid', $fileID);
	$path = $oldFile['path']; // starts with "files/"
	$newFile = getRow($newUserFiles, 'path', $path);
	$newFileID = $newFile['fileid'];
	\OCP\Util::writeLog('meta_data', 'Inserting tags for '.$path.': '.$fileID.'-->'.$newID, \OC_Log::WARN);
	$fileTags->setFileID($newFileID);
}
// Now insert file tags in the local DB
$ret = \OCA\meta_data\Tags::dbInsertUserFileTags($user_id, $fileTagsArr);

if(!empty($ret)){
	OCP\JSON::encodedPrint($ret);
}
else{
	OCP\JSON::error($ret);
}
