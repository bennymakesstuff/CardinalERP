<?php
/* Copyright (C) 2001-2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Marc Bariley / Ocebo <marc@ocebo.com>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2013      Cédric Salvador      <csalvador@gpcsolutions.fr>
 * Copyright (C) 2015 	   Claudio Aschieri     <c.aschieri@19.coop>
 * Copyright (C) 2018 	   Peter Roberts			<office@finchmc.com.au>
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
 *	\brief      Page to list time spent on a task with export option
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

$langs->load('projects');
$langs->load("exports");
$langs->load("other");

$langs->load("companies");
$langs->load("projects");
$langs->load('commercial');

$id=GETPOST('id','int');
$projectid=GETPOST('projectid','int');
$ref=GETPOST('ref','alpha');
$action=GETPOST('action','alpha');
$model=GETPOST('model','alpha');
$confirm=GETPOST('confirm','alpha');
$withproject=GETPOST('withproject','int');
$project_ref=GETPOST('project_ref','alpha');
/*
$search_dateday=GETPOST('search_dateday');
$search_datemonth=GETPOST('search_datemonth');
$search_dateyear=GETPOST('search_dateyear');
$search_datehour='';
$search_datewithhour='';
$search_note=GETPOST('search_note','alpha');
$search_duration=GETPOST('search_duration','int');
$search_value=GETPOST('search_value','int');
$search_task_ref=GETPOST('search_task_ref','alpha');
$search_task_label=GETPOST('search_task_label','alpha');
$search_user=GETPOST('search_user','int');
*/
// Security check
$socid=0;
//if ($user->societe_id > 0) $socid = $user->societe_id;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
if (!$user->rights->projet->lire) accessforbidden();

/*
$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if (empty($page) || $page == -1) { $page = 0; }     // If $page is not defined, or '' or -1
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield='t.task_date,t.task_datehour,t.rowid';
if (! $sortorder) $sortorder='DESC';
*/
// Selected Fields Array
$array_selected = array(						
	"ptt.rowid"				=>	1,
	"u.employeename"		=>	2,
//	"u.lastname"			=>	3,
	"ptt.task_datehour"		=>	3,
	"ptt.note"				=>	4,
	"ptt.task_duration"		=>	5,
	"s.nom"					=>	6,
	"p.ref"					=>	7,
	"p.title"				=>	8,
//	"pt.rowid"				=>	4,
	"pt.ref"				=>	9,
	"pt.label"				=>	10,
//	"pt.description"		=>	7,
	"pt.duration_effective"	=>	11,
	"pt.planned_workload"	=>	12,
	"pt.progress"			=>	13
//	"ptt.fk_user"			=>	15
);

$array_filtervalue = array ();

$array_labels = array(	
	"ptt.rowid"				=>	'Time ID',
	"u.employeename"		=>	'Employee',
//	"u.lastname"			=>	'Emp. Lastname',
	"ptt.task_datehour"		=>	'Start Time',
	"ptt.note"				=>	'Note',
	"ptt.task_duration"		=>	'Time Spent (hrs)',
	"s.nom"					=>	'Customer name',
	"p.ref"					=>	'Project No.',
	"p.title"				=>	'Project Title',
//	"pt.rowid"				=>	'Ref. task',
	"pt.ref"				=>	'Work Order',
	"pt.label"				=>	'Work Order Title',
//	"pt.description"		=>	'Task description',
	"pt.duration_effective"	=>	'Hours Used',
	"pt.planned_workload"	=>	'Hours Planned',
	"pt.progress"			=>	'Progress %'
//	"ptt.fk_user"			=>	'User'
);

$array_types = array(	
	"ptt.rowid"				=>	"Numeric",
	"u.employeename"		=>	"TextAuto",
	"u.lastname"			=>	"TextAuto",
	"ptt.task_datehour"		=>	"Time3",
	"ptt.note"				=>	"Text",
	"ptt.task_duration"		=>	"Duree",
	"s.nom"					=>	"TextAuto",
	"p.ref"					=>	"TextAuto",
	"p.title"				=>	"TextAuto",
//	"pt.rowid"				=>	"Numeric",
	"pt.ref"				=>	"TextAuto",
	"pt.label"				=>	"TextAuto",
//	"pt.description"		=>	"Text",
	"pt.duration_effective"	=>	"Time3",
	"pt.planned_workload"	=>	"Time3",
	"pt.progress"			=>	"Percent"
//	"ptt.fk_user"			=>	"List:user:CONCAT(u_lastname,' ',u_firstname)",
);


$sqlquery  = "SELECT ";
$sqlquery .= " s.rowid as s_rowid, s.nom as s_nom,";
$sqlquery .= " p.rowid as p_rowid, p.ref as p_ref, p.title as p_title,";
//extra.vehicleyear as extra_vehicleyear, extra.vehiclemodel as extra_vehiclemodel, extra.makename as extra_makename, 
$sqlquery .= " pt.rowid as pt_rowid, pt.ref as pt_ref, pt.label as pt_label,";
$sqlquery .= " pt.description as pt_description,";
$sqlquery .= " ROUND((pt.duration_effective / 3600),2) as pt_duration_effective,";
$sqlquery .= " ROUND((pt.planned_workload / 3600),2) as pt_planned_workload,";
$sqlquery .= " pt.progress as pt_progress,";
$sqlquery .= " ptt.rowid as ptt_rowid, ptt.task_datehour as ptt_task_datehour,";
$sqlquery .= " ROUND((ptt.task_duration / 3600),2) as ptt_task_duration,";
$sqlquery .= " ptt.fk_user as ptt_fk_user, ptt.note as ptt_note,";
$sqlquery .= " u.rowid as u_rowid, CONCAT( u.firstname, ' ', u.lastname) as u_employeename";

//pcf.makename as pcf_makename, pcf.colourname as pcf_colourname 
$sqlquery .= " FROM doli_projet as p";
//LEFT JOIN doli_projet_customfields as pcf ON p.rowid = pcf.fk_projet 
//LEFT JOIN doli_projet_extrafields as extra ON p.rowid = extra.fk_object 
//LEFT JOIN doli_c_lead_status as cls ON p.fk_opp_status = cls.rowid 
$sqlquery .= " LEFT JOIN doli_projet_task as pt ON p.rowid = pt.fk_projet";
//LEFT JOIN doli_projet_task_extrafields as extra2 ON pt.rowid = extra2.fk_object 
$sqlquery .= " LEFT JOIN doli_projet_task_time as ptt ON pt.rowid = ptt.fk_task";
$sqlquery .= " LEFT JOIN doli_societe as s ON p.fk_soc = s.rowid";
$sqlquery .= " LEFT JOIN doli_user as u ON ptt.fk_user = u.rowid";
$sqlquery .= " WHERE p.entity = 1 and pt.rowid=".$id;
$sqlquery .= " ORDER BY u_employeename, ptt_task_datehour";


$objexport=new Export($db);
$objexport->load_arrays(1,'projet_1');
$objmodelexport=new ModeleExports($db);
$form = new Form($db);
$formother = new FormOther($db);
$htmlother = new FormOther($db);
$formfile = new FormFile($db);
$sqlusedforexport='';



// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array('projecttaskcard','globalcard'));

$object = new Task($db);
$projectstatic = new Project($db);
$extrafields_project = new ExtraFields($db);
$extrafields_task = new ExtraFields($db);

if ($projectid > 0 || ! empty($ref))
{
    // fetch optionals attributes and labels
    $extralabels_projet=$extrafields_project->fetch_name_optionals_label($projectstatic->table_element);
}
$extralabels_task=$extrafields_task->fetch_name_optionals_label($object->table_element);


/*
 * Actions
 */

$parameters=array('socid'=>$socid, 'projectid'=>$projectid);
$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

/*
// Purge search criteria
if (GETPOST('button_removefilter_x','alpha') || GETPOST('button_removefilter.x','alpha') ||GETPOST('button_removefilter','alpha')) // All tests are required to be compatible with all browsers
{
    $search_date='';
    $search_datehour='';
    $search_datewithhour='';
    $search_note='';
    $search_duration='';
    $search_value='';
    $search_date_creation='';
    $search_date_update='';
    $search_task_ref='';
    $search_task_label='';
    $search_user=0;
    $toselect='';
    $search_array_options=array();
    $action='';
}

*/


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

//    $userWrite = $projectstatic->restrictedProjectArea($user,'write');

	$filename=dol_sanitizeFileName($projectstatic->ref). "-". dol_sanitizeFileName($object->ref);
	$dirname=dol_sanitizeFileName($projectstatic->ref). "/". dol_sanitizeFileName($object->ref);
	$filedir=$conf->projet->dir_output . "/" . dol_sanitizeFileName($projectstatic->ref). "/" .dol_sanitizeFileName($object->ref);
	$urlsource=$_SERVER["PHP_SELF"]."?id=".$object->id;
//	$genallowed=($user->rights->projet->lire);
//	$delallowed=($user->rights->projet->creer);

	// Build doc
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
	if ($action == 'builddoc')
	{
		$max_execution_time_for_export = (empty($conf->global->EXPORT_MAX_EXECUTION_TIME)?300:$conf->global->EXPORT_MAX_EXECUTION_TIME);    // 5mn if not defined
		$max_time = @ini_get("max_execution_time");
		if ($max_time && $max_time < $max_execution_time_for_export)
		{
			@ini_set("max_execution_time", $max_execution_time_for_export); // This work only if safe mode is off. also web servers has timeout of 300
		}
	
		// Build export file
		//$result=$objexport->build_file_tsr($user, GETPOST('model','alpha'), $datatoexport, $array_selected, $array_filtervalue, $sqlquery);
		
		$indice=0;
		asort($array_selected);


		// Creation of class to export using model ExportXXX
		$dir = DOL_DOCUMENT_ROOT . "/core/modules/export/";
		$file = "export_".$model.".modules.php";
		$classname = "Export".$model;
		require_once $dir.$file;
		$objmodel = new $classname($db);

/*
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
*/
		$sql = $sqlquery;

		//dol_syslog(get_class($this)."::".__FUNCTION__." ".$model.", ".$datatoexport.", ".implode(",", $array_selected));

		// Check parameters or context properties
		if (empty($db->array_export_fields) || ! is_array($db->array_export_fields))
		{
			$db->error="ErrorBadParameter";
			$result = -1;
		//	return -1;
		}

		// Run the sql
		$db->sqlusedforexport=$sql;
//		dol_syslog(get_class($this)."::".__FUNCTION__."", LOG_DEBUG);
		$resql = $db->query($sql);

		if ($resql)
		{
			$filename.= "-Timespent";
			$filename.='.'.$objmodel->getDriverExtension();

			$outputlangs = clone $langs; // We clone to have an object we can modify (for example to change output charset by csv handler) without changing original value

			// Open file
			dol_mkdir($filedir);
			$result=$objmodel->open_file($filedir."/".$filename, $outputlangs);

			if ($result >= 0)
			{
				// Genere en-tete
				$objmodel->write_header($outputlangs);

				// Genere ligne de titre
//				$objmodel->write_title($this->array_export_fields[$indice],$array_selected,$outputlangs,$this->array_export_TypeFields[$indice]);
				$objmodel->write_title($array_labels,$array_selected,$outputlangs,$array_types);

//				$obj = $db->fetch_object($resql);
//				$objmodel->write_record($array_selected,$obj,$outputlangs,$array_types);
//				$objmodel->write_title($array_labels,$array_selected,$outputlangs,$array_types);
				
				$var=true;
				$i = 0;
				while ($obj = $db->fetch_object($resql))
				{
/*					// Process special operations
*/
					$objmodel->write_record($array_selected,$obj,$outputlangs,$array_types);
				}
				// Genere en-tete
				$objmodel->write_footer($outputlangs);
				// Close file
				$objmodel->close_file();
			//	return 1;
			}
			else
			{
				$error=$objmodel->error;
				dol_syslog("Export::build_file Error: ".$error, LOG_ERR);
			//	return -1;
				$result = -1;
			}
		}
		else
		{
		//	$this->error=$this->db->error()." - sql=".$sql;
		//	return -1;
			$result = -1;
		}
		
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
	}
}
/*

if ($action == 'addtimespent' && $user->rights->projet->lire)
{
	$error=0;

	$timespent_durationhour = GETPOST('timespent_durationhour','int');
	$timespent_durationmin = GETPOST('timespent_durationmin','int');
	if (empty($timespent_durationhour) && empty($timespent_durationmin))
	{
		setEventMessages($langs->trans('ErrorFieldRequired',$langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error++;
	}
	if (empty($_POST["userid"]))
	{
		$langs->load("errors");
		setEventMessages($langs->trans('ErrorUserNotAssignedToTask'), null, 'errors');
		$error++;
	}

	if (! $error)
	{
		$object->fetch($id, $ref);
		$object->fetch_projet();

		if (empty($object->projet->statut))
		{
			setEventMessages($langs->trans("ProjectMustBeValidatedFirst"), null, 'errors');
			$error++;
		}
		else
		{
			$object->timespent_note = $_POST["timespent_note"];
			$object->progress = GETPOST('progress', 'int');
			$object->timespent_duration = $_POST["timespent_durationhour"]*60*60;	// We store duration in seconds
			$object->timespent_duration+= $_POST["timespent_durationmin"]*60;		// We store duration in seconds
	        if (GETPOST("timehour") != '' && GETPOST("timehour") >= 0)	// If hour was entered
	        {
				$object->timespent_date = dol_mktime(GETPOST("timehour"),GETPOST("timemin"),0,GETPOST("timemonth"),GETPOST("timeday"),GETPOST("timeyear"));
				$object->timespent_withhour = 1;
	        }
	        else
			{
				$object->timespent_date = dol_mktime(12,0,0,GETPOST("timemonth"),GETPOST("timeday"),GETPOST("timeyear"));
			}
			$object->timespent_fk_user = $_POST["userid"];
			$result=$object->addTimeSpent($user);
			if ($result >= 0)
			{
				setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
			}
			else
			{
				setEventMessages($langs->trans($object->error), null, 'errors');
				$error++;
			}
		}
	}
	else
	{
		$action='';
	}
}

if ($action == 'updateline' && ! $_POST["cancel"] && $user->rights->projet->lire)
{
	$error=0;

	if (empty($_POST["new_durationhour"]) && empty($_POST["new_durationmin"]))
	{
		setEventMessages($langs->trans('ErrorFieldRequired',$langs->transnoentitiesnoconv("Duration")), null, 'errors');
		$error++;
	}

	if (! $error)
	{
		$object->fetch($id, $ref);
		// TODO Check that ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))

		$object->timespent_id = $_POST["lineid"];
		$object->timespent_note = $_POST["timespent_note_line"];
		$object->timespent_old_duration = $_POST["old_duration"];
		$object->timespent_duration = $_POST["new_durationhour"]*60*60;	// We store duration in seconds
		$object->timespent_duration+= $_POST["new_durationmin"]*60;		// We store duration in seconds
        if (GETPOST("timelinehour") != '' && GETPOST("timelinehour") >= 0)	// If hour was entered
        {
			$object->timespent_date = dol_mktime(GETPOST("timelinehour"),GETPOST("timelinemin"),0,GETPOST("timelinemonth"),GETPOST("timelineday"),GETPOST("timelineyear"));
			$object->timespent_withhour = 1;
        }
        else
		{
			$object->timespent_date = dol_mktime(12,0,0,GETPOST("timelinemonth"),GETPOST("timelineday"),GETPOST("timelineyear"));
		}
		$object->timespent_fk_user = $_POST["userid_line"];

		$result=$object->updateTimeSpent($user);
		if ($result >= 0)
		{
			setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
		}
		else
		{
			setEventMessages($langs->trans($object->error), null, 'errors');
			$error++;
		}
	}
	else
	{
		$action='';
	}
}

if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->lire)
{
	$object->fetchTimeSpent($_GET['lineid']);
	// TODO Check that ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))
	$result = $object->delTimeSpent($user);

	if ($result < 0)
	{
		$langs->load("errors");
		setEventMessages($langs->trans($object->error), null, 'errors');
		$error++;
		$action='';
	}
}
*/

// Retreive First Task ID of Project if withprojet is on to allow project prev next to work
if (! empty($project_ref) && ! empty($withproject))
{
	if ($projectstatic->fetch(0,$project_ref) > 0)
	{
		$tasksarray=$object->getTasksArray(0, 0, $projectstatic->id, $socid, 0);
		if (count($tasksarray) > 0)
		{
			$id=$tasksarray[0]->id;
		}
		else
		{
			header("Location: ".DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.($withproject?'&withproject=1':'').(empty($mode)?'':'&mode='.$mode));
			exit;
		}
	}
}

// To show all time lines for project
$projectidforalltimes=0;
if (GETPOST('projectid'))
{
    $projectidforalltimes=GETPOST('projectid','int');

}


/*
 * View
 */
//llxHeader("",$langs->trans("Task"));
llxHeader("",$langs->trans("TimeSpent"));

$form = new Form($db);
$formother = new FormOther($db);
$userstatic = new User($db);

if (($id > 0 || ! empty($ref)) || $projectidforalltimes > 0)
{
	/*
	 * Fiche projet en mode visu
	 * Project sheet in visual mode
 	 */
    if ($projectidforalltimes)
    {
        $result=$projectstatic->fetch($projectidforalltimes);
        if (! empty($projectstatic->socid)) $projectstatic->fetch_thirdparty();
        $res=$projectstatic->fetch_optionals($object->id,$extralabels_projet);
    }
    elseif ($object->fetch($id, $ref) >= 0)
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

			if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))
			{
    			print '<div class="tabsAction">';

    			if ($user->rights->projet->all->creer || $user->rights->projet->creer)
    			{
    			    if ($object->public || $userWrite > 0)
    			    {
    			        print '<a class="butAction" href="'.$_SERVER['PHP_SELF'].'?id='.$object->id.'&action=create'.$param.'&backtopage='.urlencode($_SERVER['PHP_SELF'].'?id='.$object->id).'">'.$langs->trans('AddTask').'</a>';
    			    }
    			    else
    			    {
    			        print '<a class="butActionRefused" href="#" title="'.$langs->trans("NotOwnerOfProject").'">'.$langs->trans('AddTask').'</a>';
    			    }
    			}
    			else
    			{
    			    print '<a class="butActionRefused" href="#" title="'.$langs->trans("NotEnoughPermissions").'">'.$langs->trans('AddTask').'</a>';
    			}

    			print '</div>';
			}
			else
			{
				print '<br>';
			}
		}
	}

	if (empty($projectidforalltimes))
	{
		$head=task_prepare_head($object);
		dol_fiche_head($head, 'task_time', $langs->trans("Task"), -1, 'projecttask');

/*
		if ($action == 'deleteline')
		{
			print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id.'&lineid='.$_GET["lineid"].($withproject?'&withproject=1':''),$langs->trans("DeleteATimeSpent"),$langs->trans("ConfirmDeleteATimeSpent"),"confirm_delete",'','',1);
		}

*/
		$param=($withproject?'&withproject=1':'');
		$linkback=$withproject?'<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'">'.$langs->trans("BackToList").'</a>':'';

		if (! GETPOST('withproject','alpha') || empty($projectstatic->id))
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


		/*
		 * Form to add time spent
		 */
/*
		if ($user->rights->projet->lire)
		{
			print '<br>';

			print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';
			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			print '<input type="hidden" name="action" value="addtimespent">';
			print '<input type="hidden" name="id" value="'.$object->id.'">';
			print '<input type="hidden" name="withproject" value="'.$withproject.'">';

			print '<table class="noborder nohover" width="100%">';

			print '<tr class="liste_titre">';
			print '<td>'.$langs->trans("Date").'</td>';
			print '<td>'.$langs->trans("By").'</td>';
			print '<td>'.$langs->trans("Note").'</td>';
			print '<td>'.$langs->trans("NewTimeSpent").'</td>';
			print '<td>'.$langs->trans("ProgressDeclared").'</td>';
			print '<td></td>';
			print "</tr>\n";

			print '<tr class="oddeven">';

			// Date
			print '<td class="maxwidthonsmartphone">';
			//$newdate=dol_mktime(12,0,0,$_POST["timemonth"],$_POST["timeday"],$_POST["timeyear"]);
			$newdate='';
			print $form->select_date($newdate, 'time', ($conf->browser->layout == 'phone'?2:1), 1, 2, "timespent_date", 1, 0, 1);
			print '</td>';

			// Contributor
			print '<td class="maxwidthonsmartphone">';
			print img_object('','user','class="hideonsmartphone"');
			$contactsoftask=$object->getListContactId('internal');
			if (count($contactsoftask)>0)
			{
				$userid=$contactsoftask[0];
				print $form->select_dolusers((GETPOST('userid')?GETPOST('userid'):$userid), 'userid', 0, '', 0, '', $contactsoftask, 0, 0, 0, '', 0, $langs->trans("ResourceNotAssignedToTheTask"), 'maxwidth200');
			}
			else
			{
				print img_error($langs->trans('FirstAddRessourceToAllocateTime')).$langs->trans('FirstAddRessourceToAllocateTime');
			}
			print '</td>';

			// Note
			print '<td>';
			print '<textarea name="timespent_note" class="maxwidth100onsmartphone" rows="'.ROWS_2.'">'.($_POST['timespent_note']?$_POST['timespent_note']:'').'</textarea>';
			print '</td>';

			// Duration - Time spent
			print '<td>';
			print $form->select_duration('timespent_duration', ($_POST['timespent_duration']?$_POST['timespent_duration']:''), 0, 'text');
			print '</td>';

			// Progress declared
			print '<td class="nowrap">';
			print $formother->select_percent(GETPOST('progress')?GETPOST('progress'):$object->progress,'progress');
			print '</td>';

			print '<td align="center">';
			print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
			print '</td></tr>';

			print '</table></form>';

			print '<br>';
		}
*/
	}

	if ($projectstatic->id > 0)
	{
/*
		if ($action == 'deleteline')
		{
			print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id.'&lineid='.$_GET["lineid"].($withproject?'&withproject=1':''),$langs->trans("DeleteATimeSpent"),$langs->trans("ConfirmDeleteATimeSpent"),"confirm_delete",'','',1);
		}

	    // Initialize technical object to manage hooks. Note that conf->hooks_modules contains array
	    $hookmanager->initHooks(array('tasktimelist'));
	    $extrafields = new ExtraFields($db);

	    // Definition of fields for list
	    $arrayfields=array();
	    $arrayfields['t.task_date']=array('label'=>$langs->trans("Date"), 'checked'=>1);
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
	    {
    	    $arrayfields['t.task_ref']=array('label'=>$langs->trans("RefTask"), 'checked'=>1);
    	    $arrayfields['t.task_label']=array('label'=>$langs->trans("LabelTask"), 'checked'=>1);
	    }
	    $arrayfields['author']=array('label'=>$langs->trans("By"), 'checked'=>1);
	    $arrayfields['t.note']=array('label'=>$langs->trans("Note"), 'checked'=>1);
	    $arrayfields['t.task_duration']=array('label'=>$langs->trans("Duration"), 'checked'=>1);
	    $arrayfields['value']=array('label'=>$langs->trans("Value"), 'checked'=>1, 'enabled'=>$conf->salaries->enabled);
	    // Extra fields
	    if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	    {
	        foreach($extrafields->attribute_label as $key => $val)
	        {
	            $arrayfields["ef.".$key]=array('label'=>$extrafields->attribute_label[$key], 'checked'=>$extrafields->attribute_list[$key], 'position'=>$extrafields->attribute_pos[$key], 'enabled'=>$extrafields->attribute_perms[$key]);
	        }
	    }
*/

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

/*
			if (! empty($projectidforalltimes))
			{
			    $title=$langs->trans("ListTaskTimeUserProject");
			    $linktotasks='<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'">'.$langs->trans("GoToListOfTasks").'</a>';
			    //print_barre_liste($title, 0, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, $linktotasks, $num, $totalnboflines, 'title_generic.png', 0, '', '', 0, 1);
			    print load_fiche_titre($title,$linktotasks,'title_generic.png');
			}
*/
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


//		$arrayofselected=is_array($toselect)?$toselect:array();

		$params='';
/*
		if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
		if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;
		if ($search_note != '') $params.= '&amp;search_note='.urlencode($search_note);
		if ($search_duration != '') $params.= '&amp;search_field2='.urlencode($search_duration);
		if ($optioncss != '') $param.='&optioncss='.$optioncss;
*/
		// Add $param from extra fields
		/*foreach ($search_array_options as $key => $val)
		{
		    $crit=$val;
		    $tmpkey=preg_replace('/search_options_/','',$key);
		    if ($val != '') $param.='&search_options_'.$tmpkey.'='.urlencode($val);
		}*/
		if ($id) $params.='&amp;id='.$id;
		if ($projectid) $params.='&amp;projectid='.$projectid;
		if ($withproject) $params.='&amp;withproject='.$withproject;


		// Show description of content
/*
		$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
		$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
*/
	
        print '<div class="div-table-responsive">';
		print '<table class="tagtable liste">'."\n";
		// Fields title search
		print '<tr class="liste_titre_filter">';
		// Date
		print '<td class="liste_titre">Date</td>';
		// Employee
		print '<td class="liste_titre">Employee</td>';
		// Note
		print '<td class="liste_titre">Note</td>';
		// Duration
		print '<td class="liste_titre">Duration</td>';
		// Action column
		print '<td class="liste_titre center">&nbsp;</td>';

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

			$date1		= $db->jdate($task_time->task_date);
			$date2		= $db->jdate($task_time->task_datehour);
			$datetmp	= dol_getdate(($date2?$date2:$date1));
			$dateday	= $datetmp['mday'];
			$datemonth	= $datetmp['mon'];
			$dateyear	= $datetmp['year'];

			// Date
				print '<td class="nowrap">';
  				print dol_print_date(($date2?$date2:$date1),($task_time->task_date_withhour?'dayhour':'day'));
				print '</td>';
    			if (! $i) $totalarray['nbfield']++;

            // User
				print '<td class="nowrap">';
//                print '<td style="width:100px">';
/*
        			$tasktmp->id = $task_time->fk_task;
        			$tasktmp->ref = $task_time->ref;
        			$tasktmp->label = $task_time->label;
        			print $tasktmp->getNomUrl(1, 'withproject', 'time');
        			print '</td>';
        			if (! $i) $totalarray['nbfield']++;
    			}
            }

			// Task label
            if (! empty($arrayfields['t.task_label']['checked']))
            {
        		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
    			{
        			print '<td class="nowrap">';
        			print $task_time->label;
        			print '</td>';
        			if (! $i) $totalarray['nbfield']++;
    			}
            }

            // User
            if (! empty($arrayfields['author']['checked']))
            {
                print '<td>';
    			if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
			        if (empty($object->id)) $object->fetch($id);
    			    $contactsoftask=$object->getListContactId('internal');
    				if (!in_array($task_time->fk_user,$contactsoftask)) {
    					$contactsoftask[]=$task_time->fk_user;
    				}
    				if (count($contactsoftask)>0) {
    					print img_object('','user','class="hideonsmartphone"');
    					print $form->select_dolusers($task_time->fk_user,'userid_line',0,'',0,'',$contactsoftask);
    				}else {
    					print img_error($langs->trans('FirstAddRessourceToAllocateTime')).$langs->trans('FirstAddRessourceToAllocateTime');
    				}
    			}
    			else
    			{
*/

    				$userstatic->id         = $task_time->fk_user;
    				$userstatic->lastname	= $task_time->lastname;
    				$userstatic->firstname 	= $task_time->firstname;
    				$userstatic->photo      = $task_time->photo;
    				print $userstatic->getNomUrl(-1);
//    			}
//				print $task_time->firstname.' '.$task_time->lastname;
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
//            }

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
            // Action column
			print '<td class="center"">';
    				print '&nbsp;';
    				print '<a href="/custom/timecard/timecard.php'.'?userid='.$userstatic->id.'&amp;year='.$dateyear.'&amp;month='.$datemonth.'&amp;day='.$dateday.'" target="_blank">';
    				print img_view($titlealt = 'New window');
    				print '</a>';
        	print '</td>';
        	if (! $i) $totalarray['nbfield']++;

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


	print '<br/>'.$langs->trans("NowClickToGenerateToBuildExportFile").'<br/>';
	
	print '<div class="fichecenter"><div class="fichehalfleft">';
	print '<a href="#builddoc" name="builddoc"></a>'; // anchor

    $liste=$objmodelexport->liste_modeles($db);
    $listeall=$liste;



	/*
	* Generated documents
	*/


	$var=true;

    if (! is_dir($filedir)) dol_mkdir($filedir);
	
    print $formfile->showdocuments('project',$dirname,$filedir,$urlsource,$liste,1,(! empty($_POST['model'])?$_POST['model']:'excel2007'),1,1);

//	print $formfile->showdocuments('export',$filename,$filedir,$urlsource,$genallowed,$delallowed,$object->modelpdf);


//$upload_dir = '/home/finchmc/public_html/erp/documents/projet/Reports';



    // Poster list of documents
    // NB: The function show_documents rescues the modules qd genallowed = 1, otherwise takes $liste
//    print $formfile->showdocuments('export','',$filedir,$urlsource,$liste,1,(! empty($_POST['model'])?$_POST['model']:'excel2007'),1,1);

//    print '</div>';


print '</div><div class="fichehalfright"><div class="ficheaddleft">';

	// List of available export formats

	$var=true;
	print '<div class="titre">Available Formats</div>';
	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
    print '<td colspan="2">'.$langs->trans("AvailableFormats").'</td>';
    print '<td>'.$langs->trans("LibraryUsed").'</td>';
    print '<td align="right">'.$langs->trans("LibraryVersion").'</td>';
    print '</tr>'."\n";


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

print '</div></div></div>';

	}
}

llxFooter();
$db->close();
