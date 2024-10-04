<?php
echo('<title>'. $global_string1["Title"].' QC system </title>');
echo('</head>');

$_SESSION['textcolour']=$global_string1["web_text_colour"];
$_SESSION['bgcolour']=$global_string1["web_bg_colour"];
echo('<body
   bgcolor="'.$_SESSION['bgcolour'].'" 
   text="'.$_SESSION['textcolour'].'"
>');
?>