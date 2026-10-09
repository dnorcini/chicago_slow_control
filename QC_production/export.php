<?php
// export.php
// CSV export of one stage, in two formats:
//   wide (default): one row per test, with the old die_qc column names. This is
//                   the format compatible with the old schema, so analysis
//                   scripts written for the old tables keep working.
//   long:           one row per measured value (id, name, test_number, section,
//                   temp, pos, metric, value, error, text, and the old column
//                   name). Easiest for pandas and similar tools.
//   web:  export.php?stage=die  /  export.php?stage=die&format=long
//   CLI:  php export.php --stage=die [--format=long] > die.csv
require __DIR__ . '/bootstrap.php';

if (PHP_SAPI === 'cli') {
    $opt = getopt('', ['stage:', 'format:']);
    $stage = stage_or_404($opt['stage'] ?? '');
    $format = $opt['format'] ?? 'wide';
} else {
    require_priv('full');                     // same as the details pages
    $stage = stage_or_404($_GET['stage'] ?? '');
    $format = $_GET['format'] ?? 'wide';
}
$format = $format === 'long' ? 'long' : 'wide';
$P = STAGES[$stage];
$cells = protocol_cells($stage);

$items = [];
foreach (all("SELECT * FROM `{$P['item']}`") as $r)
    $items[$r['id']] = $r;
$tests = all('SELECT * FROM test_info WHERE stage = ? ORDER BY item_id, test_number', [$stage]);
$values = [];                                 // test id => "section|temp|pos|metric" => measurement row
foreach (all('SELECT m.* FROM measurement m JOIN test_info t ON t.id = m.test_id WHERE t.stage = ?', [$stage]) as $m)
    $values[$m['test_id']]["{$m['section']}|{$m['temp']}|{$m['pos']}|{$m['metric']}"] = $m;

// Modules: which die sits at each position (the old Die_X, Amp_X, Channel_X columns)
$wiring = [];
$wiring_cols = [];
if ($P['item'] === 'module') {
    foreach (all('SELECT id, module_id, module_pos, amp, channel FROM die WHERE module_id IS NOT NULL') as $d)
        $wiring[$d['module_id']][$d['module_pos']] = $d;
    foreach (['Die' => 'id', 'Amp' => 'amp', 'Channel' => 'channel'] as $old => $col)
        foreach ($P['positions'] as $p)
            $wiring_cols["{$old}_$p"] = [$p, $col];
}

$header = [...array_values($P['item_cols']), ...array_keys($wiring_cols), 'Test_Number',
           ...array_values($P['test_cols']), ...array_column($cells, 'old')];

if (PHP_SAPI !== 'cli') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="ccdqc_' . $stage . ($format === 'long' ? '_long' : '')
           . '_' . date('Y-m-d') . '.csv"');
}
$out = fopen('php://output', 'w');

if ($format === 'long') {
    fputcsv($out, ['id', 'name', 'test_number', 'section', 'temp', 'pos', 'metric', 'value', 'error', 'text', 'old_column'],
            ',', '"', '');
    foreach ($tests as $t)
        foreach ($cells as $c) {
            $m = $values[$t['id']]["{$c['section']}|{$c['temp']}|{$c['pos']}|{$c['metric']}"] ?? null;
            if ($m)
                fputcsv($out, [$t['item_id'], $items[$t['item_id']]['name'], $t['test_number'], $c['section'], $c['temp'],
                               $c['pos'], $c['metric'], $m['value_num'], $m['value_err'], $m['value_text'], $c['old']],
                        ',', '"', '');
        }
    exit;
}
fputcsv($out, $header, ',', '"', '');
foreach ($tests as $t) {
    $item = $items[$t['item_id']];
    $row = [];
    foreach (array_keys($P['item_cols']) as $col)
        $row[] = $item[$col];
    foreach ($wiring_cols as [$p, $col])
        $row[] = $wiring[$item['id']][$p][$col] ?? '';
    $row[] = $t['test_number'];
    foreach (array_keys($P['test_cols']) as $col)
        $row[] = $t[$col];
    foreach ($cells as $c)
        $row[] = format_value($values[$t['id']]["{$c['section']}|{$c['temp']}|{$c['pos']}|{$c['metric']}"] ?? null, $c['type']);
    fputcsv($out, $row, ',', '"', '');
}
fclose($out);
