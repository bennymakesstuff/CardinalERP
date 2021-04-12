<?php
/* Copyright (C) 2011-2018 Regis Houssin  <regis.houssin@capnetworks.com>
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
 * 		\file       /dcloud/admin/dropbox.php
 *		\ingroup    d-cloud
 *		\brief      Page to setup D-Cloud module
 */

if (! defined('JS_JQUERY_MIGRATE_DISABLED')) define('JS_JQUERY_MIGRATE_DISABLED','1'); // since dolibarr 6

// TODO upgrade fileupload
if (! defined('JS_JQUERY')) define('JS_JQUERY','../inc/jquery/');
if (! defined('JS_JQUERY_UI')) define('JS_JQUERY_UI','../inc/jquery/');

$res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../main.inc.php");		// For "custom" directory

require_once __DIR__ . '/../lib/dcloud.lib.php';
require_once __DIR__ . '/../lib/dropbox.lib.php';
require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";

$langs->load("admin");
$langs->load('main');
$langs->load('suppliers');
$langs->load('dcloud@dcloud');

if (! $user->admin) accessforbidden();

$action	= GETPOST('action','alpha');

$_SESSION['dropbox_root'] = $conf->global->DROPBOX_MAIN_ROOT;
$_SESSION['dropbox_root_label'] = 'Dropbox';
$_SESSION['dropbox_root_icon'] = 'drive';

$requestPath = requestPath(); // dropbox.lib.php
$modules = getMainModulesArray(); // dcloud.lib.php


/*
 * 	Action
 */

if ($action == 'setvalue' && !empty($user->admin))
{
	$dropbox_main_root = (GETPOST("DROPBOX_MAIN_ROOT") ? GETPOST("DROPBOX_MAIN_ROOT") : '/');
	$dropbox_main_root = (!preg_match('/^\//', $dropbox_main_root) ? '/' . $dropbox_main_root : $dropbox_main_root);			// add / at the begining
	$dropbox_main_root = (preg_match('/[^\/]+\/$/', $dropbox_main_root) ? substr($dropbox_main_root, 0, -1) : $dropbox_main_root);	// remove / at the end

	$dropbox_main_data_root = (GETPOST("DROPBOX_MAIN_DATA_ROOT") ? GETPOST("DROPBOX_MAIN_DATA_ROOT") : $dropbox_main_root);
	if ($dropbox_main_data_root != $dropbox_main_root) {
		$dropbox_main_data_root = (!preg_match('/^\//', $dropbox_main_data_root) ? '/' . $dropbox_main_data_root : $dropbox_main_data_root);			// add / at the begining
		$dropbox_main_data_root = (preg_match('/\/$/', $dropbox_main_data_root) ? substr($dropbox_main_data_root, 0, -1) : $dropbox_main_data_root);	// remove / at the end
	}

	if (empty($dropbox_main_data_root)) $dropbox_main_data_root = $dropbox_main_root;

	$result=dolibarr_set_const($db, "DROPBOX_MAIN_ROOT",$dropbox_main_root,'chaine',0,'',$conf->entity);
	$result+=dolibarr_set_const($db, "DROPBOX_MAIN_DATA_ROOT",$dropbox_main_data_root,'chaine',0,'',$conf->entity);

	foreach ($modules as $key => $value) {
		$langs->load($value['lang']);
		$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
		$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
		$constvalue = "dropbox_main_".$key."_root";
		if (!empty($conf->global->$enabled)) {
			$$constvalue = (GETPOST($constname) ? dol_html_entity_decode(GETPOST($constname), ENT_QUOTES) : $langs->transnoentities($value['title']));
			$result+=dolibarr_set_const($db,$constname,$$constvalue,'chaine',0,'',$conf->entity);
		}
	}

	if ($result >= 0)
	{
		setEventMessage($langs->trans("SetupSaved"));

		// Force new value
		$_SESSION['dropbox_root'] = $dropbox_main_root;
		$conf->global->DROPBOX_MAIN_ROOT=$dropbox_main_root;
		$conf->global->DROPBOX_MAIN_DATA_ROOT=$dropbox_main_data_root;

		foreach ($modules as $key => $value) {
			$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
			$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
			$constvalue = "dropbox_main_".$key."_root";
			if (!empty($conf->global->$enabled)) {
				$conf->global->$constname=$$constvalue;
			}
		}
	}
	else
	{
		dol_print_error($db);
	}
}
else if ($requestPath === "/dropbox-auth-start" && $user->admin && ! empty($conf->global->DROPBOX_CONSUMER_KEY) && ! empty($conf->global->DROPBOX_CONSUMER_SECRET))
{
	if (empty($conf->global->DROPBOX_ACCESS_TOKEN))
	{
		$key = getPrivateKey();
		if (!empty($key))
		{
			$_SESSION['dcloud_private_key'] = $key;
			$authorizeUrl = getWebAuth();
			if ($authorizeUrl === false)
			{
				setEventMessage($langs->trans("ErrorRedirectUrlNotAvailable"), 'errors');
				Header("Location: ".getUrl());
				exit;
			}
			else
			{
				header("Location: $authorizeUrl");
				exit;
			}
		}
		else
			setEventMessage($langs->trans("ErrorDropboxConnectionIsOut"), 'errors');
	}
	else
	{
		setEventMessage($langs->trans("ErrorTokenIsAlreadyDefined"), 'errors');
	}
}
else if ($requestPath === "/dropbox-auth-finish" && $user->admin && ! empty($conf->global->DROPBOX_CONSUMER_KEY) && ! empty($conf->global->DROPBOX_CONSUMER_SECRET))
{
	if (empty($conf->global->DROPBOX_ACCESS_TOKEN) && !empty($_SESSION['dcloud_private_key']))
	{
		checkAuthFinish();
	}
	else
	{
		setEventMessage($langs->trans("ErrorTokenIsAlreadyDefined"), 'errors');
	}
}
else if ($requestPath === "/dropbox-auth-unlink" && $user->admin)
{
	if (!empty($conf->global->DROPBOX_ACCESS_TOKEN))
	{
		$result=dolibarr_del_const($db,"DROPBOX_ACCESS_TOKEN",$conf->entity);
		if ($result >= 0)
		{
			setEventMessage($langs->trans("DropboxTokenIsUnset"));
			Header("Location: ".getUrl());
			exit;
		}
		else
		{
			dol_print_error($db);
		}
	}
	else
	{
		setEventMessage($langs->trans("ErrorTokenIsAlreadyUnset"), 'errors');
	}
}


/*
 *	View
 */

$form=new Form($db);

$moreheadcss='';
$moreheadjs='';

$arrayofjs=array(
	'/dcloud/core/js/lib_head.js',
	'/dcloud/inc/segbar/segbar.js',
	'/dcloud/inc/jstree/jquery.jstree.min.js',
	'/dcloud/inc/jstree/jquery.cookie.js',
	'/dcloud/inc/jstree/jquery.hotkeys.js',
	'includes/jquery/plugins/blockUI/jquery.blockUI.js',
	'core/js/blockUI.js'
);

$moreheadjs.='<script type="text/javascript">'."\n";
$moreheadjs.='var indicatorBlockUI = \''.DOL_URL_ROOT."/theme/".$conf->theme."/img/working.gif".'\';'."\n";
$moreheadjs.='</script>'."\n";

llxHeader($moreheadcss.$moreheadjs,$langs->trans("DropboxSetup"),'','','','',$arrayofjs);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("ModuleSetup"),$linkback,'dropbox_32x32@dcloud');

$head=dropboxadmin_prepare_head();

dol_fiche_head($head, 'parameters', $langs->trans("Module70000Name"));

// Alert if 32bits server
if (strlen((string) PHP_INT_MAX) < 19) {
	dol_htmloutput_mesg($langs->trans("DropboxAlert32bitsServer"),'','error',1);
}

if (checkDCloudEncrypt() !== true) {
	dol_htmloutput_mesg(checkDCloudEncrypt(),'','error',1);
}

if (!checkDCloudVersion()) {
	dol_htmloutput_mesg($langs->trans("DropboxUpgradeIsNeeded"),'','error',1);
}

print '<br>';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setvalue">';

adminBlockShowHide('rootmodules',$langs->trans("ParentDirectoriesParameters"));

print '<br />';

adminBlockShowHide('thirdpartymodules',$langs->trans("ThirdpartiesDirectoriesParameters"));

print '<br />';

print '</div>';

$connected = true;
if (!empty($conf->global->DROPBOX_ACCESS_TOKEN) && !is_connected()) {
	$connected = false;
	setEventMessage($langs->trans("ErrorDropboxConnectionIsOut"), 'errors');
}

if (checkDCloudVersion() && checkDCloudEncrypt())
{
	// Confirm unset token
	if (($action == 'unsettoken' && (empty($conf->use_javascript_ajax) || !empty($conf->dol_use_jmobile)))	// Output when action = clone if jmobile or no js
	|| (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile)))							// Always output when not jmobile nor js
	{
		print $form->formconfirm(getUrl('dropbox-auth-unlink'),$langs->trans("UnsetToken"),$langs->trans("ConfirmUnsetToken"),'','',0,"action-delete", 140, 400);
	}

	// Boutons actions
	print '<div class="tabsAction">';

	print '<input type="submit" id="save" name="save" class="butAction linkobject" value="'.$langs->trans("Save").'" />';
	print '</form>'."\n";
	print '&nbsp;';

	if ($connected)
	{
		if (!empty($conf->global->DROPBOX_ACCESS_TOKEN))
		{
			if (!empty($conf->use_javascript_ajax) && empty($conf->dol_use_jmobile))
			{
				print '<div class="inline-block divButAction"><span id="action-delete" class="butActionDelete">'.$langs->trans("UnsetToken").'</span></div>'."\n";
			}
			else
			{
				print '<div class="inline-block divButAction"><a class="butActionDelete" href="'.getUrl('dropbox-auth-unlink').'">'.$langs->trans("UnsetToken").'</a></div>';
			}
		}
		else
		{
			print '<a class="butAction" href="'.getUrl('dropbox-auth-start').'">'.$langs->trans("SetToken").'</a>';
		}
	}

	print '</div>';
}

if (checkDCloudVersion())
{
	if (!empty($conf->global->DROPBOX_ACCESS_TOKEN) && $connected)
	{
		dol_fiche_head();

		print '<table class="nobordernopadding" width="100%">';
		print '<tr class="liste_titre">'."\n";
		print '<td>'.$langs->trans("DropboxQuota").'</td>'."\n";
		print '</tr></table>'."\n";
		print '<br />';

		include('../tpl/ajaxdropboxquota.tpl.php');

		print '<table class="nobordernopadding" width="100%">';
		print '<tr class="liste_titre">'."\n";
		print '<td>'.$langs->trans("DropboxTree").'</td>'."\n";
		print '</tr></table>'."\n";

		include('../tpl/ajaxdropboxtree.tpl.php');

		include('tpl/synchro.tpl.php');
	}
}

llxFooter();
$db->close();
