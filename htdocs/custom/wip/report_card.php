<?php
/* Copyright (C) 2017	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2019	Peter Roberts		<webmaster@finchmc.com.au>
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
 *   	\file		htdocs/custom/wip/report_card.php
 *		\ingroup	wip
 *		\brief		Page to create/edit/view report
 */

//if (! defined('NOREQUIREDB'))				define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))			define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))			define('NOREQUIRESOC','1');					// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))			define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))	define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))	define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))				define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))			define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))			define('NOSTYLECHECK','1');					// Do not check style html tag into posted data
//if (! defined('NOREQUIREMENU'))			define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))			define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))			define('NOREQUIREAJAX','1');	   	  		// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))					define("NOLOGIN",'1');						// If this page is public (can be called outside logged session). This include the NOIPCHECK too.
//if (! defined('NOIPCHECK'))				define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined("MAIN_LANG_DEFAULT"))		define('MAIN_LANG_DEFAULT','auto');			// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE"))define('MAIN_AUTHENTICATION_MODE','aloginmodule');		// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))	define('NOREDIRECTBYMAINTOLOGIN',1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message
//if (! defined("FORCECSP"))				define('FORCECSP','none');					// Disable all Content Security Policies

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
//require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
//require_once DOL_DOCUMENT_ROOT.'/core/modules/project/task/modules_task.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formorder.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

dol_include_once('/wip/class/actions_wip.class.php');
dol_include_once('/wip/class/report.class.php');
dol_include_once('/wip/class/reportdet.class.php');
dol_include_once('/wip/lib/wip_report.lib.php');
dol_include_once('/wip/core/modules/modules_wipreport.php');

/*
require_once DOL_DOCUMENT_ROOT.'/core/lib/fourn.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';*/

require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

if (!empty($conf->produit->enabled))
	require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';

// Load translation files required by the page
$langs->loadLangs(array('wip@wip', 'other', 'projects', 'companies'));

// Get parameters
$id				= GETPOST('id', 'int');
$ref			= GETPOST('ref', 'alpha');
$reportref		= GETPOST('reportref','alpha');
$action			= GETPOST('action','aZ09')?GETPOST('action','aZ09'):'view';	// The action 'add', 'create', 'edit', 'update', 'view', ...
$confirm		= GETPOST('confirm', 'alpha');
$cancel     	= GETPOST('cancel', 'aZ09');								// We click on a Cancel button
$toselect		= GETPOST('toselect', 'array');
$withproject	= GETPOST('withproject','int');
//$withproject = 1;	// PJR TODO set for testing
$projectid		= GETPOST('projectid','int');
$project_ref	= GETPOST('project_ref','alpha');
$lineid		 	= GETPOST('lineid', 'int');
//$packetid		= GETPOST('packetid', 'int');
$packet_label	= GETPOST('packet_label','alpha');
$contextpage	= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):'reportcard';   // To manage different context of search
$backtopage		= GETPOST('backtopage','alpha');							// Go back to a dedicated page
$massaction		= GETPOST('massaction', 'alpha');
//$model			= GETPOST('model', 'alpha');

// PDF
$hidedetails = (GETPOST('hidedetails','int') ? GETPOST('hidedetails','int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS) ? 1 : 0));
$hidedesc	= (GETPOST('hidedesc','int') ? GETPOST('hidedesc','int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC) ? 1 : 0));
$hideref	= (GETPOST('hideref','int') ? GETPOST('hideref','int') : (! empty($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF) ? 1 : 0));
$hidedetails = 1;// PJR TODO for testing
$hidedesc = 0;// PJR TODO for testing

// Initialise technical objects
$object = new Report($db);
$extrafields = new ExtraFields($db);
$projectstatic = new Project($db);

//$diroutputmassaction=$conf->wip->dir_output . '/temp/massgeneration/'.$user->id;
$diroutputmassaction=$conf->wip->dir_output . '/temp/massgeneration/';
$default_spare_text = '-spare-';

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('reportcard','globalcard'));

// Fetch optional attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);

$parameters = array();

if (GETPOST('cancel','alpha')) { $action=''; $massaction=''; }
if (! GETPOST('confirmmassaction','alpha') && $massaction != 'presend' && $massaction != 'confirm_presend' && $massaction != 'confirm_move2task' && $massaction != 'confirm_move2packet') { $massaction=''; } // PJR TODO

if (empty($action) && empty($id) && empty($ref)) $action='view';

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

// Security check - Protection if external user
//if ($user->societe_id > 0) access_forbidden();
//if ($user->societe_id > 0) $socid = $user->societe_id;
//$isdraft = (($object->statut == Report::STATUS_DRAFT) ? 1 : 0);
//$result = restrictedArea($user, 'wip', $id, '', '', 'fk_soc', 'rowid', null, $isdraft);

$permissiontoadd	= $user->rights->wip->write;
$permissiontodelete	= $user->rights->wip->delete;

$permissionnote		= $user->rights->wip->write;	// Used by the include of actions_setnotes.inc.php
$permissiondellink	= $user->rights->wip->write;	// Used by the include of actions_dellink.inc.php
$permissiontoedit	= $user->rights->wip->write;	// Used by the include of actions_lineupdown.inc.php

 /*	===========================================================================	*/
 /*																				*/
 /*	Actions																		*/
 /*																				*/
 /*	===========================================================================	*/

$error=0;

if (empty($backtopage)) $backtopage = dol_buildpath('/wip/report_card.php',1).'?id='.($id > 0 ? $id : '__ID__');

//	include DOL_DOCUMENT_ROOT.'/core/actions_setnotes.inc.php';		// Must be include, not include_once
//	include DOL_DOCUMENT_ROOT.'/core/actions_dellink.inc.php';		// Must be include, not include_once
	include DOL_DOCUMENT_ROOT.'/core/actions_lineupdown.inc.php';	// Must be include, not include_once

/*
 * Cancel
 */
if ($cancel)
{
	if (! empty($backtopage))
	{
		header("Location: ".$backtopage);
		exit;
	}
	$action = '';
}

// Do action
$actionobject = new ActionsWIP($db);
$actionobject->doActions($parameters, $object, $action, $hookmanager);
$ret = $object->fetch($id); // Reload to get updated records

/*
 * Create a Report
 */
if ($action == 'createreport' && $user->rights->wip->write)
{
	$error=0;

    // If we use user timezone, we must change also view/list to use user timezone everywhere
    //$date_start = dol_mktime($_POST['dateohour'],$_POST['dateomin'],0,$_POST['dateomonth'],$_POST['dateoday'],$_POST['dateoyear'],'user');
	//$date_end = dol_mktime($_POST['dateehour'],$_POST['dateemin'],0,$_POST['dateemonth'],$_POST['dateeday'],$_POST['dateeyear'],'user');
	$date_report	= dol_mktime(0,0,0,$_POST['date_report_month'],$_POST['date_report_day'],$_POST['date_report_year']);
	$date_planned	= dol_mktime(0,0,0,$_POST['date_planned_month'],$_POST['date_planned_day'],$_POST['date_planned_year']);
	$date_start		= dol_mktime(0,0,0,$_POST['date_start_month'],$_POST['date_start_day'],$_POST['date_start_year']);
	$date_end		= dol_mktime(0,0,0,$_POST['date_end_month'],$_POST['date_end_day'],$_POST['date_end_year']);

	if (! $cancel)
	{
		if (empty($reportref))
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
			$action='create';
			$error++;
		}
	    if (empty($_POST['label']))
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), null, 'errors');
			$action='create';
			$error++;
		}
		if (empty($_POST['projectid']))
		{
			setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Project")), null, 'errors');
			$action='create';
			$error++;
		}

		if (! $error)
		{
			// Create report
			$db->begin();

			//$object->rowid;
			$object->ref				= $reportref?$reportref:GETPOST("reportref",'alpha',2);
			//$object->entity;
			$object->label				= $_POST['label'];

			$object->amount				= 0;
			$object->discounted_amount	= 0;

			$object->fk_project			= GETPOST('projectid','int');
			$object->fk_user_author		= $user->id;
			$object->date_report		= $date_report;
			$object->date_planned		= $date_planned;
			$object->date_start			= $date_start;
			$object->date_end			= $date_end;

			$object->sec1_title			= $_POST['sec1_title'];
			$object->sec2_title			= $_POST['sec2_title'];
			$object->sec3_title			= $_POST['sec3_title'];
			$object->sec4_title			= $_POST['sec4_title'];
			$object->sec5_title			= $_POST['sec5_title'];
			$object->sec6_title			= $default_spare_text?'':$_POST['sec6_title'];

			$object->sec1_description	= $_POST['descr_sec1'];
			$object->sec3_description	= $_POST['descr_sec3'];
			$object->sec4_description	= $_POST['descr_sec4'];
			$object->sec5_description	= $_POST['descr_sec5'];
			$object->sec6_description	= $default_spare_text?'':$_POST['descr_sec6'];

			//$object->note_private;
			//$object->note_public;
			//$object->model_pdf;
			//$object->date_creation;

			//$object->tms;
			$object->fk_user_creat		= $user->id;
			//$object->fk_user_modif;
			//$object->import_key;
			$object->status				= 0;

			// Fill array 'array_options' with data from add form
			$ret = $extrafields->setOptionalsFromPost($extralabels,$object);
			if ($ret < 0) $error++;

			$id = $object->create($user);

			if (! $error && $id > 0)
			{
				// Something
			}
			else
			{
				if ($db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS')
				{
					$langs->load("projects");
					setEventMessages($langs->trans('NewReportRefSuggested'),'', 'warnings');
					$duplicate_code_error = true;
				}
				else
				{
					$langs->load("errors");
					setEventMessages($langs->trans($object->error), null, 'errors');
				}
				$action = 'create';
				$error++;
			}
		}
		if (! $error)
		{
			$db->commit();

			if (! empty($backtopage))
			{
				header("Location: ".$backtopage);
				exit;
			}
			else
			{
				header("Location: ".$_SERVER['PHP_SELF']."?id=".$id);
				exit;
			}
		}
		else
		{
			$langs->load("errors");
			$db->rollback();
			$action = 'create';
			header('Location: '.$_SERVER['PHP_SELF'].'?projectid='.GETPOST('projectid','int').'&action=create&withproject=1&backtopage='.urlencode($backtopage));
			exit;
		}
	}
	else
	{
		$action = 'create';
	}
}

/*
 * Update
 */
if ($action == 'update' && ! $_POST["cancel"] && $user->rights->projet->creer)
{
	$error=0;

	if (empty($reportref))
	{
		$error++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Ref")), null, 'errors');
	}
	if (empty($_POST["label"]))
	{
		$error++;
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Label")), null, 'errors');
	}
	if (! $error)
	{
		$object->fetch($id,$ref);
		$object->oldcopy = clone $object;

		$tmparray=explode('_',$_POST['task_parent']);
		$task_parent=$tmparray[1];
		if (empty($task_parent)) $task_parent = 0;	// If task_parent is ''

		$object->ref = $reportref?$reportref:GETPOST("ref",'alpha',2);
		$object->label = $_POST["label"];
		$object->description = $_POST['report_desc'];

		$object->sec1_title = $_POST['sec1_title'];
		$object->sec2_title = $_POST['sec2_title'];
		$object->sec3_title = $_POST['sec3_title'];
		$object->sec4_title = $_POST['sec4_title'];
		$object->sec5_title = $_POST['sec5_title'];
		$object->sec6_title = $_POST['sec6_title'];

		$object->sec1_description = $_POST['sec1_description'];
		$object->sec3_description = $_POST['sec3_description'];
		$object->sec4_description = $_POST['sec4_description'];
		$object->sec5_description = $_POST['sec5_description'];
		$object->sec6_description = $_POST['sec6_description'];

		// Fill array 'array_options' with data from add form
		$ret = $extrafields->setOptionalsFromPost($extralabels,$object);
		if ($ret < 0) $error++;

		if (! $error)
		{
			$result=$object->update($user);
			if ($result < 0)
			{
				setEventMessages($object->error, $object->errors, 'errors');
			}
		}
	}
	else
	{
		$action='edit';
	}
}

/*
 * Confirm Delete
 */
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->supprimer)
{
	if ($object->fetch($id,$ref) >= 0)
	{
		$result=$projectstatic->fetch($object->fk_project);
		$projectstatic->fetch_thirdparty();

		if ($object->delete($user) > 0)
		{
			header('Location: '.DOL_URL_ROOT.'/projet/tasks.php?restore_lastsearch_values=1&id='.$projectstatic->id.($withproject?'&withproject=1':''));
			exit;
		}
		else
		{
			setEventMessages($object->error,$object->errors,'errors');
			$action='';
		}
	}
}

/*
 * Build doc
 */
if ($action == 'builddoc' && $user->rights->projet->creer)
{
	$object->fetch($id,$ref);

	// Save last template used to generate document
	if (GETPOST('model')) $object->setDocModel($user, GETPOST('model','alpha'));

	$outputlangs = $langs;
	if (GETPOST('lang_id','aZ09'))
	{
		$outputlangs = new Translate("",$conf);
		$outputlangs->setDefaultLang(GETPOST('lang_id','aZ09'));
	}
	$result= $object->generateDocument($object->modelpdf, $outputlangs);
	if ($result <= 0)
	{
		setEventMessages($object->error, $object->errors, 'errors');
		$action='';
	}
}

// Delete file in doc form
if ($action == 'remove_file' && $user->rights->projet->creer)
{
	require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

	if ($object->fetch($id,$ref) >= 0 )
	{
		$langs->load("other");
		$upload_dir = $conf->projet->dir_output;
		$file = $upload_dir . '/' . GETPOST('file');

		$ret=dol_delete_file($file);
		if ($ret) setEventMessages($langs->trans("FileWasRemoved", GETPOST('urlfile')), null, 'mesgs');
		else setEventMessages($langs->trans("ErrorFailToDeleteFile", GETPOST('urlfile')), null, 'errors');
//		$action = '';
	}
}

/*
 * OLD Actions
 */

if ($action == 'reopen')	// no test on permission here, permission to use will depends on status
{
	if (in_array($object->statut, array(1, 2, 3, 4, 5, 6, 7, 9)))
	{
		if ($object->statut == 1) $newstatus=0;	// Validated->Draft
		else if ($object->statut == 2) $newstatus=0;	// Approved->Draft
		else if ($object->statut == 3) $newstatus=2;	// Ordered->Approved
		else if ($object->statut == 4) $newstatus=3;
		else if ($object->statut == 5)
		{
			//$newstatus=2;	// Ordered
			// TODO Can we set it to submited ?
			//$newstatus=3;  // Submited
			// TODO If there is at least one reception, we can set to Received->Received partially
			$newstatus=4;  // Received partially

		}
		else if ($object->statut == 6) $newstatus=2;	// Canceled->Approved
		else if ($object->statut == 7) $newstatus=3;	// Canceled->Process running
		else if ($object->statut == 9) $newstatus=1;	// Refused->Validated
		else $newstatus = 2;

		//print "old status = ".$object->statut.' new status = '.$newstatus;
		$db->begin();

		$result = $object->setStatus($user, $newstatus);
		if ($result > 0)
		{
			// Currently the "Re-open" also remove the billed flag because there is no button "Set unpaid" yet.
			$sql = 'UPDATE '.MAIN_DB_PREFIX.'commande_fournisseur';
			$sql.= ' SET billed = 0';
			$sql.= ' WHERE rowid = '.$object->id;

			$resql=$db->query($sql);

			if ($newstatus == 0)
			{
				$sql = 'UPDATE '.MAIN_DB_PREFIX.'commande_fournisseur';
				$sql.= ' SET fk_user_approve = null, fk_user_approve2 = null, date_approve = null, date_approve2 = null';
				$sql.= ' WHERE rowid = '.$object->id;

				$resql=$db->query($sql);
			}

			$db->commit();

			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
			exit;
		}
		else
		{
			$db->rollback();

			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
}

/*
 * Classify as billed
 */
if ($action == 'classifybilled' && $user->rights->wip->write)
{
	$ret=$object->classifyBilled($user);
	if ($ret < 0) {
		setEventMessages($object->error, $object->errors, 'errors');
	}
}

/*
 * Add a new line
 * Selects from existing Time Packets to add to Report
 */
// ------------------------------------------------------------
if ($action == 'addline' && $user->rights->wip->write)
{
	$db->begin();

	$langs->load('errors');
	$error = 0;
	$tmparray=explode('_',GETPOST('addpacket_id'));
	$addpacket_id=$tmparray[1];
	$maxrang = 0;
	$numpckts = 0;

	// Find max Rang
	$sql = 'SELECT MAX(rang) AS rmax FROM '.MAIN_DB_PREFIX.'wip_reportdet WHERE fk_report = '. $id ;
	$res=$db->query($sql);
	$objres = $db->fetch_object($res);
	$maxrang = $objres->rmax;
	$db->free($res);

	// Find current number of packets in report
	$sql = 'SELECT COUNT(rowid) AS pcount FROM '.MAIN_DB_PREFIX.'wip_reportdet WHERE fk_report = '. $id ;
	$res=$db->query($sql);
	$objres = $db->fetch_object($res);
	$numpckts = $objres->pcount;
	$db->free($resql);

	// Set Rang of new packet to be one greater than maximum of Count and Max(rang) to be sure it will be sorted to the end
	$newrang = max($numpckts, $maxrang) + 1;

	if (! $error) {
		$sql = 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet SET fk_report = '. $id .', rang = '. $newrang .', fk_user_modif = '.$user->id.', status = 1 WHERE rowid IN('.$addpacket_id.')';
		$res=$db->query($sql);
		if (! res) {
			setEventMessages($sql, null, 'errors');
			$error++;
			$db->rollback();
		} else {
			$db->commit();
			setEventMessages($langs->trans("TimePacketAdded"), null, 'mesgs');
			$action='';
			header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
			exit;
		}

	} else {
		setEventMessages($langs->trans("NoRecordsMoved"), null, 'errors');
	}
}


/*
 *	Updating a line in the report
 */
//if ($action == 'updateline' && $user->rights->wip->write &&	! GETPOST('cancel','alpha'))
if ($action == 'updateline' && (! empty($lineid)) && $user->rights->wip->write && GETPOST('save'))
{
	$line = new ReportDet($db);
	$res = $line->fetch($lineid);
	if (!$res) dol_print_error($db);

	$rowid					= $lineid;
	//$fk_report,			// will not be changed by updateline
	//$fk_task,				// will not be changed by updateline
	$newparentarray			= explode('_',$_POST['newparentpkt_id']);
	$fk_parent_line			= ($newparentarray[1] >0 ? $newparentarray[1] :'NULL');
	//$fk_assoc_line,		// will not be changed by updateline // PJR TODO not used
	$servicearray			= explode('_',$_POST['serviceid']);
	$fk_product				= $servicearray[0];
	
	//Look for "Price" value in GETPOST. If set with null value set to 0. 
	if(GETPOSTISSET('price')){
		$temp_price = GETPOST('price','int');
		if($temp_price==null||$temp_price==""){
			if($line->price==null||$line->price==null){
				$price = 0;
			}
			else{
			 $price = $line->price;
			}
		}
		else{
			$price = $temp_price;
		}
	}
	else{
		$price = 0;
	}
	
	echo "<br><br>Price: ".$price."<br><br>";
	//$price					= GETPOST('price','int')?GETPOST('price','int'):$line->price;
	$product_type			= 1;	// Is labour effort and hence a Service (type 1)
	//$ref,					// will not be changed by updateline // PJR TODO not used
	$label					= GETPOST('packet_label','alpha')?GETPOST('packet_label','alpha'):$line->label;

	// Clean parameters
//	$date_start				= $line->date_start;
//	$date_end				= $line->date_end;
//	if (GETPOST('date_startmonth')>0 && GETPOST('date_startday')>0 && GETPOST('date_startyear')>0)
		$date_start			= dol_mktime(0,0,0, GETPOST('date_startmonth'), GETPOST('date_startday'), GETPOST('date_startyear'));
//	if (GETPOST('date_endmonth')>0 && GETPOST('date_endday')>0 && GETPOST('date_endyear')>0)
		$date_end			= dol_mktime(0,0,0, GETPOST('date_endmonth'), GETPOST('date_endday'), GETPOST('date_endyear'));
	$description			= GETPOST('packet_desc','alpha')?GETPOST('packet_desc','alpha'):$line->description;
	$qty					= $line->qty;
	//$discount_percent		= 0; // see below
	//$discounted_qty		= $line->qty; // see below
	//		$special_code,
	//		$rang,
	//		$rang_task,
	//		$date_creation;
	//		$tms;
	//		$fk_user_creat;
	$fk_user_modif			= $user->id;
	//		$import_key;
	$direct_amortised		= $line->direct_amortised;
	if (! GETPOST('DirectStatut','int') == $line->direct_amortised)
		$direct_amortised	= GETPOST('DirectStatut','int');
	//$billable				= 1; // see below
	$work_type				= $line->work_type;
	if (! GETPOST('WorkTypeStatut','int') == $line->work_type)
		$work_type			= GETPOST('WorkTypeStatut','int');
	if (in_array($work_type, array(5, 6)))
	{
		$billable			= 0;
		$discount_percent	= 100;
		$discounted_qty		= 0;
	} elseif (in_array($work_type, array(1, 2))) {
		$billable			= 2;
		$discount_percent	= GETPOST('discount_percent','int')?GETPOST('discount_percent','int'):$line->discount_percent;
		$discounted_qty		= ceil($qty * (100 - $discount_percent))/100; // rounds up to two decimal figures
	} else {
		$billable			= 1;
		$discount_percent	= 0;
		$discounted_qty		= $line->qty;
	}
	$status					= 1;

	$result	= $line->updateline($user,
		$rowid,
		$fk_report,
		$fk_task,
		$fk_parent_line,
		$fk_assoc_line,
		$fk_product,
		$price,
		$product_type,
		$ref,
		$label,
		$date_start,
		$date_end,
		$description,
		$qty,
		$discount_percent,
		$discounted_qty,
		$special_code,
		$rang,
		$rang_task,
		$fk_user_creat,
		$fk_user_modif,
		$import_key,
		$direct_amortised,
		$billable,
		$work_type,
		$status
	);

//	unset($_POST['action']);
	unset($_POST['lineid']);

/*
	unset($_POST['qty']);
	unset($_POST['type']);
	unset($_POST['idprodfournprice']);
	unset($_POST['remmise_percent']);
	unset($_POST['dp_desc']);
	unset($_POST['np_desc']);
	unset($_POST['pu']);
	unset($_POST['fourn_ref']);
	unset($_POST['tva_tx']);
	unset($_POST['date_start']);
	unset($_POST['date_end']);
	unset($_POST['units']);
	unset($localtax1_tx);
	unset($localtax2_tx);

	unset($_POST['date_starthour']);
	unset($_POST['date_startmin']);
	unset($_POST['date_startsec']);
	unset($_POST['date_startday']);
	unset($_POST['date_startmonth']);
	unset($_POST['date_startyear']);
	unset($_POST['date_endhour']);
	unset($_POST['date_endmin']);
	unset($_POST['date_endsec']);
	unset($_POST['date_endday']);
	unset($_POST['date_endmonth']);
	unset($_POST['date_endyear']);
*/

	if ($result	>= 0)
	{
		// Define output language
		if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
		{
			$outputlangs = $langs;
			$newlang = '';
			if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang = GETPOST('lang_id','aZ09');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
			if (! empty($newlang)) {
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
			$model=$object->modelpdf;
			$ret = $object->fetch($id); // Reload to get new records

			$result=$object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
			if ($result < 0) dol_print_error($db,$result);
		}
	$action = '';

	header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
	exit;
	}
	else
	{
	dol_print_error($db,$object->error);
	exit;
	}
}

/*
 *	Remove a line in the report
 */
if ($action == 'confirm_removeline' && (! empty($lineid)) && $confirm == 'yes' && $user->rights->wip->write)
{
	$db->begin();
	$langs->load('errors');
	$error = 0;
	$sql = 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet SET fk_report = NULL, fk_user_modif = '.$user->id.', status = 0 WHERE rowid = '.$lineid;
	$res=$db->query($sql);
	if (! res) {
		setEventMessages($sql, null, 'errors');
		$error++;
		$db->rollback();
	} else {
		$db->commit();
		setEventMessages($langs->trans("TimePacketRemoved"), null, 'mesgs');
		$action='';
		header('Location: '.$_SERVER["PHP_SELF"].'?id='.$object->id);
		exit;
	}
}

/*
 *	Validate the report
 */
if ($action == 'confirm_validate' && $confirm == 'yes' &&
	((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->wip->write))
	|| (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->fournisseur->supplier_order_advance->validate)))
	)
{
	$object->date_commande=dol_now();
	$result = $object->valid($user);
	if ($result	>= 0)
	{
		// Define output language
		if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
		{
			$outputlangs = $langs;
			$newlang = '';
			if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang = GETPOST('lang_id','aZ09');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
			if (! empty($newlang)) {
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
			$model=$object->modelpdf;
			$ret = $object->fetch($id); // Reload to get new records

			$result=$object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
			if ($result < 0) dol_print_error($db,$result);
		}
	}
	else
	{
		setEventMessages($object->error, $object->errors, 'errors');
	}

	// If we have permission, and if we don't need to provide the idwarehouse, we go directly on approved step
	if (empty($conf->global->SUPPLIER_ORDER_NO_DIRECT_APPROVE) && $user->rights->wip->approuver && ! (! empty($conf->global->STOCK_CALCULATE_ON_SUPPLIER_VALIDATE_ORDER) && $object->hasProductsOrServices(1)))
	{
		$action='confirm_approve';	// can make standard or first level approval also if permission is set
	}
}

/*
 *	Approve the report
 */
if (($action == 'confirm_approve' || $action == 'confirm_approve2') && $confirm == 'yes' && $user->rights->wip->approuver)
{
	$idwarehouse=GETPOST('idwarehouse', 'int');

	$qualified_for_stock_change=0;
	if (empty($conf->global->STOCK_SUPPORTS_SERVICES))
	{
		$qualified_for_stock_change=$object->hasProductsOrServices(2);
	}
	else
	{
		$qualified_for_stock_change=$object->hasProductsOrServices(1);
	}

	// Check parameters
	if (! empty($conf->stock->enabled) && ! empty($conf->global->STOCK_CALCULATE_ON_SUPPLIER_VALIDATE_ORDER) && $qualified_for_stock_change)	// warning name of option should be STOCK_CALCULATE_ON_SUPPLIER_APPROVE_ORDER
	{
		if (! $idwarehouse || $idwarehouse == -1)
		{
			$error++;
			setEventMessages($langs->trans('ErrorFieldRequired',$langs->transnoentitiesnoconv("Warehouse")), null, 'errors');
			$action='';
		}
	}

	if (! $error)
	{
		$result	= $object->approve($user, $idwarehouse, ($action=='confirm_approve2'?1:0));
		if ($result > 0)
		{
			if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
				$outputlangs = $langs;
				$newlang = '';
				if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang = GETPOST('lang_id','aZ09');
				if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
				if (! empty($newlang)) {
					$outputlangs = new Translate("", $conf);
					$outputlangs->setDefaultLang($newlang);
				}
				$object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
			}
			header("Location: ".$_SERVER["PHP_SELF"]."?id=".$object->id);
			exit;
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
}

/*
 *	Refuse the report
 */
if ($action == 'confirm_refuse' &&	$confirm == 'yes' && $user->rights->wip->approuver)
{
	$result = $object->refuse($user);
	if ($result > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]."?id=".$object->id);
		exit;
	}
	else
	{
		setEventMessages($object->error, $object->errors, 'errors');
	}
}

/*
 *	Confirm the report  // PJR TODO Check
 */
if ($action == 'confirm_commande' && $confirm	== 'yes' &&	$user->rights->wip->commander)
{
	$result = $object->commande($user, $_REQUEST["datecommande"],	$_REQUEST["methode"], $_REQUEST['comment']);
	if ($result > 0)
	{
		if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE))
		{
			$outputlangs = $langs;
			$newlang = '';
			if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id','aZ09')) $newlang = GETPOST('lang_id','aZ09');
			if ($conf->global->MAIN_MULTILANGS && empty($newlang))	$newlang = $object->thirdparty->default_lang;
			if (! empty($newlang)) {
				$outputlangs = new Translate("", $conf);
				$outputlangs->setDefaultLang($newlang);
			}
			$object->generateDocument($object->modelpdf, $outputlangs, $hidedetails, $hidedesc, $hideref);
		}
		$action = '';
		header("Location: ".$_SERVER["PHP_SELF"]."?id=".$object->id);
		exit;
	}
	else
	{
		setEventMessages($object->error, $object->errors, 'errors');
	}
}

/*
 *	Confirm Delete
 */
if ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->wip->supprimer)
{
	$result=$object->delete($user);
	if ($result > 0)
	{
		header("Location: ".DOL_URL_ROOT.'/fourn/commande/list.php?restore_lastsearch_values=1');
		exit;
	}
	else
	{
		setEventMessages($object->error, $object->errors, 'errors');
	}
}

/*
 *	Action cloning of object
 */
if ($action == 'confirm_clone' && $confirm == 'yes' && $user->rights->wip->write)
{
	if (1==0 && ! GETPOST('clone_content') && ! GETPOST('clone_receivers'))
	{
		setEventMessages($langs->trans("NoCloneOptionsSpecified"), null, 'errors');
	}
	else
	{
		if ($object->id > 0)
		{
			$result=$object->createFromClone();
			if ($result > 0)
			{
				header("Location: ".$_SERVER['PHP_SELF'].'?id='.$result);
				exit;
			}
			else
			{
				setEventMessages($object->error, $object->errors, 'errors');
				$action='';
			}
		}
	}
}

/*
 *	Set status of completion (complete, partial, ...)
 */
if ($action == 'livraison' && $user->rights->wip->receptionner)
{
	if (GETPOST("type") != '')
	{
		$date_liv = dol_mktime(GETPOST('rehour'),GETPOST('remin'),GETPOST('resec'),GETPOST("remonth"),GETPOST("reday"),GETPOST("reyear"));

		$result = $object->Livraison($user, $date_liv, GETPOST("type"), GETPOST("comment"));   // GETPOST("type") is 'tot', 'par', 'nev', 'can'
		if ($result > 0)
		{
			$langs->load("deliveries");
			setEventMessages($langs->trans("DeliveryStateSaved"), null);
			$action = '';
		}
		else if($result == -3)
		{
			setEventMessages($object->error, $object->errors, 'errors');
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}
	else
	{
		setEventMessages($langs->trans("ErrorFieldRequired", $langs->transnoentities("Delivery")), null, 'errors');
	}
}

if ($action == 'confirm_cancel' && $confirm == 'yes' &&	$user->rights->wip->commander)
{
	$result	= $object->cancel($user);
	if ($result > 0)
	{
		header("Location: ".$_SERVER["PHP_SELF"]."?id=".$object->id);
		exit;
	}
	else
	{
		setEventMessages($object->error, $object->errors, 'errors');
	}
}

/*
 *	Actions to build doc
 */
$upload_dir = $conf->projet->dir_output;
//$filedir = $conf->projet->dir_output . '/' . $projref .'/Reports';
$permissioncreate = $user->rights->wip->write;
include DOL_DOCUMENT_ROOT.'/core/actions_builddoc.inc.php';

/*
//  Modulebuilder
// Actions cancel, add, update, delete or clone
//	include DOL_DOCUMENT_ROOT.'/core/actions_addupdatedelete.inc.php';

// Actions when printing a doc from card
//	include DOL_DOCUMENT_ROOT.'/core/actions_printing.inc.php';

// Actions to send emails
$trigger_name='REPORT_SENTBYMAIL';
$autocopy='MAIN_MAIL_AUTOCOPY_REPORT_TO';		// used to know the automatic BCC to add
$trackid='report'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/actions_sendmails.inc.php';
*/

// Retreive First Task ID of Project if withproject is on to allow project prev next to work
if (! empty($project_ref) /*&& ! empty($withproject)*/)	// PJR TODO
{
	if ($projectstatic->fetch('',$project_ref) > 0)
	{
		$reportsarray=$object->getReportsArray($projectstatic->id, $socid, 0);
		if (count($reportsarray) > 0)
		{
			$id=$reportsarray[0]->id;
		}
		else
		{
			header("Location: ".DOL_URL_ROOT.'/custom/wip/report_card.php?id='.$projectstatic->id.(empty($mode)?'':'&mode='.$mode).'&freddo=ZZZ');
		}
	}
}

/*
 * View
 *
 * Put here all code to render page
 */

$form		= new Form($db);
$formfile	= new FormFile($db);
$formother	= new FormOther($db);
$formproject = new FormProjets($db);

$now = dol_now();

$help_url = '';
$page_title = ($object->ref?$langs->trans('Report').'-'.$object->ref:$langs->trans('NewReport'));

// Output page
// --------------------------------------------------------------------

llxHeader('', $page_title, $help_url);

if ($id > 0 || ! empty($ref) || $projectid > 0 || ! empty($projectref))
{
	/*
	 * Fiche projet en mode visu
	 * Project sheet in visual mode
 	 */
	if ($projectid > 0 || ! empty($projectref) )
	{
		//if (! empty($withproject))
		//{
		// Tabs for project
		$tab='reports';
		$head=project_prepare_head($projectstatic);
		dol_fiche_head($head, $tab, $langs->trans("Project"), -1, ($projectstatic->public?'projectpub':'project'), 0, '', '');

		$param='';

		// =================
		// Project Banner
		// =================
		$linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

		$morehtmlref='<div class="refidno">';
		// Title
		$morehtmlref.=$projectstatic->title;

		// Thirdparty
		if ($projectstatic->thirdparty->id > 0)
		{
			$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $projectstatic->thirdparty->getNomUrl(1, 'project');
		}
		$morehtmlref.='</div>';
		$moreparam='&gotolist=1'; // used to switch to list of projects

		// Define a complementary filter for search of next/prev ref.
		if (! $user->rights->projet->all->lire)
		{
			$objectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,0);
			$projectstatic->next_prev_filter=" rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
		}
//		dol_banner_tab($projectstatic, 'project_ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $moreparam);
		dol_banner_tab($projectstatic, 'project', $linkback, 1, 'ref', 'ref', $morehtmlref, $moreparam);

		print '<div class="underbanner clearboth"></div>';
//		dol_fiche_end();
		//}
	}
}

/*
 * Actions
*/

// To verify role of users
//$userAccess = $projectstatic->restrictedProjectArea($user); // We allow task affected to user even if a not allowed project
//$arrayofuseridoftask=$object->getListContactId('internal');

$head = reportPrepareHead($object);
$tab = GETPOST('tab')?GETPOST('tab'):'report_card';
$tabtitle=$langs->trans("Report");
$tabpicto=dol_buildpath('/wip/img/object_report.png',1);

// Section to create record
// ------------------------------------------------------------
//		if ($action == 'create' && $user->rights->projet->creer)	// CREATE
if ($action == 'create' || $action == 'createnilproject')	// CREATE
{
	/* =================
	 *
	 * Report card - CREATE
	 *
	 * =================
	 */
	//if ($projectid > 0 || ! empty($projectref)) print '<br>';

	print load_fiche_titre($langs->trans("NewReport"), '', $tabpicto, 1);
	if ($projectstatic->statut == 0 && ! $action == 'createnilproject')
	{
		print '<div class="warning">';
		$langs->load("errors");
		print $langs->trans("WarningProjectClosed");
		print '</div>';
	}
	else
	{
		print '<form action="'.$_SERVER['PHP_SELF'].'" method="POST">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="createreport">';
		print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';
		if (! empty($object->id)) print '<input type="hidden" name="id" value="'.$object->id.'">';
		if (! empty($projectstatic->id)) print '<input type="hidden" name="projectid" value="'.$projectstatic->id.'">';

		//dol_fiche_head('');

		print '<table class="noborder" width="100%">';

		$defaultref='';
		$obj = empty($conf->global->WIP_ADDON_NUMBER)?'mod_wip_reportnum':$conf->global->WIP_ADDON_NUMBER;
		if (! empty($conf->global->WIP_ADDON_NUMBER) && is_readable(DOL_DOCUMENT_ROOT ."/custom/wip/core/modules/".$conf->global->WIP_ADDON_NUMBER.".php"))
		{
			require_once DOL_DOCUMENT_ROOT ."/custom/wip/core/modules/".$conf->global->WIP_ADDON_NUMBER.'.php';
			$modReport = new $obj;
			$defaultref = $modReport->getNextValue($object->thirdparty,null);
		}

		if (is_numeric($defaultref) && $defaultref <= 0) $defaultref='';

		// Ref
		print '<tr><td class="titlefieldcreate"><span class="fieldrequired">'.$langs->trans("Ref").'</span></td><td>';
		if (empty($duplicate_code_error))
		{
			print (GETPOSTISSET("reportref")?GETPOST("reportref",'alpha'):$defaultref);
		}
		else
		{
			print $defaultref;
		}
		print '<input type="hidden" name="reportref" value="'.($_POST["reportref"]?$_POST["reportref"]:$defaultref).'">';
		print '</td></tr>';

		// List of projects
		if ($action == 'createnilproject')
		{
			print '<tr><td class="fieldrequired">'.$langs->trans("Project").'</td><td>';
			print $formproject->select_projects(-1, '', 'projectid', 0, 0, 1, 1);
			print '</td></tr>';
		}

		// Report Subject
		print '<tr><td class="fieldrequired">'.$langs->trans("ReportLabel").'</td><td>';
		print '<input type="text" name="label" autofocus class="minwidth500" value="'.$label.'">';
		print '</td></tr>';

		// Report Date
		print '<tr><td>'.$langs->trans("DateReport").'</td><td>';
		print $form->selectDate(($date_report?$date_report:''), 'date_report_', 0, 0, 2, '', 1, 1);
		print '</td></tr>';

		// Report Delivery Date
		print '<tr><td>'.$langs->trans("DateDeliveryPlanned").'</td><td>';
		print $form->selectDate(($date_planned?$date_planned:''), 'date_planned_', 0, 0, 2, '', 1, 1);
		print '</td></tr>';

		// Date start
		print '<tr><td>'.$langs->trans("ReportPeriodStart").'</td><td>';
		print $form->selectDate(($date_start?$date_start:''), 'date_start_', 0, 0, 1, '', 1);
		print '</td></tr>';

		// Date end
		print '<tr><td>'.$langs->trans("ReportPeriodEnd").'</td><td>';
		print $form->selectDate(($date_end?$date_end:-1),'date_end_', 0, 0, 1, '', 1);
		print '</td></tr>';

/*		// Description
		print '<tr><td class="tdtop">'.$langs->trans("WipReportDescription").'</td>';
		print '<td>';
		print '<textarea name="report_desc" class="quatrevingtpercent" rows="'.ROWS_4.'">'.$_POST['report_desc'].'</textarea>';
		print '</td></tr>';
*/

		//$mail_intro = $conf->global->WIP_DEFAULT_TITLE_SEC1 ? $conf->global->WIP_DEFAULT_TITLE_SEC1 : $langs->trans('TicketMessageMailIntroText');
		$default_title_sec1 = 'Introduction';
		$default_descr_sec1 = 'This report summarises restoration work progressed by Finch Restorations on the ';
		$default_descr_sec1.= ($projectstatic->title?$projectstatic->title:'...').' for '.($projectstatic->thirdparty->name?$projectstatic->thirdparty->name:'...');
		$default_descr_sec1.= '. The report covers progress achieved up to and including ...';
		$default_title_sec2 = 'Work Undertaken';
		$default_descr_sec2 = 'Section 2 report lines are edited individually from Report Card';
		$default_title_sec3 = 'Procurement';
		$default_descr_sec3 = 'Components, materials and/or subcontracted activities that have been procured or committed to during the reporting period include:';
		$default_title_sec4 = 'Issues Encountered / Emergent Work';
		$default_descr_sec4 = 'No major issues to report';
		$default_title_sec5 = 'Anticipated Forthcoming Work';
		$default_descr_sec5 = 'The following activities are planned to be commenced in the forthcoming period:';
		$default_title_sec6 = $default_spare_text;
		$default_descr_sec6 = $default_spare_text;

		$title_sec1	= $_POST['sec1_title']?$_POST['sec1_title']:$default_title_sec1;
		$title_sec2	= $_POST['sec2_title']?$_POST['sec2_title']:$default_title_sec2;
		$title_sec3	= $_POST['sec3_title']?$_POST['sec3_title']:$default_title_sec3;
		$title_sec4	= $_POST['sec4_title']?$_POST['sec4_title']:$default_title_sec4;
		$title_sec5	= $_POST['sec5_title']?$_POST['sec5_title']:$default_title_sec5;
		$title_sec6	= $_POST['sec6_title']?$_POST['sec6_title']:$default_title_sec6;

		$descr_sec1	= $_POST['descr_sec1']?$_POST['descr_sec1']:$default_descr_sec1;
		$descr_sec3	= $_POST['descr_sec3']?$_POST['descr_sec3']:$default_descr_sec3;
		$descr_sec4	= $_POST['descr_sec4']?$_POST['descr_sec4']:$default_descr_sec4;
		$descr_sec5	= $_POST['descr_sec5']?$_POST['descr_sec5']:$default_descr_sec5;
		$descr_sec6	= $_POST['descr_sec6']?$_POST['descr_sec6']:$default_descr_sec6;

		print '<tr class="liste_titre">';
		print '<td colspan="2">' . $langs->trans("ReportBody") . '</td>';
		print '</tr>';
		// Section 1 Title
		print '<tr><td class="fieldrequired">'.$langs->trans("Section1Title").':</td>';
		print '<td><input class="minwidth500 bold" name="sec1_title" value="'.$title_sec1.'"></td></tr>';

		// Section 1 Description
		print '<tr><td>'.$langs->trans("Section1Description").':';
		print '</td><td>';
		require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
		$doleditor = new DolEditor('descr_sec1', $default_descr_sec1, '100%', 120, 'BASIC', '', false, true, true, ROWS_2, 70);
		$doleditor->Create();
		print '<br></td></tr>';

		// Section 2 Title
		print '<tr><td class="fieldrequired">'.$langs->trans("Section2Title").':</td>';
		print '<td><input class="minwidth500 bold" name="sec2_title" value="'.$title_sec2.'"></td></tr>';

		// Section 2 Description - we add a box for visual impression of where the 'Lines' reside
		print '<tr><td>'.$langs->trans("WorkUndertaken").':</td>';
		print '<td class="wipdirectlev0 width80p"><br>&nbsp;'.$default_descr_sec2.'<br>&nbsp;<br>&nbsp;<br>&nbsp;';
		print '</td></tr>';

		// Section 3 Title
		print '<tr><td class="fieldrequired">'.$langs->trans("Section3Title").':</td>';
		print '<td><input class="minwidth500 bold" name="sec3_title" value="'.$title_sec3.'"></td></tr>';

		// Section 3 Description
		print '<tr><td>'.$langs->trans("Section3Description").':';
		print '</td><td>';
		require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
		$doleditor = new DolEditor('descr_sec3', $default_descr_sec3, '100%', 120, 'BASIC', '', false, true, true, ROWS_2, 70);
		$doleditor->Create();
		print '<br></td></tr>';

		// Section 4 Title
		print '<tr><td class="fieldrequired">'.$langs->trans("Section4Title").':</td>';
		print '<td><input class="minwidth500 bold" name="sec4_title" value="'.$title_sec4.'"></td></tr>';

		// Section 4 Description
		print '<tr><td>'.$langs->trans("Section4Description").':';
		print '</td><td>';
		require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
		$doleditor = new DolEditor('descr_sec4', $default_descr_sec4, '100%', 120, 'BASIC', '', false, true, true, ROWS_2, 70);
		$doleditor->Create();
		print '<br></td></tr>';

		// Section 5 Title
		print '<tr><td class="fieldrequired">'.$langs->trans("Section5Title").':</td>';
		print '<td><input class="minwidth500 bold" name="sec5_title" value="'.$title_sec5.'"></td></tr>';

		// Section 5 Description
		print '<tr><td>'.$langs->trans("Section5Description").':';
		print '</td><td>';
		require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
		$doleditor = new DolEditor('descr_sec5', $default_descr_sec5, '100%', 120, 'BASIC', '', false, true, true, ROWS_2, 70);
		$doleditor->Create();
		print '<br></td></tr>';

		// Section 6 Title
		print '<tr><td class="fieldrequired">'.$langs->trans("Section6Title").':</td>';
		print '<td><input class="minwidth500 bold" name="sec6_title" value="'.$title_sec6.'"></td></tr>';

		// Section 6 Description
		print '<tr><td>'.$langs->trans("Section6Description").':';
		print '</td><td>';
		require_once DOL_DOCUMENT_ROOT . '/core/class/doleditor.class.php';
		$doleditor = new DolEditor('descr_sec6', $default_descr_sec6, '100%', 120, 'BASIC', '', false, true, true, ROWS_2, 70);
		$doleditor->Create();
		print '<br></td></tr>';

		// Other options
		$parameters=array();
		$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action); // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;

		if (empty($reshook))
		{
			print $object->showOptionals($extrafields,'edit');
		}

		print '</table>';

		print '<div align="center">';
		print '<input type="submit" class="button" name="addreport" value="'.$langs->trans("Add").'">';
		print ' &nbsp; &nbsp; ';
		print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
		print '</div>';

		print '</form>';
	}
}
// Section to edit record
// ------------------------------------------------------------
elseif ($action == 'edit' && $user->rights->projet->creer)	// EDIT
{
	/* =================
	 *
	 * Report card - EDIT
	 *
	 * =================
	 */

	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';
	print '<input type="hidden" name="withproject" value="'.$withproject.'">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';

	// Tabs for reports
	dol_fiche_head($head, $tab, $tabtitle, -1, $tabpicto, 1, '', 'reposition');

	print '<table class="border" width="100%">';

	// Ref
	print '<tr><td class="titlefield fieldrequired">'.$langs->trans("Ref").'</td>';
	print '<td><input class="minwidth100" name="reportref" value="'.$object->ref.'"></td></tr>';

	// Label
	print '<tr><td class="fieldrequired">'.$langs->trans("ReportLabel").'</td>';
	print '<td><input class="minwidth500" name="label" value="'.$object->label.'"></td></tr>';

	// Project
	if (empty($withproject))
	{
		print '<tr><td>'.$langs->trans("Project").'</td><td colspan="3">';
		print $projectstatic->getNomUrl(1);
		print '</td></tr>';

		// Third party
		print '<td>'.$langs->trans("ThirdParty").'</td><td colspan="3">';
		if ($projectstatic->societe->id) print $projectstatic->societe->getNomUrl(1);
		else print '&nbsp;';
		print '</td></tr>';
	}

	// Description
	print '<tr><td class="tdtop">'.$langs->trans("WipReportDescription").'</td>';
	print '<td>';
	print '<textarea name="report_desc" class="quatrevingtpercent" rows="'.ROWS_4.'">'.$object->description.'</textarea>';
	print '</td></tr>';

	// Section 1 Title
	print '<tr><td class="fieldrequired">'.$langs->trans("Section1Title").'</td>';
	print '<td><input class="minwidth500" name="sec1_title" value="'.$object->sec1_title.'"></td></tr>';

	// Section 1 Description
	print '<tr><td class="tdtop">'.$langs->trans("Section1Description").'</td>';
	print '<td>';
	print '<textarea name="sec1_description" class="quatrevingtpercent" rows="'.ROWS_4.'">'.$object->sec1_description.'</textarea>';
	print '</td></tr>';

	// Section 2 Title
	print '<tr><td class="fieldrequired">'.$langs->trans("Section2Title").'</td>';
	print '<td><input class="minwidth500" name="sec2_title" value="'.$object->sec2_title.'"></td></tr>';

	// Section 2 Description - we add a box for visual impression of where the 'Lines' reside
	print '<tr><td class="tdtop">'.$langs->trans("WorkUndertaken").'</td>';
	print '<td class="wipdirectlev0 width80p">&nbsp;Section 2 report lines are edited individually from Report Card<br>&nbsp;<br>&nbsp;<br>&nbsp;';
	print '</td></tr>';

	// Section 3 Title
	print '<tr><td class="fieldrequired">'.$langs->trans("Section3Title").'</td>';
	print '<td><input class="minwidth500" name="sec3_title" value="'.$object->sec3_title.'"></td></tr>';

	// Section 3 Description
	print '<tr><td class="tdtop">'.$langs->trans("Section3Description").'</td>';
	print '<td>';
	print '<textarea name="sec3_description" class="quatrevingtpercent" rows="'.ROWS_4.'">'.$object->sec3_description.'</textarea>';
	print '</td></tr>';

	// Section 4 Title
	print '<tr><td class="fieldrequired">'.$langs->trans("Section4Title").'</td>';
	print '<td><input class="minwidth500" name="sec4_title" value="'.$object->sec4_title.'"></td></tr>';

	// Section 4 Description
	print '<tr><td class="tdtop">'.$langs->trans("Section4Description").'</td>';
	print '<td>';
	print '<textarea name="sec4_description" class="quatrevingtpercent" rows="'.ROWS_4.'">'.$object->sec4_description.'</textarea>';
	print '</td></tr>';

	// Section 5 Title
	print '<tr><td class="fieldrequired">'.$langs->trans("Section5Title").'</td>';
	print '<td><input class="minwidth500" name="sec5_title" value="'.$object->sec5_title.'"></td></tr>';

	// Section 5 Description
	print '<tr><td class="tdtop">'.$langs->trans("Section5Description").'</td>';
	print '<td>';
	print '<textarea name="sec5_description" class="quatrevingtpercent" rows="'.ROWS_4.'">'.$object->sec5_description.'</textarea>';
	print '</td></tr>';

	// Section 6 Title
	print '<tr><td class="fieldrequired">'.$langs->trans("Section6Title").'</td>';
	print '<td><input class="minwidth500" name="sec6_title" value="'.$object->sec6_title.'"></td></tr>';

	// Section 6 Description
	print '<tr><td class="tdtop">'.$langs->trans("Section6Description").'</td>';
	print '<td>';
	print '<textarea name="sec6_description" class="quatrevingtpercent" rows="'.ROWS_4.'">'.$object->sec6_description.'</textarea>';
	print '</td></tr>';

	// Other options
	$parameters=array();
	$reshook=$hookmanager->executeHooks('formObjectOptions',$parameters,$object,$action); // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	if (empty($reshook))
	{
		print $object->showOptionals($extrafields,'edit');
	}

	print '</table>';

	print '<div align="center">';
	print '<input type="submit" class="button" name="update" value="'.$langs->trans("Modify").'"> &nbsp; ';
	print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
	print '</div>';

	print '</form>';
}
else													// VIEW
{
	/* =================
	 *
	 * Report card - VIEW
	 *
	 * =================
	 */

	/*
	 * Report card in visual mode
	 */
	 // ------------------------------------------------------------

	$param = ($withproject?'&withproject=1':'');
	$linkback = $withproject?'<a href="' . dol_buildpath('/wip/report_list.php',1) . '?id='.$projectstatic->id . '&restore_lastsearch_values=1">' . $langs->trans("BackToList") . '</a>':'';

	// Tabs for reports
	$tabpicto = dol_buildpath('/wip/img/object_report.png',1);
	dol_fiche_head($head, $tab, $tabtitle, -1, $tabpicto, 1, '', 'reposition');

	// ------------------------------------------------------------
	$formconfirm='';

	// Confirmation to delete
	if ($action == 'delete') {
		//$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('DeleteReport'), $langs->trans('ConfirmDeleteReport'), 'confirm_delete', '', 0, 2);
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF'].'?id='.$_GET['id'].'&withproject='.$withproject,$langs->trans('DeleteReport'), $langs->trans('ConfirmDeleteReport'), 'confirm_delete', '', 0, 2);
	}

	// Clone confirmation
	if ($action == 'clone')
	{
		// Create an array for form
		$formquestion = array(
		// 'text' => $langs->trans("ConfirmClone"),
		// array('type' => 'checkbox', 'name' => 'update_prices',   'label' => $langs->trans("PuttingPricesUpToDate"),   'value' => 1)
		);
		// Incomplete payment. We ask if reason = discount or other
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id,$langs->trans('CloneOrder'), $langs->trans('ConfirmCloneOrder',$object->ref), 'confirm_clone', $formquestion, 'yes',1);
	}

	// Confirmation of validation
	if ($action == 'valid')
	{
		$object->date_commande=dol_now();

		// We check if object has a draft number
		if (preg_match('/^[\(]?PROV/i',$object->ref) || empty($object->ref)) // empty should not happened, but when it occurs, the test save life
		{
			$newref = $object->getNextNumRef($object->thirdparty);
		}
		else $newref = $object->ref;
		if ($newref < 0)
		{
			setEventMessages($object->error, $object->errors, 'errors');
			$action='';
		}
		else
		{
			$text = $langs->trans('ConfirmValidateOrder',$newref);
			if (! empty($conf->notification->enabled)) {
				require_once DOL_DOCUMENT_ROOT . '/core/class/notify.class.php';
				$notify = new Notify($db);
				$text .= '<br>';
				$text .= $notify->confirmMessage('ORDER_SUPPLIER_VALIDATE', $object->socid, $object);
			}
			$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id, $langs->trans('ValidateOrder'), $text, 'confirm_valid', '', 0, 1);
		}
	}

	// Confirm approval
	if ($action	== 'approve' || $action	== 'approve2')
	{
		$qualified_for_stock_change=0;
		if (empty($conf->global->STOCK_SUPPORTS_SERVICES))
		{
			$qualified_for_stock_change=$object->hasProductsOrServices(2);
		}
		else
		{
			$qualified_for_stock_change=$object->hasProductsOrServices(1);
		}
		$formquestion = array();
		if (! empty($conf->stock->enabled) && ! empty($conf->global->STOCK_CALCULATE_ON_SUPPLIER_VALIDATE_ORDER) && $qualified_for_stock_change)
		{
			$langs->load("stocks");
			require_once DOL_DOCUMENT_ROOT . '/product/class/html.formproduct.class.php';
			$formproduct=new FormProduct($db);
			$forcecombo=0;
			if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
			$formquestion = array(
				// 'text' => $langs->trans("ConfirmClone"),
				// array('type' => 'checkbox', 'name' => 'clone_content',   'label' => $langs->trans("CloneMainAttributes"),  'value' => 1),
				// array('type' => 'checkbox', 'name' => 'update_prices',   'label' => $langs->trans("PuttingPricesUpToDate"),   'value' => 1),
				array('type' => 'other', 'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockIncrease"),   'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse','int'), 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
			);
		}
		$text=$langs->trans("ConfirmApproveThisOrder",$object->ref);
		if (! empty($conf->notification->enabled))
		{
			require_once DOL_DOCUMENT_ROOT .'/core/class/notify.class.php';
			$notify=new	Notify($db);
			$text.='<br>';
			$text.=$notify->confirmMessage('ORDER_SUPPLIER_APPROVE', $object->socid, $object);
		}
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF']."?id=".$object->id, $langs->trans("ApproveThisOrder"), $text, "confirm_".$action, $formquestion, 1, 1, 240);
	}

	// Confirmation of disapproval
	if ($action == 'refuse')
	{
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF']."?id=$object->id",$langs->trans("DenyingThisOrder"),$langs->trans("ConfirmDenyingThisOrder",$object->ref),"confirm_refuse", '', 0, 1);
	}

	// Confirmation of cancellation
	if ($action == 'cancel')
	{
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF']."?id=$object->id",$langs->trans("Cancel"),$langs->trans("ConfirmCancelThisOrder",$object->ref),"confirm_cancel", '', 0, 1);
	}

	// Confirmation of the sending of the order
	if ($action == 'commande')
	{
		$date_com = dol_mktime(GETPOST('rehour'),GETPOST('remin'),GETPOST('resec'),GETPOST("remonth"),GETPOST("reday"),GETPOST("reyear"));
		$formconfirm = $form->formconfirm($_SERVER['PHP_SELF']."?id=".$object->id."&datecommande=".$date_com."&methode=".$_POST["methodecommande"]."&comment=".urlencode($_POST["comment"]), $langs->trans("MakeOrder"),$langs->trans("ConfirmMakeOrder",dol_print_date($date_com,'day')),"confirm_commande",'',0,2);
	}

	// Confirmation to remove line
	if ($action == 'ask_removeline')
	{
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"].'?id='.$object->id.'&lineid='.$lineid, $langs->trans('RemovePacketLine'), $langs->trans('ConfirmRemovePacketLine'), 'confirm_removeline', '', 0, 1);
	}

	// Confirmation of action xxxx
	if ($action == 'xxx')
	{
		$formquestion=array();
		/*
		$forcecombo=0;
		if ($conf->browser->name == 'ie') $forcecombo = 1;	// There is a bug in IE10 that make combo inside popup crazy
		$formquestion = array(
		// 'text' => $langs->trans("ConfirmClone"),
		// array('type' => 'checkbox', 'name' => 'clone_content', 'label' => $langs->trans("CloneMainAttributes"), 'value' => 1),
		// array('type' => 'checkbox', 'name' => 'update_prices', 'label' => $langs->trans("PuttingPricesUpToDate"), 'value' => 1),
		// array('type' => 'other',	'name' => 'idwarehouse',   'label' => $langs->trans("SelectWarehouseForStockDecrease"), 'value' => $formproduct->selectWarehouses(GETPOST('idwarehouse')?GETPOST('idwarehouse'):'ifone', 'idwarehouse', '', 1, 0, 0, '', 0, $forcecombo))
		);
		*/
		$formconfirm = $form->formconfirm($_SERVER["PHP_SELF"] . '?id=' . $object->id, $langs->trans('XXX'), $text, 'confirm_xxx', $formquestion, 0, 1, 220);
	}

	// ------------------------------------------------------------
	if (! $formconfirm) {
		$parameters = array('lineid'=>$lineid);
		$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
		if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
		elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;
	}

	// Print form confirm
	print $formconfirm;

// *******************************************************

	$object->next_prev_filter=" fk_project = ".$projectstatic->id;

	$morehtmlref='';

	// Project
	if ($projectid > 0 || ! empty($projectref) )
	{
		if ($object->status == 0) $morehtmlref.= '('.$langs->trans('Prov').') ';
		$morehtmlref.= $object->ref;
		if (!empty($object->label)) $morehtmlref.= ' - '.$object->label;
		$morehtmlref.='<div class="refidno">';
		$morehtmlref.= '<div class="maxwidth500">'.$object->sec1_description.'</div>';
		//$morehtmlref.='<br>';
		//if ($object->date_start || $object->date_end) $morehtmlref.='<div class="clearboth nowraponall">'.get_date_range($object->date_start, $object->date_end, 'day', '' ,0).'</div>';
		if ($object->date_report) $morehtmlref.='Report Date: ' . dol_print_date($object->date_report, 'day');
		$morehtmlref.='</div>';
	} else {
		$morehtmlref.='<div class="refidno">';
		$morehtmlref.=$langs->trans("Project").': ';
		$morehtmlref.=$projectstatic->getNomUrl(1);
		$morehtmlref.='<br>';
		// Third party
		$morehtmlref.=$langs->trans("ThirdParty").': ';
		if (!empty($projectstatic->thirdparty)) $morehtmlref.=$projectstatic->thirdparty->getNomUrl(1);
		$morehtmlref.='</div>';
	}

	wip_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $param);

// *******************************************************
	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';

	print '<div class="underbanner clearboth"></div>';
	print '<table class="border centpercent">'."\n";

	// Report Subject
	print '<tr><td class="nowrap width200">';
	print $form->editfieldkey("Subject", 'label', $object->label, $object, $user->rights->wip->write && !$user->societe_id, 'string');
	print '</td><td>';
	print $form->editfieldval("Subject", 'label', $object->label, $object, $user->rights->wip->write && !$user->societe_id, 'string');
	print '</td></tr>';

	// Report Date
	print '<tr><td>';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('DateReport');
	print '</td>';

	if ($action != 'editdate_report')
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=editdate_report">' . img_edit($langs->trans('SetDate'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td>';
	if ($action == 'editdate_report') {
		print '<form method="post" name="setdate_report" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		print '<input type="hidden" name="action" value="setdate_report">';
		print $form->selectDate($object->date_report, 'date_report_', '', '', '', "setdate_report", 1, 1);
		print '<input type="submit" class="button" name="modify" value="'.$langs->trans('Modify').'">';
		print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
		print '</form>';
	} else {
		print $object->date_report ? dol_print_date($object->date_report, 'daytext') : '&nbsp;';
		/*if ($object->hasDelay() && ! empty($object->date_planned)) {
			print ' '.img_picto($langs->trans("Late").' : '.$object->showDelay(), "warning");
		}*/
	}
	print '</td></tr>';

	// Report Delivery Date
	print '<tr><td class="nowrap">';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('DateDeliveryPlanned');
	print '</td>';
	if ($action != 'editdate_planned')
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=editdate_planned">' . img_edit($langs->trans('SetDate'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td>';
	if ($action == 'editdate_planned') {
		print '<form method="post" name="setdate_planned" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="setdate_planned">';
		$form->select_date($object->date_planned?$object->date_planned:-1,'date_planned_', '', '', '',"setdate_planned", 1, 1);
		print '<input type="submit" class="button" name="modify" value="'.$langs->trans('Modify').'">';
		print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
		print '</form>';
	}
	else
	{
		print $object->date_planned ? dol_print_date($object->date_planned, 'daytext') : '&nbsp;';
		//if ($object->hasDelay() && ! empty($object->date_report)) print ' '.img_picto($langs->trans("Late").' : '.$object->showDelay(), "warning");
	}
	print '</td></tr>';

	// Description
	/*print '<td class="tdtop">'.$langs->trans("WipReportDescription").'</td><td colspan="3">';
	print nl2br($object->description);
	print '</td></tr>';*/
/*
	// Author
	print '<tr><td>' . $langs->trans("AuthorReport") . '</td><td>';
	if ($object->fk_user_author > 0) {
		$author	= new User($db);
		$author->fetch($object->fk_user_author);
		print $author->getNomUrl(1, '', 0, 0, 0);
	} else {
		print $langs->trans('None');
	}

	// Show user list to assign author
	if ($action != "assign_author" && $object->status < 8 && $user->rights->wip->write) {
		print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=assign_author">' . img_picto('', 'edit') . ' ' . $langs->trans('Modify') . '</a>';
	}
	if ($action == "assign_author" && $object->status < 8 && !$user->societe_id && $user->rights->wip->write) {
		print '<form method="post" name="assign_author" enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="assign_author">';
		print '<label for="fk_user_assign">' . $langs->trans("AuthorReport") . '</label> ';
		print $form->select_dolusers($user->id, 'fk_user_author', 1);
		print ' <input class="button" type="submit" name="btn_assign_author" value="' . $langs->trans("Modify") . '" />';
		print '</form>';
	}
	print '</td></tr>';
*/
	// Author
	print '<tr><td class="nowrap">';
	print $form->editfieldkey("AuthorReport", '_author', '', $object, $user->rights->wip->write && !$user->societe_id, 'string');
	print '</td><td>';
	if ($action != "edit_author")
	{
		if ($object->fk_user_author > 0) {
			$author	= new User($db);
			$author->fetch($object->fk_user_author);
			print $author->getNomUrl(1, '', 0, 0, 0);
		} else {
			print $langs->trans('None');
		}
	}
	// Show user list to edit author
	if ($action == "edit_author" && $object->status < 8 && !$user->societe_id && $user->rights->wip->write) {
		print '<form method="post" name="edit_author" enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
		print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
		print '<input type="hidden" name="action" value="edit_author">';
//		print '<label for="fk_user_author">' . $langs->trans("AuthorReport") . '</label> ';
		print $form->select_dolusers($user->id, 'fk_user_author', 1);
		print ' <input class="button" type="submit" name="btn_edit_author" value="' . $langs->trans("Modify") . '" />';
		print '</form>';
	}
	print '</td></tr>';

	// Report Start Date
	print '<tr><td class="nowrap">';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('ReportPeriodStart');
	print '</td>';

	if ($action != 'editdate_start')
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=editdate_start">' . img_edit($langs->trans('SetDate'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td>';
	if ($action == 'editdate_start') {
		print '<form method="post" name="setdate_start" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
		print '<input type="hidden" name="token" value="' . $_SESSION ['newtoken'] . '">';
		print '<input type="hidden" name="action" value="setdate_start">';
		print $form->selectDate($object->date_start, 'date_start_', '', '', '', "setdate_start", 1, 1);
		print '<input type="submit" class="button" name="modify" value="'.$langs->trans('Modify').'">';
		print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
		print '</form>';
	} else {
		print $object->date_start ? dol_print_date($object->date_start, 'daytext') : '&nbsp;';
		/*if ($object->hasDelay() && ! empty($object->date_planned)) {
			print ' '.img_picto($langs->trans("Late").' : '.$object->showDelay(), "warning");
		}*/
	}
	print '</td></tr>';

	// Report End Date
	print '<tr><td class="nowrap">';
	print '<table class="nobordernopadding" width="100%"><tr><td>';
	print $langs->trans('ReportPeriodEnd');
	print '</td>';
	if ($action != 'editdate_end')
		print '<td align="right"><a href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&amp;action=editdate_end">' . img_edit($langs->trans('SetDate'), 1) . '</a></td>';
	print '</tr></table>';
	print '</td><td>';
	if ($action == 'editdate_end') {
		print '<form method="post" name="setdate_end" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="action" value="setdate_end">';
		$form->select_date($object->date_end?$object->date_end:-1,'date_end_', '', '', '',"setdate_end", 1, 1);
		print '<input type="submit" class="button" name="modify" value="'.$langs->trans('Modify').'">';
		print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
		print '</form>';
	}
	else
	{
		print $object->date_end ? dol_print_date($object->date_end, 'daytext') : '&nbsp;';
		//if ($object->hasDelay() && ! empty($object->date_report)) print ' '.img_picto($langs->trans("Late").' : '.$object->showDelay(), "warning");
	}
	print '</td></tr>';

	//print '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
	//print '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';

	print '</table>';

	//print '</div>';

	// Show Rates Table
	$servicetmp=new ReportDet($db);
	$servicessarray=$servicetmp->getExtendedServicesArray(1, $conf->global->WIP_SERVICE_CATEGORY,'');
	$num = count($servicessarray);

	//print '<div class="div-table-responsive-no-min">';
	print '<!-- Rates table -->'."\n";

	print '<table class="wipratestable noborder centpercent">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans('LabourRate').'</td>';
	print '<td class="maxwidth150 right">' . $langs->trans('StandardHT') . '</td>';
	print '<td>' . $langs->trans("AppliedFrom") . '</td>';
	print '<td class="maxwidth150 right">' . $langs->trans('CustomerHT') . '</td>';
	//print '<td>' . $langs->trans("AppliedFrom") . '</td>';
	//print '<td class="center">'. $langs->trans("PriceBase") . '</td>';
	//print '<td align="right">' . $langs->trans("VAT") . '</td>';
	print '<td class="right">' . $langs->trans("RateUsedHT") . '</td>';
	print '<td class="right">' . $langs->trans("RateUsedTTC") . '</td>';
	//print '<td align="right">' . $langs->trans("MinPrice") . ' ' . $langs->trans("HT") . '</td>';
	//print '<td align="right">' . $langs->trans("MinPrice") . ' ' . $langs->trans("TTC") . '</td>';
	//print '<td align="right">' . $langs->trans("ChangedBy") . '</td>';
	//print '<td>&nbsp;</td>';
	print '</tr>';

	if ($num > 0)
	{
		foreach ($servicessarray as $key=>$value)
		{
			$staticprod = new Product($db);
			$staticprod->fetch($key);
			$cust_price = ($servicessarray[$key][cprice]?1:0);

			print '<tr class="oddeven">';
			print '<td class="nowrap">' . $staticprod->getNomUrl(1) . '</td>';
			print '<td align="right">$' . price($servicessarray[$key][pprice]) . '</td>';
			dol_syslog("BENLOG - Error on line 1908: ".$servicessarray[$key][pdate_price],7);
			print '<td>' . dol_print_date($servicessarray[$key][pdate_price], "day") . '</td>'; //BB This Field Produces a bad value error with its dat function. Date must not be correct format.
			print '<td class="right'.($cust_price? ' wipltgreen': '').'">' . ($cust_price?'$'.price($servicessarray[$key][cprice]):'-.--') . '</td>';
			//print '<td>' . dol_print_date($servicessarray[$key][cdate_price], "day") . dol_print_date($servicessarray[$key][datec], "day") . '</td>';
			//print '<td align="center">' . $langs->trans($servicessarray[$key][pprice_base_type]) . "</td>";
			/*print '<td align="right">';
			print vatrate($servicessarray[$key][ptva_tx].($servicessarray[$key][pdefault_vat_code]?' ('.$servicessarray[$key][pdefault_vat_code].')':''), true, $servicessarray[$key][precuperableonly]);
			print '</td>';*/
			print '<td class="right'.($cust_price? ' wipltgreen': '').'">$' . price($cust_price?$servicessarray[$key][cprice]:$servicessarray[$key][pprice]) . '</td>';
			print '<td class="right'.($cust_price? ' wipltgreen': '').'">$' . price($cust_price?$servicessarray[$key][cprice_ttc]:$servicessarray[$key][pprice_ttc]) . '</td>';
			//print '<td align="right">' . price($servicessarray[$key][pprice_min]) . '</td>';
			//print '<td align="right">' . price($servicessarray[$key][pprice_min_ttc]) . '</td>';

			// User
			/*$userstatic = new User($db);
			$userstatic->fetch($line->fk_user);
			print '<td align="right">';
			print $userstatic->getLoginUrl(1);
			print '</td>';*/

			// Action
			/*if ($user->rights->produit->creer || $user->rights->service->creer)
			{
				print '<td align="right">';
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id='.$id.'&amp;action=showlog_customer_price&amp;socid=' . $socstatic->id . '&amp;prodid=' . $line->fk_product . '">';
				print img_info();
				print '</a>';
				print ' ';
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id='.$id.'&amp;action=edit_customer_price&amp;socid=' . $socstatic->id . '&amp;lineid=' . $line->id . '">';
				print img_edit('default', 0, 'style="vertical-align: middle;"');
				print '</a>';
				print ' ';
				print '<a href="' . $_SERVER["PHP_SELF"] . '?id='.$id.'&amp;action=delete_customer_price&amp;socid=' . $socstatic->id . '&amp;lineid=' . $line->id . '">';
				print img_delete('default', 'style="vertical-align: middle;"');
				print '</a>';
				print '</td>';
			}*/

			print "</tr>\n";
		}
	}
	else
	{
		$colspan=9;
		if ($user->rights->produit->supprimer || $user->rights->service->supprimer) $colspan+=1;
		print '<tr ' . $bc[false] . '><td colspan="'.$colspan.'">' . $langs->trans('None') . '</td></tr>';
	}
	print '</table>';
	print '</div>';

	print '<div class="fichehalfright">';
	print '<div class="ficheaddleft">';
	print '<div class="underbanner clearboth"></div>';

// *******************************************************

	print '<table class="border centpercent">';

	print '<tr class="liste_titre">';
	print '<td>&nbsp;' . $langs->trans('Description') . '</td>';
//			print '<td class = "titlefieldmiddle">&nbsp;</td>';
	print '<td>&nbsp;</td>';
	print '<td class="right">' . $langs->trans('Hours') . '</td>';
	print '<td class="right">' . $langs->trans('AvgRate') . '</td>';
	print '<td class="right">' . $langs->trans('ValDlrs') . '</td>';
	print '<td width="18">&nbsp;</td>';
	print '</tr>';
	$nbrows = 8;
	$nbcols = 2;

	// ***********************************************************
	$sql = 'SELECT SUM(wrd.qty) as total, SUM(wrd.discounted_qty) as total_adjusted';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet as wrd';
	$sql.= ' WHERE wrd.fk_report = '.$object->id.' AND wrd.direct_amortised = 0';

	$resql = $db->query($sql);
	if (! $resql) dol_print_error($db);

	$res = $db->fetch_object($resql);

	$total_direct				= $res->total;
	$total_direct_adjusted		= $res->total_adjusted;
	$total_direct_discounted	= $total_direct - $total_direct_adjusted;
	$db->free($resql);

	$sql = 'SELECT SUM(wrd.qty) as total, SUM(wrd.discounted_qty) as total_adjusted';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet as wrd';
	$sql.= ' WHERE wrd.fk_report = '.$object->id.' AND wrd.direct_amortised = 1';

	$resql = $db->query($sql);
	if (! $resql) dol_print_error($db);

	$res = $db->fetch_object($resql);

	$total_amortised			= $res->total;
	$total_amortised_adjusted	= $res->total_adjusted;
	$total_amortised_discounted	= $total_amortised - $total_amortised_adjusted;

	$db->free($resql);

	$total_hours_reported		= $total_direct + $total_amortised;
	$total_hours_discounted		= $total_direct_discounted + $total_amortised_discounted;
	$total_hours_adjusted		= $total_direct_adjusted + $total_amortised_adjusted;

	$total_hours_efficiency		= (! $total_hours_reported == 0 ? 100 * $total_hours_adjusted / $total_hours_reported : 0 );

	$arrayoftotals = array();
	$arrayoftotals = array(
		'total_direct'				=> $total_direct,
		'total_direct_adjusted'		=> $total_direct_adjusted,
		'total_amortised'			=> $total_amortised,
		'total_amortised_adjusted'	=> $total_amortised_adjusted,
		 );
	// ***********************************************************

	// Total Direct Hours
	print '<tr class="wipdirectlev0"><td class="left nowrap">&nbsp;<strong>' . $langs->trans("DirectHours") . '</strong></td><td class="right">' . $langs->trans("DirectHoursTotal") . ' :</td>';
	print '<td class="right">' . number_format($total_direct,2) . '</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// Total Direct Discounted Hours
	print '<tr class="wipdirectlev0"><td colspan="' . $nbcols . '" class="right">(' . $langs->trans("Subtract") .') '. $langs->trans("DirectDiscountedHoursTotal") . ' :</td>';
	print '<td class="right wiptextred">(' . number_format($total_direct_discounted,2) . ')</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// Total Direct Adjusted Hours
	print '<tr class="wipdirectlev0"><td colspan="' . $nbcols . '" class="right">' . $langs->trans("DirectAdjustedHoursTotal") . ' :</td>';
	print '<td class="right"><strong>' . number_format($total_direct_adjusted,2) . '</strong></td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// Total Amortised Hours
	print '<tr class="wipamortisedlev0"><td class="left nowrap">&nbsp;<strong>' . $langs->trans("AmortisedHours") . '</strong></td><td class="right">' . $langs->trans("AmortisedHoursTotal") . ' :</td>';
	print '<td class="right">' . number_format($total_amortised,2) . '</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// Total Amortised Discounted Hours
	print '<tr class="wipamortisedlev0"><td colspan="' . $nbcols . '" class="right">(' . $langs->trans("Subtract") .') '. $langs->trans("AmortisedDiscountedHoursTotal") . ' :</td>';
	print '<td class="right wiptextred">(' . number_format($total_amortised_discounted,2) . ')</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// Total Amortised Adjusted Hours
	print '<tr class="wipamortisedlev0"><td colspan="' . $nbcols . '" class="right">' . $langs->trans("AmortisedAdjustedHoursTotal") . ' :</td>';
	print '<td class="right"><strong>' . number_format($total_amortised_adjusted,2) . '</strong></td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// Total Hours Reported
	print '<tr><td class="left nowrap">&nbsp;<strong>' . $langs->trans("Totals") . '</strong></td><td class="right">' . $langs->trans("ReportTotalHours") . ' :</td>';
	print '<td class="nowrap wiptotalneutral right">' . number_format($total_hours_reported,2) . '</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// total_hours_discounted
	print '<tr><td colspan="' . $nbcols . '" class="right">';
	print '(' . $langs->trans("Subtract") .') '.$langs->trans('ReportDiscountedHours').' :</td>';
	print '<td class="nowrap wiptotalred right">(' . number_format($total_hours_discounted,2) . ')</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// total_hours_adjusted
	print '<tr><td colspan="' . $nbcols . '" class="right">';
	print $langs->trans('ReportAdjustedHours').' :</td>';
	print '<td class="nowrap wiptotalgreen right">' . number_format($total_hours_adjusted,2) . '</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	// Efficiency
	print '<tr><td colspan="' . $nbcols . '" class="right">';
	print $langs->trans('Efficiency').' :</td>';
	print '<td class="nowrap wiptotalneutral right">' . number_format($total_hours_efficiency,2) . '%</td>';
	print '<td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td><td class="nowrap">&nbsp;</td></tr>';

	print '</table>';

	print '</div>';
	print '</div>';

	print '</div>';
	print '<div class="clearboth"></div>';
	print '<br>';
	
	
	// ********************************************************** EXTRA TIME PACKET ADDITION AREA TO REDUCE SCROLL TIMES FOR ADMIN
	// ADD TIME PACKET

	print '<!-- form to add pre-existing time packet to report -->'."\n";
/*	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="addtimepacket2report">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
*/

//	print '<form name="addpacket" id="addpacket" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#addline' : '#line_' . GETPOST('lineid')) . '" method="POST">
	print '<form method="POST" name="addpacket" id="addpacket" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">
	<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">
	<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
	<input type="hidden" name="mode" value="">
	<input type="hidden" name="id" value="' . $object->id . '">
	';

	if (! empty($conf->use_javascript_ajax) && $object->statut == 0) {
		include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
	}

	print '<div class="div-table-responsive-no-min">';
	print '<table id="tablelines" class="noborder noshadow">';

	// Show object lines
	/*
	if (! empty($object->lines))
		$ret = $object->printReportLines($action, $societe, $mysoc, $lineid, 1);
	function printReportLines($action, $seller, $buyer, $selected=0, $dateSelector=0)
	*/

	$num = count($object->lines);

	// Form to add new line
	if ($object->statut == Report::STATUS_DRAFT && $user->rights->wip->write)
	{
		if ($action != 'editline')
		{
			$packet=new ReportDet($db);

			print '<tr class="liste_titre">';
			// Line numbering column
			print '<td class="wiplinecolnum" align="center" width="5">&nbsp;</td>';
			print '<td>'.$langs->trans("AddPacket2Report").'<br></td>';
			print '<td></td>';
			print '</tr>';
			print '<tr class="oddeven">';
			// Select Packets
			print '<td></td>';
			// Add Packet
			print '<td>';
			print $packet->selectTaskPackets('', $object->fk_project, 0, 0, 'addpacket_id', 1, 1, 0, '', 'minwidth400 maxwidth200onsmartphone', '');
			print '<input type="submit" name="save" class="button" value="'.$langs->trans("Add").'">';
			print ' &nbsp; ';
			print '<input type="submit" name="cancel" class="button" value="'.$langs->trans("Cancel").'">';
			print '</td></tr>';
		}
	}

	print '</table>';
	print '</div>';
	print '</form>';



	// ***********************************************************
	print '<table class="border centpercent">'."\n";

	// Section 1 Title
	print '<tr class = "liste_titre"><td class="width200 tdtop"><b>'.$langs->trans("Section1Title").'</b></td>';
	print '<td><b>'.$object->sec1_title.'</b></td></tr>';

	// Section 1 Description
	print '<tr><td class="tdtop">'.$langs->trans("Section1Description").'</td>';
	print '<td class="tdtop">'.$object->sec1_description.'</td></tr>';

	print '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
	// Section 2 Title
	print '<tr class = "liste_titre"><td class="width200 tdtop"><b>'.$langs->trans("Section2Title").'</b></td>';
	print '<td><b>'.$object->sec2_title.'</b></td></tr>';

	print '</table>'."\n";

/* =================
 *
 * Start of Main Query
 *
 * =================
 */

/*
	// Get list of tasks in tasksarray

	//$tasksarray=$taskstatic->_getTasksArray(0, 0, $object->id, $filteronthirdpartyid, 0,'',-1,$morewherefilter);
	$tasksarray=_getTasksArray($object->id,$morewherefilter);
*/
	//var_dump($tasksarray);
	// Count total nb of records
	$nbtotalofrecords = count($object->lines);
//			$nbtotalofrecords = count($tasksarray);

	/*	===============================================================	*/
	/*																	*/
	/*	Lines															*/
	/*																	*/
	/*	===============================================================	*/
/*
	if (! empty($conf->use_javascript_ajax))
	{
		include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
	}
*/

/*	====================== Array of Selected ======================	*/
// Definition of fields for list
$arrayfields=array();

$arrayofselected=is_array($toselect)?$toselect:array();

/*	================== Definition of Parameters ===================	*/
$param='';

/*	================= Form preceding table - Start ================	*/

/*	================= Form preceding table - End ==================	*/



	/* =================
	 *
	 * Mass Actions
	 *
	 * =================
	 */
/*
// List of mass actions available
$arrayofmassactions =  array(
'move2task'=>$langs->trans("Move to another Workorder"),
'move2packet'=>$langs->trans("Move to another Packet"),
);
//if ($user->rights->projet->creer) $arrayofmassactions['predelete']=$langs->trans("Delete");
if (in_array($massaction, array('move2task','move2packet'))) $arrayofmassactions=array();
$massactionbutton=$form->selectMassAction('', $arrayofmassactions);

*/
/*
// New packet button
	$newcardbutton='';
	if ($user->rights->projet->creer)
	{
		$newcardbutton = '<a class="butActionNew" href="'.DOL_URL_ROOT.'/projet/tasks.php?action=create"><span class="valignmiddle">'.$langs->trans('NewTimePacket').'</span>';
		$newcardbutton.= '<span class="fa fa-plus-circle valignmiddle"></span>';
		$newcardbutton.= '</a>';
	}
*/

	/*
	 * List of lines in view mode
	 */

// Line of Filter Fields
// Lignes des champs de filtre

/*
$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
if ($massactionbutton) $selectedfields.=$form->showCheckAddButtons('checkforselect', 1);
*/

//	print '<form name="updatepacket" id="updatepacket" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#addline' : '#line_' . GETPOST('lineid')) . '" method="POST">
	print '<form method="POST" name="updatepacket" id="updatepacket" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">
	<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">
	<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
	<input type="hidden" name="mode" value="">
	<input type="hidden" name="lineid" value="' . GETPOST('lineid') . '">
	<input type="hidden" name="id" value="' . $object->id . '">
	<input type="hidden" name="contextpage" value="'.$contextpage.'">
	<input type="hidden" name="withproject" value="'.$withproject.'">
	';



	// Title and links
	print '<!-- List of Time Packets for Report -->'."\n";
	$title= $object->sec2_title. ' - ' . $langs->trans("ListOfTimePackets");
	//$linktotasks='<a href="'.DOL_URL_ROOT.'/projet/ganttview.php?id='.$object->id.'&withproject=1">'.$langs->trans("GoToGanttView").'<span class="paddingleft fa fa-calendar-minus-o valignmiddle"></span></a>';

// Print title with navigation controls for pagination
	//print_barre_liste($title, 0, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, $linktotasks, $num, $totalnboflines, 'title_generic.png', 0, '', '', 0, 1);
/*	print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'title_generic.png', 0, $linktocreatetimepacket, '', 0, 1, 0);
*/
/*	print_barre_liste($title, 0, $_SERVER["PHP_SELF"], $param, '', '', $massactionbutton, 0, $nbtotalofrecords, 'title_generic.png', 0, $linktotasks.' &nbsp; '.$newcardbutton, '', 0, 1);
*/
	//print_barre_liste($title, 0, $_SERVER["PHP_SELF"], '', '', '', '', 0, $nbtotalofrecords, 'title_generic.png', 0, $linktotasks.' &nbsp; '.$newcardbutton, '', 0, 1);
	//print load_fiche_titre($title, $linktotasks.' &nbsp; '.$newcardbutton, 'title_generic.png');




/*	================ Show description of content ==================	*/
	// Show description of content
	/*
	$contentdesc = $langs->trans('Billing status of workorders for Project').' - '.$object->ref;
	print '<div class="opacitymedium">';
	print $contentdesc.'<br><br>';
	print '</div>';
	*/

	// Add code for pre mass action (confirmation or email presend form)
/*
	$topicmail="Information";
	$modelmail="task";
	$objecttmp=new Task($db);
	$trackid='tas'.$object->id;
*/

	include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

	// --------------------------------------------------------------------
	// --------------------------  TABLE HEAD  ----------------------------
	// --------------------------------------------------------------------
	print '<div class="div-table-responsive-no-min">';		// You can use div-table-responsive-no-min if you dont need reserved height for your table
	print '<table id="tablelines" class="tagtable liste">';

	// Title
	// --------------------------------------------------------------------
	print '<thead>';
	print '<tr class="liste_titre nodrag nodrop">';

	// Line numbering column
	print '<td class="wiplinecolnum" width="5">&nbsp;</td>';
	// Description
	print '<td class="wiplinecoldescription titlefieldmiddle" colspan = "1">'.$langs->trans('Description').'</td>';
	// Spacer Column
	print '<td class="wiplinecolspacer maxwidth25 center">&nbsp</td>';
	// Subtotal Hours
	print '<td class="wiplinecolfam maxwidth50 center">'.$langs->trans('SubtotalHoursShort').'</td>';
	// Amortised Hours
	print '<td class="wiplinecolamort maxwidth50 center">'.$langs->trans('AmortisedHoursShort').'</td>';
	// Total Hours
	print '<td class="wiplinecolqty maxwidth50 center">'.$langs->trans('TotalHours').'</td>';
	// Price HT ex GST
	print '<td class="wiplinecoluht maxwidth75 center">'.$langs->trans('PriceExTax').'</td>';
	// Price inc GST
	print '<td class="wiplinecoluttc maxwidth75 center">'.$langs->trans('PriceIncTax').'</td>';
	// Edit
	print '<td class="wiplinecoledit"></td>';  // No width to allow autodim
	// Delete
	print '<td class="wiplinecoldelete" width="10"></td>';
	// Move
	print '<td class="wiplinecolmove" width="10"></td>';
	// Selectlines
	/*if($action == 'selectlines')
	{
		print '<td class="linecolcheckall" align="center">';
		print '<input type="checkbox" class="linecheckboxtoggle" />';
		print '<script type="text/javascript">$(document).ready(function() {$(".linecheckboxtoggle").click(function() {var checkBoxes = $(".linecheckbox");checkBoxes.prop("checked", this.checked);})});</script>';
		print '</td>';
	} */

	print '</tr>';
	print '</thead>';

	// --------------------------------------------------------------------
	// --------------------------  TABLE BODY  ----------------------------
	// --------------------------------------------------------------------

	print '<tbody>';

	$plannedworkloadoutputformat='allhourmin';
	$timespentoutputformat='allhourmin';

	if ($nbtotalofrecords > 0)
	{
	// Loop on record
	// --------------------------------------------------------------------
		// Show all lines in taskarray (recursive function to go down on tree)
		$j=0; $level=0;
		$nboftaskshown=$object->reportLinesa($action, $lineid, $arrayoftotals, $j, 0, $lines, $level, true, 0, 0);
					// function reportLinesa($action, $selected = 0, $arrayofselected, &$inc, $parent, &$lines, &$level, $var, $showproject, $addordertick=0)













































	// --------------------------------------------------------------------
	// End Loop
	// --------------------------------------------------------------------
	}
	else
	{
		print '<tr class="oddeven"><td colspan="10" class="opacitymedium">'.$langs->trans("NoPackets").'</td></tr>';
	}


	// Bottom Row - used to provide an extra *** Indent column ***
	// --------------------------------------------------------------------
	print '<tr class="nodrag nodrop">';
	// Line numbering column
	print '<td class="wiplinecolnum">&nbsp;</td>';
	// *** Indent column ***
	//	print '<td class="wiplinecolnum">&nbsp;</td>';
	// Description
	print '<td class="wiplinecoldescription soixantepercent">&nbsp;</td>';
	// Spacer Row
	print '<td class="wiplinecolspacer">&nbsp</td>';
	// Family Subtotal Hours
	print '<td class="wiplinecolfam>&nbsp;</td>';
	// Amortised Hours
	print '<td class="wiplinecolamort>&nbsp;</td>';
	// Total Hours
	print '<td class="wiplinecolqty">&nbsp;</td>';
	// Price HT ex GST
	print '<td class="wiplinecoluht>&nbsp;</td>';
	// Price inc GST
	print '<td class="wiplinecoluttc>&nbsp;</td>';
	// Edit
	print '<td class="wiplinecoledit">&nbsp;</td>';  // No width to allow autodim
	// Delete
	print '<td class="wiplinecoldelete">&nbsp;</td>';
	// Move
	print '<td class="wiplinecolmove">&nbsp;</td>';
	// Select
	if($action == 'selectlines') print '<td class="wiplinecolcheckall"></td>';
	print '</tr>';










	// --------------------------------------------------------------------

	print '</tbody>';
	print '</table>';
	// --------------------------------------------------------------------
	// ---------------------------  END TABLE  ----------------------------
	// --------------------------------------------------------------------
	print '</div>';
	print '</form>';

//}

	/*
	 * Add Time Packet
	 */
	print '<!-- form to add pre-existing time packet to report -->'."\n";
/*	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="addtimepacket2report">';
	print '<input type="hidden" name="id" value="'.$object->id.'">';
*/

//	print '<form name="addpacket" id="addpacket" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . (($action != 'editline') ? '#addline' : '#line_' . GETPOST('lineid')) . '" method="POST">
	print '<form method="POST" name="addpacket" id="addpacket" action="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '">
	<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">
	<input type="hidden" name="action" value="' . (($action != 'editline') ? 'addline' : 'updateline') . '">
	<input type="hidden" name="mode" value="">
	<input type="hidden" name="id" value="' . $object->id . '">
	';

	if (! empty($conf->use_javascript_ajax) && $object->statut == 0) {
		include DOL_DOCUMENT_ROOT . '/core/tpl/ajaxrow.tpl.php';
	}

	print '<div class="div-table-responsive-no-min">';
	print '<table id="tablelines" class="noborder noshadow">';

	// Show object lines
	/*
	if (! empty($object->lines))
		$ret = $object->printReportLines($action, $societe, $mysoc, $lineid, 1);
	function printReportLines($action, $seller, $buyer, $selected=0, $dateSelector=0)
	*/

	$num = count($object->lines);

	// Form to add new line
	if ($object->statut == Report::STATUS_DRAFT && $user->rights->wip->write)
	{
		if ($action != 'editline')
		{
			$packet=new ReportDet($db);

			print '<tr class="liste_titre">';
			// Line numbering column
			print '<td class="wiplinecolnum" align="center" width="5">&nbsp;</td>';
			print '<td>'.$langs->trans("AddPacket2Report").'<br></td>';
			print '<td></td>';
			print '</tr>';
			print '<tr class="oddeven">';
			// Select Packets
			print '<td></td>';
			// Add Packet
			print '<td>';
			print $packet->selectTaskPackets('', $object->fk_project, 0, 0, 'addpacket_id', 1, 1, 0, '', 'minwidth400 maxwidth200onsmartphone', '');
			print '<input type="submit" name="save" class="button" value="'.$langs->trans("Add").'">';
			print ' &nbsp; ';
			print '<input type="submit" name="cancel" class="button" value="'.$langs->trans("Cancel").'">';
			print '</td></tr>';
		}
	}

	print '</table>';
	print '</div>';
	print '</form>';

	print '<table class="border centpercent">'."\n";

	// Section 3
	if ($object->sec3_title || $object->sec3_description)
	{
		print '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		// Section 3 Title
		print '<tr class = "liste_titre"><td class="width200 tdtop"><b>'.$langs->trans("Section3Title").'</b></td>';
		print '<td><b>'.$object->sec3_title.'</b></td></tr>';
		// Section 3 Description
		print '<tr><td class="tdtop">'.$langs->trans("Section3Description").'</td>';
		print '<td class="tdtop">'.$object->sec3_description.'</td></tr>';
	}

	// Section 4
	if ($object->sec4_title || $object->sec4_description)
	{
		print '<tr><td class = "width200">&nbsp;</td><td>&nbsp;</td></tr>';
		// Section 4 Title
		print '<tr class = "liste_titre"><td class="width200 tdtop"><b>'.$langs->trans("Section4Title").'</b></td>';
		print '<td><b>'.$object->sec4_title.'</b></td></tr>';
		// Section 4 Description
		print '<tr><td class="tdtop">'.$langs->trans("Section4Description").'</td>';
		print '<td class="tdtop">'.$object->sec4_description.'</td></tr>';
	}

	// Section 5
	if ($object->sec5_title || $object->sec5_description)
	{
		print '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		// Section 5 Title
		print '<tr class = "liste_titre"><td class="width200 tdtop"><b>'.$langs->trans("Section5Title").'</b></td>';
		print '<td><b>'.$object->sec5_title.'</b></td></tr>';
		// Section 5 Description
		print '<tr><td class="tdtop">'.$langs->trans("Section5Description").'</td>';
		print '<td class="tdtop">'.$object->sec5_description.'</td></tr>';
	}

	// Section 6
	if ($object->sec6_title || $object->sec6_description)
	{
		print '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
		// Section 6 Title
		print '<tr class = "liste_titre"><td class="width200 tdtop"><b>'.$langs->trans("Section6Title").'</b></td>';
		print '<td><b>'.$object->sec6_title.'</b></td></tr>';
		// Section 6 Description
		print '<tr><td class="tdtop">'.$langs->trans("Section6Description").'</td>';
		print '<td class="tdtop">'.$object->sec6_description.'</td></tr>';
	}

	print '<tr><td>&nbsp;</td><td>&nbsp;</td></tr>';
	print '</table>';
	print '<div class="clearboth"></div>';

	dol_fiche_end();
}	// End VIEW

if ($id > 0 || ! empty($ref))
{
		/*	===============================================================	*/
		/*																	*/
		/*	Button Actions													*/
		/*																	*/
		/*	===============================================================	*/

		/**
		 * Action Buttons
		 */
		/**
		 * Boutons actions
		 */
		if ($user->societe_id == 0 && $action != 'edit'&& $action != 'editline' && $action != 'delete')
		{
			/*
			 * Actions
 			 */
			print '<div class="tabsAction">';

			$parameters = array();
			$reshook = $hookmanager->executeHooks('addMoreActionsButtons', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
			if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

			if (empty($reshook)) {
				$object->fetchObjectLinked();		// Links are used to show or not button, so we load them now.

				// Modify
				if ($object->statut == 0 && $user->rights->wip->write) {
					print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;action=edit&amp;withproject='.$withproject.'">'.$langs->trans('Modify').'</a>';
				}

				// Validate
				if ($object->statut == 0 && $num > 0)
				{
					if ((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->wip->write))
				   	|| (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->fournisseur->supplier_order_advance->validate)))
					{
						$tmpbuttonlabel=$langs->trans('Validate');
						if ($user->rights->wip->approuver && empty($conf->global->SUPPLIER_ORDER_NO_DIRECT_APPROVE)) $tmpbuttonlabel = $langs->trans("ValidateAndApprove");

						print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=valid">';
						print $tmpbuttonlabel;
						print '</a>';
					}
				}

				// Approve
				if ($object->statut == 1)
				{
					if ($user->rights->wip->approuver)
					{
						if (! empty($conf->global->SUPPLIER_ORDER_3_STEPS_TO_BE_APPROVED) && $conf->global->MAIN_FEATURES_LEVEL > 0 && $object->total_ht >= $conf->global->SUPPLIER_ORDER_3_STEPS_TO_BE_APPROVED && ! empty($object->user_approve_id))
						{
							print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("FirstApprovalAlreadyDone")).'">'.$langs->trans("ApproveOrder").'</a>';
						}
						else
						{
							print '<a class="butAction"	href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=approve">'.$langs->trans("ApproveOrder").'</a>';
						}
					}
					else
					{
						print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans("ApproveOrder").'</a>';
					}
				}

				// Second approval (if option SUPPLIER_ORDER_3_STEPS_TO_BE_APPROVED is set)
				if (! empty($conf->global->SUPPLIER_ORDER_3_STEPS_TO_BE_APPROVED) && $conf->global->MAIN_FEATURES_LEVEL > 0 && $object->total_ht >= $conf->global->SUPPLIER_ORDER_3_STEPS_TO_BE_APPROVED)
				{
					if ($object->statut == 1)
					{
						if ($user->rights->wip->approve2)
						{
							if (! empty($object->user_approve_id2))
							{
								print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("SecondApprovalAlreadyDone")).'">'.$langs->trans("Approve2Order").'</a>';
							}
							else
							{
								print '<a class="butAction"	href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=approve2">'.$langs->trans("Approve2Order").'</a>';
							}
						}
						else
						{
							print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans("Approve2Order").'</a>';
						}
					}
				}

				// Refuse
				if ($object->statut == 1)
				{
					if ($user->rights->wip->approuver || $user->rights->wip->approve2)
					{
						print '<a class="butAction"	href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=refuse">'.$langs->trans("RefuseOrder").'</a>';
					}
					else
					{
						print '<a class="butActionRefused" href="#" title="'.dol_escape_htmltag($langs->trans("NotAllowed")).'">'.$langs->trans("RefuseOrder").'</a>';
					}
				}

				// Send
				if (in_array($object->statut, array(2, 3, 4, 5)))
				{
					if ($user->rights->wip->commander)
					{
						print '<a class="butAction" href="' . $_SERVER["PHP_SELF"] . '?id=' . $object->id . '&action=presend&mode=init#formmailbeforetitle">'.$langs->trans('SendMail') . '</a>';
					}
				}

				// Reopen
				if (in_array($object->statut, array(2)))
				{
					$buttonshown=0;
					if (! $buttonshown && $user->rights->wip->approuver)
					{
						if (empty($conf->global->SUPPLIER_ORDER_REOPEN_BY_APPROVER_ONLY)
						|| (! empty($conf->global->SUPPLIER_ORDER_REOPEN_BY_APPROVER_ONLY) && $user->id == $object->user_approve_id))
						{
							print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=reopen">'.$langs->trans("Disapprove").'</a>';
							$buttonshown++;
						}
					}
					if (! $buttonshown && $user->rights->wip->approve2 && ! empty($conf->global->SUPPLIER_ORDER_3_STEPS_TO_BE_APPROVED))
					{
						if (empty($conf->global->SUPPLIER_ORDER_REOPEN_BY_APPROVER2_ONLY)
						|| (! empty($conf->global->SUPPLIER_ORDER_REOPEN_BY_APPROVER2_ONLY) && $user->id == $object->user_approve_id2))
						{
							print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=reopen">'.$langs->trans("Disapprove").'</a>';
						}
					}
				}
				if (in_array($object->statut, array(3, 4, 5, 6, 7, 9)))
				{
					if ($user->rights->wip->commander)
					{
						print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=reopen">'.$langs->trans("ReOpen").'</a>';
					}
				}

				// Make Order
				if ($object->statut == 2)
				{
					if ($user->rights->wip->commander)
					{
						print '<div class="inline-block divButAction"><a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=makeorder#makeorder">'.$langs->trans("MakeOrder").'</a></div>';
					}
					else
					{
						print '<div class="inline-block divButAction"><a class="butActionRefused" href="#">'.$langs->trans("MakeOrder").'</a></div>';
					}
				}

				// Create bill
				if (! empty($conf->facture->enabled))
				{
					if (! empty($conf->fournisseur->enabled) && ($object->statut >= 2 && $object->statut != 7 && $object->billed != 1))  // statut 2 means approved, 7 means canceled
					{
						if ($user->rights->fournisseur->facture->creer)
						{
							print '<a class="butAction" href="'.DOL_URL_ROOT.'/fourn/facture/card.php?action=create&amp;origin='.$object->element.'&amp;originid='.$object->id.'&amp;socid='.$object->socid.'">'.$langs->trans("CreateBill").'</a>';
						}
					}
				}

				// Classify billed manually (need one invoice if module invoice is on, no condition on invoice if not)
				if ($user->rights->wip->write && $object->statut >= 2 && $object->statut != 7 && $object->billed != 1)  // statut 2 means approved
				{
					if (empty($conf->facture->enabled))
					{
						print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=classifybilled">'.$langs->trans("ClassifyBilled").'</a>';
					}
					else if (!empty($object->linkedObjectsIds['invoice_supplier']))
					{
						if ($user->rights->fournisseur->facture->creer)
						{
							print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=classifybilled">'.$langs->trans("ClassifyBilled").'</a>';
						}
					}
				}

				// Clone
				if ($user->rights->wip->write)
				{
					print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&amp;socid='.$object->socid.'&amp;action=clone&amp;object=order">'.$langs->trans("ToClone").'</a>';
				}

				// Cancel
				if ($object->statut == 2)
				{
					if ($user->rights->wip->commander)
					{
					print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=cancel">'.$langs->trans("CancelOrder").'</a>';
					}
				}

				// Delete
				if ($user->rights->wip->write)
				{
					print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'&amp;action=delete">'.$langs->trans("Delete").'</a>';
				}
			}	// End Reshooks
			print '</div>';	// End TabsAction
		}


/*
		if ($user->rights->wip->commander && $object->statut == 2 && $action == 'makeorder')
		{
			// Set status to ordered (action=commande)
			print '<!-- form to record supplier order -->'."\n";
			print '<form name="commande" id="makeorder" action="card.php?id='.$object->id.'&amp;action=commande" method="post">';

			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<input type="hidden"	name="action" value="commande">';
			print load_fiche_titre($langs->trans("ToOrder"),'','');
			print '<table class="noborder" width="100%">';
			//print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("ToOrder").'</td></tr>';
			print '<tr><td>'.$langs->trans("OrderDate").'</td><td>';
			$date_com = dol_mktime(GETPOST('rehour','int'), GETPOST('remin','int'), GETPOST('resec','int'), GETPOST('remonth','int'), GETPOST('reday','int'), GETPOST('reyear','int'));
			if (empty($date_com)) $date_com=dol_now();
			print $form->select_date($date_com,'',1,1,'',"commande",1,1,1);
			print '</td></tr>';

			print '<tr><td>'.$langs->trans("OrderMode").'</td><td>';
			$formorder->selectInputMethod(GETPOST('methodecommande'), "methodecommande", 1);
			print '</td></tr>';

			print '<tr><td>'.$langs->trans("Comment").'</td><td><input size="40" type="text" name="comment" value="'.GETPOST('comment').'"></td></tr>';
			print '<tr><td align="center" colspan="2">';
			print '<input type="submit" name="makeorder" class="button" value="'.$langs->trans("ToOrder").'">';
			print ' &nbsp; &nbsp; ';
			print '<input type="submit" name="cancel" class="button" value="'.$langs->trans("Cancel").'">';
			print '</td></tr>';
			print '</table>';

			print '</form>';
			print "<br>";
		}
*/

		// Select mail models is same action as presend
		if (GETPOST('modelselected')) $action = 'presend';

		if ($action != 'presend') {
			/*
			 * Photographs
			 */
			print '<div class="fichecenter">';


			/*
			 * Generated documents
			 */
			print '<a name="builddoc"></a>'; // ancre
			if ($projectstatic->socid > 0)
			{
				$psociete=new Societe($db);
				$psociete->fetch($projectstatic->socid);
			}



			/* //////////////////////////////////////////// */
			$objref = dol_sanitizeFileName($object->ref);
			$projref = dol_sanitizeFileName($projectstatic->ref);
	
			$fname = $objref.' '.$projref;
			$fname.= (! empty($psociete->name)?(' - '.trim(dol_trunc($psociete->name,20,'right','UTF-8', 1, 0))):'');
			$fname.= (! empty($projectstatic->title)?(' - '.trim(dol_trunc($projectstatic->title,24,'right','UTF-8', 1, 0))):'');
			$filename = trim(dol_sanitizeFileName($fname));
			$modulesubdir = $projref .'/Reports/';
	
			$relativepath = '../../documents/projet/' . $projref .'/Reports/' . $filename . '.pdf';
			$filedir = $conf->projet->dir_output . '/' . $projref .'/Reports';
			$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
			$urlsource .= '&withproject=1';
			$genallowed = $user->rights->wip->read;	// If you can read, you can build the PDF to read content
			$delallowed = $user->rights->wip->write;	// If you can create/edit, you can remove a file on card
//			print $formfile->showdocuments('wip_report', '', $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
			print $formfile->showdocuments('wip', $modulesubdir, $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);

			/* ////////////////////////////////////////// */

			/*
			$objref = dol_sanitizeFileName($object->ref);
			$projref = dol_sanitizeFileName($projectstatic->ref);

			$fname = $objref.' '.$projref;
			$fname.= (! empty($psociete->name)?(' - '.trim(dol_trunc($psociete->name,20,'right','UTF-8', 1, 0))):'');
			$fname.= (! empty($projectstatic->title)?(' - '.trim(dol_trunc($projectstatic->title,24,'right','UTF-8', 1, 0))):'');
			$filename = trim(dol_sanitizeFileName($fname));

			$modulesubdir = $projref .'/Reports/';
			$relativepath = DOL_DOCUMENT_ROOT.'/documents/projet/' . $projref .'/Reports/' . $filename . '.pdf';
			$filedir = $conf->projet->dir_output . '/' . $projref .'/Reports';
			$urlsource = $_SERVER["PHP_SELF"] . "?id=" . $object->id;
			$urlsource .= '&withproject=1';
			$genallowed = $user->rights->wip->read;	// If you can read, you can build the PDF to read content
			$delallowed = $user->rights->wip->write;	// If you can create/edit, you can remove a file on card
		    print $formfile->showdocuments('wip', '', $filedir, $urlsource, $genallowed, $delallowed, $object->modelpdf, 1, 0, 0, 28, 0, '', '', '', $soc->default_lang);
			*/
			// Show direct download link
			if ($object->statut != Facture::STATUS_DRAFT && ! empty($conf->global->INVOICE_ALLOW_EXTERNAL_DOWNLOAD))
			{
				print '<!-- Link to download main doc -->'."\n";
				print showDirectDownloadLink($object);
			}
			print '<br>';
			//print '</div>';


			/*
			 * Related Objects
			 */
			// Show links to link elements
			print '<div class="fichehalfleft">';

			$somethingshown=$formfile->numoffiles;
			$linktoelem = $form->showLinkToObjectBlock($object, null, array('report'));
			$somethingshown = $form->showLinkedObjectBlock($object, $linktoelem);

			/*
			 * Invoice Status
			 */
			if (1 == 1)
			//if ($user->rights->wip->receptionner	&& ($object->statut == 3 || $object->statut == 4))
			{
				// Set status to received (action=livraison)
				print '<!-- form to record supplier order received -->'."\n";
				print '<br>';
				print '<form action="card.php?id='.$object->id.'" method="post">';
				print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
				print '<input type="hidden"	name="action" value="livraison">';
				print load_fiche_titre($langs->trans("InvoiceStatus"),'','');

				print '<table class="noborder" width="100%">';
				//print '<tr class="liste_titre"><td colspan="2">'.$langs->trans("Receive").'</td></tr>';
				print '<tr><td>'.$langs->trans("RecordDate").'</td><td>';
				$datepreselected = dol_now();
				print $form->select_date($datepreselected,'',1,1,'',"commande",1,1,1);
				print "</td></tr>\n";

				print "<tr><td class=\"fieldrequired\">".$langs->trans("Delivery")."</td><td>\n";
				$liv = array();
				$liv[''] = '&nbsp;';
				$liv['tot']	= $langs->trans("CompleteOrNoMoreReceptionExpected");
				$liv['par']	= $langs->trans("PartialWoman");
				$liv['nev']	= $langs->trans("NeverReceived");
				$liv['can']	= $langs->trans("Canceled");
				print $form->selectarray("type",$liv);

				print '</td></tr>';
				print '<tr><td>'.$langs->trans("Comment").'</td><td><input size="40" type="text" name="comment"></td></tr>';
				print '<tr><td align="center" colspan="2"><input type="submit" class="button" value="'.$langs->trans("Receive").'"></td></tr>';
				print "</table>\n";
				print "</form>\n";
				print "<br>";
			}

			print '</div><div class="fichehalfright"><div class="ficheaddleft">';
			/*
			 * Latest linked events
			 */

			$MAXEVENT = 10;

			$morehtmlright = '<a href="'.dol_buildpath('/wip/report_info.php', 1).'?id='.$object->id.'">';
			$morehtmlright.= $langs->trans("SeeAll");
			$morehtmlright.= '</a>';

			// List of actions on element
			include_once DOL_DOCUMENT_ROOT . '/core/class/html.formactions.class.php';
			$formactions = new FormActions($db);
			//$somethingshown = $formactions->showactions($object, 'task', $socid, 1, 'listaction'.($genallowed?'largetitle':''));
			$somethingshown = $formactions->showactions($object, 'report', $socid, 1, '', $MAXEVENT, '', $morehtmlright);

			print '</div></div>';
		}

		// Presend form
		$modelmail='report_send';
		$defaulttopic='InformationMessage';
		$diroutput = $conf->wip->dir_output;
		$trackid = 'report'.$object->id;

		include DOL_DOCUMENT_ROOT.'/core/tpl/card_presend.tpl.php';
}

// End of page
llxFooter();
$db->close();
