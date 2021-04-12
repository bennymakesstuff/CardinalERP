<?php
/* Copyright (C) 2003      Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004-2012 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012 Regis Houssin        <regis.houssin@capnetworks.com>
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
 */

/**
 * 	\defgroup   timesheet     Timesheet Module
 *  \brief      timesheet module descriptor.
 *
 *  \file       htdocs/mymodule/core/modules/modtimesheet.class.php
 *  \ingroup    timesheet
 *  \brief      Description and activation file for Timesheet module
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';



// The class name should start with a lower case mod for Dolibarr to pick it up
// so we ignore the Squiz.Classes.ValidClassName.NotCamelCaps rule.
// @codingStandardsIgnoreStart

/**
 *  Description and activation class for module Timesheet
 */
class modTimesheet extends DolibarrModules
{
	// @codingStandardsIgnoreEnd
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
		$this->numero = 861002;		// TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'timesheet';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "projects";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '90';
		// Gives the possibility to the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));

		// Module label (no space allowed), used if translation string 'ModuleMyoduleName' not found (MyModule is name of module).
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleMyModuleDesc' not found (MyModule is name of module).
		$this->description = "Description of module Timesheet";
		// Used only if file README.md and README-LL.md not found.
		$this->editor_name = 'Editor name';
		$this->editor_url = 'https://www.example.com';


		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '3.3.0';
		// Key used in llx_const table to save module status enabled/disabled (where MYMODULE is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
        // Where to store the module in setup page
        // (0=common,1=interface,2=others,3=very specific)
		$this->special = 0;
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='timesheet@timesheet';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /mymodule/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /mymodule/core/modules/barcode)
		// for specific css file (eg: /mymodule/css/mymodule.css.php)
		$this->module_parts = array(
			'triggers' => 0,										// Set this to 1 if module has its own trigger directory (core/triggers)
			'login' => 0,											// Set this to 1 if module has its own login method file (core/login)
			'substitutions' => 1,									// Set this to 1 if module has its own substitution function file (core/substitutions)
			'menus' => 0,											// Set this to 1 if module has its own menus handler directory (core/menus)
			'theme' => 0,											// Set this to 1 if module has its own theme directory (theme)
			'tpl' => 0,												// Set this to 1 if module overwrite template dir (core/tpl)
			'barcode' => 0,											// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'models' => 0,											// Set this to 1 if module has its own models directory (core/modules/xxx)
			'css' => array('/timesheet/core/css/timesheet.css'),	// Set this to relative path of css file if module has its own css file
//			'js' => array('/mymodule/js/mymodule.js.php'),			// Set this to relative path of js file if module must load a js on all pages
//			'hooks' => array('data'=>array('hookcontext1','hookcontext2'), 'entity'=>'0'),	// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
			'moduleforexternal' => 0								// Set this to 1 if feature of module are opened to external users
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/mymodule/temp","/mymodule/subdir");
		$this->dirs = array(
			"/timesheet",
			"/timesheet/reports",
			"/timesheet/users",
			"/timesheet/tasks"
		);

		// Config pages. Put here list of php pages stored into mymodule/admin directory used to setup module.
		$this->config_page_url = array("timesheetsetup.php@timesheet");

		// Dependencies
		$this->hidden = false;						// A condition to hide module
		$this->depends = array('modProjet');		// List of module class names as string that must be enabled if this module is enabled
		$this->requiredby = array();				// List of module class names to disable if this one is disabled
		$this->conflictwith = array();				// List of module class names as string this module is in conflict with
		$this->langfiles = array("timesheet@timesheet");
		$this->phpmin = array(5,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(3,5);	// Minimum version of Dolibarr required by module
		$this->warnings_activation = array();		// Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array();	// Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'PartsWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;				// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('MYMODULE_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('MYMODULE_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
        $r=0;
		$this->const = array(
		);
		$this->const[$r] = array("TIMESHEET_TIME_TYPE","chaine","hours","layout mode of the timesheets"); // hours or days
		$r++;
		$this->const[$r] = array("TIMESHEET_DAY_DURATION","chaine","8","number of hour per day (used for the layout per day)"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_HIDE_DRAFT","chaine","0","option to mask to task belonging to draft project"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_HIDE_ZEROS","chaine","0","option to hide the 00:00"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_HEADERS","chaine","Tasks","list of headers to show inthe timesheets"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_HIDE_REF","chaine","0","option to hide the ref in the timesheets"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_WHITELIST_MODE","chaine","0","Option to change the behaviour of the whitelist:-whiteliste,1-blackliste,2-no impact "); 
		$r++;
		$this->const[$r] = array("TIMESHEET_WHITELIST","chaine","1","Activate the whitelist:"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_COL_DRAFT","chaine","FFFFFF","color of draft"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_COL_SUBMITTED","chaine","00FFFF","color of submitted"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_COL_APPROVED","chaine","00FF00","color of approved"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_COL_CANCELLED","chaine","FFFF00","color of cancelled"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_COL_REJECTED","chaine","FF0000","color of rejected"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_DAY_MAX_DURATION","chaine","12","max working hours per days"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_ADD_HOLIDAY_TIME","chaine","1","count the holiday in total or not"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_OPEN_DAYS","chaine","_1111100","normal day for time booking"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_APPROVAL_BY_WEEK","chaine","0","Approval by week instead of by user"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_MAX_APPROVAL","chaine","5","Max TS per Approval page"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_ADD_DOCS","chaine","0","Allow to join files to timesheets"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_APPROVAL_FLOWS","chaine","_00000","Approval flows "); 
		$r++;
		$this->const[$r] = array("TIMESHEET_INVOICE_METHOD","chaine","0","Approval by week instead of by user"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_INVOICE_TASKTIME","chaine","all","set the default task to include in the invoice item"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_INVOICE_SERVICE","chaine","0","set a default service for the invoice item"); 
		$r++;
		$this->const[$r] = array("TIMESHEET_INVOICE_SHOW_TASK","chaine","1","Show task on the invoice item "); 
		$r++;
		$this->const[$r] = array("TIMESHEET_INVOICE_SHOW_USER","chaine","1","Show user on the invoice item "); 
		$r++;
		$this->const[$r] = array("TIMESHEET_TIME_SPAN","chaine","splitedWeek","timespan of the timesheets"); // hours or days
		$r++;
		$this->const[$r] = array("TIMESHEET_ADD_FOR_OTHER","chaine","0","enable to time spent entry for subordinates"); // hours or days
		$r++;
		$this->const[$r] = array("TIMESHEET_VERSION","chaine",$this->version,"save the timesheet verison"); // hours or days
		$r++;
		$this->const[$r] = array("TIMESHEET_SHOW_TIMESPENT_NOTE","chaine","1","show the note next to the time entry"); // hours or days
		$r++;
		$this->const[$r] = array("TIMESHEET_PDF_NOTEISOTASK","chaine","0","save the timesheet verison"); // hours or days

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (! isset($conf->timesheet) || ! isset($conf->timesheet->enabled))
		{
			$conf->timesheet=new stdClass();
			$conf->timesheet->enabled=0;
		}


		// Array to add new pages in new tabs
        $this->tabs = array(
		);
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@mymodule:$user->rights->mymodule->read:/mymodule/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
        // $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@mymodule:$user->rights->othermodule->read:/mymodule/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
        // $this->tabs[] = array('data'=>'objecttype:-tabname:NU:conditiontoremove');                                                     										// To remove an existing tab identified by code tabname
        //
        // Where objecttype can be
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


        // Dictionaries
		$this->dictionaries=array(
            'langs' => 'timesheet@timesheet',
            'tabname' => array(
				MAIN_DB_PREFIX."c_fmc_wo_status",
				MAIN_DB_PREFIX."c_fmc_wo_priority",
				MAIN_DB_PREFIX."c_fmc_wo_invcategory",
				MAIN_DB_PREFIX."c_fmc_wo_department"
			),		// List of tables we want to see into dictonary editor
            'tablib' =>array(
				"WO Status",
				"WO Priority",
				"WO Invoicing Category",
				"WO Allocated Department",
			),	// Label of tables
            'tabsql'=>array(
				'SELECT f.rowid as rowid, f.code, f.pos, f.label, f.active, f.use_default FROM ' . MAIN_DB_PREFIX . 'c_fmc_wo_status as f',
				'SELECT f.rowid as rowid, f.code, f.pos, f.label, f.active, f.use_default FROM ' . MAIN_DB_PREFIX . 'c_fmc_wo_priority as f',
				'SELECT f.rowid as rowid, f.code, f.pos, f.label, f.active, f.use_default FROM ' . MAIN_DB_PREFIX . 'c_fmc_wo_invcategory as f',
				'SELECT f.rowid as rowid, f.code, f.pos, f.label, f.active, f.use_default FROM ' . MAIN_DB_PREFIX . 'c_fmc_wo_department as f'
			),	// Request to select fields
            'tabsqlsort' => array(
				"pos ASC", "pos ASC", "pos ASC", "pos ASC"
			),			// Sort order
            'tabfield' => array(
"pos,code,label,use_default", "pos,code,label,use_default", "pos,code,label,use_default", "pos,code,label,use_default"
			),	// List of fields (result of select to show dictionary)
            'tabfieldvalue' => array(
				"pos,code,label,use_default", "pos,code,label,use_default", "pos,code,label,use_default", "pos,code,label,use_default"
			),	// List of fields (list of fields to edit a record)
            'tabfieldinsert' => array(
				"pos,code,label,use_default", "pos,code,label,use_default", "pos,code,label,use_default", "pos,code,label,use_default"
			),	// List of fields (list of fields for insert)
            'tabrowid' => array(
				"rowid", "rowid", "rowid", "rowid"
			),	// Name of columns with primary key (try to always name it 'rowid')
            'tabcond' => array(
				$conf->timesheet->enabled, $conf->timesheet->enabled, $conf->timesheet->enabled, $conf->timesheet->enabled, $conf->timesheet->enabled
			)	// Condition to show each dictionary
		);
        /* Example:
        $this->dictionaries=array(
            'langs'=>'mylangfile@parts',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),										// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),										// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),									// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),								// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),														// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->parts->enabled,$conf->parts->enabled,$conf->parts->enabled)				// Condition to show each dictionary
        );
        */

        // Boxes/Widgets
		// Add here list of php file(s) stored in mymodule/core/boxes that contains class to show a widget.
        $this->boxes = array(
        	0 => array(
				'file'=>'box_approval.php@timesheet',
				'note'=>'timesheetApproval',
				'enabledbydefaulton'=>'Home')
        );
		// Example:
/*		$this->boxes=array(
			array(
				0=>array('file'=>'myboxa.php','note'=>'','enabledbydefaulton'=>'Home'),
				1=>array('file'=>'myboxb.php','note'=>''),
				2=>array('file'=>'myboxc.php','note'=>'')
			);
		);
*/


		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
		);
		// Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->parts->enabled'),
		//                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->parts->enabled')
		// );


		// Permissions
		$this->rights = array();		// Permission array used by this module

		 $r=0;
		 $this->rights[$r][0] = 86100201; 			// Permission id (must not be already used)
		 $this->rights[$r][1] = 'Approver';			// Permission label
		 $this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		 $this->rights[$r][4] = 'approval';			// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		 $this->rights[$r][5] = 'team';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)

		 $r++;
		 $this->rights[$r][0] = 86100202; 			// Permission id (must not be already used)
		 $this->rights[$r][1] = 'Admin';			// Permission label
		 $this->rights[$r][3] = 0; 					// Permission by default for new user (0/1)
		 $this->rights[$r][4] = 'approval';			// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		 $this->rights[$r][5] = 'admin';			// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)

		 $r++;
		 $this->rights[$r][0] = 86100203; 			// Permission id (must not be already used)
		 $this->rights[$r][1] = 'Read';				// Permission label
		 $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		 $this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		 //$this->rights[$r][5] = 'admin';			// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		 

		// Add here list of permission defined by an id, a label, a boolean and two constant strings.
		// Example:
		// $this->rights[$r][0] = 2000; 				// Permission id (must not be already used)
		// $this->rights[$r][1] = 'Permision label';	// Permission label
		// $this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		// $this->rights[$r][4] = 'level1';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $this->rights[$r][5] = 'level2';				// In php code, permission will be checked by test if ($user->rights->permkey->level1->level2)
		// $r++;

		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus

		/* BEGIN MODULEBUILDER TOPMENU */
		$this->menu[$r++]=array(
			'fk_menu'=>'',							// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top',			                // This is a Top menu entry
			'titre'=>'Timesheet',
			'mainmenu'=>'timesheet',
			'leftmenu'=>'timesheet',
			'url'=>'/timesheet/Timecard.php',
			'langs'=>'timesheet@timesheet',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>100,
			'enabled'=>'$conf->timesheet->enabled',	// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both

		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU TIMESHEET */

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=timesheet',	    // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'Timesheet',
			'mainmenu'=>'timesheet',
			'leftmenu'=>'Timesheet',
			'url'=>'/timesheet/Timecard.php?action=list',
			'langs'=>'timesheet@timesheet',			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>100,
			'enabled'=>'$conf->timesheet->enabled',	// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=timesheet',		// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'userReport',
			'mainmenu'=>'timesheet',
			'leftmenu'=>'timesheet',
			'url'=>'/timesheet/TimesheetReportUser.php',
			'langs'=>'timesheet@timesheet',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>120,
			'enabled'=>'$conf->timesheet->enabled', // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=timesheet',		// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'Timesheetwhitelist',
			'mainmenu'=>'timesheet',
			'leftmenu'=>'Timesheetwhitelist',
			'url'=>'/timesheet/TimesheetFavouriteAdmin.php',
			'langs'=>'timesheet@timesheet',			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>110,
			'enabled'=>'$conf->global->TIMESHEET_WHITELIST==1',	// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=timesheet,fk_leftmenu=Timesheet',  // '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'Fortnightly Timesheets',
			'mainmenu'=>'timesheet',
			'leftmenu'=>'timesheet',
			'url'=>'/timesheet/TimecardUserTimes.php',
			'langs'=>'timesheet@timesheet',			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>101,
			'enabled'=>'$conf->timesheet->enabled',	// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=projects',	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'projectReport',
			'mainmenu'=>'project',
			'leftmenu'=>'projectReport',
			'url'=>'/timesheet/TimesheetReportProject.php',
			'langs'=>'timesheet@timesheet',			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>120,
			'enabled'=>'$conf->timesheet->enabled',	// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=projects',	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'projectInvoice',
			'mainmenu'=>'project',
			'leftmenu'=>'projectInvoice',
			'url'=>'/timesheet/TimesheetProjectInvoice.php',
			'langs'=>'timesheet@timesheet',			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>121,
			'enabled'=>'$conf->timesheet->enabled',	// Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->facture->creer',	// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=projects',	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'projectsSummary',
			'mainmenu'=>'project',
			'leftmenu'=>'projectSummary',
			'url'=>'/timesheet/ProjectSummary.php',
			'langs'=>'timesheet@timesheet',			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>131,
			'enabled'=>'$conf->timesheet->enabled', // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=projects',	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'projectsColMatrix',
			'mainmenu'=>'project',
			'leftmenu'=>'projectMatrix',
			'url'=>'/timesheet/ColourMatrix.php',
			'langs'=>'timesheet@timesheet',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>132,
			'enabled'=>'$conf->timesheet->enabled', // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=timesheet',		// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'Timesheetapproval',
			'mainmenu'=>'timesheet',
			'leftmenu'=>'Timesheetapproval',
			'url'=>'/timesheet/TimesheetTeamApproval.php',
			'langs'=>'timesheet@timesheet',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>130,
			'enabled'=>'$user->rights->timesheet->approval',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=timesheet,fk_leftmenu=Timesheetapproval',	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'Adminapproval',
			'mainmenu'=>'timesheet',
			'leftmenu'=>'Adminapproval',
			'url'=>'/timesheet/TimesheetUserTasksAdmin.php?action=list&sortfield=t.date_start&sortorder=desc',
			'langs'=>'timesheet@timesheet',	        // Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>131,
			'enabled'=>'$user->rights->timesheet->approval',  // Define condition to show or hide menu entry. Use '$conf->mymodule->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'$user->rights->timesheet->approval->admin',	// Use 'perms'=>'$user->rights->mymodule->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both


		// Exports
		$r=1;

		/* BEGIN MODULEBUILDER EXPORT */
		/*

		// Example:
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='CustomersInvoicesAndInvoiceLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
        $this->export_enabled[$r]='1';                               // Condition to show export in list (ie: '$user->id==3'). Set to 1 to always show when module is enabled.
		$this->export_permission[$r]=array(array("facture","facture","export"));
		 $this->export_fields_array[$r]=array('s.rowid'=>"IdCompany",'s.nom'=>'CompanyName','s.address'=>'Address','s.zip'=>'Zip','s.town'=>'Town','s.fk_pays'=>'Country','s.phone'=>'Phone','s.siren'=>'ProfId1','s.siret'=>'ProfId2','s.ape'=>'ProfId3','s.idprof4'=>'ProfId4','s.code_compta'=>'CustomerAccountancyCode','s.code_compta_fournisseur'=>'SupplierAccountancyCode','f.rowid'=>"InvoiceId",'f.facnumber'=>"InvoiceRef",'f.datec'=>"InvoiceDateCreation",'f.datef'=>"DateInvoice",'f.total'=>"TotalHT",'f.total_ttc'=>"TotalTTC",'f.tva'=>"TotalVAT",'f.paye'=>"InvoicePaid",'f.fk_statut'=>'InvoiceStatus','f.note'=>"InvoiceNote",'fd.rowid'=>'LineId','fd.description'=>"LineDescription",'fd.price'=>"LineUnitPrice",'fd.tva_tx'=>"LineVATRate",'fd.qty'=>"LineQty",'fd.total_ht'=>"LineTotalHT",'fd.total_tva'=>"LineTotalTVA",'fd.total_ttc'=>"LineTotalTTC",'fd.date_start'=>"DateStart",'fd.date_end'=>"DateEnd",'fd.fk_product'=>'ProductId','p.ref'=>'ProductRef');
		$this->export_entities_array[$r]=array('s.rowid'=>"company",'s.nom'=>'company','s.address'=>'company','s.zip'=>'company','s.town'=>'company','s.fk_pays'=>'company','s.phone'=>'company','s.siren'=>'company','s.siret'=>'company','s.ape'=>'company','s.idprof4'=>'company','s.code_compta'=>'company','s.code_compta_fournisseur'=>'company','f.rowid'=>"invoice",'f.facnumber'=>"invoice",'f.datec'=>"invoice",'f.datef'=>"invoice",'f.total'=>"invoice",'f.total_ttc'=>"invoice",'f.tva'=>"invoice",'f.paye'=>"invoice",'f.fk_statut'=>'invoice','f.note'=>"invoice",'fd.rowid'=>'invoice_line','fd.description'=>"invoice_line",'fd.price'=>"invoice_line",'fd.total_ht'=>"invoice_line",'fd.total_tva'=>"invoice_line",'fd.total_ttc'=>"invoice_line",'fd.tva_tx'=>"invoice_line",'fd.qty'=>"invoice_line",'fd.date_start'=>"invoice_line",'fd.date_end'=>"invoice_line",'fd.fk_product'=>'product','p.ref'=>'product');
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM ('.MAIN_DB_PREFIX.'facture as f, '.MAIN_DB_PREFIX.'facturedet as fd, '.MAIN_DB_PREFIX.'societe as s)';
		$this->export_sql_end[$r] .=' LEFT JOIN '.MAIN_DB_PREFIX.'product as p on (fd.fk_product = p.rowid)';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND f.fk_soc = s.rowid AND f.rowid = fd.fk_facture';
		$this->export_sql_order[$r] .=' ORDER BY s.nom';
		$r++; */
		/* END MODULEBUILDER EXPORT */
	}

	/**
	 *	Function called when module is enabled.
	 *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *	It also creates data directories
	 *
     *	@param      string	$options    Options when enabling module ('', 'noboxes')
	 *	@return     int             	1 if OK, 0 if KO
	 */
	public function init($options = '')
	{
		global $db,$conf;
		$result=$this->_load_tables('/timesheet/sql/');
		if ($result < 0) return -1; // Do not activate module if not allowed errors found on module SQL queries (the _load_table run sql with run_sql with error allowed parameter to 'default')
		$sql = array();
		$sql[0] = 'DELETE IGNORE FROM '.MAIN_DB_PREFIX.'project_task_timesheet';
		$sql[0].= ' WHERE status IN (1,5)'; //'DRAFT','REJECTED'
		$sql[1] ="DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = 'rat' AND type='timesheetReport' AND entity = ".$conf->entity;
		$sql[2] ="INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('rat','timesheetReport',".$conf->entity.")";

		dolibarr_set_const($db, "TIMESHEET_VERSION", $this->version, 'chaine', 0, '', $conf->entity);

		// Create extrafields
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		$extrafields->addExtraField('fk_service', "DefaultService", 'sellist', 1, '', 'user',         0, 0, '', array('options'=>array('product:ref|label:rowid::tosell=1 AND fk_product_type=1'=>'N')), 1, 1, 3, 0, '', 0, 'timesheet@ptimesheet', '$conf->timesheet->enabled');
		$extrafields->addExtraField('fk_service', "DefaultService", 'sellist', 1, '', 'projet_task',  0, 0, '', array('options'=>array('product:ref|label:rowid::tosell=1 AND fk_product_type=1'=>'N')), 1, 1, 3, 0, '', 0, 'timesheet@ptimesheet', '$conf->timesheet->enabled');
		$extrafields->addExtraField('invoiceable', "Invoiceable", 'boolean',   1, '', 'projet_task',  0, 0, '', '', 1, 1, 1, 0, '', 0, 'timesheet@ptimesheet', '$conf->timesheet->enabled');

		return $this->_init($sql, $options);
	}

	/**
	 *	Function called when module is disabled.
	 *	Remove from database constants, boxes and permissions from Dolibarr database.
	 *	Data directories are not deleted
	 *
	 *	@param      string	$options    Options when enabling module ('', 'noboxes')
	 *	@return     int             	1 if OK, 0 if KO
	 */
	public function remove($options = '')
	{
		$sql = array();

		return $this->_remove($sql, $options);
	}

}