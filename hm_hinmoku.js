// ver 1.0 2018/11/21

var HINMEI_NAME = '';//品目
var TANKA_NAME = '';//単価
var SURYO_NAME = '';//数量
var TANNI_NAME = '';//単位
var ZEIRISTU_NAME = '';//税率
var KINGAKU_NAME = '';//金額項目
var KINGAKUKEI_NAME = '';//税抜金額合計
var ZEI_NAME = '';//税額
var TOTAL_NAME = '';//税込み金額合計
var FRACTION = '';//税抜き金額端数処理
var ZEIFRACTION = '';//税込み金額端数処理


//AutoCompleteの制御
function updateAutocompleteHIMValue(ctrlname,identifier,poststr)
{
	$(ctrlname).autocomplete(
	{
		source    : function(request, response){
			$.ajax(
			{
				url: 'json.php',
                                scriptCharset: 'utf-8',
                                type: 'GET',
                                data: {
                                    key: '',
                                    search: $(ctrlname).val(),
                                    table_id: identifier
                                },
                                dataType: 'json',
                                timeout: 5000,
                                cache: false,

                                success: function (data)
                                {
                                    var dataArray = data.results;
                                    var arrayData = [];
                                    var counter = 0;

                                    $.each(dataArray, function (i)
                                    {
                                        var hashData = {};
                                        hashData['label'] = dataArray[i].LABEL;
                                        hashData['value'] = dataArray[i].VALUE;
                                        hashData['code'] = dataArray[i].KEY;
                                        hashData['TANNI'] = dataArray[i].TANNI;
                                        hashData['TANKA'] = dataArray[i].TANKA;
                                        hashData['ZEI'] = dataArray[i].ZEIRITSU;

                                        arrayData[counter] = hashData;
                                        counter++;
                                    });
                                    response(arrayData);
                                }
			});
		},
		autoFocus : true,
		delay     : 100,
		minLength : 0,
		
		select : function(e, ui)
		{
			if(ui.item)
			{
				$('#'+TANKA_NAME+poststr).val(ui.item.TANKA);
				$('#'+TANNI_NAME+poststr).val(ui.item.TANNI);
				$('#'+ZEIRISTU_NAME+poststr).val(ui.item.ZEI);
			}
		}
	}).focus(function() {
		$(this).autocomplete('search', '');
	});
}

//金額の計算
function calculateKingaku( poststr )
{
    //値の取得
    var tanka = $('#' + TANKA_NAME + poststr).val();
    var suryo = $('#' + SURYO_NAME + poststr).val();
    var fraction = $('#' + FRACTION + '_0').val();// 税抜き金額端数処理
    var kingaku;

    if (tanka !== '' && suryo !== '') {
        //計算
        kingaku = tanka * suryo;
        //金額セット
        kingaku = fractionProcess(kingaku, fraction);
        $('#' + KINGAKU_NAME + poststr).val(kingaku);
    }
}

//金額の再計算
function calculateReturn()
{
    var kingaku;
    var fraction = $('#' + FRACTION + '_0').val();// 税抜き金額端数処理
    for (var i = 0; i < 15; i++) {
        //値の取得
        var tanka = $('#' + TANKA_NAME + '_0_' + i).val();
        var suryo = $('#' + SURYO_NAME + '_0_' + i).val();
        
        if (tanka !== '' && suryo !== '') {
            //計算
            kingaku = tanka * suryo;
            //金額セット
            kingaku = fractionProcess(kingaku, fraction);
            $('#' + KINGAKU_NAME + '_0_' + i).val(kingaku);
        }
    }
}

// 合計金額計算処理
function calculateTotal(){
    //合計金額計算
    var goukei = 0;
    var kingaku;
    var kingaku_zeibetsu = {0: 0, 8: 0, 10: 0};//コピー用
    var fraction = $('#' + FRACTION + '_0').val();// 税抜き金額端数処理    
    var zeifraction = $('#' + ZEIFRACTION + '_0').val();// 税込み金額端数処理
    
    for (var i = 0; i < 15; i++) {

        kingaku = $('#' + KINGAKU_NAME + '_0_' + i).val();
        var zei = $('#' + ZEIRISTU_NAME + '_0_' + i).val();

        if (kingaku == '') {
            kingaku = 0;
        }
        kingaku = parseInt(kingaku);
        goukei = goukei + kingaku;
        // 税率セット
        kingaku_zeibetsu[zei] = kingaku_zeibetsu[zei] + parseInt(kingaku);
    }
    
    // hidden作成
    if ( document.getElementById("tax8") === null || document.getElementById("tax10") === null ){
        createHidden(kingaku_zeibetsu);
    }
    
    goukei = fractionProcess(goukei, fraction);
    //税抜き金額セット
    $('#' + KINGAKUKEI_NAME + '_0').val(goukei);
    //消費税
    var tax8 = fractionProcess(kingaku_zeibetsu[8] * 0.08, zeifraction);
    var tax10 = fractionProcess(kingaku_zeibetsu[10] * 0.1, zeifraction);
    $('#tax8').val(tax8);
    $('#tax10').val(tax10);
//    var zei_goukei = kingaku_zeibetsu[8] * 0.08 + kingaku_zeibetsu[10] * 0.1;
//    zei_goukei = fractionProcess(zei_goukei, zeifraction);
    var taxtotal = tax8 + tax10;
    $('#' + ZEI_NAME + '_0').val(taxtotal);
    //税込み金額
    var total = goukei + taxtotal;
    $('#' + TOTAL_NAME + '_0').val(total);
}

// 端数処理
function fractionProcess(value,math){
    var total;
    if (math === "1"){
        total = Math.round( value );
    } else if(math === "2"){
        total = Math.ceil( value );
    } else {
        total = Math.floor( value );
    }
    
    return total;
}

// 税金のhidden作成
function createHidden(value){
    
    $('<input>').attr({
        type: 'hidden',
        id: 'tax8',
        name: 'zei8',
        value: value[8]
    }).appendTo('#edit');
    
    $('<input>').attr({
        type: 'hidden',
        id: 'tax10',
        name: 'zei10',
        value: value[10]
    }).appendTo('#edit');
}

//行操作
var row_copy = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'', ZEIRISTU:'0', KINGAKU:'' };//コピー用
var row_init = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'', ZEIRISTU:'0', KINGAKU:'' };//クリア用
var color_copy = { HINMEI:'', TANKA:'', SURYO:'', TANNI:'' };//カラーコピー用
var color_init = { HINMEI:'#fff', TANKA:'#fff', SURYO:'#fff', TANNI:'#fff' };//カラークリア用
//行のデータを連想配列に入れて返す
function getRowData(pos){
	var row = {};
	row.HINMEI = $('#'+HINMEI_NAME+'_0_'+pos).val();
	row.TANKA =  $('#'+TANKA_NAME+'_0_'+pos).val();
	row.SURYO =  $('#'+SURYO_NAME+'_0_'+pos).val();
	row.TANNI =  $('#'+TANNI_NAME+'_0_'+pos).val();
	row.ZEIRISTU =  $('#'+ZEIRISTU_NAME+'_0_'+pos).val();
	row.KINGAKU =  $('#'+KINGAKU_NAME+'_0_'+pos).val();	
	
	return row;
}
//指定行に連想配列のデータをセットする
function setRowData(pos,row){
	$('#'+HINMEI_NAME+'_0_'+pos).val(row.HINMEI );
	$('#'+TANKA_NAME+'_0_'+pos).val( row.TANKA );
	$('#'+SURYO_NAME+'_0_'+pos).val( row.SURYO );
	$('#'+TANNI_NAME+'_0_'+pos).val( row.TANNI );
	$('#'+ZEIRISTU_NAME+'_0_'+pos).val( row.ZEIRISTU );
	$('#'+KINGAKU_NAME+'_0_'+pos).val( row.KINGAKU );	
}
//行の色データを連想配列に入れて返す
function getColor(pos){
    var color = {};
    color.HINMEI = $('#'+HINMEI_NAME+'_0_'+pos).css('background-color');
    color.TANKA =  $('#'+TANKA_NAME+'_0_'+pos).css('background-color');
    color.SURYO =  $('#'+SURYO_NAME+'_0_'+pos).css('background-color');
    color.TANNI =  $('#'+TANNI_NAME+'_0_'+pos).css('background-color');

    return color;
}
//指定行に連想配列の色データをセットする
function setColor(pos,color){
        $('#'+HINMEI_NAME+'_0_'+pos).css({'background-color':color.HINMEI,'border-width':'1px'});
        $('#'+TANKA_NAME+'_0_'+pos).css({'background-color':color.TANKA,'border-width':'1px'});
        $('#'+SURYO_NAME+'_0_'+pos).css({'background-color':color.SURYO,'border-width':'1px'});
        $('#'+TANNI_NAME+'_0_'+pos).css({'background-color':color.TANNI,'border-width':'1px'});	
}
//指定行をグローバル変数にコピーする
function copyRow( pos ){
	row_copy = getRowData(pos);
        color_copy = getColor(pos);
}
//指定行にグローバル変数のデータをコピーする
function pasteRow( pos ){
	setRowData(pos,row_copy);
        setColor(pos,color_copy);
	calculateKingaku( '_0_'+String(pos) );
}
//指定箇所に空白行を挿入する
function insertRow( pos ){
	var pos_copy_to=14;
	for( ; pos_copy_to > pos; pos_copy_to-- )	{	//下から順に指定行まで処理
		var row = getRowData(pos_copy_to-1);
                var color = getColor(pos_copy_to-1);
		setRowData(pos_copy_to,row);
                setColor(pos_copy_to,color);
	}
	setRowData(pos,row_init);	//指定行は空白にする
        setColor(pos,row_init);         //指定行は白色にする
	calculateKingaku( '_0_'+String(pos) );
}
//指定箇所の行を削除してつめる
function removeRow( pos ){
	var pos_copy_to = pos;
	for( ; pos_copy_to < 14; pos_copy_to++ )	{	//上から順に14行目まで処理
		var row = getRowData(pos_copy_to+1);
                var color = getColor(pos_copy_to+1);
		setRowData(pos_copy_to,row);
                setColor(pos_copy_to,color);
                }
	setRowData(pos_copy_to,row_init);	//15行目は空白にする
        setColor(pos_copy_to,color_init);	//15行目は白色にする
	calculateKingaku( '_0_'+String(pos) );
}

/**
 * セッションストレージにデータを保持
 * 
 */
function saveStorage() {
    
    //データを保存
    for (var i = 0; i< 15;i++) {
        sessionStorage.setItem('HINMEI' + i, $('#'+HINMEI_NAME+'_0_' + i).val());
        sessionStorage.setItem('TANKA' + i, $('#'+TANKA_NAME+'_0_' + i).val());
        sessionStorage.setItem('SURYO' + i, $('#'+SURYO_NAME+'_0_' + i).val());
        sessionStorage.setItem('TANNI' + i, $('#'+TANNI_NAME+'_0_' + i).val());
        sessionStorage.setItem('KINGAKU' + i, $('#'+KINGAKU_NAME+'_0_' + i).val());
    }
    sessionStorage.setItem('HINMEI_NAME',HINMEI_NAME);
    sessionStorage.setItem('TANKA_NAME',TANKA_NAME);
    sessionStorage.setItem('SURYO_NAME',SURYO_NAME);
    sessionStorage.setItem('TANNI_NAME',TANNI_NAME);
    sessionStorage.setItem('KINGAKU_NAME',KINGAKU_NAME);
    sessionStorage.setItem('FLG','1');
}
/**
 * セッションストレージの情報から一覧にセットする
 * 
 */
function importStorage() {
    
    //データをセット
    if(sessionStorage.getItem('FLG') === "1") {
        for (var i = 0; i< 15;i++){
            $('#'+ sessionStorage.getItem('HINMEI_NAME')+'_0_'+ i).val(sessionStorage.getItem('HINMEI' + i));
            $('#'+ sessionStorage.getItem('TANKA_NAME')+'_0_' + i).val(sessionStorage.getItem('TANKA' + i));
            $('#'+ sessionStorage.getItem('SURYO_NAME')+'_0_' + i).val(sessionStorage.getItem('SURYO' + i));
            $('#'+ sessionStorage.getItem('TANNI_NAME')+'_0_' + i).val(sessionStorage.getItem('TANNI' + i));
            $('#'+ sessionStorage.getItem('KINGAKU_NAME')+'_0_' + i).val(sessionStorage.getItem('KINGAKU' + i));
        }
    }
    sessionStorage.clear();
}