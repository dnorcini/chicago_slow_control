<?php
// slow_control_webcam.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");

mysql_close($connection);

$max_num_cams = 4;
$file_root = "webcam/cam";

echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<TR>');
for ($i = 0; $i < $max_num_cams; $i++)
{
    $file_name = $file_root.$i.'.jpg';
    echo ('<TH>');
    echo ('<IMG width="" src='.$file_name.' alt="Webcam '.$i.'">');
    echo ('<BR>');
    echo (date("M d, Y @ G:i:s", filemtime($file_name)));
    echo ('</TH>');
    if (($i+1) % 2 == 0)
    {
	echo ('</TR>');
	echo ('<TR>');
    }
}
echo ('</TR>');
echo('</TABLE>');

?>
