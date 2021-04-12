<?php
/* Copyright (C) 2007-2017	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2018		Peter Roberts		<webmaster@finchmc.com.au>
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
 *   	\file	   htdocs/custom/wip/report_list.php
 *		\ingroup	wip
 *		\brief	  List all reports of a project
 */

//if (! defined('NOREQUIREDB'))			  define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))			define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))			 define('NOREQUIRESOC','1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))			define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))	define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))			  define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))		   define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))			 define('NOSTYLECHECK','1');				// Do not check style html tag into posted data
//if (! defined('NOIPCHECK'))				define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined('NOREQUIREMENU'))			define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))			define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))			define('NOREQUIREAJAX','1');	   	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))				  define("NOLOGIN",'1');						// If this page is public (can be called outside logged session)
//if (! defined("MAIN_LANG_DEFAULT"))		define('MAIN_LANG_DEFAULT','auto');					// Force lang to a particular value
//if (! defined("MAIN_AUTHENTICATION_MODE")) define('MAIN_AUTHENTICATION_MODE','aloginmodule');		// Force authentication handler
//if (! defined("NOREDIRECTBYMAINTOLOGIN"))  define('NOREDIRECTBYMAINTOLOGIN',1);		// The main.inc.php does not make a redirect if not logged, instead show simple error message

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
//require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
dol_include_once('/wip/class/report.class.php');

// Load translation files required by the page
$langs->loadLangs(array('wip@wip', 'projects', 'other'));

// Get parameters
$id			= GETPOST('id','int');
$ref		= GETPOST('ref', 'alpha');
//$reportref	= GETPOST('reportref', 'alpha');
$action		= GETPOST('action','aZ09')?GETPOST('action','aZ09'):'view';				// The action 'add', 'create', 'edit', 'update', 'view', ...
$massaction	= GETPOST('massaction','alpha');										// The bulk action (combo box choice into lists)
$show_files	= GETPOST('show_files','int');											// Show files area generated by bulk actions ?
$confirm	= GETPOST('confirm','alpha');											// Result of a confirmation
$cancel		= GETPOST('cancel', 'alpha');											// We click on a Cancel button
$toselect	= GETPOST('toselect', 'array');											// Array of ids of elements selected into a list

// Initialize context for list
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):'reportlist'; // To manage different context of search
$backtopage = GETPOST('backtopage','alpha');										// Go back to a dedicated page
$optioncss  = GETPOST('optioncss','aZ');											// Option for the css output (always '' except when 'print')

// Load variable for pagination
$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');
if (empty($page) || $page == -1 || GETPOST('button_search','alpha') || GETPOST('button_removefilter','alpha') || (empty($toselect) && $massaction === '0')) { $page = 0; }	 // If $page is not defined, or '' or -1 or if we click on clear filters or if we select empty mass action
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield='p.ref, r.ref';
if (! $sortorder) $sortorder='ASC, ASC';

// Initialize technical objects
//$object = new Report($db);
$object = new Project($db);
$reportstatic = new Report($db);
$extrafields_project = new ExtraFields($db);
$extrafields_report = new ExtraFields($db);
$diroutputmassaction=$conf->wip->dir_output . '/temp/massgeneration/'.$user->id;

include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once
if(! empty($conf->global->PROJECT_ALLOW_COMMENT_ON_PROJECT) && method_exists($object, 'fetchComments') && empty($object->comments)) $object->fetchComments();

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('reportlist'));	 // Note that conf->hooks_modules contains array

// Fetch optionals attributes and labels
if ($id > 0 || ! empty($ref))
{
	$extralabels_projet=$extrafields_project->fetch_name_optionals_label($reportstatic->table_element);
}
$extralabels_report = $extrafields_report->fetch_name_optionals_label('report');
$search_array_options=$extrafields_report->getOptionalsFromPost($extralabels,'','search_');

// Default sort order (if not yet defined by previous GETPOST)
if (! $sortfield) $sortfield="t.".key($reportstatic->fields);   // Set here default search field. By default 1st field in definition.
if (! $sortorder) $sortorder="ASC";

// Security check
$socid=0;
if ($user->societe_id > 0)	// For external user, no check is done on company because readability is managed by public status of project and assignement.
{
	//$socid = $user->societe_id;
	accessforbidden();
}
$result = restrictedArea($user, 'projet', $id, 'projet&project');
//$result = restrictedArea($user, 'wip', $id, '');

// Initialize search criterias
$search_all=trim(GETPOST("search_all",'alpha'));
$search_project=GETPOST('search_project');
if (! isset($_GET['search_projectstatus']) && ! isset($_POST['search_projectstatus']))
{
	if ($search_all != '') $search_projectstatus=-1;
	else $search_projectstatus=1;
}
else $search_projectstatus=GETPOST('search_projectstatus');
$search_project_ref=GETPOST('search_project_ref');
$search_project_title=GETPOST('search_project_title');
$search_report_ref=GETPOST('search_report_ref');
$search_report_label=GETPOST('search_report_label');
$search_report_description=GETPOST('search_report_description');
$search_project_user=GETPOST('search_project_user');

$search_sday	= GETPOST('search_sday','int');
$search_smonth	= GETPOST('search_smonth','int');
$search_syear	= GETPOST('search_syear','int');
$search_eday	= GETPOST('search_eday','int');
$search_emonth	= GETPOST('search_emonth','int');
$search_eyear	= GETPOST('search_eyear','int');

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array();
$fieldstosearchall = array(
	'r.ref'=>"Ref",
	'r.label'=>"Label",
	'r.sec1description'=>"Description",
);
// Definition of fields for list
$arrayfields=array();
foreach($reportstatic->fields as $key => $val)
{
	// If $val['visible']==0, then we never show the field
	if (! empty($val['visible'])) $arrayfields['t.'.$key]=array('label'=>$val['label'], 'checked'=>(($val['visible']<0)?0:1), 'enabled'=>$val['enabled'], 'position'=>$val['position']);
}

$arrayfields=array(
	'p.ref'=>array('label'=>$langs->trans("ProjectRef"), 'checked'=>1, 'position'=>10),
	'p.title'=>array('label'=>$langs->trans("ProjectLabel"), 'checked'=>1, 'position'=>20),
	's.nom'=>array('label'=>$langs->trans("ThirdParty"), 'checked'=>0, 'position'=>30),
	'p.fk_statut'=>array('label'=>$langs->trans("ProjectStatus"), 'checked'=>1, 'position'=>40),
	'r.ref'=>array('label'=>$langs->trans("ReportRef"), 'checked'=>1, 'position'=>50),
	'r.label'=>array('label'=>$langs->trans("ReportLabel"), 'checked'=>1, 'position'=>60),
	'r.status'=>array('label'=>$langs->trans("Status"), 'checked'=>1, 'position'=>70),
	'r.date_start'=>array('label'=>$langs->trans("DateStart"), 'checked'=>1, 'position'=>80),
	'r.date_end'=>array('label'=>$langs->trans("DateEnd"), 'checked'=>1, 'position'=>81),
	'r.date_creation'=>array('label'=>$langs->trans("DateCreation"), 'checked'=>0, 'position'=>82),
	'r.tms'=>array('label'=>$langs->trans("DateModificationShort"), 'checked'=>0, 'position'=>83),
);

// Extra fields
if (is_array($extrafields_report->attributes[$reportstatic->table_element]['label']) && count($extrafields_report->attributes[$reportstatic->table_element]['label']) > 0)
{
	foreach($extrafields_report->attributes[$reportstatic->table_element]['label'] as $key => $val)
	{
		if (! empty($extrafields_report->attributes[$reportstatic->table_element]['list'][$key]))
			$arrayfields["ef.".$key]=array('label'=>$extrafields_report->attributes[$reportstatic->table_element]['label'][$key], 'checked'=>(($extrafields_report->attributes[$reportstatic->table_element]['list'][$key]<0)?0:1), 'position'=>$extrafields_report->attributes[$reportstatic->table_element]['pos'][$key], 'enabled'=>(abs($extrafields_report->attributes[$reportstatic->table_element]['list'][$key])!=3 && $extrafields_report->attributes[$reportstatic->table_element]['perms'][$key]));
	}
}
$reportstatic->fields = dol_sort_array($reportstatic->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');


/*
 * Actions
 *
 * Put here all code to do according to value of "$action" parameter
 */

if (GETPOST('cancel','alpha')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction','alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction=''; }

$parameters=array('id'=>$id);
$reshook=$hookmanager->executeHooks('doActions', $parameters, $reportstatic, $action);	// Note that $action and $reportstatic may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	// Selection of new fields
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
	{
		$search_all='';
		$search_categ='';
		$search_project='';
		$search_projectstatus=-1;
		$search_project_ref='';
		$search_project_title='';
		$search_report_ref='';
		$search_report_label='';
		$search_report_description='';
		$search_report_status='';
		$search_sday='';
		$search_smonth='';
		$search_syear='';
		$search_eday='';
		$search_emonth='';
		$search_eyear='';
		$toselect='';
		$search_array_options=array();
	}
	if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')
		|| GETPOST('button_search_x','alpha') || GETPOST('button_search.x','alpha') || GETPOST('button_search','alpha'))
	{
		$massaction='';	 // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass='Report';
	$objectlabel='Report';
	$permtoread = $user->rights->wip->read;
	$permtodelete = $user->rights->wip->delete;
	$uploaddir = $conf->wip->dir_output;
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}

if (empty($search_projectstatus) && $search_projectstatus == '') $search_projectstatus=1;

/*
 * View
 *
 * Put here all code to render page
 */

$now = dol_now();
$form=new Form($db);
$formother=new FormOther($db);
$socstatic=new Societe($db);
$projectstatic = new Project($db);
//$reportstatic = new Report($db);
$userstatic=new User($db);

//$help_url="EN:Module_Report|FR:Module_Report_FR|ES:M&oacute;dulo_Report";
$help_url='';
$title=$langs->trans("Project").' - '.$langs->transnoentitiesnoconv("Reports").' - '.$object->ref.' '.$object->name;
if (! empty($conf->global->MAIN_HTML_TITLE) && preg_match('/projectnameonly/',$conf->global->MAIN_HTML_TITLE) && $object->name) $title=$object->ref.' '.$object->name.' - '.$langs->transnoentitiesnoconv("Reports");

// Build and execute select
// --------------------------------------------------------------------
$sql = 'SELECT ';
$sql.= ' p.rowid as projectid, p.ref as projectref, p.title as projecttitle, p.fk_statut as projectstatus, p.datee as projectdatee, p.fk_opp_status, p.public, p.fk_user_creat as projectusercreate';
$sql.= ', s.nom as name, s.rowid as socid';
$sql.= ', r.date_creation as date_creation, r.date_start as date_start, r.date_end as date_end, r.tms as date_update';
$sql.= ', r.rowid as rowid, r.ref, r.label, r.status';
// We'll need these fields in order to filter by categ
if ($search_categ) $sql .= ', cs.fk_categorie, cs.fk_project';
// Add fields from extrafields
if (! empty($extrafields_report->attributes[$reportstatic->table_element]['label']))
	foreach ($extrafields_report->attributes[$reportstatic->table_element]['label'] as $key => $val) $sql.=($extrafields_report->attributes[$reportstatic->table_element]['type'][$key] != 'separate' ? "ef.".$key.' as options_'.$key.', ' : '');
// Add fields from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListSelect', $parameters, $reportstatic);	// Note that $action and $reportstatic may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql=preg_replace('/, $/','', $sql);
$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s on p.fk_soc = s.rowid";
// We'll need this table joined to the select in order to filter by categ
if (! empty($search_categ)) $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX."categorie_project as cs ON p.rowid = cs.fk_project"; // We'll need this table joined to the select in order to filter by categ
$sql.= ', '.MAIN_DB_PREFIX.$reportstatic->table_element.' as r';
if (is_array($extrafields_report->attributes[$reportstatic->table_element]['label']) && count($extrafields_report->attributes[$reportstatic->table_element]['label'])) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX.$reportstatic->table_element."_extrafields as ef on (r.rowid = ef.fk_object)";
if ($reportstatic->ismultientitymanaged == 1) $sql.= " WHERE t.entity IN (".getEntity($reportstatic->element,0).")";
else $sql.=" WHERE 1 = 1";
$sql.= " AND r.fk_project = p.rowid";
$sql.= " AND p.entity IN (".getEntity('project',0).')';
if (! $user->rights->projet->all->lire) $sql.=" AND p.rowid IN (".($projectsListId?$projectsListId:'0').")";	// public and assigned to projects, or restricted to company for external users
// No need to check company, as filtering of projects must be done by getProjectsAuthorizedForUser
if ($id)					$sql.= " AND p.rowid = ".$id;
if ($socid)					$sql.= " AND (p.fk_soc IS NULL OR p.fk_soc = 0 OR p.fk_soc = ".$socid.")";
if ($search_categ > 0)		$sql.= " AND cs.fk_categorie = ".$db->escape($search_categ);
if ($search_categ == -2)	$sql.= " AND cs.fk_categorie IS NULL";
if ($search_project_ref)	$sql .= natural_search('p.ref', $search_project_ref);
if ($search_project_title)	$sql .= natural_search('p.title', $search_project_title);
if ($search_report_ref)		$sql .= natural_search('r.ref', $search_report_ref);
if ($search_report_label)	$sql .= natural_search('r.label', $search_report_label);
if ($search_societe)		$sql .= natural_search('s.nom', $search_societe);
if ($search_smonth > 0)
{
	if ($search_syear > 0 && empty($search_sday))
	{
		$sql.= " AND r.date_start BETWEEN '".$db->idate(dol_get_first_day($search_syear,$search_smonth,false))."' AND '".$db->idate(dol_get_last_day($search_syear,$search_smonth,false))."'";
	}
	else if ($search_syear > 0 && ! empty($search_sday))
	{
		$sql.= " AND r.date_start BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_smonth, $search_sday, $search_syear))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_smonth, $search_sday, $search_syear))."'";
	}
	else
	{
		$sql.= " AND date_format(r.date_start, '%m') = '".$search_smonth."'";
	}
}
else if ($search_syear > 0)
{
	$sql.= " AND r.date_start BETWEEN '".$db->idate(dol_get_first_day($search_syear,1,false))."' AND '".$db->idate(dol_get_last_day($search_syear,12,false))."'";
}
if ($search_emonth > 0)
{
	if ($search_eyear > 0 && empty($search_eday))
		$sql.= " AND r.date_end BETWEEN '".$db->idate(dol_get_first_day($search_eyear,$search_emonth,false))."' AND '".$db->idate(dol_get_last_day($search_eyear,$search_emonth,false))."'";
		else if ($search_eyear > 0 && ! empty($search_eday))
			$sql.= " AND r.date_end BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_emonth, $search_eday, $search_eyear))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_emonth, $search_eday, $search_eyear))."'";
		else
			$sql.= " AND date_format(r.date_end, '%m') = '".$search_emonth."'";
}
else if ($search_eyear > 0)
{
	$sql.= " AND r.date_end BETWEEN '".$db->idate(dol_get_first_day($search_eyear,1,false))."' AND '".$db->idate(dol_get_last_day($search_eyear,12,false))."'";
}
if ($search_all) $sql .= natural_search(array_keys($fieldstosearchall), $search_all);
if ($search_projectstatus >= 0)
{
	if ($search_projectstatus == 99) $sql .= " AND p.fk_statut <> 2";
	else $sql .= " AND p.fk_statut = ".$db->escape($search_projectstatus);
}
/*if ($search_report_status >= 0)
{
	if ($search_report_status == 99) $sql .= " AND r.status <> 2";
	else $sql .= " AND r.status = ".$db->escape($search_report_status);
}*/

if ($search_all) $sql.= natural_search(array_keys($fieldstosearchall), $search_all);
// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListWhere', $parameters, $reportstatic);	// Note that $action and $reportstatic may have been modified by hook
$sql.= $hookmanager->resPrint;
$sql.= $db->order($sortfield,$sortorder);

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
	$resql = $db->query($sql);
	$nbtotalofrecords = $db->num_rows($resql);
	if (($page * $limit) > $nbtotalofrecords)	// if total of record found is smaller than page * limit, goto and load page 0
	{
		$page = 0;
		$offset = 0;
	}
}
// if total of record found is smaller than limit, no need to do paging and to restart another select with limits set.
if (is_numeric($nbtotalofrecords) && $limit > $nbtotalofrecords)
{
	$num = $nbtotalofrecords;
}
else
{
	$sql.= $db->plimit($limit + 1, $offset);

	$resql = $db->query($sql);
	if (! $resql)
	{
		dol_print_error($db);
		exit;
	}

	$num = $db->num_rows($resql);
}

// Direct jump if only one record found
if ($num == 1 && ! empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $search_all)
{
	$obj = $db->fetch_object($resql);
	$reportid = $obj->rowid;
	header("Location: ".DOL_URL_ROOT.'/wip/report_card.php?id='.$reportid.'&withprojet=1');
	exit;
}

// Output page
// --------------------------------------------------------------------
llxHeader('', $title, $help_url);

// Example : Adding jquery code
/*
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

if ($id > 0 || ! empty($ref))
{
	$object->fetch($id, $ref);
	$object->fetch_thirdparty();
	$res=$object->fetch_optionals();

	// To verify role of users
	//$userAccess = $object->restrictedProjectArea($user,'read');
	$userWrite  = $object->restrictedProjectArea($user,'write');
	//$userDelete = $object->restrictedProjectArea($user,'delete');
	//print "userAccess=".$userAccess." userWrite=".$userWrite." userDelete=".$userDelete;

	$tab=GETPOST('tab')?GETPOST('tab'):'reports';

	$head=project_prepare_head($object);
	dol_fiche_head($head, $tab, $langs->trans("Project"), -1, ($object->public?'projectpub':'project'));

	$param='';
	if ($search_user_id > 0) $param.='&search_user_id='.dol_escape_htmltag($search_user_id);

	// Project card
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

	/*if (! empty($conf->global->PROJECT_USE_OPPORTUNITIES))
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
	}*/

	// Date start - end
	print '<tr><td>'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
	$start = dol_print_date($object->date_start,'dayhour');
	print ($start?$start:'?');
	$end = dol_print_date($object->date_end,'dayhour');
	print ' - ';
	print ($end?$end:'?');
	if ($object->hasDelay()) print img_warning("Late");
	print '</td></tr>';

	// Budget
	print '<tr><td>'.$langs->trans("Budget").'</td><td>';
	if (strcmp($object->budget_amount, '')) print price($object->budget_amount,'',$langs,1,0,0,$conf->currency);
	print '</td></tr>';

	// Other attributes
	$cols = 2;
	include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

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
}



/*
}
else if ($id > 0 || ! empty($ref))
{
*/
	/*
	 * Projet card in view mode
	 */

$arrayofselected=is_array($toselect)?$toselect:array();

$param='';
if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.urlencode($contextpage);
if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.urlencode($limit);
if ($search_sday)					$param.='&search_sday='.$search_sday;
if ($search_smonth)					$param.='&search_smonth='.$search_smonth;
if ($search_syear)					$param.='&search_syear=' .$search_syear;
if ($search_eday)					$param.='&search_eday='.$search_eday;
if ($search_emonth)					$param.='&search_emonth='.$search_emonth;
if ($search_eyear)					$param.='&search_eyear=' .$search_eyear;
if ($socid)							$param.='&socid='.$socid;
if ($search_all != '')				$param.='&search_all='.$search_all;
if ($search_project_ref != '')		$param.='&search_project_ref='.$search_project_ref;
if ($search_project_title != '')	$param.='&search_project_title='.$search_project_title;
if ($search_ref != '')				$param.='&search_ref='.$search_ref;
if ($search_label != '')			$param.='&search_label='.$search_label;
if ($search_societe != '')			$param.='&search_societe='.$search_societe;
if ($search_projectstatus != '')	$param.='&search_projectstatus='.$search_projectstatus;
if ($search_report_status != '')	$param.='&search_report_status='.$search_report_status;
if ($optioncss != '')				$param.='&optioncss='.urlencode($optioncss);
// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';

// List of mass actions available
$arrayofmassactions =  array(
	//'presend'=>$langs->trans("SendByMail"),
	//'builddoc'=>$langs->trans("PDFMerge"),
);
if ($user->rights->wip->delete) $arrayofmassactions['predelete']=$langs->trans("Delete");
if (in_array($massaction, array('presend','predelete'))) $arrayofmassactions=array();
$massactionbutton=$form->selectMassAction('', $arrayofmassactions);

// Link to create report
$newcardbutton='';
if ($user->rights->projet->all->creer || $user->rights->projet->creer)
{
	if ($id > 0 || ! empty($ref))
	{
		//if (! $object->statut == Project::STATUS_CLOSED)
		if ( $object->statut == 0)
		{
			$newcardbutton = '<a class="butActionNewRefused" href="#" title="'.$langs->trans("WarningProjectClosed").'">'.$langs->trans('NewReport').'<span class="fa fa-plus-circle valignmiddle"></span></a>';
		}
		else
		{
			$newcardbutton = '<a class="butActionNew" href="report_card.php?projectid='.$object->id.'&action=create&withproject=1&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id).'">'.$langs->trans('NewReport').'</span>';
			$newcardbutton.= '<span class="fa fa-plus-circle valignmiddle"></span>';
			$newcardbutton.= '</a>';
		}
	}
	else
	{
		$newcardbutton = '<a class="butActionNew" href="report_card.php?action=createnilproject&backtopage='.urlencode($_SERVER['PHP_SELF']).'">'.$langs->trans('NewReport').'</span>';
		$newcardbutton.= '<span class="fa fa-plus-circle valignmiddle"></span>';
		$newcardbutton.= '</a>';
	}
}
else
{
	$newcardbutton = '<a class="butActionNewRefused" href="#" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('NewReport').'<span class="fa fa-plus-circle valignmiddle"></span></a>';
}

print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="action" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="type" value="'.$type.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';

$title=$langs->trans("ListOfReports");

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'title_report', 0, $newcardbutton, '', $limit);

// Show description of content
/*print '<div class="opacitymedium">';
if ($search_task_user == $user->id) print $langs->trans("MyTasksDesc").'<br><br>';
else
{
	if ($user->rights->projet->all->lire && ! $socid) print $langs->trans("TasksOnProjectsDesc").'<br><br>';
	else print $langs->trans("TasksOnProjectsPublicDesc").'<br><br>';
}
print '</div>';*/

// Add code for pre mass action (confirmation or email presend form)
$topicmail="SendReportRef";
$modelmail="report";
$objecttmp=new Report($db);
$trackid='xxxx'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

if ($search_all)
{
	foreach($fieldstosearchall as $key => $val) $fieldstosearchall[$key]=$langs->trans($val);
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $search_all) . join(', ',$fieldstosearchall).'</div>';
}

$moreforfilter = '';
/*$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
$moreforfilter.= '</div>';*/

if (! $id > 0 && empty($ref))
{
	// Filter on categories
	if (! empty($conf->categorie->enabled))
	{
		require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
		$moreforfilter.='<div class="divsearchfield">';
		$moreforfilter.=$langs->trans('ProjectCategories'). ': ';
		$moreforfilter.=$formother->select_categories('project', $search_categ, 'search_categ', 1, 'maxwidth300');
		$moreforfilter.='</div>';
	}

	if (! empty($moreforfilter))
	{
		print '<div class="liste_titre liste_titre_bydiv centpercent">';
		print $moreforfilter;
		$parameters=array();
		$reshook=$hookmanager->executeHooks('printFieldPreListTitle', $parameters, $reportstatic);	// Note that $action and $reportstatic may have been modified by hook
		if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
		else $moreforfilter = $hookmanager->resPrint;
		print '</div>';
	}
}
$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
$selectedfields.=(count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">';		// You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'" id="tablelines3">'."\n";


// Fields title search
// --------------------------------------------------------------------
//print '<tr class="liste_titre">';
print '<tr class="liste_titre_filter">';

if (!$id > 0 && empty($ref))
{
	//if (! empty($arrayfields['p.ref']['checked']))
	//{
		print '<td class="liste_titre" id="p.ref">';
		print '<input type="text" class="flat" name="search_project_ref" value="'.$search_project_ref.'" size="4">';
		print '</td>';
	//}
	if (! empty($arrayfields['p.title']['checked']))
	{
		print '<td class="liste_titre" id="p.title">';
		print '<input type="text" class="flat" name="search_project_title" value="'.$search_project_title.'" size="6">';
		print '</td>';
	}
	if (! empty($arrayfields['s.nom']['checked']))
	{
		print '<td class="liste_titre" id="s.nom">';
		print '<input type="text" class="flat" name="search_societe" value="'.dol_escape_htmltag($search_societe).'" size="4">';
		print '</td>';
	}
	if (! empty($arrayfields['p.fk_statut']['checked']))
	{
		print '<td class="liste_titre center" id="p.statut">';
		$arrayofstatus = array();
		foreach($projectstatic->statuts_short as $key => $val) $arrayofstatus[$key]=$langs->trans($val);
		$arrayofstatus['99']=$langs->trans("NotClosed").' ('.$langs->trans('Draft').'+'.$langs->trans('Opened').')';
		print $form->selectarray('search_projectstatus', $arrayofstatus, $search_projectstatus, 1, 0, 0, '', 0, 0, 0, '', 'maxwidth100');
		print '</td>';
	}
}

// Report ref
//if (! empty($arrayfields['r.ref']['checked']))
//{
	print '<td class="liste_titre" id="r.ref">';
	print '<input type="text" class="flat" name="search_report_ref" value="'.dol_escape_htmltag($search_report_ref).'" size="4">';
	print '</td>';
//}
// Report label
//if (! empty($arrayfields['r.label']['checked']))
//{
	print '<td class="liste_titre" id="r.label">';
	print '<input type="text" class="flat" name="search_report_label" value="'.dol_escape_htmltag($search_report_label).'" size="8">';
	print '</td>';
//}
// Start date
if (! empty($arrayfields['r.date_start']['checked']))
{
	print '<td class="liste_titre center" id="r.date_start">';
	if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat" type="text" size="1" maxlength="2" name="search_sday" value="'.$search_sday.'">';
	print '<input class="flat" type="text" size="1" maxlength="2" name="search_smonth" value="'.$search_smonth.'">';
	$formother->select_year($search_syear?$search_syear:-1,'search_syear',1, 20, 5);
	print '</td>';
}
// End date
if (! empty($arrayfields['r.date_end']['checked']))
{
	print '<td class="liste_titre center"id="r.date_end">';
	if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat" type="text" size="1" maxlength="2" name="search_eday" value="'.$search_eday.'">';
	print '<input class="flat" type="text" size="1" maxlength="2" name="search_emonth" value="'.$search_emonth.'">';
	$formother->select_year($search_eyear?$search_eyear:-1,'search_eyear',1, 20, 5);
	print '</td>';
}
// Report status
if (! empty($arrayfields['r.status']['checked']))
{
	print '<td class="liste_titre" id="r.status">';
	print '<input type="text" class="flat" name="search_report_status" value="'.dol_escape_htmltag($search_report_status).'" size="8">';
	print '</td>';
}

// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';
// Fields from hook
$parameters=array('arrayfields'=>$arrayfields);
$reshook=$hookmanager->executeHooks('printFieldListOption', $parameters, $reportstatic);	// Note that $action and $reportstatic may have been modified by hook
print $hookmanager->resPrint;

if (! empty($arrayfields['r.date_creation']['checked']))
{
	// Date creation
	print '<td class="liste_titre" id="r.date_creation">';
	print '</td>';
}
if (! empty($arrayfields['r.tms']['checked']))
{
	// Date modification
	print '<td class="liste_titre" id="r.tms">';
	print '</td>';
}
// Action column
print '<td class="liste_titre" align="right" id="action">';
$searchpicto=$form->showFilterButtons();
print $searchpicto;
print '</td>';
print '</tr>'."\n";


// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre">';
if (!$id > 0 && empty($ref))
{
	if (! empty($arrayfields['p.ref']['checked']))			print_liste_field_titre($arrayfields['p.ref']['label'],$_SERVER["PHP_SELF"],"p.ref","",$param,"",$sortfield,$sortorder);
	if (! empty($arrayfields['p.title']['checked']))		print_liste_field_titre($arrayfields['p.title']['label'],$_SERVER["PHP_SELF"],"p.title","",$param,"",$sortfield,$sortorder);
	if (! empty($arrayfields['s.nom']['checked']))			print_liste_field_titre($arrayfields['s.nom']['label'],$_SERVER["PHP_SELF"],"s.nom","",$param,"",$sortfield,$sortorder);
	if (! empty($arrayfields['p.fk_statut']['checked']))	print_liste_field_titre($arrayfields['p.fk_statut']['label'],$_SERVER["PHP_SELF"],"p.fk_statut","",$param,'align="center"',$sortfield,$sortorder);
}
if (! empty($arrayfields['r.ref']['checked']))			print_liste_field_titre($arrayfields['r.ref']['label'],$_SERVER["PHP_SELF"],"r.ref","",$param,"",$sortfield,$sortorder);
if (! empty($arrayfields['r.label']['checked']))		print_liste_field_titre($arrayfields['r.label']['label'],$_SERVER["PHP_SELF"],"r.label","",$param,"",$sortfield,$sortorder);
if (! empty($arrayfields['r.date_start']['checked']))	print_liste_field_titre($arrayfields['r.date_start']['label'],$_SERVER["PHP_SELF"],"r.date_start","",$param,'align="center"',$sortfield,$sortorder);
if (! empty($arrayfields['r.date_end']['checked']))		print_liste_field_titre($arrayfields['r.date_end']['label'],$_SERVER["PHP_SELF"],"r.date_end","",$param,'align="center"',$sortfield,$sortorder);
if (! empty($arrayfields['r.status']['checked']))		print_liste_field_titre($arrayfields['r.status']['label'],$_SERVER["PHP_SELF"],"r.status","",$param,"",$sortfield,$sortorder);

// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
// Hook fields
$parameters=array('arrayfields'=>$arrayfields,'param'=>$param,'sortfield'=>$sortfield,'sortorder'=>$sortorder);
$reshook=$hookmanager->executeHooks('printFieldListTitle', $parameters, $reportstatic);	// Note that $action and $reportstatic may have been modified by hook
print $hookmanager->resPrint;
if (! empty($arrayfields['r.date_creation']['checked']))  print_liste_field_titre($arrayfields['r.date_creation']['label'],$_SERVER["PHP_SELF"],"date_creation","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
if (! empty($arrayfields['r.tms']['checked']))	print_liste_field_titre($arrayfields['r.tms']['label'],$_SERVER["PHP_SELF"],"r.tms","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="center"',$sortfield,$sortorder,'maxwidthsearch ');
print "</tr>\n";


// Detect if we need a fetch on each output line
$needToFetchEachLine=0;
if (is_array($extrafields_report->attributes[$reportstatic->table_element]['computed']) && count($extrafields_report->attributes[$reportstatic->table_element]['computed']) > 0)
{
	foreach ($extrafields_report->attributes[$reportstatic->table_element]['computed'] as $key => $val)
	{
		if (preg_match('/\$reportstatic/',$val)) $needToFetchEachLine++;  // There is at least one compute field that use $reportstatic
	}
}

// Loop on record
// --------------------------------------------------------------------
$i=0;
$totalarray=array();
while ($i < min($num, $limit))
{
	$obj = $db->fetch_object($resql);
	if (empty($obj)) break;		// Should not happen

	// Store properties in $reportstatic
	$reportstatic->id = $obj->rowid;
	foreach($reportstatic->fields as $key => $val)
	{
		if (isset($obj->$key)) $reportstatic->$key = $obj->$key;
	}

	$projectstatic->id = $obj->projectid;
	$projectstatic->ref = $obj->projectref;
	$projectstatic->title = $obj->projecttitle;
	$projectstatic->statut = $obj->projectstatus;
	$projectstatic->datee = $db->jdate($obj->projectdatee);

	// Show here line of result
	print '<tr data-rowid="'.$object->rowid.'" class="oddeven">';

	if (!$id > 0 && empty($ref))
	{
		// Project ref
		//if (! empty($arrayfields['p.ref']['checked']))
		//{
			print '<td class="nowrap">';
			print $projectstatic->getNomUrl(1);
			if ($projectstatic->hasDelay()) print img_warning("Late");
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		//}
		// Project title
		if (! empty($arrayfields['p.title']['checked']))
		{
			print '<td>';
			print dol_trunc($obj->projecttitle,80);
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}
		// Third party
		if (! empty($arrayfields['s.nom']['checked']))
		{
			print '<td>';
			if ($obj->socid)
			{
				$socstatic->id=$obj->socid;
				$socstatic->name=$obj->name;
				print $socstatic->getNomUrl(1);
			}
			else
			{
				print '&nbsp;';
			}
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}
		// Project status
		if (! empty($arrayfields['p.fk_statut']['checked']))
		{
			print '<td class="nowrap left">';
			print $projectstatic->getLibStatut(2);
			print '</td>';
			if (! $i) $totalarray['nbfield']++;
		}
	}
	// Ref
	//if (! empty($arrayfields['r.ref']['checked']))
	//{
		print '<td td class="nowrap">';
		print $reportstatic->getNomUrl(1,'withproject');
		//if ($reportstatic->hasDelay()) print img_warning("Late");
		print '</td>';
		if (! $i) $totalarray['nbfield']++;
	//}
	// Label
	//if (! empty($arrayfields['r.label']['checked']))
	//{
		print '<td>';
		print $reportstatic->label;
		print '</td>';
		if (! $i) $totalarray['nbfield']++;
	//}
	// Date start
	if (! empty($arrayfields['r.date_start']['checked']))
	{
		print '<td class="center">';
		print dol_print_date($db->jdate($obj->date_start),'day');
		print '</td>';
		if (! $i) $totalarray['nbfield']++;
	}
	// Date end
	if (! empty($arrayfields['r.date_end']['checked']))
	{
		print '<td class="center">';
		print dol_print_date($db->jdate($obj->date_end),'day');
		print '</td>';
		if (! $i) $totalarray['nbfield']++;
	}
	// Report status
	if (! empty($arrayfields['r.status']['checked']))
	{
		print '<td class="nowrap left">';
		print $reportstatic->getLibStatut(2);
		print '</td>';
		if (! $i) $totalarray['nbfield']++;
	}

	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
	// Fields from hook
	$parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
	$reshook=$hookmanager->executeHooks('printFieldListValue', $parameters, $reportstatic);	// Note that $action and $reportstatic may have been modified by hook
	print $hookmanager->resPrint;
	// Date creation
	if (! empty($arrayfields['r.date_creation']['checked']))
	{
		print '<td align="center">';
		print dol_print_date($db->jdate($obj->date_creation), (!$id > 0 && empty($ref))?'day':'dayhour' , 'tzuser');
		print '</td>';
		if (! $i) $totalarray['nbfield']++;
	}
	// Date modification
	if (! empty($arrayfields['r.tms']['checked']))
	{
		print '<td class="nowrap center">';
		print dol_print_date($db->jdate($obj->date_update), (!$id > 0 && empty($ref))?'day':'dayhour', 'tzuser');
		print '</td>';
		if (! $i) $totalarray['nbfield']++;
	}
	// Action column
	print '<td class="nowrap" align="center">';
	if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
	{
		$selected=0;
		if (in_array($obj->rowid, $arrayofselected)) $selected=1;
		print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected?' checked="checked"':'').'>';
	}
	print '</td>';
	if (! $i) $totalarray['nbfield']++;

	print "</tr>\n";

	//print projectLinesa();

	$i++;
}

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

// If no record found
if ($num == 0)
{
	$colspan=1;
	foreach($arrayfields as $key => $val) { if (! empty($val['checked'])) $colspan++; }
	print '<tr><td colspan="'.$colspan.'" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
}


$db->free($resql);

$parameters=array('arrayfields'=>$arrayfields, 'sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $reportstatic);	// Note that $action and $reportstatic may have been modified by hook
print $hookmanager->resPrint;

print '</table>'."\n";
print '</div>'."\n";

print '</form>'."\n";

if (in_array('builddoc',$arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords))
{
	$hidegeneratedfilelistifempty=1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) $hidegeneratedfilelistifempty=0;

	require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php');
	$formfile = new FormFile($db);

	// Show list of available documents
	$urlsource=$_SERVER['PHP_SELF'].'?sortfield='.$sortfield.'&sortorder='.$sortorder;
	$urlsource.=str_replace('&amp;','&',$param);

	$filedir=$diroutputmassaction;
	$genallowed=$user->rights->wip->read;
	$delallowed=$user->rights->wip->create;

	print $formfile->showdocuments('massfilesarea_wip','',$filedir,$urlsource,0,$delallowed,'',1,1,0,48,1,$param,$title,'','','',null,$hidegeneratedfilelistifempty);
}

// End of page
llxFooter();
$db->close();
