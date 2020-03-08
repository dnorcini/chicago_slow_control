<?php
// get_sensor_vals.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006 - 2010
// james.nikkel@yale.edu
//
if (empty($_SESSION['num_val_avgs']))
{
    $_SESSION['num_val_avgs'] = 1;
 }


$sensor_values = array();
$sensor_rates  = array();
$sensor_times  = array();
foreach ($sensor_names as $sensor_name)
{
  if (isset($get_single_type))
    $choose_type = array(array_search($get_single_type, $sens_type_array));
  else
    $choose_type = $_SESSION['choose_type'];

  if (any_in_array( explode(",", $sensor_types[$sensor_name]), $choose_type))
    {
      if ($sensor_settable[$sensor_name] == 1)
	$query = "SELECT time, value, rate FROM sc_sens_".
	  $sensor_name." ORDER BY `time` DESC LIMIT 1";
      else
	$query = "SELECT time, value, rate FROM sc_sens_".
	  $sensor_name." ORDER BY `time` DESC LIMIT ".$_SESSION['num_val_avgs'];
	
      $result = mysql_query($query);
      if (!$result)
	die ("Could not query the database <br />" . mysql_error());
	
      $time  = array();
      $value = array();
      $rate  = array();
      while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
	{	
	  $time[]  = (int)$row['time'];
	  $value[] = (double)$row['value'];
	  $rate[]  = (double)$row['rate'];
	}
	
      if (count($time) >= 5)
	{
	  sort($value);
	  array_splice($value, 0, 2);
	  rsort($value);
	  array_splice($value, 0, 2);
	  $sensor_values[] = array_sum($value)/count($value); 
	  $sensor_rates[] = array_sum($rate)/count($rate);      
	  $sensor_times[] =  max($time);
	}
      else if (count($time) >= 3)
	{
	  sort($value);
	  array_splice($value, 0, 1);
	  rsort($value);
	  array_splice($value, 0, 1);
	  $sensor_values[] = array_sum($value)/count($value);    
	  $sensor_rates[] = array_sum($rate)/count($rate);    
	  $sensor_times[] = max($time);
	}
      else if (count($time) >= 1)
	{
	  $sensor_values[] = array_sum($value)/count($value);    
	  $sensor_rates[] = array_sum($rate)/count($rate);    
	  $sensor_times[] = max($time);
	}
      else 
	{
	  $sensor_values[] = 0;  
	  $sensor_rates[]  = 0;  
	  $sensor_times[]  = 0;  
	}
    }
  else
    {
      $sensor_values[] = 0;
      $sensor_rates[]  = 0;  
      $sensor_times[]  = 0;
    }    
}

$sensor_values = array_combine($sensor_names, $sensor_values);
$sensor_rates  = array_combine($sensor_names, $sensor_rates);
$sensor_times  = array_combine($sensor_names, $sensor_times);
?>
