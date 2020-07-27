<?php
	session_start();
?>
<p>
<?php
	//IDを取得
	$page_id = filter_input( INPUT_GET, 'id' );
	//GETでIDの指定がない場合は処理しない
	if($page_id =='')
	{
		exit();
	}
	
	//チェックボックス有無
	$check = filter_input( INPUT_GET, 'check' );
	//GETでIDの指定がない場合は処理しない
	if($check =='box')
	{
		$check = true;
	}
	else
	{
		$check = false;
	}

	//ライブラリ読込み
	require_once("f_Construct.php");
	require_once("f_SQL.php");
	require_once("classesHtmlCustom.php");

	//設定ファイル読込み
	$factory = PageFactory::getInstance();
	$form_ini =$factory->pbFormIni;
	//引数用にpostを用意
	$post = array();

	//SQLを取得
	$sql = getSelectSQL($post, $page_id);

	// GET情報をまわす
	$where = '';
	$edit_list_id = '';
	$link_param = '';
	foreach($_GET as $key  =>  $value)	{
		// idはパス
		if($key === 'id'){
			continue;
		}
		if($where != ''){
			$where .= ' AND ';
			$link_param .= '&';
		}
		//キーからテーブル識別
		$table_id = $form_ini[$key]['table_num'];
		//SQL条件としてテーブル名.項目名 = 値 を作成
		$where .= $form_ini[$table_id]['table_name'].'.'.$form_ini[$key]['column'].'='.$value;
		//リンク条件としてfrm_テーブル識別項目名_0=値 を作成
		$link_param .= 'form_'.$table_id.$form_ini[$key]['column'].'_0='.$value;
	}
	//条件付与
	if($where != '')	{
		$where = str_replace('_', '.', $where);
		$sql[0] .= ' WHERE '.$where .' ';
		$sql[1] .= ' WHERE '.$where .' ';
	}

	$sql = setSQLOrderby($page_id, $form_ini, $sql);
	//ここでは先頭から一定の件数を読む
	$limit = "LIMIT 0, 15";
	$limit_start = 0;
//	echo $sql[1];//デバック用

	//指定idのコンテナを作成
	$container = new PageContainer( $factory->pbFormIni );
	//指定IDの情報をメンバ変数に
	$container->ReadPage( $page_id, $value, STEP_NONE );
	//FactoryにPageを作ってもらう
	$page = $factory->createPage( $page_id, $container );
	//ページにテーブルを作ってもらう
	$page_move = PAGE_NONE;
	if( $form_ini[$page_id]['isPageMove'] === '1' )	{
		$page_move = PAGE_COUNT_ONLY;
	}
	$result = $page->makeListV2( $sql, $post, $limit, $limit_start, $page_move );		
	//下のボタンのつけたし
	$result .= $page->makeButtonV2($page_id, 'bottom', STEP_NONE, $link_param);

	//HTMLを返す
	echo $result;
?>
</p>