<?php
// get_sensor_info.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2007, 2008, 2010
// james.nikkel@yale.edu
//
if (isset($get_all_sensors))
{
    if ($get_all_sensors)
	$query = "SELECT * FROM `sc_sensors` ORDER BY `name`";
    else
	$query = "SELECT * FROM `sc_sensors` WHERE `hide_sensor` = 0 ORDER BY `name`";
}
else
    $query = "SELECT * FROM `sc_sensors` WHERE `hide_sensor` = 0 ORDER BY `description`";

$result = mysql_query($query);
if (!$result)
{	
    die ("Could not query the database <br>" . mysql_error());
}

$sensor_names = array();
$sensor_descs  = array();
$sensor_types = array();
$sensor_subtypes = array();
$sensor_ctrl_priv = array();
$sensor_numbers = array();
$sensor_instruments = array();
$sensor_units = array();
$sensor_discrete_vals = array();
$sensor_al_set_val_low = array();
$sensor_al_set_val_high = array();
$sensor_al_arm_val_low = array();
$sensor_al_arm_val_high = array();
$sensor_al_set_rate_low = array();
$sensor_al_set_rate_high = array();
$sensor_al_arm_rate_low = array();
$sensor_al_arm_rate_high = array();
$sensor_al_trip = array();
$sensor_grace = array();
$sensor_last_trip = array();
$sensor_settable = array();
$sensor_show_rate = array();
$sensor_hide = array();
$sensor_update_period = array();
$sensor_num_format = array();
$sensor_user1 = array();
$sensor_user2 = array();
$sensor_user3 = array();
$sensor_user4 = array();
$sensor_parm1 = array();
$sensor_parm2 = array();
$sensor_parm3 = array();
$sensor_parm4 = array();
$sensor_notes  = array();

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $sensor_names[] = $row['name'];
    if (isset($row['description']))
      $sensor_descs[] = $row['description'];
    else
      $sensor_descs[] = 'description';
  
    if (isset($row['type']))    
      $sensor_types[] = $row['type'];
    else
      $sensor_types[] ='type';
    
    if (isset($row['subtype']))
      $sensor_subtypes[] = $row['subtype'];
    else
      $sensor_subtypes[] = 'subtype';
      
    if (isset($row['ctrl_priv']))
      $sensor_ctrl_priv[] = $row['ctrl_priv'];
    else
      $sensor_ctrl_priv[] = 'ctrl_priv';
   
    if (isset($row['num']))
      $sensor_numbers[] = (int)$row['num'];
    else
      $sensor_numbers[] = 0;
    
    if (isset($row['instrument']))
      $sensor_instruments[] = $row['instrument'];
    else
      $sensor_instruments[] = 'instrument';
    
    if (isset($row['units']))
      $sensor_units[] = $row['units'];
    else
      $sensor_units[] = 'units';

     if (isset($row['discrete_vals']))
      $sensor_discrete_vals[] = $row['discrete_vals'];
    else
      $sensor_discrete_vals[] = 'discrete_vals';
    
    if (isset($row['al_set_val_low']))
      $sensor_al_set_val_low[] = (double)$row['al_set_val_low'];
    else
      $sensor_al_set_val_low[] = 0.0;
    
    if (isset($row['al_set_val_high']))
      $sensor_al_set_val_high[] = (double)$row['al_set_val_high'];
    else
      $sensor_al_set_val_high[] = 0.0;

    if (isset($row['al_arm_val_low']))
      $sensor_al_arm_val_low[] = (int)$row['al_arm_val_low'];
    else
      $sensor_al_arm_val_low[] = 0;

    if (isset($row['al_arm_val_high']))
      $sensor_al_arm_val_high[] = (int)$row['al_arm_val_high'];
    else
      $sensor_al_arm_val_high[] = 0;

    if (isset($row['al_set_rate_low']))
      $sensor_al_set_rate_low[] = (double)$row['al_set_rate_low'];
    else
      $sensor_al_set_rate_low[] = 0.0;
    
    if (isset($row['al_set_rate_high']))
      $sensor_al_set_rate_high[] = (double)$row['al_set_rate_high'];
    else
      $sensor_al_set_rate_high[] = 0.0;

    if (isset($row['al_arm_rate_low']))
      $sensor_al_arm_rate_low[] = (int)$row['al_arm_rate_low'];
    else
      $sensor_al_arm_rate_low[] = 0;

    if (isset($row['al_arm_rate_high']))
      $sensor_al_arm_rate_high[] = (int)$row['al_arm_rate_high'];
    else
      $sensor_al_arm_rate_high[] = 0;

    if (isset($row['alarm_tripped']))
      $sensor_al_trip[] = (int)$row['alarm_tripped'];
    else
      $sensor_al_trip[] =0;

    if (isset($row['grace']))
      $sensor_grace[] = (int)$row['grace'];
    else
      $sensor_grace[] = 0;

    if (isset($row['last_trip']))
      $sensor_last_trip[] = (int)$row['last_trip'];
    else
      $sensor_last_trip[] = -1;

    if (isset($row['settable']))
	$sensor_settable[] = (int)$row['settable'];
    else
	$sensor_settable[] = 0;
    
    if (isset($row['show_rate']))
	$sensor_show_rate[] = (int)$row['show_rate'];
    else
	$sensor_show_rate[] = 0;

    if (isset($row['hide_sensor']))
      $sensor_hide[] = (int)$row['hide_sensor'];
    else
      $sensor_hide[] = 0;

    if (isset($row['update_period']))
      $sensor_update_period[] = (int)$row['update_period'];
    else
      $sensor_update_period[] = 0;

    if (isset($row['num_format']))
      $sensor_num_format[] = $row['num_format'];
    else
      $sensor_num_format[] = 'null';

    if (isset($row['user1']))
      $sensor_user1[] = $row['user1'];
    else
      $sensor_user1[] = 'user1';
    
    if (isset($row['user2']))
      $sensor_user2[] = $row['user2'];
    else
      $sensor_user2[] = 'user2';
    
    if (isset($row['user3']))
      $sensor_user3[] = $row['user3'];
    else
      $sensor_user3[] = 'user3';

    if (isset($row['user4']))
      $sensor_user4[] = $row['user4'];
    else
       $sensor_user4[] = 'user4';
   
    if (isset($row['parm1']))
      $sensor_parm1[] = (double)$row['parm1'];
    else
      $sensor_parm1[] = 0.0;

    if (isset($row['parm2']))
      $sensor_parm2[] = (double)$row['parm2'];
    else
      $sensor_parm2[] = 0.0;
    
    if (isset($row['parm3']))
      $sensor_parm3[] = (double)$row['parm3'];
    else
      $sensor_parm3[] = 0.0;
    
    if (isset($row['parm4']))
      $sensor_parm4[] = (double)$row['parm4'];
    else
      $sensor_parm4[] = 0.0;
    
    if (isset($row['notes']))
      $sensor_notes[] = $row['notes'];
    else
      $sensor_notes[] = 'notes';
 }

$sensor_descs = array_combine($sensor_names, $sensor_descs);
$sensor_types = array_combine($sensor_names, $sensor_types);
$sensor_subtypes = array_combine($sensor_names, $sensor_subtypes);
$sensor_ctrl_priv = array_combine($sensor_names, $sensor_ctrl_priv);
$sensor_numbers = array_combine($sensor_names, $sensor_numbers);
$sensor_instruments = array_combine($sensor_names, $sensor_instruments);
$sensor_units = array_combine($sensor_names, $sensor_units);
$sensor_discrete_vals = array_combine($sensor_names, $sensor_discrete_vals);
$sensor_al_set_val_low = array_combine($sensor_names, $sensor_al_set_val_low);
$sensor_al_set_val_high = array_combine($sensor_names, $sensor_al_set_val_high);
$sensor_al_arm_val_low = array_combine($sensor_names, $sensor_al_arm_val_low);
$sensor_al_arm_val_high = array_combine($sensor_names, $sensor_al_arm_val_high);
$sensor_al_set_rate_low = array_combine($sensor_names, $sensor_al_set_rate_low);
$sensor_al_set_rate_high = array_combine($sensor_names, $sensor_al_set_rate_high);
$sensor_al_arm_rate_low = array_combine($sensor_names, $sensor_al_arm_rate_low);
$sensor_al_arm_rate_high = array_combine($sensor_names, $sensor_al_arm_rate_high);
$sensor_al_trip = array_combine($sensor_names, $sensor_al_trip);
$sensor_grace = array_combine($sensor_names, $sensor_grace);
$sensor_last_trip = array_combine($sensor_names, $sensor_last_trip);
$sensor_settable = array_combine($sensor_names, $sensor_settable);
$sensor_show_rate = array_combine($sensor_names, $sensor_show_rate);
$sensor_hide = array_combine($sensor_names, $sensor_hide);
$sensor_update_period = array_combine($sensor_names, $sensor_update_period);
$sensor_num_format = array_combine($sensor_names, $sensor_num_format);
$sensor_user1 = array_combine($sensor_names, $sensor_user1);
$sensor_user2 = array_combine($sensor_names, $sensor_user2);
$sensor_user3 = array_combine($sensor_names, $sensor_user3);
$sensor_user4 = array_combine($sensor_names, $sensor_user4);
$sensor_parm1 = array_combine($sensor_names, $sensor_parm1);
$sensor_parm2 = array_combine($sensor_names, $sensor_parm2);
$sensor_parm3 = array_combine($sensor_names, $sensor_parm3);
$sensor_parm4 = array_combine($sensor_names, $sensor_parm4);
$sensor_notes = array_combine($sensor_names, $sensor_notes);
?>
