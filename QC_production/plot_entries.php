<?php
  // plot_entries.php
  // D.Norcini, UChicago, 2020
  //
session_start();
$req_priv = "basic";
include("db_login.php");
include("page_setup.php");
include("aux/make_data_plot.php");



$plot_type_array = array(
			"DIEs",
			"MODULE_SURFACE"
			);


if (empty($_SESSION['choose_type']))
  $_SESSION['choose_type'] = $plot_type_array[0];

if (!(empty($_POST['choose_type'])))
  $_SESSION['choose_type'] = $_POST['choose_type'];


echo ('<TABLE border="1" cellpadding="2" width=100%>');
foreach ($plot_type_array as $index)
{
  echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
  echo ('<TH>');
  echo ('<input type="submit" name="choose_type" value="'.$index.'" 
               title="'.$index.'" style="font-size: 10pt">');
  echo ('</TH>');
echo ('</FORM>');
}

echo ('</TABLE>');

echo ('<TABLE border="1" cellpadding="2" width=100%>');

//ADD ALL PARAMETERS TO SAME ARRAY
$die_parameter_names = array(
    "Activation",
    "Temp",
    "Image1_Noise_L1",
    "Image1_Noise_L2",
    "Image1_Noise_U1",
    "Image1_Noise_U2",
    "Image2_Pixel_Defects_L1",
    "Image2_Pixel_Defects_L2",
    "Image2_Pixel_Defects_U1",
    "Image2_Pixel_Defects_U2",
    "Image2_Noise_Overscan_L1",
    "Image2_Noise_Overscan_L2",
    "Image2_Noise_Overscan_U1",
    "Image2_Noise_Overscan_U2",
    "Image2_CTI_Code_L1",
    "Image2_CTI_Code_L2",
    "Image2_CTI_Code_U1",
    "Image2_CTI_Code_U2",
    "Image3_Pixel_Defects_L1",
    "Image3_Pixel_Defects_L2",
    "Image3_Pixel_Defects_U1",
    "Image3_Pixel_Defects_U2",
    "Image3_Noise_Overscan_L1",
    "Image3_Noise_Overscan_L2",
    "Image3_Noise_Overscan_U1",
    "Image3_Noise_Overscan_U2",
    "Image3_CTI_Code_L1",
    "Image3_CTI_Code_L2",
    "Image3_CTI_Code_U1",
    "Image3_CTI_Code_U2",
    "Image4_Dark_Current_L1",
    "Image4_Dark_Current_L2",
    "Image4_Dark_Current_U1",
    "Image4_Dark_Current_U2",
    "Image6_CTI_Code_L1",
    "Image6_CTI_Code_L2",
    "Image6_CTI_Code_U1",
    "Image6_CTI_Code_U2",
    "Image6_Noise_Overscan_L1",
    "Image6_Noise_Overscan_L2",
    "Image6_Noise_Overscan_U1",
    "Image6_Noise_Overscan_U2"
);

    $module_surface_parameter_names = array(
    "Activation",
    "Temp_Low",
    "Temp_High",
    "Image1_High_Noise_A",
    "Image1_High_Noise_B",
    "Image1_High_Noise_C",
    "Image1_High_Noise_D",
    "Image2_High_Column_Defects_A",
    "Image2_High_Column_Defects_B",
    "Image2_High_Column_Defects_C",
    "Image2_High_Column_Defects_D",
    "Image2_High_Noise_A",
    "Image2_High_Noise_B",
    "Image2_High_Noise_C",
    "Image2_High_Noise_D",
    "Image3_High_Resolution_A",
    "Image3_High_Resolution_B",
    "Image3_High_Resolution_C",
    "Image3_High_Resolution_D",
    "Image3_High_Gain_A",
    "Image3_High_Gain_B",
    "Image3_High_Gain_C",
    "Image3_High_Gain_D",
    "Image3_High_Dark_Current_A",
    "Image3_High_Dark_Current_B",
    "Image3_High_Dark_Current_C",
    "Image3_High_Dark_Current_D",
    "Image4_High_Pixel_Defects_A",
    "Image4_High_Pixel_Defects_B",
    "Image4_High_Pixel_Defects_C",
    "Image4_High_Pixel_Defects_D",
     "Image4_High_Column_Defects_A",
    "Image4_High_Column_Defects_B",
    "Image4_High_Column_Defects_C",
    "Image4_High_Column_Defects_D",
    "Image4_High_Noise_Overscan_A",
    "Image4_High_Noise_Overscan_B",
    "Image4_High_Noise_Overscan_C",
    "Image4_High_Noise_Overscan_D",
    "Image3_Low_Resolution_A",
    "Image3_Low_Resolution_B",
    "Image3_Low_Resolution_C",
    "Image3_Low_Resolution_D",
    "Image3_Low_Gain_A",
    "Image3_Low_Gain_B",
    "Image3_Low_Gain_C",
    "Image3_Low_Gain_D",
    "Image3_Low_Dark_Current_A",
    "Image3_Low_Dark_Current_B",
    "Image3_Low_Dark_Current_C",
    "Image3_Low_Dark_Current_D",
    "Image4_Low_Peak1_A",
    "Image4_Low_Peak1_B",
    "Image4_Low_Peak1_C",
    "Image4_Low_Peak1_D",
    "Image4_Low_Peak2_A",
    "Image4_Low_Peak2_B",
    "Image4_Low_Peak2_C",
    "Image4_Low_Peak2_D",
    "Image4_Low_Sigma_A",
    "Image4_Low_Sigma_B",
    "Image4_Low_Sigma_C",
    "Image4_Low_Sigma_D",
    );

if ($_SESSION['choose_type'] == "DIEs")
  {
    foreach ($die_parameter_names as $parm_name)
      {
	echo ('<TR>');               
	echo ('<TD align="left">');
    
	$query = "SELECT ID, ".$parm_name." FROM DIE WHERE Status!='Failed' ORDER BY ID";

	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
	
	$text_version = "";
	$die_id  = array();
	$value = array();

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
	  {
	    if ($row[$parm_name] != 0)
	      {
		$die_id[]  = (int)$row['ID'];
		$value[] = (double)$row[$parm_name];
		$text_version .= (int)$row['ID'].", ".(double)$row[$parm_name]."\n";
	      }
	  }
	
	$plot_name = "jpgraph_cache/dieplot_".$parm_name.".png";
	$plot_title = $parm_name;
 
	make_data_plot($plot_name, $die_id, $value, $plot_title, "DIE ID",
		       $parm_name." (".$die_parameter_units[$parm_name].")", 
		       $die_parameter_targets[$parm_name],
		       $die_parameter_targets_plus[$parm_name],
		       $die_parameter_targets_minus[$parm_name]);
 
	echo ('<img src='.$plot_name.'>'); 

	$plot_name = "jpgraph_cache/dieplot_".$parm_name."_hist.png";
	make_data_hist($plot_name, $die_id, $value, $plot_title, "DIE ID",
		       $parm_name." (".$die_parameter_units[$parm_name].")", 
		       $die_parameter_targets[$parm_name],
		       $die_parameter_targets_plus[$parm_name],
		       $die_parameter_targets_minus[$parm_name]);

	echo ('<img src='.$plot_name.'>'); 
	
	echo ('</TD>');

	echo ('<TD>');
	echo ('<FORM action="export_XY_data.php" method="post">');
	echo ('Export data to text file: <input type="submit" src="pixmaps/export.png" name="export" value='.$parm_name.' title="Export data">');
	echo ('<input type="hidden" name="text" value="'.$text_version.'">');
	echo ('</FORM>');
	echo ('</TD>');
       
	echo ('</TR>');   	
      }
  }

if ($_SESSION['choose_type'] == "MODULE_SURFACE")
  {
    foreach ($module_surface_parameter_names as $parm_name)
      {
	echo ('<TR>');               
	echo ('<TD align="left">');
    
	$query = "SELECT ID, ".$parm_name." FROM MODULE_SURFACE WHERE Status!='Failed' ORDER BY ID";

	$result = mysql_query($query);
	if (!$result)
	  module_surface ("Could not query the database <br />" . mysql_error());
	
	$text_version = "";
	$module_surface_id  = array();
	$value = array();

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
	  {
	    if ($row[$parm_name] != 0)
	      {
		$module_surface_id[]  = (int)$row['ID'];
		$value[] = (double)$row[$parm_name];
		$text_version .= (int)$row['ID'].", ".(double)$row[$parm_name]."\n";
	      }
	  }
	
	$plot_name = "jpgraph_cache/module_surfaceplot_".$parm_name.".png";
	$plot_title = $parm_name;
 
	make_data_plot($plot_name, $module_surface_id, $value, $plot_title, "MODULE_SURFACE ID",
		       $parm_name." (".$module_surface_parameter_units[$parm_name].")", 
		       $module_surface_parameter_targets[$parm_name],
		       $module_surface_parameter_targets_plus[$parm_name],
		       $module_surface_parameter_targets_minus[$parm_name]);
 
	echo ('<img src='.$plot_name.'>'); 

	$plot_name = "jpgraph_cache/module_surfaceplot_".$parm_name."_hist.png";
	make_data_hist($plot_name, $module_surface_id, $value, $plot_title, "MODULE_SURFACE ID",
		       $parm_name." (".$module_surface_parameter_units[$parm_name].")", 
		       $module_surface_parameter_targets[$parm_name],
		       $module_surface_parameter_targets_plus[$parm_name],
		       $module_surface_parameter_targets_minus[$parm_name]);

	echo ('<img src='.$plot_name.'>'); 
	
	echo ('</TD>');

	echo ('<TD>');
	echo ('<FORM action="export_XY_data.php" method="post">');
	echo ('Export data to text file: <input type="submit" src="pixmaps/export.png" name="export" value='.$parm_name.' title="Export data">');
	echo ('<input type="hidden" name="text" value="'.$text_version.'">');
	echo ('</FORM>');
	echo ('</TD>');
       
	echo ('</TR>');   	
      }
  }

echo ('</TABLE>');

mysql_close($connection);
echo(' </body>');
echo ('</HTML>');
?>
