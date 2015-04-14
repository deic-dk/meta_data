<?php

// Check if we are a user
 OCP\User::checkLoggedIn();

 $tmpl = new OCP\Template('meta_data', 'list', '');

 OCP\Util::addScript('meta_data', 'filelist');
 OCP\Util::addScript('meta_data', 'editor');
 OCP\Util::addStyle('meta_data', 'meta_data');

 $tmpl->printPage();

