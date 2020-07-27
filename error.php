
<?php
	session_start();
	header('Expires:-1'); 
	header('Cache-Control:'); 
	header('Pragma:');
?><!DOCTYPE html PUBLIC "-//W3C/DTD HTML 4.01">
<!-- saved from url(0013)about:internet -->
<!-- 
*------------------------------------------------------------------------------------------------------------*
*                                                                                                            *
*                                                                                                            *
*                                          ver 1.0.0  2014/05/09                                             *
*                                                                                                            *
*                                                                                                            *
*------------------------------------------------------------------------------------------------------------*
 -->

<html>



<head>
<title>ログイン</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="stylesheet" type="text/css" href="./css/list_css.css">
<script src='./jquery-1.8.3.min.js'></script>
<script src='./jquery-ui-1.10.3.custom.js'></script>
<script src='./jquery.flatshadow.js'></script>
<script src='./jquery.corner.js'></script>
<script src='./button_size.js'></script>
<script src="./jquery.corner.js"></script>
<script src="./list_jQuery.js"></script>
<script language="JavaScript"><!--
	history.forward();
	$(function()
	{
		$('.button').corner();
		$('.free').corner();
		$("a.title").flatshadow({
			fade: true
		});
		set_button_size();
	});
	function countdown(){
	location.href = "./login.php";
	}
--></script>
</head>
<body>
	<CENTER>
	
	<br><br>
	<a class = "title">不正アクセス</a>
	<br><br>
	
	<?php
		
			echo "ログイン画面に戻ってください";
			echo "<br>";
			echo "五秒後にログイン画面に遷移します。";
		?>
	
	<br><br>
	<a href="login.php">ログイン</a>
	</CENTER>
<script type="text/javascript"><!--
		setInterval( "countdown()", 5000 );
	// --></script>
</body>


</html>

