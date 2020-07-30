<?php

/*
 * 社員別金額設定画面作成
 * 
 */
class StaffMoneySet extends ListPage
{
    protected $data;
    
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

        $html .= '<script src="./customJS/staffMoneySet.js"></script>';

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
        $html .= '<link rel="stylesheet" type="text/css" href="./customCSS/staffMoneySet.css">';

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
            //DB接続、トランザクション開始
            $con = beginTransaction();
            
            if(!isset($_SESSION['list']))
            {
                    $_SESSION['list'] = array();
            }
            //検索フォーム作成,日付フォーム作成
            $this->getInfo($this->prContainer->pbInputContent,$con);
            $this->setSearchSession($this->prContainer->pbInputContent);
            $formStrArray = $this->makeformSearch_setV2( $this->prContainer->pbInputContent, 'form' );
            $form = $formStrArray[0];			//0はフォーム用HTML
            $this->prInitScript = $formStrArray[1];	//1は構築用スクリプト
            
            //社員別金額取得
            $data_sql = "";
            $judge = false;
            $count = 0;
            $data_sql = "SELECT * FROM (SELECT syaininfo.STAFFID,syaininfo.STAFFNAME,projectditealinfo.DETALECHARGE,projectditealinfo.4CODE,projectditealinfo.5CODE FROM projectditealinfo "
                    . "LEFT JOIN syaininfo ON projectditealinfo.4CODE = syaininfo.4CODE ) AS syaininfo WHERE 5CODE = ".$this->prContainer->pbInputContent['form_pjt5CODE_0'].";";
            $data_reply = $con->query($data_sql) or ($judge = true);																		// クエリ発行
            if($judge)
            {
                    error_log($con->error,0);
            }
            while($result_row = $data_reply->fetch_array(MYSQLI_ASSOC))
            {
                    $this->data[$count]['STAFFID'] =  $result_row['STAFFID'];
                    $this->data[$count]['DETALECHARGE'] = $result_row['DETALECHARGE'];
                    $count++;
            }
            //トランザクションコミットまたはロールバック
            commitTransaction($data_reply,$con);
            
            //検索SQL
            $sql = array();
            $sql[] = "SELECT * FROM (SELECT syaininfo.STAFFID,syaininfo.STAFFNAME,projectditealinfo.DETALECHARGE,projectditealinfo.4CODE,projectditealinfo.5CODE FROM projectditealinfo "
                    . "LEFT JOIN syaininfo ON projectditealinfo.4CODE = syaininfo.4CODE) AS syaininfo GROUP BY STAFFID;";
            $sql[] = "SELECT COUNT(*) FROM syaininfo ;";
            $limit = $this->prContainer->pbInputContent['list']['limit'];				// limit
            $limit_start = $this->prContainer->pbInputContent['list']['limitstart'];	// limit開始位置

            //リスト表示HTML作成
            $pagemove = intval( $this->prContainer->pbPageSetting['isPageMove'] );
            $list =  $this->makeListV2($sql, $_SESSION['list'], $limit, $limit_start, $pagemove);

            $checkList = $_SESSION['check_column'];

            //出力HTML作成
            $html ='<div class = "pad" >';
            $html .='<form name ="form" action="main.php?STAFFMONEYSET_1=" method="post"id="staffMoneySet" onsubmit = "return check(\''.$checkList.'\');">';
            $html .='<table><tr><td><fieldset><legend>検索条件</legend>';
            $html .= $form;								//検索項目表示

            $html .= '</table></form>';
            
            //合計金額計算
            $total = 0;
            for($i=0;$i<count($this->data);$i++)
            {
                $total += $this->data[$i]['DETALECHARGE']; 
            }
            $html .= '<div>合計金額：<input type=text class="readOnly" id="total" value="'.$total.'" readonly></div>';
            $html .= $list;
            $html .= '</form></br>';
            
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
            //ダイアログ
            $html = '<div id="set_dialog" title="処理確認" style="display:none;">
                                    <p></p>
                                    </div>';
            $html .= '<div id="clear_dialog" title="処理確認" style="display:none;">
                                    <p>社員別金額情報をクリアしますか？</p>
                                    </div>';
            $html .= '<div id="delete_dialog" title="処理確認" style="display:none;">
                                    <p>プロジェクトを削除しますか？</p>
                                    </div>';
        
            $html .= '<div class = "left" style = "HEIGHT : 30px"><form action="main.php" method="get">';
            //新規作成ボタン作成
            global $button_ini;
            if( $button_ini === null)
            {
                    // ボタン設定読込み
                    $button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
            }
            
            $html .= '<input type ="button" value = "設定" class = "free" name="Comp" onclick="setMoney()">';
            $html .= '<input type ="button" value = "クリア" class = "free" name="Comp" onclick="clearMoney()" >';
            $html .= '<input type ="button" value = "プロジェクト削除" class = "free" name="Comp" onclick="deletePj()">';
            
            $html .= '</form></div>';

            return $html;
    }
    /*
     * プロジェクトナンバ、枝番、製版・案件名取得
     */
    function getInfo($post,$con)
    {
        $judge = false;
        $result = true;
        
        
        $pjnum_sql = "SELECT PROJECTNUM FROM projectnuminfo WHERE 1CODE = ".$post['form_pjt1CODE_0'].";";
        $pjnum_reply = $con->query($pjnum_sql) or ($judge = true);																		// クエリ発行
        if($judge)
        {
                error_log($con->error,0);
                $result =false;
        }
        while($result_row = $pjnum_reply->fetch_array(MYSQLI_ASSOC))
        {
                $this->prContainer->pbInputContent['form_pjtPROJECTNUM_0'] = $result_row['PROJECTNUM'] ;
        }
        
        $edaban_sql = "SELECT * FROM edabaninfo WHERE 2CODE = ".$post['form_pjt2CODE_0'].";"; 
        $edaban_reply = $con->query($edaban_sql) or ($judge = true);																		// クエリ発行
        if($judge)
        {
                error_log($con->error,0);
                $result =false;
        }
        while($result_row = $edaban_reply->fetch_array(MYSQLI_ASSOC))
        {
                $this->prContainer->pbInputContent['form_pjtEDABAN_0'] = $result_row['EDABAN'] ;
                $this->prContainer->pbInputContent['form_pjtPJNAME_0'] = $result_row['PJNAME'] ;
        }
    }    
    /*
     * function makeTableTd($sql,$post)
     * 
     * 引数1	$sql					検索SQL
     * 引数2	$post				入力情報
     * 
     * 戻り値	result				リストhtml
     */
    function makeTableTd( $class_origin, &$columns_array, &$column_width_array, &$herf_link_array, &$result_row, $table_id, $rowNo )
    {
            //戻り値
            $rowHtml ='';//行開始

            $isCheckBox = $this->prContainer->pbPageSetting['isCheckBox'];
            $isNo = $this->prContainer->pbPageSetting['isNo'];
            $isEdit = $this->prContainer->pbPageSetting['isEdit'];
            $disabled = '';

            //チェックボックス
            if($isCheckBox != 0)
            {
                    if($isCheckBox == 1)
                    {
                            $code = getCode($this->prFileNameInsert);
                            //チェックボックス　　　　　　!!!当面非対応
//				$rowHtml .= "<td id = ".$table_id."".$result_row['1CODE']."class = 'center'><input type = 'checkbox' class = 'checkBox' name ='check_".$result_row[$code]."' id = 'checkid_".$result_row[$code]."'";
                            $rowHtml .= "<td class = 'center'><input type = 'checkbox' class = 'checkBox' name ='check_".$result_row[$code]."' id = 'checkid_".$result_row[$code]."'";
                            if(isset($post['check_'.$result_row[$code]]))
                            {
                                    $rowHtml .= " checked ";
                            }
                            $rowHtml .=' onclick="this.blur();this.focus();" ></td>';
                    }
                    else
                    {
                            $code = getCode($this->prFileNameInsert);
                            //ラジオボタン
                            $rowHtml .="<td class = '".$class_origin." center'><input type = 'radio' name ='frmSAIYO' id = 'frmSAIYO' value='".$result_row[$code]."'>";
                    }
            }
            //No.表示
            if($isNo == 1)
            {
                    $rowHtml .="<td class='".$class_origin." sequence'><a class='body'>".$rowNo."</a></td>";
            }

            //実データ列
            for($i = 0 ; $i < count($columns_array) ; $i++)
            {
                    $column = $columns_array[$i];
                    if($column === '' || $column === 'sp01' || $column === 'sp02' )
                    {	//ブランクは飛ばす
                            $rowHtml .= '<td class="center">'.$column.'</td>';
                            continue;
                    }
                    //何度も見るので設定値を最初に絞る
                    $column_setting = $this->prContainer->pbParamSetting[$column];
                    //設定ファイルから設定値を取得
                    $field_name = $column_setting['column'];
                    $format     = $column_setting['format'];
                    $type       = $column_setting['form1_type'];
                    $valigin    = $column_setting['list_align'];
                    $value = $result_row[$field_name];

                    //フォーマット指定
                    if($format != 0)
                    {
                            $value = format_change($format, $value, $type);
                    }

                    //リンク指定の有無
                    if(count($herf_link_array) > $i)
                    {
                            //リンク指定あり？
                            if($herf_link_array[$i] !='')
                            {
                                    $href = '';
                                    if( $type === '2' )
                                    {
                                            $href = 'file/'.$value;
                                    }
                                    else
                                    {
                                            $link_to = $herf_link_array[$i];
                                            $link_key = $column_setting['link_key'];

                                            $href = "main.php?".$link_to."_button=&edit_list_id=".$result_row[$link_key];
                                    }

                                    //パラメータ追加
                                    $href .= $this->makeGetAdditionalListParam($column);

                                    //リンクありの場合、値を<a href >で囲む
                                    $value = "<a href='".$href."'>".$value."</a>";
                                    //$value = "<a href='main.php?".$link_to."_button=&form_usr$link_key"."_0=".$result_row[$link_key]."'>".$value."</a>";
                            }
                    }

                    //列幅指定
                    $td_width = "";
                    if(count($column_width_array) > $i)
                    {
                            //列幅の固定？
                            if($column_width_array[$i]=='1')
                            {
                                    //固定であるなら、サイズ指定を使用して幅を設定
                                    $width = $column_setting['form1_size'] * 4;
                                    $td_width = " style='width:".$width."px;'";
                            }
                    }

                    //数値の場合は右寄せ
                    switch($valigin)
                    {
                    case 1:
                            $class = $class_origin." center";
                            break;
                    case 2:
                            $class = $class_origin." right";
                            break;
                    default:
                            //$class = "";
                            $class = "textoverflow";
                    }
                    
                    //テキストボックス作成
                    if($type === "8")
                    {
                        $value = "<input type=text class='money' onchange='calculateReturn()' >";
                        
                        for($j=0;$j<count($this->data);$j++)
                        {                            
                            if($result_row['STAFFID'] == $this->data[$j]['STAFFID'])
                            {
                                $value = "<input type=text class='money' value='".$this->data[$j]['DETALECHARGE']."' onchange='calculateReturn()' >";
                            }
                        }
                    }
                    //書き込み
                    $rowHtml .="<td class='".$class."'".$td_width." ><a class ='body'>".$value."</a></td>";
            }

            //編集ボタン
            if($isEdit == 1)
            {
                    $code = getCode($this->prFileNameInsert);
                    $rowHtml .= "<td class='".$class_origin." edit' valign='top'><input type='submit' name='edit_".
                                                    $result_row[$code]."_MoneySet' value = '編集' ".$disabled."></td>";
            }
            return $rowHtml;
    }
    /*
     * function makeformSearch_setV2($post,$form_name)
     * 
     * 引数	$post
     * 戻り値	なし
     */
    function makeFormSearchElement( $column, &$form_ini, $post, $element_name, $main_form_name )
    {
            //
            $serch_str = '';
            $after_script = '';
            $check_column_str = '';


            $form_format_type = $form_ini[$column]['form1_type'];

            //POSTされた値があるか
            $form_value = "";
            if(isset($post[$element_name]))
            {
                    $form_value = $post[$element_name];
            }

            // 判定基準入れ替え
            if($form_format_type == 3)
            {
                    //日付コントロール
                    $datepickerArray = datepickerDate_set( $element_name, $post );
                    $serch_str.= $datepickerArray[0];
                    $after_script.= $datepickerArray[1];
            }
            else if($form_format_type == 4)
            {
                    /********** 日時コントロール **********/
                    $serch_str .= '<input type ="text" name = "'.$element_name.'" id = "'.$element_name.'" value = "'.$form_value.'" size = "'.$form_ini[$column]['form1_size'].'"  >';
                    $after_script .= "$('#".$element_name."').datetimepicker();";
            }
            else if($form_format_type == 9)
            {
                if(isset($form_ini[$column]['sp']))
                {
                    //HTMLを取得
                    $serch_str.= $this->pulldown_setV3($element_name, $post, "", "form", 0);
                }
                else
                {
                    //プルダウン指定を取得
                    $pulldpwn = $form_ini[$column]['pul_num'];
                    //HTMLを取得
                    $serch_str.= $this->pulldown_setV2($pulldpwn, $element_name, $form_value, false, $main_form_name, false, true);
                }
            }
            else
            {
                    //その他テキスト

                    //INI設定値
                    $form_size = $form_ini[$column]['form1_size'];
                    $form_format = $form_ini[$column]['form1_format'];
                    $form_length = $form_ini[$column]['form1_length'];
                    $form_delimiter = $form_ini[$column]['form1_delimiter'];
                    $form_align = $form_ini[$column]['list_align'];

                    $input_type = 'text';
                    $check_js = 'onChange = " return inputcheck(\''.$element_name.'\','.$form_length.','.$form_format.',false,2)"';
                    $check_column_str .= $element_name."~".$form_length."~".$form_format."~".false."~2,";

                    //IME制御
                    if($form_align === 2)
                    {
                            $form_input_type = ' class = "txtmode3"';
                    }
                    else
                    {
                            $form_input_type = ' class = "readOnly txtmode2"';
                    }

                    if( $form_format > 4 )
                    {
                            $form_input_type = ' class = "readOnly txtmode1"';
                    }

                    $serch_str .= $form_delimiter.'<input type ="'.$input_type.'" name = "'.$element_name.'" id = "'.$element_name.'" value = "'.$form_value.
                                                    '" size = "'.$form_size.'" '.$check_js.$form_input_type.' readonly >';
            }

            $result_array = array();

            $result_array[0] = $serch_str;
            $result_array[1] = $after_script;
            $result_array[2] = $check_column_str;

            return $result_array;
    }
}