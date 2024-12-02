<?php
// edit_module_surface.php
// D.Norcini, Hopkins, 2024

session_start();

$req_priv = "full";
include("db_login.php");
include("page_setup.php");
include("aux/array_defs.php"); // Import all array definitions

$table = "MODULE_SURFACE";

// Clear session variables when module_surface_id changes to prevent data from other module_surfaces interfering
if (isset($_POST['choosen']) && $_POST['choosen'] !== $_SESSION['choosen_module_surface']) {
    unset($_SESSION['log_url'],$_SESSION['file_url'], $_SESSION['upload_dir'], $_SESSION['base_url'], $_SESSION['log_exists'], $_SESSION['file_exists']);
}

// Set the chosen module_surface based on session or POST data
if (!empty($_SESSION['req_id'])) {
    $_SESSION['choosen_module_surface'] = $_SESSION['req_id'];
    unset($_SESSION['req_id']);
}

if (isset($_POST['choosen'])) {
    $_SESSION['choosen_module_surface'] = $_POST['choosen'];
}

if (empty($_SESSION['choosen_module_surface'])) {
    $_SESSION['choosen_module_surface'] = 1;
}

// Create a new entry if requested
if (isset($_POST['new'])) {
    $query = "INSERT INTO `" . $table . "` (`ID`, `Last_update`) VALUES (NULL, '" . time() . "')";
    $result = mysql_query($query);
    if (!$result) {
        module_surface("Could not query the database <br />" . mysql_error());
    }
    include("aux/get_last_table_id.php");
    $_SESSION['choosen_module_surface'] = $last_id;

    // Reset only the problematic section when creating a new module_surface
    $check_A = $check_B = $check_C = $check_D = 0;
    $grade_A = $grade_B = $grade_C = $grade_D = '';
    $notes_A = $notes_B = $notes_C = $notes_D = '';

    // Clear any session variables related to file uploads
    unset($_SESSION['log_url'], $_SESSION['file_url'], $_SESSION['upload_dir'], $_SESSION['base_url'], $_SESSION['log_exists'], $_SESSION['file_exists']);
}

// Navigation logic (First, Last, Previous, Next)
if (isset($_POST['first'])) {
    $_SESSION['choosen_module_surface'] = 1;
}
if (isset($_POST['last'])) {
    include("aux/get_last_die_id.php");
    $_SESSION['choosen_module_surface'] = $last_id;
}
if (isset($_POST['prev'])) {
    $_SESSION['choosen_module_surface'] = max(1, $_SESSION['choosen_module_surface'] - 1);
}
if (isset($_POST['next'])) {
    $_SESSION['choosen_module_surface'] = $_SESSION['choosen_module_surface'] + 1;
}

// Ensure we never go below 1 or above the last MODULE_SURFACE ID
include("aux/get_last_die_id.php");
if ($_SESSION['choosen_module_surface'] < 1) {
    $_SESSION['choosen_module_surface'] = 1;
}
if ($_SESSION['choosen_module_surface'] > $last_id) {
    $_SESSION['choosen_module_surface'] = $last_id;
}

// Assign the chosen module_surface to the $id variable
$id = (int)$_SESSION['choosen_module_surface'];

// Fetch module_surface details
include("aux/get_module_surface_vals.php");

// Update module_surface details if form is submitted
if (isset($_POST['id'])) {
    $module_surface_id = (int)$_POST['id'];
    
    // Clear session variables when switching module_surfaces
    if (!isset($_SESSION['choosen_module_surface']) || $_SESSION['choosen_module_surface'] !== $module_surface_id) {
        unset($_SESSION['file_url_' . $module_surface_id], $_SESSION['file_exists_' . $module_surface_id], $_SESSION['log_url_' . $module_surface_id], $_SESSION['log_exists_' . $module_surface_id]);
    }

    // Clear session variables related to file uploads when module_surface_id changes
    unset($_SESSION['file_url_' . $module_surface_id], $_SESSION['upload_dir_' . $module_surface_id], $_SESSION['base_url_' . $module_surface_id], $_SESSION['file_exists_' . $module_surface_id], $_SESSION['log_url_' . $module_surface_id], $_SESSION['log_exists_' . $module_surface_id]);

    // Directory to store uploaded images
    $upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';
    $base_url = '/uploads/edit_module_surface/' . 'module_surface_' . $module_surface_id; // Web-accessible URL
    $_SESSION['upload_dir_' . $module_surface_id] = $upload_dir;
    $_SESSION['base_url_' . $module_surface_id] = $base_url;

    // Create the directory if it doesn't exist
    if (!is_dir($upload_dir)) {
        if (!mkdir($upload_dir, 0777, true)) {
            module_surface('Failed to create directory: ' . $upload_dir);
        }
    }

    if (!is_writable($upload_dir)) {
        module_surface("Directory is not writable: $upload_dir");
    }

    // Allowed file types
    $allowed_types = array('image/png', 'image/jpeg', 'application/pdf');
    $allowed_logs = array('text/plain');

    // ======================
    // Upload Trace and Image Files
    // ======================
    $file_fields = ['trace_low_file'];
    $log_fields = ['trace_low_log'];

    // Add image fields dynamically for image1 to image6
    for ($i = 1; $i <= 5; $i++) {
        $file_fields[] = 'image' . $i . '_low_file';
        $log_fields[] = 'image' . $i . '_low_log';
        $file_fields[] = 'image' . $i . '_high_file';
        $log_fields[] = 'image' . $i . '_high_log';
    }

    // Ensure the session arrays are initialized if not already
    if (!isset($_SESSION['file_url_' . $module_surface_id])) {
        $_SESSION['file_url_' . $module_surface_id] = [];
    }
    if (!isset($_SESSION['file_exists_' . $module_surface_id])) {
        $_SESSION['file_exists_' . $module_surface_id] = [];
    }
    if (!isset($_SESSION['log_url_' . $module_surface_id])) {
        $_SESSION['log_url_' . $module_surface_id] = [];
    }
    if (!isset($_SESSION['log_exists_' . $module_surface_id])) {
        $_SESSION['log_exists_' . $module_surface_id] = [];
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
                    // Save the file name to the session specific to this module_surface
                    $_SESSION['file_url_' . $module_surface_id][$file_field] = $base_url . '/' . $file_name;
                    $_SESSION['file_exists_' . $module_surface_id][$file_field] = true;
                } else {
                    echo "Failed to upload file: " . $file_name . '<br>';
                }
            } else {
                echo "Invalid file type for: " . $_FILES[$file_field]['name'] . '<br>';
            }
        } else {
            // No new upload, but check if there is an existing session value
            if (isset($_SESSION['file_url_' . $module_surface_id][$file_field])) {
                echo "$file_field session file_url: " . $_SESSION['file_url_' . $module_surface_id][$file_field] . '<br>';
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
                    // Save the log name to the session specific to this module_surface
                    $_SESSION['log_url_' . $module_surface_id][$log_field] = $base_url . '/' . $log_name;
                    $_SESSION['log_exists_' . $module_surface_id][$log_field] = true;
                } else {
                    echo "Failed to upload log: " . $log_name . '<br>';
                }
            } else {
                echo "Invalid file type for: " . $_FILES[$log_field]['name'] . '<br>';
            }
        } else {
            // No new upload, but check if there is an existing session value
            if (isset($_SESSION['log_url_' . $module_surface_id][$log_field])) {
                echo "$log_field session log_url: " . $_SESSION['log_url_' . $module_surface_id][$log_field] . '<br>';
            }
        }
    }

    // ======================
    // Handle Form Fields and Amplifiers
    // ======================
    
    // Include all relevant form fields to update
    $fields = array('name', 'status', 'pitch_adaptor_id', 'humidity', 'radon', 'activation', 'die_A', 'die_B', 'die_C', 'die_D', 'amp_A', 'amp_B', 'amp_C', 'amp_D', 'tester', 'test_date', 'test_time', 'chamber', 'temp_low', 'temp_high', 'feedthru_position', 'ACM', 'script', 'image_dir', 'grade_A', 'grade_B', 'grade_C', 'grade_D', 'notes', 'notes_A', 'notes_B', 'notes_C', 'notes_D', 'reviewer');
    $checkboxes = ['check_A', 'check_B', 'check_C', 'check_D'];
    $fields = array_merge($fields, $checkboxes);
    $fields = array_merge($fields, $trace_fields_low);
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
        foreach ($trace_fields_low as $base_field) {
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
        $query = "UPDATE `" . $table . "` SET " . implode(', ', $query_parts) . " WHERE `ID` = " . $module_surface_id;
        $result = mysql_query($query);
        if (!$result) {
            module_surface('Query failed: ' . mysql_error());
        }
    }
}


// Fetch module_surface details again after updates
include("aux/get_module_surface_vals.php");
mysql_close($connection);

// Function to generate a dropdown menu from an array
function generate_dropdown($name, $options, $selected_value) {
    echo '<select name="'.$name.'">';
    foreach ($options as $value) {
        echo '<option value="'.$value.'"'.($value == $selected_value ? ' selected' : '').'>'.$value.'</option>';
    }
    echo '</select>';
}

// File existence check
function check_and_update_file_session($file_field, $upload_dir, $base_url, $module_surface_id) {
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
function check_and_update_log_session($log_field, $upload_dir, $base_url, $module_surface_id) {
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
                &nbsp;&nbsp;&nbsp;&nbsp;Choose MODULE_SURFACE ID: <input type="text" name="choosen" size="6">
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

<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1" cellpadding="2" width="100%">
        <tr>
            <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                MODULE_SURFACE ID: <?php echo $id; ?>
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                Name: <input type="text" name="name" value="<?php echo $name; ?>" size="20">
            </td>
            <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                Status:
                <?php generate_dropdown('status', $status_array, $status); ?>
            </td>
            <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                Entry Last updated: <?php echo date("G:i:s M d, Y", $last_update); ?>
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
<?php echo "<b>Preliminary Grade Assessment</b>";?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <table border="1" cellpadding="2" width="100%">
	<tr>
            <td><?php echo "A"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_A', $grade_array, $grade_A); ?>
            </td>
            <td style="width: 5%;">
                <input type="hidden" name="check_A" value="0">
		<input type="checkbox" name="check_A" value="1" <?php echo ($check_A == 1) ? 'checked' : ''; ?>>
            </td>

            <td><?php echo "B"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_B', $grade_array, $grade_B); ?>
            </td>
            <td style="width: 5%;">
                <input type="hidden" name="check_B" value="0">
		<input type="checkbox" name="check_B" value="1" <?php echo ($check_B == 1) ? 'checked' : ''; ?>>
            </td>

            <td><?php echo "C"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_C', $grade_array, $grade_C); ?>
            </td>
            <td style="width: 5%;">
                <input type="hidden" name="check_C" value="0">
		<input type="checkbox" name="check_C" value="1" <?php echo ($check_C == 1) ? 'checked' : ''; ?>>
            </td>

            <td><?php echo "D"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_D', $grade_array, $grade_D); ?>
            </td>
            <td style="width: 5%;">
                <input type="hidden" name="check_D" value="0">
		<input type="checkbox" name="check_D" value="1" <?php echo ($check_D == 1) ? 'checked' : ''; ?>>
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
<?php echo "<b>Testing</b>";?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1" cellpadding="2" width="100%">
        <tr>
            <td>Tester: <input type="text" name="tester" value="<?php echo $tester; ?>" size="10"></td>
            <td>
                Test Date: <input type="date" name="test_date" value="<?php echo $test_date; ?>">
                Test Time [PT]: <input type="time" name="test_time" value="<?php echo $test_time; ?>">
            </td>
            <td>
                Chamber: <?php generate_dropdown('chamber', $chamber_array, $chamber); ?>
            </td>
        </tr>
        <tr>
            <td>
		Low TempC [K]: <input type="text" name="temp_low" value="<?php echo $temp_low; ?>" size="10">
                High TempC [K]: <input type="text" name="temp_high" value="<?php echo $temp_high; ?>" size="10">
	    </td>
	    <td>
                Feedthru Position: <?php generate_dropdown('feedthru_position', $feedthru_positions, $feedthru_position); ?>
            </td>
            <td>
                ACM Number: <?php generate_dropdown('ACM', $ACM_numbers, $ACM); ?>
            </td>
        </tr>
        <tr>
            <td colspan="3">
                Scripts Directory: <input type="text" name="script" value="<?php echo $script; ?>" size="50">
            </td>
        </tr>
        <tr>
            <td colspan="3">
                Image Directory: <input type="text" name="image_dir" value="<?php echo $image_dir; ?>" size="50">
            </td>
        </tr>
        <tr>
            <td colspan="3" style="text-align: center;">
                <input type="submit" value="Submit">
            </td>
        </tr>
    </table>
</form>
<br><br>

<!-- Trace Section -->
<?php echo "<b>Trace, Low Temp</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" colspan="1" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" colspan="1" style="width: 15%; white-space: nowrap;">Saturation/Low? [~80000 ADU]</td>
            <td align="left" colspan="1" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" colspan="1" style="width: 35%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td>
		    <?php generate_dropdown('trace_low_saturation_' . $amp, $yes_no_blank_array, ${'trace_low_saturation_' . $amp}); ?>
		</td>
		<td><input type="text" name="trace_low_comments_<?php echo $amp; ?>" value="<?php echo ${'trace_low_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="trace_low_reference_<?php echo $amp; ?>" value="<?php echo ${'trace_low_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
	$count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if trace_low_file already exists in the directory even if no form is submitted
		$trace_low_file_name = 'trace_low_file.png';
		$trace_low_file_path = $upload_dir . $trace_low_file_name;  // Use the absolute server path for file_exists()
		$trace_low_log_name = 'trace_low_log.log';
		$trace_low_log_path = $upload_dir . $trace_low_log_name;  // Use the absolute server path for file_exists()
		
		// If trace_low_file exists in the directory but no session is set, initialize the session
		if (file_exists($trace_low_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['trace_low_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['trace_low_file'] = $base_url . $trace_low_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($trace_low_file_name) && file_exists($trace_low_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['trace_low_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If trace_low_log exists in the directory but no session is set, initialize the session
                if (file_exists($trace_low_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['trace_low_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['trace_low_log'] = $base_url . $trace_low_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($trace_low_log_name) && file_exists($trace_low_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
		    $log_url = $_SESSION['log_url_' . $module_surface_id]['trace_low_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

		?>

		<?php if ($file_exists): ?>
    		      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
        	      <img src="pixmaps/icon.png" alt="Trace File" style="height: 20px; width: auto;">
    		      </a>
		<?php endif; ?>
		      <label for="trace_low_file">Image File:</label>
		      <input type="file" name="trace_low_file" accept="image/png, image/jpeg, application/pdf">
		      &nbsp; &nbsp; &nbsp; &nbsp;

		<?php if ($log_exists): ?>
    		      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
        	      <img src="pixmaps/icon2.png" alt="Trace Log" style="height: 20px; width: auto;">
    		      </a>
		<?php endif; ?>
		      <label for="trace_low_log">Log File:</label>
		      <input type="file" name="trace_low_log" accept=".log,text/plain">
 	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 1 - Track Defects-->
<?php echo "<b>Image 1, Low Temp - [1skip, 10x10binning, 160rx640c, Active region, 60s Exposure] - Aim: To see tracks</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Tracks?</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Defects?</td>
            <td align="left" style="width: 12%; white-space: nowrap;">Noise [ADU]</td>	
            <td align="left" style="width: 28%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 43%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td>
		    <?php generate_dropdown('image1_low_tracks_' . $amp, $yes_no_blank_array, ${'image1_low_tracks_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image1_low_defects_' . $amp, $yes_no_blank_array, ${'image1_low_defects_' . $amp}); ?>
		</td>
		<td><input type="text" name="image1_low_noise_<?php echo $amp; ?>" value="<?php echo ${'image1_low_noise_' . $amp}; ?>"></td>
		<td><input type="text" name="image1_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image1_low_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image1_low_reference_<?php echo $amp; ?>" value="<?php echo ${'image1_low_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image1_low_file already exists in the directory even if no form is submitted
		$image1_low_file_name = 'image1_low_file.png';
		$image1_low_file_path = $upload_dir . $image1_low_file_name;  // Use the absolute server path for file_exists()
                $image1_low_log_name = 'image1_low_log.log';
                $image1_low_log_path = $upload_dir . $image1_low_log_name;  // Use the absolute server path for file_exists()
		
		// If image1_low_file exists in the directory but no session is set, initialize the session
		if (file_exists($image1_low_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image1_low_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image1_low_file'] = $base_url . $image1_low_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image1_low_file_name) && file_exists($image1_low_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image1_low_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image1_low_log exists in the directory but no session is set, initialize the session
                if (file_exists($image1_low_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image1_low_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image1_low_log'] = $base_url . $image1_low_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image1_low_log_name) && file_exists($image1_low_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image1_low_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image1_Low File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image1_low_file">Image File:</label>
                      <input type="file" name="image1_low_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image1_Low Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image1_low_log">Log File:</label>
                      <input type="file" name="image1_low_log" accept=".log,text/plain">
	   </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 2 - Defect Map, Sharpness of Tracks, CTI, Noise -->
<?php echo "<b>Image 2, Low Temp - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Number Pixel Defects</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Number Column Defects</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Defect Region</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Overscan Noise [ADU]</td>
            <td align="left" style="width: 5%; white-space: nowrap;">CTI? - Code</td>
            <td align="left" style="width: 5%; white-space: nowrap;">CTI? - Visual</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Sharp Tracks?</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image2_low_pixel_defects_<?php echo $amp; ?>" value="<?php echo ${'image2_low_pixel_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_low_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image2_low_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_low_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image2_low_region_defect_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_low_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image2_low_noise_overscan_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_low_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image2_low_cti_code_' . $amp}; ?>"></td>
		<td>
		    <?php generate_dropdown('image2_low_cti_visual_' . $amp, $yes_no_blank_array, ${'image2_low_cti_visual_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image2_low_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image2_low_sharpness_tracks_' . $amp}); ?>
		</td>
		<td><input type="text" name="image2_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image2_low_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image2_low_reference_<?php echo $amp; ?>" value="<?php echo ${'image2_low_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image2_low_file already exists in the directory even if no form is submitted
		$image2_low_file_name = 'image2_low_file.png';
		$image2_low_file_path = $upload_dir . $image2_low_file_name;  // Use the absolute server path for file_exists()
                $image2_low_log_name = 'image2_low_log.log';
                $image2_low_log_path = $upload_dir . $image2_low_log_name;  // Use the absolute server path for file_exists()

		// If image2_low_file exists in the directory but no session is set, initialize the session
		if (file_exists($image2_low_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image2_low_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image2_low_file'] = $base_url . $image2_low_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image2_low_file_name) && file_exists($image2_low_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image2_low_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image2_low_log exists in the directory but no session is set, initialize the session
                if (file_exists($image2_low_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image2_low_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image2_low_log'] = $base_url . $image2_low_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image2_low_log_name) && file_exists($image2_low_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image2_low_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image2_Low File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image2_low_file">Image File:</label>
                      <input type="file" name="image2_low_file" accept="image/png, image/jpeg, application/pdf">
		      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image2_Low Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image2_low_log">Log File:</label>
                      <input type="file" name="image2_low_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 3 - Track Defects, High VSub and V Clk -->
<?php echo "<b>Image 3, Low Temp - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure, high VSub and V Clk] - Aim: Defect Map, Sharpness of tracks, CTI, Noise</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 15%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Number Pixel Defects</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Number Column Defects</td>
	    <td align="left" style="width: 15%; white-space: nowrap;">Defect Region</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Overscan Noise [ADU]</td>
            <td align="left" style="width: 15%; white-space: nowrap;">CTI? - Code</td>
            <td align="left" style="width: 15%; white-space: nowrap;">CTI? - Visual</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Sharp Tracks?</td>
            <td align="left" style="width: 45%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 25%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

        $count = 0;	
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image3_low_pixel_defects_<?php echo $amp; ?>" value="<?php echo ${'image3_low_pixel_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_low_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image3_low_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_low_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image3_low_region_defect_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_low_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image3_low_noise_overscan_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_low_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image3_low_cti_code_' . $amp}; ?>"></td>
		<td>
		    <?php generate_dropdown('image3_low_cti_visual_' . $amp, $yes_no_blank_array, ${'image3_low_cti_visual_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image3_low_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image3_low_sharpness_tracks_' . $amp}); ?>
		</td>
		<td><input type="text" name="image3_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image3_low_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image3_low_reference_<?php echo $amp; ?>" value="<?php echo ${'image3_low_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image3_low_file already exists in the directory even if no form is submitted
		$image3_low_file_name = 'image3_low_file.png';
		$image3_low_file_path = $upload_dir . $image3_low_file_name;  // Use the absolute server path for file_exists()
                $image3_low_log_name = 'image3_low_log.log';
                $image3_low_log_path = $upload_dir . $image3_low_log_name;  // Use the absolute server path for file_exists()

		// If image3_low_file exists in the directory but no session is set, initialize the session
		if (file_exists($image3_low_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image3_low_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image3_low_file'] = $base_url . $image3_low_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image3_low_file_name) && file_exists($image3_low_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image3_low_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image3_low_log exists in the directory but no session is set, initialize the session
                if (file_exists($image3_low_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image3_low_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image3_low_log'] = $base_url . $image3_low_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image3_low_log_name) && file_exists($image3_low_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image3_low_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image3_Low File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image3_low_file">Image File:</label>
                      <input type="file" name="image3_low_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image3_Low Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image3_low_log">Log File:</label>
                      <input type="file" name="image3_low_log" accept=".log,text/plain">
	   </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 4 - Single Electron Resolution -->
<?php echo "<b>Image 4, Low Temp - [1000skip, 1x1binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Resolution [e-]</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Gain [ADU]</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Dark Current [e-/pix/img]</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 45%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image4_low_res_<?php echo $amp; ?>" value="<?php echo ${'image4_low_res_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_low_gain_<?php echo $amp; ?>" value="<?php echo ${'image4_low_gain_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_low_dark_current_<?php echo $amp; ?>" value="<?php echo ${'image4_low_dark_current_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image4_low_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image4_low_reference_<?php echo $amp; ?>" value="<?php echo ${'image4_low_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image4_low_file already exists in the directory even if no form is submitted
		$image4_low_file_name = 'image4_low_file.png';
		$image4_low_file_path = $upload_dir . $image4_low_file_name;  // Use the absolute server path for file_exists()
                $image4_low_log_name = 'image4_low_log.log';
                $image4_low_log_path = $upload_dir . $image4_low_log_name;  // Use the absolute server path for file_exists()

		// If image4_low_file exists in the directory but no session is set, initialize the session
		if (file_exists($image4_low_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image4_low_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image4_low_file'] = $base_url . $image4_low_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image4_low_file_name) && file_exists($image4_low_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image4_low_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image4_low_log exists in the directory but no session is set, initialize the session
                if (file_exists($image4_low_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image4_low_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image4_low_log'] = $base_url . $image4_low_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image4_low_log_name) && file_exists($image4_low_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image4_low_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image4_Low File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image4_low_file">Image File:</label>
                      <input type="file" name="image4_low_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image4_Low Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image4_low_log">Log File:</label>
                      <input type="file" name="image4_low_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 5 - Serial Register Defect -->
<?php echo "<b>Image 5, Low Temp - [1skip, 1x1binning, 30rx3400c, Serial register, 10s Exposure] - Aim: Serial Register Defect</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Number Column Defects</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Noise [ADU]</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 45%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image5_low_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image5_low_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image5_low_noise_<?php echo $amp; ?>" value="<?php echo ${'image5_low_noise_' . $amp}; ?>"></td>
		<td><input type="text" name="image5_low_comments_<?php echo $amp; ?>" value="<?php echo ${'image5_low_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image5_low_reference_<?php echo $amp; ?>" value="<?php echo ${'image5_low_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image5_low_file already exists in the directory even if no form is submitted
		$image5_low_file_name = 'image5_low_file.png';
		$image5_low_file_path = $upload_dir . $image5_low_file_name;  // Use the absolute server path for file_exists()
                $image5_low_log_name = 'image5_low_log.log';
                $image5_low_log_path = $upload_dir . $image5_low_log_name;  // Use the absolute server path for file_exists()

		// If image5_low_file exists in the directory but no session is set, initialize the session
		if (file_exists($image5_low_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image5_low_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image5_low_file'] = $base_url . $image5_low_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image5_low_file_name) && file_exists($image5_low_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image5_low_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image5_low_log exists in the directory but no session is set, initialize the session
                if (file_exists($image5_low_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image5_low_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image5_low_log'] = $base_url . $image5_low_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image5_low_log_name) && file_exists($image5_low_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image5_low_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image5_Low File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image5_low_file">Image File:</label>
                      <input type="file" name="image5_low_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image5_Low Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image5_low_log">Log File:</label>
                      <input type="file" name="image5_low_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>
<br><br>

<!-- Image 1 - Track Defects-->
<?php echo "<b>Image 1, High Temp - [1skip, 10x10binning, 160rx640c, Active region, 60s Exposure] - Aim: To see tracks</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Tracks?</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Defects?</td>
            <td align="left" style="width: 12%; white-space: nowrap;">Noise [ADU]</td>	
            <td align="left" style="width: 28%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 43%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td>
		    <?php generate_dropdown('image1_high_tracks_' . $amp, $yes_no_blank_array, ${'image1_high_tracks_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image1_high_defects_' . $amp, $yes_no_blank_array, ${'image1_high_defects_' . $amp}); ?>
		</td>
		<td><input type="text" name="image1_high_noise_<?php echo $amp; ?>" value="<?php echo ${'image1_high_noise_' . $amp}; ?>"></td>
		<td><input type="text" name="image1_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image1_high_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image1_high_reference_<?php echo $amp; ?>" value="<?php echo ${'image1_high_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image1_high_file already exists in the directory even if no form is submitted
		$image1_high_file_name = 'image1_high_file.png';
		$image1_high_file_path = $upload_dir . $image1_high_file_name;  // Use the absolute server path for file_exists()
                $image1_high_log_name = 'image1_high_log.log';
                $image1_high_log_path = $upload_dir . $image1_high_log_name;  // Use the absolute server path for file_exists()
		
		// If image1_high_file exists in the directory but no session is set, initialize the session
		if (file_exists($image1_high_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image1_high_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image1_high_file'] = $base_url . $image1_high_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image1_high_file_name) && file_exists($image1_high_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image1_high_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image1_high_log exists in the directory but no session is set, initialize the session
                if (file_exists($image1_high_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image1_high_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image1_high_log'] = $base_url . $image1_high_log_name;  // Use base_url for the web link
                }

		// Check if the log exists on the server (log system path)
                if (!empty($image1_high_log_name) && file_exists($image1_high_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image1_high_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                    <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
			<img src="pixmaps/icon.png" alt="Image1_High File" style="height: 20px; width: auto;">
                    </a>
                <?php endif; ?>
                <label for="image1_high_file">Image File:</label>
                <input type="file" name="image1_high_file" accept="image/png, image/jpeg, application/pdf">
                &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                    <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
			<img src="pixmaps/icon2.png" alt="Image1_High Log" style="height: 20px; width: auto;">
                    </a>
                <?php endif; ?>
                <label for="image1_high_log">Log File:</label>
                <input type="file" name="image1_high_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 2 - Defect Map, Sharpness of Tracks, CTI, Noise -->
<?php echo "<b>Image 2, High Temp - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Number Pixel Defects</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Number Column Defects</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Defect Region</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Overscan Noise [ADU]</td>
            <td align="left" style="width: 5%; white-space: nowrap;">CTI? - Code</td>
            <td align="left" style="width: 5%; white-space: nowrap;">CTI? - Visual</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Sharp Tracks?</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image2_high_pixel_defects_<?php echo $amp; ?>" value="<?php echo ${'image2_high_pixel_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_high_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image2_high_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_high_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image2_high_region_defect_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_high_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image2_high_noise_overscan_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_high_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image2_high_cti_code_' . $amp}; ?>"></td>
		<td>
		    <?php generate_dropdown('image2_high_cti_visual_' . $amp, $yes_no_blank_array, ${'image2_high_cti_visual_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image2_high_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image2_high_sharpness_tracks_' . $amp}); ?>
		</td>
		<td><input type="text" name="image2_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image2_high_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image2_high_reference_<?php echo $amp; ?>" value="<?php echo ${'image2_high_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image2_high_file already exists in the directory even if no form is submitted
		$image2_high_file_name = 'image2_high_file.png';
		$image2_high_file_path = $upload_dir . $image2_high_file_name;  // Use the absolute server path for file_exists()
                $image2_high_log_name = 'image2_high_log.log';
                $image2_high_log_path = $upload_dir . $image2_high_log_name;  // Use the absolute server path for file_exists()

		// If image2_high_file exists in the directory but no session is set, initialize the session
		if (file_exists($image2_high_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image2_high_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image2_high_file'] = $base_url . $image2_high_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image2_high_file_name) && file_exists($image2_high_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image2_high_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image2_high_log exists in the directory but no session is set, initialize the session
                if (file_exists($image2_high_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image2_high_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image2_high_log'] = $base_url . $image2_high_log_name;  // Use base_url for the web link
                }

		// Check if the log exists on the server (log system path)
                if (!empty($image2_high_log_name) && file_exists($image2_high_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image2_high_log'];
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
                <input type="file" name="image2_high_file" accept="image/png, image/jpeg, application/pdf">
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

<!-- Image 3 - Track Defects, High VSub and V Clk -->
<?php echo "<b>Image 3, High Temp - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure, high VSub and V Clk] - Aim: Defect Map, Sharpness of tracks, CTI, Noise</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 15%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Number Pixel Defects</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Number Column Defects</td>
	    <td align="left" style="width: 15%; white-space: nowrap;">Defect Region</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Overscan Noise [ADU]</td>
            <td align="left" style="width: 15%; white-space: nowrap;">CTI? - Code</td>
            <td align="left" style="width: 15%; white-space: nowrap;">CTI? - Visual</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Sharp Tracks?</td>
            <td align="left" style="width: 45%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 25%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

        $count = 0;	
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image3_high_pixel_defects_<?php echo $amp; ?>" value="<?php echo ${'image3_high_pixel_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_high_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image3_high_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_high_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image3_high_region_defect_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_high_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image3_high_noise_overscan_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_high_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image3_high_cti_code_' . $amp}; ?>"></td>
		<td>
		    <?php generate_dropdown('image3_high_cti_visual_' . $amp, $yes_no_blank_array, ${'image3_high_cti_visual_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image3_high_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image3_high_sharpness_tracks_' . $amp}); ?>
		</td>
		<td><input type="text" name="image3_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image3_high_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image3_high_reference_<?php echo $amp; ?>" value="<?php echo ${'image3_high_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image3_high_file already exists in the directory even if no form is submitted
		$image3_high_file_name = 'image3_high_file.png';
		$image3_high_file_path = $upload_dir . $image3_high_file_name;  // Use the absolute server path for file_exists()
                $image3_high_log_name = 'image3_high_log.log';
                $image3_high_log_path = $upload_dir . $image3_high_log_name;  // Use the absolute server path for file_exists()

		// If image3_high_file exists in the directory but no session is set, initialize the session
		if (file_exists($image3_high_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image3_high_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image3_high_file'] = $base_url . $image3_high_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image3_high_file_name) && file_exists($image3_high_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image3_high_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image3_high_log exists in the directory but no session is set, initialize the session
                if (file_exists($image3_high_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image3_high_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image3_high_log'] = $base_url . $image3_high_log_name;  // Use base_url for the web link
                }

		// Check if the log exists on the server (log system path)
                if (!empty($image3_high_log_name) && file_exists($image3_high_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image3_high_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                    <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
			<img src="pixmaps/icon.png" alt="Image3_High File" style="height: 20px; width: auto;">
                    </a>
                <?php endif; ?>
                <label for="image3_high_file">Image File:</label>
                <input type="file" name="image3_high_file" accept="image/png, image/jpeg, application/pdf">
                &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                    <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
			<img src="pixmaps/icon2.png" alt="Image3_High Log" style="height: 20px; width: auto;">
                    </a>
                <?php endif; ?>
                <label for="image3_high_log">Log File:</label>
                <input type="file" name="image3_high_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 4 - Single Electron Resolution -->
<?php echo "<b>Image 4, High Temp - [1000skip, 1x1binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Resolution [e-]</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Gain [ADU]</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Dark Current [e-/pix/img]</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 45%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image4_high_res_<?php echo $amp; ?>" value="<?php echo ${'image4_high_res_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_high_gain_<?php echo $amp; ?>" value="<?php echo ${'image4_high_gain_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_high_dark_current_<?php echo $amp; ?>" value="<?php echo ${'image4_high_dark_current_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image4_high_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image4_high_reference_<?php echo $amp; ?>" value="<?php echo ${'image4_high_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image4_high_file already exists in the directory even if no form is submitted
		$image4_high_file_name = 'image4_high_file.png';
		$image4_high_file_path = $upload_dir . $image4_high_file_name;  // Use the absolute server path for file_exists()
                $image4_high_log_name = 'image4_high_log.log';
                $image4_high_log_path = $upload_dir . $image4_high_log_name;  // Use the absolute server path for file_exists()

		// If image4_high_file exists in the directory but no session is set, initialize the session
		if (file_exists($image4_high_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image4_high_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image4_high_file'] = $base_url . $image4_high_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image4_high_file_name) && file_exists($image4_high_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image4_high_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image4_high_log exists in the directory but no session is set, initialize the session
                if (file_exists($image4_high_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image4_high_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image4_high_log'] = $base_url . $image4_high_log_name;  // Use base_url for the web link
                }

		// Check if the log exists on the server (log system path)
                if (!empty($image4_high_log_name) && file_exists($image4_high_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image4_high_log'];
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
                <input type="file" name="image4_high_file" accept="image/png, image/jpeg, application/pdf">
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

<!-- Image 5 - Serial Register Defect -->
<?php echo "<b>Image 5, High Temp - [1skip, 1x1binning, 30rx3400c, Serial register, 10s Exposure] - Aim: Serial Register Defect</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Number Column Defects</td>
            <td align="left" style="width: 15%; white-space: nowrap;">Noise [ADU]</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 45%; white-space: nowrap;">Reference Image</td>
	</tr>
	<?php

	$count = 0;
	foreach ($ccds as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ext" . $count . ")"; ?></td>
		<td><input type="text" name="image5_high_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image5_high_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image5_high_noise_<?php echo $amp; ?>" value="<?php echo ${'image5_high_noise_' . $amp}; ?>"></td>
		<td><input type="text" name="image5_high_comments_<?php echo $amp; ?>" value="<?php echo ${'image5_high_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image5_high_reference_<?php echo $amp; ?>" value="<?php echo ${'image5_high_reference_' . $amp}; ?>" size="50"></td>
	    </tr>
	<?php
        $count++;
	endforeach;
	?>

	<tr>
	    <td align="left" colspan="9" style="border: none; white-space: nowrap;">
		<input type="submit" value="Submit">
		&nbsp &nbsp &nbsp &nbsp;
		<?php
		// Retrieve the current module_surface_id
		$module_surface_id = isset($_SESSION['choosen_module_surface']) ? $_SESSION['choosen_module_surface'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_module_surface/module_surface_' . $module_surface_id . '/';

		// Check if image5_high_file already exists in the directory even if no form is submitted
		$image5_high_file_name = 'image5_high_file.png';
		$image5_high_file_path = $upload_dir . $image5_high_file_name;  // Use the absolute server path for file_exists()
                $image5_high_log_name = 'image5_high_log.log';
                $image5_high_log_path = $upload_dir . $image5_high_log_name;  // Use the absolute server path for file_exists()

		// If image5_high_file exists in the directory but no session is set, initialize the session
		if (file_exists($image5_high_file_path) && !isset($_SESSION['file_url_' . $module_surface_id]['image5_high_file'])) {
		    $_SESSION['file_url_' . $module_surface_id]['image5_high_file'] = $base_url . $image5_high_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image5_high_file_name) && file_exists($image5_high_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $module_surface_id]['image5_high_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image5_high_log exists in the directory but no session is set, initialize the session
                if (file_exists($image5_high_log_path) && !isset($_SESSION['log_url_' . $module_surface_id]['image5_high_log'])) {
                    $_SESSION['log_url_' . $module_surface_id]['image5_high_log'] = $base_url . $image5_high_log_name;  // Use base_url for the web link
                }

		// Check if the log exists on the server (log system path)
                if (!empty($image5_high_log_name) && file_exists($image5_high_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $module_surface_id]['image5_high_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                    <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
			<img src="pixmaps/icon.png" alt="Image5_High File" style="height: 20px; width: auto;">
                    </a>
                <?php endif; ?>
                <label for="image5_high_file">Image File:</label>
                <input type="file" name="image5_high_file" accept="image/png, image/jpeg, application/pdf">
                &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                    <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
			<img src="pixmaps/icon2.png" alt="Image5_High Log" style="height: 20px; width: auto;">
                    </a>
                <?php endif; ?>
                <label for="image5_high_log">Log File:</label>
                <input type="file" name="image5_high_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>
