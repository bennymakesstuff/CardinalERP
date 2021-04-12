<?php
/* Copyright (C) 2016 Regis Houssin  <regis.houssin@capnetworks.com>
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
 *       \file       /milestone/core/ajax/milestone.php
 *       \brief      File to return Ajax response on Milestone
 */

if (! defined('NOTOKENRENEWAL')) define('NOTOKENRENEWAL','1'); // Disables token renewal
if (! defined('NOREQUIREMENU'))  define('NOREQUIREMENU','1');
if (! defined('NOREQUIREHTML'))  define('NOREQUIREHTML','1');
if (! defined('NOREQUIREHOOK'))  define('NOREQUIREHOOK','1');
//if (! defined('NOREQUIRESOC'))   define('NOREQUIRESOC','1');

$res=@include("../../../main.inc.php");						// For root directory
if (! $res && file_exists($_SERVER['DOCUMENT_ROOT']."/main.inc.php"))
	$res=@include($_SERVER['DOCUMENT_ROOT']."/main.inc.php"); // Use on dev env only
if (! $res) $res=@include("../../../../main.inc.php");		// For "custom" directory

require_once __DIR__ . '/../../lib/milestone.lib.php';

if (!empty($user->admin) && !empty($conf->milestone->enabled) && checkMilestoneVersion() === false)
{
	$operation = GETPOST('operation','alpha');

	if (!empty($operation) && strpos($operation, "admin_") !== false && function_exists($operation))
	{
		header('Content-type: application/json');
		echo $operation($_REQUEST);
	}
}
