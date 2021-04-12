<?php
/* Copyright (C) 2010-2012	Regis Houssin	<regis.houssin@capnetworks.com>
 * Copyright (C) 2017-2019	Peter Roberts	<webmaster@finchmc.com.au>

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
 * or see http://www.gnu.org/
 */

/**
 *	\file		htdocs/custom/wip/core/modules/pdf/pdf_project_report.modules.php
 *	\ingroup	project
 *	\brief		Class file for generating project reports
 *	\author		Peter Roberts
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/project/modules_project.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/project.class.php';
require_once DOL_DOCUMENT_ROOT.'/projet/class/task.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
/*
require_once DOL_DOCUMENT_ROOT.'/custom/customfields/lib/customfields_aux.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formprojet.class.php';
*/
require_once DOL_DOCUMENT_ROOT.'/custom/wip/class/report.class.php';

/**
 *	Class file for generating Project Reports
 */
class pdf_project_report extends ModelePDFProjects
{
	/**
	 * @var DoliDb Database handler
	 */
	public $db;

	/**
	 * @var string model name
	 */
	public $name;

	/**
	 * @var string model description (short text)
	 */
	public $description;

	/**
	 * @var string document type
	 */
	public $type;

	/**
	 * @var array() Minimum version of PHP required by module.
	 * e.g.: PHP = 5.4 = array(5, 4)
	 */
	public $phpmin = array(5, 4);

	/**
	 * Dolibarr version of the loaded document
	 * @public string
	 */
//	public $version = 'dolibarr';

	/**
	 * @var int page_largeur
	 */
	public $page_largeur;

	/**
	 * @var int page_hauteur
	 */
	public $page_hauteur;

	/**
	 * @var array format
	 */
	public $format;

	/**
	 * @var int marge_gauche
	 */
	public $marge_gauche;

	/**
	 * @var int marge_droite
	 */
	public $marge_droite;

	/**
	 * @var int marge_haute
	 */
	public $marge_haute;

	/**
	 * @var int marge_basse
	 */
	public $marge_basse;

	/**
	 * Issuer
	 * @var Company object that emits
	 */
	public $emetteur;	// Object company that issues


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db		Database handler
	 */
	public function __construct($db)
	{
		global $conf, $langs, $mysoc;

		// Load translation files required by the page
		$langs->loadLangs(array("wip@wip", "main", "projects", "companies"));

		$this->db = $db;
		$this->name = "Project Report";
		$this->description = $langs->trans("DocumentModelProjectReport");

		// Dimension page for format A4
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->MAIN_PDF_MARGIN_LEFT)?$conf->global->MAIN_PDF_MARGIN_LEFT:20;
		$this->marge_droite=isset($conf->global->MAIN_PDF_MARGIN_RIGHT)?$conf->global->MAIN_PDF_MARGIN_RIGHT:12;
		$this->marge_haute =isset($conf->global->MAIN_PDF_MARGIN_TOP)?$conf->global->MAIN_PDF_MARGIN_TOP:12;
		$this->marge_basse =isset($conf->global->MAIN_PDF_MARGIN_BOTTOM)?$conf->global->MAIN_PDF_MARGIN_BOTTOM:12;

		$this->option_logo = 1;					// Display logo FAC_PDF_LOGO
		$this->option_tva = 1;					// Manage option VAT FACTURE_TVAOPTION
		$this->option_codeproduitservice = 1;	// Display product-service code

		// Get source company
		$this->emetteur=$mysoc;
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);	// By default, if not defined

		// Define position of columns
		$this->width_hours	= 20;
		$this->width_colno	= 12;
		$this->height_title	= 9;
		$this->posx_colno	= $this->marge_gauche;
		$this->posx_desc	= $this->marge_gauche + 0;
		$this->posx_title	= $this->marge_gauche /*+ $this->width_colno*/;
		$this->posx_hours	= $this->page_largeur - $this->marge_droite - $this->width_hours;
		$this->width_desc	= $this->page_largeur - $this->marge_droite - $this->posx_desc;
		$this->width_title	= $this->page_largeur - $this->marge_droite - $this->posx_title - $this->width_hours;

		$this->posx_ref=$this->marge_gauche+1;
		$this->posx_label=$this->marge_gauche+17;
		$this->posxworkload=$this->marge_gauche+110;
		$this->posxprogress=$this->marge_gauche+130;
		$this->posxdatestart=$this->marge_gauche+142;
		$this->posxdateend=$this->marge_gauche+160;
	}

	/**
	 *	Function to build pdf onto disk
	 *
	 *	@param		Object		$object				Object to generate
	 *	@param		Translate	$outputlangs		Lang output object
	 *	@param		string		$srctemplatepath	Full path of source filename for generator using a template file
	 *	@return		int								1=OK, 0=KO
	 */
	function write_file($object,$outputlangs)
	{
		global $user, $langs, $conf, $mysoc, $db, $hookmanager;

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		// Load translation files required by the page
		$outputlangs->loadLangs(array("main", "dict", "companies", "projects"));

		// If $object is id instead of object
		if (! is_object($object))
		{
			$id = $object;
			$object = new Report($this->db);
			$result=$object->fetch($id);
			if ($result < 0)
			{
				dol_print_error($this->db,$object->error);
				return -1;
			}
		}

		$numlines = count($object->lines);

		$projectstatic = new Project($this->db);
		$result=$projectstatic->fetch($object->fk_project);
		$projectstatic->fetch_thirdparty();

		if ($projectstatic->socid > 0)
		{
			$psociete=new Societe($this->db);
			$psociete->fetch($projectstatic->socid);
		}

		if ($conf->projet->dir_output)
		{

			// Definition of $dir and $file
			$objectref = dol_sanitizeFileName($object->ref);
			$projref = dol_sanitizeFileName($projectstatic->ref);

			if ($object->specimen || preg_match('/specimen/i',$objectref)) {
				$dir = $conf->projet->dir_output .'/Reports';
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$fname = $objectref.' '.$projref;
				$fname.= (! empty($psociete->name)?(' - '.trim(dol_trunc($psociete->name,20,'right','UTF-8', 1, 0))):'');
				$fname.= (! empty($projectstatic->title)?(' - '.trim(dol_trunc($projectstatic->title,24,'right','UTF-8', 1, 0))):'');
				$filename = trim(dol_sanitizeFileName($fname));

				$dir = $conf->projet->dir_output .'/'. $projref .'/Reports';
				$file = $dir . "/" . $filename . '.pdf';
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (file_exists($dir))
			{
				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);	// Note that $action and $object may have been modified by some hooks

				// Create pdf instance
				$pdf=pdf_getInstance($this->format);
				$default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
				$pdf->SetAutoPageBreak(1,0);

				$heightforinfotot = 0;	// Height reserved to output the info and total part
				$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
// Must be set manually in Dolibarr 'Other Setup'
				$heightforfooter = $this->marge_basse + 4;	// Height reserved to output the footer (value include bottom margin)
				if ($conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS >0) $heightforfooter+= 6;

				if (class_exists('TCPDF'))
				{
					$pdf->setPrintHeader(false);
					$pdf->setPrintFooter(false);
				}
				$pdf->SetFont(pdf_getPDFFont($outputlangs));
				// Set path to the background PDF File
				if (! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
				{
					$pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
					$tplidx = $pdf->importPage(1);
				}

				// Load Object for Project Details
				$sql = "SELECT";
				$sql.= " p.rowid AS projectid, p.ref AS ref, p.title AS title, p.fk_statut";
				$sql.= ", s.nom AS name, s.rowid AS socid, s.phone AS phone";
				$sql.= ", am.makename as makename, am.make_logo as make_logo";
				$sql.= ", acc.colourname AS colourname, acc.hex_code AS hex_code";
				$sql.= ", acc.red_comp AS redcomp, acc.green_comp AS greencomp, acc.blue_comp AS bluecomp, acc.rgb_code as rgbcode, acc.text_col AS text_col";

				$sql.= " FROM ".MAIN_DB_PREFIX."projet AS p";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe AS s ON p.fk_soc = s.rowid";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_customfields AS pc ON p.rowid = pc.fk_projet";	// PJR TODO change over to extrafields
//				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."projet_task_extrafields AS pte ON pte.fk_object = pt.rowid";
//				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."workstation AS w ON w.rowid = pte.workstation";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."auto_makes AS am ON pc.makename = am.rowid";
				$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."auto_colour_codes AS acc ON pc.colourname = acc.rowid";

				$sql.= " WHERE 1=1";
				$sql.= " AND p.rowid = ".$object->fk_project;
//print '<br>SQL = '.$sql;
//exit;
				$resql = $this->db->query($sql);
				if (! $resql)
				{
					dol_print_error($this->db);
					exit;
				}
				$obj = $this->db->fetch_object($resql);

				// Description of line
				$projectdetails 			=	array();
				$projectdetails[id]			=	$obj->projectid;
				$projectdetails[ref]		=	$obj->ref;
				$projectdetails[title]		=	$obj->title;
				$projectdetails[statut]		=	$obj->fk_statut;
				$projectdetails[makename]	=	$obj->makename;
				$projectdetails[make_logo]	=	$obj->make_logo;
				$projectdetails[colourname]	=	$obj->colourname;
				$projectdetails[hex_code]	=	$obj->hex_code;

				$projectdetails[rgb]		=	array();
				//$projectdetails[rgb]		=	$obj->rgbcode;
				$projectdetails[rgb][r]		=	$obj->redcomp;
				$projectdetails[rgb][g]		=	$obj->greencomp;
				$projectdetails[rgb][b]		=	$obj->bluecomp;
				$projectdetails[text_col]	=	$obj->text_col;

				$projectdetails[custid]		=	$obj->socid;
				$projectdetails[custname]	=	$obj->name;

				$this->db->free($resql);


				// ***********************************************************
				$sql = 'SELECT SUM(wrd.qty) as total, SUM(wrd.discounted_qty) as total_adjusted';
				$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet as wrd';
				$sql.= ' WHERE wrd.fk_report = '.$object->id.' AND wrd.direct_amortised = 0';

				$resql = $this->db->query($sql);
				if (! $resql)
				{
					dol_print_error($this->db);
					exit;
				}
				$res = $this->db->fetch_object($resql);

				$total_direct				= $res->total;
				$total_direct_adjusted		= $res->total_adjusted;
				$total_direct_discounted	= $total_direct - $total_direct_adjusted;
				$this->db->free($resql);

				$sql = 'SELECT SUM(wrd.qty) as total, SUM(wrd.discounted_qty) as total_adjusted';
				$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet as wrd';
				$sql.= ' WHERE wrd.fk_report = '.$object->id.' AND wrd.direct_amortised = 1';

				$resql = $this->db->query($sql);
				if (! $resql) dol_print_error($this->db);

				$res = $this->db->fetch_object($resql);

				$total_amortised			= $res->total;
				$total_amortised_adjusted	= $res->total_adjusted;
				$total_amortised_discounted	= $total_amortised - $total_amortised_adjusted;

				$this->db->free($resql);

				$total_hours_reported		= $total_direct + $total_amortised;
				$total_hours_discounted		= $total_direct_discounted + $total_amortised_discounted;
				$total_hours_adjusted		= $total_direct_adjusted + $total_amortised_adjusted;

				$total_hours_efficiency		= (! $total_hours_reported == 0 ? 100 * $total_hours_adjusted / $total_hours_reported : 0 );

				// ***********************************************************

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("ProjectReport"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("ProjectReport"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// Cover page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				// get the current page break margin
				$bMargin = $pdf->getBreakMargin();
				// get current auto-page-break mode
				$auto_page_break = $pdf->getAutoPageBreak();
				// disable auto-page-break
				$pdf->SetAutoPageBreak(false, 0);
				// set background image
				$img_file = DOL_DOCUMENT_ROOT.'/custom/wip/img/reportcover_background.png';
				$pdf->Image($img_file, 0, 0, 210, 297, '', '', '', false, 300, '', false, false, 0);
				// restore auto-page-break status
				$pdf->SetAutoPageBreak($auto_page_break, $bMargin);
				// set the starting point for the page content
				$pdf->setPageMark();

				// Address Block
				// ------------------------------------------------------------
				$posx = 161;
				$posy = 22;
				// Show sender name
				$pdf->SetTextColor(153, 0, 0);
				$pdf->SetXY($posx, $posy);
				$pdf->SetFont('', 'B', $default_font_size - 2.5);
				$pdf->MultiCell(50, 4, 'Finch Motor Company Pty. Ltd.', 0, 'L');
				$posy=$pdf->getY();
				$pdf->SetXY($posx, $posy-1);
				$pdf->MultiCell(50, 4, '(t/a '.$outputlangs->convToOutputCharset($this->emetteur->name).')', 0, 'L');

				// Prof Id 1
				if ($this->emetteur->idprof1 && ($this->emetteur->country_code != 'FR' || ! $this->emetteur->idprof2))
				{
					$field=$outputlangs->transcountrynoentities("ProfId1", $this->emetteur->country_code);
					if (preg_match('/\((.*)\)/i', $field, $reg)) $field=$reg[1];
					$pi_line.=($pi_line?" - ":"").$field.": ".$outputlangs->convToOutputCharset($this->emetteur->idprof1);
				}
				// Prof Id 2
				if ($this->emetteur->idprof2)
				{
					$field=$outputlangs->transcountrynoentities("ProfId2", $this->emetteur->country_code);
					if (preg_match('/\((.*)\)/i', $field, $reg)) $field=$reg[1];
					$pi_line.=($pi_line?" - ":"").$field.": ".$outputlangs->convToOutputCharset($this->emetteur->idprof2);
				}

				// Professional Id
				$posy=$pdf->getY();
				$pdf->SetTextColor(0, 0, 0);
				$pdf->SetXY($posx, $posy-1);
				$pdf->SetFont('', '', $default_font_size - 3);
				$pdf->MultiCell(50, 4, $pi_line, 0, 'L');

				// Show address
				if (! empty($this->emetteur->state_id) && empty($this->emetteur->state))	$this->emetteur->state=getState($this->emetteur->state_id);
				$addressline1 = $outputlangs->convToOutputCharset($this->emetteur->address.', '.$this->emetteur->town);
				$addressline2 = $outputlangs->convToOutputCharset($this->emetteur->state.', '.$this->emetteur->zip);

				$posy=$pdf->getY();
				$pdf->SetXY($posx, $posy-1);
				$pdf->SetFont('', '', $default_font_size - 3);
				$pdf->MultiCell(50, 4, $addressline1, 0, 'L');
				$posy=$pdf->getY();
				$pdf->SetXY($posx, $posy-1.5);
				$pdf->MultiCell(50, 4, $addressline2, 0, 'L');
		  
				if (empty($conf->global->MAIN_PDF_DISABLESOURCEDETAILS))
				{
					// Phone
					if ($this->emetteur->phone) $stringaddress .= ($stringaddress ? "\n" : '' ).$outputlangs->transnoentities("PhoneShort").": ".$outputlangs->convToOutputCharset($this->emetteur->phone);
					// Fax
					if ($this->emetteur->fax) $stringaddress .= ($stringaddress ? ($this->emetteur->phone ? " - " : "\n") : '' ).$outputlangs->transnoentities("Fax").": ".$outputlangs->convToOutputCharset($this->emetteur->fax);
					// EMail
					if ($this->emetteur->email) $stringaddress .= ($stringaddress ? "\n" : '' ).$outputlangs->transnoentities("Email").": ".$outputlangs->convToOutputCharset($this->emetteur->email);
					// Web
					if ($this->emetteur->url) $stringaddress .= ($stringaddress ? "\n" : '' ).$outputlangs->transnoentities("Web").": ".$outputlangs->convToOutputCharset($this->emetteur->url);
				}

				$posy=$pdf->getY();
				$pdf->SetXY($posx, $posy-1);
				$pdf->SetFont('', '', $default_font_size - 3);
				$pdf->MultiCell(48, 4, $stringaddress, 0, 'L');


//				$this->_pagehead($pdf, $object, 1, $outputlangs, $project, $projectdetails);
//				$pdf->SetFont('','', $default_font_size - 1);
//				$pdf->MultiCell(0, 3, '');		// Set interline to 3
//				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTextColor(153,0,0);
				// Heading
				// ------------------------------------------------------------
				$heading=strtoupper($outputlangs->transnoentities("ProgressReport"));
				$pdf->SetXY(10,96);
				$pdf->SetFont('', 'B', $default_font_size + 20);
				$pdf->MultiCell(190, 12, $heading, 0, 'C', 0);

				// Report Label
				$replabel=$outputlangs->transnoentities($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetXY(10,112);
				$pdf->SetFont('', 'BI', $default_font_size + 12);
				$pdf->MultiCell(190, 12, $replabel, 0, 'C', 0);

				// Report Title
				$reptitle=strtoupper($outputlangs->transnoentities($outputlangs->convToOutputCharset($projectdetails[title])));
				$pdf->SetXY(10,130);
				$pdf->SetFont('', 'B', $default_font_size + 20);
				$pdf->MultiCell(190, 12, $reptitle, 0, 'C', 0);

				// Project Number
				$projref=$outputlangs->transnoentities($outputlangs->convToOutputCharset($projectdetails[ref]));
				$pdf->SetXY(10,145);
				$pdf->SetFont('', 'BI', $default_font_size + 12);
				$pdf->MultiCell(190, 12, '('.$projref.')', 0, 'C', 0);

				// Customer
				$custname=$outputlangs->transnoentities($outputlangs->convToOutputCharset($projectdetails[custname]));
				$pdf->SetXY(10,160);
				$pdf->SetFont('', 'B', $default_font_size + 20);
				$pdf->MultiCell(190, 12, $custname, 0, 'C', 0);


				// Report Date
				$reportdate= 'Report Date: '.dol_print_date($object->date_report,'%d %B %Y') ;
				$pdf->SetXY(10,180);
				$pdf->SetFont('', 'B', $default_font_size + 12);
				$pdf->MultiCell(190, 12, $reportdate, 0, 'C', 0);


//$html = '<span style="text-align:center;font-weight:bold;font-size:80pt;">'.$heading.'</span>';
//$pdf->writeHTML($html, true, false, true, false, '');


				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs, $project, $projectdetails);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColor(0,0,0);
				$pdf->SetDrawColor(128,128,128);

				$tab_top = $this->marge_haute + 42;
				//$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?42:$this->marge_haute);
				$tab_top_newpage = $this->marge_haute + 44; // was 34
				$tab_height = 70;
				$tab_height_newpage = 200;

				$heightoftitleline = 10;
				$iniY = $tab_top + $heightoftitleline + 0;
				$otherY = $tab_top_newpage + $heightoftitleline + 0;
				$curY = $tab_top + $heightoftitleline + 1;
				$nexY = $tab_top + $heightoftitleline + 0;
				$r1 = 1;
				$r2 = 1;

				// Section 1
				$pdf->SetXY($this->posx_colno, $tab_top + 0);
				$pdf->SetFont('','B', $default_font_size + 2);
				$pdf->MultiCell($this->width_title, $this->height_title, $r1.'. '.$outputlangs->convToOutputCharset($object->sec1_title),'','L');
				// Description
				$curY=$pdf->GetY();
				$pdf->SetXY($this->posx_desc, $curY);
				$pdf->SetFont('','', $default_font_size + 0);
				$pdf->writeHTMLCell($this->width_desc, 3, $this->posx_desc-1, $curY, dol_htmlentitiesbr($object->sec1_description), 0, 1);
				$curY=$pdf->GetY();
				$r1++;

				// Section 2
				$pdf->SetXY($this->posx_colno, $curY + 10);
				$pdf->SetFont('','B', $default_font_size + 2);
				$pdf->MultiCell($this->width_title, $this->height_title, $r1.'. '.$outputlangs->convToOutputCharset($object->sec2_title),'','L');
				$tab_top_first_page = $pdf->GetY() + 0;
				$curY = $tab_top_first_page + $heightoftitleline + 0;
				$pdf->SetXY($this->posx_colno, $curY + 0);
				$nexY = $pdf->GetY();

				// Find last printable line (not a child nor amortised)
				for ($i = 0 ; $i < $numlines ; $i++)
				{
					$pkt_parent	= ($object->lines[$i]->fk_parent_line?$object->lines[$i]->fk_parent_line:0);
					$pkt_direct_amortised	= $object->lines[$i]->direct_amortised;

					if ($pkt_parent >0 || $pkt_direct_amortised == 1) continue;
					$nblignes = $i + 1; // last line that meets condition.
				}

				// Loop on each line
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					// Description of line
					$pkt_parent	= ($object->lines[$i]->fk_parent_line?$object->lines[$i]->fk_parent_line:0);
					$pkt_direct_amortised	= $object->lines[$i]->direct_amortised;
					if ($pkt_parent >0 || $pkt_direct_amortised == 1) continue; // jump over any non-printable lines

					$pktid					= $object->lines[$i]->rowid;
					$pkt_service			= $object->lines[$i]->service;
					$pkt_title				= $object->lines[$i]->label;
					$pkt_desc				= $object->lines[$i]->description;
					$pkt_qty				= number_format($object->lines[$i]->qty,1);
					$pkt_discounted_qty		= number_format($object->lines[$i]->discounted_qty,2);
					$pkt_rang				= $object->lines[$i]->rang;
					$pkt_billable			= $object->lines[$i]->billable;
					$pkt_work_type			= $object->lines[$i]->work_type;
					$pkt_status				= $object->lines[$i]->status;	

					$datestart=dol_print_date($object->lines[$i]->date_start,'day');
					$dateend=dol_print_date($object->lines[$i]->date_end,'day');

					$sql = 'SELECT SUM(wrd.qty) as family_total, SUM(wrd.discounted_qty) as family_adjusted';
					$sql.= ' FROM '.MAIN_DB_PREFIX.'wip_reportdet as wrd';
					$sql.= ' WHERE wrd.rowid = '.$pktid.' OR wrd.fk_parent_line = '.$pktid;

					$resql = $this->db->query($sql);
					if (! $resql) dol_print_error($this->db);

					$res = $this->db->fetch_object($resql);

					$family_adjusted		= $res->family_adjusted;
					$family_amortised		= ($total_direct_adjusted == 0 ? 0 : $family_adjusted * $total_amortised_adjusted / $total_direct_adjusted);
					$family_total			= ($family_adjusted + $family_amortised);

					$this->db->free($resql);

					$curY = $nexY;
//					$curY=$pdf->GetY();
					$pdf->SetFont('','', $default_font_size - 0);   // Into loop to work with multipage
					$pdf->SetTextColor(0,0,0);
					$pdf->SetDrawColor(128,128,128);

					$pdf->setTopMargin($tab_top_newpage);
					$pdf->setPageOrientation('', 1, $heightforfooter+$heightforfreetext+$heightforinfotot);	// The only function to edit the bottom margin of current page to set it.
					$pageposbefore=$pdf->getPage();

					$showpricebeforepagebreak=1;

					$pdf->startTransaction();
					// ***********************************************************
					// Title
					$pdf->SetXY($this->posx_colno, $curY + ($i==0?0:3));
					$pdf->SetFont('','B', $default_font_size + 1);
					$pdf->MultiCell($this->width_title, $this->height_title, $r1.'.'.$r2.' '.$outputlangs->convToOutputCharset($pkt_title),'TB','L');
					$pdf->SetXY($this->posx_hours, $curY + ($i==0?0:3));
					$pdf->MultiCell($this->width_hours, $this->height_title, number_format($family_total,1), 'TBL', 'R');
					$pdf->SetFont('','', $default_font_size + 0);
					// Description
					$curY=$pdf->GetY();
					$pdf->SetXY($this->posx_desc, $curY);
					$pdf->MultiCell($this->width_desc, 2, dol_string_nohtmltag($pkt_desc,0), 0, 'L');
					// Total
					if ($i == ($nblignes-1))
					{
						$curY=$pdf->GetY();
						$pdf->SetXY($this->posx_colno, $curY + ($i==0?0:3));
						$pdf->SetFont('','B', $default_font_size + 1);
						$pdf->MultiCell($this->width_title, $this->height_title, 'Total Hours','TB','L');
						$pdf->SetXY($this->posx_hours, $curY + ($i==0?0:3));
						$pdf->MultiCell($this->width_hours, $this->height_title, number_format($total_hours_adjusted,1), 'TBL', 'R');
						$posyaftertotal=$pdf->GetY();
					}
					// ***********************************************************
					$pageposafter=$pdf->getPage();
					if ($pageposafter > $pageposbefore)	// There is a pagebreak
					{
						$pdf->rollbackTransaction(true);
						$pageposafter=$pageposbefore;
						$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.

						$pdf->AddPage('','',true);
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $project, $projectdetails);
						$pdf->setPage($pageposafter+1);
						$pdf->SetFont('','',  $default_font_size - 1);   // On repositionne la police par defaut
						$pdf->MultiCell(0, 3, '');		// Set interline to 3
						$pdf->SetTextColor(0,0,0);

						$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.
						//$curY = $otherY;
						$curY = $tab_top_newpage;

						// ***********************************************************
//						pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->posxtva-$curX,4,$curX,$curY,$hideref,$hidedesc);
						// Title
						$pdf->SetXY($this->posx_colno, $curY);
						$pdf->SetFont('','B', $default_font_size + 1);
						$pdf->MultiCell($this->width_title, $this->height_title, $r1.'.'.$r2.' '.$outputlangs->convToOutputCharset($pkt_title),'TB','L');
						$pdf->SetXY($this->posx_hours, $curY);
						$pdf->MultiCell($this->width_hours, $this->height_title, number_format($family_total,1), 'TBL', 'R');
						$pdf->SetFont('','', $default_font_size + 0);

/*						// Date
						$pdf->SetXY($this->posxdatestart, $curY);
						$pdf->MultiCell($this->posxdateend-$this->posxdatestart, 3, $datestart, 0, 'C');
						$pdf->SetXY($this->posxdateend, $curY);
						$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->posxdateend, 3, $dateend, 0, 'C');*/

						// Description
						$curY=$pdf->GetY();
						$pdf->SetXY($this->posx_desc, $curY);
						//$pdf->MultiCell($this->width_desc, 3, $outputlangs->convToOutputCharset($pkt_desc), 0, 'L');
						//$pdf->MultiCell($this->width_desc, 3, dol_htmlentitiesbr_decode($pkt_desc), 0, 'L');
						$pdf->MultiCell($this->width_desc, 2, dol_string_nohtmltag($pkt_desc,0), 0, 'L');
						// Total
						if ($i == ($nblignes-1))
						{
							$curY=$pdf->GetY();
							$pdf->SetXY($this->posx_colno, $curY + ($i==0?0:3));
							$pdf->SetFont('','B', $default_font_size + 1);
							$pdf->MultiCell($this->width_title, $this->height_title, 'Total Hours','TB','L');
							$pdf->SetXY($this->posx_hours, $curY + ($i==0?0:3));
							$pdf->MultiCell($this->width_hours, $this->height_title, number_format($total_hours_adjusted,1), 'TBL', 'R');
							$posyaftertotal=$pdf->GetY();
						}
						// ***********************************************************
						$pageposafter=$pdf->getPage();
						$posyafter=$pdf->GetY();
/*
						if ($posyafter > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforinfotot)))	// There is no space left for total+free text
						{
							if ($i == ($nblignes-1))	// No more lines, and no space left to show total, so we create a new page
							{
								$pdf->AddPage('','',true);
								if (! empty($tplidx)) $pdf->useTemplate($tplidx);
								if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
								$pdf->setPage($pageposafter+1);
							}
						}
						else
						{
							// We found a page break
							$showpricebeforepagebreak=0;
						}
*/
					}
					else	// No pagebreak
					{
						$pdf->commitTransaction();
					}
					$posYAfterDescription=$pdf->GetY();

					$nexY = $pdf->GetY();
					$pageposafter=$pdf->getPage();

					$pdf->setPage($pageposbefore);
					$pdf->setTopMargin($this->marge_haute);
					$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

					// We suppose that a too long description is moved completely on next page
					if ($pageposafter > $pageposbefore) {
						$pdf->setPage($pageposafter);
						$curY = $tab_top_newpage + $heightoftitleline + 1;
					}

					$nexY+=2;	// Pass space between lines

					// Detect if some page were added automatically and output _tableau for past pages
					while ($pagenb < $pageposafter)
					{
						$pdf->setPage($pagenb);
						if ($pagenb == 2)
						{
							$this->_tableau($pdf, $tab_top_first_page , $this->page_hauteur - $tab_top_first_page - $heightforfooter, $heightoftitleline, 0, $outputlangs, 0, 1);
						}
						elseif ($pagenb > 2)
						{
							$this->_tableau($pdf, $tab_top_newpage-$heightoftitleline, $this->page_hauteur - $tab_top_newpage + $heightoftitleline - $heightforfooter, $heightoftitleline, 0, $outputlangs, 1, 1);
						}
						$this->_pagefoot($pdf,$object,$outputlangs,$projref);
						$pagenb++;
						$pdf->setPage($pagenb);
						$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $project, $projectdetails);
					}

/*
					if ($pdf->getPage() == 1) $pageheight = $tab_top + $tab_height - $heightforfooter;
					else $pageheight = $tab_top_newpage + $tab_height_newpage - $heightforfooter;
//					   	$posyafter=$pdf->GetY();
//						if ($posyafter > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforinfotot)))	// There is no space left for total+free text
*/
 
//					if (isset($object->lines[$i+1]->pagebreak) && $object->lines[$i+1]->pagebreak)
					if ($curY > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforinfotot) - 7))	// There is a pagebreak
					{
						if ($pagenb == 2)
						{
							$this->_tableau($pdf, $tab_top_first_page , $this->page_hauteur - $tab_top_first_page - $heightforfooter, $heightoftitleline, 0, $outputlangs, 0, 1);
						}
						elseif ($pagenb > 2)
						{
							$this->_tableau($pdf, $tab_top_newpage-$heightoftitleline, $this->page_hauteur - $tab_top_newpage - $heightforfooter + $heightoftitleline, 0, $outputlangs, 1, 1);
						}
						$this->_pagefoot($pdf,$object,$outputlangs,$projref);
						// New page
						$pdf->AddPage();
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						$pagenb++;
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $project, $projectdetails);
					}
					$r2++;
				}	// End Loop on each lines

				// Show square
				if ($pagenb == 2)
				{
					$table_height = $heightoftitleline + $posyaftertotal - $iniY;
					// $table_height = $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter
					$this->_tableau($pdf, $tab_top_first_page, $table_height, $heightoftitleline, 0, $outputlangs, 0, 0);
				}
				elseif ($pagenb > 2)
				{
					$table_height = $heightoftitleline + $posyaftertotal - $tab_top_newpage;
					// $table_height = $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter
					$this->_tableau($pdf, $tab_top_newpage-$heightoftitleline, $table_height, $heightoftitleline, 0, $outputlangs, 1, 0);
				}
				$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				$nexY = $posyaftertotal + 0;

				// Loop on sections
				for ($i = 3 ; $i < 8 ; $i++)
				{
					switch ($i) {
						case 3:
							if (empty($object->sec3_title) || empty($object->sec3_description)) continue 2;
							$sec_title		 = $object->sec3_title;
							$sec_description = $object->sec3_description;
							$r1++;
							break;
						case 4:
							if (empty($object->sec4_title) || empty($object->sec4_description)) continue 2;
							$sec_title		 = $object->sec4_title;
							$sec_description = $object->sec4_description;
							$r1++;
							break;
						case 5:
							if (empty($object->sec5_title) || empty($object->sec5_description)) continue 2;
							$sec_title		 = $object->sec5_title;
							$sec_description = $object->sec5_description;
							$r1++;
							break;
						case 6:
							if (empty($object->sec6_title) || empty($object->sec6_description)) continue 2;
							$sec_title		 = $object->sec6_title;
							$sec_description = $object->sec6_description;
							$r1++;
							break;
						case 7:
							if (empty($object->sec7_title) || empty($object->sec7_description)) continue 2;
							$sec_title		 = $object->sec7_title;
							$sec_description = $object->sec7_description;
							$r1++;
							break;
						/*default: // Case 5 for testing
							if (empty($object->sec5_title) || empty($object->sec5_description)) continue 2;
							$sec_title		 = $object->sec5_title;
							$sec_description = $object->sec5_description;
							$r1++;
							break;*/
					}
					// Sections
					$curY = $nexY;
					$pdf->SetFont('','', $default_font_size - 0);   // Into loop to work with multipage
					$pdf->SetTextColor(0,0,0);
					$pdf->SetDrawColor(128,128,128);

					$pdf->setTopMargin($tab_top_newpage);
					$pdf->setPageOrientation('', 1, $heightforfooter+$heightforfreetext+$heightforinfotot);	// The only function to edit the bottom margin of current page to set it.
					$pageposbefore=$pdf->getPage();

					$pdf->startTransaction();
					// ***********************************************************
					// Title
					$pdf->SetXY($this->posx_colno, $curY + 10);
					$pdf->SetFont('','B', $default_font_size + 2);
					$pdf->writeHTMLCell($this->width_title, 6, $this->posx_colno, $curY + 10, $r1.'. '.$outputlangs->convToOutputCharset($sec_title), 0, 1);
					// Description
					$curY=$pdf->GetY();
					$pdf->SetXY($this->posx_desc, $curY);
					$pdf->SetFont('','', $default_font_size + 0);
					$pdf->writeHTMLCell($this->width_desc, 3, $this->posx_colno, $curY, dol_htmlentitiesbr($sec_description), 0, 1);
					$curY=$pdf->GetY();
					// ***********************************************************
					$pageposafter=$pdf->getPage();
					if ($pageposafter > $pageposbefore)	// There is a pagebreak
					{
						$pdf->rollbackTransaction(true);
						$pageposafter=$pageposbefore;
						$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.

						$pdf->AddPage('','',true);
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $project, $projectdetails);
						$pdf->setPage($pageposafter+1);
						$pdf->SetFont('','',  $default_font_size - 1);   // On repositionne la police par defaut
						$pdf->MultiCell(0, 3, '');		// Set interline to 3
						$pdf->SetTextColor(0,0,0);

						$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.
						$curY = $tab_top_newpage;

						// ***********************************************************
						// Title
						$pdf->SetXY($this->posx_colno, $curY + 10);
						$pdf->SetFont('','B', $default_font_size + 2);
						$pdf->writeHTMLCell($this->width_title, 6, $this->posx_colno, $curY + 10, $r1.'. '.$outputlangs->convToOutputCharset($sec_title), 0, 1);
						// Description
						$curY=$pdf->GetY();
						$pdf->SetXY($this->posx_desc, $curY);
						$pdf->SetFont('','', $default_font_size + 0);
						$pdf->writeHTMLCell($this->width_desc, 3, $this->posx_desc, $curY, dol_htmlentitiesbr($sec_description), 0, 1);
						$curY=$pdf->GetY();
						// ***********************************************************

						$pageposafter=$pdf->getPage();
						$posyafter=$pdf->GetY();
					}
					else	// No pagebreak
					{
						$pdf->commitTransaction();
					}
					$posYAfterDescription=$pdf->GetY();

					$nexY = $pdf->GetY();
					$pageposafter=$pdf->getPage();

					$pdf->setPage($pageposbefore);
					$pdf->setTopMargin($this->marge_haute);
					$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

					// We suppose that a too long description is moved completely on next page
					if ($pageposafter > $pageposbefore) {
						$pdf->setPage($pageposafter);
						$curY = $tab_top_newpage + $heightoftitleline + 1;
					}

					$nexY+=2;	// Pass space between lines

					// Detect if some page were added automatically and output _tableau for past pages
					while ($pagenb < $pageposafter)
					{
						$pdf->setPage($pagenb);
/*						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top_first_page , $this->page_hauteur - $tab_top_first_page - $heightforfooter, $heightoftitleline, 0, $outputlangs, 0, 1);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, $heightoftitleline, 0, $outputlangs, 1, 1);
						}*/
						$this->_pagefoot($pdf,$object,$outputlangs,$projref);
						$pagenb++;
						$pdf->setPage($pagenb);
						$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $project, $projectdetails);
					}

/*					if ($curY > ($this->page_hauteur - ($heightforfooter+$heightforfreetext+$heightforinfotot) - 7))	// There is a pagebreak
					{
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top_first_page , $this->page_hauteur - $tab_top_first_page - $heightforfooter, $heightoftitleline, 0, $outputlangs, 0, 1);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1);
						}
						$this->_pagefoot($pdf,$object,$outputlangs,1);
						// New page
						$pdf->AddPage();
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						$pagenb++;
						if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $project, $projectdetails);
					}*/
					//$r2++;

				} // End of section loop


				/*
				 * Footer
				 */
				$this->_pagefoot($pdf, $object, $outputlangs,$projref);
				if (method_exists($pdf, 'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file, 'F');

				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file, 'object'=>$object, 'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation', $parameters, $this, $action);	// Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
					@chmod($file, octdec($conf->global->MAIN_UMASK));

				$this->result = array('fullpath'=>$file);

				return 1;   // No error
			}
			else
			{
				$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->transnoentities("ErrorConstantNotDefined","PROJECT_OUTPUTDIR");
			return 0;
		}
	}


	/**
	 *   Show table for lines
	 *
	 *   @param		TCPDF		$pdf	 		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @return	void
	 */
	function _tableau(&$pdf, $tab_top, $tab_height, $heightoftitleline, $nexY, $outputlangs, $hidetop=0, $hidebottom=0)
	{
		global $conf, $mysoc;

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$pdf->SetDrawColor(128,128,128);

		// Draw rectangle for all table (title + lines). Rectangle takes a length in 3rd and 4th parameters
		$this->printRect($pdf,$this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height);

		// Line takes a position y in 3rd param
		$pdf->line($this->marge_gauche, $tab_top+$heightoftitleline, $this->page_largeur-$this->marge_droite, $tab_top+$heightoftitleline);

		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','', $default_font_size);

		$pdf->SetXY($this->posx_desc, $tab_top+1);
		$pdf->MultiCell($this->posxworkload-$this->posx_desc, 3, $outputlangs->transnoentities("Description"), 0, 'L');

		$pdf->SetXY($this->posx_hours, $tab_top+1);
		$pdf->MultiCell($this->width_hours, 3, $outputlangs->transnoentities("Hours"), 0, 'R');
	}

	/**
	 *  Show top header of page.
	 *
	 *  @param	TCPDF		$pdf	 		Object PDF
	 *  @param  Object		$object	 	Object to show
	 *  @param  int			$showaddress	0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $project, $projectdetails)
	{
		global $langs, $conf, $mysoc;

		// Define logo boxes
		$complogomaxwidth	= 60; 	// logo is simply a set-width rather than variable an unknown logo. Finch logo is 800 x 260. Therefore, 60 wide results in a 20 high logo.
		$complogomaxheight	= 20;
		$headerboxheight	= $complogomaxheight + 2;

		// Company logo
//		$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
		$logo = DOL_DOCUMENT_ROOT.'/custom/wip/img/company_logo.png';
		if ($this->emetteur->logo && is_readable($logo))
		{
			include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
			$dims=dol_getImageSize($logo, false);
			if ($dims['height'])
			{
				$height=$complogomaxheight;
				$width=round($height*$dims['width']/$dims['height']);
				if ($width > $complogomaxwidth)
				{
					$height=round($height*$complogomaxwidth/$width);
					$width=round($height*$dims['width']/$dims['height']);
				}
			}
		}

		// Define position of columns and rows
		//$posx				= $this->page_largeur - $this->marge_droite - 100;
		$posx				= $this->marge_gauche;
		$posy				= $this->marge_haute;

		$col_1_2_widths		= $complogomaxwidth + 0;	// make left and right columns same width
		$this->widthcol1	= $col_1_2_widths;
		$this->widthcol3	= $col_1_2_widths;
		$this->widthcol2	= $this->page_largeur - $this->marge_gauche - $this->marge_droite - $this->widthcol1 - $this->widthcol3;

		$this->heightrow0	= round($headerboxheight/2);
		$this->heightrow1	= $headerboxheight;
		$this->heightrow2	= 10;
		$this->heightrow3	= 12;
		$this->heightrow4	= 12;
		$this->heightrow5	= 12;
		$this->heightrow6	= 24;

		$this->posxcol1		= $this->marge_gauche;
		$this->posxcol2		= $this->posxcol1 + $this->widthcol1;
		$this->posxcol3		= $this->posxcol2 + $this->widthcol2;

		$this->posyrow0		= $posy;
		$this->posyrow1		= $posy + $this->heightrow0;
		$this->posyrow2		= $this->posyrow0 + $this->heightrow1;
		$this->posyrow3		= $this->posyrow2 + $this->heightrow2;
		$this->posyrow4		= $this->posyrow3 + $this->heightrow3;
		$this->posyrow5		= $this->posyrow4 + $this->heightrow4;
		$this->posyrow6		= $this->posyrow5 + $this->heightrow5;

		// Start other setup
		$default_font_size = pdf_getPDFFontSize($outputlangs);

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		// Show Draft Watermark
		if($object->status==0 && (! empty($conf->global->COMMANDE_DRAFT_WATERMARK)) )
		{
			pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->COMMANDE_DRAFT_WATERMARK);
		}

		/*
		 * Print content
		 */
		// Row 1 || Column 1
		// ------------------------------------------------------------
		// Logo Box
		$pdf->SetXY($this->posxcol1,$this->posyrow0);
		$pdf->SetTextColor(0,0,0);
		$pdf->SetFont('','', $default_font_size + 0);

		// Logo
		if ($logo)
		//if ($this->emetteur->logo)
		{
			if (is_readable($logo))
			{
				$pdf->Image($logo, $this->posxcol1 +1, $this->posyrow0 + 1	, $complogomaxwidth - 2, 0);	// height=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $default_font_size - 2);
				$pdf->MultiCell($this->widthcol1, $this->heightrow1, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell($this->widthcol1, $this->heightrow1, $outputlangs->transnoentities("ErrorGoToModuleSetup"), 0, 'L');
			}
		}
		else
		{
			$text=$this->emetteur->name;
			$pdf->MultiCell($this->widthcol1, $this->heightrow1, $outputlangs->convToOutputCharset($text), 0, 'L');
		}
		$pdf->SetXY($this->posxcol1,$this->posyrow0);
		$pdf->MultiCell($this->widthcol1, $this->heightrow1, "", 1, 'L',0);

		// Row 1 || Column 2
		// ------------------------------------------------------------
		// Heading
		$pdf->SetFont('', 'B', $default_font_size + 12);
		$pdf->SetXY($this->posxcol2,$this->posyrow0);
		$heading=$outputlangs->transnoentities("ProgressReport");
		$pdf->MultiCell($this->widthcol2, $this->heightrow1, '', 1, 'C', 0);
		$pdf->SetXY($this->posxcol2,$this->posyrow0 + 0);
		$pdf->MultiCell($this->widthcol2, $this->heightrow1 - 0, $heading, 0, 'C', 0);

		// Row 1 || Column 3
		// ------------------------------------------------------------
		// Vehicle Logo & Frame
		$pdf->SetXY($this->posxcol3,$this->posyrow0);

		$vehlogomaxheight = $this->heightrow0;
		$vehlogomaxwidth = $this->widthcol3;

		if ($projectdetails[make_logo])
		{
			$vehlogo = DOL_DOCUMENT_ROOT.'/custom/autos/img/'.$projectdetails[make_logo];
			if (is_readable($vehlogo))
			{
				include_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
				$tmp=dol_getImageSize($vehlogo, false);
				if ($tmp['height'])
				{
					$height=$vehlogomaxheight;
					$width=round($height*$tmp['width']/$tmp['height']);
					if ($width > $vehlogomaxwidth)
					{
						$height=round($height*$vehlogomaxwidth/$width);
						$width=round($height*$tmp['width']/$tmp['height']);
					}
				}

				$leftpad = round(($vehlogomaxwidth-$width)/2);
				$toppad  = round(($vehlogomaxheight-$height)/2) + 1;

				// Show vehicle logo frame
				if ($projectdetails[rgb][r] || $projectdetails[rgb][g] || $projectdetails[rgb][b])
				{
					$pdf->SetFillColor($projectdetails[rgb][r], $projectdetails[rgb][g], $projectdetails[rgb][b]);
				}
				else
				{
					$pdf->SetFillColor(255,255,255);
				}
				$pdf->MultiCell($this->widthcol3, $this->heightrow1 + $this->heightrow2, "", 1, 'R', 1);
				// Show vehicle logo
				$pdf->Image($vehlogo, $this->posxcol3 + $leftpad, $this->posyrow0 + $toppad, 0, $height);	// width=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $default_font_size - 2);
				$pdf->MultiCell(100, 3, $langs->transnoentities("ErrorLogoFileNotFound",$vehlogo), 0, 'L');
				$pdf->MultiCell(100, 3, $langs->transnoentities("ErrorGoToModuleSetup"), 0, 'L');
			}
		}
		else $pdf->MultiCell(100, 4, $outputlangs->convToOutputCharset($projectdetails[makename]), 0, 'L');

		// Project Number
		$pdf->SetFont('', 'B', $default_font_size + 10);
		$pdf->SetXY($this->posxcol3,$this->posyrow1 + 0);
		$projref=$outputlangs->transnoentities($outputlangs->convToOutputCharset($projectdetails[ref]));
		$pdf->MultiCell($this->widthcol3, $this->heightrow1, $projref, 0, 'C', 0);

		// Row 2 || Column 1
		// ------------------------------------------------------------
		// Date Box
		$pdf->SetFont('', 'B', $default_font_size + 4);
		$pdf->SetXY($this->posxcol1,$this->posyrow2);
		$reportdate= /*'Date: '.*/dol_print_date($object->date_report,'%d %B %Y') ;
		$pdf->MultiCell($this->widthcol1, $this->heightrow2, '', 1, 'C', 0);
		$pdf->SetXY($this->posxcol1,$this->posyrow2 + 2);
		$pdf->MultiCell($this->widthcol1, $this->heightrow2 - 2, $reportdate, 0, 'C', 0);

		// Row 2 || Column 2
		// ------------------------------------------------------------
		// Report Label
		$pdf->SetFont('', 'B', $default_font_size + 6);
		$pdf->SetXY($this->posxcol2,$this->posyrow2);
		$replabel=$outputlangs->transnoentities($outputlangs->convToOutputCharset($object->ref));
		$pdf->MultiCell($this->widthcol2, $this->heightrow2, '', 1, 'C', 0);
		$pdf->SetXY($this->posxcol2,$this->posyrow2 + 1);
		$pdf->MultiCell($this->widthcol2, $this->heightrow2 - 1, $replabel, 0, 'C', 0);

		// Row 2 || Column 3
		// ------------------------------------------------------------
		// Customer Vehicle
		$pdf->SetFont('', 'B', $default_font_size + 2);
		$pdf->SetXY($this->posxcol3,$this->posyrow2 - 2);
		$reptitle=$outputlangs->transnoentities($outputlangs->convToOutputCharset($projectdetails[title]));
		$pdf->MultiCell($this->widthcol3, $this->heightrow2 + 2, $reptitle, 0, 'C', 0);

	}

	/**
	 *	Show footer of page. Need this->emetteur object
	 *
	 * 	@param	TCPDF		$pdf	 			PDF
	 *	@param	Object		$object				Object to show
	 *	@param	Translate	$outputlangs		Object lang for output
	 *	@param	int			$hidefreetext		1=Hide free text
	 *	@return	int								Return height of bottom margin including footer text
	 */
	function _pagefoot(&$pdf, $object, $outputlangs, $projref)
	{
		global $conf;
//		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
//		$showdetails=3;
//		return pdf_pagefoot($pdf,$outputlangs,'XXXXXXPROJECT_FREE_TEXT',$this->emetteur,$this->marge_basse,$this->marge_gauche,$this->page_hauteur,$object,$showdetails,$hidefreetext);

		$line='';
		$dims=$pdf->getPageDimensions();

		$pdf->SetFont('', '', 7);
		$pdf->SetDrawColor(224, 224, 224);

		$marginwithfooter=$this->marge_basse  + (! empty($line1)?3:0) + (! empty($line2)?3:0) + (! empty($line3)?3:0) + (! empty($line4)?3:0);
		$posy=$marginwithfooter+0;

		$pdf->SetY(-$posy);
		$pdf->line($dims['lm'], $dims['hk']-$posy, $dims['wk']-$dims['rm'], $dims['hk']-$posy);
		$posy--;


		// Project Number
		$pdf->SetXY($dims['lm'], -$posy);
		$pdf->MultiCell(25, 2, $projref, 0, 'L', 0);

		// Report Label
		$replabel=$outputlangs->transnoentities($outputlangs->convToOutputCharset($object->ref));
		$pdf->SetXY($dims['lm'], -$posy);
		$pdf->MultiCell($dims['wk']-$dims['rm']-$dims['lm'], 2, $replabel, 0, 'C', 0);

		// Show page nb only on iso languages (so default Helvetica font)
		if (strtolower(pdf_getPDFFont($outputlangs)) == 'helvetica')
		{
			$pdf->SetXY($dims['wk']-$dims['rm']-25, -$posy);
			$pdf->MultiCell(25, 2, 'Page '.$pdf->PageNo().'/'.$pdf->getAliasNbPages(), 0, 'R', 0);
		}

		return $marginwithfooter;
	}

}
