<?php
// export_XY_data.php
// Part of the CLEAN slow control.  
// James Nikkel, Yale University, 2006.
// james.nikkel@yale.edu
//
session_start();
header("Content-Type: application/octet-stream");
header('Content-Disposition: filename="data.txt"');

echo("## Export of parameter: ".$_POST['export']."\n");
echo("## ID, value \n");

echo ( $_POST['text']);

?>