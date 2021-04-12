<?php
/* Copyright (C) 2010-2012	Regis Houssin	<regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2015	Philippe Grand	<philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 *  \file       htdocs/custom/ultimatepdf/admin/ultimatepdf.php
 *  \ingroup    ultimatepdf
 *  \brief      Page d'administration-configuration du module Ultimatepdf
 */

$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");	// For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");	// For "custom" directory

dol_include_once('/ultimatepdf/class/actions_ultimatepdf.class.php','ActionsUltimatepdf');
require '../lib/ultimatepdf.lib.php';
require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formadmin.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.form.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formother.class.php");

$langs->load("admin");
$langs->load("other");
$langs->load("ultimatepdf@ultimatepdf");

// Security check
if (! $user->admin || $user->design) accessforbidden();

$action = GETPOST('action','alpha');

$object = new ActionsUltimatepdf($db);

$form=new Form($db);
$formadmin=new FormAdmin($db);
$formother = new FormOther($db);

/*
 * Actions
 */

$object->doActions($parameters = false, $object, $action);


/*
 * View
 */

$wikihelp='EN:Module_Ultimatepdf_EN#Setup_models|FR:Module_Ultimatepdf_FR#Configuration_des_mod.C3.A8les';
llxHeader('',$langs->trans("UltimatepdfSetup"), $wikihelp);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("UltimatepdfSetup"),$linkback,'ultimatepdf@ultimatepdf');

$head = ultimatepdf_prepare_head();
dol_fiche_head($head, 'designs', $object->getTitle($action), 0, "ultimatepdf@ultimatepdf");

// Check current version
if (!checkUltimatepdfVersion())
	dol_htmloutput_mesg($langs->trans("UltimatepdfUpgradeIsNeeded"),'','error',1);

// Assign template values
$object->assign_values($action);

// Show errors
dol_htmloutput_errors($object->error,$object->errors);

// Show messages
dol_htmloutput_mesg($object->mesg,'','ok');

// Show the template
$object->display();

// Footer
llxFooter();
// Close database handler
$db->close();
?>