<?php

//require_once("classesBase.php");
/**
 * ビジネスロジック層用のクラス
 * データ処理
 */
class BaseLogicExecuter extends BasePage
{
	/**
	 * executeSQL
	 * データ操作の実行
	 */
	public function executeSQL()
	{
                // fileneme、id、codeを初期化
                $filename = "";
                $id = "";
                $code = "";
            
		//処理判定変数
		$step = $this->prContainer->pbStep;
		
		//DB接続、トランザクション開始
		$con = beginTransaction();
                
                $filename = $this->prContainer->pbFileName;
                $form_ini = parse_ini_file('./ini/form.ini', true);
                
                if(isset($this->prContainer->pbListId))
                {
                    $code = $this->prContainer->pbListId;
                }
                
                $errorinfo = $this->existCheck($this->prContainer->pbInputContent,$code,$form_ini[$filename]['use_maintable_num'],$step,$con);
          
//                if(count($errorinfo) != 1 || $errorinfo[0] != "")
                if($errorinfo[0] != "")
		{                        
                        $_SESSION['error'] = $errorinfo[0];
                    
                        if($step == STEP_INSERT)
                        {
                            $page = new InsertPage($this->prContainer);
                        }
			else if($step == STEP_EDIT)
                        {
                            $page = new EditPage($this->prContainer);
                        }
                        else if($step == STEP_DELETE)
                        {
                            $page = new DeletePage($this->prContainer);
                        }
                        
                        //html上部作成
			$page->executePreHtmlFunc();
			
			//作ったPageにHTMLを吐かせる
			$page->echoAllHtml();
                        
                        //dbを閉じる
                        dbclose();
                        
                        return;
		}
		
		if($step == STEP_INSERT)//データ登録
		{
			$result = insert($this->prContainer->pbFileName, $this->prContainer->pbInputContent,$con);
		}
		else if($step == STEP_EDIT)//データ編集
		{
			$edit = $this->prContainer->pbInputContent;
			//$tablenum = $this->prFormIni['use_maintable_num'];
			$tablenum = $this->prContainer->pbPageSetting['use_maintable_num'];
			if(isset($_SESSION['list']['uniqe']))
			{
				$edit['uniqe'] = $_SESSION['list']['uniqe'];
			}
                        $code = getCode($this->prContainer->pbFileName);
			$edit[$code] = $this->prContainer->pbListId;
			
			$result = update($this->prContainer->pbFileName, $edit,$con);
		}
		else if($step == STEP_DELETE)//データ削除
		{
			$delete = $this->prContainer->pbInputContent;
//			$tablenum = $this->prContainer->pbPageSetting['use_maintable_num'];
                        $code = getCode($this->prContainer->pbFileName);
			if(isset($_SESSION['list']['uniqe']))
			{	
				$delete['uniqe'] = $_SESSION['list']['uniqe'];
			}
			$delete[$code] = $this->prContainer->pbListId;
			//$result = delete($this->prContainer->pbFileName, $delete,$_SESSION['data'],$con);
			$result = delete($this->prContainer->pbFileName, $delete,'',$con);
		}
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		//セッション情報など初期化
		//unsetSessionParam();
		$history = $_SESSION['history'];
		$count = count($history);
		for($i = $count-1; $i >= 0 ; $i-- )
		{
			$filearray = explode("_",$history[$i]);
			//案件登録,更新時
			if($filearray[0] == "ANKENINFO")
			{
				if($step == 2)
				{
					$filename = "ANKENSHOW_1";
					$step = 2;
					$id = $this->prContainer->pbListId;
				}
				else
				{
					$filename = $filearray[0]."_2";
					$step = STEP_NONE;
					$id = STEP_NONE;
				}
				
				break;
			}
			//見積登録,更新時
			if($filearray[0] == "MITSUMORIINFO")
			{
				$filename = "ANKENSHOW_1";
				$step = 2;
				$id = $this->prContainer->pbInputContent['form_mmhANKID_0'];
				break;
			}	
			//請求登録,更新時
			if($filearray[0] == "SEIKYUINFO")
			{
				$filename = "ANKENSHOW_1";
				$step = 2;
				$id = $this->prContainer->pbInputContent['form_sehANKID_0'];
				break;
			}
			//主にマスタ系の処理実行時
			if($filearray[1] == "2")
			{
				$filename = $history[$i];
				$step = STEP_NONE;
				$id = STEP_NONE;
				break;
			}
		}
                
		$this->PageJump($filename, $id, $step, "", "");

	}
	
	function refreshSession($filename, $id, $step)
	{
	   $keep['4CODE'] = $_SESSION['4CODE'];
           $keep['STAFFID'] = $_SESSION['STAFFID'];
           $keep['STAFFNAME'] = $_SESSION['STAFFNAME'];
           $keep['STAFFVALUE'] = $_SESSION['STAFFVALUE'];
           $keep['STAFFPASS'] = $_SESSION['STAFFPASS'];
           
           if(isset($_SESSION['search']))
           {
                $keep['search'] = $_SESSION['search'];
           }
           
	   //SESSION初期化
	   $_SESSION = array();
	   $_SESSION = $keep;
	   $_SESSION['filename'] = $filename;
	   $_SESSION['step'] = $step;
	   $_SESSION['list'] = array();
	   $_SESSION['list']['id'] = $id;
	}
   /*
     *指定のページへ呼ばせる関数
     *  
   */
   function PageJump($filename,$id,$step,$Content,$secondContent,$error="")
   {
	   //item.iniから保存すべきSESSIONの項目を抽出し変数keepに保存
	   $this->refreshSession($filename, $id, $step);

	   $url = "";
	   //見積の入力値
	   if($Content != "")
	   {
		   $_SESSION['Content'] = $Content;
	   }
	   if($secondContent != "")
	   {
		   $_SESSION['SecondContent'] = $secondContent;
	   }
           if($error != "")
           {
                    $_SESSION['error'] = $error;
           }
	   //指定IDが入力されていたらURLに追加
	   if(isset($id) && isset($filename))
	   {
			$url = "?".$filename."&edit_list_id=".$id;
	
	   }

	   header("location:".(empty($_SERVER['HTTPS'])? "http://" : "https://")
			   .$_SERVER['HTTP_HOST'].dirname($_SERVER["REQUEST_URI"])."/main.php$url");

	   exit();	
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
            if($filename === "NENZIPJ_1"){
                $count = 1;
            }else{
                $count = $_GET['pjendCount'];
            }
            

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

/**
 * 操作履歴削除用のExecuter
 * 
 */
class DeleteRirekiExecuter extends BaseLogicExecuter
{
	
	/**
	 * 処理
	 * ここでは操作履歴を指定日時以前の条件で削除する
	 */
	public function executeSQL()
	{
		//指定日時
		$ssrUPDATETIME = $this->prContainer->pbInputContent['form_ssrUPDATETIME_0'];

		//DB接続、トランザクション開始
		$con = beginTransaction();
		
		//請求テーブルから情報を取得
		$sql = "DELETE FROM sousarireki WHERE UPDATETIME<'$ssrUPDATETIME'";
		$result = $con->query($sql) or ($judge = true);																		// クエリ発行
		if($judge)
		{
			error_log($con->error,0);
			$judge =false;
		}
		////////////////////操作履歴///////////////////////
		addSousarireki($this->prContainer->pbFileName, STEP_DELETE, $sql, $con);
		////////////////////操作履歴///////////////////////
		
		//トランザクションコミットまたはロールバック
		commitTransaction($result,$con);
		
		//指定ページへ遷移
		$this->PageJump( 'TOP_5', '', 0, '' );
	}
}

/**
 * CSV取込用のExecuter
 * 
 */
class ImportCsvExecute extends BaseLogicExecuter
{
    
    public $a;
  
    public function importCSV()
    {
        foreach($_FILES as $form => $value)
        {
            if ($value['size'] != 0) {
                $file_array = explode('.', $value['name']);
                $extention = $file_array[(count($file_array) - 1)];
                $tempfile = './temp/';
                $tempfile .= "tempfileinsert.txt";
                move_uploaded_file($value['tmp_name'], $tempfile);
            }
        }
        
        //------------------------//
        //          定数          //
        //------------------------//
        $FilePath = "temp/tempfileinsert.txt";
        
        //------------------------//
        //          変数          //
        //------------------------//
        $countrow = 0;
        $readBody = array();											//読み込み配列
        $columns = array();
        $column = array();
        $param = array();
        $item = "";
        
        //------------------------//
        //        取込処理        //
        //------------------------//
        
        $columns = $this->prContainer->pbPageSetting['page_columns'];
        $column = explode(',',$columns);
        $this->a = $column;
        
        for($i = 0; $i < count($column); $i++)
        {
            $param[$i] = $this->prContainer->pbParamSetting[$column[$i]]['column'];
        }
        
        //取込データを読み込み
        $file = fopen($FilePath, "r");
        if($file)
        {
            while($line = fgets($file))
            {
                $strsub = explode(",", trim($line)); //カンマ区切りのデータを取得
                
                //個数チェック
                if (count($strsub) !== count($column)) {
                    $filename = $this->prContainer->pbFileName;
                    $this->PageJump($filename, $_SESSION['userid'], 1, "error", "");
                }

                for($i = 0; $i < count($column); $i++)
                {
                    $item = mb_convert_encoding( $strsub[$i], "UTF-8","SJIS");
                    $readBody[$countrow][$param[$i]] = $item;
                }
                
                $countrow++;
            }
        }
        fclose($file);
        return $readBody;
    }
    
    /**
     * ブランクチェック
     * 
     */
    function blankCheck ($value)
    {
        if($value === "")
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * 桁数チェック
     * 
     */
    function ketaCheck ($value,$count)
    {
        if(mb_strlen($value) > $count)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    /**
     * フォーマットのチェック
     * 
     */
    function formatCheck ($value,$format)
    {
        $check = true;
        
        if($value === "")
        {
            $check = false;
        }
         else 
        {
            if(preg_match($format, $value))
            {
                $check = false;
            }
        }
        
        return $check;
    }
    
    /**
     * 存在チェック
     * 
     */
    function sonzaiCheck($value,$con,$hkey)
    {  
        $sql = "SELECT * FROM hanyoumaster WHERE HKEY='" . $hkey . "' AND HVALUE = '" . $value . "';";
        $result = $con->query($sql);
        $rownums = $result->num_rows;
        
        if($rownums === 0)
        {
            return true;
        }
        else
        {
            return false;
        }
    }
    
    
}
