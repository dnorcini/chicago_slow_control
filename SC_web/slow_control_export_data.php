<?php
// slow_control_export_data.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
header("Content-Type: application/octet-stream");
header('Content-Disposition: filename="data.txt"');
require_once("db_login.php");
require_once("aux/general_fns.php");
include("aux/get_sensor_info.php");

if (!empty($_POST['array_data']))
{
    $query = "SELECT time, value, rate, x0, x1, dxN, y0, y FROM `sc_sens_".$_POST['export']."` WHERE `time` = \"".$_POST['data_time']."\" LIMIT 1";
    $result = mysql_query($query);
    if (!$result)
    {	
	die ("Could not query the database <br />" . mysql_error());
    }
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
	$time = (int)$row['time'];
	$value = (double)$row['value'];
	$rate = (double)$row['rate'];
	$x0 = (double)$row['x0'];
	$x1 = (double)$row['x1'];
	$dxN = (int)$row['dxN'];
	$y0 =(double)$row['y0'];
	$y_string = $row['y'];
    }
    
    $y = explode(",", $y_string);
    $y = scale_array($y, $y0);
    $x = make_new_data_array_N($x0, $x1, count($y));
    
    echo("# ".$_POST['export']." : ".$sensor_descs[$_POST['export']]."\n");
    echo("# Mass (AMU) , Partial Pressure (pTorr) \n"); 

    
    $n = count($x);
    if ($n > 0)
      {
	for ($i = 0; $i < $n; $i++)
	  {
	    echo( $x[$i]." , ".$y[$i]." , ".$dy[$i]." \n" );
	  }
      }

}
else
{
    $query = "SELECT time, value, rate FROM `sc_sens_".$_POST['export']."` WHERE `time` BETWEEN ".$_SESSION['t_min_p'].
	" AND ".$_SESSION['t_max_p']." ";
    
    
    $result = mysql_query($query);
    if (!$result)
    {	
	die ("Could not query the database <br>" . mysql_error());
    }
    
    echo("# ".$_POST['export']." : ".$sensor_descs[$_POST['export']]."\n");
    echo("# Time (s) , ".$sensor_types[$_POST['export']]." (".$sensor_units[$_POST['export']].") , Rate (".$sensor_units[$_POST['export']]."/s) \n");

    //$x = array();
    //$y = array();
    //$dy = array();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {	
      	$x = (int)$row['time'];
	$y = (double)$row['value'];
	$dy = (double)$row['rate'];

	echo( $x." , ".$y." , ".$dy." \n" );
    }

    /*
    echo("# ".$_POST['export']." : ".$sensor_descs[$_POST['export']]."\n");
    echo("# Time (s) , ".$sensor_types[$_POST['export']]." (".$sensor_units[$_POST['export']].") , Rate (".$sensor_units[$_POST['export']]."/s) \n");

    $n = count($x);
    if ($n > 0)
      {
	for ($i = 0; $i < $n; $i++)
	  {
	    echo( $x[$i]." , ".$y[$i]." , ".$dy[$i]." \n" );
	  }
      }
    */

 }

?>
