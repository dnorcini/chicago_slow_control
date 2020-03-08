<?php
// array_defs.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006, 2007, 2008, 2010
// james.nikkel@yale.edu
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

$message_types_array = array();
$query = "SELECT `types` FROM `msg_log_types` ORDER BY `types`";
$result = mysql_query($query);
if (!$result)	
    die ("Could not query the database for message types <br>" . mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    $message_types_array[] = $row['types'];

////////////////////////////////////////////////////////////////////////////////////////

/* $lug_cat_array = array(); */
/* $query = "SELECT `category` FROM `lug_categories` ORDER BY `category`"; */
/* $result = mysql_query($query); */
/* if (!$result)	 */
/*     die ("Could not query the database for message types <br>" . mysql_error()); */

/* while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) */
/*     $lug_cat_array[] = $row['category']; */

/* //////////////////////////////////////////////////////////////////////////////////////// */

/* $lug_subcat_array = array(); */
/* $temp = array(); */
/* $query = "SELECT `subcategory`, `category`  FROM `lug_subcategories` ORDER BY `subcategory`"; */
/* $result = mysql_query($query); */
/* if (!$result)	 */
/*     die ("Could not query the database for message types <br>" . mysql_error()); */

/* while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) */
/* {      */
/*     $lug_subcat_array[] = $row['subcategory']; */
/*     $temp[] = $row['category']; */
/* } */
/* $lug_subcat_array = array_combine($lug_subcat_array, $temp); */
////////////////////////////////////////////////////////////////////////////////////////

$lug_cat_array = array();
$lug_subcat_array = array();
$query = "SELECT `category`, `subcategory` FROM `lug_categories` ORDER BY `category`";
$result = mysql_query($query);
if (!$result)	
    die ("Could not query the database for message types <br>" . mysql_error());
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  {
    $lug_cat_array[] = $row['category'];
    $lug_subcat_array[] = $row['subcategory'];
  }
$lug_subcat_array = array($lug_cat_array, $lug_subcat_array);
$lug_cat_array = make_unique($lug_cat_array);

////////////////////////////////////////////////////////////////////////////////////////

$shift_status_array = array();
$shift_can_manage = array();
$query = "SELECT `status`, `can_manage` FROM `user_shift_status` ORDER BY `status`";
$result = mysql_query($query);
if (!$result)	
    die ("Could not query the database for message types <br>" . mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
    $shift_status_array[] = $row['status'];
    $shift_can_manage[] = (int)$row['can_manage'];
}
$shift_can_manage = array_combine($shift_status_array, $shift_can_manage);

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

$sens_type_array = array();
$sens_type_array_index = array();
$query = "SELECT `num`, `name` FROM `sc_sensor_types` ORDER BY `name`";
$result = mysql_query($query);
if (!$result)	
    die ("Could not query the database for message types <br>" . mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
{
  $sens_type_array_index[] =  $row['num'];
  $sens_type_array[] = $row['name'];
}
$sens_type_array = array_combine($sens_type_array_index, $sens_type_array);

/////////////////////////////////////////////////////////////////////////////////////////
?>
