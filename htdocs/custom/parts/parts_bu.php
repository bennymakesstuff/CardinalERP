<?php
/* Copyright (C) 2007-2017 Laurent Destailleur  <eldy@users.sourceforge.net>
 *  * Copyright (C) 2018      Peter Roberts		<webmaster@finchmc.com.au>
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
 *      \file       htdocs/projet/parts.php
 *      \ingroup    projet
 *		\brief      List page to show parts ordered for projects
 */

//if (! defined('NOREQUIREDB'))              define('NOREQUIREDB','1');					// Do not create database handler $db
//if (! defined('NOREQUIREUSER'))            define('NOREQUIREUSER','1');				// Do not load object $user
//if (! defined('NOREQUIRESOC'))             define('NOREQUIRESOC','1');				// Do not load object $mysoc
//if (! defined('NOREQUIRETRAN'))            define('NOREQUIRETRAN','1');				// Do not load object $langs
//if (! defined('NOSCANGETFORINJECTION'))    define('NOSCANGETFORINJECTION','1');		// Do not check injection attack on GET parameters
//if (! defined('NOSCANPOSTFORINJECTION'))   define('NOSCANPOSTFORINJECTION','1');		// Do not check injection attack on POST parameters
//if (! defined('NOCSRFCHECK'))              define('NOCSRFCHECK','1');					// Do not check CSRF attack (test on referer + on token if option MAIN_SECURITY_CSRF_WITH_TOKEN is on).
//if (! defined('NOTOKENRENEWAL'))           define('NOTOKENRENEWAL','1');				// Do not roll the Anti CSRF token (used if MAIN_SECURITY_CSRF_WITH_TOKEN is on)
//if (! defined('NOSTYLECHECK'))             define('NOSTYLECHECK','1');				// Do not check style html tag into posted data
//if (! defined('NOIPCHECK'))                define('NOIPCHECK','1');					// Do not check IP defined into conf $dolibarr_main_restrict_ip
//if (! defined('NOREQUIREMENU'))            define('NOREQUIREMENU','1');				// If there is no need to load and show top and left menu
//if (! defined('NOREQUIREHTML'))            define('NOREQUIREHTML','1');				// If we don't need to load the html.form.class.php
//if (! defined('NOREQUIREAJAX'))            define('NOREQUIREAJAX','1');       	  	// Do not load ajax.lib.php library
//if (! defined("NOLOGIN"))                  define("NOLOGIN",'1');						// If this page is public (can be called outside logged session)
//if (! defined("MAIN_LANG_DEFAULT"))        define('MAIN_LANG_DEFAULT','auto');					// Force lang to a particular value
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
// require '../main.inc.php';

// PJR TODO -check what is really needed
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formorder.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.class.php';
require_once DOL_DOCUMENT_ROOT.'/fourn/class/fournisseur.commande.class.php';
require_once DOL_DOCUMENT_ROOT.'/societe/class/societe.class.php';
dol_include_once('/parts/class/ordlink.class.php');

// Load translation files required by the page
$langs->loadLangs(array('projects', 'companies', 'suppliers', 'compta', 'orders'));
$langs->loadLangs(array("parts@parts","other"));

//$action=GETPOST('action','aZ09');
$action     = GETPOST('action','aZ09')?GETPOST('action','aZ09'):'view';				// The action 'add', 'create', 'edit', 'update', 'view', ...
if ($id == '' && $projectid == '' && $ref == '' && $action != 'listall')
{
	dol_print_error('','Bad parameter');
	exit;
}

$massaction = GETPOST('massaction','alpha');											// The bulk action (combo box choice into lists)
$show_files = GETPOST('show_files','int');												// Show files area generated by bulk actions ?
$confirm    = GETPOST('confirm','alpha');												// Result of a confirmation
$cancel     = GETPOST('cancel', 'alpha');												// We click on a Cancel button
$toselect   = GETPOST('toselect', 'array');												// Array of ids of elements selected into a list
$contextpage= GETPOST('contextpage','aZ')?GETPOST('contextpage','aZ'):'ordlinklist';   // To manage different context of search
$backtopage = GETPOST('backtopage','alpha');											// Go back to a dedicated page
$optioncss  = GETPOST('optioncss','aZ');												// Option for the css output (always '' except when 'print')

$id			= GETPOST('id','int');

//$id=GETPOST('id','int')!=''?GETPOST('id','int'):274; // for testing - delete later

$projectid=$id;	// For backward compatibility
$ref=GETPOST('ref','alpha');

$socid=GETPOST('socid','int');

//$billed = GETPOST('billed','int');
//$viewstatut=GETPOST('viewstatut');

// Load variable for pagination
$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST('sortfield','alpha');
$sortorder = GETPOST('sortorder','alpha');
$page = GETPOST('page','int');
if (empty($page) || $page == -1 || GETPOST('button_search','alpha') || GETPOST('button_removefilter','alpha') || (empty($toselect) && $massaction === '0')) { $page = 0; }     // If $page is not defined, or '' or -1 or if we click on clear filters or if we select empty mass action
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;

// Initialize technical objects
$object = new Project($db);
//$extrafields = new ExtraFields($db);
$diroutputmassaction=$conf->parts->dir_output . '/temp/massgeneration/'.$user->id;
$hookmanager->initHooks(array('partslist'));     // Note that conf->hooks_modules contains array
/* PJR TODO
// Fetch optionals attributes and labels
//$extralabels = $extrafields->fetch_name_optionals_label('ordlink');
//$extralabels = $extrafields->fetch_name_optionals_label('commande');
//$extralabels = $extrafields->fetch_name_optionals_label($object->table_element);
$search_array_options=$extrafields->getOptionalsFromPost($extralabels,'','search_');
*/

// Default sort order (if not yet defined by previous GETPOST)
if ($action == 'listall')
{
	if (! $sortfield) $sortfield='p.ref,cd.rowid';
	if (! $sortorder) $sortorder='ASC,ASC';
}
else
{
	if (! $sortfield) $sortfield='cd.rowid';
	if (! $sortorder) $sortorder='ASC';
}
// Security check
$socid=0;
if ($user->societe_id > 0)	// Protection if external user
{
	//$socid = $user->societe_id;
	accessforbidden();
}
//$result = restrictedArea($user, 'parts', $id, '');

// Initialize array of search criterias
$search_all=trim(GETPOST("search_all",'alpha'));
$search=array();
/*
foreach($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
}
*/
$search_project=GETPOST('search_project');
$search_project_ref=GETPOST('search_project_ref');
$search_project_title=GETPOST('search_project_title');
$search_orderyear=GETPOST("search_orderyear","int");
$search_ordermonth=GETPOST("search_ordermonth","int");
$search_orderday=GETPOST("search_orderday","int");
$search_deliveryyear=GETPOST("search_deliveryyear","int");
$search_deliverymonth=GETPOST("search_deliverymonth","int");
$search_deliveryday=GETPOST("search_deliveryday","int");
$search_product_category=GETPOST('search_product_category','int');
$search_ref=GETPOST('search_ref','alpha')!=''?GETPOST('search_ref','alpha'):GETPOST('sref','alpha');
$search_line=GETPOST('search_line','int')!=''?GETPOST('search_line','int'):GETPOST('sline','int');
$search_desc=GETPOST('search_desc','alpha')!=''?GETPOST('search_desc','alpha'):GETPOST('sdesc','alpha');
$search_ref_customer=GETPOST('search_ref_customer','alpha');

$search_user=GETPOST('search_user','int');
$search_sale=GETPOST('search_sale','int');

$search_btn=GETPOST('button_search','alpha');
$search_remove_btn=GETPOST('button_removefilter','alpha');


/*
// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	'c.ref'=>'Ref',
	'c.ref_client'=>'RefCustomerOrder',
	'pd.description'=>'Description',
	's.nom'=>"ThirdParty",
	'c.note_public'=>'NotePublic',
);

foreach($object->fields as $key => $val)
{
	if ($val['searchall']) $fieldstosearchall['t.'.$key]=$val['label'];
}
*/

if (! isset($_GET['search_projectstatus']) && ! isset($_POST['search_projectstatus']))
{
	if ($search_all != '') $search_projectstatus=-1;
	else $search_projectstatus=1;
}
else
{
	$search_projectstatus=GETPOST('search_projectstatus');
}

// Definition of fields for list
$arrayfields=array(
	'p.ref'=>array('label'=>$langs->trans("ProjectRef"), 'checked'=>1),
	'p.title'=>array('label'=>$langs->trans("ProjectLabel"), 'checked'=>0),
	'p.fk_statut'=>array('label'=>$langs->trans("ProjectStatus"), 'checked'=>1),
	's.nom'=>array('label'=>$langs->trans("Customer"), 'checked'=>0),
	'c.line'=>array('label'=>$langs->trans("Line"), 'checked'=>1),
	'c.desc'=>array('label'=>$langs->trans("Description"), 'checked'=>1),
	'c.qty'=>array('label'=>$langs->trans("Qty"), 'checked'=>1),
	'c.unit'=>array('label'=>$langs->trans("Units"), 'checked'=>1),
//	'c.unit'=>array('label'=>$langs->trans("Units"), 'checked'=>1,'enabled'=>!empty($conf->global->ORDER_DISABLE_DELIVERY_DATE)),

	'c.ref'=>array('label'=>$langs->trans("Vehicle Order"), 'checked'=>1),
	'c.date_commande'=>array('label'=>$langs->trans("Creation Date"), 'checked'=>1),
	'c.date_delivery'=>array('label'=>$langs->trans("Wanted Date"), 'checked'=>1),/* 'enabled'=>empty($conf->global->ORDER_DISABLE_DELIVERY_DATE)),*/

/*
	'c.ref_client'=>array('label'=>$langs->trans("RefCustomerOrder"), 'checked'=>1),
	'c.datec'=>array('label'=>$langs->trans("DateCreation"), 'checked'=>0, 'position'=>500),
	'c.tms'=>array('label'=>$langs->trans("DateModificationShort"), 'checked'=>0, 'position'=>500),
	'c.fk_statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1, 'position'=>1000),
	'c.facture'=>array('label'=>$langs->trans("Billed"), 'checked'=>1, 'position'=>1000, 'enabled'=>(empty($conf->global->WORKFLOW_BILL_ON_SHIPMENT)))
*/

	'cf.ref'=>array('label'=>$langs->trans("Purchase Order"), 'checked'=>1),
	'cf.ref_supplier'=>array('label'=>$langs->trans("Supplier Ref."), 'checked'=>1, 'enabled'=>1),
	'u.login'=>array('label'=>$langs->trans("AuthorRequest"), 'checked'=>1),
	'cf.fk_soc'=>array('label'=>$langs->trans("Supplier"), 'checked'=>1),
	'cf.date_commande'=>array('label'=>$langs->trans("OrderDateShort"), 'checked'=>1),
	'cf.date_delivery'=>array('label'=>$langs->trans("Planned Delivery"), 'checked'=>1),
	'cf.total_ht'=>array('label'=>$langs->trans("AmountHT"), 'checked'=>1),
	'cf.total_vat'=>array('label'=>$langs->trans("AmountVAT"), 'checked'=>0),
	'cf.total_ttc'=>array('label'=>$langs->trans("AmountTTC"), 'checked'=>0),

	'cf.fk_statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1,),
	'cf.billed'=>array('label'=>$langs->trans("Billed"), 'checked'=>1, 'position'=>1000, 'enabled'=>1),
	'cf.datec'=>array('label'=>$langs->trans("PO Creation Date"), 'checked'=>0),
	'cf.tms'=>array('label'=>$langs->trans("PO Modif Date"), 'checked'=>0),
	'p2.ref'=>array('label'=>$langs->trans("PO Proj. Ref."), 'checked'=>1),
);
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
*/
/*
$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');
*/
/*
// Load object
include DOL_DOCUMENT_ROOT.'/core/actions_fetchobject.inc.php';  // Must be include, not include_once
if(! empty($conf->global->PROJECT_ALLOW_COMMENT_ON_PROJECT) && method_exists($object, 'fetchComments') && empty($object->comments)) $object->fetchComments();
*/

/*
 * Actions
 *
 */

/*
if (GETPOST('cancel','alpha')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction','alpha') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction=''; }
*/

$parameters=array('socid'=>$socid, 'projectid'=>$projectid);
$reshook=$hookmanager->executeHooks('doActions', $parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
	if ($cancel)
	{
		if (! empty($backtopage))
		{
			header("Location: ".$backtopage);
			exit;
		}
		$action='';
	}
	// Selection of new fields
	include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

	// Purge search criteria
	if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') ||GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
	{
		foreach($object->fields as $key => $val)
		{
			$search[$key]='';
		}
		$toselect='';
		$search_array_options=array();
		$search_all='';
		$search_categ='';
		$search_project='';
		$search_projectstatus=-1;
		$search_project_ref='';
		$search_project_title='';
		$search_user='';
		$search_sale='';
		$search_product_category='';
		$search_line=0;
		$search_desc='';
		$search_ref='';
		$search_ref_customer='';
		$search_orderyear='';
		$search_ordermonth='';
		$search_orderday='';
		$search_deliveryday='';
		$search_deliverymonth='';
		$search_deliveryyear='';
		$search_categ_cus=0;
		$viewstatut='';
		$billed='';
	}

	// Action = listall - List all Projects
	if ($action == 'listall')  // all projects
	{
		//	$socid=161; // the ID for Finch Motor Company
		//	Projects with known parts orders
		$partslists = array();
		$sql = 'SELECT ';
		$sql.= ' p.rowid, p.fk_soc, p.ref AS ref';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'commandedet as cd';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande as c ON (c.rowid = cd.fk_commande)';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet as p ON (p.rowid = c.fk_projet)';
		$sql.= ' WHERE 1 = 1';
		$sql.= ' AND cd.product_type = 0';
		$sql.= ' AND c.fk_projet IS NOT NULL';
	
		$sql.= ' UNION';
	
		$sql.= ' SELECT ';
		$sql.= ' p.rowid, p.fk_soc, p.ref AS ref';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'commande_fournisseurdet as cfd';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur as cf ON (cf.rowid = cfd.fk_commande)';
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet as p ON (p.rowid = cf.fk_projet)';
		$sql.= ' WHERE 1 = 1';
		$sql.= ' AND cfd.product_type = 0';
		$sql.= ' AND cf.fk_projet IS NOT NULL';
	
		$sql.= " ORDER BY ref";
	
		$resql = $db->query($sql);
		if ($resql)
		{
	
			$num = $db->num_rows($resql);
			$totalnboflists=$num;
	
			$i = 0;
			while ($i < $num)
			{
				$partslists[$i] = $db->fetch_object($resql);
				$i++;
			}
			$db->free($resql);
		}
		else dol_print_error($db);
		if (count($partslists) == 0) $partslists[0]='0';	// To avoid sql syntax error if not found
	}

	// Buttons
	if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') || GETPOST('button_removefilter','alpha')
		|| GETPOST('button_search_x','alpha') || GETPOST('button_search.x','alpha') || GETPOST('button_search','alpha'))
	{
		$massaction='';     // Protection to avoid mass action if we force a new search during a mass action confirmation
	}

	// Mass actions
	$objectclass='Commande';
	$objectlabel='Orders';
	$permtoread = $user->rights->commande->lire;
	$permtodelete = $user->rights->commande->supprimer;
	$uploaddir = $conf->commande->dir_output;
//	$trigger_name='ORDER_SENTBYMAIL';
	include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}

if (empty($search_projectstatus) && $search_projectstatus == '') $search_projectstatus=1;


/*
 * View
 *
 */

$form=new Form($db);
$formother = new FormOther($db);
$formorder = new FormOrder($db);
$formfile = new FormFile($db);

$now=dol_now();

//$help_url="EN:Module_OrdLink|FR:Module_OrdLink_FR";
$help_url='';

if ($action == 'listall')  // all projects
{
	$title = $langs->trans('Parts for all Projects');
	llxHeader('',$title);

	$contentdesc = $langs->trans('This table presents all parts listed in Vehicle Orders for all projects');
}
else  // Individual project
{
	$title = $langs->trans("Parts").' - '.$object->ref;
	llxHeader('',$title);

	$head=project_prepare_head($object);
	dol_fiche_head($head, 'parts', $langs->trans("Project"), -1, ($object->public?'projectpub':'project'));

	$contentdesc = $langs->trans('This table presents the parts listed in Vehicle Orders for Project').' - '.$object->ref;
}


// To verify role of users
$userAccess = $object->restrictedProjectArea($user);

/* =================
 *
 * Project card
 * 
 * =================
 */
if ($action != 'listall')  // project card only printed for individual projects
{
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
		$object->next_prev_filter=" te.rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
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

/* =================
 *
 * Start of Main SQL Query
 * 
 * =================
 */
// Build and execute select
// --------------------------------------------------------------------
$sql = 'SELECT ';
/*
foreach($object->fields as $key => $val)
{
	$sql.='t.'.$key.', ';
}
*/
$sql.= '  cd.rowid AS cdrowid';
$sql.= ', cd.fk_commande AS cdfk_commande';
$sql.= ', cd.fk_parent_line AS cdfk_parent_line';
$sql.= ', cd.fk_product AS cdfk_product';
$sql.= ', cd.label AS cdcustom_label';
$sql.= ', cd.description AS cddescription';
$sql.= ', cd.qty AS cdqty';
$sql.= ', cd.product_type AS cdproduct_type';
$sql.= ', cd.fk_unit AS cdfk_unit';

$sql.= ', prd.ref AS cdproduct_ref';
$sql.= ', prd.description AS cdproduct_desc';
$sql.= ', prd.fk_product_type AS cdpfk_product_type';
$sql.= ', prd.label AS cdproduct_label';

$sql.= ', p.rowid AS cdprojectid';
$sql.= ', p.fk_soc AS cdproject_soc';
$sql.= ', p.ref AS cdproject_ref';
$sql.= ', p.fk_statut AS cdproject_status';

$sql.= ', c.rowid AS crowid';
$sql.= ', c.ref AS cref';
$sql.= ', c.fk_projet AS cfk_projet';

$sql.= ', cfd.rowid AS cfdrowid';
$sql.= ', cfd.fk_commande AS cfdfk_commande';
$sql.= ', cfd.description AS cfddescription';
$sql.= ', cfd.qty AS cfdqty';
//$sql.= ', cfd.fk_parent_line AS cfdfk_parent_line';
$sql.= ', cfd.fk_product AS cfdfk_product';
//$sql.= ', cfd.label AS cfdcustom_label';

$sql.= ', cfd.product_type AS cfdproduct_type';
$sql.= ', cfd.fk_unit AS cfdfk_unit';

$sql.= ', p2.rowid AS cfdprojectid';
$sql.= ', p2.fk_soc AS cfdproject_soc';
$sql.= ', p2.ref AS cfdproject_ref';
$sql.= ', p2.fk_statut AS cfdproject_status';

$sql.= ', cf.rowid AS cfrowid';					//	rowid
$sql.= ', cf.ref AS cfref';						//	ref	
$sql.= ', cf.ref_supplier AS cfref_supplier';	//	ref_supplier
$sql.= ', cf.fk_soc AS cffk_soc';				//	fk_soc
$sql.= ', cf.fk_projet AS cffk_projet';			//	fk_projet
//	tms
//	date_creation
//	date_valid
//	date_approve
//	date_approve2
//	date_commande
//	fk_user_author
//	fk_user_modif
//	fk_user_valid
//	fk_user_approve
//	fk_user_approve2
//	source
//	fk_statut
//	billed
//	amount_ht
//	remise_percent
//	remise
//	tva
//	localtax1
//	localtax2
//	total_ht
//	total_ttc
//	note_private
//	note_public
//	model_pdf
//	date_livraison
//	fk_account
//	fk_cond_reglement
//	fk_mode_reglement
//	fk_input_method
//	import_key
//	extraparams
//	fk_incoterms
//	location_incoterms
//	fk_multicurrency
//	multicurrency_code
//	multicurrency_tx
//	multicurrency_total_ht
//	multicurrency_total_tva
//	multicurrency_total_ttc
//	last_main_doc





//        $sqlselect.= ' cf.*, cfd.*';

/*
// Add fields from extrafields
if (! empty($extrafields->attributes[$object->table_element]['label']))
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) $sql.=($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? "ef.".$key.' as options_'.$key.', ' : '');
// Add fields from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListSelect', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql=preg_replace('/, $/','', $sql);
*/

//$sql.= " FROM ".MAIN_DB_PREFIX.$object->table_element." as t";
$sql.= ' FROM '.MAIN_DB_PREFIX.'commandedet as cd';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande AS c ON (c.rowid = cd.fk_commande)';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product AS prd ON (prd.rowid = cd.fk_product)';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet AS p ON (p.rowid = c.fk_projet)';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'parts_ordlink AS pol ON (pol.fk_commandedet = cd.rowid)';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseurdet AS cfd ON (cfd.rowid = pol.fk_object)';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur AS cf ON (cf.rowid = cfd.fk_commande)';
$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet AS p2 ON (p2.rowid = cf.fk_projet)';

/*
		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur as cf ON (cf.rowid = cfd.fk_commande)';


		$sql = 'SELECT ';
		$sql.= ' pol.rowid, pol.fk_object, pol.fk_commandedet';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'parts_ordlink as pol';
//		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'parts_ordlink as pol ON (c.rowid = cd.fk_commande)';
//		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet as p ON (p.rowid = c.fk_projet)';
		$sql.= ' WHERE 1 = 1';
		$sql.= ' AND pol.fk_commandedet = '.$line->rowid;
//		$sql.= ' AND c.fk_projet IS NOT NULL';
	
		$sql.= " ORDER BY pol.fk_commandedet";
*/




//if (is_array($extrafields->attributes[$object->table_element]['label']) && count($extrafields->attributes[$object->table_element]['label'])) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX.$object->table_element."_extrafields as ef on (t.rowid = ef.fk_object)";
/*if ($object->ismultientitymanaged == 1)
{
	$sql.= " WHERE t.entity IN (".getEntity($object->element).")";
}
else*/
//{
	$sql.=" WHERE 1 = 1";
//}
/*
foreach($search as $key => $val)
{
	if ($key == 'status' && $search[$key] == -1) continue;
	$mode_search=(($object->isInt($object->fields[$key]) || $object->isFloat($object->fields[$key]))?1:0);
	if ($search[$key] != '') $sql.=natural_search($key, $search[$key], (($key == 'status')?2:$mode_search));
}
*/
$sql.= ' AND cd.product_type = 0';
if ($action != 'listall') $sql.= ' AND c.fk_projet = '.$id;  // PJR TODO Add WHERE for socid too
//	$sql.= ' AND c.fk_projet IS NOT NULL';

//	if ($only_product) $sql .= ' AND p.fk_product_type = 0';
/*
if ($search_ref)  $sql .= natural_search('c.ref', $search_ref);
if ($search_desc) $sql .= natural_search('cd.description', $search_desc);
if ($search_line) $sql .= ' AND cd.rowid = '.$search_line;
if ($search_project_ref)   $sql .= natural_search('p.ref', $search_project_ref);
if ($search_project_title) $sql .= natural_search('p.title', $search_project_title);
*/
/*
if ($search_task_ref) $sql .= natural_search('pt.ref', $search_task_ref);
if ($search_task_label) $sql .= natural_search('pt.label', $search_task_label);
*/
if ($search_societe) $sql .= natural_search('s.nom', $search_societe);
if ($search_projectstatus >= 0)
{
	if ($search_projectstatus == 99) $sql .= " AND p.fk_statut <> 2";
	else $sql .= " AND p.fk_statut = ".$db->escape($search_projectstatus);
}
/*
if ($search_user > 0) $sql .= natural_search('t.fk_user', $search_user);
if ($search_note) $sql .= natural_search('t.note', $search_note);
*/
if ($search_all) $sql .= natural_search(array_keys($fieldstosearchall), $search_all);

/*
// Add where from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_sql.tpl.php';
// Add where from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListWhere', $parameters, $object);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
*/

/* If a group by is required
$sql.= " GROUP BY "
foreach($object->fields as $key => $val)
{
	$sql.='t.'.$key.', ';
}
// Add fields from extrafields
if (! empty($extrafields->attributes[$object->table_element]['label'])) {
	foreach ($extrafields->attributes[$object->table_element]['label'] as $key => $val) $sql.=($extrafields->attributes[$object->table_element]['type'][$key] != 'separate' ? "ef.".$key.', ' : '');
// Add where from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListGroupBy',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql=preg_replace('/, $/','', $sql);
*/

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
	$sql.= $db->plimit($limit+1, $offset);

	$resql=$db->query($sql);
	if (! $resql)
	{
		dol_print_error($db);
		exit;
	}

	$num = $db->num_rows($resql);
}

//print $sql;
//	$sql.= ' UNION ALL';


/*
	$sql.= ' FROM '.MAIN_DB_PREFIX.'commande_fournisseurdet as cfd';
	$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur as cf ON (cf.rowid = cfd.fk_commande)';
*/


if (! $resql)
{
	dol_print_error($db);
	exit;
}
/*
// Direct jump if only one record found
if ($num == 1 && ! empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && $search_all)
{
	$obj = $db->fetch_object($resql);
	$id = $obj->rowid;
	header("Location: ".DOL_URL_ROOT.'/parts/ordlink_card.php?id='.$id);
	exit;
}
*/


/*
 * Lines
 */

// Output page
// --------------------------------------------------------------------

//llxHeader('', $title, $help_url);

print '<!-- List of parts for project(s) -->';
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
$lines = array();
$i = 0;
while ($i < $num)
{
	$objp = $db->fetch_object($resql);

	$line = new OrderLine($db);
	
	// Commandedet fields
	$line->rowid            = $objp->cdrowid;			// cd.rowid AS cdrowid
	$line->id               = $objp->rowid;				// ???
	$line->fk_commande      = $objp->cdfk_commande;		// cd.fk_commande AS cdfk_commande		USED for $orderstatic
	$line->commande_id      = $objp->cdfk_commande;
	$line->fk_parent_line   = $objp->cdfk_parent_line;	// cd.fk_parent_line AS cdfk_parent_line	
	$line->fk_product       = $objp->cdfk_product;		// cd.fk_product AS cdfk_product		USED for $productstatic
	$line->label            = $objp->custom_label;		// cd.label AS cdcustom_label
	$line->desc             = $objp->cddescription;		// cd.description AS cddescription
	$line->description      = $objp->cddescription;
	$line->qty              = $objp->cdqty;				// cd.qty AS cdqty
	$line->product_type     = $objp->cdproduct_type;	// cd.product_type AS cdproduct_type
	$line->fk_unit			= $objp->cdfk_unit;			// cd.fk_unit AS cdfkunit

	// CD Product fields
	$line->ref				= $objp->cdproduct_ref;		// prd.ref AS cdproduct_ref
	$line->product_ref		= $objp->cdproduct_ref;
	$line->libelle			= $objp->cdproduct_label;	// prd.label AS cdproduct_label
	$line->product_label	= $objp->cdproduct_label;
	$line->product_desc     = $objp->cdproduct_desc;	// prd.description AS cdproduct_desc
	$line->fk_product_type  = $objp->fk_product_type;	// Product or service - prd.fk_product_type AS cdpfk_product_type

//		$line->cdprojectid          = $objp->cdprojectid;

//		$line->fetch_optionals();

	// Commandefourndet fields
	$line->cfd_rowid            = $objp->cfdrowid;			// cd.rowid AS cdrowid
//	$line->cfd_id               = $objp->cfdrowid;			// ???
	$line->cfd_fk_commande      = $objp->cfdfk_commande;	// cd.fk_commande AS cdfk_commande		USED for $orderstatic
//	$line->commande_id      = $objp->cdfk_commande;
//	$line->fk_parent_line   = $objp->cdfk_parent_line;		// cd.fk_parent_line AS cdfk_parent_line	
	$line->cfd_fk_product       = $objp->cfdfk_product;		// cd.fk_product AS cdfk_product		USED for $productstatic
//	$line->label            = $objp->custom_label;			// cd.label AS cdcustom_label
	$line->cfd_desc             = $objp->cfddescription;	// cd.description AS cddescription
//	$line->description      = $objp->cddescription;
	$line->cfd_qty              = $objp->cfdqty;			// cd.qty AS cdqty
	$line->cfd_product_type     = $objp->cfdproduct_type;	// cd.product_type AS cdproduct_type
	$line->cfd_fk_unit			= $objp->cfdfk_unit;		// cd.fk_unit AS cdfkunit

	$lines[$i] = $line;
	$i++;
}
//$db->free($resql);

$arrayofselected=is_array($toselect)?$toselect:array();

$param='';
if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.urlencode($contextpage);
if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.urlencode($limit);
/*
foreach($search as $key => $val)
{
	$param.= '&search_'.$key.'='.urlencode($search[$key]);
}
*/
if ($id)							$param.='&id='.urlencode($id);
if ($projectid)						$param.='&projectid='.urlencode($projectid);
if ($withproject)					$param.='&withproject='.urlencode($withproject);
if ($socid > 0)             		$param.='&socid='.urlencode($socid);
if ($search_all != '') 				$param.='&search_all='.$search_all;
if ($search_project_ref != '') 		$param.='&search_project_ref='.$search_project_ref;
if ($search_project_title != '')	$param.='&search_project_title='.$search_project_title;
if ($search_ref != '')				$param.='&search_ref='.$search_ref;
if ($search_label != '') 			$param.='&search_label='.$search_label;
if ($search_societe != '') 			$param.='&search_societe='.$search_societe;
if ($search_projectstatus != '') 	$param.='&search_projectstatus='.$search_projectstatus;
if ($search_orderday)	      		$param.='&search_orderday='.urlencode($search_orderday);
if ($search_ordermonth) 	     	$param.='&search_ordermonth='.urlencode($search_ordermonth);
if ($search_orderyear)      	 	$param.='&search_orderyear='.urlencode($search_orderyear);
if ($search_deliveryday)   			$param.='&search_deliveryday='.urlencode($search_deliveryday);
if ($search_deliverymonth)			$param.='&search_deliverymonth='.urlencode($search_deliverymonth);
if ($search_deliveryyear)			$param.='&search_deliveryyear='.urlencode($search_deliveryyear);
if ($search_ref)					$param.='&search_ref='.urlencode($search_ref);
if ($search_line)   	   			$param.='&search_line='.$search_line;
if ($search_desc)      				$param.='&search_desc='.urlencode($search_desc);


/* TO DO


if ($search_company)  		$param.='&search_company='.urlencode($search_company);
if ($search_ref_customer)	$param.='&search_ref_customer='.urlencode($search_ref_customer);
if ($search_user > 0) 		$param.='&search_user='.urlencode($search_user);
if ($search_sale > 0) 		$param.='&search_sale='.urlencode($search_sale);

if ($search_type_thirdparty != '')  $param.='&search_type_thirdparty='.urlencode($search_type_thirdparty);
if ($search_product_category != '') $param.='&search_product_category='.urlencode($search_product_category);
if ($search_categ_cus > 0)          $param.='&search_categ_cus='.urlencode($search_categ_cus);
if ($show_files)            $param.='&show_files=' .urlencode($show_files);
if ($viewstatut != '')      $param.='&viewstatut='.urlencode($viewstatut);
if ($billed != '')			$param.='&billed='.urlencode($billed);
*/
if ($optioncss != '')     $param.='&optioncss='.urlencode($optioncss);

/* throws up an error on line 11
// Add $param from extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_param.tpl.php';
 */

// List of mass actions available
$arrayofmassactions =  array(
	'presend'=>$langs->trans("SendByMail"),
	'builddoc'=>$langs->trans("PDFMerge"),
	'cancelorders'=>$langs->trans("Cancel"),
);

//if($user->rights->facture->creer) $arrayofmassactions['createbills']=$langs->trans("CreateInvoiceForThisCustomer");
if ($user->rights->commande->supprimer) $arrayofmassactions['predelete']=$langs->trans("Delete");
if (in_array($massaction, array('presend','predelete','createbills'))) $arrayofmassactions=array();
if ($user->rights->commande->supprimer) $arrayofmassactions['predelete']=$langs->trans("Delete");
if (in_array($massaction, array('presend','predelete','createbills'))) $arrayofmassactions=array();
$massactionbutton=$form->selectMassAction('', $arrayofmassactions);

/*
//if (! empty($conf->use_javascript_ajax))
//{
	include DOL_DOCUMENT_ROOT.'/core/tpl/ajaxrow.tpl.php';
//}
*/

// Lines of title fields
print '<form method="POST" id="searchFormList" action="'.$_SERVER["PHP_SELF"].'?id='.$id.'">';
if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
//print '<input type="hidden" name="action" value="list">';
if ($action == 'listall') print '<input type="hidden" name="action" value="listall">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="id" value="'.$id.'">';
print '<input type="hidden" name="contextpage" value="'.$contextpage.'">';
print '<input type="hidden" name="withproject" value="'.$withproject.'">';

// New Vehicle Order Button
$newcardbutton='';
if ($contextpage == 'orderlist' && $user->rights->commande->creer)
{
	$newcardbutton='<a class="butActionNew" href="'.DOL_URL_ROOT.'/commande/card.php?action=create"><span class="valignmiddle">'.$langs->trans('New Vehicle Order').'</span>';
	$newcardbutton.= '<span class="fa fa-plus-circle valignmiddle"></span>';
	$newcardbutton.= '</a>';
}

//print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'title_companies', 0, $newcardbutton, '', $limit);

print_barre_liste($title, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_products.png', 0, $newcardbutton, '', $limit);

// Show description of content
print '<div class="opacitymedium">';
print $contentdesc.'<br><br>';
print '</div>';

/*
// Add code for pre mass action (confirmation or email presend form)
$topicmail="SendOrdLinkRef";
$modelmail="ordlink";
$objecttmp=new OrdLink($db);
$trackid='xxxx'.$object->id;
include DOL_DOCUMENT_ROOT.'/core/tpl/massactions_pre.tpl.php';

if ($sall)
{
	foreach($fieldstosearchall as $key => $val) $fieldstosearchall[$key]=$langs->trans($val);
	print '<div class="divsearchfieldfilter">'.$langs->trans("FilterOnInto", $sall) . join(', ',$fieldstosearchall).'</div>';
}
*/
$moreforfilter = '';
/*
$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.= $langs->trans('MyFilter') . ': <input type="text" name="search_myfield" value="'.dol_escape_htmltag($search_myfield).'">';
$moreforfilter.= '</div>';
*/

$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object);    // Note that $action and $object may have been modified by hook
if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
else $moreforfilter = $hookmanager->resPrint;

/*
if (! empty($moreforfilter))
{
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}
*/
$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
$selectedfields.=(count($arrayofmassactions) ? $form->showCheckAddButtons('checkforselect', 1) : '');

print '<div class="div-table-responsive">';		// You can use div-table-responsive-no-min if you dont need reserved height for your table
print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";

/*
// Define usemargins
$usemargins=0;
if (! empty($conf->margin->enabled) && ! empty($element) && in_array($element,array('facture','propal','commande'))) $usemargins=1;

$num = count($lines);
*/

// Fields title search
// --------------------------------------------------------------------
print '<thead>';
print '<tr class="liste_titre_filter">';
//print '<tr class="liste_titre">';

/*
foreach($object->fields as $key => $val)
{
	$align='';
	if (in_array($val['type'], array('date','datetime','timestamp'))) $align.=($align?' ':'').'center';
	if (in_array($val['type'], array('timestamp'))) $align.=($align?' ':'').'nowrap';
	if ($key == 'status') $align.=($align?' ':'').'center';
	if (! empty($arrayfields['t.'.$key]['checked'])) print '<td class="liste_titre'.($align?' '.$align:'').'"><input type="text" class="flat maxwidth75" name="search_'.$key.'" value="'.dol_escape_htmltag($search[$key]).'"></td>';
}
*/

if ($action == 'listall')
{
	// Project
	if (! empty($arrayfields['p.ref']['checked']))
	{
		print '<td class="liste_titre nowrap">';
		print '<input type="text" class="flat" name="search_project_ref" value="'.$search_project_ref.'" size="5">&nbsp;Proj.ref.';
		// Project Title
		if (! empty($arrayfields['p.title']['checked']))
		{
			print '<br>';
			print '<input type="text" class="flat" name="search_project_title" value="'.$search_project_title.'" size="5">&nbsp;Proj.Title';
		}
		// Project Status
		if (! empty($arrayfields['p.fk_statut']['checked']))
		{
		//	print '<td class="liste_titre center">';
			print '<br>';
			$arrayofstatus = array();
			foreach($object->statuts_short as $key => $val) $arrayofstatus[$key]=$langs->trans($val);
			$arrayofstatus['99']=$langs->trans("NotClosed").' ('.$langs->trans('Draft').'+'.$langs->trans('Opened').')';
			if (! empty($arrayfields['p.title']['checked']))
			{
				print $form->selectarray('search_projectstatus', $arrayofstatus, $search_projectstatus, 1, 0, 0, '', 0, 0, 0, '', 'maxwidth150');
			}
			else
			{
				print $form->selectarray('search_projectstatus', $arrayofstatus, $search_projectstatus, 1, 0, 0, '', 0, 0, 0, '', 'maxwidth100');
			}
		//	print '</td>';
		}
	
		print '</td>';
	}
	// Customer
	if (! empty($arrayfields['s.nom']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input type="text" class="flat" name="search_societe" value="'.dol_escape_htmltag($search_societe).'" size="10">';
		print '</td>';
	}
}

// Part Line
	if (! empty($arrayfields['c.line']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="5" type="text" name="search_line" value="'.$search_line.'">';
		print '</td>';
	}

// Description
	if (! empty($arrayfields['c.desc']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="16" type="text" name="search_desc" value="'.$search_desc.'">';
		print '</td>';
	}

// Qty
	if (! empty($arrayfields['c.qty']['checked']))
	{
		print '<td class="liste_titre">';
		print '&nbsp;';
		print '</td>';
	}

// Unit
if (! empty($arrayfields['c.unit']['checked']))
	{
		print '<td class="liste_titre">';
		print '&nbsp;';
		print '</td>';
	}

// Ref
	if (! empty($arrayfields['c.ref']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input class="flat" size="10" type="text" name="search_ref" value="'.$search_ref.'">';
		print '</td>';
	}

// Date order
	if (! empty($arrayfields['c.date_commande']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="center">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_orderday" value="'.$search_orderday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_ordermonth" value="'.$search_ordermonth.'">';
		$formother->select_year($search_orderyear?$search_orderyear:-1,'search_orderyear',1, 20, 5);
		print '</td>';
	}
	if (! empty($arrayfields['c.date_delivery']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="center">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_deliveryday" value="'.$search_deliveryday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_deliverymonth" value="'.$search_deliverymonth.'">';
		$formother->select_year($search_deliveryyear?$search_deliveryyear:-1,'search_deliveryyear',1, 20, 5);
		print '</td>';
	}
// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

// Fields from hook
$parameters=array('arrayfields'=>$arrayfields);
$reshook=$hookmanager->executeHooks('printFieldListOption', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;


	// Ref
	if (! empty($arrayfields['cf.ref']['checked']))
	{
		print '<td class="liste_titre"><input size="8" type="text" class="flat" name="search_ref" value="'.$search_ref.'"></td>';
	}
	// Ref supplier
	if (! empty($arrayfields['cf.ref_supplier']['checked']))
	{
		print '<td class="liste_titre"><input type="text" class="flat" size="8" name="search_refsupp" value="'.$search_refsupp.'"></td>';
	}
	// Request author
	if (! empty($arrayfields['u.login']['checked']))
	{
		print '<td class="liste_titre">';
		print '<input type="text" class="flat" size="6" name="search_request_author" value="'.$search_request_author.'">';
		print '</td>';
	}
	// Thirpdarty
	if (! empty($arrayfields['cf.fk_soc']['checked']))
	{
		print '<td class="liste_titre"><input type="text" size="6" class="flat" name="search_company" value="'.$search_company.'"></td>';
	}
/*
	// Company type
	if (! empty($arrayfields['typent.code']['checked']))
	{
		print '<td class="liste_titre maxwidthonsmartphone" align="center">';
		print $form->selectarray("search_type_thirdparty", $formcompany->typent_array(0), $search_type_thirdparty, 0, 0, 0, '', 0, 0, 0, (empty($conf->global->SOCIETE_SORT_ON_TYPEENT)?'ASC':$conf->global->SOCIETE_SORT_ON_TYPEENT));
		print '</td>';
	}
*/
	// Date order
	if (! empty($arrayfields['cf.date_commande']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="center">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_orderday" value="'.$search_orderday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_ordermonth" value="'.$search_ordermonth.'">';
		$formother->select_year($search_orderyear?$search_orderyear:-1,'search_orderyear',1, 20, 5);
		print '</td>';
	}
	// Date delivery
	if (! empty($arrayfields['cf.date_delivery']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="center">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_deliveryday" value="'.$search_deliveryday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_deliverymonth" value="'.$search_deliverymonth.'">';
		$formother->select_year($search_deliveryyear?$search_deliveryyear:-1, 'search_deliveryyear', 1, 20, 5);
		print '</td>';
	}
	if (! empty($arrayfields['cf.total_ht']['checked']))
	{
		// Amount
		print '<td class="liste_titre" align="right">';
		print '<input class="flat" type="text" size="5" name="search_total_ht" value="'.$search_total_ht.'">';
		print '</td>';
	}
	if (! empty($arrayfields['cf.total_vat']['checked']))
	{
		// Amount
		print '<td class="liste_titre" align="right">';
		print '<input class="flat" type="text" size="5" name="search_total_vat" value="'.$search_total_vat.'">';
		print '</td>';
	}
	if (! empty($arrayfields['cf.total_ttc']['checked']))
	{
		// Amount
		print '<td class="liste_titre" align="right">';
		print '<input class="flat" type="text" size="5" name="search_total_ttc" value="'.$search_total_ttc.'">';
		print '</td>';
	}
	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_input.tpl.php';

	// Fields from hook
	$parameters=array('arrayfields'=>$arrayfields);
	$reshook=$hookmanager->executeHooks('printFieldListOption',$parameters);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	// Date creation
	if (! empty($arrayfields['cf.datec']['checked']))
	{
		print '<td class="liste_titre">';
		print '</td>';
	}
	// Date modification
	if (! empty($arrayfields['cf.tms']['checked']))
	{
		print '<td class="liste_titre">';
		print '</td>';
	}
	// Status
	if (! empty($arrayfields['cf.fk_statut']['checked']))
	{
		print '<td class="liste_titre" align="right">';
		$formorder->selectSupplierOrderStatus((strstr($search_status, ',')?-1:$search_status),1,'search_status');
		print '</td>';
	}
	// Status billed
	if (! empty($arrayfields['cf.billed']['checked']))
	{
		print '<td class="liste_titre" align="center">';
		print $form->selectyesno('search_billed', $search_billed, 1, 0, 1);
		print '</td>';
	}
	// Project ref
	if (! empty($arrayfields['p2.ref']['checked']))
	{
		print '<td class="liste_titre"><input type="text" class="flat" size="6" name="search_project_ref" value="'.$search_project_ref.'"></td>';
	}
// Action column
print '<td class="liste_titre" align="right">';
$searchpicto=$form->showFilterButtons();
print $searchpicto;
print '</td>';
print '</tr>'."\n";


// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre">';

/*
foreach($object->fields as $key => $val)
{
	$align='';
	if (in_array($val['type'], array('date','datetime','timestamp'))) $align.=($align?' ':'').'center';
	if (in_array($val['type'], array('timestamp'))) $align.=($align?' ':'').'nowrap';
	if ($key == 'status') $align.=($align?' ':'').'center';
	if (! empty($arrayfields['t.'.$key]['checked'])) print getTitleFieldOfList($arrayfields['t.'.$key]['label'], 0, $_SERVER['PHP_SELF'], 't.'.$key, '', $param, ($align?'class="'.$align.'"':''), $sortfield, $sortorder, $align.' ')."\n";
}
*/

if ($action == 'listall')
{
	// Project
	if (! empty($arrayfields['p.ref']['checked']))          print_liste_field_titre($arrayfields['p.ref']['label'],$_SERVER["PHP_SELF"],"p.ref","",$param,"",$sortfield,$sortorder);
	// Project Title
	/*	if (! empty($arrayfields['p.title']['checked']))        print_liste_field_titre($arrayfields['p.title']['label'],$_SERVER["PHP_SELF"],"p.title","",$param,"",$sortfield,$sortorder);*/
	// Customer
	if (! empty($arrayfields['s.nom']['checked']))          print_liste_field_titre($arrayfields['s.nom']['label'],$_SERVER["PHP_SELF"],"s.nom","",$param,"",$sortfield,$sortorder);
	// Project Status
	//if (! empty($arrayfields['p.fk_statut']['checked']))	print_liste_field_titre($arrayfields['p.fk_statut']['label'],$_SERVER["PHP_SELF"],"p.fk_statut","",$param,'align="center"',$sortfield,$sortorder);
}
// Lineid
if (! empty($arrayfields['c.line']['checked']))			print_liste_field_titre($arrayfields['c.line']['label'],$_SERVER["PHP_SELF"],'cd.rowid','',$param,'',$sortfield,$sortorder);
// Description
if (! empty($arrayfields['c.desc']['checked']))			print_liste_field_titre($arrayfields['c.desc']['label'],$_SERVER["PHP_SELF"],'cd.description','',$param,'',$sortfield,$sortorder);
// Qty
if (! empty($arrayfields['c.qty']['checked']))			print '<td class="linecolqty" align="right">'.$langs->trans('Qty').'</td>';
// Units
//if(! empty($arrayfields['c.unit']['checked']) && $conf->global->PRODUCT_USE_UNITS)
if(! empty($arrayfields['c.unit']['checked']))			print '<td class="linecoluseunit" align="left">'.$langs->trans('Units').'</td>';
// Order (Commande)
if (! empty($arrayfields['c.ref']['checked']))			print_liste_field_titre($arrayfields['c.ref']['label'],$_SERVER["PHP_SELF"],'c.ref','',$param,'',$sortfield,$sortorder);
/*
// Supplier Proposal
if ($element == 'supplier_proposal' || $element == 'order_supplier' || $element == 'invoice_supplier')
{
	print '<td class="linerefsupplier"><span id="title_fourn_ref">'.$langs->trans("SupplierRef").'</span></td>';
}
*/
// Order Date
if (! empty($arrayfields['c.date_commande']['checked']))  print_liste_field_titre($arrayfields['c.date_commande']['label'],$_SERVER["PHP_SELF"],'c.date_commande','',$param, 'align="center"',$sortfield,$sortorder);
// Wanted by Date
if (! empty($arrayfields['c.date_delivery']['checked']))  print_liste_field_titre($arrayfields['c.date_delivery']['label'],$_SERVER["PHP_SELF"],'c.date_livraison','',$param, 'align="center"',$sortfield,$sortorder);

// Order (Fourn - Purchase Order)
//if (! empty($arrayfields['cfd.ref']['checked']))			print_liste_field_titre($arrayfields['cfd.ref']['label'],$_SERVER["PHP_SELF"],'cfd.ref','',$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.ref']['checked']))            print_liste_field_titre($arrayfields['cf.ref']['label'],$_SERVER["PHP_SELF"],"cf.ref","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.ref_supplier']['checked']))   print_liste_field_titre($arrayfields['cf.ref_supplier']['label'],$_SERVER["PHP_SELF"],"cf.ref_supplier","",$param,'',$sortfield,$sortorder);
//	if (! empty($arrayfields['p.project_ref']['checked'])) 	   print_liste_field_titre($arrayfields['p.project_ref']['label'],$_SERVER["PHP_SELF"],"p.ref","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['u.login']['checked'])) 	       print_liste_field_titre($arrayfields['u.login']['label'],$_SERVER["PHP_SELF"],"u.login","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.fk_soc']['checked']))             print_liste_field_titre($arrayfields['cf.fk_soc']['label'],$_SERVER["PHP_SELF"],"cf.fk_soc","",$param,'',$sortfield,$sortorder);
//	if (! empty($arrayfields['typent.code']['checked']))       print_liste_field_titre($arrayfields['typent.code']['label'],$_SERVER["PHP_SELF"],"typent.code","",$param,'align="center"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.fk_author']['checked']))      print_liste_field_titre($arrayfields['cf.fk_author']['label'],$_SERVER["PHP_SELF"],"cf.fk_author","",$param,'',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.date_commande']['checked']))  print_liste_field_titre($arrayfields['cf.date_commande']['label'],$_SERVER["PHP_SELF"],"cf.date_commande","",$param,'align="center"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.date_delivery']['checked']))  print_liste_field_titre($arrayfields['cf.date_delivery']['label'],$_SERVER["PHP_SELF"],'cf.date_livraison','',$param, 'align="center"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.total_ht']['checked']))       print_liste_field_titre($arrayfields['cf.total_ht']['label'],$_SERVER["PHP_SELF"],"cf.total_ht","",$param,'align="right"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.total_vat']['checked']))      print_liste_field_titre($arrayfields['cf.total_vat']['label'],$_SERVER["PHP_SELF"],"cf.tva","",$param,'align="right"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.total_ttc']['checked']))      print_liste_field_titre($arrayfields['cf.total_ttc']['label'],$_SERVER["PHP_SELF"],"cf.total_ttc","",$param,'align="right"',$sortfield,$sortorder);
	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
	// Hook fields
	$parameters=array('arrayfields'=>$arrayfields,'param'=>$param,'sortfield'=>$sortfield,'sortorder'=>$sortorder);
	$reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
	if (! empty($arrayfields['cf.datec']['checked']))			print_liste_field_titre($arrayfields['cf.datec']['label'],$_SERVER["PHP_SELF"],"cf.date_creation","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.tms']['checked']))				print_liste_field_titre($arrayfields['cf.tms']['label'],$_SERVER["PHP_SELF"],"cf.tms","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.fk_statut']['checked']))		print_liste_field_titre($arrayfields['cf.fk_statut']['label'],$_SERVER["PHP_SELF"],"cf.fk_statut","",$param,'align="right"',$sortfield,$sortorder);
	if (! empty($arrayfields['cf.billed']['checked']))			print_liste_field_titre($arrayfields['cf.billed']['label'],$_SERVER["PHP_SELF"],'cf.billed','',$param,'align="center"',$sortfield,$sortorder,'');

	// Project on Purchase Order
	if (! empty($arrayfields['p2.ref']['checked']))				print_liste_field_titre($arrayfields['p2.ref']['label'],$_SERVER["PHP_SELF"],"p2.ref","",$param,"",$sortfield,$sortorder);

// Extra fields
include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_search_title.tpl.php';
// Hook fields
$parameters=array('arrayfields'=>$arrayfields,'param'=>$param,'sortfield'=>$sortfield,'sortorder'=>$sortorder);
$reshook=$hookmanager->executeHooks('printFieldListTitle', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"],'','','','align="center"',$sortfield,$sortorder,'maxwidthsearch ')."\n";
//print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="center" width="80"',$sortfield,$sortorder,'maxwidthsearch ');
//print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'',$param,'align="center"',$sortfield,$sortorder,'maxwidthsearch ');

// Detect if we need a fetch on each output line
$needToFetchEachLine=0;
if (is_array($extrafields->attributes[$object->table_element]['computed']) && count($extrafields->attributes[$object->table_element]['computed']) > 0)
{
	foreach ($extrafields->attributes[$object->table_element]['computed'] as $key => $val)
	{
		if (preg_match('/\$object/',$val)) $needToFetchEachLine++;  // There is at least one compute field that use $object
	}
}
print '</tr>'."\n";

print '</thead>';

// Loop on record
// --------------------------------------------------------------------
// Show object lines

print '<tbody>';

$i=0;
//$totalarray=array();
//while ($i < min($num, $limit))
foreach ($lines as $line)
{

/*
	$obj = $db->fetch_object($resql);
	if (empty($obj)) break;		// Should not happen

	// Store properties in $object
	$object->id = $obj->rowid;
	foreach($object->fields as $key => $val)
	{
		if (isset($obj->$key)) $object->$key = $obj->$key;
	}
*/
	//Line extrafield
	$line->fetch_optionals();

	$orderstatic = new Commande($db);
	$productstatic = new Product($db);	
	$projectstatic = new Project($db);
	$socstatic = new Societe($db);
	
	$orderstatic->fetch($line->fk_commande);
	if ($line->fk_product) $productstatic->fetch($line->fk_product);

	if ($orderstatic->fk_project) $projectstatic->fetch($orderstatic->fk_project); 
	if ($projectstatic->socid) $socstatic->fetch($projectstatic->socid);

	$purchaseorderfound = false;
	if ($line->cfd_fk_commande)
	{
		$purchaseorderfound = true;
		$purchaseorderstatic = new CommandeFournisseur($db);
		$purchaseorderstatic->fetch($line->cfd_fk_commande);
		if ($purchaseorderstatic->fk_project) 
		{
			$projectstatic2 = new Project($db);
			$projectstatic2->fetch($purchaseorderstatic->fk_project);
		}
		if ($purchaseorderstatic->fk_soc)
		{
			$thirdpartytmp = new Fournisseur($db);
			$thirdpartytmp->fetch($purchaseorderstatic->fk_soc);
		}
	}

	// Show here line of result
	print '<tr class="oddeven" id="row-'.$line->rowid.'">';
/*	foreach($object->fields as $key => $val)
	{
		$align='';
		if (in_array($val['type'], array('date','datetime','timestamp'))) $align.=($align?' ':'').'center';
		if (in_array($val['type'], array('timestamp'))) $align.=($align?' ':'').'nowrap';
		if ($key == 'status') $align.=($align?' ':'').'center';
		if (! empty($arrayfields['t.'.$key]['checked']))
		{
			print '<td';
			if ($align) print ' class="'.$align.'"';
			print '>';
			print $object->showOutputField($val, $key, $obj->$key, '');
			print '</td>';
			if (! empty($val['isameasure']))
			{
				if (! $i) $totalarray['pos'][$totalarray['nbfield']]='t.'.$key;
				$totalarray['val']['t.'.$key] += $obj->$key;
			}
		}
	}*/


	//if (is_object($hookmanager) && (($line->product_type == 9 && ! empty($line->special_code)) || ! empty($line->fk_parent_line)))
	if (is_object($hookmanager))   // Old code is commented on preceding line.
	{
		if (empty($line->fk_parent_line))
		{
			$parameters = array('line'=>$line,'var'=>$var,'num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'buyer'=>$buyer,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline);
			$reshook = $hookmanager->executeHooks('printObjectLine', $parameters, $orderstatic, $action);    // Note that $action and $object may have been modified by some hooks
		}
		else
		{
			$parameters = array('line'=>$line,'var'=>$var,'num'=>$num,'i'=>$i,'dateSelector'=>$dateSelector,'seller'=>$seller,'buyer'=>$buyer,'selected'=>$selected, 'extrafieldsline'=>$extrafieldsline, 'fk_parent_line'=>$line->fk_parent_line);
			$reshook = $hookmanager->executeHooks('printObjectSubLine', $parameters, $orderstatic, $action);    // Note that $action and $object may have been modified by some hooks
		}
	}
//		$object_rights = $getRights();

	$text=''; $description=''; $type=0;

	if ($action == 'listall')
	{
		// Project ref
		if (! empty($arrayfields['p.ref']['checked']))
		{
			print '<td class="nowrap">';
			if ($projectstatic->id)
			{
				print $projectstatic->getLibStatut(3).'&nbsp;';
				print $projectstatic->getNomUrl(1, '');
				if ($projectstatic->hasDelay()) print img_warning("Late");
	
				// Project title
				if (! empty($arrayfields['p.title']['checked']))
				{
					print '<br>';
					print '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<small>'.dol_trunc($projectstatic->title,80).'</small>';
				}
			}
			else
			{
				print '&nbsp;';
			}
			print '</td>';
		}
	
		// Third party
		if (! empty($arrayfields['s.nom']['checked']))
		{
			print '<td>';
			if ($socstatic->id)
			{
				print '<small>'.$socstatic->getNomUrl(1).'</small>';
			}
			else
			{
				print '&nbsp;';
			}
			print '</td>';
		}
	}
	// Line in view mode
	if(! empty($arrayfields['c.line']['checked'])) print '<td class="linecolnum" align="center">'.$line->rowid.'</td>';

	// Show product and description
	$type=(! empty($line->product_type)?$line->product_type:$line->fk_product_type);
	// Part description
	if(! empty($arrayfields['c.desc']['checked']))
	{
		print '<td class="linecoldescription minwidth200imp">';
		if ($type==1) $text = img_object($langs->trans('Service'),'service');
		else $text = img_object($langs->trans('Product'),'product');
		
		if (! empty($line->label)) {
			$text.= ' <strong>'.$line->label.'</strong>';
//				echo $form->textwithtooltip($text,dol_htmlentitiesbr($line->description),3,'','',$i,0,(!empty($line->fk_parent_line)?img_picto('', 'rightarrow'):''));
		} else {
//				if (! empty($line->fk_parent_line)) echo img_picto('', 'rightarrow');
			echo $text.' '.dol_htmlentitiesbr($line->description);
		}
		print '</td>';
	}

	// Quantity
	if(! empty($arrayfields['c.qty']['checked'])) print '<td align="right" class="linecolqty nowrap">'.$line->qty.'</td>';

	// Units
	//if(! empty($arrayfields['c.unit']['checked']) && $conf->global->PRODUCT_USE_UNITS) 	print '<td align="right" class="linecolunit nowrap">'.$line->fk_unit.'</td>';
	if(! empty($arrayfields['c.unit']['checked'])) 	print '<td align="right" class="linecolunit nowrap">'.$line->fk_unit.'</td>';

	// Ref
	if (! empty($arrayfields['c.ref']['checked']))
	{
/*		$sql = "SELECT c.rowid";
		$sql.= ", c.ref, c.ref_client, c.fk_statut";
		$sql.= ", c.date_valid, c.date_commande, c.note_private, c.date_livraison as date_delivery, c.facture as billed";
		$sql.= " FROM ".MAIN_DB_PREFIX."commande as c";
		$sql.= " WHERE c.rowid = ".$line->fk_commande;
	
		$resql=$db->query($sql);
		if ($resql)
		{
			$objc = $db->fetch_object($resql);
			$generic_commande->id=$objc->rowid;
			$generic_commande->ref=$objc->ref;
			$generic_commande->ref_client = $objc->ref_client;
			$generic_commande->statut = $objc->fk_statut;
			$generic_commande->date_commande = $db->jdate($objc->date_commande);
			$generic_commande->date_livraison = $db->jdate($objc->date_delivery);
	
			$db->free($resql);
		}
		else
		{
			dol_print_error($db);
		}
*/
		print '<td class="nowrap">';
		print '<table class="nobordernopadding"><tr class="nocellnopadd">';
		print '<td align="left" class="nowrap">'.$orderstatic->LibStatut($orderstatic->statut, $orderstatic->billed, 3, 1).'</td>';
		print '<td class="nobordernopadding nowrap">';
		$viewstatut = 2;
		print $orderstatic->getNomUrl(1, $orderstatic->statut, 0, 0, 0, 1);
		print '</td>';

		// Warning late icon and note
		print '<td class="nobordernopadding nowrap">';
		if ($orderstatic->hasDelay()) {
			print img_picto($langs->trans("Late").' : '.$orderstatic->showDelay(), "warning");
		}
		if (!empty($orderstatic->note_private) || !empty($orderstatic->note_public))
		{
			print ' <span class="note">';
			print '<a href="'.DOL_URL_ROOT.'/commande/note.php?id='.$orderstatic->id.'">'.img_picto($langs->trans("ViewPrivateNote"),'object_generic').'</a>';
			print '</span>';
		}
		print '</td>';

		print '<td width="16" align="right" class="nobordernopadding hideonsmartphone">';
		$filename=dol_sanitizeFileName($orderstatic->ref);
		$filedir=$conf->commande->dir_output . '/' . dol_sanitizeFileName($orderstatic->ref);
		$urlsource=$_SERVER['PHP_SELF'].'?id='.$orderstatic->id;
		print $formfile->getDocumentsLink($orderstatic->element, $filename, $filedir);
		print '</td>';
		print '</tr></table>';

		print '</td>';
	}
/*
	// Status
	if (! empty($arrayfields['c.fk_statut']['checked']))
	{
		print '<td align="left" class="nowrap">'.$generic_commande->LibStatut($objc->fk_statut, $objc->billed, 3, 1).'</td>';
	}
*/
	// Order date
	if (! empty($arrayfields['c.date_commande']['checked']))
	{
		print '<td align="center">';
		print dol_print_date($db->jdate($objc->date_commande), 'day');
		print '</td>';
	}
	// Plannned date of delivery
	if (! empty($arrayfields['c.date_delivery']['checked']))
	{
		print '<td align="center">';
		print dol_print_date($db->jdate($objc->date_delivery), 'day');
		print '</td>';
	}

	// Product
	if ($line->fk_product > 0)
	{
		$product_static = new Product($db);
		$product_static->fetch($line->fk_product);

		$product_static->ref = $line->ref; //can change ref in hook
		$product_static->label = $line->label; //can change label in hook
		$text=$product_static->getNomUrl(1);


		// Define output language and label
		if (! empty($conf->global->MAIN_MULTILANGS))
		{
			if (! is_object($thirdparty))
			{
				dol_print_error('','Error: Method printObjectLine was called on an object and object->fetch_thirdparty was not done before');
				return;
			}

			$prod = new Product($db);
			$prod->fetch($line->fk_product);

			$outputlangs = $langs;
			$newlang='';
			if (empty($newlang) && GETPOST('lang_id','aZ09')) $newlang=GETPOST('lang_id','aZ09');
			if (! empty($conf->global->PRODUIT_TEXTS_IN_THIRDPARTY_LANGUAGE) && empty($newlang)) $newlang=$this->thirdparty->default_lang;		// For language to language of customer
			if (! empty($newlang))
			{
				$outputlangs = new Translate("",$conf);
				$outputlangs->setDefaultLang($newlang);
			}

			$label = (! empty($prod->multilangs[$outputlangs->defaultlang]["label"])) ? $prod->multilangs[$outputlangs->defaultlang]["label"] : $line->product_label;
		}
		else
		{
			$label = $line->product_label;
		}

		$text.= ' - '.(! empty($line->label)?$line->label:$label);
		$description.=(! empty($conf->global->PRODUIT_DESC_IN_FORM)?'':dol_htmlentitiesbr($line->description));	// Description is what to show on popup. We shown nothing if already into desc.
	}

	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
	// Fields from hook
	$parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
	$reshook=$hookmanager->executeHooks('printFieldListValue', $parameters, $object);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;
/*
	// Date creation
	if (! empty($arrayfields['c.datec']['checked']))
	{
		print '<td align="center" class="nowrap">';
		print dol_print_date($db->jdate($line->date_creation), 'dayhour', 'tzuser');
		print '</td>';

	}
	// Date modification
	if (! empty($arrayfields['c.tms']['checked']))
	{
		print '<td align="center" class="nowrap">';
		print dol_print_date($db->jdate($line->date_update), 'dayhour', 'tzuser');
		print '</td>';

	}

	// Billed
	if (! empty($arrayfields['c.facture']['checked']))
	{
		print '<td align="center">'.yn($line->billed).'</td>';
	}
*/
/*
	// Existing LINKED!! Purchase Orders
	if (1 == 1)
	{
		//	Linked parts orders
		$linkedorders = array();
		$sql = 'SELECT ';
		$sql.= ' pol.rowid, pol.fk_object, pol.fk_commandedet';
		$sql.= ' FROM '.MAIN_DB_PREFIX.'parts_ordlink as pol';
//		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'parts_ordlink as pol ON (c.rowid = cd.fk_commande)';
//		$sql.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet as p ON (p.rowid = c.fk_projet)';
		$sql.= ' WHERE 1 = 1';
		$sql.= ' AND pol.fk_commandedet = '.$line->rowid;
//		$sql.= ' AND c.fk_projet IS NOT NULL';
	
		$sql.= " ORDER BY pol.fk_commandedet";
	
		$resql2 = $db->query($sql);
		if ($resql2)
		{
	
			$num = $db->num_rows($resql2);
			$totalnboflinks=$num;
	
			$i = 0;
			while ($i < $num)
			{

				$cfline = new CommandeFournisseurLigne($db);
				$cfline = $db->fetch_object($resql2);



				$i++;
			}
			$db->free($resql2);
		}
		else dol_print_error($db);
		if (count($linkedorders) == 0) $linkedorders[0]='0';	// To avoid sql syntax error if not found
	}
*/

	// Purchase Order Ref
	if (! empty($arrayfields['cf.ref']['checked']))
	{
		print '<td class="nowrap">';
		if ($purchaseorderfound)
		{
			print '<table class="nobordernopadding"><tr class="nocellnopadd">';
			print '<td align="left" class="nowrap">'.$purchaseorderstatic->LibStatut($purchaseorderstatic->statut, 3, $purchaseorderstatic->billed).'</td>';
			print '<td class="nobordernopadding nowrap">';
			$viewstatut = 2;
			print $purchaseorderstatic->getNomUrl(1, $purchaseorderstatic->statut, 0, 0, 0, 1);
			print '</td>';
	
			// Warning late icon and note
			print '<td class="nobordernopadding nowrap">';
			if ($purchaseorderstatic->hasDelay()) {
				print img_picto($langs->trans("Late").' : '.$purchaseorderstatic->showDelay(), "warning");
			}
			if (!empty($purchaseorderstatic->note_private) || !empty($orderstatic->note_public))
			{
				print ' <span class="note">';
				print '<a href="'.DOL_URL_ROOT.'/fourn/commande/note.php?id='.$purchaseorderstatic->id.'">'.img_picto($langs->trans("ViewPrivateNote"),'object_generic').'</a>';
				print '</span>';
			}
			print '</td>';
	
			print '<td width="16" align="right" class="nobordernopadding hideonsmartphone">';
			$filename=dol_sanitizeFileName($purchaseorderstatic->ref);
			$filedir=$conf->fournisseur->commande->dir_output . '/' . dol_sanitizeFileName($purchaseorderstatic->ref);
			$urlsource=$_SERVER['PHP_SELF'].'?id='.$purchaseorderstatic->id;
			print $formfile->getDocumentsLink($purchaseorderstatic->element, $filename, $filedir);
			print '</td>';
			print '</tr></table>';
		}
		else
		{
			print '&nbsp;';
		}
		print '</td>';
	}

	// Ref Supplier
	if (! empty($arrayfields['cf.ref_supplier']['checked']))
	{
		print '<td>'.$obj->ref_supplier.'</td>'."\n";

	}

	// Author
/*	$userstatic->id = $obj->fk_user_author;
	$userstatic->lastname = $obj->lastname;
	$userstatic->firstname = $obj->firstname;
	$userstatic->login = $obj->login;
	$userstatic->photo = $obj->photo;
	if (! empty($arrayfields['u.login']['checked']))
	{
		print "<td>";
		if ($userstatic->id) print $userstatic->getNomUrl(1);
		else print "&nbsp;";
		print "</td>";
		
	}
*/
	// Thirdparty
	if (! empty($arrayfields['cf.nom']['checked']))
	{
		print '<td>';
		if ($purchaseorderfound && $purchaseorderstatic->fk_soc)
		{
//			$thirdpartytmp->id = $obj->socid;
//			$thirdpartytmp->name = $obj->name;
			print $thirdpartytmp->getNomUrl(1,'supplier');
		}
		print '</td>'."\n";
		
	}

/*
	// Type ent
	if (! empty($arrayfields['typent.code']['checked']))
	{
		print '<td align="center">';
		if (count($typenArray)==0) $typenArray = $formcompany->typent_array(1);
		print $typenArray[$obj->typent_code];
		print '</td>';
		
	}
*/
	// Order date
	if (! empty($arrayfields['cf.date_commande']['checked']))
	{
		print '<td align="center">';
		if ($obj->date_commande) print dol_print_date($db->jdate($obj->date_commande), 'day');
		else print '';
		print '</td>';
		
	}
	// Plannned date of delivery
	if (! empty($arrayfields['cf.date_delivery']['checked']))
	{
		print '<td align="center">';
		print dol_print_date($db->jdate($obj->date_delivery), 'day');
/*		if ($objectstatic->hasDelay() && ! empty($objectstatic->date_delivery)) {
			print ' '.img_picto($langs->trans("Late").' : '.$objectstatic->showDelay(), "warning");
		}*/
		print '</td>';
		
	}
	// Amount HT
	if (! empty($arrayfields['cf.total_ht']['checked']))
	{
		  print '<td align="right">'.price($obj->total_ht)."</td>\n";
		  
		  if (! $i) $totalarray['totalhtfield']=$totalarray['nbfield'];
		  $totalarray['totalht'] += $obj->total_ht;
	}
	// Amount VAT
	if (! empty($arrayfields['cf.total_vat']['checked']))
	{
		print '<td align="right">'.price($obj->total_tva)."</td>\n";
		
		if (! $i) $totalarray['totalvatfield']=$totalarray['nbfield'];
		$totalarray['totalvat'] += $obj->total_tva;
	}
	// Amount TTC
	if (! empty($arrayfields['cf.total_ttc']['checked']))
	{
		print '<td align="right">'.price($obj->total_ttc)."</td>\n";
		
		if (! $i) $totalarray['totalttcfield']=$totalarray['nbfield'];
		$totalarray['totalttc'] += $obj->total_ttc;
	}

	// Extra fields
	include DOL_DOCUMENT_ROOT.'/core/tpl/extrafields_list_print_fields.tpl.php';
	// Fields from hook
	$parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
	$reshook=$hookmanager->executeHooks('printFieldListValue',$parameters);    // Note that $action and $object may have been modified by hook
	print $hookmanager->resPrint;

	// Date creation
	if (! empty($arrayfields['cf.datec']['checked']))
	{
		print '<td align="center" class="nowrap">';
		print dol_print_date($db->jdate($obj->date_creation), 'dayhour', 'tzuser');
		print '</td>';
		
	}
	// Date modification
	if (! empty($arrayfields['cf.tms']['checked']))
	{
		print '<td align="center" class="nowrap">';
		print dol_print_date($db->jdate($obj->date_update), 'dayhour', 'tzuser');
		print '</td>';
		
	}
	// Status
	if (! empty($arrayfields['cf.fk_statut']['checked']))
	{
		print '<td align="right" class="nowrap">';
		if ($purchaseorderfound)
		{
			print $purchaseorderstatic->LibStatut($purchaseorderstatic->statut, 5, $purchaseorderstatic->billed, 1);
		}
		else
		{
			print '&nbsp';
		}
		print '</td>';
	}

	// Billed
	if (! empty($arrayfields['cf.billed']['checked']))
	{
		print '<td align="center">'.yn($obj->billed).'</td>';
		
	}

	// Project in Purchase Order
	if (! empty($arrayfields['p2.ref']['checked']))
	{
		print '<td class="nowrap">';
		if ($purchaseorderfound && $projectstatic2->id)
		{
			print $projectstatic2->getLibStatut(3).'&nbsp;';
			print $projectstatic2->getNomUrl(1, '');
			if ($projectstatic2->id != $projectstatic->id)
			{
				print img_error("Project Numbers do not match");
			}
			else
			{
				print img_picto("Project Numbers match", 'tick.png');
			}
		}
		else
		{
			print '&nbsp;';
		}
		print '</td>';
	}

	// Action column
	print '<td class="nowrap" align="center">';
	if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
	{
		$selected=0;
		if (in_array($obj->rowid, $arrayofselected)) $selected=1;
//		print '<input id="cb'.$objc->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$objc->rowid.'"'.($selected?' checked="checked"':'').'>';
		print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected?' checked="checked"':'').'>';
	}
	print '</td>';
//	

	print '</tr>';

	$i++;
}  // End of $line loop

/*
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

*/
// If no record found
if ($num == 0)
{
	$colspan=1;
	foreach($arrayfields as $key => $val) { if (! empty($val['checked'])) $colspan++; }
	print '<tr><td colspan="'.$colspan.'" class="opacitymedium">'.$langs->trans("NoRecordFound").'</td></tr>';
}


$db->free($resql);

$parameters=array('arrayfields'=>$arrayfields, 'sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter', $parameters, $object);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print '</tbody>';

print '</table>'."\n";
print '</div>'."\n";

print '</form>'."\n";

if (in_array('builddoc',$arrayofmassactions) && ($nbtotalofrecords === '' || $nbtotalofrecords))
{
	$hidegeneratedfilelistifempty=1;
	if ($massaction == 'builddoc' || $action == 'remove_file' || $show_files) $hidegeneratedfilelistifempty=0;

	$formfile = new FormFile($db);

	// Show list of available documents
	$urlsource=$_SERVER['PHP_SELF'].'?sortfield='.$sortfield.'&sortorder='.$sortorder;
	$urlsource.=str_replace('&amp;','&',$param);

	$filedir=$diroutputmassaction;
	$genallowed=$user->rights->commande->lire;
	$delallowed=$user->rights->commande->creer;

	print $formfile->showdocuments('massfilesarea_orders','',$filedir,$urlsource,0,$delallowed,'',1,1,0,48,1,$param,$title,'','','',null,$hidegeneratedfilelistifempty);
}

// Enhance with select2
if ($conf->use_javascript_ajax)
{
	include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	$comboenhancement = ajax_combobox('.elementselect');
	$out.=$comboenhancement;

	print $comboenhancement;
}

// End of page
llxFooter();

/*
function _getSupplierOrderInfos($idsupplier, $projectid='')
{
	global $db,$conf;
	
	$sql = 'SELECT rowid, ref';
	$sql .= ' FROM ' . MAIN_DB_PREFIX . 'commande_fournisseur';
	$sql .= ' WHERE fk_soc = '.$idsupplier;
	$sql .= ' AND fk_statut = 0'; // 0 = DRAFT (Brouillon)
	
	if(!empty($conf->global->SOFO_DISTINCT_ORDER_BY_PROJECT) && !empty($projectid)){
		$sql .= ' AND fk_projet = '.$projectid;
	}
	
	$sql .= ' AND entity IN('.getEntity('commande_fournisseur').')';
	$sql .= ' ORDER BY rowid DESC';
	$sql .= ' LIMIT 1';
	
	$resql = $db->query($sql);
	
	if ($resql && $db->num_rows($resql) > 0) {
		//might need some value checks
		return $db->fetch_object($resql);
	}
	
	return false;
}
*/
$db->close();
