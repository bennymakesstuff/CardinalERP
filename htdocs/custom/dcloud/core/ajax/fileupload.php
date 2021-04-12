<?php
/* Copyright (C) 2011-2017 Regis Houssin	<regis.houssin@capnetworks.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *       \file       /dcloud/core/ajax/fileupload.php
 *       \brief      File to return Ajax response on file upload
 */

if (! defined('NOREQUIRESOC'))		define('NOREQUIRESOC','1');
if (! defined('NOCSRFCHECK'))		define('NOCSRFCHECK','1');
if (! defined('NOTOKENRENEWAL'))		define('NOTOKENRENEWAL','1');
if (! defined('NOREQUIREMENU'))		define('NOREQUIREMENU','1'); // If there is no menu to show
if (! defined('NOREQUIREHTML'))		define('NOREQUIREHTML','1'); // If we don't need to load the html.form.class.php

$res=@include("../../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../../main.inc.php");	// For "custom" directory

error_reporting(E_ALL | E_STRICT);

dol_include_once('/dcloud/class/fileupload.class.php');

$upload_handler = new FileUpload(null, $_REQUEST);

header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Content-Disposition: inline; filename="files.json"');
header('X-Content-Type-Options: nosniff');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: OPTIONS, HEAD, GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: X-File-Name, X-File-Type, X-File-Size');

switch ($_SERVER['REQUEST_METHOD']) {
	case 'OPTIONS':
		break;
	case 'HEAD':
	case 'GET':
		$upload_handler->get();
		break;
	case 'POST':
		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
			$upload_handler->delete();
		} else {
			$upload_handler->post();
		}
		break;
	case 'DELETE':
		$upload_handler->delete();
		break;
	default:
		header('HTTP/1.1 405 Method Not Allowed');
}

$db->close();

?>