<?php
// Main table: assay_qc.assay_results
// Detail tables: assay_qc.assay_results_<id>  (recommended: stable & unique)

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require __DIR__ . '/bootstrap.php';
require_priv('full');
include("db_login.php");          // the old mysql_* connection the assay code runs on
page_start();
include("lib/assay_helpers.php");
include("views/table_navigation.php");

// Uncertainty: submitted value is in hidden input detail_uncertainty_ROWID; cell is display only
echo '<script>
function toggleUnc(cellId, val, rowId) {
  var inp = document.getElementById("detail_uncertainty_" + rowId);
  var cell = document.getElementById(cellId);
  if (!inp || !cell) return;
  if (val === "Upper Limit") {
    inp.style.display = "none";
    if (!cell.querySelector(".unc-na")) {
      var s = document.createElement("span");
      s.className = "unc-na";
      s.style.color = "#777";
      s.textContent = "n/a";
      cell.appendChild(s);
    }
  } else {
    inp.style.display = "";
    var na = cell.querySelector(".unc-na");
    if (na) na.parentNode.removeChild(na);
  }
}

function updateBqKgCols(tblId) {
  var tbl = document.getElementById(tblId);
  if (!tbl) return;
  var unitInputs = tbl.querySelectorAll("input[name=\"detail_result_unit\"]");
  var anyNonBqKg = false;
  for (var i = 0; i < unitInputs.length; i++) {
    if (unitInputs[i].value.trim() !== "Bq/kg") { anyNonBqKg = true; break; }
  }
  var cells = tbl.querySelectorAll(".col-bqkg");
  for (var j = 0; j < cells.length; j++) {
    cells[j].style.display = anyNonBqKg ? "" : "none";
  }
}

function syncBqKg(rowId) {
  var unitEl  = document.getElementById("detail_result_unit_"  + rowId);
  var resEl   = document.getElementById("detail_result_"       + rowId);
  var ribkEl  = document.getElementById("detail_result_bqkg_"  + rowId);
  var uibkEl  = document.getElementById("detail_unc_bqkg_"     + rowId);
  var uncEl   = document.getElementById("detail_uncertainty_"  + rowId);
  if (!unitEl || !ribkEl || !uibkEl) return;

  var isBqKg = (unitEl.value.trim() === "Bq/kg");
  if (isBqKg) {
    if (resEl) ribkEl.value = resEl.value;
    if (uncEl) uibkEl.value = uncEl.value;
    ribkEl.dataset.autoSync = "1";
    uibkEl.dataset.autoSync = "1";
    ribkEl.readOnly = true;
    ribkEl.style.background = "#f0f0f0";
    ribkEl.style.color = "#999";
    uibkEl.readOnly = true;
    uibkEl.style.background = "#f0f0f0";
    uibkEl.style.color = "#999";
  } else {
    if (ribkEl.dataset.autoSync === "1") { ribkEl.value = ""; delete ribkEl.dataset.autoSync; }
    if (uibkEl.dataset.autoSync === "1") { uibkEl.value = ""; delete uibkEl.dataset.autoSync; }
    ribkEl.readOnly = false;
    ribkEl.style.background = "";
    ribkEl.style.color = "";
    uibkEl.readOnly = false;
    uibkEl.style.background = "";
    uibkEl.style.color = "";
  }
  var tblId = unitEl.getAttribute("data-tbl");
  if (tblId) updateBqKgCols(tblId);
}
</script>';

mysql_select_db('assay_qc');
$main_table = "assay_results";
include("edit_material_POST.php");
// --------------------
// RENDER PAGE
// --------------------
$qm = "
  SELECT `ID`,`Material`,`Type`,`Manufacturer`,`Description`,`Liaison`,`Date`,`Remarks`,`Docdb`,`Finished`
  FROM `$main_table`
  ORDER BY `ID` DESC
";
$rm = mysql_query($qm);
if (!$rm) die("Could not query assay_results: " . mysql_error() . "<BR>" . h($qm));

while ($row = mysql_fetch_assoc($rm)) {

    $assay_id = (int)$row['ID'];
    $material = $row['Material'];
    $detail_table = detail_table_name_from_row($assay_id, $material);
    ensure_detail_table($detail_table);

    echo '<br>';
    echo '<a id="assay-' . $assay_id . '"></a>';
    echo '<table border="1" cellpadding="4" width="100%">';
    echo '<tr style="background:#eee;">';
    echo '<th style="text-align:left;">';
    echo h($material);
    echo '<span style="float:right; font-weight:normal; white-space:nowrap;">';
    $finished_checked = (!empty($row['Finished']) && $row['Finished'] == 1) ? ' checked' : '';
    echo '<label style="margin-right:12px;">Finished <input type="checkbox" name="assay_finished" value="1" form="update-form-' . $assay_id . '"' . $finished_checked . '></label>';
    echo '<button type="submit" form="update-form-' . $assay_id . '">Save</button>';
    echo '&nbsp;&nbsp;';
    echo '<form action="' . h($_SERVER['PHP_SELF']) . '" method="post" style="display:inline;">' . csrf_field();
    echo '<input type="hidden" name="assay_action" value="delete_assay">';
    echo '<input type="hidden" name="assay_id" value="' . h($assay_id) . '">';
    echo '<button type="submit" onclick="return confirm(\'Delete assay row and its detail table (assay_results_ID)? This cannot be undone.\');">Delete</button>';
    echo '</form>';
    echo '</span>';
    echo '</th>';
    echo '</tr>';

    echo '<tr><td>';  // ONE big cell wrapping everything

    /***********************
     * 1) UPDATE ASSAY FORM
     ***********************/
    echo '<form id="update-form-' . $assay_id . '" action="' . h($_SERVER['PHP_SELF']) . '" method="post" enctype="multipart/form-data">' . csrf_field();
    echo '<input type="hidden" name="assay_action" value="update_assay">';
    echo '<input type="hidden" name="assay_id" value="' . h($assay_id) . '">';

    echo '<table border="0" cellpadding="3" width="100%">';

    echo '<tr>';
    echo '<td width="15%"><b>Material</b></td><td width="35%"><input type="text" name="assay_material" value="' . h($row['Material']) . '" size="40"></td>';
    echo '<td width="15%"><b>Type</b></td><td width="35%"><input type="text" name="assay_type" value="' . h($row['Type']) . '" size="40"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><b>Liaison Person</b></td><td><input type="text" name="assay_liaison" value="' . h($row['Liaison']) . '" size="40"></td>';
    echo '<td><b>Manufacturer</b></td><td><input type="text" name="assay_manufacturer" value="' . h($row['Manufacturer']) . '" size="40"></td>';
    echo '<td><b>Date</b></td><td><input type="date" name="assay_date" value="' . h($row['Date']) . '"></td>';
    echo '</tr>';

    echo '<tr>';
    echo '<td><b>Description</b></td>';
    echo '<td colspan="5"><textarea name="assay_description" rows="2" cols="100">' . h($row['Description']) . '</textarea></td>';
    echo '</tr>';

    // DocDB: allow multiple IDs (comma/space-separated), one link per ID
    echo '<tr>';
    echo '<td><b>DocDB</b></td>';
    echo '<td>';
    echo '<input type="text" name="assay_docdb" value="' . h($row['Docdb']) . '" size="20" placeholder="e.g. 12345, 67890">';
    $docdb_raw = trim((string)$row['Docdb']);
    if ($docdb_raw !== '') {
        $docdb_ids = array_filter(array_map(trim(...), preg_split('/[\s,]+/', $docdb_raw)));
        foreach ($docdb_ids as $id) {
            if ($id !== '' && is_numeric($id)) {
                $doc_url = 'https://gev.uchicago.edu/cgi-bin/DocDB/ShowDocument?docid=' . urlencode($id);
                echo ' <a href="' . h($doc_url) . '" target="_blank">[' . h($id) . ']</a>';
            }
        }
    }
    echo '</td>';
    echo '<td colspan="4"></td>';
    echo '</tr>';


    echo '</form>';

    echo '</table>';


    /***********************
     * 3) Attached files (list: preview, delete)
     ***********************/
    echo '<hr style="margin:12px 0;">';

    $qf = "SELECT * FROM `assay_files` WHERE `Assay_ID` = " . (int)$assay_id . " ORDER BY `ID` DESC";
    $rf = mysql_query($qf);
    if (!$rf) die("Could not query assay_files: " . mysql_error() . "<br>" . h($qf));

    echo '<div style="margin:6px 0;"><b>Attached files</b>&nbsp;&nbsp;<input type="file" name="assay_files[]" multiple form="update-form-' . $assay_id . '"></div>';

    if (mysql_num_rows($rf) == 0) {
        echo '<div style="color:#555;">(No files uploaded.)</div>';
    } else {
        echo '<ul>';
        while ($f = mysql_fetch_assoc($rf)) {
            $file_id = (int)$f['ID'];
            $label = $f['Orig_Name'] ?: $f['Stored_Name'];
            $preview_url = "serve_assay_file.php?id=" . $file_id . "&disposition=inline";

            echo '<li>';
            echo h($label) . ' ';
            echo '[<a href="' . h($preview_url) . '" target="_blank">preview</a>] ';
            echo '[<form action="' . h($_SERVER['PHP_SELF']) . '" method="post" style="display:inline;">' . csrf_field();
            echo '<input type="hidden" name="assay_action" value="delete_file">';
            echo '<input type="hidden" name="assay_id" value="' . h($assay_id) . '">';
            echo '<input type="hidden" name="file_id" value="' . h($file_id) . '">';
            echo '<button type="submit" '
                . 'style="background:none; border:none; padding:0; margin:0; color:#00f; text-decoration:underline; cursor:pointer; font:inherit;" '
                . 'onclick="return confirm(\'Delete this file? This cannot be undone.\');">'
                . 'delete</button>';
            echo '</form>]';
            echo '</li>';
        }
        echo '</ul>';
    }

    /***********************
     * 4) DETAIL TABLE (unchanged logic; already per-row forms)
     ***********************/
    echo '<hr style="margin:12px 0;">';

    echo '<div style="margin:6px 0;"><b>Nuclide activities</b> (table: ' . h($detail_table) . ')</div>';

    echo '<table id="dtbl-' . $assay_id . '" border="1" cellpadding="3" width="100%">';
    echo '<tr style="background:#f3f3f3;">';
    echo '<th>Nuclide</th>
      <th>Limit Type</th>
      <th>Result</th>
      <th>Uncertainty</th>
      <th>Unit</th>
      <th class="col-bqkg">Result(Bq/kg)</th>
      <th class="col-bqkg">Uncertainty(Bq/kg)</th>
      <th>used in G4</th>
      <th>Note</th>
      <th>Action</th>';
    echo '</tr>';

    $qd = "SELECT * FROM `{$detail_table}` ORDER BY `ID` ASC";
    $rd = mysql_query($qd);
    if (!$rd) die("Could not query detail table: " . mysql_error() . "<br>" . h($qd));

    $detail_row_ids = [];
    while ($d = mysql_fetch_assoc($rd)) {
        echo '<tr>';
        echo '<form action="' . h($_SERVER['PHP_SELF']) . '" method="post">' . csrf_field();
        echo '<input type="hidden" name="detail_table_name" value="' . h($detail_table) . '">';
        echo '<input type="hidden" name="detail_id" value="' . h($d['ID']) . '">';

        $cell_td = ' style="text-align:center; vertical-align:middle;"';
        $cell_in = ' style="width:100%; text-align:center; box-sizing:border-box; padding:2px 6px;"';

        $row_id = (int)$d['ID'];
        $detail_row_ids[] = $row_id;
        $is_upper = ($d['Type'] === 'Upper Limit');
        $unc_display = fmt_sci($d['Uncertainty'], 2);


        // type selector
        $type_select =
            '<select name="detail_type" id="detail_type_' . h($row_id) . '"' . $cell_in
            . ' onchange="toggleUnc(\'unc_cell_' . h($row_id) . '\', this.value, ' . (int)$row_id . ')">'
            . '<option value="Upper Limit"' . ($is_upper ? ' selected' : '') . '>Upper Limit</option>'
            . '<option value="Value"' . (!$is_upper ? ' selected' : '') . '>Value</option>'
            . '</select>';

        echo '<td' . $cell_td . '><input type="text" name="detail_nuclide_1" value="' . h($d['Nuclide_1']) . '"' . $cell_in . '></td>';
        echo '<td' . $cell_td . '>' . $type_select . '</td>';

        // Result (raw)
        echo '<td' . $cell_td . '><input type="text" name="detail_result" id="detail_result_' . $row_id . '" value="' . h(fmt_sci($d['Result'], 2)) . '"' . $cell_in . ' oninput="syncBqKg(' . $row_id . ')"></td>';

        // Uncertainty (raw); direct form input, hidden when Upper Limit
        echo '<td' . $cell_td . ' id="unc_cell_' . h($row_id) . '">';
        echo '<input type="text" name="detail_uncertainty" id="detail_uncertainty_' . h($row_id) . '" value="' . h($unc_display) . '"' . $cell_in
            . ($is_upper ? ' style="display:none;"' : '')
            . ' oninput="syncBqKg(' . $row_id . ');">';
        if ($is_upper) {
            echo '<span class="unc-na" style="color:#777;">n/a</span>';
        }
        echo '</td>';

        // Unit (text input, shared for both Result and Uncertainty)
        $result_unit = $d['Result_Unit'] ?? 'Bq/kg';
        echo '<td' . $cell_td . '><input type="text" name="detail_result_unit" id="detail_result_unit_' . $row_id . '" data-tbl="dtbl-' . $assay_id . '" value="' . h($result_unit) . '"' . $cell_in . ' oninput="syncBqKg(' . $row_id . ')"></td>';

        // Result converted to Bq/kg (manual entry; auto-filled and locked when unit is Bq/kg)
        $ribk_display = isset($d['Result_in_BqKg']) ? fmt_sci($d['Result_in_BqKg'], 2) : '';
        echo '<td class="col-bqkg"' . $cell_td . '><input type="text" name="detail_result_in_bqkg" id="detail_result_bqkg_' . $row_id . '" value="' . h($ribk_display) . '"' . $cell_in . '></td>';

        // Uncertainty converted to Bq/kg (manual entry; auto-filled and locked when unit is Bq/kg)
        $uibk_display = isset($d['Uncertainty_in_BqKg']) ? fmt_sci($d['Uncertainty_in_BqKg'], 2) : '';
        echo '<td class="col-bqkg"' . $cell_td . '><input type="text" name="detail_uncertainty_in_bqkg" id="detail_unc_bqkg_' . $row_id . '" value="' . h($uibk_display) . '"' . $cell_in . '></td>';

        // Checkbox
        $checked = (!empty($d['Used_in_simulation']) && $d['Used_in_simulation'] == 1) ? ' checked' : '';
        echo '<td style="text-align:center; vertical-align:middle; width:80px; white-space:nowrap;">'
            . '<input type="checkbox" name="detail_used_in_simulation" value="1"' . $checked . '>'
            . '</td>';        //note
        echo '<td' . $cell_td . '><input type="text" name="detail_note" value="' . h($d['Note']) . '"' . $cell_in . '></td>';

        // Action buttons
        echo '<td style="text-align:center; vertical-align:middle; white-space:nowrap;">'
            . '<button type="submit" name="detail_action" value="update">Update</button> '
            . '<button type="submit" name="detail_action" value="delete" onclick="return confirm(\'Delete this nuclide row?\');">Delete</button>'
            . '</td>';

        echo '</form>';
        echo '</tr>';
    }

    // Initialize Bq/kg sync for all rendered rows, then update column visibility
    if (!empty($detail_row_ids)) {
        echo '<script>';
        foreach ($detail_row_ids as $init_id) {
            echo 'syncBqKg(' . (int)$init_id . ');';
        }
        echo 'updateBqKgCols("dtbl-' . $assay_id . '");';
        echo '</script>';
    }

    // add-new row
    $new_row_id = 'a' . $assay_id; // unique per assay, avoids collision with numeric row IDs
    echo '<tr>';
    echo '<form action="' . h($_SERVER['PHP_SELF']) . '" method="post">' . csrf_field();
    echo '<input type="hidden" name="detail_table_name" value="' . h($detail_table) . '">';
    $cell_td = ' style="text-align:center; vertical-align:middle;"';
    $cell_in = ' style="width:100%; text-align:center; box-sizing:border-box; padding:2px 6px;"';
    $type_select_new =
        '<select name="detail_type"' . $cell_in . '>'
        . '<option value="" selected></option>'
        . '<option value="Upper Limit">Upper Limit</option>'
        . '<option value="Value">Value</option>'
        . '</select>';
    echo '<td' . $cell_td . '><input type="text" name="detail_nuclide_1" value=""' . $cell_in . '></td>';
    echo '<td' . $cell_td . '>' . $type_select_new . '</td>';
    echo '<td' . $cell_td . '><input type="text" name="detail_result" id="detail_result_' . $new_row_id . '" value=""' . $cell_in . ' oninput="syncBqKg(\'' . $new_row_id . '\')"></td>';
    echo '<td' . $cell_td . '><input type="text" name="detail_uncertainty" id="detail_uncertainty_' . $new_row_id . '" value=""' . $cell_in . ' oninput="syncBqKg(\'' . $new_row_id . '\')"></td>';
    echo '<td' . $cell_td . '><input type="text" name="detail_result_unit" id="detail_result_unit_' . $new_row_id . '" data-tbl="dtbl-' . $assay_id . '" value="Bq/kg"' . $cell_in . ' oninput="syncBqKg(\'' . $new_row_id . '\')"></td>';
    echo '<td class="col-bqkg"' . $cell_td . '><input type="text" name="detail_result_in_bqkg" id="detail_result_bqkg_' . $new_row_id . '" value=""' . $cell_in . '></td>';
    echo '<td class="col-bqkg"' . $cell_td . '><input type="text" name="detail_uncertainty_in_bqkg" id="detail_unc_bqkg_' . $new_row_id . '" value=""' . $cell_in . '></td>';
    echo '<td></td>'; // used in G4 — not set on add
    echo '<td' . $cell_td . '><input type="text" name="detail_note" value=""' . $cell_in . '></td>';
    echo '<td style="text-align:center; vertical-align:middle; white-space:nowrap;"><button type="submit" name="detail_action" value="add">Add</button></td>';
    echo '</form>';
    echo '</tr>';
    echo '<script>syncBqKg(\'' . $new_row_id . '\'); updateBqKgCols("dtbl-' . $assay_id . '");</script>';

    echo '</table>'; // detail table

    echo '</td></tr>';  // close wrapper cell/row
    echo '</table>';    // close outer wrapper table
    echo '<br><br>';
}

/*******************************
 * ADD NEW ASSAY (blank form)
 *******************************/
echo '<br>';
echo '<table border="1" cellpadding="4" width="100%">';
echo '<tr style="background:#eee;"><th align="left">Add new assay</th></tr>';
echo '<tr><td>';

echo '<form action="' . h($_SERVER['PHP_SELF']) . '" method="post">' . csrf_field();
echo '<input type="hidden" name="assay_action" value="add_assay">';   // <-- change to your handler name if different

echo '<table border="0" cellpadding="3" width="100%">';

// Row 1
echo '<tr>';
echo '<td width="15%"><b>Material</b></td>';
echo '<td width="35%"><input type="text" name="assay_material" value="" size="40"></td>';

echo '<td colspan="1" style="text-align:left; padding-top:8px;">';
echo '<button type="submit">Add assay</button>';
echo '</td>';
echo '</tr>';

echo '</table>';
echo '</form>';

echo '</td></tr>';
echo '</table>';
echo '<br><br>';

page_end();
