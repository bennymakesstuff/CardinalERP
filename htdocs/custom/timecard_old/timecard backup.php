<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2010      François Legastelois <flegastelois@teclib.com>
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
 *	\file       htdocs/projet/activity/perday.php
 *	\ingroup    projet
 *	\brief      List activities of tasks (per day entry)
 */

require ("../../main.inc.php");
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';

/*
include_once DOL_DOCUMENT_ROOT.'/custom/timecard/core/lib/includeMain.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/timecard/core/lib/timecard.lib.php';
require_once DOL_DOCUMENT_ROOT.'/custom/timecard/class/timecardUser.class.php';
*/
//require_once DOL_DOCUMENT_ROOT.'/custom/timecard/class/timecard.class.php';

$langs->load('projects');

//$action=GETPOST('action');
//$mode=GETPOST("mode");
$userid=	GETPOST('userid','int');
$reday=		GETPOST('reday','int');
$remonth=	GETPOST('remonth','int');
$reyear=	GETPOST('reyear','int');

$id=GETPOST('id','int');
$projectid=GETPOST('projectid','int');
$ref=GETPOST('ref','alpha');
$action=GETPOST('action','alpha');
$confirm=GETPOST('confirm','alpha');
$withproject=GETPOST('withproject','int');
$project_ref=GETPOST('project_ref','alpha');

$search_dateday=GETPOST('search_dateday');
$search_datemonth=GETPOST('search_datemonth');
$search_dateyear=GETPOST('search_dateyear');
$search_datehour='';
$search_datewithhour='';
$search_note=GETPOST('search_note','alpha');
$search_duration=GETPOST('search_duration','int');
$search_value=GETPOST('search_value','int');

/*
$taskid=GETPOST('taskid');

$mine=0;
if ($mode == 'mine') $mine=1;

$projectid='';
$projectid=isset($_GET["id"])?$_GET["id"]:$_POST["projectid"];

$userid='';
$userid=isset($_GET["userid"])?$_GET["userid"]:$_POST["userid"];
*/
// Security check
$socid=0;
//if ($user->societe_id > 0) $socid = $user->societe_id;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
//$result = restrictedArea($user, 'projet', $projectid);

$now=dol_now();
$nowtmp=dol_getdate($now);
$nowday=$nowtmp['mday'];
$nowmonth=$nowtmp['mon'];
$nowyear=$nowtmp['year'];

//$year=	GETPOST("year","int")?GETPOST("year","int"):date("Y");
//$month=	GETPOST("month","int")?GETPOST("month","int"):date("m");
//$day=	GETPOST("day","int")?GETPOST("day","int"):date("d");

$year=GETPOST('reyear')?GETPOST('reyear'):(GETPOST("year","int")?GETPOST("year","int"):date("Y"));
$month=GETPOST('remonth')?GETPOST('remonth'):(GETPOST("month","int")?GETPOST("month","int"):date("m"));
$day=GETPOST('reday')?GETPOST('reday'):(GETPOST("day","int")?GETPOST("day","int"):date("d"));
$day = (int) $day;
$week=GETPOST("week","int")?GETPOST("week","int"):date("W");
$search_task_ref=GETPOST('search_task_ref', 'alpha');
$search_task_label=GETPOST('search_task_label', 'alpha');
$search_project_ref=GETPOST('search_project_ref', 'alpha');
$search_thirdparty=GETPOST('search_thirdparty', 'alpha');

$monthofday=	GETPOST('newtimemonth');
$dayofday=		GETPOST('newtimeday');
$yearofday=		GETPOST('newtimeyear');

$daytoparse = $now;
if ($yearofday && $monthofday && $dayofday) $daytoparse=dol_mktime(0, 0, 0, $monthofday, $dayofday, $yearofday);	// xxxofday is value of day after submit action 'addtime'
else if ($year && $month && $day) $daytoparse=dol_mktime(0, 0, 0, $month, $day, $year);							// this are value submited after submit of action 'submitdateselect'
// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array('projecttaskcard','globalcard'));

$object = new Task($db);


/*
 * Actions
 */

// Purge criteria
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) // Both test are required to be compatible with all browsers
{
    $action = '';
    $search_task_ref = '';
    $search_task_label = '';
    $search_project_ref = '';
    $search_thirdparty = '';
}
if (GETPOST("button_search_x") || GETPOST("button_search.x") || GETPOST("button_search"))
{
    $action = '';
}

if (GETPOST('submitdateselect'))
{
//	$daytoparse = dol_mktime(0, 0, 0, GETPOST('month'), GETPOST('day'), GETPOST('year'));
	$daytoparse = dol_mktime(0, 0, 0, GETPOST('remonth'), GETPOST('reday'), GETPOST('reyear'));

	$action = '';
}

if (GETPOST("userid"))
{
    $action = '';
}
/*
if ($action == 'addtime' && $user->rights->projet->lire && GETPOST('assigntask'))
{
    $action = 'assigntask';

    if ($taskid > 0)
    {
		$result = $object->fetch($taskid, $ref);
		if ($result < 0) $error++;
    }
    else
    {
    	setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired", $langs->transnoentitiesnoconv("Task")), '', 'errors');
    	$error++;
    }
    if (! GETPOST('type'))
    {
    	setEventMessages($langs->transnoentitiesnoconv("ErrorFieldRequired", $langs->transnoentitiesnoconv("Type")), '', 'errors');
    	$error++;
    }
    if (! $error)
    {
    	$idfortaskuser=$user->id;
		$result = $object->add_contact($idfortaskuser, GETPOST("type"), 'internal');

    	if ($result >= 0 || $result == -2)	// Contact add ok or already contact of task
    	{
			// Test if we are already contact of the project (should be rare but sometimes we can add as task contact without being contact of project, like when admin user has been removed from contact of project)
    		$sql='SELECT ec.rowid FROM '.MAIN_DB_PREFIX.'element_contact as ec, '.MAIN_DB_PREFIX.'c_type_contact as tc WHERE tc.rowid = ec.fk_c_type_contact';
    		$sql.=' AND ec.fk_socpeople = '.$idfortaskuser." AND ec.element_id = '.$object->fk_project.' AND tc.element = 'project' AND source = 'internal'";
    		$resql=$db->query($sql);
    		if ($resql)
    		{
    			$obj=$db->fetch_object($resql);
    			if (! $obj)	// User is not already linked to project, so we will create link to first type
    			{
    				$project = new Project($db);
    				$project->fetch($object->fk_project);
    				// Get type
    				$listofprojcontact=$project->liste_type_contact('internal');
    				
    				if (count($listofprojcontact))
    				{
    					$typeforprojectcontact=reset(array_keys($listofprojcontact));
    					$result = $project->add_contact($idfortaskuser, $typeforprojectcontact, 'internal');
    				}
    			}
    		}
    		else 
    		{
    			dol_print_error($db);
    		}
    	}
    }

	if ($result < 0)
	{
		$error++;
		if ($object->error == 'DB_ERROR_RECORD_ALREADY_EXISTS')
		{
			$langs->load("errors");
			setEventMessages($langs->trans("ErrorTaskAlreadyAssigned"), null, 'warnings');
		}
		else
		{
			setEventMessages($object->error, $object->errors, 'errors');
		}
	}

	if (! $error)
	{
		setEventMessages("TaskAssignedToEnterTime", null);
	}

	$action='';
}

if ($action == 'addtime' && $user->rights->projet->lire)
{
    $timespent_duration=array();

    foreach($_POST as $key => $time)
    {
        if (intval($time) > 0)
        {
            // Hours or minutes of duration
            if (preg_match("/([0-9]+)duration(hour|min)/",$key,$matches))
            {
                $id = $matches[1];
				if ($id > 0)
				{
	                // We store HOURS in seconds
	                if($matches[2]=='hour') $timespent_duration[$id] += $time*60*60;

	                // We store MINUTES in seconds
	                if($matches[2]=='min') $timespent_duration[$id] += $time*60;
				}
            }
        }
    }

    if (count($timespent_duration) > 0)
    {
    	foreach($timespent_duration as $key => $val)
    	{
	        $object->fetch($key);
		    $object->progress = GETPOST($key.'progress', 'int');
	        $object->timespent_duration = $val;
	        $object->timespent_fk_user = $user->id;
	        $object->timespent_note = GETPOST($key.'note');
	        if (GETPOST($key."hour") != '' && GETPOST($key."hour") >= 0)	// If hour was entered
	        {
	        	$object->timespent_date = dol_mktime(GETPOST($key."hour"),GETPOST($key."min"),0,$monthofday,$dayofday,$yearofday);
	        	$object->timespent_withhour = 1;
	        }
	        else
			{
	        	$object->timespent_date = dol_mktime(12,0,0,$monthofday,$dayofday,$yearofday);
			}

			if ($object->timespent_date > 0)
			{
				$result=$object->addTimeSpent($user);
			}
			else
			{
				setEventMessages("ErrorBadDate", null, 'errors');
				$error++;
				break;
			}

			if ($result < 0)
			{
				setEventMessages($object->error, $object->errors, 'errors');
				$error++;
				break;
			}
    	}

    	if (! $error)
    	{
	    	setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');

    	    // Redirect to avoid submit twice on back
        	header('Location: '.$_SERVER["PHP_SELF"].($projectid?'?id='.$projectid:'?').($mode?'&mode='.$mode:'').'&year='.$yearofday.'&month='.$monthofday.'&day='.$dayofday);
        	exit;
    	}
    }
    else    
    {   
   	    setEventMessages($langs->trans("ErrorTimeSpentIsEmpty"), null, 'errors');
    }
}
*/


/*
 * View
 */
// $title=$langs->trans("TimeSpent");
$title="Timecards";
llxHeader("",$title,"");

$form = new Form($db);
$formother = new FormOther($db);
$userstatic = new User($db);

$formcompany=new FormCompany($db);
$formproject=new FormProjets($db);
$projectstatic=new Project($db);
$project = new Project($db);
$taskstatic = new Task($db);
$thirdpartystatic = new Societe($db);


$prev = dol_getdate($daytoparse - (24 * 3600));
$prev_year  = $prev['year'];
$prev_month = $prev['mon'];
$prev_day   = $prev['mday'];

$next = dol_getdate($daytoparse + (24 * 3600));
$next_year  = $next['year'];
$next_month = $next['mon'];
$next_day   = $next['mday'];


$usertoprocess = $user;

$projectsListId = $projectstatic->getProjectsAuthorizedForUser($usertoprocess,0,1);  // Return all project i have permission on. I want my tasks and some of my task may be on a public projet that is not my project

if ($id)
{
    $project->fetch($id);
    $project->fetch_thirdparty();
}

$onlyopenedproject=1;	// or -1
$morewherefilter='';
if ($search_task_ref) $morewherefilter.=natural_search("t.ref", $search_task_ref);
if ($search_task_label) $morewherefilter.=natural_search("t.label", $search_task_label);
if ($search_thirdparty) $morewherefilter.=natural_search("s.nom", $search_thirdparty);
$tasksarray=$taskstatic->getTasksArray(0, 0, ($project->id?$project->id:0), $socid, 0, $search_project_ref, $onlyopenedproject, $morewherefilter);    // We want to see all task of opened project i am allowed to see, not only mine. Later only mine will be editable later.
$projectsrole=$taskstatic->getUserRolesForProjectsOrTasks($usertoprocess, 0, ($project->id?$project->id:0), 0, $onlyopenedproject);
$tasksrole=$taskstatic->getUserRolesForProjectsOrTasks(0, $usertoprocess, ($project->id?$project->id:0), 0, $onlyopenedproject);
//var_dump($tasksarray);
//var_dump($projectsrole);
//var_dump($taskrole);


/*

print '<p>Hello World</p>';

print '<p>'.$projectid.' '.$id.'</p>';

print '<p>Now = '.$now.'</p>';

print '<p>nowtmp = '.$nowtmp.'</p>';

print '<p>nowyear = '.$nowyear.'</p>';

print '<p>nowmonth = '.$nowmonth.'</p>';

print '<p>nowday = '.$nowday.'</p>';

print '<p>daytoparse = '.$daytoparse.'</p>';

print '<p>year = '.$year.'</p>';

print '<p>month = '.$month.'</p>';

print '<p>day = '.$day.'</p>';

*/

/*
print '<p>yearofday = '.$yearofday.'</p>';

print '<p>monthofday = '.$monthofday.'</p>';

print '<p>dayofday = '.$dayofday.'</p>';

print '<p>userid = '.$userid.'</p>';


print "<p><b>       var_dump(user)</b></p>";
var_dump($user);


print $sql;


print "<p><b>       var_dump(resql)</b></p>";
var_dump($resql);

print "<p><b>       var_dump(obj)</b></p>";
var_dump($obj);

print "<p><b>       print_r(obj)</b></p>";
print_r($obj);

print "<p><b>       print_r(times)</b></p>";
print_r($times);
*/
/*
print "<p><b>       var_dump(object)</b></p>";
var_dump($object);
print "<p><b>       var_dump(tasksarray)</b></p>";
// var_dump($tasksarray);
print "<p><b>       var_dump(projectsrole)</b></p>";
var_dump($projectsrole);
print "<p><b>       print_r(projectsrole)</b></p>";
print_r($projectsrole);
print "<p><b>       var_dump(taskrole)</b></p>";
var_dump($taskrole);


print "<p><b>       print_r(tasks)</b></p>";
print_r($tasks);

print "<p><b>       print_r(arrayfields)</b></p>";
print_r($arrayfields);

*/

print_barre_liste($title, $page, "Freddo".$_SERVER["PHP_SELF"], "", $sortfield, $sortorder, "", $num, '', 'title_project');

$param='';
$param.=($mode?'&amp;mode='.$mode:'');
$param.=($search_project_ref?'&amp;search_project_ref='.$search_project_ref:'');



/*
print '<form name="addtime" method="POST" action="'.$_SERVER["PHP_SELF"].($project->id > 0 ? '?id='.$project->id : '').'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="action" value="addtime">';
print '<input type="hidden" name="mode" value="'.$mode.'">';
$tmp = dol_getdate($daytoparse);
print '<input type="hidden" name="addtimeyear" value="'.$tmp['year'].'">';
print '<input type="hidden" name="addtimemonth" value="'.$tmp['mon'].'">';
print '<input type="hidden" name="addtimeday" value="'.$tmp['mday'].'">';
*/

//$head=project_timesheet_prepare_head($mode);  //Tabs for 'Input pr week' and 'Input per day'
//dol_fiche_head($head, 'inputperday', '', 0, 'task');



// Show description of content

/*
if ($mine) print $langs->trans("MyTasksDesc").($onlyopenedproject?' '.$langs->trans("OnlyOpenedProject"):'').'<br>';
else
{
	if ($user->rights->projet->all->lire && ! $socid) print $langs->trans("ProjectsDesc").($onlyopenedproject?' '.$langs->trans("OnlyOpenedProject"):'').'<br>';
	else print $langs->trans("ProjectsPublicTaskDesc").($onlyopenedproject?' '.$langs->trans("AlsoOnlyOpenedProject"):'').'<br>';
}
if ($mine)
{
	print $langs->trans("OnlyYourTaskAreVisible").'<br>';
}
else
{
	print $langs->trans("AllTaskVisibleButEditIfYouAreAssigned").'<br>';
}
*/

//dol_fiche_end();


			// Show navigation bar
			$nav ='<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$prev_year.'&amp;month='.$prev_month.'&amp;day='.$prev_day.$param.'">'.img_previous($langs->trans("Previous")).'</a>';
			$nav.='<span id="month_name">'.dol_print_date(dol_mktime(0,0,0,$month,$day,$year),'%d-%b-%G').' </span>';
			$nav.='<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$next_year.'&amp;month='.$next_month.'&amp;day='.$next_day.$param.'">'.img_next($langs->trans("Next")).'</a>';
			$nav.=' &nbsp; (<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$nowyear.'&amp;month='.$nowmonth.'&amp;day='.$nowday.$param.'">'.$langs->trans("Today").'</a>)';
			$nav.=' &nbsp; '.$form->select_date(-1,'',0,0,2,"userid",1,0,1);
//			$nav.=' <input type="submit" name="submitdateselect" class="button" value="'.$langs->trans("Refresh").'">';
			$nav.=' <input type="submit" name="" class="button" value="'.$langs->trans("Refresh").'">';
			
			//$picto='calendarweek';
//			print '<form name="addtime" method="POST" action="'.$_SERVER["PHP_SELF"].($project->id > 0 ? '?id='.$project->id : '').'">';
//			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'">';

			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].($userid > 0 ? '?idxx='.$userid : '').'">';
			//print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
			//print '<input type="hidden" name="action" value="addtime">';
			//print '<input type="hidden" name="mode" value="'.$mode.'">';
			$tmp = dol_getdate($daytoparse);

			print '<div class="floatright">'.$nav.'</div>';     // We move this before the assign to components so, the default submit button is not the assign to.

/*			print '<input type="hidden" name="userid" value="'.$userid.'">';

			print '<input type="hidden" name="newtimeyear" value="'.$tmp['year'].'">';
			print '<input type="hidden" name="newtimemonth" value="'.$tmp['mon'].'">';
			print '<input type="hidden" name="newtimeday" value="'.$tmp['mday'].'">';
*/
/*
			print '<input type="hidden" name="year" value="'.$tmp['year'].'">';
			print '<input type="hidden" name="month" value="'.$tmp['mon'].'">';
			print '<input type="hidden" name="day" value="'.$tmp['mday'].'">';
*/
//			print '<input type="submit" class="button" value="'.$langs->trans("Refresh").'">';
			print '</form>';

			// User selection

			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?id='.$userid.'">';
			print img_object('','user','class="hideonsmartphone"');
			print $form->select_dolusers((GETPOST('userid')?GETPOST('userid'):$userid), 'userid', 1, '', 0, '', '', 0, 0, 0, '', 0, $langs->trans("ResourceNotAssignedToTheTask"), 'maxwidth400');
/*			print '<input type="hidden" name="year" value="'.$year.'">';
			print '<input type="hidden" name="month" value="'.$month.'">';
			print '<input type="hidden" name="day" value="'.$day.'">';
	*/		print '<input type="submit" class="button" value="Select">';
			print '</form>';



print "<p><b>       print_r(re)</b></p>";
print_r($re);

print "<p><b>       print_r(remonth)</b></p>";
print_r($remonth);

print "<p><b>       print_r(tmp)</b></p>";
print_r($tmp);

print "<p><b>       daytoparse </b></p>";
print_r($daytoparse);

print "<p><b>       GET POST(re) </b></p>";
print_r(GETPOST('re'));
print "<p><b>       GET POST(reday) </b></p>";
print_r(GETPOST('reday'));
print "<p><b>       GET POST(remonth) </b></p>";
print_r(GETPOST('remonth'));
print "<p><b>       GET POST('reyear') </b></p>";
print_r(GETPOST('reyear'));

print "<p><b>       VAR DUMP $_SERVER) </b></p>";
var_dump($_SERVER);

/*
print '<div class="float valignmiddle">';
print $langs->trans("AssignTaskToMe").'<br>';
$formproject->selectTasks($socid?$socid:-1, $taskid, 'taskid', 32, 0, 1, 1);
print $formcompany->selectTypeContact($object, '', 'type','internal','rowid', 0);
print '<input type="submit" class="button valignmiddle" name="assigntask" value="'.$langs->trans("AssignTask").'">';
//print '</form>';
print '</div>';
*/


print '<div class="clearboth" style="padding-bottom: 8px;"></div>';

/*
if (($id > 0 || ! empty($ref)) || $projectidforalltimes > 0)
{
	// *
	// * Project sheet in visual mode
 	// *
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
			dol_fiche_head($head, $tab, $langs->trans("Project"),0,($projectstatic->public?'projectpub':'project'));

			$param=($mode=='mine'?'&mode=mine':'');

			// Project card
    
            $linkback = '<a href="'.DOL_URL_ROOT.'/projet/list.php">'.$langs->trans("BackToList").'</a>';
            
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
			
			
			//
			// Actions
			//

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
		}
	}
	
	if (empty($projectidforalltimes))
	{
		$head=task_prepare_head($object);
		dol_fiche_head($head, 'task_time', $langs->trans("Task"),0,'projecttask');

		if ($action == 'deleteline')
		{
			print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id.'&lineid='.$_GET["lineid"].($withproject?'&withproject=1':''),$langs->trans("DeleteATimeSpent"),$langs->trans("ConfirmDeleteATimeSpent"),"confirm_delete",'','',1);
		}

		print '<table class="border" width="100%">';

		$param=($withproject?'&withproject=1':'');
		$linkback=$withproject?'<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'">'.$langs->trans("BackToList").'</a>':'';

		// Ref
		print '<tr><td class="titlefield">';
		print $langs->trans("Ref");
		print '</td><td colspan="3">';
		if (! GETPOST('withproject') || empty($projectstatic->id))
		{
			$projectsListId = $projectstatic->getProjectsAuthorizedForUser($user,0,1);
			$object->next_prev_filter=" fk_projet in (".$projectsListId.")";
		}
		else $object->next_prev_filter=" fk_projet = ".$projectstatic->id;
		print $form->showrefnav($object,'ref',$linkback,1,'ref','ref','',$param);
		print '</td></tr>';

		// Label
		print '<tr><td>'.$langs->trans("Label").'</td><td colspan="3">'.$object->label.'</td></tr>';

		// Date start
		print '<tr><td>'.$langs->trans("DateStart").'</td><td colspan="3">';
		print dol_print_date($object->date_start,'dayhour');
		print '</td></tr>';

		// Date end
		print '<tr><td>'.$langs->trans("DateEnd").'</td><td colspan="3">';
		print dol_print_date($object->date_end,'dayhour');
		print '</td></tr>';

		// Planned workload
		print '<tr><td>'.$langs->trans("PlannedWorkload").'</td><td colspan="3">';
		print convertSecondToTime($object->planned_workload,'allhourmin');
		print '</td></tr>';

		// Progress declared
		print '<tr><td>'.$langs->trans("ProgressDeclared").'</td><td colspan="3">';
		print $object->progress.' %';
		print '</td></tr>';

		// Progress calculated
		print '<tr><td>'.$langs->trans("ProgressCalculated").'</td><td colspan="3">';
		if ($object->planned_workload)
		{
			$tmparray=$object->getSummaryOfTimeSpent();
			if ($tmparray['total_duration'] > 0) print round($tmparray['total_duration']/$object->planned_workload*100, 2).' %';
			else print '0 %';
		}
		else print '';
		print '</td></tr>';

		// Project
		if (empty($withproject))
		{
			print '<tr><td>'.$langs->trans("Project").'</td><td>';
			print $projectstatic->getNomUrl(1);
			print '</td></tr>';

			// Third party
			print '<td>'.$langs->trans("ThirdParty").'</td><td>';
			if ($projectstatic->thirdparty->id) print $projectstatic->thirdparty->getNomUrl(1);
			else print '&nbsp;';
			print '</td></tr>';
		}

		print '</table>';

		dol_fiche_end();
*/


		/*
		 * Form to add time spent
		 */
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
//			print '<td>'.$langs->trans("By").'</td>';
			print '<td>'.$langs->trans("Note").'</td>';
			print '<td>'.$langs->trans("ProgressDeclared").'</td>';
			print '<td align="right" colspan="2">'.$langs->trans("NewTimeSpent").'</td>';
			print "</tr>\n";

			print '<tr '.$bc[false].'>';

			// Date
			print '<td class="maxwidthonsmartphone">';
			//$newdate=dol_mktime(12,0,0,$_POST["timemonth"],$_POST["timeday"],$_POST["timeyear"]);
			$newdate='';
			print $form->select_date($newdate, 'time', ($conf->browser->layout == 'phone'?2:1), 1, 2, "timespent_date", 1, 0, 1);
			print '</td>';
/*
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
*/
			// Note
			print '<td>';
			print '<textarea name="timespent_note" class="maxwidth100onsmartphone" rows="'.ROWS_3.'">'.($_POST['timespent_note']?$_POST['timespent_note']:'').'</textarea>';
			print '</td>';

			// Progress declared
			print '<td class="nowrap">';
			print $formother->select_percent(GETPOST('progress')?GETPOST('progress'):$object->progress,'progress');
			print '</td>';

			// Duration - Time spent
			print '<td class="nowrap" align="right">';
			print $form->select_duration('timespent_duration', ($_POST['timespent_duration']?$_POST['timespent_duration']:''), 0, 'text');
			print '</td>';

			print '<td align="center">';
			print '<input type="submit" class="button" value="'.$langs->trans("Add").'">';
			print '</td></tr>';

			print '</table></form>';
			
			print '<br>';
		}

/*
	if ($projectstatic->id > 0)
	{	
		if ($action == 'deleteline')
		{
			print $form->formconfirm($_SERVER["PHP_SELF"]."?id=".$object->id.'&lineid='.$_GET["lineid"].($withproject?'&withproject=1':''),$langs->trans("DeleteATimeSpent"),$langs->trans("ConfirmDeleteATimeSpent"),"confirm_delete",'','',1);
		}
*/
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

		/*
		 *  List of time spent
		 */
		$tasks = array();
$tcuser = 1;		
		$sql = "SELECT t.rowid, t.fk_task, t.task_date, t.task_datehour, t.task_date_withhour, t.task_duration, t.fk_user, t.note, t.thm,";
		$sql .= " t.invoice_id, t.invoice_line_id,";
		$sql .= " pt.ref, pt.label,";
		$sql .= " u.lastname, u.firstname";
		$sql .= " FROM ".MAIN_DB_PREFIX."projet_task_time as t, ".MAIN_DB_PREFIX."projet_task as pt, ".MAIN_DB_PREFIX."user as u";
		$sql .= " WHERE t.fk_user = u.rowid AND t.fk_task = pt.rowid";
		$sql .= " AND t.fk_user = ".$tcuser;			//PJR
		$sql .= " AND t.task_date_withhour = 1";		//PJR
		$sql .= " ORDER BY t.task_datehour";			//PJR

/*		if (empty($projectidforalltimes)) $sql .= " AND t.fk_task =".$object->id;
		else $sql.= " AND pt.fk_projet IN (".$projectidforalltimes.")";
		if ($search_ref) $sql .= natural_search('c.ref', $search_ref);
		if ($search_note) $sql .= natural_search('t.note', $search_note);
		$sql .= $db->order($sortfield, $sortorder);
*/
		$var=true;
		$resql = $db->query($sql);
		if ($resql)
		{
			$num = $db->num_rows($resql);
			$totalnboflines=$num;

			if (! empty($projectidforalltimes))
			{
			    $title=$langs->trans("ListTaskTimeUserProject");
			    $linktotasks='<a href="'.DOL_URL_ROOT.'/projet/tasks.php?id='.$projectstatic->id.'">'.$langs->trans("GoToListOfTasks").'</a>';
			    //print_barre_liste($title, 0, $_SERVER["PHP_SELF"], '', $sortfield, $sortorder, $linktotasks, $num, $totalnboflines, 'title_generic.png', 0, '', '', 0, 1);
			    print load_fiche_titre($title,$linktotasks,'title_generic.png');
			}

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


		
		$arrayofselected=is_array($toselect)?$toselect:array();
		
		$params='';
		if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
		if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;
		if ($search_note != '') $params.= '&amp;search_note='.urlencode($search_note);
		if ($search_duration != '') $params.= '&amp;search_field2='.urlencode($search_duration);
		if ($optioncss != '') $param.='&optioncss='.$optioncss;
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
		
		
		$arrayofmassactions =  array(
		    //'presend'=>$langs->trans("SendByMail"),
		    //'builddoc'=>$langs->trans("PDFMerge"),
		);
		//if ($user->rights->projet->creer) $arrayofmassactions['delete']=$langs->trans("Delete");
		if ($massaction == 'presend') $arrayofmassactions=array();
		$massactionbutton=$form->selectMassAction('', $arrayofmassactions);
		
		
		
		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?id='.$id.'">';
        if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
		if ($action == 'editline') print '<input type="hidden" name="action" value="updateline">';
		else print '<input type="hidden" name="action" value="list">';
	    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
		
		print '<input type="hidden" name="id" value="'.$id.'">';
		print '<input type="hidden" name="projectid" value="'.$projectidforalltimes.'">';
		print '<input type="hidden" name="withproject" value="'.$withproject.'">';

		$moreforfilter = '';
		
		$parameters=array();
		$reshook=$hookmanager->executeHooks('printFieldPreListTitle',$parameters);    // Note that $action and $object may have been modified by hook
		if (empty($reshook)) $moreforfilter .= $hookmanager->resPrint;
		else $moreforfilter = $hookmanager->resPrint;
		
		if (! empty($moreforfilter))
		{
		    print '<div class="liste_titre liste_titre_bydiv centpercent">';
		    print $moreforfilter;
		    print '</div>';
		}
		
		$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
		$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
		
        print '<div class="div-table-responsive">';
		print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";
		
		print '<tr class="liste_titre">';
		if (! empty($arrayfields['t.task_date']['checked'])) print_liste_field_titre($arrayfields['t.task_date']['label'],$_SERVER['PHP_SELF'],'t.task_date,t.task_datehour,t.rowid','',$params,'',$sortfield,$sortorder);
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
        {
            if (! empty($arrayfields['t.task_ref']['checked'])) print_liste_field_titre($arrayfields['t.task_ref']['label'],$_SERVER['PHP_SELF'],'pt.ref','',$params,'',$sortfield,$sortorder);            
            if (! empty($arrayfields['t.task_label']['checked'])) print_liste_field_titre($arrayfields['t.task_label']['label'],$_SERVER['PHP_SELF'],'pt.label','',$params,'',$sortfield,$sortorder);            
        }
//        if (! empty($arrayfields['author']['checked'])) print_liste_field_titre($arrayfields['author']['label'],$_SERVER['PHP_SELF'],'','',$params,'',$sortfield,$sortorder);
		if (! empty($arrayfields['t.note']['checked'])) print_liste_field_titre($arrayfields['t.note']['label'],$_SERVER['PHP_SELF'],'t.note','',$params,'',$sortfield,$sortorder);
		if (! empty($arrayfields['t.task_duration']['checked'])) print_liste_field_titre($arrayfields['t.task_duration']['label'],$_SERVER['PHP_SELF'],'t.task_duration','',$params,'align="right"',$sortfield,$sortorder);
//		if (! empty($arrayfields['value']['checked'])) print_liste_field_titre($arrayfields['value']['label'],$_SERVER['PHP_SELF'],'','',$params,'align="right"',$sortfield,$sortorder);
		// Extra fields
		/*
    	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
    	{
    	   foreach($extrafields->attribute_label as $key => $val) 
    	   {
               if (! empty($arrayfields["ef.".$key]['checked'])) 
               {
    				$align=$extrafields->getAlignFlag($key);
    				print_liste_field_titre($extralabels[$key],$_SERVER["PHP_SELF"],"ef.".$key,"",$param,($align?'align="'.$align.'"':''),$sortfield,$sortorder);
               }
    	   }
    	}*/
	    // Hook fields
    	$parameters=array('arrayfields'=>$arrayfields);
        $reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);    // Note that $action and $object may have been modified by hook
        print $hookmanager->resPrint;
    	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"',$sortfield,$sortorder,'maxwidthsearch ');
		print "</tr>\n";

		// Fields title search
		print '<tr class="liste_titre">';
		// LIST_OF_TD_TITLE_SEARCH
		if (! empty($arrayfields['t.task_date']['checked'])) print '<td class="liste_titre"></td>';
		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
        {
            if (! empty($arrayfields['t.task_ref']['checked'])) print '<td class="liste_titre"></td>';
            if (! empty($arrayfields['t.task_label']['checked'])) print '<td class="liste_titre"></td>';
        }
//        if (! empty($arrayfields['author']['checked'])) print '<td class="liste_titre"></td>';
		if (! empty($arrayfields['t.note']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_note" value="'.$search_note.'"></td>';
		if (! empty($arrayfields['t.task_duration']['checked'])) print '<td class="liste_titre right"></td>';
//		if (! empty($arrayfields['value']['checked'])) print '<td class="liste_titre"></td>';
		// Extra fields
		/*
		if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
		{
		    foreach($extrafields->attribute_label as $key => $val)
		    {
		        if (! empty($arrayfields["ef.".$key]['checked']))
		        {
		            $align=$extrafields->getAlignFlag($key);
		            $typeofextrafield=$extrafields->attribute_type[$key];
		            print '<td class="liste_titre'.($align?' '.$align:'').'">';
		            if (in_array($typeofextrafield, array('varchar', 'int', 'double', 'select')))
		            {
		                $crit=$val;
		                $tmpkey=preg_replace('/search_options_/','',$key);
		                $searchclass='';
		                if (in_array($typeofextrafield, array('varchar', 'select'))) $searchclass='searchstring';
		                if (in_array($typeofextrafield, array('int', 'double'))) $searchclass='searchnum';
		                print '<input class="flat'.($searchclass?' '.$searchclass:'').'" size="4" type="text" name="search_options_'.$tmpkey.'" value="'.dol_escape_htmltag($search_array_options['search_options_'.$tmpkey]).'">';
		            }
		            print '</td>';
		        }
		    }
		}*/
		// Fields from hook
		$parameters=array('arrayfields'=>$arrayfields);
		$reshook=$hookmanager->executeHooks('printFieldListOption',$parameters);    // Note that $action and $object may have been modified by hook
		print $hookmanager->resPrint;
		// Action column
		print '<td class="liste_titre" align="right">';
		$searchpitco=$form->showFilterAndCheckAddButtons($massactionbutton?1:0, 'checkforselect', 1);
		print $searchpitco;
		print '</td>';
		print '</tr>'."\n";		

		$tasktmp = new Task($db);
		
		$i = 0;

		$childids = $user->getAllChildIds();
		
		$total = 0;
		$totalvalue = 0;
		$totalarray=array();
		foreach ($tasks as $task_time)
		{
			$var=!$var;
			print "<tr ".$bc[$var].">";

			$date1=$db->jdate($task_time->task_date);
			$date2=$db->jdate($task_time->task_datehour);

			// Date
			if (! empty($arrayfields['t.task_date']['checked']))
			{
    			print '<td class="nowrap">';
    			if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
    				print $form->select_date(($date2?$date2:$date1),'timeline',1,1,2,"timespent_date",1,0,1);
    			}
    			else
    			{
    				print dol_print_date(($date2?$date2:$date1),($task_time->task_date_withhour?'dayhour':'day'));
    			}
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
			}

			// Task ref
            if (! empty($arrayfields['t.task_ref']['checked']))
            {
        		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
    			{
        			print '<td class="nowrap">';
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
            
/*
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
    				$userstatic->id         = $task_time->fk_user;
    				$userstatic->lastname	= $task_time->lastname;
    				$userstatic->firstname 	= $task_time->firstname;
    				print $userstatic->getNomUrl(1);
    			}
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
            }
*/
			// Note
            if (! empty($arrayfields['t.note']['checked'])) 
            {
                print '<td align="left">';
    			if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
    				print '<textarea name="timespent_note_line" width="95%" rows="'.ROWS_2.'">'.$task_time->note.'</textarea>';
    			}
    			else
    			{
    				print dol_nl2br($task_time->note);
    			}
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
            }
            
			// Time spent
            if (! empty($arrayfields['t.task_duration']['checked']))
            {
    			print '<td align="right">';
    			if ($_GET['action'] == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
    				print '<input type="hidden" name="old_duration" value="'.$task_time->task_duration.'">';
    				print $form->select_duration('new_duration',$task_time->task_duration,0,'text');
    			}
    			else
    			{
    				print convertSecondToTime($task_time->task_duration,'allhourmin');
    			}
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
    			if (! $i) $totalarray['totaldurationfield']=$totalarray['nbfield'];
    			$totalarray['totalduration'] += $task_time->task_duration;
            }
/*            
			// Value spent
            if (! empty($arrayfields['value']['checked'])) 
            {
				print '<td align="right">';
				$value = price2num($task_time->thm * $task_time->task_duration / 3600);
				print price($value, 1, $langs, 1, -1, -1, $conf->currency);
				print '</td>';
				if (! $i) $totalarray['nbfield']++;
    			if (! $i) $totalarray['totalvaluefield']=$totalarray['nbfield'];
    			$totalarray['totalvalue'] += $value;
            }
*/
			// Fields from hook
			$parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
			$reshook=$hookmanager->executeHooks('printFieldListValue',$parameters);    // Note that $action and $object may have been modified by hook
			print $hookmanager->resPrint;
		
            // Action column
			print '<td class="right" valign="middle" width="80">';
			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid)
			{
				print '<input type="hidden" name="lineid" value="'.$_GET['lineid'].'">';
				print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
				print '<br>';
				print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
			}
			else if ($user->rights->projet->lire)    // Read project and enter time consumed on assigned tasks
			{
			    if ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))
			    {
    				print '&nbsp;';
    				print '<a href="'.$_SERVER["PHP_SELF"].'?'.($projectidforalltimes?'projectid='.$projectidforalltimes.'&amp;':'').'id='.$task_time->fk_task.'&amp;action=editline&amp;lineid='.$task_time->rowid.($withproject?'&amp;withproject=1':'').'">';
    				print img_edit();
    				print '</a>';
    
    				print '&nbsp;';
    				print '<a href="'.$_SERVER["PHP_SELF"].'?'.($projectidforalltimes?'projectid='.$projectidforalltimes.'&amp;':'').'id='.$task_time->fk_task.'&amp;action=deleteline&amp;lineid='.$task_time->rowid.($withproject?'&amp;withproject=1':'').'">';
    				print img_delete();
    				print '</a>';
			    }
			}
        	print '</td>';
        	if (! $i) $totalarray['nbfield']++;
        	
			print "</tr>\n";
			
			$i++;
		}
		
		// Show total line
		if (isset($totalarray['totaldurationfield']) || isset($totalarray['totalvaluefield']))
		{
		    print '<tr class="liste_total">';
		    $i=0;
		    while ($i < $totalarray['nbfield'])
		    {
		        $i++;
		        if ($i == 1)
		        {
		            if ($num < $limit) print '<td align="left">'.$langs->trans("Total").'</td>';
		            else print '<td align="left">'.$langs->trans("Totalforthispage").'</td>';
		        }
		        elseif ($totalarray['totaldurationfield'] == $i) print '<td align="right">'.convertSecondToTime($totalarray['totalduration'],'allhourmin').'</td>';
		        elseif ($totalarray['totalvaluefield'] == $i) print '<td align="right">'.price($totalarray['totalvalue']).'</td>';
		        else print '<td></td>';
		    }
		    print '</tr>';
		}

		print '</tr>';

		print "</table>";
		print '</div>';
		print "</form>";







// XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX

/*
print '<div class="div-table-responsive">';
print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'" id="tablelines3">'."\n";

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("RefTask").'</td>';
print '<td>'.$langs->trans("LabelTask").'</td>';
print '<td>'.$langs->trans("ProjectRef").'</td>';
if (! empty($conf->global->PROJECT_LINES_PERDAY_SHOW_THIRDPARTY))
{
    print '<td>'.$langs->trans("ThirdParty").'</td>';
}
print '<td align="right" class="maxwidth100">'.$langs->trans("PlannedWorkload").'</td>';
print '<td align="right" class="maxwidth100">'.$langs->trans("ProgressDeclared").'</td>';
print '<td align="right" class="maxwidth100">'.$langs->trans("TimeSpent").'</td>';
if ($usertoprocess->id == $user->id) print '<td align="right" class="maxwidth100">'.$langs->trans("TimeSpentByYou").'</td>';
else print '<td align="right" class="maxwidth100">'.$langs->trans("TimeSpentByUser").'</td>';
print '<td align="center">'.$langs->trans("HourStart").'</td>';
print '<td align="center" colspan="2">'.$langs->trans("Duration").'</td>';
print '<td align="right">'.$langs->trans("Note").'</td>';
print "</tr>\n";

print '<tr class="liste_titre">';
print '<td class="liste_titre"><input type="text" size="4" name="search_task_ref" value="'.dol_escape_htmltag($search_task_ref).'"></td>';
print '<td class="liste_titre"><input type="text" size="4" name="search_task_label" value="'.dol_escape_htmltag($search_task_label).'"></td>';
print '<td class="liste_titre"><input type="text" size="4" name="search_project_ref" value="'.dol_escape_htmltag($search_project_ref).'"></td>';
if (! empty($conf->global->PROJECT_LINES_PERDAY_SHOW_THIRDPARTY)) print '<td class="liste_titre"><input type="text" size="4" name="search_thirdparty" value="'.dol_escape_htmltag($search_thirdparty).'"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
// Action column
print '<td class="liste_titre nowrap" align="right">';
$searchpitco=$form->showFilterAndCheckAddButtons(0);
print $searchpitco;
print '</td>';
print "</tr>\n";


// By default, we can edit only tasks we are assigned to
$restrictviewformytask=(empty($conf->global->PROJECT_TIME_SHOW_TASK_NOT_ASSIGNED)?1:0);

if (count($tasksarray) > 0)
{
	$j=0;
	projectLinesPerDay($j, 0, $usertoprocess, $tasksarray, $level, $projectsrole, $tasksrole, $mine, $restrictviewformytask, $daytoparse);
}
else
{
	print '<tr><td colspan="10">'.$langs->trans("NoTasks").'</td></tr>';
}
print "</table>";
print '</div>';
*/
// XXXXXXXXXXXXXXXXX


print '<div class="div-table-responsive">';
print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'" id="tablelines3">'."\n";

print '<tr class="liste_titre">';
print '<td>'.$langs->trans("RefTask").'</td>';
print '<td>'.$langs->trans("LabelTask").'</td>';
print '<td>'.$langs->trans("ProjectRef").'</td>';
if (! empty($conf->global->PROJECT_LINES_PERDAY_SHOW_THIRDPARTY))
{
    print '<td>'.$langs->trans("ThirdParty").'</td>';
}
print '<td align="right" class="maxwidth100">'.$langs->trans("PlannedWorkload").'</td>';
print '<td align="right" class="maxwidth100">'.$langs->trans("ProgressDeclared").'</td>';
print '<td align="right" class="maxwidth100">'.$langs->trans("TimeSpent").'</td>';
if ($usertoprocess->id == $user->id) print '<td align="right" class="maxwidth100">'.$langs->trans("TimeSpentByYou").'</td>';
else print '<td align="right" class="maxwidth100">'.$langs->trans("TimeSpentByUser").'</td>';
print '<td align="center">'.$langs->trans("HourStart").'</td>';
print '<td align="center" colspan="2">'.$langs->trans("Duration").'</td>';
print '<td align="right">'.$langs->trans("Note").'</td>';
print "</tr>\n";

print '<tr class="liste_titre">';
print '<td class="liste_titre"><input type="text" size="4" name="search_task_ref" value="'.dol_escape_htmltag($search_task_ref).'"></td>';
print '<td class="liste_titre"><input type="text" size="4" name="search_task_label" value="'.dol_escape_htmltag($search_task_label).'"></td>';
print '<td class="liste_titre"><input type="text" size="4" name="search_project_ref" value="'.dol_escape_htmltag($search_project_ref).'"></td>';
if (! empty($conf->global->PROJECT_LINES_PERDAY_SHOW_THIRDPARTY)) print '<td class="liste_titre"><input type="text" size="4" name="search_thirdparty" value="'.dol_escape_htmltag($search_thirdparty).'"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
print '<td class="liste_titre"></td>';
// Action column
print '<td class="liste_titre nowrap" align="right">';
$searchpitco=$form->showFilterAndCheckAddButtons(0);
print $searchpitco;
print '</td>';
print "</tr>\n";


// By default, we can edit only tasks we are assigned to
$restrictviewformytask=(empty($conf->global->PROJECT_TIME_SHOW_TASK_NOT_ASSIGNED)?1:0);

if (count($tasksarray) > 0)
{
	$j=0;
	projectLinesPerDay($j, 0, $usertoprocess, $tasksarray, $level, $projectsrole, $tasksrole, $mine, $restrictviewformytask, $daytoparse);
}
else
{
	print '<tr><td colspan="10">'.$langs->trans("NoTasks").'</td></tr>';
}
print "</table>";
print '</div>';

// XXXXXXXXXXXXXXXXX

print '<div class="center">';
print '<input type="submit" class="button"'.($disabledtask?' disabled':'').' value="'.$langs->trans("Save").'">';
print '</div>';

print '</form>';


print '<script type="text/javascript">';
print "jQuery(document).ready(function () {\n";
print '		jQuery(".timesheetalreadyrecorded").tipTip({ maxWidth: "600px", edgeOffset: 10, delay: 50, fadeIn: 50, fadeOut: 50, content: \''.dol_escape_js($langs->trans("TimeAlreadyRecorded", $user->getFullName($langs))).'\'});';
print "});";
print '</script>';


llxFooter();
$db->close();
