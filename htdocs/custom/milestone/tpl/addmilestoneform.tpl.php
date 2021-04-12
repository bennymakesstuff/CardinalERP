<?php
/* Copyright (C) 2010-2016 Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2014-2016 Philippe Grand <philippe.grand@atoo-net.com>
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
 *
 */

$usemargins=0;
$colspanbutton=4;

if (! empty($conf->global->MAIN_VIEW_LINE_NUMBER))
{
	$colspantitle=10;
	$colspanlabel=7;
	$colspandesc=7;
}
else
{
	$colspantitle=9;
	$colspanlabel=5;
	$colspandesc=5;
}

if (! empty($conf->margin->enabled) && ! empty($GLOBALS['object']->element) && in_array($GLOBALS['object']->element,array('facture','propal', 'askpricesupplier','commande')))
{
	$usemargins=1;
	$colspantitle+=3;
	$colspanlabel++;
	$colspandesc+=1;
}
if (! empty($usemargins) && ! empty($conf->global->DISPLAY_MARGIN_RATES) && $user->rights->margins->liretous)
{
	$colspantitle+=2;
	$colspanlabel++;
	$colspandesc+=1;
}
if (! empty($usemargins) && ! empty($conf->global->DISPLAY_MARK_RATES) && $user->rights->margins->liretous)
{
	$colspantitle+=2;
	$colspanlabel++;
	$colspandesc+=1;
}

?>

<!-- BEGIN PHP TEMPLATE -->
<script type="text/javascript">
$(document).ready(function () {
	$("#milestone_label").focus(function() {
		hideMessage("milestone_label","<?php echo $langs->transnoentities('Label'); ?>");
    });
    $("#milestone_label").blur(function() {
        displayMessage("milestone_label","<?php echo $langs->transnoentities('Label'); ?>");
    });
	displayMessage("milestone_label","<?php echo $langs->transnoentities('Label'); ?>");
	$("#milestone_label").css("color","grey");
})
</script>

<tr class="liste_titre nodrag nodrop">
	<td colspan="<?php echo $colspantitle; ?>"><?php echo $langs->trans('AddMilestone'); ?></td>
</tr>

<input type="hidden" name="special_code" value="1790">
<input type="hidden" name="product_type" value="9">

<tr <?php echo $GLOBALS['bcnd'][$GLOBALS['var']]; ?>>
	<td colspan="<?php echo $colspanlabel; ?>">
	<input size="30" type="text" id="milestone_label" name="milestone_label" value="<?php echo $_POST["milestone_label"]; ?>">
	<input type="checkbox" name="pagebreak" value="1" /> <?php echo $langs->transnoentities('AddPageBreak'); ?>
	</td>

	<td align="center" valign="middle" rowspan="2" colspan="<?php echo $colspanbutton; ?>">
	<input type="submit" class="button" value="<?php echo $langs->trans('Add'); ?>" name="addmilestone">
	</td>
</tr>

<tr <?php echo $GLOBALS['bcnd'][$GLOBALS['var']]; ?>>
	<td colspan="<?php echo $colspandesc; ?>">
	<?php
	require_once(DOL_DOCUMENT_ROOT."/core/class/doleditor.class.php");
    $nbrows=ROWS_2;
    if (! empty($conf->global->MAIN_INPUT_DESC_HEIGHT)) $nbrows=$conf->global->MAIN_INPUT_DESC_HEIGHT;
	$doleditor=new DolEditor('milestone_desc',GETPOST('milestone_desc'),'',100,'dolibarr_details','',false,true,$conf->fckeditor->enabled && $conf->global->FCKEDITOR_ENABLE_DETAILS,$nbrows,'70%');
	$doleditor->Create();
	?>
	</td>
</tr>
<!-- END PHP TEMPLATE -->
