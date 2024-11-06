<?php

echo ('<BR>');

// Categories
echo ('<b>Ampifier grades<b>');
$types = array(
    'charizard' => array(
        "query" => "SELECT COUNT(*) AS count FROM MODULE_SURFACE WHERE (grade_A = 'Science' AND grade_B = 'Science' AND grade_C = 'Science' AND grade_D = 'Science')",
        "color" => "red",
        "note" => "4 Science grade amps"
    ),
    'charmeleon' => array(
        "query" => "SELECT COUNT(*) AS count FROM MODULE_SURFACE WHERE ((grade_A = 'Science') + (grade_B = 'Science') + (grade_C = 'Science') + (grade_D = 'Science')) = 3",
        "color" => "orange",
        "note" => "3 Science grade amps"
    ),
    'charmander' => array(
        "query" => "SELECT COUNT(*) AS count FROM MODULE_SURFACE WHERE ((grade_A = 'Science') + (grade_B = 'Science') + (grade_C = 'Science') + (grade_D = 'Science')) BETWEEN 1 AND 2",
        "color" => "yellow",
        "note" => "1 or 2 Science grade amps"
    ),
    'geodude' => array(
        "query" => "SELECT COUNT(*) AS count FROM MODULE_SURFACE WHERE (grade_A != 'Science' AND grade_B != 'Science' AND grade_C != 'Science' AND grade_D != 'Science')",
        "color" => "gray",
        "note" => "0 Science grade amps"
    )
);

echo ('<TR>');
foreach ($types as $type => $details) {
    // Display the type and its note in the first row
    echo ('<TH align="left" style="background-color: ' . $details['color'] . ';">');
    echo ucfirst($type) . ' (' . $details['note'] . ')';
    echo ('</TH>');
}
echo ('</TR>');
echo ('<TR>');
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

        // Add to good_module_surface_count if it's a Charizard, Charmander, or Chameleon
        if (in_array($type, ['charizard', 'charmeleon', 'charmander'])) {
            $good_module_surface_count += $count;
        }
    } else {
        // Optional: display an error message if the query fails
        echo '<TD align="left" style="background-color: ' . $details['color'] . ';">Error</TD>';
    }
}
echo ('</TR>');

// Display the number of amplifiers labeled "C" by grade
echo ('<TR>');
echo ('<TH align="left">'); echo ('C Science'); echo ('</TH>');
echo ('<TH align="left">'); echo ('C Engineering'); echo ('</TH>');
echo ('<TH align="left">'); echo ('C Operational'); echo ('</TH>');
echo ('<TH align="left">'); echo ('C Failed'); echo ('</TH>');
echo ('</TR>');

$grades_u1 = array("Science", "Engineering", "Operational", "Failed");
foreach ($grades_u1 as $grade) {
    $query = "SELECT COUNT(*) FROM MODULE_SURFACE WHERE grade_C = '$grade'";
    $result = mysql_query($query);
    $row = mysql_fetch_row($result);
    echo ('<TD align="left">'); echo ($row ? $row[0] : 0); echo ('</TD>');
}
echo ('</TR>');

echo ('<TR style="height: 20px;"><TD colspan="4" style="border: none; padding: 0;"></TD></TR>');
echo ('<TR>');
echo ('<TH colspan="4" align="left" style="font-weight: bold; font-size: 16px; background-color: white;">Totals</TH>');
echo ('</TR>');

// Total number of module_surfaces
echo ('<TR>');
echo ('<TH align="left">');  echo ('Number of Total MODULE_SURFACEs'); echo ('</TH>');
$query = "SELECT COUNT(*) FROM MODULE_SURFACE WHERE ID >= 1";
$result = mysql_query($query);
$row = mysql_fetch_row($result);
$total_module_surfaces = $row ? $row[0] : 0;
echo ('<TD align="left">'); echo ($total_module_surfaces); echo ('</TD>');

// Calculate and display yield
$yield = $total_module_surfaces ? $good_module_surface_count / $total_module_surfaces : 0;
echo ('<TH align="left">'); echo ('Yield (at least 1 Science grade amp)'); echo ('</TH>');
echo ('<TD align="left">'); echo (number_format($yield * 100, 2) . '%'); echo ('</TD>');
echo ('</TR>');

echo ('</TABLE>');
?>
