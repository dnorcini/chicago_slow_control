<?php
// new_HV_PMT_set_val.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2010
// james.nikkel@yale.edu
//

if (isset($_POST['start_stop_inst']))  
{
    if (check_access($_SESSION['privileges'], $sensor_ctrl_priv[$Master_sens], $allowed_host_array))
    { 
	mysql_close($connection);
	include("master_db_login.php");
	if (($inst_run[$my_inst_name] == 0) && ($_POST['start_stop_inst'] == 1))
	{
	    $query = "UPDATE `sc_insts` SET `run` = 1 WHERE `name` = \"".$my_inst_name."\"";
	    $result = mysql_query($query);
	    
	    $mesg = "".$my_inst_name." started by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	    $result += mysql_query($query);
	}
	else if (($inst_run[$my_inst_name] == 1) && ($_POST['start_stop_inst'] == 0))
	{
	    $query = "UPDATE `sc_insts` SET `run` = 0 WHERE `name` = \"".$my_inst_name."\"";
	    $result = mysql_query($query);
	    
	    $mesg = "".$my_inst_name." stopped by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".time()."', '".$mesg."', 'Config.', '0')"; 
	    $result += mysql_query($query);
	}
	if (!$result)
	    die ("Could not query the database <br />" . mysql_error());
    }
    include("aux/get_inst_info.php"); 
}


if (isset($_POST['set_HV_name']))
  {
    if (check_access($_SESSION['privileges'], $sensor_ctrl_priv[$_POST['set_HV_name']], $allowed_host_array))
      { 
	mysql_close($connection);
	include("master_db_login.php");
	if (isset($_POST['master_onoff']))               ///  Master On/Off Control
	  {
	    $new_val = floatval($_POST['master_onoff']);
	    if (is_float($new_val))
	      {
		$query = "INSERT INTO sc_sens_".$_POST['set_HV_name']." SET time = ".
		  time().", value = ".$new_val;
		$result = mysql_query($query);
		if (!$result)
		  die ("Could not query the database <br />" . mysql_error());
		
		$mesg = "New setpoint of ".$_POST['set_HV_name']." = ".
		  $new_val." requested by ".$_SESSION['user_name'].".";
		$query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".
		  time()."', '".$mesg."', 'Setpoint', '0')"; 
		$result = mysql_query($query);	
	      }
	  }
	else                ///  Per Slot Device Control
	  {
	    if (!(isset($_POST[set_HV_slot])) || !(isset($_POST[set_HV_ch])))
	      die ("There is a problem settig the HV.");
	    $slot_num = floatval($_POST[set_HV_slot]);
	    $chan_num = floatval($_POST[set_HV_ch]);
	    
	    if (isset($_POST['Card_onoff']))
	      {
		$new_val = intval($_POST['Card_onoff']);
		if (is_int($new_val))
		    $sHVs_values[$slot_num] = $new_val;
	      }
	    if (isset($_POST['Ch_onoff']))
	      {
		$new_val = intval($_POST['Ch_onoff']);
		if (is_int($new_val))
		  {
		    $E_array = explode(",",$sHVs_E[$slot_num]);
		    $E_array[$chan_num] = $new_val;
		    $sHVs_E[$slot_num] = implode(",", $E_array);
		  }
	      }
	    if (isset($_POST['Demand_V']))
	      {
		$new_val = floatval($_POST['Demand_V']);
		if (is_float($new_val))
		  {
		    $DV_array = explode(",",$sHVs_DV[$slot_num]);
		    $DV_array[$chan_num] = $new_val;
		    $sHVs_DV[$slot_num] = implode(",", $DV_array);
		  }	
	      }
	    if (isset($_POST['Trp_Cur']))
	      {
		$new_val = floatval($_POST['Trp_Cur']);
		if (is_float($new_val))
		    $sHVs_trp[$slot_num] = $new_val;
	      }
	    if (isset($_POST['Rmp_Rt']))
	      {
		$new_val = floatval($_POST['Rmp_Rt']);
		if (is_float($new_val))
		    $sHVs_rmp[$slot_num] = $new_val;
	      }
	    $query = "INSERT INTO `sc_sens_".$_POST['set_HV_name'].
	      "` (`time`, `value`, `Enable`, `Demand_V`, `Trip_Current`, `Ramp_Rate`) ".
	      " VALUES ('".time()."' , '".$sHVs_values[$slot_num].
	      "' , '".$sHVs_E[$slot_num]."' , '".$sHVs_DV[$slot_num]."' , '".$sHVs_trp[$slot_num]."' , '".$sHVs_rmp[$slot_num]."')";
	    $result = mysql_query($query);
	    if (!$result)
	      die ("Could not query the database <br />" . mysql_error());
	    
	    $mesg = "New setting of ".$_POST['set_HV_name']." requested by ".$_SESSION['user_name'].".";
	    $query = "INSERT into `msg_log` (`time`, `msgs`, `type`, `is_error`) VALUES ('".
	      time()."', '".$mesg."', 'Setpoint', '0')"; 
	    $result = mysql_query($query);
	  }
	
	include("aux/get_sensor_vals.php");
	include("aux/get_PMT_HV_vals.php");
	include("aux/get_PMT_HV_set_vals.php");
      }
    else 
      echo('You do not have access to that control.<br>');
  }
?>
      
