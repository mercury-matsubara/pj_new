<?php
class Progress extends InsertPage
{
    public $errorCode = "";
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
        
        $_SESSION['pre_post'] = null;
        $errorinfo ='';

        if(isset($_SESSION['error']))
        {
            $errorinfo = $_SESSION['error'];
            $_SESSION['error'] = "";
        }

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

        if($this->errorCode !== "")
        {
            $code = $this->errorCode;
            $html .= 'alert("'.$code.'");';
            $this->errorCode = "";
        }

        $html .= '</script>';
        return $html;
    }
}