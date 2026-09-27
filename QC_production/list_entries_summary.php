<?php

echo ('<BR>');

// Categories
echo ('<b>Amplifier grades<b>');
$types = [
    'charizard' => [
        "query" => "SELECT COUNT(*) AS count FROM DIE WHERE (grade_L1 = 'Science' AND grade_L2 = 'Science' AND grade_U1 = 'Science' AND grade_U2 = 'Science')",
        "color" => "red",
        "note" => "4 Science grade amps"
    ],
    'charmeleon' => [
        "query" => "SELECT COUNT(*) AS count FROM DIE WHERE ((grade_L1 = 'Science') + (grade_L2 = 'Science') + (grade_U1 = 'Science') + (grade_U2 = 'Science')) = 3",
        "color" => "orange",
        "note" => "3 Science grade amps"
    ],
    'charmander' => [
        "query" => "SELECT COUNT(*) AS count FROM DIE WHERE ((grade_L1 = 'Science') + (grade_L2 = 'Science') + (grade_U1 = 'Science') + (grade_U2 = 'Science')) BETWEEN 1 AND 2",
        "color" => "yellow",
        "note" => "1 or 2 Science grade amps"
    ],
    'geodude' => [
        "query" => "SELECT COUNT(*) AS count FROM DIE WHERE (grade_L1 != 'Science' AND grade_L2 != 'Science' AND grade_U1 != 'Science' AND grade_U2 != 'Science')",
        "color" => "gray",
        "note" => "0 Science grade amps"
    ]
];

echo ('<TR>');
foreach ($types as $type => $details) {
    // Display the type and its note in the first row
    echo ('<TH align="left" style="background-color: ' . $details['color'] . ';">');
    echo ucfirst($type) . ' (' . $details['note'] . ')';
    echo ('</TH>');
}
echo ('</TR>');
echo ('<TR>');
$good_die_count = 0;
foreach ($types as $type => $details) {
    // Execute the query
    $result = mysql_query($details['query']);
    if ($result) {
        $row = mysql_fetch_assoc($result);
        $count = $row['count'];

        // Display the count in the row below each label
        echo ('<TD align="left" style="background-color: ' . $details['color'] . ';">');
        echo $count;
        echo ('</TD>');

        // Add to good_die_count if it's a Charizard, Charmander, or Chameleon
        if (in_array($type, ['charizard', 'charmeleon', 'charmander'])) {
            $good_die_count += $count;
        }
    } else {
        // Optional: display an error message if the query fails
        echo '<TD align="left" style="background-color: ' . $details['color'] . ';">Error</TD>';
    }
}
echo ('</TR>');

// Display the number of amplifiers labeled "U1" by grade
echo ('<TR>');
echo ('<TH align="left">'); echo ('U1 Science'); echo ('</TH>');
echo ('<TH align="left">'); echo ('U1 Engineering'); echo ('</TH>');
echo ('<TH align="left">'); echo ('U1 Operational'); echo ('</TH>');
echo ('<TH align="left">'); echo ('U1 Failed'); echo ('</TH>');
echo ('</TR>');

$grades_u1 = ["Science", "Engineering", "Operational", "Failed"];
foreach ($grades_u1 as $grade) {
    $query = "SELECT COUNT(*) FROM DIE WHERE grade_U1 = '$grade'";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    echo ('<TD align="left">'); echo ($row ? $row[0] : 0); echo ('</TD>');
}
echo ('</TR>');

echo ('<TR style="height: 20px;"><TD colspan="4" style="border: none; padding: 0;"></TD></TR>');
echo ('<TR>');
echo ('<TH colspan="4" align="left" style="font-weight: bold; font-size: 16px; background-color: white;">Totals</TH>');
echo ('</TR>');

// Total number of dies
echo ('<TR>');
echo ('<TH align="left">');  echo ('Number of Total DIEs'); echo ('</TH>');
$query = "SELECT COUNT(*) FROM DIE WHERE ID >= 1";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$total_dies = $row ? $row[0] : 0;
echo ('<TD align="left">'); echo ($total_dies); echo ('</TD>');

// Calculate and display yield
$yield = $total_dies ? $good_die_count / $total_dies : 0;
echo ('<TH align="left">'); echo ('Yield (at least 1 Science grade amp)'); echo ('</TH>');
echo ('<TD align="left">'); echo (number_format($yield * 100, 2) . '%'); echo ('</TD>');
echo ('</TR>');

echo ('</TABLE>');
?>
