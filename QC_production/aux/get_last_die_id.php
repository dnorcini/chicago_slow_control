<?php
// get_ccd_housing_id.php
// D.Norcini, UChicago, 2020
//


$query = "SELECT `ID` FROM ".$table."  ORDER BY `ID` DESC LIMIT 1";
$result = mysql_query($query);
if (!$result)
  die ("Could not query the database <br />" . mysql_error());

// Set a default value in case no result is found
$last_id = 0;  // Default to 0 if no rows exist

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  $last_id = $row['ID'];

?>
