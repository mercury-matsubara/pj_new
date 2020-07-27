<?php
class Nenzi extends BasePage
{
    /**
     * 関数名: makeScriptPart
     *   JavaScript文字列(HTML)を作成する関数
     *   HEADタグ内に入る
     *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
     * 
     * @retrun HTML文字列
     */
    function makeScriptPart()
    {
        $html = parent::makeScriptPart();

        $html .= '<script src="./customJS/nenzi.js"></script>';

        return $html;

    }
    
    function makeBoxContentMain()
    {
        $message = "";
	$filename = $_SESSION['filename'];
	if(isset($_SESSION['post']['message']))
	{
		$message = $_SESSION['post']['message'];
		$_SESSION['post']['massage'] = null;
	}
	if(isset($_SESSION['post']['item']))
	{
		$post = $_SESSION['post']['item'];
	}
	else
	{
		$post = array();
	}
	if(empty($_SESSION['nenzi']['checkmessage']))
	{
		$html = "<left>";
		$html .= "<form action='pageJump.php' method='post'>";
		$html .= makebutton($filename,'top');
		$html .= "<div style='clear:both;'></div>";
		$html .= "</form>";
		$html .= "</left>";
		$html .= "<center>";
		$html .= "<a class = 'title'>年次処理</a>";
		$html .= "<br><a class = 'error'>".$message."</a>";
		$html .= "<br><br>";
		$html .= ("前回実施期： ".$this->nenzi_rireki()."<br><br>");
		$html .= '<table><tr><td><form action="main.php?NENZI_1=" method="post">';
		$today = explode('/',date("Y/m/d"));
		$post['period_0'] = $this->getperiod($today[1],$today[0]) - 1;
		$html .= '年次処理対象期 '.$this->period_pulldown_set("period","",$post,"","","").'</td></tr></table>';
		$html .= "<div style='display:inline-flex'>";
		$html .= "<br><br><input type='submit' name='delete' value = '期またぎ' class='free'>";
//		$html .= "<input type='submit' name='push' value = '年次処理' class='free' onClick = 'return check();'>";
                $html .= "<input type='submit' name='Comp' value = '年次処理' class='free' onClick = 'return check();'>";
		$html .= "</form>";
		$html .= "<form action='download_csv.php' method='post'>";
		$html .= "<input type = 'hidden' name = 'period' id = 'period' value = ''>";
		$html .= "<input type ='submit' name = 'csv' class='button' value = 'csvファイル生成' style ='height:30px;' onClick = ' set_value(); '>";
		$html .= "</form>";
		$html .= "</div>";
		$html .= "</center>";
	}
	else
	{
		nenji($_SESSION['nenzi']['period']);
		$html .= "<form action='pageJump.php' method='post'>";
		$html .= makebutton($filename,'top');
		$html .= "<div style='clear:both;'></div>";
		$html .= "</form>";
		$html .= "<center>";
		$html .= "<a class = 'title'>年次処理完了</a>";
		$html .= "<br><br>";
		$html .= ("実施期: ".$_SESSION['nenzi']['period']."期<br><br>");
		$html .= "</center>";
		unset($_SESSION['nenzi']);
		unset($_SESSION['list']);
	}
        
        return $html;
    }
    
    /****************************************************************************************
    function nenzi_rireki()


    引数	なし

    戻り値	なし
    ****************************************************************************************/

    function nenzi_rireki()
    {


            //------------------------//
            //        初期設定        //
            //------------------------//
            $file_ini = parse_ini_file('./ini/file.ini', true);

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $check_path = $file_ini[$filename]['file_path'];
            $date = date_create('NOW');
            $date = date_format($date, "Y-m-d");


            //------------------------//
            //          変数          //
            //------------------------//
            $buffer = "";

            //--------------------------//
            //  CSVファイルの追記処理  //
            //--------------------------//

            if(!file_exists($check_path))
            {
                    $fp = fopen($check_path, 'ab');																							// 送信確認ファイルを追記書き込みで開く
                    fclose($fp);				
            }

            $fp = fopen($check_path, 'a+b');																							// 送信確認ファイルを追記書き込みで開く
            // ファイルが開けたか //
            if ($fp)
            {
                    // ファイルのロックができたか //
                    if (flock($fp, LOCK_EX))																								// ロック
                    {
                            $buffer = fgets($fp);
                            flock($fp, LOCK_UN);																								// ロックの解除
                    }
                    else
                    {
                            // ロック失敗時の処理
                    }
            }
            fclose($fp);																												// ファイルを閉じる
            return($buffer);
    }
    
    /************************************************************************************************************
    年→期変換処理(プロジェクト管理システム)
    function getperiod($month,$year)

    引数1		$month						月
    引数2		$year 						年

    戻り値		$form						モーダルに表示リストhtml
    ************************************************************************************************************/
    function getperiod($month,$year){

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
            $startyear = $item_ini['period']['startyear'];
            $startmonth = $item_ini['period']['startmonth'];


            //------------------------//
            //          変数          //
            //------------------------//
            $period = 0 ;



            //------------------------//
            //        検索処理        //
            //------------------------//
            $period = $year - $startyear + 1;
            if($startmonth > $month)
            {
                    $period = $period - 1 ;
            }

            return $period;

    }
    
    /************************************************************************************************************
    function period_pulldown_set($name,$over,$post,$ReadOnly,$formName,$isnotnull)

    引数	$post

    戻り値	なし
    ************************************************************************************************************/
    function period_pulldown_set($name,$over,$post,$ReadOnly,$formName,$isnotnull){
            //------------------------//
            //        初期設定        //
            //------------------------//
            $item_ini = parse_ini_file('./ini/item.ini', true);

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $year = date_create('NOW');
            $year = date_format($year, "Y");
            $month = date_create('NOW');
            $month = date_format($month, "n");
            $startyear = $item_ini['period']['startyear'];
            $startmonth = $item_ini['period']['startmonth'];
            $period = 0;
            //------------------------//
            //          変数          //
            //------------------------//
            $pulldown = "";
            $num = 0;
            $text = "";
            $value ="";
            $formname ="";
            $select = "";
            $isSelect = false;
            $isdisable = "";
            $disable = "";

            //------------------------//
            //          処理          //
            //------------------------//
            $period = $year - $startyear;
            if($startmonth <= $month)
            {
                    $period = $period + 1;
            }
            if($filename == 'nenzi_5')
            {
                    $period = $period - 1;
            }
            if($ReadOnly == '')
            {
                    $isdisable = "";
            }
            else
            {
                    $isdisable = 'disabled';
            }
            if($over !="")
            {
                    $formname = $name."_0_".$over;
            }
            else
            {
                    $formname = $name."_0";
            }

            $pulldown.='<select id="'.$formname.'"  class ="'.$ReadOnly.'" name="'.$formname.'"
                                             onMouseOver ="change(this.id,\''.$ReadOnly.'\',\''.$formName.'\');" 
                                            onChange = "notnullcheck(this.id,'.$isnotnull.',\''.$formName.'\');">';
            for($i = 1 ;$i <= $period ; $i++)
            {
                    $text = $i."期";
                    $value = $i;
                    if(isset($post[$formname]))
                    {
                            if($value == $post[$formname])
                            {
                                    $select = ' selected ';
                                    $isSelect=true;
                                    $disable = "";
                            }
                    }
                    $pulldown.='<option value ="'.$value.'" '.$select.' >'.$text.'</option>';
                    $select = "";
            }
            if($isSelect)
            {
                    $pulldown.='<option value ="" > </option>';
            }
            else
            {
                    $pulldown.='<option value ="" selected > </option>';
            }
            return $pulldown;
    }

}