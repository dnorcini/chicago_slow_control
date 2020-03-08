<?php
// slow_control_logbook.php
// Part of the CLEAN slow control.  
// James Nikkel, 2013
// james.nikkel@gmail.com
//
session_start();
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_users.php");

if (empty($_SESSION['lug_cat']))
    $_SESSION['lug_cat'] = $lug_cat_array;

if (empty($_SESSION['lug_subcat']))
    $_SESSION['lug_subcat'] = array_keys($lug_subcat_array);

if (isset($_POST['show_all']))             // if the select all button has been pressed do this:
{
    $_SESSION['lug_cat'] = $lug_cat_array;
    $_SESSION['lug_subcat'] = array_keys($lug_subcat_array);
}

if (isset($_POST['choose_cat']))
    $_SESSION['lug_cat'] = $_POST['choose_cat'];

if (isset($_POST['choose_subcat']))
    $_SESSION['lug_subcat'] = $_POST['choose_subcat'];

$q_select = "`category` = \"".$_SESSION['lug_cat'][0]."\" ";
for ($i=1; $i < count($_SESSION['lug_cat']); $i++)
    $q_select = $q_select." OR `category` = \"".$_SESSION['lug_cat'][$i]."\"";


include("aux/get_logbook_entries.php");
mysql_close($connection);

//////////////////////////////////////////////////////////////////////////////////////////


echo ('<TABLE border="1" cellpadding="2" cellspacing="2" width=100%>');

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<TD align=center>');
echo ('Choose categories: &#160 &#160');
foreach ($lug_cat_array as $lug_cat)
{
    echo ($lug_cat.':<input type="checkbox" name="choose_cat[]" value="'.$lug_cat.'" ');
    if (in_array($lug_cat, $_SESSION['lug_cat']))
	echo ('checked="checked" />  &#160 &#160; ');
    else 
	echo ('/>  &#160 &#160');
    echo ('</font>');
}
echo ('<input type="image" src="pixmaps/edit.png" value="Change View" alt="Change" title="Refresh page with selected entry types">');
echo ('</TD>');
echo ('</FORM>');

echo ('</TABLE>');

echo ('<TABLE border="0" cellpadding="1" cellspacing="2">');

echo ('<FORM action="slow_control_add_logbook_entry.php" method="post">');  
echo ('<TD align=left>');
echo ('Add New LogBook Entry');
echo ('</TD>');
echo ('<TD align=left>');
echo ('<input type="image" src="pixmaps/xedit.png" value="Change" alt="Refresh" title="Add New LogBook Entry">');
echo ('</TD>');
echo ('</FORM>');

echo ('<TD align=center>');
echo ('&#160 &#160 &#160 &#160 &#160 &#160');
echo ('</TD>');

echo ('<FORM action='.$_SERVER['PHP_SELF'].' method="post">');  
echo ('<TD align=right>');
echo ('Search Entries:');
echo ('</TD>');
echo ('<TD align=left>');
echo ('<input type="text"  name="search_text">');
echo ('</TD>');
echo ('</FORM>');
echo ('</TABLE>');

echo ('<TABLE border="1" cellpadding="4" cellspacing="2">');
echo ('<TR>');

echo ('<TH align=left>');
echo ('Plot');  
echo ('</TH>');

echo ('<TH align=center>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="lug_last" value=1>');
echo ('<input type="image" src="pixmaps/top.png" title="Go to latest entry">');
echo ('</FORM>');
echo('  ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden"name="lug_next" value=1>');
echo ('<input type="image" src="pixmaps/up.png" title="Go to more recent entries">');
echo ('</FORM>');
echo ('Action Time');  

echo ('</TH>');

echo ('<TH align=left>');
echo ('Run #');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Category / SubCategory');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Posted By / For');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Entry');  
echo ('</TH>');

echo ('</TR>');

foreach ($lug_entry_ids as $lug_entry_id)
{
    echo ('<TR valign="center">');
    echo ('<TD align=center>');    
    echo ('<FORM action="slow_control_plots.php" method="post">');
    if ($_SESSION['go_msg_time'] == $lug_action_time[$lug_entry_id])
        echo ('<input type="image" src="pixmaps/plot_line.png" name="go_msg_time" value='.$lug_action_time[$lug_entry_id].' >');
    else
        echo ('<input type="image" src="pixmaps/plot.png" name="go_msg_time" value='.$lug_action_time[$lug_entry_id].' >');
    echo ('</FORM>');
    echo ('</TD>');
    
    echo ('<TD align=center>');
    echo (date("G:i:s", $lug_action_time[$lug_entry_id]));
    echo ('<br>');
    echo (date("M d, Y", $lug_action_time[$lug_entry_id]));
    echo ('</TD>');
    
    echo ('<TD align=center>');
    echo ($lug_run_number[$lug_entry_id]);
    echo ('</TD>');
    
    echo ('<TD align=center>');
    echo ($lug_category[$lug_entry_id].' / '.$lug_subcategory[$lug_entry_id]);
    echo ('</TD>');
    
    echo ('<TD align=left>');
    echo ('<a href="mailto:'.$users_email[$lug_entry_user[$lug_entry_id]].'">'.$lug_entry_user[$lug_entry_id].'</a>');
    echo (' / <a href="mailto:'.$users_email[$lug_action_user[$lug_entry_id]].'">'.$lug_action_user[$lug_entry_id].'</a>');
    echo ('<br> at '); 
    echo (date("G:i:s", $lug_entry_time[$lug_entry_id]));
    echo (date(" M d, Y", $lug_entry_time[$lug_entry_id]));
    echo ('</TD>');
    
    echo ('<TD align=left>');
    echo ('<PRE>');
    echo ($lug_entry_description[$lug_entry_id]);
    echo ('</PRE>');
    echo ('</TD>');
    echo ('</TR>');
    
}
echo ('<TR valign="center">');
echo ('<TD align=center colspan="1">');
echo ('</TD>');

echo ('<TD align=center>');   
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="lug_prev" value=1>');
echo ('<input type="image" src="pixmaps/down.png" title="Go to previous entries">');
echo ('</FORM>');
echo (' ');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="hidden" name="lug_first" value=1>');
echo ('<input type="image" src="pixmaps/bottom.png" title="Go to first entry">');
echo ('</FORM>');
echo ('</TD>');

echo ('<TD align=center colspan="4">');
echo ('</TD>');
echo ('</TR>');

echo ('</TABLE>');

echo(' </body>');
echo ('</HTML>');
?>
