<?php
// migrate/verify.php
// Item 2 check: reads ccdqc back and compares every value with die_qc.
// Independent of migrate.php's conversion code: it starts from what is in
// ccdqc and works back to the old cell.
//
//   docker exec -e DB_HOST=db -e TARGET_DB_HOST=targetDB ccdqc-target php /var/www/html/migrate/verify.php
//
// Prints every difference; exits 1 if there is any that the conversion rules
// don't explain.
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../QC_production/lib/protocol.php';

function connect(string $host, string $db): PDO {
    $cfg_file = __DIR__ . '/../QC_production/db_config.php';
    $cfg = is_file($cfg_file) ? require $cfg_file : [];
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4",
                   getenv('DB_USER') ?: ($cfg['user'] ?? ''), getenv('DB_PASSWORD') ?: ($cfg['password'] ?? ''),
                   [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
    $pdo->exec("SET time_zone = '+00:00'");
    return $pdo;
}
$cfg_file = __DIR__ . '/../QC_production/db_config.php';
$src_host = getenv('DB_HOST') ?: ((is_file($cfg_file) ? require $cfg_file : [])['host'] ?? 'localhost');
$old = connect($src_host, 'die_qc');
$old->exec("SET NAMES binary");                       // raw bytes = the real UTF-8 text
$new = connect(getenv('TARGET_DB_HOST') ?: $src_host, 'ccdqc');

$problems = 0;
$skipped = ['empty' => 0, 'ccd_zero' => 0, 'peak_zero' => 0, 'check_zero' => 0];
function problem(string $msg): void { global $problems; $problems++; echo "  DIFF $msg\n"; }
function same_num(string $a, $b): bool { return abs((float)$a - (float)$b) <= 1e-9 * max(1, abs((float)$a)); }
function keyed(PDO $db, string $sql, string $key): array {
    $out = [];
    foreach ($db->query($sql) as $r) $out[$r[$key]] = $r;
    return $out;
}

// 1. Measurements: every old cell in the protocol, against what ccdqc holds
$num = '[-+]?(?:\d+\.?\d*|\.\d+)(?:[eE][-+]?\d+)?';
$checked = 0;
foreach (STAGES as $stage => $S) {
    $rows = keyed($old, "SELECT * FROM `{$S['old_table']}`", $S['old_table'] === 'CCD' ? 'ID' : 'id');
    $tests = keyed($new, "SELECT id, item_id FROM test_info WHERE stage = " . $new->quote($stage), 'item_id');
    $values = [];
    foreach ($new->query("SELECT m.* FROM measurement m JOIN test_info t ON t.id = m.test_id
                          WHERE t.stage = " . $new->quote($stage)) as $v)
        $values[$v['test_id']]["{$v['section']}|{$v['temp']}|{$v['pos']}|{$v['metric']}"] = $v;
    if (count($tests) !== count($rows)) problem("$stage: " . count($rows) . " old rows, " . count($tests) . " tests");

    foreach ($rows as $id => $r) {
        $test_id = $tests[$id]['id'] ?? null;
        if (!$test_id) { problem("$stage $id: no test"); continue; }
        foreach (protocol_cells($stage) as $c) {
            $raw = $r[$c['old']];
            $v = $values[$test_id]["{$c['section']}|{$c['temp']}|{$c['pos']}|{$c['metric']}"] ?? null;
            unset($values[$test_id]["{$c['section']}|{$c['temp']}|{$c['pos']}|{$c['metric']}"]);
            $where = "$stage $id {$c['old']}";
            $t = $raw === null ? '' : trim($raw);
            $checked++;
            if ($v === null) {                                         // no row: must be a rule
                if ($t === '') $skipped['empty']++;
                elseif ($c['type'] === 'check' && $t === '0') $skipped['check_zero']++;
                elseif ($stage === 'ccd' && is_numeric($t) && (float)$t == 0) $skipped['ccd_zero']++;
                elseif ($c['type'] === 'num_err' && is_numeric($t) && (float)$t == 0) $skipped['peak_zero']++;
                else problem("$where: old \"$raw\" has no row");
                continue;
            }
            if ($v['value_text'] !== null) {                           // text, or text in a number
                if ($v['value_text'] !== $raw) problem("$where: old \"$raw\", new text \"{$v['value_text']}\"");
            } elseif ($c['type'] === 'num_err' && $v['value_err'] !== null) {
                if (!preg_match("~^\s*($num)\s*\+/-\s*($num)\s*$~", $raw, $m)
                    || !same_num($m[1], $v['value_num']) || !same_num($m[2], $v['value_err']))
                    problem("$where: old \"$raw\", new {$v['value_num']} +/- {$v['value_err']}");
            } elseif (!is_numeric($t) || !same_num($t, $v['value_num'])) {
                problem("$where: old \"$raw\", new {$v['value_num']}");
            }
        }
        foreach ($values[$test_id] ?? [] as $k => $_) problem("$stage $id: extra value $k");
    }
}

// 2. Item and test columns, copied as they are
function compare_rows(PDO $old, PDO $new, string $label, string $old_sql, string $new_sql, string $key, array $map): int {
    $o = keyed($old, $old_sql, $key);
    $n = keyed($new, $new_sql, 'k');
    if (count($o) !== count($n)) problem("$label: " . count($o) . " old rows, " . count($n) . " new");
    foreach ($o as $k => $r)
        foreach ($map as $new_col => $old_col)
            if (!isset($n[$k]) || !array_key_exists($new_col, $n[$k]) || $n[$k][$new_col] !== $r[$old_col])
                problem("$label $k $new_col: old " . var_export($r[$old_col], true) . ", new " . var_export($n[$k][$new_col] ?? null, true));
    return count($o);
}
$ccd_cols = ['name' => 'Name', 'ccd_type' => 'CCD_Type', 'size' => 'Size', 'status' => 'Status', 'location' => 'Location',
             'wafer_id' => 'Wafer_ID', 'wafer_position' => 'Wafer_position', 'production_date' => 'Production_date',
             'packager' => 'Packager', 'packaging_date' => 'Packaging_date', 'cable_np' => 'Cable_np',
             'jfet_u1' => 'JFET_U1', 'jfet_l1' => 'JFET_L1', 'jfet_u2' => 'JFET_U2', 'jfet_l2' => 'JFET_L2',
             'glue_humid' => 'Glue_humid', 'glue_temp' => 'Glue_temp', 'glue_radon' => 'Glue_radon',
             'gluing_details' => 'Gluing_details', 'wb_humid' => 'Wb_humid', 'wb_temp' => 'Wb_temp',
             'wb_radon' => 'Wb_radon', 'wb_power' => 'Wb_power', 'wb_time' => 'Wb_time', 'wb_date' => 'Wb_date',
             'wirebonding_details' => 'Wirebonding_details', 'note' => 'Note', 'updated' => 'Last_update'];
$n = compare_rows($old, $new, 'ccd', "SELECT * FROM CCD", "SELECT *, id AS k, UNIX_TIMESTAMP(updated_at) AS updated FROM ccd", 'ID', $ccd_cols);
$die_cols = ['name' => 'Name', 'status' => 'Status', 'wafer_id' => 'Wafer_ID', 'wafer_position' => 'Wafer_Position',
             'activation' => 'Activation', 'humidity' => 'Humidity', 'radon' => 'Radon', 'updated' => 'Last_Update'];
$n += compare_rows($old, $new, 'die', "SELECT * FROM DIE", "SELECT *, id AS k, UNIX_TIMESTAMP(updated_at) AS updated FROM die", 'id', $die_cols);
$mod_cols = ['name' => 'Name', 'status' => 'Status', 'activation' => 'Activation', 'humidity' => 'Humidity', 'radon' => 'Radon',
             'updated' => 'lu'];
$n += compare_rows($old, $new, 'module',
    "SELECT s.id, BINARY s.Name AS Name, BINARY s.Status AS Status, s.Activation, s.Humidity, s.Radon,
            GREATEST(s.Last_Update, IFNULL(u.Last_Update, 0)) AS lu
       FROM MODULE_SURFACE s LEFT JOIN MODULE_UNDERGROUND2 u ON u.id = s.id
     UNION SELECT u.id, BINARY u.Name, BINARY u.Status, NULL, NULL, NULL, u.Last_Update FROM MODULE_UNDERGROUND2 u
       WHERE u.id NOT IN (SELECT id FROM MODULE_SURFACE)",
    "SELECT *, id AS k, UNIX_TIMESTAMP(updated_at) AS updated FROM module", 'id', $mod_cols);
foreach (['A', 'B', 'C', 'D'] as $p)
    $n += compare_rows($old, $new, "module position $p",
        "SELECT Die_$p AS die, id, BINARY Amp_$p AS amp, BINARY Channel_$p AS channel FROM MODULE_SURFACE WHERE Die_$p > 0
         UNION SELECT Die_$p, id, BINARY Amp_$p, BINARY Channel_$p FROM MODULE_UNDERGROUND2 WHERE Die_$p > 0",
        "SELECT id AS k, module_id, amp, channel FROM die WHERE module_pos = '$p'", 'die',
        ['module_id' => 'id', 'amp' => 'amp', 'channel' => 'channel']);
$test_cols = ['tester' => 'Tester', 'test_date' => 'Test_Date', 'test_time' => 'Test_Time', 'chamber' => 'Chamber',
              'acm' => 'ACM', 'feedthru_position' => 'Feedthru_Position', 'script' => 'Script',
              'reviewer' => 'Reviewer', 'notes' => 'Notes', 'updated' => 'Last_Update'];
foreach (['die' => 'DIE', 'surface' => 'MODULE_SURFACE', 'underground' => 'MODULE_UNDERGROUND2'] as $stage => $table)
    $n += compare_rows($old, $new, "$stage test", "SELECT * FROM $table",
        "SELECT *, item_id AS k, UNIX_TIMESTAMP(updated_at) AS updated FROM test_info WHERE stage = '$stage'", 'id', $test_cols);
$n += compare_rows($old, $new, 'ccd test', "SELECT * FROM CCD",
    "SELECT *, item_id AS k, UNIX_TIMESTAMP(updated_at) AS updated FROM test_info WHERE stage = 'ccd'", 'ID',
    ['tester' => 'Tester', 'notes' => 'Test_details', 'updated' => 'Last_update']);
$n += compare_rows($old, $new, 'history', "SELECT * FROM history", "SELECT *, entry AS k FROM history", 'entry',
    ['type' => 'type', 'sub_id' => 'sub_id', 'date' => 'date', 'action' => 'action', 'location' => 'location',
     'description' => 'description', 'reviewer' => 'reviewer', 'Last_update' => 'Last_update']);
$n += compare_rows($old, $new, 'users', "SELECT * FROM users", "SELECT *, user_name AS k FROM users", 'user_name',
    ['password' => 'password', 'full_name' => 'full_name', 'affiliation' => 'affiliation', 'email' => 'email',
     'privileges' => 'privileges']);
$n += compare_rows($old, $new, 'user_privileges', "SELECT * FROM user_privileges", "SELECT *, u_p_indx AS k FROM user_privileges",
    'u_p_indx', ['name' => 'name']);

// test numbers: surface 1, underground 2 (1 when the module has no surface test)
foreach ($new->query("SELECT t.item_id, t.test_number, s.id AS has_surface FROM test_info t
                      LEFT JOIN test_info s ON s.item_id = t.item_id AND s.stage = 'surface'
                      WHERE t.stage = 'underground'") as $r)
    if ((int)$r['test_number'] !== ($r['has_surface'] ? 2 : 1)) problem("underground {$r['item_id']}: test_number {$r['test_number']}");

echo "Measurement cells compared: $checked\n";
echo "Old cells with no row, by rule: " . json_encode($skipped) . "\n";
echo "Item, test, history and user rows compared: $n\n";
echo $problems ? "$problems differences\n" : "No differences.\n";
exit($problems ? 1 : 0);
