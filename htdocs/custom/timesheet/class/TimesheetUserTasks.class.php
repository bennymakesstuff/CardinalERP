<?php
/* 
 * Copyright (C) 2014 delcroip <patrick@pmpd.eu>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


/*Class to handle a line of timesheet*/
#require_once('mysql.class.php');
require_once DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php";

require_once DOL_DOCUMENT_ROOT.'/core/class/html.form.class.php';
require_once 'class/TimesheetHoliday.class.php';
require_once 'core/lib/generic.lib.php';
require_once 'class/TimesheetTask.class.php';
require_once 'class/TimesheetFavourite.class.php';


class TimesheetUserTasks extends CommonObject
{
    //common
    public $db;							//!< To store db handler
    public $error;							//!< To return error code (or message)
    public $errors=array();				//!< To return several error codes (or messages)
    public $element='timesheetuser';			//!< Id that identify managed objects
    public $table_element='project_task_timesheet';		//!< Name of table without prefix where object is stored
// from db
    public $id;
    public $userId;
    public $date_start='';
    public $date_end;
    public $status;
    public $note;

//basic DB logging
    public $date_creation='';
    public $date_modification='';
    public $user_creation;
    public $user_modification;  

//working variable

    public $duration;
    public $ref; 
    public $user;
    public $holidays;
    public $taskTimesheet;
    public $headers;
    public $weekDays;
    public $timestamp;
    public $whitelistmode;
    public $userName;
    /**
     *   Constructor
     *
     *   @param		DoliDB		$db      Database handler
     */
    function __construct($db,$userId=0)
    {
        global $user,$conf;
        $this->db = $db;
        //$this->holidays=array();
        $this->user=$user;
        $this->userId= ($userId==0)?(is_object($user)?$user->id:$user):$userId;
        $this->headers=explode('||', $conf->global->TIMESHEET_HEADERS);
        $this->getUserName();
        
    }
 /******************************************************************************
 * 
 * DB methods
 * 
 ******************************************************************************/
    /**
     *  cREATE object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->userId)) $this->userId=trim($this->userId);
		if (isset($this->date_start)) $this->date_start=trim($this->date_start);
		if (isset($this->date_end)) $this->date_end=trim($this->date_end);
		if (isset($this->status)) $this->status=trim($this->status);
		if (isset($this->date_creation)) $this->date_creation=trim($this->date_creation);
		if (isset($this->date_modification)) $this->date_modification=trim($this->date_modification);
		if (isset($this->user_modification)) $this->user_modification=trim($this->user_modification);
		if (isset($this->note)) $this->note=trim($this->note);
                $userId= (is_object($user)?$user->id:$user);
        
		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element."(";
		
		$sql.= 'fk_userid,';
		$sql.= 'date_start,';
                $sql.= 'date_end,';
		$sql.= 'status,';
		$sql.= 'date_creation,';
                $sql.= 'date_modification,';
                $sql.= 'fk_user_modification,';
                $sql.= 'note';

		
        $sql.= ") VALUES (";
        
		$sql.=' '.(! isset($this->userId)?'NULL':'\''.$this->userId.'\'').',';
		$sql.=' '.(! isset($this->date_start) || dol_strlen($this->date_start)==0?'NULL':'\''.$this->db->idate($this->date_start).'\'').',';
		$sql.=' '.(! isset($this->date_end) || dol_strlen($this->date_end)==0?'NULL':'\''.$this->db->idate($this->date_end).'\'').',';
		$sql.=' '.(! isset($this->status)?DRAFT:$this->status).',';
		$sql.=' NOW() ,';
                $sql.=' NOW() ,';
		$sql.=' \''.$userId.'\','; //fixme 3.5
		$sql.=' '.(! isset($this->note)?'NULL':'\''.$this->db->escape(dol_html_entity_decode($this->note, ENT_QUOTES)).'\'');
        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(__METHOD__, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //$result=$this->call_trigger('MYOBJECT_CREATE',$user);
	            //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		} 
		else
		{
			$this->db->commit();
            return $this->id;
		}
    }


    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    	Id object
     *  @param	string	$ref	Ref
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetch($id,$ref='')
    {
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.=' t.fk_userid,';
		$sql.=' t.date_start,';
		$sql.=' t.date_end,';
		$sql.=' t.status,';
		$sql.=' t.date_creation,';
		$sql.=' t.date_modification,';
		$sql.=' t.fk_user_modification,';
		$sql.=' t.note';

		
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        if ($ref) $sql.= " WHERE t.ref = '".$ref."'";
        else $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetch");
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                $this->userId = $obj->fk_userid;
                $this->date_start = $this->db->jdate($obj->date_start);
                $this->date_end = $this->db->jdate($obj->date_end);
                $this->status = $obj->status;
                $this->date_creation = $this->db->jdate($obj->date_creation);
                $this->date_modification = $this->db->jdate($obj->date_modification);
                $this->user_modification = $obj->fk_user_modification;
                $this->note  = $obj->note;

                
            }
            $this->db->free($resql);
            $this->ref=$this->date_start.'_'.$this->userId;
            
            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            return -1;
        }
    }
    /**
     *  Load object in memory from the database
     *
     *  @return int          	<0 if KO, >0 if OK
     */
    function fetchByWeek()
    {
        
    	global $langs;
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.=' t.fk_userid,';
		$sql.=' t.date_start,';
		$sql.=' t.date_end,';
		$sql.=' t.status,';
		$sql.=' t.date_creation,';
		$sql.=' t.date_modification,';
		$sql.=' t.fk_user_modification,';
		//$sql.=' t.fk_task,';
		$sql.=' t.note';

		
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";

        $sql.= " WHERE t.date_start = '".$this->db->idate($this->date_start)."'";
	$sql.= " AND t.fk_userid = '".$this->userId."'";

        //$sql.= " AND t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetchByWeek");
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->id    = $obj->rowid;
                
                $this->userId = $obj->fk_userid;
                $this->date_start = $this->db->jdate($obj->date_start);
                $this->date_end = $this->db->jdate($obj->date_end);
                $this->status = $obj->status;
                 $this->date_creation = $this->db->jdate($obj->date_creation);
                $this->date_modification = $this->db->jdate($obj->date_modification);
                $this->user_modification = $obj->fk_user_modification;
                $this->note  = $obj->note;
                $this->date_end= $this->db->jdate($obj->date_end);
                
            }else{
                unset($this->status) ;
                unset($this->date_modification );
                unset($this->user_modification );
                unset($this->note );
                unset($this->date_creation  );
            
                //$this->date_end= getEndWeek($this->date_start);
                $this->create($this->user);
                $this->fetch($this->id);
            }
            $this->db->free($resql);
            
            return 1;
        }
        else
        {
      	    $this->error="Error ".$this->db->lasterror();
            return -1;
        }
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    function update($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (isset($this->userId)) $this->userId=trim($this->userId);
		if (isset($this->date_start)) $this->date_start=trim($this->date_start);
		if (isset($this->date_end)) $this->date_end=trim($this->date_end);
		if (isset($this->status)) $this->status=trim($this->status);
		if (isset($this->date_creation)) $this->date_creation=trim($this->date_creation);
		if (isset($this->date_modification)) $this->date_modification=trim($this->date_modification);
		if (isset($this->user_modification)) $this->user_modification=trim($this->user_modification);
		if (isset($this->note)) $this->note=trim($this->note);
                $userId= (is_object($user)?$user->id:$user);
        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
        
		$sql.=' fk_userid='.(empty($this->userId) ? 'null':'\''.$this->userId.'\'').',';
		$sql.=' date_start='.(dol_strlen($this->date_start)!=0 ? '\''.$this->db->idate($this->date_start).'\'':'null').',';
		$sql.=' date_end='.(dol_strlen($this->date_end)!=0 ? '\''.$this->db->idate($this->date_end).'\'':'null').',';
		$sql.=' status='.(empty($this->status)? DRAFT:$this->status).',';
		$sql.=' date_modification=NOW() ,';
		$sql.=' fk_user_modification=\''.$userId.'\',';
		$sql.=' note=\''.$this->db->escape(dol_html_entity_decode($this->note, ENT_QUOTES)).'\'';

        
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		dol_syslog(__METHOD__);
        $resql = $this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
		{
			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //$result=$this->call_trigger('MYOBJECT_MODIFY',$user);
	            //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
	            //// End call triggers
			 }
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
	            $this->error.=($this->error?', '.$errmsg:$errmsg);
			}
			$this->db->rollback();
			return -1*$error;
		}
		else
		{
			$this->db->commit();
			return 1;
		}
    }
    /**
     *  Delete object in database
     *
     *	@param  User	$user        User that deletes
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return	int					 <0 if KO, >0 if OK
     */
    function delete($user, $notrigger=0)
    {
            global $conf, $langs;
            $error=0;

            $this->db->begin();

            if (! $error)
            {
                    if (! $notrigger)
                    {
                            // Uncomment this and change MYOBJECT to your own tag if you
                    // want this action calls a trigger.

                //// Call triggers
                //$result=$this->call_trigger('MYOBJECT_DELETE',$user);
                //if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
                //// End call triggers
                    }
            }

            if (! $error)
            {
            $sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
            $sql.= " WHERE rowid=".$this->id;

            dol_syslog(__METHOD__);
            $resql = $this->db->query($sql);
            if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
            }

    // Commit or rollback
            if ($error)
            {
                    foreach($this->errors as $errmsg)
                    {
                dol_syslog(__METHOD__." ".$errmsg, LOG_ERR);
                $this->error.=($this->error?', '.$errmsg:$errmsg);
                    }
                    $this->db->rollback();
                    return -1*$error;
            }
            else
            {
                    $this->db->commit();
                    return 1;
            }
    }



    /**
     *	Load an object from its id and create a new one in database
     *
     *	@param	int		$fromid     Id of object to clone
     * 	@return	int					New id of clone
     */
    function createFromClone($fromid)
    {
            global $user,$langs;

            $error=0;

            $object=new Timesheetuser($this->db);

            $this->db->begin();

            // Load source object
            $object->fetch($fromid);
            $object->id=0;
            $object->statut=0;

            // Clear fields
            // ...

            // Create clone
            $result=$object->create();

            // Other options
            if ($result < 0)
            {
                    $this->error=$object->error;
                    $error++;
            }

            if (! $error)
            {


            }

            // End
            if (! $error)
            {
                    $this->db->commit();
                    return $object->id;
            }
            else
            {
                    $this->db->rollback();
                    return -1;
            }
    }   
    
/******************************************************************************
 * 
 * Other methods
 * 
 ******************************************************************************/    
    
    /* Funciton to fect the holiday of a single user for a single week.
    *  @param    string              	$startDate            start date in php format
    *  @return     string                                       result
    */    
    function fetchAll($startdate,$whitelistmode=false){
        global $conf;
        $this->whitelistmode=(is_numeric($whitelistmode)&& !empty($whitelistmode) )?$whitelistmode:$conf->global->TIMESHEET_WHITELIST_MODE;
        $this->date_start=  getStartDate($startdate);     
        $this->ref=$this->date_start.'_'.$this->userId;
        $this->date_end= getEndDate($this->date_start);
        $this->timestamp=  getToken();
        $ret=$this->fetchByWeek();
        $ret+=$this->fetchTaskTimesheet();
        //$ret+=$this->getTaskTimeIds(); 
        //FIXME module holiday should be activated ?
        $ret2=$this->fetchUserHoliday(); 
        $this->saveInSession();
    }        
    
     /* Funciton to fect the holiday of a single user for a single week.
    *  @return     string                                       result
    */
    function fetchUserHoliday(){
        $this->holidays=new TimesheetHoliday($this->db);
        $ret=$this->holidays->fetchUserWeek($this->userId,$this->date_start,$this->date_end);
        return $ret;
    }
    /*
 * function to load the parma from the session
 */
function loadFromSession($timestamp,$id){

    $this->fetch($id);
    $this->timestamp=$timestamp;
    $this->userId= $_SESSION['task_timesheet'][$timestamp][$id]['userId'];
    $this->date_start= $_SESSION['task_timesheet'][$timestamp][$id]['dateStart'];
    $this->date_end= $_SESSION['task_timesheet'][$timestamp][$id]['dateEnd'];
    $this->ref=$_SESSION['task_timesheet'][$timestamp][$id]['ref'];
    $this->holidays=  unserialize( $_SESSION['task_timesheet'][$timestamp][$id]['holiday']);
    $this->taskTimesheet=  unserialize( $_SESSION['task_timesheet'][$timestamp][$id]['taskTimesheet']);;
}

    /*
 * function to load the parma from the session
 */
function saveInSession(){
    $_SESSION['task_timesheet'][$this->timestamp][$this->id]['userId']=$this->userId;
    $_SESSION['task_timesheet'][$this->timestamp][$this->id]['ref']=$this->ref;
    $_SESSION['task_timesheet'][$this->timestamp][$this->id]['dateStart']=$this->date_start;
    $_SESSION['task_timesheet'][$this->timestamp][$this->id]['dateEnd']=$this->date_end;
    $_SESSION['task_timesheet'][$this->timestamp][$this->id]['holiday']= serialize($this->holidays);
    $_SESSION['task_timesheet'][$this->timestamp][$this->id]['taskTimesheet']= serialize($this->taskTimesheet);
}


/*
 * function to genegate the timesheet tab
 * 
 *  @param    int              	$userid                   user id to fetch the timesheets
 *  @return     array(string)                                             array of timesheet (serialized)
 */
 function fetchTaskTimesheet($userid=''){     
    global $conf;
 
    if($userid==''){$userid=$this->userId;}
    $whiteList=array();
    $staticWhiteList=new TimesheetFavourite($this->db);
    $datestart=$this->date_start;
    $datestop= $this->date_end;
    $whiteList=$staticWhiteList->fetchUserList($userid, $datestart, $datestop);

     // Save the param in the SeSSION
     $tasksList=array();
     //$whiteListNumber=is_array($whiteList)?count($whiteList):0;
     $sqlwhiteList='';
    /* if($whiteListNumber){
         
            $sqlwhiteList=', (CASE WHEN tsk.rowid IN ('.implode(",",  array_keys($whiteList)).') THEN \'1\' ';
            $sqlwhiteList.=' ELSE \'0\' END ) AS listed';
    }*/
  
    
    $sql ='SELECT DISTINCT element_id as taskid,prj.fk_soc,tsk.fk_projet,';
    $sql.='tsk.fk_task_parent,tsk.rowid,app.rowid as appid,prj.ref as prjRef, tsk.ref as tskRef';
    $sql.=$sqlwhiteList;
    $sql.=" FROM ".MAIN_DB_PREFIX."element_contact as ec"; 
    $sql.=' LEFT JOIN '.MAIN_DB_PREFIX.'c_type_contact as ctc ON (ctc.rowid=ec.fk_c_type_contact  AND ctc.active=\'1\') ';
    $sql.=' LEFT JOIN '.MAIN_DB_PREFIX.'projet_task as tsk ON tsk.rowid=ec.element_id ';
    $sql.=' LEFT JOIN '.MAIN_DB_PREFIX.'projet as prj ON prj.rowid= tsk.fk_projet ';
    //approval

    if( $this->status==DRAFT || $this->status==REJECTED){
     $sql.=' LEFT JOIN '.MAIN_DB_PREFIX.'project_task_time_approval as app ';
    }else{ // take only the ones with a task_time linked
        $sql.='JOIN '.MAIN_DB_PREFIX.'project_task_time_approval as app ';
    }
    $sql.=' ON tsk.rowid= app.fk_projet_task AND app.fk_userid=fk_socpeople'; 

    $sql.=' AND app.date_start=\''.$this->db->idate($datestart).'\'';    
    $sql.=' AND app.date_end=\''.$this->db->idate($datestop).'\'';    

    //end approval
    $sql.=" WHERE ec.fk_socpeople='".$userid."' AND ctc.element='project_task' ";
    if($conf->global->TIMESHEET_HIDE_DRAFT=='1'){
         $sql.=' AND prj.fk_statut>"0" ';
    }
    $sql.=' AND (prj.datee>=\''.$this->db->idate($datestart).'\' OR prj.datee IS NULL)';
    $sql.=' AND (prj.dateo<=\''.$this->db->idate($datestop).'\' OR prj.dateo IS NULL)';
    $sql.=' AND (tsk.datee>=\''.$this->db->idate($datestart).'\' OR tsk.datee IS NULL)';
    $sql.=' AND (tsk.dateo<=\''.$this->db->idate($datestop).'\' OR tsk.dateo IS NULL)';
    $sql.='  ORDER BY prj.fk_soc,prjRef,tskRef ';

     dol_syslog("timesheet::getTasksTimesheet full ", LOG_DEBUG);

    $resql=$this->db->query($sql);
    if ($resql)
    {
        $this->taskTimesheet=array();
            $num = $this->db->num_rows($resql);
            $i = 0;
            // Loop on each record found, so each couple (project id, task id)
            while ($i < $num)
            {
                    $error=0;
                    $obj = $this->db->fetch_object($resql);
                    $tasksList[$i] = NEW TimesheetTask($this->db,$obj->taskid);
                    //$tasksList[$i]->id= $obj->taskid;                     
                    if($obj->appid){
                        $tasksList[$i]->fetch($obj->appid);
                    }              
                    $tasksList[$i]->userId=$this->userId;
                    $tasksList[$i]->date_start_approval=$this->date_start;
                    $tasksList[$i]->date_end_approval=$this->date_end;
                    $tasksList[$i]->task_timesheet=$this->id;
                    //$tasksList[$i]->listed=$obj->listed;
                    $tasksList[$i]->listed=$whiteList[$obj->taskid];
                    $i++;
                    
                    
            }
            $this->db->free($resql);
             $i = 0;
            if(isset($this->taskTimesheet))unset($this->taskTimesheet);
             foreach($tasksList as $row)
            {
                dol_syslog(__METHOD__.'::task='.$row->id, LOG_DEBUG);
                $row->getTaskInfo(); // get task info include in fetch    
                $row->getActuals($datestart,$datestop,$userid); 
                $this->taskTimesheet[]=  $row->serialize();                   
            }
            return 1;

    }else
    {
            dol_print_error($this->db);
            return -1;
    }
 }

 /*
 * function to post the all actual submitted
 * 
 *  @param    array(int)              	$tabPost               array sent by POST with all info about the task
 *  @return     int                                                        number of tasktime creatd/changed
 */
 function updateActuals($tabPost,$notes=array())
{
     //FIXME, tta should be creted
    if($this->status==APPROVED)
        return -1;
    dol_syslog('Entering in Timesheet::task_timesheet.php::updateActuals()');     
    $ret=0;
   // $tmpRet=0;
    $_SESSION['task_timesheet'][$this->timestamp]['timeSpendCreated']=0;
    $_SESSION['task_timesheet'][$this->timestamp]['timeSpendDeleted']=0;
    $_SESSION['task_timesheet'][$this->timestamp]['timeSpendModified']=0;
        /*
         * For each task store in matching the session timestamp
         */
        foreach ($this->taskTimesheet as $key  => $row) {
            $tasktime= new TimesheetTask($this->db);
            $tasktime->unserialize($row);     
            $ret+=$tasktime->postTaskTimeActual($tabPost[$tasktime->id],$this->userId,$this->user, $this->timestamp, $this->status,$notes[$tasktime->appId]);

            $this->taskTimesheet[$key]=$tasktime->serialize();
            
        } 
        /*
    if(!empty($idList)){
        //$this->project_tasktime_list=$idList;
        $this->update($this->user);
    }
    */
    return $ret;
}


/*
 * function to get the name from a list of ID
 * 
  *  @param    object           	$db             database objet
 *  @param    array(int)/int        $userids    	array of manager id 
  *  @return  array (int => String)  				array( ID => userName)
 */
function getUserName(){


    $sql="SELECT usr.rowid, CONCAT(usr.firstname,' ',usr.lastname) as username FROM ".MAIN_DB_PREFIX.'user AS usr WHERE';

	$sql.=' usr.rowid = '.$this->userId;
      dol_syslog("task_timesheet::get_userName ", LOG_DEBUG);
    $resql=$this->db->query($sql);
    
    if ($resql)
    {
        $i=0;
        $num = $this->db->num_rows($resql);
        if ( $num)
        {
            $obj = $this->db->fetch_object($resql);
            
            if ($obj)
            {
                $this->userName=$obj->username;        
            }else{
                return -1;
            }
            $i++;
        }
        
    }
    else
    {
       return -1;
    }
      //$select.="\n";
    return 0;
 }
  /*
 * update the status based on the underlying Task_time_approval
 *  
 *  @param    object/int                $user           timesheet object, (task)
 *  @param    string              	$status              to overrule the logic if the status enter has an higher priority
 *  @return     string                         status updated of KO(-1)
 */
function updateStatus($user,$status=0){

    if($this->id<=0)return -1;
    $updatedStatus=2;
    if ($status!=''){
        if($status<0 || $status> STATUSMAX)return -1; // status not valid
        $updatedStatus=  $status;
    }else if(!empty($this->status)){
         $updatedStatus=  $this->status;
    }
    
    
    if(count($this->taskTimesheet)<1 )$this->fetchTaskTimesheet();
    if($status==$this->status){ // to avoid eternal loop
        return 1;
    }
    //look for the status to apply to the TS  from the TTA
    foreach($this->taskTimesheet as $row){
        $tta= new TimesheetTask($this->db);
        $tta->unserialize($row);
        if($tta->appId>0){ // tta already created
            $tta->fetch($tta->appId);
            $statusPriorityCur=  $tta->status; 
            $updatedStatus=($updatedStatus>$statusPriorityCur)?$updatedStatus:$statusPriorityCur;
        }// no else as the tta should be created upon submission of the TS not status update
        
    }
    $this->status= $updatedStatus;
    $this->update($user);
     return $this->status;
}
 
 /*
 * change the status of an approval 
 * 
 *  @param      object/int        $user         user object or user id doing the modif
 *  @param      int               $id           id of the timesheetuser
 *  @return     int      		   	 <0 if KO, Id of created object if OK
 */
//    Public function setAppoved($user,$id=0){
Public function setStatus($user,$status,$id=0){ //role ?
            $error=0;
            //if the satus is not an ENUM status
            if($status<0 || $status>STATUSMAX){
                dol_syslog(get_class($this)."::setStatus this status '{$status}' is not part or the enum list", LOG_ERR);
                return false;
            }
            $Approved=(in_array($status, array(APPROVED,UNDERAPPROVAL)));
            $Rejected=(in_array($status, array(REJECTED,CHALLENGED)));
            $Submitted= ($status==SUBMITTED)?true:false;
            $draft= ($status==DRAFT)?true:false;
            // Check parameters
            
            if($id!=0)$this->fetch($id);
            $this->status=$status;
        // Update request
            $error=($this->id<=0)?$this->create($user):$this->update($user);
            if($error>0){  
                    if($status==REJECTED)$this->sendRejectedReminders($user);
                    if(count($this->taskTimesheet)<1 ){
                        $this->fetch($id);
                        $this->fetchTaskTimesheet();
                  
                    }
                    if(count($this->taskTimesheet)>0 )foreach($this->taskTimesheet as $ts)
                    {
                        $tasktime= new TimesheetTask($this->db);
                        $tasktime->unserialize($ts);
                        //$tasktime->appId=$this->id;
                        if($Approved)$ret=$tasktime->Approved($user,TEAM,false);
                        else if($Rejected)$ret=$tasktime->challenged($user,TEAM,false);
                        else if($Submitted)$ret=$tasktime->submitted($user);
                        else if($draft)$ret=$tasktime->setStatus($user,DRAFT);
                    }
                      //if($ret>0)$this->db->commit();
			return 1;
		}

    }



    
/******************************************************************************
 * 
 * HTML  methods
 * 
 ******************************************************************************/
/* function to genegate the tHTML view of the TS
 * 
  *  @param    bool           $ajax     ajax of html behaviour
  *  @return     string                                                   html code
 */
    
    
function getHTML($ajax=false,$Approval=false){ 
    global $langs;
    $Form =$this->getHTMLHeader($ajax);
// show the filter
    $Form .='<tr class="timesheet_line" id="searchline">';
    $Form .='<td><a>'.$langs->trans("Search").'</a></td>';
    $Form .='<td span="0"><input type="texte" name="taskSearch" onkeyup="searchTask(this)"></td></tr>';
    $Form .=$this->getHTMLHolidayLines($ajax);

    if(!$Approval)$Form .=$this->getHTMLTotal();
    //$Form .='<tbody style="overflow:auto;">'; //FIXME, max height should be defined
    $Form .=$this->getHTMLtaskLines($ajax);
    //$Form .= '</tbody>'; // overflow div
    $Form .=$this->getHTMLTotal();
    $Form .= '</table>';
    $Form .=$this->getHTMLNote($ajax);
    if(!$Approval){
        $Form .=$this->getHTMLFooter($ajax);
    }
    $Form .= '</br>'."\n";
    return $Form;
}
    
    
    
/* function to genegate the timesheet table header
 * 
  *  @param    bool           $ajax     ajax of html behaviour
  *  @param     int            $week    week to show (0 means show all)  // FIXME
  *  @return     string                                                   html code
 */
function getHTMLHeader($ajax=false,$week=0){
     global $langs,$conf;
     
    $weeklength=  getDayInterval($this->date_start, $this->date_end);
    $maxColSpan=$weeklength+count($this->headers);
    $format=($langs->trans("FormatDateShort")!="FormatDateShort"?$langs->trans("FormatDateShort"):$conf->format_date_short);  
        $html = '<input type="hidden" name="timestamp" value="'.$this->timestamp."\"/>\n";
    $html .= '<input type="hidden" name="startDate" value="'.$this->date_start.'" />';  
     $html .= '<input type="hidden" name="tsUserId" value="'.$this->id.'" />'; 
    $html.="\n<table id=\"timesheetTable_{$this->id}\" class=\"noborder\" width=\"100%\">\n";
     ///Whitelist tab
        if($conf->global->TIMESHEET_TIME_SPAN=="month"){
        //remove Year
        //$format=str_replace('Y','',str_replace('%Y','',str_replace('Y/','',str_replace('/%Y','',$format))));    
        $format="%d";
        $html.='<tr class="liste_titre" id="">'."\n";
        $html.='<td colspan="'.$maxColSpan.'" align="center"><a >'.$langs->trans(date('F',$this->date_start)).' '.date('Y',$this->date_start).'</a></td>';
        $html.='</tr>';
    }
    
    
    $html.='<tr class="liste_titre" id="">'."\n";
     
     
     foreach ($this->headers as $key => $value){
         $html.="\t<th ";
         if (count($this->headers)==1){
                $html.='colspan="2" ';
         }
         $html.=">".$langs->trans($value)."</th>\n";
     }
    $opendays=str_split($conf->global->TIMESHEET_OPEN_DAYS);
    
  

    for ($i=0;$i<$weeklength;$i++)
    {
        $curDay=$this->date_start+ SECINDAY*$i+SECINDAY/4;
//        $html.="\t".'<th width="60px"  >'.$langs->trans(date('l',$curDay)).'<br>'.dol_mktime($curDay)."</th>\n";
        $htmlDay=($conf->global->TIMESHEET_TIME_SPAN=="month")?substr($langs->trans(date('l',$curDay)),0,3):$langs->trans(date('l',$curDay));
        $html.="\t".'<th class="days_'.$this->id.'" id="'.$this->id.'_'.$i.'" width="35px" style="text-align:center;" >'.$htmlDay.'<br>'.dol_print_date($curDay,$format)."</th>\n"; //FIXME : should remove Y/,/Y and Y from the regex
    }
    // $html.="</tr>\n";
     //$html.='<tr id="hiddenParam" style="display:none;">';
     //$html.= '<td colspan="'.($this->headers.lenght+$weeklength).'"> ';

    //$html .='</td></tr>';

     return $html;
     
 }
 
/* function to genegate the timesheet table header
 * 
  *  @param    bool           $ajax     ajax of html behaviour
  *  @return     string                                                   html code
 */
 function getHTMLFormHeader($ajax=false){
     global $langs;
    $html ='<form id="timesheetForm" name="timesheet" action="?action=submit&wlm='.$this->whitelistmode.'&userid='.$this->userId.'" method="POST"';
    if($ajax)$html .=' onsubmit=" return submitTimesheet(0);"'; 
    $html .='>';
     return $html;
     
 }
  /* function to genegate ttotal line
 * 
  *  @param     int            $week    week to show (0 means show all)  // FIXME
  *  @return     string                                                   
 */
 function getHTMLTotal($week=0){

    $html .="<tr class='liste_titre'>\n";
    $html .='<th colspan="'.(count($this->headers)-1).'" align="right" > TOTAL </th>';
    $length=  getDayInterval($this->date_start,$this->date_end);
    $html .="<th><div class=\"TotalUser_{$this->id}\">&nbsp;</div></th>\n"; 
    for ($i=0;$i<$length;$i++)
    {
       $html .="<th><div class=\"TotalColumn_{$this->id} TotalColumn_{$this->id}_{$i}\">&nbsp;</div></th>\n";
     }
    $html .="</tr>\n";
    return $html;
     
 }

  /* function to genegate the timesheet table header
 * 
 *  @param     int              	$ajax         enable ajax handling
 *  @return     string                                               html code
 */
 function getHTMLFooter($ajax=false){
     global $langs,$apflows;
    //form button

    $html .= '<div class="tabsAction">';
     $isOpenSatus=in_array($this->status, array(DRAFT,CANCELLED,REJECTED));
    if($isOpenSatus){
        $html .= '<input type="submit" class="butAction" name="save" value="'.$langs->trans('Save')."\" />\n";
        //$html .= '<input type="submit" class="butAction" name="submit" onClick="return submitTs();" value="'.$langs->trans('Submit')."\" />\n";

        if(in_array('1',array_slice ($apflows,1))){
            $html .= '<input type="submit" class="butAction" name="submit"  value="'.$langs->trans('Submit')."\" />\n";
        }
        $html .= '<a class="butActionDelete" href="?action=list&startDate='.$this->date_start.'">'.$langs->trans('Cancel').'</a>';

    }else if($this->status==SUBMITTED)$html .= '<input type="submit" class="butAction" name="recall" " value="'.$langs->trans('Recall')."\" />\n";

    $html .= '</div>';
    $html .= "</form>\n";
    if($ajax){
    $html .= '<script type="text/javascript">'."\n\t";
    $html .='window.onload = function(){loadXMLTimesheet("'.$this->date_start.'",'.$this->userId.');}';

    $html .= "\n\t".'</script>'."\n";
    }
     return $html;
     
 }
   /* function to genegate the timesheet table header
 * 
 *  @param    int           $current           number associated with the TS AP
 *  @param     int              	$timestamp         timestamp
  *  @return     string                                                   html code
 */
 function getHTMLFooterAp($current,$timestamp){
     global $langs;
    //form button

    $html .= '<input type="hidden" name="timestamp" value="'.$timestamp."\"/>\n";
    $html .= '<input type="hidden" name="target" value="'.($current+1)."\"/>\n";
    $html .= '<div class="tabsAction">';
    if($offset==0 || $prevOffset!=$offset)$html .= '<input type="submit" class="butAction" name="Send" value="'.$langs->trans('Next')."\" />\n";
    //$html .= '<input type="submit" class="butAction" name="submit" onClick="return submitTs();" value="'.$langs->trans('Submit')."\" />\n";


    $html .= '</div>';
    $html .= "</form>\n";
    if($ajax){
    $html .= '<script type="text/javascript">'."\n\t";
    $html .='window.onload = function(){loadXMLTimesheet("'.$this->date_start.'",'.$this->userId.');}';

    $html .= "\n\t".'</script>'."\n";
    }
     return $html;
     
 }
      /*
 * function to genegate the timesheet list
 *  @return     string                                                   html code
 */
 function getHTMLtaskLines($ajax=false){

        $i=1;
        $Lines='';
        $nbline=count($this->taskTimesheet);
        if(!$ajax & is_array($this->taskTimesheet)){
            foreach ($this->taskTimesheet as $timesheet) {          
                $row=new TimesheetTask($this->db);            
                 $row->unserialize($timesheet);
                //$row->db=$this->db;
                if(in_array($this->status, array(REJECTED, DRAFT,PLANNED,CANCELLED ))){
                    $openOveride= 1;
                }else if(in_array($this->status, array(UNDERAPPROVAL, INVOICED, APPROVED,CHALLENGED,SUBMITTED ))) {
                    $openOveride= -1;
                }else{
                    $openOveride= 0;
                }
                        
            
                $Lines.=$row->getFormLine( $i,$this->headers,$this->id,$openOveride); 
                if( $i%10==0 &&  $nbline-$i >5) $Lines.=$this->getHTMLTotal ();
		$i++;
            }
        }
        
        return $Lines;
 }    
   /* function to genegate the timesheet note
 * 
  *  @return     string                                                   html code
 */
 function getHTMLNote(){
     global $langs;
     $isOpenSatus=(in_array($this->status, array(REJECTED, DRAFT,PLANNED,CANCELLED )));
     $html='<div class="noborder"><div  width="100%">'.$langs->trans('Note').'</div><div width="100%">';
   

    if($isOpenSatus){
        $html.='<textarea class="flat"  cols="75" name="noteTaskApproval['.$this->id.']" rows="3" >'.$this->note.'</textarea>';
        $html.='</div>';
    }else if(!empty($this->note)){
        $html.=$this->note;
        $html.='></div>';
    }else{
        $html="";
    }
    
    return $html;  
 }
        /*
 * function to genegate the timesheet list
 *  @return     string                                                   html code
 */
 function getHTMLHolidayLines($ajax=false){

        $i=0;
        $Lines='';
        if(!$ajax){
            $Lines.=$this->holidays->getHTMLFormLine($this->headers,$this->id);
        
        }
        
        return $Lines;
 }    
 /*
 * function to print the timesheet navigation header
 * 
 *  @param    string              	$optioncss           printmode
 *  @param     int              	$ajax                support the ajax mode ( not supported yet)
 *  @param     object             	$form                form object
 *  @return     string                                       HTML
 */
function getHTMLNavigation($optioncss, $ajax=false){
	global $langs,$conf;
        $form= new Form($this->db);
        $tail='';
        //$tail='&wlm='.$this->whitelistmode;
        if(isset($conf->global->TIMESHEET_ADD_FOR_OTHER) && $conf->global->TIMESHEET_ADD_FOR_OTHER==1  )$tail='&userid='.$this->userId;
        $Nav=  '<table class="noborder" width="50%">'."\n\t".'<tr>'."\n\t\t".'<th>'."\n\t\t\t";
	//if($ajax){
       //     $Nav.=  '<a id="navPrev" onClick="loadXMLTimesheet(\''.getStartDate($this->date_start,-1).'\',0);';
        //}else{
            $Nav.=  '<a href="?dateStart='.getStartDate($this->date_start,-1).$tail;   
        //}
        if ($optioncss != '')$Nav.=   '&amp;optioncss='.$optioncss;
	$Nav.=  '">  &lt;&lt; '.$langs->trans("Previous").' </a>'."\n\t\t</th>\n\t\t<th>\n\t\t\t";
	//if($ajax){
        //    $Nav.=  '<form name="goToDate" onsubmit="return toDateHandler();" action="?action=goToDate&wlm='.$this->whitelistmode.'" method="POST">'."\n\t\t\t";
        //}else{
            $Nav.=  '<form name="goToDate" action="?action=goToDate'.$tail.'" method="POST" >'."\n\t\t\t";
        //}
        $Nav.=   $langs->trans("GoTo").': '.$form->select_date(-1,'toDate',0,0,0,"",1,1,1)."\n\t\t\t";;
	$Nav.=  '<input type="submit" value="Go" /></form>'."\n\t\t</th>\n\t\t<th>\n\t\t\t";
	//if($ajax){
        //    $Nav.=  '<a id="navNext" onClick="loadXMLTimesheet(\''.getStartDate($this->date_start,1).'\',0);';
	//}else{
            $Nav.=  '<a href="?dateStart='.getStartDate($this->date_start,1).$tail;
            
        //}
        if ($optioncss != '') $Nav.=   '&amp;optioncss='.$optioncss;
        $Nav.=  '">'.$langs->trans("Next").' &gt;&gt; </a>'."\n\t\t</th>\n\t</tr>\n </table>\n";
        return $Nav;
}



     /**
     *	Return clickable name (with picto eventually)
     *
     *	@param		string			$htmlcontent 		text to show
     *	@param		int			$id                     Object ID
     *	@param		string			$ref                    Object ref
     *	@param		int			$withpicto		0=_No picto, 1=Includes the picto in the linkn, 2=Picto only
     *	@return		string						String with URL
     */
    function getNomUrl($htmlcontent,$id=0,$ref='',$withpicto=0)
    {
    	global $langs;

    	$result='';
        if(empty($ref) && $id==0){
            if(isset($this->id))  {
                $id=$this->id;
            }else if (isset($this->rowid)){
                $id=$this->rowid;
            }if(isset($this->ref)){
                $ref=$this->ref;
            }
        }
        
        if($id){
            $lien = '<a href="'.DOL_URL_ROOT.'/timesheet/timesheetuser.php?id='.$id.'&action=view">';
        }else if (!empty($ref)){
            $lien = '<a href="'.DOL_URL_ROOT.'/timesheet/timesheetuser.php?ref='.$ref.'&action=view">';
        }else{
            $lien =  "";
        }
        $lienfin=empty($lien)?'':'</a>';

    	$picto='timesheet@timesheet';
        
        if($ref){
            $label=$langs->trans("Show").': '.$ref;
        }else if($id){
            $label=$langs->trans("Show").': '.$id;
        }
    	if ($withpicto==1){ 
            $result.=($lien.img_object($label,$picto).$htmlcontent.$lienfin);
        }else if ($withpicto==2) {
            $result.=$lien.img_object($label,$picto).$lienfin;
        }else{  
            $result.=$lien.$htmlcontent.$lienfin;
        }
    	return $result;
    }    
/**
*	Return HTML to get other user
*
*	@param		string			$htmlcontent 		text to show
*	@param		int			$id                     Object ID
*	@param		string			$ref                    Object ref
*	@param		int			$withpicto		0=_No picto, 1=Includes the picto in the linkn, 2=Picto only
*	@return		string						String with URL
*/
function getHTMLGetOtherUserTs($idsList,$selected,$admin){
    global $langs;
    $form=new Form($this->db);
    $HTML='<form id="timesheetForm" name="OtherUser" action="?action=getOtherTs&wlm='.$this->whitelistmode.'" method="POST">'; 

    if(!$admin){
        //$HTML.='<select name="userid"> ';
        //$Names=getUsersName($idsList);
        //foreach ($Names as $subordiateId => $subordiateName){
        //    $HTML.='<option  value="'.$subordiateId.'" '.(($selected==$subordiateId)?'selected':'').'> '.$subordiateName.'</option>';    
       // }
       // $HTML.='</select> ';
        $HTML.=$form->select_dolusers($selected,'userid',0,null,0,$idsList);
    }else{

         $HTML.=$form->select_dolusers($selected,'userid');
    }
       $HTML.='<input type="submit" value="'.$langs->trans('Submit').'"/></form> ';

    return $HTML;
}


	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	function initAsSpecimen()
	{
		$this->id=0;
		
		$this->userId='';
		$this->date_start='';
		$this->date_end='';
		//$this->status='';
		//$this->sender='';
		//$this->recipient='';
		//$this->estimates='';
		//$this->tracking='';
		//$this->tracking_ids='';
		$this->date_creation='';
		//$this->date_modification='';
		$this->user_creation='';
		//$this->user_modification='';
		$this->task='';
		$this->note='';

		
	}
      
 
/******************************************************************************
 * 
 * AJAX methods
 * 
 ******************************************************************************/
 
/*
 * function to get the timesheet in XML format ( not up to date)
 * 
 *  @return     string                                         XML result containing the timesheet info
 */
        /*
function GetTimeSheetXML()
{
    global $langs,$conf;
    $xml.= "<timesheet dateStart=\"{$this->date_start}\" timestamp=\"{$this->timestamp}\" timetype=\"".$conf->global->TIMESHEET_TIME_TYPE."\"";
    $xml.=' nextWeek="'.date('Y\WW',strtotime($this->date_start."+3 days +1 week")).'" prevWeek="'.date('Y\WW',strtotime($this->date_start."+3 days -1 week")).'">';
    //error handling
    $xml.=getEventMessagesXML();
    //header
    $i=0;
    $xmlheaders=''; 
    foreach($this->headers as $header){
        if ($header=='Project'){
            $link=' link="'.DOL_URL_ROOT.'/projet/card.php?id="';
        }elseif ($header=='Tasks' || $header=='TaskParent'){
            $link=' link="'.DOL_URL_ROOT.'/projet/tasks/task.php?withproject=1&amp;id="';
        }elseif ($header=='Company'){
            $link=' link="'.DOL_URL_ROOT.'/societe/soc.php?socid="';
        }else{
            $link='';
        }
        $xmlheaders.= "<header col=\"{$i}\" name=\"{$header}\" {$link}>{$langs->transnoentitiesnoconv($header)}</header>";
        $i++;
    }
    $xml.= "<headers>{$xmlheaders}</headers>";
        //days
    $xmldays='';
    for ($i=0;$i<7;$i++)
    {
       $curDay=strtotime( $this->date_start.' +'.$i.' day');
       //$weekDays[$i]=date('d-m-Y',$curDay);
       $curDayTrad=$langs->trans(date('l',$curDay)).'  '.dol_mktime($curDay);
       $xmldays.="<day col=\"{$i}\">{$curDayTrad}</day>";
    }
    $xml.= "<days>{$xmldays}</days>";
        
        $tab=$this->fetchTaskTimesheet();
        $i=0;
        $xml.="<userTs userid=\"{$this->userId}\"  count=\"".count($this->taskTimesheet)."\" userName=\"{$this->userName}\" >";
        foreach ($this->taskTimesheet as $timesheet) {
            $row=new TimesheetTask($this->db);
             $row->unserialize($timesheet);
            $xml.= $row->getXML($this->date_start);//FIXME
            $i++;
        }  
        $xml.="</userTs>";
    //}
    $xml.="</timesheet>";
    return $xml;
}	*/
        /**
	 *	function that will send email to 
	 *
	 *	@return	void
	 */
     function sendApprovalReminders(){
                  global $langs;
            $sql = 'SELECT';
            $sql.=' COUNT(t.rowid) as nb,';
            $sql.=' u.email,';
            $sql.=' u.fk_user as approverid';
            $sql.= ' FROM '.MAIN_DB_PREFIX.'project_task_timesheet as t';
            $sql.= ' JOIN '.MAIN_DB_PREFIX.'user as u on t.fk_userid=u.rowid ';
            $sql.= ' WHERE (t.status='.SUBMITTED.' OR t.status='.UNDERAPPROVAL.' OR t.status='.CHALLENGED.') ';
            $sql.= '  AND t.recipient='.TEAM.' GROUP BY u.fk_user';
             dol_syslog(__METHOD__, LOG_DEBUG);
            $resql=$this->db->query($sql);
            
            if ($resql)
            {
                $num = $this->db->num_rows($resql);
                for( $i=0;$i<$num;$i++)
                {
                    $obj = $this->db->fetch_object($resql);
                    if ($obj)
                    {

                        $message=str_replace("__NB_TS__", $obj->nb, str_replace('\n', "\n",$langs->trans('YouHaveApprovalPendingMsg')));
                        //$message="Bonjour,\n\nVous avez __NB_TS__ feuilles de temps à approuver, veuillez vous connecter à Dolibarr pour les approuver.\n\nCordialement.\n\nVotre administrateur Dolibarr.";
                        $sendto=$obj->email;
                        $replyto=$obj->email;
                        $subject=$langs->transnoentities("YouHaveApprovalPending");
                        if(!empty($sendto) && $sendto!="NULL"){
                           require_once DOL_DOCUMENT_ROOT .'/core/class/CMailFile.class.php';
                           $mailfile = new CMailFile(
	                        $subject,
	                        $sendto,
	                        $replyto,
	                        $message,
                                $filename_list=array(), 
                                $mimetype_list=array(), 
                                $mimefilename_list=array(), 
                                $addr_cc, $addr_bcc=0,
                                $deliveryreceipt=0, 
                                $msgishtml=1 
                                  
	                    );
                           $mailfile->sendfile();
                        }
                        
                    }
                }

            }
            else
            {
                $error++;
                dol_print_error($db);
                $list= array();
            }
        }
        /**
	 *	function that will send email upon timesheet rejection
	 * @param       $user       objet   
	 *	@return	void
	 */
    function sendRejectedReminders($user){
        global $langs,$db,$dolibarr_main_url_root,$dolibarr_main_url_root_alt;
        $tsUser= new User($db);
        $tsUser->fetch($this->userId);

          $url=$dolibarr_main_url_root;
          if(strpos($dolibarr_main_url_root_alt,$_SERVER['PHP_SELF'])>0)
          {
               $url.=$dolibarr_main_url_root_alt;
          }
          $url.='/timesheet/timesheet.php?dateStart='.$this->date_start;
          $message=$langs->trans('YouHaveTimesheetRejectedMsg',date(' d',$this->date_start),$url);
          //$message="Bonjour,\n\nVous avez __NB_TS__ feuilles de temps à approuver, veuillez vous connecter à Dolibarr pour les approuver.\n\nCordialement.\n\nVotre administrateur Dolibarr.";
          $sendto=$tsUser->email;
          $replyto=$user->email;
          $subject=$langs->transnoentities("YouHaveTimesheetRejected");
          if(!empty($sendto) && $sendto!="NULL"){
             require_once DOL_DOCUMENT_ROOT .'/core/class/CMailFile.class.php';
             $mailfile = new CMailFile(
                  $subject,
                  $sendto,
                  $replyto,
                  $message,
                  $filename_list=array(), 
                  $mimetype_list=array(), 
                  $mimefilename_list=array(), 
                  $addr_cc, $addr_bcc=0,
                  $deliveryreceipt=0, 
                  $msgishtml=1 
              );
             $mailfile->sendfile();
          }        

    }
}
