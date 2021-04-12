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

// PHP post_max_size
$post_max_size				= ini_get('post_max_size');
$mul_post_max_size			= substr($post_max_size, -1);
$mul_post_max_size			= ($mul_post_max_size == 'M' ? 1048576 : ($mul_post_max_size == 'K' ? 1024 : ($mul_post_max_size == 'G' ? 1073741824 : 1)));
$post_max_size				= $mul_post_max_size * (int) $post_max_size;
// PHP upload_max_filesize
$upload_max_filesize		= ini_get('upload_max_filesize');
$mul_upload_max_filesize	= substr($upload_max_filesize, -1);
$mul_upload_max_filesize	= ($mul_upload_max_filesize == 'M' ? 1048576 : ($mul_upload_max_filesize == 'K' ? 1024 : ($mul_upload_max_filesize == 'G' ? 1073741824 : 1)));
$upload_max_filesize		= $mul_upload_max_filesize * (int) $upload_max_filesize;
// Max file size
$max_file_size 				= (($post_max_size < $upload_max_filesize) ? $post_max_size : $upload_max_filesize);

?>

<!-- START TEMPLATE FILE UPLOAD MAIN -->
<script type="text/javascript">
window.locale = {
    "fileupload": {
        "errors": {
            "maxFileSize": "<?php echo $langs->transnoentities('FileIsTooBig'); ?>",
            "minFileSize": "<?php echo $langs->transnoentities('FileIsTooSmall'); ?>",
            "acceptFileTypes": "<?php echo $langs->transnoentities('FileTypeNotAllowed'); ?>",
            "maxNumberOfFiles": "<?php echo $langs->transnoentities('MaxNumberOfFilesExceeded'); ?>",
            "uploadedBytes": "<?php echo $langs->transnoentities('UploadedBytesExceedFileSize'); ?>",
            "emptyResult": "<?php echo $langs->transnoentities('EmptyFileUploadResult'); ?>"
        },
        "error": "<?php echo $langs->transnoentities('Error'); ?>",
        "start": "<?php echo $langs->transnoentities('Start'); ?>",
        "cancel": "<?php echo $langs->transnoentities('Cancel'); ?>",
        "destroy": "<?php echo $langs->transnoentities('Delete'); ?>"
    }
};

$(function () {
	'use strict';

	$.removeCookie('dol_jstree_select');
	$.removeCookie('dol_jstree_parent');

	// Initialize the jQuery File Upload widget:
	$('#fileupload').fileupload();

	// Options
	$('#fileupload').fileupload('option', {
		filesContainer: '#fileupload-view',
		maxFileSize: '<?php echo $max_file_size; ?>'
	});

	// Events
	$('#fileupload').fileupload({
		sent: function (e, data) {
			var tree = $.jstree._focused();
			var currentNode = tree._get_node(null, false);
			tree.refresh(currentNode);
		},
		destroy: function (e, data) {
			var that = $(this).data('fileupload');
			$( "#confirm-delete" ).dialog({
				resizable: false,
				width: 400,
				modal: true,
				buttons: {
					"<?php echo $langs->trans('Ok'); ?>": function() {
						$( "#confirm-delete" ).dialog( "close" );
						if (data.url) {
							$.ajax(data)
								.success(function (data) {
									if (data) {
										that._adjustMaxNumberOfFiles(1);
										$(this).fadeOut(function () {
											$(this).remove();
											$.jnotify("<?php echo $langs->trans('FileWasRemoved'); ?>");
											var tree = $.jstree._focused();
											var currentNode = tree._get_node(null, false);
											tree.refresh(currentNode);
										});
									} else {
										$.jnotify("<?php echo $langs->trans('ErrorFailToDeleteFile'); ?>", "error", true);
									}
								});
						} else {
							data.context.fadeOut(function () {
								$(this).remove();
							});
						}
					},
					"<?php echo $langs->trans('Cancel'); ?>": function() {
						$( "#confirm-delete" ).dialog( "close" );
					}
				}
			});
		}
	});

	// Load existing files:
	$('#fileupload').each(function () {
		var that = this;
		$.getJSON(this.action, { id: "<?php echo $_SESSION['dropbox_root']; ?>"	}, function (result) {
			if (result && result.length) {
				$(that).fileupload('option', 'done')
					.call(that, null, {result: result});
			}
		});
	});

	$("#fileupload-view").on("click", ".dcloud-directory", function() {
		$.pleaseBePatient("<?php echo $langs->trans('PleaseBePatient'); ?>");

		var node_name = $(this).attr("id");
		$.cookie('dol_jstree_select', node_name);

		if ($(this).attr("rel") == 'parentfolder') {
			var parent_node_name = $.cookie('dol_jstree_select').substring(0, $.cookie('dol_jstree_select').lastIndexOf("0_0"));
		} else {
			var parent_node_name = $.cookie('dol_jstree_select').replace("0_0" + $(this).attr("title").replace(" ","-_-"),"");
		}

		$.cookie('dol_jstree_parent', parent_node_name);

		// Load existing files:
		$('#fileupload').each(function () {
			var that = this;
			$.getJSON(this.action, {
				id: node_name
				},
				function (result) {
					$("#fileupload-view").empty();
					$("#dropbox_tree").jstree("deselect_all", true);
					//alert("#node_" + node_name);
					$.jstree._focused().open_node("#node_" + node_name.replace("0_00_0","0_0"));
					$("#dropbox_tree").jstree("select_node", "#node_" + node_name.replace("0_00_0","0_0"), true);
					if (result && result.length) {
						$(that).fileupload('option', 'done')
							.call(that, null, {result: result});
					}
					$.unblockUI();
				}
			);
		});
	});

});
</script>
<!-- END TEMPLATE FILE UPLOAD MAIN -->
