<?php

/*
 * Copyright (C) 2018 ProgSI (contact@progsi.ma)
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
 * \file config
 * \ingroup config
 * \brief config
 */
// __MENU_GROUPS_DEFINES__
include_once dirname(__DIR__) . '/main.inc.php';

// Protection (if external user for example)
if (!($conf->kanview->enabled && $user->admin)) {
	accessforbidden();
	exit();
}

require_once DOL_DOCUMENT_ROOT . '/core/lib/admin.lib.php';

$build = '1325293317';

$langs->load("kanview@kanview");
$langs->load("admin");
$langs->load('other');

// locale et rtl pour syncfusion controls
$rtl		 = "false";
$locale	 = str_replace('_', '-', $langs->defaultlang);
if (strpos($locale, 'ar-') !== false)
	$rtl		 = "true";
/// 

$nomenu			 = GETPOST('nomenu', 'int');
$action			 = GETPOST('action', 'alpha'); // possible actions : 'setprop1' | 'updateoptions'
$value			 = GETPOST('value', 'alpha');
$module_nom	 = 'kanview'; // utilisé par les actions des modèles pdf et la table " . LLX_ . "document_model

$hasNumberingGenerator = false;
$hasDocGenerator			 = false;

if (!empty($nomenu)) {
	$conf->dol_hide_topmenu	 = 1;
	$conf->dol_hide_leftmenu = 1;
}

/* * ************************************************************************************************
 * 
 * ------------------------------------------ Actions
 * 
 * ************************************************************************************************ */

// création

// ------------------------- action to update

if ($action == 'updateoptions') {
	
// 
// ------------- update of KANVIEW_HOME_PAGE
// 
if (GETPOST('submit_KANVIEW_HOME_PAGE')) {
$newvalue = GETPOST('KANVIEW_HOME_PAGE');
$res = dolibarr_set_const($db, "KANVIEW_HOME_PAGE", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_SHOW_PICTO
// 
if (GETPOST('submit_KANVIEW_SHOW_PICTO')) {
$newvalue = GETPOST('KANVIEW_SHOW_PICTO');
$res = dolibarr_set_const($db, "KANVIEW_SHOW_PICTO", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROJETS_TAG
// 
if (GETPOST('submit_KANVIEW_PROJETS_TAG')) {
$newvalue = GETPOST('KANVIEW_PROJETS_TAG');
$res = dolibarr_set_const($db, "KANVIEW_PROJETS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROJETS_DRAFT_COLOR
// 
if (GETPOST('submit_KANVIEW_PROJETS_DRAFT_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROJETS_DRAFT_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROJETS_DRAFT_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROJETS_OPEN_COLOR
// 
if (GETPOST('submit_KANVIEW_PROJETS_OPEN_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROJETS_OPEN_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROJETS_OPEN_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROJETS_CLOSED_COLOR
// 
if (GETPOST('submit_KANVIEW_PROJETS_CLOSED_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROJETS_CLOSED_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROJETS_CLOSED_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_TASKS_TAG
// 
if (GETPOST('submit_KANVIEW_TASKS_TAG')) {
$newvalue = GETPOST('KANVIEW_TASKS_TAG');
$res = dolibarr_set_const($db, "KANVIEW_TASKS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_TASKS_OK_COLOR
// 
if (GETPOST('submit_KANVIEW_TASKS_OK_COLOR')) {
$newvalue = GETPOST('KANVIEW_TASKS_OK_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_TASKS_OK_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_TASKS_LATE1_COLOR
// 
if (GETPOST('submit_KANVIEW_TASKS_LATE1_COLOR')) {
$newvalue = GETPOST('KANVIEW_TASKS_LATE1_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_TASKS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_TASKS_LATE2_COLOR
// 
if (GETPOST('submit_KANVIEW_TASKS_LATE2_COLOR')) {
$newvalue = GETPOST('KANVIEW_TASKS_LATE2_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_TASKS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROPALS_TAG
// 
if (GETPOST('submit_KANVIEW_PROPALS_TAG')) {
$newvalue = GETPOST('KANVIEW_PROPALS_TAG');
$res = dolibarr_set_const($db, "KANVIEW_PROPALS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROPALS_LATE1_COLOR
// 
if (GETPOST('submit_KANVIEW_PROPALS_LATE1_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROPALS_LATE1_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROPALS_LATE2_COLOR
// 
if (GETPOST('submit_KANVIEW_PROPALS_LATE2_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROPALS_LATE2_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROPALS_LATE3_COLOR
// 
if (GETPOST('submit_KANVIEW_PROPALS_LATE3_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROPALS_LATE3_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROPALS_LATE4_COLOR
// 
if (GETPOST('submit_KANVIEW_PROPALS_LATE4_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROPALS_LATE4_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROPALS_LATE4_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_TAG
// 
if (GETPOST('submit_KANVIEW_INVOICES_TAG')) {
$newvalue = GETPOST('KANVIEW_INVOICES_TAG');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_LATE1_COLOR
// 
if (GETPOST('submit_KANVIEW_INVOICES_LATE1_COLOR')) {
$newvalue = GETPOST('KANVIEW_INVOICES_LATE1_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_LATE2_COLOR
// 
if (GETPOST('submit_KANVIEW_INVOICES_LATE2_COLOR')) {
$newvalue = GETPOST('KANVIEW_INVOICES_LATE2_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_LATE3_COLOR
// 
if (GETPOST('submit_KANVIEW_INVOICES_LATE3_COLOR')) {
$newvalue = GETPOST('KANVIEW_INVOICES_LATE3_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_ORDERS_TAG
// 
if (GETPOST('submit_KANVIEW_ORDERS_TAG')) {
$newvalue = GETPOST('KANVIEW_ORDERS_TAG');
$res = dolibarr_set_const($db, "KANVIEW_ORDERS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_ORDERS_LATE1_COLOR
// 
if (GETPOST('submit_KANVIEW_ORDERS_LATE1_COLOR')) {
$newvalue = GETPOST('KANVIEW_ORDERS_LATE1_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_ORDERS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_ORDERS_LATE2_COLOR
// 
if (GETPOST('submit_KANVIEW_ORDERS_LATE2_COLOR')) {
$newvalue = GETPOST('KANVIEW_ORDERS_LATE2_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_ORDERS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_ORDERS_LATE3_COLOR
// 
if (GETPOST('submit_KANVIEW_ORDERS_LATE3_COLOR')) {
$newvalue = GETPOST('KANVIEW_ORDERS_LATE3_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_ORDERS_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROSPECTS_TAG
// 
if (GETPOST('submit_KANVIEW_PROSPECTS_TAG')) {
$newvalue = GETPOST('KANVIEW_PROSPECTS_TAG');
$res = dolibarr_set_const($db, "KANVIEW_PROSPECTS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROSPECTS_PL_HIGH_COLOR
// 
if (GETPOST('submit_KANVIEW_PROSPECTS_PL_HIGH_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROSPECTS_PL_HIGH_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_HIGH_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROSPECTS_PL_LOW_COLOR
// 
if (GETPOST('submit_KANVIEW_PROSPECTS_PL_LOW_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROSPECTS_PL_LOW_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_LOW_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROSPECTS_PL_MEDIUM_COLOR
// 
if (GETPOST('submit_KANVIEW_PROSPECTS_PL_MEDIUM_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_MEDIUM_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_PROSPECTS_PL_NONE_COLOR
// 
if (GETPOST('submit_KANVIEW_PROSPECTS_PL_NONE_COLOR')) {
$newvalue = GETPOST('KANVIEW_PROSPECTS_PL_NONE_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_PROSPECTS_PL_NONE_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_TAG
// 
if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_TAG')) {
$newvalue = GETPOST('KANVIEW_INVOICES_SUPPLIERS_TAG');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_TAG", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR
// 
if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR')) {
$newvalue = GETPOST('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR
// 
if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR')) {
$newvalue = GETPOST('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


// 
// ------------- update of KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR
// 
if (GETPOST('submit_KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR')) {
$newvalue = GETPOST('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR');
$res = dolibarr_set_const($db, "KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR", $newvalue, 'chaine', 0, '', $conf->entity);
if (! $res > 0) 
$error++;
if (! $error){
    setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
    }else{
    setEventMessages($langs->trans("Error"), null, 'errors');
}
}


}
// 
// ------------------------------------------ actions du modèle de numérotation
// 
// --------------------- action to update mask of generic numbering model
elseif ($action == 'updateMask') {
	$maskconstorder	 = GETPOST('maskconstorder', 'alpha');
	$maskorder			 = GETPOST('maskorder', 'alpha');

	if ($maskconstorder)
		$res = dolibarr_set_const($db, $maskconstorder, $maskorder, 'chaine', 0, '', $conf->entity);

	if (!$res > 0)
		$error ++;

	if (!$error) {
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}
// -------------------- action to activate a numbering model
elseif ($action == 'setmod') {
	// TODO Check if numbering module chosen can be activated
	// by calling method canBeActivated

	dolibarr_set_const($db, "_ADDON_NUMBER", $value, 'chaine', 0, $langs->trans('NumberingModuleDesc'), $conf->entity);
}/// --- fin modeles de numerotation
//
// ------------------------------------------- actions du modèle de génération de documents (PDF/ODT)
// 
// --------------------- action Aperçu (specimen) du modèle du document
elseif ($action == 'specimen') {
	$modele = GETPOST('module', 'alpha');

	$src	 = KANVIEW_DOCUMENT_ROOT . '/core/modules/kanview/doc/' . $modele . "_specimen.pdf";
	$dest	 = DOL_DATA_ROOT . '/kanview/' . $modele . "_specimen.pdf";
	$msg	 = '';

	clearstatcache();

	if (!file_exists($dest)) {
		if (file_exists($src)) {
			if (!copy($src, $dest))
				$msg = 'SpecimenCopyFail';
		} else {
			$msg = 'SpecimenSourceNotFound';
		}
	}

	if (empty($msg)) {
		header("Location: " . DOL_URL_ROOT . "/document.php?modulepart=kanview&file=" . $modele . "_specimen.pdf");
		exit();
	} else {
		setEventMessages($msg, null, 'errors');
	}

	// 	include_once KANVIEW_DOCUMENT_ROOT . '/class/__my_object_class_name__.class.php';
	// 	$object = new __MyObjectClass__($db);
	// 	$object->initAsSpecimen();
	// 	// Search template files
	// 	$file = '';
	// 	$classname = '';
	// 	$filefound = 0;
	// 	$dirmodels = array_merge(array(
	// 		'/'), (array) $conf->modules_parts['models']);
	// 	foreach ($dirmodels as $reldir) {
	// 		$file = dol_buildpath($reldir . "core/modules//doc/pdf_" . $modele . ".modules.php", 0);
	// 		if (file_exists($file)) {
	// 			$filefound = 1;
	// 			$classname = "pdf_" . $modele;
	// 			break;
	// 		}
	// 	}
	// 	if ($filefound) {
	// 		require_once $file;
	// 		$module = new $classname($db);
	// 		if ($module->write_file($object, $langs) > 0) {
	// 			header("Location: " . DOL_URL_ROOT . "/document.php?modulepart=kanview&file=SPECIMEN.pdf");
	// 			return;
	// 		} else {
	// 			setEventMessages($module->error, null, 'errors');
	// 			dol_syslog($module->error, LOG_ERR);
	// 		}
	// 	} else {
	// 		setEventMessages($langs->trans("ErrorModuleNotFound"), null, 'errors');
	// 		dol_syslog($langs->trans("ErrorModuleNotFound"), LOG_ERR);
	// 	}
}
// ----------------------- action to Activate a pdf model
elseif ($action == 'set') {
	$ret = addDocumentModel($value, $module_nom, $label, $scandir);
}
// ---------------------- action to disable a pdf model 
elseif ($action == 'del') {
	$ret = delDocumentModel($value, $module_nom);
	if ($ret > 0) {
		if ($conf->global->_ADDON_PDF == "$value")
			dolibarr_del_const($db, '_ADDON_PDF', $conf->entity);
	}
}
// --------------------- action to Set default pdf model

elseif ($action == 'setdoc') {
	if (dolibarr_set_const($db, "_ADDON_PDF", $value, 'chaine', 0, '', $conf->entity)) {
		// The constant that was read before the new set
		// We therefore requires a variable to have a coherent view
		$conf->global->_ADDON_PDF = $value;
	}

	// On active le modele
	$ret = delDocumentModel($value, $module_nom);
	if ($ret > 0) {
		$ret = addDocumentModel($value, $module_nom, $label, $scandir);
	}
}
/// --- fin modèles d'impression
// ----------------------- action to Define constants for submodules that contains parameters (forms with param1, param2, ... and value1, value2, ...)
elseif ($action == 'setModuleOptions') {
	$post_size = count($_POST);

	$db->begin();

	for ($i = 0; $i < $post_size; $i ++) {
		if (array_key_exists('param' . $i, $_POST)) {
			$param = GETPOST("param" . $i, 'alpha');
			$value = GETPOST("value" . $i, 'alpha');
			if ($param)
				$res	 = dolibarr_set_const($db, $param, $value, 'chaine', 0, '', $conf->entity);
			if (!$res > 0)
				$error ++;
		}
	}
	if (!$error) {
		$db->commit();
		setEventMessages($langs->trans("SetupSaved"), null, 'mesgs');
	} else {
		$db->rollback();
		setEventMessages($langs->trans("Error"), null, 'errors');
	}
}

$container = 'kanview';

// __OTHER_CONFIG_ACTIONS__

/* * *************************************************************************************************
 * 
 * ---------------------------------------- View
 * 
 * ************************************************************************************************* */

/*
 * $var = "some text";
 * $text = <<<EOT
 * Place your text between the EOT. It's
 * the delimiter that ends the text
 * of your multiline string.
 * $var
 * EOT;
 */

clearstatcache();

$dirmodels = array_merge(array(
		'/'), (array) $conf->modules_parts['models']);
$form			 = new Form($db);

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
$jsEnabled = true;
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

$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.button.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.menu.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.slider.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.splitbutton.min.js?b=' . $build;
$arrayofjs[] = $LIB_URL_RELATIVE . '/sf/Scripts/web/ej.colorpicker.min.js?b=' . $build;
	
// ----- sf traductions (garder les après common et others)
	if (in_array($lang->defaultlang, array('fr_FR', 'en_US', 'ar_SA'))) {
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

llxHeader('', $langs->trans("Kanview_SetupPage"), '', '', 0, 0, $arrayofjs, $arrayofcss, '');

// llxHeader('', $langs->trans("Kanview_SetupPage"), '', '', 0, 0, '', array(str_replace(DOL_URL_ROOT, '', KANVIEW_URL_ROOT) . '/css/' . str_replace('.php', '.css', basename($_SERVER['SCRIPT_NAME']))));

$linkback = '<a href="' . DOL_URL_ROOT . '/admin/modules.php">' . $langs->trans("BackToModuleList") . '</a>';
echo load_fiche_titre($langs->trans("Kanview_SetupPage"), $linkback, 'title_setup');

$head	 = kanview_admin_prepare_head();
// $titre = $langs->trans("libelleSingulierCode");
$picto = 'kanview@kanview'; // icone du module,

dol_fiche_head($head, 'setup', $langs->trans("Module125032Name"), 0, $picto);

//
// -------------------------------------------------  view options principales 
//


// 
// ----------- group Kanview_ConstGroupMain
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupMain"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_HOME_PAGE
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_HOME_PAGE') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_HOME_PAGE
$ajax_combobox = false;
$values = 'PROJETS,TASKS,PROPALS,ORDERS,INVOICES,PROSPECTS,INVOICES_SUPPLIERS';
$keys = 'projets,tasks,propals,orders,invoices,prospects,invoices_suppliers';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_HOME_PAGE" class="flat" name="KANVIEW_HOME_PAGE" title="' . $langs->trans('KANVIEW_HOME_PAGE_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_HOME_PAGE) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_HOME_PAGE)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_HOME_PAGE" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_HOME_PAGE" title="' . $langs->trans('KANVIEW_HOME_PAGE_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_HOME_PAGE) ? $langs->trans($conf->global->KANVIEW_HOME_PAGE) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_HOME_PAGE');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_HOME_PAGE" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_SHOW_PICTO
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_SHOW_PICTO') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_SHOW_PICTO
$ajax_combobox = false;
$values = 'OUI,NON';
$keys = '1,0';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_SHOW_PICTO" class="flat" name="KANVIEW_SHOW_PICTO" title="' . $langs->trans('KANVIEW_SHOW_PICTO_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_SHOW_PICTO) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_SHOW_PICTO)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_SHOW_PICTO" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_SHOW_PICTO" title="' . $langs->trans('KANVIEW_SHOW_PICTO_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_SHOW_PICTO) ? $langs->trans($conf->global->KANVIEW_SHOW_PICTO) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_SHOW_PICTO');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_SHOW_PICTO" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




print '<br><br>';

// 
// ----------- group Kanview_ConstGroupProjets
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupProjets"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_PROJETS_TAG
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_PROJETS_TAG
$ajax_combobox = false;
$values = 'OPP_PERCENT,OPP_AMOUNT,DATEO,DATEE';
$keys = 'opp_percent,opp_amount,dateo,datee';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_PROJETS_TAG" class="flat" name="KANVIEW_PROJETS_TAG" title="' . $langs->trans('KANVIEW_PROJETS_TAG_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROJETS_TAG) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROJETS_TAG)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_PROJETS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_PROJETS_TAG" title="' . $langs->trans('KANVIEW_PROJETS_TAG_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_PROJETS_TAG) ? $langs->trans($conf->global->KANVIEW_PROJETS_TAG) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_PROJETS_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_TAG" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROJETS_DRAFT_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_DRAFT_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#46c6f4';
$value = ((! empty($conf->global->KANVIEW_PROJETS_DRAFT_COLOR)) ? $conf->global->KANVIEW_PROJETS_DRAFT_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROJETS_DRAFT_COLOR" class="flat" type="text" name="KANVIEW_PROJETS_DRAFT_COLOR" title="' . $langs->trans('KANVIEW_PROJETS_DRAFT_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROJETS_DRAFT_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_DRAFT_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROJETS_OPEN_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_OPEN_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#73bf44';
$value = ((! empty($conf->global->KANVIEW_PROJETS_OPEN_COLOR)) ? $conf->global->KANVIEW_PROJETS_OPEN_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROJETS_OPEN_COLOR" class="flat" type="text" name="KANVIEW_PROJETS_OPEN_COLOR" title="' . $langs->trans('KANVIEW_PROJETS_OPEN_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROJETS_OPEN_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_OPEN_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROJETS_CLOSED_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROJETS_CLOSED_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#ff0000';
$value = ((! empty($conf->global->KANVIEW_PROJETS_CLOSED_COLOR)) ? $conf->global->KANVIEW_PROJETS_CLOSED_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROJETS_CLOSED_COLOR" class="flat" type="text" name="KANVIEW_PROJETS_CLOSED_COLOR" title="' . $langs->trans('KANVIEW_PROJETS_CLOSED_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROJETS_CLOSED_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROJETS_CLOSED_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




print '<br><br>';

// 
// ----------- group Kanview_ConstGroupTasks
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupTasks"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_TASKS_TAG
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_TASKS_TAG
$ajax_combobox = false;
$values = 'TASK_PROJECT,TASK_PERIOD,TASK_PLANNED_WORKLOAD,TOTAL_TASK_DURATION,TASK_PROGRESSION';
$keys = 'projet_title,task_period,planned_workload,total_task_duration,progress';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_TASKS_TAG" class="flat" name="KANVIEW_TASKS_TAG" title="' . $langs->trans('KANVIEW_TASKS_TAG_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_TASKS_TAG) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_TASKS_TAG)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_TASKS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_TASKS_TAG" title="' . $langs->trans('KANVIEW_TASKS_TAG_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_TASKS_TAG) ? $langs->trans($conf->global->KANVIEW_TASKS_TAG) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_TASKS_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_TAG" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_TASKS_OK_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_OK_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#73bf44';
$value = ((! empty($conf->global->KANVIEW_TASKS_OK_COLOR)) ? $conf->global->KANVIEW_TASKS_OK_COLOR : $defaultvalue);
echo '<input id="KANVIEW_TASKS_OK_COLOR" class="flat" type="text" name="KANVIEW_TASKS_OK_COLOR" title="' . $langs->trans('KANVIEW_TASKS_OK_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_TASKS_OK_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_OK_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_TASKS_LATE1_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#f7991d';
$value = ((! empty($conf->global->KANVIEW_TASKS_LATE1_COLOR)) ? $conf->global->KANVIEW_TASKS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_TASKS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_TASKS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_TASKS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_TASKS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_LATE1_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_TASKS_LATE2_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_TASKS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#ff0000';
$value = ((! empty($conf->global->KANVIEW_TASKS_LATE2_COLOR)) ? $conf->global->KANVIEW_TASKS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_TASKS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_TASKS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_TASKS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_TASKS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_TASKS_LATE2_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




print '<br><br>';

// 
// ----------- group Kanview_ConstGroupPropals
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupPropals"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_PROPALS_TAG
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_PROPALS_TAG
$ajax_combobox = false;
$values = 'DATEP,FIN_VALIDITE,DATE_LIVRAISON,TOTAL_HT';
$keys = 'datep,fin_validite,date_livraison,total_ht';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_PROPALS_TAG" class="flat" name="KANVIEW_PROPALS_TAG" title="' . $langs->trans('KANVIEW_PROPALS_TAG_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROPALS_TAG) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROPALS_TAG)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_PROPALS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_PROPALS_TAG" title="' . $langs->trans('KANVIEW_PROPALS_TAG_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_PROPALS_TAG) ? $langs->trans($conf->global->KANVIEW_PROPALS_TAG) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_PROPALS_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_TAG" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROPALS_LATE1_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#46c6f4';
$value = ((! empty($conf->global->KANVIEW_PROPALS_LATE1_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE1_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROPALS_LATE2_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#f7991d';
$value = ((! empty($conf->global->KANVIEW_PROPALS_LATE2_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE2_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROPALS_LATE3_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#b76c99';
$value = ((! empty($conf->global->KANVIEW_PROPALS_LATE3_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE3_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE3_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE3_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROPALS_LATE4_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROPALS_LATE4_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#ff0000';
$value = ((! empty($conf->global->KANVIEW_PROPALS_LATE4_COLOR)) ? $conf->global->KANVIEW_PROPALS_LATE4_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROPALS_LATE4_COLOR" class="flat" type="text" name="KANVIEW_PROPALS_LATE4_COLOR" title="' . $langs->trans('KANVIEW_PROPALS_LATE4_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROPALS_LATE4_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROPALS_LATE4_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




print '<br><br>';

// 
// ----------- group Kanview_ConstGroupInvoices
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupInvoices"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_INVOICES_TAG
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_INVOICES_TAG
$ajax_combobox = false;
$values = 'DATEF,DATE_LIM_REGLEMENT,TOTAL_TTC_TOTAL_PAYE,TOTAL_RESTANT';
$keys = 'datef,date_lim_reglement,total_ttc_total_paye,total_restant';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_INVOICES_TAG" class="flat" name="KANVIEW_INVOICES_TAG" title="' . $langs->trans('KANVIEW_INVOICES_TAG_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_INVOICES_TAG) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_INVOICES_TAG)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_INVOICES_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_INVOICES_TAG" title="' . $langs->trans('KANVIEW_INVOICES_TAG_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_INVOICES_TAG) ? $langs->trans($conf->global->KANVIEW_INVOICES_TAG) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_INVOICES_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_TAG" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_INVOICES_LATE1_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#b76caa';
$value = ((! empty($conf->global->KANVIEW_INVOICES_LATE1_COLOR)) ? $conf->global->KANVIEW_INVOICES_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_LATE1_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_LATE1_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_LATE1_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_INVOICES_LATE2_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#f7991d';
$value = ((! empty($conf->global->KANVIEW_INVOICES_LATE2_COLOR)) ? $conf->global->KANVIEW_INVOICES_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_LATE2_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_LATE2_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_LATE2_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_INVOICES_LATE3_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#ff0000';
$value = ((! empty($conf->global->KANVIEW_INVOICES_LATE3_COLOR)) ? $conf->global->KANVIEW_INVOICES_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_LATE3_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_LATE3_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_LATE3_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




print '<br><br>';

// 
// ----------- group Kanview_ConstGroupOrders
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupOrders"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_ORDERS_TAG
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_ORDERS_TAG
$ajax_combobox = false;
$values = 'TOTAL_HT,DATE_COMANDE,DATE_LIVRAISON,TOTAL_HT_DATE_COMMANDE,TOTAL_HT_DATE_LIVRAISON';
$keys = 'total_ht,date_commande,date_livraison,total_ht_date_commande,total_ht_date_livraison';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_ORDERS_TAG" class="flat" name="KANVIEW_ORDERS_TAG" title="' . $langs->trans('KANVIEW_ORDERS_TAG_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_ORDERS_TAG) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_ORDERS_TAG)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_ORDERS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_ORDERS_TAG" title="' . $langs->trans('KANVIEW_ORDERS_TAG_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_ORDERS_TAG) ? $langs->trans($conf->global->KANVIEW_ORDERS_TAG) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_ORDERS_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_TAG" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_ORDERS_LATE1_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#b76caa';
$value = ((! empty($conf->global->KANVIEW_ORDERS_LATE1_COLOR)) ? $conf->global->KANVIEW_ORDERS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_ORDERS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_ORDERS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_ORDERS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_ORDERS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_LATE1_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_ORDERS_LATE2_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#f7991d';
$value = ((! empty($conf->global->KANVIEW_ORDERS_LATE2_COLOR)) ? $conf->global->KANVIEW_ORDERS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_ORDERS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_ORDERS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_ORDERS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_ORDERS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_LATE2_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_ORDERS_LATE3_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_ORDERS_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#ff0000';
$value = ((! empty($conf->global->KANVIEW_ORDERS_LATE3_COLOR)) ? $conf->global->KANVIEW_ORDERS_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_ORDERS_LATE3_COLOR" class="flat" type="text" name="KANVIEW_ORDERS_LATE3_COLOR" title="' . $langs->trans('KANVIEW_ORDERS_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_ORDERS_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_ORDERS_LATE3_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




print '<br><br>';

// 
// ----------- group Kanview_ConstGroupProspects
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupProspects"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_PROSPECTS_TAG
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_PROSPECTS_TAG
$ajax_combobox = false;
$values = 'COUNTRY_TOWN,EMAIL,PHONE,TYPENT_LIBELLE,EFFECTIF_LIBELLE,PROSPECTLEVEL_LABEL';
$keys = 'country_town,email,phone,typent_libelle,effectif_libelle,prospectlevel_label';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_PROSPECTS_TAG" class="flat" name="KANVIEW_PROSPECTS_TAG" title="' . $langs->trans('KANVIEW_PROSPECTS_TAG_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_PROSPECTS_TAG) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_PROSPECTS_TAG)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_PROSPECTS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_PROSPECTS_TAG" title="' . $langs->trans('KANVIEW_PROSPECTS_TAG_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_PROSPECTS_TAG) ? $langs->trans($conf->global->KANVIEW_PROSPECTS_TAG) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_PROSPECTS_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_TAG" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROSPECTS_PL_HIGH_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_HIGH_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#73bf44';
$value = ((! empty($conf->global->KANVIEW_PROSPECTS_PL_HIGH_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_HIGH_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_HIGH_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_HIGH_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_HIGH_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_HIGH_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_HIGH_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROSPECTS_PL_LOW_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_LOW_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#b76caa';
$value = ((! empty($conf->global->KANVIEW_PROSPECTS_PL_LOW_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_LOW_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_LOW_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_LOW_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_LOW_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_LOW_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_LOW_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROSPECTS_PL_MEDIUM_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#f7991d';
$value = ((! empty($conf->global->KANVIEW_PROSPECTS_PL_MEDIUM_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_MEDIUM_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_MEDIUM_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_MEDIUM_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_MEDIUM_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_MEDIUM_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_PROSPECTS_PL_NONE_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_PROSPECTS_PL_NONE_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#ff0000';
$value = ((! empty($conf->global->KANVIEW_PROSPECTS_PL_NONE_COLOR)) ? $conf->global->KANVIEW_PROSPECTS_PL_NONE_COLOR : $defaultvalue);
echo '<input id="KANVIEW_PROSPECTS_PL_NONE_COLOR" class="flat" type="text" name="KANVIEW_PROSPECTS_PL_NONE_COLOR" title="' . $langs->trans('KANVIEW_PROSPECTS_PL_NONE_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_PROSPECTS_PL_NONE_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_PROSPECTS_PL_NONE_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




print '<br><br>';

// 
// ----------- group Kanview_ConstGroupInvoicesSuppliers
// 
print load_fiche_titre($langs->trans("Kanview_ConstGroupInvoicesSuppliers"),'','');
$form=new Form($db);
$var=true;
echo '<form method="POST" action="'.$_SERVER['PHP_SELF'].'">';
echo '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
echo '<input type="hidden" name="action" value="updateoptions">';

echo '<table class="noborder" width="100%">';
// ligne des titre de la table
echo '<tr class="liste_titre">';
echo "<td>".$langs->trans("Parameters")."</td>\n";
echo '<td align="right" width="60">'.$langs->trans("Value").'</td>'."\n";
echo '<td width="80">&nbsp;</td></tr>'."\n";


// 
// --- row KANVIEW_INVOICES_SUPPLIERS_TAG
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_TAG') . ' </td>';
echo '<td width="20%" align="right">';

// ----------- EDIT - KANVIEW_INVOICES_SUPPLIERS_TAG
$ajax_combobox = false;
$values = 'DATEF,DATE_LIM_REGLEMENT,TOTAL_TTC_TOTAL_PAYE,TOTAL_RESTANT';
$keys = 'datef,date_lim_reglement,total_ttc_total_paye,total_restant';
$valuesArray = explode(',', $values);
$keysArray = explode(',', $keys);
$count = count($valuesArray);
if(count($keysArray) != $count)
$keysArray = array();
if ($count > 0) {
echo '<select id="KANVIEW_INVOICES_SUPPLIERS_TAG" class="flat" name="KANVIEW_INVOICES_SUPPLIERS_TAG" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_TAG_DESC') . '">';
echo '';		// fournie par le générateur
for ($i = 0; $i < $count; $i++) {
if((isset($keysArray[$i]) && $keysArray[$i] == $conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG) || ( ! isset($keysArray[$i]) && $valuesArray[$i] == $conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG)){
$optionSelected = 'selected';
}else{
$optionSelected = '';
}
echo '<option value="' . (isset($keysArray[$i]) ? $keysArray[$i] : $valuesArray[$i]) . '" ' . $optionSelected . '>' . ( ! empty($valuesArray[$i]) ? $langs->trans($valuesArray[$i]) :  '') . '</option>';
}
echo '</select>';
} else {
dol_syslog(__FILE__ . ' - ' . __LINE__ . ' - ' . 'Aucune valeur dans la liste', LOG_ERR);
// en cas d'echec de l'affichage de la liste, on affiche un input standard
echo '<input id="KANVIEW_INVOICES_SUPPLIERS_TAG" class="flat __ADDITIONAL_CLASSES__" name="KANVIEW_INVOICES_SUPPLIERS_TAG" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_TAG_DESC') . '" value="' . ( ! empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG) ? $langs->trans($conf->global->KANVIEW_INVOICES_SUPPLIERS_TAG) : '') . '">';

}
if($ajax_combobox){
include_once DOL_DOCUMENT_ROOT . '/core/lib/ajax.lib.php';
        echo ajax_combobox('KANVIEW_INVOICES_SUPPLIERS_TAG');
}
/// ---


echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_TAG" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#b76caa';
$value = ((! empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR)) ? $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#f7991d';
$value = ((! empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR)) ? $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




// 
// --- row KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR
// 
$var=!$var;
echo '<tr ' . $bc[$var] . '>';
echo '<td width="30%">' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR') . ' </td>';
echo '<td width="20%" align="right">';
$defaultvalue = '#ff0000';
$value = ((! empty($conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR)) ? $conf->global->KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR : $defaultvalue);
echo '<input id="KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR" class="flat" type="text" name="KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR" title="' . $langs->trans('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR_DESC') . '" size="3" maxlength="3" ' . 
'value="' . $value . '" style="" >';
echo '<script>$("#KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR").ejColorPicker({locale: "' . $locale . '", modelType: "palette"});</script>';
echo '</td>';
echo '<td align="left">';
echo '<input type="submit" class="button" name="submit_KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR" value="'.$langs->trans("Modify").'">';
echo '</td>';
echo '</tr>';




print '</table>';
print '</form>';




//
// ------------------------------------------------ view Numbering models (modules de numérotation)
//

if ($hasNumberingGenerator) {

	print '<br>';
	print load_fiche_titre($langs->trans("NumberingModels"), '', '');

	print '<table class="noborder" width="100%">';
	print '<tr class="liste_titre">';
	print '<td>' . $langs->trans("Name") . '</td>';
	print '<td>' . $langs->trans("Description") . '</td>';
	print '<td class="nowrap">' . $langs->trans("Example") . '</td>';
	print '<td align="center" width="60">' . $langs->trans("Status") . '</td>';
	print '<td align="center" width="16">' . $langs->trans("ShortInfo") . '</td>';
	print '</tr>' . "\n";

	clearstatcache();

	foreach ($dirmodels as $reldir) {
		$dir = dol_buildpath($reldir . "core/modules//");

		if (is_dir($dir)) {
			$handle = opendir($dir);
			if (is_resource($handle)) {
				$var = true;

				while (($file = readdir($handle)) !== false) {
					if (substr($file, 0, dol_strlen('mod__')) == 'mod__' && substr($file, dol_strlen($file) - 3, 3) == 'php') {
						$file = substr($file, 0, dol_strlen($file) - 4);

						require_once $dir . $file . '.php';

						$module = new $file($db);

						// Show modules according to features level
						if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2)
							continue;
						if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1)
							continue;

						if ($module->isEnabled()) {
							$var = !$var;
							print '<tr ' . $bc[$var] . '><td>' . $module->nom . "</td><td>\n";
							print $module->info();
							print '</td>';

							// Show example of numbering model
							print '<td class="nowrap">';
							$tmp = $module->getExample();
							if (preg_match('/^Error/', $tmp))
								print '<div class="error">' . $langs->trans($tmp) . '</div>';
							elseif ($tmp == 'NotConfigured')
								print $langs->trans($tmp);
							else
								print $tmp;
							print '</td>' . "\n";

							print '<td align="center">';
							if ($conf->global->_ADDON_NUMBER == $file) {
								print img_picto($langs->trans("Activated"), 'switch_on');
							} else {
								print '<a href="' . $_SERVER["PHP_SELF"] . '?action=setmod&amp;value=' . $file . '">';
								print img_picto($langs->trans("Disabled"), 'switch_off');
								print '</a>';
							}
							print '</td>';

							$object = new __MyEnityClass__($db);
							$object->initAsSpecimen();

							// Info
							$htmltooltip	 = '';
							$htmltooltip	 .= '' . $langs->trans("Version") . ': <b>' . $module->getVersion() . '</b><br>';
							$object->type	 = 0;
							$nextval			 = $module->getNextValue($mysoc, $object);
							if ("$nextval" != $langs->trans("NotAvailable")) { // Keep " on nextval
								$htmltooltip .= '' . $langs->trans("NextValue") . ': ';
								if ($nextval) {
									if (preg_match('/^Error/', $nextval) || $nextval == 'NotConfigured')
										$nextval		 = $langs->trans($nextval);
									$htmltooltip .= $nextval . '<br>';
								} else {
									$htmltooltip .= $langs->trans($module->error) . '<br>';
								}
							}

							print '<td align="center">';
							print $form->textwithpicto('', $htmltooltip, 1, 0);
							print '</td>';

							print "</tr>\n";
						}
					}
				}
				closedir($handle);
			}
		}
	}
	print "</table>";
}

// 
// -------------------------------------------- view Document templates generators
// 

if ($hasDocGenerator) {

	print '<br>';
	print load_fiche_titre($langs->trans("PdfModels"), '', '');

	// Load array def with activated templates
	$def	 = array();
	$sql	 = "SELECT nom";
	$sql	 .= " FROM " . MAIN_DB_PREFIX . "document_model";
	$sql	 .= " WHERE type = '" . $module_nom . "'";
	$sql	 .= " AND entity = " . $conf->entity;
	$resql = $db->query($sql);
	if ($resql) {
		$i				 = 0;
		$num_rows	 = $db->num_rows($resql);
		while ($i < $num_rows) {
			$array = $db->fetch_array($resql);
			array_push($def, $array[0]);
			$i ++;
		}
	} else {
		dol_print_error($db);
	}

	print "<table class=\"noborder\" width=\"100%\">\n";
	print "<tr class=\"liste_titre\">\n";
	print '<td>' . $langs->trans("Name") . '</td>';
	print '<td>' . $langs->trans("Description") . '</td>';
	print '<td align="center" width="60">' . $langs->trans("Status") . "</td>\n";
	print '<td align="center" width="60">' . $langs->trans("Default") . "</td>\n";
	print '<td align="center" width="38">' . $langs->trans("ShortInfo") . '</td>';
	print '<td align="center" width="38">' . $langs->trans("Preview") . '</td>';
	print "</tr>\n";

	clearstatcache();

	$var = true;
	foreach ($dirmodels as $reldir) {
		foreach (array(
		'',
		'/doc') as $valdir) {

			$dir = dol_buildpath($reldir . "core/modules/kanview" . $valdir);

			if (is_dir($dir)) {

				$handle = opendir($dir);
				if (is_resource($handle)) {
					while (($file = readdir($handle)) !== false) {
						$filelist[] = $file;
					}
					closedir($handle);
					arsort($filelist);

					foreach ($filelist as $file) {
						if (preg_match('/\.modules\.php$/i', $file) && preg_match('/^(pdf_|doc_)/', $file)) {

							if (file_exists($dir . '/' . $file)) {
								$name			 = substr($file, 4, dol_strlen($file) - 16);
								$classname = substr($file, 0, dol_strlen($file) - 12);

								require_once $dir . '/' . $file;
								$module = new $classname($db);

								$modulequalified = 1;
								if ($module->version == 'development' && $conf->global->MAIN_FEATURES_LEVEL < 2)
									$modulequalified = 0;
								if ($module->version == 'experimental' && $conf->global->MAIN_FEATURES_LEVEL < 1)
									$modulequalified = 0;

								if ($modulequalified) {
									$var = !$var;
									print '<tr ' . $bc[$var] . '><td width="100">';
									print(empty($module->name) ? $name : $module->name);
									print "</td><td>\n";
									if (method_exists($module, 'info'))
										print $module->info($langs);
									else
										print $module->description;
									print '</td>';

									// Active
									if (in_array($name, $def)) {
										print '<td align="center">' . "\n";
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=del&value=' . $name . '">';
										print img_picto($langs->trans("Enabled"), 'switch_on');
										print '</a>';
										print '</td>';
									} else {
										print '<td align="center">' . "\n";
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=set&value=' . $name . '&amp;scandir=' . $module->scandir . '&amp;label=' . urlencode($module->name) . '">' . img_picto($langs->trans("Disabled"), 'switch_off') . '</a>';
										print "</td>";
									}

									// Default
									print '<td align="center">';
									if ($conf->global->_ADDON_PDF == $name) {
										print img_picto($langs->trans("Default"), 'on');
									} else {
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=setdoc&value=' . $name . '&amp;scandir=' . $module->scandir . '&amp;label=' . urlencode($module->name) . '" alt="' . $langs->trans("Default") . '">' . img_picto($langs->trans("Disabled"), 'off') . '</a>';
									}
									print '</td>';

									// Info
									$htmltooltip = '' . $langs->trans("Name") . ': ' . $module->name;
									$htmltooltip .= '<br>' . $langs->trans("Type") . ': ' . ($module->type ? $module->type : $langs->trans("Unknown"));
									if ($module->type == 'pdf') {
										$htmltooltip .= '<br>' . $langs->trans("Width") . '/' . $langs->trans("Height") . ': ' . $module->page_largeur . '/' . $module->page_hauteur;
									}

									// 									$htmltooltip .= '<br><br><u>' . $langs->trans("FeaturesSupported") . ':</u>';
									// 									$htmltooltip .= '<br>' . $langs->trans("Logo") . ': ' . yn($module->option_logo, 1, 1);
									// 									$htmltooltip .= '<br>' . $langs->trans("PaymentMode") . ': ' . yn($module->option_modereg, 1, 1);
									// 									$htmltooltip .= '<br>' . $langs->trans("PaymentConditions") . ': ' . yn($module->option_condreg, 1, 1);
									// 									$htmltooltip .= '<br>' . $langs->trans("MultiLanguage") . ': ' . yn($module->option_multilang, 1, 1);
									// 									//$htmltooltip.='<br>'.$langs->trans("Discounts").': '.yn($module->option_escompte,1,1);
									// 									//$htmltooltip.='<br>'.$langs->trans("CreditNote").': '.yn($module->option_credit_note,1,1);
									// 									$htmltooltip .= '<br>' . $langs->trans("WatermarkOnDraft") . ': ' . yn($module->option_draft_watermark, 1, 1);
									// __OTHER_INFOS__

									print '<td align="center">';
									print $form->textwithpicto('', $htmltooltip, 1, 0);
									print '</td>';

									// Preview
									print '<td align="center">';
									if ($module->type == 'pdf') {
										print '<a href="' . $_SERVER["PHP_SELF"] . '?action=specimen&module=' . $name . '">' . img_object($langs->trans("Preview"), 'bill') . '</a>';
									} else {
										print img_object($langs->trans("PreviewNotAvailable"), 'generic');
									}
									print '</td>';

									print "</tr>\n";
								}
							}
						}
					}
				}
			}
		}
	}

	print '</table>';
}

dol_fiche_end();

// ----------------------------------- javascripts spécifiques à cette page
// quelques variables javascripts fournis par php
echo '<script type="text/javascript">
 		var dateSeparator = "' . trim(substr($langs->trans('FormatDateShort'), 2, 1)) . '";
 		var KANVIEW_URL_ROOT = "' . trim(KANVIEW_URL_ROOT) . '";
 		var locale = "' . trim($langs->defaultlang) . '";
		var  = "' . trim($langs->trans('Kanview_TopMenu_Dashboard')) . '";
 		
var UpdateNotAllowed_ProjectClosed = "' . trim($langs->transnoentities('UpdateNotAllowed_ProjectClosed')) . '"; 
		var parent1	= "' . trim(module) . '";
		var parent2	= "' . trim($container) . '";
 	</script>';

// includes de fichiers javascripts
echo '<script src="' . KANVIEW_URL_ROOT . '/js/kanview.js?b=' . $build . '"></script>';
echo '<script src="' . KANVIEW_URL_ROOT . '/js/' . str_replace('.php', '.js', basename($_SERVER['SCRIPT_NAME'])) . '?b=' . $build . '"></script>';

llxFooter();
$db->close();

// --------------------------------------------- Functions -------------------------------------------

/**
 * Prepare array with list of tabs for page Admin/Config
 *
 * @return array Array of tabs to show
 */
function kanview_admin_prepare_head() {
	global $langs, $conf, $user;

	$langs->load("kanview@kanview");

	$h		 = 0;
	$head	 = array();

	// onglet principal page config
	$head[$h][0] = KANVIEW_URL_ROOT . '/admin/kanview_config.php';
	$head[$h][1] = $langs->trans("Setup");
	$head[$h][2] = 'setup';
	$h ++;

	// onglet pour extrafields
	
	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@kanview:/kanview/mypage.php?id=__ID__'); to add new tab
	// $this->tabs = array('entity:-tabname); to remove a tab
	complete_head_from_modules($conf, $langs, null, $head, $h, 'my_table_admin');

	complete_head_from_modules($conf, $langs, null, $head, $h, 'my_table_admin', 'remove');

	return $head;
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
