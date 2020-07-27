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
        
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
        $error = $this->nenjiCheck($_GET['period_0'],$con);
	if(!empty($_SESSION['nenzi']['error']))
	{
		$message = $_SESSION['nenzi']['error'];
		$judge = false;
		unset($_SESSION['nenzi']['error']);
	}
	if(!empty($error[0]['PROJECTNUM']))
	{
		$judge = false;
		$list = $this->makeList_error($error);
	}
	if(!empty($_SESSION['errormonth']))
	{
		$errormonth = $_SESSION['errormonth'];
		$judge = false;
		unset($_SESSION['errormonth']);
	}
	if($judge)
	{
		$_SESSION['nenzi']['checkmessage'] = "aaa";
		echo "<form action='pageJump.php' method='post'>";
		echo "<div class = 'left' id = 'space_button'>　</div>";
		echo "<div><table id = 'button'><tr><td>";
		echo makebutton($filename,'top');
		echo "</td></tr></table></div>";
		echo "</form>";
		echo "<div class = 'center'><br><br>";
		echo "<a class = 'title'>".$title."</a>";
		echo "<br><br>";
		echo "<table><tr>";
		echo "<td class = 'space'></td><td class = 'one'><a class = 'itemname'>年次実行期</a></td><td class = 'two'><a class = 'comp' >".$_SESSION['nenzi']['period']."</a></td></tr>";
		echo "</table>";
	
	}
	else
	{
//		$_SESSION['post'] = $_SESSION['pre_post'];
//		$_SESSION['pre_post'] = null;
		echo "<form action='pageJump.php' method='post'>";
		echo "<div class = 'left' id = 'space_button'>　</div>";
		echo "<div><table id = 'button'><tr><td>";
		echo makebutton($filename,'top');
		echo "</td></tr></table></div>";
		echo "</form>";
		echo "<div class = 'center'><br><br>";
		echo "<a class = 'title'>".$title."</a>";
		echo "</div>";
		echo "<br><br>";
		echo "<center><div>";
		if(!empty($message))
		{
			echo $message;
		}
		else if(!empty($errormonth))
		{
			echo $errormonth."月の月次が完了していません。";
		}
		else if($list != "")
		{
			echo "以下のプロジェクトの終了処理が行われていません。\n期またぎ処理、もしくはプロジェクトの終了処理を完了してから再度年次処理を行ってください。<br><br>";
			echo $list;
		}
		echo "</div><br><br>";
		echo '<form action="main.php" method="post" >';
		echo '<input type="submit" name = "cancel" value = "戻る" class = "free">';
		echo "</div>";
		echo "</form></center>";
	}
        
        //トランザクションコミットまたはロールバック
        commitTransaction($error,$con);
    }

    /*
     * 年次処理(プロジェクト管理システム)
     * function nenjiCheck($period)
     * 
     * 引数1		$month						処理対象月
     * 引数2		$period 					期
     * 引数3		$kubun						0:通常処理	1:年次処理
     * 
     * 戻り値		$form						モーダルに表示リストhtml
     */
    function nenjiCheck($period,$con){

            //------------------------//
            //        初期設定        //
            //------------------------//
            require_once("f_DB.php");																							// DB関数呼び出し準備
            require_once("f_File.php");																							// DB関数呼び出し準備
            $form_ini = parse_ini_file('./ini/form.ini', true);
            $item_ini = parse_ini_file('./ini/item.ini', true);

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $teijitime = (float)$item_ini['settime']['teijitime'];
            $nowdate = date_create("NOW");
            $nowdate = date_format($nowdate, 'Y-n-j');

            //------------------------//
            //          変数          //
            //------------------------//
            $judge = false;
            $monthjudge = false;
            $error_month = "";
            $error_pj = array();
            $endmonth = array();
            $Month = "6,7,8,9,10,11,12,1,2,3,4,5";
            $arrayMonth = explode(',',$Month);
            $checkflgmessage = '';
            $count = 0;

            //------------------------//
            //        検索処理        //
            //------------------------//

            //年次チェック
            $sql = "SELECT * FROM endperiodinfo WHERE PERIOD = '".$period."';";
            $result = $con->query($sql);
            $rows = $result->num_rows;
            if($rows == 0)
            {
                    //月次チェック
                    $sql = "SELECT * FROM endmonthinfo WHERE PERIOD = '".$period."';";
                    $result = $con->query($sql);
                    $rows = $result->num_rows;
                    while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                    {
                            $endmonth[$count] = $result_row['MONTH'];
                            $count++;
                    }
                    for($i = 0; $i < 12; $i++)
                    {
                            for($j = 0; $j < count($endmonth); $j++)
                            {
                                    if($arrayMonth[$i] == $endmonth[$j])
                                    {
                                            $monthjudge = true;
                                    }
                            }
                            if(!$monthjudge)
                            {
                                    //月次を行っていない月を集計
                                    $error_month .= $arrayMonth[$i].',';
                            }
                            $monthjudge = false;
                    }
                    $_SESSION['errormonth'] = rtrim($error_month,',');
                    $count = 0;
                    //PJチェック
                    $start_year = getyear('6',$period);
                    $end_year = $start_year + 1;
                    $sql = "SELECT DISTINCT(EDABAN),PROJECTNUM,PJNAME FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) LEFT JOIN projectinfo USING(5CODE) LEFT JOIN projectnuminfo USING(1CODE) "
                            ."LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) LEFT JOIN kouteiinfo USING(3CODE) WHERE projectinfo.5PJSTAT = 1 AND "
                            ."progressinfo.SAGYOUDATE BETWEEN '".$start_year."-06-01' AND '".$end_year."-05-31' order by PROJECTNUM,EDABAN ;";
                    $result = $con->query($sql);
                    $rows = $result->num_rows;
                    if($rows > 0)
                    {
                            while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                            {
                                    $error_pj[$count]['PROJECTNUM'] = $result_row['PROJECTNUM'];
                                    $error_pj[$count]['EDABAN'] = $result_row['EDABAN'];
                                    $error_pj[$count]['PJNAME'] = $result_row['PJNAME'];
                                    $count++;
                            }
                    }
                    return ($error_pj);
            }
            else
            {
                    $_SESSION['nenzi']['error'] = $period."期は既に年次処理済です。";
            }
    }
    
    /*
     * PJ終了処理(プロジェクト管理システム)
     * function makeList_error($post)
     * 
     * 引数1		$post						削除対象
     * 
     * 戻り値		$form						モーダルに表示リストhtml
     */
    function makeList_error($post){

            //------------------------//
            //        初期設定        //
            //------------------------//
            require_once("f_DB.php");																							// DB関数呼び出し準備
            require_once("f_File.php");																							// DB関数呼び出し準備
            $form_ini = parse_ini_file('./ini/form.ini', true);
            $item_ini = parse_ini_file('./ini/item.ini', true);

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];

            //------------------------//
            //          変数          //
            //------------------------//
            $list_html = "";

            //------------------------//
            //        検索処理        //
            //------------------------//
            if($filename == 'pjend_5' || $filename == 'getuzi_5')
            {
                    $list_html .= "<table class ='list'><thead><tr>";
                    $list_html .="<th><a class ='head'>No</a></th>";
                    $list_html .="<th><a class ='head'>日付</a></th>";
                    $list_html .="<th><a class ='head'>作業者</a></th>";
                    $list_html .="<th><a class ='head'>工程</a></th>";
                    $list_html .="<th><a class ='head'>原因</a></th>";
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
                            $list_html .="<td ".$id." ><a class ='body'>".$post[$i]['SAGYOUDATE']."</a></td>";
                            $list_html .="<td ".$id." ><a class ='body'>".$post[$i]['STAFFNAME']."</a></td>";
                            $list_html .="<td ".$id." ><a class ='body'>".$post[$i]['KOUTEINAME']."</a></td>";
                            $list_html .="<td ".$id." ><a class ='body'>".$post[$i]['GENIN']."</a></td></tr>";
                    }
                    $list_html .="</tbody></table>";
            }
            else
            {
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
            }
            return($list_html);
    }
}