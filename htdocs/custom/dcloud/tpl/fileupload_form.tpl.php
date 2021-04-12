<?php
/* Copyright (C) 2011-2015 Regis Houssin <regis.houssin@capnetworks.com>
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

<!-- START TEMPLATE FILE UPLOAD -->

<!-- The file upload form used as target for the file upload widget -->
<form id="fileupload" action="<?php echo dol_buildpath('/dcloud/core/ajax/fileupload.php',1); ?>" method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" value="<?php echo $_SESSION['dropbox_root']; ?>" />
<!-- The fileupload-buttonbar contains buttons to add/delete files and start/cancel the upload -->
<div class="row fileupload-buttonbar">
	<?php if (!empty($user->rights->dcloud->write)) { ?>
	<div class="span7">
		<!-- The fileinput-button span is used to style the file input field as button -->
		<span class="btn btn-success fileinput-button">
			<i class="icon-plus icon-white"></i>
			<span><?php echo $langs->trans('AddFiles'); ?></span>
			<input type="file" name="files[]" multiple>
		</span>
		<button type="submit" class="btn btn-primary start">
			<i class="icon-upload icon-white"></i>
			<span><?php echo $langs->trans('StartUpload'); ?></span>
		</button>
		<button type="reset" class="btn btn-warning cancel">
			<i class="icon-ban-circle icon-white"></i>
			<span><?php echo $langs->trans('CancelUpload'); ?></span>
		</button>
		<!--
		<button type="button" class="btn btn-danger delete">
			<i class="icon-trash icon-white"></i>
			<span><?php echo $langs->trans('Delete'); ?></span>
		</button>
		<input type="checkbox" class="toggle">
		-->
	</div>
	<?php } ?>
	<!-- The global progress information -->
	<div class="span5 fileupload-progress fade">
		<!-- The global progress bar -->
		<!--
		<div class="progress progress-success progress-striped active" role="progressbar" aria-valuemin="0" aria-valuemax="100">
			<div class="bar" style="width:0%;"></div>
		</div>
		-->
		<!-- The extended global progress information -->
		<div class="progress-extended">&nbsp;</div>
	</div>
</div>
<!-- The loading indicator is shown during file processing -->
<div class="fileupload-loading"></div>
<!-- The table listing the files available for upload/download -->
<!-- <table role="presentation" class="table table-striped"><tbody class="files" data-toggle="modal-gallery" data-target="#modal-gallery"></tbody></table> -->
</form>
<!-- END PHP TEMPLATE -->
