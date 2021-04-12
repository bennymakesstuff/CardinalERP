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
require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';	// BugFix : utilisé par fournisseur.facture.class.php mais non référencée par celui-ci
require_once DOL_DOCUMENT_ROOT . '/fourn/class/fournisseur.facture.class.php';

if (!hasPermissionForKanbanView('invoices_suppliers')) {
	accessforbidden();
	exit();
}

$build = '094152547';

$langs->load("kanview@kanview");
$langs->load('fournisseur');
$langs->load("other");

// ***************************************************************************************************************
// 
//                                    Traitements des GET/POST
// 
// *************************************************************************************************************** 

$view		 = GETPOST("view", '', 3); // type de vue : standard
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

// 1er affichage, par défaut : la Date Fin est la date d'aujourd'hui
if ((empty($df_year) || $df_year == '0000') && (empty($df_month) || $df_month == '00') && (empty($df_day) || $df_day == '00')) {
	$df_year	 = str_pad(date('Y'), 2, '0', STR_PAD_LEFT);
	$df_month	 = str_pad(date('m'), 2, '0', STR_PAD_LEFT);
	$df_day		 = str_pad(date('d'), 2, '0', STR_PAD_LEFT);
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
$search_societe_logo						 = GETPOST('search_societe_logo', 'alpha');
$search_societe_nom_alias				 = GETPOST('search_societe_nom_alias', 'alpha');
$search_societe_nom							 = GETPOST('search_societe_nom', 'alpha');
$search_extraparams							 = GETPOST('search_extraparams', 'alpha');
$search_import_key							 = GETPOST('search_import_key', 'alpha');
$search_model_pdf								 = GETPOST('search_model_pdf', 'alpha');
$search_location_incoterms			 = GETPOST('search_location_incoterms', 'alpha');
$search_fk_incoterms						 = GETPOST('search_fk_incoterms', 'alpha');
$search_note_public							 = GETPOST('search_note_public', 'alpha');
$search_note_private						 = GETPOST('search_note_private', 'alpha');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_date_lim_reglement_day	 = GETPOST('search_date_lim_reglement_day', 'int');
$search_date_lim_reglement_month = GETPOST('search_date_lim_reglement_month', 'int');
$search_date_lim_reglement_year	 = GETPOST('search_date_lim_reglement_year', 'int');

$search_fk_mode_reglement	 = GETPOST('search_fk_mode_reglement', 'alpha');
$search_fk_cond_reglement	 = GETPOST('search_fk_cond_reglement', 'alpha');
$search_fk_account				 = GETPOST('search_fk_account', 'alpha');
$search_fk_projet					 = GETPOST('search_fk_projet', 'alpha');
$search_fk_facture_source	 = GETPOST('search_fk_facture_source', 'alpha');
$search_fk_user_valid			 = GETPOST('search_fk_user_valid', 'alpha');
$search_fk_user_modif			 = GETPOST('search_fk_user_modif', 'alpha');
$search_fk_user_author		 = GETPOST('search_fk_user_author', 'alpha');
$search_fk_statut					 = GETPOST('search_fk_statut', 'alpha');
$search_total_ttc					 = GETPOST('search_total_ttc', 'alpha');
$search_total_tva					 = GETPOST('search_total_tva', 'alpha');
$search_total_ht					 = GETPOST('search_total_ht', 'alpha');
$search_total							 = GETPOST('search_total', 'alpha');
$search_localtax2					 = GETPOST('search_localtax2', 'alpha');
$search_localtax1					 = GETPOST('search_localtax1', 'alpha');
$search_tva								 = GETPOST('search_tva', 'alpha');
$search_close_note				 = GETPOST('search_close_note', 'alpha');
$search_close_code				 = GETPOST('search_close_code', 'alpha');
$search_remise						 = GETPOST('search_remise', 'alpha');
$search_amount						 = GETPOST('search_amount', 'alpha');
$search_paye							 = GETPOST('search_paye', 'alpha');
$search_libelle						 = GETPOST('search_libelle', 'alpha');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_tms_day						 = GETPOST('search_tms_day', 'int');
$search_tms_month					 = GETPOST('search_tms_month', 'int');
$search_tms_year					 = GETPOST('search_tms_year', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_datef_day					 = GETPOST('search_datef_day', 'int');
$search_datef_month				 = GETPOST('search_datef_month', 'int');
$search_datef_year				 = GETPOST('search_datef_year', 'int');

// datec - date début
$search_dd_datec_day	 = str_pad(GETPOST('search_dd_datec_day', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_month = str_pad(GETPOST('search_dd_datec_month', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_year	 = str_pad(GETPOST('search_dd_datec_year', 'alpha'), 4, '0', STR_PAD_LEFT);
$search_dd_datec_hour	 = str_pad(GETPOST('search_dd_datec_hour', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_min	 = str_pad(GETPOST('search_dd_datec_min', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec_sec	 = str_pad(GETPOST('search_dd_datec_sec', 'alpha'), 2, '0', STR_PAD_LEFT);
$search_dd_datec			 = dol_stringtotime($search_dd_datec_year . $search_dd_datec_month . $search_dd_datec_day . $search_dd_datec_hour . $search_dd_datec_min . $search_dd_datec_sec, 0);
$search_dd_datec_mysql = dol_print_date($search_dd_datec, '%Y-%m-%d %H:%M:%S', 'tzserver'); // format mysql pour le WHERE
// 1er affichage, par défaut : la Date début est l'année flottante précédente
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
// 1er affichage, par défaut : la Date fin est aujourd'hui 
if ((empty($search_df_datec_year) || $search_df_datec_year == '0000') && (empty($search_df_datec_month) || $search_df_datec_month == '00') && (empty($search_df_datec_day) || $search_df_datec_day == '00')) {
	$search_df_datec_year	 = str_pad(date('Y'), 4, '0', STR_PAD_LEFT);
	$search_df_datec_month = str_pad(date('m'), 2, '0', STR_PAD_LEFT);
	$search_df_datec_day	 = str_pad(date('d'), 2, '0', STR_PAD_LEFT);
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

$search_fk_soc				 = GETPOST('search_fk_soc', 'alpha');
$search_type					 = GETPOST('search_type', 'alpha');
$search_ref_ext				 = GETPOST('search_ref_ext', 'alpha');
$search_entity				 = GETPOST('search_entity', 'alpha');
$search_ref_supplier	 = GETPOST('search_ref_supplier', 'alpha');
$search_ref						 = GETPOST('search_ref', 'alpha');
$search_id						 = GETPOST('search_id', 'alpha');
$search_rowid					 = GETPOST('search_rowid', 'alpha');
$search_total_paye		 = GETPOST('search_total_paye', 'alpha');
$search_nbre_services	 = GETPOST('search_nbre_services', 'int');
$search_nbre_lignes		 = GETPOST('search_nbre_lignes', 'alpha');
$search_nbre_produits	 = GETPOST('search_nbre_produits', 'alpha');
/// ---
// effacement de la recherche si demandée
// doit rester avant le calcul des WHERE/HAVING
if (GETPOST("button_removefilter_x") || GETPOST("button_removefilter.x") || GETPOST("button_removefilter")) { // All test are required to be compatible with all browsers
	// ------------------------------------------------------
	// $search_prop1 = '';
	$search_societe_logo						 = '';
	$search_societe_nom_alias				 = '';
	$search_societe_nom							 = '';
	$search_extraparams							 = '';
	$search_import_key							 = '';
	$search_model_pdf								 = '';
	$search_location_incoterms			 = '';
	$search_fk_incoterms						 = '';
	$search_note_public							 = '';
	$search_note_private						 = '';
	$search_date_lim_reglement_day	 = '';
	$search_date_lim_reglement_month = '';
	$search_date_lim_reglement_year	 = '';
	$search_fk_mode_reglement				 = '';
	$search_fk_cond_reglement				 = '';
	$search_fk_account							 = '';
	$search_fk_projet								 = '';
	$search_fk_facture_source				 = '';
	$search_fk_user_valid						 = '';
	$search_fk_user_modif						 = '';
	$search_fk_user_author					 = '';
	$search_fk_statut								 = '';
	$search_total_ttc								 = '';
	$search_total_tva								 = '';
	$search_total_ht								 = '';
	$search_total										 = '';
	$search_localtax2								 = '';
	$search_localtax1								 = '';
	$search_tva											 = '';
	$search_close_note							 = '';
	$search_close_code							 = '';
	$search_remise									 = '';
	$search_amount									 = '';
	$search_paye										 = '';
	$search_libelle									 = '';
	$search_tms_day									 = '';
	$search_tms_month								 = '';
	$search_tms_year								 = '';
	$search_datef_day								 = '';
	$search_datef_month							 = '';
	$search_datef_year							 = '';
	$search_datec_day								 = '';
	$search_datec_month							 = '';
	$search_datec_year							 = '';
	$search_fk_soc									 = '';
	$search_type										 = '';
	$search_ref_ext									 = '';
	$search_entity									 = '';
	$search_ref_supplier						 = '';
	$search_ref											 = '';
	$search_id											 = '';
	$search_rowid										 = '';
	$search_total_paye							 = '';
	$search_nbre_services						 = '';
	$search_nbre_lignes							 = '';
	$search_nbre_produits						 = '';

	$search_array_options = array();
}
/// ---
// paramètres additionnels
// __GETPOST_ADDITIONNELS__
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


$params = '';

//$params .= '&dd_year=' . $dd_year;
//$params .= '&dd_month=' . $dd_month;
//$params .= '&dd_day=' . $dd_day;
//$params .= '&dd_hour=' . $dd_hour;
//$params .= '&dd_min=' . $dd_min;
//$params .= '&dd_sec=' . $dd_sec;
//
//$params .= '&df_year=' . $df_year;
//$params .= '&df_month=' . $df_month;
//$params .= '&df_day=' . $df_day;
//$params .= '&df_hour=' . $df_hour;
//$params .= '&df_min=' . $df_min;
//$params .= '&df_sec=' . $df_sec;
//
//$params .= '&orientation=' . $orientation;
// params additionnels 
// 

if ($search_societe_logo != '')
	$params	 .= '&amp;search_societe_logo=' . urlencode($search_societe_logo);
if ($search_societe_nom_alias != '')
	$params	 .= '&amp;search_societe_nom_alias=' . urlencode($search_societe_nom_alias);
if ($search_societe_nom != '')
	$params	 .= '&amp;search_societe_nom=' . urlencode($search_societe_nom);
if ($search_extraparams != '')
	$params	 .= '&amp;search_extraparams=' . urlencode($search_extraparams);
if ($search_import_key != '')
	$params	 .= '&amp;search_import_key=' . urlencode($search_import_key);
if ($search_model_pdf != '')
	$params	 .= '&amp;search_model_pdf=' . urlencode($search_model_pdf);
if ($search_location_incoterms != '')
	$params	 .= '&amp;search_location_incoterms=' . urlencode($search_location_incoterms);
if ($search_fk_incoterms != '')
	$params	 .= '&amp;search_fk_incoterms=' . urlencode($search_fk_incoterms);
if ($search_note_public != '')
	$params	 .= '&amp;search_note_public=' . urlencode($search_note_public);
if ($search_note_private != '')
	$params	 .= '&amp;search_note_private=' . urlencode($search_note_private);
if ($search_date_lim_reglement != '')
	$params	 .= '&amp;search_date_lim_reglement=' . urlencode($search_date_lim_reglement);
if ($search_fk_mode_reglement != '')
	$params	 .= '&amp;search_fk_mode_reglement=' . urlencode($search_fk_mode_reglement);
if ($search_fk_cond_reglement != '')
	$params	 .= '&amp;search_fk_cond_reglement=' . urlencode($search_fk_cond_reglement);
if ($search_fk_account != '')
	$params	 .= '&amp;search_fk_account=' . urlencode($search_fk_account);
if ($search_fk_projet != '')
	$params	 .= '&amp;search_fk_projet=' . urlencode($search_fk_projet);
if ($search_fk_facture_source != '')
	$params	 .= '&amp;search_fk_facture_source=' . urlencode($search_fk_facture_source);
if ($search_fk_user_valid != '')
	$params	 .= '&amp;search_fk_user_valid=' . urlencode($search_fk_user_valid);
if ($search_fk_user_modif != '')
	$params	 .= '&amp;search_fk_user_modif=' . urlencode($search_fk_user_modif);
if ($search_fk_user_author != '')
	$params	 .= '&amp;search_fk_user_author=' . urlencode($search_fk_user_author);
if ($search_fk_statut != '')
	$params	 .= '&amp;search_fk_statut=' . urlencode($search_fk_statut);
if ($search_total_ttc != '')
	$params	 .= '&amp;search_total_ttc=' . urlencode($search_total_ttc);
if ($search_total_tva != '')
	$params	 .= '&amp;search_total_tva=' . urlencode($search_total_tva);
if ($search_total_ht != '')
	$params	 .= '&amp;search_total_ht=' . urlencode($search_total_ht);
if ($search_total != '')
	$params	 .= '&amp;search_total=' . urlencode($search_total);
if ($search_localtax2 != '')
	$params	 .= '&amp;search_localtax2=' . urlencode($search_localtax2);
if ($search_localtax1 != '')
	$params	 .= '&amp;search_localtax1=' . urlencode($search_localtax1);
if ($search_tva != '')
	$params	 .= '&amp;search_tva=' . urlencode($search_tva);
if ($search_close_note != '')
	$params	 .= '&amp;search_close_note=' . urlencode($search_close_note);
if ($search_close_code != '')
	$params	 .= '&amp;search_close_code=' . urlencode($search_close_code);
if ($search_remise != '')
	$params	 .= '&amp;search_remise=' . urlencode($search_remise);
if ($search_amount != '')
	$params	 .= '&amp;search_amount=' . urlencode($search_amount);
if ($search_paye != '')
	$params	 .= '&amp;search_paye=' . urlencode($search_paye);
if ($search_libelle != '')
	$params	 .= '&amp;search_libelle=' . urlencode($search_libelle);
if ($search_tms != '')
	$params	 .= '&amp;search_tms=' . urlencode($search_tms);
if ($search_datef != '')
	$params	 .= '&amp;search_datef=' . urlencode($search_datef);
if ($search_datec != '')
	$params	 .= '&amp;search_datec=' . urlencode($search_datec);
if ($search_fk_soc != '')
	$params	 .= '&amp;search_fk_soc=' . urlencode($search_fk_soc);
if ($search_type != '')
	$params	 .= '&amp;search_type=' . urlencode($search_type);
if ($search_ref_ext != '')
	$params	 .= '&amp;search_ref_ext=' . urlencode($search_ref_ext);
if ($search_entity != '')
	$params	 .= '&amp;search_entity=' . urlencode($search_entity);
if ($search_ref_supplier != '')
	$params	 .= '&amp;search_ref_supplier=' . urlencode($search_ref_supplier);
if ($search_ref != '')
	$params	 .= '&amp;search_ref=' . urlencode($search_ref);
if ($search_id != '')
	$params	 .= '&amp;search_id=' . urlencode($search_id);
if ($search_rowid != '')
	$params	 .= '&amp;search_rowid=' . urlencode($search_rowid);
if ($search_total_paye != '')
	$params	 .= '&amp;search_total_paye=' . urlencode($search_total_paye);
if ($search_nbre_services != '')
	$params	 .= '&amp;search_nbre_services=' . urlencode($search_nbre_services);
if ($search_nbre_lignes != '')
	$params	 .= '&amp;search_nbre_lignes=' . urlencode($search_nbre_lignes);
if ($search_nbre_produits != '')
	$params	 .= '&amp;search_nbre_produits=' . urlencode($search_nbre_produits);

// ***************************************************************************************************************
// 
//                                    Actions Part 1 - Avant collecte de données
// 
// ***************************************************************************************************************
// ---------------------------------------- action après Drag&Drop d'une tuile ==> mise à jour du "Status" de l'objet
// doit rester avant la connecte des données parce qu'elle peut les modifier en amont
// titres des colonnes
define("INVOICE_DRAFT", "INVOICE_DRAFT");
define("INVOICE_VALIDATED", "INVOICE_VALIDATED");
define("INVOICE_PAID", "INVOICE_PAID");
define("INVOICE_TO_CLASSIFY_PAID", "INVOICE_TO_CLASSIFY_PAID");
define("INVOICE_PARTIALLY_PAID", "INVOICE_PARTIALLY_PAID");
define("INVOICE_LATE", "INVOICE_LATE");
// define("INVOICE_ABANDONED", "INVOICE_ABANDONED");
// les statuts
define("INVOICE_STATUS_DRAFT", FactureFournisseur::STATUS_DRAFT); // 0	création
define("INVOICE_STATUS_VALIDATED", FactureFournisseur::STATUS_VALIDATED); // 1	validée, pas encore payée, pas en retard
define("INVOICE_STATUS_PAID", FactureFournisseur::STATUS_CLOSED); // 2	classée payée, 
//	si payée partiellement, le champ close_code peut avoir 
//	les valeurs suivantes : CLOSECODE_DISCOUNTVAT, CLOSECODE_BADDEBT
// define("INVOICE_STATUS_ABANDONED", FactureFournisseur::STATUS_ABANDONED); // 3	abandonnée sans paiement 
// (voir champ close_code pour les raisons d'abandon, 
// valeurs possibles : CLOSECODE_BADDEBT, CLOSECODE_ABANDONED, CLOSECODE_REPLACED)
define("INVOICE_STATUS_PARTIALLY_PAID", 20); // 20	non classé payée et montant payé < montant facture
define("INVOICE_STATUS_TO_CLASSIFY_PAID", 30); // 30	non classé payée et montant payé >= montant facture
define("INVOICE_STATUS_LATE", 40); // 40	non classée payée et montant payé < montant facture et date limite règlement < aujourd'hui

include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_main_invoices_suppliers.class.php';

if ($action == 'cardDrop') {
	$response		 = array();
	$object			 = new FactureFournisseur($db);
	$id					 = GETPOST('id', 'int');
	$newStatusID = GETPOST('newStatusID');
	if ($id > 0) {
		$ret = $object->fetch($id);
		if ($ret > 0) {

			// object from requete, contient les infos sur les paiements en plus des infos de la facture elle-même
			$objectFromReq = new ReqKbMainInvoicesSuppliers($db);
			$res1					 = $objectFromReq->fetchOneByField("t.rowid", $id);

			if ($res1 > 0) {

				// --- destination Draft

				if ($newStatusID == INVOICE_DRAFT) {
					// il est interdit de revenir à draft
					if ($object->statut == INVOICE_STATUS_DRAFT) {
						// rien à faire
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed");
					} else {
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed_ReturnToDraft");
					}
				}

				// --- destination Validée
				elseif ($newStatusID == INVOICE_VALIDATED) {

					// il est interdit de valider une facture vide 
					if (count($object->lines) == 0) {
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed_EmptyInvoice");
					} elseif ($object->statut == INVOICE_STATUS_VALIDATED) {
						// rien à faire
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed");
					}
					// on doit valider ou réouvrir  ?
					// si on vient de Draft, on valide
					elseif ($object->statut == INVOICE_STATUS_DRAFT) {
						// validée
						$action1 = 'validate';
					}
					// si on vient d'ailleur on réouvre
					// elseif ($object->statut == INVOICE_STATUS_PAID || $object->statut == INVOICE_STATUS_ABANDONED) {
					elseif ($object->statut == FactureFournisseur::STATUS_CLOSED || ($object->statut == FactureFournisseur::STATUS_ABANDONED && $object->close_code != 'replaced')) {
						// réouvrir
						$action1 = 'reopen';
					} else {
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed");
					}
				}

				// --- destination payée
				elseif ($newStatusID == INVOICE_PAID) {
					// il est interdit de venir de Draft
					if ($object->statut == INVOICE_STATUS_DRAFT) {
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed_YouMustValidateFirst");
					} elseif ($object->statut == INVOICE_STATUS_PAID) {
						// rien à faire
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed");
					} elseif ($object->statut == INVOICE_STATUS_VALIDATED && $objectFromReq->total_paye > 0) {
						// classer payée (cloturée)
						$action1 = 'setpaid';
					} else {
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed");
					}
				}

				// --- destination abandonnées 
//				elseif ($newStatusID == INVOICE_ABANDONED) {
//					// il est interdit de venir de Draft
//					if ($object->statut == INVOICE_STATUS_DRAFT) {
//						$response['status']	 = 'KO';
//						$response['message'] = $langs->trans("ActionNotAllowed_YouMustValidateFirst");
//					}
//					// si on vient de abandonned
//					elseif ($object->statut == INVOICE_STATUS_ABANDONED) {
//						// rien à faire
//						$response['status']	 = 'KO';
//						$response['message'] = $langs->trans("ActionNotAllowed");
//					}
//					// on ne peut classer abandonnée que si la facture est : 
//					// validée + non payée 
//					// ou validée + partiellemen payée
//					// ou validée + en retard de payment
//					elseif ($object->statut == INVOICE_STATUS_VALIDATED && ($objectFromReq->total_paye < $objectFromReq->total_ttc)) {
//						// classer abandonnée
//						// $action1 = 'setabandoned';
//						$action1 = 'setpaid';
//					} else {
//						$response['status']	 = 'KO';
//						$response['message'] = $langs->trans("ActionNotAllowed_ToClassifyAbandonnedMustBoNotPaid");
//					}
//				}
				// --- destination partiellement payée
				elseif ($newStatusID == INVOICE_PARTIALLY_PAID) {
					// si on vient de l'état paid (closed), il se peut que la facture soit classée payée 
					// alors qu'elle n'était que partiellement payée et qu'on veuille la réouvrir
					if ($object->statut == INVOICE_STATUS_PAID && $objectFromReq->total_paye < $objectFromReq->total_ttc) {
						// réouvrir
						$action1 = 'reopen';
					} else { // pour les autres cette destination est interdite, c'est un état en lecture seule
						$response['status']	 = 'KO';
						$response['message'] = $langs->trans("ActionNotAllowed");
					}
				}

				// --- destination A classer payée 
				elseif ($newStatusID == INVOICE_TO_CLASSIFY_PAID) {
					// cette destination est interdite, c'est un état en lecture seule
					$response['status']	 = 'KO';
					$response['message'] = $langs->trans("ActionNotAllowed");
				}

				// --- destination en retard
				elseif ($newStatusID == INVOICE_STATUS_LATE) {
					// non autorisé, c'est un état en lecture seule
					$response['status']	 = 'KO';
					$response['message'] = $langs->trans("ActionNotAllowed");

					// --- on ne doit pas arriver ici
				} else {
					$response['status']	 = 'KO';
					$response['message'] = $langs->trans("UnknownDestinationColumn");
				}

				// --------------------------- exécution des actions 
				// 
				// -------- Validate
				// 
				if ($action1 == 'validate') {
					// if (((empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->fournisseur->facture->creer))
					//	    || (! empty($conf->global->MAIN_USE_ADVANCED_PERMS) && ! empty($user->rights->fournisseur->supplier_invoice_advance->validate))))

					$idwarehouse = GETPOST('idwarehouse', 'int');
					$newRef			 = ''; // après validation, la ref est modifié de (PROVXXX) à SIXX-XXXX par exemple

					$object->fetch($id);
					$object->fetch_thirdparty();

					$qualified_for_stock_change = 0;
					if (empty($conf->global->STOCK_SUPPORTS_SERVICES)) {
						$qualified_for_stock_change = $object->hasProductsOrServices(2);
					} else {
						$qualified_for_stock_change = $object->hasProductsOrServices(1);
					}

					// Check for warehouse
					if (!empty($conf->stock->enabled) && !empty($conf->global->STOCK_CALCULATE_ON_SUPPLIER_BILL) && $qualified_for_stock_change) {
						if (!$idwarehouse || $idwarehouse == - 1) {
							$error ++;
							$response['status']	 = 'KO';
							$response['message'] = $langs->trans('ErrorFieldRequired', $langs->transnoentitiesnoconv("Warehouse"));
						}
					}

					if (!$error) {
						$result = $object->validate($user, '', $idwarehouse);
						if ($result >= 0) {
							$ret		 = $object->fetch($id); // Reload to get new records
							$newRef	 = $object->ref;

							// Define output language
							if (empty($conf->global->MAIN_DISABLE_PDF_AUTOUPDATE)) {
								$outputlangs = $langs;
								$newlang		 = '';

								if (DOL_VERSION <= '5.0.7') {
									if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id'))
										$newlang = GETPOST('lang_id', 'alpha');
								}else {
									if ($conf->global->MAIN_MULTILANGS && empty($newlang) && GETPOST('lang_id', 'aZ09'))
										$newlang = GETPOST('lang_id', 'aZ09');
								}

								if ($conf->global->MAIN_MULTILANGS && empty($newlang))
									$newlang = $object->thirdparty->default_lang;

								if (!empty($newlang)) {
									$outputlangs = new Translate("", $conf);
									$outputlangs->setDefaultLang($newlang);
//										if (DOL_VERSION >= '6.0.6') {
//											$outputlangs->load('products');
//										}
								}
								$model	 = $object->modelpdf;
								$result	 = $object->generateDocument($model, $outputlangs, $hidedetails, $hidedesc, $hideref);
								if ($result < 0) {
									$response['status']	 = 'KO';
									$response['message'] = $object->error;
								} else {
									$response['status']	 = 'OK';
									$response['message'] = $langs->trans('InvoiceValidatedSuccessfully');
								}
							} else {
								$response['status']	 = 'OK';
								$response['message'] = $langs->trans('InvoiceValidatedSuccessfully');
							}
						} else {
							if (count($object->errors)) {
								$response['status']	 = 'KO';
								$response['message'] = join(' - ', $object->errors);
							} else {
								$response['status']	 = 'KO';
								$response['message'] = $object->error;
							}
						}
					}
				}

				// 
				// -------- Reopen  
				// 
				if ($action1 == 'reopen') {
					// if ($user->rights->fournisseur->facture->creer) {

					if ($object->statut == FactureFournisseur::STATUS_CLOSED || ($object->statut == FactureFournisseur::STATUS_ABANDONED && $object->close_code != 'replaced')) {
						$result = $object->set_unpaid($user);
						if ($result > 0) {
							$response['status']	 = 'OK';
							$response['message'] = $langs->trans('InvoiceReopenedSuccessfully');
						} else {
							$response['status']	 = 'KO';
							$response['message'] = $object->error;
						}
					}
				}

				// 
				// -------- SetPaid (close)
				// 
				if ($action1 == 'setpaid') {
					// if ($user->rights->fournisseur->facture->creer) {
					// totalement payée
					if ($objectFromReq->total_paye == $objectFromReq->total_ttc) {
						$result = $object->set_paid($user);
						if ($result > 0) {
							$response['status']	 = 'OK';
							$response['message'] = $langs->trans('InvoiceClassifyPaidSuccessfully');
						} else {
							$response['status']	 = 'KO';
							$response['message'] = $object->error;
						}
					}
					// partiellement payée
					elseif ($objectFromReq->total_paye > 0 && $objectFromReq->total_paye < $objectFromReq->total_ttc) {
//							$close_code	 = GETPOST("close_code");
//							$close_note	 = GETPOST("close_note");
//							if ($close_code) {
						$result = $object->set_paid($user); // , $close_code, $close_note);
						if ($result > 0) {
							$response['status']	 = 'OK';
							$response['message'] = $langs->trans('InvoiceClassifyPaidSuccessfully');
						} else {
							$response['status']	 = 'KO';
							$response['message'] = $object->error;
						}
//							} else {
//								$response['status']	 = 'KO';
//								$response['message'] = $langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Reason"));
//							}
					} else {
						
					}
				}

				// 
				// -------- SetAbandonned
				// 
//				if ($action1 == 'setabandoned') {
//					$close_code	 = GETPOST("close_code");
//					$close_note	 = GETPOST("close_note");
//					if ($close_code) {
//						$result = $object->set_canceled($user, $close_code, $close_note);
//						
//						if ($result > 0) {
//							$response['status']	 = 'OK';
//							$response['message'] = $langs->trans('InvoiceClassifyAbandonedSuccessfully');
//						} else {
//							$response['status']	 = 'KO';
//							$response['message'] = (empty($object->error) ? join(' - ', $object->errors) : $object->error);
//						}
//					} else {
//						$response['status']	 = 'KO';
//						$response['message'] = $langs->trans("ErrorFieldRequired", $langs->transnoentitiesnoconv("Reason"));
//					}
//				}
			} elseif ($res1 == 0) { // on n'a pas trouvé la facture à partir de la requete
				dol_syslog('RecordNotFound : Facture : ' . $id, LOG_DEBUG);
				$response['status']	 = 'KO';
				$response['message'] = $langs->trans("RecordNotFound");
			} else { // une erreur s'est produite lors de la recherche de la facture à partir de la requete
				dol_syslog($object->error, LOG_DEBUG);
				$response['status']	 = 'KO';
				$response['message'] = $object->error;
			}
		} elseif ($ret == 0) { // facture non trouvée en utilisant la classe Facture de dolibarr
			dol_syslog('RecordNotFound : Facture : ' . $id, LOG_DEBUG);
			$response['status']	 = 'KO';
			$response['message'] = $langs->trans("RecordNotFound");
		} elseif ($ret < 0) { // une erreur s'est produite lors de la recherche de la facture à partir de la classe Facture de Dolibarr
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
	
	// l'envoi de la réponse se fait plus loin après collecte de données pour pouvoir récupérer le nbre d'éléments par colonne
	// exit(json_encode($response));
}

// **************************************************************************************************************
//
//                                     Kanban - Collecte de données
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

if ($search_societe_logo != '')
	$WHERE .= natural_search("llx_societe.logo", $search_societe_logo, __NATURAL_SEARCH_MODE__);
if ($search_societe_nom_alias != '')
	$WHERE .= natural_search("llx_societe.name_alias", $search_societe_nom_alias, __NATURAL_SEARCH_MODE__);
if ($search_societe_nom != '')
	$WHERE .= natural_search("llx_societe.nom", $search_societe_nom, __NATURAL_SEARCH_MODE__);
if ($search_extraparams != '')
	$WHERE .= natural_search("t.extraparams", $search_extraparams, __NATURAL_SEARCH_MODE__);
if ($search_import_key != '')
	$WHERE .= natural_search("t.import_key", $search_import_key, __NATURAL_SEARCH_MODE__);
if ($search_model_pdf != '')
	$WHERE .= natural_search("t.model_pdf", $search_model_pdf, __NATURAL_SEARCH_MODE__);
if ($search_location_incoterms != '')
	$WHERE .= natural_search("t.location_incoterms", $search_location_incoterms, __NATURAL_SEARCH_MODE__);
if ($search_fk_incoterms != '')
	$WHERE .= natural_search("t.fk_incoterms", $search_fk_incoterms, __NATURAL_SEARCH_MODE__);
if ($search_note_public != '')
	$WHERE .= natural_search("t.note_public", $search_note_public, __NATURAL_SEARCH_MODE__);
if ($search_note_private != '')
	$WHERE .= natural_search("t.note_private", $search_note_private, __NATURAL_SEARCH_MODE__);
if ($search_date_lim_reglement_month > 0) {
	if ($search_date_lim_reglement_year > 0 && empty($search_date_lim_reglement_day))
		$WHERE .= " AND t.date_lim_reglement BETWEEN '" . $db->idate(dol_get_first_day($search_date_lim_reglement_year, $search_date_lim_reglement_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_lim_reglement_year, $search_date_lim_reglement_month, false)) . "'";
	else if ($search_date_lim_reglement_year > 0 && !empty($search_date_lim_reglement_day))
		$WHERE .= " AND t.date_lim_reglement BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_date_lim_reglement_month, $search_date_lim_reglement_day, $search_date_lim_reglement_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_date_lim_reglement_month, $search_date_lim_reglement_day, $search_date_lim_reglement_year)) . "'";
	else
		$WHERE .= " AND date_format(t.date_lim_reglement, '%m') = '" . $search_date_lim_reglement_month . "'";
}
else if ($search_date_lim_reglement_year > 0) {
	$WHERE .= " AND t.date_lim_reglement BETWEEN '" . $db->idate(dol_get_first_day($search_date_lim_reglement_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_date_lim_reglement_year, 12, false)) . "'";
}
if ($search_fk_mode_reglement != '')
	$WHERE .= natural_search("t.fk_mode_reglement", $search_fk_mode_reglement, __NATURAL_SEARCH_MODE__);
if ($search_fk_cond_reglement != '')
	$WHERE .= natural_search("t.fk_cond_reglement", $search_fk_cond_reglement, __NATURAL_SEARCH_MODE__);
if ($search_fk_account != '')
	$WHERE .= natural_search("t.fk_account", $search_fk_account, __NATURAL_SEARCH_MODE__);
if ($search_fk_projet != '')
	$WHERE .= natural_search("t.fk_projet", $search_fk_projet, __NATURAL_SEARCH_MODE__);
if ($search_fk_facture_source != '')
	$WHERE .= natural_search("t.fk_facture_source", $search_fk_facture_source, __NATURAL_SEARCH_MODE__);
if ($search_fk_user_valid != '')
	$WHERE .= natural_search("t.fk_user_valid", $search_fk_user_valid, __NATURAL_SEARCH_MODE__);
if ($search_fk_user_modif != '')
	$WHERE .= natural_search("t.fk_user_modif", $search_fk_user_modif, __NATURAL_SEARCH_MODE__);
if ($search_fk_user_author != '')
	$WHERE .= natural_search("t.fk_user_author", $search_fk_user_author, __NATURAL_SEARCH_MODE__);
if ($search_fk_statut != '')
	$WHERE .= natural_search("t.fk_statut", $search_fk_statut, __NATURAL_SEARCH_MODE__);
if ($search_total_ttc != '')
	$WHERE .= natural_search("t.total_ttc", $search_total_ttc, __NATURAL_SEARCH_MODE__);
if ($search_total_tva != '')
	$WHERE .= natural_search("t.total_tva", $search_total_tva, __NATURAL_SEARCH_MODE__);
if ($search_total_ht != '')
	$WHERE .= natural_search("t.total_ht", $search_total_ht, __NATURAL_SEARCH_MODE__);
if ($search_total != '')
	$WHERE .= natural_search("t.total", $search_total, __NATURAL_SEARCH_MODE__);
if ($search_localtax2 != '')
	$WHERE .= natural_search("t.localtax2", $search_localtax2, __NATURAL_SEARCH_MODE__);
if ($search_localtax1 != '')
	$WHERE .= natural_search("t.localtax1", $search_localtax1, __NATURAL_SEARCH_MODE__);
if ($search_tva != '')
	$WHERE .= natural_search("t.tva", $search_tva, __NATURAL_SEARCH_MODE__);
if ($search_close_note != '')
	$WHERE .= natural_search("t.close_note", $search_close_note, __NATURAL_SEARCH_MODE__);
if ($search_close_code != '')
	$WHERE .= natural_search("t.close_code", $search_close_code, __NATURAL_SEARCH_MODE__);
if ($search_remise != '')
	$WHERE .= natural_search("t.remise", $search_remise, __NATURAL_SEARCH_MODE__);
if ($search_amount != '')
	$WHERE .= natural_search("t.amount", $search_amount, __NATURAL_SEARCH_MODE__);
if ($search_paye != '')
	$WHERE .= natural_search("t.paye", $search_paye, __NATURAL_SEARCH_MODE__);
if ($search_libelle != '')
	$WHERE .= natural_search("t.libelle", $search_libelle, __NATURAL_SEARCH_MODE__);
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
if ($search_datef_month > 0) {
	if ($search_datef_year > 0 && empty($search_datef_day))
		$WHERE .= " AND t.datef BETWEEN '" . $db->idate(dol_get_first_day($search_datef_year, $search_datef_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datef_year, $search_datef_month, false)) . "'";
	else if ($search_datef_year > 0 && !empty($search_datef_day))
		$WHERE .= " AND t.datef BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_datef_month, $search_datef_day, $search_datef_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_datef_month, $search_datef_day, $search_datef_year)) . "'";
	else
		$WHERE .= " AND date_format(t.datef, '%m') = '" . $search_datef_month . "'";
}
else if ($search_datef_year > 0) {
	$WHERE .= " AND t.datef BETWEEN '" . $db->idate(dol_get_first_day($search_datef_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datef_year, 12, false)) . "'";
}
if ($search_datec_month > 0) {
	if ($search_datec_year > 0 && empty($search_datec_day))
		$WHERE .= " AND t.datec BETWEEN '" . $db->idate(dol_get_first_day($search_datec_year, $search_datec_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datec_year, $search_datec_month, false)) . "'";
	else if ($search_datec_year > 0 && !empty($search_datec_day))
		$WHERE .= " AND t.datec BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_datec_month, $search_datec_day, $search_datec_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_datec_month, $search_datec_day, $search_datec_year)) . "'";
	else
		$WHERE .= " AND date_format(t.datec, '%m') = '" . $search_datec_month . "'";
}
else if ($search_datec_year > 0) {
	$WHERE .= " AND t.datec BETWEEN '" . $db->idate(dol_get_first_day($search_datec_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datec_year, 12, false)) . "'";
}
if ($search_fk_soc != '')
	$WHERE .= natural_search("t.fk_soc", $search_fk_soc, __NATURAL_SEARCH_MODE__);
if ($search_type != '')
	$WHERE .= natural_search("t.type", $search_type, __NATURAL_SEARCH_MODE__);
if ($search_ref_ext != '')
	$WHERE .= natural_search("t.ref_ext", $search_ref_ext, __NATURAL_SEARCH_MODE__);
if ($search_entity != '')
	$WHERE .= natural_search("t.entity", $search_entity, __NATURAL_SEARCH_MODE__);
if ($search_ref_supplier != '')
	$WHERE .= natural_search("t.ref_supplier", $search_ref_supplier, __NATURAL_SEARCH_MODE__);
if ($search_ref != '')
	$WHERE .= natural_search("t.ref", $search_ref, __NATURAL_SEARCH_MODE__);
if ($search_id != '')
	$WHERE .= natural_search("t.rowid", $search_id, __NATURAL_SEARCH_MODE__);
if ($search_rowid != '')
	$WHERE .= natural_search("t.rowid", $search_rowid, __NATURAL_SEARCH_MODE__);
if ($search_total_paye != '')
	$WHERE .= natural_search("paiement_facture.total_paye", $search_total_paye, __NATURAL_SEARCH_MODE__);
if ($search_nbre_services != '')
	$WHERE .= natural_search("COUNT(CASE WHEN llx_product.fk_product_type = 1 THEN 1 ELSE NULL END)", $search_nbre_services, __NATURAL_SEARCH_MODE__);
if ($search_nbre_lignes != '')
	$WHERE .= natural_search("COUNT(llx_facture_fourn_det.rowid)", $search_nbre_lignes, __NATURAL_SEARCH_MODE__);
if ($search_nbre_produits != '')
	$WHERE .= natural_search("COUNT(CASE WHEN llx_product.fk_product_type = 0 THEN 1 ELSE NULL END)", $search_nbre_produits, __NATURAL_SEARCH_MODE__);


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

// ----- exécution de la requete principale (doit rester avant les actions part 2)

$dataArray = array();

include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_main_invoices_suppliers.class.php';
$ReqObject				 = new ReqKbMainInvoicesSuppliers($db);
// les "isNew" sont à "false" parce qu'on veut garder les paramétrage de la requete d'origine
$num							 = $ReqObject->fetchAll($limit, $offset, $ORDERBY, $isNewOrderBy			 = false, $WHERE, $isNewWhere				 = false, $HAVING, $isNewHaving			 = false);
$nbtotalofrecords	 = $ReqObject->nbtotalofrecords;

// 
// --------------------------- liste des entrepots (utile si "décrémentation du stock par les factures" activée)
// 
// lorsque l'utilisateur tente de valider un brouillon la liste des entrepots est affichée dans un dialog pour choisir l'entrepot à décrémenter
if (DOL_VERSION < "7.0.0")
	$SQL								 = "SELECT rowid, label AS ref FROM " . LLX_ . "entrepot WHERE statut = 1 ORDER BY ref ";
else
	$SQL								 = "SELECT rowid, ref FROM " . LLX_ . "entrepot WHERE statut = 1 ORDER BY ref ";
$warehouseList			 = '';
$warehouseListEmpty	 = 1; // ne pas utiliser "true" car pose problème niveau js
$res								 = $db->query($SQL);
if ($res) {
	while ($obj = $db->fetch_object($res)) {
		$warehouseList			 .= '<li value="' . $obj->rowid . '">' . ((!empty($obj->ref)) ? $obj->ref : $obj->rowid) . '</li>';
		$warehouseListEmpty	 = 0; // ne pas utiliser "false" car pose problème niveau js
	}
} else {
	dol_syslog($db->lasterror, LOG_ERR);
	setEventMessages("WarehousesListNotObtained", null, 'errors');
}

//  
// --------------------------- les titres des colonnes
// 
$titlesValues			 = INVOICE_DRAFT
		. "," . INVOICE_VALIDATED
		. "," . INVOICE_PAID
		. "," . INVOICE_TO_CLASSIFY_PAID
		. "," . INVOICE_PARTIALLY_PAID
		. "," . INVOICE_LATE;
// . "," . INVOICE_ABANDONED;
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
		if ($columnsArray[$i]['key'] == INVOICE_DRAFT) {
			$columnsArray[$i]['allowDrop'] = false;
		} elseif ($columnsArray[$i]['key'] == INVOICE_LATE) {
			$columnsArray[$i]['allowDrop'] = false;
		} elseif ($columnsArray[$i]['key'] == INVOICE_PARTIALLY_PAID) {
			// $columnsArray[$i]['allowDrop']		 = false;    // il faut le garder actif par ce qu'il se peut qu'on veuille réouvrir une facture partiellement payée qui avait été clôturée auparavant
		} elseif ($columnsArray[$i]['key'] == INVOICE_TO_CLASSIFY_PAID) {
			$columnsArray[$i]['allowDrop'] = false;
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
		$dataArray[$i]['priority']					 = - $obj->datec; // date création timestamp inversé pour le trie descendant des cartes du kanban, voir fields.priority
		$dataArray[$i]['rowid']							 = $obj->rowid;
		$dataArray[$i]['ref']								 = $obj->ref;
		$dataArray[$i]['ref_ext']						 = $obj->ref_ext;
		$dataArray[$i]['ref_supplier']			 = $obj->ref_supplier;
		$dataArray[$i]['type']							 = $obj->type;
		$dataArray[$i]['fk_soc']						 = $obj->fk_soc;
		$dataArray[$i]['datec']							 = $obj->datec;
		$dataArray[$i]['datef']							 = $obj->datef;
		$dataArray[$i]['tms']								 = $obj->tms;
		$dataArray[$i]['paye']							 = $obj->paye;
		$dataArray[$i]['amount']						 = $obj->amount;
		$dataArray[$i]['close_code']				 = $obj->close_code;
		$dataArray[$i]['close_note']				 = $obj->close_note;
		$dataArray[$i]['total']							 = $obj->total;
		$dataArray[$i]['total_ttc']					 = $obj->total_ttc;
		$dataArray[$i]['fk_statut']					 = $obj->fk_statut;
		$dataArray[$i]['date_lim_reglement'] = $obj->date_lim_reglement;
		$dataArray[$i]['note_private']			 = $obj->note_private;
		$dataArray[$i]['note_public']				 = $obj->note_public;
		$dataArray[$i]['societe_nom']				 = $obj->societe_nom;
		$dataArray[$i]['societe_nom_alias']	 = $obj->societe_nom_alias;
		$dataArray[$i]['societe_logo']			 = $obj->societe_logo;
		$dataArray[$i]['total_paye']				 = $obj->total_paye;
		$dataArray[$i]['nbre_lignes']				 = $obj->nbre_lignes;
		$dataArray[$i]['nbre_services']			 = $obj->nbre_services;
		$dataArray[$i]['nbre_produits']			 = $obj->nbre_produits;

		// quelques transformations et additions
		$dataArray[$i]['ref_tiers']						 = $dataArray[$i]['ref'] . ' - ' . $dataArray[$i]['societe_nom'];
		$dataArray[$i]['total_ttc']						 = str_replace(',', '.', price($dataArray[$i]['total_ttc'], 0, '', 0, 0, 2, 'auto'));
		$dataArray[$i]['total_paye']					 = str_replace(',', '.', price($dataArray[$i]['total_paye'], 0, '', 0, 0, 2, 'auto'));
		$dataArray[$i]['total_restant']				 = str_replace(',', '.', price($obj->total_ttc - $obj->total_paye, 0, '', 0, 0, 2, 'auto'));
		$dataArray[$i]['total_ttc_total_paye'] = str_replace(',', '.', $dataArray[$i]['total_ttc'] . ' - ' . $dataArray[$i]['total_paye']);
		$dataArray[$i]['date_lim_reglement']	 = dol_print_date($dataArray[$i]['date_lim_reglement'], 'day', 'tzuser');
		// $dataArray[$i]['date_valid']					 = dol_print_date($dataArray[$i]['date_valid'], 'day', 'tzuser');
		$dataArray[$i]['datef']								 = dol_print_date($dataArray[$i]['datef'], 'day', 'tzuser');

		$dataArray[$i]['kanban_status']							 = ''; // voir ci-dessous			(keyField)
		$dataArray[$i]['qualified_for_stock_change'] = ''; // voir ci-dessous
		// la rubrique image a un traitement supplémentaire pour générer l'url complète de l'image
		if (($fieldImageUrl != 'null' && !empty($fieldImageUrl)) && !empty($obj->{$fieldImageUrl})) {
			$dataArray[$i][$fieldImageUrl] = DOL_URL_ROOT . '/viewimage.php?modulepart=societe&file=' . $obj->fk_soc . '/logos/' . urlencode($obj->{$fieldImageUrl});
		}

		// traitements additionnels 
		$qualified_for_stock_change = 0;
		if (empty($conf->global->STOCK_SUPPORTS_SERVICES)) {
			$qualified_for_stock_change = ($obj->nbre_produits > 0 ? 1 : 0);
		} else {
			$qualified_for_stock_change = ($obj->nbre_lignes > 0 ? 1 : 0);
		}
		if ($obj->type != FactureFournisseur::TYPE_DEPOSIT && !empty($conf->global->STOCK_CALCULATE_ON_SUPPLIER_BILL) && $qualified_for_stock_change)
			$dataArray[$i]['qualified_for_stock_change'] = 1;
		else
			$dataArray[$i]['qualified_for_stock_change'] = 0;

		// les statuts
		if ($dataArray[$i]['fk_statut'] == INVOICE_STATUS_DRAFT) {
			$dataArray[$i]['kanban_status'] = INVOICE_DRAFT;
		} elseif ($dataArray[$i]['fk_statut'] == INVOICE_STATUS_VALIDATED) {
			$dataArray[$i]['kanban_status'] = INVOICE_VALIDATED;
		} elseif ($dataArray[$i]['fk_statut'] == INVOICE_STATUS_PAID) {
			$dataArray[$i]['kanban_status'] = INVOICE_PAID;
		}
//		elseif ($dataArray[$i]['fk_statut'] == INVOICE_STATUS_ABANDONED) {
//			$dataArray[$i]['kanban_status'] = INVOICE_ABANDONED;
//		}

		if ($dataArray[$i]['fk_statut'] == INVOICE_STATUS_VALIDATED && $obj->total_paye >= $obj->total_ttc) {
			$dataArray[$i]['kanban_status'] = INVOICE_TO_CLASSIFY_PAID;
		}

		if ($dataArray[$i]['fk_statut'] == INVOICE_STATUS_VALIDATED && $obj->total_paye > 0 && $obj->total_paye < $obj->total_ttc) {
			$dataArray[$i]['kanban_status'] = INVOICE_PARTIALLY_PAID;
		}

		if ($dataArray[$i]['fk_statut'] == INVOICE_STATUS_VALIDATED && ($obj->date_lim_reglement + (60 * 60 * 24)) < dol_now('tzserver') && $obj->total_paye == 0) {
			$dataArray[$i]['kanban_status'] = INVOICE_LATE;
		}

		$columnsCountArray[$dataArray[$i]['kanban_status']] += 1; // on incrémente le nbre d'éléments dans la colonne
		// calcul du retard pour le paramétrage des couleurs
		// ce calcul n'a de sens que si la facture est en retard de réglement 
		if ($dataArray[$i]['kanban_status'] == INVOICE_LATE) {
			$retard = intval((intval(dol_now('tzserver')) - intval($obj->date_lim_reglement)) / (60 * 60 * 24)); // retard en jours
			if ($retard > 0 && intval($obj->date_lim_reglement) > 0) {
				if ($retard <= 30) {
					$dataArray[$i]['late_status'] = 'INVOICE_LATE_STATUS_1';
				} elseif ($retard <= 60) {
					$dataArray[$i]['late_status'] = 'INVOICE_LATE_STATUS_2';
				} else {
					$dataArray[$i]['late_status'] = 'INVOICE_LATE_STATUS_3';
				}
			} else {
				$dataArray[$i]['late_status'] = 'INVOICE_LATE_STATUS_0';
			}
		}

		// gestion tooltip
		$prefix														 = '<div id="invoice-' . $obj->rowid . '">';
		$suffix														 = '</div>';
		$dataArray[$i]['tooltip_content']	 = '<table><tbody>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainInvoicesSuppliers_Fieldref') . '</b></td><td>: <span class="tooltip-ref-' . $obj->rowid . '">' . $obj->ref . '</span></td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainInvoicesSuppliers_Fieldsociete_nom') . '</b></td><td>: ' . $obj->societe_nom . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainInvoicesSuppliers_Fielddatef') . '</b></td><td>: ' . $dataArray[$i]['datef'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainInvoicesSuppliers_Fielddate_lim_reglement') . '</b></td><td>: ' . $dataArray[$i]['date_lim_reglement'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainInvoicesSuppliers_Fieldtotal_ttc') . '</b></td><td>: ' . $dataArray[$i]['total_ttc'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainInvoicesSuppliers_Fieldtotal_paye') . '</b></td><td>: ' . $dataArray[$i]['total_paye'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainInvoicesSuppliers_Fieldtotal_restant') . '</b></td><td>: ' . price($obj->total_ttc - $obj->total_paye, 0, '', 0, 0, 2, 'auto') . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '</tbody></table>';

		// contenu
		$dataArray[$i]['ref_tiers'] = '<a class="object-link" href="' . DOL_URL_ROOT . '/fourn/facture/card.php?facid=' . $obj->rowid . '" target="_blank">' . $obj->ref . '</a>' . $prefix . $dataArray[$i]['societe_nom'] . $suffix; // encapsulation du contenu dans un div pour permettre l'affichage du tooltip

		$i ++; // prochaine ligne de données
	}

	unset($ReqObject);
} else {
	$error ++;
	dol_print_error($db);
}

$container = 'kanview';

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

// on trie le tableau 
// usort($dataArray, 'natural_sort');
// paramètres de l'url
// ***************************************************************************************************************
// 
//                                Actions  part 2 - Après collecte des données
// 
// ***************************************************************************************************************
// 
// suite de l'action if ($action == 'cardDrop') 
if ($action == 'cardDrop') {
	if (is_array($response) && $response['status'] == 'OK') {
		$response['data']['kanbanHeaderCounts'] = $kanbanHeaderCounts;
		// $response['data']['num'] = $num;
	}
	exit(json_encode($response));
}

// 
// ************************************************************************************************************** 
// 
//                                        VIEW - Envoi du header et Filter
// 
// ***************************************************************************************************************
// 
// ------------------------------------ CSS & JS ---------------------------------------------
// 
$LIB_URL_RELATIVE = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT);

// ---- css
$arrayofcss		 = array();
$arrayofcss[]	 = $LIB_URL_RELATIVE . '/sf/Content/ejthemes/default-theme/ej.web.all.min.css';
$arrayofcss[]	 = $LIB_URL_RELATIVE . '/sf/Content/ejthemes/responsive-css/ej.responsive.css';
$arrayofcss[]	 = str_replace(DOL_URL_ROOT, '', KANVIEW_URL_ROOT) . '/css/kanview.css';
// $arrayofcss[]	 = KANVIEW_URL_ROOT . '/css/' . str_replace('.php', '.css', basename($_SERVER['SCRIPT_NAME']));
// ---- js
$jsEnabled		 = true;
if (!empty($conf->use_javascript_ajax)) {
	$arrayofjs	 = array();
// $arrayofjs[] = $LIB_URL_RELATIVE . '/sf/js/jquery-3.1.1.min.js';
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/jsrender.min.js';

// ----- sf common
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.core.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.data.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.draggable.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.globalize.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.scroller.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.touch.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.unobtrusive.min.js?b=' . $build;
	$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/common/ej.webform.min.js?b=' . $build;

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

// ----- sf traductions (garder les après common et others)
	if (in_array($langs->defaultlang, array('fr_FR', 'en_US', 'ar_SA'))) {
		$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/i18n/ej.culture.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
		$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/l10n/ej.localetexts.' . str_replace('_', '-', $langs->defaultlang) . '.min.js?b=' . $build;
	} else {
		$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/i18n/ej.culture.fr-FR.min.js?b=' . $build;
		$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/l10n/ej.localetexts.fr-FR.min.js?b=' . $build;
	}
/// ----------
} else {
	$jsEnabled = false;
}
/// --------------------------------------- end css & js --------------------------------------------

$help_url = ''; // EN:Module_Kanban_En|FR:Module_Kanban|AR:M&oacute;dulo_Kanban';

llxHeader('', $langs->trans("Kanview_KB_KanbanInvoicesSuppliers"), $help_url, '', 0, 0, $arrayofjs, $arrayofcss, '');

$form = new Form($db);

$head = kanview_kanban_prepare_head($params);

$tabactive = '__KANBAN_TABACTIVE__';

// dol_fiche_head($head, $tabactive, $langs->trans('Kanview_KB_KanbanInvoicesSuppliers'), 0, 'action');
// le selecteur du nbre d'éléments par page généré par print_barre_liste() doit se trouver ds le <form>
// doit rester avant print_barre_liste()
print '<form name="listactionsfilter" class="listactionsfilter" action="' . $_SERVER["PHP_SELF"] . '" method="get">';

//
// __KANBAN_BEFORE_TITLE__
// 
// titre du Kanban
$title = $langs->trans('Kanview_KB_KanbanInvoicesSuppliers');
print_barre_liste($title, intval($page), $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', intval($num) + 1, intval($nbtotalofrecords), 'title_accountancy.png', 0, '', '', intval($limit));

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
// ------------- filtre --req_kb_main_invoices-- datec - période
// 
$value = empty($search_dd_datec) ? - 1 : $search_dd_datec;
echo '<tr id="tr-periode">';
echo '<td class="td-card-label">' . $langs->trans("ReqKbMainInvoicesSuppliers_Fielddatec") . '</td>';
echo '<td>' . $langs->trans("Du") . '</td>';
echo '<td class="td-card-data">';
$form->select_date($value, 'search_dd_datec_', '', '', '', "dd", 1, 1); // datepicker
$value = empty($search_df_datec) ? - 1 : $search_df_datec;
echo '&nbsp;&nbsp;&nbsp;&nbsp;<span>' . $langs->trans("Au") . '</span>&nbsp;';
$form->select_date($value, 'search_df_datec_', '', '', '', "df", 1, 1); // datepicker
echo '</td>';
echo '</tr>';

// 
// ------------- filtre --req_kb_main_invoices-- fk_soc
// 
echo '<tr id="tr-search_fk_soc" class="tr-external-filter"><td class="td-card-label">' . $langs->trans('ReqKbMainInvoicesSuppliers_Fieldfk_soc') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="fk_soc_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';

// Attention : ne pas utiliser "WHERE t.status = 1" ds la requete ci-dessous 
// parce que des clients désactivés peuvent avoir des commandes passées avant leur désactivation
$SQL						 = "SELECT 
	t.rowid AS rowid,	
	t.nom AS societe_nom,	
	t.name_alias AS name_alias,	
	t.ref_int AS ref_int
FROM 
	" . LLX_ . "societe AS t 
WHERE 
	t.fournisseur = 1 
ORDER BY
	t.nom";
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
	print '<td style="width: 80%; ">';	// garder les 80% sinon défaut d'affichage
	print '<fieldset class="filters-fields" style="height: 100%; padding-left: 5px;">';
	print '<legend><span class="" style="text-align: left;">' . $langs->trans('LEGEND') . '</span></legend>';
	print '<table>';
	// -- legend 1
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR . ' transparent;">'
// le découpage suivant n'a pas marché sur FF
//			. 'border-color-top: transparent; '
//			. 'border-color-right: transparent; '
//			. 'border-color-bottom: #007bff; '
//			. 'border-color-left: transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR') . '</td>';
	print '</tr>';
	// -- legend 2
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR') . '</td>';
	print '</tr>';
	// -- legend 3
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR') . '</td>';
	print '</tr>';
	// -- legend 4
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . '#179BD7' . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_INVOICES_NOT_LATE_COLOR') . '</td>';
	print '</tr>';
	// -- legend 5 (TAG)
	print '<tr>';
	print '<td>';
	print '<div class="legend-name">TAG</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans(strtoupper($conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG)) . '</td>';
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
// -- si pas de données on le dit -- // on laisse le kanban le dire
// if ($num === 0) {
//	echo '<div id="AucunElementTrouve">';
//	echo '<p>' . $langs->trans('AucunElementTrouve') . '</p>';
//	echo '</div>';
// }
// ************************************************************************************************************** 
// 
// ------------------------------------------------------ VIEW - Kanban Output
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
	// ----------------------------------- javascripts spécifiques à cette page
// quelques variables javascripts fournis par php
	echo '<script type="text/javascript">
		var columnIDs							= ' . trim($columnIDs) . ';
		var kanbanData						= ' . trim($kanbanData) . ';
		var columns								=  ' . trim($columns) . ';
		var invoices_tag					= "' . trim($conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG) . '";
		var invoice_late_status_0 = "' . trim($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE0_COLOR) . '";
		var invoice_late_status_1 = "' . trim($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR) . '";
		var invoice_late_status_2 = "' . trim($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR) . '";
		var invoice_late_status_3 = "' . trim($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR) . '";
			
		var msgPrintKanbanView				= "' . trim($langs->transnoentities('msgPrintKanbanView')) . '";

 		var dateSeparator						= "' . trim(substr($langs->trans('FormatDateShort'), 2, 1)) . '";
		var DOL_URL_ROOT						= "' . trim(DOL_URL_ROOT) . '";
		var DOL_VERSION							= "' . trim(DOL_VERSION) . '";
 		var KANVIEW_URL_ROOT				= "' . trim(KANVIEW_URL_ROOT) . '";
 		var locale									= "' . trim($langs->defaultlang) . '";
		var sfLocale								= "' . trim(str_replace('_', '-', $langs->defaultlang)) . '";
		var fieldImageUrl							= "' . trim($fieldImageUrl) . '";

		var STOCK_CALCULATE_ON_BILL		= "' . (!empty($conf->global->STOCK_CALCULATE_ON_BILL) ? $conf->global->STOCK_CALCULATE_ON_BILL : 0) . '";
		var stock_enabled							= "' . (!empty($conf->stock->enabled) ? $conf->stock->enabled : 0) . '";

		var UpdateNotAllowed					= "' . trim($langs->transnoentities('UpdateNotAllowed')) . '"; 
		var ActionNotAllowed					= "' . trim($langs->transnoentities('ActionNotAllowed')) . '"; 
		var ActionNotAllowed_EmptyInvoice		= "' . trim($langs->transnoentities('ActionNotAllowed_EmptyInvoice')) . '"; 
		var msgWarehouseToIncrementDialogTitle						= "' . trim($langs->transnoentities('msgWarehouseToIncrementDialogTitle')) . '"; 
		var warehouseListEmpty				= ' . $warehouseListEmpty . ';
		var ValidationNotAllowed_NoWarehouse = "' . trim($langs->transnoentities('ValidationNotAllowed_NoWarehouse')) . '";
		var ActionNotAllowed_ReturnToDraft				= "' . trim($langs->transnoentities('ActionNotAllowed_ReturnToDraft')) . '";
		var ActionNotAllowed_YouMustValidateFirst = "' . trim($langs->transnoentities('ActionNotAllowed_YouMustValidateFirst')) . '";
		var ActionNotAllowed_ToClassifyAbandonnedMustBoNotPaid = "' . trim($langs->transnoentities('ActionNotAllowed_ToClassifyAbandonnedMustBoNotPaid')) . '";
			
		var parent1	= "' . trim(module) . '";
		var parent2	= "' . trim($container) . '";

		var msgOK									= "' . trim($langs->transnoentities('OK')) . '"; 
		var msgCancel							= "' . trim($langs->transnoentities('Cancel')) . '"; 
		
		var msgInvoice_SetPaid_DialogTitle		= "' . trim($langs->transnoentities('Invoice_SetPaid_DialogTitle')) . '";
		var msgInvoice_SetAbandoned_DialogTitle	= "' . trim($langs->transnoentities('Invoice_SetAbandoned_DialogTitle')) . '";
			
		var mytitle = "' . trim(mytitle) . '";

		var enableNativeTotalCount				= ' . trim(empty($enableNativeTotalCount) ? 'false' : 'true') . ';
		var tooltipsActive								= false;		

		var token = "' . trim($_SESSION['newtoken']) . '";

 	</script>';
	?>

	<!-- =============================================== dialog warehouse choice =============================== -->

	<div id="warehouse_choice_dialog" style="display: none;">
		<!-- list -->
		<ul id="warehouse_choice_list">
	<?php echo $warehouseList; ?>     
		</ul>
		<!-- footer -->
		<div id="warehouse_choice_dialog_footer" style="margin-top: 5px; border-top: 1px grey solid;">
			<div class="footerspan" style="float:right; padding: 5px;">
				<button id="btnOK_Warehouse"><?php echo $langs->transnoentities('OK'); ?></button>
				<button id="btnCancel_Warehouse"><?php echo $langs->transnoentities('Cancel'); ?></button>
			</div>
		</div>
	</div>

	<!-- ================================================= dialog set paid ======================================== -->

	<div id="set_paid_dialog" style="display: none;">
		<!-- list -->
		<span class="titlefield"><?php echo $langs->transnoentities('Invoice_SetPaid_Reason'); ?></span><br>
		<ul id="set_paid_reason_choice_list">
			<li value="discount_vat"><?php echo $langs->transnoentities('CLOSECODE_DISCOUNTVAT'); ?></li>     
			<li value="badcustomer"><?php echo $langs->transnoentities('CLOSECODE_BADDEBT'); ?></li>
		</ul>
		<!-- comment -->
		<span class="titlefield"><?php echo $langs->transnoentities('Invoice_SetPaid_Comment'); ?></span><br>
		<input type="text" id="txtReason_SetPaid" class="e-textbox" value="" maxlength="128" style="width: 355px;">
		<!-- footer -->
		<div id="set_paid_dialog_footer" style="margin-top: 5px; border-top: 1px grey solid;">
			<div class="footerspan" style="float:right; padding: 5px;">
				<button id="btnOK_SetPaid"><?php echo $langs->transnoentities('OK'); ?></button>
				<button id="btnCancel_SetPaid"><?php echo $langs->transnoentities('Cancel'); ?></button>
			</div>
		</div>
	</div>

	<!-- ==================================================== dialog set abandoned ================================= -->

	<div id="set_abandoned_dialog" style="display: none;">
		<!-- list -->
		<span class="titlefield"><?php echo $langs->transnoentities('Invoice_SetAbandoned_Reason'); ?></span><br>
		<ul id="set_abandoned_reason_choice_list">
			<li value="badcustomer"><?php echo $langs->transnoentities('CLOSECODE_BADDEBT'); ?></li>     
			<li value="abandon"><?php echo $langs->transnoentities('CLOSECODE_ABANDONED'); ?></li>
			<!-- <li value="replaced"><?php echo $langs->transnoentities('CLOSECODE_REPLACED'); ?></li> -->
		</ul>
		<!-- comment -->
		<span class="titlefield"><?php echo $langs->transnoentities('Invoice_SetAbandoned_Comment'); ?></span><br>
		<input type="text" id="txtReason_SetAbandoned" class="e-textbox" value="" maxlength="128" style="width: 355px;">
		<!-- footer -->
		<div id="set_abandoned_dialog_footer" style="margin-top: 5px; border-top: 1px grey solid;">
			<div class="footerspan" style="float:right; padding: 5px;">
				<button id="btnOK_SetAbandoned"><?php echo $langs->transnoentities('OK'); ?></button>
				<button id="btnCancel_SetAbandoned"><?php echo $langs->transnoentities('Cancel'); ?></button>
			</div>
		</div>
	</div>

	<?php
}

// __KANBAN_AFTER_KANBAN__
// 
//
// --------------------------------------- END Output

dol_fiche_end(); // fermeture du cadre
//// inclusion des fichiers js
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
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_invoices_suppliers_kanban');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_invoices_suppliers_kanban', 'remove');

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

