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

// $build = '121240601';

/**
 * Class ReqKbMainInvoicesSuppliers
 *
 * Put some comments here
 */
class ReqKbMainInvoicesSuppliers extends CommonObject {

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
	 * @var ReqKbMainInvoicesSuppliersLine[] Lines
	 */
	public $lines = array();

	// public $prop1;
	
public $rowid;
public $id;
public $ref;
public $ref_supplier;
public $entity;
public $ref_ext;
public $type;
public $fk_soc;
public $datec;
public $datef;
public $tms;
public $libelle;
public $paye;
public $amount;
public $remise;
public $close_code;
public $close_note;
public $tva;
public $localtax1;
public $localtax2;
public $total;
public $total_ht;
public $total_tva;
public $total_ttc;
public $fk_statut;
public $fk_user_author;
public $fk_user_modif;
public $fk_user_valid;
public $fk_facture_source;
public $fk_projet;
public $fk_account;
public $fk_cond_reglement;
public $fk_mode_reglement;
public $date_lim_reglement;
public $note_private;
public $note_public;
public $fk_incoterms;
public $location_incoterms;
public $model_pdf;
public $import_key;
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
$this->ref = '';
$this->ref_supplier = '';
$this->entity = '';
$this->ref_ext = '';
$this->type = '';
$this->fk_soc = '';
$this->datec = '';
$this->datef = '';
$this->tms = '';
$this->libelle = '';
$this->paye = '';
$this->amount = '';
$this->remise = '';
$this->close_code = '';
$this->close_note = '';
$this->tva = '';
$this->localtax1 = '';
$this->localtax2 = '';
$this->total = '';
$this->total_ht = '';
$this->total_tva = '';
$this->total_ttc = '';
$this->fk_statut = '';
$this->fk_user_author = '';
$this->fk_user_modif = '';
$this->fk_user_valid = '';
$this->fk_facture_source = '';
$this->fk_projet = '';
$this->fk_account = '';
$this->fk_cond_reglement = '';
$this->fk_mode_reglement = '';
$this->date_lim_reglement = '';
$this->note_private = '';
$this->note_public = '';
$this->fk_incoterms = '';
$this->location_incoterms = '';
$this->model_pdf = '';
$this->import_key = '';
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
				$line = new ReqKbMainInvoicesSuppliersLine();

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
$objDest->ref_supplier = $objSource->ref_supplier;
$objDest->entity = $objSource->entity;
$objDest->ref_ext = $objSource->ref_ext;
$objDest->type = $objSource->type;
$objDest->fk_soc = $objSource->fk_soc;
$objDest->datec = $this->db->jdate($objSource->datec);
$objDest->datef = $this->db->jdate($objSource->datef);
$objDest->tms = $this->db->jdate($objSource->tms);
$objDest->libelle = $objSource->libelle;
$objDest->paye = $objSource->paye;
$objDest->amount = $objSource->amount;
$objDest->remise = $objSource->remise;
$objDest->close_code = $objSource->close_code;
$objDest->close_note = $objSource->close_note;
$objDest->tva = $objSource->tva;
$objDest->localtax1 = $objSource->localtax1;
$objDest->localtax2 = $objSource->localtax2;
$objDest->total = $objSource->total;
$objDest->total_ht = $objSource->total_ht;
$objDest->total_tva = $objSource->total_tva;
$objDest->total_ttc = $objSource->total_ttc;
$objDest->fk_statut = $objSource->fk_statut;
$objDest->fk_user_author = $objSource->fk_user_author;
$objDest->fk_user_modif = $objSource->fk_user_modif;
$objDest->fk_user_valid = $objSource->fk_user_valid;
$objDest->fk_facture_source = $objSource->fk_facture_source;
$objDest->fk_projet = $objSource->fk_projet;
$objDest->fk_account = $objSource->fk_account;
$objDest->fk_cond_reglement = $objSource->fk_cond_reglement;
$objDest->fk_mode_reglement = $objSource->fk_mode_reglement;
$objDest->date_lim_reglement = $this->db->jdate($objSource->date_lim_reglement);
$objDest->note_private = $objSource->note_private;
$objDest->note_public = $objSource->note_public;
$objDest->fk_incoterms = $objSource->fk_incoterms;
$objDest->location_incoterms = $objSource->location_incoterms;
$objDest->model_pdf = $objSource->model_pdf;
$objDest->import_key = $objSource->import_key;
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
$sql .= " " . "t.ref AS ref,";
$sql .= " " . "t.ref_supplier AS ref_supplier,";
$sql .= " " . "t.entity AS entity,";
$sql .= " " . "t.ref_ext AS ref_ext,";
$sql .= " " . "t.type AS type,";
$sql .= " " . "t.fk_soc AS fk_soc,";
$sql .= " " . "t.datec AS datec,";
$sql .= " " . "t.datef AS datef,";
$sql .= " " . "t.tms AS tms,";
$sql .= " " . "t.libelle AS libelle,";
$sql .= " " . "t.paye AS paye,";
$sql .= " " . "t.amount AS amount,";
$sql .= " " . "t.remise AS remise,";
$sql .= " " . "t.close_code AS close_code,";
$sql .= " " . "t.close_note AS close_note,";
$sql .= " " . "t.tva AS tva,";
$sql .= " " . "t.localtax1 AS localtax1,";
$sql .= " " . "t.localtax2 AS localtax2,";
$sql .= " " . "t.total AS total,";
$sql .= " " . "t.total_ht AS total_ht,";
$sql .= " " . "t.total_tva AS total_tva,";
$sql .= " " . "t.total_ttc AS total_ttc,";
$sql .= " " . "t.fk_statut AS fk_statut,";
$sql .= " " . "t.fk_user_author AS fk_user_author,";
$sql .= " " . "t.fk_user_modif AS fk_user_modif,";
$sql .= " " . "t.fk_user_valid AS fk_user_valid,";
$sql .= " " . "t.fk_facture_source AS fk_facture_source,";
$sql .= " " . "t.fk_projet AS fk_projet,";
$sql .= " " . "t.fk_account AS fk_account,";
$sql .= " " . "t.fk_cond_reglement AS fk_cond_reglement,";
$sql .= " " . "t.fk_mode_reglement AS fk_mode_reglement,";
$sql .= " " . "t.date_lim_reglement AS date_lim_reglement,";
$sql .= " " . "t.note_private AS note_private,";
$sql .= " " . "t.note_public AS note_public,";
$sql .= " " . "t.fk_incoterms AS fk_incoterms,";
$sql .= " " . "t.location_incoterms AS location_incoterms,";
$sql .= " " . "t.model_pdf AS model_pdf,";
$sql .= " " . "t.import_key AS import_key,";
$sql .= " " . "t.extraparams AS extraparams,";
$sql .= " " . "" . LLX_ . "societe.nom AS societe_nom,";
$sql .= " " . "" . LLX_ . "societe.name_alias AS societe_nom_alias,";
$sql .= " " . "" . LLX_ . "societe.logo AS societe_logo,";
$sql .= " " . "paiement_facture.total_paye AS total_paye,";
$sql .= " " . "COUNT(" . LLX_ . "facture_fourn_det.rowid) AS nbre_lignes,";
$sql .= " " . "COUNT(CASE WHEN " . LLX_ . "product.fk_product_type = 1 THEN 1 ELSE NULL END) AS nbre_services,";
$sql .= " " . "COUNT(CASE WHEN " . LLX_ . "product.fk_product_type = 0 THEN 1 ELSE NULL END) AS nbre_produits";

		$sql .= " FROM ";
		$sql .= "" . LLX_ . "facture_fourn as t   LEFT JOIN " . LLX_ . "societe ON t.fk_soc = " . LLX_ . "societe.rowid   LEFT JOIN " . LLX_ . "facture_fourn_det ON t.rowid = " . LLX_ . "facture_fourn_det.fk_facture_fourn   LEFT JOIN " . LLX_ . "product ON " . LLX_ . "facture_fourn_det.fk_product = " . LLX_ . "product.rowid   LEFT JOIN (     SELECT       " . LLX_ . "paiementfourn_facturefourn.fk_facturefourn AS fk_facturefourn,       SUM(" . LLX_ . "paiementfourn_facturefourn.amount) AS total_paye     FROM " . LLX_ . "paiementfourn_facturefourn      GROUP BY fk_facturefourn    ) AS paiement_facture    ON t.rowid = paiement_facture.fk_facturefourn";

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
		$GROUPBY = "t.rowid,  id,  t.ref,  t.ref_supplier,  t.entity,  t.ref_ext,  t.type,  t.fk_soc,  t.datec,  t.datef,  t.tms,  t.libelle,  t.paye,  t.amount,  t.remise,  t.close_code,  t.close_note,  t.tva,  t.localtax1,  t.localtax2,  t.total,  t.total_ht,  t.total_tva,  t.total_ttc,  t.fk_statut,  t.fk_user_author,    t.fk_user_modif,   t.fk_user_valid,    t.fk_facture_source,  t.fk_projet,  t.fk_account,  t.fk_cond_reglement,  t.fk_mode_reglement,  t.date_lim_reglement,  t.note_private,  t.note_public,  t.fk_incoterms,  t.location_incoterms,  t.model_pdf,  t.import_key,  t.extraparams,   " . LLX_ . "societe.nom,  " . LLX_ . "societe.name_alias,  " . LLX_ . "societe.logo,    paiement_facture.total_paye";
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
		$ORDERBY = "t.datec DESC, t.ref DESC";
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
		$fields_array	 = array('rowid','id','ref','ref_supplier','entity','ref_ext','type','fk_soc','datec','datef','tms','libelle','paye','amount','remise','close_code','close_note','tva','localtax1','localtax2','total','total_ht','total_tva','total_ttc','fk_statut','fk_user_author','fk_user_modif','fk_user_valid','fk_facture_source','fk_projet','fk_account','fk_cond_reglement','fk_mode_reglement','date_lim_reglement','note_private','note_public','fk_incoterms','location_incoterms','model_pdf','import_key','extraparams','societe_nom','societe_nom_alias','societe_logo',);

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
		$fields_array	 = array('rowid','id','ref','ref_supplier','entity','ref_ext','type','fk_soc','datec','datef','tms','libelle','paye','amount','remise','close_code','close_note','tva','localtax1','localtax2','total','total_ht','total_tva','total_ttc','fk_statut','fk_user_author','fk_user_modif','fk_user_valid','fk_facture_source','fk_projet','fk_account','fk_cond_reglement','fk_mode_reglement','date_lim_reglement','note_private','note_public','fk_incoterms','location_incoterms','model_pdf','import_key','extraparams','societe_nom','societe_nom_alias','societe_logo',);

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
 * Class ReqKbMainInvoicesSuppliersLine
 */
class ReqKbMainInvoicesSuppliersLine {

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
public $ref_supplier;
public $entity;
public $ref_ext;
public $type;
public $fk_soc;
public $datec;
public $datef;
public $tms;
public $libelle;
public $paye;
public $amount;
public $remise;
public $close_code;
public $close_note;
public $tva;
public $localtax1;
public $localtax2;
public $total;
public $total_ht;
public $total_tva;
public $total_ttc;
public $fk_statut;
public $fk_user_author;
public $fk_user_modif;
public $fk_user_valid;
public $fk_facture_source;
public $fk_projet;
public $fk_account;
public $fk_cond_reglement;
public $fk_mode_reglement;
public $date_lim_reglement;
public $note_private;
public $note_public;
public $fk_incoterms;
public $location_incoterms;
public $model_pdf;
public $import_key;
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






















