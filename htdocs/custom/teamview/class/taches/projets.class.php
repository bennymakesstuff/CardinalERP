<?php 
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php'; 
 
class projets extends Commonobject{ 
	public $errors = array();
	public $rowid;
	public $dateo;
	public $datee;
	public $fk_soc;
	public $fk_statut;
	public $public;

	public function __construct(DoliDBMysqli $db){ 
		$this->db = $db;
		return 1;
	}
	

	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = "SELECT * FROM ";
		$sql .= MAIN_DB_PREFIX ."projet";
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
				$line->dateo 	= $obj->dateo;
				$line->datee 	= $obj->datee;
				$line->fk_soc 	= $obj->fk_soc;
				$line->fk_statut = $obj->fk_statut;
				$line->public 	= $obj->public;
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

	public function fetch($id, $ref = null)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX . 'projet WHERE rowid = ' . $id;

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);
				$this->rowid 		= $obj->rowid;
				$this->dateo 		= $obj->dateo;
				$this->datee 		= $obj->datee;
				$this->fk_soc 		= $obj->fk_soc;
				$this->fk_statut 	= $obj->fk_statut;
				$this->public 	= $obj->public;
			}

			$this->db->free($resql);

			if ($numrows) {
				return 1 ;
			} else {
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return -1;
		}
	}
} 

?>