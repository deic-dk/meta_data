<?php

\OCP\JSON::callCheck();
\OCP\JSON::checkLoggedIn();
\OCP\JSON::checkAppEnabled('meta_data');


$filename = $_POST['filename'];



require_once('meta_data//3rdparty/getID3-1.9.8/getid3/getid3.php');
$getID3 = new getID3;
$fileInfo = $getID3->analyze($filename);



echo json_encode($fileInfo);
