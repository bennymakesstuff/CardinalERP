<?php
/* Copyright (C) 2011-2018 Regis Houssin  <regis.houssin@capnetworks.com>
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
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 */

/**
 * 		\defgroup   d-cloud     Module D-Cloud
 *      \brief      Add integration with external clouds (dropbox, ...).
 *      \file       /core/modules/modDcloud.class.php
 *      \ingroup    d-cloud
 *      \brief      Description and activation file for module D-Cloud
 */

include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 * 		\class      modDcloud
 *      \brief      Description and activation class for module D-Cloud
 */
class modDcloud extends DolibarrModules
{
   /**
    *	Constructor
    *
    *	@param	DoliDB	$db		Database handler
    */
	function __construct($db)
	{
		global $langs;

		$this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 70000;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'dcloud';

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "other";
		// Gives the possibility to the module, to provide his own family info and position of this family.
		$this->familyinfo = array(
			'core' => array(
				'position' => '001',
				'label' => $langs->trans("iNodbox")
			)
		);
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "Module for manage files in external clouds (Dropbox, etc...)";
		//$this->descriptionlong = "A very lon description. Can be a full HTML content";
		$this->editor_name = 'Régis Houssin';
		$this->editor_url = 'https://www.inodbox.com';
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '2.5.1';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory, use this->picto=DOL_URL_ROOT.'/module/img/file.png'
		$this->picto='dcloud@dcloud';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
			'triggers' => 1,
			'hooks' => array('fileslib','pdfgeneration','odtgeneration','docxgeneration','actionlinkedfiles'),
			'css' => array('/dcloud/inc/fileupload/css/jquery.fileupload-ui.css', '/dcloud/css/dropbox.css.php'),
			'js' => array(
				'/dcloud/inc/template/tmpl.min.js',
				'/dcloud/inc/fileupload/js/jquery.iframe-transport.js',
				'/dcloud/inc/fileupload/js/jquery.fileupload.js',
				//'/dcloud/inc/fileupload/js/jquery.fileupload-process.js',
				'/dcloud/inc/fileupload/js/jquery.fileupload-fp.js',
				'/dcloud/inc/fileupload/js/jquery.fileupload-ui.js',
				//'/dcloud/inc/fileupload/js/jquery.fileupload-jquery-ui.js',
				'/dcloud/inc/fileupload/js/jquery.fileupload-jui.js'
			)
		);

		// Data directories to create when module is enabled.
		$this->dirs = array('/dcloud/temp');

		// Config pages. Put here list of php page names stored in admmin directory used to setup module.
		$this->config_page_url = array("dropbox.php@dcloud");

		// Dependencies
		$this->depends = array();					// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();				// List of modules id to disable if this one is disabled
		$this->phpmin = array(5,6,4);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(4,0,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array('dcloud@dcloud');

		// Possibility for an external module to add its own parameters
		$modules = array(
			"customer"			=> array(
				"name"			=> "societe",		// for $conf->$name->enabled
				"lang"			=> "commercial",		// for $langs->load(lang);
				"title"			=> "Customers",		// for $langs->trans(title);
				"refname"		=> "nom"				// for sql query
			),
			"supplier"			=> array(
				"name"			=> "fournisseur",
				"lang"			=> "suppliers",
				"title"			=> "Suppliers",
				"dbname"			=> "societe",		// for sql query
				"refname"		=> "nom",			// for sql query
			),
			"product"			=> array(
				"name"			=> "product",
				"lang"			=> "products",
				"title"			=> "Products"
			),
			"service"			=> array(
				"name"			=> "service",
				"lang"			=> "products",
				"title"			=> "Services",
				"dbname"			=> "product"			// for sql query
			),
			"propal"				=> array(
				"name"			=> "propal",
				"lang"			=> "propal",
				"rootdir"		=> array("customer"),// for define the parent directory
				"title"			=> "Proposals",
				"prov"			=>	true				// For use VALIDATE trigger (PROV ref)
			),
			"order"				=> array(
				"name"			=> "commande",
				"lang"			=> "orders",
				"rootdir"		=> array("customer"),
				"title"			=> "Orders",
				"prov"			=>	true				// For use VALIDATE trigger (PROV ref)
			),
			"invoice"			=> array(
				"name"			=> "facture",
				"lang"			=> "bills",
				"rootdir"		=> array("customer"),
				"title"			=> "Invoices",
				"prov"			=>	true,			// For use VALIDATE trigger (PROV ref)
				"refname"		=> "facnumber"		// for sql query
			),
			"shipping"			=> array(
				"name"			=> "expedition_bon",
				"parentoutput"	=> "expedition",		// For specific dolibarr dir_ouput
				"diroutput"		=> "sending",		// For specific dolibarr dir_ouput
				"lang"			=> "sendings",
				"rootdir"		=> array("customer"),
				"title"			=> "Sendings",
				"prov"			=>	true,				// For use VALIDATE trigger (PROV ref)
				"dbname"			=> "expedition"			// for sql query
			),
			"delivery"			=> array(
				"name"			=> "livraison_bon",
				"parentoutput"	=> "expedition",		// For specific dolibarr dir_ouput
				"diroutput"		=> "receipt",			// For specific dolibarr dir_ouput
				"lang"			=> "deliveries",
				"rootdir"		=> array("customer"),
				"title"			=> "Deliveries",
				"prov"			=>	true,				// For use VALIDATE trigger (PROV ref)
				"dbname"			=> "livraison"			// for sql query
			),
			"project"			=> array(
				"name"			=> "projet",
				"lang"			=> "projects",
				"rootdir"		=> array("customer", "supplier"), // For customer and/or supplier
				"title"			=> "Projects"
			),
			"project_task"		=> array(
				"name"			=> "projet",
				"parentoutput"	=> "projet",			// For specific dolibarr dir_ouput
				"diroutput"		=> "task",
				"lang"			=> "projects",
				"rootdir"		=> array("customer", "supplier"),
				"subelement"		=> "project",			// For specific subelement (eg Project/Tasks)
				"title"			=> "Tasks",
				"nosync"			=> true					// More bugs for the moment
			),
			"intervention"		=> array(
				"name"			=> "ficheinter",
				"element"		=> "fichinter",			// For specific element name
				"lang"			=> "interventions",
				"rootdir"		=> array("customer", "supplier"),
				"title"			=> "Interventions",
				"prov"			=>	true,				// For use VALIDATE trigger (PROV ref)
				"dbname"			=> "fichinter"			// for sql query
			),
			"supplier_proposal"	=> array(
				"name"			=> "supplier_proposal",
				"diroutput"		=> "supplier_proposal",
				"lang"			=> "supplier_proposal",
				"rootdir"		=> array("supplier"),
				"title"			=> "Proposals",
				"title2"			=> "SupplierProposals",	// for an alternative admin page title
				"prov"			=>	true 				// For use VALIDATE trigger (PROV ref)
			),
			"order_supplier"		=> array(
				"name"			=> "fournisseur",
				"parentoutput"	=> "fournisseur",		// For specific dolibarr dir_ouput
				"diroutput"		=> "commande",
				"lang"			=> "orders",
				"rootdir"		=> array("supplier"),
				"title"			=> "Orders",
				"title2"			=> "SuppliersOrders",	// for an alternative admin page title
				"prov"			=>	true,				// For use VALIDATE trigger (PROV ref)
				"dbname"			=> "commande_fournisseur"	// for sql query
			),
			"invoice_supplier"	=> array(
				"name"			=> "fournisseur",
				"parentoutput"	=> "fournisseur",		// For specific dolibarr dir_ouput
				"diroutput"		=> "facture",
				"lang"			=> "bills",
				"rootdir"		=> array("supplier"),
				"title"			=> "Invoices",
				"title2"			=> "BillsSuppliers",
				"dbname"			=> "facture_fourn"		// for sql query
			),
			"payment_supplier"	=> array(
				"name"			=> "fournisseur",
				"parentoutput"	=> "fournisseur",		// For specific dolibarr dir_ouput
				"diroutput"		=> "payment",
				"lang"			=> "bills",
				"rootdir"		=> array("supplier"),
				"title"			=> "Payments",
				"title2"			=> "SupplierPayments",
				"dbname"			=> "paiementfourn"		// for sql query
			),
			"agenda"				=> array(
				"name"			=> "agenda",
				"element"		=> "action",			// For specific element name
				"lang"			=> "agenda",
				"title"			=> "Agendas",
				"dbname"			=> "actioncomm",		// for sql query
				"idname"			=> "id",				// for sql query
				"refname"		=> "id",				// for sql query
			),
			"resource"			=> array(
				"name"			=> "resource",		// for $conf->$name->enabled
				"element"		=> "dolresource",	// For specific element name
				"lang"			=> "resource",		// for $langs->load(lang);
				"title"			=> "Resources",		// for $langs->trans(title);
			),
		);

		$fileupload = '<!-- The XDomainRequest Transport is included for cross-domain file deletion for IE8+ -->'."\n";
		$fileupload.= '<!--[if gte IE 8]><script type="text/javascript" src="'.DOL_URL_ROOT.'/includes/jquery/plugins/fileupload/js/cors/jquery.xdr-transport.js"></script><![endif]-->'."\n";

		// Constants
		// List of particular constants to add when module is enabled
		$this->const=array(
			0 => array('DCLOUD_MAIN_VERSION','chaine',$this->version,'',0,'current',1),
			1 => array('DCLOUD_MAIN_MODULES','chaine',json_encode($modules),'',0,'current',1),
			2 => array('DCLOUD_MAIN_SYNC_INFO','chaine',json_encode(array()),'',0,'current',1),
			3 => array('DROPBOX_CONSUMER_KEY','chaine','jju8dtt3oa8xurn','Don\'t change this value',0,'current',1),
			4 => array('DROPBOX_CONSUMER_SECRET','chaine','gzlgxgfrug97d7p','Don\'t change this value',0,'current',1),
			5 => array('DROPBOX_ACCESS_TOKEN','chaine','','',0,'current',1),
			6 => array('DROPBOX_MAIN_ROOT','chaine','/','',0),
			7 => array('MAIN_HTML_HEADER','chaine',$fileupload,'',0,'current',1),
			8 => array('DCLOUD_MEMCACHED_SERVER','chaine','127.0.0.1:11211','',0),
			9 => array('MAIN_MODULES_FOR_EXTERNAL','chaine','user,facture,categorie,commande,fournisseur,contact,propal,projet,contrat,societe,ficheinter,expedition,agenda,adherent,dcloud','Don\'t change this values',0,'current',1),
			10 => array('DCLOUD_SHOW_THIRDPARTY_DROPBOX_TAB','chaine',1,'',0,'current',1),
			11 => array('DCLOUD_SHOW_THIRDPARTY_NATIVE_TAB','chaine',0,'',0,'current',1),
			12 => array('DCLOUD_SHOW_PRODUCT_DROPBOX_TAB','chaine',1,'',0,'current',1),
			13 => array('DCLOUD_SHOW_PRODUCT_NATIVE_TAB','chaine',0,'',0,'current',1)
		);

		// Tabs
		$this->tabs = array(
			'thirdparty:+dropbox:Dropbox:dcloud@dcloud:!empty($conf->dcloud->enabled) && !empty($conf->global->DROPBOX_ACCESS_TOKEN) && !empty($conf->global->DCLOUD_SHOW_THIRDPARTY_DROPBOX_TAB) && !empty($user->rights->dcloud->read) && (!empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) || !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED)):/dcloud/document.php?element=thirdparty&socid=__ID__',
			'thirdparty:-document:Documents:!empty($conf->dcloud->enabled) && !empty($conf->global->DROPBOX_ACCESS_TOKEN) && empty($conf->global->DCLOUD_SHOW_THIRDPARTY_NATIVE_TAB) && (!empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) || !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED))',
			'product:+dropbox:Dropbox:dcloud@dcloud:!empty($conf->dcloud->enabled) && !empty($conf->global->DROPBOX_ACCESS_TOKEN) && !empty($conf->global->DCLOUD_SHOW_PRODUCT_DROPBOX_TAB) && !empty($user->rights->dcloud->read) && (($object->type == 0 && !empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED)) || ($object->type == 1 && !empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED))):/dcloud/document.php?element=product&id=__ID__',
			'product:-documents:Documents:!empty($conf->dcloud->enabled) && !empty($conf->global->DROPBOX_ACCESS_TOKEN) && empty($conf->global->DCLOUD_SHOW_PRODUCT_NATIVE_TAB) && (($object->type == 0 && !empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED)) || ($object->type == 1 && !empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED)))'
		);


		// Boxes
		$this->boxes = array();			// List of boxes
		$r=0;

		// Permissions
		$this->rights = array();		// Permission array used by this module
		$r=0;

		$this->rights[$r][0] = 70001; // id de la permission
		$this->rights[$r][1] = 'Read Dropbox files and directories'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (deprecated)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'read';
		$r++;

		$this->rights[$r][0] = 70002; // id de la permission
		$this->rights[$r][1] = 'Write Dropbox files and directories'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (deprecated)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'write';
		$r++;

		$this->rights[$r][0] = 70003; // id de la permission
		$this->rights[$r][1] = 'Delete Dropbox files and directories'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (deprecated)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'delete';
		$r++;

		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		$this->menu[$r]=array(
			'fk_menu'=>0,
			'type'=>'top',
			'titre'=>'Dropbox',
			'mainmenu'=>'dcloud',
			'leftmenu'=>'1',
			'url'=>'/dcloud/document.php',
			'langs'=>'dcloud@dcloud',
			'position'=>100,
			'perms'=>'$user->rights->dcloud->read',
			'enabled'=>'$conf->dcloud->enabled && !empty($conf->global->DROPBOX_ACCESS_TOKEN)',
			'target'=>'',
			'user'=>2
		);
		$r++;

  	}

	/**
     *	Function called when module is enabled.
     *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
     *	It also creates data directories.
     *
	 *	@return     int             1 if OK, 0 if KO
     */
	function init($options = '')
  	{
		$sql = array();

		//$result=$this->load_tables();

		return $this->_init($sql);
  	}

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted.
	 *
	 *	@return     int             1 if OK, 0 if KO
 	 */
	function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql);
  	}


	/**
	 *	Create tables and keys required by module
	 *	Files mymodule.sql and mymodule.key.sql with create table and create keys
	 *	commands must be stored in directory /mymodule/sql/
	 *	This function is called by this->init.
	 *
	 *	@return		int		<=0 if KO, >0 if OK
	 */
  	function load_tables()
	{
		return $this->_load_tables();
	}
}
