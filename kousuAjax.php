<?php

session_start();
require_once("f_Construct.php");
require_once("f_SQL.php");
require_once("./customHtml/kousu.php");
    /**
     * 工数入力コピー処理
     */
    //IDを取得
    $page_id = filter_input(INPUT_GET, 'id');
    $date = filter_input(INPUT_GET, 'date');
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

    //指定idのコンテナを作成
    $container = new PageContainer($factory->pbFormIni);
    //指定IDの情報をメンバ変数に
    $container->ReadPage($page_id, "", STEP_NONE);
    //FactoryにPageを作ってもらう
    $page = $factory->createPage($page_id, $container);
    $column = explode(",", $form_ini[$page_id]['page_columns']);
    for ($i = 0; $i < count($column); $i++) {
        $columnName[] = $container->pbParamSetting[$column[$i]]['item_name'];
    }
    //データリスト作成
    $sql = kousuCopySQL($_SESSION['STAFFID'], $date);
    $con = dbconect();
    $result = $con->query($sql);
    if (!$result) {
        error_log($con->error, 0);
        exit();
    }

    $resultArray = array();
    $count = 0;
    while ($result_row = $result->fetch_array(MYSQLI_ASSOC)) {
        $rowArray = array('PROJECTNUM' => $result_row['PROJECTNUM'],
            'EDABAN' => $result_row['EDABAN'],
            'PJNAME' => $result_row['PJNAME'],
            'KOUTEIID' => $result_row['KOUTEIID'],
            'KOUTEINAME' => $result_row['KOUTEINAME'],
            'TEIZITIME' => $result_row['TEIZITIME'],
            'ZANGYOUTIME' => $result_row['ZANGYOUTIME']);
        //配列に割り当て
        $resultArray[$count] = $rowArray;
        $count++;
    }

    //応答形式のjsonにあわせる
    $jsonArray = array('results' => $resultArray);

    //json形式に変換
    $jsonString = json_encode($jsonArray, JSON_UNESCAPED_UNICODE);

    header('Content-Type: application/json');


    echo $jsonString;
