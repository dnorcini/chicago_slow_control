<?php
  // slow_control_text.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2006, 2011
  // james.nikkel@gmail.com
  //
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php"); 



if (isset($_POST['toggle_low_alarm']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    
    if ($sensor_al_arm_val_low[$_POST['toggle_low_alarm']])
      $query = "UPDATE `sc_sensors` SET `al_arm_val_low` = 0 WHERE `name` = \"".$_POST['toggle_low_alarm']."\"";
    else
      $query = "UPDATE `sc_sensors` SET `al_arm_val_low` = 1 WHERE `name` = \"".$_POST['toggle_low_alarm']."\"";
    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }

if (isset($_POST['toggle_high_alarm']))
  { 
    mysql_close($connection);
    include("master_db_login.php");
    if ($sensor_al_arm_val_high[$_POST['toggle_high_alarm']])
      $query = "UPDATE `sc_sensors` SET `al_arm_val_high` = 0 WHERE `name` = \"".$_POST['toggle_high_alarm']."\"";
    else
      $query = "UPDATE `sc_sensors` SET `al_arm_val_high` = 1 WHERE `name` = \"".$_POST['toggle_high_alarm']."\"";
    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }

if (isset($_POST['toggle_low_rate_alarm']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    if ($sensor_al_arm_rate_low[$_POST['toggle_low_rate_alarm']])
      $query = "UPDATE `sc_sensors` SET `al_arm_rate_low` = 0 WHERE `name` = \"".$_POST['toggle_low_rate_alarm']."\"";
    else
      $query = "UPDATE `sc_sensors` SET `al_arm_rate_low` = 1 WHERE `name` = \"".$_POST['toggle_low_rate_alarm']."\"";
    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }

if (isset($_POST['toggle_high_rate_alarm']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    if ($sensor_al_arm_rate_high[$_POST['toggle_high_rate_alarm']])
      $query = "UPDATE `sc_sensors` SET `al_arm_rate_high` = 0 WHERE `name` = \"".$_POST['toggle_high_rate_alarm']."\"";
    else
      $query = "UPDATE `sc_sensors` SET `al_arm_rate_high` = 1 WHERE `name` = \"".$_POST['toggle_high_rate_alarm']."\"";
    $result = mysql_query($query);
    if (!$result)
      die ("Could not query the database <br />" . mysql_error());
  }

if (isset($_POST['al_set_val_low']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    $new_val = floatval($_POST['al_set_val_low']);
    
    if (is_float($new_val))
      {
	$query = "UPDATE `sc_sensors` SET `al_set_val_low` = ".$new_val ." WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "New alarm limit for ".$_POST['sens_name']." set by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Alarm', '0')"; 
	$result = mysql_query($query);	
      }
  }

if (isset($_POST['al_set_val_high']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    $new_val = floatval($_POST['al_set_val_high']);
    
    if (is_float($new_val))
      {
	$query = "UPDATE `sc_sensors` SET `al_set_val_high` = ".$new_val ." WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());

	$mesg = "New alarm limit for ".$_POST['sens_name']." set by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Alarm', '0')"; 
	$result = mysql_query($query);	
      }
  }

if (isset($_POST['al_set_rate_low']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    $new_val = floatval($_POST['al_set_rate_low']);
    
    if (is_float($new_val))
      {
	$query = "UPDATE `sc_sensors` SET `al_set_rate_low` = ".$new_val ." WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "New alarm limit for ".$_POST['sens_name']." set by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Alarm', '0')"; 
	$result = mysql_query($query);	
      }
  }

if (isset($_POST['al_set_rate_high']))
  {
    mysql_close($connection);
    include("master_db_login.php");
    $new_val = floatval($_POST['al_set_rate_high']);
    
    if (is_float($new_val))
      {
	$query = "UPDATE `sc_sensors` SET `al_set_rate_high` = ".$new_val ." WHERE `name` = \"".$_POST['sens_name']."\"";
	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
	
	$mesg = "New alarm limit for ".$_POST['sens_name']." set by ".$_SESSION['user_name'].".";
	$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Alarm', '0')"; 
	$result = mysql_query($query);	
      }
  }

if (isset($_POST['sens_grace']))
  {
    mysql_close($connection);
    include("master_db_login.php");
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

///  Get all the sensor information from the database so that we can 
///  define what we want to plot.
include("aux/get_sensor_info.php"); 

/// If an alarm has gone off, make a button so that it can be turned off.
include("aux/alarm_button.php");

///  The idea here is to create a list of unique sensor types from all the available sensors.
///  This allows us to just view one type at a time on the screen.
include("aux/choose_types.php");    // sets session variable $choose_type from the check boxes.

///  Read the last values from the database and save them to an array $sensor_values
///  indexed by $sensor_name
$temp_num = $_SESSION['num_val_avgs'];
$_SESSION['num_val_avgs'] = 1;
include("aux/get_sensor_vals.php");
$_SESSION['num_val_avgs'] = $temp_num;

mysql_close($connection);

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

//  Sort sensors by alarm status... is there a better way to do this?
$alarm_sensor_names = array();
foreach ($my_sensor_names as $sensor_name)
{
  if ($sensor_al_trip[$sensor_name])
    $alarm_sensor_names[] = $sensor_name;
}
foreach ($my_sensor_names as $sensor_name)
{
  if (!($sensor_al_trip[$sensor_name]) && 
      ($sensor_al_arm_val_low[$sensor_name] || $sensor_al_arm_val_high[$sensor_name] || 
       $sensor_al_arm_rate_low[$sensor_name] ||  $sensor_al_arm_rate_high[$sensor_name] ))
    $alarm_sensor_names[] = $sensor_name;
}
foreach ($my_sensor_names as $sensor_name)
{
  if (!($sensor_al_trip[$sensor_name]) && 
      !($sensor_al_arm_val_low[$sensor_name] || $sensor_al_arm_val_high[$sensor_name] || 
	$sensor_al_arm_rate_low[$sensor_name] ||  $sensor_al_arm_rate_high[$sensor_name] ))
    $alarm_sensor_names[] = $sensor_name;
}


foreach ($alarm_sensor_names as $sensor_name)
{
  if ($sensor_settable[$sensor_name] == 0)
    {     // don't show settable or hidden sensors in this table
      echo ('<TD>');

      echo ('<TABLE border="0" cellpadding="0" width=100%>');
      echo ('<TR>');

      echo ('<TH align="center" colspan="4">');
      if ($sensor_al_trip[$sensor_name])
	echo ('<font color="red">');
      echo ($sensor_descs[$sensor_name]);
      if ($_SESSION['show_sens_name'])
	echo (" (".$sensor_name.")");
      echo ('</TH>');    

      echo ('</TR>');    
	    
      echo ('<TR>');

      echo ('<TD align="center" colspan="4">');
      echo ('Current value:'.format_num($sensor_values[$sensor_name]));
      if  (strncmp($sensor_units[$sensor_name], "discrete", 8) == 0)  
	echo (' (= '.get_disc_units_val($sensor_values[$sensor_name], $sensor_discrete_vals[$sensor_name]).')');
      else
	echo ('('.$sensor_units[$sensor_name].')');
      echo ('</TD>');

      echo ('</TR>');

      echo ('<TR>');
	    
      echo ('<TD align="center" colspan="4">');
      echo ('last update: '.($cur_time-$sensor_times[$sensor_name]).' seconds ago ('. date("G:i:s  M d, y", $sensor_times[$sensor_name]) . ')');
      echo ('</TD>');
	    
      echo ('</TR>');

      echo ('<TR>');
	    
      echo ('<TD align="center" colspan="4">');
      echo ('-----');
      echo ('</TD>');
	    
      echo ('</TR>');

      echo ('<TR>');
	    
      echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
      echo ('<input type="hidden" name="toggle_low_alarm" value="'.$sensor_name.'">');
      echo ('<TD align="left" width="17">');
      if ($sensor_al_arm_val_low[$sensor_name])
	echo ('<input type="image" src="pixmaps/green_circ.png" alt="Armed" title="Armed">');
      else
	echo ('<input type="image" src="pixmaps/red_circ.png" alt="Not armed" title="Not armed">');
      echo ('</TD>');
      echo ('</FORM>');

      echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
      echo ('<TD align="left">');
      echo ('Low alarm: <input type="text" name="al_set_val_low" value="'.format_num($sensor_al_set_val_low[$sensor_name]).'" size = 7>');
      echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
      echo ('</TD>');
      echo ('</FORM>');
	    
      echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
      echo ('<input type="hidden" name="toggle_high_alarm" value="'.$sensor_name.'">');
      echo ('<TD align="left" width="17">');
      if ($sensor_al_arm_val_high[$sensor_name])
	echo ('<input type="image" src="pixmaps/green_circ.png" alt="Armed" title="Armed">');
      else
	echo ('<input type="image" src="pixmaps/red_circ.png" alt="Not armed" title="Not armed">');
      echo ('</TD>');
      echo ('</FORM>');
	    
      echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
      echo ('<TD align="left">');
      echo ('High alarm:<input type="text" name="al_set_val_high" value="'.format_num($sensor_al_set_val_high[$sensor_name]).'" size = 7>');
      echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
      echo ('</TD>');
      echo ('</FORM>');
	    
      echo ('</TR>');
    
      echo ('<TR>');

      echo ('<TD align="left" width="17">');
      echo ('</TD>');

      echo ('<TD align="left">');
      echo ('('.$sensor_units[$sensor_name].')');
      echo ('</TD>');
	    
      echo ('<TD align="left" width="17">');
      echo ('</TD>');

      echo ('<TD align="left">');
      echo ('('.$sensor_units[$sensor_name].')');
      echo ('</TD>');

      echo ('</TR>');

      if ($sensor_show_rate[$sensor_name] == 1)
	{
	  echo ('<TR>');
	    
	  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	  echo ('<input type="hidden" name="toggle_low_rate_alarm" value="'.$sensor_name.'">');
	  echo ('<TD align="left" width="17">');
	  if ($sensor_al_arm_rate_low[$sensor_name])
	    echo ('<input type="image" src="pixmaps/green_circ.png" alt="Armed" title="Armed">');
	  else
	    echo ('<input type="image" src="pixmaps/red_circ.png" alt="Not armed" title="Not armed">');
	  echo ('</TD>');
	  echo ('</FORM>');

	  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	  echo ('<TD align="left">');
	  echo ('Low rate alarm: <input type="text" name="al_set_rate_low" value="'.format_num($sensor_al_set_rate_low[$sensor_name]).'" size =7>');
	  echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
	  echo ('</TD>');
	  echo ('</FORM>');
	    
	  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	  echo ('<input type="hidden" name="toggle_high_rate_alarm" value="'.$sensor_name.'">');
	  echo ('<TD align="left" width="17">');
	  if ($sensor_al_arm_rate_high[$sensor_name])
	    echo ('<input type="image" src="pixmaps/green_circ.png" alt="Armed" title="Armed">');
	  else
	    echo ('<input type="image" src="pixmaps/red_circ.png" alt="Not armed" title="Not armed">');
	  echo ('</TD>');
	  echo ('</FORM>');
	    
	  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	  echo ('<TD align="left">');
	  echo ('High rate alarm:<input type="text" name="al_set_rate_high" value="'.format_num($sensor_al_set_rate_high[$sensor_name]).'" size = 7>');
	  echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
	  echo ('</TD>');
	  echo ('</FORM>');

	  echo ('</TR>');    

	  echo ('<TD align="left" width="17">');
	  echo ('</TD>');

	  echo ('<TD align="left">');
	  echo ('('.$sensor_units[$sensor_name].'/s)');
	  echo ('</TD>');
	    
	  echo ('<TD align="left" width="17">');
	  echo ('</TD>');

	  echo ('<TD align="left">');
	  echo ('('.$sensor_units[$sensor_name].'/s)');
	  echo ('</TD>');
	  echo ('</TR>');
	}
      echo ('<TR>');
	    
      echo ('<TD align="center" colspan="4">');
      echo ('-----');
      echo ('</TD>');
	    
      echo ('</TR>');

      echo ('<TR>');
      echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
      echo ('<TD align="center" colspan="4">');
      echo ('Grace period: <input type="text" name="sens_grace" value="'.format_num($sensor_grace[$sensor_name]).'" size = 6> seconds');
      echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
      echo ('</TD>');
      echo ('</FORM>');
      echo ('</TR>');

      echo ('</TABLE>');

      echo ('</TD>');
      if ($i % 3 ==0)
	{
	  echo ('</TR>');
	  echo ('<TR>');
	}
      $i++;
    }
}
echo ('</TR>');
echo ('</TABLE>');

echo ('</body>');
echo ('</HTML>');
?>
