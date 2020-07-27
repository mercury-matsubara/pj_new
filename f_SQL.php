<?php




/////////////////////////////////////////////////////////////////////////////////////
//                                                                                 //
//                                                                                 //
//                             ver 1.1.0 2014/07/03                                //
//                                                                                 //
//                                                                                 //
/////////////////////////////////////////////////////////////////////////////////////


/************************************************************************************************************
function InsertSQL($post,$tablenum,$over)

引数	$post

戻り値	なし
************************************************************************************************************/
function InsertSQL( &$post, $tablenum, $over, $filename ){
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
	require_once 'f_DB.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	//$columns = $form_ini[$filename]['insert_form_tablenum'];
	$columns = $form_ini[$filename]['page_columns'];
	$columns_array = explode(',',$columns);
	$tableName = $form_ini[$tablenum]['table_name'];
	$mastertablenum = $form_ini[$tablenum]['seen_table_num'];
	$mastertablenum_array = explode(',',$mastertablenum);
	//$table_columns = $form_ini[$tablenum]['insert_form_num'];
	if(isset($form_ini[$tablenum]['page_columns']))
	{	
		$table_columns = $form_ini[$tablenum]['page_columns'];
		$table_columns_array = explode(',',$table_columns);
	}
	$update_column = $form_ini[$filename]['auto_ins_column_num'];
	$update_value = $form_ini[$filename]['auto_ins_column_value'];
	$update_column_array = explode(',',$update_column);
	$update_value_array = explode(',',$update_value);

	//------------------------//
	//          変数          //
	//------------------------//
	$columnName = "";
	$columnValue = "";
	$insert_SQL = "";
	$singleQute = "";
	$fieldtype = "";
	$serch_str = "";
	$formtype ="";
	$delimiter = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	$insert_SQL .= "INSERT INTO ".$tableName." (";
	
	// 項目名の構築
	for($i = 0 ; $i < count($columns_array) ; $i++)
	{
		//ページの設定からINSERT項目を読み出し(優先)
		if(isset($form_ini[$columns_array[$i]]['column']) == true)
		{
			//フォーマット指定を取得
			$format = $form_ini[$columns_array[$i]]['form1_format'];
			//DBによるAutoIncrement項目は含めない
			if($format === '7')
			{
				continue;
			}
			//列名を取得してつけたし
			$columnName = $form_ini[$columns_array[$i]]['column'];
			$insert_SQL .= $columnName.",";
		}
		//テーブルの設定からINSERT項目を読み出し(ページの設定がない場合)
		else if($tablenum == $columns_array[$i])
		{
			for($k = 0 ; $k < count($table_columns_array) ; $k++)
			{
				$columnName = $form_ini[$table_columns_array[$k]]['column'];
				$insert_SQL .= $columnName.",";
			}
		}
	}
	//参照マスタテーブルが設定されている場合
	if($mastertablenum != '')
	{
		//マスタテ-ーブルのCODEを項目に追加---
		for( $i = 0 ; $i < count($mastertablenum_array) ; $i++)
		{
			$insert_SQL .= $mastertablenum_array[$i]."ID,";
		}
	}
	//自動更新項目
	if($update_column != '')
	{
		for( $i = 0 ; $i < count($update_column_array) ; $i++)
		{
			//表示項目に含まれているなら処理しない
			if(in_array( $update_column_array[$i], $columns_array ) )
			{
				continue;
			}
			$columnName = $form_ini[$update_column_array[$i]]['column'];
			$insert_SQL .= $columnName.",";
		}
	}
	//項目名末尾の「,」を取る
	$insert_SQL = substr($insert_SQL,0,-1);

	//ここから値
	$insert_SQL .= ")VALUES(";
	//項目数ループ
	for($i = 0 ; $i < count($columns_array) ; $i++)
	{
		$columnValue = '';
		//型を取得
		if(isset($form_ini[$columns_array[$i]]['form1_type']) == true)
		{
			//フォーマット指定を取得
			$format = $form_ini[$columns_array[$i]]['form1_format'];
			//DBによるAutoIncrement項目は含めない
			if($format === '7')
			{
				continue;
			}
			//自動更新項目に含まれているか？
			if(in_array( $columns_array[$i], $update_column_array ) )
			{
				//含まれているので、位置を検索
				$idx = array_search( $columns_array[$i], $update_column_array);
				//同位置の値指定を取り、自動更新値に変換して付け足し
				$columnValue = getAutoUpdateValue( $update_value_array[$idx], $post );
				$insert_SQL .= $columnValue.",";
				//引数にもセットしておく
				 $post["form_".$columns_array[$i]."_0"] = $columnValue;
				continue;
			}
			$formtype = $form_ini[$columns_array[$i]]['form1_type'];
//			if($formtype == 1 || $formtype == 2|| $formtype == 4  )
//			{
//				$delimiter = "-";
//			}
//			else
//			{
				$delimiter = "";
//			}
			for($j = 0; $j < 5 ; $j++)
			{
				if($over == "")
				{
					$serch_str = "form_".$columns_array[$i]."_".$j;
				}
				else
				{
					$serch_str = "form_".$columns_array[$i]."_".$j."_".$over ;
				}
				if(isset($post[$serch_str]))
				{
					$columnValue .= $post[$serch_str].$delimiter;
				}
			}
			$columnValue = rtrim($columnValue,$delimiter);
						
			$fieldtype = $form_ini[$columns_array[$i]]['fieldtype'];
			$singleQute = $fieldtype_ini[$fieldtype];
			//数値フィールドの場合にnullに置換
			if( $fieldtype !== 'VARCHAR' && $columnValue ==='' )
			{
				$insert_SQL .= 'null,';
			}
			else
			{
				$insert_SQL .= $singleQute.$columnValue.$singleQute.",";				
			}
			$columnValue ="";
		}
		else if($tablenum == $columns_array[$i])
		{
			for($k = 0 ; $k < count($table_columns_array) ; $k++)
			{
				$formtype = $form_ini[$table_columns_array[$k]]['form1_type'];
//				if($formtype == 1 || $formtype == 2|| $formtype == 4  )
//				{
//					$delimiter = "-";
//				}
//				else
//				{
					$delimiter = "";
//				}
				for($j = 0; $j < 5 ; $j++)
				{
					if($over == "")
					{
						$serch_str = "form_".$table_columns_array[$k]."_".$j;
					}
					else
					{
						$serch_str = "form_".$table_columns_array[$k]."_".$j."_".$over ;
					}
					if(isset($post[$serch_str]))
					{
						$columnValue .= $post[$serch_str].$delimiter;
					}
				}
				$columnValue = rtrim($columnValue,$delimiter);

				$fieldtype = $form_ini[$table_columns_array[$k]]['fieldtype'];
				$singleQute = $fieldtype_ini[$fieldtype];
				$insert_SQL .= $singleQute.$columnValue.$singleQute.",";
				$columnValue ="";
			}
		}
	}
	
	//マスタテーブルのCODEを追加
	if($mastertablenum != '')
	{
		for($i = 0 ; $i < count($mastertablenum_array) ; $i++)
		{
			$insert_SQL .= $post[$mastertablenum_array[$i]."ID"].",";
		}
	}
	//自動更新項目
	if($update_value != '')
	{
		for( $i = 0 ; $i < count($update_value_array) ; $i++)
		{
			//表示項目に含まれているなら処理しない
			if(in_array( $update_column_array[$i], $columns_array ) )
			{
				continue;
			}
			//指定値
			$columnValue = getAutoUpdateValue( $update_value_array[$i], "" );

			$insert_SQL .= $columnValue.",";
		}
	}
	//末尾の「,」を取る
	$insert_SQL = substr($insert_SQL,0,-1);
	
	// )で閉める
	$insert_SQL .= ");";
	
	
	
	return($insert_SQL);
}

/************************************************************************************************************
function HeaderInsertSQL($post,$tablenum,$over)

引数	$post
ヘッダ明細登録
戻り値	なし
************************************************************************************************************/
function MeisaiInsertSQL($post,$over,$filename){
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
	require_once 'f_DB.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $filename . '_M';
	//_Mの設定値がない場合
	if(!isset($form_ini[$filename]))
	{
		return 0;
	}
	$tablenum = $form_ini[$filename]['use_maintable_num'];
	$columns = $form_ini[$filename]['page_columns'];
	$columns_array = explode(',',$columns);
	$tableName = $form_ini[$tablenum]['table_name'];
	$update_column = $form_ini[$filename]['auto_ins_column_num'];
	$update_value = $form_ini[$filename]['auto_ins_column_value'];
	$update_column_array = explode(',',$update_column);
	$update_value_array = explode(',',$update_value);

	//------------------------//
	//          変数          //
	//------------------------//
	$columnName = "";
	$columnValue = "";
	$formatType = "";
	$insert_SQL = "";
	$singleQute = "";
	$key_array = array();
	$fieldtype = "";
	$serch_str = "";
	$key_id = array();
	$formtype ="";
	$delimiter = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	
	$Meisai_Insert = array();
	//見積明細15回回す
	for($counter = 0; $counter < 15; $counter++ )
	{
		//$blankflag = 0;
		$insert_SQL .= "INSERT INTO ".$tableName." (";

		// 項目名の構築
		for($i = 0 ; $i < count($columns_array) ; $i++)
		{
			//ページの設定からINSERT項目を読み出し(優先)
			if(isset($form_ini[$columns_array[$i]]['column']) == true)
			{
				$columnName = $form_ini[$columns_array[$i]]['column'];
				$insert_SQL .= $columnName.",";
			}
		}
		
		//自動更新項目
		if($update_column != '')
		{
			for( $i = 0 ; $i < count($update_column_array) ; $i++)
			{
				$columnName = $form_ini[$update_column_array[$i]]['column'];
				$insert_SQL .= $columnName.",";
			}
		}
		//項目名末尾の「,」を取る
		$insert_SQL = substr($insert_SQL,0,-1);
//-----------------------------------------------------------------------------------------------------------------------------------------------------------------------//
		//ここから値
		$insert_SQL .= ")VALUES(";
		//項目数ループ
		for($i = 0 ; $i < count($columns_array) ; $i++)
		{
			//型を取得
			if(isset($form_ini[$columns_array[$i]]['form1_type']) == true)
			{
				$formtype = $form_ini[$columns_array[$i]]['form1_type'];
	
				$delimiter = "";
				if($over == "")
				{
					$serch_str = "form_".$columns_array[$i]."_"."0";
				}
				else
				{
					$serch_str = "form_".$columns_array[$i]."_".$counter."_".$over ;
				}
				if(isset($post[$serch_str]))
				{
					$columnValue .= $post[$serch_str].$delimiter;
				}
				else 
				{
					$serch_str = "form_".$columns_array[$i]."_"."0"."_".$counter;
					if(isset($post[$serch_str]))
					{
						$columnValue .= $post[$serch_str].$delimiter;
					} 
				}
				
				$columnValue = rtrim($columnValue,$delimiter);
				
				$fieldtype = $form_ini[$columns_array[$i]]['fieldtype'];
				$singleQute = $fieldtype_ini[$fieldtype];
				//数値フィールドの場合にnullに置換
				if( $fieldtype !== 'VARCHAR' && $columnValue ==='' )
				{
					$insert_SQL .= 'null,';
				}
				else
				{
					$insert_SQL .= $singleQute.$columnValue.$singleQute.",";				
				}

				$columnValue ="";
			}
			
		}
		
		//項目に入力あり時
		//自動更新項目
		if($update_value != '')
		{
			for( $i = 0 ; $i < count($update_value_array) ; $i++)
			{
					//指定値
				$outvalue = getAutoUpdateValue( $update_value_array[$i] ,$post);
				$insert_SQL .= $outvalue.",";
			}
		}		

		//末尾の「,」を取る
		$insert_SQL = substr($insert_SQL,0,-1);

		// )で閉める
		$insert_SQL .= ");";
		$Meisai_Insert[$counter] = $insert_SQL;
		$insert_SQL = "";

	}
	
	return($Meisai_Insert);
}


/************************************************************************************************************
function joinSelectSQL($post,$tablenum)

引数	$post

戻り値	なし
************************************************************************************************************/
function joinSelectSQL( $post, $tablenum, $filename, &$form_ini )
{
	//------------------------//
	//        初期設定        //
	//------------------------//
	$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
	require_once 'f_DB.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	$columns = $form_ini[$filename]['sech_form_num'];
	$columns_array = explode(',',$columns);
	$tableName = $form_ini[$tablenum]['table_name'];
	$fromto_columns = $form_ini[$filename]['sech_fromto_column'];
	$fromto_columns_array = explode(',',$fromto_columns);

	//------------------------//
	//          変数          //
	//------------------------//
	$columnName = "";
	$columnValue = "";
	$select_SQL = "";
	$count_SQL = "";
	$singleQute = "";
	$fieldtype = "";
	$formtype = "";
	$serch_str = "";
	$formatdate = "";
	$singleQute_start = "";
	$singleQute_end = "";
	$convert = "";

	//------------------------//
	//          処理          //
	//------------------------//
	$sql = getSelectSQL($post, $filename);
	if( count($sql) > 1 )
	{
		$select_SQL .= $sql[0];
		$count_SQL .= $sql[1];
	}
	else
	{
		$select_SQL .= "SELECT * FROM ".$tableName." ";
		$count_SQL .= "SELECT COUNT(*) FROM ".$tableName." ";
	}

	//DELETEFLAGが存在する場合
	$select_SQL .= " WHERE";
	$count_SQL .= " WHERE";

	
	//項目数ループ
	for($i = 0 ; $i < count($columns_array) ; $i++)
	{
		//項目名
		$column_id = $columns_array[$i];
		if( $column_id == '' )
		{
			continue;
		}
		
		//値の検索
		$formtype = $form_ini[$column_id]['form1_type'];
 		for($j = 0; $j < 5 ; $j++)
		{
			$serch_str = "form_".$column_id."_".$j;
			if(isset($post[$serch_str]))
			{
				$columnValue .= $post[$serch_str];
			}
		}

		//値がない場合はさっさと次へ
		if( $columnValue === '')
		{
			$formatdate = '';
			continue;
		}
		//項目設定値
		$columnName = $form_ini[$column_id]['column'];
		$fieldtype = $form_ini[$column_id]['fieldtype'];
		$singleQute = $fieldtype_ini[$fieldtype];
		$table_id = $form_ini[$column_id]['table_num'];
		$search_table = $form_ini[$table_id]['table_name'];
		
		if( $fieldtype == 'VARCHAR' || $fieldtype == 'CHAR'  )
		{
			//$convert =  " convert(replace(replace(".$tableName.".".$columnName
			//			.",' ',''),'　','') using utf8) COLLATE utf8_unicode_ci ";
//			$convert =  " convert(replace(replace(".$search_table.".".$columnName
//						.",' ',''),'　','') using utf8) COLLATE utf8_unicode_ci ";
			$convert = " ".$search_table.".".$columnName;
			$singleQute_start = " LIKE '%";
			$singleQute_end = "%'";
		}
		else
		{
			//$convert = " ".$tableName.".".$columnName;
			$convert = " ".$search_table.".".$columnName;
			$singleQute_start = " = ";
			$singleQute_end = "";
		}
		
		//FROM-TO検索？
		if( in_array( $column_id, $fromto_columns_array ) )
		{
			//FROM-TOの場合
			$value_from = '';
			$value_to = '';
			// Fromの値を取得
			$serch_str = "form_".$column_id."_0";
			if(isset($post[$serch_str]))
			{
				$value_from = $post[$serch_str];
			}
			// Toの値を取得
			$serch_str = "form_".$column_id."_1";
			if(isset($post[$serch_str]))
			{
				$value_to = $post[$serch_str];
			}
			//指定によって条件を変える
			$sql_add = '';
			if( $value_from !== '' && $value_to !== '' )
			{
				//両方指定されているので、BETWEEN
				$sql_add = $convert.' BETWEEN ' .$singleQute.$value_from.$singleQute. ' AND ' .$singleQute.$value_to.$singleQute. ' AND';
			}
			elseif( $value_from !== '' )
			{
				//FROMだけなので＞＝
				$sql_add = $convert.' >= ' .$singleQute.$value_from.$singleQute.' AND';				
			}
			elseif( $value_to !== '' )
			{
				//TOだけなので、＜＝
				$sql_add = $convert.' <= ' .$singleQute.$value_to.$singleQute.' AND';								
			}
			//SQL文につけたし
			$select_SQL .= $sql_add;
			$count_SQL .= $sql_add;
		}
		else
		{
			//if ($columnValue != "" && ($formtype != 3 ))
			if (($formtype != 3 ))
			{
				$columnValue = str_replace(" ", "%", $columnValue); 
				$columnValue = str_replace("　", "%", $columnValue);
				$select_SQL .= $convert;
				$select_SQL .= $singleQute_start.$columnValue.$singleQute_end." AND";
				$count_SQL .= $convert;
				$count_SQL .= $singleQute_start.$columnValue.$singleQute_end." AND";
			}
			//else if ($columnValue != "")
			else
			{
				$select_SQL .= " ".$search_table.".".$columnName." =". $singleQute.$columnValue.$singleQute." AND";
				$count_SQL .= " ".$search_table.".".$columnName." =". $singleQute.$columnValue.$singleQute." AND";
				$formatdate = "";
			}
		}

		$columnValue ='';
	}
	$select_SQL = rtrim($select_SQL,'WHERE');
	$select_SQL = rtrim($select_SQL,'AND');
	$count_SQL = rtrim($count_SQL,'WHERE');
	$count_SQL = rtrim($count_SQL,'AND');

	$select_SQL .= ";";
	$count_SQL .= ";";
	$sql[0] = $select_SQL;
	$sql[1] = $count_SQL;
	return ($sql);
}





/************************************************************************************************************
function idSelectSQL($code_value,$tablenum,$code)

引数	$post

戻り値	なし
************************************************************************************************************/
function idSelectSQL($code_value,$tablenum,$code){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	
	//------------------------//
	//          定数          //
	//------------------------//
	$tableName = $form_ini[$tablenum]['table_name'];

	//------------------------//
	//          変数          //
	//------------------------//
	$select_SQL = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	$select_SQL .= "SELECT * FROM ".$tableName." WHERE";
	$select_SQL .= " ".$code." = ";
	$select_SQL .= $code_value." ";
	$select_SQL .= ";";
	return $select_SQL;
}


/************************************************************************************************************
function UpdateSQL($post,$tablenum,$over)

引数	$post

戻り値	なし
************************************************************************************************************/
function UpdateSQL( $post, $tablenum, $over, $filename ){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
	require_once 'f_Construct.php';
	require_once 'f_DB.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	$columns = $form_ini[$filename]['page_columns'];
	$columns_array = explode(',',$columns);
	$tableName = $form_ini[$tablenum]['table_name'];
	$update_column = $form_ini[$filename]['auto_up_column_num'];
	$update_value = $form_ini[$filename]['auto_up_column_value'];
	$update_column_array = explode(',',$update_column);
	$update_value_array = explode(',',$update_value);
	$table_columns = '';
	if(isset($form_ini[$tablenum]['insert_form_num']))
	{
		$table_columns = $form_ini[$tablenum]['insert_form_num'];
	}
	$table_columns_array = explode(',',$table_columns);

	//------------------------//
	//          変数          //
	//------------------------//
	$columnName = "";
	$columnValue = "";
	$formatType = "";
	$update_SQL = "";
	$singleQute = "";
	$key_array = array();
	$fieldtype = "";
	$serch_str = "";
	$key_id = array();
	$formtype = "";
	$delimiter = "";
	
	//------------------------//
	//          処理          //
	//------------------------//

	$update_SQL .= "UPDATE ".$tableName." SET";
	for($i = 0 ; $i < count($columns_array) ; $i++)
	{
		if(isset($form_ini[$columns_array[$i]]['form1_type']) == true)
		{
			//フォーマット指定を取得
			$format = $form_ini[$columns_array[$i]]['form1_format'];
			//DBによるAutoIncrement項目は含めない
			if($format === '7')
			{
				continue;
			}
			$formtype = $form_ini[$columns_array[$i]]['form1_type'];
			$delimiter = "";

			//自動更新項目に含まれているか？
			if(in_array( $columns_array[$i], $update_column_array ) )
			{
				//含まれているので、位置を検索
				$idx = array_search( $columns_array[$i], $update_column_array);
				//同位置の値指定を取り、自動更新値に変換して付け足し
				$columnValue = getAutoUpdateValue( $update_value_array[$idx], $post );
			}
			else 
			{
				for($j = 0; $j < 5 ; $j++)
				{
					if($over == "")
					{
						$serch_str = "form_".$columns_array[$i]."_".$j;
					}
					else
					{
						$serch_str = "form_".$columns_array[$i]."_".$j."_".$over ;
					}
					if(isset($post[$serch_str]))
					{
						$columnValue .= $post[$serch_str].$delimiter;
					}
				}
				$columnValue = rtrim( $columnValue, $delimiter );
			}
			//設定値
			$columnName = $form_ini[$columns_array[$i]]['column'];
			$fieldtype = $form_ini[$columns_array[$i]]['fieldtype'];
			$singleQute = $fieldtype_ini[$fieldtype];
			//数値フィールドの場合にnullに置換
			if( $fieldtype !== 'VARCHAR' && $columnValue ==='' )
			{
				$update_SQL .= " ".$columnName." = null ,";
			}
			else
			{
				$update_SQL .= " ".$columnName." = ".$singleQute.$columnValue.$singleQute." ,";
			}
			//更新文
			$columnValue ="";
		}
		else if($tablenum == $columns_array[$i])
		{
			for($k = 0 ; $k < count($table_columns_array) ; $k++)
			{
				$formtype = $form_ini[$table_columns_array[$k]]['form1_type'];
				$delimiter = "";
				for($j = 0; $j < 5 ; $j++)
				{
					if($over == "")
					{
						$serch_str = "form_".$table_columns_array[$k]."_".$j;
					}
					else
					{
						$serch_str = "form_".$table_columns_array[$k]."_".$j."_".$over ;
					}
					if(isset($post[$serch_str]))
					{
						$columnValue .= $post[$serch_str].$delimiter;
					}
				}
				$columnValue = rtrim($columnValue,$delimiter);
				$columnName = $form_ini[$table_columns_array[$k]]['column'];
				$fieldtype = $form_ini[$table_columns_array[$k]]['fieldtype'];
				$singleQute = $fieldtype_ini[$fieldtype];
				$update_SQL .= " ".$columnName." = ";
				$update_SQL .= $singleQute.$columnValue.$singleQute." ,";
				$columnValue ="";
			}
		}
	}

	if($update_column != '')
	{
		for( $i = 0 ; $i < count($update_column_array) ; $i++)
		{
			//表示項目に含まれているなら処理しない
			if(in_array( $update_column_array[$i], $columns_array ) )
			{
				continue;
			}
			// 更新列名
			$columnName = $form_ini[$update_column_array[$i]]['column'];
			
			//指定値
			$columnValue = getAutoUpdateValue( $update_value_array[$i], "" );
			//
			$update_SQL .= ' '.$columnName.' = '. $columnValue.' ,';
		}
	}
	//末尾の,を除去
	$update_SQL = rtrim($update_SQL,',');

	//条件を指定
        $code = getCode($filename);
	$key_id =  strtoupper($code);
	$key_value = '';
	if(array_key_exists($key_id, $post) === true)
	{
		//キーが直指定で入っている
		$key_value = $post[$key_id];
	}
	else
	{
		//キーが項目として入っている
		$key_value = $post[ 'form_'.$tablenum.$key_id.'_0' ];
	}
	$update_SQL .= " WHERE ".$key_id." = ".$key_value;
	$update_SQL .= ";";

	return $update_SQL;
}
/************************************************************************************************************
function HeaderUpdateSQL($post,$tablenum,$over)

引数1	$post				ヘッダ明細	
引数2	$tablenum			mmh
引数3	$over
ヘッダ明細更新
戻り値	なし
************************************************************************************************************/
function MeisaiUpdateSQL($post,$tablenum,$over,$filename){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
	require_once 'f_DB.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	
	$filename = $filename . '_M';
	//_Mの設定値がない場合
	if(!isset($form_ini[$filename]))
	{
		return 0;
	}
	$columns = $form_ini[$filename]['page_columns'];
	$columns_array = explode(',',$columns);
	$use_code = $form_ini[$filename]['use_maintable_num'];
	$tableName = $form_ini[$use_code]['table_name'];
	$mastertablenum = $form_ini[$tablenum]['seen_table_num'];
	$mastertablenum_array = explode(',',$mastertablenum);

	//------------------------//
	//          変数          //
	//------------------------//
	$columnName = "";
	$columnValue = "";
	$formatType = "";
	$update_SQL = "";
	$singleQute = "";
	$key_array = array();
	$fieldtype = "";
	$serch_str = "";
	$key_id = array();
	$formtype ="";
	$delimiter = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	
	$Meisai_Update = array();
	//見積明細15回回す
	for($counter = 0; $counter < 15; $counter++ )
	{
		
		//更新SQL作成
		$update_SQL .= "UPDATE ".$tableName." SET";
		
		//項目数ループ
		for($i = 0 ; $i < count($columns_array) ; $i++)
		{
			//型を取得
			if(isset($form_ini[$columns_array[$i]]['form1_type']) == true)
			{
				$formtype = $form_ini[$columns_array[$i]]['form1_type'];
	
				$delimiter = "";
				
				if($over == "")
				{
					//$serch_str = "form_".$columns_array[$i]."_"."0"."_".$counter;
					$serch_str = "form_".$columns_array[$i]."_"."0"."_".$counter;
				}
				else
				{
					$serch_str = "form_".$columns_array[$i]."_".$counter."_".$over ;
				}
				if(isset($post[$serch_str]))
				{
					$columnValue .= $post[$serch_str].$delimiter;
				}
				$columnValue = rtrim($columnValue,$delimiter);
				$columnName = $form_ini[$columns_array[$i]]['column'];
				$fieldtype = $form_ini[$columns_array[$i]]['fieldtype'];
				$singleQute = $fieldtype_ini[$fieldtype];
				//数値フィールドの場合にnullに置換
				$update_SQL .= " ".$columnName." = ";
				if( $fieldtype !== 'VARCHAR' && $columnValue ==='' )
				{
					$update_SQL .= "null ,";
				}
				else
				{
					$update_SQL .= $singleQute.$columnValue.$singleQute." ,";
				}
				$columnValue ="";
			}
			
		}
		
			if($mastertablenum != '')
			{
				for( $i = 0 ; $i < count($mastertablenum_array) ; $i++)
				{
					$update_SQL .= " ".$mastertablenum_array[$i]."ID = ";
					$update_SQL .= $post[$mastertablenum_array[$i]."ID"].",";
				}
			}
			//末尾の,を除去
			$update_SQL = rtrim($update_SQL,',');

			//条件を指定
			$update_SQL .= " WHERE ".strtoupper($tablenum)."ID = ".$post[$tablenum."ID"]. " AND SEQ = ".$counter."";
			$update_SQL .= ";";
			$Meisai_Update[$counter] = $update_SQL;
			$update_SQL = "";
		
	}
	return($Meisai_Update);
}

/************************************************************************************************************
function DeleteSQL($codeValue,$tablenum,$code)

引数	1	$codeValue		削除ID
引数2	$tablenum		テーブル名
引数3	$code			削除項目
引数4	$filename		ページ名

戻り値	なし
************************************************************************************************************/
function DeleteSQL($codeValue,$tablenum,$code,$filename){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	
	//------------------------//
	//          定数          //
	//------------------------//
	$tableName = $form_ini[$tablenum]['table_name'];
	$headerFileName = $filename.'_M';
	//------------------------//
	//          変数          //
	//------------------------//
	$delete_SQL = array();
	
	//------------------------//
	//          処理          //
	//------------------------//
	
	//顧客マスタ、ログインの削除はDELETEFLAGに1を立てる
	if($tableName == 'kokyakumaster' || $tableName == 'loginuserinfo' )
	{	
		//削除時DELETEFLAGに1を立てる
		$delete_SQL[0] = "UPDATE ".$tableName." ";
		$delete_SQL[0] .= " SET DELETEFLAG = '1' ";
		$delete_SQL[0] .= " WHERE ".$code." = ".$codeValue;
		$delete_SQL[0] .= ";";
	}
	else
	{	
		$delete_SQL[0] = "DELETE FROM ".$tableName." ";
		$delete_SQL[0] .= " WHERE ".$code." = ".$codeValue;
		$delete_SQL[0] .= ";";
	}
	//ヘッダ明細削除
	if(isset($form_ini[$headerFileName]))
	{
		$headernum = $form_ini[$headerFileName]['use_maintable_num'];
		$headerTableName = $form_ini[$headernum]['table_name'];
		$delete_SQL[1] = "DELETE FROM ".$headerTableName." ";
		$delete_SQL[1] .= " WHERE ".$code." = ".$codeValue;
		$delete_SQL[1] .= ";";
	}	
	
	
	return($delete_SQL);
}



/************************************************************************************************************
function uniqeSelectSQL($post,$tablenum,$columns)

引数	$post

戻り値	なし
************************************************************************************************************/
function uniqeSelectSQL($post,$tablenum,$columns){
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$fieldtype_ini = parse_ini_file('./ini/fieldtype.ini');
	require_once 'f_DB.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	$columns_array = explode(',',$columns);
	$tableName = $form_ini[$tablenum]['table_name'];

	//------------------------//
	//          変数          //
	//------------------------//
	$columnName = "";
	$columnValue = "";
	$formatType = "";
	$select_SQL = "";
	$singleQute = "";
	$key_array = array();
	$fieldtype = "";
	$serch_str = "";
	$key_id = array();
	$uniqefiled = array();
	$isValueExit = true;
	$judge = true;
	$delimiter = "";
	$formtype = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	if(isset($post['uniqe']) == false)
	{
		$judge = false;
	}
	$select_SQL .= "SELECT * FROM ".$tableName." WHERE";
	for($i = 0 ; $i < count($columns_array) ; $i++)
	{
		if($columns_array[$i] == "")
		{
			break;
		}
		$uniqefiled = $columns_array[$i];
		$uniqefiled = explode('~',$columns_array[$i]);
		for($j = 0 ; $j < count($uniqefiled) ; $j++)
		{
			$formtype = $form_ini[$uniqefiled[$j]]['form1_type'];
			$columnName = $form_ini[$uniqefiled[$j]]['column'];
//			if($formtype == 1 || $formtype == 2|| $formtype == 4  )
//			{
//				$delimiter = "-";
//			}
//			else
//			{
				$delimiter = "";
//			}
			for($k = 0; $k < 5 ; $k++)
			{
//				if(strstr($columnName,'ID') != false)
                                if(strstr($columnName,'CODE') != false)
				{
					$serch_str = $columnName;
					if($k != 0)
					{
						break;
					}
				}
				else
				{
					$serch_str = "form_".$uniqefiled[$j]."_".$k;
				}
				if(isset($post[$serch_str]))
				{
					$columnValue .= $post[$serch_str].$delimiter;
				}
			}
			$columnValue  = rtrim($columnValue,$delimiter);
			if(isset($post['uniqe'][$columns_array[$i]]))
			{
				if($post['uniqe'][$columns_array[$i]] != $columnValue )
				{
					$judge = false;
				}
			}
			$fieldtype = $form_ini[$uniqefiled[$j]]['fieldtype'];
			$singleQute = $fieldtype_ini[$fieldtype];
			if (count($uniqefiled) == 1)
			{
				$select_SQL .= " ".$columnName." = ";
				$select_SQL .= $singleQute.$columnValue.$singleQute." OR";
			}
			else if( count($uniqefiled) > 1)
			{
				if($j == 0)
				{
					$select_SQL .="(";
				}
				$select_SQL .= " ".$columnName." = ";
				$select_SQL .= $singleQute.$columnValue.$singleQute." AND";
			}
			$columnValue ="";
		}
		if(count($uniqefiled) > 1)
		{
			$select_SQL = rtrim($select_SQL,'(');
			$select_SQL = rtrim($select_SQL,'AND');
			$select_SQL .= ") OR";
		}
	}
	$select_SQL = rtrim($select_SQL,'OR');
	$select_SQL = rtrim($select_SQL,'WHERE');
	$select_SQL .= ";";
	if($judge == true)
	{
		$select_SQL = "";
	}
	return $select_SQL;
}



/************************************************************************************************************
function SQLsetOrderby($post,$tablenum,$sql)

引数	$post

戻り値	なし
************************************************************************************************************/
function SQLsetOrderby($post,$tablenum,$sql){
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	$SQL_ini = parse_ini_file('./ini/SQL.ini', true);
	
	
	//------------------------//
	//          定数          //
	//------------------------//
	$filename = $_SESSION['filename'];
	$orderby = " ORDER BY ";
	$orderby_columns = $form_ini[$tablenum]['orderby_columns'];
	$orderby_columns_array = explode(',',$orderby_columns);
	$orderby_type = $form_ini[$tablenum]['orderby_type'];
	$orderby_type_array = explode(',',$orderby_type);
	$oderby_array = array();
	$oderby_array[0] = " ASC ";
	$oderby_array[1] = " DESC ";
	
	//------------------------//
	//          変数          //
	//------------------------//
	$sqlresult = "";
	
	$sql[0] = substr($sql[0],0,-1);
	$sql[1] = substr($sql[1],0,-1);
	//------------------------//
	//          処理          //
	//------------------------//
	
	for($i = 0 ; $i < count($orderby_columns_array) ; $i++ )
	{
		if($orderby_columns == "")
		{
			break;
		}
		$orderby_column_name = $form_ini[$orderby_columns_array[$i]]['column'];
		$sql[0] .= " ".$orderby." ".$orderby_column_name." ".$oderby_array[$orderby_type_array[$i]];
		$sql[1] .= " ".$orderby." ".$orderby_column_name." ".$oderby_array[$orderby_type_array[$i]];
		$orderby = " , ";
	}
	
	
	
	if(isset($post['sort']))
	{
		$orderby_column_num = $post['sort'];
		if($orderby_column_num != 0 && $orderby_column_num != 1)
		{
			$orderby_table_num = $form_ini[$orderby_column_num]['table_num'];
			$orderby_column_name = $form_ini[$orderby_column_num]['column'];
			$orderby_table_name = $form_ini[$orderby_table_num]['table_name'];
			$sql[0] .= " ".$orderby." ".$orderby_table_name.".".
							$orderby_column_name." ".$post['radiobutton'];
			$sql[1] .= " ".$orderby." ".$orderby_table_name.".".
							$orderby_column_name." ".$post['radiobutton'];
		}
	}
	
	$sql[0] .= " ;";
	$sql[1] .= " ;";
	return($sql);
	
}



/************************************************************************************************************
function getSelectSQL($codeValue,$tablenum,$code)

引数	$params
引数$tablenum

戻り値	なし
************************************************************************************************************/
function getSelectSQL( $params, $tablenum ){
	

	// 変数
	$SQL_ini = parse_ini_file('./ini/SQL.ini', true);
	$sql = array();
	$tempsql = "";
	
	if(!isset( $SQL_ini[$tablenum] ))
	{
		return ($sql);
	}
	//設定されているSQLを読む
	for( $i = 1; $i < 99; $i++ )
	{
		$key = "SQL_" . sprintf('%02d', $i); // 01
		//セット押されていなくなったら抜ける
		if( !isset($SQL_ini[$tablenum][$key]) )
		{
			break;
		}
		//読んだものをつなげる
		$tempsql .= $SQL_ini[$tablenum][$key];
	}
	
	$sql[0] = $tempsql;
	
	$sql[1] = $SQL_ini[$tablenum]["COUNT_SQL"];
	
	
	return($sql);
}

/************************************************************************************************************
function setSQLOrderby($post,$tablenum,$sql)

引数	$post

戻り値	なし
************************************************************************************************************/
function setSQLOrderby($filename, &$form_ini, $sql){

	
	//------------------------//
	//          定数          //
	//------------------------//
	$orderby = " ORDER BY ";
	$orderby_columns = $form_ini[$filename]['orderby_columns'];
	$orderby_columns_array = explode(',',$orderby_columns);
	$orderby_type = $form_ini[$filename]['orderby_type'];
	$orderby_type_array = explode(',',$orderby_type);
	$oderby_array = array();
	$oderby_array[0] = " ASC ";
	$oderby_array[1] = " DESC ";

	//------------------------//
	//          変数          //
	//------------------------//
	$sql[0] = substr($sql[0],0,-1);
	$sql[1] = substr($sql[1],0,-1);

	//------------------------//
	//          処理          //
	//------------------------//
	for($i = 0 ; $i < count($orderby_columns_array) ; $i++ )
	{
		if($orderby_columns == "")
		{
			break;
		}
		$orderby_column_name = $form_ini[$orderby_columns_array[$i]]['column'];
		$sql[0] .= " ".$orderby." ".$orderby_column_name." ".$oderby_array[$orderby_type_array[$i]];
		//$sql[1] .= " ".$orderby." ".$orderby_column_name." ".$oderby_array[$orderby_type_array[$i]];
		$orderby = " , ";
	}

	$sql[0] .= " ;";
	$sql[1] .= " ;";

	return($sql);
}



?>
