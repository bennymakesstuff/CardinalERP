<?php
/* Copyright (C) 2005		Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2006-2016	Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2010		François Legastelois <flegastelois@teclib.com>
 * Copyright (C) 2011		Juanjo Menent		<jmenent@2byte.es>
 * Copyright (C) 2018		Peter Roberts		<peter.roberts@finchmc.com.au>
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
 *	\file       htdocs/custom/timesheet/TimecardUserTimes.php
 *	\ingroup    custom
 *	\brief      List time entries for users (per fortnight entries)
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

$langs->load('projects');
$langs->load('users');

$action =			GETPOST('action','alpha');
$id =				GETPOST('id','int');  		// task id
$timespentid =		GETPOST('lineid');
$confirm =			GETPOST('confirm','alpha');


			// Security check
			$socid=0;
			//if ($user->societe_id > 0) $socid = $user->societe_id;    // For external user, no check is done on company because readability is managed by public status of project and assignement.
			if (!$user->rights->projet->lire) accessforbidden();
			
			$now			= dol_now();
			$nowtmp			= dol_getdate($now);
			$nowday			= $nowtmp['mday'];
			$nowmonth		= $nowtmp['mon'];
			$nowyear		= $nowtmp['year'];
			$nowwday		= $nowtmp['wday'];
			$daytoparse		= $now;
			$year			= GETPOST('reyear')?GETPOST('reyear','int'):(GETPOST('year','int')?GETPOST("year","int"):date("Y"));
			$month			= GETPOST('remonth')?GETPOST('remonth','int'):(GETPOST('month','int')?GETPOST('month','int'):date("m"));
			$day			= GETPOST('reday')?GETPOST('reday','int'):(GETPOST('day','int')?GETPOST('day','int'):date("d"));
			$day			= (int) $day;
//			$week			= GETPOST("week","int")?GETPOST("week","int"):date("W");
			
			//print $day.' - '.$month.' - '.$year;
			
			$basedatearray	= dol_get_first_day_week(24, 11, 2014);
			/*$startdayarray	= dol_get_first_day_week($day, $month, $year);
			$first_day		= $startdayarray['first_day'];
			$first_month	= $startdayarray['first_month'];
			$first_year		= $startdayarray['first_year'];
			$week			= $startdayarray['week'];
			*/
			
			// Define firstdaytoshow and lastdaytoshow (warning: lastdaytoshow is last second to show + 1)
			$basedate		= dol_mktime(12,0,0,11,24,2014);		// Base date is Monday 24-Nov-2014 - start of the pay fortnight cycle
			$focusdate		= dol_mktime(12,0,0,$month,$day,$year);
			$fortnights		= floor(num_between_day($basedate,$focusdate,0)/14);
			$numdays		= $fortnights * 14;
			
			$firstdaytoshow	= dol_time_plus_duree($basedate, $numdays, 'd');
			$lastdaytoshow	= dol_time_plus_duree($firstdaytoshow, 13, 'd');
			$prevdaytoshow	= dol_time_plus_duree($firstdaytoshow, -14, 'd');
			$nextdaytoshow	= dol_time_plus_duree($firstdaytoshow, 14, 'd');
			
			$first			= dol_getdate($firstdaytoshow);
			$first_weekday	= $first['weekday'];
			$first_year		= $first['year'];
			$first_month	= $first['mon'];
			$first_lmonth	= $first['month'];
			$first_day		= $first['mday'];
			$first_tstamp	= dol_mktime(0,0,0,$first_month,$first_day,$first_year);
			$first_date		= dol_print_date($first_tstamp,'%Y-%m-%d');
			
			$last			= dol_getdate($lastdaytoshow);
			$last_weekday	= $last['weekday'];
			$last_year		= $last['year'];
			$last_month		= $last['mon'];
			$last_lmonth	= $last['month'];
			$last_day		= $last['mday'];
			$last_tstamp	= dol_mktime(0,0,0,$last_month,$last_day,$last_year);
			$last_date		= dol_print_date($last_tstamp,'%Y-%m-%d');
			
			$prev			= dol_getdate($prevdaytoshow);
			$prev_year		= $prev['year'];
			$prev_month		= $prev['mon'];
			$prev_day		= $prev['mday'];
			
			$next			= dol_getdate($nextdaytoshow);
			$next_year		= $next['year'];
			$next_month		= $next['mon'];
			$next_day		= $next['mday'];
			
			
			/*
			$usertoprocess=$user;
			*/
			$object=new Task($db);

			/*
			 * Actions
			 */
			
			/*
			$parameters=array('socid'=>$socid, 'projectid'=>$projectid);
			$reshook=$hookmanager->executeHooks('doActions',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
			if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');
			*/

			/*
			 * View
			 */

			$form=new Form($db);
			$formother=new FormOther($db);
			$formcompany=new FormCompany($db);
			$formproject=new FormProjets($db);
			$projectstatic=new Project($db);
			$project = new Project($db);
			$taskstatic = new Task($db);
			$thirdpartystatic = new Societe($db);

			$title=$langs->trans("Fortnight Timecards");

			llxHeader("",$title,"");
/*
			llxHeader("",$title,"",'','','',array('/core/js/timesheet.js'));
			
			print_barre_liste($title, $page, $_SERVER["PHP_SELF"], "", $sortfield, $sortorder, "", $num, '', 'title_project');
			
			$param='';
			$param.=($mode?'&amp;mode='.$mode:'');
			$param.=($search_project_ref?'&amp;search_project_ref='.$search_project_ref:'');
*/
			
			// Show navigation bar
			$nav =' <span style="font-weight:bold" id="month_name">Period: '.$first_weekday.', '.$first_day.' '.$first_lmonth.' '.$first_year.' to '.$last_weekday.', '.$last_day.' '.$last_lmonth.' '.$last_year.'</span>';
			//$nav.='<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$prev_year.'&amp;month='.$prev_month.'&amp;day='.$prev_day.'">'.img_previous($langs->trans("Previous")).' </a>';
			$nav.='<a class="inline-block valignmiddle" href="?year='.$prev_year."&amp;month=".$prev_month."&amp;day=".$prev_day.$param.'">'.img_previous($langs->trans("Previous")).'</a>';
			//$nav.=' <span id=\"month_name\">'.dol_print_date(dol_mktime(0,0,0,$first_month,$first_day,$first_year),"%Y").', '.$langs->trans("WeekShort").' '.$week.' - xxx -</span>';
			//$nav.='<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$next_year.'&amp;month='.$next_month.'&amp;day='.$next_day.'"> '.img_next($langs->trans("Next")).'</a>';
			$nav.='<a class="inline-block valignmiddle" href="?year='.$next_year."&amp;month=".$next_month."&amp;day=".$next_day.$param.'">'.img_next($langs->trans("Next")).'</a>';
			//$nav.=' &nbsp; (<a class="inline-block valignmiddle" href="?userid='.$userid.'&amp;year='.$nowyear.'&amp;month='.$nowmonth.'&amp;day='.$nowday.'">'.$langs->trans("Today").'</a>)';
			$nav.=' &nbsp; (<a href="?year='.$nowyear.'&amp;month='.$nowmonth.'&amp;day='.$nowday.$param.'">'.$langs->trans("Today").'</a>)';
			$nav.=' &nbsp; '.$form->select_date(-1,'',0,0,2,"userid",1,0,1);
			//$nav.='<br>'.$form->select_date(-1,'',0,0,2,"addtime",1,0,1).' ';
			$nav.=' <input type="submit" name="submitdateselect" class="button" value="'.$langs->trans("Refresh").'">';
			
			$picto='calendarweek';
			$head = array();
			$head[0][0] = DOL_URL_ROOT."/custom/timesheet/TimecardUserTimes.php";
			$head[0][1] = $langs->trans("All Fortnightly Timesheets");
			$head[0][2] = 'alltimesheets';
			$head[1][0] = DOL_URL_ROOT."/custom/timesheet/TimecardSingleUserTimes.php";
			$head[1][1] = $langs->trans("Individual Timesheets <not yet functioning>");
			$head[1][2] = 'singletimesheet';

			dol_fiche_head($head, 'alltimesheets', '', 0, 'user');
/*			
			// Show description of content
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
			dol_fiche_end();

			print '<form name="" method="GET" action="'.$_SERVER["PHP_SELF"].($userid > 0 ? '?userid='.$userid : '').'&amp;year='.$nowyear.'&amp;month='.$nowmonth.'&amp;day='.$nowday.'">';
			$tmp = dol_getdate($daytoparse);
			print '<input type="hidden" name="userid" value="'.$userid.'">';

			print '<div class="floatright">'.$nav.'</div>';     // We move this before the assign to components so, the default submit button is not the assign to.
			print '</form>';

/*			// User selection

			print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'">';
			print img_object('','user','class="hideonsmartphone"');
			print $form->select_dolusers((GETPOST('userid')?GETPOST('userid'):$userid), 'userid', 1, '', 0, '', '', 0, 0, 0, '', 0, $langs->trans("ResourceNotAssignedToTheTask"), 'maxwidth400');
			print '<input type="hidden" name="year" value="'.$year.'">';
			print '<input type="hidden" name="month" value="'.$month.'">';
			print '<input type="hidden" name="day" value="'.$day.'">';
			print '<input type="submit" class="button" value="Select">';
			print '</form>';
*/

			/*
			 * Set-up for View
			 */
			//		Employees present in fortnight
			$sql = "SELECT DISTINCT t.fk_user as userid";
			$sql.= ", u.lastname as lastname, u.firstname as firstname";
			$sql.= " FROM ".MAIN_DB_PREFIX."projet_task_time as t";
			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user AS u ON t.fk_user = u.rowid";
			$sql.= " WHERE t.task_date BETWEEN '".$first_date."' AND '".$last_date."'";
			$sql.= " ORDER BY lastname, firstname";

			$resql = $db->query($sql);
			$usersinfn = array();
	
			if ($resql)
			{
				$numusers = $db->num_rows($resql);
				for ($i = 0 ; $i < $numusers; $i++)
					{
						$row = $db->fetch_object($resql);
						$usersinfn[$i] = $row;
					}
				$db->free($resql);
			}
			else
			{
				dol_print_error($db);
			}

			$compdailytime = array();
			$compworkeddaily = array();
			$compbillabledaily = array();
			$comp_ws_workeddaily = array();
			$comp_ws_billabledaily = array();
			$compoverheaddaily = array();
			$comppayabletime = array();
			$total_ws_workedtime = 0;
			$total_ws_billable = 0;

			for ($j = 0 ; $j < $numusers ; $j++)
			{
				print '<div class="clearboth" style="padding-bottom: 8px;"></div>';

				$userstatic = new User($db);
				$userid=$usersinfn[$j]->userid;
				
	//			$userid=	GETPOST('userid','int')?GETPOST('userid','int'):-1;
				$userstatic->fetch($userid);
		
//		print 'userstatic :'.$userstatic->array_options['options_emptype'].'<br/>';
		//$object->fetch_optionals($rowid,$extralabels);
//        print_r ($extraemptype);exit;

				switch ($userstatic->array_options['options_emptype']) {
				   case 1:
						 $useremptype = "Full-Time";
						 $paidleave = 1;
						 break;
				   case 2:
						 $useremptype = "Part-time";
 						 $paidleave = 1;
						 break;
				   case 3:
						 $useremptype = "Casual";
						 $paidleave = 0;
						 break;
				   case 4:
						 $useremptype = "Contractor";
						 $paidleave = 0;
						 break;
				   default:
						 $useremptype = "Error: 'Employee Type' needs to be added in 'User' setup";
						 $paidleave = 0;
						 break;
				}

				switch ($userstatic->array_options['options_dept']) {
				   case 1:
						 $department = "Administration";
						 $workshop = 0;
						 break;
				   case 2:
						 $department = "Project Management Office";
 						 $workshop = 0;
						 break;
				   case 3:
						 $department = "Mechanic Shop";
						 $workshop = 1;
						 break;
				   case 4:
						 $department = "Panel Shop";
						 $workshop = 1;
						 break;
				   case 5:
						 $department = "Powerhouse";
						 $workshop = 1;
						 break;
				   default:
						 $department = "Error: 'Department' needs to be added in 'User' setup";
						 $workshop = 0;
						 break;
				}
				
				$moreparam='&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day;
				dol_banner_tab($userstatic,'userid','',0,'','','',$moreparam,0,'',$useremptype.'&nbsp;-&nbsp;',1,'');
	
				$maxminarray = array();
				$offtimearray = array();
				$adminoheadarray = array();
				$workedoheadarray = array();
				$workshopoheadarray = array();
				$timesarray = array();
				$time1 = array();
				$time3 = array();
	
				/*
				 *  List of time spent
				 */
	
				for ($k = 0 ; $k < 14 ; $k++)
				{
					$dayduration = array();
					$pbreaks = array();
					$upbreaks = array();
					$pleave = array();
					$upleave = array();
					$overheads = array();
					$workedtime = array();
					$billtime = array();
					$payabletime = array();	

					$daytoshow	= dol_time_plus_duree($firstdaytoshow, $k, 'd');
					$kdate			= dol_getdate($daytoshow);
					$kdate_weekday	= $kdate['weekday'];
					$kdate_year		= $kdate['year'];
					$kdate_month	= $kdate['mon'];
					$kdate_lmonth	= $kdate['month'];
					$kdate_day		= $kdate['mday'];
					$kdate_tstamp	= dol_mktime(0,0,0,$kdate_month,$kdate_day,$kdate_year);
					$kdate_date		= dol_print_date($kdate_tstamp,'%Y-%m-%d');
	
					// Max, Min Times, durations and count - potential to use for error checks
					$sql = "SELECT COUNT(t.rowid) as timescount, MIN(t.task_datehour) as start_time, MAX(t.task_datehour) as end_time, SUM(t.task_duration) as total_time";
					$sql.= " FROM ".MAIN_DB_PREFIX."projet_task_time as t";
					$sql.= " WHERE t.task_date = '".$kdate_date."'";
					$sql.= " AND t.fk_user = ".$userid;
	
					$resql = $db->query($sql);
			
					if ($resql)
					{
						$row = $db->fetch_object($resql);
						$maxminarray[$k] = $row;
						$db->free($resql);
					}
					else
					{
						dol_print_error($db);
					}
	
					// Leave & Break Times
					$sql = "SELECT SUM(ptt.task_duration) as overhead_time, ptt.rowid, ptt.fk_task";
					$sql.= ", pt.label as label";
					$sql.= " FROM ".MAIN_DB_PREFIX."projet_task_time as ptt";
					$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task AS pt ON ptt.fk_task = pt.rowid";
					$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet AS p ON pt.fk_projet = p.rowid";
					$sql.= " WHERE ptt.task_date = '".$kdate_date."'";
					$sql.= " AND ptt.fk_user = ".$userid;
					$sql.= " AND p.ref IN ('AD14004','AD14005')";
					$sql.= " GROUP BY ptt.fk_task";
	
					$resql = $db->query($sql);
					if ($resql)
					{
						$ohtimes = $db->num_rows($resql);
						for ($i = 0 ; $i < $ohtimes; $i++)
							{
								$row = $db->fetch_object($resql);
								$offtimearray[$k][$row->fk_task] = $row;
							}
						$db->free($resql);
					}
					else
					{
						dol_print_error($db);
					}

					// Admin Overhead Times
					$sql = "SELECT SUM(ptt.task_duration) as overhead_time, ptt.rowid, ptt.fk_task";
					$sql.= ", pt.label as label";
					$sql.= " FROM ".MAIN_DB_PREFIX."projet_task_time as ptt";
					$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task AS pt ON ptt.fk_task = pt.rowid";
					$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet AS p ON pt.fk_projet = p.rowid";
					$sql.= " WHERE ptt.task_date = '".$kdate_date."'";
					$sql.= " AND ptt.fk_user = ".$userid;
					$sql.= " AND p.ref IN ('AD14001','AD14003')";
//					$sql.= " GROUP BY ptt.fk_task";
	
					$resql = $db->query($sql);
					if ($resql)
					{
						$ohtimes = $db->num_rows($resql);
						for ($i = 0 ; $i < $ohtimes; $i++)
							{
								$row = $db->fetch_object($resql);
								$adminoheadarray[$k] = $row;
//								$adminoheadarray[$k][$row->fk_task] = $row;
							}
						$db->free($resql);
					}
					else
					{
						dol_print_error($db);
					}

//			        print_r ($adminoheadarray);print '<br/>';

					// Workshop Overhead Times
					$sql = "SELECT SUM(ptt.task_duration) as overhead_time, ptt.rowid, ptt.fk_task";
					$sql.= ", pt.label as label";
					$sql.= " FROM ".MAIN_DB_PREFIX."projet_task_time as ptt";
					$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task AS pt ON ptt.fk_task = pt.rowid";
					$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet AS p ON pt.fk_projet = p.rowid";
					$sql.= " WHERE ptt.task_date = '".$kdate_date."'";
					$sql.= " AND ptt.fk_user = ".$userid;
					$sql.= " AND p.ref IN ('AD14002','AD14006')";
//					$sql.= " GROUP BY ptt.fk_task";
	
					$resql = $db->query($sql);
					if ($resql)
					{
						$ohtimes = $db->num_rows($resql);
						for ($i = 0 ; $i < $ohtimes; $i++)
							{
								$row = $db->fetch_object($resql);
								$workedoheadarray[$k] = $row;
//								$workedoheadarray[$k][$row->fk_task] = $row;
							}
						$db->free($resql);
					}
					else
					{
						dol_print_error($db);
					}

					// All times for User / Employee
					
					$sql = "SELECT t.rowid as trowid, t.task_date as tdate, t.task_datehour as tstart_time, t.task_duration as tduration";
		//			$sql.= ", u.lastname as lastname, u.firstname as firstname";
					$sql.= " FROM ".MAIN_DB_PREFIX."projet_task_time as t";
		//			$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."user AS u ON t.fk_user = u.rowid";
					$sql.= " WHERE t.task_date = '".$kdate_date."'";
					$sql.= " AND t.fk_user = ".$userid;
					$sql.= " ORDER BY t.task_datehour";
	
					$resql = $db->query($sql);
			
					if ($resql)
					{
						$numtimes = $db->num_rows($resql);
						if ($numtimes >0);
						{
							for ($i = 0 ; $i < $numtimes; $i++)
							{
								$row = $db->fetch_object($resql);
								$timesarray[$k][$i] = $row;
							}
						}
						$db->free($resql);
					}
					else
					{
						dol_print_error($db);
					}
				}
	
/*
				// PJR was POST
				print '<form method="GET" action="'.$_SERVER["PHP_SELF"].'?userid='.$userid.'&amp;year='.$year.'&amp;month='.$month.'&amp;day='.$day.'">';
				print '<input type="hidden" name="action" value="">';
				print '<input type="hidden" name="userid" value="'.$userid.'">';
				print '<input type="hidden" name="year" value="'.$year.'">';
				print '<input type="hidden" name="month" value="'.$month.'">';
				print '<input type="hidden" name="day" value="'.$day.'">';
				print '<input type="hidden" name="id" value="'.$id.'">';
*/
	
				print '<div class="clearboth" style="padding-bottom: 8px;"></div>';
				
				print '<div class="div-table-responsive">';
				print '<table class="tagtable liste" id="tablelines3">';

				// Heading Row
				print '<tr class="liste_titre">';
				print '<td>Description</td>';
				//print '<td align="right" class="maxwidth75">'.$langs->trans("TimeSpent").'</td>';
		
		
				for($i=0;$i<14;$i++)
				{
					// This section could be combined with previous FOR loop - but hey, saves time for me now!
					$daytoshow		= dol_time_plus_duree($firstdaytoshow, $i, 'd');
					$kdate			= dol_getdate($daytoshow);
					$kdate_year		= $kdate['year'];
					$kdate_month	= $kdate['mon'];
					$kdate_day		= $kdate['mday'];
					$kdate_tstamp	= dol_mktime(0,0,0,$kdate_month,$kdate_day,$kdate_year);
					$kdate_date		= dol_print_date($kdate_tstamp,'%Y-%m-%d');
					$kdate_date_a	= dol_print_date($kdate_tstamp, '%a');	
					$kdate_date_drf	= dol_print_date($kdate_tstamp, 'dayreduceformat');	

//		'<a href="'.DOL_URL_ROOT.'/projet/tasks/time.php?id='.$lines[$i]->id.($showproject?'':'&withproject=1').'">'

					print '<td width="6%" align="center" class="hide'.$i.'"><a href="'.DOL_URL_ROOT.'/custom/timesheet/Timecard.php?userid='.$userid.'&amp;year='.$kdate_year.'&amp;month='.$kdate_month.'&amp;day='.$kdate_day.'">'.$kdate_date_a.'<br>'.$kdate_date_drf.'</a></td>';
				}
				print '<td>Totals<br><i><small>(dec.)</small></i></td>';
				print '</tr>';
				
				// Start Time Row
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">Start Time</td>';
				
				for($i=0;$i<14;$i++)
				{
					$time1[$i]=$db->jdate($timesarray[$i][0]->tstart_time);
					print '<td width="6%" align="center" class="hide'.$i.'">'.dol_print_date($time1[$i], '%H:%M', '').'</td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';

				// Finish Time Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">Finish Time</td>';

				for($i=0;$i<14;$i++)
				{
					$ftc	= $maxminarray[$i]->timescount;		// counter for last time session of day - to grab last duration for adding to last session starting time
					if ($ftc > 0)
					{
						$time2	= $db->jdate($timesarray[$i][$ftc-1]->tstart_time);
						$time3[$i]	= $time2+$timesarray[$i][$ftc-1]->tduration;
						$dayduration[$i] = $time3[$i] - $time1[$i];
						print '<td width="6%" align="center" class="hide'.$i.'">'.dol_print_date($time3[$i], '%H:%M', '').'</td>';
					}
					else
					{
						print '<td></td>';
					}
				}
				print '<td>&nbsp;</td>';
				print '</tr>';

				// Breaks Heading				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top" colspan="16"><b>Breaks</b></td>';
				print '</tr>';
				
				// Morning Tea Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Morning Tea <i>(mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
					$tea	= $offtimearray[$i][15]->overhead_time / 60;		// 15 is taskid for morning tea which was in seconds
					if ($tea > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.$tea.'</td>';
						$timededuct= max(($tea-15),0)*60;	// 15 minutes is max allowed paid break
						$upbreaks[$i] += $timededuct;
						$pbreaks[$i] += min(($tea),15)*60;	// 15 minutes is max allowed paid break
						$tea=0;
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';
				
				// Lunch Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Lunch <i>(mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
					$tea	= $offtimearray[$i][16]->overhead_time / 60;		// 16 is taskid for lunch which was in seconds
					if ($tea > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.$tea.'</td>';
						$upbreaks[$i] += $tea*60;
						$tea=0;
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';
				
				// Afternoon Tea Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Afternoon tea <i>(mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
					$tea	= $offtimearray[$i][17]->overhead_time / 60;		// 17 is taskid for afternoon tea which was in seconds
					if ($tea > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.$tea.'</td>';
						$atbreak = 0;
						if ($dayduration[$i] > 9*3600) $atbreak = 0;  // paid afternoon tea break was 10 minutes if employee works more than 9 hour day - only used by Colin who does not take afternoon tea
						$timededuct= max(($tea-$atbreak),0)*60;	// $atbreak (10 minutes) is max allowed paid break if working over 9 hours
						$upbreaks[$i] += $timededuct;
						$tea=0;
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';
				
				// Other Breaks Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Other break <i>(mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
					$tea	= $offtimearray[$i][18]->overhead_time / 60;		// 18 is taskid for other breaks which was in seconds
					if ($tea > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.$tea.'</td>';
						$upbreaks[$i] += $tea*60;
						$tea=0;
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';
				

				// Absences Heading				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top" colspan="16"><b>Absences / Leave</b></td>';
				print '</tr>';
				
				if ($paidleave == 1) // Full-Time and Part-Time Employees
				{
					// Annual Leave Row				
					print "<tr ".$bc[$var].">";
					print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Annual Leave <i>(hours:mins)</i></td>';
					
					$totalannualleave = 0;
					for($i=0;$i<14;$i++)
					{
						$leave	= $offtimearray[$i][62]->overhead_time;		// 62 is taskid for annual leave which was in seconds
						if ($leave > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($leave,'allhourmin').'</td>';
							$pleave[$i] += $leave;
							$totalannualleave += round(($leave / 3600),2);
							$leave=0;
						}
						else print '<td></td>';
					}
					print '<td align="right"><b>'.number_format($totalannualleave, 2, '.', '').'</b></td>';
					print '</tr>';
					
					// Personal Leave				
					print "<tr ".$bc[$var].">";
					print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Personal Leave <i>(hours:mins)</i></td>';
					
					$totalpersonalleave = 0;
					for($i=0;$i<14;$i++)
					{
						$leave	= $offtimearray[$i][63]->overhead_time;		// 63 is taskid for personal leave which was in seconds
						if ($leave > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($leave,'allhourmin').'</td>';
							$pleave[$i] += $leave;
							$totalpersonalleave += round(($leave / 3600),2);
							$leave=0;
						}
						else print '<td></td>';
					}
					print '<td align="right"><b>'.number_format($totalpersonalleave, 2, '.', '').'</b></td>';
					print '</tr>';
					
					// Public Holidays Row
					print "<tr ".$bc[$var].">";
					print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Public Holidays <i>(hours:mins)</i></td>';

					$totalpubhols = 0;
					for($i=0;$i<14;$i++)
					{
						$leave	= $offtimearray[$i][64]->overhead_time;		// 64 is taskid for public holidays which was in seconds
						if ($leave > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($leave,'allhourmin').'</td>';
							$pleave[$i] += $leave;
							$totalpubhols += round(($leave / 3600),2);
							$leave=0;
						}
						else print '<td></td>';
					}					
					print '<td align="right"><b>'.number_format($totalpubhols, 2, '.', '').'</b></td>';
					print '</tr>';
				}
				else // Casual employees and contractors
				{
					// Recognised Absence (no timecard) Row				
					print "<tr ".$bc[$var].">";
					print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Recognised no timecard <i>(hrs:mins)</i></td>';

					$totalabsence = 0;					
					for($i=0;$i<14;$i++)
					{
						$leave	= $offtimearray[$i][727]->overhead_time;		// 727 is taskid for annual leave which was in seconds
						if ($leave > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($leave,'allhourmin').'</td>';
							$upleave[$i] += $leave;
							$totalabsence += round(($leave / 3600),2);
							$leave=0;
						}
						else print '<td></td>';
					}
					print '<td align="right"><b>'.number_format($totalabsence, 2, '.', '').'</b></td>';
					print '</tr>';
				}


				// Attendance Time (hours-mins) Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Attendance Time <i>(hours:mins)</i></b></td>';

				$totalattendance = 0;
				for($i=0;$i<14;$i++)
				{
				$attendancetime = $dayduration[$i] - $pleave[$i] - $upleave[$i];
					if ($attendancetime > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($attendancetime,'allhourmin').'</td>';
						$totalattendance += round(($attendancetime / 3600),2);
					}
					elseif ($attendancetime == 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">00:00</td>';
					}
					else
					{
						print '<td width="6%" align="center" class="hide'.$i.'">ERROR '.$attendancetime.'</td>';
					}
				}
				print '<td align="right"><b>'.number_format($totalattendance, 2, '.', '').'</b></td>';
				print '</tr>';

				// Total Unpaid Breaks Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Total unpaid breaks <i>(mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
				$dailybreaks = $upbreaks[$i] / 60;
					if ($dailybreaks > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.$dailybreaks.'</td>';
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';

				// Total Paid Breaks Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Total paid breaks <i>(mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
				$dailybreaks = $pbreaks[$i] / 60;
					if ($dailybreaks > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.$dailybreaks.'</td>';
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';

				// Payable Time (hours-mins) Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Payable Time <i>(hours:mins)</i></b></td>';
				
				$totalpayable = 0;
				for($i=0;$i<14;$i++)
				{
				$payabletime[$i] = $dayduration[$i] - $upleave[$i] - $upbreaks[$i];
					if ($payabletime[$i] > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($payabletime[$i],'allhourmin').'</td>';
						$totalpayable += round(($payabletime[$i] / 3600),2);
					}
					elseif ($payabletime[$i] == 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">00:00</td>';
					}
					else
					{
						print '<td width="6%" align="center" class="hide'.$i.'">ERROR '.$payabletime[$i].'</td>';
					}
				}
				print '<td align="right"><b>'.number_format($totalpayable, 2, '.', '').'</b></td>';
				print '</tr>';

				// Worked Time (hours-mins) Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Worked Time <i>(hours:mins)</i></b></td>';
				
				$totalworked = 0;
				for($i=0;$i<14;$i++)
				{
					$workedtime[$i] = $dayduration[$i] - $upleave[$i] - $upbreaks[$i] - $pleave[$i] - $pbreaks[$i];
					$compworkeddaily[$i] += round(($workedtime[$i] / 3600),2);
					$totalworked += round(($workedtime[$i] / 3600),2);
					if ($workshop == 1)
					{
						$comp_ws_workeddaily[$i] += round(($workedtime[$i] / 3600),2);
						$total_ws_workedtime += round(($workedtime[$i] / 3600),2);
					}
					if ($workedtime[$i] > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($workedtime[$i],'allhourmin').'</td>';
											}
					elseif ($workedtime[$i] == 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">00:00</td>';
					}
					else
					{
						print '<td width="6%" align="center" class="hide'.$i.'">ERROR '.$workedtime[$i].'</td>';
					}
				}
				print '<td align="right"><b>'.number_format($totalworked, 2, '.', '').'</b></td>';
				print '</tr>';

				
				// Office Overhead Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Office overhead time <i>(hours:mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
//					$offoh	= $offtimearray[$i][42]->overhead_time;		// 42 is taskid for workshop overhead which was in seconds
					$offoh	= $adminoheadarray[$i]->overhead_time;
					if ($offoh > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($offoh,'allhourmin').'</td>';
						$overheads[$i] += $offoh;
						$offoh=0;
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';
				
				// Workshop Overhead Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top">&nbsp;&nbsp;&nbsp;&nbsp;Workshop overhead time <i>(hours:mins)</i></td>';
				
				for($i=0;$i<14;$i++)
				{
//					$wsoh	= $offtimearray[$i][21]->overhead_time;		// 21 is taskid for workshop overhead which was in seconds
					$wsoh	= $workedoheadarray[$i]->overhead_time;
					if ($wsoh > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($wsoh,'allhourmin').'</td>';
						$overheads[$i] += $wsoh;
						$wsoh=0;
					}
					else print '<td></td>';
				}
				print '<td>&nbsp;</td>';
				print '</tr>';

				// Total Overhead Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Overhead Time <i>(hours:mins)</i></b></td>';
				$totaloverhead = 0;				
				for($i=0;$i<14;$i++)
				{
					$compoverheaddaily[$i] += round(($overheads[$i] / 3600),2);
					if ($overheads[$i] > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($overheads[$i],'allhourmin').'</td>';
						$totaloverhead += round(($overheads[$i] / 3600),2);
					}
					else print '<td></td>';
				}
				print '<td align="right"><b>'.number_format($totaloverhead, 2, '.', '').'</b></td>';
				print '</tr>';

				// Billable Hours (hours-mins) Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Billable Hours <i>(hours:mins)</i></b></td>';
				$totalbillable = 0;
				for($i=0;$i<14;$i++)
				{
				$billtime[$i] = $workedtime[$i] - $overheads[$i];
				$compbillabledaily[$i] += round(($workedtime[$i] / 3600),2) - round($overheads[$i]/3600,2);
				$totalbillable += round(($workedtime[$i] / 3600),2) - round($overheads[$i]/3600,2);
				if ($workshop == 1)
				{
					$comp_ws_billabledaily[$i] += round(($workedtime[$i] / 3600),2) - round($overheads[$i]/3600,2);
					$total_ws_billable += round(($workedtime[$i] / 3600),2) - round($overheads[$i]/3600,2);
				}

					if ($billtime[$i] > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.convertSecondToTime($billtime[$i],'allhourmin').'</td>';
					}
					elseif ($billtime[$i] == 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">00:00</td>';
					}
					else print '<td></td>';
				}

				print '<td align="right"><b>'.number_format($totalbillable, 2, '.', '').'</b></td>';
				print '</tr>';

				// Productivity Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Productivity <i>(percentage)</i></b></td>';
				for($i=0;$i<14;$i++)
				{
					if ($workedtime[$i] > 0)
					{
						$productivity = round(100*($billtime[$i]) / $workedtime[$i], 1);
						if ($productivity > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.$productivity.'%</td>';
						}
						else print '<td align="center">0.0%</td>';
					}
					else print '<td></td>';
				}
				if ($totalworked > 0)
				{
					print '<td align="right"><b>'.round(100*$totalbillable/$totalworked,1).'%</b></td>';
				}
				else print '<td align="right"><b>0.0%</b></td>';
				print '</tr>';


				// Check Time Worked (hours-mins) Row				
				$checktime = array();
				$checktotal = 0;
				for($i=0;$i<14;$i++)
				{
					$checktime[$i] = $dayduration[$i] - $maxminarray[$i]->total_time;
					$checktotal += abs($checktime[$i]); // we use ABS() in case two errors cancel each other out
				}
				if ($checktotal != 0)
				{
					print "<tr ".$bc[$var].">";
					print '<td class="nowrap" valign="top">Data Error <i>(hours:mins)</i>'.img_error("Error").'</td>';
					
					for($i=0;$i<14;$i++)
					{
						if ($checktime[$i] < 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.img_error("Error").'(-)'.convertSecondToTime(abs($checktime[$i]),'allhourmin').'</td>';
						}
						elseif ($checktime[$i] > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.img_error("Error").convertSecondToTime($checktime[$i],'allhourmin').'</td>';
						}
						else print '<td></td>';
					}
					print '<td>&nbsp;</td>';
					print '</tr>';
				}

				// Payable Time (decimal hours) Row				
				print "<tr ".$bc[$var]."><td colspan='16'>&nbsp;</td></tr>";
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Payable Time <i>(decimal hours)</i></b></td>';
				$totalpayabletime = 0;
				for($i=0;$i<14;$i++)
				{
				$comppayabletime[$i] += round(($payabletime[$i] / 3600),2);
				$totalpayabletime += round(($payabletime[$i] / 3600),2);
					if ($payabletime[$i] > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'"><b>'.number_format(round(($payabletime[$i] / 3600),2), 2, '.', '').'</b></td>';
					}
					else print '<td></td>';
				}
				print '<td align="right"><b>'.number_format($totalpayabletime, 2, '.', '').'</b></td>';
				print '</tr>';

				print "</table>";
				print '</div>';
				print '<br/><br/>';

			}
				/*
				 * Company Totals
				 */

				print '<div class="clearboth" style="padding-bottom: 8px;"></div>';
				print '<div class="inline-block floatleft valignmiddle refid">';
				print 'Company Totals';
				print '</div>';
				
				print '<div class="div-table-responsive">';
				print '<table class="tagtable liste" id="tablelines3">';

				// Heading Row
				print '<tr class="liste_titre">';
				print '<td>Description</td>';
				//print '<td align="right" class="maxwidth75">'.$langs->trans("TimeSpent").'</td>';
		
				for($i=0;$i<14;$i++)
				{
					print '<td width="6%" align="center" class="hide'.$i.'">'.dol_print_date($first_tstamp + ($i * 3600 * 24), '%a').'<br>'.dol_print_date($first_tstamp + ($i * 3600 * 24), 'dayreduceformat').'</td>';
				}
				print '<td>Totals</td>';
				print '</tr>';


				// Total Payable Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Payable Time <i>(decimal hours)</i></b></td>';
				$totalpayable = 0;				
				for($i=0;$i<14;$i++)
				{
					if ($comppayabletime[$i]  > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.number_format(($comppayabletime[$i]), 2, '.', '').'</td>';
						$totalpayable += round($comppayabletime[$i] ,2) ;
					}
					else print '<td></td>';
				}
				print '<td align="right"><b>'.number_format($totalpayable, 2, '.', '').'</b></td>';
				print '</tr>';


				// Time Worked (decimal hours) Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Time Worked <i>(decimal hours)</i></b></td>';
				$totalworkedtime = 0;
				for($i=0;$i<14;$i++)
				{
				$totalworkedtime += $dailytime;
					if ($compworkeddaily[$i] > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.number_format($compworkeddaily[$i], 2, '.', '').'</td>';
						$totalworkedtime += round($compworkeddaily[$i] ,2) ;
					}
					else print '<td></td>';
				}
				print '<td align="right"><b>'.number_format($totalworkedtime, 2, '.', '').'</b></td>';
				print '</tr>';


				// Total Overhead Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Overhead Time <i>(decimal hours)</i></b></td>';
				$totaloverhead = 0;				
				for($i=0;$i<14;$i++)
				{
					if ($compoverheaddaily[$i] > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.number_format(($compoverheaddaily[$i]), 2, '.', '').'</td>';
						$totaloverhead += round($compoverheaddaily[$i],2) ;
					}
					else print '<td></td>';
				}
				print '<td align="right"><b>'.number_format($totaloverhead, 2, '.', '').'</b></td>';
				print '</tr>';

				// Billable Hours (hours-mins) Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Total Billable Hours <i>(decimal hours)</i></b></td>';
				$totalbillable = 0;				
				for($i=0;$i<14;$i++)
				{
				$billtime = round($compbillabledaily[$i],2);
				$totalbillable += $billtime;
					if ($billtime > 0)
					{
						print '<td width="6%" align="center" class="hide'.$i.'">'.number_format(($compbillabledaily[$i]), 2, '.', '').'</td>';
					}
					else print '<td></td>';
				}
				print '<td align="right"><b>'.number_format($totalbillable, 2, '.', '').'</b></td>';
				print '</tr>';


				// Productivity Payable Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Productivity Payable<i> (percentage)</i></b></td>';
				for($i=0;$i<14;$i++)
				{
					if ($comppayabletime[$i] > 0)
					{
					$productivity = round(100*$compbillabledaily[$i] / $comppayabletime[$i], 1);
						if ($productivity > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.$productivity.'%</td>';
						}
						else print '<td></td>';
					}
					else print '<td></td>';
				}
				if ($totalpayable > 0)
				{
					print '<td align="right"><b>'.round(100*$totalbillable/$totalpayable,1).'%</b></td>';
				}
				else print '<td align="right"><b>0.0%</b></td>';
				print '</tr>';

				// Productivity Worked Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Productivity Worked<i> (percentage)</i></b></td>';
				for($i=0;$i<14;$i++)
				{
					if ($compworkeddaily[$i] > 0)
					{
					$productivity = round(100*$compbillabledaily[$i] / $compworkeddaily[$i], 1);
						if ($productivity > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.$productivity.'%</td>';
						}
						else print '<td></td>';
					}
					else print '<td></td>';
				}
				if ($totalworkedtime > 0)
				{
					print '<td align="right"><b>'.round(100*$totalbillable/$totalworkedtime,1).'%</b></td>';
				}
				else print '<td align="right"><b>0.0%</b></td>';		
				print '</tr>';

				// Productivity Workshop Row				
				print "<tr ".$bc[$var].">";
				print '<td class="nowrap" valign="top"><b>Productivity Workshop<i> (percentage)</i></b></td>';
				for($i=0;$i<14;$i++)
				{
					if ($comp_ws_workeddaily[$i] > 0)
					{
					$productivity = round(100*$comp_ws_billabledaily[$i] / $comp_ws_workeddaily[$i], 1);
						if ($productivity > 0)
						{
							print '<td width="6%" align="center" class="hide'.$i.'">'.$productivity.'%</td>';
						}
						else print '<td></td>';
					}
					else print '<td></td>';
				}
				if ($total_ws_workedtime > 0)
				{
					print '<td align="right"><b>'.round(100*$total_ws_billable/$total_ws_workedtime,1).'%</b></td>';
				}
				else print '<td align="right"><b>0.0%</b></td>';
				print '</tr>';

				print "</table>";
				print '</div>';
				print '<br/><br/>';


			llxFooter();
			$db->close();