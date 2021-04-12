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

// $build = '1332247724';

/**
 * Class ReqKbMainProspects
 *
 * Put some comments here
 */
class ReqKbMainProspects extends CommonObject {

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
	public $reffield = 'ref_int';

	/**
	 * nbre total des enregistrements 
	 * 
	 */
	public $nbtotalofrecords = 0; // voir fetchAll()

	/**/

	public $container = 'kanview';

	/**
	 *
	 * @var ReqKbMainProspectsLine[] Lines
	 */
	public $lines = array();

	// public $prop1;
	
public $rowid;
public $id;
public $nom;
public $name_alias;
public $entity;
public $ref_ext;
public $ref_int;
public $statut;
public $parent;
public $tms;
public $datec;
public $status;
public $code_client;
public $code_fournisseur;
public $code_compta;
public $code_compta_fournisseur;
public $address;
public $zip;
public $town;
public $fk_departement;
public $fk_pays;
public $fk_account;
public $phone;
public $fax;
public $url;
public $email;
public $skype;
public $fk_effectif;
public $fk_typent;
public $fk_forme_juridique;
public $fk_currency;
public $siren;
public $siret;
public $ape;
public $idprof4;
public $idprof5;
public $idprof6;
public $tva_intra;
public $capital;
public $fk_stcomm;
public $note_private;
public $note_public;
public $model_pdf;
public $prefix_comm;
public $client;
public $fournisseur;
public $supplier_account;
public $fk_prospectlevel;
public $fk_incoterms;
public $location_incoterms;
public $customer_bad;
public $customer_rate;
public $supplier_rate;
public $fk_user_creat;
public $fk_user_modif;
public $remise_client;
public $mode_reglement;
public $cond_reglement;
public $mode_reglement_supplier;
public $cond_reglement_supplier;
public $fk_shipping_method;
public $tva_assuj;
public $barcode;
public $fk_barcode_type;
public $price_level;
public $outstanding_limit;
public $default_lang;
public $logo;
public $code_client_nom;
public $stcomm_code;
public $stcomm_libelle;
public $prospectlevel_label;
public $pays_code;
public $pays_code_iso;
public $pays_label;
public $effectif_code;
public $effectif_libelle;
public $typent_code;
public $typent_libelle;

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
$this->nom = '';
$this->name_alias = '';
$this->entity = '';
$this->ref_ext = '';
$this->ref_int = '';
$this->statut = '';
$this->parent = '';
$this->tms = '';
$this->datec = '';
$this->status = '';
$this->code_client = '';
$this->code_fournisseur = '';
$this->code_compta = '';
$this->code_compta_fournisseur = '';
$this->address = '';
$this->zip = '';
$this->town = '';
$this->fk_departement = '';
$this->fk_pays = '';
$this->fk_account = '';
$this->phone = '';
$this->fax = '';
$this->url = '';
$this->email = '';
$this->skype = '';
$this->fk_effectif = '';
$this->fk_typent = '';
$this->fk_forme_juridique = '';
$this->fk_currency = '';
$this->siren = '';
$this->siret = '';
$this->ape = '';
$this->idprof4 = '';
$this->idprof5 = '';
$this->idprof6 = '';
$this->tva_intra = '';
$this->capital = '';
$this->fk_stcomm = '';
$this->note_private = '';
$this->note_public = '';
$this->model_pdf = '';
$this->prefix_comm = '';
$this->client = '';
$this->fournisseur = '';
$this->supplier_account = '';
$this->fk_prospectlevel = '';
$this->fk_incoterms = '';
$this->location_incoterms = '';
$this->customer_bad = '';
$this->customer_rate = '';
$this->supplier_rate = '';
$this->fk_user_creat = '';
$this->fk_user_modif = '';
$this->remise_client = '';
$this->mode_reglement = '';
$this->cond_reglement = '';
$this->mode_reglement_supplier = '';
$this->cond_reglement_supplier = '';
$this->fk_shipping_method = '';
$this->tva_assuj = '';
$this->barcode = '';
$this->fk_barcode_type = '';
$this->price_level = '';
$this->outstanding_limit = '';
$this->default_lang = '';
$this->logo = '';
$this->code_client_nom = '';
$this->stcomm_code = '';
$this->stcomm_libelle = '';
$this->prospectlevel_label = '';
$this->pays_code = '';
$this->pays_code_iso = '';
$this->pays_label = '';
$this->effectif_code = '';
$this->effectif_libelle = '';
$this->typent_code = '';
$this->typent_libelle = '';
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

		$refField = 'ref_int';

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
				$line = new ReqKbMainProspectsLine();

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
$objDest->nom = $objSource->nom;
$objDest->name_alias = $objSource->name_alias;
$objDest->entity = $objSource->entity;
$objDest->ref_ext = $objSource->ref_ext;
$objDest->ref_int = $objSource->ref_int;
$objDest->statut = $objSource->statut;
$objDest->parent = $objSource->parent;
$objDest->tms = $this->db->jdate($objSource->tms);
$objDest->datec = $this->db->jdate($objSource->datec);
$objDest->status = $objSource->status;
$objDest->code_client = $objSource->code_client;
$objDest->code_fournisseur = $objSource->code_fournisseur;
$objDest->code_compta = $objSource->code_compta;
$objDest->code_compta_fournisseur = $objSource->code_compta_fournisseur;
$objDest->address = $objSource->address;
$objDest->zip = $objSource->zip;
$objDest->town = $objSource->town;
$objDest->fk_departement = $objSource->fk_departement;
$objDest->fk_pays = $objSource->fk_pays;
$objDest->fk_account = $objSource->fk_account;
$objDest->phone = $objSource->phone;
$objDest->fax = $objSource->fax;
$objDest->url = $objSource->url;
$objDest->email = $objSource->email;
$objDest->skype = $objSource->skype;
$objDest->fk_effectif = $objSource->fk_effectif;
$objDest->fk_typent = $objSource->fk_typent;
$objDest->fk_forme_juridique = $objSource->fk_forme_juridique;
$objDest->fk_currency = $objSource->fk_currency;
$objDest->siren = $objSource->siren;
$objDest->siret = $objSource->siret;
$objDest->ape = $objSource->ape;
$objDest->idprof4 = $objSource->idprof4;
$objDest->idprof5 = $objSource->idprof5;
$objDest->idprof6 = $objSource->idprof6;
$objDest->tva_intra = $objSource->tva_intra;
$objDest->capital = $objSource->capital;
$objDest->fk_stcomm = $objSource->fk_stcomm;
$objDest->note_private = $objSource->note_private;
$objDest->note_public = $objSource->note_public;
$objDest->model_pdf = $objSource->model_pdf;
$objDest->prefix_comm = $objSource->prefix_comm;
$objDest->client = $objSource->client;
$objDest->fournisseur = $objSource->fournisseur;
$objDest->supplier_account = $objSource->supplier_account;
$objDest->fk_prospectlevel = $objSource->fk_prospectlevel;
$objDest->fk_incoterms = $objSource->fk_incoterms;
$objDest->location_incoterms = $objSource->location_incoterms;
$objDest->customer_bad = $objSource->customer_bad;
$objDest->customer_rate = $objSource->customer_rate;
$objDest->supplier_rate = $objSource->supplier_rate;
$objDest->fk_user_creat = $objSource->fk_user_creat;
$objDest->fk_user_modif = $objSource->fk_user_modif;
$objDest->remise_client = $objSource->remise_client;
$objDest->mode_reglement = $objSource->mode_reglement;
$objDest->cond_reglement = $objSource->cond_reglement;
$objDest->mode_reglement_supplier = $objSource->mode_reglement_supplier;
$objDest->cond_reglement_supplier = $objSource->cond_reglement_supplier;
$objDest->fk_shipping_method = $objSource->fk_shipping_method;
$objDest->tva_assuj = $objSource->tva_assuj;
$objDest->barcode = $objSource->barcode;
$objDest->fk_barcode_type = $objSource->fk_barcode_type;
$objDest->price_level = $objSource->price_level;
$objDest->outstanding_limit = $objSource->outstanding_limit;
$objDest->default_lang = $objSource->default_lang;
$objDest->logo = $objSource->logo;
$objDest->code_client_nom = $objSource->code_client_nom;
$objDest->stcomm_code = $objSource->stcomm_code;
$objDest->stcomm_libelle = $objSource->stcomm_libelle;
$objDest->prospectlevel_label = $objSource->prospectlevel_label;
$objDest->pays_code = $objSource->pays_code;
$objDest->pays_code_iso = $objSource->pays_code_iso;
$objDest->pays_label = $objSource->pays_label;
$objDest->effectif_code = $objSource->effectif_code;
$objDest->effectif_libelle = $objSource->effectif_libelle;
$objDest->typent_code = $objSource->typent_code;
$objDest->typent_libelle = $objSource->typent_libelle;
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
$sql .= " " . "t.nom AS nom,";
$sql .= " " . "t.name_alias AS name_alias,";
$sql .= " " . "t.entity AS entity,";
$sql .= " " . "t.ref_ext AS ref_ext,";
$sql .= " " . "t.ref_int AS ref_int,";
$sql .= " " . "t.statut AS statut,";
$sql .= " " . "t.parent AS parent,";
$sql .= " " . "t.tms AS tms,";
$sql .= " " . "t.datec AS datec,";
$sql .= " " . "t.status AS status,";
$sql .= " " . "t.code_client AS code_client,";
$sql .= " " . "t.code_fournisseur AS code_fournisseur,";
$sql .= " " . "t.code_compta AS code_compta,";
$sql .= " " . "t.code_compta_fournisseur AS code_compta_fournisseur,";
$sql .= " " . "t.address AS address,";
$sql .= " " . "t.zip AS zip,";
$sql .= " " . "t.town AS town,";
$sql .= " " . "t.fk_departement AS fk_departement,";
$sql .= " " . "t.fk_pays AS fk_pays,";
$sql .= " " . "t.fk_account AS fk_account,";
$sql .= " " . "t.phone AS phone,";
$sql .= " " . "t.fax AS fax,";
$sql .= " " . "t.url AS url,";
$sql .= " " . "t.email AS email,";
$sql .= " " . "t.skype AS skype,";
$sql .= " " . "t.fk_effectif AS fk_effectif,";
$sql .= " " . "t.fk_typent AS fk_typent,";
$sql .= " " . "t.fk_forme_juridique AS fk_forme_juridique,";
$sql .= " " . "t.fk_currency AS fk_currency,";
$sql .= " " . "t.siren AS siren,";
$sql .= " " . "t.siret AS siret,";
$sql .= " " . "t.ape AS ape,";
$sql .= " " . "t.idprof4 AS idprof4,";
$sql .= " " . "t.idprof5 AS idprof5,";
$sql .= " " . "t.idprof6 AS idprof6,";
$sql .= " " . "t.tva_intra AS tva_intra,";
$sql .= " " . "t.capital AS capital,";
$sql .= " " . "t.fk_stcomm AS fk_stcomm,";
$sql .= " " . "t.note_private AS note_private,";
$sql .= " " . "t.note_public AS note_public,";
$sql .= " " . "t.model_pdf AS model_pdf,";
$sql .= " " . "t.prefix_comm AS prefix_comm,";
$sql .= " " . "t.client AS client,";
$sql .= " " . "t.fournisseur AS fournisseur,";
$sql .= " " . "t.supplier_account AS supplier_account,";
$sql .= " " . "t.fk_prospectlevel AS fk_prospectlevel,";
$sql .= " " . "t.fk_incoterms AS fk_incoterms,";
$sql .= " " . "t.location_incoterms AS location_incoterms,";
$sql .= " " . "t.customer_bad AS customer_bad,";
$sql .= " " . "t.customer_rate AS customer_rate,";
$sql .= " " . "t.supplier_rate AS supplier_rate,";
$sql .= " " . "t.fk_user_creat AS fk_user_creat,";
$sql .= " " . "t.fk_user_modif AS fk_user_modif,";
$sql .= " " . "t.remise_client AS remise_client,";
$sql .= " " . "t.mode_reglement AS mode_reglement,";
$sql .= " " . "t.cond_reglement AS cond_reglement,";
$sql .= " " . "t.mode_reglement_supplier AS mode_reglement_supplier,";
$sql .= " " . "t.cond_reglement_supplier AS cond_reglement_supplier,";
$sql .= " " . "t.fk_shipping_method AS fk_shipping_method,";
$sql .= " " . "t.tva_assuj AS tva_assuj,";
$sql .= " " . "t.barcode AS barcode,";
$sql .= " " . "t.fk_barcode_type AS fk_barcode_type,";
$sql .= " " . "t.price_level AS price_level,";
$sql .= " " . "t.outstanding_limit AS outstanding_limit,";
$sql .= " " . "t.default_lang AS default_lang,";
$sql .= " " . "t.logo AS logo,";
$sql .= " " . "concat(t.code_client, '-', t.nom) AS code_client_nom,";
$sql .= " " . "" . LLX_ . "c_stcomm.code AS stcomm_code,";
$sql .= " " . "" . LLX_ . "c_stcomm.libelle AS stcomm_libelle,";
$sql .= " " . "" . LLX_ . "c_prospectlevel.label AS prospectlevel_label,";
$sql .= " " . "" . LLX_ . "c_country.code AS pays_code,";
$sql .= " " . "" . LLX_ . "c_country.code_iso AS pays_code_iso,";
$sql .= " " . "" . LLX_ . "c_country.label AS pays_label,";
$sql .= " " . "" . LLX_ . "c_effectif.code AS effectif_code,";
$sql .= " " . "" . LLX_ . "c_effectif.libelle AS effectif_libelle,";
$sql .= " " . "" . LLX_ . "c_typent.code AS typent_code,";
$sql .= " " . "" . LLX_ . "c_typent.libelle AS typent_libelle";

		$sql .= " FROM ";
		$sql .= "" . LLX_ . "societe as t    left join " . LLX_ . "c_stcomm on t.fk_stcomm = " . LLX_ . "c_stcomm.id   left join " . LLX_ . "c_prospectlevel on t.fk_prospectlevel = " . LLX_ . "c_prospectlevel.code   left join " . LLX_ . "c_country on t.fk_pays = " . LLX_ . "c_country.rowid   left join " . LLX_ . "c_effectif on t.fk_effectif = " . LLX_ . "c_effectif.id   left join " . LLX_ . "c_typent on t.fk_typent = " . LLX_ . "c_typent.id";

		// --------- WHERE
		$WHERE = " t.fournisseur = 0   and   t.client = 2";
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
		$ORDERBY = "t.datec DESC, t.nom";
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
		$fields_array	 = array('rowid','id','nom','name_alias','entity','ref_ext','ref_int','statut','parent','tms','datec','status','code_client','code_fournisseur','code_compta','code_compta_fournisseur','address','zip','town','fk_departement','fk_pays','fk_account','phone','fax','url','email','skype','fk_effectif','fk_typent','fk_forme_juridique','fk_currency','siren','siret','ape','idprof4','idprof5','idprof6','tva_intra','capital','fk_stcomm','note_private','note_public','model_pdf','prefix_comm','client','fournisseur','supplier_account','fk_prospectlevel','fk_incoterms','location_incoterms','customer_bad','customer_rate','supplier_rate','fk_user_creat','fk_user_modif','remise_client','mode_reglement','cond_reglement','mode_reglement_supplier','cond_reglement_supplier','fk_shipping_method','tva_assuj','barcode','fk_barcode_type','price_level','outstanding_limit','default_lang','logo','code_client_nom','stcomm_code','stcomm_libelle','prospectlevel_label','pays_code','pays_code_iso','pays_label','effectif_code','effectif_libelle','typent_code','typent_libelle',);

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
		$fields_array	 = array('rowid','id','nom','name_alias','entity','ref_ext','ref_int','statut','parent','tms','datec','status','code_client','code_fournisseur','code_compta','code_compta_fournisseur','address','zip','town','fk_departement','fk_pays','fk_account','phone','fax','url','email','skype','fk_effectif','fk_typent','fk_forme_juridique','fk_currency','siren','siret','ape','idprof4','idprof5','idprof6','tva_intra','capital','fk_stcomm','note_private','note_public','model_pdf','prefix_comm','client','fournisseur','supplier_account','fk_prospectlevel','fk_incoterms','location_incoterms','customer_bad','customer_rate','supplier_rate','fk_user_creat','fk_user_modif','remise_client','mode_reglement','cond_reglement','mode_reglement_supplier','cond_reglement_supplier','fk_shipping_method','tva_assuj','barcode','fk_barcode_type','price_level','outstanding_limit','default_lang','logo','code_client_nom','stcomm_code','stcomm_libelle','prospectlevel_label','pays_code','pays_code_iso','pays_label','effectif_code','effectif_libelle','typent_code','typent_libelle',);

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
 * Class ReqKbMainProspectsLine
 */
class ReqKbMainProspectsLine {

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
	public $reffield = 'ref_int';

	// public $prop1;
	
public $rowid;
public $id;
public $nom;
public $name_alias;
public $entity;
public $ref_ext;
public $ref_int;
public $statut;
public $parent;
public $tms;
public $datec;
public $status;
public $code_client;
public $code_fournisseur;
public $code_compta;
public $code_compta_fournisseur;
public $address;
public $zip;
public $town;
public $fk_departement;
public $fk_pays;
public $fk_account;
public $phone;
public $fax;
public $url;
public $email;
public $skype;
public $fk_effectif;
public $fk_typent;
public $fk_forme_juridique;
public $fk_currency;
public $siren;
public $siret;
public $ape;
public $idprof4;
public $idprof5;
public $idprof6;
public $tva_intra;
public $capital;
public $fk_stcomm;
public $note_private;
public $note_public;
public $model_pdf;
public $prefix_comm;
public $client;
public $fournisseur;
public $supplier_account;
public $fk_prospectlevel;
public $fk_incoterms;
public $location_incoterms;
public $customer_bad;
public $customer_rate;
public $supplier_rate;
public $fk_user_creat;
public $fk_user_modif;
public $remise_client;
public $mode_reglement;
public $cond_reglement;
public $mode_reglement_supplier;
public $cond_reglement_supplier;
public $fk_shipping_method;
public $tva_assuj;
public $barcode;
public $fk_barcode_type;
public $price_level;
public $outstanding_limit;
public $default_lang;
public $logo;
public $code_client_nom;
public $stcomm_code;
public $stcomm_libelle;
public $prospectlevel_label;
public $pays_code;
public $pays_code_iso;
public $pays_label;
public $effectif_code;
public $effectif_libelle;
public $typent_code;
public $typent_libelle;
}

// *******************************************************************************************************************
//                                                  FIN
// *******************************************************************************************************************






















