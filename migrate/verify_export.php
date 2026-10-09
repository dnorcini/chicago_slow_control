<?php
// migrate/verify_export.php
// Second migration check: export each stage from ccdqc
// with QC_production/export.php and compare the CSV with the old die_qc table,
// column by column and id by id. Read-only.
//
//   docker exec -e DB_HOST=db -e TARGET_DB_HOST=targetDB ccdqc-target php /var/www/html/migrate/verify_export.php
//
// die_qc is read on DB_HOST, ccdqc on TARGET_DB_HOST (default: the same host).
// Differences the redesign makes on purpose are counted, not listed:
//   - CCD test values 0.00 and peak "0.0" placeholders export as blank
//   - whitespace-only text exports as blank
//   - an unticked check that was NULL exports as 0
//   - module item columns (Status) come from the one module row, so the
//     underground export shows them where the old underground table had NULL
//   - the same number written differently ("6.518E-05" vs "6.518E-5", "5.590" vs "5.59");
//     the numbers must be exactly equal
// Anything else is listed and counted as a difference.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../QC_production/lib/protocol.php';

$cfg_file = __DIR__ . '/../QC_production/db_config.php';
$cfg = is_file($cfg_file) ? require $cfg_file : [];
$src_host = getenv('DB_HOST') ?: ($cfg['host'] ?? 'localhost');
$dst_host = getenv('TARGET_DB_HOST') ?: $src_host;
$old = new PDO("mysql:host=$src_host;dbname=die_qc", getenv('DB_USER') ?: ($cfg['user'] ?? ''),
               getenv('DB_PASSWORD') ?: ($cfg['password'] ?? ''),
               [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$old->exec('SET NAMES binary');               // raw bytes = the real UTF-8 text
echo "Old tables: die_qc on $src_host. Export: ccdqc on $dst_host\n";

// Runs export.php for one stage against ccdqc; returns [header, rows keyed by id]
function export_csv(string $stage, string $host): array {
    $env = getenv();
    $env['DB_HOST'] = $host;
    $env['DB_NAME'] = 'ccdqc';
    $p = proc_open([PHP_BINARY, __DIR__ . '/../QC_production/export.php', "--stage=$stage"],
                   [1 => ['pipe', 'w']], $pipes, null, $env);
    $header = fgetcsv($pipes[1], null, ',', '"', '');
    $rows = [];
    while (($r = fgetcsv($pipes[1], null, ',', '"', '')) !== false) {
        $r = array_combine($header, $r);
        $rows[$r['id'] ?? $r['ID']][] = $r;
    }
    if (proc_close($p) !== 0) { fwrite(STDERR, "export.php --stage=$stage failed\n"); exit(1); }
    return [$header, $rows];
}

const NUM = '[-+]?(\d+\.?\d*|\.\d+)([eE][-+]?\d+)?';
function same_number(string $a, string $b): bool {
    return (float)trim($a) === (float)trim($b);
}
// '' when equal; otherwise why they differ
function compare(?string $old, string $new, string $type): string {
    $old = $old ?? '';
    if ($old === $new) return '';
    if (trim($old) === '' && $new === '') return 'blank';
    if (trim($old) === '' && $new === '0' && $type === 'check') return 'unticked';
    if (is_numeric($old) && $new === '' && (float)$old == 0) return 'zero_blank';
    if (is_numeric($old) && is_numeric($new)) return same_number($old, $new) ? 'number_format' : 'DIFF';
    $pm = '/^\s*(' . NUM . ')\s*\+\/-\s*(' . NUM . ')\s*$/';
    if (preg_match($pm, $old, $a) && preg_match($pm, $new, $b))
        return same_number($a[1], $b[1]) && same_number($a[4], $b[4]) ? 'number_format' : 'DIFF';
    return 'DIFF';
}

$total_diffs = 0;
foreach (STAGES as $stage => $S) {
    $table = $S['old_table'];
    $key = $S['item_cols']['id'];
    [$header, $rows] = export_csv($stage, $dst_host);
    $old_rows = [];
    foreach ($old->query("SELECT * FROM `$table`") as $r)
        $old_rows[$r[$key]] = $r;
    $old_cols = array_keys(reset($old_rows));

    echo "\n== $stage ($table)\n";
    printf("  rows: old %d, export %d\n", count($old_rows), array_sum(array_map('count', $rows)));
    foreach ($rows as $id => $list)
        if (count($list) > 1) { echo "  DIFF id $id has " . count($list) . " tests of this stage\n"; $total_diffs++; }
    foreach (array_diff(array_keys($old_rows), array_keys($rows)) as $id) { echo "  DIFF old id $id missing\n"; $total_diffs++; }
    foreach (array_diff(array_keys($rows), array_keys($old_rows)) as $id) { echo "  DIFF export id $id not in old\n"; $total_diffs++; }

    $only_new = array_diff($header, $old_cols);
    $only_old = array_diff($old_cols, $header);
    echo '  columns only in export: ' . (implode(' ', $only_new) ?: '-') . "\n";
    printf("  columns only in old table: %d (not carried over)\n", count($only_old));
    // Those must be empty, apart from bookkeeping and the known surface strays
    $with_data = [];
    foreach ($only_old as $col)
        foreach ($old_rows as $r)
            if (trim((string)$r[$col]) !== '' && !(is_numeric($r[$col]) && (float)$r[$col] == 0)) { $with_data[$col] = true; break; }
    unset($with_data['Last_Update'], $with_data['Last_update']);
    echo '  ... holding data (Last_Update not counted): ' . (implode(' ', array_keys($with_data)) ?: '-') . "\n";

    $types = array_column(protocol_cells($stage), 'type', 'old');
    $why = [];
    $n = 0;
    foreach ($rows as $id => [$r]) {
        if (!isset($old_rows[$id])) continue;
        foreach (array_intersect($header, $old_cols) as $col) {
            $n++;
            $w = compare($old_rows[$id][$col], $r[$col], $types[$col] ?? '');
            if ($w === 'DIFF' && $S['item'] === 'module' && in_array($col, $S['item_cols'], true)
                && trim((string)$old_rows[$id][$col]) === '')
                $w = 'module_row';
            if ($w === '') continue;
            $why[$w] = ($why[$w] ?? 0) + 1;
            if ($w === 'DIFF' && $why[$w] <= 20)
                printf("  DIFF id %s %s: old %s, export %s\n", $id, $col, json_encode($old_rows[$id][$col]), json_encode($r[$col]));
        }
    }
    printf("  cells compared: %d; equal: %d; differing on purpose: %s\n", $n, $n - array_sum($why),
           json_encode(array_diff_key($why, ['DIFF' => 1])));
    $total_diffs += $why['DIFF'] ?? 0;
}
echo "\n" . ($total_diffs ? "$total_diffs differences\n" : "No differences.\n");
exit($total_diffs ? 1 : 0);
