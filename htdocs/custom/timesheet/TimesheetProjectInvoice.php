<?php
 /* Copyright (C) 2017 delcroip <patrick@pmpd.eu>
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
/*
define('$conf->global->TIMESHEET_INVOICE_METHOD','user');
define('$conf->global->TIMESHEET_INVOICE_TASKTIME','user');
define('$conf->global->TIMESHEET_INVOICE_SERVICE','1');
define('$conf->global->TIMESHEET_INVOICE_SHOW_TASK','1');
define('$conf->global->TIMESHEET_INVOICE_SHOW_USER','1');
*/
//load class
include 'core/lib/includeMain.lib.php';
include 'core/lib/generic.lib.php';
include 'core/lib/timesheet.lib.php';
//require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

//get param
$staticProject=new Project($db);
$projectId=GETPOST('projectid','int');

$socid=GETPOST('socid','int');
//$month=GETPOST('month','alpha');
//$year=GETPOST('year','int');
$mode=GETPOST('invoicingMethod','alpha');
$step=GETPOST('step','alpha');
$ts2Invoice=GETPOST('ts2Invoice','alpha');
$tsNotInvoiced=GETPOST('tsNotInvoiced','alpha');
$userid=  is_object($user)?$user->id:$user;
//init handling object
$form = new Form($db);

$dateStart                 = strtotime(GETPOST('dateStart', 'alpha'));
$dateStartday =GETPOST('dateStartday', 'int'); // to not look for the date if action not goTodate
$dateStartmonth                 = GETPOST('dateStartmonth', 'int');
$dateStartyear                 = GETPOST('dateStartyear', 'int');
$dateStart=parseDate($dateStartday,$dateStartmonth,$dateStartyear,$dateStart);

$dateEnd                 = strtotime(GETPOST('dateEnd', 'alpha'));
$dateEndday =GETPOST('dateEndday', 'int'); // to not look for the date if action not goTodate
$dateEndmonth                 = GETPOST('dateEndmonth', 'int');
$dateEndyear                 = GETPOST('dateEndyear', 'int');
$dateEnd=parseDate($dateEndday,$dateEndmonth,$dateEndyear,$dateEnd);



if ($user->rights->facture->creer & hasProjectRight($userid,$projectId))
{
    if($projectId>0)$staticProject->fetch($projectId);
    if($socid==0 || !is_numeric($socid))$socid=$staticProject->socid; //FIXME check must be in place to ensure the user hqs the right to see the project details
$edit=1;
// avoid SQL issue
if(empty($dateStart) || empty($dateEnd) ||$dateStart==$dateEnd ){
    $step=0;
//    $dateStart=  strtotime("first day of previous month",time());
    $dateStart=  strtotime("first day of January this year",time());  // PJR change
    $dateEnd=  strtotime("last day of previous month",time());
 }
 $langs->load("main");
$langs->load("projects");
$langs->load('timesheet@timesheet');
//steps
    switch ($step)
    { 
        case 2:{
           $fields=($mode=='user')?'fk_user':(($mode=='taskUser')?'fk_task,fk_user':'fk_task'); 
            $sql= 'SELECT  '.$fields.', task_date, note, SUM(tt.task_duration) as duration, ';  // PJR change
            if($db->type!='pgsql'){
                $sql.=" GROUP_CONCAT(tt.rowid  SEPARATOR ',') as task_time_list";
                $sql.=", GROUP_CONCAT(tt.note  SEPARATOR '\n') as task_note"; // PJR addition
            }else{
                $sql.=" STRING_AGG(to_char(tt.rowid,'9999999999999999'), ',') as task_time_list";
            }
             $sql.=' From '.MAIN_DB_PREFIX.'projet_task_time as tt';
            $sql.=' JOIN '.MAIN_DB_PREFIX.'projet_task as t ON tt.fk_task=t.rowid';
            $sql.=' WHERE t.fk_projet='.$projectId;

                $sql.=" AND tt.task_date BETWEEN '".$db->idate($dateStart);
                $sql.="' AND '".$db->idate($dateEnd)."'";

            if($ts2Invoice!='all'){
                /*$sql.=' AND tt.rowid IN(SELECT GROUP_CONCAT(fk_project_s SEPARATOR ", ")';
                $sql.=' FROM '.MAIN_DB_PREFIX.'project_task_time_approval';  
                $sql.=' WHERE status= "APPROVED" AND MONTH(date_start)='.$month;  
                $sql.=' AND YEAR(date_start)="'.$year.'")'; 
                $sql.=' AND YEAR(date_start)="'.$year.'")'; */
                $sql.=' AND tt.status = '.APPROVED; 
            }
            if($tsNotInvoiced==1){
                $sql.=' AND tt.invoice_id IS NULL'; 
            }
            $sql.=' GROUP BY task_date,'.$fields;  // PJR change
            $sql.=' ORDER BY fk_task,task_date,fk_user';  // PJR addition
            dol_syslog('timesheet::timesheetProjectInvoice step2', LOG_DEBUG);    

            
            $Form ='<form name="settings" action="?step=3" method="POST" >'."\n\t"; 
            $Form .='<input type="hidden" name="projectid" value ="'.$projectId.'">';
            $Form .='<input type="hidden" name="dateStart" value ="'.dol_print_date($dateStart,'dayxcard').'">';
            $Form .='<input type="hidden" name="dateEnd" value ="'.dol_print_date($dateEnd,'dayxcard').'">';
            $Form .='<input type="hidden" name="socid" value ="'.$socid.'">';
            $Form .='<input type="hidden" name="invoicingMethod" value ="'.$mode.'">';
            $Form .='<input type="hidden" name="ts2Invoice" value ="'.$ts2Invoice.'">';
            
            $resql=$db->query($sql);
            $num=0;
            $resArray=array();
            if ($resql)
            {
                    $num = $db->num_rows($resql);
                    $i = 0;
                   
                    // Loop on each record found,
                    while ($i < $num)
                    {                           
                        $error=0;
                        $obj = $db->fetch_object($resql);
                        $duration=floor($obj->duration/3600).":".str_pad (floor($obj->duration%3600/60),2,"0",STR_PAD_LEFT);
                        switch($mode){
                            case 'user':
                                 //step 2.2 get the list of user  (all or approved)
                                $resArray[]=array("USER" => $obj->fk_user,"TASK" =>'any',"DURATION"=>$duration,'LIST'=>$obj->task_time_list);
                                break;
                            case 'taskUser':
                                 //step 2.3 get the list of taskUser  (all or approved)
                                $resArray[]=array("USER" => $obj->fk_user,"TASK" =>$obj->fk_task,"DURATION"=>$duration,'LIST'=>$obj->task_time_list, 'NOTE'=>$obj->task_note, 'DATE'=>$obj->task_date);  // modified by PJR
                                break;
                            default:
                            case 'task':                   
                                 //step 2.1 get the list of task  (all or approved)
                                $resArray[]=array("USER" => "any","TASK" =>$obj->fk_task,"DURATION"=>$duration,'LIST'=>$obj->task_time_list);
                              break;
                         }
                           
                        $i++;                           
                    }
                    $db->free($resql);
            }else
            {
                    dol_print_error($db);
                    return '';
            }

             //FIXME asign a service + price to each array elements (or price +auto generate name 
            $Form .='<table class="noborder" width="100%">'."\n\t\t";
            $Form .='<tr class="liste_titre" width="100%" ><th colspan="6">'.$langs->trans('invoicedServiceSelectoin').'</th></tr>';  // corrected by PJR (unhacked needed to be 9; 8 after PJR hack)
            $Form .='<tr class="liste_titre" width="100%" ><th >'.$langs->trans("User").'<br/>'.$langs->trans("Date").'</th>';
            $Form .='<th colspan="4">'.$langs->trans("Note").'</th><th >'.$langs->trans("savedDuration").'</th></tr>';
			$Form .='<tr class="liste_titre" width="100%" ><th ></th><th >'.$langs->trans("Service").': '.$langs->trans("Existing")." / ".$langs->trans("Custom").'</th>';  // modified by PJR
            $Form .='<th >'.$langs->trans("Custom").': '.$langs->trans("Description").'</th><th >'.$langs->trans("Custom").': '.$langs->trans("UnitPriceHT").'</th>';
//            $Form .='<th >'.$langs->trans("Custom").':'.$langs->trans("VAT").'</th><th >'.$langs->trans("unitDuration").'</th></tr>';
            $Form .='<th >'.$langs->trans("Custom").': '.$langs->trans("VAT").'</th><th ></th></tr>';
            $form = new Form($db);
			$taskheader = '';
            foreach($resArray as $res){
				if($taskheader!= $res["TASK"]){
		            $Form .='<tr class="oddeven trforbreak"><td colspan="6"><b>'.print_generic('projet_task','rowid',$res["TASK"],'ref','label',' ').'</b></td></tr>';
				}
                $Form .=htmlPrintServiceChoice($res["USER"],$res["TASK"],'oddeven',$res["DURATION"],$res['LIST'],$mysoc,$socid,$res['NOTE'],$res['DATE']);
				$taskheader = $res["TASK"];
            }
            
            $Form .='</table>';
            $Form .='<input type="submit"  class="butAction" value="'.$langs->trans('Next')."\">\n</form>";

                        
                        
             break;}
        case 3: // review choice and list of item + quantity ( editable)
            require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
            require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
            $object = new Facture($db);

		$db->begin();
		$error = 0;
               
                $dateinvoice = time();
			//$date_pointoftax = dol_mktime(12, 0, 0, $_POST['date_pointoftaxmonth'], $_POST['date_pointoftaxday'], $_POST['date_pointoftaxyear']);
				// Si facture standard
                $object->socid				= $socid;
                $object->type				= 0;//Facture::TYPE_STANDARD;
                $object->date				= $dateinvoice;
                $object->fk_project			= $projectId;
                $object->fetch_thirdparty();
                $id = $object->create($user);
                $resArray=$_POST['userTask'];
                $task_time_array=array();
                if(is_array($resArray)){
                    foreach($resArray as $uId =>$userTaskService){
                        //$userTaskService[$user][$task]=array('duration', 'VAT','Desc','PriceHT','Service','unit_duration','unit_duration_unit');
                        if(is_array($userTaskService ))foreach($userTaskService as  $tId => $service){
                            $durationTab=explode (':',$service['duration']);
                            $duration=$durationTab[1]*60+$durationTab[0]*3600;   
                            //$startday = dol_mktime(12, 0, 0, $month, 1, $year);
                            //$endday = dol_mktime(12, 0, 0, $month, date('t',$startday), $year);

                            $details='';
                            $result ='';
                            if(($tId!='any') && $conf->global->TIMESHEET_INVOICE_SHOW_TASK)$details="\n".$service['taskLabel'];
                            if(($uId!='any')&& $conf->global->TIMESHEET_INVOICE_SHOW_USER)$details.="\n".$service['userName'];

                            if($service['Service']>0){
                                 $product = new Product($db);
                                 $product->fetch($service['Service']);

                                 $unit_duration_unit=substr($product->duration, -1);
                                 $unit_factor=($unit_duration_unit=='h')?3600:8*3600;//FIXME support week and month 
                                 
                                 $factor=intval(substr($product->duration,0, -1));
                                 if($factor==0)$factor=1;//to avoid divided by $factor0
                                 $quantity= $duration/($factor*$unit_factor);
                                 $result = $object->addline($product->description.$details, $product->price, $quantity, $product->tva_tx, $product->localtax1_tx, $product->localtax2_tx, $service['Service'], 0, $startday, $endday, 0, 0, '', $product->price_base_type, $product->price_ttc, $product->type, -1, 0, '', 0, 0, null, 0, '', 0, 100, '', $product->fk_unit);


                            }elseif ($service['Service']<>-999){
                                 $factor=($service['unit_duration_unit']=='h')?3600:8*3600;//FIXME support week and month 
                                 $factor=$factor*intval($service['unit_duration']);

                                 $quantity= $duration/$factor;
                                 $result = $object->addline($service['Desc'].$details, $service['PriceHT'], $quantity, $service['VAT'], '', '', '', 0, $dateStart, $dateEnd, 0, 0, '', 'HT', '', 1, -1, 0, '', 0, 0, null, 0, '', 0, 100, '', '');

                             }
                             if($service['taskTimeList']<>'' &&  $result>0)$task_time_array[$result]=$service['taskTimeList'];
                        }else $error++;
                    }
                }else $error++;
                
     		// End of object creation, we show it
		if ($id > 0 && ! $error)
		{
			$db->commit();
                        if(version_compare(DOL_VERSION,"4.9.9")>=0){
                            foreach($task_time_array AS $idLine=> $task_time_list){
                                    //dol_syslog("ProjectInvoice::setnvoice".$idLine.' '.$task_time_list, LOG_DEBUG);
                                Update_task_time_invoice($id,$idLine,$task_time_list);
                            }
                        }
			header('Location: ' . $object->getNomUrl(0,'',0,1,''));
			exit();
		}
		else
		{
			$db->rollback();
			//header('Location: ' . $_SERVER["PHP_SELF"] . '?step=0');
			setEventMessages($object->error, $object->errors, 'errors');
                     
		}
            
            break;
               
        case 1:
        
$edit=0;
    case 0:
    default:
        require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
        $htmlother = new FormOther($db);
        $sqlTail='';
        
        if(!$user->admin){    
            $sqlTailJoin=' JOIN '.MAIN_DB_PREFIX.'element_contact  as ec ON t.rowid=ec.element_id';
            $sqlTailJoin.=' LEFT JOIN '.MAIN_DB_PREFIX.'c_type_contact as ctc ON ctc.rowid=fk_c_type_contact';
            $sqlTailWhere=" ctc.element='project' AND ctc.active='1' "; // WARINING: any project role can do the invoice if they have the right to create invoices
            $sqlTailWhere.=' AND fk_socpeople=\''.$userid.'\' AND fk_statut = 1';
        }
            $Form ='<form name="settings" action="?step=2" method="POST" >'."\n\t";
            $Form .='<table class="noborder" width="100%">'."\n\t\t";
            $Form .='<tr class="liste_titre" width="100%" ><th colspan="2">'.$langs->trans('generalInvoiceProjectParam').'</th></tr>';    
            $invoicingMethod=$conf->global->TIMESHEET_INVOICE_METHOD;
            $Form .='<tr class="oddeven"><th align="left" width="80%">'.$langs->trans('Project').'</th><th align="left" width="80%" >';
            //select_generic($table, $fieldValue,$htmlName,$fieldToShow1,$fieldToShow2='',$selected='',$separator=' - ',$sqlTailWhere='', $selectparam='', $addtionnalChoices=array('NULL'=>'NULL'),$sqlTailTable='', $ajaxUrl='')
            $ajaxNbChar=$conf->global->PROJECT_USE_SEARCH_TO_SELECT;
            $sqlTailJoin.=' ORDER BY t.ref'; // added by PJR
            //$Form .=select_generic('projet', 'rowid','projectid','ref','title',$projectId,' - ',$sqlTailWhere,'',NULL,,$ajaxNbChar);
            $htmlProjectArray=array('name'=>'projectid','ajaxNbChar'=>$ajaxNbChar,'otherparam'=>' onchange="reload(this.form)"');
            $sqlProjectArray=array('table'=>'projet','keyfield'=>'rowid','fields'=>'ref,title','join'=>$sqlTailJoin,'where'=>$sqlTailWhere,'separator' => ' - ');
            $Form .= select_sellist($sqlProjectArray,$htmlProjectArray,$projectId);
            $Form .='<tr class="oddeven"><th align="left" width="80%">'.$langs->trans('DateStart').'</th>';
            $Form.=   '<th align="left" width="80%">'.$form->select_date($dateStart,'dateStart',0,0,0,"",1,1,1)."</th></tr>";
            $Form .='<tr class="oddeven"><th align="left" width="80%">'.$langs->trans('DateEnd').'</th>';
            $Form.=   '<th align="left" width="80%">'.$form->select_date($dateEnd,'dateEnd',0,0,0,"",1,1,1)."</th></tr>";
            $Form .='<tr class="oddeven"><th align="left" width="80%">'.$langs->trans('invoicingMethod').'</th><th align="left"><input type="radio" name="invoicingMethod" value="task" ';
            $Form .=($invoicingMethod=="task"?"checked":"").'> '.$langs->trans("Tasks").'</br> ';
            $Form .='<input type="radio" name="invoicingMethod" value="user" ';
            $Form .=($invoicingMethod=="user"?"checked":"").'> '.$langs->trans("User")."</br> ";
            $Form .='<input type="radio" name="invoicingMethod" value="taskUser" ';
            $Form .=($invoicingMethod=="taskUser"?"checked":"").'> '.$langs->trans("Tasks").' & '.$langs->trans("User")."</th></tr>\n\t\t";
//cust list
            $Form .='<tr class="oddeven"><th  align="left">'.$langs->trans('Customer').'</th><th  align="left">'.$form->select_company($socid, 'socid', '(s.client=1 OR s.client=2 OR s.client=3)', 1).'</th></tr>';
//all ts or only approved
           $ts2Invoice=$conf->global->TIMESHEET_INVOICE_TASKTIME;
            $Form .='<tr class="oddeven"><th align="left" width="80%">'.$langs->trans('TimesheetToInvoice').'</th><th align="left"><input type="radio" name="ts2Invoice" value="approved" ';
            $Form .=($ts2Invoice=="approved"?"checked":"").'> '.$langs->trans("approvedOnly").' </br>';
            $Form .='<input type="radio" name="ts2Invoice" value="all" ';
            $Form .=($ts2Invoice=="all"?"checked":"").'> '.$langs->trans("All")."</th></tr>";
// not alreqdy invoice
            if(version_compare(DOL_VERSION,"4.9.9")>=0){
                $Form .='<tr class="oddeven"><th align="left" width="80%">'.$langs->trans('TimesheetNotInvoiced');
                $Form .='</th><th align="left"><input type="checkbox" name="tsNotInvoiced" value="1" ></th></tr>';
                
            }else{
                $Form .='<input type="hidden" name="tsNotInvoiced" value ="0">';

            }
            $Form .='</table>';
 
            $Form .='<input type="submit" onclick="return checkEmptyFormFields(event,\'settings\',\'';
            $Form .=$langs->trans("pleaseFillAll").'\')" class="butAction" value="'.$langs->trans('Next')."\">\n</from>";
            if($ajaxNbChar>=0) $Form .= "\n<script type='text/javascript'>\n$('input#Project').change(function(){\nif($('input#search_Project').val().length>2)reload($(this).form)\n;});\n</script>\n";
            break;
    }
}else{
    $accessforbidden = accessforbidden("you don't have enough rights to see this page");   
}

/***************************************************
* VIEW
*
* Put here all code to build page
****************************************************/
$morejs=array("/timesheet/core/js/jsparameters.php","/timesheet/core/js/timesheet.js");
llxHeader('',$langs->trans('TimesheetToInvoice'),'','','','',$morejs);


print $Form;

//javascript to reload the page with the poject selected
print '
<SCRIPT type="text/javascript">
function reload(form){
var pjt=document.getElementById("projectid").value;
self.location="?projectid=" + pjt ;
}
</script>';

llxFooter();
$db->close();



/***************************************************
* FUNCTIONS
*
* Put here all code of the functions
****************************************************/
//FIXME: javascript to block htmlPrintServiceChoice desc priceHT TVA is a service is selected

function getProjectCustomer($projectID){
    //FIXME
}


/***
 * Function to print the line to chose between a predefined service or an ad-hoc one
 */
function htmlPrintServiceChoice($user,$task,$class,$duration,$tasktimelist,$seller,$buyer,$tasknote,$date){  // PJR mod
    global $form,$langs,$conf;
    $userName=($user=='any')?(' - '):print_generic('user','rowid',$user,'firstname','lastname',' '); // PJR mod
    $taskLabel=($task=='any')?(' - '):print_generic('projet_task','rowid',$task,'ref','label',' ');
    $html='<tr class="'.$class.' tsoddeven">';  // PJR mod
	$html.='<td rowspan = "2" align="left" width="20%">'.$userName.'</br>'.$date; // PJR mod
//    $html.='</td><td rowspan = "2" align="left" width="20%">'.$taskLabel;
//    $html.='</td><td rowspan = "2" align="left" width="20%">'.$date;
    $html.='<input type="hidden"   name="userTask['.$user.']['.$task.'][userName]" value="'.$userName.'">';
    $html.='<input type="hidden"   name="userTask['.$user.']['.$task.'][taskLabel]"  value="'. $taskLabel.'">';
    $html.='<input type="hidden"   name="userTask['.$user.']['.$task.'][taskTimeList]"  value="'. $tasktimelist.'">';
    $html.='</td><td colspan = "4" class="tswhitespace">'.$tasknote.'</td>';
//	$html.='<td><input type="text" size="2" maxlength="2" name="userTask['.$user.']['.$task.'][duration]" value="'.$duration.'" ></td></tr>';
	$html.='<td>'.$duration.'</td></tr>';
    $defaultService=getDefaultService($user,$task);
    $addchoices=array('-999'=> $langs->transnoentities('not2invoice'),-1=> $langs->transnoentities('Custom'));
    
    $ajaxNbChar=$conf->global->PRODUIT_USE_SEARCH_TO_SELECT;
    $html.='<tr><td width="20%">'.select_generic('product', 'rowid','userTask['.$user.']['.$task.'][Service]','ref','label',$defaultService,$separator=' - ',$sqlTail=' tosell=1 AND fk_product_type=1', $selectparam='',$addchoices,'',$ajaxNbChar,0).'</td>';  // PJR mod
    $html.='<td ><input type="text"  size="30" name="userTask['.$user.']['.$task.'][Desc]" ></td>';  // PJR mod
    $html.='<td><input type="text"  size="6" name="userTask['.$user.']['.$task.'][PriceHT]" ></td>';  // PJR mod
    //$html.='<th><input type="text" size="6" name="userTask['.$user.']['.$task.']["VAT"]" ></th>';
    $html.='<td>'.$form->load_tva('userTask['.$user.']['.$task.'][VAT]', -1, $seller, $buyer, 0, 0, '', false, 1).'</td>';  // PJR mod
/*
    $html.='<td><input type="text" size="2" maxlength="2" name="userTask['.$user.']['.$task.'][unit_duration]" value="1" >';
    $html.='</br><input name="userTask['.$user.']['.$task.'][unit_duration_unit]" type="radio" value="h" '.(($conf->global->TIMESHEET_TIME_TYPE=="days")?'':'checked').'>'.$langs->trans('Hour');
    $html.='</br><input name="userTask['.$user.']['.$task.'][unit_duration_unit]" type="radio" value="d" '.(($conf->global->TIMESHEET_TIME_TYPE=="days")?'checked':'').'>'.$langs->trans('Days').'</td>';
    $html.='<th><input type="text" size="2" maxlength="2" name="userTask['.$user.']['.$task.'][duration]" value="'.$duration.'" >';
*/
	$html.='<td>';  //PJR mod
    
    $html.='</td</tr>'; // PJR mod
    return $html;
}

function getDefaultService($userid,$taskid){
    global $db,$conf;
    $res= 0;
    $sql.=' SELECT fk_service FROM '.MAIN_DB_PREFIX.'projet_task_extrafields WHERE fk_object=\''.$taskid.'\'';
    $sql.=' UNION ALL';
    $sql.=' SELECT fk_service FROM '.MAIN_DB_PREFIX.'user_extrafields WHERE fk_object=\''.$userid.'\'';
    $sql.=' LIMIT 1';
    $resql=$db->query($sql);
        
    if ($db->num_rows($resql)>0){
        $obj = $db->fetch_object($resql);
        $res=$obj->fk_service;
    }
    return ($res>0)?$res:$conf->global->TIMESHEET_INVOICE_SERVICE;
}

function hasProjectRight($userid,$projectid){
    global $db,$user;
    $res=true;
    if($projectid && !$user->admin){
        $sql=' SELECT rowid FROM '.MAIN_DB_PREFIX.'element_contact ';
        $sql.=' WHERE fk_c_type_contact = \'160\' AND element_id=\''.$projectid; //FIXME 
        $sql.='\' AND fk_socpeople=\''.$userid.'\'';
        $resql=$db->query($sql);
        if (!$resql)$res=false;
    }
    return $res;
}

function Update_task_time_invoice($idInvoice, $idLine,$task_time_list){
    global $db;
    $res=true;
    $sql='UPDATE '.MAIN_DB_PREFIX.'projet_task_time';
    $sql.=" SET invoice_id={$idInvoice}, invoice_line_id={$idLine}";
    $sql.=" WHERE rowid in ({$task_time_list})";
    dol_syslog("ProjectInvoice::setnvoice", LOG_DEBUG);
    $resql=$db->query($sql);
        if (!$resql)$res=false;
    return $res;
}
