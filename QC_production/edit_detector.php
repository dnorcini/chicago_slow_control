<?php
// edit_background.php
// PHP 5.6 (mysql_*), "higher-level" page:
// - Main table: assay_qc.component  (background simulation components)
// - For each component row:
//     (A) show/edit component fields
//     (B) show linked assay activities (read-only) via component.Assay
//     (C) show/edit cosmogenic activation table components_activation_<component_id>
//         + show blank columns for Activity/DRU (calculation left for later)
//
// Assumptions / conventions:
// - component.Assay stores either:
//     * an integer assay_results.ID (recommended), OR
//     * a material name that matches assay_results.Material (fallback supported)
// - Assay detail table uses the stable naming: assay_results_<assay_id>
//   (created by edit_assay_results.php)

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

$req_priv = "full";
include("db_login.php");   // legacy mysql_connect() etc.
include("page_setup.php"); // your header/menu
include("aux/assay_helpers.php");

mysql_select_db('assay_qc');

function h($s)
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
function post_esc($key)
{
    return mysql_real_escape_string(isset($_POST[$key]) ? $_POST[$key] : "");
}

// Allowlist table name helper
function assert_table_name($name, $prefix)
{
    // Only allow [a-z0-9_] and must start with prefix
    $name = mysql_real_escape_string($name);
    $re = '/^' . preg_quote($prefix, '/') . '[a-z0-9_]+$/';
    if (!preg_match($re, $name)) die("Invalid table name.");
    return $name;
}

function ensure_activation_table($table_name)
{
    $table_name = assert_table_name($table_name, "components_activation_");
    $q = "
      CREATE TABLE IF NOT EXISTS `{$table_name}` (
        `ID` INT(11) NOT NULL AUTO_INCREMENT,
        `Nuclide` VARCHAR(8) DEFAULT NULL,
        `Shielding` DOUBLE DEFAULT NULL,
        `Exposure` DOUBLE DEFAULT NULL,
        `Remarks` TEXT,
        PRIMARY KEY (`ID`)
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1
    ";
    $r = mysql_query($q);
    if (!$r) die("Could not create activation table: " . mysql_error() . "<BR>" . h($q));
}

function assay_id_from_component_assay($assay_field_value)
{
    // If numeric -> direct ID
    $assay_field_value = trim((string)$assay_field_value);
    if ($assay_field_value === "") return 0;

    if (preg_match('/^\d+$/', $assay_field_value)) {
        return (int)$assay_field_value;
    }

    // Fallback: treat as Material string, get the most recent assay with that Material
    $mat = mysql_real_escape_string($assay_field_value);
    $q = "SELECT `ID` FROM `assay_results` WHERE `Material` = '$mat' ORDER BY `ID` DESC LIMIT 1";
    $r = mysql_query($q);
    if ($r && mysql_num_rows($r) === 1) {
        $row = mysql_fetch_assoc($r);
        return (int)$row['ID'];
    }
    return 0;
}

function assay_detail_table_name($assay_id)
{
    $assay_id = (int)$assay_id;
    if ($assay_id <= 0) return "";
    return "assay_results_" . $assay_id;
}

function assay_detail_table_exists($table_name)
{
    if ($table_name === "") return false;
    // allow only assay_results_<digits>
    if (!preg_match('/^assay_results_\d+$/', $table_name)) return false;
    $t = mysql_real_escape_string($table_name);
    $q = "SHOW TABLES LIKE '$t'";
    $r = mysql_query($q);
    return ($r && mysql_num_rows($r) > 0);
}

// --------------------
// HANDLE POST: Add new component (creates activation table)
// --------------------
if (!empty($_POST['comp_action']) && $_POST['comp_action'] === "add_component") {

    $name        = post_esc("comp_name");
    $material    = post_esc("comp_material");
    $subgroup    = post_esc("comp_subgroup");
    $geant4_id   = post_esc("comp_geant4_id");
    $cad_name    = post_esc("comp_cad_name");
    $remarks     = post_esc("comp_remarks");
    $provenance  = post_esc("comp_provenance");
    $assay       = post_esc("comp_assay");

    $vol_sql = "NULL";
    if (isset($_POST['comp_volumn']) && trim($_POST['comp_volumn']) !== "") {
        $vol_sql = (0.0 + $_POST['comp_volumn']);
    }

    if ($name === "") {
        echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">Name is required to add a component.</div>';
    } else {
        $q = "
          INSERT INTO `components`
            (`Name`,`Material`,`Subgroup`,`Volumn`,`Geant4-id`,`CAD-name`,`Remarks`,`Provenance`,`Assay`)
          VALUES
            ('$name','$material','$subgroup',$vol_sql,'$geant4_id','$cad_name','$remarks','$provenance','$assay')
        ";
        $r = mysql_query($q);
        if (!$r) die("Could not add component: " . mysql_error() . "<BR>" . h($q));

        $new_id = (int)mysql_insert_id();
        $act_table = "components_activation_" . $new_id;
        ensure_activation_table($act_table);

        echo '<div style="border:1px solid #ccc; padding:6px; margin:6px 0;">Added component ID ' . h($new_id) . ' and created activation table ' . h($act_table) . '.</div>';
    }
}

// --------------------
// HANDLE POST: Update component
// --------------------
if (!empty($_POST['comp_action']) && $_POST['comp_action'] === "update_component" && isset($_POST['comp_id'])) {

    $id         = (int)$_POST['comp_id'];
    $name       = post_esc("comp_name");
    $material   = post_esc("comp_material");
    $subgroup   = post_esc("comp_subgroup");
    $geant4_id  = post_esc("comp_geant4_id");
    $cad_name   = post_esc("comp_cad_name");
    $remarks    = post_esc("comp_remarks");
    $prov       = post_esc("comp_provenance");
    $assay      = post_esc("comp_assay");

    $vol_sql = "NULL";
    if (isset($_POST['comp_volumn']) && trim($_POST['comp_volumn']) !== "") {
        $vol_sql = (0.0 + $_POST['comp_volumn']);
    }

    $q = "
      UPDATE `components` SET
        `Name`        = '$name',
        `Material`    = '$material',
        `Subgroup`    = '$subgroup',
        `Volumn`      = $vol_sql,
        `Geant4-id`   = '$geant4_id',
        `CAD-name`    = '$cad_name',
        `Remarks`     = '$remarks',
        `Provenance`  = '$prov',
        `Assay`       = '$assay'
      WHERE `ID` = $id
      LIMIT 1
    ";
    $r = mysql_query($q);
    if (!$r) die("Could not update component: " . mysql_error() . "<BR>" . h($q));

    // Ensure activation table exists
    $act_table = "components_activation_" . $id;
    ensure_activation_table($act_table);
}

// --------------------
// HANDLE POST: Delete component row (does NOT drop activation table by default)
// --------------------
if (!empty($_POST['comp_action']) && $_POST['comp_action'] === "delete_component" && isset($_POST['comp_id'])) {
    $id = (int)$_POST['comp_id'];

    $q = "DELETE FROM `components` WHERE `ID` = $id LIMIT 1";
    $r = mysql_query($q);
    if (!$r) die("Could not delete component: " . mysql_error() . "<BR>" . h($q));

    // Safer default: do NOT drop activation table automatically.
    // If you want it:
    // $act_table = "components_activation_" . $id;
    // mysql_query("DROP TABLE IF EXISTS `".mysql_real_escape_string($act_table)."`");
}

// --------------------
// HANDLE POST: Activation row add/update/delete
// --------------------
if (!empty($_POST['act_action']) && !empty($_POST['act_table_name'])) {

    $act_table = assert_table_name($_POST['act_table_name'], "components_activation_");
    ensure_activation_table($act_table);

    $action   = $_POST['act_action'];
    $nuclide  = post_esc("act_nuclide");
    $remarks  = post_esc("act_remarks");

    $shield_sql = "NULL";
    if (isset($_POST['act_shielding']) && trim($_POST['act_shielding']) !== "") {
        $shield_sql = (0.0 + $_POST['act_shielding']);
    }
    $expo_sql = "NULL";
    if (isset($_POST['act_exposure']) && trim($_POST['act_exposure']) !== "") {
        $expo_sql = (0.0 + $_POST['act_exposure']);
    }

    if ($action === "add") {
        $q = "
          INSERT INTO `{$act_table}`
            (`Nuclide`,`Shielding`,`Exposure`,`Remarks`)
          VALUES
            ('$nuclide',$shield_sql,$expo_sql,'$remarks')
        ";
        $r = mysql_query($q);
        if (!$r) die("Could not add activation row: " . mysql_error() . "<BR>" . h($q));
    }

    if ($action === "update") {
        if (!isset($_POST['act_id'])) die("Missing act_id for update.");
        $aid = (int)$_POST['act_id'];

        $q = "
          UPDATE `{$act_table}` SET
            `Nuclide`    = '$nuclide',
            `Shielding`  = $shield_sql,
            `Exposure`   = $expo_sql,
            `Remarks`    = '$remarks'
          WHERE `ID` = $aid
          LIMIT 1
        ";
        $r = mysql_query($q);
        if (!$r) die("Could not update activation row: " . mysql_error() . "<BR>" . h($q));
    }

    if ($action === "delete") {
        if (!isset($_POST['act_id'])) die("Missing act_id for delete.");
        $aid = (int)$_POST['act_id'];

        $q = "DELETE FROM `{$act_table}` WHERE `ID` = $aid LIMIT 1";
        $r = mysql_query($q);
        if (!$r) die("Could not delete activation row: " . mysql_error() . "<BR>" . h($q));
    }
}

// --------------------
// RENDER PAGE
// --------------------
echo '<H2>Background components</H2>';

$q = "
  SELECT
    `ID`,`Name`,`Material`,`Subgroup`,`Volumn`,`Geant4-id`,`CAD-name`,`Remarks`,`Provenance`,`Assay`
  FROM `components`
  ORDER BY `ID` DESC
";
$r = mysql_query($q);
if (!$r) die("Could not query component: " . mysql_error() . "<BR>" . h($q));

while ($c = mysql_fetch_assoc($r)) {

    $cid = (int)$c['ID'];
    $act_table = "components_activation_" . $cid;
    ensure_activation_table($act_table);

    // Resolve linked assay
    $assay_id = assay_id_from_component_assay($c['Assay']);
    $assay_table = assay_detail_table_name($assay_id);
    $assay_ok = assay_detail_table_exists($assay_table);

    echo '<BR>';
    echo '<TABLE border="1" cellpadding="4" width="100%">';
    echo '<TR style="background:#eee;"><TH colspan="4" align="left">Component ID ' . h($cid) . ' — ' . h($c['Name']) . '</TH></TR>';

    // ---------------- Component edit block ----------------
    echo '<TR><TD colspan="4">';
    echo '<FORM action="' . h($_SERVER['PHP_SELF']) . '" method="post">';
    echo '<input type="hidden" name="comp_action" value="update_component">';
    echo '<input type="hidden" name="comp_id" value="' . h($cid) . '">';

    echo '<TABLE border="0" cellpadding="3" width="100%">';
    echo '<TR>';
    echo '<TD width="15%"><b>ID</b></TD><TD width="35%">' . h($cid) . '</TD>';
    echo '<TD width="15%"><b>Name</b></TD><TD width="35%"><input type="text" name="comp_name" value="' . h($c['Name']) . '" size="40"></TD>';
    echo '</TR>';

    echo '<TR>';
    echo '<TD><b>Material</b></TD><TD><input type="text" name="comp_material" value="' . h($c['Material']) . '" size="40"></TD>';
    echo '<TD><b>Subgroup</b></TD><TD><input type="text" name="comp_subgroup" value="' . h($c['Subgroup']) . '" size="40"></TD>';
    echo '</TR>';

    echo '<TR>';
    echo '<TD><b>Volumn (kg)</b></TD><TD><input type="text" name="comp_volumn" value="' . h($c['Volumn']) . '" size="16"></TD>';
    echo '<TD><b>Provenance</b></TD><TD><input type="text" name="comp_provenance" value="' . h($c['Provenance']) . '" size="40"></TD>';
    echo '</TR>';

    echo '<TR>';
    echo '<TD><b>Geant4-id</b></TD><TD><input type="text" name="comp_geant4_id" value="' . h($c['Geant4-id']) . '" size="55"></TD>';
    echo '<TD><b>CAD-name</b></TD><TD><input type="text" name="comp_cad_name" value="' . h($c['CAD-name']) . '" size="55"></TD>';
    echo '</TR>';


    // a dropdown menu to select assays:
    $materials = array();
    $res_mat = mysql_query("SELECT DISTINCT Material FROM assay_results ORDER BY Material ASC");
    while ($row_mat = mysql_fetch_assoc($res_mat)) {
        $materials[] = $row_mat['Material'];
    }
    echo '<TR>';
    echo '<TD><b>Assay</b></TD>';
    echo '<TD colspan="3">';

    echo '<select name="comp_assay" style="width:300px; padding:2px 6px;">';
    echo '<option value=""></option>';  // empty option

    foreach ($materials as $mat) {
        echo '<option value="' . h($mat) . '"'
            . ($c['Assay'] == $mat ? ' selected' : '')
            . '>' . h($mat) . '</option>';
    }

    echo '</select> ';

    echo '<span style="color:#555;">(select from assay_results.Material)</span>';
    echo '</TD>';
    echo '</TR>';


    echo '<TR>';
    echo '<TD><b>Remarks</b></TD><TD colspan="3"><TEXTAREA name="comp_remarks" rows="2" cols="100">' . h($c['Remarks']) . '</TEXTAREA></TD>';
    echo '</TR>';

    echo '<TR>';
    echo '<TD colspan="4" align="left">';
    // Use two submit buttons in the SAME form (avoid nested <form>, which can cause "Update" to submit as delete).
    echo '<button type="submit" name="comp_action" value="update_component">Update component</button> ';
    echo '<button type="submit" name="comp_action" value="delete_component" onclick="return confirm(\'Delete component row (activation table kept)?\');">Delete component row</button>';
    echo '</TD>';
    echo '</TR>';

    echo '</TABLE>';
    echo '</FORM>';
    echo '</TD></TR>';

    // ---------------- Linked assay activities (read-only) ----------------
    echo '<TR><TD colspan="4">';
    echo '<DIV style="margin:6px 0;"><b>Linked assay activities</b></DIV>';

    if ($assay_id <= 0) {
        echo '<DIV style="color:#a00;">No linked assay found from Assay = ' . h($c['Assay']) . '.</DIV>';
    } else if (!$assay_ok) {
        echo '<DIV style="color:#a00;">Linked assay ID ' . h($assay_id) . ' found, but detail table ' . h($assay_table) . ' does not exist.</DIV>';
    } else {
        // show assay header info
        $qh = "SELECT `ID`,`Material`,`Type`,`Manufacturer`,`Description`,`Liaison`,`Date`,`Remarks`,`Docdb` FROM `assay_results` WHERE `ID` = " . (int)$assay_id . " LIMIT 1";
        $rh = mysql_query($qh);
        if ($rh && mysql_num_rows($rh) === 1) {
            $ah = mysql_fetch_assoc($rh);
            echo '<DIV style="color:#333;">Assay ID ' . h($ah['ID']) . ' — ' . h($ah['Material']) . ' (' . h($ah['Type']) . '), DocDB ' . h($ah['Docdb']) . '</DIV>';
        }

        echo '<TABLE border="1" cellpadding="3" width="100%" style="margin-top:6px;">';
        echo '<TR style="background:#f3f3f3;">';
        echo '<TH>Nuclide_1</TH><TH>Nuclide_2</TH><TH>Type</TH><TH>Result(Bq/kg)</TH><TH>Uncertainty(Bq/kg)</TH><TH>Activity(decay/kg/day)</TH><TH>d.r.u.(events/keV/kg/day)</TH><TH>Note</TH>';
        echo '</TR>';

        $qd = "SELECT `Nuclide_1`,`Nuclide_2`,`Type`,`Result`,`Uncertainty`,`Note` FROM `{$assay_table}` ORDER BY `ID` DESC";
        $rd = mysql_query($qd);
        if (!$rd) {
            echo '<TR><TD colspan="6" style="color:#a00;">Could not query assay table: ' . h(mysql_error()) . '</TD></TR>';
        } else if (mysql_num_rows($rd) === 0) {
            echo '<TR><TD colspan="6" style="color:#555;">(No nuclide rows yet.)</TD></TR>';
        } else {
            while ($d = mysql_fetch_assoc($rd)) {
                echo '<TR>';
                echo '<TD>' . h($d['Nuclide_1']) . '</TD>';
                echo '<TD>' . h($d['Nuclide_2']) . '</TD>';
                echo '<TD>' . h($d['Type']) . '</TD>';
                echo '<TD>' . h(fmt_sci($d['Result'], 2)) . '</TD>';
                echo '<TD>' . h(fmt_sci($d['Uncertainty'], 2)) . '</TD>';
                // Placeholder computed columns
                echo '<TD style="color:#777;">&nbsp;</TD>';
                echo '<TD style="color:#777;">&nbsp;</TD>';
                echo '<TD>' . nl2br(h($d['Note'])) . '</TD>';
                echo '</TR>';
            }
        }
        echo '</TABLE>';
    }

    echo '</TD></TR>';

    // ---------------- Cosmogenic activation table (editable) ----------------
    echo '<TR><TD colspan="4">';
    echo '<DIV style="margin:6px 0;"><b>Cosmogenic activation</b> (table: ' . h($act_table) . ')</DIV>';

    echo '<TABLE border="1" cellpadding="3" width="100%">';
    echo '<TR style="background:#f3f3f3;">';
    echo '<TH>Nuclide</TH><TH>Shielding</TH><TH>First Day Underground</TH><TH>Total Time Underground</TH><TH>Remarks</TH><TH>Activity(decay/kg/day)</TH><TH>Geant4 factor to dru</TH><TH>d.r.u.(events/keV/kg/day)</TH><TH>Action</TH>';
    echo '</TR>';

    $qa = "SELECT * FROM `{$act_table}` ORDER BY `ID` DESC";
    $ra = mysql_query($qa);
    if (!$ra) die("Could not query activation table: " . mysql_error() . "<BR>" . h($qa));

    while ($a = mysql_fetch_assoc($ra)) {
        echo '<TR>';
        echo '<FORM action="' . h($_SERVER['PHP_SELF']) . '" method="post">';
        echo '<input type="hidden" name="act_table_name" value="' . h($act_table) . '">';
        echo '<input type="hidden" name="act_id" value="' . h($a['ID']) . '">';

        echo '<TD><input type="text" name="act_nuclide" value="' . h($a['Nuclide']) . '" size="10"></TD>';
        echo '<TD><input type="text" name="act_shielding" value="' . h($a['Shielding']) . '" size="12"></TD>';
        // echo '<TD><input type="text" name="act_exposure" value="' . h($a['Exposure']) . '" size="12"></TD>';
        // Placeholder for firstday underground columns
        echo '<TD style="color:#777; font-style:italic;">YYYY-MM-DD</TD>';
        echo '<TD style="color:#777;">&nbsp;</TD>';

        echo '<TD><name="act_remarks" rows="2" cols="30">' . h($a['Remarks']) . '</TD>';

        // Placeholder computed columns
        echo '<TD style="color:#777;">&nbsp;</TD>';
        echo '<TD style="color:#777;">&nbsp;</TD>';
        echo '<TD style="color:#777;">&nbsp;</TD>';

        echo '<TD style="white-space:nowrap;">';
        echo '<button type="submit" name="act_action" value="update">Update</button> ';
        echo '<button type="submit" name="act_action" value="delete" onclick="return confirm(\'Delete this activation row?\');">Delete</button>';
        echo '</TD>';

        echo '</FORM>';
        echo '</TR>';
    }

    // Add-new activation row
    echo '<TR>';
    echo '<FORM action="' . h($_SERVER['PHP_SELF']) . '" method="post">';
    echo '<input type="hidden" name="act_table_name" value="' . h($act_table) . '">';

    echo '<TD><input type="text" name="act_nuclide" value="" size="10"></TD>';
    echo '<TD><input type="text" name="act_shielding" value="" size="12"></TD>';
    echo '<TD style="color:#777;">&nbsp;</TD>';
    echo '<TD style="color:#777;">&nbsp;</TD>';

    echo '<TD><name="act_remarks" rows="2" cols="30"></TD>';

    echo '<TD style="color:#777;">&nbsp;</TD>';
    echo '<TD style="color:#777;">&nbsp;</TD>';
    echo '<TD style="color:#777;">&nbsp;</TD>';

    echo '<TD><button type="submit" name="act_action" value="add">Add</button></TD>';

    echo '</FORM>';
    echo '</TR>';

    echo '</TABLE>';
    echo '</TD></TR>';

    echo '</TABLE>';
    echo '<BR><BR>';
}

// --------------------
// Add new component panel
// --------------------
echo '<TABLE border="1" cellpadding="4" width="100%">';
echo '<TR style="background:#eee;"><TH align="left">Add new component</TH></TR>';
echo '<TR><TD>';

echo '<FORM action="' . h($_SERVER['PHP_SELF']) . '" method="post">';
echo '<input type="hidden" name="comp_action" value="add_component">';

echo '<DIV>';
echo 'Name: <input type="text" name="comp_name" size="20"> ';
echo 'Material: <input type="text" name="comp_material" size="16"> ';
echo 'Subgroup: <input type="text" name="comp_subgroup" size="16"> ';
echo 'Volumn (kg): <input type="text" name="comp_volumn" size="10"> ';
echo 'Provenance: <input type="text" name="comp_provenance" size="12"> ';
echo 'Assay: <input type="text" name="comp_assay" size="10">';
echo '</DIV>';

echo '<DIV style="margin-top:6px;">Geant4-id: <input type="text" name="comp_geant4_id" size="70"></DIV>';
echo '<DIV style="margin-top:6px;">CAD-name: <input type="text" name="comp_cad_name" size="70"></DIV>';

echo '<DIV style="margin-top:6px;">Remarks:<BR><TEXTAREA name="comp_remarks" rows="2" cols="110"></TEXTAREA></DIV>';

echo '<DIV style="margin-top:6px;"><input type="submit" value="Add component"></DIV>';
echo '</FORM>';

echo '</TD></TR>';
echo '</TABLE>';
