<?php
/**
 * PJ進捗画面
 * 
 */
class Progress extends InsertPage
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

   
}