<?php
/* Copyright (C) 2007-2017	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2018		Peter Roberts		<webmaster@finchmc.com.au>
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
 *   	\file		htdocs/custom/wip/report_about.php
 *		\ingroup	wip
 *		\brief		Instructions for preparing reports
 */

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php")) $res=@include(dirname(substr($tmp, 0, ($i+1)))."/main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../main.inc.php")) $res=@include("../main.inc.php");
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

// Libraries
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/wip/lib/wip.lib.php';
dol_include_once('/wip/class/report.class.php');
dol_include_once('/wip/lib/wip_report.lib.php');

// Load translation files required by the page
$langs->loadLangs(array('wip@wip', 'projects', 'errors', 'admin'));

// Get parameters
$id				= GETPOST('id', 'int');
$ref			= GETPOST('ref', 'alpha');
$reportref		= GETPOST('reportref','alpha');
$action			= GETPOST('action','aZ09')?GETPOST('action','aZ09'):'view';	// The action 'add', 'create', 'edit', 'update', 'view', ...
$projectid		= GETPOST('projectid','int');
$project_ref	= GETPOST('project_ref','alpha');
$backtopage		= GETPOST('backtopage','alpha');							// Go back to a dedicated page

// Initialise technical objects
$object = new Report($db);
$projectstatic = new Project($db);

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('reportcard','globalcard'));

// Fetch optional attributes and labels

$parameters = array();
// Load object
//include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

if (GETPOST('gotolist','int') > 0)	// For next/prev to function we need to redirect to the list of project reports.
{
	$newurl = dol_buildpath('/wip/report_list.php',1).'?ref='.GETPOST('project','alpha');
	header("Location: ".$newurl);
	exit;
}

if ($id > 0 || ! empty($ref))
{
	$ret = $object->fetch($id, $ref);
	if ($ret < 0) dol_print_error($db,$object->error);
	$ret=$object->fetch_optionals();
	$ret=$projectstatic->fetch($object->fk_project);
	if ($ret < 0) dol_print_error($db,$object->error);
	$userWrite = $projectstatic->restrictedProjectArea($user,'write');
	$projectstatic->fetch_thirdparty();
	$projectid = $projectstatic->id;
	$socid = $projectstatic->socid;
}
elseif ($projectid > 0 || ! empty($projectref) )
{
	$ret=$projectstatic->fetch($projectid,$projectref);
	if ($ret < 0) dol_print_error($db,$projectstatic->error);
	$object->fk_project = $projectstatic->id;
	if (empty($projectid) && ! empty($projectstatic->socid))$projectid = $projectstatic->id;
	$userWrite = $projectstatic->restrictedProjectArea($user,'write');
	$projectstatic->fetch_thirdparty();
	$socid = $projectstatic->socid;
	//$object->project = clone $projectstatic;
	//if ($ret < 0) dol_print_error($db,$projectstatic->error);
}

// Security check
if (! $user->rights->projet->lire) accessforbidden();

/*
 * Actions
 */

$error=0;

if (empty($backtopage)) $backtopage = dol_buildpath('/wip/report_card.php',1).'?id='.($id > 0 ? $id : '__ID__');

// None

/*
 * View
 *
 * Put here all code to render page
 */

$form		= new Form($db);

// wip_get_report_head parameters
$page_name	= "Instructions";
$page_title	= $langs->trans($page_name);
$help_url	= '';
$target		= '';
$tabactive	= GETPOST('tab')?GETPOST('tab'):'report_about';
$tabtitle	= $langs->trans($page_name);
$withproject= 1;

// Output page
// --------------------------------------------------------------------
wip_get_report_head($object, $projectstatic, $page_title, $help_url, $target, $tabactive, $tabtitle, $withproject);

// *******************************************************

print load_fiche_titre($langs->trans($page_name), '', 'title_setup.png');

$text = 'Hello World';

wip_awesome_character('1');
print $langs->trans("Step1Description").' ';
print $langs->trans("AreaForAdminOnly").' ';
print $langs->trans("Step2Description", $langs->transnoentities("Modules")).'<br><br>';

print '<br>';

// Show info setup company
wip_awesome_character('2');
print $text.'<br>';
$setupcompanynotcomplete=1;
print img_picto('','puce').' '.$langs->trans("SetupDescription3", DOL_URL_ROOT.'/admin/company.php?mainmenu=home'.(empty($setupcompanynotcomplete)?'':'&action=edit'), $langs->transnoentities("Setup"), $langs->transnoentities("MenuCompanySetup"));
print img_picto('','puce').' '.$langs->trans("SetupDescription3", DOL_URL_ROOT.'/admin/company.php?mainmenu=home'.(empty($setupcompanynotcomplete)?'':'&action=edit'), $langs->transnoentities("Setup"), $langs->transnoentities("MenuCompanySetup"));

print '<br>';
print '<br>';
wip_awesome_character('3');
print $text.'<br>';
if (! empty($setupcompanynotcomplete))
{
	$langs->load("errors");
	$warnpicto=img_warning($langs->trans("WarningMandatorySetupNotComplete"), 'style="padding-right: 6px;"');
	print '<br><div class="warning"><a href="'.DOL_URL_ROOT.'/admin/company.php?mainmenu=home'.(empty($setupcompanynotcomplete)?'':'&action=edit').'">'.$warnpicto.$langs->trans("WarningMandatorySetupNotComplete").'</a></div>';
}
print '<br>';
wip_awesome_character('4');
print $text.'<br>';
// Show info setup module
print img_picto('','puce').' '.$langs->trans("Step4Description", DOL_URL_ROOT.'/admin/modules.php?mainmenu=home', $langs->transnoentities("Setup"), $langs->transnoentities("Modules"));

/*
$nbofactivatedmodules=count($conf->modules);
$moreinfo=$langs->trans("TotalNumberOfActivatedModules",($nbofactivatedmodules-1), count($modules));
if ($nbofactivatedmodules <= 1) $moreinfo .= ' '.img_warning($langs->trans("YouMustEnableOneModule"));
print '<br>'.$moreinfo;
*/

print '<br>';
wip_awesome_character('5');
print $text.'<br>';
print '<div class="info hideonsmartphone"><span class="fa fa-info-circle" title="'.dol_escape_htmltag($langs->trans('Note')).'"></span> '.$text.' </div>';

print '<br>';
print '<br>';
print '<br>';
// Add hook to add information
$parameters=array();
$reshook=$hookmanager->executeHooks('addHomeSetup',$parameters,$object,$action);	// Note that $action and $object may have been modified by some hooks
print $hookmanager->resPrint;
if (empty($reshook))
{
	// Show into other
	wip_awesome_character('6');
	print $langs->trans("Step6Description")."<br>";
	print "<br>";

	// Show logo
	print '<div class="center"><div class="logo_setup"></div></div>';
}
dol_fiche_end();						// card banner
if ($withproject == 1) dol_fiche_end();	// project banner
// End of page
llxFooter();
$db->close();
