<?php
/* Copyright (C) 2011-2018 Regis Houssin  <regis.houssin@capnetworks.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * 	\file		/dcloud/admin/about.php
 * 	\ingroup	d-cloud
 * 	\brief		About Page
 */

$res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory


// Libraries
require_once "../lib/dcloud.lib.php";
require_once "../lib/PHP_Markdown/markdown.php";


// Translations
$langs->load("dcloud@dcloud");

// Access control
if (!$user->admin)
	accessforbidden();

/*
 * View
 */

llxHeader('', $langs->trans("Module70000Name"));

// Subheader
$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ModuleSetup"), $linkback, 'dropbox_32x32@dcloud');

// Configuration header
$head = dropboxadmin_prepare_head();
dol_fiche_head($head, 'about', $langs->trans("Module70000Name"));

// About page goes here

print '<br>';

$buffer = file_get_contents(dol_buildpath('/dcloud/README.md',0));
print Markdown($buffer);

print '<br>';

$url = 'https://www.inodbox.com/';
$link = '<a href="'.$url.'" target="_blank">iNodbox</a>';
print $langs->trans("DcloudMoreModules", $link).'<br><br>';
print '<a href="'.$url.'" target="_blank"><img border="0" width="180" src="'.dol_buildpath('/dcloud/img/inodbox.png',1).'"></a>';
print '<br><br><br>';

print '<a target="_blank" href="'.dol_buildpath('/dcloud/COPYING',1).'"><img src="'.dol_buildpath('/dcloud/img/gplv3.png',1).'"/></a>';

llxFooter();

$db->close();
