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

?>

<!-- BEGIN PHP TEMPLATE FOR SYNCHRONIZATION -->
<div id="dcloud-sync-cancel" title="<?php echo $langs->trans('ErrorElementPathNotDefined'); ?>" style="display: none;">
	<p><?php echo img_warning().' '.$langs->trans('ErrorElementPathNotDefinedDescription'); ?></p>
</div>
<div id="dcloud-sync-confirm" title="<?php echo $langs->trans('DcloudSyncConfirm'); ?>" style="display: none;">
	<p><?php echo img_info().' '.$langs->trans('DcloudSyncConfirmDescription'); ?></p>
</div>
<script type="text/javascript">
$(document).ready(function() {
	$(document).on("click", ".dcloud-button-sync", function() {
		var url = '<?php echo dol_buildpath('/dcloud/core/ajax/dropbox.php', 1); ?>';
		var elementId = $(this).attr('id').replace("sync_button_","");
		var elementName = elementId.replace("DROPBOX_MAIN_","").replace("_ROOT","").toLowerCase();
		var elementPath = $('#' + elementId).val();

		if (elementPath.length == 0)
		{
			$( "#dcloud-sync-cancel" ).dialog({
				resizable: false,
				width: 450,
				modal: true,
				buttons: {
					"<?php echo dol_escape_js($langs->transnoentities('Cancel')); ?>": function() {
						$( "#dcloud-sync-cancel" ).dialog( "close" );
					}
				}
			});
		}
		else
		{
			$( "#dcloud-sync-confirm" ).dialog({
				resizable: false,
				width: 450,
				modal: true,
				open: function() {
					$('.ui-dialog-buttonset > button:last').focus();
				},
				buttons: {
					"<?php echo dol_escape_js($langs->transnoentities('Start')); ?>": function() {
						$( "#dcloud-sync-confirm" ).dialog( "close" );
						$.pleaseBePatient("<?php echo $langs->trans('PleaseBePatient'); ?>");
						$.get(url, {
							operation : 'admin_getElementFiles',
							element : elementName
						},
						function(data) {
							if (data.num == 0) {
								$.unblockUI();
							}
							else {
								$.each(data.nodes, function( key, values ) {
									$.each(values.files, function( num, value ) {
										var json_values = JSON.stringify(value, null, 2);
										var thirdparty = (values.thirdpartyname !== null && values.thirdpartyname !== undefined) ? '&thirdpartyname=' + Base64.encode(values.thirdpartyname) + '&customer=' + values.customer + '&supplier=' + values.supplier : '';
										$.ajax({
											url: url + '?operation=admin_syncFiles&element=' + elementName + thirdparty + '&ref=' + Base64.encode(key) + '&values=' + Base64.encode(json_values),
											async: false,
											success: function(result) {
												$('.blockMsg').html('<?php echo $langs->trans('CurrentCard'); ?>' + ' : ' + key + '<br>(' + result.percent + '% <?php echo $langs->trans('Performed'); ?>)');
												if (result.percent == '100') {
													$.unblockUI();
													window.location = window.location.pathname;
												}
											}
										});
									});
								});
							}
						});
					},
					"<?php echo dol_escape_js($langs->transnoentities('Cancel')); ?>": function() {
						$( "#dcloud-sync-confirm" ).dialog( "close" );
					}
				}
			});
		}
	});
});
</script>
<!-- END PHP TEMPLATE FOR SYNCHRONIZATION -->
