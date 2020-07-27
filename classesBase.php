<?php

/**
 * BaseObject
 * BasePage、BaseLogicExecuterの継承元となるクラス。共通メンバを定義する。
 *
 */

class BaseObject
{
	/** form.iniの全情報を保持するarray */
	protected $prContainer;
	/** 処理対象となるテーブル */
	protected $prMainTable;//テーブル名
	/** ページ識別 */
	protected $prFileNameInsert;//登録ファイル名
	/** ページ入力情報*/
	protected $prPageEdit;//ページ編集情報
	protected $prPageInsert;//ページ登録情報
	/** ﾘｽﾄの表示件数(makeListV2でセットするのでその後でなければ入っていない) */
	protected $prListCount;
	
	protected $prJudge;
	
	/**
	 * コンストラクタ
	 * 
	 * @param PageContainer $FormIni	form.iniの全情報
	 * 
	 */
	public function __construct(&$container) 
	{
		require_once("f_Construct.php");
		require_once ("f_Button.php");
		require_once ("f_DB.php");
		require_once ("f_Form.php");
		require_once ("f_SQL.php");
		$this->prContainer =  $container;
		$this->prListCount = 0;
	}
}

/**
 * Pageオブジェクト用のBaseクラス
 */
class BasePage extends BaseObject
{
	/** ページのタイトル */
	protected $prTitle;
	/** 初期化用のスクリプトとなる文字列。ページ末尾に出力される */
	protected $prInitScript;

	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		header('Expires:-1');
		header('Cache-Control:'); 
		header('Pragma:');
	}

	/**
	 * 関数名: echoAllHtml
	 *   ページ用のHTMLを出力する処理
	 * 各HTML文字列作成関数を順に呼び出し、最後に出力する
	 */
	public function echoAllHtml()
	{
		//html作成
		$html = '<!DOCTYPE html PUBLIC "-//W3C/DTD HTML 4.01">';
		$html .= '<html>';
		//<head>を構築
		$html .= '<head>';
		//$html .= $this->makeTitle();
		$html .= $this->makeGeneralHeader();
		$html .= $this->makeStylePart();
		$html .= $this->makeScriptPart();
		$html .= '</head>';
		//<body>を構築
		$html .= '<body>';
		$html .= $this->makeBoxHeader();
		$html .= $this->makeBoxMenu();
		$html .= $this->makeBoxMainContent();
		$html .= '</body>';
		$html .= $this->makeAfterScript();
		$html .= '</html>';
		
		echo $html, PHP_EOL;
		
	}
	
	
	
	
	/**
	 * 関数名: makeGeneralHeader
	 *   汎用のヘッダ文字列(HTML)を作成する関数
	 * 
	 * @retrun HTML文字列
	 */
	public function makeGeneralHeader()
	{
		$html = '<title>';
		$html .= $this->prTitle;
		$html .='</title>';
		$html .= '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
		$html .='<link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet"><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome-animation/0.0.10/font-awesome-animation.css" type="text/css" media="all" />
                        <link rel="icon" type="image/png" href="./image/favicon.ico">
			<link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1/themes/redmond/jquery-ui.css" >
			<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.css">
			<link rel="stylesheet" href="./css/jquery.datetimepicker.css">
			<link rel="stylesheet" href="./css/MonthPicker.css">';
		return $html;
		
	}
	
	/**
	 * 関数名: makeStylePart
	 *   CSS定義文字列(HTML)を作成する関数
	 * (基本的にはCSSファイルへのリンクを作成)
	 * 
	 * @retrun HTML文字列
	 */
	function makeStylePart()
	{
		$html = '<link rel="stylesheet" type="text/css" href="./css/list_css.css">';
                $html .= '<link rel="stylesheet" type="text/css" href="./css/popup.css">';
		return $html;
	}
	
	/**
	 * 関数名: makeScriptPart
	 *   JavaScript文字列(HTML)を作成する関数
	 *   HEADタグ内に入る
	 *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeScriptPart()
	{
		$html ='<!---↓jQuery---!>
			<script src="./js/jquery.js"></script>
			<script src="./js/jquery.datetimepicker.full.min.js"></script>
			<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.min.js"></script>
			<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1/i18n/jquery.ui.datepicker-ja.min.js"></script>
			<script src="./js/MonthPicker.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/spectrum.min.js"></script>
			<script src="https://cdnjs.cloudflare.com/ajax/libs/spectrum/1.8.0/i18n/jquery.spectrum-ja.min.js"></script>
			<script src="https://ajaxzip3.github.io/ajaxzip3.js" charset="UTF-8"></script>
			<!---↑jQuery---!>
			<script src="./js/pulldown.js"></script>
			<script src="./js/jquery.corner.js"></script>
                        <script src="./inputcheck.js"></script>
			<script src="./list_jQuery.js"></script>
			<script src="./tabscript.js"></script>
			<script src="./hm_hinmoku.js"></script>
                        <script src="./regist.js"></script>';                       
		return $html;
		
	}
	
	/**
	 * 関数名: makeBoxHeader
	 *   画面上部に表示されるシステム名表示部分のHTML文字列を作成する
	 * 基本的に<div class="titlebox">タグで囲む
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxHeader()
	{
		$html = '<div class="titlebox" id="print">';
		$html.= '<div class="main_title"><h1>';
		$html.= APPLICATION_NAME;

		$html.= '</h1>';
		$html.='<div class="tantomei" >';
		$html.= '担当:';
		$html.= mb_convert_encoding($_SESSION['STAFFNAME'], "UTF-8","SJIS");
		$html.= '</div>';
		$html.= '</div>';
                $html.= '<div class="mlogo"><img src="./image/mlogo.png" class="logo"></div>';
		$html.= '</div>';  
		return $html;
	}
	
	/**
	 * 関数名: makeBoxMenu
	 *   画面左に表示されるメニュー部分のHTML文字列を作成する
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxMenu()
	{
		//画面左側ボタン作成
		$html = '';
		$html .= '<div class="pkg_contents" id="print">';
		$html .='<div  class="main_menu">';
		$html .= makeAllMenu();
		$html .='</div></div>';
		return $html;
	}
	
	/**
	 * 関数名: makeBoxMainContent
	 *   タイトル、メニューを除くメインの機能提供部分のHTML文字列を作成する
	 *   BoxHeader、BoxMenu後にコールされ、内部的にはmakeBoxContentTop、
	 *   makeBoxContentMain、makeBoxContentBottomを順に呼び出す。
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxMainContent()
	{
		//画面中央作成
		$html = '';
		$html .= $this->makeBoxContentTop();
		$html .= $this->makeBoxContentMain();
		$html .= $this->makeBoxContentBottom();
		return $html;
		
	}
	
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentTop()
	{
		$filename = $this->prContainer->pbFileName;
		if(strstr($filename, '_5') != false)
		{
			$this->prTitle = "";
			$html = "";
		}
		else
		{	
			$html = '<div class = "center"><a class = "title_list">';
			$html .= $this->prTitle;  //タイトル表示
			$html .= '</a></div>';
		}
		
		return $html;
	}
	
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分のHTML文字列を作成する
	 *   リストでは一覧表示、入力では各入力フィールドの構築など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$html = '';
		return $html;
		
	}

	/**
	 * 関数名: makeBoxContentBottom
	 *   メインの機能提供部分下部のHTML文字列を作成する
	 *   他ページへの遷移ボタンなどを作成
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentBottom()
	{
		$html = '';
		return $html;
		
	}
	
	/**
	 * 関数名: makeAfterScript
	 *   BODYタグの後ろに埋め込むJavaScript文字列を作成する
	 * 
	 * @retrun HTML文字列
	 */
	function makeAfterScript()
	{	
		$html = '<script language="JavaScript">';
			
		$html.= ' $("#contents .sub-menu > a").click(function (e) {
					$("#contents ul ul").slideUp(), $(this).next().is(":visible") || $(this).next().slideDown(),
					e.stopPropagation();
				});';
		
		$html .= 'function makeDatepicker()
			{' ;
		$html.= $this->prInitScript;
		$html.= '}
				</script>';
		return $html;	
	}
	
	
	/**
	 * 登録フォーム用のHTMLを返す
	 * V2はtableタグで囲まれた文字列として返す
	 *
	 * @param int  $post 入力内容
	 * @param  $errorinfo
	 * @param  $isReadOnly
	 * @param  $form_name
	 * @param $form_ini 画面設定値
	 * @param $ParamSet 項目設定値 
	 * 
	 * @return array
	 */
	function makeformInsert_setV2( $post, $errorinfo, $isReadOnly, $form_name ,&$container)
	{

		//------------------------//
		//          定数          //
		//------------------------//
		$columns_string = $container->pbPageSetting['page_columns'];
		$readonly_string = $container->pbPageSetting['readonly'];																	// 読取専用項目

		//V3を呼ぶ
		$input_result_v3 = $this->makeformInsert_setV3( $post, $columns_string, $errorinfo, $readonly_string, $form_name ,$container->pbParamSetting);
		//<TD>配列
		$td_array = $input_result_v3[0];
		//<TABLE>構築
		$insert_str = '<table name ="formedit" id ="edit">';
		foreach($td_array as $td){
			$insert_str .= '<tr>'.$td.'</td>';
		}	
		$insert_str .= '</table>';

		//戻り値
		$form_result[0] = $insert_str;
		$form_result[1] = $input_result_v3[1];

		return ($form_result);
	}

	/**
	 * 登録フォーム用のHTMLを返す
	 * V3はtableのtdタグ配列で返す。何らかの特殊編集を行う場合はこちらを使用する
	 *
	 * @param  $post 入力内容
	 * @param  $columns_string
	 * @param  $out_err_string
	 * @param  $readonly_string
	 * @param $form_name 
	 * @param $param_setting 項目設定値 
	 * 
	 * @return array
	 */
	function makeformInsert_setV3( &$post, $columns_string, $out_err_string, $readonly_string, $form_name ,&$param_setting)
	{
		//------------------------//
		//          定数          //
		//------------------------//
		$out_err_column = explode(',',$out_err_string);																// 入力チェック(php側)で不可カラム番号配列
		$columns_array = explode(',',$columns_string);																// 登録カラム一覧(配列)
		$readonly_array = explode(',',$readonly_string);															// 読み取り専用項目(配列)

		//------------------------//
		//          変数          //
		//------------------------//
		$colum = "";																						// 作成対象フォームのカラム番号
		$form_format = "";																					// 作成対象フォーム 入力可能条件 form.ini 'form*_format'
		$form_length = "";																					// 作成対象フォーム 入力可能桁数 form.ini 'form*_length'
		$form_isJust = "";																					// 作成対象フォーム 入力可能桁数 form.ini 'form*_length'
		$form_delimiter = "";																				// 作成対象フォーム 区切り文字 form.ini 'form*_length'
		$ctrl_name = "";																					// 作成対象フォーム name
		$insert_str = "";																					// 入力フォームhtml 戻り値
		$check_column_str = "";																				// 入力チェック対象フォームname(csv)
		$notnull_column_str = "";																			// 入力必須フォームテーブル番号(csv)
		$notnull_type_str = "";																				// 入力必須フォームテーブル番号(csv)
		$isnotnull = 0;																						// 入力必須項目判断
		$isout = false;																						// 作成対象フォームが入力チェック(php側)不可カラムか
		$form_result = array();																				// リストテーブルの繰り返しID配列
		$max_over = -1;																						// リストテーブルの繰り返し最大数
		$error ="";
		$datepicker ="";
		$input_result = array();																					// リストテーブルの繰り返しID配列
		$insert_td = array();

		//------------------------//
		//          処理          //
		//------------------------//

		// 登録カラム数分ループ
		for($i = 0 ; $i < count($columns_array) ; $i++)
		{
			//項目HTML初期化
			$insert_str = '';

			//項目名
			$colum = $columns_array[$i];
			//読み取り専用指定
			$isReadOnly = $readonly_array[$i];
			//デフォルトになるコントロールID
			$ctrl_name = "form_".$colum."_0";

			//エラー表示の分解
			for($outcounter1 = 0 ; $outcounter1 < count($out_err_column) ; $outcounter1++)
			{
				if( $out_err_column[$outcounter1] == "" )
				{
					continue;
				}
				if(strstr($out_err_column[$outcounter1], $colum))
				{
					$out = explode(',',$out_err_column[$outcounter1]);
					for($outcounter2 = 0 ; $outcounter2 < count($out) ; $outcounter2++)
					{
						$error .= $param_setting[$out[$outcounter2]]['item_name'].",";
					}
					$error = substr($error,0,-1);
					$isout = true;
				}
			}

			//型を取得
			$form_type = $param_setting[$colum]['form1_type'];

			//NotNull指定
			if($param_setting[$colum]['isnotnull'] == 1)	
			{
				$notnull_column_str .= $colum.",";
				$notnull_type_str .= $form_type.",";
				$isnotnull = 1;
			}
			else
			{
				$isnotnull = 0;
			}

			//項目設定値
			$form_format = $param_setting[$colum]['form1_format'];
			$form_length = $param_setting[$colum]['form1_length'];
			$form_isJust = $param_setting[$colum]['isJust'];
			$form_delimiter = $param_setting[$colum]['form1_delimiter'];

			//チェック用文字列
			if($form_type == 6)
			{
				// エラーチェック無し
			}
			else if($form_type > 9)
			{
				//チェックとりあえずなし
			}
			else
			{
				$check_column_str .= $ctrl_name."~".$form_length."~".$form_format."~".$isnotnull."~".$form_isJust.",";
			}

			//HTML
			$input_result = $this->getFormHtml($param_setting, $colum, $post, $isnotnull, $form_name ,$isReadOnly);
			$insert_str .= $input_result[0];	//コントロール用HTML
			$datepicker .= $input_result[1];	//構築用スクリプト

			//エラー出力
			if($isout)
			{
				$insert_str .="<td></td><td>";
				$insert_str .="</td><td><a class='error'>"
								.$error."は既に登録されています。</a>";
				$insert_str .="</td>";
				$isout = false;
				$error = "";
			}

			$insert_td[] = $insert_str;

		}	// 登録カラム数分ループ

		//チェック変数
		$_SESSION['check_column'] = rtrim($check_column_str,',');
		$_SESSION['notnullcolumns'] =rtrim($notnull_column_str,',');
		$_SESSION['notnulltype'] = rtrim($notnull_type_str,',');
		$_SESSION['max_over'] = $max_over;

		//戻り値
		$form_result[0] = $insert_td;
		$form_result[1] = $datepicker;

		return ($form_result);
	}

	/**
	 * 入力項目用の文字列を返す
	 * 
	 *
	 * @param [array]  $form_ini	form.iniを読込んだarray 項目設定値
	 * @param [string] $colum		form.iniで指定している項目名
	 * @param [array] $post 送信情報
	 * @param [string] $isnotnull	IsNull判定結果
	 * 
	 * @return string
	 */
	function getFormHtml(&$form_ini, $colum, &$post, $isnotnull, $form_name, $readonly_setting)
	{
		$result = array();
		$javascript_str = '';
                $form_pull_str = '';
		//項目名
		$form_label_str ='<td class = "space"></td><td class ="one">';/* @var $form_element_str 戻り値?となるフォームHTML文字列 */
		$form_label_str .= '<a class = "itemname">';
		$form_label_str .= $form_ini[$colum]['item_name'];
		$form_label_str .= '</a></td>';

		//型を取得
		$form_type = intval($form_ini[$colum]['form1_type']);

		//その他設定値
		$form_size = $form_ini[$colum]['form1_size'];
		$form_delimiter = $form_ini[$colum]['form1_delimiter'];

		//デフォルトになるコントロール値
		$element_id = 'form_'.$colum.'_0';
		$element_name = 'form_'.$colum.'_0';
		$input_type = 'text';

		//読取専用判定
		$is_readonly = false;
		$readonly_attribute = '';
		$readonly_class = '';
		if( $readonly_setting == 1 || 
			($readonly_setting == 2 && $form_name == 'insert') ||
			($readonly_setting == 3 && $form_name == 'edit') ||
			($form_name == 'delete') )
		{
			$is_readonly = true;
			$readonly_attribute = 'readonly';
			$readonly_class = 'readOnly';
		}

		//表示値
		$form_value = "";
		if(isset($post[$element_id]))
		{
			$form_value = $post[$element_id];
		}
		else
		{
			$column_name = $form_ini[$colum]['column'];
			if(isset($post[$column_name]))
			{
				$form_value = $post[$column_name];
			}
		}
		//デフォルト値
		if($form_value == "")
		{
			$default = $form_ini[$colum]['default'];
			if($default != "")
			{
				$form_value = getAutoUpdateValue($default, $post);
			}
		}
		
		//hiddenはここまでとする
		if($readonly_setting == 4)
		{
                    $result[0] = "";
                    $result[1] = "";
                    // プルダウン非表示の場合は
                    if($form_type != 9)
                    {
                        $form_element_str = '<input type ="hidden" name = "'.$element_name.'" id = "'.$element_id.'" value = "'.$form_value.'" >';
                        $result[0] = $form_element_str;
			$result[1] = $javascript_str;
                    }
                    return $result;
		}

		$required = '';
		if($isnotnull == 1){
			$required = ' required';	
		}
		
		
		//入力項目の構築
		$form_element_str = $form_delimiter.'<input type ="'.$input_type.'" name = "'.$element_name.'" id = "'.$element_id.'" class = "'.$readonly_class.'" value = "'.$form_value.'" size = "'.$form_size.'" '.$readonly_attribute.$required.'  >';
		if($form_type === 3)
		{
			/********** 日付コントロール **********/
			//入力項目用HTML文
			if($is_readonly === false )
			{
				//Datepickerスクリプト
//				$javascript_str .= "$('#$element_id').datepicker();";
				$javascript_str .= "$('#$element_id').datepicker({
                                                                                    showOn: 'button',
                                                                                    buttonImage: './image/icon.gif',
                                                                                    buttonImageOnly: true
                                                                                });";
				//デフォルト表示値
				if($form_value != "")
				{
					$date_str = str_replace ('-', '/', $form_value);
					if($date_str !== $form_value)
					{
						$form_element_str = str_replace ( $form_value, $date_str, $form_element_str );
					}
					$javascript_str .= "$('#$element_id').datepicker('setDate', '$date_str');";
				}
			}
                    
		}
		else if($form_type === 4)
		{
			/********** 日時コントロール **********/
			//Datetimepickerスクリプト
			//入力項目用HTML文
			if($is_readonly === false ){
				$javascript_str .= "$('#".$element_id."').datetimepicker();";
			}
		}
		else if($form_type === 5)
		{
			/********** 年月コントロール **********/
			//Datepickerスクリプト
			if($is_readonly === false ){
                                $javascript_str .= "$('#".$element_id."').MonthPicker({Button: '<img src=\"./image/icon.gif\" class=\"ui-datepicker-trigger\"></img>'})";
			}
		}
		else if($form_type === 6)
		{
			//カラーコントロール
			if($is_readonly === false ){
				$javascript_str .= "$('#$element_id').spectrum(";
				$javascript_str .= "{ color: '$form_value', preferredFormat: 'hex'});";
			}

		}
		else if($form_type === 8)
		{
			//テキストエリア
			$row = ceil( intval($form_size) / 70 );
			if( $row > 10 )
			{
				$row = 10;
			}
			//入力項目用HTML文
			$form_element_str = $form_delimiter.'<textarea name = "'.$element_name.'" id = "'.$element_id.'" cols = "70" rows="'.$row.'" class = "'.$readonly_class.'" '.$readonly_attribute.' >'.$form_value.'</textarea>';

		}
		else if($form_type === 9)
		{
			//プルダウン
			$pulldpwn = $form_ini[$colum]['pul_num'];
			$form_element_str= $this->pulldown_setV2($pulldpwn, $element_name, $form_value, $readonly_class, $form_name, $isnotnull);
		}
		else if($form_type === 10)
		{
			$form_element_str = '-';
			//未指定かどうか
			if( $form_value !== '' && $form_value !== '0' )
			{
				//リンク設定からsuggest越しにひっぱる
				$link = $this->prContainer->pbParamSetting[$colum]['link_to'];
				$ref_search = $this->prContainer->pbParamSetting[$colum]['ref_search'];
				//suggest関数を使って結果を取得
				$sug_result = getSuggestValue( $ref_search, $form_value );
				if( $sug_result !== null )
				{
					$form_element_str = "<a href='main.php?".$link."_button=&edit_list_id=".$form_value."'>".$sug_result[0]['LABEL']."</a>";
				}
				$form_element_str .= '<input type ="hidden" name = "'.$element_name.'" id = "'.$element_id.'" value = "'.$form_value.'" >';
			}
		}
		else
		{
			$form_element_str = '';
			// 設定値
			$form_format = $form_ini[$colum]['form1_format'];
			$form_length = $form_ini[$colum]['form1_length'];
			$form_isJust = $form_ini[$colum]['isJust'];
			$form_delimiter = $form_ini[$colum]['form1_delimiter'];
			$refrer = $form_ini[$colum]['ref_key_input'];
			$table_num = $form_ini[$colum]['table_num'];
			$align = $form_ini[$colum]['list_align'];
			$check_js = '';
			if($align == 2)
			{
				$readonly_class .= ' txtmode3';//右寄せ
			}
			else
			{
				$readonly_class .= ' txtmode2';
			}	
			

			//入力項目分岐
			if($form_type === 2)
			{
				$input_type = 'file';
			}
			else if($form_type === 7)
			{
				$input_type = 'password';
				//パスワードは1個だと「保存しますか？」が出ることがあるので、ダミーを作る
				$form_element_str .= '<input type ="'.$input_type.'" name = "'.$element_name.'_dummy" id = "'.$element_id.'_dummy" style="display:none;" >';
			}
			else
			{
				$input_type = 'text';
				//IME制御
				if( $form_format > 5 )
				{
					$readonly_class .= ' txtmode1';
				}
				else
				{
					$input_type = 'tel';

				}
				$check_js = 'onChange = " return inputcheck(\''.$element_name.'\','.$form_length.','.$form_format.','.$isnotnull.','.$form_isJust.')"';
			}

			//特殊設定
			$sp = $form_ini[$colum]['sp'];
			$onKeyUp = '';
			if($sp != '' )
			{
				//指定がある場合
				$sp_setting = explode(':',$sp);
				if( count($sp_setting) > 1 && $is_readonly === false )
				{
					// "zip"で郵便番号指定
					if($sp_setting[0] == 'zip')
					{
						$onKeyUp = 'onKeyUp="AjaxZip3.zip2addr(this,\'\',\'form_' .$sp_setting[1]. '_0\',\'form_' .$sp_setting[1]. '_0\');" ';
					}
					// "auto"でIDを伴うautocomplete補完指定
					if($sp_setting[0] == 'auto')
					{
						$javascript_str .= 'updateAutocompleteByID(\'#'.$element_name.'\',\'#form_' .$sp_setting[2]. '_0\',\''.$sp_setting[1].'\');';
					}
                                        
				}
                                if($sp_setting[0] == 'pull')
                                {
                                    //横プルダウン作成
                                    $pulldpwn = $form_ini[$sp_setting[1]]['pul_num'];
                                    $pullname = 'form_'.$sp_setting[1].'_0';
                                    if(isset($post[$pullname]))
                                    {
                                        $pullvalue = $post[$pullname];
                                    } else {
                                        loadJisyaMaster();
                                        $pullset = explode($table_num,$sp_setting[1]);
                                        $pullvalue= $_SESSION[$pullset[1]];
                                    }
                                    //アイコン作成プルダウン作成
                                    $form_pull_str ='<div class="icon icon--minus icon--plus "><span class="icon__mark"></span></div>';
                                    $form_pull_str .='<div class="pull">'.$this->pulldown_setV2($pulldpwn, $pullname, $pullvalue, "", $form_name, $isnotnull).'</div>';
                                }
                        }

			//IDに対する値の表示用HTML
			$temp_element_str = '';
			//参照指定がある場合、参照用項目を作成
			if($refrer != '' && $refrer != $table_num )
			{
				//参照用項目名
				$element_show = $element_name.'_show';
				//値がわたってきていれば取得
				$value_show = '';
				if(isset($post[$element_show]))
				{
					$value_show = $post[$element_show];
				}
				//構築文字列
				$temp_element_str = '<input type ="text" name = "'.$element_show.'" id = "'.$element_id.'_show" class = "'.$readonly_class.'" value = "'.$value_show.'" size = "40" '.$readonly_attribute.$required.' >';
				if($is_readonly === false)
				{
					$javascript_str .= "updateAutocomplete('#$element_id','$refrer');";
				}
				if($form_value != "")
				{
					$javascript_str .= "updateShowValue('#$element_id','$refrer');";
				}
				if( $readonly_attribute !== 'readonly' )
				{
					$readonly_attribute = 'readonly';
					$readonly_class .= ' readOnly';
				}
			}
			$form_element_str .= $form_delimiter.'<input type ="'.$input_type.'" name = "'.$element_name.'" id = "'.$element_id.'" class = "'.$readonly_class.'" value = "'.$form_value.'" size = "'.$form_size.'" '.$readonly_attribute.$required.' '.$onKeyUp.' '.$check_js. ' >';
			$form_element_str .= $temp_element_str;
		}

		$result[0] = $form_label_str.'<td class = "two">'.$form_element_str.$form_pull_str.'</td>';
		$result[1] = $javascript_str;

		return $result;
	}

	/**
	 * 入力項目用の文字列を返す
	 * 
	 *
	 * @param [array]  $form_ini	form.iniを読込んだarray
	 * @param [string] $colum		form.iniで指定している項目名
	 * @param [string] $form_value	設定値があれば指定。なければブランク
	 * @param [string] $isnotnull	IsNull判定結果
	 * 
	 * @return string
	 */
	function getFormHtmlElement(&$form_ini, $colum, $form_value, $isnotnull, $form_name, $element_name, $readonly)
	{
		require_once 'f_Construct.php';																			// f_From関数呼び出し

		$result = array();

		$form_element_str ='';/* @var $form_element_str 戻り値?となるフォームHTML文字列 */
		$javascript_str = '';

		//型を取得
		$form_type = $form_ini[$colum]['form1_type'];

		//その他設定値
		$form_size = $form_ini[$colum]['form1_size'];
		$form_delimiter = $form_ini[$colum]['form1_delimiter'];

		//デフォルトになるコントロール値
		$element_id = $element_name;
		$input_type = 'text';
		//値が空の場合はデフォルトをとる
		if($form_value == "")
		{
			$default = $form_ini[$colum]['default'];
			if($default != "")
			{
				$form_value = $default;
			}
		}
		//読み取り専用
		$readonly_element = '';
		$readonly_class = '';
		if($readonly === '1')
		{
			$readonly_element = 'readonly';
			$readonly_class = 'readOnly ';
		}

		//element文字列デフォルト
		$default_element_str =  $form_delimiter.'<input type ="'.$input_type.'" name = "'.$element_name.'" id = "'.$element_id.'" value = "'.$form_value.'" size = "'.$form_size.'" '.$readonly_element.' >';
		//入力項目の構築
		if($form_type == 3)
		{
			/********** 日付コントロール **********/
			//入力項目用HTML文
			$form_element_str .= $default_element_str;

			//Datepickerスクリプト
			$javascript_str .= "$('#$element_id').datepicker();";
			$javascript_str .= "$('#$element_id').datepicker('option', 'showOn', 'button');";
			//デフォルト表示値
			if($form_value != "")
			{
				$date_str = str_replace ('-', '/', $form_value);
				$javascript_str .= "$('#$element_id').datepicker('setDate', '$date_str');";
			}

		}
		else if($form_type == 4)
		{
			/********** 日時コントロール **********/
			//入力項目用HTML文
			$form_element_str .= $default_element_str;

			//Datetimepickerスクリプト
			$javascript_str .= "$('#".$element_id."').datetimepicker();";
		}
		else if($form_type == 5)
		{
			/********** 年月コントロール **********/
			//入力項目用HTML文
			$form_element_str .= $default_element_str;

			//Datepickerスクリプト
			//$javascript_str .= "$(\"#".$element_id."\").MonthPicker({ Button: '<button>...</button>' });";
			$javascript_str .= "$('#".$element_id."').MonthPicker({ Button: '<img class=\"icon\" src=\"image/icon.gif\" />' });";
		}
		else if($form_type == 6)
		{
			//カラーコントロール
			//入力項目用HTML文
			$form_element_str .= $default_element_str;

			//spectrumスクリプト
			$javascript_str .= "$('#$element_id').spectrum(";
			$javascript_str .= "{ color: '$form_value', preferredFormat: 'hex'});";

		}
		else if($form_type == 8)
		{
			//テキストエリア

			//入力項目用HTML文
			$form_element_str .= $form_delimiter.'<textarea name = \"'.$element_name.'" id = "'.$element_id.'" cols = "50" '.$readonly_element.' >'.$form_value.'</textarea>';

		}
		else if($form_type == 9)
		{
			//プルダウン
	//		$element_name = "form_".$colum;
			$over = "";
			$pulldpwn = $form_ini[$colum]['pul_num'];

			$form_element_str.= $this->pulldown_setV2($pulldpwn, $element_name, $form_value, $readonly_class, $form_name, $isnotnull);
		}
		else
		{
			// 設定値
			$form_format = $form_ini[$colum]['form1_format'];
			$form_length = $form_ini[$colum]['form1_length'];
			$form_isJust = $form_ini[$colum]['isJust'];
			$form_delimiter = $form_ini[$colum]['form1_delimiter'];
			$refrer_key = $form_ini[$colum]['ref_key_input'];
			$refrer_value = $form_ini[$colum]['ref_value_copy'];
			$table_num = $form_ini[$colum]['table_num'];
			$align = $form_ini[$colum]['list_align'];

	//		//チェック用文字列
	//		$check_column_str .= $element_name."~".$form_length."~".$form_format."~".$isnotnull."~".$form_isJust.",";

			$check_js = '';
			$class = $readonly_class.'txtmode2';

			//入力項目分岐
			if($form_type == 2)
			{
				$input_type = 'file';
			}
			else if($form_type == 7)
			{
				$input_type = 'password';
				//パスワードは1個だと「保存しますか？」が出ることがあるので、ダミーを作る
				$form_element_str .= '<input type ="'.$input_type.'" name = "'.$element_name.'_dummy" id = "'.$element_id.'_dummy" style="display:none;" >';
			}
			else
			{
				$input_type = 'text';
				$check_js = 'onChange = " return inputcheck(\''.$element_name.'\','.$form_length.','.$form_format.','.$isnotnull.','.$form_isJust.')"';

				//IME制御
				if( $form_format > 4 )
				{
					$class = $readonly_class.'txtmode1';
				}
				else if ( $align == 2 )
				{
					$input_type = 'tel';
					$class = $readonly_class.'txtmode3';			
				}
			}

			//特殊設定
			$sp = $form_ini[$colum]['sp'];
			$onKeyUp = '';
			if($sp != '' )
			{
				//指定がある場合
				$sp_setting = explode(':',$sp);
				if(count($sp_setting) > 1)
				{
					// "zip"で郵便番号指定
					if($sp_setting[0] == 'zip')
					{
						$onKeyUp = 'onKeyUp="AjaxZip3.zip2addr(this,\'\',\'form_' .$sp_setting[1]. '_0\',\'form_' .$sp_setting[1]. '_0\');" ';
					}
				}
			}

			//入力項目用HTML文
			$form_element_str .= $form_delimiter.'<input type ="'.$input_type.'" name = "'.$element_name.'" id = "'.$element_id.'" class = "'.$class.'" value = "'.$form_value.'" size = "'.$form_size.'" '.$onKeyUp.' '.$check_js.' '.$readonly_element.' >';

			//参照指定がある場合、参照用項目を作成
			if($refrer_key != '' && $refrer_key != $table_num )
			{
				$form_element_str .= '<input type ="text" name = "'.$element_name.'_show" id = "'.$element_id.'_show" value = "" size = "40">';
				$javascript_str .= "updateAutocomplete('#$element_id','$refrer_key');";
				if($form_value != "")
				{
					$javascript_str .= "updateShowValue('#$element_id','$refrer_key');";
				}
			}
			//参照指定がある場合、参照用項目を作成
			if($refrer_value != '' )
			{
				$javascript_str .= "updateAutocompleteValue('#$element_id','$refrer_value');";
			}
		}

		$result[0] = $form_element_str;
		$result[1] = $javascript_str;

		return $result;
	}
	
	/************************************************************************************************************
	function makeformSearch_setV2($post,$form_name)

	引数	$post

	戻り値	なし
	************************************************************************************************************/
	function makeformSearch_setV2( $post, $main_form_name )
	{
		//------------------------//
		//        初期設定        //
		//------------------------//
		$form_ini = $this->prContainer->pbFormIni;

		//------------------------//
		//          定数          //
		//------------------------//
		$filename = $this->prContainer->pbFileName;
		$columns = $form_ini[$filename]['sech_form_num'];
		$columns_array = explode(',',$columns);
		$label_string = $form_ini[$filename]['sech_form_labels'];
		$columns_labels = explode(',',$label_string);
		$fromto_columns = $form_ini[$filename]['sech_fromto_column'];
		$fromto_columns_array = explode(',',$fromto_columns);

		//------------------------//
		//          変数          //
		//------------------------//
		$column = "";
//		$form_item_name = "";
		$serch_str = "";
		$check_column_str = "";
		$makeDatepicker = "";
		$readonlyHeader= false;

		//------------------------//
		//          処理          //
		//------------------------//
		// 2018/06/29 追加
		if( isset( $post['readonlyHeader'] ) )
		{
			$readonlyHeader= $post['readonlyHeader'];
		}
		// 2018/06/29 追加

		//hiddenで画面IDを埋め込む
		$serch_str .= '<input type="hidden" name="'.$this->prContainer->pbFileName.'" >';
		
		//<table>開始
		$serch_str .= "<table name ='formInsert' id ='serch'>";

		//検索項目数分ループ
		for($i = 0 ; $i < count($columns_array) ; $i++)
		{
			//列名取得
			$column = $columns_array[$i];
			if($column == "")
			{
				break;
			}

			//項目名
			$element_name = "form_".$column."_0";
//			$form_item_name = $form_ini[$column]['item_name'];

			$serch_str .="<tr><td><a class = 'itemname'>";
			$serch_str .= $columns_labels[$i];
			$serch_str .= "</a></td>";
			$serch_str .= "<td>";
			
			//エレメント構築
			$element_array = $this->makeFormSearchElement( $column, $form_ini, $post, $element_name, $main_form_name );
			//
			$serch_str .= $element_array[0];
			$makeDatepicker .= $element_array[1];
			$check_column_str .= $element_array[2];
			
			if( in_array( $column, $fromto_columns_array ) )
			{
				$element_name = "form_".$column."_1";
				//エレメント構築
				$element_array = $this->makeFormSearchElement( $column, $form_ini, $post, $element_name, $main_form_name );
				//
				$serch_str .= '　-　'.$element_array[0];
				$makeDatepicker .= $element_array[1];
				$check_column_str .= $element_array[2];				
			}
			
			$serch_str .= "</td></tr>";

		}

	

		$serch_str .= '</table>';
		
		$check_column_str =  substr($check_column_str,0,-1);
		$_SESSION['check_column'] = $check_column_str;

		$returnStr = array();
		$returnStr[0] =  $serch_str;
		$returnStr[1] =  $makeDatepicker;

		return ($returnStr);

	}
	
	/************************************************************************************************************
	function makeformSearch_setV2($post,$form_name)

	引数	$post

	戻り値	なし
	************************************************************************************************************/
	function makeFormSearchElement( $column, &$form_ini, $post, $element_name, $main_form_name )
	{
		//
		$serch_str = '';
		$after_script = '';
		$check_column_str = '';
		
		
		$form_format_type = $form_ini[$column]['form1_type'];

		//POSTされた値があるか
		$form_value = "";
		if(isset($post[$element_name]))
		{
			$form_value = $post[$element_name];
		}
		
		// 判定基準入れ替え
		if($form_format_type == 3)
		{
			//日付コントロール
			$datepickerArray = datepickerDate_set( $element_name, $post );
			$serch_str.= $datepickerArray[0];
			$after_script.= $datepickerArray[1];
		}
		else if($form_format_type == 4)
		{
			/********** 日時コントロール **********/
			$serch_str .= '<input type ="text" name = "'.$element_name.'" id = "'.$element_name.'" value = "'.$form_value.'" size = "'.$form_ini[$column]['form1_size'].'"  >';
			$after_script .= "$('#".$element_name."').datetimepicker();";
		}
		else if($form_format_type == 9)
		{
                    if(isset($form_ini[$column]['sp']))
                    {
                        //HTMLを取得
                        $serch_str.= $this->pulldown_setV3($element_name, $post, "", "form", 0);
                    }
                    else
                    {
			//プルダウン指定を取得
			$pulldpwn = $form_ini[$column]['pul_num'];
			//HTMLを取得
			$serch_str.= $this->pulldown_setV2($pulldpwn, $element_name, $form_value, false, $main_form_name, false, true);
                    }
		}
		else
		{
			//その他テキスト

			//INI設定値
			$form_size = $form_ini[$column]['form1_size'];
			$form_format = $form_ini[$column]['form1_format'];
			$form_length = $form_ini[$column]['form1_length'];
			$form_delimiter = $form_ini[$column]['form1_delimiter'];
			$form_align = $form_ini[$column]['list_align'];
			
			$input_type = 'text';
			$check_js = 'onChange = " return inputcheck(\''.$element_name.'\','.$form_length.','.$form_format.',false,2)"';
			$check_column_str .= $element_name."~".$form_length."~".$form_format."~".false."~2,";

			//IME制御
			if($form_align === 2)
			{
				$form_input_type = ' class = "txtmode3"';
			}
			else
			{
				$form_input_type = ' class = "txtmode2"';
			}
			
			if( $form_format > 4 )
			{
				$form_input_type = ' class = "txtmode1"';
			}

			$serch_str .= $form_delimiter.'<input type ="'.$input_type.'" name = "'.$element_name.'" id = "'.$element_name.'" value = "'.$form_value.
							'" size = "'.$form_size.'" '.$check_js.$form_input_type.' >';

			//参照指定がある場合は、オートコンプリート
			$refrer = $form_ini[$column]['ref_search'];
			if($refrer != "")
			{
				$after_script .= "updateAutocomplete('#" .$element_name. "','" .$refrer. "');";
			}
		}
		
		$result_array = array();
		
		$result_array[0] = $serch_str;
		$result_array[1] = $after_script;
		$result_array[2] = $check_column_str;
		
		return $result_array;
	}

	/************************************************************************************************************
	function pulldown_set($type,$name,$over,$post,$readonly,$element_name,$isnotnull)

	引数	$post

	戻り値	なし
	************************************************************************************************************/
	function pulldown_setV2($type, $element_name, $formvalue, $readonly, $form_name, $isnotnull, $isAddNoneSel=false)
	{

		//------------------------//
		//          定数          //
		//------------------------//

		//------------------------//
		//          変数          //
		//------------------------//
		$pulldown = '';
		$text = '';
		$value ='';
		$select = '';
		$isSelect = false;
		$disable = '';

		//------------------------//
		//          処理          //
		//------------------------//
		$hkey = 'hanyou.'.$type;
		//SESSIONに保存されているか？されていなければ取る
		if( array_key_exists( $hkey, $_SESSION ) === false )
		{
			// db接続関数実行
			$con = dbconect();
			$judge = false;
			// クエリ発行
			$sql = "SELECT * FROM hanyoumaster WHERE HKEY = '$type' ORDER BY HVALUE";
			$result = $con->query($sql) or ($judge = true);
			if($judge)
			{
				error_log($con->error,0);
				return false;
			}

			$list = array();
			while($result_row = $result->fetch_array(MYSQLI_ASSOC))
			{
				//DBの値
				$text = $result_row['HLABEL'];
				$value = $result_row['HVALUE'];
				//リストを作成
				$list[$value] = $text;
			}
			$result->close();
			//セッションにセット
			$_SESSION[$hkey] = $list;
		}
		//セッションから取得
		$list_array = $_SESSION[$hkey];

		//ReadOnly指定
		if($readonly == '' || $formvalue === '' )
		{
			$disable = '';
		}
		else
		{
			$disable = 'disabled';
		}

		//項目名
		$pulldown.='<select id="'.$element_name.'" class ="cp_ipselect '.$readonly.'" name="'.$element_name.'" onMouseOver ="change(this.id,\''
					.$readonly.'\',\''.$form_name.'\');" onChange = "notnullcheck(this.id,'.$isnotnull.',\''.$element_name.'\');">';

		//null可の場合
		if($isAddNoneSel == true)
		{
			$select = '';
			if($formvalue === '' )	{
				$select = ' selected ';				
			}
			
			$pulldown.='<option value =""'.$select.' >(未指定)</option>';
		}

		foreach ( $list_array as $list_key => $list_text )
		{
 			$select = '';

			//値と一致しているか
			if($formvalue !== '' && $list_key == $formvalue)
			{
				//一致している場合は、選択状態に
				$select = ' selected ';
				$isSelect = true;
			}
			else
			{
				$select = $disable;
			}

			$pulldown.='<option value ="'.$list_key.'" '.$select.' >'.$list_text.'</option>';
		}

		return $pulldown;
	}

/**
	 * 
	 * @param type $filename
	 * @param type $form_ini
	 * @param type $post
	 * @return string
	 * 
	 */
	function makeTabHtml($filename, &$form_ini, &$post  )
	{
		$result = array();

		$html = '';
		$script = '';

		$tab_page = '';
		if(	isset($form_ini[$filename]['tab_page']) )
		{
			$tab_page = $form_ini[$filename]['tab_page'];
		}
		if( $tab_page != '' )
		{
			//配列に分解
			$tab_page_array = explode(',', $tab_page);
			//キーを取得
			$tab_page_key = $form_ini[$filename]['tab_page_key'];
			$tab_page_key_array = explode(',', $tab_page_key);

			//デフォルト表示するページ
			$default = '';

			//タブの大枠
			if(strstr($filename, '_5') != false)
			{
				//TOP画面
				$html .= '<div class = "tab_wrap">';
			}
			else
			{	
				$html .= '<div class = "tab_wrap2">';
			}	
			$html .=  '<div id="tab_area" class="tab_area">';
			//タブ項目
			for($i = 0; $i < count($tab_page_array); $i++)
			{
				//添付する条件文
				$cond = '';
				//キー
				$key = $tab_page_key_array[$i];
				if($key != '')
				{
					//フォーム上の名前
					$element_name = "form_".$key."_0";
					//値
					$form_value = '0';
					$column_name = $form_ini[$key]['column'];
					$table_id = $form_ini[$key]['table_num'];
					$table_name = $form_ini[$table_id]['table_name'];
					
					//値を取得する
					if(isset($post[$element_name]))
					{
						$form_value = $post[$element_name];
					}
					else
					{
						if(isset($post[$column_name]))
						{
							$form_value = $post[$column_name];
							$_SESSION[$column_name] = $form_value;
						}
						else if(isset($_SESSION[$column_name]))//案件情報更新時tab情報が見れないための対応処理
						{
							$form_value = $_SESSION[$column_name];
						}	
					}
					$cond = '&'.$key.'='.$form_value;
				}

				$html .=  '<label class="glossarytab-item" data-tab="tabcontent.php?id=' .$tab_page_array[$i].$cond. '">' . $form_ini[$tab_page_array[$i]]['title']. '</label>';

				//デフォルト
				if($default === '')
				{
					$default = $tab_page_array[$i].$cond;
				}
			}
			$html .=  '</div>';
			$html .=  '<div id="panel_area" class="panel_area">';
			//ここにコンテンツが入る
			$html .=  '</div>';
			$html .=  '</div>';

			$script = 'defaultPage=\''.$default.'\';pageLoad();	tabClick();';
		}

		$result[0] = $html;
		$result[1] = $script;

		return $result;
	}

	/************************************************************************************************************
	function makeList($sql,$post)

	引数1	$sql						検索SQL
	引数2	$post						ページ移動時のポスト

	戻り値	list_html					リストhtml
	************************************************************************************************************/
	function makeListV2( $sql, &$post, $limit, $limitstart, $page_mode )
	{	
		//          変数          //
		$columns_array = explode(',',$this->prContainer->pbPageSetting['page_columns']);
		$isCheckBox = $this->prContainer->pbPageSetting['isCheckBox'];
		$isNo = $this->prContainer->pbPageSetting['isNo'];
		$isEdit = $this->prContainer->pbPageSetting['isEdit'];
		$main_table = $this->prContainer->pbPageSetting['use_maintable_num'];
		$table_id = mb_strtoupper($main_table);

		$herf_link_array = explode(',',$this->prContainer->pbPageSetting['herf_link']);
		$column_width_array = explode(',',$this->prContainer->pbPageSetting['column_width']);
		
		//******************** アコーディオン ************************
		$list_type = $this->prContainer->pbPageSetting['list_type'];
		$meisai_columns_array = array();
		$meisai_herf_link_array = array();
		$meisai_sql = array();
		$cond_column = '';
		$meisai_filename = '';
		$meisai_page = null;
		if($list_type == 1)
		{
			$meisai_filename = $this->prContainer->pbFileName.'_M';

			$meisai_container = new PageContainer($this->prContainer->pbFormIni);
			$meisai_container->ReadPage($meisai_filename,$this->prContainer->pbListId,$this->prContainer->pbStep);
			
			$factory = PageFactory::getInstance();
			$meisai_page = $factory->createPage($meisai_filename,$meisai_container);
			
			//SQLを取得
			$meisai_sql = getSelectSQL($post, $meisai_filename);
			
			$cond_key = $meisai_container->pbPageSetting['cond_column'];
			$cond_column =  $meisai_container->pbFormIni[$cond_key]['column'];		
			$meisai_herf_link_array = explode(',',$meisai_page->prContainer->pbPageSetting['herf_link']);
			$meisai_columns_array = explode(',',$meisai_container->pbPageSetting['page_columns']);
		}
		//******************** アコーディオン ************************

		//------------------------//
		//          変数          //
		//------------------------//
		$list_html = "";
		$counter = 1;
		$class = "";
		$totalcount = 0;
		$listcount = 0;
		$result = array();
		$judge = false;

		//------------------------//
		//          処理          //
		//------------------------//
		// db接続関数実行
		$con = dbconect();

		// クエリ発行(カウント文)
		if($page_mode !== PAGE_NONE){
			$result = $con->query($sql[1]) or ($judge = true);
			if($judge)	{
				error_log($con->error,0);
				$judge = false;
			}
			while($result_row = $result->fetch_array(MYSQLI_ASSOC))		{
				$totalcount = $result_row['COUNT(*)'];
			}
		}

		//SQL文成形
		$sql[0] = substr($sql[0],0,-1);						// 最後の';'削除
		$sql[0] .= $limit.";";								// 「LIMIT」追加
		// クエリ発行(実データ取得)
		$result = $con->query($sql[0]) or ($judge = true);
		if($judge)
		{
			error_log($con->error,0);
			$judge = false;
		}
		$listcount = $result->num_rows;						// 検索結果件数取得
		if( $listcount === 0 )
		{
			return "該当データが登録されていません。<br>";
		}
		
		//フラグがONの場合のみ件数表示
		if($page_mode !== PAGE_NONE)
		{
			if ($totalcount == $limitstart )
			{
				$list_html .= $totalcount."件中 ".($limitstart)."件〜".($limitstart + $listcount)."件 表示中";	// 件数表示作成
			}
			else
			{
				$list_html .= $totalcount."件中 ".($limitstart + 1)."件〜".($limitstart + $listcount)."件 表示中";	// 件数表示作成
			}
		}
		if($page_mode === PAGE_COUNT_ONLY){
			//ボタンのつけたし
			$list_html .= $this->makeButtonV2($this->prContainer->pbFileName, 'top', STEP_NONE, '');
		}

		//<table>開始
		$list_html .= "<table class ='list'><thead><tr>";
		//チェックボックス有無
		if($isCheckBox != 0 )
		{
			$list_html .="<th><a class ='head'>選択</a></th>";
		}
		//No.表示有無
		if($isNo == 1 )
		{
			$list_html .="<th><a class ='head'>No.</a></th>";
		}
		$labels_array = explode(',',$this->prContainer->pbPageSetting['column_labels']);
		//項目名（ここがヘッダの主要構成箇所）
		for($i = 0 ; $i < count($labels_array) ; $i++)
		{
			$label = str_replace('※', '', $labels_array[$i]);
			$label = str_replace('＊', '', $label);
			$list_html .="<th class ='textoverflow' ><a class ='head'>".$label."</a></th>";
		}
		//編集ボタンの有無
		if($isEdit == 1)
		{
			$list_html .="<th><a class ='head'>編集</a></th>";
		}
		//ここまでがtableヘッダ部分
		$list_html .= "</tr></thead>\n";

		//ここからデータ部分
		$list_html .= "<tbody>";
		//取得行数分実行
		while($result_row = $result->fetch_array(MYSQLI_ASSOC))
		{
			//ループ変数クリア
			$class = '';
			$uniqueCount = $limitstart + $counter;

			//行のスプライト表示用に、1行毎にIDを変える
			if(($counter%2) == 1)
			{
				$class = "stripe_none";
			}
			else
			{
				$class = "stripe";
			}
			
			//1行書く
			if($list_type == 1)
			{
				$list_html .='<tr class="' .$class. '" onclick="show_hide_row(\'hidden_row'.$uniqueCount.'\');" >';	//行開始
			}
			else
			{
				$list_html .='<tr  class="' .$class. '">';	//行開始
			}
			$list_html .= $this->makeTableTd('', $columns_array, $column_width_array, $herf_link_array, $result_row, $table_id, $uniqueCount);
			$list_html .= "</tr>\n";
			
			//******************** アコーディオン ************************
			if($list_type == 1)
			{
				//明細用のSQLを作成
				$m_sql = $meisai_sql;	//編集用にコピーする
				$m_sql[0] = $m_sql[0].' WHERE '.$cond_column.'='. $result_row[$cond_column].' ';
				$m_sql = setSQLOrderby($meisai_filename, $meisai_page->prContainer->pbFormIni, $m_sql);
				//css用のクラス名にhidden_rowを付け足す
				$meisai_class = $class.' hidden_row';
				// クエリ発行(カウント文)
				$meisai_result = $con->query($m_sql[0]) or ($judge = true);
				if($judge)
				{
					error_log($con->error,0);
					$judge = false;
				}
				else
				{
					while($meisai_result_row = $meisai_result->fetch_array(MYSQLI_ASSOC))
					{
						//ループ変数クリア
						$list_html .='<tr class="' .$meisai_class. '" id = \'hidden_row'.$uniqueCount.'\' >';	//行開始
						$list_html .= $meisai_page->makeTableTd($class, $meisai_columns_array, $column_width_array, $meisai_herf_link_array, $meisai_result_row, $table_id, ($limitstart + $counter));
						$list_html .= "</tr>\n";
					}
				}
			}
			//******************** アコーディオン ************************
			//カウント++
			$counter++;
		}
		// <tabel>終了
		$list_html .="</tbody></table>";
		//ﾘｽﾄの数をメンバにいれる
		$this->prListCount = $counter-1;
		
		////////////////////////////////////////
		//フラグがONの場合のみページを表示
		////////////////////////////////////////
		if($page_mode === PAGE_ALL)
		{
			//１ページ行数
			$limit_count = intval( $this->prContainer->pbPageSetting['limit'] );
			//最大ページ(切り上げ)
			$page_max = ceil( $totalcount / $limit_count );
			//現在ページ
			$page_now = ceil($limitstart / $limit_count);
			
			//URLを調べて、ページ指定以外の部分を切り出す
			$url_parameter = parse_url($_SERVER["REQUEST_URI"], PHP_URL_QUERY );
			//limitstart指定の有無
			if( strpos($url_parameter,'&limitstart=') !== false )
			{
				//ある場合は前後を切り出し
				$start_pos = strpos( $url_parameter, '&limitstart=');
				$first_str = substr( $url_parameter, 0, $start_pos );
				$second_str = substr( $url_parameter, $start_pos+12 );
				//後ろの部分の次パラメータ以降を切断
				if( strpos($second_str,'&') !== false )
				{
					$second_str = substr( $url_parameter, strpos($second_str,'&') );
				}
				else
				{
					$second_str = '';
				}
				//つないで使う
				$url_parameter = $first_str.$second_str;
			}
			//limitstartをつけたし
			$url_parameter .= '&limitstart=';

			//＜（前ページ）作成
			if( $limitstart != 0 )
			{
				//前ページの位置計算
				$start = $limitstart - $limit_count;
				if( $start < 0 )
				{
					$start = 0;
				}
				//リンクありで書き出し
				$list_html .= '<a href="main.php?'.$url_parameter.$start.'"><</a>';
			}
			else
			{
				//最初のページの場合は無効（リンクを貼らない）
				$list_html .= '<';			
			}
			
			//全ページループ
			for($i = 0; $i < $page_max; $i++ )
			{
				//前後5ページまで表示とする
				if( abs($i - $page_now) > 5 )
				{
					continue;
				}

				//開始位置
				$start = $i * $limit_count;
				//現在ページと同じ場合はリンクを貼らない
				if( $start != $limitstart )
				{
					$list_html .= '　<a href="main.php?'.$url_parameter.$start.'">'.($i+1).'</a>';									
				}
				else
				{
					$list_html .= '　'.($i+1);				
				}
			}
			
			//＞（次ページ）作成
			if( ($limitstart+$limit_count) >= $totalcount )
			{
				//最後のページの場合は無効（リンクを貼らない）
				$list_html .= '　>';			
			}
			else
			{
				//次ページの位置計算
				$start = $limitstart + $limit_count;
				if( $start < 0 )
				{
					$start = 0;
				}
				$list_html .= '　<a href="main.php?'.$url_parameter.$start.'">></a>';
			}
		}
		//ここまでページ移動
		////////////////////////////////////////

		return ($list_html);
	}

	
	/************************************************************************************************************
	function makeTableTd($sql,$post)

	引数1	$sql					検索SQL
	引数2	$post				入力情報	

	戻り値	result				リストhtml
	************************************************************************************************************/
	function makeTableTd( $class_origin, &$columns_array, &$column_width_array, &$herf_link_array, &$result_row, $table_id, $rowNo )
	{
		//戻り値
		$rowHtml ='';//行開始
	
		$isCheckBox = $this->prContainer->pbPageSetting['isCheckBox'];
		$isNo = $this->prContainer->pbPageSetting['isNo'];
		$isEdit = $this->prContainer->pbPageSetting['isEdit'];
		$disabled = '';
		
		//チェックボックス
		if($isCheckBox != 0)
		{
			if($isCheckBox == 1)
			{
                                $code = getCode($this->prFileNameInsert);
				//チェックボックス　　　　　　!!!当面非対応
//				$rowHtml .= "<td id = ".$table_id."".$result_row['1CODE']."class = 'center'><input type = 'checkbox' class = 'checkBox' name ='check_".$result_row[$code]."' id = 'checkid_".$result_row[$code]."'";
                                $rowHtml .= "<td class = 'center'><input type = 'checkbox' class = 'checkBox' name ='check_".$result_row[$code]."' id = 'checkid_".$result_row[$code]."'";
				if(isset($post['check_'.$result_row[$code]]))
				{
					$rowHtml .= " checked ";
				}
				$rowHtml .=' onclick="this.blur();this.focus();" ></td>';
			}
			else
			{
                                $code = getCode($this->prFileNameInsert);
				//ラジオボタン
				$rowHtml .="<td class = '".$class_origin." center'><input type = 'radio' name ='frmSAIYO' id = 'frmSAIYO' value='".$result_row[$code]."'>";
			}
		}
		//No.表示
		if($isNo == 1)
		{
			$rowHtml .="<td class='".$class_origin." sequence'><a class='body'>".$rowNo."</a></td>";
		}

		//実データ列
		for($i = 0 ; $i < count($columns_array) ; $i++)
		{
			$column = $columns_array[$i];
			if($column === '' || $column === 'sp01' || $column === 'sp02' )
			{	//ブランクは飛ばす
				$rowHtml .= '<td class="center">'.$column.'</td>';
				continue;
			}
			//何度も見るので設定値を最初に絞る
			$column_setting = $this->prContainer->pbParamSetting[$column];
			//設定ファイルから設定値を取得
			$field_name = $column_setting['column'];
			$format     = $column_setting['format'];
			$type       = $column_setting['form1_type'];
			$valigin    = $column_setting['list_align'];
			$value = $result_row[$field_name];

			//フォーマット指定
			if($format != 0)
			{
				$value = format_change($format, $value, $type);
			}

			//リンク指定の有無
			if(count($herf_link_array) > $i)
			{
				//リンク指定あり？
				if($herf_link_array[$i] !='')
				{
					$href = '';
					if( $type === '2' )
					{
						$href = 'file/'.$value;
					}
					else
					{
						$link_to = $herf_link_array[$i];
						$link_key = $column_setting['link_key'];
						
						$href = "main.php?".$link_to."_button=&edit_list_id=".$result_row[$link_key];
					}
					
					//パラメータ追加
					$href .= $this->makeGetAdditionalListParam($column);
					
					//リンクありの場合、値を<a href >で囲む
					$value = "<a href='".$href."'>".$value."</a>";
					//$value = "<a href='main.php?".$link_to."_button=&form_usr$link_key"."_0=".$result_row[$link_key]."'>".$value."</a>";
				}
			}

			//列幅指定
			$td_width = "";
			if(count($column_width_array) > $i)
			{
				//列幅の固定？
				if($column_width_array[$i]=='1')
				{
					//固定であるなら、サイズ指定を使用して幅を設定
					$width = $column_setting['form1_size'] * 4;
					$td_width = " style='width:".$width."px;'";
				}
			}

			//数値の場合は右寄せ
			switch($valigin)
			{
			case 1:
				$class = $class_origin." center";
				break;
			case 2:
				$class = $class_origin." right";
				break;
			default:
				//$class = "";
				$class = "textoverflow";
			}

			//書き込み
			$rowHtml .="<td class='".$class."'".$td_width." ><a class ='body'>".$value."</a></td>";
		}

		//編集ボタン
		if($isEdit == 1)
		{
                        $code = getCode($this->prFileNameInsert);
			$rowHtml .= "<td class='".$class_origin." edit' valign='top'><input type='submit' name='edit_".
							$result_row[$code]."' value = '編集' ".$disabled."></td>";
		}

		return $rowHtml;
	}

	/************************************************************************************************************
	function makeList_itemV2($sql,$post)

	引数1	$sql					検索SQL
	引数2	$post				入力情報	

	戻り値	result				リストhtml
	************************************************************************************************************/
	function makeList_itemV2( $sql, $post )
	{
		//------------------------//
		//        初期設定        //
		//------------------------//
		$form_ini = $this->prContainer->pbFormIni;
		require_once ("f_Form.php");
		require_once ("f_DB.php");																							// DB関数呼び出し準備
		require_once ("f_SQL.php");																							// DB関数呼び出し準備

		//------------------------//
		//          定数          //
		//------------------------//
		//$value_array = array();

		$main_form_type = $form_ini[$_SESSION['filename']]['form_type'];
		$filename = $_SESSION['filename'];
		$filename_M = $_SESSION['filename'] . '_M';//明細
		if(!isset($form_ini[$filename_M]))
		{
			return 0;
		}

		$columns = $form_ini[$filename_M]['page_columns'];
		$columns_array = explode(',',$columns);
		$readonlys = $form_ini[$filename_M]['readonly'];
		$readonly_array = explode(',',$readonlys);
		$isNo = 1;//$form_ini[$filename]['isNo'];
		$main_table = $form_ini[$filename_M]['use_maintable_num'];
		$columns_count = count($columns_array);

		//明細編集時
		if(isset($this->prContainer->pbListId) && !isset($this->prContainer->pbPageCheck) && !isset($this->prContainer->pbSecondInputContent))
		{
			$use_code = $form_ini[$filename]['use_maintable_num'];//mmh
			$main_code = $this->prContainer->pbListId;//編集するID
			$post = make_headerpost($filename_M,$main_code,$use_code);
		}
		//------------------------//
		//          変数          //
		//------------------------//
		$list_html = "";
		$counter = 1;
		$class = '';
		$result = array();
		$columns_name = '';

		//------------------------//
		//          処理          //
		//------------------------//
		$list_html .= '<table class ="list"><thead><tr>';
		$script_str = '';

		if($isNo == 1 )
		{
			$list_html .='<th><a class ="head">No.</a></th>';
		}
		//列名ヘッダ
		for($i = 0 ; $i < $columns_count; $i++)
		{
			$column = $columns_array[$i];
			if( $column == ( $main_table.'SEQ' ) )
			{
				continue;
			}
			$columns_name = $form_ini[ $column ]['item_name'];
			$list_html .= '<th><a class ="head">'.$columns_name.'</a></th>';
		}

		$list_html .='<th><a class ="head">編集</a></th></tr></thead><tbody>';
		for($counter = 0; $counter < 15; $counter++ )
		{
			if(($counter%2) == 1)
			{
				$class = 'stripe_none';
			}
			else
			{
				$class = 'stripe';
			}
			$list_html .= '<tr class="'.$class.'">';

			if($isNo == 1)
			{
				$list_html .='<td class = "'.$class.' center" style="width:10px;"><a class="body">'.
								($counter+1)."</a></td>";
			}

			$insert_str = '';

			// 登録カラム数分ループ
			for($i = 0 ; $i < count($columns_array) ; $i++)
			{
				//カラム
				$column = $columns_array[$i];
				$readonly = $readonly_array[$i];

				//型を取得
				$form_type = $form_ini[$column]['form1_type'];

				//NotNull指定
				if($form_ini[$column]['isnotnull'] == 1)
				{
	//				$notnull_column_str .= $colum.",";
	//				$notnull_type_str .= $form_type.",";
					$isnotnull = 1;
				}
				else
				{
					$isnotnull = 0;
				}

				//デフォルトになるコントロール名
				$element_name = "form_".$column."_0_".$counter;
				// 設定値
				$td_width = ' style="width:'.$form_ini[$column]['form1_size'].'px;"';
				//送信値
				$form_value = "";
				if(isset($post[$element_name]))
				{
					$form_value = $post[$element_name];
				}
				else
				{
					$column_name = $form_ini[$column]['column'];
					if(isset($post[$column_name]))
					{
						$form_value = $post[$column_name];		
					}
				}

				//チェック用文字列
				if($form_type == 6)
				{
					// エラーチェック無し
				}
				else if($form_type > 9)
				{
					//チェックとりあえずなし
				}
				else
				{
	//				$check_column_str .= $form_name."~".$form_length."~".$form_format."~".$isnotnull."~".$form_isJust.",";
				}
				$form_name = 'UPSERT.';

				//SEQは隠し項目として扱う
				if( $column == ( $main_table.'SEQ' ) )
				{
					$insert_str .= '<input type ="hidden" name = '.$element_name.' id = '.$element_name.' value = "'.$counter.'" >';
					continue;
				}
				//elementは別関数で作成
				$input_result = $this->getFormHtmlElement($form_ini, $column, $form_value, $isnotnull, $form_name, $element_name, $readonly);
				$insert_str .= '<td class = "two"' .$td_width. '>' . $input_result[0] . '</td>';
				if( $main_form_type == 1 )
				{
					$refrer_value = $form_ini[$column]['ref_value_copy'];
					$script_str .= "updateAutocompleteHIMValue('#$element_name', '$refrer_value', '_0_$counter');";
				}
				else
				{
					$script_str .= $input_result[1];
				}

			}	// 登録カラム数分ループ

			$insert_str .= '<td><button type="button" title="行をコピー" onclick="copyRow('.$counter.');"><i class="far fa-copy faa-tada animated-hover"></i></button>';
			$insert_str .= '<button type="button" title="コピーデータを貼り付け"  onclick="pasteRow('.$counter.');"><i class="fas fa-paint-brush faa-tada animated-hover"></i></button>';
			$insert_str .= '<button type="button" title="行を挿入"  onclick="insertRow('.$counter.');"><i class="fas fa-plus-square faa-tada animated-hover"></i></button>';
			$insert_str .= '<button type="button" title="行を削除"  onclick="removeRow('.$counter.');"><i class="far fa-minus-square faa-tada animated-hover"></i></button></td>';
			$list_html .= $insert_str."</tr>\n";
		}
		$list_html .="</tbody></table>";

		//計算を行うための変数設定をスクリプトに付け足す
		if($main_form_type == 1 )
		{
                    //hm_hinmoku.jsの定義にあわせる
                    $var_name = array('TANKA_NAME','SURYO_NAME','TANNI_NAME','ZEIRISTU_NAME','KINGAKU_NAME','HINMEI_NAME','KINGAKUKEI_NAME','ZEI_NAME', 'TOTAL_NAME', 'FRACTION', 'ZEIFRACTION');
                    $calc_setting =$form_ini[$filename_M]['calc_setting_column'];
                    $calc_setting_array = explode( ',', $calc_setting );
                    if( count($calc_setting_array) > 6 )
                    {
                        for($i = 0; $i < count($var_name); $i++)
			{
                            $script_str .= "$var_name[$i]='form_$calc_setting_array[$i]';";
			}
                    }
		}

		$result[0] = $list_html;
		$result[1] = $script_str;

		return ($result);
	}

	/************************************************************************************************************
	function makeErrorNotExist
		エラー用HTML文字列出力
	引数 なし
	戻り値	指定コード非存在時のエラー画面
	************************************************************************************************************/
	function makeErrorNotExist()
	{
		$this->prJudge = false;
		$html = '<br><br><div = class="center">';
		$html .='<a class = "title">';
		$html .= $this->prTitle;
		$html .='不可</a>';
		$html .='</div><br><br>';
		$html .='<div class ="center"><a class ="error">他の端末ですでにデータが削除されているため,編集できません。</a></div>';
		$html .='<form action="main.php" method="post" >';
		$html .='<div class = "center">';
		$html .='<input type="submit" name = "cancel" value = "一覧に戻る" class = "free">';
		$html .='</div></form>';

		return $html;
	}
	
	/**
	 * 次の画面へ送るパラメーター作成
	 * 
	 * @param $column
	 * 
	 * return $getparam
	 */
	function makeGetAdditionalListParam($column)
	{
		$getparam = '';
		return $getparam;
	}
	
	/**
	 * 次の画面へ送るhiddenの作成
	 * 
	 * @param $list_id 画面ID
	 * @param $step 画面判定
	 * 
	 * return $hiddenparam 
	 */
	function makeHiddenParam( $list_id, $step )
	{
		if($list_id != "")
		{
			$hiddenparam = '<input type="hidden" name = "edit_list_id" value = "'.$list_id.'" class="free">';
		}
		
		if($step != 0)
		{
			if($list_id != "")
			{
				$hiddenparam .= '<input type="hidden" name = "step" value = '.$step.' class="free">';
			}	
			else
			{
				$hiddenparam = '<input type="hidden" name = "step" value = '.$step.' class="free">';
			}	
		}	
		
		//$hiddenparam = '<input type="hidden" name = "filename" value = '.$filename.' class="free">';
		return $hiddenparam;
		
		
	}
	
	
	/***************************************************************************
	function makebutton($fileName,$buttonPosition)
          ヘッダ部・フッタ部に表示するボタンを作成する処理

	引数1	$fileName			表示ファイル名
	引数2	$buttonPosition		表示位置

	戻り値	$con	mysql接続済みobject
	***************************************************************************/
	function makeButtonV2( $filename, $button_pos, $enable_step, $add_param = '' )
	{
		// ボタン設定読込み
		global $button_ini;
		if( $button_ini === null)
		{
			// ボタン設定読込み
			$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
		}

		//------------------------//
		//          変数          //
		//------------------------//
		$button_html = '';
		//設定値
		$button_num = $button_ini[$filename]['set_button_'.$button_pos];
		if( $button_num === '' )
		{
			return $button_html;
		}
		$button_name = $button_ini[$filename]['set_button_'.$button_pos.'_name'];
		$button_enable = $button_ini[$filename]['set_button_'.$button_pos.'_enable'];
		//設定値を,で分割
		$button_num_array = explode(',',$button_num);
		$button_name_array = explode(',',$button_name);
		$button_enable_array = explode(',',$button_enable);
		$button_count = count($button_num_array);

		//------------------------//
		//     ボタン作成処理     //
		//------------------------//
		for ($i = 0; $i < $button_count; $i++ )		{
			//IDを取り、今のページと同じなら追加しない
			$button_id = $button_num_array[$i];
			$param_string = $add_param;
			//有効？
			$enable = intval($button_enable_array[$i]);
			if( $enable == 0 || $enable == $enable_step )	{
				//キー
				$key_column = $button_ini[$button_id]['key_column'];
				if($key_column !== '')	{
					foreach( $this->prContainer->pbInputContent as $key => $value )		{
						if( strpos( $key, $key_column ) !== false )		{
							$param_string .= '&'.$key_column.'='.$value;
							break;
						}
					}
				}
				$button_html .='　<a href="main.php?'.$button_id.'_button=&'.$param_string.'" class="btn-radius">'.$button_name_array[$i].'</a>';
			}
		}
		return ($button_html);

	}
        /************************************************************************************************************
        function existCheck($post,$tablenum,$type)

        引数1		$post							登録フォーム入力値
        引数2		$main_codeValue					編集ID
        引数3		$tablenum						テーブル番号
        引数4		$type							1:insert 2:edit 3:delete

        戻り値		$errorinfo						既登録確認結果
        ************************************************************************************************************/
        //function existCheck($post,$tablenum,$type){
        function existCheck($post,$main_codeValue,$tablenum,$type,$con)
        {
	
            //------------------------//
            //        初期設定        //
            //------------------------//
            $form_ini = parse_ini_file('./ini/form.ini', true);
//            require_once ("f_Form.php");
//            require_once ("f_DB.php");																							// DB関数呼び出し準備
//            require_once ("f_SQL.php");																							// SQL関数呼び出し準備

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $uniquecolumn = $form_ini[$filename]['uniquecheck'];
            $uniquecolumn_array = explode(',',$uniquecolumn);
            $master_tablenum = $form_ini[$tablenum]['seen_table_num'];
            $master_tablenum_array = explode(',',$master_tablenum);
            //------------------------//
            //          変数          //
            //------------------------//
            $errorinfo = array();
            $errorinfo[0] = "";
            $sql = "";
            $judge = false;
            $codeValue = "";
            $code = "";
            $table_title = "";
            $counter = 1;
            $syorimei = "";

            //------------------------//
            //          処理          //
            //------------------------//
            switch($type)
            {
            case 1 :
                    $syorimei = "登録";
                    break;
            case 2 :
                    $syorimei = "編集";
                    break;
            case 3 :
                    $syorimei = "削除";
                    break;
            default :
                    break;
            }																								// db接続関数実行
            if($type == 2)
            {
                    $table_title = $form_ini[$tablenum]['table_title'];
                    $code = getCode($filename);
                    //$post[$code] = $main_codeValue;//編集するID
                    //$codeValue = $post[$code];
                    $codeValue = $main_codeValue;//編集するID
                    $sql = idSelectSQL($codeValue,$tablenum,$code);
                    $result = $con->query($sql) or ($judge = true);																	// クエリ発行
                    if($judge)
                    {
                            error_log($con->error,0);
                            $judge = false;
                    }
                    if($result->num_rows == 0 )
                    {
                            $errorinfo[$counter] = "<div class = 'center'><a class = 'error'>".
                                                                            $table_title."情報が削除されているため".
                                                                            $syorimei."できません。</a></div><br>";
                            $counter++;
                    }
                    else
                    {
                            $errorinfo[$counter] = "";
                            $counter++;
                    }
            }
            for( $j = 0 ; $j < count($uniquecolumn_array) ; $j++)
            {
                    if($uniquecolumn_array[$j] == "")
                    {
                            break;
                    }
                    $sql = uniqeSelectSQL($post,$tablenum,$uniquecolumn_array[$j]);
                    if(isset($post['edit_list_id']))
                    {
                        $unique = explode($tablenum,$uniquecolumn_array[$j])[1];
                        $uniqueSql = "SELECT ".explode($tablenum,$uniquecolumn_array[$j])[1]." FROM ".$table_title." WHERE ".$code." = ".$post['edit_list_id'].";";
                        $uniqueResult = $con->query($uniqueSql) or ($judge = true);
                        foreach($uniqueResult as $row)
                        {
                            $colum = $row[$unique];
                        }
                        
                        if($filename === "PJNUMMASTER_1")
                        {
                            if($post['form_pjnPROJECTNUM_0'] === $colum)
                            {
                                break;
                            }
                        }
                        else if($filename === "EDABANMASTER_3")
                        {
                            if($post['form_edaEDABAN_0'] === $colum)
                            {
                                break;
                            }
                        }
                        else if($filename === "KOUTEIMASTER_1")
                        {
                            if($post['form_kouKOUTEIID_0'] === $colum)
                            {
                                break;
                            }
                        }
                        else if($filename === "SYAINMASTER_1")
                        {
                            if($post['form_syaSTAFFID_0'] === $colum)
                            {
                                break;
                            }
                        }
                    }
                    if(explode('_',$this->prContainer->pbFileName)[0] === 'USERMASTER' || explode('_',$this->prContainer->pbFileName)[0] === 'KOKYAKUMASTER')
                    {
                        $sql = rtrim($sql,';');
                        $sql .=" AND DELETEFLAG = 0;";
                    }
                    
                    if($sql != '')
                    {
                            $result = $con->query($sql) or ($judge = true);																// クエリ発行
                            if($judge)
                            {
                                    error_log($con->error,0);
                                    $judge = false;
                            }
                            if($result->num_rows != 0 )
                            {
                                    $errorinfo[0] .= $uniquecolumn_array[$j].",";
                            }
                    }
            }
            for($k = 0 ; $k < count($master_tablenum_array) ; $k++ )
            {
                    if($master_tablenum == '')
                    {
                            break;
                    }
                    $table_title = $form_ini[$master_tablenum_array[$k]]['table_title'];
                    $code = $master_tablenum_array[$k].'ID';
                    $codeValue = $post[$code];
                    $sql = idSelectSQL($codeValue,$master_tablenum_array[$k],$code);
                    $result = $con->query($sql) or ($judge = true);																	// クエリ発行
                    if($judge)
                    {
                            error_log($con->error,0);
                            $judge = false;
                    }
                    if($result->num_rows == 0 )
                    {
                            $errorinfo[$counter] = "<div class = 'center'><a class = 'error'>".
                                                                            $table_title."情報が削除されているため".
                                                                            $syorimei."できません。</a></div><br>";
                            $counter++;
                    }
            }
            return ($errorinfo);
        }
    
        
    /**
     * カレンダー作成
     */
    function makeCalendar()
    {
        
        // タイムゾーンを設定
        date_default_timezone_set('Asia/Tokyo');

        // 前月・次月リンクが押された場合は、GETパラメーターから年月を取得
        if (isset($_GET['ym'])) {
//            $ym = $_GET['ym'];
            $ym = rtrim($_GET['ym'], ';');
        } else {
            // 今月の年月を表示
            $ym = date('Y-m');
        }

        // タイムスタンプを作成し、フォーマットをチェックする
        $timestamp = strtotime($ym . '-01');
        if ($timestamp === false) {
            $ym = date('Y-m');
            $timestamp = strtotime($ym . '-01');
        }

        // 今日の日付 フォーマット　例）2018-07-3
        $today = date('Y-m-j');

        // カレンダーのタイトルを作成　例）2017年7月
        $this->month = date('Y年n月', $timestamp);

        // 前月・次月の年月を取得
        // 方法１：mktimeを使う mktime(hour,minute,second,month,day,year)
        $this->prev = date('Y-m', mktime(0, 0, 0, date('m', $timestamp) - 1, 1, date('Y', $timestamp)));
        $this->next = date('Y-m', mktime(0, 0, 0, date('m', $timestamp) + 1, 1, date('Y', $timestamp)));

        // 方法２：strtotimeを使う
        // $prev = date('Y-m', strtotime('-1 month', $timestamp));
        // $next = date('Y-m', strtotime('+1 month', $timestamp));
        // 該当月の日数を取得
        $day_count = date('t', $timestamp);

        // １日が何曜日か　0:日 1:月 2:火 ... 6:土
        // 方法１：mktimeを使う
        $youbi = date('w', mktime(0, 0, 0, date('m', $timestamp), 1, date('Y', $timestamp)));
        // 方法２
        // $youbi = date('w', $timestamp);
        // カレンダー作成の準備
        $weeks = [];
        $week = '';

        // 第１週目：空のセルを追加
        // 例）１日が水曜日だった場合、日曜日から火曜日の３つ分の空セルを追加する
        $week .= str_repeat('<td></td>', $youbi);
        // データ取得
        list($workDate,$worktimeTotal) = $this->getProjectData($ym);
        $this->worktimeTotal = $worktimeTotal;
        
        for ($day = 1; $day <= $day_count; $day++, $youbi++) {

            // yyyy-mm-dd
            $date = $ym . '-' . $day;
            // 0埋め行う
            $dayCcount = str_pad($day, 2, 0, STR_PAD_LEFT);
            $time = $ym . '-' . $dayCcount;
            
            if ($today == $date) {
                // 今日の日付の場合は、class="today"をつける
                $week .= '<td id="popup" class="today"><span class="dayof">' . $day . '</span>';
            } else {
                $week .= '<td id="popup"><span class="dayof">' . $day . '</span>';
            }
//            $week .= '<span class="worktime">7.75</span><span class="overtime"></span></td>';
            if(!isset($workDate[$time])){
                $week .= '<span class="worktime"></span><span class="overtime"></span></td>';
            }else{
                $week .= $this->createWorkTd($workDate[$time]);
            }
            

            // 週終わり、または、月終わりの場合
            if ($youbi % 7 == 6 || $day == $day_count) {

                if ($day == $day_count) {
                    // 月の最終日の場合、空セルを追加
                    // 例）最終日が木曜日の場合、金・土曜日の空セルを追加
                    $week .= str_repeat('<td></td>', 6 - ($youbi % 7));
                }

                // weeks配列にtrと$weekを追加する
                $weeks[] = '<tr>' . $week . '</tr>';
                
                // weekをリセット
                $week = '';
            }
        }
        return $weeks;
    }
    
    /**
     * ポップアップ作成
     */
    function createPopUp(){
     
        $html = "";
        $html .="<div class='modal js-modal'>";
        $html .="<div class='modal__bg js-modal-close'></div>";
        $html .="<div class='modal__content'>";
        $html .=$this->createPopUpContent();
        $html .="<div class='buttonArea'>";
        $html .='<input type="button" name = "insert" value = "登録" class="free">';
        $html .='<input type="button" value = "戻る" class="free js-modal-close">';
        $html .="</div><!--buttonArea-->";
        $html .="</div><!--modal__inner-->";
        $html .="</div><!--modal-->";
        
        return $html;
    }
    
     /**
     * ポップアップ入力項目作成
     */
    function createPopUpContent(){
        
        $html ="";
        //$html .="<p>サンプル</p>";
        return $html;
        
    }

    /*
     * function pulldown_setV3($name,$post,$ReadOnly,$formName,$isnotnull)
     * 
     * 引数	$post
     * 戻り値	なし
     */
    function pulldown_setV3($name,$post,$ReadOnly,$formName,$isnotnull){
            //------------------------//
            //        初期設定        //
            //------------------------//
            $item_ini = parse_ini_file('./ini/item.ini', true);

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $year = date_create('NOW');
            $year = date_format($year, "Y");
            $month = date_create('NOW');
            $month = date_format($month, "n");
            $startyear = $item_ini['period']['startyear'];
            $startmonth = $item_ini['period']['startmonth'];
            $period = 0;
            //------------------------//
            //          変数          //
            //------------------------//
            $pulldown = "";
            $num = 0;
            $text = "";
            $value ="";
            $formname ="";
            $select = "";
            $isSelect = false;
            $isdisable = "";
            $disable = "";

            //------------------------//
            //          処理          //
            //------------------------//
            $period = $year - $startyear;
            if($startmonth <= $month)
            {
                    $period = $period + 1;
            }
            if($filename == 'nenzi_5')
            {
                    $period = $period - 1;
            }
            if($ReadOnly == '')
            {
                    $isdisable = "";
            }
            else
            {
                    $isdisable = 'disabled';
            }
            if($name !="")
            {
                    $formname = $name;
            }

            $pulldown.='<select id="'.$formname.'"  class ="'.$ReadOnly.'" name="'.$formname.'"
                                             onMouseOver ="change(this.id,\''.$ReadOnly.'\',\''.$formName.'\');" 
                                            onChange = "notnullcheck(this.id,'.$isnotnull.',\''.$formName.'\');">';
            for($i = 1 ;$i <= $period ; $i++)
            {
                    $text = $i."期";
                    $value = $i;
                    if(isset($post[$formname]))
                    {
                            if($value == $post[$formname])
                            {
                                    $select = ' selected ';
                                    $isSelect=true;
                                    $disable = "";
                            }
                    }
                    $pulldown.='<option value ="'.$value.'" '.$select.' >'.$text.'</option>';
                    $select = "";
            }
            if($isSelect)
            {
                    $pulldown.='<option value ="" > </option>';
            }
            else
            {
                    $pulldown.='<option value ="" selected > </option>';
            }
            return $pulldown;
    }
}
