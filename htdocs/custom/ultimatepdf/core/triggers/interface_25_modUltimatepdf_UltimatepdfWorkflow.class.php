<?php
/* Copyright (C) 2010-2013  Regis Houssin  <regis.houssin@capnetworks.com>
 * Copyright (C) 2014-2015 Philippe Grand <philippe.grand@atoo-net.com>
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
 *      \file       /ultimatepdf/core/triggers/interface_25_modUltimatepdf_MUltimatepdfWorkflow.class.php
 *      \ingroup    ultimatepdf
 *      \brief      Trigger file for create ultimatepdf data
 */


/**
 *      \class      InterfaceUltimatepdfWorkflow
 *      \brief      Classe des fonctions triggers des actions personnalisees d'ultimatepdf
 */

class InterfaceUltimatepdfWorkflow
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
        $this->family = "ultimatepdf";
        $this->description = "Triggers of this module allows to create ultimatepdf data";
        $this->version = '3.8.0';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'ultimatepdf@ultimatepdf';
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

    }

}
?>
