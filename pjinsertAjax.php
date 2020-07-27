<?php

session_start();
require_once("f_Construct.php");
require_once("f_SQL.php");
/**
 * プロジェクト登録ポップアップ入力項目作成
 * 
 */
//IDを取得
$page_id = filter_input(INPUT_GET, 'id');
//GETでIDの指定がない場合は処理しない
if ($page_id == '') {
    exit();
}
$html = "サンプル";
echo $html;
