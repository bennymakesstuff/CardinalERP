<?php
/* Copyright (C) 2011-2018 Regis Houssin  <regis.houssin@capnetworks.com>
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
 *	\file			/dcloud/lib/dcloud.lib.php
 *  \ingroup		d-cloud
 *  \brief			Library for common d-cloud functions
 */

/**
 *  Define head array for tabs of dropbox pages
 *  @return			Array of head
 */
function dropbox_prepare_head($object)
{
	global $langs, $conf;

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/dcloud/document.php",1).'?element=thirdparty&type=customer&socid='.$object->id;
	$head[$h][1] = $langs->trans("CustomerDocuments");
	$head[$h][2] = 'customer';
	$h++;

	$head[$h][0] = dol_buildpath("/dcloud/document.php",1).'?element=thirdparty&type=supplier&socid='.$object->id;
	$head[$h][1] = $langs->trans("SupplierDocuments");
	$head[$h][2] = 'supplier';
	$h++;

	// Show more tabs from modules
	// Entries must be declared in modules descriptor with line
	// $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
	// $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
	complete_head_from_modules($conf,$langs,$object,$head,$h,'dropbox');

	return $head;
}

/**
 *  Define head array for tabs of dropbox tools setup pages
 *  @return			Array of head
 */
function dropboxadmin_prepare_head()
{
	global $langs, $conf;

	$object = new stdClass();

	$h = 0;
	$head = array();

	$head[$h][0] = dol_buildpath("/dcloud/admin/dropbox.php",1);
	$head[$h][1] = $langs->trans("Parameters");
	$head[$h][2] = 'parameters';
	$h++;

	$head[$h][0] = dol_buildpath("/dcloud/admin/options.php",1);
	$head[$h][1] = $langs->trans("Options");
	$head[$h][2] = 'options';
	$h++;

	/*
	$head[$h][0] = dol_buildpath("/dcloud/admin/dropbox.php",1);
	$head[$h][1] = $langs->trans("CreateAnAccount");
	$head[$h][2] = 'createaccount';
	$h++;
	*/

    // Show more tabs from modules
    // Entries must be declared in modules descriptor with line
    // $this->tabs = array('entity:+tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to add new tab
    // $this->tabs = array('entity:-tabname:Title:@mymodule:/mymodule/mypage.php?id=__ID__');   to remove a tab
    complete_head_from_modules($conf,$langs,$object,$head,$h,'dropboxadmin');

    $head[$h][0] = dol_buildpath("/dcloud/admin/about.php",1);
    $head[$h][1] = $langs->trans("About");
    $head[$h][2] = 'about';
    $h++;

    return $head;
}

/**
 * 	Convert Dropbox string time to timestamp
 */
function dropbox_stringtotime($sDate)
{
	if (function_exists("date_parse_from_format"))
	{
		$gm=true;
		$sFormat = "a, d b Y H:M:S z";
		$time = date_parse_from_format($sFormat, $sDate);
		$timestamp = dol_mktime($time['hour'],$time['minute'],$time['second'],$time['month'],$time['day'],$time['year'],$gm);
	}
	else if (function_exists("strptime"))
	{
		$gm=false;
		$sFormat = "%a, %d %b %Y %H:%M:%S %z";
		$time = strptime($sDate, $sFormat);
		$timestamp = dol_mktime($time['tm_hour'],$time['tm_min'],$time['tm_sec'],$time['tm_mon']+1,$time['tm_mday'],$time['tm_year']+1900,$gm);
	}
	else
	{
		$gm=true;
		$sFormat = "%S, %M, %H, %d, %m, %Y";
		$time = dol_strptime($sDate, $sFormat);
		$timestamp = dol_mktime($time['tm_hour'],$time['tm_min'],$time['tm_sec'],$time['tm_mon']+1,$time['tm_mday'],$time['tm_year']+1900,$gm);
	}

	return $timestamp;
}

/**
 *
 * strptime for Windows
 * @param unknown_type $sDate
 * @param unknown_type $sFormat
 */
if (!function_exists('dol_strptime'))
{
	function dol_strptime($sDate, $sFormat)
	{

		preg_match('/^([a-z]{3}),\s([0-9]{2})\s([a-z]{3})\s([0-9]{4})\s([0-9]{2}):([0-9]{2}):([0-9]{2})/i',$sDate,$regs);

		$day	= $regs[2];
		$month	= $regs[3];
		$year	= $regs[4];
		$hour	= $regs[5];
		$min	= $regs[6];
		$sec	= $regs[7];

		if ($month=='Jan') $month='1';
		if ($month=='Feb') $month='2';
		if ($month=='Mar') $month='3';
		if ($month=='Apr') $month='4';
		if ($month=='May') $month='5';
		if ($month=='Jun') $month='6';

		$sDate = $sec.', '.$min.', '.$hour.', '.$day.', '.$month.', '.$year;

		$aResult = array
		(
				'tm_sec'   => 0,
				'tm_min'   => 0,
				'tm_hour'  => 0,
				'tm_mday'  => 1,
				'tm_mon'   => 0,
				'tm_year'  => 0,
				'tm_wday'  => 0,
				'tm_yday'  => 0,
				'unparsed' => $sDate,
		);

		while($sFormat != "")
		{
			// ===== Search a %x element, Check the static string before the %x =====
			$nIdxFound = strpos($sFormat, '%');
			if($nIdxFound === false)
			{

				// There is no more format. Check the last static string.
				$aResult['unparsed'] = ($sFormat == $sDate) ? "" : $sDate;
				break;
			}

			$sFormatBefore = substr($sFormat, 0, $nIdxFound);
			$sDateBefore   = substr($sDate,   0, $nIdxFound);

			if($sFormatBefore != $sDateBefore) break;

			// ===== Read the value of the %x found =====
			$sFormat = substr($sFormat, $nIdxFound);
			$sDate   = substr($sDate,   $nIdxFound);

			$aResult['unparsed'] = $sDate;

			$sFormatCurrent = substr($sFormat, 0, 2);
			$sFormatAfter   = substr($sFormat, 2);

			$nValue = -1;
			$sDateAfter = "";

			switch($sFormatCurrent)
			{
				case '%S': // Seconds after the minute (0-59)

					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

					if(($nValue < 0) || ($nValue > 59)) return false;

					$aResult['tm_sec']  = $nValue;
					break;

					// ----------
				case '%M': // Minutes after the hour (0-59)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

					if(($nValue < 0) || ($nValue > 59)) return false;

					$aResult['tm_min']  = $nValue;
					break;

					// ----------
				case '%H': // Hour since midnight (0-23)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

					if(($nValue < 0) || ($nValue > 23)) return false;

					$aResult['tm_hour']  = $nValue;
					break;

					// ----------
				case '%d': // Day of the month (1-31)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

					if(($nValue < 1) || ($nValue > 31)) return false;

					$aResult['tm_mday']  = $nValue;
					break;

					// ----------
				case '%m': // Months since January (0-11)
					sscanf($sDate, "%2d%[^\\n]", $nValue, $sDateAfter);

					if(($nValue < 1) || ($nValue > 12)) return false;

					$aResult['tm_mon']  = ($nValue - 1);
					break;

					// ----------
				case '%Y': // Years since 1900
					sscanf($sDate, "%4d%[^\\n]", $nValue, $sDateAfter);

					if($nValue < 1900) return false;

					$aResult['tm_year']  = ($nValue - 1900);
					break;

					// ----------
				default:
					break 2; // Break Switch and while

			} // END of case format

			// ===== Next please =====
			$sFormat = $sFormatAfter;
			$sDate   = $sDateAfter;

			$aResult['unparsed'] = $sDate;

		} // END of while($sFormat != "")

		// ===== Create the other value of the result array =====
		$nParsedDateTimestamp = dol_mktime($aResult['tm_hour'], $aResult['tm_min'], $aResult['tm_sec'], $aResult['tm_mon'] + 1, $aResult['tm_mday'], $aResult['tm_year'] + 1900,true);

		// Before PHP 5.1 return -1 when error
		if(($nParsedDateTimestamp === false)
				||($nParsedDateTimestamp === -1)) return false;

		$aResult['tm_wday'] = (int) strftime("%w", $nParsedDateTimestamp); // Days since Sunday (0-6)
		$aResult['tm_yday'] = (strftime("%j", $nParsedDateTimestamp) - 1); // Days since January 1 (0-365)

		return $aResult;
	}
}

/**
 * 	Convert file size
 * 	@param 	$fs		File size
 */
function filesizeInfo($fs)
{
	global $langs;

	$bytes = array($langs->trans('Kb'),$langs->trans('Kb'),$langs->trans('Mb'),$langs->trans('Gb'),$langs->trans('Tb'));
	// values are always displayed in at least 1 kilobyte:
	if ($fs <= 999) {
		$fs = 1;
	}
	for ($i = 0; $fs > 999; $i++) {
		$fs /= 1024;
	}
	return array(round($fs,1), $bytes[$i]);
}

/**
 *  Construct path with rawurlencode
 */
function dol_rawurlencode($url,$urlencode='')
{
	if (preg_match('/^\/([^\/]+)/i',$url,$regs))
	{
		$urlencode.= '/'.rawurlencode($regs[1]);
		$url = preg_replace('/^'.preg_quote($regs[0],'/').'/','',$url,1);
		if (! empty($url)) return dol_rawurlencode($url,$urlencode);
		else return $urlencode;
	}
}

/**
 * Recursive array searching
 */
function dol_in_array_r($needle, $haystack)
{
	$found = false;
	foreach ($haystack as $item) {
		if ($item === $needle) {
			$found = true;
			break;
		} elseif (is_array($item)) {
			$found = dol_in_array_r($needle, $item);
			if($found) {
				break;
			}
		}
	}
	return $found;
}

/**
 *  Convert string for jstree
 */
function dol_jstree_replace($string)
{
	$string = str_replace('&','5_5',$string); // Use string combination instead amp
	$string = str_replace(')','4_4',$string); // Use string combination instead parenthesis
	$string = str_replace('(','3_3',$string); // Use string combination instead parenthesis
	$string = str_replace('.','2_2',$string); // Use string combination instead dot
	$string = str_replace(' ','1_1',$string); // Use string combination instead space
	$string = str_replace('/','0_0',$string); // Use string combination instead slash
	return $string;
}

/**
 *  Convert string for dropbox
 */
function dol_dropbox_replace($string)
{
	$string = str_replace('0_0','/',$string); // convert slash
	$string = str_replace('1_1',' ',$string); // convert space
	$string = str_replace('2_2','.',$string); // convert dot
	$string = str_replace('3_3','(',$string); // convert parenthesis
	$string = str_replace('4_4',')',$string); // convert parenthesis
	$string = str_replace('5_5','&',$string); // convert amp
	return $string;
}

function dol_replace_invalid_char($string)
{
	$string = str_replace(' / ', '/', $string);
	return preg_replace('~[\/\?\*\"\|<>:]~',' ',$string);
}

/**
 *
 */
function getMainModulesArray()
{
	global $conf;

	$ret=array();
	if (!empty($conf->global->DCLOUD_MAIN_MODULES))
		$ret=json_decode($conf->global->DCLOUD_MAIN_MODULES, true);

	return $ret;
}

/**
 *
 */
function getDependencyModulesArray($element, $constname=null, $disable=false, $thirdparty=false)
{
	global $conf;

	$ret=array();
	if (!empty($conf->global->DCLOUD_MAIN_MODULES))
	{
		$modules=json_decode($conf->global->DCLOUD_MAIN_MODULES, true);
		if ($constname)
		{
			if ($disable)
				$ret[] = '#'.$constname;
			else
				$ret[] = $constname;
		}
		foreach ($modules as $key => $values)
		{
			if ($thirdparty && !empty($values['subelement']) && $values['subelement'] == $element)
			{
				// Showhide
				if (!$constname)
				{
					if (!in_array("#show_DROPBOX_MAIN_".strtoupper($key)."_ROOT",$ret))
						$ret[] = "#show_DROPBOX_MAIN_".strtoupper($key)."_ROOT";
				}
				else
				{
					// Disabled
					if ($disable)
					{
						if (!in_array("#DROPBOX_MAIN_".strtoupper($key)."_ROOT",$ret))
							$ret[] = "#DROPBOX_MAIN_".strtoupper($key)."_ROOT";
					}
					else
					{
						// delete
						if (!in_array("DROPBOX_MAIN_".strtoupper($key)."_ROOT",$ret))
							$ret[] = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
						if (!in_array("DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED",$ret))
							$ret[] = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
					}
				}
			}
			elseif (!empty($values['rootdir']) && empty($values['subelement']))
			{
				foreach ($values['rootdir'] as $id => $rootdir)
				{
					if ($rootdir == $element)
					{
						if ($id > 0) continue; // just the first iteration

						// Showhide
						if (!$constname)
						{
							if (!in_array("#show_DROPBOX_MAIN_".strtoupper($key)."_ROOT",$ret))
								$ret[] = "#show_DROPBOX_MAIN_".strtoupper($key)."_ROOT";
						}
						else
						{
							// Disabled
							if ($disable)
							{
								if (!in_array("#DROPBOX_MAIN_".strtoupper($key)."_ROOT",$ret))
									$ret[] = "#DROPBOX_MAIN_".strtoupper($key)."_ROOT";
							}
							else
							{
								// delete
								if (!in_array("DROPBOX_MAIN_".strtoupper($key)."_ROOT",$ret))
									$ret[] = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
								if (!in_array("DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED",$ret))
									$ret[] = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
							}
						}
					}
				}
			}
		}
	}
//var_dump($ret);
	return $ret;
}

/**
 * 	On/off button for constant
 *
 * 	@param	string	$code			Name of constant
 * 	@param	array	$input			Array of type->list of CSS element to switch. Example: array('disabled'=>array(0=>'cssid'))
 * 	@param	int		$entity			Entity to set
 *  @param	int		$revertonoff	Revert on/off
 * 	@return	void
 */
function ajax_dcloudconstantonoff($code, $input=array(), $entity=null, $revertonoff=0)
{
	global $conf, $langs;

	$entity = ((isset($entity) && is_numeric($entity) && $entity >= 0) ? $entity : $conf->entity);

	$out= "\n<!-- Ajax code to switch constant ".$code." -->".'
	<script type="text/javascript">
		$(document).ready(function() {
			var input = '.json_encode($input).';
			var url = \''.DOL_URL_ROOT.'/core/ajax/constantonoff.php\';
			var code = \''.$code.'\';
			var entity = \''.$entity.'\';
			var yesButton = "'.dol_escape_js($langs->transnoentities("Yes")).'";
			var noButton = "'.dol_escape_js($langs->transnoentities("No")).'";

			// Set constant
			$("#set_" + code).click(function() {
				if (input.alert && input.alert.set) {
					if (input.alert.set.yesButton) yesButton = input.alert.set.yesButton;
					if (input.alert.set.noButton)  noButton = input.alert.set.noButton;
					confirmDcloudConstantAction("set", url, code, input, input.alert.set, entity, yesButton, noButton);
				} else {
					setDcloudConstant(url, code, input, entity);
				}
			});

			// Del constant
			$("#del_" + code).click(function() {
				if (input.alert && input.alert.del) {
					if (input.alert.del.yesButton) yesButton = input.alert.del.yesButton;
					if (input.alert.del.noButton)  noButton = input.alert.del.noButton;
					confirmDcloudConstantAction("del", url, code, input, input.alert.del, entity, yesButton, noButton);
				} else {
					delDcloudConstant(url, code, input, entity);
				}
			});
		});
	</script>'."\n";

	$out.= '<div id="confirm_'.$code.'" title="" style="display: none;"></div>';
	$out.= '<span id="set_'.$code.'" class="linkobject '.(!empty($conf->global->$code)?'hideobject':'').'">'.($revertonoff?img_picto($langs->trans("Enabled"),'switch_on'):img_picto($langs->trans("Disabled"),'switch_off')).'</span>';
	$out.= '<span id="del_'.$code.'" class="linkobject '.(!empty($conf->global->$code)?'':'hideobject').'">'.($revertonoff?img_picto($langs->trans("Disabled"),'switch_off'):img_picto($langs->trans("Enabled"),'switch_on')).'</span>';
	$out.="\n";

	return $out;
}

/**
 *
 */
function adminBlockShowHide($blocname, $title, $hide = false)
{
	global $db, $conf, $langs;
	global $modules;

	$form = new Form($db);

	$rootdir = (!empty($conf->global->DROPBOX_MAIN_ROOT) ? $conf->global->DROPBOX_MAIN_ROOT : '/');
	$datarootdir = (!empty($conf->global->DROPBOX_MAIN_DATA_ROOT) ? $conf->global->DROPBOX_MAIN_DATA_ROOT.'/' : $rootdir);
	$datarootdirnoslash = ((!empty($conf->global->DROPBOX_MAIN_DATA_ROOT) && $conf->global->DROPBOX_MAIN_DATA_ROOT != $rootdir) ? $conf->global->DROPBOX_MAIN_DATA_ROOT : '');
	$sync_info = (!empty($conf->global->DCLOUD_MAIN_SYNC_INFO) ? json_decode($conf->global->DCLOUD_MAIN_SYNC_INFO, true) : array());

	$var=false;

	include 'tpl/bloc_showhide.tpl.php';
}

/**
 *
 */
function admin_getElementFiles($data)
{
	global $db, $conf;
	require_once __DIR__ . '/../class/actions_dcloud.class.php';
	$dcloud = new ActionsDcloud($db);

	$element = $data['element'];

	$nodesArray = $dcloud->getElementFiles($element);

	setCounter($nodesArray);

	sleep(1);

	//print_r($nodesArray);

	return json_encode(array('nodes' => $nodesArray, 'num' => $_SESSION['dcloud_sync']['num_elements']));
}

/**
 *
 */
function admin_syncFiles($data)
{
	global $conf;

	$element = $data['element'];
	$thirdpartyname = (!empty($data['thirdpartyname'])?dol_replace_invalid_char(base64_decode($data['thirdpartyname'])):false);
	$objectref = base64_decode($data['ref']);
	$node = (!empty($data['values']) ? json_decode(base64_decode($data['values']), true) : false);

	$modules = getMainModulesArray(); // dcloud.lib.php
	foreach ($modules as $key => $values)
	{
		if ($element == $values['name'] || $element == $key)
		{
			$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
			$thirdpartytype = false;
			if (!empty($values['rootdir']) && is_array($values['rootdir']))
			{
				foreach($values['rootdir'] as $type)
				{
					//echo 'key='.$key.' element='.$element.' type='.$type."\n";
					//if ($element == $type)
						$thirdpartytype = "DROPBOX_MAIN_".strtoupper($type)."_ROOT";
				}
			}
			break;
		}
	}

	$path = $conf->global->DROPBOX_MAIN_DATA_ROOT.'/'.(!empty($thirdpartytype) && !empty($conf->global->$thirdpartytype) ? $conf->global->$thirdpartytype.'/'.$thirdpartyname.'/' : '').$conf->global->$constname.'/'.$objectref;

	if (!empty($node))
	{
		if (!dropbox_file_exists($path.'/'.$node['name']))
		{
			if ($node['type'] == 'dir')
			{
				$metadata = dropbox_create_folder($path.'/'.$node['name']);
				if (!empty($node['files']))
				{
					foreach($node['files'] as $subnode)
					{
						if ($subnode['type'] == 'dir')
							$metadata = dropbox_create_folder($path.'/'.$node['name'].'/'.$subnode['name']);
						else
							$metadata = dropbox_upload_file($path.'/'.$node['name'].'/'.$subnode['name'], $subnode['fullname']);
					}
				}
			}
			else
				$metadata = dropbox_upload_file($path.'/'.$node['name'], $node['fullname']);
		}

		$percent = getCounter();
	}
	//else
		//usleep(10000); // 1sec = 1000000

	if ($percent == 100)
	{
		setSyncInfo($element);
	}

	return json_encode(array('percent' => $percent));
}

/**
 * Set sync info
 */
function setSyncInfo($element)
{
	global $db, $conf, $user;

	require_once DOL_DOCUMENT_ROOT."/core/lib/admin.lib.php";

	$sync_info = json_decode($conf->global->DCLOUD_MAIN_SYNC_INFO, true);
	$time = dol_now();
	$current_user = $user->login;

	if (!empty($user->firstname) || !empty($user->lastname))
		$current_user = (!empty($user->firstname)?$user->firstname:'').' '.(!empty($user->lastname)?$user->lastname:'').' ('.$user->login.')';

	$sync_info[$element] = array('user'  => $current_user, 'date' => $time);
	$conf->global->DCLOUD_MAIN_SYNC_INFO = json_encode($sync_info);

	dolibarr_set_const($db,'DCLOUD_MAIN_SYNC_INFO',$conf->global->DCLOUD_MAIN_SYNC_INFO,'chaine',0,'',$conf->entity);
}

/**
 *
 */
function setCounter($elementArray)
{
	$countValues = 0;

	if (is_array($elementArray) && !empty($elementArray))
	{
		foreach ($elementArray as $values)
		{
			$num = count($values['files']);

			if ($num)
				$countValues += $num;
			else
				$countValues++;
		}
	}

	$_SESSION['dcloud_sync'] = array('num_elements' => $countValues);
}

/**
 * Get counter
 */
function getCounter()
{
	if (is_array($_SESSION['dcloud_sync']))
	{
		if (!isset($_SESSION['dcloud_sync']['percent']))
		{
			$_SESSION['dcloud_sync']['percent'] = 0;
			if (!empty($_SESSION['dcloud_sync']['num_elements']))
				$_SESSION['dcloud_sync']['quotient'] = round(100 / $_SESSION['dcloud_sync']['num_elements'], 3);  // 100000 max
			else
				$_SESSION['dcloud_sync']['percent'] = 100;
		}
	}
	else
		return false;

	if ($_SESSION['dcloud_sync']['percent'] < 100)
	{
		$_SESSION['dcloud_sync']['percent'] += $_SESSION['dcloud_sync']['quotient'];
		$_SESSION['dcloud_sync']['num_elements']--;
	}

	if ($_SESSION['dcloud_sync']['percent'] >= 100 || ($_SESSION['dcloud_sync']['percent'] < 100 && $_SESSION['dcloud_sync']['num_elements'] == 0)) {
		unset($_SESSION['dcloud_sync']);
		$percent = 100;
	}
	else
		$percent = $_SESSION['dcloud_sync']['percent'];

	return round($percent);
}

/**
 * Check module version
 */
function checkDCloudVersion()
{
	global $conf;

	if (empty($conf->global->DCLOUD_MAIN_VERSION)) return false;
	if ($conf->global->DCLOUD_MAIN_VERSION < '2.5.0') return false;

	return true;
}

/**
 * Check encryption
 */
function checkDCloudEncrypt()
{
	global $langs;

	if (version_compare(PHP_VERSION, '7.2.0') >= 0 && ! function_exists('openssl_encrypt')) return $langs->trans('OpenSSLNotInstalled');
	if (version_compare(PHP_VERSION, '7.2.0') < 0 && ! function_exists('mcrypt_decrypt')) return $langs->trans('McryptNotInstalled');

	return true;
}

