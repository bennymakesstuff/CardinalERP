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
 *	\file       htdocs/custom/timecard/timespentreport.php
 *	\ingroup    projet
 *	\brief      Page to list timespent on a task with clean format
 */

require '../../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/exports/class/export.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/modules/export/modules_export.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
/*
require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
require_once DOL_DOCUMENT_ROOT.'/categories/class/categorie.class.php';
*/
if (!$user->rights->projet->lire) accessforbidden();

$langs->load("exports");
$langs->load("other");

$langs->load("companies");
$langs->load("projects");
$langs->load('commercial');

$id=GETPOST('id','int');
$ref=GETPOST('ref','alpha');
$object = new Task($db);
$projectstatic = new Project($db);
$extrafields_project = new ExtraFields($db);
$extrafields_task = new ExtraFields($db);

$datatoexport='fred';

// Selected Fields Array
$array_selected = array(
	‘s.nom’					=>	1,
	‘p.ref’					=>	2,
	‘p.title’				=>	3,
	‘pt.rowid’				=>	4,
	‘pt.ref’				=>	5,
	‘pt.label’				=>	6,
	‘pt.description’		=>	7,
	‘pt.duration_effective’	=>	8,
	‘pt.planned_workload’	=>	9,
	‘pt.progress’			=>	10,
	‘ptt.rowid’				=>	11,
	‘ptt.task_datehour’		=>	12,
	‘ptt.task_duration’		=>	13,
	‘ptt.fk_user’			=>	14,
	‘ptt.note’				=>	15
);
$array_filtervalue = array ();


$sql = "SELECT t.rowid, t.fk_task, t.task_date, t.task_datehour, t.task_date_withhour, t.task_duration, t.fk_user, t.note, t.thm,";
		$sql .= " pt.ref, pt.label,";

$sqlquery  = "SELECT DISTINCT";
$sqlquery .= "s.rowid as s_rowid, s.nom as s_nom,";
$sqlquery .= "p.rowid as p_rowid, p.ref as p_ref, p.title as p_title,";
//extra.vehicleyear as extra_vehicleyear, extra.vehiclemodel as extra_vehiclemodel, extra.makename as extra_makename,
$sqlquery .= "pt.rowid as pt_rowid, pt.ref as pt_ref, pt.label as pt_label,";
$sqlquery .= "pt.description as pt_description,";
$sqlquery .= "pt.duration_effective as pt_duration_effective,";
$sqlquery .= "pt.planned_workload as pt_planned_workload,";
$sqlquery .= "pt.progress as pt_progress,";
$sqlquery .= "ptt.rowid as ptt_rowid, ptt.task_datehour as ptt_task_datehour,";
$sqlquery .= "ptt.task_duration as ptt_task_duration,";
$sqlquery .= "ptt.fk_user as ptt_fk_user,ptt.note as ptt_note,";
//pcf.makename as pcf_makename, pcf.colourname as pcf_colourname
$sqlquery .= "FROM doli_projet as p";
//LEFT JOIN doli_projet_customfields as pcf ON p.rowid = pcf.fk_projet
//LEFT JOIN doli_projet_extrafields as extra ON p.rowid = extra.fk_object
//LEFT JOIN doli_c_lead_status as cls ON p.fk_opp_status = cls.rowid
$sqlquery .= "LEFT JOIN doli_projet_task as pt ON p.rowid = pt.fk_projet";
//LEFT JOIN doli_projet_task_extrafields as extra2 ON pt.rowid = extra2.fk_object
$sqlquery .= "LEFT JOIN doli_projet_task_time as ptt ON pt.rowid = ptt.fk_task";
$sqlquery .= "LEFT JOIN doli_societe as s ON p.fk_soc = s.rowid";
$sqlquery .= "WHERE p.entity = 1 and pt.rowid=".$id;

$action=GETPOST('action', 'alpha');

$objexport=new Export($db);
$objexport->load_arrays($user,$datatoexport);

$objmodelexport=new ModeleExports($db);
$form = new Form($db);
$formother = new FormOther($db);
$htmlother = new FormOther($db);
$formfile = new FormFile($db);
$sqlusedforexport='';



//$userstatic = new User($db);

$parameters=array('socid'=>$socid, 'projectid'=>$projectid);

//$modelpdf='open_projects_report';


/*
 * Actions
 */

if ($action=='selectfield')     // Selection of field at step 2
{
}
if ($action=='unselectfield')
{
}

if ($action=='downfield' || $action=='upfield')
{
}

if ($step == 1 || $action == 'cleanselect')
{
}

	// Build doc
if ($action == 'builddoc')
{
    $max_execution_time_for_export = (empty($conf->global->EXPORT_MAX_EXECUTION_TIME)?300:$conf->global->EXPORT_MAX_EXECUTION_TIME);    // 5mn if not defined
    $max_time = @ini_get("max_execution_time");
    if ($max_time && $max_time < $max_execution_time_for_export)
    {
        @ini_set("max_execution_time", $max_execution_time_for_export); // This work only if safe mode is off. also web servers has timeout of 300
    }

    // Build export file
	$result=$objexport->build_file_tsr($user, GETPOST('model','alpha'), $datatoexport, $array_selected, $array_filtervalue, $sqlquery);
	if ($result < 0)
	{
		setEventMessages($objexport->error, $objexport->errors, 'errors');
		$sqlusedforexport=$objexport->sqlusedforexport;
	}
	else
	{
		setEventMessages($langs->trans("FileSuccessfullyBuilt"), null, 'mesgs');
	    $sqlusedforexport=$objexport->sqlusedforexport;
    }

/*
	// Save last template used to generate document
//	if (GETPOST('model')) $object->setDocModel($user, GETPOST('model','alpha'));

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
*/
}

// Delete file
if ($step == 5 && $action == 'confirm_deletefile' && $confirm == 'yes')
{
}

if ($action == 'deleteprof')
{
}
if ($action == 'add_export_model')
{

}

// Reload an predefined export model
if ($step == 2 && $action == 'select_model')
{
}

// Get form with filters
if ($step == 4 && $action == 'submitFormField')
{

}


/*
 * View
 */

llxHeader("",$langs->trans("TimeSpent"));



if ($id > 0 || ! empty($ref))
{
	/*
	 * Project sheet in visual mode
 	 */
    if ($object->fetch($id, $ref) >= 0)
	{
		$result=$projectstatic->fetch($object->fk_project);
		if (! empty($projectstatic->socid)) $projectstatic->fetch_thirdparty();
		$res=$projectstatic->fetch_optionals($object->id,$extralabels_projet);

		$object->project = clone $projectstatic;
    }

    $userWrite = $projectstatic->restrictedProjectArea($user,'write');

	if ($projectstatic->id > 0)
	{
		if ($withproject)
		{
			// Tabs for project
			$tab='tasks';
			$head=project_prepare_head($projectstatic);
			dol_fiche_head($head, $tab, $langs->trans("Project"), -1, ($projectstatic->public?'projectpub':'project'));

			$param=($mode=='mine'?'&mode=mine':'');

			// Project card

            $linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php?restore_lastsearch_values=1">'.$langs->trans("BackToList").'</a>';

            $morehtmlref='<div class="refidno">';
            // Title
            $morehtmlref.=$projectstatic->title;
            // Thirdparty
            if ($projectstatic->thirdparty->id > 0)
            {
                $morehtmlref.='<br>'.$langs->trans('ThirdParty') . ' : ' . $projectstatic->thirdparty->getNomUrl(1, 'project');
            }
            $morehtmlref.='</div>';

            // Define a complementary filter for search of next/prev ref.
            if (! $user->rights->projet->all->lire)
            {
                $objectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,0);
                $projectstatic->next_prev_filter=" rowid in (".(count($objectsListId)?join(',',array_keys($objectsListId)):'0').")";
            }

            dol_banner_tab($projectstatic, 'project_ref', $linkback, 1, 'ref', 'ref', $morehtmlref);

            print '<div class="fichecenter">';
            print '<div class="fichehalfleft">';
            print '<div class="underbanner clearboth"></div>';

            print '<table class="border" width="100%">';

            // Visibility
            print '<tr><td class="titlefield">'.$langs->trans("Visibility").'</td><td>';
            if ($projectstatic->public) print $langs->trans('SharedProject');
            else print $langs->trans('PrivateProject');
            print '</td></tr>';

            // Date start - end
            print '<tr><td>'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
            print dol_print_date($projectstatic->date_start,'day');
            $end=dol_print_date($projectstatic->date_end,'day');
            if ($end) print ' - '.$end;
            print '</td></tr>';

            // Budget
            print '<tr><td>'.$langs->trans("Budget").'</td><td>';
            if (strcmp($projectstatic->budget_amount, '')) print price($projectstatic->budget_amount,'',$langs,1,0,0,$conf->currency);
            print '</td></tr>';

            // Other attributes
            $cols = 2;
            //include DOL_DOCUMENT_ROOT . '/core/tpl/extrafields_view.tpl.php';

            print '</table>';

            print '</div>';
            print '<div class="fichehalfright">';
            print '<div class="ficheaddleft">';
            print '<div class="underbanner clearboth"></div>';

            print '<table class="border" width="100%">';

            // Description
            print '<td class="titlefield tdtop">'.$langs->trans("Description").'</td><td>';
            print nl2br($projectstatic->description);
            print '</td></tr>';

            // Categories
            if($conf->categorie->enabled) {
                print '<tr><td valign="middle">'.$langs->trans("Categories").'</td><td>';
                print $form->showCategories($projectstatic->id,'project',1);
                print "</td></tr>";
            }

            print '</table>';

            print '</div>';
            print '</div>';
            print '</div>';

            print '<div class="clearboth"></div>';

			dol_fiche_end();


			/*
			 * Actions
			 */

				print '<br>';
		}
	}

	if (empty($projectidforalltimes))
	{
		$head=task_prepare_head($object);
		dol_fiche_head($head, 'task_time', $langs->trans("Task"), -1, 'projecttask');

		$param=($withproject?'&withproject=1':'');
		$linkback=$withproject?'<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'">'.$langs->trans("BackToList").'</a>':'';

		if (! GETPOST('withproject') || empty($projectstatic->id))
		{
			$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,1);
			$object->next_prev_filter=" fk_projet in (".$projectsListId.")";
		}
		else $object->next_prev_filter=" fk_projet = ".$projectstatic->id;

		$morehtmlref='';

		// Project
		if (empty($withproject))
		{
		    $morehtmlref.='<div class="refidno">';
		    $morehtmlref.=$langs->trans("Project").': ';
		    $morehtmlref.=$projectstatic->getNomUrl(1);
		    $morehtmlref.='<br>';

		    // Third party
	    	$morehtmlref.=$langs->trans("ThirdParty").': ';
	    	if (is_object($projectstatic->thirdparty)) {
	    		$morehtmlref.=$projectstatic->thirdparty->getNomUrl(1);
	    	}
		    $morehtmlref.='</div>';
		}

		dol_banner_tab($object, 'ref', $linkback, 1, 'ref', 'ref', $morehtmlref, $param);

		print '<div class="fichecenter">';
		print '<div class="fichehalfleft">';

        print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';

		// Date start - Date end
		print '<tr><td class="titlefield">'.$langs->trans("DateStart").' - '.$langs->trans("DateEnd").'</td><td>';
		$start = dol_print_date($object->date_start,'dayhour');
		print ($start?$start:'?');
		$end = dol_print_date($object->date_end,'dayhour');
		print ' - ';
		print ($end?$end:'?');
		if ($object->hasDelay()) print img_warning("Late");
		print '</td></tr>';

		// Planned workload
		print '<tr><td>'.$langs->trans("PlannedWorkload").'</td><td>';
		print convertSecondToTime($object->planned_workload,'allhourmin');
		print '</td></tr>';

		print '</table>';
		print '</div>';

		print '<div class="fichehalfright"><div class="ficheaddleft">';

		print '<div class="underbanner clearboth"></div>';
		print '<table class="border" width="100%">';

		// Progress declared
		print '<tr><td class="titlefield">'.$langs->trans("ProgressDeclared").'</td><td>';
		print $object->progress.' %';
		print '</td></tr>';

		// Progress calculated
		print '<tr><td>'.$langs->trans("ProgressCalculated").'</td><td>';
		if ($object->planned_workload)
		{
			$tmparray=$object->getSummaryOfTimeSpent();
			if ($tmparray['total_duration'] > 0) print round($tmparray['total_duration']/$object->planned_workload*100, 2).' %';
			else print '0 %';
		}
		else print '<span class="opacitymedium">'.$langs->trans("WorkloadNotDefined").'</span>';
		print '</td></tr>';

		print '</table>';

		print '</div>';
		print '</div>';

		print '</div>';
		print '<div class="clearboth"></div>';

		dol_fiche_end();
	}

/*
// *********** BELOW IS FOR OPEN PROJECTS REPORT
$socid=161; // the ID for Finch Motor Company
*/
$formfile = new FormFile($db);
// *********** ABOVE IS FOR OPEN PROJECTS REPORT


		/*
		 *  List of time spent
		 */
		$tasks = array();

		$sql = "SELECT t.rowid, t.fk_task, t.task_date, t.task_datehour, t.task_date_withhour, t.task_duration, t.fk_user, t.note, t.thm,";
		$sql .= " pt.ref, pt.label,";
		$sql .= " u.lastname, u.firstname, u.login, u.photo";
		$sql .= " FROM ".MAIN_DB_PREFIX."projet_task_time as t, ".MAIN_DB_PREFIX."projet_task as pt, ".MAIN_DB_PREFIX."user as u";
		$sql .= " WHERE t.fk_user = u.rowid AND t.fk_task = pt.rowid";
		if (empty($projectidforalltimes)) $sql .= " AND t.fk_task =".$object->id;
		else $sql.= " AND pt.fk_projet IN (".$projectidforalltimes.")";
		if ($search_ref) $sql .= natural_search('c.ref', $search_ref);
		if ($search_note) $sql .= natural_search('t.note', $search_note);
		if ($search_task_ref) $sql .= natural_search('pt.ref', $search_task_ref);
		if ($search_task_label) $sql .= natural_search('pt.label', $search_task_label);
		if ($search_user > 0) $sql .= natural_search('t.fk_user', $search_user);
		$sql .= $db->order($sortfield, $sortorder);

		$var=true;
		$resql = $db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$totalnboflines=$num;

			$i = 0;
			while ($i < $num)
			{
				$row = $db->fetch_object($resql);
				$tasks[$i] = $row;
				$i++;
			}
			$db->free($resql);
		}
		else
		{
			dol_print_error($db);
		}

		$params='';

		if ($id) $params.='&amp;id='.$id;


		// Show description of content

        print '<div class="div-table-responsive">';
		print '<table class="tagtable liste">'."\n";
		print '<tr class="liste_titre_filter">';
		// Date
		print '<td class="liste_titre">Date</td>';
		// Employee
		print '<td class="liste_titre">Employee</td>';
		// Note
		print '<td class="liste_titre">Note</td>';
		// Duration
		print '<td class="liste_titre">Duration</td>';
		print '</tr>'."\n";


		$tasktmp = new Task($db);

		$i = 0;

		$childids = $user->getAllChildIds();

		$total = 0;
		$totalvalue = 0;
		$totalarray=array();
		foreach ($tasks as $task_time)
		{
			print '<tr class="oddeven">';

			$date1=$db->jdate($task_time->task_date);
			$date2=$db->jdate($task_time->task_datehour);

			// Date
				print '<td class="nowrap">';
  				print dol_print_date(($date2?$date2:$date1),($task_time->task_date_withhour?'dayhour':'day'));
				print '</td>';
    			if (! $i) $totalarray['nbfield']++;

            // User
				print '<td class="nowrap">';
//                print '<td style="width:100px">';
/*    				$userstatic->id         = $task_time->fk_user;
    				$userstatic->lastname	= $task_time->lastname;
    				$userstatic->firstname 	= $task_time->firstname;
    				$userstatic->photo      = $task_time->photo;
    				print $userstatic->getNomUrl(-1);
*/
				print $task_time->firstname.' '.$task_time->lastname;
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;

			// Note
                print '<td align="left">';
    				print dol_nl2br($task_time->note);
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;

			// Time spent
    			print '<td align="right">';
    				print convertSecondToTime($task_time->task_duration,'allhourmin');
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
    			if (! $i) $totalarray['totaldurationfield']=$totalarray['nbfield'];
    			$totalarray['totalduration'] += $task_time->task_duration;

			print "</tr>\n";
			$i++;
		}

		// Show total line
		    print '<tr class="liste_total">';
		    $i=0;
		    while ($i < $totalarray['nbfield'])
		    {
		        $i++;
		        if ($i == 1)
		        {
		            print '<td align="left">'.$langs->trans("Total").'</td>';
		        }
		        elseif ($totalarray['totaldurationfield'] == $i) print '<td align="right">'.convertSecondToTime($totalarray['totalduration'],'allhourmin').'</td>';
		        elseif ($totalarray['totalvaluefield'] == $i) print '<td align="right">'.price($totalarray['totalvalue']).'</td>';
		        else print '<td></td>';
		    }
		    print '</tr>';

		print "</table>";
		print '</div>';
		print "</form>";
}

	print '<div class="fichecenter"><div class="fichehalfleft">';
	print '<a href="#builddoc" name="builddoc"></a>'; // anchor

	print '<br/>'.$langs->trans("NowClickToGenerateToBuildExportFile").'<br/>';

	// List of available export formats


	$var=true;
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
    print '<td colspan="2">'.$langs->trans("AvailableFormats").'</td>';
    print '<td>'.$langs->trans("LibraryUsed").'</td>';
    print '<td align="right">'.$langs->trans("LibraryVersion").'</td>';
    print '</tr>'."\n";

    $liste=$objmodelexport->liste_modeles($db);
    $listeall=$liste;
    foreach($listeall as $key => $val)
    {
    	if (preg_match('/__\(Disabled\)__/',$listeall[$key]))
    	{
    		$listeall[$key]=preg_replace('/__\(Disabled\)__/','('.$langs->transnoentitiesnoconv("Disabled").')',$listeall[$key]);
    		unset($liste[$key]);
    	}


        print '<tr class="oddeven">';
        print '<td width="16">'.img_picto_common($key,$objmodelexport->getPictoForKey($key)).'</td>';
	    $text=$objmodelexport->getDriverDescForKey($key);
	    $label=$listeall[$key];
	    print '<td>'.$form->textwithpicto($label,$text).'</td>';
        print '<td>'.$objmodelexport->getLibLabelForKey($key).'</td><td align="right">'.$objmodelexport->getLibVersionForKey($key).'</td></tr>'."\n";
    }
    print '</table>';

	/*
	* Generated documents
	*/

	$filename=dol_sanitizeFileName($projectstatic->ref). "/". dol_sanitizeFileName($object->ref);
	$filedir=$conf->projet->dir_output . "/" . dol_sanitizeFileName($projectstatic->ref). "/" .dol_sanitizeFileName($object->ref);
	$urlsource=$_SERVER["PHP_SELF"]."?id=".$object->id;
//	$genallowed=($user->rights->projet->lire);
//	$delallowed=($user->rights->projet->creer);

	$var=true;

    if (! is_dir($filedir)) dol_mkdir($filedir);

    print $formfile->showdocuments('export','',$filedir,$urlsource,$liste,1,(! empty($_POST['model'])?$_POST['model']:'excel2007'),1,1);

//	print $formfile->showdocuments('export',$filename,$filedir,$urlsource,$genallowed,$delallowed,$object->modelpdf);


//$upload_dir = DOL_DOCUMENT_ROOT.'/documents/projet/Reports';



    // Poster list of documents
    // NB: The function show_documents rescues the modules qd genallowed = 1, otherwise takes $liste
//    print $formfile->showdocuments('export','',$filedir,$urlsource,$liste,1,(! empty($_POST['model'])?$_POST['model']:'excel2007'),1,1);

    print '</div>';

/*
* Generated documents
*/
/*
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

*/

llxFooter();

$db->close();

/*
exit;	// don't know why but apache hangs with php 5.3.10-1ubuntu3.12 and apache 2.2.2 if i remove this exit or replace with return
*/

	/**
	 *
	 *      FUNCTIONS
	 *
	 */

	/**
	 *      Build export file.
	 *      File is built into directory $conf->export->dir_temp.'/'.$user->id
	 *      Arrays this->array_export_xxx are already loaded for required datatoexport
	 *
	 *      @param      User		$user               User that export
	 *      @param      string		$model              Export format
	 *      @param      string		$datatoexport       Name of dataset to export
	 *      @param      array		$array_selected     Filter on array of fields to export
	 *      @param      array		$array_filterValue  Filter on array of fields with a filter
	 *      @param		string		$sqlquery			If set, transmit the sql request for select (otherwise, sql request is generated from arrays)
	 *      @return		int								<0 if KO, >0 if OK
	 */
	function build_file_tsr($user, $model, $datatoexport, $array_selected, $array_filterValue, $sqlquery)
 	{
		global $conf,$langs;

		$indice=0;
		asort($array_selected);

		dol_syslog(get_class($this)."::".__FUNCTION__." ".$model.", ".$datatoexport.", ".implode(",", $array_selected));

		// Check parameters or context properties
		if (empty($this->array_export_fields) || ! is_array($this->array_export_fields))
		{
			$this->error="ErrorBadParameter";
			return -1;
		}

		// Creation of class to export using model ExportXXX
		$dir = DOL_DOCUMENT_ROOT . "/core/modules/export/";
		$file = "export_".$model.".modules.php";
		$classname = "Export".$model;
		require_once $dir.$file;
		$objmodel = new $classname($this->db);

		if (! empty($sqlquery)) $sql = $sqlquery;
        else
		{
			// Define value for indice from $datatoexport
			$foundindice=0;
			foreach($this->array_export_code as $key => $dataset)
			{
				if ($datatoexport == $dataset)
				{
					$indice=$key;
					$foundindice++;
					//print "Found indice = ".$indice." for dataset=".$datatoexport."\n";
					break;
				}
			}
			if (empty($foundindice))
			{
				$this->error="ErrorBadParameter can't find dataset ".$datatoexport." into preload arrays this->array_export_code";
				return -1;
			}
        	$sql=$this->build_sql($indice, $array_selected, $array_filterValue);
		}

		// Run the sql
		$this->sqlusedforexport=$sql;
		dol_syslog(get_class($this)."::".__FUNCTION__."", LOG_DEBUG);
		$resql = $this->db->query($sql);
		if ($resql)
		{
			//$this->array_export_label[$indice]
			if ($conf->global->EXPORT_PREFIX_SPEC)
				$filename=$conf->global->EXPORT_PREFIX_SPEC."_".$datatoexport;
			else
				$filename="export_".$datatoexport;
			$filename.='.'.$objmodel->getDriverExtension();
			$dirname=$conf->export->dir_temp.'/'.$user->id;

			$outputlangs = clone $langs; // We clone to have an object we can modify (for example to change output charset by csv handler) without changing original value

			// Open file
			dol_mkdir($dirname);
			$result=$objmodel->open_file($dirname."/".$filename, $outputlangs);

			if ($result >= 0)
			{
				// Genere en-tete
				$objmodel->write_header($outputlangs);

				// Genere ligne de titre
				$objmodel->write_title($this->array_export_fields[$indice],$array_selected,$outputlangs,$this->array_export_TypeFields[$indice]);

				$var=true;

				while ($obj = $this->db->fetch_object($resql))
				{
					// Process special operations
					if (! empty($this->array_export_special[$indice]))
					{
						foreach ($this->array_export_special[$indice] as $key => $value)
						{
							if (! array_key_exists($key, $array_selected)) continue;		// Field not selected
							// Operation NULLIFNEG
							if ($this->array_export_special[$indice][$key]=='NULLIFNEG')
							{
								//$alias=$this->array_export_alias[$indice][$key];
								$alias=str_replace(array('.', '-','(',')'),'_',$key);
								if ($obj->$alias < 0) $obj->$alias='';
							}
							// Operation ZEROIFNEG
							elseif ($this->array_export_special[$indice][$key]=='ZEROIFNEG')
							{
								//$alias=$this->array_export_alias[$indice][$key];
								$alias=str_replace(array('.', '-','(',')'),'_',$key);
								if ($obj->$alias < 0) $obj->$alias='0';
							}
							// Operation INVOICEREMAINTOPAY
							elseif ($this->array_export_special[$indice][$key]=='getRemainToPay')
							{
								//$alias=$this->array_export_alias[$indice][$key];
								$alias=str_replace(array('.', '-','(',')'),'_',$key);
								$remaintopay='';
								if ($obj->f_rowid > 0)
								{
								    global $tmpobjforcomputecall;
								    if (! is_object($tmpobjforcomputecall))
								    {
								        include_once DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php';
								        $tmpobjforcomputecall=new Facture($this->db);
								    }
								    $tmpobjforcomputecall->id = $obj->f_rowid;
								    $tmpobjforcomputecall->total_ttc = $obj->f_total_ttc;
								    $remaintopay=$tmpobjforcomputecall->getRemainToPay();
								}
								$obj->$alias=$remaintopay;
							}
							else
							{
							    // TODO FIXME Export of compute field does not work. $obj containt $obj->alias_field and formulat will contains $obj->field
							    $computestring=$this->array_export_special[$indice][$key];
							    $tmp=dol_eval($computestring, 1, 0);
							    $obj->$alias=$tmp;

							    $this->error="ERROPNOTSUPPORTED. Operation ".$this->array_export_special[$indice][$key]." not supported. Export of 'computed' extrafields is not yet supported, please remove field.";
							    return -1;
							}
						}
					}
					// end of special operation processing
					$objmodel->write_record($array_selected,$obj,$outputlangs,$this->array_export_TypeFields[$indice]);
				}

				// Genere en-tete
				$objmodel->write_footer($outputlangs);

				// Close file
				$objmodel->close_file();

        		return 1;
			}
			else
			{
				$this->error=$objmodel->error;
				dol_syslog("Export::build_file Error: ".$this->error, LOG_ERR);
				return -1;
			}
		}
		else
		{
			$this->error=$this->db->error()." - sql=".$sql;
			return -1;
		}
	}
