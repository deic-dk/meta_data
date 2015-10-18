<?php


function build_sorter_desc($key) {
  return function ($a, $b) use ($key) {
	return strnatcmp($b[$key], $a[$key]);
  };
}

OCP\JSON::checkLoggedIn();
\OC::$session->close();

$tagids = \OCA\Meta_data\Tags::getUserDisplayTags();
$tags = \OCA\Meta_data\Tags::getTags($tagids);

OCP\JSON::success(array('tags' => $tags));
