
function Regist()
{
    //ダイアログ作成
    $("#dialog").dialog({
        //×ボタン隠す
        open: $(".ui-dialog-titlebar-close").hide(),
        autoOpen: true,
        buttons:
                {
                    "ＯＫ": function ()
                    {
                        //エレメント作成
                        var ele = document.createElement("input");
                        //データを設定
                        ele.setAttribute("type", "hidden");
                        ele.setAttribute("name", "Comp");
                        ele.setAttribute("value", "");
                        // 要素を追加
                        //document.send.appendChild(ele);
                        $("#send").append(ele);
                        //submit処理
                        $("#send").submit();

                    },
                    "キャンセル": function () {
                        $(this).dialog("close");
                    }

                }
    });
}


function Delete()
{
    //ダイアログ作成
    $("#dialog").dialog({
        //×ボタン隠す
        open: $(".ui-dialog-titlebar-close").hide(),
        autoOpen: true,
        buttons:
                {
                    "ＯＫ": function ()
                    {
                        //エレメント作成
                        var ele = document.createElement("input");
                        //データを設定
                        ele.setAttribute("type", "hidden");
                        ele.setAttribute("name", "CompDel");
                        ele.setAttribute("value", "");
                        
                        // 要素を追加
                        //document.send.appendChild(ele);
                        $("#send").append(ele);
                        //submit処理
                        $("#send").submit();

                    },
                    "キャンセル": function () {
                        $(this).dialog("close");
                    }

                }
    });
}
