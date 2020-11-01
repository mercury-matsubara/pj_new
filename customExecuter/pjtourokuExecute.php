<?php

class PjtourokuExecuteSQL extends BaseLogicExecuter
{
    private $pCode;
    private $eCode;
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {            
            //DB接続、トランザクション開始
            $con = beginTransaction();
            
            $filename = $this->prContainer->pbFileName;
            $main_table = $this->prContainer->pbPageSetting['use_maintable_num'];
            $input = $this->prContainer->pbInputContent;
            //$code = getCode($filename,$input);
            $checkcode = $this->selectCode($input,$con);
            if($checkcode === false){
                //トランザクションコミットまたはロールバック
                commitTransaction(false,$con);
            }
//            $errorinfo = $this->existCheck($code,$main_table,1,$con);
//            if(count($errorinfo) != 1 || $errorinfo[0] != "")
//            {
//                
//            }
            $sql = "INSERT INTO projectinfo (1CODE,2CODE,CHARGE)VALUES(?,?,?);";
            $stmt = $con->prepare($sql);
            $stmt->bind_param('iii',$this->pCode, $this->eCode, $input['form_pjtCHARGE_0']);
            //クエリを実行
            $result = $stmt->execute();
            //ステートメントを閉じる
            $stmt->close();
            //トランザクションコミットまたはロールバック
            commitTransaction($result,$con);
            $this->PageJump("TOP_5", "", STEP_NONE, "", "");
    }
    
    /**
     * CODE取得
     */
    function selectCode($input,$con){
 
        $pSql = "SELECT 1CODE FROM projectnuminfo where PROJECTNUM ='".$input['form_pjtPROJECTNUM_0']."'  AND PROJECTNAME ='" .$input['form_pjtPROJECTNAME_0']."' ";
        $eSql = "SELECT 2CODE FROM edabaninfo where EDABAN ='".$input['form_pjtEDABAN_0']."'  AND PJNAME ='" .$input['form_pjtPJNAME_0']."' ";
        
        $result = $con->query($pSql);// クエリ発行
	if (!$result) {
            error_log($con->error, 0);
            return false;
        }
        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $this->pCode = $result_row['1CODE'];
        }
        $result2 = $con->query($eSql);// クエリ発行
	if (!$result2) {
            error_log($con->error, 0);
            return false;
        }
        while($result_row = $result2->fetch_array(MYSQLI_ASSOC))
        {
            $this->eCode = $result_row['2CODE'];
        }
        
        
        return true;
    }
    
    /*
     * function existCheck($post,$tablenum,$type)
     * 
     * 引数1		$post							登録フォーム入力値
     * 引数2		$tablenum						テーブル番号
     * 引数3		$type							1:insert 2:edit 3:delete
     * 
     * 戻り値		$errorinfo						既登録確認結果
     */
//    function existCheck($post,$tablenum,$type,$con){
//
//            //------------------------//
//            //        初期設定        //
//            //------------------------//
//            $form_ini = parse_ini_file('./ini/form.ini', true);
//            require_once ("f_Form.php");
//            //require_once ("f_DB.php");																							// DB関数呼び出し準備
//            require_once ("f_SQL.php");																							// SQL関数呼び出し準備
//
//            //------------------------//
//            //          定数          //
//            //------------------------//
//            $filename = $_SESSION['filename'];
//            $uniquecolumn = $form_ini[$filename]['uniquecheck'];
//            $uniquecolumn_array = explode(',',$uniquecolumn);
//            $master_tablenum = $form_ini[$tablenum]['seen_table_num'];
//            $master_tablenum_array = explode(',',$master_tablenum);
//            //------------------------//
//            //          変数          //
//            //------------------------//
//            $errorinfo = array();
//            $errorinfo[0] = "";
//            $sql = "";
//            $judge = false;
//            $codeValue = "";
//            $code = "";
//            $table_title = "";
//            $counter = 1;
//            $syorimei = "";
//
//            //------------------------//
//            //          処理          //
//            //------------------------//
//            switch($type)
//            {
//            case 1 :
//                    $syorimei = "登録";
//                    break;
//            case 2 :
//                    $syorimei = "編集";
//                    break;
//            case 3 :
//                    $syorimei = "削除";
//                    break;
//            default :
//                    break;
//            }																								// db接続関数実行
//            if($type == 1)
//            {
//
//                    if ($filename ==  "PJICHIRAN_1") {
//                            $cntrow = 0;
//                            $code1 = "";
//                            $code2 = "";
//                            $code1 = $post['1CODE'];
//                            $code2 = $post['2CODE'];
//
//                            $sql = "SELECT COUNT(*) FROM projectinfo WHERE 1CODE = ".$code1." AND 2CODE = ".$code2." ;";
//
//                            $result = $con->query($sql) or ($judge = true);																	// クエリ発行
//                            if($judge)
//                            {
//                                    error_log($con->error,0);
//                                    $judge = false;
//                            }
//
//                            while($result_row = $result->fetch_array(MYSQLI_ASSOC))
//                            {
//                                    $cntrow = $result_row['COUNT(*)'] ;
//                            }
//
//                            if($cntrow > 0){
//                                    $errorinfo[$counter] = "<div class = 'center'><a class = 'error'>".
//                                                                                    $table_title."すでに登録されているPJ情報ため".
//                                                                                    $syorimei."できません。</a></div><br>";
//                                    $counter++;
//                            }
//                    }
//
//            }
//            if($type == 2)
//            {
//                    $table_title = $form_ini[$tablenum]['table_title'];
//                    $code = $tablenum.'CODE';
//                    $codeValue = $post[$code];
//                    $sql = idSelectSQL($codeValue,$tablenum,$code);
//                    $result = $con->query($sql) or ($judge = true);																	// クエリ発行
//                    if($judge)
//                    {
//                            error_log($con->error,0);
//                            $judge = false;
//                    }
//                    if($result->num_rows == 0 )
//                    {
//                            $errorinfo[$counter] = "<div class = 'center'><a class = 'error'>".
//                                                                            $table_title."情報が削除されているため".
//                                                                            $syorimei."できません。</a></div><br>";
//                            $counter++;
//                    }
//                    else
//                    {
//                            $errorinfo[$counter] = "";
//                            $counter++;
//                    }
//            }
//            for( $j = 0 ; $j < count($uniquecolumn_array) ; $j++)
//            {
//                    if($uniquecolumn_array[$j] == "")
//                    {
//                            break;
//                    }
//                    $sql = uniqeSelectSQL($post,$tablenum,$uniquecolumn_array[$j]);
//                    if($sql != '')
//                    {
//                            $result = $con->query($sql) or ($judge = true);																// クエリ発行
//                            if($judge)
//                            {
//                                    error_log($con->error,0);
//                                    $judge = false;
//                            }
//                            if($result->num_rows != 0 )
//                            {
//                                    $errorinfo[0] .= $uniquecolumn_array[$j].",";
//                            }
//                    }
//            }
//            for($k = 0 ; $k < count($master_tablenum_array) ; $k++ )
//            {
//                    if($master_tablenum == '')
//                    {
//                            break;
//                    }
//                    $table_title = $form_ini[$master_tablenum_array[$k]]['table_title'];
//                    $code = $master_tablenum_array[$k].'CODE';
//                    $codeValue = $post[$code];
//                    $sql = idSelectSQL($codeValue,$master_tablenum_array[$k],$code);
//                    $result = $con->query($sql) or ($judge = true);																	// クエリ発行
//                    if($judge)
//                    {
//                            error_log($con->error,0);
//                            $judge = false;
//                    }
//                    if($result->num_rows == 0 )
//                    {
//                            $errorinfo[$counter] = "<div class = 'center'><a class = 'error'>".
//                                                                            $table_title."情報が削除されているため".
//                                                                            $syorimei."できません。</a></div><br>";
//                            $counter++;
//                    }
//            }
//            return ($errorinfo);
//    }
}