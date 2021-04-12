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

// $build = '1332195320';

/**
 * Class ReqKbMainPropals
 *
 * Put some comments here
 */
class ReqKbMainPropals extends CommonObject {

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
	 * @var ReqKbMainPropalsLine[] Lines
	 */
	public $lines = array();

	// public $prop1;
	
public $rowid;
public $id;
public $ref;
public $entity;
public $ref_int;
public $ref_client;
public $fk_soc;
public $fk_projet;
public $tms;
public $datec;
public $datep;
public $fin_validite;
public $date_valid;
public $date_cloture;
public $fk_user_author;
public $fk_user_modif;
public $fk_user_valid;
public $fk_user_cloture;
public $fk_statut;
public $price;
public $remise_absolue;
public $total_ht;
public $tva;
public $localtax1;
public $localtax2;
public $total;
public $fk_account;
public $fk_currency;
public $note_private;
public $note_public;
public $date_livraison;
public $fk_shipping_method;
public $fk_availability;
public $fk_input_reason;
public $location_incoterms;
public $extraparams;
public $fk_delivery_address;
public $societe_nom;
public $societe_name_alias;
public $societe_ref_int;
public $societe_logo;
public $projet_title;

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
$this->ref_int = '';
$this->ref_client = '';
$this->fk_soc = '';
$this->fk_projet = '';
$this->tms = '';
$this->datec = '';
$this->datep = '';
$this->fin_validite = '';
$this->date_valid = '';
$this->date_cloture = '';
$this->fk_user_author = '';
$this->fk_user_modif = '';
$this->fk_user_valid = '';
$this->fk_user_cloture = '';
$this->fk_statut = '';
$this->price = '';
$this->remise_absolue = '';
$this->total_ht = '';
$this->tva = '';
$this->localtax1 = '';
$this->localtax2 = '';
$this->total = '';
$this->fk_account = '';
$this->fk_currency = '';
$this->note_private = '';
$this->note_public = '';
$this->date_livraison = '';
$this->fk_shipping_method = '';
$this->fk_availability = '';
$this->fk_input_reason = '';
$this->location_incoterms = '';
$this->extraparams = '';
$this->fk_delivery_address = '';
$this->societe_nom = '';
$this->societe_name_alias = '';
$this->societe_ref_int = '';
$this->societe_logo = '';
$this->projet_title = '';
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
				$line = new ReqKbMainPropalsLine();

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
$objDest->ref_int = $objSource->ref_int;
$objDest->ref_client = $objSource->ref_client;
$objDest->fk_soc = $objSource->fk_soc;
$objDest->fk_projet = $objSource->fk_projet;
$objDest->tms = $this->db->jdate($objSource->tms);
$objDest->datec = $this->db->jdate($objSource->datec);
$objDest->datep = $this->db->jdate($objSource->datep);
$objDest->fin_validite = $this->db->jdate($objSource->fin_validite);
$objDest->date_valid = $this->db->jdate($objSource->date_valid);
$objDest->date_cloture = $this->db->jdate($objSource->date_cloture);
$objDest->fk_user_author = $objSource->fk_user_author;
$objDest->fk_user_modif = $objSource->fk_user_modif;
$objDest->fk_user_valid = $objSource->fk_user_valid;
$objDest->fk_user_cloture = $objSource->fk_user_cloture;
$objDest->fk_statut = $objSource->fk_statut;
$objDest->price = $objSource->price;
$objDest->remise_absolue = $objSource->remise_absolue;
$objDest->total_ht = $objSource->total_ht;
$objDest->tva = $objSource->tva;
$objDest->localtax1 = $objSource->localtax1;
$objDest->localtax2 = $objSource->localtax2;
$objDest->total = $objSource->total;
$objDest->fk_account = $objSource->fk_account;
$objDest->fk_currency = $objSource->fk_currency;
$objDest->note_private = $objSource->note_private;
$objDest->note_public = $objSource->note_public;
$objDest->date_livraison = $this->db->jdate($objSource->date_livraison);
$objDest->fk_shipping_method = $objSource->fk_shipping_method;
$objDest->fk_availability = $objSource->fk_availability;
$objDest->fk_input_reason = $objSource->fk_input_reason;
$objDest->location_incoterms = $objSource->location_incoterms;
$objDest->extraparams = $objSource->extraparams;
$objDest->fk_delivery_address = $objSource->fk_delivery_address;
$objDest->societe_nom = $objSource->societe_nom;
$objDest->societe_name_alias = $objSource->societe_name_alias;
$objDest->societe_ref_int = $objSource->societe_ref_int;
$objDest->societe_logo = $objSource->societe_logo;
$objDest->projet_title = $objSource->projet_title;
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

		
$sql .= " " . "t.rowid AS rowid,";
$sql .= " " . "t.rowid AS id,";
$sql .= " " . "t.ref AS ref,";
$sql .= " " . "t.entity AS entity,";
$sql .= " " . "t.ref_int AS ref_int,";
$sql .= " " . "t.ref_client AS ref_client,";
$sql .= " " . "t.fk_soc AS fk_soc,";
$sql .= " " . "t.fk_projet AS fk_projet,";
$sql .= " " . "t.tms AS tms,";
$sql .= " " . "t.datec AS datec,";
$sql .= " " . "t.datep AS datep,";
$sql .= " " . "t.fin_validite AS fin_validite,";
$sql .= " " . "t.date_valid AS date_valid,";
$sql .= " " . "t.date_cloture AS date_cloture,";
$sql .= " " . "t.fk_user_author AS fk_user_author,";
$sql .= " " . "t.fk_user_modif AS fk_user_modif,";
$sql .= " " . "t.fk_user_valid AS fk_user_valid,";
$sql .= " " . "t.fk_user_cloture AS fk_user_cloture,";
$sql .= " " . "t.fk_statut AS fk_statut,";
$sql .= " " . "t.price AS price,";
$sql .= " " . "t.remise_absolue AS remise_absolue,";
$sql .= " " . "t.total_ht AS total_ht,";
$sql .= " " . "t.tva AS tva,";
$sql .= " " . "t.localtax1 AS localtax1,";
$sql .= " " . "t.localtax2 AS localtax2,";
$sql .= " " . "t.total AS total,";
$sql .= " " . "t.fk_account AS fk_account,";
$sql .= " " . "t.fk_currency AS fk_currency,";
$sql .= " " . "t.note_private AS note_private,";
$sql .= " " . "t.note_public AS note_public,";
$sql .= " " . "t.date_livraison AS date_livraison,";
$sql .= " " . "t.fk_shipping_method AS fk_shipping_method,";
$sql .= " " . "t.fk_availability AS fk_availability,";
$sql .= " " . "t.fk_input_reason AS fk_input_reason,";
$sql .= " " . "t.location_incoterms AS location_incoterms,";
$sql .= " " . "t.extraparams AS extraparams,";
$sql .= " " . "t.fk_delivery_address AS fk_delivery_address,";
$sql .= " " . "" . LLX_ . "societe.nom AS societe_nom,";
$sql .= " " . "" . LLX_ . "societe.name_alias AS societe_name_alias,";
$sql .= " " . "" . LLX_ . "societe.ref_int AS societe_ref_int,";
$sql .= " " . "" . LLX_ . "societe.logo AS societe_logo,";
$sql .= " " . "" . LLX_ . "projet.title AS projet_title";

		$sql .= " FROM ";
		$sql .= "" . LLX_ . "propal as t    left join " . LLX_ . "societe on t.fk_soc = " . LLX_ . "societe.rowid   left join " . LLX_ . "projet on t.fk_projet = " . LLX_ . "projet.rowid";

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
		$GROUPBY = "";
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
		$ORDERBY = "t.datec DESC";
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
		$fields_array	 = array('rowid','id','ref','entity','ref_int','ref_client','fk_soc','fk_projet','tms','datec','datep','fin_validite','date_valid','date_cloture','fk_user_author','fk_user_modif','fk_user_valid','fk_user_cloture','fk_statut','price','remise_absolue','total_ht','tva','localtax1','localtax2','total','fk_account','fk_currency','note_private','note_public','date_livraison','fk_shipping_method','fk_availability','fk_input_reason','location_incoterms','extraparams','fk_delivery_address','societe_nom','societe_name_alias','societe_ref_int','societe_logo','projet_title',);

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
		$fields_array	 = array('rowid','id','ref','entity','ref_int','ref_client','fk_soc','fk_projet','tms','datec','datep','fin_validite','date_valid','date_cloture','fk_user_author','fk_user_modif','fk_user_valid','fk_user_cloture','fk_statut','price','remise_absolue','total_ht','tva','localtax1','localtax2','total','fk_account','fk_currency','note_private','note_public','date_livraison','fk_shipping_method','fk_availability','fk_input_reason','location_incoterms','extraparams','fk_delivery_address','societe_nom','societe_name_alias','societe_ref_int','societe_logo','projet_title',);

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
 * Class ReqKbMainPropalsLine
 */
class ReqKbMainPropalsLine {

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
public $ref_int;
public $ref_client;
public $fk_soc;
public $fk_projet;
public $tms;
public $datec;
public $datep;
public $fin_validite;
public $date_valid;
public $date_cloture;
public $fk_user_author;
public $fk_user_modif;
public $fk_user_valid;
public $fk_user_cloture;
public $fk_statut;
public $price;
public $remise_absolue;
public $total_ht;
public $tva;
public $localtax1;
public $localtax2;
public $total;
public $fk_account;
public $fk_currency;
public $note_private;
public $note_public;
public $date_livraison;
public $fk_shipping_method;
public $fk_availability;
public $fk_input_reason;
public $location_incoterms;
public $extraparams;
public $fk_delivery_address;
public $societe_nom;
public $societe_name_alias;
public $societe_ref_int;
public $societe_logo;
public $projet_title;
}

// *******************************************************************************************************************
//                                                  FIN
// *******************************************************************************************************************






















