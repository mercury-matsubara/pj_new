<?php

/**
 * データクラス
 * 
 */
class PageContainer
{
	/** 画面識別 */
	public $pbFileName;
	/** 画面の設定値 */
	public $pbPageSetting;
	/** 画面用のデータ項目設定値配列 */
	public $pbParamSetting;
	/** form.iniの全情報 */ 
	public $pbFormIni;
	/** 入力内容保持変数*/
	public $pbInputContent;
	public $pbPageCheck;
	/** 画面ID保持($_SESSION['list']['id'])*/
	public $pbListId;
	/** 画面判定$step変数*/
	public $pbStep;
	/** コピー時の明細内容保持*/
	public $pbSecondInputContent;
	
	/**
	 * コンストラクタ
	 */
	public function __construct($formini)
	{
		$this->pbFormIni = $formini;
		require_once ("f_DB.php");
	}
	
	/**
	 * IDからページ設定をメンバ変数に落とし込む
	 * checkページの判定
	 * @param string $filename 画面名
	 * @param string $list_id 画面ID
	 * @param string $step 画面判定変数
	 */
	public function ReadPage( $filename, $list_id, $step )
	{

		if($list_id != "")
		{
			//画面ID保持($_SESSION['list']['id'])
			$this->pbListId = $list_id;
			unset($_SESSION['list']['id']);
		}
		else
		{
			//関数PageJump使用時
			if(isset($_SESSION['list']['id']))
			{
				$this->pbListId = $_SESSION['list']['id'];
				
				//見積コピー時
				if(isset($_SESSION['Content']))//見積の内容
				{
					$this->pbInputContent = $_SESSION['Content'];
					unset($_SESSION['Content']);
				}
				if(isset($_SESSION['SecondContent']))//コピー時の明細内容保持
				{
					$this->pbSecondInputContent = $_SESSION['SecondContent'];
				}
				
				unset($_SESSION['list']['id']);
			}
			else if(isset($_GET['edit_list_id']))//チェックページ遷移時
			{
				//画面ID
				$this->pbListId = $_GET['edit_list_id'];
			}
			else
			{
				//データ処理時,初期起動時,リスト表示時
				//$step = 0;
			}	
			
		}
		
		//ページfilename
		$this->pbFileName = $filename;
		//form_iniの設定値(ページの設定値)
		$this->pbPageSetting = $this->pbFormIni[$filename];

		//列設定
		if(isset($this->pbPageSetting['page_columns']))
		{
			//カラム取得
			$columns = $this->pbPageSetting['page_columns'];
			$columns_array = explode(',',$columns);
			//form_ini項目設定値
			$this->pbParamSetting = array();
			foreach ($columns_array as $key => $value)
			{
				if($value === '' || $value === 'sp01' || $value === 'sp02' )
				{	//ブランクは飛ばす
					continue;
				}
				$this->pbParamSetting[$value] = $this->pbFormIni[$columns_array[$key]];
			}
		}
		
		//送信情報からID指定を取得する試み
		// ボタン設定
		global $button_ini;
		if( $button_ini === null)
		{
			// ボタン設定読込み
			$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
		}
		//ページに指定されているキー
		if( array_key_exists( $filename, $button_ini ) === true )
		{
			$key_column = $button_ini[$filename]['key_column'];
			if($key_column !== '' && isset($_GET[$key_column]))
			{
				$this->pbListId = $_GET[$key_column];
			}
		}
		//メインテーブルからID指定を取得する試み
		if($this->pbListId === null && isset($this->pbPageSetting['use_maintable_num']))
		{
			//メインテーブル識別
			$maintable_id = $this->pbPageSetting['use_maintable_num'];
			//識別から一意キー項目名を作成
			$key_id = 'form_'.$maintable_id.strtoupper($maintable_id).'ID_0';
			if(isset($_GET[$key_id]))
			{
				$this->pbListId = $_GET[$key_id];
			}
		}
		
		//--ステップ変数の代入--//
		if(isset($_GET['step']))
		{
			//画面判定,処理判定$step変数
			$this->pbStep = $_GET['step'];
		}
		if(isset($step))
		{
			//画面判定,処理判定$step変数
			$this->pbStep = $step;
		}
		
		
		//if(isset($_GET))
		if(count($_GET) != 0 && $this->pbInputContent == "")
		{
			//入力情報を保持
			$this->pbInputContent = $_GET;
			
			if(array_key_exists('limitstart', $_GET) )
			{
				$this->pbInputContent['list']['limitstart'] = intval( $_GET['limitstart'] );
			}
			
			//Checkページか判定
			$Checkarray = array_keys($_GET);
			foreach ($Checkarray as $key)
			{
				if($key == 'insert')
				{
					$this->pbPageCheck = 'InsertCheck';
				}
				else if($key == 'kousinn')
				{
					$this->pbPageCheck = 'EditCheck';
					//ユニークキー確認
					if(isset($_SESSION['list']['uniqe']))
					{
						$uniqe = $_SESSION['list']['uniqe'];
						$this->pbInputContent['uniqe'] = $uniqe;
					}
				}
				else if($key =='delete')
				{
					$this->pbPageCheck = 'DeleteCheck';
				}
				else if($key == 'Comp' || $key == 'CompDel' || strstr($key, 'pjend0') || strstr($key, '_Del') || strstr($key, 'pjcancel0'))
				{
					$this->pbPageCheck = 'Execute';
				}
			}
			
			
			//編集画面時uniqe取得
			if($step == STEP_EDIT || $step == STEP_DELETE)
			{	
				if(!isset($this->pbPageCheck))//チェックページなら処理は行わない
				{
					//入力値取得
					$this->pbInputContent = make_post($this->pbInputContent, $this->pbListId);
					if(isset($this->pbInputContent['uniqe']))
					{
						$_SESSION['list']['uniqe'] = $this->pbInputContent['uniqe'];
					}
				}
			}

		}
	}
}