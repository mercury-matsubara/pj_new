<?php

//require_once("classesPageContainer.php");
//require_once("classesBase.php");




/**
 * リストぺージ用Pageクラス
 */
class ListPage extends BasePage
{
	
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親の処理
		parent::executePreHtmlFunc();

		//PageID取得を_で分割
		$filename_array = explode('_',$this->prContainer->pbFileName);
		$filename_insert = $filename_array[0]."_1";     //insert時ファイル名
		if(isset($this->prContainer->pbInputContent['list']['limitstart']) == false)
		{
			$this->prContainer->pbInputContent['list']['limitstart'] = 0;
		}
		$this->prContainer->pbInputContent['list']['limit']	= ' LIMIT '.$this->prContainer->pbInputContent['list']['limitstart'].','
																		.$this->prContainer->pbPageSetting['limit'];	
			
		//変数をセット
		$this->prTitle = $this->prContainer->pbPageSetting['title'];					//メンバ変数タイトル
		$this->prMainTable = $this->prContainer->pbPageSetting['use_maintable_num'];					//main_table
		$this->prFileNameInsert = $filename_insert;			//新規作成
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
		$html = parent::makeScriptPart();
		
		$html .='<script language="JavaScript"><!--
				//history.forward();
				var isCancel = false;
				$(window).resize(function()
				{
				});
				$(function()
				{
					$(".button").corner();
					$(".free").corner();
					makeDatepicker();
				});
				function show_hide_row(row){
					$("[id="+row+"]").toggle(300);
				}
				--></script>';
			return $html;
			
	}
	
	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		if(!isset($_SESSION['list']))
		{
			$_SESSION['list'] = array();
		}
		//検索フォーム作成,日付フォーム作成
                if(isset($_SESSION['search']['flg']))
                {
                    if($_SESSION['search']['flg'] === 1)
                    {
                        $this->prContainer->pbInputContent = $_SESSION['search']['input'];
                        $_SESSION['search']['flg'] = 0;
                    }
                    else
                    {
                        $this->setSearchSession($this->prContainer->pbInputContent);
                    }
                }
                else
                {
                    $this->setSearchSession($this->prContainer->pbInputContent);
                }
		$formStrArray = $this->makeformSearch_setV2( $this->prContainer->pbInputContent, 'form' );
		$form = $formStrArray[0];			//0はフォーム用HTML
		$this->prInitScript = $formStrArray[1];	//1は構築用スクリプト
		
		//検索SQL
		$sql = array();
		$sql = joinSelectSQL($this->prContainer->pbInputContent, $this->prMainTable, $this->prContainer->pbFileName, $this->prContainer->pbFormIni);
		$sql = SQLsetOrderby($this->prContainer->pbInputContent, $this->prContainer->pbFileName, $sql);
		$limit = $this->prContainer->pbInputContent['list']['limit'];				// limit
		$limit_start = $this->prContainer->pbInputContent['list']['limitstart'];	// limit開始位置
		
		//リスト表示HTML作成
		$pagemove = intval( $this->prContainer->pbPageSetting['isPageMove'] );
		$list =  $this->makeListV2($sql, $_SESSION['list'], $limit, $limit_start, $pagemove);
		
		$checkList = $_SESSION['check_column'];
		
		//出力HTML作成
		$html ='<div class = "pad" >';
		$html .='<form name ="form" action="main.php" method="get"onsubmit = "return check(\''.$checkList.'\');">';
		$html .='<table><tr><td><fieldset><legend>検索条件</legend>';
		$html .= $form;								//検索項目表示
                
                //--2019/06/06追加　filename取得　
                $filename = $this->prContainer->pbFileName;
                $html .='<input type="hidden" id="clear_'.$filename.'" value = "'.$this->prContainer->pbPageSetting['sech_form_num'].'" >';
		$html .='</fieldset></td><td valign="bottom"><input type="submit" name="serch_'.$filename.'" value = "表示" class="free" ></td>';
                $html .='</fieldset></td><td valign="bottom"><input type="button" value = "クリア" class="free" onclick="clearSearch(\''.$filename.'\')"></td></tr></table>';
		$html .= $list;
		$html .= '</form>';
		
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
		$html = '<div class = "left" style = "HEIGHT : 30px"><form action="main.php" method="get">';
		//新規作成ボタン作成
		global $button_ini;
		if( $button_ini === null)
		{
			// ボタン設定読込み
			$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
		}
		//新規作成ページに指定されているIDがbutton.iniにあるか？
		if( array_key_exists( $this->prFileNameInsert, $button_ini ) === true )
		{
			$html .= '<input type ="submit" value = "新規作成" class = "free" name = "'.$this->prFileNameInsert.'_button">';
		}
		//CSVボタン
		$is_csv = $this->prContainer->pbPageSetting['isCSV'];
		if( $is_csv === '1' )
		{
			$html .='　<a href="csv_out.php?id='.$this->prContainer->pbFileName.'&'.$_SERVER['QUERY_STRING'].'" target="_blank" class="btn-radius">CSV出力</a>';
		}
		$html .= '</form></div>';
			
		return $html;
	}
	
	/**
	 * 関数名: makCsv
	 *   CSV出力用関数
	 * 
	 * @retrun CSV文字列
	 */
	function makCsv()
	{
		//SQL文をリストページと同じ手順で構築
		$sql = joinSelectSQL($this->prContainer->pbInputContent, $this->prMainTable, $this->prContainer->pbFileName, $this->prContainer->pbFormIni);
		$sql = SQLsetOrderby($this->prContainer->pbInputContent, $this->prContainer->pbFileName, $sql);
		
		// 項目変数
		$columns_array = explode(',',$this->prContainer->pbPageSetting['page_columns']);
		$labels_array = explode(',',$this->prContainer->pbPageSetting['column_labels']);

		//csv
		$csv_str = '';

		//------------------------//
		//          処理          //
		//------------------------//
		// db接続関数実行
		$con = dbconect();

		// クエリ発行(実データ取得)
		$judge = false;
		$result = $con->query($sql[0]) or ($judge = true);
		if($judge)
		{
			error_log($con->error,0);
			$judge = false;
		}
	
		//項目名（ここがヘッダの主要構成箇所）
		$column_count = count($labels_array);
		for($i = 0 ; $i < $column_count ; $i++)		{
			$label = str_replace('※', '', $labels_array[$i]);
			$label = str_replace('＊', '', $label);
			if($i !== 0){
				$csv_str .= ',';	//カンマを付け足す
			}
			$csv_str .= $label;
		}
		$csv_str .= "\r\n";	//改行

		//ここからデータ部分
		while($result_row = $result->fetch_array(MYSQLI_ASSOC))		{
			for($i = 0 ; $i < $column_count ; $i++)		{
				if($i !== 0){
					$csv_str .= ',';	//カンマを付け足す
				}
				//列名
				$field_name = $this->prContainer->pbParamSetting[$columns_array[$i]]['column'];
				//値
				$csv_str .= $result_row[$field_name];
			}
			$csv_str .= "\r\n";	//改行
		}
		//EXCELで開きやすいようにSJISに変換
		$result_str = mb_convert_encoding($csv_str, "SJIS");

		return $result_str;
	}
}

/** 
 *インサート
 *
 */
class InsertPage extends BasePage
{
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親の処理
		parent::executePreHtmlFunc();
			
		$maxover = -1;
		if(isset($_SESSION['max_over']))
		{
			$maxover = $_SESSION['max_over'];
		}
		
		//メンバ変数設定
		$this->prTitle = $this->prContainer->pbPageSetting['title'];
		$this->prMainTable = $this->prContainer->pbPageSetting['use_maintable_num'];
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
		$html = parent::makeScriptPart();
		
		$html .='<script language="JavaScript"><!--
			//history.forward();
			var isCancel = false;
			$(function()
			{
                                    $("input"). keydown(function(e) {
                                        if ((e.which && e.which === 13) || (e.keyCode && e.keyCode === 13)) {
                                            return false;
                                        } else {
                                            return true;
                                        }
                                    });
				$("input").blur(function()
				{
					var idx = this.name.lastIndexOf( "_0_" );
					if(idx !== -1)
					{
						var poststr = this.name.substr(idx);
						calculateKingaku( poststr );
                                                calculateTotal();
					}
				});
				$("select").blur(function()
				{
					var idx = this.name.lastIndexOf( "_0_" );
					if(idx !== -1)
					{
						var poststr = this.name.substr(idx);
						calculateKingaku( poststr );
                                                calculateTotal();
					}
				});
                                $(".cp_ipselect").change(function()
				{
                                        //　自社マスタ時は処理をしない
					var idx = this.name.indexOf( "jsy" );
					if(idx === -1)
					{
                                            calculateReturn()
                                            calculateTotal();
					}
				});
                                $("#print_btn").on("click", function() {
                                    //印刷ボタン押下時
                                    calculateTotal();
                                    saveStorage();
                                    submitaction();
                                });
                                importStorage();
				makeDatepicker();
			});
			--></script>';
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

		$html = '<div class = "center"><a class = "title_edit">';
		$html .= $this->prTitle;  //タイトル表示
		$html .= '</a>';

		//遷移ボタン
		$linkValue = '';
		if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
		}
		else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
		}	
		
		$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'top', STEP_INSERT, $linkValue);
		
		$html .= '</div>';
		
		if($this->prContainer->pbPageSetting['message'] != "")
		{	
			$html .= '<div class = "message">';
			$html .= '<p>';
			$html .= $this->prContainer->pbPageSetting['message'];
			$html .= '</p>';
			$html .= '</div>';
		}	
		return $html;
	}

	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$_SESSION['pre_post'] = null;
		$errorinfo ='';
			
                if(isset($_SESSION['error']))
                {
                    $errorinfo = $_SESSION['error'];
                    $_SESSION['error'] = "";
                }
                
		//入力項目作成
		$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $errorinfo, '', "insert", $this->prContainer);
		$form = $form_array[0];
		$this->prInitScript =  $form_array[1];
		
		//----明細入力作成----//
		$header_array = $this->makeList_itemV2('', $this->prContainer->pbSecondInputContent);
		if(isset($header_array))
		{
			$header = $header_array[0];
			$this->prInitScript .=  $header_array[1];
		}
		
		//--tab作成--//
		$tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
		$tab = $tabarray[0];
		$this->prInitScript .= $tabarray[1];
		
		$checkList = $_SESSION['check_column'];
		$notnullcolumns = $_SESSION['notnullcolumns'];
		$notnulltype = $_SESSION['notnulltype'];
		
		//2019/03/25パラメーター追加
		//hidden作成
		$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
		
		$send = '<form name ="insert" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" autocomplete="off" id="send" enctype="multipart/form-data" 
				onsubmit = "return check(\''.$checkList.
				'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
		
		//出力HTML
		$html = '<br>';
		$html .= $send;
		$html .= '<div class = "edit_table">';
		$html .= $form;
		$html .= $hidden;
		$html .= $header;
		$html .= '</div>';
		$html .= $tab;
		
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
		if( isPermission($this->prContainer->pbFileName) )
		{
			//ダイアログ
			$html .= '<div id="dialog" title="入力確認" style="display:none;">
						<p>この内容でよろしいでしょうか？</p>
						</div>';
			// 読取指定
			if($this->prContainer->pbPageSetting['form_type'] !== '2')
			{
				//通常は更新ボタンを表示
				$html .= '<div class = "pad">';
				$html .= '<input type="button" name = "insert" value = "登録" class="free" onClick = "Regist()">';
//                                $html .= '<input type="button" name = "insert" value = "登録" class="free" onClick = submit()>';
                                //<input type="reset" name = "cancel" value = "クリア" class="free" onClick ="isCancel = true;">';
				if($this->prContainer->pbFileName == 'MITSUMORIINFO_1' || $this->prContainer->pbFileName == 'SEIKYUINFO_1')
				{	
					$html .= '<input type="submit" id="print_btn" name = "print" value="印刷" class="free"  >';
				}
				$html .='</div>';
			}

			//遷移ボタン
			$linkValue = '';
			if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
			}
			else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
			}
			$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'bottom', STEP_INSERT, $linkValue);
		}
		$html .='</div>';
		$html .= '</form>';
		return $html;
	}
	
}

/**
 * 編集 インサートページを継承
 * 
 */
class EditPage extends InsertPage
{
	/**
	 * 関数名: makeBoxContentTop
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentTop()
	{

		$html = '<div class = "center"><a class = "title_edit">';
		$html .= $this->prTitle;  //タイトル表示
		$html .= '</a>';

		//遷移ボタン
		$linkValue = '';
		if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
		}
		else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
		{
			$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
		}
		
		$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'top', STEP_EDIT, $linkValue);
		
		$html .= '</div>';
		if($this->prContainer->pbPageSetting['message'] != "")
		{	
			$html .= '<div class = "message">';
			$html .= '<p>';
			$html .= $this->prContainer->pbPageSetting['message'];
			$html .= '</p>';
			$html .= '</div>';
		}
		return $html;
	}
	
	/**
	* @param string $prInitScript jQuery日付
	* @param $isexist 編集確認
	* @return string $html 編集項目作成
	*/
	function makeBoxContentMain()
	{
		//$_SESSION['post'] = $_SESSION['pre_post'];
		//$_SESSION['pre_post'] = null;
		$isMaster = false;
		$isReadOnly = false;
		
		$isexist = true;
		//$checkResultarray = existID($_SESSION['list']['id']);
		$checkResultarray = existID($this->prContainer->pbListId);
		if(count($checkResultarray) == 0)
		{
			$isexist = false;
		}
		
		if($isexist)
		{
			$errorinfo ='';
			
                        if(isset($_SESSION['error']))
                        {
                            $errorinfo = $_SESSION['error'];
                            $_SESSION['error'] = "";
                        }
			
			if(isset($_SESSION['data']))
			{
				$data = $_SESSION['data'];
			}
			else
			{
				$data = "";
			}
			$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $errorinfo, $isReadOnly, "edit",$this->prContainer);
			$form = $form_array[0];
			$makeDatepicker =  $form_array[1];
			
			//--↓明細作成--//
			$header_array = $this->makeList_itemV2('', $this->prContainer->pbInputContent);
			if(isset($header_array))
			{
				$header = $header_array[0];
				$makeDatepicker .=  $header_array[1];
			}
			//--↑明細作成--//
			
			$checkList = $_SESSION['check_column'];
			$notnullcolumns = $_SESSION['notnullcolumns'];
			$notnulltype = $_SESSION['notnulltype'];
			
			//2019/03/25パラメーター追加
			//hidden作成
			//$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
			$hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
			$send = '<form name ="edit" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" autocomplete="off" id="send" enctype="multipart/form-data" 
					onsubmit = "return check(\''.$checkList.
					'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
			$this->prInitScript = $makeDatepicker;//メンバ変数に保存
			$html = '<br>';
			$html .= '<div style="clear:both;"></div>';
			$html .= $send;
			$html .= $hidden;
			$html .= '<div class = "edit_table">';
			$html .= $form;
			$html .= $header;
			$html .= '</div>';
			
			//--↓tab追加--//
			$tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
			$html .= $tabarray[0];
			$this->prInitScript .= $tabarray[1];
			//--↑tab追加--//
			
		}
		else
		{
			//エラー時共通出力
			$html = $this->makeErrorNotExist();
		}
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
		$is_permission = isPermission($this->prContainer->pbFileName);
		//ユーザーマスタで権限がない
		if( $is_permission === false && $this->prContainer->pbFileName === 'USERMASTER_1' ){
			//自分のIDのみ許可する
			$is_permission =( $this->prContainer->pbListId == $_SESSION['userid'] );
		}
		if( $is_permission ){
                        //ダイアログ
			$html .= '<div id="dialog" title="入力確認" style="display:none;">
						<p>この内容でよろしいでしょうか？</p>
						</div>';
			// 読取指定
			if($this->prContainer->pbPageSetting['form_type'] !== '2')
			{
                                if($this->prContainer->pbFileName === "JISYAMASTER_3")
                                {
                                    $html .= '<div class = "pad">
					<input type="submit" name = "kousinn" value = "更新" class="free" onclick="document.getElementByName(\'edit\').action=\'main.php?'.$this->prContainer->pbFileName.'_button=\'" >';
                                }
                                else
                                {
                                    $html .= '<div class = "pad">
                                            <input type="button" name = "kousinn" value = "更新" class="free" onClick="Regist()" >';
                                    $html .='<input type="submit" name = "delete" value = "削除" class="free" onClick = "ischeckpass = false;">';
                                }
                                
				if($this->prContainer->pbFileName == 'MITSUMORIINFO_1' || $this->prContainer->pbFileName == 'SEIKYUINFO_1')
				{	
//					$html .= '<input type="submit" id="print" name = "print" value="印刷" class="free" onclick="document.getElementByName(\'edit\').action=\'main.php?MITSUMORIPRINT_5_button=\'" >';
                                        $html .= '<input type="button" id="print_btn" name = "print" value="印刷" class="free" >';
				}

				$html .='</div>';
			}

			//遷移ボタン
			$linkValue = '';
			if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
			}
			else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
			{
				$linkValue = 'edit_list_id='.$this->prContainer->pbListId;
			}
			$html .= $this->makeButtonV2($this->prContainer->pbFileName, 'bottom', STEP_EDIT, $linkValue);
		}
		$html .= '</form>';
		return $html;
	}
}

/**
 * 削除
 * 
 */
class DeletePage extends EditPage
{
	
	/**
	 * 関数名: exequtePreHtmlFunc
	 *   ページ用のHTMLを出力する前の処理
	 */
	public function executePreHtmlFunc()
	{
		//親呼び出し
		parent::executePreHtmlFunc();
		
		//$_SESSION['post'] = $_SESSION['pre_post'];
		//$filename = $_SESSION['filename'];
		$main_table = $this->prContainer->pbPageSetting['use_maintable_num'];
		$title1 = $this->prContainer->pbPageSetting['title'];
		$title2 = '';
		$isMaster = false;
		$isReadOnly = false;
		switch ($this->prContainer->pbPageSetting['delete_type'])
		{
			case 0:
				$title1 = '削除確認';
				$isReadOnly = true;
				break;
			case 1:
				$title1 = '削除確認';
				$isMaster = true;
				$isReadOnly = true;
				break;
			default:
				$title2 = '';
		}
		$this->prTitle = $title1.$title2;
		$this->prMainTable = $main_table;
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
		$isReadOnly = true;
		$_SESSION['edit']['true'] = true;
		
		$isexist = true;
		//$checkResultarray = existID($_SESSION['list']['id']);
		$checkResultarray = existID($this->prContainer->pbListId);
		if(count($checkResultarray) == 0)
		{
			$isexist = false;
		}
		
		if($isexist)
		{
			$this->prJudge = true;
			$errorinfo ='';
			
                        if(isset($_SESSION['error']))
                        {
                            $errorinfo = $_SESSION['error'];
                            $_SESSION['error'] = "";
                        }
			//make_post($_SESSION['list']['id']);
			if(isset($_SESSION['data']))
			{
				$data = $_SESSION['data'];
			}
			else
			{
				$data = "";
			}
			
			$checkList = $_SESSION['check_column'];
			$notnullcolumns = $_SESSION['notnullcolumns'];
			$notnulltype = $_SESSION['notnulltype'];
			//$form_array = makeformInsert_setV2($_SESSION['edit'], $out_column, $isReadOnly, "edit",$this->prContainer);
			$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $errorinfo, $isReadOnly, "delete",$this->prContainer);
			$form = $form_array[0];
			$makeDatepicker =  $form_array[1];
			//--↓明細作成--//
			$header_array = $this->makeList_itemV2('', $this->prContainer->pbInputContent);
			if(isset($header_array))
			{	
				$header = $header_array[0];
				$makeDatepicker .=  $header_array[1];
			}
			//--↑明細作成--//
			
			$send = '<form name ="edit" id="send" action="main.php?'.$this->prContainer->pbFileName.'=&comp" method="post" enctype="multipart/form-data" 
					onsubmit = "return check(\''.$checkList.
					'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
			$html = '<br>';
                        
                        //ダイアログ
                        $html .= '<div id="dialog" title="削除確認" style="display:none;">
					<p>削除してよろしいでしょうか？</p>
					</div>';
                        
			$html .= '<div style="clear:both;"></div>';
			$html .= $send;
			$html .= '<div class = "edit_table">';
			$html .=$form;
			//$html .= $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);
			$html .= $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep);
			$html .=$header;
			$html .= '</div>';
			$html .= '<div class = "pad">';
			$html .= '<input type="button" name = "delete" value = "削除" class="free" onClick="regist()">';
//			$html .= '<input type="submit" name = "cancel" value = "一覧に戻る" class="free" onClick ="isCancel = true;">';
                        $html .= '<input type="submit" name = "cancel" value = "一覧に戻る" class="free">';
			$html .='</div>';
			$html .= '</form>';
		}
		else
		{
			//エラー時共通出力
			$html = $this->makeErrorNotExist();
		}	
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
		//ダイアログ
		$html .= '<div id="dialog" title="削除確認" style="display:none;">
					<p>削除してよろしいでしょうか？</p>
					</div>';
		return $html;
	}
}

/**
 * 印刷用Pageクラス
 * 
 */
class CondisionPage extends InsertPage
{
	/** 画面識別 */
	protected $prRcall;
	/** 画面の設定値 */
	protected $prExecute;

	/**
	 * コンストラクタ
	 */
	public function __construct(&$container)
	{
		parent::__construct($container);
		$this->prRcall = '表示';
		$this->prExecute = '処理実行';
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
		$html = parent::makeScriptPart();
		
		$html .='<script language="JavaScript"><!--
				function switchAction( page_id ){
					$("form").attr("action", page_id);
					$("form").submit();
				}
				--></script>';
		
		return $html;
	}

	/**
	 * 関数名: makeBoxContentMain
	 *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
	 *   機能名の表示など
	 * 
	 * @retrun HTML文字列
	 */
	function makeBoxContentMain()
	{
		$_SESSION['pre_post'] = null;
		$recall = $this->prContainer->pbPageSetting['recall_type'];
		$exec_id = $this->prContainer->pbPageSetting['list_page'];
		
		//入力項目作成
		$form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, '', '', "insert", $this->prContainer);
		$form = $form_array[0];
		$this->prInitScript =  $form_array[1];
				
		$checkList = $_SESSION['check_column'];
		$notnullcolumns = $_SESSION['notnullcolumns'];
		$notnulltype = $_SESSION['notnulltype'];
		$send = '<form name ="print" id ="print" action="main.php?'.$this->prContainer->pbFileName.'_button=" method="post" enctype="multipart/form-data" 
				onsubmit = "return check(\''.$checkList.
				'\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
		
		//出力HTML
		$html = '<br>';
		$html .= $send;
		$html .= '<div class = "edit_table">';
		$html .= '<table><tr><td>';
		$html .= $form;
		$html .= '</td><td>';

		//権限が十分な場合だけボタンを表示
		if( isPermission($this->prContainer->pbFileName) ){
			if( $recall !== '0' )
			{
				$html .= '<button type="button" name = "recall" onclick="switchAction( \'main.php?'.$this->prContainer->pbFileName.'_button=\' );" class="free">'.$this->prRcall."</button>";
			}
			$html .= '<button type="button" name = "execute" onclick="switchAction( \'main.php?'.$exec_id.'_button=\' );" class="free">'.$this->prExecute."</button>";
		}
		$html .= '</td></tr></table>';
		
		$html .= '</div>';

		$html .= '</form>';
		
		return $html;
	}
	
}
