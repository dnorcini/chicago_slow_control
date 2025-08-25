<?php
// module_comparison.php
// Cinyu Zhu, Hopkins, 2025
// a readonly page to compare the testing results from 
// yes, there are different styles of html writting in this table alone;...
include("module_comparison_helper.php");
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();
?>

<!-- HTML Form for Data Input -->
<!-- select module ID  -->
<table border="3" cellpadding="2" width="100%">

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
<table border="3" cellpadding="2" width="100%">
    <tr>
        <td style="width: 300px; white-space: nowrap;">
            MODULE ID: <?php echo $id; ?>
            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
            Name: <?php echo $sur_name; ?>
        </td>
        <td style="width: 300px; white-space: nowrap;">
            Status (underground testing): <?php echo htmlspecialchars($udg_status); ?>
        </td>
        <td style="width: 300px; white-space: nowrap;">
            Underground Entry Last updated: <?php echo date("G:i:s M d, Y", $last_update); ?> (ET)
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
<table border="3" cellpadding="2" width="100%">
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
<br><br>

<?php echo "<b>Testing Temperatures</b>"; ?>
<table border="3" cellpadding="2" width="100%">
    <tr>
        <td align="center">
            High Temp C surface: <?php echo htmlspecialchars($sur_temp_high ?: 'N/A'); ?> K
        </td>
        <td align="center">
            High Temp B underground: <?php echo htmlspecialchars($udg_temp_high_B ?: 'N/A'); ?> K
        </td>
        <td align="center">
            High Temp C underground: <?php echo htmlspecialchars($udg_temp_high_C ?: 'N/A'); ?> K
        </td>
    </tr>
    <tr>
        <td align="center">
            Low Temp C surface: <?php echo htmlspecialchars($sur_temp_low ?: 'N/A'); ?> K
        </td>
        <td align="center">
            Low Temp B underground: <?php echo htmlspecialchars($udg_temp_low_B ?: 'N/A'); ?> K
        </td>
        <td align="center">
            Low Temp C underground: <?php echo htmlspecialchars($udg_temp_low_C ?: 'N/A'); ?> K
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

<b>Image 1, High Temp - [1skip, 20x20binning, 80rx320c, Active region, 3s Exposure] - Aim: To see tracks</b>
<table border="3">
    <tr>
        <td align="center" style="width: 7%; white-space: nowrap;">Amplifier</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Tracks?</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Defects?</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Noise [ADU]</td>
        <td align="center" style="width: 33%; white-space: nowrap;">Comments</td>
        <td align="center" style="width: 30%; white-space: nowrap;">Reference Image</td>
    </tr>
    <tr>
        <td colspan="6" style="border-bottom: 3px solid black;"></td>
    </tr>

    <?php
    $count = 0;
    $count_plus = 1;

    // Map short prefixes to nicer display labels


    foreach ($ccds as $amp):
        foreach ($typeMap as $prefix => $label):
    ?>
            <tr>
                <td align="center">
                    <?php echo "ch" . $count . " (ext" . $count_plus . ") - " . $label; ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image1_high_tracks_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image1_high_defects_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image1_high_noise_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image1_high_comments_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image1_high_comments_' . $amp}); ?>
                </td>
            </tr>
    <?php
        endforeach;
        echo '<tr><td colspan="6" style="border-bottom: 3px solid black;"></td></tr>';

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
        ['name' => 'image1_high_file.png', 'icon' => 'pixmaps/icon.png',  'label' => 'Image File'],
        ['name' => 'image1_high_log.log',  'icon' => 'pixmaps/icon2.png', 'label' => 'Log File']
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

<b>Image 2, High Temp - [1skip, 1x1binning, 30rx6400c, Serial register, 10s Exposure] - Aim: Serial Register Defect</b>
<table border="3">
    <tr>
        <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
        <td align="center" style="width: 15%; white-space: nowrap;">Number Column Defects</td>
        <td align="center" style="width: 15%; white-space: nowrap;">Noise [ADU]</td>
        <td align="center" style="width: 30%; white-space: nowrap;">Comments</td>
        <td align="center" style="width: 30%; white-space: nowrap;">Reference Image</td>
    </tr>
    <tr>
        <td colspan="5" style="border-bottom: 3px solid black;"></td>
    </tr>
    <?php
    $count = 0;
    $count_plus = 1;

    // Map short prefixes to nicer display labels
    foreach ($ccds as $amp):
        foreach ($typeMap as $prefix => $label):
    ?>
            <tr>
                <td align="center">
                    <?php echo "ch" . $count . " (ext" . $count_plus . ") - " . $label; ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image2_high_column_defects_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image2_high_noise_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image2_high_comments_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image2_high_reference_' . $amp}); ?>
                </td>
            </tr>
    <?php
        endforeach;
        echo '<tr><td colspan="5" style="border-bottom: 3px solid black;"></td></tr>';
        $count++;
        $count_plus++;
    endforeach;
    ?>

    <!-- file fields -->
    <?php
    $files = [
        ['name' => 'image2_high_file.png', 'icon' => 'pixmaps/icon.png',  'label' => 'Image File'],
        ['name' => 'image2_high_log.log',  'icon' => 'pixmaps/icon2.png', 'label' => 'Log File']
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

<b>Image 3, High Temp - [1000skip, 1x1binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution</b>
<table border="3">
    <tr>
        <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Resolution [ADU]</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Resolution [e-]</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Gain [ADU/e]</td>
        <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [ADU/bin/img]</td>
        <td align="center" style="width: 25%; white-space: nowrap;">Comments</td>
        <td align="center" style="width: 20%; white-space: nowrap;">Reference Image</td>
    </tr>
    <tr>
        <td colspan="7" style="border-bottom: 3px solid black;"></td>
    </tr>

    <?php
    $count = 0;
    $count_plus = 1;
    $typeMap3 = [
        ['label' => 'Surface',    'prefix' => 'sur_image3_'],
        ['label' => 'Underground', 'prefix' => 'udg_image32_']
    ];
    foreach ($ccds as $i => $amp):
        foreach ($typeMap3 as $info): ?>
            <tr>
                <td align="center"><?= "ch{$count} (ext{$count_plus}) - {$info['label']}" ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'high_res_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'high_res_e_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'high_gain_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'high_dark_current_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'high_comments_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'high_reference_' . $amp}) ?></td>
            </tr>


        <?php endforeach; ?>


    <?php
        echo '<tr><td colspan="7" style="border-bottom: 3px solid black;"></td></tr>';
        $count++;
        $count_plus++;
    endforeach;
    ?>
    <?php
    $id = $_SESSION['choosen_module_underground'];

    // Map type → [label, filename prefix]
    $types = [
        'surface'     => ['label' => 'Surface',     'prefix' => 'image3_'],
        'underground' => ['label' => 'Underground', 'prefix' => 'image32_']
    ];

    $files = [
        [
            'label'  => 'Image File',
            'icon'   => 'pixmaps/icon.png',
            'suffix' => [
                'surface'     => 'high_file.png',
                'underground' => 'high_file.png' // if typo, change to 'high_file.png'
            ]
        ],
        [
            'label'  => 'Log File',
            'icon'   => 'pixmaps/icon2.png',
            'suffix' => [
                'surface'     => 'high_log.log',
                'underground' => 'high_log.log'
            ]
        ],
    ];

    foreach ($types as $type => $info): ?>
        <tr>
            <td colspan="9" style="border:none; white-space:nowrap; text-align:left;">
                <?php
                $dir = "/var/www/html/QC_production/uploads/edit_module_{$type}/module_{$type}_{$id}/";
                $url = "/QC_production/uploads/edit_module_{$type}/module_{$type}_{$id}/";

                foreach ($files as $f) {
                    $fname = $info['prefix'] . $f['suffix'][$type];
                    $path  = $dir . $fname;

                    if (file_exists($path)) {
                        echo '<a href="' . htmlspecialchars($url . $fname) . '" target="_blank">'
                            . '<img src="' . htmlspecialchars($f['icon']) . '" alt="' . htmlspecialchars($f['label'] . ' ' . $info['label']) . '" style="height:20px; width:auto;">'
                            . '</a>';
                    }
                    echo '<label>' . htmlspecialchars($f['label'] . ' ' . $info['label']) . '</label>&nbsp;&nbsp;&nbsp;';
                }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>

<br><br>


<b>Image 4, High Temp - [1skip, 1x1binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise, CCD channel mapping</b>
<table border="3">
    <tr>
        <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Pixel Defects</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Column Defects</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Defect Region</td>
        <td align="center" style="width: 5%; white-space: nowrap;"> Noise [ADU]</td>
        <td align="center" style="width: 10%; white-space: nowrap;">CTI-Code</td>
        <td align="center" style="width: 5%; white-space: nowrap;">CTI</td>
        <td align="center" style="width: 10%; white-space: nowrap;">SharpTrack</td>
        <td align="center" style="width: 15%; white-space: nowrap;">Comments</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Reference Image</td>
    </tr>
    <tr>
        <td colspan="10" style="border-bottom: 3px solid black;"></td>
    </tr>
    <?php
    $count = 0;
    $count_plus = 1;

    // Map short prefixes to nicer display labels


    foreach ($ccds as $amp):
        foreach ($typeMap as $prefix => $label):
    ?>
            <tr>
                <td align="center">
                    <?php echo "ch" . $count . " (ext" . $count_plus . ") - " . $label; ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_pixel_defects_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_column_defects_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_region_defect_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_noise_overscan_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_cti_code_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_cti_visual_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_sharpness_tracks_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_comments_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_high_reference_' . $amp}); ?>
                </td>
            </tr>
    <?php
        endforeach;
        echo '<tr><td colspan="10" style="border-bottom: 3px solid black;"></td></tr>';
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
        ['name' => 'image4_high_file.png', 'icon' => 'pixmaps/icon.png',  'label' => 'Image File'],
        ['name' => 'image4_high_log.log',  'icon' => 'pixmaps/icon2.png', 'label' => 'Log File']
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


<b>Image 3, Low Temp - [1000skip, 1x10binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution</b>
<table border="3">
    <tr>
        <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Resolution [ADU]</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Resolution [e-]</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Gain [ADU/e]</td>
        <td align="center" style="width: 15%; white-space: nowrap;">Dark Current [ADU/bin/img]</td>
        <td align="center" style="width: 25%; white-space: nowrap;">Comments</td>
        <td align="center" style="width: 20%; white-space: nowrap;">Reference Image</td>
    </tr>
    <tr>
        <td colspan="7" style="border-bottom: 3px solid black;"></td>
    </tr>

    <?php
    $count = 0;
    $count_plus = 1;
    $typeMap3 = [
        ['label' => 'Surface',    'prefix' => 'sur_image3_'],
        ['label' => 'Underground', 'prefix' => 'udg_image32_']
    ];
    foreach ($ccds as $i => $amp):
        foreach ($typeMap3 as $info): ?>
            <tr>
                <td align="center"><?= "ch{$count} (ext{$count_plus}) - {$info['label']}" ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'low_res_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'low_res_e_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'low_gain_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'low_dark_current_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'low_comments_' . $amp}) ?></td>
                <td align="center"><?= htmlspecialchars(${$info['prefix'] . 'low_reference_' . $amp}) ?></td>
            </tr>

        <?php endforeach; ?>

    <?php
        echo '<tr><td colspan="7" style="border-bottom: 3px solid black;"></td></tr>';
        $count++;
        $count_plus++;
    endforeach;
    ?>
    <?php
    // Map type → [label, filename prefix]
    $types = [
        'surface'     => ['label' => 'Surface',     'prefix' => 'image3_'],
        'underground' => ['label' => 'Underground', 'prefix' => 'image32_']
    ];

    $files = [
        [
            'label'  => 'Image File',
            'icon'   => 'pixmaps/icon.png',
            'suffix' => [
                'surface'     => 'low_file.png',
                'underground' => 'low_file.png'
            ]
        ],
        [
            'label'  => 'Log File',
            'icon'   => 'pixmaps/icon2.png',
            'suffix' => [
                'surface'     => 'low_log.log',
                'underground' => 'low_log.log'
            ]
        ],
    ];

    foreach ($types as $type => $info): ?>
        <tr>
            <td colspan="9" style="border:none; white-space:nowrap; text-align:left;">
                <?php
                $dir = "/var/www/html/QC_production/uploads/edit_module_{$type}/module_{$type}_{$id}/";
                $url = "/QC_production/uploads/edit_module_{$type}/module_{$type}_{$id}/";

                foreach ($files as $f) {
                    $fname = $info['prefix'] . $f['suffix'][$type];
                    $path  = $dir . $fname;

                    if (file_exists($path)) {
                        echo '<a href="' . htmlspecialchars($url . $fname) . '" target="_blank">'
                            . '<img src="' . htmlspecialchars($f['icon']) . '" alt="' . htmlspecialchars($f['label'] . ' ' . $info['label']) . '" style="height:20px; width:auto;">'
                            . '</a>';
                    }
                    echo '<label>' . htmlspecialchars($f['label'] . ' ' . $info['label']) . '</label>&nbsp;&nbsp;&nbsp;';
                }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
<br><br>


<b>Image 4, Low Temp - [1skip, 1x1binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, CTI, Fe55 clusters</b>
<table border="1">
    <tr>
        <td align="center" style="width: 10%; white-space: nowrap;">Amplifier</td>
        <td align="center" style="width: 10%; white-space: nowrap;">Defects?</td>
        <td align="center" style="width: 10%; white-space: nowrap;">CTI? - Visual</td>
        <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 1 [keV] </td>
        <td align="center" style="width: 15%; white-space: nowrap;">Energy Peak 2 [keV] </td>
        <td align="center" style="width: 10%; white-space: nowrap;">Comments</td>
        <td align="center" style="width: 30%; white-space: nowrap;">Reference Image</td>
    </tr>
    <tr>
        <td colspan="7" style="border-bottom: 3px solid black;"></td>
    </tr>

    <?php
    $count = 0;
    $count_plus = 1;
    // Map short prefixes to nicer display labels
    foreach ($ccds as $amp):
        foreach ($typeMap as $prefix => $label):
    ?>
            <tr>
                <td align="center">
                    <?php echo "ch" . $count . " (ext" . $count_plus . ") - " . $label; ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_low_defects_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_low_cti_visual_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_low_peak1_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_low_peak2_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_low_comments_' . $amp}); ?>
                </td>
                <td align="center">
                    <?php echo htmlspecialchars(${$prefix . 'image4_low_reference_' . $amp}); ?>
                </td>
            </tr>
    <?php
        endforeach;
        echo '<tr><td colspan="7" style="border-bottom: 3px solid black;"></td></tr>';

        $count++;
        $count_plus++;
    endforeach;
    ?>

    <?php
    $id = $_SESSION['choosen_module_underground'];

    $types = [
        'surface'     => ['label' => 'Surface'],
        'underground' => ['label' => 'Underground'],
    ];

    $files = [
        [
            'label' => 'Composite',
            'icon'  => 'pixmaps/icon.png',
            'name'  => [
                'surface'     => 'image4_low_file.png',
                'underground' => 'image41_low_file.png',
            ],
        ],
        [
            'label' => 'Energy',
            'icon'  => 'pixmaps/icon2.png',
            'name'  => [
                'surface'     => 'image5_low_file.png',
                'underground' => 'image43_low_file.png',
            ],
        ],
    ];

    foreach ($types as $type => $info): ?>
        <tr>
            <td colspan="9" style="border:none; white-space:nowrap; text-align:left;">
                <?php
                $dir = "/var/www/html/QC_production/uploads/edit_module_{$type}/module_{$type}_{$id}/";
                $url = "/QC_production/uploads/edit_module_{$type}/module_{$type}_{$id}/";

                foreach ($files as $f) {
                    $fname = $f['name'][$type];
                    $path  = $dir . $fname;

                    if (file_exists($path)) {
                        echo '<a href="' . htmlspecialchars($url . $fname) . '" target="_blank">'
                            . '<img src="' . htmlspecialchars($f['icon']) . '" alt="' . htmlspecialchars($f['label'] . ' ' . $info['label']) . '" style="height:20px; width:auto;">'
                            . '</a>';
                    }
                    echo '<label>' . htmlspecialchars($f['label'] . ' ' . $info['label']) . '</label>&nbsp;&nbsp;&nbsp;';
                }
                ?>
            </td>
        </tr>
    <?php endforeach; ?>

</table>

<br><br>