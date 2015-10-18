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
$userid = isset($_GET['userid'])?$_GET['userid']:\OCP\User::getUser();

$tags = \OCA\Meta_data\Tags::dbSearchKeys($name, $userid);

\OCP\Util::writeLog('meta_data', 'Returning keys '.serialize($tags), \OC_Log::WARN);

OCP\JSON::encodedPrint($tags);

