<?php

class NenziPjExecute extends BaseLogicExecuter
{
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {
        $filename = $_SESSION['filename'];
        $code = [];
        //DB接続、トランザクション開始
        $con = beginTransaction();
        $code[0] = $this->prContainer->pbInputContent['be5CODE'];
        $flag = true;

        $message = $this->pjend($code,$con);
        if($message[0]){
            // 1CODE取得
            $pjCode = ProjectNumSQL($con,$this->prContainer->pbInputContent['form_pjtPROJECTNUM_0'],$this->prContainer->pbInputContent['form_pjtPROJECTNAME_0']);
            if($pjCode == ''){
                $flag = false;
                $message[1] = "次期PJ登録処理にてエラーが発生しました。<br>";
            }
            // 2CODE取得
            $edaCode = EdabanCODESQL($con,$this->prContainer->pbInputContent['form_pjtEDABAN_0'],$this->prContainer->pbInputContent['form_pjtPJNAME_0']);
            if($edaCode == ''){
                $flag = false;
                $message[1] = "次期PJ登録処理にてエラーが発生しました。<br>";
            }
            // 追加プロジェクト金額
            $addcharge = $this->prContainer->pbInputContent['form_pjtCHARGE_0'];
            // 前のプロジェクト金額
            $nowcharge = $this->prContainer->pbInputContent['beCHARGE'];
            $sql = "INSERT INTO projectinfo (1CODE,2CODE,CHARGE,5PJSTAT) VALUES (".$pjCode.",".$edaCode.",".$addcharge.",1) ;";
            $result = $con->query($sql) or ( $judge = true);                  //クエリ発行
            if ($judge) {
                error_log($con->error, 0);
                $judge = false;
                $flag = false;
                $message[1] = "次期PJ登録処理にてエラーが発生しました。<br>";
            }

            if ($flag) {
                //もちこし金額分を差し引く
                $sql = "UPDATE projectinfo SET CHARGE =  " . ($nowcharge - $addcharge) . " WHERE 5CODE = " . $code[0] . " ;";
                $result = $con->query($sql) or ( $judge = true);                  //クエリ発行
                if ($judge) {
                    error_log($con->error, 0);
                    $judge = false;
                    $flag = false;
                    $message[1] = "前期PJ更新処理にてエラーが発生しました。<br>";
                }
            }
            
        }
        //トランザクションコミットまたはロールバック
        commitTransaction($flag,$con);

        $id = 0;
        $step = 0;
        if($flag)
        {
            $filename = "NENZIPERIOD_2";
            $this->PageJump($filename,$id,$step,"","");                      
        }
        else
        {
            $this->PageJump($filename,$id,$step,"","",$message);
        }       
    }
    
}