 
jQuery(document).ready(function() {
  	// $('.todo_content .contents .scroll_div').slimScroll();

    var w_right = $('#id-container>#id-right').width();
    var w_side = $('#id-container>.side-nav').width();
    if ($('#id-container>.side-nav').is(":hidden")) {
      w_side = 0;
    }
  	var h_right = $('#id-container>#id-right').height();
  	var h_fiche = $('#id-right>.fiche').height();
  	var h_head = $('#id-top').height();
  	var marg = h_head+"px 0 "+h_head+"px";
  	$('.hover_bkgr_fricc').css({'height':'calc(100vh - '+h_head+'px )','width':'calc(100vw - '+w_side+'px )','margin-top':h_head+"px",'margin-bottom':h_head+"px"});
  	// $('.slimScrollDiv,.scroll_div').css({'min-height':(h_right-h_fiche)+"px"});
 	// js on Pop up
    $('.popupCloseButton').click(function(){
    	if ($('.popupCloseButton').attr('changed') == "yes") {
    		load_all_commandes();
    	}
      $('.hover_bkgr_fricc').hide();
      $('.createorupdatetask').hide();
      $('.popupCloseButton').attr('changed','no');
      notask = '<tr class="oddeven"><td colspan="2" class="opacitymedium" align="center">Aucune sous-tâche</td></tr>';
      $("#sous_taches tbody").html(notask);
      $("#commentaires").html("");
    });
    $('#lightbox,#lightbox p').click(function() {
        $('#lightbox').hide();
    });
    $('.todo_content .columns_ .one_content').mousedown(function(){$(".actif_onitem").removeClass("actif_onitem");$(this).addClass("actif_onitem");});
});