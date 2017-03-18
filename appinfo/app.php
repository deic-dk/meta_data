<?php
OC::$CLASSPATH['OCA\meta_data\Tags']       = 'apps/meta_data/lib/tags.php';
OC::$CLASSPATH['OCA\meta_data\hooks']      = 'apps/meta_data/lib/hooks.php';
OC::$CLASSPATH['OCA\Search_meta_data\Tag'] = 'apps/meta_data/lib/searchTags.php';
OC::$CLASSPATH['OCA\Search_meta_data\Metadata'] = 'apps/meta_data/lib/searchTags.php';

if(isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI']!='/' &&
		strpos($_SERVER['REQUEST_URI'], "/js/")===false){
	OC_Search::registerProvider('OCA\Search_meta_data\Tag');
	OC_Search::registerProvider('OCA\Search_meta_data\Metadata');
	
	$order=3;
	
	\OCP\App::addNavigationEntry(
		array(
				'appname' => 'meta_data', 
				'id' => 'meta_data',
				'order' => $order,
				'href' => OCP\Util::linkTo("meta_data", "index.php"),
				'name' => 'Metadata'
		)
	);
	

	if(\OCP\User::isLoggedIn()){
		if(isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], "/settings/")===false){
			if(strpos($_SERVER['REQUEST_URI'], "index.php/apps/")===false){
				OCP\Util::addScript('meta_data', 'filelist');
			}
			OCP\Util::addScript('meta_data', 'editor');
			OCP\Util::addStyle('meta_data', 'meta_data');
			$tags = \OCA\Meta_data\Tags::searchTags('%',\OCP\User::getUser());
			foreach ($tags as $tag){
				$order+=1./100;
				\OCA\Files\App::getNavigationManager()->add(
						array(
								"id" => 'tag-'.$tag['id'],
								"appname" => 'meta_data',
								"script" => 'list.php',
								"order" =>  $order,
								"name" => $tag['name']
						)
				);
			}
		}
		OCP\Util::addScript('meta_data', 'fileactions');
		OCP\Util::addScript('meta_data', 'app');
		OCP\Util::addScript('meta_data', 'dropdown');
		OCP\Util::addStyle('meta_data', 'filelist');
	}
	
	\OCP\Util::connectHook('OC_Filesystem', 'delete', 'OCA\meta_data\hooks', 'deleteFile');
	\OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\meta_data\Hooks', 'deleteUser');
}

