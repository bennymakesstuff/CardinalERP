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
 *	\file       htdocs/projet/colourmatrix.php
 *	\ingroup    projet
 *	\brief      Page to list projects
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
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
/*			$modelpdf=open_projects_report;
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
*/
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
			 * Set-up for View
			 */
			
			$socid=161; // the ID for Finch Motor Company
	
			//		Colours for table
			$sql = "SELECT acc.colourid as colourid, acc.colourname as colourname, acc.hex_code as hex_code, acc.red_comp as red, acc.green_comp as green, acc.blue_comp as blue, acc.text_col as text_col";
			$sql.= " FROM auto_colour_codes as acc";
			$sql.= " WHERE acc.published = 1";
//			$sql.= " ORDER BY colourname";
			$sql.= " ORDER BY colourid";
			
			$resql = $db->query($sql);
			$colcodes = array();
			$maxid = 0;
	
			if ($resql)
			{
				$numcols = $db->num_rows($resql);
				for ($i = 0 ; $i < $numcols; $i++)
					{
						$row = $db->fetch_object($resql);
						$colcodes[$i] = $row;
					}
				$db->free($resql);
			}
			else
			{
				dol_print_error($db);
			}
	
			//		Car Makes for table
			$sql = "SELECT COUNT(makeid) as makecount, am.makeid as makeid, am.makename as makename, am.make_logo as make_logo";
			$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_customfields as pc on p.rowid = pc.fk_projet";
			$sql.= " LEFT JOIN auto_makes as am on pc.makename = am.makeid";
			$sql.= " WHERE p.ref LIKE 'FR%'";
			$sql.= " AND p.fk_statut = 1";
			$sql.= " GROUP BY makeid";
			$sql.= " ORDER BY makename";
			
			$resql = $db->query($sql);
			$carmakes = array();
			$num = 0;
			
			if ($resql)
			{
				$nummakes = $db->num_rows($resql);
				for ($i = 0 ; $i < $nummakes; $i++)
					{
						$row = $db->fetch_object($resql);
						$carmakes[$i] = $row;
						$num += $row->makecount;
					}
				$db->free($resql);
			}
			else
			{
				dol_print_error($db);
			}
/*
print '$carmakes : <br/>';
print_r ($carmakes);
*/
			/*
			 * View
			 */
			
			$title = $langs->trans("Open Project Colour Code Matrix");
			llxHeader("",$title,"EN:Module_Projects|FR:Module_Projets|ES:M&oacute;dulo_Proyectos");

			// Show description of content
			$text=$langs->trans("Colour-Code Matrix for Open Projects");
			$desc='This table represents all Open (not Draft nor Closed) customer projects.<br/><br/>';
			print_barre_liste($text, '', $_SERVER["PHP_SELF"], '', '', '', '', $num, $num, 'title_project', 0, '', '', 0, 1);
			
			// Table
//			print '<div class="div-table-responsive">';
//			print '<table class="tagtable liste" border = "1" bordercolor = "silver">'."\n";
			print '<table class="border">'."\n";
			
/*			print '<tr class="liste_titre">';
			print '<td colspan="8">';
			print_barre_liste($text, '', $_SERVER["PHP_SELF"], '', '', '', '', $num, $num, 'title_project', 0, '', '', 0, 1);
			//	print $desc;
			print '</td>';
			print '</tr>';
*/
			print '<tr class="liste_titre">';
			print '<td align = "center" width= "50">Make </td>';
			for ($i = 0 ; $i < $numcols; $i++)
			{
				$textcol = '<font>';
				if ($colcodes[$i]->text_col == 1) $textcol = '<font color = "white">';
				print '<td align="center" bgcolor="'.$colcodes[$i]->hex_code.'" width= "50"><small>'.$textcol.$colcodes[$i]->colourname.'</font></small></td>';
			}
			print '<td align="center" width= "50"><small>Needs Colour allocated</small></td>';

			print '</tr>'."\n";
				
			$var=false;
			$carprojects = array();
			$colmakes = array();

			//Make and Colour Code Matrix
			
			for ($j = 0 ; $j < $nummakes ; $j++)
			{
				for ($k = 0 ; $k <= $numcols ; $k++)
				{
					$sql = "SELECT COUNT(p.rowid) as colcount";
					$sql.= ", am.makename as makename, am.make_logo as make_logo";
					$sql.= ", acc.colourid as colourid, acc.colourname as colourname, acc.hex_code as hex_code, acc.text_col as text_col";
					
					$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
					$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_customfields as pc on p.rowid = pc.fk_projet";
					$sql.= " LEFT JOIN auto_makes as am on pc.makename = am.makeid";
					$sql.= " LEFT JOIN auto_colour_codes as acc on pc.colourname = acc.colourid";
					
					$sql.= " WHERE p.ref LIKE 'FR%'";
					$sql.= " AND p.fk_statut = 1";
					if ($carmakes[$j]->makeid) $sql.= " AND am.makeid = ".$carmakes[$j]->makeid;
					else $sql.= " AND am.makeid IS NULL";
					if ($k == $numcols) $sql.= " AND acc.colourid IS NULL";
					else $sql.= " AND acc.colourid = ".$colcodes[$k]->colourid;
					$sql.= " GROUP BY acc.colourid";
					$sql.= " ORDER BY p.ref";
					$resql = $db->query($sql);
					
					if ($resql)
					{
						$numprojects = $db->num_rows($resql);
						for ($i = 0 ; $i < $numprojects; $i++)
							{
								$row = $db->fetch_object($resql);
								$colmakes[$j][$k] = $row;
							}
						$db->free($resql);
					}
					else
					{
						dol_print_error($db);
					}
				}
//				if ($j == $nummakes-1) print_r ($colmakes);
			}

			for ($j = 0 ; $j < $nummakes ; $j++)
			{
				$makelogo=DOL_DOCUMENT_ROOT.'/custom/autos/img/icons/'.$carmakes[$j]->make_logo;
				if (is_readable($makelogo))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
					$url=false;
					$tmp=dol_getImageSize($makelogo, $url);
					if ($tmp['width'] > 50) $mlwidth = 50;
					else $mlwidth = $tmp['width'];
				}
				
				print '<tr '.$bc[$var].'>';
//				print '<td valign = "middle" colspan = "'.($numcols + 3).'">'.img_picto($carmakes[$j]->makename, '/custom/autos/img/icons/'.$carmakes[$j]->make_logo,'',1,0,0).'<br/><b>'.$carmakes[$j]->makename.'</b></td>';
				if ($carmakes[$j]->makename)
					print '<td align = "center"><img src="/custom/autos/img/icons/'.$carmakes[$j]->make_logo.'" border="0" title="'.$carmakes[$j]->makename.'" width = "'.$mlwidth.'"><br/><small><b>'.$carmakes[$j]->makename.'</b></small></td>';
				else
					print '<td align = "center"><small><b>Needing Make to be allocated</b></small></td>';

/*				print '</tr>';
				$var=!$var;

				print "<tr ".$bc[$var].">";
//				print "<tr>";
				print '<td> </td>';
*/
								
				//Project Details

				for ($k = 0 ; $k <= $numcols ; $k++)
				{
					if ($colmakes[$j][$k]->colcount > 0)
					{
						$sql = "SELECT p.rowid as projectid, p.ref, p.title as label";
						$sql.= ", am.makename as makename, am.make_logo as make_logo";
						$sql.= ", acc.colourid as colourid, acc.colourname as colourname, acc.hex_code as hex_code, acc.text_col as text_col";
				        $sql.= ", s.rowid as thirdparty_id, s.nom as thirdparty_name";
						
						$sql.= " FROM ".MAIN_DB_PREFIX."projet as p";
        				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON p.fk_soc = s.rowid";
						$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_customfields as pc ON p.rowid = pc.fk_projet";
						$sql.= " LEFT JOIN auto_makes as am on pc.makename = am.makeid";
						$sql.= " LEFT JOIN auto_colour_codes as acc on pc.colourname = acc.colourid";
						
						$sql.= " WHERE p.ref LIKE 'FR%'";
						$sql.= " AND p.fk_statut = 1";
						if ($carmakes[$j]->makeid) $sql.= " AND am.makeid = ".$carmakes[$j]->makeid;
						else $sql.= " AND am.makeid IS NULL";
						if ($k == $numcols) $sql.= " AND acc.colourid IS NULL";
						else $sql.= " AND acc.colourid = ".$colcodes[$k]->colourid;
						$sql.= " ORDER BY p.ref";
						
						$resql = $db->query($sql);
						
						if ($resql)
						{
							$numprojects = $db->num_rows($resql);
							print '<td class="nowrap" align="center" bgcolor="'.$colmakes[$j][$k]->hex_code.'"><small>';
							for ($i = 0 ; $i < $numprojects; $i++)
								{

									$project = new Project($db);
									$row = $db->fetch_object($resql);
									$carprojects[$i] = $row;
									$project->id = $carprojects[$i]->projectid;
									$project->ref = $carprojects[$i]->ref;
									$project->label = $carprojects[$i]->label;
									$project->thirdparty_name = $carprojects[$i]->thirdparty_name;
									$textcol = '<span>';
									if ($colmakes[$j][$k]->text_col == 1) $textcol = '<span style="background-color: white">';
									print $textcol.$project->getNomUrl(0,'',0,'<b>Description: </b>'.$carprojects[$i]->label).'</span><br/>';
								}
							print '</small></td>';
							$db->free($resql);
						}
						else dol_print_error($db);
					}
					else
					{
						print '<td border = "1"></td>';
					}
				}

				print "</tr>\n";
			}
			
			print "</table>\n";
//			print '</div>';

//			print_r ($project);
//			print_r ($carprojects);

/*
* Documents generes
*/

/*
print '<div class="fichecenter"><div class="fichehalfleft">';
print '<a href="#builddoc" name="builddoc"></a>'; // ancre

$filename='Reports';
$filedir='/home/finchmc/public_html/erp/documents/projet/Reports';
$urlsource=$_SERVER["PHP_SELF"].'#builddoc';
$genallowed=1;
$delallowed=1;
$modelpdf=open_projects_report;

$var=true;

// print $formfile->showdocuments('project',$filename,$filedir,$urlsource,$genallowed,$delallowed,$modelpdf);

print '</div><div class="fichehalfright"><div class="ficheaddleft">';

print '</div></div></div>';
*/

llxFooter();

$db->close();
