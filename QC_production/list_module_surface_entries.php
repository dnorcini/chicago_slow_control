<?php
  // list_entries.php
  // D. Norcini, Hopkins, 2024
  
session_start();
$req_priv = "basic";
include("db_login.php");
include("page_setup.php");
include("aux/make_data_plot.php");

echo ('<TABLE border="1" cellpadding="2" width=100%>');

$plot_type_array = array(
    "Summary",
    "MODULE_SURFACEs",
);

if (empty($_SESSION['choose_type'])) {
    $_SESSION['choose_type'] = $plot_type_array[0];
}

if (!(empty($_POST['choose_type']))) {
    $_SESSION['choose_type'] = $_POST['choose_type'];
}

echo ('<TABLE border="1" cellpadding="2" width=100%>');
foreach ($plot_type_array as $index) {
    echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
    echo ('<TH>');
    echo ('<input type="submit" name="choose_type" value="'.$index.'" 
               title="'.$index.'" style="font-size: 10pt">');
    echo ('</TH>');
}
echo ('</TABLE>');

echo ('<TABLE border="1" cellpadding="2" width=100%>');
if ($_SESSION['choose_type'] == "Summary") { 
    include("list_module_surface_entries_summary.php");
}
else if ($_SESSION['choose_type'] == "MODULE_SURFACEs") {
    $temp = $_SESSION['choosen_module_surface'];

    if (isset($_POST['go'])) {
        $_SESSION['req_id'] = $_POST['go'];
        header("Location: edit_module_surface.php");
    }

    echo ('<TR>');
    echo ('<TH align="left">MODULE_SURFACE ID</TH>');
    echo ('<TH align="left">Pitch Adaptor ID </TH>');
    echo ('<TH align="left">ACM</TH>');
    echo ('<TH align="left">Test Date</TH>');
    echo ('<TH align="left">Module Name</TH>');
    echo ('<TH align="left">A</TH>');
    echo ('<TH align="left">B</TH>');
    echo ('<TH align="left">C</TH>');
    echo ('<TH align="left">D</TH>');
    echo ('<TH align="left">Grade</TH>');
    echo ('</TR>');

    $table = "MODULE_SURFACE";
    include("aux/get_last_table_id.php");

    for ($i=1; $i <= $last_id; $i++) { 
        $_SESSION['choosen_module_surface'] = $i;

        // Get selected module_surface values:
        include("aux/get_module_surface_vals.php");

        // Logic to calculate tallies based on grades
        $tallies = "";

        if (!empty($grade_A) && !empty($grade_B) && !empty($grade_C) && !empty($grade_D)) {
            if ($grade_A == "Science" && $grade_B == "Science" && $grade_C == "Science" && $grade_D == "Science") {
                $tallies = "Charizard";
            }
            elseif (($grade_A == "Science" && $grade_B == "Science") || ($grade_C == "Science" && $grade_D == "Science")) {
                $tallies = "Charmeleon";
            }
            elseif ($grade_A == "Science" || $grade_B == "Science" || $grade_C == "Science" || $grade_D == "Science") {
                $tallies = "Charmander";
            }
            else {
                $tallies = "Geodude";
            }
        }

        // Determine the background color based on $tallies value
        $bg_color = "";
        if ($tallies == "Geodude") {
            $bg_color = "background-color: gray;";
        } elseif ($tallies == "Charmander") {
            $bg_color = "background-color: yellow;";
        } elseif ($tallies == "Charmeleon") {
            $bg_color = "background-color: orange;";
        } elseif ($tallies == "Charizard") {
            $bg_color = "background-color: red;";
        }

	// Check if any reviewer grade is empty; if so, set text color to red
        $text_color = "";
        if (empty($check_A) || empty($check_B) || empty($check_C) || empty($check_D)) {
            $text_color = "color: red;";
        }
	
        echo ('<TR style="'.$text_color.'">'); // Apply red text color if any reviewer grade is unchecked
        echo ('<TD align="left">'); 
        echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
        echo ('<input type="submit" name="go" value="'.$id.'" title="Goto MODULE_SURFACE ID '.$id.'" style="font-size: 14pt">');
        echo ('</FORM>');
        echo ('</TD>');

        echo ('<TD align="left">' . htmlspecialchars($pitch_adaptor_id) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($ACM) . '</TD>');    
        echo ('<TD align="left">' . htmlspecialchars($test_date) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($name) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($grade_A) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($grade_B) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($grade_C) . '</TD>');
	echo ('<TD align="left">' . htmlspecialchars($grade_D) . '</TD>');
        echo ('<TD align="left" style="'.$bg_color.'">' . htmlspecialchars($tallies) . '</TD>');
        echo ('</TR>');
    }

    $_SESSION['choosen_module_surface'] = $temp;

    echo ('</TABLE>');
}

mysql_close($connection);
echo(' </body>');
echo ('</HTML>');
?>
