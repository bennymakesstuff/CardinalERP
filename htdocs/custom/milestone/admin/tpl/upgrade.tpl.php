<?php
/* Copyright (C) 2016	Regis Houssin	<regis.houssin@capnetworks.com>
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

<!-- BEGIN AJAX TEMPLATE FOR MILESTONE UPGRADE -->
<div id="milestone-upgrade-confirm" title="<?php echo $langs->trans('MilestoneUpgradeConfirm'); ?>" style="display: none;">
	<p><?php echo img_info().' '.$langs->trans('MilestoneUpgradeConfirmDescription'); ?></p>
	<p><input type="radio" name="milestone_upgrade" value="2" checked> <?php echo $langs->trans('MilestoneUpgradeSimulation'); ?></p>
	<p><input type="radio" name="milestone_upgrade" value="1"> <?php echo $langs->trans('MilestoneUpgrade'); ?></p>
	<p style="color: red;"><?php echo $langs->trans('MilestoneUpgradeConfirmAlert'); ?></p>
</div>
<?php $_SESSION['MILESTONE_UPGRADE_STATUS']=array(); ?>
<script type="text/javascript">
$(document).ready(function() {
	$( ".divButAction" ).on("click", ".milestone-button-upgrade", function() {
		var url = '<?php echo dol_buildpath('/milestone/core/ajax/milestone.php', 1); ?>';
		var elementId = $(this).attr('id');
		var elementPath = 'upgrade';
		$( "#milestone-upgrade-confirm" ).dialog({
			resizable: false,
			width: 480,
			modal: true,
			open: function() {
				$('.ui-dialog-buttonset > button:last').focus();
			},
			buttons: {
				"<?php echo dol_escape_js($langs->transnoentities('Validate')); ?>": function() {
					$( "#milestone-upgrade-confirm" ).dialog( "close" );
					var upgrade = $("input[name='milestone_upgrade']:checked").val();
					if (upgrade !== null && upgrade !== undefined) {
						$.pleaseBePatient("<?php echo $langs->trans('PleaseBePatient'); ?>");
						upgradeStep1(url,upgrade);
					}
				},
				"<?php echo dol_escape_js($langs->transnoentities('Cancel')); ?>": function() {
					$( "#milestone-upgrade-confirm" ).dialog( "close" );
				}
			}
		});
	});

	// Step 1: propal
	function upgradeStep1(url,upgrade) {
		var percent = 0;
		$.get(url, {
			operation : 'admin_getMilestoneElement',
			element : 'propal'
		},
		function(data) {
			$.each(data.milestones, function( key, values ) {
				$.ajax({
					url: url + '?operation=admin_upgradeMilestone&upgrade=' + upgrade + '&element=' + values.elementtype + '&fk_element=' + values.fk_element + '&label=' + Base64.encode(values.label) + '&options=' + Base64.encode(values.options),
					async: false,
					success: function(result) {
						$('.blockMsg').html('<?php echo $langs->trans('MilestonePropalMigration'); ?>' + ' (' + result.percent + '% <?php echo $langs->trans('Performed'); ?>)');
						percent = result.percent;
					}
				});
			});
			if (percent == 100 || (data.milestones !== undefined && (data.milestones === false || data.milestones.length == 0)))
			{
				upgradeStep2(url,upgrade);
			}
		});
	}
	// Step 2: order
	function upgradeStep2(url,upgrade) {
		var percent = 0;
		$.get(url, {
			operation : 'admin_getMilestoneElement',
			element : 'commande'
		},
		function(data) {
			$.each(data.milestones, function( key, values ) {
				$.ajax({
					url: url + '?operation=admin_upgradeMilestone&upgrade=' + upgrade + '&element=' + values.elementtype + '&fk_element=' + values.fk_element + '&label=' + Base64.encode(values.label) + '&options=' + Base64.encode(values.options),
					async: false,
					success: function(result) {
						$('.blockMsg').html('<?php echo $langs->trans('MilestoneOrderMigration'); ?>' + ' (' + result.percent + '% <?php echo $langs->trans('Performed'); ?>)');
						percent = result.percent;
					}
				});
			});
			if (percent == 100 || (data.milestones !== undefined && (data.milestones === false || data.milestones.length == 0)))
			{
				upgradeStep3(url,upgrade);
			}
		});
	}
	// Step 3: facture
	function upgradeStep3(url,upgrade) {
		var percent = 0;
		$.get(url, {
			operation : 'admin_getMilestoneElement',
			element : 'facture'
		},
		function(data) {
			$.each(data.milestones, function( key, values ) {
				$.ajax({
					url: url + '?operation=admin_upgradeMilestone&upgrade=' + upgrade + '&element=' + values.elementtype + '&fk_element=' + values.fk_element + '&label=' + Base64.encode(values.label) + '&options=' + Base64.encode(values.options),
					async: false,
					success: function(result) {
						$('.blockMsg').html('<?php echo $langs->trans('MilestoneInvoiceMigration'); ?>' + ' (' + result.percent + '% <?php echo $langs->trans('Performed'); ?>)');
						percent = result.percent;
					}
				});
			});
			if (percent == 100 || (data.milestones !== undefined && (data.milestones === false || data.milestones.length == 0)))
			{
				upgradeStep4(url,upgrade);
			}
		});
	}
	// Step 4: propal orphans
	function upgradeStep4(url,upgrade) {
		var percent = 0;
		$.get(url, {
			operation : 'admin_getOrphanChildsElement',
			element : 'propaldet'
		},
		function(data) {
			$.each(data.orphans, function( key, values ) {
				$.ajax({
					url: url + '?operation=admin_deleteOrphan&upgrade=' + upgrade + '&element=' + values.elementtype + '&rowid=' + values.rowid,
					async: false,
					success: function(result) {
						$('.blockMsg').html('<?php echo $langs->trans('DeleteOrphansProductLine'); ?>' + ' (' + result.percent + '% <?php echo $langs->trans('Performed'); ?>)');
						percent = result.percent;
					}
				});
			});
			if (percent == 100 || (data.orphans !== undefined && (data.orphans === false || data.orphans.length == 0)))
			{
				upgradeStep5(url,upgrade);
			}
		});
	}
	// Step 5: order orphans
	function upgradeStep5(url,upgrade) {
		var percent = 0;
		$.get(url, {
			operation : 'admin_getOrphanChildsElement',
			element : 'commandedet'
		},
		function(data) {
			$.each(data.orphans, function( key, values ) {
				$.ajax({
					url: url + '?operation=admin_deleteOrphan&upgrade=' + upgrade + '&element=' + values.elementtype + '&rowid=' + values.rowid,
					async: false,
					success: function(result) {
						$('.blockMsg').html('<?php echo $langs->trans('DeleteOrphansProductLine'); ?>' + ' (' + result.percent + '% <?php echo $langs->trans('Performed'); ?>)');
						percent = result.percent;
					}
				});
			});
			if (percent == 100 || (data.orphans !== undefined && (data.orphans === false || data.orphans.length == 0)))
			{
				upgradeStep6(url,upgrade);
			}
		});
	}
	// Step 6: facture
	function upgradeStep6(url,upgrade) {
		var percent = 0;
		$.get(url, {
			operation : 'admin_getOrphanChildsElement',
			element : 'facturedet'
		},
		function(data) {
			$.each(data.orphans, function( key, values ) {
				$.ajax({
					url: url + '?operation=admin_deleteOrphan&upgrade=' + upgrade + '&element=' + values.elementtype + '&rowid=' + values.rowid,
					async: false,
					success: function(result) {
						$('.blockMsg').html('<?php echo $langs->trans('DeleteOrphansProductLine'); ?>' + ' (' + result.percent + '% <?php echo $langs->trans('Performed'); ?>)');
						percent = result.percent;
					}
				});
			});
			if (percent == 100 || (data.orphans !== undefined && (data.orphans === false || data.orphans.length == 0)))
			{
				$.unblockUI();
				$.ajax({
					url: url + '?operation=admin_setMilestoneUpgradeStatus&upgrade=' + upgrade,
					async: false,
					success: function(result) {
						if (result.status == 'success')
							window.location = window.location.pathname;
					}
				});
			}
		});
	}
});
</script>
<!-- END AJAX TEMPLATE FOR MILESTONE UPGRADE -->
