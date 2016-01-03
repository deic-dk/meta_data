<?php

namespace OCA\meta_data;

/**
 * This class contains all hooks.
 */
class hooks {
    private static $deleteFiles = array();
    /**
	 * @brief Deletes all tags and keys when a file is deleted
	 * @param paramters parameters from Delete-Hook
	 * @return array
	 */
	public static function deleteFile($params) {
      $fileInfo = \OC\Files\Filesystem::getFileInfo($params['path']);

      $sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE fileid=?';
      $args = array($fileInfo['fileid']);
      $query = \OCP\DB::prepare($sql);
      $resRsrc = $query->execute($args);
      
      $sql = 'DELETE FROM *PREFIX*meta_data_docKeys WHERE fileid=?';
      $args = array($fileInfo['fileid']);
      $query = \OCP\DB::prepare($sql);
      $resRsrc = $query->execute($args);

   	  return true;
	}
	
	/**
	 * Delete remaining private tags when a user is deleted.
	 * @param array $params The hook params
	 */
	public static function deleteUser($params) {
		$uid = $params['uid'];
		if(empty($uid)){
			return false;
		}
		if(\OCP\App::isEnabled('files_sharding') && !\OCA\FilesSharding\Lib::isMaster()){
			return true;
		}
		$sql = 'SELECT id FROM *PREFIX*meta_data_tags WHERE owner=? AND public=?';
		$args = array($uid, 0);
		$query = \OCP\DB::prepare($sql);
		$result = $query->execute($args);
		while($row = $result->fetchRow()){
			\OCA\Meta_data\Tags::dbDeleteKeys($row['id'], '%');
		}
	}
	
}
