
$(function(){
   
    $("#copydate").datepicker({
        showOn: 'button',
        buttonImage: './image/icon.gif',
        buttonImageOnly: true,
        maxDate: 0
    });
    calculatetime("teizi","teizibox");
    calculatetime("zangyo","zangyobox");
    
    // コピーボタン押下時
    $('.copybutton').on('click',function(){
        // html
        var searchdate;
        var date;
        searchdate = $('#copydate').val();
        date = $('.pjday').text();
        if(searchdate === ''){
            alert('日付を選択してください。');
            return;
        }
        var contentsPath = "kousuAjax.php?id=KOUSU_1";
        // 非同期通信
            $.ajax(contentsPath,
            {
                type: 'get',
                dataType: 'html',
                data: {
                    date: searchdate
                }
            })
            .done(function (data)
            {
                // リスト内容変更
                $(".list").html("");
                $(".list").append(data);
                return false;
            })
            .fail(function() {
                // データ取得失敗
                alert('データの取得に失敗しました。');
                
                return false;
                
            });
        
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

