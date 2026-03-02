<?php
// slow_control_log.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$req_priv = "full";
$never_ref = 1;

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

if (!isset($_SESSION['per_page']))
    $_SESSION['per_page'] = 10;
if (!isset($_SESSION['log_page']))
    $_SESSION['log_page'] = 1;
if (!isset($_SESSION['use_plot_times']))
    $_SESSION['use_plot_times'] = 0;

if (!isset($_SESSION['go_msg_time']))
    $_SESSION['go_msg_time'] = -1;

if (isset($_POST['per_page'])) {
    $_SESSION['per_page'] = (int)$_POST['per_page'];
    $_SESSION['log_page'] = 1;
    $_SESSION['use_plot_times'] = (!empty($_POST['use_plot_times'])) ? 1 : 0;
}
if (isset($_POST['log_page']))
    $_SESSION['log_page'] = max(1, (int)$_POST['log_page']);

$per_page = (int)$_SESSION['per_page'];
if ($per_page < 1) $per_page = 10;

$where = "(".$type_q_string.")";
if (!empty($_SESSION['use_plot_times']) && isset($_SESSION['t_min_p']) && isset($_SESSION['t_max_p']))
    $where .= " AND `time` BETWEEN ".(int)$_SESSION['t_min_p']." AND ".(int)$_SESSION['t_max_p'];

$count_query = "SELECT COUNT(*) AS total FROM `msg_log` WHERE ".$where;
$count_result = mysql_query($count_query);
if (!$count_result)
    die ("Could not query the database <br />" . mysql_error());
$total_msgs = (int)mysql_result($count_result, 0, 'total');
$total_pages = $total_msgs > 0 ? (int)ceil($total_msgs / $per_page) : 1;

$page = (int)$_SESSION['log_page'];
if ($page > $total_pages)
    $page = $total_pages;
if ($page < 1)
    $page = 1;
$_SESSION['log_page'] = $page;

$offset = ($page - 1) * $per_page;
$query = "SELECT * FROM `msg_log` WHERE ".$where." ORDER BY `time` DESC LIMIT ".$offset.", ".$per_page;

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

echo ('<div style="max-width:1200px;margin:0 auto;">');
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
echo ('Messages per page: <select name="per_page"> ');
foreach ($per_page_array as $st_s => $st_v)
{
    echo('<option ');
    if ($st_v == $_SESSION['per_page'])
    {
	echo ('selected="selected"');
    }
    echo(' value="'.$st_v.'" >'.$st_s.'</option> ');
}
echo ('</select>');
echo (' &#160 ');
echo ('<label><input type="checkbox" name="use_plot_times" value="1" ');
if (!empty($_SESSION['use_plot_times'])) echo ('checked="checked"');
echo (' /> Limit to plot time range</label>');

echo ('<input type="image" src="pixmaps/reload.png" value="Change" alt="Refresh" title="Refresh with new per-page and filters">');
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

// Pagination bar: Page X of Y, First / Previous / Next / Last
echo ('<TABLE border="0" cellpadding="4" cellspacing="2"><TR><TD align="center">');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="1" />');
echo ('<input type="submit" value="First" ');
if ($total_pages <= 1 || $page <= 1) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM> ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="'.($page > 1 ? $page - 1 : 1).'" />');
echo ('<input type="submit" value="Previous" ');
if ($total_pages <= 1 || $page <= 1) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM> ');
echo (' &#160 <strong>Page '.$page.' of '.$total_pages.'</strong> ('.$total_msgs.' messages) &#160 ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="'.($page < $total_pages ? $page + 1 : $total_pages).'" />');
echo ('<input type="submit" value="Next" ');
if ($total_pages <= 1 || $page >= $total_pages) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM> ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="'.$total_pages.'" />');
echo ('<input type="submit" value="Last" ');
if ($total_pages <= 1 || $page >= $total_pages) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM>');
echo ('</TD></TR></TABLE>');

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

// Pagination bar below table
echo ('<TABLE border="0" cellpadding="4" cellspacing="2"><TR><TD align="center">');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="1" />');
echo ('<input type="submit" value="First" ');
if ($total_pages <= 1 || $page <= 1) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM> ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="'.($page > 1 ? $page - 1 : 1).'" />');
echo ('<input type="submit" value="Previous" ');
if ($total_pages <= 1 || $page <= 1) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM> ');
echo (' &#160 <strong>Page '.$page.' of '.$total_pages.'</strong> &#160 ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="'.($page < $total_pages ? $page + 1 : $total_pages).'" />');
echo ('<input type="submit" value="Next" ');
if ($total_pages <= 1 || $page >= $total_pages) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM> ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post" style="display:inline;">');
echo ('<input type="hidden" name="log_page" value="'.$total_pages.'" />');
echo ('<input type="submit" value="Last" ');
if ($total_pages <= 1 || $page >= $total_pages) echo ('disabled="disabled" ');
echo ('/>');
echo ('</FORM>');
echo ('</TD></TR></TABLE>');

echo ('</div>');

echo(' </body>');
echo ('</HTML>');
?>
