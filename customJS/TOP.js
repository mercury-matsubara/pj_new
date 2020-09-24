
/**
 * ポップアップ表示
 * 
 */
$(function(){
    // tdクリック時イベント
    $("td#popup").each(function() {
    $(this).on("click", function(event){
        var yearmonth;
        var month = $(".month").text();
        //2020/X
        month = month.replace(/[^0-9]/g, '/');
        month = month.slice(2,-3);
        yearmonth = month.split("/");
        var day = $(".dayof",this).text();
        var date = yearmonth[0] +"/" + ("00"+ yearmonth[1]).slice(-2) + "/" + ("00" + day).slice(-2);
        location.href = "./main.php?KOUSU_1_button?date="+date;
    });

});

    $('.js-modal-close').on('click',function(){
        $('.js-modal').fadeOut();
        return false;
    });
});