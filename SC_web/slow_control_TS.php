<?php
// slow_control_text.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$senstype="TSctrl";
$auto_ref = 1;
$req_priv = "TS";
$my_inst_name = "TSctrl";
$_SESSION['auto_refresh_time'] = 10;
include("master_db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php"); 
include("aux/get_inst_info.php"); 

if (isset($_POST['start_stop_inst']))  
{
    if (check_access($_SESSION['privileges'], $req_priv, $allowed_host_array))
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

if (!empty($_POST['sens_name']))
{
    $new_val = floatval($_POST['new_set_val']);
    $changed_sensor = $_POST['sens_name'];
    if (is_float($new_val))
    {
	$query = "INSERT INTO ".$changed_sensor." SET time = ".time().", value = ".$new_val;
	$result = mysql_query($query);
	if (!$result)
	{	
	    die ("Could not query the database <br />" . mysql_error());
	}
    }
}


///  Read the last values from the database and save them to an array $sensor_values
///  indexed by $sensor_name
include("aux/get_sensor_vals.php");
mysql_close($connection);

////   This next section generate the HTML with the plot names in a table.
include("aux/inst_status.php");

echo ('<TABLE border="1" cellpadding="2" width=100%>');
$i=1;
echo ('<TR>');
foreach ($sensor_names as $sensor_name)
{
    if (strcmp($sensor_types[$sensor_name], $senstype) == 0)
    {
	if (!$sensor_settable[$sensor_name])	
	{                                                                      
	    echo ('<TH>');
	    if ($sensor_al_trip[$sensor_name])
		echo ('<font color="red">');
	    echo ($sensor_descs[$sensor_name]);
	    echo ('<br>');
	    echo (' <font size="+4"> ');
	    echo (format_num2($sensor_values[$sensor_name], $sensor_num_format[$sensor_name]));
	    echo (' </font> ');
	    echo ('<br>');
	    echo ('('.$sensor_units[$sensor_name].')');
	    echo ('</TH>');
	    if ($i % 3 ==0)
	    {
		echo ('</TR>');
		echo ('<TR>');
	    }
	    $i++;
	}
	else
	{
	    echo ('<TD>');
	    
	    echo ('<TABLE border="0" cellpadding="0" width=100%>');
	    echo ('<TR>');
	    echo ('<TH align="center" colspan="4">');
	    if ($sensor_al_trip[$sensor_name])
		echo ('<font color="red">');
	    echo ($sensor_descs[$sensor_name]);
	    echo ('</TH>');
	    echo ('</TR>');    
	    
	    echo ('<TR>');
	    
	    if ((strncmp($sensor_units[$sensor_name], "bool", 4) == 0) ||         // discrete toggle:
		(strncmp($sensor_units[$sensor_name], "binary", 6) == 0) ||
		(strncmp($sensor_units[$sensor_name], "trinary", 7) == 0) ||
		(strncmp($sensor_units[$sensor_name], "discrete", 5) == 0))
	    {
		$all_vals = explode(";",$sensor_discrete_vals[$sensor_name]);
		$av_v = explode(":", $all_vals[0]);
		foreach ($av_v as $temp_i => $temp) $av_v[$temp_i] = (double)$temp;  
		$av_s = explode(":", $all_vals[1]);
		$all_vals = array_combine($av_v, $av_s);
		
		$cur_val = $all_vals[(string)$sensor_values[$sensor_name]];
		
		echo ('<TD align="center" colspan="4">');
		echo ('Current value: '.$cur_val);
		echo ('</TD>');
		echo ('</TR>');
		
		echo ('<TR>');
		echo ('<TD align="center" colspan="4">');
		echo ('-----');
		echo ('</TD>');
		echo ('</TR>');
		
		echo ('<TR>');
		if (count($all_vals) < 5)                // make buttons if there are < 4 items
		{
		    foreach ($all_vals as $av_v => $av_s)
			if ($av_v != $sensor_values[$sensor_name])
			{
			    echo ('<TD  align="center">');
			    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
			    echo ('<input type="submit" name="dummy" value="'.$av_s.'" title="Set to '.$av_s.'">  ');
			    echo ('<input type="hidden" name="new_set_val" value="'.$av_v.'">');
			    echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
			    echo ('</FORM> ');
			    echo ('</TD>');
			}
		}
		else                                   // use pull down for more possibilities
		{
		    echo ('<TD align="center" colspan="4">');
		    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
		    echo ('<input type="submit" name="dummy" value="Change" title="Set to: ">  ');
		    echo ('<select name="new_set_val">');
		    foreach ($all_vals as $av_v => $av_s)
		    {
			if ($av_v != $sensor_values[$sensor_name])
			{
			    echo('<option ');
			    echo(' value="'.$av_v.'" >'.$av_s.'</option> ');
			}
		    }
		    echo ('</select>');
		    echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
		    echo ('</FORM> ');
		    echo ('</TD>');
		}
		echo ('</TR>');    
	    }
	    else                                         // normal numeric entry:
	    {
		echo ('<TD align="center" colspan="4">');
		echo ('Current value:'.format_num2($sensor_values[$sensor_name], $sensor_num_format[$sensor_name]));
		echo ('('.$sensor_units[$sensor_name].')');
		echo ('</TD>');
		echo ('</TR>');
		
		echo ('<TR>');
		echo ('<TD align="center" colspan="4">');
		echo ('-----');
		echo ('</TD>');
		echo ('</TR>');
		
		echo ('<TR>');
		echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
		echo ('<TD align="center">');
		echo ('New Value: <input type="text" name="new_set_val" value="'.format_num2($sensor_values[$sensor_name], $sensor_num_format[$sensor_name]).'" size = 9>');
		echo ('<input type="hidden" name="sens_name" value="'.$sensor_name.'">');
		echo ('('.$sensor_units[$sensor_name].')');
		echo ('</TD>');
		echo ('</FORM>');
		echo ('</TR>');    
	    }
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
}

echo ('</TR>');
echo ('</TABLE>');

echo ('</body>');
echo ('</HTML>');
?>
