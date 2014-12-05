<?php

// Highlight current menu item
OCP\App::setActiveNavigationEntry('meta_data');

//\OCP\Util::addStyle('meta_data', 'ui.fancytree');
//\OCP\Util::addStyle('meta_data', 'fileviewer');
//\OCP\Util::addScript('meta_data', 'fancytree/jquery.fancytree');
//\OCP\Util::addScript('meta_data', 'fancytree/jquery.fancytree.dnd');

//\OCP\Util::addScript('meta_data', 'meta_data_tagtree');
\OCP\Util::addScript('meta_data', 'meta_data_main');
\OCP\Util::addScript('meta_data', 'meta_data_search');

\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('meta_data');




$tpl = new OCP\Template("meta_data", "main", "user");
$tpl->printPage();
