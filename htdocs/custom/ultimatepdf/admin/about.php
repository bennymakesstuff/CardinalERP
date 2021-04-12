<?php
/* Copyright (C) 2011-2015	Philippe Grand	<philippe.grand@atoo-net.com>
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
 * 	\file		htdocs/custom/ultimatepdf/admin/about.php
 * 	\ingroup	ultimatepdf
 * 	\brief		about page
 */

// Dolibarr environment
$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");		// For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");	// For "custom" directory


// Libraries
require_once("../lib/ultimatepdf.lib.php");
dol_include_once("ultimatepdf/lib/PHP_Markdown/markdown.php");


// Translations
$langs->load("admin");
$langs->load("ultimatepdf@ultimatepdf");

// Access control
if (!$user->admin)
	accessforbidden();

/*
 * View
 */

$wikihelp='EN:Module_Ultimatepdf_EN|FR:Module_Ultimatepdf_FR';
$page_name = $langs->trans("About");
llxHeader('', $page_name, $wikihelp);

// Subheader
$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($page_name,$linkback,'ultimatepdf@ultimatepdf');

// Configuration header
$head = ultimatepdf_prepare_head();
dol_fiche_head($head, 'about', $langs->trans("Module300100Name"), 0, "ultimatepdf@ultimatepdf");

// About page goes here

print '<br>';

$buffer = file_get_contents(dol_buildpath('/ultimatepdf/README.md',0));
print Markdown($buffer);

print '<br>';
print $langs->trans("MoreModules").'<br>';
print '&nbsp; &nbsp; &nbsp; '.$langs->trans("MoreModulesLink").'<br>';
$url=$langs->trans("MoreModulesLink2");
print '<a href="'.$url.'" target="_blank"><img border="0" width="180" src="'.DOL_URL_ROOT.'/theme/dolistore_logo.png"></a><br><br><br>';

print '<a href="'.dol_buildpath('/ultimatepdf/COPYING',1).'">';
print '<img src="'.dol_buildpath('/ultimatepdf/img/gplv3.png',1).'"/>';
print '</a>';

// Footer
llxFooter();
// Close database handler
$db->close();
?>
