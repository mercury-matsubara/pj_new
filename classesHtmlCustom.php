<?php

require_once("classesPageContainer.php");
require_once("classesBase.php");
require_once("classesHtml.php");
require_once("classesPageFactory.php");
require_once("classesExecute.php");

class csvImport extends InsertPage
{
    /**
     * 関数名: makeBoxContentBottom
     *   CSV取込画面上部のHTML文字列を作成する
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain()
    {
        $html = '<form name ="fileinsert" action="main.php" method="post" enctype="multipart/form-data" 
				onsubmit = "return check();">';
        $html .= '<div style="margin-top:1%;margin-left:12%" >';
        $html .= '<input type="file" id="csvSelect" name="sansyo" value="" />';
        $html .= '<br>';
        
        if($_SESSION['filename'] == 'USERCSVIMPORT_1')
        {
            $html .= '<p>ユーザーの取込情報は、<br>ユーザー名,パスワード,権限,表示名,印鑑用名称,識別コードの形式でCSVを作成してください。</p>';            
        }
        else if($_SESSION['filename'] == 'KOKYAKUCSVIMPORT_1')
        {
            $html .= '<p>顧客の取込情報は、<br>顧客名,郵便番号,住所1,住所2,TEL,FAX,担当者1,Eメール1,担当者2,Eメール2の形式でCSVを作成してください。</p>';
        }
        else if($_SESSION['filename'] == 'MITSUMORICSVIMPORT_1')
        {
            $html .= '<p>見積品目の取込情報は、<br>品目名,単位,単価,消費税率,備考の形式でCSVを作成してください。</p>';
        }

        
        $content = $this->prContainer->pbInputContent;
        if($content == "error")
        {
            $html .= '<a class = "error">CSVを取り込めませんでした。</a>';
        }
        $html .= '<input type="hidden" name = "step" value = "1" class="free">';
        return $html;
    }
    
    /**
     * 関数名: makeBoxContentBottom
     *   CSV取込画面下部のHTML文字列を作成する
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentBottom() 
    {
        
        $html = '<div class = "center"><input type="button" name = "Comp" value = "登録" class="free" style="margin-top:2%;margin-left:-15%" onclick="csvCheck()" >';
        $html .= '</form>';
        
        return $html;
    }
}


        