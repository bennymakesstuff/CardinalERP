<?php
/* Copyright (C) 2014-2018	Regis Houssin	<regis.houssin@capnetworks.com>
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
 *	\file       htdocs/dcloud/class/dao_dcloud.class.php
 *	\ingroup    d-cloud
 *	\brief      File of class to manage dcloud dao
 */

if (!class_exists(CommonObject))
	include DOL_DOCUMENT_ROOT . '/core/class/commonobject.class.php';

/**
 *	\class      DaoDcloud
 *	\brief      File of class to manage dcloud dao
 */
class DaoDcloud extends CommonObject
{
	var $db;
	var $error;

	var $socid;

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
	 *
	 */
	function getObjectList($dbname, $idfield, $reffield, $element)
	{
		global $conf;

		$resArray = array();

		$sql = "SELECT DISTINCT db." . $idfield . ", db." . $reffield;
		if (($reffield == 'ref' || $reffield == 'facnumber') && $dbname != 'product' && $dbname != 'paiementfourn') {
			$sql.= ", db.fk_soc";
		} else if ($dbname == 'paiementfourn') {
			$sql.= ", f.fk_soc";
		}
		$sql.= " FROM ".MAIN_DB_PREFIX . $dbname . " as db";
		if ($dbname == 'paiementfourn') {
			$sql.= ','.MAIN_DB_PREFIX.'paiementfourn_facturefourn as pf,'.MAIN_DB_PREFIX.'facture_fourn as f';
		}
		$sql.= ' WHERE db.entity = ' . $conf->entity;
		if ($element == 'customer')
			$sql.= ' AND db.client IN (1,2,3)';
		else if ($element == 'supplier')
			$sql.= ' AND db.fournisseur = 1';
		else if ($element == 'product')
			$sql.= ' AND db.fk_product_type = 0';
		else if ($element == 'service')
			$sql.= ' AND db.fk_product_type = 1';
		else if ($element == 'payment_supplier') {
			$sql .= ' AND pf.fk_facturefourn = f.rowid';
		}

		//echo $sql; exit;

		//$sql.= ' LIMIT 50';

		dol_syslog(get_class($this)."::getObjectList sql=".$sql, LOG_DEBUG);
		$resql  = $this->db->query($sql);
		if ($resql)
		{
			$i=0;
			$num = $this->db->num_rows($resql);
			while ($i < $num)
			{
				$ret = $this->db->fetch_object($resql);
				$resArray[$ret->$idfield][$reffield] = $ret->$reffield;
				if (!empty($ret->fk_soc))
				{
					$this->socid = $ret->fk_soc;
					$this->fetch_thirdparty();
					$resArray[$ret->$idfield]['thirdpartyname'] = $this->thirdparty->name;
					$resArray[$ret->$idfield]['customer'] = $this->thirdparty->client;
					$resArray[$ret->$idfield]['supplier'] = $this->thirdparty->fournisseur;
				}

				$i++;
			}

			return $resArray;
		}
		else
		{
			dol_print_error($this->db);
			return -1;
		}
	}

}
