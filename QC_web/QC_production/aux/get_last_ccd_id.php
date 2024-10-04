<?php
// get_ccd_housing_id.php
// D.Norcini, UChicago, 2020
//


$query = "SELECT `ID` FROM ".$table."  ORDER BY `ID` DESC LIMIT 1";
$result = mysql_query($query);
if (!$result)
  die ("Could not query the database <br />" . mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  $last_id = $row['ID'];

?>
