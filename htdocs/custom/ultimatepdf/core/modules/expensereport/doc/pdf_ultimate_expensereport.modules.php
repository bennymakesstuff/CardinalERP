<?php
/* Copyright (C) 2015 Laurent Destailleur    <eldy@users.sourceforge.net>
 * Copyright (C) 2015 Alexandre Spangaro     <aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2017 Philippe Grand		 <philippe.grand@atoo-net.com>
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
 *	\file       htdocs/custom/ultimatepdf/core/modules/expensereport/doc/pdf_ultimate_expensereport.modules.php
 *	\ingroup    expensereport
 *	\brief      File of class to generate expense report from ultimate_expensereport model
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/expensereport/modules_expensereport.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
dol_include_once("/ultimatepdf/lib/ultimatepdf.lib.php");



/**
 *	Class to generate expense report based on ultimate_expensereport model
 */
class pdf_ultimate_expensereport extends ModeleExpenseReport
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
	public $number_width;

    /**
	* Issuer
	* @var Societe
	*/
	public $emetteur;


	/**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	function __construct($db)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load("trips");
		$langs->load("project");
		$langs->load("ultimatepdf@ultimatepdf");

		$this->db = $db;
		$this->name = "ultimate_expensereport";
		$this->description = $langs->trans('PDFStandardExpenseReports');

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
		$this->option_tva = 1;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 1;                 // Affiche mode reglement
		$this->option_condreg = 1;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 1;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_escompte = 0;                // Affiche si il y a eu escompte
		$this->option_credit_note = 0;             // Support credit notes
		$this->option_freetext = 1;				   // Support add of a personalised text
		$this->option_draft_watermark = 1;		   // Support add of a watermark on drafts

		$this->franchise=!$mysoc->tva_assuj;
		
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
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if was not defined

		// Define position of columns
		if (! empty($conf->global->ULTIMATE_EXPENSEREPORT_WITH_LINE_NUMBER))
		{
			$this->posxnumber=$this->marge_gauche+1;
			$this->number_width = 10;
		}
		else
		{
			$this->posxnumber=0;
			$this->number_width = 0;
		}
		$this->posxcomment=$this->marge_gauche+$this->number_width;
		$this->posxdate=80;
		$this->posxtype=97;
		$this->posxprojet=116;
		$this->posxtva=136;
		$this->posxup=148;
		$this->posxqty=166;
		$this->postotalttc=178;
		if ($this->page_largeur < 210) // To work with US executive format
		{
			$this->posxdate-=20;
			$this->posxtype-=20;
			$this->posxprojet-=20;
			$this->posxtva-=20;
			$this->posxup-=20;
			$this->posxqty-=20;
			$this->postotalttc-=20;
		}

		$this->tva=array();
		$this->localtax1=array();
		$this->localtax2=array();
		$this->atleastoneratenotnull=0;
	}


	/**
     *  Function to build pdf onto disk
     *
     *  @param		Object		$object				Object to generate
     *  @param		Translate	$outputlangs		Lang output object
     *  @param		string		$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int			$hidedetails		Do not show line details
     *  @param		int			$hidedesc			Do not show desc
     *  @param		int			$hideref			Do not show ref
     *  @return     int             				1=OK, 0=KO
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

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("trips");
		$outputlangs->load("project");
		$outputlangs->load("ultimatepdf@ultimatepdf");

		$nblignes = count($object->lines);

		if ($conf->expensereport->dir_output)
		{
			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->expensereport->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->expensereport->dir_output . "/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
					return 0;
				}
			}

			if (isset($object->lignes) && ! isset($object->lines)) $object->lines=$object->lignes;

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
				$heightforinfotot = 40;	// Height reserved to output the info and total part
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:8);	// Height reserved to output the free text on last page
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

				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref_number));
				$pdf->SetSubject($outputlangs->transnoentities("Trips"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Trips"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColorArray($textcolor);
				
				//catch logo height
				$logo_height=pdf_getUltimateHeightForLogo($logo);
				$hautcadre=46;
				if ($logo_height<30) 
				{
					$tab_top=$this->marge_haute+30+$hautcadre+10;
				}
				else
				{
					$tab_top = $this->marge_haute+$logo_height+$hautcadre+10;
				}	
				$tab_top_newpage = (empty($conf->global->ULTIMATE_EXPENSEREPORT_PDF_DONOTREPEAT_HEAD)?$this->marge_haute+$logo_height+15:10);
				$tab_height = 130;
				$tab_height_newpage = 150;

				// Show notes
				$notetoshow=empty($object->note_public)?'':$object->note_public;
				if (! empty($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_NOTE))
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
				if ($notetoshow)
				{
					if ($logo_height<30) 
					{
						$tab_top=$this->marge_haute+32+$hautcadre+10;
					}
					else
					{
						$tab_top = $this->marge_haute+$logo_height+$hautcadre+10;
					}	

					$pdf->SetFont('','', $default_font_size - 1);
               		$pdf->writeHTMLCell(190, 3, $this->posxcomment-$this->posxnumber+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1, 2, $round_corner = '1111', 'S', $this->style, array());

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+6;
				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				// Loop on each lines
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					$piece_comptable = $i +1;

					$curY = $nexY;
					$pdf->SetFont('','', $default_font_size - 2);   // Into loop to work with multipage
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

					// Description of product line
					$curX = $this->posxcomment-1;

					$showpricebeforepagebreak=1;

					// Accountancy piece
					$pdf->SetXY($this->posxnumber, $curY);
					$pdf->MultiCell($this->posxcomment-$this->posxnumber-0.8, 4, $piece_comptable, 0, 'C');

					// Comments
					$pdf->SetXY($this->posxcomment, $curY);
					$pdf->writeHTMLCell($this->posxdate-$this->posxcomment-0.8, 4, $this->posxcomment-1, $curY, $object->lines[$i]->comments, 0, 1);

					//nexY
					$nexY = $pdf->GetY();
					$pageposafter=$pdf->getPage();
					$pdf->setPage($pageposbefore);
					$pdf->setTopMargin($this->marge_haute);
					$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.

					// Date
					$pdf->SetXY($this->posxdate-1, $curY);
					$pdf->MultiCell($this->posxtype-$this->posxdate-0.8, 4,dol_print_date($object->lines[$i]->date,"day",false,$outpulangs), 0, 'C');

					// Type
					$pdf->SetXY($this->posxtype-1, $curY);
					$pdf->MultiCell($this->posxprojet-$this->posxtype-0.8, 4,$outputlangs->transnoentities($object->lines[$i]->type_fees_code), 0, 'C');

					// Project
					$pdf->SetXY($this->posxprojet-1, $curY);
					$pdf->MultiCell($this->posxtva-$this->posxprojet-0.8, 4,$object->lines[$i]->projet_ref, 0, 'C');

					// VAT Rate
					if (empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT))
					{
						$vat_rate = pdf_getlinevatrate($object, $i, $outputlangs, $hidedetails);
						$pdf->SetXY($this->posxtva, $curY);
						$pdf->MultiCell($this->posxup-$this->posxtva-0.8, 4, $vat_rate, 0, 'C');
					}

					// Unit price
					$pdf->SetXY($this->posxup, $curY);
					$pdf->MultiCell($this->posxqty-$this->posxup-0.8, 4, price($object->lines[$i]->value_unit), 0, 'R');

					// Quantity
					$pdf->SetXY($this->posxqty, $curY);
					$pdf->MultiCell($this->postotalttc-$this->posxqty-0.8, 4,$object->lines[$i]->qty, 0, 'C');

					// Total with all taxes
					$pdf->SetXY($this->postotalttc-1, $curY);
					$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->postotalttc, 4, price($object->lines[$i]->total_ttc), 0, 'R');
					
					// Add line
					if (! empty($conf->global->ULTIMATE_EXPENSEREPORT_PDF_DASH_BETWEEN_LINES) && $i < ($nblignes - 1))
					{
						$pdf->setPage($pageposafter);
						$pdf->SetLineStyle(array('dash'=>'1,1','color'=>array(210,210,210)));
						$pdf->line($this->marge_gauche, $nexY+1, $this->page_largeur - $this->marge_droite, $nexY+1);
						$pdf->SetLineStyle(array('dash'=>0));
					}

					// Cherche nombre de lignes a venir pour savoir si place suffisante
					if ($i < ($nblignes - 1))	// If it's not last line
					{
						//on recupere le commentaire suivant
						$follow_comment = $object->lines[$i+1]->comments;
						//on compte le nombre de ligne afin de verifier la place disponible (largeur de ligne 52 caracteres)
						$nblineFollowComment = dol_nboflines_bis($follow_comment,52,$outputlangs->charset_output)*4;
					}
					else	// If it's last line
					{
						$nblineFollowComment = 0;
					}

					$nexY+=5;    // Passe espace entre les lignes

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
						if (empty($conf->global->ULTIMATE_EXPENSEREPORT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
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
						if (empty($conf->global->ULTIMATE_EXPENSEREPORT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
					}
				}

				// Show square
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 0, 0);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 1, 0);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				}

				// Affiche zone totaux
				$posy=$this->_tableau_tot($pdf, $object, $deja_regle, $bottomlasttab, $outputlangs);

				// Pied de page
				$this->_pagefoot($pdf, $object, $outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

				$pdf->Close();

				$pdf->Output($file,'F');

				// Add pdfgeneration hook
				$hookmanager->initHooks(array('pdfgeneration'));
				$parameters=array('file'=>$file,'object'=>$object,'outputlangs'=>$outputlangs);
				global $action;
				$reshook=$hookmanager->executeHooks('afterPDFCreation',$parameters,$this,$action);    // Note that $action and $object may have been modified by some hooks

				if (! empty($conf->global->MAIN_UMASK))
				@chmod($file, octdec($conf->global->MAIN_UMASK));

				return 1;   // Pas d'erreur
			}
			else
			{
				$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->trans("ErrorConstantNotDefined","EXPENSEREPORT_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}
	
	/**
	 *	Show total to pay
	 *
	 *	@param	PDF			&$pdf           Object PDF
	 *	@param  Facture		$object         Object invoice
	 *	@param  int			$deja_regle     Montant deja regle
	 *	@param	int			$posy			Position depart
	 *	@param	Translate	$outputlangs	Objet langs
	 *	@return int							Position pour suite
	 */
	function _tableau_tot(&$pdf, $object, $deja_regle, $posy, $outputlangs)
	{
	   global $conf,$mysoc,$langs;

		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$currency_code = $langs->getCurrencySymbol($conf->currency);
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

		$tab2_top = $posy;
		$tab2_hl = 4;
		$pdf->SetFont('','', $default_font_size - 1);

		// Tableau total
		$col1x = $this->page_largeur/2 +10; $col2x = 170; 
		if ($this->page_largeur < 210) // To work with US executive format
		{
			$col2x-=20;
		}
		$largcol2 = ($this->page_largeur - $this->marge_droite - $col2x);
		
		$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
		$deltax=$this->marge_gauche+$widthrecbox+4;
		$pdf->SetAlpha($opacity);
		$pdf->RoundedRect($deltax, $tab2_top, $widthrecbox, 20, 2, $round_corner = '1111', 'FD', $this->style, $bgcolor);
		$pdf->SetAlpha(1);

		$useborder=0;
		$index = 0;

		// Total HT
		$pdf->SetFillColor(255,255,255);
		$pdf->SetXY ($col1x, $tab2_top + 0);
		$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalHT"), 0, 'L', 1);
		
		$pdf->SetXY ($col2x, $tab2_top + 0);
		$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ht, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);

		// Show VAT by rates and total
		$pdf->SetFillColor(248,248,248);

		$this->atleastoneratenotnull=0;
		if ($conf->global->ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT == "no")
		{
			$tvaisnull=((! empty($this->tva) && count($this->tva) == 1 && isset($this->tva['0.000']) && is_float($this->tva['0.000'])) ? true : false);
			if (! empty($conf->global->ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT_ISNULL) && $tvaisnull)
			{
				// Nothing to do
			}
			else
			{
				// VAT		
				$index++;
				$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','',$default_font_size );
				$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalVAT"), $useborder, 'L', 1);
				$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
				$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_tva, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);				

				// Total TTC
				$index++;
				$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','B',$default_font_size );
				$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalTTC"), $useborder, 'L', 1);

				$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
				$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ttc, 0, $outputlangs,0,-1,-1,$currency_code), $useborder, 'R', 1);
			}
		}
		else
		{
			// Total TTC without VAT			
			$index++;
			$pdf->SetXY ($col1x, $tab2_top + 0);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalHT"), 0, 'L', 1);
			$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetFont('','B',$default_font_size );
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalTTC"), $useborder, 'L', 1);

			$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ht, 0, $outputlangs, 0, -1, -1, $currency_code), 0, 'R', 1);		
		}			
		$pdf->SetTextColorArray($textcolor);

		$index++;
		return ($tab2_top + ($tab2_hl * $index));
	}
	
	/**
	 *   Affiche la grille des lignes de factures
	 *
	 *   @param     PDF			$pdf     		Object PDF
	 *   @param		int			$tab_top		Tab top
	 *   @param		int			$tab_height		Tab height
	 *   @param		int			$nexY			next y
	 *   @param		Translate	$outputlangs	Output langs
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

		// Amount in (at tab_top - 1)
		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFillColorArray($bgcolor);
		$pdf->SetFont('','', $default_font_size - 2);

		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, 2, $round_corner = '0110', 'S', $this->style, array());
		
		/*$titre = $outputlangs->transnoentities("AmountInCurrency",$outputlangs->transnoentitiesnoconv("Currency".$conf->currency));
		$pdf->SetXY($this->page_largeur - $this->marge_droite - ($pdf->GetStringWidth($titre) + 4), $tab_top -4);
		$pdf->MultiCell(($pdf->GetStringWidth($titre) + 3), 2, $titre);*/
		// Accountancy piece
		if (! empty($conf->global->ULTIMATE_EXPENSEREPORT_WITH_LINE_NUMBER))
		{
			if (empty($hidetop))
			{		
				$pdf->SetXY ($this->posxnumber-1, $tab_top);
				$pdf->MultiCell($this->posxcomment-$this->posxnumber+2,5, $outputlangs->transnoentities("Numbering"), 0, 'C', 1);				
			}
		}

		$pdf->SetFont('','',8);

		// Comments
		$pdf->line($this->posxcomment-1, $tab_top, $this->posxcomment-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxcomment, $tab_top);
			$pdf->MultiCell($this->posxdate-$this->posxcomment,5,$outputlangs->transnoentities("Description"),0,'L',1);
		}

		// Date
		$pdf->line($this->posxdate-1, $tab_top, $this->posxdate-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxdate-1, $tab_top);
			$pdf->MultiCell($this->posxtype-$this->posxdate,5, $outputlangs->transnoentities("Date"), 0, 'C', 1);
		}

		// Type
		$pdf->line($this->posxtype-1, $tab_top, $this->posxtype-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxtype-1, $tab_top);
			$pdf->MultiCell($this->posxprojet-$this->posxtype,5, $outputlangs->transnoentities("Type"), 0, 'C', 1);
		}

		// Project
		$pdf->line($this->posxprojet-1, $tab_top, $this->posxprojet-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxprojet-1, $tab_top);
			$pdf->MultiCell($this->posxtva-$this->posxprojet,5, $outputlangs->transnoentities("Project"), 0, 'C', 1);
		}

		// VAT
		if (empty($conf->global->MAIN_GENERATE_DOCUMENTS_WITHOUT_VAT))
		{
			$pdf->line($this->posxtva-1, $tab_top, $this->posxtva-1, $tab_top + $tab_height);
			if (empty($hidetop)) 
			{
				$pdf->SetXY($this->posxtva-1, $tab_top);
				$pdf->MultiCell($this->posxup-$this->posxtva,5, $outputlangs->transnoentities("VAT"), 0, 'C', 1);
			}
		}

		// Unit price
		$pdf->line($this->posxup-1, $tab_top, $this->posxup-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxup-1, $tab_top);
			$pdf->MultiCell($this->posxqty-$this->posxup,5, $outputlangs->transnoentities("PriceU"), 0, 'C', 1);
		}

		// Quantity
		$pdf->line($this->posxqty-1, $tab_top, $this->posxqty-1, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxqty-1, $tab_top);
			$pdf->MultiCell($this->postotalttc-$this->posxqty,5, $outputlangs->transnoentities("Qty"), 0, 'C', 1);
		}

		// Total with all taxes
		$pdf->line($this->postotalttc, $tab_top, $this->postotalttc, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->postotalttc-1, $tab_top);
			$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->postotalttc+1, 5, $outputlangs->transnoentities("TotalTTC"), 0, 'R', 1);
		}

		$pdf->SetTextColorArray($textcolor);
	}


	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs)
	{
		global $conf,$langs,$hookmanager;

		$outputlangs->load("main");
		$outputlangs->load("trips");
		$outputlangs->load("companies");
		
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

		/*
		// ajout du fondu vert en bas de page à droite
		$image_fondue = $conf->mycompany->dir_output.'/fondu_vert_.jpg';
		$pdf->Image($image_fondue,20,107,200,190);*/

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);
		

	    // Draft watermark
		if ($object->fk_statut==1 && ! empty($conf->global->EXPENSEREPORT_FREE_TEXT))
		{
 			pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->EXPENSEREPORT_FREE_TEXT);
		}

		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFont('','B', $default_font_size + 3);
		$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
		$posy=$this->marge_haute;
		$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;

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
			// Logo		
			$logo=$conf->mycompany->dir_output.'/logos/'.$this->emetteur->logo;
			if ($this->emetteur->logo)
			{
				if (is_readable($logo))
				{
					$logo_height=pdf_getUltimateHeightForLogo($logo);
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

		$pdf->SetFont('','B', $default_font_size + 4);
		$pdf->SetXY($posx,$posy);
   		$pdf->SetTextColorArray($textcolor);
		$pdf->MultiCell($this->page_largeur-$this->marge_droite-$posx,6,$langs->trans("ExpenseReport"), 0, 'L');

		$pdf->SetFont('','', $default_font_size -1);

   		// Ref complete
   		$posy+=8;
   		$pdf->SetXY($posx,$posy);
   		$pdf->SetTextColorArray($textcolor);
   		$pdf->MultiCell($this->page_largeur-$this->marge_droite-$posx, 3, $outputlangs->transnoentities("Ref")." : " . $object->ref, '', 'L');

   		// Date start period
   		$posy+=5;
   		$pdf->SetXY($posx,$posy);
   		$pdf->SetTextColorArray($textcolor);
   		$pdf->MultiCell($this->page_largeur-$this->marge_droite-$posx, 3, $outputlangs->transnoentities("DateStart")." : " . ($object->date_debut>0?dol_print_date($object->date_debut,"day",false,$outpulangs):''), '', 'L');

   		// Date end period
   		$posy+=5;
   		$pdf->SetXY($posx,$posy);
   		$pdf->SetTextColorArray($textcolor);
   		$pdf->MultiCell($this->page_largeur-$this->marge_droite-$posx, 3, $outputlangs->transnoentities("DateEnd")." : " . ($object->date_fin>0?dol_print_date($object->date_fin,"day",false,$outpulangs):''), '', 'L');

   		// Status Expense Report
   		$posy+=5;
   		$pdf->SetXY($posx,$posy);
   		$pdf->SetFont('','B',$default_font_size+1);
   		$pdf->SetTextColor(111,81,124);
		$pdf->MultiCell($this->page_largeur-$this->marge_droite-$posx, 3, $object->getLibStatut(0), '', 'L');
		
		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur = pdf_order_build_address($outputlangs, $this->emetteur, $object->thirdparty);
			
			if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->page_largeur-$this->marge_droite-$widthrecbox;  

			// Show sender
			if ($logo_height<30) 
			{
				$posy=$this->marge_haute+30;
			}
			else
			{
				$posy=$logo_height+$this->marge_haute+2;
			}	
			$posx=$this->marge_gauche;
			$hautcadre=46;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=118;

			// Show sender frame
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetFont('','B', $default_font_size - 2);
			$pdf->SetXY($posx+2,$posy);
			$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("TripSociete"), 0, 'R');

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

			// Show recipient
			if ($logo_height<30) 
			{
				$posy=$this->marge_haute+30;
			}
			else
			{
				$posy=$logo_height+$this->marge_haute+2;
			}
			$posx=$this->page_largeur-$this->marge_droite-$widthrecbox;
			if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

			// Show invoice address
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
			$pdf->SetAlpha(1);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($posx+2,$posy);
			$pdf->MultiCell($widthrecbox-5,4, $outputlangs->transnoentities("TripNDF"), 0, 'R');
			

			// Informations for trip (dates and users workflow)
			if ($object->fk_user_author > 0)
			{
				$userfee=new User($this->db);
				$userfee->fetch($object->fk_user_author); $posy+=3;
				$pdf->SetXY($posx+2,$posy);
				$pdf->SetFont('','',10);
				$pdf->MultiCell(96,4,$outputlangs->transnoentities("AUTHOR")." : ".dolGetFirstLastname($userfee->firstname,$userfee->lastname),0,'L');
				$posy+=5;
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell(96,4,$outputlangs->transnoentities("DateCreation")." : ".dol_print_date($object->date_create,"day",false,$outpulangs),0,'L');
			}

			if ($object->fk_statut==99)
			{
				if ($object->fk_user_refuse > 0)
				{
					$userfee=new User($this->db);
					$userfee->fetch($object->fk_user_refuse); $posy+=6;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("REFUSEUR")." : ".dolGetFirstLastname($userfee->firstname,$userfee->lastname),0,'L');
					$posy+=5;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("MOTIF_REFUS")." : ".$outputlangs->convToOutputCharset($object->detail_refuse),0,'L');
					$posy+=5;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("DATE_REFUS")." : ".dol_print_date($object->date_refuse,"day",false,$outpulangs),0,'L');
				}
			}
			else if($object->fk_statut==4)
			{
				if ($object->fk_user_cancel > 0)
				{
					$userfee=new User($this->db);
					$userfee->fetch($object->fk_user_cancel); $posy+=6;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("CANCEL_USER")." : ".dolGetFirstLastname($userfee->firstname,$userfee->lastname),0,'L');
					$posy+=5;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("MOTIF_CANCEL")." : ".$outputlangs->convToOutputCharset($object->detail_cancel),0,'L');
					$posy+=5;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("DATE_CANCEL")." : ".dol_print_date($object->date_cancel,"day",false,$outpulangs),0,'L');
				}
			}
			else
			{
				if ($object->fk_user_approve > 0)
				{
					$userfee=new User($this->db);
					$userfee->fetch($object->fk_user_approve); $posy+=6;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("VALIDOR")." : ".dolGetFirstLastname($userfee->firstname,$userfee->lastname),0,'L');
					$posy+=5;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("DateApprove")." : ".dol_print_date($object->date_approve,"day",false,$outpulangs),0,'L');
				}
			}

			if($object->fk_statut==6)
			{
				if ($object->fk_user_paid > 0)
				{
					$userfee=new User($this->db);
					$userfee->fetch($object->fk_user_paid); $posy+=6;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("AUTHORPAIEMENT")." : ".dolGetFirstLastname($userfee->firstname,$userfee->lastname),0,'L');
					$posy+=5;
					$pdf->SetXY($posx+2,$posy);
					$pdf->MultiCell(96,4,$outputlangs->transnoentities("DATE_PAIEMENT")." : ".dol_print_date($object->date_paiement,"day",false,$outpulangs),0,'L');
				}
			}
		}
		
		$pdf->SetTextColorArray($textcolor);
   	}

	/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *   	@param	PDF			$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_ultimatepagefoot($pdf,$outputlangs,'EXPENSEREPORT_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext, $footertextcolor);
	}

}

