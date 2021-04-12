<?php
/* Copyright (C) 2010-2016	Regis Houssin	<regis.houssin@capnetworks.com>
 * Copyright (C) 2014-2015	Philippe Grand	<philippe.grand@atoo-net.com>
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
 *      \file       /milestone/core/triggers/interface_25_modMilestone_MilestoneWorkflow.class.php
 *      \ingroup    milestone
 *      \brief      Trigger file for create milestone data
 */


/**
 *      \class      InterfaceMilestoneWorkflow
 *      \brief      Classe des fonctions triggers des actions personnalisees du milestone
 */

class InterfaceMilestoneWorkflow
{
    private $db;

    /**
     *   Constructor
     *
     *   @param      DoliDB		$db		Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "milestone";
        $this->description = "Triggers of this module allows to create milestone data";
        $this->version = '2.0.0';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'milestone@milestone';
    }


    /**
     * Trigger name
     *
     * 	@return		string	Name of trigger file
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Trigger description
     *
     * 	@return		string	Description of trigger file
     */
    public function getDesc()
    {
        return $this->description;
    }

    /**
     * Trigger version
     *
     * 	@return		string	Version of trigger file
     */
    public function getVersion()
    {
        global $langs;
        $langs->load("admin");

        if ($this->version == 'development') return $langs->trans("Development");
        elseif ($this->version == 'experimental') return $langs->trans("Experimental");
        elseif ($this->version == 'dolibarr') return DOL_VERSION;
        elseif ($this->version) return $this->version;
        else return $langs->trans("Unknown");
    }

     /**
     * Function called when a Dolibarrr business event is done.
     * All functions "run_trigger" are triggered if file
     * is inside directory core/triggers
     *
     * 	@param		string		$action		Event action code
     * 	@param		Object		$object		Object
     * 	@param		User		$user		Object user
     * 	@param		Translate	$langs		Object langs
     * 	@param		conf		$conf		Object conf
     * 	@return		int						<0 if KO, 0 if no triggered ran, >0 if OK
     */
    public function run_trigger($action, $object, $user, $langs, $conf)
    {
        // Mettre ici le code a executer en reaction de l'action
        // Les donnees de l'action sont stockees dans $object


        // Add line
        if (($action == 'LINEPROPAL_INSERT'
        || $action == 'LINEORDER_INSERT'
        || ( $action == 'LINEBILL_INSERT' && !GETPOST('invoiceAvoirWithLines') && GETPOST('type','int') != Facture::TYPE_REPLACEMENT && GETPOST('action') != 'confirm_clone') )
        && ! empty($object->fk_parent_line))
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ". __FILE__ .". id=".$object->rowid);

        	if ($action == 'LINEPROPAL_INSERT')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php');
        		$milestone = new PropaleLigne($this->db);
        	}
        	else if ($action == 'LINEORDER_INSERT')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php');
        		$milestone = new OrderLine($this->db);
        	}
        	else if ($action == 'LINEBILL_INSERT')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php');
        		$milestone = new FactureLigne($this->db);
        	}

        	$milestone->fetch($object->fk_parent_line);

        	$milestone->total_ht += $object->total_ht;
        	$milestone->total_tva += $object->total_tva;
        	$milestone->total_ttc += $object->total_ttc;

        	$ret = $milestone->update_total();

        	return $ret;
        }

        // Update line
        else if (($action == 'LINEPROPAL_UPDATE' || $action == 'LINEORDER_UPDATE' || $action == 'LINEBILL_UPDATE') && ($object->fk_parent_line > 0 || ! empty($object->oldline->fk_parent_line)))
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ". __FILE__ .". id=".$object->rowid);

			if ($action == 'LINEPROPAL_UPDATE')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php');
        		$milestone = new PropaleLigne($this->db);
        	}
        	else if ($action == 'LINEORDER_UPDATE')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php');
        		$milestone = new OrderLine($this->db);
        	}
        	else if ($action == 'LINEBILL_UPDATE')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php');
        		$milestone = new FactureLigne($this->db);
        	}

        	// Stay a child
        	if ($object->fk_parent_line > 0 && ! empty($object->oldline->fk_parent_line))
        	{
        		// remove old values
        		$milestone->fetch($object->oldline->fk_parent_line);
        		if ($milestone->total_ht != 0) $milestone->total_ht -= $object->oldline->total_ht;
        		if ($milestone->total_tva != 0) $milestone->total_tva -= $object->oldline->total_tva;
        		if ($milestone->total_ttc != 0) $milestone->total_ttc -= $object->oldline->total_ttc;
        		$ret = $milestone->update_total();

        		// add new values
        		if ($ret > 0)
        		{
        			$milestone->fetch($object->fk_parent_line);
        			$milestone->total_ht += $object->total_ht;
        			$milestone->total_tva += $object->total_tva;
        			$milestone->total_ttc += $object->total_ttc;
        			$ret = $milestone->update_total();
        		}
        	}
        	// Become a child
        	else if ($object->fk_parent_line > 0 && empty($object->oldline->fk_parent_line))
        	{
        		// add new values
        		$milestone->fetch($object->fk_parent_line);
        		$milestone->total_ht += $object->total_ht;
        		$milestone->total_tva += $object->total_tva;
        		$milestone->total_ttc += $object->total_ttc;
        		$ret = $milestone->update_total();
        	}
        	// Become an individual line
        	else if ($object->fk_parent_line < 0 && ! empty($object->oldline->fk_parent_line))
        	{
        		// remove old values
        		$milestone->fetch($object->oldline->fk_parent_line);
        		$milestone->total_ht -= $object->oldline->total_ht;
        		$milestone->total_tva -= $object->oldline->total_tva;
        		$milestone->total_ttc -= $object->oldline->total_ttc;
        		$ret = $milestone->update_total();
        	}

        	return $ret;
        }

    	// Delete line
        else if (($action == 'LINEPROPAL_DELETE' || $action == 'LINEORDER_DELETE' || $action == 'LINEBILL_DELETE') && ! empty($object->fk_parent_line))
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ". __FILE__ .". id=".$object->rowid);

			if ($action == 'LINEPROPAL_DELETE')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/comm/propal/class/propal.class.php');
        		$milestone = new PropaleLigne($this->db);
        	}
        	else if ($action == 'LINEORDER_DELETE')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/commande/class/commande.class.php');
        		$milestone = new OrderLine($this->db);
        	}
        	else if ($action == 'LINEBILL_DELETE')
        	{
        		require_once(DOL_DOCUMENT_ROOT.'/compta/facture/class/facture.class.php');
        		$milestone = new FactureLigne($this->db);
        	}

        	$milestone->fetch($object->fk_parent_line);

        	$milestone->total_ht -= $object->total_ht;
        	$milestone->total_tva -= $object->total_tva;
        	$milestone->total_ttc -= $object->total_ttc;

        	$ret = $milestone->update_total();

        	return $ret;
        }

        // Add line
        if ($action == 'MILESTONE_MIGRATE_CHILD')
        {
        	dol_syslog("Trigger '".$this->name."' for action '$action' launched by ". __FILE__ .". id=".$object->rowid);

        	if ($object->element == 'propaldet')
        	{
        		require_once DOL_DOCUMENT_ROOT . '/comm/propal/class/propal.class.php';
        		$milestone = new PropaleLigne($this->db);
        	}
        	else if ($object->element == 'commandedet')
        	{
        		require_once DOL_DOCUMENT_ROOT . '/commande/class/commande.class.php';
        		$milestone = new OrderLine($this->db);
        	}
        	else if ($object->element == 'facturedet')
        	{
        		require_once DOL_DOCUMENT_ROOT . '/compta/facture/class/facture.class.php';
        		$milestone = new FactureLigne($this->db);
        	}

        	$milestone->fetch($object->fk_parent_line);

        	$milestone->total_ht += $object->total_ht;
        	$milestone->total_tva += $object->total_tva;
        	$milestone->total_ttc += $object->total_ttc;

        	$ret = $milestone->update_total();

        	return $ret;
        }

		return 0;
    }

}
?>
