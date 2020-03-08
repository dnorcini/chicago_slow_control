<?php
  // slow_control_sens_config.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2009.
  // james.nikkel@yale.edu
  //
session_start();
$never_ref = 1;
$req_priv = "config";
include("master_db_login.php");
include("slow_control_page_setup.php");
$get_all_sensors = 1;
include("aux/get_sensor_info.php"); 
 /*
     if (!get_magic_quotes_gpc())
     $_POST['sens_type'] = addslashes($_POST['sens_type']);
     $old_type = $sensor_types[$_POST['sens_name']];
     if (strcmp($old_type, $_POST['sens_type']) != 0)
     {
     $query = "UPDATE `sc_sensors` SET `type` = \"".$_POST['sens_type']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
     $result = mysql_query($query);
     if (!$result)
     die ("Could not query the database <br />" . mysql_error());
	
     $mesg = "".$_POST['sens_name']." type changed from ".$old_type." to ".$_POST['sens_type']." by ".$_SESSION['user_name'].".";
     $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
     $result = mysql_query($query);
     }
    */
////////////////////////////////////    Do table updates :


/// the three toggles:
if (isset($_POST['sens_hide']))
{
    if ($sensor_hide[$_POST['sens_hide']])
	$query = "UPDATE `sc_sensors` SET `hide_sensor` = 0 WHERE `name` = \"".$_POST['sens_hide']."\"";
    else
	$query = "UPDATE `sc_sensors` SET `hide_sensor` = 1 WHERE `name` = \"".$_POST['sens_hide']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
    
    if ($sensor_hide[$_POST['sens_hide']])
	$mesg = "".$_POST['sens_hide']." un-hidden by ".$_SESSION['user_name'].".";
    else
	$mesg = "".$_POST['sens_hide']." hidden by ".$_SESSION['user_name'].".";
    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
    $result = mysql_query($query);	
}

if (isset($_POST['sens_show_rate']))
{
    if ($sensor_show_rate[$_POST['sens_show_rate']])
	$query = "UPDATE `sc_sensors` SET `show_rate` = 0 WHERE `name` = \"".$_POST['sens_show_rate']."\"";
    else
	$query = "UPDATE `sc_sensors` SET `show_rate` = 1 WHERE `name` = \"".$_POST['sens_show_rate']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
}

if (isset($_POST['sens_settable']))
{
    if ($sensor_settable[$_POST['sens_settable']])
	$query = "UPDATE `sc_sensors` SET `settable` = 0 WHERE `name` = \"".$_POST['sens_settable']."\"";
    else
	$query = "UPDATE `sc_sensors` SET `settable` = 1 WHERE `name` = \"".$_POST['sens_settable']."\"";
    $result = mysql_query($query);
    If (!$result)
	die ("Could not query the database <br />" . mysql_error());
    
    if ($sensor_settable[$_POST['sens_settable']])
	$mesg = "".$_POST['sens_settable']." set to un-settable by ".$_SESSION['user_name'].".";
    else
	$mesg = "".$_POST['sens_settable']." set to settable by ".$_SESSION['user_name'].".";
    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
    $result = mysql_query($query);	
}
///////////////////////////////////////////////////////////////////////////////////////////////

if (isset($_POST['sens_privileges']))
{   
    $query = "UPDATE `sc_sensors` SET `ctrl_priv` = \"".implode(",",$_POST['sens_privileges'])."\" WHERE `name` = \"".$_POST['sens_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
    
    $mesg = "".$_POST['sens_name']." control privileges changed by ".$_SESSION['user_name'].".";
    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
    $result = mysql_query($query);  
}

if (isset($_POST['sens_desc']))
{
    $_POST['sens_desc'] = trim($_POST['sens_desc']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_desc'] = addslashes($_POST['sens_desc']);
    if (strcmp($sensor_descs[$_POST['sens_name']], $_POST['sens_desc']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `description` = \"".$_POST['sens_desc']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." description changed by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);  
    }
}

if (isset($_POST['sens_type']))
{
    if (strcmp($sensor_types[$_POST['sens_name']], implode(",",$_POST['sens_type'])) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `type` = \"".implode(",",$_POST['sens_type'])."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    
	$mesg = "".$_POST['sens_name']." type changed by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);
    }
}

if (isset($_POST['sens_subtype']))
{
    $_POST['sens_subtype'] = trim($_POST['sens_subtype']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_subtype'] = addslashes($_POST['sens_subtype']);
    $old_subtype = $sensor_subtypes[$_POST['sens_name']];
    if (strcmp($old_subtype, $_POST['sens_subtype']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `subtype` = \"".$_POST['sens_subtype']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." subtype changed from ".$old_subtype." to ".$_POST['sens_subtype']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);
    }
}

if (isset($_POST['sens_num']))
{
    $_POST['sens_num'] = (int)$_POST['sens_num'];
    $old_num = $sensor_numbers[$_POST['sens_name']];
    if ($old_num != $_POST['sens_num'])
    {
	$query = "UPDATE `sc_sensors` SET `num` = \"".$_POST['sens_num']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." num changed from ".$old_num." to ".$_POST['sens_num']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_inst']))
{
    $_POST['sens_inst'] = trim($_POST['sens_inst']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_inst'] = addslashes($_POST['sens_inst']);
    $old_inst = $sensor_instruments[$_POST['sens_name']];
    if (strcmp($old_inst, $_POST['sens_inst']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `instrument` = \"".$_POST['sens_inst']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." instrument changed from ".$old_inst." to ".$_POST['sens_inst']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_units']))
{
    $_POST['sens_units'] = trim($_POST['sens_units']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_units'] = addslashes($_POST['sens_units']);
    $old_units = $sensor_units[$_POST['sens_name']];
    if (strcmp($old_units, $_POST['sens_units']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `units` = \"".$_POST['sens_units']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." units changed from ".$old_units." to ".$_POST['sens_units']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_discrete_vals']))
{
    $_POST['sens_discrete_vals'] = trim($_POST['sens_discrete_vals']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_discrete_vals'] = addslashes($_POST['sens_discrete_vals']);
    $old_disc_vals = $sensor_discrete_vals[$_POST['sens_name']];
    if (strcmp($old_disc_vals, $_POST['sens_discrete_vals']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `discrete_vals` = \"".$_POST['sens_discrete_vals']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." discrete unit vals changed from ".$old_disc_vals." to ".$_POST['sens_discrete_vals']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_period']))
{
    $_POST['sens_period'] = (int)$_POST['sens_period'];
    $old_period = $sensor_update_period[$_POST['sens_name']];
    if ($old_period != $_POST['sens_period'])
    {
	if ($_POST['sens_period'] > 0)
	{
	    $query = "UPDATE `sc_sensors` SET `update_period` = \"".$_POST['sens_period']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	    $result = mysql_query($query);
	    if (!$result)
		die ("Could not query the database <br />" . mysql_error());
	}
	
	$mesg = "".$_POST['sens_name']." update period changed from ".$old_period." to ".$_POST['sens_period']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_grace']))
{
    $_POST['sens_grace'] = (int)$_POST['sens_grace'];
    $old_grace = $sensor_grace[$_POST['sens_name']];
    if ($old_grace != $_POST['sens_grace'])
    {
	if ($_POST['sens_grace'] > 0)
	{	
	    $query = "UPDATE `sc_sensors` SET `grace` = \"".$_POST['sens_grace']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	    $result = mysql_query($query);
	    if (!$result)
		die ("Could not query the database <br />" . mysql_error());
	    
	    $mesg = "".$_POST['sens_name']." grace period changed from ".$old_grace." to ".$_POST['sens_grace']." by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	    $result = mysql_query($query); 
	}
    }
}

if (isset($_POST['sens_format']))
{
    $_POST['sens_format'] = trim($_POST['sens_format']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_format'] = addslashes($_POST['sens_format']);
    if  (strcmp($sensor_num_format[$_POST['sens_name']], $_POST['sens_format']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `num_format` = \"".$_POST['sens_format']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    }
}

if (isset($_POST['sens_user1']))
{
    $_POST['sens_user1'] = trim($_POST['sens_user1']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_user1'] = addslashes($_POST['sens_user1']);

    $old_field = $sensor_user1[$_POST['sens_name']];
    if (strcmp($old_field, $_POST['sens_user1']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `user1` = \"".$_POST['sens_user1']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." string field 1 changed from ".$old_field." to ".$_POST['sens_user1']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_user2']))
{
    $_POST['sens_user2'] = trim($_POST['sens_user2']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_user2'] = addslashes($_POST['sens_user2']);

    $old_field = $sensor_user2[$_POST['sens_name']];
    if (strcmp($old_field, $_POST['sens_user2']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `user2` = \"".$_POST['sens_user2']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." string field 2 changed from ".$old_field." to ".$_POST['sens_user2']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_user3']))
{
    $_POST['sens_user3'] = trim($_POST['sens_user3']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_user3'] = addslashes($_POST['sens_user3']);

    $old_field = $sensor_user3[$_POST['sens_name']];
    if (strcmp($old_field, $_POST['sens_user3']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `user3` = \"".$_POST['sens_user3']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." string field 3 changed from ".$old_field." to ".$_POST['sens_user3']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_user4']))
{
    $_POST['sens_user4'] = trim($_POST['sens_user4']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_user4'] = addslashes($_POST['sens_user4']);

    $old_field = $sensor_user4[$_POST['sens_name']];
    if (strcmp($old_field, $_POST['sens_user4']) != 0)
    {
	$query = "UPDATE `sc_sensors` SET `user4` = \"".$_POST['sens_user4']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." string field 4 changed from ".$old_field." to ".$_POST['sens_user4']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query); 
    }
}

if (isset($_POST['sens_parm1']))
{
    $_POST['sens_parm1'] = (double)$_POST['sens_parm1'];
    $old_parm =  $sensor_parm1[$_POST['sens_name']];
    if ( $old_parm != $_POST['sens_parm1'])
    {
	$query = "UPDATE `sc_sensors` SET `parm1` = \"".$_POST['sens_parm1']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." parameter 1 changed from ".$old_parm." to ".$_POST['sens_parm1']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);  
    }
}

if (isset($_POST['sens_parm2']))
{
    $_POST['sens_parm2'] = (double)$_POST['sens_parm2'];
    $old_parm =  $sensor_parm2[$_POST['sens_name']];
    if ($old_parm != $_POST['sens_parm2'])
    {
	$query = "UPDATE `sc_sensors` SET `parm2` = \"".$_POST['sens_parm2']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." parameter 2 changed from ".$old_parm." to ".$_POST['sens_parm2']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);  
    }
}

if (isset($_POST['sens_parm3']))
{
    $_POST['sens_parm3'] = (double)$_POST['sens_parm3'];
    $old_parm =  $sensor_parm3[$_POST['sens_name']];
    if ($old_parm != $_POST['sens_parm3'])
    {
	$query = "UPDATE `sc_sensors` SET `parm3` = \"".$_POST['sens_parm3']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." parameter 3 changed from ".$old_parm." to ".$_POST['sens_parm3']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);  
    }
}


if (isset($_POST['sens_parm4']))
{
    $_POST['sens_parm4'] = (double)$_POST['sens_parm4'];
    $old_parm =  $sensor_parm4[$_POST['sens_name']];
    if ($old_parm != $_POST['sens_parm4'])
    {
	$query = "UPDATE `sc_sensors` SET `parm4` = \"".$_POST['sens_parm4']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "".$_POST['sens_name']." parameter 4 changed from ".$old_parm." to ".$_POST['sens_parm4']." by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	$result = mysql_query($query);  
    }
}

if (isset($_POST['sens_note']))
{
    $_POST['sens_note'] = trim($_POST['sens_note']);
    if (!get_magic_quotes_gpc())
	$_POST['sens_note'] = addslashes($_POST['sens_note']);
    
    $query = "UPDATE `sc_sensors` SET `notes` = \"".$_POST['sens_note']."\" WHERE `name` = \"".$_POST['sens_name']."\"";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());    
}

if (isset($_POST['new_sens_name']))
{
    $_POST['new_sens_name'] = trim($_POST['new_sens_name']);
    if (!get_magic_quotes_gpc())
	$_POST['new_sens_name'] = addslashes($_POST['new_sens_name']);

    $s_length = strlen($_POST['new_sens_name']);
    if ($s_length > 16)
	die ("Sensor names must have fewer than 17 characters! <br>");
    
    $s_array = str_split($_POST['new_sens_name']);
    for ($i = 0; $i < $s_length; $i++)
    {
	$a_v = ord($s_array[$i]);
	
	if (!((($a_v > 47) && ($a_v < 58)) || (($a_v > 64) && ($a_v < 91)) || (($a_v > 96) && ($a_v < 123)) || ($a_v == 95)))
	    die ("Sensor names must only contain letters, numbers or underscores! <br>");	    
    }
    
    $query = "INSERT into `sc_sensors` (`name`)  VALUES ('".$_POST['new_sens_name']."')"; 
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
     
    $query = "CREATE  TABLE  `sc_sens_".$_POST['new_sens_name']."` (  `time` int( 11  )  NOT  NULL , `value` double NOT  NULL , `rate` double NOT  NULL , KEY  `time` (  `time`  )  ) ENGINE  =  MyISAM  DEFAULT CHARSET  = latin1 COLLATE  = latin1_general_cs;";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not make table <br />" . mysql_error());

    $mesg = "New sensor: ".$_POST['new_sens_name']." added by ".$_SESSION['user_name'].".";
    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
    $result = mysql_query($query);  
}



/////////////   get all the sensor table entries here:
include("aux/get_sensor_info.php"); 

///  The idea here is to create a list of unique sensor types from all the available sensors.
///  This allows us to just view one type at a time on the screen.
include("aux/choose_types.php");    // sets session variable $choose_type from the check boxes.

///  Read the last values from the database and save them to an array $sensor_values
///  indexed by $sensor_name
$temp_num = $_SESSION['num_val_avgs'];
$_SESSION['num_val_avgs'] = 1;
include("aux/get_sensor_vals.php");
$_SESSION['num_val_avgs'] = $temp_num;

////   This next section generate the HTML with the plot names in a table.
$cur_time = time();

echo ('<TABLE border="1" cellpadding="2" width=100%>');
$i=1;
echo ('<TR>');

$temp_types = $sensor_types;
$my_sensor_names = array();
foreach ($_SESSION['choose_type'] as $c_index)
{
    foreach ($sensor_names as $sensor_name)
	if (in_array($c_index, explode(",", $temp_types[$sensor_name])))
	{
	    $my_sensor_names[] = $sensor_name;
	    $temp_types[$sensor_name] = "";
	}
}
foreach ($sensor_names as $sensor_name)
{
    if (!(any_in_array(explode(",", $sensor_types[$sensor_name]), $sens_type_array_index)))
	$my_sensor_names[] = $sensor_name;
}

foreach ($my_sensor_names as $sensor_name)
{
    echo ('<TD>');
    echo ('<TABLE border="0" cellpadding="0" width=100%>');

    echo ('<TR>');                 /////  row 1
    echo ('<TH align="left" colspan = 1>');
    if ($sensor_al_trip[$sensor_name])
	echo ('<font color="red">');
    echo ($sensor_name .':  ');
    echo ('</TH>');   
	    
    echo ('<TD align="right" colspan = 1>');
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<input type="hidden" name="sens_hide" value="'.$sensor_name.'">');
    if ($sensor_hide[$sensor_name])
	echo ('Hide <input type="image" src="pixmaps/checked.png" alt="Unhide" title="Unhide">');
    else
	echo ('Hide <input type="image" src="pixmaps/unchecked.png" alt="Hide" title="Hide">');
    echo ('</FORM>');
    echo ('</TD>');

    echo ('<TD align="right" colspan = 1>');
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<input type="hidden" name="sens_show_rate" value="'.$sensor_name.'">');
    if ($sensor_show_rate[$sensor_name])
	echo ('Show Rate <input type="image" src="pixmaps/checked.png" alt="Do Not Show Rate" title="No Not Show Rate">');
    else
	echo ('Show Rate <input type="image" src="pixmaps/unchecked.png" alt="Show Rate" title="Show Rate">');
    echo ('</FORM>');
    echo ('</TD>');
	    
    echo ('<TD align="right" colspan = 1>');
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<input type="hidden" name="sens_settable" value="'.$sensor_name.'">');
    if ($sensor_settable[$sensor_name])
	echo ('Settable: <input type="image" src="pixmaps/checked.png" alt="Make not settable" title="Make not settable">');
    else
	echo ('Settable: <input type="image" src="pixmaps/unchecked.png" alt="Make settable" title="Make settable">');
    echo ('</FORM>');    
    echo ('</TD>');
    echo ('</TR>');
    
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<TR>');                 /////  row 2
    echo ('<TD align="left" colspan = 4>');
    echo ('Sensor description: <input type="text" name="sens_desc" value="'.$sensor_descs[$sensor_name].'" size = 48>');
    echo ('</TD>');
    echo ('</TR>');    
	    
    echo ('<TR>');                 /////  row 3
    echo ('<TD align="left" colspan = 2>');
    //echo ('Type: <input type="text" name="sens_type" value="'.$sensor_types[$sensor_name].'" size = 16>');
    echo ('Type: ');
    echo ('<SELECT name="sens_type[]" multiple=true size=2>');
    foreach ($sens_type_array_index as $index)
    {
	echo('<option ');
	if (in_array($index, explode(",", $sensor_types[$sensor_name])))
	    echo ('selected="selected"');
	echo(' value="'.$index.'">  '.$sens_type_array[$index].'  </option>');
    }
    echo ('</SELECT>');
    echo ('</TD>');
	    
    echo ('<TD align="right" colspan = 2>');
    echo ('Subtype: <input type="text" name="sens_subtype" value="'.$sensor_subtypes[$sensor_name].'" size = 16>');
    echo ('</TD>');
    echo ('</TR>');  
	    
    echo ('<TR>');                 /////  row 4
    echo ('<TD align="left" colspan = 2>');
    echo ('Number: <input type="text" name="sens_num" value="'.$sensor_numbers[$sensor_name].'" size = 6>');
    echo ('</TD>');
    echo ('<TD align="right" colspan = 2>');
    echo ('Instrument Name: <input type="text" name="sens_inst" value="'.$sensor_instruments[$sensor_name].'" size = 16>');
    echo ('</TD>');
    echo ('</TR>');  
	    
    echo ('<TR>');                 /////  row 5
    echo ('<TD align="left" colspan=2>');
    echo ('Units: <input type="text" name="sens_units" value="'.$sensor_units[$sensor_name].'" size = 16>');
    echo ('</TD>');
    echo ('<TD align="right" colspan=2>');
    echo ('Discrete Units: <input type="text" name="sens_discrete_vals" value="'.$sensor_discrete_vals[$sensor_name].'" size = 16>');
    echo ('</TD>');
    echo ('</TR>');  
	    
    echo ('<TR>');                 /////  row 6
    echo ('<TD align="left" colspan=2>');
    echo ('Update period: <input type="text" name="sens_period" value="'.$sensor_update_period[$sensor_name].'" size = 6> (seconds) ');
    echo ('</TD>');
    echo ('<TD align="right" colspan = 2>');
    echo ('Alarm grace period: <input type="text" name="sens_grace" value="'.$sensor_grace[$sensor_name].'" size = 6> (seconds)');
    echo ('</TD>');
    echo ('</TR>');  
	    
    echo ('<TR>');                 /////  row 7
    echo ('<TD align="left" colspan=4>');
    echo ('Text number format: <input type="text" name="sens_format" value="'.$sensor_num_format[$sensor_name].'" size = 6> (C style)');
    echo ('</TD>');
    echo ('</TR>');  

    echo ('<TR>');                 /////  row 8
    echo ('<TD align="left" colspan = 2>');
    echo ('String field 1: <input type="text" name="sens_user1" value="'.$sensor_user1[$sensor_name].'" size = 16> (txt)');
    echo ('</TD>');   
    echo ('<TD align="right"  colspan = 2>');
    echo ('String field 2: <input type="text" name="sens_user2" value="'.$sensor_user2[$sensor_name].'" size = 16> (txt)');
    echo ('</TD>');
    echo ('</TR>');    

    echo ('<TR>');                 /////  row 9
    echo ('<TD align="left"  colspan = 2>');
    echo ('String field 3: <input type="text" name="sens_user3" value="'.$sensor_user3[$sensor_name].'" size = 16> (txt)');
    echo ('</TD>');   
    echo ('<TD align="right" colspan = 2>');
    echo ('String field 4: <input type="text" name="sens_user4" value="'.$sensor_user4[$sensor_name].'" size = 16> (txt)');
    echo ('</TD>');
    echo ('</TR>');    
	    
    echo ('<TR>');                 /////  row 10
    echo ('<TD align="left" colspan = 2>');
    echo ('Parameter 1: <input type="text" name="sens_parm1" value="'.$sensor_parm1[$sensor_name].'" size = 8> (dbl)');
    echo ('</TD>');   
    echo ('<TD align="right"  colspan = 2>');
    echo ('Parameter 2: <input type="text" name="sens_parm2" value="'.$sensor_parm2[$sensor_name].'" size = 8> (dbl)');
    echo ('</TD>');
    echo ('</TR>');    
	    
    echo ('<TR>');                 /////  row 11
    echo ('<TD align="left"  colspan = 2>');
    echo ('Parameter 3: <input type="text" name="sens_parm3" value="'.$sensor_parm3[$sensor_name].'" size = 8> (dbl)');
    echo ('</TD>');   
    echo ('<TD align="right" colspan = 2>');
    echo ('Parameter 4: <input type="text" name="sens_parm4" value="'.$sensor_parm4[$sensor_name].'" size = 8> (dbl)');
    echo ('</TD>');
    echo ('</TR>');    
	    
    echo ('<TR>');                   
    echo ('<TD align="center" colspan=4>');
    echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
    echo ('<input type="submit" name="void" value="submit">');
    echo ('</TD>');
    echo ('</TR>');
    echo ('</FORM>');
	    
	    
    echo ('<TR>');                   
    echo ('<TD align="center" colspan=4>');
    echo ('-----');		      
    echo ('</TD>');
    echo ('</TR>');   
	    
    echo ('<TR>');                /////  row 12       
    echo ('<TD align="left" colspan=3>');
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('Notes:');
    echo ('<BR>');
    echo ('<TEXTAREA name="sens_note" value="'.$sensor_notes[$sensor_name].'" rows="4" cols="60">');
    if ($sensor_notes[$sensor_name] != "NULL")
	echo ($sensor_notes[$sensor_name]);
    echo ('</TEXTAREA>');
    echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
    echo ('<BR>');
    echo ('<input type="submit" name="Edit" value="Edit Note">');
    echo ('</FORM>');
    echo ('</TD>');
	    
    if ($sensor_settable[$sensor_name])
    {
	echo ('<TD align="right" colspan = 1>');
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('Control privileges: ');
	echo ('<BR>');
	echo ('<SELECT name="sens_privileges[]" multiple=true size=4>');
	foreach ($privilege_array as $pa)
	{
	    echo('<option ');
	    if (strpos($sensor_ctrl_priv[$sensor_name], $pa) !== false)
		echo ('selected="selected"');
	    echo(' value="'.$pa.'">  '.$pa.'  </option>');
	}
	echo ('</SELECT>');
	echo ('<BR>');
	echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
	echo ('<input type="submit" name="void" value="submit">');
	echo ('</FORM>');
	echo ('</TD>');
    }
    echo ('</TR>');   
	    
    echo ('<TR>');  
    echo ('<TD align="right" colspan=4>'); 
    echo ('<FORM action="slow_control_del_sensor.php" method="post">');
    echo ('<input type="hidden" name="del_sensor" value='.$sensor_name.'>');
    echo ('<input type="image" src="pixmaps/drop.png">  Delete this sensor ');
    echo ('</FORM>');
    echo ('</TD>');
    echo ('</TR>');

    echo ('<TR>');                    ///////////////////  footer
    echo ('<TD align="center" colspan=4>');
    echo ('-----');
    echo ('<BR>');
    echo ('Current value:'.format_num($sensor_values[$sensor_name]));
    if  (strncmp($sensor_units[$sensor_name], "discrete", 8) == 0)  
	echo (' (= '.get_disc_units_val($sensor_values[$sensor_name], $sensor_discrete_vals[$sensor_name]).')');
    else
	echo ('('.$sensor_units[$sensor_name].')');
    echo ('<BR>');
    echo ('Last update: '.($cur_time-$sensor_times[$sensor_name]).' seconds ago. ('. date("G:i:s  M d, y", $sensor_times[$sensor_name]) . ')');
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
echo ('</TR>');
echo ('</TABLE>');

echo ('<BR>');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('New Sensor: <input type="text" name="new_sens_name" size = 32>');
echo ('<input type="submit" name="New" value="New">  (Less than or equal to 16 characters, no spaces)');
echo ('</FORM>');

mysql_close($connection);

echo ('</body>');
echo ('</HTML>');
?>
