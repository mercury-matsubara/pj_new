<?php
/*
Plugin Name: paka3_editer_media_button
Plugin URI: http://www.paka3.com/wpplugin
Description: 投稿画面からポップアップウィンドウを開き、（入力した）値をエディタに挿入
Author: Shoji ENDO
Version: 0.1
Author URI:http://www.paka3.com/
*/

$p3EMB= new Paka3EditerMediaButton();

class Paka3EditerMediaButton
{
	public function __construct(){
		add_filter( "media_buttons_context" , array( &$this, "paka3_media_buttons_context" ) );
		//ポップアップウィンドウ
		//media_upload_{ $type }
		add_action('media_upload_paka3Type', array( &$this,'paka3_wp_iframe' ) );
		//クラス内のメソッドを呼び出す場合はこんな感じ。
		add_action( "admin_head-media-upload-popup", array( &$this, "paka3_head" ) );
	}


	public function paka3_head(){
		global $type;
		if( $type == "paka3Type" ){
		echo <<< EOS
			<script type="text/javascript">
			jQuery(function($) {
		
				$(document).ready(function() {
					$('#paka3_ei_btn_yes').on('click', function() {
						var str = $('#paka3_editer_insert_content').val();
						//inlineのときはwindow
						top.send_to_editor( '<h3>' + str + '</h3>');
						top.tb_remove(); 
					});
					$('#paka3_ei_btn_no').on('click', function() {
						top.tb_remove(); 
					});
					
					//Enterキーが入力されたとき
					$('#paka3_editer_insert_content').on('keypress',function () {
						if(event.which == 13) {
							$('#paka3_ei_btn_yes').trigger("click");
						}
						//Form内のエンター：サブミット回避
						return event.which !== 13;
					});
				});
			})
			</script>
EOS;
		}
	}

	//##########################
	//メディアボタンの表示
	//##########################
	public function paka3_media_buttons_context ( $context ) {
		$img = plugin_dir_url( __FILE__ ) ."icon.png";
		$link = "media-upload.php?tab=paka3Tab&type=paka3Type&TB_iframe=true&width=600&height=550";

		$context .= <<<EOS
    <a href='{$link}'
    class='thickbox' title='お助けウィンドウ'>
      <img src='{$img}' /></a>
EOS;
		return $context;
	}


	//##########################
	//ポップアップウィンドウ
	//##########################
	function paka3_wp_iframe() {
		wp_iframe(array( $this , 'media_upload_paka3_form' ) );
	}

	//関数名をmedia_***としないとスタイルシートが適用されない謎
	function media_upload_paka3_form() {
		add_filter( "media_upload_tabs", array( &$this, "paka3_upload_tabs" ) ,1000);
		media_upload_header();
		echo <<< EOS
			<div id="paka3_popup_window" >
			<form  action="">
				<h2>ココには何かが書かれています</h2>
				<p>
				<input type="text" id="paka3_editer_insert_content" value="テストメッセージ" />
				</p>
				<input type="button" value="OK" id="paka3_ei_btn_yes" class="button button-primary" /> 
				<input type="button" value="キャンセル" id="paka3_ei_btn_no"  class="button" />
			</form>
			</div>
EOS;
	}

	//##########################
	//ポップアップウィンドウのタブ
	//##########################
	function paka3_upload_tabs( $tabs )
	{
		$tabs = array();
		$tabs[ "paka3Tab" ] = "文字だけ表示してるよ" ;
		return $tabs;
	}


}