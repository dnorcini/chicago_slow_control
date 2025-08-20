<?php
// module_comparison.php
// Cinyu Zhu, Hopkins, 2025
include("module_comparison_helper.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
?>

<!-- HTML Form for Data Input -->
<!-- select module ID  -->
<table border="1" cellpadding="2" width="100%">

    <tr>
        <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <th>
                &nbsp;&nbsp;&nbsp;&nbsp;Choose MODULE ID: <input type="text" name="choosen" size="6">
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

<!-- basic info about the module -->
<table border="1" cellpadding="2" width="100%">
    <tr>
        <td style="width: 300px; white-space: nowrap;">
            MODULE ID: <?php echo $id; ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Name: <?php echo $sur_name; ?>
        </td>
        <td style="width: 300px; white-space: nowrap;">
            Status: <?php echo htmlspecialchars($sur_status); ?>
        </td>
        <td style="width: 300px; white-space: nowrap;">
            Entry Last updated: <?php echo date("G:i:s M d, Y", $last_update); ?> (ET)
        </td>
    </tr>
    <tr>
        <td style="width: 300px; white-space: nowrap;">
            Pitch adaptor ID: <?php echo htmlspecialchars($sur_pitch_adaptor_id); ?>
        </td>
        <td style="width: 300px; white-space: nowrap;">
            UW Activation [days]: <?php echo htmlspecialchars($sur_activation); ?>
        </td>
    </tr>
    <tr>
        <td style="width: 300px; white-space: nowrap;">
            Packaging humidity [%]: <?php echo htmlspecialchars($sur_humidity); ?>
        </td>
        <td style="width: 300px; white-space: nowrap;">
            Packaging radon [Bq/m^3]: <?php echo htmlspecialchars($sur_radon); ?>
        </td>
    </tr>
</table>
<br><br>

<!-- Module layout -->
<?php echo "<b>Module layout</b>"; ?>
<table border="1" cellpadding="2" width="100%">
    <tr>
        <td>
            <?php if (!empty($sur_die_A)): ?>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                    DIE ID
                    <input type="hidden" name="go" value="<?php echo htmlspecialchars($sur_die_A); ?>">
                    <input type="submit" value="A" style="font-size: 14pt;">
                </form>
            <?php else: ?>
                DIE ID A:
            <?php endif; ?>
            <?php echo htmlspecialchars($sur_die_A ?: 'N/A'); ?>
            &nbsp;&nbsp;&nbsp;&nbsp; Amp A: <?php echo htmlspecialchars($sur_amp_A ?: 'N/A'); ?>
        </td>
        <td>
            <?php if (!empty($sur_die_B)): ?>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                    DIE ID
                    <input type="hidden" name="go" value="<?php echo htmlspecialchars($sur_die_B); ?>">
                    <input type="submit" value="B" style="font-size: 14pt;">
                </form>
            <?php else: ?>
                DIE ID B:
            <?php endif; ?>
            <?php echo htmlspecialchars($sur_die_B ?: 'N/A'); ?>
            &nbsp;&nbsp;&nbsp;&nbsp; Amp B: <?php echo htmlspecialchars($sur_amp_B ?: 'N/A'); ?>
        </td>
        <td>
            <?php if (!empty($sur_die_C)): ?>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                    DIE ID
                    <input type="hidden" name="go" value="<?php echo htmlspecialchars($sur_die_C); ?>">
                    <input type="submit" value="C" style="font-size: 14pt;">
                </form>
            <?php else: ?>
                DIE ID C:
            <?php endif; ?>
            <?php echo htmlspecialchars($sur_die_C ?: 'N/A'); ?>
            &nbsp;&nbsp;&nbsp;&nbsp; Amp C: <?php echo htmlspecialchars($sur_amp_C ?: 'N/A'); ?>
        </td>
        <td>
            <?php if (!empty($sur_die_D)): ?>
                <form method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>" style="display:inline;">
                    DIE ID
                    <input type="hidden" name="go" value="<?php echo htmlspecialchars($sur_die_D); ?>">
                    <input type="submit" value="A" style="font-size: 14pt;">
                </form>
            <?php else: ?>
                DIE ID D:
            <?php endif; ?>
            <?php echo htmlspecialchars($sur_die_D ?: 'N/A'); ?>
            &nbsp;&nbsp;&nbsp;&nbsp; Amp D: <?php echo htmlspecialchars($sur_amp_D ?: 'N/A'); ?>
        </td>
    </tr>
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

<b>Preliminary Grade Assessment, channel mapping, grades, notes </b>
<br><br>
<!-- trace -->
<?php echo "<b>Trace, High Temp </b>"; ?>
<table border="3">
    <tr>
        <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
        <td align="center" style="width: 20%; white-space: nowrap;">Saturation/Low? [~80000 ADU]</td>
        <td align="center" style="width: 35%; white-space: nowrap;">Comments</td>
        <td align="center" style="width: 35%; white-space: nowrap;">Reference Image</td>
    </tr>

    <tr>
        <td colspan="4" style="border-bottom: 3px solid black;"></td>
    </tr>
    <?php
    $count = 0;
    $count_plus = 1;

    // Map short prefixes to nicer display labels
    $typeMap = [
        'sur_' => 'surface',
        'udg_' => 'underground'
    ];

    foreach ($ccds as $amp):
        foreach ($typeMap as $prefix => $label):
    ?>
            <tr>
                <td align="center">
                    <?php echo "ch" . $count . " (ext" . $count_plus . ") - " . $label; ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'trace_high_saturation_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'trace_high_comments_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'trace_high_reference_' . $amp}); ?>
                </td>
            </tr>
    <?php
        endforeach;
        echo '<tr><td colspan="4" style="border-bottom: 3px solid black;"></td></tr>';

        $count++;
        $count_plus++;
    endforeach;
    ?>

    <?php
    $id = $_SESSION['choosen_module_underground'];

    $types = [
        'surface'     => 'Surface',
        'underground' => 'Underground'
    ];

    $files = [
        ['name' => 'trace_high_file.png', 'icon' => 'pixmaps/icon.png',  'label' => 'Image File'],
        ['name' => 'trace_high_log.log',  'icon' => 'pixmaps/icon2.png', 'label' => 'Log File']
    ];

    foreach ($types as $folder => $labelSuffix): ?>
        <tr>
            <td colspan="9" style="border:none; white-space:nowrap; text-align:left;">
                <?php
                $dir = "/var/www/html/QC_production/uploads/edit_module_{$folder}/module_{$folder}_$id/";
                $url = "/QC_production/uploads/edit_module_{$folder}/module_{$folder}_$id/";

                foreach ($files as $f) {
                    $path = $dir . $f['name'];
                    if (file_exists($path)) {
                        echo '<a href="' . htmlspecialchars($url . $f['name']) . '" target="_blank">
                      <img src="' . $f['icon'] . '" alt="' . $f['label'] . ' ' . $labelSuffix . '" style="height:20px; width:auto;">
                    </a>';
                    }
                    echo '<label>' . $f['label'] . ' ' . $labelSuffix . '</label>&nbsp;&nbsp;&nbsp;';
                }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>


</table>
<br><br>

<b>Image 1 High </b>
<br><br>

<b>Image 2 High </b>
<br><br>

<b>Image 3 High (1000skips) </b>
<br><br>


<b>Image 4 High </b>
<br><br>


<b>Image 3 Low (1000skips) </b>
<br><br>


<b>Image 4 Low (only the shared fields) </b>
<br><br>