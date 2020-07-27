

/////////////////////////////////////////////////////////////////////////////////////
//                                                                                 //
//                                                                                 //
//                             ver 1.1.0 2014/07/03                                //
//                                                                                 //
//                                                                                 //
/////////////////////////////////////////////////////////////////////////////////////




//---------------------------------------------------//
//                                                   //
//             入力チェック関数                      //
//   引数name : ドキュメントID                       //
//   引数size : 最大文字入力文字数                   //
//   引数type : 入力タイプ(                          //
//                       1:全角のみ                  //
//                       2:半角のみ                  //
//                       3:半角英数のみ(記号不可)    //
//                       4:半角数字のみ              //
//                       5:All OK                    //
//   引数isnotnull:入力必須か                        //
//   戻り値judge:チェック結果                        //
//                                                   //
//---------------------------------------------------//
function inputcheck(name,size,type,isnotnull,isJust){
    
	var judge =true;
	var str = document.getElementById(name).value;
	m = String.fromCharCode(event.keyCode);
	var len = 0;
	var str2 = escape(str);
        
        // 空白か確認
        if(isnotnull === 1)
	{
		if(document.getElementById(name).value === '')
		{
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
			judge = false;
			window.alert('値を入力してください');
		}
		else if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
	}else if(isnotnull === 0) {
            // 入力値が空だったら戻る
            if(document.getElementById(name).value === '')
            {
               return judge;
            }
        }
        
	if(type===1)
	{
		for(i = 0; i < str2.length; i++, len++){
			if(str2.charAt(i) === "%"){
				if(str2.charAt(++i) === "u"){
					i += 3;
					len++;
				}
				else
				{
					judge=false;
				}
				i++;
			}
			else
			{
				judge=false;
			}
		}
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
		else
		{
			window.alert('全角で入力してください');
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
		}
	}
	else if(type===2)
	{
		for(i = 0; i < str2.length; i++, len++){
			if(str2.charAt(i) === "%"){
				if(str2.charAt(++i) === "u"){
					i += 3;
					len++;
					judge=false;
				}
			}
		}
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
		else
		{
			window.alert('半角で入力してください');
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
		}
	}
	else if(type===3)
	{
                
		if(str.match(/[^0-9A-Za-z]+/))
		{
			judge=false;
		}
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
		else
		{
			window.alert('半角英数で入力してください');
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
		}
	}
	else if(type===4)
	{
                // 小数点の場合（/^[+,-]?([1-9]\d*|0)(\.\d+)?$/）
                // マイナス数値のみの場合（/^[-]?([1-9]\d*|0)$/）
		if(!str.match(/^[+,-]?([1-9]\d*|0)(\.\d+)?$/))
		{
			judge=false;
		}
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
		else
		{
			window.alert('半角数字で入力してください');
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
		}
	}
	else if(type===5)
	{
            if(name.indexOf('YUBIN') > -1 || name.indexOf('TEL') > -1 || name.indexOf('FAX') > -1)
            {
                if(!str.match(/^[0-9\-]+$/))
		{
			judge=false;
		}
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
		else

		{
			window.alert('半角数字記号で入力してください');
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
		}
            }
            else
            {
                if(!str.match(/^[\x20-\x7e]*$/))
		{
			judge=false;
		}
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';
			$("#"+name).removeClass('colorChange');
		}
		else
		{
			window.alert('半角英数字記号で入力してください');
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
		}
            }
	}
//	if (size < (str.length))
	if (size < strlen(str) && isJust === 2)
	{
		if("\b\r".indexOf(m, 0) < 0)
		{
			window.alert(size+'文字以内で入力してください');
		}
//              document.getElementById(name).style.backgroundColor = '#ff0000';
		$("#"+name).addClass('colorChange');
                judge = false;
	}
	else if(isJust === 2)
	{
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
	}
	else if (size !== strlen(str) && strlen(str) !== 0 && isJust === 1)
	{
		if("\b\r".indexOf(m, 0) < 0)
		{
			window.alert(size+'文字で入力してください');
		}
//              document.getElementById(name).style.backgroundColor = '#ff0000';
		$("#"+name).addClass('colorChange');
		judge = false;
	}
	else if(isJust === 1)
	{
		if(judge)
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
	}
	
	
	return judge;
}

function notnullcheck(id,isnotnull)
{
	if(isnotnull === 1)
	{
		var selectnum = document.getElementById(id).selectedIndex;
		if(document.getElementById(id).options[selectnum].value === "")
		{
//			document.getElementById(name).style.backgroundColor = '#ff0000';
			$("#"+name).addClass('colorChange');
			judge = false;
				window.alert('値を選択して下さい');
		}
		else
		{
//			document.getElementById(name).style.backgroundColor = '';			
			$("#"+name).removeClass('colorChange');
		}
	}
}


function strlen(str) {
  var ret = 0;
  for (var i = 0; i < str.length; i++,ret++) {
    var upper = str.charCodeAt(i);
    var lower = str.length > (i + 1) ? str.charCodeAt(i + 1) : 0;
    if (isSurrogatePear(upper, lower)) {
      i++;
    }
  }
  return ret;
}

function strsub(str, begin, end) {
  var ret = '';
  for (var i = 0, len = 0; i < str.length; i++, len++) {
    var upper = str.charCodeAt(i);
    var lower = str.length > (i + 1) ? str.charCodeAt(i + 1) : 0;
    var s = "";
    if(isSurrogatePear(upper, lower)) {
      i++;
      s = String.fromCharCode(upper, lower);
    } else {
      s = String.fromCharCode(upper);
    }
    if (begin <= len && len < end) {
      ret += s;
    }
  }
  return ret;
}

function isSurrogatePear(upper, lower) {
  return 0xD800 <= upper && upper <= 0xDBFF && 0xDC00 <= lower && lower <= 0xDFFF;
}


function check(checkList, checkNull, notNullType)
{
	var judge = true;
	var checkListArray = checkList.split(",");
	for (var i = 0 ; i < checkListArray.length ; i++ )
	{
		var param = checkListArray[i].split("~");
		if(!inputcheck(param[0],Number(param[1]),Number(param[2]),Number(param[3]),Number(param[4])))
		{
			judge = false;
		}
	}
	return judge;
}
