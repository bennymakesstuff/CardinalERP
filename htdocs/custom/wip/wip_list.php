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
 *	\file		htdocs/custom/wip/wip_list.php
 *	\ingroup	wip
 *	\brief		Page to assess Work In Progress (WIP) for project
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
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';

// Load translation files required by the page
$langs->loadLangs(array('projects', 'companies', 'commercial', 'wip@wip'));

// Get Parameters
$id= GETPOST('id','int')!=''?GETPOST('id','int'):276; // for testing - delete later
//$id			= GETPOST('id','int');														// ID(rowid) of individual project of interest
$projectid	= $id;																		// For backward compatibility
$ref		= GETPOST('ref', 'alpha');													// Ref of individual project of interest
$taskref	= GETPOST('taskref', 'alpha');

$action		= GETPOST('action','aZ09')?GETPOST('action','aZ09'):'view';				// The action 'add', 'create', 'edit', 'update', 'view', ...
$backtopage = GETPOST('backtopage','alpha');											// Go back to a dedicated page
$cancel	 = GETPOST('cancel', 'alpha');												// We click on a Cancel button

$massaction = GETPOST('massaction','alpha');											// The bulk action (combo box choice into lists)
$show_files = GETPOST('show_files','int');												// Show files area generated by bulk actions ?
$confirm	= GETPOST('confirm','alpha');												// Result of a confirmation

$toselect   = GETPOST('toselect', 'array');												// Array of ids of elements selected into a list
// Initialize context for list
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):'wiplist';	// To manage different context of search
$optioncss  = GETPOST('optioncss','aZ');												// Option for the css output (always '' except when 'print')

/*
//$billed = GETPOST('billed','int');
//$viewstatut= GETPOST('viewstatut');
*/

// Security check
$socid=0;
/*
// No check is done on company because readability is managed by public status of project and assignement.
if ($user->societe_id > 0)	// Protection if external user
{
	$socid = $user->societe_id;
	accessforbidden();
}
*/
//$result = restrictedArea($user, 'parts', $id, '');
$result = restrictedArea($user, 'projet', $id, 'projet&project');

$diroutputmassaction=$conf->projet->dir_output . '/temp/massgeneration/'.$user->id;

// Load variable for pagination
$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');

if (empty($page) || $page == -1) { $page = 0; }	 // If $page is not defined, or '' or -1
//if (! $sortfield) $sortfield='p.ref';
//if (! $sortorder) $sortorder='DESC';
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize search criterias
$search_all=trim((GETPOST('search_all', 'alphanohtml')!='')?GETPOST('search_all', 'alphanohtml'):GETPOST('sall', 'alphanohtml'));
$search_user_id			= GETPOST('search_user_id', 'int');
$search_taskref			= GETPOST('search_taskref', 'alpha');		// task ref
$search_tasklabel		= GETPOST('search_tasklabel', 'alpha');		// task title
$search_dtstartday		= GETPOST('search_dtstartday');
$search_dtstartmonth	= GETPOST('search_dtstartmonth');
$search_dtstartyear		= GETPOST('search_dtstartyear');
$search_dtendday		= GETPOST('search_dtendday');
$search_dtendmonth		= GETPOST('search_dtendmonth');
$search_dtendyear		= GETPOST('search_dtendyear');
$search_planedworkload	= GETPOST('search_planedworkload');
$search_timespend		= GETPOST('search_timespend');
$search_progresscalc	= GETPOST('search_progresscalc');
$search_progressdeclare	= GETPOST('search_progressdeclare');


//if (! $user->rights->projet->all->lire) $mine=1;	// Special for projects


// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$object = new Project($db);
$hookmanager->initHooks(array('projectlist'));
$object = new Project($db);
$taskstatic = new Task($db);
$extrafields_project = new ExtraFields($db);
$extrafields_task = new ExtraFields($db);

/*
// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	't.ref'=>"Ref",
	't.label'=>"Label",
	't.description'=>"Description",
	't.note_public'=>"NotePublic",
);
*/

// Definition of fields for list
$arrayfields=array();
$arrayfields['t.task_ref']=array('label'=>$langs->trans("RefTask"), 'checked'=>1);
$arrayfields['t.task_label']=array('label'=>$langs->trans("LabelTask"), 'checked'=>1);
$arrayfields['t.task_date_start']=array('label'=>$langs->trans("DateStart"), 'checked'=>1);
$arrayfields['t.task_date_end']=array('label'=>$langs->trans("DateEnd"), 'checked'=>1);
/*
foreach($object->fields as $key => $val)
{
	// If $val['visible']==0, then we never show the field
	if (! empty($val['visible'])) $arrayfields['t.'.$key]=array('label'=>$val['label'], 'checked'=>(($val['visible']<0)?0:1), 'enabled'=>$val['enabled'], 'position'=>$val['position']);
}
*/
/*
// Extra fields
if (is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label']) > 0)
{
	foreach($extrafields->attributes[$object->table_element]['label'] as $key => $val)
	{
		if (! empty($extrafields->attributes[$object->table_element]['list'][$key]))
			$arrayfields["ef.".$key]=array('label'=>$extrafields->attributes[$object->table_element]['label'][$key], 'checked'=>(($extrafields->attributes[$object->table_element]['list'][$key]<0)?0:1), 'position'=>$extrafields->attributes[$object->table_element]['pos'][$key], 'enabled'=>(abs($extrafields->attributes[$object->table_element]['list'][$key])!=3 && $extrafields->attributes[$object->table_element]['perms'][$key]));
	}
}

//$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');
*/

// Fetch optional attributes and labels
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once

if ($id > 0 || ! empty($ref))
{
	$extralabels_projet=$extrafields_project->fetch_name_optionals_label($object->table_element);
}
$extralabels_task=$extrafields_task->fetch_name_optionals_label($taskstatic->table_element);


/*
$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label('commande');	//Array ( [reqby] => Requested by [ticket] => Ticket )
//print_r($extralabels);
//print_r($extrafields);
$search_array_options=$extrafields->getOptionalsFromPost($extralabels,'','search_');
*/

$progress=GETPOST('progress', 'int');
$label=GETPOST('label', 'alpha');
$description=GETPOST('description');
$planned_workloadhour=(GETPOST('planned_workloadhour','int')?GETPOST('planned_workloadhour','int'):0);
$planned_workloadmin=(GETPOST('planned_workloadmin','int')?GETPOST('planned_workloadmin','int'):0);
$planned_workload=$planned_workloadhour*3600+$planned_workloadmin*60;


$parameters=array('id'=>$id);
//$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);	// Note that $action and $object may have been modified by some hooks
//if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

/*
 * Actions
 *
 * Put here all code to do according to value of "$action" parameter
 */

// Cancel
if ($cancel)
{
	if (! empty($backtopage))
	{
		header("Location: ".$backtopage);
		exit;
	}
	$action = '';
}

//if (empty($reshook))
//{

	// Purge search criteria
	// Buttons
	if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
	{
		$massaction='';
		$toselect='';
		$search_array_options=array();
		$search_user_id="";
		$search_taskref='';
		$search_tasklabel='';
		$search_dtstartday='';
		$search_dtstartmonth='';
		$search_dtstartyear='';
		$search_dtendday='';
		$search_dtendmonth='';
		$search_dtendyear='';
		$search_planedworkload='';
		$search_timespend='';
		$search_progresscalc='';
		$search_progressdeclare='';

//		$search_categ_cus=0;
//		$viewstatut='';
//		$billed='';
}

/*
if (GETPOST('cancel','alpha')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction','alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction=''; }
*/



$morewherefilterarray=array();

if (!empty($search_taskref)) {
	$morewherefilterarray[]= natural_search('t.ref', $search_taskref, 0, 1);
}

if (!empty($search_tasklabel)) {
	$morewherefilterarray[]= natural_search('t.label', $search_tasklabel, 0, 1);
}

if ($search_dtstartmonth > 0)
{
	if ($search_dtstartyear > 0 && empty($search_dtstartday)) {
		$morewherefilterarray[]= " (t.dateo BETWEEN '".$db->idate(dol_get_first_day($search_dtstartyear,$search_dtstartmonth,false))."' AND '".$db->idate(dol_get_last_day($search_dtstartyear,$search_dtstartmonth,false))."')";
	}else if ($search_dtstartyear > 0 && ! empty($search_dtstartday)) {
		$morewherefilterarray[]= " (t.dateo BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_dtstartmonth, $search_dtstartday, $search_dtstartyear))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_dtstartmonth, $search_dtstartday, $search_dtstartyear))."')";
	}else {
		$morewherefilterarray[]= " date_format(t.dateo, '%m') = '".$search_dtstartmonth."'";
	}
}
else if ($search_dtstartyear > 0)
{
	$morewherefilterarray[]= " (t.dateo BETWEEN '".$db->idate(dol_get_first_day($search_dtstartyear,1,false))."' AND '".$db->idate(dol_get_last_day($search_dtstartyear,12,false))."')";
}

if ($search_dtendmonth > 0)
{
	if ($search_dtendyear > 0 && empty($search_dtendday)) {
		$morewherefilterarray[]= " (t.datee BETWEEN '".$db->idate(dol_get_first_day($search_dtendyear,$search_dtendmonth,false))."' AND '".$db->idate(dol_get_last_day($search_dtendyear,$search_dtendmonth,false))."')";
	}else if ($search_dtendyear > 0 && ! empty($search_dtendday)) {
		$morewherefilterarray[]= " (t.datee BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_dtendmonth, $search_dtendday, $search_dtendyear))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_dtendmonth, $search_dtendday, $search_dtendyear))."')";
	}else {
		$morewherefilterarray[]= " date_format(t.datee, '%m') = '".$search_dtendmonth."'";
	}
}
else if ($search_dtendyear > 0)
{
	$morewherefilterarray[]= " (t.datee BETWEEN '".$db->idate(dol_get_first_day($search_dtendyear,1,false))."' AND '".$db->idate(dol_get_last_day($search_dtendyear,12,false))."')";
}

if (!empty($search_planedworkload)) {
	$morewherefilterarray[]= natural_search('t.planned_workload', $search_planedworkload, 1, 1);
}

if (!empty($search_timespend)) {
	$morewherefilterarray[]= natural_search('t.duration_effective', $search_timespend, 1, 1);
}

if (!empty($search_progresscalc)) {
	$filterprogresscalc='if '.natural_search('round(100 * $line->duration / $line->planned_workload,2)',$search_progresscalc,1,1). '{return 1;} else {return 0;}';
} else {
	$filterprogresscalc='';
}

if (!empty($search_progressdeclare)) {
	$morewherefilterarray[]= natural_search('t.progress', $search_progressdeclare, 1, 1);
}


$morewherefilter='';
if (count($morewherefilterarray)>0) {
	$morewherefilter= ' AND '. implode(' AND ', $morewherefilterarray);
}

// Mass actions
$objectclass='Task';
$objectlabel='Tasks';
$permtoread = $user->rights->projet->lire;
$permtodelete = $user->rights->projet->supprimer;
$uploaddir = $conf->projet->dir_output.'/tasks';
include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';



/*
 * View
 *
 * Put here all code to render page
 */

$now = dol_now();
$form		= new Form($db);
$formother	= new FormOther($db);
$taskstatic	= new Task($db);
$userstatic	= new User($db);

// Output page
// --------------------------------------------------------------------

$title=$langs->trans("Project").' - '.$langs->trans("Tasks").' - '.$object->ref.' '.$object->name;
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/projectnameonly/',$conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->ref.' '.$object->name.' - '.$langs->trans("Tasks");
$help_url='';
llxHeader('', $title, $help_url);

if ($id > 0 || ! empty($ref))
{
	$object->fetch($id, $ref);
	$object->fetch_thirdparty();
	$res=$object->fetch_optionals();

	$tab=GETPOST('tab')?GETPOST('tab'):'tasks';

	$head=project_prepare_head($object);
	dol_fiche_head($head, $tab, $langs->trans("Project"), -1, ($object->public?'projectpub':'project'));

	// To verify role of users
	//$userAccess = $object->restrictedProjectArea($user,'read');
	$userAccess = $object->restrictedProjectArea($user);
	//$userAccess=0;
	$userWrite  = $object->restrictedProjectArea($user,'write');
	//$userDelete = $object->restrictedProjectArea($user,'delete');
	//print "userAccess=".$userAccess." userWrite=".$userWrite." userDelete=".$userDelete;

	/* =================
	 *
	 * Project card
	 * 
	 * =================
	 */

	$linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

	$morehtmlref='<div class="refidno">';
	// Title
	$morehtmlref.=$object->title;
	// Thirdparty
	if ($object->thirdparty->id > 0)
	{
		$morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $object->thirdparty->getNomUrl(1, 'project');
	}
	$morehtmlref.='</div>';

	// Define a complementary filter for search of next/prev ref.
	if (! $user->rights->projet->all->lire)
	{
		$objectsListId = $object->getProjectsAuthorizedForUser($user,0,0);
	//	$object->next_prev_filter=" te.rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
		$object->next_prev_filter=" rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
	}

	dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

	print '<div class="fichecenter">';
	print '<div class="fichehalfleft">';
	print '<div class="underbanner clearboth"></div>';

	print '<table class="border" width="100%">';

	// Visibility
	print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
	if ($object->public) print $langs->trans('SharedProject');
	else print $langs->trans('PrivateProject');
	print '</td></tr>';

	/*
	if (! empty($conf->global->PROJECT_USE_OPPORTUNITIES))
	{
		// Opportunity status
		print '<tr><td>'.$langs->trans("OpportunityStatus").'</td><td>';
		$code = dol_getIdFromCode($db, $object->opp_status, 'c_lead_status', 'rowid', 'code');
		if ($code) print $langs->trans("OppStatus".$code);
		print '</td></tr>';

		// Opportunity percent
		print '<tr><td>'.$langs->trans("OpportunityProbability").'</td><td>';
		if (strcmp($object->opp_percent,'')) print price($object->opp_percent,'',$langs,1,0).' %';
		print '</td></tr>';

		// Opportunity Amount
		print '<tr><td>'.$langs->trans("OpportunityAmount").'</td><td>';
		if (strcmp($object->opp_amount,'')) print price($object->opp_amount,'',$langs,1,0,0,$conf->currency);
		print '</td></tr>';
	}
	*/

	// Date start - end
	print '<tr><td>'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
	$start = dol_print_date($object->date_start,'day');
	print ($start?$start:'?');
	$end = dol_print_date($object->date_end,'day');
	print ' - ';
	print ($end?$end:'?');
	if ($object->hasDelay()) print img_warning("Late");
	print '</td></tr>';

	// Budget
	print '<tr><td>'.$langs->trans("Budget").'</td><td>';
	if (strcmp($object->budget_amount, '')) print price($object->budget_amount,'',$langs,1,0,0,$conf->currency);
	print '</td></tr>';

	/*
	// Other attributes
	$cols = 2;
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';
	*/

	print '</table>';

	print '</div>';
	print '<div class="fichehalfright">';
	print '<div class="ficheaddleft">';
	print '<div class="underbanner clearboth"></div>';

	print '<table class="border" width="100%">';

	// Description
	print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
	print nl2br($object->description);
	print '</td></tr>';

	// Bill time
	if (! empty($conf->global->PROJECT_BILL_TIME_SPENT))
	{
		print '<tr><td>'.$langs->trans("BillTime").'</td><td>';
		print yn($object->bill_time);
		print '</td></tr>';
	}

	// Categories
	if($conf->categorie->enabled) {
		print '<tr><td valign="middle">'.$langs->trans("Categories").'</td><td>';
		print $form->showCategories($object->id,'project',1);
		print "</td></tr>";
	}

	print '</table>';

	print '</div>';
	print '</div>';
	print '</div>';

	print '<div class="clearboth"></div>';


	dol_fiche_end();

	/* =================
	 *
	 * Lines
	 * 
	 * =================
	 */
	
	// Get list of tasks in tasksarray 

	//$tasksarray=$taskstatic->_getTasksArray(0, 0, $object->id, $filteronthirdpartyid, 0,'',-1,$morewherefilter);
	$tasksarray=_getTasksArray($object->id,$morewherefilter);

	//var_dump($tasksarray);
	// Count total nb of records
	$nbtotalofrecords = count($tasksarray);	
	
	//llxHeader('', $title, $help_url);
	
	print '<!-- List of tasks for project -->';
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

	$param='';
	if ($search_user_id > 0) $param.='&search_user_id='.dol_escape_htmltag($search_user_id);
	
	/* TO DO
	if ($show_files)			$param.='&show_files=' .urlencode($show_files);
	if ($viewstatut != '')	  $param.='&viewstatut='.urlencode($viewstatut);
	if ($billed != '')			$param.='&billed='.urlencode($billed);
	*/
	if ($optioncss != '')	 $param.='&optioncss='.urlencode($optioncss);

	// List of mass actions available
	$arrayofmassactions =  array(
	);

	if ($user->rights->commande->supprimer) $arrayofmassactions['predelete']=$langs->trans("Delete");
	if (in_array($massaction, array('presend','predelete','createbills'))) $arrayofmassactions=array();
	if ($user->rights->commande->supprimer) $arrayofmassactions['predelete']=$langs->trans("Delete");
	if (in_array($massaction, array('presend','predelete','createbills'))) $arrayofmassactions=array();
	$massactionbutton=$form->selectMassAction('', $arrayofmassactions);

	if (! empty($conf->use_javascript_ajax))
	{
		include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
	}

	// New Task Button
	$newcardbutton='';
	if ($user->rights->projet->creer)
	{
		$newcardbutton = '<a class="butActionNew" href="'.DOL_URL_ROOT.'/projet/tasks.php?action=create"><span class="valignmiddle">'.$langs->trans('NewTask').'</span>';
		$newcardbutton.= '<span class="fa fa-plus-circle valignmiddle"></span>';
		$newcardbutton.= '</a>';
	}

	/*
	 * List of tasks in view mode
	 */

	print '<br>';

	// Lines of title fields
	print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
	if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="list">';
	print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
	print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	//print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
	//print '<input type="hidden" name="page" value="'.$page.'">';
	//print '<input type="hidden" name="type" value="'.$type.'">';
	print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';
	print '<input type="hidden" name="withproject" value="'.$withproject.'">';

	$title=$langs->trans("ListOfTasks");
	$linktotasks='<a href="'.DOL_URL_ROOT.'/projet/ganttview.php?id='.$object->id.'&withproject=1">'.$langs->trans("GoToGanttView").'<span class="paddingleft fa fa-calendar-minus-o valignmiddle"></span></a>';

	//print_barre_liste($title, 0, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, $linktotasks, $num, $totalnboflines, 'title_generic.png', 0, '', '', 0, 1);
	print_barre_liste($title, 0, $_SERVER["PHP_SELF"], '', '', '', '', 0, $nbtotalofrecords, 'title_generic.png', 0, $linktotasks.' &nbsp; '.$newcardbutton, '', 0, 1);
	//print load_fiche_titre($title, $linktotasks.' &nbsp; '.$newcardbutton, 'title_generic.png');

	// Show description of content
	$contentdesc = $langs->trans('Billing status of workorders for Project').' - '.$object->ref;
	print '<div class="opacitymedium">';
	print $contentdesc.'<br><br>';
	print '</div>';

	// Add code for pre mass action (confirmation or email presend form)
	$topicmail="Information";
	$modelmail="task";
	$objecttmp=new Task($db);
	$trackid='tas'.$object->id;
	include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

	// Filter on categories

	$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
	$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
	if ($massactionbutton) $selectedfields.=$form->showCheckAddButtons('checkforselect', 1);

	print '<div class="div-table-responsive">';		// You can use div-table-responsive-no-min if you dont need reserved height for your table
	print '<table id="tablelines" class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">';

	// Fields title search
	// --------------------------------------------------------------------
	print '<thead>';
	print '<tr class="liste_titre_filter">';
	// Task ref
	print '<td class="liste_titre">';
	print '<input class="flat searchstring maxwidth50" type="text" name="search_taskref" value="'.dol_escape_htmltag($search_taskref).'">';
	print '</td>';
	// Task title
	print '<td class="liste_titre">';
	print '<input class="flat searchstring maxwidth100" type="text" name="search_tasklabel" value="'.dol_escape_htmltag($search_tasklabel).'">';
	print '</td>';
	// Start date
	print '<td class="liste_titre center">';
	print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_dtstartday" value="'.$search_dtstartday.'">';
	print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_dtstartmonth" value="'.$search_dtstartmonth.'"><br/>';
	$formother->select_year($search_dtstartyear?$search_dtstartyear:-1,'search_dtstartyear',1, 20, 5);
	print '</td>';
	// End date
	print '<td class="liste_titre center">';
	print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_dtendday" value="'.$search_dtendday.'">';
	print '<input class="flat valignmiddle" type="text" size="1" maxlength="2" name="search_dtendmonth" value="'.$search_dtendmonth.'"><br/>';
	$formother->select_year($search_dtendyear?$search_dtendyear:-1,'search_dtendyear',1, 20, 5);
	print '</td>';

	// Declared progress
	print '<td class="liste_titre" align="right">';
//	print '<input class="flat" type="text" size="4" name="search_progressdeclare" value="'.$search_progressdeclare.'">';
	print '</td>';
	
	// Calculated progress
	print '<td class="liste_titre" align="right">';
//	print '<input class="flat" type="text" size="4" name="search_progresscalc" value="'.$search_progresscalc.'">';
	print '</td>';

	// Planned workload
	print '<td class="liste_titre" align="right">';
//	print '<input class="flat" type="text" size="4" name="search_planedworkload" value="'.$search_planedworkload.'">';
	print '</td>';

	// Time spent
	print '<td class="liste_titre" align="right">';
	//print '<input class="flat" type="text" size="4" name="search_timespend" value="'.$search_timespend.'">';
	print '</td>';

	// Billable
	print '<td class="liste_titre" align="right"></td>';
	// WIP
	print '<td class="liste_titre" align="right"></td>';
	// Processed
	print '<td class="liste_titre" align="right"></td>';
	// Billed
	print '<td class="liste_titre" align="right"></td>';
	// Invoice
	print '<td class="liste_titre" align="right"></td>';

	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';
	// Fields from hook
	$parameters=array('arrayfields'=>$arrayfields);
	$reshook=$hookmanager->executeHooks('printFieldListOption',$parameters);	// Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	// Action column
	print '<td class="liste_titre" align="right">';
	$searchpicto=$form->showFilterButtons();
	print $searchpicto;
	print '</td>';
	print '</tr>';

	// --------------------------------------------------------------------
	// ----------------------------  NEXT ROW  ----------------------------
	// --------------------------------------------------------------------

	print '<tr class="liste_titre">';
	// Task ref
	print_liste_field_titre("RefTask", $_SERVER["PHP_SELF"],"",'','','',$sortfield,$sortorder,'');
	// Task title
	print_liste_field_titre("LabelTask", $_SERVER["PHP_SELF"],"",'','','',$sortfield,$sortorder,'');
	// Start date
	print_liste_field_titre("DateStart", $_SERVER["PHP_SELF"],"",'','','align="center"',$sortfield,$sortorder,'');
	// End date
	print_liste_field_titre("DateEnd", $_SERVER["PHP_SELF"],"",'','','align="center"',$sortfield,$sortorder,'');

	// Declared progress
	print_liste_field_titre("Declared %", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');
	// Calculated progress
	print_liste_field_titre("ProgressCalculated", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');
	// Planned workload
	print_liste_field_titre("PlannedWorkload", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');
	// Time spent
	print_liste_field_titre("TimeSpent", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');

	// Billable
	print_liste_field_titre("Billable", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');
	// WIP
	print_liste_field_titre("W.I.P.", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');	
	// Reported
	print_liste_field_titre("Processed", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');	
	// Billed
	print_liste_field_titre("Billed", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');
	// Invoice
	print_liste_field_titre("Invoice", $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'');

	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
	// Hook fields
	$parameters=array('arrayfields'=>$arrayfields,'param'=>$param,'sortfield'=>$sortfield,'sortorder'=>$sortorder);
	$reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);	// Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	// Action column
	print_liste_field_titre('', $_SERVER["PHP_SELF"],"",'','','align="center" width="80"',$sortfield,$sortorder,'maxwidthsearch ');
	print '</tr>';

	print '</thead>';
	
	// Loop on record
	// --------------------------------------------------------------------
	
	print '<tbody>';


	$plannedworkloadoutputformat='allhourmin';
	$timespentoutputformat='allhourmin';

	if (count($tasksarray) > 0)
	{
		// Show all lines in taskarray (recursive function to go down on tree)
		$j=0; $level=0;
		$nboftaskshown=_projectLinesa($j, 0, $tasksarray, $level, true, 0, $tasksrole, $object->id, 1, $object->id, $filterprogresscalc);
	}
	else
	{
		print '<tr class="oddeven"><td colspan="10" class="opacitymedium">'.$langs->trans("NoTasks").'</td></tr>';
	}

	$i++;

	// Show total line
	if (isset($totalarray['pos']))
	{
		print '<tr class="liste_total">';
		$i=0;
		while ($i < $totalarray['nbfield'])
		{
			$i++;
			if (! empty($totalarray['pos'][$i]))  print '<td align="right">'.price($totalarray['val'][$totalarray['pos'][$i]]).'</td>';
			else
			{
				if ($i == 1)
				{
					if ($num < $limit) print '<td align="left">'.$langs->trans("Total").'</td>';
					else print '<td align="left">'.$langs->trans("Totalforthispage").'</td>';
				}
				else print '<td></td>';
			}
		}
		print '</tr>';
	}

	print '</tbody>';

	print '</table>';
	print '</div>';

	print '</form>';

}

/*
if (in_array('builddoc',$arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords))
{
	$hidegeneratedfilelistifempty=1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) $hidegeneratedfilelistifempty=0;

	$formfile = new FormFile($db);

	// Show list of available documents
	$urlsource=$_SERVER['PHP_SELF'].'?sortfield='.$sortfield.'&sortorder='.$sortorder;
	$urlsource.=str_replace('&amp;','&',$param);

	//$diroutputmassaction=$conf->parts->dir_output . '/temp/massgeneration/'.$user->id;
	$diroutputmassaction='';
	$filedir=$diroutputmassaction;
	$genallowed=$user->rights->commande->lire;
	$delallowed=$user->rights->commande->creer;

	print $formfile->showdocuments('massfilesarea_orders','',$filedir,$urlsource,0,$delallowed,'',1,1,0,48,1,$param,$title,'','','',null,$hidegeneratedfilelistifempty);
}

if ($projmode == 'listall')
{

print '<div class="fichecenter"><div class="fichehalfleft">';
print '<a href="#builddoc" name="builddoc"></a>'; // anchor
*/

/*
* Generated documents
*/

/*
//$modulesubdir='reports';
$modulesubdir='';

$filedir=DOL_DOCUMENT_ROOT.'/documents/projet/';
$urlsource=$_SERVER["PHP_SELF"].'#builddoc';
$genallowed=1;
$delallowed=1;
$modelpdf=partslist;

$var=true;

print $formfile->showdocuments('project',$modulesubdir,$filedir,$urlsource,$genallowed,$delallowed,$modelpdf);

print '</div><div class="fichehalfright"><div class="ficheaddleft">';

print '</div></div></div>';

}

/*
// Enhance with select2
if ($conf->use_javascript_ajax)
{
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	$comboenhancement = ajax_combobox('.elementselect');
	$out.=$comboenhancement;

	print $comboenhancement;
}

*/
// End of page
llxFooter();


/**
 * Return list of tasks for all projects or for one particular project
 * Sort order is on project, then on position of task, and last on start date of first level task
 *
 * @param	int		$projectid			Project id
 * @param	string	$filteronproj		Filter on project ref or label
 * @param	string	$morewherefilter	Add more filter into where SQL request (must start with ' AND ...')

 * @return 	array						Array of tasks
 */
function _getTasksArray($projectid=0, $morewherefilter='')
{
	global $db, $conf;

	$tasks = array();

	/* =================
	 *
	 * Start of Main SQL Query
	 * 
	 * =================
	 */

	// Build and execute select - list of tasks
	$sql = 'SELECT ';
	$sql.= '  t.rowid as taskid, t.ref as taskref, t.label, t.description, t.fk_task_parent, t.duration_effective, t.progress, t.fk_statut as status';
	$sql.= ', t.dateo as date_start, t.datee as date_end, t.planned_workload, t.rang';
	$sql.= ', tef.invoiceable as billable, tef.invcat, tef.invoice as invoice';
	$sql.= ', p.rowid as projectid, p.ref, p.title as plabel, p.public, p.fk_statut as projectstatus';
	$sql.= ', s.rowid as thirdparty_id, s.nom as thirdparty_name, s.email as thirdparty_email';
	$sql.= ', woic.rowid, woic.label as invcat_label';
	$sql.= ' FROM '.MAIN_DB_PREFIX.'projet_task as t';
	$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet_task_extrafields AS tef ON tef.fk_object = t.rowid';
	$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet as p ON p.rowid = t.fk_projet';
	$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as s ON s.rowid = p.fk_soc';
	$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'c_fmc_wo_invcategory as woic ON woic.rowid = tef.invcat';
	$sql.= ' WHERE 1 = 1';
	$sql.= ' AND t.fk_projet = '.$projectid.' ';
	if ($morewherefilter) $sql.=$morewherefilter;
	$sql.= ' ORDER BY t.rang, t.dateo';

	//print $sql;exit;
	dol_syslog("::_getTasksArray", LOG_DEBUG);
	$resql = $db->query($sql);
	if ($resql)
	{
		$num = $db->num_rows($resql);
		$i = 0;
		// Loop on each record found, so each couple (project id, task id)
		while ($i < $num)
		{
			$error=0;

			$obj = $db->fetch_object($resql);

			if (! $error)
			{
				$tasks[$i] = new Task($db);
				$tasks[$i]->id				= $obj->taskid;
				$tasks[$i]->ref				= $obj->taskref;
				$tasks[$i]->fk_project		= $obj->projectid;
				$tasks[$i]->projectref		= $obj->ref;
				$tasks[$i]->projectlabel	= $obj->plabel;
				$tasks[$i]->projectstatus	= $obj->projectstatus;
				$tasks[$i]->label			= $obj->label;
				$tasks[$i]->description		= $obj->description;
				$tasks[$i]->fk_parent		= $obj->fk_task_parent;	  // deprecated
				$tasks[$i]->fk_task_parent	= $obj->fk_task_parent;
				$tasks[$i]->duration		= $obj->duration_effective;
				$tasks[$i]->planned_workload= $obj->planned_workload;
				$tasks[$i]->progress		= $obj->progress;
				$tasks[$i]->fk_statut		= $obj->status;
				$tasks[$i]->public			= $obj->public;
				$tasks[$i]->date_start		= $db->jdate($obj->date_start);
				$tasks[$i]->date_end		= $db->jdate($obj->date_end);
				$tasks[$i]->rang	   		= $obj->rang;

				$tasks[$i]->socid			= $obj->thirdparty_id;	// For backward compatibility
				$tasks[$i]->thirdparty_id	= $obj->thirdparty_id;
				$tasks[$i]->thirdparty_name	= $obj->thirdparty_name;
				$tasks[$i]->thirdparty_email= $obj->thirdparty_email;

//				$tasks[$i]->invcat			= $obj->invcat;
				$tasks[$i]->invcat_label	= $obj->invcat_label;
				$tasks[$i]->invoice			= $obj->invoice;
				$tasks[$i]->billable		= (($obj->invcat_label == 'Billable')?1:0); // 1 if billable, 0 if not

				$tasks[$i]->processed	= 0;
				$tasks[$i]->billed		= 0;
				$tasks[$i]->wip			= 0;
				if ($tasks[$i]->billable == 1)
				{
					$tasks[$i]->processed	= 0;	// PJR TODO - create link to Customer Order
					$tasks[$i]->billed		= (empty($tasks[$i]->invoice)? 0 : $tasks[$i]->duration);	// PJR TODO - change from duration to actual hours invoiced
					$tasks[$i]->wip			= $tasks[$i]->duration - max($tasks[$i]->processed,$tasks[$i]->billed);
				}

			}

			$i++;
		}
		$db->free($resql);
	}
	else
	{
		dol_print_error($db);
	}

	return $tasks;
}


/**
 * Show task lines with a particular parent
 *
 * @param	string	   	$inc				Line number (start to 0, then increased by recursive call)
 * @param   string		$parent				Id of parent project to show (0 to show all)
 * @param   Task[]		$lines				Array of lines
 * @param   int			$level				Level (start to 0, then increased/decrease by recursive call), or -1 to show all level in order of $lines without the recursive groupment feature.
 * @param 	string		$var				Color
 * @param 	int			$showproject		Show project columns
 * @param	int			$taskrole			Array of roles of user for each tasks
 * @param	int			$projectsListId		List of id of project allowed to user (string separated with comma)
 * @param	int			$addordertick		Add a tick to move task
 * @param   int		 $projectidfortotallink	 0 or Id of project to use on total line (link to see all time consumed for project)
 * @param   string	  $filterprogresscalc	 filter text
 * @return	void
 */
function _projectLinesa(&$inc, $parent, &$lines, &$level, $var, $showproject, &$taskrole, $projectsListId='', $addordertick=0, $projectidfortotallink=0, $filterprogresscalc='')
{
	global $user, $bc, $langs, $conf, $db;
	global $projectstatic, $taskstatic;

	$lastprojectid=0;

	$projectsArrayId=explode(',',$projectsListId);
	if ($filterprogresscalc!=='') {
		foreach ($lines as $key=>$line) {
			if (!empty($line->planned_workload) && !empty($line->duration)) {
				$filterprogresscalc = str_replace(' = ', ' == ', $filterprogresscalc);
				if (!eval($filterprogresscalc)) {
					unset($lines[$key]);
				}
			}
		}
		$lines=array_values($lines);
	}

	$numlines=count($lines);

	// We declare counter as global because we want to edit them into recursive call
	global $total_projectlinesa_spent,$total_projectlinesa_planned,$total_projectlinesa_spent_if_planned, $total_projectlinesa_spent_if_billable, $total_projectlinesa_wip, $total_projectlinesa_processed, $total_projectlinesa_billed;
	if ($level == 0)
	{
		$total_projectlinesa_spent=0;
		$total_projectlinesa_planned=0;
		$total_projectlinesa_spent_if_planned=0;
		$total_projectlinesa_spent_if_billable=0;
		$total_projectlinesa_wip=0;
		$total_projectlinesa_processed=0;
		$total_projectlinesa_billed=0;
	}

	for ($i = 0 ; $i < $numlines ; $i++)
	{
		if ($parent == 0 && $level >= 0) $level = 0;			  // if $level = -1, we dont' use sublevel recursion, we show all lines

		// Process line
		// print "i:".$i."-".$lines[$i]->fk_project.'<br>';

		if ($lines[$i]->fk_parent == $parent || $level < 0)	   // if $level = -1, we dont' use sublevel recursion, we show all lines
		{
			// Show task line.
			$showline=1;
			$showlineingray=0;

			// If there is filters to use
			if (is_array($taskrole))
			{
				// If task not legitimate to show, search if a legitimate task exists later in tree
				if (! isset($taskrole[$lines[$i]->id]) && $lines[$i]->id != $lines[$i]->fk_parent)
				{
					// So search if task has a subtask legitimate to show
					$foundtaskforuserdeeper=0;
					searchTaskInChild($foundtaskforuserdeeper,$lines[$i]->id,$lines,$taskrole);
					//print '$foundtaskforuserpeeper='.$foundtaskforuserdeeper.'<br>';
					if ($foundtaskforuserdeeper > 0)
					{
						$showlineingray=1;		// We will show line but in gray
					}
					else
					{
						$showline=0;			// No reason to show line
					}
				}
			}
			else
			{
				// Caller did not ask to filter on tasks of a specific user (this probably means he want also tasks of all users, into public project
				// or into all other projects if user has permission to).
				if (empty($user->rights->projet->all->lire))
				{
					// User is not allowed on this project and project is not public, so we hide line
					if (! in_array($lines[$i]->fk_project, $projectsArrayId))
					{
						// Note that having a user assigned to a task into a project user has no permission on, should not be possible
						// because assignement on task can be done only on contact of project.
						// If assignement was done and after, was removed from contact of project, then we can hide the line.
						$showline=0;
					}
				}
			}

			if ($showline)
			{
				// Break on a new project
				if ($parent == 0 && $lines[$i]->fk_project != $lastprojectid)
				{
					$var = !$var;
					$lastprojectid=$lines[$i]->fk_project;
				}

				print '<tr '.$bc[$var].' id="row-'.$lines[$i]->id.'">'."\n";

				if ($showproject)
				{
					// Project ref
					print "<td>";
					//if ($showlineingray) print '<i>';
					$projectstatic->id=$lines[$i]->fk_project;
					$projectstatic->ref=$lines[$i]->projectref;
					$projectstatic->public=$lines[$i]->public;
					$projectstatic->title=$lines[$i]->projectlabel;
					if ($lines[$i]->public || in_array($lines[$i]->fk_project,$projectsArrayId) || ! empty($user->rights->projet->all->lire)) print $projectstatic->getNomUrl(1);
					else print $projectstatic->getNomUrl(1,'nolink');
					//if ($showlineingray) print '</i>';
					print "</td>";

					// Project status
					print '<td>';
					$projectstatic->statut=$lines[$i]->projectstatus;
					print $projectstatic->getLibStatut(2);
					print "</td>";
				}

				// Ref of task
				print '<td class = "nowrap">';
				if ($showlineingray)
				{
					print '<i>'.img_object('','projecttask').' '.$lines[$i]->ref.'</i>';
				}
				else
				{
					$taskstatic->id=$lines[$i]->id;
					$taskstatic->ref=$lines[$i]->ref;
					$taskstatic->label=($taskrole[$lines[$i]->id]?$langs->trans("YourRole").': '.$taskrole[$lines[$i]->id]:'');
					print $taskstatic->getNomUrl(1,'withproject');
				}
				print '</td>';

				// Title of task
				print "<td>";
				if ($showlineingray) print '<i>';
				//else print '<a href="'.DOL_URL_ROOT.'/projet/tasks/task.php?id='.$lines[$i]->id.'&withproject=1">';
				for ($k = 0 ; $k < $level ; $k++)
				{
					print "&nbsp; &nbsp; &nbsp;";
				}
				print $lines[$i]->label;
				if ($showlineingray) print '</i>';
				//else print '</a>';
				print "</td>\n";

				// Date start
				print '<td align="center">';
				print dol_print_date($lines[$i]->date_start,'day');
				print '</td>';

				// Date end
				print '<td align="center">';
				$taskstatic->projectstatus = $lines[$i]->projectstatus;
				$taskstatic->progress = $lines[$i]->progress;
				$taskstatic->fk_statut = $lines[$i]->status;
				$taskstatic->datee = $lines[$i]->date_end;
				print dol_print_date($lines[$i]->date_end,'day');
				if ($taskstatic->hasDelay()) print img_warning($langs->trans("Late"));
				print '</td>';

				$plannedworkloadoutputformat='allhourmin';
				$timespentoutputformat='allhourmin';
				if (! empty($conf->global->PROJECT_PLANNED_WORKLOAD_FORMAT)) $plannedworkloadoutputformat=$conf->global->PROJECT_PLANNED_WORKLOAD_FORMAT;
				if (! empty($conf->global->PROJECT_TIMES_SPENT_FORMAT)) $timespentoutputformat=$conf->global->PROJECT_TIME_SPENT_FORMAT;

				// Progress declared
				print '<td align="right">';
				if ($lines[$i]->progress != '')
				{
					print $lines[$i]->progress.' %';
				}
				print '</td>';

				// Progress calculated (Note: ->duration is time spent)
				print '<td align="right">';
				if ($lines[$i]->planned_workload || $lines[$i]->duration)
				{
					if ($lines[$i]->planned_workload) print round(100 * $lines[$i]->duration / $lines[$i]->planned_workload,2).' %';
					else print '<span class="opacitymedium">'.$langs->trans('WorkloadNotDefined').'</span>';
				}
				print '</td>';

				// Planned Workload (in working hours)
				print '<td align="right">';
				$fullhour=convertSecondToTime($lines[$i]->planned_workload,$plannedworkloadoutputformat);
				$workingdelay=convertSecondToTime($lines[$i]->planned_workload,'all',86400,7);	// TODO Replace 86400 and 7 to take account working hours per day and working day per weeks
				if ($lines[$i]->planned_workload != '')
				{
					print $fullhour;
					// TODO Add delay taking account of working hours per day and working day per week
					//if ($workingdelay != $fullhour) print '<br>('.$workingdelay.')';
				}
				//else print '--:--';
				print '</td>';

				// Time spent
				print '<td align="right">';
				if ($showlineingray) print '<i>';
				else print '<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?id='.$lines[$i]->id.($showproject?'':'&withproject=1').'">';
				if ($lines[$i]->duration) print convertSecondToTime($lines[$i]->duration,$timespentoutputformat);
				else print '--:--';
				if ($showlineingray) print '</i>';
				else print '</a>';
				print '</td>';

				// Billable
				print '<td class="left">';
				print $lines[$i]->invcat_label;
				print '</td>';

				// WIP (billable work in progress)
				print '<td class="right">';
				print (($lines[$i]->wip > 0)?convertSecondToTime($lines[$i]->wip,$timespentoutputformat):'--:--');
				print '</td>';

				// Processed
				print '<td class="right">';
				print (($lines[$i]->processed == 0)?'--:--':convertSecondToTime($lines[$i]->processed,$timespentoutputformat));
				print '</td>';

				// Billed
				print '<td class="right">';
				print (($lines[$i]->billed == 0)?'--:--':convertSecondToTime($lines[$i]->billed,$timespentoutputformat));
				print '</td>';

				// Invoice
				print '<td class="nowrap left">';
				if (! empty($lines[$i]->invoice))
				{
					$facturestatic=new Facture($db);
					$facturestatic->fetch($lines[$i]->invoice);
					print $facturestatic->getNomUrl(1,'',200,0,'',0,1);
				}
				else
				{
					print '&nbsp;';
				}
				print '</td>';

				// Contacts of task
				if (! empty($conf->global->PROJECT_SHOW_CONTACTS_IN_LIST))
				{
					print '<td>';
					foreach(array('internal','external') as $source)
					{
						$tab = $lines[$i]->liste_contact(-1,$source);
						$num=count($tab);
						if (!empty($num)){
							foreach ($tab as $contacttask){
								//var_dump($contacttask);
								if ($source == 'internal') $c = new User($db);
								else $c = new Contact($db);
								$c->fetch($contacttask['id']);
								print $c->getNomUrl(1) . ' (' . $contacttask['libelle'] . ')' . '<br>';
							}
						}
					}
					print '</td>';
				}

				// Tick to drag and drop
				if ($addordertick)
				{
					print '<td align="center" class="tdlineupdown hideonsmartphone">&nbsp;</td>';
				}

				print "</tr>\n";

				if (! $showlineingray) $inc++;

				if ($level >= 0)	// Call sublevels
				{
					$level++;
					if ($lines[$i]->id) _projectLinesa($inc, $lines[$i]->id, $lines, $level, $var, $showproject, $taskrole, $projectsListId, $addordertick);
					$level--;
				}

				$total_projectlinesa_spent += $lines[$i]->duration;
				$total_projectlinesa_planned += $lines[$i]->planned_workload;
				if ($lines[$i]->planned_workload) $total_projectlinesa_spent_if_planned += $lines[$i]->duration;
				if ($lines[$i]->billable == 1) $total_projectlinesa_spent_if_billable += $lines[$i]->duration;

				$total_projectlinesa_wip += $lines[$i]->wip;
				$total_projectlinesa_processed += $lines[$i]->processed;
				$total_projectlinesa_billed += $lines[$i]->billed;

			}
		}
		else
		{
			//$level--;
		}
	}

	if (($total_projectlinesa_planned > 0 || $total_projectlinesa_spent > 0) && $level <= 0)
	{
		print '<tr class="liste_total nodrag nodrop">';
		print '<td class="liste_total">'.$langs->trans("Total").'</td>';
		if ($showproject) print '<td></td><td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td></td>';
		print '<td align="right" class="nowrap liste_total">';
		if ($total_projectlinesa_planned) print round(100 * $total_projectlinesa_spent / $total_projectlinesa_planned,2).' %';
		print '</td>';
		print '<td align="right" class="nowrap liste_total">';
		print convertSecondToTime($total_projectlinesa_planned, 'allhourmin');
		print '</td>';
		print '<td align="right" class="nowrap liste_total">';
		if ($projectidfortotallink > 0) print '<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?projectid='.$projectidfortotallink.($showproject?'':'&withproject=1').'">';
		print convertSecondToTime($total_projectlinesa_spent, 'allhourmin');
		if ($projectidfortotallink > 0) print '</a>';
		print '</td>';
		print '<td align="right" class="nowrap liste_total">';
		print convertSecondToTime($total_projectlinesa_spent_if_billable, 'allhourmin');
		print '</td>';

		print '<td align="right" class="nowrap liste_total">';
		print convertSecondToTime($total_projectlinesa_wip, 'allhourmin');
		print '</td>';

		print '<td align="right" class="nowrap liste_total">';
		print convertSecondToTime($total_projectlinesa_processed, 'allhourmin');
		print '</td>';
		
		print '<td align="right" class="nowrap liste_total">';
		print convertSecondToTime($total_projectlinesa_billed, 'allhourmin');
		print '</td>';
		
		print '<td></td>';	// Invoice
		// Contacts of task
		if (! empty($conf->global->PROJECT_SHOW_CONTACTS_IN_LIST))
		{
			print '<td></td>';
		}
		if ($addordertick) print '<td class="hideonsmartphone"></td>';
		print '</tr>';
	}

	return $inc;
}

$db->close();
