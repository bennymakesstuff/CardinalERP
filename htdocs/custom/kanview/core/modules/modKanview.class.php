<?php

/*
 * Copyright (C) 2018 ProgSI (contact@progsi.ma)
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

// __LOCKED__

/**
 * ***************************************************************
 * +++0+++ 2018051418321679
 * ***************************************************************
 */
/**
 * \defgroup kanview Module Kanview
 * \brief 'Ce module gère des vues Kanban'
 * \file 
 * \ingroup kanview
 * \brief Description and activation file for module Kanview
 */
include_once dirname(dirname(__DIR__)) . '/master.inc.php';
require_once DOL_DOCUMENT_ROOT . '/core/modules/DolibarrModules.class.php';

/**
 * Description and activation class for module Kanview
 */
class modKanview extends DolibarrModules {

	static $build = '183216818';

	static $released = true;

	/**
	 * Constructor.
	 * Define names, constants, directories, boxes, permissions
	 *
	 * @param DoliDB $db
	 *        	Database handler
	 */
	public function __construct($db) {
		global $langs, $conf;
		
		$this->db = $db;

		// Id for module
		$this->numero				 = 125032;
		// Key text used to identify module (for permissions, menus, etc...)
		$this->rights_class	 = 'kanview';

		// Family
		$this->family = 'technic';

		// $this->familyinfo = array('technic' => array('position' => '100', 'label' => $langs->trans('technic')));
		// Module position in the family
		$this->module_position = 100;

		// Module label
		$this->name					 = 'kanview'; // preg_replace('/^mod/i', '', get_class($this));
		// Module description
		$this->description	 = 'Module125032Desc';
		// editor name 
		$this->editor_name	 = 'ProgSI';
		// editor url
		$this->editor_url		 = 'https://progsi.ma';
		// editor email
		$this->editor_email	 = 'contact@progsi.ma';

		// version
		$this->version = '2.0.0';

		// Key used in llx_const table to save module status enabled/disabled (where KANVIEW is value of property name of module in uppercase)
		$this->const_name	 = 'MAIN_MODULE_KANVIEW'; // 'MAIN_MODULE_' . strtoupper($this->name);
		// Where to store the module in setup page of Dolibarr (0=common,1=interface,2=others,3=very specific)
		$this->special		 = 0;

		// Name of image file used for this module.
		$this->picto = 'kanview@kanview';

		// myname
		$conf->thisname = $this->name;
		
		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts	 = array(
				'triggers'			 => 0,
				'login'					 => 0,
				'substitutions'	 => 0,
				'menus'					 => 0,
				'theme'					 => 0,
				'tpl'						 => 0,
				'barcode'				 => 0,
				'models'				 => 0,
				'css'						 => array(),
				'js'						 => array(),
				'hooks'					 => array(
				),
				'dir'						 => array(
				),
				'workflow'			 => array(
		)); // end module_parts
		// Data directories to create
		$this->dirs					 = array(
		);

		// Config pages
		$this->config_page_url = array(
				'kanview_config.php@kanview');

		// Lang files
		$this->langfiles = array('kanview@kanview');

		// Dependencies
		// A condition to hide module (php code that return a boolean value)
		$this->hidden				 = 0;
		// List of modules id that must be enabled if this module is enabled
		$this->depends			 = array(
		);
		// List of modules id to disable if this one is disabled
		$this->requiredby		 = array(
		);
		// List of modules id this module is in conflict with
		$this->conflictwith	 = array(
		);

		// Minimum version of PHP
		$this->phpmin								 = array(
				5, 5);
		// Minimum version of Dolibarr
		$this->need_dolibarr_version = array(
				5, 0);

		// constants
		$this->const = array(
				0	 => array('KANVIEW_HOME_PAGE', 'string', 'projets', 'KANVIEW_HOME_PAGE', '0', '0', '0',),
				
				1	 => array('KANVIEW_PROJETS_TAG', 'string', 'opp_amount', 'KANVIEW_PROJETS_TAG', '0', '0', '0',),
				2	 => array('KANVIEW_PROJETS_DRAFT_COLOR', 'string', '#46c6f4', 'KANVIEW_PROJETS_DRAFT_COLOR', '0', '0', '0',),
				3	 => array('KANVIEW_PROJETS_OPEN_COLOR', 'string', '#73bf44', 'KANVIEW_PROJETS_OPEN_COLOR', '0', '0', '0',),
				4	 => array('KANVIEW_PROJETS_CLOSED_COLOR', 'string', '#ff0000', 'KANVIEW_PROJETS_CLOSED_COLOR', '0', '0', '0',),
				
				5	 => array('KANVIEW_TASKS_TAG', 'string', 'task_period', 'KANVIEW_TASKS_TAG', '0', '0', '0',),
				6	 => array('KANVIEW_TASKS_OK_COLOR', 'string', '#73bf44', 'KANVIEW_TASKS_OK_COLOR', '0', '0', '0',),
				7	 => array('KANVIEW_TASKS_LATE1_COLOR', 'string', '#f7991d', 'KANVIEW_TASKS_LATE1_COLOR', '0', '0', '0',),
				8	 => array('KANVIEW_TASKS_LATE2_COLOR', 'string', '#ff0000', 'KANVIEW_TASKS_LATE2_COLOR', '0', '0', '0',),
				
				9	 => array('KANVIEW_PROPALS_TAG', 'string', 'datep', 'KANVIEW_PROPALS_TAG', '0', '0', '0',),
				
				10 => array('KANVIEW_INVOICES_TAG', 'string', 'datef', 'KANVIEW_INVOICES_TAG', '0', '0', '0',),
				
				11 => array('KANVIEW_PROSPECTS_TAG', 'string', 'prospectlevel_label', 'KANVIEW_PROSPECTS_TAG', '0', '0', '0',),
				12 => array('KANVIEW_PROSPECTS_PL_HIGH_COLOR', 'string', '#73bf44', 'KANVIEW_PROSPECTS_PL_HIGH_COLOR', '0', '0', '0',),
				13 => array('KANVIEW_PROSPECTS_PL_LOW_COLOR', 'string', '#b76caa', 'KANVIEW_PROSPECTS_PL_LOW_COLOR', '0', '0', '0',),
				14 => array('KANVIEW_PROSPECTS_PL_MEDIUM_COLOR', 'string', '#f7991d', 'KANVIEW_PROSPECTS_PL_MEDIUM_COLOR', '0', '0', '0',),
				15 => array('KANVIEW_PROSPECTS_PL_NONE_COLOR', 'string', '#ff0000', 'KANVIEW_PROSPECTS_PL_NONE_COLOR', '0', '0', '0',),
				
				16 => array('KANVIEW_ORDERS_TAG', 'string', 'amount_ht', 'KANVIEW_ORDERS_TAG', '0', '0', '0',),
				17 => array('KANVIEW_ORDERS_LATE1_COLOR', 'string', '#b76caa', 'KANVIEW_ORDERS_LATE1_COLOR', '0', '0', '0',),
				18 => array('KANVIEW_ORDERS_LATE2_COLOR', 'string', '#f7991d', 'KANVIEW_ORDERS_LATE2_COLOR', '0', '0', '0',),
				19 => array('KANVIEW_ORDERS_LATE3_COLOR', 'string', '#ff0000', 'KANVIEW_ORDERS_LATE3_COLOR', '0', '0', '0',),
				
				20 => array('KANVIEW_INVOICES_LATE1_COLOR', 'string', '#b76caa', 'KANVIEW_INVOICES_LATE1_COLOR', '0', '0', '0',),
				21 => array('KANVIEW_INVOICES_LATE2_COLOR', 'string', '#f7991d', 'KANVIEW_INVOICES_LATE2_COLOR', '0', '0', '0',),
				22 => array('KANVIEW_INVOICES_LATE3_COLOR', 'string', '#ff0000', 'KANVIEW_INVOICES_LATE3_COLOR', '0', '0', '0',), 
				
				23 => array('KANVIEW_SHOW_PICTO', 'yesno', '1', 'KANVIEW_SHOW_PICTO', '0', '0', '0',),
				
				24 => array('KANVIEW_PROPALS_LATE1_COLOR', 'string', '#46c6f4', 'KANVIEW_PROPALS_LATE1_COLOR', '0', '0', '0',),
				25 => array('KANVIEW_PROPALS_LATE2_COLOR', 'string', '#f7991d', 'KANVIEW_PROPALS_LATE2_COLOR', '0', '0', '0',),
				26 => array('KANVIEW_PROPALS_LATE3_COLOR', 'string', '#b76c99', 'KANVIEW_PROPALS_LATE3_COLOR', '0', '0', '0',),
				27 => array('KANVIEW_PROPALS_LATE4_COLOR', 'string', '#ff0000', 'KANVIEW_PROPALS_LATE4_COLOR', '0', '0', '0',),
				
				28 => array('KANVIEW_INVOICES_SUPPLIERS_TAG', 'string', 'datef', 'KANVIEW_INVOICES_SUPPLIERS_TAG', '0', '0', '0',),
				29 => array('KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR', 'string', '#b76caa', 'KANVIEW_INVOICES_SUPPLIERS_LATE1_COLOR', '0', '0', '0',),
				30 => array('KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR', 'string', '#f7991d', 'KANVIEW_INVOICES_SUPPLIERS_LATE2_COLOR', '0', '0', '0',),
				31 => array('KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR', 'string', '#ff0000', 'KANVIEW_INVOICES_SUPPLIERS_LATE3_COLOR', '0', '0', '0',), 
			);

		// tabs
		$this->tabs = array(
		);

		// Dictionaries
		// This is to avoid warnings
		if (!isset($conf->kanview->enabled)) {
			$conf->kanview					 = new stdClass();
			$conf->kanview->enabled	 = 0;
		}
		// dicos
		$this->dictionaries = array(
		);

		// boxes
		$this->boxes = array(
		);

		// Cronjobs
		$this->cronjobs = array(
		);

		// Permissions
		$this->rights = array(
				0	 => array(
						0	 => '125032002',
						1	 => 'Rights_CanUseKanview',
						2	 => 'w',
						3	 => $conf->kanview->enabled, 
						4	 => 'canuse', 
						5	 => '',),
				1	 => array(
						0	 => '125032003',
						1	 => 'Rights_CanUseKanview_Projects',
						2	 => 'w',
						3	 => ($conf->kanview->enabled), 
						4	 => 'kanview_advance', 
						5	 => 'canuse_projects',),
				2	 => array(
						0	 => '125032004',
						1	 => 'Rights_CanUseKanview_Tasks',
						2	 => 'w',
						3	 => ($conf->kanview->enabled), 
						4	 => 'kanview_advance', 
						5	 => 'canuse_tasks',),
				3	 => array(
						0	 => '125032005',
						1	 => 'Rights_CanUseKanview_Propals',
						2	 => 'w',
						3	 => ($conf->kanview->enabled),
						4	 => 'kanview_advance', 
						5	 => 'canuse_propals',),
				4	 => array(
						0	 => '125032006',
						1	 => 'Rights_CanUseKanview_Orders',
						2	 => 'w',
						3	 => ($conf->kanview->enabled), 
						4	 => 'kanview_advance', 
						5	 => 'canuse_orders',),
				5	 => array(
						0	 => '125032007',
						1	 => 'Rights_CanUseKanview_Invoices',
						2	 => 'w',
						3	 => ($conf->kanview->enabled), 
						4	 => 'kanview_advance', 
						5	 => 'canuse_invoices',),
				6	 => array(
						0	 => '125032008',
						1	 => 'Rights_CanUseKanview_Prospects',
						2	 => 'w',
						3	 => ($conf->kanview->enabled), 
						4	 => 'kanview_advance', 
						5	 => 'canuse_prospects',),
				7	 => array(
						0	 => '125032009',
						1	 => 'Rights_CanUseKanview_InvoicesSuppliers',
						2	 => 'w',
						3	 => ($conf->kanview->enabled), 
						4	 => 'kanview_advance', 
						5	 => 'canuse_invoices_suppliers',),
		);

		// Main menu entries
		$fk_invoices_mainmenu = '';
		$fk_invoices_leftmenu = '';
		if(DOL_VERSION < '6.0.0'){
			$fk_invoices_mainmenu = 'accountancy';
			$fk_invoices_leftmenu = 'customers_bills';
		}elseif(DOL_VERSION < '7.0.0'){
			$fk_invoices_mainmenu = 'accountancy';
			$fk_invoices_leftmenu = 'customers_bills_list';
		}else{
			$fk_invoices_mainmenu = 'billing';
			$fk_invoices_leftmenu = 'customers_bills_list';
		}
		
		$this->menu = array(
				// menu dashboard (top menu)
				0	 => array(
						'fk_menu'	 => '0',
						'type'		 => 'top',
						'titre'		 => 'Kanview_TopMenu_Dashboard',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/kanview_board.php',
						'langs'		 => 'kanview@kanview',
						'position' => '100',
						'enabled'	 => '$conf->kanview->enabled',
						'perms'		 => '$user->rights->kanview->canuse',
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu projects
				1	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_Projet_Kanban',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/projets_kb.php',
						'langs'		 => 'kanview@kanview',
						'position' => '200',
						'enabled'	 => '$conf->kanview->enabled && $conf->projet->enabled',
						'perms'		 => hasPermissionForKanbanView('projets', true),
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu tasks
				2	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_ProjetTask_Kanban',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/tasks_kb.php',
						'langs'		 => 'kanview@kanview',
						'position' => '250',
						'enabled'	 => '$conf->kanview->enabled && $conf->projet->enabled',
						'perms'		 => hasPermissionForKanbanView('tasks', true),
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu propals
				3	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_Propal_Kanban',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/propals_kb.php',
						'langs'		 => 'kanview@kanview',
						'position' => '300',
						'enabled'	 => '$conf->kanview->enabled && $conf->propal->enabled',
						'perms'		 => hasPermissionForKanbanView('propals', true),
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu orders
				4	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_Commande_Kanban',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/orders_kb.php',
						'langs'		 => 'kanview@kanview',
						'position' => '400',
						'enabled'	 => '$conf->kanview->enabled && $conf->commande->enabled',
						'perms'		 => hasPermissionForKanbanView('orders', true),
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu invoices
				5	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_Facture_Kanban',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/invoices_kb.php',
						'langs'		 => 'kanview@kanview',
						'position' => '500',
						'enabled'	 => '$conf->kanview->enabled && $conf->facture->enabled',
						'perms'		 => hasPermissionForKanbanView('invoices', true),
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu prospects
				6	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_Societe_Kanban',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/prospects_kb.php',
						'langs'		 => 'kanview@kanview',
						'position' => '600',
						'enabled'	 => '$conf->kanview->enabled && $conf->societe->enabled',
						'perms'		 => hasPermissionForKanbanView('prospects', true),
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu suppliers invoices
				7	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_FactureFournisseur_Kanban',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/view/invoices_suppliers_kb.php',
						'langs'		 => 'kanview@kanview',
						'position' => '700',
						'enabled'	 => '$conf->kanview->enabled && $conf->fournisseur->enabled',
						'perms'		 => hasPermissionForKanbanView('invoices_suppliers', true),
						'target'	 => '',
						'user'		 => 0,
				),
				
				// left menu parameters
				8	 => array(
						'fk_menu'	 => 'r=0',
						'type'		 => 'left',
						'titre'		 => 'Kanview_LeftMenu_Config',
						'mainmenu' => 'kanview',
						'leftmenu' => '',
						'url'			 => '/kanview/admin/kanview_config.php',
						'langs'		 => 'kanview@kanview',
						'position' => '800',
						'enabled'	 => '$conf->kanview->enabled && $user->admin',
						'perms'		 => '$user->admin',
						'target'	 => '_blank',
						'user'		 => 0,
				),
				
				// ------ entrées de menus gauches dans les modules natifs Dolibarr
				// -- certainnes entrées peuvent ne pas fonctionner pour certaines versions dolibarr (en particulier Factures et Tâches)
				// -- project - projects
//				9	 => array(
//						'fk_menu'	 => 'fk_mainmenu=project,fk_leftmenu=projects',	
//						'mainmenu' => 'project',
//						'leftmenu' => 'projects',
//						'type'		 => 'left',
//						'titre'		 => 'Kanview_LeftMenu_ProjectInProjects',
//						'url'			 => '/kanview/view/projets_kb.php',
//						'langs'		 => 'kanview@kanview',
//						'position' => '800',
//						'enabled'	 => '$conf->kanview->enabled && $conf->projet->enabled',
//						'perms'		 => hasPermissionForKanbanView('projets', true),
//						'target'	 => '',
//						'user'		 => 0,
//				),
//				
//				// -- project - tasks (ici, on met les taches avec les projets parce que le sous-menus Taches de Dolibarr ne permet pas l'ajout d'autres optons par les modules externes (jusqu'à version dolibarr 8.0.0 au moins))
//				10	 => array(
//						'fk_menu'	 => 'fk_mainmenu=project,fk_leftmenu=projects',
//						'mainmenu' => 'project',
//						'leftmenu' => 'projects',
//						'type'		 => 'left',
//						'titre'		 => 'Kanview_LeftMenu_TasksInProjects',
//						'url'			 => '/kanview/view/tasks_kb.php',
//						'langs'		 => 'kanview@kanview',
//						'position' => '810',
//						'enabled'	 => '$conf->kanview->enabled && $conf->projet->enabled',
//						'perms'		 => hasPermissionForKanbanView('tasks', true),
//						'target'	 => '',
//						'user'		 => 0,
//				),
//				
//				// -- commercial - propals
//				11	 => array(
//						'fk_menu'	 => 'fk_mainmenu=commercial,fk_leftmenu=propals',
//						'mainmenu' => 'commercial',
//						'leftmenu' => 'propals',
//						'type'		 => 'left',
//						'titre'		 => 'Kanview_LeftMenu_InOtherModules',
//						'url'			 => '/kanview/view/propals_kb.php',
//						'langs'		 => 'kanview@kanview',
//						'position' => '820',
//						'enabled'	 => '$conf->kanview->enabled && $conf->propal->enabled',
//						'perms'		 => hasPermissionForKanbanView('propals', true),
//						'target'	 => '',
//						'user'		 => 0,
//				),
//				
//				// -- commercial - orders
//				12	 => array(
//						'fk_menu'	 => 'fk_mainmenu=commercial,fk_leftmenu=orders',
//						'mainmenu' => 'commercial',
//						'leftmenu' => 'orders',
//						'type'		 => 'left',
//						'titre'		 => 'Kanview_LeftMenu_InOtherModules',
//						'url'			 => '/kanview/view/orders_kb.php',
//						'langs'		 => 'kanview@kanview',
//						'position' => '830',
//						'enabled'	 => '$conf->kanview->enabled && $conf->commande->enabled',
//						'perms'		 => hasPermissionForKanbanView('orders', true),
//						'target'	 => '',
//						'user'		 => 0,
//				),
//				
//				// -- accountancy - customers_bills (peut fonctionner à partir de la version dolibarr 5.0.2)
//				13	 => array(
//						'fk_menu'	 => 'fk_mainmenu=' . $fk_invoices_mainmenu . ',fk_leftmenu=' . $fk_invoices_leftmenu,
//						'mainmenu' => $fk_invoices_mainmenu,
//						'leftmenu' => $fk_invoices_leftmenu,
//						'type'		 => 'left',
//						'titre'		 => 'Kanview_LeftMenu_InOtherModules',
//						'url'			 => '/kanview/view/invoices_kb.php',
//						'langs'		 => 'kanview@kanview',
//						'position' => '840',
//						'enabled'	 => '$conf->kanview->enabled && $conf->facture->enabled',
//						'perms'		 => hasPermissionForKanbanView('invoices', true),
//						'target'	 => '',
//						'user'		 => 0,
//				),
//			
//				// -- companies - prospects
//				14	 => array(
//						'fk_menu'	 => 'fk_mainmenu=companies,fk_leftmenu=prospects',
//						'mainmenu' => 'companies',
//						'leftmenu' => 'prospects',
//						'type'		 => 'left',
//						'titre'		 => 'Kanview_LeftMenu_InOtherModules',
//						'url'			 => '/kanview/view/prospects_kb.php',
//						'langs'		 => 'kanview@kanview',
//						'position' => '850',
//						'enabled'	 => '$conf->kanview->enabled && $conf->societe->enabled',
//						'perms'		 => hasPermissionForKanbanView('prospects', true),
//						'target'	 => '',
//						'user'		 => 0,
//				),
//				
//				// -- accountancy - suppliers_bills (peut fonctionner à partir de la version dolibarr 5.0.2)
//				13	 => array(
//						'fk_menu'	 => 'fk_mainmenu=accountancy,fk_leftmenu=suppliers_bills',
//						'mainmenu' => 'accountancy',
//						'leftmenu' => 'suppliers_bills',
//						'type'		 => 'left',
//						'titre'		 => 'Kanview_LeftMenu_InOtherModules',
//						'url'			 => '/kanview/view/invoices_suppliers_kb.php',
//						'langs'		 => 'kanview@kanview',
//						'position' => '860',
//						'enabled'	 => '$conf->kanview->enabled && $conf->fournisseur->enabled',
//						'perms'		 => hasPermissionForKanbanView('invoices_suppliers', true),
//						'target'	 => '',
//						'user'		 => 0,
//				),
				
		);

		// ----------------------------------- Exports

		$this->export_code							 = array(
		);
		$this->export_label							 = array(
		);
		$this->export_icon							 = array(
		);
		// export_entities_array : We define here only fields that use another icon that the one defined into export_icon
		$this->export_entities_array		 = array(
		);
		$this->export_enabled						 = array(
		);
		$this->export_sql_start					 = array(
		);
		$this->export_sql_end						 = array(
		);
		$this->export_sql_order					 = array(
		);
		$this->export_permission				 = array(
		);
		$this->export_fields_array			 = array(
		);
		$this->export_TypeFields_array	 = array(
		);
		$this->export_dependencies_array = array(
		);
		// END exports
		// ----------------------------------------------- Imports

		$this->import_code								 = array(
				// $this->rights_class.'_'.$r,
		);
		$this->import_label								 = array(
				// "Products", // Translation key
		);
		$this->import_icon								 = array(
				// $this->picto,
		);
		// We define here only fields that use another icon that the one defined into import_icon
		$this->import_entities_array			 = array(
				// array(),
		);
		$this->import_tables_array				 = array(
				// array('p'=>MAIN_DB_PREFIX.'product','extra'=>MAIN_DB_PREFIX.'product_extrafields')
		);
		// import_tables_creator_array : Fields to store import user id
		$this->import_tables_creator_array = array(
				// array('p'=>'fk_user_author')
		);
		$this->import_fields_array				 = array(
				// array('p.ref'=>"Ref*",'p.label'=>"Label*",'p.description'=>"Description",'p.url'=>"PublicUrl",'p.accountancy_code_sell'=>"ProductAccountancySellCode",'p.accountancy_code_buy'=>"ProductAccountancyBuyCode",'p.note'=>"Note",'p.length'=>"Length",'p.surface'=>"Surface",'p.volume'=>"Volume",'p.weight'=>"Weight",'p.duration'=>"Duration",'p.customcode'=>'CustomCode','p.price'=>"SellingPriceHT",'p.price_ttc'=>"SellingPriceTTC",'p.tva_tx'=>'VAT','p.tosell'=>"OnSell*",'p.tobuy'=>"OnBuy*",'p.fk_product_type'=>"Type*",'p.finished'=>'Nature','p.datec'=>'DateCreation')
		);

		// TODO : gérer l'import des extrafields
		// Add extra fields
		$import_extrafield_sample				 = array();
		/*
		 * $sql="SELECT name, label, fieldrequired FROM ".MAIN_DB_PREFIX."extrafields WHERE elementtype = 'product' AND entity IN (0, ".$conf->entity.')';
		 * $resql=$this->db->query($sql);
		 * if ($resql) // This can fail when class is used on old database (during migration for example)
		 * {
		 * while ($obj=$this->db->fetch_object($resql))
		 * {
		 * $fieldname='extra.'.$obj->name;
		 * $fieldlabel=ucfirst($obj->label);
		 * $this->import_fields_array[$r][$fieldname]=$fieldlabel.($obj->fieldrequired?'*':'');
		 * $import_extrafield_sample[$fieldname]=$fieldlabel;
		 * }
		 * }
		 */
		// End add extra fields
		// aliastable.field => ('user->id' or 'lastrowid-'.tableparent)
		$this->import_fieldshidden_array = array(
				// array('extra.fk_object'=>'lastrowid-'.MAIN_DB_PREFIX.'product')
		);
		$this->import_regex_array				 = array(
				// array('p.ref'=>'[^ ]','p.tosell'=>'^[0|1]$','p.tobuy'=>'^[0|1]$','p.fk_product_type'=>'^[0|1]$','p.datec'=>'^[0-9][0-9][0-9][0-9]-[0-9][0-9]-[0-9][0-9]$','p.recuperableonly'=>'^[0|1]$'),
		);
		$import_sample									 = array(
				// array('p.ref'=>"PREF123456",'p.label'=>"My product",'p.description'=>"This is a description example for record",'p.note'=>"Some note",'p.price'=>"100",'p.price_ttc'=>"110",'p.tva_tx'=>'10','p.tosell'=>"0 or 1",'p.tobuy'=>"0 or 1",'p.fk_product_type'=>"0 for product/1 for service",'p.finished'=>'','p.duration'=>"1y",'p.datec'=>'2008-12-31','p.recuperableonly'=>'0 or 1'),
		);
		$count1													 = count($import_sample);
		$count2													 = count($import_extrafield_sample);
		if ($count1 > 0 && $count2 > 0 && $count1 === $count2) {
			for ($i = 0; $i < $count1; $i ++) {
				$this->import_examplevalues_array[$i] = array_merge($import_sample[$i], $import_extrafield_sample[$i]);
			}
		}
		
		// TODO : gérer l'import MultiLangues
		if (!empty($conf->global->MAIN_MULTILANGS)) {
			$r ++;
			/*
			 * FIXME Must be a dedicated import profil. Not working yet
			 * $this->import_code[$r]=$this->rights_class.'_multiprice';
			 * $this->import_label[$r]="ProductTranslations";
			 * $this->import_icon[$r]=$this->picto;
			 * $this->import_entities_array[$r]=array(); // We define here only fields that use another icon that the one defined into import_icon
			 * $this->import_tables_array[$r]['l']=MAIN_DB_PREFIX.'product_lang';
			 * // multiline translation, one line per translation
			 * $this->import_fields_array[$r]['l.lang']='Language';
			 * $this->import_fields_array[$r]['l.label']='TranslatedLabel';
			 * $this->import_fields_array[$r]['l.description']='TranslatedDescription';
			 * $this->import_fields_array[$r]['l.note']='TranslatedNote';
			 * $this->import_examplevalues_array[$r]['l.lang']='en_US';
			 */
			// single line translation, one column per translation
			/*
			 * foreach($langs as $l) {
			 * $this->import_tables_array[$r][$l] = MAIN_DB_PREFIX.'product_lang';
			 * $this->import_fields_array[$r][$l.'.label']=$l.'_label';
			 * $this->import_fields_array[$r][$l.'.description']=$l.'_description';
			 * $this->import_fields_array[$r][$l.'.note']=$l.'_note';
			 * $this->import_fieldshidden_array[$r][$l.'.lang']="'$l'";
			 * $this->import_fieldshidden_array[$r][$l.'.fk_product']='lastrowid-'.MAIN_DB_PREFIX.'product';
			 * }
			 */
		}
		// END Import
		// Can be enabled / disabled only in the main company when multi-company is in use
		// $this->core_enabled = __CORE_ENABLED__;
	}

	// end constructor

	/**
	 * Function called when module is enabled.
	 * The init function add constants, boxes, permissions and menus (defined in constructor) into Dolibarr database.
	 * It also creates data directories
	 *
	 * @param string $options
	 *        	Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function init($options = '') {
		$sql		 = array();
		$repSQL	 = '/kanview/sql/';

		// code sql additionnel, une requete par ligne sous-format : $sql[] = 'requete sql'	
		return $this->_init($sql, $options);
	}

	/**
	 * Function called when module is disabled.
	 * Remove from database constants, boxes and permissions from Dolibarr database.
	 * Data directories are not deleted
	 *
	 * @param string $options
	 *        	Options when enabling module ('', 'noboxes')
	 * @return int 1 if OK, 0 if KO
	 */
	public function remove($options = '') {
		$sql = array();
		
		// nettoyage de la bdd

		return $this->_remove($sql, $options);
	}

}
