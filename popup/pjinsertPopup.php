<?php

require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");

/**
 * プロジェクト選択popup作成
 */
class PjInsertPopup extends ListPage
{
    /**
     * 検索フォーム
     * @param type $page_id
     * @param type $form
     * @return html
     */
    function createSearchForm($page_id, $form ,$flg) {

        //PageID取得を_で分割
        $filename_array = explode('_', $this->prContainer->pbFileName);
        // code取得時必要
        $filename_insert = $filename_array[0] . "_1";
        $this->prFileNameInsert = $filename_insert;
        // 表示ボタン押下時
        if($flg === true){
            return;
        }
        $formStrArray = $this->makeformSearch_setV2( $page_id, $form );
        $filename = $this->prContainer->pbFileName;
        $search = $formStrArray[0];			//0はフォーム用HTML
        $html = "";
        $html .='<div class = "popup_content" >';
        $html .='<table><tr><td><fieldset><legend>検索条件</legend>';
        $html .='<input type="hidden" id="clear_'.$filename.'" value = "'.$this->prContainer->pbPageSetting['sech_form_num'].'" >';
        $html .= $search;
        $html .='</fieldset></td><td valign="bottom">';
        $html .='<input type="button" id="search_'.$page_id.'" value = "表示" class="free" data-action="popupAjax.php?id='.$page_id.'" onclick="ajaxSearch(\''.$page_id.'\')"></td>';
        $html .='</fieldset></td><td valign="bottom"><input type="button" value = "クリア" class="free" onclick="clearSearch(\''.$page_id.'\')"></td></tr></table>';
	
        return $html;
    }
    
}


        