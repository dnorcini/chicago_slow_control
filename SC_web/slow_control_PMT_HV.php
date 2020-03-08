<?php
// slow_control_text.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
$req_priv = "full";
include("db_login.php");
include("slow_control_page_setup.php");
$get_all_sensors = 1;
$get_single_type = "PMT_HV";
$my_inst_name = "LeCroy1458"; 
include("aux/get_sensor_info.php"); 
include("aux/get_inst_info.php"); 

//////////    Set globals here:
$Name_stub = "PMT_HV_S";  //  so the actual names are "PMT_HV_S02", etc..
$Master_sens = "PMT_HV_Master";
$master_sub = "Master";
$veto_sub = "Veto";
$det_sub = "Detector";

///  Read the last values from the database and save them to an array $sensor_*
include("aux/get_sensor_vals.php");
///  Read the last values from the database and save them to an array $sHV_*
include("aux/get_PMT_HV_vals.php");
///  Read the last values from the database and save them to an array $sHVs_*
include("aux/get_PMT_HV_set_vals.php");
//   Do the setting of new values 
include("aux/new_PMT_HV_set_val.php");
mysql_close($connection);

echo ('<TABLE border="1" cellpadding="2" width=100%>');
echo ('<TD align="center" colspan="1">');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="submit" name="dummy" value="Turn Off HV" title="Set Master HV Off">');
echo ('<input type="hidden" name="master_onoff" value="0" title="Set Master HV Off">');
echo ('<input type="hidden" name="set_HV_name" value="'.$Master_sens.'">');
echo ('</FORM></TD>');
echo ('<TD align="center" colspan="2">');
echo ('<b> ');
echo ($sensor_descs[$Master_sens]);
echo (' </b>');
echo (' -- Current value: ');
if ($sensor_values[$Master_sens] < 0.5)
  echo ('Off');
 else
   echo ('On');
echo ('</TD>');
echo ('<TD align="center" colspan="1">');
echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
echo ('<input type="submit" name="dummy" value="Turn On HV" title="Set Master HV On">');
echo ('<input type="hidden" name="master_onoff" value="1" title="Set Master HV On">');
echo ('<input type="hidden" name="set_HV_name" value="'.$Master_sens.'">');
echo ('</FORM></TD>');
echo ('<TD align="center" colspan="1">');
include("aux/inst_status.php");
echo ('</TD>');
echo ('</TABLE>');

echo ('<TABLE border="0" cellpadding="0" width=100%>');
echo ('<TR>');
for ($i = 0; $i < 16; $i++)
  {
    echo ('<TD>'); 
    $name = $sHV_names[$i];
    $Card_status = $sHV_values[$i];
    $Voltages = explode(",", $sHV_V[$i]);
    $Currents = explode(",", $sHV_C[$i]);
    $Stati = explode(",", $sHV_S[$i]);
    
    $set_name  = $sHVs_names[$i];
    $Card_onoff = $sHVs_values[$i];
    $Enable = explode(",", $sHVs_E[$i]);
    $Demand_V = explode(",", $sHVs_DV[$i]);
    $Trip_C = $sHVs_trp[$i];
    $Ramp_Rate = $sHVs_rmp[$i];
    
    if (($sensor_hide[$set_name]==0)&&($sensor_hide[$name]==0))
      {	
	echo ('<TABLE border="1" cellpadding="0" width=100%>');
	echo ('<TR>');
	echo ('<TH>');
	echo (' Slot '.$i);
	echo ('<br>'.$sensor_subtypes[$set_name]);
	echo ('<HR>');
	echo ('(Sts:'.$Card_status.')');
	echo ('</TH>');
	echo ('</TR>');

	echo ('<TR>');
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('<TD>');
	echo ('Trip<br>Current<br>(uA):');
	echo ('<input type="text" name="Trp_Cur" value="'.$Trip_C.'" size=3>');
	echo ('<input type="hidden" name="set_HV_name" value="'.$set_name.'">');
	echo ('<input type="hidden" name="set_HV_slot" value="'.$i.'">');
	echo ('<input type="hidden" name="set_HV_ch" value="'.$j.'">');
	echo ('</TD>');
	echo ('</TR>');

	echo ('<TR>');
	echo ('</FORM> ');
	echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	echo ('<TD>');
	echo ('Ramp<br> Rate<br>(V/s):');
	echo ('<input type="text" name="Rmp_Rt" value="'.$Ramp_Rate.'" size=3>');
	echo ('<input type="hidden" name="set_HV_name" value="'.$set_name.'">');
	echo ('<input type="hidden" name="set_HV_slot" value="'.$i.'">');
	echo ('<input type="hidden" name="set_HV_ch" value="'.$j.'">');
	echo ('</TD>');
	echo ('</FORM> ');
	echo ('</TR>');

	for ($j = 0; $j < 12; $j++)
	  {
	    echo ('<TR>');		
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<TD>');
	    if ($Enable[$j])
	      echo ('<input type="image" src="pixmaps/green_circ.png" name="Ch_onoff" value="0" title="Turn Off Channel '.
		    $j.' of Slot '.$i.'">');
	    else
	      echo ('<input type="image" src="pixmaps/red_circ.png" name="Ch_onoff" value="1" title="Turn On Channel '.
		    $j.' of Slot '.$i.'">');
	    if ($Stati[$j] > 12)
	      echo ('<font color="red"><blink>');
	    echo (' CH '.$j);
	    echo ('<input type="hidden" name="set_HV_name" value="'.$set_name.'">');
	    echo ('<input type="hidden" name="set_HV_slot" value="'.$i.'">');
	    echo ('<input type="hidden" name="set_HV_ch" value="'.$j.'">');
	    echo ('</FORM> ');
	    echo ('<HR>');
	    echo ($Voltages[$j].'(V)<br>'.$Currents[$j].'(uA)<br>');
	    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
	    echo ('<input type="text" name="Demand_V" value="'.$Demand_V[$j].'" size=3>(V)');
	    echo ('<input type="hidden" name="set_HV_name" value="'.$set_name.'">');
	    echo ('<input type="hidden" name="set_HV_slot" value="'.$i.'">');
	    echo ('<input type="hidden" name="set_HV_ch" value="'.$j.'">');
	    echo ('</TD>');
	    echo ('</FORM> ');
	    echo ('</TR>');
	  }

	echo ('</TABLE>');
      }
    else
	echo (' Slot '.$i.'<br>Disabled<br>');
    
    echo ('</TD>');
  }
echo ('</TR>');
echo ('</TABLE>');

echo ('</body>');
echo ('</HTML>');
?>
