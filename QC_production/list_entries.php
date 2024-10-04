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
    "DIEs",
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
    include("list_entries_summary.php");
}
else if ($_SESSION['choose_type'] == "DIEs") {
    $temp = $_SESSION['choosen_die'];

    if (isset($_POST['go'])) {
        $_SESSION['req_id'] = $_POST['go'];
        header("Location: edit_die.php");
    }

    echo ('<TR>');
    echo ('<TH align="left">DIE ID</TH>');
    echo ('<TH align="left">Wafer ID </TH>');
    echo ('<TH align="left">Wafer Position</TH>');
    echo ('<TH align="left">ACM</TH>');
    echo ('<TH align="left">Test Date</TH>');
    echo ('<TH align="left">CCD Name</TH>');
    echo ('<TH align="left">L1</TH>');
    echo ('<TH align="left">L2</TH>');
    echo ('<TH align="left">U1</TH>');
    echo ('<TH align="left">U2</TH>');
    echo ('<TH align="left">Grade</TH>');
    echo ('</TR>');

    $table = "DIE";
    include("aux/get_last_table_id.php");

    for ($i=1; $i <= $last_id; $i++) { 
        $_SESSION['choosen_die'] = $i;

        // Get selected die values:
        include("aux/get_die_vals.php");

        // Logic to calculate tallies based on grades
        $tallies = "";

        if (!empty($grade_L1) && !empty($grade_L2) && !empty($grade_U1) && !empty($grade_U2)) {
            if ($grade_L1 == "Science" && $grade_L2 == "Science" && $grade_U1 == "Science" && $grade_U2 == "Science") {
                $tallies = "Charizard";
            }
            elseif (($grade_L1 == "Science" && $grade_L2 == "Science") || ($grade_U1 == "Science" && $grade_U2 == "Science")) {
                $tallies = "Charmeleon";
            }
            elseif ($grade_L1 == "Science" || $grade_L2 == "Science" || $grade_U1 == "Science" || $grade_U2 == "Science") {
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
        if (empty($check_L1) || empty($check_L2) || empty($check_U1) || empty($check_U2)) {
            $text_color = "color: red;";
        }
	
        echo ('<TR style="'.$text_color.'">'); // Apply red text color if any reviewer grade is unchecked
        echo ('<TD align="left">'); 
        echo ('<FORM action="'.$_SERVER['PHP_SELF'].'" method="post">');
        echo ('<input type="submit" name="go" value="'.$id.'" title="Goto DIE ID '.$id.'" style="font-size: 14pt">');
        echo ('</FORM>');
        echo ('</TD>');

        echo ('<TD align="left">' . htmlspecialchars($wafer_id) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($wafer_position) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($ACM) . '</TD>');    
        echo ('<TD align="left">' . htmlspecialchars($test_date) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($name) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($grade_L1) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($grade_L2) . '</TD>');
        echo ('<TD align="left">' . htmlspecialchars($grade_U1) . '</TD>');
	echo ('<TD align="left">' . htmlspecialchars($grade_U2) . '</TD>');
        echo ('<TD align="left" style="'.$bg_color.'">' . htmlspecialchars($tallies) . '</TD>');
        echo ('</TR>');
    }

    $_SESSION['choosen_die'] = $temp;

    echo ('</TABLE>');
}

mysql_close($connection);
echo(' </body>');
echo ('</HTML>');
?>
