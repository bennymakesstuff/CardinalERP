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
if (!hasPermissionForKanbanView('tasks')) {
	accessforbidden();
	exit();
}


require_once DOL_DOCUMENT_ROOT . '/core/lib/date.lib.php';

$build = '1820447827';

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
$dd_year		 = str_pad(GETPOST('dd_year', 'int'), 4, '0', STR_PAD_LEFT);
$dd_month		 = str_pad(GETPOST('dd_month', 'int'), 2, '0', STR_PAD_LEFT);
$dd_day			 = str_pad(GETPOST('dd_day', 'int'), 2, '0', STR_PAD_LEFT);
$dd_hour		 = str_pad(GETPOST('dd_hour', 'int'), 2, '0', STR_PAD_LEFT);
$dd_min			 = str_pad(GETPOST('dd_min', 'int'), 2, '0', STR_PAD_LEFT);
$dd_sec			 = str_pad(GETPOST('dd_sec', 'int'), 2, '0', STR_PAD_LEFT);

// 1er affichage, par défaut : la Date début est le mois flottant précédent
if ((empty($dd_year) || $dd_year == '0000') && (empty($dd_month) || $dd_month == '00') && (empty($dd_day) || $dd_day == '00')) {
	$dd_year				 = date('Y');
	$dd_month				 = date('m');
	$dd_day					 = date('d');
	$previous_month	 = dol_get_prev_month($dd_month, $dd_year);
	$dd_month				 = str_pad($previous_month['month'], 2, '0', STR_PAD_LEFT);
	$dd_year				 = str_pad($previous_month['year'], 4, '0', STR_PAD_LEFT);
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
	$df_year		 = date('Y');
	$df_month		 = date('m');
	$df_day			 = date('d');
	$next_month	 = dol_get_next_month($df_month, $df_year);
	$df_month		 = str_pad($next_month['month'], 2, '0', STR_PAD_LEFT);
	$df_year		 = str_pad($next_month['year'], 2, '0', STR_PAD_LEFT);
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
$search_rowid				 = GETPOST('search_rowid', 'int');
$search_ref					 = GETPOST('search_ref', 'alpha');
$search_fk_projet		 = GETPOST('search_fk_projet', 'int');
$search_fk_soc			 = GETPOST('search_fk_soc', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_dateo_day		 = GETPOST('search_dateo_day', 'int');
$search_dateo_month	 = GETPOST('search_dateo_month', 'int');
$search_dateo_year	 = GETPOST('search_dateo_year', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_datee_day		 = GETPOST('search_datee_day', 'int');
$search_datee_month	 = GETPOST('search_datee_month', 'int');
$search_datee_year	 = GETPOST('search_datee_year', 'int');
$search_label				 = GETPOST('search_label', 'alpha');
$search_description	 = GETPOST('search_description', 'alpha');

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

$container = 'kanview';

$search_duration_effective	 = GETPOST('search_duration_effective', 'alpha');
$search_planned_workload		 = GETPOST('search_planned_workload', 'alpha');
$search_progress						 = GETPOST('search_progress', 'int');
$search_priority						 = GETPOST('search_priority', 'int');
$search_fk_statut						 = GETPOST('search_fk_statut', 'int');
$search_rang								 = GETPOST('search_rang', 'int');
$search_progress_level			 = GETPOST('search_progress_level', 'int');
$search_projet_ref					 = GETPOST('search_projet_ref', 'alpha');
$search_projet_title				 = GETPOST('search_projet_title', 'alpha');
$search_total_task_duration	 = GETPOST('search_total_task_duration', 'alpha');
$search_task_period					 = GETPOST('search_task_period', 'alpha');
$search_id									 = GETPOST('search_id', 'alpha');
$search_entity							 = GETPOST('search_entity', 'int');
$search_fk_task_parent			 = GETPOST('search_fk_task_parent', 'int');
// proprités Date ou DateTime, filtre par Day/Mois/Année
$search_datev_day						 = GETPOST('search_datev_day', 'int');
$search_datev_month					 = GETPOST('search_datev_month', 'int');
$search_datev_year					 = GETPOST('search_datev_year', 'int');
$search_note_private				 = GETPOST('search_note_private', 'alpha');
$search_note_public					 = GETPOST('search_note_public', 'alpha');
$search_contact							 = GETPOST('search_contact', 'alpha');

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
$langs->load("other");

// Initialize technical object to manage hooks of thirdparties. Note that conf->hooks_modules contains array array
$hookmanager->initHooks(array(
		'my_prefix_kanban'));


// ***************************************************************************************************************
// 
//                                           Actions part 1 - Avant collecte de données
// 
// ***************************************************************************************************************
// ---------------------------------------- action après Drag&Drop d'une tuile ==> mise à jour du "Status" de l'objet

$postData	 = GETPOST('postData', '', 2);
$postData	 = json_decode($postData);
if (!empty($postData->action))
	$action		 = $postData->action;

if ($action == 'kanbanTasks_cardDragStop' || $action == 'dlgTaskEdit_btnOK_click') {
	$response = array();
//	if ($user->rights->projet->creer) {
		require_once DOL_DOCUMENT_ROOT . '/projet/class/task.class.php';
		$object			 = new Task($db);
		$id					 = intval($postData->data->rowid); // GETPOST('id', 'int');
		$newProgress = intval($postData->data->progress); // GETPOST('newStatusID', 'string');
		$err				 = 0;

		if ($id > 0) {
			$ret = $object->fetch($id);
			if ($ret > 0) {
				$object->progress	 = $newProgress;
				$res							 = $object->update($user);
				if ($res > 0) {
					// setEventMessages($langs->trans("RecordUpdatedSuccessfully"), null);
					$response['status']	 = 'OK';
					$response['message'] = '';
					$response['data']		 = array();
					// task_status 
					if ($object->dateo < dol_now() && $object->datee > dol_now() && $newProgress == 0) {
						$response['data']['task_status'] = 'LATE1';
					} elseif ($object->datee < dol_now() && $newProgress < 100) {
						$response['data']['task_status'] = 'LATE2';
					} else {
						$response['data']['task_status'] = 'OK';
					}
					// progress_level
					if ($newProgress <= 0)
						$response['data']['progress_level']	 = 'TASK_NOT_STARTED';
					elseif ($newProgress < 30)
						$response['data']['progress_level']	 = 'TASK_LEVEL_1';
					elseif ($newProgress < 60)
						$response['data']['progress_level']	 = 'TASK_LEVEL_2';
					elseif ($newProgress < 90)
						$response['data']['progress_level']	 = 'TASK_LEVEL_3';
					elseif ($newProgress < 100)
						$response['data']['progress_level']	 = 'TASK_LEVEL_4';
					elseif ($newProgress >= 100)
						$response['data']['progress_level']	 = 'TASK_DONE';
				} else {
					// setEventMessages($object->error, null, 'errors');
					$response['status']	 = 'KO';
					$response['message'] = empty($object->error) ? join("\n", $object->errors) : $object->error;
					$err++;
				}
			} elseif ($ret == 0) {
				dol_syslog('RecordNotFound : project : ' . $id, LOG_DEBUG);
				// setEventMessages($langs->trans("RecordNotFound"), null, 'errors');
				$response['status']	 = 'KO';
				$response['message'] = $langs->trans("RecordNotFound");
				$err++;
			} elseif ($ret < 0) {
				dol_syslog($object->error, LOG_DEBUG);
				// setEventMessages($object->error, null, 'errors');
				$response['status']	 = 'KO';
				$response['message'] = empty($object->error) ? join("\n", $object->errors) : $object->error;
				$err++;
			}
		} else {
			// setEventMessages($langs->trans('IncorrectParameter'), null, 'errors');
			$response['status']	 = 'KO';
			$response['message'] = $langs->trans('IncorrectParameter');
			$err++;
		}

		$response['token'] = $_SESSION['newtoken'];
		
		// voir suite après la collecte et formatage des données
		// parce qu'on a besoin de connaitre le nbre d'éléments de chaque colonne avant de quitter
		
		// exit(json_encode($response));
//	} else {
//		$response['status']	 = 'KO';
//		$response['message'] = $langs->trans("NotEnoughRights");
//		exit(json_encode($response));
//	}
}

// ***************************************************************************************************************
// 
//                                           Collecte de données
// 
// ***************************************************************************************************************
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
if ($page < 0) {
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
	$search_rowid				 = '';
	$search_ref					 = '';
	$search_fk_projet		 = '';
	$search_fk_soc			 = '';
	$search_dateo_day		 = '';
	$search_dateo_month	 = '';
	$search_dateo_year	 = '';
	$search_datee_day		 = '';
	$search_datee_month	 = '';
	$search_datee_year	 = '';
	$search_label				 = '';
	$search_description	 = '';

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

	$search_duration_effective	 = '';
	$search_planned_workload		 = '';
	$search_progress						 = '';
	$search_priority						 = '';
	$search_fk_statut						 = '';
	$search_rang								 = '';
	$search_progress_level			 = '';
	$search_projet_ref					 = '';
	$search_projet_title				 = '';
	$search_total_task_duration	 = '';
	$search_task_period					 = '';
	$search_id									 = '';
	$search_entity							 = '';
	$search_fk_task_parent			 = '';
	$search_datev_day						 = '';
	$search_datev_month					 = '';
	$search_datev_year					 = '';
	$search_note_private				 = '';
	$search_note_public					 = '';
	$search_contact							 = '';


	// -----------------------------------------------------

	$search_array_options = array();
}

//
// --------------------------- Requête principale (doit rester avant les actions part 2)
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
if ($search_ref != '')
	$WHERE .= natural_search("t.ref", $search_ref);
if ($search_fk_projet != '')
	$WHERE .= natural_search("t.fk_projet", $search_fk_projet, 1);
if ($search_fk_soc != '')
	$WHERE .= natural_search("" . LLX_ . "projet.fk_soc", $search_fk_soc, 1);
if ($search_dateo_month > 0) {
	if ($search_dateo_year > 0 && empty($search_dateo_day))
		$WHERE .= " AND t.dateo BETWEEN '" . $db->idate(dol_get_first_day($search_dateo_year, $search_dateo_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_dateo_year, $search_dateo_month, false)) . "'";
	else if ($search_dateo_year > 0 && !empty($search_dateo_day))
		$WHERE .= " AND t.dateo BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_dateo_month, $search_dateo_day, $search_dateo_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_dateo_month, $search_dateo_day, $search_dateo_year)) . "'";
	else
		$WHERE .= " AND date_format(t.dateo, '%m') = '" . $search_dateo_month . "'";
}
else if ($search_dateo_year > 0) {
	$WHERE .= " AND t.dateo BETWEEN '" . $db->idate(dol_get_first_day($search_dateo_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_dateo_year, 12, false)) . "'";
}
if ($search_datee_month > 0) {
	if ($search_datee_year > 0 && empty($search_datee_day))
		$WHERE .= " AND t.datee BETWEEN '" . $db->idate(dol_get_first_day($search_datee_year, $search_datee_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datee_year, $search_datee_month, false)) . "'";
	else if ($search_datee_year > 0 && !empty($search_datee_day))
		$WHERE .= " AND t.datee BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_datee_month, $search_datee_day, $search_datee_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_datee_month, $search_datee_day, $search_datee_year)) . "'";
	else
		$WHERE .= " AND date_format(t.datee, '%m') = '" . $search_datee_month . "'";
}
else if ($search_datee_year > 0) {
	$WHERE .= " AND t.datee BETWEEN '" . $db->idate(dol_get_first_day($search_datee_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datee_year, 12, false)) . "'";
}
if ($search_label != '')
	$WHERE .= natural_search("t.label", $search_label);
if ($search_description != '')
	$WHERE .= natural_search("t.description", $search_description);

if ($search_dd_datec_mysql != '' && $search_df_datec_mysql != '') {
	// si date début et date fin sont dans le mauvais ordre, on les inverse
	if ($search_dd_datec_mysql > $search_df_datec_mysql) {
		$tmp									 = $search_dd_datec_mysql;
		$search_dd_datec_mysql = $search_df_datec_mysql;
		$search_df_datec_mysql = $tmp;
	}

	$WHERE .= " AND (t.datec BETWEEN '" . $search_dd_datec_mysql . "' AND '" . $search_df_datec_mysql . "')";
}

if ($search_duration_effective != '')
	$WHERE .= natural_search("t.duration_effective", $search_duration_effective, 1);
if ($search_planned_workload != '')
	$WHERE .= natural_search("t.planned_workload", $search_planned_workload);
if ($search_progress != '')
	$WHERE .= natural_search("t.progress", $search_progress, 1);
if ($search_priority != '')
	$WHERE .= natural_search("t.priority", $search_priority, 1);
if ($search_fk_statut != '')
	$WHERE .= natural_search("t.fk_statut", $search_fk_statut);
if ($search_rang != '')
	$WHERE .= natural_search("t.rang", $search_rang, 1);
if ($search_projet_ref != '')
	$WHERE .= natural_search("" . LLX_ . "projet.ref", $search_projet_ref);
if ($search_projet_title != '')
	$WHERE .= natural_search("" . LLX_ . "projet.title", $search_projet_title);
if ($search_total_task_duration != '')
	$WHERE .= natural_search("SUM(" . LLX_ . "projet_task_time.task_duration)", $search_total_task_duration, 1);
if ($search_task_period != '')
	$WHERE .= natural_search("concat(t.dateo, '-', t.datee)", $search_task_period);
if ($search_id != '')
	$WHERE .= natural_search("t.rowid", $search_id, 1);
if ($search_entity != '')
	$WHERE .= natural_search("t.entity", $search_entity);
if ($search_fk_task_parent != '')
	$WHERE .= natural_search("t.fk_task_parent", $search_fk_task_parent, 1);
if ($search_datev_month > 0) {
	if ($search_datev_year > 0 && empty($search_datev_day))
		$WHERE .= " AND t.datev BETWEEN '" . $db->idate(dol_get_first_day($search_datev_year, $search_datev_month, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datev_year, $search_datev_month, false)) . "'";
	else if ($search_datev_year > 0 && !empty($search_datev_day))
		$WHERE .= " AND t.datev BETWEEN '" . $db->idate(dol_mktime(0, 0, 0, $search_datev_month, $search_datev_day, $search_datev_year)) . "' AND '" . $db->idate(dol_mktime(23, 59, 59, $search_datev_month, $search_datev_day, $search_datev_year)) . "'";
	else
		$WHERE .= " AND date_format(t.datev, '%m') = '" . $search_datev_month . "'";
}
else if ($search_datev_year > 0) {
	$WHERE .= " AND t.datev BETWEEN '" . $db->idate(dol_get_first_day($search_datev_year, 1, false)) . "' AND '" . $db->idate(dol_get_last_day($search_datev_year, 12, false)) . "'";
}
if ($search_note_private != '')
	$WHERE .= natural_search("t.note_private", $search_note_private);
if ($search_note_public != '')
	$WHERE .= natural_search("t.note_public", $search_note_public);
if ($search_contact != '')
	$WHERE .= " AND contactsIdsWithSource LIKE '%" . $search_contact . "%'";

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

//if ($db->type = 'mysqli') {
	include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_main_tasks_mysql.class.php';
	$ReqObject = new ReqKbMainTasksMysql($db);
//} else {
//	include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_main_tasks_postgres.class.php';
//	$ReqObject = new ReqKbMainTasksPostgres($db);
//}

// les "isNew" sont à "false" parce qu'on veut garder les paramétrage de la requete d'origine
$num							 = $ReqObject->fetchAll($limit, $offset, $ORDERBY, $isNewOrderBy			 = false, $WHERE, $isNewWhere				 = false, $HAVING, $isNewHaving			 = false);
$nbtotalofrecords	 = $ReqObject->nbtotalofrecords;

// **************************************************************************************************************
//
//                                  Kanban - Formatage des données
//
// ***************************************************************************************************************
// 
// --------------------------- Requête fournissant les titres des colonnes
// 
$titlesValues			 = "TASK_NOT_STARTED,TASK_LEVEL_1,TASK_LEVEL_2,TASK_LEVEL_3,TASK_LEVEL_4,TASK_DONE";
$columnsArray			 = array();
$columnsIDsArray	 = array(); // tableau associatif : 'titre' => 'son id', ça nous permet de retrouver (côté js) les ids des Statuts en fonction de leur code 
$columnsCountArray = array(); // tableau associatif : 'titre' => 'nbre d'éléments' dans la colonne (incrémenté dans la boucle de parcours des données principales) 

$columnsTitles = explode(",", $titlesValues);
$countColumns	 = count($columnsTitles);
if ($countColumns > 0) {
	for ($i = 0; $i < $countColumns; $i++) {
		$columnsArray[$i]['headerText']				 = $langs->trans($columnsTitles[$i]);
		$columnsArray[$i]['key']							 = $columnsTitles[$i];
		$columnsArray[$i]['allowDrag']				 = true;
		$columnsArray[$i]['allowDrop']				 = true;
		$columnsIDsArray[$columnsTitles[$i]]	 = $columnsTitles[$i];
		$columnsCountArray[$columnsTitles[$i]] = 0; // sera incrémenté dans la boucle de parcours des données principales ci-dessous
		// traitements additionnels 
		if ($columnsArray[$i]['key'] == 'TASK_DONE') {
			// $columnsArray[$i]['allowDrag'] = false;
		} else {
			// $columnsArray[$i]['allowDrop'] = false;
		}
	}
} else {
	dol_syslog('ColumnsTitles not supplied', LOG_ERR);
	setEventMessages("ColumnsTitlesNotSupplied", null, 'errors');
}
/// ---
// 
// --------------------------- données principales
// 

$tasksIDsCommaSeparated = '';

if (!empty($conf->global->KANVIEW_SHOW_PICTO))
	$fieldImageUrl = 'contact_picto'; // TODO : quelle rubrique pour le logo ?
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

		// var_dump($obj->contactsIdsWithSource);
		// collecte des ids des taches (utilisé plus loin lors de la collecte des contacts des taches)
		if (!empty($tasksIDsCommaSeparated))
			$tasksIDsCommaSeparated	 .= ',';
		$tasksIDsCommaSeparated	 .= $obj->rowid;

		// $dataArray[$i]['nom_field'] = $obj->nom_field; 
		$dataArray[$i]['priority']							 = - $obj->datec; // date création timestamp inversé pour le trie descendant des cartes du kanban, voir fields.priority
		$dataArray[$i]['rowid']									 = $obj->rowid;
		$dataArray[$i]['ref']										 = $obj->ref;
		$dataArray[$i]['fk_projet']							 = $obj->fk_projet;
		$dataArray[$i]['dateo']									 = $obj->dateo;
		$dataArray[$i]['datee']									 = $obj->datee;
		$dataArray[$i]['label']									 = $obj->label;
		$dataArray[$i]['description']						 = $obj->description;
		$dataArray[$i]['datec']									 = $obj->datec;
		$dataArray[$i]['duration_effective']		 = $obj->duration_effective;
		$dataArray[$i]['planned_workload']			 = $obj->planned_workload;
		$dataArray[$i]['progress']							 = ((!empty($obj->progress)) ? $obj->progress : 0) . '%';
		$dataArray[$i]['priority']							 = $obj->priority;
		$dataArray[$i]['fk_statut']							 = $obj->fk_statut;
		$dataArray[$i]['rang']									 = $obj->rang;
		$dataArray[$i]['progress_level']				 = $obj->progress_level; // keyField 
		$dataArray[$i]['projet_ref']						 = $obj->projet_ref;
		$dataArray[$i]['projet_title']					 = $obj->projet_title;
		$dataArray[$i]['total_task_duration']		 = $obj->total_task_duration;
		$dataArray[$i]['fk_soc']								 = $obj->fk_soc;
		$dataArray[$i]['task_period']						 = $obj->task_period;
		$dataArray[$i]['contactsIdsWithSource']	 = $obj->contactsIdsWithSource;
		$dataArray[$i]['contact_picto']					 = ''; // utilisé par JS, ne pas modifier

		$columnsCountArray[$dataArray[$i]['progress_level']] += 1; // on incrémente le nbre d'éléments dans la colonne
// la rubrique image a un traitement supplémentaire pour générer l'url complète de l'image
		// pour les taches, la gestion des images se fait coté JS car une tache peut avoir plusieurs contacts, ce qui impose une gestion particulière
//		if ((!empty($fieldImageUrl)) && !empty($obj->{$fieldImageUrl})) {
//			$dataArray[$i][$fieldImageUrl] = DOL_URL_ROOT . '/viewimage.php?modulepart=&file=' . '' . urlencode($obj->{$fieldImageUrl});
//		}
		// traitements additionnels 
		$dataArray[$i]['dateo']															 = dol_print_date($dataArray[$i]['dateo'], 'day', 'tzuser');
		$dataArray[$i]['datee']															 = dol_print_date($dataArray[$i]['datee'], 'day', 'tzuser');
		$dataArray[$i]['task_period']												 = $dataArray[$i]['dateo'] . '-' . $dataArray[$i]['datee'];

		// durée effectuée en h:m
		$hour															 = (int) ($dataArray[$i]['planned_workload'] / 3600);
		$min															 = (int) (($dataArray[$i]['planned_workload'] % 3600) / 60);
		$dataArray[$i]['planned_workload'] = $hour . ':' . $min;

		// durée prévue en h:m
		$hour																	 = (int) ($dataArray[$i]['total_task_duration'] / 3600);
		$min																	 = (int) (($dataArray[$i]['total_task_duration'] % 3600) / 60);
		$dataArray[$i]['total_task_duration']	 = $hour . ':' . $min;

		if ($obj->dateo < dol_now() && $obj->datee > dol_now() && $dataArray[$i]['progress'] <= 0) {
			$dataArray[$i]['task_status'] = 'LATE1';
		} elseif ($obj->datee < dol_now() && $dataArray[$i]['progress'] < 100) {
			$dataArray[$i]['task_status'] = 'LATE2';
		} else {
			$dataArray[$i]['task_status'] = 'OK';
		}

		// gestion tooltip
		$prefix														 = '<div id="task-' . $obj->rowid . '">'; // encapsulation du contenu dans un div pour permmettre l'affichage du tooltip
		$suffix														 = '</div>';
		$dataArray[$i]['tooltip_content']	 = '<table><tbody>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fieldref') . '</b></td><td>: <span class="tooltip-ref-' . $obj->rowid . '">' . $obj->ref . '</span>' . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fieldlabel') . '</b></td><td>: ' . $obj->label . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fieldprojet_title') . '</b></td><td>: ' . $obj->projet_title . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fielddateo') . '</b></td><td>: ' . dol_print_date($obj->dateo, 'day', 'tzuser') . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fielddatee') . '</b></td><td>: ' . dol_print_date($obj->datee, 'day', 'tzuser') . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fieldplanned_workload') . '</b></td><td>: ' . $dataArray[$i]['planned_workload'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fieldtotal_task_duration') . '</b></td><td>: ' . $dataArray[$i]['total_task_duration'] . '</td></tr>';
		$dataArray[$i]['tooltip_content']	 .= '<tr class="tooltip-tr"><td class="tooltip-label"><b>' . $langs->trans('ReqKbMainTasks_Fieldprogress') . '</b></td><td>: <span class="tooltip-progress-' . $obj->rowid . '">' . $obj->progress . '%</span>' . '</td></tr>'; // ne pas utiliser l'id comme identifiant du <span> parce que ejToolTip crée une copie du contenu pour afficher le tooltip et donc on pourrait se retrouver avec plusieurs éléments avec le même id
		$dataArray[$i]['tooltip_content']	 .= '</tbody></table>';
		
		// contenu
		$dataArray[$i]['ref_libelle']			 = '<a class="object-link" href="' . DOL_URL_ROOT . '/projet/tasks/task.php?id=' . $obj->rowid . '&withproject=1" target="_blank">' . $obj->ref . '</a>' . $prefix . $obj->label . $suffix;

		$i ++; // prochaine ligne de données
	}

	unset($ReqObject);
} else {
	$error ++;
	dol_print_error($db);
}

// ---- nbre d'éléments dans une colonnes. on utilise :
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
/// --- 
// var_dump($tasksIDsCommaSeparated);
// 
// ---------------------------- collecte des contacts des taches (doit rester après les données principales)
//
include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_internal_tasks_contacts.class.php';
$ReqTasksInternalContacts	 = new ReqKbInternalTasksContacts($db);
$ORDERBY									 = 'type_contact_code DESC, firstname, lastname';
$WHERE										 = 't.element_id IN (' . $tasksIDsCommaSeparated . ') '; // on ne prend que les contacts des tâches collectées ci-dessus
$nbTasksInternalContacts	 = $ReqTasksInternalContacts->fetchAll(0, 0, $ORDERBY, true, $WHERE, false);
// $nbtotalofrecords_tic		 = $ReqTasksInternalContacts->nbtotalofrecords;

include_once KANVIEW_DOCUMENT_ROOT . '/class/req_kb_external_tasks_contacts.class.php';
$ReqTasksExternalContacts	 = new ReqKbExternalTasksContacts($db);
$ORDERBY									 = 'type_contact_code DESC, firstname, lastname';
$WHERE										 = 't.element_id IN (' . $tasksIDsCommaSeparated . ') '; // on ne prend que les contacts des tâches collectées ci-dessus
$nbTasksExternalContacts	 = $ReqTasksExternalContacts->fetchAll(0, 0, $ORDERBY, true, $WHERE, false);
// $nbtotalofrecords_tec		 = $ReqTasksExternalContacts->nbtotalofrecords;
//
// on réorganise ces contacts de façon à avoir un tableau associatif de format : 
// $tasksResources['task_rowid'] = array(les ressources de la tache)
// utilisé pour afficher les contacts de la tache dans sa fuche Kanban
$tasksResources						 = array();
$resourcesDistinct				 = array(); // utilisé pour remplir la combo du filtre contact
$resourcesDistinctIdsTmp	 = array();
foreach ($ReqTasksInternalContacts->lines as $resource) {
	$resource->taskResourceId						 = $resource->fk_socpeople;
	$resource->taskResourceIdWithSource	 = $resource->fk_socpeople . '-I'; // composition de l'ID de façon à avoir un id unique même en faisant l'UNION des contacts internes (llx_user) et externes (llx_socpeople) (ne pas modifier ce format car utilisé dans JS)
	$resource->taskResourceName					 = trim($resource->firstname . ' ' . $resource->lastname) . ' (I)';
	$resource->taskResourceType					 = $resource->type_contact_code;
	$resource->taskResourceSource				 = $resource->type_contact_source;
	$resource->taskResourceRowid				 = $resource->rowid;
	$resource->taskResourcePhoto				 = $resource->photo;

	$tasksResources[$resource->element_id][] = $resource;

	if (!in_array($resource->taskResourceIdWithSource, $resourcesDistinctIdsTmp)) {
		$resourcesDistinctIdsTmp[] = $resource->taskResourceIdWithSource;
		$resourcesDistinct[]			 = array(
				'id'	 => $resource->taskResourceIdWithSource,
				'name' => $resource->taskResourceName
		);
	}
}
foreach ($ReqTasksExternalContacts->lines as $resource) {
	$resource->taskResourceId						 = $resource->fk_socpeople;
	$resource->taskResourceIdWithSource	 = $resource->fk_socpeople . '-E'; // composition de l'ID de façon à avoir un id unique même en faisant l'UNION des contacts internes (llx_user) et externes (llx_socpeople) ((ne pas modifier ce format car utilisé dans JS))
	$resource->taskResourceName					 = trim($resource->firstname . ' ' . $resource->lastname) . ' (E)';
	$resource->taskResourceType					 = $resource->type_contact_code;
	$resource->taskResourceSource				 = $resource->type_contact_source;
	$resource->taskResourceRowid				 = $resource->rowid;
	$resource->taskResourcePhoto				 = $resource->photo;

	$tasksResources[$resource->element_id][] = $resource;

	if (!in_array($resource->taskResourceIdWithSource, $resourcesDistinctIdsTmp)) {
		$resourcesDistinctIdsTmp[] = $resource->taskResourceIdWithSource;
		$resourcesDistinct[]			 = array(
				'id'	 => $resource->taskResourceIdWithSource,
				'name' => $resource->taskResourceName
		);
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
if ($search_fk_projet != '')
	$params	 .= '&amp;search_fk_projet=' . urlencode($search_fk_projet);
if ($search_fk_soc != '')
	$params	 .= '&amp;search_fk_soc=' . urlencode($search_fk_soc);
if ($search_dateo != '')
	$params	 .= '&amp;search_dateo=' . urlencode($search_dateo);
if ($search_datee != '')
	$params	 .= '&amp;search_datee=' . urlencode($search_datee);
if ($search_label != '')
	$params	 .= '&amp;search_label=' . urlencode($search_label);
if ($search_description != '')
	$params	 .= '&amp;search_description=' . urlencode($search_description);
if ($search_datec != '')
	$params	 .= '&amp;search_datec=' . urlencode($search_datec);
if ($search_duration_effective != '')
	$params	 .= '&amp;search_duration_effective=' . urlencode($search_duration_effective);
if ($search_planned_workload != '')
	$params	 .= '&amp;search_planned_workload=' . urlencode($search_planned_workload);
if ($search_progress != '')
	$params	 .= '&amp;search_progress=' . urlencode($search_progress);
if ($search_priority != '')
	$params	 .= '&amp;search_priority=' . urlencode($search_priority);
if ($search_fk_statut != '')
	$params	 .= '&amp;search_fk_statut=' . urlencode($search_fk_statut);
if ($search_rang != '')
	$params	 .= '&amp;search_rang=' . urlencode($search_rang);
if ($search_progress_level != '')
	$params	 .= '&amp;search_progress_level=' . urlencode($search_progress_level);
if ($search_projet_ref != '')
	$params	 .= '&amp;search_projet_ref=' . urlencode($search_projet_ref);
if ($search_projet_title != '')
	$params	 .= '&amp;search_projet_title=' . urlencode($search_projet_title);
if ($search_total_task_duration != '')
	$params	 .= '&amp;search_total_task_duration=' . urlencode($search_total_task_duration);
if ($search_task_period != '')
	$params	 .= '&amp;search_task_period=' . urlencode($search_task_period);
if ($search_id != '')
	$params	 .= '&amp;search_id=' . urlencode($search_id);
if ($search_entity != '')
	$params	 .= '&amp;search_entity=' . urlencode($search_entity);
if ($search_fk_task_parent != '')
	$params	 .= '&amp;search_fk_task_parent=' . urlencode($search_fk_task_parent);
if ($search_datev != '')
	$params	 .= '&amp;search_datev=' . urlencode($search_datev);
if ($search_note_private != '')
	$params	 .= '&amp;search_note_private=' . urlencode($search_note_private);
if ($search_note_public != '')
	$params	 .= '&amp;search_note_public=' . urlencode($search_note_public);
if ($search_contact != '')
	$params	 .= '&amp;search_contact=' . urlencode($search_contact);


// ***************************************************************************************************************
// 
//                                           Actions part 2 - Après collecte de données
// 
// ***************************************************************************************************************
// 
// suite de l'action if ($action == 'kanbanTasks_cardDragStop' || $action == 'dlgTaskEdit_btnOK_click') 
if ($action == 'kanbanTasks_cardDragStop' || $action == 'dlgTaskEdit_btnOK_click') {
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
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.combobox.min.js?b=' . $build;
$arrayofjs[] = str_replace(DOL_URL_ROOT, '', LIB_URL_ROOT) . '/sf/Scripts/web/ej.slider.min.js?b=' . $build;
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
llxHeader('', $langs->trans("Kanview_KB_Tasks"), $help_url, '', 0, 0, $arrayofjs, $arrayofcss, '');

$form = new Form($db);

$head = kanview_kanban_prepare_head($params);

$tabactive = '__KANBAN_TABACTIVE__';

// dol_fiche_head($head, $tabactive, $langs->trans('Kanview_KB_Tasks'), 0, 'action');
/// ------------------------------------------- fin zone du filtre
//
// __KANBAN_BEFORE_TITLE__
// 
// le selecteur du nbre d'éléments par page généré par print_barre_liste() doit se trouver ds le <form>
// cette ligne doit donc rester avant l'appel à print_barre_liste()
print '<form name="listactionsfilter" class="listactionsfilter" action="' . $_SERVER["PHP_SELF"] . '" method="get">';

// titre du Kanban
$title = $langs->trans('Kanview_KB_Tasks');
print_barre_liste($title, intval($page), $_SERVER["PHP_SELF"], $params, $sortfield, $sortorder, '', intval($num) + 1, intval($nbtotalofrecords), 'title_project', 0, '', '', intval($limit));

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
// ------------- filtre --req_kb_main_tasks-- datec - période
// 
$value = empty($search_dd_datec) ? - 1 : $search_dd_datec;
echo '<tr id="tr-periode">';
echo '<td class="td-card-label">' . $langs->trans("ReqKbMainTasks_Fielddatec") . '</td>';
echo '<td>' . $langs->trans("Du") . '</td>';
echo '<td class="td-card-data">';
$form->select_date($value, 'search_dd_datec_', '', '', '', "dd", 1, 1); // datepicker
$value = empty($search_df_datec) ? - 1 : $search_df_datec;
echo '&nbsp;&nbsp;&nbsp;&nbsp;<span>' . $langs->trans("Au") . '</span>&nbsp;';
$form->select_date($value, 'search_df_datec_', '', '', '', "df", 1, 1); // datepicker
echo '</td>';
echo '</tr>';
/// --- 
//
// ------------- filtre --req_kb_main_tasks-- label
// 
echo '<tr id="tr-search_label" class="tr-external-filter">';
echo '<td class="td-card-label">' . $langs->trans('ReqKbMainTasks_Fieldlabel') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="label_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';
echo '<input  id="label_input_filtre" align="" type="text" class="flat" name="search_label" ' .
 'value="' . $search_label . '" size="10">';
echo '</div>';
echo '</td>';
echo '</tr>';
/// ---
//
// ------------- filtre --req_kb_main_tasks-- fk_projet
// 
echo '<tr id="tr-search_fk_projet" class="tr-external-filter"><td class="td-card-label">' . $langs->trans('ReqKbMainTasks_Fieldfk_projet') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="fk_projet_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';

$SQL						 = "SELECT 
	t.rowid AS rowid,	
	t.rowid AS id,
	t.ref AS ref,	
	t.title AS title, 
	CONCAT(t.ref, ' - ', t.title) AS projet
FROM 
	" . LLX_ . "projet AS t
WHERE 
	t.fk_statut != 2
ORDER BY 
	dateo DESC, projet
";
$sql_fk_projet	 = $db->query($SQL);
$options				 = array();
$optionSelected	 = '';
$defaultValue		 = '';
$blanckOption		 = 1;
if ($sql_fk_projet) {
	while ($obj_fk_projet = $db->fetch_object($sql_fk_projet)) {
		if ($obj_fk_projet->rowid == $search_fk_projet) {
			$optionSelected = $obj_fk_projet->rowid;
		}
		$options[$obj_fk_projet->rowid] = $obj_fk_projet->projet;
	}
	$db->free($sql_fk_projet);
} else {
	dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . $db->lasterror(), LOG_ERR);
}
echo '<select  id="fk_projet_input_filtre" class="flat" name="search_fk_projet" title="">';
if (empty($optionSelected) && !$blanckOption) {
	$optionSelected = $defaultValue;
}
if ($blanckOption) {
	echo '<option value="" ' . (empty($search_fk_projet) ? 'selected' : '') . '></option>';
	echo '<option value="" ' . (empty($search_fk_projet) ? 'selected' : '') . '></option>'; // pour une raison obscure, en mode ajax, il faut 2 options vides pour afficher une option vide
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $value . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
echo ajax_combobox('fk_projet_input_filtre');
/// ---- 
//
// ------------- filtre --req_kb_main_tasks-- fk_soc
// 
echo '<tr id="tr-search_fk_soc" class="tr-external-filter"><td class="td-card-label">' . $langs->trans('ReqKbMainTasks_Fieldfk_soc') . '</td>';
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
	echo '<option value="" ' . (empty($search_fk_soc) ? 'selected' : '') . '></option>'; // pour une raison obscure, en mode ajax, il faut 2 options vides pour afficher une option vide
}
foreach ($options as $key => $value) {
	echo '<option value="' . $key . '" ' . ($key == $optionSelected ? 'selected' : '') . '>' . $value . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
echo ajax_combobox('fk_soc_input_filtre');
/// -----
//
// ------------- filtre -- contact
// 
echo '<tr id="tr-search_contact" class="tr-external-filter"><td class="td-card-label">' . $langs->trans('ReqKbMainTasks_FieldContact') . '</td>';
echo '<td>' . '' . '</td>';
echo '<td id="contact_td_filtre" class="liste_filtre" align="" valign="">';
echo '<div class="div-external-filter">';
echo '<select  id="contact_input_filtre" class="flat" name="search_contact" title="">';
echo '<option value="" ' . (empty($search_contact) ? 'selected' : '') . '></option>';
$count = count($resourcesDistinct);
for ($i = 0; $i < $count; $i++) {
	echo '<option value="' . $resourcesDistinct[$i]['id'] . '" ' . ($resourcesDistinct[$i]['id'] == $search_contact ? 'selected' : '') . '>' . $resourcesDistinct[$i]['name'] . '</option>';
}
echo '</select>';
echo '</div>';
echo '</td>';
echo '</tr>';
/// -----

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
	print '<td style="width: 80%; ">';		// garder les 80% sinon défaut d'affichage
	print '<fieldset class="filters-fields" style="height: 100%; padding-left: 5px;">';
	print '<legend><span class="" style="text-align: left;">' . $langs->trans('LEGEND') . '</span></legend>';
	print '<table>';
	// -- legend 1
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_TASKS_OK_COLOR . ' transparent;">'
// le découpage suivant n'a pas marché sur FF
//			. 'border-color-top: transparent; '
//			. 'border-color-right: transparent; '
//			. 'border-color-bottom: #007bff; '
//			. 'border-color-left: transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_TASKS_OK_COLOR') . '</td>';
	print '</tr>';
	// -- legend 2
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_TASKS_LATE1_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_TASKS_LATE1_COLOR') . '</td>';
	print '</tr>';
	// -- legend 3
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . $conf->global->KANVIEW_TASKS_LATE2_COLOR . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_TASKS_LATE2_COLOR') . '</td>';
	print '</tr>';
	// -- legend 4
	print '<tr>';
	print '<td>';
	print '<div class="legend-color" '
			. 'style="border-color: transparent transparent ' . '#179BD7' . ' transparent;">'
			. '</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans('KANVIEW_STATUS_UNKNOWN_COLOR') . '</td>';
	print '</tr>';
	// -- legend 5 (TAG)
	print '<tr>';
	print '<td>';
	print '<div class="legend-name">TAG</div>';
	print '</td>';
	print '<td class="legend-label">' . $langs->trans(strtoupper($conf->global->KANVIEW_TASKS_TAG)) . '</td>';
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
//                                           VIEW - Kanban Output
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
?>
<div id="dlgTaskEdit" style="display: none;"> 
	<div style="width: 100%; height: 80px; padding-top: 40px; padding-left: 10px;">
		<div id="dlgTaskEdit_sliderProgressChoice"></div>
		<div style="padding-top: 30px; font-weight: bold; text-align: center;">
			<span id="dlgTaskEdit_sliderValue"></span> %
		</div>
	</div>
	<!-- footer -->
	<div id="dlgTaskEditFooter" style="margin-top: 5px; border-top: 1px grey solid;">
		<div class="footerspan" style="float:right; padding: 5px;">
			<button id="dlgTaskEdit_btnOK"><?php echo trim($langs->transnoentities('OK')) ?></button>
			<button id="dlgTaskEdit_btnCancel"><?php echo trim($langs->transnoentities('Cancel')) ?></button>
		</div>
	</div>
</div>
<?php
// ----------------------------------- javascripts spécifiques à cette page
// quelques variables javascripts fournis par php
echo '<script type="text/javascript">
 		var dateSeparator						= "' . trim(substr($langs->trans('FormatDateShort'), 2, 1)) . '";
		var DOL_URL_ROOT						= "' . trim(DOL_URL_ROOT) . '";	
		var DOL_VERSION							= "' . trim(DOL_VERSION) . '";
 		var KANVIEW_URL_ROOT				= "' . trim(KANVIEW_URL_ROOT) . '";
		var fieldImageUrl							= "' . trim($fieldImageUrl) . '";
 		var mytitle = "' . trim(mytitle) . '";
		var UpdateNotAllowed_ProjectClosed = "' . trim($langs->transnoentities('UpdateNotAllowed_ProjectClosed')) . '"; 
			
		var msgOK											= "' . trim($langs->transnoentities('OK')) . '"; 
		var msgCancel									= "' . trim($langs->transnoentities('Cancel')) . '"; 
		var msgDlgTaskEditTitle				= "' . trim($langs->transnoentities('msgDlgTaskEditTitle')) . '"; 
		var msgPrintKanbanView				= "' . trim($langs->transnoentities('msgPrintKanbanView')) . '"; 

		var enableNativeTotalCount		= ' . trim(empty($enableNativeTotalCount) ? 'false' : 'true') . ';
		var tooltipsActive						= false;		// mémorise le fait que les tooltps sont activés ou non

		var locale									= "' . trim($langs->defaultlang) . '";
		var sfLocale								= "' . trim(str_replace('_', '-', $langs->defaultlang)) . '";

		var parent1	= "' . trim(module) . '";
		var parent2	= "' . trim($container) . '";

		var tasksResources	= ' . trim(json_encode($tasksResources)) . ';
		var columnIDs				= ' . trim($columnIDs) . ';
		var kanbanData			= ' . trim($kanbanData) . ';
		var columns					= ' . trim($columns) . ';
		var tasks_tag				= "' . trim($conf->global->KANVIEW_TASKS_TAG) . '";
		var colorMapping = {
				"' . trim($conf->global->KANVIEW_TASKS_OK_COLOR) . '": "OK",
				"' . trim($conf->global->KANVIEW_TASKS_LATE1_COLOR) . '": "LATE1",
				"' . trim($conf->global->KANVIEW_TASKS_LATE2_COLOR) . '": "LATE2",
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
	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_tasks_kanban');

	complete_head_from_modules($conf, $langs, $object, $head, $h, 'kanview_tasks_kanban', 'remove');

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

