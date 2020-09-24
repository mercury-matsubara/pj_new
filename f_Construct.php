<?php

// 定数
const APPLICATION_NAME = "プロジェクト管理";
const STEP_INSERT = 1;
const STEP_EDIT = 2;
const STEP_DELETE = 3;
const STEP_COMP = 4;
const STEP_PRINT = 5;
const STEP_NONE = 0;

const PAGE_NONE = 0;
const PAGE_ALL = 1;
const PAGE_COUNT_ONLY = 2;


function kousuSQL($syain,$day){
    
    $searchday = str_replace("/","-",$day);
    $sql = "SELECT PROJECTNUM,EDABAN,PJNAME,KOUTEIID,KOUTEINAME,TEIZITIME,ZANGYOUTIME
            FROM progressinfo 
            LEFT JOIN kouteiinfo USING (3CODE ) 
            LEFT JOIN projectditealinfo USING (6CODE ) 
            LEFT JOIN syaininfo USING (4CODE ) 
            LEFT JOIN projectinfo USING (5CODE ) 
            LEFT JOIN projectnuminfo USING (1CODE ) 
            LEFT JOIN edabaninfo USING (2CODE ) 
            WHERE 7PJSTAT = 1 
            AND convert(replace(replace(syaininfo.STAFFID,' ',''),'　','') using utf8) COLLATE utf8_unicode_ci LIKE '$syain' 
            AND  SAGYOUDATE Like '$searchday'";
    return $sql;
    
    
}

function convertGet2Post()
{

	// GET情報をPOST情報に格納する
	if( isset($_GET) )
	{
		foreach($_GET as $key  =>  $value)
		{
			$_POST[$key] = $value;
		}

	}

}
function convertPost2Get()
{

	// GET情報をPOST情報に格納する
	if( isset($_POST) )
	{
		foreach($_POST as $key  =>  $value)
		{
			$_GET[$key] = $value;
		}

	}

}

/**
 * 関数名: getAutoUpdateValue
 *   自動更新値、デフォルト値を取得するための関数
 *   
 * @param [string] $setting_value	設定値文字列
 * @param [array] $post 送信情報

 * @retrun 採番文字列
 */
function getAutoUpdateValue( $setting_value, $post )
{
	$result = '';
        
	//@の有無を検索
	$pos = strpos($setting_value,'@');
	if( $pos == false )
	{
		//@がない場合はそのまま
		$result = $setting_value;
		
	}
	else
	{
		//スコープ指定の切り出し
		$scope_temp = substr($setting_value,$pos+1);
		$scope = str_replace ( "'", '', $scope_temp );
		//変数名指定の切り出し
		$valname_temp =  substr($setting_value,0,$pos);
		$valname = str_replace ( "'", '', $valname_temp );

		//値の取得
		$outvalue = '';
		if( $scope === 'session' )
		{
			if( array_key_exists ( 'SYAMEI', $_SESSION ) === false)
			{	//未ロードの場合は自社情報読み出し
				loadJisyaMaster();
			}
			//session指定なのでsessionから値を取得
			$outvalue = $_SESSION[$valname];
		}
		else if( $scope === "post")
		{
			//post指定なのでpostから取得
			if( array_key_exists ( $valname, $post ) === true)
			{
				$outvalue = $post[$valname];
			}
			else
			{
				$outvalue = $post['form_'.$valname.'_0'];				
			}
		}	
		else if( $scope === "saiban")
		{
			$outvalue = getAutoSaiban( $valname );
		}
		else if( $scope === "system")
		{
			if( $valname === 'date' )
			{
				$outvalue = date("Y/m/d");
			}
			if( $valname === 'datetime' )
			{
				$outvalue = date("Y/m/d H:i:s");
			}
		}
                if($outvalue == null)
                {
                    $outvalue = '';
                }    
		//値の編集
		$result = str_replace ( $valname.'@'.$scope, $outvalue, $setting_value );
	}
	
	// 「|」 がある場合改行
	if(strstr($setting_value,'|') !== false)
	{
		$result = str_replace('|',"\n",$setting_value);
		//$result = nl2br($result);
	}
	
	return $result;
}


/**
 * 関数名: getAutoSaiban
 *   指定の採番をカウントアップし、結果を返す
 * 
 * @retrun 採番文字列
 */
function getAutoSaiban( $saiban_id )
{
	$ukey = $saiban_id.'UKEY';

	if(array_key_exists( $ukey, $_SESSION ) === false )
	{
		//自社マスタロード
		loadJisyaMaster();
	}
	
	//識別子(全社+個人)
	if(array_key_exists( 'PSUKEY', $_SESSION ) === false )
	{
		//※暫定処理。本来はタイムアウトしていなければ必ずある
		$_SESSION['PSUKEY'] = '';
	}
	$ukeyValue = $_SESSION[$ukey] . $_SESSION['PSUKEY'];
	
	//-----------------------------------//
	//     DBアクセス処理(ストアド実行)  //
	//-----------------------------------//
	//$con = dbconect();
	$db_ini_array = parse_ini_file("./ini/DB.ini",true);	
	$host = $db_ini_array["database"]["host"];																			// DBサーバーホスト
	$user = $db_ini_array["database"]["user"];																			// DBサーバーユーザー
	$password = $db_ini_array["database"]["userpass"];																	// DBサーバーパスワード
	$database = $db_ini_array["database"]["database"];	
	$con = new mysqli($host,$user,$password, $database, "3306") or die('1'.$con->error);// DB接続
	$con->set_charset("utf8") or die('2'.$con->error);                                  // utf8を使用する
	//ステートメントを作成
	$stmt = $con->prepare('call SAIBAN(?)');
        //パラメータをバインド
        $stmt->bind_param("s", $ukeyValue);
        //クエリを実行
        $stmt->execute();
        //結果変数をバインド
        $stmt->bind_result($saiban_id);
        //値取得
        $stmt->fetch();
        //ステートメントを閉じる
        $stmt->close();

        return $saiban_id;

}

/**
 * 関数名: loadJisyaMaster
 *   自社マスタの必要情報をSESSIONに読込む
 * 
 * @retrun なし
 */
function loadJisyaMaster()
{
	//DB接続
	$con = dbconect();	
	//SQL実行
	$result = $con->query('SELECT * FROM jisyamaster');				// クエリ発行

	//行数ループ
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach ( $result_row as $list_key => $list_text )
		{
			//識別
			$_SESSION[$list_key] =$list_text;
		}
		break;
	}
	$result->close();
}

/**
 * 関数名: suggets.iniの設定を使用してDBから値を取得する
 * 
 * @retrun 取得値
 */
function getSuggestValue( $suggest_id, $key )
{
	//DB接続
	$suggest_ini = parse_ini_file('./ini/suggest.ini', true);		// suggest.ini

	//IDからテーブル名など取得
	$select_sql = $suggest_ini[$suggest_id]['select_sql'];
	$key_column  = $suggest_ini[$suggest_id]['key_column'];
	$key_param  = $suggest_ini[$suggest_id]['key_param'];	
	$value_column  = $suggest_ini[$suggest_id]['value_column'];	
	$label_column  = $suggest_ini[$suggest_id]['label_column'];
	$order_by  = $suggest_ini[$suggest_id]['order_by'];
	$limit  = $suggest_ini[$suggest_id]['limit'];
	$add_column_string  = $suggest_ini[$suggest_id]['result_add_column'];
	$add_column  = explode(',',$add_column_string);
	$add_column_count = count($add_column);
	
	//検索条件
	$whereSql = "";
	if( $key != "" )
	{
		$whereSql .= " WHERE " .$key_column.(str_replace("@param", $key, $key_param));
	}
	
	//ORDER BY指定
	if($order_by != "")
	{
		$order_by = " ORDER BY ".$order_by;
	}

	//SQL文
	$sql = $select_sql. $whereSql.$order_by. " LIMIT " .$limit;

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
						  'VALUE'=>mb_convert_encoding($result_row[$value_column], "UTF-8", "SJIS"),
						  'LABEL'=>mb_convert_encoding($result_row[$label_column], "UTF-8", "SJIS"));
		//追加カラム有無
		if( $add_column_string != '' )
		{
			//追加分のカラムを処理
			for( $i= 0; $i < $add_column_count; $i++ )
			{
				$add_temp = $add_column[ $i ];
				$rowArray[$add_temp] = mb_convert_encoding($result_row[$add_temp], "UTF-8", "SJIS");
			}
			
		}
		//配列に割り当て
		$resultArray[$count] = $rowArray;
		$count++;
	}
	
	return $resultArray;
}

/**
 * 関数名: ユーザー情報を取得する
 * 
 * @retrun ユーザー情報
 */
function loginUserValue($userid)
{
	//DB接続
	$con = dbconect();	
	//SQL実行
	$result = $con->query('SELECT * FROM loginuserinfo where USRID ='.$userid);				// クエリ発行

	//行数ループ
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach ( $result_row as $list_key => $list_text )
		{
			//識別
			$value[$list_key] =$list_text;
		}
		break;
	}

	return $value;
}

/**
 * 関数名: ajustFilename
 *			filenameを調整する
 * 
 * @retrun なし
 */
function ajustFilename()
{
	//filename を調整する
	if(strpos($_SESSION['filename'],'PRINT_5') !== false)
	{
		$now_filename = str_replace('PRINT_5', 'INFO_1', $_SESSION['filename']);
		$_SESSION['filename'] = $now_filename;
	}
}

/**
 * 関数名: ユーザー情報を取得する
 * 
 * @retrun ユーザー情報
 */
function loadDBRecord($table,$id)
{
	global $form_ini;
	if( $form_ini != null)
	{
		$form_ini = parse_ini_file('./ini/form.ini', true);
	}

	$values = array();
	//DB接続
	$con = dbconect();	
	//SQL実行
	$result = $con->query('SELECT * FROM '.$form_ini[$table]['table_name'].' WHERE '. strtoupper($table).'ID ='.$id);				// クエリ発行
	//行数ループ
	while($result_row = $result->fetch_array(MYSQLI_ASSOC))
	{
		foreach ( $result_row as $list_key => $list_text )
		{
			$values[$list_key] =$list_text;
		}
		break;
	}

	return $values;
}
