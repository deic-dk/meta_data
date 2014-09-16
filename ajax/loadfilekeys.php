<?php
\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');

$ctags = new \OCA\meta_data\tags();

$fileID   = $_POST['fileid'];


$result = $ctags->loadFileKeys($fileID);


echo  json_encode( $result );
