<?php

namespace OCA\Meta_data;
use OC\Files\FileInfo;

class Helper
{
		public static function getTaggedFiles($tagid, $user, $sortAttribute = '', $sortDescending = false){
				$result = array();

				$sql = "SELECT fileid FROM *PREFIX*meta_data_docTags WHERE tagid = ?";
				$args = array($tagid);
				$query = \OCP\DB::prepare($sql);
				$output = $query->execute($args);

				while($row=$output->fetchRow()){
						$filepath = \OC\Files\Filesystem::getpath($row['fileid']);
						$fileInfo = \OC\Files\Filesystem::getFileInfo($filepath);
						$result[] = $fileInfo;
				}

				if ($sortAttribute !== '') {
						return \OCA\Files\Helper::sortFiles($result, $sortAttribute, $sortDescending);
				}
				return $result;
		}

		public static function getFileTags($fileid){
				$result = array();
				$sql = "SELECT tagid FROM *PREFIX*meta_data_docTags WHERE fileid = ?";
				$args = array($fileid);
				$query = \OCP\DB::prepare($sql);
				$output = $query->execute($args);

				
				while($row=$output->fetchRow()){
						$result[] = $row['tagid'];
				}
				
				return $result;
		}


		public static function searchTag($descr, $userid) {                                                                           
				$sql = "SELECT tagid,descr,color FROM *PREFIX*meta_data_tags WHERE descr LIKE ? AND owner LIKE ?";
				$args = array($descr,$userid);                                                                                    
				$query = \OCP\DB::prepare($sql);                                                                                       
				$output = $query->execute($args);                                                                                     

				while($row=$output->fetchRow()){
						$result[] = $row;
				}    
				return $result;
		}  

		public static function formatFileInfo($i) {
				$entry = array();

				$entry['id'] = $i['fileid'];
				$entry['parentId'] = $i['parent'];
				$entry['date'] = \OCP\Util::formatDate($i['mtime']);
				$entry['mtime'] = $i['mtime'] * 1000;
				$path = explode("/", dirname($i['path']));
				unset($path[0]);
				$path = implode("/", $path);
				$entry['path'] = $path;
				// only pick out the needed attributes
				$entry['icon'] = \OCA\Files\Helper::determineIcon($i);
				if (\OC::$server->getPreviewManager()->isMimeSupported($i['mimetype'])) {
						$entry['isPreviewAvailable'] = true;
				}
				$entry['name'] = $i->getName();
				$entry['permissions'] = $i['permissions'];
				$entry['mimetype'] = $i['mimetype'];
				$entry['size'] = $i['size'];
				$entry['type'] = $i['type'];
				$entry['etag'] = $i['etag'];
				if (isset($i['displayname_owner'])) {
						$entry['shareOwner'] = $i['displayname_owner'];
				}
				if (isset($i['is_share_mount_point'])) {
						$entry['isShareMountPoint'] = $i['is_share_mount_point'];
				}
				$mountType = null;
				if ($i->isShared()) {
						$mountType = 'shared';
				} else if ($i->isMounted()) {
						$mountType = 'external';
				}
				if ($mountType !== null) {
						if ($i->getInternalPath() === '') {
								$mountType .= '-root';
						}
						$entry['mountType'] = $mountType;
				}
				$entry['tags'] = $i['tags'];
				return $entry;

		}

		public static function formatFileInfos($fileInfos) {
				$files = array();
				foreach ($fileInfos as $i) {
						$files[] = self::formatFileInfo($i);
				}

				return $files;
		}



}
