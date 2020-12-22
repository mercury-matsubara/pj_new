//期またぎボタン押下時
function nenziperiod(){
    var len = $("input[name='frmSAIYO']:checked").length;
    // チェックされているチェックボックスの数
    if (len > 0)
    {
        var code = $("input[name='frmSAIYO']:checked").val();

        //ダイアログ作成
        $("#dialog").dialog({
            //×ボタン隠す
            //open: $(".ui-dialog-titlebar-close").hide(),
            modal:true,
            autoOpen: true,
            buttons:
                    {
                        "ＯＫ": function ()
                        {
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'nenziperiod',
                                value: code
                            }).appendTo('.list');
                            $('#nenziperiod').submit();
                        },
                        "キャンセル": function () {
                            $(this).dialog("close");
                        }

                    }
        });
    } 
    else 
    {
        alert("期またぎを行うプロジェクトが選択されていません。");
    }
}
