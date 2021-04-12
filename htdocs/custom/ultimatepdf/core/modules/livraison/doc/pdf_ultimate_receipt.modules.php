<?php
/* Copyright (C) 2004-2008 Laurent Destailleur   <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2009 Regis Houssin         <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2017 Philippe Grand        <philippe.grand@atoo-net.com>
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
 *	\file       ultimatepdf/core/modules/livraison/pdf/pdf_ultimate_receipt.modules.php
 *	\ingroup    livraison
 *	\brief      File of class to manage receving receipts with template ultimate_receipt
 */

require_once DOL_DOCUMENT_ROOT.'/core/modules/livraison/modules_livraison.php';
require_once(DOL_DOCUMENT_ROOT."/product/class/product.class.php");
require_once DOL_DOCUMENT_ROOT.'/livraison/class/livraison.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
dol_include_once("/ultimatepdf/lib/ultimatepdf.lib.php");


/**
 *	\class      pdf_ultimate_receipt
 *	\brief      Classe permettant de generer les bons de livraison au modele Typho
 */

class pdf_ultimate_receipt extends ModelePDFDeliveryOrder
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
	public function __construct($db)
	{
		global $conf,$langs,$mysoc;

		$langs->load("main");
		$langs->load("bills");
		$langs->load("sendings");
		$langs->load("companies");
		$langs->load("ultimatepdf@ultimatepdf");

		$this->db = $db;
		$this->name = "ultimate_receipt";
		$this->description = $langs->trans("DocumentDesignUltimate_receipt");

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

		$this->option_logo = 1;                    // Affiche logo FAC_PDF_LOGO
		$this->option_codeproduitservice = 1;      // Affiche code produit-service
		$this->option_multilang = 1;               // Dispo en plusieurs langues
		$this->option_freetext = 1;				   // Support add of a personalised text

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
		if (! empty($conf->global->ULTIMATE_RECEIPTS_WITH_LINE_NUMBER))
		{
			$this->posxnumber=$this->marge_gauche+1;
			$this->number_width = 10;
		}
		else
		{
			$this->posxnumber=0;
			$this->number_width = 0;
		}
		if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes")
		{
			$this->posxref=$this->marge_gauche+$this->number_width;
			$this->posxdesc=$this->posxref + (isset($conf->global->ULTIMATE_DOCUMENTS_WITH_REF_WIDTH)?$conf->global->ULTIMATE_DOCUMENTS_WITH_REF_WIDTH:22);		
		}
		else
		{			
			$this->posxdesc=$this->marge_gauche+$this->number_width;	
		}
		
		$this->posxqtyordered=140;
		$this->posxqtytoship=160;
		$this->posxreliquat=180;
		if (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)) $this->posxpicture=$this->posxqtyordered;
		$this->posxpicture=$this->posxqtyordered-1 - (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)?20:$conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH);	// width of images		
		if ($this->page_largeur < 210) // To work with US executive format
		{
			$this->posxdesc-=20;
			$this->posxpicture-=20;
			$this->posxqtyordered-=20;
			$this->posxqtytoship-=20;
			$this->posxreliquat-=20;
		}
		
		$this->atleastoneref=0;
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
		global $user,$langs,$conf,$hookmanager;
		
		$object->fetch_thirdparty();
		
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}

		if (! is_object($outputlangs)) $outputlangs=$langs;

		$outputlangs->load("main");
		$outputlangs->load("dict");
		$outputlangs->load("companies");
		$outputlangs->load("bills");
		$outputlangs->load("products");
		$outputlangs->load("deliveries");
		$outputlangs->load("sendings");
		$outputlangs->load("ultimatepdf@ultimatepdf");
		
		$nblines = count($object->lines);
		
		// Loop on each lines to detect if there is at least one image to show
		$realpatharray=array();
		if (! empty($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_PICTURE))
		{
			$objphoto = new Product($this->db);

			for ($i = 0 ; $i < $nblines ; $i++)
			{
				if (empty($object->lines[$i]->fk_product)) continue;

				$objphoto->fetch($object->lines[$i]->fk_product);

				if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))
				{
					$pdir[0] = get_exdir($objphoto->id,2,0,0,$objphoto,'product') . $objphoto->id ."/photos/";
					$pdir[1] = get_exdir(0,0,0,0,$objphoto,'product') . dol_sanitizeFileName($objphoto->ref).'/';
				}
				else
				{
					$pdir[0] = get_exdir(0,0,0,0,$objphoto,'product') . dol_sanitizeFileName($objphoto->ref).'/';				// default
					$pdir[1] = get_exdir($objphoto->id,2,0,0,$objphoto,'product') . $objphoto->id ."/photos/";	// alternative
				}

				$arephoto = false;
				foreach ($pdir as $midir)
				{
					if (! $arephoto)
					{
						$dir = $conf->product->dir_output.'/'.$midir;

						foreach ($objphoto->liste_photos($dir,1) as $key => $obj)
						{
							if (empty($conf->global->CAT_HIGH_QUALITY_IMAGES))		// If CAT_HIGH_QUALITY_IMAGES not defined, we use thumb if defined and then original photo
							{
								if ($obj['photo_vignette'])
								{
									$filename= $obj['photo_vignette'];
								}
								else
								{
									$filename=$obj['photo'];
								}
							}
							else
							{
								$filename=$obj['photo'];
							}

							$realpath = $dir.$filename;
							$arephoto = true;
						}
					}
				}
				
				if ($realpath && $arephoto) $realpatharray[$i]=$realpath;
			}
		}
		
		if (count($realpatharray) == 0) $this->posxpicture=$this->posxqtyordered;

		if ($conf->expedition->dir_output)
		{
			// Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->expedition->dir_output."/receipt";
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->expedition->dir_output."/receipt/" . $objectref;
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
                $heightforinfotot = 50;	// Height reserved to output the info and total part
		        $heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
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

				// Complete object by loading several other informations
				$expedition=new Expedition($this->db);
				$result = $expedition->fetch($object->expedition_id);

				$commande = new Commande($this->db);
				if ($expedition->origin == 'commande')
				{
					$commande->fetch($expedition->origin_id);
				}
				$object->commande=$commande;


				$pdf->Open();
				$pagenb=0;
				$pdf->SetDrawColor(128,128,128);
				
				//Generation de l entete du fichier
				$pdf->SetTitle($outputlangs->convToOutputCharset($object->ref));
				$pdf->SetSubject($outputlangs->transnoentities("DeliveryOrder"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("DeliveryOrder"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				
				// Positionne $this->atleastoneref si on a au moins une ref 
				for ($i = 0 ; $i < $nblines ; $i++)
				{
					if ($object->lines[$i]->product_ref)
					{
						$this->atleastoneref++;
					}
				}
				
				if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes")
				{
					if (!empty($this->atleastoneref))
					{
						$this->posxref=$this->marge_gauche+$this->number_width;
						$this->posxdesc=$this->posxref + (isset($conf->global->ULTIMATE_DOCUMENTS_WITH_REF_WIDTH)?$conf->global->ULTIMATE_DOCUMENTS_WITH_REF_WIDTH:22);
					}
					else
					{
						$this->posxdesc=$this->marge_gauche+$this->number_width;
					}				
				}
				else
				{	
					$this->posxdesc=$this->marge_gauche+$this->number_width;
				}

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColorArray($textcolor);
				
				//catch logo height
				$logo_height=pdf_getUltimateHeightForLogo($logo);
				$hautcadre=46;
				
				if ($logo_height<30) 
				{
					$tab_top=$this->marge_haute+30+$hautcadre+17;
				}
				else
				{
					$tab_top = $this->marge_haute+$logo_height+$hautcadre+17;
				}	
				$tab_top_newpage = (empty($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD)?$this->marge_haute+$logo_height+8:10);
				$tab_height = 130;
				$tab_height_newpage = 150;
				
				// Incoterm
				$height_incoterms = 0;
				if ($conf->incoterm->enabled)
				{
					$desc_incoterms = $object->getIncotermsForPDF();
					if ($desc_incoterms)
					{
						if ($logo_height<30) 
						{
							$tab_top=$this->marge_haute+30+$hautcadre+17;
						}
						else
						{
							$tab_top = $this->marge_haute+$logo_height+$hautcadre+17;
						}

						$pdf->SetFont('','', $default_font_size - 2);
						$pdf->writeHTMLCell(190, 3, $this->posxdesc-1, $tab_top-1, dol_htmlentitiesbr($desc_incoterms), 0, 1);
						$nexY = $pdf->GetY();
						$height_incoterms=$nexY-$tab_top;

						// Rect prend une longueur en 3eme param
						$pdf->SetDrawColor(192,192,192);
						$pdf->Rect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_incoterms+1);

						$tab_top = $nexY+2;
						$height_incoterms += 4;
					}
				}

				// Affiche notes
				$notetoshow=empty($object->note_public)?'':$object->note_public;
				if (! empty($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_SHIPMENT_NOTE))
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
						$tab_top=$this->marge_haute+30+$hautcadre+17+$height_incoterms;
					}
					else
					{
						$tab_top = $this->marge_haute+$logo_height+$hautcadre+17+$height_incoterms;
					}		

					$pdf->SetFont('','', $default_font_size - 2);   // Dans boucle pour gerer multi-page				
					if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF != "yes")
					{
						$pdf->writeHTMLCell(190, 3, $this->posxdesc-$this->posxnumber+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					}
					else
					{
						if (!empty($this->atleastoneref))
						{
							$pdf->writeHTMLCell(190, 3, $this->posxref-$this->posxnumber+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
						}
						else
						{
							$pdf->writeHTMLCell(190, 3, $this->posxdesc-$this->posxnumber+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
						}
					}		
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme param
					$pdf->SetDrawColor(192,192,192);
					$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $height_note+1, 2, $round_corner = '1111', 'S', $this->style, array(''));

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

				// Loop on each lines
				$line_number=1;
				for ($i = 0 ; $i < $nblines ; $i++)
				{
					$curY = $nexY;
					$pdf->SetFont('','', $default_font_size - 1);   // Into loop to work with multipage
					$pdf->SetTextColorArray($textcolor);

					// Define size of image if we need it
					$imglinesize=array();
					if (! empty($realpatharray[$i])) $imglinesize=pdf_getSizeForImage($realpatharray[$i]);

					$pdf->setTopMargin($tab_top_newpage);
					//If we aren't on last lines footer space needed is on $heightforfooter
					if ($i != $nblines-1)
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
					
					$showpricebeforepagebreak=1;
					$posYAfterImage=0;
					$posYStartDescription=0;
					$posYAfterDescription=0;
					$posYafterRef=0;	
					
					// We start with Photo of product line
					if (isset($imglinesize['width']) && isset($imglinesize['height']) && ($curY + $imglinesize['height']) > ($this->page_hauteur-$bMargin))	// If photo too high, we moved completely on new page
					{
						$pdf->AddPage('','',true);
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						if (empty($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="SendingSheet");
						$pdf->setPage($pageposbefore+1);	
						
						$curY = $tab_top_newpage;
						$showpricebeforepagebreak=1;					
					}
					
					$picture=false;
					if (isset($imglinesize['width']) && isset($imglinesize['height']))
					{	
						$curX = $this->posxpicture-1;
						$pdf->Image($realpatharray[$i], $curX, $curY, $imglinesize['width'], $imglinesize['height'], '', '', '', 2, 300,'',false,false,0,false,false,true);	// Use 300 dpi
						// $pdf->Image does not increase value return by getY, so we save it manually
						$posYAfterImage=$curY+$imglinesize['height'];
						$picture=true;
					}		
					
					if ($picture) 
					{
						$nexY=$posYAfterImage;
					}
					
					// Description of product line
					$curX = $this->posxdesc;
					$text_length=($picture?$this->posxpicture:$this->posxqtyordered);      			

                    $pdf->startTransaction();			
                    if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF != "yes")
					{
						pdf_writelinedesc($pdf,$object,$i,$outputlangs,$text_length-$curX,3,$curX,$curY,$hideref,$hidedesc);
						$pageposafter=$pdf->getPage();
						if ($pageposafter > $pageposbefore)	// There is a pagebreak
						{
							$pdf->rollbackTransaction(true);
							$pageposafter=$pageposbefore;
							$posYStartDescription=$curY;
							$pdf->setPageOrientation('', 1, $bMargin);	// The only function to edit the bottom margin of current page to set it.
							pdf_writelinedesc($pdf,$object,$i,$outputlangs,$text_length-$curX,3,$curX,$curY,$hideref,$hidedesc);
							$posYAfterDescription=$pdf->GetY();
							$pageposafter=$pdf->getPage();

							if ($posYAfterDescription > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
							{
								if ($i == ($nblignes-1))	// No more lines, and no space left to show total, so we create a new page
								{
									$pdf->AddPage('','',true);
									if (! empty($tplidx)) $pdf->useTemplate($tplidx);
									if (empty($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
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
					}
					else
					{
						if (!empty($this->atleastoneref))
						{
							$curX = $this->posxdesc-1;
						}
						$break_on_ref=false;
						$break_on_desc=false;

						if (!empty($object->lines[$i]->fk_product)) 
						{
							$posYStartRef=$pdf->GetY();
							pdf_writelinedesc_ref($pdf,$object,$i,$outputlangs,$this->posxdesc-$this->posxref,3,$this->marge_gauche+$this->number_width,$curY,$hideref,$hidedesc,0,'ref');
							$posYafterRef=$pdf->GetY();
							$pageposafterRef=$pdf->getPage();
							
							if ($posYafterRef<$posYStartRef && $pageposafterRef>$pageposbefore) 
							{
								$break_on_ref=true;
							}
						}
						else 
						{
							$posYafterRef=$curY;
						}
						
						$page_current=$pdf->getPage();
						
						if($page_current==$pageposbefore) 
						{
							if (!empty($object->lines[$i]->fk_product)) {
								$posYStartDescription = $posYStartRef;
							}
							else{
								$posYStartDescription = $curY;
							}
							$pdf->setY($posYStartDescription);
							$posYStartDescription=$curY;
							pdf_writelinedesc_ref($pdf,$object,$i,$outputlangs,$text_length-$curX,3,$curX,$curY,$hideref,$hidedesc,0,'label');
							$posYAfterDescription=$pdf->GetY();
							
							if($posYafterRef>$posYAfterDescription && $page_current==$pageposbefore) $pdf->setY($pdf->GetY()+$posYafterRef-$posYAfterDescription); //évite le chevauchement quand $posYafterRef>$posYAfterDescription
							$pageposafterdesc=$pdf->getPage();						

							if ($posYAfterDescription<$posYStartDescription && $pageposafterdesc>$pageposbefore) 
							{
								$break_on_desc=true;
							}
						} 
						else 
						{
							$break_on_ref=false;
						}	
					
						$pageposafter=$pdf->getPage();
					
                    if ($pageposafter > $pageposbefore)	// There is a pagebreak
						{
							$posYAfterImage=$tab_top_newpage+$imglinesize['height'];
							$pdf->rollbackTransaction(true);
							$showpricebeforepagebreak=1;
							$pdf->setPageOrientation('', 1, $bMargin);	// The only function to edit the bottom margin of current page to set it.
							
							//Settings for jalon with pagebreak
							if (($conf->milestone->enabled) && (isset($object->lines[$i]->pagebreak) && $object->lines[$i]->pagebreak)) 
							{					
								$curY=$tab_top_newpage+1;
							}
							if (!empty($object->lines[$i]->fk_product)) 
							{
								$posYStartRef=$pdf->GetY();	
								$pageposStartRef=$pdf->getPage();
								pdf_writelinedesc_ref($pdf,$object,$i,$outputlangs,$this->posxdesc-$this->posxref,3,$this->marge_gauche+$this->number_width,$curY,$hideref,$hidedesc,0,'ref');
								$posYafterRef=$pdf->GetY();										
								$pageposafterRef=$pdf->getPage();	
								
								if ($posYafterRef<$posYStartRef && $pageposafterRef>$pageposbefore) 
								{
									$break_on_ref=true;
								}
							}
							else 
							{
								$posYafterRef=$curY;
							}
							
							$page_current=$pdf->getPage();
							
							$posYStartDescription=0;	
							if (!empty($object->lines[$i]->fk_product) && $object->lines[$i]->product_type != 9)	
							{						
								$pdf->setPage($pageposStartRef); 
							}
							$posYStartDescription=$curY;
							pdf_writelinedesc_ref($pdf,$object,$i,$outputlangs,$text_length-$curX,3,$curX,$curY,$hideref,$hidedesc,0,'label');
							$posYAfterDescription=$pdf->GetY();

							if($posYafterRef>$posYAfterDescription && $page_current==$pageposafter) $pdf->setY($pdf->GetY()+$posYafterRef-$posYAfterDescription); //évite le chevauchement quand $posYafterRef>$posYAfterDescription

							$pageposafter=$pdf->getPage();
							if ($posYAfterDescription > ($this->page_hauteur - $bMargin))	// There is no space left for total+free text
							{
								if ($i == ($nblignes-1))	// No more lines, and no space left to show total, so we create a new page
								{
									$pdf->AddPage('','',true);
									if (! empty($tplidx)) $pdf->useTemplate($tplidx);
									if (empty($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
									$pdf->setPage($pageposafter+1);
								}
							}
							elseif ($pageposafter==$pageposbefore || ($pageposafter>$pageposbefore && $posYAfterDescription<$curY) && $posYStartDescription>$this->page_hauteur - $bMargin)	
							{																
								// We found a page break
								$showpricebeforepagebreak=1;
							}
						}
						else	// No pagebreak
						{
							$pdf->commitTransaction();
						}
					}
					$posYAfterDescription=$pdf->GetY();
					
					if ($posYAfterImage > $posYAfterDescription)
					{
						$nexY=$posYAfterImage;					
					}
					else
					{
						$nexY = $pdf->GetY();
					}
					$pageposafter=$pdf->getPage();					
					$pdf->setPage($pageposbefore);
					$pdf->setTopMargin($this->marge_haute);
					$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.	
					
					// We suppose that a too long description or photo were moved completely on next page
					if ($pageposafter>$pageposbefore && empty($showpricebeforepagebreak)) 
					{
						$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
					}
					if ($pageposafterRef>$pageposbefore && $posYafterRef < $posYStartRef)
					{
						$pdf->setPage($pageposbefore); $showpricebeforepagebreak=1;
					}
					if ($nexY>$curY && $pageposafter>$pageposbefore)	
					{
						$pdf->setPage($pageposafter); $curY = $tab_top_newpage+1;
					}
					
					$pdf->SetFont('','', $default_font_size - 2);   // On repositionne la police par defaut
					
					if ($posYStartDescription>$posYAfterDescription && $pageposafter>$pageposbefore)
					{
						$pdf->setPage($pageposbefore); $curY = $posYStartDescription;
					}
					if ($curY+2>($this->page_hauteur - $bMargin))	
					{			
						$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
					}
					
					//Line numbering
					if (! empty($conf->global->ULTIMATE_RECEIPTS_WITH_LINE_NUMBER))
					{
						if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes" && $object->lines[$i]->product_type != 9 )
                        {
                            if (array_key_exists($i,$object->lines))
                            {
                                $pdf->SetXY($this->posxnumber, $curY);
                                $pdf->MultiCell($this->posxref-$this->posxnumber-0.8, 3, $line_number, 0, "C");
								$line_number++;
                            }
                        }
                        elseif ($object->lines[$i]->product_type != 9)
                        {
                            if (array_key_exists($i,$object->lines))
                            {
                                $pdf->SetXY($this->posxnumber, $curY);
                                $pdf->MultiCell($this->posxdesc-$this->posxnumber-0.8, 3, $line_number, 0, "C");
								$line_number++;
                            }
                        }
					}

					// Quantity
					$pdf->SetXY($this->posxqtyordered, $curY);
					$pdf->MultiCell(($this->posxqtytoship - $this->posxqtyordered), 3, $object->lines[$i]->qty_asked,'','C');
					
					// Remaining to ship
					$pdf->SetXY($this->posxqtytoship, $curY);
					$pdf->MultiCell(($this->posxreliquat - $this->posxqtytoship), 3, $object->lines[$i]->qty_shipped,'','C');
					
					// reliquat after ship
					$pdf->SetXY($this->posxreliquat, $curY);
					$pdf->MultiCell(($this->page_largeur - $this->marge_droite - $this->posxreliquat), 3, $object->lines[$i]->qty_asked - $object->lines[$i]->qty_shipped,'','C');

					// Add line
					if (! empty($conf->global->ULTIMATE_RECEIPT_PDF_DASH_BETWEEN_LINES) && $i < ($nblines - 1))
					{
						$pdf->SetLineStyle(array('dash'=>'1,1','color'=>array(210,210,210)));
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
							$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1);
						}
						$this->_pagefoot($pdf,$object,$outputlangs,1);
						$pagenb++;
						$pdf->setPage($pagenb);
						$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
						if (empty($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
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
						if (empty($conf->global->ULTIMATE_RECEIPT_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
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

				// Affiche zone agreement
				$posy=$this->_agreement($pdf, $object, $posy, $outputlangs);

				// Insertion du pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();

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
				$this->error=$langs->transnoentities("ErrorCanNotCreateDir",$dir);
				return 0;
			}
		}
		else
		{
			$this->error=$langs->transnoentities("ErrorConstantNotDefined","LIVRAISON_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->transnoentities("ErrorUnknown");
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
		global $conf,$mysoc,$langs;
		
		$default_font_size = pdf_getPDFFontSize($outputlangs);	
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}
		$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
		
		if (! empty($conf->global->ULTIMATE_DISPLAY_RECEIPTS_AGREEMENT_BLOCK))
	    {
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetTextColorArray($textcolor);
			// Cadres signatures
			$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:5);	// Height reserved to output the free text on last page
			$heightforfooter = $this->marge_basse + 12;	// Height reserved to output the footer (value include bottom margin)
			$heightforinfotot = 40;	// Height reserved to output the info and total part
			$deltay=$this->page_hauteur-$heightforfreetext-$heightforfooter-$heightforinfotot;
			$cury=max($cury,$deltay);
			$deltax=$this->marge_gauche;

			$pdf->RoundedRect($deltax, $cury, $widthrecbox, 40, 2, $round_corner = '1111', 'S', $this->style, array());
			$pdf->SetFont('','B', $default_font_size - 1);
			$pdf->SetXY($deltax, $cury);
			$titre = $outputlangs->transnoentities("For").' '.$outputlangs->convToOutputCharset($mysoc->name);
			$pdf->MultiCell(80, 5, $titre, 0, 'L',0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($deltax, $cury+5);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(90, 3, "",0,'L',0);
			$pdf->SetXY($deltax, $cury+12);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $langs->transnoentities("DocORDER3"), 0, 'L', 0);
			$pdf->SetXY($deltax, $cury+17);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $langs->transnoentities("DocORDER5"), 0, 'L', 0);

			$cury=max($cury,$deltay);
			$deltax=$this->marge_gauche+$widthrecbox+4;

			$pdf->RoundedRect($deltax, $cury, $widthrecbox, 40, 2, $round_corner = '1111', 'S', $this->style, array());
			$pdf->SetFont('','B', $default_font_size - 1);
			$pdf->SetXY($deltax, $cury);
			$titre = $outputlangs->trans("ForCustomer");
			$pdf->MultiCell(80, 5, $titre, 0, 'L',0);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($deltax, $cury+5);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(90, 3, "",0,'L',0);
			$pdf->SetXY($deltax, $cury+12);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $langs->transnoentities("DocORDER3"), 0, 'L', 0);
			$pdf->SetXY($deltax, $cury+17);
			$pdf->SetFont('','I', $default_font_size - 2);
			$pdf->MultiCell(80, 3, $langs->transnoentities("DocORDER4"), 0, 'L', 0);
			
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
		global $conf, $langs;
		
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

		// Rect prend une longueur en 3eme param
		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, 2, $round_corner = '0110', 'S', $this->style, array());
		
		if (! empty($conf->global->ULTIMATE_RECEIPTS_WITH_LINE_NUMBER))
		{
			if (empty($hidetop))
			{
				if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes")
				{
					$pdf->SetXY ($this->posxnumber-1, $tab_top);
					$pdf->MultiCell($this->posxref-$this->posxnumber+2,8, $outputlangs->transnoentities("Numbering"), 0, 'C', 1);
				}
				else
				{
					$pdf->SetXY ($this->posxnumber-1, $tab_top);
					$pdf->MultiCell($this->posxdesc-$this->posxnumber+2,8, $outputlangs->transnoentities("Numbering"), 0, 'C', 1);
				}			
			}
			$pdf->line($this->posxnumber+9, $tab_top, $this->posxnumber+9, $tab_top + $tab_height);
		}
		
		// line prend une position y en 3eme param
		if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes" && ! empty($this->atleastoneref))
		{
			$pdf->line($this->posxdesc-1, $tab_top, $this->posxdesc-1, $tab_top + $tab_height);
		}
		if (empty($hidetop))
		{		
			if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes")
			{	
				if (!empty($this->atleastoneref))
				{
					$pdf->SetXY ($this->posxref, $tab_top);
					$pdf->MultiCell($this->posxdesc-$this->posxref,8, $outputlangs->transnoentities("Ref"), 0, 'L', 1);
					$pdf->SetXY ($this->posxdesc, $tab_top);
					$pdf->MultiCell($this->posxqtyordered-$this->posxdesc,8, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
				}
				else
				{
					$pdf->SetXY ($this->posxdesc, $tab_top);
					$pdf->MultiCell($this->posxqtyordered-$this->marge_gauche,8, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
				}
			}
			else			
			{			
				$pdf->SetXY ($this->posxdesc, $tab_top);
				$pdf->MultiCell($this->posxqtyordered-$this->marge_gauche,8, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
			}
		}
		
		if (!empty($conf->global->ULTIMATE_GENERATE_RECEIPTS_WITH_PICTURE))
		{
			if (empty($hidetop))
			{
				//
			}
		}		

		// Modif SEB pour avoir une col en plus pour les commentaires clients
		/*$pdf->line($this->posxcomm, $tab_top, $this->posxcomm, $tab_top + $tab_height);
		if (empty($hidetop)) 
		{
			$pdf->SetXY($this->posxcomm, $tab_top);
			$pdf->MultiCell($this->posxqty - $this->posxcomm,6, $outputlangs->transnoentities("Comments"),0,'L',1);
		}*/

		$pdf->line($this->posxqtyordered-1, $tab_top, $this->posxqtyordered-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxqtyordered-1, $tab_top);
			$pdf->MultiCell(($this->posxqtytoship - $this->posxqtyordered), 8, $outputlangs->transnoentities("QtyOrdered"),'','C',1);
		}

		$pdf->line($this->posxqtytoship-1, $tab_top, $this->posxqtytoship-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxqtytoship-1, $tab_top);
			$pdf->MultiCell(($this->posxreliquat - $this->posxqtytoship), 8, $outputlangs->transnoentities("QtyShipped"),'','C',1);
		}
		
		$pdf->line($this->posxreliquat-1, $tab_top, $this->posxreliquat-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxreliquat-1, $tab_top);
			$pdf->MultiCell(($this->page_largeur - $this->marge_droite - $this->posxreliquat+1), 8, $outputlangs->transnoentities("Reliquat"),'','C',1);
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

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);
		
		// Show Draft Watermark
		if($object->statut==0 && (! empty($conf->global->COMMANDE_DRAFT_WATERMARK)) )
		{
			pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->COMMANDE_DRAFT_WATERMARK);
		}

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

		// Entete
		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFont('','B', $default_font_size + 2);
		$pdf->SetXY($posx,$posy);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("DeliveryOrder")." ".$outputlangs->convToOutputCharset($object->ref), '', 'R');

		$pdf->SetFont('','',$default_font_size + 2);

		$posy+=5;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		if ($object->date_valid)
		{
			$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Date")." : " . dol_print_date(($object->date_delivery?$object->date_delivery:$date->valid),"%d %b %Y",false,$outputlangs,true), '', 'R');
		}
		else
		{
			$pdf->SetTextColor(255,0,0);
			$pdf->MultiCell(100, 4, $outputlangs->transnoentities("DeliveryNotValidated"), '', 'R');
			$pdf->SetTextColorArray($textcolor);
		}

		if ($object->thirdparty->code_client)
		{
			$posy+=5;
			$pdf->SetXY($posx,$posy);
			$pdf->SetTextColorArray($textcolor);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("CustomerCode")." : " . $outputlangs->transnoentities($object->thirdparty->code_client), '', 'R');
		}

		$pdf->SetTextColorArray($textcolor);

		$posy+=2;

		// Add list of linked orders on shipment
		// Currently not supported by pdf_writeLinkedObjects, link for delivery to order is done through shipment)
		if ($object->origin == 'expedition' || $object->origin == 'shipping')
		{
			$Yoff=$posy-5;

			include_once DOL_DOCUMENT_ROOT.'/expedition/class/expedition.class.php';
			$shipment = new Expedition($this->db);
			$shipment->fetch($object->origin_id);

		    $origin 	= $shipment->origin;
			$origin_id 	= $shipment->origin_id;

			if ($conf->$origin->enabled)
			{
				$outputlangs->load('orders');

				$classname = ucfirst($origin);
				$linkedobject = new $classname($this->db);
				$result=$linkedobject->fetch($origin_id);
				if ($result >= 0)
				{
					$pdf->SetFont('','', $default_font_size - 2);
					$text=$linkedobject->ref;
					if ($linkedobject->ref_client) $text.=' ('.$linkedobject->ref_client.')';
					$Yoff = $Yoff+8;
					$pdf->SetXY($this->page_largeur - $this->marge_droite - 100,$Yoff);
					$pdf->MultiCell(100, 2, $outputlangs->transnoentities("RefOrder") ." : ".$outputlangs->transnoentities($text), 0, 'R');
					$Yoff = $Yoff+3;
					$pdf->SetXY($this->page_largeur - $this->marge_droite - 60,$Yoff);
					$pdf->MultiCell(60, 2, $outputlangs->transnoentities("OrderDate")." : ".dol_print_date($linkedobject->date,"day",false,$outputlangs,true), 0, 'R');
				}
			}

			$posy=$Yoff;
		}
		
		// Show list of linked objects
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);

		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur = pdf_build_address($outputlangs, $this->emetteur, $object->thirdparty);

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
			$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
			
			// Show sender frame
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', '', $bgcolor );
			$pdf->SetAlpha(1);

			// Show sender name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B',$default_font_size);
			$pdf->MultiCell($widthrecbox, 4, $outputlangs->convToOutputCharset($this->emetteur->name), 0, 'L');
			$posy=$pdf->getY();

			// Show sender information
			$pdf->SetXY($posx+2,$posy);
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetTextColorArray($textcolor);
			$pdf->MultiCell($widthrecbox-5,4, $carac_emetteur, 0, "L");

			// If SHIPPING contact defined on invoice, we use it
			$usecontact=false;
			$arrayidcontact=$object->commande->getIdContact('external','SHIPPING');
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

			$carac_client=pdf_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

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

			// Show recipient frame
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($posx, $posy, $widthrecbox, $hautcadre, 2, $round_corner = '1111', 'FD', '', $bgcolor );
			$pdf->SetAlpha(1);

			// Show customer/recipient name
			$pdf->SetXY($posx+2,$posy+3);
			$pdf->SetFont('','B', $default_font_size);
			$pdf->MultiCell($widthrecbox,4, $carac_client_name, 0, 'L');

			// Show recipient information
			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetXY($posx+2,$posy+4+(dol_nboflines_bis($carac_client_name,50)*4));
			$pdf->MultiCell($widthrecbox,4, $carac_client, 0, 'L');
		}
		$pdf->SetTextColorArray($textcolor);
	}

	/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *		@param	PDF			&$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	void
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_ultimatepagefoot($pdf,$outputlangs,'DELIVERY_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext, $footertextcolor);
	}

}

?>