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
 * or see http://www.gnu.org/
 */

/**
 *	\file       /milestone/lib/milestone.lib.php
 *	\brief      Ensemble de fonctions de base pour le module Milestone
 *	\ingroup	milestone
 */

function milestoneadmin_prepare_head()
{
	global $langs, $conf;

	$langs->load("milestone@milestone");

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/milestone/admin/milestone.php",1);
	$head[$h][1] = $langs->trans("Options");
	$head[$h][2] = 'options';
	$h++;

	$head[$h][0] = dol_buildpath("/milestone/admin/about.php",1);
	$head[$h][1] = $langs->trans("About");
	$head[$h][2] = 'about';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'milestone');

	return $head;
}

/**
 *
 */
function checkMilestoneVersion()
{
	global $conf;

	if (empty($conf->global->MILESTONE_MAIN_VERSION)) return false;
	if ($conf->global->MILESTONE_MAIN_VERSION < '2.0.1') return false;
	if ($conf->global->MILESTONE_LAST_UPGRADE < '2.0.1') return false;

	return true;
}

/**
 *
 */
function admin_getMilestoneElement($data)
{
	global $db, $conf;
	require_once __DIR__ . '/../class/dao_milestone.class.php';
	$milestone = new DaoMilestone($db);

	$element = $data['element'];

	$milestone->getListByElement($element);

	setMilestoneCounter($milestone->milestones, $element);

	sleep(1);

	return json_encode(array('milestones' => $milestone->milestones, 'num' => $_SESSION['milestone_upgrade']['num_elements']));
}

/**
 *
 */
function admin_getOrphanChildsElement($data)
{
	global $db, $conf;
	require_once __DIR__ . '/../class/dao_milestone.class.php';
	$milestone = new DaoMilestone($db);

	$element = $data['element'];

	$milestone->getOrphanChildsByElement($element);

	setMilestoneCounter($milestone->orphans, $element);

	sleep(1);

	return json_encode(array('orphans' => $milestone->orphans, 'num' => $_SESSION['milestone_upgrade']['num_elements']));
}

/**
 *
 */
function admin_upgradeMilestone($data)
{
	global $db;

	$upgrade 	= $data['upgrade'];
	$element 	= $data['element'];
	$fk_element = $data['fk_element'];
	$label 		= base64_decode($data['label']);
	$options	= json_decode(base64_decode($data['options']), true);

	if ($element == 'propal')
	{
		require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
		$object = new Propal($db);
	}
	elseif ($element == 'commande')
	{
		require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
		$object = new Commande($db);
	}
	elseif ($element == 'facture')
	{
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		$object = new Facture($db);
	}

	$db->begin();

	// Get object id
	$rowid = $object->getValueFrom($object->table_element_line, $fk_element, $object->fk_element);
	if (!empty($rowid))
	{
		// Set objectline label
		$ret = $object->setValueFrom('label', $label, $object->table_element_line, $fk_element, '', '', 'none');
		if ($ret > 0)
		{
			// Get object extraparams
			$extra = $object->getValueFrom($object->table_element, $rowid, 'extraparams');
			$extraparams = json_decode($extra, true);
			if (!is_array($extraparams)) $extraparams=array();
			if (!isset($extraparams['milestone'])) $extraparams['milestone'] = array();
			$extraparams['milestone'][$fk_element] = $options;

			// Set object extraparams
			$ret = $object->setValueFrom('extraparams', json_encode($extraparams), $object->table_element, $rowid);
		}
	}

	if ($upgrade == 1) $db->commit();
	else if ($upgrade == 2)	$db->rollback();

	$percent = getMilestoneCounter();

	if ($percent == 100)
	{
		if (is_array($_SESSION['MILESTONE_UPGRADE_STATUS']) && empty($_SESSION['MILESTONE_UPGRADE_STATUS'][$element]))
			$_SESSION['MILESTONE_UPGRADE_STATUS'][$element] = 1; // Element upgrade completed
	}

	//sleep(1);

	return json_encode(array('percent' => $percent));
}

/**
 *
 */
function admin_deleteOrphan($data)
{
	global $db;

	$upgrade 	= $data['upgrade'];
	$element 	= $data['element'];
	$rowid 		= $data['rowid'];

	if ($element == 'propaldet')
	{
		require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
		$object = new PropaleLigne($db);
	}
	elseif ($element == 'commandedet')
	{
		require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
		$object = new OrderLine($db);
	}
	elseif ($element == 'facturedet')
	{
		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
		$object = new FactureLigne($db);
	}

	$db->begin();

	if (!empty($rowid))
	{
		$object->rowid = $rowid;
		$ret = $object->delete();
	}

	if ($upgrade == 1) $db->commit();
	else if ($upgrade == 2)	$db->rollback();

	$percent = getMilestoneCounter();

	if ($percent == 100)
	{
		if (is_array($_SESSION['MILESTONE_UPGRADE_STATUS']) && empty($_SESSION['MILESTONE_UPGRADE_STATUS'][$element]))
			$_SESSION['MILESTONE_UPGRADE_STATUS'][$element] = 1; // Element upgrade completed
	}

	//sleep(1);

	return json_encode(array('percent' => $percent));
}

/**
 * Set upgrade status
 */
function admin_setMilestoneUpgradeStatus($data)
{
	global $db, $conf, $langs;

	require_once DOL_DOCUMENT_ROOT . "/core/lib/admin.lib.php";

	if (!empty($_SESSION['MILESTONE_UPGRADE_STATUS']['propal']) && !empty($_SESSION['MILESTONE_UPGRADE_STATUS']['commande']) && !empty($_SESSION['MILESTONE_UPGRADE_STATUS']['facture'])
		&& !empty($_SESSION['MILESTONE_UPGRADE_STATUS']['propaldet']) && !empty($_SESSION['MILESTONE_UPGRADE_STATUS']['commandedet']) && !empty($_SESSION['MILESTONE_UPGRADE_STATUS']['facturedet'])
	)
	{
		unset($_SESSION['MILESTONE_UPGRADE_STATUS']);
		if ($data['upgrade'] == 2)
			setEventMessages($langs->trans("MilestoneUpgradeSimulationCompleted"), null, 'mesgs');
		else
			setEventMessages($langs->trans("MilestoneUpgradeCompleted"), null, 'mesgs');
	}
	else
	{
		setEventMessages($langs->trans("ErrorMilestoneUpgradeNotCompleted"), null, 'errors');
		return json_encode(array('status' => 'error', 'data' => $_SESSION['MILESTONE_UPGRADE_STATUS']), JSON_PRETTY_PRINT);
	}

	if ($data['upgrade'] < 2) {
		$sql = 'DROP TABLE IF EXISTS '.MAIN_DB_PREFIX.'milestone';
		if ($db->query($sql))
			dolibarr_set_const($db,'MILESTONE_LAST_UPGRADE',$conf->global->MILESTONE_MAIN_VERSION,'chaine',0,'Milestone last upgrade version',0);
	}

	return json_encode(array('status' => 'success'));
}

/**
 *
 */
function setMilestoneCounter($elementArray, $element)
{
	$num = 0;

	if (is_array($elementArray) && !empty($elementArray))
	{
		$num = count($elementArray);
	}
	else
	{
		if (is_array($_SESSION['MILESTONE_UPGRADE_STATUS']) && empty($_SESSION['MILESTONE_UPGRADE_STATUS'][$element]))
			$_SESSION['MILESTONE_UPGRADE_STATUS'][$element] = 1; // Element upgrade completed
	}

	$_SESSION['milestone_upgrade'] = array('num_elements' => $num);
}

/**
 * Get counter
 */
function getMilestoneCounter()
{
	if (is_array($_SESSION['milestone_upgrade']))
	{
		if (!isset($_SESSION['milestone_upgrade']['percent']))
		{
			$_SESSION['milestone_upgrade']['percent'] = 0;
			if (!empty($_SESSION['milestone_upgrade']['num_elements']))
				$_SESSION['milestone_upgrade']['quotient'] = round(100 / $_SESSION['milestone_upgrade']['num_elements'], 5);  // Quotient precision
			else
				$_SESSION['milestone_upgrade']['percent'] = 100;
		}
	}
	else
		return false;

	if ($_SESSION['milestone_upgrade']['percent'] < 100)
	{
		$_SESSION['milestone_upgrade']['percent'] += $_SESSION['milestone_upgrade']['quotient'];
		$_SESSION['milestone_upgrade']['num_elements']--;
	}

	if ($_SESSION['milestone_upgrade']['percent'] >= 100 || ($_SESSION['milestone_upgrade']['percent'] < 100 && $_SESSION['milestone_upgrade']['num_elements'] == 0)) {
		unset($_SESSION['milestone_upgrade']);
		$percent = 100;
	}
	else
		$percent = $_SESSION['milestone_upgrade']['percent'];

	return round($percent);
}
