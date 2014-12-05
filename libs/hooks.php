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
}
