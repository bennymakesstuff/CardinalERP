<?php
/* Copyright (C) 2003       Rodolphe Quiedeville        <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2010  Laurent Destailleur         <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012	Regis Houssin				<regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2017  Philippe Grand              <philippe.grand@atoo-net.com>
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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 * or see http://www.gnu.org/
 */

/**
 *	\file       core/modules/fichinter/doc/pdf_best_inter.modules.php
 *	\ingroup    ficheinter
 *	\brief      Fichier de la classe permettant de generer les fiches d'intervention au modele best_inter
 */
require_once DOL_DOCUMENT_ROOT.'/core/modules/fichinter/modules_fichinter.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/date.lib.php';
require_once DOL_DOCUMENT_ROOT.'/fichinter/class/fichinter.class.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
dol_include_once("/ultimatepdf/lib/ultimatepdf.lib.php");


/**
 *	Class to build interventions documents with model best_inter
 */
class pdf_best_inter extends ModelePDFFicheinter
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
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
	public function  __construct($db)
	{
		global $conf,$langs,$mysoc;
		
		$langs->load("ultimatepdf@ultimatepdf");
		$langs->load("products");
		
		$this->db = $db;
		$this->name = 'best_inter';
		$this->description = $langs->trans("DocumentModelStandard");

		// Dimension page pour format A4
		$this->type = 'pdf';
		$formatarray=pdf_getFormat();
		$this->page_largeur = $formatarray['width'];
		$this->page_hauteur = $formatarray['height'];
		$this->format = array($this->page_largeur,$this->page_hauteur);
		$this->marge_gauche=isset($conf->global->ULTIMATE_PDF_MARGIN_LEFT)?$conf->global->ULTIMATE_PDF_MARGIN_LEFT:10;
		$this->marge_droite=isset($conf->global->ULTIMATE_PDF_MARGIN_RIGHT)?$conf->global->ULTIMATE_PDF_MARGIN_RIGHT:10;
		$this->marge_haute =isset($conf->global->ULTIMATE_PDF_MARGIN_TOP)?$conf->global->ULTIMATE_PDF_MARGIN_TOP:5;
		$this->marge_basse =isset($conf->global->ULTIMATE_PDF_MARGIN_BOTTOM)?$conf->global->ULTIMATE_PDF_MARGIN_BOTTOM:10;

		$this->option_logo = 1;                    // Affiche logo
		$this->option_tva = 0;                     // Gere option tva FACTURE_TVAOPTION
		$this->option_modereg = 0;                 // Affiche mode reglement
		$this->option_condreg = 0;                 // Affiche conditions reglement
		$this->option_codeproduitservice = 0;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_draft_watermark = 1;		   //Support add of a watermark on drafts

		if(!empty($conf->global->ULTIMATE_BORDERCOLOR_COLOR))
		{
			$bordercolor = html2rgb($conf->global->ULTIMATE_BORDERCOLOR_COLOR);
			$dashdotted = $conf->global->ULTIMATE_DASH_DOTTED;
			$this->style = array('width' => 0.2, 'cap' => 'butt', 'join' => 'miter', 'dash' => $dashdotted , 'color' => $bordercolor);
		}

		// Get source company
		$this->emetteur=$mysoc;
		if (empty($this->emetteur->country_code)) $this->emetteur->country_code=substr($langs->defaultlang,-2);    // By default, if not defined

		//  Define position of columns
		$this->posxdate=$this->marge_gauche+1;
		$this->posxshifting=40;
        $this->posxhours=70;
        $this->posxwaiting=100;

		$this->posxqty=$this->marge_gauche+1;
		$this->posxart=40;
		$this->posxdesc=90;
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
		$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);

		if (! is_object($outputlangs)) $outputlangs=$langs;

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("interventions");
		$outputlangs->load("ultimatepdf@ultimatepdf");
		
		$nblines = count($object->lines);	

		if ($conf->ficheinter->dir_output)
		{
            $object->fetch_thirdparty();

			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->ficheinter->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->ficheinter->dir_output . "/" . $objectref;
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
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:20);	// Height reserved to output the free text on last page
	            $heightforfooter = $this->marge_basse + 42;	// Height reserved to output the footer (value include bottom margin)
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

				//Generation de l entete du fichier
				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("InterventionCard"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("InterventionCard"));
				if ($conf->global->MAIN_DISABLE_PDF_COMPRESSION) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right

				$pdf->SetFont('','', $default_font_size - 3);

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs , $titlekey=$outputlangs->transnoentities("InterventionCard"));
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3

				//Initialisation des coordonnees
				//catch logo height
				$logo_height=pdf_getUltimateHeightForLogo($logo);
				if ($logo_height<30) 
				{
					$tab_top=$this->marge_haute+30+53;
				}
				else
				{
					$tab_top = $this->marge_haute+$logo_height+53;
				}	
				$tab_top_newpage = (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)?$this->marge_haute+$logo_height+15:10);
				$tab_height = 130;
				$tab_height_newpage = 150;				

				// Affiche notes
				$notetoshow=empty($object->note_public)?'':$object->note_public;
				if (! empty($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_ORDER_NOTE))
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
						$tab_top=$this->marge_haute+30+53;
					}
					else
					{
						$tab_top = $this->marge_haute+$logo_height+53;
					}	

					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
					$pdf->writeHTMLCell(190, 3, $this->posxdate-1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme et 4eme param
					$pdf->SetDrawColor(192,192,192);					
					$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1, 2, $round_corner = '1111', 'S', $this->style, $fill_color=array());

					$tab_height = $tab_height - $height_note;
					$tab_top = $nexY+4;
				}
				else
				{
					$height_note=0;
				}

				$iniY = $tab_top + 7;
				$curY = $tab_top + 7;
				$nexY = $tab_top + 7;

				// Loop on each lines
				for ($i = 0 ; $i < $nblines ; $i++)
				{	
					$objectline = $object->lines[$i];
					$valide = $objectline->id ? $objectline->fetch($objectline->id) : 0;
					if ($objectline->duration >0)
					{
					
						if ($valide > 0 || $object->specimen)
						{
							$curY = $nexY;
							$pdf->SetFont('','B', $default_font_size - 1);
							$pdf->SetTextColorArray($textcolor);
							$page_current=$pdf->getPage();
							$pdf->setTopMargin($tab_top_newpage);
							if ($i!=$nblines-1) 
							{
								$bMargin=$heightforfooter;
							}
							else
							{
								$bMargin=$heightforfooter+$heightforfreetext+$heightforinfotot;
							}
							//If we aren't on last lines footer space needed is on $heightforfooter
							$pdf->setPageOrientation('', 1, $bMargin);
							// The only function to edit the bottom margin of current page to set it.
							$pageposbefore=$pdf->getPage();
							
							// Description of product line
							$curX=$this->posxwaiting;
							$page_current=$pdf->getPage();
							$showpricebeforepagebreak=0;
							
							$pdf->startTransaction();
							pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->marge_droite-$this->posxwaiting,3,$curX,$curY,$hideref,$hidedesc);
							$posYafterDesc=$pdf->GetY();
							
							$pageposafter=$pdf->getPage();
							
							if ($pageposafter > $pageposbefore)	// There is a pagebreak
							{
								$pdf->rollbackTransaction(true);
					
								pdf_writelinedesc($pdf,$object,$i,$outputlangs,$this->marge_droite-$this->posxwaiting,3,$curX,$curY,$hideref,$hidedesc);

								$posYafterDesc=$pdf->GetY();

								if ($posYafterDesc > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
								{
									if ($i == ($nblignes-1))	// No more lines, and no space left to show total, so we create a new page
									{
										$pdf->AddPage('','',true);
										if (! empty($tplidx)) $pdf->useTemplate($tplidx);
										if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey=$outputlangs->transnoentities("InterventionCard"));
										$pdf->setPage($pageposafter+1); 
									}
								}
								else
								{
									// We found a page break
									$showpricebeforepagebreak=1;
								}
							}
							else	// No pagebreak
							{
								$pdf->commitTransaction();
							}
							
							$nexY = $pdf->GetY();
							$pageposafter=$pdf->getPage();
							$pdf->setPage($pageposbefore);
							$pdf->setTopMargin($this->marge_haute);
							$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
							
							// We suppose that a too long description is moved completely on next page
							if ($pageposafter > $pageposbefore && empty($showpricebeforepagebreak)) {
								$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
							}

							$pdf->SetFont('','', $default_font_size - 1);   // On repositionne la police par defaut
							
							// Date
							$date=dol_print_date($objectline->datei,'dayhour',false,$outputlangs,true);
							$pdf->SetXY ($this->posxdate, $curY);
							$pdf->MultiCell($this->posxshifting-$this->posxdate, 4, $date, 0, 'L', 0);

							// Travel expenses using extrafields
							$title_key=(empty($object->array_options['options_newprice']))?'':($object->array_options['options_newprice']);
							$extrafields = new ExtraFields ( $this->db );
							$extralabels = $extrafields->fetch_name_optionals_label ( $object->table_element, true );
							if (is_array ( $extralabels ) && key_exists ( 'newprice', $extralabels ) && !empty($title_key))
							{
								$pdf->SetFont('','B', $default_font_size - 1);
								$pdf->SetXY ($this->posxshifting, $curY);
								$shifting = $extrafields->showOutputField ( 'newprice', $title_key );
								$pdf->MultiCell($this->posxhours-$this->posxshifting, 4, $shifting, 0, 'L', 0);
							}

							// Duration
							$duration=ConvertSecondToTime($objectline->duration);
							$pdf->SetXY ($this->posxhours, $curY);
							$pdf->MultiCell($this->posxwaiting-$this->posxhours, 4, $duration, 0, 'L', 0);
							
							// Add line
							if (! empty($conf->global->MAIN_PDF_DASH_BETWEEN_LINES) && $i < ($nblignes - 1))
							{
								$pdf->setPage($pageposafter);
								$pdf->SetLineStyle(array('dash'=>'1,1','color'=>array(210,210,210)));
								//$pdf->SetDrawColor(190,190,200);
								$pdf->line($this->marge_gauche, $nexY+1, $this->page_largeur - $this->marge_droite, $nexY+1);
								$pdf->SetLineStyle(array('dash'=>0));
							}

							$nexY+=2;    // Passe espace entre les lignes

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
								if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey=$outputlangs->transnoentities("InterventionCard"));
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
								if (empty($conf->global->MAIN_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 1, $outputlangs , $titlekey=$outputlangs->transnoentities("InterventionCard"));
							}
						}
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

				// Affiche zone infos
				$posy=$this->_tableau_info($pdf, $object, $bottomlasttab, $outputlangs);
				
				// Affiche zone agreement
				$posy=$this->_agreement($pdf, $object, $posy, $outputlangs);
				
				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
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
			$this->error=$langs->trans("ErrorConstantNotDefined","FICHEINTER_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		return 0;   // Erreur par defaut
	}
	
	/**
	 *   Show miscellaneous information (payment mode, payment term, ...)
	 *
	 *   @param		PDF			&$pdf     		Object PDF
	 *   @param		Object		$object			Object to show
	 *   @param		int			$posy			Y
	 *   @param		Translate	$outputlangs	Langs object
	 *   @return	void
	 */
	function _tableau_info(&$pdf, $object, $posy, $outputlangs)
	{
		global $conf,$langs;
		$outputlangs->load("ultimatepdf@ultimatepdf");
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		
		$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:12);	// Height reserved to output the free text on last page
		$heightforfooter = $this->marge_basse + 42;	// Height reserved to output the footer (value include bottom margin)
		$heightforinfotot = 40;	// Height reserved to output the info and total part
		$deltay=$pdf->GetY()+ $tab_height;

		$posy=max($posy,$deltay);
		$curx=$this->marge_gauche;
		$pdf->SetFont('','B', $default_font_size + 1);
		$pdf->RoundedRect($curx, $posy, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $heightforinfotot, 0.1, $round_corner = '1111','S', $this->style, array(200, 210, 234) );

        $pdf->SetXY($curx, $posy);
		$pdf->line($this->marge_gauche, $posy+6, $this->page_largeur-$this->marge_droite, $posy+6);
		$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->marge_gauche,6, $outputlangs->transnoentities("ReplacedMaterial"), 0, 'C', 1);
		
		$pdf->SetXY($curx, $posy);
		$pdf->line($this->marge_gauche, $posy+12, $this->page_largeur-$this->marge_droite, $posy+12);
		$pdf->SetFont('','', $default_font_size - 1);
		$pdf->line($this->posxart, $posy+6, $this->posxart, $posy+40);		
		$pdf->SetXY ($this->posxqty-1, $posy+6);
		$pdf->MultiCell(30,6, $outputlangs->transnoentities("Qty"), 0, 'C', 1);
		
		$pdf->line($this->posxdesc, $posy+6, $this->posxdesc, $posy+40);		
		$pdf->SetXY ($this->posxart-1, $posy+6);
		$pdf->MultiCell(50,6, $outputlangs->transnoentities("ArtCode"), 0, 'C', 1);		
		
		$pdf->SetXY ($this->posxdesc-1, $posy+6);
		$pdf->MultiCell(111,6, $outputlangs->transnoentities("Description"), 0, 'C', 1);
		
		// Loop on each lines
		$objectline = new Product($this->db);
		//$result = $objectline->fetch();
		
		//if (!empty($object->lines[$i]->fk_product)) 
//var_dump($result);exit;
		if ($result)
		{
			$nblines = count($object->lines);
			for ($j = 0 ; $j < $nblines ; $j++)
			{										
				$objectline = $object->lines[$j];
				if (strlen($objectline->ref) >0)
				{
					//posxqty
					$pdf->SetXY ($this->posxqty-1, $posy+14+($j*7));
					$pdf->MultiCell($this->posxart-$this->marge_gauche, 4, $objectline->qty, 0, 'J');
					
					//posxart
					$pdf->SetXY ($this->posxart, $posy+14+($j*7));
					$pdf->MultiCell($this->posxdesc-$this->posxart, 4, $objectline->ref, 0, 'J');
					
					//posxdesc
					$pdf->SetXY ($this->posxdesc, $posy+14+($j*7));
					$pdf->MultiCell(110, 4, $objectline->desc, 0, 'L');

				}
			}
		}
		else
		{
			$pdf->SetXY ($this->posxdesc, $posy+14+($j*6));
			$pdf->MultiCell(50, 4, $outputlangs->transnoentities("PAS DE DONNEES") , 0, 'L');
		}
		return $posy;	
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
		$widthrecbox=$this->page_largeur-$this->marge_gauche-$this->marge_droite;
		
		if (! empty($conf->global->ULTIMATE_DISPLAY_INTER_AGREEMENT_BLOCK))
	    {
			$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:20);	// Height reserved to output the free text on last page
			$heightforfooter = $this->marge_basse + 42;	// Height reserved to output the footer (value include bottom margin)
			$now=dol_now();
			$heightforinfotot = 40;	// Height reserved to output the info and total part
			$deltay=$pdf->GetY() + $heightforinfotot;	
			$posy=max($posy,$deltay);
			$deltax=$this->marge_gauche;
			$pdf->RoundedRect($deltax, $posy, $widthrecbox, $heightforinfotot, 2, $round_corner = '1111', 'S', $this->style, '');
			
			// Example using extrafields
			$title_key=(empty($object->array_options['options_newline']))?'':($object->array_options['options_newline']);
			$extrafields = new ExtraFields ( $this->db );
			$extralabels = $extrafields->fetch_name_optionals_label ( $object->table_element, true );
			if (is_array ( $extralabels ) && key_exists ( 'newline', $extralabels ) && !empty($title_key)) {
				$pdf->SetFont('','B', $default_font_size - 1);
				$pdf->SetXY($deltax, $posy);
				$title = $extrafields->showOutputField ( 'newline', $title_key );
				$pdf->writeHTMLCell($widthrecbox, 4, $this->marge_gauche, $posy-8, $title, 0, 0, false, true, 'L', true);
			$posy=$pdf->GetY()+7;
			}
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($deltax, $posy+5);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->transnoentities('Date')." : " . dol_print_date($now,"day",false,$outputlangs,true),0,'C',0);
			$pdf->SetXY($deltax, $posy+10);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->transnoentities('AgreeWith').' '.$outputlangs->convToOutputCharset($this->emetteur->name),0,'L',0);
			$pdf->SetXY($deltax, $posy+20);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->transnoentities('CustomerOrWhatelse'), 0, 'L', 0);
			$pdf->SetXY($deltax, $posy+30);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->MultiCell($widthrecbox, 3, $outputlangs->transnoentities('SignatureFrNl'), 0, 'L', 0);

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
		global $conf, $object, $langs;	

		// Force to disable hidetop and hidebottom
		$hidebottom=0;
		if ($hidetop) $hidetop=-1;

		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$bgcolor = html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
		$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);

		$outputlangs->load("main");
		$outputlangs->load("interventions");
		$outputlangs->load("ultimatepdf@ultimatepdf");

		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFillColorArray($bgcolor);
		$pdf->SetFont('','', $default_font_size );
		
		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, 0.1, $round_corner = '1111','S', $this->style, array(200, 210, 234) );

		if (empty($hidetop))
		{
			$pdf->SetXY ($this->posxdate-1, $tab_top);
			$pdf->MultiCell($this->posxshifting-$this->marge_gauche,6, $outputlangs->transnoentities("InterDate"), 0, 'C', 1);
		}
		
		$pdf->line($this->posxshifting-1, $tab_top, $this->posxshifting-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY ($this->posxshifting-1, $tab_top);
			$pdf->MultiCell($this->posxhours-$this->posxshifting,6, $outputlangs->transnoentities("InterDeplacement"), 0, 'C', 1);
		}
		
		$pdf->line($this->posxhours-1, $tab_top, $this->posxhours-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY ($this->posxhours-1, $tab_top);
			$pdf->MultiCell($this->posxwaiting-$this->posxhours,6, $outputlangs->transnoentities("InterHeures"), 0, 'C', 1);
		}
		
		$pdf->line($this->posxwaiting-1, $tab_top, $this->posxwaiting-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY ($this->posxwaiting-1, $tab_top);
			$pdf->MultiCell(101,6, $outputlangs->transnoentities("InterAttenteNote"), 0, 'C', 1);
		}	
	}
	
	/**
	 *  Show top header of page.
	 *
	 *  @param	PDF			&$pdf     		Object PDF
	 *  @param  Object		$object     	Object to show
	 *  @param  int	    	$showaddress    0=no, 1=yes
	 *  @param  Translate	$outputlangs	Object lang for output
	 *  @param	string		$titlekey		Translation key to show as title of document
	 *  @return	void
	 */
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $titlekey)
	{
		global $user,$langs,$conf,$mysoc,$user;
		
		if ($conf->ticketsup->enabled)
		{
			dol_include_once("/ticketsup/class/ticketsup.class.php");
			
			$ticketstatic = new Ticketsup($this->db);
			$ticketstatic->fetch($user->id, $track_id, $ref);
			$userstatic=new User($this->db);
			$userstatic->fetch($ticketstatic->fk_user_assign);
		}

		$default_font_size = pdf_getPDFFontSize($outputlangs);

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("interventions");
		$outputlangs->load("ultimatepdf@ultimatepdf");

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);
		$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		$bgcolor = html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
		$bordercolor = html2rgb($conf->global->ULTIMATE_BORDERCOLOR_COLOR);

		//Affiche le filigrane brouillon - Print Draft Watermark
		if($object->statut==0 && (! empty($conf->global->FICHINTER_DRAFT_WATERMARK)) )
		{
            pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->FICHINTER_DRAFT_WATERMARK);
		}

		//Prepare la suite
		$pdf->SetTextColor(0,0,60);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posx=$this->page_largeur-$this->marge_droite-100;
		$posy=$this->marge_haute;

		$pdf->SetXY($this->marge_gauche,$posy);

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
		
		// Entete
		// Example using extrafields for new title of document
		$title_key=(empty($object->array_options['options_newtitle']))?'':($object->array_options['options_newtitle']);	
		$extrafields = new ExtraFields($this->db);
		$extralabels = $extrafields->fetch_name_optionals_label ($object->table_element, true);
		if (is_array($extralabels ) && key_exists('newtitle', $extralabels) && !empty($title_key)) 
		{
			$titlekey = $extrafields->showOutputField ('newtitle', $title_key);
		}
		
		$posx=80;
		$posy=($logo_height+$this->marge_haute)/2;	
		$pdf->SetFont('','B', $default_font_size + 3);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		$title=$outputlangs->transnoentities($titlekey);
		$pdf->MultiCell(100, 3, $title, '', 'L');

		$pdf->SetFont('','B', $default_font_size + 2);

		// Sender properties
		
		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur='';
			$contact_inter='';
			// Add internal contact of proposal if defined
			$arrayidcontact=$object->getIdContact('internal','INTERVENING');
			if (count($arrayidcontact) > 0)
			{
				$object->fetch_user($arrayidcontact[0]);
				$contact_inter = ($contact_inter ? "\n" : '' ).$outputlangs->convToOutputCharset($object->user->getFullName($outputlangs))."\n";
			}

			$carac_emetteur .= pdf_build_address($outputlangs,$this->emetteur);
			$bgcolor = html2rgb($conf->global->ULTIMATE_BGCOLOR_COLOR);
			// Show sender
			$posy=$marge_haute;
			$posx=120;

			// Show sender frame
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetFont('','', $default_font_size + 1);	
			//$pdf->RoundedRect($posx, $posy, 84, $hautcadre, 2, $round_corner = '1111', 'F', '', $bgcolor);
		
			// Show sender name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size + 1);
			$pdf->MultiCell(86,4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'C');

			// Show sender information
			$pdf->SetXY($posx+2,$posy+7);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetTextColorArray($textcolor);	        
			$pdf->MultiCell(86,4, $carac_emetteur, 0, "C");
		
			// Emetteur information
			if ($logo_height<30) 
			{
				$posy=$this->marge_haute+35;
			}
			else
			{
				$posy=$logo_height+$this->marge_haute+5;
			}	
			$posx=$this->marge_gauche;

			$hautcadre=40;
			$pdf->RoundedRect($posx, $posy, 100, $hautcadre, 0.1, $round_corner = '1000', 'S', $this->style, $fill_color=array(''));
			$pdf->RoundedRect($posx, $posy, 100, 6, 0.1, $round_corner = '1111', 'F', $this->style, $bgcolor);
			$pdf->SetXY($posx,$posy);
			$pdf->SetFont('','B', $default_font_size + 1);
			$pdf->MultiCell(86,4, $outputlangs->transnoentities("Informations").' '.$outputlangs->convToOutputCharset($this->emetteur->name), 0, 'C');
			$pdf->line($posx, $posy+6, 110, $posy+6, $this->style);
			$pdf->SetXY($posx,$posy+6);
			$pdf->MultiCell(86,4, $outputlangs->transnoentities("Project N°"), 0, 'L');
			$pdf->SetXY($posx+42,$posy+6);
			$pdf->line($posx, $posy+12, 110, $posy+12, $this->style);
			$pdf->MultiCell(60,4, $outputlangs->convToOutputCharset($object->ref), 0, 'L');
			$pdf->SetXY($posx,$posy+12);
			$pdf->MultiCell(86,4, $outputlangs->transnoentities("Technicien"), 0, 'L');
			$pdf->SetXY($posx+42,$posy+12);
			$pdf->SetFont('','', $default_font_size + 1);
			$pdf->line($posx, $posy+18, 110, $posy+18, $this->style);
			if ($conf->ticketsup->enabled)
			{
				$pdf->MultiCell(60,4, $outputlangs->convToOutputCharset($userstatic->getFullName($outputlangs)), 0, 'L');
			}
			$pdf->SetXY($posx,$posy+18);
			$pdf->SetFont('','B', $default_font_size + 1);
			$pdf->MultiCell(86,4, $outputlangs->transnoentities("CHM"), 0, 'L');
			$pdf->rect($posx+12, $posy+19, 4, 4);
			$pdf->line($posx, $posy+24, 110, $posy+24, $this->style);
			$pdf->SetXY($posx+42,$posy+18);
			$pdf->MultiCell(86,4, $outputlangs->transnoentities("No CHM"), 0, 'L');
			$pdf->rect($posx+60, $posy+19, 4, 4);
			$pdf->line($posx, $posy+30, 110, $posy+30, $this->style);
			$pdf->SetXY($posx,$posy+24);
			$pdf->MultiCell(50,4, $outputlangs->transnoentities("ID suivi"), 0, 'L');
			$pdf->line(50, $posy+6, 50, $posy+30, $this->style);
			$pdf->SetXY($posx+42,$posy+24);
			$pdf->MultiCell(60,4, $ticketstatic->track_id, 0, 'L');
			
			//vertical
			$pdf->SetFont('','B', $default_font_size + 3);
			$pdf->SetXY($posx,$posy+32);
			$pdf->MultiCell($posx+42,4, $outputlangs->transnoentities("Job Ticket"), 0, 'L');
			$pdf->SetXY(60,$posy+32);
			$pdf->MultiCell(60,4, $ticketstatic->ref, 0, 'L');
			$pdf->line(50, $posy+30, 50, $posy+40, $this->style);
			
			// If CUSTOMER contact defined, we use it
			$usecontact=false;
			$arrayidcontact=$object->getIdContact('external','CUSTOMER');
			if (count($arrayidcontact) > 0)
			{
				$usecontact=true;
				$result=$object->fetch_contact($arrayidcontact[0]);
			}

			// Recipient name
			if (! empty($usecontact))
			{
				// On peut utiliser le nom de la societe du contact
				if ($conf->global->MAIN_USE_COMPANY_NAME_OF_CONTACT) $socname = $object->contact->socname;
				else $socname = $object->client->nom;
				$carac_client_name=$outputlangs->convToOutputCharset($socname);
			}
			else
			{
				$carac_client_name=$outputlangs->convToOutputCharset($object->client->name);
			}
	
			$carac_client=pdf_build_address($outputlangs,$this->emetteur,$object->client,$object->contact,$usecontact,'target');

			// Show recipient
			$posx=112;
			if (! empty($conf->global->MAIN_INVERT_SENDER_RECIPIENT)) $posx=$this->marge_gauche;

			// Show recipient frame
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->RoundedRect($posx, $posy, 88, $hautcadre, 0.1, $round_corner = '1000', 'S', $this->style, $fill_color=array(''));
			$pdf->RoundedRect($posx, $posy, 88, 6, 0.1, $round_corner = '1111', 'F', $this->style, $bgcolor);
			$pdf->SetXY($posx,$posy);
			$pdf->SetFont('','B', $default_font_size + 1);
			$pdf->MultiCell(86,4, $outputlangs->transnoentities("CustomerInformation"), 0, 'C');
			$pdf->SetXY($posx,$posy+6);
			$pdf->line($posx, $posy+6, $this->page_largeur-$this->marge_gauche, $posy+6, $this->style);
			//vertical
			$pdf->line($posx+34, $posy+6, $posx+34, $posy+40, $this->style);
			$pdf->line($posx, $posy+12, $posx+34, $posy+12, $this->style);
			$pdf->SetXY($posx,$posy+6);
			$pdf->MultiCell(40,4, $outputlangs->transnoentities("Company"), 0, 'L');
			$pdf->line($posx, $posy+18, $posx+34, $posy+18, $this->style);
			$pdf->SetXY($posx,$posy+12);
			$pdf->MultiCell(40,4, $outputlangs->transnoentities("Contact"), 0, 'L');
			$pdf->line($posx, $posy+34, $posx+34, $posy+34, $this->style);
			$pdf->SetXY($posx,$posy+18);
			$pdf->MultiCell(40,4, $outputlangs->transnoentities("Address"), 0, 'L');
			$pdf->SetXY($posx,$posy+34);
			$pdf->MultiCell(40,4, $outputlangs->transnoentities("Phone"), 0, 'L');
		
			// Show recipient name
			$pdf->SetXY($posx+36,$posy+6);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell(50,4, $carac_client_name, 0, 'L');

			// Show recipient information
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+36,$posy+14);
			$pdf->MultiCell(86,4, $carac_client, 0, 'L');
			
			// Show recipient Tel/portable/mail
			if ($object->client->phone)
			{
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->SetXY($posx+36,$posy+34);
				$pdf->MultiCell(86,4, $object->client->phone, 0, 'L');
			}
			// Show recipient Tel/portable/mail
			if ($object->contact->phone_mobile)
			{
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->SetXY($posx+36,$posy+36);
				$pdf->MultiCell(86,4, $object->contact->phone_mobile, 0, 'L');
			}
			elseif ($object->contact->phone_pro)
			{
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->SetXY($posx+36,$posy+36);
				$pdf->MultiCell(86,4, $object->contact->phone_pro, 0, 'L');
			}
			elseif ($object->contact->phone_perso)
			{
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->SetXY($posx+36,$posy+36);
				$pdf->MultiCell(86,4, $object->contact->phone_perso, 0, 'L');
			}
			// Show recipient Tel/portable/mail
			if ($object->contact->email)
			{
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->SetXY($posx+32,$posy+36);
				//$pdf->MultiCell(86,4, $object->contact->email, 0, 'L');
			}
			
			// Other informations
			// Creation des cases a cocher
			$posy = $pdf->GetY()+17;
			$posx=$this->marge_gauche;
			
			$title_key=(empty($object->array_options['options_newrdv']))?'':($object->array_options['options_newrdv']);
			$extrafields = new ExtraFields ( $this->db );
			$extralabels = $extrafields->fetch_name_optionals_label ( $object->table_element, true );
			if (is_array ( $extralabels ) && key_exists ( 'newrdv', $extralabels ) && !empty($title_key))
			{
				$newrdv = $extrafields->showOutputField ( 'newrdv', $title_key );
			}
			//Si nouvelle intervention à prévoir
			$pdf->rect($posx, $posy-5, 4, 4);
			$pdf->SetXY ($posx, $posy-5);
			
			if ($newrdv == 'Oui')
			{
				$pdf->SetFont('','B', $default_font_size - 1);
				$pdf->MultiCell(10,6, "X", 0, 'L', 0);
			}
			$pdf->SetFont('','', $default_font_size - 1); 
			$pdf->SetXY ($posx+4, $posy-5);
			$pdf->MultiCell(100,6, $outputlangs->transnoentities("NewAppointment"), 0, 'L', 0);
			
			// si l'intervention est close
			$pdf->rect($posx+66, $posy-5, 4, 4);
			$pdf->SetXY ($posx+66, $posy-5);
			
			if ($newrdv == 'Non')
			{
				$pdf->SetFont('','B', $default_font_size - 1);
				$pdf->MultiCell(10,6, "X", 0, 'L', 0);
			}
			$pdf->SetFont('','', $default_font_size - 1); 
			$pdf->SetXY ($posx+70, $posy-5);
			$pdf->MultiCell(100,6, $outputlangs->transnoentities("TestedAndOrder"), 0, 'L', 0);
			
			$pdf->SetXY ($posx+126, $posy-5);
			$pdf->MultiCell(40, 6, $outputlangs->transnoentities("VATIntra").' : ', 0, 'L', false);
			if ($object->client->tva_intra) 
			{ 
				$pdf->SetFont('','', $default_font_size - 1); 
				$pdf->SetXY($posx+140, $posy-5);
				$pdf->SetTextColorArray($textcolor);
				$pdf->MultiCell(60, 6, $outputlangs->transnoentities($object->client->tva_intra), '0', 'C'); 
			} 
			else 
			{ 
				$pdf->SetFont('','', $default_font_size - 1); 
				$pdf->SetXY($posx+140, $posy-5);
				$pdf->SetTextColorArray($textcolor); 
				$pdf->SetFillColor(255,255,255); 
				$pdf->MultiCell(60, 6, '', '0', 'C'); 					
			} 		
		}
	}			

	/**
	 *   	Show footer of page
	 *   	@param      pdf     		PDF factory
	 * 		@param		object			Object invoice
	 *      @param      outputlangs		Object lang for output
	 * 		@remarks	Need this->emetteur object
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_ultimatepagefoot($pdf,$outputlangs,'FICHINTER_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext, $footertextcolor);

	}

}

?>