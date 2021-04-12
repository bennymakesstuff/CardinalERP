<?php
/* Copyright (C) 2011-2018	Regis Houssin	<regis.houssin@capnetworks.com>
 * Copyright (C) 2015-2018	Philippe Grand	<philippe.grand@atoo-net.com>
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
 * or see http://www.gnu.org/
 */

/**
 *  \file       /milestone/admin/milestone.php
 *  \ingroup    milestone
 *  \brief      Administration/configuration of Milestone module
 */
$res=@include "../../main.inc.php";					// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include $_SERVER['DOCUMENT_ROOT']."/main.inc.php"; // Use on dev env only
if (! $res) $res=@include "../../../main.inc.php";		// For "custom" directory

dol_include_once('/milestone/lib/milestone.lib.php');
include_once(DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php');
require_once(DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php');

// Load traductions files requiredby by page
$langs->loadLangs(array("milestone@milestone","admin"));

// Security check
if (! $user->admin) {
	accessforbidden();
}

$action		= GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');


/*
 * Action
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_set_const($db, $code, 1, 'chaine', 0, '', $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

if (preg_match('/del_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_del_const($db, $code, $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

if ($action == 'setcolor')
{
	require_once (DOL_DOCUMENT_ROOT . "/core/lib/files.lib.php");

	$background_color = GETPOST('MILESTONE_BACKGROUND_COLOR', 'alpha');
	if (! empty($background_color))
	{
		$res = dolibarr_set_const($db, 'MILESTONE_BACKGROUND_COLOR', $background_color, 'chaine', 0, '', $conf->entity);
	}
	else
	{
		$res = dolibarr_set_const($db, 'MILESTONE_BACKGROUND_COLOR', 'e0e0e0', 'chaine', 0, '', $conf->entity);
	}
	if (! $res > 0)
		$error ++;
	if (! $error)
	{
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
	else
	{
		setEventMessages($langs->trans("Error") . " " . $msg, null, 'errors');
	}
}

/*
 * View
 */

$moreheadjs='';
$arrayjs='';
if (!checkMilestoneVersion()) {

	$moreheadjs.='<script type="text/javascript">'."\n";
	$moreheadjs.='var indicatorBlockUI = \''.DOL_URL_ROOT."/theme/".$conf->theme."/img/working.gif".'\';'."\n";
	$moreheadjs.='</script>'."\n";

	$arrayjs = array(
		'/milestone/core/js/lib_head.js',
		'includes/jquery/plugins/blockUI/jquery.blockUI.js',
		'core/js/blockUI.js'
	);
}

$wikihelp='EN:Module_Jalon_FR#Configuration_du_module|FR:Module_Jalon_FR#Configuration_du_module';
llxHeader($moreheadjs,$langs->trans("Module1790Name"), $wikihelp, '', 0, 0, $arrayjs);

$formother=new FormOther($db);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1">'.$langs->trans("BackToModuleList").'</a>';
print load_fiche_titre($langs->trans("ModuleSetup"),$linkback,'milestone@milestone');

print '<br>';

$head = milestoneadmin_prepare_head();

dol_fiche_head($head, 'options', $langs->trans("Module1790Name"), 0, 'milestone@milestone');

// Check current version
if (!checkMilestoneVersion()) {
	dol_htmloutput_mesg($langs->trans("MilestoneUpgradeIsNeeded"),'','error',1);
}

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("MilestoneSetup").'</em></b>';
print '</div>';

print '<table class="border centpercent">'."\n";
print '<tr class="liste_titre info">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

/*
 * Formulaire parametres divers
 */

// Hide product details inside milestone
print '<tr class="oddeven">';
print '<td>'.$langs->trans("HideBydefaultProductDetailsInsideMilestone").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if (! empty($conf->use_javascript_ajax))
{
	print ajax_constantonoff('MILESTONE_HIDE_PRODUCT_DETAILS');
}
else
{
	if (empty($conf->global->MILESTONE_HIDE_PRODUCT_DETAILS))
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MILESTONE_HIDE_PRODUCT_DETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MILESTONE_HIDE_PRODUCT_DETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide product description inside milestone
print '<tr class="oddeven">';
print '<td>'.$langs->trans("HideByDefaultProductDescInsideMilestone").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if (! empty($conf->use_javascript_ajax))
{
	print ajax_constantonoff('MILESTONE_HIDE_PRODUCT_DESC');
}
else
{
	if (empty($conf->global->MILESTONE_HIDE_PRODUCT_DESC))
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MILESTONE_HIDE_PRODUCT_DESC">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MILESTONE_HIDE_PRODUCT_DESC">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide milestone amount
print '<tr class="oddeven">';
print '<td>'.$langs->trans("HideByDefaultMilestoneAmount").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if (! empty($conf->use_javascript_ajax))
{
	print ajax_constantonoff('MILESTONE_HIDE_MILESTONE_AMOUNT');
}
else
{
	if (empty($conf->global->MILESTONE_HIDE_MILESTONE_AMOUNT))
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MILESTONE_HIDE_MILESTONE_AMOUNT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MILESTONE_HIDE_MILESTONE_AMOUNT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide or display picto for jalon
print '<tr class="oddeven">';
print '<td>'.$langs->trans("HideOrDisplayMilestonePicto").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if (! empty($conf->use_javascript_ajax))
{
	print ajax_constantonoff('MILESTONE_HIDE_DISPLAY_PICTO');
}
else
{
	if (empty($conf->global->MILESTONE_HIDE_DISPLAY_PICTO))
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MILESTONE_HIDE_DISPLAY_PICTO">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MILESTONE_HIDE_DISPLAY_PICTO">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

print '</table>';

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("GUISetup").'</em></b>';
print '</div>';

// Colorpicker for Milestone
print '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="setcolor">';
print '<input type="hidden" name="backtopage" value="'.$backtopage.'">';

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// PDF background color
print '<tr class="oddeven"><td>'.$langs->trans("MilestoneBackgroundColor").'</td>';
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">';
$backgroundcolor = (! empty($conf->global->MILESTONE_BACKGROUND_COLOR)?$conf->global->MILESTONE_BACKGROUND_COLOR:'e6e6e6');
print $formother->selectColor($backgroundcolor, "MILESTONE_BACKGROUND_COLOR", null, 1, '', 'hideifnotset');
print '</td></tr>';

print '</table><br>';

dol_fiche_end();

// Boutons actions
print '<div class="tabsAction">';

if (!checkMilestoneVersion())
{
	print '<div class="inline-block divButAction"><span id="action-upgrade" class="milestone-button-upgrade butAction">'.img_warning().' '.$langs->trans("MilestoneModuleUpgrade").'</span></div>'."\n";

	include('tpl/upgrade.tpl.php');
}
else
	print '<div class="center"><input type="submit" id="save" name="save" class="butAction linkobject" value="'.$langs->trans("Save").'" />';
	print '</div>';

print '</form>'."\n";

print '</div>';

llxFooter();
$db->close();
