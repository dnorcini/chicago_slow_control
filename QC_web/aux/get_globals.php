<?php
// get_globals_info.php
// James Nikkel, Yale University, 2016
// james.nikkel@yale.edu
//

$query = "SELECT * FROM `globals` ORDER BY `name`";
$result = mysql_query($query);
if (!$result)
{	
    die ("Could not get globals table.<br>Some features may not work. <br>");
}
else
{
    $global_names = array();
    $global_int1 = array();
    $global_int2 = array();
    $global_int3 = array();
    $global_int4 = array();
    $global_double1 = array();
    $global_double2 = array();
    $global_double3 = array();
    $global_double4 = array();
    $global_string1 = array();
    $global_string2 = array();
    $global_string3 = array();
    $global_string4 = array();
    
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
    {
	$global_names[] = $row['name'];
	$global_int1[] = (int)$row['int1'];
	$global_int2[] = (int)$row['int2'];
	$global_int3[] = (int)$row['int3'];
	$global_int4[] = (int)$row['int4'];
	$global_double1[] = (double)$row['double1'];
	$global_double2[] = (double)$row['double2'];
	$global_double3[] = (double)$row['double3'];
	$global_double4[] = (double)$row['double4'];
	$global_string1[] = $row['string1'];
	$global_string2[] = $row['string2'];
	$global_string3[] = $row['string3'];
	$global_string4[] = $row['string4'];
    }
    
    $global_int1 = array_combine($global_names, $global_int1);
    $global_int2 = array_combine($global_names, $global_int2);
    $global_int3 = array_combine($global_names, $global_int3);
    $global_int4 = array_combine($global_names, $global_int4);
    $global_double1 = array_combine($global_names, $global_double1);
    $global_double2 = array_combine($global_names, $global_double2);
    $global_double3 = array_combine($global_names, $global_double3);
    $global_double4 = array_combine($global_names, $global_double4);
    $global_string1 = array_combine($global_names, $global_string1);
    $global_string2 = array_combine($global_names, $global_string2);
    $global_string3 = array_combine($global_names, $global_string3);
    $global_string4 = array_combine($global_names, $global_string4);
}

?>
