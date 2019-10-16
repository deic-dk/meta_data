<?php
/**
 * ownCloud
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
 * You should have received a copy of the GNU Affero General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Search_meta_data;

use \OCP\User;
use \OCA\meta_data\Tags;

/**
 * Provide search results from the 'meta_data' app
 */
class Tag extends \OC_Search_Provider {

  /**
   * Search for tags matching the given query
   * @param string $query
   * @return \OCP\Search\Result
   */
  function search($query) {
		$results = array();
  	if(empty($query)){
  		return $results;
  	}
  	\OCP\Util::writeLog('meta_data', 'Searching tags: '.$query, \OC_Log::WARN);
		$tags = Tags::searchTags($query."%", User::getUser());
		if(!empty($tags)){
			foreach($tags as $tagData) {
				$result['type'] = 'tag';
				$result['name'] = $tagData['name'];
				$result['link'] = "/index.php/apps/files?dir=%2F&view=tag-".$tagData['id'];
				$result['owner'] = $tagData['owner'];
				$result['public'] = $tagData['public'];
				$results[] = $result;
			}
		}
  	\OCP\Util::writeLog('meta_data', 'Searching keys: '.$query, \OC_Log::WARN);
		$tags = Tags::searchKeys($query."%", User::getUser());
		if(!empty($tags)){
			foreach($tags as $tagData) {
				$result['type'] = 'tag';
				$result['name'] = $tagData['name'];
				$result['link'] = "/index.php/apps/files?dir=%2F&view=tag-".$tagData['id'];
				$result['owner'] = $tagData['owner'];
				$result['public'] = $tagData['public'];
				if(in_array($result, $results)){
					continue;
				}
				$results[] = $result;
			}
		}
		return $results;
  }
}


class Metadata extends \OC_Search_Provider {
  /**
   * Search for meta data matching the given query
   * @param string $query
   * @return \OCP\Search\Result
   */

	function search($query) {
		$results = array();
		$userid = User::getUser();
		// Rich metadata search.
		// TODO: improve this
		$queryTag = preg_replace('|.*\W*tag: *(\w+)\W*.*|i', '$1', $query, 1, $reps);
		if($reps==0){
			$queryTag = '';
			$queryTagID = '';
		}
		else{
			$queryTagIDs = Tags::searchTags($queryTag, $userid);
			if(empty($queryTagIDs)){
				// Non-existing tag
				return $results;
			}
			$queryTagID = $queryTagIDs[0]['id'];
		}
		if(!empty($queryTag)){
			$query = preg_replace('|(.*)\W*tag: *\w+\W*(.*)|i', '$1$2', $query, 1);
			\OCP\Util::writeLog('meta_data', 'Query now: '.$query, \OC_Log::WARN);
		}
		$queryKeyValues = array();
		while(true){
			$queryKeyValueStr = preg_replace('#^(\W*|.*\W+)(\w+): *(\w+)(\W*.*|\W*)$#', '$2:$3', $query, 1, $reps);
			if($reps==0 || empty($queryKeyValueStr)){
				break;
			}
			else{
				$queryKeyValueArr = explode(':', $queryKeyValueStr);
				$queryKeyValues[$queryKeyValueArr[0]] = $queryKeyValueArr[1];
				$query = preg_replace('#^(\W*|.*\W+)\w+: *\w+(\W*.*|\W*)$#', '$1$2', $query, 1);
			}
		}
		$query = trim($query);
		\OCP\Util::writeLog('meta_data', 'Searching meta: '.$queryTag.':'.$queryTagID.' --> '.
				serialize($queryKeyValues).' --> '.$query, \OC_Log::WARN);
		$tags = Tags::searchMetadata($query, $userid, $queryTagID, '', $queryKeyValues);
		$tagids = array_map(function($x){ return $x['tagid']; }, $tags);
		$keyids = array_map(function($x){ return $x['keyid']; }, $tags);
		$tagInfos = Tags::searchTagsByIDs($tagids);
		$keyInfos = Tags::searchKeysByIDs($keyids);
		foreach ($tags as $tagData) {
			// TODO: support group folders
			$filepath = \OC\Files\Filesystem::getpath($tagData['fileid']);
			\OCP\Util::writeLog('meta_data', 'Found file: '.$tagData['fileid'].' --> '.$filepath, \OC_Log::WARN);
			$fileInfo = \OC\Files\Filesystem::getFileInfo($filepath);
			$tagInfo = $tagInfos[$tagData['tagid']];
			if(!empty($tagData['keyid']) && !empty($keyInfos[$tagData['keyid']])){
				$keyInfo = $keyInfos[$tagData['keyid']];
			}
			$result['fileid'] = $tagData['fileid'];
			$result['type'] = 'metadata';
			$result['name'] = $fileInfo['name'];
			$tagLink = \OCP\Util::linkTo(
				'files',
				'index.php',
				array('view' => 'tag-'.$tagInfo['id'])
			);
			$result['text']  = "<a class='label outline' href='".$tagLink."'><i class='icon-tag'></i>".
				$tagInfo['name']."</a>".(empty($keyInfo)?'':$keyInfo['name']."=".$tagData['value']);
			$result['color'] = $tagInfo['color'];
			$path = preg_replace('|^files/|', '/', $fileInfo['path']);
			$result['link'] = \OCP\Util::linkTo(
				'files',
				'index.php',
				array('dir' => $path, 'file' => $fileInfo['name'])
			);
			$result['permissions'] = $fileInfo['permissions'];
			$result['path'] = $path;
			$result['parentdir'] = dirname($path);
			$result['parentid'] = \OCA\FilesSharding\Lib::getFileId($result['parentdir']);
			$result['modified'] = $fileInfo['mtime'];
			$result['mime_type'] = $fileInfo['mimetype'];
			$results[] = $result;
		}
		return $results;
  }

}

