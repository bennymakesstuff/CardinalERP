<?php 
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php'; 
 
class p_projets extends Commonobject{ 
	public $errors = array();
	public $id;
	public $rowid;
	public $ref;
	public $title;
	public $fk_soc;
	public $dateo;
	public $fk_statut; 
	// 1 = Ouvert | 0 = Brouillon | 2 : Clôturé 
	public $fk_opp_status; 
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

		// echo $sql;
		$this->rows = array();
		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new stdClass;
				$line->id 	= $obj->rowid;
				$line->rowid 	= $obj->rowid;
				$line->ref 		= $obj->ref;
				$line->title 	= $obj->title;
				$line->fk_soc 	= $obj->fk_soc;
				$line->dateo 	= $obj->dateo;
				$line->fk_statut = $obj->fk_statut;
				$line->fk_opp_status = $obj->fk_opp_status;
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
				$this->id 		= $obj->rowid;
				$this->rowid 		= $obj->rowid;
				$this->ref 			= $obj->ref;
				$this->title 		= $obj->title;
				$this->fk_soc 		= $obj->fk_soc;
				$this->dateo 		= $obj->dateo;
				$this->fk_statut 	= $obj->fk_statut;
				$this->fk_opp_status 	= $obj->fk_opp_status;
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

	public function getNomUrl($withpicto = 0,  $id = null, $ref = null)
	{
		global $langs;

        $result	= '';
        $setRef = (null !== $ref) ? $ref : '';
        $id  	= ($id  ?: '');
        $label  = $langs->trans("Show").': '. $setRef;

        $link 	 = '<a href="'.DOL_URL_ROOT.'/projet/card.php?id='. $id .'" title="'.dol_escape_htmltag($label, 1).'" class="classfortooltip">';
        $linkend ='</a>';
        $picto   = 'elemt@lproduction';

        if ($withpicto) $result.=($link.img_object($label, $picto, 'class="classfortooltip"').$linkend);
        if ($withpicto && $withpicto != 2) $result.=' ';
        if ($withpicto != 2) $result.=$link.$setRef.$linkend;

        $result = $link.$setRef.$linkend;
        return $result;
	}
	public function select_all_clients($selected=0,$name='select_',$showempty=1,$val="rowid",$opt="label",$id=''){
	    global $conf;
	    $moreforfilter = '';
	    $nodatarole = '';
	    $id = (!empty($id)) ? $id : $name;
	    if ($conf->use_javascript_ajax)
	    {
	        include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
	        $comboenhancement = ajax_combobox('select_'.$id);
	        $moreforfilter.=$comboenhancement;
	        $nodatarole=($comboenhancement?' data-role="none"':'');
	    }
	
	    $moreforfilter.='<select class="flat" id="select_'.$id.'" name="'.$name.'" '.$nodatarole.'>';
	    if ($showempty) $moreforfilter.='<option value="0">&nbsp;</option>';

    	$sql = "SELECT ".$val.",".$opt." FROM ".MAIN_DB_PREFIX."projet";

    	$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$moreforfilter.='<option value="'.$obj->$val.'" data-ref="'.$obj->$opt.'"';
	            if ($obj->$val == $selected) $moreforfilter.=' selected';
	            $moreforfilter.='>'.$obj->$opt.'</option>';
			}
			$this->db->free($resql);
		}

	    $moreforfilter.='</select>';
	    $moreforfilter.='<style>#s2id_select_'.$name.'{ width: 100% !important;}</style>';
	    return $moreforfilter;
	}

	public function update($id, array $data,$echo_sql=0)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		if (!$id || $id <= 0)
			return false;

		$sql = 'UPDATE ' . MAIN_DB_PREFIX .'projet SET ';

		if (count($data) && is_array($data))
			foreach ($data as $key => $val) {
				$val = is_numeric($val) ? $val : '"'. $val .'"';
				$sql .= '`'. $key. '` = '. $val .',';
			}

		$sql  = substr($sql, 0, -1);
		$sql .= ' WHERE rowid = ' . $id;

		// echo $sql;
		// $this->db->begin();

		$resql = $this->db->query($sql);

		if (!$resql) {
			$this->db->rollback();
			$this->errors[] = 'Error '.get_class($this).' : '. $this->db->lasterror();
			return -1;
		} 
		return 1;
	}
} 

?>