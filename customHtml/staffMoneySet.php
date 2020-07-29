<?php

/*
 * 社員別金額設定画面作成
 * 
 */
class StaffMoneySet extends ListPage
{
    /**
     * 関数名: makeBoxContentMain
     *   メインの機能提供部分の上部に表示されるHTML文字列を作成する
     *   機能名の表示など
     * 
     * @retrun HTML文字列
     */
    function makeBoxContentMain()
    {
            if(!isset($_SESSION['list']))
            {
                    $_SESSION['list'] = array();
            }
            //検索フォーム作成,日付フォーム作成
            $this->getInfo($this->prContainer->pbInputContent);
            $this->setSearchSession($this->prContainer->pbInputContent);
            $formStrArray = $this->makeformSearch_setV2( $this->prContainer->pbInputContent, 'form' );
            $form = $formStrArray[0];			//0はフォーム用HTML
            $this->prInitScript = $formStrArray[1];	//1は構築用スクリプト
            
            //検索SQL
            $sql = array();
//            $sql[] = "SELECT * FROM (SELECT syaininfo.STAFFID,syaininfo.STAFFNAME,projectditealinfo.DETALECHARGE,projectditealinfo.4CODE,projectditealinfo.5CODE FROM projectditealinfo "
//                    . "LEFT JOIN syaininfo ON projectditealinfo.4CODE = syaininfo.4CODE ) AS syaininfo WHERE 5CODE = ".$this->prContainer->pbInputContent['form_pjt5CODE_0'].";";
//            $sql[] = "SELECT COUNT(*) FROM (SELECT syaininfo.STAFFID,syaininfo.STAFFNAME,projectditealinfo.DETALECHARGE,projectditealinfo.4CODE,projectditealinfo.5CODE FROM projectditealinfo "
//                    . "LEFT JOIN syaininfo ON projectditealinfo.4CODE = syaininfo.4CODE ) AS syaininfo WHERE 5CODE = ".$this->prContainer->pbInputContent['form_pjt5CODE_0'].";";
            $sql[] = "SELECT * FROM (SELECT syaininfo.STAFFID,syaininfo.STAFFNAME,projectditealinfo.DETALECHARGE,projectditealinfo.4CODE,projectditealinfo.5CODE FROM projectditealinfo "
                    . "LEFT JOIN syaininfo ON projectditealinfo.4CODE = syaininfo.4CODE) AS syaininfo GROUP BY STAFFID;";
            $sql[] = "SELECT COUNT(*) FROM (SELECT syaininfo.STAFFID,syaininfo.STAFFNAME,projectditealinfo.DETALECHARGE,projectditealinfo.4CODE,projectditealinfo.5CODE FROM projectditealinfo "
                    . "LEFT JOIN syaininfo ON projectditealinfo.4CODE = syaininfo.4CODE) AS syaininfo GROUP BY STAFFID;";
            $limit = $this->prContainer->pbInputContent['list']['limit'];				// limit
            $limit_start = $this->prContainer->pbInputContent['list']['limitstart'];	// limit開始位置

            //リスト表示HTML作成
            $pagemove = intval( $this->prContainer->pbPageSetting['isPageMove'] );
            $list =  $this->makeListV2($sql, $_SESSION['list'], $limit, $limit_start, $pagemove);

            $checkList = $_SESSION['check_column'];

            //出力HTML作成
            $html ='<div class = "pad" >';
            $html .='<form name ="form" action="main.php" method="get"onsubmit = "return check(\''.$checkList.'\');">';
            $html .='<table><tr><td><fieldset><legend>検索条件</legend>';
            $html .= $form;								//検索項目表示

            $html .= '</table></form>';
            $html .= '<div>合計金額：<input type = text></div>';
            $html .= $list;
            
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
            $html = '<div class = "left" style = "HEIGHT : 30px"><form action="main.php" method="get">';
            //新規作成ボタン作成
            global $button_ini;
            if( $button_ini === null)
            {
                    // ボタン設定読込み
                    $button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
            }
            //新規作成ページに指定されているIDがbutton.iniにあるか？
//            if( array_key_exists( $this->prFileNameInsert, $button_ini ) === true )
//            {
//                    $html .= '<input type ="submit" value = "新規作成" class = "free" name = "'.$this->prFileNameInsert.'_button">';
//            }
//            //CSVボタン
//            $is_csv = $this->prContainer->pbPageSetting['isCSV'];
//            if( $is_csv === '1' )
//            {
//                    $html .='　<a href="csv_out.php?id='.$this->prContainer->pbFileName.'&'.$_SERVER['QUERY_STRING'].'" target="_blank" class="btn-radius">CSV出力</a>';
//            }
            $html = '</form></div>';

            return $html;
    }
    /*
     * プロジェクトナンバ、枝番、製版・案件名取得
     */
    function getInfo($post)
    {
        $judge = false;
        $result = true;
        
        //DB接続、トランザクション開始
        $con = beginTransaction();
        
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
        
        //トランザクションコミットまたはロールバック
        commitTransaction($result,$con);
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
                        $value = "<input type = text value = >";
                        //$value = "<input type = text value = ".$result_row['DETALECHARGE'].">";
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
}