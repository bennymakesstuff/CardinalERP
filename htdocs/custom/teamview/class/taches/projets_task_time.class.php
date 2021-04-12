<?php 
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php'; 
dol_include_once('/teamview/class/taches/projets_task_time.class.php');
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';

class projets_task_time extends Commonobject{ 
	public $errors = array();
	public $rowid;
	public $fk_task;
	public $task_date;
	public $task_datehour;
	public $task_date_withhour;
	public $fk_user;
	public $task_duration;

	public function __construct(DoliDBMysqli $db){ 
		$this->db = $db;
		return 1;
	}
	public function getAllTimesTask($task_id,$creer = null){ 
		global $langs,$trans;
		$langs->load('teamview@teamview');

		$userp = new User($this->db);
		$form = new Form($db);
		$times = new projets_task_time($this->db);
		$times->fetchAll('DESC','rowid','','',' AND fk_task = '.$task_id.'');
		
		$html = '';
		if (count($times->rows) > 0) {
			for ($i=0; $i < count($times->rows) ; $i++) {
				$item = $times->rows[$i];
				$html .= '<tr>';
					$html .= '<td align="center">';
						$date = $item->task_date;
						if ($item->task_date_withhour > 0)
							$date = $item->task_datehour;

						$date=date_create($date);
						$r = date_format($date,"d/m/Y");

						if ($item->task_date_withhour > 0)
							$r = date_format($date,"d/m/Y H:i");

						$html .= $r;
					$html .= '</td>';
					$html .= '<td align="center">';
						$userp->fetch($item->fk_user);
						$html .= $userp->getNomUrl(1,'',0,1);
					$html .= '</td>';
					$html .= '<td align="right" class="inputs_hm">';
					// echo $item->task_duration;
						$h_m = convertSecondToTime($item->task_duration,'allhourmin');
						$h_m = explode(":", $h_m);
						$html .= '<input type="hidden" class="id_task" value="'.$task_id.'"/>';
						$html .= '<input type="hidden" class="id_time" value="'.$item->rowid.'"/>';
						if ($creer > 0){
							$html .= '<input placeholder="H" type="number" min="0" size="1" name="new_durationhour" class="flat maxwidth50 inputhour" data-orig="'.($h_m[0]+0).'" value="'.($h_m[0]+0).'" onchange="times_task_changed(this)">';
							$html .= '<span class="hideonsmartphone">:</span>';
							$html .= '<span class="hideonsmartphone">&nbsp;</span>';
							$html .= '<input placeholder="mn" type="number" min="0" size="1" name="new_durationmin" class="flat maxwidth50 inputminute" data-orig="'.($h_m[1]+0).'" value="'.($h_m[1]+0).'" onchange="times_task_changed(this)">';
						}else{
							$html .= ($h_m[0]);
							$html .= '<span class="hideonsmartphone">:</span>';
							$html .= ($h_m[1]);
						}
						
					$html .= '</td>';
					$html .= '<td align="center">';
					$html .= '<button class="update_times_tasks button button_save_" onclick="update_times_tasks(this);" disabled="disabled">'.$langs->trans("save").'</button>';
					$html .= '</td>';

				$html .= '</tr>';
			}
		}else{
			$html = '<tr><td align="center" colspan="4">'.$langs->trans("aucun_temp_consommé").'</td></tr>';
		}
		// print_r($timesTask);
		// die();
		// echo $html;
		// die();
		return $html;
	}

	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = "SELECT * FROM ";
		$sql .= MAIN_DB_PREFIX ."projet_task_time";
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
				$line->fk_task 		= $obj->fk_task;
				$line->task_date 	= $obj->task_date;
				$line->task_datehour 	= $obj->task_datehour;
				$line->task_date_withhour 	= $obj->task_date_withhour;
				$line->fk_user 	= $obj->fk_user;
				$line->task_duration 	= $obj->task_duration;
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

		$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX .'projet_task_time WHERE rowid = ' . $id;

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			
			if ($numrows) {
				$obj 					= $this->db->fetch_object($resql);
				$this->rowid 	= $obj->rowid;
				$this->fk_task 		= $obj->fk_task;
				$this->task_date 	= $obj->task_date;
				$this->task_datehour 	= $obj->task_datehour;
				$this->task_date_withhour 	= $obj->task_date_withhour;
				$this->task_duration 	= $obj->task_duration;
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