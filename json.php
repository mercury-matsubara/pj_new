<?php
	session_start();
	
	if( isset($_GET) == false )
	{
		exit();
	}
	
	require_once("f_Construct.php");
	require_once("f_DB.php");
	
	//------------------------//
	//          処理          //
	//------------------------//

	
	$suggest_ini = parse_ini_file('./ini/suggest.ini', true);		// suggest.ini

	//ID指定を取得
	$suggest_id = filter_input( INPUT_GET, "table_id" );
	//IDからテーブル名など取得
	$select_sql = $suggest_ini[$suggest_id]['select_sql'];
	$key_column  = $suggest_ini[$suggest_id]['key_column'];
	$key_param  = $suggest_ini[$suggest_id]['key_param'];	
	$search01_column  = $suggest_ini[$suggest_id]['search01_column'];
	$search01_param  = $suggest_ini[$suggest_id]['search01_param'];	
	$value_column  = $suggest_ini[$suggest_id]['value_column'];	
	$label_column  = $suggest_ini[$suggest_id]['label_column'];
	$order_by  = $suggest_ini[$suggest_id]['order_by'];
	$limit  = $suggest_ini[$suggest_id]['limit'];
	$add_column_string  = $suggest_ini[$suggest_id]['result_add_column'];
	$add_column  = explode(',',$add_column_string);
	$add_column_count = count($add_column);
	
	//検索条件
	$key = filter_input( INPUT_GET, "key" );
	$search = filter_input( INPUT_GET, "search" );
	$whereSql = "";
	if( $key != "" )
	{
            $whereSql .= " WHERE " .$key_column.(str_replace("@param", $key, $key_param));
	}
	else if ( $search != "" )
	{
            $whereSql .= " WHERE " .$search01_column. " ".str_replace("@param", $search, $search01_param);
	}
	
	//ORDER BY指定
	if($order_by != "")
	{
		$order_by = " ORDER BY ".$order_by;
	}
        
        //枝番の時　GROUP BY
        $group_by = "";
        if($search01_column === "EDABAN" || $search01_column === "PROJECTNUM")
        {
                $group_by = " GROUP BY " .$search01_column;
        }

	//SQL文
	$sql = $select_sql. $whereSql. $group_by. $order_by. " LIMIT " .$limit;
        
//$file = 'C:/Apache24/htdocs/log.txt';
//file_put_contents($file, $sql."\n", FILE_APPEND);

	// db接続関数実行
	$con = dbconect();
	$result = $con->query( $sql );																	// クエリ発行
	if(!$result)
	{
		error_log($con->error,0);
		exit();
	}

	$resultArray = array();
	$count = 0;
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		$rowArray =array( 'KEY'=>$result_row[$key_column],
						  'VALUE'=>$result_row[$value_column],
						  'LABEL'=>$result_row[$label_column]);
//						  'VALUE'=>mb_convert_encoding($result_row[$value_column], "UTF-8", "SJIS"),
//						  'LABEL'=>mb_convert_encoding($result_row[$label_column], "UTF-8", "SJIS"));
		//追加カラム有無
		if( $add_column_string != '' )
		{
			//追加分のカラムを処理
			for( $i= 0; $i < $add_column_count; $i++ )
			{
				$add_temp = $add_column[ $i ];
				$rowArray[$add_temp] = $result_row[$add_temp];
//				$rowArray[$add_temp] = mb_convert_encoding($result_row[$add_temp], "UTF-8", "SJIS");
			}
			
		}
		//配列に割り当て
		$resultArray[$count] = $rowArray;
		$count++;
	}
	
	//応答形式のjsonにあわせる
	$jsonArray = array( 'results'=>$resultArray );

	//json形式に変換
	$jsonString = json_encode( $jsonArray, JSON_UNESCAPED_UNICODE  );

	header('Content-Type: application/json');

	echo $jsonString;
