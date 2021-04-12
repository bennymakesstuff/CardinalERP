<?php
/* Copyright (C) 2011-2017 Regis Houssin	<regis.houssin@capnetworks.com>
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
 *   \file			/dcloud/class/fileupload.class.php
 *   \ingroup		d-cloud
 *   \brief			File of class to manage fileupload handler
 */

dol_include_once('/dcloud/lib/dcloud.lib.php');
include_once dirname(__FILE__).'/../lib/dropbox.lib.php';
require_once DOL_DOCUMENT_ROOT."/core/lib/files.lib.php";

/**
 *
 */
class FileUpload
{
	protected $options;
	protected $data;

	function __construct($options=null, $data=null) {

		$id = (isset($data['id']) ? $data['id'] : '');
		$this->data = array("id" => $id);

		//print_r($data);

		if (isset($_COOKIE['dol_jstree_select']) && ! empty($_COOKIE['dol_jstree_select'])) {
			$jstree_select = $_COOKIE['dol_jstree_select'];
		} else {
			$jstree_select = $this->data['id'];
		}

		$this->options = array(
			'script_url' => dol_buildpath('/dcloud/core/ajax/fileupload.php',1).'?file='.$jstree_select,
			'upload_dir' => $jstree_select,
			//'upload_url' => dol_buildpath('/dcloud/download.php',1).'?file='.dol_dropbox_replace($jstree_select),
			'upload_url' => dol_buildpath('/dcloud/download.php',1).'?file=',
			'param_name' => 'files',
			// Set the following option to 'POST', if your server does not support
			// DELETE requests. This is a parameter sent to the client:
			'delete_type' => 'DELETE',
			// The php.ini settings upload_max_filesize and post_max_size
			// take precedence over the following max_file_size setting:
			'max_file_size' => null,
			'min_file_size' => 1,
			'accept_file_types' => '/.+$/i',
			// The maximum number of files for the upload directory:
			'max_number_of_files' => null,
			// Image resolution restrictions:
			'max_width' => null,
			'max_height' => null,
			'min_width' => 1,
			'min_height' => 1,
			// Set the following option to false to enable resumable uploads:
			'discard_aborted_uploads' => true,
		);
		if ($options) {
			$this->options = array_replace_recursive($this->options, $options);
		}
	}

	protected function set_file_delete_url($file, $urlencode=false) {
		$file->delete_url = $this->options['script_url'].'0_0'.($urlencode ? rawurlencode($file->name) : $file->name);
		$file->delete_type = $this->options['delete_type'];
		if ($file->delete_type !== 'DELETE') {
			$file->delete_url .= '&_method=DELETE';
		}
	}

	protected function get_file_object($object) {

		global $conf, $langs;

		$langs->load('dcloud@dcloud');

		//print_r($object);

		$file = new stdClass();
		//$file->rev		= $object['rev'];

		if (! empty($object['id'])) {
			$file->id		= $object['id'];
		}

		$file->name		= ((!empty($object['is_dir']) && $object['is_dir'] == 2) ? $langs->transnoentities($object['name']) : $object['name']);
		$file->icon		= $object['icon'].".png";
		$file->mime 		= (!empty($object['is_dir']) ? $file->icon : dol_mimetype($object['name'],'',2));
		$file->size 		= $object['bytes'];
		$file->date		= dol_print_date(dropbox_stringtotime($object['modified']),'dayhour');
		$file->is_dir	= $object['is_dir'];

		if (!empty($object['is_dir'])) {
			if ($object['is_dir'] === 2 && isset($_COOKIE['dol_jstree_parent'])) {
				if (!preg_match('/^'.preg_quote($_SESSION['dropbox_root'], '/').'/', $_COOKIE['dol_jstree_parent'])) {
					$file->url = $_SESSION['dropbox_root'];
				} else {
					$file->url = $_COOKIE['dol_jstree_parent'];
				}
			} else {
				$file->url = $this->options['upload_dir'].'0_0'.dol_jstree_replace($file->name);
			}
		} else {
			//$file->url = $this->options['upload_url'].'/'.rawurlencode($file->name);
			$file->url = $this->options['upload_url'] . rawurlencode($file->id);
		}

		$this->set_file_delete_url($file, true);

		return $file;
	}

	protected function get_file_objects() {
		global $conf;

		if ($conf->dcloud->enabled && !empty($conf->global->DROPBOX_CONSUMER_KEY) && !empty($conf->global->DROPBOX_CONSUMER_SECRET) && !empty($conf->global->DROPBOX_ACCESS_TOKEN))
		{
			$fileslist = dropbox_get_fileslist($this->data);
			if (! empty($fileslist)) return array_values(array_filter(array_map(array($this, 'get_file_object'), $fileslist)));
			else return null;
		}

		return null;
	}

	protected function validate($uploaded_file, $file, $error, $index) {
		if ($error) {
			$file->error = $error;
			return false;
		}
		if (! $file->name) {
			$file->error = 'missingFileName';
			return false;
		}
		if (!preg_match($this->options['accept_file_types'], $file->name)) {
			$file->error = 'acceptFileTypes';
			return false;
		}
		if ($uploaded_file && is_uploaded_file($uploaded_file)) {
			$file_size = filesize($uploaded_file);
		} else {
			$file_size = $_SERVER['CONTENT_LENGTH'];
		}
		if ($this->options['max_file_size'] && (
				$file_size > $this->options['max_file_size'] ||
				$file->size > $this->options['max_file_size'])
		) {
			$file->error = 'maxFileSize';
			return false;
		}
		if ($this->options['min_file_size'] &&
				$file_size < $this->options['min_file_size']) {
			$file->error = 'minFileSize';
			return false;
		}
		if (is_int($this->options['max_number_of_files']) && (
				count($this->get_file_objects()) >= $this->options['max_number_of_files'])
		) {
			$file->error = 'maxNumberOfFiles';
			return false;
		}
		list($img_width, $img_height) = @getimagesize($uploaded_file);
		if (is_int($img_width)) {
			if ($this->options['max_width'] && $img_width > $this->options['max_width'] ||
					$this->options['max_height'] && $img_height > $this->options['max_height']) {
				$file->error = 'maxResolution';
				return false;
			}
			if ($this->options['min_width'] && $img_width < $this->options['min_width'] ||
					$this->options['min_height'] && $img_height < $this->options['min_height']) {
				$file->error = 'minResolution';
				return false;
			}
		}
		return true;
	}

	protected function upcount_name_callback($matches) {
		$index = isset($matches[1]) ? intval($matches[1]) + 1 : 1;
		$ext = isset($matches[2]) ? $matches[2] : '';
		return ' ('.$index.')'.$ext;
	}

	protected function upcount_name($name) {
		return preg_replace_callback(
				'/(?:(?: \(([\d]+)\))?(\.[^.]+))?$/',
				array($this, 'upcount_name_callback'),
				$name,
				1
		);
	}

	protected function trim_file_name($name, $type, $index) {
		// Remove path information and dots around the filename, to prevent uploading
		// into different directories or replacing hidden system files.
		// Also remove control characters and spaces (\x00..\x20) around the filename:
		$file_name = trim(basename(stripslashes($name)), ".\x00..\x20");
		// Add missing file extension for known image types:
		if (strpos($file_name, '.') === false &&
				preg_match('/^image\/(gif|jpe?g|png)/', $type, $matches)) {
			$file_name .= '.'.$matches[1];
		}
		if ($this->options['discard_aborted_uploads']) {
			while(is_file($this->options['upload_dir'].'/'.$file_name)) {
				$file_name = $this->upcount_name($file_name);
			}
		}
		return $file_name;
	}

	protected function handle_form_data($file, $index) {
		// Handle form data, e.g. $_REQUEST['description'][$index]
	}

	protected function handle_file_upload($uploaded_file, $name, $size, $type, $error, $index) {
		global $conf;

		//print_r($this->options);

		$file = new stdClass();
		$file->name = $this->trim_file_name($name, $type, $index);
		$file->mime = dol_mimetype($file->name,'',2);
		$file->size = intval($size);
		$file->type = $type;
		$file->date	= dol_print_date(dol_now(),'dayhour');
		if ($this->validate($uploaded_file, $file, $error, $index)) {
			$this->handle_form_data($file, $index);
			$file_path = dol_dropbox_replace($this->options['upload_dir']).'/'.$file->name;
			clearstatcache();
			if ($uploaded_file && is_uploaded_file($uploaded_file)) {

				//echo $file_path . ' ' . $uploaded_file; exit;
				$metadata = dropbox_upload_file($file_path, $uploaded_file);

			} else {
				// Non-multipart uploads (PUT method support)
				file_put_contents(
						$file_path,
						fopen('php://input', 'r'),
						$append_file ? FILE_APPEND : 0
				);
			}

			if ($metadata)
			{
				$file->url = $this->options['upload_url'].'/'.rawurlencode($file->name);
			}
			else if ($this->options['discard_aborted_uploads'])
			{
				$file->error = 'abort';
			}

			$this->set_file_delete_url($file);

			return $file;

		}
	}

	public function get() {
		$file_name = isset($_REQUEST['file']) ?
		basename(stripslashes($_REQUEST['file'])) : null;
		if ($file_name) {
			$info = $this->get_file_object($file_name);
		} else {
			$info = $this->get_file_objects();
		}
		header('Content-type: application/json');
		echo json_encode($info);
	}

	public function post() {
		global $conf;

		if (isset($_REQUEST['_method']) && $_REQUEST['_method'] === 'DELETE') {
			return $this->delete();
		}
		$upload = isset($_FILES[$this->options['param_name']]) ?
		$_FILES[$this->options['param_name']] : null;
		$info = array();

		if ($conf->dcloud->enabled && !empty($conf->global->DROPBOX_CONSUMER_KEY) && !empty($conf->global->DROPBOX_CONSUMER_SECRET) && !empty($conf->global->DROPBOX_ACCESS_TOKEN))
		{
			if (is_array($upload['tmp_name'])) {
				// param_name is an array identifier like "files[]",
				// $_FILES is a multi-dimensional array:
				foreach ($upload['tmp_name'] as $index => $value) {
					$info[] = $this->handle_file_upload(
							$upload['tmp_name'][$index],
							isset($_SERVER['HTTP_X_FILE_NAME']) ?
							$_SERVER['HTTP_X_FILE_NAME'] : $upload['name'][$index],
							isset($_SERVER['HTTP_X_FILE_SIZE']) ?
							$_SERVER['HTTP_X_FILE_SIZE'] : $upload['size'][$index],
							isset($_SERVER['HTTP_X_FILE_TYPE']) ?
							$_SERVER['HTTP_X_FILE_TYPE'] : $upload['type'][$index],
							$upload['error'][$index],
							$index
					);
				}
			} elseif (isset($_SERVER['HTTP_X_FILE_NAME'])) {
				// param_name is a single object identifier like "file",
				// $_FILES is a one-dimensional array:
				$info[] = $this->handle_file_upload(
						isset($upload['tmp_name']) ? $upload['tmp_name'] : null,
						isset($_SERVER['HTTP_X_FILE_NAME']) ?
						$_SERVER['HTTP_X_FILE_NAME'] : (isset($upload['name']) ?
								$upload['name'] : null),
						isset($_SERVER['HTTP_X_FILE_SIZE']) ?
						$_SERVER['HTTP_X_FILE_SIZE'] : (isset($upload['size']) ?
								$upload['size'] : null),
						isset($_SERVER['HTTP_X_FILE_TYPE']) ?
						$_SERVER['HTTP_X_FILE_TYPE'] : (isset($upload['type']) ?
								$upload['type'] : null),
						isset($upload['error']) ? $upload['error'] : null,
						null
				);
			}
		}

		header('Vary: Accept');
		$json = json_encode($info);
		$redirect = isset($_REQUEST['redirect']) ?
		stripslashes($_REQUEST['redirect']) : null;
		if ($redirect) {
			header('Location: '.sprintf($redirect, rawurlencode($json)));
			return;
		}
		if (isset($_SERVER['HTTP_ACCEPT']) &&
				(strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)) {
			header('Content-type: application/json');
		} else {
			header('Content-type: text/plain');
		}
		echo $json;
	}

	public function delete() {
		global $conf;

		$file_name = isset($_REQUEST['file']) ? stripslashes($_REQUEST['file']) : null;

		if ($conf->dcloud->enabled && !empty($conf->global->DROPBOX_CONSUMER_KEY) && !empty($conf->global->DROPBOX_CONSUMER_SECRET) && !empty($conf->global->DROPBOX_ACCESS_TOKEN))
		{
			$metadata = dropbox_delete_file(dol_dropbox_replace($file_name));
			if ($metadata === false) $success=false;
			else $success=true;
		}

		header('Content-type: application/json');
		echo json_encode($success);
	}
}

?>