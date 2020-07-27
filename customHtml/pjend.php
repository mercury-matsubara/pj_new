<?php
class Pjend extends ListPage
{
    public $errorCode;
    
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

        $html .= '<script src="./customJS/pjend.js"></script>';

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
            $_SESSION['error'] = "";
        }
            if(!isset($_SESSION['list']))
            {
                    $_SESSION['list'] = array();
            }
            //検索フォーム作成,日付フォーム作成
            $formStrArray = $this->makeformSearch_setV2( $this->prContainer->pbInputContent, 'form' );
            $form = $formStrArray[0];			//0はフォーム用HTML
            $this->prInitScript = $formStrArray[1];	//1は構築用スクリプト

            //検索SQL
            $sql = array();
            $sql = joinSelectSQL($this->prContainer->pbInputContent, $this->prMainTable, $this->prContainer->pbFileName, $this->prContainer->pbFormIni);
            $sql = SQLsetOrderby($this->prContainer->pbInputContent, $this->prContainer->pbFileName, $sql);
            $limit = $this->prContainer->pbInputContent['list']['limit'];				// limit
            $limit_start = $this->prContainer->pbInputContent['list']['limitstart'];	// limit開始位置

            //リスト表示HTML作成
            $pagemove = intval( $this->prContainer->pbPageSetting['isPageMove'] );
            $list =  $this->makeListV2($sql, $_SESSION['list'], $limit, $limit_start, $pagemove);

            $checkList = $_SESSION['check_column'];

            //出力HTML作成
            $html ='<div class = "pad" >';
            $html .='<form name ="form" action="main.php?PJEND_1=" method="post" id="pjend" onsubmit = "return check(\''.$checkList.'\');">';
            $html .='<table><tr><td><fieldset><legend>検索条件</legend>';
            $html .= $form;								//検索項目表示

            //--2019/06/06追加　filename取得　
            $filename = $this->prContainer->pbFileName;        
            $html .='</fieldset></td><td valign="bottom"><input type="submit" name="serch_'.$filename.'" value = "表示" class="free" ></td></tr></table>';
            $html .= $list;
            $html .= '</form>';

            return $html;
    }
     
    /**
     * 関数名: makeBoxContentBottom
     *   メインの機能提供部分下部のHTML文字列を作成する
     *   他ページへの遷移ボタンなどを作成
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentBottom() 
    {
        //ダイアログ
        $html = '<div id="dialog" title="入力確認" style="display:none;">
                                <p>この内容でよろしいでしょうか？</p>
                                </div>';
        
        $html .= '<div class = "left" style = "HEIGHT : 30px"><form action="main.php" method="get">';
			
        //終了ボタン作成
        $html .= '<input type ="button" value = "終了" class = "free" name = "Comp" onclick="pjend()">';
                
        $html .= '</form></div>';
       
        return $html;
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

        if(isset($this->errorCode))
        {
            $code = $this->errorCode[1];
            $num = $this->getNumber($code);
            $html .= 'alert("終了処理を実行できませんでした。\n'
                    .'プロジェクトナンバ：'.$num[0].'\n'
                    .'枝番ナンバ：'.$num[1].'");';
        }

        $html .= '</script>';
        return $html;
    }
    
    /*
     * 関数名：getNumber
     * エラーが出たデータのプロジェクトナンバと枝番ナンバを取得する
     */
    function getNumber($code)
    {
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
        $sql = "SELECT * FROM (select * from projectinfo LEFT JOIN projectnuminfo USING (1CODE) LEFT JOIN edabaninfo USING (2CODE)  WHERE 5PJSTAT = 1 ) as projectinfo where 5CODE = ".$code.";";
        $result = $con->query($sql) or ($judge = true);
        
        foreach($result as $row)
        {
            $num[] = $row["PROJECTNUM"];
            $num[] = $row["EDABAN"];                    
        }
        
        //トランザクションコミットまたはロールバック
        commitTransaction($result,$con);
        
        return $num;
    }
}