<?php
// get_PMT_HV_set_vals.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2010
// james.nikkel@yale.edu
//

$sHVs_names  = array();
$sHVs_times  = array();
$sHVs_values = array();
$sHVs_E      = array();
$sHVs_DV     = array();
$sHVs_trp    = array();
$sHVs_rmp    = array();

for ($i = 0; $i < 16; $i++)
  {
    $sensor_name = sprintf("%s%02d_ctrl", $Name_stub, $i);
    $query = "SELECT time, value, Enable, Demand_V, Trip_Current, Ramp_Rate FROM sc_sens_".
      $sensor_name." ORDER BY `time` DESC LIMIT 1";
    
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br />" . mysql_error());
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
      {	
	$sHVs_names[]  = $sensor_name;
	$sHVs_times[]  = (int)$row['time'];
	$sHVs_values[] = (double)$row['value'];
	$sHVs_E[]      = $row['Enable'];
	$sHVs_DV[]     = $row['Demand_V'];
	$sHVs_trp[]    = (double)$row['Trip_Current'];
	$sHVs_rmp[]    = (double)$row['Ramp_Rate'];	
      }
  }
?>
