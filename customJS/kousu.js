
//定時時間の再計算
function calculateReturnTeizi()
{
    var time = 0;   
    var total = 0;
    $('.teizi').each(function() {
            time = ($(this).val());
            
            if(time === "")
            {
                time = 0;
            }
            
            //計算
            total += parseInt(time) ;
        });
        //合計セット
        $('#teizi_total').val(total);
}
//残業時間の再計算
function calculateReturnZangyo()
{
    var time = 0;   
    var total = 0;
    $('.zangyo').each(function() {
            time = ($(this).val());
            
            if(time === "")
            {
                time = 0;
            }
            
            //計算
            total += parseInt(time) ;
        });
        //合計セット
        $('#zangyo_total').val(total);
}