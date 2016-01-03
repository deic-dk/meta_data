<?php
OC::$CLASSPATH['OCA\meta_data\Tags']       = 'apps/meta_data/lib/tags.php';
OC::$CLASSPATH['OC_meta_data_mainview']    = 'apps/meta_data/lib/fileViewer.php';
// TODO: delete fileViewer.php ?
OC::$CLASSPATH['OCA\meta_data\hooks']      = 'apps/meta_data/lib/hooks.php';
OC::$CLASSPATH['OCA\Search_meta_data\Tag'] = 'apps/meta_data/lib/searchTags.php';
OC::$CLASSPATH['OCA\Search_meta_data\Metadata'] = 'apps/meta_data/lib/searchTags.php';

if(strpos($_SERVER['REQUEST_URI'], "/ajax/")<=0 && strpos($_SERVER['REQUEST_URI'], "/js/")<=0){
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
	
	if(\OCP\User::isLoggedIn() ){
		OCP\Util::addScript('meta_data', 'fileactions');
		OCP\Util::addScript('meta_data', 'app');
		OCP\Util::addScript('meta_data', 'dropdown');
		OCP\Util::addStyle('meta_data', 'filelist');
	}
	
	\OCP\Util::connectHook('OC_Filesystem', 'delete', 'OCA\meta_data\hooks', 'deleteFile');
	\OCP\Util::connectHook('OC_User', 'post_deleteUser', 'OCA\meta_data\Hooks', 'deleteUser');
}

