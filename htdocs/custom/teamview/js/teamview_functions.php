<script type="text/javascript">


function OpenTachePop($t){
	var id_tache = $($t).parent('.one_content').find('.id_tache').val();
	var data = {
		'id_tache' : id_tache,
		'action' : "oneTache"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
		data: data, 
		dataType: 'json',
		success: function(found){
			if (found) {
				// console.log(found);
				$("#tache_title").html(found["ref"]);
				$("#tache_etat").html(found["etat"]);
				$("#tache_avance").html(found["avance"]);
				$("#tache_description").val(found["description"]);
				$("#tache_temps_consomme").html(found["tache_temps_consomme"]);
				if (found["disabledornot"] == "yes")
					$('#tache_description').attr('disabled', true);
				else
					$('#tache_description').attr('disabled', false);
				getchildtasks();
				$('.hover_bkgr_fricc').show();
				getallcomments();
				// get_files_joints();
			}
		}
	});
}

function getchildtasks(){
	var id_tache = $("#tache_title").find('.title').attr('id');
	// console.log(id_tache);
	var id_projet = $("#select_projet").val();
	var data = {
		'id_tache' : id_tache,
		'id_projet' : id_projet,
		'action' : "getchildtasks"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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

function createorupdatetask(){
	var todo_id = $("#todo_id").val();
	var id_tache = $("#tache_title").find('.title').attr('id');
	var id_projet = $("#tache_title").find('.title').attr('projet-id');
	var etat_tache = $("#tache_etat select option:selected").val();
	var avance_tache = $("#tache_avance select option:selected").val();
	var description = $("#tache_description").val();

	var editornew = $('#editornew').val();

	// console.log(editornew);
	var data = {
		'todo_id' : todo_id,
		'id_tache' : id_tache,
		'id_projet' : id_projet,
		'etat_tache' : etat_tache,
		'avance_tache' : avance_tache,
		'description' : description,
		'editornew' : editornew,
		'action' : "createorupdatetask"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
		data: data, 
		dataType: 'json',
		success: function(found){
			if (found) {
				$('.createorupdatetask').hide();
				$('.popupCloseButton').attr('changed','yes');
			}
		}
	});
	$('#editornew').val('edit');	
}

function show_times_task($t,$cls){
	$('.tr_of_times_in_subtask:not(.task_'+$cls+')').hide();
	$('.tr_of_times_in_subtask.task_'+$cls).toggle();
	$('tr#row-'+$cls).addClass('ActiveTask');
	$('#tablelines tr.trparent:not(tr#row-'+$cls+')').removeClass('ActiveTask');
}

function times_task_changed($t){
	var id_task = $($t).parent('.inputs_hm').find('.id_task').val();
	var id_time = $($t).parent('.inputs_hm').find('.id_time').val();


	var val_h = $($t).parent('.inputs_hm').find('.inputhour').val();
	var val_m = $($t).parent('.inputs_hm').find('.inputminute').val();
	var val_h_orig = $($t).parent('.inputs_hm').find('.inputhour').data('orig');
	var val_m_orig = $($t).parent('.inputs_hm').find('.inputminute').data('orig');

	if (val_h_orig != val_h || val_m_orig != val_m)
		$($t).parent('.inputs_hm').parent('tr').find('.update_times_tasks').attr('disabled', false);
	else
		$($t).parent('.inputs_hm').parent('tr').find('.update_times_tasks').attr('disabled', true);
}

function update_times_tasks($t){
	var id_task = $($t).parent('td').parent('tr').find('.id_task').val();
	var id_time = $($t).parent('td').parent('tr').find('.id_time').val();


	var val_h = $($t).parent('td').parent('tr').find('.inputhour').val();
	var val_m =$($t).parent('td').parent('tr').find('.inputminute').val();
	$($t).parent('td').parent('tr').find('.inputhour').data('orig',val_h);
	$($t).parent('td').parent('tr').find('.inputminute').data('orig',val_m);


	$($t).parent('td').parent('tr').find('.update_times_tasks').attr('disabled', true);

	var data = {
		'id_task' : id_task,
		'id_time' : id_time,
		'inputhour' : val_h,
		'inputminute' : val_m,
		'action' : "update_times_tasks"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
		data: data, 
		dataType: 'json',
		success: function(found){
			if (found) {
				$($t).parent('.inputs_hm').parent('tr').find('.update_times_tasks').attr('disabled', true);
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
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
		data: data, 
		dataType: 'json',
		success: function(found){
			if (found) {
				$('.button_avanc_tasks').attr('disabled', true);
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

function progress_tasks_change(){
	var all100 = true;
	$('#tablelines > tbody  > tr .tache_avanc').each(function() {
		tot = $(this).find('select option:selected').val();
		if (tot < 100)
			all100 = false;
	});
	if (all100){
		$("#tache_avance select").val(100);
		$("#tache_avance select").trigger('change');
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
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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

function slct_etat_change(){
	$('.createorupdatetask').show();
	// $('.popupCloseButton').attr('changed','yes');
}

function get_contacts_users_project(){
	id_projet = $('.projet_choose #select_projet').val();
		var data = {
		'id_projet' : id_projet,
		'action' : "get_contacts_users_project"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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

function projet_choose_change(){
	$('.filter_in_tasks').val('');
	$('#nbr_task_parent>b>span').html('0');
	get_contacts_users_project();
	$('.todo_content .contents .scroll_div').html('');
	id_projet = $('.projet_choose #select_projet').val();
		var data = {
		'id_projet' : id_projet,
		'action' : "getallTasks"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
		data: data, 
		dataType: 'json',
		success: function(found){
			if (found) {
				$.each( found, function( key, value ) {
					var k = key.replace(/"/g,'');
					$('.todo_content #'+k+' .contents .scroll_div').html(value["content"]);
				})
				$('#nbr_task_parent>b>span').html(found['tototot']);
				var ToDo = $('#ToDo .one_content').length;
				var EnCours = $('#EnCours .one_content').length;
				var AValider = $('#AValider .one_content').length;
				var Validé = $('#Validé .one_content').length;
				$('#nbr_todo').html('<?php echo trim(addslashes($langs->trans("nbrelements"))); ?> '+ToDo);
				$('#nbr_encours').html('<?php echo trim(addslashes($langs->trans("nbrelements"))); ?> '+EnCours);
				$('#nbr_avalider').html('<?php echo trim(addslashes($langs->trans("nbrelements"))); ?> '+AValider);
				$('#nbr_valider').html('<?php echo trim(addslashes($langs->trans("nbrelements"))); ?> '+Validé);
			}
		}
	});
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
	var id_tache 	= $("#tache_title").find('.title').attr('id');

	var data = {
		'id_tache' : id_tache,
		'action' : "get_files_joints"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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

    if ($('.one_comment#comment_'+cmntId).find('.tache_comment_edit').val() == "")
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
	    url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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



function comment_change(){
	$('.create_comment').removeAttr('disabled');
	if ($("#tache_comment").val() == "")
		$('.create_comment').attr('disabled', true);
}

function comment_change_edit($t){
	if ($($t).val() == "")
		$($t).parent().find('.update_comment').attr('disabled', true);
	else
		$($t).parent().find('.update_comment').attr('disabled', false);
}

function edit_comment($t){
	$($t).parent().find('.commentaire_txt').hide();
	$($t).parent().find('.commentaire_txt_input').show();
	var textarea = $($t).parent().find('.commentaire_txt').text();
	$($t).parent().find('.commentaire_txt_input textarea').val(textarea);
	// console.log(textarea);
}

function update_comment($t) {
	if ($($t).parent().find('.add_photo').val() != "")
		$($t).parent().find('.add_photo').parent().parent('form').submit();

	$($t).parent().find('.create_comment').attr('disabled', true);
	var id_comment 	= $($t).parent().parent().find(".id_comment").val();
	var comment 	= $($t).parent().find(".tache_comment_edit").val();
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
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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
	var id_tache 	= $("#tache_title").find('.title').attr('id');
	var comment 	= $("#tache_comment").val();
	var id_user 	= '<?php echo $user->id; ?>';
	var created_at 	= "<?php echo date('Y-m-d H:i:s'); ?>";
	var files 		= [];
    var index = 1;

	formData = new FormData();
	// formData.append( 'id_comment', id_comment );
	formData.append( 'id_tache', id_tache );
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
	    url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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
			$("#tache_comment").val('');
			$($t).parent().find('.add_joint').removeClass("filledjoint");
			getallcomments();
			$('.popupCloseButton').attr('changed','yes');
			$($t).parent().find('form').html('<div class="one_file"><span class="add_joint" onclick="trigger_upload_file(this)"><i class="fa fa-paperclip"></i></span><input class="add_photo" type="file" name="photo[]" onchange="change_upload_file(this)"/></div><span class="add_plus" onclick="new_input_joint(this)"><i class="fa fa-plus"></i></span><div></div><hr>');
	    }
	} );
}

function getallcomments() {
	var id_tache 	= $("#tache_title").find('.title').attr('id');

	var data = {
		'id_tache' : id_tache,
		'action' : "getallcomments"
	};
	$.ajax({
		type: "POST",
		url: "<?php echo dol_buildpath('/teamview/check.php',2); ?>",
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

function cancel_cmt($t){
	$($t).parent().parent().find('.commentaire_txt').show();
	$($t).parent().parent().find('.commentaire_txt_input').hide();
}


</script>