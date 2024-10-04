<?php
// get_last_table_id.php
// James Nikkel, Yale University, 2016
// james.nikkel@yale.edu
//


$query = "SELECT `ID` FROM `".$table."`  ORDER BY `ID` DESC LIMIT 1";
$result = mysql_query($query);
if (!$result)
  die ("Could not query the database <br />" . mysql_error());

while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  $last_id = $row['ID'];

?>
