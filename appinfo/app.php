<?php
\OCP\App::addNavigationEntry(array(

    // the string under which your app will be referenced in owncloud
    'id' => 'meta_data',

    // sorting weight for the navigation. The higher the number, the higher
    // will it be listed in the navigation
    'order' => 2,

    // the route that will be shown on startup
	"href" => OCP\Util::linkTo("meta_data", "index.php"),

    // the icon that will be shown in the navigation
    // this file needs to exist in img/example.png
    'icon' => \OCP\Util::imagePath('meta_data', 'nav-icon.svg'),

    // the title of your application. This will be used in the
    // navigation or on the settings page of your app
    'name' => 'Tags - beta'
));

OC::$CLASSPATH['OCA\meta_data\tags']   = 'apps/meta_data/libs/tags.php';
OC::$CLASSPATH['OC_meta_data_mainview']= 'apps/meta_data/libs/file_viewer.php';
OC::$CLASSPATH['OCA\meta_data\hooks']  = 'apps/meta_data/libs/hooks.php';

\OCP\Util::addScript('meta_data', 'bootstrap-tokenfield');
\OCP\Util::addStyle( 'meta_data', 'bootstrap-tokenfield');
\OCP\Util::addStyle( 'meta_data', 'tokenfield-typeahead');
\OCP\Util::addScript('meta_data', 'meta_data_fileinfo');
\OCP\Util::addStyle('meta_data', 'meta_data');

\OCP\Util::addScript('meta_data', 'mp3/id3-minimized');

\OCP\Util::connectHook('OC_Filesystem', 'delete', 'OCA\meta_data\hooks', 'deleteFile');  


