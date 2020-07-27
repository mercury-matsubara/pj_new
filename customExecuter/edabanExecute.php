<?php
require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");
require_once("f_DB.php");

class EdabanInsertExecuteSQL extends BaseLogicExecuter
{
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {
        $judge =false;
        
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
        $result = $this->insertEdaban($this->prContainer->pbFileName, $this->prContainer->pbInputContent,$con);
        
        if($result)
        {
                //枝番抽出
                $sql = "SELECT MAX(2CODE) FROM edabaninfo;";
                $reply = $con->query($sql) or ($judge = true);																		// クエリ発行
                if($judge)
                {
                        error_log($con->error,0);
//                        $judge =false;
                        $result =false;
                }
                while($result_row = $reply->fetch_array(MYSQLI_ASSOC))
                {
                        $code2 = $result_row['MAX(2CODE)'] ;
                }

                $pjnum = $this->prContainer->pbInputContent['form_pjnPROJECT_0'];

                //PJナンバ抽出
                $sql2 = "SELECT * FROM projectnuminfo WHERE PROJECTNUM = '".$pjnum."';";
                $reply2 = $con->query($sql2) or ($judge = true);																		// クエリ発行
                if($judge)
                {
                        error_log($con->error,0);
//                        $judge =false;
                        $result =false;
                }
                while($result_row = $reply2->fetch_array(MYSQLI_ASSOC))
                {
                        $code1 = $result_row['1CODE'] ;
                }

                $charge = $this->prContainer->pbInputContent['form_pjtCHARGE_0'];

                //PJ登録
                $sql3 = "INSERT INTO projectinfo (1CODE,2CODE,CHARGE) VALUES (".$code1.",".$code2.",".$charge.");";
                $reply3 = $con->query($sql3) or ($judge = true);																		// クエリ発行
                if($judge)
                {
                        error_log($con->error,0);
//                        $judge =false;
                        $result =false;
                }

                //トランザクションコミットまたはロールバック
                commitTransaction($result,$con);

                //指定ページへ遷移
                $this->PageJump("EDABANMASTER_2", $edit['edit_list_id'], 2,"","");
        }
    }
    
    function insertEdaban($filename, &$post,&$con)
    {
        $judge = false;
        
        $insert_SQL = "INSERT INTO edabaninfo (EDABAN,PJNAME) VALUE ('".$post['form_edaEDABAN_0']."','".$post['form_edaPJNAME_0']."');";
        
        $result = $con->query($insert_SQL) or ($judge = true);																		// クエリ発行
	
	if(!$result)
	{
		error_log($con->error,0);
		$judge =false;
	}
	////////////////////操作履歴///////////////////////
	addSousarireki($filename, STEP_INSERT, $insert_SQL, $con);
	////////////////////操作履歴///////////////////////
        
        return $result;
    }
}