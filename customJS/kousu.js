
/*
 * 工数入力javascript
 */
$(function(){
   
    $("#copydate").datepicker({
        showOn: 'button',
        buttonImage: './image/icon.gif',
        buttonImageOnly: true,
        maxDate: 0
    });
    // 時間計算
    calculatetime("teizi","teizibox");
    calculatetime("zangyo","zangyobox");
    
    // コピーボタン押下時
    $('.copybutton').on('click',function(){
        var searchdate;
        searchdate = $('#copydate').val();
        if(searchdate === ''){
            alert('日付を選択してください。');
            return;
        }
        var contentsPath = "kousuAjax.php?id=KOUSU_1";
        // 非同期通信
            $.ajax(contentsPath,
            {
                type: 'get',
                dataType: 'json',
                data: {
                    date: searchdate
                }
            })
            .done(function (data)
            {
                // すべてクリア
                $(".list").find(':text').val("");
                var dataArray = data.results;
                var dataCount = dataArray.length;
                // データセット
                for(var i = 0; i < dataCount; i++){
                    $("#form_topPROJECTNUM_" + i).val(dataArray[i]['PROJECTNUM']);
                    $("#form_topEDABAN_" + i).val(dataArray[i]['EDABAN']);
                    $("#form_topPJNAME_" + i).val(dataArray[i]['PJNAME']);
                    $("#form_topKOUTEIID_" + i).val(dataArray[i]['KOUTEIID']);
                    $("#form_topKOUTEINAME_" + i).val(dataArray[i]['KOUTEINAME']);
                    $("#form_topTEIZITIME_" + i).val(dataArray[i]['TEIZITIME']);
                    $("#form_topZANGYOUTIME_" + i).val(dataArray[i]['ZANGYOUTIME']); 
                }
                
                // 時間再計算
                calculatetime("teizi", "teizibox");
                calculatetime("zangyo", "zangyobox");
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
        return false;
    }
    //合計セット
    $('.' + act).text(total.toFixed(2));
//    if (value === 'teizi' && total > 7.75 ) {
//        alert("7.75を超えています。");
//    }
//    else if(value === 'teizi' && total < 7.75){
//        alert("7.75以下です。");
//    }
    
    return;
}

