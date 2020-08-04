
/**
 * PJ登録画面作成
 * 
 * 
 */

$(function(){
    // ボタン複数ある場合
    $('input#popup').each(function () {
        
        // ボタンクリック時イベント
        $(this).on("click", function (event) {

            let contentsPath = $(this).attr('data-action');
            // 非同期通信
            $.ajax(contentsPath,
            {
                type: 'get',
                dataType: 'html'
            })
            .done(function (data)
            {
                // ポップアップ内容追加
                $(".modal__content").append(data);
                $('.js-modal').fadeIn();
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
});