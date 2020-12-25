<?php

class PjendExecuteSQL extends BaseLogicExecuter
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

            for($i=0;$i<$_GET['pjendCount'];$i++)
            {
                $code[$i] = str_replace('check_','',$_GET['pjend'.$i]);
            }
            $message = $this->pjend($code,$con);
            //トランザクションコミットまたはロールバック
            commitTransaction($message,$con);
            
            $id = 0;
            $step = 0;
            if($message[0])
            {       
                $this->PageJump($filename,$id,$step,"","");                      
            }
            else
            {
                $this->PageJump($filename,$id,$step,"","",$message);
            }       
    }
    
}