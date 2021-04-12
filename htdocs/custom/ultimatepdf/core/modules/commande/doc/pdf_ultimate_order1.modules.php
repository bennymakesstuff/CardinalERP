<?php
/* Copyright (C) 2004-2010 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2010 Regis Houssin        <regis.houssin@capnetworks.com>
 * Copyright (C) 2008      Raphael Bertrand     <raphael.bertrand@resultic.fr>
 * Copyright (C) 2010      Juanjo Menent	    <jmenent@2byte.es>
 * Copyright (C) 2011-2017 Philippe Grand		<philippe.grand@atoo-net.com>
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
 *	\file       core/modules/commande/doc/pdf_ultimate_order1.modules.php
 *	\ingroup    commande
 *	\brief      Fichier de la classe permettant de generer les commandes au modele ultimate_order1 
 */

require_once DOL_DOCUMENT_ROOT .'/core/modules/commande/modules_commande.php';
require_once DOL_DOCUMENT_ROOT.'/product/class/product.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/company.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/pdf.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
dol_include_once('/ultimatepdf/lib/ultimatepdf.lib.php');


/**
 *	Class for generate the commands with ultimate_order1 model
 */
class pdf_ultimate_order1 extends ModelePDFCommandes
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
     * @var array style
     */
	public $style;
	
	/**
     * @var string logo_height
     */
	public $logo_height;	
	
	/**
     * @var int number column width
     */
	public $number_width;
	
	/**
     * @var int description column width
     */
	public $desc_width;
	
	/**
     * @var int vat column width
     */
	public $tva_width;
	
	/**
     * @var int up column width
     */
	public $up_width;
	
	/**
     * @var int up after column width
     */
	public $upafter_width;
	
	/**
     * @var int qty column width
     */
	public $qty_width;
	
	/**
     * @var int weight column width
     */
	public $weight_width;
	
	/**
     * @var int discount column width
     */
	public $discount_width;

	/**
	* Issuer
	* @var Societe
	*/
	public $emetteur;
	
	private $messageErrBarcodeSet;


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
		$langs->load("products");
		$langs->load("ultimatepdf@ultimatepdf");

		$this->db = $db;
		$this->name = "ultimate_order1";
		$this->description = $langs->trans('PDFUltimate_order1Description');
		$_SESSION['ultimatepdf_model'] = true;

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
		if (! empty($conf->global->ULTIMATE_ORDERS_WITH_LINE_NUMBER))
		{
			$this->posxnumber=$this->marge_gauche+1;
			$this->number_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH)?10:$conf->global->ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH;
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
		$this->desc_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_DESC_WIDTH)?40:$conf->global->ULTIMATE_DOCUMENTS_WITH_DESC_WIDTH;
		
		$this->posxpicture=$this->posxdesc+$this->desc_width;
		$this->picture_width = empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)?20:$conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH;	// width of images

		$this->posxtva=$this->posxdesc+$this->desc_width+$this->picture_width;
		$this->tva_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH;

		$this->posxup=$this->posxdesc+$this->desc_width+$this->picture_width+$this->tva_width;
		$this->up_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_UP_WIDTH)?20:$conf->global->ULTIMATE_DOCUMENTS_WITH_UP_WIDTH;
		$this->posxqty=$this->posxdesc+$this->desc_width+$this->picture_width+$this->tva_width+$this->up_width;
		$this->qty_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH;
		if($conf->global->PRODUCT_USE_UNITS)
		{	
			$this->posxunit=$this->posxdesc+$this->desc_width+$this->picture_width+$this->tva_width+$this->up_width+$this->qty_width;
			$this->unit_width=empty($conf->global->ULTIMATE_DOCUMENTS_WITH_UNIT_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_UNIT_WIDTH;
		}

		$this->posxdiscount=$this->posxtva+$this->tva_width+$this->up_width+$this->qty_width+$this->unit_width;
		$this->discount_width = empty($conf->global->ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH)?12:$conf->global->ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH;
		$this->postotalht=$this->posxtva+$this->tva_width+$this->up_width+$this->qty_width+$this->unit_width+$this->discount_width;
		if (! ($conf->global->ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT == "no" && empty($conf->global->ULTIMATE_SHOW_HIDE_VAT_COLUMN))) $this->posxtva=$this->posxup;
		$this->posxpicture=$this->posxup - (empty($conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH)?20:$conf->global->MAIN_DOCUMENTS_WITH_PICTURE_WIDTH);	// width of images
		if ($this->page_largeur < 210) // To work with US executive format
		{
			$this->posxdesc-=20;
			$this->posxpicture-=20;
			$this->posxtva-=20;
			$this->posxup-=20;
			$this->posxqty-=20;
			$this->posxunit-=20;
			$this->posxdiscount-=20;
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
     *  @param		Object		$object				Object to generate
     *  @param		Translate	$outputlangs		Lang output object
     *  @param		string		$srctemplatepath	Full path of source filename for generator using a template file
     *  @param		int			$hidedetails		Do not show line details
     *  @param		int			$hidedesc			Do not show desc
     *  @param		int			$hideref			Do not show ref
     *  @return     int             				1=OK, 0=KO
	 */
	public function write_file($object,$outputlangs,$srctemplatepath='',$hidedetails=0,$hidedesc=0,$hideref=0)
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
		$outputlangs->load("products");
		$outputlangs->load("orders");
		$outputlangs->load("ultimatepdf@ultimatepdf");
		
		$nblignes = count($object->lines);
		
		// Loop on each lines to detect if there is at least one image to show
		$realpatharray=array();
		if (! empty($conf->global->ULTIMATE_GENERATE_ORDERS_WITH_PICTURE))
		{			
			for ($i = 0 ; $i < $nblignes ; $i++)
			{
				if (empty($object->lines[$i]->fk_product)) continue;

				$objphoto = new Product($this->db);
				$objphoto->fetch($object->lines[$i]->fk_product);

				$pdir = get_exdir($object->lines[$i]->fk_product,2,0,0,$objphoto,'product') . $object->lines[$i]->fk_product ."/photos/";
				$dir = $conf->product->dir_output.'/'.$pdir;

				$realpath='';
				foreach ($objphoto->liste_photos($dir,1) as $key => $obj)
				{
					$filename=$obj['photo'];
					//if ($obj['photo_vignette']) $filename='thumbs/'.$obj['photo_vignette'];
					$realpath = $dir.$filename;
					break;
				}

				if ($realpath) $realpatharray[$i]=$realpath;
			}
		}
		if (count($realpatharray) == 0) $this->posxpicture=$this->posxtva;

		if ($conf->commande->dir_output)
		{
            $object->fetch_thirdparty();

            $deja_regle = "";

            // Definition of $dir and $file
			if ($object->specimen)
			{
				$dir = $conf->commande->dir_output;
				$file = $dir . "/SPECIMEN.pdf";
			}
			else
			{
				$objectref = dol_sanitizeFileName($object->ref);
				$dir = $conf->commande->dir_output . "/" . $objectref;
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
				$default_font_size = pdf_getPDFFontSize($outputlangs);  // Must be after pdf_getInstance
				if (! empty($conf->global->ULTIMATE_DISPLAY_ORDER_AGREEMENT_BLOCK))
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
				$pdf->SetSubject($outputlangs->transnoentities("Order"));
				$pdf->SetCreator("Dolibarr ".DOL_VERSION);
				$pdf->SetAuthor($outputlangs->convToOutputCharset($user->getFullName($outputlangs)));
				$pdf->SetKeyWords($outputlangs->convToOutputCharset($object->ref)." ".$outputlangs->transnoentities("Order"));
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
					$this->posxpicture+=($this->postotalht - $this->posxdiscount);
					$this->posxtva+=($this->postotalht - $this->posxdiscount);
					$this->posxup+=($this->postotalht - $this->posxdiscount);
					$this->posxqty+=($this->postotalht - $this->posxdiscount);
					$this->posxunit+=($this->postotalht - $this->posxdiscount);
					$this->posxdiscount+=($this->postotalht - $this->posxdiscount);
				}
				if ($conf->global->ULTIMATE_SHOW_HIDE_PUHT)
				{
					$this->desc_width+=($this->posxqty - $this->posxup);
					$this->posxpicture+=($this->posxqty - $this->posxup);
					$this->posxtva+=($this->posxqty - $this->posxup);
				}
				if ($conf->global->ULTIMATE_SHOW_HIDE_QTY)
				{
					$this->desc_width+=$this->qty_width;
					$this->posxpicture+=$this->qty_width;
					$this->posxtva+=$this->qty_width;
					$this->posxup+=$this->qty_width;
				}

				// New page
				$pdf->AddPage();
				if (! empty($tplidx)) $pdf->useTemplate($tplidx);
				$pagenb++;
				$this->_pagehead($pdf, $object, 1, $outputlangs, $titlekey="Order");
				$pdf->SetFont('','', $default_font_size - 2);
				$pdf->MultiCell(0, 3, '');		// Set interline to 3
				$pdf->SetTextColorArray($textcolor);

				//catch logo height
				$logo_height=max(pdf_getUltimateHeightForLogo($logo),30);
				$delta=35-$logo_height;

				//Set $hautcadre
				if (($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','SHIPPING')) && ($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1) || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','SHIPPING')) && ($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1)  || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','BILLING')) && ($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1) || (! empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS) && !empty($object->note_public)))
				{
					$hautcadre=68;
				}
				else
				{
					$hautcadre=52;
				}
				$tab_top = $this->marge_haute+$logo_height+$hautcadre+$delta+15;				
				$tab_top_newpage = (empty($conf->global->ULTIMATE_ORDER_PDF_DONOTREPEAT_HEAD)?$this->marge_haute+$logo_height+$delta:10);
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
				if ($notetoshow && empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS))
				{
					$tab_top = $this->marge_haute+$logo_height+$hautcadre+$delta+$height_incoterms+15;		
					$pdf->SetFont('','', $default_font_size - 1);   // Dans boucle pour gerer multi-page
					$pdf->writeHTMLCell($tab_width, 3, $this->marge_gauche+1, $tab_top, dol_htmlentitiesbr($notetoshow), 0, 1);

					$nexY = $pdf->GetY();
					$height_note=$nexY-$tab_top;

					// Rect prend une longueur en 3eme et 4eme param
					$pdf->SetDrawColor(192,192,192);					
					$pdf->RoundedRect($this->marge_gauche, $tab_top-1, $tab_width, $height_note+1, 2, $round_corner = '1111', 'S', $this->style, array());

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
				$line_number=1;
				for ($i = 0 ; $i < $nblignes ; $i++)
                {
                    $curY = $nexY;
                    $pdf->SetFont('','', $default_font_size - 2);   // Into loop to work with multipage
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
						if (empty($conf->global->ULTIMATE_ORDER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="Order");
						$pdf->setPage($pageposbefore+1);
						
						$curY = $tab_top_newpage;
						$showpricebeforepagebreak=1;
					}
					
					$picture=false;
					if (isset($imglinesize['width']) && isset($imglinesize['height']))
					{
						$curX = $this->posxpicture-1;
						$pdf->Image($realpatharray[$i], $curX + ($this->posxtva-$this->posxpicture-$imglinesize['width']), $curY, $imglinesize['width'], $imglinesize['height'], '', '', '', 2, 300,'',false,false,0,false,false,true);	// Use 300 dpi
						// $pdf->Image does not increase value return by getY, so we save it manually
						$posYAfterImage=$curY+$imglinesize['height'];
						$picture=true;
					}
					
					if ($picture) 
					{
						$nexY=$posYAfterImage;
					}

					// Description of product line
					$curX = $this->posxdesc+1;
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
									if (empty($conf->global->ULTIMATE_ORDER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs);
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
							pdf_writelinedesc_ref($pdf,$object,$i,$outputlangs,$this->posxdesc-$this->posxref,3,$this->marge_gauche+$this->number_width+1,$curY,$hideref,$hidedesc,0,'ref');
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
									if (empty($conf->global->ULTIMATE_ORDER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="Order");
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

					$pdf->SetFont('','',  $default_font_size - 2);   // On repositionne la police par defaut
					//test extrafields on line
					/*$object->lines[$i]->fetch_optionals($object->lines[$i]->id);
					$posxdate=$object->lines[$i]->array_options['options_confirm'];
					$pdf->SetXY($this->posxdate, $curY);
					$pdf->MultiCell($this->posxtva-$this->posxdate-0.8, 3, dol_print_date($posxdate,"day",false,$outputlangs,true), 0, 'C');*/
					
					if ($posYStartDescription>$posYAfterDescription && $pageposafter>$pageposbefore)
					{
						$pdf->setPage($pageposbefore); $curY = $posYStartDescription;
					}
					if ($curY+2>($this->page_hauteur - $heightforfooter))	
					{			
						$pdf->setPage($pageposafter); $curY = $tab_top_newpage;
					}

					//Line numbering
					if (! empty($conf->global->ULTIMATE_ORDERS_WITH_LINE_NUMBER))
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
						$pdf->MultiCell($this->tva_width-0.8, 3, $vat_rate, 0, 'C');
					}

					// Unit price before discount
					if (empty($conf->global->ULTIMATE_SHOW_HIDE_PUHT))
					{
						$up_excl_tax = pdf_getlineupexcltax($object, $i, $outputlangs, $hidedetails);
						$pdf->SetXY ($this->posxup, $curY);
						$pdf->MultiCell($this->up_width-0.8, 3, $up_excl_tax, 0, 'R', 0);
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
							$pdf->MultiCell($this->posxdiscount-$this->posxqty-0.8, 4, $qty, 0, 'C');
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
							$pdf->MultiCell($this->posxdiscount-$this->posxunit-0.8, 3, $unit, 0, 'C');	// Enough for 6 chars
						}
						else
						{
							$unit = pdf_getlineunit($object, $i, $outputlangs, $hidedetails, $hookmanager);
							$pdf->SetXY($this->posxunit, $curY);
							$pdf->MultiCell($this->posxdiscount-$this->posxunit-0.8, 4, $unit, 0, 'C');
						}
					}
					
					// Discount on line                	
					$pdf->SetXY($this->posxdiscount, $curY);
					if ($object->lines[$i]->remise_percent)
					{
						$pdf->SetXY($this->posxdiscount-2, $curY);
						$remise_percent = pdf_getlineremisepercent($object, $i, $outputlangs, $hidedetails);
						$pdf->MultiCell($this->postotalht-$this->posxdiscount+2, 3, $remise_percent, 0, 'C');
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
					if ($conf->multicurrency->enabled && $object->multicurrency_tx != 1) $tvaligne=$object->lines[$i]->multicurrency_total_tva;
					else $tvaligne=$object->lines[$i]->total_tva;
					
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
					if (! isset($this->tva[$vatrate])) 				$this->tva[$vatrate]=0;
					$this->tva[$vatrate] += $tvaligne;

					// Add line
					if (! empty($conf->global->ULTIMATE_ORDER_PDF_DASH_BETWEEN_LINES) && $i < ($nblignes - 1))
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
							$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
						}
						$this->_pagefoot($pdf,$object,$outputlangs,1);
						$pagenb++;
						$pdf->setPage($pagenb);
						$pdf->setPageOrientation('', 1, 0);	// The only function to edit the bottom margin of current page to set it.
						if (empty($conf->global->ULTIMATE_ORDER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="Order");
					}
					if (isset($object->lines[$i+1]->pagebreak) && $object->lines[$i+1]->pagebreak)
					{
						if ($pagenb == 1)
						{
							$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforfooter, 0, $outputlangs, 0, 1, $object->multicurrency_code);
						}
						else
						{
							$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforfooter, 0, $outputlangs, 1, 1, $object->multicurrency_code);
						}

						$this->_pagefoot($pdf,$object,$outputlangs,1);
						// New page
						$pdf->AddPage();
						if (! empty($tplidx)) $pdf->useTemplate($tplidx);
						$pagenb++;
						if (empty($conf->global->ULTIMATE_ORDER_PDF_DONOTREPEAT_HEAD)) $this->_pagehead($pdf, $object, 0, $outputlangs, $titlekey="Order");
					}
				}

				// Show square
				if ($pagenb == 1)
				{
					$this->_tableau($pdf, $tab_top, $this->page_hauteur - $tab_top - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 0, 0, $object->multicurrency_code);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				}
				else
				{
					$this->_tableau($pdf, $tab_top_newpage, $this->page_hauteur - $tab_top_newpage - $heightforinfotot - $heightforfreetext - $heightforfooter, 0, $outputlangs, 1, 0, $object->multicurrency_code);
					$bottomlasttab=$this->page_hauteur - $heightforinfotot - $heightforfreetext - $heightforfooter + 1;
				}

				// Affiche zone infos
				$posy=$this->_tableau_info($pdf, $object, $bottomlasttab, $outputlangs);

				// Affiche zone totaux
				$posy=$this->_tableau_tot($pdf, $object, $deja_regle, $bottomlasttab, $outputlangs);
				
				// Affiche zone agreement
				$posy=$this->_agreement($pdf, $object, $posy, $outputlangs);

				// Affiche zone versements
				if ($deja_regle)
				{
					$posy=$this->_tableau_versements($pdf, $object, $posy, $outputlangs);
				}

				// Pied de page
				$this->_pagefoot($pdf,$object,$outputlangs);
				if (method_exists($pdf,'AliasNbPages')) $pdf->AliasNbPages();
				
				// Add PDF ask to merge
				if (!empty($conf->global->ULTIMATEPDF_GENERATE_ORDERS_WITH_MERGED_PDF))
				{
					dol_include_once ( '/ultimatepdf/class/ordermergedpdf.class.php' );
					
					$already_merged=array();
					
					foreach ( $object->lines as $line ) 
					{
						if (! empty ( $line->fk_commande ) && !(in_array($line->fk_commande, $already_merged))) 
						{
						
							// Find the desire PDF
							$filetomerge = new Ordermergedpdf ( $this->db );
							
							if ($conf->global->MAIN_MULTILANGS) 
							{
								$filetomerge->fetch_by_order ( $line->fk_commande, $outputlangs->defaultlang);
							} 
							else 
							{
								$filetomerge->fetch_by_order ( $line->fk_commande );
							}
							
							
							$already_merged[]=$line->fk_commande;
							
							// If PDF is selected and file is not empty
							if (count ( $filetomerge->lines ) > 0) 
							{
								foreach ( $filetomerge->lines as $linefile ) 
								{
									
									if (! empty ( $linefile->id ) && ! empty ( $linefile->file_name )) 
									{
										
										if (! empty ( $conf->commande->enabled ))
											$filetomerge_dir = $conf->commande->dir_output. '/' . dol_sanitizeFileName ( $object->ref );
										
										$infile = $filetomerge_dir . '/' . $linefile->file_name;
										dol_syslog ( get_class ( $this ) . ':: $upload_dir=' . $filetomerge_dir, LOG_DEBUG );
										// If file really exists
										if (is_file ( $infile )) 
										{																			
											
											$count = $pdf->setSourceFile ( $infile );
											// import all page
											for($i = 1; $i <= $count; $i ++) 
											{
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
			$this->error=$langs->trans("ErrorConstantNotDefined","COMMANDE_OUTPUTDIR");
			return 0;
		}
		$this->error=$langs->trans("ErrorUnknown");
		
		unset($_SESSION['ultimatepdf_model']);
		
		return 0;   // Erreur par defaut
	}

	/**
	 *  Show payments table
     *
	 *  @param	PDF			&$pdf     		Object PDF
	 *  @param  Object		$object			Object order
	 *	@param	int			$posy			Position y in PDF
	 *	@param	Translate	$outputlangs	Object langs for output
	 *	@return int							<0 if KO, >0 if OK
	 */
	function _tableau_versements(&$pdf, $object, $posy, $outputlangs)
	{

	}


	/**
	 *   Show miscellaneous information (payment mode, payment term, ...)
	 *
	 *   @param		TCPDF		&$pdf     		Object PDF
	 *   @param		Object		$object			Object to show
	 *   @param		int			$posy			Y
	 *   @param		Translate	$outputlangs	Langs object
	 *   @return	void
	 */
	function _tableau_info(&$pdf, $object, $posy, $outputlangs)
	{
		global $conf;
		
		$default_font_size = pdf_getPDFFontSize($outputlangs);
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}

		$pdf->SetFont('','', $default_font_size - 1);

        // If France, show VAT mention if not applicable
		if ($this->emetteur->country_code == 'FR' && $this->franchise == 1)
		{
			$pdf->SetFont('','B', $default_font_size - 2);
			$pdf->SetXY($this->marge_gauche, $posy);
			$pdf->MultiCell(100, 3, $outputlangs->transnoentities("VATIsNotUsedForInvoice"), 0, 'L', 0);

			$posy=$pdf->GetY()+4;
		}
		
		$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
		
		// Show planed date of delivery
        if (! empty($object->date_livraison))
		{
            $outputlangs->load("sendings");
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($this->marge_gauche, $posy);
			$titre = '<strong>'.$outputlangs->transnoentities("DateDeliveryPlanned").'</strong>'.' : ';
			$dlp=dol_print_date($object->date_livraison,"daytext",false,$outputlangs,true);
			$pdf->writeHTMLCell($widthrecbox, 4, $this->marge_gauche, $posy, $titre .' '.$dlp, 0, 0, false, true, 'L', true);

            $posy=$pdf->GetY()+4;
		}
        elseif ($object->availability_code || $object->availability)    // Show availability conditions
		{
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetTextColorArray($textcolor);
			$pdf->SetXY($this->marge_gauche, $posy);
			$titre = '<strong>'.$outputlangs->transnoentities("AvailabilityPeriod").'</strong>'.' : ';
			$lib_availability=$outputlangs->transnoentities("AvailabilityType".$object->availability_code)!=('AvailabilityType'.$object->availability_code)?$outputlangs->transnoentities("AvailabilityType".$object->availability_code):$outputlangs->convToOutputCharset($object->availability);
			$lib_availability=str_replace('\n',"\n",$lib_availability);
			$pdf->writeHTMLCell($widthrecbox, 4, $this->marge_gauche, $posy, $titre .' '.$lib_availability, 0, 0, false, true, 'L', true);

			$posy=$pdf->GetY()+4;
		}
		
		// Show payments conditions
		if ($object->cond_reglement_code || $object->cond_reglement)
		{
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($this->marge_gauche, $posy);
			$titre = '<strong>'.$outputlangs->transnoentities("PaymentConditions").'</strong>'.' : ';		
			$lib_condition_paiement=$outputlangs->transnoentities("PaymentCondition".$object->cond_reglement_code)!=('PaymentCondition'.$object->cond_reglement_code)?$outputlangs->transnoentities("PaymentCondition".$object->cond_reglement_code):$outputlangs->convToOutputCharset($object->cond_reglement_doc);
			$lib_condition_paiement=str_replace('\n',"\n",$lib_condition_paiement);
			$pdf->writeHTMLCell($widthrecbox, 4,$this->marge_gauche,$posy, $titre.' '.$lib_condition_paiement, 0, 0, false, true, 'L', true);
	        
			$posy=$pdf->GetY()+7;
		}

      	// Show payment mode
        if ($object->mode_reglement_code
        && $object->mode_reglement_code != 'CHQ'
        && $object->mode_reglement_code != 'VIR')
        {
	        $pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($this->marge_gauche, $posy);
			$titre = '<strong>'.$outputlangs->transnoentities("PaymentMode").'</strong>'.' : ';
			$lib_mode_reg=$outputlangs->transnoentities("PaymentType".$object->mode_reglement_code)!=('PaymentType'.$object->mode_reglement_code)?$outputlangs->transnoentities("PaymentType".$object->mode_reglement_code):$outputlangs->convToOutputCharset($object->mode_reglement);
			$pdf->writeHTMLCell($widthrecbox, 4,$this->marge_gauche,$posy, $titre.' '.$lib_mode_reg, 0, 0, false, true, 'L', true);

			$posy=$pdf->GetY()+4;
        }
		
		// Auto-liquidation régime de la sous-traitance
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_ORDERS_WITH_AUTO_LIQUIDATION))
		{
			$pdf->SetFont('','', $default_font_size - 2);
			$pdf->SetXY($this->marge_gauche, $posy);
			$titre1 = '<strong>'.$outputlangs->transnoentities("AutoLiquidation1").'</strong>';			
			$titre2 = $outputlangs->transnoentities("AutoLiquidation2");
			$pdf->writeHTMLCell($widthrecbox, 4, $this->marge_gauche, $posy, $titre1 .' '.$titre2, 0, 0, false, true, 'L', true);
			
			$posy=$pdf->GetY()+7;
		}
		
		// Example using extrafields
		$title_key=(empty($object->array_options['options_newline']))?'':($object->array_options['options_newline']);
		$extrafields = new ExtraFields ( $this->db );
		$extralabels = $extrafields->fetch_name_optionals_label ( $object->table_element, true );
		if (is_array ( $extralabels ) && key_exists ( 'newline', $extralabels ) && !empty($title_key)) {
			$pdf->SetXY($this->marge_gauche, $posy);
			$title = $extrafields->showOutputField ( 'newline', $title_key );
			$pdf->writeHTMLCell($widthrecbox, 4, $this->marge_gauche, $posy, $title, 0, 0, false, true, 'L', true);
			
			$posy=$pdf->GetY()+7;
		}

		// Show payment mode CHQ
        if (empty($object->mode_reglement_code) || $object->mode_reglement_code == 'CHQ')
        {
        	// Si mode reglement non force ou si force a CHQ
	        if (! empty($conf->global->FACTURE_CHQ_NUMBER))
	        {
				$diffsizetitle=(empty($conf->global->PDF_DIFFSIZE_TITLE)?3:$conf->global->PDF_DIFFSIZE_TITLE);
				
	            if ($conf->global->FACTURE_CHQ_NUMBER > 0)
	            {					
	                $account = new Account($this->db);
	                $account->fetch($conf->global->FACTURE_CHQ_NUMBER);

	                $pdf->SetXY($this->marge_gauche, $posy);
	                $pdf->SetFont('','B', $default_font_size - $diffsizetitle);
	                $pdf->MultiCell($widthrecbox, 3, $outputlangs->transnoentities('PaymentByChequeOrderedTo',$account->proprio),0,'L',0);
		            $posy=$pdf->GetY()+1;

		            if (empty($conf->global->MAIN_PDF_HIDE_CHQ_ADDRESS))
		            {
		                $pdf->SetXY($this->marge_gauche, $posy);
		                $pdf->SetFont('','', $default_font_size - $diffsizetitle);
		                $pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset($account->owner_address), 0, 'L', 0);
			            $posy=$pdf->GetY()+2;
		            }
	            }
	            if ($conf->global->FACTURE_CHQ_NUMBER == -1)
	            {
	                $pdf->SetXY($this->marge_gauche, $posy);
	                $pdf->SetFont('','B', $default_font_size - $diffsizetitle);
	                $pdf->MultiCell($widthrecbox, 3, $outputlangs->transnoentities('PaymentByChequeOrderedToShort').' '.$outputlangs->convToOutputCharset($this->emetteur->name).' '.$outputlangs->transnoentities('SendTo').':',0,'L',0);
		            $posy=$pdf->GetY()+1;

		            if (empty($conf->global->MAIN_PDF_HIDE_CHQ_ADDRESS))
		            {
			            $pdf->SetXY($this->marge_gauche, $posy);
		                $pdf->SetFont('','', $default_font_size - $diffsizetitle);
		                $pdf->MultiCell($widthrecbox, 3, $outputlangs->convToOutputCharset($this->emetteur->getFullAddress()), 0, 'L', 0);
			            $posy=$pdf->GetY()+2;
		            }
	            }
	        }
		}

        // If payment mode not forced or forced to VIR, show payment with BAN
        if (empty($object->mode_reglement_code) || $object->mode_reglement_code == 'VIR')
        {
        	if (! empty($object->fk_account) || ! empty($object->fk_bank) || ! empty($conf->global->FACTURE_RIB_NUMBER))
			{
				$bankid=(empty($object->fk_account)?$conf->global->FACTURE_RIB_NUMBER:$object->fk_account);
				if (! empty($object->fk_bank)) $bankid=$object->fk_bank;   // For backward compatibility when object->fk_account is forced with object->fk_bank
				$account = new Account($this->db);
				$account->fetch($bankid);

				$curx=$this->marge_gauche;
				$cury=$posy;

				$posy=pdf_bank($pdf,$outputlangs,$curx,$cury,$account,0,$default_font_size);

				$posy+=2;
			}
        }
		return $posy;
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
		
		$total_ht = ($conf->multicurrency->enabled && $object->mylticurrency_tx != 1 ? $object->multicurrency_total_ht : $object->total_ht);
		$pdf->SetXY ($col2x, $tab2_top + 0);
		$pdf->MultiCell($largcol2, $tab2_hl, price($total_ht + (! empty($object->remise)?$object->remise:0), 0, $outputlangs, 0, -1, -1, $currency_code), 0, 'R', 1);

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
				//Local tax 1 before VAT
				foreach( $this->localtax1 as $localtax_type => $localtax_rate )
				{
					if (in_array((string) $localtax_type, array('1','3','5','7'))) continue;
					
					foreach( $localtax_rate as $tvakey => $tvaval )
					{
						if ($tvakey!=0)    // On affiche pas taux 0
						{
							$index++;
							$pdf->SetXY($col1x, $tab2_top + $tab2_hl * $index);

							$tvacompl='';
							if (preg_match('/\*/',$tvakey))
							{
								$tvakey=str_replace('*','',$tvakey);
								$tvacompl = " (".$outputlangs->transnoentities("NonPercuRecuperable").")";
							}
							$totalvat = $outputlangs->transcountrynoentities("TotalLT1",$mysoc->country_code).' ';
							$totalvat.=vatrate(abs($tvakey),1).$tvacompl;
							$pdf->MultiCell($col2x-$col1x, $tab2_hl, $totalvat, 0, 'L', 1);

							$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
							$pdf->MultiCell($largcol2, $tab2_hl, price($tvaval, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);						
						}
					}
				}

				//Local tax 2  before VAT
				foreach( $this->localtax2 as $localtax_type => $localtax_rate )
				{
					if (in_array((string) $localtax_type, array('1','3','5','7'))) continue;
					
					foreach( $localtax_rate as $tvakey => $tvaval )
					{
						if ($tvakey!=0)    // On affiche pas taux 0
						{
							$index++;
							$pdf->SetXY($col1x, $tab2_top + $tab2_hl * $index);

							$tvacompl='';
							if (preg_match('/\*/',$tvakey))
							{
								$tvakey=str_replace('*','',$tvakey);
								$tvacompl = " (".$outputlangs->transnoentities("NonPercuRecuperable").")";
							}
							$totalvat = $outputlangs->transcountrynoentities("TotalLT2",$mysoc->country_code).' ';
							$totalvat.=vatrate(abs($tvakey),1).$tvacompl;
							$pdf->MultiCell($col2x-$col1x, $tab2_hl, $totalvat, 0, 'L', 1);

							$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
							$pdf->MultiCell($largcol2, $tab2_hl, price($tvaval, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);
						}
					}
				}
				
				// VAT
				foreach($this->tva as $tvakey => $tvaval)
				{
					if ($tvakey > 0)    // On affiche pas taux 0
					{
						$this->atleastoneratenotnull++;

						$index++;
						$pdf->SetXY($col1x, $tab2_top + $tab2_hl * $index);

						$tvacompl='';
						if (preg_match('/\*/',$tvakey))
						{
							$tvakey=str_replace('*','',$tvakey);
							$tvacompl = " (".$outputlangs->transnoentities("NonPercuRecuperable").")";
						}
						$totalvat =$outputlangs->transnoentities("TotalVAT").' ';
						$totalvat.=vatrate($tvakey,1).$tvacompl;
						$pdf->MultiCell($col2x-$col1x, $tab2_hl, $totalvat, 0, 'L', 1);

						$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
						$pdf->MultiCell($largcol2, $tab2_hl, price($tvaval, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);
					}
				}

				//Local tax 1 after VAT
				foreach( $this->localtax1 as $localtax_type => $localtax_rate )
				{
					if (in_array((string) $localtax_type, array('2','4','6'))) continue;

					foreach( $localtax_rate as $tvakey => $tvaval )
					{
						if ($tvakey != 0)    // On affiche pas taux 0
						{
							$index++;
							$pdf->SetXY($col1x, $tab2_top + $tab2_hl * $index);

							$tvacompl='';
							if (preg_match('/\*/',$tvakey))
							{
								$tvakey=str_replace('*','',$tvakey);
								$tvacompl = " (".$outputlangs->transnoentities("NonPercuRecuperable").")";
							}
							$totalvat = $outputlangs->transcountrynoentities("TotalLT1",$mysoc->country_code).' ';
							
							$totalvat.=vatrate(abs($tvakey),1).$tvacompl;
							$pdf->MultiCell($col2x-$col1x, $tab2_hl, $totalvat, 0, 'L', 1);
							$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
							$pdf->MultiCell($largcol2, $tab2_hl, price($tvaval, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);								
						}
					}
				}

				//Local tax 2  after VAT
				foreach( $this->localtax2 as $localtax_type => $localtax_rate )
				{
					if (in_array((string) $localtax_type, array('2','4','6'))) continue;

					foreach( $localtax_rate as $tvakey => $tvaval )
					{
						if ($tvakey != 0)    // On affiche pas taux 0
						{
							$index++;
							$pdf->SetXY($col1x, $tab2_top + $tab2_hl * $index);

							$tvacompl='';
							if (preg_match('/\*/',$tvakey))
							{
								$tvakey=str_replace('*','',$tvakey);
								$tvacompl = " (".$outputlangs->transnoentities("NonPercuRecuperable").")";
							}
							$totalvat = $outputlangs->transcountrynoentities("TotalLT2",$mysoc->country_code).' ';
								
							$totalvat.=vatrate($tvakey,1).$tvacompl;
							$pdf->MultiCell($col2x-$col1x, $tab2_hl, $totalvat, 0, 'L', 1);

							$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
							$pdf->MultiCell($largcol2, $tab2_hl, price($tvaval, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);							
						}
					}
				}

				// Total TTC
				$index++;
				$pdf->SetXY ($col1x, $tab2_top + $tab2_hl * $index);
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','B',$default_font_size );
				$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("TotalTTC"), $useborder, 'L', 1);
				
				$total_ttc = ($conf->multicurrency->enabled && $object->multiccurency_tx != 1) ? $object->multicurrency_total_ttc : $object->total_ttc;
				$pdf->SetXY ($col2x, $tab2_top + $tab2_hl * $index);
				$pdf->MultiCell($largcol2, $tab2_hl, price($total_ttc, 0, $outputlangs, 0, -1, -1, $currency_code), $useborder, 'R', 1);
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
			$pdf->MultiCell($largcol2, $tab2_hl, price($object->total_ht + (! empty($object->remise)?$object->remise:0),0,$outputlangs,0,-1,-1,$currency_code), 0, 'R', 1);		
		}			
		$pdf->SetTextColorArray($textcolor);

		$creditnoteamount=0;
        $depositsamount=0;
		$resteapayer = price2num($object->total_ttc - $deja_regle - $creditnoteamount - $depositsamount, 'MT');
		if (! empty($object->paye)) $resteapayer=0;

		if ($deja_regle > 0)
		{
			// Already paid + Deposits
			$index++;

			$pdf->SetXY($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("AlreadyPaid"), 0, 'L', 0);
			$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($deja_regle, 0, $outputlangs,0,-1,-1,$currency_code), 0, 'R', 0);

			$index++;
			$pdf->SetTextColor(0,0,60);
			$pdf->SetFillColor(224,224,224);
			$pdf->SetXY($col1x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($col2x-$col1x, $tab2_hl, $outputlangs->transnoentities("RemainderToPay"), $useborder, 'L', 1);

			$pdf->SetXY($col2x, $tab2_top + $tab2_hl * $index);
			$pdf->MultiCell($largcol2, $tab2_hl, price($resteapayer, 0, $outputlangs,0,-1,-1,$currency_code), $useborder, 'R', 1);

			$pdf->SetFont('','', $default_font_size - 1);
			$pdf->SetTextColorArray($textcolor);
		}

		$index++;
		return ($tab2_top + ($tab2_hl * $index));
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
		$textcolor = array('25','25','25');
		if (!empty($conf->global->ULTIMATE_TEXTCOLOR_COLOR))
		{
			$textcolor =  html2rgb($conf->global->ULTIMATE_TEXTCOLOR_COLOR);
		}
		$widthrecbox=($this->page_largeur-$this->marge_gauche-$this->marge_droite-4)/2;
		
		if (! empty($conf->global->ULTIMATE_DISPLAY_ORDER_AGREEMENT_BLOCK))
	    {
			$heightforfreetext= (isset($conf->global->MAIN_PDF_FREETEXT_HEIGHT)?$conf->global->MAIN_PDF_FREETEXT_HEIGHT:12);	// Height reserved to output the free text on last page
			$heightforfooter = $this->marge_basse + 12;	// Height reserved to output the footer (value include bottom margin)
			$heightforinfotot = 35;	// Height reserved to output the info and total part
			$deltay=$this->page_hauteur-$heightforfreetext-$heightforfooter-$heightforinfotot;	
			$posy=max($posy,$deltay);
			$deltax=$this->marge_gauche+$widthrecbox+4;
			$pdf->RoundedRect($deltax, $posy, $widthrecbox, 40, 2, $round_corner = '1111', 'S', $this->style, array());
			$pdf->SetFont('','B', $default_font_size - 1);
			$pdf->SetXY($deltax, $posy);
			$titre = $outputlangs->transnoentities('DocORDER1'); 
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
		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFillColorArray($bgcolor);
		$pdf->SetFont('','', $default_font_size - 2);

		$pdf->RoundedRect($this->marge_gauche, $tab_top, $this->page_largeur-$this->marge_gauche-$this->marge_droite, $tab_height, 2, $round_corner = '0110', 'S', $this->style, array());
		
		if (! empty($conf->global->ULTIMATE_ORDERS_WITH_LINE_NUMBER))
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
			$pdf->line($this->posxnumber+$this->number_width, $tab_top, $this->posxnumber+$this->number_width, $tab_top + $tab_height);
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
					$pdf->SetXY ($this->posxref+1, $tab_top);
					$pdf->MultiCell($this->posxdesc-$this->posxref, 6, $outputlangs->transnoentities("RefShort"),0,'L',1);
					$pdf->SetXY ($this->posxdesc, $tab_top);
					$pdf->MultiCell($this->posxtva-$this->posxdesc, 6, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
				}
				else
				{
					$pdf->SetXY ($this->posxdesc+1, $tab_top);
					$pdf->MultiCell($this->posxtva-$this->marge_gauche, 6, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
				}
			}
			else			
			{			
				$pdf->SetXY ($this->posxdesc+1, $tab_top);
				$pdf->MultiCell($this->posxtva-$this->marge_gauche, 6, $outputlangs->transnoentities("Designation"), 0, 'L', 1);
			}
		}
		
		if (! empty($conf->global->ULTIMATE_GENERATE_ORDERS_WITH_PICTURE))
		{
			//$pdf->line($this->posxpicture-1, $tab_top, $this->posxpicture-1, $tab_top + $tab_height);
			if (empty($hidetop))
			{
				
			}
		}	

		if ($conf->global->ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT == "no" && empty($conf->global->ULTIMATE_SHOW_HIDE_VAT_COLUMN))
		{
			$pdf->line($this->posxtva-1, $tab_top, $this->posxtva-1, $tab_top + $tab_height);
			if (empty($hidetop))
			{
				$pdf->SetXY($this->posxtva-3, $tab_top);
				$pdf->MultiCell($this->posxup-$this->posxtva+3,6, $outputlangs->transnoentities("VAT"), 0, 'C', 1);
			}
		}
		
		// Unit price before discount
		if (empty($conf->global->ULTIMATE_SHOW_HIDE_PUHT))
		{
			$pdf->line($this->posxup-1, $tab_top, $this->posxup-1, $tab_top + $tab_height);
			if (empty($hidetop))
			{
				$pdf->SetXY ($this->posxup-1, $tab_top);
				$pdf->MultiCell($this->posxqty-$this->posxup+2,6, $outputlangs->transnoentities("PriceUHT"), 0, 'C', 1);
			}
		}
		
		if (empty($conf->global->ULTIMATE_SHOW_HIDE_QTY))
		{
			$pdf->line($this->posxqty-1, $tab_top, $this->posxqty-1, $tab_top + $tab_height);
			if (empty($hidetop))
			{
				$pdf->SetXY($this->posxqty-1, $tab_top);
				if($conf->global->PRODUCT_USE_UNITS)
				{
					$pdf->MultiCell($this->posxunit-$this->posxqty+1,6, $outputlangs->transnoentities("Qty"),'','C', 1);
				}
				else
				{
					$pdf->MultiCell($this->posxdiscount-$this->posxqty+1,6, $outputlangs->transnoentities("Qty"),'','C', 1);
				}
			}
		}

		if($conf->global->PRODUCT_USE_UNITS) 
		{
			$pdf->line($this->posxunit - 1, $tab_top, $this->posxunit - 1, $tab_top + $tab_height);
			if (empty($hidetop)) 
			{
				$pdf->SetXY($this->posxunit, $tab_top);
				$pdf->MultiCell($this->posxdiscount - $this->posxunit, 6, $outputlangs->transnoentities("Unit"), '', 'C', 1);
			}
		}

        $pdf->line($this->posxdiscount-1, $tab_top, $this->posxdiscount-1, $tab_top + $tab_height);
		if (empty($hidetop))
		{
			if ($this->atleastonediscount)
			{
				$pdf->SetXY ($this->posxdiscount-1, $tab_top);
				$pdf->MultiCell($this->postotalht-$this->posxdiscount+1,6, $outputlangs->transnoentities("ReductionShort"), 0, 'C', 1);
			}
		}

        if ($this->atleastonediscount)
        {
            $pdf->line($this->postotalht-1, $tab_top, $this->postotalht-1, $tab_top + $tab_height);
        }
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
	function _pagehead(&$pdf, $object, $showaddress, $outputlangs, $titlekey="Order")
	{
		global $conf,$langs,$hookmanager;
		
		$outputlangs->load("main");
		$outputlangs->load("bills");
		$outputlangs->load("propal");
		$outputlangs->load("companies");
		$outputlangs->load("orders");
		$outputlangs->load("commercial");
        $outputlangs->load("deliveries");
		
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

		//affiche repere de pliage	
		if (! empty($conf->global->MAIN_DISPLAY_ORDERS_FOLD_MARK))
		{
			$pdf->Line(0,($this->page_hauteur)/3,3,($this->page_hauteur)/3);
		}

		pdf_pagehead($pdf,$outputlangs,$this->page_hauteur);

		//Affiche le filigrane brouillon - Print Draft Watermark
		if($object->statut==0 && (! empty($conf->global->COMMANDE_DRAFT_WATERMARK)) )
		{
            pdf_watermark($pdf,$outputlangs,$this->page_hauteur,$this->page_largeur,'mm',$conf->global->COMMANDE_DRAFT_WATERMARK);
		}

		$pdf->SetTextColorArray($textcolor);
		$pdf->SetFont('','B', $default_font_size + 3);

		$posy=$this->marge_haute;
		$posx=$this->page_largeur-$this->marge_droite-100;

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
		
		//Display Thirdparty barcode at top				
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_ORDERS_WITH_TOP_BARCODE))
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
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_ORDERS_WITH_TOP_QRCODE))
		{
			$code = pdf_codeContents(); //get order link
			$pdf->write2DBarcode($code, 'QRCODE,L', $posxQRcode, $posy, $heightQRcode, $heightQRcode, $styleQr, 'N');
		}
		// My Company QR-code
		if (! empty($conf->global->ULTIMATEPDF_GENERATE_ORDERS_WITH_MYCOMP_QRCODE))
		{
			$code = pdf_mycompCodeContents();
			$pdf->write2DBarcode($code, 'QRCODE,L', $posxQRcode, $posy, $heightQRcode, $heightQRcode, $styleQr, 'N');
		}
		
		// Example using extrafields for new title of document
		$title_key=(empty($object->array_options['options_newtitle']))?'':($object->array_options['options_newtitle']);	
		$extrafields = new ExtraFields($this->db);
		$extralabels = $extrafields->fetch_name_optionals_label ($object->table_element, true);
		if (is_array($extralabels ) && key_exists('newtitle', $extralabels) && !empty($title_key)) 
		{
			$titlekey = $extrafields->showOutputField ('newtitle', $title_key);
		}

		$pdf->SetFont('','B', $default_font_size + 3);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		$title=$outputlangs->transnoentities($titlekey);
		$pdf->MultiCell(100, 3, $title, '', 'R');

		$pdf->SetFont('','B', $default_font_size + 2);

		$posy+=6;
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("Ref")." : " . $outputlangs->convToOutputCharset($object->ref), '', 'R');

		$posy+=6;
		$pdf->SetFont('','', $default_font_size - 1);
		$pdf->SetXY($posx,$posy);
		$pdf->SetTextColorArray($textcolor);
		$pdf->MultiCell(100, 4, $outputlangs->transnoentities("OrderDate")." : " . dol_print_date($object->date,"%d %b %Y",false,$outputlangs,true), '', 'R');
		
		$posy+=4;
		
		// Show list of linked objects
		$posy = pdf_writeLinkedObjects($pdf, $object, $outputlangs, $posx, $posy, 100, 3, 'R', $default_font_size);
		
		if ($showaddress)
		{
			// Sender properties
			$carac_emetteur = pdf_order_build_address($outputlangs, $this->emetteur, $object->thirdparty);

			// Show sender
			$delta=35-$logo_height;
			$posy=$logo_height+$this->marge_haute+$delta;	
			$posx=$this->marge_gauche;
			if (($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','SHIPPING')) && ($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1) || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','SHIPPING')) && ($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1)  || ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','BILLING')) && ($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1) || (! empty($conf->global->MAIN_PUBLIC_NOTE_IN_ADDRESS) && !empty($object->note_public)))
			{
				$hautcadre=68;
			}
			else
			{
				$hautcadre=52;
			}
			$widthrecbox=$conf->global->ULTIMATE_WIDTH_RECBOX;
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
			
			// If SHIPPING and BILLING contact defined, we use it
			if ($arrayidcontact=$object->getIdContact('external','BILLING') && $object->getIdContact('external','SHIPPING'))
			{
				if (($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1) || !empty($object->note_public))
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
				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show invoice address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $outputlangs->transnoentities("BillAddress"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client, 0, 'L');	
				
				// If SHIPPING contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','SHIPPING');
			
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
				
				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show shipping address
				$posy=$logo_height+$this->marge_haute+$delta;
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy+$hautcadre*0.5, $widthrecboxrecipient, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy+$hautcadre*0.5);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $outputlangs->transnoentities("DeliveryAddress"), 0, 'R');	
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1+$hautcadre*0.5);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();

				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client, 0, 'L');
			}
			// If SHIPPING and CUSTOMER contact defined, we use it
			elseif ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','SHIPPING'))
			{
				if (($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1) || !empty($object->note_public))
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
				
				// Recipient address
				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show invoice address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $outputlangs->transnoentities("TypeContact_commande_external_CUSTOMER"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+2);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client, 0, 'L');	
				
				// If SHIPPING contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','SHIPPING');
			
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
				
				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show shipping address
				$posy=$logo_height+$this->marge_haute+$delta;
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy+$hautcadre*0.5, $widthrecboxrecipient, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy+$hautcadre*0.5);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $outputlangs->transnoentities("DeliveryAddress"), 0, 'R');	
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+2+$hautcadre*0.5);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();

				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client, 0, 'L');
			}
			// If BILLING and CUSTOMER contact defined, we use it
			elseif ($arrayidcontact=$object->getIdContact('external','CUSTOMER') && $object->getIdContact('external','BILLING'))
			{
				if (($conf->global->ULTIMATE_PDF_ORDER_ADDALSOTARGETDETAILS == 1) || !empty($object->note_public))
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
				
				// Recipient address
				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show invoice address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $outputlangs->transnoentities("TypeContact_commande_external_CUSTOMER"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client, 0, 'L');	
				
				// If BILLING contact defined on invoice, we use it
				$usecontact=false;
				$arrayidcontact=$object->getIdContact('external','BILLING');
			
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
				
				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show shipping address
				$posy=$logo_height+$this->marge_haute+$delta;
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy+$hautcadre*0.5, $widthrecboxrecipient, $hautcadre*0.5, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy+$hautcadre*0.5);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $outputlangs->transnoentities("BillAddress"), 0, 'R');	
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1+$hautcadre*0.5);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();

				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5,4, $carac_client, 0, 'L');
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

				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$delta;		
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;	
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show billing address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx+2,$posy);		
				$pdf->MultiCell($widthrecboxrecipient-5,4, $outputlangs->transnoentities("BillAddress"), 0, 'R');
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+1);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client, 0, 'L');

			}
			elseif ($arrayidcontact=$object->getIdContact('external','SHIPPING'))
			{			
				// If SHIPPING contact defined, we use it
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

				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,$object->contact,$usecontact,'target');

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$delta;	
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);
				
				// Show shipping address
				$pdf->SetXY($posx,$posy);
				$pdf->SetAlpha($opacity);				
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->MultiCell($widthrecboxrecipient,4, $outputlangs->transnoentities("DeliveryAddress"), 0, 'R');

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client, 0, 'L');			
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

				$carac_client=pdf_order_build_address($outputlangs,$this->emetteur,$object->thirdparty,($usecontact?$object->contact:''),$usecontact,'target');

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$delta;		
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;	
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;
				
				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);

				// Show Contact_commande_external_CUSTOMER address
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);
				$pdf->SetXY($posx,$posy);		
				$pdf->MultiCell($widthrecboxrecipient,4, $outputlangs->transnoentities("TypeContact_commande_external_CUSTOMER"), 0, 'R');
				
				// Show recipient name
				$pdf->SetXY($posx+2,$posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client, 0, 'L');

			}
			else
			{
				$thirdparty = $object->thirdparty;
				// Recipient name
				$carac_client_name= pdfBuildThirdpartyName($thirdparty, $outputlangs);
				// Recipient address
				$carac_client=pdf_order_build_address($outputlangs, $this->emetteur, $object->thirdparty, '', 0, 'target');

				// Show recipient
				$widthrecboxrecipient=$this->page_largeur-$this->marge_droite-$this->marge_gauche-$conf->global->ULTIMATE_WIDTH_RECBOX-2;
				$posy=$logo_height+$this->marge_haute+$delta;
				$posx=$this->page_largeur-$this->marge_droite-$widthrecboxrecipient;
				if (! empty($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT) && ($conf->global->ULTIMATE_INVERT_SENDER_RECIPIENT != "no")) $posx=$this->marge_gauche;

				// Show recipient frame
				$pdf->SetTextColorArray($textcolor);
				$pdf->SetFont('','', $default_font_size - 2);
				
				// Show shipping address
				$pdf->SetXY($posx,$posy-4);	
				$pdf->SetAlpha($opacity);
				$pdf->RoundedRect($posx, $posy, $widthrecboxrecipient, $hautcadre, 2, $round_corner = '1111', 'FD', array(), $bgcolor);
				$pdf->SetAlpha(1);

				// Show recipient name
				$pdf->SetXY($posx+2,$posy+3);
				$pdf->SetFont('','B', $default_font_size);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client_name, 0, 'L');
				
				$posy = $pdf->getY();
				
				// Show recipient information
				$pdf->SetFont('','', $default_font_size - 1);
				$pdf->SetXY($posx+2,$posy);
				$pdf->MultiCell($widthrecboxrecipient-5, 4, $carac_client, 0, 'L');				
			}				

			// Other informations

			$pdf->SetFillColor(255,255,255);

			// Availability Period
			$width=$main_page/5 -1.5;
			$RoundedRectHeight = $this->marge_haute+$logo_height+$hautcadre+$delta+2;
			$pdf->SetAlpha($opacity);			
			$pdf->RoundedRect($this->marge_gauche, $RoundedRectHeight, $width, 6, 2, $round_corner = '1001', 'FD', array(), $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche,$RoundedRectHeight+0.5);
	        $pdf->SetTextColorArray($textcolor);
			$text='<div style="line-height:90%;">'.$outputlangs->transnoentities("AvailabilityPeriod").'</div>';
	        $pdf->writeHTMLCell($width, 5,$this->marge_gauche,$RoundedRectHeight+0.5, $text, 0, 0, false, true, 'C', true);

	        if (! empty($object->availability_id))
	        {
				$form = new Form($this->db);
				$form->load_cache_availability();
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$availability=$form->cache_availability[$object->availability_id]['label'];
				$pdf->writeHTMLCell($width, 6, $this->marge_gauche, $RoundedRectHeight+6, $availability, 0, 0, false, true, 'C', true);
	        }
			else
			{
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}

	        // Delivery date
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width+2, $RoundedRectHeight, $width, 6, 2, $round_corner = '1001', 'FD', array(), $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width+2,$RoundedRectHeight);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 5, $outputlangs->transnoentities("DeliveryDate"), 0, 'C', false);

	        if (! empty($object->date_livraison))
	        {
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width+2,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
	        	$pdf->MultiCell($width, 6, dol_print_date($object->date_livraison,"day",false,$outputlangs,true), '0', 'C');
	        }
			else
			{
				$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width+2,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}

	        // Commercial Interlocutor
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width*2+4, $RoundedRectHeight, $width, 6, 2, $round_corner = '1001', 'FD', array(), $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+0.5);
	        $pdf->SetTextColorArray($textcolor);
			$text='<div style="line-height:90%;">'.$outputlangs->transnoentities("SalesRepresentative").'</div>';
	        $pdf->writeHTMLCell($width, 5,$this->marge_gauche+$width*2+4,$RoundedRectHeight+0.5, $text, 0, 0, false, true, 'C', true);
	        
	        $contact_id = $object->getIdContact('internal','SALESREPFOLL');

	        if (! empty($contact_id))
	        {
	        	$object->fetch_user($contact_id[0]);
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 5, $object->user->firstname.' '.$object->user->lastname, 0, 'C', false);
				$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+9);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 7, $object->user->office_phone, '0', 'C');
	        }
	        else if ($object->user_author_id)
	        {
	        	$object->fetch_user($object->user_author_id);
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 6, $object->user->firstname.' '.$object->user->lastname, '0', 'C');
				$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+9);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 7, $object->user->office_phone, '0', 'C');
	        }
			else
			{
				$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*2+4,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}

	        // Customer code
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width*3+6, $RoundedRectHeight, $width, 6, 2, $round_corner = '1001', 'FD', array(), $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*3+6,$RoundedRectHeight);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 5, $outputlangs->transnoentities("CustomerCode"), 0, 'C', false);

	        if ($object->thirdparty->code_client)
	        {
	        	$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*3+6,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
	        	$pdf->MultiCell($width, 6, $outputlangs->transnoentities($object->thirdparty->code_client), '0', 'C');
	        }
			else
			{
				$pdf->SetFont('','', $default_font_size - 2);
	        	$pdf->SetXY($this->marge_gauche+$width*3+6,$RoundedRectHeight+6);
	        	$pdf->SetTextColorArray($textcolor);
				$pdf->SetFillColor(255,255,255);
				$pdf->MultiCell($width, 6, '', '0', 'C');
			}
			
			// Customer ref
			$pdf->SetAlpha($opacity);
			$pdf->RoundedRect($this->marge_gauche+$width*4+8, $RoundedRectHeight, $width, 6, 2, $round_corner = '1001', 'FD', array(), $bgcolor);
			$pdf->SetAlpha(1);
	        $pdf->SetFont('','B', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*4+8,$RoundedRectHeight);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 5, $outputlangs->transnoentities("RefCustomer"), 0, 'C', false);
	        
			if ($object->ref_client)
			{
	        $pdf->SetFont('','', $default_font_size - 2);
	        $pdf->SetXY($this->marge_gauche+$width*4+8,$RoundedRectHeight+6);
	        $pdf->SetTextColorArray($textcolor);
	        $pdf->MultiCell($width, 6, $object->ref_client, '0', 'C');	
			}
		}
		$pdf->SetTextColorArray($textcolor);
	}

	/**
	 *   	Show footer of page. Need this->emetteur object
     *
	 *   	@param	PDF			&$pdf     			PDF
	 * 		@param	Object		$object				Object to show
	 *      @param	Translate	$outputlangs		Object lang for output
	 *      @param	int			$hidefreetext		1=Hide free text
	 *      @return	int								Return height of bottom margin including footer text
	 */
	function _pagefoot(&$pdf,$object,$outputlangs,$hidefreetext=0)
	{
		global $conf;
		$showdetails=$conf->global->MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS;
		return pdf_ultimatepagefoot($pdf, $outputlangs, 'ORDER_FREE_TEXT', $this->emetteur, $this->marge_basse, $this->marge_gauche, $this->page_hauteur, $object, $showdetails, $hidefreetext, $footertextcolor);
	}

}

?>