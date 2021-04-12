<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2019 Peter Roberts <webmaster@finchmc.com.au>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *  \file	   htdocs/wip/report_document.php
 *  \ingroup	wip
 *  \brief	  Tab for documents linked to Report
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
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
dol_include_once('/wip/class/report.class.php');
dol_include_once('/wip/lib/wip_report.lib.php');
//require_once DOL_DOCUMENT_ROOT.'/product/class/propalmergepdfproduct.class.php';

// Load translation files required by the page
$langs->loadLangs(array("wip@wip","companies","other","mails"));

$id			= (GETPOST('socid','int') ? GETPOST('socid','int') : GETPOST('id','int'));
$ref		= GETPOST('ref', 'alpha');
$action		= GETPOST('action','aZ09');
$confirm	= GETPOST('confirm');

// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$result = restrictedArea($user, 'wip', $id);

// Get parameters
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if (empty($page) || $page == -1) { $page = 0; }	 // If $page is not defined, or '' or -1
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="ASC";
//if (! $sortfield) $sortfield="name";
if (! $sortfield) $sortfield="position_name";

// Initialize technical objects
$object = new Report($db);
$projectstatic = new Project($db);
$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->wip->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('reportdocument','globalcard'));	 // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('report');

// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once  // Must be include, not include_once. Include fetch and fetch_thirdparty but not fetch_optionals

if ($id > 0 || ! empty($ref))
{
	$result = $projectstatic->fetch($object->fk_project);
//	$projectstatic->fetch_thirdparty();

	$objref = dol_sanitizeFileName($object->ref);
	$projref = dol_sanitizeFileName($projectstatic->ref);
	$upload_dir = $conf->projet->dir_output.'/'.$projref.'/Images';
/*
		$fname = $objref.' '.$projref;
		$fname.= (! empty($psociete->name)?(' - '.trim(dol_trunc($psociete->name,20,'right','UTF-8', 1, 0))):'');
		$fname.= (! empty($projectstatic->title)?(' - '.trim(dol_trunc($projectstatic->title,24,'right','UTF-8', 1, 0))):'');
		$filename = trim(dol_sanitizeFileName($fname));

		$relativepath = '../../documents/projet/' . $projref .'/Images/' . $filename . '.pdf';
		$filedir = $conf->projet->dir_output . '/' . $projref .'/Images';
		$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
		$urlsource .= '&withproject=1';*/
}

/*
 * Actions
 */
if (GETPOST('gotolist','int') > 0)	// For next/prev to function we need to redirect to the list of project reports.
{
	$newurl = dol_buildpath('/wip/report_list.php',1).'?ref='.GETPOST('project','alpha');
	header("Location: ".$newurl);
	exit;
}

	// Action submit/delete file/link
	include_once DOL_DOCUMENT_ROOT . '/core/actions_linkedfiles.inc.php';


/*
 *	View
 */

$form = new Form($db);

$title = $langs->trans("Report").' - '.$langs->trans("Files");
$help_url = '';
//$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('', $title, $help_url);

if ($object->id)
{
	/*
	 * Show tabs
	 */
	$head	= reportPrepareHead($object);
	$title	= $langs->trans("Report");
	$picto	= 'report@wip';

//	dol_fiche_head($head, 'document', $langs->trans("Report"), -1, 'report@wip');
	dol_fiche_head($head, 'documents', $title, -1, $picto);


	// Build file list
	$filearray=dol_dir_list($upload_dir,"files",0,'','(\.meta|_preview.*\.png)$',$sortfield,(strtolower($sortorder)=='desc'?SORT_DESC:SORT_ASC),1);
	$totalsize=0;
	foreach($filearray as $key => $file)
	{
		$totalsize+=$file['size'];
	}

	// Object card
	// ------------------------------------------------------------
	$linkback = '<a href="' .dol_buildpath('/wip/report_list.php',1) . '?restore_lastsearch_values=1' . (! empty($socid) ? '&socid=' . $socid : '') . '">' . $langs->trans("BackToList") . '</a>';

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

	print '<div class="fichecenter">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">';

	// Number of files
	print '<tr><td class="titlefield">'.$langs->trans("NbOfAttachedFiles").'</td><td colspan="3">'.count($filearray).'</td></tr>';

	// Total size
	print '<tr><td>'.$langs->trans("TotalSizeOfAttachedFiles").'</td><td colspan="3">'.$totalsize.' '.$langs->trans("bytes").'</td></tr>';

	print '</table>';

	print '</div>';
	print '<div style="clear:both"></div>';

	dol_fiche_end();

	$modulepart = '';

	//$permission = $user->rights->wip->create;
	$permission = 1;
	//$permtoedit = $user->rights->wip->create;
	$permtoedit = 1; // PJR TODO Photos resize action will not work for non-core $modulepart

	$param = '&id=' . $object->id;


//	include_once DOL_DOCUMENT_ROOT . '/core/tpl/document_actions_post_headers.tpl.php';
// ====================================================

	$langs->load("link");
	if (empty($relativepathwithnofile)) $relativepathwithnofile='';
	if (empty($permtoedit)) $permtoedit=-1;
	
	// Drag and drop for up and down allowed on product, thirdparty, ...
	// The drag and drop call the page core/ajax/row.php
	// If you enable the move up/down of files here, check that page that include template set its sortorder on 'position_name' instead of 'name'
	// Also the object->fk_element must be defined.
	$disablemove=0;
	
	/*
	 * Confirm form to delete
	 */
	
	if ($action == 'delete')
	{
		$langs->load("companies");	// Need for string DeleteFile+ConfirmDeleteFiles
		print $form->formconfirm(
				$_SERVER["PHP_SELF"] . '?id=' . $object->id . '&urlfile=' . urlencode(GETPOST("urlfile")) . '&linkid=' . GETPOST('linkid', 'int') . (empty($param)?'':$param),
				$langs->trans('DeleteFile'),
				$langs->trans('ConfirmDeleteFile'),
				'confirm_deletefile',
				'',
				0,
				1
		);
	}
	
	$formfile=new FormFile($db);
	
	// We define var to enable the feature to add prefix of uploaded files
	$savingdocmask='';
	$savingdocmask=dol_sanitizeFileName($object->ref).'_'.'__file__';

	$withproject = 1;	

	// Show upload form (document and links)
	$formfile->form_attach_new_file(
		$_SERVER["PHP_SELF"].'?id='.$object->id.(empty($withproject)?'':'&withproject=1'),
		'',
		0,
		0,
		1,
		$conf->browser->layout == 'phone' ? 40 : 60,
		$object,
		'',
		1,
		$savingdocmask
	);

	$modulepart = 'project';
	$relativepathwithnofile = '/'.dol_sanitizeFileName($projref).'/Images/';

	// List of document
	$formfile->list_of_documents(
		$filearray,
		$object,
		$modulepart,
		$param,
		0,
		$relativepathwithnofile,		// relative path with no file. For example "0/1"
		$permission,
		0,
		'',
		0,
		'',
		'',
		0,
		$permtoedit,
		$upload_dir,
		$sortfield,
		$sortorder,
		$disablemove
	);

}
else
{
	accessforbidden('',0,0);
}

// End of page
llxFooter();
$db->close();
