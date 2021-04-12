<?php
/* Copyright (C) 2010-2018 Regis Houssin  <regis.houssin@capnetworks.com>
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
 *	\defgroup   milestone       Milestone module
 *	\brief      Module to manage milestones
 *	\file       htdocs/core/modules/modMilestone.class.php
 *	\ingroup    milestone
 *	\brief      Fichier de description et activation du module Milestone
 */
include_once DOL_DOCUMENT_ROOT . "/core/modules/DolibarrModules.class.php";

/**
 *	\class      modMilestone
 *	\brief      Classe de description et activation du module Milestone
 */
class modMilestone extends DolibarrModules
{
	/**
	 *	Constructor
	 *
	 *	@param	DB	handler d'acces base
	 */
	function __construct($db)
	{
		$this->db = $db;
		$this->numero = 1790;

		$this->family = "technic";
		// Module label (no space allowed), used if translation string 'ModuleXXXName' not found (where XXX is value of numeric property 'numero' of module)
		$this->name = preg_replace('/^mod/i','',get_class($this));
		$this->description = "Gestion des jalons (projets, contrats, propales, ...)";

		// Possible values for version are: 'development', 'experimental', 'dolibarr' or version
		$this->version = '8.0+2.3.0';

		$this->const_name = 'MAIN_MODULE_'.strtoupper($this->name);
		$this->picto = 'milestone@milestone';

		// Data directories to create when module is enabled
		$this->dirs = array();

		// Dependencies
		$this->depends = array();		// List of modules id that must be enabled if this module is enabled
		$this->requiredby = array();	// List of modules id to disable if this one is disabled
		$this->conflictwith = array('modSubtotal');	// List of module class names as string this module is in conflict with
		$this->phpmin = array(5,3);					// Minimum version of PHP required by module
		$this->need_dolibarr_version = array(5,0);	// Minimum version of Dolibarr required by module

		// Config pages
		$this->config_page_url = array('milestone.php@milestone');
		$this->langfiles = array('milestone@milestone');

		// Defined all module parts (triggers, login, substitutions, menus, css, etc...)
		$this->module_parts = array(
				'triggers' => 1,
				'hooks' => array(
						'propalcard',
						'ordercard',
						'invoicecard',
						'pdf_getlineunit'
						//'ordersuppliercard',
						//'invoicesuppliercard'
				)
		);

		// Constantes
		$this->const=array(
				1 => array('MAIN_FORCE_RELOAD_PAGE',"chaine",1,'',0),
				2 => array('MAIN_PDF_DASH_BETWEEN_LINES',"yesno",0,'Add dash between lines',0),
				3 => array('MILESTONE_MAIN_VERSION','chaine',$this->version,'',0,'multicompany',1)
		);

		// Boxes
		$this->boxes = array();

		// Permissions
		$this->rights = array();
		$this->rights_class = 'milestone';

		$r=0;

		$this->rights[$r][0] = 1791; // id de la permission
		$this->rights[$r][1] = 'Read milestones'; // libelle de la permission
		$this->rights[$r][2] = 'r'; // type de la permission (deprecated)
		$this->rights[$r][3] = 1; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'lire';
		$r++;

		$this->rights[$r][0] = 1792; // id de la permission
		$this->rights[$r][1] = 'Create/update milestones'; // libelle de la permission
		$this->rights[$r][2] = 'w'; // type de la permission (deprecated)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'creer';
		$r++;

		$this->rights[$r][0] = 1793; // id de la permission
		$this->rights[$r][1] = 'Delete milestones'; // libelle de la permission
		$this->rights[$r][2] = 'd'; // type de la permission (deprecated)
		$this->rights[$r][3] = 0; // La permission est-elle une permission par defaut
		$this->rights[$r][4] = 'supprimer';
		$r++;

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
		dol_include_once('/milestone/lib/milestone.lib.php');

		$sql = array();

		// Check current version
		if (!checkMilestoneVersion()) {
			$table = $this->db->DDLListTables($this->db->database_name, MAIN_DB_PREFIX."milestone");
			if (empty($table))
			{
				$sql = array(
					"INSERT INTO ".MAIN_DB_PREFIX."const (name,value,type,visible,note,entity) VALUES ('".$this->db->encrypt('MILESTONE_LAST_UPGRADE')."','".$this->db->encrypt($this->version)."','chaine',0,'Milestone last upgrade version',0)"
				);
			}
		}

		return $this->_init($sql);
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
		$sql = array(
			"DELETE FROM ".MAIN_DB_PREFIX."const WHERE name = ".$this->db->encrypt('MILESTONE_LAST_UPGRADE', 1)
		);

		return $this->_remove($sql);
	}

}
