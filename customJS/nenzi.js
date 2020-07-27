//ボタン押下時
function check()
{
        var judge = true;
        var res = confirm("年次処理を行いますがよろしいですか。");
        if ( res == true ) { 
                // OKボタンを押した時の処理
        } else {
                judge = false;
        }
        return judge;
}