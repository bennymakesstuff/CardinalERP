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
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once 'class/TimesheetUserTasks.class.php';


class TimesheetTask extends Task 
{
    public $element='Task_time_approval'; //!< Id that identify managed objects
    public $table_element='project_task_time_approval'; //!< Name of table without prefix where object is stored
    private $ProjectTitle="Not defined";
    public $tasklist;
    // private $fk_project;
    private $taskParentDesc;

    //company info
    private $companyName;
    private $companyId;

    //project info
	private $startDatePjct;
	private $stopDatePjct;
	private $pStatus;

    //whitelist
    private $hidden; // in the whitelist 
	//time
    // from db
    public $appId;
    public $planned_workload_approval;
	public $userId;
	public $date_start_approval=''; 
    public $date_end_approval;
	public $status;
	public $sender;
	public $recipient;
    public $note; 
    public $user_app;

    //basic DB logging
	public $date_creation='';
	public $date_modification='';
    //public $user_creation;
	public $user_modification;
    public $task_timesheet;
        

    //working variable

    public $duration;
    public $weekDays;
    public $userName;
    
    /**
     *  init the static variable
     *
     *  @return void          no return
     */    
    public function init() 
    {
        /* key used upon update of the TS via the TTA
         * canceled or planned shouldn't affect the TS status update
         * draft will be stange at this stage but could be retrieved automatically // FIXME
         * invoiced should appear when there is no Submitted, underapproval, Approved, challenged, rejected
         * Approved should apear when there is no Submitted, underapproval,  challenged, rejected left
         * Submitted should appear when no approval action is started: underapproval, Approved, challenged, rejected
         * 
         */
        global $conf;
        //self::$statusList=array(0=>'CANCELLED',1=>'PLANNED',2=>'DRAFT',3=>'INVOICED',4=>'APPROVED',5=>'SUBMITTED',6=>'UNDERAPPROVAL',7=>'CHALLENGED',8=>'REJECTED');
        //self::$roleList=array(0=> 'user',1=> 'team', 2=> 'project',3=>'customer',4=>'supplier',5=>'other');
        //self::$apflows=str_split($conf->global->TIMESHEET_APPROVAL_FLOWS); //remove the leading _
    
    }
    public function __construct($db,$taskId=0,$id=0) 
	{
		$this->db=$db;
		$this->id=$taskId;
        $this->appId=$id;
        $this->status=DRAFT;
        $this->sender=USER;
        $this->recipient=TEAM;
        $this->user_app= array('team'=>0 ,'project'=>0 ,'customer'=>0 ,'supplier'=>0 ,'other'=>0  );
	}

    /******************************************************************************
     * 
     * DB methods
     * 
     ******************************************************************************/

    /**
     *  CREATE object in the database
     *
     *  @param	int		$id    	Id object
     *  @param	string	$ref	Ref
     *  @return int          	<0 if KO, >0 if OK
     */
    function create($user, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (!empty($this->userId)) $this->userId=trim($this->userId);
		if (!empty($this->date_start)) $this->date_start=trim($this->date_start);
		if (!empty($this->date_end)) $this->date_end=trim($this->date_end);
		if (!empty($this->date_start_approval)) $this->date_start_approval=trim($this->date_start_approval);
		if (!empty($this->date_end_approval)) $this->date_end_approval=trim($this->date_end_approval);
		if (!empty($this->status)) $this->status=trim($this->status);
		if (!empty($this->sender)) $this->sender=trim($this->sender);
		if (!empty($this->recipient)) $this->recipient=trim($this->recipient);
		if (!empty($this->planned_workload_approval)) $this->planned_workload_approval=trim($this->planned_workload_approval);
		if (!empty($this->user_app['team'])) $this->user_app['team']=trim($this->user_app['team']);
		if (!empty($this->user_app['project'])) $this->user_app['project']=trim($this->user_app['project']);
		if (!empty($this->user_app['customer'])) $this->user_app['customer']=trim($this->user_app['customer']);
		if (!empty($this->user_app['supplier'])) $this->user_app['supplier']=trim($this->user_app['supplier']);
		if (!empty($this->user_app['other'])) $this->user_app['other']=trim($this->user_app['other']);
		//if (!empty($this->date_creation)) $this->date_creation=trim($this->date_creation);
		//if (!empty($this->date_modification)) $this->date_modification=trim($this->date_modification);
		if (!empty($this->user_creation)) $this->user_creation=trim($this->user_creation);
		if (!empty($this->user_modification)) $this->user_modification=trim($this->user_modification);
		if (!empty($this->id)) $this->id=trim($this->id);
		if (!empty($this->note)) $this->note=trim($this->note);
		if (!empty($this->task_timesheet)) $this->task_timesheet=trim($this->task_timesheet);

        $userId= (is_object($user)?$user->id:$user);

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element."(";
		
		$sql.= 'fk_userid,';
		$sql.= 'date_start,';
        $sql.= 'date_end,';
		$sql.= 'status,';
		$sql.= 'sender,';
		$sql.= 'recipient,';
		$sql.= 'planned_workload,';
        $sql.= 'fk_user_app_team,';
        $sql.= 'fk_user_app_project,';
        $sql.= 'fk_user_app_customer,';
        $sql.= 'fk_user_app_supplier,';
        $sql.= 'fk_user_app_other,';               
		$sql.= 'date_creation,';
        $sql.= 'date_modification,';
		$sql.= 'fk_user_creation,';
        $sql.= 'fk_projet_task,';
        $sql.= 'fk_project_task_timesheet,';
        $sql.= 'note';

		
        $sql.= ") VALUES (";
        
		$sql.=' '.(empty($this->userId)?'NULL':'\''.$this->userId.'\'').',';
		$sql.=' '.(empty($this->date_start_approval) || dol_strlen($this->date_start_approval)==0?'NULL':'\''.$this->db->idate($this->date_start_approval).'\'').',';
		$sql.=' '.(empty($this->date_end_approval) || dol_strlen($this->date_end_approval)==0?'NULL':'\''.$this->db->idate($this->date_end_approval).'\'').',';
		$sql.=' '.(empty($this->status)?'1':'\''.$this->status.'\'').',';
		$sql.=' '.(empty($this->sender)?USER:'\''.$this->sender.'\'').',';
		$sql.=' '.(empty($this->recipient)?TEAM:'\''.$this->recipient.'\'').',';
		$sql.=' '.(empty($this->planned_workload_approval)?'NULL':'\''.$this->planned_workload_approval.'\'').',';
		$sql.=' '.(empty($this->user_app['team'])?'NULL':'\''.$this->user_app['team'].'\'').',';
		$sql.=' '.(empty($this->user_app['project'])?'NULL':'\''.$this->user_app['project'].'\'').',';
		$sql.=' '.(empty($this->user_app['customer'])?'NULL':'\''.$this->user_app['customer'].'\'').',';
		$sql.=' '.(empty($this->user_app['supplier'])?'NULL':'\''.$this->user_app['supplier'].'\'').',';
		$sql.=' '.(empty($this->user_app['other'])?'NULL':'\''.$this->user_app['other'].'\'').',';
		$sql.=' NOW() ,';
        $sql.=' NOW() ,';
		$sql.=' \''.$userId.'\','; 
		$sql.=' '.(empty($this->id)?'NULL':'\''.$this->id.'\'').',';
		$sql.=' '.(empty($this->task_timesheet)?'NULL':'\''.$this->task_timesheet.'\'').',';
		$sql.=' '.(empty($this->note)?'NULL':'\''.$this->db->escape(dol_html_entity_decode($this->note, ENT_QUOTES)).'\'');
        
		$sql.= ")";

		$this->db->begin();

	   	dol_syslog(__METHOD__, LOG_DEBUG);
        $resql=$this->db->query($sql);
    	if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }

		if (! $error)
        {
            $this->appId = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);
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
                        return $this->appId;
		}
    }        
    /**
	 *  Load object in memory from database
	 *
	 *  @param	int		$id					Id object
	 *  @param	int		$ref				ref object
	 *  @param	int		$loadparentdata		Also load parent data
	 *  @return int 		        		<0 if KO, 0 if not found, >0 if OK
     */
    function fetch($id, $ref='', $loadparentdata=1)
    {
        global $langs;
        $sql = "SELECT";
        $sql.= " t.rowid,";
		
        $sql.=' t.fk_userid,';
        $sql.=' t.date_start,';
        $sql.=' t.date_end,';
        $sql.=' t.status,';
        $sql.=' t.sender,';
        $sql.=' t.recipient,';
		$sql.=' t.planned_workload,';
        $sql.= 't.fk_user_app_team,';
        $sql.= 't.fk_user_app_project,';
        $sql.= 't.fk_user_app_customer,';
        $sql.= 't.fk_user_app_supplier,';
        $sql.= 't.fk_user_app_other,';
		$sql.=' t.date_creation,';
		$sql.=' t.date_modification,';
		$sql.=' t.fk_user_creation,';
		$sql.=' t.fk_user_modification,';
		$sql.=' t.fk_projet_task,';
		$sql.=' t.fk_project_task_timesheet,';
		$sql.=' t.note';

		
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(__METHOD__);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->appId    = $obj->rowid;
                $this->userId = $obj->fk_userid;
                $this->date_start_approval = $this->db->jdate($obj->date_start);
                $this->date_end_approval = $this->db->jdate($obj->date_end);
                $this->status = $obj->status;
                $this->sender = $obj->sender;
                $this->recipient = $obj->recipient;
                $this->planned_workload_approval = $obj->planned_workload;
                $this->user_app['team'] = $obj->fk_user_app_team;
                $this->user_app['other'] = $obj->fk_user_app_other;
                $this->user_app['supplier'] = $obj->fk_user_app_supplier;
                $this->user_app['customer'] = $obj->fk_user_app_customer;
                $this->user_app['project'] = $obj->fk_user_app_project;               
                $this->date_creation = $this->db->jdate($obj->date_creation);
                $this->date_modification = $this->db->jdate($obj->date_modification);
                $this->user_creation = $obj->fk_user_creation;
                $this->user_modification = $obj->fk_user_modification;
                $this->id = $obj->fk_projet_task;
                $this->task_timesheet = $obj->fk_project_task_timesheet;
                $this->note  = $obj->note;

                
            }
            $this->db->free($resql);
            $this->ref=$this->date_start_approval.'_'.$this->userId.'_'.$this->id;
            $this->whitelistmode=2; // no impact
            if($loadparentdata)$this->getTaskInfo();
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
		$sql.=' t.sender,';
		$sql.=' t.recipient,';
		$sql.=' t.planned_workload,';
        $sql.= 't.fk_user_app_team,';
        $sql.= 't.fk_user_app_project,';
        $sql.= 't.fk_user_app_customer,';
        $sql.= 't.fk_user_app_supplier,';
        $sql.= 't.fk_user_app_other,';
		$sql.=' t.date_creation,';
		$sql.=' t.date_modification,';
		$sql.=' t.fk_user_creation,';
		$sql.=' t.fk_user_modification,';
		$sql.=' t.fk_projet_task,';
		$sql.=' t.fk_project_task_timesheet,';
		$sql.=' t.note';

		
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element." as t";

        $sql.= " WHERE t.date_start = '".$this->db->idate($this->date_start_approval)."'";
        $sql.= " AND t.fk_userid = '".$this->userId."'";

        //$sql.= " AND t.rowid = ".$id;

    	dol_syslog(get_class($this)."::fetchByWeek");
        $resql=$this->db->query($sql);
        if ($resql)
        {
            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->appId    = $obj->rowid;
                
                $this->userId = $obj->fk_userid;
                $this->date_start_approval = $this->db->jdate($obj->date_start);
                $this->date_end_approval = $this->db->jdate($obj->date_end);
                $this->status = $obj->status;
                $this->sender = $obj->sender;
                $this->recipient = $obj->recipient;
                $this->user_app['team'] = $obj->fk_user_app_team;
                $this->user_app['other'] = $obj->fk_user_app_other;
                $this->user_app['supplier'] = $obj->fk_user_app_supplier;
                $this->user_app['customer'] = $obj->fk_user_app_customer;
                $this->user_app['project'] = $obj->fk_user_app_project;  
                $this->date_creation = $this->db->jdate($obj->date_creation);
                $this->date_modification = $this->db->jdate($obj->date_modification);
                $this->user_creation = $obj->fk_user_creation;
                $this->user_modification = $obj->fk_user_modification;
                $this->id = $obj->fk_projet_task;
                $this->task_timesheet = $obj->fk_project_task_timesheet;
                $this->note  = $obj->note;
               
            } else {
                unset($this->status) ;
                unset($this->sender) ;
                unset($this->recipient) ;
                unset($this->planned_workload_approvals) ;
                unset($this->tracking) ;
                unset($this->tracking_ids) ;
                unset($this->date_modification );
                unset($this->user_app['team ']);
                unset($this->user_app['project'] );
                unset($this->user_app['customer'] );
                unset($this->user_app['supplier'] );
                unset($this->user_app['other'] );
                // unset($this->date_start ); 
                // unset($this->date_end );
                // unset($this->date_start_approval );
                // unset($this->date_end_approval );
                unset($this->user_creation );
                unset($this->user_modification );
                unset($this->id );
                unset($this->note );
                unset($this->task_timesheet );
                unset($this->date_creation  );
                
                //$this->date_end= getEndWeek($this->date_start_approval);
                $this->create($this->user);
                $this->fetch($this->appId);
            }
            $this->db->free($resql);
            $this->getTaskInfo();
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
    function update($user = NULL, $notrigger=0)
    {
    	global $conf, $langs;
		$error=0;

		// Clean parameters
        
		if (!empty($this->userId)) $this->userId=trim($this->userId);
		if (!empty($this->date_start_approval)) $this->date_start_approval=trim($this->date_start_approval);
		if (!empty($this->date_end_approval)) $this->date_end_approval=trim($this->date_end_approval);
		if (!empty($this->status)) $this->status=trim($this->status);
		if (!empty($this->sender)) $this->sender=trim($this->sender);
		if (!empty($this->recipient)) $this->recipient=trim($this->recipient);
		if (!empty($this->planned_workload_approval)) $this->planned_workload_approval=trim($this->planned_workload_approval);
		if (!empty($this->user_app['team'])) $this->user_app['team']=trim($this->user_app['team']);
		if (!empty($this->user_app['project'])) $this->user_app['project']=trim($this->user_app['project']);
		if (!empty($this->user_app['customer'])) $this->user_app['customer']=trim($this->user_app['customer']);
		if (!empty($this->user_app['supplier'])) $this->user_app['supplier']=trim($this->user_app['supplier']);
		if (!empty($this->user_app['other'])) $this->user_app['other']=trim($this->user_app['other']);
		if (!empty($this->date_creation)) $this->date_creation=trim($this->date_creation);
		if (!empty($this->date_modification)) $this->date_modification=trim($this->date_modification);
		if (!empty($this->user_creation)) $this->user_creation=trim($this->user_creation);
		if (!empty($this->user_modification)) $this->user_modification=trim($this->user_modification);
		if (!empty($this->id)) $this->id=trim($this->id);
		if (!empty($this->task_timesheet)) $this->task_timesheet=trim($this->task_timesheet);
		if (!empty($this->note)) $this->note=trim($this->note);
        $userId= (is_object($user)?$user->id:$user);
        

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element." SET";
        
		$sql.=' fk_userid='.(empty($this->userId) ? 'null':'\''.$this->userId.'\'').',';
		$sql.=' date_start='.(dol_strlen($this->date_start_approval)!=0 ? '\''.$this->db->idate($this->date_start_approval).'\'':'null').',';
		$sql.=' date_end='.(dol_strlen($this->date_end_approval)!=0 ? '\''.$this->db->idate($this->date_end_approval).'\'':'null').',';
		$sql.=' status='.(empty($this->status)? 'null':'\''.$this->status.'\'').',';
		$sql.=' sender='.(empty($this->sender) ? 'null':'\''.$this->sender.'\'').',';
		$sql.=' recipient='.(empty($this->recipient) ? 'null':'\''.$this->recipient.'\'').',';
		$sql.=' planned_workload='.(empty($this->planned_workload_approval) ? 'null':'\''.$this->planned_workload_approval.'\'').',';
		$sql.=' fk_user_app_team='.(empty($this->user_app['team']) ? 'NULL':'\''.$this->user_app['team'].'\'').',';
		$sql.=' fk_user_app_project='.(empty($this->user_app['project']) ? 'NULL':'\''.$this->user_app['project'].'\'').',';
		$sql.=' fk_user_app_customer='.(empty($this->user_app['customer']) ? 'NULL':'\''.$this->user_app['customer'].'\'').',';
		$sql.=' fk_user_app_supplier='.(empty($this->user_app['supplier']) ? 'NULL':'\''.$this->user_app['supplier'].'\'').',';
		$sql.=' fk_user_app_other='.(empty($this->user_app['other']) ? 'NULL':'\''.$this->user_app['other'].'\'').',';
		$sql.=' date_modification=NOW() ,';
		$sql.=' fk_user_modification=\''.$userId.'\',';
		$sql.=' fk_projet_task='.(empty($this->id) ? 'null':'\''.$this->id.'\'').',';
		$sql.=' fk_project_task_timesheet='.(empty($this->task_timesheet) ? 'null':'\''.$this->task_timesheet.'\'').',';
		$sql.=' note=\''.$this->db->escape(dol_html_entity_decode($this->note, ENT_QUOTES)).'\'';

        
        $sql.= " WHERE rowid=".$this->appId;

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
            $sql.= " WHERE rowid=".$this->appId;

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

        
        
        
        
        
        
        
    /******************************************************************************
     * 
     * object methods
     * 
     ******************************************************************************/        
 
    /*
     * update the project task time Item
     * 
     *  @param      int               $status  status
     *  @return     int               <0 if KO, Id of created object if OK
     */
    Public function updateTaskTime($status){
        $error=0;
        if($status<0 || $status>STATUSMAX) return -1; // role not valide      
        //Update the the fk_tta in the project task time 
        $idList=array(); 
        if(!is_array($this->tasklist)) $this->getActuals ($this->date_start_approval, $this->date_end_approval, $this->userId);
        if(is_array($this->tasklist))foreach($this->tasklist as $item){
            if($item['id']!='')$idList[]=$item['id'];
        }
        $ids=implode(',',$idList);
        $sql='UPDATE '.MAIN_DB_PREFIX.'projet_task_time SET fk_task_time_approval=\'';
        $sql.=$this->appId.'\', status=\''.$status.'\' WHERE rowid in ('.$ids.')';
        // SQL start
        dol_syslog(__METHOD__);
        $this->db->begin();
	    $resql = $this->db->query($sql);

    	if (! $resql) { 
            $error++; 
            $this->errors[]="Error ".$this->db->lasterror();  
        }
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

        return 1;
    }            
        
        
        
        
        
    /*
     * Get the task information from the dB
     * 
     *  @return     int               <0 if KO, Id of created object if OK
     */
    public function getTaskInfo()
    {global $conf;
        $Company=strpos($conf->global->TIMESHEET_HEADERS, 'Company')!==FALSE;
        $taskParent=strpos($conf->global->TIMESHEET_HEADERS, 'TaskParent')!==FALSE;
        $sql ='SELECT p.rowid,p.datee as pdatee, p.fk_statut as pstatus, p.dateo as pdateo, pt.dateo,pt.datee, pt.planned_workload, pt.duration_effective';
        if($conf->global->TIMESHEET_HIDE_REF==1){
            $sql .= ',p.title as title, pt.label as label,pt.planned_workload';
            if($taskParent) $sql .= ',pt.fk_task_parent,ptp.label as taskParentLabel';	        	
        } else {
            $sql .= ",CONCAT(p.ref,' - ',p.title) as title";
            $sql .= ",CONCAT(pt.ref,' - ',pt.label) as label";
            if($taskParent) $sql .= ",pt.fk_task_parent,CONCAT(ptp.ref,' - ',ptp.label) as taskParentLabel";	
        }
        if($Company)$sql .= ',p.fk_soc as companyId,s.nom as companyName';

        $sql .=" FROM ".MAIN_DB_PREFIX."projet_task AS pt";
        $sql .=" JOIN ".MAIN_DB_PREFIX."projet as p";
        $sql .=" ON pt.fk_projet=p.rowid";
        if($taskParent){
            $sql .=" LEFT JOIN ".MAIN_DB_PREFIX."projet_task as ptp";
            $sql .=" ON pt.fk_task_parent=ptp.rowid";
        }
        if($Company){
            $sql .=" LEFT JOIN ".MAIN_DB_PREFIX."societe as s";
            $sql .=" ON p.fk_soc=s.rowid";
        }
        $sql .=" WHERE pt.rowid ='".$this->id."'";
        #$sql .= "WHERE pt.rowid ='1'";
        dol_syslog(__METHOD__, LOG_DEBUG);

        $resql=$this->db->query($sql);
        if ($resql)
        {

            if ($this->db->num_rows($resql))
            {
                $obj = $this->db->fetch_object($resql);

                $this->description			= $obj->label;
                $this->fk_project           = $obj->rowid;
                $this->ProjectTitle			= $obj->title;
                #$this->date_start			= strtotime($obj->dateo.' +0 day');
                #$this->date_end			= strtotime($obj->datee.' +0 day');
                $this->date_start			= $this->db->jdate($obj->dateo);
                $this->date_end			    = $this->db->jdate($obj->datee);
                $this->duration_effective   = $obj->duration_effective; // total of time spent on this task
                
                $this->planned_workload     = $obj->planned_workload;
                $this->startDatePjct=$this->db->jdate($obj->pdateo);
                $this->stopDatePjct=$this->db->jdate($obj->pdatee);
                $this->pStatus=$obj->pstatus;
                    
                if($taskParent){
                    $this->fk_projet_task_parent        = $obj->fk_projet_task_parent;
                    $this->taskParentDesc               =$obj->taskParentLabel;
                }
                if($Company){
                    $this->companyName                  =$obj->companyName;
                    $this->companyId                    =$obj->companyId;
                }
            }
            $this->db->free($resql);

            return 1;
        }
        else
        {
            $this->error="Error ".$this->db->lasterror();
            dol_syslog(__METHOD__.$this->error, LOG_ERR);

            return -1;
        }	
    }

    /*
     *  FUNCTION TO GET THE ACTUALS FOR A WEEK AND AN USER
     *  @param    Datetime              timeStart       start date to look for actuals
     *  @param    Datetime              $timeEnd        end date to look for actuals
     *  @param     int              	$userId         used in the form processing
     *  @param    string              	$tasktimeIds    limit the seach if defined
     *  @return     int                                 success (1) / failure (-1)
     */
    public function getActuals($timeStart=0,$timeEnd=0,$userid=0)
    {
        // change the time to take all the TS per day
        //$timeStart=floor($timeStart/SECINDAY)*SECINDAY;
        //$timeEnd=ceil($timeEnd/SECINDAY)*SECINDAY;
        
        if($timeStart==0) $timeStart=$this->date_start_approval;
        if($timeEnd==0) $timeEnd=$this->date_end_approval;
        if($userid==0) $userid=$this->userId;
        $dayelapsed=getDayInterval($timeStart,$timeEnd);
        if($dayelapsed<1)return -1;
        $sql = "SELECT ptt.rowid, ptt.task_duration, ptt.task_date,ptt.note";	
        $sql .= " FROM ".MAIN_DB_PREFIX."projet_task_time AS ptt";
        $sql .= " WHERE ";
        // FIXME If status above

        // if(in_array($this->status,array_slice(self::$statusList, 3,4))){
        if(in_array($this->status, array(SUBMITTED,UNDERAPPROVAL,APPROVED,CHALLENGED,INVOICED))){
            $sql.=' ptt.fk_task_time_approval=\''.$this->appId.'\'';
        } else {
            $sql.=" ptt.fk_task='".$this->id."' ";
            $sql .= " AND (ptt.fk_user='".$userid."') ";
            $sql .= " AND (ptt.task_date>='".$this->db->idate($timeStart)."') ";
            $sql .= " AND (ptt.task_date<'".$this->db->idate($timeEnd)."')";
        }  
        dol_syslog(__METHOD__, LOG_DEBUG);
		for($i=0;$i<$dayelapsed;$i++){
			
			$this->tasklist[$i]=array('id'=>0,'duration'=>0,'date'=>$timeStart+SECINDAY*$i+SECINDAY/4);
		}

        $resql=$this->db->query($sql);
        if ($resql)
        {

            $num = $this->db->num_rows($resql);
            $i = 0;
            // Loop on each record found, so each couple (project id, task id)
                while ($i < $num)
            {
                $error=0;
                $obj = $this->db->fetch_object($resql);
                $dateCur=$this->db->jdate($obj->task_date);
                $day=getDayInterval($timeStart,$dateCur);

                $this->tasklist[$day]=array('id'=>$obj->rowid,'date'=>$dateCur,'duration'=>$obj->task_duration,'note'=>$obj->note);
                $i++;
            }
            
            $this->db->free($resql);
            return 1;
         }
        else
        {
            $this->error="Error ".$this->db->lasterror();
            dol_syslog(__METHOD__.$this->error, LOG_ERR);

            return -1;
        }
    }	   
   

    /*
     * function to form a HTMLform line for this timesheet
     * 
     *  @param     int              	$line number         used in the form processing
     *  @param    string              	$usUserId            id that will be used for the total
     *  @param    int                   $openOveride         0- no effect; 1 - force edition; (-1) - block edition
     *  @return     string                                   HTML result containing the timesheet info
     */
    public function getFormLine( $lineNumber,$headers,$tsUserId=0,$openOveride=0)
    {
        global $langs,$conf,$statusColor;
        // change the time to take all the TS per day

        $dayelapsed=getDayInterval($this->date_start_approval,$this->date_end_approval);
  
   
        if(($dayelapsed<1)||empty($headers))
           return '<tr>ERROR: wrong parameters for getFormLine'.$dayelapsed.'|'.$headers.'</tr>';
        if($tsUserId!=0)$this->userId=$tsUserId;
        $timetype=$conf->global->TIMESHEET_TIME_TYPE;
        $dayshours=$conf->global->TIMESHEET_DAY_DURATION;
        $hidezeros=$conf->global->TIMESHEET_HIDE_ZEROS;
        $hidden=false;
        $status=$this->status;

        //if(($whitelistemode==0 && !$this->listed)||($whitelistemode==1 && $this->listed))$hidden=true;
        //$linestyle=(($hidden)?'display:none;':'');
        $Class='oddeven '.(($this->listed)?'timesheet_whitelist':'timesheet_blacklist').' timesheet_line ';
        $htmltail='';
        $linestyle='';
        if(($this->pStatus == "2")){
            $linestyle.='background:#'.TIMESHEET_BC_FREEZED.';';
        } else if($statusColor[$this->status]!='' &&  $statusColor[$this->status]!='FFFFFF') {
            $linestyle.='background:#'.$statusColor[$this->status].';';// --FIXME
        }
        /*
        * Open task ?
        */
        if($status==INVOICED)$openOveride=-1; // once invoice it should not change
        $isOpenSatus=($openOveride==1) || in_array($status ,array(DRAFT ,CANCELLED,REJECTED,PLANNED));
        if($openOveride==-1)$isOpenSatus=false;
        $opendays=str_split($conf->global->TIMESHEET_OPEN_DAYS);
    
        /*
         * info section
         */
        $html= '<tr class="'.$Class.'" '.((!empty($linestyle))?'style="'.$linestyle.'"':'');
        if(!empty($this->note))$html.=' title="'.htmlentities($this->note).'"';
        $html.=  '>'."\n"; 
        //title section
        foreach ($headers as $key => $title){
            $htmlTitle='';
            switch($title){
                case 'Project':
                    if(version_compare(DOL_VERSION,"3.7")>=0){
                        //if(file_exists("../projet/card.php")||file_exists("../../projet/card.php")){
                        $htmlTitle.='<a href="'.DOL_URL_ROOT.'/projet/card.php?mainmenu=project&id='.$this->fk_project.'">'.$this->ProjectTitle.'</a>';
                    } else {
                        $htmlTitle.='<a href="'.DOL_URL_ROOT.'/projet/fiche.php?mainmenu=project&id='.$this->fk_project.'">'.$this->ProjectTitle.'</a>';
                    }
                    break;
                case 'TaskParent':
                    $htmlTitle.='<a href="'.DOL_URL_ROOT.'/projet/tasks/task.php?mainmenu=project&id='.$this->fk_projet_task_parent.'&withproject='.$this->fk_project.'">'.$this->taskParentDesc.'</a>';
                    break;
                case 'Tasks':
                    if($isOpenSatus && $conf->global->TIMESHEET_WHITELIST==1)$htmlTitle.='<img id = "'.$this->listed.'" src="img/fav_'.(($this->listed>0)?'on':'off').'.png" onClick=favOnOff(event,'.$this->fk_project.','.$this->id.') style="cursor:pointer;">  ';
                    $htmlTitle.='<a href="'.DOL_URL_ROOT.'/projet/tasks/task.php?mainmenu=project&id='.$this->id.'&withproject='.$this->fk_project.'"> '.$this->description.'</a>';
                    break;
                case 'DateStart':
                    $htmlTitle.=$this->date_start?dol_print_date($this->date_start,'day'):'';
                    break;
                case 'DateEnd':
                    $htmlTitle.=$this->date_end?dol_print_date($this->date_end,'day'):'';
                    break;
                case 'Company':
                    $htmlTitle.='<a href="'.DOL_URL_ROOT.'/societe/soc.php?mainmenu=companies&socid='.$this->companyId.'">'.$this->companyName.'</a>';
                    break;
                case 'Progress':
                    $htmlTitle .=$this->parseTaskTime($this->duration_effective).'/';
                    if($this->planned_workload>0)
                    {
                        $htmlTitle .= $this->parseTaskTime($this->planned_workload ).'('.floor($this->duration_effective/$this->planned_workload*100).'%)';
                    } else {
                        $htmlTitle .= "-:--(-%)";
                    }
                    if($this->planned_workload_approval) // show the time planned for the week
                    {
                        $htmlTitle .= '('.$this->parseTaskTime($this->planned_workload_approval).')';
                    }
                    break;
                case 'User':
                    $userName=getUsersName($this->userId);
                    $htmlTitle .=  $userName[$this->userId];
                    break;
                case 'Total':
                    $htmlTitle='<div class="lineTotal" id="'.$this->userId.'_'.$this->id.'">&nbsp;</div>';
                    break;
                case 'Approval':
                        $htmlTitle .= "<input type='text' style='border: none;' class = 'approval_switch'";
                        $htmlTitle .=' name="approval['.$this->appId.']" ';
                        $htmlTitle .=' id="task_'.$this->userId.'_'.$this->appId.'_approval" ';
                        $htmlTitle .= " onfocus='this.blur()' readonly='true' size='1' value='&#x2753;' onclick='tristate_Marks(this)' />\n";
                        break;
                case 'Note':
                    $htmlTitle .=img_object('Note', 'generic', ' onClick="openNote(\'noteTask_'.$this->userId.'_'.$this->id.'\')"');
                    $html .='<div class="modal" id="noteTask_'.$this->userId.'_'.$this->id.'" >';
                    $html .='<div class="modal-content">';
                    $html .='<span class="close " onclick="closeNotes()">&times;</span>';
                    $html.='<a align="left">'.$langs->trans('Note').' ('.$this->ProjectTitle.', '.$this->description.")".'</a></br>';                    
                    $html.= '<textarea class="flat"  rows="3" style="width:350px;top:10px"';
                    $html.= 'name="task['.$this->userId.']['.$this->id.']['.$dayCur.'][1]" ';
                    $html .= '>'.$this->tasklist[$dayCur]['note'].'</textarea>';
                    $html .='</div></div>';  
 
                    
                    
               /*     
                    $htmltail='<tr class="timesheet_note" id="noteTask_'.$this->userId.'_'.$this->id.'" style="display:none;"><td colspan="1">';
                    $htmltail.='<a>'.$langs->trans('Note').'</a></td><td colspan="100%">';
                    //if($isOpenSatus){
                        $htmltail.= '<textarea class="flat"  rows="2" cols="100"';
                        if($this->appId!=0){
                            $htmltail .= 'name="note['.$this->appId.']" ';
                        } else {
                            $htmltail .= 'name="task['.$this->userId.']['.$this->id.'][Note]" ';
                        }
                        $htmltail .= '>'.$this->note.'</textarea>';
                    //}else if(!empty($this->note)){
                    //    $htmltail.=$this->note;                
                    //}
                    $htmltail.="</td></tr>\n";*/
            }

            $html.='<td align="left" '.((count($headers)==1)?'colspan="2" ':'').'>'.$htmlTitle."</td>\n";
                
        }

        // day section


        for($dayCur=0;$dayCur<$dayelapsed;$dayCur++)
        {

            //$shrinkedStyle=(!$opendays[$dayCur+1] && $shrinked)?'display:none;':'';
            $today= $this->date_start_approval+SECINDAY*$dayCur +SECINDAY/4;
            # to avoid editing if the task is closed 
            $dayWorkLoadSec=isset($this->tasklist[$dayCur])?$this->tasklist[$dayCur]['duration']:0;
            $dayWorkLoad=formatTime($dayWorkLoadSec,-1);
            /*if ($timetype=="days")
            {
                $dayWorkLoad=$dayWorkLoadSec/3600/$dayshours;
            }else {
                $dayWorkLoad=date('H:i',mktime(0,0,$dayWorkLoadSec));
            }*/
            $startDates=($this->date_start>$this->startDatePjct )?$this->date_start:$this->startDatePjct;
            $stopDates=(($this->date_end<$this->stopDatePjct && $this->date_end!=0) || $this->stopDatePjct==0)?$this->date_end:$this->stopDatePjct;
            if($isOpenSatus){
                $isOpen=$isOpenSatus && (($startDates==0) || ($startDates < $today +SECINDAY/4));
                $isOpen= $isOpen && (($stopDates==0) ||($stopDates >= $today ));

                $isOpen= $isOpen && ($this->pStatus < "2") ;
                $isOpen= $isOpen  && $opendays[date("N",$today)];

                $bkcolor='';

                if($isOpen){
                    $bkcolor='background:#'.$statusColor[$this->status];
                    if($dayWorkLoadSec!=0 && $this->status==DRAFT )$bkcolor='background:#'.TIMESHEET_BC_VALUE;
                    
                } else {
                    $bkcolor='background:#'.TIMESHEET_BC_FREEZED;
                } 
                $html .= "<td >\n"; 
                // add note popup
                if($isOpen && $conf->global->TIMESHEET_SHOW_TIMESPENT_NOTE){
                $html .=img_object('Note', 'generic', ' style="display:inline-block;float:right;" onClick="openNote(\'note_'.$this->userId.'_'.$this->id.'_'.$dayCur.'\')"');
                //note code
                $html .='<div class="modal" id="note_'.$this->userId.'_'.$this->id.'_'.$dayCur.'" >';
                $html .='<div class="modal-content">';
                $html .='<span class="close " onclick="closeNotes()">&times;</span>';
                $html.='<a align="left">'.$langs->trans('Note').' ('.$this->ProjectTitle.', '.$this->description.', '.dol_print_date($today,'day').")".'</a></br>';                    
                $html.= '<textarea class="flat"  rows="3" style="width:350px;top:10px"';
                $html.= 'name="task['.$this->userId.']['.$this->id.']['.$dayCur.'][1]" ';
                $html .= '>'.$this->tasklist[$dayCur]['note'].'</textarea>';
                $html .='</div></div>';  
                }
                //add input day
                $html .= '<div style="display:inline-block;"><input  type="text" '.(($isOpen)?'':'readonly').' class="column_'.$this->userId.'_'.$dayCur.' user_'.$this->userId.' line_'.$this->userId.'_'.$this->id.'" ';
                // $html .= 'name="task['.$this->userId.']['.$this->id.']['.$dayCur.']" '; if one whant multiple ts per validation
                $html .= ' name="task['.$this->userId.']['.$this->id.']['.$dayCur.'][0]" ';
                $html .=' value="'.((($hidezeros==1) && ($dayWorkLoadSec==0))?"":$dayWorkLoad);
                $html .='" maxlength="5" size="2" style="'.$bkcolor.'" ';
                $html .='onkeypress="return regexEvent(this,event,\'timeChar\')" ';
                $html .= 'onblur="validateTime(this,\''.$this->userId.'_'.$dayCur.'\')" /></div>';

                 //end note code       
                $html .= "</td>\n"; 
            } else {
                //$bkcolor='background:#'.(($dayWorkLoadSec!=0)?(self::$statusColor[$this->status]):'#FFFFFF');
                //$html .= ' <td style="'.$bkcolor.'"><a class="time4day['.$this->userId.']['.$dayCur.']"';
                $html .= ' <td ><a class="column_'.$this->userId.'_'.$dayCur.' user_'.$this->userId.' line_'.$this->userId.'_'.$this->id.'"';
                //$html .= ' name="task['.$this->userId.']['.$this->id.']['.$dayCur.']" ';if one whant multiple ts per validation
                $html .= ' name="task['.$this->id.']['.$dayCur.']" ';
                $html .= ' style="width: 90%;"';
                $html .=' >'.((($hidezeros==1) && ($dayWorkLoadSec==0))?"":$dayWorkLoad);
                $html .='</a> ';
                $html .= "</td>\n"; 


            }
        }
        $html .= "</tr>\n";
        return $html.$htmltail;

    }	

    /*
    * function to form a XML for this timesheet
    * 
    *  @param    string              	$startDate            year week like 2015W09
    *  @return     string                                         XML result containing the timesheet info
    *//*
    public function getXML( $startDate)
    {
        $timetype=$conf->global->TIMESHEET_TIME_TYPE;
        $dayshours=$conf->global->TIMESHEET_DAY_DURATION;
        $hidezeros=$conf->global->TIMESHEET_HIDE_ZEROS;
        $xml= "<task id=\"{$this->id}\" >";
        //title section
        $xml.="<Tasks id=\"{$this->id}\">{$this->description} </Tasks>";
        $xml.="<Project id=\"{$this->fk_project}\">{$this->ProjectTitle} </Project>";
        $xml.="<TaskParent id=\"{$this->fk_projet_task_parent}\">{$this->taskParentDesc} </TaskParent>";
        //$xml.="<task id=\"{$this->id}\" name=\"{$this->description}\">\n";
        $xml.="<DateStart unix=\"$this->date_start\">";
        if($this->date_start)
            $xml.=dol_mktime($this->date_start);
        $xml.=" </DateStart>";
        $xml.="<DateEnd unix=\"$this->date_end\">";
        if($this->date_end)
            $xml.=dol_mktime($this->date_end);
        $xml.=" </DateEnd>";
        $xml.="<Company id=\"{$this->companyId}\">{$this->companyName} </Company>";
        $xml.="<TaskProgress id=\"{$this->companyId}\">";
        if($this->planned_workload)
        {
            $xml .= $this->parseTaskTime($this->planned_workload).'('.floor($this->duration_effective/$this->planned_workload*100).'%)';
        } else {
            $xml .= "-:--(-%)";
        }
        $xml.="</TaskProgress>";


        // day section
        //foreach ($this->weekWorkLoad as $dayOfWeek => $dayWorkLoadSec)
        for($dayOfWeek=0;$dayOfWeek<7;$dayOfWeek++)
        {
                $today= strtotime($startDate.' +'.($dayOfWeek).' day  ');
                # to avoid editing if the task is closed 
                $dayWorkLoadSec=isset($this->tasklist[$dayOfWeek])?$this->tasklist[$dayOfWeek]['duration']:0;
                # to avoid editing if the task is closed 
                if($hidezeros==1 && $dayWorkLoadSec==0){
                    $dayWorkLoad=' ';
                }else if ($timetype=="days")
                {
                    $dayWorkLoad=$dayWorkLoadSec/3600/$dayshours;
                }else {
                    $dayWorkLoad=date('H:i',mktime(0,0,$dayWorkLoadSec));
                }
                $open='0';
                if((empty($this->date_start) || ($this->date_start <= $today +86399)) && (empty($this->date_end) ||($this->date_end >= $today )))
                {             
                    $open='1';                   
                }
                $xml .= "<day col=\"{$dayOfWeek}\" open=\"{$open}\">{$dayWorkLoad}</day>";

        } 
        $xml.="</task>"; 
        return $xml;
        //return utf8_encode($xml);

    }
    */
    /*
    * function to save a time sheet as a string
    */
    function serialize(){
        $arRet=array();
        
        $arRet['id']=$this->id; //task id
        $arRet['listed']=$this->listed; //task id
        $arRet['description']=$this->description; //task id
        $arRet['appId']=$this->appId; // Task_time_approval id
        $arRet['tasklist']=$this->tasklist;
        $arRet['userId']=$this->userId; // user id booking the time
        $arRet['note']=$this->note;			
        $arRet['fk_project']=$this->fk_project ;
        $arRet['ProjectTitle']=$this->ProjectTitle;
        $arRet['date_start']=$this->date_start;			
        $arRet['date_end']=$this->date_end	;    
        $arRet['date_start_approval']=$this->date_start_approval;			
        $arRet['date_end_approval']=$this->date_end_approval	;		
        $arRet['duration_effective']=$this->duration_effective ;   
        $arRet['planned_workload']=$this->planned_workload ;
        $arRet['fk_projet_task_parent']=$this->fk_projet_task_parent ;
        $arRet['taskParentDesc']=$this->taskParentDesc ;
        $arRet['companyName']=$this->companyName  ;
        $arRet['companyId']= $this->companyId;
        $arRet['pSatus']= $this->pStatus;
        $arRet['status']= $this->status; 
        $arRet['recipient']= $this->recipient; 
        $arRet['sender']= $this->sender; 
        $arRet['task_timesheet']= $this->task_timesheet; 

                        
        return serialize($arRet);
    }
    /*
     * function to load a time sheet as a string
     */
    function unserialize($str){
        $arRet=unserialize($str);
        $this->id=$arRet['id'];
        $this->listed=$arRet['listed'];
        $this->description=$arRet['description'];
        $this->appId=$arRet['appId'];
        $this->userId=$arRet['userId'];
        $this->tasklist=$arRet['tasklist'];
        $this->note=$arRet['note'];			
        $this->fk_project=$arRet['fk_project'] ;
        $this->ProjectTitle=$arRet['ProjectTitle'];
        $this->date_start_approval=$arRet['date_start_approval'];			
        $this->date_end_approval=$arRet['date_end_approval']	;		
        $this->date_start=$arRet['date_start'];			
        $this->date_end=$arRet['date_end']	;		
        $this->duration_effective=$arRet['duration_effective'] ;   
        $this->planned_workload=$arRet['planned_workload'] ;
        $this->fk_projet_task_parent=$arRet['fk_projet_task_parent'] ;
        $this->taskParentDesc=$arRet['taskParentDesc'] ;
        $this->companyName=$arRet['companyName']  ;
        $this->companyId=$arRet['companyId'];
        $this->status=$arRet['status'];
        $this->sender=$arRet['sender'];
        $this->recipient=$arRet['recipient'];
        $this->pStatus=$arRet['pSatus'];
        $this->task_timesheet=$arRet['task_timesheet'];
    }
 
    public function getTaskTab()
    {
        return $this->tasklist;
    }
    
    public function updateTimeUsed() 
    {
        $this->db->begin();
        $error=0;
        $sql ="UPDATE ".MAIN_DB_PREFIX."projet_task AS pt "
            ."SET duration_effective=(SELECT SUM(ptt.task_duration) "
            ."FROM ".MAIN_DB_PREFIX."projet_task_time AS ptt "
            ."WHERE ptt.fk_task ='".$this->id."') "
            ."WHERE pt.rowid='".$this->id."' ";

        dol_syslog(__METHOD__, LOG_DEBUG);


        $resql=$this->db->query($sql);
        if ($resql)
        {
                // return 1;
        }
        else
        {
                $this->error="Error ".$this->db->lasterror();
                dol_syslog(__METHOD__.$this->error, LOG_ERR);

                $error++;
        }
        // Commit or rollback
        if ($error)
        {
            foreach($this->errors as $errmsg)
            {
                dol_syslog(__METHOD__.$errmsg, LOG_ERR);
                $this->error.=($this->error?', '.$errmsg:$errmsg);
            }
            $this->db->rollback();
            return -1*$error;
        } else
        {
            $this->db->commit();
            return $this->id;
        }

    }
    function parseTaskTime($taskTime){
        
        $ret=floor($taskTime/3600).":".str_pad (floor($taskTime%3600/60),2,"0",STR_PAD_LEFT);
        
        return $ret;
        //return '00:00';
          
    }
    
    /*
     * change the status of an approval 
     * 
     *  @param      object/int        $user         user object or user id doing the modif
     *  @param      int               $id           id of the timesheetuser
     *  @param      bool              $updateTS      update the timesheet if true
     *  @return     int      		   	 <0 if KO, Id of created object if OK
     */
    //    Public function setAppoved($user,$id=0){
    Public function setStatus($user,$status,$updateTS=true){ //FIXME
        $error=0;
        $ret=0;
        //if the satus is not an ENUM status
        //if(!in_array($status, self::$statusList)){
        if($this->status<0 || $this->status> STATUSMAX){
            dol_syslog(__METHOD__." this status '{$status}' is not part or the possible list", LOG_ERR);
            return false;
        }
        // Check parameters
        $this->status=$status;
        //if($this->appId && $this->date_start_approval==0)$this->fetch($this->appId);

        if($this->getDuration()>0 || $this->note!=''){
            if($this->appId >0){
                $ret=$this->update($user);
            } else{
                $ret=$this->create($user);
            }
        }else if($this->appId >0){
                $ret=$this->delete($user);
        }
        if($ret>0 && $updateTS==true){// success of the update, then update the timesheet user if possible
            $staticTS= new TimesheetUserTasks($this->db );
            $staticTS->fetch($this->task_timesheet);
            $ret=$staticTS->updateStatus($user,$status);
        }
        return $ret;
    }   
  
    /*
     * change the status of an approval 
     * 
     *  @param      object/int        $user         user object or user id doing the modif
     *  @param      int               $id           id of the timesheetuser
     *  @param      bool              $updateTS      update the timesheet if true
     *  @return     int      		   	 <0 if KO, Id of created object if OK
     */
    //    Public function setAppoved($user,$id=0){
    Public function getDuration(){ //FIXME
        $ttaDuration=0;
        
        if(!is_array($this->tasklist))$this->getActuals();
        foreach($this->tasklist as $item){
            $ttaDuration+=$item['duration'];
        }
        return $ttaDuration;
    }
    /* function to post on task_time
    * 
    *  @param    int              	$user                    user id to fetch the timesheets
    *  @param    object             	$tasktime             timesheet object, (task)
    *  @param    array(int)              	$tasktimeid          the id of the tasktime if any
    *  @param     int              	$timestamp          timesheetweek
    *  @param     sting             	$status          status to be update
    *  @return     int                                                       1 => succes , 0 => Failure
    */
    function postTaskTimeActual($timesheetPost,$userId,$Submitter,$timestamp,$status,$note='')
    {
        global $conf;
        $ret=0;
        $noteUpdate=0;
        dol_syslog("Timesheet.class::postTaskTimeActual  taskTimeId=".$this->id, LOG_DEBUG);
        $this->timespent_fk_user=$userId;
        
        if(isset($timesheetPost['Note'])&& $timesheetPost['Note']!=$this->note){
            $this->note=$timesheetPost['Note'];
            $noteUpdate++;
        }else if($note!=NULL && $note!=$this->note){
            $this->note=($note);
            $noteUpdate++;
        }
            
        if(is_array($timesheetPost))foreach ($timesheetPost as $dayKey => $dayData){
            $wkload=$dayData[0];
            $note=$dayData[1];
            $item=$this->tasklist[$dayKey];
            
            if($conf->global->TIMESHEET_TIME_TYPE=="days")
            {
                $duration=$wkload*$conf->global->TIMESHEET_DAY_DURATION*3600;
            }else
            {
                $durationTab=date_parse($wkload);
                $duration=$durationTab['minute']*60+$durationTab['hour']*3600;
            }
            dol_syslog(__METHOD__."   duration Old=".$item['duration']." New=".$duration." Id=".$item['id'].", date=".$item['date'], LOG_DEBUG);
            $this->timespent_date=$item['date'];
            if(isset( $this->timespent_datehour))
            {
                $this->timespent_datehour=$item['date'];
            }
            if($item['id']>0)
            {

                $this->timespent_id=$item['id'];
                $this->timespent_old_duration=$item['duration'];
                $this->timespent_duration=$duration; 
                $this->timespent_note=$note;

                if($item['duration']!=$duration || $note!=$this->tasklist[$dayKey]['note'])
                {

                    if($this->timespent_duration>0 || !empty($note)){ 
                        dol_syslog(__METHOD__."  taskTimeUpdate", LOG_DEBUG);
                        if($this->updateTimeSpent($Submitter,0)>=0)
                        {
                            $ret++; 
                            $_SESSION['task_timesheet'][$timestamp]['timeSpendModified']++;
                        } else {
                            $_SESSION['task_timesheet'][$timestamp]['updateError']++;
                        }
                    } else {
                        dol_syslog(__METHOD__."  taskTimeDelete", LOG_DEBUG);
                        if($this->delTimeSpent($Submitter,0)>=0)
                        {
                            $ret++;
                            $_SESSION['task_timesheet'][$timestamp]['timeSpendDeleted']++;
                            $this->tasklist[$dayKey]['id']=0;
                        } else {
                            $_SESSION['task_timesheet'][$timestamp]['updateError']++;
                        }
                    }
                }
            } elseif ($duration>0)
            { 
                $this->timespent_duration=$duration; 
                $newId=$this->addTimeSpent($Submitter,0);
                if($newId>=0)
                {
                    $ret++;
                    $_SESSION['task_timesheet'][$timestamp]['timeSpendCreated']++;
                    $this->tasklist[$dayKey]['id']=$newId;
                } else {
                    $_SESSION['task_timesheet'][$timestamp]['updateError']++;
                }
            }
            //update the task list


            $this->tasklist[$dayKey]['duration']=$duration;
        }
        if($ret)$this->updateTimeUsed(); // needed upon delete
        return $ret+$noteUpdate;
        //return $idList;
    }
 

    /**
	 *	function that will send email to 
	 *
	 *	@return	void
	 */
    /*     
    function sendApprovalReminders(){
            global $langs;
            $sql = 'SELECT';
            $sql.=' COUNT(t.rowid) as nb,';
            $sql.=' u.email,';
            $sql.=' u.fk_user as approverid';
            $sql.= ' FROM '.MAIN_DB_PREFIX.'project_task_time_approval as t';
            $sql.= ' JOIN '.MAIN_DB_PREFIX.'user as u on t.fk_userid=u.rowid ';
            $sql.= ' WHERE (t.status="SUBMITTED" OR t.status="UNDERAPPROVAL" OR t.status="CHALLENGED")  AND t.recipient="team"';
            $sql.= ' GROUP BY u.fk_user';
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

                        $message=str_replace("__NB_TS__", $obj->nb, str_replace('\n', "\n",$langs->transnoentities('YouHaveApprovalPendingMsg')));
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
	                        $message
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
    */

    /*
     * pget the next approval in the chaine
     * 
     *  @param      object/int        $user         user object or user id doing the modif
     *  @param      string            $role         role who challenge
     *  @param      bool              $updteTS      update the timesheet if true
     *  @return     int      		   	 <0 if KO, Id of created object if OK
     */
    Public function Approved($user,$role, $updteTS =true){
        global $apflows;
        $userId=  is_object($user)?$user->id:$user;
        if($role<0&& $role>ROLEMAX) return -1; // role not valide
        $nextStatus=0;
        $ret=-1;

        //set the approver
        $this->user_app[$role]=$userId;
        //update the roles
        $rolepassed=false;
        // look for the role open after the curent role and set it as recipient
        foreach(array_slice ($apflows,1) as $key=> $value){
            $key++;
            if ($value==1 ){  
                if ( $key==$role){
                     $this->sender=$key;
                     $rolepassed=true;
                }else if ($rolepassed){
                    $this->recipient=$key;
                    $ret=$key;      
                    break;
                }                         
            }

        }
       
        if($ret>0){//other approval found
            $nextStatus=UNDERAPPROVAL;
            $ret=$this->setStatus($user,UNDERAPPROVAL,$updteTS);
        }else if($this->sender==$role){ // only if the role was alloed
             $this->recipient=USER;
             $nextStatus=APPROVED;           
            // if approved,recipient 

            //$this->recipient= self::$roleList[array_search('1', self::$apflows)];
        }
        $ret=$this->setStatus($user,$nextStatus,$updteTS);
        // save the change in the db
        if($ret>0)$ret=$this->updateTaskTime($nextStatus); 
        return $ret;
    }
        
    /*
     * challenge the tsak time approval
     * 
     *  @param      object/int        $user         user object or user id doing the modif
     *  @param      string            $role         role who challenge
     *  @param      bool              $updteTS      update the timesheet if true
     *  @return     int      		                <0 if KO, Id of created object if OK
     */
    Public function challenged($user,$role,$updteTS=true){
        global $apflows;
        $userId=  is_object($user)?$user->id:$user;
        $nextStatus=0;
        if($role<0&& $role>ROLEMAX) return -1; // role not valide
        $ret=-1;
       //unset the approver ( could be set previsouly)
        $this->user_app[$role]=$userId;
        //update the roles, look for the open role and define it as sender and save the previous role open as recipient 
        foreach(array_slice ($apflows,1) as $key=> $recipient){
            $key++;
            if ($recipient==1){  
                if ( $key==$role){
                        $this->sender= $role;
                    break;
                } else {
                    $this->recipient= $key; 
                    $ret=$key;
                }                                          
            }
        } 
        if($ret>0){//other approval found
            $nextStatus=CHALLENGED;
        } else if($this->sender==$role) { //update only if the role was allowed
            $this->recipient=USER;   
            $nextStatus=REJECTED;
        }
        $ret=$this->setStatus($user,$nextStatus,$updteTS);  
        if($ret>0)$ret=$this->updateTaskTime($nextStatus);
        return $ret;// team key is 0 
    }
        

    /*
     * submit the TS 
     * 
     *  @param      bool   $updteTS      update the timesheet if true
     *  @return     int      		   	 <0 if KO, Id of created object if OK
     */
    Public function submitted($user,$updteTS=false){
        global $apflows;
        // assign the first role open as recipient, put user as default
        $this->recipient=USER;
        foreach(array_slice ($apflows,1) as $key=> $recipient){   
            $key++;
            if ($recipient==1){  
                $this->recipient= $key;
                break;
            }
        }
        //Update the the fk_tta in the project task time 
        $ret=$this->setStatus($user,SUBMITTED,$updteTS); // must be executed first to get the appid
        if($ret>0)$ret=$this->updateTaskTime(SUBMITTED);
        
        return $ret+1;// team key is 0 
    }

}

TimesheetTask::init();
