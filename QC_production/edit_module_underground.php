<?php
// edit_module_underground.php
// D.Norcini, Hopkins, 2024
// Cinyu Zhu, Hopkins, 2025

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();


//to avoil session key conflicts, leading to incorrect file paths
if (isset($_SESSION['choosen_module_underground'])) {
    $key = 'file_url_' . $_SESSION['choosen_module_underground'];
    unset($_SESSION[$key]);
    $key = 'log_url_' . $_SESSION['choosen_module_underground'];
    unset($_SESSION[$key]);
}

$req_priv = "full";
include("db_login.php");
include("page_setup.php");
include("aux/array_defs.php"); // Import all array definitions

$table = "MODULE_UNDERGROUND2";

// Clear session variables when module_underground_id changes to prevent data from other module_undergrounds interfering
if (isset($_POST['choosen']) && $_POST['choosen'] !== $_SESSION['choosen_module_underground']) {
    unset($_SESSION['log_url'], $_SESSION['file_url'], $_SESSION['upload_dir'], $_SESSION['base_url'], $_SESSION['log_exists'], $_SESSION['file_exists']);
}


// Set the chosen module_underground from session "req_id"
if (!empty($_SESSION['req_id'])) {
    $_SESSION['choosen_module_underground'] = $_SESSION['req_id'];
    unset($_SESSION['req_id']);
}
// If the user manually selects a module from a dropdown or button, it updates the current session to reflect that selection.
if (isset($_POST['choosen'])) {
    $_SESSION['choosen_module_underground'] = $_POST['choosen'];
}
// Set default as first module if none is chosen
if (empty($_SESSION['choosen_module_underground'])) {
    $_SESSION['choosen_module_underground'] = 1;
}

// Create a new entry if requested
if (isset($_POST['new'])) {
    $query = "INSERT INTO `" . $table . "` (`ID`, `Last_update`) VALUES (NULL, '" . time() . "')";
    $result = mysql_query($query);
    if (!$result) {
        die("Could not query the database <br />" . mysql_error());
    }
    include("aux/get_last_table_id.php");
    $_SESSION['choosen_module_underground'] = $last_id;

    // Reset only the problematic section when creating a new module_underground
    $check_A = $check_B = $check_C = $check_D = 0;
    $grade_A = $grade_B = $grade_C = $grade_D = '';
    $notes_A = $notes_B = $notes_C = $notes_D = '';
    // $channel_A = $channel_B = $channel_C = $channel_D = '';


    // Clear any session variables related to file uploads
    unset($_SESSION['log_url'], $_SESSION['file_url'], $_SESSION['upload_dir'], $_SESSION['base_url'], $_SESSION['log_exists'], $_SESSION['file_exists']);
}

// Navigation logic (First, Last, Previous, Next)
if (isset($_POST['first'])) {
    $_SESSION['choosen_module_underground'] = 1;
}
if (isset($_POST['last'])) {
    include("aux/get_last_die_id.php");
    $_SESSION['choosen_module_underground'] = $last_id;
}
if (isset($_POST['prev'])) {
    $_SESSION['choosen_module_underground'] = max(1, $_SESSION['choosen_module_underground'] - 1);
}
if (isset($_POST['next'])) {
    $_SESSION['choosen_module_underground'] = $_SESSION['choosen_module_underground'] + 1;
}

// Ensure we never go below 1 or above the last MODULE_UNDERGROUND ID
include("aux/get_last_die_id.php");
if ($_SESSION['choosen_module_underground'] < 1) {
    $_SESSION['choosen_module_underground'] = 1;
}
if ($_SESSION['choosen_module_underground'] > $last_id) {
    $_SESSION['choosen_module_underground'] = $last_id;
}

// Assign the chosen choosen_module_underground to the $id variable
$id = (int)$_SESSION['choosen_module_underground'];

// Fetch module_underground details
include("aux/get_module_underground_vals.php");

// Update module_underground details if form is submitted
if (isset($_POST['id'])) {
    $module_underground_id = (int)$_POST['id'];

    // Clear session variables when switching module_underground
    if (!isset($_SESSION['choosen_module_underground']) || $_SESSION['choosen_module_underground'] !== $module_underground_id) {
        unset($_SESSION['file_url_' . $module_underground_id], $_SESSION['file_exists_' . $module_underground_id], $_SESSION['log_url_' . $module_underground_id], $_SESSION['log_exists_' . $module_underground_id]);
    }

    // Clear session variables related to file uploads when module_underground_id changes
    unset($_SESSION['file_url_' . $module_underground_id], $_SESSION['upload_dir_' . $module_underground_id], $_SESSION['base_url_' . $module_underground_id], $_SESSION['file_exists_' . $module_underground_id], $_SESSION['log_url_' . $module_underground_id], $_SESSION['log_exists_' . $module_underground_id]);

    // Directory to store uploaded images
    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL


    $_SESSION['upload_dir_' . $module_underground_id] = $upload_dir;
    $_SESSION['base_url_' . $module_underground_id] = $base_url;

    // Create the directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            die('Failed to create directory: ' . $upload_dir);
        }
    }

    if (!is_writable($upload_dir)) {
        die("Directory is not writable: $upload_dir");
    }

    // Allowed file types
    $allowed_types = array('image/png', 'image/jpeg', 'application/pdf', 'application/octet-stream', '.seq', '.bcf');
    $allowed_logs = array('text/plain');

    // ======================
    // Upload Trace and Image Files
    // ======================
    $file_fields = ['trace_high_file'];
    $log_fields = ['trace_high_log'];

    //sequencer, config and psd files
    $file_fields[] = 'seq_high_file';
    $file_fields[] = 'bcf_high_file';
    $file_fields[] = 'seq_low_file';
    $file_fields[] = 'bcf_low_file';
    // for ch0123, but follow the naming convention of other variables woth ABCD
    $file_fields[] = 'psd_high_file_A';
    $file_fields[] = 'psd_high_file_B';
    $file_fields[] = 'psd_high_file_C';
    $file_fields[] = 'psd_high_file_D';


    // Add image fields dynamically for image1 to 99
    for ($i = 1; $i <= 100; $i++) {
        $file_fields[] = 'image' . $i . '_low_file';
        $log_fields[] = 'image' . $i . '_low_log';
        $file_fields[] = 'image' . $i . '_high_file';
        $log_fields[] = 'image' . $i . '_high_log';
    }

    // Ensure the session arrays are initialized if not already
    if (!isset($_SESSION['file_url_' . $module_underground_id])) {
        $_SESSION['file_url_' . $module_underground_id] = [];
    }
    if (!isset($_SESSION['file_exists_' . $module_underground_id])) {
        $_SESSION['file_exists_' . $module_underground_id] = [];
    }
    if (!isset($_SESSION['log_url_' . $module_underground_id])) {
        $_SESSION['log_url_' . $module_underground_id] = [];
    }
    if (!isset($_SESSION['log_exists_' . $module_underground_id])) {
        $_SESSION['log_exists_' . $module_underground_id] = [];
    }

    // Loop through all file fields (trace and image files)
    foreach ($file_fields as $file_field) {
        $file_exists = false;  // Initialize existence flag
        $file_name = '';       // Initialize file name

        // Handle new file uploads if the form has been submitted
        if (isset($_FILES[$file_field]) && $_FILES[$file_field]['error'] == 0) {

            // Get file extension and ensure it matches allowed types
            $file_type = $_FILES[$file_field]['type'];
            if (in_array($file_type, $allowed_types)) {
                // Create a unique filename for the file
                $file_name = $file_field . '.' . pathinfo($_FILES[$file_field]['name'], PATHINFO_EXTENSION);
                $target_path = $upload_dir . $file_name;

                // Move uploaded file to target directory (this will overwrite the existing file)
                if (file_exists($target_path)) {
                    unlink($target_path); // Delete the old file
                }

                if (move_uploaded_file($_FILES[$file_field]['tmp_name'], $target_path)) {
                    // Save the file name to the session specific to this module_underground
                    $_SESSION['file_url_' . $module_underground_id][$file_field] = $base_url . '/' . $file_name;
                    $_SESSION['file_exists_' . $module_underground_id][$file_field] = true;
                } else {
                    echo "Failed to upload file: " . $file_name . '<br>';
                }
            } else {
                echo "Invalid file type for: " . $_FILES[$file_field]['name'] . '<br>';
            }
        } else {
            // No new upload, but check if there is an existing session value
            if (isset($_SESSION['file_url_' . $module_underground_id][$file_field])) {
                echo "$file_field session file_url: " . $_SESSION['file_url_' . $module_underground_id][$file_field] . '<br>';
            }
        }
    }

    // Loop through all log fields (trace and image logs)
    foreach ($log_fields as $log_field) {
        $log_exists = false;  // Initialize existence flag
        $log_name = '';       // Initialize log name

        // Handle new log uploads if the form has been submitted
        if (isset($_FILES[$log_field]) && $_FILES[$log_field]['error'] == 0) {

            // Get log extension and ensure it matches allowed types
            $log_extension = strtolower(pathinfo($_FILES[$log_field]['name'], PATHINFO_EXTENSION));

            // Check if the file has the correct extension
            if ($log_extension === 'log') {
                // Create a unique log name for the log
                $log_name = $log_field . '.' . $log_extension;
                $target_path = $upload_dir . $log_name;

                // Move uploaded log to target directory (this will overwrite the existing log)
                if (file_exists($target_path)) {
                    unlink($target_path); // Delete the old log
                }

                if (move_uploaded_file($_FILES[$log_field]['tmp_name'], $target_path)) {
                    // Save the log name to the session specific to this module_underground
                    $_SESSION['log_url_' . $module_underground_id][$log_field] = $base_url . '/' . $log_name;
                    $_SESSION['log_exists_' . $module_underground_id][$log_field] = true;
                } else {
                    echo "Failed to upload log: " . $log_name . '<br>';
                }
            } else {
                echo "Invalid file type for: " . $_FILES[$log_field]['name'] . '<br>';
            }
        } else {
            // No new upload, but check if there is an existing session value
            if (isset($_SESSION['log_url_' . $module_underground_id][$log_field])) {
                echo "$log_field session log_url: " . $_SESSION['log_url_' . $module_underground_id][$log_field] . '<br>';
            }
        }
    }

    // ======================
    // Handle Form Fields and Amplifiers
    // ======================

    // Include all relevant form fields to update
    $fields = array(
        'name',
        'status',
        'pitch_adaptor_id',
        'humidity',
        'radon',
        'activation',
        'die_A',
        'die_B',
        'die_C',
        'die_D',
        'amp_A',
        'amp_B',
        'amp_C',
        'amp_D',
        'tester',
        'test_date',
        'test_time',
        'chamber',
        'temp_low_B',
        'temp_high_B',
        'temp_low_C',
        'temp_high_C',
        'feedthru_position',
        'ACM',
        'script',
        'image_high_dir',
        'image_low_dir',
        'grade_A',
        'grade_B',
        'grade_C',
        'grade_D',
        'defects_A',
        'defects_B',
        'defects_C',
        'defects_D',
        'notes',
        'notes_A',
        'notes_B',
        'notes_C',
        'notes_D',
        'reviewer',
        'channel_A',
        'channel_B',
        'channel_C',
        'channel_D',
        'image5_low_crosstalk_comments'
    );
    $checkboxes = ['check_A', 'check_B', 'check_C', 'check_D'];
    $fields = array_merge($fields, $checkboxes);
    $fields = array_merge($fields, $trace_fields_high);
    $fields = array_merge($fields, $image_fields);

    // Iterate over each checkbox to set them to 0 if not set in POST
    foreach ($checkboxes as $checkbox) {
        $_POST[$checkbox] = isset($_POST[$checkbox]) ? $_POST[$checkbox] : 0;
    }

    // Initialize an array to hold the parts of the query
    $query_parts = array();

    // Loop through regular form fields and construct query parts
    foreach ($fields as $field) {
        // Special handling for checkboxes
        if (in_array($field, $checkboxes)) {
            // Include the checkbox even if its value is 0
            $query_parts[] = "`$field` = '" . mysql_real_escape_string($_POST[$field]) . "'";
        } else {
            // For other fields, keep the existing condition to avoid empty values
            if (array_key_exists($field, $_POST) && !empty($_POST[$field])) {
                $query_parts[] = "`$field` = '" . mysql_real_escape_string($_POST[$field]) . "'";
            }
        }
    }

    // Handle dynamic fields for amplifiers
    foreach ($ccds as $amp) {
        foreach ($trace_fields_high as $base_field) {
            $field_name = $base_field . $amp; // E.g., trace_saturation_A
            $dynamic_fields[$field_name] = isset($_POST[$field_name]) ? $_POST[$field_name] : '';
            if (!empty($dynamic_fields[$field_name])) {
                $query_parts[] = "`$field_name` = '" . mysql_real_escape_string($dynamic_fields[$field_name]) . "'";
            }
        }

        // Loop through image fields related to amplifiers
        foreach ($image_numbers_low as $number_field) {
            foreach ($image_fields as $base_field) {
                $field_name = 'image' . $number_field . $base_field . $amp; // E.g., image1_tracks_A
                $dynamic_fields[$field_name] = isset($_POST[$field_name]) ? $_POST[$field_name] : '';
                if (!empty($dynamic_fields[$field_name])) {
                    $query_parts[] = "`$field_name` = '" . mysql_real_escape_string($dynamic_fields[$field_name]) . "'";
                }
            }
        }
        foreach ($image_numbers_high as $number_field) {
            foreach ($image_fields as $base_field) {
                $field_name = 'image' . $number_field . $base_field . $amp; // E.g., image1_tracks_A
                $dynamic_fields[$field_name] = isset($_POST[$field_name]) ? $_POST[$field_name] : '';
                if (!empty($dynamic_fields[$field_name])) {
                    $query_parts[] = "`$field_name` = '" . mysql_real_escape_string($dynamic_fields[$field_name]) . "'";
                }
            }
        }
    }

    // Construct and execute the final query if there are parts
    if (!empty($query_parts)) {
        // Add the Last_update timestamp
        $query_parts[] = "`Last_update` = '" . time() . "'";

        $query = "UPDATE `" . $table . "` SET " . implode(', ', $query_parts) . " WHERE `ID` = " . $module_underground_id;
        $result = mysql_query($query);
        if (!$result) {
            echo "Generated Query: $query<br>";
            die('Query failed: ' . mysql_error());
        }
    }
}

// Fetch module_underground details again after updates
include("aux/get_module_underground_vals.php");
mysql_close($connection);

// Function to generate a dropdown menu from an array
function generate_dropdown($name, $options, $selected_value)
{
    echo '<select name="' . $name . '">';
    foreach ($options as $value) {
        echo '<option value="' . $value . '"' . ($value == $selected_value ? ' selected' : '') . '>' . $value . '</option>';
    }
    echo '</select>';
}

// File existence check
function check_and_update_file_session($file_field, $upload_dir, $base_url, $module_underground_id)
{
    $upload_dir = isset($_SESSION['upload_dir']) ? $_SESSION['upload_dir'] : '';
    $base_url = isset($_SESSION['base_url']) ? $_SESSION['base_url'] : '';

    // Construct the file name based on the file field
    $file_name = $file_field . '.png';
    $file_path = $upload_dir . $file_name;

    // Check if the file exists on the server
    if (file_exists($file_path)) {
        // If the file exists, set the session values
        $_SESSION['file_url'][$file_field] = $base_url . '/' . $file_name;
        $_SESSION['file_exists'][$file_field] = true;
    } else {
        // If the file does not exist, clear the session values
        $_SESSION['file_exists'][$file_field] = false;
        unset($_SESSION['file_url'][$file_field]);
    }
}

// Log existence check
function check_and_update_log_session($log_field, $upload_dir, $base_url, $module_underground_id)
{
    $upload_dir = isset($_SESSION['upload_dir']) ? $_SESSION['upload_dir'] : '';
    $base_url = isset($_SESSION['base_url']) ? $_SESSION['base_url'] : '';

    // Construct the log name based on the log field
    $log_name = $log_field . '.log';
    $log_path = $upload_dir . $log_name;

    // Check if the log exists on the server
    if (log_exists($log_path)) {
        // If the log exists, set the session values
        $_SESSION['log_url'][$log_field] = $base_url . '/' . $log_name;
        $_SESSION['log_exists'][$log_field] = true;
    } else {
        // If the log does not exist, clear the session values
        $_SESSION['log_exists'][$log_field] = false;
        unset($_SESSION['log_url'][$log_field]);
    }
}
?>

<!-- HTML Form for Data Input -->
<table border="1" cellpadding="2" width="100%">
    <tr>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <th>
                <input type="submit" name="new" value="New MODULE" title="Generate a new MODULE entry" style="font-size: 10pt">
            </th>
        </form>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <th>
                &nbsp;&nbsp;&nbsp;&nbsp;Choose MODULE_UNDERGROUND ID: <input type="text" name="choosen" size="6">
            </th>
        </form>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <th>
                <input type="submit" name="first" value="Goto First MODULE" title="Go to the first added MODULE" style="font-size: 10pt">
                <input type="submit" name="prev" value="Goto Previous MODULE" title="Go to the MODULE before the currently selected one" style="font-size: 10pt">
                <input type="submit" name="next" value="Goto Next MODULE" title="Go to the MODULE after the currently selected one" style="font-size: 10pt">
                <input type="submit" name="last" value="Goto Last MODULE" title="Go to the last added MODULE" style="font-size: 10pt">
            </th>
        </form>
    </tr>
</table>
<br><br>
<?php include("aux/table_selection.php") ?>
<div class="section-block" data-section="preliminary" data-label="Preliminary">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1" cellpadding="2" width="100%">
            <tr>
                <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                    MODULE_UNDERGROUND ID: <?php echo $id; ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    Name: <input type="text" name="name" value="<?php echo $name; ?>" size="20">
                </td>
                <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                    Status:
                    <?php generate_dropdown('status', $status_array, $status); ?>
                </td>
                <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                    Entry Last updated: <?php echo date("G:i:s M d, Y", $last_update); ?> (ET)
                </td>
            </tr>
            <tr>
                <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                    Pitch adaptor ID: <input type="text" name="pitch_adaptor_id" value="<?php echo $pitch_adaptor_id; ?>" size="20">
                </td>
                <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                    UW Activation [days]: <input type="text" name="activation" value="<?php echo $activation; ?>" size="10">
                </td>
            </tr>
            <tr>
                <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                    Packaging humidity [%]: <input type="text" name="humidity" value="<?php echo $humidity; ?>" size="10">
                </td>
                <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                    Packaging radon [Bq/m^3]: <input type="text" name="radon" value="<?php echo $radon; ?>" size="10">
                </td>
            </tr>
        </table>
        <input type="submit" id="hiddenSubmit" style="display: none;">
    </form>
    <br><br>


    <!-- Module layout -->
    <?php echo "<b>Module layout</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1" cellpadding="2" width="100%">
            <tr>
                <td>
                    <?php if (!empty($die_A)): ?>
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                            DIE ID
                            <input type="hidden" name="go" value="<?php echo htmlspecialchars($die_A); ?>">
                            <input type="submit" value="A" style="font-size: 14pt;">
                        </form>
                    <?php else: ?>
                        DIE ID A
                    <?php endif; ?>
                    <input type="text" name="die_A" value="<?php echo htmlspecialchars($die_A); ?>" size="10">
                    &nbsp&nbsp&nbsp&nbsp; Amp A<?php generate_dropdown('amp_A', $amp_array, $amp_A); ?>
                </td>
                <td>
                    <?php if (!empty($die_B)): ?>
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                            DIE ID
                            <input type="hidden" name="go" value="<?php echo htmlspecialchars($die_B); ?>">
                            <input type="submit" value="B" style="font-size: 14pt;">
                        </form>
                    <?php else: ?>
                        DIE ID B
                    <?php endif; ?>
                    <input type="text" name="die_B" value="<?php echo htmlspecialchars($die_B); ?>" size="10">
                    &nbsp&nbsp&nbsp&nbsp; Amp B<?php generate_dropdown('amp_B', $amp_array, $amp_B); ?>
                </td>
                <td>
                    <?php if (!empty($die_C)): ?>
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                            DIE ID
                            <input type="hidden" name="go" value="<?php echo htmlspecialchars($die_C); ?>">
                            <input type="submit" value="C" style="font-size: 14pt;">
                        </form>
                    <?php else: ?>
                        DIE ID C
                    <?php endif; ?>
                    <input type="text" name="die_C" value="<?php echo htmlspecialchars($die_C); ?>" size="10">
                    &nbsp&nbsp&nbsp&nbsp; Amp C<?php generate_dropdown('amp_C', $amp_array, $amp_C); ?>
                </td>
                <td>
                    <?php if (!empty($die_D)): ?>
                        <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                            DIE ID
                            <input type="hidden" name="go" value="<?php echo htmlspecialchars($die_D); ?>">
                            <input type="submit" value="D" style="font-size: 14pt;">
                        </form>
                    <?php else: ?>
                        DIE ID D
                    <?php endif; ?>
                    <input type="text" name="die_D" value="<?php echo htmlspecialchars($die_D); ?>" size="10">
                    &nbsp&nbsp&nbsp&nbsp; Amp D<?php generate_dropdown('amp_D', $amp_array, $amp_D); ?>
                </td>
            </tr>
            <tr>
                <td colspan="12" style="text-align: center;">
                    <input type="submit" value="Submit">
                </td>
            </tr>
    </form>
    </table>

    <?php
    // Handle form submission to set the session variable and redirect
    if (isset($_POST['go'])) {
        // Set the session variable for `choosen_die` to the DIE ID (die_A, die_B, etc.) selected
        $_SESSION['choosen_die'] = $_POST['go'];

        // Redirect to edit_die.php
        echo '<script>window.location.href = "edit_die.php";</script>';
        exit(); // Ensure no further code runs
    }
    ?>
    <br><br>


    <!-- Preliminary Grade Assessment -->
    <?php echo "<b>Preliminary Grade Assessment</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1" cellpadding="2" width="100%">
            <tr>
                <td><?php echo "A"; ?></td>
                <td style="width: 10%;">
                    <?php generate_dropdown('grade_A', $grade_array, $grade_A); ?>
                    <!-- <?php echo "Matched?"; ?> -->
                    <!-- <?php generate_dropdown('defects_A', $yes_no_blank_array, $defects_A); ?> -->
                    <!-- <input type="checkbox" name="check_A" value="1" <?php echo ($check_A == 1) ? 'checked' : ''; ?>> -->
                </td>
                <td style="width: 5%;">
                    <input type="hidden" name="check_A" value="0">
                    <?php echo "ch"; ?>
                    <?php generate_dropdown('channel_A', $channels, $channel_A); ?>

                </td>

                <td><?php echo "B"; ?></td>
                <td style="width: 10%;">
                    <?php generate_dropdown('grade_B', $grade_array, $grade_B); ?>
                    <!-- <?php echo "Matched?"; ?> -->
                    <!-- <?php generate_dropdown('defects_B', $yes_no_blank_array, $defects_B); ?> -->
                    <!-- <input type="checkbox" name="check_B" value="1" <?php echo ($check_B == 1) ? 'checked' : ''; ?>> -->

                </td>
                <td style="width: 5%;">
                    <input type="hidden" name="check_B" value="0">

                    <?php echo "ch"; ?>
                    <?php generate_dropdown('channel_B', $channels, $channel_B); ?>

                </td>

                <td><?php echo "C"; ?></td>
                <td style="width: 10%;">
                    <?php generate_dropdown('grade_C', $grade_array, $grade_C); ?>
                    <!-- <?php echo "Matched?"; ?> -->
                    <!-- <?php generate_dropdown('defects_C', $yes_no_blank_array, $defects_C); ?> -->
                    <!-- <input type="checkbox" name="check_C" value="1" <?php echo ($check_C == 1) ? 'checked' : ''; ?>> -->

                </td>
                <td style="width: 5%;">
                    <input type="hidden" name="check_C" value="0">
                    <?php echo "ch"; ?>
                    <?php generate_dropdown('channel_C', $channels, $channel_C); ?>

                </td>

                <td><?php echo "D"; ?></td>
                <td style="width: 10%;">
                    <?php generate_dropdown('grade_D', $grade_array, $grade_D); ?>
                    <!-- <?php echo "Matched?"; ?> -->
                    <!-- <?php generate_dropdown('defects_D', $yes_no_blank_array, $defects_D); ?> -->
                    <!-- <input type="checkbox" name="check_D" value="1" <?php echo ($check_D == 1) ? 'checked' : ''; ?>> -->
                </td>
                <td style="width: 5%;">
                    <input type="hidden" name="check_D" value="0">
                    <?php echo "ch"; ?>
                    <?php generate_dropdown('channel_D', $channels, $channel_D); ?>

                </td>
            </tr>
            <tr>
                <td colspan="3">
                    <textarea name="notes_A" rows="2" style="width: 100%;"><?php echo $notes_A; ?></textarea>
                </td>
                <td colspan="3">
                    <textarea name="notes_B" rows="2" style="width: 100%;"><?php echo $notes_B; ?></textarea>
                </td>
                <td colspan="3">
                    <textarea name="notes_C" rows="2" style="width: 100%;"><?php echo $notes_C; ?></textarea>
                </td>
                <td colspan="3">
                    <textarea name="notes_D" rows="2" style="width: 100%;"><?php echo $notes_D; ?></textarea>
                </td>
            <tr>
            </tr>
            <td colspan="2">
                Reviewer: <input type="text" name="reviewer" rows="1" value="<?php echo $reviewer; ?>" size="20">
            </td>

            <td colspan="10">
                Notes: <textarea name="notes" rows="1" style="width: 90%;"><?php echo $notes; ?></textarea>
            </td>
            </tr>
            <tr>
                <td colspan="12" style="text-align: center;">
                    <input type="submit" value="Submit">
                </td>
            </tr>
        </table>
    </form>
    <br><br>

    <!-- Testing Section -->
    <?php echo " $name <b>Testing</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1" cellpadding="2" width="100%">
            <tr>
                <td width="50"> Tester: <input type="text" name="tester" value="<?php echo $tester; ?>" size="30"></td>
                <td width="50">
                    Test Date: <input type="date" name="test_date" value="<?php echo $test_date; ?>">
                </td>
                <td width="50">
                    Test Time [CET]: <input type="time" name="test_time" value="<?php echo $test_time; ?>">
                </td>
                <td width="50">
                    Chamber: <?php generate_dropdown('chamber', $chamber_array, $chamber); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    Low Temp B [K]: <input type="text" name="temp_low_B" value="<?php echo $temp_low_B; ?>" size="8">
                    High Temp B [K]: <input type="text" name="temp_high_B" value="<?php echo $temp_high_B; ?>" size="8">
                    Low Temp C [K]: <input type="text" name="temp_low_C" value="<?php echo $temp_low_C; ?>" size="8">
                    High Temp C [K]: <input type="text" name="temp_high_C" value="<?php echo $temp_high_C; ?>" size="8">
                </td>
                <td>
                    Feedthru Position: <?php generate_dropdown('feedthru_position', $feedthru_positions, $feedthru_position); ?>
                </td>
                <td>
                    ACM Number: <?php generate_dropdown('ACM', $ACM_numbers, $ACM); ?>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    Scripts Directory: <input type="text" name="script" value="<?php echo $script; ?>" size="80">
                </td>
            </tr>
            <tr>
                <td colspan="4">
                    Image High Directory: <input type="text" name="image_high_dir" value="<?php echo $image_high_dir; ?>" size="60">
                    Image Low Directory: <input type="text" name="image_low_dir" value="<?php echo $image_low_dir; ?>" size="60">
                </td>
            </tr>

            <tr>
                <td colspan="4" align="center" style="white-space: nowrap;">
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';  // This should be the actual server file path
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL

                    // here 'high' was kept only because of the old version, high and low images uses the same sequencer
                    $seq_file_name = 'seq_high_file.seq';
                    $seq_high_path = $upload_dir . $seq_file_name;
                    $bcf_file_name = 'bcf_high_file.bcf';
                    $bcf_file_path = $upload_dir . $bcf_file_name;
                    if (file_exists($seq_high_path) && !isset($_SESSION['file_url_' . $module_underground_id]['seq_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['seq_high_file'] = $base_url . $seq_file_name;  // Use base_url for the web link
                    }
                    if (!empty($seq_file_name) && file_exists($seq_high_path)) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['seq_high_file'];
                    } else {
                        $file_exists = false;
                    }
                    if (file_exists($bcf_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['bcf_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['bcf_high_file'] = $base_url . $bcf_file_name;  // Use base_url for the web link
                    }
                    if (!empty($bcf_file_name) && file_exists($bcf_file_path)) {
                        $file_exists2 = true;
                        $file_url2 = $_SESSION['file_url_' . $module_underground_id]['bcf_high_file'];
                    } else {
                        $file_exists2 = false;
                    }

                    ?>
                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="seq_high_file" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label id="seq_high_file" for="seq_high_file">sequencer file:</label>
                    <input type="file" name="seq_high_file" accept=".seq, application/octet-stream">
                    &nbsp; &nbsp; &nbsp; &nbsp;

                    <?php if ($file_exists2): ?>
                        <a href="<?php echo htmlspecialchars($file_url2); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="bcf_high_file" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label id="bcf_high_file" for="bcf_high_file">bcf file:</label>
                    <input type="file" name="bcf_high_file" accept=".bcf, application/octet-stream">
                    &nbsp; &nbsp; &nbsp; &nbsp;
                </td>
            </tr>
            <tr>
                <td colspan="4" style="text-align: center;">
                    <input type="submit" value="Submit">
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<!-- HIGH TEMP -->
<!-- Trace Section -->
<div class="section-block" data-section="trace" data-label="Trace">
    <?php echo "$name <b>Trace and PSD, High Temp</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#trace" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" colspan="1" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" colspan="1" style="width: 20%; white-space: nowrap;">Saturation/Low? [~80000 ADU]</td>
                <td align="center" colspan="1" style="width: 30%; white-space: nowrap;">Comments</td>
                <td align="center" colspan="1" style="width: 30%; white-space: nowrap;">Reference Image</td>
                <td align="center" colspan="1" style="width: 10%; white-space: nowrap;">PSD</td>
            </tr>
            <?php

            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center" style="width: 10%"> <?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <?php generate_dropdown('trace_high_saturation_' . $amp, $yes_no_blank_array, ${'trace_high_saturation_' . $amp}); ?> </td>
                    <td align="center"><input type="text" style="width: 90%" name="trace_high_comments_<?php echo $amp; ?>" value="<?php echo ${'trace_high_comments_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 80%" name="trace_high_reference_<?php echo $amp; ?>" value="<?php echo ${'trace_high_reference_' . $amp}; ?>" size="50"></td>
                    <td align="center" style="white-space: nowrap;">
                        <?php
                        // Retrieve the current module_underground_id
                        $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                        $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';  // This should be the actual server file path
                        $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL

                        // Check if psd_high_file already exists in the directory even if no form is submitted
                        $psd_high_file_name = 'psd_high_file_' . $amp . '.png';
                        $psd_high_file_path = $upload_dir . $psd_high_file_name;  // Use the absolute server path for file_exists()

                        // If psd_high_file exists in the directory but no session is set, initialize the session
                        if (file_exists($psd_high_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['psd_high_file_' . $amp])) {
                            $_SESSION['file_url_' . $module_underground_id]['psd_high_file_' . $amp] = $base_url . $psd_high_file_name;  // Use base_url for the web link
                        }
                        // Check if the file exists on the server (file system path)
                        if (!empty($psd_high_file_name) && file_exists($psd_high_file_path)) {
                            // File exists, keep the session variables and show the icon
                            $file_exists = true;
                            $file_url = $_SESSION['file_url_' . $module_underground_id]['psd_high_file_' . $amp];
                        } else {
                            $file_exists = false;
                        }
                        ?>
                        <?php if ($file_exists): ?>
                            <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                                <img src="pixmaps/icon.png" alt="PSD File" style="height: 20px; width: auto;">
                            </a>
                        <?php endif; ?>
                        <input type="file" name="psd_high_file_<?php echo htmlspecialchars($amp); ?>" accept="image/png">
                        &nbsp; &nbsp; &nbsp; &nbsp;
                    </td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td colspan="9" align="left" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;

                    // Absolute path on the server's file system
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';  // This should be the actual server file path
                    // Web URL for accessing files via the browser
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL

                    // Check if trace_high_file already exists in the directory even if no form is submitted
                    $trace_high_file_name = 'trace_high_file.png';
                    $trace_high_file_path = $upload_dir . $trace_high_file_name;  // Use the absolute server path for file_exists()
                    // If trace_high_file exists in the directory but no session is set, initialize the session
                    if (file_exists($trace_high_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['trace_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['trace_high_file'] = $base_url . $trace_high_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($trace_high_file_name) && file_exists($trace_high_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['trace_high_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        $file_exists = false;
                    }

                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Trace File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="trace_high_file">Image File:</label>
                    <input type="file" name="trace_high_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image1high" data-label="Image1">
    <!-- Image 1 - Track Defects-->
    <?php echo "$name <b> Image 1, High Temp - [1 skip, 20x20 binning, 80rx320c, Active Region, 3s Exposure] - Aim: To see tracks</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image1high" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 7%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Tracks?</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Defects?</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Noise [ADU]</td>
                <td align="center" style="width: 33%; white-space: nowrap;">Comments</td>
            </tr>
            <?php

            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <?php generate_dropdown('image1_high_tracks_' . $amp, $yes_no_blank_array, ${'image1_high_tracks_' . $amp}); ?> </td>
                    <td align="center">
                        <?php generate_dropdown('image1_high_defects_' . $amp, $yes_no_blank_array, ${'image1_high_defects_' . $amp}); ?>
                    </td>
                    <td align="center"><input type="text" name="image1_high_noise_<?php echo $amp; ?>" value="<?php echo ${'image1_high_noise_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image1_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image1_high_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>
            <!-- row for image1_ref, there is only one file used -->
            <tr>
                <td align="center" style="border: none; white-space: nowrap;"> Reference Image: </td>
                <td align=" left" colspan="9" style="border: none; white-space: nowrap;"> <input type="text" style="width: 98%;" name="image1_high_reference_A" value="<?php echo $image1_high_reference_A; ?>" </td>
            </tr>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL

                    // Check if image1_high_file already exists in the directory even if no form is submitted
                    $image1_high_file_name = 'image1_high_file.png';
                    $image1_high_file_path = $upload_dir . $image1_high_file_name;  // Use the absolute server path for file_exists()

                    // If image1_high_file exists in the directory but no session is set, initialize the session
                    if (file_exists($image1_high_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['image1_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image1_high_file'] = $base_url . $image1_high_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($image1_high_file_name) && file_exists($image1_high_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image1_high_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        // echo $image1_high_file_path;
                        $file_exists = false;
                    }
                    ?>
                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image1_High File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image1_high_file">Image File:</label>
                    <input type="file" name="image1_high_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image2high" data-label="Image2">
    <?php echo " $name <b>Image 2, High Temp - [1 skip, 1x1 binning, 30rx6400c, Serial Register, 10s Exposure] - Aim: Serial Register Defects</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image2high" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Number Column Defects</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Noise [ADU]</td>
                <td align="center" style="width: 30%; white-space: nowrap;">Comments</td>
            </tr>
            <?php

            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image2_high_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image2_high_column_defects_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image2_high_noise_<?php echo $amp; ?>" value="<?php echo ${'image2_high_noise_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image2_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image2_high_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>
            <!-- row for image2_ref, there is only one file used. use the field for ampA. but is actually same for all channels -->
            <tr>
                <td align="center" style="border: none; white-space: nowrap;"> Reference Image: </td>
                <td align=" left" colspan="9" style="border: none; white-space: nowrap;"> <input type="text" style="width: 98%;" name="image2_high_reference_A" value="<?php echo $image2_high_reference_A; ?>" </td>
            </tr>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;

                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL

                    // Check if image2_high_file already exists in the directory even if no form is submitted
                    $image2_high_file_name = 'image2_high_file.png';
                    $image2_high_file_path = $upload_dir . $image2_high_file_name;  // Use the absolute server path for file_exists()
                    $image2_high_log_name = 'image2_high_log.log';
                    $image2_high_log_path = $upload_dir . $image2_high_log_name;  // Use the absolute server path for file_exists()

                    // If image2_high_file exists in the directory but no session is set, initialize the session
                    if (file_exists($image2_high_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['image2_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image2_high_file'] = $base_url . $image2_high_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($image2_high_file_name) && file_exists($image2_high_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image2_high_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        $file_exists = false;
                    }

                    // If image2_high_log exists in the directory but no session is set, initialize the session
                    if (file_exists($image2_high_log_path) && !isset($_SESSION['log_url_' . $module_underground_id]['image2_high_log'])) {
                        $_SESSION['log_url_' . $module_underground_id]['image2_high_log'] = $base_url . $image2_high_log_name;  // Use base_url for the web link
                    }

                    // Check if the log exists on the server (log system path)
                    if (!empty($image2_high_log_name) && file_exists($image2_high_log_path)) {
                        // Log exists, keep the session variables and show the icon
                        $log_exists = true;
                        $log_url = $_SESSION['log_url_' . $module_underground_id]['image2_high_log'];
                    } else {
                        // Log does not exist, clear the session variables and remove the icon
                        $log_exists = false;
                    }

                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image2_High File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image2_high_file">Image File:</label>
                    <input type="file" name="image2_high_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;

                    <?php if ($log_exists): ?>
                        <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="Image2_High Log" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image2_high_log">Log File:</label>
                    <input type="file" name="image2_high_log" accept=".log,text/plain">
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image31high" data-label="Image3-1 High">
    <!-- Image 3 - Single Electron Resolution -->
    <?php echo " $name <b>Image 3-1, High Temp - [500 skip, 1x1 binning, 30rx640c, Serial Register, 0s Exposure] - Aim: Single Electron Resolution</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image31high" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [ADU]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [e-]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Gain [ADU/e]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [ADU/bin/img]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [e-/pix/day]</td>
                <td align="center" style="width: 25%; white-space: nowrap;">Comments</td>
                <td align="center" style="width: 20%; white-space: nowrap;">Reference Image</td>
            </tr>
            <?php

            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_high_res_<?php echo $amp; ?>" value="<?php echo ${'image31_high_res_' . $amp}; ?>"></td>
                    <td align="center"><?php echo htmlspecialchars(${'image31_high_res_e_' . $amp}); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_high_gain_<?php echo $amp; ?>" value="<?php echo ${'image31_high_gain_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_high_dark_current_<?php echo $amp; ?>" value="<?php echo ${'image31_high_dark_current_' . $amp}; ?>"></td>
                    <td align="center"><?php echo number_format(${'image31_high_dark_current_' . $amp} / ${'image31_high_gain_' . $amp} * 86400 / 341, 2); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image31_high_comments_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_high_reference_<?php echo $amp; ?>" value="<?php echo ${'image31_high_reference_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL
                    $image31_high_file_name = 'image31_high_file.png';
                    $image31_high_file_path = $upload_dir . $image31_high_file_name;  // Use the absolute server path for file_exists()

                    // If image31_high_file exists in the directory but no session is set, initialize the session
                    if (file_exists($image31_high_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['image31_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image31_high_file'] = $base_url . $image31_high_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($image31_high_file_name) && file_exists($image31_high_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image31_high_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        $file_exists = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image3_High File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image31_high_file">Image File:</label>
                    <input type="file" name="image31_high_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image32high" data-label="Image3-2 High">
    <!-- Image 32 - Single Electron Resolution -->
    <?php echo "$name <b>Image 3-2, High Temp - [1000 skip, 1x1 binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution </b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image32high" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [ADU]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [e-]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Gain [ADU/e]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [ADU/bin/img]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [e-/pix/day]</td>
                <td align="center" style="width: 25%; white-space: nowrap;">Comments</td>
                <td align="center" style="width: 20%; white-space: nowrap;">Reference Image</td>
            </tr>
            <?php

            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_high_res_<?php echo $amp; ?>" value="<?php echo ${'image32_high_res_' . $amp}; ?>"></td>
                    <td align="center"><?php echo htmlspecialchars(${'image32_high_res_e_' . $amp}); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_high_gain_<?php echo $amp; ?>" value="<?php echo ${'image32_high_gain_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_high_dark_current_<?php echo $amp; ?>" value="<?php echo ${'image32_high_dark_current_' . $amp}; ?>"></td>
                    <td align="center"><?php echo number_format(${'image32_high_dark_current_' . $amp} / ${'image32_high_gain_' . $amp} * 86400 / 654, 2); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image32_high_comments_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_high_reference_<?php echo $amp; ?>" value="<?php echo ${'image32_high_reference_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL
                    $image32_high_file_name = 'image32_high_file.png';
                    $image32_high_file_path = $upload_dir . $image32_high_file_name;  // Use the absolute server path for file_exists()
                    // If image32_high_file exists in the directory but no session is set, initialize the session
                    if (file_exists($image32_high_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['image32_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image32_high_file'] = $base_url . $image32_high_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($image32_high_file_name) && file_exists($image32_high_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image32_high_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        $file_exists = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image3_High File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image32_high_file">Image File:</label>
                    <input type="file" name="image32_high_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image4high" data-label="Image4 High">
    <!-- Image 4 - Track Defects, High VSub and V Clk -->
    <?php echo "$name <b>Image 4, High Temp - [1 skip, 1x1 binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise, CCD channel mapping</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image4high" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 8%; white-space: nowrap;">Pixel Defects</td>
                <td align="center" style="width: 8%; white-space: nowrap;">Column Defects</td>
                <td align="center" style="width: 8%; white-space: nowrap;">Defect Region</td>
                <td align="center" style="width: 5%; white-space: nowrap;"> Noise [ADU]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">CTI-Code</td>
                <td align="center" style="width: 7%; white-space: nowrap;">CTI</td>
                <td align="center" style="width: 10%; white-space: nowrap;">SharpTrack</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Comments</td>
            </tr>
            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center" style="width: 10%"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"><input type="text" style="width: 90%" name="image4_high_pixel_defects_<?php echo $amp; ?>" value="<?php echo ${'image4_high_pixel_defects_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%" name="image4_high_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image4_high_column_defects_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%" name="image4_high_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image4_high_region_defect_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%" name="image4_high_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image4_high_noise_overscan_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%" name="image4_high_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image4_high_cti_code_' . $amp}; ?>"></td>
                    <td align="center">
                        <?php generate_dropdown('image4_high_cti_visual_' . $amp, $yes_no_blank_array, ${'image4_high_cti_visual_' . $amp}); ?>
                    </td>
                    <td align="center">
                        <?php generate_dropdown('image4_high_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image4_high_sharpness_tracks_' . $amp}); ?>
                    </td>
                    <td align="center"><input type="text" style="width: 90%" name="image4_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image4_high_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>
            <!-- row for image4_ref, there is only one file used. use the field for ampA. but is actually same for all channels -->
            <tr>
                <td align="center" style="border: none; white-space: nowrap;"> Reference Image: </td>
                <td align=" left" colspan="9" style="border: none; white-space: nowrap;"> <input type="text" style="width: 98%;" name="image4_high_reference_A" value="<?php echo $image4_high_reference_A; ?>" </td>
            </tr>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;

                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL

                    // Check if image4_high_file already exists in the directory even if no form is submitted
                    $image4_high_file_name = 'image4_high_file.png';
                    $image4_high_file_path = $upload_dir . $image4_high_file_name;  // Use the absolute server path for file_exists()
                    $image4_high_log_name = 'image4_high_log.log';
                    $image4_high_log_path = $upload_dir . $image4_high_log_name;  // Use the absolute server path for file_exists()

                    // If image4_high_file exists in the directory but no session is set, initialize the session
                    if (file_exists($image4_high_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['image4_high_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image4_high_file'] = $base_url . $image4_high_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($image4_high_file_name) && file_exists($image4_high_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image4_high_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        $file_exists = false;
                    }

                    // If image4_high_log exists in the directory but no session is set, initialize the session
                    if (file_exists($image4_high_log_path) && !isset($_SESSION['log_url_' . $module_underground_id]['image4_high_log'])) {
                        $_SESSION['log_url_' . $module_underground_id]['image4_high_log'] = $base_url . $image4_high_log_name;  // Use base_url for the web link
                    }

                    // Check if the log exists on the server (log system path)
                    if (!empty($image4_high_log_name) && file_exists($image4_high_log_path)) {
                        // Log exists, keep the session variables and show the icon
                        $log_exists = true;
                        $log_url = $_SESSION['log_url_' . $module_underground_id]['image4_high_log'];
                    } else {
                        // Log does not exist, clear the session variables and remove the icon
                        $log_exists = false;
                    }

                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image4_High File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image4_high_file">Image File:</label>
                    <input type="file" name="image4_high_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;

                    <?php if ($log_exists): ?>
                        <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="Image4_High Log" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image4_high_log">Log File:</label>
                    <input type="file" name="image4_high_log" accept=".log,text/plain">
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image31low" data-label="Image3-1 Low">
    <!-- Image 3-1 - Single Electron Resolution -->
    <?php echo "$name <b>Image 3-1, Low Temp - [500 skip, 1x10 binning, 30rx640c, Serial Register, 0s Exposure] - Aim: Single Electron Resolution</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image31low" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [ADU]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [e-]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Gain [ADU/e]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [ADU/bin/img]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [e-/pix/day]</td>
                <td align="center" style="width: 25%; white-space: nowrap;">Comments</td>
                <td align="center" style="width: 20%; white-space: nowrap;">Reference Image</td>
            </tr>
            <?php

            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_low_res_<?php echo $amp; ?>" value="<?php echo ${'image31_low_res_' . $amp}; ?>"></td>
                    <td align="center"><?php echo htmlspecialchars(${'image31_low_res_e_' . $amp}); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_low_gain_<?php echo $amp; ?>" value="<?php echo ${'image31_low_gain_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_low_dark_current_<?php echo $amp; ?>" value="<?php echo ${'image31_low_dark_current_' . $amp}; ?>"></td>
                    <td align="center"><?php echo number_format(${'image31_low_dark_current_' . $amp} / ${'image31_low_gain_' . $amp} * 86400 / 371 / 10, 2); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image31_low_comments_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image31_low_reference_<?php echo $amp; ?>" value="<?php echo ${'image31_low_reference_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL


                    $image31_low_file_name = 'image31_low_file.png';
                    $image31_low_file_path = $upload_dir . $image31_low_file_name;  // Use the absolute server path for file_exists()

                    // If image31_low_file exists in the directory but no session is set, initialize the session
                    if (file_exists($image31_low_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['image31_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image31_low_file'] = $base_url . $image31_low_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($image31_low_file_name) && file_exists($image31_low_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image31_low_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        $file_exists = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image31_Low File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image31_low_file">Image File:</label>
                    <input type="file" name="image31_low_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image32low" data-label="Image3-2 Low">
    <!-- Image 3-2 - Single Electron Resolution -->
    <?php echo "$name <b>Image 3-2, Low Temp - [1000 skip, 1x10 binning, 30rx640c, Serial Register, 0s Exposure] - Aim: Single Electron Resolution</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image32low" method="post" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [ADU]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Resolution [e-]</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Gain [ADU/e]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [ADU/bin/img]</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [e-/pix/day]</td>
                <td align="center" style="width: 25%; white-space: nowrap;">Comments</td>
                <td align="center" style="width: 20%; white-space: nowrap;">Reference Image</td>
            </tr>
            <?php

            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_low_res_<?php echo $amp; ?>" value="<?php echo ${'image32_low_res_' . $amp}; ?>"></td>
                    <td align="center"><?php echo htmlspecialchars(${'image32_low_res_e_' . $amp}); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_low_gain_<?php echo $amp; ?>" value="<?php echo ${'image32_low_gain_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_low_dark_current_<?php echo $amp; ?>" value="<?php echo ${'image32_low_dark_current_' . $amp}; ?>"></td>
                    <td align="center"><?php echo number_format(${'image32_low_dark_current_' . $amp} / ${'image32_low_gain_' . $amp} * 86400 / 684 / 10, 2); ?></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image32_low_comments_' . $amp}; ?>"></td>
                    <td align="center"><input type="text" style="width: 90%;" name="image32_low_reference_<?php echo $amp; ?>" value="<?php echo ${'image32_low_reference_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    // Retrieve the current module_underground_id
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/'; // Web URL
                    $image32_low_file_name = 'image32_low_file.png';
                    $image32_low_file_path = $upload_dir . $image32_low_file_name;  // Use the absolute server path for file_exists()
                    // If image32_low_file exists in the directory but no session is set, initialize the session
                    if (file_exists($image32_low_file_path) && !isset($_SESSION['file_url_' . $module_underground_id]['image32_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image32_low_file'] = $base_url . $image32_low_file_name;  // Use base_url for the web link
                    }

                    // Check if the file exists on the server (file system path)
                    if (!empty($image32_low_file_name) && file_exists($image32_low_file_path)) {
                        // File exists, keep the session variables and show the icon
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image32_low_file'];
                    } else {
                        // File does not exist, clear the session variables and remove the icon
                        $file_exists = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image32_Low File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image32_low_file">Image File:</label>
                    <input type="file" name="image32_low_file" accept="image/png">
                    &nbsp; &nbsp; &nbsp; &nbsp;
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>

<div class="section-block" data-section="image4low" data-label="Image4 Low">
    <!-- Image4 Low Temp -->
    <?php echo "$name <b>Image 4, Low Temp - [1 skip, 1x1 binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise, CCD channel mapping</b>"; ?>
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image4low" method="post" enctype="multipart/form-data"> <input type="hidden" name="id" value="<?php echo $id; ?>">
        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Defects?</td>
                <td align="center" style="width: 10%; white-space: nowrap;">CTI? - Visual</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 1 [keV] </td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 2 [keV] </td>
                <td align="center" style="width: 10%; white-space: nowrap;">Comments</td>
            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <?php generate_dropdown('image4_low_defects_' . $amp, $yes_no_blank_array, ${'image4_low_defects_' . $amp}); ?> </td>
                    <td align="center"> <?php generate_dropdown('image4_low_cti_visual_' . $amp, $yes_no_blank_array, ${'image4_low_cti_visual_' . $amp}); ?> </td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_peak1_<?php echo $amp; ?>" value="<?php echo ${'image4_low_peak1_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_peak2_<?php echo $amp; ?>" value="<?php echo ${'image4_low_peak2_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image4_low_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>
            <!-- row for image4_ref, there is only one file used. use the field for ampA. but is actually same for all channels -->
            <tr>
                <td align="center" style="border: none; white-space: nowrap;"> Reference Image: </td>
                <td align=" left" colspan="9" style="border: none; white-space: nowrap;"> <input type="text" style="width: 98%;" name="image4_low_reference_A" value="<?php echo $image4_low_reference_A; ?>" </td>
            </tr>
            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image41_low_file_name'} = "image41_low_file.png";
                    ${'image41_low_file_path'} = $upload_dir . ${'image41_low_file_name'};
                    ${'image42_low_file_name'} = "image42_low_file.png";
                    ${'image42_low_file_path'} = $upload_dir . ${'image42_low_file_name'};
                    ${'image42_low_log_name'} = "image42_low_log.log";
                    ${'image42_low_log_path'} = $upload_dir . ${'image42_low_log_name'};

                    if (file_exists(${'image41_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image41_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image41_low_file'] = $base_url . ${'image41_low_file_name'};
                    }
                    if (!empty(${'image41_low_file_name'}) && file_exists(${'image41_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image41_low_file'];
                    } else {
                        $file_exists = false;
                    }

                    if (file_exists(${'image42_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image42_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image42_low_file'] = $base_url . ${'image42_low_file_name'};
                    }
                    if (!empty(${'image42_low_file_name'}) && file_exists(${'image42_low_file_path'})) {
                        $file_exists2 = true;
                        $file_url2 = $_SESSION['file_url_' . $module_underground_id]['image42_low_file'];
                    } else {
                        $file_exists2 = false;
                    }

                    if (file_exists(${'image42_low_log_path'}) && !isset($_SESSION['log_url_' . $module_underground_id]['image42_low_log'])) {
                        $_SESSION['log_url_' . $module_underground_id]['image42_low_log'] = $base_url . ${'image42_low_log_name'};
                    }
                    if (!empty(${'image42_low_log_name'}) && file_exists(${'image42_low_log_path'})) {
                        $log_exists2 = true;
                        $log_url2 = $_SESSION['log_url_' . $module_underground_id]['image42_low_log'];
                    } else {
                        $log_exists2 = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 41_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image41_low_file"> Composite Images:</label>
                    <input type="file" name="image41_low_file" accept="image/png">

                    <?php if ($file_exists2): ?>
                        <a href="<?php echo htmlspecialchars($file_url2); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 42_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image42_low_file">Energy_Peaks:</label>
                    <input type="file" name="image42_low_file" accept="image/png">

                    <?php if ($log_exists2): ?>
                        <a href="<?php echo htmlspecialchars($log_url2); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="Image 42_Low Log" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image42_low_log">Defect Log:</label>
                    <input type="file" name="image42_low_log" accept=".log">
                </td>
            </tr>
        </table>

        <!-- FRONT CTI-->
        <?php echo "$name <b>Image 4, Low Temp - Frontside CTI</b>"; ?>
        <table border="1">

            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Right Fraction</td>
                <td align="center" style="width: 25%; white-space: nowrap;">CTIx Comments</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Above Fraction </td>
                <td align="center" style="width: 25%; white-space: nowrap;">CTIy Comments</td>
            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_front_right_fraction_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_front_right_fraction_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_ctix_comments_<?php echo $amp; ?>" value="<?php echo ${'image4_low_ctix_comments_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_front_above_fraction_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_front_above_fraction_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_ctiy_comments_<?php echo $amp; ?>" value="<?php echo ${'image4_low_ctiy_comments_' . $amp}; ?>"></td>

                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image43_low_file_name'} = "image43_low_file.png";
                    ${'image43_low_file_path'} = $upload_dir . ${'image43_low_file_name'};

                    if (file_exists(${'image43_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image43_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image43_low_file'] = $base_url . ${'image43_low_file_name'};
                    }
                    if (!empty(${'image43_low_file_name'}) && file_exists(${'image43_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image43_low_file'];
                    } else {
                        $file_exists = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 43_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image43_low_file"> Frontside Images:</label>
                    <input type="file" name="image43_low_file" accept="image/png">
                </td>
            </tr>
        </table>

        <!-- BACKSIDE CTI-->
        <?php echo "$name <b>Image 4, Low Temp - Backside CTI</b>"; ?>

        <table border="1">

            <tr>
                <td align="center" style="width: 12%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX Mean</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX RMS</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX Skewness</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX Integral</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY Mean</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY RMS</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY Skewness</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY Integral</td>

            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_left_mean_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_left_mean_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_left_rms_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_left_rms_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_left_skewness_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_left_skewness_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_left_integral_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_left_integral_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_below_mean_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_below_mean_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_below_rms_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_below_rms_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_below_skewness_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_below_skewness_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image4_low_cti_back_below_integral_<?php echo $amp; ?>" value="<?php echo ${'image4_low_cti_back_below_integral_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image44_low_file_name'} = "image44_low_file.png";
                    ${'image44_low_file_path'} = $upload_dir . ${'image44_low_file_name'};
                    $image4_low_log_name = 'image4_low_log.log';
                    $image4_low_log_path = $upload_dir . $image4_low_log_name;  // Use the absolute server path for file_exists()


                    if (file_exists(${'image44_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image44_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image44_low_file'] = $base_url . ${'image44_low_file_name'};
                    }
                    if (!empty(${'image44_low_file_name'}) && file_exists(${'image44_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image44_low_file'];
                    } else {
                        $file_exists = false;
                    }

                    // If image4_low_log exists in the directory but no session is set, initialize the session
                    if (file_exists($image4_low_log_path) && !isset($_SESSION['log_url_' . $module_underground_id]['image4_low_log'])) {
                        $_SESSION['log_url_' . $module_underground_id]['image4_low_log'] = $base_url . $image4_low_log_name;  // Use base_url for the web link
                    }

                    // Check if the log exists on the server (log system path)
                    if (!empty($image4_low_log_name) && file_exists($image4_low_log_path)) {
                        // Log exists, keep the session variables and show the icon
                        $log_exists = true;
                        $log_url = $_SESSION['log_url_' . $module_underground_id]['image4_low_log'];
                    } else {
                        // Log does not exist, clear the session variables and remove the icon
                        $log_exists = false;
                    }

                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 44_Low File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image44_low_file"> Backside Images:</label>
                    <input type="file" name="image44_low_file" accept="image/png">

                    <?php if ($log_exists): ?>
                        <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="Image4_Low Log" style="height: 20px; width: auto; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image4_low_log">CTI Log:</label>
                    <input type="file" name="image4_low_log" accept=".log,text/plain">

                </td>
            </tr>
        </table>
    </form>
    <br><br>
    <!-- Insert the code block of next image after this -->
</div>

<div class="section-block" data-section="image5low" data-label="Image5">
    <!-- Image5 Low Temp -->
    <?php echo "$name <b>Image 5, Low Temp - [500 skip, 1x1 binning, 320rx640c, Active region, 500s Exposure] - Aim: High Resolution Fe55 Cluster Analysis, CTI, noise </b>"; ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image5low" method="post" enctype="multipart/form-data"> <input type="hidden" name="id" value="<?php echo $id; ?>">

        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 1 [keV] </td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 2 [keV] </td>
                <td align="center" style="width: 30%; white-space: nowrap;">Comments</td>
            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_peak1_<?php echo $amp; ?>" value="<?php echo ${'image5_low_peak1_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_peak2_<?php echo $amp; ?>" value="<?php echo ${'image5_low_peak2_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image5_low_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>
            <!-- row for image5_ref, there is only one file used. use the field for ampA. but is actually same for all channels -->
            <tr>
                <td align="center" style="border: none; white-space: nowrap;"> Reference Image: </td>
                <td align=" left" colspan="9" style="border: none; white-space: nowrap;"> <input type="text" style="width: 98%;" name="image5_low_reference_A" value="<?php echo $image5_low_reference_A; ?>" </td>
            </tr>
            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';


                    ${'image52_low_file_name'} = "image52_low_file.png";
                    ${'image52_low_file_path'} = $upload_dir . ${'image52_low_file_name'};
                    $image5_low_log_name = 'image5_low_log.log';
                    $image5_low_log_path = $upload_dir . $image5_low_log_name;  // Use the absolute server path for file_exists()



                    if (file_exists(${'image52_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image52_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image52_low_file'] = $base_url . ${'image52_low_file_name'};
                    }
                    if (!empty(${'image52_low_file_name'}) && file_exists(${'image52_low_file_path'})) {
                        $file_exists2 = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image52_low_file'];
                    } else {
                        $file_exists2 = false;
                    }
                    // If image5_low_log exists in the directory but no session is set, initialize the session
                    if (file_exists($image5_low_log_path) && !isset($_SESSION['log_url_' . $module_underground_id]['image5_low_log'])) {
                        $_SESSION['log_url_' . $module_underground_id]['image5_low_log'] = $base_url . $image5_low_log_name;  // Use base_url for the web link
                    }

                    // Check if the log exists on the server (log system path)
                    if (!empty($image5_low_log_name) && file_exists($image5_low_log_path)) {
                        // Log exists, keep the session variables and show the icon
                        $log_exists = true;
                        $log_url = $_SESSION['log_url_' . $module_underground_id]['image5_low_log'];
                    } else {
                        // Log does not exist, clear the session variables and remove the icon
                        $log_exists = false;
                    }
                    ?>

                    <?php if ($file_exists2): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 52_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image52_low_file">Energy_Peaks:</label>
                    <input type="file" name="image52_low_file" accept="image/png">
                    <?php if ($log_exists): ?>
                        <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="Image5_Low Log" style="height: 20px; width: auto; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image5_low_log">Log File:</label>
                    <input type="file" name="image5_low_log" accept=".log,text/plain"
                        </td>
            </tr>
        </table>

        <!-- FRONT CTI-->
        <?php echo "$name <b>Image 5, Low Temp - Frontside CTI</b>"; ?>

        <table border="1">

            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Right Fraction</td>
                <td align="center" style="width: 35%; white-space: nowrap;">CTIx Comments</td>
                <td align="center" style="width: 10%; white-space: nowrap;">Above Fraction </td>
                <td align="center" style="width: 35%; white-space: nowrap;">CTIy Comments</td>
            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_front_right_fraction_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_front_right_fraction_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_ctix_comments_<?php echo $amp; ?>" value="<?php echo ${'image5_low_ctix_comments_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_front_above_fraction_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_front_above_fraction_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_ctiy_comments_<?php echo $amp; ?>" value="<?php echo ${'image5_low_ctiy_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image53_low_file_name'} = "image53_low_file.png";
                    ${'image53_low_file_path'} = $upload_dir . ${'image53_low_file_name'};

                    if (file_exists(${'image53_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image53_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image53_low_file'] = $base_url . ${'image53_low_file_name'};
                    }
                    if (!empty(${'image53_low_file_name'}) && file_exists(${'image53_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image53_low_file'];
                    } else {
                        $file_exists = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 53_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image53_low_file"> Frontside Images:</label>
                    <input type="file" name="image53_low_file" accept="image/png">
                </td>
            </tr>
        </table>

        <!-- BACKSIDE CTI-->
        <?php echo "$name <b>Image 5, Low Temp - Backside CTI</b>"; ?>

        <table border="1">

            <tr>
                <td align="center" style="width: 12%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX Mean</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX RMS</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX Skewness</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjX Integral</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY Mean</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY RMS</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY Skewness</td>
                <td align="center" style="width: 11%; white-space: nowrap;">ProjY Integral</td>

            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_left_mean_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_left_mean_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_left_rms_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_left_rms_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_left_skewness_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_left_skewness_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_left_integral_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_left_integral_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_below_mean_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_below_mean_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_below_rms_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_below_rms_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_below_skewness_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_below_skewness_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image5_low_cti_back_below_integral_<?php echo $amp; ?>" value="<?php echo ${'image5_low_cti_back_below_integral_' . $amp}; ?>"></td>

                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="left" colspan="9" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image54_low_file_name'} = "image54_low_file.png";
                    ${'image54_low_file_path'} = $upload_dir . ${'image54_low_file_name'};


                    if (file_exists(${'image54_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image54_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image54_low_file'] = $base_url . ${'image54_low_file_name'};
                    }
                    if (!empty(${'image54_low_file_name'}) && file_exists(${'image54_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image54_low_file'];
                    } else {
                        $file_exists = false;
                    }

                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 54_Low File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image54_low_file"> Backside Images:</label>
                    <input type="file" name="image54_low_file" accept="image/png">

                </td>
            </tr>
        </table>

        <!-- CROSSTALK-->
        <?php echo "$name <b>Image 5, Low Temp - Crosstalk</b>"; ?>

        <table border="1">

            <tr>
                <td align="center" style="width: 20%; white-space: nowrap;"> </td>
                <td align="center" style="width: 20%; white-space: nowrap;">ch0</td>
                <td align="center" style="width: 20%; white-space: nowrap;">ch1</td>
                <td align="center" style="width: 20%; white-space: nowrap;">ch2</td>
                <td align="center" style="width: 20%; white-space: nowrap;">ch3</td>
            </tr>

            <tr>
                <td align="center" style="width: 20%; white-space: nowrap;">ch0</td>
                <td align="center">N/A</td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_AB" value="<?php echo ${'image5_low_crosstalk_AB'}; ?>"></td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_AC" value="<?php echo ${'image5_low_crosstalk_AC'}; ?>"></td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_AD" value="<?php echo ${'image5_low_crosstalk_AD'}; ?>"></td>
            </tr>
            <tr>
                <td align="center" style="width: 20%; white-space: nowrap;">ch1</td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_BA" value="<?php echo ${'image5_low_crosstalk_BA'}; ?>"></td>
                <td align="center">N/A</td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_BC" value="<?php echo ${'image5_low_crosstalk_BC'}; ?>"></td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_BD" value="<?php echo ${'image5_low_crosstalk_BD'}; ?>"></td>
            </tr>
            <tr>
                <td align="center" style="width: 20%; white-space: nowrap;">ch2</td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_CA" value="<?php echo ${'image5_low_crosstalk_CA'}; ?>"></td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_CB" value="<?php echo ${'image5_low_crosstalk_CB'}; ?>"></td>
                <td align="center">N/A</td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_CD" value="<?php echo ${'image5_low_crosstalk_CD'}; ?>"></td>
            </tr>
            <tr>
                <td align="center" style="width: 20%; white-space: nowrap;">ch3</td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_DA" value="<?php echo ${'image5_low_crosstalk_DA'}; ?>"></td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_DB" value="<?php echo ${'image5_low_crosstalk_DB'}; ?>"></td>
                <td align="center"> <input type="text" style="width: 90%;" name="image5_low_crosstalk_DC" value="<?php echo ${'image5_low_crosstalk_DC'}; ?>"></td>
                <td align="center">N/A</td>
            </tr>
            <tr>
                <td align="center">Comment</td>
                <td align="center" colspan="4">
                    <input type="text" style="width: 90%;" name="image5_low_crosstalk_comments" value="<?php echo ${'image5_low_crosstalk_comments'}; ?>">
                </td>
            </tr>

            <tr>
                <td align="left" colspan="2" style="border: none; white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image55_low_file_name'} = "image55_low_file.png";
                    ${'image55_low_file_path'} = $upload_dir . ${'image55_low_file_name'};


                    if (file_exists(${'image55_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image55_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image55_low_file'] = $base_url . ${'image55_low_file_name'};
                    }
                    if (!empty(${'image55_low_file_name'}) && file_exists(${'image55_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image55_low_file'];
                    } else {
                        $file_exists = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 55_Low File" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image55_low_file"> Crosstalk Images:</label>
                    <input type="file" name="image55_low_file" accept="image/png">

                </td>
                <td align="left" colspan="2" style="border: none; white-space: nowrap;">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    ${'image55_low_log_name'} = "image55_low_log.log";
                    ${'image55_low_log_path'} = $upload_dir . ${'image55_low_log_name'};


                    if (file_exists(${'image55_low_log_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image55_low_log'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image55_low_log'] = $base_url . ${'image55_low_log_name'};
                    }
                    if (!empty(${'image55_low_log_name'}) && file_exists(${'image55_low_log_path'})) {
                        $log_exists = true;
                        $log_url = $_SESSION['log_url_' . $module_underground_id]['image55_low_log'];
                    } else {
                        $log_exists = false;
                    }
                    ?>

                    <?php if ($log_exists): ?>
                        <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="Image 55_Low log" style="height: 20px; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image55_low_log"> Crosstalk Images:</label>
                    <input type="file" name="image55_low_log" accept=".log,text/plain">

                </td>
            </tr>

        </table>

    </form>
    <br><br>
</div>
<!-- Insert the code block of next image after this -->

<div class="section-block" data-section="image6low" data-label="Image6">
    <!-- Image6 Low Temp -->
    <?php echo "$name <b>Image 6, Low Temp - [500 skip, 10x1 binning, 320rx640c, Active region, 500s Exposure] - Aim: High Resolution Fe55 Cluster Analysis, CTI, noise</b>"; ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image6low" method="post" enctype="multipart/form-data"> <input type="hidden" name="id" value="<?php echo $id; ?>">

        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 1 [keV] </td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 2 [keV] </td>
                <td align="center" style="width: 30%; white-space: nowrap;">Comments</td>
            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image6_low_peak1_<?php echo $amp; ?>" value="<?php echo ${'image6_low_peak1_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image6_low_peak2_<?php echo $amp; ?>" value="<?php echo ${'image6_low_peak2_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image6_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image6_low_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>
            <!-- row for image6_ref, there is only one file used. use the field for ampA. but is actually same for all channels -->
            <tr>
                <td align="center" style=" white-space: nowrap;"> Reference Image: </td>
                <td align=" left" colspan="9" style=" white-space: nowrap;"> <input type="text" style="width: 98%;" name="image6_low_reference_A" value="<?php echo $image6_low_reference_A; ?>" </td>
            </tr>

            <tr>
                <td align="left" colspan="1" style="border: none; white-space: nowrap;"> </td>
                <td align="left" colspan="9" style="white-space: nowrap;">
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image62_low_file_name'} = "image62_low_file.png";
                    ${'image62_low_file_path'} = $upload_dir . ${'image62_low_file_name'};
                    $image6_low_log_name = 'image6_low_log.log';
                    $image6_low_log_path = $upload_dir . $image6_low_log_name;  // Use the absolute server path for file_exists()

                    if (file_exists(${'image62_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image62_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image62_low_file'] = $base_url . ${'image62_low_file_name'};
                    }
                    if (!empty(${'image62_low_file_name'}) && file_exists(${'image62_low_file_path'})) {
                        $file_exists2 = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image62_low_file'];
                    } else {
                        $file_exists2 = false;
                    }

                    // If image6_low_log exists in the directory but no session is set, initialize the session
                    if (file_exists($image6_low_log_path) && !isset($_SESSION['log_url_' . $module_underground_id]['image6_low_log'])) {
                        $_SESSION['log_url_' . $module_underground_id]['image6_low_log'] = $base_url . $image6_low_log_name;  // Use base_url for the web link
                    }

                    // Check if the log exists on the server (log system path)
                    if (!empty($image6_low_log_name) && file_exists($image6_low_log_path)) {
                        // Log exists, keep the session variables and show the icon
                        $log_exists = true;
                        $log_url = $_SESSION['log_url_' . $module_underground_id]['image6_low_log'];
                    } else {
                        // Log does not exist, clear the session variables and remove the icon
                        $log_exists = false;
                    }
                    ?>

                    <?php if ($file_exists2): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 62_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image62_low_file">Energy_Peaks:</label>
                    <input type="file" name="image62_low_file" accept="image/png">

                    <?php if ($log_exists): ?>
                        <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="Image6_Low Log" style="height: 20px; width: auto; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image6_low_log">Log File:</label>
                    <input type="file" name="image6_low_log" accept=".log,text/plain">

                </td>
            </tr>

            <tr>
                <td align="left" colspan="9" style="white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image63_low_file_name'} = "image63_low_file.png";
                    ${'image63_low_file_path'} = $upload_dir . ${'image63_low_file_name'};
                    ${'image64_low_file_name'} = "image64_low_file.png";
                    ${'image64_low_file_path'} = $upload_dir . ${'image64_low_file_name'};

                    if (file_exists(${'image63_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image63_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image63_low_file'] = $base_url . ${'image63_low_file_name'};
                    }
                    if (!empty(${'image63_low_file_name'}) && file_exists(${'image63_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image63_low_file'];
                    } else {
                        $file_exists = false;
                    }
                    if (file_exists(${'image64_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image64_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image64_low_file'] = $base_url . ${'image64_low_file_name'};
                    }
                    if (!empty(${'image64_low_file_name'}) && file_exists(${'image64_low_file_path'})) {
                        $file_exists2 = true;
                        $file_url2 = $_SESSION['file_url_' . $module_underground_id]['image64_low_file'];
                    } else {
                        $file_exists2 = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 63_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image63_low_file"> Frontside Images:</label>
                    <input type="file" name="image63_low_file" accept="image/png">
                    <?php if ($file_exists2): ?>
                        <a href="<?php echo htmlspecialchars($file_url2); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 64_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image64_low_file"> Backside Images:</label>
                    <input type="file" name="image64_low_file" accept="image/png">
                </td>
            </tr>
        </table>
    </form>
    <br><br>
</div>
<!-- Insert the code block of next image after this -->

<div class="section-block" data-section="image7low" data-label="Image7">
    <!-- Image7 Low Temp -->
    <?php echo "$name <b>Image 7, Low Temp - [500 skip, 1x10 binning, 320rx640c, Active region, 500s Exposure] - Aim: High Resolution Fe55 Cluster Analysis, CTI, Noise</b>"; ?>

    <form action="<?php echo $_SERVER['PHP_SELF']; ?>#image7low" method="post" enctype="multipart/form-data"> <input type="hidden" name="id" value="<?php echo $id; ?>">

        <table border="1">
            <tr>
                <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 1 [keV] </td>
                <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 2 [keV] </td>
                <td align="center" style="width: 30%; white-space: nowrap;">Comments</td>
            </tr>

            <?php
            $count = 0;
            $count_plus = 1;
            foreach ($ccds as $amp):
            ?>
                <tr>
                    <td align="center"><?php echo "ch" . $count . " (ext" . $count_plus . ")"; ?></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image7_low_peak1_<?php echo $amp; ?>" value="<?php echo ${'image7_low_peak1_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image7_low_peak2_<?php echo $amp; ?>" value="<?php echo ${'image7_low_peak2_' . $amp}; ?>"></td>
                    <td align="center"> <input type="text" style="width: 90%;" name="image7_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image7_low_comments_' . $amp}; ?>"></td>
                </tr>
            <?php
                $count++;
                $count_plus++;
            endforeach;
            ?>

            <tr>
                <td align="center" style="white-space: nowrap;"> Reference Image: </td>
                <td align=" left" colspan="9" style="border: none; white-space: nowrap;"> <input type="text" style="width: 98%;" name="image7_low_reference_A" value="<?php echo $image7_low_reference_A; ?>" </td>
            </tr>

            <tr>
                <td align="left" colspan="0.3" style="border: none; white-space: nowrap;"> </td>
                <td align="left" colspan="3" style="white-space: nowrap;">
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image72_low_file_name'} = "image72_low_file.png";
                    ${'image72_low_file_path'} = $upload_dir . ${'image72_low_file_name'};
                    $image7_low_log_name = 'image7_low_log.log';
                    $image7_low_log_path = $upload_dir . $image7_low_log_name;  // Use the absolute server path for file_exists()

                    if (file_exists(${'image72_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image72_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image72_low_file'] = $base_url . ${'image72_low_file_name'};
                    }
                    if (!empty(${'image72_low_file_name'}) && file_exists(${'image72_low_file_path'})) {
                        $file_exists2 = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image72_low_file'];
                    } else {
                        $file_exists2 = false;
                    }

                    // If image7_low_log exists in the directory but no session is set, initialize the session
                    if (file_exists($image7_low_log_path) && !isset($_SESSION['log_url_' . $module_underground_id]['image7_low_log'])) {
                        $_SESSION['log_url_' . $module_underground_id]['image7_low_log'] = $base_url . $image7_low_log_name;  // Use base_url for the web link
                    }

                    // Check if the log exists on the server (log system path)
                    if (!empty($image7_low_log_name) && file_exists($image7_low_log_path)) {
                        // Log exists, keep the session variables and show the icon
                        $log_exists = true;
                        $log_url = $_SESSION['log_url_' . $module_underground_id]['image7_low_log'];
                    } else {
                        // Log does not exist, clear the session variables and remove the icon
                        $log_exists = false;
                    }
                    ?>

                    <?php if ($file_exists2): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 62_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image72_low_file">Energy_Peaks:</label>
                    <input type="file" name="image72_low_file" accept="image/png">

                    <?php if ($log_exists): ?>
                        <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                            <img src="pixmaps/icon2.png" alt="image7_Low Log" style="height: 20px; width: auto; width: auto;">
                        </a>
                    <?php endif; ?>
                    <label for="image7_low_log">Log File:</label>
                    <input type="file" name="image7_low_log" accept=".log,text/plain">

                </td>
            </tr>

            <tr>
                <td align="left" colspan="9" style="white-space: nowrap;">
                    <input type="submit" value="Submit">
                    &nbsp &nbsp &nbsp &nbsp;
                    <?php
                    $module_underground_id = isset($_SESSION['choosen_module_underground']) ? $_SESSION['choosen_module_underground'] : 0;
                    $upload_dir = '/var/www/html/QC_production/uploads/edit_module_underground/module_underground_' . $module_underground_id . '/';
                    $base_url   = '/QC_production/uploads/edit_module_underground/' . 'module_underground_' . $module_underground_id . '/';

                    ${'image73_low_file_name'} = "image73_low_file.png";
                    ${'image73_low_file_path'} = $upload_dir . ${'image73_low_file_name'};
                    ${'image74_low_file_name'} = "image74_low_file.png";
                    ${'image74_low_file_path'} = $upload_dir . ${'image74_low_file_name'};

                    if (file_exists(${'image73_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image73_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image73_low_file'] = $base_url . ${'image73_low_file_name'};
                    }
                    if (!empty(${'image73_low_file_name'}) && file_exists(${'image73_low_file_path'})) {
                        $file_exists = true;
                        $file_url = $_SESSION['file_url_' . $module_underground_id]['image73_low_file'];
                    } else {
                        $file_exists = false;
                    }
                    if (file_exists(${'image74_low_file_path'}) && !isset($_SESSION['file_url_' . $module_underground_id]['image74_low_file'])) {
                        $_SESSION['file_url_' . $module_underground_id]['image74_low_file'] = $base_url . ${'image74_low_file_name'};
                    }
                    if (!empty(${'image74_low_file_name'}) && file_exists(${'image74_low_file_path'})) {
                        $file_exists2 = true;
                        $file_url2 = $_SESSION['file_url_' . $module_underground_id]['image74_low_file'];
                    } else {
                        $file_exists2 = false;
                    }
                    ?>

                    <?php if ($file_exists): ?>
                        <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 73_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image73_low_file"> Frontside Images:</label>
                    <input type="file" name="image73_low_file" accept="image/png">
                    <?php if ($file_exists2): ?>
                        <a href="<?php echo htmlspecialchars($file_url2); ?>" target="_blank">
                            <img src="pixmaps/icon.png" alt="Image 74_Low File" style="height: 20px;">
                        </a>
                    <?php endif; ?>
                    <label for="image74_low_file"> Backside Images:</label>
                    <input type="file" name="image74_low_file" accept="image/png">
                </td>
            </tr>
        </table>
    </form>
    <br><br>
    <!-- Insert the code block of next image after this -->
</div>

<?php
// script for navigating the input cells via arrow keys
include("aux/table_navigation.php");


if (isset($_POST['run_script'])) {
    $id_safe = escapeshellarg($id);
    $output = shell_exec("python3 energypeaks.py --id $id_safe 2>&1");
}
?>

<form method="post">
    <button type="submit" name="run_script">Update Image</button>
</form>

<img src="<?php echo $base_url . 'Fe55Energy.png?v=' . time(); ?>" alt="Fe55Energy" width="800">