<?php
OC::$CLASSPATH['OCA\meta_data\tags']   = 'apps/meta_data/libs/tags.php';
OC::$CLASSPATH['OC_meta_data_mainview']= 'apps/meta_data/libs/file_viewer.php';
OC::$CLASSPATH['OCA\meta_data\hooks']  = 'apps/meta_data/libs/hooks.php';
OC::$CLASSPATH['OCA\Meta_data\Helper'] = 'apps/meta_data/libs/helper.php';


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


$tags=\OCA\Meta_data\Helper::searchTag('%',\OCP\User::getUser()); 


foreach ($tags as $tag){
		$order+=1./100;
		\OCA\Files\App::getNavigationManager()->add(
				array(
						"id" => 'tag-'.$tag['tagid'],
						"appname" => 'meta_data',
						"script" => 'list.php',
						"order" =>  $order,
						"name" => $tag['descr']
				)
		);

}
 


if(\OCP\User::isLoggedIn() ){
		OCP\Util::addScript('meta_data', 'fileactions');
		OCP\Util::addScript('meta_data', 'app');
		\OCP\Util::addScript('meta_data', 'fileDropdown');

		\OCP\Util::addStyle('meta_data', 'filelist');
}

//\OCP\Util::addScript('meta_data', 'mp3/id3-minimized');
\OCP\Util::connectHook('OC_Filesystem', 'delete', 'OCA\meta_data\hooks', 'deleteFile');  





