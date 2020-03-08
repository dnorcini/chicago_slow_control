<?php
// slow_control_calc.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
header("Content-Type: application/octet-stream");
header('Content-Disposition: filename="data.txt"');
include("db_login.php");
require_once("aux/general_fns.php");

include("aux/get_sensor_info.php"); 

$target_time = strtotime($_POST['export_time']);

echo(date("G:i:s  M d, y", $target_time));
echo (" \n");
echo ("name :: closest val :: 6pt avg val +- 6pt deviation :: units \n");

foreach ($sensor_names as $sensor_name)
{
    $query = "SELECT time, value FROM sc_sens_".$sensor_name." WHERE `time` <= ".$target_time." ORDER BY `time` DESC LIMIT 1";
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br>" . mysql_error());
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {	
	$t1 = (double)$row['time'];
	$v1 = (double)$row['value'];
    }
    
    $query = "SELECT * FROM sc_sens_".$sensor_name." WHERE `time` > ".$target_time." ORDER BY `time` ASC LIMIT 1";  
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br>" . mysql_error());
    
    while ($row = mysql_fetch_array($result, MYSQL_NUM))
    {	
	$t2 = (double)$row[0];
	$v2 = (double)$row[1];
    }
    
    if (abs($t1-$target_time) < abs($t2-$target_time))
	$vt = $v1;
    else
	$vt = $v2;
    
    $value = array();
    
    $query = "SELECT * FROM sc_sens_".$sensor_name." WHERE `time` <= ".$target_time." ORDER BY `time` DESC LIMIT 3";
    
    $result = mysql_query($query);
    if (!$result)	
	die ("Could not query the database <br>" . mysql_error());
    
    
    while ($row = mysql_fetch_array($result, MYSQL_NUM))
	$value[] = (double)$row[1];
    
    
    $query = "SELECT * FROM sc_sens_".$sensor_name." WHERE `time` > ".$target_time." ORDER BY `time` ASC LIMIT 3";
    
    $result = mysql_query($query);
    if (!$result)
	die ("Could not query the database <br>" . mysql_error());
    
    while ($row = mysql_fetch_array($result, MYSQL_NUM))
	$value[] = (double)$row[1];
    
    $n = count($value);
    if ($n > 0)
    {		
	$avg_val = array_sum($value)/count($value); 	
	$dev_val = 0.0;
	
	for ($i = 0; $i < $n; $i++)
	{
	    $dev_val += ($value[$i] - $avg_val) * ($value[$i] - $avg_val); 
	}
	$dev_val = sqrt($dev_val);
	
	echo ($sensor_name);
	echo (" :: ");
	echo (format_num2($vt, "%f"));
	echo (" :: ");
	echo (format_num2($avg_val, "%f"));
	echo (" +- ");
	echo (format_num2($dev_val, "%f")); 
	echo (" :: ");
	echo ($sensor_units[$sensor_name]);
	echo (" \n");
    }
}
mysql_close($connection);
?>
