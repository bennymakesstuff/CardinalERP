<?php
/* Copyright (C) 2011-2016 Regis Houssin <regis.houssin@capnetworks.com>
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
 *	\file       /dcloud/document.php
 *  \brief      Wrapper to download data files
 *  \remarks    Call of this wrapper is made with URL:
 * 				document.php?file=filename
 */

if (! defined('NOTOKENRENEWAL'))	define('NOTOKENRENEWAL',1); // Disables token renewal
if (! defined('NOREQUIREMENU'))		define('NOREQUIREMENU','1');
if (! defined('NOREQUIREHTML'))		define('NOREQUIREHTML','1');
if (! defined('NOREQUIREAJAX'))		define('NOREQUIREAJAX','1');

// C'est un wrapper, donc header vierge
function llxHeader() { }

$res=@include("../main.inc.php");						// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../main.inc.php");		// For "custom" directory

dol_include_once('/dcloud/lib/dcloud.lib.php');
include_once dirname(__FILE__).'/lib/dropbox.lib.php';
require_once DOL_DOCUMENT_ROOT.'/core/lib/files.lib.php';

$file = GETPOST('file','alpha');	// Do not use urldecode here ($_GET are already decoded by PHP).

if (!empty($file) && $conf->dcloud->enabled && !empty($conf->global->DROPBOX_CONSUMER_KEY) && !empty($conf->global->DROPBOX_CONSUMER_SECRET) && !empty($conf->global->DROPBOX_ACCESS_TOKEN))
{
	preg_match('/\/([^\/]+)$/i',$file,$regs);

	$original_file = $regs[1];

	// Define mime type
	$type=dol_mimetype($original_file);

	// Define attachment (attachment=true to force choice popup 'open'/'save as')
	$attachment = true;
	// Text files
	if (preg_match('/\.txt$/i',$original_file))  	{ $attachment = false; }
	if (preg_match('/\.csv$/i',$original_file))  	{ $attachment = true; }
	if (preg_match('/\.tsv$/i',$original_file))  	{ $attachment = true; }
	// Documents MS office
	if (preg_match('/\.doc(x)?$/i',$original_file)) { $attachment = true; }
	if (preg_match('/\.dot(x)?$/i',$original_file)) { $attachment = true; }
	if (preg_match('/\.mdb$/i',$original_file))     { $attachment = true; }
	if (preg_match('/\.ppt(x)?$/i',$original_file)) { $attachment = true; }
	if (preg_match('/\.xls(x)?$/i',$original_file)) { $attachment = true; }
	// Documents Open office
	if (preg_match('/\.odp$/i',$original_file))     { $attachment = true; }
	if (preg_match('/\.ods$/i',$original_file))     { $attachment = true; }
	if (preg_match('/\.odt$/i',$original_file))     { $attachment = true; }
	// Misc
	if (preg_match('/\.(html|htm)$/i',$original_file)) 	{ $attachment = false; }
	if (preg_match('/\.pdf$/i',$original_file))  	{ $attachment = true; }
	if (preg_match('/\.sql$/i',$original_file))     { $attachment = true; }
	// Images
	if (preg_match('/\.jpg$/i',$original_file)) 	{ $attachment = true; }
	if (preg_match('/\.jpeg$/i',$original_file)) 	{ $attachment = true; }
	if (preg_match('/\.png$/i',$original_file)) 	{ $attachment = true; }
	if (preg_match('/\.gif$/i',$original_file)) 	{ $attachment = true; }
	if (preg_match('/\.bmp$/i',$original_file)) 	{ $attachment = true; }
	if (preg_match('/\.tiff$/i',$original_file)) 	{ $attachment = true; }
	// Calendar
	if (preg_match('/\.vcs$/i',$original_file))  	{ $attachment = true; }
	if (preg_match('/\.ics$/i',$original_file))  	{ $attachment = true; }
	if (GETPOST("attachment"))                      { $attachment = true; }
	if (!empty($conf->global->MAIN_DISABLE_FORCE_SAVEAS)) $attachment=false;

	clearstatcache();

	// Output file on browser
	dol_syslog("Dropbox::download.php download $original_file content-type=$type");

	if ($type)       header('Content-Type: '.$type.(preg_match('/text/',$type)?'; charset="'.$conf->file->character_set_client:''));
	if ($attachment) header('Content-Disposition: attachment; filename="'.$original_file.'"');
	else header('Content-Disposition: inline; filename="'.$original_file.'"');

	// Ajout directives pour resoudre bug IE
	header('Cache-Control: Public, must-revalidate');
	header('Pragma: public');

	echo dropbox_get_file($file);
}

?>