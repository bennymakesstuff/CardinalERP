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

// $build = '1332222322';

/**
 * Class ReqKbMainInvoices
 *
 * Put some comments here
 */
class ReqKbMainInvoices extends CommonObject {

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
	public $reffield = 'facnumber';

	/**
	 * nbre total des enregistrements 
	 * 
	 */
	public $nbtotalofrecords = 0; // voir fetchAll()

	/**/

	public $container = 'kanview';

	/**
	 *
	 * @var ReqKbMainInvoicesLine[] Lines
	 */
	public $lines = array();

	// public $prop1;
	
public $rowid;
public $id;
public $facnumber;
public $entity;
public $ref_ext;
public $ref_int;
public $ref_client;
public $type;
public $increment;
public $fk_soc;
public $datec;
public $datef;
public $date_pointoftax;
public $date_valid;
public $tms;
public $paye;
public $amount;
public $remise_percent;
public $remise_absolue;
public $remise;
public $close_code;
public $close_note;
public $tva;
public $localtax1;
public $localtax2;
public $revenuestamp;
public $total;
public $total_ttc;
public $fk_statut;
public $fk_user_author;
public $fk_user_modif;
public $fk_user_valid;
public $fk_facture_source;
public $fk_cond_reglement;
public $fk_mode_reglement;
public $date_lim_reglement;
public $note_private;
public $note_public;
public $situation_cycle_ref;
public $situation_counter;
public $situation_final;
public $extraparams;
public $societe_nom;
public $societe_nom_alias;
public $societe_logo;
public $total_paye;
public $nbre_lignes;
public $nbre_services;
public $nbre_produits;

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
$this->facnumber = '';
$this->entity = '';
$this->ref_ext = '';
$this->ref_int = '';
$this->ref_client = '';
$this->type = '';
$this->increment = '';
$this->fk_soc = '';
$this->datec = '';
$this->datef = '';
$this->date_pointoftax = '';
$this->date_valid = '';
$this->tms = '';
$this->paye = '';
$this->amount = '';
$this->remise_percent = '';
$this->remise_absolue = '';
$this->remise = '';
$this->close_code = '';
$this->close_note = '';
$this->tva = '';
$this->localtax1 = '';
$this->localtax2 = '';
$this->revenuestamp = '';
$this->total = '';
$this->total_ttc = '';
$this->fk_statut = '';
$this->fk_user_author = '';
$this->fk_user_modif = '';
$this->fk_user_valid = '';
$this->fk_facture_source = '';
$this->fk_cond_reglement = '';
$this->fk_mode_reglement = '';
$this->date_lim_reglement = '';
$this->note_private = '';
$this->note_public = '';
$this->situation_cycle_ref = '';
$this->situation_counter = '';
$this->situation_final = '';
$this->extraparams = '';
$this->societe_nom = '';
$this->societe_nom_alias = '';
$this->societe_logo = '';
$this->total_paye = '';
$this->nbre_lignes = '';
$this->nbre_services = '';
$this->nbre_produits = '';
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

		$refField = 'facnumber';

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
				$line = new ReqKbMainInvoicesLine();

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
$objDest->facnumber = $objSource->facnumber;
$objDest->entity = $objSource->entity;
$objDest->ref_ext = $objSource->ref_ext;
$objDest->ref_int = $objSource->ref_int;
$objDest->ref_client = $objSource->ref_client;
$objDest->type = $objSource->type;
$objDest->increment = $objSource->increment;
$objDest->fk_soc = $objSource->fk_soc;
$objDest->datec = $this->db->jdate($objSource->datec);
$objDest->datef = $this->db->jdate($objSource->datef);
$objDest->date_pointoftax = $this->db->jdate($objSource->date_pointoftax);
$objDest->date_valid = $this->db->jdate($objSource->date_valid);
$objDest->tms = $this->db->jdate($objSource->tms);
$objDest->paye = $objSource->paye;
$objDest->amount = $objSource->amount;
$objDest->remise_percent = $objSource->remise_percent;
$objDest->remise_absolue = $objSource->remise_absolue;
$objDest->remise = $objSource->remise;
$objDest->close_code = $objSource->close_code;
$objDest->close_note = $objSource->close_note;
$objDest->tva = $objSource->tva;
$objDest->localtax1 = $objSource->localtax1;
$objDest->localtax2 = $objSource->localtax2;
$objDest->revenuestamp = $objSource->revenuestamp;
$objDest->total = $objSource->total;
$objDest->total_ttc = $objSource->total_ttc;
$objDest->fk_statut = $objSource->fk_statut;
$objDest->fk_user_author = $objSource->fk_user_author;
$objDest->fk_user_modif = $objSource->fk_user_modif;
$objDest->fk_user_valid = $objSource->fk_user_valid;
$objDest->fk_facture_source = $objSource->fk_facture_source;
$objDest->fk_cond_reglement = $objSource->fk_cond_reglement;
$objDest->fk_mode_reglement = $objSource->fk_mode_reglement;
$objDest->date_lim_reglement = $this->db->jdate($objSource->date_lim_reglement);
$objDest->note_private = $objSource->note_private;
$objDest->note_public = $objSource->note_public;
$objDest->situation_cycle_ref = $objSource->situation_cycle_ref;
$objDest->situation_counter = $objSource->situation_counter;
$objDest->situation_final = $objSource->situation_final;
$objDest->extraparams = $objSource->extraparams;
$objDest->societe_nom = $objSource->societe_nom;
$objDest->societe_nom_alias = $objSource->societe_nom_alias;
$objDest->societe_logo = $objSource->societe_logo;
$objDest->total_paye = $objSource->total_paye;
$objDest->nbre_lignes = $objSource->nbre_lignes;
$objDest->nbre_services = $objSource->nbre_services;
$objDest->nbre_produits = $objSource->nbre_produits;
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
$sql .= " " . "t.facnumber AS facnumber,";
$sql .= " " . "t.entity AS entity,";
$sql .= " " . "t.ref_ext AS ref_ext,";
$sql .= " " . "t.ref_int AS ref_int,";
$sql .= " " . "t.ref_client AS ref_client,";
$sql .= " " . "t.type AS type,";
$sql .= " " . "t.increment AS increment,";
$sql .= " " . "t.fk_soc AS fk_soc,";
$sql .= " " . "t.datec AS datec,";
$sql .= " " . "t.datef AS datef,";
$sql .= " " . "t.date_pointoftax AS date_pointoftax,";
$sql .= " " . "t.date_valid AS date_valid,";
$sql .= " " . "t.tms AS tms,";
$sql .= " " . "t.paye AS paye,";
$sql .= " " . "t.amount AS amount,";
$sql .= " " . "t.remise_percent AS remise_percent,";
$sql .= " " . "t.remise_absolue AS remise_absolue,";
$sql .= " " . "t.remise AS remise,";
$sql .= " " . "t.close_code AS close_code,";
$sql .= " " . "t.close_note AS close_note,";
$sql .= " " . "t.tva AS tva,";
$sql .= " " . "t.localtax1 AS localtax1,";
$sql .= " " . "t.localtax2 AS localtax2,";
$sql .= " " . "t.revenuestamp AS revenuestamp,";
$sql .= " " . "t.total AS total,";
$sql .= " " . "t.total_ttc AS total_ttc,";
$sql .= " " . "t.fk_statut AS fk_statut,";
$sql .= " " . "t.fk_user_author AS fk_user_author,";
$sql .= " " . "t.fk_user_modif AS fk_user_modif,";
$sql .= " " . "t.fk_user_valid AS fk_user_valid,";
$sql .= " " . "t.fk_facture_source AS fk_facture_source,";
$sql .= " " . "t.fk_cond_reglement AS fk_cond_reglement,";
$sql .= " " . "t.fk_mode_reglement AS fk_mode_reglement,";
$sql .= " " . "t.date_lim_reglement AS date_lim_reglement,";
$sql .= " " . "t.note_private AS note_private,";
$sql .= " " . "t.note_public AS note_public,";
$sql .= " " . "t.situation_cycle_ref AS situation_cycle_ref,";
$sql .= " " . "t.situation_counter AS situation_counter,";
$sql .= " " . "t.situation_final AS situation_final,";
$sql .= " " . "t.extraparams AS extraparams,";
$sql .= " " . "" . LLX_ . "societe.nom AS societe_nom,";
$sql .= " " . "" . LLX_ . "societe.name_alias AS societe_nom_alias,";
$sql .= " " . "" . LLX_ . "societe.logo AS societe_logo,";
$sql .= " " . "paiement_facture.total_paye AS total_paye,";
$sql .= " " . "count(" . LLX_ . "facturedet.rowid) AS nbre_lignes,";
$sql .= " " . "count(case when " . LLX_ . "product.fk_product_type = 1 then 1 else NULL END) AS nbre_services,";
$sql .= " " . "COUNT(CASE WHEN " . LLX_ . "product.fk_product_type = 0 THEN 1 ELSE NULL END) AS nbre_produits";

		$sql .= " FROM ";
		$sql .= "" . LLX_ . "facture as t    left join " . LLX_ . "societe on t.fk_soc = " . LLX_ . "societe.rowid   left join " . LLX_ . "facturedet on t.rowid = " . LLX_ . "facturedet.fk_facture   left join " . LLX_ . "product on " . LLX_ . "facturedet.fk_product = " . LLX_ . "product.rowid   left join (     select       " . LLX_ . "paiement_facture.fk_facture as fk_facture,       SUM(" . LLX_ . "paiement_facture.amount) AS total_paye     FROM " . LLX_ . "paiement_facture      group by fk_facture    ) AS paiement_facture    on t.rowid = paiement_facture.fk_facture";

		// --------- WHERE
		$WHERE = "  t.type = 0";
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
		$GROUPBY = "t.rowid,  id,  t.facnumber,  t.entity,  t.ref_ext,    t.ref_int,    t.ref_client,  t.type,  t.increment,   t.fk_soc,   t.datec,  t.datef,  t.date_pointoftax,  t.date_valid,  t.tms,  t.paye,  t.amount,  t.remise_percent,  t.remise_absolue,  t.remise,  t.close_code,   t.close_note,  t.tva,  t.localtax1,   t.localtax2,  t.revenuestamp,  t.total,  t.total_ttc,   t.fk_statut,   t.fk_user_author,  t.fk_user_modif,  t.fk_user_valid,  t.fk_facture_source,  t.fk_cond_reglement,  t.fk_mode_reglement,  t.date_lim_reglement,  t.note_private,  t.note_public,  t.situation_cycle_ref,  t.situation_counter,  t.situation_final,  t.extraparams,   societe_nom,  societe_nom_alias,     societe_logo,       total_paye";
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
		$ORDERBY = "t.datec DESC, t.facnumber DESC";
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
		$fields_array	 = array('rowid','id','facnumber','entity','ref_ext','ref_int','ref_client','type','increment','fk_soc','datec','datef','date_pointoftax','date_valid','tms','paye','amount','remise_percent','remise_absolue','remise','close_code','close_note','tva','localtax1','localtax2','revenuestamp','total','total_ttc','fk_statut','fk_user_author','fk_user_modif','fk_user_valid','fk_facture_source','fk_cond_reglement','fk_mode_reglement','date_lim_reglement','note_private','note_public','situation_cycle_ref','situation_counter','situation_final','extraparams','societe_nom','societe_nom_alias','societe_logo','total_paye','nbre_lignes','nbre_services','nbre_produits',);

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
		$fields_array	 = array('rowid','id','facnumber','entity','ref_ext','ref_int','ref_client','type','increment','fk_soc','datec','datef','date_pointoftax','date_valid','tms','paye','amount','remise_percent','remise_absolue','remise','close_code','close_note','tva','localtax1','localtax2','revenuestamp','total','total_ttc','fk_statut','fk_user_author','fk_user_modif','fk_user_valid','fk_facture_source','fk_cond_reglement','fk_mode_reglement','date_lim_reglement','note_private','note_public','situation_cycle_ref','situation_counter','situation_final','extraparams','societe_nom','societe_nom_alias','societe_logo','total_paye','nbre_lignes','nbre_services','nbre_produits',);

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
 * Class ReqKbMainInvoicesLine
 */
class ReqKbMainInvoicesLine {

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
	public $reffield = 'facnumber';

	// public $prop1;
	
public $rowid;
public $id;
public $facnumber;
public $entity;
public $ref_ext;
public $ref_int;
public $ref_client;
public $type;
public $increment;
public $fk_soc;
public $datec;
public $datef;
public $date_pointoftax;
public $date_valid;
public $tms;
public $paye;
public $amount;
public $remise_percent;
public $remise_absolue;
public $remise;
public $close_code;
public $close_note;
public $tva;
public $localtax1;
public $localtax2;
public $revenuestamp;
public $total;
public $total_ttc;
public $fk_statut;
public $fk_user_author;
public $fk_user_modif;
public $fk_user_valid;
public $fk_facture_source;
public $fk_cond_reglement;
public $fk_mode_reglement;
public $date_lim_reglement;
public $note_private;
public $note_public;
public $situation_cycle_ref;
public $situation_counter;
public $situation_final;
public $extraparams;
public $societe_nom;
public $societe_nom_alias;
public $societe_logo;
public $total_paye;
public $nbre_lignes;
public $nbre_services;
public $nbre_produits;
}

// *******************************************************************************************************************
//                                                  FIN
// *******************************************************************************************************************






















