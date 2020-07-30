<?php

function readRequire()
{
    $php = array();
//    require_once("f_Construct.php");
//    require_once("f_Button.php");
//    require_once("classesHtmlCustom.php");
//    require_once("classesHtmlPrint.php");
//    require_once("./customExecuter/EdabanExecute.php");
    $php[] = "f_Construct.php";
    $php[] = "f_Button.php";
    $php[] = "classesHtmlCustom.php";
    $php[] = "classesHtmlPrint.php";
    $php[] = "./customExecuter/edabanExecute.php";
    $php[] = "./customExecuter/pjendExecute.php";
    $php[] = "./customExecuter/pjcancelExecute.php";
    $php[] = "./customExecuter/getsuziExecute.php";
    $php[] = "./customExecuter/nenziExecute.php";
    $php[] = "./customExecuter/pjtourokuExecute.php";
    $php[] = "./customExecuter/progressExecute.php";
    $php[] = "./customExecuter/staffMoneySetExecute.php";
    $php[] = "./customHtml/getsuzi.php";
    $php[] = "./customHtml/nenzi.php";
    $php[] = "./customHtml/pjend.php";
    $php[] = "./customHtml/TOP.php";
    $php[] = "./customHtml/pjcancel.php";
    $php[] = "./customHtml/pjinsert.php";
    $php[] = "./customHtml/progress.php";
    $php[] = "./customHtml/staffMoneySet.php";
    return $php;
}