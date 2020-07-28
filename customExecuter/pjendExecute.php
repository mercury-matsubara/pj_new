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
            if($message)
            {       
                $this->PageJump($filename,$id,$step,"","");                      
            }
            else
            {
                $this->PageJump($filename,$id,$step,"","",$message);
            }       
    }
    /*
     * PJ終了処理
     * 
     * 引数1		$post						削除対象
     * 戻り値		$form						モーダルに表示リストhtml
     */
    function pjend($code,$con){

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
            $pjid = $code;
            $nowdate = date_create("NOW");
            $nowdate = date_format($nowdate, 'Y-n-j');
            $teijitime = (float)$item_ini['settime']['teijitime'];
            $count = $_GET['pjendCount'];

            //------------------------//
            //          変数          //
            //------------------------//
            $judge = false;
            $time = array();
            $teizi = 0;
            $zangyou = 0;
            $charge = 0;
            $period = 0;
            $upcode6 = "";
            $errorcnt = 0;
            $syaincnt = 0;
            $error = array();
            $syainArray = array();
            $checkflg = false;

            //------------------------//
            //      定時チェック      //
            //------------------------//																								// db接続関数実行

            for($i=0;$i<$count;$i++)
            {
                //プロジェクトの開始日と終了日取得
                $sql = "SELECT MIN(SAGYOUDATE),MAX(SAGYOUDATE) FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) "
                                ."LEFT JOIN projectinfo USING(5CODE) LEFT JOIN projectnuminfo USING(1CODE) "
                                ."LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) "
                                ."LEFT JOIN kouteiinfo USING(3CODE) WHERE 5CODE = ".$pjid[$i]." order by SAGYOUDATE ;";

                $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                $result_row = $result->fetch_array(MYSQLI_ASSOC);
                $start = $result_row['MIN(SAGYOUDATE)'];
                $end =  $result_row['MAX(SAGYOUDATE)'];

                //プロジェクトの作業社員取得
                $sql = "SELECT DISTINCT(4CODE) FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) "
                                ."LEFT JOIN projectinfo USING(5CODE) LEFT JOIN projectnuminfo USING(1CODE) "
                                ."LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) "
                                ."LEFT JOIN kouteiinfo USING(3CODE) WHERE 5CODE = ".$pjid[$i]." order by 4CODE ;";
                $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                {
                        $syainArray[$syaincnt] = $result_row['4CODE'];
                        $syaincnt++;
                }

                //社員ごとに定時チェック
                for($s = 0; $s < count($syainArray); $s++)
                {
                        //社員が変わるごとにbeforeとteiziを初期化
                        $before = "";
                        $teizi = 0;

                        $sql = "SELECT * FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) LEFT JOIN projectinfo USING(5CODE) "
                                        ."LEFT JOIN projectnuminfo USING(1CODE) LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) "
                                        ."LEFT JOIN kouteiinfo USING(3CODE) WHERE progressinfo.SAGYOUDATE BETWEEN '".$start."' AND '".$end."' AND syaininfo.4CODE = ".$syainArray[$s]." ORDER BY SAGYOUDATE;";

                        $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                        if($judge)
                        {
                                error_log($con->error,0);
                                $judge = false;
                        }
                        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                        {
                                $after = $result_row['SAGYOUDATE'];
                                if(!empty($before))
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
                                                        $errorcnt++;
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
                                                        $errorcnt++;
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
                                                $errorcnt++;
                                        }
                                }
                                $before = $result_row['SAGYOUDATE'];
                        }
                }

//                $_SESSION['error'];
                //------------------------//
                //      終了登録処理      //
                //------------------------//

                if(!$checkflg)
                {
                        //該当プロジェクト($pjid)を選択
                        $sql = "SELECT * FROM progressinfo LEFT JOIN projectditealinfo USING(6CODE) LEFT JOIN projectinfo USING(5CODE) "
                                        ."LEFT JOIN projectnuminfo USING(1CODE) LEFT JOIN syaininfo USING(4CODE) LEFT JOIN edabaninfo USING(2CODE) "
                                        ."LEFT JOIN kouteiinfo USING(3CODE) WHERE projectditealinfo.5CODE = ".$pjid[$i]." order by SAGYOUDATE ;";
                        $result = $con->query($sql) or ($judge = true);																		// クエリ発行
                        if($judge)
                        {
                                error_log($con->error,0);
                                $judge = false;
                        }
                        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
                        {
                                //社員別プロジェクトコード(6CODE)ごとに多次元配列に格納
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
                        foreach($keyarray as $key)
                        {
                                //$key(=6CODE)が変わるごとに初期化
                                $teizi = 0;
                                $zangyou = 0;
                                unset($before);
                                //実績時間計算
                                for($j = 0 ; $j < count($time[$key]) ; $j++)
                                {
                                        $teizi += $time[$key][$j]['TEIZITIME'];
                                        $zangyou += $time[$key][$j]['ZANGYOUTIME'];
                                }
                                //終了PJ登録
                                $pjnum = $time[$key][0]['PROJECTNUM'];
                                $pjeda = $time[$key][0]['EDABAN'];
                                $pjname = $time[$key][0]['PJNAME'];
                                $charge = $time[$key][0]['DETALECHARGE'];
                                $total = $teizi + $zangyou;
                                $performance = round($charge/$total,3);
                                $sql_end = "INSERT INTO endpjinfo (6CODE,TEIJITIME,ZANGYOTIME,TOTALTIME,PERFORMANCE,8ENDDATE,PROJECTNUM,EDABAN,PJNAME) VALUES "
                                                        ."(".$key.",".$teizi.",".$zangyou.",".$total.",".$performance.","."'".$nowdate."'".","."'".$pjnum."'".","."'".$pjeda."'".","."'".$pjname."'".") ;";
                                $result = $con->query($sql_end) or ($judge = true);																		// クエリ発行
                                if($judge)
                                {
                                        error_log($con->error,0);
                                        $judge = false;
                                }
                                if(!empty($upcode6))
                                {
                                        $upcode6 .= $key.",";
                                }
                                else
                                {
                                        $upcode6 = $key.",";
                                }
                        }
                        //フラグを終了PJ(STAT=2)に更新
                        $sql_update = "UPDATE projectinfo SET  5ENDDATE = '".$nowdate."' , 5PJSTAT = '2' WHERE 5CODE = ".$pjid[$i]." ;";
                        $result = $con->query($sql_update) or ($judge = true);																		// クエリ発行
                        if($judge)
                        {
                                error_log($con->error,0);
                                $judge = false;
                        }

                        $upcode6 = substr($upcode6, 0, -1);
                        $sql_update = "UPDATE projectditealinfo SET 6ENDDATE = '".$nowdate."' , 6PJSTAT = '2' WHERE 6CODE IN (".$upcode6.");";
                        $result = $con->query($sql_update) or ($judge = true);																		// クエリ発行
                        if($judge)
                        {
                                error_log($con->error,0);
                                $judge = false;
                        }
                        $sql_update = "UPDATE progressinfo SET 7ENDDATE = '".$nowdate."' , 7PJSTAT = '2' WHERE 6CODE IN (".$upcode6.");";
                        $result = $con->query($sql_update) or ($judge = true);																		// クエリ発行
                        if($judge)
                        {
                                error_log($con->error,0);
                                $judge = false;
                        }
                }
                if(!$checkflg)
                {
                        $message[] = true;
                        $message[] = $pjid[$i];
                }
                else
                {
                        $message[] = false;
                        $message[] = $pjid[$i];
                        return($message);
                }
            }
            return($message);
    }
}