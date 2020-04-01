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
			"CCDs",
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
$ccd_parameter_names = array_merge($glue_parameter_names, $wb_parameter_names, $testing_parameter_names);

if ($_SESSION['choose_type'] == "CCDs")
  {
    foreach ($ccd_parameter_names as $parm_name)
      {
	echo ('<TR>');               
	echo ('<TD align="left">');
    
	$query = "SELECT ID, ".$parm_name." FROM CCD WHERE Status!='Failed' ORDER BY ID";

	$result = mysql_query($query);
	if (!$result)
	  die ("Could not query the database <br />" . mysql_error());
	
	$text_version = "";
	$ccd_id  = array();
	$value = array();

	while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
	  {
	    if ($row[$parm_name] != 0)
	      {
		$ccd_id[]  = (int)$row['ID'];
		$value[] = (double)$row[$parm_name];
		$text_version .= (int)$row['ID'].", ".(double)$row[$parm_name]."\n";
	      }
	  }
	
	$plot_name = "jpgraph_cache/ccdplot_".$parm_name.".png";
	$plot_title = $parm_name;
 
	make_data_plot($plot_name, $ccd_id, $value, $plot_title, "CCD ID",
		       $parm_name." (".$ccd_parameter_units[$parm_name].")", 
		       $ccd_parameter_targets[$parm_name],
		       $ccd_parameter_targets_plus[$parm_name],
		       $ccd_parameter_targets_minus[$parm_name]);
 
	echo ('<img src='.$plot_name.'>'); 

	$plot_name = "jpgraph_cache/ccdplot_".$parm_name."_hist.png";
	make_data_hist($plot_name, $ccd_id, $value, $plot_title, "CCD ID",
		       $parm_name." (".$ccd_parameter_units[$parm_name].")", 
		       $ccd_parameter_targets[$parm_name],
		       $ccd_parameter_targets_plus[$parm_name],
		       $ccd_parameter_targets_minus[$parm_name]);

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
