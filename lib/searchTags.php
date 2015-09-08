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
		$tags = Tags::searchTags($query."%", User::getUser());
		$results = array();
		foreach ($tags as $tagData) {
			$result['type'] = 'tag';
			$result['name'] = $tagData['name'];
			$result['link'] = "/index.php/apps/files?dir=%2F&view=tag-".$tagData['id'];
			$results[] = $result;
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
		$tags = Tags::searchMetadata($query."%", User::getUser());
		$results = array();
		foreach ($tags as $tagData) {
			$filepath = \OC\Files\Filesystem::getpath($tagData['fileid']);
			$fileInfo = \OC\Files\Filesystem::getFileInfo($filepath);
			$tagInfo = Tags::searchTagByID($tagData['id']);
			$keyInfo = Tags::searchKeyByID($tagData['keyid']);
			
			$result['fileid'] = $tagData['fileid'];
			$result['type'] = 'metadata';
			$result['name'] = $fileInfo['name'];
			$result['text']  = "<span class='label outline'><i class='icon-tag'></i>". $tagInfo['name']."</span>".$keyInfo['name']."=".$tagData['value'];
			$result['color'] = $tagInfo['color'];
			$result['link'] = \OCP\Util::linkTo(
				'files',
				'index.php',
				array('dir' => $fileInfo['path'], 'file' => $FileInfo['name'])
			);
			$result['permissions'] = $fileInfo['permissions'];
			$result['path'] = $fileInfo['path'];
			$result['modified'] = $fileInfo['mtime'];
			$result['mime_type'] = $fileInfo['mimetype'];
			$results[] = $result;
		}
		return $results;
  }

}

