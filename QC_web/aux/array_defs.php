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
			"DAMIC Module",
			"47/6 Skipper+DES"
			);


$item_packager_array = array(	
		             " ",
	                     "UW",
			     "LSM",
			     "LBNL",
                             );
			     
$item_location_array = array(
		             " ",
			     "UChicago",
			     "UW",
			     "LPNHE",
			     "LSM",
			     "PNNL",
			     "UZH",
			     "IFCA",
			     "Other",
			     );

$ccd_size_array = array(
			" ",
                        "4kx2k",
			"1kx4k",
			"1kx6k",
			"6kx1k",
			"6kx1.5k",
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
$testing_noise_names = array(
				 "Noise_U1",
				 "Noise_L1",
				 "Noise_U2",
				 "Noise_L2"
                                  );

$testing_noise_title = array(
				 "Noise U1",
				 "Noise L1",
				 "Noise U2",
				 "Noise L2"
                                  );
$testing_noise_title = array_combine($testing_noise_names, $testing_noise_title);

$testing_noise_units = array(
				 "ADU",
				 "ADU",
				 "ADU",
				 "ADU",
				 );
$testing_noise_units = array_combine($testing_noise_names, $testing_noise_units);

////////////////////////////////////////////////////////////////////////////////////////
$testing_resolution_names = array(
				 "Resolution_U1",
				 "Resolution_L1",
				 "Resolution_U2",
				 "Resolution_L2"
                                  );

$testing_resolution_title = array(
				 "Resolution U1",
				 "Resolution L1",
				 "Resolution U2",
				 "Resolution L2"
                                  );
$testing_resolution_title = array_combine($testing_resolution_names, $testing_resolution_title);

$testing_resolution_units = array(
				 "ADU",
				 "ADU",
				 "ADU",
				 "ADU",
				 );
$testing_resolution_units = array_combine($testing_resolution_names, $testing_resolution_units);

////////////////////////////////////////////////////////////////////////////////////////
$testing_gain_names = array(
				 "Gain_U1",
				 "Gain_L1",
				 "Gain_U2",
				 "Gain_L2"
                                  );

$testing_gain_title = array(
				 "Gain U1",
				 "Gain L1",
				 "Gain U2",
				 "Gain L2"
                                  );
$testing_gain_title = array_combine($testing_gain_names, $testing_gain_title);

$testing_gain_units = array(
				 "ADU",
				 "ADU",
				 "ADU",
				 "ADU",
				 );
$testing_gain_units = array_combine($testing_gain_names, $testing_gain_units);

////////////////////////////////////////////////////////////////////////////////////////
$testing_dark_current_names = array(
				 "Dark_current_U1",
				 "Dark_current_L1",
				 "Dark_current_U2",
				 "Dark_current_L2"
                                  );

$testing_dark_current_title = array(
				 "Dark_current U1",
				 "Dark_current L1",
				 "Dark_current U2",
				 "Dark_current L2"
                                  );
$testing_dark_current_title = array_combine($testing_dark_current_names, $testing_dark_current_title);

$testing_dark_current_units = array(
				 "e-/px/day",
				 "e-/px/day",
				 "e-/px/day",
				 "e-/px/day",
				 );
$testing_dark_current_units = array_combine($testing_dark_current_names, $testing_dark_current_units);

////////////////////////////////////////////////////////////////////////////////////////
//$testing_parameter_names = array(
//				 "Noise",
//				 "Resolution",
//				 "Gain",
//				 "Dark current"
//                                  );

//$testing_parameter_title = array(
//				 "Noise",
//				 "Resolution",
//				 "Gain",
//				 "Dark current"
//                                  );
//$testing_parameter_title = array_combine($testing_U1_names, $testing_U1_title);

//$testing_parameter_units = array(
//				 "ADU",
//				 "e-",
//				 "ADU/e-",
//				 "e-/px/day",
//				 );
//$testing_U1_units = array_combine($testing_U1_names, $testing_U1_units);


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
