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
                if($this->prContainer->pbStep === "1")
                {
                    $judge = $this->insert($post,$con);
                }
                else if($this->prContainer->pbStep === "2")
                {
                    $judge = $this->update($post,$con);
                }
                else if($this->prContainer->pbStep === 3)
                {
                    $judge = $this->delete($post,$con);
                }
                $filename = "PROGRESSINFO_2";
                $step = 0;
                // $judge = true;
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
     * 戻り値		$errorinfo						既登録確認結果
     */
    function endCheck($year,$month,$con) {
        
        $errorinfo = "";
        $sql = "";
			
        $month = ltrim($month, '0'); // db接続関数実行
        $sql = "SELECT * FROM endmonthinfo WHERE YEAR = '" . $year . "' AND MONTH = '" . $month . "';";
        $result = $con->query($sql);
        $rows = $result->num_rows;
        if ($rows > 0) {
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
    function insert($post,$con) {
        
        $code = $this->selectCode($post,$con);
        if($code === false){
            return false;
        }
        // 登録処理
        $sql = "INSERT INTO progressinfo (SAGYOUDATE,TEIZITIME,ZANGYOUTIME,3CODE,6CODE)"
                . "VALUES('" . $post['form_pjpSAGYOUDATE_0'] . "'," . $post['form_pjpTEIZITIME_0'] . "," . $post['form_pjpZANGYOUTIME_0'] . ",".$code['3CODE'].",".$code['6CODE'].");";
        $result = $con->query($sql) or ( $judge = true);                  // クエリ発行
        if ($judge) {
            error_log($con->error, 0);
            return false;
        }
        
        return true;
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
        $code = $this->selectCode($post,$con);
        if($code === false){
            return false;
        }
        $sql = "UPDATE progressinfo SET 6CODE = ".$code['6CODE'].", 3CODE = ".$code['3CODE'].", SAGYOUDATE = '" . $post['form_pjpSAGYOUDATE_0'] . "', TEIZITIME = " . $post['form_pjpTEIZITIME_0'] . ", ZANGYOUTIME = " . $post['form_pjpZANGYOUTIME_0'] . " WHERE 7CODE = " . $post['edit_list_id'] . ";";
        $result = $con->query($sql) or ( $judge = true);                  // クエリ発行
        if ($judge) {
            error_log($con->error, 0);
            return false;
        }
        return true;
    }
    
    /**
     * function Delete 
     * PJ進捗削除
     */
    function delete($post,$con){
        $sql = "DELETE FROM progressinfo WHERE 7CODE =" . $post['edit_list_id'] . "";
        $result = $con->query($sql) or ( $judge = true);                  // クエリ発行
        if ($judge) {
            error_log($con->error, 0);
            return false;
        }
        return true;
    }
    
    /**
     * CODE取得
     * @param type $post
     * @param type $con
     */
    function selectCode($post,$con)
    {	
        $code = array();
        $judge = false;
        // 6CODE取得
        $codeSql = pjSelectSQL($post['form_pjpPROJECTNUM_0'],$post['form_pjpEDABAN_0'],$post['form_pjpSTAFFID_0']);
        $resultCode = $con->query($codeSql) or ( $judge = true);                  // クエリ発行
        if ($judge) {
            error_log($con->error, 0);
            return false;
        }
        while($result_row = $resultCode->fetch_array(MYSQLI_ASSOC))
        {
            $code['6CODE'] = $result_row['6CODE'];
        }
        // 工程コード取得
        $kouteiSql = kouteiExistSQL($post['form_pjpKOUTEIID_0'],$post['form_pjpKOUTEINAME_0']);
        $resultKoutei = $con->query($kouteiSql) or ( $judge = true);                  // クエリ発行
        if ($judge) {
            error_log($con->error, 0);
            return false;
        }
        while($result_row = $resultKoutei->fetch_array(MYSQLI_ASSOC))
        {
            $code['3CODE'] = $result_row['3CODE'];
        }
        
        return $code;
    }
}