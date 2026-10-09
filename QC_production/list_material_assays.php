<?php
require __DIR__ . '/bootstrap.php';
require_priv('basic');
include("db_login.php");          // the old mysql_* connection the assay code runs on
page_start();
include("lib/assay_helpers.php");

mysql_select_db('assay_qc');

$qm = "SELECT `ID`, `Material`, `Type`, `Finished` FROM `assay_results` ORDER BY `ID` ASC";
$rm = mysql_query($qm);
if (!$rm) die("Could not query assay_results: " . mysql_error());

echo '<table border="1" cellpadding="4" width="100%" style="border-collapse:collapse;">';
echo '<tr style="background:#ddd;">';
echo '<th align="left">Material</th>';
echo '<th align="left">Type</th>';
echo '<th align="center">Finished</th>';
echo '<th align="left">Nuclide Activities</th>';
echo '<th align="center">Details</th>';
echo '</tr>';

while ($row = mysql_fetch_assoc($rm)) {
    $assay_id    = (int)$row['ID'];
    $detail_table = detail_table_name_from_row($assay_id, $row['Material']);

    // Check table exists before querying
    $t_esc = mysql_real_escape_string($detail_table);
    $exists_r = mysql_query("SHOW TABLES LIKE '$t_esc'");
    $has_table = ($exists_r && mysql_num_rows($exists_r) > 0);

    // Fetch nuclide rows
    $nuclide_html = '<span style="color:#aaa;">(none)</span>';
    if ($has_table) {
        $qd = "SELECT `Nuclide_1`, `Type`, `Result`, `Uncertainty`, `Result_Unit` FROM `{$detail_table}` ORDER BY `ID` ASC";
        $rd = mysql_query($qd);
        if ($rd && mysql_num_rows($rd) > 0) {
            $chips = [];
            while ($d = mysql_fetch_assoc($rd)) {
                $nuc = h($d['Nuclide_1']);
                $res = h(fmt_sci($d['Result'], 2));
                if ($d['Type'] === 'Upper Limit') {
                    $val = '&lt;' . $res;
                } else {
                    $unc = h(fmt_sci($d['Uncertainty'], 2));
                    $val = $res . ($unc !== '' ? '&plusmn;' . $unc : '');
                }
                $unit_label = (!empty($d['Result_Unit'])) ? $d['Result_Unit'] : 'Bq/kg';
                $chips[] = '<span style="display:inline-block; white-space:nowrap; margin:1px 6px 1px 0; font-size:0.85em;">'
                    . '<b>' . $nuc . '</b>:&nbsp;<span style="font-family:monospace;">' . $val . '</span>'
                    . '&nbsp;<span style="color:#999;">' . h($unit_label) . '</span></span>';
            }
            $nuclide_html = implode('', $chips);
        }
    }

    $finished = (!empty($row['Finished']) && $row['Finished'] == 1);
    $status_html = $finished
        ? '<span style="color:green; font-size:1.2em;">&#10003;</span>'
        : '<span style="color:#bbb; font-size:1.2em;">&#10007;</span>';

    $bg = $finished ? '' : ' style="background:#fffbe6;"';

    echo '<tr' . $bg . '>';
    echo '<td><b>' . h($row['Material']) . '</b></td>';
    echo '<td>' . h($row['Type']) . '</td>';
    echo '<td align="center">' . $status_html . '</td>';
    echo '<td>' . $nuclide_html . '</td>';
    echo '<td align="center"><a href="edit_materials.php#assay-' . $assay_id . '">Edit</a></td>';
    echo '</tr>';
}

echo '</table>';

page_end();
