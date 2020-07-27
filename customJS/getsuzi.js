// 期と月を取得
function set_value()
{
        document.getElementById('period').value = document.getElementById('period_0').value;
        document.getElementById('month').value = document.getElementById('month_0').value;
}
//月次処理ボタン押下時
function check()
{
        var judge = true;
        var res = confirm("月次処理を行いますがよろしいですか。");
        if ( res == true ) { 
                // OKボタンを押した時の処理
        } else {
                judge = false;
        }
        return judge;
}