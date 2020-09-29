<?php
/**
 * 工数入力処理
 */
class kousuExecute extends BaseLogicExecuter
{
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {
        $progresscode = array();
        $kouteicode = array();
        // トランザクション開始
        $con = beginTransaction();
        // 登録日付
        $date = $this->prContainer->pbInputContent['date'];
        // 存在値チェック
        for ($i = 0; $i < 10; $i++){
            $pronum = $this->prContainer->pbInputContent['form_topPROJECTNUM_'.$i];
            $eda = $this->prContainer->pbInputContent['form_topEDABAN_'.$i];
            $id = $this->prContainer->pbInputContent['form_topKOUTEIID_'.$i];
            $name = $this->prContainer->pbInputContent['form_topKOUTEINAME_'.$i];
            $teizi = $this->prContainer->pbInputContent['form_topTEIZITIME_'.$i];
            $zangyo = $this->prContainer->pbInputContent['form_topZANGYOUTIME_'.$i];
            // 工数チェック
            $kousu = $this->kousuExistCheck($con,$pronum,$eda);
            if($kousu !== false){
                $progresscode[$i] = $kousu;
            }
            // 工程チェック
            $koutei = $this->kouteiExistCheck($con,$id,$name);
            if($koutei !== false){
                $kouteicode[$i] = $koutei;
            }
            
            $result = $this->kousuDelete($progresscode[$i],$kouteicode[$i]);
            
            
        }
        

        
        //トランザクションコミットまたはロールバック
	commitTransaction(false,$con);
        
    }
    

    /**
     * 工数存在値チェック
     * @param type $con
     * @param type $post
     */
    function kousuExistCheck($con,$pronum,$eda)
    {
        $code = "";
        if($pronum === "" || $eda === "")
        {
            return $code;
        }
        $sql = kousuExistSQL($pronum, $eda,$_SESSION['4CODE']);
        $result = $con->query($sql);// クエリ発行
	if (!$result) {
            error_log($con->error, 0);
            return false;
        }
        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $code = $result_row['6CODE'];
        }
        return $code;
    }
    /**
     * 工程存在値チェック
     * @param type $con
     * @param type $post
     */
    function kouteiExistCheck($con,$id,$name)
    {
        $koutei = "";
        if($id === "" || $name === "")
        {
            return $koutei;
        }
        $sql = kouteiExistSQL($id, $name);
        $result = $con->query($sql);// クエリ発行
	if (!$result) {
            error_log($con->error, 0);
            return false;
        }
        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $koutei = $result_row['3CODE'];
        }
        return $koutei;
    }
    
    /**
     * 工数削除
     */
    function kousuDelete(){
        
    }
    
    /**
     * 工数登録
     */
    function kousuInsert(){
        
    }
}


