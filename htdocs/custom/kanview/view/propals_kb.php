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
if (!hasPermissionForKanbanView('propals')) {
	accessforbidden();
	exit();
}

require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

$build = '1314085517';

// ------------------------------------------- Params 

$view		 = GETPOST("view", '', 3); // type de vue : standard, perressource ou list
if (empty($view))
	$view		 = 'standard';
$action	 = GETPOST('action', 'alpha');
if (empty($action))
	$action	 = 'show';

//
// -------------- Filter Date Début du Kanban
//
$dd_year	 = str_pad(GETPOST('dd_year', 'int'), 4, '0', STR_PAD_LEFT);
$dd_month	 = str_pad(GETPOST('dd_month', 'int'), 2, '0', STR_PAD_LEFT);
$dd_day		 = str_pad(GETPOST('dd_day', 'int'), 2, '0', STR_PAD_LEFT);
$dd_hour	 = str_pad(GETPOST('dd_hour', 'int'), 2, '0', STR_PAD_LEFT);
$dd_min		 = str_pad(GETPOST('dd_min', 'int'), 2, '0', STR_PAD_LEFT);
$dd_sec		 = str_pad(GETPOST('dd_sec', 'int'), 2, '0', STR_PAD_LEFT);

// 1er affichage, par défaut : la Date début est 6 mois avant aujourd'hui
if ((empty($dd_year) || $dd_year == '0000') && (empty($dd_month) || $dd_month == '00') && (empty($dd_day) || $dd_day == '00')) {
	$ddTmp		 = $db->idate(dol_time_plus_duree(dol_now('tzserver') + (60 * 60 * 24), -6, 'm')); // format timstamp puis format : yyyymmddhhiiss
	$dd_year	 = substr($ddTmp, 0, 4);
	$dd_month	 = substr($ddTmp, 4, 2);
	$dd_day		 = substr($ddTmp, 6, 2);
}

// blindage, si la date n'est pas valide, on prend la date d'aujourd'hui
if (empty($dd_year) || $dd_year == '0000')
	$dd_year	 = date('Y');
if (empty($dd_month) || $dd_month == '00')
	$dd_month	 = date('m');
if (empty($dd_day) || $dd_day == '00')
	$dd_day		 = date('d');

// format numérique (entier), utilisé par certaines fonctions dolibarr
$date_debut				 = dol_stringtotime($dd_year . $dd_month . $dd_day . $dd_hour . $dd_min . $dd_sec, 0);
// format "yyyymmddhhiiss" utilisée par certaines fonctions dolibarr
$date_debut_str		 = $dd_year . $dd_month . $dd_day . $dd_hour . $dd_min . $dd_sec;
// format mysql  "yyyy-mm-dd hh:ii:ss", acceptée par le constructeur de DateTime, utilisé par le Kanban, voir en bas partie paramétrage du Kanban
$date_debut_mysql	 = $dd_year . '-' . $dd_month . '-' . $dd_day . ' ' . $dd_hour . ':' . $dd_min . ':' . $dd_sec;

//
// -------------- Filter Date Fin du Kanban
//
$df_year	 = str_pad(GETPOST('df_year', 'int'), 4, '0', STR_PAD_LEFT);
$df_month	 = str_pad(GETPOST('df_month', 'int'), 2, '0', STR_PAD_LEFT);
$df_day		 = str_pad(GETPOST('df_day', 'int'), 2, '0', STR_PAD_LEFT);
$df_hour	 = str_pad(GETPOST('df_hour', 'int'), 2, '0', STR_PAD_LEFT);
$df_min		 = str_pad(GETPOST('df_min', 'int'), 2, '0', STR_PAD_LEFT);
$df_sec		 = str_pad(GETPOST('df_sec', 'int'), 2, '0', STR_PAD_LEFT);

// 1er affichage, par défaut : la Date Fin est le mois flottant suivant
if ((empty($df_year) || $df_year == '0000') && (empty($df_month) || $df_month == '00') && (empty($df_day) || $df_day == '00')) {
	$df_year	 = date('Y');
	$df_month	 = date('m');
	$df_day		 = date('d');
	// $next_month	 = dol_get_next_month($df_month, $df_year);
	$df_month	 = str_pad($df_month, 2, '0', STR_PAD_LEFT);
	$df_year	 = str_pad($df_year, 4, '0', STR_PAD_LEFT);
}

// blindage, si la date n'est pas valide, on pend la date d'aujourd'hui
if (empty($df_year) || $df_year == '0000')
	$df_year	 = date('Y');
if (empty($df_month) || $df_month == '00')
	$df_month	 = date('m');
if (empty($df_day) || $df_day == '00')
	$df_day		 = date('d');

// format timestamp (entier) utilisé par cetaines fonction dolibarr
$date_fin				 = dol_stringtotime($df_year . $df_month . $df_day . $df_hour . $df_min . $df_sec, 1);
// format "yyyymmddhhiiss" utilisée par certaines fonctions dolibarr
$date_fin_str		 = $df_year . $df_month . $df_day . $df_hour . $df_min . $df_sec;
// format mysql "yyyy-mm-dd hh:ii:ss", acceptée par le constructeur de DateTime, utilisé par le Kanban, voir en bas partie paramétrage du Kanban
$date_fin_mysql	 = $df_year . '-' . $df_month . '-' . $df_day . ' ' . $df_hour . ':' . $df_min . ':' . $df_sec;

// paramètres filtres additionnels
//off//
$search_rowid					 = GETPOST('search_rowid', 'int');
$search_ref						 = GETPOST('search_ref', 'alpha');
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
$search_fk_soc							 = GETPOST('search_fk_soc', 'int');
$search_fk_projet						 = GETPOST('search_fk_projet', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_datep_day						 = GETPOST('search_datep_day', 'int');
$search_datep_month					 = GETPOST('search_datep_month', 'int');
$search_datep_year					 = GETPOST('search_datep_year', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_fin_validite_day		 = GETPOST('search_fin_validite_day', 'int');
$search_fin_validite_month	 = GETPOST('search_fin_validite_month', 'int');
$search_fin_validite_year		 = GETPOST('search_fin_validite_year', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_date_cloture_day		 = GETPOST('search_date_cloture_day', 'int');
$search_date_cloture_month	 = GETPOST('search_date_cloture_month', 'int');
$search_date_cloture_year		 = GETPOST('search_date_cloture_year', 'int');
$search_fk_statut						 = GETPOST('search_fk_statut', 'int');
$search_total_ht						 = GETPOST('search_total_ht', 'alpha');
$search_note_private				 = GETPOST('search_note_private', 'alpha');
$search_note_public					 = GETPOST('search_note_public', 'alpha');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_date_livraison_day	 = GETPOST('search_date_livraison_day', 'int');
$search_date_livraison_month = GETPOST('search_date_livraison_month', 'int');
$search_date_livraison_year	 = GETPOST('search_date_livraison_year', 'int');
$search_societe_nom					 = GETPOST('search_societe_nom', 'alpha');
$search_id									 = GETPOST('search_id', 'alpha');
$search_entity							 = GETPOST('search_entity', 'int');
$search_ref_int							 = GETPOST('search_ref_int', 'alpha');
$search_ref_client					 = GETPOST('search_ref_client', 'alpha');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_tms_day							 = GETPOST('search_tms_day', 'int');
$search_tms_month						 = GETPOST('search_tms_month', 'int');
$search_tms_year						 = GETPOST('search_tms_year', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_date_valid_day			 = GETPOST('search_date_valid_day', 'int');
$search_date_valid_month		 = GETPOST('search_date_valid_month', 'int');
$search_date_valid_year			 = GETPOST('search_date_valid_year', 'int');
$search_fk_user_author			 = GETPOST('search_fk_user_author', 'int');
$search_fk_user_modif				 = GETPOST('search_fk_user_modif', 'int');
$search_fk_user_valid				 = GETPOST('search_fk_user_valid', 'int');
$search_fk_user_cloture			 = GETPOST('search_fk_user_cloture', 'int');
$search_price								 = GETPOST('search_price', 'alpha');
$search_remise_absolue			 = GETPOST('search_remise_absolue', 'alpha');
$search_tva									 = GETPOST('search_tva', 'alpha');
$search_localtax1						 = GETPOST('search_localtax1', 'alpha');
$search_localtax2						 = GETPOST('search_localtax2', 'alpha');
$search_total								 = GETPOST('search_total', 'alpha');
$search_fk_account					 = GETPOST('search_fk_account', 'int');
$search_fk_currency					 = GETPOST('search_fk_currency', 'alpha');
$search_fk_shipping_method	 = GETPOST('search_fk_shipping_method', 'int');
$search_fk_availability			 = GETPOST('search_fk_availability', 'int');
$search_fk_input_reason			 = GETPOST('search_fk_input_reason', 'int');
$search_location_incoterms	 = GETPOST('search_location_incoterms', 'alpha');
$search_extraparams					 = GETPOST('search_extraparams', 'alpha');
$search_fk_delivery_address	 = GETPOST('search_fk_delivery_address', 'int');
$search_societe_name_alias	 = GETPOST('search_societe_name_alias', 'alpha');
$search_societe_ref_int			 = GETPOST('search_societe_ref_int', 'alpha');

// --- current view
$default_current_view	 = '';
$current_view					 = GETPOST('current_view', 'alpha'); // TODO : ajouter mécanisme pour modfier le hidden input current_view lorsque l'utilisateur sélectionne une vue dans le Kanban
if (empty($current_view) || !in_array($current_view, array('day', 'week', 'workweek', 'month', 'agenda')))
	$current_view					 = $default_current_view;

// read only ?
$read_only = false;
$container = 'kanview';

// paramètres additionnels
// __GETPOST_ADDITIONNELS__

$langs->load("kanview@kanview");

$langs->load("other");

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'propal_kanban'));

// ------------ récupération des données (ce code doit rester avant les actions car utilisé par printPDF())
// si l'heure n'est pas fourni, on la règle de façon à ce que la journée de "date fin" soit incluse
if (empty($df_hour) || $df_hour == '00')
	$df_hour			 = '23';
if (empty($df_min) || $df_min == '00')
	$df_min				 = '59';
if (empty($df_sec) || $df_sec == '00')
	$df_sec				 = '59';
$date_fin_str	 = $df_year . $df_month . $df_day . $df_hour . $df_min . $df_sec;

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
	$search_rowid								 = '';
	$search_ref									 = '';
// datec - date début
	$search_dd_datec_day				 = '';
	$search_dd_datec_month			 = '';
	$search_dd_datec_year				 = '';
	$search_dd_datec_hour				 = '';
	$search_dd_datec_min				 = '';
	$search_dd_datec_sec				 = '';
	$search_dd_datec						 = '';
	$search_dd_datec_mysql			 = '';
// datec - date fin
	$search_df_datec_day				 = '';
	$search_df_datec_month			 = '';
	$search_df_datec_year				 = '';
	$search_df_datec_hour				 = '';
	$search_df_datec_min				 = '';
	$search_df_datec_sec				 = '';
	$search_df_datec						 = '';
	$search_df_datec_mysql			 = '';
	$search_fk_soc							 = '';
	$search_fk_projet						 = '';
	$search_datep_day						 = '';
	$search_datep_month					 = '';
	$search_datep_year					 = '';
	$search_fin_validite_day		 = '';
	$search_fin_validite_month	 = '';
	$search_fin_validite_year		 = '';
	$search_date_cloture_day		 = '';
	$search_date_cloture_month	 = '';
	$search_date_cloture_year		 = '';
	$search_fk_statut						 = '';
	$search_total_ht						 = '';
	$search_note_private				 = '';
	$search_note_public					 = '';
	$search_date_livraison_day	 = '';
	$search_date_livraison_month = '';
	$search_date_livraison_year	 = '';
	$search_societe_nom					 = '';
	$search_id									 = '';
	$search_entity							 = '';
	$search_ref_int							 = '';
	$search_ref_client					 = '';
	$search_tms_day							 = '';
	$search_tms_month						 = '';
	$search_tms_year						 = '';
	$search_date_valid_day			 = '';
	$search_date_valid_month		 = '';
	$search_date_valid_year			 = '';
	$search_fk_user_author			 = '';
	$search_fk_user_modif				 = '';
	$search_fk_user_valid				 = '';
	$search_fk_user_cloture			 = '';
	$search_price								 = '';
	$search_remise_absolue			 = '';
	$search_tva									 = '';
	$search_localtax1						 = '';
	$search_localtax2						 = '';
	$search_total								 = '';
	$search_fk_account					 = '';
	$search_fk_currency					 = '';
	$search_fk_shipping_method	 = '';
	$search_fk_availability			 = '';
	$search_fk_input_reason			 = '';
	$search_location_incoterms	 = '';
	$search_extraparams					 = '';
	$search_fk_delivery_address	 = '';
	$search_societe_name_alias	 = '';
	$search_societe_ref_int			 = '';


	// -----------------------------------------------------

	$search_array_options = array();
}

// ***************************************************************************************************************
// 
//                                             Actions Part 1 - Avant collecte de données
// 
// ***************************************************************************************************************
// ---------------------------------------- action après Drag&Drop d'une tuile ==> mise à jour du "Status" de l'objet
// doit rester avant collecte de données car peut les modifier en amont
if ($action == 'cardDrop') {

	require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
	$response		 = array();
	$object			 = new Propal($db);
	$id					 = GETPOST('id', 'int');
	$newStatusID = GETPOST('newStatusID', 'int');
	$err				 = 0;
	if ($id > 0 && in_array($newStatusID, array(Propal::STATUS_DRAFT, Propal::STATUS_VALIDATED, Propal::STATUS_SIGNED, Propal::STATUS_NOTSIGNED, Propal::STATUS_BILLED))) {
		$ret = $object->fetch($id);
		if ($ret > 0) {
			// on ne peut pas repasser à brouillon
			if (($object->statut != Propal::STATUS_DRAFT && $newStatusID == Propal::STATUS_DRAFT)) {
				$response['status']	 = 'KO';
				$response['message'] = $langs->trans("ActionNotAllowed_PropalDestDraft");

				// on ne peut pas passer de brouillon à autre chose que validée 
			} elseif ($object->statut == Propal::STATUS_DRAFT && $newStatusID != Propal::STATUS_VALIDATED) {
				$response['status']	 = 'KO';
				$response['message'] = $langs->trans("ActionNotAllowed_PropalFromDraftToValidatedOnly");

				// on ne peut pas passer de autre chose que signed à facturée
			} elseif ($object->statut != Propal::STATUS_SIGNED && $newStatusID == Propal::STATUS_BILLED) { // on ne peut pas passer de brouillon à autre chose que validée 
				$response['status']	 = 'KO';
				$response['message'] = $langs->trans("ActionNotAllowed_PropalFromSignedToBilleddOnly");
			} else {
				switch ($newStatusID) {
					case Propal::STATUS_DRAFT :
						// on ne doit pas arriver ici
						break;

					case Propal::STATUS_VALIDATED :
						if ($object->statut == Propal::STATUS_DRAFT) {
							$res = $object->valid($user);
							// regénération du pdf
							if ($res > 0) {
								$ret				 = $object->fetch($id); // Reload to get new records
								$newRef			 = $object->ref;
								$outputlangs = $langs;
								$newlang		 = '';
								if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id'))
									$newlang		 = GETPOST('lang_id', 'alpha');
								if ($conf->global->MAIN_MULTILANGS && empty($newlang))
									$newlang		 = $object->thirdparty->default_lang;
								if (!empty($newlang)) {
									$outputlangs = new Translate("", $conf);
									$outputlangs->setDefaultLang($newlang);
								}
								$model = $object->modelpdf;
								$object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);

								if ($res > 0) {
									$response['status']	 = 'OK';
									$response['message'] = $langs->trans("PropalSuccessfullyValidated");
									// setEventMessages($langs->trans("RecordUpdatedSuccessfully"), null);
								}
							}
						} else {
							$res = $object->reopen($user, 1);
							if ($res > 0) {
								$response['status']	 = 'OK';
								$response['message'] = $langs->trans("PropalSuccessfullyReopened");
								// setEventMessages($langs->trans("RecordUpdatedSuccessfully"), null);
							}
						}
						break;

					case Propal::STATUS_SIGNED :
						$res = $object->cloture($user, Propal::STATUS_SIGNED, '');
						if ($res > 0) {
							$response['status']	 = 'OK';
							$response['message'] = $langs->trans("PropalSuccessfullyClosedSigned");
							// setEventMessages($langs->trans("RecordUpdatedSuccessfully"), null);
						}
						break;

					case Propal::STATUS_NOTSIGNED :
						$res = $object->cloture($user, Propal::STATUS_NOTSIGNED, '');
						if ($res > 0) {
							$response['status']	 = 'OK';
							$response['message'] = $langs->trans("PropalSuccessfullyClosedNotSigned");
							// setEventMessages($langs->trans("RecordUpdatedSuccessfully"), null);
						}
						break;

					case Propal::STATUS_BILLED :
						$res = $object->cloture($user, Propal::STATUS_BILLED, '');
						if ($res > 0) {
							$response['status']	 = 'OK';
							$response['message'] = $langs->trans("PropalSuccessfullyClosedBilled");
							// setEventMessages($langs->trans("RecordUpdatedSuccessfully"), null);
						}
						break;
				}

				if ($res <= 0) {
					$response['status']	 = 'KO';
					$response['message'] = $object->error;
				}
				
			}
		} elseif ($ret == 0) {
			dol_syslog('RecordNotFound : Propal : ' . $id, LOG_DEBUG);
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

	if ($response['status'] == 'OK')
		$response['data']['newRef'] = $newRef;

	$response['token'] = $_SESSION['newtoken'];

	// l'envoi de la réponse se fait plus loin après collecte de données pour pour pouvoir completer $response avec le nbre d'éléments de chaque colonne
	// exit(json_encode($response));
}

// **************************************************************************************************************
//
//                                        Kanban - Collecte de données
//
// ***************************************************************************************************************
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


if ($search_rowid != '')
	$WHERE .= natural_search("t.rowid", $search_rowid, 1);
if ($search_ref != '')
	$WHERE .= natural_search("t.ref", $search_ref);
if ($search_dd_datec_mysql != '' && $search_df_datec_mysql != '') {
	// si date début et date fin sont dans le mauvais ordre, on les inverse
	if ($search_dd_datec_mysql > $search_df_datec_mysql) {
		$tmp									 = $search_dd_datec_mysql;
		$search_dd_datec_mysql = $search_df_datec_mysql;
		$search_df_datec_mysql = $tmp;
	}

	$WHERE .= " AND (t.datec BETWEEN '" . $search_dd_datec_mysql . "' AND '" . $search_df_datec_mysql . "')";
}
if ($search_fk_soc != '')
	$WHERE .= natural_search("t.fk_soc", $search_fk_soc);
if ($search_fk_projet != '')
	$WHERE .= natural_search("t.fk_projet", $search_fk_projet);
if ($search_datep_month > 0) {
	if ($search_datep_year > 0 && empty($search_datep_day))
		$WHERE .= " AND t.datep BETWEEN '" . $db->idate(dol_get_first_day($search_datep_year, $search_datep_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datep_year, $search_datep_month, false)) . "'";
	else if ($search_datep_year > 0 && !empty($search_datep_day))
		$WHERE .= " AND t.datep BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_datep_month, $search_datep_day, $search_datep_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_datep_month, $search_datep_day, $search_datep_year)) . "'";
	else
		$WHERE .= " AND date_format(t.datep, '%m') = '" . $search_datep_month . "'";
}
else if ($search_datep_year > 0) {
	$WHERE .= " AND t.datep BETWEEN '" . $db->idate(dol_get_first_day($search_datep_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datep_year, 12, false)) . "'";
}
if ($search_fin_validite_month > 0) {
	if ($search_fin_validite_year > 0 && empty($search_fin_validite_day))
		$WHERE .= " AND t.fin_validite BETWEEN '" . $db->idate(dol_get_first_day($search_fin_validite_year, $search_fin_validite_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_fin_validite_year, $search_fin_validite_month, false)) . "'";
	else if ($search_fin_validite_year > 0 && !empty($search_fin_validite_day))
		$WHERE .= " AND t.fin_validite BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_fin_validite_month, $search_fin_validite_day, $search_fin_validite_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_fin_validite_month, $search_fin_validite_day, $search_fin_validite_year)) . "'";
	else
		$WHERE .= " AND date_format(t.fin_validite, '%m') = '" . $search_fin_validite_month . "'";
}
else if ($search_fin_validite_year > 0) {
	$WHERE .= " AND t.fin_validite BETWEEN '" . $db->idate(dol_get_first_day($search_fin_validite_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_fin_validite_year, 12, false)) . "'";
}
if ($search_date_cloture_month > 0) {
	if ($search_date_cloture_year > 0 && empty($search_date_cloture_day))
		$WHERE .= " AND t.date_cloture BETWEEN '" . $db->idate(dol_get_first_day($search_date_cloture_year, $search_date_cloture_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_cloture_year, $search_date_cloture_month, false)) . "'";
	else if ($search_date_cloture_year > 0 && !empty($search_date_cloture_day))
		$WHERE .= " AND t.date_cloture BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_date_cloture_month, $search_date_cloture_day, $search_date_cloture_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_date_cloture_month, $search_date_cloture_day, $search_date_cloture_year)) . "'";
	else
		$WHERE .= " AND date_format(t.date_cloture, '%m') = '" . $search_date_cloture_month . "'";
}
else if ($search_date_cloture_year > 0) {
	$WHERE .= " AND t.date_cloture BETWEEN '" . $db->idate(dol_get_first_day($search_date_cloture_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_cloture_year, 12, false)) . "'";
}
if ($search_fk_statut != '')
	$WHERE .= natural_search("t.fk_statut", $search_fk_statut, 1);
if ($search_total_ht != '')
	$WHERE .= natural_search("t.total_ht", $search_total_ht, 1);
if ($search_note_private != '')
	$WHERE .= natural_search("t.note_private", $search_note_private);
if ($search_note_public != '')
	$WHERE .= natural_search("t.note_public", $search_note_public);
if ($search_date_livraison_month > 0) {
	if ($search_date_livraison_year > 0 && empty($search_date_livraison_day))
		$WHERE .= " AND t.date_livraison BETWEEN '" . $db->idate(dol_get_first_day($search_date_livraison_year, $search_date_livraison_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_livraison_year, $search_date_livraison_month, false)) . "'";
	else if ($search_date_livraison_year > 0 && !empty($search_date_livraison_day))
		$WHERE .= " AND t.date_livraison BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_date_livraison_month, $search_date_livraison_day, $search_date_livraison_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_date_livraison_month, $search_date_livraison_day, $search_date_livraison_year)) . "'";
	else
		$WHERE .= " AND date_format(t.date_livraison, '%m') = '" . $search_date_livraison_month . "'";
}
else if ($search_date_livraison_year > 0) {
	$WHERE .= " AND t.date_livraison BETWEEN '" . $db->idate(dol_get_first_day($search_date_livraison_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_livraison_year, 12, false)) . "'";
}
if ($search_societe_nom != '')
	$WHERE .= natural_search("" . LLX_ . "societe.nom", $search_societe_nom);
if ($search_id != '')
	$WHERE .= natural_search("t.rowid", $search_id, 1);
if ($search_entity != '')
	$WHERE .= natural_search("t.entity", $search_entity, 1);
if ($search_ref_int != '')
	$WHERE .= natural_search("t.ref_int", $search_ref_int);
if ($search_ref_client != '')
	$WHERE .= natural_search("t.ref_client", $search_ref_client);
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
if ($search_date_valid_month > 0) {
	if ($search_date_valid_year > 0 && empty($search_date_valid_day))
		$WHERE .= " AND t.date_valid BETWEEN '" . $db->idate(dol_get_first_day($search_date_valid_year, $search_date_valid_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_valid_year, $search_date_valid_month, false)) . "'";
	else if ($search_date_valid_year > 0 && !empty($search_date_valid_day))
		$WHERE .= " AND t.date_valid BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_date_valid_month, $search_date_valid_day, $search_date_valid_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_date_valid_month, $search_date_valid_day, $search_date_valid_year)) . "'";
	else
		$WHERE .= " AND date_format(t.date_valid, '%m') = '" . $search_date_valid_month . "'";
}
else if ($search_date_valid_year > 0) {
	$WHERE .= " AND t.date_valid BETWEEN '" . $db->idate(dol_get_first_day($search_date_valid_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_valid_year, 12, false)) . "'";
}
if ($search_fk_user_author != '')
	$WHERE .= natural_search("t.fk_user_author", $search_fk_user_author, 1);
if ($search_fk_user_modif != '')
	$WHERE .= natural_search("t.fk_user_modif", $search_fk_user_modif, 1);
if ($search_fk_user_valid != '')
	$WHERE .= natural_search("t.fk_user_valid", $search_fk_user_valid, 1);
if ($search_fk_user_cloture != '')
	$WHERE .= natural_search("t.fk_user_cloture", $search_fk_user_cloture, 1);
if ($search_price != '')
	$WHERE .= natural_search("t.price", $search_price, 1);
if ($search_remise_absolue != '')
	$WHERE .= natural_search("t.remise_absolue", $search_remise_absolue, 1);
if ($search_tva != '')
	$WHERE .= natural_search("t.tva", $search_tva, 1);
if ($search_localtax1 != '')
	$WHERE .= natural_search("t.localtax1", $search_localtax1, 1);
if ($search_localtax2 != '')
	$WHERE .= natural_search("t.localtax2", $search_localtax2, 1);
if ($search_total != '')
	$WHERE .= natural_search("t.total", $search_total, 1);
if ($search_fk_account != '')
	$WHERE .= natural_search("t.fk_account", $search_fk_account, 1);
if ($search_fk_currency != '')
	$WHERE .= natural_search("t.fk_currency", $search_fk_currency, 1);
if ($search_fk_shipping_method != '')
	$WHERE .= natural_search("t.fk_shipping_method", $search_fk_shipping_method, 1);
if ($search_fk_availability != '')
	$WHERE .= natural_search("t.fk_availability", $search_fk_availability, 1);
if ($search_fk_input_reason != '')
	$WHERE .= natural_search("t.fk_input_reason", $search_fk_input_reason, 1);
if ($search_location_incoterms != '')
	$WHERE .= natural_search("t.location_incoterms", $search_location_incoterms);
if ($search_extraparams != '')
	$WHERE .= natural_search("t.extraparams", $search_extraparams);
if ($search_fk_delivery_address != '')
	$WHERE .= natural_search("t.fk_delivery_address", $search_fk_delivery_address, 1);
if ($search_societe_name_alias != '')
	$WHERE .= natural_search("" . LLX_ . "societe.name_alias", $search_societe_name_alias);
if ($search_societe_ref_int != '')
	$WHERE .= natural_search("" . LLX_ . "societe.ref_int", $search_societe_ref_int);


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

include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_main_propals.class.php';
$ReqObject				 = new ReqKbMainPropals($db);
// les "isNew" sont à "false" parce qu'on veut garder les paramétrage de la requete d'origine
$num							 = $ReqObject->fetchAll($limit, $offset, $ORDERBY, $isNewOrderBy			 = false, $WHERE, $isNewWhere				 = false, $HAVING, $isNewHaving			 = false);
$nbtotalofrecords	 = $ReqObject->nbtotalofrecords;


// --------------------------- Requête fournissant les titres des colonnes
// 
$titlesValues			 = "DRAFT,VALIDATED,SIGNED,NOTSIGNED,BILLED";
$columnsArray			 = array();
$columnsIDsArray	 = array(); // tableau associatif : 'titre' => 'son id', ça nous permet de retrouver (côté js) les ids des Statuts en fonction de leur code 
$columnsCountArray = array(); // tableau associatif : 'titre' => 'nbre d'éléments' dans la colonne (incrémenté dans la boucle de parcours des données principales) 
$columnsTitles		 = explode(",", $titlesValues);
$countColumns			 = count($columnsTitles);
if ($countColumns > 0) {
	for ($i = 0; $i < $countColumns; $i++) {
		$columnsArray[$i]['headerText']				 = $langs->trans($columnsTitles[$i]);
		$columnsArray[$i]['key']							 = $columnsTitles[$i];
		$columnsArray[$i]['allowDrag']				 = true;
		$columnsArray[$i]['allowDrop']				 = true;
		$columnsIDsArray[$columnsTitles[$i]]	 = 0; // voir le switch ci-dessous pour les ids
		$columnsCountArray[$columnsTitles[$i]] = 0; // sera incrémenté dans la boucle de parcours des données principales ci-dessous
		// traitements additionnels si nécessaire
		switch ($columnsTitles[$i]) {
			case "DRAFT" :
				$columnsArray[$i]['allowDrop']			 = false;
				$columnsIDsArray[$columnsTitles[$i]] = Propal::STATUS_DRAFT;
				break;
			case "VALIDATED" :
				$columnsIDsArray[$columnsTitles[$i]] = Propal::STATUS_VALIDATED;
				break;
			case "SIGNED" :
				$columnsIDsArray[$columnsTitles[$i]] = Propal::STATUS_SIGNED;
				break;
			case "NOTSIGNED" :
				$columnsIDsArray[$columnsTitles[$i]] = Propal::STATUS_NOTSIGNED;
				break;
			case "BILLED" :
				$columnsIDsArray[$columnsTitles[$i]] = Propal::STATUS_BILLED;
				break;
			default:
				break;
		}
	}
} else {
	dol_syslog('ColumnsTitles not supplied', LOG_ERR);
	setEventMessages("ColumnsTitlesNotSupplied", null, 'errors');
}

/// ---
// --------------------------- données principales
if (!empty($conf->global->KANVIEW_SHOW_PICTO))
	$fieldImageUrl = 'societe_logo';
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
		$dataArray[$i]['priority']			 = - $obj->datec; // date création timestamp inversé pour le trie descendant des cartes du kanban, voir fields.priority
		$dataArray[$i]['rowid']					 = $obj->rowid;
		$dataArray[$i]['ref']						 = $obj->ref;
		$dataArray[$i]['fk_projet']			 = $obj->fk_projet;
		$dataArray[$i]['datec']					 = $obj->datec;
		$dataArray[$i]['datep']					 = $obj->datep;
		$dataArray[$i]['fin_validite']	 = $obj->fin_validite;
		$dataArray[$i]['date_cloture']	 = $obj->date_cloture;
		$dataArray[$i]['fk_statut']			 = $obj->fk_statut; // keyField
		$dataArray[$i]['total_ht']			 = $obj->total_ht;
		$dataArray[$i]['note_private']	 = $obj->note_private;
		$dataArray[$i]['note_public']		 = $obj->note_public;
		$dataArray[$i]['date_livraison'] = $obj->date_livraison;
		$dataArray[$i]['fk_soc']				 = $obj->fk_soc;
		$dataArray[$i]['societe_logo']	 = $obj->societe_logo;
		$dataArray[$i]['societe_nom']		 = $obj->societe_nom;
		$dataArray[$i]['late_status']		 = ''; // calculé ci-dessous
		// affichage des dates et montants
		$dataArray[$i]['datep']					 = dol_print_date($obj->datep, 'day', 'tzuser');
		$dataArray[$i]['date_cloture']	 = dol_print_date($obj->date_cloture, 'day', 'tzuser');
		$dataArray[$i]['date_livraison'] = dol_print_date($obj->date_livraison, 'day', 'tzuser');
		$dataArray[$i]['fin_validite']	 = dol_print_date($obj->fin_validite, 'day', 'tzuser');
		$dataArray[$i]['total_ht']			 = price($obj->total_ht, 0, '', 0, 0, 2, 'auto');
		$dataArray[$i]['total_ht']			 = str_replace(',', '.', $dataArray[$i]['total_ht']); // la virgule est utilisée comme séparateur de tags, ce n'est pas ce qu'on désire ici
		// la rubrique image a un traitement supplémentaire pour générer l'url complète de l'image
		if (($fieldImageUrl != 'null') && !empty($obj->{$fieldImageUrl})) {
			$dataArray[$i][$fieldImageUrl] = DOL_URL_ROOT . '/viewimage.php?modulepart=societe&file=' . $obj->fk_soc . '/logos/' . urlencode($obj->{$fieldImageUrl});
		}

		// traitements additionnels si nécessaire
		switch ($obj->fk_statut) {
			case Propal::STATUS_DRAFT :
				$dataArray[$i]['fk_statut']	 = "DRAFT";
				break;
			case Propal::STATUS_VALIDATED :
				$dataArray[$i]['fk_statut']	 = "VALIDATED";
				break;
			case Propal::STATUS_SIGNED :
				$dataArray[$i]['fk_statut']	 = "SIGNED";
				break;
			case Propal::STATUS_NOTSIGNED :
				$dataArray[$i]['fk_statut']	 = "NOTSIGNED";
				break;
			case Propal::STATUS_BILLED :
				$dataArray[$i]['fk_statut']	 = "BILLED";
				break;
			default:
				break;
		}

		// calcul du retard pour le paramétrage des couleurs
		// ce calcul n'a de sens que si la propale n'a pas encore été signée ni facturée
		if ($dataArray[$i]['fk_statut'] != "SIGNED" && $dataArray[$i]['fk_statut'] != "BILLED") {
			if (!empty($obj->fin_validite)) {
				$retard = intval((intval($obj->fin_validite) - intval(dol_now('tzserver'))) / (60 * 60 * 24)); // retard en jours
				if ($retard < 0) {
					$dataArray[$i]['late_status'] = 'KANVIEW_PROPALS_LATE4_COLOR'; // date fin valididté dépassé
				} elseif ($retard <= 7) {
					$dataArray[$i]['late_status'] = 'KANVIEW_PROPALS_LATE3_COLOR'; // plus que 7 jours
				} elseif ($retard <= 15) {
					$dataArray[$i]['late_status'] = 'KANVIEW_PROPALS_LATE2_COLOR'; // plus que 15 jours
				} elseif ($retard <= 30) {
					$dataArray[$i]['late_status'] = 'KANVIEW_PROPALS_LATE1_COLOR'; // plus que 30 jours
				} else {
					$dataArray[$i]['late_status'] = '';
				}
			} else {
				$dataArray[$i]['late_status'] = '';
			}
		}

		$columnsCountArray[$dataArray[$i]['fk_statut']]	 += 1; // on incrémente le nbre d'éléments dans la colonne
		// gestion tooltip
		$prefix																					 = '<div id="propal-' . $obj->rowid . '">'; // encapsulation du contenu dans un div pour permmettre l'affichage du tooltip
		$suffix																					 = '</div>';
		$dataArray[$i]['tooltip_content']								 = '<table><tbody>';
		$dataArray[$i]['tooltip_content']								 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainPropals_Fieldref') . '</b></td><td>: <span class="tooltip-ref-' . $obj->rowid . '">' . $obj->ref . '</span></td></tr>';
		$dataArray[$i]['tooltip_content']								 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainPropals_Fieldsociete_nom') . '</b></td><td>: ' . $obj->societe_nom . '</td></tr>';
		$dataArray[$i]['tooltip_content']								 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainPropals_Fielddatep') . '</b></td><td>: ' . $dataArray[$i]['datep'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']								 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainPropals_Fielddate_livraison') . '</b></td><td>: ' . $dataArray[$i]['date_livraison'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']								 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainPropals_Fieldfin_validite') . '</b></td><td>: ' . $dataArray[$i]['fin_validite'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']								 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainPropals_Fieldtotal_ht') . '</b></td><td>: ' . price($obj->total_ht, 0, '', 0, 0, 2, 'auto') . '</td></tr>';
		$dataArray[$i]['tooltip_content']								 .= '</tbody></table>';

		// contenu
		$dataArray[$i]['ref_tiers'] = '<a class="object-link" href="' . DOL_URL_ROOT . '/comm/propal/card.php?id=' . $obj->rowid . '" target="_blank">' . $obj->ref . '</a>' . $prefix . $obj->societe_nom . $suffix;

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
				$kanbanHeaderCounts[$key]				 = $value; // si action ajax, ce tableau permet la mise à jour du nbre des taches de chaque colonne
				break;
			}
		}
	}
}

// événements à partir de requêtes additionnelles
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
if ($search_ref != '')
	$params	 .= '&amp;search_ref=' . urlencode($search_ref);
if ($search_datec != '')
	$params	 .= '&amp;search_datec=' . urlencode($search_datec);
if ($search_fk_soc != '')
	$params	 .= '&amp;search_fk_soc=' . urlencode($search_fk_soc);
if ($search_fk_projet != '')
	$params	 .= '&amp;search_fk_projet=' . urlencode($search_fk_projet);
if ($search_datep != '')
	$params	 .= '&amp;search_datep=' . urlencode($search_datep);
if ($search_fin_validite != '')
	$params	 .= '&amp;search_fin_validite=' . urlencode($search_fin_validite);
if ($search_date_cloture != '')
	$params	 .= '&amp;search_date_cloture=' . urlencode($search_date_cloture);
if ($search_fk_statut != '')
	$params	 .= '&amp;search_fk_statut=' . urlencode($search_fk_statut);
if ($search_total_ht != '')
	$params	 .= '&amp;search_total_ht=' . urlencode($search_total_ht);
if ($search_note_private != '')
	$params	 .= '&amp;search_note_private=' . urlencode($search_note_private);
if ($search_note_public != '')
	$params	 .= '&amp;search_note_public=' . urlencode($search_note_public);
if ($search_date_livraison != '')
	$params	 .= '&amp;search_date_livraison=' . urlencode($search_date_livraison);
if ($search_societe_nom != '')
	$params	 .= '&amp;search_societe_nom=' . urlencode($search_societe_nom);
if ($search_id != '')
	$params	 .= '&amp;search_id=' . urlencode($search_id);
if ($search_entity != '')
	$params	 .= '&amp;search_entity=' . urlencode($search_entity);
if ($search_ref_int != '')
	$params	 .= '&amp;search_ref_int=' . urlencode($search_ref_int);
if ($search_ref_client != '')
	$params	 .= '&amp;search_ref_client=' . urlencode($search_ref_client);
if ($search_tms != '')
	$params	 .= '&amp;search_tms=' . urlencode($search_tms);
if ($search_date_valid != '')
	$params	 .= '&amp;search_date_valid=' . urlencode($search_date_valid);
if ($search_fk_user_author != '')
	$params	 .= '&amp;search_fk_user_author=' . urlencode($search_fk_user_author);
if ($search_fk_user_modif != '')
	$params	 .= '&amp;search_fk_user_modif=' . urlencode($search_fk_user_modif);
if ($search_fk_user_valid != '')
	$params	 .= '&amp;search_fk_user_valid=' . urlencode($search_fk_user_valid);
if ($search_fk_user_cloture != '')
	$params	 .= '&amp;search_fk_user_cloture=' . urlencode($search_fk_user_cloture);
if ($search_price != '')
	$params	 .= '&amp;search_price=' . urlencode($search_price);
if ($search_remise_absolue != '')
	$params	 .= '&amp;search_remise_absolue=' . urlencode($search_remise_absolue);
if ($search_tva != '')
	$params	 .= '&amp;search_tva=' . urlencode($search_tva);
if ($search_localtax1 != '')
	$params	 .= '&amp;search_localtax1=' . urlencode($search_localtax1);
if ($search_localtax2 != '')
	$params	 .= '&amp;search_localtax2=' . urlencode($search_localtax2);
if ($search_total != '')
	$params	 .= '&amp;search_total=' . urlencode($search_total);
if ($search_fk_account != '')
	$params	 .= '&amp;search_fk_account=' . urlencode($search_fk_account);
if ($search_fk_currency != '')
	$params	 .= '&amp;search_fk_currency=' . urlencode($search_fk_currency);
if ($search_fk_shipping_method != '')
	$params	 .= '&amp;search_fk_shipping_method=' . urlencode($search_fk_shipping_method);
if ($search_fk_availability != '')
	$params	 .= '&amp;search_fk_availability=' . urlencode($search_fk_availability);
if ($search_fk_input_reason != '')
	$params	 .= '&amp;search_fk_input_reason=' . urlencode($search_fk_input_reason);
if ($search_location_incoterms != '')
	$params	 .= '&amp;search_location_incoterms=' . urlencode($search_location_incoterms);
if ($search_extraparams != '')
	$params	 .= '&amp;search_extraparams=' . urlencode($search_extraparams);
if ($search_fk_delivery_address != '')
	$params	 .= '&amp;search_fk_delivery_address=' . urlencode($search_fk_delivery_address);
if ($search_societe_name_alias != '')
	$params	 .= '&amp;search_societe_name_alias=' . urlencode($search_societe_name_alias);
if ($search_societe_ref_int != '')
	$params	 .= '&amp;search_societe_ref_int=' . urlencode($search_societe_ref_int);

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
//                                     VIEW - Envoi du header et Filter
// 
// ***************************************************************************************************************

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

$help_url = ''; // EN:Module_Kanban_En|FR:Module_Kanban|AR:M&oacute;dulo_Kanban';
llxHeader('', $langs->trans("Kanview_KB_Propals"), $help_url, '', 0, 0, $arrayofjs, $arrayofcss, '');

$form = new Form($db);

$head = kanview_kanban_prepare_head($params);

$tabactive = '__KANBAN_TABACTIVE__';

// dol_fiche_head($head, $tabactive, $langs->trans('Kanview_KB_Propals'), 0, 'action');
// le selecteur du nbre d'éléments par page généré par print_barre_liste() doit se trouver ds le <form>
// cette ligne doit donc rester avant l'appel à print_barre_liste()
print '<form name="listactionsfilter" class="listactionsfilter" action="' . $_SERVER["PHP_SELF"] . '" method="get">';

//
// __KANBAN_BEFORE_TITLE__
// 
// titre du Kanban
$title = $langs->trans('Kanview_KB_Propals');
print_barre_liste($title, intval($page), $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', intval($num) + 1, intval($nbtotalofrecords), 'title_commercial.png', 0, '', '', intval($limit));

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
	print '<td style="width: 70%">';
}
/// ---
// fieldset filtre - formulaire et table contenant le filtre pour le Kanban
print '<fieldset class="filters-fields" style="width: 99%; height: 100%; padding-right: 1px;">';
print '<legend><span class="e-icon e-filter" style="text-align: left;"></span></legend>';

if (!empty($conf->browser->phone))
	print '<div class="fichehalfleft">';
else
	print '<table class="nobordernopadding" width="100%"><tr style="width: 100%; height: 100%;"><td class="borderright">';

print '<table class="nobordernopadding" width="100%">';

// 
// ----------- Date Début/Fin Du Kanban
// 
//$value = empty($date_debut) ? - 1 : $date_debut;
//echo '<tr id="tr-periode">';
//echo '<td class="td-card-label">' . $langs->trans("Periode") . '&nbsp;&nbsp;&nbsp;&nbsp;' . $langs->trans("Du") . '</td>';
//echo '<td class="td-card-data" style="padding-bottom: 5px;">';
//$form->select_date($value, 'dd_', '', '', '', "dd", 1, 1); // datepicker
//$value = empty($date_fin) ? - 1 : $date_fin;
//echo '&nbsp;&nbsp;&nbsp;&nbsp;<span>' . $langs->trans("Au") . '</span>&nbsp;';
//$form->select_date($value, 'df_', '', '', '', "df", 1, 1); // datepicker
//echo '</td>';
//echo '</tr>';
// 
// ------------- filtre --req_kb_main_propals-- datec - période
// 
$value = empty($search_dd_datec) ? - 1 : $search_dd_datec;
echo '<tr id="tr-periode">';
echo '<td class="td-card-label">' . $langs->trans("ReqKbMainPropals_Fielddatec") . '</td>';
echo '<td>' . $langs->trans("Du") . '</td>';
echo '<td class="td-card-data">';
$form->select_date($value, 'search_dd_datec_', '', '', '', "dd", 1, 1); // datepicker
$value = empty($search_df_datec) ? - 1 : $search_df_datec;
echo '&nbsp;&nbsp;&nbsp;&nbsp;<span>' . $langs->trans("Au") . '</span>&nbsp;';
$form->select_date($value, 'search_df_datec_', '', '', '', "df", 1, 1); // datepicker
echo '</td>';
echo '</tr>';

// 
// ------------- filtre --req_kb_main_propals-- fk_soc
// 
echo '<tr id="tr-search_fk_soc" class="tr-external-filter"><td class="td-card-label">' . $langs->trans('ReqKbMainPropals_Fieldfk_soc') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="fk_soc_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';

$SQL						 = "SELECT 
	t.rowid AS rowid,	
	t.nom AS societe_nom,	
	t.name_alias AS name_alias,	
	t.ref_int AS ref_int
FROM 
	" . LLX_ . "societe AS t 
WHERE 
	t.status = 1
ORDER BY
	t.nom
";
$sql_fk_soc			 = $db->query($SQL);
$options				 = array();
$optionSelected	 = '';
$defaultValue		 = '';
$blanckOption		 = 1;
if ($sql_fk_soc) {
	while ($obj_fk_soc = $db->fetch_object($sql_fk_soc)) {
		if ($obj_fk_soc->rowid == $search_fk_soc) {
			$optionSelected = $obj_fk_soc->rowid;
		}
		$options[$obj_fk_soc->rowid] = $obj_fk_soc->societe_nom;
	}
	$db->free($sql_fk_soc);
} else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
}
echo '<select  id="fk_soc_input_filtre" class="flat" name="search_fk_soc" title="">';
if (empty($optionSelected) && !$blanckOption) {
	$optionSelected = $defaultValue;
}
if ($blanckOption) {
	echo '<option value="" ' . (empty($search_fk_soc) ? 'selected' : '') . '></option>';
	echo '<option value="" ' . (empty($search_fk_soc) ? 'selected' : '') . '></option>'; // pour une raison obscure, en mode ajax, il faut 2 options vides pour affciher une option vide
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $value . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
echo ajax_combobox('fk_soc_input_filtre');

print '</table>';

if (!empty($conf->browser->phone))
	print '</div>';
else
	print '</td>';

if (!empty($conf->browser->phone))
	print '<div class="fichehalfright">';
else
	print '<td align="center" valign="middle" class="nowrap">';

// ---- bouton refresh
print '<table><tr><td align="center">';
print '<div class="formleftzone">';
print '<input type="submit" class="button" style="min-width:120px" name="refresh" value="' . $langs->trans("Refresh") . '">';
print '</div>';
print '</td></tr>';
print '</table>';

if (!empty($conf->browser->phone))
	print '</div>';
else
	print '</td></tr></table>';

print '</fieldset>'; /// .filters-fields
//
// ------ fieldset legend
// la légende n'est affichée que pour grand écran
if (empty($conf->browser->phone)) {
	print '</td>';
	print '<td style="width: 80%; ">'; // garder les 80% sinon défaut d'affichage
	print '<fieldset class="filters-fields" style="height: 100%; padding-left: 5px;">';
	print '<legend><span class="" style="text-align: left;">' . $langs->trans('LEGEND') . '</span></legend>';
	print '<table>';

	// -- legend 1
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROPALS_LATE1_COLOR . ' transparent;">'
// le découpage suivant n'a pas marché sur FF
//			. 'border-color-top: transparent; '
//			. 'border-color-right: transparent; '
//			. 'border-color-bottom: #007bff; '
//			. 'border-color-left: transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROPALS_LATE1_COLOR') . '</td>';
	print '</tr>';
	// -- legend 2
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROPALS_LATE2_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROPALS_LATE2_COLOR') . '</td>';
	print '</tr>';
	// -- legend 3
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROPALS_LATE3_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROPALS_LATE3_COLOR') . '</td>';
	print '</tr>';
	// -- legend 4
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_PROPALS_LATE4_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROPALS_LATE4_COLOR') . '</td>';
	print '</tr>';
	// -- legend 5
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . '#179BD7' . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_PROPALS_NOT_LATE_COLOR') . '</td>';
	print '</tr>';
	// -- legend 6 (TAG)
	print '<tr>';
	print '<td>';
	print '<div class="legend-name">TAG</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans(strtoupper($conf->global->KANVIEW_PROPALS_TAG)) . '</td>';
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
//                                          VIEW - Kanban Output
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

	// var_dump($columnIDs);
	// $now = dol_print_date(dol_now('tzuser'), $format = '%Y-%m-%d %H:%M:%S', $tzoutput = 'tzuser', $outputlangs = '', $encodetooutput = false);
}

// __KANBAN_AFTER_KANBAN__
// 
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
		var fieldImageUrl		  	= "' . trim($fieldImageUrl) . '";
 		var mytitle = "' . trim($langs->trans('Kanview_TopMenu_Dashboard')) . '";
		var UpdateNotAllowed_ProjectClosed = "' . trim($langs->transnoentities('UpdateNotAllowed_ProjectClosed')) . '"; 
		var msgPrintKanbanView				= "' . trim($langs->transnoentities('msgPrintKanbanView')) . '";

		var locale									= "' . trim($langs->defaultlang) . '";
		var sfLocale								= "' . trim(str_replace('_', '-', $langs->defaultlang)) . '";

		var enableNativeTotalCount		= ' . trim(empty($enableNativeTotalCount) ? 'false' : 'true') . ';
		var tooltipsActive						= false;		// mémorise le fait que les tooltps sont activés ou non

		var parent1	= "' . trim(module) . '";
		var parent2	= "' . trim($container) . '";

		var columnIDs			= ' . trim($columnIDs) . ';
		var kanbanData		= ' . trim($kanbanData) . ';
		var columns				= ' . trim($columns) . ';
		var propals_tag		= "' . trim($conf->global->KANVIEW_PROPALS_TAG) . '";
		var colorMapping  =  {
	        "' . trim($conf->global->KANVIEW_PROPALS_LATE1_COLOR) . '": "KANVIEW_PROPALS_LATE1_COLOR",
	        "' . trim($conf->global->KANVIEW_PROPALS_LATE2_COLOR) . '": "KANVIEW_PROPALS_LATE2_COLOR",
	        "' . trim($conf->global->KANVIEW_PROPALS_LATE3_COLOR) . '": "KANVIEW_PROPALS_LATE3_COLOR",
	        "' . trim($conf->global->KANVIEW_PROPALS_LATE4_COLOR) . '": "KANVIEW_PROPALS_LATE4_COLOR",
	      };
				
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
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_propals_kanban');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_propals_kanban', 'remove');

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

