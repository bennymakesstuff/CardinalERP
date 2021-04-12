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
 * or see http://www.gnu.org/
 */

/**
 *	\file		/dcloud/lib/dropbox.lib.php
 *  \ingroup		d-cloud
 *  \brief		Library for common dropbox functions
 */

require_once __DIR__ . '/../inc/dropbox/autoload.php';

use Kunnu\Dropbox\Dropbox;
use Kunnu\Dropbox\DropboxApp;
use Kunnu\Dropbox\Store\MemcachePersistentDataStore;
use Kunnu\Dropbox\Store\ShmopPersistentDataStore;
use Kunnu\Dropbox\DropboxFile;

/**
 * Check connection
 */
function is_connected()
{
	$connected = @fsockopen("dropbox.com", 443);
	//website, port  (try 80 or 443)
	if ($connected){
		$is_conn = true; //action when connected
		fclose($connected);
	}else{
		$is_conn = false; //action in connection failure
	}
	return $is_conn;
}

/**
 *
 * @return getAuthHelper
 */
function getAppConfig()
{
	// Get Dropbox service
	$dropbox = getClient();

	//DropboxAuthHelper
	$authHelper = $dropbox->getAuthHelper();

	return $authHelper;
}

/**
 *
 * @return Dropbox
 */
function getClient()
{
	global $conf;

	//Configure Dropbox Application
	$app = new DropboxApp(
		$conf->global->DROPBOX_CONSUMER_KEY,
		$conf->global->DROPBOX_CONSUMER_SECRET,
		(! empty($conf->global->DROPBOX_ACCESS_TOKEN)?$conf->global->DROPBOX_ACCESS_TOKEN:null)
	);

	$persistentDataStore='session'; // Default session cache

	if (! empty($conf->global->DCLOUD_MEMCACHED_ENABLED)) {
		$persistentDataStore = new MemcachePersistentDataStore();
	}
	else if (! empty($conf->global->DCLOUD_SHMOP_ENABLED)) {
		$persistentDataStore = new ShmopPersistentDataStore();
	}

	$config = [
		'http_client_handler' => null,
		'random_string_generator' => null,
		'persistent_data_store' => $persistentDataStore
	];

	//Configure Dropbox service
	$dropbox = new Dropbox($app, $config);

	return $dropbox;
}

/**
 *
 * @param unknown $relative_path
 * @return string
 */
function getUrl($relative_path='')
{
	if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
		$scheme = "https";
	} else {
		$scheme = "http";
	}
	$host = $_SERVER['HTTP_HOST'];
	$path = getPath($relative_path);
	return $scheme."://".$host.$path;
}

/**
 *
 * @param unknown $relative_path
 * @return string
 */
function getPath($relative_path='')
{
	return $_SERVER["SCRIPT_NAME"].(!empty($relative_path) ? "/".$relative_path : '');
}

/**
 *
 */
function requestPath()
{
	if (isset($_SERVER['PATH_INFO'])) {
		return $_SERVER['PATH_INFO'];
	} else {
		return "/";
	}
}

/*
 * Client
 */

/**
 * 	Get quota v2
 */
function dropbox_get_quota()
{
	global $langs;

	$langs->load('dcloud@dcloud');

	$key = 'dropbox_quota_' . date("Y-m-d-H"); // 1 hour validity
	$cache = dropbox_get_cache($key);

	if (! empty($cache))
	{
		$accountinfo = $cache;
	}
	else
	{
		try {
			$dbxClient = getClient();
			$accountinfo = $dbxClient->getSpaceUsage();
		}
		catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			dol_syslog("dropbox_get_quota: " . $e->getMessage(), LOG_ERR);
			return false;
		}

		dropbox_set_cache($key, $accountinfo);
	}

	//var_dump($accountinfo);

	$percentUsed=round(($accountinfo['used']/$accountinfo['allocation']['allocated']) * 100, 1);
	$percentFree=(100-$percentUsed);
	$size=filesizeInfo($accountinfo['allocation']['allocated']);
	$used=filesizeInfo($accountinfo['used']);
	$free=filesizeInfo($accountinfo['allocation']['allocated']-$accountinfo['used']);


	$out = array(
		'used'			=> $percentUsed,
		'free'			=> $percentFree,
		'usedMetric'		=> price($used[0]) . ' ' . $used[1],
		'freeMetric'		=> price($free[0]) . ' ' . $free[1]
	);

	return json_encode($out);
}

/**
 *  Get children
 *  @param	data	Request data
 */
function dropbox_get_children($data, $cleancache = false)
{
	$dbxClient = getClient();
	if (!is_object($dbxClient))
		return false;

	$result=array();
	$icon = array(
			'page_white_acrobat',
			'page_white_picture',
			'page_white_compressed',
			'page_white_word',
			'page_white_excel',
			'page_white_tux'
	);

	$path = dol_dropbox_replace($data['id']);

	if (!$cleancache && $cache = dropbox_get_cache($path))
	{
		$metadata = $cache;
	}
	else
	{
		try {
			$listFolderContents = $dbxClient->listFolder($path);
			$metadata = $listFolderContents->getItems();
		}
		catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			dol_syslog("dropbox_get_children: " . $e->getMessage(), LOG_ERR);
			return false;
		}

		dropbox_set_cache($path, $metadata);
	}

	//print_r($metadata);

	if (empty($metadata) || (isset($metadata['is_deleted']) && $metadata['is_deleted'] == true))
	{
		// Create root folder if not exist
		$metadata = dropbox_create_folder($path);
	}
	else if (! empty($metadata))
	{
		// Directories
		foreach($metadata as $v) {
			if ($v->getTag() == 'folder') {
				$result[] = array(
					"attr" => array('id' => 'node_'.dol_jstree_replace($v->getPathDisplay()), 'rel' => 'folder'),
					//"attr" => array('id' => 'node_' . dol_jstree_replace($v->getId()), 'rel' => 'folder'),
					"data" => $v->getName(),
					"state" => "closed"
				);
			}
		}

		// Files
		foreach($metadata as $v) {
			if ($v->getTag() == 'file') {
				$result[] = array(
					"attr" => array('id' => 'node_'.dol_jstree_replace($v->getPathDisplay()), 'rel' => 'default'),
					//"attr" => array('id' => 'node_' . dol_jstree_replace($v->getId()), 'rel' => 'default'),
					"data" => $v->getName(),
					"state" => ""
				);
			}
		}
	}
	//print_r($result);
	return json_encode($result);
}

/**
 *  Get files list v2
 *  @param	data	Request data
 */
function dropbox_get_fileslist($data, $cleancache = false, $skipparent = false)
{
	$dbxClient = getClient();
	if (!is_object($dbxClient))
		return false;

	$result=array();

	$path = dol_dropbox_replace($data['id']);

	if (!$cleancache && $cache = dropbox_get_cache($path))
	{
		$metadata = $cache;
	}
	else
	{
		try {
			$listFolderContents = $dbxClient->listFolder($path);
			$metadata = $listFolderContents->getItems();
		}
		catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			dol_syslog("dropbox_get_fileslist: " . $e->getMessage(), LOG_ERR);
			return false;
		}

		dropbox_set_cache($path, $metadata);
	}

	// Root
	if (!$skipparent)
	{
		if ($data['id'] != $_SESSION['dropbox_root'] ) {
			$result[] = array(
					"name" => 'ParentFolder',
					"path" => '',
					"is_dir" => 2,
					"icon" => 'parentfolder',
					"thumb_exists" => '',
					"bytes" => '',
					"modified" => ''
			);
		}
	}

	if (! empty($metadata))
	{
		// Directories
		foreach($metadata as $v) {
			if ($v->getTag() == 'folder') {
				$result[] = array(
					"rev" => false,
					"name" => $v->getName(),
					"path" => dol_jstree_replace($v->getPathDisplay()),
					"id" => $v->getId(),
					"is_dir" => true,
					"icon" => 'folder',
					"thumb_exists" => false,
					"bytes" => false,
					"modified" => false
				);
			}
		}

		// Files
		foreach($metadata as $v) {
			if ($v->getTag() == 'file') {
				$result[] = array(
					"rev" => $v->getRev(),
					"name" => $v->getName(),
					"path" => dol_jstree_replace($v->getPathDisplay()),
					"id" => $v->getId(),
					"is_dir" => false,
					"icon" => 'default',
					"thumb_exists" => false,
					"bytes" => $v->getSize(),
					"modified" => $v->getServerModified()
				);
			}
		}
	}

	return $result;
}

/**
 * 	Create a node v2
 * 	@param	data	Request data
 */
function dropbox_create_node($data)
{
	$data['id'] = dol_dropbox_replace($data['id']);
	$title = dol_unescapefile(dol_replace_invalid_char($data['title']));
	$metadata = dropbox_create_folder($data['id'].'/'.$title);

	if ($metadata === false) {
		$result=array('status' => 0, 'error' => 'ErrorCreateFileOrFolder');
	}
	else {
		$result=array('status' => 1, 'id' => $data['id'].'/'.$title);
	}

	return json_encode($result);
}

/**
 * 	Rename a node v2
 * 	@param	data	Request data
 */
function dropbox_rename_node($data)
{
	$data['id'] = dol_dropbox_replace($data['id']);
	$dirname = trim(dirname($data['id']));
	$oldfilename = dol_unescapefile($data['id']);
	$newfilename = dol_unescapefile($data['title']);

	$oldPath = str_replace('//', '/', $dirname.'/'.$oldfilename);
	$newPath = str_replace('//', '/', $dirname.'/'.$newfilename);

	$metadata = dropbox_move_file($oldPath, $newPath);

	if ($metadata === false) {
		$result=array('status' => 0, 'error' => 'ErrorRenameFileOrFolder');
	}
	else {
		$result=array('status' => 1, 'id' => $dirname.'/'.dol_replace_invalid_char($newfilename), 'parent_node' => $dirname);
	}

	return json_encode($result);
}

/**
 * 	Move a node v2
 * 	@param	data	Request data
 */
function dropbox_move_node($data)
{
	$data['id'] = dol_dropbox_replace($data['id']);
	$data['ref'] = dol_dropbox_replace($data['ref']);

	if ($data['copy'])
	{
		$dbxClient = getClient();
		if (!is_object($dbxClient)) {
			return false;
		}
		$dirname = trim(dirname($data['id']));
		$filename = dol_unescapefile($data['id']);
		$fromPath = str_replace('//', '/', $dirname.'/'.$filename);
		$toPath = str_replace('//', '/', $data['ref'].'/'.$filename);

		try {
			$metadata = $dbxClient->copy($fromPath, $toPath);
		}
		catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
			dol_syslog("dropbox_move_node (copy/paste): From=" . $fromPath . " To=" . $toPath . " error=" . $e->getMessage(), LOG_ERR);
			return json_encode(array('status' => 0, 'error' => 'ErrorCopyPasteFileOrFolder'));
		}

		dropbox_clear_cache($dirname);
		dropbox_clear_cache($data['ref']);
		$result=array('status' => 1, 'id' => $data['ref'], 'parent_node' => $data['ref']);

		return json_encode($result);
	}
	else
	{
		$dirname = trim(dirname($data['id']));
		$filename = dol_unescapefile($data['id']);

		$metadata = dropbox_move_file($dirname.'/'.$filename, $data['ref'].'/'.$filename);

		if ($metadata === false) {
			$result=array('status' => 0, 'error' => 'ErrorMoveFileOrFolder');
		}
		else {
			$result=array('status' => 1, 'id' => $data['ref'], 'parent_node' => $data['ref']);
		}

		return json_encode($result);
	}
}

/**
 * 	Remove a node v2
 * 	@param	data	Request data
 */
function dropbox_remove_node($data)
{
	$data['id'] = dol_dropbox_replace($data['id']);
	$dirname = trim(dirname($data['id']));
	$filename = dol_unescapefile($data['id']);

	$metadata = dropbox_delete_file($dirname.'/'.$filename);

	if ($metadata === false) {
		$result=array('status' => 0, 'error' => 'Error');
	}
	else {
		$result=array('status' => 1, 'parent_node' => $dirname, 'id'=> $filename, 'rel' => 'default');
	}

	return json_encode($result);
}

/**
 * Upload file v2
 */
function dropbox_upload_file($path, $uploaded_file, $writemode='add', $rev=null)
{
	$metadata=false;
	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}

	try {
		$dropboxFile = new DropboxFile($uploaded_file);
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		dol_syslog("dropbox_upload_file::DropboxFile - file to upload=" . $uploaded_file . " error=" . $e->getMessage(), LOG_ERR);
		return false;
	}

	if ($writemode == 'force') {
		$parameters = array(
			"mode" => "overwrite"
		);
	}
	else if ($writemode == 'update' && ! empty($rev)) {
		$parameters = array(
			"mode" => "update"
		);
	}
	else {
		$parameters = array(
			"mode" => "add"
		);
	}

	try {
		$dirname = trim(dirname($path));
		$path = str_replace('//', '/', $path);
		$metadata = $dbxClient->upload($dropboxFile, $path, $parameters);
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		dol_syslog("dropbox_upload_file::upload - file to upload=" . $uploaded_file . " path=" . $path . " parameters=" . json_encode($parameters) . " error=" . $e->getMessage(), LOG_ERR);
		return false;
	}

	dol_syslog("dropbox_upload_file::DropboxFile - clear cache dirname=" . $dirname, LOG_DEBUG);

	dropbox_clear_cache($dirname);
	return $metadata;
}

/**
 * Move file v2
 */
function dropbox_move_file($fromPath, $toPath)
{
	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}
	$fromPath = str_replace('//', '/', trim($fromPath));
	$toPath = str_replace('//', '/', trim($toPath));

	try {
		$metadata = $dbxClient->move($fromPath, $toPath);
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		dol_syslog("dropbox_move_file: From=" . $fromPath . " To=" . $toPath . " error=" . $e->getMessage(), LOG_ERR);
		return false;
	}

	dropbox_clear_cache(dirname($fromPath));
	dropbox_clear_cache(dirname($toPath));
	return $metadata;
}

/**
 * Download file v2
 */
function dropbox_get_file($path)
{
	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}

	try {
		$file = $dbxClient->download($path);
		$contents = $file->getContents();
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		dol_syslog("dropbox_get_file: path=" . $path . " error=" . $e->getMessage(), LOG_ERR);
		return false;
	}

	return $contents;
}

/**
 * 	Delete file/directory v2
 */
function dropbox_delete_file($path)
{
	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}
	$dirname = trim(dirname($path));
	$filename = dol_unescapefile($path);
	$path = str_replace('//', '/', $dirname.'/'.$filename);

	try {
		$metadata = $dbxClient->delete($path);
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		dol_syslog("dropbox_delete_file: path=" . $path . " error=" . $e->getMessage(), LOG_ERR);
		return false;
	}

	dol_syslog("dropbox_delete_file::DropboxFile - clear cache dirname=" . $dirname, LOG_DEBUG);

	dropbox_clear_cache($dirname);
	return $metadata;
}

/**
 * Get file/directory exists v2
 */
function dropbox_file_exists($path)
{
	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}
	$dirname = trim(dirname($path));
	$filename = dol_unescapefile($path);
	$path = str_replace('//', '/',$dirname.'/'.dol_replace_invalid_char($filename));

	try {
		$metadata = $dbxClient->getMetadata($path);
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		dol_syslog("dropbox_file_exists: path=" . $path . " error=" . $e->getMessage(), LOG_ERR);
		dropbox_clear_cache($dirname);
		return false;
	}

	return true;
}

/**
 * Create folder v2
 */
function dropbox_create_folder($path)
{
	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}
	$dirname = trim(dirname($path));
	$foldername = dol_unescapefile($path);
	$path = str_replace('//', '/', $dirname.'/'.dol_replace_invalid_char($foldername));

	try {
		$metadata = $dbxClient->createFolder($path);
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		dol_syslog("dropbox_create_folder: path=" . $path . " error=" . $e->getMessage(), LOG_ERR);
		return false;
	}

	dropbox_clear_cache($dirname);
	return $metadata;
}

/*
 * Cache
 */

/**
 * Set cache v2
 */
function dropbox_set_cache($key, $data)
{
	global $conf;

	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}

	$cache = $dbxClient->getPersistentDataStore();

	return $cache->set(md5($key), base64_encode(serialize($data)));
}

/**
 * Get cache v2
 */
function dropbox_get_cache($key)
{
	global $conf;

	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}

	$cache = $dbxClient->getPersistentDataStore();

	return unserialize(base64_decode($cache->get(md5($key))));
}

/**
 * Clear cache v2
 */
function dropbox_clear_cache($key)
{
	global $conf;

	if (is_array($key)) {
		$key = $key['id'];
	}

	$key = dol_dropbox_replace($key);

	$dbxClient = getClient();
	if (!is_object($dbxClient)) {
		return false;
	}

	$cache = $dbxClient->getPersistentDataStore();

	return $cache->clear(md5($key));
}

/*
 * Authentication
 */

/**
 *
 * @return getAuthUrl
 */
function getWebAuth()
{
	$authHelper = getAppConfig();

	//Callback URL
	$callbackUrl = getDCloudUrl();

	if (! $callbackUrl) {
		return false;
	}

	//Fetch the Authorization/Login URL
	$authUrl = $authHelper->getAuthUrl($callbackUrl, array(), dc_encrypt(getUrl("dropbox-auth-finish"), $_SESSION['dcloud_private_key']));

	return $authUrl;
}

/**
 *
 * @return string
 */
function getDCloudUrl()
{
	$connected = @fsockopen("dcloud.dolibox.net", 443);
	//website, port  (try 80 or 443)
	if ($connected){
		fclose($connected);
		return 'https://dcloud.dolibox.net/check.php'; //action when connected
	}else{
		$connected = @fsockopen("dcloud.cap-networks.com", 443);
		//website, port  (try 80 or 443)
		if ($connected){
			fclose($connected);
			return 'https://dcloud.cap-networks.com/check.php'; //action when connected
		}
	}
	return false;
}

/**
 *
 */
function getPrivateKey()
{
	global $conf;

	$dcloudurl = getDCloudUrl();
	if (!$dcloudurl) {
		return false;
	}

	if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
		$url = $dcloudurl.'?action=getPublicKey&v=2&token='.$conf->file->cookie_cryptkey;
	} else {
		$url = $dcloudurl.'?action=getPublicKey&token='.$conf->file->cookie_cryptkey;
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_REFERER, getUrl());
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Accept: application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);

	if (isset($_SERVER["WINDIR"]))
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // for avoid error with Wampserver

	$res = curl_exec($ch);

	curl_close($ch);

	$json = json_decode($res, true);
	$private_key = sha1(md5($_SERVER['HTTP_USER_AGENT'].$json['public_key'].$conf->file->cookie_cryptkey));

	return $private_key;
}

/**
 *
 */
function dc_encrypt($data, $key)
{
	$key = hash('sha256', $key, TRUE);

	if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
		return base64_encode(openssl_encrypt($data, 'BF-ECB', $key, OPENSSL_RAW_DATA));
	} else {
		return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $data, MCRYPT_MODE_CBC, md5(md5($key))));
	}
}

/**
 *
 */
function dc_decrypt($data_encrypted, $key)
{
	$key = hash('sha256', $key, TRUE);

	if (version_compare(PHP_VERSION, '7.2.0') >= 0) {
		return openssl_decrypt(base64_decode(str_replace(' ', '+', $data_encrypted)), 'BF-ECB', $key, OPENSSL_RAW_DATA);
	} else {
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode(str_replace(' ', '+', $data_encrypted)), MCRYPT_MODE_CBC, md5(md5($key))));
	}
}

/**
 *
 */
function checkAuthFinish()
{
	global $db, $conf, $langs;

	$code = GETPOST('code', 'alpha');
	$state = GETPOST('state', 'alpha');

	try {

		$code = dc_decrypt($code, $_SESSION['dcloud_private_key']);

		$authHelper = getAppConfig();

		// Callback URL
		$callbackUrl = getDCloudUrl();

		if (! $callbackUrl) {
			return false;
		}

		// Fetch the AccessToken
		$accessToken = $authHelper->getAccessToken($code, $state, $callbackUrl);

		$token = $accessToken->getToken();
	}
	catch (Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
		// Write full details to server error log.
		// IMPORTANT: Never show the $ex->getMessage() string to the user -- it could contain
		// sensitive information.
		dol_syslog("/dropbox-auth-finish: bad request: " . $e->getMessage(), LOG_DEBUG);
		setEventMessage($langs->trans("ErrorBadRequest"), 'errors');
		header("Location: ".getPath());
		exit;
	}

	$result=dolibarr_set_const($db, "DROPBOX_ACCESS_TOKEN",$token,'chaine',0,'',$conf->entity);
	if ($result >= 0)
	{
		unset($_SESSION['dcloud_private_key']);
		setEventMessage($langs->trans("DropboxTokenIsReady"));
		Header("Location: ".getPath());
		exit;
	}
	else
	{
		dol_print_error($db);
	}
}
