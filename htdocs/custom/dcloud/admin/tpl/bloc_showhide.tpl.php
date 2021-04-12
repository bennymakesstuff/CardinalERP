<?php
/* Copyright (C) 2014-2016 Regis Houssin <regis.houssin@capnetworks.com>
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
 */

$hide = (isset($_COOKIE['dcloud-'.$blocname])?(bool)$_COOKIE['dcloud-'.$blocname]:$hide);

?>

<!-- BEGIN PHP TEMPLATE BLOC SHOW/HIDE -->
<script type="text/javascript">
$(document).ready(function() {
	$("#hide-<?php echo $blocname ?>").click(function(){
		setShowHide(1);
		$("#<?php echo $blocname ?>_bloc").hide("blind", {direction: "vertical"}, 300).removeClass("nohideobject");
		$(this).hide();
		$("#show-<?php echo $blocname ?>").show();
	});
	$("#show-<?php echo $blocname ?>").click(function(){
		setShowHide(0);
		$("#<?php echo $blocname ?>_bloc").show("blind", {direction: "vertical"}, 300).addClass("nohideobject");
		$(this).hide();
		$("#hide-<?php echo $blocname ?>").show();
	});
	function setShowHide(status) {
		$.cookie('dcloud-<?php echo $blocname ?>', status);
	}
});
</script>

<div id="<?php echo $blocname ?>_title" class="liste_titre display-table centpercent">
	<?php echo '<div class="tagtd fifty-percent text-align-left">'.$title.'</div>'; ?>
	<div id="hide-<?php echo $blocname ?>" class="showhide-button linkobject<?php echo ($hide ? ' hideobject' : ''); ?>"><?php echo img_picto('', '1uparrow.png'); ?></div>
	<div id="show-<?php echo $blocname ?>" class="showhide-button linkobject<?php echo ($hide ? '' : ' hideobject'); ?>"><?php echo img_picto('', '1downarrow.png'); ?></div>
</div>

<div id="<?php echo $blocname ?>_bloc" class="<?php echo ($hide ? 'hideobject' : 'nohideobject'); ?>">

<?php include_once $blocname.'.tpl.php'; ?>

</div>
<!-- END PHP TEMPLATE BLOC SHOW/HIDE -->