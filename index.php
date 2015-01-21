<?php

// Highlight current menu item
//OCP\App::setActiveNavigationEntry('meta_data');


\OCP\Util::addScript('meta_data', 'meta_data_main');
\OCP\Util::addScript('meta_data', 'meta_data_search');

\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('meta_data');




$tpl = new OCP\Template("meta_data", "main", "user");
$tpl->printPage();
