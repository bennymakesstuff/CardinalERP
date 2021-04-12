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
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */
?>

<!-- START PHP TEMPLATE ADMIN ROOT MODULES -->
<div class="table-border centpercent">
	<div class="table-border-row <?php echo $var?"pair":"impair"; ?>">
		<div class="tagtd padding-left5 fifty-percent text-align-left"><b><?php echo $langs->trans("DROPBOX_MAIN_ROOT"); ?></b><br><i><?php echo $langs->trans('MainRootDescription'); ?></i><br><br></div>
		<div class="tagtd valign-middle text-align-left"><div class="float-right"><input size="36" type="text" name="DROPBOX_MAIN_ROOT" value="<?php echo $rootdir; ?>" /></div></div>
		<div class="tagtd valign-middle text-align-center"><span class="dcloud-nosync-nobutton">&nbsp;</span></div>
		<div class="tagtd valign-middle text-align-center"><span class="dcloud-nosync-noinfo">&nbsp;</span></div>
		<div class="tagtd valign-middle text-align-center"><span class="blockspan">&nbsp;</span></div>
	</div>
</div>
<?php $var=!$var; ?>
<div class="table-border centpercent">
	<div class="table-border-row <?php echo $var?"pair":"impair"; ?>">
		<div class="tagtd padding-left5 fifty-percent text-align-left"><b><?php echo $langs->trans("DROPBOX_MAIN_DATA_ROOT"); ?></b><br><i><?php echo $langs->trans('MainDataRootDescription',$rootdir,$rootdir); ?></i><br><br></div>
		<div class="tagtd valign-middle text-align-left"><div class="float-right"><input size="36" type="text" name="DROPBOX_MAIN_DATA_ROOT" value="<?php echo (!empty($conf->global->DROPBOX_MAIN_DATA_ROOT)?$conf->global->DROPBOX_MAIN_DATA_ROOT:''); ?>" placeholder="<?php echo $langs->trans('MainDataRootExample',$datarootdir); ?>"/></div></div>
		<div class="tagtd valign-middle text-align-center"><span class="dcloud-nosync-nobutton">&nbsp;</span></div>
		<div class="tagtd valign-middle text-align-center"><span class="dcloud-nosync-noinfo">&nbsp;</span></div>
		<div class="tagtd valign-middle text-align-center"><span class="blockspan">&nbsp;</span></div>
	</div>
</div>
<?php
foreach ($modules as $key => $values) {
	$modulename = $values['name'];
	if (empty($values['rootdir']) && !empty($conf->$modulename->enabled)) {
		$langs->load($values['lang']);
		$module = ucfirst($key);
		$enabled = "DROPBOX_MAIN_".strtoupper($key)."_ROOT_ENABLED";
		$constname = "DROPBOX_MAIN_".strtoupper($key)."_ROOT";
		$lastsync_user = (!empty($sync_info[$key]['user'])?$sync_info[$key]['user']:$langs->trans('Unknown'));
		$lastsync_date = (!empty($sync_info[$key]['date'])?dol_print_date($sync_info[$key]['date'], 'dayhour'):$langs->trans('Unknown'));
		$var=!$var;
		?>
		<div class="table-border centpercent">
			<div class="table-border-row <?php echo $var?"pair":"impair"; ?>">
				<div class="tagtd padding-left5 fifty-percent" align="left">
					<b><?php echo $langs->trans("RootDirectoryOf",$langs->transnoentities((!empty($values['title2'])?$values['title2']:$values['title']))); ?></b>
					<br><i><?php echo $langs->trans('MainModulesRootDescription',$langs->transnoentities($values['title']),$datarootdirnoslash.'/'.$langs->transnoentities($values['title']),$langs->transnoentities($values['title'])); ?></i><br><br>
				</div>
				<div class="tagtd valign-middle text-align-left">
					<div class="float-right">
						<input size="36" type="text" id="<?php echo $constname; ?>" name="<?php echo $constname; ?>" value="<?php echo (!empty($conf->global->$constname)?$conf->global->$constname:''); ?>" placeholder="<?php echo $langs->trans('MainModulesRootExample',$langs->transnoentities($values['title'])); ?>"<?php echo (empty($conf->global->$enabled) ? ' disabled="disabled"' : ''); ?> />
					</div>
				</div>
				<div class="tagtd valign-middle text-align-center">
					<div id="sync_button_div_<?php echo $constname; ?>" class="<?php echo (!empty($conf->global->$enabled) && !empty($conf->global->$constname)?'dcloud-sync-button':'dcloud-nosync-button'); ?>">
						<?php echo img_picto('','refresh', 'id="sync_button_'.$constname.'" class="dcloud-button-sync'.(!empty($conf->global->$enabled) && !empty($conf->global->$constname) && !empty($conf->global->DROPBOX_ACCESS_TOKEN)?'':' hideobject').'"'); ?>
					</div>
				</div>
				<div class="tagtd valign-middle text-align-center">
					<div id="sync_info_div_<?php echo $constname; ?>" class="<?php echo (!empty($conf->global->$enabled) && !empty($conf->global->$constname) && !empty($sync_info[$key])?'dcloud-sync-info':'dcloud-nosync-info'); ?>">
						<?php echo $form->textwithtooltip('',$langs->trans("DcloudLastSynchronization",$lastsync_date,$lastsync_user),2,1,img_picto('','tick', 'id="sync_info_'.$constname.'"'),'dcloud-button-info'.(!empty($conf->global->$enabled) && !empty($conf->global->$constname) && !empty($sync_info[$key]) && !empty($conf->global->DROPBOX_ACCESS_TOKEN)?'':' hideobject'),2); ?>
					</div>
				</div>
				<div class="tagtd valign-middle text-align-center">
				<?php
				$input = array(
						'disabledenabled' => array(
								$constname
						),
						'disabled' => getDependencyModulesArray($key, null, true),
						'showhide' => getDependencyModulesArray($key),
						'del' => getDependencyModulesArray($key, $constname),
						'hidebutton' => array(
								$constname
						)
				);
				echo ajax_dcloudconstantonoff($enabled, $input);
				?>
				</div>
			</div>
		</div>
<?php } } ?>
<!-- END PHP TEMPLATE ADMIN ROOT MODULES -->