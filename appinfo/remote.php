<?php

OCP\JSON::checkAppEnabled('chooser');
require_once('chooser/lib/lib_chooser.php');
require_once('chooser/lib/ip_auth.php');
require_once('chooser/lib/nbf_auth.php');

$ok = false;

if(!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])){
	$authBackendNBF = new OC_Connector_Sabre_Auth_NBF();
	$ok = $authBackendNBF->checkUserPass($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
}

if(!$ok){
	$authBackendIP = new Sabre\DAV\Auth\Backend\IP();
	$ok = $authBackendIP->checkUserPass($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW']);
}

if(!$ok){
	$user = \OC_Chooser::checkIP();
	if(empty($user)){
		if(!OCA\FilesSharding\Lib::checkIP()){
			http_response_code(401);
			exit;
		}
	}
}
else{
	$user = OCP\USER::getUser();
}

OCP\JSON::checkLoggedIn();

$userServerAccess = \OCA\FilesSharding\Lib::getUserServerAccess();
// Block all access if account is locked on server
if($ok && \OCP\App::isEnabled('files_sharding') &&
		$userServerAccess!=\OCA\FilesSharding\Lib::$USER_ACCESS_ALL &&
		$userServerAccess!=\OCA\FilesSharding\Lib::$USER_ACCESS_READ_ONLY){
	$ok = false;
}

// Block write operations on r/o server
if($ok && \OCP\App::isEnabled('files_sharding') &&
		$userServerAccess==\OCA\FilesSharding\Lib::$USER_ACCESS_READ_ONLY &&
		(strtolower($_SERVER['REQUEST_METHOD'])=='mkcol' || strtolower($_SERVER['REQUEST_METHOD'])=='put' ||
				strtolower($_SERVER['REQUEST_METHOD'])=='move' || strtolower($_SERVER['REQUEST_METHOD'])=='delete' ||
				strtolower($_SERVER['REQUEST_METHOD'])=='proppatch')){
	$ok = false;
}

\OCP\Util::writeLog('meta_data', 'User '.$_SERVER['PHP_AUTH_USER']." --> ".$ok, \OC_Log::WARN);

if(!$ok || empty($user)){
	header($_SERVER['SERVER_PROTOCOL'] . ' 403 Forbidden');
	exit();
}

// Redirect if not on home server of user
if(OCP\App::isEnabled('files_sharding')){
	$masterUrl = OCA\FilesSharding\Lib::getMasterURL();
	$serverUrl = OCA\FilesSharding\Lib::getServerForUser($user);
	if(!OCA\FilesSharding\Lib::onServerForUser($user)){
		if(!empty($_SERVER['HTTP_DESTINATION'])){
			$destination = preg_replace('|^'.$masterUrl.'|', $serverUrl, $_SERVER['HTTP_DESTINATION']);
			header("Destination: " . $destination);
		}
		header("HTTP/1.1 301 Moved Permanently");
		header("Location: " . $serverUrl . $_SERVER['REQUEST_URI']);
		header("User: " . $user);
		exit;
	}
}

$group = isset($_REQUEST['group']) ? $_REQUEST['group'] : '';
if(!empty($group) && !empty($user)){
	$filesDir = '/'.$user.'/user_group_admin/'.$group;
	OC_Log::write('meta_data','Non-files access: '.$filesDir, OC_Log::WARN);
	\OC\Files\Filesystem::tearDown();
	\OC\Files\Filesystem::init($user, $filesDir);
}
elseif(!empty($user)){
	$filesDir = '/'.$user.'/files';
	\OC\Files\Filesystem::init($user, $filesDir);
}

///////////////////////////////

require_once('apps/chooser/appinfo/apache_note_user.php');

require_once('meta_data/lib/tags.php');

header("Content-Type: application/json");

$ret = [];

switch($_GET['action']){
	case 'searchtags':
		$tag = !empty($_GET['tag']) ? $_GET['tag'] : '';
		$ret['tags'] = \OCA\meta_data\Tags::searchTags($tag, $user);
		break;
	case 'createtag':
		$tag = !empty($_POST['tag']) ? $_POST['tag'] : '';
		$display = !empty($_POST['display']) ? $_POST['display'] : '';
		$color = !empty($_POST['color']) ? $_POST['color'] : '';
		$public = !empty($_POST['public']) ? $_POST['public'] : '';
		$ret['status'] = empty($tag)?0:
			\OCA\meta_data\Tags::newTag($tag, $user, $display, $color, $public);
		break;
	case 'updatetag':
		$tag = !empty($_POST['tag']) ? $_POST['tag'] : '';
		$tagid = \OCA\meta_data\Tags::getTagID($tag, $user);
		$display = !empty($_POST['display']) ? $_POST['display'] : '';
		$color = !empty($_POST['color']) ? $_POST['color'] : '';
		$public = !empty($_POST['public']) ? $_POST['public'] : '';
		$description = !empty($_POST['description']) ? $_POST['description'] : '';
		$ret['status'] = empty($tagid)?0:
			\OCA\meta_data\Tags::updateTag($tagid, null, $description, $color, $public, $user, $display);
		break;
	case 'deletetag':
		$tag = !empty($_POST['tag']) ? $_POST['tag'] : '';
		$tagid = \OCA\meta_data\Tags::getTagID($tag, $user);
		$ret['status'] = empty($tagid)?0:\OCA\meta_data\Tags::deleteTag($tagid, $user);
		break;
	case 'addattribute':
		$tag = !empty($_POST['tag']) ? $_POST['tag'] : '';
		$tagid = \OCA\meta_data\Tags::getTagID($tag, $user);
		$attribute = !empty($_GET['attribute']) ? $_GET['attribute'] : '';
		$ret['status'] =  (empty($tagid)||empty($attribute))?0:
			\OCA\meta_data\Tags::newKey($tagid, $attribute);
		break;
	case 'deleteattribute':
		$tag = !empty($_POST['tag']) ? $_POST['tag'] : '';
		$tagid = \OCA\meta_data\Tags::getTagID($tag, $user);
		$attribute = !empty($_POST['attribute']) ? $_POST['attribute'] : '';
		$ret['status'] =  (empty($tagid)||empty($attribute))?0:
			\OCA\meta_data\Tags::deleteKeys($tag, $attribute);
		break;
	case 'listattributes':
		$tag = !empty($_GET['tag']) ? $_GET['tag'] : '';
		$tagid = \OCA\meta_data\Tags::getTagID($tag, $user);
		$ret['attributes'] =  (empty($tagid)||empty($attribute))?array():
			\OCA\meta_data\Tags::searchKey($tagid, '%', $user);
		break;
	case 'addtag':
		$tag = !empty($_POST['tag']) ? $_POST['tag'] : '';
		$tagid = \OCA\meta_data\Tags::getTagID($tag, $user);
		$files = !empty($_POST['file']) ? $_POST['file'] : '';
		// We allow $file to be a colon-separated list of paths
		if(!empty($files) && strpos($files, ':')>0){
			$fileArr = explode(':', $files);
		}
		else{
			$fileArr = array($files);
		}
		foreach($fileArr as $file){
			if(OCP\App::isEnabled('files_sharding') && OCP\App::isEnabled('user_group_admin')){
				$fileid = \OCA\FilesSharding\Lib::getFileId($file, $user, $group);
			}
			else{
				$view = new \OC\Files\View('/'.$user.'/files');
				$fileInfo = $view->getFileInfo($file);
				if($fileInfo){
					$fileid = $fileInfo['fileid'];
				}
			}
			$ret['status'] =  $ret['status'] && ((empty($tagid)||empty($fileid))?0:
				\OCA\meta_data\Tags::updateFileTag($tagid, $user, $fileid));
		}
		break;
	case 'removetag':
		$tag = !empty($_POST['tag']) ? $_POST['tag'] : '';
		$tagid = \OCA\meta_data\Tags::getTagID($tag, $user);
		$files = !empty($_POST['file']) ? $_POST['file'] : '';
		// We allow $file to be a colon-separated list of paths
		if(!empty($files) && strpos($files, ':')>0){
			$fileArr = explode(':', $files);
		}
		else{
			$fileArr = array($files);
		}
		foreach($fileArr as $file){
			if(OCP\App::isEnabled('files_sharding') && OCP\App::isEnabled('user_group_admin')){
				$fileid = \OCA\FilesSharding\Lib::getFileId($file, $user, $group);
			}
			else{
				$view = new \OC\Files\View('/'.$user.'/files');
				$fileInfo = $view->getFileInfo($file);
				if($fileInfo){
					$fileid = $fileInfo['fileid'];
				}
			}
			$ret['status'] = $ret['status'] && ((empty($tagid)||empty($fileid))?0:
				\OCA\meta_data\Tags::removeFileTag($tagid, $fileid));
		}
		break;
	case 'listfiles':
		$tag = !empty($_GET['tag']) ? $_GET['tag'] : '';
		$tagid = empty($tag)?'':\OCA\meta_data\Tags::getTagID($tag, $user);
		$ret['files'] = empty($tagid)?array():\OCA\meta_data\Tags::getTaggedFiles($tagid);
		break;
	case 'searchfiles':
		$tag = !empty($_GET['tag']) ? $_GET['tag'] : '';
		$tagid = empty($tag)?'':\OCA\meta_data\Tags::getTagID($tag, $user);
		$attribute = !empty($_GET['attribute']) ? $_GET['attribute'] : '';
		$attributeid = empty($tag)?'':\OCA\meta_data\Tags::getKeyID($tagid, $attribute, $user);
		$value = !empty($_GET['value']) ? $_GET['value'] : '';
		$ret['files'] = \OCA\meta_data\Tags::getFilesWithMetadata($value, $user, $tagid, $attributeid);
		break;
	case 'getmetadata':
		$tag = !empty($_GET['tag']) ? $_GET['tag'] : '';
		$tagids = !empty($tag)?[\OCA\meta_data\Tags::getTagID($tag, $user)] : [];
		$files = !empty($_GET['files']) ? $_GET['files'] : "";
		// $files is a json encoded list of paths
		if(!empty($files)){
			$fileArr = json_decode($files, true);
		}
		\OCP\Util::writeLog('meta_data', 'Getting metadata for '.$files.' --> '.serialize($fileArr), \OC_Log::WARN);
		$fileIdArr = array();
		foreach($fileArr as $file){
			if(OCP\App::isEnabled('files_sharding') && OCP\App::isEnabled('user_group_admin')){
				$fileid = \OCA\FilesSharding\Lib::getFileId($file, $user, $group);
			}
			else{
				$view = new \OC\Files\View('/'.$user.'/files');
				$fileInfo = $view->getFileInfo($file);
				if($fileInfo){
					$fileid = $fileInfo['fileid'];
				}
			}
			$fileIdArr[] = $fileid;
		}
		\OCP\Util::writeLog('meta_data', 'Getting tags for '.$user.':'.$files.' --> '.serialize($fileIdArr).':'.$tagid, \OC_Log::WARN);
		$ret = \OCA\meta_data\Tags::getUserFileTags($user, $fileIdArr, $tagids, true, true);
	default:
		break;
}

OCP\JSON::encodedPrint($ret);

