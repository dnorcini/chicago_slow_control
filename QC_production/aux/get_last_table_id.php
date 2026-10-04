<?php
// get_last_table_id.php
// James Nikkel, Yale University, 2016
// james.nikkel@yale.edu
//
// Sets $last_id to the highest ID in $table (0 if the table is empty).

$query = "SELECT `ID` FROM `".$table."`  ORDER BY `ID` DESC LIMIT 1";
$result = mysql_query($query);
if (!$result)
  die ("Could not query the database <br />" . mysql_error());

$last_id = 0;
while ($row = mysql_fetch_array($result, MYSQL_ASSOC))
  $last_id = $row['ID'];

?>
