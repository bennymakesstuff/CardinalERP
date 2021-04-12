<?php
/* Copyright (C) 2010-2014	Regis Houssin	<regis.houssin@inodbox.com>
 * Copyright (C) 2014       Marcos García   <marcosgdf@gmail.com>
 * Copyright (C) 2018		Peter Roberts			<webmaster@finchmc.com.au>
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
 * or see http://www.gnu.org/
 */

/**
 *		\file       htdocs/custom/wip/core/modules/modules_wip_report.php
 *      \ingroup    project
 *      \brief      File that contains parent class for WIP Reports models
 *                  and parent class for WIP Reports numbering models
 */
require_once DOL_DOCUMENT_ROOT.'/core/class/commondocgenerator.class.php';
//require_once DOL_DOCUMENT_ROOT.'/compta/bank/class/account.class.php';	// required for use by classes that inherit


/**
 *	Parent class for reports models
 */
abstract class ModelePDFReport extends CommonDocGenerator
{
	/**
	 * @var string Error code (or message)
	 */
	public $error='';


    // phpcs:disable PEAR.NamingConventions.ValidFunctionName.NotCamelCaps
	/**
	 *  Return list of active generation models
	 *
     *  @param	DoliDB	$db     			Database handler
     *  @param  integer	$maxfilenamelength  Max length of value to show
     *  @return	array						List of templates
	 */
	static function liste_modeles($db,$maxfilenamelength=0)
	{
		// phpcs:enable
		global $conf;

		$type='wip_report';
		$liste=array();

		include_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';
		$liste=getListOfModels($db,$type,$maxfilenamelength);

		return $liste;
	}
}



/**
 *	Parent Class of numbering models of WIP Report references
 */
abstract class ModeleNumRefWipReports
{
	/**
	 * @var string Error code (or message)
	 */
	public $error='';

	/**  Return if a model can be used or not
	 *
	 *   @return	boolean     true if model can be used
	 */
	function isEnabled()
	{
		return true;
	}

	/**  Returns default description of numbering model
	 *
	 *   @return    string      Description Text
	 */
	function info()
	{
		global $langs;
		$langs->load("wip");
		return $langs->trans("NoDescription");
	}

	/**   Returns a numbering example
	 *
	 *    @return   string      Example
	 */
	function getExample()
	{
		global $langs;
		$langs->load("wip");
		return $langs->trans("NoExample");
	}

	/**  Tests if existing numbers make problems with numbering
	 *
	 *   @return	boolean     false if conflict, true if ok
	 */
	function canBeActivated()
	{
		return true;
	}

	/**  Returns next value assigned
	 *
	 *   @return     string      Valeur
	 */
	function getNextValue()
	{
		global $langs;
		return $langs->trans("NotAvailable");
	}

	/**   Returns version of the numbering model
	 *
	 *    @return     string      Value
	 */
	function getVersion()
	{
		global $langs;
		$langs->load("admin");

		if ($this->version == 'development') return $langs->trans("VersionDevelopment");
		if ($this->version == 'experimental') return $langs->trans("VersionExperimental");
		if ($this->version == 'dolibarr') return DOL_VERSION;
		if ($this->version) return $this->version;
		return $langs->trans("NotAvailable");
	}
}
