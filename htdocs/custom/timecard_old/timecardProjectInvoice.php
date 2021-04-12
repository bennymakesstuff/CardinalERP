<?php
 /* Copyright (C) 2017 delcroip <delcroip@gmail.com>
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
define('TIMESHEET_INVOICE_METHOD','user');
define('TIMESHEET_INVOICE_TASKTIME','user');
define('TIMESHEET_INVOICE_SERVICE','1');
define('TIMESHEET_INVOICE_SHOW_TASK','1');
define('TIMESHEET_INVOICE_SHOW_USER','1');
*/
//load class
include 'core/lib/includeMain.lib.php';
include 'core/lib/generic.lib.php';
//require_once DOL_DOCUMENT_ROOT . '/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';

require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';

$formproject=new FormProjets($db);


//get param
$staticProject=new Project($db);
$projectId=GETPOST('projectid');

$socid=GETPOST('socid');
$month=GETPOST('month');
$year=GETPOST('year');
$mode=GETPOST('invoicingMethod');
$step=GETPOST('step');
$ts2Invoice=GETPOST('ts2Invoice');
$tsNotInvoiced=GETPOST('tsNotInvoiced');
$userid=  is_object($user)?$user->id:$user;
//init handling object
$form = new Form($db);

//FIXME check autorisation for project and page/

if ($user->rights->facture->creer & hasProjectRight($userid,$projectid))
{
    if($projectId>0)$staticProject->fetch($projectId);
    if($socid<=1 || !is_numeric($socid))$socid=$staticProject->socid; //FIXME check must be in place to ensure the user hqs the right to see the project details
$edit=1;
// avoid SQL issue
if(empty($month) || empty($year) || empty($projectId)){
    $step=1;
    
    $month=(date('m')-1);
    if ($month==0){
        $month=12;
        $year=date('Y')-1;
    }else {
        $year=date('Y');
    }
            
}
//steps
    switch ($step)
    { 
        case 2:{
           $fields=($mode=='user')?'fk_user':(($mode=='taskUser')?'fk_user,fk_task':'fk_task'); 
            $sql= 'SELECT  '.$fields.', SUM(tt.task_duration) as duration ';
            $sql.=', GROUP_CONCAT(tt.rowid SEPARATOR ", ") as task_time_list';
             $sql.=' From '.MAIN_DB_PREFIX.'projet_task_time as tt';
            $sql.=' JOIN '.MAIN_DB_PREFIX.'projet_task as t ON tt.fk_task=t.rowid';
            $sql.=' WHERE t.fk_projet='.$projectId;
            $sql.=' AND MONTH(tt.task_date)='.$month;
            $sql.=' AND YEAR(tt.task_date)='.$year;
            if($ts2Invoice!='all'){
                /*$sql.=' AND tt.rowid IN(SELECT GROUP_CONCAT(fk_project_s SEPARATOR ", ")';
                $sql.=' FROM '.MAIN_DB_PREFIX.'project_task_time_approval';  
                $sql.=' WHERE status= "APPROVED" AND MONTH(date_start)='.$month;  
                $sql.=' AND YEAR(date_start)="'.$year.'")'; 
                $sql.=' AND YEAR(date_start)="'.$year.'")'; */
                $sql.=' AND tt.status = "APPROVED"'; 
            }
            if($tsNotInvoiced==1){
                $sql.=' AND tt.invoice_id IS NULL'; 
            }
            $sql.=' GROUP BY '.$fields;
            dol_syslog('timecard::timecardProjectInvoice step2', LOG_DEBUG);    

            
            $Form ='<form name="settings" action="?step=3" method="POST" >'."\n\t"; 
            $Form .='<input type="hidden" name="projectid" value ="'.$projectId.'">';
            $Form .='<input type="hidden" name="year" value ="'.$year.'">';
            $Form .='<input type="hidden" name="month" value ="'.$month.'">';
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
                                $resArray[]=array("USER" => $obj->fk_user,"TASK" =>$obj->fk_task,"DURATION"=>$duration,'LIST'=>$obj->task_time_list);
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
            $Form .='<tr class="liste_titre" width="100%" ><th colspan="8">'.$langs->trans('invoicedServiceSelectoin').'</th><th>';
            $Form .='<tr class="liste_titre" width="100%" ><th >'.$langs->trans("User").'</th>';
            $Form .='<th >'.$langs->trans("Task").'</th><th >'.$langs->trans("Existing").':'.$langs->trans("Service").'</th>';
            $Form .='<th >'.$langs->trans("New").':'.$langs->trans("Description").'</th><th >'.$langs->trans("New").':'.$langs->trans("PriceHT").'</th>';
            $Form .='<th >'.$langs->trans("VAT").'</th><th >'.$langs->trans("unitDuration").'</th><th >'.$langs->trans("savedDuration").'</th>';
            $form = new Form($db);
            foreach($resArray as $res){
                $Form .=htmlPrintServiceChoice($res["USER"],$res["TASK"],'pair',$res["DURATION"],$res['LIST'],$mysoc,$socid);
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
                $object->fk_project			= $projectid;
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
                            $startday = dol_mktime(12, 0, 0, $month, 1, $year);
                            $endday = dol_mktime(12, 0, 0, $month, date('t',$startday), $year);

                            $details='';
                            $result ='';
                            if(($tId!='any') && TIMECARD_INVOICE_SHOW_TASK)$details="\n".$service['taskLabel'];
                            if(($uId!='any')&& TIMECARD_INVOICE_SHOW_USER)$details.="\n".$service['userName'];

                            if($service['Service']>0){
                                 $product = new Product($db);
                                 $product->fetch($service['Service']);

                                 $unit_duration_unit=substr($product->duration, -1);
                                 $factor=($unit_duration_unit=='h')?3600:8*3600;//FIXME support week and month 
                                 $factor=$factor*intval(substr($product->duration,0, -1));
                                 $quantity= $duration/$factor;
                                 $result = $object->addline($product->description.$details, $product->price, $quantity, $product->tva_tx, $product->localtax1_tx, $product->localtax2_tx, $service['Service'], 0, $startday, $endday, 0, 0, '', $product->price_base_type, $product->price_ttc, $product->type, -1, 0, '', 0, 0, null, 0, '', 0, 100, '', $product->fk_unit);


                            }elseif ($service['Service']<>-999){
                                 $factor=($service['unit_duration_unit']=='h')?3600:8*3600;//FIXME support week and month 
                                 $factor=$factor*intval($service['unit_duration']);

                                 $quantity= $duration/$factor;
                                 $result = $object->addline($service['Desc'].$details, $service['PriceHT'], $quantity, $service['VAT'], '', '', '', 0, $startday, $endday, 0, 0, '', 'HT', '', 1, -1, 0, '', 0, 0, null, 0, '', 0, 100, '', '');

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
            $sqlTailJoin=' JOIN llx_element_contact ON t.rowid= element_id ';
            $sqlTailWhere.='  (fk_c_type_contact = "160" OR fk_c_type_contact = "161")'; // should be part of the project with the invoice right
            $sqlTailWhere.=' AND fk_socpeople="'.$userid.'" AND fk_statut=1';
        }
//  PJR was POST
            $Form ='<form name="settings" action="?step=2" method="GET" >'."\n\t";
//            $Form ='<form name="settings" action="?step=2" method="POST" >'."\n\t";
            $Form .='<table class="noborder" width="100%">'."\n\t\t";
            $Form .='<tr class="liste_titre" width="100%" ><th colspan="2">'.$langs->trans('generalInvoiceProjectParam').'</th><th>';    
            $invoicingMethod=TIMECARD_INVOICE_METHOD;
            $Form .='<tr class="pair"><th align="left" width="80%">'.$langs->trans('Project').'</th><th  >';
            //select_generic($table, $fieldValue,$htmlName,$fieldToShow1,$fieldToShow2='',$selected='',$separator=' - ',$sqlTailWhere='', $selectparam='', $addtionnalChoices=array('NULL'=>'NULL'),$sqlTailTable='', $ajaxUrl='')
            $ajaxNbChar=$conf->global->PROJECT_USE_SEARCH_TO_SELECT;
			
						$maxlength = 28;
            $Form .= $formproject->select_projects_list(-1, $projectId, 'projectid', $maxlength, 0, 1, 1, 0, 0, 0, '', 1);

			
//            $Form .=select_generic('projet', 'rowid','projectid','ref','title',$projectId,' - ',$sqlTailWhere,'',NULL,$sqlTailJoin,$ajaxNbChar);
            $Form .='</th></tr><tr class="impair"><th align="left" width="80%">'.$langs->trans('Month').' - '.$langs->trans('Year').'</th><th align="left">'.$htmlother->select_month($month, 'month').' - '.$htmlother->selectyear($year,'year',1,10,3).'</th></tr>';
 //           $Form .='<tr class="pair"><th align="left" width="80%">'.$langs->trans('Month').'</th><th ><input type="text" name="month" value ="'.$month.'"></th></tr>';
           // $Form .='<tr class="impair"><th align="left" width="80%">'.$langs->trans('Customer').'</th><th "><input type="text" name="ccust" value ="'.$custId.'"></th></tr>';
            $Form .='<tr class="pair"><th align="left" width="80%">'.$langs->trans('Mode').'</th><th align="left"><input type="radio" name="invoicingMethod" value="task" ';
            $Form .=($invoicingMethod=="task"?"checked":"").'> '.$langs->trans("Task").' ';
            $Form .='<input type="radio" name="invoicingMethod" value="user" ';
            $Form .=($invoicingMethod=="user"?"checked":"").'> '.$langs->trans("User")." ";
            $Form .='<input type="radio" name="invoicingMethod" value="taskUser" ';
            $Form .=($invoicingMethod=="taskUser"?"checked":"").'> '.$langs->trans("Task").'&'.$langs->trans("User")."</th></tr>\n\t\t";

//cust list
            $Form .='<tr class="impair"><th  align="left">'.$langs->trans('Customer').'</th><th  align="left">'.$form->select_company($socid, 'socid', '(s.client=1 OR s.client=2 OR s.client=3)', 1).'</th></tr>';
//all ts or only approved
           $ts2Invoice=TIMECARD_INVOICE_TASKTIME;
            $Form .='<tr class="pair"><th align="left" width="80%">'.$langs->trans('TimecardToInvoice').'</th><th align="left"><input type="radio" name="ts2Invoice" value="approved" ';
            $Form .=($ts2Invoice=="approved"?"checked":"").'> '.$langs->trans("approvedOnly").' ';
            $Form .='<input type="radio" name="ts2Invoice" value="all" ';
            $Form .=($ts2Invoice=="all"?"checked":"").'> '.$langs->trans("All")."</th></tr>";
// not alreqdy invoice
            if(version_compare(DOL_VERSION,"4.9.9")>=0){
                $Form .='<tr class="impair"><th align="left" width="80%">'.$langs->trans('TimecardNotInvoiced');
                $Form .='</th><th align="left"><input type="checkbox" name="tsNotInvoiced" value="1" ></th></tr>';
                
            }else{
                $Form .='<input type="hidden" name="tsNotInvoiced" value ="0">';

            }
            $Form .='</table>';
 
            $Form .='<input type="submit" onclick="return checkEmptyFormFields(event,\'settings\',\'';
            $Form .=$langs->trans("pleaseFillAll").'\')" class="butAction" value="'.$langs->trans('Next')."\">\n</from>";
 
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
$morejs=array("/timecard/core/js/jsparameters.php","/timecard/core/js/timecard.js");
llxHeader('',$langs->trans('TimecardInvoice'),'','','','',$morejs);

    print '<div class="fichecenter">';
    print '<div class="fichehalfleft">';
print $Form;

    print '</div>';
    print '</div>';

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
function htmlPrintServiceChoice($user,$task,$class,$duration,$tasktimelist,$seller,$byer){
    global $form,$langs;
    $userName=($user=='any')?(' - '):print_generic('user','rowid',$user,'lastname','firstname',' ');
    $taskLabel=($task=='any')?(' - '):print_generic('projet_task','rowid',$task,'ref','label',' ');
    $html='<tr class="'.$class.'"><th align="left" width="20%">'.$userName;
    $html.='</th><th align="left" width="20%">'.$taskLabel;
    $html.='<input type="hidden"   name="userTask['.$user.']['.$task.'][userName]" value="'.$userName.'">';
    $html.='<input type="hidden"   name="userTask['.$user.']['.$task.'][taskLabel]"  value="'. $taskLabel.'">';
    $html.='<input type="hidden"   name="userTask['.$user.']['.$task.'][taskTimeList]"  value="'. $tasktimelist.'">';
    $defaultService=TIMESHEET_INVOICE_SERVICE; 
    $addchoices=array('-999'=> $langs->transnoentitiesnoconv('not2invoice'));
    $ajaxNbChar=$conf->global->PRODUIT_USE_SEARCH_TO_SELECT;
    $html.='</th><th >'.select_generic('product', 'rowid','userTask['.$user.']['.$task.'][Service]','ref','label',$defaultService,$separator=' - ',$sqlTail=' tosell=1 AND fk_product_type=1', $selectparam='',$addchoices,'',$ajaxNbChar).'</th>';
    $html.='<th ><input type="text"  size="30" name="userTask['.$user.']['.$task.'][Desc]" ></th>';
    $html.='<th><input type="text"  size="6" name="userTask['.$user.']['.$task.'][PriceHT]" ></th>';
    //$html.='<th><input type="text" size="6" name="userTask['.$user.']['.$task.']["VAT"]" ></th>';
    $html.='<th>'.$form->load_tva('userTask['.$user.']['.$task.'][VAT]', -1, $seller, $buyer, 0, 0, '', false, 1).'</th>';
    $html.='<th><input type="text" size="2" maxlength="2" name="userTask['.$user.']['.$task.'][unit_duration]" value="1" >';
    $html.='</br><input name="userTask['.$user.']['.$task.'][unit_duration_unit]" type="radio" value="h" >'.$langs->trans('Hour');
    $html.='</br><input name="userTask['.$user.']['.$task.'][unit_duration_unit]" type="radio" value="d" checked>'.$langs->trans('Days').'</th>';
    $html.='<th><input type="text" size="2" maxlength="2" name="userTask['.$user.']['.$task.'][duration]" value="'.$duration.'" >';
    
    $html.='</th</tr>';
    return $html;
}

function hasProjectRight($userid,$projectid){
    global $db,$user;
    $res=true;
    if($projectid && !$user->admin){
        $sql=' SELECT rowid FROM '.MAIN_DB_PREFIX.'element_contact ';
        $sql.=' WHERE fk_c_type_contact = "160" AND element_id="'.$projectid;
        $sql.='" AND fk_socpeople="'.$userid.'"';
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