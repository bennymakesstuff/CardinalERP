<?php
/* Copyright (C) 2014-2018 Regis Houssin  <regis.houssin@capnetworks.com>
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
 * 		\file       /dcloud/admin/options.php
 *		\ingroup    d-cloud
 *		\brief      Page to setup options for D-Cloud module
 */


$res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory

require_once __DIR__ . '/../lib/dcloud.lib.php';
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";

$langs->load("admin");
$langs->load('dcloud@dcloud');

if (! $user->admin) accessforbidden();

$action	= GETPOST('action','alpha');


/*
 * 	Action
 */

if ($action == 'setvalue' && $user->admin)
{
	$result=dolibarr_set_const($db, "DCLOUD_MEMCACHED_SERVER",GETPOST("DCLOUD_MEMCACHED_SERVER"),'chaine',0,'',$conf->entity);

	if ($result >= 0)
	{
		setEventMessage($langs->trans("SetupSaved"));

		// Force new value
		$conf->global->DCLOUD_MEMCACHED_SERVER=GETPOST("DCLOUD_MEMCACHED_SERVER");
	}
	else
	{
		dol_print_error($db);
	}
}


/*
 *	View
 */

$form=new Form($db);

$arrayofjs=array(
		'/dcloud/core/js/lib_head.js',
		'/dcloud/inc/jstree/jquery.cookie.js'
);

llxHeader('',$langs->trans("DropboxSetup"),'','','','',$arrayofjs);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ModuleSetup"),$linkback,'dropbox_32x32@dcloud');

$head=dropboxadmin_prepare_head();

dol_fiche_head($head, 'options', $langs->trans("Module70000Name"));

if (!checkDCloudVersion())
	dol_htmloutput_mesg($langs->trans("DropboxUpgradeIsNeeded"),'','error',1);

print '<br />';

print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvalue">';

adminBlockShowHide('caches', $langs->trans("CachesSystems"));

print '</div>';

// Boutons actions
print '<div class="tabsAction">';
print '<input type="submit" id="save" name="save" class="butAction linkobject" value="'.$langs->trans("Save").'" />';
print '</form>'."\n";
print '</div>';

if (!empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) || !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED)
	|| !empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED) || !empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED)) {

	print '<br />';
	adminBlockShowHide('parameters', $langs->trans("Parameters"));
	print '</div>';
}

llxFooter();
$db->close();
