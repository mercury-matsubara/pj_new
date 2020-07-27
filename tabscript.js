/* ===========================================
  定数定義
===========================================*/
const tabItemClass = 'glossarytab-item';             // タブ要素のクラス名
const tabItemSelectClass = 'glossarytab-selected';   // タブ選択時にタブ要素に付与されるクラス名
const tabContentsId = 'panel_area';            // 読み込みコンテンツを表示する要素のid名

/* ===========================================
  変数定義
===========================================*/
let intItemPath = 'tabcontent.php';                   // ロード時に読み込むコンテンツの初期値
let intItemTab = '[data-tab="'+ intItemPath +'?id=SAISINANKEN_2"]';   // ロード時にtabItemSelectClassを付与するtabItemClass要素の初期値

let defaultPage = 'SAISINANKEN_2';
/* ===========================================
  関数定義
===========================================/

/*---------------------------------------------------
 ロード時に発火する関数
 urlパラメータで「?tab=x」となっている場合 (x-1)番目の【tabItemClass】要素の「data-tab」に設定されているコンテンツを読み込む
---------------------------------------------------*/
function pageLoad()
{
	//urlパラメータを'&'区切りで配列urlParametersを生成
	const urlParameters = location.search.slice(1).split('&');

	// urlパラメータがあった場合の処理
	if( urlParameters[0] !== "" )
	{
		parameterArray = new Array;

		// urlParametersから連想配列 parameterArray を生成
		// パラメータ ?key=value を parameterArray[key] = value となるように parameterArray に入れていく。
		for( var i in urlParameters )
		{
			let y = urlParameters[i].split('=');
			parameterArray[y[0]] = y[1];
		}

		// parameterArray["tab"]の値 が undefined でない かつ parameterArray["tab"]の値が【tabItemClass】の要素数より小さい場合
		if( parameterArray["tab"] !== undefined && parameterArray["tab"] < $('.'+tabItemClass).length )
		{
			let intItemPath = $('.'+tabItemClass).eq(parameterArray["tab"]).attr("data-tab");
			let intItemTab = '[data-tab="'+ intItemPath +'"]';
			tabLoad(intItemPath,intItemTab);
		}
		else
		{      
			let intItemTab = '[data-tab="'+ intItemPath +'"]';
			tabLoad(intItemPath,intItemTab);
		}
	}
	else
	{  
		// urlパラメータがなかった場合の処理
		//let intItemTab = '[data-tab="'+ intItemPath +'?id=SAISINANKEN_2"]';
		tabLoad(intItemPath,intItemTab);
	}
}


/*---------------------------------------------------
ロード時に読み込むコンテンツと、タブに【tabItemSelectClass】を付与する関数
---------------------------------------------------*/
function tabLoad(intItemPath,intItemTab)
{
	//var itemPath = intItemPath + '?id=' + defaultPage;
	$('.'+tabItemClass).eq(0).addClass(tabItemSelectClass);
	let itemPath = $('.'+tabItemClass).eq(0).attr('data-tab');                                         // クリックしたタブの「data-tab」を取得
	$.ajax(itemPath,
	{
		type: 'get',
		dataType: 'html'
	})
	.done(function(data)
	{
		$.when(
			$('#'+tabContentsId).append('<div id="'+tabContentsId+'-current">'+data+'</div>')
		).done(function()
		{
				$('#'+tabContentsId+'-current').slideDown(300);                                     //contents2をスライドダウンで表示
		});
	});
}


/*---------------------------------------------------
タブをクリックしたときに発火する関数
---------------------------------------------------*/
function tabClick(){
	$('.'+tabItemClass).on('click',function()
	{
		let contentsPath = $(this).attr('data-tab');                                         // クリックしたタブの「data-tab」を取得
		$('.'+tabItemClass+'.'+tabItemSelectClass).removeClass(tabItemSelectClass);          // 選択されていたタブからクラス【tabItemSelectClass】を除去
		$(this).addClass(tabItemSelectClass);                                                // クリックしたタブに【tabItemSelectClass】を付与  
		$.ajax(contentsPath,
		{
			type: 'get',
			dataType: 'html'
		})
		.done(function(data)
		{
			$.when(
				$('#'+tabContentsId+'-current').slideUp(300),                                       // 表示されていたコンテンツ内容（contents1）をスライドアップで非表示
//				$('#'+tabContentsId+'-current').fadeOut(300),                                       // 表示されていたコンテンツ内容（contents1）をスライドアップで非表示
				$('#'+tabContentsId).append('<div id="'+tabContentsId+'-next">'+data+'</div>')   // 【tabContentsId】の中に、次に表示するコンテンツ内容（contents2）を追加
			).done(function()
			{
				$.when(
					$('#'+tabContentsId+'-next').slideDown(300)                                      //contents2をスライドダウンで表示
//					$('#'+tabContentsId+'-next').fadeIn(300)                                      //contents2をスライドダウンで表示
				).done(function(){
					$('#'+tabContentsId+'-current').remove();                                      //contents1をhtmlから削除                                     
					$('#'+tabContentsId+'-next').attr('id',''+tabContentsId+'-current');           //contents2のid名を「【tabContentsId】-next」から「【tabContentsId】-current」に変更
				});
			});
		});
	});
}
