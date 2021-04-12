<script type="text/javascript">
$( function() {
    $( ".date_inputs" ).datepicker({
        dateFormat: 'dd/mm/yy'
    });
});
function applyJsToolTipForOneContent(){
    $(document).tooltip({
        items: ".one_content",
        tooltipClass: "arrow",
        content: function () {
            var $this = $(this),
            random, html = "";
            return $(this).data("title");
        },
        position: {
            my: "center bottom-4", // the "anchor point" in the tooltip element
            at: "center top", // the position of that anchor point relative to selected element
        }
        // ,
        // animation: true,
        // delay: { "show": 500, "hide": 100 }
    });
}
function one_content_hovered(that){
    $(that).on({
    mouseenter: function () {
        //stuff to do on mouse enter
        $(that).addClass("hovered_onitem");
    },
    mouseleave: function () {
        $(that).removeClass("hovered_onitem");
        //stuff to do on mouse leave
    }
});
}
function dateNowInInput(inputId){
    $("#"+inputId).val('<?php echo date("d/m/Y");?>');
}
function countEachColumnNumbers(){
    $(".todo_content .columns_").each(function(){
        var numbers = $(this).find('.one_content').length
        if (numbers > 0){
            $(this).find('.filter_in_etat').html('<?php echo trim(addslashes($langs->trans("nbrelements"))); ?> '+numbers);
        }else{
            $(this).find('.filter_in_etat').html("");
        } 
        numbers = 0;
    });
}
</script>