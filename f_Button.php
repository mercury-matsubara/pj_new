<?php


/***************************************************************************
function makebutton($fileName,$buttonPosition)


引数1	$fileName			表示ファイル名
引数2	$buttonPosition		表示位置

戻り値	$con	mysql接続済みobject
***************************************************************************/

function makebutton($fileName,$buttonPosition){
	
	//------------------------//
	//        初期設定        //
	//------------------------//
	global $button_ini;
	if( $button_ini === null)
	{
		// ボタン設定読込み
		$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
	}
	
	//------------------------//
	//          定数          //
	//------------------------//
	$column_num = 0;
	$button_num = $button_ini[$fileName]['set_button_'.$buttonPosition];
	$button_num_array = explode(",",$button_num);
	$total_button = count($button_num_array);
	
	
	//------------------------//
	//          変数          //
	//------------------------//
	$count = 0;
	$button_html = "";
	
	//------------------------//
	//     ボタン作成処理     //
	//------------------------//
	if($column_num == 0 )
	{
		$column_num = $total_button;
	}
	while ($count != $total_button)
	{
		for($i = 0 ; $i < $column_num ; $i++)
		{
			if( $button_num_array[$count] == "" )
			{
				$count++;
				continue;
			}
			$button_html .="<div class = 'left' style =' HEIGHT:".
							$button_ini[$button_num_array[$count]]['size_y']."px'>";
			$button_html .="<input type = 'submit' class = 'button'";
			$button_html .=" name = '".$button_ini[$button_num_array[$count]]['button_name']."' ";
			$button_html .=" value = '".$button_ini[$button_num_array[$count]]['value']."' ";
			$button_html .=" style='WIDTH:".$button_ini[$button_num_array[$count]]['size_x']."px  ;";
			$button_html .=" HEIGHT:".$button_ini[$button_num_array[$count]]['size_y']."px' >";
			$button_html .="</div>";
			$count++;
			if($total_count == $total_button)
			{
				break  2;
			}
		}
		$button_html .="<div style='clear:both;'></div>";
	}
	return ($button_html);
}

 /**
  * メニュー項目生成用関数
  *
  * @param $fileName 画面ID 
  * @param $button_ini_array Button.ini設定ファイルの読み込み情報
  * 
  * @return string メニュー項目HTML文字列
  *
 */
function makeMenuHtml( $fileName, &$button_ini )
{
    // 設定値
    $button_value = $button_ini[$fileName]['value'];
    $button_name = $button_ini[$fileName]['button_name'];
    $permission = $button_ini[$fileName]['permission'];

	$judge = isPermission($fileName);
	if($judge == false)
	{
		return '';
	}	
	//権限が満たない場合は表示しない
	/*if( $permission >= $_SESSION['KENGEN'] )
	{	
		if($_SESSION['KENGEN'] !== 9)//権限が9の場合はすべて見れる
		{
			return '';
		}	
	}*/
	
    // ボタンのname設定を"_"で区切る
    $button_name_array = explode("_",$button_name);

	$menu_html = '';
    // 2番目が4のものはメニュー
    if( $button_name_array[1] == '4' )
    {
		 // メニュータグ開始
		$menu_html .= '<li class="sub-menu">';
        $menu_html .= '<a href="#">'.$button_value.'</a>';
        // ボタン配列を取得
        $menuKey = $button_name_array[0].'_'.$button_name_array[1];
        $child_button_num = $button_ini[$menuKey]['set_button_center'];
        // ","で区切る
        $child_button_array = explode(',',$child_button_num);
        // 設定の数
        $child_count = count($child_button_array);

        $count = 0;

        //------------------------//
        //     メニュー作成処理    //
        //------------------------//
        $menu_html .='<ul>';
        while ($count < $child_count)
        {
            // メニューキー
            $menu = $child_button_array[$count];
            // 子メニューの作成
            $menu_html .= makeMenuHtml( $menu, $button_ini );
            // カウンタ++
            $count++;
        }
        $menu_html .='</ul>';

		 // メニュータグ終了
		$menu_html .= '</li>';
    }
    else
    {
        // URLエンコード
        $urlencode = rawurlencode($button_value);
        // 遷移先URLを含めてメニュー項目作成
        // ----2018/11/19 クラス化----//
        //$menu_html .= "<a href=\"pageJump.php?".$button_name."=".$urlencode."\">".$button_value."</a>";
		$menu_html .= '<li>';
        $menu_html .= '<a href="main.php?'.$button_name.'='.$urlencode.'">'.$button_value.'</a>';
		$menu_html .= "</li>\n";
    }

	return ($menu_html);
}

 /**
  * メニュー項目生成用関数
  *
  * @param $fileName 画面ID 
  * @param $button_ini_array Button.ini設定ファイルの読み込み情報
  * 
  * @return string メニュー項目HTML文字列
  *
 */
function makeAllMenu()
{
	// ボタン設定読込み
	global $button_ini;
	if( $button_ini === null){
		// ボタン設定読込み
		$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
	}

	if(!isset($_SESSION['KENGEN']))		{
		$_SESSION['KENGEN'] = 0;
	}
	// 設定
	$button_num = $button_ini['MENU_4']['set_button_center'];
	// カンマで区切る
	$button_num_array = explode(",",$button_num);
	$total_button = count($button_num_array);

	//------------------------//
	//     メニュー作成処理     //
	//------------------------//
	$button_count = 0;
	$button_html = '<div id="contents" class="content" ><ul class="nav"><li><a href="main.php?TOP_5">TOP</a></li>';
	//$button_html = '<ul class="nav"><li><a href="main.php?TOP_5">TOP</a></li>';
	while ($button_count < $total_button){
		// メニューキー
		$menu = $button_num_array[$button_count];
		//関数呼び出し
		$button_html .= makeMenuHtml( $menu, $button_ini );

		$button_count++;
	}

	$button_html .='<li><a href="login.php">ログアウト</a></li><!--nav--></ul></div>';
	//$button_html .='<li><a href="login.php">ログアウト</a></li><!--nav--></ul>';

	return ($button_html);
}

function isPermission($filename)
{
	// ボタン設定読込み
	global $button_ini;
	if( $button_ini === null){
		// ボタン設定読込み
		$button_ini = parse_ini_file("./ini/button.ini",true);	// ボタン基本情報格納.iniファイル
	}

	// 設定
	$permission = $button_ini[$filename]['permission'];
	//権限が十分か
	$is_permission = ( $permission <= $_SESSION['KENGEN'] );

	return $is_permission;
}

