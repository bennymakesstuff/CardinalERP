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
 *	\file       htdocs/milestone/class/dao_milestone.class.php
 *	\ingroup    milestone
 *	\brief      Fichier de la classe des jalons
 */

include_once DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';


/**
 *	\class      DaoMilestone
 *	\brief      Classe permettant la gestion des jalons
 */
class DaoMilestone extends CommonObject
{
	var $db;
	var $error;

	var $id;
	var $label;
	var $options=array();

	var $fk_element;
	var $elementtype;

	var $rang;
	var $rangtouse;

	var $lines=array();			// Tableau en memoire des jalons
	var $milestones=array();
	var $orphans=array();


	/**
	 * 	Constructor
	 *
	 * 	@param	DoliDB	$db		Database handler
	 */
	function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 * 	Charge le jalon
	 *
	 * 	@param	int		$id			Object line id
	 * 	@param	string	$element	Type of element
	 */
	function fetch($fk_element=null, $element=null, $id=null)
	{
		$sql = "SELECT rowid, fk_element, elementtype, label, tms, options";
		$sql.= " FROM ".MAIN_DB_PREFIX."milestone";
		if (!empty($id))
			$sql.= " WHERE rowid = " . $id;
		else if (!empty($fk_element))
			$sql.= " WHERE fk_element = " . $fk_element;
		if (!empty($element))
			$sql.= " AND elementtype = '" . $element . "'";

		dol_syslog(get_class($this)."::fetch sql=".$sql, LOG_DEBUG);
		$resql  = $this->db->query ($sql);
		if ($resql)
		{
			$obj = $this->db->fetch_object($resql);

			$this->rowid			= $obj->rowid;
			$this->fk_element		= $obj->fk_element;
			$this->elementtype		= $obj->elementtype;
			$this->label	   		= $obj->label;
			$this->options			= json_decode($obj->options, true);

			$this->db->free($resql);
		}
		else
		{
			dol_print_error ($this->db);
			return -1;
		}
	}

	/**
	 *
	 */
	function getChildObject($object)
	{
		global $conf;

		$element = $object->element;

		$sql = "SELECT rowid";
		$sql.= " FROM ".MAIN_DB_PREFIX.$element;
		$sql.= " WHERE fk_parent_line = ".$lineid;

		if ($this->db->query($sql))
		{

		}
	}

	/**
	 *
	 */
	function getListByElement($element)
	{
		$sql = "SELECT rowid";
		$sql.= " FROM " . MAIN_DB_PREFIX . "milestone";
		$sql.= " WHERE elementtype = '" . $element . "'";

		dol_syslog(get_class($this)."::getListByElement sql=".$sql, LOG_DEBUG);
		$resql  = $this->db->query ($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;

			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				$objectstatic = new self($this->db);
				$ret = $objectstatic->fetch(null, $element, $obj->rowid);

				$this->milestones[$i]['rowid']			= $objectstatic->rowid;
				$this->milestones[$i]['fk_element']		= $objectstatic->fk_element;
				$this->milestones[$i]['elementtype']	= $objectstatic->elementtype;
				$this->milestones[$i]['label']			= $objectstatic->label;
				$this->milestones[$i]['options']		= json_encode($objectstatic->options);

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error ($this->db);
			return -1;
		}
	}

	/**
	 *
	 */
	function getOrphanChildsByElement($element, $fk_name="fk_parent_line", $product_type=9, $special_code=1790)
	{
		$allchilds=array();

		$sql = "SELECT rowid, fk_parent_line";
		$sql.= " FROM " . MAIN_DB_PREFIX . $element;
		$sql.= " WHERE fk_parent_line > 0";

		dol_syslog(get_class($this)."::getOrphanChildsByElement sql=".$sql, LOG_DEBUG);
		$resql  = $this->db->query ($sql);
		if ($resql)
		{
			$num = $this->db->num_rows($resql);
			$i = 0;

			while ($i < $num)
			{
				$obj = $this->db->fetch_object($resql);

				$allchilds[$i]['rowid']				= $obj->rowid;
				$allchilds[$i]['fk_parent_line']	= $obj->fk_parent_line;
				$allchilds[$i]['elementtype']		= $element;

				$i++;
			}

			$this->db->free($resql);
		}
		else
		{
			dol_print_error ($this->db);
			return -1;
		}

		if (!empty($allchilds))
		{
			// Check if child is for this module, another modules or is orphan

			$allparents=array();

			$sql = " SELECT rowid FROM " . MAIN_DB_PREFIX . $element;
			$sql.= " WHERE product_type > 3";
			$sql.= " AND special_code > 0";

			dol_syslog(get_class($this)."::getOrphanChildsByElement sql=".$sql, LOG_DEBUG);
			$resql  = $this->db->query ($sql);
			if ($resql)
			{
				$num = $this->db->num_rows($resql);
				$i = 0;

				while ($i < $num)
				{
					$obj = $this->db->fetch_object($resql);

					$allparents[$i] = $obj->rowid;

					$i++;
				}

				$this->db->free($resql);
			}
			else
			{
				dol_print_error ($this->db);
				return -1;
			}

			if (!empty($allparents))
			{
				foreach ($allparents as $id)
				{
					foreach ($allchilds as $key => $values)
					{
						if ($id == $values['fk_parent_line'])
						{
							unset($allchilds[$key]);
							continue;
						}
					}
				}
			}

			$this->orphans = $allchilds;
		}

		return 1;
	}

}
