<?php

/**
 * 印刷用Pageクラス
 *  見積
 */
class PrintPage extends BasePage {

    protected $Code;            //見積コード/請求コード
    protected $AnkCode;         //案件コード		
    protected $Kenmei;          //件名
    protected $MitsumoriAte;    //見積宛
    protected $Kokyakutanto;    //顧客担当
    protected $Mitsumoribi;     //見積日
    protected $HeadTitle;       //見出し
    protected $tax8;            //税金8%
    protected $tax10;           //税金10%
    protected $kingakukei;      //税抜き合計
    protected $total;           //税込み総合計
    protected $filename;

    /**
     * 関数名: makeStylePart
     *   CSS定義文字列(HTML)を作成する関数
     * (基本的にはCSSファイルへのリンクを作成)
     * 
     * @retrun HTML文字列
     */
    function makeStylePart() {
        
        $html = '<link rel="stylesheet" type="text/css" href="./css/list_css.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./css/display.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./css/stamp.css">';
        $html .= '<link rel="stylesheet" type="text/css" href="./css/print.css" media="print">';
        return $html;
    }

    /**
     * 関数名: exequtePreHtmlFunc
     *   ページ用のHTMLを出力する前の処理
     */
    public function executePreHtmlFunc() {
        //親の処理
        parent::executePreHtmlFunc();
        $title1 = $this->prContainer->pbPageSetting['title'];
        //メンバ変数タイトル
        $this->prTitle = $title1;
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
        //親の処理を呼び出す
        $html = parent::makeScriptPart();
        //必要なHTMLを付け足す
        $html .= '<script src="./js/tabscript.js"></script>';
        $html .= '<script language="JavaScript"><!--
			$(function()
			{
				pageLoad();
				tabClick();
			});
			--></script>';

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
        $this->filename = $this->prContainer->pbFileName;
        if (strstr($this->filename, '_5') != false) {
            $this->prTitle = "";
            $html = "";
        }


        $columns = $this->prContainer->pbPageSetting['page_columns']; //画面設定値
        $columns_array = explode(',', $columns);       //画面設定値
        $Code = "form_" . $columns_array[0] . "_" . "0";
        //見積,請求コピー画面時、見積書表示選択時
        //GET情報がないためデータ取得する必要あり
        if (!isset($this->prContainer->pbInputContent[$Code])) {
            $this->prContainer->pbInputContent = make_post("", $this->prContainer->pbListId);
        }
        //見積コード/請求コード
        $frm_code = "form_" . $columns_array[0] . "_" . "0";
        $this->Code = $this->prContainer->pbInputContent[$frm_code];

        //見積件名	案件名?
        $Kenmei = "form_" . $columns_array[2] . "_" . "0";
        $this->Kenmei = $this->prContainer->pbInputContent[$Kenmei];
        //見積宛	
        $MitsumoriAte = "form_" . $columns_array[3] . "_" . "0";
        $this->MitsumoriAte = $this->prContainer->pbInputContent[$MitsumoriAte];
        //顧客担当者
        $Kokyakutanto = "form_" . $columns_array[4] . "_" . "0";
        $this->Kokyakutanto = $this->prContainer->pbInputContent[$Kokyakutanto];
        //見積日
        $Mitsumoribi = "form_" . $columns_array[5] . "_" . "0";
        $this->Mitsumoribi = $this->prContainer->pbInputContent[$Mitsumoribi];
        // 見出し設定
        if($this->filename === "MITSUMORIPRINT_5") {
            $this->HeadTitle = "見積書";
        } else {
            $this->HeadTitle = "請求書";
        }
        // 税抜き金額
        $kingakukei = "form_" . $columns_array[10] . "_" . "0";
        $this->kingakukei = $this->prContainer->pbInputContent[$kingakukei];
        // 税込み金額
        $total = "form_" . $columns_array[11] . "_" . "0";
        $this->total = $this->prContainer->pbInputContent[$total];
        // 税率8%
        $this->tax8 = $this->prContainer->pbInputContent['zei8'];
        // 税率10%
        $this->tax10 = $this->prContainer->pbInputContent['zei10'];
        
        
        return $html;
    }

    /**
     * 関数名: makeBoxContentMain
     *   メインの機能提供部分のHTML文字列を作成する
     *   リストでは一覧表示、入力では各入力フィールドの構築など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain() {
        
        // 印刷画面作成
        $html = $this->makePrintMainPage($this->Mitsumoribi,$this->Code,$this->MitsumoriAte,$this->HeadTitle);
        $html .= $this->makePrintDetailPage($this->Mitsumoribi, $this->prContainer);
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
        
        $html = "";
        if($this->filename !== "MITSUMORIPRINT_5"){
            $html .= '<div id="print" class = "print">';
            $html .= '<label><input type="checkbox" id="copy" checked ><span class="checkbox">控</span></label><br>';
            $html .= '<label><input type="checkbox" id="delively"><span class="checkbox">納品書</span></label><br>';
            $html .= '<input type="button" value="印刷" id="print" class="print"  onClick="window.print()">';
            $html .= '</div>';
            // 控え印刷作成
            $html .= $this->makePrintMainPage($this->Mitsumoribi, $this->Code, $this->MitsumoriAte, $this->HeadTitle, $flg = 2);
            $html .= $this->makePrintDetailPage($this->Mitsumoribi, $this->prContainer, $flg = 2);
            $html .= $this->makePrintMainPage($this->Mitsumoribi, $this->Code, $this->MitsumoriAte, $this->HeadTitle, $flg = 3);
            $html .= $this->makePrintDetailPage($this->Mitsumoribi, $this->prContainer, $flg = 3);
        } else {
            $html .= '<div id="print" class = "print">';
            $html .= '<input type="button" value="印刷" id="print" class="print"  onClick="window.print()">';
            $html .= '</div>';
        }
        
        return $html;
    }
    
    /**
     * 関数名: makePrintMainPage
     * 印刷画面上部作成
     * 
     * @retrun HTML文字列
     */
    function makePrintMainPage($datetitle, $code, $mitsumoriAte,$headtitle,$flg = 1){
         
        // 控えか判定
        if($flg === 2){
            $id = "id='copyprint'";
            $class = "printpage2";
        }else if($flg === 3){
            $id = "id='delivelyprint'";
            $class = "printpage3 dispNone";
        }else{
            $id="";
            $class = "printpage";
        }

        $print = "<div $id class='$class'>";
        //日付分解
        $datearray = explode('/', $datetitle);
        //配列になっていなかったら
        if (count($datearray) != 3) {
            $datearray = explode('-', $datetitle);
        }
            //自社情報
        if (array_key_exists('SYAMEI', $_SESSION) === false) {
            loadJisyaMaster();
        }
        /* 日付、請求No */
        // 控えか判定
        if($flg === 2){
            $print .= "<div id='secondDateBox' class='secondDateBox'>";
        }else if($flg === 3){
            $print .= "<div id='thirdDateBox' class='thirdDateBox'>";
        }else{
            $print .= "<div class='firstDateBox'>";
        }
        $print .= "<table class='date'>";
        $print .= "<tbody>";
        $print .= "<tr>";
        $print .= "<td>日付:</td>";
        $print .= "<td>$datearray[0]年$datearray[1]月$datearray[2]日</td>";
        $print .= "</tr>";
        $print .= "<tr>";
        if($flg === 1 || $flg === 2)
        {
            $print .= "<td>$headtitle No:</td>";
        } else if($flg === 3) {
            $print .= "<td>納品書 No:</td>";
        }
        $print .= "<td>$code</td>";
        $print .= "</tr>";
        $print .= "</tbody>";
        $print .= "</table>";
        $print .= "</div>";

        //見出し
        if($flg === 2){
            // 控え記入あり
            $print .= "<h2>$headtitle</h2>";
            $print .= "<p class='hikae'>（控）</p>";
        }else if($flg === 3){
            $print .= "<h2>納品書</h2>";
        }else{
            $print .= "<h2>$headtitle</h2>";
        }

        ///件名や金額
        $print .= "<div>";
        //企業名
        $print .= "<div class='kigyo'>";
        $print .= "<span>$mitsumoriAte</span>";
        $print .= "</div>";
        /* 固定部分 */
        $print .= "<div class='kotei'>";
        $print .= "<span>御中</span>";
        $print .= "</div>";
        $print .= "</div>";
    //    $print .= "<div class='kenmei'><span>件名: $kenmei</span></div></div>";
        //ハンコ画像
        if ($flg === 1){
            $print .= "<div id='hanko' class='hanko'>";
        } else if($flg === 2){
            $print .= "<div id='hanko2' class='hanko2'>";
        } else if($flg === 3){
            $print .= "<div id='hanko3' class='hanko3'>";
        }

        $print .= "<img src='./image/mercury.png' class='png'>";
        $print .= "</div>";
        //金額、有効期限、自社情報、印鑑部分の枠
        $print .= "<div class='box'>";
        $print .= "<div class='goukeibox'>";
        $print .= "<span>下記の通りお見積り申し上げます。</span>";

        return $print;
    }

    /**
     * 関数名: makePrintDetailPage
     * 印刷画面詳細部作成
     * 
     * @retrun HTML文字列
     */
    function makePrintDetailPage($MitusumoriDate, &$content, $flg = 1) {

        //------------------------//
        //        初期設定        //
        //------------------------//
        require_once ("f_DB.php");
        require_once ("f_Form.php");
        require_once ("f_SQL.php");
        //------------------------//
        //          定数          //
        //------------------------//
        $post = $content->pbInputContent;
        $value = $content->pbPageSetting;
        $form_ini = $content->pbFormIni;
        $filename = $content->pbFileName;
        $columns = $value['page_columns'];                              //画面設定値
        $columns_array = explode(',', $columns);                        //画面設定値
        $filename_M = $filename . "_M";                                 //ヘッダ明細画面名
        $columns_M = $form_ini[$filename_M]['page_columns'];            //ヘッダ明細設定値	
        $columns_M_array = explode(',', $columns_M);                    //ヘッダ明細設定値
        //軽減税率用※変数
        $taxmark = "※";
        $date = 0;

        //日付
        $stampdate = date('Y.n.j', strtotime($MitusumoriDate));

        //有効期限,支払期限
        if ($filename=="MITSUMORIPRINT_5"){
            $Kigen = "有効期限";
        } else {
            $Kigen = "お支払期限";
        }
        $Yukoukigen = "form_" . $columns_array[8] . "_" . "0";
        $Yukoukigen = $post[$Yukoukigen];
        if (strstr($Yukoukigen, '-')) {
            $Yukoukigen = date('Y年n月j日', strtotime($Yukoukigen));
            $date = 1;
        }
        if (strstr($Yukoukigen, '/')) {
            $Yukoukigen = date('Y年n月j日', strtotime($Yukoukigen));
            $date = 1;
        }

        //備考
        $Bikou = "form_" . $columns_array[9] . "_" . "0";
        if (!isset($post[$Bikou])) {
            $Bikou = "";
        } else {
            $Bikou = $post[$Bikou];
            $Bikou = nl2br($Bikou);
        }

        //見積、請求コピー画面時、見積表示選択時 明細の入力値を取得
        $hinmei = "form_" . $columns_M_array[1] . "_" . "0" . "_0";
        if (!isset($post[$hinmei])) {
            $userid = $post['USRID'];
            $use_code = $form_ini[$filename]['use_maintable_num'];
            $post = make_headerpost($filename_M, $content->pbListId, $use_code);
        }

        //ログインユーザー情報取得
        $usercolumn = "form_" . $columns_array[6] . "_" . "0";
        if (isset($post[$usercolumn])) {
            $userid = $post[$usercolumn];
        }

        $uservalue = loginUserValue($userid);

        $stamp01 = '<div class="stamp stamp-approve"><span>' . $stampdate . '</span><span>MCS</span></div>'; //承認
        $stamp02 = '<div class="stamp stamp-audit"><span></span><span></span></div>';    //審査
        $stamp03 = '<div class="stamp stamp-write"><span>' . $stampdate . '</span><span>' . $uservalue['STAMPNAME'] . '</span></div>'; //担当
        //自社情報
        if (array_key_exists('SYAMEI', $_SESSION) === false) {
            loadJisyaMaster();
        }
        $total = number_format($this->total);
        $print = "<div class='kingaku'>金額計(税込)　　　&yen $total</div>";
        if ($flg === 1 || $flg === 2){
            $print .= "<div class='kigen'>" . $Kigen . "　　　            " . $Yukoukigen . "</div>";
        }
        
        $print .= "</div>";
        $print .= "<div class='addressBox'>";
        $print .= "<p class='name'>" . $_SESSION['SYAMEI'] . "</p>";
        $print .= "<p class='address'>〒" . $_SESSION['YUBIN'];
        $print .= "</br>";
        $print .= $_SESSION['JYUSHO1'];
        $print .= "</br>";
        $print .= $_SESSION['JYUSHO2'];
        $print .= "</br>";
        $print .= "TEL：" . $_SESSION['TEL'] . " FAX：" . $_SESSION['FAX'] . "";
        $print .= "</p>";
        $print .= "<p class='tantousya'>担当者：" . $uservalue['HYOJIMEI'] . " </p>";
        $print .= "</div>";
        $print .= "</div>";

        //明細部分
        $print .= "<div class='meiaibox' >";
        $print .= "<table class='meisai' border='1' align='center'>";
        $print .= "<tbody>";
        $print .= "<tr class='color'>";
        $print .= "<td width='300' align='center'>品名</td>";
        $print .= "<td width='120' align='center'>単価</td>";
        $print .= "<td width='120' align='center'>数量</td>";
        $print .= "<td width='120' align='center'>金額</td>";
        $print .= "</tr>";

        for ($i = 0; $i < 15; $i++) {
            //品名
            $hinmei = "form_" . $columns_M_array[1] . "_" . "0" . "_" . $i;
            $hinmei = $post[$hinmei];
            //単価
            $tanka = "form_" . $columns_M_array[2] . "_" . "0" . "_" . $i;
            $tanka = $post[$tanka];
            //数量
            $suryo = "form_" . $columns_M_array[3] . "_" . "0" . "_" . $i;
            $suryo = $post[$suryo];
            //単位
            $tani = "form_" . $columns_M_array[4] . "_" . "0" . "_" . $i;
            $tani = $post[$tani];
            //金額
            $money = "form_" . $columns_M_array[5] . "_" . "0" . "_" . $i;
            $money = $post[$money];
            //税率
            $zei = "form_" . $columns_M_array[6] . "_" . "0" . "_" . $i;
            $zei = $post[$zei];

            if (($i % 2) == 1) {
                $id = 'class = "color"';
            } else {
                $id = 'class = "backcolor"';
            }

            $print .= "<tr $id>";
            //品名
            if ($zei == "8") {//税率判定
                //軽減税率の場合　※記入 半角スペース10行
                $print .= "<td height='27' align='left' >$taxmark$hinmei</td>";
            } else {
                $print .= "<td height='27' align='left' >　$hinmei</td>";
            }
            //単価
            if ($tanka != 0) {
                $tanka_print = number_format($tanka);
                $print .= "<td  align='right'>&yen $tanka_print</td>";
            } else {
                $tanka = "";
                $print .= "<td  align='right'></td>";
            }
            //数量
            if ($suryo != 0) {
                $suryo_print = number_format($suryo);
                //単位が円なら表示しない、一式なら感じで表示する
                if ($tani == "円") {
                    $print .= "<td  align='center'>$suryo_print</td>";
                } else if ($tani == "一式") {
                    $print .= "<td  align='center'>一式</td>";
                } else {
                    $print .= "<td  align='center'>$suryo_print$tani</td>";
                }
            } else {
                $suryo = "";
                $print .= "<td  align='center'></td>";
            }
            //金額
            if ($money != 0) {
                $money = number_format($money);
                $print .= "<td  align='right'>&yen $money</td>";
            } else {
                $money = "";
                $print .= "<td  align='right'></td>";
            }
            $print .= "</tr>";
        }

        $print .= "</tbody>";
        $print .= "</table>";
        $print .= "<table border='1'  align='center' class='goukei'>";
        $print .= "<tbody>";
        $kingakukei = number_format($this->kingakukei);
        $tax8 = number_format($this->tax8);
        $tax10 = number_format($this->tax10);
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>小計</td><td class='backcolor' align='right'>&yen $kingakukei</td></tr>";
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>消費税（8％）</td><td class='backcolor' align='right'>&yen $tax8</td></tr>";
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>消費税（10％）</td><td class='backcolor' align='right'>&yen $tax10</td></tr>";
        $print .= "<tr class='frame'><td height='30' align='center' class='color'>総合計</td><td class='backcolor' align='right'>&yen $total</td></tr>";
        $print .= "</tbody>";
        $print .= "</table>";
        $print .= "</div>";

        if ($flg === 1 || $flg === 2)
        {
            $print .= "<div class='goannaiBox'>";
            $print .= "<p>$Bikou</p>";
        } else if ($flg === 3) {
            $print .= "<div class='bikouBox'>"; 
        }
        
        $print .= "</div>";
        
        //印鑑押す
        if ($flg === 1 || $flg === 2){
                $print .= "<div class='kaishahanko' >";
            $print .= "<table class='inkan' border='1' align='right' >";
            $print .= "<tbody>";
            $print .= "<tr>";
            $print .= "<td width='80' align='center'>承認</td>";
            $print .= "<td width='80' align='center'>審査</td>";
            $print .= "<td width='80' align='center'>担当</td>";
            $print .= "</tr>";
            $print .= "<tr>";
            $print .= "<td height='80'>$stamp01</td>"; //承認
            //$print .= "<td>$stamp02</td>";
            $print .= "<td></td>"; //審査
            $print .= "<td>$stamp03</td>"; //担当
            $print .= "</tr>";
            $print .= "</tbody>";
            $print .= "</table>";
            $print .= "</div>";
            $print .= "</div>";
        }

        return ($print);
    }
}

