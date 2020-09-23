
$(function(){
   
    $("#copydate").datepicker({
        showOn: 'button',
        buttonImage: './image/icon.gif',
        buttonImageOnly: true,
        maxDate: 0
    });
});

//時間の計算
function calculatetime(value,act)
{
    var time = 0;   
    var total = 0;
    $('.' + value).each(function () {
        time = ($(this).val());

        if (time === "")
        {
            time = 0;
        }

        //計算
        total += parseFloat(time);
    });
    if (value === 'teizi' && total > 7.75) {
        alert("7.75を超えています。");
    }
    //合計セット
    $('.' + act).text(total.toFixed(2));
    return;
}

