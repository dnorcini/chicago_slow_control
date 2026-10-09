<?php
// export.php
// CSV export of one stage: one row per test, with the old die_qc column names,
// so analysis scripts written for the old tables keep working.
//   web:  export.php?stage=die
//   CLI:  php export.php --stage=die > die.csv
require __DIR__ . '/bootstrap.php';

if (PHP_SAPI === 'cli') {
    $stage = stage_or_404(getopt('', ['stage:'])['stage'] ?? '');
} else {
    require_priv('basic');
    $stage = stage_or_404($_GET['stage'] ?? '');
}
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
    header('Content-Disposition: attachment; filename="ccdqc_' . $stage . '_' . date('Y-m-d') . '.csv"');
}
$out = fopen('php://output', 'w');
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
