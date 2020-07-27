
/**
 * ポップアップ表示
 * 
 */
$(function(){
    // tdクリック時イベント
    $("td#popup").each(function() {
    $(this).on("click", function(event){
        $('.js-modal').fadeIn();
        return false;

    });

});

    $('.js-modal-close').on('click',function(){
        $('.js-modal').fadeOut();
        return false;
    });
});