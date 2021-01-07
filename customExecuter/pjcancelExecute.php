<?php

class PjcancelExecuteSQL extends BaseLogicExecuter
{
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {
            $filename = $_SESSION['filename'];
            
            //DB接続、トランザクション開始
            $con = beginTransaction();

            for($i=0;$i<$_GET['pjcancelCount'];$i++)
            {
                $code[$i] = str_replace('check_','',$_GET['pjcancel'.$i]);
            }
            $message = $this->pjcancel($code,$con);
            //トランザクションコミットまたはロールバック
            commitTransaction($message,$con);
            
            $id = 0;
            $step = 0;
            if($message)
            {       
                $this->PageJump($filename,$id,$step,"","");
            }
            else
            {
                $this->PageJump($filename,$id,$step,"","",$message);
            }    
    }

    /*
     * 終了PJキャンセル処理(プロジェクト管理システム)
     * function pjagain($post)
     * 
     * 引数1		$post						対象
     * 戻り値		$form						モーダルに表示リストhtml
     */
    function pjcancel($code,$con){

            //------------------------//
            //        初期設定        //
            //------------------------//
            require_once("f_DB.php");																							// DB関数呼び出し準備
            require_once("f_File.php");																							// DB関数呼び出し準備
            $form_ini = parse_ini_file('./ini/form.ini', true);
            $item_ini = parse_ini_file('./ini/item.ini', true);

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $pjid = $code;
            $nowdate = date_create("NOW");
            $nowdate = date_format($nowdate, 'Y-n-j');
            $teijitime = (float)$item_ini['settime']['teijitime'];

            //------------------------//
            //          変数          //
            //------------------------//
            $judge = false;
            $time = array();
            $teizi = array();
            $zangyou = array();
            $charge = 0;
            $period = 0;
            $code5 = 0;
            $code6 = 0;
            $code8 = 0;
    //	$sql6 = "";

            //------------------------//
            //        検索処理        //
            //------------------------//	
    //	$sql = "SELECT * FROM endpjinfo;";
            for($i=0;$i<count($code);$i++)
            {
                $sql = "SELECT * FROM endpjinfo LEFT JOIN projectditealinfo USING (6CODE) LEFT JOIN syaininfo USING (4CODE)"
                                ." RIGHT JOIN projectinfo USING (5CODE) LEFT JOIN progressinfo USING (6CODE) LEFT JOIN projectnuminfo USING (1CODE) LEFT JOIN edabaninfo USING (2CODE)"
                                ." WHERE projectinfo.5CODE = ".$pjid[$i].";";			// db接続関数実行
                $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                if($judge)
                {
                        error_log($con->error,0);
                        $judge = false;
                }
                while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                {
                        if($code8 != $result_row['8CODE'])
                        {
                                $code8 = $result_row['8CODE'];
                                //endpjinfoから削除
                                $sql_delete =  "DELETE FROM endpjinfo WHERE 8CODE = ".$code8." ;";
                                $result_delete = $con->query($sql_delete) or ($judge = true);																		// クエリ発行
                                if($judge)
                                {
                                        error_log($con->error,0);
                                        $judge = false;
                                }
                        }
                        if($code5 != $result_row['5CODE'])
                        {
                                $code5 = $result_row['5CODE'];
                                //フラグを1（未処理）に変更
                                $sql5 = "UPDATE projectinfo SET  5ENDDATE = NULL , 5PJSTAT = '1' WHERE 5CODE = ".$code5.";";
                                $result5 = $con->query($sql5) or ($judge = true);																		// クエリ発行
                                if($judge)
                                {
                                        error_log($con->error,0);
                                        $judge = false;
                                }
                        }

                        if($code6 != $result_row['6CODE'])
                        {
                                $code6 = $result_row['6CODE'];
                                //フラグを1（未処理）に変更
                                $sql6 = "UPDATE projectditealinfo SET  6ENDDATE = NULL , 6PJSTAT = '1' WHERE 6CODE = ".$code6.";";
                                $result6 = $con->query($sql6) or ($judge = true);																		// クエリ発行
                                if($judge)
                                {
                                        error_log($con->error,0);
                                        $judge = false;
                                }
                                $sql7 = "UPDATE progressinfo SET  7ENDDATE = NULL , 7PJSTAT = '1' WHERE 6CODE = ".$code6.";";
                                $result7 = $con->query($sql7) or ($judge = true);																		// クエリ発行
                                if($judge)
                                {
                                        error_log($con->error,0);
                                        $judge = false;
                                }
                        }
                }
                return true;
        }
    }
}