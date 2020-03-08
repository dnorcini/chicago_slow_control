<?php
// slow_control_add_note.php
// Part of the CLEAN slow control.  
// James Nikkel, 2013.
// james.nikkel@gmail.com
//
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_users.php");
include("aux/get_runs.php");

if (isset($_POST['Add']))
{
    mysql_close($connection);
    include("master_db_login.php");
    
    if (!get_magic_quotes_gpc())
      $_POST['entry_description'] = addslashes($_POST['entry_description']);
    $_POST['run_number'] = (int)$_POST['run_number'];
    $_POST['action_time'] = strtotime($_POST['action_time']);
    
    if (($_POST['run_number'] > $run_nums[0]) || ($_POST['run_number'] < 1))
        echo ('<br>Invalid Run number. <br>');
    else if (strcmp($_POST['category'], "none") == 0)
        echo ('<br>You must choose a category and subcategory<br>');
    else
    {
        $catsubcat = explode(":_:", $_POST['category']);
	$cat = $catsubcat[0];
	$subcat = $catsubcat[1];
	
	$query = "INSERT into `lug_entries` ";
	$query = $query."(`action_user`, `action_time`, `edit_user`, `edit_time`, ";
	$query = $query."`run_number`, `category`, `subcategory`, `entry_description`) ";
	$query = $query."VALUES ('". $_POST['action_user']."', '".$_POST['action_time']."', '";
	$query = $query.$_SESSION['user_name']."', '".time()."', '".$_POST['run_number']."', '";
	$query = $query.$cat."', '".$subcat."', '".$_POST['entry_description']."')";
	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
	$_SESSION['lug_indx_offset']=1e9;
	header('Location: slow_control_logbook.php');
    }
}
mysql_close($connection);

//////////////////////////////////////////////////////////////////////////////////////////

echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');

echo ('<TABLE border="1" cellpadding="4" cellspacing="2" width="100%">');
echo ('<TR>');

echo ('<TH align=left>');
echo ('Submitted for');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Run Number');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Action time');  
echo ('</TH>');

echo ('<TH align=left>'); 
echo ('Category / Subcategory');  
echo ('</TH>');

echo ('</TR>');

echo ('<TR>');

echo ('<TD align=left>');
echo ('<select name="action_user"> ');
foreach ($users_user_name as $user_name)
{
    echo('<option ');
    if (isset($_POST['Add']))
    {
	if (strcmp($_POST['action_user'], $user_name) == 0)
	    echo(' selected = 1 ');
    }
    else
	if (strcmp($_SESSION['user_name'], $user_name) == 0)
	    echo(' selected = 1 ');
    echo(' value="'.$user_name.'" >'.$user_name.'</option> ');
}
echo ('</select>');
echo ('</TD>');

echo ('<TD align=left>');
if (isset($_POST['Add']))
    echo ('<input type="text" name="run_number" value="'.$_POST['run_number'].'" >');
else
    echo ('<input type="text" name="run_number" value="'.$run_nums[0].'" >');
echo ('</TD>');

echo ('<TD align=left>');
if (isset($_POST['Add']))
    echo ('<input type="text" name="action_time" value="'.date("G:i:s  M d, y", $_POST['action_time']).'" >');
else
    echo ('<input type="text" name="action_time" value="'.date("G:i:s  M d, y", time()).'" >');
echo ('</TD>');

echo ('<TD align=left>');
echo ('<select name="category"> ');
echo ('<OPTION selected label="none" value="none">None</OPTION>');
foreach ($lug_cat_array as $lug_cat)
{
  echo('<OPTGROUP label="'.$lug_cat.'"> ');
  for ($i = 0; $i < count($lug_subcat_array[0]); $i++)
    {
      if (strcmp($lug_subcat_array[0][$i], $lug_cat) == 0)
	{
	  echo('<option label="'.$lug_subcat_array[1][$i].'" value="'.$lug_cat.':_:'.$lug_subcat_array[1][$i].'" >'.$lug_cat.' / '.$lug_subcat_array[1][$i].'</option>');
	}
    }
  echo('</OPTGROUP> '); 
}
echo ('</select>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TH align=left colspan="5">'); 
echo ('Entry Description:');  
echo ('</TH>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align=left colspan="5">');
echo ('<TEXTAREA name="entry_description" rows="8" cols="100">');
if (isset($_POST['Add']))
    echo ($_POST['entry_description']);
echo ('</TEXTAREA>');
echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD align=left colspan="5">');
echo ('<input type="submit" name="Add" value="Add">');
echo ('</TD>');

echo ('</TR>');

echo ('</TABLE>');

echo ('</FORM>');

echo(' </body>');
echo ('</HTML>');

?>

