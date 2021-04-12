<?php
/* Copyright (C) 2014-2018 Regis Houssin <regis.houssin@capnetworks.com>
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

<!-- START PHP TEMPLATE ADMIN PARAMETERS -->
<?php if (!empty($conf->global->DROPBOX_MAIN_CUSTOMER_ROOT_ENABLED) || !empty($conf->global->DROPBOX_MAIN_SUPPLIER_ROOT_ENABLED)) { ?>
<div class="table-border centpercent">
	<div class="table-border-row <?php echo $var?"pair":"impair"; ?>">
		<div class="tagtd padding-left5 seventy-percent" align="left"><?php echo $langs->trans("DcloudShowThirdpartyDropboxTab"); ?><br></div>
		<div class="tagtd valign-middle button-align-right">
		<?php
		echo ajax_dcloudconstantonoff('DCLOUD_SHOW_THIRDPARTY_DROPBOX_TAB');
		?>
		</div>
	</div>
</div>
<?php $var=!$var; ?>
<div class="table-border centpercent">
	<div class="table-border-row <?php echo $var?"pair":"impair"; ?>">
		<div class="tagtd padding-left5 seventy-percent" align="left"><?php echo $langs->trans("DcloudShowThirdpartyNativeTab"); ?><br></div>
		<div class="tagtd valign-middle button-align-right">
		<?php
		echo ajax_dcloudconstantonoff('DCLOUD_SHOW_THIRDPARTY_NATIVE_TAB');
		?>
		</div>
	</div>
</div>
<?php $var=!$var; ?>
<?php } ?>
<?php if (!empty($conf->global->DROPBOX_MAIN_PRODUCT_ROOT_ENABLED) || !empty($conf->global->DROPBOX_MAIN_SERVICE_ROOT_ENABLED)) { ?>
<div class="table-border centpercent">
	<div class="table-border-row <?php echo $var?"pair":"impair"; ?>">
		<div class="tagtd padding-left5 seventy-percent" align="left"><?php echo $langs->trans("DcloudShowProductDropboxTab"); ?><br></div>
		<div class="tagtd valign-middle button-align-right">
		<?php
		echo ajax_dcloudconstantonoff('DCLOUD_SHOW_PRODUCT_DROPBOX_TAB');
		?>
		</div>
	</div>
</div>
<?php $var=!$var; ?>
<div class="table-border centpercent">
	<div class="table-border-row <?php echo $var?"pair":"impair"; ?>">
		<div class="tagtd padding-left5 seventy-percent" align="left"><?php echo $langs->trans("DcloudShowProductNativeTab"); ?><br></div>
		<div class="tagtd valign-middle button-align-right">
		<?php
		echo ajax_dcloudconstantonoff('DCLOUD_SHOW_PRODUCT_NATIVE_TAB');
		?>
		</div>
	</div>
</div>
<?php } ?>
<!-- END PHP TEMPLATE ADMIN PARAMETERS -->