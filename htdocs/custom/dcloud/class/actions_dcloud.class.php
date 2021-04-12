<?php
/* Copyright (C) 2011-2018	Regis Houssin	<regis.houssin@capnetworks.com>
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
 *   \file			/dcloud/class/actions_dcloud.class.php
 *   \ingroup		d-cloud
 *   \brief			File of class to manage dcloud actions
 */

require_once __DIR__ . '/../class/dao_dcloud.class.php';
require_once __DIR__ . '/../lib/dcloud.lib.php';
require_once __DIR__ . '/../lib/dropbox.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

/**
 *    \class      ActionsDcloud
 *    \brief      Class to manage dcloud actions
 */
class ActionsDcloud
{
	var $db;

	var $error;
	var $errors=array();

	var $resprints;
	var $results=array();

   /**
	*	Constructor
	*
	*	@param	DoliDB	$db		Database handler
	*/
	function __construct($db)
	{
		$this->db = $db;
	}

	/**
	 *
	 */
	function getInstanceDao()
	{
		if (! is_object($this->dao))
			$this->dao=new DaoDcloud($this->db);

		return $this->dao;
	}

	/**
	 *
	 */
	function getElementFiles($element, $forupgrade = false)
	{
		global $conf;

		$modules = getMainModulesArray(); // dcloud.lib.php
		foreach ($modules as $key => $values)
		{
			$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
			$modulename = $values['name'];
			if (($element == $values['name'] || $element == $key) && !empty($conf->$modulename->enabled) && !empty($conf->global->$enabled))
			{
				$dbname = (!empty($values['dbname'])?$values['dbname']:$values['name']);
				$idname = (!empty($values['idname'])?$values['idname']:'rowid');
				$refname = (!empty($values['refname'])?$values['refname']:'ref');
				$diroutput = (!empty($values['diroutput'])?$values['diroutput']:$values['name']);
				$parentoutput = (!empty($values['parentoutput'])?$values['parentoutput']:'');
				$dirpath	= (!empty($parentoutput)?$parentoutput:(!empty($diroutput)?$diroutput:$values['name']));
				$dir_output	= $conf->$dirpath->dir_output;
				if ($key == 'supplier')
					$dir_output	= $conf->societe->dir_output; // For compatibility
				break;
			}
		}

		if (!empty($dir_output))
		{
			$this->getInstanceDao();
			$listArray = $this->dao->getObjectList($dbname, $idname, $refname, $element); //print_r($listArray); exit;
			if (!empty($listArray))
			{
				$dirContentArray=array();
				foreach($listArray as $id => $values)
				{
					if ($dbname == 'societe')
					{
						$dir = $dir_output . "/" . $id;
					}
					else
					{
						$objectref	= dol_sanitizeFileName($values[$refname]);
						$dir		= $dir_output . '/' . (!empty($parentoutput) && !empty($diroutput) ? $diroutput.'/' : '') . $objectref;
						if ($element == 'invoice_supplier') {
							$dir = $dir_output . '/'  . (!empty($parentoutput) && !empty($diroutput) ? $diroutput.'/' : '') . get_exdir($id, 2, 0, 0, null, 'invoice_supplier') . $objectref; // For backward compatibility
						} else if ($element == 'product' || $element == 'service') {
							$dir = $dir_output . '/'  . (!empty($parentoutput) && !empty($diroutput) ? $diroutput.'/' : '') . get_exdir($id, 2, 0, 0, null, 'product') . $id; // For backward compatibility
						}
					}

					$excludeFilter = array('^logos$','^thumbs$','\.meta$'); // Exclude specific directories or files
					$ret = dol_dir_list($dir, "all", 0, "", $excludeFilter); //echo 'dir='.$dir."\n"; print_r($ret);

					if (!empty($ret) || $forupgrade)
					{
						$elementName = $values[$refname];
						if (!empty($values['thirdpartyname'])) {
							$dirContentArray[$elementName]['thirdpartyname'] = $values['thirdpartyname'];
							$dirContentArray[$elementName]['customer'] = $values['customer'];
							$dirContentArray[$elementName]['supplier'] = $values['supplier'];
						}

						if (!is_array($dirContentArray[$elementName]['files']))
							$dirContentArray[$elementName]['files']=array();

						$key=0;
						foreach ($ret as $node)
						{
							if (is_array($node) && !empty($node))
							{
								if (!is_array($dirContentArray[$elementName]['files'][$key]))
									$dirContentArray[$elementName]['files'][$key]=array();

								$dirContentArray[$elementName]['files'][$key] = array_merge($dirContentArray[$elementName]['files'][$key], $node);

								if ($node['type'] == 'dir')
								{
									$subret = dol_dir_list($node['fullname'], "all", 0, "", $excludeFilter);
									if (!empty($subret))
									{
										$subkey=0;
										foreach ($subret as $subnode)
										{
											if (is_array($subnode) && !empty($subnode))
											{
												if (!is_array($dirContentArray[$elementName]['files'][$key]['files']))
													$dirContentArray[$elementName]['files'][$key]['files']=array();
												if (!is_array($dirContentArray[$elementName]['files'][$key]['files'][$subkey]))
													$dirContentArray[$elementName]['files'][$key]['files'][$subkey]=array();

												$dirContentArray[$elementName]['files'][$key]['files'][$subkey] = array_merge($dirContentArray[$elementName]['files'][$key]['files'][$subkey], $subnode);

												$subkey++;
											}
										}
									}
								}

								$key++;
							}
						}
					}
				}

				return $dirContentArray;
			}
		}

		return false;
	}

	/**
	 * Hook moveUploadedFile (files.lib.php)
	 */
	function moveUploadedFile($parameters=false, &$object='', &$action='')
	{
		global $conf, $user, $langs;

		if (!checkDCloudVersion()) {
			setEventMessage($langs->trans("DropboxUpgradeIsNeeded"), 'errors');
			return 1;
		}

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$modulefund=false;

		if (! is_object($object) || ! $object->element) return;

		if (! empty($object->element) && $object->element == 'societe' && !empty($object->client) && empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if (! empty($object->element) && $object->element == 'societe' && !empty($object->fournisseur) && empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if (! empty($object->element) && $object->element == 'product' && $object->type == 0 && empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if (! empty($object->element) && $object->element == 'product' && $object->type == 1 && empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync

		if (empty($object->thirdparty) && $object->element != 'societe') $object->fetch_thirdparty();

		$element = $object->element;
		if ($object->element == 'product' && $object->type == 1) $element = 'service';

		$modules = getMainModulesArray(); // dcloud.lib.php
		foreach ($modules as $key => $values)
		{
			$elementname = (!empty($values['element']) ? $values['element'] : false);
			$modulename = $values['name'];
			if (($element == $values['name'] || $element == $key || $element == $elementname) && !empty($conf->$modulename->enabled))
			{
				$modulefund=true;
				$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
				if (! empty($conf->global->$enabled))
				{
					if (!is_connected())
					{
						$this->error++;
						return "ErrorDropboxConnectionIsOut";
					}
					else
					{
						$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
						$thirdpartytype = false;
						if (! empty($values['rootdir']) && is_array($values['rootdir']))
						{
							foreach($values['rootdir'] as $type)
							{
								if ($type == 'supplier' && empty($object->thirdparty->fournisseur)) continue;
								if ($type == 'customer' && empty($object->thirdparty->client)) continue;
								$thirdpartytype = "DROPBOX_MAIN_".strtoupper($type)."_ROOT";
							}
						}
						break;
					}
				}
				else
					return; // Use default dolibarr function if not use dropbox sync
			}
		}

		if (! $modulefund) return; // Use default dolibarr function if not use dropbox sync

		// The file functions must be in OS filesystem encoding.
		$src_file_osencoded=dol_osencode($src_file);
		$file_name_osencoded=dol_osencode($file_name);

		// Check if destination dir is writable
		// TODO

		// Check if destination file already exists
		if (!$allowoverwrite)
		{
			if (file_exists($file_name_osencoded))
			{
				dol_syslog("DCLOUD::dol_move_uploaded_file File ".$file_name." already exists. Return 'ErrorFileAlreadyExists'", LOG_WARNING);
				setEventMessage($langs->trans("ErrorFileAlreadyExists"), 'errors');
				$this->error++;
			}
		}

		if (empty($this->error) && !empty($constname))
		{
			// Move file
			$return=move_uploaded_file($src_file_osencoded, $file_name_osencoded);
			if ($return)
			{
				if (! empty($conf->global->MAIN_UMASK)) @chmod($file_name_osencoded, octdec($conf->global->MAIN_UMASK));
				dol_syslog("DCLOUD::dol_move_uploaded_file Success to move ".$src_file." to ".$file_name." - Umask=".$conf->global->MAIN_UMASK, LOG_DEBUG);

				if (file_exists($dest_file))
				{
					preg_match('/\/([^\/]+)\/([^\/]+)$/i', $dest_file, $regs);
					$objectref	= $regs[1];
					$filename	= $regs[2];

					if (($element == 'product' || $element == 'service') && $objectref == 'photos')
					{
						$filename	= $objectref.'/'.$regs[2];
						$objectref	= $object->ref;
					}

					dol_syslog("DCLOUD::file_upload element=".$element.' objectref='.$objectref.' filename='.$filename, LOG_DEBUG);

					if ($element == 'societe')
					{
						if (!empty($object->client))
							$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name);
						elseif (!empty($object->fournisseur))
							$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name);
					}
					else
						$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($conf->global->$thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$objectref;

					dol_syslog("DCLOUD::file_upload path=".$path." src_file=".$dest_file, LOG_DEBUG);

					// Create dropbox directory if not exist
					if (!dropbox_file_exists($path))
						$metadata = dropbox_create_folder($path);

					// Push document
					$metadata = dropbox_upload_file($path.'/'.$filename, $dest_file, 'force');
				}
			}
			else
			{
				dol_syslog("DCLOUD::dol_move_uploaded_file Failed to move ".$src_file." to ".$file_name, LOG_ERR);
				setEventMessage($langs->trans("ErrorFailedToMoveFile"), 'errors');
			}
		}

		if (empty($this->error)) {
			return 1;
		} else {
			return -1;
		}
	}

	/**
	 * Hook renameUploadedFile (actions_linkedfiles.inc.php)
	 */
	function renameUploadedFile($parameters=false, &$object='', &$action='')
	{
		global $conf, $user, $langs;

		if (!checkDCloudVersion()) {
			setEventMessage($langs->trans("DropboxUpgradeIsNeeded"), 'errors');
			return 1;
		}

		if (is_array($parameters) && ! empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$modulefund=false;

		if (! is_object($object) || ! $object->element) return;

		if (! empty($object->element) && $object->element == 'societe' && !empty($object->client) && empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if (! empty($object->element) && $object->element == 'societe' && !empty($object->fournisseur) && empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if (! empty($object->element) && $object->element == 'product' && $object->type == 0 && empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if (! empty($object->element) && $object->element == 'product' && $object->type == 1 && empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync

		if (empty($object->thirdparty) && $object->element != 'societe') $object->fetch_thirdparty();

		$element = $object->element;
		if ($object->element == 'product' && $object->type == 1) $element = 'service';

		$modules = getMainModulesArray(); // dcloud.lib.php
		foreach ($modules as $key => $values)
		{
			$elementname = (!empty($values['element']) ? $values['element'] : false);
			$modulename = $values['name'];
			if (($element == $values['name'] || $element == $key || $element == $elementname) && !empty($conf->$modulename->enabled))
			{
				$modulefund=true;
				$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
				if (! empty($conf->global->$enabled))
				{
					if (!is_connected())
					{
						$this->error++;
						return "ErrorDropboxConnectionIsOut";
					}
					else
					{
						$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
						$thirdpartytype = false;
						if (! empty($values['rootdir']) && is_array($values['rootdir']))
						{
							foreach($values['rootdir'] as $type)
							{
								if ($type == 'supplier' && empty($object->thirdparty->fournisseur)) continue;
								if ($type == 'customer' && empty($object->thirdparty->client)) continue;
								$thirdpartytype = "DROPBOX_MAIN_".strtoupper($type)."_ROOT";
							}
						}
						break;
					}
				}
				else
					return; // Use default dolibarr function if not use dropbox sync
			}
		}

		if (! $modulefund) return; // Use default dolibarr function if not use dropbox sync

		// Security:
		// Disallow file with some extensions. We rename them.
		// Because if we put the documents directory into a directory inside web root (very bad), this allows to execute on demand arbitrary code.
		if (preg_match('/\.htm|\.html|\.php|\.pl|\.cgi$/i',$filenameto) && empty($conf->global->MAIN_DOCUMENT_IS_OUTSIDE_WEBROOT_SO_NOEXE_NOT_REQUIRED))
		{
			$filenameto.= '.noexe';
		}

		if (empty($this->error) && !empty($constname) && $filenamefrom && $filenameto)
		{
			$srcpath = $upload_dir.'/'.$filenamefrom;
			$destpath = $upload_dir.'/'.$filenameto;

			if (!file_exists($destpath))
			{
				// Move file
				$return=dol_move($srcpath, $destpath);
				if ($return)
				{
					if ($object->id)
					{
						$object->addThumbs($destpath);
					}

					// TODO Add revert function of addThumbs to remove for old name
					//$object->delThumbs($srcpath);

					if (file_exists($destpath))
					{
						$objectref	= $object->ref;

						if ($element == 'societe')
						{
							if (!empty($object->client)) {
								$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name);
							}
							elseif (!empty($object->fournisseur)) {
								$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name);
							}
						}
						else {
							$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($conf->global->$thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$objectref;
						}

						dol_syslog("DCLOUD::actions_linkedfiles.inc.php from=".$path.'/'.$filenamefrom." to=".$path.'/'.$filenameto, LOG_DEBUG);

						if (dropbox_file_exists($path.'/'.$filenamefrom) && !dropbox_file_exists($path.'/'.$filenameto)) {
							$metadata = dropbox_move_file($path.'/'.$filenamefrom, $path.'/'.$filenameto);
						}
						else {
							return -1;
						}

						setEventMessages($langs->trans("FileRenamed"), null);
					}
				}
				else
				{
					$langs->load("errors"); // key must be loaded because we can't rely on loading during output, we need var substitution to be done now.
					dol_syslog("DCLOUD::actions_linkedfiles.inc.php Failed to rename ".$srcpath." to ".$destpath, LOG_ERR);
					setEventMessages($langs->trans("ErrorFailToRenameFile", $filenamefrom, $filenameto), null, 'errors');
				}
			}
			else
			{
				$langs->load("errors"); // key must be loaded because we can't rely on loading during output, we need var substitution to be done now.
				dol_syslog("DCLOUD::actions_linkedfiles.inc.php Failed to rename ".$srcpath." to ".$destpath, LOG_ERR);
				setEventMessages($langs->trans("ErrorFailToRenameFile", $filenamefrom, $filenameto), null, 'errors');
			}
		}

		if (empty($this->error)) {
			return 1;
		} else {
			return -1;
		}
	}

	/**
	 * Hook deleteFile (files.lib.php)
	 */
	function deleteFile($parameters=false, &$object='', &$action='')
	{
		global $conf, $user, $langs;

		if (!checkDCloudVersion()) {
			setEventMessage($langs->trans("DropboxUpgradeIsNeeded"), 'errors');
			$this->resprints = 1;
			return;
		}

		if (is_array($parameters) && !empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		if (!is_object($object) || !$object->element)
			return;

		if ($object->element == 'societe' && !empty($object->client) && empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if ($object->element == 'societe' && !empty($object->fournisseur) && empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if ($object->element == 'product' && $object->type == 0 && empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync
		if ($object->element == 'product' && $object->type == 1 && empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED)) return; // Use default dolibarr function if not use dropbox sync

		if (empty($object->thirdparty) && $object->element != 'societe')
			$object->fetch_thirdparty();

		$element = $object->element;
		if ($object->element == 'product' && $object->type == 1)
			$element = 'service';

		$modules = getMainModulesArray(); // dcloud.lib.php
		foreach ($modules as $key => $values)
		{
			$elementname = (!empty($values['element']) ? $values['element'] : false);
			$modulename = $values['name'];
			if (($element == $values['name'] || $element == $key || $element == $elementname) && !empty($conf->$modulename->enabled))
			{
				$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
				if (!empty($conf->global->$enabled)) {
					if (!is_connected())
					{
						$this->error++;
						return "ErrorDropboxConnectionIsOut";
					}
					else
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
						break;
					}
				}
				else
					return; // Use default dolibarr function if not use dropbox sync
			}
		}

		$ok=false;
		if (empty($this->error) && !empty($constname))
		{
			//print "x".$file." ".$disableglob;
			$ok=true;
			$file_osencoded=dol_osencode($file);    // New filename encoded in OS filesystem encoding charset
			if (empty($disableglob) && !empty($file_osencoded))
			{
				foreach (glob($file_osencoded) as $filename)
				{
					if ($nophperrors) $ok=@unlink($filename);  // The unlink encapsulated by dolibarr
					else $ok=unlink($filename);  // The unlink encapsulated by dolibarr
					if ($ok) dol_syslog("DCLOUD::dol_delete_file Success to delete file ".$filename, LOG_DEBUG);
					else dol_syslog("DCLOUD::dol_delete_file Failed to remove file ".$filename, LOG_WARNING);
				}
			}
			else
			{
				if ($nophperrors) $ok=@unlink($file_osencoded);        // The unlink encapsulated by dolibarr
				else $ok=unlink($file_osencoded);        // The unlink encapsulated by dolibarr
				if ($ok) dol_syslog("Removed file ".$file_osencoded, LOG_DEBUG);
				else dol_syslog("DCLOUD::dol_delete_file Failed to remove file ".$file_osencoded, LOG_WARNING);
			}

			if ($ok)
			{
				preg_match('/\/([^\/]+)\/([^\/]+)$/i', $file, $regs);
				$objectref	= $regs[1];
				$filename	= $regs[2];

				if (($element == 'product' || $element == 'service') && $objectref == 'photos')
				{
					$filename	= $objectref.'/'.$regs[2];
					$objectref	= $object->ref;
				}

				dol_syslog("DCLOUD::file_delete element=".$element.' objectref='.$objectref.' filename='.$filename, LOG_DEBUG);

				if ($element == 'societe')
				{
					if (!empty($object->client))
						$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name).'/'.$filename;
					elseif (!empty($object->fournisseur))
					$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name).'/'.$filename;
				}
				else
					$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($conf->global->$thirdpartytype) ? $conf->global->$thirdpartytype.'/'.dol_replace_invalid_char($object->thirdparty->name).'/' : '').$conf->global->$constname.'/'.$objectref.'/'.$filename;

				dol_syslog("DCLOUD::file_delete path=".$path, LOG_DEBUG);

				// Delete document
				if (dropbox_file_exists($path))
					$metadata = dropbox_delete_file($path);
			}
		}

		$this->resprints = $ok;
	}

	/**
	 * Hook afterPDFCreation
	 */
	function afterPDFCreation($parameters=false, &$object='', &$action='')
	{
		global $conf, $user, $langs;

		if (!checkDCloudVersion()) {
			setEventMessage($langs->trans("DropboxUpgradeIsNeeded"), 'errors');
			$this->resprints = 1;
			return;
		}

		if (is_array($parameters) && !empty($parameters))
		{
			foreach($parameters as $key=>$value)
			{
				$$key=$value;
			}
		}

		$modules = getMainModulesArray(); // dcloud.lib.php
		$element=$object->element;

		foreach ($modules as $key => $values)
		{
			$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
			$elementname = (!empty($values['element']) ? $values['element'] : false);
			$modulename = $values['name'];
			if (($element == $values['name'] || $element == $key || $element == $elementname) && !empty($conf->$modulename->enabled) && !empty($conf->global->$enabled))
			{
				if (!empty($file))
				{
					$objectref	= dol_replace_invalid_char(dol_sanitizeFileName($object->ref));

					preg_match('/\/([^\/])+$/', $file, $regs);
					$filename = $regs[0];

					if (file_exists($file))
					{
						if ($element == 'societe')
						{
							if (!empty($object->client))
								$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_CUSTOMER_ROOT.'/'.dol_replace_invalid_char($object->name);
							elseif (!empty($object->fournisseur))
								$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.$conf->global->DROPBOX_MAIN_SUPPLIER_ROOT.'/'.dol_replace_invalid_char($object->name);
						}
						else
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
						}

						dol_syslog("Dropbox::file_upload path=".$path." file=".$file, LOG_DEBUG);

						// Create dropbox directory if not exist
						if (!dropbox_file_exists($path))
							$metadata = dropbox_create_folder($path);

						// Push document
						$metadata = dropbox_upload_file($path.'/'.$filename, $file, 'force');
					}
					else
						dol_syslog("Dropbox::file_upload File not exists localfile=".$file, LOG_ERR);
				}

				break;
			}
		}
	}

	/**
	 * Hook afterODTCreation
	 */
	function afterODTCreation($parameters=false, &$object='', &$action='')
	{
		global $conf, $user, $langs;

		if (!checkDCloudVersion()) {
			setEventMessage($langs->trans("DropboxUpgradeIsNeeded"), 'errors');
			$this->resprints = 1;
			return;
		}

		$ret=$this->afterPDFCreation($parameters, $object, $action);
	}

	/**
	 * Hook afterDOCXCreation
	 */
	function afterDOCXCreation($parameters=false, &$object='', &$action='')
	{
		global $conf, $user, $langs;

		if (!checkDCloudVersion()) {
			setEventMessage($langs->trans("DropboxUpgradeIsNeeded"), 'errors');
			$this->resprints = 1;
			return;
		}

		$ret=$this->afterPDFCreation($parameters, $object, $action);
	}
}
