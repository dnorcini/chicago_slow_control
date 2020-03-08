<?php
  // slow_control_text.php
  // Part of the CLEAN slow control.  
  // James Nikkel, Yale University, 2006.
  // james.nikkel@yale.edu
  //
session_start();
$never_ref = 1;
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php"); 
include("aux/new_set_val.php");     // do insert of new value if requested

///  The idea here is to create a list of unique sensor types from all the available sensors.
///  This allows us to just view one type at a time on the screen.
include("aux/choose_types.php");    // sets session variable $choose_type from the check boxes.

///  Read the last values from the database and save them to an array $sensor_values
///  indexed by $sensor_name
include("aux/get_sensor_vals.php");
mysql_close($connection);

////   This next section generate the HTML with the plot names in a table.

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
foreach ($sensor_names as $sensor_name)
{
  if (!(any_in_array(explode(",", $sensor_types[$sensor_name]), $sens_type_array_index)))
    $my_sensor_names[] = $sensor_name;
}

foreach ($my_sensor_names as $sensor_name)
{
  if ((check_access($_SESSION['privileges'], $sensor_ctrl_priv[$sensor_name], $allowed_host_array)) && ($sensor_settable[$sensor_name]))
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
      if  (strncmp($sensor_units[$sensor_name], "discrete", 8) == 0)   // discrete toggle:
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
	  if (count($all_vals) < 5)                // make buttons if there are < 5 items
	    {
	      foreach ($all_vals as $av_v => $av_s)
		{
		  echo ('<TD  align="center">');
		  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
		  echo ('<input type="submit" name="dummy" value="'.$av_s.'" title="Set to '.$av_s.'">  ');
		  echo ('<input type="hidden" name="new_set_val" value="'.$av_v.'">');
		  echo ('<input type="hidden" name="set_sens_name" value="'.$sensor_name.'">');
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
		  echo('<option ');
		  if (strcmp($av_v , $sensor_values[$sensor_name]) == 0)
		    echo ('selected="selected"');
		  echo(' value="'.$av_v.'" >'.$av_s.'</option> ');   
		}
	      echo ('</select>');
	      echo ('<input type="hidden" name="set_sens_name" value="'.$sensor_name.'">');
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
	  echo ('<input type="hidden" name="set_sens_name" value="'.$sensor_name.'">');
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
echo ('</TR>');
echo ('</TABLE>');

echo ('</body>');
echo ('</HTML>');
?>
