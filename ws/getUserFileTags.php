<?php

/**
 * ownCloud meta_data app
 *
 * @author Frederik Orellana
 * @copyright 2015 Frederik Orellana
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU AFFERO GENERAL PUBLIC LICENSE
 * License as published by the Free Software Foundation; either
 * version 3 of the License, or any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU AFFERO GENERAL PUBLIC LICENSE for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

OCP\JSON::checkAppEnabled('meta_data');
OCP\JSON::checkAppEnabled('files_sharding');

if(!OCA\FilesSharding\Lib::checkIP()){
	http_response_code(401);
	exit;
}

$userid = isset($_GET['user_id'])?$_GET['user_id']:'';
$fileids = isset($_GET['fileids'])?$_GET['fileids']:'';
$tagids = isset($_GET['tagids'])?$_GET['tagids']:'';
$useKeyNames = isset($_GET['usekeynames'])?$_GET['usekeynames']=='yes':false;

$tags = \OCA\Meta_data\Tags::getUserFileTags($userid, $fileids, $tagids, $useKeyNames);

\OCP\Util::writeLog('meta_data', 'Returning tags', \OC_Log::DEBUG);

OCP\JSON::encodedPrint($tags);



