<?php 
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php'; 
 
class taches extends Commonobject{ 
	public $errors = array();
	public $rowid;
	public $ref;
	public $label;
	public $fk_projet;
	public $fk_task_parent;
	public $progress;

	public function __construct(DoliDBMysqli $db){ 
		$this->db = $db;
		return 1;
	}
	public function arrayofallowstasks($userid){
		$taskallows = [];
		$sql = "SELECT rowid FROM ".MAIN_DB_PREFIX."projet_task";
		$sql .= " WHERE rowid in (SELECT element_id from ".MAIN_DB_PREFIX."element_contact where fk_socpeople = ".$userid." AND fk_c_type_contact in (SELECT rowid from ".MAIN_DB_PREFIX."c_type_contact where element = 'project_task'))";
		// echo $sql;
		$resql = $this->db->query($sql);
		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
				$taskallows[] = $obj->rowid;
			}
			$this->db->free($resql);
		}
		return $taskallows;
	}

	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = "SELECT * FROM ";
		$sql .= MAIN_DB_PREFIX ."projet_task";
		if (!empty($filter)) {
			$sql .= " WHERE 1>0 ".$filter;
		}
		
		// echo $sql;
		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield, $sortorder);
		}

		if (!empty($limit)) {
			if($offset==1)
				$sql .= " limit ".$limit;
			else
				$sql .= " limit ".$offset.",".$limit;				
		}

		$this->rows = array();
		$resql = $this->db->query($sql);
		//echo $sql;
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new stdClass;
				$line->rowid 	= $obj->rowid;
				$line->ref 		= $obj->ref;
				$line->label 	= $obj->label;
				$line->fk_projet 	= $obj->fk_projet;
				$line->fk_task_parent 	= $obj->fk_task_parent;
				$line->progress = $obj->progress;
				$this->rows[] 	= $line;
			}
			$this->db->free($resql);

			return $num;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}

	}
	
	public function update_task_avanc($sql)
	{
		$resql = $this->db->query($sql);
		if ($resql) {
			
			return "valide";
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);
		}
	}
} 

?>