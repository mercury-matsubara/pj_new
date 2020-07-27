<?php



/////////////////////////////////////////////////////////////////////////////////////
//                                                                                 //
//                                                                                 //
//                             ver 1.1.0 2014/07/03                                //
//                                                                                 //
//                                                                                 //
/////////////////////////////////////////////////////////////////////////////////////


/************************************************************************************************************
function format_change($format,$value,$type)

引数	$post

戻り値	なし
************************************************************************************************************/
function format_change($format,$value,$type){
	//------------------------//
	//        初期設定        //
	//------------------------//
	
	//------------------------//
	//          定数          //
	//------------------------//
	
	
	//------------------------//
	//          変数          //
	//------------------------//
	$prevalue = array();
	$result = "";
	
	//------------------------//
	//          処理          //
	//------------------------//
	switch ($format)
	{
	case 1:
		if(preg_match('/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $value))
		{
			$prevalue = explode('-',$value);
			if(checkdate($prevalue[1], $prevalue[2], $prevalue[0]))
			{
				$prevalue[0] = wareki_date($value)."年 ";
				$prevalue[1] = $prevalue[1]."月 ";
				$prevalue[2] = $prevalue[2]."日";
				$result .= $prevalue[0];
				if($type != 5 && $type != 6)
				{
					$result .= $prevalue[1];
				}
				if($type == 1 || $type == 2)
				{
					$result .= $prevalue[2];
				}
			}
		}
		return $result;
		break;
	case 2:
		if(preg_match('/^[0-9]{4}\-[0-9]{1,2}\-[0-9]{1,2}$/', $value))
		{
			$prevalue = explode('-',$value);
			if(checkdate($prevalue[1], $prevalue[2], $prevalue[0]))
			{
				$prevalue[0] = $prevalue[0]."年 ";
				$prevalue[1] = $prevalue[1]."月 ";
				$prevalue[2] = $prevalue[2]."日";
				$result .= $prevalue[0];
				if($type != 5 && $type != 6)
				{
					$result .= $prevalue[1];
				}
				if($type == 1 || $type == 2)
				{
					$result .= $prevalue[2];
				}
			}
		}
		return $result;
		break;
	case 3:
		if (is_numeric($value))
		{
			$result = number_format($value);
		}
		return $result;
		break;
	case 4:
		if (is_numeric($value))
		{
			if($value == 0)
			{
				$result = '出荷中';
			}
			if($value == 1)
			{
				$result = '再処理待ち';
			}
			if($value == 2)
			{
				$result = '完了';
			}
		}
		return $result;
		break;
	case 5:
		if (is_numeric($value))
		{
			if($value == 1)
			{
				$result = '出荷';
			}
			if($value == 2)
			{
				$result = '返却';
			}
		}
		return $result;
		break;
	case 6:
		if (is_numeric($value))
		{
			if($value == 1)
			{
				$result = '不足';
			}
			if($value == 2)
			{
				$result = '過剰';
			}
		}
		return $result;
		break;
	case 7:
		if (is_numeric($value))
		{
			if($value == 1)
			{
				$result = '差異未処理';
			}
			if($value == 2)
			{
				$result = '再処理済み';
			}
		}
		return $result;
		break;
	case 8:
		if (is_numeric($value))
		{
			if($value == 0)
			{
				$result = 'なし';
			}
			if($value == 1)
			{
				$result = 'あり';
			}
		}
		return $result;
		break;
	case 9:
		$date = date_create($value);
		$result = date_format($date, 'Y/m/d H:i:s');
		return $result;
		break;
	default :
		$result = $value;
	}
	return $result;

}



/************************************************************************************************************
function formvalue_return($colum_num,$value,$type)

引数	$colum_num
引数	$value

戻り値	$result
************************************************************************************************************/
function formvalue_return($colum_num,$value,$type) {
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once 'f_Form.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	$fild_name = $form_ini[$colum_num]['column'];
	
	//------------------------//
	//          変数          //
	//------------------------//
	$column_value = '';
	$form_name  = '';
	$form_type  = '';
	$form_para = array();
	
	

		if(strstr($fild_name,'ID') != false)
		{
			$form_name .= $fild_name.',';
		}
		else
		{
			$form_name .= 'form_'.$colum_num.'_0,';
		}
		$column_value .= $value.'#$';
		$form_type .=$type.',';
	$form_para[0] = $form_name;
	$form_para[1] = $column_value;
	$form_para[2] = $form_type;
	
	return($form_para);
}

/************************************************************************************************************
function getover($post,$tablenum)

引数	$colum_num
引数	$value

戻り値	$result
************************************************************************************************************/
function getover($post,$tablenum) {
	//------------------------//
	//        初期設定        //
	//------------------------//
	$form_ini = parse_ini_file('./ini/form.ini', true);
	require_once 'f_Form.php';
	
	//------------------------//
	//          定数          //
	//------------------------//
	//$columns = $form_ini[$tablenum]['insert_form_num'];
	$columns = $form_ini[$tablenum]['page_columns'];
	$columns_array = explode(',', $columns);
	
	//------------------------//
	//          変数          //
	//------------------------//
	$over =array();
	$keyarray = array();
	$counter = 0;
	$keyparam = array();
	
	//------------------------//
	//          処理          //
	//------------------------//
	
	$keyarray = array_keys($post);
	foreach($keyarray as $key)
	{
		if(strstr($key,$columns_array[0]) != false )
		{
			$keyparam = explode('_',$key);
			if(count($keyparam) == 3)
			{
				$over[$counter] = "";
			}
			else if(count($keyparam) == 4)
			{
				$over[$counter] = $keyparam[3];
			}
			else
			{
				$over[$counter] = "";
			}
			$counter++;
		}
	}
	return($over);
}

/************************************************************************************************************
function pulldownDate_set($type,$beforeyear,$afteryear,$name,$over,$post,$ReadOnly,$formName,$isnotnull)

引数	なし

戻り値	なし
************************************************************************************************************/
function datepickerDate_set($name, $post){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	$value = '';
	if(isset($post[$name]))
	{
		$value = $post[$name];
	}

	$str[0] = "<input type=\"text\" value=\"".$value."\" id=\"".$name."\" name=\"".$name."\" />";
//	$str[1] = "$(\"#".$name."\").datepicker();";
//	$str[1] .= "$(\"#".$name."\").datepicker(\"option\", \"showOn\", 'button');";
        $str[1] = "$(\"#".$name."\").datepicker({
                                                    showOn: 'button',
                                                    buttonImage: './image/icon.gif',
                                                    buttonImageOnly: true
                                                });";

	return $str;
}
