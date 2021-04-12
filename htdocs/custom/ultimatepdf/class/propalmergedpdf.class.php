<?php
/* Copyright (C) 2013 Florian HENRY  <florian.henry@open-concept.pro>
 * Copyright (C) 2014-2017 Philippe GRAND <philippe.grand@atoo-net.com>
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
 *  \file       ultimatepdf/class/propalmergedpdf.class.php
 *  \ingroup    ultimatepdf
 *  \brief      This file is an CRUD class file (Create/Read/Update/Delete)
 */

require_once DOL_DOCUMENT_ROOT.'/core/class/commonobject.class.php';


/**
 * Class Propalmergedpdf
 *
 * Class for merging pdf documents with prosal
 *
 * @see CommonObject
 */
class Propalmergedpdf extends CommonObject
{
	/**
     * @var DoliDb Database handler
     */
    public $db;	
	
	/**
	 * @var string To return error code (or message)
	 */
	public $error;	

	/**
	 * @var array() To return several error codes (or messages)
	 */
	public $errors=array();
	
	/**
	 * @var string Id to identify managed objects
	 */
	public $element='ultimatepdf_propal_merged_pdf';

	/**
	 * @var string Name of table without prefix where object is stored
	 */
	public $table_element='ultimatepdf_propal_merged_pdf';		

    public $id;
    
	public $fk_propal;
	
	public $file_name;
	
	public $fk_user_author;
	
	public $fk_user_mod;
	
	public $datec='';
	
	public $tms='';
	
	public $import_key;
	
	public $lang;
	
	public $lines=array();

    /**
     *  Constructor
     *
     *  @param	DoliDb		$db      Database handler
     */
    public function __construct(DoliDB $db)
	{
		$this->db = $db;
	}

    /**
	 * Create object into database
	 *
	 * @param  User $user      User that creates
	 * @param  bool $notrigger false=launch triggers after, true=disable triggers
	 *
	 * @return int <0 if KO, Id of created object if OK
	 */
	public function create(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
    	global $conf, $langs;
		
		$error=0;

		// Clean parameters       
		if (isset($this->fk_propal)) $this->fk_propal=trim($this->fk_propal);
		if (isset($this->file_name)) $this->file_name=trim($this->file_name);
		if (isset($this->fk_user_author)) $this->fk_user_author=trim($this->fk_user_author);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->lang)) $this->lang=trim($this->lang);
		if (isset($this->import_key)) $this->import_key=trim($this->import_key);

		// Check parameters
		// Put here code to add control on parameters values

        // Insert request
		$sql = "INSERT INTO ".MAIN_DB_PREFIX.$this->table_element."(";
		
		$sql.= "fk_propal,";
		$sql.= "file_name,";
		$sql.= "fk_user_author,";
		$sql.= "fk_user_mod,";
		$sql.= "datec";

		
        $sql.= ") VALUES (";
        
		$sql.= " ".(! isset($this->fk_propal)?'NULL':"'".$this->fk_propal."'").",";
		$sql.= " ".(! isset($this->file_name)?'NULL':"'".$this->db->escape($this->file_name)."'").",";
		$sql.= " ".$user->id.",";
		$sql.= " ".$user->id.",";
		$sql.= " '".$this->db->idate(dol_now())."'";

        
		$sql.= ")";

		$this->db->begin();

	   	$resql = $this->db->query($sql);
		if (!$resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . implode(',', $this->errors), LOG_ERR);
		}

		if (! $error)
        {
            $this->id = $this->db->last_insert_id(MAIN_DB_PREFIX.$this->table_element);

			if (! $notrigger)
			{
	            // Uncomment this and change MYOBJECT to your own tag if you
	            // want this action calls a trigger.

	            //// Call triggers
	            //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
	            //$interface=new Interfaces($this->db);
	            //$result=$interface->run_triggers('MYOBJECT_CREATE',$this,$user,$langs,$conf);
	            //if ($result < 0) { $error++; $this->errors=$interface->errors; }
	            //// End call triggers
			}
        }

        // Commit or rollback
        if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(__METHOD__ .'errmsg='.$errmsg, LOG_ERR);
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
     *  @param	int		$id    Id object
     *  @return int          	<0 if KO, >0 if OK
     */
    public function fetch($id)
    {
		dol_syslog(__METHOD__, LOG_DEBUG);
		
    	global $langs,$conf;
    	
        $sql = "SELECT";
		$sql.= " t.rowid,";
		
		$sql.= " t.fk_propal,";
		$sql.= " t.file_name,";
		$sql.= " t.fk_user_author,";
		$sql.= " t.fk_user_mod,";
		$sql.= " t.datec,";
		$sql.= " t.tms,";
		$sql.= " t.import_key";
	
        $sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element.' as t';
        $sql.= " WHERE t.rowid = ".$id;

    	dol_syslog(__METHOD__ .' sql='.$sql, LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
            $numrows = $this->db->num_rows($resql);
			if ($numrows) 
			{
				$obj = $this->db->fetch_object($resql);

                $this->id = $obj->rowid;             
				$this->fk_propal = $obj->fk_propal;
				$this->file_name = $obj->file_name;
				$this->fk_user_author = $obj->fk_user_author;
				$this->fk_user_mod = $obj->fk_user_mod;
				$this->datec = $this->db->jdate($obj->datec);
				$this->tms = $this->db->jdate($obj->tms);
				$this->import_key = $obj->import_key;              
            }
			
			// Retrieve all extrafields for proposal
			// fetch optionals attributes and labels
			require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
			$extrafields=new ExtraFields($this->db);
			$extralabels=$extrafields->fetch_name_optionals_label($this->table_element,true);
			$this->fetch_optionals($this->id,$extralabels);
			
            $this->db->free($resql);

           if ($numrows) {
				return 1;
			} else {
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . implode(',', $this->errors), LOG_ERR);

			return - 1;
		}
    }
    
    /**
     *  Load object in memory from the database
     *
     *  @param	int		$id    Id object
     *  @param	string	$lang  lang string id
     *  @return int          	<0 if KO, >0 if OK
     */
    public function fetch_by_propal($propal_id, $lang='')
    {
    	global $langs,$conf;
    	
    	$sql = "SELECT";
    	$sql.= " t.rowid,";
    
    	$sql.= " t.fk_propal,";
    	$sql.= " t.file_name,";
    	$sql.= " t.fk_user_author,";
    	$sql.= " t.fk_user_mod,";
    	$sql.= " t.datec,";
    	$sql.= " t.tms,";
    	$sql.= " t.import_key";
    
    
    	$sql.= " FROM ".MAIN_DB_PREFIX.$this->table_element.' as t';
    	$sql.= " WHERE t.fk_propal = ".$propal_id;
    
    	dol_syslog(__METHOD__ .' sql='.$sql, LOG_DEBUG);
    	$resql=$this->db->query($sql);
    	if ($resql)
    	{
    		$num = $this->db->num_rows($resql);
			
			while($obj = $this->db->fetch_object($resql)) 
			{
				$line = new PropalmergedpdfpropalLine();
				
				$line->id = $obj->rowid;    
				$line->fk_propal = $obj->fk_propal;
				$line->file_name = $obj->file_name;
				$line->fk_user_author = $obj->fk_user_author;
				$line->fk_user_mod = $obj->fk_user_mod;
				$line->datec = $this->db->jdate($obj->datec);
				$line->tms = $this->db->jdate($obj->tms);
				$line->import_key = $obj->import_key;
				$this->lines[$obj->file_name]=$line;		
			}
    		$this->db->free($resql);
    
    		return $num;
    	}
    	else
    	{
    		$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . implode(',', $this->errors), LOG_ERR);

			return - 1;
    	}
    }


    /**
     *  Update object into database
     *
     *  @param	User	$user        User that modifies
     *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
     *  @return int     		   	 <0 if KO, >0 if OK
     */
    public function update(User $user, $notrigger = false)
    {
    	global $conf, $langs;
		
		$error=0;
		
		dol_syslog(__METHOD__, LOG_DEBUG);

		// Clean parameters      
		if (isset($this->fk_propal)) $this->fk_propal=trim($this->fk_propal);
		if (isset($this->file_name)) $this->file_name=trim($this->file_name);
		if (isset($this->fk_user_mod)) $this->fk_user_mod=trim($this->fk_user_mod);
		if (isset($this->lang)) $this->lang=trim($this->lang);

		// Check parameters
		// Put here code to add a control on parameters values

        // Update request
        $sql = "UPDATE ".MAIN_DB_PREFIX.$this->table_element.' SET';
        
		$sql.= " fk_propal=".(isset($this->fk_propal)?$this->fk_propal:"null").",";
		$sql.= " file_name=".(isset($this->file_name)?"'".$this->db->escape($this->file_name)."'":"null").",";
		$sql.= " fk_user_mod=".$user->id;
     
        $sql.= " WHERE rowid=".$this->id;

		$this->db->begin();

		$resql = $this->db->query($sql);
		if (!$resql) {
			$error ++;
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . implode(',', $this->errors), LOG_ERR);
		}

		if (!$error && !$notrigger) 
		{
			// Uncomment this and change MYOBJECT to your own tag if you
			// want this action calls a trigger.

			//// Call triggers
			//$result=$this->call_trigger('MYOBJECT_MODIFY',$user);
			//if ($result < 0) { $error++; //Do also what you must do to rollback action if trigger fail}
			//// End call triggers
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(__METHOD__ .'errmsg='.$errmsg, LOG_ERR);
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
	public function delete(User $user, $notrigger = false)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
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
		        //include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
		        //$interface=new Interfaces($this->db);
		        //$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
		        //if ($result < 0) { $error++; $this->errors=$interface->errors; }
		        //// End call triggers
			}
		}

		if (! $error)
		{
    		$sql = "DELETE FROM ".MAIN_DB_PREFIX.$this->table_element;
    		$sql.= " WHERE rowid=".$this->id;

    		$resql = $this->db->query($sql);
			if (!$resql) {
				$error ++;
				$this->errors[] = 'Error ' . $this->db->lasterror();
				dol_syslog(__METHOD__ . ' ' . implode(',', $this->errors), LOG_ERR);
			}
		}

        // Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
	            dol_syslog(__METHOD__ .'errmsg='.$errmsg, LOG_ERR);
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
	 *	@param  int		$propal_id	 propal_id
	 *  @param  string	$lang_id	 language
	 *  @param  int		$notrigger	 0=launch triggers after, 1=disable triggers
	 *  @return	int					 <0 if KO, >0 if OK
	 */
	public function delete_by_propal($user, $propal_id, $lang_id='', $notrigger=false)
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
				//include_once DOL_DOCUMENT_ROOT . '/core/class/interfaces.class.php';
				//$interface=new Interfaces($this->db);
				//$result=$interface->run_triggers('MYOBJECT_DELETE',$this,$user,$langs,$conf);
				//if ($result < 0) { $error++; $this->errors=$interface->errors; }
				//// End call triggers
			}
		}
	
		if (! $error)
		{
			$sql = "DELETE FROM ".MAIN_DB_PREFIX."ultimatepdf_propal_merged_pdf";
			$sql.= " WHERE fk_propal=".$propal_id;
	
			dol_syslog(__METHOD__ .' sql='.$sql, LOG_DEBUG);
			$resql = $this->db->query($sql);
			if (! $resql) { $error++; $this->errors[]="Error ".$this->db->lasterror(); }
		}
	
		// Commit or rollback
		if ($error)
		{
			foreach($this->errors as $errmsg)
			{
				dol_syslog(__METHOD__ .'errmsg='.$errmsg, LOG_ERR);
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
	public function createFromClone($fromid)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		global $user,$langs;
		$error=0;
		$object=new Propalmergedpdf($this->db);

		$this->db->begin();

		// Load source object
		$object->fetch($fromid);
		// Reset object
		$object->id=0;
		$object->statut=0;

		// Clear fields
		// ...

		// Create clone
		$result=$object->create($user);

		// Other options
		if ($result < 0) {
			$error ++;
			$this->errors = $object->errors;
			dol_syslog(__METHOD__ . ' ' . implode(',', $this->errors), LOG_ERR);
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

	/**
	 *	Initialise object with example values
	 *	Id must be 0 if object instance is a specimen
	 *
	 *	@return	void
	 */
	public function initAsSpecimen()
	{
		$this->id=0;
		
		$this->fk_propal='';
		$this->file_name='';
		$this->fk_user_author='';
		$this->fk_user_mod='';
		$this->datec='';
		$this->tms='';
		$this->import_key='';

		
	}
}

class PropalmergedpdfpropalLine
{
	/**
	 * @var int ID
	 */
	public $id;
	
	public $fk_propal;
	public $file_name;
	public $fk_user_author;
	public $fk_user_mod;
	public $datec='';
	public $tms='';
	public $import_key;

	function __construct() {
		return 1;
	}

}
