<?php
// ccd_summary.php
// Hub page linking to all QC tables and detail pages.

session_start();
$req_priv = "basic";
include("db_login.php");
include("page_setup.php");

$sections = [
    [
        "label"       => "Pre-Production CCD",
        "list_label"  => "Pre-Production List",
        "list_url"    => "list_ccd_entries.php",
        "list_priv"   => "basic",
        "detail_label" => "Pre-Production Details",
        "detail_url"  => "edit_ccd.php",
        "detail_priv" => "full",
    ],
    [
        "label"       => "DAMIC-M Die",
        "list_label"  => "Die List",
        "list_url"    => "list_entries.php",
        "list_priv"   => "basic",
        "detail_label" => "Die Details",
        "detail_url"  => "edit_die.php",
        "detail_priv" => "full",
    ],
    [
        "label"       => "Module Surface",
        "list_label"  => "Module Surface List",
        "list_url"    => "list_module_surface_entries.php",
        "list_priv"   => "basic",
        "detail_label" => "Module Surface Details",
        "detail_url"  => "edit_module_surface.php",
        "detail_priv" => "full",
    ],
    [
        "label"       => "Module Underground",
        "list_label"  => "Module Underground List",
        "list_url"    => "list_module_underground_entries.php",
        "list_priv"   => "basic",
        "detail_label" => "Module Underground Details",
        "detail_url"  => "edit_module_underground.php",
        "detail_priv" => "full",
    ],
];

echo ('<br>');
echo ('<h2>CCD Quality Control</h2>');
echo ('<table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:70%;">');
echo ('<tr style="background-color:#ddd;">');
echo ('<th align="left" width="25%">Table</th>');
echo ('<th align="left" width="37%">Summary / List</th>');
echo ('<th align="left" width="38%">Detail</th>');
echo ('</tr>');

foreach ($sections as $s) {
    echo ('<tr>');
    echo ('<td><strong>' . $s['label'] . '</strong></td>');

    // Summary / List column
    echo ('<td>');
    if ($s['list_url'] && check_access($_SESSION['privileges'], $s['list_priv'], $allowed_host_array))
        echo ('<a href="' . $s['list_url'] . '">' . $s['list_label'] . '</a>');
    else
        echo ('&mdash;');
    echo ('</td>');

    // Detail column
    echo ('<td>');
    if ($s['detail_url'] && check_access($_SESSION['privileges'], $s['detail_priv'], $allowed_host_array))
        echo ('<a href="' . $s['detail_url'] . '">' . $s['detail_label'] . '</a>');
    else
        echo ('&mdash;');
    echo ('</td>');

    echo ('</tr>');
}

echo ('</table>');
echo ('<br>');

mysql_close($connection);
echo ('</body>');
echo ('</html>');
