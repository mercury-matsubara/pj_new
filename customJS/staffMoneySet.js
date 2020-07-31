//金額の再計算
function calculateReturn()
{
    var money = 0;   
    var total = 0;
    $('.kingaku').each(function() {
            money = ($(this).val());
            
            if(money === "")
            {
                money = 0;
            }
            
            //計算
            total += parseInt(money) ;
        });
        //金額合計セット
        $('#total').val(total);
}
//社員別金額設定
function setMoney()
{
    if(document.getElementById('total').value == document.getElementById('form_pjtCHARGE_0').value)
    {
            if(confirm("入力内容正常確認。\n記入金額で個別金額を設定しますがよろしいですか？"))
            {
                    return true;
            }
            else
            {
                    return false;
            }
    }
    else
    {
            if(confirm("入力内容正常確認。\nプロジェクト金額と合計金額が異なります。\n合計金額でプロジェクト金額を変更しますがよろしいですか？"))
            {
                    return true;
            }
            else
            {
                    return false;
            }
    }
//    //ダイアログ作成
//    $("#set_dialog").dialog({
//        //×ボタン隠す
//        open: $(".ui-dialog-titlebar-close").hide(),
//        autoOpen: true,
//        buttons:
//                {
//                    "ＯＫ": function ()
//                    {
//                        $(this).dialog("close");
//                    },
//                    "キャンセル": function () {
//                        $(this).dialog("close");
//                    }
//                }
//            });
}
//社員別金額クリア
function clearMoney()
{
    //ダイアログ作成
    $("#clear_dialog").dialog({
        //×ボタン隠す
        open: $(".ui-dialog-titlebar-close").hide(),
        autoOpen: true,
        buttons:
                {
                    "ＯＫ": function ()
                    {
                        $('.money').each(function() {
                                $(this).val("");
                        });
                        //金額合計セット
                        $('#total').val(0);
                        $(this).dialog("close");
                    },
                    "キャンセル": function () {
                        $(this).dialog("close");
                    }
                }
            });
}
//プロジェクト削除
function deletePj(code)
{
    //ダイアログ作成
    $("#delete_dialog").dialog({
        //×ボタン隠す
        open: $(".ui-dialog-titlebar-close").hide(),
        autoOpen: true,
        buttons:
                {
                    "ＯＫ": function ()
                    {
                        $('<input>').attr({
                                    type: 'hidden',
                                    name: '5CODE',
                                    value: code
                        }).appendTo('.list');
                        $('<input>').attr({
                            type: 'hidden',
                            name: 'Comp'
                        }).appendTo('.list');
                $('#staffMoneySet').submit();
                    },
                    "キャンセル": function () {
                        $(this).dialog("close");
                    }
                }
            });
}