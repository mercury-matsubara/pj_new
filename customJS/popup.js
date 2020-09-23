
//反映ID
var popupKey = '';
var itemnum = '';

/**
 * ポップアップ画面作成
 */
$(function(){
    // ボタン複数ある場合
    $('input#popup').each(function () {
        // ボタンクリック時イベント
        $(this).on("click", function (event) {
            // 反映id取得
            popupKey = $(this).attr('popup-key');
            // 行数取得
            itemnum = $(this).attr('itemnum');
            // URL
            let contentsPath = $(this).attr('data-action');
            // 非同期通信
            $.ajax(contentsPath,
            {
                type: 'get',
                dataType: 'html',
                data: {
                    key: '',
                    search: ''
                }
            })
            .done(function (data)
            {
                // ポップアップ内容追加
                $(".modal__content").append(data);
                $('.js-modal').fadeIn();
                return false;
            })
            .fail(function() {
                // データ取得失敗
                alert('データの取得に失敗しました。');
                $(".modal__content").html("");
                return false;
                
            });
        });
    });
    
    // 戻るボタン押下時
    $('.js-modal-close').on('click',function(){
        // html
        $(".modal__content").html("");
        $('.js-modal').fadeOut();
        return false;
    });
    
    // データを取得して返す関数
    var getkRowsData = function() {
    var data = [];
    var popupcolumn = $('#pop_column').val();
    let column = popupcolumn.split(',');
    $('input[name="frmSAIYO"]:checked').each(function(i, elm) {
        
        // 変数定義
        var $input = $(elm),
            row = $input.closest('tr');
        for(var i = 0; i < column.length; i++){
            data[i] = row.find('#' + column[i] ).text();
        }
    });
    return data;
};

  // 登録ボタン押下時
  $('#insert').on('click', function() {
        

        var rowsData = getkRowsData();
        // カンマ区切り
        let key = popupKey.split(',');
        // 項目にセット
        for (var i = 0; i < key.length; i++) {
            $('#form_' + key[i] + '_' + itemnum).val(rowsData[i]);
        }
        $(".modal__content").html("");
        $('.js-modal').fadeOut();
        return false;
    });
});


/**
 * 表示ボタン押下時
 * @param {type} filename
 * @returns {undefined}
 */
function ajaxSearch(filename){
//    var a = $('#form_pjtPROJECTNUM_0').val();
//    var b = $('#form_pjnPROJECTNUM_0').val();
//    alert(a);
    // URL
    let contentsPath = $('#search_' + filename).attr('data-action');
    // 検索項目
    let searchContents = $('#clear_' + filename).val();
    // カンマ区切り
    let key = searchContents.split(',');
    // 連想配列作成
    let contentArray = {};
    for (var i = 0; key.length > i; i++){
        contentArray[key[i]] = $('#form_' + key[i] + '_0').val();
    }
    
    // 非同期通信
    $.ajax(contentsPath,
    {
        type: 'get',
        dataType: 'html',
        data: {
            key: key,
            search: contentArray
        }
    })
    .done(function (data)
    {
        // データ取得成功
        // ポップアップ内容追加
        $(".list_content").html("");
        $(".list_content").append(data);
        return false;
    })
    .fail(function() {
        // データ取得失敗
        alert('データの取得に失敗しました。');
        $(".list_content").html("");
        return false;
    });
    
}

