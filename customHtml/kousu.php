<?php

require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");

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
        $html .= '<script src="./customJS/popup.js"></script>';
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
                $this->errorCode = $_SESSION['error'];
                //$errorinfo = $_SESSION['error'];
                $_SESSION['error'] = "";
            }
            //日付
            // $day = '<input type="text" name="day" class="top_text day" value='.$_GET['KOUSU_1_button?date'].'>';
            if(isset($_GET['KOUSU_1_button?date'])){
                $day = "<h3 class='pjday'>".$_GET['KOUSU_1_button?date']."</h3>";
                $date = $_GET['KOUSU_1_button?date'];
            }else{
                $day = "<h3 class='pjday'>".$this->prContainer->pbInputContent."</h3>";
                $date = $this->prContainer->pbInputContent;
            }
            
            //登録ボタン、戻るボタン
            $button = '<input type="button" id="regist" name = "comp" value = "登録" class="free">';
            $button .= '<a href="main.php?TOP_5='.str_replace("/","-",substr($date,0,7)).'"><input type="button" name = "back" value = "戻る" class="free"></a>';
            // コピー日付
            $copydate = '<input type="text" id = "copydate" class="copytime" readonly >';
            $copydate .= '<input type="button" name = "copy" value = "コピー" class="copybutton" onClick = "">';
            //定時時間、残業時間
            $zangyoTotal = "0.00";
            $teiziTotal = "0.00";
            
            $time = '<span style="margin-left: 10%;">定時：</span><span class="teizibox">'.$teiziTotal.'</span>';
            $time .= '<span style="margin-left: 2%;">残業：</span><span class="zangyobox">'.$zangyoTotal.'</span>';
            
            //テーブル作成
            $filename = $this->prContainer->pbFileName;
            $columnRaw = $this->prContainer->pbFormIni[$filename]['page_columns'];
            $column = explode(",",$columnRaw);
            for($i=0;$i<count($column);$i++)
            {
                $columnName[] = $this->prContainer->pbParamSetting[$column[$i]]['item_name'];
            }
            // 入力項目作成
            $table = $this->createTable($columnName,$column,$date);
            
            //出力HTML
            $html = '<br>';
            $html .= '<form method="post" name="Comp" action="main.php?'.$this->prContainer->pbFileName.'" id="send" enctype="multipart/form-data" >';
            $html .= '<div class = "pad">';
            $html .='<input type="hidden" name="step" value = "'.STEP_COMP.'" >';
            $html .='<input type="hidden" name="date" value = "'.$date.'" >';
            $html .= $day;
            $html .= '<div class = "line">';
            $html .= $button;
            $html .= $copydate;
            $html .= $time;
            $html .= '</div>';
            $html .='<input type="hidden" id="columnRaw" value = "'.$columnRaw.'" >';
            $html .= "<table class='list'>";
            $html .= $table;
            $html .="</table>";
            $html .= '</div>';
            $html .= '</form>';
            // ポップアップ作成
            $html .= $this->createPopUp();
            $this->prInitScript = $this->createAfterScript();
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
            if ($this->prContainer->pbPageSetting['form_type'] !== '2') {

            }

            //遷移ボタン
            $linkValue = '';
            if (isset($this->prContainer->pbInputContent['edit_list_id'])) {
                $linkValue = 'edit_list_id=' . $this->prContainer->pbInputContent['edit_list_id'];
            } else if (isset($this->prContainer->pbListId)) {//ステータス更新時GET情報がないため
                $linkValue = 'edit_list_id=' . $this->prContainer->pbListId;
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
    function createTable($post,$column,$day)
    {
        
        $sql = kousuSQL($_SESSION['STAFFID'], $day);
        // db接続関数実行
        $con = dbconect();
        $result = $con->query($sql);// クエリ発行
	if (!$result) {
            error_log($con->error, 0);
            exit();
        }
        $count = 0;
        $workData = array();
        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $workData[$count]['PROJECTNUM'] = $result_row['PROJECTNUM'];
            $workData[$count]['EDABAN'] = $result_row['EDABAN'];
            $workData[$count]['PJNAME'] = $result_row['PJNAME'];
            $workData[$count]['KOUTEIID'] = $result_row['KOUTEIID'];
            $workData[$count]['KOUTEINAME'] = $result_row['KOUTEINAME'];
            $workData[$count]['TEIZITIME'] = $result_row['TEIZITIME'];
            $workData[$count]['ZANGYOUTIME'] = $result_row['ZANGYOUTIME'];
            $workData[$count]['7PJSTAT'] = $result_row['7PJSTAT'];
            $count++;
        }
        
        //ポップアップ対象のファイル名取得
        $progress_link = $this->prContainer->pbParamSetting[$column[8]]['link_to'];
        $koutei_link = $this->prContainer->pbParamSetting[$column[9]]['link_to'];
        //ポップアップ登録押下時の反映ID取得
        $progress_popupKey = $this->prContainer->pbParamSetting[$column[8]]['link_key'];
        $koutei_popupKey = $this->prContainer->pbParamSetting[$column[9]]['link_key'];
        
        $html ="   <thead>";
        $html .="       <tr>";
        $html .="           <th style='width: 8%;'>$post[0]</th>";
        $html .="           <th style='width: 8%;'>$post[1]</th>";
        $html .="           <th >$post[2]</th>";
        $html .="           <th style='width: 5%;'></th>";        
        $html .="           <th style='width: 6%;'>$post[3]</th>";
        $html .="           <th style='width: 6%;'>$post[4]</th>";
        $html .="           <th style='width: 5%;'></th>";        
        $html .="           <th style='width: 6%;'>$post[5]</th>";
        $html .="           <th style='width: 6%;'>$post[6]</th>";
        $html .="       </tr>";
        $html .="   </thead>";
        $html .="   <tbody>";
        
        // データセット
        for($i=0;$i<10;$i++)
        {
            if ($i % 2 === 1) {
                $html .= "   <tr class='stripe'>";
            } else {
                $html .= "   <tr class='stripe_none'>";
            }
            // null判定(nullの場合ブランクセット)
            $project = isset($workData[$i]["PROJECTNUM"]) ? $workData[$i]["PROJECTNUM"] : "";
            $edaban = isset($workData[$i]["EDABAN"]) ? $workData[$i]["EDABAN"] : "";
            $pjname = isset($workData[$i]["PJNAME"]) ? $workData[$i]["PJNAME"] : "";
            $kouteid = isset($workData[$i]["KOUTEIID"]) ? $workData[$i]["KOUTEIID"] : "";
            $kouteiname = isset($workData[$i]["KOUTEINAME"]) ? $workData[$i]["KOUTEINAME"] : "";
            $teizitime = isset($workData[$i]["TEIZITIME"]) ? $workData[$i]["TEIZITIME"] : '';
            $zangyoutime = isset($workData[$i]["ZANGYOUTIME"]) ? $workData[$i]["ZANGYOUTIME"] : "";
            $status = isset($workData[$i]["7PJSTAT"]) ? $workData[$i]["7PJSTAT"] : "";
            
            // 読取専用か判定
            $disabled = "";
            if($status == "2"){
                $disabled = "disabled";
            }
            
            // PJナンバ
            $html .="           <td ><input type='text' value='".$project."' class='pjnum' "
                    . "id='form_".$column[0]."_".$i."' name='form_".$column[0]."_".$i."' $disabled></td>";
            // 枝番
            $html .="           <td ><input type='text' value='".$edaban."' class='eda' "
                    . "id='form_".$column[1]."_".$i."' name='form_".$column[1]."_".$i."' $disabled></td>";
            // 作業名
            $html .="           <td ><input type='text' value='".$pjname."' class='pjname' "
                    . "id='form_".$column[2]."_".$i."' name='form_".$column[2]."_".$i."' $disabled></td>";
            // ポップアップ
            $html .='           <td ><input type="button" id="popup" value="PJ詳細選択" itemnum = '.$i.' popup-key="' . $progress_popupKey . '" '
                    . 'data-action="popupAjax.php?id=' . $progress_link . '" '.$disabled.' ></td>'; 
            // 工程ID
            $html .="           <td ><input type='text' value='".$kouteid."' class='kouid' "
                    . "id='form_".$column[3]."_".$i."' name='form_".$column[3]."_".$i."' $disabled></td>";
            // 工程名
            $html .="           <td ><input type='text' value='".$kouteiname."' class='kouname' "
                    . "id='form_".$column[4]."_".$i."' name='form_".$column[4]."_".$i."' $disabled></td>";
            // ポップアップ
            $html .='           <td ><input type="button" id="popup" value="工程選択" itemnum = '.$i.' popup-key="' . $koutei_popupKey . '" '
                    . 'data-action="popupAjax.php?id=' . $koutei_link . '" '.$disabled.'></td>';  
            // 定時時間
            $html .="           <td ><input type='text' value='".$teizitime."' class='teizi' "
                    . "id='form_".$column[5]."_".$i."' name='form_".$column[5]."_".$i."' onchange='calculatetime(\"teizi\",\"teizibox\")' $disabled></td>";
            // 残業時間
            $html .="           <td ><input type='text' value='".$zangyoutime."' class='zangyo' "
                    . "id='form_".$column[6]."_".$i."' name='form_".$column[6]."_".$i."' onchange='calculatetime(\"zangyo\",\"zangyobox\")' $disabled></td>";
            
            $html .="       </tr>";
        }
        $html .="   </tbody>";
        
        return $html;
    }
    
    /**
     * javasc作成
     */
    function createAfterScript(){
        
        $script = "";
        for($i=0;$i<10;$i++){
            $script .= "updateAutocompleteValue('#form_topPROJECTNUM_".$i."','TOP');";
            $script .= "updateAutocompleteValue('#form_topEDABAN_".$i."','ETOP');";
            $script .= "updateAutocompleteByID('#form_topPJNAME_".$i."','#form_topEDABAN_".$i."','ETOP2');";
            $script .= "updateAutocompleteValue('#form_topKOUTEIID_".$i."','KOUTOP');";
            $script .= "updateAutocompleteByID('#form_topKOUTEINAME_".$i."','#form_topKOUTEIID_".$i."','KOUTOP2');";
        }
        
        return $script;
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
            $code = $this->errorCode;
            $html .= 'alert("'.$code.');';
        }

        $html .= '</script>';
        return $html;
    }
    
}