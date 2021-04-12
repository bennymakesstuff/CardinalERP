<?php
/* Copyright (C) 2010-2017 Regis Houssin  <regis.houssin@capnetworks.com>
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
 *		\defgroup   ultimatepdf Module ultimatepdf
 *		\brief      Pdf Designs management
 *		\file       ultimatepdf/core/modules/modUltimatepdf.class.php
 *		\class    	ultimatepdf
 *		\brief      Description and activation class for module Ultimatepdf
 */

include_once(DOL_DOCUMENT_ROOT ."/core/modules/DolibarrModules.class.php");


/**
 *	\class      modUltimatepdf
 *	\brief      Description and activation class for module Ultimatepdf
 */
class modUltimatepdf extends DolibarrModules
{

	/**
	 *	Constructor. Define names, constants, directories, boxes, permissions
	 *
	 *	@param	DoliDB	$db		Database handler
	 */
	function __construct($db)
	{
		global $langs, $conf;

		$this->db = $db ;
		$this->numero = 300100 ;

		$this->family = "technic";
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = $langs->trans("Module300100Desc"); //"Pdf Models management";
		$this->descriptionlong = $langs->trans("Module300100DescLong");
		$this->editor_name = 'philippe.grand@atoo-net.com';
		$this->editor_url = 'https://atoo-net.com/';
		// Can be enabled / disabled only in the main company with superadmin account
		$this->core_enabled = 0;
		$this->version = '5.0.0';
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->special = 0;
		$this->picto='ultimatepdf@ultimatepdf';

		// Data directories to create when module is enabled
		$this->dirs = array("/ultimatepdf/otherlogo");

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = 'ultimatepdf.php@ultimatepdf';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array('models' => 1,
									'css' => array('/ultimatepdf/css/ultimatepdf.css.php'),
									'hooks' => array('propalcard','ordercard','invoicecard','contractcard','invoicesuppliercard','ordersuppliercard','interventioncard','toprightmenu','pdfgeneration','supplier_proposalcard'));

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(5,0,0);	// Minimum version of Dolibarr required by module
		//$this->need_dolibarr_version = array(3,9);	// Minimum version of Dolibarr required by module
		$this->langfiles = array('ultimatepdf@ultimatepdf');

		// Constants
		$this->const = array();			// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 0 or 'allentities')
		$r=0;

		$this->const[$r][0] = "MAIN_GENERATE_DOCUMENTS_HIDE_DETAILS";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Hide product details within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_GENERATE_DOCUMENTS_HIDE_DESC";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Hide product description within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_GENERATE_DOCUMENTS_HIDE_REF";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Hide reference within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_TVAINTRA_NOT_IN_ADDRESS";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Hide tva within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_SHOW_HIDE_PUHT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Hide puht within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_SHOW_HIDE_QTY";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Hide qty within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_SHOW_HIDE_THT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Hide tht within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_DISPLAY_FOLD_MARK";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Show by default fold mark within documents';
		$this->const[$r][4] = 1;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_GENERATE_PROPOSALS_WITH_PICTURE";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Show by default photos within proposals documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_GENERATE_ORDERS_WITH_PICTURE";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Show by default photos within orders documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_GENERATE_INVOICES_WITH_PICTURE";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Show by default photos within invoices documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_PDF_ADDALSOTARGETDETAILS";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add address details within documents';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_PDF_FORCE_FONT";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add choice of font';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_GENERATE_DOCUMENTS_WITHOUT_VAT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add choice of VAT or not';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_DASH_DOTTED";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add choice of VAT or not';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_BGCOLOR_COLOR";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add choice of background color';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_BORDERCOLOR_COLOR";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add choice of border color';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_TEXTCOLOR_COLOR";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add choice of text color';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_PDF_FREETEXT_HEIGHT";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add set of freetext height';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_INVERT_SENDER_RECIPIENT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add set for invert sender and recipient';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_PDF_MARGIN_LEFT";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add set of pdf margin left';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_PDF_MARGIN_RIGHT";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add set of pdf margin right';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_PDF_MARGIN_TOP";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add set of pdf margin top';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATE_PDF_MARGIN_BOTTOM";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Add set of pdf margin bottom';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_USE_COMPANY_NAME_OF_CONTACT";
		$this->const[$r][1] = "yesno";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Use company name of contact';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_VIEW_LINE_NUMBER";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Use view line number';
		$this->const[$r][4] = 1;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "MAIN_FORCE_RELOAD_PAGE";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '1';
		$this->const[$r][3] = 'Main force to reload page';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$r++;

		$this->const[$r][0] = "ULTIMATEPDF_MAIN_VERSION";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = $this->version;
		$this->const[$r][3] = 'Ultimatepdf main version';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 'current';
		$this->const[$r][6] = 1;
		$r++;

		$this->const[$r][0] = "ULTIMATE_DOCUMENTS_WITH_NUMBERING_WIDTH";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = '0';
		$this->const[$r][3] = 'Ultimatepdf numbering width';
		$this->const[$r][4] = 0;
		$this->const[$r][5] = 0;
		$this->const[$r][6] = 1;
		$r++;

		// Dictionnaries
		if (! isset($conf->ultimatepdf->enabled))
		{
			$conf->ultimatepdf = (object) array();
			$conf->ultimatepdf->enabled=0; // This is to avoid warnings
		}
		$this->dictionaries=$this->dictionnaries;
		$this->dictionaries=array(
			'langs'=>'ultimatepdf@ultimatepdf',
			'tabname'=>array(MAIN_DB_PREFIX."c_ultimatepdf_line",
							 MAIN_DB_PREFIX."c_ultimatepdf_title"
							 ),
			'tablib'=>array("UltimatepdfLine",
							"UltimatepdfTitle"
							 ),

			// Request to select fields
			'tabsql'=>array('SELECT ul.rowid as rowid, ul.code, ul.label, ul.description, ul.active FROM '.MAIN_DB_PREFIX.'c_ultimatepdf_line as ul',
							 'SELECT ut.rowid as rowid, ut.code, ut.label, ut.description, ut.active FROM '.MAIN_DB_PREFIX.'c_ultimatepdf_title as ut'
							 ),
			// Sort order
			'tabsqlsort'=>array("code ASC", "code ASC"),
			// List of fields (result of select to show dictionnary)
			// Nom des champs en resultat de select pour affichage du dictionnaire;
			'tabfield'=>array("code,label", "code,label"),
			// List of fields (list of fields to edit a record)
			// Nom des champs d'edition pour modification d'un enregistrement
			'tabfieldvalue'=>array("code,label", "code,label"),
			// List of fields (list of fields for insert)
			'tabfieldinsert'=>array("code,label", "code,label"),
			// Name of columns with primary key (try to always name it 'rowid')
			'tabrowid'=>array("rowid","rowid"),
			// Condition to show each dictionnary
			'tabcond'=>array($conf->ultimatepdf->enabled,$conf->ultimatepdf->enabled)
		);

		// Boxes
		$this->boxes = array();

		// Permissions
		$this->rights_class = 'ultimatepdf'; 	// Key text used to identify module (for permissions, menus, etc...)
		$this->rights = array();
		$r=0;

		$r++;
		$this->rights[$r][0] = 300101;
		$this->rights[$r][1] = 'Consulter les infos du modele';
		$this->rights[$r][2] = 'r';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'read';

		$r++;
		$this->rights[$r][0] = 300102;
		$this->rights[$r][1] = 'Modifier la fiche du modele';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'write';

		$r++;
		$this->rights[$r][0] = 300103;
		$this->rights[$r][1] = 'Changer le design';
		$this->rights[$r][2] = 'w';
		$this->rights[$r][3] = 0;
		$this->rights[$r][4] = 'ultimatepdf_design';
		$this->rights[$r][5] = 'write';

		// Main menu entries
		$this->menus = array();			// List of menus to add
		$r=0;

	}


	/**
     *		Function called when module is enabled.
     *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *		It also creates data directories.
     *
	 *      @return     int             1 if OK, 0 if KO
     */
	function init($options = '')
	{
		dol_include_once('/ultimatepdf/lib/ultimatepdf.lib.php');

		global $db, $conf;

		$sql = array(
				"INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, entity, type) VALUES
					('ultimate_invoice1', '".$conf->entity."', 'invoice'),
					('ultimate_invoice2', '".$conf->entity."', 'invoice'),
					('ultimate_weight_invoice1', '".$conf->entity."', 'invoice'),
					('ultimate_order1', '".$conf->entity."', 'order'),
					('ultimate_order2', '".$conf->entity."', 'order'),
					('ultimate_weight_order1', '".$conf->entity."', 'order'),
					('ultimate_inter', '".$conf->entity."', 'ficheinter'),
					('ultimate_shipment', '".$conf->entity."', 'shipping'),
					('ultimate_receipt', '".$conf->entity."', 'delivery'),
					('ultimate_supplierorder', '".$conf->entity."', 'order_supplier'),
					('ultimate_supplierinvoice', '".$conf->entity."', 'invoice_supplier'),
					('ultimate_project', '".$conf->entity."', 'project'),
					('ultimatecontract', '".$conf->entity."', 'contract'),
					('ultimate_propal1', '".$conf->entity."', 'propal'),
					('ultimate_propal2', '".$conf->entity."', 'propal'),
					('ultimate_weight_propal1', '".$conf->entity."', 'propal'),
					('ultimate_expensereport', '".$conf->entity."', 'expensereport');"
		);

		$result=$this->load_tables();

		$result=$this->setFirstDesign();

		// Check current version
		if (!checkUltimatepdfVersion())
		{

		}

		dolibarr_set_const($db, "MAIN_VIEW_LINE_NUMBER", '1', 'chaine', 0, '', $conf->entity);

		return $this->_init($sql, $options);
	}

	/**
	 *		Function called when module is disabled.
 	 *      Remove from database constants, boxes and permissions from Dolibarr database.
 	 *		Data directories are not deleted.
 	 *
	 *      @return     int             1 if OK, 0 if KO
 	 */
	function remove($options = '')
	{
		global $conf;

		$sql = array(
				"DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE entity = '".$conf->entity."' AND
				(nom = 'ultimate_invoice1' OR nom = 'ultimate_invoice2'	OR nom = 'ultimate_weight_invoice1'
				OR nom = 'ultimate_propal1' OR nom = 'ultimate_propal2' OR nom = 'ultimate_weight_propal1'
				OR nom = 'ultimate_order1' OR nom = 'ultimate_order2' OR nom = 'ultimate_weight_order1'
				OR nom = 'ultimate_proforma1' OR nom = 'ultimate_proforma2'
				OR nom = 'ultimate_inter' OR nom = 'best_inter'
				OR nom = 'ultimate_shipment' OR nom = 'ultimate_receipt'
				OR nom = 'ultimate_supplierorder' OR nom = 'ultimate_supplierinvoice'
				OR nom = 'ultimate_project' OR nom = 'ultimatecontract'
				OR nom = 'ultimate_expensereport');",
				"DELETE FROM ".MAIN_DB_PREFIX."extrafields WHERE entity = '".$conf->entity."'
				AND (name = 'newline' OR name = 'newtitle' OR name = 'newprice' OR name = 'newrdv');"
		);

		return $this->_remove($sql, $options);
	}

	/**
	 *		Create tables and keys required by module
	 *		This function is called by this->init.
	 *
	 * 		@return		int		<=0 if KO, >0 if OK
	 */
	function load_tables()
	{
		return $this->_load_tables('/ultimatepdf/sql/');
	}

	/**
	 *	Set the first design
	 *
	 *	@return void
	 */
	function setFirstDesign()
	{
		global $user, $langs, $conf;

		$langs->load('ultimatepdf@ultimatepdf');

		$sql = 'SELECT count(rowid) FROM '.MAIN_DB_PREFIX.'ultimatepdf';
		$res = $this->db->query($sql);
		if ($res) $num = $this->db->fetch_array($res);
		else dol_print_error($this->db);

		if (empty($num[0]))
		{
			$this->db->begin();

			$now = dol_now();
			$optionarray =  json_encode(array(
					'bgcolor'=>'aad4ff',
					'opacity'=>'0.5',
					'bordercolor'=>'003f7f',
					'textcolor'=>'191919',
					'dashdotted'=>'8, 2',
					'withref'=>'no',
					'withoutvat'=>'no',
					'otherlogo'=>'',
					'otherfont'=>'Helvetica',
					'heightforfreetext'=>'12',
					'freetextfontsize'=>'7',
					'usebackground'=>'',
					'imglinesize'=>'20',
					'logoheight'=>'20',
					'logowidth'=>'40',
					'invertSenderRecipient'=>'no',
					'widthrecbox'=>'93',
					'widthnumbering'=>'10',
					'widthref'=>'20',
					'otherlogoheight'=>'20',
					'otherlogowidth'=>'40',
					'marge_gauche'=>'10',
					'marge_droite'=>'10',
					'marge_haute'=>'10',
					'marge_basse'=>'10'
			));

			$sql = 'INSERT INTO '.MAIN_DB_PREFIX.'ultimatepdf (';
			$sql.= 'label';
			$sql.= ', description';
			$sql.= ', options';
			$sql.= ', datec';
			$sql.= ', fk_user_creat';
			$sql.= ') VALUES (';
			$sql.= '"'.$langs->trans("MasterDesign").'"';
			$sql.= ', "'.$langs->trans("MasterDesignDesc").'"';
			$sql.= ", '".$optionarray."'";
			$sql.= ', "'.$this->db->idate($now).'"';
			$sql.= ', '.$user->id;
			$sql.= ')';

			if ($this->db->query($sql))
			{
				// par défaut le premier design est sélectionné
				dolibarr_set_const($this->db, "ULTIMATE_DESIGN", 1,'chaine',0,'',$conf->entity);
				$this->db->commit();
				return 1;
			}
			else
			{
				$this->db->rollback();
				return -1;
			}
		}
		else
		{
			return 0;
		}
	}
}
?>
