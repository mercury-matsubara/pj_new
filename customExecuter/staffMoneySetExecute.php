<?php
class StaffMoneySetExecuteSQL extends BaseLogicExecuter
{
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {
            $filename = "PJICHIRAN_2";
            $post = $_POST;
            //DB接続、トランザクション開始
            $con = beginTransaction();
            
            if($this->prContainer->pbInputContent['Comp'] === "del")
            {
                $message = $this->pjdelete($post,$con);
            }
            else if($this->prContainer->pbInputContent['Comp'] === "set")
            {
                $message = $this->moneySet($post,$con);
            }
            
            //トランザクションコミットまたはロールバック
            commitTransaction($message,$con);   
            
            $id = 0;
            $step = 0;
            $this->PageJump($filename,$id,$step,"","");
    }
    /*
     * function pjdelete($post,$con)
     * 
     * 引数1		$post								入力内容
     * 引数2		$data								登録ファイル内容
     * 
     * 戻り値	なし
     */
    function pjdelete($post,$con){

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

            //------------------------//
            //          変数          //
            //------------------------//
            $sql = "";
            $judge = false;

            //------------------------//
            //          処理          //
            //------------------------//																							// db接続関数実行
            $id = $post;
            //プロジェクト削除
            $sql = "DELETE FROM projectinfo WHERE 5CODE = ".$id.";";
            $result = $con->query($sql) or ($judge = true);																		// クエリ発行
            if($judge)
            {
                    error_log($con->error,0);
                    $judge = false;
            }

                $sql = "SELECT * FROM projectditealinfo WHERE 5CODE = ".$id.";";
                $result = $con->query($sql) or ($judge = true);
                while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                {
                        $code .= $result_row['6CODE'].',';
                }
                
                if(isset($code))
                {
                    $code = rtrim($code,',');
                    $sql = "DELETE FROM progressinfo WHERE 6CODE IN (".$code.");";
                    $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                    if($judge)
                    {
                            error_log($con->error,0);
                            $judge = false;
                    }
                }

            //社員別プロジェクト削除
            $sql = "DELETE FROM projectditealinfo WHERE 5CODE = ".$id.";";
            $result = $con->query($sql) or ($judge = true);																		// クエリ発行
            if($judge)
            {
                    error_log($con->error,0);
                    $judge = false;
            }
            return true;
    }
    /*
     * 社員別金額設定
     */
    function moneySet($post,$con)
    {
        $CODE5 = $post['5CODE'];
        $keyarray = array_keys($post);
	foreach($keyarray as $key)
	{
		if (strstr($key, 'money'))
		{
			$name_arrsy = explode('_',$key);
			$CODE4 = $name_arrsy[1];
			$row_num = 0 ;
			$judge = false;
			$SQL = "SELECT COUNT(*) FROM projectditealinfo WHERE 4CODE = ".$CODE4." AND 5CODE = ".$CODE5." ;";
			$result = $con->query($SQL) or ($judge = true);																	// クエリ発行
			if($judge)
			{
				error_log($con->error,0);
				$judge = false;
			}
			while($result_row = $result->fetch_array(MYSQLI_ASSOC))
			{
				$row_num = $result_row['COUNT(*)'];
			}
			if($row_num == 0 && $post[$key] != '')
			{
				$judge = false;
				$SQL = "INSERT INTO projectditealinfo (4CODE,5CODE,DETALECHARGE) VALUES(".$CODE4.",".$CODE5.",".$post[$key].");";
				$con->query($SQL) or ($judge = true);																	// クエリ発行
				if($judge)
				{
					error_log($con->error,0);
					$judge = false;
				}
			}
			else if($row_num == 1)
			{
				$judge = false;
				if($post[$key] == '')
				{
					$post[$key] = 0;
				}
				$SQL = "UPDATE projectditealinfo SET DETALECHARGE = ".$post[$key]." WHERE 4CODE = ".$CODE4." AND 5CODE  = ".$CODE5." ;";
				$con->query($SQL) or ($judge = true);																	// クエリ発行
				if($judge)
				{
					error_log($con->error,0);
					$judge = false;
				}
			}
		}
		else if($key == 'charge')
		{
			$judge = false;
			$SQL = "UPDATE projectinfo SET CHARGE = ".$post['charge']." WHERE  5CODE  = ".$CODE5." ;";
			$con->query($SQL) or ($judge = true);																	// クエリ発行
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