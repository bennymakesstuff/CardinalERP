<?php
/* Copyright (C) 2011-2018 Regis Houssin	<regis.houssin@capnetworks.com>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *	\file       /dcloud/document.php
 *	\ingroup    d-cloud
 *	\brief      D-Cloud tab for thirparty card
 */

if (! defined('JS_JQUERY_MIGRATE_DISABLED')) define('JS_JQUERY_MIGRATE_DISABLED','1'); // since dolibarr 6

// TODO upgrade fileupload
if (! defined('JS_JQUERY')) define('JS_JQUERY','./inc/jquery/');
if (! defined('JS_JQUERY_UI')) define('JS_JQUERY_UI','./inc/jquery/');


$res=@include("../main.inc.php");									// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../main.inc.php");					// For "custom" directory

dol_include_once('/dcloud/lib/dcloud.lib.php');
include_once dirname(__FILE__) . '/lib/dropbox.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

// Load traductions files
$langs->load("ecm");
$langs->load("companies");
$langs->load("other");
$langs->load("errors");
$langs->load('dcloud@dcloud');

// Get parameters
$id = (GETPOST('socid') ? GETPOST('socid') : GETPOST('id'));
$ref = GETPOST('ref', 'alpha');
$element = (GETPOST('element') ? GETPOST('element') : 'dcloud');
$feature = $element;
$shared = '';

// External user check
if ($user->societe_id) {
	$id=$user->societe_id;
	$element = 'thirdparty';
}

if (!empty($id) || !empty($ref))
{
	if ($element == 'thirdparty')
	{
		require_once DOL_DOCUMENT_ROOT."/core/lib/company.lib.php";

		$feature = 'societe';
		$shared = '&societe'; // For multicompany

		$object = new Societe($db);
		$ret = $object->fetch($id, $ref);

		$type = GETPOST('type','alpha') ? GETPOST('type','alpha') : ( empty($object->client) && !empty($object->fournisseur) ? 'supplier' : 'customer');
	}
	else if ($element == 'product')
	{
		require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
		require_once DOL_DOCUMENT_ROOT."/core/lib/product.lib.php";

		$feature = 'produit|service';
		$shared = 'product&product'; // For multicompany

		$object = new Product($db);
		$ret = $object->fetch($id, $ref);
	}
}


// Security check
$result = restrictedArea($user, $feature, $id, $shared);


$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $conf->liste_limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="label";

if (!empty($conf->global->DROPBOX_ACCESS_TOKEN) && !is_connected())
	setEventMessage($langs->trans("ErrorDropboxConnectionIsOut"), 'errors');


/*
 * Actions
 */



/*
 * View
 */

if (checkDCloudVersion())
{
	//print "xx".$_SESSION["dol_screenheight"];
	$maxheightwin=(isset($_SESSION["dol_screenheight"]) && $_SESSION["dol_screenheight"] > 500)?($_SESSION["dol_screenheight"]-166):660;

	$moreheadcss='';
	$moreheadjs='';

	$arrayofjs=array(
		'/dcloud/inc/jstree/jquery.jstree.min.js',
		'/dcloud/inc/jstree/jquery.cookie.js',
		'/dcloud/inc/jstree/jquery.hotkeys.js',
		'/dcloud/inc/layout/jquery.layout.min.js',
		'includes/jquery/plugins/blockUI/jquery.blockUI.js',
		'core/js/blockUI.js'
	);

	$moreheadcss.="
<!-- dol_screenheight=".$_SESSION["dol_screenheight"]." -->
<style type=\"text/css\">
    #containerlayout {
        height:     ".$maxheightwin."px;
        margin:     0 auto;
        width:      100%;
        min-width:  700px;
        _width:     700px; /* min-width for IE6 */
    }
    .toolbar {
    	height: 28px !important;
    	overflow: hidden !important;
    }
    .fileupload-buttonbar {
    	padding-top: 2px;
    }
</style>";
	$moreheadjs.="
<script type=\"text/javascript\">
    $(document).ready(function () {
        $('#containerlayout').layout({
        	name: \"ecmlayout\"
        ,   center__paneSelector:   \"#ecm-layout-center\"
        ,   north__paneSelector:    \"#ecm-layout-north\"
        ,   west__paneSelector:     \"#ecm-layout-west\"
        ,   resizable: true
        ,   north__size:        34
        ,   north__resizable:   false
        ,   north__closable:    false
        ,   west__size:         340
        ,   west__minSize:      280
        ,   west__slidable:     true
        ,   west__resizable:    true
        ,   west__togglerLength_closed: '100%'
        ,   useStateCookie:     true
            });

        $('#ecm-layout-center').layout({
            center__paneSelector:   \".ecm-in-layout-center\"
        //,   south__paneSelector:    \".ecm-in-layout-south\"
        ,   resizable: false
        ,   south__minSize:      32
        ,   south__resizable:   false
        ,   south__closable:    false
            });
    });
</script>";

	$moreheadjs.='<script type="text/javascript">'."\n";
	$moreheadjs.='var indicatorBlockUI = \''.DOL_URL_ROOT."/theme/".$conf->theme."/img/working.gif".'\';'."\n";
	$moreheadjs.='</script>'."\n";

	llxHeader($moreheadcss.$moreheadjs,$langs->trans("Dropbox"),'','','','',$arrayofjs);

	if (!empty($object->id))
	{
		$form=new Form($db);

		if (($element == 'thirdparty' && $type == 'customer' && !empty($object->client) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT))
		|| ($element == 'thirdparty' && $type == 'supplier' && !empty($object->fournisseur) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT)))
		{
			$objectname = dol_replace_invalid_char($object->name);
			if ($type == 'customer')
			{
				$_SESSION['dropbox_root'] = dol_jstree_replace($conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.$objectname);
			}
			elseif ($type == 'supplier')
			{
				$_SESSION['dropbox_root'] = dol_jstree_replace($conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.$objectname);
			}

			$_SESSION['dropbox_root_label'] = $object->name;
			$_SESSION['dropbox_root_icon'] = 'thirdparty';

			$head = societe_prepare_head($object);
			dol_fiche_head($head, 'dropbox', $langs->trans("ThirdParty"),0,'company');

			$linkback = '<a href="'.DOL_URL_ROOT.'/societe/list.php">'.$langs->trans("BackToList").'</a>';

			dol_banner_tab($object, 'socid', $linkback, ($user->societe_id?0:1), 'rowid', 'nom');

			print '<div class="underbanner clearboth"></div>';

		}
		else if ($element == 'product')
		{
			if ((!$object->isservice() && !empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT))
			|| ($object->isservice() && !empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT)))
			{
				$_SESSION['dropbox_root'] = dol_jstree_replace($conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.($object->isservice() ? $conf->global->DROPBOX_MAIN_SERVICE_ROOT : $conf->global->DROPBOX_MAIN_PRODUCT_ROOT).'/'.dol_replace_invalid_char($object->ref));
				$_SESSION['dropbox_root_label'] = $object->ref;
				$_SESSION['dropbox_root_icon'] = ($object->isservice() ? 'service' : 'product');

				$head = product_prepare_head($object, $user);
				$titre=$langs->trans("CardProduct".$object->type);
				$picto=($object->isservice() ? 'service' : 'product');
				dol_fiche_head($head, 'dropbox', $titre, 0, $picto);

				$linkback = '<a href="'.DOL_URL_ROOT.'/product/list.php?type='.$object->type.'">'.$langs->trans("BackToList").'</a>';
				$object->next_prev_filter=" fk_product_type = ".$object->type;

				$shownav = 1;
				if ($user->societe_id && ! in_array('product', explode(',',$conf->global->MAIN_MODULES_FOR_EXTERNAL))) $shownav=0;

				dol_banner_tab($object, 'ref', $linkback, $shownav, 'ref');

				print '<div class="underbanner clearboth"></div>';

			}
		}
	}
	else
	{
		$_SESSION['dropbox_root'] = dol_jstree_replace($conf->global->DROPBOX_MAIN_ROOT);
		$_SESSION['dropbox_root_label'] = 'Dropbox';
		$_SESSION['dropbox_root_icon'] = 'drive';

		print_fiche_titre(' - '.$langs->trans("FileManager"),'','dropbox_logo@dcloud');
		print '<br />';

		//dol_include_once('/dcloud/tpl/ajaxdropboxquota.tpl.php');
	}


	print '<div id="containerlayout"> <!-- begin div id="containerlayout" -->';
	print '<div id="ecm-layout-north" class="toolbar">';

	// Fileupload form
	dol_include_once('/dcloud/tpl/fileupload_form.tpl.php');

	print '</div><!-- end div ecm-layout-north -->';

	// Left area
	print '<div id="ecm-layout-west" class="hidden">';

	print '<table width="100%" class="nobordernopadding">';

	print '<tr class="liste_titre">';
	print '<td class="liste_titre" align="left" colspan="6">';
	print '&nbsp;'.$langs->trans("ECMSections");
	print '</td></tr>';

	print "</table>";

	// Tree container
	dol_include_once('/dcloud/tpl/ajaxdropboxtree.tpl.php');
	//print '<div id="dropbox_tree"></div>';

	print '</div><!-- end div ecm-layout-west -->';

	print '<div id="ecm-layout-center" class="hidden">';
	print '<div class="pane-in ecm-in-layout-center">';

	// Fileupload instance
	dol_include_once('/dcloud/tpl/fileupload_main.tpl.php');

	print '<table width="100%" class="nobordernopadding">';
	print '<tr class="liste_titre">';
	//print_liste_field_titre($langs->trans("Documents2"),'','','','','align="left"',$sortfield,$sortorder);
	//print_liste_field_titre($langs->trans("Preview"),'','','','','align="center"',$sortfield,$sortorder);
	//print_liste_field_titre($langs->trans("Size"),'','','','','align="right"',$sortfield,$sortorder);
	//print_liste_field_titre($langs->trans("Date"),'','','','','align="center"',$sortfield,$sortorder);
	print_liste_field_titre('&nbsp;','','');
	print_liste_field_titre('&nbsp;','','');
	print '</tr>';
	print '</table>';

	// Fileupload files list
	print '<table role="presentation" class="table table-striped" width="100%"><tbody id="fileupload-view" class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table>';
	// Fileupload template
	dol_include_once('/dcloud/tpl/fileupload_view.tpl.php');

	print '</div><!-- end div ecm-in-layout-center -->';
	print '</div><!-- end div ecm-layout-center -->';
	print '</div> <!-- end div id="containerlayout" -->';
}
else
{
	llxHeader('',$langs->trans("Dropbox"));

	if (!empty($id) || !empty($ref))
	{
		$form=new Form($db);

		if ($element == 'thirdparty') {

			$object = new Societe($db);

			$ret = $object->fetch($id);

			if ($ret > 0)
			{
				$head = societe_prepare_head($object);
				dol_fiche_head($head, 'dropbox', $langs->trans("ThirdParty"),0,'company');

				print '<table class="border"width="100%">';

				// Ref
				print '<tr><td width="30%">'.$langs->trans("ThirdPartyName").'</td>';
				print '<td colspan="3">';
				print $form->showrefnav($object,'socid','',($user->societe_id?0:1),'rowid','nom');
				print '</td></tr>';

				print '</table>';

				dol_htmloutput_mesg($langs->trans("DropboxUpgradeIsNeeded"),'','error',1);

				print "<br>\n";
			}
		} else if ($element == 'product') {

			$object = new Product($db);

			$ret = $object->fetch($id, $ref);

			if ($ret > 0)
			{
				$head = product_prepare_head($object, $user);
				$titre=$langs->trans("CardProduct".$object->type);
				$picto=($object->type==1?'service':'product');
				dol_fiche_head($head, 'dropbox', $titre, 0, $picto);

				print '<table class="border"width="100%">';

				// Ref
				print '<tr><td width="30%">'.$langs->trans("Ref").'</td>';
				print '<td colspan="3">';
				print $form->showrefnav($object,'ref','',1,'ref','','','&element=product');
				print '</td></tr>';

				// Label
				print '<tr><td>'.$langs->trans("Label").'</td><td colspan="2">'.$object->libelle.'</td>';

				print '</table>';

				dol_htmloutput_mesg($langs->trans("DropboxUpgradeIsNeeded"),'','error',1);

				print "<br>\n";
			}
		}
	}
	else
		dol_htmloutput_mesg($langs->trans("DropboxUpgradeIsNeeded"),'','error',1);
}

// End of page
llxFooter();
$db->close();
?>
