<?php

echo ('<TR>');
echo ('<TH align="left">');  echo ('Number of Total DIEs');     echo ('</TH>');
echo ('</TR>');

echo ('<TR>');
$query = "SELECT COUNT(*) FROM DIE WHERE ID >= 1";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);          echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TH align="left">');  echo ('Science grade DIEs'); echo ('</TH>');
echo ('<TH align="left">');  echo ('Engineering grade DIEs');     echo ('</TH>');
echo ('<TH align="left">');  echo ('Operational grade DIEs');     echo ('</TH>');
echo ('<TH align="left">');  echo ('Failed DIEs');       echo ('</TH>');
echo ('</TR>');

echo ('<TR>');
$query = "SELECT COUNT(*) FROM DIE WHERE DIE_Type like '%Skipper%' AND Status='Science-grade' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);	      echo ('</TD>');

$query = "SELECT COUNT(*) FROM DIE WHERE DIE_Type like '%Skipper%' AND Status='Operation-grade' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);	      echo ('</TD>');

$query = "SELECT COUNT(*) FROM DIE WHERE DIE_Type like '%Skipper%' AND Status='Failed' ";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
echo ('<TD align="left">');  echo ($row[0]);          echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TD colspan=5 align="center">'); echo ('---'); echo ('</TD>');
echo ('</TR>');

// New section for type tallying and color coding
$types = array(
    'charizard' => 'red',
    'geodes' => 'blue',
    'charmander' => 'orange',
    'charmeleon' => 'yellow'
);

$total_tally = 0;
$total_science = 0;

foreach ($types as $type => $color) {
    echo ('<TR>');
    echo ('<TH align="left" style="color: ' . $color . ';">');  echo ('Number of ' . ucfirst($type) . ' DIEs');  echo ('</TH>');

    $query = "SELECT COUNT(*) FROM DIE WHERE DIE_Type LIKE '%" . ucfirst($type) . "%'";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    echo ('<TD align="left">');  echo ($row[0]);  echo ('</TD>');
    $total_tally += $row[0];

    // Count the science-grade DIEs for this type
    $query = "SELECT COUNT(*) FROM DIE WHERE DIE_Type LIKE '%" . ucfirst($type) . "%' AND Status='Science-grade'";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    echo ('<TD align="left">');  echo ($row[0]);  echo ('</TD>');
    $total_science += $row[0];
    echo ('</TR>');
}

// Display total tally and science DIEs
echo ('<TR>');
echo ('<TH align="left">');  echo ('Total Tally');  echo ('</TH>');
echo ('<TD align="left">');  echo ($total_tally);  echo ('</TD>');
echo ('</TR>');

echo ('<TR>');
echo ('<TH align="left">');  echo ('Total Science DIEs');  echo ('</TH>');
echo ('<TD align="left">');  echo ($total_science);  echo ('</TD>');
echo ('</TR>');

// Yield calculation
$yield = ($total_tally > 0) ? ($total_science / $total_tally) * 100 : 0;
echo ('<TR>');
echo ('<TH align="left">');  echo ('Yield (Total Science / Total Tally) %');  echo ('</TH>');
echo ('<TD align="left">');  echo (number_format($yield, 2));  echo ('%</TD>');
echo ('</TR>');

// U2 status tally
echo ('<TR>');
echo ('<TH align="left">');  echo ('U2 Status - Science');  echo ('</TH>');
echo ('<TH align="left">');  echo ('U2 Status - Engineering');  echo ('</TH>');
echo ('<TH align="left">');  echo ('U2 Status - Operational');  echo ('</TH>');
echo ('<TH align="left">');  echo ('U2 Status - Failed');  echo ('</TH>');
echo ('</TR>');

$u2_statuses = array('Science-grade', 'Engineering-grade', 'Operational-grade', 'Failed');
foreach ($u2_statuses as $status) {
    $query = "SELECT COUNT(*) FROM DIE WHERE DIE_Type='U2' AND Status='$status'";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    echo ('<TD align="left">');  echo ($row[0]);  echo ('</TD>');
}
echo ('</TR>');
?>
