<?php
// edit_die.php
// D.Norcini, Hopkins, 2024

session_start();

$req_priv = "full";
include("db_login.php");
include("page_setup.php");
include("aux/array_defs.php"); // Import all array definitions

$table = "DIE";

// Clear session variables when die_id changes to prevent data from other dies interfering
if (isset($_POST['choosen']) && $_POST['choosen'] !== $_SESSION['choosen_die']) {
    unset($_SESSION['log_url'],$_SESSION['file_url'], $_SESSION['upload_dir'], $_SESSION['base_url'], $_SESSION['log_exists'], $_SESSION['file_exists']);
}

// Set the chosen die based on session or POST data
if (!empty($_SESSION['req_id'])) {
    $_SESSION['choosen_die'] = $_SESSION['req_id'];
    unset($_SESSION['req_id']);
}

if (isset($_POST['choosen'])) {
    $_SESSION['choosen_die'] = $_POST['choosen'];
}

if (empty($_SESSION['choosen_die'])) {
    $_SESSION['choosen_die'] = 1;
}

// Create a new entry if requested
if (isset($_POST['new'])) {
    $query = "INSERT INTO `" . $table . "` (`ID`, `Last_update`) VALUES (NULL, '" . time() . "')";
    $result = mysql_query($query);
    if (!$result) {
        die("Could not query the database <br />" . mysql_error());
    }
    include("aux/get_last_table_id.php");
    $_SESSION['choosen_die'] = $last_id;

    // Reset only the problematic section when creating a new die
    $check_L1 = $check_L2 = $check_U1 = $check_U2 = 0;
    $grade_L1 = $grade_L2 = $grade_U1 = $grade_U2 = '';
    $notes_L1 = $notes_L2 = $notes_U1 = $notes_U2 = '';

    // Clear any session variables related to file uploads
    unset($_SESSION['log_url'], $_SESSION['file_url'], $_SESSION['upload_dir'], $_SESSION['base_url'], $_SESSION['log_exists'], $_SESSION['file_exists']);
}

// Navigation logic (First, Last, Previous, Next)
if (isset($_POST['first'])) {
    $_SESSION['choosen_die'] = 1;
}
if (isset($_POST['last'])) {
    include("aux/get_last_die_id.php");
    $_SESSION['choosen_die'] = $last_id;
}
if (isset($_POST['prev'])) {
    $_SESSION['choosen_die'] = max(1, $_SESSION['choosen_die'] - 1);
}
if (isset($_POST['next'])) {
    $_SESSION['choosen_die'] = $_SESSION['choosen_die'] + 1;
}

// Ensure we never go below 1 or above the last DIE ID
include("aux/get_last_die_id.php");
if ($_SESSION['choosen_die'] < 1) {
    $_SESSION['choosen_die'] = 1;
}
if ($_SESSION['choosen_die'] > $last_id) {
    $_SESSION['choosen_die'] = $last_id;
}

// Assign the chosen die to the $id variable
$id = (int)$_SESSION['choosen_die'];

// Fetch die details
include("aux/get_die_vals.php");

// Update die details if form is submitted
if (isset($_POST['id'])) {
    $die_id = (int)$_POST['id'];
    
    // Clear session variables when switching dies
    if (!isset($_SESSION['choosen_die']) || $_SESSION['choosen_die'] !== $die_id) {
        unset($_SESSION['file_url_' . $die_id], $_SESSION['file_exists_' . $die_id], $_SESSION['log_url_' . $die_id], $_SESSION['log_exists_' . $die_id]);
    }

    // Clear session variables related to file uploads when die_id changes
    unset($_SESSION['file_url_' . $die_id], $_SESSION['upload_dir_' . $die_id], $_SESSION['base_url_' . $die_id], $_SESSION['file_exists_' . $die_id], $_SESSION['log_url_' . $die_id], $_SESSION['log_exists_' . $die_id]);

    // Directory to store uploaded images
    $upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';
    $base_url = '/uploads/edit_die/' . 'die_' . $die_id; // Web-accessible URL
    $_SESSION['upload_dir_' . $die_id] = $upload_dir;
    $_SESSION['base_url_' . $die_id] = $base_url;

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
    $allowed_types = array('image/png', 'image/jpeg', 'application/pdf');
    $allowed_logs = array('text/plain');

    // ======================
    // Upload Trace and Image Files
    // ======================
    $file_fields = ['trace_file'];
    $log_fields = ['trace_log'];


    // Add image fields dynamically for image1 to image6
    for ($i = 1; $i <= 6; $i++) {
	$file_fields[] = 'image' . $i . '_file';
	$log_fields[] = 'image' . $i . '_log';

    }

    // Ensure the session arrays are initialized if not already
    if (!isset($_SESSION['file_url_' . $die_id])) {
	$_SESSION['file_url_' . $die_id] = [];
    }
    if (!isset($_SESSION['file_exists_' . $die_id])) {
	$_SESSION['file_exists_' . $die_id] = [];
    }

    if (!isset($_SESSION['log_url_' . $die_id])) {
	$_SESSION['log_url_' . $die_id] = [];
    }
    if (!isset($_SESSION['log_exists_' . $die_id])) {
	$_SESSION['log_exists_' . $die_id] = [];
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
                    // Save the file name to the session specific to this die
                    $_SESSION['file_url_' . $die_id][$file_field] = $base_url . '/' . $file_name;
                    $_SESSION['file_exists_' . $die_id][$file_field] = true;
		} else {
                    echo "Failed to upload file: " . $file_name . '<br>';
		}
            } else {
		echo "Invalid file type for: " . $_FILES[$file_field]['name'] . '<br>';
            }
	} else {
            // No new upload, but check if there is an existing session value
            if (isset($_SESSION['file_url_' . $die_id][$file_field])) {
		echo "$file_field session file_url: " . $_SESSION['file_url_' . $die_id][$file_field] . '<br>';
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
                   // Save the log name to the session specific to this die
                   $_SESSION['log_url_' . $die_id][$log_field] = $base_url . '/' . $log_name;
                   $_SESSION['log_exists_' . $die_id][$log_field] = true;
            	} else {
                  echo "Failed to upload log: " . $log_name . '<br>';
                }
       	    } else {
              echo "Invalid file type for: " . $_FILES[$log_field]['name'] . '<br>';
            }
    	  } else {
            // No new upload, but check if there is an existing session value
            if (isset($_SESSION['log_url_' . $die_id][$log_field])) {
            echo "$log_field session log_url: " . $_SESSION['log_url_' . $die_id][$log_field] . '<br>';
          }
    }
}

    // ======================
    // Loop Through Amplifiers for Other Fields
    // ======================
    foreach ($amplifiers as $amp) {
        // Loop through trace fields related to amplifiers
        foreach ($trace_fields as $base_field) {
            $field_name = $base_field . $amp; // E.g., trace_saturation_L1

            // Initialize the field in the array using $_POST if available, or set a default value
            $dynamic_fields[$field_name] = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

            // Add this to the query if it's not empty
            if (!empty($dynamic_fields[$field_name])) {
                $query_parts[] = "`$field_name` = '" . mysql_real_escape_string($dynamic_fields[$field_name]) . "'";
            }
        }

        // Loop through image fields related to amplifiers
        foreach ($image_numbers as $number_field) {
            foreach ($image_fields as $base_field) {
                $field_name = 'image' . $number_field . $base_field . $amp; // E.g., image1_tracks_L1

                // Initialize the field in the array using $_POST if available, or set a default value
                $dynamic_fields[$field_name] = isset($_POST[$field_name]) ? $_POST[$field_name] : '';

                // Add this to the query if it's not empty
                if (!empty($dynamic_fields[$field_name])) {
                    $query_parts[] = "`$field_name` = '" . mysql_real_escape_string($dynamic_fields[$field_name]) . "'";
                }
            }
        }
    }

    // Construct and execute the final query if there are parts
    if (!empty($query_parts)) {
        $query = "UPDATE `" . $table . "` SET " . implode(', ', $query_parts) . " WHERE `ID` = " . $die_id;
        $result = mysql_query($query);
        if (!$result) {
            die('Query failed: ' . mysql_error());
        }
    }
}

// Fetch die details again after updates
include("aux/get_die_vals.php");
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
function check_and_update_file_session($file_field, $upload_dir, $base_url, $die_id) {
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
function check_and_update_log_session($log_field, $upload_dir, $base_url, $die_id) {
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
                <input type="submit" name="new" value="New DIE" title="Generate a new DIE entry" style="font-size: 10pt">
            </th>
        </form>

        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <th>
                &nbsp;&nbsp;&nbsp;&nbsp;Choose DIE ID: <input type="text" name="choosen" size="6">
            </th>
        </form>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <th>
                <input type="submit" name="first" value="Goto First DIE" title="Go to the first added DIE" style="font-size: 10pt">
                <input type="submit" name="prev" value="Goto Previous DIE" title="Go to the DIE before the currently selected one" style="font-size: 10pt">
                <input type="submit" name="next" value="Goto Next DIE" title="Go to the DIE after the currently selected one" style="font-size: 10pt">
                <input type="submit" name="last" value="Goto Last DIE" title="Go to the last added DIE" style="font-size: 10pt">
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
                DIE ID: <?php echo $id; ?>
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
                Wafer ID: <input type="text" name="wafer_id" value="<?php echo $wafer_id; ?>" size="20">
            </td>
            <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                Wafer Position:
                <?php generate_dropdown('wafer_position', $wafer_positions, $wafer_position); ?>
            </td>
            <td align="left" colspan="1" style="width: 300px; white-space: nowrap;">
                UW Activation [days]: <input type="text" name="activation" value="<?php echo $activation; ?>" size="10">
            </td>
        </tr>
    </table>
    <input type="submit" id="hiddenSubmit" style="display: none;">
</form>
<br><br>

<!-- Preliminary Grade Assessment -->
<?php echo "<b>Preliminary Grade Assessment</b>";?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
      <input type="hidden" name="id" value="<?php echo $id; ?>">
      <table border="1" cellpadding="2" width="100%">
	<tr>
            <td><?php echo "L1"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_L1', $grade_array, $grade_L1); ?>
            </td>
            <td style="width: 5%;">
                <!-- Hidden field to ensure unchecked checkboxes are handled -->
                <input type="hidden" name="check_L1" value="0">
		<input type="checkbox" name="check_L1" value="1" <?php echo ($check_L1 == 1) ? 'checked' : ''; ?>>
            </td>

            <td><?php echo "L2"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_L2', $grade_array, $grade_L2); ?>
            </td>
            <td style="width: 5%;">
                <!-- Hidden field to ensure unchecked checkboxes are handled -->
                <input type="hidden" name="check_L2" value="0">
		<input type="checkbox" name="check_L2" value="1" <?php echo ($check_L2 == 1) ? 'checked' : ''; ?>>
            </td>

            <td><?php echo "U1"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_U1', $grade_array, $grade_U1); ?>
            </td>
            <td style="width: 5%;">
                <!-- Hidden field to ensure unchecked checkboxes are handled -->
                <input type="hidden" name="check_U1" value="0">
		<input type="checkbox" name="check_U1" value="1" <?php echo ($check_U1 == 1) ? 'checked' : ''; ?>>
            </td>

            <td><?php echo "U2"; ?></td>
            <td style="width: 20%;">
		<?php generate_dropdown('grade_U2', $grade_array, $grade_U2); ?>
            </td>
            <td style="width: 5%;">
                <!-- Hidden field to ensure unchecked checkboxes are handled -->
                <input type="hidden" name="check_U2" value="0">
		<input type="checkbox" name="check_U2" value="1" <?php echo ($check_U2 == 1) ? 'checked' : ''; ?>>
            </td>
	</tr>
	<tr>
	    <td colspan="3">
                <textarea name="notes_L1" rows="2" style="width: 100%;"><?php echo $notes_L1; ?></textarea>
            </td>
	    <td colspan="3">
                <textarea name="notes_L2" rows="2" style="width: 100%;"><?php echo $notes_L2; ?></textarea>
            </td>
	    <td colspan="3">
                <textarea name="notes_U1" rows="2" style="width: 100%;"><?php echo $notes_U1; ?></textarea>
            </td>
            <td colspan="3">
                <textarea name="notes_U2" rows="2" style="width: 100%;"><?php echo $notes_U2; ?></textarea>
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
            <td>TempC [K]: <input type="text" name="temp" value="<?php echo $temp; ?>" size="10"></td>
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
<?php echo "<b>Trace</b>"; ?>
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
	foreach ($amplifiers as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ch" . $count . ")"; ?></td>
		<td>
		    <?php generate_dropdown('trace_saturation_' . $amp, $yes_no_blank_array, ${'trace_saturation_' . $amp}); ?>
		</td>
		<td><input type="text" name="trace_comments_<?php echo $amp; ?>" value="<?php echo ${'trace_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="trace_reference_<?php echo $amp; ?>" value="<?php echo ${'trace_reference_' . $amp}; ?>" size="50"></td>
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
		// Retrieve the current die_id
		$die_id = isset($_SESSION['choosen_die']) ? $_SESSION['choosen_die'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_die/die_' . $die_id . '/';

		// Check if trace_file already exists in the directory even if no form is submitted
		$trace_file_name = 'trace_file.png';
		$trace_file_path = $upload_dir . $trace_file_name;  // Use the absolute server path for file_exists()
		$trace_log_name = 'trace_log.log';
		$trace_log_path = $upload_dir . $trace_log_name;  // Use the absolute server path for file_exists()
		
		// If trace_file exists in the directory but no session is set, initialize the session
		if (file_exists($trace_file_path) && !isset($_SESSION['file_url_' . $die_id]['trace_file'])) {
		    $_SESSION['file_url_' . $die_id]['trace_file'] = $base_url . $trace_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($trace_file_name) && file_exists($trace_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $die_id]['trace_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If trace_log exists in the directory but no session is set, initialize the session
                if (file_exists($trace_log_path) && !isset($_SESSION['log_url_' . $die_id]['trace_log'])) {
                    $_SESSION['log_url_' . $die_id]['trace_log'] = $base_url . $trace_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($trace_log_name) && file_exists($trace_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
		    $log_url = $_SESSION['log_url_' . $die_id]['trace_log'];
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
		      <label for="trace_file">Image File:</label>
		      <input type="file" name="trace_file" accept="image/png, image/jpeg, application/pdf">
		      &nbsp; &nbsp; &nbsp; &nbsp;

		<?php if ($log_exists): ?>
    		      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
        	      <img src="pixmaps/icon2.png" alt="Trace Log" style="height: 20px; width: auto;">
    		      </a>
		<?php endif; ?>
		      <label for="trace_log">Log File:</label>
		      <input type="file" name="trace_log" accept=".log,text/plain">
 	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 1 - Track Defects -->
<?php echo "<b>Image 1 - [1skip, 10x10binning, 160rx640c, Active region, 60s Exposure] - Aim: To see tracks</b>"; ?>
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

	$count = 1;
	foreach ($amplifiers as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ch" . $count . ")"; ?></td>
		<td>
		    <?php generate_dropdown('image1_tracks_' . $amp, $yes_no_blank_array, ${'image1_tracks_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image1_defects_' . $amp, $yes_no_blank_array, ${'image1_defects_' . $amp}); ?>
		</td>
		<td><input type="text" name="image1_noise_<?php echo $amp; ?>" value="<?php echo ${'image1_noise_' . $amp}; ?>"></td>
		<td><input type="text" name="image1_comments_<?php echo $amp; ?>" value="<?php echo ${'image1_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image1_reference_<?php echo $amp; ?>" value="<?php echo ${'image1_reference_' . $amp}; ?>" size="50"></td>
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
		// Retrieve the current die_id
		$die_id = isset($_SESSION['choosen_die']) ? $_SESSION['choosen_die'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_die/die_' . $die_id . '/';

		// Check if image1_file already exists in the directory even if no form is submitted
		$image1_file_name = 'image1_file.png';
		$image1_file_path = $upload_dir . $image1_file_name;  // Use the absolute server path for file_exists()
                $image1_log_name = 'image1_log.log';
                $image1_log_path = $upload_dir . $image1_log_name;  // Use the absolute server path for file_exists()
		
		// If image1_file exists in the directory but no session is set, initialize the session
		if (file_exists($image1_file_path) && !isset($_SESSION['file_url_' . $die_id]['image1_file'])) {
		    $_SESSION['file_url_' . $die_id]['image1_file'] = $base_url . $image1_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image1_file_name) && file_exists($image1_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $die_id]['image1_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image1_log exists in the directory but no session is set, initialize the session
                if (file_exists($image1_log_path) && !isset($_SESSION['log_url_' . $die_id]['image1_log'])) {
                    $_SESSION['log_url_' . $die_id]['image1_log'] = $base_url . $image1_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image1_log_name) && file_exists($image1_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $die_id]['image1_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image1 File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image1_file">Image File:</label>
                      <input type="file" name="image1_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image1 Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image1_log">Log File:</label>
                      <input type="file" name="image1_log" accept=".log,text/plain">
	   </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 2 - Defect Map, Sharpness of Tracks, CTI, Noise -->
<?php echo "<b>Image 2 - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise</b>"; ?>
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

	$count = 1;
	foreach ($amplifiers as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ch" . $count . ")"; ?></td>
		<td><input type="text" name="image2_pixel_defects_<?php echo $amp; ?>" value="<?php echo ${'image2_pixel_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image2_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image2_region_defect_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image2_noise_overscan_' . $amp}; ?>"></td>
		<td><input type="text" name="image2_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image2_cti_code_' . $amp}; ?>"></td>
		<td>
		    <?php generate_dropdown('image2_cti_visual_' . $amp, $yes_no_blank_array, ${'image2_cti_visual_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image2_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image2_sharpness_tracks_' . $amp}); ?>
		</td>
		<td><input type="text" name="image2_comments_<?php echo $amp; ?>" value="<?php echo ${'image2_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image2_reference_<?php echo $amp; ?>" value="<?php echo ${'image2_reference_' . $amp}; ?>" size="50"></td>
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
		// Retrieve the current die_id
		$die_id = isset($_SESSION['choosen_die']) ? $_SESSION['choosen_die'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_die/die_' . $die_id . '/';

		// Check if image2_file already exists in the directory even if no form is submitted
		$image2_file_name = 'image2_file.png';
		$image2_file_path = $upload_dir . $image2_file_name;  // Use the absolute server path for file_exists()
                $image2_log_name = 'image2_log.log';
                $image2_log_path = $upload_dir . $image2_log_name;  // Use the absolute server path for file_exists()

		// If image2_file exists in the directory but no session is set, initialize the session
		if (file_exists($image2_file_path) && !isset($_SESSION['file_url_' . $die_id]['image2_file'])) {
		    $_SESSION['file_url_' . $die_id]['image2_file'] = $base_url . $image2_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image2_file_name) && file_exists($image2_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $die_id]['image2_file'];
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image2_log exists in the directory but no session is set, initialize the session
                if (file_exists($image2_log_path) && !isset($_SESSION['log_url_' . $die_id]['image2_log'])) {
                    $_SESSION['log_url_' . $die_id]['image2_log'] = $base_url . $image2_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image2_log_name) && file_exists($image2_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $die_id]['image2_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image2 File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image2_file">Image File:</label>
                      <input type="file" name="image2_file" accept="image/png, image/jpeg, application/pdf">
		      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image2 Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image2_log">Log File:</label>
                      <input type="file" name="image2_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 3 - Track Defects, High VSub and V Clk -->
<?php echo "<b>Image 3 - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure, high VSub and V Clk] - Aim: Defect Map, Sharpness of tracks, CTI, Noise</b>"; ?>
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

	foreach ($amplifiers as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ch" . $count . ")"; ?></td>
		<td><input type="text" name="image3_pixel_defects_<?php echo $amp; ?>" value="<?php echo ${'image3_pixel_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image3_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image3_region_defect_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image3_noise_overscan_' . $amp}; ?>"></td>
		<td><input type="text" name="image3_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image3_cti_code_' . $amp}; ?>"></td>
		<td>
		    <?php generate_dropdown('image3_cti_visual_' . $amp, $yes_no_blank_array, ${'image3_cti_visual_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image3_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image3_sharpness_tracks_' . $amp}); ?>
		</td>
		<td><input type="text" name="image3_comments_<?php echo $amp; ?>" value="<?php echo ${'image3_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image3_reference_<?php echo $amp; ?>" value="<?php echo ${'image3_reference_' . $amp}; ?>" size="50"></td>
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
		// Retrieve the current die_id
		$die_id = isset($_SESSION['choosen_die']) ? $_SESSION['choosen_die'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_die/die_' . $die_id . '/';

		// Check if image3_file already exists in the directory even if no form is submitted
		$image3_file_name = 'image3_file.png';
		$image3_file_path = $upload_dir . $image3_file_name;  // Use the absolute server path for file_exists()
                $image3_log_name = 'image3_log.log';
                $image3_log_path = $upload_dir . $image3_log_name;  // Use the absolute server path for file_exists()

		// If image3_file exists in the directory but no session is set, initialize the session
		if (file_exists($image3_file_path) && !isset($_SESSION['file_url_' . $die_id]['image3_file'])) {
		    $_SESSION['file_url_' . $die_id]['image3_file'] = $base_url . $image3_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image3_file_name) && file_exists($image3_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $die_id]['image3_file'];
		    echo "image3_file exists, URL: " . $file_url . "<br>";
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image3_log exists in the directory but no session is set, initialize the session
                if (file_exists($image3_log_path) && !isset($_SESSION['log_url_' . $die_id]['image3_log'])) {
                    $_SESSION['log_url_' . $die_id]['image3_log'] = $base_url . $image3_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image3_log_name) && file_exists($image3_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $die_id]['image3_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image3 File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image3_file">Image File:</label>
                      <input type="file" name="image3_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image3 Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image3_log">Log File:</label>
                      <input type="file" name="image3_log" accept=".log,text/plain">
	   </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 4 - Single Electron Resolution -->
<?php echo "<b>Image 4 - [1000skip, 1x1binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution</b>"; ?>
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

	$count = 1;
	foreach ($amplifiers as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ch" . $count . ")"; ?></td>
		<td><input type="text" name="image4_res_<?php echo $amp; ?>" value="<?php echo ${'image4_res_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_gain_<?php echo $amp; ?>" value="<?php echo ${'image4_gain_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_dark_current_<?php echo $amp; ?>" value="<?php echo ${'image4_dark_current_' . $amp}; ?>"></td>
		<td><input type="text" name="image4_comments_<?php echo $amp; ?>" value="<?php echo ${'image4_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image4_reference_<?php echo $amp; ?>" value="<?php echo ${'image4_reference_' . $amp}; ?>" size="50"></td>
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
		// Retrieve the current die_id
		$die_id = isset($_SESSION['choosen_die']) ? $_SESSION['choosen_die'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_die/die_' . $die_id . '/';

		// Check if image4_file already exists in the directory even if no form is submitted
		$image4_file_name = 'image4_file.png';
		$image4_file_path = $upload_dir . $image4_file_name;  // Use the absolute server path for file_exists()
                $image4_log_name = 'image4_log.log';
                $image4_log_path = $upload_dir . $image4_log_name;  // Use the absolute server path for file_exists()

		// If image4_file exists in the directory but no session is set, initialize the session
		if (file_exists($image4_file_path) && !isset($_SESSION['file_url_' . $die_id]['image4_file'])) {
		    $_SESSION['file_url_' . $die_id]['image4_file'] = $base_url . $image4_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image4_file_name) && file_exists($image4_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $die_id]['image4_file'];
		    echo "image4_file exists, URL: " . $file_url . "<br>";
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image4_log exists in the directory but no session is set, initialize the session
                if (file_exists($image4_log_path) && !isset($_SESSION['log_url_' . $die_id]['image4_log'])) {
                    $_SESSION['log_url_' . $die_id]['image4_log'] = $base_url . $image4_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image4_log_name) && file_exists($image4_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $die_id]['image4_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image4 File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image4_file">Image File:</label>
                      <input type="file" name="image4_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image4 Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image4_log">Log File:</label>
                      <input type="file" name="image4_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 5 - Serial Register Defect -->
<?php echo "<b>Image 5 - [1skip, 1x1binning, 30rx3400c, Serial register, 10s Exposure] - Aim: Serial Register Defect</b>"; ?>
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

	$count = 1;
	foreach ($amplifiers as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ch" . $count . ")"; ?></td>
		<td><input type="text" name="image5_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image5_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image5_noise_<?php echo $amp; ?>" value="<?php echo ${'image5_noise_' . $amp}; ?>"></td>
		<td><input type="text" name="image5_comments_<?php echo $amp; ?>" value="<?php echo ${'image5_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image5_reference_<?php echo $amp; ?>" value="<?php echo ${'image5_reference_' . $amp}; ?>" size="50"></td>
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
		// Retrieve the current die_id
		$die_id = isset($_SESSION['choosen_die']) ? $_SESSION['choosen_die'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_die/die_' . $die_id . '/';

		// Check if image5_file already exists in the directory even if no form is submitted
		$image5_file_name = 'image5_file.png';
		$image5_file_path = $upload_dir . $image5_file_name;  // Use the absolute server path for file_exists()
                $image5_log_name = 'image5_log.log';
                $image5_log_path = $upload_dir . $image5_log_name;  // Use the absolute server path for file_exists()

		// If image5_file exists in the directory but no session is set, initialize the session
		if (file_exists($image5_file_path) && !isset($_SESSION['file_url_' . $die_id]['image5_file'])) {
		    $_SESSION['file_url_' . $die_id]['image5_file'] = $base_url . $image5_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image5_file_name) && file_exists($image5_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $die_id]['image5_file'];
		    echo "image5_file exists, URL: " . $file_url . "<br>";
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image5_log exists in the directory but no session is set, initialize the session
                if (file_exists($image5_log_path) && !isset($_SESSION['log_url_' . $die_id]['image5_log'])) {
                    $_SESSION['log_url_' . $die_id]['image5_log'] = $base_url . $image5_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image5_log_name) && file_exists($image5_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $die_id]['image5_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image5 File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image5_file">Image File:</label>
                      <input type="file" name="image5_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image5 Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image5_log">Log File:</label>
                      <input type="file" name="image5_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

<!-- Image 6 - Defect Map, Sharpness of Tracks, CTI, Noise -->
<?php echo "<b>Image 6 - [1skip, 1x1binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise</b>"; ?>
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" enctype="multipart/form-data">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <table border="1">
	<tr>
            <td align="left" style="width: 5%; white-space: nowrap;">Amplifier</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Number Column Defects</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Defect Region</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Overscan Noise [ADU]</td>
            <td align="left" style="width: 5%; white-space: nowrap;">CTI? - Code</td>
            <td align="left" style="width: 5%; white-space: nowrap;">CTI? - Visual</td>
            <td align="left" style="width: 5%; white-space: nowrap;">Sharp Tracks?</td>
            <td align="left" style="width: 35%; white-space: nowrap;">Comments</td>
            <td align="left" style="width: 45%; white-space: nowrap;">Reference Image</td>
	</tr>

	<?php
	$count = 1;
	foreach ($amplifiers as $amp):
	?>
	    <tr>
		<td><?php echo $amp . " (ch" . $count . ")"; ?></td>
		<td><input type="text" name="image6_column_defects_<?php echo $amp; ?>" value="<?php echo ${'image6_column_defects_' . $amp}; ?>"></td>
		<td><input type="text" name="image6_region_defect_<?php echo $amp; ?>" value="<?php echo ${'image6_region_defect_' . $amp}; ?>"></td>
		<td><input type="text" name="image6_noise_overscan_<?php echo $amp; ?>" value="<?php echo ${'image6_noise_overscan_' . $amp}; ?>"></td>
		<td><input type="text" name="image6_cti_code_<?php echo $amp; ?>" value="<?php echo ${'image6_cti_code_' . $amp}; ?>"></td>
		<td>
		    <?php generate_dropdown('image6_cti_visual_' . $amp, $yes_no_blank_array, ${'image6_cti_visual_' . $amp}); ?>
		</td>
		<td>
		    <?php generate_dropdown('image6_sharpness_tracks_' . $amp, $yes_no_blank_array, ${'image6_sharpness_tracks_' . $amp}); ?>
		</td>
		<td><input type="text" name="image6_comments_<?php echo $amp; ?>" value="<?php echo ${'image6_comments_' . $amp}; ?>" size="40"></td>
		<td><input type="text" name="image6_reference_<?php echo $amp; ?>" value="<?php echo ${'image6_reference_' . $amp}; ?>" size="50"></td>
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
		// Retrieve the current die_id
		$die_id = isset($_SESSION['choosen_die']) ? $_SESSION['choosen_die'] : 0;

		// Absolute path on the server's file system
		$upload_dir = '/home/uploads/edit_die/die_' . $die_id . '/';  // This should be the actual server file path

		// Web URL for accessing files via the browser
		$base_url = '/uploads/edit_die/die_' . $die_id . '/';

		// Check if image6_file already exists in the directory even if no form is submitted
		$image6_file_name = 'image6_file.png';
		$image6_file_path = $upload_dir . $image6_file_name;  // Use the absolute server path for file_exists()
                $image6_log_name = 'image6_log.log';
                $image6_log_path = $upload_dir . $image6_log_name;  // Use the absolute server path for file_exists()
		
		// If image6_file exists in the directory but no session is set, initialize the session
		if (file_exists($image6_file_path) && !isset($_SESSION['file_url_' . $die_id]['image6_file'])) {
		    $_SESSION['file_url_' . $die_id]['image6_file'] = $base_url . $image6_file_name;  // Use base_url for the web link
		}

		// Check if the file exists on the server (file system path)
		if (!empty($image6_file_name) && file_exists($image6_file_path)) {
		    // File exists, keep the session variables and show the icon
		    $file_exists = true;
		    $file_url = $_SESSION['file_url_' . $die_id]['image6_file'];
		    echo "image6_file exists, URL: " . $file_url . "<br>";
		} else {
		    // File does not exist, clear the session variables and remove the icon
		    $file_exists = false;
		}

                // If image6_log exists in the directory but no session is set, initialize the session
                if (file_exists($image6_log_path) && !isset($_SESSION['log_url_' . $die_id]['image6_log'])) {
                    $_SESSION['log_url_' . $die_id]['image6_log'] = $base_url . $image6_log_name;  // Use base_url for the web link
                }

               // Check if the log exists on the server (log system path)
                if (!empty($image6_log_name) && file_exists($image6_log_path)) {
                    // Log exists, keep the session variables and show the icon
                    $log_exists = true;
                    $log_url = $_SESSION['log_url_' . $die_id]['image6_log'];
                } else {
                    // Log does not exist, clear the session variables and remove the icon
                    $log_exists = false;
                }

                ?>

                <?php if ($file_exists): ?>
                      <a href="<?php echo htmlspecialchars($file_url); ?>" target="_blank">
                      <img src="pixmaps/icon.png" alt="Image6 File" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image6_file">Image File:</label>
                      <input type="file" name="image6_file" accept="image/png, image/jpeg, application/pdf">
                      &nbsp; &nbsp; &nbsp; &nbsp;

                <?php if ($log_exists): ?>
                      <a href="<?php echo htmlspecialchars($log_url); ?>" target="_blank">
                      <img src="pixmaps/icon2.png" alt="Image6 Log" style="height: 20px; width: auto;">
                      </a>
                <?php endif; ?>
                      <label for="image6_log">Log File:</label>
                      <input type="file" name="image6_log" accept=".log,text/plain">
	    </td>
	</tr>
    </table>
</form>
<br><br>

