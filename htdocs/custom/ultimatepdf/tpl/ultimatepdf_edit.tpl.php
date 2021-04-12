<?php
/* Copyright (C) 2009-2012 Regis Houssin <regis.houssin@capnetworks.com>
 * Copyright (C) 2011-2016 Philippe Grand <philippe.grand@atoo-net.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
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
?>
 
<!-- BEGIN PHP TEMPLATE -->
<?php require_once DOL_DOCUMENT_ROOT . '/core/class/html.form.class.php'; ?>
<?php require_once DOL_DOCUMENT_ROOT.'/core/class/html.formfile.class.php'; ?>
<?php $form = new Form($this->db); ?>

<form name="form_index" action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="token" value="<?php echo $_SESSION['newtoken']; ?>" />
<input type="hidden" name="action" value="" />
<input type="hidden" name="id" value="<?php echo GETPOST('id'); ?>" />

<?php $var=true; ?>
<div align="center" class="info">
	<em><b><?php echo $langs->trans("CreateYourModel"); ?></em></b>
</div>
<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("DesignInfo"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><span class="fieldrequired"><?php echo $langs->trans("Label"); ?></span></td>
	<td><input name="label" size="30" value="<?php echo $this->tpl['label']; ?>" /></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td valign="top"><?php echo $langs->trans("Description"); ?></td>
	<td><textarea class="flat" name="description" cols="60" rows="<?php echo ROWS_3; ?>"><?php echo $this->tpl['description']; ?></textarea></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetFontToWhatYouWant"), $langs->trans("SetFontToWhatYouWantDescription")); ?></td>
	<td><?php echo $this->tpl['select_otherfont']; ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("UseBackGround"), $langs->trans("UseBackGroundDescription")); ?></td>
	<td><input name="usebackground" size="30" value="<?php echo $this->tpl['usebackground']; ?>" /></td>
</tr>
</table>
<br>
<div align="center" class="info">
	<em><b><?php echo $langs->trans("SetUpHeader"); ?></em></b>
</div>
<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetLogoHeigth"); ?></td>
	<td width="35%"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php global $mysoc; ?>
<?php  if (! empty($mysoc->logo))
    {
		$urllogo=DOL_URL_ROOT.'/viewimage.php?cache=1&amp;modulepart=companylogo&amp;file='.urlencode($mysoc->logo);
	}
?>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetLogoHeigth"), $langs->trans("SetLogoHeigthDescription")); ?></td>
	<td>
		<div id="container_logo" class="ui-widget-content">
			<div id="ui-state-active" class="ui-state-active"> 
				<img id="resizable-1" src="<?php echo (empty($urllogo)?DOL_URL_ROOT.'/public/theme/common/nophoto.png':$urllogo); ?>" />
			</div>
		</div>
	</td>
	<td><input type="text" name="logoheight" id="logoheight" size="30" placeholder="<?php echo $langs->trans("Height"); ?>" value="<?php echo $this->tpl['logoheight']; ?>" /><br><input type="text" name="logowidth" id="logowidth" size="30" placeholder="<?php echo $langs->trans("Width"); ?>" value="<?php echo $this->tpl['logowidth']; ?>" /><br><span id="resizable-2"></span></td>		
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SelectAnOtherlogo"); ?></td>
	<td width="35%"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SelectAnOtherlogo"), $langs->trans("OtherlogoDescription")); ?></td>
	<td><input type="file" id="otherlogo" name="otherlogo" size="40" value="<?php echo $this->tpl['select_otherlogo']; ?>" />
	<input type="hidden" id="otherlogo_file" name="otherlogo_file" value="<?php echo $this->tpl['select_otherlogo_file']; ?>" />
	</td>
	<td><button type="button" id="maj_otherlogo"><?php echo $langs->trans("Update"); ?></button>&nbsp;&nbsp;&nbsp;<?php echo '<a href="' . $_SERVER["PHP_SELF"] . '?action=removeotherlogo&id='.GETPOST('id').'">' . img_delete($langs->trans("Delete")) . '</a>';?></td>
</tr>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetLogoHeigth"), $langs->trans("SetLogoHeigthDescription")); ?></td>
	<td>
		<div id="container_otherlogo" class="ui-widget-content">
			<div id="ui-state-active" class="ui-state-active"> 
				<img id="resizable-3" src="<?php echo (empty($this->tpl['select_otherlogo'])?DOL_URL_ROOT.'/public/theme/common/nophoto.png':$this->tpl['select_otherlogo']); ?>" />			
			</div>
		</div>
	</td>
	<td><input type="text" name="otherlogoheight" id="otherlogoheight" size="30" placeholder="<?php echo $langs->trans("Height"); ?>" value="<?php echo $this->tpl['otherlogoheight']; ?>" /><br><input type="text" name="otherlogowidth" id="otherlogowidth" size="30" placeholder="<?php echo $langs->trans("Width"); ?>" value="<?php echo $this->tpl['otherlogowidth']; ?>" /><br><span id="resizable-4"></span></td>
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetAddressesBlocks"); ?></td>
	<td width="35%"><?php echo $langs->trans("Parameters"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Value"); ?></td>
</tr>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetAddressesBlocks"), $langs->trans("SetAddressesBlocksDescription")); ?></td>
	<td>
		<div id="container_AddressesBlocks" class="ui-widget-content">	
			<div id="sender_frame"> sender frame</div> 
			<div id="recipient_frame"> recipient frame</div> 
		</div>
	</td>
	<td><input type="text" name="widthrecbox" id="widthrecbox" size="30" placeholder="<?php echo $langs->trans("SenderBlockWidth"); ?>" value="<?php echo $this->tpl['widthrecbox']?93:$this->tpl['widthrecbox']; ?>" /><span id="resizable-24"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>		
</tr>
</table>
<br>

<div align="center" class="info" >
	<em><b><?php echo $langs->trans("SetCoreBloc"); ?></em></b>
</div>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("DesignInfo"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $langs->trans("BackgroundColorByDefault"); ?></td>
	<td><?php echo $this->tpl['select_bgcolor']; ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetOpacityForBackgroundColor"),$langs->trans("SetOpacityForBackgroundColorDescription")); ?></td>
	<td><input type="text" name="opacity" id="opacity" size="12" value="<?php echo $this->tpl['select_opacity']; ?>" /></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $langs->trans("BorderColorByDefault"); ?></td>
	<td><?php echo $this->tpl['select_bordercolor']; ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $langs->trans("SetBorderToDashDotted"); ?></td>
	<td><?php echo $this->tpl['select_dashdotted']; ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $langs->trans("TextcolorByDefault"); ?></td>
	<td><?php echo $this->tpl['select_textcolor']; ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $langs->trans("QRcodeColorByDefault"); ?></td>
	<td><?php echo $this->tpl['select_qrcodecolor']; ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("HideByDefaultProductTvaInsideUltimatepdf"), $langs->trans("SelectWithoutVatDescription")); ?></td>
	<td><?php echo $this->tpl['select_withoutvat']; ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetInvertSenderRecipient"), $langs->trans("SetInvertSenderRecipientDescription")); ?></td>
	<td><?php echo $this->tpl['invertSenderRecipient']; ?></td>
</tr>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetPdfMargin"); ?></td>
	<td><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetPdfMargin"), $langs->trans("SetPdfMarginDescription")); ?></td>
	<td>
		<div id="container2" class="ui-widget-content">
			<div id="resizable-5" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetPdfMargin"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="marge_gauche" id="marge_gauche" size="30" value="<?php echo $this->tpl['marge_gauche']; ?>" /><br><input type="text" name="marge_droite" id="marge_droite" size="30" value="<?php echo $this->tpl['marge_droite']; ?>" /><br><input type="text" name="marge_haute" id="marge_haute" size="30" value="<?php echo $this->tpl['marge_haute']; ?>" /><br><input type="text" name="marge_basse" id="marge_basse" size="30" value="<?php echo $this->tpl['marge_basse']; ?>" /><br><span id="resizable-6"></span></td>	
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetNumberingColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetNumberingWidth"), $langs->trans("SetNumberingWidthDescription")); ?></td>
	<td>
		<div id="container3" class="ui-widget-content">
			<div id="resizable-13" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetNumberingWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthnumbering" id="widthnumbering" size="30"  placeholder="<?php echo $langs->trans("Width"); ?>" value="<?php echo $this->tpl['widthnumbering']; ?>" /><br><span id="resizable-14"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>	
</tr>
</table>
<br> 

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetRefColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SelectWithRef"), $langs->trans("SelectWithRefDescription")); ?></td>
	<td colspan="3"><?php echo $this->tpl['select_withref']; ?></td>
</tr>
<br>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetRefWidth"), $langs->trans("SetRefWidthDescription")); ?></td>
	<td>
		<div id="container4" class="ui-widget-content">
			<div id="resizable-7" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetRefWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthref" id="widthref" size="30" value="<?php echo $this->tpl['select_widthref']; ?>" /><br><span id="resizable-8"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetDescColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetDescWidth"), $langs->trans("SetDescWidthDescription")); ?></td>
	<td>
		<div id="container_desc" class="ui-widget-content">
			<div id="resizable_desc" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetDescWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthdesc" id="widthdesc" size="30" value="<?php echo $this->tpl['widthdesc']; ?>" /><br><span id="resizable_desc2"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>	
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetImageColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>
<br>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetImageWidth"), $langs->trans("SetImageWidthDescription")); ?></td>
	<td>
		<div id="container4" class="ui-widget-content">
			<div id="resizable-9" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetImageWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="imglinesize" id="imglinesize" size="30" value="<?php echo $this->tpl['imglinesize']; ?>" /><br><span id="resizable-10"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetTvaColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetTvaWidth"), $langs->trans("SetTvaWidthDescription")); ?></td>
	<td>
		<div id="container6" class="ui-widget-content">
			<div id="resizable-15" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetTvaWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthvat" id="widthvat" size="30" placeholder="<?php echo $langs->trans("Width"); ?>" value="<?php echo $this->tpl['widthvat']; ?>" /><br><span id="resizable-16"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>	
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetUpColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetUpWidth"), $langs->trans("SetUpWidthDescription")); ?></td>
	<td>
		<div id="container7" class="ui-widget-content">
			<div id="resizable-17" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetUpWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthup" id="widthup" size="30" value="<?php echo $this->tpl['widthup']; ?>" /><br><span id="resizable-18"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>	
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetQtyColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetQtyWidth"), $langs->trans("SetQtyWidthDescription")); ?></td>
	<td>
		<div id="container8" class="ui-widget-content">
			<div id="resizable-19" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetQtyWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthqty" id="widthqty" size="30" value="<?php echo $this->tpl['widthqty']; ?>" /><br><span id="resizable-20"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>	
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetUnitColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetUnitWidth"), $langs->trans("SetUnitWidthDescription")); ?></td>
	<td>
		<div id="container_unit" class="ui-widget-content">
			<div id="resizable_unit" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetUnitWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthunit" id="widthunit" size="30" value="<?php echo $this->tpl['widthunit']; ?>" /><br><span id="resizable_unit2"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>	
</tr>
</table>
<br>

<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("SetDiscountColumn"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Parameters"); ?></td>
	<td><?php echo $langs->trans("Value"); ?></td>
</tr>

<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetDiscountWidth"), $langs->trans("SetDiscountWidthDescription")); ?></td>
	<td>
		<div id="container9" class="ui-widget-content">
			<div id="resizable-21" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("SetDiscountWidth"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="widthdiscount" id="widthdiscount" size="30" value="<?php echo $this->tpl['widthdiscount']; ?>" /><br><span id="resizable-22"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>	
</tr>
</table>
<br>

<div align="center" class="info" >
	<em><b><?php echo $langs->trans("SetFooterBloc"); ?></em></b>
</div>
<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("Parameters"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Value"); ?></td>
	<td><?php echo $langs->trans("Action"); ?></td>
</tr>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $langs->trans("SetFontSizeForFreeText"); ?></td>
	<td><input name="freetextfontsize" id="freetextfontsize" class="changeMe" size="25" value="<?php echo $this->tpl['select_freetextfontsize']; ?>" /></td><td id="freetextfontsize_text" style="font-size:<?php echo $this->tpl['select_freetextfontsize'].'px'; ?>"><?php echo $langs->trans("Lorem ipsum dolor sit amet, consectetur adipiscing elit"); ?></td><td><button type="button" id="maj_freetext"><?php echo $langs->trans("Update"); ?></button></td>
</tr>
<br>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("SetHeightForFreeText"), $langs->trans("SetHeightForFreeTextDescription")); ?></td>
	<td>
		<div id="container5" class="ui-widget-content">
			<div id="resizable-11" class="ui-state-active"> 				
				<h3 class="ui-widget-header"><?php echo $langs->trans("Resizable"); ?></h3>
			</div>
		</div>
	</td>
	<td><input type="text" name="heightforfreetext" id="heightforfreetext" size="30" value="<?php echo $this->tpl['select_heightforfreetext']; ?>" /><br><span id="resizable-12"></span></td>
	<td><button type="button" id="maj_img"><?php echo $langs->trans("Update"); ?></button></td>
</tr>
</table>
<br>
<table class="noborder">
<tr class="liste_titre">
	<td width="35%"><?php echo $langs->trans("Parameters"); ?></td>
	<td colspan="2"><?php echo $langs->trans("Value"); ?></td>
	<td><?php echo $langs->trans("Action"); ?></td>
</tr>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $form->textwithpicto($langs->trans("PDFFooterAddressForging"), $langs->trans("ShowDetailsInPDFPageFoot")); ?></td>
	<td colspan="3"><?php echo $this->tpl['select_showdetails']; ?></td>
</tr>
<br>
<?php $var=!$var; ?>
<tr <?php echo $bc[$var]; ?>>
	<td><?php echo $langs->trans("SetFooterTextcolorByDefault"); ?></td>
	<td ><?php echo $this->tpl['select_footertextcolor']; ?></td>
	<td ><?php echo '&nbsp'; ?></td>
</tr>
</table>
<br>

<div class="tabsAction">
<input type="submit" class="butAction linkobject" name="update" value="<?php echo $langs->trans('Update'); ?>" />
<input type="submit" class="butAction linkobject" name="cancel" value="<?php echo $langs->trans("Cancel"); ?>" />
</div>
<!-- Javascript -->
    <script>
        $(function() {
		const k=72/254;
			$( "#resizable-1" ).css({
				height: function(){return Math.round(<?php echo (empty($this->tpl['logoheight'])?1:$this->tpl['logoheight']); ?>*1/k);},
				width: function(){return Math.round(<?php echo (empty($this->tpl['logowidth'])?1:$this->tpl['logowidth']); ?>*1/k);}
				});
            $( "#resizable-1" ).resizable({ 
				containment: "#container_logo",
			    minHeight: 75,
			    minWidth: 150,
				maxHeight: 150,
				maxWidth: 450,
                resize: function (event, ui)
                {
					$("#resizable-2").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(ui.size.width*k) + "mm" +
						", <?php echo $langs->trans("Height"); ?> = " + Math.round(ui.size.height*k) + "mm");
					$("#logoheight").val(Math.round(ui.size.height*k));
					$("#logowidth").val(Math.round(ui.size.width*k));
                }
            });
        });
    </script>
	<script>
        $(function() {
		const k=72/254;
			$( "#resizable-3" ).css({
				height: function(){return Math.round(<?php echo (empty($this->tpl['otherlogoheight'])?1:$this->tpl['otherlogoheight']); ?>*1/k);},
				width: function(){return Math.round(<?php echo (empty($this->tpl['otherlogowidth'])?1:$this->tpl['otherlogowidth']); ?>*1/k);}
				});
            $( "#resizable-3" ).resizable({ 
				containment: "#container_otherlogo",
			    minHeight: 75,
			    minWidth: 150,
				maxHeight: 150,
				maxWidth: 450,
                resize: function (event, ui)
                {
					$("#resizable-4").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(ui.size.width*k) + "mm" +
						", <?php echo $langs->trans("Height"); ?> = " + Math.round(ui.size.height*k) + "mm");
					$("#otherlogoheight").val(Math.round(ui.size.height*k));
					$("#otherlogowidth").val(Math.round(ui.size.width*k));
                }
            });	
			$('#maj_otherlogo').click(function() {
				var files = $('#otherlogo')[0].files;

				if (files.length > 0) {
					// On part du principe qu'il n'y qu'un seul fichier
					// étant donné que l'on a pas renseigné l'attribut "multiple"
					var file = files[0];
					$image_preview = $('#resizable-3');
		 
					// Ici on injecte les informations recoltées sur le fichier pour l'utilisateur
					//$image_preview.find('.thumbnail').removeClass('hidden');
					$image_preview.attr('src', window.URL.createObjectURL(file));
					//$image_preview.find('h4').html(file.name);
					//$image_preview.find('.caption p:first').html(file.size +' bytes');
				}
			});
        });	
    </script>

	<script>
		$(function() {
		
			$( "#resizable-5" ).css({
			posleft: function(){return Math.round(<?php echo (empty($this->tpl['marge_gauche'])?1:$this->tpl['marge_gauche']); ?>);},
			posright: function(){return Math.round(<?php echo (empty($this->tpl['marge_droite'])?1:$this->tpl['marge_droite']); ?>);},
			postop: function(){return Math.round(<?php echo (empty($this->tpl['marge_haute'])?1:$this->tpl['marge_haute']); ?>);},
			posbottom: function(){return Math.round(<?php echo (empty($this->tpl['marge_basse'])?1:$this->tpl['marge_basse']); ?>);}
			});
			$("#resizable-5").resizable({ 
			containment: "#container2",
			minHeight: 257,
			minWidth: 170,
			maxHeight: 297,
			maxWidth: 210,
			resize: function (event, ui)
				{
				var posleft=ui.position.left;
				var posright=210 - ui.size.width - ui.position.left;
				var postop=ui.position.top;			
				var posbottom=297 - ui.size.height - ui.position.top;		
					if(posleft < 0)
						posleft=0;
					if(posright < 0)
						posright=0;
					if(postop < 0)
						postop=0;
					if(posbottom < 0)
						posbottom=0;	
					$("#resizable-6").text ("<?php echo $langs->trans("MargeGauche"); ?> = " + Math.round(posleft) + "mm" +
						", <?php echo $langs->trans("MargeDroite"); ?> = " + Math.round(posright) + "mm" +
						", <?php echo $langs->trans("MargeHaute"); ?> = " + Math.round(postop) + "mm" +
						", <?php echo $langs->trans("MargeBasse"); ?> = " + Math.round(posbottom) + "mm");								
					$("#marge_gauche").val(Math.round(posleft));
					$("#marge_droite").val(Math.round(posright));
					$("#marge_haute").val(Math.round(postop));
					$("#marge_basse").val(Math.round(posbottom));
				},
			handles: "n, e, s, w" });
			var handles = $("#resizable-5").resizable("option", "handles");
			$("#resizable-5").resizable("option", "handles", "n, e, s, w");
			$("#marge_gauche").change(function() {
				var margeleft = parseInt($(this).val());
				var margecurrentleft = parseInt($('#resizable-5').css('left').replace('px',''));
				var margewidth = parseInt($('#resizable-5').css('width').replace('px',''));
				var blockwidth = (margecurrentleft + margewidth) - margeleft;
				$('#resizable-5').css({'left': margeleft + 'px', 'width': blockwidth + 'px'});
				$('#resizable-6').text("<?php echo $langs->trans("MargeGauche"); ?> = " + margeleft + 'px');
			});
			$("#marge_droite").change(function() {
				var margeright = parseInt($(this).val());
				var margecurrentright = parseInt($('#resizable-5').css('right').replace('px',''));
				var margewidth = parseInt($('#resizable-5').css('width').replace('px',''));
				var blockwidth = (margecurrentright + margewidth) - margeright;
				$('#resizable-5').css({'right': margeright + 'px', 'width': blockwidth + 'px'});
				$('#resizable-6').text("<?php echo $langs->trans("MargeDroite"); ?> = " + margeright + 'px');
			});
			$("#marge_haute").change(function() {
				var margetop = parseInt($(this).val());
				var margecurrenttop = parseInt($('#resizable-5').css('top').replace('px',''));
				var margeheight = parseInt($('#resizable-5').css('height').replace('px',''));
				var blockheight = (margecurrenttop + margeheight) - margetop;
				$('#resizable-5').css({'top': margetop + 'px', 'height': blockheight + 'px'});
				$('#resizable-6').text("<?php echo $langs->trans("MargeHaute"); ?> = " + margetop + 'px');
			});
			$("#marge_basse").change(function() {
				var margebottom = parseInt($(this).val());
				var margecurrentbottom = parseInt($('#resizable-5').css('bottom').replace('px',''));
				var margeheight = parseInt($('#resizable-5').css('height').replace('px',''));
				var blockheight = (margecurrentbottom + margeheight) - margebottom;
				$('#resizable-5').css({'bottom': margebottom + 'px', 'height': blockheight + 'px'});
				$('#resizable-6').text("<?php echo $langs->trans("MargeBasse"); ?> = " + margebottom + 'px');
			});
		});
	</script>
	<script>
        $(function() {
			
            $( "#resizable-7" ).resizable({ 
				containment: "#container4",
				minHeight: 297,
			    minWidth: 10,
				maxWidth: 80,
                resize: function (event, ui)
                {
					var widthref=ui.size.width;
					$("#resizable-8").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthref) + "px");
					$("#widthref").val(Math.round(widthref));
                }
			});				
            $("#widthref").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable-7').css({'width': blockwidth + 'px'});
			$('#resizable-8').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});
		});
    </script>
	<script>
        $(function() {
		
			$( "#resizable-9" ).css({
				blockwidth: function(){return Math.round(<?php echo (empty($this->tpl['imglinesize'])?1:$this->tpl['imglinesize']); ?>);}
				});
            $( "#resizable-9" ).resizable({ 
				containment: "#container5",
				minHeight: 297,
			    minWidth: 16,
				maxWidth: 80,
                resize: function (event, ui)
                {
					var imglinesize=ui.size.width;
					$("#resizable-10").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(imglinesize) + "px");
					$("#imglinesize").val(Math.round(imglinesize));
                },
				handles: "w,sw" });
			var handles = $("#resizable-9").resizable("option", "handles");
			$("#resizable-9").resizable("option", "handles", "w,sw");
			$('.ui-resizable-sw').addClass('ui-icon ui-icon-gripsmall-diagonal-sw');
			$("#imglinesize").change(function() {
			var blockwidth = parseInt($(this).val());
			var blockwidthcurrent = parseInt($('#resizable-9').css('width').replace('px',''));
			var blockleftcurrent = parseInt($('#resizable-9').css('left').replace('px',''));
			var blockleft = blockleftcurrent + (blockwidthcurrent - blockwidth);
			$('#resizable-9').css({'width': blockwidth + 'px'});
			$('#resizable-9').css({'left': blockleft + 'px'});
			$('#resizable-10').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});
		});
    </script>
	<script>
        $(function() {
		
			$( "#resizable-11" ).css({
				blockheight: function(){return Math.round(<?php echo (empty($this->tpl['select_heightforfreetext'])?1:$this->tpl['select_heightforfreetext']); ?>);}
				});
            $( "#resizable-11" ).resizable({ 
				containment: "#container5",
			    minHeight: 10,
			    minWidth: 210,
				maxHeight: 80,
                resize: function (event, ui)
                {
					$("#resizable-12").text ("<?php echo $langs->trans("Height"); ?> = " + Math.round(ui.size.height) + "px");
					$("#heightforfreetext").val(Math.round(ui.size.height));
                },
				handles: "n" });
			var handles = $("#resizable-11").resizable("option", "handles");
			$("#resizable-11").resizable("option", "handles", "n"); 
			$("#heightforfreetext").change(function() {	
			var blockheight = parseInt($(this).val());
			var blockheightcurrent = parseInt($('#resizable-11').css('height').replace('px',''));
			var blocktopcurrent = parseInt($('#resizable-11').css('top').replace('px',''));
			var blocktop = blocktopcurrent + (blockheightcurrent - blockheight);
			$('#resizable-11').css({'height': blockheight + 'px'});
			$('#resizable-11').css({'top': blocktop + 'px'});
			$('#resizable-12').text("<?php echo $langs->trans("Height"); ?> = " + blockheight + 'px');
			});	
			$('#maj_freetext').click(function() {
			$('#freetextfontsize_text').css("font-size", $("#freetextfontsize").val() + "px");
			});
		});
    </script>
	<script>
        $(function() {
			
            $( "#resizable-13" ).resizable({ 
				containment: "#container3",
				minHeight: 297,
			    minWidth: 5,
				maxWidth: 15,
                resize: function (event, ui)
                {
					var widthnumbering=ui.size.width;
					$("#resizable-14").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthnumbering) + "px");
					$("#widthnumbering").val(Math.round(widthnumbering));
                }
			});				
            $("#widthnumbering").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable-13').css({'width': blockwidth + 'px'});
			$('#resizable-14').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});
		});
    </script>
	<script>
        $(function() {
            $( "#resizable-15" ).resizable({ 
				containment: "#container6",
				minHeight: 297,
			    minWidth: 5,
				maxWidth: 20,
                resize: function (event, ui)
                {
					var widthvat=ui.size.width;
					$("#resizable-16").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthvat) + "px");
					$("#widthvat").val(Math.round(widthvat));
                }
			});	
			$("#widthvat").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable-15').css({'width': blockwidth + 'px'});
			$('#resizable-16').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});			
        });
    </script>
	<script>
        $(function() {
            $( "#resizable-17" ).resizable({ 
				containment: "#container7",
				minHeight: 297,
			    minWidth: 20,
				maxWidth: 30,
                resize: function (event, ui)
                {
					var widthup=ui.size.width;
					$("#resizable-18").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthup) + "px");
					$("#widthup").val(Math.round(widthup));
                }
			});
			$("#widthup").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable-17').css({'width': blockwidth + 'px'});
			$('#resizable-18').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});				
        });
    </script>
	<script>
        $(function() {
            $( "#resizable-19" ).resizable({ 
				containment: "#container8",
				minHeight: 297,
			    minWidth: 10,
				maxWidth: 30,
                resize: function (event, ui)
                {
					var widthqty=ui.size.width;
					$("#resizable-20").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthqty) + "px");
					$("#widthqty").val(Math.round(widthqty));
                }
			});
			$("#widthqty").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable-19').css({'width': blockwidth + 'px'});
			$('#resizable-20').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});				
        });
    </script>
	<script>
        $(function() {
            $( "#resizable_unit" ).resizable({ 
				containment: "#container_unit",
				minHeight: 297,
			    minWidth: 10,
				maxWidth: 15,
                resize: function (event, ui)
                {
					var widthunit=ui.size.width;
					$("#resizable_unit2").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthunit) + "px");
					$("#widthunit").val(Math.round(widthunit));
                }
			});
			$("#widthunit").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable_unit').css({'width': blockwidth + 'px'});
			$('#resizable_unit2').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});				
        });
    </script>
	<script>
        $(function() {
            $( "#resizable-21" ).resizable({ 
				containment: "#container9",
				minHeight: 297,
			    minWidth: 10,
				maxWidth: 30,
                resize: function (event, ui)
                {
					var widthdiscount=ui.size.width;
					$("#resizable-22").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthdiscount) + "px");
					$("#widthdiscount").val(Math.round(widthdiscount));
                }
			});
			$("#widthdiscount").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable-21').css({'width': blockwidth + 'px'});
			$('#resizable-22').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});				
        });
    </script>
	<script>
        $(function() {
            $( "#resizable_desc" ).resizable({ 
				containment: "#container_desc",
				minHeight: 290,
			    minWidth: 40,
				maxWidth: 190,
				maxHeight: 295,
				grid: 50,
                resize: function (event, ui)
                {
					var widthdesc=ui.size.width;
					$("#resizable_desc2").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthdesc) + "px");
					$("#widthdesc").val(Math.round(widthdesc));
                }
			});
			$("#widthdesc").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#resizable_desc').css({'width': blockwidth + 'px'});
			$('#resizable_desc2').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});				
        });
    </script>
	<script>
        $(function() {
           $("#sender_frame").resizable({
			   maxWidth: 120,
			   minWidth: 70
		   });
			$('#sender_frame').resize(function(event, ui){
				var widthrecbox=ui.size.width;
				$("#resizable-24").text ("<?php echo $langs->trans("Width"); ?> = " + Math.round(widthrecbox) + "px");
				$("#widthrecbox").val(Math.round(widthrecbox));
			    $('#recipient_frame').width($("#container_AddressesBlocks").width()-$("#sender_frame").width()); 
			});
			$(window).resize(function(){
			   $('#recipient_frame').width($("#container_AddressesBlocks").width()-$("#sender_frame").width()); 
			   $('#sender_frame').height($("#container_AddressesBlocks").height()); 
			});
			   
			$("#widthrecbox").change(function() {	
			var blockwidth = parseInt($(this).val());
			$('#sender_frame').css({'width': blockwidth + 'px'});
			$('#resizable-24').text("<?php echo $langs->trans("Width"); ?> = " + blockwidth + 'px');
			});				
        });
    </script>

</form>

<!-- END PHP TEMPLATE -->