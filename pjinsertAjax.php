<?php

session_start();
require_once("f_Construct.php");
require_once("f_SQL.php");
require_once("./popup/pjinsertPopup.php");
    /**
     * ポップアップ入力項目作成
     */
    //IDを取得
    $page_id = filter_input(INPUT_GET, 'id');
    //GETでIDの指定がない場合は処理しない
    if ($page_id == '') {
        exit();
    }
    //設定ファイル読込み
    $factory = PageFactory::getInstance();
    $form_ini = $factory->pbFormIni;
    //引数用にpostを用意
    $post = array();

    //SQLを取得
    //$sql = getSelectSQL($post, $page_id);

    //指定idのコンテナを作成
    $container = new PageContainer($factory->pbFormIni);
    //指定IDの情報をメンバ変数に
    $container->ReadPage($page_id, "", STEP_NONE);
    //FactoryにPageを作ってもらう
    $page = $factory->createPage($page_id, $container);
    
    $formStrArray = $page->makeformSearch_setV2( $page->prContainer->pbInputContent, 'form' );
    $html = $formStrArray[0];			//0はフォーム用HTML






    // $html = "サンプル";
    echo $html;
