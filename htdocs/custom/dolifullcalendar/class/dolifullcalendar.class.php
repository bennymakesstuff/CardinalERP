<?php
/* <one line to give the program's name and a brief idea of what it does.>
 * Copyright (C) <year>  <name of author>
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

/**
 * 	\file		class/myclass.class.php
 * 	\ingroup	mymodule
 * 	\brief		This file is an example CRUD class file (Create/Read/Update/Delete)
 * 				Put some comments here
 */
// Put here all includes required by your class file
//require_once DOL_DOCUMENT_ROOT."/core/class/commonobject.class.php";
//require_once DOL_DOCUMENT_ROOT."/societe/class/societe.class.php";
//require_once DOL_DOCUMENT_ROOT."/product/class/product.class.php";

/**
 * Put your class' description here
 */
class Dolifullcalendar // extends CommonObject
{

	private $db; //!< To store db handler
	public $error; //!< To return error code (or message)
	public $errors = array(); //!< To return several error codes (or messages)
	//public $element='skeleton';	//!< Id that identify managed objects
	//public $table_element='skeleton';	//!< Name of table without prefix where object is stored
	public $id;
	public $title;
	public $start;
	public $end;
	public $color;

	/**
	 * Constructor
	 *
	 * 	@param	DoliDb		$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;

		return 1;
	}

	/**
	 * Create object into database
	 *
	 * 	@param		User	$user		User that create
	 * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
	 * 	@return		int					<0 if KO, Id of created object if OK
	 */
	
	function add($user,$notrigger=0)
    {
        global $langs,$conf,$hookmanager;

        $error=0;
        $now=dol_now();

        // Clean parameters
        $this->title=dol_trunc(trim($this->title),128);
        //if (! empty($this->date)  && ! empty($this->dateend)) $this->durationa=($this->dateend - $this->date);
        if (! empty($this->start) && ! empty($this->end) && $this->start > $this->end) $this->end=$this->start;
        

        $this->db->begin();

        $sql = "INSERT INTO ".MAIN_DB_PREFIX."fc_events";
        $sql.= "(title,";
        $sql.= "start,";
        $sql.= "end,";
        $sql.= "color,";	// deprecated
        $sql.= "fk_user_author";
        $sql.= ") VALUES (";
        $sql.= "'".$this->db->escape($this->title)."',";
        $sql.= (strval($this->start)!=''?"'".$this->start."'":"null").",";
        $sql.= (strval($this->end)!=''?"'".$this->end."'":"null").",";
		$sql.= "'".$this->db->escape($this->color)."',";
        $sql.= (isset($user->id) && $user->id > 0 ? "'".$user->id."'":"null")."";
        
        $sql.= ")";
		
        dol_syslog(get_class($this)."::add", LOG_DEBUG);
        $resql=$this->db->query($sql);
        if ($resql)
        {
			$this->db->commit();
			return 1;
           
        }
        else
        {
            $this->db->rollback();
            $this->error=$this->db->lasterror();
            return -1;
        }

    }

	/**
	 * Load object in memory from database
	 *
	 * 	@param		int		$id	Id object
	 * 	@return		int			<0 if KO, >0 if OK
	 */
    /**
     *    Load object from database
     *
     *    @param	int		$id     	Id of action to get
     *    @param	string	$ref    	Ref of action to get
     *    @param	string	$ref_ext	Ref ext to get
     *    @return	int					<0 if KO, >0 if OK
     */
    function fetchAll($year)
    {
        global $langs;

        $sql = "SELECT rowid,";
        $sql.= " title,";
        $sql.= " start,";
        $sql.= " end,";
        $sql.= " color ";	
        $sql.= " FROM ".MAIN_DB_PREFIX."fc_events as a ";
        $sql.= " WHERE year(start) = $year";
        

        dol_syslog(get_class($this)."::fetch", LOG_DEBUG);
        $resql=$this->db->query($sql);
		if ($resql) {
			$i = 0;
			$obj = '';
			$num = $this->db->num_rows($resql);
			$data = array();
			if ($num) {
				while ( $i < $num ) {
					
					$obj = $this->db->fetch_object($resql);
					
					$name_cat = $obj->name_cat;
					
					$data[$i] =	array(
									'id' => $obj->rowid,
									'start' => $obj->start,
									'end' => $obj->end,
									'title' => $obj->title,
									'color' => $obj->color,
									);
					
					$i ++;
				}
			}
			
			return $data;
		} else {
			$this->error = "Error " . $this->db->lasterror();
			dol_syslog(__METHOD__ . " " . $this->error, LOG_ERR);

			return -1;
		}

    }

	/**
	 * Update object into database
	 *
	 * 	@param		User	$user		User that modify
	 * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
	 * 	@return		int					<0 if KO, >0 if OK
	 */
	public function updateTitle($user = 0, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		if (isset($this->title)) {
			$this->title = trim($this->title);
		}
		if (isset($this->start)) {
			$this->start = trim($this->start);
		}
		if (isset($this->end)) {
			$this->end = trim($this->end);
		}
		if (isset($this->color)) {
			$this->color = trim($this->color);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "fc_events SET";
		$sql.= " title=" . (isset($this->title) ? "'" . $this->db->escape($this->title) . "'" : "null") . ",";
		$sql.= " color=" . (isset($this->color) ? "'" . $this->db->escape($this->color) . "'" : "null") . "";

		$sql.= " WHERE rowid=" . $this->id;

		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}


		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}
	
	/**
	 * Update object into database
	 *
	 * 	@param		User	$user		User that modify
	 * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
	 * 	@return		int					<0 if KO, >0 if OK
	 */
	public function updateDate($user = 0, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		// Clean parameters
		
		if (isset($this->start)) {
			$this->start = trim($this->start);
		}
		if (isset($this->end)) {
			$this->end = trim($this->end);
		}

		// Check parameters
		// Put here code to add control on parameters values
		// Update request
		$sql = "UPDATE " . MAIN_DB_PREFIX . "fc_events SET";
		$sql.= " start=" . (isset($this->start) ? "'" . $this->db->escape($this->start) . "'" : "null") . ",";
		$sql.= " end=" . (isset($this->end) ? "'" . $this->db->escape($this->end) . "'" : "null") . "";

		$sql.= " WHERE rowid=" . $this->id;

		$this->db->begin();

		dol_syslog(__METHOD__ . " sql=" . $sql, LOG_DEBUG);
		$resql = $this->db->query($sql);
		if (! $resql) {
			$error ++;
			$this->errors[] = "Error " . $this->db->lasterror();
		}


		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return 0;
		} else {
			$this->db->commit();

			return 1;
		}
	}

	/**
	 * Delete object in database
	 *
	 * 	@param		User	$user		User that delete
	 * 	@param		int		$notrigger	0=launch triggers after, 1=disable triggers
	 * 	@return		int					<0 if KO, >0 if OK
	 */
	public function delete($user, $notrigger = 0)
	{
		global $conf, $langs;
		$error = 0;

		$this->db->begin();



		if (! $error) {
			$sql = "DELETE FROM " . MAIN_DB_PREFIX . "fc_events";
			$sql.= " WHERE rowid=" . $this->id;

			dol_syslog(__METHOD__ . " sql=" . $sql);
			$resql = $this->db->query($sql);
			if (! $resql) {
				$error ++;
				$this->errors[] = "Error " . $this->db->lasterror();
			}
		}

		// Commit or rollback
		if ($error) {
			foreach ($this->errors as $errmsg) {
				dol_syslog(__METHOD__ . " " . $errmsg, LOG_ERR);
				$this->error.=($this->error ? ', ' . $errmsg : $errmsg);
			}
			$this->db->rollback();

			return -1 * $error;
		} else {
			$this->db->commit();

			return 1;
		}
	}

}
