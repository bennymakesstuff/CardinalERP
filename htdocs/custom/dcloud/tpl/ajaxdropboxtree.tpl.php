<?php
/* Copyright (C) 2011-2017 Regis Houssin	<regis.houssin@capnetworks.com>
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
?>

<!-- BEGIN PHP TEMPLATE FOR DROPBOX TREE -->
<div id="confirm-delete" title="<?php echo $langs->trans('DeleteFile'); ?>" style="display: none;">
	<p><?php echo img_warning().' '.$langs->trans('ConfirmDeleteFile'); ?></p>
</div>
<div id="dropbox_tree"></div>
<script type="text/javascript">
$(document).ready(function(){
	var dropbox_root = "<?php echo $_SESSION['dropbox_root']; ?>";
	var dropbox_root_label = "<?php echo $_SESSION['dropbox_root_label']; ?>";
	var dropbox_root_icon = "<?php echo $_SESSION['dropbox_root_icon']; ?>";

	$("#dropbox_tree").jstree({
		"core" : {
			"strings" : { new_node : "<?php echo $langs->trans('NewDirectory'); ?>"	},
			"load_open" : true
		},
		"themes" : {
			"theme" : "apple"
		},
		"plugins" : [ "themes", "json_data", "ui", "crrm", "cookies", "dnd", "types", "hotkeys", "contextmenu" ],
		// I usually configure the plugin that handles the data first
		// This example uses JSON as it is most common
		"json_data" : {
			"data" : {
				"data" : dropbox_root_label,
				"attr" : {
					"id" : "node_" + dropbox_root,
					"rel" : dropbox_root_icon
				},
				"state" : "open"
	        },
			// This tree is ajax enabled - as this is most common, and maybe a bit more complex
			// All the options are almost the same as jQuery's AJAX (read the docs)
			"ajax" : {
				// the URL to fetch the data
				"url" : "<?php echo dol_buildpath('/dcloud/core/ajax/dropbox.php',1); ?>",
				// the `data` function is executed in the instance's scope
				// the parameter is the node being loaded
				// (may be -1, 0, or undefined when loading the root nodes)
				"data" : function (n) {
					var node_name = n.attr ? n.attr("id").replace("node_","") : dropbox_root;
					if (n.attr && n.attr("rel").match(/^folder|drive|thirdparty|product|service/)) {
						$.cookie('dol_jstree_select', node_name);
					}
					// the result is fed to the AJAX request `data` option
					return {
						"operation" : "dropbox_get_children",
						"id" :  node_name
					};
				}
			}
		},
		"contextmenu" : {
			"items": function(n) {

                if(n.attr('rel').match(/^drive|thirdparty|product|service|folder_photos|folder_public/)) {
                    return {
                    	"refresh"	: menu_refresh(n),
                    	<?php if (!empty($user->rights->dcloud->write)) { ?>
                        "create"	: menu_create(n),
                        "paste"		: menu_paste(n)
                        <?php } ?>
                    };
                } else if(n.attr('rel').match(/^folder/)) {
                    return {
                    	"refresh"	: menu_refresh(n),
                    	<?php if (!empty($user->rights->dcloud->write)) { ?>
                        "create"	: menu_create(n),
                        "rename"	: menu_rename(n),
                        <?php } ?>
                        <?php if (!empty($user->rights->dcloud->delete)) { ?>
                        "remove"	: menu_remove(n),
                        <?php } ?>
                        <?php if (!empty($user->rights->dcloud->write)) { ?>
                        "copy"		: menu_copy(n),
                        "cut"		: menu_cut(n),
                        "paste"		: menu_paste(n)
                        <?php } ?>
                    };
                } else {
                    return {
                    	<?php if (!empty($user->rights->dcloud->write)) { ?>
                        "rename"	: menu_rename(n),
                        <?php } ?>
                        <?php if (!empty($user->rights->dcloud->delete)) { ?>
                        "remove"	: menu_remove(n),
                        <?php } ?>
                        <?php if (!empty($user->rights->dcloud->write)) { ?>
                        "copy"		: menu_copy(n),
                        "cut"		: menu_cut(n)
                        <?php } ?>
                    };
                }
            }
		},
		"types" : {
			// I set both options to -2, as I do not need depth and children count checking
			// Those two checks may slow jstree a lot, so use only when needed
			"max_depth" : -2,
			"max_children" : -2,
			// I want only `drive` nodes to be root nodes
			// This will prevent moving or creating any other type as a root node
			"valid_children" : [ "drive", "thirdparty" ],
			"types" : {
				// The default type
				"default" : {
					// I want this type to have no children (so only leaf nodes)
					// In my case - those are files
					"valid_children" : "none",
					// If we specify an icon for the default type it WILL OVERRIDE the theme icons
					"icon" : {
						"image" : "<?php echo dol_buildpath('/dcloud/img/file.png',1); ?>"
					}
				},
				"page_white_acrobat" : {
					"valid_children" : "none",
					"icon" : {
						"image" : "<?php echo DOL_URL_ROOT.'/theme/common/mime/pdf.png'; ?>"
					}
				},
				"page_white_picture" : {
					"valid_children" : "none",
					"icon" : {
						"image" : "<?php echo DOL_URL_ROOT.'/theme/common/mime/image.png'; ?>"
					}
				},
				"page_white_compressed" : {
					"valid_children" : "none",
					"icon" : {
						"image" : "<?php echo DOL_URL_ROOT.'/theme/common/mime/archive.png'; ?>"
					}
				},
				"page_white_word" : {
					"valid_children" : "none",
					"icon" : {
						"image" : "<?php echo DOL_URL_ROOT.'/theme/common/mime/doc.png'; ?>"
					}
				},
				"page_white_excel" : {
					"valid_children" : "none",
					"icon" : {
						"image" : "<?php echo DOL_URL_ROOT.'/theme/common/mime/xls.png'; ?>"
					}
				},
				"page_white_tux" : {
					"valid_children" : "none",
					"icon" : {
						"image" : "<?php echo DOL_URL_ROOT.'/theme/common/mime/ooffice.png'; ?>"
					}
				},
				"folder" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"icon" : {
						"image" : "<?php echo dol_buildpath('/dcloud/img/folder.png',1); ?>"
					}
				},
				"folder_customer" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"icon" : {
						"image" : "<?php echo dol_buildpath('/dcloud/img/folder_customer.png',1); ?>"
					}
				},
				"folder_user" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"icon" : {
						"image" : "<?php echo dol_buildpath('/dcloud/img/folder_user.png',1); ?>"
					}
				},
				"folder_public" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"delete_node" : false,
					"remove" : false,
					"rename" : false,
					"icon" : {
						"image" : "<?php echo dol_buildpath('/dcloud/img/folder_public.png',1); ?>"
					}
				},
				"folder_photos" : {
					"valid_children" : [ "folder", "page_white_picture" ],
					"delete_node" : false,
					"remove" : false,
					"rename" : false,
					"icon" : {
						"image" : "<?php echo dol_buildpath('/dcloud/img/folder_photos.png',1); ?>"
					}
				},
				// The root nodes
				"drive" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"icon" : {
						"image" : "<?php echo dol_buildpath('/dcloud/img/dropbox_16x16.png',1); ?>"
					},
					// those prevent the functions with the same name to be used on `drive` nodes
					// internally the `before` event is used
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"rename" : false,
					"remove" : false
				},
				// Thirdparty nodes
				"thirdparty" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"icon" : {
						"image" : "<?php echo img_picto('', 'object_company', '', false, 1); ?>"
					},
					// those prevent the functions with the same name to be used on `drive` nodes
					// internally the `before` event is used
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"rename" : false,
					"remove" : false
				},
				// Product nodes
				"product" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"icon" : {
						"image" : "<?php echo img_picto('', 'object_product', '', false, 1); ?>"
					},
					// those prevent the functions with the same name to be used on `drive` nodes
					// internally the `before` event is used
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"rename" : false,
					"remove" : false
				},
				// Service nodes
				"service" : {
					"valid_children" : [ "folder", "default", "page_white_acrobat", "page_white_picture", "page_white_compressed", "page_white_word", "page_white_excel", "page_white_tux" ],
					"icon" : {
						"image" : "<?php echo img_picto('', 'object_service', '', false, 1); ?>"
					},
					// those prevent the functions with the same name to be used on `drive` nodes
					// internally the `before` event is used
					"start_drag" : false,
					"move_node" : false,
					"delete_node" : false,
					"rename" : false,
					"remove" : false
				}
			}
		}
	})
	.bind("create.jstree", function (e, data) {
		$.post(
			"<?php echo dol_buildpath('/dcloud/core/ajax/dropbox.php',1); ?>",
			{
				"operation" : "dropbox_create_node",
				"id" : data.rslt.parent.attr("id").replace("node_",""),
				"position" : data.rslt.position,
				"title" : data.rslt.name,
				"type" : data.rslt.obj.attr("rel")
			},
			function (r) {
				if(r.status) {
					$(data.rslt.obj).attr("id", "node_" + r.id);
					// Load existing files:
					<?php if (basename($_SERVER['PHP_SELF']) == 'document.php') { ?>
					$('#fileupload').each(function () {
						var that = this;
						$.getJSON(this.action, {
							id: data.rslt.parent.attr("id").replace("node_","")
							},
							function (result) {
								$("#fileupload-view").empty();
								if (result && result.length) {
									$(that).fileupload('option', 'done')
										.call(that, null, {result: result});
								}
							}
						);
					});
					<?php } ?>
				} else {
					$.jstree.rollback(data.rlbk);
				}
			}, "json");
	})
	.bind("remove.jstree", function (e, data) {
		$( "#confirm-delete" ).dialog({
			resizable: false,
			width: 400,
			modal: true,
			buttons: {
				"<?php echo $langs->trans('Ok'); ?>": function() {
					$( this ).dialog( "close" );
					data.rslt.obj.each(function () {
						$.ajax({
							async : false,
							type: 'POST',
							dataType: "json",
							url: "<?php echo dol_buildpath('/dcloud/core/ajax/dropbox.php',1); ?>",
							data : {
								"operation" : "dropbox_remove_node",
								"id" : this.id.replace("node_","")
							},
							success : function (r) {
								if(!r.status) {
									$.jnotify(r.error, "error", true);
									$.jstree.rollback(data.rlbk);
								} else {
									<?php if (basename($_SERVER['PHP_SELF']) == 'document.php') { ?>
									$('#fileupload').each(function () {
										var that = this;
										$.getJSON(this.action, {
											id: r.parent_node
											},
											function (result) {
												$("#fileupload-view").empty();
												if (result && result.length) {
													$(that).fileupload('option', 'done')
														.call(that, null, {result: result});
												}
											}
										);
									});
									<?php } ?>
								}
							}
						});
					});
				},
				"<?php echo $langs->trans('Cancel'); ?>": function() {
					$( this ).dialog( "close" );
					$.jstree.rollback(data.rlbk);
				}
			}
		});
	})
	.bind("rename.jstree", function (e, data) {
		$.post(
			"<?php echo dol_buildpath('/dcloud/core/ajax/dropbox.php',1); ?>",
			{
				"operation" : "dropbox_rename_node",
				"id" : data.rslt.obj.attr("id").replace("node_",""),
				"title" : data.rslt.new_name
			},
			function (r) {
				if(!r.status) {
					$.jnotify(r.error, "error", true);
					$.jstree.rollback(data.rlbk);
				} else {
					$(data.rslt.obj).attr("id", "node_" + r.id);
					<?php if (basename($_SERVER['PHP_SELF']) == 'document.php') { ?>
					$('#fileupload').each(function () {
						var that = this;
						$.getJSON(this.action, {
							id: r.parent_node
							},
							function (result) {
								$("#fileupload-view").empty();
								if (result && result.length) {
									$(that).fileupload('option', 'done')
										.call(that, null, {result: result});
								}
							}
						);
					});
					<?php } ?>
				}
			}, "json");
	})
	.bind("move_node.jstree", function (e, data) {
		data.rslt.o.each(function (i) {
			$.ajax({
				async : false,
				type: 'POST',
				dataType: "json",
				url: "<?php echo dol_buildpath('/dcloud/core/ajax/dropbox.php',1); ?>",
				data : {
					"operation" : "dropbox_move_node",
					"id" : $(this).attr("id").replace("node_",""),
					"ref" : data.rslt.cr === -1 ? 1 : data.rslt.np.attr("id").replace("node_",""),
					"position" : data.rslt.cp + i,
					"title" : data.rslt.name,
					"copy" : data.rslt.cy ? 1 : 0
				},
				success : function (r) {
					if(!r.status) {
						$.jnotify(r.error, "error", true);
						$.jstree.rollback(data.rlbk);
					} else {
						$(data.rslt.oc).attr("id", "node_" + r.id);
						if(data.rslt.cy && $(data.rslt.oc).children("UL").length) {
							data.inst.refresh(data.inst._get_parent(data.rslt.oc));
						}
						<?php if (basename($_SERVER['PHP_SELF']) == 'document.php') { ?>
						$('#fileupload').each(function () {
							var that = this;
							$.getJSON(this.action, {
								id: r.parent_node
								},
								function (result) {
									$("#fileupload-view").empty();
									if (result && result.length) {
										$(that).fileupload('option', 'done')
											.call(that, null, {result: result});
									}
								}
							);
						});
						<?php } ?>
					}
				}
			});
		});
	})
	<?php if (basename($_SERVER['PHP_SELF']) == 'document.php') { ?>
	.bind("select_node.jstree", function (e, data) {
		if (data.rslt.obj.attr("rel").match(/^folder|drive|thirdparty|product|service/)) {
			$.pleaseBePatient("<?php echo $langs->trans('PleaseBePatient'); ?>");
			var node_name = data.rslt.obj.attr("id").replace("node_","");
			$.cookie('dol_jstree_select', node_name);
			var parent_node_name = node_name.substring(0, node_name.lastIndexOf("0_0"));
			$.cookie('dol_jstree_parent', parent_node_name);
			// Load existing files:
			$('#fileupload').each(function () {
				var that = this;
				$.getJSON(this.action, {
					id: node_name
					},
					function (result) {
						$("#fileupload-view").empty();
						if (result && result.length) {
							$(that).fileupload('option', 'done')
								.call(that, null, {result: result});
						}
						$.unblockUI();
					}
				);
			});
		} else {
			var node_name = data.rslt.obj.parents('li').attr('id').replace("node_","");
			$.cookie('dol_jstree_select', node_name);
		}
	})
	<?php } ?>
	.bind("paste.jstree", function (e, data) {
		if (data.rslt.obj.attr("rel").match(/^folder|drive|thirdparty|product|service/)) {
			// Load existing files:
			<?php if (basename($_SERVER['PHP_SELF']) == 'document.php') { ?>
			$('#fileupload').each(function () {
				var that = this;
				$.getJSON(this.action, {
					id: data.rslt.obj.attr("id").replace("node_","")
					},
					function (result) {
						$("#fileupload-view").empty();
						if (result && result.length) {
							$(that).fileupload('option', 'done')
								.call(that, null, {result: result});
						}
					}
				);
			});
			<?php } ?>
		}
	});

	function menu_create(n) {
		return {
			// The item label
        	"label"             : "<?php echo $langs->trans('NewDirectory'); ?>",
        	// The function to execute upon a click
        	"action"            : function (n) { this.create(n); },
        	// All below are optional
        	"_class"            : "class",  // class is applied to the item LI node
        	"separator_before"  : false,    // Insert a separator before the item
        	"separator_after"   : true,     // Insert a separator after the item
        	// false or string - if does not contain `/` - used as classname
        	"icon"              : "<?php echo dol_buildpath('/dcloud/img/create.png',1); ?>"
        	//"submenu"           : { /* Collection of objects (the same structure) */ }
		};
	}
	function menu_refresh(n) {
		return {
			// The item label
        	"label"             : "<?php echo $langs->trans('Refresh'); ?>",
        	// The function to execute upon a click
        	"action"            : function (n) {
        		$.ajax({
					async : false,
					type: 'POST',
					dataType: "json",
					url: "<?php echo dol_buildpath('/dcloud/core/ajax/dropbox.php',1); ?>",
					data : {
						"operation" : "dropbox_clear_cache",
						"id" : $(n).attr('id').replace("node_","")
					}
        		});
            	this.refresh(n);
            	},
        	// All below are optional
        	"_class"            : "class",  // class is applied to the item LI node
        	"separator_before"  : false,    // Insert a separator before the item
        	"separator_after"   : true,     // Insert a separator after the item
        	// false or string - if does not contain `/` - used as classname
        	"icon"              : "<?php echo dol_buildpath('/dcloud/img/refresh.png',1); ?>"
        	//"submenu"           : { /* Collection of objects (the same structure) */ }
		};
	}
	function menu_rename(n) {
		return {
			// The item label
        	"label"             : "<?php echo $langs->trans('Rename'); ?>",
        	// The function to execute upon a click
        	"action"            : function (n) { this.rename(n); },
        	// All below are optional
        	"_class"            : "class",  // class is applied to the item LI node
        	"separator_before"  : false,    // Insert a separator before the item
        	"separator_after"   : false,     // Insert a separator after the item
        	// false or string - if does not contain `/` - used as classname
        	"icon"              : "<?php echo dol_buildpath('/dcloud/img/rename.png',1); ?>"
        	//"submenu"           : { /* Collection of objects (the same structure) */ }
		};
	}
	function menu_remove(n) {
		return {
			// The item label
        	"label"             : "<?php echo $langs->trans('Delete'); ?>",
        	// The function to execute upon a click
        	"action"            : function (n) { this.remove(n); },
        	// All below are optional
        	"_class"            : "class",  // class is applied to the item LI node
        	"separator_before"  : false,    // Insert a separator before the item
        	"separator_after"   : true,     // Insert a separator after the item
        	// false or string - if does not contain `/` - used as classname
        	"icon"              : "<?php echo dol_buildpath('/dcloud/img/remove.png',1); ?>"
        	//"submenu"           : { /* Collection of objects (the same structure) */ }
		};
	}
	function menu_cut(n) {
		return {
			// The item label
        	"label"             : "<?php echo $langs->trans('Cut'); ?>",
        	// The function to execute upon a click
        	"action"            : function (n) { this.cut(n); },
        	// All below are optional
        	"_class"            : "class",  // class is applied to the item LI node
        	"separator_before"  : false,    // Insert a separator before the item
        	"separator_after"   : false,     // Insert a separator after the item
        	// false or string - if does not contain `/` - used as classname
        	"icon"              : "<?php echo dol_buildpath('/dcloud/img/cut.png',1); ?>"
        	//"submenu"           : { /* Collection of objects (the same structure) */ }
		};
	}
	function menu_copy(n) {
		return {
			// The item label
        	"label"             : "<?php echo $langs->trans('Copy'); ?>",
        	// The function to execute upon a click
        	"action"            : function (n) { this.copy(n); },
        	// All below are optional
        	"_class"            : "class",  // class is applied to the item LI node
        	"separator_before"  : false,    // Insert a separator before the item
        	"separator_after"   : false,     // Insert a separator after the item
        	// false or string - if does not contain `/` - used as classname
        	"icon"              : "<?php echo dol_buildpath('/dcloud/img/copy.png',1); ?>"
        	//"submenu"           : { /* Collection of objects (the same structure) */ }
		};
	}
	function menu_paste(n) {
		return {
			// The item label
        	"label"             : "<?php echo $langs->trans('Paste'); ?>",
        	// The function to execute upon a click
        	"action"            : function (n) { this.paste(n); },
        	// All below are optional
        	"_class"            : "class",  // class is applied to the item LI node
        	"separator_before"  : false,    // Insert a separator before the item
        	"separator_after"   : false,     // Insert a separator after the item
        	// false or string - if does not contain `/` - used as classname
        	"icon"              : "<?php echo dol_buildpath('/dcloud/img/paste.png',1); ?>"
        	//"submenu"           : { /* Collection of objects (the same structure) */ }
		};
	}

});
</script>
<!-- END PHP TEMPLATE FOR DROPBOX TREE -->
