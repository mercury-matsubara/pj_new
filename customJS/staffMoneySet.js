//金額の再計算
function calculateReturn()
{
    var kingaku = 0;
    for (var i = 1; i < 28; i++) {
        //値の取得
        var tanka = $('#money_' + i).val();
        
        if(tanka === "")
        {
            tanka = 0;
        }
        
        //計算
        kingaku += parseInt(tanka) ;
        //金額合計セット
        $('#total').val(kingaku);
    }
}