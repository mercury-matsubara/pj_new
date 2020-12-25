<?php

/**
 * PJ登録画面作成
 * 
 */
class Pjinsert extends InsertPage {
    
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
     * 関数名: makeBoxContentMain
     * 
     *   メインの機能提供部分のHTML文字列を作成する
     *   リストでは一覧表示、入力では各入力フィールドの構築など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain() {
        
        $html = parent::makeBoxContentMain();
        //$html .= $this->createPopUp();
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
//        $html .="<p>サンプル</p>";
        return $html;
        
    }

}

