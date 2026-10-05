<?php
// config/protocol.php
// The test protocol, written down once: which sections (images), temperatures,
// positions and metrics each stage has, and the old die_qc column each value
// came from. Read by migrate/migrate.php now; by the forms, lists and CSV
// export in item 3. See REFACTOR_DB_REDESIGN.md §6.
//
// Labels, dropdown options, descriptions and upload slots are added in item 3.
//
// Old column name = section 'old' prefix + '_' + temperature 'col' + '_' +
// metric 'col' + '_' + position, leaving out the empty parts. For example,
// 'Image4' + 'Low' + 'Peak1' + 'A' -> Image4_Low_Peak1_A.

// Temperature labels. A third temperature is one more line here.
const TEMPS = [
    'low'  => ['label' => 'Low Temp',  'col' => 'Low'],
    'high' => ['label' => 'High Temp', 'col' => 'High'],
];

// Metrics: one key, one meaning, in every stage.
// type: num | num_err ("5.9 +/- 0.003") | text | yes_no | choice | check
// col:  the old column name piece
const METRICS = [
    // per-amp summary
    'grade'          => ['type' => 'choice', 'col' => 'Grade'],
    'check'          => ['type' => 'check',  'col' => 'Check'],
    'charge'         => ['type' => 'check',  'col' => 'AMP'],
    'defects'        => ['type' => 'yes_no', 'col' => 'Defects'],
    'notes'          => ['type' => 'text',   'col' => 'Notes'],
    // images
    'saturation'     => ['type' => 'yes_no', 'col' => 'Saturation'],
    'tracks'         => ['type' => 'yes_no', 'col' => 'Tracks'],
    'noise'          => ['type' => 'num',    'col' => 'Noise'],
    'res'            => ['type' => 'num',    'col' => 'Res'],
    'resolution'     => ['type' => 'num',    'col' => 'Resolution'],
    'gain'           => ['type' => 'num',    'col' => 'Gain'],
    'dark_current'   => ['type' => 'num',    'col' => 'Dark_Current'],
    'pixel_defects'  => ['type' => 'num',    'col' => 'Pixel_Defects'],
    'column_defects' => ['type' => 'num',    'col' => 'Column_Defects'],
    'region_defect'  => ['type' => 'text',   'col' => 'Region_Defect'],
    'noise_overscan' => ['type' => 'num',    'col' => 'Noise_Overscan'],
    'cti_code'       => ['type' => 'text',   'col' => 'CTI_Code'],
    'cti_visual'     => ['type' => 'yes_no', 'col' => 'CTI_Visual'],
    'sharpness_tracks' => ['type' => 'yes_no', 'col' => 'Sharpness_Tracks'],
    'peak1'          => ['type' => 'num_err', 'col' => 'Peak1'],
    'peak2'          => ['type' => 'num_err', 'col' => 'Peak2'],
    'sigma'          => ['type' => 'num_err', 'col' => 'Sigma'],
    'front'          => ['type' => 'yes_no', 'col' => 'Front'],
    'cti_back_left_mean'       => ['type' => 'num', 'col' => 'CTI_Back_Left_Mean'],
    'cti_back_left_rms'        => ['type' => 'num', 'col' => 'CTI_Back_Left_RMS'],
    'cti_back_left_skewness'   => ['type' => 'num', 'col' => 'CTI_Back_Left_Skewness'],
    'cti_back_left_integral'   => ['type' => 'num', 'col' => 'CTI_Back_Left_Integral'],
    'cti_back_below_mean'      => ['type' => 'num', 'col' => 'CTI_Back_Below_Mean'],
    'cti_back_below_rms'       => ['type' => 'num', 'col' => 'CTI_Back_Below_RMS'],
    'cti_back_below_skewness'  => ['type' => 'num', 'col' => 'CTI_Back_Below_Skewness'],
    'cti_back_below_integral'  => ['type' => 'num', 'col' => 'CTI_Back_Below_Integral'],
    'cti_front_right_fraction' => ['type' => 'num', 'col' => 'CTI_Front_Right_Fraction'],
    'cti_front_above_fraction' => ['type' => 'num', 'col' => 'CTI_Front_Above_Fraction'],
    'ctix_comments'  => ['type' => 'text',   'col' => 'CTIx_Comments'],
    'ctiy_comments'  => ['type' => 'text',   'col' => 'CTIy_Comments'],
    'crosstalk'      => ['type' => 'num',    'col' => 'Crosstalk'],
    'crosstalk_comments' => ['type' => 'text', 'col' => 'Crosstalk_comments'],
    'comments'       => ['type' => 'text',   'col' => 'Comments'],
    'reference'      => ['type' => 'text',   'col' => 'Reference'],
    // test-level values (section 'run'; old names are given per stage)
    'test_date'      => ['type' => 'text'],
    'temp'           => ['type' => 'num'],
    'temp_b'         => ['type' => 'num'],
    'temp_c'         => ['type' => 'num'],
    'vref'           => ['type' => 'num'],
    'eff_resistivity' => ['type' => 'num'],
    'image_dir'      => ['type' => 'text'],
];

// Lists of metrics that several images share
const M_DEFECT_MAP = ['pixel_defects', 'column_defects', 'region_defect', 'noise_overscan',
                      'cti_code', 'cti_visual', 'sharpness_tracks'];
const M_RES_GAIN   = ['res', 'gain', 'dark_current', 'comments', 'reference'];
const M_CTI        = ['cti_back_left_mean', 'cti_back_left_rms', 'cti_back_left_skewness', 'cti_back_left_integral',
                      'cti_back_below_mean', 'cti_back_below_rms', 'cti_back_below_skewness', 'cti_back_below_integral',
                      'cti_front_right_fraction', 'cti_front_above_fraction'];
const M_PEAKS_CTI  = ['peak1', 'peak2', ...M_CTI, 'ctix_comments', 'ctiy_comments', 'comments'];

// Stages (test protocols), in time order. A new testing campaign is a new entry.
//
// Each section:
//   'old'     old column prefix ('' for none)
//   'temps'   either a list of temperatures that all measure the same metrics,
//             or temperature => its own metric lists. No 'temps' = one temperature ('').
//   'per_pos' metrics with one value per position
//   'once'    metrics with one value per image (pos ''), read from the old _A column;
//             or metric => full old column name
//   'pairs'   metrics with one value per ordered pair of positions (AB, AC, ...)
const STAGES = [
    'ccd' => [
        'item'      => 'ccd',
        'old_table' => 'CCD',
        'positions' => ['U1', 'L1', 'U2', 'L2'],
        'types'     => ['defects' => 'text'],              // CCD "Defects" is a free-text description
        'cols'      => ['dark_current' => 'Dark_current'],
        'sections'  => [
            'run'  => ['old' => '', 'once' => ['test_date' => 'Test_date', 'temp' => 'Test_temp', 'vref' => 'Test_vref',
                                               'eff_resistivity' => 'Eff_resistivity', 'defects' => 'Defects']],
            'test' => ['old' => '', 'per_pos' => ['charge', 'noise', 'resolution', 'gain', 'dark_current']],
        ],
    ],
    'die' => [
        'item'      => 'die',
        'old_table' => 'DIE',
        'positions' => ['U1', 'U2', 'L1', 'L2'],
        'sections'  => [
            'run'   => ['old' => '', 'once' => ['temp' => 'Temp', 'image_dir' => 'Image_Dir']],
            'amp'   => ['old' => '', 'per_pos' => ['grade', 'check', 'notes']],
            'trace' => ['old' => 'Trace',  'per_pos' => ['saturation', 'comments', 'reference']],
            'img1'  => ['old' => 'Image1', 'per_pos' => ['tracks', 'noise', 'defects', 'comments', 'reference']],
            'img2'  => ['old' => 'Image2', 'per_pos' => [...M_DEFECT_MAP, 'comments', 'reference']],
            'img3'  => ['old' => 'Image3', 'per_pos' => [...M_DEFECT_MAP, 'comments', 'reference']],
            'img4'  => ['old' => 'Image4', 'per_pos' => M_RES_GAIN],
            'img5'  => ['old' => 'Image5', 'per_pos' => ['column_defects', 'noise', 'comments', 'reference']],
            'img6'  => ['old' => 'Image6', 'per_pos' => ['column_defects', 'region_defect', 'noise_overscan', 'cti_code',
                                                         'cti_visual', 'sharpness_tracks', 'comments', 'reference']],
        ],
    ],
    'surface' => [
        'item'      => 'module',
        'old_table' => 'MODULE_SURFACE',
        'positions' => ['A', 'B', 'C', 'D'],
        'sections'  => [
            'run'   => ['old' => '', 'temps' => [
                            ''     => ['once' => ['image_dir' => 'Image_Dir']],
                            'low'  => ['once' => ['temp' => 'Temp_Low']],
                            'high' => ['once' => ['temp' => 'Temp_High']]]],
            'amp'   => ['old' => '', 'per_pos' => ['grade', 'defects', 'notes']],
            'trace' => ['old' => 'Trace',  'temps' => ['high'], 'per_pos' => ['saturation', 'comments', 'reference']],
            'img1'  => ['old' => 'Image1', 'temps' => ['high'], 'per_pos' => ['tracks', 'noise', 'defects', 'comments', 'reference']],
            'img2'  => ['old' => 'Image2', 'temps' => ['high'], 'per_pos' => ['noise', 'column_defects', 'comments', 'reference']],
            'img3'  => ['old' => 'Image3', 'temps' => ['low', 'high'], 'per_pos' => M_RES_GAIN],
            'img4'  => ['old' => 'Image4', 'temps' => [
                            'low'  => ['per_pos' => ['peak1', 'peak2', 'sigma', 'front', 'defects', 'cti_visual',
                                                     'comments', 'reference']],
                            'high' => ['per_pos' => [...M_DEFECT_MAP, 'comments', 'reference']]]],
        ],
    ],
    'underground' => [
        'item'      => 'module',
        'old_table' => 'MODULE_UNDERGROUND2',
        'positions' => ['A', 'B', 'C', 'D'],
        'sections'  => [
            'run'   => ['old' => '', 'temps' => [
                            'low'  => ['once' => ['temp_b' => 'Temp_Low_B', 'temp_c' => 'Temp_Low_C', 'image_dir' => 'Image_Low_Dir']],
                            'high' => ['once' => ['temp_b' => 'Temp_High_B', 'temp_c' => 'Temp_High_C', 'image_dir' => 'Image_High_Dir']]]],
            'amp'   => ['old' => '', 'per_pos' => ['grade', 'defects', 'notes']],
            'trace' => ['old' => 'Trace',  'temps' => ['high'], 'per_pos' => ['saturation', 'comments', 'reference']],
            'img1'  => ['old' => 'Image1', 'temps' => ['high'], 'per_pos' => ['tracks', 'defects', 'noise', 'comments'], 'once' => ['reference']],
            'img2'  => ['old' => 'Image2', 'temps' => ['high'], 'per_pos' => ['column_defects', 'noise', 'comments'], 'once' => ['reference']],
            'img31' => ['old' => 'Image31', 'temps' => ['low', 'high'], 'per_pos' => M_RES_GAIN],
            'img32' => ['old' => 'Image32', 'temps' => ['low', 'high'], 'per_pos' => M_RES_GAIN],
            'img4'  => ['old' => 'Image4', 'temps' => [
                            'low'  => ['per_pos' => ['defects', 'cti_visual', ...M_PEAKS_CTI], 'once' => ['reference']],
                            'high' => ['per_pos' => [...M_DEFECT_MAP, 'comments'], 'once' => ['reference']]]],
            'img5'  => ['old' => 'Image5', 'temps' => ['low'], 'per_pos' => M_PEAKS_CTI,
                        'once' => ['reference', 'crosstalk_comments' => 'Image5_Low_Crosstalk_comments'],
                        'pairs' => ['crosstalk']],
            'img6'  => ['old' => 'Image6', 'temps' => ['low'], 'per_pos' => ['peak1', 'peak2', 'comments'], 'once' => ['reference']],
            'img7'  => ['old' => 'Image7', 'temps' => ['low'], 'per_pos' => ['peak1', 'peak2', 'comments'], 'once' => ['reference']],
        ],
    ],
];
