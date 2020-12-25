<?php

/**
 * 期またぎ時PJ登録画面作成
 * 
 */
class NenziPj extends InsertPage {
    
    /**
     * 関数名: makeStylePart
     *   CSS定義文字列(HTML)を作成する関数
     * (基本的にはCSSファイルへのリンクを作成)
     * 
     * @retrun HTML文字列
    */
    function makeStylePart() {
        $html = parent::makeStylePart();
        $html .= '<link rel="stylesheet" type="text/css" href="./customCSS/nenzipj.css">';
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
        $html = parent::makeScriptPart();
        $html .= '<script src="./customJS/popup.js"></script>';
        return $html;
    }
    
    /**
     * 関数名: makeBoxContentTop
     *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
     *   機能名の表示など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentTop() {

        $html = '<div class = "center"><a class = "title_edit">';
        $html .= $this->prTitle;  //タイトル表示
        $html .= '</a>';
        $html .= '</div>';
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
            $_SESSION['filename'] = $this->prContainer->pbFileName;
            //入力項目作成
            $form_array = $this->makeformInsert_setV2($this->prContainer->pbInputContent, $errorinfo, '', "insert", $this->prContainer);
            $form = $form_array[0];
            $this->prInitScript =  $form_array[1];

            $checkList = $_SESSION['check_column'];
            $notnullcolumns = $_SESSION['notnullcolumns'];
            $notnulltype = $_SESSION['notnulltype'];

            //2019/03/25パラメーター追加
            //hidden作成
            $hidden = $this->makeHiddenParam($this->prContainer->pbListId,$this->prContainer->pbStep, $this->prContainer->pbFileName);

            $send = '<form name ="insert" action="main.php?'.$this->prContainer->pbFileName.'=" method="post" autocomplete="off" id="send" enctype="multipart/form-data" 
                            onsubmit = "return check(\''.$checkList.
                            '\',\''.$notnullcolumns.'\',\''.$notnulltype.'\');">';
            $html = $send;
            // 選択項目作成
            $html .= $this->createSelectionPJ();
            if ($this->prContainer->pbPageSetting['message'] != "") {
                $html .= '<div class = "message">';
                $html .= '<p>';
                $html .= $this->prContainer->pbPageSetting['message'];
                $html .= '</p>';
                $html .= '</div>';
            }
            //出力HTML
            $html .= '<br>';
            $html .= '<div class = "edit_table">';
            $html .= $form;
            $html .= $hidden;
            $html .= '</div>';
            // ポップアップ作成
            $html .= $this->createPopUp();
            return $html;
	}
    /**
     * 登録フォーム用のHTMLを返す
     * V2はtableタグで囲まれた文字列として返す
     *
     * @param int  $post 入力内容
     * @param  $errorinfo
     * @param  $isReadOnly
     * @param  $form_name
     * @param $form_ini 画面設定値
     * @param $ParamSet 項目設定値 
     * 
     * @return array
    */
    function makeformInsert_setV2($post, $errorinfo, $isReadOnly, $form_name, &$container) {

        //------------------------//
        //          定数          //
        //------------------------//
        $columns_string = $container->pbPageSetting['page_columns'];
        $readonly_string = $container->pbPageSetting['readonly'];                 // 読取専用項目
        //V3を呼ぶ
        $input_result_v3 = $this->makeformInsert_setV3($post, $columns_string, $errorinfo, $readonly_string, $form_name, $container->pbParamSetting);
        //<TD>配列
        $td_array = $input_result_v3[0];
        //<TABLE>構築
        $insert_str = '<table name ="formedit" id ="edit">';
        foreach ($td_array as $td) {
            $insert_str .= '<tr>' . $td . '</td>';
        }
        $insert_str .= '</table>';

        //戻り値
        $form_result[0] = $insert_str;
        $form_result[1] = $input_result_v3[1];

        return ($form_result);
    }
    
    /**
     * ポップアップ入力項目作成
     */
    function createPopUpContent(){
        
        $html = "";
        return $html;
        
    }
    
    /**
     * 選択項目作成
     * 
     * @retrun HTML文字列
     */
    function createSelectionPJ(){
        
        $code = $this->prContainer->pbInputContent['nenziperiod'];
        $con = dbconect();
        $sql = ProjectSQL($code);
	$result = $con->query($sql) or die($con-> error);		// クエリ発行
        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
            $pjcode = $result_row['1CODE'];
            $pjnum = $result_row['PROJECTNUM'];
            $edacode = $result_row['2CODE'];
            $edanum = $result_row['EDABAN'];
            $pjname = $result_row['PJNAME'];
            $chaege = $result_row['CHARGE'];
            
	}


        $html = "<table class='selection'>
                    <tbody>
                      <tr>
                        <td>
                          <fieldset>
                            <legend>選択項目</legend>
                            <table name='formInsert' id='serch'>
                              <tbody>
                                <tr>
                                  <td><a class='itemname'>プロジェクトナンバ</a></td>
                                  <td><input type='text' name='bePROJECTNUM' id='bePROJECTNUM' value='$pjnum' size='10' class='readOnly' readonly></td>
                                      <input type='hidden' name='be1CODE' id='be1CODE' value='$pjcode' size='10'  >
                                          <input type='hidden' name='be5CODE' id='be5CODE' value='$code' size='10'  >
                                </tr>
                                <tr>
                                  <td><a class='itemname'>枝番ナンバ</a></td>
                                  <td><input type='text' name='beEDABAN' id='beEDABAN' value='$edanum' size='10' class='readOnly' readonly ></td>
                                      <input type='hidden' name='be2CODE' id='be2CODE' value='$edacode' size='10'  >
                                </tr>
                                <tr>
                                  <td><a class='itemname'>製版・案件名</a></td>
                                  <td><input type='text' name='bePJNAME' id='bePJNAME' value='$pjname' size='62' class='readOnly' readonly ></td>
                                </tr>
                                <tr>
                                  <td><a class='itemname'>受注金額</a></td>
                                  <td><input type='text' name='beCHARGE' id='beCHARGE' value='$chaege' size='62' class='readOnly' readonly></td>
                                </tr>
                              </tbody>
                            </table></fieldset>
                        </td>
                      </tr>
                    </tbody>
                  </table>";
        return $html;
    }

}

