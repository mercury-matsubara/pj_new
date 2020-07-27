
/**
 * PJ登録画面作成
 * 
 * 
 */

$(function(){
    // tdクリック時イベント
    $("#popup").on("click", function(event){
        
        let contentsPath = $(this).attr('data-action');
        $.ajax(contentsPath,
	{
            type: 'get',
            dataType: 'html'
	})
	.done(function(data)
	{
            // ポップアップ内容追加
            $(".modal__content").append(data);
            $('.js-modal').fadeIn();
            return false;
        });


        
        

    });


    $('.js-modal-close').on('click',function(){
        $('.js-modal').fadeOut();
        return false;
    });
});