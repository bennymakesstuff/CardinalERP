<?php
/* Copyright (C) 2011-2018  Regis Houssin  <regis.houssin@capnetworks.com>
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
 *      \file       /dcloud/core/triggers/interface_20_modDcloud_DropboxWorkflow.class.php
 *      \ingroup    d-cloud
 *      \brief      Trigger file for Dropbox workflow
 */


/**
 *      \class      InterfaceDropboxWorkflow
 *      \brief      Classe des fonctions triggers des actions personalisees du module Dropbox
 */

class InterfaceDropboxWorkflow
{
    protected $db;

    /**
     *   Constructor
     *
     *   @param	DoliDB	$db		Database handler
     */
    function __construct($db)
    {
        $this->db = $db ;

        $this->name = preg_replace('/^Interface/i','',get_class($this));
        $this->family = "dcloud";
        $this->description = "Triggers of this module allows to manage Dropbox workflow";
        $this->version = '2.4.0';            // 'development', 'experimental', 'dolibarr' or version
        $this->picto = 'dcloud@dcloud';
    }

    /**
     *   \brief      Renvoi nom du lot de triggers
     *   \return     string      Nom du lot de triggers
     */
    function getName()
    {
        return $this->name;
    }

    /**
     *   \brief      Renvoi descriptif du lot de triggers
     *   \return     string      Descriptif du lot de triggers
     */
    function getDesc()
    {
        return $this->description;
    }

    /**
     *   \brief      Renvoi version du lot de triggers
     *   \return     string      Version du lot de triggers
     */
    function getVersion()
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
     * 	Fonction appelee lors du declenchement d'un evenement Dolibarr.
     * 	D'autres fonctions run_trigger peuvent etre presentes dans core/triggers
     *
     * 	@param      action      Code de l'evenement
     * 	@param      object      Objet concerne
     * 	@param      user        Objet user
     * 	@param      lang        Objet lang
     * 	@param      conf        Objet conf
     * 	@return     int         <0 if fatal error, 0 si nothing done, >0 if ok
     */
	function run_trigger($action,$object,$user,$langs,$conf)
    {
        // Mettre ici le code a executer en reaction de l'action
        // Les donnees de l'action sont stockees dans $object

    	if (!empty($conf->dcloud->enabled)
    	&& !empty($conf->global->DROPBOX_CONSUMER_KEY)
    	&& !empty($conf->global->DROPBOX_CONSUMER_SECRET)
    	&& !empty($conf->global->DROPBOX_ACCESS_TOKEN)
    	&& !empty($conf->global->DROPBOX_MAIN_DATA_ROOT))
    	{
    		require_once __DIR__ . '/../../lib/dcloud.lib.php';
    		require_once __DIR__ . '/../../lib/dropbox.lib.php';
    		require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

    		// Create company directory
    		if ($action == 'COMPANY_CREATE' && (($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT)) || ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))))
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

    			if ($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT))
    			{
    				$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name);
    				dol_syslog("Dropbox::customer_create path=".$path, LOG_DEBUG);
    				if (!dropbox_file_exists($path))
    					$metadata = dropbox_create_folder($path);
    			}
    			if ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))
    			{
    				$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name);
    				dol_syslog("Dropbox::supplier_create path=".$path, LOG_DEBUG);
    				if (!dropbox_file_exists($path))
    					$metadata = dropbox_create_folder($path);
    			}
    		}

    		// Rename company directory
    		else if ($action == 'COMPANY_MODIFY' && (($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT)) || ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))))
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

    			if ($object->oldcopy->name != $object->name)
    			{
    				if ($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT))
    				{
    					$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.$object->oldcopy->name;
    					$newpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name);
    					dol_syslog("Dropbox::customer_modify path=".$path." newpath=".$newpath, LOG_DEBUG);
    					if (dropbox_file_exists($path) && !dropbox_file_exists($newpath))
    						$metadata = dropbox_move_file($path, $newpath);
    				}
    				if ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))
    				{
    					$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->oldcopy->name);
    					$newpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name);
    					dol_syslog("Dropbox::supplier_modify path=".$path." newpath=".$newpath, LOG_DEBUG);
    					if (dropbox_file_exists($path) && !dropbox_file_exists($newpath))
    						$metadata = dropbox_move_file($path, $newpath);
    				}
    			}
    			if ($object->oldcopy->client != $object->client)
    			{
    				if ($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT))
    				{
    					$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name);
    					dol_syslog("Dropbox::customer_modify path=".$path, LOG_DEBUG);
    					if (!dropbox_file_exists($path))
    						$metadata = dropbox_create_folder($path);
    				}
    			}
    			if ($object->oldcopy->fournisseur != $object->fournisseur)
    			{
    				if ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))
    				{
    					$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name);
    					dol_syslog("Dropbox::supplier_modify path=".$path, LOG_DEBUG);
    					if (!dropbox_file_exists($path))
    						$metadata = dropbox_create_folder($path);
    				}
    			}
    		}

    		// Delete company directory
    		else if ($action == 'COMPANY_DELETE' && (($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT)) || ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))))
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

    			if ($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT))
    			{
    				$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name);
    				dol_syslog("Dropbox::customer_delete path=".$path, LOG_DEBUG);
    				if (dropbox_file_exists($path))
    					$metadata = dropbox_delete_file($path);
    			}
    			if ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))
    			{
    				$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name);
    				dol_syslog("Dropbox::supplier_delete path=".$path, LOG_DEBUG);
    				if (dropbox_file_exists($path))
    					$metadata = dropbox_delete_file($path);
    			}
    		}

    		// Rename project directory
    		else if ($action == 'PROJECT_MODIFY')
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

    			$ref = dol_sanitizeFileName($object->ref);
    			$oldref = dol_sanitizeFileName($object->oldcopy->ref);

    			if ($oldref != $ref)
    			{
    				if ($object->client == 1 && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT))
    				{
    					$oldpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->thirdparty->name)."/".$conf->global->DROPBOX_MAIN_PROJECT_ROOT."/".$oldref;
    					$newpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->thirdparty->name)."/".$conf->global->DROPBOX_MAIN_PROJECT_ROOT."/".$ref;
    				}
    				elseif ($object->fournisseur == 1 && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED) && !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT))
    				{
    					$oldpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->thirdparty->name)."/".$conf->global->DROPBOX_MAIN_PROJECT_ROOT."/".$oldref;
    					$newpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->thirdparty->name)."/".$conf->global->DROPBOX_MAIN_PROJECT_ROOT."/".$ref;
    				}

    				dol_syslog("Dropbox::project_modify oldref=".$oldref." path=".$newpath, LOG_DEBUG);

    				if (dropbox_file_exists($oldpath) && !dropbox_file_exists($newpath))
    					$metadata = dropbox_move_file($oldpath, $newpath);
    				else
    					return -1;
    			}
    		}

    		// Rename product directory
    		else if ($action == 'PRODUCT_MODIFY')
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id);

    			$ref = dol_sanitizeFileName($object->ref);
    			$oldref = dol_sanitizeFileName($object->oldcopy->ref);

    			if ($oldref != $ref)
    			{
    				if ($object->isservice())
    				{
    					$oldpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SERVICE_ROOT.'/'.$oldref;
    					$newpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SERVICE_ROOT.'/'.$ref;
    				}
    				else
    				{
    					$oldpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_PRODUCT_ROOT.'/'.$oldref;
    					$newpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_PRODUCT_ROOT.'/'.$ref;
    				}

    				dol_syslog("Dropbox::product_modify oldref=".$oldref." path=".$newpath, LOG_DEBUG);

    				if (dropbox_file_exists($oldpath) && !dropbox_file_exists($newpath))
    					$metadata = dropbox_move_file($oldpath, $newpath);
    			}
    		}

    		// Delete object
    		else if (preg_match('/_MODIFY$/', $action) && !preg_match('/^LINE/', $action) && $action != 'COMPANY_MODIFY' && $action != 'PRODUCT_MODIFY' && $action != 'PROJECT_MODIFY')
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id." element=".$object->element);

    			if (!empty($object->element))
    			{
    				$modules = getMainModulesArray(); // dcloud.lib.php

    				$element=$object->element;
    				$ref = dol_sanitizeFileName($object->ref);
    				$oldref = dol_sanitizeFileName($object->oldcopy->ref);

    				foreach ($modules as $key => $values)
    				{
    					$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
    					$elementname = (!empty($values['element']) ? $values['element'] : false);
    					$modulename = $values['name'];
    					if (($element == $values['name'] || $element == $key || $element == $elementname) && !empty($conf->$modulename->enabled) && !empty($conf->global->$enabled))
    					{
    						$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
    						$thirdpartytype = false;
    						if (!empty($values['rootdir']) && is_array($values['rootdir']))
    						{
    							foreach($values['rootdir'] as $type)
    							{
    								if ($type == 'supplier' && empty($object->thirdparty->fournisseur)) continue;
    								if ($type == 'customer' && empty($object->thirdparty->client)) continue;
    								$thirdpartytype = "DROPBOX_MAIN_".strtoupper($type)."_ROOT";
    							}
    						}

    						$oldpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$oldref;
    						$newpath = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$ref;

    						dol_syslog("Dropbox::modify_object path=".$path." path_encoded=".$path_encoded, LOG_DEBUG);

    						// Modify object directory
    						if (dropbox_file_exists($path))
    							$metadata = dropbox_move_file($oldpath, $newpath);

    							break;
    					}
    				}
    			}
    			else
    				dol_syslog("Dropbox::delete_object element is empty", LOG_ERR);
    		}

    		// Delete object
    		else if (preg_match('/_DELETE$/', $action) && !preg_match('/^LINE/', $action) && $action != 'COMPANY_DELETE')
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id." element=".$object->element);

    			if (!empty($object->element))
    			{
    				$modules = getMainModulesArray(); // dcloud.lib.php

    				$element=$object->element;
    				$objectref = dol_sanitizeFileName($object->ref);

    				foreach ($modules as $key => $values)
    				{
    					$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
    					$elementname = (!empty($values['element']) ? $values['element'] : false);
    					$modulename = $values['name'];
    					if (($element == $values['name'] || $element == $key || $element == $elementname) && !empty($conf->$modulename->enabled) && !empty($conf->global->$enabled))
    					{
    						$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
    						$thirdpartytype = false;
    						if (!empty($values['rootdir']) && is_array($values['rootdir']))
    						{
    							foreach($values['rootdir'] as $type)
    							{
    								if ($type == 'supplier' && empty($object->thirdparty->fournisseur)) continue;
    								if ($type == 'customer' && empty($object->thirdparty->client)) continue;
    								$thirdpartytype = "DROPBOX_MAIN_".strtoupper($type)."_ROOT";
    							}
    						}
    						$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$objectref;

    						dol_syslog("Dropbox::delete_object path=".$path." path_encoded=".$path_encoded, LOG_DEBUG);

    						// Delete object directory
    						if (dropbox_file_exists($path))
    							$metadata = dropbox_delete_file($path);

    						break;
    					}
    				}
    			}
    			else
    				dol_syslog("Dropbox::delete_object element is empty", LOG_ERR);
    		}

    		// Delete PROV documents
    		else if (preg_match('/_VALIDATE$/', $action))
    		{
    			dol_syslog("Trigger '".$this->name."' for action '$action' launched by ".__FILE__.". id=".$object->id." element=".$object->element);

    			$modules = getMainModulesArray(); // dcloud.lib.php

    			$element=$object->element;

    			foreach ($modules as $key => $values)
    			{
    				$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
    				$elementname = (!empty($values['element']) ? $values['element'] : false);
    				$modulename = $values['name'];
    				//dol_syslog("Dropbox::delete_prov element=".$element." values_name=".$values['name'].' key='.$key.' prov='.$values['prov'].' enabled='.$conf->$modulename->enabled.' const='.$conf->global->$enabled, LOG_DEBUG);
    				if (($element == $values['name'] || $element == $key || $element == $elementname) && !empty($values['prov']) && !empty($conf->$modulename->enabled) && !empty($conf->global->$enabled))
    				{
    					if (empty($object->oldref))
    					{
    						$newobject = clone $object;
    						$newobject->fetch($object->id);
    						$oldref	= dol_sanitizeFileName($object->ref);
    						$newref = dol_sanitizeFileName($newobject->ref);
    					}
    					else
    					{
    						$oldref	= dol_sanitizeFileName($object->oldref);
    						$newref = dol_sanitizeFileName($object->ref);
    					}

    					$filename	= $oldref . ".pdf";

    					$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
    					$thirdpartytype = false;
    					if (!empty($values['rootdir']) && is_array($values['rootdir']))
    					{
    						foreach($values['rootdir'] as $type)
    						{
    							if ($type == 'supplier' && empty($object->thirdparty->fournisseur)) continue;
    							if ($type == 'customer' && empty($object->thirdparty->client)) continue;
    							$thirdpartytype = "DROPBOX_MAIN_".strtoupper($type)."_ROOT";
    						}
    					}

    					$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$oldref;
    					$path_file = $path.'/'.$filename;
    					$new_path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$newref;

    					dol_syslog("Dropbox::delete_prov path_file=".$path_file." new_path=".$new_path, LOG_DEBUG);

    					// Delete prov document
    					if (dropbox_file_exists($path.'/'.$filename))
    						$metadata = dropbox_delete_file($path.'/'.$filename);

    					// Rename prov directory
    					if (dropbox_file_exists($path))
    						$metadata = dropbox_move_file($path, $new_path);

    					break;
    				}
    			}
    		}
    	}

		return 0;
    }

}
