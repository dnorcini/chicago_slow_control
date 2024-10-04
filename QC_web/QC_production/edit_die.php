<?php
// edit_die.php
// D.Norcini, Hopkins, 2024

session_start();
$req_priv = "full";
include("db_login.php");
include("page_setup.php");

$table = "DIE";

// Set chosen die based on session or POST data
if (!empty($_SESSION['req_id'])) {
    $_SESSION['choosen_die'] = $_SESSION['req_id'];
    unset($_SESSION['req_id']);
}

if (!empty($_POST['choosen'])) {
    $_SESSION['choosen_die'] = $_POST['choosen'];
}

if (empty($_SESSION['choosen_die'])) {
    $_SESSION['choosen_die'] = 1;
}

// New entry
if (isset($_POST['new'])) {
    $query = "INSERT INTO `".$table."` (`ID`, `Last_update`) VALUES (NULL, '".time()."')";
    $result = mysql_query($query);
    if (!$result) {
        die("Could not query the database <br />" . mysql_error());
    }

    include("aux/get_last_table_id.php");
    $_SESSION['choosen_die'] = $last_id;
}

// Navigation logic
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

// Ensure the chosen die ID is within valid range
include("aux/get_last_die_id.php");
if ($_SESSION['choosen_die'] < 1) {
    $_SESSION['choosen_die'] = 1;
}

if ($_SESSION['choosen_die'] > $last_id) {
    $_SESSION['choosen_die'] = $last_id;
}

$id = (int)$_SESSION['choosen_die'];

// Update die details
if (isset($_POST['id'])) {
    $die_id = (int)$_POST['id'];

    $fields = ['name', 'status', 'wafer_id', 'wafer_position', 'activation', 'tester', 'test_date', 'test_time', 'chamber', 'temp', 'feedthru_position', 'ACM', 'script', 'image_dir', 'notes'];
    
    foreach ($fields as $field) {
        $_POST[$field] = trim($_POST[$field]);
        if (!get_magic_quotes_gpc()) {
            $_POST[$field] = addslashes($_POST[$field]);
        }
    }
    
    $query = "UPDATE `die` SET 
        `Name` = '" . $_POST['name'] . "',
        `Status` = '" . $_POST['status'] . "',
        `Wafer_ID` = '" . $_POST['wafer_id'] . "',
        `Wafer_Position` = '" . $_POST['wafer_position'] . "',
        `Activation` = '" . $_POST['activation'] . "',
        `Tester` = '" . $_POST['tester'] . "',
        `Test_Date` = '" . $_POST['test_date'] . "',
        `Test_Time` = '" . $_POST['test_time'] . "',
        `Chamber` = '" . $_POST['chamber'] . "',
        `Temp` = '" . $_POST['temp'] . "',
        `Feedthru_Position` = '" . $_POST['feedthru_position'] . "',
        `ACM` = '" . $_POST['ACM'] . "',
        `Script` = '" . $_POST['script'] . "',
        `Image_Dir` = '" . $_POST['image_dir'] . "',
        `Notes` = '" . $_POST['notes'] . "'
    WHERE `ID` = " . $die_id;

    $result = mysql_query($query);
    if (!$result) {
        die("Could not query the database <br />" . mysql_error());
    }
}

// Get selected die values
include("aux/get_die_vals.php");

mysql_close($connection);

// HTML output for form
?>

<br><br>

<table border="1" cellpadding="2" width="100%">
    <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
        <tr>
            <td align="left" colspan="1">
                die ID: <?php echo $id; ?>
            </td>
            <td align="left" colspan="1">
                Name: <input type="text" name="name" value="<?php echo htmlspecialchars($name); ?>" size="10">
            </td>
            <td align="left" colspan="1">
                Entry Last updated: <?php echo date("G:i:s M d, Y", $last_update); ?>
            </td>
        </tr>
        <tr>
            <td align="left" colspan="1">
                Status: 
                <select name="status">
                    <?php
                    $status_array = ['Tested', 'Failed', 'Not tested'];
                    foreach ($status_array as $option): ?>
                        <option value="<?php echo $option; ?>" <?php if ($option === $status) echo 'selected'; ?>>
                            <?php echo $option; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </td>
            <td align="left" colspan="1">
                Wafer ID: <input type="text" name="wafer_id" value="<?php echo htmlspecialchars($wafer_id); ?>" size="10">
            </td>
            <td align="left" colspan="1">
                Wafer Position: <input type="text" name="wafer_position" value="<?php echo htmlspecialchars($wafer_position); ?>" size="10">
            </td>
        </tr>
        <tr>
            <td align="left" colspan="1">
                Activation [days]: <input type="text" name="activation" value="<?php echo htmlspecialchars($activation); ?>" size="10">
            </td>
            <td align="left" colspan="1">
                Tester: <input type="text" name="tester" value="<?php echo htmlspecialchars($tester); ?>" size="10">
            </td>
            <td align="left" colspan="1">
                Test Date: <input type="date" name="test_date" value="<?php echo htmlspecialchars($test_date); ?>">
            </td>
        </tr>
        <tr>
            <td align="left" colspan="1">
                Test Time: <input type="time" name="test_time" value="<?php echo htmlspecialchars($test_time); ?>">
            </td>
            <td align="left" colspan="1">
                JHU Chamber: <input type="text" name="chamber" value="<?php echo htmlspecialchars($chamber); ?>" size="10">
            </td>
            <td align="left" colspan="1">
                TempC [K]: <input type="text" name="temp" value="<?php echo htmlspecialchars($temp); ?>" size="10">
            </td>
        </tr>
        <tr>
            <td align="left" colspan="1">
                Feedthru Position: <input type="text" name="feedthru_position" value="<?php echo htmlspecialchars($feedthru_position); ?>" size="10">
            </td>
            <td align="left" colspan="1">
                ACM number: <input type="text" name="ACM" value="<?php echo htmlspecialchars($ACM); ?>" size="10">
            </td>
            <td align="left" colspan="1">
                Scripts Directory: <input type="text" name="script" value="<?php echo htmlspecialchars($script); ?>" size="10">
            </td>
        </tr>
        <tr>
            <td align="left" colspan="1">
                Image Directory: <input type="text" name="image_dir" value="<?php echo htmlspecialchars($image_dir); ?>" size="10">
            </td>
            <td align="left" colspan="2">
                Notes: <textarea name="notes" rows="2" cols="30"><?php echo htmlspecialchars($notes); ?></textarea>
            </td>
        </tr>
        <tr>
            <td align="left" colspan="3">
                <input type="submit" value="Submit">
            </td>
        </tr>
    </form>
</table>

<br><br>
