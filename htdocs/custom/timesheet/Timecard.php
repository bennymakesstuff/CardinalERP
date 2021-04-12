<?php
/* Copyright (C) 2005      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2006-2016 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2010      François Legastelois <flegastelois@teclib.com>
 * Copyright (C) 2011		Juanjo Menent			<jmenent@2byte.es> 
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
 *	\file       htdocs/custom/timesheet/Timecard.php
 *	\ingroup    custom
 *	\brief      List time entries for users (per day entries)
 */

require ("../../main.inc.php");
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formcompany.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';

//include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

//include_once DOL_DOCUMENT_ROOT.'/custom/timecard/core/lib/includeMain.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/timecard/core/lib/timecard.lib.php';
//require_once DOL_DOCUMENT_ROOT.'/custom/timecard/class/timecardUser.class.php';

// require_once DOL_DOCUMENT_ROOT.'/custom/timecard/class/timecard.class.php';

$langs->load('projects');

//$userid=	GETPOST('userid','int')?GETPOST('userid','int'):-1;
//$object = new User($db);
//$object->fetch($userid);


// Security check
$socid=0;
//if ($user->societe_id > 0) $socid = $user->societe_id;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
if (!$user->rights->projet->lire) accessforbidden();

/*
$limit = GETPOST("limit")?GETPOST("limit","int"):$conf->liste_limit;
$sortfield = GETPOST("sortfield",'alpha');
$sortorder = GETPOST("sortorder",'alpha');
$page = GETPOST("page",'int');
if ($page == -1) { $page = 0; }
$offset = $limit * $page;
$pageprev = $page - 1;
$pagenext = $page + 1;
if (! $sortfield) $sortfield='t.task_date,t.task_datehour,t.rowid';
if (! $sortorder) $sortorder='DESC';
*/

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
// $hookmanager->initHooks(array('projecttaskcard','globalcard'));

$object = new Task($db);
/*
$projectstatic = new Project($db);
$extrafields_project = new ExtraFields($db);
$extrafields_task = new ExtraFields($db);

if ($projectid > 0 || ! empty($ref))
{
    // fetch optionals attributes and labels
    $extralabels_projet=$extrafields_project->fetch_name_optionals_label($projectstatic->table_element);
}
$extralabels_task=$extrafields_task->fetch_name_optionals_label($object->table_element);
*/

$action =			GETPOST('action','alpha');
//print 'Action: '.$action;
$id =				GETPOST('id','int');  		// task id
$timespentid =		GETPOST('lineid');
$confirm =			GETPOST('confirm','alpha');
$projectid =		GETPOST('projectid','int');
/*
$cancel =			GETPOST('cancel','alpha');
$ref =				GETPOST('ref','alpha');

$withproject =		GETPOST('withproject','int');
$project_ref =		GETPOST('project_ref','alpha');
*/
$st_time_year =		GETPOST("st_time_year","int")?GETPOST("st_time_year","int"):date("Y");
$st_time_month =	GETPOST("st_time_month","int")?GETPOST("st_time_month","int"):date("m");
$st_time_day =		GETPOST("st_time_day","int")?GETPOST("st_time_day","int"):date("d");
$st_time_hour =		GETPOST("st_time_hour","int")?GETPOST("st_time_hour","int"):date("H");
$st_time_min =		GETPOST("st_time_min","int")?GETPOST("st_time_min","int"):date("i");

$fin_time_year =	GETPOST("fin_time_year","int")?GETPOST("fin_time_year","int"):date("Y");
$fin_time_month =	GETPOST("fin_time_month","int")?GETPOST("fin_time_month","int"):date("m");
$fin_time_day =		GETPOST("fin_time_day","int")?GETPOST("fin_time_day","int"):date("d");
$fin_time_hour =	GETPOST("fin_time_hour","int")?GETPOST("fin_time_hour","int"):date("H");
$fin_time_min =		GETPOST("fin_time_min","int")?GETPOST("fin_time_min","int"):date("i");

$st_time =			dol_mktime($st_time_hour, $st_time_min, 0, $st_time_month, $st_time_day, $st_time_year);
$fin_time =			dol_mktime($fin_time_hour, $fin_time_min, 0, $fin_time_month, $fin_time_day, $fin_time_year);

$year =				GETPOST('reyear')?GETPOST('reyear'):(GETPOST("year","int")?GETPOST("year","int"):date("Y"));
$month =			GETPOST('remonth')?GETPOST('remonth'):(GETPOST("month","int")?GETPOST("month","int"):date("m"));
$day =				GETPOST('reday')?GETPOST('reday'):(GETPOST("day","int")?GETPOST("day","int"):date("d"));
$day =				(int) $day;
// $week =			GETPOST("week","int")?GETPOST("week","int"):date("W");

$now =				dol_now();
$nowtmp =			dol_getdate($now);
$nowday =			$nowtmp['mday'];
$nowmonth =			$nowtmp['mon'];
$nowyear =			$nowtmp['year'];
$nowwday =			$nowtmp['wday'];
$daytoparse =		$now;
if ($year && $month && $day) $daytoparse=dol_mktime(0, 0, 0, $month, $day, $year);
/*
if ($yearofday && $monthofday && $dayofday) $daytoparse=dol_mktime(0, 0, 0, $monthofday, $dayofday, $yearofday);	// xxxofday is value of day after submit action 'addtime'
else if ($year && $month && $day) $daytoparse=dol_mktime(0, 0, 0, $month, $day, $year);								// this are value submited after submit of action 'submitdateselect'
*/


			/*
			 * Actions
			 */
			
			/*
			$parameters=array('socid'=>$socid, 'projectid'=>$projectid);
			$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
			if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
			*/

			// Cancel / Error checks / Setting Up

			if ( GETPOST('cancel'))
			{
				$action='';
			}

			// Error checks

			if ($action == 'addtimespent' || $action == 'updateline')
			{
				$error=0;

				$tmparray=explode('_',GETPOST('newtask_id'));
				$newprojectid=$tmparray[0];
				if (empty($newprojectid)) $newprojectid = $id; // If newprojectid is ''
				$newtask_id=$tmparray[1];
				if (empty($newtask_id)) $newtask_id = 0;	// If newtask_id is ''
			
				if (($fin_time - $st_time) < 0)
				{
					setEventMessages($langs->trans('Task duration is less than zero'), null, 'errors');
					$error++;
				}

				if (($fin_time - $st_time) > 24*60*60) // check if timespent event is over 24 hours duration - could potentially be set less than 24 hours. This basically just checks for day selection errors
				{
					setEventMessages($langs->trans('Task duration is greater than 24 hours'), null, 'errors');
					$error++;
				}

				if (! $newtask_id > 0)  // need a task, any task, to be selected and not just a parent project (i.e. without a task) which can happen with selectProjectTasks function
				{
					setEventMessages($langs->trans('A task / work-order needs to be selected'), null, 'errors');
					$error++;
				}

				if (! $error)
				{
					if ($action == 'updateline')
					{
						$object->fetch($id);
						// TODO Check that ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))
						$object->timespent_id = GETPOST('lineid');
					}

					$object->timespent_date = dol_mktime(12,0,0,GETPOST("st_time_month"),GETPOST("st_time_day"),GETPOST("st_time_year"));
					if (GETPOST("st_time_hour") != '' && GETPOST("st_time_hour") >= 0)	// If hour was entered
					{
						$object->timespent_datehour = dol_mktime(GETPOST("st_time_hour"),GETPOST("st_time_min"),0,GETPOST("st_time_month"),GETPOST("st_time_day"),GETPOST("st_time_year"));
						$object->timespent_withhour = 1;
					}
					else
					{
						$object->timespent_datehour = $object->timespent_date;
						$object->timespent_withhour = 0;
					}		

					$object->timespent_duration = $fin_time - $st_time;
			//		$object->timespent_fk_user = $_POST["userid_line"];
					$object->timespent_fk_user = GETPOST('userid');		
					$object->timespent_note = GETPOST('timespent_note_line');

					$object->timespent_old_duration = GETPOST('old_duration');
					$object->progress = GETPOST('progress', 'int');

			
					// Clean parameters
					if (empty($object->timespent_datehour)) $object->timespent_datehour = $object->timespent_date;
					if (isset($object->timespent_note)) $object->timespent_note = trim($object->timespent_note);

				}

			}

			// Addtimespent

			if ($action == 'addtimespent' && $user->rights->projet->lire)
			{
				if (! $error)
				{
					$object->id = $newtask_id;
					$result=$object->addTimeSpent($user);
					if ($result >= 0)
					{
						setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
						$action='';
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

			// Update Timespent

			if ($action == 'updateline' && ! GETPOST('cancel') && $user->rights->projet->lire)
			{
				if (! $error)
				{
					$ret = 0;

					$db->begin();
			
					$sql = "UPDATE ".MAIN_DB_PREFIX."projet_task_time SET";
					$sql.= " fk_task = ".$newtask_id.",";
					$sql.= " task_date = '".$db->idate($object->timespent_date)."',";
					$sql.= " task_datehour = '".$db->idate($object->timespent_datehour)."',";
					$sql.= " task_date_withhour = ".(empty($object->timespent_withhour)?0:1).",";
					$sql.= " task_duration = ".$object->timespent_duration.",";
					$sql.= " fk_user = ".$object->timespent_fk_user.",";
					$sql.= " note = ".(isset($object->timespent_note)?"'".addslashes($object->timespent_note)."'":"null");
					$sql.= " WHERE rowid = ".$object->timespent_id;

					//        dol_syslog(get_class($this)."::updateTimeSpent", LOG_DEBUG);
					if ($db->query($sql) )
					{
						if (! $notrigger)
						{
							// Call trigger
							$result=$object->call_trigger('TASK_TIMESPENT_MODIFY',$user);
							if ($result < 0)
							{
								$db->rollback();
								$ret = -1;
							}
							else $ret = 1;
							// End call triggers
						}
						else $ret = 1;
					}
					else
					{
						$error=$db->lasterror();
						$db->rollback();
						$ret = -1;
					}

					if ($ret == 1)
					{
						if (($object->timespent_old_duration != $object->timespent_duration) || ($object->id != $newtask_id))
						{
							// $newDuration = $this->timespent_duration - $this->timespent_old_duration;
				
							$sql = "UPDATE ".MAIN_DB_PREFIX."projet_task";
							$sql.= " SET duration_effective = (SELECT SUM(task_duration) FROM ".MAIN_DB_PREFIX."projet_task_time as ptt where ptt.fk_task = ".$object->id.")";
							$sql.= " WHERE rowid = ".$object->id;
				
							//		dol_syslog(get_class($this)."::updateTimeSpent", LOG_DEBUG);
							if (! $db->query($sql) )
							{
								$error=$db->lasterror();
								$db->rollback();
								$ret = -2;
							}

							if ($object->id != $newtask_id)
							{
								$sql = "UPDATE ".MAIN_DB_PREFIX."projet_task";
								$sql.= " SET duration_effective = (SELECT SUM(task_duration) FROM ".MAIN_DB_PREFIX."projet_task_time as ptt where ptt.fk_task = ".$newtask_id.")";
								$sql.= " WHERE rowid = ".$newtask_id;
					
								//		dol_syslog(get_class($this)."::updateTimeSpent", LOG_DEBUG);
								if (! $db->query($sql) )
								{
									$error=$db->lasterror();
									$db->rollback();
									$ret = -3;
								}
							}
						}
					}

					//$result=$object->updateFullTimeSpent($user);

					if ($ret >= 0)
					{
						$db->commit();
						setEventMessages($langs->trans("RecordSaved"), null, 'mesgs');
						$action='';
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

			//	Confirm Delete

			if ($action == 'confirm_delete' && $confirm == "yes" && $user->rights->projet->lire)
			{
				$object->fetchTimeSpent(GETPOST('lineid'));
				// TODO Check that ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))

				$result = $object->delTimeSpent($user);
				$action='';
				$confirm='';
				$morewherefilter = ' AND NOT t.progress = 100';

				if ($result < 0)
				{
					$langs->load("errors");
					setEventMessages($langs->trans($object->error), null, 'errors');
					$error++;
					$action='';
				}
			}
			
			// Retreive First Task ID of Project if withprojet is on to allow project prev next to work

			if (! empty($project_ref) && ! empty($withproject))
			{
				if ($projectstatic->fetch(0,$project_ref) > 0)
				{
					$tasksarray=$object->getTasksArray(0, 0, $projectstatic->id, $socid, 0, '', -1, $morewherefilter, 0, 0);
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
			
			/*
			 * View
			 */
			
			$title=$langs->trans("Timecards");
			llxHeader("",$title,"");
			
			$form = new Form($db);
			$formother = new FormOther($db);
			$userstatic = new User($db);
			$userid=	GETPOST('userid','int')?GETPOST('userid','int'):-1;
			$userstatic->fetch($userid);
			
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
			
			/*
			
			llxHeader("",$title,"");
			
			print_barre_liste($title, $page, $_SERVER["PHP_SELF"], "", $sortfield, $sortorder, "", $num, '', 'title_project');
			*/

			// Show navigation bar
			$nav='<span id="month_name">'.dol_print_date(dol_mktime(0,0,0,$month,$day,$year),'%A %d-%b-%G').' </span>';
			$nav.='<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$prev_year.'&amp;month='.$prev_month.'&amp;day='.$prev_day.'">'.img_previous($langs->trans("Previous")).' </a>';
			$nav.=' &nbsp; ';
			$nav.='<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$next_year.'&amp;month='.$next_month.'&amp;day='.$next_day.'"> '.img_next($langs->trans("Next")).'</a>';
			$nav.=' &nbsp; (<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$nowyear.'&amp;month='.$nowmonth.'&amp;day='.$nowday.'">'.$langs->trans("Today").'</a>)';
			$nav.=' &nbsp; '.$form->select_date(-1,'re',0,0,2,"userid",1,0,1);
			$nav.=' <input type="submit" name="" class="button" value="'.$langs->trans("Refresh").'">';

			$picto='calendarweek';

/*
			print '<form name="addtime" method="POST" action="'.$_SERVER["PHP_SELF"].($project->id > 0 ? '?id='.$project->id : '').'">';   
			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'">';
*/
			print '<form name="addtime" method="GET" action="'.$_SERVER["PHP_SELF"].($userid > 0 ? '?userid='.$userid : '').'&amp;year='.$nowyear.'&amp;month='.$nowmonth.'&amp;day='.$nowday.'">';
			$tmp = dol_getdate($daytoparse);
			print '<input type="hidden" name="userid" value="'.$userid.'">';
/*
			print '<input type="hidden" name="newtimeyear" value="'.$tmp['year'].'">';
			print '<input type="hidden" name="newtimemonth" value="'.$tmp['mon'].'">';
			print '<input type="hidden" name="newtimeday" value="'.$tmp['mday'].'">';
*/
			print '<div class="floatright">'.$nav.'</div>';     // We move this before the assign to components so, the default submit button is not the assign to.
			print '</form>';

			// User selection

			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'">';
			print img_object('','user','class="hideonsmartphone"');
			print $form->select_dolusers((GETPOST('userid')?GETPOST('userid'):$userid), 'userid', 1, '', 0, '', '', 0, 0, 0, '', 0, $langs->trans("ResourceNotAssignedToTheTask"), 'maxwidth400');
			print '<input type="hidden" name="year" value="'.$year.'">';
			print '<input type="hidden" name="month" value="'.$month.'">';
			print '<input type="hidden" name="day" value="'.$day.'">';
			print '<input type="submit" class="button" value="Select">';
			print '</form>';

			print '<div class="clearboth" style="padding-bottom: 8px;"></div>';

			//	Delete Timespent line

			if ($action == 'deleteline')
			{
				print $form->formconfirm($_SERVER["PHP_SELF"].'?userid='.$userid.'&year='.$year.'&month='.$month.'&day='.$day.'&lineid='.GETPOST('lineid'),$langs->trans("DeleteATimeSpent"),$langs->trans("ConfirmDeleteATimeSpent"),"confirm_delete",'','no',1);
			}


	    // Initialize technical object to manage hooks. Note that conf->hooks_modules contains array
	    $hookmanager->initHooks(array('tasktimelist'));
	    $extrafields = new ExtraFields($db);

	    // Definition of fields for list
	    $arrayfields=array();
	    $arrayfields['t.task_date']=array('label'=>$langs->trans("Start Time"), 'checked'=>1);
	    $arrayfields['t.task_date_end']=array('label'=>$langs->trans("Finish Time"), 'checked'=>1);
	    $arrayfields['t.task_duration']=array('label'=>$langs->trans("Duration"), 'checked'=>1);
	    $arrayfields['t.project_ref']=array('label'=>$langs->trans("Project, Task / Work-Order and Note"), 'checked'=>1);
//		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
//	    {
    	    $arrayfields['t.task_ref']=array('label'=>$langs->trans("Task/Work-Order"), 'checked'=>1);
    	    $arrayfields['t.task_label']=array('label'=>$langs->trans("Task Description"), 'checked'=>1);
//	    }
//	    $arrayfields['author']=array('label'=>$langs->trans("By"), 'checked'=>1);
	    $arrayfields['t.note']=array('label'=>$langs->trans("Note"), 'checked'=>1);

		if (! $action == 'editline')
		{
			$arrayfields['t.progress']=array('label'=>$langs->trans("Declared<br/>Progress"), 'checked'=>1);
			$arrayfields['t.invoice_id']=array('label'=>$langs->trans("Invoice"), 'checked'=>1);
			$arrayfields['t.invoice_line_id']=array('label'=>$langs->trans("Invoice Line"), 'checked'=>1);
		}
//	    $arrayfields['value']=array('label'=>$langs->trans("Value"), 'checked'=>1, 'enabled'=>$conf->salaries->enabled);
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

		$sql = "SELECT t.rowid, t.fk_task, t.task_date, t.task_datehour, t.task_date_withhour, t.task_duration, t.fk_user, t.note, t.thm,";
		$sql .= " t.invoice_id, t.invoice_line_id,";
		$sql .= " pt.ref, pt.label, pt.fk_projet, pt.progress,";
		$sql .= " u.lastname, u.firstname";
		$sql .= " FROM ".MAIN_DB_PREFIX."projet_task_time as t, ".MAIN_DB_PREFIX."projet_task as pt, ".MAIN_DB_PREFIX."user as u";
		$sql .= " WHERE t.fk_user = u.rowid AND t.fk_task = pt.rowid";
		$sql .= " AND t.task_date = "."'".dol_print_date(dol_mktime(0,0,0,$month,$day,$year),'%Y-%m-%d'."'");		//PJR
		$sql .= " AND t.fk_user = ".$userid;			//PJR
		$sql .= " AND t.task_date_withhour = 1";		//PJR
		$sql .= " ORDER BY t.task_datehour";			//PJR

/*
		if (empty($projectidforalltimes)) $sql .= " AND t.fk_task =".$object->id;
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

			$moreparam='&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day;
            dol_banner_tab($userstatic,'userid','Prev Employee / Next Employee',1,'','','',$moreparam);

		}
		else
		{
			dol_print_error($db);
		}


		$arrayofselected=is_array($toselect)?$toselect:array();

		$params='';
		if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
		if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;
/*
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
/*
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
*/		

		// PJR was POST
		print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'">';
/*
        if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
		print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
	    print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
*/
		if ($action == 'editline') print '<input type="hidden" name="action" value="updateline">';
		else print '<input type="hidden" name="action" value="">';
			print '<input type="hidden" name="userid" value="'.$userid.'">';
			print '<input type="hidden" name="year" value="'.$year.'">';
			print '<input type="hidden" name="month" value="'.$month.'">';
			print '<input type="hidden" name="day" value="'.$day.'">';
/*
	    print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
	    print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
*/		
		print '<input type="hidden" name="id" value="'.$id.'">';
/*
		print '<input type="hidden" name="projectid" value="'.$projectidforalltimes.'">';
		print '<input type="hidden" name="withproject" value="'.$withproject.'">';
*/

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
		print '<table class="tagtable liste'./*($moreforfilter?" listwithfilterbefore":"").*/'">'."\n";
		
		print '<tr class="liste_titre">';
		if (! empty($arrayfields['t.task_date']['checked'])) print '<td>'. $arrayfields['t.task_date']['label'].'</td>';
		if (! empty($arrayfields['t.task_date_end']['checked'])) print '<td>'. $arrayfields['t.task_date_end']['label'].'</td>';
		if (! empty($arrayfields['t.task_duration']['checked'])) print '<td>'. $arrayfields['t.task_duration']['label'].'</td>';
		if (! empty($arrayfields['t.project_ref']['checked'])) print '<td>'. $arrayfields['t.project_ref']['label'].'</td>';
//            if (! empty($arrayfields['t.task_ref']['checked'])) print '<td>'. $arrayfields['t.task_ref']['label'].'</td>';
//            if (! empty($arrayfields['t.task_label']['checked'])) print '<td>'. $arrayfields['t.task_label']['label'].'</td>';
		if (! empty($arrayfields['t.progress']['checked'])) print '<td align="center">'. $arrayfields['t.progress']['label'].'</td>';

//		if (! empty($arrayfields['t.invoice_id']['checked'])) print '<td>'. $arrayfields['t.invoice_id']['label'].'</td>';
//		if (! empty($arrayfields['t.invoice_line_id']['checked'])) print '<td>'. $arrayfields['t.invoice_line_id']['label'].'</td>';
		print '<td align="center">Action</td>';

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
/*	    // Hook fields
    	$parameters=array('arrayfields'=>$arrayfields);
        $reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);    // Note that $action and $object may have been modified by hook
        print $hookmanager->resPrint;
    	print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="right"');
*/
/*		print '</tr><tr class="liste_titre">';
		if (! empty($arrayfields['t.note']['checked'])) print '<td>'. $arrayfields['t.note']['label'].'</td>';
*/
		print '</tr>';
/*
		// Fields title search
		print '<tr class="liste_titre">';
		// LIST_OF_TD_TITLE_SEARCH
		if (! empty($arrayfields['t.task_date']['checked'])) print '<td class="liste_titre"></td>';
		if (! empty($arrayfields['t.task_date_end']['checked'])) print '<td class="liste_titre"></td>';
		if (! empty($arrayfields['t.task_duration']['checked'])) print '<td class="liste_titre"></td>';
		if (! empty($arrayfields['t.project_ref']['checked'])) print '<td class="liste_titre"></td>';

		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
        {
            if (! empty($arrayfields['t.task_ref']['checked'])) print '<td class="liste_titre"></td>';
            if (! empty($arrayfields['t.task_label']['checked'])) print '<td class="liste_titre"></td>';
        }

//        if (! empty($arrayfields['author']['checked'])) print '<td class="liste_titre"></td>';

		if (! empty($arrayfields['t.progress']['checked'])) print '<td class="liste_titre"></td>';
		if (! empty($arrayfields['t.note']['checked'])) print '<td class="liste_titre"><input type="text" class="flat" name="search_note" value="'.$search_note.'"></td>';
		if (! empty($arrayfields['t.invoice_id']['checked'])) print '<td class="liste_titre"></td>';
		if (! empty($arrayfields['t.invoice_line_id']['checked'])) print '<td class="liste_titre"></td>';

//		if (! empty($arrayfields['t.task_duration']['checked'])) print '<td class="liste_titre right"></td>';
//		if (! empty($arrayfields['value']['checked'])) print '<td class="liste_titre"></td>';
*/
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
/*
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
*/
		$tasktmp = new Task($db);
		
		$i = 0;

		$childids = $user->getAllChildIds();

//print "<p><b>childids: </b></p>";
//var_dump ($childids);

		$total = 0;
		$totalvalue = 0;
		$totalarray=array();
		foreach ($tasks as $task_time)
		{
			$var=!$var;
			print "<tr ".$bc[$var].">";

			$date1=$db->jdate($task_time->task_date);
			$date2=$db->jdate($task_time->task_datehour);
			$date3=$date2+$task_time->task_duration;
			if ($i == 0) $daystart = $date2;

			// Start Time
			if (! empty($arrayfields['t.task_date']['checked']))
			{
    			print '<td class="nowrap" rowspan="1" valign="top">';
    			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
    				print $form->select_date(($date2?$date2:$date1),'st_time_',1,1,0,"timespent_stdate",1,0,1);
    			}
    			else
    			{
    				print dol_print_date(($date2?$date2:$date1),($task_time->task_date_withhour?'dayhour':'day'));
					if ($i != 0 && $date2 != $prev_end)
					{
						print img_error("Error");
					}
					$prev_end = $date3;
    			}
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
			}

			// Finish Time
			if (! empty($arrayfields['t.task_date_end']['checked']))
			{
    			print '<td class="nowrap" rowspan="1" valign="top">';
    			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
    				print $form->select_date(($date3?$date3:$date1),'fin_time_',1,1,0,"timespent_findate",1,0,1);
    			}
    			else
    			{
    				print dol_print_date(($date3?$date3:$date1),($task_time->task_date_withhour?'dayhour':'day'));
    			}
    			print '</td>';
    			if (! $i) $totalarray['nbfield']++;
			}

			// Time spent
            if (! empty($arrayfields['t.task_duration']['checked']))
            {
    			print '<td class="nowrap" rowspan="1" valign="top" align="center">';
    			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
    				print '<input type="hidden" name="old_duration" value="'.$task_time->task_duration.'">';
//    				print $form->select_duration('new_duration',$task_time->task_duration,0,'text');
    				print convertSecondToTime($task_time->task_duration,'allhourmin');
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
			// Project ref
            if (! empty($arrayfields['t.project_ref']['checked']))
            {
        			print '<td class="nowrap">';
					$project = new Project($db);
					$project->fetch($task_time->fk_projet);
        			print $project->getNomUrl(1,'',10);
        			print '</td>';
        			if (! $i) $totalarray['nbfield']++;
            }
*/
/*            
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
*/            
			// Project, Task and Note labels
            if (! empty($arrayfields['t.task_label']['checked']))
            {
				print '<td align="left" valign="top">';
				$project = new Project($db);
				$project->fetch($task_time->fk_projet);
				$tasktmp->id = $task_time->fk_task;
				$tasktmp->ref = $task_time->ref;
				$tasktmp->label = $task_time->label;
//        		$tasktmp->note = $task_time->note;
//    			print '<input type="hidden" name="old_duration" value="'.$task_time->task_duration.'">';

    			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid)
    			{
					print $formother->selectProjectTasks($task_time->fk_task, 0, 'newtask_id', 0, 0, 0, 0);
    				print '<br/><br/>Note:<br/><textarea name="timespent_note_line" wrap="soft" cols="60" rows="'.ROWS_3.'">'.$task_time->note.'</textarea>';
    			}
    			else
    			{
					$taskdesc=$project->getNomUrl(1,'',20)." ".$tasktmp->getNomUrl(1, 'withproject', 'time')." ".$task_time->label;
					if (! empty($task_time->note)) $taskdesc.="<br/><br/>Note: ".dol_nl2br($task_time->note);
					print $taskdesc;
    			}
				print '</td>';
        		if (! $i) $totalarray['nbfield']++;
            }
/*
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
*/
			// Declared progress
            if (! empty($arrayfields['t.progress']['checked']))
            {
//        		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
//    			{
        			print '<td valign="top" align="center">';
        			print $task_time->progress.' %';	
        			print '</td>';
        			if (! $i) $totalarray['nbfield']++;
//    			}
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
/*
			// Invoice
            if (! empty($arrayfields['t.invoice_id']['checked']))
            {
//        		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
//    			{
        			print '<td class="nowrap" valign="top" align="center">';
        			print $task_time->invoice_id;	
        			print '</td>';
        			if (! $i) $totalarray['nbfield']++;
//    			}
            }
            
			// Invoice Line
            if (! empty($arrayfields['t.invoice_line_id']['checked']))
            {
//        		if ((empty($id) && empty($ref)) || ! empty($projectidforalltimes))   // Not a dedicated task
//    			{
        			print '<td class="nowrap" valign="top" align="center">';
        			print $task_time->invoice_line_id;	
        			print '</td>';
        			if (! $i) $totalarray['nbfield']++;
//    			}
            }
*/
/*

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
/*
			// Fields from hook
			$parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
			$reshook=$hookmanager->executeHooks('printFieldListValue',$parameters);    // Note that $action and $object may have been modified by hook
			print $hookmanager->resPrint;
*/		
            // Action column
			print '<td valign="middle" align="center" width="80">';

			if ($action == 'editline' && $_GET['lineid'] == $task_time->rowid)
			{
				print '<input type="hidden" name="lineid" value="'.$_GET['lineid'].'">';
				print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
				print '<br>';
				print '<input type="submit" class="button" name="cancel" value="'.$langs->trans('Cancel').'">';
			}
//			else if ($user->rights->projet->lire)    // Read project and enter time consumed on assigned tasks
			else
			{
//			    if ($task_time->fk_user == $user->id || in_array($task_time->fk_user, $childids))
//			    {
    				print '&nbsp;';
    				print '<a href="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'&amp;id='.$task_time->fk_task.'&amp;action=editline&amp;lineid='.$task_time->rowid.'">';
					
    				print img_edit();
    				print '</a>';

    				print '&nbsp;';
    				print '<a href="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'&amp;id='.$task_time->fk_task.'&amp;action=deleteline&amp;lineid='.$task_time->rowid.'">';

//    				print '<a href="'.$_SERVER["PHP_SELF"].'?action=deleteline&amp;lineid='.$task_time->rowid.'">';
//    				print '<a href="'.$_SERVER["PHP_SELF"].'?id='.$task_time->fk_task.'&amp;action=deleteline&amp;lineid='.$task_time->rowid.'">';

    				print img_delete();
    				print '</a>';
//			    }
			}
        	print '</td>';
        	if (! $i) $totalarray['nbfield']++;
        	
			print '</tr>';
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
		            if ($num < $limit) print '<td></td><td align="left">'.$langs->trans("Total").'</td>';
		            else print '<td align="left">'.$langs->trans("Totalforthispage").'</td>';
		        }
		        elseif ($totalarray['totaldurationfield'] == $i)
				{
					print '<td align="center">'.convertSecondToTime($totalarray['totalduration'],'allhourmin').'</td><br/><br/>';
//					print '<td align="center">'.convertSecondToTime($date3-$daystart,'allhourmin').'</td>';
				}
		     //   elseif ($totalarray['totalvaluefield'] == $i) print '<td align="right">'.price($totalarray['totalvalue']).'</td>';
		        else print '<td></td>';
		    }
		    print '</tr>';
		}

					$checktime	 = $totalarray['totalduration'] - ($date3-$daystart);
//					$checktotal += abs($checktime);
				if ($checktime != 0)
				{
				    print '<tr class="liste_total">';
					print '<td class="nowrap" colspan = "2" valign="top">Data Error <i>(hours:mins)</i>'.img_error("Error").'</td>';
					
					if ($checktime < 0)
					{
						print '<td align="center">(-)'.convertSecondToTime(abs($checktime),'allhourmin').img_error("Error").'</td>';
					}
					elseif ($checktime > 0)
					{
						print '<td align="center">(+)'.convertSecondToTime($checktime,'allhourmin').img_error("Error").'</td>';
					}
					else 
					{
						print '<td></td>';
					}
					print '<td>&nbsp;</td>';
					print '</tr>';
				}



		print '</tr>';

		print "</table>";
//		print '</div>';
//		print "</form>";

		// Add advice line below list of table of timespent lines
/*
print "<p><b>Object: </b></p>";
print_r ($object);
/*
print "<p><b>Action: </b></p>";
print_r ($action);
*/

		if (! $action == 'editline')
		{
			if ($userid>0)
			{
				if ($totalnboflines <1)
				{
					$title="There are no time-entries for ".$userstatic->getNomUrl(0)." on ".dol_print_date(dol_mktime(0,0,0,$month,$day,$year),'%A %d-%b-%G').". Add new time-entries below.";
				}
				else
				{
					$title="End of time-entry list for ".$userstatic->getNomUrl(0)." on ".dol_print_date(dol_mktime(0,0,0,$month,$day,$year),'%A %d-%b-%G').". Add new time-entries below.";
				}
			}
			else
			{
			    $title="No employee selected. Please select from the drop-down above.";
			}
			print load_fiche_titre($title,'','title_generic.png')."<br/><br/>";
		}

		/*
		 * Form to add time spent
		 */
/*
		if ($user->rights->projet->lire)	// IF502 Time.PHP
*/
		if ( $action != 'editline' && $userid>0)

		{
			//print '<br>';

		// PJR was POST
//			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'&amp;projectid='.$projectid.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'">';

//			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?id='.$object->id.'">';

//			print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
//			print '<input type="hidden" name="action" value="addtimespent">';
//			print '<input type="hidden" name="id" value="'.$object->id.'">';
//			print '<input type="hidden" name="withproject" value="'.$withproject.'">';

			print '<table class="noborder nohover" width="100%">';

			print '<tr class="liste_titre">';
			print '<td class="nowrap">'.$langs->trans("Start Time").'</td>';
			print '<td class="nowrap">'.$langs->trans("Finish Time").'</td>';
			print '<td colspan="1">'.$langs->trans("Project, Task / Work-Order and Note").'</td>';
			print '<td>'.$langs->trans("Declared<br/>Progress").'</td>';
/*
			print '<td>'.$langs->trans("Note").'</td>';
			print '<td align="center"> Action </td>';
*/

			print "</tr>";
			print "<tr>";
			print "<td></td><td></td>";
			print "<td>";
			
		// PJR was POST
			print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'&amp;projectid='.$projectid.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'">';
			print '<input type="hidden" name="action" value="projectsel">';
			$maxlength = 28;
			print $formproject->select_projects_list(-1, $projectid, 'projectid', $maxlength, 0, 1, 1, 0, 0, 0, '', 1);
			print '<input type="submit" class="button" name = "" value="'.$langs->trans("Optional - Select Project First").'">';
//			print '<br/>x1x<br/>'.$projectid.'<br/>x2x<br/>';

			print '</form>';


			print "</td><td></td>";
			print "</tr>";

		// PJR was POST
			print '<form method="POST" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'&amp;projectid='.$projectid.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'">';
			print '<input type="hidden" name="action" value="addtimespent">';

			print "<tr>";

			// Start Time
			print '<td class="nowrap">';
			print $form->select_date($date3?$date3:$daytoparse,'st_time_',1,1,0,"timespent_stdate2",1,0,1);
			print '</td>';

			// Finish Time
			print '<td class="nowrap">';
			print $form->select_date($date3?$date3:$daytoparse,'fin_time_',1,1,0,"timespent_findate2",1,0,1);
			print '</td>';

/*			// Duration - Time spent
			print '<td class="nowrap" align="right">';
			print $form->select_duration('timespent_duration', ($_POST['timespent_duration']?$_POST['timespent_duration']:''), 0, 'text');
			print '</td>';
*/

			// Task select
			print '<td>';

/*			
			//require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
			$out='';
			$maxlength = 30;
			$htmlname = 'projectid';
 
			// Search all projects
			$sql = 'SELECT p.rowid, p.ref, p.title, p.fk_soc, p.fk_statut, p.public';
			$sql.= ' FROM '.MAIN_DB_PREFIX .'projet as p';
			$sql.= " WHERE p.fk_statut = 1"; // only show open projects
			$sql.= " ORDER BY p.ref ASC";
			
			//      dol_syslog(get_class($this)."::select_projects sql=".$sql,LOG_DEBUG);
			$resql=$db->query($sql);
			if ($resql)
			{
				$out.= '<select class="select2-container flat" name="'.$htmlname.'">';
				$out.= '<option value="0">&nbsp;</option>';
				$num = $db->num_rows($resql);
				if ($num)
				{
					for($i=0; $i<$num; $i++)
			  		{
						$obj = $db->fetch_object($resql);
						$out.='<option value="'.$obj->rowid.'">'.dol_trunc($obj->ref,18).' - '.dol_trunc($obj->title,$maxlength).'</option>';
					}
				}
				$out.= '</select>';
				print $out;
				$db->free($resql);
			}
			else
			{
				dol_print_error($this->db);
			}
			print '<br/>x1x<br/>';
			
*/

			print $formother->selectProjectTasks('', $projectid, 'newtask_id', 0, 0, 0, 1, 0, '1', '');
//			print $formother->selectTasks(-1, 0, 'newtask_id', 0, 0, 0, 1);
			print '</td>';

			// Progress declared
//			print '<td class="nowrap">';
//			print '<td  align="right">';
			print '<td>';
			print $formother->select_percent(GETPOST('progress')?GETPOST('progress'):$object->progress,'progress');
			print '</td>';

			print '</tr>';
			print '<tr>';

			// Note
			$noteline = GETPOST('timespent_note_line');
			print '<td colspan="2"></td><td valign="top">';
//			print 'Note:<br/><textarea name="timespent_note" wrap="soft" cols="60" rows="'.ROWS_3.'">'.($_POST['timespent_note']?$_POST['timespent_note']:'').'</textarea>';
			print 'Note:<br/><textarea name="timespent_note_line" wrap="soft" cols="60" rows="'.ROWS_3.'"></textarea>';
			print '</td>';

			// Add button
			
			print '<td align="center">';
			print '<input type="submit" class="button" name = "" value="'.$langs->trans("Add").'">';

			print '</td>';
			print '</tr>';
			print '</form>';

			print '</table>';
			
			print '<br/>';
		}	// IF502 Time.PHP



		print '</div>';

print '</form>';




llxFooter();
$db->close();
