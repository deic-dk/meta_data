<?php

// Highlight current menu item
OCP\App::setActiveNavigationEntry('meta_data');

\OCP\Util::addStyle('meta_data', 'ui.fancytree');
\OCP\Util::addScript('meta_data', 'fancytree/jquery.fancytree');
\OCP\Util::addScript('meta_data', 'fancytree/jquery.fancytree.dnd');

// Following is needed by layout manager
//\OCP\Util::addScript('oclife', 'layout/jquery.sizes');
//\OCP\Util::addScript('oclife', 'layout/jlayout.border');
//\OCP\Util::addScript('oclife', 'layout/jquery.jlayout');
//\OCP\Util::addScript('oclife', 'layout/layout');

// THEN execute what needed by us...
//\OCP\Util::addStyle('oclife', 'oclife');
\OCP\Util::addScript('meta_data', 'meta_data_tagtree');

\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('meta_data');

$tpl = new OCP\Template("meta_data", "main", "user");

$tpl->printPage();
