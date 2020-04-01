<?php
// array_defs.php
// D.Norcini, UChicago, 2020
//
$select_times = array(
    "10s" => 10,
    "30s" => 30,
    "1m" => 1*60,
    "3m" => 3*60,
    "10m" => 10*60,
    "30m" => 30*60,
    "never" => -1 
    );

$num_msgs_array = array(
    "Last 10" => 10,
    "Last 50" => 50,
    "Last 100" => 100,
    "All" => -1,
    "From plot times" => -2,
    "First 10" => -10,
    "First 50" => -50,
    "First 100" => -100,
    );

$html_colours = array(
    "aqua", "black", "blue", 
    "gray", "green", "lime", "maroon", 
    "navy", "orange", "purple", "red", 
    "silver", "teal", "wheat", "white", "yellow"
    );

////////////////////////////////////////////////////////////////////////////////////////


$privilege_array = array();
$allowed_host_array = array();
$query = "SELECT `name`, `allowed_host` FROM `user_privileges` ORDER BY `name`";
$result = mysql_query($query);
if (!$result)	
  die ("Could not query the database for message types <br>" . mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $privilege_array[] = $row['name'];
    $allowed_host_array[] = $row['allowed_host'];
}
$allowed_host_array = array($privilege_array, $allowed_host_array);
$privilege_array = make_unique($privilege_array);

////////////////////////////////////////////////////////////////////////////////////////

$ccd_qc_status_array = array(
		             " ",
			     "Unchecked", 
			     "Science-grade",
			     "Operation-grade",
			     "Toy-grade",
			     "Failed",
			     );


$ccd_type_array = array(
			" ",
			"DES D42",
			"LBNL Skipper",
			"DAMIC Skipper",
			"47/6 Skipper+DES"
			);


$item_packager_array = array(	
		             " ",
	                     "UW",
			     "Modane",
			     "LBNL",
                             );
			     
$item_location_array = array(
		             " ",
			     "UChicago",
			     "UW",
			     "LPNHE",
			     "Modane",
			     "PNNL",
			     "Zurich",
			     "IFCA",
			     "Other",
			     );

$ccd_size_array = array(
			" ",
                        "4kx2k",
			"1kx4k",
			"1kx6k",
			"6kx4k",
			"6kx6k"
	                );

////////////////////////////////////////////////////////////////////////////////////////
$glue_parameter_names = array(
				 "Glue_humid",
				 "Glue_temp",
				 "Glue_radon",
                                  );

$glue_parameter_title = array(
				 "Rel. Humidity",
                                 "Temperature",
				 "Radon",
                                  );
$glue_parameter_title = array_combine($glue_parameter_names, $glue_parameter_title);

$glue_parameter_units = array(
				 "%",
				 "C",
				 "Bq/m^3",
				 );
$glue_parameter_units = array_combine($glue_parameter_names, $glue_parameter_units);

////////////////////////////////////////////////////////////////////////////////////////
$wb_parameter_names = array(
				 "Wb_humid",
				 "Wb_temp",
				 "Wb_radon",
				 "Wb_power",
				 "Wb_time",
                                  );

$wb_parameter_title = array(
				 "Rel. Humidity",
                                 "Temperature",
				 "Radon",
				 "Bond Power",
				 "Bond Time",
                                  );
$wb_parameter_title = array_combine($wb_parameter_names, $wb_parameter_title);

$wb_parameter_units = array(
				 "%",
				 "C",
				 "Bq/m^3",
				 "%",
				 "us"
				 );
$wb_parameter_units = array_combine($wb_parameter_names, $wb_parameter_units);

////////////////////////////////////////////////////////////////////////////////////////
$testing_parameter_names = array(
				 "Dark_current",
				 "Resolution",
				 "Eff_resistivity",
                                  );

$testing_parameter_title = array(
				 "Dark Current",
                                 "Resolution",
				 "Effective Resistivity",
                                  );
$testing_parameter_title = array_combine($testing_parameter_names, $testing_parameter_title);

$testing_parameter_units = array(
				 "e-/pixel/image",
				 "e-",
				 "kOhm-cm",
				 );
$testing_parameter_units = array_combine($testing_parameter_names, $testing_parameter_units);


////////////////////////////////////////////////////////////////////////////////////////

//$ccd_parameter_targets = array("A" => 1,
//				   "B" => 1,
//				   );

//$ccd_parameter_targets_plus = array("A" => 0.01,
//					"B" => 0.01,
//					);

//$ccd_parameter_targets_minus = array("A" => 0.01,
//					 "B" => 0.01,
//					);

////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////
?>
