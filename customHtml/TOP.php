<?php


/**
 * TOP画面作成
 * 
 */
class TopPage extends BasePage
{
    /**
     * 前月
     * @var type 
     */
    protected $prev;
    /**
     * 次月
     * @var type 
     */
    protected $next;
    /**
     * 月
     */
    protected $month;
    /*
     * 勤務総時間
     */
    protected $worktimeTotal;
    
    /**
     * 関数名: exequtePreHtmlFunc
     *   ページ用のHTMLを出力する前の処理
     */
    public function executePreHtmlFunc() {
        //親の処理
        parent::executePreHtmlFunc();
        $title1 = $this->prContainer->pbPageSetting['title'];
        //メンバ変数タイトル
        $this->prTitle = $title1;
    }
    
    /**
     * 関数名: makeStylePart
     *   CSS定義文字列(HTML)を作成する関数
     * (基本的にはCSSファイルへのリンクを作成)
     * 
     * @retrun HTML文字列
     */
    function makeStylePart() {
        $html = '<link rel="stylesheet" type="text/css" href="./css/list_css.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./css/calendar.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./css/popup.css">';
        return $html;
    }

    /**
     * 関数名: makeScriptPart
     *   JavaScript文字列(HTML)を作成する関数
     *   HEADタグ内に入る
     *   使用するスクリプトへのリンクや、スクリプトの直接記述文字列を作成
     * 
     * @retrun HTML文字列
     */
    function makeScriptPart() {
        //親の処理を呼び出す
        $html = parent::makeScriptPart();
        //必要なHTMLを付け足す
        $html .= '<script src="./customJS/TOP.js"></script>';
        $html .= '<script language="JavaScript"><!--
			$(function()
			{
			});
			--></script>';

        return $html;
    }

    /**
     * 関数名: makeBoxContentMain
     * 
     *   メインの機能提供部分のHTML文字列を作成する
     *   リストでは一覧表示、入力では各入力フィールドの構築など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain() {
        
        $html = "";

        // カレンダー作成
        $calendar = $this->makeCalendar();
        $html .="<div class='container'>";
        //$html .= "<div class='month'><h3><a href='?ym=$this->prev;'>&lt;</a> $this->month <a href='?ym=$this->next; '>&gt;</a></h3></div>";
        $html .= "<div class='month'><h3><a href='?TOP_5=$this->prev;'>&lt;</a> $this->month <a href='?TOP_5=$this->next; '>&gt;</a></h3></div>";
        $html .= "<div class='totaltime'><p>[勤務]".$this->worktimeTotal['TEIZITIME']."　[残業]".$this->worktimeTotal['ZANGYOUTIME']."　[総時間]".$this->worktimeTotal['TOTAL']."</p></div>";
        $html .= "<table class='calendar'>";
        $html .="    <tr class=youbi>";
        $html .="        <th class=youbiColor>日</th>";
        $html .="        <th class=youbiColor>月</th>";
        $html .="        <th class=youbiColor>火</th>";
        $html .="        <th class=youbiColor>水</th>";
        $html .="        <th class=youbiColor>木</th>";
        $html .="        <th class=youbiColor>金</th>";
        $html .="        <th class=youbiColor>土</th>";
        $html .="    </tr>";
        foreach ($calendar as $week) {
            // カレンダー表示
            $html .= $week;
        }
        $html .="</table>";
        $html .="</div>";
        $html .= $this->createPopUp();
        
        return $html;
    }
    
    /**
     * プロジェクト進捗データ取得
     */
    function getProjectData($month){
        
        // db接続関数実行
        $con = dbconect();

        $sql = getSelectSQL("",$this->prContainer->pbFileName);
        
        //指定値を置換(スタッフID、年月)
        $sql[0] = str_replace('@01', $_SESSION['STAFFID'], $sql[0]);
        $sql[0] = str_replace('@02', $month, $sql[0]);
        // SQL実行
        $result = $con->query( $sql[0] );																	// クエリ発行
	if(!$result)
	{
		error_log($con->error,0);
		exit();
	}
        $workDate = array();
        // 取得データ配列へ
        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $workDate[$result_row['SAGYOUDATE']]['TEIZITIME'] = $result_row['TEIZITIME'];
            $workDate[$result_row['SAGYOUDATE']]['ZANGYOUTIME'] = $result_row['ZANGYOUTIME'];
        }
        
        //指定値を置換(スタッフID)
        $sql[1] = str_replace('@01', $_SESSION['STAFFID'], $sql[1]);
        $sql[1] = str_replace('@02', $month, $sql[1]);
        // SQL実行
        $reply = $con->query( $sql[1] );																	// クエリ発行
	if(!$reply)
	{
		error_log($con->error,0);
		exit();
	}
        $worktimeTotal = array();
        // 取得データ配列へ
        while($result_row = $reply->fetch_array(MYSQLI_ASSOC))
        {
            $worktimeTotal['TEIZITIME'] = $result_row['TEIZITIME'];
            $worktimeTotal['ZANGYOUTIME'] = $result_row['ZANGYOUTIME'];
            $worktimeTotal['TOTAL'] = $result_row['TEIZITIME'] + $result_row['ZANGYOUTIME'];
            
            if ($worktimeTotal['TEIZITIME'] === null && $worktimeTotal['ZANGYOUTIME'] === null)
            {
                $worktimeTotal['TEIZITIME'] = "0.00";
                $worktimeTotal['ZANGYOUTIME'] = "0.00";
                $worktimeTotal['TOTAL'] = "0.00";
            }           
        }
        
        return [$workDate,$worktimeTotal];
        
    }
    
    
    /**
     * 作業時間、残業時間作成
     */
    function createWorkTd($workDate) {
        
        $work = "<span class='worktime'>";
        $work .= $workDate['TEIZITIME'];
        $work .= "</span>";
        $work .= "<span class='overtime'>";
        $work .= $workDate['ZANGYOUTIME'];
        $work .= "</span></td>";
        return $work;
    }
    

    
    /**
     * ポップアップ入力項目作成
     */
    function createPopUpContent() {
        
        $html ="";
        $html .= "<table class='popupcontent'>";
        $html .="    <tr class='text'>";
        $html .="        <th class='topth'>プロジェクトコード</th>";
        $html .="        <th class='topth'>枝番コード</th>";
        $html .="        <th class='topth'>製番・案件名</th>";
        $html .="        <th class='topth'>工程コード</th>";
        $html .="        <th class='topth'>工程名</th>";
        $html .="        <th class='topth'>作業日</th>";
        $html .="        <th class='topth'>定時時間</th>";
        $html .="        <th class='topth'>残業時間</th>";
        $html .="    </tr>";
        $html .="</table>";
        return $html;
        
    }
    

}
