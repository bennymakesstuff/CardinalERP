<?php 
require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php'; 
dol_include_once('/teamview/class/taches/projets_task_time.class.php');
class teamview extends Commonobject{ 
	public $errors = array();
	public $rowid;
	public $id_projet;
	public $id_tache;
	public $etat_tache;
	public $description;

	public function __construct(DoliDBMysqli $db){ 
		$this->db = $db;
		return 1;
	}
	public function recursive_append_children($arr, $children){
		// if (is_array($arr)){
	    foreach($arr as $key => $page)
	        if(isset($children[$key]))
	            $arr[$key]['children'] = $this->recursive_append_children($children[$key], $children);
      	// }
	    return $arr;
	}
	function projectLinesa(&$inc, $parent, &$lines, &$level, $var, $showproject, &$taskrole, $projectsListId='', $addordertick=0, $projectidfortotallink=0,$justAvance = false)
	{
		global $user, $bc, $langs;
		global $projectstatic, $taskstatic, $resulthtml, $id_tache, $tot_arr, $taskallows,$spacesub;

		$selecte_html = '
			<option value="-1">&nbsp;</option>
			<option value="0">0 % </option>
			<option value="5">5 % </option>
			<option value="10">10 % </option>
			<option value="15">15 % </option>
			<option value="20">20 % </option>
			<option value="25">25 % </option>
			<option value="30">30 % </option>
			<option value="35">35 % </option>
			<option value="40">40 % </option>
			<option value="45">45 % </option>
			<option value="50">50 % </option>
			<option value="55">55 % </option>
			<option value="60">60 % </option>
			<option value="65">65 % </option>
			<option value="70">70 % </option>
			<option value="75">75 % </option>
			<option value="80">80 % </option>
			<option value="85">85 % </option>
			<option value="90">90 % </option>
			<option value="95">95 % </option>
			<option value="100">100 % </option>
		</select>';

		$numlines=count($lines);

		for ($i = 0 ; $i < $numlines ; $i++)
		{
			if ($parent == 0 && $level >= 0) {
				$level = 0;
				
			}
				// if $level = -1, we dont' use sublevel recursion, we show all lines

			if ($lines[$i]->fk_parent == $parent || $level < 0)       // if $level = -1, we dont' use sublevel recursion, we show all lines
			{
				// Show task line.
				$showline=1;
				$showlineingray=0;

				if ($showline)
				{
					// Break on a new project
					if ($parent == 0 && $lines[$i]->fk_project != $lastprojectid)
					{
						$var = !$var;
						$lastprojectid=$lines[$i]->fk_project;
					}

					$resulthtml .= '<tr class="trparent" '.$bc[$var].' id="row-'.$lines[$i]->id.'">'."\n";
			
					// Title of task
					$resulthtml .= '<td class="tache_id" data-rowid="'.$lines[$i]->id.'">';
					
					for ($k = 0 ; $k < $level ; $k++)
					{
						$resulthtml .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;";
						$spacesub .= "&nbsp; &nbsp; &nbsp;";
					}
					$resulthtml .= $lines[$i]->ref." - ".$lines[$i]->label;
					// $resulthtml .= '<span class="avanc_line" id="avanc'.$lines[$i]->id.'"></span>';
					$resulthtml .= "</td>\n";

					// Progress calculated (Note: ->duration is time spent)
					// $resulthtml .= '<td align="right">';
					// if ($lines[$i]->planned_workload || $lines[$i]->duration)
					// {
					// 	if ($lines[$i]->planned_workload) 
					// 		$resulthtml .= round(100 * $lines[$i]->duration / $lines[$i]->planned_workload,2).' %';
					// 	else 
					// 		$resulthtml .= '<span class="opacitymedium">'.$langs->trans('WorkloadNotDefined').'</span>';
					// }

					// $resulthtml .= '</td>';

					// Progress declared
					$resulthtml .= '<td class="tache_avanc" align="right" data-avanc="'.$lines[$i]->progress.'">';
					// <option value="45" '. if($lines[$i]->progress == 45 ){ .'selected'. } .'>45 % </option>
					// print_r($taskallows);
					if ($user->rights->projet->creer){
					 	$resulthtml .= '<select class="flat sous_tasks task_'.$lines[$i]->id.'" name="progress_tasks['.$lines[$i]->id.']" onchange="progress_tasks_change()">';
						$resulthtml .= str_replace(' value="'.$lines[$i]->progress.'"', ' value="'.$lines[$i]->progress.'" selected="selected"', $selecte_html);
					}else{
						if ($lines[$i]->progress > 0) {
							$resulthtml .= $lines[$i]->progress. ' %';
						}else{
							$resulthtml .='-';
						}
					}
					// if ($lines[$i]->progress != '')
					// {
					// 	$resulthtml .= $lines[$i]->progress.' %';
					// }
					$resulthtml .= '</td>';
					$resulthtml .= '<td class="show_times_task" align="right">';
					$resulthtml .= '<button class="button button_times_" onclick="show_times_task(this,'.$lines[$i]->id.');">'.$langs->trans("Temps_consommé").'</button>';
					$resulthtml .= '</td>';

					$tot_arr['tot'] += 1;
					if ($lines[$i]->progress == 100) {
						$tot_arr['tot_c'] += 1;
					}
					$resulthtml .= "</tr>\n";

$resulthtml .= '<tr class="tr_of_times_in_subtask task_'.$lines[$i]->id.'" style="display:none;">';
$resulthtml .= '<td colspan="3" align="right">';
$resulthtml .= '<table class="noborder times_task_table">';
$resulthtml .= '<tr>';
$resulthtml .= '<th>'.$langs->trans("Date").'</th>';
$resulthtml .= '<th>'.$langs->trans("Par").'</th>';
$resulthtml .= '<th align="right" class="durre_text_th">'.$langs->trans("Durée").'</th>';
$resulthtml .= '<th align="center" class="">'.$langs->trans("Action").'</th>';
$resulthtml .= '</tr>';
$projets_task_time = new projets_task_time($this->db);
$timesTaskHtml = $projets_task_time->getAllTimesTask($lines[$i]->id,$user->rights->projet->creer);
$resulthtml .= $timesTaskHtml;
$resulthtml .= '</table>';
$resulthtml .= '</td>';
$resulthtml .= '</tr>';
$resulthtml .= '<tr class="noborder_taskunder">';
$resulthtml .= '<td colspan="3"></td>';
$resulthtml .= '</tr>';

					if (! $showlineingray) $inc++;

					if ($level >= 0)    // Call sublevels
					{
						$level++;
						if ($lines[$i]->id) 
							$this->projectLinesa($inc, $lines[$i]->id, $lines, $level, $var, $showproject, $taskrole, $projectsListId, $addordertick);
						$level--;
					}
				}
			}
		}
		if (empty($resulthtml)) {
			$resulthtml = '<tr class="oddeven"><td colspan="2" class="opacitymedium" align="center">Aucune sous-tâche</td></tr>';
		}
		if($justAvance)
			return $tot_arr;
		return $resulthtml;
	}













	public function select_all_projets($selected=0,$name='select_',$showempty=1,$val="rowid",$opt="label",$id='',$all=false,$userid){
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
	    $objet = "title";
	    $moreforfilter.='<select onchange="projet_choose_change()" class="flat" id="select_'.$id.'" name="'.$name.'" '.$nodatarole.'>';
	    if ($showempty) $moreforfilter.='<option value="0">&nbsp;</option>';

    	$sql = "SELECT ".$val.",".$opt.",".$objet." FROM ".MAIN_DB_PREFIX."projet";
    	if ($all) {
    		$sql .= " WHERE rowid in (SELECT element_id from ".MAIN_DB_PREFIX."element_contact where fk_socpeople = ".$userid.") OR public = 1";
    	}
    	// echo $sql;
    	$resql = $this->db->query($sql);

		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$moreforfilter.='<option value="'.$obj->$val.'" data-ref="'.$obj->$opt.'"';
	            if ($obj->$val == $selected) $moreforfilter.=' selected';
	            $moreforfilter.='>'.$obj->$opt.' - '.$obj->$objet.'</option>';
			}
			$this->db->free($resql);
		}

	    $moreforfilter.='</select>';
	    $moreforfilter.='<style>#s2id_select_'.$name.'{ width: 100% !important;}</style>';
	    return $moreforfilter;
	}

	public function create($echo_sql=0,$insert)
	{
		$sql  = "INSERT INTO " . MAIN_DB_PREFIX ."".get_class($this)." ( ";
		
		foreach ($insert as $column => $value) {
			$alias = (is_numeric($value)) ? "" : "'";
			$sql_column .= " , `".$column."`";
			$sql_value .= " , ".$alias.$value.$alias;
		}

		$sql .= substr($sql_column, 2)." ) VALUES ( ".substr($sql_value, 2)." )";
		// echo $sql;
		// die();
		// $this->db->begin();
		$resql = $this->db->query($sql);

		// if ($echo_sql)
		// 	echo "<br>".$sql."<br>";

		if (!$resql) {
			$this->db->rollback();
			$this->errors[] = 'Error '.get_class($this).' '. $this->db->lasterror();
			return 0;
		} 
		return $this->db->db->insert_id;
	}

	public function update($id, array $data,$echo_sql=0)
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		if (!$id || $id <= 0)
			return false;

		$sql = 'UPDATE ' . MAIN_DB_PREFIX .get_class($this). ' SET ';

		if (count($data) && is_array($data))
			foreach ($data as $key => $val) {
				$val = is_numeric($val) ? $val : '"'. $val .'"';
				$sql .= '`'. $key. '` = '. $val .',';
			}

		$sql  = substr($sql, 0, -1);
		$sql .= ' WHERE rowid = ' . $id;
		// echo $sql;
		// die();
		if($echo_sql)
			echo "<br>".$sql."<br>";

		// $this->db->begin();

		$resql = $this->db->query($sql);

		if (!$resql) {
			$this->db->rollback();
			// echo 'Error '.get_class($this).' : '. $this->db->lasterror();
			$this->errors[] = 'Error '.get_class($this).' : '. $this->db->lasterror();
			return -1;
		} 
		return 1;
	}


	public function get_item($item,$rowid)
	{
		$sql = "SELECT ".$item." FROM ".MAIN_DB_PREFIX.get_class($this)." WHERE rowid=".$rowid;

		$resql = $this->db->query($sql);
		$item ;

		if ($resql) {
			while ($obj = $this->db->fetch_object($resql)) {
					$item = $obj->item;
			}
			$this->db->free($resql);
			return $item;
		}
	}

    
	public function fetchAll($sortorder = '', $sortfield = '', $limit = 0, $offset = 0, $filter = '', $filtermode = 'AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = "SELECT * FROM ";
		$sql .= MAIN_DB_PREFIX .get_class($this);

		if (!empty($filter)) {
			$sql .= " WHERE 1>0 ".$filter;
		}
		
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
				$line->id_projet 	= $obj->id_projet;
				$line->id_tache 	= $obj->id_tache;
				$line->etat_tache 	= $obj->etat_tache;
				$line->description 	= $obj->description;
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

		$sql = 'SELECT * FROM ' . MAIN_DB_PREFIX .get_class($this). ' WHERE rowid = ' . $id;

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);
				$this->rowid 	= $obj->rowid;
				$this->id_projet 	= $obj->id_projet;
				$this->id_tache 	= $obj->id_tache;
				$this->etat_tache 	= $obj->etat_tache;
				$this->description 	= $obj->description;
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