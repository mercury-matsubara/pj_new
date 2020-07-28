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
            $id = 0;
            $step = 1;
            $judge = false;
            
            //DB接続、トランザクション開始
            $con = beginTransaction();
            
            $sagyoudate = explode("/",$_POST['form_pjpSAGYOUDATE_0']);
            $errorinfo = $this->endCheck($sagyoudate[0],$sagyoudate[1],$con);
            if($errorinfo === '')
            {             
                    $post = $_POST;
                    if($post['step'] === "1")
                    {
                        $this->insert($post,$con);
                    }
                    else if($post['step'] === "2")
                    {
                        $this->update($post,$con);
                    }
                    $filename = "PROGRESSINFO_2";
                    $step = 0;
                    $judge = true;
            }

            //トランザクションコミットまたはロールバック
            commitTransaction($judge,$con);    
            
            $this->PageJump($filename,$id,$step,"","",$errorinfo);
    }
    /*
     * function endCheck($year,$month)
     * 
     * 引数1		$post							登録フォーム入力値
     * 引数2		$tablenum						テーブル番号
     * 引数3		$type							1:insert 2:edit 3:delete
     *                  $con
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
            $errorinfo = "";
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
                    $errorinfo = "既に月次処理が完了している期間のため、登録できません。";
            }
            return ($errorinfo);
    }
    /*
     * function insert($post)
     * 
     * 引数		$post						入力内容
     * 戻り値		なし
     */
    function insert($post,$con){

            //------------------------//
            //        初期設定        //
            //------------------------//
            $form_ini = parse_ini_file('./ini/form.ini', true);
            require_once ("f_Form.php");
            require_once ("f_DB.php");																							// DB関数呼び出し準備
            require_once ("f_SQL.php");																							// DB関数呼び出し準備

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $tablenum = $form_ini[$filename]['use_maintable_num'];
            $list_tablenum = $form_ini[$tablenum]['see_table_num'];
            $list_tablenum_array = explode(',',$list_tablenum);
            $main_table_type = $form_ini[$tablenum]['table_type'];
            //------------------------//
            //          変数          //
            //------------------------//
            $sql = "";
            $judge = false;
            $endjudge = false;
            $codeValue = "";
            $code = "";
            $counter = 1;
            $main_CODE =0;
            $over = array();

            //------------------------//
            //          処理          //
            //------------------------//																								// db接続関数実行
            if(!$endjudge)
            {
                    $sql = "INSERT INTO progressinfo (SAGYOUDATE,TEIZITIME,ZANGYOUTIME,3CODE,6CODE)VALUES('".$post['form_pjpSAGYOUDATE_0']."',".$post['form_pjpTEIZITIME_0'].",".$post['form_pjpZANGYOUTIME_0'].",45,6521);";
                    $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                    if($judge)
                    {
                            error_log($con->error,0);
                            $judge =false;
                    }
                    if($main_table_type == 0)
                    {
                            $main_CODE = $con->insert_id;
                            $post[$tablenum.'CODE'] = $main_CODE;
                            for( $i = 0 ; $i < count($list_tablenum_array) ; $i++)
                            {
                                    if($list_tablenum_array[$i] == "" )
                                    {
                                            break;
                                    }
                                    $over =getover($post,$list_tablenum_array[$i]);
                                    for( $j = 0; $j < count($over) ; $j++ )
                                    {
                                            $sql = InsertSQL($post,$list_tablenum_array[$i],$over[$j]);
                                            $result = $con->query($sql) or ($judge = true);																// クエリ発行
                                            if($judge)
                                            {
                                                    error_log($con->error,0);
                                            }
                                    }
                            }
                    }
            }
    }
    /*
     * function update($post)
     * 
     * 引数		$post								入力内容
     *                  $con
     * 戻り値		なし
     */
    function update($post,$con)
    {
            //------------------------//
            //        初期設定        //
            //------------------------//
            $form_ini = parse_ini_file('./ini/form.ini', true);
            require_once ("f_Form.php");
            require_once ("f_DB.php");																							// DB関数呼び出し準備
            require_once ("f_SQL.php");																							// DB関数呼び出し準備

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $tablenum = $form_ini[$filename]['use_maintable_num'];
            $list_tablenum = $form_ini[$tablenum]['see_table_num'];
            $list_tablenum_array = explode(',',$list_tablenum);
            $main_table_type = $form_ini[$tablenum]['table_type'];
            //------------------------//
            //          変数          //
            //------------------------//
            $sql = "";
            $judge = false;
            $codeValue = "";
            $code = "";
            $counter = 1;
            $main_CODE =0;
            $over = array();
            $delete =array();
            $delete_param = array();
            $delete_path = "";
            $delete_CODE = "";

            //------------------------//
            //          処理          //
            //------------------------//																									// db接続関数実行
            $sql = "UPDATE progressinfo SET SAGYOUDATE = '".$post['form_pjpSAGYOUDATE_0']."', TEIZITIME = ".$post['form_pjpTEIZITIME_0'].", ZANGYOUTIME = ".$post['form_pjpZANGYOUTIME_0']." WHERE 7CODE = ".$post['edit_list_id'].";";
            $result = $con->query($sql) or ($judge = true);																		// クエリ発行
            if($judge)
            {
                    error_log($con->error,0);
            }
            if($main_table_type == 0)
            {
                    for( $i = 0 ; $i < count($list_tablenum_array) ; $i++)
                    {
                            if(isset($post['delete'.$list_tablenum_array[$i]]))
                            {
                                    $delete = $post['delete'.$list_tablenum_array[$i]];
                                    for($j = 0 ; $j < count($delete) ; $j++)
                                    {
                                            $delete_param = explode(':',$delete[$j]);
                                            $delete_path = $delete_param[0];
                                            $delete_CODE = $delete_param[1];
                                            $tablenum = $list_tablenum_array[$i];
                                            $code = $tablenum.'CODE';
                                            if(file_exists($delete_path))
                                            {
                                                    unlink($delete_path);
                                            }
                                            $sql = DeleteSQL($delete_CODE,$tablenum,$code);
                                            $result = $con->query($sql) or ($judge = true);																// クエリ発行
                                            if($judge)
                                            {
                                                    error_log($con->error,0);
                                            }
                                    }
                            }
                    }
                    for( $i = 0 ; $i < count($list_tablenum_array) ; $i++)
                    {
                            if($list_tablenum_array[$i] == "" )
                            {
                                    break;
                            }
                            $over =getover($post,$list_tablenum_array[$i]);
                            for( $j = 0; $j < count($over) ; $j++ )
                            {
                                    $sql = InsertSQL($post,$list_tablenum_array[$i],$over[$j]);
                                    $result = $con->query($sql) or ($judge = true);																// クエリ発行
                                    if($judge)
                                    {
                                            error_log($con->error,0);
                                    }
                            }
                    }
            }

    }
}