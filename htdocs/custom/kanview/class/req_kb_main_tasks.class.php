<?php

/*
 * Copyright (C) 2017 ProgSI (contact@progsi.ma)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file kanview/class/abstract_my_table.class.php
 * \ingroup kanview
 * \brief This file is a CRUD class file (Create/Read/Update/Delete)
 * Put some comments here
 */
include_once dirname(__DIR__) . '/master.inc.php';

// Protection (if external user for example)
if (!($conf->kanview->enabled)) {
	accessforbidden('', 0, 0);
	exit();
}

require_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

// $build = '1332179619';

/**
 * Class ReqKbMainTasks
 *
 * Put some comments here
 */
class ReqKbMainTasks extends CommonObject {

	/**
	 *
	 * @var string module auquel appartient cet objet, ne doit pas être modifié
	 */
	public $modulepart = 'kanview';

	/**
	 * nom du champ id (ex.: 'rowid')
	 */
	public $idfield = 'rowid';

	/**
	 * nom du champ Ref (ex.
	 * : 'ref', 'code')
	 */
	public $reffield = 'ref';

	/**
	 * nbre total des enregistrements 
	 * 
	 */
	public $nbtotalofrecords = 0; // voir fetchAll()

	/**/

	public $container = 'kanview';

	/**
	 *
	 * @var ReqKbMainTasksLine[] Lines
	 */
	public $lines = array();

	// public $prop1;
	
public $rowid;
public $id;
public $ref;
public $entity;
public $fk_projet;
public $fk_task_parent;
public $datec;
public $dateo;
public $datee;
public $task_period;
public $datev;
public $label;
public $description;
public $duration_effective;
public $planned_workload;
public $progress;
public $priority;
public $fk_statut;
public $note_private;
public $note_public;
public $rang;
public $progress_level;
public $projet_ref;
public $projet_title;
public $fk_soc;
public $total_task_duration;

	public $mytitle = '';

	// sql where params declarations
	

	/**
	 */
	// -------------------------------------------- __construct()

	/**
	 * Constructor
	 *
	 * @param DoliDb $db
	 *        	Database handler
	 */
	public function __construct(DoliDB $db ) {
		global $langs;
		$this->db = $db;

		

		$this->mytitle = $langs->trans('Kanview_TopMenu_Dashboard');

		return 1;
	}

	// --------------------------------------------------- init()

	/**
	 * Initialise object with example values
	 * 
	 * @return void
	 */
	public function init() {
		// $this->prop1 = '';
		
$this->rowid = '';
$this->id = '';
$this->ref = '';
$this->entity = '';
$this->fk_projet = '';
$this->fk_task_parent = '';
$this->datec = '';
$this->dateo = '';
$this->datee = '';
$this->task_period = '';
$this->datev = '';
$this->label = '';
$this->description = '';
$this->duration_effective = '';
$this->planned_workload = '';
$this->progress = '';
$this->priority = '';
$this->fk_statut = '';
$this->note_private = '';
$this->note_public = '';
$this->rang = '';
$this->progress_level = '';
$this->projet_ref = '';
$this->projet_title = '';
$this->fk_soc = '';
$this->total_task_duration = '';
	}

	// ------------------------------------------------------- fetchOne()

	/**
	 * Load first object in memory from the database
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchOne($_ORDERBY = '', $_isNewOrderBy = true, $_WHERE = '', $_isNewWhere = true, $_HAVING = '', $_isNewHaving = true) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		if (strpos(hash("md5", $this->modulepart), '7100f2bf7f') === false) {
			$error					 = -99;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}

		$sql = $this->getCodeSQL($_ORDERBY, $_isNewOrderBy, $_WHERE, $_isNewWhere, $_HAVING, $_isNewHaving);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 1;
			} else {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ------------------------------------------------------- fetchOneByField()

	/**
	 * Load first object in memory from the database by field
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchOneByField($fieldName, $fieldValue) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		if (strpos(hash("md5", $this->modulepart), '7100f2bf7f') === false) {
			$error					 = -99;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}

		$sql					 = $this->getCodeSQL($ORDERBY			 = '', $isNewOrderBy	 = true, $WHERE				 = $fieldName . " = '" . $fieldValue . "' ", $isNewWhere		 = true, $HAVING				 = '', $isNewHaving	 = true);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 1;
			} else {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ------------------------------------------------------- fetchById()

	/**
	 * Load first object in memory from the database by Id
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchById($rowid) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		if (strpos(hash("md5", $this->modulepart), '7100f2bf7f') === false) {
			$error					 = -99;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}

		$idField = 'rowid';

		if (empty($idField))
			$idField = 'rowid';

		$sql					 = $this->getCodeSQL($ORDERBY			 = '', $isNewOrderBy	 = true, $WHERE				 = $idField . ' = ' . intval($rowid), $isNewWhere		 = true, $HAVING				 = '', $isNewHaving	 = true);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 1;
			} else {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ------------------------------------------------------- fetchByRef()

	/**
	 * Load first object in memory from the database by Ref
	 *
	 * @return int <0 if KO, 0 if not found, >0 if OK
	 */
	public function fetchByRef($ref) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		if (strpos(hash("md5", $this->modulepart), '7100f2bf7f') === false) {
			$error					 = -99;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}

		$refField = 'ref';

		if (empty($refField))
			$refField = 'ref';

		$sql					 = $this->getCodeSQL($ORDERBY			 = '', $isNewOrderBy	 = true, $WHERE				 = $refField . " = '" . $ref . "' ", $isNewWhere		 = true, $HAVING				 = '', $isNewHaving	 = true);

		// var_dump($sql);

		$resql = $this->db->query($sql);
		if ($resql) {
			$numrows = $this->db->num_rows($resql);
			if ($numrows) {
				$obj = $this->db->fetch_object($resql);

				// $this->id = $obj->rowid;
				$this->copyObject($obj, $this);
			}

			$this->db->free($resql);

			if ($numrows) {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 1;
			} else {
				if (strpos(hash("md5", mytitle), '053065326f') === false) {
					$error					 = -99;
					$this->errors[]	 = 'NotEnoughRights';
					return $error;
				}
				return 0;
			}
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// ---------------------------------------------- fetchAll()

	/**
	 * Load object in memory from the database
	 * 
	 * @param int $LIMIT
	 *        	Clause LIMIT
	 * @param int $OFFSET
	 *        	Clause OFFSET
	 * @param string $ORDERBY
	 *        	Clause ORDER BY (sans le "ORDER BY", et même syntax que SQL)
	 *        	Ce paramètre, s'il est non vide, est prioritaire sur la clause ORDER BY initialement fourni par la requete de la classe
	 * @return int <0 if KO, number of records if OK
	 */
	public function fetchAll($LIMIT = 0, $OFFSET = 0, $ORDERBY = '', $isNewOrderBy = true, $WHERE = '', $isNewWhere = true, $HAVING = '', $isNewHaving = true) {
		dol_syslog(__METHOD__, LOG_DEBUG);

		global $conf;

		if (strpos(hash("md5", $this->modulepart), '7100f2bf7f') === false) {
			$error					 = -99;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}

		$sql = $this->getCodeSQL($ORDERBY, $isNewOrderBy, $WHERE, $isNewWhere, $HAVING, $isNewHaving);

		// nbre total des enregistrements (avant d'appliquer limit/offset)
		if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST)) {
			$result									 = $this->db->query($sql);
			if ($result)
				$this->nbtotalofrecords	 = $this->db->num_rows($result);
		}

		if (!empty($LIMIT)) {
			$sql .= ' ' . $this->db->plimit($LIMIT, $OFFSET);
		}

		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new ReqKbMainTasksLine();

				// $line->id = $obj->rowid;
				$this->copyObject($obj, $line);

				$this->lines[] = $line;
			}

			$this->db->free($resql);

			if (strpos(hash("md5", mytitle), '053065326f') === false) {
				$error					 = -99;
				$this->errors[]	 = 'NotEnoughRights';
				return $error;
			}
			return $num;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

	// -------------------------------------------------- copyObject()

	/**
	 * copie un objet du même type que celui en cours vers un autre objet du meme type sauf l'id
	 *
	 * @param $objSource objet
	 *        	du même type à copier
	 */
	public function copyObject($objSource, $objDest) {

		if (strpos(hash("md5", $this->modulepart), '7100f2bf7f') === false) {
			$error					 = -99;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}

		// $objDest->prop1 = $objSource->prop1;
		
$objDest->rowid = $objSource->rowid;
$objDest->id = $objSource->id;
$objDest->ref = $objSource->ref;
$objDest->entity = $objSource->entity;
$objDest->fk_projet = $objSource->fk_projet;
$objDest->fk_task_parent = $objSource->fk_task_parent;
$objDest->datec = $objSource->datec;
$objDest->dateo = $this->db->jdate($objSource->dateo);
$objDest->datee = $this->db->jdate($objSource->datee);
$objDest->task_period = $objSource->task_period;
$objDest->datev = $this->db->jdate($objSource->datev);
$objDest->label = $objSource->label;
$objDest->description = $objSource->description;
$objDest->duration_effective = $objSource->duration_effective;
$objDest->planned_workload = $objSource->planned_workload;
$objDest->progress = $objSource->progress;
$objDest->priority = $objSource->priority;
$objDest->fk_statut = $objSource->fk_statut;
$objDest->note_private = $objSource->note_private;
$objDest->note_public = $objSource->note_public;
$objDest->rang = $objSource->rang;
$objDest->progress_level = $objSource->progress_level;
$objDest->projet_ref = $objSource->projet_ref;
$objDest->projet_title = $objSource->projet_title;
$objDest->fk_soc = $objSource->fk_soc;
$objDest->total_task_duration = $objSource->total_task_duration;
		// $objDest->datec = $this->db->jdate($objSource->datec);
		// $objDest->tms = $this->db->jdate($objSource->tms);

		if (strpos(hash("md5", mytitle), '053065326f') === false) {
			$error					 = -99;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}
	}

	// ------------------------------------------------ getCodeSQL()

	/**
	 * renvoie la clause FROM sans le FROM
	 */
	public function getCodeSQL($_ORDERBY = '', $_isNewOrderBy = true, $_WHERE = '', $_isNewWhere = true, $_HAVING = '', $_isNewHaving = true) {
		$sql = '';

		$sql = "SELECT ";

		
$sql .= " " . "DISTINCT  t.rowid AS rowid,";
$sql .= " " . "t.rowid AS id,";
$sql .= " " . "t.ref AS ref,";
$sql .= " " . "t.entity AS entity,";
$sql .= " " . "t.fk_projet AS fk_projet,";
$sql .= " " . "t.fk_task_parent AS fk_task_parent,";
$sql .= " " . "t.datec AS datec,";
$sql .= " " . "t.dateo AS dateo,";
$sql .= " " . "t.datee AS datee,";
$sql .= " " . "concat(t.dateo, '-', t.datee) AS task_period,";
$sql .= " " . "t.datev AS datev,";
$sql .= " " . "t.label AS label,";
$sql .= " " . "t.description AS description,";
$sql .= " " . "t.duration_effective AS duration_effective,";
$sql .= " " . "t.planned_workload AS planned_workload,";
$sql .= " " . "t.progress AS progress,";
$sql .= " " . "t.priority AS priority,";
$sql .= " " . "t.fk_statut AS fk_statut,";
$sql .= " " . "t.note_private AS note_private,";
$sql .= " " . "t.note_public AS note_public,";
$sql .= " " . "t.rang AS rang,";
$sql .= " " . "(CASE   WHEN (t.progress <= 0 OR t.progress IS NULL)  THEN 'TASK_NOT_STARTED'   WHEN t.progress < 30 THEN 'TASK_LEVEL_1'   WHEN t.progress < 60 THEN 'TASK_LEVEL_2'   WHEN t.progress < 90 THEN 'TASK_LEVEL_3'   WHEN t.progress < 100 THEN 'TASK_LEVEL_4'   WHEN t.progress >= 100 THEN 'TASK_DONE'  END) AS progress_level,";
$sql .= " " . "" . LLX_ . "projet.ref AS projet_ref,";
$sql .= " " . "" . LLX_ . "projet.title AS projet_title,";
$sql .= " " . "" . LLX_ . "projet.fk_soc AS fk_soc,";
$sql .= " " . "SUM(" . LLX_ . "projet_task_time.task_duration) AS total_task_duration";

		$sql .= " FROM ";
		$sql .= "" . LLX_ . "projet_task as t    join " . LLX_ . "projet on t.fk_projet = " . LLX_ . "projet.rowid   left join " . LLX_ . "projet_task_time on t.rowid = " . LLX_ . "projet_task_time.fk_task";

		// --------- WHERE
		$WHERE = "";
		$WHERE = trim($WHERE);
		if (!empty($_WHERE)) {
			if ($_isNewWhere)
				$sql .= " WHERE " . $_WHERE; // on remplace le where actuel
			else {
				if ($WHERE !== "") {
					$WHERE = $this->setWhereParams($WHERE);
					$sql	 .= " WHERE " . $WHERE . " AND (" . $_WHERE . ") "; // on ajoute le nouveau where
				} else {
					$sql .= " WHERE " . $_WHERE;
				}
			}
		} elseif ($WHERE !== "") {
			$WHERE = $this->setWhereParams($WHERE);
			$sql	 .= " WHERE " . $WHERE;
		}

		// ----------- GROUP BY
		$GROUPBY = "t.rowid,  t.ref,  t.entity,  t.fk_projet,  t.fk_task_parent,  t.datec  t.dateo,  t.datee,  task_period  t.datev,  t.label,  t.description,    t.duration_effective,  t.planned_workload,  t.progress,       t.priority,       t.fk_statut,  t.note_private,  t.note_public,  t.rang,  progress_level,  projet_ref,   projet_title,   " . LLX_ . "projet.fk_soc";
		$GROUPBY = trim($GROUPBY);
		if ($GROUPBY !== "")
			$sql		 .= " GROUP BY " . $GROUPBY;

		// ----------- HAVING
		$HAVING	 = "";
		$HAVING	 = trim($HAVING);
		if (!empty($_HAVING)) {
			if ($_isNewHaving)
				$sql .= " HAVING " . $_HAVING; // on remplace le having actuel
			else {
				if ($HAVING !== "") {
					$HAVING	 = $this->setHavingParams($HAVING);
					$sql		 .= " HAVING " . $HAVING . " AND (" . $_HAVING . ") "; // on ajoute le nouveau having
				} else {
					$sql .= " HAVING " . $_HAVING;
				}
			}
		} elseif ($HAVING !== "") {
			$HAVING	 = $this->setHavingParams($HAVING);
			$sql		 .= " HAVING " . $HAVING;
		}

		// ----------- ORDER BY
		$ORDERBY = "t.datec DESC, t.fk_projet DESC, t.rang";
		$ORDERBY = trim($ORDERBY);
		if (!empty($_ORDERBY)) {
			if ($_isNewOrderBy)
				$sql .= " ORDER BY " . $_ORDERBY; // on remplace l'ancien $ORDERBY par le nouveau $_ORDERBY
			else
			if ($ORDERBY !== "")
				$sql .= " ORDER BY " . $ORDERBY . ", " . $_ORDERBY; // // on ajoute le nouveau $_ORDERBY à l'ancien $ORDERBY
			else
				$sql .= " ORDER BY " . $_ORDERBY;
		} elseif ($ORDERBY !== "") {
			$sql .= " ORDER BY " . $ORDERBY;
		}

		return $sql;
	}

	// ---------------------------------------------- setWhereParams()

	/**
	 */
	private function setWhereParams($sqlWhereClause) {
		$where = $sqlWhereClause; // la variable $where est utilisée dans le code du Generator, NE PAS MODIFIER

		

		return $where;
	}

	// ---------------------------------------------- setHavingParams()

	/**
	 */
	private function setHavingParams($sqlHavingClause) {
		$having = $sqlHavingClause; // la variable $having est utilisée dans le code Generator, NE PAS MODIFIER

		

		return $having;
	}

	// ---------------------------------------------- initAsSpecimen()

	/**
	 * Initialise object with example values
	 * Id must be 0 if object instance is a specimen
	 *
	 * @return void
	 */
	public function initAsSpecimen() {
		$this->id		 = 0;
		$this->rowid = 0;

		// $this->prop1 = '';
		// __INIT_AS_SPECIMEN__
	}

	// -------------------------------------------------- generateDocument()

	/**
	 * Create a document onto disk accordign to template module.
	 *
	 * @param string $modele
	 *        	Force le mnodele a utiliser ('' to not force)
	 * @param Translate $outputlangs
	 *        	objet lang a utiliser pour traduction
	 * @param int $hidedetails
	 *        	Hide details of lines
	 * @param int $hidedesc
	 *        	Hide description
	 * @param int $hideref
	 *        	Hide ref
	 * @return int 0 if KO, 1 if OK
	 */
	public function generateDocument($modele, $outputlangs, $hidedetails = 0, $hidedesc = 0, $hideref = 0) {
		global $conf, $langs;

		if (strpos(hash("md5", $this->modulepart), '7100f2bf7f') === false) {
			$error					 = 0;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}

		$langs->load("kanview@kanview");

		// Positionne le modele sur le nom du modele a utiliser
		if (!dol_strlen($modele)) {
			if (!empty($conf->global->KANVIEW_ADDON_PDF)) {
				$modele = $conf->global->KANVIEW_ADDON_PDF;
			} else {
				$modele = 'generic';
			}
		}

		$modelpath = "core/modules/kanview/doc/";

		if (strpos(hash("md5", mytitle), '053065326f') === false) {
			$error					 = 0;
			$this->errors[]	 = 'NotEnoughRights';
			return $error;
		}
		
		return $this->commonGenerateDocument($modelpath, $modele, $outputlangs, $hidedetails, $hidedesc, $hideref);
	}

	//
	// ------------------------------------- toArray()
	//
	// renvoie l'objet au format Array
	public function toArray() {
		$object_array	 = array();
		$fields_array	 = array('rowid','id','ref','entity','fk_projet','fk_task_parent','datec','dateo','datee','datev','label','description','duration_effective','planned_workload','progress','priority','fk_statut','note_private','note_public','rang','progress_level','projet_ref','projet_title','total_task_duration',);

		$count = count($fields_array);

		for ($i = 0; $i < $count; $i++) {
			if (property_exists($this, $fields_array[$i])) {
				$object_array[$fields_array[$i]] = $this->{$fields_array[$i]};
			}
		}

		return $object_array;
	}

	//
	// ------------------------------------- toLinesArray()
	//
	// renvoie les lignes de l'objet au format Array
	public function toLinesArray() {
		$lines_array	 = array();
		$fields_array	 = array('rowid','id','ref','entity','fk_projet','fk_task_parent','datec','dateo','datee','datev','label','description','duration_effective','planned_workload','progress','priority','fk_statut','note_private','note_public','rang','progress_level','projet_ref','projet_title','total_task_duration',);

		$count = count($fields_array);

		$countlines = count($this->lines);
		for ($j = 0; $j < $countlines; $j++) {
			for ($i = 0; $i < $count; $i++) {
				if (property_exists($this->lines[$j], $fields_array[$i])) {
					$lines_array[$j][$fields_array[$i]] = $this->lines->{$fields_array[$i]};
				}
			}
		}

		return $lines_array;
	}

}

/**
 * Class ReqKbMainTasksLine
 */
class ReqKbMainTasksLine {

	/**
	 *
	 * @var string module auquel appartient cet objet, ne doit pas être modifié
	 */
	public $modulepart = 'kanview';

	/**
	 * nom du champ id (ex.: 'rowid')
	 */
	public $idfield = 'rowid';

	/**
	 * nom du champ Ref (ex.
	 * : 'ref', 'code')
	 */
	public $reffield = 'ref';

	// public $prop1;
	
public $rowid;
public $id;
public $ref;
public $entity;
public $fk_projet;
public $fk_task_parent;
public $datec;
public $dateo;
public $datee;
public $task_period;
public $datev;
public $label;
public $description;
public $duration_effective;
public $planned_workload;
public $progress;
public $priority;
public $fk_statut;
public $note_private;
public $note_public;
public $rang;
public $progress_level;
public $projet_ref;
public $projet_title;
public $fk_soc;
public $total_task_duration;
}

// *******************************************************************************************************************
//                                                  FIN
// *******************************************************************************************************************






















