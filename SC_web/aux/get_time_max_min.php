<?php
// get_time_max_min.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2010
// james.nikkel@yale.edu
//
// Loops through all the current sensors, and defines session variables,
// t_min and t_max to indicate the first and last time entries in the database.
// t_max will, of course, get latger as time goes on, but t_min should be constant.
//

$min_t = array();
$max_t = array();
foreach ($sensor_names as $sensor)
{
    if (!($sensor_settable[$sensor]))
    {
	$query = "SELECT MIN(`time`) AS `time` FROM sc_sens_".$sensor;
	$result = mysql_query($query);
	if (!$result)
	{	
	    die ("Could not query the database <br>" . mysql_error());
	}
	while ($res = mysql_fetch_row($result))
	{
	    if (!is_null($res[0]))
		$min_t[] = $res[0];
	}
	$query = "SELECT MAX(`time`) AS `time` FROM sc_sens_".$sensor;
	$result = mysql_query($query);
	if (!$result)
	{	
	    die ("Could not query the database <br>" . mysql_error());
	}
	while ($res = mysql_fetch_row($result))
	{
	    if (!is_null($res[0]))
		$max_t[] = $res[0];
	}
    }
}
$_SESSION['t_min'] = min($min_t);
$_SESSION['t_max'] = max($max_t);

?>
