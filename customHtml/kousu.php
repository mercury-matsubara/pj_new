<?php
class Kousu extends InsertPage
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

        $html .= '<script src="./customJS/kousu.js"></script>';

        return $html;

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
            $day = '<input type="text" name="day" class="top_text day" value='.$_GET['KOUSU_1_button?date'].'>';
            //登録ボタン、戻るボタン
            $button = '<input type="button" name = "insert" value = "登録" class="free" onClick = "Regist()">';
            $button .= '<a href="main.php?TOP_5_button=&"><input type="button" name = "back" value = "戻る" class="free"></a>';
            //定時時間、残業時間
            $zangyoTotal = "0.00";
            $teiziTotal = "0.00";
//            for($i=0;$i<count($this->data);$i++)
//            {
//                $zangyoTotal += $this->data[$i]['DETALECHARGE']; 
//            }
            $time = '<input type="text" class="top_text time" id="zangyo_total" value="'.$zangyoTotal.'">';
            $time .= '<input type="text" class="top_text time" id="teizi_total" value="'.$teiziTotal.'">';
            //テーブル作成
            $filename = $this->prContainer->pbFileName;
            $column = explode(",",$this->prContainer->pbFormIni[$filename]['page_columns']);
            for($i=0;$i<count($column);$i++)
            {
                $columnName[] = $this->prContainer->pbParamSetting[$column[$i]]['item_name'];
            }
            $table = $this->createTable($columnName,$column);
            
            //出力HTML
            $html = '<br>';
            $html .= '<form name="form">';
            $html .= '<div class = "pad">';
            $html .= $day;
            $html .= $button;
            $html .= $time;
            $html .= $table;
            $html .= '</div>';
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
    /*
     * テーブル作成
     */
    function createTable($post,$column)
    {
        $html = "<table class='list'>";
        $html .="   <thead>";
        $html .="       <tr>";
        $html .="           <th >$post[0]</th>";
        $html .="           <th >$post[1]</th>";
        $html .="           <th >$post[2]</th>";
        $html .="           <th ></th>";        
        $html .="           <th >$post[3]</th>";
        $html .="           <th >$post[4]</th>";
        $html .="           <th ></th>";        
        $html .="           <th >$post[5]</th>";
        $html .="           <th >$post[6]</th>";
        $html .="       </tr>";
        $html .="   </thead>";
        $html .="   <tbody>";
        for($i=0;$i<9;$i++)
        {
            if($i%2 === 1)
            {
                $html .="       <tr class='stripe'>";
            }
            else
            {
                $html .="       <tr class='stripe_none'>";
            }
            $html .="           <td ><input type='text' class='pjnum' name='form_".$column[0]."_".$i."'></td>";
            $html .="           <td ><input type='text' class='eda' name='form_".$column[1]."_".$i."'></td>";
            $html .="           <td ><input type='text' class='pjname' name='form_".$column[2]."_".$i."'></td>";
            $html .="           <td ><input type='button' name='4' value='プロジェクト・枝番選択'></td>";        
            $html .="           <td ><input type='text' class='kouid' name='form_".$column[3]."_".$i."'></td>";
            $html .="           <td ><input type='text' class='kouname' name='form_".$column[4]."_".$i."'></td>";
            $html .="           <td ><input type='button' name='7' value='工程選択'></td>";        
            $html .="           <td ><input type='text' class='teizi' name='form_".$column[5]."_".$i."' onchange='calculateReturnTeizi()'></td>";
            $html .="           <td ><input type='text' class='zangyo' name='form_".$column[6]."_".$i."' onchange='calculateReturnZangyo()'></td>";
            $html .="       </tr>";
        }
        $html .="   </tbody>";
        $html .="</table>";
        return $html;
    }
}