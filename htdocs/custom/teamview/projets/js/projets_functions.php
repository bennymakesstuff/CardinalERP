<script type="text/javascript">
function load_all_projets(){

	var actif_onitem = 0;
	if ($('.actif_onitem').length > 0)
		actif_onitem = $('.actif_onitem').data("rowid");
	var sortfield 	= $('#sortfield_').val();
	var sortorder 	= $('#sortorder_').val();
	var limit 		= $('#limit_').val();
	var offset 		= $('#offset_').val();
	var filter 		= $('#filter_').val();

	$('.filter_in_tasks').val('');
	$('.todo_content .contents .scroll_div').html('');

	var data = {
		'actif_onitem' : actif_onitem,
		'sortfield' : sortfield,
		'sortorder' : sortorder,
		'limit' : limit,
		'offset' : offset,
		'filter' : filter,
		'action' : "getallprojets"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',3); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				// console.log('Found this s : '+found)
				$.each( found, function( key, value ) {
					var k = key.replace(/"/g,'');
					$('.todo_content #'+k+' .contents .scroll_div').html(value["content"]);
				})
				$.each( found, function( key, value ) {
					var k = key.replace(/"/g,'');
					$('#nbr_'+k+'').html('<?php echo trim(addslashes($langs->trans("nbrelements"))); ?> '+$('#'+k+' .one_content').length);
				})
			}
			applydraggable();
			applyJsToolTipForOneContent();
			$('.todo_content .columns_ .one_content').mousedown(function(){$(".actif_onitem").removeClass("actif_onitem");$(this).addClass("actif_onitem");});
		}
	});
}
function OpenProjetPop($t){
	var id_projet = $($t).find('.id_projet').val();
	var data = {
		'id_projet' : id_projet,
		'action' : "oneProjet"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',3); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				// console.log("Projet found : "+found);
				$("#projet_title").html(found["ref"]);
				$("#projet_tiers").html(found["tiers"]);
				$('.hover_bkgr_fricc').show();
				getallcomments();
			}
		}
	});
}
function comment_clicked($t){
	$($t).parent().dblclick();
}

function applydraggable(){
	$(".one_content").draggable({
		containment: ".todo_content",
      	revert: "invalid",
      	refreshPositions: true,
      	drag: function (event, ui) {
          	ui.helper.addClass("draggable");
          	$(".actif_onitem").removeClass("actif_onitem");
          	ui.helper.addClass("actif_onitem");
          	$('.ui-tooltip').hide();
          	// if ($('.scroll_div.ui-droppable.ui-droppable-active.ui-droppable-hover').length > 0)
          	// 	$('.scroll_div.ui-droppable.ui-droppable-active.ui-droppable-hover').css("border-style","dashed");
          	// else
          	// 	$('.scroll_div.ui-droppable.ui-droppable-active.ui-droppable-hover').css("border-style","none");
      	},
      	stop: function (event, ui) {
          	ui.helper.removeClass("draggable");
          	// setTimeout(
          	// 	function(){
          	// 		ui.helper.removeClass("actif_onitem");
          	// 	}, 3000
          	// );
      }
  	});
  	$(".scroll_div").droppable({
		accept: function(dropElem) {
			var arr = [];
			var from = dropElem.parent().parent().parent().attr("id");
			return true;
		},
		drop: function (event, ui) {

			ui.draggable.css("left","0");
			ui.draggable.css("top","auto");
			var from_etat = ui.draggable.parent().parent().parent().data("etat");
			var to_etat = $(this).parent().parent().data("etat");
			var fk_opp_status = $(this).parent().parent().data("opp_status");

			var from = ui.draggable.parent().parent().parent().attr("id");
			var to = $(this).parent().parent().attr("id");
			var id_projet = ui.draggable.data("rowid");

			var arr = [];

			if (to == "JC")
				arr = ["NPC","AC","CEC","CR","DESA"];

			var permis = 1;
			<?php
			if (!$user->rights->projet->creer) {
			?>
			permis = 0;
			<?php
			}
			?>

			if (to !== from && permis == 1) {
				if($(".jnotify-container>div").length >= 3)
					$(".jnotify-notification").remove();

				var data = {
					'id_projet' : id_projet,
					'from_etat' : from_etat,
					'to_etat' : to_etat,
					'fk_opp_status' : fk_opp_status,
					'action' : "updateprojetetat"
				};
				$.ajax({
					type: "POST",
					url: "<?php echo dol_buildpath('/teamview/projets/check.php',3); ?>",
					data: data,
					dataType: 'json',
					success: function(found){
						$.jnotify('<?php echo trim($langs->trans("updatedSuccessfuly")); ?>',
							"500",
							false,
							{ remove: function (){} } );
						if (from == "DRAFT")
							load_all_projets();

						countEachColumnNumbers();
					}
				});
				$("#"+to+" .scroll_div").append(ui.draggable);
			}else{
				if (permis == 0) {
					$.jnotify('<?php echo trim(addslashes($langs->trans("permissiondenied"))); ?>',
					"warning",
					false,
					{ remove: function (){} } );
				}
			}
		}
  	});
}










































































function filter_tasks_content(that){
    var txt = $(that).val().toLowerCase();
    $('.todo_content .contents .one_content').each(function(){
    	var str = $(this).find('h4').text();
    	// console.log(str.toLowerCase().indexOf(txt));
    	if (str.toLowerCase().indexOf(txt) >= 0)
    		$(this).show();
    	else
    		$(this).hide();
    });
}
function consulter_img($t,e){
	e.preventDefault();
    var image_href = $($t).attr("href");
    $('#lightbox #content').html('<img src="' + image_href + '" />');
    $('#lightbox').show();
}

function trigger_upload_file($t){
	$($t).parent().find(".add_photo").trigger('click');
}

function change_upload_file($t){
	// console.log($($t).val());
	if ($($t).val() != "")
		$($t).parent().find('.add_joint').addClass("filledjoint");
	else
		$($t).parent().find('.add_joint').removeClass("filledjoint");

	if ($($t).parent().parent().parent().find('.textarea_comment').val() == "")
		$($t).parent().parent().parent().find('.comment_btn').attr('disabled', true);
	else
		$($t).parent().parent().parent().find('.comment_btn').attr('disabled', false);
}

function get_files_joints() {
	var id_projet 	= $("#projet_title").find('.title').attr('id');

	var data = {
		'id_projet' : id_projet,
		'action' : "get_files_joints"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				$("#files_joints").html(found);
			}else{
				$("#files_joints").html("");
			}
		}
	});
}

function to_delete_file($t, e, cmntId){
	e.preventDefault();
    var files_href = $($t).attr("datafile");
    var files_deleted = $('.files_deleted').val();
    if(files_deleted == '')
        $($t).parent('li').parent().parent().find('.files_deleted').val(files_href);
    else
        $($t).parent('li').parent().parent().find('.files_deleted').val(files_deleted+','+files_href);
    $($t).parent('li').remove();

    if ($('.one_comment#comment_'+cmntId).find('.projet_comment_edit').val() == "")
		$('.one_comment#comment_'+cmntId).find('.update_comment').attr('disabled', true);
	else
		$('.one_comment#comment_'+cmntId).find('.update_comment').attr('disabled', false);
}
function upload_file($t, e){
	e.preventDefault();
	var id_comment 	= $($t).parent().parent().find('.id_comment').val();
    //var file 		= $($t).find('.add_photo').get( 0 ).files[0];
    var files 		= [];
    var index = 1;
	formData = new FormData();
	formData.append( 'id_comment', id_comment );
	formData.append( 'action', "upload_file" );

    $($t).find('.add_photo').each(function(){
		file =  $(this).get( 0 ).files[0];
		formData.append( 'files[file_'+index+']', file );
		index ++;
    });
 	$.ajax( {
	    url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
	    type       : 'POST',
	    contentType: false,
	    cache      : false,
	    processData: false,
	    data       : formData,
	    success    : function ( data )
	    {
	        //Do something success-ish
	        // console.log( 'Completed.' );
	        $($t).find('.add_photo').val('');
	        // get_files_joints();
	    }
	} );
}

function new_input_joint($t){
	 var cnt = $($t).parent();
	 $($t).parent().find('.add_plus').before('<div class="one_file"><span class="add_joint" onclick="trigger_upload_file(this)"><i class="fa fa-paperclip"></i></span><input class="add_photo" type="file" name="photo[]" onchange="change_upload_file(this)"/></div>');
}

function progress_tasks_change(){
	var all100 = true;
	$('#tablelines > tbody  > tr .projet_avanc').each(function() {
		tot = $(this).find('select option:selected').val();
		if (tot < 100)
			all100 = false;
	});
	if (all100){
		$("#projet_avance select").val(100);
		$("#projet_avance select").trigger('change');
		// createorupdatetask();
	}

	$('.button_avanc_tasks').attr('disabled', false);
	check_user_permission_projet();
}
function check_user_permission_projet(){
	var data = {
		'action' : "check_user_permission_projet"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found == "no") {
				$('.sous_tasks').attr('disabled', true);
				$('.button_avanc_tasks').attr('disabled', true);
			}else{
				$('.sous_tasks').attr('disabled', false);
			}
		}
	});
}
function update_avanc_tasks() {
	var progress_tasks = $('form#form_progress_tasks').serialize();
	$('.popupCloseButton').attr('changed','yes');
	var data = {
		'progress_tasks' : progress_tasks,
		'action' : "update_avanc_tasks"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				$('.button_avanc_tasks').attr('disabled', true);
			}
		}
	});
}
function edit_comment($t){
	$($t).parent().find('.commentaire_txt').hide();
	$($t).parent().find('.commentaire_txt_input').show();
	var textarea = $($t).parent().find('.commentaire_txt').text();
	$($t).parent().find('.commentaire_txt_input textarea').val(textarea);
	// console.log(textarea);

}
function cancel_cmt($t){
	$($t).parent().parent().find('.commentaire_txt').show();
	$($t).parent().parent().find('.commentaire_txt_input').hide();
}
function comment_change(){
	$('.create_comment').removeAttr('disabled');
	if ($("#projet_comment").val() == "")
		$('.create_comment').attr('disabled', true);
}
function comment_change_edit($t){
	if ($($t).val() == "")
		$($t).parent().find('.update_comment').attr('disabled', true);
	else
		$($t).parent().find('.update_comment').attr('disabled', false);
}
function update_comment($t) {
	if ($($t).parent().find('.add_photo').val() != "")
		$($t).parent().find('.add_photo').parent().parent('form').submit();

	$($t).parent().find('.create_comment').attr('disabled', true);
	var id_comment 	= $($t).parent().parent().find(".id_comment").val();
	var comment 	= $($t).parent().find(".projet_comment_edit").val();
	var files_deleted  = $($t).parent().find(".files_deleted").val();
	// console.log(files_deleted);
	var data = {
		'id_comment' : id_comment,
		'comment' : comment,
		'files_deleted' : files_deleted,
		'modified' : 1,
		'action' : "update_comment"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				getallcomments();
			}
		}
	});
}
function delete_comment($t) {
	var id_comment 	= $($t).parent().find(".id_comment").val();
	var data = {
		'id_comment' : id_comment,
		'action' : "delete_comment"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				getallcomments();
				$('.popupCloseButton').attr('changed','yes');
			}
		}
	});
}
function create_comment($t) {
	var id_projet 	= $("#projet_title").find('.title').attr('id');
	var comment 	= $("#projet_comment").val();
	var id_user 	= '<?php echo $user->id; ?>';
	var created_at 	= "<?php echo date('Y-m-d H:i:s'); ?>";
	var files 		= [];
    var index = 1;

	formData = new FormData();
	// formData.append( 'id_comment', id_comment );
	formData.append( 'id_projet', id_projet );
	formData.append( 'id_user', id_user );
	formData.append( 'created_at', created_at );
	formData.append( 'comment', comment );
	formData.append( 'action', "create_comment" );

    $($t).parent().find('.add_photo').each(function(){
		file =  $(this).get( 0 ).files[0];
		formData.append( 'files[file_'+index+']', file );
		index ++;
    });
    // console.log(formData);
 	$.ajax( {
	    url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
	    type       : 'POST',
	    contentType: false,
	    cache      : false,
	    processData: false,
	    data       : formData,
	    success    : function ( data )
	    {
	        //Do something success-ish
	        // console.log( 'Completed.' );
	        $('.create_comment').attr('disabled', true);
			$("#projet_comment").val('');
			$($t).parent().find('.add_joint').removeClass("filledjoint");
			getallcomments();
			$('.popupCloseButton').attr('changed','yes');
			$($t).parent().find('form').html('<div class="one_file"><span class="add_joint" onclick="trigger_upload_file(this)"><i class="fa fa-paperclip"></i></span><input class="add_photo" type="file" name="photo[]" onchange="change_upload_file(this)"/></div><span class="add_plus" onclick="new_input_joint(this)"><i class="fa fa-plus"></i></span><div></div><hr>');
	    }
	} );
}
function getallcomments() {
	var id_projet 	= $("#projet_title").find('.title').attr('id');

	var data = {
		'id_projet' : id_projet,
		'action' : "getallcomments"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				// console.log(found);
				$("#commentaires").html(found);
				$('.update_comment').attr('disabled', true);
			}else{
				$("#commentaires").html("");
			}
		}
	});
}

function getchildtasks(){
	var id_projet = $("#projet_title").find('.title').attr('id');
	// console.log(id_projet);
	var id_projet = $("#select_projet").val();
	var data = {
		'id_projet' : id_projet,
		'id_projet' : id_projet,
		'action' : "getchildtasks"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				// console.log(found);
				$("#sous_taches tbody").html(found);
			}
		}
	});
}
function get_contacts_users_project(){
	id_projet = $('.projet_choose #select_projet').val();
		var data = {
		'id_projet' : id_projet,
		'action' : "get_contacts_users_project"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/projets/check.php',2); ?>",
		data: data,
		dataType: 'json',
		success: function(found){
			if (found) {
				$('#contacts_projets').html(found['allcontacts']);
				$('#etat_projet_>b>span').html(found['etat']);
				$('#tier_projet>b>span').html(found['tiers']);
				$('#dates_begin_end>b>span').html(found['dates']);
			}
		}
	});
}
</script>
