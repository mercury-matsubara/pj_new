<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>timer</title>
    <link rel ="stylesheet" href ="time.css">
<!--    <script src="./js/jquery.js"></script>-->
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script src="sample.js"></script>
    <script>
    window.onload = function(){
        // 画面開いたら一番最初に走る処理
        time(900);//15分で設定
    };
    //document.cookie = 'name=太郎';
    $.cookie('HOGE_KEY', 'HOGE_VALUE', {});
    var a = $.cookie("HOGE_KEY");
    alert(a);
    </script>
</head>
<body>
    <div id = "box">
        <div id ="timer">15:00</div>
        <form >
            <input type ="button" id ="start" name ="start" value ="スタート">
            <input type ="button" id ="stop"  name ="stop" value ="ストップ" >
            <input type ="button" id ="reset" name ="reset" value ="リセット">
        </form>
        <form name ="insert" action="sample2.php" method="post" id="send" enctype="multipart/form-data" >
           
            <input type ="submit" id ="reset" name ="reset" value ="リセット">
        </form>
        <div id="ajax">
            
        </div>
    </div>
</body>
</html>