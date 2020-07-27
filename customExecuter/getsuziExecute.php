<?php
class GetsuziExecuteSQL extends BaseLogicExecuter
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

            $message = $this->getuzi($_GET['month_0'],$_GET['period_0'],$con);
            
            if($message === true)
            {
                    //トランザクションコミットまたはロールバック
                    commitTransaction($message, $con);
                    
                    $id = 0;
                    $step = 0;
                    $message = "";
//                    echo "<center>";
//                    echo "<a class = 'title'>月次処理完了</a>";
//                    echo "<br><br><a>月次処理が完了しました。 </a>";
//                    echo "<table><tr>";
//                    echo "<td class = 'space'></td><td class = 'one'><a class = 'itemname'>月次実行月</a></td><td class = 'two'><a class = 'comp' >".$_SESSION['getuji']['period']."期　".$_SESSION['getuji']['month']."月</a></td></tr>";
//                    echo "</table>";
//                    echo "<br><br>";
//                    echo "<form action='pageJump.php' method='post'>";
//                    echo "<div class = 'left' id = 'space_button'>　</div>";
//                    echo "<div><table id = 'button'><tr><td>";
//                    echo makebutton($filename,'top');
//                    echo "</td></tr></table></div>";
//                    echo "</form>";
//                    echo "</center>";
            }
            else
            {
                    $id = 0;
                    $step = 0;
//                    if($message == '月次処理にてエラーが発生しました。')
//                    {
//                            $list = $this->makeList_error($_SESSION['error']);
//                    }
//
//                    echo "<div class = 'center'><br><br>";
//                    echo "<a class = 'title'>月次処理エラー</a>";
//                    echo "</div>";
//                    echo "<br><br>";
//                    echo "<center><div>";
//                    echo $message;
//                    if($list != "")
//                    {
//                            echo $list;
//                    }
//                    echo "</div><br><br>";
//                    echo "<form action='pageJump.php' method='post'>";
//                    echo "<div class = 'left' id = 'space_button'>　</div>";
//                    echo "<div><table id = 'button'><tr><td>";
//                    echo makebutton($filename,'top');
//                    echo "</td></tr></table></div>";
//                    echo "</form>";
            }
            
            //トランザクションコミットまたはロールバック
            commitTransaction($message,$con);
            
            $this->PageJump($filename,$id,$step,"","",$message);
    }
    
    /*
     * 月次処理(プロジェクト管理システム)
     * function getuji($month,$period,$kubun)
     * 
     * 引数1		$month						処理対象月
     * 引数2		$period 					期
     * 引数3		$kubun						0:通常処理	1:年次処理
     * 戻り値		$form						モーダルに表示リストhtml
     */
    function getuzi($month,$period,$con){

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


            //------------------------//
            //          変数          //
            //------------------------//
            $judge = false;
            $endjudge = false;
            $checkflg = false;
            $insertArray = array();
            $syaincnt = 0;
            $syainArray = array();
            $time = array();
            $cnt = 0;
            $syaincnt = 0;
            $errorcnt = 0;
            $year = getyear($month,$period);
            $lastday = getlastday($month,$year);
            $Month = str_pad($month, 2, "0", STR_PAD_LEFT);

            //------------------------//
            //        検索処理        //
            //------------------------//

            //月次済判定																								// db接続関数実行
            $sql = "SELECT * FROM endmonthinfo WHERE PERIOD = ".$period." AND MONTH = ".$month.";";
            $result = $con->query($sql);
            $rows = $result->num_rows;
            if($rows > 0)
            {
                    $endjudge = true;
            }
            if(!$endjudge)
            {
                    //指定期間内に登録されている社員コード取得
                    $sql = "SELECT DISTINCT(4CODE) FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) LEFT JOIN projectinfo USING(5CODE) "
                                    ."LEFT JOIN projectnuminfo USING(1CODE) LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) "
                                    ."LEFT JOIN kouteiinfo USING(3CODE) WHERE progressinfo.SAGYOUDATE BETWEEN '"
                                    .$year."-".$month."-1' AND '".$year."-".$month."-".$lastday."' ORDER BY syaininfo.4CODE;";
                    $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                    while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                    {
                            $syainArray[$syaincnt] = $result_row['4CODE'];
                            $syaincnt++;
                    }

                    //定時チェック
                    for($s = 0; $s < count($syainArray); $s++)
                    {
                            //初期化
                            $before = "";
                            $teizi = 0;
                            $zangyou = 0;

                            //社員コードと日付を条件に作業日順で選択
                            $sql = "SELECT * FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) LEFT JOIN projectinfo USING(5CODE) "
                                            ."LEFT JOIN projectnuminfo USING(1CODE) LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) "
                                            ."LEFT JOIN kouteiinfo USING(3CODE) WHERE progressinfo.SAGYOUDATE BETWEEN '"
                                            .$year."-".$month."-1' AND '".$year."-".$month."-".$lastday."' AND syaininfo.4CODE = ".$syainArray[$s]." ORDER BY SAGYOUDATE;";
                            $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                            if($judge)
                            {
                                    error_log($con->error,0);
                                    $judge = false;
                                    $checkflg = false;
                            }
                            while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                            {
                                    $after = $result_row['SAGYOUDATE'];
                                    if(isset($before))
                                    {
                                            if($before == $after)
                                            {

                                                    $teizi += $result_row['TEIZITIME'];
                                                    if($teizi > $teijitime)
                                                    {
                                                            $checkflg = true;
                                                            //定時エラー//
                                                            $errrecname = $result_row['STAFFNAME'];
                                                            $errrecdate = $result_row['SAGYOUDATE'];
                                                            $error[$errorcnt]['STAFFNAME'] = $errrecname;
                                                            $error[$errorcnt]['SAGYOUDATE'] = $errrecdate;
                                                            $error[$errorcnt]['KOUTEINAME'] = "";
                                                            $error[$errorcnt]['GENIN'] = "規定の定時時間を越えています。";
                                                    }
                                            }
                                            else
                                            {
                                                    //日付が変わるごとにteiziを初期化
                                                    $teizi = 0;
                                                    $teizi += $result_row['TEIZITIME'];
                                                    if($teizi > $teijitime)
                                                    {
                                                            $checkflg = true;
                                                            //定時エラー//
                                                            $errrecname = $result_row['STAFFNAME'];
                                                            $errrecdate = $result_row['SAGYOUDATE'];
                                                            $error[$errorcnt]['STAFFNAME'] = $errrecname;
                                                            $error[$errorcnt]['SAGYOUDATE'] = $errrecdate;
                                                            $error[$errorcnt]['KOUTEINAME'] = "";
                                                            $error[$errorcnt]['GENIN'] = "規定の定時時間を越えています。";
                                                    }
                                            }
                                    }
                                    else
                                    {
                                            $teizi += $result_row['TEIZITIME'];
                                            if($teizi > $teijitime)
                                                    {
                                                            $checkflg = true;
                                                            //定時エラー//
                                                            $errrecname = $result_row['STAFFNAME'];
                                                            $errrecdate = $result_row['SAGYOUDATE'];
                                                            $error[$errorcnt]['STAFFNAME'] = $errrecname;
                                                            $error[$errorcnt]['SAGYOUDATE'] = $errrecdate;
                                                            $error[$errorcnt]['KOUTEINAME'] = "";
                                                            $error[$errorcnt]['GENIN'] = "規定の定時時間を越えています。";
                                                    }
                                    }
                                    $before = $result_row['SAGYOUDATE'];
                            }
                    }

                    //実績計算
                    if(!$checkflg)
                    {
                            //指定期間中のレコードを作業日順に選択
                            $sql = "SELECT * FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) LEFT JOIN projectinfo USING(5CODE) "
                                            ."LEFT JOIN projectnuminfo USING(1CODE) LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) "
                                            ."LEFT JOIN kouteiinfo USING(3CODE) WHERE progressinfo.SAGYOUDATE BETWEEN '"
                                            .$year."-".$Month."-1' AND '".$year."-".$Month."-".$lastday."' ORDER BY SAGYOUDATE;";
                            $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                            if($judge)
                            {
                                    error_log($con->error,0);
                                    $judge = false;
                                    $checkflg = false;
                            }
                            while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                            {
                                    //社員別プロジェクトコード(6CODE)ごとに多配列登録
                                    if(isset($time[$result_row['6CODE']]))
                                    {
                                            $time[$result_row['6CODE']][count($time[$result_row['6CODE']])] = $result_row;
                                    }
                                    else
                                    {
                                            $time[$result_row['6CODE']][0] = $result_row;
                                    }
                            }
                            $keyarray = array_keys($time);
    //			$checkflg = false;
    //			$delcheckflg = false;
                            foreach($keyarray as $key)
                            {
                                    //初期化
                                    $teizi = 0;
                                    $zangyou = 0;
    //				$teizicheck = 0;
                                    unset($before);
    //				$checkkouteiarray = array();

                                    //登録データ格納
                                    $insertArray[$cnt]['4CODE'] = $time[$key][0]['4CODE'];
                                    $insertArray[$cnt]['5CODE'] = $time[$key][0]['5CODE'];
                                    $insertArray[$cnt]['PROJECTNUM'] = $time[$key][0]['PROJECTNUM'];
                                    $insertArray[$cnt]['EDABAN'] = $time[$key][0]['EDABAN'];
                                    $insertArray[$cnt]['PJNAME'] = $time[$key][0]['PJNAME'];
                                    //社員別プロジェクトコードごとに実績計算
                                    for($i = 0 ; $i < count($time[$key]) ; $i++)
                                    {

    /*					$after = $time[$key][$i]['SAGYOUDATE'];
                                            if(isset($before))
                                            {
                                                    if($before == $after)
                                                    {
                                                            $teizicheck += $time[$key][$i]['TEIZITIME'];
                                                            if($teizicheck > $teijitime)
                                                            {
                                                                    $checkflg = true;
                                                                    //定時エラー//
                                                                    $errrecname = $time[$key][$i]['STAFFNAME'];
                                                                    $errrecdate = $time[$key][$i]['SAGYOUDATE'];
                                                                    $error[$errorcnt]['STAFFNAME'] = $errrecname;
                                                                    $error[$errorcnt]['SAGYOUDATE'] = $errrecdate;
                                                                    $error[$errorcnt]['KOUTEINAME'] = "";
                                                                    $error[$errorcnt]['GENIN'] = "規定の定時時間を越えています。";
                                                            }
                                                            if(array_search($time[$key][$i]['KOUTEINAME'],$checkkouteiarray) !== FALSE)
                                                            {
                                                                    $checkflg = true;
                                                                    //同一レコードエラー//
                                                                    $errrecname = $time[$key][$i]['STAFFNAME'];
                                                                    $errrecdate = $time[$key][$i]['SAGYOUDATE'];
                                                                    $error[$errorcnt]['STAFFNAME'] = $errrecname;
                                                                    $error[$errorcnt]['SAGYOUDATE'] = $errrecdate;
                                                                    $error[$errorcnt]['KOUTEINAME'] = $time[$key][$i]['KOUTEINAME'];
                                                                    $error[$errorcnt]['GENIN'] = "同一工程のレコードが存在します。";
                                                                    $checkstack = array_search($time[$key][$i]['KOUTEINAME'],$checkkouteiarray);
                                                                    $checkkouteiarray[$checkstack] = '';
                                                            }
                                                    }
                                                    else
                                                    {
                                                            $teizicheck = 0;
                                                            $teizicheck += $time[$key][$i]['TEIZITIME'];
                                                            if($teizicheck > $teijitime)
                                                            {
                                                                    $checkflg = true;
                                                                    //定時エラー//
                                                                    $errrecname = $time[$key][$i]['STAFFNAME'];
                                                                    $errrecdate = $time[$key][$i]['SAGYOUDATE'];
                                                                    $error[$errorcnt]['STAFFNAME'] = $errrecname;
                                                                    $error[$errorcnt]['SAGYOUDATE'] = $errrecdate;
                                                                    $error[$errorcnt]['KOUTEINAME'] = "";
                                                                    $error[$errorcnt]['GENIN'] = "規定の定時時間を越えています。";
                                                            }
                                                            $checkkouteiarray = array();
                                                    }
                                            }
                                            else
                                            {
                                                    $teizicheck += $time[$key][$i]['TEIZITIME'];
                                                    if($teizicheck > $teijitime)
                                                    {
                                                            $checkflg = true;
                                                            //定時エラー//
                                                            $errrecname = $time[$key][$i]['STAFFNAME'];
                                                            $errrecdate = $time[$key][$i]['SAGYOUDATE'];
                                                            $error[$errorcnt]['STAFFNAME'] = $errrecname;
                                                            $error[$errorcnt]['SAGYOUDATE'] = $errrecdate;
                                                            $error[$errorcnt]['KOUTEINAME'] = "";
                                                            $error[$errorcnt]['GENIN'] = "規定の定時時間を越えています。";
                                                    }
                                            }
    */					//一ヵ月分の実績データ計算
                                            $teizi += $time[$key][$i]['TEIZITIME'];
                                            $zangyou += $time[$key][$i]['ZANGYOUTIME'];
                                            $before = $time[$key][$i]['SAGYOUDATE'];
    //					$checkkouteiarray[] = $time[$key][$i]['KOUTEINAME'];
                                    }
                                    //一ヵ月分の実績データ作成
                                    $insertArray[$cnt]['TEIZI'] = $teizi;
                                    $insertArray[$cnt]['ZANGYOU'] = $zangyou;
                                    $cnt++;
                            }
                    }
                    //月間実績登録
                    if(!$checkflg)
                    {
                            for($i = 0; $i < count($insertArray); $i++)
                            {
                                    $sql_month = "INSERT INTO monthdatainfo (4CODE,5CODE,PERIOD,MONTH,ITEM,VALUE,9ENDDATE,PROJECTNUM,EDABAN,PJNAME) VALUES"
                                                            ." (".$insertArray[$i]['4CODE'].",".$insertArray[$i]['5CODE'].",'".$period."','".$month."','定時時間','".$insertArray[$i]['TEIZI']."'"
                                                            .",NOW(),"."'".$insertArray[$i]['PROJECTNUM']."'".","."'".$insertArray[$i]['EDABAN']."'".","."'".$insertArray[$i]['PJNAME']."'".");";
                                    $result = $con->query($sql_month) or ($judge = true);																		// クエリ発行
                                    if($judge)
                                    {
                                            error_log($con->error,0);
                                            $judge = false;
                                    }
                                    $sql_month = "INSERT INTO monthdatainfo (4CODE,5CODE,PERIOD,MONTH,ITEM,VALUE,9ENDDATE,PROJECTNUM,EDABAN,PJNAME) VALUES"
                                                            ." (".$insertArray[$i]['4CODE'].",".$insertArray[$i]['5CODE'].",'".$period."','".$month."','残業時間','".$insertArray[$i]['ZANGYOU']."'"
                                                            .",NOW(),"."'".$insertArray[$i]['PROJECTNUM']."'".","."'".$insertArray[$i]['EDABAN']."'".","."'".$insertArray[$i]['PJNAME']."'".");";
                                    $result = $con->query($sql_month) or ($judge = true);																		// クエリ発行
                                    if($judge)
                                    {
                                            error_log($con->error,0);
                                            $judge = false;
                                    }
                            }
                    }
            }
            else
            {
                    $checkflg = true;
                    $message = '既に処理済の期月です。';
            }
            if(!$checkflg)
            {
                    //月次済期間登録
                    $year = getyear($month,$period);
                    $sql = "INSERT INTO endmonthinfo (PERIOD,YEAR,MONTH) VALUE ('".$period."','".$year."','".$month."');";
                    $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                    if($judge)
                    {
                            error_log($con->error,0);
                            $judge = false;
                    }
                    deletedate_change();
                    $message = true;
                    return $message;
            }
            else
            {
                    if(!empty($error))
                    {
                            $_SESSION['error'] = $error;
                            $message = '月次処理にてエラーが発生しました。';
                    }
                    return($message);
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
