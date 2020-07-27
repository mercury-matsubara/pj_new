<?php
	session_start();

	//IDを取得
	$page_id = filter_input( INPUT_GET, 'id' );
	//GETでIDの指定がない場合は処理しない
	if($page_id =='')
	{
		exit();
	}
	
	//ライブラリ読込み
	require_once("f_Construct.php");
	require_once("f_SQL.php");
	require_once("classesHtmlCustom.php");

	$factory = PageFactory::getInstance();
	//フォーム設定情報の読込み
	$container = new PageContainer( $factory->pbFormIni );
	//指定IDの情報をメンバ変数に
	$container->ReadPage( $page_id, '', STEP_NONE );
	//FactoryにPageを作ってもらう
	$page = $factory->createPage( $page_id, $container );
	//ページにCSVを作ってもらう
	$page->executePreHtmlFunc();
	$result = $page->makCsv();		

	header('Content-Type: text/csv');
	header("Content-Disposition: attachment; filename=output_data.csv");
	
	//HTMLを返す
	echo $result;
