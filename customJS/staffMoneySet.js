//金額の再計算
function calculateReturn()
{
    var money = 0;   
    var total = 0;
    $('.money').each(function() {
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
    //ダイアログ作成
    $("#set_dialog").dialog({
        //×ボタン隠す
        open: $(".ui-dialog-titlebar-close").hide(),
        autoOpen: true,
        buttons:
                {
                    "ＯＫ": function ()
                    {
                        $(this).dialog("close");
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
function deletePj()
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