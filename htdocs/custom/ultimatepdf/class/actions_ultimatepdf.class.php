<?php
/* Copyright (C) 2011-2012 Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2017 Philippe Grand <philippe.grand@atoo-net.com>
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
 *	\file       /ultimatepdf/class/actions_ultimatepdf.class.php
 *	\ingroup    ultimatepdf
 *	\brief      ultimatepdf designs actions class files
 */

dol_include_once('/ultimatepdf/class/dao_ultimatepdf.class.php','DaoUltimatepdf');
require_once DOL_DOCUMENT_ROOT.'/core/lib/admin.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/ajax.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/images.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

/**
 *	\class      ActionsUltimatepdf
 *	\brief      Ultimatepdf designs actions class files
 */
class ActionsUltimatepdf
{
	/**
     * @var DoliDb Database handler
     */
    public $db;
	
	/**
     * @var string instance of class
     */
    public $dao;

	/**
     * @var string instance of class
     */
    public $mesg;
	
	/**
	 * @var string 		Error string
     * @deprecated		Use instead the array of error strings
     * @see             errors
	 */
	public $error;
	
	/**
	 * @var string[] Array of error strings
	 */
	public $errors= array();
	
	/**
	 * @var int Numero de l'erreur
	 */
	public $errno = 0;
	
	/**
	 * @var int The object identifier
	 */
	public $id;
	
	/**
	 * @var
	 */
	public $template_dir;
	
	/**
	 * @var
	 */
	public $template;

	/**
	 * @var
	 */
	public $label;
	
	/**
	 * @var
	 */
	public $description;
	
	/**
	 * @var
	 */
	public $value;
	
	/**
	 * @var
	 */
	public $cancel;
	
	/**
	 * @var
	 */
	public $dashdotted;
	
	/**
	 * @var
	 */
	public $bgcolor;
	
	/**
	 * @var
	 */
	public $opacity;
	
	/**
	 * @var
	 */
	public $bordercolor;
	
	/**
	 * @var
	 */
	public $textcolor;
	
	/**
	 * @var
	 */
	public $footertextcolor;
	
	/**
	 * @var
	 */
	public $qrcodecolor;
	
	/**
	 * @var
	 */
	public $widthnumbering;
	
	/**
	 * @var
	 */
	public $widthdesc;
	
	/**
	 * @var
	 */
	public $widthvat;
	
	/**
	 * @var
	 */
	public $widthup;
	
	/**
	 * @var
	 */
	public $widthqty;
	
	/**
	 * @var
	 */
	public $widthunit;
	
	/**
	 * @var
	 */
	public $widthdiscount;
	
	/**
	 * @var
	 */
	public $withref;
	
	/**
	 * @var
	 */
	public $widthref;
	
	/**
	 * @var
	 */
	public $withoutvat;
	
	/**
	 * @var
	 */
	public $showdetails;
	
	/**
	 * @var
	 */
	public $otherlogo;
	
	/**
	 * @var
	 */
	public $otherfont;
	
	/**
	 * @var
	 */
	public $heightforfreetext;
	
	/**
	 * @var
	 */
	public $freetextfontsize;
	
	/**
	 * @var
	 */
	public $usebackground;
	
	/**
	 * @var
	 */
	public $imglinesize;
	
	/**
	 * @var
	 */
	public $logoheight;
	
	/**
	 * @var
	 */
	public $logowidth;
	
	/**
	 * @var
	 */
	public $otherlogoheight;
	
	/**
	 * @var
	 */
	public $otherlogowidth;
	
	/**
	 * @var
	 */
	public $invertSenderRecipient;
	
	/**
	 * @var
	 */
	public $widthrecbox;
	
	/**
	 * @var
	 */
	public $marge_gauche;
	
	/**
	 * @var
	 */
	public $marge_droite;
	
	/**
	 * @var
	 */
	public $marge_haute;
	
	/**
	 * @var
	 */
	public $marge_basse;

	/**
	 * @var
	 */
	public $options=array();
	
	/**
	 * @var
	 */
	public $designs=array();
	
	/**
	 * @var
	 */
	public $tpl=array();
	
	/**
	 * @var For Hookmanager return
	 */
	public $resprints = '';


	/**
	 * 	Constructor
	 *
	 * 	@param	DoliDB	$db		Database handler
	 */
	public function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * Instantiation of DAO class
	 *
	 * @return	void
	 */
	private function getInstanceDao()
	{
		if (! is_object($this->dao))
		{
			$this->dao = new DaoUltimatepdf($this->db);
		}
	}


	/**
	 * 	Enter description here ...
	 *
	 * 	@param	string	$action		Action type
	 */
	function doActions($parameters = false, &$object, &$action = '')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		global $conf,$user,$langs,$hookmanager;

		$this->getInstanceDao();
		
		$id=GETPOST('id','int');
		$label=GETPOST('label','alpha');
		$description=GETPOST('description','alpha');
		$value=GETPOST('value','int');
		$cancel=GETPOST('cancel');
		$dashdotted=GETPOST('dashdotted');
		$bgcolor=GETPOST('bgcolor');
		$opacity=GETPOST('opacity');
		$bordercolor=GETPOST('bordercolor');
		$textcolor=GETPOST('textcolor');
		$footertextcolor=GETPOST('footertextcolor');
		$qrcodecolor=GETPOST('qrcodecolor');
		$widthnumbering=GETPOST('widthnumbering');
		$widthdesc=GETPOST('widthdesc');
		$widthvat=GETPOST('widthvat');
		$widthup=GETPOST('widthup');
		$widthunit=GETPOST('widthunit');
		$widthqty=GETPOST('widthqty');
		$widthdiscount=GETPOST('widthdiscount');
		$withref=GETPOST('withref');
		$widthref=GETPOST('widthref');
		$withoutvat=GETPOST('withoutvat');
		$showdetails=GETPOST('showdetails');
		$otherlogo=GETPOST('otherlogo');
		$otherlogo_file=GETPOST('otherlogo_file');
		$otherfont=GETPOST('otherfont');
		$heightforfreetext=GETPOST('heightforfreetext');
		$freetextfontsize=GETPOST('freetextfontsize');
		$usebackground=GETPOST('usebackground');
		$imglinesize=GETPOST('imglinesize');
		$logoheight=GETPOST('logoheight');
		$logowidth=GETPOST('logowidth');
		$otherlogoheight=GETPOST('otherlogoheight');
		$otherlogowidth=GETPOST('otherlogowidth');
		$invertSenderRecipient=GETPOST('invertSenderRecipient');
		$widthrecbox=GETPOST('widthrecbox');
		$marge_gauche=GETPOST('marge_gauche');
		$marge_droite=GETPOST('marge_droite');
		$marge_haute=GETPOST('marge_haute');
		$marge_basse=GETPOST('marge_basse');

		
		if (is_object($object) && $object->table_element == 'propal')
		{
			if ( ! empty ( $object->id ) && $action == 'filemerge') 
			{			
				dol_include_once ( '/ultimatepdf/class/propalmergedpdf.class.php' );
				
				$filetomerge_file_array=GETPOST('filetoadd');			
				
				//Delete all files already associated
				$filetomerge = new Propalmergedpdf ( $this->db );		

				$filetomerge->delete_by_propal ($user, $object->id);			
					
				//for each file checked add it to the proposal
				if (is_array($filetomerge_file_array)) 
				{
					foreach ($filetomerge_file_array as $filetomerge_file) 
					{
						$filetomerge->fk_propal = $object->id;
						$filetomerge->file_name = $filetomerge_file;					
						$filetomerge->create ( $user );
					} 
				}		
			}
			return 0;
		}
		
		if (is_object($object) && $object->table_element == 'facture')
		{
			if ( ! empty ( $object->id ) && $action == 'filemerge') 
			{		
				dol_include_once ( '/ultimatepdf/class/invoicemergedpdf.class.php' );
				
				$filetomerge_file_array=GETPOST('filetoadd');			
				
				//Delete all files already associated
				$filetomerge = new Invoicemergedpdf ( $this->db );		

				$filetomerge->delete_by_invoice ($user, $object->id);			
					
				//for each file checked add it to the invoice
				if (is_array($filetomerge_file_array)) 
				{
					foreach ($filetomerge_file_array as $filetomerge_file) 
					{
						$filetomerge->fk_facture = $object->id;
						$filetomerge->file_name = $filetomerge_file;					
						$filetomerge->create ( $user );
					} 
				}		
			}
			return 0;
		}
		
		if (is_object($object) && $object->table_element == 'commande')
		{
			if ( ! empty ( $object->id ) && $action == 'filemerge') 
			{		
				dol_include_once ( '/ultimatepdf/class/ordermergedpdf.class.php' );
				
				$filetomerge_file_array=GETPOST('filetoadd');			
				
				//Delete all files already associated
				$filetomerge = new Ordermergedpdf ( $this->db );		
				
				$filetomerge->delete_by_order ($user, $object->id);			
					
				//for each file checked add it to the order
				if (is_array($filetomerge_file_array)) 
				{
					foreach ($filetomerge_file_array as $filetomerge_file) 
					{
						$filetomerge->fk_commande = $object->id;
						$filetomerge->file_name = $filetomerge_file;				
						$filetomerge->create ( $user );
					} 
				}		
			}
			return 0;
		}
		
		if (is_object($object) && $object->table_element == 'contrat')
		{
			if (!empty($object->id) && $action == 'filemerge') 
			{		
				dol_include_once('/ultimatepdf/class/contractmergedpdf.class.php');
				
				$filetomerge_file_array=GETPOST('filetoadd');			
				
				//Delete all files already associated
				$filetomerge = new Contractmergedpdf($this->db);		

				$filetomerge->delete_by_contract($user, $object->id);			
					
				//for each file checked add it to the contract
				if (is_array($filetomerge_file_array))
				{
					foreach ($filetomerge_file_array as $filetomerge_file) 
					{
						$filetomerge->fk_contrat = $object->id;
						$filetomerge->file_name = $filetomerge_file;		
						$filetomerge->create($user);
					} 
				}		
			}
			return 0;
		}
		
		if (is_object($object) && $object->table_element == 'commande_fournisseur')
		{
			if (! empty ($object->id) && $action == 'filemerge') 
			{		
				dol_include_once ('/ultimatepdf/class/supplierordermergedpdf.class.php');
				
				$filetomerge_file_array=GETPOST('filetoadd');			
				
				//Delete all files already associated
				$filetomerge = new Supplierordermergedpdf($this->db);		
				
				$filetomerge->delete_by_supplierorder ($user, $object->id);			
					
				//for each file checked add it to the supplier order
				if (is_array($filetomerge_file_array)) 
				{
					foreach ($filetomerge_file_array as $filetomerge_file) 
					{					
						$filetomerge->fk_commande = $object->id;
						$filetomerge->file_name = $filetomerge_file;						
						$filetomerge->create ($user);
					} 
				}		
			}
			return 0;
		}
		
		if (is_object($object) && $object->table_element == 'supplier_proposal')
		{
			if (! empty ($object->id) && $action == 'filemerge') 
			{		
				dol_include_once ('/ultimatepdf/class/supplierproposalmergedpdf.class.php');
				
				$filetomerge_file_array=GETPOST('filetoadd');			
				
				//Delete all files already associated
				$filetomerge = new SupplierProposalmergedpdf($this->db);		
				
				$filetomerge->delete_by_supplierproposal ($user, $object->id);			
					
				//for each file checked add it to the supplier order
				if (is_array($filetomerge_file_array)) 
				{
					foreach ($filetomerge_file_array as $filetomerge_file) 
					{
						$filetomerge->fk_supplier_proposal = $object->id;
						$filetomerge->file_name = $filetomerge_file;						
						$filetomerge->create ($user);
					} 
				}		
			}
			return 0;
		}

		if (GETPOST('add') && empty($this->cancel) && $user->admin)
		{
			$error=0;

			if (! $label)
			{
				$error++;
				array_push($this->errors, $langs->trans("ErrorFieldRequired",$langs->transnoentities("Label") ) );
				$action = 'create';
			}

			// Verify if label already exist in database
			if ($label)
			{
				$this->dao->getDesigns();
				if (! empty($this->dao->designs))
				{
					$label = strtolower(trim($label));

					foreach($this->dao->designs as $design)
					{
						if (strtolower($design->label) == $label) $error++;
					}
					if ($error)
					{
						array_push($this->errors, $langs->trans("ErrorDesignLabelAlreadyExist") );
						$action = 'create';
					}
				}
			}

			if (! $error)
			{
				$this->db->begin();

				$this->dao->label = $label;
				$this->dao->description = $description;

				$this->dao->options['dashdotted'] = $dashdotted;
				$this->dao->options['bgcolor'] = $bgcolor;
				$this->dao->options['opacity'] = $opacity;
				$this->dao->options['bordercolor'] = $bordercolor;
				$this->dao->options['textcolor'] = $textcolor;
				$this->dao->options['footertextcolor'] = $footertextcolor;
				$this->dao->options['qrcodecolor'] = $qrcodecolor;
				$this->dao->options['widthnumbering'] = $widthnumbering;
				$this->dao->options['widthdesc'] = $widthdesc;
				$this->dao->options['widthvat'] = $widthvat;
				$this->dao->options['widthup'] = $widthup;
				$this->dao->options['widthqty'] = $widthqty;
				$this->dao->options['widthunit'] = $widthunit;
				$this->dao->options['widthdiscount'] = $widthdiscount;
				$this->dao->options['withref'] = $withref;
				$this->dao->options['widthref'] = $widthref;
				$this->dao->options['withoutvat'] = $withoutvat;
				$this->dao->options['showdetails'] = $showdetails;
				$this->dao->options['otherlogo'] = $otherlogo;
				$this->dao->options['otherlogo_file'] = $otherlogo_file;
				$this->dao->options['otherfont'] = $otherfont;
				$this->dao->options['heightforfreetext'] = $heightforfreetext;
				$this->dao->options['freetextfontsize'] = $freetextfontsize;
				$this->dao->options['usebackground'] = $usebackground;
				$this->dao->options['imglinesize'] = $imglinesize;
				$this->dao->options['logoheight'] = $logoheight;
				$this->dao->options['logowidth'] = $logowidth;
				$this->dao->options['otherlogoheight'] = $otherlogoheight;
				$this->dao->options['otherlogowidth'] = $otherlogowidth;
				$this->dao->options['invertSenderRecipient'] = $invertSenderRecipient;
				$this->dao->options['widthrecbox'] = $widthrecbox;
				$this->dao->options['marge_gauche'] = $marge_gauche;
				$this->dao->options['marge_droite'] = $marge_droite;
				$this->dao->options['marge_haute'] = $marge_haute;
				$this->dao->options['marge_basse'] = $marge_basse;
				

				$id = $this->dao->create($user);
				if ($id <= 0)
				{
					$error++;
					$errors=($this->dao->error ? array($this->dao->error) : $this->dao->errors);
					$action = 'create';
				}

				if (! $error && $id > 0)
				{
					$this->db->commit();
				}
				else
				{
					$this->db->rollback();
				}
			}
		}

		if ($action == 'edit' && $user->admin)
		{
			$error=0;

			if ($this->dao->fetch($id) < 0)
			{
				$error++;
				//array_push($this->errors, $langs->trans("ErrorDesignIsNotValid"));
				$_GET["action"] = $_POST["action"] = '';
			}
		}

		if (GETPOST('update') && $id && $user->admin)
		{
			$error=0;

			$ret = $this->dao->fetch($id);
			if ($ret < 0)
			{
				$error++;
				array_push($this->errors, $langs->trans("ErrorDesignIsNotValid"));
				$action = '';
			}
			else if (! $label)
			{
				$error++;
				array_push($this->errors, $langs->trans("ErrorFieldRequired",$langs->transnoentities("Label") ) );
				$action = 'edit';
			}

			if (! $error)
			{
				$this->db->begin();

				$this->dao->label = $label;
				$this->dao->description	= $description;

				$this->dao->options['dashdotted'] = (GETPOST('dashdotted') ? GETPOST('dashdotted') : null);
				$this->dao->options['bgcolor'] = (GETPOST('bgcolor') ? GETPOST('bgcolor') : null);
				$this->dao->options['opacity'] = (GETPOST('opacity') ? GETPOST('opacity') : null);
				$this->dao->options['bordercolor'] = (GETPOST('bordercolor') ? GETPOST('bordercolor') : null);
				$this->dao->options['textcolor'] = (GETPOST('textcolor') ? GETPOST('textcolor') : null);
				$this->dao->options['footertextcolor'] = (GETPOST('footertextcolor') ? GETPOST('footertextcolor') : null);
				$this->dao->options['qrcodecolor'] = (GETPOST('qrcodecolor') ? GETPOST('qrcodecolor') : null);
				$this->dao->options['widthnumbering'] = (GETPOST('widthnumbering') ? GETPOST('widthnumbering') : null);
				$this->dao->options['widthdesc'] = (GETPOST('widthdesc') ? GETPOST('widthdesc') : null);
				$this->dao->options['widthvat'] = (GETPOST('widthvat') ? GETPOST('widthvat') : null);
				$this->dao->options['widthup'] = (GETPOST('widthup') ? GETPOST('widthup') : null);
				$this->dao->options['widthqty'] = (GETPOST('widthqty') ? GETPOST('widthqty') : null);
				$this->dao->options['widthunit'] = (GETPOST('widthunit') ? GETPOST('widthunit') : null);
				$this->dao->options['widthdiscount'] = (GETPOST('widthdiscount') ? GETPOST('widthdiscount') : null);
				$this->dao->options['withref'] = (GETPOST('withref') ? GETPOST('withref') : 'no');
				$this->dao->options['widthref'] = (GETPOST('widthref') ? GETPOST('widthref') : null);
				$this->dao->options['withoutvat'] = (GETPOST('withoutvat') ? GETPOST('withoutvat') : 'no');
				$this->dao->options['showdetails'] = (GETPOST('showdetails') ? GETPOST('showdetails') : null);
				$this->dao->options['otherlogo_file'] = (GETPOST('otherlogo_file') ? GETPOST('otherlogo_file') : null);
				$this->dao->options['otherfont'] = (GETPOST('otherfont') ? GETPOST('otherfont') : null);
				$this->dao->options['heightforfreetext'] = (GETPOST('heightforfreetext') ? GETPOST('heightforfreetext') : null);
				$this->dao->options['freetextfontsize'] = (GETPOST('freetextfontsize') ? GETPOST('freetextfontsize') : null);
				$this->dao->options['usebackground'] = (GETPOST('usebackground') ? GETPOST('usebackground') : null);
				$this->dao->options['imglinesize'] = (GETPOST('imglinesize') ? GETPOST('imglinesize') : null);
				$this->dao->options['logoheight'] = (GETPOST('logoheight') ? GETPOST('logoheight') : null);
				$this->dao->options['logowidth'] = (GETPOST('logowidth') ? GETPOST('logowidth') : null);
				$this->dao->options['otherlogoheight'] = (GETPOST('otherlogoheight') ? GETPOST('otherlogoheight') : null);
				$this->dao->options['otherlogowidth'] = (GETPOST('otherlogowidth') ? GETPOST('otherlogowidth') : null);
				$this->dao->options['invertSenderRecipient'] = (GETPOST('invertSenderRecipient') ? GETPOST('invertSenderRecipient') : 'no');
				$this->dao->options['widthrecbox'] = (GETPOST('widthrecbox') ? GETPOST('widthrecbox') : null);
				$this->dao->options['marge_gauche'] = (GETPOST('marge_gauche') ? GETPOST('marge_gauche') : null);
				$this->dao->options['marge_droite'] = (GETPOST('marge_droite') ? GETPOST('marge_droite') : null);
				$this->dao->options['marge_haute'] = (GETPOST('marge_haute') ? GETPOST('marge_haute') : null);
				$this->dao->options['marge_basse'] = (GETPOST('marge_basse') ? GETPOST('marge_basse') : null);
				
				$ret = $this->dao->update($id,$user);

				if ($ret <= 0)
				{
					$error++;
					$errors=($this->dao->error ? array($this->dao->error) : $this->dao->errors);
					$action = 'edit';
				}

				if (! $error && $ret > 0)
				{

					dolibarr_set_const($this->db, "ULTIMATE_DASH_DOTTED", $dashdotted,'chaine',0,'',$conf->entity);

					dolibarr_set_const($this->db, "ULTIMATE_BGCOLOR_COLOR", $bgcolor,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_BGCOLOR_OPACITY", $bgcolor,'chaine',0,'',$conf->entity);

					dolibarr_set_const($this->db, "ULTIMATE_BORDERCOLOR_COLOR", $bordercolor,'chaine',0,'',$conf->entity);

					dolibarr_set_const($this->db, "ULTIMATE_TEXTCOLOR_COLOR", $textcolor,'chaine',0,'',$conf>entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_FOOTERTEXTCOLOR_COLOR", $footertextcolor,'chaine',0,'',$conf>entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_QRCODECOLOR_COLOR", $qrcodecolor,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH", $widthnumbering,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_DESC_WIDTH", $widthdesc,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH", $widthvat,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_UP_WIDTH", $widthup,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH", $widthqty,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_UNIT_WIDTH", $widthunit,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH", $widthdiscount,'chaine',0,'',$conf->entity);

					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_REF", $withref,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_REF_WIDTH", $widthref,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT", $withoutvat,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS", $showdetails,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO_FILE", $this->dao->options['otherlogo_file'],'chaine',0,'',$conf->entity);
					
					if ($_FILES ["otherlogo"] ["tmp_name"]) 
					{
						if (preg_match ( '/([^\\/:]+)$/i', $_FILES ["otherlogo"] ["name"], $reg )) 
						{
							$otherlogo = $reg [1];
							
							$isimage = image_format_supported ( $otherlogo );
							if ($isimage >= 0) 
							{
								dol_syslog ( "Move file " . $_FILES ["otherlogo"] ["tmp_name"] . " to " . $conf->ultimatepdf->dir_output . '/otherlogo/' . $otherlogo );
								if (! is_dir ( $conf->ultimatepdf->dir_output . '/otherlogo/' )) 
								{
									dol_mkdir ( $conf->ultimatepdf->dir_output . '/otherlogo/' );
								}
								$result = dol_move_uploaded_file ( $_FILES ["otherlogo"] ["tmp_name"], $conf->ultimatepdf->dir_output . '/otherlogo/' . $otherlogo, 1, 0, $_FILES ['otherlogo'] ['error'] );
								if ($result > 0) 
								{
									dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO", $otherlogo,'chaine',0,'',$conf->entity);
									$this->dao->options['otherlogo'] = $otherlogo;
									
									$ret = $this->dao->update($id,$user);

									if ($ret <= 0)
									{
										$error++;
										$errors=($this->dao->error ? array($this->dao->error) : $this->dao->errors);
										$action = 'edit';
									}
									
								} 
								else if (preg_match ( '/^ErrorFileIsInfectedWithAVirus/', $result )) 
								{
									$langs->load ( "errors" );
									$tmparray = explode ( ':', $result );
									setEventMessage ( $langs->trans ( 'ErrorFileIsInfectedWithAVirus', $tmparray [1] ), 'errors' );
									$error ++;
								} 
								else 
								{
									setEventMessage ( $langs->trans ( "ErrorFailedToSaveFile" ), 'errors' );
									$error ++;
								}
							} 
							else 
							{
								setEventMessage ( $langs->trans ( "ErrorOnlyPngJpgSupported" ), 'errors' );
								$error ++;
							}
						}
					}
					
					dolibarr_set_const($this->db, "MAIN_PDF_FORCE_FONT", $otherfont,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "MAIN_PDF_FREETEXT_HEIGHT", $heightforfreetext,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATEPDF_FREETEXT_FONT_SIZE", $freetextfontsize,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "MAIN_USE_BACKGROUND_ON_PDF", $usebackground,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "MAIN_DOCUMENTS_WITH_PICTURE_WIDTH", $imglinesize,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_LOGO_HEIGHT", $logoheight,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_LOGO_WIDTH", $logowidth,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO_HEIGHT", $otherlogoheight,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO_WIDTH", $otherlogowidth,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_INVERT_SENDER_RECIPIENT", $invertSenderRecipient,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_WIDTH_RECBOX", $widthrecbox,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_LEFT", $marge_gauche,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_RIGHT", $marge_droite,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_TOP", $marge_haute,'chaine',0,'',$conf->entity);
					
					dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_BOTTOM", $marge_basse,'chaine',0,'',$conf->entity);

					$this->db->commit();

				}
				else
				{
					$this->db->rollback();
				}
			}
		}
			
		if ($action == 'removeotherlogo') 
		{			
			$ret = $this->dao->fetch($id);
			if ($ret < 0)
			{
				$error++;
				array_push($this->errors, $langs->trans("ErrorDesignIsNotValid"));
				$action = '';
			}
			
			if (! $error && $ret > 0) 
			{
				require_once (DOL_DOCUMENT_ROOT . "/core/lib/files.lib.php");
				
				$this->dao->options['otherlogo_file']=null;
				$this->dao->options['otherlogo'] = null;											
				$ret = $this->dao->update($id,$user);

				if ($ret <= 0)
				{
					$error++;
					$errors=($this->dao->error ? array($this->dao->error) : $this->dao->errors);
					$action = 'edit';
				}
			}
			
			if (! $error && $ret > 0) 
			{
			
				$logofile = $conf->ultimatepdf->dir_output . '/otherlogo/' . $conf->global->ULTIMATE_OTHERLOGO;
				
				dol_delete_file ( $logofile );
				dolibarr_del_const ( $this->db, "ULTIMATE_OTHERLOGO", $conf->entity );
				dolibarr_del_const ( $this->db, "ULTIMATE_OTHERLOGO_FILE", $conf->entity );
			}
		}

		if ($action == 'confirm_delete' && GETPOST('confirm') == 'yes' && $user->admin)
		{
			$error=0;

			if ($id == 1)
			{
				$error++;
				array_push($this->errors, $langs->trans("ErrorNotDeleteMasterDesign") );
				$action = '';
			}

			if (! $error)
			{
				if ($this->dao->fetch($id) > 0)
				{
					if ($this->dao->delete($id) > 0)
					{
						$this->mesg=$langs->trans('ConfirmedDesignDeleted');
					}
					else
					{
						$this->error=$this->dao->error;
						$action = '';
					}
				}
			}
		}

		if ($action == 'setactive' && $user->admin)
		{
			$this->dao->setDesign($id,'active',$value);
		}			
	}

	/**
	 *	Return combo list of designs.
	 *
	 *	@param	int		$selected	Preselected design
	 *	@param	int		$htmlname	Name
	 *	@param	string	$option		Option
	 *	@param	int		$login		If use in login page or not
	 *	@return	string
	 */
	function select_designs($selected='', $htmlname='design', $option='', $login=0)
	{
		global $user, $langs;
		
		$this->getInstanceDao();

		$this->dao->getDesigns($login);

		$return = '<select class="flat" id="'.$htmlname.'" name="'.$htmlname.'"'.$option.'>';
		if (is_array($this->dao->designs))
		{
			foreach ($this->dao->designs as $design)
			{
				if ($design->active == 1)
				{
					$return.= '<option value="'.$design->id.'" ';
					if ($selected == $design->id)	$return.= 'selected="selected"';
					$return.= '>';
					$return.= $design->label;
					$return.= '</option>';
				}
			}
		}
		$return.= '</select>';

		return $return;
	}

	/**
	 *    Switch to another design.
	 *    @param	id		Id of the destination design
	 */
	function switchDesign($id)
	{
		global $conf,$user;

		$this->getInstanceDao();

		if ($this->dao->fetch($id) > 0)
		{
			// Controle des droits sur le changement
			if($this->dao->verifyRight($id,$user->id) || $user->admin || $user->rights->ultimatepdf->ultimatepdf_design->write)
			{
				dolibarr_set_const($this->db, "ULTIMATE_DESIGN", $id,'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DASH_DOTTED", $this->dao->options['dashdotted'],'chaine',0,'',$conf->entity);

				dolibarr_set_const($this->db, "ULTIMATE_BGCOLOR_COLOR", $this->dao->options['bgcolor'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_BGCOLOR_OPACITY", $this->dao->options['opacity'],'chaine',0,'',$conf->entity);

				dolibarr_set_const($this->db, "ULTIMATE_BORDERCOLOR_COLOR", $this->dao->options['bordercolor'],'chaine',0,'',$conf->entity);

				dolibarr_set_const($this->db, "ULTIMATE_TEXTCOLOR_COLOR", $this->dao->options['textcolor'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_FOOTERTEXTCOLOR_COLOR", $this->dao->options['footertextcolor'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_QRCODECOLOR_COLOR", $this->dao->options['qrcodecolor'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH", $this->dao->options['widthnumbering'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_DESC_WIDTH", $this->dao->options['widthdesc'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_TVA_WIDTH", $this->dao->options['widthvat'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_UP_WIDTH", $this->dao->options['widthup'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_QTY_WIDTH", $this->dao->options['widthqty'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_UNIT_WIDTH", $this->dao->options['widthunit'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_DISCOUNT_WIDTH", $this->dao->options['widthdiscount'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_REF", $this->dao->options['withref'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_DOCUMENTS_WITH_REF_WIDTH", $this->dao->options['widthref'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT", $this->dao->options['withoutvat'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "MAIN_GENERATE_DOCUMENTS_SHOW_FOOT_DETAILS", $this->dao->options['showdetails'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO", $this->dao->options['otherlogo'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO_FILE", $this->dao->options['otherlogo_file'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "MAIN_PDF_FORCE_FONT", $this->dao->options['otherfont'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "MAIN_PDF_FREETEXT_HEIGHT", $this->dao->options['heightforfreetext'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATEPDF_FREETEXT_FONT_SIZE", $this->dao->options['freetextfontsize'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "MAIN_USE_BACKGROUND_ON_PDF", $this->dao->options['usebackground'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "MAIN_DOCUMENTS_WITH_PICTURE_WIDTH", $this->dao->options['imglinesize'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_LOGO_HEIGHT", $this->dao->options['logoheight'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_LOGO_WIDTH", $this->dao->options['logowidth'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO_HEIGHT", $this->dao->options['otherlogoheight'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_OTHERLOGO_WIDTH", $this->dao->options['otherlogowidth'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_INVERT_SENDER_RECIPIENT", $this->dao->options['invertSenderRecipient'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_LEFT", $this->dao->options['marge_gauche'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_RIGHT", $this->dao->options['marge_droite'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_TOP", $this->dao->options['marge_haute'],'chaine',0,'',$conf->entity);
				
				dolibarr_set_const($this->db, "ULTIMATE_PDF_MARGIN_BOTTOM", $this->dao->options['marge_basse'],'chaine',0,'',$conf->entity);

				return 1;
			}
			else
			{
				return -2;
			}
		}
		else
		{
			return -1;
		}
	}

	/**
	 * 	Get design info
	 * 	@param	id	Object id
	 */
	function getInfo($id)
	{
		$this->getInstanceDao();
		$this->dao->fetch($id);

		$this->label		= $this->dao->label;
		$this->description	= $this->dao->description;
	}

	/**
	 * 	Get action title
	 * 	@param	action	Type of action
	 */
	function getTitle($action='')
	{
		global $langs;

		if ($action == 'create') return $langs->trans("AddDesign");
		else if ($action == 'edit') return $langs->trans("EditDesign");
		else return $langs->trans("DesignsManagement");
	}

	/**
	 *    Assigne les valeurs pour les templates
	 *    @param      action     Type of action
	 */
	function assign_values(&$action = 'view')
	{
		global $conf,$langs,$user;
		global $form,$formother,$formadmin;

		$this->getInstanceDao();

		$this->template_dir = dol_buildpath('/ultimatepdf/tpl/');

		if ($action == 'create')
		{
			$this->template = 'ultimatepdf_create.tpl.php';
		}
		else if ($action == 'edit')
		{
			$this->template = 'ultimatepdf_edit.tpl.php';

			if (!empty($id)) $ret = $this->dao->fetch($id);
		}

		if ($action == 'create' || $action == 'edit')
		{
			// Label
			$this->tpl['label'] = ($label?$label:$this->dao->label);

			// Description
			$this->tpl['description'] = ($description?$description:$this->dao->description);

			// Dash dotted
			$ddvalue=array('0' => $langs->trans('ContinuousLine'), '8, 2' => $langs->trans('DottedLine'));
			$this->tpl['select_dashdotted'] = $form->selectarray('dashdotted',$ddvalue,($dashdotted?$dashdotted:$this->dao->options['dashdotted']));

			// Bgcolor
			$this->tpl['select_bgcolor'] = $formother->selectColor(($bgcolor?$bgcolor:$this->dao->options['bgcolor']), 'bgcolor', '', 1);
			
			// Bgcolor opacity
			$this->tpl['select_opacity'] = ($opacity?$opacity:$this->dao->options['opacity']);

			// Bordercolor
			$this->tpl['select_bordercolor'] = $formother->selectColor(($bordercolor?$bordercolor:$this->dao->options['bordercolor']), 'bordercolor', '', 1);

			// Textcolor
			$this->tpl['select_textcolor'] = $formother->selectColor(($textcolor?$textcolor:$this->dao->options['textcolor']), 'textcolor', '', 1);
			
			// FooterTextcolor
			$this->tpl['select_footertextcolor'] = $formother->selectColor(($footertextcolor?$footertextcolor:$this->dao->options['footertextcolor']), 'footertextcolor', '', 1);
			
			// QRcodecolor
			$this->tpl['select_qrcodecolor'] = $formother->selectColor(($qrcodecolor?$qrcodecolor:$this->dao->options['qrcodecolor']), 'qrcodecolor', '', 1);
			
			// widthnumbering		
			$this->tpl['widthnumbering'] = ($widthnumbering?$widthnumbering:$this->dao->options['widthnumbering']);
			
			// widthdesc		
			$this->tpl['widthdesc'] = ($widthdesc?$widthdesc:$this->dao->options['widthdesc']);
			
			// widthvat		
			$this->tpl['widthvat'] = ($widthvat?$widthvat:$this->dao->options['widthvat']);
			
			// widthup		
			$this->tpl['widthup'] = ($widthup?$widthup:$this->dao->options['widthup']);
			
			// widthqty		
			$this->tpl['widthqty'] = ($widthqty?$widthqty:$this->dao->options['widthqty']);
			
			// widthunit	
			$this->tpl['widthunit'] = ($widthunit?$widthunit:$this->dao->options['widthunit']);
			
			// widthdiscount		
			$this->tpl['widthdiscount'] = ($widthdiscount?$widthdiscount:$this->dao->options['widthdiscount']);
			
			// withref		
			$this->tpl['select_withref'] = $form->selectyesno('withref',($withref?$withref:$this->dao->options['withref']),0,false);
			
			// Ref width	
			$this->tpl['select_widthref'] = ($widthref?$widthref:$this->dao->options['widthref']);
			
			// withoutvat		
			$this->tpl['select_withoutvat'] = $form->selectyesno('withoutvat',($withoutvat?$withoutvat:$this->dao->options['withoutvat']),0,false);
			
			// showdetails
			$arraydetailsforpdffoot = array(
				0 => $langs->trans('NoDetails'),
				1 => $langs->trans('DisplayCompanyInfo'),
				2 => $langs->trans('DisplayManagersInfo'),
				3 => $langs->trans('DisplayCompanyInfoAndManagers')
			);			
			$this->tpl['select_showdetails'] = $form->selectarray('showdetails', $arraydetailsforpdffoot, ($showdetails?$showdetails:$this->dao->options['showdetails']));

			// Otherlogo			
			if (! empty($conf->global->ULTIMATE_OTHERLOGO))
			{
				$other_file=urlencode('/otherlogo/'.$conf->global->ULTIMATE_OTHERLOGO);
				$otherlogo = DOL_URL_ROOT . '/viewimage.php?modulepart=ultimatepdf&amp;file='.$other_file ;
				
			}		
			$this->tpl['select_otherlogo'] = ($otherlogo?$otherlogo:$this->dao->options['otherlogo']);
			$this->tpl['select_otherlogo_file'] = $other_file;

			// Other font
			$fontvalue=array('Helvetica' => 'Helvetica', 'DejaVuSans' => 'DejaVuSans', 'FreeMono' => 'FreeMono');
			$this->tpl['select_otherfont'] = $form->selectarray('otherfont',$fontvalue,($otherfont?$otherfont:$this->dao->options['otherfont']));
			
			// heightforfreetext
			$this->tpl['select_heightforfreetext'] = ($heightforfreetext?$heightforfreetext:$this->dao->options['heightforfreetext']);
			
			// freetextfontsize
			$this->tpl['select_freetextfontsize'] = ($freetextfontsize?$freetextfontsize:$this->dao->options['freetextfontsize']);
			
			// Use background on pdf
			$this->tpl['usebackground'] = ($usebackground?$usebackground:$this->dao->options['usebackground']);
			
			// Set image width
			$this->tpl['imglinesize'] = ($imglinesize?$imglinesize:$this->dao->options['imglinesize']);
			
			// Set logo height
			$this->tpl['logoheight'] = ($logoheight?$logoheight:$this->dao->options['logoheight']);
			
			// Set logo width
			$this->tpl['logowidth'] = ($logowidth?$logowidth:$this->dao->options['logowidth']);
			
			// Set otherlogo height
			$this->tpl['otherlogoheight'] = ($otherlogoheight?$otherlogoheight:$this->dao->options['otherlogoheight']);
			
			// Set otherlogo width
			$this->tpl['otherlogowidth'] = ($otherlogowidth?$otherlogowidth:$this->dao->options['otherlogowidth']);
			
			// Invert sender and recipient
			$this->tpl['invertSenderRecipient'] = $form->selectyesno('invertSenderRecipient',($invertSenderRecipient?$invertSenderRecipient:$this->dao->options['invertSenderRecipient']),0,false);
			
			// Set widthrecbox
			$this->tpl['widthrecbox'] = ($widthrecbox?$widthrecbox:$this->dao->options['widthrecbox']);
			
			// Set marge_gauche
			$this->tpl['marge_gauche'] = ($marge_gauche?$marge_gauche:$this->dao->options['marge_gauche']);
			
			// Set marge_droite
			$this->tpl['marge_droite'] = ($marge_droite?$marge_droite:$this->dao->options['marge_droite']);
			
			// Set marge_haute
			$this->tpl['marge_haute'] = ($marge_haute?$marge_haute:$this->dao->options['marge_haute']);
			
			// Set marge_basse
			$this->tpl['marge_basse'] = ($marge_basse?$marge_basse:$this->dao->options['marge_basse']);
			
		}
		else
		{

			$this->dao->getDesigns();

			$this->tpl['designs']		= $this->dao->designs;
			$this->tpl['img_on'] 		= img_picto($langs->trans("Activated"),'on');
			$this->tpl['img_off'] 		= img_picto($langs->trans("Disabled"),'off');
			$this->tpl['img_modify'] 	= img_edit();
			$this->tpl['img_delete'] 	= img_delete();

			// Confirm delete
			if ($_GET["action"] == 'delete')
			{
				$this->tpl['action_delete'] = $form->formconfirm($_SERVER["PHP_SELF"]."?id=".GETPOST('id'),$langs->trans("DeleteDesign"),$langs->trans("ConfirmDeleteDesign"),"confirm_delete",'',0,1);
			}

			$this->template = 'ultimatepdf_view.tpl.php';
		}
	}

	/**
	 *    Display the template
	 */
	function display()
	{
		global $conf, $langs;
		global $bc;

		include($this->template_dir.$this->template);
	}	

	/**
	 *
	 */
	function printTopRightMenu()
	{
		return $this->getTopRightMenu();
	}

	/**
	 * 	Show design info
	 */
	private function getTopRightMenu()
	{
		global $conf,$user,$langs;

		$langs->load('ultimatepdf@ultimatepdf');

		$out='';
		$form=new Form($this->db);
		$this->getInfo($conf->design);
		$this->getInstanceDao();
		$this->dao->getDesigns($login);
		if (is_array($this->dao->designs))
		{
			$htmltext ='<u>'.$langs->trans("Design").'</u>'."\n";
			foreach ($this->dao->designs as $design)
			{
				if ($design->active == 1)
				{
					if ($conf->global->ULTIMATE_DESIGN == $design->id)	
					{
						$htmltext.='<br><b>'.$langs->trans("Label").'</b>: '.$design->label."\n";
						$htmltext.='<br><b>'.$langs->trans("Description").'</b>: '.$design->description."\n";
					}
				}
			}
		}
		
		$text = img_picto('', 'object_ultimatepdf.png@ultimatepdf','id="switchdesign" class="design linkobject"');
		
		$out.= $form->textwithtooltip('',$htmltext,2,1,$text,'login_block_elem',2);
		
		$out.= '<script type="text/javascript">
			$( "#switchdesign" ).click(function() {
				$( "#dialog-switchdesign" ).dialog({
					modal: true,
					width: 400,
					buttons: {
						\''.$langs->trans('Ok').'\': function() {
							choice=\'ok\';
							$.get( "'.dol_buildpath('/ultimatepdf/ajaxswitchdesign.php',1).'", {
								action: \'switchdesign\',
								design: $( "#design" ).val()
							},
							function(content) {
								$( "#dialog-switchdesign" ).dialog( "close" );
							});
						},
						\''.$langs->trans('Cancel').'\': function() {
							choice=\'ko\';
							$(this).dialog( "close" );
						}
					},
					close: function(event, ui) {
						if (choice == \'ok\') {
							location.href=\''.DOL_URL_ROOT.'\';
						}
					}
				});
			});
			</script>';
		
		$out.= '<div id="dialog-switchdesign" class="hideobject" title="'.$langs->trans('SwitchToAnotherDesign').'">'."\n";
		$out.= '<br>'.$langs->trans('SelectADesign').': ';
		$out.= ajax_combobox('design');
		$out.= $this->select_designs($conf->global->ULTIMATE_DESIGN)."\n";
		$out.= '</div>'."\n";

		$this->resprints = $out;
		return 1;
	}
	
	/**
	 * formObjectOptions Method Hook Call
	 *
	 * @param array $parameters parameters
	 * @param Object	&$object			Object to use hooks on
	 * @param string	&$action			Action code on calling page ('create', 'edit', 'view', 'add', 'update', 'delete'...)
	 * @param object $hookmanager class instance
	 * @return void
	 */
	function formObjectOptions($parameters, &$object, &$action) {

		global $langs, $conf, $user, $hookmanager;
		
		$langs->load('ultimatepdf@ultimatepdf');
		
		dol_syslog(__METHOD__, LOG_DEBUG);
		
		// Add javascript Jquery to add button Select doc form
		if ($object->table_element == 'propal' && ! empty($object->id) && ! empty($conf->global->ULTIMATEPDF_GENERATE_PROPOSALS_WITH_MERGED_PDF)) {
			
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			dol_include_once ( '/ultimatepdf/class/propalmergedpdf.class.php' );
			
			$filetomerge = new Propalmergedpdf ( $this->db );
			$result = $filetomerge->fetch_by_propal ( $object->id );			
			
			if (! empty ( $conf->propal->enabled ))
				$upload_dir = $conf->propal->dir_output . '/' . dol_sanitizeFileName ( $object->ref );
			
			$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1 );

			// For each file build select list with PDF extention
			if (count ( $filearray ) > 0) {
				$html = '<BR><BR>';
				// Actual file to merge is :
				if (count($filetomerge->lines)>0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'PropalMergePdfPropalActualFile' );
					$html .= '</div>';
				}				
				
				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/comm/propal/card.php?id=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';			
							
				if (count($filetomerge->lines)==0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'PropalMergePdfPropalChooseFile' );
					$html .= '</div>';
				}
				
				$html .= '<table class=\"noborder\" width=\"100%\">';
				$html .= '<tbody>';			
				$html .= '<tr class=\"liste_titre\">';
				$html .= '<th>'. $langs->trans ( 'Documents' ) .'';
				$html .= '</th></tr>';
				$html .= '</tbody>';
				$style='impair';
				$hasfile=false;
				foreach ( $filearray as $filetoadd ) {

					if (($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') && ($filename = pathinfo ( $filetoadd ['name'], PATHINFO_FILENAME )!=$object->ref)) {
				
						if ($style=='pair') {
							$style='impair';
						}
						else {
							$style='pair';
						}
						
						$checked = '';
						$filename=$filetoadd ['name'];
						
						if (array_key_exists($filetoadd ['name'],$filetomerge->lines)) {
							$checked =' checked=\"checked\" ';
						}
						
						$hasfile=true;
						$icon='<img border=\"0\" title=\"Fichier: '.$filename.'\" alt=\"Fichier: '.$filename.'\" src=\"'. DOL_URL_ROOT .'/theme/common/mime/pdf.png\">';
						$html .= '<tr class=\"'.$style.'\"><td class=\"nowrap\" style=\"font-weight:bold\">';
						
						$html .= '<input type=\"checkbox\" '.$checked.' name=\"filetoadd[]\" id=\"filetoadd\" value=\"'.$filetoadd ['name'].'\"> '.$icon.' '.$filename.'</input>';
						$html .= '</td></tr>';
					}								
				}
				
				if (!$hasfile) {
					$html .= '<tr><td>';
					$warning='<img border=\"0\" src=\"'. DOL_URL_ROOT .'/theme/eldy/img/warning.png\">';
					$html .= $warning.' '.$langs->trans ( 'GotoDocumentsTab' );
					$html .= '</td></tr>';
				}
				
				if ($hasfile) {
					$html .= '<tr><td>';			
					$html .= '<input type=\"submit\" class=\"button\" name=\"save\" value=\"' . $langs->trans ( 'Save' ) . '\">';
					$html .= '<br><br>';
					$html .= '</td></tr>';
				}
				
				$html .= '</table>';					
				$html .= '</form>';
				$html .= '</div>';				
				$html .= '</div>';
				
				if ($conf->use_javascript_ajax)
				{
					print "\n".'<script type="text/javascript" language="javascript">';
					print 'jQuery(document).ready(function () {
					jQuery(function() {jQuery(".fiche").append("' . $html . '");});
					})';
					print '</script>'."\n";
				}
			}
		}
		elseif ($object->table_element == 'facture' && ! empty ( $object->id ) && ! empty($conf->global->ULTIMATEPDF_GENERATE_INVOICES_WITH_MERGED_PDF)) 
		{
			
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			dol_include_once ( '/ultimatepdf/class/invoicemergedpdf.class.php' );
			
			$filetomerge = new Invoicemergedpdf ( $this->db );
			$result = $filetomerge->fetch_by_invoice ( $object->id );			
			
			if (! empty ( $conf->facture->enabled ))
				$upload_dir = $conf->facture->dir_output . '/' . dol_sanitizeFileName ( $object->ref );
			
			$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1 );

			// For each file build select list with PDF extention
			if (count ( $filearray ) > 0) {
				$html = '<BR><BR>';
				// Actual file to merge is :
				if (count($filetomerge->lines)>0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'InvoiceMergePdfInvoiceActualFile' );
					$html .= '</div>';
				}				
				
				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/compta/facture.php?facid=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';			
							
				if (count($filetomerge->lines)==0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'InvoiceMergePdfInvoiceChooseFile' );
					$html .= '</div>';
				}
				
				$html .= '<table class=\"noborder\" width=\"100%\">';
				$html .= '<tbody>';			
				$html .= '<tr class=\"liste_titre\">';
				$html .= '<th>'. $langs->trans ( 'Documents' ) .'';
				$html .= '</th></tr>';
				$html .= '</tbody>';
				$style='impair';
				$hasfile=false;
				foreach ( $filearray as $filetoadd ) {

					if (($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') && ($filename = pathinfo ( $filetoadd ['name'], PATHINFO_FILENAME )!=$object->ref)) {
				
						if ($style=='pair') {
							$style='impair';
						}
						else {
							$style='pair';
						}
						
						$checked = '';
						$filename=$filetoadd ['name'];
						
						if (array_key_exists($filetoadd ['name'],$filetomerge->lines)) {
							$checked =' checked=\"checked\" ';
						}
						
						$hasfile=true;
						$icon='<img border=\"0\" title=\"Fichier: '.$filename.'\" alt=\"Fichier: '.$filename.'\" src=\"'. DOL_URL_ROOT .'/theme/common/mime/pdf.png\">';
						$html .= '<tr class=\"'.$style.'\"><td class=\"nowrap\" style=\"font-weight:bold\">';
						
						$html .= '<input type=\"checkbox\" '.$checked.' name=\"filetoadd[]\" id=\"filetoadd\" value=\"'.$filetoadd ['name'].'\"> '.$icon.' '.$filename.'</input>';
						$html .= '</td></tr>';
					}								
				}
				
				if (!$hasfile) {
					$html .= '<tr><td>';
					$warning='<img border=\"0\" src=\"'. DOL_URL_ROOT .'/theme/eldy/img/warning.png\">';
					$html .= $warning.' '.$langs->trans ( 'GotoDocumentsTab' );
					$html .= '</td></tr>';
				}
				
				if ($hasfile) {
					$html .= '<tr><td>';			
					$html .= '<input type=\"submit\" class=\"button\" name=\"save\" value=\"' . $langs->trans ( 'Save' ) . '\">';
					$html .= '<br><br>';
					$html .= '</td></tr>';
				}
				
				$html .= '</table>';					
				$html .= '</form>';
				$html .= '</div>';				
				$html .= '</div>';
				
				if ($conf->use_javascript_ajax)
				{
					print "\n".'<script type="text/javascript" language="javascript">';
					print 'jQuery(document).ready(function () {
					jQuery(function() {jQuery(".fiche").append("' . $html . '");});
					})';
					print '</script>'."\n";
				}
			}
		}
		elseif ($object->table_element == 'commande' && ! empty ( $object->id ) && ! empty($conf->global->ULTIMATEPDF_GENERATE_ORDERS_WITH_MERGED_PDF))  
		{
			
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			dol_include_once ( '/ultimatepdf/class/ordermergedpdf.class.php' );
			
			$filetomerge = new Ordermergedpdf ( $this->db );
			$result = $filetomerge->fetch_by_order ( $object->id );			
			
			if (! empty ( $conf->commande->enabled ))
				$upload_dir = $conf->commande->dir_output . '/' . dol_sanitizeFileName ( $object->ref );
			
			$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1 );

			// For each file build select list with PDF extention
			if (count ( $filearray ) > 0) {
				$html = '<BR><BR>';
				// Actual file to merge is :
				if (count($filetomerge->lines)>0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'OrderMergePdfOrderActualFile' );
					$html .= '</div>';
				}				
				
				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/commande/card.php?id=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';						
				
				if (count($filetomerge->lines)==0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'OrderMergePdfOrderChooseFile' );
					$html .= '</div>';
				}
				
				$html .= '<table class=\"noborder\" width=\"100%\">';
				$html .= '<tbody>';			
				$html .= '<tr class=\"liste_titre\">';
				$html .= '<th>'. $langs->trans ( 'Documents' ) .'';
				$html .= '</th></tr>';
				$html .= '</tbody>';
				$style='impair';
				$hasfile=false;
				foreach ( $filearray as $filetoadd ) {
				
					if (($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') && ($filename = pathinfo ( $filetoadd ['name'], PATHINFO_FILENAME )!=$object->ref)) {
				
						if ($style=='pair') {
							$style='impair';
						}
						else {
							$style='pair';
						}
						
						$checked = '';
						$filename=$filetoadd ['name'];
						
						if (array_key_exists($filetoadd ['name'],$filetomerge->lines)) {
							$checked =' checked=\"checked\" ';
						}
						
						$hasfile=true;
						$icon='<img border=\"0\" title=\"Fichier: '.$filename.'\" alt=\"Fichier: '.$filename.'\" src=\"'. DOL_URL_ROOT .'/theme/common/mime/pdf.png\">';
						$html .= '<tr class=\"'.$style.'\"><td class=\"nowrap\" style=\"font-weight:bold\">';
						
						$html .= '<input type=\"checkbox\" '.$checked.' name=\"filetoadd[]\" id=\"filetoadd\" value=\"'.$filetoadd ['name'].'\"> '.$icon.' '.$filename.'</input>';
						$html .= '</td></tr>';
					}								
				}
				
				if (!$hasfile) {
					$html .= '<tr><td>';
					$warning='<img border=\"0\" src=\"'. DOL_URL_ROOT .'/theme/eldy/img/warning.png\">';
					$html .= $warning.' '.$langs->trans ( 'GotoDocumentsTab' );
					$html .= '</td></tr>';
				}
				
				if ($hasfile) {
					$html .= '<tr><td>';			
					$html .= '<input type=\"submit\" class=\"button\" name=\"save\" value=\"' . $langs->trans ( 'Save' ) . '\">';
					$html .= '<br><br>';
					$html .= '</td></tr>';
				}
				
				$html .= '</table>';					
				$html .= '</form>';
				$html .= '</div>';				
				$html .= '</div>';
				
				if ($conf->use_javascript_ajax)
				{
					print "\n".'<script type="text/javascript" language="javascript">';
					print 'jQuery(document).ready(function () {
					jQuery(function() {jQuery(".fiche").append("' . $html . '");});
					})';
					print '</script>'."\n";
				}
			}
		}
		elseif ($object->table_element == 'commande_fournisseur' && ! empty ( $object->id ) && ! empty($conf->global->ULTIMATEPDF_GENERATE_SUPPLIERORDERS_WITH_MERGED_PDF))  
		{
			
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			dol_include_once ( '/ultimatepdf/class/supplierordermergedpdf.class.php' );
			
			$filetomerge = new Supplierordermergedpdf ( $this->db );
			$result = $filetomerge->fetch_by_supplierorder ( $object->id );			
			
			if (! empty ( $conf->fournisseur->enabled ))
				$upload_dir = $conf->fournisseur->dir_output.'/commande/'.dol_sanitizeFileName($object->ref);
			
			$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1 );
			
			// For each file build select list with PDF extention
			if (count ( $filearray ) > 0) {
				$html = '<BR><BR>';
				// Actual file to merge is :
				if (count($filetomerge->lines)>0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'OrderMergePdfOrderActualFile' );
					$html .= '</div>';
				}				
				
				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/fourn/commande/card.php?id=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';						
				
				if (count($filetomerge->lines)==0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'OrderMergePdfOrderChooseFile' );
					$html .= '</div>';
				}
				
				$html .= '<table class=\"noborder\" width=\"100%\">';
				$html .= '<tbody>';			
				$html .= '<tr class=\"liste_titre\">';
				$html .= '<th>'. $langs->trans ( 'Documents' ) .'';
				$html .= '</th></tr>';
				$html .= '</tbody>';
				$style='impair';
				$hasfile=false;
				foreach ( $filearray as $filetoadd ) {
				
					if (($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') && ($filename = pathinfo ( $filetoadd ['name'], PATHINFO_FILENAME )!=$object->ref)) {
				
						if ($style=='pair') {
							$style='impair';
						}
						else {
							$style='pair';
						}
						
						$checked = '';
						$filename=$filetoadd ['name'];
						
						if (array_key_exists($filetoadd ['name'],$filetomerge->lines)) {
							$checked =' checked=\"checked\" ';
						}
						
						$hasfile=true;
						$icon='<img border=\"0\" title=\"Fichier: '.$filename.'\" alt=\"Fichier: '.$filename.'\" src=\"'. DOL_URL_ROOT .'/theme/common/mime/pdf.png\">';
						$html .= '<tr class=\"'.$style.'\"><td class=\"nowrap\" style=\"font-weight:bold\">';
						
						$html .= '<input type=\"checkbox\" '.$checked.' name=\"filetoadd[]\" id=\"filetoadd\" value=\"'.$filetoadd ['name'].'\"> '.$icon.' '.$filename.'</input>';
						$html .= '</td></tr>';
					}								
				}
				
				if (!$hasfile) {
					$html .= '<tr><td>';
					$warning='<img border=\"0\" src=\"'. DOL_URL_ROOT .'/theme/eldy/img/warning.png\">';
					$html .= $warning.' '.$langs->trans ( 'GotoDocumentsTab' );
					$html .= '</td></tr>';
				}
				
				if ($hasfile) {
					$html .= '<tr><td>';			
					$html .= '<input type=\"submit\" class=\"button\" name=\"save\" value=\"' . $langs->trans ( 'Save' ) . '\">';
					$html .= '<br><br>';
					$html .= '</td></tr>';
				}
				
				$html .= '</table>';					
				$html .= '</form>';
				$html .= '</div>';				
				$html .= '</div>';
				
				if ($conf->use_javascript_ajax)
				{
					print "\n".'<script type="text/javascript" language="javascript">';
					print 'jQuery(document).ready(function () {
					jQuery(function() {jQuery(".fiche").append("' . $html . '");});
					})';
					print '</script>'."\n";
				}
			}
		}
		elseif ($object->table_element == 'supplier_proposal' && ! empty ( $object->id ) && ! empty($conf->global->ULTIMATEPDF_GENERATE_SUPPLIER_PROPOSAL_WITH_MERGED_PDF))  
		{
			
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			dol_include_once ( '/ultimatepdf/class/supplierproposalmergedpdf.class.php' );
			
			$filetomerge = new SupplierProposalmergedpdf ( $this->db );
			$result = $filetomerge->fetch_by_supplierproposal ( $object->id );			
			
			if (! empty ( $conf->supplier_proposal->enabled ))
				$upload_dir = $conf->supplier_proposal->dir_output . '/' . dol_sanitizeFileName ( $object->ref );
			
			$filearray = dol_dir_list ( $upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1 );
			
			// For each file build select list with PDF extention
			if (count ( $filearray ) > 0) {
				$html = '<BR><BR>';
				// Actual file to merge is :
				if (count($filetomerge->lines)>0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'OrderMergePdfOrderActualFile' );
					$html .= '</div>';
				}				
				
				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/supplier_proposal/card.php?id=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';	
									
				
				if (count($filetomerge->lines)==0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans ( 'OrderMergePdfOrderChooseFile' );
					$html .= '</div>';
				}
				
				$html .= '<table class=\"noborder\" width=\"100%\">';
				$html .= '<tbody>';			
				$html .= '<tr class=\"liste_titre\">';
				$html .= '<th>'. $langs->trans ( 'Documents' ) .'';
				$html .= '</th></tr>';
				$html .= '</tbody>';
				$style='impair';
				$hasfile=false;
				foreach ( $filearray as $filetoadd ) {
				
					if (($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') && ($filename = pathinfo ( $filetoadd ['name'], PATHINFO_FILENAME )!=$object->ref)) {
				
						if ($style=='pair') {
							$style='impair';
						}
						else {
							$style='pair';
						}
						
						$checked = '';
						$filename=$filetoadd ['name'];
						
						if (array_key_exists($filetoadd ['name'],$filetomerge->lines)) {
							$checked =' checked=\"checked\" ';
						}
						
						$hasfile=true;
						$icon='<img border=\"0\" title=\"Fichier: '.$filename.'\" alt=\"Fichier: '.$filename.'\" src=\"'. DOL_URL_ROOT .'/theme/common/mime/pdf.png\">';
						$html .= '<tr class=\"'.$style.'\"><td class=\"nowrap\" style=\"font-weight:bold\">';
						
						$html .= '<input type=\"checkbox\" '.$checked.' name=\"filetoadd[]\" id=\"filetoadd\" value=\"'.$filetoadd ['name'].'\"> '.$icon.' '.$filename.'</input>';
						$html .= '</td></tr>';
					}								
				}
				
				if (!$hasfile) {
					$html .= '<tr><td>';
					$warning='<img border=\"0\" src=\"'. DOL_URL_ROOT .'/theme/eldy/img/warning.png\">';
					$html .= $warning.' '.$langs->trans ( 'GotoDocumentsTab' );
					$html .= '</td></tr>';
				}
				
				if ($hasfile) {
					$html .= '<tr><td>';			
					$html .= '<input type=\"submit\" class=\"button\" name=\"save\" value=\"' . $langs->trans ( 'Save' ) . '\">';
					$html .= '<br><br>';
					$html .= '</td></tr>';
				}
				
				$html .= '</table>';					
				$html .= '</form>';
				$html .= '</div>';				
				$html .= '</div>';
				
				if ($conf->use_javascript_ajax)
				{
					print "\n".'<script type="text/javascript" language="javascript">';
					print 'jQuery(document).ready(function () {
					jQuery(function() {jQuery(".fiche").append("' . $html . '");});
					})';
					print '</script>'."\n";
				}
			}
		}
		elseif ($object->table_element == 'contrat' && ! empty($object->id) && ! empty($conf->global->ULTIMATEPDF_GENERATE_CONTRACTS_WITH_MERGED_PDF)) 
		{
			
			require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
			dol_include_once('/ultimatepdf/class/contractmergedpdf.class.php');
			
			$filetomerge = new Contractmergedpdf($this->db);
			$result = $filetomerge->fetch_by_contract($object->id);			
			
			if (! empty($conf->contrat->enabled))
				$upload_dir = $conf->contrat->dir_output . '/' . dol_sanitizeFileName($object->ref);
			
			$filearray = dol_dir_list($upload_dir, "files", 0, '', '\.meta$', 'name', SORT_ASC, 1);

			// For each file build select list with PDF extention
			if (count($filearray) > 0) {
				$html = '<BR><BR>';
				// Actual file to merge is :
				if (count($filetomerge->lines)>0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans('ContractMergePdfContractActualFile');
					$html .= '</div>';
				}				
				
				$html .= '<form name=\"filemerge\" action=\"' . DOL_URL_ROOT . '/contrat/card.php?id=' . $object->id . '\" method=\"post\">';
				$html .= '<input type=\"hidden\" name=\"token\" value=\"' . $_SESSION ['newtoken'] . '\">';
				$html .= '<input type=\"hidden\" name=\"action\" value=\"filemerge\">';			
				
				
				if (count($filetomerge->lines)==0) {
					$html .= '<div class=\"fichecenter\">';
					$html .= '<div class=\"fichehalfleft\">';
					$html .= '<div class=\"titre\">';
					$html .= '<br>';
					$html .= $langs->trans('ContractMergePdfContractChooseFile');
					$html .= '</div>';
				}
				
				$html .= '<table class=\"noborder\" width=\"100%\">';
				$html .= '<tbody>';			
				$html .= '<tr class=\"liste_titre\">';
				$html .= '<th>'. $langs->trans('Documents') .'';
				$html .= '</th></tr>';
				$html .= '</tbody>';
				$style='impair';
				$hasfile=false;
				foreach ($filearray as $filetoadd) {

					if (($ext = pathinfo ( $filetoadd ['name'], PATHINFO_EXTENSION ) == 'pdf') && ($filename = pathinfo ( $filetoadd ['name'], PATHINFO_FILENAME )!=$object->ref)) {
				
						if ($style=='pair') {
							$style='impair';
						}
						else {
							$style='pair';
						}
						
						$checked = '';
						$filename=$filetoadd ['name'];
						
						if (array_key_exists($filetoadd ['name'],$filetomerge->lines)) {
							$checked =' checked=\"checked\" ';
						}
						
						$hasfile=true;
						$icon='<img border=\"0\" title=\"Fichier: '.$filename.'\" alt=\"Fichier: '.$filename.'\" src=\"'. DOL_URL_ROOT .'/theme/common/mime/pdf.png\">';
						$html .= '<tr class=\"'.$style.'\"><td class=\"nowrap\" style=\"font-weight:bold\">';
						
						$html .= '<input type=\"checkbox\" '.$checked.' name=\"filetoadd[]\" id=\"filetoadd\" value=\"'.$filetoadd ['name'].'\"> '.$icon.' '.$filename.'</input>';
						$html .= '</td></tr>';
					}								
				}
				
				if (!$hasfile) {
					$html .= '<tr><td>';
					$warning='<img border=\"0\" src=\"'. DOL_URL_ROOT .'/theme/eldy/img/warning.png\">';
					$html .= $warning.' '.$langs->trans('GotoDocumentsTab');
					$html .= '</td></tr>';
				}
				
				if ($hasfile) {
					$html .= '<tr><td>';			
					$html .= '<input type=\"submit\" class=\"button\" name=\"save\" value=\"' .$langs->trans('Save'). '\">';
					$html .= '<br><br>';
					$html .= '</td></tr>';
				}
				
				$html .= '</table>';					
				$html .= '</form>';
				$html .= '</div>';				
				$html .= '</div>';
				
				if ($conf->use_javascript_ajax)
				{
					print "\n".'<script type="text/javascript" language="javascript">';
					print 'jQuery(document).ready(function () {
					jQuery(function() {jQuery(".fiche").append("' . $html . '");});
					})';
					print '</script>'."\n";
				}
			}
		}
	}
	
	 /**
     * Complete doc forms
     *
     * @param	array	$parameters		Array of parameters
     * @param	object	&$object		Object
     * @return	string					HTML content to add by hook
     */
    function formBuilddocOptions($parameters,&$object)
    {
        global $langs, $user, $conf;
		global $form;

        $langs->load("ultimatepdf@ultimatepdf");
        $form=new Form($this->db);

        $out='';

        $morefiles=array();

        if (($parameters['modulepart'] == 'invoice' || $parameters['modulepart'] == 'facture') && ($object->mode_reglement_code == 'VIR' || empty($object->mode_reglement_code)))
        {
       		$selectedbank=empty($object->fk_bank)?(isset($_POST['fk_bank'])?$_POST['fk_bank']:$conf->global->FACTURE_RIB_NUMBER):$object->fk_bank;

       		$statut='0';$filtre='';
       		$listofbankaccounts=array();
       		$sql = "SELECT rowid, label, bank";
       		$sql.= " FROM ".MAIN_DB_PREFIX."bank_account";
       		$sql.= " WHERE clos = '".$statut."'";
       		$sql.= " AND entity = ".$conf->entity;
       		if ($filtre) $sql.=" AND ".$filtre;
       		$sql.= " ORDER BY label";
       		dol_syslog(__METHOD__ .' sql='.$sql);
       		$result = $this->db->query($sql);
       		if ($result)
       		{
       			$num = $this->db->num_rows($result);
       			$i = 0;
       			if ($num)
       			{
       				while ($i < $num)
       				{
       					$obj = $this->db->fetch_object($result);
       					$listofbankaccounts[$obj->rowid]=$obj->label;
       					$i++;
       				}
       			}
       		}
			else dol_print_error($this->db);

        	$out.='<tr class="liste_titre">';
        	$out.='<td align="left" colspan="4" valign="top" class="formdoc">';
        	$out.=$langs->trans("BankAccount").' (pdf)';
       		$out.= $form->selectarray('fk_bank',$listofbankaccounts,$selectedbank,(count($listofbankaccounts)>1?1:0));
        }
        $out.='</td></tr>';

        $this->resprints = $out;
		return 1;
    }
	
	/**
	 * Return action of hook
	 *
	 * @param array $parameters
	 * @param object $object
	 * @param string $action
	 * @param object $hookmanager class instance
	 * @return void
	 */
	function afterPDFCreation($parameters = false, &$object, &$action = '', $hookmanager) {
		
	}

}
