<?php
class ProgressExecuteSQL extends BaseLogicExecuter
{
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {
            $filename = $_SESSION['filename'];
            $errorinfo = "";
            
            //DB接続、トランザクション開始
            $con = beginTransaction();
            
            $sagyoudate = explode("/",$_POST['form_pjpSAGYOUDATE_0']);
            $errorinfo = $this->endCheck($sagyoudate[0],$sagyoudate[1],$con);
            if(count($errorinfo) == 1 && $errorinfo[0] == '')
            {
                    $judge = true;
                    $_SESSION['insert']['true'] = true;
                    $_SESSION['pre_post'] = $_SESSION['post'];
            }

            //トランザクションコミットまたはロールバック
            commitTransaction($errorinfo,$con);    
    }
    /*
     * function endCheck($year,$month)
     * 
     * 引数1		$post							登録フォーム入力値
     * 引数2		$tablenum						テーブル番号
     * 引数3		$type							1:insert 2:edit 3:delete
     * 
     * 戻り値		$errorinfo						既登録確認結果
     */
    function endCheck($year,$month,$con){

            //------------------------//
            //        初期設定        //
            //------------------------//
            $form_ini = parse_ini_file('./ini/form.ini', true);
            require_once ("f_Form.php");
            //require_once ("f_DB.php");																							// DB関数呼び出し準備
            require_once ("f_SQL.php");																							// SQL関数呼び出し準備

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];

            //------------------------//
            //          変数          //
            //------------------------//
            $errorinfo = array();
            $errorinfo[0] = "";
            $sql = "";
            $judge = false;
            $codeValue = "";
            $code = "";
            $table_title = "";
            $counter = 1;
            $syorimei = "";

            //------------------------//
            //          処理          //
            //------------------------//					
            $month = ltrim($month, '0');																			// db接続関数実行
            $sql = "SELECT * FROM endmonthinfo WHERE YEAR = '".$year."' AND MONTH = '".$month."';";
            $result = $con->query($sql);
            $rows = $result->num_rows;
            if($rows > 0)
            {
                    $errorinfo[1] = "<div class = 'center'><a class = 'error'>既に月次処理が完了している期間のため、登録できません。</a></div><br>";
            }
            return ($errorinfo);
    }
}