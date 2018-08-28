<?php

namespace OCA\meta_data;

class MetaData implements \JsonSerializable {
	public $metadata;
	public $keyvals;
	public function __construct($keyvals=array()) {
		$this->keyvals = $keyvals;
		if(!empty($keyvals)){
			$this->metadata = array();
			foreach($keyvals as $keyID=>$val){
				$key = \OCA\meta_data\Tags::searchKeyByID($keyID);
				\OCP\Util::writeLog('meta_data', 'KEY: '.$keyID.'-->'.$key['name'], \OC_Log::WARN);
				if(!empty($key)){
					if($key['type']=='json'){
						if(json_decode($val, false)!=null){
							$this->metadata[$key['name']] = json_decode($val, false);
							\OCP\Util::writeLog('meta_data', 'Key type JSON. Encoded '.
									$val.' --> '.serialize($this->metadata[$key['name']]), \OC_Log::WARN);
						}
					}
					elseif(!empty($val)){
						$this->metadata[$key['name']] = $val;
					}
				}
			}
		}
	}
	public function getValue($keyName){
		return empty($this->metadata[$keyName])?null:$this->metadata[$keyName];
	}
	public function jsonSerialize(){
		return $this->metadata;
	}
}

class FileTag implements \JsonSerializable {
	public $fileid;
	/*optional and volatile*/
	public $filename;
	public $tagid;
	/*optional and volatile*/
	public $tagname;
	public $keyvals;
	public function __construct($fileid, $tagid, $keyvals=array()) {
		$this->fileid = $fileid;
		$this->tagid = $tagid;
		$this->keyvals = $keyvals;
	}
	public function setFileID($fileid){
		$this->fileid = $fileid;
	}
	public function setFileName($filename){
		$this->filename = $filename;
	}
	public function setTagID($tagid){
		$this->tagid = $tagid;
	}
	public function setTagName($tagname){
		$this->tagname = $tagname;
	}
	public function addKeyVal($keyid, $val) {
		if(!empty($keyid)){
			$this->keyvals[$keyid] = $val;
		}
	}
	public function removeKeyVal($keyid){
		unset($this->keyvals[$keyid]);
	}
	public function getKeys(){
		return array_keys($this->keyvals);
	}
	public function getValue($keyid) {
		return $this->keyvals[$keyid];
	}
	
	public function getMetadata(){
		$metadata = new \OCA\meta_data\Metadata($this->keyvals);
		return $metadata;
	}
	
	public function jsonSerialize(){
		$ret = ['fileid' => $this->fileid, 'tagid' => $this->tagid];
		\OCP\Util::writeLog('meta_data', 'Serializing '. serialize($ret), \OC_Log::WARN);
		if(!empty($this->keyvals)){
			$ret['keyvals'] = $this->getMetadata();
			$ret['metadata'] = $this->getMetadata();
		}
		if(!empty($this->filename)){
			$ret['filename'] = $this->filename;
		}
		if(!empty($this->tagname)){
			$ret['tagname'] = $this->tagname;
		}
		return $ret;
	}
}

class FileTags implements \JsonSerializable {
	public $fileid;
	public $filetags;
	public function __construct($fileid, $filetags = array()) {
		$this->fileid = $fileid;
		$this->filetags = $filetags;
	}
	public function addFileTag(\OCA\meta_data\FileTag $fileTag){
		if(!empty($fileTag)){
			$this->filetags[$fileTag->tagid] = $fileTag;
		}
	}
	public function getFileTag($tagID) {
		return $this->filetags[$tagID];
	}
	public function removeFileTag($tagID){
		unset($this->filetags[$tagID]);
	}
	public function getFileTags() {
		return $this->filetags;
	}
	public function setFileID($fileid){
		$this->fileid = $fileid;
		foreach($this->filetags as &$fileTag){
			$fileTag->setFileID($fileid);
		}
	}
	public function jsonSerialize(){
		return [
		'fileid' => $this->fileid,
		'filetags' => $this->filetags,
		];
	}
}

class FilesTags implements \JsonSerializable {
	public $filestags;
	public function __construct($filesTags) {
		$this->filestags = $filesTags;
	}
	public function addFileTags(\OCA\meta_data\FileTags $fileTags){
		$this->filestags[$fileTags->fileid] = $fileTags;
	}
	public function jsonSerialize(){
		return empty($this->filestags)?new \ArrayObject([]):$this->filestags;
	}
}


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
	
	public static function getTagID($name, $userid){
		$tags = self::searchTags($name, $userid);
		if(empty($tags)){
			return '';
		}
		elseif(sizeof($tags)>1){
			\OCP\Util::writeLog('meta_data', 'More than one tag with name '.$name.' for user '.$userid, \OC_Log::ERROR);
			return '';
		}
		else{
			return $tags[0]['id'];
		}
	}
	
	public static function dbSearchKey($tagid, $name, $userid) {
		$sql = "SELECT * FROM *PREFIX*meta_data_keys WHERE tagid=? AND name LIKE ? ORDER BY id";
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
	
	public static function getKeyID($tagid, $name, $userid){
		$keys = self::searchKey($tagid, $name, $userid);
		if(empty($keys)){
			return '';
		}
		elseif(sizeof($keys)>1){
			\OCP\Util::writeLog('meta_data', 'More than one key with name '.$name.' for tag '.$tagid, \OC_Log::ERROR);
			return '';
		}
		else{
			return $keys[0]['id'];
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
		$sql = "SELECT * FROM *PREFIX*meta_data_keys WHERE id=?";
		$args = array($keyid);
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$result = $resRsrc->fetchRow();
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
			foreach($keyids as $n=>$keyid){
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
	
	public static function dbNewTag($name, $userid, $display=0, $color="color-1", $public=0){
		if(trim($name) === '') {
			\OCP\Util::writeLog('meta_data', 'Need tag name', \OC_Log::ERROR);
			return false;
		}
		if(count(self::searchTags($name, $userid)) != 0 ){
			\OCP\Util::writeLog('meta_data', 'Tag exists: '.$name, \OC_Log::ERROR);
			return false;
		}
		$sql = "INSERT INTO *PREFIX*meta_data_tags (name,owner,public,color) VALUES (?,?,?,?)";
		$args = array($name, $userid, $public, $color);
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
	
	public static function dbUpdateTag($id, $name, $description, $color, $public, $owner) {
		$resRsrc = true;
		if(self::isNonEmpty($name) || self::isNonEmpty($description) || self::isNonEmpty($color) ||
				self::isNonEmpty($public) || self::isNonEmpty($owner)){
			$sql = 'UPDATE *PREFIX*meta_data_tags SET '.
			(self::isNonEmpty($name)?'name=?, ':'').
			(self::isNonEmpty($description)?'description=?, ':'').
			(self::isNonEmpty($color)?'color=?, ':'').
			(self::isNonEmpty($public)?'public=?, ':'').
			(self::isNonEmpty($owner)?'owner=?, ':'').
			'WHERE id=?';
			$sql = str_replace('=?, WHERE', '=? WHERE', $sql);
			$sql = str_replace('SET ,', 'SET', $sql);
			$args = array($name, $description, $color, $public, $owner, $id);
			$args = array_values(array_filter($args, array(__CLASS__, 'isNonEmpty')));
			\OCP\Util::writeLog('meta_data', 'SQL: '.$sql. ', ARGS: '.serialize($args).' --> '.count($args), \OC_Log::WARN);
			$query = \OCP\DB::prepare($sql);
			$resRsrc = $query->execute($args);
		}
		return $resRsrc;
	}
	
	public static function updateTag($id, $name, $description, $color, $public, $owner, $visible) {
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbUpdateTag($id, $name, $description, $color, $public, $owner);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('updateTag', array('id'=>$id,
					'name'=>urlencode($name), 'description'=>urlencode($description), 'color'=>$color,
					'public'=>$public, 'owner'=>$owner),
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
	
	public static function searchMetadata($val, $userid, $tagid='', $keyid='', $keyvals=[]) {
		$result = array();
		if(empty($tagid) && empty($keyid)){
			if(empty($val)){
				$sql = "SELECT id, fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE TRUE";
				$args = array();
			}
			else{
				$sql = "SELECT id, fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE value LIKE ?";
				$args = array('%'.$val.'%');
			}
		}
		elseif(!empty($tagid) && empty($keyid)){
			$sql = "SELECT id, fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE value LIKE ? AND tagid = ?";
			$args = array('%'.$val.'%', $tagid);
		}
		elseif(empty($tagid) &&! empty($keyid)){
			$sql = "SELECT id, fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE value LIKE ? AND keyid = ?";
			$args = array('%'.$val.'%', $keyid);
		}
		else{
			$sql = "SELECT id, fileid, tagid, keyid, value FROM *PREFIX*meta_data_docKeys WHERE value LIKE ? AND tagid = ? AND keyid = ?";
			$args = array('%'.$val.'%', $tagid, $keyid);
		}
		if(!empty($tagid) && !empty($keyvals)){
			$sql .= " AND tagid LIKE ?";
			$args[] = $tagid;
			foreach($keyvals as $key=>$val){
				$keys = self::searchKey($tagid, $key, $userid);
				if(empty($keys)){
					continue;
				}
				if(sizeof($keys)>1){
					\OCP\Util::writeLog('meta_data', 'WARNING: multiple key matches for key '.$key.', tag '.$tagid, \OC_Log::INFO);
				}
				$sql .= " AND keyid LIKE ? AND value LIKE ?";
				$args[] = $keys[0]['id'];
				$args[] = $val;
			}
		}
		\OCP\Util::writeLog('meta_data', 'SQL: '.$sql.' --> '.implode(' // ', $args), \OC_Log::WARN);
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
	
	public static function getFilesWithMetadata($val, $userid, $tagid='', $keyid=''){
		$data = self::searchMetadata($val, $userid, $tagid, $keyid);
		foreach($data as $row){
			$storage = \OC\Files\Filesystem::getStorage('/');
			$info = new \OC\Files\FileInfo($row['path'], $storage, $row['internalPath'], $row);
			if(empty($info)){
				// TODO: test that this works
				$storage = \OCP\Files::getStorage('user_group_admin');
				$info = new \OC\Files\FileInfo($row['path'], $storage, $row['internalPath'], $row);
			}
			if(!empty($info)){
				$results[] = $info;
			}
		}
		if($sortAttribute !== '') {
			$results = \OCA\Files\Helper::sortFiles($results, $sortAttribute, $sortDescending);
		}
		return $results;
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
		$fs = null;
		while($row=$output->fetchRow()){
			$filepath = \OC\Files\Filesystem::getpath($row['fileid']);
			$group = null;
			if(empty($filepath) && \OCP\App::isEnabled('user_group_admin')){
				// Not found in files/, try user_group_admin/
				if(empty($fs)){
					$fs = \OCP\Files::getStorage('user_group_admin');
				}
				$filepath = $fs->getPath($row['fileid']);
				// Now get the group name
				if(!empty($filepath)){
					$gIndex = strpos($filepath, '/', 1);
					$group = $gIndex>0?substr($filepath, 1, $gIndex-1):'';
					\OCP\Util::writeLog('meta_data', 'Group: '.$row['fileid'].'-->'.$filepath.'-->'.$group, \OC_Log::INFO);
				}
			}
			if(empty($filepath)){
				\OCP\Util::writeLog('meta_data', 'No path info for '.$row['fileid'].'-->'.$filepath, \OC_Log::INFO);
				continue;
			}
			if(!empty($group)){
				$fileInfo = $fs->getFileInfo($filepath);
			}
			else{
				$fileInfo = \OC\Files\Filesystem::getFileInfo($filepath);
			}
			if(empty($fileInfo)){
				continue;
			}
			//$files[] = $fileInfo;
			\OCP\Util::writeLog('meta_data', 'Adding file '.$row['fileid'].'-->'.$filepath, \OC_Log::INFO);
			$data = $fileInfo->getData();
			$data['type'] = $fileInfo->getType();
			//$data['storage'] = $fileInfo->getStorage();
			//$data['path'] = $fileInfo->getPath();
			$data['path'] = $fileInfo->getInternalPath();
			//$data['internalPath'] = $fileInfo->getInternalPath();
			$tagArr = self::dbGetFileTags(array($row['fileid']));
			$data['tags'] = $tagArr[$row['fileid']];
			if(!empty($group)){
				$data['group'] = $group;
				\OCP\Util::writeLog('meta_data', 'GROUP: '.$filepath.'-->'.$group, \OC_Log::WARN);
			}
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
	
	public static $keyArr;

	public static function getTaggedFiles($tagid, $userid = null,
			$sortAttribute = '', $sortDescending = false, $keyVals = []){
		$results = array();
		$data = self::dbGetTaggedFiles($tagid, $userid);
		$storage = \OC\Files\Filesystem::getStorage('/');
		foreach($data as $row){
			$storage = \OC\Files\Filesystem::getStorage('/');
			$info = new \OC\Files\FileInfo($row['path'], $storage, $row['internalPath'], $row);
			if(empty($info)){
				// TODO: test that this works
				$storage = \OCP\Files::getStorage('user_group_admin');
				$info = new \OC\Files\FileInfo($row['path'], $storage, $row['internalPath'], $row);
			}
			$badMatch = false;
			if(!empty($info) && !empty($keyVals)){
				$fileKeyVals = self::dbLoadFileKeys($info['fileid'], $tagid);
				$badMatch = false;
				foreach($keyVals as $keyName=>$val){
					Tags::$keyArr = self::searchKey($tagid, $keyName, $userid);
					$fileValArr = array_values(array_filter($fileKeyVals,
							function($row){return $row['keyid']==Tags::$keyArr[0]['id'];
					}));
					$fileVal = empty($fileValArr)?'':$fileValArr[0]['value'];
					\OCP\Util::writeLog('meta_data', 'Matching key: '.Tags::$keyArr[0]['id'].'-->'.
							$val.'<->'.$fileVal, \OC_Log::WARN);
					if(!empty(Tags::$keyArr) && !empty(Tags::$keyArr[0]['id']) &&
							$val != $fileVal){
						$badMatch = true;
						break;
					}
				}
			}
			if(empty($info) || $badMatch){
				continue;
			}
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
										' --> '.$owner, \OC_Log::DEBUG);
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
	
	public static function getFileTags($fileids, $owner=null, $fileowners=null){
		$result = self::dbGetFileTags($fileids);
		\OCP\Util::writeLog('meta_data', 'DB file tags: '.$owner.':'.serialize($fileowners).'-->'.
				implode(', ', $fileids).'-->'.serialize($result), \OC_Log::DEBUG);
		if(empty($owner) && empty($fileowners) || !\OCP\App::isEnabled('files_sharding')){
			return $result;
		}
		//$sharedItem = \OCA\FilesSharding\Lib::ws('getItemSharedWithBySource', Array('itemType' => 'file',
		//		'user_id'=>\OCP\USER::getUser(), 'itemSource'=>$fileid));
		//\OCP\Util::writeLog('meta_data', 'Shared item '.serialize($sharedItem), \OC_Log::WARN);
		if(!empty($owner) && empty($fileowners)){
			$fileowners = array_fill(0, sizeof($fileids), $owner);
		}
		foreach($fileids as $n=>$fileid){
			$idarray['fileid['.$n.']'] = $fileid;
		}
		$servers = array();
		foreach($fileowners as $owner){
			$servers[] = \OCA\FilesSharding\Lib::getServerForUser($owner, true);
		}
		\OCP\Util::writeLog('meta_data', 'SERVERS: '.serialize($fileowners).'-->'.serialize($servers), \OC_Log::WARN);
		$servers = array_unique($servers);
		foreach($servers as $server){
			$tags = \OCA\FilesSharding\Lib::ws('getFileTags', $idarray, false, true, $server, 'meta_data');
			\OCP\Util::writeLog('meta_data', 'WS file tags: '.implode(', ', $fileids).'-->'.serialize($result).
					'-->'.serialize($tags),
					\OC_Log::WARN);
			if(empty($tags)){
				continue;
			}
			$result = empty($result)?$tags:$result+$tags;
		}
		\OCP\Util::writeLog('meta_data', 'All file tags: '.serialize($result), \OC_Log::WARN);
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
	
	// Duplicated from files_sharding
	private static function getFilePath($id, $owner=null) {
		if(isset($owner) && $owner!=\OCP\USER::getUser()){
			$user_id = self::switchUser($owner);
		}
		$ret = \OC\Files\Filesystem::getpath($id);
		if(isset($user_id) && $user_id){
			self::restoreUser($user_id);
		}
		return $ret;
	}
	
	/**
	 * Return all file/tag/key info for a user/file/tag.
	 * Optionally, massage the output tag objects to use key names instead of IDs.
	 */
	public static function getUserFileTags($user_id, $fileids=null, $tagids=null, $useKeyNames=false){
		if(empty($user_id)){
			return new \OCA\meta_data\FilesTags();
		}
		if(!empty($fileids)){
			$fileids = array_values(array_filter($fileids, array(__CLASS__, 'isNonEmpty')));
			$sql = "SELECT fileid, tagid FROM *PREFIX*meta_data_docTags WHERE FALSE";
			foreach($fileids as $fileid){
				$sql .= " OR fileid=?";
			}
		}
		else{
			$fileids = array();
			$sql = "SELECT fileid, tagid FROM *PREFIX*meta_data_docTags";
		}
		$query = \OCP\DB::prepare($sql);
		$output = $query->execute($fileids);
		// A list of FileTags objects, one object for each tagged file.
		// Each object in the list contains all tag/key/value info for the given file
		$fileTagsArr = array();
		while($row=$output->fetchRow()){
			// If tags are given, match
			if(!empty($tagids) && !in_array($row['tagid'], $tagids)){
				continue;
			}
			// Check if file is owned by user
			$path = self::getFilePath($row['fileid'], $user_id);
			if(empty($path)){
				continue;
			}
			// Create new FileTag object
			$fileTag = new \OCA\meta_data\FileTag($row['fileid'], $row['tagid']);
			$fileTag->setFileName($path);
			$tag = self::searchTagByID($row['tagid']);
			$tagname = $tag['name'];
			$fileTag->setTagName($tagname);
			// Add key/values to the FileTag object
			$keyVals = self::dbLoadFileKeys($row['fileid'], $row['tagid']);
			foreach($keyVals as $keyVal){
				$fileTag->addKeyVal(/*use name instead of id*/
				/*self::searchKeyByID($keyVal['keyid'])['name']'*/
					$keyVal['keyid'],
					$keyVal['value']);
			}
			if(empty($fileTagsArr[$row['fileid']])){
				// Add new FileTags object to the result list
				$fileTagsArr[$row['fileid']] = new \OCA\meta_data\FileTags($row['fileid']);
			}
			// Add the FileTag object to the FileTags object
			$fileTagsArr[$row['fileid']]->addFileTag($fileTag);
		}
		return new \OCA\meta_data\FilesTags($fileTagsArr);
	}
	
	/**
	 * Insert array of FileTags objects in the local database.
	 * @param $user_id
	 * @param $fileTagsArr
	 */
	private static function dbInsertUserFileTags($user_id, $fileTagsArr){
		$ret = true;
		foreach($fileTagsArr as $fileTags){
			foreach($fileTags->filetags as $fileTag){
				\OCP\Util::writeLog('meta_data', 'Updating tag: '.serialize($fileTag), \OC_Log::WARN);
				$ret = self::updateFileTag($fileTag->{'tagid'}, $user_id, $fileTag->{'fileid'}) && $ret;
				if(!empty($fileTag->keyvals)){
					foreach((Array) $fileTag->keyvals as $keyID=>$val){
						$ret = self::updateFileKeyVal($fileTag->fileid, $fileTag->tagid, $keyID, $val) && $ret;
					}
				}
			}
		}
		return $ret;
	}
	
	public static function dbNewKey($tagid, $keyname, $type=null, $controlledvalues=null) {
		if(empty($keyname) || trim($keyname) === '') {
			return false;
		}
			if(!empty($type)){
			if($type=='""'){
				$type = '';
			}
			$sql .= ', type=?';
			$args[] = $type;
		}
		if(empty($controlledvalues) && empty($type)){
			$sql = "INSERT INTO *PREFIX*meta_data_keys (tagid,name) VALUES (?,?)";
			$args = array($tagid, $keyname);
		}
		elseif(empty($type) && !empty($controlledvalues)){
			if($controlledvalues=='""'){
				$controlledvalues = '';
			}
			$sql = "INSERT INTO *PREFIX*meta_data_keys (tagid,name, allowed_values) VALUES (?,?,?)";
			$args = array($tagid,$keyname, $controlledvalues);
		}
		elseif(!empty($type) && empty($controlledvalues)){
			if($type=='""'){
				$type = '';
			}
			$sql = "INSERT INTO *PREFIX*meta_data_keys (tagid,name, type) VALUES (?,?,?)";
			$args = array($tagid, $keyname, $type);
		}
		else{
			if($controlledvalues=='""'){
				$controlledvalues = '';
			}
			if($type=='""'){
				$type = '';
			}
			$sql = "INSERT INTO *PREFIX*meta_data_keys (tagid,name, type, allowed_values) VALUES (?,?,?,?)";
			$args = array($tagid,$keyname, $type, $controlledvalues);
		}
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		$key = self::searchKey($tagid, $keyname);
		return $key;
	}

	public static function newKey($tagid, $keyname, $type='', $controlledvalues=''){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbNewkey($tagid, $keyname, $type, $controlledvalues);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('newKey', array(
					'tagid'=>$tagid, 'keyname'=>$keyname, 'type'=>$type,
					'controlledvalues'=>$controlledvalues),
					false, true, null, 'meta_data');
		}
		return $result;
	}

	public static function dbAlterKey($tagid, $keyid, $keyname, $userid,
			$type=null, $controlledvalues=null) {
		$sql = 'UPDATE *PREFIX*meta_data_keys SET name=?';
		$args = array($keyname);
		if(!empty($type)){
			if($type=='""'){
				$type = '';
			}
			$sql .= ', type=?';
			$args[] = $type;
		}
		if(!empty($controlledvalues)){
			if($controlledvalues=='""'){
				$controlledvalues = '';
			}
			$sql .= ', allowed_values=?';
			$args[] = $controlledvalues;
		}
		$sql .= ' WHERE tagid=? AND id=?';
		$args[] = $tagid;
		$args[] = $keyid;
		$query = \OCP\DB::prepare($sql);
		$resRsrc = $query->execute($args);
		return $resRsrc;
	}
	
	public static function alterKey($tagid, $keyid, $keyname, $userid,
			$type='', $controlledvalues=''){
		if(!\OCP\App::isEnabled('files_sharding') || \OCA\FilesSharding\Lib::isMaster()){
			$result = self::dbAlterKey($tagid, $keyid, $keyname, $userid, $type, $controlledvalues);
		}
		else{
			$result = \OCA\FilesSharding\Lib::ws('alterKey', array('userid'=>$userid,
					'tagid'=>$tagid,  'keyid'=>$keyid, 'keyname'=>$keyname,
					'controlledvalues'=>$controlledvalues, 'type'=>$type),
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
	
	
	public static function updateFileTag($tagid, $userid, $fileids){
		// We allow $fileid to be a colon-separated list of ids
		if($fileids && strpos($fileids, ':')>0){
			$fileidArr = explode(':', $fileids);
		}
		else{
			$fileidArr = array($fileids);
		}
		$ret = true;
		foreach($fileidArr as $fileid){
			$result = array();
			$sql = 'SELECT * FROM *PREFIX*meta_data_docTags WHERE fileid=? AND tagid=?';
			$args = array($fileid, $tagid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
			while($row=$output->fetchRow()){
				$result[] = $row;
			}
			if(count($result)==0){
				\OCP\Util::writeLog('meta_data', 'Inserting tag: '.$fileid.':'.$tagid, \OC_Log::WARN);
				$sql = 'INSERT INTO *PREFIX*meta_data_docTags (fileid, tagid) VALUES (?,?)';
				$args = array($fileid, $tagid);
			}
			else{
				\OCP\Util::writeLog('meta_data', 'Updating tag: '.$fileid.':'.$tagid, \OC_Log::WARN);
				$sql = 'UPDATE *PREFIX*meta_data_docTags SET tagid=? WHERE fileid=?';
				$args = array($tagid, $fileid);
			}
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
			$ret && !\OCP\DB::isError($output);
		}
		return $ret;
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

	public static function updateFileKeyVal($fileids, $tagid, $keyid, $value){
		// We allow $fileid to be a colon-separated list of ids
		$fileidArr = explode(':', $fileids);
		$res = array();
		foreach($fileidArr as $i => $fileid){
			$sql = 'SELECT * FROM *PREFIX*meta_data_docKeys WHERE fileid=? AND tagid=? and keyid=?';
			$result = array();
			$args = array($fileid, $tagid, $keyid);
			$query = \OCP\DB::prepare($sql);
			$output = $query->execute($args);
			while($row=$output->fetchRow()){
				$result[] = $row;
			}
			if(count($result) == 0 ){
				$sql = 'INSERT INTO *PREFIX*meta_data_docKeys (fileid,tagid,keyid,value) VALUES (?,?,?,?)';
				$args = array($fileid, $tagid, $keyid, $value);
				$query = \OCP\DB::prepare($sql);
				\OCP\Util::writeLog('meta_data', 'ARGS: '.implode('#', $args).'-->'.sizeof($args), \OC_Log::WARN);
				$resRsrc = $query->execute($args);
				if($resRsrc){
					$res[] = array('tagid'=>$tagid, 'keyid'=>$keyid, 'fileid'=>$fileid, 'value'=>$value);
				}
			}
			else if(count($result)==1){
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
	
	private static function getRow($array, $key, $val) {
		foreach($array as $row){
			if($row[$key]===$val){
				return $row;
			}
		}
		return null;
	}
	
	/**
	 * Update tags for a user from his primary server after getting his files.
	 * Only used by files_sharding cron job.
	 * @param unknown $user_id
	 * @param unknown $baseurl
	 * @return multitype:
	 */
	public static function updateUserFileTags($user_id, $baseurl){
		// Get map fileID->[tag1, tag2, ...] with file owned by user
		$fileTagsArr = \OCA\FilesSharding\Lib::ws('getUserFileTags', array('user_id'=>$user_id), false, false,
				$baseurl, 'meta_data');
		// Get all files owned by user from old server
		$oldUserFiles = \OCA\FilesSharding\Lib::ws('get_user_files', array('user_id'=>$user_id), false, true, $baseurl);
		// Get all files owned by user locally/on new server
		$newUserFiles = \OCA\FilesSharding\Lib::dbGetUserFiles($user_id);
		// Fix up file tags with new fileid instead of old one
		\OCP\Util::writeLog('meta_data', 'Inserting TAGS '.serialize($fileTagsArr), \OC_Log::WARN);
		$newFileTagsArr = array();
		foreach($fileTagsArr as $fileTagsStd){
			$fileTags = self::decodeStdClassToFileTags($fileTagsStd);
			$oldFileID = $fileTags->fileid;
			$oldFile = self::getRow($oldUserFiles, 'fileid', $oldFileID);
			$path = $oldFile['path']; // starts with "files/"
			$newFile = self::getRow($newUserFiles, 'path', $path);
			$newFileID = $newFile['fileid'];
			\OCP\Util::writeLog('meta_data', 'Inserting tags for '.$path.': '.$oldFileID.'-->'.$newFileID.'-->'.serialize($fileTags), \OC_Log::WARN);
			// If no local file was found, syncing files probably failed - back off.
			if(!empty($newFile)){
				$fileTags->setFileID($newFileID);
				$newFileTagsArr[] = $fileTags;
			}
		}
		\OCP\Util::writeLog('meta_data', 'Inserting TAGS1 '.serialize($newFileTagsArr), \OC_Log::WARN);
		// Now insert file tags in the local DB
		$ret = self::dbInsertUserFileTags($user_id, $newFileTagsArr);
		return $ret;
	}

	
	public static function decodeStdClassToFileTags($fileTags){
		\OCP\Util::writeLog('meta_data', 'FILETAGS: '.serialize($fileTags), \OC_Log::WARN);
		$fileTags1 = new FileTags($fileTags->fileid);
		foreach($fileTags->filetags as $fileTag){
			$temp = serialize($fileTag);
			\OCP\Util::writeLog('meta_data', 'TEMP: '.$temp, \OC_Log::WARN);
			if(!preg_match('@^O:8:"stdClass":@', $temp)){
				continue;
			}
			$temp = preg_replace('@^O:8:"stdClass":@','O:8:"FileTags":', $temp);
			$temp = preg_replace('@O:8:"stdClass":@','O:7:"FileTag":', $temp);
			$fileTag1 = unserialize(stripslashes($temp));
			$fileTags1->addFileTag(new FileTag($fileTags->{'fileid'}, $fileTag->{'tagid'},
				(isset($fileTag->{'keyvals'})?$fileTag->{'keyvals'}:array())));
		}
		return $fileTags1;
	}

}
