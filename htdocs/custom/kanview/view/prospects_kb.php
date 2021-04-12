<?php

/* Copyright (C) 2018   ProgSI  (contact@progsi.ma)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// __LOCKED__

/**
 * \file
 * \ingroup agenda
 * \brief Home page of kanban events
 */
require_once dirname(__DIR__) . '/main.inc.php';

// Protection 
if (!hasPermissionForKanbanView('prospects')) {
	accessforbidden();
	exit();
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

$build = '173013918';

// ------------------------------------------- Params 

$view		 = GETPOST("view", '', 3); // type de vue : 
if (empty($view))
	$view		 = 'standard';
$action	 = GETPOST('action', 'alpha');
if (empty($action))
	$action	 = 'show';

// paramètres filtres additionnels
//off//
$search_rowid					 = GETPOST('search_rowid', 'alpha');
$search_nom						 = GETPOST('search_nom', 'alpha');
$search_name_alias		 = GETPOST('search_name_alias', 'alpha');
$search_entity				 = GETPOST('search_entity', 'int');
$search_ref_ext				 = GETPOST('search_ref_ext', 'alpha');
$search_ref_int				 = GETPOST('search_ref_int', 'alpha');
$search_statut				 = GETPOST('search_statut', 'int');
$search_parent				 = GETPOST('search_parent', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_tms_day				 = GETPOST('search_tms_day', 'int');
$search_tms_month			 = GETPOST('search_tms_month', 'int');
$search_tms_year			 = GETPOST('search_tms_year', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
//$search_datec_day				 = GETPOST('search_datec_day', 'int');
//$search_datec_month				 = GETPOST('search_datec_month', 'int');
//$search_datec_year				 = GETPOST('search_datec_year', 'int');
// datec - date début
$search_dd_datec_day	 = str_pad(GETPOST('search_dd_datec_day', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_month = str_pad(GETPOST('search_dd_datec_month', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_year	 = str_pad(GETPOST('search_dd_datec_year', 'alpha'), 4, '0', STR_PAD_LEFT);
$search_dd_datec_hour	 = str_pad(GETPOST('search_dd_datec_hour', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_min	 = str_pad(GETPOST('search_dd_datec_min', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_sec	 = str_pad(GETPOST('search_dd_datec_sec', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec			 = dol_stringtotime($search_dd_datec_year . $search_dd_datec_month . $search_dd_datec_day . $search_dd_datec_hour . $search_dd_datec_min . $search_dd_datec_sec, 0);
$search_dd_datec_mysql = dol_print_date($search_dd_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
// 1er affichage, par défaut : la Date début est 6 mois précédents
if ((empty($search_dd_datec_year) || $search_dd_datec_year == '0000') && (empty($search_dd_datec_month) || $search_dd_datec_month == '00') && (empty($search_dd_datec_day) || $search_dd_datec_day == '00')) {
	$ddTmp								 = $db->idate(dol_time_plus_duree(dol_now('tzserver') + (60 * 60 * 24), -6, 'm')); // format timstamp puis format : yyyymmddhhiiss
	$search_dd_datec_year	 = substr($ddTmp, 0, 4);
	$search_dd_datec_month = substr($ddTmp, 4, 2);
	$search_dd_datec_day	 = substr($ddTmp, 6, 2);

	$search_dd_datec			 = dol_stringtotime($search_dd_datec_year . $search_dd_datec_month . $search_dd_datec_day . $search_dd_datec_hour . $search_dd_datec_min . $search_dd_datec_sec, 0);
	$search_dd_datec_mysql = dol_print_date($search_dd_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
}
// datec - date fin
$search_df_datec_day	 = str_pad(GETPOST('search_df_datec_day', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_month = str_pad(GETPOST('search_df_datec_month', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_year	 = str_pad(GETPOST('search_df_datec_year', 'alpha'), 4, '0', STR_PAD_LEFT);
$search_df_datec_hour	 = str_pad(GETPOST('search_df_datec_hour', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_min	 = str_pad(GETPOST('search_df_datec_min', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_df_datec_sec	 = str_pad(GETPOST('search_df_datec_sec', 'alpha'), 2, '0', STR_PAD_LEFT);
// si l'heure n'est pas fourni, on la règle de façon à ce que la journée de "date fin" soit incluse
if (empty($search_df_datec_hour) || $search_df_datec_hour == '00')
	$search_df_datec_hour	 = '23';
if (empty($search_df_datec_min) || $search_df_datec_min == '00')
	$search_df_datec_min	 = '59';
if (empty($search_df_datec_sec) || $search_df_datec_sec == '00')
	$search_df_datec_sec	 = '59';
$search_df_datec			 = dol_stringtotime($search_df_datec_year . $search_df_datec_month . $search_df_datec_day . $search_df_datec_hour . $search_df_datec_min . $search_df_datec_sec, 0);
$search_df_datec_mysql = dol_print_date($search_df_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
// 1er affichage, par défaut : la Date fin est le mois flottant suivant
if ((empty($search_df_datec_year) || $search_df_datec_year == '0000') && (empty($search_df_datec_month) || $search_df_datec_month == '00') && (empty($search_df_datec_day) || $search_df_datec_day == '00')) {
	$search_df_datec_year	 = date('Y');
	$search_df_datec_month = date('m');
	$search_df_datec_day	 = date('d');
	// $next_month				 = dol_get_next_month($search_df_datec_month, $search_df_datec_year);
	$search_df_datec_month = str_pad($search_df_datec_month, 2, '0', STR_PAD_LEFT);
	$search_df_datec_year	 = str_pad($search_df_datec_year, 4, '0', STR_PAD_LEFT);

	// $tmp				 = dol_get_prev_day(intval($search_df_datec_day), intval($next_month['month']), intval($next_month['year']));
	// $search_df_datec_day = str_pad($tmp['day'], 2, '0', STR_PAD_LEFT);
// si l'heure n'est pas fourni, on la règle de façon à ce que la journée de "date fin" soit incluse
	if (empty($search_df_datec_hour) || $search_df_datec_hour == '00')
		$search_df_datec_hour	 = '23';
	if (empty($search_df_datec_min) || $search_df_datec_min == '00')
		$search_df_datec_min	 = '59';
	if (empty($search_df_datec_sec) || $search_df_datec_sec == '00')
		$search_df_datec_sec	 = '59';
	$search_df_datec			 = dol_stringtotime($search_df_datec_year . $search_df_datec_month . $search_df_datec_day . $search_df_datec_hour . $search_df_datec_min . $search_df_datec_sec, 0);
	$search_df_datec_mysql = dol_print_date($search_df_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
}

$search_status									 = GETPOST('search_status', 'int');
$search_code_client							 = GETPOST('search_code_client', 'alpha');
$search_address									 = GETPOST('search_address', 'alpha');
$search_zip											 = GETPOST('search_zip', 'alpha');
$search_town										 = GETPOST('search_town', 'alpha');
$search_fk_pays									 = GETPOST('search_fk_pays', 'int');
$search_fk_departement					 = GETPOST('search_fk_departement', 'int');
$search_fk_stcomm								 = GETPOST('search_fk_stcomm', 'int');
$search_note_private						 = GETPOST('search_note_private', 'alpha');
$search_note_public							 = GETPOST('search_note_public', 'alpha');
$search_prefix_comm							 = GETPOST('search_prefix_comm', 'alpha');
$search_client									 = GETPOST('search_client', 'int');
$search_fk_prospectlevel				 = GETPOST('search_fk_prospectlevel', 'alpha');
$search_customer_bad						 = GETPOST('search_customer_bad', 'int');
$search_customer_rate						 = GETPOST('search_customer_rate', 'alpha');
$search_supplier_rate						 = GETPOST('search_supplier_rate', 'alpha');
$search_logo										 = GETPOST('search_logo', 'alpha');
$search_url											 = GETPOST('search_url', 'alpha');
$search_email										 = GETPOST('search_email', 'alpha');
$search_skype										 = GETPOST('search_skype', 'alpha');
$search_fk_effectif							 = GETPOST('search_fk_effectif', 'int');
$search_fk_typent								 = GETPOST('search_fk_typent', 'int');
$search_fk_forme_juridique			 = GETPOST('search_fk_forme_juridique', 'int');
$search_fk_currency							 = GETPOST('search_fk_currency', 'alpha');
$search_siren										 = GETPOST('search_siren', 'alpha');
$search_siret										 = GETPOST('search_siret', 'alpha');
$search_ape											 = GETPOST('search_ape', 'alpha');
$search_idprof4									 = GETPOST('search_idprof4', 'alpha');
$search_idprof5									 = GETPOST('search_idprof5', 'alpha');
$search_idprof6									 = GETPOST('search_idprof6', 'alpha');
$search_tva_intra								 = GETPOST('search_tva_intra', 'alpha');
$search_capital									 = GETPOST('search_capital', 'alpha');
$search_model_pdf								 = GETPOST('search_model_pdf', 'alpha');
$search_fournisseur							 = GETPOST('search_fournisseur', 'int');
$search_supplier_account				 = GETPOST('search_supplier_account', 'alpha');
$search_fk_incoterms						 = GETPOST('search_fk_incoterms', 'int');
$search_location_incoterms			 = GETPOST('search_location_incoterms', 'alpha');
$search_fk_user_creat						 = GETPOST('search_fk_user_creat', 'int');
$search_fk_user_modif						 = GETPOST('search_fk_user_modif', 'int');
$search_remise_client						 = GETPOST('search_remise_client', 'alpha');
$search_mode_reglement					 = GETPOST('search_mode_reglement', 'int');
$search_cond_reglement					 = GETPOST('search_cond_reglement', 'int');
$search_mode_reglement_supplier	 = GETPOST('search_mode_reglement_supplier', 'int');
$search_cond_reglement_supplier	 = GETPOST('search_cond_reglement_supplier', 'int');
$search_fk_shipping_method			 = GETPOST('search_fk_shipping_method', 'int');
$search_tva_assuj								 = GETPOST('search_tva_assuj', 'int');
$search_barcode									 = GETPOST('search_barcode', 'alpha');
$search_fk_barcode_type					 = GETPOST('search_fk_barcode_type', 'int');
$search_price_level							 = GETPOST('search_price_level', 'int');
$search_outstanding_limit				 = GETPOST('search_outstanding_limit', 'alpha');
$search_default_lang						 = GETPOST('search_default_lang', 'alpha');
$search_id											 = GETPOST('search_id', 'alpha');
$search_code_fournisseur				 = GETPOST('search_code_fournisseur', 'alpha');
$search_code_compta							 = GETPOST('search_code_compta', 'alpha');
$search_code_compta_fournisseur	 = GETPOST('search_code_compta_fournisseur', 'alpha');
$search_fk_account							 = GETPOST('search_fk_account', 'int');
$search_phone										 = GETPOST('search_phone', 'alpha');
$search_fax											 = GETPOST('search_fax', 'alpha');

// --- current view
$default_current_view	 = '';
$current_view					 = GETPOST('current_view', 'alpha'); // TODO : ajouter mécanisme pour modfier le hidden input current_view lorsque l'utilisateur sélectionne une vue dans le Kanban
if (empty($current_view) || !in_array($current_view, array('day', 'week', 'workweek', 'month', 'agenda')))
	$current_view					 = $default_current_view;

// read only ?
$read_only = false;

// paramètres additionnels
// __GETPOST_ADDITIONNELS__

$langs->load("kanview@kanview");
$langs->load("companies");
$langs->load("commercial");
$langs->load("other");

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'my_prefix_kanban'));

// ------------ récupération des données (ce code doit rester avant les actions car utilisé par printPDF())
// sortfield, sortorder, page, limit et offset
$sortfield = GETPOST("sortfield", 'alpha');
$sortorder = GETPOST("sortorder", 'alpha');
$page			 = GETPOST("page", "int");
if ($page == - 1) {
	$page = 0;
}
$limit	 = GETPOST('limit') ? GETPOST('limit', 'int') : $conf->liste_limit;
$offset	 = (int) $limit * (int) $page;
$offset	 = ($offset > 0 ? $offset - 1 : 0);

// effacement de la recherche si demandée
// doit rester avant le calcul des WHERE/HAVING
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) { // All test are required to be compatible with all browsers
	// ------------------------------------------------------
	// $search_prop1 = '';
	$search_rowid			 = '';
	$search_nom				 = '';
	$search_name_alias = '';
	$search_entity		 = '';
	$search_ref_ext		 = '';
	$search_ref_int		 = '';
	$search_statut		 = '';
	$search_parent		 = '';
	$search_tms_day		 = '';
	$search_tms_month	 = '';
	$search_tms_year	 = '';

//	$search_datec_day				 = '';
//	$search_datec_month				 = '';
//	$search_datec_year				 = '';
	// datec - date début
	$search_dd_datec_day	 = '';
	$search_dd_datec_month = '';
	$search_dd_datec_year	 = '';
	$search_dd_datec_hour	 = '';
	$search_dd_datec_min	 = '';
	$search_dd_datec_sec	 = '';
	$search_dd_datec			 = '';
	$search_dd_datec_mysql = '';
// datec - date fin
	$search_df_datec_day	 = '';
	$search_df_datec_month = '';
	$search_df_datec_year	 = '';
	$search_df_datec_hour	 = '';
	$search_df_datec_min	 = '';
	$search_df_datec_sec	 = '';
	$search_df_datec			 = '';
	$search_df_datec_mysql = '';

	$search_status									 = '';
	$search_code_client							 = '';
	$search_address									 = '';
	$search_zip											 = '';
	$search_town										 = '';
	$search_fk_pays									 = '';
	$search_fk_departement					 = '';
	$search_fk_stcomm								 = '';
	$search_note_private						 = '';
	$search_note_public							 = '';
	$search_prefix_comm							 = '';
	$search_client									 = '';
	$search_fk_prospectlevel				 = '';
	$search_customer_bad						 = '';
	$search_customer_rate						 = '';
	$search_supplier_rate						 = '';
	$search_logo										 = '';
	$search_url											 = '';
	$search_email										 = '';
	$search_skype										 = '';
	$search_fk_effectif							 = '';
	$search_fk_typent								 = '';
	$search_fk_forme_juridique			 = '';
	$search_fk_currency							 = '';
	$search_siren										 = '';
	$search_siret										 = '';
	$search_ape											 = '';
	$search_idprof4									 = '';
	$search_idprof5									 = '';
	$search_idprof6									 = '';
	$search_tva_intra								 = '';
	$search_capital									 = '';
	$search_model_pdf								 = '';
	$search_fournisseur							 = '';
	$search_supplier_account				 = '';
	$search_fk_incoterms						 = '';
	$search_location_incoterms			 = '';
	$search_fk_user_creat						 = '';
	$search_fk_user_modif						 = '';
	$search_remise_client						 = '';
	$search_mode_reglement					 = '';
	$search_cond_reglement					 = '';
	$search_mode_reglement_supplier	 = '';
	$search_cond_reglement_supplier	 = '';
	$search_fk_shipping_method			 = '';
	$search_tva_assuj								 = '';
	$search_barcode									 = '';
	$search_fk_barcode_type					 = '';
	$search_price_level							 = '';
	$search_outstanding_limit				 = '';
	$search_default_lang						 = '';
	$search_id											 = '';
	$search_code_fournisseur				 = '';
	$search_code_compta							 = '';
	$search_code_compta_fournisseur	 = '';
	$search_fk_account							 = '';
	$search_phone										 = '';
	$search_fax											 = '';


	// -----------------------------------------------------

	$search_array_options = array();
}

$container = 'kanview';

// ***************************************************************************************************************
// 
//                                                 Actions Part 1  - avant collecte de données
// 
// ***************************************************************************************************************
// 
// -------------------- action après Drag&Drop d'une tuile ==> mise à jour du "Status" de l'objet
// 
if ($action == 'cardDrop') {

	require_once DOL_DOCUMENT_ROOT . '/societe/class/societe.class.php';
	$response		 = array();
	$object			 = new Societe($db);
	$id					 = GETPOST('id', 'int');
	$newStatusID = GETPOST('newStatusID');
	$err				 = 0;
	if ($id > 0 && !empty($newStatusID)) {
		$ret = $object->fetch($id);
		if ($ret > 0) {
			// traitements additionnels 
			switch ($newStatusID) {
				case 'PROSPECT_NOT_CONTACTED':
					$object->status			 = 1;
					$object->stcomm_id	 = 0;
					break;
				case 'PROSPECT_DONT_CONTACT':
					$object->status			 = 1;
					$object->stcomm_id	 = -1;
					break;
				case 'PROSPECT_TO_CONTACT':
					$object->status			 = 1;
					$object->stcomm_id	 = 1;
					break;
				case 'PROSPECT_CONTACT_INPROGRESS':
					$object->status			 = 1;
					$object->stcomm_id	 = 2;
					break;
				case 'PROSPECT_CONTACTED':
					$object->status			 = 1;
					$object->stcomm_id	 = 3;
					break;
				case 'PROSPECT_INACTIVE':
					$object->status			 = 0;
					break;
				default:
					$response['status']	 = 'KO';
					$response['message'] = $langs->trans("ActionNotAllowed");
					break;
			}

			if (!$err) {
				$res = $object->update($id, $user);
				if ($res > 0) {
					$response['status']	 = 'OK';
					$response['message'] = $langs->trans("RecordUpdatedSuccessfully");
				} else {
					$response['status']	 = 'KO';
					$response['message'] = $object->error;
				}
			}
		} elseif ($ret == 0) {
			dol_syslog('RecordNotFound : project : ' . $id, LOG_DEBUG);
			$response['status']	 = 'KO';
			$response['message'] = $langs->trans("RecordNotFound");
		} elseif ($ret < 0) {
			dol_syslog($object->error, LOG_DEBUG);
			$response['status']	 = 'KO';
			$response['message'] = $object->error;
		}
	} else {
		$response['status']	 = 'KO';
		$response['message'] = $langs->trans('IncorrectParameter');
	}

	$response['token'] = $_SESSION['newtoken'];
	
	// envoi de la réponse se fait plus loin après collecte des données pour pouvoir ajouter le nbre d'éléments par colonnes
	// exit(json_encode($response));
}

// 
// --------------------- action countryChange,  on remplit la liste des départements du pays
// 
elseif ($action === 'countryChange') {

	$idpays = GETPOST('idpays', 'int');

	if ((!empty($idpays)) || $idpays == 0) {
		$SQL								 = "
							SELECT t.rowid, t.nom, t.fk_region, " . LLX_ . "c_regions.fk_pays
							FROM " . LLX_ . "c_departements as t
								LEFT JOIN " . LLX_ . "c_regions ON t.fk_region = " . LLX_ . "c_regions.rowid 
							WHERE t.active = 1 AND " . LLX_ . "c_regions.fk_pays = " . ($idpays > 0 ? $idpays : -1) . " 
							ORDER BY t.nom
							";
		$sql_fk_departement	 = $db->query($SQL);
		$options						 = array();
		$optionSelected			 = '';
		$defaultValue				 = '';
		$blanckOption				 = 1;
		$out								 = '';
		$err								 = 0;
		if ($sql_fk_departement) {
			while ($obj_fk_departement = $db->fetch_object($sql_fk_departement)) {
				if ($obj_fk_departement->rowid == $search_fk_departement) {
					$optionSelected = $obj_fk_departement->rowid;
				}
				$options[$obj_fk_departement->rowid] = $obj_fk_departement->nom;
			}
			$db->free($sql_fk_departement);
		} else {
			dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
			setEventMessages($langs->trans($db->lasterror()), null, 'errors');
			$err++;
		}
		// echo '<select  id="fk_departement_input_filtre" class="flat" name="search_fk_departement" title="">';
		if (empty($optionSelected) && !$blanckOption) {
			$optionSelected = $defaultValue;
		}
		if ($blanckOption) {
			$out .= '<option value="" ' . (empty($search_fk_departement) ? 'selected' : '') . '></option>';
		}
		foreach ($options as $key => $value) {
			$out .= '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $value . '</option>';
		}
		// echo '</select>';
	} else {
		setEventMessages($langs->trans('IncorrectParameter'), null, 'errors');
		$err++;
	}

	if ($err > 0)
		print "ERROR:"; // permet à JS de savoir qu'une erreur s'est produite dans les traitements serveur pour annuler le Drop, 

		
// le code JS doit supprimer cette chaine de la réponse finale avant d'afficher la réponse à l'utilisateur
	dol_htmloutput_events();
	exit($out);
}

// 
// --------------------------------------------------- action non gérée
// 
elseif (!empty($action) && $action !== 'show') { // si on reçoit une action non gérée, on quitte
	exit('ActionNotAllowed');
}

// **************************************************************************************************************
//
//                                         Kanban - Collecte de données
//
// ***************************************************************************************************************
// 
//
// --------------------------- Requête principale (doit rester avant les actions)
//
// ----------- WHERE, HAVING et ORDER BY

$WHERE	 = " 1 = 1 ";
$HAVING	 = " 1 = 1 ";

// --- filtre période
// $WHERE .= " AND __FIELD_START_TIME__ >= '" . $date_debut_str . "' ";
// $WHERE .= " AND __FIELD_END_TIME__ <= '" . $date_fin_str . "' ";
/// ---
//
//

if ($search_rowid != '')
	$WHERE .= natural_search("t.rowid", $search_rowid, 1);
if ($search_nom != '')
	$WHERE .= natural_search("t.nom", $search_nom);
if ($search_name_alias != '')
	$WHERE .= natural_search("t.name_alias", $search_name_alias);
if ($search_entity != '')
	$WHERE .= natural_search("t.entity", $search_entity, 1);
if ($search_ref_ext != '')
	$WHERE .= natural_search("t.ref_ext", $search_ref_ext);
if ($search_ref_int != '')
	$WHERE .= natural_search("t.ref_int", $search_ref_int);
if ($search_statut != '')
	$WHERE .= natural_search("t.statut", $search_statut, 1);
if ($search_parent != '')
	$WHERE .= natural_search("t.parent", $search_parent, 1);
if ($search_tms_month > 0) {
	if ($search_tms_year > 0 && empty($search_tms_day))
		$WHERE .= " AND t.tms BETWEEN '" . $db->idate(dol_get_first_day($search_tms_year, $search_tms_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_tms_year, $search_tms_month, false)) . "'";
	else if ($search_tms_year > 0 && !empty($search_tms_day))
		$WHERE .= " AND t.tms BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_tms_month, $search_tms_day, $search_tms_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_tms_month, $search_tms_day, $search_tms_year)) . "'";
	else
		$WHERE .= " AND date_format(t.tms, '%m') = '" . $search_tms_month . "'";
}
else if ($search_tms_year > 0) {
	$WHERE .= " AND t.tms BETWEEN '" . $db->idate(dol_get_first_day($search_tms_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_tms_year, 12, false)) . "'";
}

//if ($search_datec_month > 0) {
//	if ($search_datec_year > 0 && empty($search_datec_day))
//		$WHERE	 .= " AND t.datec BETWEEN '" . $db->idate(dol_get_first_day($search_datec_year, $search_datec_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datec_year, $search_datec_month, false)) . "'";
//	else if ($search_datec_year > 0 && !empty($search_datec_day))
//		$WHERE	 .= " AND t.datec BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_datec_month, $search_datec_day, $search_datec_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_datec_month, $search_datec_day, $search_datec_year)) . "'";
//	else
//		$WHERE	 .= " AND date_format(t.datec, '%m') = '" . $search_datec_month . "'";
//}
//else if ($search_datec_year > 0) {
//	$WHERE .= " AND t.datec BETWEEN '" . $db->idate(dol_get_first_day($search_datec_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datec_year, 12, false)) . "'";
//}

if ($search_dd_datec_mysql != '' && $search_df_datec_mysql != '') {
	// si date début et date fin sont dans le mauvais ordre, on les inverse
	if ($search_dd_datec_mysql > $search_df_datec_mysql) {
		$tmp									 = $search_dd_datec_mysql;
		$search_dd_datec_mysql = $search_df_datec_mysql;
		$search_df_datec_mysql = $tmp;
	}

	$WHERE .= " AND (t.datec BETWEEN '" . $search_dd_datec_mysql . "' AND '" . $search_df_datec_mysql . "')";
}

if ($search_status != '')
	$WHERE .= natural_search("t.status", $search_status, 1);
if ($search_code_client != '')
	$WHERE .= natural_search("t.code_client", $search_code_client);
if ($search_address != '')
	$WHERE .= natural_search("t.address", $search_address);
if ($search_zip != '')
	$WHERE .= natural_search("t.zip", $search_zip);
if ($search_town != '')
	$WHERE .= natural_search("t.town", $search_town);
if (intval($search_fk_pays) > 0)
	$WHERE .= " AND t.fk_pays = " . intval($search_fk_pays, 1); // natural_search("t.fk_pays", $search_fk_pays);
if (intval($search_fk_departement) > 0)
	$WHERE .= " AND t.fk_departement = " . intval($fk_departement, 1); // natural_search("t.fk_departement", $search_fk_departement);
if ($search_fk_stcomm != '')
	$WHERE .= natural_search("t.fk_stcomm", $search_fk_stcomm, 1);
if ($search_note_private != '')
	$WHERE .= natural_search("t.note_private", $search_note_private);
if ($search_note_public != '')
	$WHERE .= natural_search("t.note_public", $search_note_public);
if ($search_prefix_comm != '')
	$WHERE .= natural_search("t.prefix_comm", $search_prefix_comm);
if ($search_client != '')
	$WHERE .= natural_search("t.client", $search_client);
if ($search_fk_prospectlevel != '')
	$WHERE .= natural_search("t.fk_prospectlevel", $search_fk_prospectlevel); // ATTENTION : llx_societe.fk_prospectlevel n'est pas numérique mais égale à une des valuers de llx_c_prospectlevel.code  (PL_HIGH, PL_LOW, PL_MEDIUM, PL_NONE, ...)
if ($search_customer_bad != '')
	$WHERE .= natural_search("t.customer_bad", $search_customer_bad);
if ($search_customer_rate != '')
	$WHERE .= natural_search("t.customer_rate", $search_customer_rate);
if ($search_supplier_rate != '')
	$WHERE .= natural_search("t.supplier_rate", $search_supplier_rate);
if ($search_logo != '')
	$WHERE .= natural_search("t.logo", $search_logo);
if ($search_url != '')
	$WHERE .= natural_search("t.url", $search_url);
if ($search_email != '')
	$WHERE .= natural_search("t.email", $search_email);
if ($search_skype != '')
	$WHERE .= natural_search("t.skype", $search_skype);
if ($search_fk_effectif != '')
	$WHERE .= natural_search("t.fk_effectif", $search_fk_effectif, 1);
if ($search_fk_typent != '')
	$WHERE .= natural_search("t.fk_typent", $search_fk_typent, 1);
if ($search_fk_forme_juridique != '')
	$WHERE .= natural_search("t.fk_forme_juridique", $search_fk_forme_juridique, 1);
if ($search_fk_currency != '')
	$WHERE .= natural_search("t.fk_currency", $search_fk_currency, 1);
if ($search_siren != '')
	$WHERE .= natural_search("t.siren", $search_siren);
if ($search_siret != '')
	$WHERE .= natural_search("t.siret", $search_siret);
if ($search_ape != '')
	$WHERE .= natural_search("t.ape", $search_ape);
if ($search_idprof4 != '')
	$WHERE .= natural_search("t.idprof4", $search_idprof4);
if ($search_idprof5 != '')
	$WHERE .= natural_search("t.idprof5", $search_idprof5);
if ($search_idprof6 != '')
	$WHERE .= natural_search("t.idprof6", $search_idprof6);
if ($search_tva_intra != '')
	$WHERE .= natural_search("t.tva_intra", $search_tva_intra);
if ($search_capital != '')
	$WHERE .= natural_search("t.capital", $search_capital);
if ($search_model_pdf != '')
	$WHERE .= natural_search("t.model_pdf", $search_model_pdf);
if ($search_fournisseur != '')
	$WHERE .= natural_search("t.fournisseur", $search_fournisseur);
if ($search_supplier_account != '')
	$WHERE .= natural_search("t.supplier_account", $search_supplier_account);
if ($search_fk_incoterms != '')
	$WHERE .= natural_search("t.fk_incoterms", $search_fk_incoterms, 1);
if ($search_location_incoterms != '')
	$WHERE .= natural_search("t.location_incoterms", $search_location_incoterms);
if ($search_fk_user_creat != '')
	$WHERE .= natural_search("t.fk_user_creat", $search_fk_user_creat, 1);
if ($search_fk_user_modif != '')
	$WHERE .= natural_search("t.fk_user_modif", $search_fk_user_modif, 1);
if ($search_remise_client != '')
	$WHERE .= natural_search("t.remise_client", $search_remise_client);
if ($search_mode_reglement != '')
	$WHERE .= natural_search("t.mode_reglement", $search_mode_reglement);
if ($search_cond_reglement != '')
	$WHERE .= natural_search("t.cond_reglement", $search_cond_reglement);
if ($search_mode_reglement_supplier != '')
	$WHERE .= natural_search("t.mode_reglement_supplier", $search_mode_reglement_supplier);
if ($search_cond_reglement_supplier != '')
	$WHERE .= natural_search("t.cond_reglement_supplier", $search_cond_reglement_supplier);
if ($search_fk_shipping_method != '')
	$WHERE .= natural_search("t.fk_shipping_method", $search_fk_shipping_method, 1);
if ($search_tva_assuj != '')
	$WHERE .= natural_search("t.tva_assuj", $search_tva_assuj);
if ($search_barcode != '')
	$WHERE .= natural_search("t.barcode", $search_barcode);
if ($search_fk_barcode_type != '')
	$WHERE .= natural_search("t.fk_barcode_type", $search_fk_barcode_type, 1);
if ($search_price_level != '')
	$WHERE .= natural_search("t.price_level", $search_price_level);
if ($search_outstanding_limit != '')
	$WHERE .= natural_search("t.outstanding_limit", $search_outstanding_limit);
if ($search_default_lang != '')
	$WHERE .= natural_search("t.default_lang", $search_default_lang);
if ($search_id != '')
	$WHERE .= natural_search("t.rowid", $search_id);
if ($search_code_fournisseur != '')
	$WHERE .= natural_search("t.code_fournisseur", $search_code_fournisseur);
if ($search_code_compta != '')
	$WHERE .= natural_search("t.code_compta", $search_code_compta);
if ($search_code_compta_fournisseur != '')
	$WHERE .= natural_search("t.code_compta_fournisseur", $search_code_compta_fournisseur);
if ($search_fk_account != '')
	$WHERE .= natural_search("t.fk_account", $search_fk_account, 1);
if ($search_phone != '')
	$WHERE .= natural_search("t.phone", $search_phone);
if ($search_fax != '')
	$WHERE .= natural_search("t.fax", $search_fax);


$ORDERBY	 = '';
if (empty($sortorder))
	$sortorder = 'ASC';
if (empty($sortfield))
	$sortfield = '1'; //
if ((!empty($sortfield)) && (!empty($sortorder))) {
	$ORDERBY = $sortfield . ' ' . $sortorder;
}

if ($WHERE == ' 1 = 1 ')
	$WHERE	 = '';
if ($HAVING == ' 1 = 1 ')
	$HAVING	 = '';

// ----- exécution de la requete principale (doit rester avant les actions)

$dataArray = array(); // array of events

include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_main_prospects.class.php';
$ReqObject = new ReqKbMainProspects($db);
$num			 = 0;
// les "isNew" sont à "false" parce qu'on veut garder les paramétrage de la requete d'origine
$num							 = $ReqObject->fetchAll($limit, $offset, $ORDERBY, $isNewOrderBy			 = false, $WHERE, $isNewWhere				 = false, $HAVING, $isNewHaving			 = false);
$nbtotalofrecords	 = $ReqObject->nbtotalofrecords;

// --------------------------- Requête fournissant les titres des colonnes
// 
$titlesValues			 = ""
		. "PROSPECT_NOT_CONTACTED,"
		. "PROSPECT_DONT_CONTACT,"
		. "PROSPECT_TO_CONTACT,"
		. "PROSPECT_CONTACT_INPROGRESS,"
		. "PROSPECT_CONTACTED,"
		. "PROSPECT_INACTIVE,"
		. "PROSPECT_STATUS_UNKNOWN";
$columnsArray			 = array();
$columnsIDsArray	 = array(); // tableau associatid : 'titre' => 'son id', ça nous permet de retrouver (côté js) les ids des Statuts en fonction de leur code 
$columnsCountArray = array(); // tableau associatif : 'titre' => 'nbre d'éléments' dans la colonne (incrémenté dans la boucle de parcours des données principales) 
$columnsTitles		 = explode(",", $titlesValues);
$countColumns			 = count($columnsTitles);
if ($countColumns > 0) {
	for ($i = 0; $i < $countColumns; $i++) {
		$columnsArray[$i]['headerText']				 = $langs->trans($columnsTitles[$i]);
		$columnsArray[$i]['key']							 = $columnsTitles[$i];
		$columnsArray[$i]['allowDrag']				 = true;
		$columnsArray[$i]['allowDrop']				 = true;
		$columnsIDsArray[$columnsTitles[$i]]	 = $columnsTitles[$i];
		$columnsCountArray[$columnsTitles[$i]] = 0; // sera incrémenté dans la boucle de parcours des données principales ci-dessous
		// traitements additionnels 
		if ($columnsArray[$i]['key'] === "PROSPECT_INACTIVE") {
			// $columnsArray[$i]['allowDrag'] = false;
			// $columnsArray[$i]['allowDrop'] = false;
		} elseif ($columnsArray[$i]['key'] === "PROSPECT_STATUS_UNKNOWN") {
			$columnsArray[$i]['allowDrop'] = false;
		}
	}
} else {
	dol_syslog('ColumnsTitles not supplied', LOG_ERR);
	setEventMessages("ColumnsTitlesNotSupplied", null, 'errors');
}

/// ---
// --------------------------- données principales
if(!empty($conf->global->KANVIEW_SHOW_PICTO))
	$fieldImageUrl = 'logo';
else
	$fieldImageUrl = '';

if ($num >= 0) {

	// ----------------------------
	// if ($search_fk_salairegroupe != '')
	// $params .= '&amp;search_fk_salairegroupe=' . urlencode($search_fk_salairegroupe);
	// __PARAM_SEARCH_PROP__
	// ---------------- données

	$i = 0;
	// parcours des résultas 
	while ($i < $num) {
		$obj = $ReqObject->lines[$i];

		// $dataArray[$i]['nom_field'] = $obj->nom_field; 
		$dataArray[$i]['priority']						 = - $obj->datec; // date création timestamp inversé pour le trie descendant des cartes du kanban, voir fields.priority
		$dataArray[$i]['fk_stcomm']						 = $obj->fk_stcomm;
		$dataArray[$i]['stcomm_code']					 = $obj->stcomm_code;
		$dataArray[$i]['stcomm_libelle']			 = $obj->stcomm_libelle;
		$dataArray[$i]['fk_prospectlevel']		 = $obj->fk_prospectlevel; // code du level
		$dataArray[$i]['prospectlevel_label']	 = $obj->prospectlevel_label;
		$dataArray[$i]['note_private']				 = $obj->note_private;
		$dataArray[$i]['note_public']					 = $obj->note_public;
		$dataArray[$i]['prefix_comm']					 = $obj->prefix_comm;
		$dataArray[$i]['client']							 = $obj->client;
		$dataArray[$i]['customer_bad']				 = $obj->customer_bad;
		$dataArray[$i]['customer_rate']				 = $obj->customer_rate;
		$dataArray[$i]['supplier_rate']				 = $obj->supplier_rate;
		$dataArray[$i]['logo']								 = $obj->logo;
		$dataArray[$i]['rowid']								 = $obj->rowid;
		$dataArray[$i]['nom']									 = $obj->nom;
		$dataArray[$i]['name_alias']					 = $obj->name_alias;
		$dataArray[$i]['email']								 = $obj->email;
		$dataArray[$i]['phone']								 = $obj->phone;
		$dataArray[$i]['entity']							 = $obj->entity;
		$dataArray[$i]['ref_ext']							 = $obj->ref_ext;
		$dataArray[$i]['ref_int']							 = $obj->ref_int;
		$dataArray[$i]['statut']							 = $obj->statut;
		$dataArray[$i]['parent']							 = $obj->parent;
		$dataArray[$i]['tms']									 = $obj->tms;
		$dataArray[$i]['datec']								 = $obj->datec;
		$dataArray[$i]['status']							 = $obj->status;
		$dataArray[$i]['code_client']					 = $obj->code_client;
		$dataArray[$i]['address']							 = $obj->address;
		$dataArray[$i]['zip']									 = $obj->zip;
		$dataArray[$i]['town']								 = $obj->town;
		$dataArray[$i]['fk_departement']			 = $obj->fk_departement;
		$dataArray[$i]['fk_pays']							 = $obj->fk_pays;
		$dataArray[$i]['code_client_nom']			 = $obj->code_client_nom;

		$dataArray[$i]['pays_code']				 = $obj->pays_code;
		$dataArray[$i]['pays_code_iso']		 = $obj->pays_code_iso;
		$dataArray[$i]['pays_label']			 = $obj->pays_label;
		$dataArray[$i]['effectif_code']		 = $obj->effectif_code;
		$dataArray[$i]['effectif_libelle'] = $obj->effectif_libelle;
		$dataArray[$i]['typent_code']			 = $obj->typent_code;
		$dataArray[$i]['typent_libelle']	 = $obj->typent_libelle;

		// tag et tooltip
		$dataArray[$i]['country_town']		 = ((! empty($obj->pays_label)) ? $langs->trans($obj->pays_label) : '') . '-' . ( (! empty($obj->town)) ? $langs->trans($obj->town) : '');
		$dataArray[$i]['typent_libelle']	 = (!empty($obj->typent_libelle) ? $langs->trans($obj->typent_libelle) : '');
		$dataArray[$i]['effectif_libelle'] = (!empty($obj->effectif_libelle) ? $langs->trans($obj->effectif_libelle) : '');

		$dataArray[$i]['prospectlevel_label'] = $langs->trans($obj->fk_prospectlevel);

		$dataArray[$i]['kanban_status'] = ''; // voir ci-dessous
		// la rubrique image a un traitement supplémentaire pour générer l'url complète de l'image
		if (($fieldImageUrl != 'null' && !empty($fieldImageUrl)) && !empty($obj->{$fieldImageUrl})) {
			$dataArray[$i][$fieldImageUrl] = DOL_URL_ROOT . '/viewimage.php?modulepart=societe&file=' . $obj->rowid . '/logos/' . urlencode($obj->{$fieldImageUrl});
		}

		// traitements additionnels 
		// keyField
		if ($obj->status == 0) {
			$dataArray[$i]['kanban_status'] = 'PROSPECT_INACTIVE';
		} else {
			switch ($obj->stcomm_code) {
				case 'ST_NO':
					$dataArray[$i]['kanban_status']	 = 'PROSPECT_DONT_CONTACT';
					break;
				case 'ST_NEVER':
					$dataArray[$i]['kanban_status']	 = 'PROSPECT_NOT_CONTACTED';
					break;
				case 'ST_TODO':
					$dataArray[$i]['kanban_status']	 = 'PROSPECT_TO_CONTACT';
					break;
				case 'ST_PEND':
					$dataArray[$i]['kanban_status']	 = 'PROSPECT_CONTACT_INPROGRESS';
					break;
				case 'ST_DONE':
					$dataArray[$i]['kanban_status']	 = 'PROSPECT_CONTACTED';
					break;
				default:
					$dataArray[$i]['kanban_status']	 = 'PROSPECT_STATUS_UNKNOWN';
					break;
			}
		}

		$columnsCountArray[$dataArray[$i]['kanban_status']]	 += 1; // on incrémente le nbre d'éléments dans la colonne
		// gestion tooltip
		$prefix																							 = '<div id="prospect-' . $obj->rowid . '">'; // encapsulation du contenu dans un div pour permmettre l'affichage du tooltip
		$suffix																							 = '</div>';
		$dataArray[$i]['tooltip_content']	 = '<table><tbody>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldcode_client') . '</b></td><td>: <span class="tooltip-ref-' . $obj->rowid . '">' . $obj->code_client . '</span></td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldnom') . '</b></td><td>: ' . $obj->nom . '</td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldprospectlevel_label') . '</b></td><td>: ' . (!empty($dataArray[$i]['prospectlevel_label']) ? $langs->trans($dataArray[$i]['prospectlevel_label']) : '') . '</td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldpays_label') . '</b></td><td>: ' . $langs->trans($obj->pays_label) . '</td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldtown') . '</b></td><td>: ' . (!empty($obj->town) ? $langs->trans($obj->town) : '') . '</td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldemail') . '</b></td><td>: ' . $obj->email . '</td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldphone') . '</b></td><td>: ' . $obj->phone . '</td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldtypent_libelle') . '</b></td><td>: ' . $dataArray[$i]['typent_libelle'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']										 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainProspects_Fieldeffectif_libelle') . '</b></td><td>: ' . $dataArray[$i]['effectif_libelle'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '</tbody></table>';
		
		// contenu
		$thirdpartyCard = (DOL_VERSION < '6.0.0' ? 'soc.php' : 'card.php');
		if (!empty($obj->code_client)) // pour les prospects le code/ref n'est pas obligatoire
			$dataArray[$i]['code_client_nom']	 = '<a class="object-link" href="' . DOL_URL_ROOT . '/societe/' . $thirdpartyCard . '?socid=' . $obj->rowid . '" target="_blank">' . $obj->code_client . '</a>' . $prefix . $obj->nom . $suffix;
		else
			$dataArray[$i]['code_client_nom']	 = $prefix . '<a class="object-link" href="' . DOL_URL_ROOT . '/societe/' . $thirdpartyCard . '?socid=' . $obj->rowid . '" target="_blank">' . $obj->nom . '</a>' . $suffix;

		$i ++; // prochaine ligne de données
	}

	unset($ReqObject);
} else {
	$error ++;
	dol_print_error($db);
}

// nbre d'éléments dans une colonnes. on utilise :
// soit la propriété "enableTotalCount: true" du kanban 
// soit on ajoute le nbre d'éléments de la colonne au titre de celle-ci
// en fonction de la constante cachée KANVIEW_ENABLE_NATIVE_TOTAL_COUNT  
$kanbanHeaderCounts			 = array(); // si action ajax, ce tableau permet la mise à jour du nbre des taches de chaque colonne
$enableNativeTotalCount	 = false;
if (!empty($conf->global->KANVIEW_ENABLE_NATIVE_TOTAL_COUNT))
	$enableNativeTotalCount	 = true;
if (!$enableNativeTotalCount) {
	$countColumns = count($columnsCountArray);
	for ($i = 0; $i < $countColumns; $i++) {
		foreach ($columnsCountArray as $key => $value) {
			if ($columnsArray[$i]['key'] === $key) {
				$columnsArray[$i]['headerText']	 .= ' <span id="' . $key . '" class="badge">' . $value . '</span>';
				$kanbanHeaderCounts[$key]				 = $value;	// si action ajax, ce tableau permet la mise à jour du nbre des taches de chaque colonne
				break;
			}
		}
	}
}

// données à partir de requêtes additionnelles
// __DATA_FROM_ADDITIONAL_QUERIES__
// Complete $dataArray with data coming from external module
$parameters	 = array();
$object			 = null;
$reshook		 = $hookmanager->executeHooks('getKanbanData', $parameters, $object, $action);
if (!empty($hookmanager->resArray['dataarray']))
	$dataArray	 = array_merge($dataArray, $hookmanager->resArray['dataarray']);

// on trie le tableau des événements
usort($dataArray, 'natural_sort');

// paramètres de l'url
$params = '';

if (!empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"])
	$params	 .= '&contextpage=' . $contextpage;
if ($limit > 0 && $limit != $conf->liste_limit)
	$params	 .= '&limit=' . $limit;
if ($search_all != '')
	$params	 = "&amp;sall=" . urlencode($search_all);
if ($sall != '')
	$params	 .= "&amp;sall=" . urlencode($sall);

if ($search_rowid != '')
	$params	 .= '&amp;search_rowid=' . urlencode($search_rowid);
if ($search_nom != '')
	$params	 .= '&amp;search_nom=' . urlencode($search_nom);
if ($search_name_alias != '')
	$params	 .= '&amp;search_name_alias=' . urlencode($search_name_alias);
if ($search_entity != '')
	$params	 .= '&amp;search_entity=' . urlencode($search_entity);
if ($search_ref_ext != '')
	$params	 .= '&amp;search_ref_ext=' . urlencode($search_ref_ext);
if ($search_ref_int != '')
	$params	 .= '&amp;search_ref_int=' . urlencode($search_ref_int);
if ($search_statut != '')
	$params	 .= '&amp;search_statut=' . urlencode($search_statut);
if ($search_parent != '')
	$params	 .= '&amp;search_parent=' . urlencode($search_parent);
if ($search_tms != '')
	$params	 .= '&amp;search_tms=' . urlencode($search_tms);
if ($search_datec != '')
	$params	 .= '&amp;search_datec=' . urlencode($search_datec);
if ($search_status != '')
	$params	 .= '&amp;search_status=' . urlencode($search_status);
if ($search_code_client != '')
	$params	 .= '&amp;search_code_client=' . urlencode($search_code_client);
if ($search_address != '')
	$params	 .= '&amp;search_address=' . urlencode($search_address);
if ($search_zip != '')
	$params	 .= '&amp;search_zip=' . urlencode($search_zip);
if ($search_town != '')
	$params	 .= '&amp;search_town=' . urlencode($search_town);
if ($search_fk_pays != '')
	$params	 .= '&amp;search_fk_pays=' . urlencode($search_fk_pays);
if ($search_fk_departement != '')
	$params	 .= '&amp;search_fk_departement=' . urlencode($search_fk_departement);
if ($search_fk_stcomm != '')
	$params	 .= '&amp;search_fk_stcomm=' . urlencode($search_fk_stcomm);
if ($search_note_private != '')
	$params	 .= '&amp;search_note_private=' . urlencode($search_note_private);
if ($search_note_public != '')
	$params	 .= '&amp;search_note_public=' . urlencode($search_note_public);
if ($search_prefix_comm != '')
	$params	 .= '&amp;search_prefix_comm=' . urlencode($search_prefix_comm);
if ($search_client != '')
	$params	 .= '&amp;search_client=' . urlencode($search_client);
if ($search_fk_prospectlevel != '')
	$params	 .= '&amp;search_fk_prospectlevel=' . urlencode($search_fk_prospectlevel);
if ($search_customer_bad != '')
	$params	 .= '&amp;search_customer_bad=' . urlencode($search_customer_bad);
if ($search_customer_rate != '')
	$params	 .= '&amp;search_customer_rate=' . urlencode($search_customer_rate);
if ($search_supplier_rate != '')
	$params	 .= '&amp;search_supplier_rate=' . urlencode($search_supplier_rate);
if ($search_logo != '')
	$params	 .= '&amp;search_logo=' . urlencode($search_logo);
if ($search_url != '')
	$params	 .= '&amp;search_url=' . urlencode($search_url);
if ($search_email != '')
	$params	 .= '&amp;search_email=' . urlencode($search_email);
if ($search_skype != '')
	$params	 .= '&amp;search_skype=' . urlencode($search_skype);
if ($search_fk_effectif != '')
	$params	 .= '&amp;search_fk_effectif=' . urlencode($search_fk_effectif);
if ($search_fk_typent != '')
	$params	 .= '&amp;search_fk_typent=' . urlencode($search_fk_typent);
if ($search_fk_forme_juridique != '')
	$params	 .= '&amp;search_fk_forme_juridique=' . urlencode($search_fk_forme_juridique);
if ($search_fk_currency != '')
	$params	 .= '&amp;search_fk_currency=' . urlencode($search_fk_currency);
if ($search_siren != '')
	$params	 .= '&amp;search_siren=' . urlencode($search_siren);
if ($search_siret != '')
	$params	 .= '&amp;search_siret=' . urlencode($search_siret);
if ($search_ape != '')
	$params	 .= '&amp;search_ape=' . urlencode($search_ape);
if ($search_idprof4 != '')
	$params	 .= '&amp;search_idprof4=' . urlencode($search_idprof4);
if ($search_idprof5 != '')
	$params	 .= '&amp;search_idprof5=' . urlencode($search_idprof5);
if ($search_idprof6 != '')
	$params	 .= '&amp;search_idprof6=' . urlencode($search_idprof6);
if ($search_tva_intra != '')
	$params	 .= '&amp;search_tva_intra=' . urlencode($search_tva_intra);
if ($search_capital != '')
	$params	 .= '&amp;search_capital=' . urlencode($search_capital);
if ($search_model_pdf != '')
	$params	 .= '&amp;search_model_pdf=' . urlencode($search_model_pdf);
if ($search_fournisseur != '')
	$params	 .= '&amp;search_fournisseur=' . urlencode($search_fournisseur);
if ($search_supplier_account != '')
	$params	 .= '&amp;search_supplier_account=' . urlencode($search_supplier_account);
if ($search_fk_incoterms != '')
	$params	 .= '&amp;search_fk_incoterms=' . urlencode($search_fk_incoterms);
if ($search_location_incoterms != '')
	$params	 .= '&amp;search_location_incoterms=' . urlencode($search_location_incoterms);
if ($search_fk_user_creat != '')
	$params	 .= '&amp;search_fk_user_creat=' . urlencode($search_fk_user_creat);
if ($search_fk_user_modif != '')
	$params	 .= '&amp;search_fk_user_modif=' . urlencode($search_fk_user_modif);
if ($search_remise_client != '')
	$params	 .= '&amp;search_remise_client=' . urlencode($search_remise_client);
if ($search_mode_reglement != '')
	$params	 .= '&amp;search_mode_reglement=' . urlencode($search_mode_reglement);
if ($search_cond_reglement != '')
	$params	 .= '&amp;search_cond_reglement=' . urlencode($search_cond_reglement);
if ($search_mode_reglement_supplier != '')
	$params	 .= '&amp;search_mode_reglement_supplier=' . urlencode($search_mode_reglement_supplier);
if ($search_cond_reglement_supplier != '')
	$params	 .= '&amp;search_cond_reglement_supplier=' . urlencode($search_cond_reglement_supplier);
if ($search_fk_shipping_method != '')
	$params	 .= '&amp;search_fk_shipping_method=' . urlencode($search_fk_shipping_method);
if ($search_tva_assuj != '')
	$params	 .= '&amp;search_tva_assuj=' . urlencode($search_tva_assuj);
if ($search_barcode != '')
	$params	 .= '&amp;search_barcode=' . urlencode($search_barcode);
if ($search_fk_barcode_type != '')
	$params	 .= '&amp;search_fk_barcode_type=' . urlencode($search_fk_barcode_type);
if ($search_price_level != '')
	$params	 .= '&amp;search_price_level=' . urlencode($search_price_level);
if ($search_outstanding_limit != '')
	$params	 .= '&amp;search_outstanding_limit=' . urlencode($search_outstanding_limit);
if ($search_default_lang != '')
	$params	 .= '&amp;search_default_lang=' . urlencode($search_default_lang);
if ($search_id != '')
	$params	 .= '&amp;search_id=' . urlencode($search_id);
if ($search_code_fournisseur != '')
	$params	 .= '&amp;search_code_fournisseur=' . urlencode($search_code_fournisseur);
if ($search_code_compta != '')
	$params	 .= '&amp;search_code_compta=' . urlencode($search_code_compta);
if ($search_code_compta_fournisseur != '')
	$params	 .= '&amp;search_code_compta_fournisseur=' . urlencode($search_code_compta_fournisseur);
if ($search_fk_account != '')
	$params	 .= '&amp;search_fk_account=' . urlencode($search_fk_account);
if ($search_phone != '')
	$params	 .= '&amp;search_phone=' . urlencode($search_phone);
if ($search_fax != '')
	$params	 .= '&amp;search_fax=' . urlencode($search_fax);

// ***************************************************************************************************************
// 
//                                           Actions part 2 - Après collecte de données
// 
// ***************************************************************************************************************
// 
// suite de l'action if ($action == 'cardDrop') 
if ($action == 'cardDrop') {
	if (is_array($response) && $response['status'] == 'OK') {
		$response['data']['kanbanHeaderCounts'] = $kanbanHeaderCounts;
	}
	exit(json_encode($response));
}

// 
// ************************************************************************************************************** 
// 
//                                      VIEW - Envoi du header et Filter
// 
// ***************************************************************************************************************

$help_url = ''; // EN:Module_Kanban_En|FR:Module_Kanban|AR:M&oacute;dulo_Kanban';
// llxHeader('', $langs->trans("Kanban"), $help_url);

$arrayofcss		 = array();
$arrayofcss[]	 = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Content/ejthemes/default-theme/ej.web.all.min.css';
$arrayofcss[]	 = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Content/ejthemes/responsive-css/ej.responsive.css';
$arrayofcss[]	 = str_replace(DOL_URL_ROOT, '', KANVIEW_URL_ROOT) . '/css/kanview.css?b=' . $build;
// $arrayofcss[]	 = KANVIEW_URL_ROOT . '/css/' . str_replace('.php', '.css', basename($_SERVER['SCRIPT_NAME'])) . '?b=' . $build;

$arrayofjs	 = array();
// $arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/js/jquery-3.1.1.min.js';
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/jsrender.min.js';

// ----------------------------------------- sf ---------------------------------------------------
// ----- sf common
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.core.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.data.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.draggable.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.globalize.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.scroller.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.touch.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.unobtrusive.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/common/ej.webform.min.js?b=' . $build;

// ----- sf others
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.button.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.checkbox.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.datepicker.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.datetimepicker.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.dialog.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.dropdownlist.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.editor.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.kanban.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.menu.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.rte.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.toolbar.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.waitingpopup.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.listbox.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.tooltip.min.js?b=' . $build;

// ----- sf traductions (garder les après common et controls)
if (in_array($langs->defaultlang, array(
				'fr_FR',
				'en_US'))) {
	$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/i18n/ej.culture.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
	$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/l10n/ej.localetexts.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
} else {
	$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/i18n/ej.culture.fr-FR.min.js?b=' . $build;
	$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/l10n/ej.localetexts.fr-FR.min.js?b=' . $build;
}
/// ----------

llxHeader('', $langs->trans("Kanview_KB_KanbanProspects"), $help_url, '', 0, 0, $arrayofjs, $arrayofcss, '');

$form = new Form($db);

$head = kanview_kanban_prepare_head($params);

$tabactive = '__KANBAN_TABACTIVE__';

// dol_fiche_head($head, $tabactive, $langs->trans('Kanview_KB_KanbanProspects'), 0, 'action');
// le selecteur du nbre d'éléments par page généré par print_barre_liste() doit se trouver ds le <form>
// cette ligne doit donc rester avant l'appel à print_barre_liste()
print '<form name="listactionsfilter" class="listactionsfilter" action="' . $_SERVER["PHP_SELF"] . '" method="get">';

//
// __KANBAN_BEFORE_TITLE__
// 
// titre du Kanban
$title = $langs->trans('Kanview_KB_KanbanProspects');
print_barre_liste($title, intval($page), $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', intval($num) + 1, intval($nbtotalofrecords), 'title_companies', 0, '', '', intval($limit));

// __KANBAN_AFTER_TITLE__
// 
// ------------------------------------------- zone Filter
// 
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';

print '<input type="hidden" name="token" value="' . $_SESSION['newtoken'] . '">';
print '<input type="hidden" name="current_view" value="' . $current_view . '">';

print '<div class="fichecenter">';

// pour afficher la légende, on encapsule le filtre et la légende dans une table avec un seul <tr> 
// et on affiche le filtre dans le 1er <td> et la légende dans le 2e <td>
// (ceci pour grand ecran uniquement, pour les autres on affcihe pas la légende)
// voir plus loin pour le fieldset légende
if (empty($conf->browser->phone)) {
	print '<table style="width: 100%; height: 100%; margin-bottom: 15px; padding-bottom: 0px;">';
	print '<tr style="width: 100%; height: 100%;">';
	print '<td style="width: 80%">';
}
/// ---
// fieldset filtre - formulaire et table contenant le filtre pour le Kanban
print '<fieldset class="filters-fields" style="width: 99%; height: 100%; padding-right: 1px;">';
print '<legend><span class="e-icon e-filter" style="text-align: left;"></span></legend>';

if (!empty($conf->browser->phone))
	print '<div class="fichehalfleft">';
else
	print '<table class="nobordernopadding" width="100%" height="100%"><tr style="width: 100%; height: 100%;"><td class="borderright">';

print '<table class="nobordernopadding" width="100%">';

// 
// ------------- filtre --req_kb_main_prospects-- datec - période
// 
$value = empty($search_dd_datec) ? - 1 : $search_dd_datec;
echo '<tr id="tr-periode">';
echo '<td class="td-card-label">' . $langs->trans("ReqKbMainProspects_Fielddatec") . '</td>';
echo '<td>' . $langs->trans("Du") . '</td>';
echo '<td class="td-card-data">';
$form->select_date($value, 'search_dd_datec_', '', '', '', "dd", 1, 1); // datepicker
$value = empty($search_df_datec) ? - 1 : $search_df_datec;
echo '&nbsp;&nbsp;&nbsp;&nbsp;<span>' . $langs->trans("Au") . '</span>&nbsp;';
$form->select_date($value, 'search_df_datec_', '', '', '', "df", 1, 1); // datepicker
echo '</td>';
echo '</tr>';

// 
// --------------------------------------------- pays
// 

$mySocCountryId = 0;
if (!empty($conf->global->MAIN_INFO_SOCIETE_COUNTRY)) {
	$mySocCountryInfos = explode(':', $conf->global->MAIN_INFO_SOCIETE_COUNTRY);
	if (count($mySocCountryInfos) > 0)
		$mySocCountryId		 = intval($mySocCountryInfos[0]);
}

echo '<tr id="tr-search_fk_pays" class="tr-external-filter">';
echo '<td class="td-card-label">' . $langs->trans('ReqKbMainProspects_Fieldfk_pays') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="fk_pays_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';
// ------------- filtre --req_kb_main_prospects-- fk_pays
$SQL						 = "
					SELECT rowid, code, label
					FROM " . LLX_ . "c_country as t
					WHERE t.active = 1
					ORDER BY t.favorite DESC, t.label
					";
$sql_fk_pays		 = $db->query($SQL);
$options				 = array();
$optionSelected	 = '';
$defaultValue		 = '';
$blanckOption		 = 1;
if ($sql_fk_pays) {
	while ($obj_fk_pays = $db->fetch_object($sql_fk_pays)) {
		if ($obj_fk_pays->rowid == $search_fk_pays) {
			$optionSelected = $obj_fk_pays->rowid;
		}
		$options[$obj_fk_pays->rowid] = $obj_fk_pays->code;
	}
	$db->free($sql_fk_pays);
} else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
}
echo '<select  id="fk_pays_input_filtre" class="flat" name="search_fk_pays" title="">';
if (empty($optionSelected) && !$blanckOption) {
	$optionSelected = $defaultValue;
}
if ($blanckOption) {
	// echo '<option value="" ' . (empty($search_fk_pays) ? 'selected' : '') . '></option>';
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . (empty($value) ? '' : $langs->trans('Country' . $value)) . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
echo ajax_combobox('fk_pays_input_filtre');

// 
// -------------------------------------- departements
// 
echo '<tr id="tr-search_fk_departement" class="tr-external-filter">';
echo '<td class="td-card-label">' . $langs->trans('ReqKbMainProspects_Fieldfk_departement') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="fk_departement_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';
// ------------- filtre --req_kb_main_prospects-- fk_departement
$options				 = array();
$optionSelected	 = '';
$defaultValue		 = '';
$blanckOption		 = 1;
if (!empty($search_fk_pays)) {
	$SQL								 = "
						SELECT t.rowid, t.nom, t.fk_region, " . LLX_ . "c_regions.fk_pays
						FROM " . LLX_ . "c_departements as t
							LEFT JOIN " . LLX_ . "c_regions ON t.fk_region = " . LLX_ . "c_regions.rowid 
						WHERE t.active = 1 AND " . LLX_ . "c_regions.fk_pays = " . $search_fk_pays . " 
						ORDER BY t.nom
						";
	$sql_fk_departement	 = $db->query($SQL);
	if ($sql_fk_departement) {
		while ($obj_fk_departement = $db->fetch_object($sql_fk_departement)) {
			if ($obj_fk_departement->rowid == $search_fk_departement) {
				$optionSelected = $obj_fk_departement->rowid;
			}
			$options[$obj_fk_departement->rowid] = $obj_fk_departement->nom;
		}
		$db->free($sql_fk_departement);
	} else {
		dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
	}
}
echo '<select  id="fk_departement_input_filtre" class="flat" name="search_fk_departement" title="">';
if (empty($optionSelected) && !$blanckOption) {
	$optionSelected = $defaultValue;
}
if ($blanckOption) {
	echo '<option value="" ' . (empty($search_fk_departement) ? 'selected' : '') . '></option>';
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $value . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
// echo ajax_combobox('fk_departement_input_filtre');
// 
// ----------------------------------------------- prospect level
// 
echo '<tr id="tr-search_fk_prospectlevel" class="tr-external-filter">';
echo '<td class="td-card-label">' . $langs->trans('ReqKbMainProspects_Fieldfk_prospectlevel') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="fk_prospectlevel_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';
// ------------- filtre --req_kb_main_prospects-- fk_prospectlevel
$SQL									 = "
							SELECT code, label
							FROM " . LLX_ . "c_prospectlevel as t
							WHERE t.active = 1
							ORDER BY t.sortorder
							";
$sql_fk_prospectlevel	 = $db->query($SQL);
$options							 = array();
$optionSelected				 = '';
$defaultValue					 = '';
$blanckOption					 = 1;
if ($sql_fk_prospectlevel) {
	while ($obj_fk_prospectlevel = $db->fetch_object($sql_fk_prospectlevel)) {
		if ($obj_fk_prospectlevel->code == $search_fk_prospectlevel) {
			$optionSelected = $obj_fk_prospectlevel->code;
		}
		$options[$obj_fk_prospectlevel->code] = $obj_fk_prospectlevel->code;
	}
	$db->free($sql_fk_prospectlevel);
} else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
}
echo '<select  id="fk_prospectlevel_input_filtre" class="flat" name="search_fk_prospectlevel" title="">';
if (empty($optionSelected) && !$blanckOption) {
	$optionSelected = $defaultValue;
}
if ($blanckOption) {
	echo '<option value="" ' . (empty($search_fk_prospectlevel) ? 'selected' : '') . '></option>';
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $langs->trans($value) . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';

print '</table>';

if (!empty($conf->browser->phone))
	print '</div>';
else
	print '</td>';


// ---- bouton refresh
if (!empty($conf->browser->phone))
	print '<div class="fichehalfright">';
else
	print '<td align="center" valign="middle" class="nowrap">';

print '<table><tr><td align="center">';
print '<div class="formleftzone">';
print '<input type="submit" class="button" style="min-width:120px" name="refresh" value="' . $langs->trans("Refresh") . '">';
print '</div>';
print '</td></tr>';
print '</table>';

if (!empty($conf->browser->phone))
	print '</div>';
else
	print '</td>';
/// -- fin bouton refresh


if (!empty($conf->browser->phone))
	;
else
	print '</tr></table>';

print '</fieldset>'; /// .filters-fields
//
// ------ fieldset legend
// la légende n'est affichée que pour grand écran
if (empty($conf->browser->phone)) {
	print '</td>';
	print '<td style="width: 80%; ">';		// garder les 80% sinon défaut d'affichage
	print '<fieldset class="filters-fields" style="height: 100%; padding-left: 5px;">';
	print '<legend><span class="" style="text-align: left;">' . $langs->trans('LEGEND') . '</span></legend>';
	print '<table>';
	// -- legend 1
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROSPECTS_PL_HIGH_COLOR . ' transparent;">'
// le découpage suivant n'a pas marché sur FF
//			. 'border-color-top: transparent; '
//			. 'border-color-right: transparent; '
//			. 'border-color-bottom: #007bff; '
//			. 'border-color-left: transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROSPECTS_PL_HIGH_COLOR_DESC') . '</td>';
	print '</tr>';
	// -- legend 2
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROSPECTS_PL_MEDIUM_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR') . '</td>';
	print '</tr>';
	// -- legend 3
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROSPECTS_PL_LOW_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROSPECTS_PL_LOW_COLOR') . '</td>';
	print '</tr>';
	// -- legend 4
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROSPECTS_PL_NONE_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROSPECTS_PL_NONE_COLOR') . '</td>';
	print '</tr>';
	// -- legend 5
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . '#179BD7' . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_STATUS_UNKNOWN_COLOR') . '</td>';
	print '</tr>';
	// -- legend 6 (TAG)
	print '<tr>';
	print '<td>';
	print '<div class="legend-name">TAG</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans(strtoupper($conf->global->KANVIEW_PROSPECTS_TAG)) . '</td>';
	print '</tr>';
	
	
	print '</table>';
	print '</fieldset>';
	print '</td>';
	print '</tr>';
	print '</table>';
}
/// --- fin légend

print '</div>'; // Close fichecenter
print '<div style="clear:both"></div>';

print '</form>';


// 
// -- si pas de données on le dit --
/* // pas la peine, kanban s'en charge
  if ($num === 0) {
  echo '<div id="AucunElementTrouve">';
  echo '<p>' . $langs->trans('AucunElementTrouve') . '</p>';
  echo '</div>';
  }
 */

// ************************************************************************************************************** 
// 
//                                              VIEW - Kanban Output
// 
// ***************************************************************************************************************

$columns		 = "[]";
$kanbanData	 = "[]";
$columnIDs	 = "[]";

if (count($columnsArray) > 0) {

	// --- titres des colunnes dans un tableau d'objets json 
	$count	 = count($columnsArray);
	$columns = "[";
	for ($i = 0; $i < $count; $i++) {
		$columns .= json_encode($columnsArray[$i]) . ",";
	}
	$columns .= "]";

	// --- données principales du kanban dans un tableau d'objets json
	$count			 = count($dataArray);
	$kanbanData	 = "[";
	for ($i = 0; $i < $count; $i++) {
		$kanbanData .= json_encode($dataArray[$i]) . ",";
	}
	$kanbanData .= "]";

	$columnIDs = json_encode($columnsIDsArray);

	// $now = dol_print_date(dol_now('tzuser'), $format = '%Y-%m-%d %H:%M:%S', $tzoutput = 'tzuser', $outputlangs = '', $encodetooutput = false);
}

// __KANBAN_AFTER_KANBAN__
//
// --------------------------------------- END Output

dol_fiche_end(); // fermeture du cadre
// ----------------------------------- javascripts spécifiques à cette page
// quelques variables javascripts fournis par php
echo '<script type="text/javascript">
 		var dateSeparator				= "' . trim(substr($langs->trans('FormatDateShort'), 2, 1)) . '";
		var DOL_URL_ROOT				= "' . trim(DOL_URL_ROOT) . '";
		var DOL_VERSION							= "' . trim(DOL_VERSION) . '";
 		var KANVIEW_URL_ROOT		= "' . trim(KANVIEW_URL_ROOT) . '";
		var fieldImageUrl							= "' . trim($fieldImageUrl) . '";

		var locale									= "' . trim($langs->defaultlang) . '";
		var sfLocale								= "' . trim(str_replace('_', '-', $langs->defaultlang)) . '";

		var parent1	= "' . trim(module) . '";
		var parent2	= "' . trim($container) . '";

		// var UpdateNotAllowed_ProjectClosed		= "' . trim($langs->transnoentities('UpdateNotAllowed_ProjectClosed')) . '"; 
		var msgPrintKanbanView									= "' . trim($langs->transnoentities('msgPrintKanbanView')) . '";

		var enableNativeTotalCount				= ' . trim(empty($enableNativeTotalCount) ? 'false' : 'true') . ';
		var tooltipsActive								= false;		// mémorise le fait que les tooltps sont activés ou non
		
		var columnIDs				= ' . trim($columnIDs) . ';
		var kanbanData			= ' . trim($kanbanData) . ';
		var columns					= ' . trim($columns) . ';
		var prospects_tag		= "' . trim($conf->global->KANVIEW_PROSPECTS_TAG) . '";
		var colorMapping  =  {
	        "' . trim($conf->global->KANVIEW_PROSPECTS_PL_HIGH_COLOR) . '": "PL_HIGH",
	        "' . trim($conf->global->KANVIEW_PROSPECTS_PL_LOW_COLOR) . '": "PL_LOW",
	        "' . trim($conf->global->KANVIEW_PROSPECTS_PL_MEDIUM_COLOR) . '": "PL_MEDIUM",
	        "' . trim($conf->global->KANVIEW_PROSPECTS_PL_NONE_COLOR) . '": "PL_NONE",
	      };
		var mytitle = "' . trim($langs->trans('Kanview_TopMenu_Dashboard')) . '";

		var token = "' . trim($_SESSION['newtoken']) . '";

 	</script>';

// inclusion des fichiers js
echo '<script src="' . KANVIEW_URL_ROOT . '/js/kanview.js?b=' . $build . '"></script>';
echo '<script src="' . KANVIEW_URL_ROOT . '/js/' . str_replace('.php', '.js', basename($_SERVER['SCRIPT_NAME'])) . '?b=' . $build . '"></script>';

llxFooter();

$db->close();

// -------------------------------------------------- Functions ----------------------------------------
// -------------------------------- displayField (pour la vue liste)
// test si on doit afficher le champ ou non
function displayField($fieldName) {
	global $arrayfields, $secondary;
	if (((!empty($arrayfields[$fieldName]['checked'])) && empty($secondary)) || ((!empty($arrayfields[$fieldName]['checked'])) && (!empty($secondary) && empty($arrayfields[$fieldName]['hideifsecondary']))))
		return true;
	else
		return false;
}

// ---------------------------- preapre_head
function kanview_kanban_prepare_head($params) {
	global $langs, $conf, $user;
	global $action;

	$h		 = 0;
	$head	 = array();

	// kanban par ressources
	// __KANBAN_HEAD__

	$object = new stdClass();

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@kanview:/kanview/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname);   												to remove a tab
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_prospects_kanban');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_prospects_kanban', 'remove');

	return $head;
}

// 
// ------------------------------------- natural_sort()
// 
function natural_sort($a, $b) {
	global $sortfield, $sortorder;
	$sortorder = strtoupper($sortorder);
	if (empty($sortfield) || $sortfield == '1')
		$sortfield = 'id';
	if (empty($sortorder) || $sortorder == 'ASC')
		return strnatcasecmp($a[$sortfield], $b[$sortfield]);
	else
		return - strnatcasecmp($a[$sortfield], $b[$sortfield]);
}

/**
 * Return a path to have a directory according to object without final '/'.
 * (hamid-210118-fonction ajoutée pour gérer les fichiers des modules perso)
 * 
 * @param Object $object
 *        	Object
 * @param Object $idORref
 *        	'id' ou 'ref', si 'id' le nom du sous repertoire est l'id de l'objet sinon c'est la ref de l'objet
 * @param string $additional_subdirs
 *        	sous-repertoire à ajouter à cet objet pour stocker/retrouver le fichier en cours de traitement, doit être sans '/' ni au début ni à la fin (ex. 'album/famille')
 * @return string Dir to use ending. Example '' or '1/' or '1/2/'
 */
function get_exdir2($object, $idORref, $additional_subdirs = '') {
	global $conf;

	$path = '';

	if ((!empty($object->idfield)) && !empty($object->reffield)) {
		if ($idORref == 'id') // 'id' prioritaire
			$path	 = ($object->{$object->idfield} ? $object->{$object->idfield} : $object->{$object->reffield});
		else // 'ref' prioritaire
			$path	 = $object->{$object->reffield} ? $object->{$object->reffield} : $object->{$object->idfield};
	}

	if (isset($additional_subdirs) && $additional_subdirs != '') {
		$path	 = (!empty($path) ? $path	 .= '/' : '');
		$path	 .= trim($additional_subdirs, '/');
	}

	return $path;
}

// --------------------------------

