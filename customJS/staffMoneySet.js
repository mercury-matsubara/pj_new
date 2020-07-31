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
    var id = "";
    if($('#total').val() === $('#form_pjtCHARGE_0').val())
    {
        id = "#set_dialog_1";
    }
    else
    {
        id = "#set_dialog_2";
    }
    //ダイアログ作成
    $(id).dialog({
        //×ボタン隠す
        open: $(".ui-dialog-titlebar-close").hide(),
        autoOpen: true,
        buttons:
        {
            "ＯＫ": function ()
            {
                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'Comp',
                                    value: 'set'
                                }).appendTo('.list');
                $('#staffMoneySet').submit();
            },
            "キャンセル": function () {
                $(this).dialog("close");
            }
        }
    });
}
//社員別金額クリア
function clearMoney()
{
//    //ダイアログ作成
//    $("#clear_dialog").dialog({
//        //×ボタン隠す
//        open: $(".ui-dialog-titlebar-close").hide(),
//        autoOpen: true,
//        buttons:
//                {
//                    "ＯＫ": function ()
//                    {
                        $('.kingaku').each(function() {
                                $(this).val("");
                        });
                        //金額合計セット
                        $('#total').val(0);
                        $(this).dialog("close");
//                    },
//                    "キャンセル": function () {
//                        $(this).dialog("close");
//                    }
//                }
//            });
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
                                    name: 'Comp',
                                    value: 'del'
                                }).appendTo('.list');
                        $('#staffMoneySet').submit();
                    },
                    "キャンセル": function () {
                        $(this).dialog("close");
                    }
                }
            });
}