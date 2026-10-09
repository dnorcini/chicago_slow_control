<?php
// lib/assay_helpers.php
// Helpers of the assay pages (list_material_assays.php, edit_materials.php).
// h() is in lib/html.php (loaded by bootstrap.php)
function post_esc($key)
{
    return mysql_real_escape_string($_POST[$key] ?? "");
}
// Recommended: stable per-assay table name
function detail_table_name_from_row($assay_id, $material)
{
    $id = (int)$assay_id;
    return "assay_results_" . $id; // stable even if Material changes
}


function ensure_detail_table($table_name)
{
    $table_name_esc = mysql_real_escape_string($table_name);

    // Allowlist: only [a-z0-9_] and must start with assay_results_
    if (!preg_match('/^assay_results_[a-z0-9_]+$/', $table_name_esc)) {
        die("Invalid detail table name.");
    }

    $q = "
      CREATE TABLE IF NOT EXISTS `{$table_name_esc}` (
        `ID` INT(11) NOT NULL AUTO_INCREMENT,
        `Nuclide_1` VARCHAR(8) DEFAULT NULL,
        `Type` VARCHAR(20) DEFAULT NULL,
        `Result` DOUBLE DEFAULT NULL,
        `Result_Unit` VARCHAR(20) NOT NULL DEFAULT 'Bq/kg',
        `Result_in_BqKg` DOUBLE DEFAULT NULL,
        `Uncertainty` DOUBLE DEFAULT NULL,
        `Uncertainty_in_BqKg` DOUBLE DEFAULT NULL,
        `Used_in_simulation` TINYINT(1) DEFAULT 0,
        `Note` TEXT,
        PRIMARY KEY (`ID`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1
    ";
    $r = mysql_query($q);
    if (!$r) die("Could not create detail table: " . mysql_error() . "<BR>" . h($q));

    // Migrate existing tables that predate these columns
    $cols_to_add = [
        'Result_Unit'         => "VARCHAR(20) NOT NULL DEFAULT 'Bq/kg'",
        'Result_in_BqKg'      => 'DOUBLE DEFAULT NULL',
        'Uncertainty_in_BqKg' => 'DOUBLE DEFAULT NULL',
    ];
    foreach ($cols_to_add as $col => $def) {
        $col_esc = mysql_real_escape_string($col);
        $chk = mysql_query("SELECT COUNT(*) AS n FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = '{$table_name_esc}'
            AND COLUMN_NAME = '{$col_esc}'");
        if ($chk) {
            $crow = mysql_fetch_assoc($chk);
            if ((int)$crow['n'] === 0) {
                mysql_query("ALTER TABLE `{$table_name_esc}` ADD COLUMN `{$col_esc}` {$def}");
            }
        }
    }
}

function fmt_sci($v, $precision = 3)
{
    if ($v === null || $v === '') return '';
    $v = (float)$v;
    if ($v == 0.0) return '0';
    return sprintf('%.' . $precision . 'e', $v);
}
