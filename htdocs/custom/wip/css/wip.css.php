<?php
/* Copyright (C) 2019 Peter Roberts <peter.roberts@finchmc.com.au>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * \file    wip/css/wip.css.php
 * \ingroup wip
 * \brief   CSS file for module WIP.
 */

//if (! defined('NOREQUIREUSER')) define('NOREQUIREUSER','1');	// Not disabled because need to load personalized language
//if (! defined('NOREQUIREDB'))   define('NOREQUIREDB','1');	// Not disabled. Language code is found on url.
if (! defined('NOREQUIRESOC'))    define('NOREQUIRESOC','1');
//if (! defined('NOREQUIRETRAN')) define('NOREQUIRETRAN','1');	// Not disabled because need to do translations
if (! defined('NOCSRFCHECK'))     define('NOCSRFCHECK',1);
if (! defined('NOTOKENRENEWAL'))  define('NOTOKENRENEWAL',1);
if (! defined('NOLOGIN'))         define('NOLOGIN',1);          // File must be accessed by logon page so without login
//if (! defined('NOREQUIREMENU'))   define('NOREQUIREMENU',1);  // We need top menu content
if (! defined('NOREQUIREHTML'))   define('NOREQUIREHTML',1);
if (! defined('NOREQUIREAJAX'))   define('NOREQUIREAJAX','1');

// Load Dolibarr environment
$res=0;
// Try main.inc.php into web root known defined into CONTEXT_DOCUMENT_ROOT (not always defined)
if (! $res && ! empty($_SERVER["CONTEXT_DOCUMENT_ROOT"])) $res=@include($_SERVER["CONTEXT_DOCUMENT_ROOT"]."/main.inc.php");
// Try main.inc.php into web root detected using web root calculated from SCRIPT_FILENAME
$tmp=empty($_SERVER['SCRIPT_FILENAME'])?'':$_SERVER['SCRIPT_FILENAME'];$tmp2=realpath(__FILE__); $i=strlen($tmp)-1; $j=strlen($tmp2)-1;
while($i > 0 && $j > 0 && isset($tmp[$i]) && isset($tmp2[$j]) && $tmp[$i]==$tmp2[$j]) { $i--; $j--; }
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/main.inc.php");
if (! $res && $i > 0 && file_exists(substr($tmp, 0, ($i+1))."/../main.inc.php")) $res=@include(substr($tmp, 0, ($i+1))."/../main.inc.php");
// Try main.inc.php using relative path
if (! $res && file_exists("../../main.inc.php")) $res=@include("../../main.inc.php");
if (! $res && file_exists("../../../main.inc.php")) $res=@include("../../../main.inc.php");
if (! $res) die("Include of main fails");

require_once DOL_DOCUMENT_ROOT.'/core/lib/functions2.lib.php';

session_cache_limiter(false);

// Load user to have $user->conf loaded (not done by default here because of NOLOGIN constant defined) and load permission if we need to use them in CSS
/*if (empty($user->id) && ! empty($_SESSION['dol_login']))
{
    $user->fetch('',$_SESSION['dol_login']);
	$user->getrights();
}*/


// Define css type
header('Content-type: text/css');
// Important: Following code is to cache this file to avoid page request by browser at each Dolibarr page access.
// You can use CTRL+F5 to refresh your browser cache.
if (empty($dolibarr_nocache)) header('Cache-Control: max-age=3600, public, must-revalidate');
else header('Cache-Control: no-cache');

?>
 /* ============================================================================== */
 /*  Time Packets                                                                  */
 /* ============================================================================== */

 .wipvtop {
   vertical-align: top !important;
 } 
 .wipblue {
   background: rgb(108,152,185) !important;
 }
 .wipltgreen {
   background: rgb(204,255,204) !important;
 }
 .wipgreen {
   background: rgb(160,173,58) !important;
 }
 .wipgold {
   background: rgb(174,145,62) !important;
 }
 .wipyellow {
   background: rgb(241,206,101) !important;
 }
 .wipred {
   /*background: rgb(176,90,90) !important;*/
   background: rgb(176,90,89) !important;
 }
 .wipwhite {
   background: rgb(255,255,255) !important;
 }
 .wipdirectlev0 {
   background: rgb(178,207,229) !important;
 }
 .wipdirectlev1 {
   background: rgb(204,233,255) !important;
 }
 .wipamortisedlev0 {
   background: rgb(229,203,127) !important;
 }
 .wipamortisedlev1 {
   background: rgb(255,228,153) !important;
 }
 .wiptextarea {
   border-radius: 0;
   border-top:solid 1px rgba(0,0,0,.2);
   border-left:solid 1px rgba(0,0,0,.2);
   border-right:solid 1px rgba(0,0,0,.2);
   border-bottom:solid 1px rgba(0,0,0,.2);
 
   padding:4px;
   margin-left:0px;
   margin-bottom:1px;
   margin-top:1px;
  }
 td.wipchild {
   /*padding-left: 100px !important*/;
   margin-left:100px !important;
  }
 .wippaddingtopbottom {
   padding: 15px 0px 15px 0px !important;		/* t r b l */
/* padding-top: 15px !important;
   padding-right: 0px !important;
   padding-bottom: 15px !important;
   padding-left: 0px !important;*/
 }
 .wiptotalgreen {
   color: #008800; /* green */
   font-weight: bold;
   font-size: 1.4em;
 }
 .wiptotalred{
   color: #880000;  /* red */
   font-weight: bold;
   font-size: 1.4em;
 }
 .wiptotalneutral {
   font-weight: bold;
   font-size: 1.4em;
 }
 .wiptextred{
   color: #880000;  /* red */
 }
 .wiptextblue{
   color: #000064;  /* blue */
 }

 table.wipratestable td {
   padding: 2px 8px 2px 8px !important;
 }

 
/* ============================================================================== */
/* Eldy fixes                                                                     */
/* ============================================================================== */

div.tabsAction {
    margin: 0px 0em 0px 0em !important;
/*    padding: 0em 0em;
    text-align: right;*/
}
div.divButAction {
	margin-bottom: 0 !important;
}
 
#id-right {
	padding-top: 0 !important; /* Fix to correct space above right body of page in eldy theme css*/
/*	padding-bottom: 16px;

	display: table-cell;
	float: none;
	vertical-align: top;*/
}