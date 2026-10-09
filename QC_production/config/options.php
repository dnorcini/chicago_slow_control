<?php
// config/options.php
// Dropdown lists. Add an option by adding it to its list. The first entry ''
// is the blank choice; choosing it stores nothing.
// A value already in the DB that isn't in its list is still shown (and kept).

const OPTIONS = [
    // ccd
    'ccd_status'     => ['', 'Unchecked', 'Science-grade', 'Operation-grade', 'Toy-grade', 'Failed'],
    'ccd_type'       => ['', 'DES D42', 'LBNL Skipper', 'DAMIC Skipper', 'DAMIC Module', '47/6 Skipper+DES'],
    'ccd_size'       => ['', '4kx2k', '1kx4k', '1kx6k', '6kx1k', '6kx1.5k', '6kx4k', '6kx6k'],
    'packager'       => ['', 'UW', 'LSM', 'LBNL'],
    'location'       => ['', 'UChicago', 'UW', 'Hopkins', 'LPNHE', 'LSM', 'PNNL', 'UZH', 'IFCA', 'Other'],
    // die, module
    'status'         => ['', 'Not Tested', 'Tested', 'Failed'],
    'wafer_position' => ['', 'A', 'B', 'C', 'D'],
    'amp'            => ['', 'L1', 'L2', 'U1', 'U2'],
    'channel'        => ['', 'ch0', 'ch1', 'ch2', 'ch3'],
    // tests
    'grade'          => ['', 'Failed', 'Operational', 'Engineering', 'Science'],
    'yes_no'         => ['', 'Yes', 'No'],
    'chamber'        => ['', 'JH1', 'JH2'],
    'acm'            => ['', '101', '102', '106', '108', '109', '110'],
    'feedthru'       => ['', '1', '2', '3', '4'],
];
