<?php
// slow_control_stderr.php
// View stdout and stderr of backend instruments from /dev/shm (last N lines).
// Linked from Config page per instrument; user can switch instrument and request more lines.
session_start();
$req_priv = "config";
$never_ref = 1;

include("db_login.php");
include("slow_control_page_setup.php");
include("aux/get_inst_info.php");
require_once(__DIR__ . "/aux/inst_log_lib.php");

$lines_options = array(
    "Last 20"  => 20,
    "Last 50"  => 50,
    "Last 100"  => 100,
    "Last 500"  => 500,
    "Last 1000" => 1000,
    "Last 5000" => 5000,
);

if (!isset($inst_names) || !is_array($inst_names)) {
    $inst_names = array();
}

$num_lines = isset($_GET['lines']) ? (int)$_GET['lines'] : null;
if ($num_lines === null && isset($_SESSION['stderr_lines'])) {
    $num_lines = (int)$_SESSION['stderr_lines'];
}
if ($num_lines === null || $num_lines < 1) $num_lines = 100;
$num_lines = min(max(1, $num_lines), 5000);
$_SESSION['stderr_lines'] = $num_lines;

$current_inst = null;
if (!empty($_GET['inst']) && in_array($_GET['inst'], $inst_names, true)) {
    $current_inst = $_GET['inst'];
}
if ($current_inst === null && !empty($_SESSION['stderr_inst']) && in_array($_SESSION['stderr_inst'], $inst_names, true)) {
    $current_inst = $_SESSION['stderr_inst'];
}
if ($current_inst === null && count($inst_names) > 0) {
    $current_inst = $inst_names[0];
}
if ($current_inst !== null) {
    $_SESSION['stderr_inst'] = $current_inst;
}

echo '<div style="max-width:1200px;margin:0 auto;">';
echo '<TABLE border="1" cellpadding="4" width="100%">';
echo '<TR><TD colspan="2">';

echo '<FORM action="slow_control_stderr.php" method="get">';
echo 'Instrument: <select name="inst">';
foreach ($inst_names as $name) {
    echo '<option value="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"';
    if ($name === $current_inst) echo ' selected="selected"';
    echo '>' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '</option>';
}
echo '</select> ';
echo 'Lines: <select name="lines">';
foreach ($lines_options as $label => $val) {
    echo '<option value="' . (int)$val . '"';
    if ($val === $num_lines) echo ' selected="selected"';
    echo '>' . $label . '</option>';
}
echo '</select> ';
echo '<input type="submit" value="Show">';
echo '</FORM>';

echo '</TD></TR>';

if ($current_inst !== null) {
    $r1 = sc_read_inst_log($current_inst, 'stdout', $num_lines, $inst_names);
$stdout_content = $r1['content'];
$stdout_error   = $r1['error'];

$r2 = sc_read_inst_log($current_inst, 'stderr', $num_lines, $inst_names);
$stderr_content = $r2['content'];
$stderr_error   = $r2['error'];
    echo '<TR><TD colspan="2"><strong>Instrument: ' . htmlspecialchars($current_inst, ENT_QUOTES, 'UTF-8') . '</strong></TD></TR>';

    echo '<TR><TD width="50%" valign="top"><strong>stdout</strong> (/dev/shm/stdout.' . htmlspecialchars($current_inst, ENT_QUOTES, 'UTF-8') . ')</TD>';
    echo '<TD width="50%" valign="top"><strong>stderr</strong> (/dev/shm/stderr.' . htmlspecialchars($current_inst, ENT_QUOTES, 'UTF-8') . ')</TD></TR>';

    echo '<TR><TD valign="top"><pre style="overflow-x:auto; white-space:pre-wrap; margin:0; border:1px solid #ccc; padding:6px; max-height:50em; overflow-y:auto;">';
    if ($stdout_error !== null) {
        echo htmlspecialchars($stdout_error, ENT_QUOTES, 'UTF-8');
    } elseif ($stdout_content === '') {
        echo '(empty)';
    } else {
        echo htmlspecialchars($stdout_content, ENT_QUOTES, 'UTF-8');
    }
    echo '</pre></TD>';
    echo '<TD valign="top"><pre style="overflow-x:auto; white-space:pre-wrap; margin:0; border:1px solid #ccc; padding:6px; max-height:50em; overflow-y:auto;">';
    if ($stderr_error !== null) {
        echo htmlspecialchars($stderr_error, ENT_QUOTES, 'UTF-8');
    } elseif ($stderr_content === '') {
        echo '(empty)';
    } else {
        echo htmlspecialchars($stderr_content, ENT_QUOTES, 'UTF-8');
    }
    echo '</pre></TD></TR>';
} else {
    echo '<TR><TD colspan="2">No instruments defined. Add instruments in Config.</TD></TR>';
}

echo '</TABLE>';
echo '</div>';

echo '</body>';
echo '</html>';
