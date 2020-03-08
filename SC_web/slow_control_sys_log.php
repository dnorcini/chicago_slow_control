<?php
// slow_control_log.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");

if (empty($_SESSION['choose_message_type']))
    $_SESSION['choose_message_type'] = $message_types_array;

if (isset($_POST['s_all']))             // if the select all button has been pressed do this:
    $_SESSION['choose_message_type']= $message_types_array;

if (isset($_POST['choose_type_P']))
    $_SESSION['choose_message_type'] = $_POST['choose_type_P'];

$type_q_string = "`type` = \"".$_SESSION['choose_message_type'][0]."\" ";
for ($i=1; $i < count($_SESSION['choose_message_type']); $i++)
    $type_q_string = $type_q_string." OR `type` = \"".$_SESSION['choose_message_type'][$i]."\"";

if (!(empty($_POST['search_string'])))             
{
    if (!get_magic_quotes_gpc())
	$_POST['search_string'] = addslashes($_POST['search_string']);
    $type_q_string = "(".$type_q_string.") AND (`msgs` LIKE '%".$_POST['search_string']."%')";
}

if (!isset($_SESSION['num_msgs']))
    $_SESSION['num_msgs'] = 10;

if (!isset($_SESSION['go_msg_time']))
  $_SESSION['go_msg_time'] = -1;

if (isset($_POST['num_msgs']))
    $_SESSION['num_msgs'] = $_POST['num_msgs'];

if ($_SESSION['num_msgs'] == -1)
    $query = "SELECT * FROM `msg_log` WHERE (".$type_q_string.") ORDER BY `time` DESC";

else if ($_SESSION['num_msgs'] == -2)
    $query = "SELECT * FROM `msg_log`  WHERE ((".$type_q_string.") AND  `time` BETWEEN ".$_SESSION['t_min_p']." AND ".$_SESSION['t_max_p'].") ORDER BY `time` DESC";

else if ($_SESSION['num_msgs'] < -5)
    $query = "SELECT * FROM `msg_log`  WHERE (".$type_q_string.") ORDER BY `time` ASC LIMIT ". abs($_SESSION['num_msgs']);

else
    $query = "SELECT * FROM `msg_log`  WHERE (".$type_q_string.") ORDER BY `time` DESC LIMIT ". $_SESSION['num_msgs'];


$result = mysql_query($query);
if (!$result)	
    die ("Could not query the database <br />" . mysql_error());

$time = array();
$msgs = array();
$types = array();
$is_error = array();
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{	
    $time[] = (int)$row['time'];
    $msgs[] = $row['msgs'];
    $types[] = $row['type'];
    $is_error[] = (int)$row['is_error'];
} 
mysql_close($connection);


echo ('<TABLE border="1" cellpadding="2" width=100%>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD align=center>');
echo ('Choose types: &#160 &#160');
foreach ($message_types_array as $message_type)
{
    echo ($message_type.':<input type="checkbox" name="choose_type_P[]" value="'.$message_type.'" ');
    if (in_array($message_type, $_SESSION['choose_message_type']))
	echo ('checked="checked" />  &#160 &#160; ');
    else 
	echo ('/>  &#160 &#160');
    echo ('</font>');
}
echo ('<input type="image" src="pixmaps/edit.png" value="Change View" alt="Change" title="Refresh page with selected message types">');
echo ('</TD>');
echo ('</FORM>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD  align=center>');
echo ('Number of messages to show: <select name="num_msgs"> ');
foreach ($num_msgs_array as $st_s => $st_v)
{
    echo('<option ');
    if ($st_v == $_SESSION['num_msgs'])
    {
	echo ('selected="selected"');
    }
    echo(' value="'.$st_v.'" >'.$st_s.'</option> ');
}
echo ('</select>');

echo ('<input type="image" src="pixmaps/reload.png" value="Change" alt="Refresh" title="Refresh page with new number of messages">');
echo ('</TD>');
echo ('</FORM>');

echo ('</TABLE>');


echo ('<TABLE border="0" cellpadding="1" cellspacing="2">');
echo ('<TD align=center>');
echo ('&#160 &#160 &#160 &#160 &#160 &#160');
echo ('</TD>');

echo ('<FORM action='.$_SERVER['PHP_SELF'].' method="post">');  
echo ('<TD align=right>');
echo ('Search Systems Log:');
echo ('</TD>');
echo ('<TD align=left>');
echo ('<input type="text"  name="search_string">');
echo ('</TD>');
echo ('</FORM>');
echo ('</TABLE>');


$n_msgs = count($time);

echo ('<FORM action="slow_control_plots.php" method="post">');

echo ('<TABLE border="1" cellpadding="4" cellspacing="2">');
echo ('<TR>');

echo ('<TH align=center>');
echo ('Plot');  
echo ('</TH>');

echo ('<TH align=left>');
echo ('Time');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Type');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Message');  
echo ('</TH>');
echo ('<TBODY>');
echo ('</TR>');
echo ('<TBODY>');

for ($i = 0; $i < $n_msgs; $i++)
{
    echo ('<TR valign="center">');
    echo ('<TD align=center>');    
    if ($_SESSION['go_msg_time'] == $time[$i])
	echo ('<input type="image" src="pixmaps/plot_line.png" name="go_msg_time" value='.$time[$i].' >');
    else
	echo ('<input type="image" src="pixmaps/plot.png" name="go_msg_time" value='.$time[$i].' >');
    echo ('</FORM>');
    echo ('</TD>');
    
    echo ('<TD align=left>');
    if ($is_error[$i])
	echo ('<font color="red">');
    echo (date("M d, Y   G:i:s", $time[$i]));
    echo ('</TD>');
    
    echo ('<TD align=left>');
    if ($is_error[$i])
	echo ('<font color="red">');
    echo ($types[$i]);
    echo ('</TD>');
    
    echo ('<TD align=left>');
    echo ('<PRE>');
    echo ($msgs[$i]);
    echo ('</PRE>');
    echo ('</TD>');
    echo ('</TR>');
    
}
echo ('</TABLE>');

echo ('</FORM>');

echo(' </body>');
echo ('</HTML>');
?>
