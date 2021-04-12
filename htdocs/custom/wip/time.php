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
 *	\file		htdocs/custom/wip/time.php
 *	\ingroup	wip
 *	\brief		Page to list time spent on a task for reporting
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
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/exports/class/export.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/export/modules_export.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions.lib.php';

include_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
dol_include_once('/wip/class/report.class.php');
dol_include_once('/wip/class/reportdet.class.php');
dol_include_once('/wip/lib/wip_reportdet.lib.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/doleditor.class.php';

// Load translation files required by the page
$langs->loadLangs(array('wip@wip', 'other', 'projects', 'companies'));

// Get parameters
$id				= GETPOST('id', 'int');
$ref			= GETPOST('ref', 'alpha');
$action			= GETPOST('action', 'alpha');
$confirm		= GETPOST('confirm', 'alpha');
$cancel			= GETPOST('cancel', 'aZ09');
$toselect		= GETPOST('toselect', 'array');
$withproject	= GETPOST('withproject','int');
$projectid		= GETPOST('projectid','int');
$project_ref	= GETPOST('project_ref','alpha');
$massaction		= GETPOST('massaction', 'alpha');
$model			= GETPOST('model', 'alpha');

$isproject		= GETPOST('isproject','int');

if ($isproject > 0)	// For next/prev to function the 'ref' needs to be allocated to 'project_ref' or will be confused as a Task ref.
{
	$project_ref = $ref;
	$ref = '';
	$isproject = 0;
}

// To show all time lines for project
$projectidforalltimes=0;
if (GETPOST('projectid','none'))
{
	$projectidforalltimes=GETPOST('projectid','int');
}

$diroutputmassaction=$conf->projet->dir_output . '/tasks/temp/massgeneration';

// Security check
$socid = (is_numeric($_GET["socid"]) ? $_GET["socid"] : 0 );
//if ($user->societe_id > 0) $socid = $user->societe_id;	// For external user, no check is done on company because readability is managed by public status of project and assignement.
if (!$user->rights->projet->lire) accessforbidden();

/*
// Load variable for pagination
$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');

if (empty($page) || $page == -1) { $page = 0; }	 // If $page is not defined, or '' or -1
if (! $sortfield) $sortfield='p.ref';
if (! $sortorder) $sortorder='ASC';
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
*/

// Initialise search criterias
$search_day			= GETPOST('search_day','int');
$search_month		= GETPOST('search_month','int');
$search_year		= GETPOST('search_year','int');
$search_datehour	= '';
$search_datewithhour= '';
$search_note		= GETPOST('search_note','alpha');
$search_duration	= GETPOST('search_duration','int');
$search_value		= GETPOST('search_value','int');
$search_task_ref	= GETPOST('search_task_ref','alpha');
$search_task_label	= GETPOST('search_task_label','alpha');
$search_user		= GETPOST('search_user','int');

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
//$object = new TaskTime($db);
$object = new Task($db);
$projectstatic = new Project($db);
$extrafields_project = new ExtraFields($db);
$extrafields_task = new ExtraFields($db);

//$hookmanager->initHooks(array('projecttasktime','globalcard'));
//$hookmanager->initHooks(array('projecttaskcard','globalcard'));
$hookmanager->initHooks(array('reportcard','globalcard'));

// Fetch optional attributes and labels
$extralabels_projet = $extrafields_project->fetch_name_optionals_label($projectstatic->table_element);
$extralabels_task = $extrafields_task->fetch_name_optionals_label($object->table_element);



/*	===========================================================================	*/
/*
 * Actions
 *
 * Put here all code to do according to value of "$action" parameter
 */
/*	===========================================================================	*/

// Cancel
if (GETPOST('cancel','alpha')) { $action=''; $massaction=''; }

// Purge massaction
if (! GETPOST('confirmmassaction','alpha') && $massaction != 'presend' && $massaction != 'confirm_presend' && $massaction != 'confirm_move2task' && $massaction != 'confirm_move2packet') { $massaction=''; } // PJR TODO

$parameters = array('socid'=>$socid, 'projectid'=>$projectid);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);	// Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// Selection of new fields
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
	{
		$search_day='';
		$search_month='';
		$search_year='';
		$search_date='';
		$search_datehour='';
		$search_datewithhour='';
		$search_note='';
		$search_duration='';
		$search_value='';
		$search_date_creation='';
		$search_date_update='';
		$search_task_ref='';
		$search_task_label='';
		$search_user=0;
		$toselect='';
		$search_array_options=array();
		$action='';
	}
	if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')
		|| GETPOST('button_search_x','alpha') || GETPOST('button_search.x','alpha') || GETPOST('button_search','alpha'))
	{
		$massaction='';	 // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass='Task';
	$objectlabel='Tasks';
	$permtoread = $user->rights->projet->lire;
	$permtodelete = $user->rights->projet->supprimer;
	$uploaddir = $conf->projet->dir_output.'/tasks';
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';

	if ($massaction == 'confirm_move2packet' || $massaction == 'confirm_move2task') {
		// PJR TODO create check that packet has not already been invoiced or reported
		$error				= 0;
		$dest_packet		= 0;
		$dest_task			= 0;
		$trigger_task		= 0;	// if later changed to 1, then Task totals need update
		$trigger_report		= 0;	// if later changed to 1, then Report totals need update
		$num_origin_packets = 0;

		$tasktimelist	= implode(", ", GETPOST('toselect','array'));	// List of Task-Times that have been selected

		if ($massaction == 'confirm_move2packet')
		{
			$tmparray		= explode('_',GETPOST('newpacket_id'));
			$dest_packet	= (! empty($tmparray[1]) ? $tmparray[1] : 0);
			$trigger_report	= 1;	// moving time entries from a packet can change report totals
			$trigger_task = 0;		// no need to recalculate task totals as time entries stay within task

			// 'selectTaskPackets' function can select the parent task to move time entries out of child packets
			if (! $dest_packet > 0) 
			{ 
				$dest_task = $id;		// if no destination packet, we are moving time entries to current task
			}
		}
		elseif ($massaction == 'confirm_move2task')
		{
			$tmparray		= explode('_',GETPOST('newtask_id'));
			$dest_task		= (! empty($tmparray[1]) ? $tmparray[1] : 0);
			$trigger_task	= 1;	// need to recalculate task totals as time entries move between tasks

			// 'selectProjectTasks' function can select a parent project and so need to check selection of a child task
			if (! $dest_task > 0) 
			{ 
				setEventMessages($langs->trans('A task / workorder needs to be selected.'), null, 'errors'); //PJR TODO
				$error++;
			}
			else if ($dest_task == $id)
			{
				setEventMessages($langs->trans('SameTask'), null, 'errors');
				$error++;
			}
		}

		// Create list of modified Packets affected by Time Entries either being moved to them, or originating in them
		if (! $error)
		{
			$modifiedpacketsarray=array();
			$packettmp=new ReportDet($db);
			$packetsarray=$packettmp->getPacketsArray(0, 0, 0, $tasktimelist, 2, $filteronproj, $filteronprojstatus, $morewherefilter);	// Array of Packets being modified
			$num_origin_packets=count($packetsarray); // Note, if zero then time entries come from a parent task only

			for ($i = 0 ; $i < $num_origin_packets ; $i++) {
				$modifiedpacketsarray[$i]=$packetsarray[$i]->rowid;	// Simplified array of Packets being modified
			}

			// If destination is a packet, add the 'destination Packet' to end of array of modified Packets
			if ($dest_packet > 0 ) $modifiedpacketsarray[$num_origin_packets]=$dest_packet;
			$modifiedpacketslist = implode(", ", $modifiedpacketsarray);

			if ($dest_packet > 0 || $num_origin_packets > 0) $trigger_report = 1;
		}
/*
		// If number of origin packets not >0 then, futilely, time entries are moving within same parent Task
		if (! $num_origin_packets > 0 && $massaction == 'confirm_move2packet')
		{
			setEventMessages($langs->trans('SameTask'), null, 'errors');
			$error++;
		}
*/
		if (! $error)
		{
			$db->begin();

			// Update Time Entries that have moved to existing or new task (not to a packet)
			if ($dest_task > 0 )
			{
				$sql = "UPDATE ".MAIN_DB_PREFIX."projet_task_time SET fk_task = ".$dest_task.", fk_reportdet = NULL WHERE rowid IN(".$tasktimelist.")";
				$res=$db->query($sql);
				if (! res) {
					setEventMessages($sql, null, 'errors');
					$error++;
				}

				// Recalculate time spent where time entries have moved between tasks
				// ------------------------------------------------------------------
				// Recalculate amount of time spent for current task
				$sql1 = 'UPDATE '.MAIN_DB_PREFIX.'projet_task';
				$sql1.= ' SET duration_effective = (SELECT SUM(task_duration) FROM '.MAIN_DB_PREFIX.'projet_task_time as ptt where ptt.fk_task = '.$id.')';
				$sql1.= ' WHERE rowid = '.$id;
				$res1=$db->query($sql1);
				if (! $res1) {
					setEventMessages('SQL1='.$sql1, null, 'errors');
					$error++;
				}

				// Recalculate amount of time spent for destination task (if not same as current)
				if ($dest_task != $id)
				{ 
					$sql2 = 'UPDATE '.MAIN_DB_PREFIX.'projet_task';
					$sql2.= ' SET duration_effective = (SELECT SUM(task_duration) FROM '.MAIN_DB_PREFIX.'projet_task_time as ptt where ptt.fk_task = '.$dest_task.')';
					$sql2.= ' WHERE rowid = '.$dest_task;
					$res2=$db->query($sql2);
					if (! $res2) {
						setEventMessages('SQL2='.$sql2, null, 'errors');
						$error++;
					}
				}
			}

			// Update Time Entries that have moved to a new packet
			if ($dest_packet > 0 )
			{
				// Update to new fk_reportdet for time entries that have moved to a new packet
				$sql3 = 'UPDATE '.MAIN_DB_PREFIX.'projet_task_time SET fk_reportdet = '.$dest_packet.' WHERE rowid IN('.$tasktimelist.')';
				// Show user who modified the packets
				$sql4= 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet SET fk_user_modif = '.$user->id.' WHERE rowid IN('.$modifiedpacketslist.')';

				$res3=$db->query($sql3);
				$res4=$db->query($sql4);

				if (! $res3 || ! $res4) {
					setEventMessages($langs->trans("NoRecordsMoved"), null, 'mesgs');
					$error++;
				}
			}
		}
		// Commit or rollback
		if ($error)
		{
			foreach($errors as $errmsg)
			{
				$error.=($error?', '.$errmsg:$errmsg);
			}
			$db->rollback();
		}
		else
		{
			$db->commit();
			setEventMessages($langs->trans("TimeRecordsMoved"), null, 'mesgs');
			$massaction		= '';
			$action			= '';
			$toselect		= '';
			$dest_packet	= '';
			$dest_task		= '';

			// Update Report totals for modified packets
			$result=$packettmp->updateReportSums($modifiedpacketsarray);
			if (! $result >= 0)
			{
					setEventMessages($langs->trans("NoReportTotalsUpdated"), null, 'errors');	// PJR TODO Trans
					$error++;
			}
			else
			{
				setEventMessages($langs->trans("ReportTotalsUpdated"), null, 'mesgs');	// PJR TODO Trans
			}
		}
	}
}

if ($action == 'addtimepacket' && $user->rights->projet->lire)
{
	$error=0;

/*	if (empty($timespent_durationhour) && empty($timespent_durationmin))
	{
		setEventMessages($langs->trans('ErrorFieldRequired',$langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error++;
	}
	if (empty($_POST["userid"]))
	{
		$langs->load("errors");
		setEventMessages($langs->trans('ErrorUserNotAssignedToTask'), null, 'errors');
		$error++;
	}
*/

	if (! $error)
	{
		$packet = new Reportdet($db);

		if (! $error)
		{
			/*
			public $rowid;
			public $ref;
			public $label;
			public $description;
			public $date_creation;
			public $tms;
			public $fk_user_creat;
			public $fk_user_modif;
			public $import_key;
			public $status;
			public $fk_report;
			public $fk_task;
			public $fk_parent_line;
			public $fk_assoc_line;
			public $product_type;
			public $qty;
			public $discount_percent;
			public $special_code;
			public $rang;
			public $rang_task;
			public $fk_product;
			public $date_start;
			public $date_end;
			*/

			$date_start	= dol_mktime(0,0,0,GETPOST('startdatemonth','int'),GETPOST('startdateday','int'),GETPOST('startdateyear','int'));
			$date_end	= dol_mktime(0,0,0,GETPOST('enddatemonth','int'),GETPOST('enddateday','int'),GETPOST('enddateyear','int'));

//print '$date_start: '.$date_start.'<br/>';
//print '$date_end: '.$date_end.'<br/>';
			$db->begin();

//			$packet->ref			= GETPOST('ref','alpha');
			$packet->label			= GETPOST('packet_label','none'); // Do not use 'alpha' here, we want field as it is
			$packet->description	= GETPOST('packet_desc','none'); // Do not use 'alpha' here, we want field as it is
			$packet->fk_task		= $id;
			$packet->date_creation	= dol_now();
			$packet->date_start		= $date_start;
			$packet->date_end		= $date_end;
//			$packet->fk_product		= ????;
//			$packet->product_type	= ????;

//		var_dump($_REQUEST);
//		print '<br/>';
//		print_r($packet);

		$sql = "INSERT INTO " . MAIN_DB_PREFIX . "wip_reportdet (";
		$sql.= "label";
		$sql.= ", description";
		$sql.= ", fk_task";
		$sql.= ", fk_user_creat";
		$sql.= ", date_creation";
		$sql.= ", date_start";
		$sql.= ", date_end";
		$sql.= ") VALUES (";
		$sql.= "'" . $packet->label . "'";
		$sql.= ", '" . $packet->description . "'";
		$sql.= ", " . ($id > 0 ? $id : "null");
		$sql.= ", " . $user->id;
		$sql.= ", '". $db->idate(dol_now())."'";
		$sql.= ", " . ($packet->date_start != '' ? "'".$db->idate($date_start)."'" : 'null');
		$sql.= ", " . ($packet->date_end != '' ? "'".$db->idate($date_end)."'" : 'null');
		$sql.= ")";

		$resql = $db->query($sql);
		if ($resql)

//			$result = $packet->create($user);
//			if ($result > 0)
			{
				$db->commit();
				setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');

				if ($backtopage)
				{
					header("Location: ".$backtopage.'&projectid='.$object->id);
					exit;
				}
/*				else
				{
					header("Location:card.php?id=".$object->id);
					exit;
				}
*/
			}
			else
			{
				$langs->load("errors");
				setEventMessages($langs->trans($packet->error), null, 'errors');
				$error++;

				$db->rollback();

				$action = 'create';
			}
		}
		else
		{
			$action = 'create';
		}
	}
	else
	{
		if (empty($id)) $action='createtimepacket';
		else $action='createtimepacket';
	}
}

/*
 * Update
 */
/*
if ($action == 'updateline' && ! $_POST["cancel"] && $user->rights->projet->lire)
{
	$error=0;

	if (empty($_POST["new_durationhour"]) && empty($_POST["new_durationmin"]))
	{
		setEventMessages($langs->trans('ErrorFieldRequired',$langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error++;
	}

	if (! $error)
	{
		$object->fetch($id, $ref);
		// TODO Check that ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))

		$object->timespent_id = $_POST["lineid"];
		$object->timespent_note = $_POST["timespent_note_line"];
		$object->timespent_old_duration = $_POST["old_duration"];
		$object->timespent_duration = $_POST["new_durationhour"]*60*60;	// We store duration in seconds
		$object->timespent_duration+= $_POST["new_durationmin"]*60;		// We store duration in seconds
		if (GETPOST("timelinehour") != '' && GETPOST("timelinehour") >= 0)	// If hour was entered
		{
			$object->timespent_date = dol_mktime(GETPOST("timelinehour"),GETPOST("timelinemin"),0,GETPOST("timelinemonth"),GETPOST("timelineday"),GETPOST("timelineyear"));
			$object->timespent_withhour = 1;
		}
		else
		{
			$object->timespent_date = dol_mktime(12,0,0,GETPOST("timelinemonth"),GETPOST("timelineday"),GETPOST("timelineyear"));
		}
		$object->timespent_fk_user = $_POST["userid_line"];

		$result=$object->updateTimeSpent($user);
		if ($result >= 0)
		{
			setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
		}
		else
		{
			setEventMessages($langs->trans($object->error), null, 'errors');
			$error++;
		}
	}
	else
	{
		$action='';
	}
}
*/
/*
 * Confirm Delete
 */
/*
if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->lire)
{
	$object->fetchTimeSpent(GETPOST('lineid','int'));
	// TODO Check that ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))
	$result = $object->delTimeSpent($user);

	if ($result < 0)
	{
		$langs->load("errors");
		setEventMessages($langs->trans($object->error), null, 'errors');
		$error++;
		$action='';
	}
	else
	{
		setEventMessages($langs->trans("RecordDeleted"), null, 'mesgs');
	}
}
*/



// Retreive First Task ID of Project if withproject is on to allow project prev next to work
if (! empty($project_ref) && ! empty($withproject))	// PJR TODO
{
	$projectstatic->fetch(0,$project_ref);
	if ($projectstatic->id > 0)
	{
		$tasksarray=$object->getTasksArray(0, 0, $projectstatic->id, $socid, 0);
		if (count($tasksarray) > 0)
		{
			$id=$tasksarray[0]->id;
		}
		else
		{
			header("Location: ".DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.($withproject?'&withproject=1':'').(empty($mode)?'':'&mode='.$mode));
			exit;
		}
	}
}


/*	===========================================================================	*/
/*
 * View
 *
 * Put here all code to render page
 */
/*	===========================================================================	*/

$form		= new Form($db);

$formother	= new FormOther($db);
$formproject = new FormProjets($db);
$userstatic = new User($db);

//$now = dol_now();

$help_url = '';
$title = $langs->trans("Task");
llxHeader('', $title, $help_url);

/*
// Example : Adding jquery code
print '<script type="text/javascript" language="javascript">
jQuery(document).ready(function() {
	function init_myfunc()
	{
		jQuery("#myid").removeAttr(\'disabled\');
		jQuery("#myid").attr(\'disabled\',\'disabled\');
	}
	init_myfunc();
	jQuery("#mybutton").click(function() {
		init_myfunc();
	});
});
</script>';
*/



if (($id > 0 || ! empty($ref)) || $projectidforalltimes > 0)
{
	/*
	 * Fiche projet en mode visu
	 * Project sheet in visual mode
 	 */
	if ($projectidforalltimes)
	{
		$result=$projectstatic->fetch($projectidforalltimes);
		if (! empty($projectstatic->socid)) $projectstatic->fetch_thirdparty();
		$res=$projectstatic->fetch_optionals();
	}
	elseif ($object->fetch($id, $ref) >= 0)
	{
		if (! empty($conf->global->PROJECT_ALLOW_COMMENT_ON_TASK) && method_exists($object, 'fetchComments') && empty($object->comments)) $object->fetchComments();
		$result=$projectstatic->fetch($object->fk_project);
		if (! empty($conf->global->PROJECT_ALLOW_COMMENT_ON_PROJECT) && method_exists($projectstatic, 'fetchComments') && empty($projectstatic->comments)) $projectstatic->fetchComments();
		if (! empty($projectstatic->socid)) $projectstatic->fetch_thirdparty();
		$res=$projectstatic->fetch_optionals();

		$object->project = clone $projectstatic;
	}

	$userWrite = $projectstatic->restrictedProjectArea($user,'write');

	if ($projectstatic->id > 0)
	{
		if (! empty($withproject))
		{
			// Tabs for project
			if (empty($id)) $tab='timespent';
			else $tab='tasks';
			$head=project_prepare_head($projectstatic);
			dol_fiche_head($head, $tab, $langs->trans("Project"), -1, ($projectstatic->public?'projectpub':'project'), 0, '', '');

			$param=($mode=='mine'?'&mode=mine':'');

	/* =================
	 *
	 * Project Banner
	 * 
	 * =================
	 */

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
			$moreparam='&isproject=1&withproject=1'; // used to differentiate between Project ref and Task ref

			// Define a complementary filter for search of next/prev ref.
			if (! $user->rights->projet->all->lire)
			{
				$objectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,0);
				$projectstatic->next_prev_filter=" rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
			}

			dol_banner_tab($projectstatic, 'project_ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $moreparam);

	/* =================
	 *
	 * Project Card
	 * 
	 * =================
	 */
			print '<div class="fichecenter">';
			print '<div class="fichehalfleft">';
			print '<div class="underbanner clearboth"></div>';

			print '<table class="border" width="100%">';

			// Visibility
			print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
			if ($projectstatic->public) print $langs->trans('SharedProject');
			else print $langs->trans('PrivateProject');
			print '</td></tr>';

			// Date start - end
			print '<tr><td>'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
			$start = dol_print_date($projectstatic->date_start,'day');
			print ($start?$start:'?');
			$end = dol_print_date($projectstatic->date_end,'day');
			print ' - ';
			print ($end?$end:'?');
			if ($projectstatic->hasDelay()) print img_warning("Late");
			print '</td></tr>';

			// Budget
			print '<tr><td>'.$langs->trans("Budget").'</td><td>';
			if (strcmp($projectstatic->budget_amount, '')) print price($projectstatic->budget_amount,'',$langs,1,0,0,$conf->currency);
			print '</td></tr>';

			// Other attributes
			$cols = 2;
			//include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

			print '</table>';

			print '</div>';
			print '<div class="fichehalfright">';
			print '<div class="ficheaddleft">';
			print '<div class="underbanner clearboth"></div>';

			print '<table class="border" width="100%">';

			// Description
			print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
			print nl2br($projectstatic->description);
			print '</td></tr>';

			// Bill time
			if (empty($conf->global->PROJECT_HIDE_TASKS) && ! empty($conf->global->PROJECT_BILL_TIME_SPENT))
			{
				print '<tr><td>'.$langs->trans("BillTime").'</td><td>';
				print yn($projectstatic->bill_time);
				print '</td></tr>';
			}

			// Categories
			if ($conf->categorie->enabled) {
				print '<tr><td valign="middle">'.$langs->trans("Categories").'</td><td>';
				print $form->showCategories($projectstatic->id,'project',1);
				print "</td></tr>";
			}

			print '</table>';

			print '</div>';
			print '</div>';
			print '</div>';

			print '<div class="clearboth"></div>';
			dol_fiche_end();
		}
	}

	if (empty($projectidforalltimes))
	{

		/*
		 * Actions
		*/

		// To verify role of users
		//$userAccess = $projectstatic->restrictedProjectArea($user); // We allow task affected to user even if a not allowed project
		//$arrayofuseridoftask=$object->getListContactId('internal');

		$head		= task_prepare_head($object);
		$tab		= GETPOST('tab')?GETPOST('tab'):'packets'; //PJR TODO review
		$tabtitle	= $langs->trans('Task');
//		$tabpicto	= dol_buildpath('/wip/img/object_report.png',1);
		$tabpicto	= 'projecttask';

		// Section to edit record
		// ------------------------------------------------------------
		if ($action == 'edit' && $user->rights->projet->creer)	// EDIT
		{
			/* =================
			 *
			 * Report card - EDIT
			 * 
			 * =================
			 */

		}
		else													// VIEW
		{
			/* =================
			 *
			 * Task Card
			 * 
			 * =================
			 */

			/*
			 * Task card in visual mode
			 */
			 // ------------------------------------------------------------

			$param = ($withproject?'&withproject=1':'');
			$linkback = $withproject?'<a href="' . DOL_URL_ROOT . '/projet/tasks.php?id='.$projectstatic->id.'">' . $langs->trans("BackToList").'</a>':'';

			// Tabs for Task
			dol_fiche_head($head, $tab, $tabtitle, -1, $tabpicto, 0, '', 'reposition');

			// ------------------------------------------------------------
			$formconfirm='';

			// Confirmation to delete

			// Confirmation of validation

			// Confirm approval

			// Confirmation of disapproval

			// Confirmation of cancellation

			// Confirmation of the sending of the order

			// Confirmation to delete line
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
			/*
			if (! $formconfirm) {
				$parameters = array('lineid'=>$lineid);
				$reshook = $hookmanager->executeHooks('formConfirm', $parameters, $object, $action); // Note that $action and $object may have been modified by hook
				if (empty($reshook)) $formconfirm.=$hookmanager->resPrint;
				elseif ($reshook > 0) $formconfirm=$hookmanager->resPrint;
			}

			// Print form confirm
			print $formconfirm;
			*/

// *******************************************************
			if ($user->rights->projet->all->creer || $user->rights->projet->creer)
			{
				if ($projectstatic->public || $userWrite > 0)
				{
					if (! empty($projectidforalltimes))		// We are on tab 'Time Spent' of Project
					{
						$backtourl = $_SERVER['PHP_SELF'].'?projectid='.$projectstatic->id.($withproject?'&withproject=1':'');
						//$linktocreatetime = '<a class="butActionNew" href="'.$_SERVER['PHP_SELF'].'?withproject=1&projectid='.$projectstatic->id.'&action=createtime'.$param.'&backtopage='.urlencode($backtourl).'">'.$langs->trans('AddTimeSpent').'<span class="fa fa-plus-circle valignmiddle"></span></a>';
						$linktocreatetimepacket = '';		// We only want time packets within Tasks
					}
					else									// We are on tab 'Time Spent' of Task
					{
						$backtourl = $_SERVER['PHP_SELF'].'?id='.$object->id.($withproject?'&withproject=1':'');
						$linktocreatetimepacket = '<a class="butActionNew" href="'.$_SERVER['PHP_SELF'].'?withproject=1'.($object->id > 0 ? '&id='.$object->id : '&projectid='.$projectstatic->id).'&action=createtimepacket'.$param.'&backtopage='.urlencode($backtourl).'">'.$langs->trans('Add Time Packet').'<span class="fa fa-plus-circle valignmiddle"></span></a>';
					}
				}
				else
				{
					$linktocreatetimepacket = '<a class="butActionNewRefused" href="#" title="'.$langs->trans("NotOwnerOfProject").'">'.$langs->trans('Add Time Packet').'<span class="fa fa-plus-circle valignmiddle"></span></a>';
				}
			}
			else
			{
				$linktocreatetimepacket = '<a class="butActionNewRefused" href="#" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('Add Time Packet').'<span class="fa fa-plus-circle valignmiddle"></span></a>';
			}
		}


// *******************************************************

		if (! GETPOST('withproject') || empty($projectstatic->id))
		{
			$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,1);
			$object->next_prev_filter=" fk_project in (".$projectsListId.")";
		}
		else $object->next_prev_filter=" fk_project = ".$projectstatic->id;

		$morehtmlref='';

		// Project
		if (empty($withproject))
		{
			$morehtmlref.='<div class="refidno">';
			$morehtmlref.=$langs->trans("Project").': ';
			$morehtmlref.=$projectstatic->getNomUrl(1);
			$morehtmlref.='<br>';

			// Third party
			$morehtmlref.=$langs->trans("ThirdParty").': ';
			if (is_object($projectstatic->thirdparty)) {
				$morehtmlref.=$projectstatic->thirdparty->getNomUrl(1);
			}
			$morehtmlref.='</div>';
		}

		dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $param);

		print '<div class="fichecenter">';
		print '<div class="fichehalfleft">';

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';

		// Date start - Date end
		print '<tr><td class="titlefield">'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
		$start = dol_print_date($object->date_start,'dayhour');
		print ($start?$start:'?');
		$end = dol_print_date($object->date_end,'dayhour');
		print ' - ';
		print ($end?$end:'?');
		if ($object->hasDelay()) print img_warning("Late");
		print '</td></tr>';

		// Planned workload
		print '<tr><td>'.$langs->trans("PlannedWorkload").'</td><td>';
		if ($object->planned_workload)
		{
			print convertSecondToTime($object->planned_workload,'allhourmin');
		}
		print '</td></tr>';

		print '</table>';
		print '</div>';

		print '<div class="fichehalfright"><div class="ficheaddleft">';

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';

		// Progress declared
		print '<tr><td class="titlefield">'.$langs->trans("ProgressDeclared").'</td><td>';
		print $object->progress != '' ? $object->progress.' %' : '';
		print '</td></tr>';

		// Progress calculated
		print '<tr><td>'.$langs->trans("ProgressCalculated").'</td><td>';
		if ($object->planned_workload)
		{
			$tmparray=$object->getSummaryOfTimeSpent();
			if ($tmparray['total_duration'] > 0) print round($tmparray['total_duration']/$object->planned_workload*100, 2).' %';
			else print '0 %';
		}
		else print '<span class="opacitymedium">'.$langs->trans("WorkloadNotDefined").'</span>';
		print '</td></tr>';

		print '</table>';

		print '</div>';
		print '</div>';

		print '</div>';
		print '<div class="clearboth"></div>';
//		print '<br>';

/*

			if (! empty($conf->global->MAIN_DISABLE_NOTES_TAB)) {
				$blocname = 'notes';
				$title = $langs->trans('Notes');
				include DOL_DOCUMENT_ROOT . '/core/tpl/bloc_showhide.tpl.php';
				print '<div class="clearboth"></div>';
			}

*/

		/* =================
		 *
		 * Start of Main Query
		 * 
		 * =================
		 */

		if (! $sortfield) $sortfield='wrd.rang_task,t.fk_reportdet,t.task_date,t.task_datehour,t.rowid';
		if (! $sortorder) $sortorder='ASC,ASC,ASC,ASC,ASC';

		//  List of time spent
		$tasks = array();
		$sql = 'SELECT';
		$sql .= " t.rowid, t.fk_task, t.task_date, t.task_datehour, t.task_date_withhour, t.task_duration, t.fk_user, t.note, t.thm, t.fk_reportdet,";
		$sql .= " wrd.rang, wrd.rang_task,";
		$sql .= " pt.ref, pt.label,";
		$sql .= " u.lastname, u.firstname, u.login, u.photo, u.statut as user_status,";
		$sql .= " il.fk_facture as invoice_id, il.total_ht";
		$sql .= " FROM ".MAIN_DB_PREFIX."projet_task_time as t";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."wip_reportdet as wrd ON t.rowid = wrd.fk_task";
		$sql .= " LEFT JOIN ".MAIN_DB_PREFIX."facturedet as il ON il.rowid = t.invoice_line_id";
		$sql .= ", ".MAIN_DB_PREFIX."projet_task as pt, ".MAIN_DB_PREFIX."user as u";
		$sql .= ' WHERE 1=1';
		$sql .= " AND t.fk_user = u.rowid AND t.fk_task = pt.rowid";
		if (empty($projectidforalltimes)) $sql .= " AND t.fk_task =".$object->id;
		else $sql.= " AND pt.fk_projet IN (".$projectidforalltimes.")";
		if ($search_ref) $sql .= natural_search('c.ref', $search_ref);
		if ($search_note) $sql .= natural_search('t.note', $search_note);
		if ($search_task_ref) $sql .= natural_search('pt.ref', $search_task_ref);
		if ($search_task_label) $sql .= natural_search('pt.label', $search_task_label);
		if ($search_user > 0) $sql .= natural_search('t.fk_user', $search_user);
		if ($search_month > 0)
		{
			if ($search_year > 0 && empty($search_day))
			$sql.= " AND t.task_datehour BETWEEN '".$db->idate(dol_get_first_day($search_year,$search_month,false))."' AND '".$db->idate(dol_get_last_day($search_year,$search_month,false))."'";
			else if ($search_year > 0 && ! empty($search_day))
			$sql.= " AND t.task_datehour BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_month, $search_day, $search_year))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_month, $search_day, $search_year))."'";
			else
			$sql.= " AND date_format(t.task_datehour, '%m') = '".$db->escape($search_month)."'";
		}
		else if ($search_year > 0)
		{
			$sql.= " AND t.task_datehour BETWEEN '".$db->idate(dol_get_first_day($search_year,1,false))."' AND '".$db->idate(dol_get_last_day($search_year,12,false))."'";
		}

		$sql .= $db->order($sortfield, $sortorder);

		$resql = $db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$totalnboflines=$num;
			$i = 0;
			while ($i < $num)
			{
				$row = $db->fetch_object($resql);
				$tasks[$i] = $row;
				$i++;
			}
			$db->free($resql);
		}
		else
		{
			dol_print_error($db);
		}

			/*	===============================================================	*/
			/*																	*/
			/*	Lines															*/
			/*																	*/
			/*	===============================================================	*/

	if ($projectstatic->id > 0)
	{
/*
		if ($action == 'deleteline' && ! empty($projectidforalltimes))
		{
			print $form->formconfirm($_SERVER["PHP_SELF"]."?".($object->id>0?"id=".$object->id:'projectid='.$projectstatic->id).'&lineid='.GETPOST('lineid','int').($withproject?'&withproject=1':''),$langs->trans("DeleteATimeSpent"),$langs->trans("ConfirmDeleteATimeSpent"),"confirm_delete",'','',1);
		}
*/

		/*	====================== Array of Selected ======================	*/
		// Definition of fields for list
		$arrayfields=array();
		$arrayfields['t.task_date']=array('label'=>$langs->trans("Date"), 'checked'=>1);
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
		{
			$arrayfields['t.task_ref']=array('label'=>$langs->trans("RefTask"), 'checked'=>1);
			$arrayfields['t.task_label']=array('label'=>$langs->trans("LabelTask"), 'checked'=>1);
		}
//		$arrayfields['author']=array('label'=>$langs->trans("Employee"), 'checked'=>1);
		$arrayfields['author']=array('label'=>"Employee/Technician", 'checked'=>1);
		$arrayfields['t.note']=array('label'=>$langs->trans("Note"), 'checked'=>1);
		$arrayfields['t.task_duration']=array('label'=>$langs->trans("Duration"), 'checked'=>1);
		$arrayfields['value'] =array('label'=>$langs->trans("Value"), 'checked'=>1, 'enabled'=>(empty($conf->salaries->enabled)?0:1));
		$arrayfields['valuebilled'] =array('label'=>$langs->trans("AmountInvoiced"), 'checked'=>1, 'enabled'=>((! empty($conf->global->PROJECT_HIDE_TASKS) || empty($conf->global->PROJECT_BILL_TIME_SPENT))?0:1));
		// Extra fields
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
		{
			foreach($extrafields->attribute_label as $key => $val)
			{
				if (! empty($extrafields->attribute_list[$key])) $arrayfields["ef.".$key]=array('label'=>$extrafields->attribute_label[$key], 'checked'=>(($extrafields->attribute_list[$key]<0)?0:1), 'position'=>$extrafields->attribute_pos[$key], 'enabled'=>(abs($extrafields->attribute_list[$key])!=3 && $extrafields->attribute_perms[$key]));
			}
		}

		$arrayofselected=is_array($toselect)?$toselect:array();

		/*	================== Definition of Parameters ===================	*/
		$param='';
		if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
		if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;
		if ($search_month > 0) $param.= '&search_month='.urlencode($search_month);
		if ($search_year > 0) $param.= '&search_year='.urlencode($search_year);
		if ($search_user > 0) $param.= '&search_user='.urlencode($search_user);
		if ($search_task_ref != '') $param.= '&search_task_ref='.urlencode($search_task_ref);
		if ($search_task_label != '') $param.= '&search_task_label='.urlencode($search_task_label);
		if ($search_note != '') $param.= '&search_note='.urlencode($search_note);
		if ($search_duration != '') $param.= '&amp;search_field2='.urlencode($search_duration);
		if ($optioncss != '') $param.='&optioncss='.urlencode($optioncss);

		// Add $param from extra fields
		// include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

		if ($id) $param.='&id='.urlencode($id);
		if ($projectid) $param.='&projectid='.urlencode($projectid);
		if ($withproject) $param.='&withproject='.urlencode($withproject);

		/*	================= Form preceding table - Start ================	*/
		// Form to add time packet on task
		if ($action == 'createtimepacket' && $object->id > 0 && $user->rights->projet->lire)
		{
			print '<!-- form to add time packet on task -->'."\n";
			print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<input type="hidden" name="action" value="addtimepacket">';
			print '<input type="hidden" name="id" value="'.$object->id.'">';
			print '<input type="hidden" name="withproject" value="'.$withproject.'">';

			print '<div class="div-table-responsive-no-min">';
			print '<table class="noborder nohover" width="100%">';

			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("PacketLabel").'</td>';
			print '<td>'.$langs->trans("StartOptional").'</td>';
			print '<td>'.$langs->trans("EndOptional").'</td>';
			print '<td>'.$langs->trans("Description").'</td>';
			print '<td></td>';
			print "</tr>\n";

			print '<tr class="oddeven">';

			// Label
			print '<td>';
//			print '<textarea name="timespent_note" class="maxwidth100onsmartphone" rows="'.ROWS_2.'">'.($_POST['timespent_note']?$_POST['timespent_note']:'').'</textarea>';
//			print '<input type="text" name="packet_label" autofocus class="minwidth500" value="'.$label.'">';
			print '<input type="text" name="packet_label" autofocus class="flat minwidth200 maxwidth100onsmartphone" id="packet_label" maxlength="255" value="'.$label.'">';
			print '</td>';

			// Start Date
			print '<td class="maxwidthonsmartphone">';
			$startdate='';
			print $form->select_date($date_start, 'startdate', 0, 0, 1, '', 1, 1, 1);
//			print $form->selectDate($startdate, 'time', ($conf->browser->layout == 'phone'?2:1), 1, 2, "packet_start_date", 1, 0, 1);
			print '</td>';

			// End Date
			print '<td class="maxwidthonsmartphone">';
			$enddate='';
			print $form->select_date($date_end, 'enddate', 0, 0, 1, '', 1, 1, 1);
//			print $form->selectDate($enddate, 'time', ($conf->browser->layout == 'phone'?2:1), 1, 2, "packet_end_date", 1, 0, 1);
			print '</td>';

			// Description
			print '<td>';
			print '<textarea name="packet_desc" class="flat minwidth400 maxwidth200onsmartphone" rows="'.ROWS_2.'" style="margin-top: 5px; width: 90%">'.($_POST['packet_desc']?$_POST['packet_desc']:'').'</textarea>';
			print '</td>';

			print '<td align="center">';
			print '<input type="submit" name="save" class="button" value="'.$langs->trans("Add").'">';
			print ' &nbsp; ';
			print '<input type="submit" name="cancel" class="button" value="'.$langs->trans("Cancel").'">';
			print '</td></tr>';

			print '</table>';
			print '</div>';

			print '</form>';

			print '<br>';
		}
		/*	================= Form preceding table - End ==================	*/
	}



			/* =================
			 *
			 * Mass Actions
			 * 
			 * =================
			 */

		// List of mass actions available
		$arrayofmassactions =  array(
		'move2task'=>$langs->trans("Move to another Workorder"),
		'move2packet'=>$langs->trans("Move to another Packet"),
		);
		//if ($user->rights->projet->creer) $arrayofmassactions['predelete']=$langs->trans("Delete");
		if (in_array($massaction, array('move2task','move2packet'))) $arrayofmassactions=array();
		$massactionbutton=$form->selectMassAction('', $arrayofmassactions);

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

		$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
		$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
		if ($massactionbutton) $selectedfields.=$form->showCheckAddButtons('checkforselect', 1);

		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$id.'">';
		if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
		print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
/*

		if ($action == 'editline') print '<input type="hidden" name="action" value="updateline">';

		else print '<input type="hidden" name="action" value="list">';
*/
		print '<input type="hidden" name="action" value="list">';
		print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
		print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
		print '<input type="hidden" name="page" value="'.$page.'">';

		print '<input type="hidden" name="id" value="'.$id.'">';
		print '<input type="hidden" name="projectid" value="'.$projectidforalltimes.'">';
		print '<input type="hidden" name="withproject" value="'.$withproject.'">';



			// Title and links
		if (! empty($projectidforalltimes))
		{
			print '<!-- List of time spent for project -->'."\n";
			$title=$langs->trans("ListTaskTimeUserProject");
			//$linktotasks='<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'">'.$langs->trans("GoToListOfTasks").'</a>';
		}
		else
		{

			print '<!-- List of time spent for task -->'."\n";
			$title=$langs->trans("ListTaskTimeForTask");
		}

		// Print title with navigation controls for pagination
		print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'title_generic.png', 0, $linktocreatetimepacket, '', 0, 1, 0);

		if ($massaction == 'move2task')
		{
			//var_dump($_REQUEST);
			print '<input type="hidden" name="massaction" value="confirm_move2task">';

			print '<table class="border" width="100%" >';
			print '<tr>';
			print '<td class="titlefield right">';
//			print '<td class="titlefieldmiddle">';
			print $langs->trans('SelectDestTask').':';
			print '</td>';
			print '<td>';
			print $formother->selectProjectTasks($id, $projectid, 'newtask_id', 0, 0, 0, 1, 0, 1, '');
			print '</td>';
			print '</tr>';
			print '</table>';

		print '<br>';
			print '<div class="center">';
			print '<input type="submit" class="button" id="move2task" name="move2task" value="'.$langs->trans('Move time entries to this Workorder').'">  ';
			print '<input type="submit" class="button" id="cancel" name="cancel" value="'.$langs->trans('Cancel').'">';
			print '</div>';
			print '<br>';
		}

		if ($massaction == 'move2packet') {
			//var_dump($_REQUEST);
			$packet=new ReportDet($db);
			print '<input type="hidden" name="massaction" value="confirm_move2packet">';

			print '<table class="border" width="100%" >';
			print '<tr>';
			print '<td class="titlefield right">';
//			print '<td class="titlefieldmiddle">';
			print $langs->trans('SelectDestPacket').':';
			print '</td>';
			print '<td>';
			print $packet->selectTaskPackets('', $projectid, $id, 0, 'newpacket_id', 0, '', '',  '', 'minwidth400 maxwidth200onsmartphone', '');
			print '</td>';
			print '</tr>';
			print '</table>';

		print '<br>';
			print '<div class="center">';
			print '<input type="submit" class="button" id="move2packet" name="move2packet" value="'.$langs->trans('MoveToPacket').'">  ';
			print '<input type="submit" class="button" id="cancel" name="cancel" value="'.$langs->trans('Cancel').'">';
			print '</div>';
			print '<br>';
		}
		$moreforfilter = '';

		$parameters=array();
		$reshook=$hookmanager->executeHooks('printFieldPreListTitle',$parameters);	// Note that $action and $object may have been modified by hook
		if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
		else $moreforfilter = $hookmanager->resPrint;

		if (! empty($moreforfilter)) {
			print '<div class="liste_titre liste_titre_bydiv centpercent">';
			print $moreforfilter;
			print '</div>';
		}

		/*	================ Show description of content ==================	*/
		// Show description of content
			/*
			$contentdesc = $langs->trans('Billing status of workorders for Project').' - '.$object->ref;
			print '<div class="opacitymedium">';
			print $contentdesc.'<br><br>';
			print '</div>';
			*/

		include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

			// --------------------------------------------------------------------
			// --------------------------  TABLE HEAD  ----------------------------
			// --------------------------------------------------------------------
			print '<div class="div-table-responsive">';		// You can use div-table-responsive-no-min if you dont need reserved height for your table
		print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";

		$colspan=0;
		foreach($arrayfields as $key => $val) { if (! empty($val['checked'])) $colspan++; }

			// Title
			// --------------------------------------------------------------------
			print '<thead>';

		// Fields title search
		print '<tr class="liste_titre_filter">';
		// Date
		if (! empty($arrayfields['t.task_date']['checked']))
		{
			print '<td class="liste_titre">';
			if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_day" value="'.$search_day.'">';
			print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_month" value="'.$search_month.'">';
			$formother->select_year($search_year,'search_year',1, 20, 5);
			print '</td>';
		}
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
		{
			if (! empty($arrayfields['t.task_ref']['checked'])) print '<td class="liste_titre"><input type="text" class="flat maxwidth100" name="search_task_ref" value="'.dol_escape_htmltag($search_task_ref).'"></td>';
			if (! empty($arrayfields['t.task_label']['checked'])) print '<td class="liste_titre"><input type="text" class="flat maxwidth100" name="search_task_label" value="'.dol_escape_htmltag($search_task_label).'"></td>';
		}
		// Author
		if (! empty($arrayfields['author']['checked'])) print '<td class="liste_titre">'.$form->select_dolusers(($search_user > 0 ? $search_user : -1), 'search_user', 1, null, 0, '', '', 0, 0, 0, '', 0, '', 'maxwidth200').'</td>';
		// Note
		if (! empty($arrayfields['t.note']['checked'])) print '<td class="liste_titre"><input type="text" class="flat maxwidth100" name="search_note" value="'.dol_escape_htmltag($search_note).'"></td>';
		// Duration
		if (! empty($arrayfields['t.task_duration']['checked'])) print '<td class="liste_titre right"></td>';
		// Value in main currency
		if (! empty($arrayfields['value']['checked'])) print '<td class="liste_titre"></td>';
		// Value billed
		if (! empty($arrayfields['valuebilled']['checked'])) print '<td class="liste_titre"></td>';

		// Fields from hook
		$parameters=array('arrayfields'=>$arrayfields);
		$reshook=$hookmanager->executeHooks('printFieldListOption',$parameters);	// Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;

		// Action column
		print '<td class="liste_titre center">';
//		$searchpicto=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
		$searchpicto=$form->showFilterButtons();
		print $searchpicto;

		print '</td>';
		print '</tr>'."\n";

		print '<tr class="liste_titre">';
		if (! empty($arrayfields['t.task_date']['checked']))	  print_liste_field_titre($arrayfields['t.task_date']['label'],$_SERVER['PHP_SELF'],'t.task_date,t.task_datehour,t.rowid','',$param,'',$sortfield,$sortorder);
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
		{
			if (! empty($arrayfields['t.task_ref']['checked']))   print_liste_field_titre($arrayfields['t.task_ref']['label'],$_SERVER['PHP_SELF'],'pt.ref','',$param,'',$sortfield,$sortorder);
			if (! empty($arrayfields['t.task_label']['checked'])) print_liste_field_titre($arrayfields['t.task_label']['label'],$_SERVER['PHP_SELF'],'pt.label','',$param,'',$sortfield,$sortorder);
		}
		if (! empty($arrayfields['author']['checked']))		   print_liste_field_titre($arrayfields['author']['label'],$_SERVER['PHP_SELF'],'','',$param,'',$sortfield,$sortorder);
		if (! empty($arrayfields['t.note']['checked']))		   print_liste_field_titre($arrayfields['t.note']['label'],$_SERVER['PHP_SELF'],'t.note','',$param,'',$sortfield,$sortorder);
		if (! empty($arrayfields['t.task_duration']['checked']))  print_liste_field_titre($arrayfields['t.task_duration']['label'],$_SERVER['PHP_SELF'],'t.task_duration','',$param,'align="right"',$sortfield,$sortorder);
		if (! empty($arrayfields['value']['checked']))			print_liste_field_titre($arrayfields['value']['label'],$_SERVER['PHP_SELF'],'','',$param,'align="right"',$sortfield,$sortorder);
		if (! empty($arrayfields['valuebilled']['checked']))	  print_liste_field_titre($arrayfields['valuebilled']['label'],$_SERVER['PHP_SELF'],'il.total_ht','',$param,'align="right"',$sortfield,$sortorder);

		/*
		// Extra fields
		include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
		*/
		// Hook fields
		$parameters=array('arrayfields'=>$arrayfields,'param'=>$param,'sortfield'=>$sortfield,'sortorder'=>$sortorder);
		$reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);	// Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="center" width="80"',$sortfield,$sortorder,'maxwidthsearch ');
		print "</tr>\n";

		print '</thead>';

			// --------------------------------------------------------------------
			// --------------------------  TABLE BODY  ----------------------------
			// --------------------------------------------------------------------

		print '<tbody>';

		$i = 0;
		$tasktmp = new Task($db);
		$childids = $user->getAllChildIds();
		$total = 0;
		$totalvalue = 0;
		$totalarray=array();
		$lastpacketid=-1;

		// Loop on record
		// --------------------------------------------------------------------
		foreach ($tasks as $task_time)
		{
			if ($task_time->fk_reportdet != $lastpacketid) // Break found
			{
				if (empty($task_time->fk_reportdet))
				{
					print '<tr>';
					print '<td colspan="'.($colspan+1).'" class="wipred bold">Time entries not allocated to a Time Packet</td>';
					print '</tr>';
				}
				else
				{
					$packettmp = new Reportdet($db);
					$packettmp->fetch($task_time->fk_reportdet);
					$packettmp->rowid = $task_time->fk_reportdet;	// PJR TODO - not sure why this is necessary due to fetch not getting rowid

					if(! empty($packettmp->fk_product))
					{
						$product_static=new Product($db);
						$product_static->rowid = $packettmp->fk_product;
						$product_static->fetch($packettmp->fk_product);
					}
					if (! empty($packettmp->fk_assoc_line)) {
						$packetassoc = new Reportdet($db);
						$packetassoc->fetch($packettmp->fk_assoc_line);
						$packetassoc->rowid = $packettmp->fk_assoc_line;	// PJR TODO - not sure why this is necessary due to fetch not getting rowid
					}

					// packetCard fields
					$pc_packetid	= $packettmp->rowid;
					$pc_action		= $action;
					$pc_selected	= 0;
					$pc_mode		= 'time';

					print '<tr>';
					print '<td colspan="'.$colspan.'">';
					print '<br/><br/>';
					print '</td></tr>';
					print '<tr class="wipblue">';
						print '<td colspan="'.$colspan.'">';
						// ************************************
						// function packetCard($packetid, $action = '', $selected = 0, $mode = 'report_card')
						print $packettmp->packetCard($pc_packetid, $pc_action, $pc_selected, $pc_mode);
						print '</td>';
						print '<td rowspan="1" class = "valigntop center">';
						print img_edit();
						print '<br/>XXX</td>';
					print '</tr>';
/*					print '<tr class="wipblue">';
					print '<td colspan="'.$colspan.'">';
*/
// ************************************ 	function packetCard($packetid, $action = '', $selected = 0, $mode = 'report_card')
//					print $packettmp->packetCard($pc_packetid, $pc_action, $pc_selected, $pc_mode);
// ************************************
/*					print '</td></tr>';
					print '<tr class="wipblue">';
						print '<td colspan="'.$colspan.'">';

						print '<table class="border" width="100%" >';
						print '<tr class="liste_titre">';
//							print '<td>'.$langs->trans("Packet Number & Label").'</td>';
							print '<td>'.$langs->trans("Packet Time Span (Optional)").'</td>';
							print '<td>Created by</td>';
							print '<td>Last Modified By</td>';
							print '<td class = "center">Reported in Packet</td>';
							print '<td class = "center">Reporting Status</td>';
						print '</tr>';
						print '<tr>';
							print '<td class="tdoverflowmax150">';
//							print 'TP'.sprintf("%06d", $packettmp->rowid).' '.$packettmp->label;
//							print '</td><td>';
							print dol_print_date($packettmp->date_start,"%d %b %Y").' to '.dol_print_date($packettmp->date_end,"%d %b %Y");
							print '</td><td>';
		   					if ($packettmp->fk_user_creat)
							{
								$userstatic->id= $task_time->fk_user_creat;
								$userstatic->fetch($packettmp->fk_user_creat);
								print $userstatic->getNomUrl(-1);
							}
							print '</td><td>';
	   						if ($packettmp->fk_user_modif)
							{
								$userstatic->id= $task_time->fk_user_modif;
								$userstatic->fetch($packettmp->fk_user_modif);
								print $userstatic->getNomUrl(-1);
							}
							print '</td><td class = "center">';
							if ($packettmp->fk_assoc_line) print $packetassoc->getNomUrl(1);
							print '</td><td>';
							print '</td>';
						print '</tr>';
						print '<tr class="liste_titre">';
							print '<td>Labour Type</td>';
							print '<td colspan="2">'.$langs->trans("Description").'</td>';
							print '<td class = "center">Direct Hours<br/>(Decimal)</td>';
							print '<td class = "center">'.$langs->trans("Billing Status").'</td>';
						print '</tr>';
						print '<tr>';
							print '<td rowspan = "'.($packettmp->status == 0 ? '1' : '3').'" class="tdoverflowmax150 valigntop">'.(empty($packettmp->fk_product)?'&nbsp;&nbsp;':$product_static->getNomUrl(1).'&nbsp;&nbsp;'.dol_trunc($product_static->label,40)).'</td>';
							print '<td rowspan = "'.($packettmp->status == 0 ? '1' : '3').'" colspan="2" class="titlefieldmiddle wipwhite wiptextarea valigntop">'.$packettmp->description.'</td>';
							print '<td class = "valigntop center">'.$packettmp->qty.'</td>';
							print '<td class = "valigntop center">'.'&nbsp;'.'</td>';
						print '</tr>';
		   				if ($packettmp->status != 0)
						{
							print '<tr class="liste_titre">';
							print '<td class = "center">Discounted Hours<br/>(Decimal)</td>';
							print '<td class = "center">Discount Percent</td>';
							print '</tr><tr>';
							print '<td class = "valigntop center">'.'&nbsp;'.'</td>';
							print '<td class = "valigntop center">'.'&nbsp;'.'</td>';
							print '</tr>';
						}

						print '</table>';

					print '</td>';
					print '</tr>';
*/
				}
				$lastpacketid=$task_time->fk_reportdet;
			}

			print '<tr class="oddeven">';

			$date1		= $db->jdate($task_time->task_date);
			$date2		= $db->jdate($task_time->task_datehour);
			$datetmp	= dol_getdate(($date2?$date2:$date1));
			$dateday	= $datetmp['mday'];
			$datemonth	= $datetmp['mon'];
			$dateyear	= $datetmp['year'];

			// Date
			if (! empty($arrayfields['t.task_date']['checked']))
			{
				print '<td class="nowrap">';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
				{
					if (empty($task_time->task_date_withhour))
					{
						print $form->select_date(($date2?$date2:$date1),'timeline',3,3,2,"timespent_date",1,0,1);
						//print $form->selectDate(($date2?$date2:$date1), 'timeline', 3, 3, 2, "timespent_date", 1, 0);
					}
					else print $form->select_date(($date2?$date2:$date1),'timeline',1,1,2,"timespent_date",1,0,1);
					//else print $form->selectDate(($date2?$date2:$date1), 'timeline', 1, 1, 2, "timespent_date", 1, 0);
				}
				else
				{
					print dol_print_date(($date2?$date2:$date1),($task_time->task_date_withhour?'dayhour':'day'));
				}
				print '</td>';
				if (! $i) $totalarray['nbfield']++;
			}

			// Task ref
			if (! empty($arrayfields['t.task_ref']['checked']))
			{
				if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
				{
					print '<td class="nowrap">';
					$tasktmp->id = $task_time->fk_task;
					$tasktmp->ref = $task_time->ref;
					$tasktmp->label = $task_time->label;
					print $tasktmp->getNomUrl(1, 'withproject', 'time');
					print '</td>';
					if (! $i) $totalarray['nbfield']++;
				}
			}

			// Task label
			if (! empty($arrayfields['t.task_label']['checked']))
			{
				if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
				{
					print '<td class="nowrap">';
					print $task_time->label;
					print '</td>';
					if (! $i) $totalarray['nbfield']++;
				}
			}

			// User
			if (! empty($arrayfields['author']['checked']))
			{
				print '<td>';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
				{
					if (empty($object->id)) $object->fetch($id);
					$contactsoftask=$object->getListContactId('internal');
					if (!in_array($task_time->fk_user,$contactsoftask)) {
						$contactsoftask[]=$task_time->fk_user;
					}
					if (count($contactsoftask)>0) {
						print img_object('','user','class="hideonsmartphone"');
						print $form->select_dolusers($task_time->fk_user,'userid_line',0,'',0,'',$contactsoftask);
					}else {
						print img_error($langs->trans('FirstAddRessourceToAllocateTime')).$langs->trans('FirstAddRessourceToAllocateTime');
					}
				}
				else
				{
					$userstatic->id		 = $task_time->fk_user;
					$userstatic->lastname	= $task_time->lastname;
					$userstatic->firstname 	= $task_time->firstname;
					$userstatic->photo	  = $task_time->photo;
					$userstatic->statut	 = $task_time->user_status;
					print $userstatic->getNomUrl(-1);
				}
				print '</td>';
				if (! $i) $totalarray['nbfield']++;
			}

			// Note
			if (! empty($arrayfields['t.note']['checked']))
			{
				print '<td align="left">';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
				{
					print '<textarea name="timespent_note_line" width="95%" rows="'.ROWS_2.'">'.$task_time->note.'</textarea>';
				}
				else
				{
					print dol_nl2br($task_time->note);
				}
				print '</td>';
				if (! $i) $totalarray['nbfield']++;
			}

			// Time spent
			if (! empty($arrayfields['t.task_duration']['checked']))
			{
				print '<td align="right">';
				if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
				{
					print '<input type="hidden" name="old_duration" value="'.$task_time->task_duration.'">';
					print $form->select_duration('new_duration',$task_time->task_duration,0,'text');
				}
				else
				{
					print convertSecondToTime($task_time->task_duration,'allhourmin');
				}
				print '</td>';
				if (! $i) $totalarray['nbfield']++;
				if (! $i) $totalarray['totaldurationfield']=$totalarray['nbfield'];
				$totalarray['totalduration'] += $task_time->task_duration;
			}

			// Value spent
			if (! empty($arrayfields['value']['checked']))
			{
				print '<td align="right">';
				$value = price2num($task_time->thm * $task_time->task_duration / 3600);
				print price($value, 1, $langs, 1, -1, -1, $conf->currency);
				print '</td>';
				if (! $i) $totalarray['nbfield']++;
				if (! $i) $totalarray['totalvaluefield']=$totalarray['nbfield'];
				$totalarray['totalvalue'] += $value;
			}

			// Value billed
			if (! empty($arrayfields['valuebilled']['checked']))
			{
				print '<td align="right">';
				$valuebilled = price2num($task_time->total_ht);
				if (isset($task_time->total_ht)) print price($valuebilled, 1, $langs, 1, -1, -1, $conf->currency);
				print '</td>';
				if (! $i) $totalarray['nbfield']++;
				if (! $i) $totalarray['totalvaluebilledfield']=$totalarray['nbfield'];
				$totalarray['totalvaluebilled'] += $valuebilled;
			}

			/*
			// Extra fields
			include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
			*/

			// Fields from hook
			$parameters=array('arrayfields'=>$arrayfields, 'obj'=>$task_time);
			$reshook=$hookmanager->executeHooks('printFieldListValue',$parameters);	// Note that $action and $object may have been modified by hook
			print $hookmanager->resPrint;

			// Action column
			print '<td class="nowrap" align="center">';
//		print '<td class="center nowraponall">';

//		print '&nbsp;';
			print '<a href="/custom/timesheet/Timecard.php'.'?userid='.$userstatic->id.'&amp;year='.$dateyear.'&amp;month='.$datemonth.'&amp;day='.$dateday.'" target="_blank">';
			print img_edit($titlealt = 'Edit (New window)');
//			print img_view($titlealt = 'New window');
			print '</a>';
			print '&nbsp;&nbsp;';
			if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
			{
				$selected=0;
				//if (in_array($obj->id, $arrayofselected)) $selected=1;
				//print '<input id="cb'.$obj->id.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->id.'"'.($selected?' checked="checked"':'').'>';
				if (in_array($task_time->rowid, $arrayofselected)) $selected=1;
				print '<input id="cb'.$task_time->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$task_time->rowid.'"'.($selected?' checked="checked"':'').'>';
			}

/*
			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid)
			{
				print '<input type="hidden" name="lineid" value="'.$_GET['lineid'].'">';
				print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
				print '<br>';
				print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
			}
			else if ($user->rights->projet->lire || $user->rights->projet->all->creer)	// Read project and enter time consumed on assigned tasks
			{
				if ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids) || $user->rights->projet->all->creer)
				{
					//$param = ($projectidforalltimes?'projectid='.$projectidforalltimes.'&amp;':'').'.($withproject?'&amp;withproject=1':'');
					print '&nbsp;';
					print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$task_time->fk_task.'&amp;action=editline&amp;lineid='.$task_time->rowid.$param.'">';
					print img_edit();
					print '</a>';

					print '&nbsp;';
					print '<a class="reposition" href="'.$_SERVER["PHP_SELF"].'?id='.$task_time->fk_task.'&amp;action=deleteline&amp;lineid='.$task_time->rowid.$param.'">';
					print img_delete();
					print '</a>';
				}
			}
*/
			print '</td>';
			if (! $i) $totalarray['nbfield']++;

			print '</tr>';
			$i++;
		}

			// --------------------------------------------------------------------
			// End Loop
			// --------------------------------------------------------------------

		// Show total line
		if (isset($totalarray['totaldurationfield']) || isset($totalarray['totalvaluefield']))
		{
			print '<tr class="liste_total">';
			$i=0;
			while ($i < $totalarray['nbfield'])
			{
				$i++;
				if ($i == 1)
				{
					if ($num < $limit && empty($offset)) print '<td align="left">'.$langs->trans("Total").'</td>';
					else print '<td align="left">'.$langs->trans("Totalforthispage").'</td>';
				}
				elseif ($totalarray['totaldurationfield'] == $i) print '<td align="right">'.convertSecondToTime($totalarray['totalduration'],'allhourmin').'</td>';
				elseif ($totalarray['totalvaluefield'] == $i) print '<td align="right">'.price($totalarray['totalvalue']).'</td>';
				elseif ($totalarray['totalvaluebilledfield'] == $i) print '<td align="right">'.price($totalarray['totalvaluebilled']).'</td>';
				else print '<td></td>';
			}
			print '</tr>';
		}

		// Show any empty packets
		$mtpackets = array();
		$sql = 'SELECT';
		$sql .= ' wrd.rowid as packetid, wrd.fk_report as fk_report, wrd.fk_task as fk_task, wrd.fk_parent_line as fk_parent_line';
		$sql .= ', wrd.fk_assoc_line as fk_assoc_line, wrd.fk_product as fk_product, wrd.product_type as product_type';
		$sql .= ', wrd.ref as packetref, wrd.label as packetlabel, wrd.date_start as date_start, wrd.date_end as date_end';
		$sql .= ', wrd.description as packetdescription, wrd.qty as qty, wrd.discount_percent as discount_percent';
		$sql .= ', wrd.special_code as special_code, wrd.rang as rang, wrd.rang_task as rang_task';
		$sql .= ', wrd.date_creation as date_creation, wrd.tms as tms, wrd.fk_user_creat as fk_user_creat';
		$sql .= ', wrd.fk_user_modif as fk_user_modif, wrd.import_key as import_key, wrd.status as packetstatus';
		$sql .= ', ptt.rowid, ptt.fk_task, ptt.fk_reportdet';
//		$sql .= ' pt.ref, pt.label';
		$sql .= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet AS wrd';
		$sql .= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_time as ptt on (ptt.fk_reportdet = wrd.rowid)';
		$sql .= ' WHERE 1=1';
		$sql .= ' AND ptt.fk_reportdet IS NULL';
		if (empty($projectidforalltimes)) $sql .= ' AND wrd.fk_task = '.$object->id;
		$sql .= ' ORDER BY wrd.fk_task ASC, wrd.rang_task ASC, wrd.rang ASC, wrd.rowid ASC';

		$resql = $db->query($sql);
		if ($resql) {
			$num = $db->num_rows($resql);
			$i = 0;
			while ($i < $num)
			{
				$mtrow = $db->fetch_object($resql);
				$mtpackets[$i] = $mtrow;
				$i++;
			}
			$db->free($resql);
		} else {
			dol_print_error($db);
		}

		if	($num>0) {
			$mtheading = 'Empty Time Packet - no time-entries allocated';
			if	($num>1) $mtheading = 'Empty Time Packets - no time-entries allocated';
			print '<tr>';
			print '<td colspan="'.($colspan+1).'">';
			print '<br/><br/><br/>';
			print '</td></tr>';
			print '<tr>';
			print '<td colspan="'.($colspan+1).'" class="bold">'.$mtheading;
			print '</td></tr>';

			foreach ($mtpackets as $mtpacket)
			{
				$packettmp = new Reportdet($db);
				$packettmp->rowid = $mtpacket->packetid;	// PJR TODO - not sure why this is necessary due to fetch not getting rowid
				$packettmp->fetch($mtpacket->packetid);
				if(! empty($packettmp->fk_product)) {
					$product_static=new Product($db);
					$product_static->rowid = $packettmp->fk_product;
					$product_static->fetch($packettmp->fk_product);
				}
				if (! empty($packettmp->fk_assoc_line)) {
					$packetassoc = new Reportdet($db);
					$packetassoc->rowid = $packettmp->fk_assoc_line;	// PJR TODO - not sure why this is necessary due to fetch not getting rowid
					$packetassoc->fetch($packettmp->fk_assoc_line);
				}

				print '<tr>';
				print '<td colspan="'.($colspan+1).'">';
				print '<br/>';
				print '</td></tr>';
				print '<tr class="wipyellow">';
					print '<td colspan="'.$colspan.'" class="bold">';
					print $packettmp->getNomUrl(1).' - '.$packettmp->label;
					print '</td>';
					print '<td rowspan="2" class = "valigntop center">';
						print img_edit();
						print '<br/>XXX</td>';
				print '</tr>';
				print '<tr class="wipyellow">';
					print '<td colspan="'.$colspan.'">';

					print '<table class="border" width="100%" >';
					print '<tr class="liste_titre">';
//								print '<td>'.$langs->trans("Packet Number & Label").'</td>';
						print '<td>'.$langs->trans("Packet Time Span (Optional)").'</td>';
						print '<td>Created by</td>';
						print '<td>Last Modified By</td>';
						print '<td class = "center">Reported in Packet</td>';
						print '<td class = "center">Reporting Status</td>';
					print '</tr>';
					print '<tr>';
						print '<td class="tdoverflowmax150">';
//							print 'TP'.sprintf("%06d", $packettmp->rowid).' '.$packettmp->label;
//							print '</td><td>';
						print dol_print_date($packettmp->date_start,"%d %b %Y").' to '.dol_print_date($packettmp->date_end,"%d %b %Y");
						print '</td><td>';
						if ($packettmp->fk_user_creat)
						{
							$userstatic->id= $mtpacket->fk_user_creat;
							$userstatic->fetch($packettmp->fk_user_creat);
							print $userstatic->getNomUrl(-1);
						}
						print '</td><td>';
						if ($packettmp->fk_user_modif)
						{
							$userstatic->id= $mtpacket->fk_user_modif;
							$userstatic->fetch($packettmp->fk_user_modif);
							print $userstatic->getNomUrl(-1);
						}
						print '</td><td class = "center">';
						if ($packettmp->fk_assoc_line) print $packetassoc->getNomUrl(1);
						print '</td><td>';
						print '</td>';
					print '</tr>';
					print '<tr class="liste_titre">';
						print '<td>Labour Type</td>';
						print '<td colspan="2">'.$langs->trans("Description").'</td>';
						print '<td class = "center">Direct Hours<br/>(Decimal)</td>';
						print '<td class = "center">'.$langs->trans("Billing Status").'</td>';
					print '</tr>';
					print '<tr>';
						print '<td rowspan = "'.($packettmp->status == 0 ? '1' : '3').'" class="tdoverflowmax150 valigntop">'.(empty($packettmp->fk_product)?'&nbsp;&nbsp;':$product_static->getNomUrl(1).'&nbsp;&nbsp;'.dol_trunc($product_static->label,40)).'</td>';
						print '<td rowspan = "'.($packettmp->status == 0 ? '1' : '3').'" colspan="2" class="titlefieldmiddle wipwhite wiptextarea valigntop">'.$packettmp->description.'</td>';
						print '<td class = "valigntop center">'.$packettmp->qty.'</td>';
						print '<td class = "valigntop center">'.'&nbsp;'.'</td>';
					print '</tr>';
					if ($packettmp->status != 0)
					{
						print '<tr class="liste_titre">';
						print '<td class = "center">Discounted Hours<br/>(Decimal)</td>';
						print '<td class = "center">Discount Percent</td>';
						print '</tr><tr>';
						print '<td class = "valigntop center">'.'&nbsp;'.'</td>';
						print '<td class = "valigntop center">'.'&nbsp;'.'</td>';
						print '</tr>';
					}

					print '</table>';

				print '</td>';
				print '</tr>';

			}
		}




//			print '</tr>';

			// --------------------------------------------------------------------

		print '</tbody>';
		print '</table>';
			// --------------------------------------------------------------------
			// ---------------------------  END TABLE  ----------------------------
			// --------------------------------------------------------------------
		print '</div>';
		print '</form>';


/*
 *	Builddoc
 */

if ($id > 0 || ! empty($ref))
{
	/*
	 * Project sheet in visual mode
 	 */
	if ($object->fetch($id, $ref) >= 0)
	{
		$result=$projectstatic->fetch($object->fk_project);
		if (! empty($projectstatic->socid)) $projectstatic->fetch_thirdparty();
		$res=$projectstatic->fetch_optionals($object->id,$extralabels_projet);

		$object->project = clone $projectstatic;
	}

//	$userWrite = $projectstatic->restrictedProjectArea($user,'write');

	$filename=dol_sanitizeFileName($projectstatic->ref). "-". dol_sanitizeFileName($object->ref);
	$dirname=dol_sanitizeFileName($projectstatic->ref). "/". dol_sanitizeFileName($object->ref);
	$filedir=$conf->projet->dir_output . "/" . dol_sanitizeFileName($projectstatic->ref). "/" .dol_sanitizeFileName($object->ref);
	$urlsource=$_SERVER["PHP_SELF"]."?id=".$object->id.($withproject?'&withproject=1':'');
//	$genallowed=($user->rights->projet->lire);
//	$delallowed=($user->rights->projet->creer);

// Selected Fields Array
$array_selected = array(
	"ptt.rowid"				=>	1,
	"u.employeename"		=>	2,
//	"u.lastname"			=>	3,
	"ptt.task_datehour"		=>	3,
	"ptt.note"				=>	4,
	"ptt.task_duration"		=>	5,
	"s.nom"					=>	6,
	"p.ref"					=>	7,
	"p.title"				=>	8,
//	"pt.rowid"				=>	4,
	"pt.ref"				=>	9,
	"pt.label"				=>	10,
//	"pt.description"		=>	7,
	"pt.duration_effective"	=>	11,
	"pt.planned_workload"	=>	12,
	"pt.progress"			=>	13
//	"ptt.fk_user"			=>	15
);

$array_filtervalue = array ();

$array_labels = array(
	"ptt.rowid"				=>	'Time ID',
	"u.employeename"		=>	'Employee',
//	"u.lastname"			=>	'Emp. Lastname',
	"ptt.task_datehour"		=>	'Start Time',
	"ptt.note"				=>	'Note',
	"ptt.task_duration"		=>	'Time Spent (hrs)',
	"s.nom"					=>	'Customer name',
	"p.ref"					=>	'Project No.',
	"p.title"				=>	'Project Title',
//	"pt.rowid"				=>	'Ref. task',
	"pt.ref"				=>	'Work Order',
	"pt.label"				=>	'Work Order Title',
//	"pt.description"		=>	'Task description',
	"pt.duration_effective"	=>	'Hours Used',
	"pt.planned_workload"	=>	'Hours Planned',
	"pt.progress"			=>	'Progress %'
//	"ptt.fk_user"			=>	'User'
);

$array_types = array(
	"ptt.rowid"				=>	"Numeric",
	"u.employeename"		=>	"TextAuto",
	"u.lastname"			=>	"TextAuto",
	"ptt.task_datehour"		=>	"Time3",
	"ptt.note"				=>	"Text",
	"ptt.task_duration"		=>	"Duree",
	"s.nom"					=>	"TextAuto",
	"p.ref"					=>	"TextAuto",
	"p.title"				=>	"TextAuto",
//	"pt.rowid"				=>	"Numeric",
	"pt.ref"				=>	"TextAuto",
	"pt.label"				=>	"TextAuto",
//	"pt.description"		=>	"Text",
	"pt.duration_effective"	=>	"Time3",
	"pt.planned_workload"	=>	"Time3",
	"pt.progress"			=>	"Percent"
//	"ptt.fk_user"			=>	"List:user:CONCAT(u_lastname,' ',u_firstname)",
);


$sqlquery  = "SELECT ";
$sqlquery .= " s.rowid as s_rowid, s.nom as s_nom,";
$sqlquery .= " p.rowid as p_rowid, p.ref as p_ref, p.title as p_title,";
//extra.vehicleyear as extra_vehicleyear, extra.vehiclemodel as extra_vehiclemodel, extra.makename as extra_makename, 
$sqlquery .= " pt.rowid as pt_rowid, pt.ref as pt_ref, pt.label as pt_label,";
$sqlquery .= " pt.description as pt_description,";
$sqlquery .= " ROUND((pt.duration_effective / 3600),2) as pt_duration_effective,";
$sqlquery .= " ROUND((pt.planned_workload / 3600),2) as pt_planned_workload,";
$sqlquery .= " pt.progress as pt_progress,";
$sqlquery .= " ptt.rowid as ptt_rowid, ptt.task_datehour as ptt_task_datehour,";
$sqlquery .= " ROUND((ptt.task_duration / 3600),2) as ptt_task_duration,";
$sqlquery .= " ptt.fk_user as ptt_fk_user, ptt.note as ptt_note,";
$sqlquery .= " u.rowid as u_rowid, CONCAT( u.firstname, ' ', u.lastname) as u_employeename";

//pcf.makename as pcf_makename, pcf.colourname as pcf_colourname 
$sqlquery .= " FROM doli_projet as p";
//LEFT JOIN doli_projet_customfields as pcf ON p.rowid = pcf.fk_projet 
//LEFT JOIN doli_projet_extrafields as extra ON p.rowid = extra.fk_object 
//LEFT JOIN doli_c_lead_status as cls ON p.fk_opp_status = cls.rowid 
$sqlquery .= " LEFT JOIN doli_projet_task as pt ON p.rowid = pt.fk_projet";
//LEFT JOIN doli_projet_task_extrafields as extra2 ON pt.rowid = extra2.fk_object 
$sqlquery .= " LEFT JOIN doli_projet_task_time as ptt ON pt.rowid = ptt.fk_task";
$sqlquery .= " LEFT JOIN doli_societe as s ON p.fk_soc = s.rowid";
$sqlquery .= " LEFT JOIN doli_user as u ON ptt.fk_user = u.rowid";
$sqlquery .= " WHERE p.entity = 1 and pt.rowid=".$id;
$sqlquery .= " ORDER BY u_employeename, ptt_task_datehour";


$objexport=new Export($db);
$objexport->load_arrays(1,'projet_1');
$objmodelexport=new ModeleExports($db);
$form = new Form($db);
$formother = new FormOther($db);
$htmlother = new FormOther($db);
$formfile = new FormFile($db);
$sqlusedforexport='';




	// Build doc
	/**
	 *	  Build export file.
	 *	  File is built into directory $conf->export->dir_temp.'/'.$user->id
	 *	  Arrays this->array_export_xxx are already loaded for required datatoexport
	 *
	 *	  @param	  User		$user			   User that export
	 *	  @param	  string		$model			  Export format
	 *	  @param	  string		$datatoexport	   Name of dataset to export
	 *	  @param	  array		$array_selected	 Filter on array of fields to export
	 *	  @param	  array		$array_filterValue  Filter on array of fields with a filter
	 *	  @param		string		$sqlquery			If set, transmit the sql request for select (otherwise, sql request is generated from arrays)
	 *	  @return		int								<0 if KO, >0 if OK
	 */
	if ($action == 'builddoc')
	{
		$max_execution_time_for_export = (empty($conf->global->EXPORT_MAX_EXECUTION_TIME)?300:$conf->global->EXPORT_MAX_EXECUTION_TIME);	// 5mn if not defined
		$max_time = @ini_get("max_execution_time");
		if ($max_time && $max_time < $max_execution_time_for_export)
		{
			@ini_set("max_execution_time", $max_execution_time_for_export); // This work only if safe mode is off. also web servers has timeout of 300
		}

		// Build export file
		//$result=$objexport->build_file_tsr($user, GETPOST('model','alpha'), $datatoexport, $array_selected, $array_filtervalue, $sqlquery);

		$indice=0;
		asort($array_selected);

//		dol_syslog(get_class($this)."::".__FUNCTION__." ".$model.", ".$datatoexport.", ".implode(",", $array_selected));

		// Check parameters or context properties
		if (empty($db->array_export_fields) || ! is_array($db->array_export_fields))
		{
			$db->error="ErrorBadParameter";
			$result = -1;
		//	return -1;
		}

		// Creation of class to export using model ExportXXX
		$dir = DOL_DOCUMENT_ROOT . "/core/modules/export/";
		$file = "export_".$model.".modules.php";
		$classname = "Export".$model;
		require_once $dir.$file;
		$objmodel = new $classname($db);

/*
		if (! empty($sqlquery)) $sql = $sqlquery;
		else
		{
			// Define value for indice from $datatoexport
			$foundindice=0;
			foreach($this->array_export_code as $key => $dataset)
			{
				if ($datatoexport == $dataset)
				{
					$indice=$key;
					$foundindice++;
					//print "Found indice = ".$indice." for dataset=".$datatoexport."\n";
					break;
				}
			}
			if (empty($foundindice))
			{
				$this->error="ErrorBadParameter can't find dataset ".$datatoexport." into preload arrays this->array_export_code";
				return -1;
			}
			$sql=$this->build_sql($indice, $array_selected, $array_filterValue);
		}
*/
		$sql = $sqlquery;

		// Run the sql
		$db->sqlusedforexport=$sql;
//		dol_syslog(get_class($this)."::".__FUNCTION__."", LOG_DEBUG);
		$resql = $db->query($sql);
		if ($resql)
		{
			$filename.= "-Timespent";
			$filename.='.'.$objmodel->getDriverExtension();

			$outputlangs = clone $langs; // We clone to have an object we can modify (for example to change output charset by csv handler) without changing original value

			// Open file
			dol_mkdir($filedir);
			$result=$objmodel->open_file($filedir."/".$filename, $outputlangs);

			if ($result >= 0)
			{
				// Genere en-tete
				$objmodel->write_header($outputlangs);

				// Genere ligne de titre
//				$objmodel->write_title($this->array_export_fields[$indice],$array_selected,$outputlangs,$this->array_export_TypeFields[$indice]);
				$objmodel->write_title($array_labels,$array_selected,$outputlangs,$array_types);

				$var=true;
				$i = 0;
				while ($obj = $db->fetch_object($resql))
				{
					// Process special operations
/*
*/
					$objmodel->write_record($array_selected,$obj,$outputlangs,$array_types);
				}

				// Genere en-tete
				$objmodel->write_footer($outputlangs);

				// Close file
				$objmodel->close_file();
			//	return 1;
			}
			else
			{
				$error=$objmodel->error;
				dol_syslog("Export::build_file Error: ".$error, LOG_ERR);
			//	return -1;
				$result = -1;
			}
		}
		else
		{
		//	$this->error=$this->db->error()." - sql=".$sql;
		//	return -1;
			$result = -1;
		}

		if ($result < 0)
		{
			setEventMessages($objexport->error, $objexport->errors, 'errors');
			$sqlusedforexport=$objexport->sqlusedforexport;
		}
		else
		{
			setEventMessages($langs->trans("FileSuccessfullyBuilt"), null, 'mesgs');
			$sqlusedforexport=$objexport->sqlusedforexport;
		}
	}
}

//	$db->free($resql);

	/*
	 * Generated documents
	 */

	print '<br/>'.$langs->trans("NowClickToGenerateToBuildExportFile").'<br/>';

	print '<div class="fichecenter"><div class="fichehalfleft">';
	print '<a href="#builddoc" name="builddoc"></a>'; // anchor

	$liste=$objmodelexport->liste_modeles($db);
	$listeall=$liste;


	// Show list of available documents

	$var=true;

	if (! is_dir($filedir)) dol_mkdir($filedir);

	print $formfile->showdocuments('project',$dirname,$filedir,$urlsource,$liste,1,(! empty($_POST['model'])?$_POST['model']:'excel2007'),1,1);

//	print $formfile->showdocuments('export',$filename,$filedir,$urlsource,$genallowed,$delallowed,$object->modelpdf);


//$upload_dir = '/home/finchmc/public_html/erp/documents/projet/Reports';



	// Poster list of documents
	// NB: The function show_documents rescues the modules qd genallowed = 1, otherwise takes $liste
//	print $formfile->showdocuments('export','',$filedir,$urlsource,$liste,1,(! empty($_POST['model'])?$_POST['model']:'excel2007'),1,1);

//		print '</div>';


print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	// List of available export formats

	$var=true;
	print '<div class="titre">Available Formats</div>';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td colspan="2">'.$langs->trans("AvailableFormats").'</td>';
	print '<td>'.$langs->trans("LibraryUsed").'</td>';
	print '<td align="right">'.$langs->trans("LibraryVersion").'</td>';
	print '</tr>'."\n";

	foreach($listeall as $key => $val)
	{
		if (preg_match('/__\(Disabled\)__/',$listeall[$key]))
		{
			$listeall[$key]=preg_replace('/__\(Disabled\)__/','('.$langs->transnoentitiesnoconv("Disabled").')',$listeall[$key]);
			unset($liste[$key]);
		}
		print '<tr class="oddeven">';
		print '<td width="16">'.img_picto_common($key,$objmodelexport->getPictoForKey($key)).'</td>';
		$text=$objmodelexport->getDriverDescForKey($key);
		$label=$listeall[$key];
		print '<td>'.$form->textwithpicto($label,$text).'</td>';
		print '<td>'.$objmodelexport->getLibLabelForKey($key).'</td><td align="right">'.$objmodelexport->getLibVersionForKey($key).'</td></tr>'."\n";
	}
	print '</table>';

print '</div></div></div>';

	}
}

// End of page
llxFooter();
$db->close();
