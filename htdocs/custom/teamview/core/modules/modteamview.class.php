<?php
/* Copyright (C) 2017  Hamza Noui <h.nouib@nextconcept.ma>
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


/**
 *  Description and activation class for module MyModule
 */
class modteamview extends DolibarrModules
{
	/**
	 * Constructor. Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db Database handler
	 */
	public function __construct($db)
	{
        global $langs,$conf;

        $this->db = $db;

		// Id for module (must be unique).
		// Use here a free id (See in Home -> System information -> Dolibarr for list of used modules id).
		$this->numero = 688890938;		// TODO Go on page http://wiki.dolibarr.org/index.php/List_of_modules_id to reserve id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = get_class($this);

		// Family can be 'crm','financial','hr','projects','products','ecm','technic','other'
		// It is used to group modules in module setup page
		$this->family = "projects";
		// Gives the possibility to the module, to provide his own family info and position of this family. (canceled $this->family)
		//$this->familyinfo = array('Production' => array('position' => '001', 'label' => $langs->trans("Production")));
		// Module position in the family
		$this->module_position = 1;
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		// $this->name = str_replace('_', ' ', preg_replace('/^mod/i','',get_class($this)) );
		$this->name = preg_replace('/^mod/i','',get_class($this));
		
		// Module description, used if translation string 'ModuleXXXDesc' not found (where XXX is value of numeric property 'numero' of module)
		$this->description = "DescriptionMod688890938";
		// Possible values for version are: 'development', 'experimental', 'dolibarr' or 'dolibarr_deprecated' or version
		$this->version = '1.4';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Where to store the module in setup page (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto = 'teamview@teamview';

		$this->module_parts = array(
			'triggers' 	=> 1,
			'css' 		=> array('teamview/css/style.css','teamview/css/fontawesome.css'),
		);

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		//$this->module_parts = array(
		//                        	'triggers' => 0,                                 	// Set this to 1 if module has its own trigger directory (core/triggers)
		//							'login' => 0,                                    	// Set this to 1 if module has its own login method directory (core/login)
		//							'substitutions' => 0,                            	// Set this to 1 if module has its own substitution function file (core/substitutions)
		//							'menus' => 0,                                    	// Set this to 1 if module has its own menus handler directory (core/menus)
		//							'theme' => 0,                                    	// Set this to 1 if module has its own theme directory (theme)
		//                        	'tpl' => 0,                                      	// Set this to 1 if module overwrite template dir (core/tpl)
		//							'barcode' => 0,                                  	// Set this to 1 if module has its own barcode directory (core/modules/barcode)
		//							'models' => 0,                                   	// Set this to 1 if module has its own models directory (core/modules/xxx)
		//							'css' => array('/mymodule/css/mymodule.css.php'),	// Set this to relative path of css file if module has its own css file
	 	//							'js' => array('/mymodule/js/mymodule.js'),          // Set this to relative path of js file if module must load a js on all pages
		//							'hooks' => array('hookcontext1','hookcontext2')  	// Set here all hooks context managed by module
		//							'dir' => array('output' => 'othermodulename'),      // To force the default directories names
		//							'workflow' => array('WORKFLOW_MODULE1_YOURACTIONTYPE_MODULE2'=>array('enabled'=>'! empty($conf->module1->enabled) && ! empty($conf->module2->enabled)', 'picto'=>'yourpicto@mymodule')) // Set here all workflow context managed by module
		//                        );
		//$this->module_parts = array();

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp");
		$this->dirs = array();

		// Config pages. Put here list of php page, stored into mymodule/admin directory, to use to setup module.
		$this->config_page_url = array();

		// Dependencies
		$this->hidden = false;			// A condition to hide module
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->conflictwith = array();	// List of modules id this module is in conflict with
		$this->phpmin = array(5,0);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,0);	// Minimum version of Dolibarr required by module
		$this->langfiles = array("teamview@teamview");

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$this->const = array();

		// Array to add new pages in new tabs
		// Example: $this->tabs = array('objecttype:+tabname1:Title1:mylangfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__',  					// To add a new tab identified by code tabname1
        //                              'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        //                              'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
		// where objecttype can be
		// 'categories_x'	  to add a tab in category view (replace 'x' by type of category (0=product, 1=supplier, 2=customer, 3=member)
		// 'contact'          to add a tab in contact view
		// 'contract'         to add a tab in contract view
		// 'group'            to add a tab in group view
		// 'intervention'     to add a tab in intervention view
		// 'invoice'          to add a tab in customer invoice view
		// 'invoice_supplier' to add a tab in supplier invoice view
		// 'member'           to add a tab in fundation member view
		// 'opensurveypoll'	  to add a tab in opensurvey poll view
		// 'order'            to add a tab in customer order view
		// 'order_supplier'   to add a tab in supplier order view
		// 'payment'		  to add a tab in payment view
		// 'payment_supplier' to add a tab in supplier payment view
		// 'product'          to add a tab in product view
		// 'propal'           to add a tab in propal view
		// 'project'          to add a tab in project view
		// 'stock'            to add a tab in stock view
		// 'thirdparty'       to add a tab in third party view
		// 'user'             to add a tab in user view
        $this->tabs = array();

        // Dictionaries
	    if (! isset($conf->teamview->enabled))
        {
        	$conf->teamview=new stdClass();
        	$conf->teamview->enabled=0;
        }
		$this->dictionaries=array();
        /* Example:
        if (! isset($conf->mymodule->enabled)) $conf->mymodule->enabled=0;	// This is to avoid warnings
        $this->dictionaries=array(
            'langs'=>'mylangfile@mymodule',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),																					// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),																					// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),																				// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),																			// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),																									// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->mymodule->enabled,$conf->mymodule->enabled,$conf->mymodule->enabled)												// Condition to show each dictionary
        );
        */

        // Boxes
		// Add here list of php file(s) stored in core/boxes that contains class to show a box.
        $this->boxes = array();			// List of boxes
		// Example:
		//$this->boxes=array(
		//    0=>array('file'=>'myboxa.php@mymodule','note'=>'','enabledbydefaulton'=>'Home'),
		//    1=>array('file'=>'myboxb.php@mymodule','note'=>''),
		//    2=>array('file'=>'myboxc.php@mymodule','note'=>'')
		//);

		// Cronjobs
		$this->cronjobs = array();			// List of cron jobs entries to add
		
		$this->rights = array();
		$r=500;
		$r++;
		// Permission TeamView
		$this->rights[$r][0] = $this->numero + $r;
		$this->rights[$r][1] = 'Consulter';
		$this->rights[$r][3] = 1;
		$this->rights[$r][4] = 'gestion';
		$this->rights[$r][5] = 'consulter';
		// $r++;
		// $this->rights[$r][0] = $this->numero + $r;
		// $this->rights[$r][1] = 'Ajouter / Modifier';
		// $this->rights[$r][3] = 0;
		// $this->rights[$r][4] = 'gestion';
		// $this->rights[$r][5] = 'update';
		// $r++;
		// $this->rights[$r][0] = $this->numero + $r;
		// $this->rights[$r][1] = 'Supprimer';
		// $this->rights[$r][3] = 0;
		// $this->rights[$r][4] = 'gestion';
		// $this->rights[$r][5] = 'delete';
		$r=0;
		$this->menu[$r]=array(	'fk_menu'=>0,
					'type'=>'top',
					'titre'=>'TeamView',
					'mainmenu'=>'teamview',
					'leftmenu'=>'teamview',
					'url'=>'/teamview/projets/index.php',
					'langs'=>'teamview@teamview',
					'position'=>100,
					'enabled'=>'1',
					'perms'=>'$user->rights->modteamview->gestion->consulter',
					'target'=>'',
					'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=teamview',
					'type'=>'left',
					'titre'=>'Projets',
					//'mainmenu'=>'teamview',
		            'leftmenu'=>'projets',
					'url'=>'/teamview/projets/index.php',
					'langs'=>'teamview@teamview',
					'position'=>1,
					'enabled'=>'1',
					'perms'=>'$user->rights->modteamview->gestion->consulter',
					'target'=>'',
					'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=teamview',
					'type'=>'left',
					'titre'=>'Tâches',
					//'mainmenu'=>'teamview',
		            'leftmenu'=>'teamview1',
					'url'=>'/teamview/index.php',
					'langs'=>'teamview@teamview',
					'position'=>1,
					'enabled'=>'1',
					'perms'=>'$user->rights->modteamview->gestion->consulter',
					'target'=>'',
					'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=teamview',
					'type'=>'left',
					'titre'=>'Propales',
					//'mainmenu'=>'teamview',
		            'leftmenu'=>'propals',
					'url'=>'/teamview/propals/index.php',
					'langs'=>'teamview@teamview',
					'position'=>1,
					'enabled'=>'1',
					'perms'=>'$user->rights->modteamview->gestion->consulter',
					'target'=>'',
					'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=teamview',
					'type'=>'left',
					'titre'=>'Commandes',
					//'mainmenu'=>'teamview',
		            'leftmenu'=>'commandes',
					'url'=>'/teamview/commandes/index.php',
					'langs'=>'teamview@teamview',
					'position'=>1,
					'enabled'=>'1',
					'perms'=>'$user->rights->modteamview->gestion->consulter',
					'target'=>'',
					'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=teamview',
					'type'=>'left',
					'titre'=>'Factures',
					//'mainmenu'=>'teamview',
		            'leftmenu'=>'factures',
					'url'=>'/teamview/factures/index.php',
					'langs'=>'teamview@teamview',
					'position'=>1,
					'enabled'=>'1',
					'perms'=>'$user->rights->modteamview->gestion->consulter',
					'target'=>'',
					'user'=>2);
		$r++;
		$this->menu[$r]=array(	'fk_menu'=>'fk_mainmenu=teamview',
					'type'=>'left',
					'titre'=>'Prospects',
					//'mainmenu'=>'teamview',
		            'leftmenu'=>'prospects',
					'url'=>'/teamview/prospects/index.php',
					'langs'=>'teamview@teamview',
					'position'=>1,
					'enabled'=>'1',
					'perms'=>'$user->rights->modteamview->gestion->consulter',
					'target'=>'',
					'user'=>2);
		$r=1;

	}

	/**
	 *		Function called when module is enabled.
	 *		The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *		It also creates data directories
	 *
     *      @param      string	$options    Options when enabling module ('', 'noboxes')
	 *      @return     int             	1 if OK, 0 if KO
	 */
	public function init($options='')
	{
		$sql = array();

		$this->_load_tables('/teamview/sql/');

		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param      string	$options    Options when enabling module ('', 'noboxes')
	 * @return     int             	1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = $this->dropTables();

		return $this->_remove($sql, $options);
	}


	private function dropTables()
	{
		return array(
		);
		
	}



}

