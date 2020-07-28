<?php
class Pjcancel extends ListPage
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

        $html .= '<script src="./customJS/pjcancel.js"></script>';

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
            $html .='<form name ="form" action="main.php?PJCANCEL_1=" method="post" id="pjcancel" onsubmit = "return check(\''.$checkList.'\');">';
            $html .='<table><tr><td><fieldset><legend>検索条件</legend>';
            $html .= $form;								//検索項目表示

            //--2019/06/06追加　filename取得　
            $filename = $this->prContainer->pbFileName;        
            $html .='<input type="hidden" id="clear_'.$filename.'" value = "'.$this->prContainer->pbPageSetting['sech_form_num'].'" >';
            $html .='</fieldset></td><td valign="bottom"><input type="submit" name="serch_'.$filename.'" value = "表示" class="free" ></td>';
            $html .='</fieldset></td><td valign="bottom"><input type="button" value = "クリア" class="free" onclick="clearSearch(\''.$filename.'\')"></td></tr></table>';
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
        $html .= '<input type ="button" value = "終了キャンセル" class = "free" name = "Comp" onclick="pjcancel()">';
                
        $html .= '</form></div>';
       
        return $html;
    }
    
}