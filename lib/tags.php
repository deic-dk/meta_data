<?php

namespace OCA\meta_data;

class Tags {
	
	/// Centralized stuff, i.e. called by ws/* on master: All queries pertaining to meta_data_tags

	public static function dbSearchTags($name, $userid) {
		$sql = "SELECT id,name,color,owner,public FROM *PREFIX*meta_data_tags WHERE name LIKE ? AND (owner LIKE ? OR public = 1) ORDER BY public ASC";
		$args = array($name, $userid);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		$rows = $output->fetchAll();
		$result = array();
		foreach($rows as $row){
			if($row['owner']==$userid){
				$result[] = $row;
			}
		}
		foreach($rows as $row){
			if($row['owner']!=$userid){
				$result[] = $row;
			}
		}
		return $result;
	}
	
	public static function searchTags($name, $userid) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchTags($name, $userid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('searchTags', array('name'=>$name,
				'userid'=>$userid), false, true, null, 'meta_data');
		}
		return $result;
	}
	
	private static function dbSearchKey($tagid, $name, $userid) {
		$sql = "SELECT keyid,name FROM *PREFIX*meta_data_keys WHERE tagid=? AND name LIKE ? ORDER BY keyid";
		$args = array($tagid,$name);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		if($output->rowCount() > 0){
			while($row=$output->fetchRow()){
				$tag = self::dbSearchTagByID($tagid);
				if($tag['owner']==$userid || $tag['public']==1){
					$result[] = $row;
				}
			}
			return $result;
		}
		else{
			return false;
		}
	}
	
	public static function searchKey($tagid, $name, $userid=null) {
		if(empty($userid)){
			$userid = \OCP\USER::getUser();
		}
		if(empty($name)){
			return array();
		}
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchKey($tagid, $name, $userid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('searchKey', array('name'=>$name,
				'tagid'=>$tagid, 'userid'=>$userid),false, true, null, 'meta_data');
		}
		return $result;
	}

	public static function dbSearchTagByID($tagid){
		$sql = "SELECT name,color,owner,public FROM *PREFIX*meta_data_tags WHERE id=?";
		$args = array($tagid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$result=$resRsrc->fetchRow();
		return $result;
	}
	
	public static function searchTagByID($tagid){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchTagByID($tagid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('searchTagByID', array(
					'tagid'=>$tagid),false, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbSearchKeyByID($keyid){
		$sql = "SELECT name FROM *PREFIX*meta_data_keys WHERE keyid=?";
		$args = array($keyid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$result=$resRsrc->fetchRow();
		return $result;
	}
	
	public static function searchKeyByID($keyid){
		if(!OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchKeyByID($keyid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('searchKeyByID', array(
					'keyid'=>$keyid),false, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function getTagName($tagid) {
		$tag = self::searchTagByID($tagid);
		return array($tag['name'],$tag['color']);
	}
	
	public static function dbNewTag($name, $userid, $display, $color, $public){
		if(trim($name) === '') {
			\OCP\Util::writeLog('meta_data', 'Need tag name', \OC_Log::ERROR);
			return false;
		}
		if(count(self::searchTags($name, $userid)) != 0 ){
			\OCP\Util::writeLog('meta_data', 'Tag exists: '.$name, \OC_Log::ERROR);
			return false;
		}
		$sql = "INSERT INTO *PREFIX*meta_data_tags (name,owner,public,color) VALUES (?,?,?,?)";
		$args = array($name,$userid,$public,$color);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$tag = self::searchTags($name,$userid);
		self::setTagDisplay($tag['id'], $display);
		return $tag;
	}
	
	public static function newTag($name, $userid, $display, $color, $public){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbNewTag($name, $userid, $display, $color, $public);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('newTag', array('userid'=>$userid,
					'name'=>$name, 'color'=>$color, 'display'=>$display, 'public'=>$public),
					null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbDeleteTag($tagid, $userid){
		$sql = 'DELETE FROM *PREFIX*meta_data_tags WHERE tagid=? AND owner=?';
		$args = array($tagid, $userid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE tagid=?';
		$args = array($tagid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return TRUE;
	}
	
	public static function deleteTag($tagid, $userid){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbDeleteTag($tagid, $userid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('deleteTag', array(
					'tagid'=>$tagid, 'userid'=>$userid),false, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbUpdateTag($id, $name, $color, $display, $public) {
		$resRsrc = true;
		if($name || $color || $public){
			$sql = 'UPDATE *PREFIX*meta_data_tags SET '.
			(!empty($name)?'name=?, ':'').(!empty($color)?'color=?, ':'').
			(!empty($public)!=''?', public=? ':'').'WHERE id=?';
			$sql = str_replace('=?, WHERE', '=? WHERE', $sql);
			$sql = str_replace('SET ,', 'SET', $sql);
			$args = array($name, $color, $public, $id);
			$args = array_values(array_filter($args, array(__CLASS__, 'emptyTest')));
			\OCP\Util::writeLog('meta_data', 'SQL: '.$sql. ', ARGS: '.serialize($args).' --> '.count($args), \OC_Log::WARN);
			$query = \OCP\DB::prepare($sql);
			$resRsrc = $query->execute($args);
		}
		return self::setTagDisplay($id, $display) && $resRsrc;
	}
	
	public static function updateTag($id, $name, $color, $visible, $public) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbUpdateTag($id, $name, $color, $visible, $public);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('updateTag', array('id'=>$id,
					'name'=>$name, 'color'=>$color, 'visible'=>$visible, 'public'=>$public),
					null, 'meta_data');
		}
		return $result;
	}
	
	/// Distributed stuff: Searching file meta-data has to happen on each slave server.
	
	// Duplicate code in lib_files_sharding.php
	private static function switchUser($owner){
		$user_id = \OCP\USER::getUser();
		if($owner && $owner!==$user_id){
			\OC_Util::teardownFS();
			//\OC\Files\Filesystem::initMountPoints($owner);
			\OC_User::setUserId($owner);
			\OC_Util::setupFS($owner);
			\OCP\Util::writeLog('files_sharding', 'Owner: '.$owner.', user: '.\OCP\USER::getUser(), \OC_Log::WARN);
			return $user_id;
		}
		else{
			return null;
		}
	}
	
	private static function restoreUser($user_id){
		// If not done, the user shared with will now be logged in as $owner
		\OC_Util::teardownFS();
		\OC_User::setUserId($user_id);
		\OC_Util::setupFS($user_id);
	}
	
	private static function userCanReadFile($fileid, $userid){
		if($userid!=\OCP\USER::getUser()){
			$user_id = self::switchUser($userid);
		}
		$path = \OC\Files\Filesystem::getPath($fileid);
		if(!empty($path)){
			if(isset($user_id) && $user_id){
				self::restoreUser($user_id);
			}
			return true;
		}
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$shares = \OCP\Share::getItemsSharedWith('file');
		}
		else{
			$shares = \OCA\FilesSharding\Lib::ws('getItemsSharedWith', array('user_id' => \OC_User::getUser(),
						'itemType' => 'file'));
		}
		$ret = false;
		foreach($shares as $share){
			if($share['item_source']==$fileid){
				$ret = true;
				break;
			}
		}
		if(isset($user_id) && $user_id){
			self::restoreUser($user_id);
		}
		return $ret;
	}
	
	public static function searchMetadata($query, $userid) {
		$sql = "SELECT fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE value LIKE ?";
		$args = array($query);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		if($output->rowCount() > 0){
			while($row=$output->fetchRow()){
				if(self::userCanReadFile($row['fileid'], $userid)){
					$result[] = $row;
				}
			}
			return $result;
		}
		else {
			return array();
		}
	}
	
	public static function loadFileKeys($fileid, $tagid){
		$result = array();
		$sql = "SELECT keyid,value FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND tagid=?";
		$args = array($fileid, $tagid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		while($row=$resRsrc->fetchRow()){
			$result[] = array( 'value' => $row['value'],
					'keyid' => $row['keyid']);
		}
		return $result;
	}
	
	public static function dbGetTaggedFiles($tagid, $userid = null, $sortAttribute = '', $sortDescending = false){
		if(!empty($userid) && $userid!=\OCP\USER::getUser()){
			$user_id = self::switchUser($userid);
		}
		$result = array();
		$sql = "SELECT fileid FROM *PREFIX*meta_data_docTags WHERE tagid = ?";
		$args = array($tagid);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		while($row=$output->fetchRow()){
			$filepath = \OC\Files\Filesystem::getpath($row['fileid']);
			if(empty($filepath)){
				continue;
			}
			$fileInfo = \OC\Files\Filesystem::getFileInfo($filepath);
			$result[] = $fileInfo;
		}
		if(isset($user_id) && $user_id){
			self::restoreUser($user_id);
		}
		if($sortAttribute !== '') {
			return \OCA\Files\Helper::sortFiles($result, $sortAttribute, $sortDescending);
		}
		return $result;
	}

	public static function getTaggedFiles($tagid, $userid = null, $sortAttribute = '', $sortDescending = false){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			return self::dbGetTaggedFiles($tagid, $userid, $sortAttribute, $sortDescending);
		}
		$sharedItems = \OCA\FilesSharding\Lib::getItemsSharedWithUser($userid);
		$serverUsers = \OCA\FilesSharding\Lib::getServerUsers($sharedItems);
		$allServers = \OCA\FilesSharding\Lib::getServersList();
		$results = array();
		foreach($allServers as $server){
			if(!array_key_exists($server['id'], $serverUsers)){
				continue;
			}
			if(!isset($server['internal_url']) && !empty($server['internal_url'])){
				continue;
			}
			\OCP\Util::writeLog('search', 'Searching server '.$server['internal_url'], \OC_Log::WARN);
			foreach($serverUsers[$server['id']] as $owner){
				$matches = Lib::ws('getTaggedFiles', Array('userid'=>$owner, 'tagid'=>$tagid), true, true,
						$server['internal_url']);
				$res = array();
				foreach($matches as $match){
					foreach($sharedItems as $item){
						if(in_array($match, $res)){
							continue;
						}
						if(isset($item['fileid']) && isset($match['id']) && $item['fileid']==$match['id']){
							$match['server'] = $server['internal_url'];
							$match['owner'] = $owner;
							$res[] = $match;
							continue;
						}
						// Check if match is in a shared folder or subfolders thereof
						if($cache->getMimetype($item['mimetype']) === 'httpd/unix-directory'){
							$len = strlen($item['owner_path'])+1;
							\OCP\Util::writeLog('search', 'Matching '.$match['link'].':'.$item['owner_path'].' --> '.$server['internal_url'].
										' --> '.$owner, \OC_Log::WARN);
							if(substr($match['link'], 0, $len)===$item['owner_path'].'/'){
								$match['server'] = $server['internal_url'];
								$match['owner'] = $owner;
								$res[] = $match;
								continue;
							}
						}
					}
				}
				$results = array_merge($results, $res);
			}
		}
		return $results;
	}
	
	public static function getFileTags($fileid){
		$result = array();
		$sql = "SELECT tagid FROM *PREFIX*meta_data_docTags WHERE fileid = ?";
		$args = array($fileid);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		$result = [];
		while($row=$output->fetchRow()){
			$result[] = $row['tagid'];
		}
		return $result;
	}
	
	/// Local stuff: A user can only add or modify meta-data on files he owns.
	///              Thus, adding or modifying file metadata, happens locally
	///              on the user's slave server the.
	
	public static function newKey($tagid, $key) {
		if(trim($key) === '') {
			return false;
		}
		$sql = "INSERT INTO *PREFIX*meta_data_keys (tagid,name) VALUES (?,?)";
		$args = array($tagid,$key);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$key = self::searchKey($tagid, $key);
		return $key;
	}

	public static function alterKey($tagid,$keyid, $keyname, $userid) {
		$sql = 'UPDATE *PREFIX*meta_data_keys SET name=? WHERE tagid=? AND keyid=?';
		$args = array($keyname, $tagid,$keyid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return TRUE;
	}

	public static function deleteKeys($tagid, $keyid){
		$sql = 'DELETE FROM *PREFIX*meta_data_keys WHERE tagid=? AND keyid LIKE ?';
		$args = array($tagid, $keyid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
	}

	public static function updateFileTags($tagid, $userid, $fileid){
		$result = array();
		$sql = 'SELECT tagid FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
		$args = array($fileid, $tagid);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		while($row=$output->fetchRow()){
			$result[] = $row;
		}
		if(count($result) == 0){
			$sql = 'INSERT INTO *PREFIX*meta_data_docTags (fileid, tagid) VALUES (?,?)';
			$args = array($fileid, $tagid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
		}
		return $result;
	}

	public static function removeFileTag($tagid, $fileid){
			$sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
			$args = array($fileid, $tagid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
	}

	public static function removeFileKey($tagid, $fileid, $keyid){
			$sql = 'DELETE FROM *PREFIX*meta_data_docKeys WHERE fileid LIKE ? AND tagid=? AND keyid=?';
			$args = array($fileid, $tagid, $keyid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
	}

	public static function updateFileKeys ($fileid, $tagid, $keyid, $value){
		$sql = 'SELECT tagid FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND tagid=? and keyid=?';
		$args = array($fileid, $tagid, $keyid);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		while($row=$output->fetchRow()){
			$result[] = $row;
		}
		if(count($result) == 0 ) {
			$sql = 'INSERT INTO *PREFIX*meta_data_docKeys (fileid,tagid,keyid,value) VALUES (?,?,?,?)';
			$args = array($fileid, $tagid, $keyid ,$value);
			$query = \OCP\DB::prepare($sql);
			$resRsrc = $query->execute($args);
		}
		else if (count($result) == 1 ) {
			$sql = 'UPDATE *PREFIX*meta_data_docKeys SET value=? WHERE fileid=? AND keyid=? AND tagid=?';
			$args = array($value, $fileid, $keyid, $tagid);
			$query = \OCP\DB::prepare($sql);
			$resRsrc = $query->execute($args);
		}
		else {
			return false;
		}
	}

	public static function getMimeType($mtype){
		$sql = "SELECT mimetype FROM *PREFIX*mimetypes WHERE id = ?";
		$args = array($mtype);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		return $output->fetchRow();
	}

	private static function emptyTest($val) {
		return !empty($val) || $val==="0";
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

	public static function getUserDisplayTags() {
		$result = array();
		$sql = "SELECT configkey, configvalue FROM *PREFIX*preferences WHERE userid = ? AND appid = ? AND configkey LIKE ? AND configvalue = ?";
		$args = array(\OCP\USER::getUser(), 'meta_data', 'display_%', '1');
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		$result = [];
		while($row=$output->fetchRow()){
			$result[] = str_replace('display_', '', $row['configkey']);
		}
		return $result;
	}

	public static function setTagDisplay($tagid, $value) {
		if($value != 1 && $value != 0){
			throw new \Exception("Must be 1 or 0: $value");
		}
		if($value==1){
			return \OCP\Config::setUserValue(\OCP\USER::getUser(), 'meta_data', 'display_'.$tagid, $value);
		}
		else{
			$sql = "delete FROM *PREFIX*preferences WHERE userid = ? AND appid = ? AND configkey = ?";
			$args = array(\OCP\USER::getUser(), 'meta_data', 'display_'.$tagid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
			return $output;
		}
	}

}
