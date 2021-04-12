<?php
/* Copyright (C) 2004-2018 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2019 Peter Roberts <webmaster@finchmc.com.au>
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
 * 	\defgroup   wip     Module WIP
 *  \brief      WIP module descriptor.
 *
 *  \file       htdocs/wip/core/modules/modWIP.class.php
 *  \ingroup    wip
 *  \brief      Description and activation file for module WIP
 */
include_once DOL_DOCUMENT_ROOT .'/core/modules/DolibarrModules.class.php';


// The class name should start with a lower case mod for Dolibarr to pick it up
// so we ignore the Squiz.Classes.ValidClassName.NotCamelCaps rule.
// @codingStandardsIgnoreStart
/**
 *  Description and activation class for module WIP
 */
class modWIP extends DolibarrModules
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
		$this->numero = 1711500020;		// TODO Go on page https://wiki.dolibarr.org/index.php/List_of_modules_id to reserve id number for your module
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class = 'wip';

		// Family can be 'base' (core modules),'crm','financial','hr','projects','products','ecm','technic' (transverse modules),'interface' (link with external tools),'other','...'
		// It is used to group modules by family in module setup page
		$this->family = "finch";
		// Module position in the family on 2 digits ('01', '10', '20', ...)
		$this->module_position = '03';
		// Gives the possibility to the module, to provide his own family info and position of this family (Overwrite $this->family and $this->module_position. Avoid this)
		//$this->familyinfo = array('myownfamily' => array('position' => '01', 'label' => $langs->trans("MyOwnFamily")));
		$this->familyinfo = array(
			'core' => array(
				'position' => '001',
				'label' => $langs->trans("Finch")
			)
		);
		// Module label (no space allowed), used if translation string 'ModuleWIPName' not found (WIP is name of module).
		$this->name = preg_replace('/^mod/i','',get_class($this));
		// Module description, used if translation string 'ModuleWIPDesc' not found (WIP is name of module).
		$this->description = "WIPDescription";
		// Used only if file README.md and README-LL.md not found.
		$this->descriptionlong = "WIPDescription (Long)";
		$this->editor_name = 'Peter Roberts';
		$this->editor_url = 'https://www.finchmc.com.au';

		// Possible values for version are: 'development', 'experimental', 'dolibarr', 'dolibarr_deprecated' or a version string like 'x.y.z'
		$this->version = '1.0';
		// Key used in llx_const table to save module status enabled/disabled (where WIP is value of property name of module in uppercase)
		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		// Name of image file used for this module.
		// If file is in theme/yourtheme/img directory under name object_pictovalue.png, use this->picto='pictovalue'
		// If file is in module/img directory under name object_pictovalue.png, use this->picto='pictovalue@module'
		$this->picto='wip@wip';

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		// for default path (eg: /wip/core/xxxxx) (0=disable, 1=enable)
		// for specific path of parts (eg: /wip/core/modules/barcode)
		// for specific css file (eg: /wip/css/wip.css.php)
		$this->module_parts = array(
			'triggers' => 1,										// Set this to 1 if module has its own trigger directory (core/triggers)
			'login' => 0,											// Set this to 1 if module has its own login method file (core/login)
			'substitutions' => 1,									// Set this to 1 if module has its own substitution function file (core/substitutions)
			'menus' => 0,											// Set this to 1 if module has its own menus handler directory (core/menus)
			'theme' => 0,											// Set this to 1 if module has its own theme directory (theme)
			'tpl' => 0,												// Set this to 1 if module overwrite template dir (core/tpl)
			'barcode' => 0,											// Set this to 1 if module has its own barcode directory (core/modules/barcode)
			'models' => 1,											// Set this to 1 if module has its own models directory (core/modules/xxx)
			'css' => array('/wip/css/wip.css.php'),				// Set this to relative path of css file if module has its own css file
	 		'js' => array('/wip/js/wip.js.php'),          		// Set this to relative path of js file if module must load a js on all pages
			'hooks' => array(
					//'ordercard',
					//'propalcard',
					//'invoicecard',
					//'pdf_getlineunit'
					//'ordersuppliercard',
					//'invoicesuppliercard'
			),
//			'hooks' => array('data'=>array('hookcontext1','hookcontext2'), 'entity'=>'0'),	// Set here all hooks context managed by module. To find available hook context, make a "grep -r '>initHooks(' *" on source code. You can also set hook context 'all'
			'moduleforexternal' => 0								// Set this to 1 if feature of module are opened to external users
		);

		// Data directories to create when module is enabled.
		// Example: this->dirs = array("/wip/temp","/wip/subdir");
		$this->dirs = array(
			"/wip/temp"
		);

		// Config pages. Put here list of php pages, stored into wip/admin directory used to setup module.
		$this->config_page_url = array("setup.php@wip");

		// Dependencies
		$this->hidden = false;						// A condition to hide module
		$this->depends = array();					// List of module class names as string that must be enabled if this module is enabled
		$this->requiredby = array();				// List of module class names to disable if this one is disabled
		$this->conflictwith = array();				// List of module class names as string this module is in conflict with
		$this->langfiles = array("wip@wip");
		$this->phpmin = array(5,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(4,0);	// Minimum version of Dolibarr required by module
		$this->warnings_activation = array();		// Warning to show when we activate module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		$this->warnings_activation_ext = array();	// Warning to show when we activate an external module. array('always'='text') or array('FR'='textfr','ES'='textes'...)
		//$this->automatic_activation = array('FR'=>'WIPWasAutomaticallyActivatedBecauseOfYourCountryChoice');
		//$this->always_enabled = true;				// If true, can't be disabled

		// Constants
		// List of particular constants to add when module is enabled (key, 'chaine', value, desc, visible, 'current' or 'allentities', deleteonunactive)
		// Example: $this->const=array(0=>array('WIP_MYNEWCONST1','chaine','myvalue','This is a constant to add',1),
		//                             1=>array('WIP_MYNEWCONST2','chaine','myvalue','This is another constant to add',0, 'current', 1)
		// );
		$this->const = array();
		$r=0;
		/*
		$this->const = array(
			$r=>array(
				'WIP_TIMEBLOCK',
				'chaine',
				'10',
				'Minimum block of time for billing',
				1,
				'allentities',
				1)
		);
		*/
		$this->const[$r][0] = "WIP_ADDON_PDF";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "project_report";
		$this->const[$r][3] = 'Name of the PDF report generation manager';
		$this->const[$r][4] = 0;
		$r++;
 
		$this->const[$r][0] = "WIP_ADDON_NUMBER";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "mod_wip_reportnum";
		$this->const[$r][3] = 'Name of the report numbering manager';
		$this->const[$r][4] = 0;
		$r++;

		$this->const[$r][0] = "WIP_TIME_BLOCK";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "10";
		$this->const[$r][3] = 'Minimum block of time for billing';
		$this->const[$r][4] = 1;
		$this->const[$r][5] = 'allentities';
		$this->const[$r][6] = 0;
		$r++;

		$this->const[$r][0] = "WIP_DEFAULT_SERVICE_PRICE";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "100";
		$this->const[$r][3] = 'Default Labour Rate (to prefill forms with this number instead of only a zero)';
		$this->const[$r][4] = 1;
		$this->const[$r][5] = 'allentities';
		$this->const[$r][6] = 0;
		$r++;

		$this->const[$r][0] = "WIP_DEFAULT_MINIMUM_PRICE";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "100";
		$this->const[$r][3] = 'Default Minimum Labour Rate (to prefill forms with this number instead of only a zero)';
		$this->const[$r][4] = 1;
		$this->const[$r][5] = 'allentities';
		$this->const[$r][6] = 0;
		$r++;

		$this->const[$r][0] = "WIP_SERVICE_CATEGORY";
		$this->const[$r][1] = "chaine";
		$this->const[$r][2] = "1";
		$this->const[$r][3] = 'Category ID for Labour Service Category (as set up manually for this company)';
		$this->const[$r][4] = 1;
		$this->const[$r][5] = 'allentities';
		$this->const[$r][6] = 0;
		$r++;

		// Some keys to add into the overwriting translation tables
		/*$this->overwrite_translation = array(
			'en_US:ParentCompany'=>'Parent company or reseller',
			'fr_FR:ParentCompany'=>'Maison mère ou revendeur'
		)*/

		if (! isset($conf->wip) || ! isset($conf->wip->enabled))
		{
			$conf->wip=new stdClass();
			$conf->wip->enabled=0;
		}


		// Array to add new pages in new tabs
        $this->tabs = array(
			'data'=>
			'project:+reports:Reports:wip@wip::/wip/report_list.php?id=__ID__',
			'project:+wip:WIP:wip@wip::/wip/wiplist.php?id=__ID__',
			'task:+packets:Packets:wip@wip::/wip/time.php?id=__ID__&withproject=1'
		);
		// Example:
		// $this->tabs[] = array('data'=>'objecttype:+tabname1:Title1:mylangfile@wip:$user->rights->wip->read:/wip/mynewtab1.php?id=__ID__');  					// To add a new tab identified by code tabname1
        // $this->tabs[] = array('data'=>'objecttype:+tabname2:SUBSTITUTION_Title2:mylangfile@wip:$user->rights->othermodule->read:/wip/mynewtab2.php?id=__ID__',  	// To add another new tab identified by code tabname2. Label will be result of calling all substitution functions on 'Title2' key.
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
		);
        /* Example:
        $this->dictionaries=array(
            'langs'=>'mylangfile@wip',
            'tabname'=>array(MAIN_DB_PREFIX."table1",MAIN_DB_PREFIX."table2",MAIN_DB_PREFIX."table3"),		// List of tables we want to see into dictonnary editor
            'tablib'=>array("Table1","Table2","Table3"),													// Label of tables
            'tabsql'=>array('SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table1 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table2 as f','SELECT f.rowid as rowid, f.code, f.label, f.active FROM '.MAIN_DB_PREFIX.'table3 as f'),	// Request to select fields
            'tabsqlsort'=>array("label ASC","label ASC","label ASC"),										// Sort order
            'tabfield'=>array("code,label","code,label","code,label"),										// List of fields (result of select to show dictionary)
            'tabfieldvalue'=>array("code,label","code,label","code,label"),									// List of fields (list of fields to edit a record)
            'tabfieldinsert'=>array("code,label","code,label","code,label"),								// List of fields (list of fields for insert)
            'tabrowid'=>array("rowid","rowid","rowid"),														// Name of columns with primary key (try to always name it 'rowid')
            'tabcond'=>array($conf->wip->enabled,$conf->wip->enabled,$conf->wip->enabled)				// Condition to show each dictionary
        );
        */


        // Boxes/Widgets
		// Add here list of php file(s) stored in wip/core/boxes that contains class to show a widget.
        $this->boxes = array(
//        	0 => array(
//				'file'=>'wipwidget1.php@wip',
//				'note'=>'Widget provided by WIP',
//				'enabledbydefaulton'=>'Home'),
        	//1=>array('file'=>'wipwidget2.php@wip','note'=>'Widget provided by WIP'),
        	//2=>array('file'=>'wipwidget3.php@wip','note'=>'Widget provided by WIP')
        );


		// Cronjobs (List of cron jobs entries to add when module is enabled)
		// unit_frequency must be 60 for minute, 3600 for hour, 86400 for day, 604800 for week
		$this->cronjobs = array(
			0 => array(
				'label'=>'MyJob label',
				'jobtype'=>'method',
				'class'=>'/wip/class/wiprequest.class.php',
				'objectname'=>'WIPRequest',
				'method'=>'doScheduledJob',
				'parameters'=>'',
				'comment'=>'Comment',
				'frequency'=>2,
				'unitfrequency'=>3600,
				'status'=>0,
				'test'=>'$conf->wip->enabled'
			)
		);
		// Example: $this->cronjobs=array(0=>array('label'=>'My label', 'jobtype'=>'method', 'class'=>'/dir/class/file.class.php', 'objectname'=>'MyClass', 'method'=>'myMethod', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>2, 'unitfrequency'=>3600, 'status'=>0, 'test'=>'$conf->wip->enabled'),
		//                                1=>array('label'=>'My label', 'jobtype'=>'command', 'command'=>'', 'parameters'=>'param1, param2', 'comment'=>'Comment', 'frequency'=>1, 'unitfrequency'=>3600*24, 'status'=>0, 'test'=>'$conf->wip->enabled')
		// );


		// Permissions
		$this->rights = array();		// Permission array used by this module

		$r=0;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Read report of WIP';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'read';				// In php code, permission will be checked by test if ($user->rights->wip->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->wip->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Create/Update report of WIP';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'write';				// In php code, permission will be checked by test if ($user->rights->wip->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->wip->level1->level2)

		$r++;
		$this->rights[$r][0] = $this->numero + $r;	// Permission id (must not be already used)
		$this->rights[$r][1] = 'Delete report of WIP';	// Permission label
		$this->rights[$r][3] = 1; 					// Permission by default for new user (0/1)
		$this->rights[$r][4] = 'delete';				// In php code, permission will be checked by test if ($user->rights->wip->level1->level2)
		$this->rights[$r][5] = '';				    // In php code, permission will be checked by test if ($user->rights->wip->level1->level2)


		// Main menu entries
		$this->menu = array();			// List of menus to add
		$r=0;

		// Add here entries to declare new menus

		/* BEGIN MODULEBUILDER TOPMENU */
/*		$this->menu[$r++]=array(
			'fk_menu'=>'',							// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'top',			                // This is a Top menu entry
			'titre'=>'WIP',
			'mainmenu'=>'wip',
			'leftmenu'=>'',
			'url'=>'/wip/wip.php?action=listall',
			'langs'=>'wip@wip',	        		// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>71,
			'enabled'=>'$conf->wip->enabled',		// Define condition to show or hide menu entry. Use '$conf->wip->enabled' if entry must be visible if module is enabled.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->wip->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);				                // 0=Menu for internal users, 1=external users, 2=both
*/
		/* END MODULEBUILDER TOPMENU */

		/* BEGIN MODULEBUILDER LEFTMENU WIP */
/*		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=wip',	    	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'WIP List',
			'mainmenu'=>'wip',
			'leftmenu'=>'wip',
			'url'=>'/custom/wip/wip.php?action=listall',
			'langs'=>'wip@wip',	        		// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1100+$r,
			'enabled'=>'$conf->wip->enabled',  	// Define condition to show or hide menu entry. Use '$conf->wip->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->wip->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both


		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=wip,fk_leftmenu=wip',	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'WIP to order',
			'mainmenu'=>'wip',
			'leftmenu'=>'wiptoorder',
			'url'=>'/custom/supplierorderfromorder/ordercustomer.php',
			'langs'=>'wip@wip',	        		// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>1100+$r,
			'enabled'=>'$conf->wip->enabled',		// Define condition to show or hide menu entry. Use '$conf->wip->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->wip->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both
*/
		/* END MODULEBUILDER LEFTMENU WIP */

		/* BEGIN LEFTMENU WIP REPORTS IN PROJECT */
		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project', 		// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'Reports',
			'mainmenu'=>'project',
			'leftmenu'=>'reports',
			'url'=>'/custom/wip/report_index.php?leftmenu=reports',
			'langs'=>'wip@wip',	        			// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>10+$r,
			'enabled'=>'$conf->wip->enabled',		// Define condition to show or hide menu entry. Use '$conf->wip->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',							// Use 'perms'=>'$user->rights->wip->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=reports',	    	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'New Report',
			'mainmenu'=>'project',
			'leftmenu'=>'reports_new',
			'url'=>'/custom/wip/report_card.php?action=create&leftmenu=reports',
			'langs'=>'parts@parts',	        		// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>10+$r,
			'enabled'=>'$conf->parts->enabled',  // Define condition to show or hide menu entry. Use '$conf->wip->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->wip->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		$this->menu[$r++]=array(
			'fk_menu'=>'fk_mainmenu=project,fk_leftmenu=reports',	// '' if this is a top menu. For left menu, use 'fk_mainmenu=xxx' or 'fk_mainmenu=xxx,fk_leftmenu=yyy' where xxx is mainmenucode and yyy is a leftmenucode
			'type'=>'left',							// This is a Left menu entry
			'titre'=>'List of Reports',
			'mainmenu'=>'report',
			'leftmenu'=>'reports_list',
			'url'=>'/custom/wip/report_list.php?leftmenu=reports',
			'langs'=>'wip@wips',	        		// Lang file to use (without .lang) by module. File must be in langs/code_CODE/ directory.
			'position'=>10+$r,
			'enabled'=>'$conf->wip->enabled',  	// Define condition to show or hide menu entry. Use '$conf->wip->enabled' if entry must be visible if module is enabled. Use '$leftmenu==\'system\'' to show if leftmenu system is selected.
			'perms'=>'1',			                // Use 'perms'=>'$user->rights->wip->level1->level2' if you want your menu with a permission rules
			'target'=>'',
			'user'=>2);								// 0=Menu for internal users, 1=external users, 2=both

		/* END LEFTMENU WIP REPORTS IN PROJECT */



		// Exports
		$r=1;

		/* BEGIN MODULEBUILDER EXPORT REPORT */
		/*
		$langs->load("wip@wip");
		$this->export_code[$r]=$this->rights_class.'_'.$r;
		$this->export_label[$r]='ReportLines';	// Translation key (used only if key ExportDataset_xxx_z not found)
		$this->export_icon[$r]='report@wip';
		$keyforclass = 'Report';
		$keyforclassfile='/mymobule/class/report.class.php';
		$keyforelement='report';
		include DOL_DOCUMENT_ROOT.'/core/commonfieldsinexport.inc.php';
		$keyforselect='report';
		$keyforaliasextra='extra';
		$keyforelement='report';
		include DOL_DOCUMENT_ROOT.'/core/extrafieldsinexport.inc.php';
		//$this->export_dependencies_array[$r]=array('mysubobject'=>'ts.rowid', 't.myfield'=>array('t.myfield2','t.myfield3')); // To force to activate one or several fields if we select some fields that need same (like to select a unique key if we ask a field of a child to avoid the DISTINCT to discard them, or for computed field than need several other fields)
		$this->export_sql_start[$r]='SELECT DISTINCT ';
		$this->export_sql_end[$r]  =' FROM '.MAIN_DB_PREFIX.'report as t';
		$this->export_sql_end[$r] .=' WHERE 1 = 1';
		$this->export_sql_end[$r] .=' AND t.entity IN ('.getEntity('report').')';
		$r++; */
		/* END MODULEBUILDER EXPORT REPORT */
	}

	/**
	 *	Function called when module is enabled.
	 *	The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 *	It also creates data directories
	 *
     *	@param      string	$options    Options when enabling module ('', 'noboxes')
	 *	@return     int             	1 if OK, 0 if KO
	 */
	public function init($options='')
	{
		$result=$this->_load_tables('/wip/sql/');
		$tmpentity = empty($conf->entity)?1:$conf->entity;
		if ($result < 0) return -1; // Do not activate module if not allowed errors found on module SQL queries (the _load_table run sql with run_sql with error allowed parameter to 'default')
		$sql = array(
			 "DELETE FROM ".MAIN_DB_PREFIX."document_model WHERE nom = '".$this->db->escape($this->const[0][2])."' AND type = 'wip_report' AND entity = ".$tmpentity,
			 "INSERT INTO ".MAIN_DB_PREFIX."document_model (nom, type, entity) VALUES('".$this->db->escape($this->const[0][2])."','wip_report',".$tmpentity.")"
		);


		// Create extrafields
		include_once DOL_DOCUMENT_ROOT.'/core/class/extrafields.class.php';
		$extrafields = new ExtraFields($this->db);

		//$result1=$extrafields->addExtraField('myattr1', "New Attr 1 label", 'boolean', 1,  3, 'thirdparty',   0, 0, '', '', 1, '', 0, 0, '', '', 'wip@wip', '$conf->wip->enabled');
		//$result2=$extrafields->addExtraField('myattr2', "New Attr 2 label", 'varchar', 1, 10, 'project',      0, 0, '', '', 1, '', 0, 0, '', '', 'wip@wip', '$conf->wip->enabled');
		//$result3=$extrafields->addExtraField('myattr3', "New Attr 3 label", 'varchar', 1, 10, 'bank_account', 0, 0, '', '', 1, '', 0, 0, '', '', 'wip@wip', '$conf->wip->enabled');
		//$result4=$extrafields->addExtraField('myattr4', "New Attr 4 label", 'select',  1,  3, 'thirdparty',   0, 1, '', array('options'=>array('code1'=>'Val1','code2'=>'Val2','code3'=>'Val3')), 1 '', 0, 0, '', '', 'wip@wip', '$conf->wip->enabled');
		//$result5=$extrafields->addExtraField('myattr5', "New Attr 5 label", 'text',    1, 10, 'user',         0, 0, '', '', 1, '', 0, 0, '', '', 'wip@wip', '$conf->wip->enabled');

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
