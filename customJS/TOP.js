
/**
 * ポップアップ表示
 * 
 */
$(function(){
    // tdクリック時イベント
    $("td#popup").each(function() {
    $(this).on("click", function(event){
//        $('.js-modal').fadeIn();
//        return false;
        window.location.href = "./main.php?KOUSU_1_button";
    });

});

    $('.js-modal-close').on('click',function(){
        $('.js-modal').fadeOut();
        return false;
    });
});