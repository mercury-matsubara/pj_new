
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
        var month = $(".month").text();
        month = month.replace(/[^0-9]/g, '/');
        month = month.slice(2,-3);
        var day = $(this).text();
        var date = month+"/"+day;
//        $.post("./main.php?KOUSU_1_button","date="+date)
//        window.location.href = "./main.php?KOUSU_1_button";
        location.href = "./main.php?KOUSU_1_button?date="+date;
    });

});

    $('.js-modal-close').on('click',function(){
        $('.js-modal').fadeOut();
        return false;
    });
});