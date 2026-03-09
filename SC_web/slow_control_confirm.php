<?php
session_start();
$req_priv = "full";

include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_sensor_info.php");

// Validate input early
if (!isset($_POST['set_sens_name'], $_POST['new_set_val'])) {
    die("Invalid request.");
}

$sensor = $_POST['set_sens_name'];
$newval = $_POST['new_set_val'];

// Check access again (never trust the previous page)
if (!check_access($_SESSION['privileges'], $sensor_ctrl_priv[$sensor], $allowed_host_array)) {
    die("Access denied.");
}

// Human-readable label
$label = $sensor_descs[$sensor];

// Optional: pretty value display
$display_val = $newval;
if (strncmp($sensor_units[$sensor], "discrete", 8) == 0) {
    $all_vals = explode(";", $sensor_discrete_vals[$sensor]);
    $vals = array_combine(
        explode(":", $all_vals[0]),
        explode(":", $all_vals[1])
    );
    if (isset($vals[$newval])) {
        $display_val = $vals[$newval];
    }
}

echo "<h2>Confirm sensor change</h2>";
echo "<p><b>Sensor:</b> {$label}</p>";
echo "<p><b>New value:</b> {$display_val}</p>";

echo <<<HTML
<p style="color:red">
⚠ This will immediately change the hardware state.
</p>

<form method="post" action="slow_control_apply.php">
  <p>
    Type <b>yes, i do</b> to confirm:
    <input type="text" name="confirm_phrase">
  </p>

  <input type="hidden" name="set_sens_name" value="{$sensor}">
  <input type="hidden" name="new_set_val" value="{$newval}">

  <input type="submit" value="Confirm">
  <a href="slow_control_text.php">Cancel</a>
</form>
HTML;
?>