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

$name = isset($_GET['name'])?$_GET['name']:'%';
$tagid = isset($_GET['tagid'])?$_GET['tagid']:null;
$user = isset($_GET['user'])?$_GET['user']:\OCP\User::getUser();

$keys = \OCA\Meta_data\Tags::dbSearchKey($tagid, $name, $userid);

\OCP\Util::writeLog('meta_data', 'Returning keys '.serialize($keys), \OC_Log::WARN);

OCP\JSON::encodedPrint($keys);

