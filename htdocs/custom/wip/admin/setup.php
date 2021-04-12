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

// Translations
$langs->loadLangs(array('admin', 'errors', 'other', 'projects', 'wip@wip'));

// Access control
if (! $user->admin) accessforbidden();

// Parameters
$type = GETPOST('type', 'alpha');
$value = GETPOST('value', 'alpha');
$action = GETPOST('action', 'alpha');
$label = GETPOST('label', 'alpha');
$scandir = GETPOST('scan_dir', 'alpha');
$backtopage = GETPOST('backtopage', 'alpha');

$arrayofparameters=array(
	'WIP_TIME_BLOCK'=>array('css'=>'minwidth50','enabled'=>1),
	'WIP_SERVICE_CATEGORY'=>array('css'=>'minwidth50','enabled'=>1),
	'WIP_DEFAULT_SERVICE_PRICE'=>array('css'=>'minwidth50','enabled'=>1),
	'WIP_DEFAULT_MINIMUM_PRICE'=>array('css'=>'minwidth50','enabled'=>1),
);


/*
 * Actions
 */
if ((float) DOL_VERSION >= 6)
{
	include DOL_DOCUMENT_ROOT.'/core/actions_setmoduleoptions.inc.php';
}

if ($action == 'updateMask')
{
	$maskconstreport=GETPOST('maskconstreport','alpha');
	$maskvalue=GETPOST('maskvalue','alpha');

    if ($maskconstreport)  $res = dolibarr_set_const($db,$maskconstreport,$maskvalue,'chaine',0,'',$conf->entity);

	if (! $res > 0) $error++;

	if (! $error)
	{
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	}
	else
	{
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

else if ($action == 'specimen')  // For reports
{
	$modele=GETPOST('module','alpha');

    $report = new Report($db);
    $report->initAsSpecimen();
    $report->thirdparty=$specimenthirdparty;

	// Search template files
	$file=''; $classname=''; $filefound=0;
	$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);
	foreach($dirmodels as $reldir)
	{
		$file=dol_buildpath($reldir."core/modules/pdf/pdf_".$modele.".modules.php",0);
		if (file_exists($file))
		{
			$filefound=1;
			$classname = "pdf_".$modele;
			break;
		}
	}

	if ($filefound)
	{
		require_once $file;

		$module = new $classname($db);
//		$module = new $classname($db,$report);

		if ($module->write_file($report,$langs) > 0)
		{
			header("Location: ".DOL_URL_ROOT."/document.php?modulepart=reports&file=SPECIMEN.pdf");
			return;
		}
		else
		{
			setEventMessages($module->error, $module->errors, 'errors');
			dol_syslog($module->error, LOG_ERR);
		}
	}
	else
	{
		setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	}
}

// Activate a model
else if ($action == 'set')
{
	$ret = addDocumentModel($value, $type, $label, $scandir);
}

else if ($action == 'del')
{
	$ret = delDocumentModel($value, $type);
	if ($ret > 0)
	{
		if ($conf->global->WIP_ADDON_PDF == "$value") dolibarr_del_const($db, 'WIP_ADDON_PDF',$conf->entity);
	}
}

// Set default model
else if ($action == 'setdoc')
{
	if (dolibarr_set_const($db, "WIP_ADDON_PDF",$value,'chaine',0,'',$conf->entity))
	{
		// The constant that was read ahead of the new set is therefore passed through a variable to have a coherent display
		$conf->global->WIP_ADDON_PDF = $value;
	}

	// We activate the model
	$ret = delDocumentModel($value, $type);
	if ($ret > 0)
	{
		$ret = addDocumentModel($value, $type, $label, $scandir);
	}
}

else if ($action == 'setmod')
{
    // TODO Check if selected numbering module can be active by call method  canBeActivated

    dolibarr_set_const($db, "WIP_ADDON_NUMBER",$value,'chaine',0,'',$conf->entity);
}

// Activate ask for payment bank
/*
 * View
 */

$page_name = "WIPSetup";

$form=new Form($db);

$dirmodels=array_merge(array('/'),(array) $conf->modules_parts['models']);

llxHeader('', $langs->trans($page_name));

// Subheader
$linkback = '<a href="'.($backtopage?$backtopage:DOL_URL_ROOT.'/admin/modules.php?restore_lastsearch_values=1').'">'.$langs->trans("BackToModuleList").'</a>';

print load_fiche_titre($langs->trans($page_name), $linkback, 'object_wip@wip');

// Configuration header
$head = wipAdminPrepareHead();
dol_fiche_head($head, 'settings', '', -1, "wip@wip");

// Setup page goes here
echo $langs->trans("WIPSetupPage");

/*
 * Projects Numbering model
 */

print load_fiche_titre($langs->trans("ReportsNumberingModules"),'','');

print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td width="100">'.$langs->trans("Name").'</td>';
print '<td>'.$langs->trans("Description").'</td>';
print '<td>'.$langs->trans("Example").'</td>';
print '<td align="center" width="60">'.$langs->trans("Status").'</td>';
print '<td align="center" width="16">'.$langs->trans("ShortInfo").'</td>';
print "</tr>\n";

clearstatcache();

foreach ($dirmodels as $reldir)
{
	$dir = dol_buildpath($reldir."core/modules/");

	if (is_dir($dir))
	{
		$handle = opendir($dir);
		if (is_resource($handle))
		{
			while (($file = readdir($handle))!==false)
			{
				if (substr($file, 0, 8) == 'mod_wip_' && substr($file, dol_strlen($file)-3, 3) == 'php')
				{
					$file = substr($file, 0, dol_strlen($file)-4);

					require_once $dir.$file.'.php';

					$module = new $file;

					if ($module->isEnabled())
					{
					// Show modules according to features level
						if ($module->version == 'development'  && $conf->global->MAIN_FEATURES_LEVEL < 2) continue;
						if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1) continue;

						print '<tr class="oddeven"><td>'.$module->nom."</td><td>\n";
						print $module->info();
						print '</td>';

						// Show example of numbering module
						print '<td class="nowrap">';
						$tmp=$module->getExample();
						if (preg_match('/^Error/',$tmp)) {
							$langs->load("errors"); print '<div class="error">'.$langs->trans($tmp).'</div>';
						}
						elseif ($tmp=='NotConfigured') print $langs->trans($tmp);
						else print $tmp;
						print '</td>'."\n";

						print '<td align="center">';
						if ($conf->global->WIP_ADDON_NUMBER == "$file")
						{
							print img_picto($langs->trans("Activated"),'switch_on');
						}
						else
						{
							print '<a href="'.$_SERVER["PHP_SELF"].'?action=setmod&amp;value='.$file.'" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
						}
						print '</td>';

						 $report=new Report($db);
						$report->initAsSpecimen();

						// Info
						$htmltooltip='';
						$htmltooltip.=''.$langs->trans("Version").': <b>'.$module->getVersion().'</b><br>';
						$nextval=$module->getNextValue($mysoc,$report);
						if ("$nextval" != $langs->trans("NotAvailable")) {  // Keep " on nextval
							$htmltooltip.=''.$langs->trans("NextValue").': ';
							if ($nextval) {
								if (preg_match('/^Error/',$nextval) || $nextval=='NotConfigured')
								$nextval = $langs->trans($nextval);
								$htmltooltip.=$nextval.'<br>';
							} else {
								$htmltooltip.=$langs->trans($module->error).'<br>';
							}
						}

						print '<td align="center">';
						print $form->textwithpicto('',$htmltooltip,1,0);
						print '</td>';

						print '</tr>';
					}
				}
			}
			closedir($handle);
		}
	}
}

print '</table><br>';


/*
 *  Document models for Reports
 */

print load_fiche_titre($langs->trans("ReportsModelModule"),'','');

// Define model table
$type='wip_report';
$def = array();

$sql = "SELECT nom";
$sql.= " FROM ".MAIN_DB_PREFIX."document_model";
$sql.= " WHERE type = '".$type."'";
$sql.= " AND entity = ".$conf->entity;

$resql=$db->query($sql);
if ($resql)
{
	$i = 0;
	$num_rows=$db->num_rows($resql);
	while ($i < $num_rows)
	{
		$array = $db->fetch_array($resql);
		array_push($def, $array[0]);
		$i++;
	}
}
else
{
	dol_print_error($db);
}

print '<table class="noborder" width="100%">'."\n";
print '<tr class="liste_titre">'."\n";
print '<td width="100">'.$langs->trans("Name").'</td>'."\n";
print '<td>'.$langs->trans("Description").'</td>'."\n";
print '<td align="center" width="60">'.$langs->trans("Status").'</td>'."\n";
print '<td align="center" width="60">'.$langs->trans("Default").'</td>'."\n";
print '<td align="center" width="40">'.$langs->trans("ShortInfo").'</td>';
print '<td align="center" width="40">'.$langs->trans("Preview").'</td>';
print '</tr>'."\n";

clearstatcache();

foreach ($dirmodels as $reldir)
{
	$dir = dol_buildpath($reldir."core/modules/pdf/");

	if (is_dir($dir))
	{
		$handle=opendir($dir);
		if (is_resource($handle))
		{
			while (($file = readdir($handle))!==false)
			{
				if (preg_match('/\.modules\.php$/i',$file) && preg_match('/^(pdf_|doc_)/',$file))
				{
					$name = substr($file, 4, dol_strlen($file) -16);
					$classname = substr($file, 0, dol_strlen($file) -12);

					require_once $dir.'/'.$file;
					$module = new $classname($db, new Report($db));


                    print "<tr class=\"oddeven\">\n";
                    print "<td>";
					print (empty($module->name)?$name:$module->name);
					print "</td>\n";
                    print "<td>\n";
                    require_once $dir.$file;
                    $module = new $classname($db,$specimenthirdparty);
					if (method_exists($module,'info')) print $module->info($langs);
					else print $module->description;
					print "</td>\n";

					// Active
					if (in_array($name, $def))
					{
						print "<td align=\"center\">\n";
                        if ($conf->global->WIP_ADDON_PDF != "$name")
						{
							print '<a href="'.$_SERVER["PHP_SELF"].'?action=del&amp;value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'&amp;type=wip_report">';
							print img_picto($langs->trans("Enabled"),'switch_on');
							print '</a>';
						}
						else
						{
							print img_picto($langs->trans("Enabled"),'switch_on');
                        }
							print "</td>";
					}
					else
                    {
                        print '<td align="center">'."\n";
                        print '<a href="'.$_SERVER["PHP_SELF"].'?action=set&amp;value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'&amp;type=wip_report">'.img_picto($langs->trans("Disabled"),'switch_off').'</a>';
                        print "</td>";
					}

					// Default
					print '<td align="center">';
					if ($conf->global->WIP_ADDON_PDF == "$name")
					{
						print img_picto($langs->trans("Default"),'on');
					}
					else
					{
						print '<a href="'.$_SERVER["PHP_SELF"].'?action=setdoc&amp;value='.$name.'&amp;scan_dir='.$module->scandir.'&amp;label='.urlencode($module->name).'&amp;type=wip_report"" alt="'.$langs->trans("Default").'">'.img_picto($langs->trans("Disabled"),'off').'</a>';
					}
					print '</td>';

					// Info
					$htmltooltip =    ''.$langs->trans("Name").': '.$module->name;
					$htmltooltip.='<br>'.$langs->trans("Type").': '.($module->type?$module->type:$langs->trans("Unknown"));
					$htmltooltip.='<br>'.$langs->trans("Width").'/'.$langs->trans("Height").': '.$module->page_largeur.'/'.$module->page_hauteur;
					$htmltooltip.='<br><br><u>'.$langs->trans("FeaturesSupported").':</u>';
					$htmltooltip.='<br>'.$langs->trans("Logo").': '.yn($module->option_logo,1,1);

					print '<td align="center">';
					print $form->textwithpicto('',$htmltooltip,1,0);
					print '</td>';

					// Preview
					print '<td align="center">';
					print '<a href="'.$_SERVER["PHP_SELF"].'?action=specimen&amp;module='.$name.'">'.img_object($langs->trans("Preview"),'report').'</a>';
					print '</td>';

					print "</tr>\n";
				}
			}

			closedir($handle);
		}
	}
}

print '</table><br/>';

/*
 * Other options
 */


if ($action == 'edit')
{
	print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'">';
	print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	print '<input type="hidden" name="action" value="update">';

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Parameter").'</td><td>'.$langs->trans("Value").'</td></tr>';

	foreach($arrayofparameters as $key => $val)
	{
		if (isset($val['enabled']) && empty($val['enabled'])) continue;

		print '<tr class="oddeven"><td>';
		print $form->textwithpicto($langs->trans($key),$langs->trans($key.'Tooltip'));
		print '</td><td><input name="'.$key.'"  class="flat '.(empty($val['css'])?'minwidth200':$val['css']).'" value="' . $conf->global->$key . '"></td></tr>';
	}

	print '</table>';

	print '<br><div class="center">';
	print '<input class="button" type="submit" value="'.$langs->trans("Save").'">';
	print '</div>';

	print '</form>';
	print '<br>';
}
else
{
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre"><td class="titlefield">'.$langs->trans("Parameter").'</td><td>'.$langs->trans("Value").'</td></tr>';

	foreach($arrayofparameters as $key => $val)
	{
		print '<tr class="oddeven"><td>';
		print $form->textwithpicto($langs->trans($key),$langs->trans($key.'Tooltip'));
		print '</td><td>' . $conf->global->$key . '</td></tr>';
	}

	print '</table>';

	print '<div class="tabsAction">';
	print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit">'.$langs->trans("Modify").'</a>';
	print '</div>';
}


// Page end
dol_fiche_end();

llxFooter();
$db->close();
