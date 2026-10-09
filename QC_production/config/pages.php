<?php
// config/pages.php
// How details.php lays out each stage's page, top to bottom. What is stored
// (sections, metrics, positions) is in config/protocol.php; dropdown lists are
// in config/options.php.
//
// Each block is one form with its own Submit, and one checkbox in the
// "show sections" panel at the top of the page:
//   'key', 'label'  anchor / panel key, and the checkbox text
//   'title'         heading
//   'fields'        rows of single fields, above the grid. A field is
//                     'item.name'        a column of the item (ccd / die / module)
//                     'test.tester'      a column of test_info
//                     'test/low/temp_b'  a value: section/temp/metric (position '')
//                     'updated', 'location'  shown only: last update, current location (history)
//                   or [field, overrides...], e.g. ['test.tester', 'size' => 30]
//   'grid'          'section/temp': a table with one row per position
//   'show'          the grid's columns, if not the section's metrics (e.g. to add derived ones)
//   'tables'        to split the grid into several tables, each with
//                   'title', 'metrics', 'once' (one value per image), 'pairs' (crosstalk), 'files'
//   'fields_after'  rows of single fields, below the grid
//   'files'         upload slots: slot => label. Slots ending in _log take .log files,
//                   seq_* .seq, bcf_* .bcf, all others png/jpg/pdf (lib/uploads.php)
//   'pos_files'     one upload slot per position, as a column: slot prefix => heading
//   'pos_label'     row labels: a sprintf format (%1$s position, %2$d index from 0,
//                   %3$d index from 1), or position => label. Default: the stage's.
//   'divisor'       for 'dc_day' (Dark Current [e-/pix/day])
//   'special'       'history' (history box) or 'layout' (which die sits at each position)

// Single fields (item and test_info columns). label; options (config/options.php);
// size; rows (text area); input (date/time); check (checkbox); num (must be a number)
const FIELDS = [
    'item.name'            => ['label' => 'Name', 'size' => 20],
    'item.status'          => ['label' => 'Status', 'options' => 'status'],
    'item.wafer_id'        => ['label' => 'Wafer ID', 'size' => 20],
    'item.wafer_position'  => ['label' => 'Wafer Position', 'options' => 'wafer_position'],
    'item.activation'      => ['label' => 'UW Activation [days]', 'size' => 10, 'num' => true],
    'item.humidity'        => ['label' => 'Packaging humidity [%]', 'size' => 10, 'num' => true],
    'item.radon'           => ['label' => 'Packaging radon [Bq/m^3]', 'size' => 10, 'num' => true],
    // ccd
    'item.ccd_type'        => ['label' => 'CCD Format', 'options' => 'ccd_type'],
    'item.size'            => ['label' => 'Size', 'options' => 'ccd_size'],
    'item.location'        => ['label' => 'Current location', 'options' => 'location'],
    'item.production_date' => ['label' => 'CCD production date (YYYY/MM)', 'size' => 10],
    'item.cable_np'        => ['label' => 'Cable connections n+ p+', 'check' => true],
    'item.jfet_u1'         => ['label' => 'JFET U1', 'check' => true],
    'item.jfet_l1'         => ['label' => 'JFET L1', 'check' => true],
    'item.jfet_u2'         => ['label' => 'JFET U2', 'check' => true],
    'item.jfet_l2'         => ['label' => 'JFET L2', 'check' => true],
    'item.glue_humid'      => ['label' => 'Gluing: Rel. Humidity [%]', 'size' => 8, 'num' => true],
    'item.glue_temp'       => ['label' => 'Temperature [C]', 'size' => 8, 'num' => true],
    'item.glue_radon'      => ['label' => 'Radon [Bq/m^3]', 'size' => 8, 'num' => true],
    'item.gluing_details'  => ['label' => 'Gluing details (Elog)', 'size' => 120],
    'item.wb_humid'        => ['label' => 'Wirebonding: Rel. Humidity [%]', 'size' => 8, 'num' => true],
    'item.wb_temp'         => ['label' => 'Temperature [C]', 'size' => 8, 'num' => true],
    'item.wb_radon'        => ['label' => 'Radon [Bq/m^3]', 'size' => 8, 'num' => true],
    'item.wb_power'        => ['label' => 'Bond Power [%]', 'size' => 8, 'num' => true],
    'item.wb_time'         => ['label' => 'Bond Time [us]', 'size' => 8, 'num' => true],
    'item.wirebonding_details' => ['label' => 'Wirebonding details (Elog)', 'size' => 120],
    'item.packager'        => ['label' => 'Packaged by', 'options' => 'packager'],
    'item.packaging_date'  => ['label' => 'Packaging Date (MM/YYYY)', 'size' => 10],
    'item.note'            => ['label' => 'Notes (YYYY/MM/DD initials:)', 'rows' => 8],
    // test_info
    'test.tester'            => ['label' => 'Tester', 'size' => 10],
    'test.test_date'         => ['label' => 'Test Date', 'input' => 'date'],
    'test.test_time'         => ['label' => 'Test Time [PT]', 'input' => 'time'],
    'test.chamber'           => ['label' => 'Chamber', 'options' => 'chamber'],
    'test.feedthru_position' => ['label' => 'Feedthru Position', 'options' => 'feedthru'],
    'test.acm'               => ['label' => 'ACM Number', 'options' => 'acm'],
    'test.script'            => ['label' => 'Scripts Directory', 'size' => 50],
    'test.reviewer'          => ['label' => 'Reviewer', 'size' => 20],
    'test.notes'             => ['label' => 'Notes', 'rows' => 4],
];

// Blocks the module stages share
const MODULE_GRADE = ['key' => 'grade', 'label' => 'Grade', 'title' => 'Preliminary Grade Assessment',
                      'grid' => 'amp/', 'pos_label' => '%1$s', 'fields_after' => [['test.reviewer', 'test.notes']]];
const M_CTI_FRONT  = ['cti_front_right_fraction', 'ctix_comments', 'cti_front_above_fraction', 'ctiy_comments'];
const M_CTI_BACK   = ['cti_back_left_mean', 'cti_back_left_rms', 'cti_back_left_skewness', 'cti_back_left_integral',
                      'cti_back_below_mean', 'cti_back_below_rms', 'cti_back_below_skewness', 'cti_back_below_integral'];
const SHOW_RES     = ['res', 'res_e', 'gain', 'dark_current', 'comments', 'reference'];
const SHOW_RES_DAY = ['res', 'res_e', 'gain', 'dark_current', 'dc_day', 'comments', 'reference'];

const PAGES = [
    'ccd' => [
        'item_label' => 'CCD',
        'pos_label'  => ['U1' => 'U1 (CCD C)', 'L1' => 'L1 (CCD A)', 'U2' => 'U2 (CCD D)', 'L2' => 'L2 (CCD B)'],
        'blocks' => [
            ['key' => 'origin', 'label' => 'Origin', 'title' => 'Origin', 'fields' => [
                ['item.name', 'updated'],
                ['item.ccd_type', 'item.size'],
                ['item.status', 'item.location'],
                ['item.wafer_id', ['item.wafer_position', 'label' => 'CCD wafer position', 'options' => null, 'size' => 10],
                 'item.production_date']]],
            ['key' => 'history', 'label' => 'History', 'special' => 'history'],
            ['key' => 'packaging', 'label' => 'Packaging', 'title' => 'Packaging', 'fields' => [
                ['item.cable_np', 'item.jfet_u1', 'item.jfet_l1', 'item.jfet_u2', 'item.jfet_l2'],
                ['item.glue_humid', 'item.glue_temp', 'item.glue_radon'],
                ['item.gluing_details'],
                ['item.wb_humid', 'item.wb_temp', 'item.wb_radon', 'item.wb_power', 'item.wb_time'],
                ['item.wirebonding_details'],
                ['item.packager', 'item.packaging_date']]],
            ['key' => 'testing', 'label' => 'Testing', 'title' => 'Testing', 'grid' => 'amp/',
             'fields' => [
                ['test//temp', 'test//vref', 'test//eff_resistivity'],
                ['test//ccd_defect_notes']],
             'fields_after' => [
                [['test.notes', 'label' => 'Testing details (Elog)', 'rows' => 0, 'size' => 120]],
                [['test.tester', 'label' => 'Tested at', 'options' => 'location', 'size' => null], 'test//test_date']]],
            ['key' => 'notes', 'label' => 'Notes', 'title' => 'Notes', 'fields' => [['item.note']]],
        ],
    ],
    'die' => [
        'item_label' => 'DIE',
        'pos_label'  => '%1$s (ch%3$d)',
        'blocks' => [
            ['key' => 'preliminary', 'label' => 'Preliminary', 'fields' => [
                ['item.name', 'item.status', 'updated'],
                ['item.wafer_id', 'item.wafer_position', 'item.activation'],
                ['item.humidity', 'item.radon', 'location']]],
            ['key' => 'history', 'label' => 'History', 'special' => 'history'],
            ['key' => 'grade', 'label' => 'Grade', 'title' => 'Preliminary Grade Assessment', 'grid' => 'amp/',
             'pos_label' => '%1$s', 'fields_after' => [['test.reviewer', 'test.notes']]],
            ['key' => 'testing', 'label' => 'Testing', 'title' => 'Testing', 'fields' => [
                ['test.tester', 'test.test_date', 'test.test_time', 'test.chamber'],
                ['test//temp', 'test.feedthru_position', 'test.acm'],
                ['test.script'],
                ['test//image_dir']]],
            ['key' => 'trace', 'label' => 'Trace', 'title' => 'Trace', 'grid' => 'trace/', 'pos_label' => '%1$s (ch%2$d)',
             'files' => ['trace_file' => 'Image File', 'trace_log' => 'Log File']],
            ['key' => 'image1', 'label' => 'Image 1', 'grid' => 'img1/',
             'title' => 'Image 1 - [1skip, 10x10binning, 160rx640c, Active region, 60s Exposure] - Aim: To see tracks',
             'files' => ['image1_file' => 'Image File', 'image1_log' => 'Log File']],
            ['key' => 'image2', 'label' => 'Image 2', 'grid' => 'img2/',
             'title' => 'Image 2 - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise',
             'files' => ['image2_file' => 'Image File', 'image2_log' => 'Log File']],
            ['key' => 'image3', 'label' => 'Image 3', 'grid' => 'img3/',
             'title' => 'Image 3 - [1skip, 1x1binning, 800rx3400c, Active region, 300s Exposure, high VSub and V Clk] - Aim: Defect Map, Sharpness of tracks, CTI, Noise',
             'files' => ['image3_file' => 'Image File', 'image3_log' => 'Log File']],
            ['key' => 'image4', 'label' => 'Image 4', 'grid' => 'img4/',
             'title' => 'Image 4 - [1000skip, 1x1binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution',
             'files' => ['image4_file' => 'Image File', 'image4_log' => 'Log File']],
            ['key' => 'image5', 'label' => 'Image 5', 'grid' => 'img5/',
             'title' => 'Image 5 - [1skip, 1x1binning, 30rx3400c, Serial register, 10s Exposure] - Aim: Serial Register Defect',
             'files' => ['image5_file' => 'Image File', 'image5_log' => 'Log File']],
            ['key' => 'image6', 'label' => 'Image 6', 'grid' => 'img6/',
             'title' => 'Image 6 - [1skip, 1x1binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise',
             'files' => ['image6_file' => 'Image File', 'image6_log' => 'Log File']],
        ],
    ],
    'surface' => [
        'item_label' => 'MODULE',
        'pos_label'  => 'ch%2$d (ext%3$d)',
        'blocks' => [
            ['key' => 'preliminary', 'label' => 'Preliminary', 'fields' => [
                ['item.name', 'item.status', 'updated'],
                ['item.activation'],
                ['item.humidity', 'item.radon', 'location']]],
            ['key' => 'history', 'label' => 'History', 'special' => 'history'],
            ['key' => 'layout', 'label' => 'Layout', 'title' => 'Module layout', 'special' => 'layout'],
            MODULE_GRADE,
            ['key' => 'testing', 'label' => 'Testing', 'title' => 'Testing', 'fields' => [
                ['test.tester', 'test.test_date', 'test.test_time', 'test.chamber'],
                ['test/low/temp', 'test/high/temp', 'test.feedthru_position', 'test.acm'],
                ['test.script'],
                ['test//image_dir']]],
            ['key' => 'trace_high', 'label' => 'Trace', 'title' => 'Trace, High Temp', 'grid' => 'trace/high',
             'files' => ['trace_high_file' => 'Image File', 'trace_high_log' => 'Log File']],
            ['key' => 'image1high', 'label' => 'Image 1 High', 'grid' => 'img1/high',
             'title' => 'Image 1, High Temp - [1skip, 20x20binning, 80rx320c, Active region, 3s Exposure] - Aim: To see tracks',
             'files' => ['image1_high_file' => 'Image File', 'image1_high_log' => 'Log File']],
            ['key' => 'image2high', 'label' => 'Image 2 High', 'grid' => 'img2/high',
             'title' => 'Image 2, High Temp - [1skip, 1x1binning, 30rx6400c, Serial register, 10s Exposure] - Aim: Serial Register Defect',
             'files' => ['image2_high_file' => 'Image File', 'image2_high_log' => 'Log File']],
            ['key' => 'image3high', 'label' => 'Image 3 High', 'grid' => 'img3/high', 'show' => SHOW_RES,
             'title' => 'Image 3, High Temp - [1000skip, 1x1binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution',
             'files' => ['image3_high_file' => 'Image File', 'image3_high_log' => 'Log File']],
            ['key' => 'image4high', 'label' => 'Image 4 High', 'grid' => 'img4/high',
             'title' => 'Image 4, High Temp - [1skip, 1x1binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise, CCD channel mapping',
             'files' => ['image4_high_file' => 'Image File', 'image4_high_log' => 'Log File']],
            ['key' => 'image3low', 'label' => 'Image 3 Low', 'grid' => 'img3/low', 'show' => SHOW_RES,
             'title' => 'Image 3, Low Temp - [1000skip, 1x10binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution',
             'files' => ['image3_low_file' => 'Image File', 'image3_low_log' => 'Log File']],
            ['key' => 'image4low', 'label' => 'Image 4 Low', 'grid' => 'img4/low',
             'title' => 'Image 4, Low Temp - [1skip, 1x1binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise, Fe55 clusters',
             'files' => ['image4_low_file' => 'Image File', 'image5_low_file' => 'Image 5 File', 'image4_low_log' => 'Log File']],
        ],
    ],
    'underground' => [
        'item_label' => 'MODULE',
        'pos_label'  => 'ch%2$d (ext%3$d)',
        'blocks' => [
            ['key' => 'preliminary', 'label' => 'Preliminary', 'fields' => [
                ['item.name', 'item.status', 'updated'],
                ['location']]],
            ['key' => 'layout', 'label' => 'Layout', 'title' => 'Module layout', 'special' => 'layout'],
            ['key' => 'history', 'label' => 'History', 'special' => 'history'],
            MODULE_GRADE,
            ['key' => 'testing', 'label' => 'Testing', 'title' => 'Testing', 'fields' => [
                [['test.tester', 'size' => 30], 'test.test_date', ['test.test_time', 'label' => 'Test Time [CET]'], 'test.chamber'],
                ['test/low/temp_b', 'test/high/temp_b', 'test/low/temp_c', 'test/high/temp_c'],
                ['test.feedthru_position', 'test.acm'],
                [['test.script', 'size' => 80]],
                [['test/high/image_dir', 'size' => 60], ['test/low/image_dir', 'size' => 60]]],
             'files' => ['seq_high_file' => 'sequencer file', 'bcf_high_file' => 'bcf file']],
            ['key' => 'trace', 'label' => 'Trace', 'title' => 'Trace and PSD, High Temp', 'grid' => 'trace/high',
             'pos_files' => ['psd_high_file' => 'PSD'], 'files' => ['trace_high_file' => 'Image File']],
            ['key' => 'image1high', 'label' => 'Image1', 'grid' => 'img1/high',
             'title' => 'Image 1, High Temp - [1 skip, 20x20 binning, 80rx320c, Active Region, 3s Exposure] - Aim: To see tracks',
             'files' => ['image1_high_file' => 'Image File']],
            ['key' => 'image2high', 'label' => 'Image2', 'grid' => 'img2/high',
             'title' => 'Image 2, High Temp - [1 skip, 1x1 binning, 30rx6400c, Serial Register, 10s Exposure] - Aim: Serial Register Defects',
             'files' => ['image2_high_file' => 'Image File', 'image2_high_log' => 'Log File']],
            ['key' => 'image31high', 'label' => 'Image3-1 High', 'grid' => 'img31/high', 'show' => SHOW_RES_DAY, 'divisor' => 341,
             'title' => 'Image 3-1, High Temp - [500 skip, 1x1 binning, 30rx640c, Serial Register, 0s Exposure] - Aim: Single Electron Resolution',
             'files' => ['image31_high_file' => 'Image File']],
            ['key' => 'image32high', 'label' => 'Image3-2 High', 'grid' => 'img32/high', 'show' => SHOW_RES_DAY, 'divisor' => 654,
             'title' => 'Image 3-2, High Temp - [1000 skip, 1x1 binning, 30rx640c, Serial register, 0s Exposure] - Aim: Single Electron Resolution',
             'files' => ['image32_high_file' => 'Image File']],
            ['key' => 'image4high', 'label' => 'Image4 High', 'grid' => 'img4/high',
             'title' => 'Image 4, High Temp - [1 skip, 1x1 binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise, CCD channel mapping',
             'files' => ['image4_high_file' => 'Image File', 'image4_high_log' => 'Log File']],
            ['key' => 'image31low', 'label' => 'Image3-1 Low', 'grid' => 'img31/low', 'show' => SHOW_RES_DAY, 'divisor' => 371 * 10,
             'title' => 'Image 3-1, Low Temp - [500 skip, 1x10 binning, 30rx640c, Serial Register, 0s Exposure] - Aim: Single Electron Resolution',
             'files' => ['image31_low_file' => 'Image File']],
            ['key' => 'image32low', 'label' => 'Image3-2 Low', 'grid' => 'img32/low', 'show' => SHOW_RES_DAY, 'divisor' => 684 * 10,
             'title' => 'Image 3-2, Low Temp - [1000 skip, 1x10 binning, 30rx640c, Serial Register, 0s Exposure] - Aim: Single Electron Resolution',
             'files' => ['image32_low_file' => 'Image File']],
            ['key' => 'image4low', 'label' => 'Image4 Low', 'grid' => 'img4/low',
             'title' => 'Image 4, Low Temp - [1 skip, 1x1 binning, 1600rx6400c, Active region, 500s Exposure] - Aim: Defect Map, Sharpness of tracks, CTI, Noise, CCD channel mapping',
             'tables' => [
                ['metrics' => ['defects', 'cti_visual', 'peak1', 'peak2', 'comments'], 'once' => ['reference'],
                 'files' => ['image41_low_file' => 'Composite Images', 'image42_low_file' => 'Energy_Peaks',
                             'image42_low_log' => 'Defect Log']],
                ['title' => 'Image 4, Low Temp - Frontside CTI', 'metrics' => M_CTI_FRONT,
                 'files' => ['image43_low_file' => 'Frontside Images']],
                ['title' => 'Image 4, Low Temp - Backside CTI', 'metrics' => M_CTI_BACK,
                 'files' => ['image44_low_file' => 'Backside Images', 'image4_low_log' => 'CTI Log']]]],
            ['key' => 'image5low', 'label' => 'Image5', 'grid' => 'img5/low',
             'title' => 'Image 5, Low Temp - [500 skip, 1x1 binning, 320rx640c, Active region, 500s Exposure] - Aim: High Resolution Fe55 Cluster Analysis, CTI, noise',
             'tables' => [
                ['metrics' => ['peak1', 'peak2', 'comments'], 'once' => ['reference'],
                 'files' => ['image52_low_file' => 'Energy_Peaks', 'image5_low_log' => 'Log File']],
                ['title' => 'Image 5, Low Temp - Frontside CTI', 'metrics' => M_CTI_FRONT,
                 'files' => ['image53_low_file' => 'Frontside Images']],
                ['title' => 'Image 5, Low Temp - Backside CTI', 'metrics' => M_CTI_BACK,
                 'files' => ['image54_low_file' => 'Backside Images']],
                ['title' => 'Image 5, Low Temp - Crosstalk', 'pairs' => ['crosstalk'], 'once' => ['crosstalk_comments'],
                 'files' => ['image55_low_file' => 'Crosstalk Images', 'image55_low_log' => 'Crosstalk Log']]]],
            ['key' => 'image6low', 'label' => 'Image6', 'grid' => 'img6/low',
             'title' => 'Image 6, Low Temp - [500 skip, 10x1 binning, 320rx640c, Active region, 500s Exposure] - Aim: High Resolution Fe55 Cluster Analysis, CTI, noise',
             'files' => ['image62_low_file' => 'Energy_Peaks', 'image6_low_log' => 'Log File',
                         'image63_low_file' => 'Frontside Images', 'image64_low_file' => 'Backside Images']],
            ['key' => 'image7low', 'label' => 'Image7', 'grid' => 'img7/low',
             'title' => 'Image 7, Low Temp - [500 skip, 1x10 binning, 320rx640c, Active region, 500s Exposure] - Aim: High Resolution Fe55 Cluster Analysis, CTI, Noise',
             'files' => ['image72_low_file' => 'Energy_Peaks', 'image7_low_log' => 'Log File',
                         'image73_low_file' => 'Frontside Images', 'image74_low_file' => 'Backside Images']],
        ],
    ],
];
