<?php

OCP\JSON::checkLoggedIn();

$tagid   =  isset( $_GET['tagid'] ) ? $_GET['tagid'] : '';
$color   =  isset( $_GET['color'] ) ? $_GET['color'] : '';
$tagname =  isset( $_GET['tagname'] ) ? $_GET['tagname'] : '';
$visible =  isset( $_GET['visible'] ) ? $_GET['visible'] : '';


$result = \OCA\Meta_data\Helper::updateTag($tagid, $tagname, $color, $visible);


