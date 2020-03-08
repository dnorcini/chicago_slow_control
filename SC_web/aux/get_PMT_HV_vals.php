<?php
// get_PMT_HV_vals.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2010
// james.nikkel@yale.edu
//
$sHV_names  = array();
$sHV_times  = array();
$sHV_values = array();
$sHV_V      = array();
$sHV_C      = array();
$sHV_S      = array();

for ($i = 0; $i < 16; $i++)
  {
    $sensor_name = sprintf("%s%02d", $Name_stub, $i);
    $query = "SELECT time, value, Voltage, Current, Status FROM sc_sens_".
      $sensor_name." ORDER BY `time` DESC LIMIT 1";
    
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
      {	
	$sHV_names[]  = $sensor_name;
	$sHV_times[]  = (int)$row['time'];
	$sHV_values[] = (double)$row['value'];
	$sHV_V[]      = $row['Voltage'];
	$sHV_C[]      = $row['Current'];
	$sHV_S[]      = $row['Status'];	
      }
  }
?>
