<?php
/* Copyright (C) 2011-2012 	Regis Houssin  	<regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2017 	Philippe Grand 	<philippe.grand@atoo-net.com>
 * Copyright (C) 2012 		Juanjo Menent 	<jmenent@2byte.es>
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
 * or see http://www.gnu.org/
 */

/**
 *	\file       htdocs/custom/core/modules/propale/pdf_ultimate_propal2.modules.php
 *  \ingroup    propale
 *  \brief      Fichier de la classe permettant de generer les propales au modele ultimate_propal2 
 */

dol_include_once('/ultimatepdf/core/modules/propale/doc/pdf_ultimate_propal1.modules.php');

/**
 *	Class to generate PDF proposal pdf_ultimate_propal2
 */
class pdf_ultimate_propal2 extends pdf_ultimate_propal1
{

    /**
	 *	Constructor
	 *
	 *  @param		DoliDB		$db      Database handler
	 */
    public function __construct($db)
    {
        global $conf,$langs,$mysoc;

		parent::__construct($db);
		
        $this->name = "ultimate_propal2";
        $this->description = $langs->trans('PDFUltimate_propal2Description');
		$_SESSION['ultimatepdf_model'] = true;
		
		if($conf->global->PRODUCT_USE_UNITS)
		{
			$this->posxtva=94;
			$this->tva_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH;
			$this->posxup=$this->posxtva+$this->tva_width;
			$this->up_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_UP_WIDTH)?20:$conf->global->ULTIMATE_DOCUMENTS_WITH_UP_WIDTH;
			$this->posxdiscount=$this->posxtva+$this->tva_width+$this->up_width;
			$this->discount_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH;
			$this->posxupafter=$this->posxtva+$this->tva_width+$this->up_width+$this->discount_width;
			$this->upafter_width=$this->up_width;
			$this->posxqty=$this->posxtva+$this->tva_width+$this->up_width+$this->discount_width+$this->upafter_width;
			$this->qty_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH;
			$this->posxunit=$this->posxtva+$this->tva_width+$this->up_width+$this->discount_width+$this->upafter_width+$this->qty_width;
			$this->unit_width=10;
		}
		else
		{
			$this->posxtva=106;
			$this->tva_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH;
			$this->posxup=$this->posxtva+$this->tva_width;
			$this->up_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_UP_WIDTH)?20:$conf->global->ULTIMATE_DOCUMENTS_WITH_UP_WIDTH;
			$this->posxdiscount=$this->posxtva+$this->tva_width+$this->up_width;
			$this->discount_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH;
			$this->posxupafter=$this->posxtva+$this->tva_width+$this->up_width+$this->discount_width;
			$this->upafter_width=$this->up_width;
			$this->posxqty=$this->posxtva+$this->tva_width+$this->up_width+$this->discount_width+$this->upafter_width;
			$this->qty_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH;
			$this->unit_width=0;
		}
		$this->postotalht=$this->posxtva+$this->tva_width+$this->up_width+$this->discount_width+$this->upafter_width+$this->qty_width+$this->unit_width;
		if (! ($conf->global->ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT == "no" && empty($conf->global->ULTIMATE_SHOW_HIDE_VAT_COLUMN))) $this->posxtva=$this->posxup;
		$this->posxpicture=$this->posxtva-1 - (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)?20:$conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH);	// width of images
		if ($this->page_largeur < 210) // To work with US executive format
		{
			$this->posxdesc-=20;
			$this->posxpicture-=20;
			$this->posxtva-=20;
			$this->posxup-=20;
			$this->posxdiscount-=20;
			$this->posxupafter-=20;
			$this->posxqty-=20;		
			$this->postotalht-=20;
		}

		$this->tva=array();
		$this->localtax1=array();
		$this->localtax2=array();
		$this->atleastoneratenotnull=0;
		$this->atleastonediscount=0;
		$this->atleastoneref=0;
	}

    /**
     *  Function to build pdf onto disk
     *
     *  @param		int		$object				Id of object to generate
     *  @param		object	$outputlangs		Lang output object
     *  @param		string	$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int		$hidedetails		Do not show line details
     *  @param		int		$hidedesc			Do not show desc
     *  @param		int		$hideref			Do not show ref
     *  @param		object	$hookmanager		Hookmanager object
     *  @return     int             			1=OK, 0=KO
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
		$outputlangs->load("companies");
		$outputlangs->load("bills");
		$outputlangs->load("propal");
		$outputlangs->load("products");
		$outputlangs->load("ultimatepdf@ultimatepdf");
		
		$nblignes = count($object->lines);
		
		// Loop on each lines to detect if there is at least one image to show
		$realpatharray=array();
		if (! empty($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE))
		{
			$objphoto = new Product($this->db);

			for ($i = 0 ; $i < $nblignes ; $i++)
			{
				if (empty($object->lines[$i]->fk_product)) continue;

				$objphoto->fetch($object->lines[$i]->fk_product);

				if (! empty($conf->global->PRODUCT_USE_OLD_PATH_FOR_PHOTO))
				{
					$pdir[0] = get_exdir($objphoto->id,2,0,0,$objphoto,'product') . $objphoto->id ."/photos/";
					$pdir[1] = dol_sanitizeFileName($objphoto->ref).'/';
				}
				else
				{
					$pdir[0] = dol_sanitizeFileName($objphoto->ref).'/';				// default
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
		if (count($realpatharray) == 0) $this->posxpicture=$this->posxtva;

        if ($conf->propal->dir_output)
        {
        	$object->fetch_thirdparty();

			// Definition de $dir et $file
			if ($object->specimen)
			{
				$dir = $conf->propal->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->propal->dir_output . "/" . $objectref;
				$file = $dir . "/" . $objectref . ".pdf";
			}

			if (! file_exists($dir))
			{
				if (dol_mkdir($dir) < 0)
				{
					$this->error=$langs->trans("ErrorCanNotCreateDir",$dir);
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
				if (! empty($conf->global->MAIN_DISPLAY_PROPOSAL_AGREEMENT_BLOCK))
				{
					$heightforinfotot = 60;	// Height reserved to output the info and total part
				}
				else
				{
					$heightforinfotot = 50;
				}
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
				$pdf->SetSubject($outputlangs->transnoentities("CommercialProposal"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("CommercialProposal"));
				if (! empty($conf->global->MAIN_DISABLE_PDF_COMPRESSION)) $pdf->SetCompression(false);

				$pdf->SetMargins($this->marge_gauche, $this->marge_haute, $this->marge_droite);   // Left, Top, Right
				
				// Positionne $this->atleastoneref si on a au moins une ref 
				for ($i = 0 ; $i < $nblignes ; $i++)
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
				
				// Positionne $this->atleastonediscount si on a au moins une remise 
				for ($i = 0 ; $i < $nblignes ; $i++)
				{
					if ($object->lines[$i]->remise_percent)
					{
						$this->atleastonediscount++;
					}
				}
				if (empty($this->atleastonediscount))
				{
					$this->posxpicture+=($this->posxqty - $this->posxdiscount);
					$this->posxtva+=($this->posxqty - $this->posxdiscount);
					$this->posxup+=($this->posxqty - $this->posxdiscount);
					$this->posxdiscount+=($this->posxqty - $this->posxdiscount);
				}				
				
				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs);
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColorArray($textcolor);

				//catch logo height
				$logo_height=max(pdf_getUltimateHeightForLogo($logo),30);
				$delta=35-$logo_height;

				//Set $hautcadre
				if (($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','SHIPPING')) && ($conf->global->ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS == 1) || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','SHIPPING')) && ($conf->global->ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS == 1)|| ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','BILLING')) && ($conf->global->ULTIMATE_PDF_PROPAL_ADDALSOTARGETDETAILS == 1) || (! empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS) && !empty($object->note_public)))
				{
					$hautcadre=68;
				}
				else
				{
					$hautcadre=52;
				}
				$tab_top = $this->marge_haute+$logo_height+$hautcadre+$delta+15;
				
				$tab_top_newpage = (empty($conf->global->ULTIMATE_PROPAL_PDF_DONOTREPEAT_HEAD)?$this->marge_haute+$logo_height+15:10);
				$tab_height = 130;
				$tab_height_newpage = 150;
				$tab_width = $this->page_largeur-$this->marge_gauche-$this->marge_droite;
				
				// Incoterm
				$height_incoterms = 0;
				if ($conf->incoterm->enabled)
				{
					$desc_incoterms = $object->getIncotermsForPDF();
					if ($desc_incoterms)
					{
						$tab_top = $this->marge_haute+$logo_height+$hautcadre+$delta+15;
						$pdf->SetFont('','', $default_font_size - 2);
						$pdf->writeHTMLCell($tab_width, 3, $this->marge_gauche+1, $tab_top-1, dol_htmlentitiesbr($desc_incoterms), 0, 1);
						$nexY = $pdf->GetY();
						$height_incoterms=$nexY-$tab_top;

						// Rect prend une longueur en 3eme param
						$pdf->SetDrawColor(192,192,192);
						$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $tab_width, $height_incoterms+1, 2, $round_corner = '1111', 'S', $this->style, array());

						$tab_top = $nexY+2;
						$height_incoterms += 4;
					}
				}

				// Affiche notes
				$notetoshow=empty($object->note_public)?'':$object->note_public;
				if (! empty($conf->global->MAIN_ADD_SALE_REP_SIGNATURE_IN_PROPAL_NOTE))
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
					$tab_top = $this->marge_haute+$logo_height+$hautcadre+$delta+$height_incoterms+15;
					$pdf->SetFont('','', $default_font_size - 2);   // Dans boucle pour gerer multi-page
					$pdf->writeHTMLCell($tab_width, 3, $this->marge_gauche+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);
					
					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme et 4eme param
					$pdf->SetDrawColor(192,192,192);					
					$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $tab_width, $height_note+1, 2, $round_corner = '1111', 'S', $this->style, array());

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
				$line_number=1;
                for ($i = 0 ; $i < $nblignes ; $i++)
                {
                    $curY = $nexY;
                    $pdf->SetFont('','', $default_font_size - 2);   // Dans boucle pour gerer multi-page
					$pdf->SetTextColorArray($textcolor);					
					
					// Define size of image if we need it
					$imglinesize=array();
					if (! empty($realpatharray[$i])) $imglinesize=pdf_getSizeForImage($realpatharray[$i]);

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
					$pdf->setPageOrientation('', 1, $bMargin);	// The only function to edit the bottom margin of current 
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
						if (empty($conf->global->ULTIMATE_PROPAL_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="CommercialProposal");
						$pdf->setPage($pageposbefore+1);

						$curY = $tab_top_newpage;
						$showpricebeforepagebreak=1;
					}
					
					$picture=false;
					if (isset($imglinesize['width']) && isset($imglinesize['height']))
					{
						$curX = $this->posxpicture-1;
						$pdf->Image($realpatharray[$i], $curX + (($this->posxtva-$this->posxpicture-$imglinesize['width'])/2), $curY, $imglinesize['width'], $imglinesize['height'], '', '', '', 2, 300);	// Use 300 dpi
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
					$text_length=($picture?$this->posxpicture:$this->posxtva);

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
									if (empty($conf->global->ULTIMATE_PROPAL_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
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
						//Settings for jalon with pagebreak
						if (($conf->milestone->enabled) && (isset($object->lines[$i]->pagebreak) && $object->lines[$i]->pagebreak)) 
						{							
							$curY=$tab_top_newpage+1;
						}
						
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
							$pdf->setPageOrientation('', 1, $heightforfooter);	// The only function to edit the bottom margin of current page to set it.
							
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
									if (empty($conf->global->ULTIMATE_PROPAL_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="CommercialProposal");
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
					
					//test extrafields on line
					/*$object->lines[$i]->fetch_optionals($object->lines[$i]->rowid,'');
					$posxcoef=$object->lines[$i]->array_options['options_coef'];
					$pdf->SetXY($this->posxcoef, $curY);
					$pdf->MultiCell($this->posxtva-$this->posxcoef-0.8, 3, $posxcoef, 0, 'C');*/

					if ($posYStartDescription>$posYAfterDescription && $pageposafter>$pageposbefore)
					{
						$pdf->setPage($pageposbefore); $curY = $posYStartDescription;
					}
					if ($curY+2>($this->page_hauteur - $heightforfooter))	// There is no space left for total+free text
					{						
						$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
					}
					//Line numbering
					if (! empty($conf->global->ULTIMATE_PROPOSALS_WITH_LINE_NUMBER))
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
					
                	// VAT Rate
					if ($conf->global->ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT == "no" && empty($conf->global->ULTIMATE_SHOW_HIDE_VAT_COLUMN))
					{
						$vat_rate = pdf_getlinevatrate($object, $i, $outputlangs, $hidedetails);
						$pdf->SetXY($this->posxtva, $curY);
						$pdf->MultiCell($this->posxup-$this->posxtva-0.8, 3, $vat_rate, 0, 'C');
					}
					
                    // Unit price before discount
					if (empty($conf->global->ULTIMATE_SHOW_HIDE_PUHT))
					{
						$up_excl_tax = pdf_getlineupexcltax($object, $i, $outputlangs, $hidedetails);
						$pdf->SetXY($this->posxup, $curY);
						$pdf->MultiCell($this->posxdiscount-$this->posxup-0.8, 3, $up_excl_tax, 0, 'R', 0);
					}
					
					// Discount on line                	
					if ($object->lines[$i]->remise_percent)
					{
						$pdf->SetXY($this->posxdiscount, $curY);
						$remise_percent = pdf_getlineremisepercent($object, $i, $outputlangs, $hidedetails);
						$pdf->MultiCell($this->posxupafter-$this->posxdiscount-0.8, 3, $remise_percent, 0, 'C');
					}
					
					// Prix unitaire HT apres remise
					$string = $langs->trans("Offered");
					if ($remise_percent == $string)
					{
						$up_after = price(0);
						$pdf->SetXY ($this->posxupafter, $curY);
						$pdf->MultiCell($this->posxqty-$this->posxupafter-0.8, 4, $up_after, 0, 'R');
					}
					else
					{
						if ($object->lines[$i]->remise_percent > 0)
						{
							$up_after = price2num((float)(100 - $remise_percent) * price2num($up_excl_tax) / (float)100, 'MU');
							$pdf->SetXY ($this->posxupafter, $curY);
							$pdf->MultiCell($this->posxqty-$this->posxupafter-0.8, 4, price($up_after), 0, 'R');
						}
					}

					// Quantity
					if (empty($conf->global->ULTIMATE_SHOW_HIDE_QTY))
					{
						$qty = pdf_getlineqty($object, $i, $outputlangs, $hidedetails);
						$pdf->SetXY($this->posxqty, $curY);
						// Enough for 6 chars
						if($conf->global->PRODUCT_USE_UNITS)
						{
							$pdf->MultiCell($this->posxunit-$this->posxqty-0.8, 4, $qty, 0, 'C');
						}
						else
						{
							$pdf->MultiCell($this->postotalht-$this->posxqty-0.8, 4, $qty, 0, 'C');
						}
					}
					
					// Unit
					if($conf->global->PRODUCT_USE_UNITS)
					{
						if ($conf->vigieunite->enabled)
						{
							//compatibility with module Purchase/Sell Units management
							dol_include_once('/vigieunite/class/vigieuniteunite.class.php');
							$vigieunite = new Vigieuniteunite($this->db);
							$exist = $vigieunite->fetchByIdProduct($object->lines[$i]->fk_product);
							if ($exist)
								$unit = $vigieunite->unite_vente;
							else
								$unit = '';
							$pdf->SetXY($this->posxunit, $curY);
							$pdf->MultiCell($this->postotalht-$this->posxunit-0.8, 3, $unit, 0, 'C');	// Enough for 6 chars
						}
						else
						{
							$unit = pdf_getlineunit($object, $i, $outputlangs, $hidedetails, $hookmanager);
							$pdf->SetXY($this->posxunit, $curY);
							$pdf->MultiCell($this->postotalht-$this->posxunit-0.8, 4, $unit, 0, 'C');
						}
					}

                    if (!empty($conf->global->ULTIMATE_SHOW_LINE_TTTC))
					{
						// Total TTC line
						if (empty($conf->global->ULTIMATE_SHOW_HIDE_THT))
						{
							$total_incl_tax = pdf_ultimate_getlinetotalwithtax($object, $i, $outputlangs, $hidedetails);
							$pdf->SetXY ($this->postotalht, $curY);
							$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->postotalht, 3, $total_incl_tax, 0, 'R', 0);
						}
					}
					else
					{
						// Total HT line
						if (empty($conf->global->ULTIMATE_SHOW_HIDE_THT))
						{
							$total_excl_tax = pdf_getlinetotalexcltax($object, $i, $outputlangs, $hidedetails);
							$pdf->SetXY ($this->postotalht, $curY);
							$pdf->MultiCell($this->page_largeur-$this->marge_droite-$this->postotalht, 3, $total_excl_tax, 0, 'R', 0);
						}
					}

                    // Collecte des totaux par valeur de tva dans $this->tva["taux"]=total_tva
					$tvaligne=$object->lines[$i]->total_tva;
					$localtax1ligne=$object->lines[$i]->total_localtax1;
					$localtax2ligne=$object->lines[$i]->total_localtax2;
					$localtax1_rate=$object->lines[$i]->localtax1_tx;
					$localtax2_rate=$object->lines[$i]->localtax2_tx;
					$localtax1_type=$object->lines[$i]->localtax1_type;
					$localtax2_type=$object->lines[$i]->localtax2_type;

					if ($object->remise_percent) $tvaligne-=($tvaligne*$object->remise_percent)/100;
					if ($object->remise_percent) $localtax1ligne-=($localtax1ligne*$object->remise_percent)/100;
					if ($object->remise_percent) $localtax2ligne-=($localtax2ligne*$object->remise_percent)/100;

					$vatrate=(string) $object->lines[$i]->tva_tx;
					
					// Retrieve type from database for backward compatibility with old records
					if ((! isset($localtax1_type) || $localtax1_type=='' || ! isset($localtax2_type) || $localtax2_type=='') // if tax type not defined
					&& (! empty($localtax1_rate) || ! empty($localtax2_rate))) // and there is local tax
					{
						$localtaxtmp_array=getLocalTaxesFromRate($vatrate,0,$object->thirdparty,$mysoc);
						$localtax1_type = $localtaxtmp_array[0];
						$localtax2_type = $localtaxtmp_array[2];
					}
					
					// retrieve global local tax
					if ($localtax1_type && $localtax1ligne != 0)
						$this->localtax1[$localtax1_type][$localtax1_rate]+=$localtax1ligne;
					if ($localtax2_type && $localtax2ligne != 0)
						$this->localtax2[$localtax2_type][$localtax2_rate]+=$localtax2ligne;

					if (($object->lines[$i]->info_bits & 0x01) == 0x01) $vatrate.='*';
					if (! isset($this->tva[$vatrate]))				$this->tva[$vatrate]=0;
					$this->tva[$vatrate] += $tvaligne;
					
					
					// Add line
					if (! empty($conf->global->ULTIMATE_PROPAL_PDF_DASH_BETWEEN_LINES) && $i < ($nblignes - 1))
					{
						$pdf->setPage($pageposafter);
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
						if (empty($conf->global->ULTIMATE_PROPAL_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="CommercialProposal");
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
						if (empty($conf->global->ULTIMATE_PROPAL_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="CommercialProposal");
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

				// Affiche zone totaux
				$posy=$this->_tableau_tot($pdf, $object, 0, $bottomlasttab, $outputlangs);
				
				// Affiche zone agreement
				$posy=$this->_agreement($pdf, $object, $posy, $outputlangs);

                // Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				//If propal merge product PDF is active
				if (!empty($conf->global->PRODUIT_PDF_MERGE_PROPAL))
				{
					require_once DOL_DOCUMENT_ROOT.'/product/class/propalmergepdfproduct.class.php';

					$already_merged = array ();
					foreach ( $object->lines as $line ) {
						if (! empty($line->fk_product) && ! (in_array($line->fk_product, $already_merged))) {
							// Find the desire PDF
							$filetomerge = new Propalmergepdfproduct($this->db);

							if ($conf->global->MAIN_MULTILANGS) {
								$filetomerge->fetch_by_product($line->fk_product, $outputlangs->defaultlang);
							} else {
								$filetomerge->fetch_by_product($line->fk_product);
							}

							$already_merged[] = $line->fk_product;

							$product = new Product($this->db);
							$product->fetch($line->fk_product);

							if ($product->entity!=$conf->entity) {
								$entity_product_file=$product->entity;
							} else {
								$entity_product_file=$conf->entity;
							}

							// If PDF is selected and file is not empty
							if (count($filetomerge->lines) > 0) {
								foreach ( $filetomerge->lines as $linefile ) {
									if (! empty($linefile->id) && ! empty($linefile->file_name)) {
										if (! empty($conf->product->enabled))
											$filetomerge_dir = $conf->product->multidir_output[$entity_product_file] . '/' . dol_sanitizeFileName($line->product_ref);
										elseif (! empty($conf->service->enabled))
											$filetomerge_dir = $conf->service->multidir_output[$entity_product_file] . '/' . dol_sanitizeFileName($line->product_ref);

										dol_syslog(get_class($this) . ':: upload_dir=' . $filetomerge_dir, LOG_DEBUG);

										$infile = $filetomerge_dir . '/' . $linefile->file_name;
										if (is_file($infile)) {
											$pagecount = $pdf->setSourceFile($infile);
											for($i = 1; $i <= $pagecount; $i ++) {
												$tplidx = $pdf->ImportPage($i);
												$s = $pdf->getTemplatesize($tplidx);
												$pdf->AddPage($s['h'] > $s['w'] ? 'P' : 'L');
												$pdf->useTemplate($tplidx);
											}
										}
									}
								}
							}
						}
					}
				}
				
				// Add PDF ask to merge
				if (!empty($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MERGED_PDF))
				{
					dol_include_once ( '/ultimatepdf/class/propalmergedpdf.class.php' );
					
					$already_merged=array();
					
					foreach ( $object->lines as $line ) {
						if (! empty ( $line->fk_propal ) && !(in_array($line->fk_propal, $already_merged))) {
						
							// Find the desire PDF
							$filetomerge = new Propalmergedpdf ( $this->db );
							
							if ($conf->global->MAIN_MULTILANGS) {
								$filetomerge->fetch_by_propal ( $line->fk_propal, $outputlangs->defaultlang);
							} else {
								$filetomerge->fetch_by_propal ( $line->fk_propal );
							}
							
							
							$already_merged[]=$line->fk_propal;
							
							// If PDF is selected and file is not empty
							if (count ( $filetomerge->lines ) > 0) {
								foreach ( $filetomerge->lines as $linefile ) {
									
									if (! empty ( $linefile->id ) && ! empty ( $linefile->file_name )) {
										
										if (! empty ( $conf->propal->enabled ))
											$filetomerge_dir = $conf->propal->dir_output. '/' . dol_sanitizeFileName ( $object->ref );
										
										$infile = $filetomerge_dir . '/' . $linefile->file_name;
										dol_syslog ( get_class ( $this ) . ':: $upload_dir=' . $filetomerge_dir, LOG_DEBUG );
										// If file really exists
										if (is_file ( $infile )) {																			
											
											$count = $pdf->setSourceFile ( $infile );
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
				}
				
                $pdf->Close();

				$pdf->Output($file,'F');

				//Add pdfgeneration hook
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
                $this->error=$outputlangs->trans("ErrorCanNotCreateDir",$dir);
                return 0;
            }
        }
        else
        {
            $this->error=$outputlangs->trans("ErrorConstantNotDefined","PROP_OUTPUTDIR");
            return 0;
        }

        $this->error=$outputlangs->trans("ErrorUnknown");
		
		unset($_SESSION['ultimatepdf_model']);
		
        return 0;   // Erreur par defaut
    }

	/**
	 *   Show table for lines
	 *
	 *   @param		PDF			&$pdf     		Object PDF
	 *   @param		string		$tab_top		Top position of table
	 *   @param		string		$tab_height		Height of table (rectangle)
	 *   @param		int			$nexY			Y (not used)
	 *   @param		Translate	$outputlangs	Langs object
	 *   @param		int			$hidetop		1=Hide top bar of array and title, 0=Hide nothing, -1=Hide only title
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

		// Amount in (at tab_top - 1)
        $pdf->SetFillColorArray($bgcolor);
		$pdf->SetTextColorArray($textcolor);
        $pdf->SetFont('','', $default_font_size - 2);

		// Output RoundedRect
		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, 2, $round_corner = '0110', 'S', $this->style, array());
		
		if (! empty($conf->global->ULTIMATE_PROPOSALS_WITH_LINE_NUMBER))
		{
			if (empty($hidetop))
			{
				if ($conf->global->ULTIMATE_DOCUMENTS_WITH_REF == "yes")
				{
					$pdf->SetXY ($this->posxnumber-1, $tab_top);
					$pdf->MultiCell($this->posxref-$this->posxnumber+2,6, $outputlangs->transnoentities("Numbering"), 0, 'C', 1);
				}
				else
				{
					$pdf->SetXY ($this->posxnumber-1, $tab_top);
					$pdf->MultiCell($this->posxdesc-$this->posxnumber+2,6, $outputlangs->transnoentities("Numbering"), 0, 'C', 1);
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
				if (! empty($this->atleastoneref))
				{
					$pdf->SetXY ($this->posxref, $tab_top);
					$pdf->MultiCell($this->posxdesc-$this->posxref,6, $outputlangs->transnoentities("RefShort"),0,'L',1);
					$pdf->SetXY ($this->posxdesc, $tab_top);
					$pdf->MultiCell($this->posxtva-$this->posxdesc,6, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
				}
				else
				{
					$pdf->SetXY ($this->posxdesc, $tab_top);
					$pdf->MultiCell($this->posxtva-$this->marge_gauche,6, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
				}
			}
			else			
			{			
				$pdf->SetXY ($this->posxdesc, $tab_top);
				$pdf->MultiCell($this->posxtva-$this->marge_gauche,6, $outputlangs->transnoentities("Designation"),0,'L',1);
			}
		}
		
		if (! empty($conf->global->ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE))
		{
			if (empty($hidetop))
			{
				//
			}
		}		

		if ($conf->global->ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT == "no" && empty($conf->global->ULTIMATE_SHOW_HIDE_VAT_COLUMN))
		{
			$pdf->line($this->posxtva-1, $tab_top, $this->posxtva-1, $tab_top + $tab_height);
			if (empty($hidetop))
			{
				$pdf->SetXY($this->posxtva-3, $tab_top);
				$pdf->MultiCell($this->posxup-$this->posxtva+3,6, $outputlangs->transnoentities("VAT"),0,'C', 1);
			}
		}
		else
		{
			if (empty($hidetop))
			{
				$pdf->SetXY($this->posxdesc, $tab_top);
				$pdf->MultiCell($this->posxup-$this->posxdesc+3,6, $outputlangs->transnoentities(""),0,'C', 1);
			}	
		}

        $pdf->line($this->posxup-1, $tab_top, $this->posxup-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			$pdf->SetXY ($this->posxup-1, $tab_top);
			$pdf->MultiCell($this->posxdiscount-$this->posxup,6, $outputlangs->transnoentities("PriceUHT"), 0, 'C', 1);
		}
		
		$pdf->line($this->posxdiscount-1, $tab_top, $this->posxdiscount-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			if ($this->atleastonediscount)
			{
				$pdf->SetXY ($this->posxdiscount-1, $tab_top);
				$pdf->MultiCell($this->posxupafter-$this->posxdiscount+1,6, $outputlangs->transnoentities("Discount"), 0, 'C', 1);
			}
		}
		
		if ($this->atleastonediscount)
        {
			$pdf->line($this->posxupafter-1, $tab_top, $this->posxupafter-1, $tab_top + $tab_height);
		}
		if (empty($hidetop))
		{
			if ($this->atleastonediscount)
			{
				$pdf->SetXY ($this->posxupafter-1, $tab_top);
				$pdf->MultiCell($this->posxqty-$this->posxupafter,6, $outputlangs->transnoentities("PuAfter"), 0, 'C', 1);
			}
		}

        if ($this->atleastonediscount)
        {
			$pdf->line($this->posxqty-1, $tab_top, $this->posxqty-1, $tab_top + $tab_height);
		}
		if (empty($hidetop))
		{
			$pdf->SetXY($this->posxqty-1, $tab_top);
			if($conf->global->PRODUCT_USE_UNITS)
			{
				$pdf->MultiCell($this->posxunit-$this->posxqty+1,6, $outputlangs->transnoentities("Qty"),'','C', 1);
			}
			else
			{
				$pdf->MultiCell($this->postotalht-$this->posxqty+1,6, $outputlangs->transnoentities("Qty"),'','C', 1);
			}
		}

		if($conf->global->PRODUCT_USE_UNITS) 
		{
			$pdf->line($this->posxunit - 1, $tab_top, $this->posxunit - 1, $tab_top + $tab_height);
			if (empty($hidetop)) 
			{
				$pdf->SetXY($this->posxunit - 1, $tab_top);
				$pdf->MultiCell($this->postotalht - $this->posxunit - 1, 6, $outputlangs->transnoentities("Unit"), '',
					'C', 1);
			}
		}        
			
		$pdf->line($this->postotalht-1, $tab_top, $this->postotalht-1, $tab_top + $tab_height);
         if (!empty($conf->global->ULTIMATE_SHOW_LINE_TTTC))
		{
			if (empty($hidetop))
			{
				$pdf->SetXY ($this->postotalht-2, $tab_top);
				$pdf->MultiCell(($this->page_largeur-$this->marge_droite)-$this->postotalht+2, 6, $outputlangs->transnoentities("TotalTTC"), 0, 'R', 1);
			}
		}
		else
		{
			if (empty($hidetop))
			{
				$pdf->SetXY ($this->postotalht-2, $tab_top);
				$pdf->MultiCell(($this->page_largeur-$this->marge_droite)-$this->postotalht+2, 6, $outputlangs->transnoentities("TotalHT"), 0, 'R', 1);
			}
		}
    }
}

?>