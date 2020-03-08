<?php
  // slow_control_inst_config.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2009.
  // james.nikkel@yale.edu
  //
session_start();
$never_ref = 1;
$req_priv = "config";
include("master_db_login.php");
include("slow_control_page_setup.php");
include("aux/get_inst_info.php"); 

////////////////////////////////////    Do table updates :

if (!empty($_POST['inst_run']))
{
    if ($inst_run[$_POST['inst_run']] == 0)
    {
	$query = "UPDATE `sc_insts` SET `run` = 1 WHERE `name` = \"".$_POST['inst_run']."\"";
	$result = mysql_query($query);
	
	$mesg = "".$_POST['inst_run']." started by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result += mysql_query($query);
    }
    else
    {
	if ($inst_run[$_POST['inst_run']])
	{
	    $query = "UPDATE `sc_insts` SET `run` = 0 WHERE `name` = \"".$_POST['inst_run']."\"";
	    $result = mysql_query($query);
	    
	    $mesg = "".$_POST['inst_run']." stopped by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	    $result += mysql_query($query);
	    
	}
	else
	{
	    $query = "UPDATE `sc_insts` SET `run` = 1 WHERE `name` = \"".$_POST['inst_run']."\"";
	    $result = mysql_query($query);
	    
	    $mesg = "".$_POST['inst_run']." started by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	    $result += mysql_query($query);
	}
    }
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_restart']))
{
    if ($inst_restart[$_POST['inst_restart']])
    {
	$query = "UPDATE `sc_insts` SET `restart` = 0 WHERE `name` = \"".$_POST['inst_restart']."\"";
	$result = mysql_query($query);
    }
    else
    {
	$query = "UPDATE `sc_insts` SET `restart` = 1 WHERE `name` = \"".$_POST['inst_restart']."\"";
	$result = mysql_query($query);
	
	$mesg = "".$_POST['inst_restart']." restarted by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result += mysql_query($query);
    }
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_WD_ctrl']))
{
    if ($inst_WD_ctrl[$_POST['inst_WD_ctrl']])
	$query = "UPDATE `sc_insts` SET `WD_ctrl` = 0 WHERE `name` = \"".$_POST['inst_WD_ctrl']."\"";
    else
	$query = "UPDATE `sc_insts` SET `WD_ctrl` = 1 WHERE `name` = \"".$_POST['inst_WD_ctrl']."\"";
    $result = mysql_query($query);

    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!(empty($_POST['inst_reset'])))
{
    if (!(empty($_POST['really_reset'])))
    {
	$query = "UPDATE `sc_insts` SET `PID`=-1, `start_time`=-1, `last_update_time`=-1  WHERE `name` = \"".$_POST['reset_inst']."\" LIMIT 1";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    }
}

if (!empty($_POST['inst_desc']))
{
    $_POST['inst_desc'] = trim($_POST['inst_desc']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_desc'] = addslashes($_POST['inst_desc']);
    
    $query = "UPDATE `sc_insts` SET `description` = \"".$_POST['inst_desc']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_subsys']))
{
    $_POST['inst_subsys'] = trim($_POST['inst_subsys']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_subsys'] = addslashes($_POST['inst_subsys']);
    
    $query = "UPDATE `sc_insts` SET `subsys` = \"".$_POST['inst_subsys']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_path']))
{
    $_POST['inst_path'] = trim($_POST['inst_path']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_path'] = addslashes($_POST['inst_path']);
    
    $query = "UPDATE `sc_insts` SET `path` = \"".$_POST['inst_path']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_dev_type']))
{
    $_POST['inst_dev_type'] = trim($_POST['inst_dev_type']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_dev_type'] = addslashes($_POST['inst_dev_type']);
    
    $query = "UPDATE `sc_insts` SET `dev_type` = \"".$_POST['inst_dev_type']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_dev_addy']))
{
    $_POST['inst_dev_addy'] = trim($_POST['inst_dev_addy']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_dev_addy'] = addslashes($_POST['inst_dev_addy']);
    
    $query = "UPDATE `sc_insts` SET `dev_address` = \"".$_POST['inst_dev_addy']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_user1']))
{
    $_POST['inst_user1'] = trim($_POST['inst_user1']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_user1'] = addslashes($_POST['inst_user1']);
    
    $query = "UPDATE `sc_insts` SET `user1` = \"".$_POST['inst_user1']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_user2']))
{
    $_POST['inst_user2'] = trim($_POST['inst_user2']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_user2'] = addslashes($_POST['inst_user2']);
    
    $query = "UPDATE `sc_insts` SET `user2` = \"".$_POST['inst_user2']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_parm1']))
{
    $_POST['inst_parm1'] = (double)$_POST['inst_parm1'];
    $query = "UPDATE `sc_insts` SET `parm1` = \"".$_POST['inst_parm1']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
    {	
	die ("Could not query the database <br />" . mysql_error());
    }   
}

if (!empty($_POST['inst_parm2']))
{
    $_POST['inst_parm2'] = (double)$_POST['inst_parm2'];
    $query = "UPDATE `sc_insts` SET `parm2` = \"".$_POST['inst_parm2']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['inst_note']))
{
    $_POST['inst_note'] = trim($_POST['inst_note']);
    if (!get_magic_quotes_gpc())
	$_POST['inst_note'] = addslashes($_POST['inst_note']);
    
    $query = "UPDATE `sc_insts` SET `notes` = \"".$_POST['inst_note']."\" WHERE `name` = \"".$_POST['inst_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (!empty($_POST['new_inst']))
{
    $_POST['new_inst'] = trim($_POST['new_inst']);
    if (!get_magic_quotes_gpc())
	$_POST['new_inst'] = addslashes($_POST['new_inst']);

    $s_length = strlen($_POST['new_inst']);
    if ($s_length > 16)
	die ("Instrument names must have fewer than 17 characters! <br>");
    $s_array = str_split($_POST['new_inst']);
    for ($i = 0; $i < $s_length; $i++)
    {
	$a_v = ord($s_array[$i]);
	
	if (!((($a_v > 47) && ($a_v < 58)) || (($a_v > 64) && ($a_v < 91)) || (($a_v > 96) && ($a_v < 123)) || ($a_v == 95)))
	    die ("Instrument names must only contain letters, numbers or underscores! <br>");	    
    }

    $query =  "INSERT into `sc_insts` (`name`)  VALUES ('".$_POST['new_inst']."')"; 
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}


/////////////   get all the physical instrument table entries here:
include("aux/get_inst_info.php"); 
include("aux/choose_inst_subsys.php"); 

mysql_close($connection);

////   This next section generate the HTML with the plot names in a table.

echo ('<TABLE border="1" cellpadding="2" width=100%>');
$i=1;
echo ('<TR>');

foreach ($_SESSION['choose_subsys'] as $unique_inst_subsys)
{
    foreach ($inst_names as $inst_name)
    {
	if (strcmp($inst_subsys[$inst_name], $unique_inst_subsys) == 0)  // show all entries in table!
	{
	    echo ('<TD>');
	  
	    echo ('<TABLE border="0" cellpadding="0" width=100%>');
	  
	    echo ('<TR>');                 /////  row 1
	    echo ('<TH align="left" colspan = 2>');
	    echo ($inst_name .':  ');
	    echo ('</TH>');   
	    
	    echo ('<TD align="center" colspan = 1>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<input type="hidden" name="inst_WD_ctrl" value="'.$inst_name.'">');
	    echo ('Controlled by Watchdog? ');
	    if ($inst_WD_ctrl[$inst_name])
		echo ('<input type="image" src="pixmaps/checked.png" alt="Controlled by Watchdog" title="Controlled by Watchdog">');
	    else
		echo ('<input type="image" src="pixmaps/unchecked.png" alt="Not controlled by Watchdog" title="Not controlled by Watchdog">');
	    echo ('</FORM>');
	    echo ('</TD>');

	    echo ('<TD align="right" colspan = 1>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<input type="hidden" name="inst_restart" value="'.$inst_name.'">');
	    if ($inst_restart[$inst_name])
		echo ('Restart <input type="image" src="pixmaps/checked.png" alt="Wait for restart" title="Wait for restart">');
	    else
		echo ('Restart <input type="image" src="pixmaps/unchecked.png" alt="Click to Restart" title="Click to Restart">');
	    echo ('</FORM>');
	    
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<input type="hidden" name="inst_run" value="'.$inst_name.'">');
	    if (strcmp($inst_dev_types[$inst_name], "watchdog") == 0)
		echo (' Run <input type="image" src="pixmaps/checked_grey.png" alt="Watchdog always runs" title="Watchdog always runs">');
	    else
	    {
		if ($inst_run[$inst_name])
		    echo (' Run <input type="image" src="pixmaps/checked.png" alt="Click to Stop" title="Click to Stop">');
		else
		    echo (' Run <input type="image" src="pixmaps/unchecked.png" alt="Click to Run" title="Click to Run">');
	    }
	    echo ('</FORM>');
	    echo ('</TD>');
	    echo ('</TR>');    
      
	    echo ('<TR>');                 /////  row 2
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="left" colspan = 4>');
	    echo ('Description: <input type="text" name="inst_desc" value="'.$inst_descs[$inst_name].'" size = 64>');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('</TR>');  
      
	    echo ('<TR>');                 /////  row 3
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="left" colspan = 2>');
	    echo ('Run path: <input type="text" name="inst_path" value="'.$inst_paths[$inst_name].'" size = 32>');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="right" colspan = 2>');
	    echo ('Sub System: <input type="text" name="inst_subsys" value="'.$inst_subsys[$inst_name].'" size = 16>');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('</TR>');  
      
	    echo ('<TR>');                 /////  row 4
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="left" colspan = 2>');
	    echo ('Device type: <input type="text" name="inst_dev_type" value="'.$inst_dev_types[$inst_name].'" size = 16>');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="right" colspan = 2>');
	    echo ('Device address: <input type="text" name="inst_dev_addy" value="'.$inst_dev_addys[$inst_name].'" size = 16>');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('</TR>');  
      
	    echo ('<TR>');                 /////  row 5
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="left" colspan = 2>');
	    echo ('User field 1: <input type="text" name="inst_user1" value="'.$inst_user1[$inst_name].'" size = 16> (txt)');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="right" colspan = 2>');
	    echo ('User field 2: <input type="text" name="inst_user2" value="'.$inst_user2[$inst_name].'" size = 16> (txt)');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('</TR>');  
      
	    echo ('<TR>');                 /////  row 6
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="left" colspan = 2>');
	    echo ('Parameter 1: <input type="text" name="inst_parm1" value="'.$inst_parm1[$inst_name].'" size = 8> (dbl)');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD align="right" colspan = 2>');
	    echo ('Parameter 2: <input type="text" name="inst_parm2" value="'.$inst_parm2[$inst_name].'" size = 8> (dbl)');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('</TR>');  
      
	    echo ('<TR>');                   
	    echo ('<TD align="center" colspan=4>');
	    echo ('-----');		      
	    echo ('</TD>');
	    echo ('</TR>');   
      
	    echo ('<TR>');                   
	    echo ('<TD align="left" colspan=4>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('Notes:');
	    echo ('<BR>');
	    echo ('<TEXTAREA name="inst_note" value="'.$inst_notes[$inst_name].'" rows="4" cols="75">');
	    if ($inst_notes[$inst_name] != "NULL")
		echo ($inst_notes[$inst_name]);
	    echo ('</TEXTAREA>');
	    echo ('<input type="hidden" name="inst_name" value="'.$inst_name.'">');
	    echo ('<BR>');
	    echo ('<input type="submit" name="Edit" value="Edit Note">');
	    echo ('</TD>');
	    echo ('</FORM>');
	    echo ('</TR>');   
      
	    echo ('<TR>');  
	    echo ('<TD align="right" colspan=4>'); 
	    echo ('<FORM action="slow_control_del_inst.php" method="post">');
	    echo ('<input type="image" src="pixmaps/drop.png">  Delete this instrument ');
	    echo ('<input type="hidden" name="del_inst" value='.$inst_name.'>');
	    echo ('</FORM>');
	    echo ('</TD>');
	    echo ('</TR>');    
      
	    echo ('<TR>');  
	    echo ('<TD align="right" colspan=4>'); 
	    echo ('<FORM action="slow_control_reset_inst.php" method="post">');
	    echo ('<input type="image" src="pixmaps/red_circ.png">  Reset this instrument ');
	    echo ('<input type="hidden" name="reset_inst" value='.$inst_name.'>');
	    echo ('</FORM>');
	    echo ('</TD>');
	    echo ('</TR>');    
      
      
	    echo ('<TR>');                    ///////////////////  footer
	    echo ('<TD align="center" colspan=4>');
	    echo ('-----');
	    echo ('<BR>');
	    if ($inst_PIDs[$inst_name] == -1)
	    {
		echo ('Not running');	
	    }
	    else
	    {
		echo ('PID: '.$inst_PIDs[$inst_name]);
		echo ('<BR>');
		echo ('Program started: '.(time()-$inst_start_times[$inst_name]).' seconds ago. ('. date("G:i:s  M d, y", $inst_start_times[$inst_name]) . ')');
		echo ('<BR>');
		echo ('Last update: '.(time()-$inst_last_update_times[$inst_name]).' seconds ago. ');
	    }
	    echo ('</TD>');
      
	    echo ('</TR>');
      
	    echo ('</TABLE>');
      
	    echo ('</TD>');
	    if ($i % 2 ==0)
	    {
		echo ('</TR>');
		echo ('<TR>');
	    }
	    $i++;
	}
    }
}
echo ('</TR>');
echo ('</TABLE>');

echo ('<BR>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('New Instrument: <input type="text" name="new_inst" size = 32>');
echo ('<input type="submit" name="New" value="New">  (Less than or equal to 16 characters, no spaces)');
echo ('</FORM>');

echo ('</body>');
echo ('</HTML>');
?>
    
