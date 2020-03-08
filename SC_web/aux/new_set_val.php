<?php
// new_set_val.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2010
// james.nikkel@yale.edu
//
if (isset($_POST['set_sens_name']))
{
    if (check_access($_SESSION['privileges'], $sensor_ctrl_priv[$_POST['set_sens_name']], $allowed_host_array))
    {
	mysql_close($connection);
	include("master_db_login.php");
	$new_val = floatval($_POST['new_set_val']);
	if (is_float($new_val))
	{
	    $query = "INSERT INTO sc_sens_".$_POST['set_sens_name']." SET time = ".time().", value = ".$new_val.", rate = 0";
	    $result = mysql_query($query);
	    if (!$result)
	    {	
		die ("Could not query the database <br />" . mysql_error());
	    }
	    
	    $mesg = "New setpoint of ".$_POST['set_sens_name']." = ".$new_val." requested by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Setpoint', '0')"; 
	    $result = mysql_query($query);	
	}
    }
    else 
    {
	echo('You do not have access to that control.<br>');
    }
}
?>
