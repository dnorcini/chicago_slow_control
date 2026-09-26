<?php

// STEP 1 DO THE SQL QUERY
mysql_select_db('assay_qc');
$query = "SELECT * FROM `components` ORDER BY `name`";
$result = mysql_query($query);
if (!$result) {
    die("Could not query the database <br>" . mysql_error());
}
// STEP 2 define php arrays here
$component_name = array();
$component_detector_parts = array();
$component_material = array();
$component_geant4_id = array();
$component_cad_name = array();
$component_volumn = array();
$component_surface = array();
$component_reference = array();
$component_docdb = array();
$component_note = array();

// STEP 3 assign sql results to php arrays
while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
    $component_name[] = $row['name'];
    $component_note[] = $row['note'];

    // if (isset($row['note']))
    //     $component_note[] = $row['note'];
    // else
    //     $component_note[] = 'note';
}
////////////to be populated////////////////



// STEP 4 ASSOCIATE NAME TO EACH COLUMN
// $component_detector_parts = array_combine($component_name, $component_detector_parts);
// $component_material = array_combine($component_name, $component_material);


////////// to be populated! //////////
$component_note = array_combine($component_name, $component_note);
