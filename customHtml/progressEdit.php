<?php
/**
 * PJ進捗画面
 * 
 */
class ProgressEdit extends EditPage
{
    public $errorCode = "";
    
    /**
     * 関数名: makeStylePart
     *   CSS定義文字列(HTML)を作成する関数
     * (基本的にはCSSファイルへのリンクを作成)
     * 
     * @retrun HTML文字列
     */
    function makeStylePart() {
        $html = parent::makeStylePart();
        $html .= '<link rel="stylesheet" type="text/css" href="./customCSS/progresspopup.css">';
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
        $html = parent::makeBoxContentTop();
        // db接続関数実行
        $con = dbconect();
        $pjsql = pjEditSQL($this->prContainer->pbInputContent['form_pjp6CODE_0']);
        // SQL実行
        $result = $con->query( $pjsql );																	// クエリ発行
	if(!$result)
	{
            error_log($con->error,0);
            exit();
	}
        // 取得データ配列へ
        while($result_row = $result->fetch_array(MYSQLI_ASSOC))
        {
            $this->prContainer->pbInputContent['form_pjpPROJECTNUM_0'] = $result_row['PROJECTNUM'];
            $this->prContainer->pbInputContent['form_pjpEDABAN_0'] = $result_row['EDABAN'];
            $this->prContainer->pbInputContent['form_pjpPJNAME_0'] = $result_row['PJNAME'];
            $this->prContainer->pbInputContent['form_pjpSTAFFID_0'] = $result_row['STAFFID'];
            $this->prContainer->pbInputContent['form_pjpSTAFFNAME_0'] = $result_row['STAFFNAME'];
        }
        $kousql = "select * from kouteiinfo where 3CODE = ".$this->prContainer->pbInputContent['form_pjp3CODE_0'];
        // SQL実行
        $kouresult = $con->query( $kousql );																	// クエリ発行
	if(!$result)
	{
            error_log($con->error,0);
            exit();
	}
        // 取得データ配列へ
        while($result_row = $kouresult->fetch_array(MYSQLI_ASSOC))
        {
            $this->prContainer->pbInputContent['form_pjpKOUTEIID_0'] = $result_row['KOUTEIID'];
            $this->prContainer->pbInputContent['form_pjpKOUTEINAME_0'] = $result_row['KOUTEINAME'];
        }
        
        return $html;
    }

    /**
     * 関数名: makeBoxContentBottom
     *   メインの機能提供部分下部のHTML文字列を作成する
     *   他ページへの遷移ボタンなどを作成
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentBottom() {
        $html = '';
        $is_permission = isPermission($this->prContainer->pbFileName);
        //ユーザーマスタで権限がない
        if ($is_permission === false && $this->prContainer->pbFileName === 'USERMASTER_1') {
            //自分のIDのみ許可する
            $is_permission = ( $this->prContainer->pbListId == $_SESSION['userid'] );
        }
        if ($is_permission) {
            //ダイアログ
            $html .= '<div id="dialog" title="入力確認" style="display:none;">
						<p>この内容でよろしいでしょうか？</p>
						</div>';
            // 読取指定
            if ($this->prContainer->pbPageSetting['form_type'] !== '2') {
                if ($this->prContainer->pbFileName === "JISYAMASTER_3") {
                    $html .= '<div class = "pad">
					<input type="submit" name = "kousinn" value = "更新" class="free" onclick="document.getElementByName(\'edit\').action=\'main.php?' . $this->prContainer->pbFileName . '_button=\'" >';
                } else {
                    $html .= '<div class = "pad">
                                            <input type="button" name = "kousinn" value = "更新" class="free" onClick="Regist()" >';
                    $html .= '<input type="button" name = "delete" value = "削除" class="free" onClick = "Delete();">';
                }

                

                $html .= '</div>';
            }

            //遷移ボタン
            $linkValue = '';
            if (isset($this->prContainer->pbInputContent['edit_list_id'])) {
                $linkValue = 'edit_list_id=' . $this->prContainer->pbInputContent['edit_list_id'];
            } else if (isset($this->prContainer->pbListId)) {//ステータス更新時GET情報がないため
                $linkValue = 'edit_list_id=' . $this->prContainer->pbListId;
            }
            $html .= $this->makeButtonV2($this->prContainer->pbFileName, 'bottom', STEP_EDIT, $linkValue);
        }
        $html .= '</form>';
        return $html;
    }

}