<?php
class NenziExecuteSQL extends BaseLogicExecuter
{
    /**
     * executeSQL
     * データ操作の実行
     */
    public function executeSQL()
    {
        $filename = $_SESSION['filename'];
        $title = "年次処理";
        $period = $_GET['period_0'];
        $judge = true;
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
        $error = $this->nenjiCheck($period,$con);
	if(isset($_SESSION['error']))
	{
            $message = $_SESSION['error'];
            $judge = false;
            unset($_SESSION['error']);
	}
	if(!empty($error[0]['PROJECTNUM']))
	{
            $judge = false;
            $message = "以下のプロジェクトの終了処理が行われていません。\n期またぎ処理、もしくはプロジェクトの終了処理を完了してから再度年次処理を行ってください。<br><br>";
            $message .= $this->makeList_error($error);
	}
	if(!empty($_SESSION['errormonth']))
	{
            $message = $_SESSION['errormonth']."月の月次が完了していません。";
            $judge = false;
            unset($_SESSION['errormonth']);
	}
        
	if($judge)
	{
	    $sql = "INSERT INTO endperiodinfo (PERIOD) VALUE ('".$period."');";
            $con->query($sql) or ($judge2 = true);																		// クエリ発行
            if($judge2)
            {
                error_log($con->error,0);
                $judge = false;
            }
            deletedate_change();
	}
	else
	{
            $judge = false;
	}
        
        //トランザクションコミットまたはロールバック
        commitTransaction($judge,$con);
        
        $this->PageJump($filename, STEP_NONE, STEP_NONE, "", "",$message);
    }

    /************************************************************************************************************
    PJ終了処理(プロジェクト管理システム)
    function makeList_error($post)

    引数1		$post						削除対象

    戻り値		$form						モーダルに表示リストhtml
    ************************************************************************************************************/
    function makeList_error($post){


            //------------------------//
            //          変数          //
            //------------------------//
            $list_html = "";

            //------------------------//
            //        検索処理        //
            //------------------------//
            $list_html .= "<table class ='list'><thead><tr>";
            $list_html .="<th><a class ='head'>No</a></th>";
            $list_html .="<th><a class ='head'>プロジェクトコード</a></th>";
            $list_html .="<th><a class ='head'>枝番コード</a></th>";
            $list_html .="<th><a class ='head'>製番・案件名</a></th>";
            $list_html .="</tr><thead><tbody>";

            for($i = 0; $i < count($post); $i++)
            {
                    $list_html .="<tr>";
                    if(($i%2) == 0)
                    {
                            $id = "";
                    }
                    else
                    {
                            $id = "id = 'stripe'";
                    }

                    $list_html .="<td ".$id." class = 'center'><a class='body'>".($i + 1)."</a></td>";
                    $list_html .="<td ".$id." ><a class ='body'>".$post[$i]['PROJECTNUM']."</a></td>";
                    $list_html .="<td ".$id." ><a class ='body'>".$post[$i]['EDABAN']."</a></td>";
                    $list_html .="<td ".$id." ><a class ='body'>".$post[$i]['PJNAME']."</a></td>";
            }
            $list_html .="</tbody></table>";
            
            return($list_html);
    }

}