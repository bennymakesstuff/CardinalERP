<?php
/* Copyright (C) 2003		Rodolphe Quiedeville		<rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010	Laurent Destailleur			<eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin				<regis.houssin@capnetworks.com>
 * Copyright (C) 2008		Raphael Bertrand (Resultic)	<raphael.bertrand@resultic.fr>
 * Copyright (C) 2011		Fabrice CHERRIER
 * Copyright (C) 2013-2017  Philippe Grand	            <philippe.grand@atoo-net.com>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/core/modules/contract/doc/pdf_ultimatecontract.modules.php
 *	\ingroup    ficheinter
 *	\brief      Fichier de la classe permettant de generer les contrats au modele Ultimatecontract
 */
require_once DOL_DOCUMENT_ROOT.'/core/modules/contract/modules_contract.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
dol_include_once('/ultimatepdf/lib/ultimatepdf.lib.php');


/**
 *	Class to build contracts documents with model ultimatecontract
 */
class pdf_ultimatecontract extends ModelePDFContract
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
	 * e.g.: PHP ≥ 5.3 = array(5, 3)
     */
	public $phpmin = array(5, 2); 
	
	/**
     * Dolibarr version of the loaded document
     * @public string
     */
	public $version = 'dolibarr';

	public $page_largeur;
	public $page_hauteur;
	public $format;
	public $marge_gauche;
	public $marge_droite;
	public $marge_haute;
	public $marge_basse;
	public $style;
	public $logo_height;

	/**
	* Issuer
	* @var Societe
	*/
	public $emetteur;
	/**
	* Recipient
	* @var Societe
	*/
	public $recipient;
	
	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function __construct($db)
	{
		global $conf,$langs,$mysoc;

		$this->db = $db;
		$this->name = 'ultimatecontract';
		$this->description = $langs->trans("StandardContractsTemplate");

		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->ULTIMATE_PDF_MARGIN_LEFT)?$conf->global->ULTIMATE_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->ULTIMATE_PDF_MARGIN_RIGHT)?$conf->global->ULTIMATE_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->ULTIMATE_PDF_MARGIN_TOP)?$conf->global->ULTIMATE_PDF_MARGIN_TOP:10;
		$this->marge_basse =isset($conf->global->ULTIMATE_PDF_MARGIN_BOTTOM)?$conf->global->ULTIMATE_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_draft_watermark = 1;		   // Support add of a watermark on drafts
		
		$bordercolor = array('0','63','127');
		$dashdotted = isset($conf->global->ULTIMATE_DASH_DOTTED)?$conf->global->ULTIMATE_DASH_DOTTED:'';
		if(!empty($conf->global->ULTIMATE_BORDERCOLOR_COLOR))
		{
			$bordercolor = html2rgb($conf->global->ULTIMATE_BORDERCOLOR_COLOR);
			if(!empty($conf->global->ULTIMATE_DASH_DOTTED))
			{
				$dashdotted = $conf->global->ULTIMATE_DASH_DOTTED;
			}
			$this->style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => $dashdotted , 'color' => $bordercolor);
		}
		
		// Get source company
		$this->emetteur=$mysoc;
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if not defined

		// Define position of columns
		if (! empty($conf->global->ULTIMATE_CONTRACT_WITH_LINE_NUMBER))
		{
			$this->posxnumber=$this->marge_gauche+1;
			$this->number_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH)?10:$conf->global->ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH;
		}
		else
		{
			$this->posxnumber=0;
			$this->number_width = 0;
		}
		$this->posxdesc=$this->marge_gauche+$this->number_width;
		$this->posxdate_ouverture_prevue=122;
		$this->posxdate_fin_validite=142;
		$this->posxdate_ouverture=162;
		$this->posxdate_cloture=182;
	}

	/**
     *  Function to build pdf onto disk
     *
     *  @param		CommonObject	$object				Id of object to generate
     *  @param		object			$outputlangs		Lang output object
     *  @param		string			$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int				$hidedetails		Do not show line details
     *  @param		int				$hidedesc			Do not show desc
     *  @param		int				$hideref			Do not show ref
     *  @return     int             					1=OK, 0=KO
	 */
	function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
	{
		global $user,$langs,$conf,$mysoc,$db,$hookmanager;
		
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}

		if (! is_object($outputlangs)) $outputlangs=$langs;
		// For backward compatibility with FPDF, force output charset to ISO, because FPDF expect text to be encoded in ISO
		if (! empty($conf->global->MAIN_USE_FPDF)) $outputlangs->charset_output='ISO-8859-1';

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("contracts");
		$outputlangs->load("ultimatepdf@ultimatepdf");

		if ($conf->contrat->dir_output)
		{
            $object->fetch_thirdparty();

			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->contrat->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->contrat->dir_output . "/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$outputlangs->trans("ErrorCanNotCreateDir",$dir);
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
				$reshook=$hookmanager->executeHooks('beforePDFCreation',$parameters,$object,$action);    // Note that $action and $object may have been modified by some hooks
				
				// Create pdf instance
                $pdf=pdf_getInstance($this->format);
                $default_font_size = pdf_getPDFFontSize($outputlangs);	// Must be after pdf_getInstance
                $heightforinfotot = 50;	// Height reserved to output the info and total part
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:12);	// Height reserved to output the free text on last page
	            $heightforfooter = $this->marge_basse + 12;	// Height reserved to output the footer (value include bottom margin)
                $pdf->SetAutoPageBreak(1,0);

                if (class_exists('TCPDF'))
                {
                    $pdf->setPrintHeader(false);
                    $pdf->setPrintFooter(false);
                }
                $pdf->SetFont(pdf_getPDFFont($outputlangs));
                // Set path to the background PDF File
                if (empty($conf->global->MAIN_DISABLE_FPDI) && ! empty($conf->global->MAIN_ADD_PDF_BACKGROUND))
                {
                    $pagecount = $pdf->setSourceFile($conf->mycompany->dir_output.'/'.$conf->global->MAIN_ADD_PDF_BACKGROUND);
                    $tplidx = $pdf->importPage(1);
                }

				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("ContractCard"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("ContractCard"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColorArray($textcolor);
				
				//catch logo height
				$logo_height=max(pdf_getUltimateHeightForLogo($logo),30);
				$delta=45-$logo_height;

				//Set $hautcadre
				if (($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','SALESREPSIGN')) && ($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1) || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','SALESREPSIGN')) && ($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1)  || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','BILLING')) && ($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1) || (! empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS) && !empty($object->note_public)))
				{
					$hautcadre=68;
				}
				else
				{
					$hautcadre=52;
				}
				$tab_top = $this->marge_haute+$logo_height+$hautcadre+$delta+15;
				
				$tab_top_newpage = (empty($conf->global->ULTIMATE_CONTRACT_PDF_DONOTREPEAT_HEAD)?$this->marge_haute+$logo_height+$delta:10);
				$tab_height = 130;
				$tab_height_newpage = 150;
				$tab_width = $this->page_largeur-$this->marge_gauche-$this->marge_droite;

				// Affiche notes
				$notetoshow=empty($object->note_public)?'':$object->note_public;
				if (! empty($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_CONTRACT_NOTE))
				{
					// Get first sale rep
					if (is_object($object->thirdparty))
					{
						$salereparray=$object->thirdparty->getSalesRepresentatives($user);
						$salerepobj=new User($this->db);
						$salerepobj->fetch($salereparray[0]['id']);
						if (! empty($salerepobj->signature)) $notetoshow=dol_concatdesc($notetoshow, $salerepobj->signature);
					}
				}
				if ($notetoshow && empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS))
				{
					$tab_top = $this->marge_haute+$logo_height+$hautcadre+$delta+15;

					$pdf->SetFont('','', $default_font_size - 1);
					$pdf->writeHTMLCell($tab_width, 3, $this->posxdesc-1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $tab_width, $height_note+1, 2, $round_corner = '1111', 'S', $this->style, $fill_color=array());

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+4;
				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 9;

				$pdf->SetXY($this->marge_gauche, $tab_top);

				$pdf->MultiCell(0, 2, '');		// Set interline to 3. Then writeMultiCell must use 3 also.

				$nblines = count($object->lines);

				// Loop on each lines
				$line_number=1;
				for ($i = 0; $i < $nblines; $i++)
				{
					$objectligne = $object->lines[$i];

					$valide = $objectligne->id ? $objectligne->fetch($objectligne->id) : 0;

					if ($valide > 0 || $object->specimen)
					{
						$curX = $this->posxdesc-1;
						$curY = $nexY;
						$pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
						$pdf->SetTextColorArray($textcolor);	

						$pdf->setTopMargin($tab_top_newpage);
						//If we aren't on last lines footer space needed is on $heightforfooter
						if ($i != $nblignes-1)
						{
							$bMargin=$heightforfooter;
						}
						else 
						{
							//We are on last item, need to check all footer (freetext, ...)
							$bMargin=$heightforfooter+$heightforfreetext+$heightforinfotot;
						}
						$pdf->setPageOrientation('', 1, $bMargin);	// The only function to edit the bottom margin of current page to set it.
						$pageposbefore=$pdf->getPage();
						
						// Description of intervention line
						$curX = $this->posxdesc;
						
						$pdf->startTransaction();
						
						// Description
						$txtpredefinedservice='';
                        $txtpredefinedservice = $objectligne->product_ref;
                        if ($objectligne->product_label)
                        {
                        	$txtpredefinedservice .= ' - ';
                        	$txtpredefinedservice .= $objectligne->product_label;
                        }
						$desc=dol_htmlentitiesbr($objectligne->desc, 1);
						$pdf->SetXY($curX, $curY);
						$pdf->writeHTMLCell($this->posxdate_ouverture_prevue-$this->posxdesc-0.8, 4, $curX, $curY, dol_concatdesc($txtpredefinedservice,$desc), 0, 1, 0);
						
						$pageposafter=$pdf->getPage();

						if ($pageposafter > $pageposbefore)	// There is a pagebreak
						{
							$pdf->rollbackTransaction(true);
							$pageposafter=$pageposbefore;

							$pdf->setPageOrientation('', 1, $bMargin);	// The only function to edit the bottom margin of current page to set it.
							$txtpredefinedservice = $objectligne->product_ref;
							if ($objectligne->product_label)
							{
								$txtpredefinedservice .= ' - ';
								$txtpredefinedservice .= $objectligne->product_label;
							}
							$desc=dol_htmlentitiesbr($objectligne->desc, 1);
							$pdf->SetXY($curX, $curY);
							$pdf->writeHTMLCell($this->posxdate_ouverture_prevue-$this->posxdesc-0.8, 4, $curX, $curY, dol_concatdesc($txtpredefinedservice,$desc), 0, 1, 0);
							$pageposafter=$pdf->getPage();
							$posyafter=$pdf->GetY();

							if ($posyafter > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
							{
								if ($i == ($nblines-1))	// No more lines, and no space left to show total, so we create a new page
								{
									$pdf->AddPage('','',true);
									if (! empty($tplidx)) $pdf->useTemplate($tplidx);
									if (empty($conf->global->ULTIMATE_CONTRACT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
									$pdf->setPage($pageposafter+1);
								}
							}
						}
						else	// No pagebreak
						{
							$pdf->commitTransaction();
						}

						$nexY = $pdf->GetY() + 2;
						$pageposafter=$pdf->getPage();
						$pdf->setPage($pageposbefore);
						$pdf->setTopMargin($this->marge_haute);
						$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

						// We suppose that a too long description is moved completely on next page
						if ($pageposafter > $pageposbefore) 
						{
							$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
						}

						$pdf->SetFont('','', $default_font_size - 2);   // On repositionne la police par defaut
						
						if ($curY > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
						{
							$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
						}
						
						//Line numbering
						if (! empty($conf->global->ULTIMATE_CONTRACT_WITH_LINE_NUMBER))
						{
							if ($object->lines[$i]->product_type != 9)
							{
								if (array_key_exists($i,$object->lines))
								{
									$pdf->SetXY($this->posxnumber, $curY);
									$pdf->MultiCell($this->posxdesc-$this->posxnumber-0.8, 3, $line_number, 0, "C");
									$line_number++;
								}
							}
						}
						
						// Date ouverture prevue
						$pdf->SetXY($this->posxdate_ouverture_prevue-1, $curY);
						if ($objectligne->date_ouverture_prevue) 
						{
							$datei = dol_print_date($objectligne->date_ouverture_prevue,'day',false,$outputlangs,true);
						} 
						else 
						{
							$datei = $langs->trans("Unknown");
						}
						$pdf->MultiCell($this->posxdate_fin_validite-$this->posxdate_ouverture_prevue-0.8, 4, $datei, 0, 'C');
						
						// Date fin validite
						$pdf->SetXY($this->posxdate_fin_validite-1, $curY);
						if ($objectligne->date_fin_validite) 
						{
							$durationi = convertSecondToTime($objectligne->date_fin_validite - $objectligne->date_ouverture_prevue, 'allwithouthour');
							$datee = dol_print_date($objectligne->date_fin_validite,'day',false,$outputlangs,true);
						} 
						else 
						{
							$durationi = $langs->trans("Unknown");
							$datee = $langs->trans("Unknown");
						}
						$pdf->MultiCell($this->posxdate_fin_validite-$this->posxdate_ouverture_prevue-0.8, 4, $datee, 0, 'C');
						
						// Date ouverture 
						$pdf->SetXY($this->posxdate_ouverture-1, $curY);
						if ($objectligne->date_ouverture) 
						{
							$daters = dol_print_date($objectligne->date_ouverture,'day',false,$outputlangs,true);
						} 
						else 
						{
							$daters = $langs->trans("Unknown");
						}
						$pdf->MultiCell($this->posxdate_cloture-$this->posxdate_ouverture-0.8, 4, $daters, 0, 'C');
						
						// Date cloture 
						$pdf->SetXY($this->posxdate_cloture-1, $curY);
						if ($objectligne->date_cloture) 
						{
							$daters = dol_print_date($objectligne->date_cloture,'day',false,$outputlangs,true);
						} 
						else 
						{
							$daters = $langs->trans("Unknown");
						}
						$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->posxdate_cloture-0.8, 4, $daters, 0, 'C');
						
						// Add line
						if (! empty($conf->global->ULTIMATE_CONTRACT_PDF_DASH_BETWEEN_LINES) && $i < ($nblines - 1))
						{
							$pdf->setPage($pageposafter);
							$pdf->SetLineStyle(array('dash'=>'1,1','color'=>array(210,210,210)));
							$pdf->line($this->marge_gauche, $nexY+1, $this->page_largeur - $this->marge_droite, $nexY+1);
							$pdf->SetLineStyle(array('dash'=>0));
						}
						
						$nexY+=4;    // Passe espace entre les lignes
						
						// Detect if some page were added automatically and output _tableau for past pages
						while ($pagenb < $pageposafter)
						{
							$pdf->setPage($pagenb);
							if ($pagenb == 1)
							{
								$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1);
							}
							else
							{
								$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1);
							}
							$this->_pagefoot($pdf,$object,$outputlangs,1);
							$pagenb++;
							$pdf->setPage($pagenb);
							$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
							if (empty($conf->global->ULTIMATE_CONTRACT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
						if (isset($object->lines[$i+1]->pagebreak) && $object->lines[$i+1]->pagebreak)
						{
							if ($pagenb == 1)
							{
								$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1);
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
							if (empty($conf->global->ULTIMATE_CONTRACT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
						}
					}
				}

				// Show square
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 0, 0);
					$bottomlasttab=$this->page_hauteur - $heightforfooter - $heightforfooter + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 1, 0);
					$bottomlasttab=$this->page_hauteur - $heightforfooter - $heightforfooter + 1;
				}
				
				// Affiche zone agreement
				$posy=$this->_agreement($pdf, $object, $posy, $outputlangs);
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// Add PDF asked to be merged
				if (!empty($conf->global->ULTIMATEPDF_GENERATE_CONTRACTS_WITH_MERGED_PDF))
				{
					dol_include_once ( '/ultimatepdf/class/contractmergedpdf.class.php' );
					
					$already_merged=array();

					if (! empty ( $object->id ) && !(in_array($object->id, $already_merged))) {
						
						// Find the desire PDF
						$filetomerge = new Contractmergedpdf($this->db);
						
						if ($conf->global->MAIN_MULTILANGS) {
							$filetomerge->fetch_by_contract($object->id, $outputlangs->defaultlang);
						} else {
							$filetomerge->fetch_by_contract($object->id);
						}
						
						$already_merged[]= $object->id;
						
						// If PDF is selected and file is not empty
						if (count($filetomerge->lines) > 0) {
							foreach ($filetomerge->lines as $linefile) {
								if (! empty ($linefile->id) && ! empty ($linefile->file_name)) {

									$filetomerge_dir = $conf->contrat->dir_output. '/' . dol_sanitizeFileName($object->ref);
									
									$infile = $filetomerge_dir . '/' . $linefile->file_name;
									dol_syslog(get_class ($this) . ':: $upload_dir=' . $filetomerge_dir, LOG_DEBUG);
									// If file really exists
									if (is_file ($infile)) {
											
										$count = $pdf->setSourceFile ($infile);
										// import all page
										for($i = 1; $i <= $count; $i ++) {
											// New page
											$pdf->AddPage ();
											$tplIdx = $pdf->importPage ( $i );
											$pdf->useTemplate ( $tplIdx, 0, 0, $this->page_largeur );
											if (method_exists ( $pdf, 'AliasNbPages' ))
												$pdf->AliasNbPages ();
										}
									}
								}
							}
						}
					}
				}

				$pdf->Close();

				$pdf->Output($file,'F');

				// Add pdfgeneration hook
				if (! is_object($hookmanager))
				{
					include_once DOL_DOCUMENT_ROOT.'/core/class/hookmanager.class.php';
					$hookmanager=new HookManager($this->db);
				}
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","CONTRACT_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}
	
	/**
	 *	Show good for agreement
	 *
	 *	@param	PDF			&$pdf           Object PDF
	 *  @param	Object		$object			Object to show
	 *	@param	int			$posy			Position depart
	 *	@param	Translate	$outputlangs	Objet langs
	 *	@return int							Position pour suite
	 */
	function _agreement(&$pdf, $object, $posy, $outputlangs)
	{
		global $conf,$langs;
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
		
		if (! empty($conf->global->ULTIMATE_DISPLAY_CONTRACT_AGREEMENT_BLOCK))
	    {
			$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:12);	// Height reserved to output the free text on last page
			$heightforfooter = $this->marge_basse + 12;	// Height reserved to output the footer (value include bottom margin)
			$heightforinfotot = 35;	// Height reserved to output the info and total part
			$deltay=$this->page_hauteur-$heightforfreetext-$heightforfooter-$heightforinfotot;	
			$cury=max($cury,$deltay);
			$deltax=$this->marge_gauche;

			$pdf->RoundedRect($deltax, $cury, $widthrecbox, 40, 2, $round_corner = '1111', 'S', $this->style, '');
			$pdf->SetFont('','B', $default_font_size - 1);
			$pdf->SetXY($deltax, $cury);
			$titre = $outputlangs->transnoentities("ContactNameAndSignature", $this->emetteur->name);
			$pdf->MultiCell(80, 5, $titre, 0, 'L',0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($deltax, $cury+5);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(90, 3, "",0,'L',0);
			$pdf->SetXY($deltax, $cury+12);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $outputlangs->transnoentities("DocORDER3"), 0, 'L', 0);
			$pdf->SetXY($deltax, $cury+17);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $outputlangs->transnoentities("DocORDER5"), 0, 'L', 0);
			
			$posy=max($posy,$deltay);
			$deltax=$this->marge_gauche+$widthrecbox+4;
			
			$pdf->RoundedRect($deltax, $posy, $widthrecbox, 40, 2, $round_corner = '1111', 'S', $this->style, '');
			$pdf->SetFont('','B', $default_font_size - 1);
			$pdf->SetXY($deltax, $posy);
			$titre = $outputlangs->transnoentities("ContactNameAndSignature", $this->recipient->name); 
			$pdf->MultiCell(80, 5, $titre, 0, 'L',0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($deltax, $posy+5);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(90, 3, $outputlangs->transnoentities('DocORDER2'),0,'L',0);
			$pdf->SetXY($deltax, $posy+12);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $outputlangs->transnoentities('DocORDER3'), 0, 'L', 0);
			$pdf->SetXY($deltax, $posy+17);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $outputlangs->transnoentities('DocORDER4'), 0, 'L', 0);

			return $posy;
		}
	}

	/**
	 *   Show table for lines
	 *
	 *   @param		PDF			&$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		Hide top bar of array
	 *   @param		int			$hidebottom		Hide bottom bar of array
	 *   @return	void
	 */
	function _tableau(&$pdf, $tab_top, $tab_height, $nexY, $outputlangs, $hidetop=0, $hidebottom=0)
	{
		global $conf;

		// Force to disable hidetop and hidebottom
		$hidebottom=0;
		if ($hidetop) $hidetop=-1;

		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$bgcolor = array('170','212','255');
		if (!empty($conf->global->ULTIMATE_BGCOLOR_COLOR))
		{
			$bgcolor =  html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
		}
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}
		
		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFillColorArray($bgcolor);
		$pdf->SetFont('','', $default_font_size - 2);

		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, 2, $round_corner = '0110', 'S', $this->style, array());
		
		if (! empty($conf->global->ULTIMATE_CONTRACT_WITH_LINE_NUMBER))
		{
			if (empty($hidetop))
			{
				$pdf->SetXY ($this->posxnumber-1, $tab_top);
				$pdf->MultiCell($this->posxdesc-$this->posxnumber+2, 7, $outputlangs->transnoentities("Numbering"), 0, 'C', 1);		
			}
			$pdf->line($this->posxnumber+9, $tab_top, $this->posxnumber+9, $tab_top + $tab_height);
		}
		
		// Comments
		if (empty($hidetop))
		{			
			$pdf->SetXY ($this->posxdesc, $tab_top);
			$pdf->MultiCell($this->posxdate_ouverture_prevue-$this->posxdesc, 7, $outputlangs->transnoentities("Description"), 0, 'L', 1);
		}
		
		// Date ouverture
		$pdf->line($this->posxdate_ouverture_prevue-1, $tab_top, $this->posxdate_ouverture_prevue-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxdate_ouverture_prevue-1, $tab_top);
			$pdf->MultiCell($this->posxdate_fin_validite-$this->posxdate_ouverture_prevue, 7, $outputlangs->transnoentities("DateStartPlannedShort"), 0, 'C', 1);
		}
		
		// Date fermeture
		$pdf->line($this->posxdate_fin_validite-1, $tab_top, $this->posxdate_fin_validite-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxdate_fin_validite-1, $tab_top);
			$pdf->MultiCell($this->posxdate_ouverture-$this->posxdate_fin_validite, 7, $outputlangs->transnoentities("DateEndPlanned"), 0, 'C', 1);
		}
		
		// Date ouverture réelle
		$pdf->line($this->posxdate_ouverture-1, $tab_top, $this->posxdate_ouverture-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxdate_ouverture-1, $tab_top);
			$pdf->MultiCell($this->posxdate_cloture-$this->posxdate_ouverture, 7, $outputlangs->transnoentities("DateStartRealShort"), 0, 'C', 1);
		}
		
		// Date fermeture
		$pdf->line($this->posxdate_cloture-1, $tab_top, $this->posxdate_cloture-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxdate_cloture-1, $tab_top);
			$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->posxdate_cloture+1, 7, $outputlangs->transnoentities("DateEndRealShort"), 0, 'C', 1);
		}
	}

	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			&$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $conf,$langs,$hookmanager;

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("bills");
		$outputlangs->load("companies");
		$outputlangs->load("contract");
		
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$bgcolor = array('170','212','255');
		if (!empty($conf->global->ULTIMATE_BGCOLOR_COLOR))
		{
			$bgcolor =  html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
		}
		$opacity = 0.5;
		if (!empty($conf->global->ULTIMATE_BGCOLOR_OPACITY))
		{
			$opacity =  $conf->global->ULTIMATE_BGCOLOR_OPACITY;
		}
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}
		$qrcodecolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$qrcodecolor =  html2rgb($conf->global->ULTIMATE_QRCODECOLOR_COLOR);
		}
		
		$main_page = $this->page_largeur-$this->marge_gauche-$this->marge_droite;	

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		//Affiche le filigrane brouillon - Print Draft Watermark
		if($object->statut==0 && (! empty($conf->global->CONTRACT_DRAFT_WATERMARK)) )
		{
			pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->CONTRACT_DRAFT_WATERMARK);
		}

		//Prepare la suite
		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posx=$this->page_largeur-$this->marge_droite-100;
		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

		// Other Logo 
		if ($conf->global->ULTIMATE_OTHERLOGO)
		{
			$logo=$conf->ultimatepdf->dir_output.'/otherlogo/'.$conf->global->ULTIMATE_OTHERLOGO;
			if ($conf->global->ULTIMATE_OTHERLOGO && is_readable($logo))
			{
				$logo_height=pdf_getUltimateHeightForLogo($logo,true);
				$pdf->Image($logo, $this->marge_gauche, $posy, 0, $logo_height);	// width=0 (auto)
			}
			else
			{
				$pdf->SetTextColor(200,0,0);
				$pdf->SetFont('','B', $default_font_size - 2);
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
				$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToModuleSetup"), 0, 'L');
			}
		}
		else
		{		
			$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
			if ($this->emetteur->logo)
			{
				if (is_readable($logo))
				{
					$logo_height=pdf_getUltimateHeightForLogo($logo,true);
					$pdf->Image($logo, $this->marge_gauche, $posy, 0, $logo_height);	// width=0 (auto)
				}
				else
				{
					$pdf->SetTextColor(200,0,0);
					$pdf->SetFont('','B', $default_font_size - 2);
					$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorLogoFileNotFound",$logo), 0, 'L');
					$pdf->MultiCell(100, 3, $outputlangs->transnoentities("ErrorGoToModuleSetup"), 0, 'L');
				}
			}
			else
			{
				$text=$this->emetteur->name;
				$pdf->MultiCell(100, 3, $outputlangs->convToOutputCharset($text), 0, 'L');
			}
		}
		
		//Display Thirdparty barcode at top				
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_CONTRACT_WITH_TOP_BARCODE))
		{
			$result=$object->thirdparty->fetch_barcode();
			$barcode=$object->thirdparty->barcode;	
			$posxbarcode=$this->page_largeur*2/3;
			$posybarcode=$posy-$this->marge_haute;
			$pdf->SetXY($posxbarcode,$posy-$this->marge_haute);
			$styleBc = array(
				'position' => '',
				'align' => 'R',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'hpadding' => 'auto',
				'vpadding' => 'auto',
				'fgcolor' => array(0,0,0),
				'bgcolor' => false, //array(255,255,255),
				'text' => true,
				'font' => 'helvetica',
				'fontsize' => 8,
				'stretchtext' => 4
				);
			if ($barcode <= 0)
			{
				$error++;
				if (empty($this->messageErrBarcodeSet)) 
				{
					setEventMessages($outputlangs->trans("BarCodeDataForThirdpartyMissing"), null, 'errors');
					$this->messageErrBarcodeSet=true;
				}
			}	
			else
			{
				// barcode_type_code
				$pdf->write1DBarcode($barcode, $object->thirdparty->barcode_type_code, $posxbarcode, $posybarcode, '', 12, 0.4, $styleBc, 'R');
			}
		}
		
		if ($logo_height<=30) 
		{
			$heightQRcode=$logo_height;
		}
		else 
		{
			$heightQRcode=30;
		}
		$posxQRcode=$this->page_largeur/2;		
		// set style for QRcode
		$styleQr = array(
		'border' => false,
		'vpadding' => 'auto',
		'hpadding' => 'auto',
		'fgcolor' => $qrcodecolor,
		'bgcolor' => false, //array(255,255,255)
		'module_width' => 1, // width of a single module in points
		'module_height' => 1 // height of a single module in points
		);
		// Order link QRcode
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_ORDERLINK_WITH_TOP_QRCODE))
		{
			$code = pdf_codeOrderLink(); //get order link
			$pdf->write2DBarcode($code, 'QRCODE,L', $posxQRcode, $posy, $heightQRcode, $heightQRcode, $styleQr, 'N');
		}
		// ThirdParty QRcode
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_CONTRACT_WITH_TOP_QRCODE))
		{
			$code = pdf_codeContents(); //get order link
			$pdf->write2DBarcode($code, 'QRCODE,L', $posxQRcode, $posy, $heightQRcode, $heightQRcode, $styleQr, 'N');
		}
		// My Company QR-code
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_CONTRACT_WITH_MYCOMP_QRCODE))
		{
			$code = pdf_mycompCodeContents();
			$pdf->write2DBarcode($code, 'QRCODE,L', $posxQRcode, $posy, $heightQRcode, $heightQRcode, $styleQr, 'N');
		}

		$pdf->SetFont('','B',$default_font_size + 3);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		$title=$outputlangs->transnoentities("ContractCard");
		$pdf->MultiCell(100, 4, $title, '', 'R');

		$pdf->SetFont('','B',$default_font_size + 2);

		$posy+=5;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Ref")." : " . $outputlangs->convToOutputCharset($object->ref), '', 'R');

		$posy+=1;
		$pdf->SetFont('','', $default_font_size);

		$posy+=4;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		$pdf->MultiCell(100, 3, $outputlangs->transnoentities("Date")." : " . dol_print_date($object->date_creation,"day",false,$outputlangs,true), '', 'R');

		if ($object->thirdparty->code_client)
		{
			$posy+=4;
			$pdf->SetXY($posx,$posy);
			$pdf->SetTextColorArray($textcolor);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("CustomerCode")." : " . $outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		if ($showaddress)
		{
			// Sender properties
			$bgcolor = html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
			$carac_emetteur='';

			$carac_emetteur .= pdf_contract_build_address($outputlangs, $this->emetteur, $object->thirdparty);

			// Show sender
			$delta=45-$logo_height;
			$posy=$logo_height+$this->marge_haute+$delta;	
			$posx=$this->marge_gauche;
			if (($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','SALESREPSIGN')) && ($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1) || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','SALESREPSIGN')) && ($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1)  || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','BILLING')) && ($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1) || (! empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS) && !empty($object->note_public)))
			{
				$hautcadre=68;
			}
			else
			{
				$hautcadre=52;
			}
			$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
			if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->page_largeur-$this->marge_droite-$widthrecbox;  

			// Show sender frame
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
			$pdf->SetAlpha(1);

			// Show sender name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell($widthrecbox-5, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy=$pdf->getY();

			// Show sender information
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+2,$posy);
			$pdf->MultiCell($widthrecbox-5, 4, $carac_emetteur, 0, 'L');
			$posy=$pdf->getY();
			
			// Show public note
			if (! empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS))
    		{
				$pdf->SetXY($posx+2,$posy+3);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell($widthrecbox-5, 4, dol_string_nohtmltag($object->note_public), 0, 'L');
			}
			
			// If CUSTOMER and BILLING contact defined, we use it
			if ($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','CUSTOMER'))
			{
				if (($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1) || !empty($object->note_public))
				{
					$hautcadre=68;
				}
				else
				{
					$hautcadre=52;
				}
				// If BILLING contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','BILLING');
				if (count($arrayidcontact) > 0);
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				// Recipient address
				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show invoice address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("BillAddress"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client, 0, 'L');	
				
				// If CUSTOMER contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','CUSTOMER');
			
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show shipping address
				$posy=$logo_height+$this->marge_haute+$delta;
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy+$hautcadre*0.5, $widthrecbox, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy+$hautcadre*0.5);
				$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("TypeContact_contrat_external_CUSTOMER"), 0, 'R');	
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1+$hautcadre*0.5);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();

				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client, 0, 'L');
			}
			// If CUSTOMER and SALESREPSIGN contact defined, we use it
			elseif ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','SALESREPSIGN'))
			{
				if (($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1) || !empty($object->note_public))
				{
					$hautcadre=68;
				}
				else
				{
					$hautcadre=52;
				}
				// If CUSTOMER contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','CUSTOMER');
				if (count($arrayidcontact) > 0);
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				// Recipient address
				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show invoice address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("TypeContact_contrat_external_CUSTOMER"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client, 0, 'L');	
				
				// If SALESREPSIGN contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','SALESREPSIGN');
			
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show shipping address
				$posy=$logo_height+$this->marge_haute+$delta;
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy+$hautcadre*0.5, $widthrecbox, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy+$hautcadre*0.5);
				$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("TypeContact_contrat_external_SALESREPSIGN"), 0, 'R');	
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1+$hautcadre*0.5);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();

				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client, 0, 'L');
			}
			// If BILLING and SALESREPSIGN contact defined, we use it
			elseif ($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','SALESREPSIGN'))
			{
				if (($conf->global->ULTIMATE_PDF_CONTRACT_ADDALSOTARGETDETAILS == 1) || !empty($object->note_public))
				{
					$hautcadre=68;
				}
				else
				{
					$hautcadre=52;
				}
				// If BILLING contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','BILLING');
				if (count($arrayidcontact) > 0);
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				// Recipient address
				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show invoice address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("BillAddress"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client, 0, 'L');	
				
				// If SALESREPSIGN contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','SALESREPSIGN');
			
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				
				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show shipping address
				$posy=$logo_height+$this->marge_haute+$delta;
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy+$hautcadre*0.5, $widthrecbox, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy+$hautcadre*0.5);
				$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("TypeContact_contrat_external_SALESREPSIGN"), 0, 'R');	
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1+$hautcadre*0.5);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();

				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5,4, $carac_client, 0, 'L');
			}
			elseif ($arrayidcontact=$object->getIdContact('external','BILLING'))
			{
				// If BILLING contact defined on invoice, we use it
				$usecontact=false;
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);

				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$posy=$logo_height+$this->marge_haute+$delta;		
				$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;	
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show billing address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);		
				$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("BillAddress"), 0, 'R');
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client, 0, 'L');

			}
			elseif ($arrayidcontact=$object->getIdContact('external','CUSTOMER'))
			{
				// If CUSTOMER contact defined on order, we use it
				$usecontact=false;
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);

				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$posy=$logo_height+$this->marge_haute+$delta;		
				$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;	
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show Contact_commande_external_CUSTOMER address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx,$posy);		
				$pdf->MultiCell($widthrecbox,4, $outputlangs->transnoentities("TypeContact_contrat_external_CUSTOMER"), 0, 'R');
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client, 0, 'L');

			}
			elseif ($arrayidcontact=$object->getIdContact('external','SALESREPSIGN'))
			{
				// If SALESREPSIGN contact defined on order, we use it
				$usecontact=false;
				if (count($arrayidcontact) > 0)
				{
					$usecontact=true;
					$result=$object->fetch_contact($arrayidcontact[0]);
				}

				// Recipient name
				// On peut utiliser le nom de la societe du contact
				if ($usecontact && !empty($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT)) 
				{
					$thirdparty = $object->contact;
				} 
				else 
				{
					$thirdparty = $object->thirdparty;
				}

				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);

				$carac_client=pdf_contract_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$posy=$logo_height+$this->marge_haute+$delta;		
				$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;	
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show SALESREPSIGN address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx,$posy);		
				$pdf->MultiCell($widthrecbox,4, $outputlangs->transnoentities("TypeContact_contrat_external_SALESREPSIGN"), 0, 'R');
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client, 0, 'L');

			}
			else
			{
				$thirdparty = $object->thirdparty;
				// Recipient name
				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				// Recipient address
				$carac_client=pdf_contract_build_address($outputlangs, $this->emetteur, $object->thirdparty, '', 0, 'target');

				// Show recipient
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);
				
				// Show shipping address
				$pdf->SetXY($posx,$posy-4);	
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecbox-5, 4, $carac_client, 0, 'L');				
			}	
		}
	}

	/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *   	@param	PDF			&$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	void
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_ultimatepagefoot($pdf,$outputlangs,'CONTRACT_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext, $footertextcolor);
	}

}

?>
