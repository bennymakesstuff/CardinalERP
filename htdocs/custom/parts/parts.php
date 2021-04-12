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
require_once DOL_DOCUMENT_ROOT.'/ticket/class/ticket.class.php';
dol_include_once('/parts/class/ordlink.class.php');

// Load translation files required by the page
$langs->loadLangs(array('projects', 'companies', 'suppliers', 'compta', 'orders'));
$langs->loadLangs(array("parts@parts","other"));

//$id=GETPOST('id','int')!=''?GETPOST('id','int'):274; // for testing - delete later
$id			= GETPOST('id','int');
$projectid=$id;	// For backward compatibility
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
$object->fetch($id);

//$diroutputmassaction=$conf->parts->dir_output . '/temp/massgeneration/'.$user->id;
//$hookmanager->initHooks(array('partslist'));     // Note that conf->hooks_modules contains array

/*
// Fetch optionals attributes and labels
$extrafields = new ExtraFields($db);
$extralabels = $extrafields->fetch_name_optionals_label('commande');	//Array ( [reqby] => Requested by [ticket] => Ticket )
//print_r($extralabels);
//print_r($extrafields);
$search_array_options=$extrafields->getOptionalsFromPost($extralabels,'','search_');
*/

// Default sort order (if not yet defined by previous GETPOST)
if ($action == 'listall')
{
	if (! $sortfield) $sortfield='cd_project_ref,c_rowid, cf_rowid, cfd_rowid';
	if (! $sortorder) $sortorder='ASC,ASC,ASC,ASC';
}
else
{
	if (! $sortfield) $sortfield='c_rowid, cf_rowid, cfd_rowid';
	if (! $sortorder) $sortorder='ASC,ASC,ASC';
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
$search_all=trim(GETPOST("search_all",'alphanohtml'));
$search=array();
/*
foreach($object->fields as $key => $val)
{
	if (GETPOST('search_'.$key,'alpha')) $search[$key]=GETPOST('search_'.$key,'alpha');
}
*/

if (! isset($_GET['search_projectstatus']) && ! isset($_POST['search_projectstatus']))
{
	if ($search_all != '') $search_projectstatus=-1;
	else $search_projectstatus=1;
}
else $search_projectstatus=GETPOST('search_projectstatus');
//$search_project=GETPOST('search_project');
$search_project_ref=GETPOST('search_project_ref', 'alpha');		// project ref
$search_project_title=GETPOST('search_project_title', 'alpha');	// project title
$search_thirdparty=GETPOST('search_thirdparty', 'alpha');		// customer
$search_company=GETPOST('search_company','alpha');				// vendor
$search_supp_ordno=GETPOST('search_supp_ordno');				// vendor order number
$search_supp_partno=GETPOST('search_supp_partno');				// vendor part number
$search_desc=GETPOST('search_desc','alpha');					// part description
$search_poref=GETPOST('search_poref','alpha');					// purchase order number
$search_prref=GETPOST('search_prref','alpha');					// purchase requisition number
$search_requester=GETPOST('search_requester','alpha');// requester
$search_status=GETPOST('search_status','alpha');				// alpha and not intbecause it can be '6,7'
$search_reqyear=GETPOST('search_reqyear','int');				// purchase requisition date
$search_reqmonth=GETPOST('search_reqmonth','int');				// purchase requisition date
$search_reqday=GETPOST('search_reqday','int');					// purchase requisition date
$search_orderyear=GETPOST('search_orderyear','int');			// purchase order date
$search_ordermonth=GETPOST('search_ordermonth','int');			// purchase order date
$search_orderday=GETPOST('search_orderday','int');				// purchase order date
$search_deliveryyear=GETPOST('search_deliveryyear','int');		// anticipated delivery date
$search_deliverymonth=GETPOST('search_deliverymonth','int');	// anticipated delivery date
$search_deliveryday=GETPOST('search_deliveryday','int');		// anticipated delivery date


/*
$search_product_category=GETPOST('search_product_category','int');
$search_line=GETPOST('search_line','int')!=''?GETPOST('search_line','int'):GETPOST('sline','int');
$search_ref_customer=GETPOST('search_ref_customer','alpha');
$search_user=GETPOST('search_user','int');
$search_sale=GETPOST('search_sale','int');
$search_btn=GETPOST('button_search','alpha');
$search_remove_btn=GETPOST('button_removefilter','alpha');
*/

/*
// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
	'c.ref'=>'Ref',
	'c.ref_client'=>'RefCustomerOrder',
	'pd.description'=>'Description',
	's.nom'=>"ThirdParty",
	'c.note_public'=>'NotePublic',
);
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
//	'p.ref'=>array('label'=>$langs->trans("Project ref."), 'checked'=>1),
//	'p.title'=>array('label'=>$langs->trans("ProjectLabel"), 'checked'=>0),
//	'p.fk_statut'=>array('label'=>$langs->trans("ProjectStatus"), 'checked'=>1),
//	's.nom'=>array('label'=>$langs->trans("Customer"), 'checked'=>0),
	'c.line'=>array('label'=>$langs->trans("Line"), 'checked'=>1),
	'c.desc'=>array('label'=>$langs->trans("Description"), 'checked'=>1),
	'c.qty'=>array('label'=>$langs->trans("Qty"), 'checked'=>1),
	'cf.fk_statut'=>array('label'=>$langs->trans("PO Status"), 'checked'=>1,),
//	'c.unit'=>array('label'=>$langs->trans("Units"), 'checked'=>1),
//	'c.unit'=>array('label'=>$langs->trans("Units"), 'checked'=>1,'enabled'=>!empty($conf->global->ORDER_DISABLE_DELIVERY_DATE)),
	'c.refs'=>array('label'=>$langs->trans("PR&PO References"), 'checked'=>1),
	'cf.req_vend'=>array('label'=>$langs->trans("Requester & Vendor"), 'checked'=>1),
	'cf.vendor_refs'=>array('label'=>$langs->trans("Vend. Ref."), 'checked'=>1),
	'c.date_commande'=>array('label'=>$langs->trans("Req. Date"), 'checked'=>1),
	'cf.date_commande'=>array('label'=>$langs->trans("Date Ordered"), 'checked'=>1),
	'cf.date_delivery'=>array('label'=>$langs->trans("Anticipated Delivery"), 'checked'=>1),	
		
//	'c.date_delivery'=>array('label'=>$langs->trans("Wanted Date"), 'checked'=>1),/* 'enabled'=>empty($conf->global->ORDER_DISABLE_DELIVERY_DATE)),*/

/*
	'c.ref_client'=>array('label'=>$langs->trans("RefCustomerOrder"), 'checked'=>1),
	'c.datec'=>array('label'=>$langs->trans("DateCreation"), 'checked'=>0, 'position'=>500),
	'c.tms'=>array('label'=>$langs->trans("DateModificationShort"), 'checked'=>0, 'position'=>500),
	'c.fk_statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1, 'position'=>1000),
	'c.facture'=>array('label'=>$langs->trans("Billed"), 'checked'=>1, 'position'=>1000, 'enabled'=>(empty($conf->global->WORKFLOW_BILL_ON_SHIPMENT)))
*/

//	'cf.ref'=>array('label'=>$langs->trans("Purchase Order"), 'checked'=>1),

//	'cf.fk_soc'=>array('label'=>$langs->trans("Supplier"), 'checked'=>1),

//	'cfd.ref'=>array('label'=>$langs->trans("Vend.Part#"), 'checked'=>1, 'enabled'=>1),


//	'cf.total_ht'=>array('label'=>$langs->trans("AmountHT"), 'checked'=>1),
//	'cf.total_vat'=>array('label'=>$langs->trans("AmountVAT"), 'checked'=>0),
//	'cf.total_ttc'=>array('label'=>$langs->trans("AmountTTC"), 'checked'=>0),

//	'cf.billed'=>array('label'=>$langs->trans("Billed"), 'checked'=>1, 'enabled'=>1),
//	'p2.ref'=>array('label'=>$langs->trans("PO Proj. Ref."), 'checked'=>1),
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

//$object->fields = dol_sort_array($object->fields, 'position');
$arrayfields = dol_sort_array($arrayfields, 'position');

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
/*		foreach($object->fields as $key => $val)
		{
			$search[$key]='';
		}*/
		$toselect='';
		$search_array_options=array();
		$search_all='';
//		$search_categ='';
//		$search_project='';
		$search_projectstatus=-1;
		$search_project_ref='';
		$search_project_title='';
		$search_thirdparty='';
		$search_company='';
		$search_supp_ordno='';
		$search_supp_partno='';
//		$search_user='';
//		$search_sale='';
//		$search_product_category='';
//		$search_line=0;
		$search_desc='';
		$search_poref='';
		$search_prref='';
		$search_requester='';
		$search_status='';
//		$search_ref_customer='';
		$search_reqyear='';
		$search_reqmonth='';
		$search_reqday='';
		$search_orderyear='';
		$search_ordermonth='';
		$search_orderday='';
		$search_deliveryday='';
		$search_deliverymonth='';
		$search_deliveryyear='';
//		$search_categ_cus=0;
//		$viewstatut='';
//		$billed='';
	}


if (empty($search_projectstatus) && $search_projectstatus == '') $search_projectstatus=1;

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

	$contentdesc = $langs->trans('This table presents all parts listed in Purchase Requisitions (PR) and Purchase Orders (PO) for all projects');
}
else  // Individual project
{
	$title = $langs->trans("Parts").' - '.$object->ref;
	llxHeader('',$title);

	$head=project_prepare_head($object);
	dol_fiche_head($head, 'parts', $langs->trans("Project"), -1, ($object->public?'projectpub':'project'));

	$contentdesc = $langs->trans('This table presents the parts listed in Purchase Requisitions (PR) and Purchase Orders (PO) for Project').' - '.$object->ref;
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
$sql1 = 'SELECT ';
$sql2 = 'SELECT ';
/*
foreach($object->fields as $key => $val)
{
	$sql.='t.'.$key.', ';
}
*/
$sql1.= '  cd.rowid AS cd_rowid';
$sql1.= ', cd.fk_commande AS cd_fk_commande';
$sql1.= ', cd.fk_parent_line AS cd_fk_parent_line';
$sql1.= ', cd.fk_product AS cd_fk_product';
$sql1.= ', cd.label AS cd_custom_label';
$sql1.= ', cd.description AS cd_description';
$sql1.= ', cd.qty AS cd_qty';
$sql1.= ', cd.product_type AS cd_product_type';
$sql1.= ', cd.fk_unit AS cd_fk_unit';

$sql2.= '  NULL AS cd_rowid';
$sql2.= ', NULL AS cd_fk_commande';
$sql2.= ', NULL AS cd_fk_parent_line';
$sql2.= ', NULL AS cd_fk_product';
$sql2.= ', NULL AS cd_custom_label';
$sql2.= ', NULL AS cd_description';
$sql2.= ', NULL AS cd_qty';
$sql2.= ', NULL AS cd_product_type';
$sql2.= ', NULL AS cd_fk_unit';

$sql1.= ', c.rowid AS c_rowid';
$sql1.= ', c.ref AS c_prref';
$sql1.= ', c.fk_projet AS c_fk_projet';
$sql1.= ', c.fk_user_author AS c_fk_user_author';	
$sql1.= ', c.date_commande AS c_date_commande';	

$sql2.= ', NULL AS c_rowid';
$sql2.= ', NULL AS c_prref';
$sql2.= ', NULL AS c_fk_projet';
$sql2.= ', NULL AS c_fk_user_author';
$sql2.= ', NULL AS c_date_commande';

$sql1.= ', u.firstname AS u_firstname';
$sql1.= ', u.lastname AS u_lastname';
$sql1.= ', u.photo AS u_photo';
$sql1.= ', u.login AS u_login';

$sql2.= ', NULL AS u_firstname';
$sql2.= ', NULL AS u_lastname';
$sql2.= ', NULL AS u_photo';
$sql2.= ', NULL AS u_login';

$sql1.= ', cef.reqby AS cef_requester';
$sql1.= ', cef.ticket AS cef_ticket';

$sql2.= ', NULL AS cef_requester';
$sql2.= ', NULL AS cef_ticket';

// TODO - look if this is needed or needs replicating for cfd
$sql1.= ', prd.ref AS cdp_product_ref';
$sql1.= ', prd.description AS cdp_product_desc';
$sql1.= ', prd.fk_product_type AS cdp_fk_product_type';
$sql1.= ', prd.label AS cdp_product_label';

$sql2.= ', NULL AS cdp_product_ref';
$sql2.= ', NULL AS cdp_product_desc';
$sql2.= ', NULL AS cdp_fk_product_type';
$sql2.= ', NULL AS cdp_product_label';

$sql12='';
$sql12.= ', p.rowid AS cd_projectid';
$sql12.= ', p.ref AS cd_project_ref';
$sql12.= ', p.title AS cd_project_title';
$sql12.= ', p.fk_soc AS cd_project_soc';
$sql12.= ', p.fk_statut AS cd_project_status';

$sql12.= ', s.rowid as socid';
$sql12.= ', s.nom as company_name';

$sql12.= ', cfd.rowid AS cfd_rowid';
$sql12.= ', cfd.fk_commande AS cfd_fk_commande';
//$sql12.= ', cfd.fk_parent_line AS cfd_fk_parent_line';
$sql12.= ', cfd.ref AS cfd_partno_ref';
$sql12.= ', cfd.label AS cfd_label';
$sql12.= ', cfd.description AS cfd_description';
$sql12.= ', cfd.qty AS cfd_qty';
$sql12.= ', cfd.fk_product AS cfd_fk_product';

$sql12.= ', cfd.product_type AS cfd_product_type';
$sql12.= ', cfd.fk_unit AS cfd_fk_unit';
$sql12.= ', cfd.total_ht AS cfd_total_ht';
$sql12.= ', cfd.total_tva AS cfd_total_tva';
$sql12.= ', cfd.total_ttc AS cfd_total_ttc';			

$sql12.= ', p2.rowid AS cfd_projectid';
$sql12.= ', p2.fk_soc AS cfd_project_soc';
$sql12.= ', p2.ref AS cfd_project_ref';
$sql12.= ', p2.fk_statut AS cfd_project_status';

$sql12.= ', cf.rowid AS cf_rowid';						//	rowid
$sql12.= ', cf.ref AS cf_poref';						//	ref	
$sql12.= ', cf.ref_supplier AS cf_ordno_ref';	//	ref_supplier
$sql12.= ', cf.fk_soc AS cf_fk_soc';					//	fk_soc
$sql12.= ', cf.fk_projet AS cf_fk_projet';				//	fk_projet

//	tms
//	date_creation
//	date_valid
//	date_approve
//	date_approve2
$sql12.= ', cf.date_commande AS cf_date_commande';		//	date_commande
$sql12.= ', cf.fk_user_author AS cf_fk_user_author';	//	fk_user_author
//	fk_user_modif
//	fk_user_valid
//	fk_user_approve
//	fk_user_approve2
//	source
$sql12.= ', cf.fk_statut AS cf_fk_statut';				//	fk_statut
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
$sql12.= ', cf.date_livraison AS cf_date_livraison';	//	date_livraison
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


$sql12.= ', s.rowid AS cust_socid';
$sql12.= ', s.nom AS customer_name';
$sql12.= ', s2.rowid AS vend_socid';
$sql12.= ', s2.nom AS vendor_name';

$sql12.= ', pol.rowid AS polrowid';	

$sql1.= $sql12;
$sql2.= $sql12;

$sql1.= ' FROM '.MAIN_DB_PREFIX.'commandedet as cd';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande AS c ON (c.rowid = cd.fk_commande)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'product AS prd ON (prd.rowid = cd.fk_product)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet AS p ON (p.rowid = c.fk_projet)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'parts_ordlink AS pol ON (pol.fk_commandedet = cd.rowid)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseurdet AS cfd ON (cfd.rowid = pol.fk_object)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur AS cf ON (cf.rowid = cfd.fk_commande)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet AS p2 ON (p2.rowid = cf.fk_projet)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe AS s ON (s.rowid = p.fk_soc)';	// customer
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe AS s2 ON (s2.rowid = cf.fk_soc)';	// vendor
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_extrafields AS cef ON (cef.fk_object = c.rowid)';
$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'user AS u ON (u.rowid = cef.reqby)';
//		$sql1.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur as cf ON (cf.rowid = cfd.fk_commande)';

$sql2.= ' FROM '.MAIN_DB_PREFIX.'parts_ordlink AS pol';
$sql2.= ' RIGHT JOIN '.MAIN_DB_PREFIX.'commande_fournisseurdet AS cfd ON (pol.fk_object = cfd.rowid)';
$sql2.= ' LEFT JOIN '.MAIN_DB_PREFIX.'commande_fournisseur AS cf ON (cf.rowid = cfd.fk_commande)';
$sql2.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet AS p ON (p.rowid = cf.fk_projet)';
$sql2.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet AS p2 ON (p2.rowid = cf.fk_projet)';
$sql2.= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as s ON (s.rowid = p2.fk_soc)';	// customer
$sql2.= ' LEFT JOIN '.MAIN_DB_PREFIX.'societe as s2 ON (s2.rowid = cf.fk_soc)';	// vendor
//	$sql2.= ' LEFT JOIN '.MAIN_DB_PREFIX.'user AS u ON (u.rowid = cf.fk_user_author)';

$sql1.= ' WHERE 1 = 1';
$sql2.= ' WHERE 1 = 1';

$sql1.= ' AND cd.product_type = 0';
$sql2.= ' AND cfd.product_type = 0';
$sql2.= ' AND pol.fk_object IS NULL'; // looking for non-linked Purchase Order items

if ($action != 'listall')
{
	$sql1.= ' AND c.fk_projet = '.$id;  // PJR TODO Add WHERE for socid too
	$sql2.= ' AND cf.fk_projet = '.$id;  // PJR TODO Add WHERE for socid too
}


$sql = 'SELECT * FROM (';
$sql.= $sql1;
$sql.= ' UNION ';
$sql.= $sql2;
$sql.= ') AS t WHERE 1 = 1';

//if ($sall) $sql .= natural_search(array_keys($fieldstosearchall), $sall);
//if ($search_all) $sql .= natural_search(array_keys($fieldstosearchall), $search_all);
if ($search_projectstatus >= 0)
{
	if ($search_projectstatus == 99) $sql .= " AND cd_project_status <> 2";
	else $sql .= " AND cd_project_status = ".$db->escape($search_projectstatus);
}

if ($search_project_ref)	$sql .= natural_search('cd_project_ref', $search_project_ref);
if ($search_project_title)	$sql .= natural_search('cd_project_title', $search_project_title);
if ($search_requester)		$sql .= natural_search(array('u_lastname','u_firstname','u_login'), $search_requester) ;
if ($search_thirdparty)		$sql .= natural_search('customer_name', $search_thirdparty);			// search customer name
if ($search_company) 		$sql .= natural_search('vendor_name', $search_company);					// search vendor name
if ($search_supp_ordno)		$sql.= natural_search('cf_ordno_ref', $search_supp_ordno);		// vendor order number
if ($search_supp_partno)	$sql.= natural_search('cfd_partno_ref', $search_supp_partno);	// vendor part number
//if ($search_desc)			$sql .= ' AND ('.natural_search('cd_description', $search_desc, 0, 1).' OR '.natural_search('cfd_description', $search_desc, 0, 1).')';	// seems reasonable to search both descriptions
if ($search_desc)			$sql .= natural_search(array('cd_description','cfd_description'), $search_desc); // seems reasonable to search both descriptions
if ($search_poref)			$sql .= natural_search('cf_poref', $search_poref);						// purchase order number
if ($search_prref)			$sql .= natural_search('c_prref', $search_prref);						// purchase requisition number

//Required triple check because statut=0 means draft filter
if (GETPOST('statut', 'intcomma') !== '')
{
	$sql .= " AND cf_fk_statut IN (".$db->escape($db->escape(GETPOST('statut', 'intcomma'))).")";
}
if ($search_status != '' && $search_status >= 0)
{
	$sql .= " AND cf_fk_statut IN (".$db->escape($search_status).")";
}

if ($search_reqmonth > 0)
{
	if ($search_reqyear > 0 && empty($search_reqday))
		$sql.= " AND c_date_commande BETWEEN '".$db->idate(dol_get_first_day($search_reqyear,$search_reqmonth,false))."' AND '".$db->idate(dol_get_last_day($search_reqyear,$search_reqmonth,false))."'";
	else if ($search_reqyear > 0 && ! empty($search_reqday))
		$sql.= " AND c_date_commande BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_eliverymonth, $search_reqday, $search_reqyear))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_reqmonth, $search_reqday, $search_reqyear))."'";
	else
		$sql.= " AND date_format(c_date_commande, '%m') = '".$db->escape($search_reqmonth)."'";
}
else if ($search_reqyear > 0)
{
	$sql.= " AND c_date_commande BETWEEN '".$db->idate(dol_get_first_day($search_reqyear,1,false))."' AND '".$db->idate(dol_get_last_day($search_reqyear,12,false))."'";
}

if ($search_ordermonth > 0)
{
	if ($search_orderyear > 0 && empty($search_orderday))
		$sql.= " AND cf_date_commande BETWEEN '".$db->idate(dol_get_first_day($search_orderyear,$search_ordermonth,false))."' AND '".$db->idate(dol_get_last_day($search_orderyear,$search_ordermonth,false))."'";
	else if ($search_orderyear > 0 && ! empty($search_orderday))
		$sql.= " AND cf_date_commande BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_ordermonth, $search_orderday, $search_orderyear))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_ordermonth, $search_orderday, $search_orderyear))."'";
	else
		$sql.= " AND date_format(cf_date_commande, '%m') = '".$db->escape($search_ordermonth)."'";
}
else if ($search_orderyear > 0)
{
	$sql.= " AND cf_date_commande BETWEEN '".$db->idate(dol_get_first_day($search_orderyear,1,false))."' AND '".$db->idate(dol_get_last_day($search_orderyear,12,false))."'";
}

if ($search_deliverymonth > 0)
{
	if ($search_deliveryyear > 0 && empty($search_deliveryday))
		$sql.= " AND cf_date_livraison BETWEEN '".$db->idate(dol_get_first_day($search_deliveryyear,$search_deliverymonth,false))."' AND '".$db->idate(dol_get_last_day($search_deliveryyear,$search_deliverymonth,false))."'";
	else if ($search_deliveryyear > 0 && ! empty($search_deliveryday))
		$sql.= " AND cf_date_livraison BETWEEN '".$db->idate(dol_mktime(0, 0, 0, $search_eliverymonth, $search_deliveryday, $search_deliveryyear))."' AND '".$db->idate(dol_mktime(23, 59, 59, $search_deliverymonth, $search_deliveryday, $search_deliveryyear))."'";
	else
		$sql.= " AND date_format(cf_date_livraison, '%m') = '".$db->escape($search_deliverymonth)."'";
}
else if ($search_deliveryyear > 0)
{
	$sql.= " AND cf_date_livraison BETWEEN '".$db->idate(dol_get_first_day($search_deliveryyear,1,false))."' AND '".$db->idate(dol_get_last_day($search_deliveryyear,12,false))."'";
}

//$sql.= ' ORDER BY cd_project_ref ASC, cd_rowid ASC, cfd_rowid ASC';
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
//exit;


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
	$line->rowid            = $objp->cd_rowid;			// cd.rowid AS cd_rowid
	$line->id               = $objp->cd_rowid;			// ???
	$line->fk_commande      = $objp->cd_fk_commande;	// cd.fk_commande AS cd_fk_commande		USED for $requisitionstatic
	$line->commande_id      = $objp->cd_fk_commande;
	$line->fk_parent_line   = $objp->cd_fk_parent_line;	// cd.fk_parent_line AS cd_fk_parent_line	
	$line->fk_product       = $objp->cd_fk_product;		// cd.fk_product AS cd_fk_product		USED for $productstatic
	$line->label            = $objp->custom_label;		// cd.label AS cd_custom_label
	$line->desc             = $objp->cd_description;	// cd.description AS cd_description
	$line->description      = $objp->cd_description;
	$line->qty              = $objp->cd_qty;			// cd.qty AS cd_qty
	$line->product_type     = $objp->cd_product_type;	// cd.product_type AS cd_product_type
	$line->fk_unit			= $objp->cd_fk_unit;		// cd.fk_unit AS cdfkunit

	// CD Product fields
	$line->ref				= $objp->cdp_product_ref;	// prd.ref AS cdp_product_ref
	$line->product_ref		= $objp->cdp_product_ref;
	$line->libelle			= $objp->cdp_product_label;	// prd.label AS cdp_product_label
	$line->product_label	= $objp->cdp_product_label;
	$line->product_desc     = $objp->cdp_product_desc;	// prd.description AS cdp_product_desc
	$line->fk_product_type  = $objp->fk_product_type;	// Product or service - prd.fk_product_type AS cdp_fk_product_type

		$line->cdprojectid          = $objp->cd_projectid;

//		$line->fetch_optionals();


	// Commandefourndet fields
	$line->cfd_rowid		= $objp->cfd_rowid;			// cd.rowid AS cd_rowid
//	$line->cfd_id			= $objp->cfd_rowid;			// ???
	$line->cfd_fk_commande	= $objp->cfd_fk_commande;	// cd.fk_commande AS cd_fk_commande	USED for $requisitionstatic
//	$line->commande_id		= $objp->cd_fk_commande;
//	$line->fk_parent_line	= $objp->cd_fk_parent_line;	// cd.fk_parent_line AS cd_fk_parent_line	
	$line->cfd_fk_product	= $objp->cfd_fk_product;	// cd.fk_product AS cd_fk_product	USED for $productstatic
	$line->cfd_partno_ref	= $objp->cfd_partno_ref;	// cfd.ref AS cfd_ref
//	$line->cfd_label		= $objp->cfd_label;			// cd.label AS cd_custom_label
	$line->cfd_desc			= $objp->cfd_description;	// cfd.description AS cfd_description
//	$line->description      = $objp->cd_description;
	$line->cfd_qty			= $objp->cfd_qty;			// cfd.qty AS cfd_qty
	$line->cfd_product_type	= $objp->cfd_product_type;	// cfd.product_type AS cfd_product_type
	$line->cfd_fk_unit		= $objp->cfd_fk_unit;		// cfd.fk_unit AS cfdfkunit
	$line->cfd_total_ht		= $objp->cfd_total_ht;		// cfd.total_ht AS cfd_total_ht
	$line->cfd_total_tva	= $objp->cfd_total_tva;		// cfd.total_tva AS cfd_total_tva
	$line->cfd_total_ttc	= $objp->cfd_total_ttc;		// cfd.total_ttc AS cfd_total_ttc

	// Extrafields
	$line->cef_requester	= $objp->cef_requester;		// Requisition requester
	$line->cef_ticket		= $objp->cef_ticket;		// Initiating ticket (if exists)

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
if ($action)						$param.='&action='.urlencode($action);
if ($id)							$param.='&id='.urlencode($id);
if ($projectid)						$param.='&projectid='.urlencode($projectid);
if ($withproject)					$param.='&withproject='.urlencode($withproject);
if ($socid > 0)             		$param.='&socid='.urlencode($socid);
if ($search_all != '') 				$param.='&search_all='.$search_all;
if ($search_projectstatus != '') 	$param.='&search_projectstatus='.$search_projectstatus;
if ($search_project_ref != '') 		$param.='&search_project_ref='.$search_project_ref;
if ($search_project_title != '')	$param.='&search_project_title='.$search_project_title;
if ($search_thirdparty != '') 		$param.='&search_thirdparty='.$search_thirdparty;
if ($search_company != '') 			$param.='&search_company='.$search_company;
if ($search_supp_ordno != '') 		$param.='&search_supp_ordno='.$search_supp_ordno;
if ($search_supp_partno != '') 		$param.='&search_supp_partno='.$search_supp_partno;
//if ($search_line)   	   			$param.='&search_line='.$search_line;
if ($search_desc)      				$param.='&search_desc='.urlencode($search_desc);
if ($search_poref)					$param.='&search_poref='.urlencode($search_poref);
if ($search_prref)					$param.='&search_prref='.urlencode($search_prref);
if ($search_requester)				$param.='&search_requester='.urlencode($search_requester);
if ($search_status)					$param.='&search_status='.urlencode($search_status);
if ($search_reqday)   				$param.='&search_reqday='.urlencode($search_reqday);
if ($search_reqmonth)				$param.='&search_reqmonth='.urlencode($search_reqmonth);
if ($search_reqyear)				$param.='&search_reqyear='.urlencode($search_reqyear);
if ($search_orderday)	      		$param.='&search_orderday='.urlencode($search_orderday);
if ($search_ordermonth) 	     	$param.='&search_ordermonth='.urlencode($search_ordermonth);
if ($search_orderyear)      	 	$param.='&search_orderyear='.urlencode($search_orderyear);
if ($search_deliveryday)   			$param.='&search_deliveryday='.urlencode($search_deliveryday);
if ($search_deliverymonth)			$param.='&search_deliverymonth='.urlencode($search_deliverymonth);
if ($search_deliveryyear)			$param.='&search_deliveryyear='.urlencode($search_deliveryyear);

//if ($search_ref)					$param.='&search_ref='.urlencode($search_ref);
//if ($search_label != '') 			$param.='&search_label='.$search_label;

/* TO DO
if ($show_files)            $param.='&show_files=' .urlencode($show_files);
if ($viewstatut != '')      $param.='&viewstatut='.urlencode($viewstatut);
if ($billed != '')			$param.='&billed='.urlencode($billed);
*/
if ($optioncss != '')     $param.='&optioncss='.urlencode($optioncss);

// List of mass actions available
$arrayofmassactions =  array(
	'presend'=>$langs->trans("SendByMail"),
	'builddoc'=>$langs->trans("PDFMerge"),
	'cancelorders'=>$langs->trans("Cancel"),
);

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

// New Purchase Requsition Button
$newcardbutton='';
if ($contextpage == 'orderlist' && $user->rights->commande->creer)
{
	$newcardbutton='<a class="butActionNew" href="'.DOL_URL_ROOT.'/commande/card.php?action=create"><span class="valignmiddle">'.$langs->trans('New Purchase Requisition').'</span>';
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

// Filter on categories
/*
if (! empty($conf->categorie->enabled))
{
	require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
	$moreforfilter.='<div class="divsearchfield">';
	$moreforfilter.=$langs->trans('ProjectCategories'). ': ';
	$moreforfilter.=$formother->select_categories('project', $search_categ, 'search_categ', 1, 1, 'maxwidth300');
	$moreforfilter.='</div>';
}*/

// Project Status
$arrayofstatus = array();
foreach($object->statuts_short as $key => $val) $arrayofstatus[$key]=$langs->trans($val);
$arrayofstatus['99']=$langs->trans("NotClosed").' ('.$langs->trans('Draft').'+'.$langs->trans('Opened').')';
$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.='<div class="inline-block hideonsmartphone">'.$langs->trans('Project Status'). ' &nbsp;</div>';
$moreforfilter.=$form->selectarray('search_projectstatus', $arrayofstatus, $search_projectstatus, 1, 0, 0, '', 0, 0, 0, '', 'maxwidth150');
$moreforfilter.='</div>';

// Project Ref
$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.='<div class="inline-block">';
$moreforfilter.=getTitleFieldOfList('Project Ref.',0,$_SERVER["PHP_SELF"],"cd_project_ref","",$param,$titlecolspan,$sortfield,$sortorder);
$moreforfilter.=' &nbsp;</div>';
$moreforfilter.='<input type="text" size="4" name="search_project_ref" class="marginleftonly" value="'.dol_escape_htmltag($search_project_ref).'">';
$moreforfilter.='</div>';

// Project Title
$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.='<div class="inline-block hideonsmartphone">';
$moreforfilter.=getTitleFieldOfList('Project Title',0,$_SERVER["PHP_SELF"],"cd_project_title","",$param,"",$sortfield,$sortorder);
$moreforfilter.=' &nbsp;</div>';
$moreforfilter.='<input type="text" size="4" name="search_project_title" class="marginleftonly" value="'.dol_escape_htmltag($search_project_title).'">';
$moreforfilter.='</div>';

// Customer
$moreforfilter.='<div class="divsearchfield">';
$moreforfilter.='<div class="inline-block">';
$moreforfilter.=getTitleFieldOfList('Customer',0,$_SERVER["PHP_SELF"],"cd_project_soc","",$param,"",$sortfield,$sortorder);
$moreforfilter.=' &nbsp;</div>';
$moreforfilter.='<input type="text" size="4" name="search_thirdparty" class="marginleftonly" value="'.dol_escape_htmltag($search_thirdparty).'">';
$moreforfilter.='</div>';

$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldPreListTitle', $parameters, $object);    // Note that $action and $object may have been modified by hook
if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
else $moreforfilter = $hookmanager->resPrint;

if (! empty($moreforfilter) && $action == 'listall')
{
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	print '</div>';
}

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
//print '<tr class="liste_titre_filter">';
print '<tr class="partsliste_slim">';
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

$colspan=0;
// Part Line
	if (! empty($arrayfields['c.line']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
	}

// Description
	if (! empty($arrayfields['c.desc']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
		$colspan++; // two columns used
		print '<td class="liste_titre">&nbsp</td>';
	}

// Qty & Unit
	if (! empty($arrayfields['c.qty']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
	}

	// Status
	if (! empty($arrayfields['cf.fk_statut']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
	}

// Purchase Requisition (Commande) Ref
	if (! empty($arrayfields['c.refs']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre right">';
		print 'PR:';
		print '</td>';
		$colspan++; // two columns used
		print '<td class="liste_titre">';
		print '<input class="flat" size="6" type="text" name="search_prref" value="'.$search_prref.'">';
		print '</td>';
	}

// Requester
	if (! empty($arrayfields['cf.req_vend']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre right">';
		print 'Requester:'; // adjacent search field title
		print '</td>';
		$colspan++; // two columns used
		print '<td class="liste_titre">';
		print '<input type="text" class="flat" size="6" name="search_requester" value="'.$search_requester.'">';
		print '</td>';
	}

// Vendor Order Ref
	if (! empty($arrayfields['cf.vendor_refs']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre"><input type="text" class="flat" size="6" name="search_supp_ordno" value="'.$search_supp_ordno.'"></td>';
	}

// Requisition Date
	if (! empty($arrayfields['c.date_commande']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
	}

	// Order Date
	if (! empty($arrayfields['cf.date_commande']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
	}
	// Anticipated Delivery Date
	if (! empty($arrayfields['cf.date_delivery']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';

	}
/*	// Amount
	if (! empty($arrayfields['cf.total_ht']['checked']))
	{
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
		$colspan++;
		print '<td class="liste_titre">&nbsp</td>';
		$colspan++; // three columns used
		print '<td class="liste_titre">&nbsp</td>';
	}
*/
	// Action column
	$colspan++;
	print '<td class="liste_titre">&nbsp</td>';
		
	print '</tr>'."\n";
//	print '<tr class="liste_titre_filter">';
	print '<tr class="partsliste_slim">';

	// Part Line
	if (! empty($arrayfields['c.line']['checked']))
	{
		print '<td class="liste_titre">&nbsp</td>';
	}

	// Description
	if (! empty($arrayfields['c.desc']['checked']))
	{
		print '<td class="liste_titre right">';
		print 'Desc:'; // adjacent search field title
		print '</td>';
		print '<td class="liste_titre">';
		print '<input class="flat" size="12" type="text" name="search_desc" value="'.$search_desc.'">';
		print '</td>';
	}

	// Qty & Unit
	if (! empty($arrayfields['c.qty']['checked']))
	{
		print '<td class="liste_titre">&nbsp</td>';
	}
	
	// Status
	if (! empty($arrayfields['cf.fk_statut']['checked']))
	{
		print '<td class="liste_titre" align="right">';
		$formorder->selectSupplierOrderStatus((strstr($search_status, ',')?-1:$search_status),1,'search_status');
		print '</td>';
	}

	// Purchase Order Ref
	if (! empty($arrayfields['c.refs']['checked']))
	{
		print '<td class="liste_titre right">';
		print 'PO:'; // adjacent search field title
		print '</td>';
		print '<td class="liste_titre">';
		print '<input class="flat" size="6" type="text" name="search_poref" value="'.$search_poref.'">';
		print '</td>';
	}

	// Vendor
	if (! empty($arrayfields['cf.req_vend']['checked']))
	{
		print '<td class="liste_titre right">';
		print 'Vendor:'; // adjacent search field title
		print '</td>';
		print '<td class="liste_titre"><input type="text" size="6" class="flat" name="search_company" value="'.$search_vendor.'"></td>';
	}

	// Vendor Line/Part Ref (e.g. Part Number)
	if (! empty($arrayfields['cf.vendor_refs']['checked']))
	{
		print '<td class="liste_titre"><input type="text" class="flat" size="6" name="search_supp_partno" value="'.$search_supp_partno.'"></td>';
	}

	// Requisition Date
	if (! empty($arrayfields['c.date_commande']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="center">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_reqday" value="'.$search_reqday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_reqmonth" value="'.$search_reqmonth.'">&nbsp;';
		$formother->select_year($search_reqyear?$search_reqyear:-1,'search_reqyear',1, 20, 5);
		print '</td>';
	}

	// Order Date
	if (! empty($arrayfields['cf.date_commande']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="center">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_orderday" value="'.$search_orderday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_ordermonth" value="'.$search_ordermonth.'">';
		$formother->select_year($search_orderyear?$search_orderyear:-1,'search_orderyear',1, 20, 5);
		print '</td>';
	}
	// Anticipated Delivery Date
	if (! empty($arrayfields['cf.date_delivery']['checked']))
	{
		print '<td class="liste_titre nowraponall" align="center">';
		if (! empty($conf->global->MAIN_LIST_FILTER_ON_DAY)) print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_deliveryday" value="'.$search_deliveryday.'">';
		print '<input class="flat width25 valignmiddle" type="text" maxlength="2" name="search_deliverymonth" value="'.$search_deliverymonth.'">';
		$formother->select_year($search_deliveryyear?$search_deliveryyear:-1, 'search_deliveryyear', 1, 20, 5);
		print '</td>';
	}
/*	if (! empty($arrayfields['cf.total_ht']['checked']))
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
*/
/*	// Status billed
	if (! empty($arrayfields['cf.billed']['checked']))
	{
		print '<td class="liste_titre" align="center">';
		print $form->selectyesno('search_billed', $search_billed, 1, 0, 1);
		print '</td>';
	}*/

// Action column
print '<td class="liste_titre" align="right">';
$searchpicto=$form->showFilterButtons();
print $searchpicto;
print '</td>';
print '</tr>'."\n";


// Fields title label
// --------------------------------------------------------------------
print '<tr class="liste_titre">';

// Lineid
if (! empty($arrayfields['c.line']['checked']))				print_liste_field_titre($arrayfields['c.line']['label'],$_SERVER["PHP_SELF"],'cd_rowid','',$param,'',$sortfield,$sortorder);

// Description
if (! empty($arrayfields['c.desc']['checked']))				print_liste_field_titre($arrayfields['c.desc']['label'],$_SERVER["PHP_SELF"],'cd_description','',$param,'colspan = "2"',$sortfield,$sortorder);

// Qty
if (! empty($arrayfields['c.qty']['checked']))				print '<td class="linecolnum right">'.$langs->trans('Qty').'</td>';

// Units
//if(! empty($arrayfields['c.unit']['checked']) && $conf->global->PRODUCT_USE_UNITS)
//if(! empty($arrayfields['c.unit']['checked']))			print '<td class="linecoluseunit" align="left">'.$langs->trans('Units').'</td>';

//Status
if (! empty($arrayfields['cf.fk_statut']['checked']))		print_liste_field_titre($arrayfields['cf.fk_statut']['label'],$_SERVER["PHP_SELF"],"cf_fk_statut","",$param,'align="center"',$sortfield,$sortorder);
//	if (! empty($arrayfields['cf.billed']['checked']))			print_liste_field_titre($arrayfields['cf.billed']['label'],$_SERVER["PHP_SELF"],'cf.billed','',$param,'align="center"',$sortfield,$sortorder,'');

// ===== Purchase Requisition and Purchase Order =====
if (! empty($arrayfields['c.refs']['checked']))
{
	print '<th class = "center" colspan = "2">';
	print '<table class="nobordernopadding" width = 100%><thead><tr class="nocellnopadd">';
	// Purchase Requisition Ref
	print_liste_field_titre($langs->trans("Purchase Req."),$_SERVER["PHP_SELF"],'c_prref','',$param,'align="center" colspan = "2"',$sortfield,$sortorder);
	print '</tr>';
	print '<tr>';
	// Purchase Order Ref
	print_liste_field_titre($langs->trans("Purchase Order"),$_SERVER["PHP_SELF"],"cf_poref",'',$param,'colspan = "2"',$sortfield,$sortorder);
	print '</tr></thead></table>';
	print '</th>';
}

// ===== Requester and Vendor =====
if (! empty($arrayfields['cf.req_vend']['checked']))
{
	print '<th class = "center" colspan = "2">';
	print '<table class="nobordernopadding" width = 100%><tr class="nocellnopadd">';
	// Requester
	print_liste_field_titre($langs->trans("Requester"),$_SERVER["PHP_SELF"],"cef_requester","",$param,'',$sortfield,$sortorder);
	print '</tr>';
	print '<tr>';
	// Vendor
	print_liste_field_titre($langs->trans("Supplier"),$_SERVER["PHP_SELF"],"cf_fk_soc","",$param,'',$sortfield,$sortorder);
	print '</tr></table>';
	print '</th>';
}

// ===== Vendor References =====
if (! empty($arrayfields['cf.vendor_refs']['checked']))
{
	print '<th class = "center">';
	print '<table class="nobordernopadding" width = 100%><tr class="nocellnopadd">';
	// Vendor Order Reference
	print_liste_field_titre($langs->trans("Vend. Ref."),$_SERVER["PHP_SELF"],"cf_ordno_ref","",$param,'',$sortfield,$sortorder);
	print '</tr>';
	print '<tr>';
	// Vendor Part Number
	print_liste_field_titre($langs->trans("Vend.Part#"),$_SERVER["PHP_SELF"],"cfd_partno_ref","",$param,'',$sortfield,$sortorder);
	print '</tr></table>';
	print '</th>';
}

/*
// Supplier Proposal
if ($element == 'supplier_proposal' || $element == 'order_supplier' || $element == 'invoice_supplier')
{
	print '<td class="linerefsupplier"><span id="title_fourn_ref">'.$langs->trans("SupplierRef").'</span></td>';
}
*/
// Requisition Date
if (! empty($arrayfields['c.date_commande']['checked']))	print_liste_field_titre($arrayfields['c.date_commande']['label'],$_SERVER["PHP_SELF"],'c_date_commande','',$param, 'align="center"',$sortfield,$sortorder);

// Wanted by Date
//if (! empty($arrayfields['c.date_delivery']['checked']))  print_liste_field_titre($arrayfields['c.date_delivery']['label'],$_SERVER["PHP_SELF"],'c.date_livraison','',$param, 'align="center"',$sortfield,$sortorder);

// Ordered Date
	if (! empty($arrayfields['cf.date_commande']['checked']))  print_liste_field_titre($arrayfields['cf.date_commande']['label'],$_SERVER["PHP_SELF"],"cf_date_commande","",$param,'align="center"',$sortfield,$sortorder);

// Anticpated Delivery Date
	if (! empty($arrayfields['cf.date_delivery']['checked']))  print_liste_field_titre($arrayfields['cf.date_delivery']['label'],$_SERVER["PHP_SELF"],'cf_date_livraison','',$param, 'align="center"',$sortfield,$sortorder);
/*
	if (! empty($arrayfields['cf.total_ht']['checked']))       print_liste_field_titre($arrayfields['cf.total_ht']['label'],$_SERVER["PHP_SELF"],"cf.total_ht","",$param,'align="right"',$sortfield,$sortorder);

	if (! empty($arrayfields['cf.total_vat']['checked']))      print_liste_field_titre($arrayfields['cf.total_vat']['label'],$_SERVER["PHP_SELF"],"cf.tva","",$param,'align="right"',$sortfield,$sortorder);

	if (! empty($arrayfields['cf.total_ttc']['checked']))      print_liste_field_titre($arrayfields['cf.total_ttc']['label'],$_SERVER["PHP_SELF"],"cf.total_ttc","",$param,'align="right"',$sortfield,$sortorder);

	// Project on Purchase Order
	if (! empty($arrayfields['p2.ref']['checked']))				print_liste_field_titre($arrayfields['p2.ref']['label'],$_SERVER["PHP_SELF"],"p2.ref","",$param,"",$sortfield,$sortorder);
*/

$parameters=array('arrayfields'=>$arrayfields,'param'=>$param,'sortfield'=>$sortfield,'sortorder'=>$sortorder);

print getTitleFieldOfList($selectedfields, 0, $_SERVER["PHP_SELF"],'','','','align="center"',$sortfield,$sortorder,'maxwidthsearch ')."\n";

print '</tr>'."\n";

print '</thead>';

// Loop on record
// --------------------------------------------------------------------

print '<tbody>';

$i=0;
$previousproj = -1;
//foreach($arrayfields as $key => $val) { if (! empty($val['checked'])) $colspan++; }
//print 'Number of columns = '.$colspan;
$titlecolspan = 'colspan = "'.$colspan.'"';

//=====================================================	
//while ($i < min($num, $limit))
foreach ($lines as $line)
{
	$requisitionstatic = new Commande($db);
	$productstatic = new Product($db);	
	$projectstatic = new Project($db);
	$socstatic = new Societe($db);
	
	if ($line->fk_commande) $requisitionstatic->fetch($line->fk_commande);
	if ($line->fk_product) $productstatic->fetch($line->fk_product);
	if ($line->cdprojectid) $projectstatic->fetch($line->cdprojectid); 
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
	}

	// Show here line of result

	$text=''; $description=''; $type=0;
	$currentproj = (! empty($projectstatic->id)?$projectstatic->id:0);

	// Project Title Line
	if ($action == 'listall' && $previousproj != $currentproj)
	{
		print '<tr class="trforpartsbreak">'."\n";
		print '<td class="nowrap" '.$titlecolspan.'>';  // PJR TODO

		if ($projectstatic->id)
		{
			$previousproj = $projectstatic->id;
			print $projectstatic->getLibStatut(3).'&nbsp;';
			print $projectstatic->getNomUrl(1, '');
			if ($projectstatic->hasDelay()) print img_warning("Late");
			if ($socstatic->id) print ' - '.$socstatic->getNomUrl(1);
			if ($projectstatic->title) print ' - '.$projectstatic->title;
		}
		else
		{
			$previousproj = 0;
			print img_warning("Orphans").'Orphan part(s) not belonging to a project';
		}
		print '</td>';
		print '</tr>';
	}

	print '<tr class="oddeven" id="row-'.$line->rowid.'">';

	// Line in view mode
	if(! empty($arrayfields['c.line']['checked'])) print '<td class="linecolnum" align="center">'.$line->rowid.'</td>';

	// Show product and description
	$type=(! empty($line->product_type)?$line->product_type:$line->fk_product_type);
	// Part description
	if(! empty($arrayfields['c.desc']['checked']))
	{
		print '<td class="linecoldescription minwidth150imp" colspan = "2">';
		if ($type==1) $text = img_object($langs->trans('Service'),'service');
		else $text = img_object($langs->trans('Product'),'product');
		
		if (! empty($line->cfd_desc))
		{
			$text.= ' <strong><small>PO: </small></strong>';
			print $text.' '.dol_htmlentitiesbr($line->cfd_desc);
		}
		elseif (! empty($line->description))
		{
			$text.= ' <strong><small>PR: </small></strong>';
			print $text.' '.dol_htmlentitiesbr($line->description);
		}
		else
		{
			print img_warning("No description").' No description';
		}
		print '</td>';
	}

	// Quantity
	if(! empty($arrayfields['c.qty']['checked']))
	{
		// following based on $line->getLabelOfUnit('')
		$label_type = 'short_label';
		$sqlu = 'SELECT '.$label_type.' FROM '.MAIN_DB_PREFIX.'c_units WHERE rowid='.$line->fk_unit;
		$resqlu = $db->query($sqlu);
		if($resqlu && $db->num_rows($resqlu) > 0)
		{
			$res = $db->fetch_array($resqlu);
			$label = ' '.$res[$label_type];
			$db->free($resqlu);
		}
		else
		{
			$label='';//XX--XX'.$sqlu;
		}
		print '<td align="right" class="linecolnum nowrap">'.$line->qty.$label.'</td>';
	}

	// Status
	if (! empty($arrayfields['cf.fk_statut']['checked']))
	{
		print '<td class="nowrap left">';
		if ($purchaseorderfound)
		{
			print $purchaseorderstatic->LibStatut($purchaseorderstatic->statut, 1, $purchaseorderstatic->billed, 1);
		}
		else
		{
			print '&nbsp';
		}
		print '</td>';
	}

	if (! empty($arrayfields['c.refs']['checked']))
	{
		// Purchase Requisition and Purchase Order
		print '<td colspan = "2">';
		print '<table class="nobordernopadding"><tr class="nocellnopadd">';
			// Purchase Requisition Ref
			print '<td class="nowrap">';
			if ($line->fk_commande)
			{
			print '<table class="nobordernopadding"><tr class="nocellnopadd">';
				print '<td align="left" class="nowrap">'.$requisitionstatic->LibStatut($requisitionstatic->statut, $requisitionstatic->billed, 3, 1).'</td>';
				print '<td class="nobordernopadding nowrap">';
//				$viewstatut = 2;
//				print $requisitionstatic->getNomUrl(1, $requisitionstatic->statut, 0, 0, 0, 1);
				print $requisitionstatic->getNomUrl(1, 0, 0, 0, 0, 1);
				print '</td>';
		
				// Warning late icon and note
				print '<td class="nobordernopadding nowrap">';
				if ($requisitionstatic->hasDelay()) {
					print img_picto($langs->trans("Late").' : '.$requisitionstatic->showDelay(), "warning");
				}
				if (!empty($requisitionstatic->note_private) || !empty($requisitionstatic->note_public))
				{
					print ' <span class="note">';
					print '<a href="'.DOL_URL_ROOT.'/commande/note.php?id='.$requisitionstatic->id.'">'.img_picto($langs->trans("ViewPrivateNote"),'object_generic').'</a>';
					print '</span>';
				}
				print '</td>';
		
				print '<td width="16" align="right" class="nobordernopadding hideonsmartphone">';
				$filename=dol_sanitizeFileName($requisitionstatic->ref);
				$filedir=$conf->commande->dir_output . '/' . dol_sanitizeFileName($requisitionstatic->ref);
				$urlsource=$_SERVER['PHP_SELF'].'?id='.$requisitionstatic->id;
				print $formfile->getDocumentsLink($requisitionstatic->element, $filename, $filedir);

				print '</td>';
			print '</tr></table>';
			}
			else
			{
				print img_error("No Purchase Requisition found").' No P.Req. found';
			}
			print '</td>';
	
			print '</tr>';
			print '<tr>';
	
		// Purchase Order Ref
			print '<td class="nowrap">';
			if ($purchaseorderfound)
			{
				print '<table class="nobordernopadding"><tr class="nocellnopadd">';
					print '<td align="left" class="nowrap">'.$purchaseorderstatic->LibStatut($purchaseorderstatic->statut, 3, $purchaseorderstatic->billed).'</td>';
					print '<td class="nobordernopadding nowrap">';
//					$viewstatut = 2;
					print $purchaseorderstatic->getNomUrl(1, $purchaseorderstatic->statut, 0, 0, 0, 1);
					print '</td>';
			
					// Warning late icon and note
					print '<td class="nobordernopadding nowrap">';
					if ($purchaseorderstatic->hasDelay()) {
						print img_picto($langs->trans("Late").' : '.$purchaseorderstatic->showDelay(), "warning");
					}
					if (!empty($purchaseorderstatic->note_private) || !empty($requisitionstatic->note_public))
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
						if ($projectstatic2->id != $projectstatic->id)
						{
							if ($projectstatic2->id) print $projectstatic2->getLibStatut(3).'&nbsp;'.$projectstatic2->getNomUrl(1, '');
							print ' '.img_error("Project Numbers do not match");
						}
						/*else
						{
							if ($projectstatic2->id) print $projectstatic2->getLibStatut(3).'&nbsp;'.$projectstatic2->getNomUrl(1, '');
							print ' '.img_picto("Project Numbers match", 'tick.png');
						}*/
					print '</td>';
				print '</tr></table>';
			}
			else
			{
				print '&nbsp;';
			}
			print '</td>';

		print '</tr></table>';
		print '</td>';
	}


	// Requester and Vendor
	if (! empty($arrayfields['cf.req_vend']['checked']))
	{
		print '<td class = "minwidth150imp" colspan = "2">';
		print '<table class="nobordernopadding"><tr class="nocellnopadd">';
			// Requester
			print '<td>';
			if ($line->cef_requester)
			{
				$userstatic = new User($db);
				$userstatic->fetch($line->cef_requester);
				print $userstatic->getNomUrl(1);
			}
			if ($line->cef_ticket)
			{
				$ticketstatic = new Ticket($db);
				$ticketstatic->fetch($line->cef_ticket);
				print ' '.$ticketstatic->getNomUrl(2);
			}
			print "</td>";
	
			print '</tr>';
			print '<tr>';
		
		// Vendor
			print '<td>';
			if ($purchaseorderfound && $purchaseorderstatic->socid)
			{
				$thirdpartytmp = new Fournisseur($db);
				$thirdpartytmp->fetch($purchaseorderstatic->socid);
				print $thirdpartytmp->getNomUrl(1,'supplier');
			}
			print '</td>'."\n";
		print '</tr></table>';

		print '</td>';
	}


	// Vendor References
	if (! empty($arrayfields['cf.vendor_refs']['checked']))
	{
		print '<td class = "minwidth150imp" colspan = "1">';
		print '<table class="nobordernopadding"><tr class="nocellnopadd">';
			// Vendor Order No.
			print '<td>';
			if ($purchaseorderfound && $purchaseorderstatic->ref_supplier)
			{
				print $purchaseorderstatic->ref_supplier;
			}
			print "</td>";
	
			print '</tr>';
			print '<tr>';

			// Vendor Part No.
			print '<td>'.$line->cfd_partno_ref.'</td>'."\n";
		print '</tr></table>';

		print '</td>';
	}

	// Order date
	if (! empty($arrayfields['c.date_commande']['checked']))
	{
		print '<td align="center">';
		print dol_print_date($requisitionstatic->date_commande, 'day');
		print '</td>';
	}
/*		// Plannned date of delivery
	if (! empty($arrayfields['c.date_delivery']['checked']))
	{
		print '<td align="center">';
		print dol_print_date($requisitionstatic->date_delivery, 'day');
		print '</td>';
	}
*/
	// Order date
	if (! empty($arrayfields['cf.date_commande']['checked']))
	{
		print '<td align="center">';
		if ($purchaseorderfound && $purchaseorderstatic->date_commande)
		{
			print dol_print_date($purchaseorderstatic->date_commande, 'day');
		}
		else
		{
			print '';
		}
		print '</td>';
	}
	// Plannned date of delivery
	if (! empty($arrayfields['cf.date_delivery']['checked']))
	{
		print '<td align="center">';
		if ($purchaseorderfound && $purchaseorderstatic->date_livraison)
		{
			print dol_print_date($purchaseorderstatic->date_livraison, 'day');
			if ($purchaseorderstatic->hasDelay() && ! empty($purchaseorderstatic->date_livraison)) {
				print ' '.img_picto($langs->trans("Late").' : '.$purchaseorderstatic->showDelay(), "warning");
			}
		}
		else
		{
			print '';
		}
		print '</td>';
	}
/*
	// Amount HT
	if (! empty($arrayfields['cf.total_ht']['checked']))
	{
		print '<td align="right">';
		if ($purchaseorderfound)
		{
			print price($line->cfd_total_ht);
		}
		else
		{
			print '';
		}
		print '</td>';
	}
	// Amount VAT
	if (! empty($arrayfields['cf.total_vat']['checked']))
	{
		print '<td align="right">';
		if ($purchaseorderfound)
		{
			print price($line->cfd_total_tva);
		}
		else
		{
			print '';
		}
		print '</td>';
	}
	// Amount TTC
	if (! empty($arrayfields['cf.total_ttc']['checked']))
	{
		print '<td align="right">';
		if ($purchaseorderfound)
		{
			print price($line->cfd_total_ttc);
		}
		else
		{
			print '';
		}
		print '</td>';
	}
*/

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

	print '</tr>';

	$i++;
}  // End of $line loop

//=====================================================		

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
