<?php

namespace OCA\meta_data;

class Tags {
	
	/// Centralized stuff, i.e. called by ws/* on master: All queries pertaining to meta_data_tags

	public static function dbSearchTags($name, $userid) {
		$sql = "SELECT id,name,description,color,owner,public FROM *PREFIX*meta_data_tags WHERE name LIKE ? AND (owner LIKE ? OR public = 1) ORDER BY public ASC";
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
			$result = \OCA\FilesSharding\Lib::ws('searchTags', array('name'=>urlencode($name),
				'user'=>$userid), false, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbSearchKey($tagid, $name, $userid) {
		$sql = "SELECT id,name FROM *PREFIX*meta_data_keys WHERE tagid=? AND name LIKE ? ORDER BY id";
		$args = array($tagid,$name);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		$result = [];
		if($output->rowCount() > 0){
			while($row=$output->fetchRow()){
				$tag = self::dbSearchTagByID($tagid);
				if(empty($userid) || $tag['owner']==$userid || $tag['public']==1){
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

	public static function dbSearchKeys($name, $userid) {
		$sql = "SELECT id,tagid,name FROM *PREFIX*meta_data_keys WHERE name LIKE ? ORDER BY id";
		$args = array($name);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		if($output->rowCount() > 0){
			while($row=$output->fetchRow()){
				$tag = self::dbSearchTagByID($row['tagid']);
				if($tag['owner']==$userid || $tag['public']==1){
					$result[] = $tag;
				}
			}
			return $result;
		}
		else{
			return false;
		}
	}
	
	public static function searchKeys($name, $userid=null) {
		if(empty($userid)){
			$userid = \OCP\USER::getUser();
		}
		if(empty($name)){
			return array();
		}
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchKeys($name, $userid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('searchKeys', array('name'=>$name,
					'userid'=>$userid),false, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbSearchTagByID($tagid){
		$sql = "SELECT id, name, color, owner, public FROM *PREFIX*meta_data_tags WHERE id=?";
		$args = array($tagid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$result = $resRsrc->fetchRow();
		return $result;
	}
	
	// TODO: ditch this plus ws/searchTagByID.php
	public static function searchTagByID($tagid){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchTagByID($tagid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('searchTagByID',
					array('tagid'=>$tagid),false, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbSearchTagsByIDs($tagids){
		$tagids = array_values(array_filter($tagids, array(__CLASS__, 'isNonEmpty')));
		$sql = "SELECT id, name, color, owner, public FROM *PREFIX*meta_data_tags WHERE FALSE";
		foreach($tagids as $tagid){
			$sql .= " OR id=?";
		}
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($tagids);
		$result = [];
		while($row=$resRsrc->fetchRow()){
			$result[$row['id']] = $row;
		}
		return $result;
	}
	
	public static function searchTagsByIDs($tagids){
		if(empty($tagids)){
			return array();
		}
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchTagsByIDs($tagids);
		}
		else{
			$idarray = array();
			foreach($tagids as $i=>$tagid){
				$idarray['tagid['.$i.']'] = $tagid;
			}
			$result = \OCA\FilesSharding\Lib::ws('searchTagsByIDs', $idarray, true, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbSearchKeyByID($keyid){
		$sql = "SELECT name FROM *PREFIX*meta_data_keys WHERE id=?";
		$args = array($keyid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$result=$resRsrc->fetchRow();
		return $result;
	}
	
	public static function searchKeyByID($keyid){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchKeyByID($keyid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('searchKeyByID', array('keyid'=>$keyid), false,
					true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbSearchKeysByIDs($keyids){
		$keyids = array_values(array_filter($keyids, array(__CLASS__, 'isNonEmpty')));
		$sql = "SELECT id, tagid, name FROM *PREFIX*meta_data_keys WHERE FALSE";
		foreach($keyids as $keyid){
			$sql .= " OR id=?";
		}
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($keyids);
		$result = [];
		while($row=$resRsrc->fetchRow()){
			$result[$row['id']] = $row;
		}
		return $result;
	}
	
	public static function searchKeysByIDs($keyids){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbSearchKeysByIDs($keyids);
		}
		else{
			$idarray = array();
			foreach($tagids as $n=>$keyid){
				$idarray['keyid['.$n.']'] = $keyid;
			}
			$result = \OCA\FilesSharding\Lib::ws('searchKeysByIDs', $idarray, true, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function getTags($tagids) {
		$tags = self::searchTagsByIDs($tagids);
		return $tags;
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
		\OCP\DB::beginTransaction();
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$tags = self::searchTags($name, $userid);
		\OCP\DB::commit();
		$tag = $tags[0];
		self::setTagDisplay($tag['id'], $display);
		return $tags;
	}
	
	public static function newTag($name, $userid, $display, $color, $public){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbNewTag($name, $userid, $display, $color, $public);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('newTag', array('userid'=>$userid,
					'name'=>urlencode($name), 'color'=>$color, 'display'=>$display, 'public'=>$public),
					false, true, null, 'meta_data');
		}
		return $result;
	}
	
	public static function dbDeleteTag($tagid, $userid){
		$sql = 'DELETE FROM *PREFIX*meta_data_tags WHERE id=? AND owner=?';
		$args = array($tagid, $userid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return $resRsrc;
	}
	
	private static function dbDeleteDocTags($tagid, $userid){
		$sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE tagid=?';
		$args = array($tagid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return $resRsrc;
	}
	
	private static function dbDeleteDocKeys($tagid, $userid){
		$sql = 'DELETE FROM *PREFIX*meta_data_docKeys WHERE tagid=?';
		$args = array($tagid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return $resRsrc;
	}
	
	public static function deleteTag($tagid, $userid){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbDeleteTag($tagid, $userid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('deleteTag', array(
					'tagid'=>$tagid, 'userid'=>$userid),false, true, null, 'meta_data');
		}
		self::dbDeleteDocTags($tagid, $userid);
		self::dbDeleteDocKeys($tagid, $userid);
		return $result;
	}
	
	public static function dbUpdateTag($id, $name, $description, $color, $public) {
		$resRsrc = true;
		if(self::isNonEmpty($name) || self::isNonEmpty($description) || self::isNonEmpty($color) || self::isNonEmpty($public)){
			$sql = 'UPDATE *PREFIX*meta_data_tags SET '.
			(self::isNonEmpty($name)?'name=?, ':'').(self::isNonEmpty($description)?'description=?, ':'').
			(self::isNonEmpty($color)?'color=?, ':'').
			(self::isNonEmpty($public)?'public=?, ':'').'WHERE id=?';
			$sql = str_replace('=?, WHERE', '=? WHERE', $sql);
			$sql = str_replace('SET ,', 'SET', $sql);
			$args = array($name, $description, $color, $public, $id);
			$args = array_values(array_filter($args, array(__CLASS__, 'isNonEmpty')));
			\OCP\Util::writeLog('meta_data', 'SQL: '.$sql. ', ARGS: '.serialize($args).' --> '.count($args), \OC_Log::WARN);
			$query = \OCP\DB::prepare($sql);
			$resRsrc = $query->execute($args);
		}
		return $resRsrc;
	}
	
	public static function updateTag($id, $name, $description, $color, $public, $visible) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbUpdateTag($id, $name, $description, $color, $public);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('updateTag', array('id'=>$id,
					'name'=>urlencode($name), 'description'=>urlencode($description), 'color'=>$color, 'public'=>$public),
					false, true, null, 'meta_data');
		}
		\OCP\Util::writeLog('meta_data', 'RESULT: '.serialize($result).':'.\OCP\DB::isError($result), \OC_Log::WARN);
		return $result && self::setTagDisplay($id, $visible);
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
		$sql = "SELECT id, fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE value LIKE ?";
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
	
	public static function dbLoadFileKeys($fileid, $tagid){
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
	
	public static function loadFileKeys($fileid, $tagid, $owner) {
		if(empty($owner) || !\OCP\App::isEnabled('files_sharding')){
			return self::dbLoadFileKeys($fileid, $tagid);
		}
		$server = \OCA\FilesSharding\Lib::getServerForUser($owner, true);
		$result = \OCA\FilesSharding\Lib::ws('loadFileKeys', array('fileid'=>$fileid,
			'tagid'=>$tagid), false, true, $server, 'meta_data');
		return $result;
	}
	
	public static function dbGetTaggedFiles($tagid, $userid = null){
		if(!empty($userid) && $userid!=\OCP\USER::getUser()){
			$user_id = self::switchUser($userid);
		}
		$files = array();
		$sql = "SELECT fileid FROM *PREFIX*meta_data_docTags WHERE tagid = ?";
		$args = array($tagid);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		$data = null;
		while($row=$output->fetchRow()){
			$filepath = \OC\Files\Filesystem::getpath($row['fileid']);
			if(empty($filepath)){
				continue;
			}
			$fileInfo = \OC\Files\Filesystem::getFileInfo($filepath);
			if(empty($fileInfo)){
				continue;
			}
			//$files[] = $fileInfo;
			\OCP\Util::writeLog('meta_data', 'Adding file '.$row['fileid'].'-->'.$filepath, \OC_Log::WARN);
			$data = $fileInfo->getData();
			$data['type'] = $fileInfo->getType();
			//$data['storage'] = $fileInfo->getStorage();
			$data['path'] = $fileInfo->getpath();
			$data['internalPath'] = $fileInfo->getInternalPath();
			$tagArr = self::dbGetFileTags(array($row['fileid']));
			$data['tags'] = $tagArr[$row['fileid']];
			$files[] = $data;
		}
		//$result = \OCA\Files\Helper::formatFileInfos($files);
		if(isset($user_id) && $user_id){
			self::restoreUser($user_id);
		}
		\OCP\Util::writeLog('meta_data', 'Returning '.serialize($files), \OC_Log::DEBUG);
		//return $result;
		return $files;
	}

	public static function getTaggedFiles($tagid, $userid = null, $sortAttribute = '', $sortDescending = false){
		$storage = \OC\Files\Filesystem::getStorage('/');
		$results = array();
		$data = self::dbGetTaggedFiles($tagid, $userid);
		foreach($data as $row){
			$info = new \OC\Files\FileInfo($row['path'], $storage, $row['internalPath'], $row);
			$results[] = $info;
		}
		if($sortAttribute !== '') {
			$results = \OCA\Files\Helper::sortFiles($results, $sortAttribute, $sortDescending);
		}
		
		if(!\OCP\App::isEnabled('files_sharding')){
			return $results;
		}
		
		$sharedItems = \OCA\FilesSharding\Lib::getItemsSharedWithUser($userid);
		$serverUsers = \OCA\FilesSharding\Lib::getServerUsers($sharedItems);
		\OCP\Util::writeLog('meta_data', 'Server users '.serialize($serverUsers), \OC_Log::WARN);
		$allServers = \OCA\FilesSharding\Lib::getServersList();
		$cache = $storage->getCache();
		foreach($allServers as $server){
			if(!array_key_exists($server['id'], $serverUsers)){
				continue;
			}
			if(!isset($server['internal_url']) && !empty($server['internal_url'])){
				continue;
			}
			\OCP\Util::writeLog('meta_data', 'Searching server '.$server['internal_url'], \OC_Log::WARN);
			foreach($serverUsers[$server['id']] as $owner){
				$matches = \OCA\FilesSharding\Lib::ws('getTaggedFiles', Array('userid'=>$owner, 'tagid'=>$tagid),
						false, true, $server['internal_url'], 'meta_data');
				$res = array();
				foreach($matches as $match){
					foreach($sharedItems as $item){
						if(isset($item['fileid']) && isset($match['fileid']) && $item['fileid']==$match['fileid']){
							\OCP\Util::writeLog('meta_data', 'Matched '.$match['path'].':'.$item['owner_path'].' --> '.$server['internal_url'].
								' --> '.$owner, \OC_Log::WARN);
							$match['server'] = $server['internal_url'];
							$match['owner'] = $owner;
							$info = new \OC\Files\FileInfo($match['path'], $storage, $match['internalPath'], $match);
							if(!in_array($info, $res)){
								$res[] = $info;
							}
							continue;
						}
						// Check if match is in a shared folder or subfolders thereof
						if($cache->getMimetype($item['mimetype'])==='httpd/unix-directory' && isset($item['owner_path'])){
							$fullpath = '/'.$owner.'/files'.$item['owner_path'].'/';
							$len = strlen($fullpath);
							if(substr($match['path'], 0, $len)===$fullpath){
								\OCP\Util::writeLog('meta_data', 'Matching '.substr($match['path'], 0, $len).':'.$fullpath.' --> '.$server['internal_url'].
										' --> '.$owner, \OC_Log::WARN);
								$match['server'] = $server['internal_url'];
								$match['owner'] = $owner;
								$info = new \OC\Files\FileInfo($match['path'], $storage, $match['internalPath'], $match);
								if(!in_array($info, $res)){
									$res[] = $info;
								}
								continue;
							}
						}
					}
				}
				$results = array_merge($results, $res);
			}
		}
		if($sortAttribute !== '') {
			$results = \OCA\Files\Helper::sortFiles($results, $sortAttribute, $sortDescending);
		}
		return $results;
	}
	
	// TODO: this should operate on a list of fileids
	public static function getFileTags($fileids, $owner=null){
		$result = self::dbGetFileTags($fileids);
		if(empty($owner) || !\OCP\App::isEnabled('files_sharding')){
			return $result;
		}
		\OCP\Util::writeLog('meta_data', 'DB file tags: '.implode(', ', $fileids).'-->'.serialize($result), \OC_Log::WARN);
		//$sharedItem = \OCA\FilesSharding\Lib::ws('getItemSharedWithBySource', Array('itemType' => 'file',
		//		'user_id'=>\OCP\USER::getUser(), 'itemSource'=>$fileid));
		//\OCP\Util::writeLog('meta_data', 'Shared item '.serialize($sharedItem), \OC_Log::WARN);
		$server = \OCA\FilesSharding\Lib::getServerForUser($owner, true);
		$idarray = array();
		foreach($fileids as $n=>$fileid){
			$idarray['fileid['.$n.']'] = $fileid;
		}
		$args = array_merge(array('userid'=>$owner), $idarray);
		$tags = \OCA\FilesSharding\Lib::ws('getFileTags', $args,
				false, true, $server, 'meta_data');
		\OCP\Util::writeLog('meta_data', 'WS file tags: '.implode(', ', $fileids).'-->'.serialize($tags), \OC_Log::WARN);
		$result = array_unique(array_merge($result, $tags));
		return $result;
	}
	
	public static function dbGetFileTags($fileids){
		$result = array();
		$fileids = array_values(array_filter($fileids, array(__CLASS__, 'isNonEmpty')));
		$sql = "SELECT fileid, tagid FROM *PREFIX*meta_data_docTags WHERE FALSE";
		foreach($fileids as $fileid){
			$sql .= " OR fileid=?";
		}
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($fileids);
		$result = [];
		while($row=$output->fetchRow()){
			if(isset($result[$row['fileid']])){
				array_push($result[$row['fileid']], $row['tagid']);
			}
			else{
				$result[$row['fileid']] = array($row['tagid']);
			}
		}
		return $result;
	}
	
	public static function dbNewKey($tagid, $keyname) {
		if(empty($keyname) || trim($keyname) === '') {
			return false;
		}
		$sql = "INSERT INTO *PREFIX*meta_data_keys (tagid,name) VALUES (?,?)";
		$args = array($tagid,$keyname);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$key = self::searchKey($tagid, $keyname);
		return $key;
	}

	public static function newKey($tagid, $keyname){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbNewkey($tagid, $keyname);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('newKey', array(
					'tagid'=>$tagid, 'keyname'=>$keyname),
					false, true, null, 'meta_data');
		}
		return $result;
	}

	public static function dbAlterKey($tagid, $keyid, $keyname, $userid) {
		$sql = 'UPDATE *PREFIX*meta_data_keys SET name=? WHERE tagid=? AND id=?';
		$args = array($keyname, $tagid,$keyid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return $resRsrc;
	}
	
	public static function alterKey($tagid, $keyid, $keyname, $userid){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbAlterKey($tagid, $keyid, $keyname, $userid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('alterKey', array('userid'=>$userid,
					'tagid'=>$tagid,  'keyid'=>$keyid, 'keyname'=>$keyname),
					false, true, null, 'meta_data');
		}
		return $result;
	}

	public static function dbDeleteKeys($tagid, $keyid){
		$sql = 'DELETE FROM *PREFIX*meta_data_keys WHERE tagid=? AND id LIKE ?';
		$args = array($tagid, $keyid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return $resRsrc;
	}
	
	public static function deleteKeys($tagid, $keyid){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbDeleteKeys($tagid, $keyid);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('deleteKeys', array(
					'tagid'=>$tagid,  'keyid'=>$keyid),
					false, true, null, 'meta_data');
		}
		return $result;
	}

	/// Local stuff: A user can only add or modify meta-data on files he owns.
	///              Thus, adding or modifying file metadata, happens locally
	///              on the user's slave server the.
	
	
	public static function updateFileTags($tagid, $userid, $fileids){
		// We allow $fileid to be a colon-separated list of ids
		if($fileids && strpos($fileids, ':')>0){
			$fileidArr = explode(':', $fileids);
		}
		else{
			$fileidArr = array($fileids);
		}
		$res = array();
		foreach($fileidArr as $fileid){
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
				$res = array_merge($res, $result);
			}
		}
		return $result;
	}

	public static function removeFileTag($tagid, $fileids){
		// We allow $fileid to be a colon-separated list of ids
		if($fileids && strpos($fileids, ':')>0){
			$fileidArr = explode(':', $fileids);
		}
		else{
			$fileidArr = array($fileids);
		}
		$output = true;
		foreach($fileidArr as $fileid){
			$sql = 'DELETE FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
			$args = array($fileid, $tagid);
			$query = \OCP\DB::prepare($sql);
			$output = $output && $query->execute($args);
		}
		return $output;
	}

	public static function removeFileKey($tagid, $fileid, $keyid){
			$sql = 'DELETE FROM *PREFIX*meta_data_docKeys WHERE fileid LIKE ? AND tagid=? AND keyid=?';
			$args = array($fileid, $tagid, $keyid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
	}

	public static function updateFileKeys ($fileids, $tagid, $keyid, $value){
		// We allow $fileid to be a colon-separated list of ids
		$fileidArr = explode(':', $fileids);
		$res = array();
		foreach($fileidArr as $fileid){
			$sql = 'SELECT tagid FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND tagid=? and keyid=?';
			$result = array();
			$args = array($fileid, $tagid, $keyid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
			while($row=$output->fetchRow()){
				$result[] = $row;
			}
			if(count($result) == 0 ) {
				$sql = 'INSERT INTO *PREFIX*meta_data_docKeys (fileid,tagid,keyid,value) VALUES (?,?,?,?)';
				$args = array($fileid, $tagid, $keyid, $value);
				$query = \OCP\DB::prepare($sql);
				\OCP\Util::writeLog('meta_data', 'ARGS: '.implode('#', $args).'-->'.sizeof($args), \OC_Log::WARN);
				$resRsrc = $query->execute($args);
				if($resRsrc){
					$res[] = array('tagid'=>$tagid, 'keyid'=>$keyid, 'fileid'=>$fileid, 'value'=>$value);
				}
			}
			else if (count($result) == 1 ) {
				$sql = 'UPDATE *PREFIX*meta_data_docKeys SET value=? WHERE fileid=? AND keyid=? AND tagid=?';
				$args = array($value, (int) $fileid, $keyid, $tagid);
				$query = \OCP\DB::prepare($sql);
				\OCP\Util::writeLog('meta_data', 'ARGS1: '.implode('#', $args).'-->'.sizeof($args), \OC_Log::WARN);
				$resRsrc = $query->execute($args);
				if($resRsrc){
					$res[] = array('tagid'=>$tagid, 'keyid'=>$keyid, 'fileid'=>$fileid, 'value'=>$value);
				}
			}
		}
		return $res;
	}

	public static function getMimeType($mtype){
		$sql = "SELECT mimetype FROM *PREFIX*mimetypes WHERE id = ?";
		$args = array($mtype);
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($args);
		return $output->fetchRow();
	}

	private static function isNonEmpty($val) {
		return isset($val) && (!empty($val) || $val==='0' || $val===0);
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
			if (isset($i['owner'])) {
				$entry['owner'] = $i['owner'];
		}
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
		if(!self::isNonEmpty($value)){
			\OCP\Util::writeLog('meta_data', 'No value for '.$tagid, \OC_Log::WARN);
			return true;
		}
		\OCP\Util::writeLog('meta_data', 'Setting '.$tagid.' --> '.$value, \OC_Log::WARN);
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
