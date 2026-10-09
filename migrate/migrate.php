<?php
// migrate/migrate.php
// Item 2: copy die_qc into the new ccdqc schema (REFACTOR_DB_REDESIGN.md).
// die_qc is only read. ccdqc's tables are dropped and recreated from schema.sql,
// so the script can be run again at any time.
//
// Run it in the targetOS container (CLI only):
//   docker exec -e DB_HOST=db -e TARGET_DB_HOST=targetDB ccdqc-target php /var/www/html/migrate/migrate.php
// Options:
//   --replace   allowed to overwrite a ccdqc that already holds tests
//   --list      also print every old column that is not carried over
//
// die_qc is read on DB_HOST, ccdqc is written on TARGET_DB_HOST (default: the
// same host, as in production). Credentials: DB_USER / DB_PASSWORD, or
// QC_production/db_config.php.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../QC_production/lib/protocol.php';

// Old columns that are not carried over but hold a value (REFACTOR_ISSUES.md §2.3).
// Any other unread column with data stops the migration.
const KNOWN_STRAY = [
    'MODULE_SURFACE' => ['Image1_Low_Tracks_A', 'Image1_Low_Noise_C', 'Image3_Low_Sharpness_Tracks_A',
                         'Image3_Low_CTI_Visual_B', 'Image5_Low_Reference_A', 'Image5_Low_Reference_D',
                         'Image5_Low_Column_Defects_B', 'Image5_Low_Noise_B'],
];
// CCD test values where 0.00 means "not entered" (§7 rule 3)
const CCD_ZERO_IS_EMPTY = ['noise', 'resolution', 'gain', 'dark_current', 'temp', 'vref', 'eff_resistivity'];

// Old item and test columns, copied as they are (§4, §5): 'item_cols' and
// 'test_cols' of each stage in config/protocol.php.
const CCD_COLS = STAGES['ccd']['item_cols'];
const DIE_COLS = STAGES['die']['item_cols'];

function fail(string $msg): never { fwrite(STDERR, "STOP: $msg\n"); exit(1); }

function connect(string $host, ?string $db): PDO {
    $cfg_file = __DIR__ . '/../QC_production/db_config.php';
    $cfg = is_file($cfg_file) ? require $cfg_file : [];
    $pdo = new PDO("mysql:host=$host" . ($db ? ";dbname=$db" : '') . ";charset=utf8mb4",
                   getenv('DB_USER') ?: ($cfg['user'] ?? ''), getenv('DB_PASSWORD') ?: ($cfg['password'] ?? ''),
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}

// All rows of an old table, keyed by id, as raw bytes. The latin1 columns
// hold UTF-8 bytes (§7 rule 6), so reading the bytes unconverted and checking
// they are valid UTF-8 gives the real text.
function old_rows(PDO $src, string $table, string $key): array {
    $out = [];
    foreach ($src->query("SELECT * FROM `$table` ORDER BY `$key`") as $r) {
        foreach ($r as $col => $v)
            if ($v !== null && !mb_check_encoding($v, 'UTF-8'))
                fail("$table.$col ($key {$r[$key]}) is not valid UTF-8");
        $out[$r[$key]] = $r;
    }
    return $out;
}

function is_blank(?string $v): bool { return $v === null || trim($v) === ''; }

// One old cell -> [value_num, value_err, value_text], or null for "no row" (§7).
// $why collects what happened, for the report.
function convert(?string $raw, string $type, bool $zero_is_empty, array &$why, string $where): ?array {
    if (is_blank($raw)) return null;                                   // rule 1
    $t = trim($raw);
    switch ($type) {
        case 'num':
            if (!is_numeric($t)) { $why['text_in_number'][] = "$where = \"$raw\""; return [null, null, $raw]; }
            if ($zero_is_empty && (float)$t == 0) { @$why['ccd_zero']++; return null; }   // rule 3
            return [$t, null, null];
        case 'num_err':                                                // rule 2
            $num = '[-+]?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?';
            if (preg_match("~^($num)\s*\+/-\s*($num)$~", $t, $m)) return [$m[1], $m[2], null];
            if (is_numeric($t)) {
                if ((float)$t == 0) { @$why['peak_zero']++; return null; }
                return [$t, null, null];
            }
            $why['text_in_number'][] = "$where = \"$raw\"";             // rule 5
            return [null, null, $raw];
        case 'check':                                                  // rule 4
            if ($t === '1') return ['1', null, null];
            if ($t === '0') return null;
            $why['odd_check'][] = "$where = \"$raw\"";
            return [null, null, $raw];
        default:                                                       // text, yes_no, choice
            return [null, null, $raw];
    }
}

// ---------------------------------------------------------------------------
$opt = getopt('', ['replace', 'list']);
$cfg_file = __DIR__ . '/../QC_production/db_config.php';
$src_host = getenv('DB_HOST') ?: ((is_file($cfg_file) ? require $cfg_file : [])['host'] ?? 'localhost');
$dst_host = getenv('TARGET_DB_HOST') ?: $src_host;
$src = connect($src_host, 'die_qc');
$src->exec("SET NAMES binary");
$dst = connect($dst_host, null);
echo "Reading die_qc on $src_host, writing ccdqc on $dst_host\n";

// 1. Read everything from die_qc
$old = [
    'ccd'         => old_rows($src, 'CCD', 'ID'),
    'die'         => old_rows($src, 'DIE', 'id'),
    'surface'     => old_rows($src, 'MODULE_SURFACE', 'id'),
    'underground' => old_rows($src, 'MODULE_UNDERGROUND2', 'id'),
];
$history   = old_rows($src, 'history', 'entry');
$users     = old_rows($src, 'users', 'user_name');
$user_privs = old_rows($src, 'user_privileges', 'u_p_indx');

// 2. Build the new rows in memory, checking as we go
$why = [];

// module: the surface row, or the underground row for underground-only ids
$modules = [];
foreach ($old['underground'] + $old['surface'] as $id => $_) {
    $s = $old['surface'][$id] ?? null;
    $u = $old['underground'][$id] ?? null;
    if ($s && $u && $s['Name'] !== $u['Name']) fail("module $id: surface name {$s['Name']} != underground name {$u['Name']}");
    $base = $s ?? $u;
    $modules[$id] = ['id' => $id, 'name' => $base['Name'], 'status' => $base['Status'],
                     'activation' => $s['Activation'] ?? null, 'humidity' => $s['Humidity'] ?? null,
                     'radon' => $s['Radon'] ?? null,
                     'updated_at' => max($s['Last_Update'] ?? 0, $u['Last_Update'] ?? 0)];
}
ksort($modules);

// die -> module wiring from both module tables; they must agree (§4)
$wiring = [];
foreach (['surface', 'underground'] as $stage)
    foreach ($old[$stage] as $id => $r)
        foreach (['A', 'B', 'C', 'D'] as $p) {
            $die = $r["Die_$p"];
            if ($die === null || (int)$die === 0) continue;
            $w = ['module_id' => $id, 'module_pos' => $p, 'amp' => $r["Amp_$p"], 'channel' => $r["Channel_$p"]];
            if (isset($wiring[$die]) && $wiring[$die] != $w)
                fail("die $die: module wiring differs: " . json_encode($wiring[$die]) . " vs $stage " . json_encode($w));
            if (!isset($old['die'][$die])) fail("module $id position $p: die $die is not in DIE");
            $wiring[$die] = $w;
        }

$ccds = [];
foreach ($old['ccd'] as $r) {
    $row = [];
    foreach (CCD_COLS as $new => $o) $row[$new] = $r[$o];
    $ccds[] = $row + ['updated_at' => $r['Last_update']];
}
$dies = [];
foreach ($old['die'] as $id => $r) {
    $row = [];
    foreach (DIE_COLS as $new => $o) $row[$new] = $r[$o];
    $w = $wiring[$id] ?? ['module_id' => null, 'module_pos' => null, 'amp' => null, 'channel' => null];
    $dies[] = $row + $w + ['updated_at' => $r['Last_Update']];
}

// tests and measurements, in time order: ccd, die, surface, underground
$tests = [];
$read = [];             // old table => [column => true] read by the migration
foreach (STAGES as $stage => $S) {
    $table = $S['old_table'];
    $cells = protocol_cells($stage);
    $test_cols = $S['test_cols'];
    foreach ($cells as $c) $read[$table][$c['old']] = true;
    foreach ($test_cols as $o) $read[$table][$o] = true;

    foreach ($old[$stage] as $id => $r) {
        $t = ['item_type' => $S['item'], 'item_id' => $id, 'stage' => $stage,
              'test_number' => ($stage === 'underground' && isset($old['surface'][$id])) ? 2 : 1];
        foreach (array_keys(TEST_COLS) as $new) $t[$new] = null;
        foreach ($test_cols as $new => $o) $t[$new] = $r[$o];
        $t['updated_at'] = $r[$stage === 'ccd' ? 'Last_update' : 'Last_Update'];

        $t['values'] = [];
        foreach ($cells as $c) {
            $zero_empty = $stage === 'ccd' && in_array($c['metric'], CCD_ZERO_IS_EMPTY, true);
            $v = convert($r[$c['old']], $c['type'], $zero_empty, $why, "$table id $id ({$r['Name']}) {$c['old']}");
            if ($v !== null) $t['values'][] = [$c['section'], $c['temp'], $c['pos'], $c['metric'], ...$v];
        }
        $tests[] = $t;
    }
}
// item and bookkeeping columns read from each old table
foreach (array_merge(CCD_COLS, ['Last_update']) as $o) $read['CCD'][$o] = true;
foreach (array_merge(DIE_COLS, ['Last_Update']) as $o) $read['DIE'][$o] = true;
foreach (['MODULE_SURFACE', 'MODULE_UNDERGROUND2'] as $table) {
    foreach (['id', 'Name', 'Status', 'Last_Update'] as $o) $read[$table][$o] = true;
    foreach (['A', 'B', 'C', 'D'] as $p) foreach (['Die', 'Amp', 'Channel'] as $o) $read[$table]["{$o}_$p"] = true;
}
foreach (['Activation', 'Humidity', 'Radon'] as $o) $read['MODULE_SURFACE'][$o] = true;

// 3. Columns not carried over must be empty (NULL, '' or 0), apart from the known strays (§8)
$tables = ['ccd' => 'CCD', 'die' => 'DIE', 'surface' => 'MODULE_SURFACE', 'underground' => 'MODULE_UNDERGROUND2'];
echo "\nOld columns not carried over:\n";
foreach ($tables as $stage => $table) {
    $all = array_keys(reset($old[$stage]));
    $unread = array_values(array_diff($all, array_keys($read[$table])));
    $with_data = [];
    foreach ($unread as $col)
        foreach ($old[$stage] as $r)
            if (!is_blank($r[$col]) && !(is_numeric(trim($r[$col])) && (float)$r[$col] == 0)) $with_data[$col] = true;
    $with_data = array_keys($with_data);
    printf("  %-20s %3d of %3d columns; with data: %d\n", $table, count($unread), count($all), count($with_data));
    if (isset($opt['list'])) echo '    ' . implode(' ', $unread) . "\n";
    $known = KNOWN_STRAY[$table] ?? [];
    sort($with_data); sort($known);
    if ($with_data !== $known)
        fail("$table: unread columns with data " . json_encode($with_data) . ", expected " . json_encode($known));
}

// 4. Write ccdqc
$has_tests = $dst->query("SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'ccdqc' AND table_name = 'test_info'")->fetchColumn();
if ($has_tests && $dst->query("SELECT COUNT(*) FROM ccdqc.test_info")->fetchColumn() > 0 && !isset($opt['replace']))
    fail("ccdqc already holds tests. Run again with --replace to overwrite them.");

$sql = preg_replace('/--[^\n]*/', '', file_get_contents(__DIR__ . '/schema.sql'));
foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) $dst->exec($stmt);

function insert(PDO $db, string $table, array $row, array $unixtime = []): string {
    $cols = array_keys($row);
    $marks = array_map(fn($c) => in_array($c, $unixtime, true) ? 'FROM_UNIXTIME(?)' : '?', $cols);
    $db->prepare("INSERT INTO `$table` (`" . implode('`, `', $cols) . "`) VALUES (" . implode(', ', $marks) . ")")
       ->execute(array_values($row));
    return $db->lastInsertId();
}

$dst->beginTransaction();
foreach ($ccds as $row)    insert($dst, 'ccd', $row, ['updated_at']);
foreach ($modules as $row) insert($dst, 'module', $row, ['updated_at']);
foreach ($dies as $row)    insert($dst, 'die', $row, ['updated_at']);

$m = $dst->prepare("INSERT INTO measurement (test_id, section, temp, pos, metric, value_num, value_err, value_text)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$n_values = [];
foreach ($tests as $t) {
    $values = $t['values'];
    unset($t['values']);
    $test_id = insert($dst, 'test_info', $t, ['updated_at']);
    foreach ($values as $v) $m->execute([$test_id, ...$v]);
    $n_values[$t['stage']] = ($n_values[$t['stage']] ?? 0) + count($values);
}
foreach ($history as $r)    insert($dst, 'history', $r);
foreach ($users as $r)      { unset($r['shift_status']); insert($dst, 'users', $r); }
foreach ($user_privs as $r) { unset($r['allowed_host']); insert($dst, 'user_privileges', $r); }
$dst->commit();

// 5. Report
echo "\nRows written to ccdqc:\n";
foreach (['ccd', 'die', 'module', 'test_info', 'measurement', 'history', 'users', 'user_privileges'] as $table)
    printf("  %-16s %6d\n", $table, $dst->query("SELECT COUNT(*) FROM ccdqc.`$table`")->fetchColumn());
echo "  measurement per stage: " . json_encode($n_values) . "\n";
echo "\nConversion rules applied (§7):\n";
printf("  CCD test values 0.00 -> no row (rule 3): %d\n", $why['ccd_zero'] ?? 0);
printf("  peak \"0.0\" -> no row (rule 2):          %d\n", $why['peak_zero'] ?? 0);
foreach (['text_in_number' => 'text in a numeric metric, kept as text (rule 5)',
          'odd_check' => 'check value other than 0/1, kept as text'] as $k => $label) {
    printf("  %s: %d\n", $label, count($why[$k] ?? []));
    foreach ($why[$k] ?? [] as $line) echo "    $line\n";
}
echo "\nDone.\n";
