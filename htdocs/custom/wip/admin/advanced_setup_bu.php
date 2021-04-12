<?php
/* Copyright (C) 2004-2017	Laurent Destailleur	<eldy@users.sourceforge.net>
 * Copyright (C) 2018		Peter Roberts		<webmaster@finchmc.com.au>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    wip/admin/setup.php
 * \ingroup wip
 * \brief   WIP setup page.
 */

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
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

global $langs, $user;

// Libraries
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
//require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once '../lib/wip.lib.php';
require_once '../class/report.class.php';
require_once '../class/reportdet.class.php';

// Translations
$langs->loadLangs(array('admin', 'errors', 'other', 'projects', 'wip@wip'));

// Access control
if (! $user->admin) accessforbidden();

/*
 * Parameters
 */
$action = 'view';
$action = GETPOST('action', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

// 1.Copy fk_reportdet from fk_task  **  Requires creation of new column fk_reportdet in projet_task_time beforehand
	$sql_1 = 'UPDATE '.MAIN_DB_PREFIX.'projet_task_time as tt';
	$sql_1.= ' SET fk_reportdet = fk_task';
	$sql_1.= ' WHERE NOT EXISTS';
	$sql_1.= ' (SELECT * FROM '.MAIN_DB_PREFIX.'wip_reportdet as wrd';
	$sql_1.= 		' WHERE tt.fk_reportdet = wrd.rowid)';

// 2.Create a new entry in Time Packet (reportdet) table corresponding to each Task. Thus an initial hammock Packet for each Task is generated. 
	$sql_2 = 'INSERT IGNORE INTO '.MAIN_DB_PREFIX.'wip_reportdet (rowid)';
	$sql_2.= ' SELECT rowid FROM '.MAIN_DB_PREFIX.'projet_task';

// 3.Update foreign keys for the new entries in Time Packet (reportdet) table corresponding to each Task. 
	$sql_3 = 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet (rowid)';
	$sql_3.= ' SET fk_task = rowid';
	$sql_3.= ' WHERE fk_task IS NULL';

// 4.Update hours
	$billperiod = $conf->global->WIP_TIME_BLOCK;  //  To make code below a little more readable
	$sql_4 = 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet AS wrd';
	$sql_4.= ' INNER JOIN (';
	$sql_4.= ' SELECT';
	$sql_4.= ' tt.fk_reportdet AS ttfk_reportdet';
	$sql_4.= ', SUM(tt.task_duration/3600) AS duration';
	$sql_4.= ', SUM(				CEIL(  (CEIL(  (tt.task_duration/60) / '.$billperiod.'  ) * '.$billperiod.')   / (60/100)  )/100												) AS billhours';
	$sql_4.= ', SUM(	CEIL(	(	CEIL(  (CEIL(  (tt.task_duration/60) / '.$billperiod.'  ) * '.$billperiod.')   / (60/100)  )/100	) * (100 - wrd1.discount_percent)   )/100	) AS dischours';
	$sql_4.= ' FROM '.MAIN_DB_PREFIX.'projet_task_time as tt';
	$sql_4.= ' LEFT JOIN '.MAIN_DB_PREFIX.'wip_reportdet as wrd1 on wrd1.rowid = tt.fk_reportdet';
	$sql_4.= ' GROUP BY tt.fk_reportdet';
	$sql_4.= ') AS sums ON wrd.rowid = sums.ttfk_reportdet'; 
	$sql_4.= ' SET wrd.duration = sums.duration, wrd.qty = sums.billhours, wrd.discounted_qty = sums.billhours';

	/*
	$task_time = $this->db->fetch_object($resql);
	$timeminutes = $task_time->task_duration / 60;
	$billminutes = ceil($timeminutes / $billperiod) * $billperiod;
	$billhours = ceil($billminutes / (60/100))/100;	// includes rounding up to two decimal figures
	$discountedhours = ceil($billhours * (100 - $task_time->discount_percent))/100; // includes rounding up to two decimal figures
	$packetsum  += $billhours;
	$packetdiscsum  += $discountedhours;
	*/

// 5.Update descriptions
	$tpdescription	= 'This time-packet was automatically created as a hammock for this work-order';
	$sql_5 = 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet AS wrd';
	$sql_5.= ' INNER JOIN (';

		$sql_5.= ' SELECT pttfk_reportdet, GROUP_CONCAT(tnote SEPARATOR "\n") as concat_note';
		$sql_5.= ' FROM (';
	
			$sql_5.= ' SELECT';
			$sql_5.= ' ptt.fk_reportdet AS pttfk_reportdet';
			$sql_5.= ', GROUP_CONCAT(ptt.note';
			$sql_5.= ' ORDER BY ptt.task_datehour ASC';
			$sql_5.= ' SEPARATOR ". ") as tnote';
			$sql_5.= ' FROM '.MAIN_DB_PREFIX.'projet_task_time as ptt ';
			$sql_5.= ' GROUP BY ptt.fk_reportdet, ptt.task_date, ptt.fk_user';
			$sql_5.= ' ORDER BY ptt.task_date ASC, ptt.fk_user ASC';
	
		$sql_5.= ') AS tbli ';
		$sql_5.= ' GROUP BY tbli.pttfk_reportdet';

	$sql_5.= ' ) AS tblo ON wrd.rowid = tblo.pttfk_reportdet';

	$sql_5.= " SET wrd.description = IF (wrd.qty < 250, tblo.concat_note, '".$tpdescription."')";
	$sql_5.= ' WHERE 1 = 1';
	$sql_5.= " AND (wrd.description IS NULL OR wrd.description = '".$tpdescription."' OR wrd.qty !< 250)";

// 6.Update labels
	$tplabel = ' - Auto-generated Time Packet';
	$sql_6 = 'UPDATE '.MAIN_DB_PREFIX.'wip_reportdet AS wrd';
	$sql_6.= ' LEFT JOIN '.MAIN_DB_PREFIX.'projet_task AS pt ON pt.rowid = wrd.fk_task';
	$sql_6.= ' SET wrd.label= CONCAT(pt.label, "'.$tplabel.'")';
	$sql_6.= ' WHERE 1 = 1';
	$sql_6.= ' AND wrd.label IS NULL';

/*
 * Actions
 */
$process = 0;

// 1.Copy fk_reportdet from fk_task  **  Requires creation of new column fk_reportdet beforehand
if ($action == 'copy_fktask')
{
	$sql = $sql_1;
	$process = 1;
}
// 2.Create a new entry in Time Packet (reportdet) table corresponding to each Task. Thus an initial hammock Packet for each Task is generated.
// =============================
else if ($action == 'new_rowid')
{
	$sql = $sql_2;
	$process = 1;
}
// 3.Update foreign keys for the new entries in Time Packet (reportdet) table corresponding to each Task. 
else if ($action == 'update_fktask')
{
	$sql = $sql_3;
	$process = 1;
}
// 4.Update hours
else if ($action == 'update_hours')
{
	$sql = $sql_4;
	$process = 1;
}
// 5.Update descriptions
else if ($action == 'update_descs')
{
	$sql = $sql_5;
	$process = 1;
}
// 6.Update descriptions
else if ($action == 'update_labels')
{
	$sql = $sql_6;
	$process = 1;
}


if ($process == 1)
{
	$process = 0;
	$error = 0;
	dol_syslog(__METHOD__, LOG_DEBUG);
	$resql=$db->query($sql);
	if (!$resql)
	{
		$errors[]=$db->error();
		$error++;
	} else {
		$num = $db->affected_rows($resql);
	}

	if (! $error)
	{
		$db->commit();
	}
	else
	{
		$db->rollback();
	}
}

/*
 * View
 */

$page_name = "WIPAdvancedSetup";

llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_wip@wip');

// Configuration header
$head = wipAdminPrepareHead();
dol_fiche_head($head, 'advanced', '', -1, "wip@wip");

// Setup page goes here
echo $langs->trans("WIPAdvancedSetupPage");

print load_fiche_titre($langs->trans("DatabaseInitiation"),'','');

// Table start
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td class="width80p">'.$langs->trans("Description").'</td>';
print '<td class="maxwidth100">Action</td>';
print '</tr>';


// 1.Copy fk_reportdet from fk_task  **  Requires creation of new column fk_reportdet beforehand
// =============================
print '<tr class="oddeven valigntop">';
print '<td>';
wip_awesome_character('1');
print 'Copy fk_reportdet from fk_task  (*Note: Requires creation of new column fk_reportdet in "'.MAIN_DB_PREFIX.'projet_task_time" beforehand.)';
print '<br><br>$sql = '.$sql_1.'<br><br>';
if ($action == 'copy_fktask')
{
	print '<br><strong>Updated '.($num>0?$num:0).' records.</strong><br>';
	$num = 0;
	$action = '';
}
print '</td>';

print '<td>';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="copy_fktask">';
print '<input type="submit" class="butAction" value="'.$langs->trans('Update')."\">\n</form>";
print '</td>';

// 2.Create a new entry in Time Packet (reportdet) table corresponding to each Task. Thus an initial hammock Packet for each Task is generated.
// =============================
print '<tr class="oddeven valigntop">';
print '<td>';
wip_awesome_character('2');
print 'Create a new entry in Time Packet (reportdet) table corresponding to each Task. Thus an initial hammock Packet for each Task is generated.)';
print '<br><br>$sql = '.$sql_2.'<br><br>';
if ($action == 'new_rowid')
{
	print '<br><strong>Added '.($num>0?$num:0).' records.</strong><br>';
	$num = 0;
	$action = '';
}
print '</td>';

print '<td>';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="new_rowid">';
print '<input type="submit" class="butAction" value="'.$langs->trans('Update')."\">\n</form>";
print '</td>';

// 3.Update foreign keys for the new entries in Time Packet (reportdet) table corresponding to each Task. 
// =============================
print '<tr class="oddeven valigntop">';
print '<td>';
wip_awesome_character('3');
print 'Update foreign keys for the new entries in Time Packet (reportdet) table corresponding to each Task.)';
print '<br><br>$sql = '.$sql_3.'<br><br>';
if ($action == 'update_fktask')
{
	print '<br><strong>Updated '.($num>0?$num:0).' records.</strong><br>';
	$num = 0;
	$action = '';
}
print '</td>';

print '<td>';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update_fktask">';
print '<input type="submit" class="butAction" value="'.$langs->trans('Update')."\">\n</form>";
print '</td>';

// 4.Update quantities of hours
// =============================
print '<tr class="oddeven valigntop">';
print '<td>';
wip_awesome_character('4');
print 'Update time quantities of all Time Packets';
print '<br><br>$billperiod = '.$billperiod;
print '<br>$sql = '.$sql_4.'<br><br>';
if ($action == 'update_hours')
{
	print '<br><strong>Updated '.($num>0?$num:0).' records.</strong><br>';
	$num = 0;
	$action = '';
}
print '</td>';

print '<td>';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update_hours">';
print '<input type="submit" class="butAction" value="'.$langs->trans('Update')."\">\n</form>";
print '</td>';

// 5.Update descriptions
// =============================
print '<tr class="oddeven valigntop">';
print '<td>';
wip_awesome_character('5');
print 'Update descriptions of all null Time Packets (or old description = "'. $tpdescription .'").';
print '<br><br>$sql = '.$sql_5.'<br><br>';
if ($action == 'update_descs')
{
	print '<br><strong>Updated '.($num>0?$num:0).' records.</strong><br>';
	$num = 0;
	$action = '';
}
print '</td>';

print '<td>';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update_descs">';
print '<input type="submit" class="butAction" value="'.$langs->trans('Update')."\">\n</form>";
print '</td>';

// 6.Update labels
// =============================
print '<tr class="oddeven valigntop">';
print '<td>';
wip_awesome_character('6');
print 'Update labels of all null Time Packets.';
print '<br><br>$sql = '.$sql_6.'<br><br>';
if ($action == 'update_labels')
{
	print '<br><strong>Updated '.($num>0?$num:0).' records.</strong><br>';
	$num = 0;
	$action = '';
}
print '</td>';

print '<td>';
print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="update_labels">';
print '<input type="submit" class="butAction" value="'.$langs->trans('Update')."\">\n</form>";
print '</td>';




// Table end
print '</tr>';
print '</table><br>';


// Page end
dol_fiche_end();

llxFooter();
$db->close();
