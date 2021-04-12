<?php
/* Copyright (C) 2011 Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2017	Philippe Grand	<philippe.grand@atoo-net.com>
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
 *  \file       htdocs/custom/ultimatepdf/admin/options.php
 *  \ingroup    ultimatepdf
 *  \brief      Page d'administration/configuration du module ultimatepdf
 */

$res=0;
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");					// For root directory
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");	// For "custom" directory

require_once(DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formbarcode.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formadmin.class.php");
require_once("../lib/ultimatepdf.lib.php");

$langs->load("admin");
$langs->load("ultimatepdf@ultimatepdf");

// Security check
if (! $user->admin) accessforbidden();

$action	= GETPOST('action');


/*
 * Action
 */
if (preg_match('/set_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_set_const($db, $code, 1, 'chaine', 0, '', $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

if (preg_match('/del_(.*)/',$action,$reg))
{
    $code=$reg[1];
    if (dolibarr_del_const($db, $code, $conf->entity) > 0)
    {
        Header("Location: ".$_SERVER["PHP_SELF"]);
        exit;
    }
    else
    {
        dol_print_error($db);
    }
}

if ($action == 'GENBARCODE_BARCODETYPE_THIRDPARTY')
{
	$coder_id = GETPOST('coder_id','alpha');
	$res = dolibarr_set_const($db, "GENBARCODE_BARCODETYPE_THIRDPARTY", $coder_id,'chaine',0,'',$conf->entity);
}

if ($action == 'update')
{
	dolibarr_set_const($db, "MAIN_PDF_FORMAT", $_POST["MAIN_PDF_FORMAT"],'chaine',0,'',$conf->entity);
	
	dolibarr_set_const($db, "MAIN_PROFID1_IN_ADDRESS", $_POST["MAIN_PROFID1_IN_ADDRESS"],'chaine',0,'',$conf->entity);
	dolibarr_set_const($db, "MAIN_PROFID2_IN_ADDRESS",    $_POST["MAIN_PROFID2_IN_ADDRESS"],'chaine',0,'',$conf->entity);
	dolibarr_set_const($db, "MAIN_PROFID3_IN_ADDRESS",    $_POST["MAIN_PROFID3_IN_ADDRESS"],'chaine',0,'',$conf->entity);
	dolibarr_set_const($db, "MAIN_PROFID4_IN_ADDRESS",    $_POST["MAIN_PROFID4_IN_ADDRESS"],'chaine',0,'',$conf->entity);
	
	header("Location: ".$_SERVER["PHP_SELF"]."?mainmenu=home&leftmenu=setup");
	exit;
}

/*
 * View
 */

$wikihelp='EN:Module_Ultimatepdf_EN#Options_tab|FR:Module_Ultimatepdf_FR#Onglet_Options';
llxHeader('',$langs->trans("UltimatepdfSetup"), $wikihelp);

$formbarcode=new FormBarCode($db);
$formadmin=new FormAdmin($db);

$linkback='<a href="'.DOL_URL_ROOT.'/admin/modules.php">'.$langs->trans("BackToModuleList").'</a>';
print_fiche_titre($langs->trans("UltimatepdfSetup"),$linkback,'ultimatepdf@ultimatepdf');

$head = ultimatepdf_prepare_head();

dol_fiche_head($head, 'options', $langs->trans("ModuleSetup"), 0, "ultimatepdf@ultimatepdf");

print $langs->trans("PDFDesc")."<br>\n";
print "<br>\n";

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetUpHeader").'</em></b>';
print '</div>';

// Addresses
print_fiche_titre($langs->trans("PDFAddressForging"),'','').'<br>';
$var=true;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// Display Public Note In Source Address. 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ShowAlsoPublicNoteInSourceAddress").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_PUBLIC_NOTE_IN_ADDRESS');
}
else
{
	if($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_PUBLIC_NOTE_IN_ADDRESS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_PUBLIC_NOTE_IN_ADDRESS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
// add also details for contact address. 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ShowAlsoTargetDetails").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_PDF_ADDALSOTARGETDETAILS');
}
else
{
	if($conf->global->MAIN_PDF_ADDALSOTARGETDETAILS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_PDF_ADDALSOTARGETDETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_PDF_ADDALSOTARGETDETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_PDF_ADDALSOTARGETDETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// use company name of contact. 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("MainUseCompanyNameOfContactInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_USE_COMPANY_NAME_OF_CONTACT');
}
else
{
	if($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_USE_COMPANY_NAME_OF_CONTACT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_USE_COMPANY_NAME_OF_CONTACT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// hide TVA intra within address. 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideTvaIntraWithinAddress").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_TVAINTRA_NOT_IN_ADDRESS');
}
else
{
	if($conf->global->MAIN_TVAINTRA_NOT_IN_ADDRESS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_TVAINTRA_NOT_IN_ADDRESS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_TVAINTRA_NOT_IN_ADDRESS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_TVAINTRA_NOT_IN_ADDRESS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Show prof id 1 in address into pdf
if (! $noCountryCode)
{
	$pid1=$langs->transcountry("ProfId1",$mysoc->country_code);
	if ($pid1 == '-') $pid1=false;
}
else
{
	$pid1 = img_warning().' <font class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("CompanyCountry")).'</font>';
}
if ($pid1)
{
	$var=!$var;
	print "<tr ".$bc[$var].">";
	print '<td>'.$langs->trans("ShowProfIdInAddress").' - '.$pid1.'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
}

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_PROFID1_IN_ADDRESS');
}
else
{
	if($conf->global->MAIN_PROFID1_IN_ADDRESS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_PROFID1_IN_ADDRESS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_PROFID1_IN_ADDRESS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_PROFID1_IN_ADDRESS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Show prof id 2 in address into pdf
if (! $noCountryCode)
{
	$pid2=$langs->transcountry("ProfId2",$mysoc->country_code);
	if ($pid2 == '-') $pid2=false;
}
else
{
	$pid2 = img_warning().' <font class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("CompanyCountry")).'</font>';
}
if ($pid2)
{
	$var=!$var;
	print "<tr ".$bc[$var].">";
	print '<td>'.$langs->trans("ShowProfIdInAddress").' - '.$pid2.'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
}

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_PROFID2_IN_ADDRESS');
}
else
{
	if($conf->global->MAIN_PROFID2_IN_ADDRESS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_PROFID2_IN_ADDRESS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_PROFID2_IN_ADDRESS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_PROFID2_IN_ADDRESS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Show prof id 3 in address into pdf
if (! $noCountryCode)
{
	$pid3=$langs->transcountry("ProfId3",$mysoc->country_code);
	if ($pid3 == '-') $pid3=false;
}
else
{
	$pid3 = img_warning().' <font class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("CompanyCountry")).'</font>';
}
if ($pid3)
{
	$var=!$var;
	print "<tr ".$bc[$var].">";
	print '<td>'.$langs->trans("ShowProfIdInAddress").' - '.$pid3.'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
}

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_PROFID3_IN_ADDRESS');
}
else
{
	if($conf->global->MAIN_PROFID3_IN_ADDRESS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_PROFID3_IN_ADDRESS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_PROFID3_IN_ADDRESS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_PROFID3_IN_ADDRESS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Show prof id 4 in address into pdf
if (! $noCountryCode)
{
	$pid4=$langs->transcountry("ProfId4",$mysoc->country_code);
	if ($pid4 == '-') $pid4=false;
}
else
{
	$pid4 = img_warning().' <font class="error">'.$langs->trans("ErrorFieldRequired",$langs->transnoentitiesnoconv("CompanyCountry")).'</font>';
}
if ($pid4)
{
	$var=!$var;
	print "<tr ".$bc[$var].">";
	print '<td>'.$langs->trans("ShowProfIdInAddress").' - '.$pid4.'</td>';
	print '<td align="center" width="20">&nbsp;</td>';
}

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_PROFID4_IN_ADDRESS');
}
else
{
	if($conf->global->MAIN_PROFID4_IN_ADDRESS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_PROFID4_IN_ADDRESS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_PROFID4_IN_ADDRESS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_PROFID4_IN_ADDRESS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetCoreBloc").'</em></b>';
print '</div>';
print '</td></tr>';

print_fiche_titre($langs->trans("PDFColumnForging"),'','').'<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

/*
 * Formulaire parametres fabrication des colonnes
 */
 
 // Hide product description 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideByDefaultProductDescInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_GENERATE_DOCUMENTS_HIDE_DESC');
}
else
{
	if($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_GENERATE_DOCUMENTS_HIDE_DESC">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DESC == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_GENERATE_DOCUMENTS_HIDE_DESC">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide product reference 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideByDefaultProductRefInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_GENERATE_DOCUMENTS_HIDE_REF');
}
else
{
	if($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_GENERATE_DOCUMENTS_HIDE_REF">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_REF == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_GENERATE_DOCUMENTS_HIDE_REF">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide product details 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideBydefaultProductDetailsInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS');
}
else
{
	if($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Show line total with TTC
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ShowBydefaultLineWithTotalTTCInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_SHOW_LINE_TTTC');
}
else
{
	if($conf->global->ULTIMATE_SHOW_LINE_TTTC == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_SHOW_LINE_TTTC">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_SHOW_LINE_TTTC == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_SHOW_LINE_TTTC">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide product VAT column
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideBydefaultProductVATColumnInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_SHOW_HIDE_VAT_COLUMN');
}
else
{
	if($conf->global->ULTIMATE_SHOW_HIDE_VAT_COLUMN == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_SHOW_HIDE_VAT_COLUMN">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_SHOW_HIDE_VAT_COLUMN == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_SHOW_HIDE_VAT_COLUMN">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide product PUHT
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideBydefaultProductPUHTInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_SHOW_HIDE_PUHT');
}
else
{
	if($conf->global->ULTIMATE_SHOW_HIDE_PUHT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_SHOW_HIDE_PUHT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_SHOW_HIDE_PUHT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide product QTY
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideBydefaultProductQtyInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_SHOW_HIDE_QTY');
}
else
{
	if($conf->global->ULTIMATE_SHOW_HIDE_PUHT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_SHOW_HIDE_QTY">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_SHOW_HIDE_PUHT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_SHOW_HIDE_QTY">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Hide product Total HT
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideBydefaultProductTHTInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATE_SHOW_HIDE_THT');
}
else
{
	if($conf->global->ULTIMATE_SHOW_HIDE_PUHT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATE_SHOW_HIDE_THT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATE_SHOW_HIDE_THT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATE_SHOW_HIDE_THT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Activate Unit column
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ActivateProductUnitcolumn").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('PRODUCT_USE_UNITS');
}
else
{
	if($conf->global->PRODUCT_USE_UNITS == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_PRODUCT_USE_UNITS">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->PRODUCT_USE_UNITS == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_PRODUCT_USE_UNITS">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

print '<div align="center" class="info">';
print '<em><b>'.$langs->trans("SetFooterBloc").'</em></b>';
print '</div>';
print '</td></tr>';

/*
 * Formulaire parametres divers
 */
 
print_fiche_titre($langs->trans("UltimatepdfMiscellaneous"),'','').'<br>';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>'."\n";
print '<td align="center" width="20">&nbsp;</td>';
print '<td align="center" width="100">'.$langs->trans("Value").'</td>'."\n";
print '</tr>';

// PDF_BANK_HIDE_NUMBER_SHOW_ONLY_BICIBAN. 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("HideByDefaultBankDetailsInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('PDF_BANK_HIDE_NUMBER_SHOW_ONLY_BICIBAN');
}
else
{
	if($conf->global->PDF_BANK_HIDE_NUMBER_SHOW_ONLY_BICIBAN == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_PDF_BANK_HIDE_NUMBER_SHOW_ONLY_BICIBAN">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->PDF_BANK_HIDE_NUMBER_SHOW_ONLY_BICIBAN == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_PDF_BANK_HIDE_NUMBER_SHOW_ONLY_BICIBAN">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// use autowrap on free text. 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("UseAutowrapOnFreeTextInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('MAIN_USE_AUTOWRAP_ON_FREETEXT');
}
else
{
	if($conf->global->MAIN_USE_AUTOWRAP_ON_FREETEXT == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_MAIN_USE_AUTOWRAP_ON_FREETEXT">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->MAIN_USE_AUTOWRAP_ON_FREETEXT == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_MAIN_USE_AUTOWRAP_ON_FREETEXT">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';
print '</table>';

print load_fiche_titre($langs->trans("OtherOptions"),'','');

print "<form method=\"post\" action=\"".$_SERVER["PHP_SELF"]."\">";
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print "<input type=\"hidden\" name=\"action\" value=\"update\">";

$var=true;
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre">';
print '<td>'.$langs->trans("Parameters").'</td>';
print '<td width="60" align="center">'.$langs->trans("Value").'</td>';
print '<td>&nbsp;</td>';
print '</tr>';

// add barcode at bottom within documents. 
$var=!$var;
print '<tr '.$bc[$var].'>';
print '<td>'.$langs->trans("ShowByDefaultBarcodeAtBottomInsideUltimatepdf").'</td>';
print '<td align="center" width="20">&nbsp;</td>';

print '<td align="center" width="100">';
if ($conf->use_javascript_ajax)
{
	print ajax_constantonoff('ULTIMATEPDF_GENERATE_DOCUMENTS_WITH_BOTTOM_BARCODE');
}
else
{
	if($conf->global->ULTIMATEPDF_GENERATE_DOCUMENTS_WITH_BOTTOM_BARCODE == 0)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=set_ULTIMATEPDF_GENERATE_DOCUMENTS_WITH_BOTTOM_BARCODE">'.img_picto($langs->trans("Disabled"),'off').'</a>';
	}
	else if($conf->global->ULTIMATEPDF_GENERATE_DOCUMENTS_WITH_BOTTOM_BARCODE == 1)
	{
		print '<a href="'.$_SERVER['PHP_SELF'].'?action=del_ULTIMATEPDF_GENERATE_DOCUMENTS_WITH_BOTTOM_BARCODE">'.img_picto($langs->trans("Enabled"),'on').'</a>';
	}
}
print '</td></tr>';

// Module thirdparty
if (! empty($conf->societe->enabled))
{
	$var=!$var;
	print "<tr ".$bc[$var].">";
	print '<td>'.$langs->trans("SetDefaultBarcodeTypeThirdParties").'</td>';
	print '<td width="60" align="right">';
	print $formbarcode->select_barcode_type($conf->global->GENBARCODE_BARCODETYPE_THIRDPARTY,"GENBARCODE_BARCODETYPE_THIRDPARTY",1);
	print '</td><td align="right">';
	print '<input type="submit" class="button" name="submit_GENBARCODE_BARCODETYPE_THIRDPARTY" value="'.$langs->trans("Modify").'">';
	print "</td>";
	print '</tr>';
}

print "</table>\n";
print '</form>';

print '<br>';

if ($action == 'edit')	// Edit
{
    print '<form method="post" action="'.$_SERVER["PHP_SELF"].'">';
    print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
    print '<input type="hidden" name="action" value="update">';

    clearstatcache();

    // Misc options
    print load_fiche_titre($langs->trans("DictionaryPaperFormat"),'','').'<br>';
	$var=true;
    print '<table summary="more" class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>'.$langs->trans("Parameter").'</td><td width="200px">'.$langs->trans("Value").'</td></tr>';

    $selected=$conf->global->MAIN_PDF_FORMAT;
    if (empty($selected)) $selected=dol_getDefaultFormat();

    // Show pdf format
    $var=!$var;
    print '<tr '.$bc[$var].'><td>'.$langs->trans("DictionaryPaperFormat").'</td><td>';
    print $formadmin->select_paper_format($selected,'MAIN_PDF_FORMAT');
    print '</td></tr>';

	print '</table>';

	print '<br><div class="center">';
    print '<input class="button" type="submit" value="'.$langs->trans("Save").'">';
    print '</div>';

    print '</form>';
    print '<br>';
}
else	// Show
{
    $var=true;

    // Misc options
    print load_fiche_titre($langs->trans("DictionaryPaperFormat"),'','');
	$var=true;
    print '<table summary="more" class="noborder" width="100%">';
    print '<tr class="liste_titre"><td>'.$langs->trans("Parameter").'</td><td width="200px">'.$langs->trans("Value").'</td></tr>';

    // Show pdf format
    $var=!$var;
    print '<tr '.$bc[$var].'><td>'.$langs->trans("DictionaryPaperFormat").'</td><td>';

    $pdfformatlabel='';
    if (empty($conf->global->MAIN_PDF_FORMAT))
    {
        include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
        $pdfformatlabel=dol_getDefaultFormat();
    }
    else $pdfformatlabel=$conf->global->MAIN_PDF_FORMAT;
    if (! empty($pdfformatlabel))
    {
    	$sql="SELECT code, label, width, height, unit FROM ".MAIN_DB_PREFIX."c_paper_format";
        $sql.=" WHERE code LIKE '%".$db->escape($pdfformatlabel)."%'";

        $resql=$db->query($sql);
        if ($resql)
        {
            $obj=$db->fetch_object($resql);
            $paperKey = $langs->trans('PaperFormat'.$obj->code);
            $unitKey = $langs->trans('SizeUnit'.$obj->unit);
            $pdfformatlabel = ($paperKey == 'PaperFormat'.$obj->code ? $obj->label : $paperKey).' - '.round($obj->width).'x'.round($obj->height).' '.($unitKey == 'SizeUnit'.$obj->unit ? $obj->unit : $unitKey);
        }
    }
    print $pdfformatlabel;
    print '</td></tr>';

	print '</table>';
	
	print '<div class="tabsAction">';
    print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?action=edit">'.$langs->trans("Modify").'</a>';
    print '</div>';
	print '<br>';
}

// Footer
llxFooter();
// Close database handler
$db->close();
?>
