<?php
// --------------------
// HANDLE POST: Add a new assay (creates detail table)
// --------------------
if (!empty($_POST['assay_action']) && $_POST['assay_action'] === "add_assay") {
    // Read form fields from $_POST and trim whitespace
    $material = trim($_POST['assay_material'] ?? "");
    $manufacturer = trim($_POST['assay_manufacturer'] ?? "");
    $description = trim($_POST['assay_description'] ?? "");
    $liaison = trim($_POST['assay_liaison'] ?? "");
    $date = trim($_POST['assay_date'] ?? "");
    $remarks = trim($_POST['assay_remarks'] ?? "");
    $docdb = trim($_POST['assay_docdb'] ?? "");

    // SQL-injection protection, Example: O'Reilly becomes O\'Reilly
    $material_esc = mysql_real_escape_string($material);
    $type_esc = mysql_real_escape_string($type);
    $manufacturer_esc = mysql_real_escape_string($manufacturer);
    $description_esc = mysql_real_escape_string($description);
    $liaison_esc = mysql_real_escape_string($liaison);
    $remarks_esc = mysql_real_escape_string($remarks);

    $date_sql = "NULL";
    if ($date !== "") {
        $date_esc = mysql_real_escape_string($date);
        $date_sql = "'" . $date_esc . "'";
    }

    $docdb_sql = "NULL";
    if ($docdb !== "") {
        $docdb_esc = mysql_real_escape_string($docdb);
        $docdb_sql = "'" . $docdb_esc . "'";
    }

    if ($material_esc === "") {
        echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">Material is required to add a new assay.</div>';
    } else {
        // add a new entry
        $q = "
INSERT INTO `$main_table`
(`Material`,`Type`,`Manufacturer`,`Description`,`Liaison`,`Date`,`Remarks`,`Docdb`)
VALUES
('$material_esc','$type_esc','$manufacturer_esc','$description_esc','$liaison_esc',$date_sql,'$remarks_esc',$docdb_sql)
";
        $r = mysql_query($q); //run
        if (!$r) die("Could not add assay: " . mysql_error() . "<BR>" . h($q)); //print out the sql error message
        // Get the newly-created row’s ID
        $new_id = (int)mysql_insert_id();
        // Compute the “detail table” name for this assay, then ensure it exists
        $detail_table = detail_table_name_from_row($new_id, $material);
        ensure_detail_table($detail_table);
        // Success message to the user
        echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">Added new assay ID ' . h($new_id) . ' and created detail table ' . h($detail_table) . '.</div>';
    }
}

// --------------------
// HANDLE POST: Update assay row (main table)
// --------------------
if (!empty($_POST['assay_action']) && $_POST['assay_action'] === "update_assay" && isset($_POST['assay_id'])) {

    $id = (int)$_POST['assay_id'];

    $material = post_esc("assay_material");
    $type = post_esc("assay_type");
    $manufacturer = post_esc("assay_manufacturer");
    $description = post_esc("assay_description");
    $liaison = post_esc("assay_liaison");
    $remarks = post_esc("assay_remarks");

    // Date
    $date_sql = "NULL";
    if (isset($_POST['assay_date']) && trim($_POST['assay_date']) !== "") {
        $date_sql = "'" . mysql_real_escape_string($_POST['assay_date']) . "'";
    }
    // Docdb (text: comma/space-separated IDs)
    $docdb_sql = "NULL";
    if (isset($_POST['assay_docdb']) && trim($_POST['assay_docdb']) !== "") {
        $docdb_esc = mysql_real_escape_string(trim($_POST['assay_docdb']));
        $docdb_sql = "'" . $docdb_esc . "'";
    }

    $finished = isset($_POST['assay_finished']) ? 1 : 0;

    $q = "
UPDATE `$main_table` SET
`Material` = '$material',
`Type` = '$type',
`Manufacturer` = '$manufacturer',
`Description` = '$description',
`Liaison` = '$liaison',
`Date` = $date_sql,
`Remarks` = '$remarks',
`Docdb` = $docdb_sql,
`Finished` = $finished
WHERE `ID` = $id
LIMIT 1
";
    $r = mysql_query($q);
    if (!$r) die("Could not update assay: " . mysql_error() . "<BR>" . h($q));

    // Make sure the detail table exists (in case it was deleted)
    $detail_table = detail_table_name_from_row($id, $material);
    ensure_detail_table($detail_table);

    // Upload any attached files submitted with this form
    if (!empty($_FILES['assay_files']['name'][0])) {
        $upload_dir = "/var/www/html/QC_production/uploads/edit_assay/";
        $max_bytes = 30 * 1024 * 1024;
        $names = $_FILES['assay_files']['name'];
        $tmps  = $_FILES['assay_files']['tmp_name'];
        $errs  = $_FILES['assay_files']['error'];
        $sizes = $_FILES['assay_files']['size'];
        $types = $_FILES['assay_files']['type'];

        for ($i = 0; $i < count($names); $i++) {
            if ($names[$i] === '' || (int)$errs[$i] === UPLOAD_ERR_NO_FILE) continue;
            if ((int)$errs[$i] !== UPLOAD_ERR_OK || (int)$sizes[$i] > $max_bytes) continue;
            if (!is_uploaded_file($tmps[$i])) continue;

            $ext = strtolower(pathinfo($names[$i], PATHINFO_EXTENSION));
            $ext = preg_replace('/[^a-z0-9]/', '', $ext) ?: 'bin';
            $stored = "assay_" . $id . "_" . time() . "_" . mt_rand(10000, 99999) . "." . $ext;

            if (!move_uploaded_file($tmps[$i], $upload_dir . $stored)) continue;

            $orig_esc   = mysql_real_escape_string($names[$i]);
            $stored_esc = mysql_real_escape_string($stored);
            $ext_esc    = mysql_real_escape_string($ext);
            $mime_esc   = mysql_real_escape_string($types[$i]);
            $size_int   = (int)$sizes[$i];

            $q = "INSERT INTO `assay_files` (`Assay_ID`,`Orig_Name`,`Stored_Name`,`Ext`,`Mime`,`Size_Bytes`,`Uploaded_At`)
                  VALUES ($id,'$orig_esc','$stored_esc','$ext_esc','$mime_esc',$size_int," . time() . ")";
            $r = mysql_query($q);
            if (!$r) { @unlink($upload_dir . $stored); die("Could not insert assay_files: " . mysql_error()); }
        }
    }
}

// --------------------
// HANDLE POST: Delete assay row (optional; DOES NOT drop detail table by default)
// --------------------
if (!empty($_POST['assay_action']) && $_POST['assay_action'] === "delete_assay" && isset($_POST['assay_id'])) {

    $id = (int)$_POST['assay_id'];
    $upload_dir = "/var/www/html/QC_production/uploads/edit_assay/";

    // Delete uploaded files from disk and assay_files table
    $qf = "SELECT `Stored_Name` FROM `assay_files` WHERE `Assay_ID` = $id";
    $rf = mysql_query($qf);
    if (!$rf) die("Could not query assay_files: " . mysql_error() . "<BR>" . h($qf));
    while ($f = mysql_fetch_assoc($rf)) {
        $stored = (string)$f['Stored_Name'];
        if (preg_match('/^assay_\d+_\d+_\d+\.[A-Za-z0-9]{1,20}$/', $stored)) {
            @unlink($upload_dir . $stored);
        }
    }
    $qfd = "DELETE FROM `assay_files` WHERE `Assay_ID` = $id";
    $rfd = mysql_query($qfd);
    if (!$rfd) die("Could not delete assay_files: " . mysql_error() . "<BR>" . h($qfd));

    // Delete main assay row
    $q = "DELETE FROM `$main_table` WHERE `ID` = $id LIMIT 1";
    $r = mysql_query($q);
    if (!$r) die("Could not delete assay: " . mysql_error() . "<BR>" . h($q));

    // Drop detail table
    $detail_table = detail_table_name_from_row($id, "");
    $detail_table_esc = mysql_real_escape_string($detail_table);
    $qd = "DROP TABLE IF EXISTS `{$detail_table_esc}`";
    $rd = mysql_query($qd);
    if (!$rd) die("Could not drop detail table: " . mysql_error() . "<BR>" . h($qd));
}

// --------------------
// HANDLE POST: Detail row add/update/delete
// --------------------
if (!empty($_POST['detail_action']) && !empty($_POST['detail_table_name'])) {

    $detail_table = post_esc("detail_table_name");

    // Allowlist check done inside ensure_detail_table()
    ensure_detail_table($detail_table);

    $action = $_POST['detail_action'];

    $nu1  = post_esc("detail_nuclide_1");
    $type = post_esc("detail_type");
    $note = post_esc("detail_note");

    // Unit (shared for Result and Uncertainty); default to Bq/kg
    $unit = post_esc("detail_result_unit");
    if ($unit === '') $unit = 'Bq/kg';

    // Result (raw)
    $res_sql = "NULL";
    if (isset($_POST['detail_result']) && trim($_POST['detail_result']) !== "") {
        $res_sql = (0.0 + $_POST['detail_result']);
    }

    // Uncertainty (raw)
    $unc_sql = "NULL";
    if (isset($_POST['detail_uncertainty']) && trim($_POST['detail_uncertainty']) !== "") {
        $unc_sql = (0.0 + $_POST['detail_uncertainty']);
    }

    // Result converted to Bq/kg (manual)
    $ribk_sql = "NULL";
    if (isset($_POST['detail_result_in_bqkg']) && trim($_POST['detail_result_in_bqkg']) !== "") {
        $ribk_sql = (0.0 + $_POST['detail_result_in_bqkg']);
    }

    // Uncertainty converted to Bq/kg (manual)
    $uibk_sql = "NULL";
    if (isset($_POST['detail_uncertainty_in_bqkg']) && trim($_POST['detail_uncertainty_in_bqkg']) !== "") {
        $uibk_sql = (0.0 + $_POST['detail_uncertainty_in_bqkg']);
    }

    if ($action === "update" && $type === "Upper Limit" && $unc_sql === "NULL") {
        // Keep existing uncertainty in DB when type is Upper Limit and none submitted
        $did = (int)$_POST['detail_id'];
        $qr = mysql_query("SELECT `Uncertainty` FROM `{$detail_table}` WHERE `ID` = $did LIMIT 1");
        if ($qr && mysql_num_rows($qr) === 1) {
            $row = mysql_fetch_assoc($qr);
            if (array_key_exists('Uncertainty', $row) && $row['Uncertainty'] !== null && $row['Uncertainty'] !== '') {
                $unc_sql = (0.0 + $row['Uncertainty']);
            }
        }
    }

    if ($action === "add") {
        $q = "
INSERT INTO `{$detail_table}`
(`Nuclide_1`,`Type`,`Result`,`Uncertainty`,`Result_Unit`,`Result_in_BqKg`,`Uncertainty_in_BqKg`,`Note`)
VALUES
('$nu1','$type',$res_sql,$unc_sql,'$unit',$ribk_sql,$uibk_sql,'$note')
";
        $r = mysql_query($q);
        if (!$r) die("Could not add detail row: " . mysql_error() . "<BR>" . h($q));
    }

    if ($action === "update") {
        if (!isset($_POST['detail_id'])) die("Missing detail_id for update.");
        $did = (int)$_POST['detail_id'];
        $used = isset($_POST['detail_used_in_simulation']) ? 1 : 0;

        $q = "
UPDATE `{$detail_table}` SET
`Nuclide_1` = '$nu1',
`Type` = '$type',
`Result` = $res_sql,
`Uncertainty` = $unc_sql,
`Result_Unit` = '$unit',
`Result_in_BqKg` = $ribk_sql,
`Uncertainty_in_BqKg` = $uibk_sql,
`Used_in_simulation` = " . intval($used) . ",
`Note` = '$note'
WHERE `ID` = $did
LIMIT 1
";
        $r = mysql_query($q);
        if (!$r) die("Could not update detail row: " . mysql_error() . "<BR>" . h($q));
    }

    if ($action === "delete") {
        if (!isset($_POST['detail_id'])) die("Missing detail_id for delete.");
        $did = (int)$_POST['detail_id'];

        $q = "DELETE FROM `{$detail_table}` WHERE `ID` = $did LIMIT 1";
        $r = mysql_query($q);
        if (!$r) die("Could not delete detail row: " . mysql_error() . "<BR>" . h($q));
    }
}
// --------------------
// HANDLE POST: Delete an uploaded file
// --------------------
if (
    !empty($_POST['assay_action']) && $_POST['assay_action'] === "delete_file"
    && isset($_POST['assay_id']) && isset($_POST['file_id'])
) {
    $assay_id = (int)$_POST['assay_id'];
    $file_id  = (int)$_POST['file_id'];

    $q = "SELECT `ID`,`Assay_ID`,`Stored_Name`,`Orig_Name`
          FROM `assay_files`
          WHERE `ID` = $file_id AND `Assay_ID` = $assay_id
          LIMIT 1";
    $r = mysql_query($q);
    if (!$r) die("Could not query assay_files: " . mysql_error() . "<BR>" . h($q));

    if (mysql_num_rows($r) !== 1) {
        echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">File not found for this assay.</div>';
    } else {
        $f = mysql_fetch_assoc($r);
        $upload_dir = "/var/www/html/QC_production/uploads/edit_assay/";
        $stored = (string)$f['Stored_Name'];

        if (!preg_match('/^assay_\d+_\d+_\d+\.[A-Za-z0-9]{1,20}$/', $stored)) {
            die("Refusing to delete: invalid stored filename.");
        }

        $path = $upload_dir . $stored;
        if (is_file($path)) {
            if (!unlink($path)) {
                echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">Could not delete file on disk. DB row not removed.</div>';
            } else {
                $qd = "DELETE FROM `assay_files` WHERE `ID` = $file_id LIMIT 1";
                $rd = mysql_query($qd);
                if (!$rd) die("Could not delete assay_files row: " . mysql_error() . "<BR>" . h($qd));
                echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">Deleted file: ' . h($f['Orig_Name']) . '</div>';
            }
        } else {
            $qd = "DELETE FROM `assay_files` WHERE `ID` = $file_id LIMIT 1";
            $rd = mysql_query($qd);
            if (!$rd) die("Could not delete assay_files row: " . mysql_error() . "<BR>" . h($qd));
            echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">File missing on disk; removed DB record.</div>';
        }
    }
}
