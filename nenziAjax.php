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
    //検索条件
    $key = filter_input(INPUT_GET, 'key' , FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    $search = filter_input(INPUT_GET, 'search' , FILTER_DEFAULT, FILTER_REQUIRE_ARRAY);
    
    //設定ファイル読込み
    $factory = PageFactory::getInstance();
    $form_ini = $factory->pbFormIni;
    //引数用にpost,limitを用意
    $post = array();
    $limit = "";
    $limit_start = 0;
    // 検索か判定するフラグ
    $flg = false;
    //ページにテーブルを作ってもらう
    $page_move = PAGE_COUNT_ONLY;
    //リスト作成判定
    $popup = 1;
    //SQLを取得
    $sql = getSelectSQL($post, $page_id);
    // where文作成
    $where = '';
    if($key !== false) {
	foreach($search as $column  =>  $value)	{
            // value値がない場合スキップ
            if ($value === ""){
                continue;
            }
            // 検索項目複数時
            if ($where != '' ) {
                $where .= ' AND ';
            }
            //キーからテーブル識別
            $table_id = $form_ini[$column]['table_num'];
            //SQL条件としてテーブル名.項目名 = 値 を作成
            $where .= $form_ini[$table_id]['table_name'] . '.' . $form_ini[$column]['column'] . " LIKE '%$value%'";
        }
        $flg = true;
    }
    //条件付与
    if ($where != '') {
        $where = str_replace('_', '.', $where);
        $sql[0] .= ' WHERE ' . $where . ' ';
        $sql[1] .= ' WHERE ' . $where . ' ';
    }
    // スタッフID置換
    $sql[0] = str_replace('@01', $_SESSION['STAFFID'], $sql[0]);
    $sql[1] = str_replace('@01', $_SESSION['STAFFID'], $sql[1]);
    // 並び順取得
    $sqlv2 = setSQLOrderby($page_id, $form_ini, $sql);
    //指定idのコンテナを作成
    $container = new PageContainer($factory->pbFormIni);
    //指定IDの情報をメンバ変数に
    $container->ReadPage($page_id, "", STEP_NONE);
    //FactoryにPageを作ってもらう
    $page = $factory->createPage($page_id, $container);
    //検索フォーム作成
    $form = $page->createSearchForm( $page_id, 'form', $flg );
    //データリスト作成
    $list = $page->makeListV2( $sqlv2, $post, $limit, $limit_start, $page_move,$popup );
    
    if($flg === false){
        //初期表示
        $html = $form;
        $html .= "<div class='list_content'>";
        $html .= $list;
        $html .= "</div>";
    } else {
        //表示ボタン押下時
        $html = $list;
    }
    
    echo $html;
