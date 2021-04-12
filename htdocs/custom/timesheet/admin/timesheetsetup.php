<?php
/*
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
 *  \file       htdocs/admin/project.php
 *  \ingroup    project
 *  \brief      Page to setup project module
 */

include '../core/lib/includeMain.lib.php';
include '../core/lib/generic.lib.php';

require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/project.lib.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';

$langs->load("admin");
$langs->load("errors");
$langs->load("other");
$langs->load("timesheet@timesheet");
        
if (!$user->admin) {
    $accessforbidden = accessforbidden("you need to be admin");           
}
$action = $_GET['action'];
$timetype=$conf->global->TIMESHEET_TIME_TYPE;
$timeSpan=$conf->global->TIMESHEET_TIME_SPAN;
$hoursperday=$conf->global->TIMESHEET_DAY_DURATION;
$maxhoursperday=$conf->global->TIMESHEET_DAY_MAX_DURATION;
$maxApproval=$conf->global->TIMESHEET_MAX_APPROVAL;
$hidedraft=$conf->global->TIMESHEET_HIDE_DRAFT;
$hideref=$conf->global->TIMESHEET_HIDE_REF;
$hidezeros=$conf->global->TIMESHEET_HIDE_ZEROS;
$approvalbyweek=$conf->global->TIMESHEET_APPROVAL_BY_WEEK;
$headers=$conf->global->TIMESHEET_HEADERS;
$whiteListMode=$conf->global->TIMESHEET_WHITELIST_MODE;
$whiteList=$conf->global->TIMESHEET_WHITELIST;
$dropdownAjax=$conf->global->MAIN_DISABLE_AJAX_COMBOX;
$draftColor=$conf->global->TIMESHEET_COL_DRAFT;
$submittedColor=$conf->global->TIMESHEET_COL_SUBMITTED;
$approvedColor=$conf->global->TIMESHEET_COL_APPROVED;
$rejectedColor=$conf->global->TIMESHEET_COL_REJECTED;
$cancelledColor=$conf->global->TIMESHEET_COL_CANCELLED;
$addholidaytime=$conf->global->TIMESHEET_ADD_HOLIDAY_TIME;
$adddocs=$conf->global->TIMESHEET_ADD_DOCS;
$opendays=str_split($conf->global->TIMESHEET_OPEN_DAYS);
$addForOther=$conf->global->TIMESHEET_ADD_FOR_OTHER;
$noteOnPDF=$conf->global->TIMESHEET_PDF_NOTEISOTASK;
$showTimespentNote=$conf->global->TIMESHEET_SHOW_TIMESPENT_NOTE;
//Invoice part

$invoicetasktime=$conf->global->TIMESHEET_INVOICE_TASKTIME;
$invoicemethod=$conf->global->TIMESHEET_INVOICE_METHOD;
$invoiceservice=$conf->global->TIMESHEET_INVOICE_SERVICE;
$invoiceshowtask=$conf->global->TIMESHEET_INVOICE_SHOW_TASK;
$invoiceshowuser=$conf->global->TIMESHEET_INVOICE_SHOW_USER;
$searchbox=intval($conf->global->TIMESHEET_SEARCHBOX);
if(sizeof($opendays)!=8)$opendays=array('_','0','0','0','0','0','0','0');
$apflows=str_split($conf->global->TIMESHEET_APPROVAL_FLOWS);
if(sizeof($apflows)!=6)$apflows=array('_','0','0','0','0','0');
$error=0;
function null2int($var,$int=0){
    return ($var=='')?$int:$var;
} 
switch($action)
{
    case "save":
        //general option
        $timetype=$_POST['timeType'];
        $timeSpan=$_POST['timeSpan'];
        if($timeSpan!=$conf->global->TIMESHEET_TIME_SPAN){
            $sql = 'DELETE FROM '.MAIN_DB_PREFIX.'project_task_timesheet';
            $sql.= ' WHERE status IN (1,5)'; //'DRAFT','REJECTED'
            dol_syslog(__METHOD__);
            $resql = $db->query($sql);
        }
        $hoursperday=null2int($_POST['hoursperday']);
        $maxhoursperday=null2int($_POST['maxhoursperday']);
        $hidedraft=null2int($_POST['hidedraft']);
        $hidezeros=null2int($_POST['hidezeros']);
        $maxApproval=null2int($_POST['maxapproval'],5);
        $approvalbyweek=null2int($_POST['approvalbyweek']);
        $hideref=null2int($_POST['hideref']);        
        $whiteListMode=null2int($_POST['blackWhiteListMode']);
        $whiteList=null2int($_POST['blackWhiteList']);
        $dropdownAjax=null2int($_POST['dropdownAjax']);
        $addForOther=null2int($_POST['addForOther']);
        dolibarr_set_const($db, "TIMESHEET_TIME_TYPE", $timetype, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_TIME_SPAN", $timeSpan, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_DAY_DURATION", $hoursperday, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_DAY_MAX_DURATION", $maxhoursperday, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_HIDE_DRAFT", $hidedraft, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_HIDE_ZEROS", $hidezeros, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_APPROVAL_BY_WEEK", $approvalbyweek, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_MAX_APPROVAL", $maxApproval, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_HIDE_REF", $hideref, 'chaine', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_WHITELIST_MODE", $whiteList?$whiteListMode:2, 'int', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_WHITELIST", $whiteList, 'int', 0, '', $conf->entity);
        dolibarr_set_const($db, "MAIN_DISABLE_AJAX_COMBOX", $dropdownAjax, 'int', 0, '', $conf->entity);
        dolibarr_set_const($db, "TIMESHEET_ADD_FOR_OTHER", $addForOther, 'int', 0, '', $conf->entity);
        //headers handling
        $showProject=$_POST['showProject'];
        $showTaskParent=$_POST['showTaskParent'];
        $showTasks=$_POST['showTasks'];
        $showDateStart=$_POST['showDateStart'];
        $showDateEnd=$_POST['showDateEnd'];
        $showProgress=$_POST['showProgress'];
        $showCompany=$_POST['showCompany'];
        $showNote=$_POST['showNote'];
        $showTotal=$_POST['showTotal'];
        
        $headers=$showNote?'Note':'';
        $headers.=$showCompany?(empty($headers)?'':'||').'Company':'';
        $headers.=$showProject?(empty($headers)?'':'||').'Project':'';
        $headers.=$showTaskParent?(empty($headers)?'':'||').'TaskParent':'';
        $headers.=$showTasks?(empty($headers)?'':'||').'Tasks':'';
        $headers.=$showDateStart?(empty($headers)?'':'||').'DateStart':'';
        $headers.=$showDateEnd?(empty($headers)?'':'||').'DateEnd':'';
        $headers.=$showProgress?(empty($headers)?'':'||').'Progress':'';
        $headers.=$showTotal?(empty($headers)?'':'||').'Total':'';
        dolibarr_set_const($db, "TIMESHEET_HEADERS", $headers, 'chaine', 0, '', $conf->entity);
        
        //color handling
        $draftColor=$_POST['draftColor'];
        dolibarr_set_const($db, "TIMESHEET_COL_DRAFT", $draftColor, 'chaine', 0, '', $conf->entity);
        
        $submittedColor=$_POST['submittedColor'];
        dolibarr_set_const($db, "TIMESHEET_COL_SUBMITTED", $submittedColor, 'chaine', 0, '', $conf->entity);
        
        $approvedColor=$_POST['approvedColor'];
        dolibarr_set_const($db, "TIMESHEET_COL_APPROVED", $approvedColor, 'chaine', 0, '', $conf->entity);
        
        $rejectedColor=$_POST['rejectedColor'];
        dolibarr_set_const($db, "TIMESHEET_COL_REJECTED", $rejectedColor, 'chaine', 0, '', $conf->entity);
        
        $cancelledColor=$_POST['cancelledColor'];
        dolibarr_set_const($db, "TIMESHEET_COL_CANCELLED", $cancelledColor, 'chaine', 0, '', $conf->entity);
        
        $addholidaytime=$_POST['addholidaytime'];
        dolibarr_set_const($db, "TIMESHEET_ADD_HOLIDAY_TIME", $addholidaytime, 'chaine', 0, '', $conf->entity);
        
        $adddocs=null2int($_POST['adddocs']);
        dolibarr_set_const($db, "TIMESHEET_ADD_DOCS", $adddocs, 'chaine', 0, '', $conf->entity);
                
        $opendays=array('_','0','0','0','0','0','0','0');
        foreach($_POST['opendays'] as $key => $day){
            $opendays[$key]=$day;
        }
        dolibarr_set_const($db, "TIMESHEET_OPEN_DAYS", implode('',$opendays), 'chaine', 0, '', $conf->entity);
                

        $apflows=array('_','0','0','0','0','0');
        foreach($_POST['apflows'] as $key => $flow){
            $apflows[$key]=$flow;
        }
        
        //INVOICE
        dolibarr_set_const($db, "TIMESHEET_APPROVAL_FLOWS", implode('',$apflows), 'chaine', 0, '', $conf->entity)  ;             
        $invoicemethod=$_POST['invoiceMethod'];
        dolibarr_set_const($db, "TIMESHEET_INVOICE_METHOD", $invoicemethod, 'chaine', 0, '', $conf->entity);        
        $invoicetasktime=null2int($_POST['invoiceTaskTime']);
        dolibarr_set_const($db, "TIMESHEET_INVOICE_TASKTIME", $invoicetasktime, 'chaine', 0, '', $conf->entity);        
        $invoiceservice=null2int($_POST['invoiceService']);
        dolibarr_set_const($db, "TIMESHEET_INVOICE_SERVICE", $invoiceservice, 'chaine', 0, '', $conf->entity);       
        $invoiceshowtask=null2int($_POST['invoiceShowTask']);
        dolibarr_set_const($db, "TIMESHEET_INVOICE_SHOW_TASK", $invoiceshowtask, 'chaine', 0, '', $conf->entity);       
        $invoiceshowuser=null2int($_POST['invoiceShowUser']);
        dolibarr_set_const($db, "TIMESHEET_INVOICE_SHOW_USER", $invoiceshowuser, 'chaine', 0, '', $conf->entity);
        $showTimespentNote=null2int($_POST['showTimespentNote']);
        dolibarr_set_const($db, "TIMESHEET_SHOW_TIMESPENT_NOTE", $showTimespentNote, 'chaine', 0, '', $conf->entity);
        $noteOnPDF=null2int($_POST['noteOnPDF']);
        dolibarr_set_const($db, "TIMESHEET_PDF_NOTEISOTASK", $noteOnPDF, 'chaine', 0, '', $conf->entity);
        // serach box
        $searchbox=null2int($_POST['searchBox']);
        dolibarr_set_const($db, "TIMESHEET_SEARCHBOX", $searchbox, 'chaine', 0, '', $conf->entity);       


        break;
    default:
        break;
}
$headersT=explode('||',$headers);
foreach ($headersT as $header) {
    switch($header){
        case 'Project':
            $showProject=1;
            Break;
        case 'TaskParent':
            $showTaskParent=1;
            Break;
        case 'Tasks':
            $showTasks=1;
            Break;
        case 'DateStart':
            $showDateStart=1;
            Break;
        case 'DateEnd':
            $showDateEnd=1;
            Break;
        case 'Progress':
            $showProgress=1;
            Break;
        case 'Company':
            $showCompany=1;
            Break;
        case 'Note':
            $showNote=1;
            Break;
        case 'Total':
            $showTotal=1;
            Break;
        default:
            break;
    }
    
}

/* 
 *  VIEW
 *  */


//permet d'afficher la structure dolibarr
$morejs=array("/timesheet/core/js/timesheet.js?v2.0","/timesheet/core/js/jscolor.js");
llxHeader("",$langs->trans("timesheetSetup"),'','','','',$morejs,'',0,0);


$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("timesheetSetup"),$linkback,'title_setup');
echo '<div class="fiche"><br><br>';
/*
 * TABS
 */

echo '<div class="tabs" data-role="controlgroup" data-type="horizontal"  >';
echo '  <div id="defaultOpen"  class="inline-block tabsElem" onclick="openTab(event, \'General\')"><a  href="javascript:void(0);"  class="tabunactive tab inline-block" data-role="button">'.$langs->trans('General').'</a></div>';
echo '  <div class="inline-block tabsElem" onclick="openTab(event, \'Advanced\')"><a  href="javascript:void(0);" class="tabunactive tab inline-block" data-role="button">'.$langs->trans('Advanced').'</a></div>';
echo '  <div class="inline-block tabsElem"  onclick="openTab(event, \'Invoice\')"><a href="javascript:void(0);" class="tabunactive tab inline-block" data-role="button">'.$langs->trans('Invoice').'</a></div>';
echo '  <div class="inline-block tabsElem"   onclick="openTab(event, \'Other\')"><a href="javascript:void(0);" class="tabunactive tab inline-block" data-role="button">'.$langs->trans('Other').'</a></div>';

echo '</div>';


/*
 * General
 */
echo '<div id="General" class="tabBar">';
print '<a>'.$langs->trans("GeneralTabDesc").'</a>';
print_titre($langs->trans("GeneralOption"));
echo '<form name="settings" action="?action=save" method="POST" >'."\n\t";
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
// type time
echo '<tr class="oddeven"><td align="left">'.$langs->trans("timeType").'</td><td align="left">'.$langs->trans("timeTypeDesc").'</td>';
echo '<td align="left"><input type="radio" name="timeType" value="hours" ';
echo ($timetype=="hours"?"checked":"").'> '.$langs->trans("Hours").'<br>';
echo '<input type="radio" name="timeType" value="days" ';
echo ($timetype=="days"?"checked":"").'> '.$langs->trans("Days")."</td></tr>\n\t\t";
//hours perdays
echo '<tr class="oddeven"><td align="left">'.$langs->trans("hoursperdays");
echo '</td><td align="left">'.$langs->trans("hoursPerDaysDesc").'</td>';
echo '<td align="left"><input type="text" name="hoursperday" value="'.$hoursperday;
echo "\" size=\"4\" ></td></tr>\n\t\t";
//max hours perdays
echo '<tr class="oddeven"><td align="left">'.$langs->trans("maxhoursperdays"); //FIXTRAD
echo '</td><td align="left">'.$langs->trans("maxhoursPerDaysDesc").'</td>'; // FIXTRAD
echo '<td align="left"><input type="text" name="maxhoursperday" value="'.$maxhoursperday;
echo "\" size=\"4\" ></td></tr>\n\t\t";
// time span
echo '<tr class="oddeven"><td align="left">'.$langs->trans("timeSpan").'</td><td align="left">'.$langs->trans("timeSpanDesc").'</td>';
echo '<td align="left"><input type="radio" name="timeSpan" value="week" ';
echo ($timeSpan=="week"?"checked":"").'> '.$langs->trans("Week").'<br>';
echo '<input type="radio" name="timeSpan" value="splitedWeek" ';
echo ($timeSpan=="splitedWeek"?"checked":"").'> '.$langs->trans("splitedWeek").'<br>';
echo '<input type="radio" name="timeSpan" value="month" ';
echo ($timeSpan=="month"?"checked":"").'> '.$langs->trans("Month")."</td></tr>\n\t\t";
// hide draft
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("hidedraft");
echo '</td><td align="left">'.$langs->trans("hideDraftDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="hidedraft" value="1" ';
echo (($hidedraft=='1')?'checked':'')."></td></tr>\n\t\t";
// hide ref
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("hideref");
echo '</td><td align="left">'.$langs->trans("hideRefDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="hideref" value="1" ';
echo (($hideref=='1')?'checked':'')."></td></tr>\n\t\t";

// hide zeros
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("hidezeros");
echo '</td><td align="left">'.$langs->trans("hideZerosDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="hidezeros" value="1" ';
echo (($hidezeros=='1')?'checked':'')."></td></tr>\n";

// show timespentNote
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("ShowTimespentNote");
echo '</td><td align="left">'.$langs->trans("ShowTimespentNoteDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showTimespentNote" value="1" ';
echo (($showTimespentNote=='1')?'checked':'')."></td></tr>\n";

// add holiday time
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("addholidaytime");
echo '</td><td align="left">'.$langs->trans("addholidaytimeDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="addholidaytime" value="1" ';
echo (($addholidaytime=='1')?'checked':'')."></td></tr>\n";

// add docs
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("adddocs");
echo '</td><td align="left">'.$langs->trans("adddocsDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="adddocs" value="1" ';
echo (($adddocs=='1')?'checked':'')."></td></tr>\n";

//Add for other
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("addForOther");
echo '</td><td align="left">'.$langs->trans("addForOtherDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="addForOther" value="1" ';
echo (($addForOther=='1')?'checked':'')."></td></tr>\n\t\t";



echo "\t</table><br>\n";





print_titre($langs->trans("OpenDays")); 
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th>'.$langs->trans("Monday").'</th><th>';
echo $langs->trans("Tuesday").'</th><th>'.$langs->trans("Wednesday").'</th><th>';
echo $langs->trans("Thursday").'</th><th>'.$langs->trans("Friday").'</th><th>';
echo $langs->trans("Saturday").'</th><th>'.$langs->trans("Sunday").'</th>';
echo '<input type="hidden" name="opendays[0]" value="_">';
echo "</tr><tr>\n\t\t";

for ($i=1; $i<8;$i++){
echo  '<td width="14%" style="text-align:left"><input type="checkbox" name="opendays['.$i.']" value="1" ';
echo (($opendays[$i]=='1')?'checked':'')."></td>\n\t\t";
        }
echo "</tr>\n\t</table><br>\n";

print_titre($langs->trans("ColumnToShow"));
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
// Project
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("Project");
echo '</td><td align="left">'.$langs->trans("ProjectColDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showProject" value="1" ';
echo (($showProject=='1')?'checked':'')."></td></tr>\n\t\t";
// task parent
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("TaskParent");
echo '</td><td align="left">'.$langs->trans("TaskParentColDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showTaskParent" value="1" ';
echo (($showTaskParent=='1')?'checked':'')."></td></tr>\n\t\t";
// task
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("Tasks");
echo '</td><td align="left">'.$langs->trans("TasksColDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showTasks" value="1" ';
echo (($showTasks=='1')?'checked':'')."></td></tr>\n\t\t";
// date de debut
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("DateStart");
echo '</td><td align="left">'.$langs->trans("DateStartColDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showDateStart" value="1" ';
echo (($showDateStart=='1')?'checked':'')."></td></tr>\n\t\t";
// date de fin
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("DateEnd");
echo '</td><td align="left">'.$langs->trans("DateEndColDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showDateEnd" value="1" ';
echo (($showDateEnd=='1')?'checked':'')."></td></tr>\n\t\t";
// Progres
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("Progress");
echo '</td><td align="left">'.$langs->trans("ProgressColDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showProgress" value="1" ';
echo (($showProgress=='1')?'checked':'')."></td></tr>\n\t\t";
// Company
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("Company");
echo '</td><td align="left">'.$langs->trans("CompanyColDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showCompany" value="1" ';
echo (($showCompany=='1')?'checked':'')."></td></tr>\n\t\t";
//note
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("Note");
echo '</td><td align="left">'.$langs->trans("NoteDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showNote" value="1" ';
echo (($showNote=='1')?'checked':'')."></td></tr>\n\t\t";
//Total
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("Total");
echo '</td><td align="left">'.$langs->trans("TotalDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="showTotal" value="1" ';
echo (($showTotal=='1')?'checked':'')."></td></tr>\n\t\t";


/*
// custom FIXME
echo  '<tr class="oddeven"><th align="left">'.$langs->trans("CustomCol");
echo '</th><th align="left">'.$langs->trans("CustomColDesc").'</th>';
echo  '<th align="left"><input type="checkbox" name="showCustomCol" value="1" ';
echo (($showCustomCol=='1')?'checked':'')."</th></tr>\n\t\t";
*/
echo '</table>';
echo "</div>\n";

/*
 * ADVANCED
 */



echo '<div id="Advanced" class="tabBar">';
print '<a>'.$langs->trans("AdvancedTabDesc").'</a>';
print_titre($langs->trans("Approval")); 

echo '<table class="noborder" width="100%">'."\n\t\t";
// approval by week
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("approvalbyweek");
echo '</td><td align="left">'.$langs->trans("approvalbyweekDesc").'</td>';
echo '<td align="left"><input type="radio" name="approvalbyweek" value="0" ';
echo ($approvalbyweek=='0'?"checked":"").'> '.$langs->trans("User").'<br>';
echo '<input type="radio" name="approvalbyweek" value="1" ';
echo ($approvalbyweek=='1'?"checked":"").'> '.$langs->trans("Week").'<br>';
echo '<input type="radio" name="approvalbyweek" value="2" ';
echo ($approvalbyweek=='2'?"checked":"").'> '.$langs->trans("Month")."</td></tr>\n\t\t";
// max approval 
echo '<tr class="oddeven" ><td align="left">'.$langs->trans("maxapproval"); //FIXTRAD
echo '</td><td align="left">'.$langs->trans("maxapprovalDesc").'</td>'; // FIXTRAD
echo '<td  align="left"><input type="text" name="maxapproval" value="'.$maxApproval;
echo "\" size=\"4\" ></td></tr>\n\t\t";
echo '</table>'."\n\t\t";
// approval flows
print_titre($langs->trans("ApplovalFlow"));
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value");
echo '<input type="hidden" name="apflows[0]" value="_"></th>';
echo "</tr>\n\t\t";
//team
echo '<tr><td>'.$langs->trans("Team").'</td><td>'.$langs->trans("TeamApprovalDesc").'</td><td><input type="checkbox" name="apflows[1]" value="1"';
echo (($apflows[1]=='1')?'checked':'').'></td><tr>';
//Project
echo '<tr><td>'.$langs->trans("Project").'</td><td>'.$langs->trans("ProjectApprovalDesc").'</td><td><input type="checkbox" name="apflows[2]" value="1"';
echo (($apflows[2]=='1')?'checked':'').'></td><tr>';
/*//Customer
echo '<tr style="display:none"><td>'.$langs->trans("Customer").'</td><td>'.$langs->trans("CustomerApprovalDesc").'</td><td><input type="checkbox" name="apflows[3]" value="1"';
echo (($apflows[3]=='1')?'checked':'').'></td><tr>';
//Supplier
echo '<tr style="display:none"><td>'.$langs->trans("Supplier").'</td><td>'.$langs->trans("SupplierApprovalDesc").'</td><td><input type="checkbox" name="apflows[4]" value="1"';
echo (($apflows[4]=='1')?'checked':'').'></td><tr>';
//Other
echo '<tr style="display:none"><td>'.$langs->trans("Other").'</td><td>'.$langs->trans("OtherApprovalDesc").'</td><td><input type="checkbox" name="apflows[5]" value="1"';
echo (($apflows[5]=='1')?'checked':'').'></td><tr>';

*/


echo "</tr>\n\t</table><br>\n";

//Color
print_titre($langs->trans("Color"));
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
// color draft
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("draft");
echo '</td><td align="left">'.$langs->trans("draftColorDesc").'</td>';
echo  '<td align="left"><input name="draftColor" class="jscolor" value="';
echo $draftColor."\"></td></tr>\n\t\t";
// color submitted
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("submitted");
echo '</td><td align="left">'.$langs->trans("submittedColorDesc").'</td>';
echo  '<td align="left"><input name="submittedColor" class="jscolor" value="';
echo $submittedColor."\"></td></tr>\n\t\t";
// color approved
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("approved");
echo '</td><td align="left">'.$langs->trans("approvedColorDesc").'</td>';
echo  '<td align="left"><input name="approvedColor" class="jscolor" value="';
echo $approvedColor."\"></td></tr>\n\t\t";
// color cancelled
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("cancelled");
echo '</td><td align="left">'.$langs->trans("cancelledColorDesc").'</td>';
echo  '<td align="left"><input name="cancelledColor" class="jscolor" value="';
echo $cancelledColor."\"></td></tr>\n\t\t";
// color rejected
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("rejected");
echo '</td><td align="left">'.$langs->trans("rejectedColorDesc").'</td>';
echo  '<td align="left"><input name="rejectedColor" class="jscolor" value="';
echo $rejectedColor."\"></td></tr>\n\t\t";


echo '</table><br>';




//whitelist mode
print_titre($langs->trans("blackWhiteList"));
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
// whitelist on/off
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("blackWhiteList");
echo '</td><td align="left">'.$langs->trans("blackWhiteListDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="blackWhiteList" value="1" ';
echo (($whiteList=='1')?'checked':'')."></td></tr>\n\t\t";
// Project
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("blackWhiteListMode").'</td>';
echo '<td align="left">'.$langs->trans("blackWhiteListModeDesc").'</td>';
echo '<td align="left"><input type="radio" name="blackWhiteListMode" value="0" ';
echo ($whiteListMode=="0"?"checked":"").'> '.$langs->trans("modeWhiteList").'<br>';
echo '<input type="radio" name="blackWhiteListMode" value="1" ';
echo ($whiteListMode=="1"?"checked":"").'> '.$langs->trans("modeBlackList")."<br>";
echo '<input type="radio" name="blackWhiteListMode" value="2" ';
echo ($whiteListMode=="2"?"checked":"").'> '.$langs->trans("modeNone")."</td></tr>\n\t\t";
echo '</table><br>';


echo '<br>';

echo '</div>';

/*
 * INVOICE
 */
echo '<div id="Invoice" class="tabBar">';
print '<a>'.$langs->trans("InvoiceTabDesc").'</a>';
print_titre($langs->trans("Invoice"));
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
//lines invoice method
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("invoiceMethod");
echo '</td><td align="left">'.$langs->trans("invoiceMethodDesc").'</td>';
echo  '<td align="left"><input type="radio" name="invoiceMethod" value="task" ';
echo (($invoicemethod=='task')?'checked':'').">".$langs->trans("Task").'<br>';
echo '<input type="radio" name="invoiceMethod" value="user" ';
echo (($invoicemethod=='user')?'checked':'').">".$langs->trans("User").'<br>';
echo '<input type="radio" name="invoiceMethod" value="taskUser" ';
echo (($invoicemethod=='taskUser')?'checked':'').">".$langs->trans("Tasks").' & '.$langs->trans("User").'<br>';
echo "</td></tr>\n\t\t";
//line invoice Service
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("invoiceService");
echo '</td><td align="left">'.$langs->trans("invoiceServiceDesc").'</td>';
echo  '<td align="left">';
$addchoices=array('-999'=> $langs->transnoentitiesnoconv('not2invoice'),-1=> $langs->transnoentitiesnoconv('Custom'));
$ajaxNbChar=$conf->global->PRODUIT_USE_SEARCH_TO_SELECT;
$htmlProductArray=array('name'=>'invoiceService','ajaxNbChar'=>$ajaxNbChar);
$sqlProductArray=array('table'=>'product','keyfield'=>'rowid','fields'=>'ref,label','where'=>'tosell=1 AND fk_product_type=1','separator' => ' - ');
print select_sellist($sqlProductArray,$htmlProductArray,$invoiceservice,$addchoices);
echo "</td></tr>\n\t\t";
//line tasktime ==
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("invoiceTaskTime");
echo '</td><td align="left">'.$langs->trans("invoiceTaskTimeDesc").'</td>';
echo  '<td align="left"><input type="radio" name="invoiceTaskTime" value="all" ';
echo (($invoicetasktime=='all')?'checked':'').">".$langs->trans("All").'<br>';
echo '<input type="radio" name="invoiceTaskTime" value="appoved" ';
echo (($invoicetasktime=='Approved')?'checked':'').">".$langs->trans("Approved").'<br>';
echo "</td></tr>\n\t\t";
//line show user
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("invoiceShowUser");
echo '</td><td align="left">'.$langs->trans("invoiceShowUserDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="invoiceShowUser" value="1" ';
echo (($invoiceshowuser=='1')?'checked':'')."></td></tr>\n\t\t";
//line show task
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("invoiceShowTask");
echo '</td><td align="left">'.$langs->trans("invoiceShowTaskDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="invoiceShowTask" value="1" ';
echo (($invoiceshowuser=='1')?'checked':'')."></td></tr>\n\t\t";

// Show note on PDF
echo '<tr class="oddeven"><td align="left">'.$langs->trans("NoteOnPDF").'</td><td align="left">'.$langs->trans("NoteOnPDFDesc").'</td>';
echo '<td align="left"><input type="radio" name="noteOnPDF" value="0" ';
echo ($noteOnPDF=="0"?"checked":"").'> '.$langs->trans("Task").'<br> <input type="radio" name="noteOnPDF" value="1" ';
echo ($noteOnPDF=="1"?"checked":"").'> '.$langs->trans("Note").'<br>  <input type="radio" name="noteOnPDF" value="2"';
echo ($noteOnPDF=="2"?"checked":"").'> '.$langs->trans("Task")."&".$langs->trans("Note")."</td></tr>\n\t\t";


echo '</table><br>';

echo '</div>';//taskbar
/*
 * Other
 */
echo '<div id="Other" class="tabBar">';
print '<a>'.$langs->trans("OtherTabDesc").'</a>';
print_titre($langs->trans("Dolibarr"));
echo '<table class="noborder" width="100%">'."\n\t\t";
echo '<tr class="liste_titre" width="100%" ><th width="200px">'.$langs->trans("Name").'</th><th>';
echo $langs->trans("Description").'</th><th width="100px">'.$langs->trans("Value")."</th></tr>\n\t\t";
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("dropdownAjax");
echo '</td><td align="left">'.$langs->trans("dropdownAjaxDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="dropdownAjax" value="1" ';
echo (($dropdownAjax=='1')?'checked':'')."></td></tr>\n\t\t";
// searchbox
echo  '<tr class="oddeven"><td align="left">'.$langs->trans("searchbox");
echo '</td><td align="left">'.$langs->trans("searchboxDesc").'</td>';
echo  '<td align="left"><input type="checkbox" name="searchBox" value="1" ';
echo (($searchbox=='1')?'checked':'')."></td></tr>\n";

echo '</table><br>';
// doc
print_titre($langs->trans("Manual"));
echo '<a href="../doc/Module_timesheet.pdf">  PDF </a></br>'."\n\t\t";
echo '<a href="../doc/Module_timesheet.docx">  DOCX </a></br></br>'."\n\t\t";

print_titre($langs->trans("Feedback"));
echo $langs->trans('feebackDesc').' : <a href="mailto:patrick@pmpd.eu?subject=TimesheetFeedback"> Patrick Delcroix</a></br></br>';

print_titre($langs->trans("Reminder"));
print '<br><div>'.$langs->trans('reminderEmailProcess').'</div>';
echo '</div>';
echo '</div>'; // end fiche
echo '<input type="submit" class="butAction" value="'.$langs->trans('Save')."\">\n</from>";
echo '<br><br><br>';
echo '<script>document.getElementById("defaultOpen").click()</script>';

llxFooter();
$db->close();
