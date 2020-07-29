<?php

/*
 * 社員別金額設定画面作成
 * 
 */
class StaffMoneySet extends ListPage
{
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
            $this->getInfo($this->prContainer->pbInputContent);
            $this->setSearchSession($this->prContainer->pbInputContent);
            $formStrArray = $this->makeformSearch_setV2( $this->prContainer->pbInputContent, 'form' );
            $form = $formStrArray[0];			//0はフォーム用HTML
            $this->prInitScript = $formStrArray[1];	//1は構築用スクリプト
            
            //検索SQL
            $sql = array();
            $sql[] = "SELECT * FROM syaininfo WHERE DELETEFLG = 0 ORDER BY STAFFID ASC ;";
            $sql[] = "SELECT COUNT(*) FROM syaininfo WHERE DELETEFLG = 0 ORDER BY STAFFID ASC ;";
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

            $html .= '</table></form>';
            $html .= '<div>合計金額：<input type = text></div>';
            $html .= $list;
            
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
//            if( array_key_exists( $this->prFileNameInsert, $button_ini ) === true )
//            {
//                    $html .= '<input type ="submit" value = "新規作成" class = "free" name = "'.$this->prFileNameInsert.'_button">';
//            }
//            //CSVボタン
//            $is_csv = $this->prContainer->pbPageSetting['isCSV'];
//            if( $is_csv === '1' )
//            {
//                    $html .='　<a href="csv_out.php?id='.$this->prContainer->pbFileName.'&'.$_SERVER['QUERY_STRING'].'" target="_blank" class="btn-radius">CSV出力</a>';
//            }
            $html = '</form></div>';

            return $html;
    }
    /*
     * プロジェクトナンバ、枝番、製版・案件名取得
     */
    function getInfo($post)
    {
        $judge = false;
        $result = true;
        
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
        $pjnum_sql = "SELECT PROJECTNUM FROM projectnuminfo WHERE 1CODE = ".$post['form_pjt1CODE_0'].";";
        $pjnum_reply = $con->query($pjnum_sql) or ($judge = true);																		// クエリ発行
        if($judge)
        {
                error_log($con->error,0);
                $result =false;
        }
        while($result_row = $pjnum_reply->fetch_array(MYSQLI_ASSOC))
        {
                $this->prContainer->pbInputContent['form_pjtPROJECTNUM_0'] = $result_row['PROJECTNUM'] ;
        }
        
        $edaban_sql = "SELECT * FROM edabaninfo WHERE 2CODE = ".$post['form_pjt2CODE_0'].";"; 
        $edaban_reply = $con->query($edaban_sql) or ($judge = true);																		// クエリ発行
        if($judge)
        {
                error_log($con->error,0);
                $result =false;
        }
        while($result_row = $edaban_reply->fetch_array(MYSQLI_ASSOC))
        {
                $this->prContainer->pbInputContent['form_pjtEDABAN_0'] = $result_row['EDABAN'] ;
                $this->prContainer->pbInputContent['form_pjtPJNAME_0'] = $result_row['PJNAME'] ;
        }
        
        //トランザクションコミットまたはロールバック
        commitTransaction($result,$con);
    }
}