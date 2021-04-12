<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Bariley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2013      Cédric Salvador      <csalvador@gpcsolutions.fr>
 * Copyright (C) 2015 	   Claudio Aschieri     <c.aschieri@19.coop>
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
 *	\file       htdocs/projet/projectsummary.php
 *	\ingroup    projet
 *	\brief      Page to list projects
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
/*
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
*/
if (!$user->rights->projet->lire) accessforbidden();

$langs->load('projects');
$langs->load('companies');
$langs->load('commercial');

$action=GETPOST('action','alpha');
$object = new Project($db);

/*
 * Actions
 */
$modelpdf=open_projects_report;
	// Build doc
	if ($action == 'builddoc')
	{
		// Save last template used to generate document
//		if (GETPOST('model')) $object->setDocModel($user, GETPOST('model','alpha'));

	    $outputlangs = $langs;
	    if (GETPOST('lang_id'))
	    {
	        $outputlangs = new Translate("",$conf);
	        $outputlangs->setDefaultLang(GETPOST('lang_id'));
	    }
	    $result= $object->generateDocument($modelpdf, $outputlangs);
	    if ($result <= 0)
	    {
	        setEventMessages($object->error, $object->errors, 'errors');
	        $action='';
	    }
	}
/*
	// Delete file in doc form
	if ($action == 'remove_file' && $user->rights->projet->creer)
	{
	    if ($object->id > 0)
	    {
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';

			$langs->load("other");
			$upload_dir = $conf->projet->dir_output;
			$file = $upload_dir . '/' . GETPOST('file');
			$ret = dol_delete_file($file, 0, 0, 0, $object);
			if ($ret)
				setEventMessages($langs->trans("FileWasRemoved", GETPOST('file')), null, 'mesgs');
			else
				setEventMessages($langs->trans("ErrorFailToDeleteFile", GETPOST('file')), null, 'errors');
			$action = '';
	    }
	}
*/

/*
 * View
 */

$socid=161; // the ID for Finch Vehicles
$socid2=529; // the ID for Finch Administration (Overheads)
$formfile = new FormFile($db);

$title = $langs->trans("Open Projects");
llxHeader("",$title,"EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos");

// Table
print '<div class="div-table-responsive">';
print '<table class="tagtable liste">'."\n";

$j=0;
while ($j < 2)
{

	$projectstatic = new Project($db);
	$socstatic = new Societe($db);

	$sql = "SELECT p.rowid as projectid, p.ref, p.title, p.fk_statut";
	$sql.= ", s.nom as name, s.rowid as socid, s.phone as phone";
	$sql.= ", se.altphone as altphone";
	$sql.= ", am.makename as makename, am.make_logo as make_logo";
	$sql.= ", acc.colourname as colourname, acc.hex_code as hex_code";

	$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s on p.fk_soc = s.rowid";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe_extrafields as se on p.fk_soc = se.fk_object";
	$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_customfields as pc on p.rowid = pc.fk_projet";
	$sql.= " LEFT JOIN auto_makes as am on pc.makename = am.makeid";
	$sql.= " LEFT JOIN auto_colour_codes as acc on pc.colourname = acc.colourid";

	//if ($socid) $sql.= " AND (p.fk_soc IS NULL OR p.fk_soc = 0 OR p.fk_soc = ".$socid.")";
	if ($j ==0)
	{
		$sql.= " WHERE (p.fk_soc <> ".$socid." AND p.fk_soc <> ".$socid2.")";
	}
	else
	{
		$sql.= " WHERE (p.fk_soc = ".$socid." OR p.fk_soc = ".$socid2.")";
	}
	$sql .= " AND p.fk_statut = 1";
	$sql .= " ORDER BY p.ref";

	$resql = $db->query($sql);
	if (! $resql)
	{
		dol_print_error($db);
		exit;
	}
	$num = $db->num_rows($resql);
	$var=true;

	if ($j == 0)
	{
		$text=$langs->trans("Open Customer Projects");
		$desc='This table represents all Open (not Draft nor Closed) customer projects.<br/><br/>';
	}
	else
	{
		$text=$langs->trans("Open Finch Projects");
		$desc='This table represents all Open (not Draft nor Closed) Finch projects.<br/><br/>';
	}

	// Show description of content
	print '<tr class="liste_titre">';
	print '<td colspan="8">';
	print_barre_liste($text, '', $_SERVER["PHP_SELF"], '', '', '', '', $num, $num, 'title_project', 0, '', '', 0, 1);
//	print $desc;
	print '</td>';
	print '</tr>';

	print '<tr class="liste_titre">';

	// Project Number
	print '<td class="liste_titre">Project Number</td>';

	// Project Description
	print '<td class="liste_titre">Project Description</td>';

	// Third Party Name
	print '<td class="liste_titre">Third party</td>';

	// Contact Number
	print '<td class="liste_titre">Contact number</td>';

	// Alt Number
	print '<td class="liste_titre">Alternate number</td>';

	// Make
	print '<td class="liste_titre" align="center">Make</td>';

	// Colour code
	print '<td class="liste_titre" align="center">Colour code</td>';

	// Status
	print '<td class="liste_titre nowrap" align="right">Status &nbsp; &nbsp; </td>';

	print '</tr>'."\n";


	$i=0;
	$var=true;

	while ($i < $num)
	{
		$obj = $db->fetch_object($resql);

		$projectstatic->id = $obj->projectid;
		$projectstatic->ref = $obj->ref;
		$projectstatic->statut = $obj->fk_statut;
		$projectstatic->makename = $obj->makename;
		$projectstatic->make_logo = $obj->make_logo;
		$projectstatic->colourname = $obj->colourname;
		$projectstatic->hex_code = $obj->hex_code;

		$var=!$var;
		print "<tr ".$bc[$var].">";

		// Project url
		print '<td class="nowrap">';
		print $projectstatic->getNomUrl(1);
		if ($projectstatic->hasDelay()) print img_warning($langs->trans('Late'));
		print '</td>';

		// Title
		print '<td>';
		print dol_trunc($obj->title,80);
		print '</td>';

		// Company
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

		// Phone
		print '<td>';
		if ($obj->socid && $obj->phone)
		{
			$socstatic->phone=$obj->phone;
			print img_picto('', 'object_phoning').'&nbsp;'.$socstatic->phone;
		}
		else
		{
			print '&nbsp;';
		}
		print '</td>';

		// Alt. Phone
		print '<td>';
		if ($obj->socid && $obj->altphone)
		{
			$socstatic->altphone=$obj->altphone;
			print img_picto('', 'object_phoning').'&nbsp;'.$socstatic->altphone;
		}
		else
		{
			print '&nbsp;';
		}
		print '</td>';

		// Make
		print '<td align="center">';
		if ($obj->makename)
		{
			print img_picto($projectstatic->makename, '/custom/autos/img/icons/'.$projectstatic->make_logo,'',1,0,0);
		}
		else
		{
			print '&nbsp;';
		}
		print '</td>';

		// Colour Code
		if ($obj->colourname)
		{
			print '<td align="center" bgcolor="'.$projectstatic->hex_code.'">';
			print $projectstatic->colourname;
		}
		else
		{
			print '<td>';
			print '&nbsp;';
		}
		print '</td>';

		// Status
		$projectstatic->statut = $obj->fk_statut;
		print '<td align="right">'.$projectstatic->getLibStatut(5).'</td>';

		print "</tr>\n";

		$i++;
	}

	$db->free($resql);

	$j++;
}

	print "</table>\n";
	print '</div>';

print '<div class="fichecenter"><div class="fichehalfleft">';
print '<a href="#builddoc" name="builddoc"></a>'; // ancre

/*
* Documents generes
*/
// Benjamin Broad Mod
// This file needs to be modified to use the dolibar doc_root route.
$filename='Reports';
$filedir=DOL_DOCUMENT_ROOT.'/documents/projet/Reports';
$urlsource=$_SERVER["PHP_SELF"].'#builddoc';
$genallowed=1;
$delallowed=1;
$modelpdf=open_projects_report;

$var=true;

print $formfile->showdocuments('project',$filename,$filedir,$urlsource,$genallowed,$delallowed,$modelpdf);

print '</div><div class="fichehalfright"><div class="ficheaddleft">';

print '</div></div></div>';


llxFooter();

$db->close();
