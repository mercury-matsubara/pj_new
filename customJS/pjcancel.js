//終了ボタン押下時
function pjcancel(){
    var len = $(".checkBox:checked").length;
    // チェックされているチェックボックスの数
    if (len > 0)
    {
        var code = [];
        //name取得
        $('.checkBox:checked').each(function() {
            code.push($(this).attr('name'));
        });
        //ダイアログ作成
        $("#dialog").dialog({
            //×ボタン隠す
            open: $(".ui-dialog-titlebar-close").hide(),
            modal:true,
            autoOpen: true,
            buttons:
                    {
                        "ＯＫ": function ()
                        {
                            for(var i=0;i<len;i++)
                            {
                                $('<input>').attr({
                                    type: 'hidden',
                                    name: 'pjcancel'+i,
                                    value: code[i]
                                }).appendTo('.list');
                            }
                            $('<input>').attr({
                                    type: 'hidden',
                                    name: 'pjcancelCount',
                                    value: len
                                }).appendTo('.list');
                            $('#pjcancel').submit();
                        },
                        "キャンセル": function () {
                            $(this).dialog("close");
                        }

                    }
        });
    } 
    else 
    {
        alert("終了キャンセル処理を行うプロジェクトが選択されていません。");
    }
}
