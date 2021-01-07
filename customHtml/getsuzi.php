<?php
class Getsuzi extends BasePage
{
    public $errorCode;
    /**
     * 関数名: exequtePreHtmlFunc
     *   ページ用のHTMLを出力する前の処理
     */
    public function executePreHtmlFunc()
    {
        //親の処理
        parent::executePreHtmlFunc();
        //変数をセット
        $this->prTitle = "月次処理";					//メンバ変数タイトル
    }
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

        $html .= '<script src="./customJS/getsuzi.js"></script>';

        return $html;

    }
    
    /**
     * 関数名: makeBoxContentMain
     *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
     *   機能名の表示など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain()
    {
        if(isset($_SESSION['error']))
        {
            $this->errorCode = $_SESSION['error'];
            $_SESSION['error'] = null;
        }
        $this->prTitle = "月次処理";
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
	$html = "<left>";
	$html .= "<form action='pageJump.php' method='post'><div class='left'>";
	$html .= makebutton($filename,'top');
	$html .= "</div>";
	$html .= "<div style='clear:both;'></div>";
	$html .= "</form>";
	$html .= "</left>";
	$html .= "<center>";
	$html .= "<a class = 'title'>月次処理</a>";
	$html .= "<br><a class = 'error'>".$message."</a>";
	$html .= "<br><br>";
	$html .= ("前回実施月： ".$this->getuzi_rireki()."<br><br>");
	$html .= '<form action="main.php?GETSUZI_1=" method="post" >';
	$today = explode('/',date("Y/m/d"));
	if($today[1] == '6')
	{
		$post['period_0'] = $this->getperiod($today[1],$today[0]) - 1;
	}
	else
	{
		$post['period_0'] = $this->getperiod($today[1],$today[0]);
	}
	
	$post['month_0'] = $today[1]-1;
	$html .= '<table><tr><td>月次処理対象期 </td><td>'.$this->period_pulldown_set("period","",$post,"","","").'</td></tr>';
	$html .= '<tr><td>月次処理対象月 </td><td>'.$this->month_pulldown_set("month","",$post,"","","").'</td></tr></table>';
	$html .= $this->makeEndMonth();
	$html .= "<div style='display:inline-flex'>";
//	$html .= "<input type='submit' name='delete' value = '月次処理' class='free' onClick = 'return check();'>";
        $html .= "<input type='submit' name='Comp' value = '月次処理' class='free' onClick = 'return check();'>";
	$html .= "</form>";
	$html .= "<form action='download_csv.php' method='post'>";
	$html .= "<input type = 'hidden' name = 'period' id = 'period' value = ''>";
	$html .= "<input type = 'hidden' name = 'month' id = 'month' value = ''>";
	$html .= "<input type ='submit' name = 'csv' class='button' value = 'csvファイル生成' style ='height:30px;' onClick = ' set_value(); '>";
	$html .= "</form>";
	$html .= "</div>";
	$html .= "</center>";
        
        return $html;
    }
    
    /****************************************************************************************
    function getuzi_rireki()


    引数	なし

    戻り値	なし
    ****************************************************************************************/
    function getuzi_rireki()
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
    function getperiod($month,$year)
    {

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
    function period_pulldown_set($name,$over,$post,$ReadOnly,$formName,$isnotnull)
    {
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
    
    /************************************************************************************************************
    function month_pulldown_set($name,$over,$post,$ReadOnly,$formName,$isnotnull)

    引数	$post

    戻り値	なし
    ************************************************************************************************************/
    function month_pulldown_set($name,$over,$post,$ReadOnly,$formName,$isnotnull)
    {
            //------------------------//
            //        初期設定        //
            //------------------------//
            $item_ini = parse_ini_file('./ini/item.ini', true);

            //------------------------//
            //          定数          //
            //------------------------//

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
            for($i = 1 ;$i <= 12 ; $i++)
            {
                    $text = $i."月";
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
    
    /************************************************************************************************************
    function makeEndMonth()

    引数1		$post							登録フォーム入力値
    引数2		$tablenum						テーブル番号
    引数3		$type							1:insert 2:edit 3:delete

    戻り値		$errorinfo						既登録確認結果
    ************************************************************************************************************/
    function makeEndMonth()
    {

            //------------------------//
            //        初期設定        //
            //------------------------//
            $form_ini = parse_ini_file('./ini/form.ini', true);
            $endmonth_ini = parse_ini_file('./ini/endmonth.ini', true);
            require_once ("f_Form.php");
            require_once ("f_DB.php");																							// DB関数呼び出し準備
            require_once ("f_SQL.php");																							// SQL関数呼び出し準備

            //------------------------//
            //          定数          //
            //------------------------//
            $filename = $_SESSION['filename'];
            $date = date_create('NOW');
            $nowyr = date_format($date, "Y");
            $nowmn = date_format($date, "n");
            $nowpd = $this->getperiod($nowmn,$nowyr);
            $before = $endmonth_ini['endmonth']['before_period'];
            $start = $nowpd - $before + 1 ;

            //------------------------//
            //          変数          //
            //------------------------//
            $sql = "";
            $judge = false;
            $endmonth = array();
            $listhtml = "";

            //------------------------//
            //          処理          //
            //------------------------//
            $con = dbconect();																									// db接続関数実行
            $sql = "SELECT * FROM endmonthinfo;";
            $result = $con->query($sql);
            while($result_row = $result->fetch_array(MYSQLI_ASSOC))
            {
                    if(isset($endmonth[$result_row['PERIOD']]))
                    {
                            $endmonth[$result_row['PERIOD']][count($endmonth[$result_row['PERIOD']])] = $result_row['MONTH'];
                    }
                    else
                    {
                            $endmonth[$result_row['PERIOD']][0] = $result_row['MONTH'];
                    }
            }
            $listhtml .= "<table><tr><td>";
            $listhtml .= "<table><td width='140''>　</td><td width='20' bgcolor='#f4a460'>　</td><td>･･･月次済</td></tr></table>";
            $listhtml .= "</td></tr><tr><td><table class ='list'><thead><tr>";
            $listhtml .= "<th><a class ='head'>期</a></th>";
            $listhtml .= "<th colspan='12'><a class ='head'>月</a></th></tr></thead>";
            $listhtml .= "<tbody>";	

            for($i = 0; $i < $before; $i++)
            {
                    //期を作成
                    $listhtml .= "<tr><td class='center' bgcolor='#1E90FF'><a class ='body'>".($start+$i)."</a></td>";

                    //12ヶ月表作成
                    for($j = 0; $j < 12; $j++)
                    {
                            if($j < 7)
                            {
                                    $color = "";
                                    if(!empty($endmonth[($start+$i)]))
                                    {
                                            for($g = 0; $g < count($endmonth[($start+$i)]); $g++)
                                            {
                                                    $month = $endmonth[($start+$i)][$g];
                                                    if($month == ($j + 6))
                                                    {
                                                            $color = "#f4a460";
                                                            break;
                                                    }
                                            }
                                    }
                                    $listhtml .= "<td class='center' width='25' bgcolor='".$color."'><a class ='body'>".($j + 6)."</a></td>";
                            }
                            else
                            {
                                    if(!empty($endmonth[($start+$i)]))
                                    {
                                            $color = "";
                                            for($g = 0; $g < count($endmonth[($start+$i)]); $g++)
                                            {
                                                    $month = $endmonth[($start+$i)][$g];
                                                    if($month == ($j - 6))
                                                    {
                                                            $color = "#f4a460";
                                                            break;
                                                    }
                                            }
                                    }
                                    $listhtml .= "<td class='center' width='25' bgcolor='".$color."'><a class ='body'>".($j - 6)."</a></td>";
                            }
                    }
                    $listhtml .= "</tr>";
            }
            $listhtml .= "</tbody></table></td></tr></table>";

            return ($listhtml);
    }

    /**
     * 関数名: makeAfterScript
     *   BODYタグの後ろに埋め込むJavaScript文字列を作成する
     * 
     * @retrun HTML文字列
     */
    function makeAfterScript()
    {	    
        $html = '<script language="JavaScript">';

        $html.= ' $("#contents .sub-menu > a").click(function (e) {
                                $("#contents ul ul").slideUp(), $(this).next().is(":visible") || $(this).next().slideDown(),
                                e.stopPropagation();
                        });';

        $html .= 'function makeDatepicker()
                {' ;
        $html.= $this->prInitScript;
        $html.= '}';

        //ToDo エラー表示変更するかも
        if(isset($this->errorCode))
        {
            $code = $this->errorCode;
            
            $html .= 'alert("月次処理を実行できませんでした。\n'
                    .''.$code.'");';
        }

        $html .= '</script>';
        return $html;
    }
}