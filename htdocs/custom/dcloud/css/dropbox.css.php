<?php
/* Copyright (C) 2011-2017 Regis Houssin  <regis.houssin@capnetworks.com>
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
 *		\file       /dcloud/css/dropbox.css.php
 *		\brief      Fichier de style CSS complementaire du module D-Cloud
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled cause need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled to increase speed. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled cause need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK',1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL',1);
if (! defined('NOLOGIN'))         define('NOLOGIN',1);
if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML',1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX','1');


$res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory

require_once DOL_DOCUMENT_ROOT."/core/lib/functions.lib.php";

// Define css type
header('Content-type: text/css');
// Important: Following code is to avoid page request by browser and PHP CPU at
// each Dolibarr page access.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');


if (! empty($_GET["lang"])) $langs->setDefaultLang($_GET["lang"]);	// If language was forced on URL by the main.inc.php
$langs->load("main",0,1);
$right=($langs->direction=='rtl'?'left':'right');
$left=($langs->direction=='rtl'?'right':'left');
?>

.used-hover:hover {
	background: #50a2e3!important;
}
.free-hover:hover {
	background: #9ad6f9!important;
}

div.liste_titre {
	padding-top: 5px!important;
	padding-bottom: 5px!important;
	/*padding-left: 5px!important;*/
	border-bottom: 0!important;
}

.padding-left5 {
	padding-left: 5px;
}

.fifty-percent {
	width: 50%;
}
.seventy-percent {
	width: 70%;
}

.float-right {
	float: right;
}
.valign-middle {
	vertical-align: middle;
}

.text-align-right {
	text-align: right;
}
.text-align-center {
	text-align: center;
}
.text-align-left {
	text-align: left;
}
.blockspan {
  display: inline-block;
  width: 50px;
}
.display-table {
	display: table;
}

.button-align-right {
	text-align: right;
	padding-right: 30px;
}

.showhide-button {
	float:right;
	position: relative;
	right:5px;
}

.dcloud-button-sync {
	cursor: pointer;
}
.dcloud-sync-button {
	display: inline-block;
	width: 14px;
}
.dcloud-nosync-button {
  display: inline-block;
  width: 14px;
}
.dcloud-nosync-nobutton {
  display: inline-block;
  width: 14px;
}

.dcloud-button-info {
	cursor: help;
}
.dcloud-sync-info {
	display: inline-block;
	width: 14px;
}
.dcloud-nosync-info {
  display: inline-block;
  width: 14px;
}
.dcloud-nosync-noinfo {
  display: inline-block;
  width: 14px;
}

.ui-dialog .ui-dialog-buttonpane {
    text-align: center;
}
.ui-dialog .ui-dialog-buttonpane .ui-dialog-buttonset {
    float: none;
}

#tiptip_content {
	text-align: center;
}

.button-not-allowed {
	cursor:not-allowed
}

.fileinput-button {
    margin-top: 0!important;
    margin-left: 1px;
}

.ecm-in-layout-center {
	overflow-y: auto;
}

.ui-layout-pane-west {
	overflow-y: auto;
}