<?php
class Kousu extends InsertPage
{
     /**
     * 関数名: makeStylePart
     *   CSS定義文字列(HTML)を作成する関数
     * (基本的にはCSSファイルへのリンクを作成)
     * 
     * @retrun HTML文字列
     */
    function makeStylePart() {
        $html = '<link rel="stylesheet" type="text/css" href="./css/list_css.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./css/popup.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./customCSS/kousu.css">';

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
            $_SESSION['pre_post'] = null;
            $errorinfo ='';

            if(isset($_SESSION['error']))
            {
                $errorinfo = $_SESSION['error'];
                $_SESSION['error'] = "";
            }

            //日付
            $day = '<div class = "pad">';
            $day .= '<input type="text" name="day" class="top_text day" value="選択した日">';
            //登録ボタン、戻るボタン
            $button = '<input type="button" name = "insert" value = "登録" class="free" onClick = "Regist()">';
            $button .= '<a href="main.php?TOP_5_button=&"><input type="button" name = "back" value = "戻る" class="free"></a>';
            //定時時間、残業時間
            $time = '<input type="text" class="top_text time" value="定時">';
            $time .= '<input type="text" class="top_text time" value="残業">';
            $time .= '</div>';
            //入力項目作成
            $form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $errorinfo, '', "insert", $this->prContainer);
            $form = $form_array[0];
            $this->prInitScript =  $form_array[1];

            //----明細入力作成----//
            $header_array = $this->makeList_itemV2('', $this->prContainer->pbSecondInputContent);
            if(isset($header_array))
            {
                    $header = $header_array[0];
                    $this->prInitScript .=  $header_array[1];
            }

            //--tab作成--//
            $tabarray = $this->makeTabHtml($this->prContainer->pbFileName, $this->prContainer->pbFormIni, $this->prContainer->pbInputContent);
            $tab = $tabarray[0];
            $this->prInitScript .= $tabarray[1];

            $checkList = $_SESSION['check_column'];
            $notnullcolumns = $_SESSION['notnullcolumns'];
            $notnulltype = $_SESSION['notnulltype'];

            //2019/03/25パラメーター追加
            //hidden作成
            $hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);

            $send = '<form name ="insert" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" autocomplete="off" id="send" enctype="multipart/form-data" 
                            onsubmit = "return check(\''.$checkList.
                            '\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';

            //出力HTML
            $html = '<br>';
            $html .= $day;
            $html .= $button;
            $html .= $time;
            $html .= $send;
            $html .= '<div class = "edit_table">';
            $html .= $form;
            $html .= $hidden;
            $html .= $header;
            $html .= '</div>';
            $html .= $tab;

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
            $html = '';
            if( isPermission($this->prContainer->pbFileName) )
            {
                    //ダイアログ
                    $html .= '<div id="dialog" title="入力確認" style="display:none;">
                                            <p>この内容でよろしいでしょうか？</p>
                                            </div>';
                    // 読取指定
                    if($this->prContainer->pbPageSetting['form_type'] !== '2')
                    {

                    }

                    //遷移ボタン
                    $linkValue = '';
                    if( isset( $this->prContainer->pbInputContent['edit_list_id'] ) )
                    {
                            $linkValue = 'edit_list_id='.$this->prContainer->pbInputContent['edit_list_id'];
                    }
                    else if(isset( $this->prContainer->pbListId))//ステータス更新時GET情報がないため
                    {
                            $linkValue = 'edit_list_id='.$this->prContainer->pbListId;
                    }
                    $html .= $this->makeButtonV2($this->prContainer->pbFileName, 'bottom', STEP_INSERT, $linkValue);
            }
            $html .='</div>';
            $html .= '</form>';
            return $html;
    }
}