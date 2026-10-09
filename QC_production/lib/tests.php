<?php
// lib/tests.php
// Items (ccd / die / module), their tests (test_info) and the measured values,
// as used by details.php. All SQL is here; table names come from
// config/protocol.php, never from the request.

function item_table(string $stage): string {
    return STAGES[$stage]['item'];
}

function find_item(string $stage, int $id): ?array {
    return one('SELECT * FROM `' . item_table($stage) . '` WHERE id = ?', [$id]);
}

// [first, previous, next, last] item ids around $id (null where there is none)
function neighbours(string $stage, int $id): array {
    $t = '`' . item_table($stage) . '`';
    $r = one("SELECT (SELECT MIN(id) FROM $t) first, (SELECT MAX(id) FROM $t WHERE id < ?) prev,
                     (SELECT MIN(id) FROM $t WHERE id > ?) next, (SELECT MAX(id) FROM $t) last", [$id, $id]);
    return [$r['first'], $r['prev'], $r['next'], $r['last']];
}

function new_item(string $stage): int {
    q('INSERT INTO `' . item_table($stage) . '` () VALUES ()');
    return (int)db()->lastInsertId();
}

// The item's latest test of this stage, or null if it has none yet
function find_test(string $stage, int $item_id): ?array {
    return one('SELECT * FROM test_info WHERE item_type = ? AND item_id = ? AND stage = ?
                ORDER BY test_number DESC LIMIT 1', [item_table($stage), $item_id, $stage]);
}

// Creates the item's next test (test_number counts across all its stages)
function create_test(string $stage, int $item_id): array {
    $type = item_table($stage);
    $n = one('SELECT COALESCE(MAX(test_number), 0) + 1 n FROM test_info WHERE item_type = ? AND item_id = ?',
             [$type, $item_id])['n'];
    q('INSERT INTO test_info (item_type, item_id, stage, test_number) VALUES (?, ?, ?, ?)', [$type, $item_id, $stage, $n]);
    return one('SELECT * FROM test_info WHERE id = ?', [db()->lastInsertId()]);
}

// "section|temp|pos|metric" => measurement row
function load_values(?array $test): array {
    if (!$test)
        return [];
    $values = [];
    foreach (all('SELECT * FROM measurement WHERE test_id = ?', [$test['id']]) as $m)
        $values["{$m['section']}|{$m['temp']}|{$m['pos']}|{$m['metric']}"] = $m;
    return $values;
}

// Saves one block of the page (config/pages.php) from its form:
// item[col], test[col], v["section/temp/pos/metric"]. Blank = NULL / no row.
// Returns messages for the user (values that could not be saved).
function save_block(string $stage, array $item, ?array $test, array $block, array $post): array {
    $msgs = [];
    $item_set = $test_set = [];
    foreach (array_merge($block['fields'] ?? [], $block['fields_after'] ?? []) as $row)
        foreach ($row as $f) {
            $d = field_def($stage, $f);
            if ($d['kind'] !== 'item' && $d['kind'] !== 'test')
                continue;
            $v = (string)($post[$d['kind']][$d['col']] ?? '');     // kept as typed
            if (!empty($d['check']))
                $v = $v === '1' ? 1 : 0;
            elseif (trim($v) === '')
                $v = null;
            elseif (!empty($d['num']) && !is_numeric(trim($v))) {
                $msgs[] = "{$d['label']}: \"$v\" is not a number, not saved.";
                continue;
            }
            if ($d['kind'] === 'item') $item_set[$d['col']] = $v;
            else                       $test_set[$d['col']] = $v;
        }
    $cells = [];                              // [section, temp, pos, metric] => parsed value or null
    foreach (block_cells($stage, $block) as $c)
        $cells[implode('/', $c)] = [$c, parse_value((string)($post['v'][implode('/', $c)] ?? ''), METRICS[$c[3]]['type'])];
    // The item's first test of this stage is created when there is something to store
    $has_values = array_filter($test_set, fn($v) => $v !== null) || array_filter(array_column($cells, 1));

    db()->beginTransaction();
    if ($item_set)
        q('UPDATE `' . item_table($stage) . '` SET ' . implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($item_set)))
          . ', updated_at = NOW() WHERE id = ?', [...array_values($item_set), $item['id']]);
    if (!$test && $has_values)
        $test = create_test($stage, $item['id']);
    if ($test && $test_set)
        q('UPDATE test_info SET ' . implode(', ', array_map(fn($c) => "`$c` = ?", array_keys($test_set)))
          . ' WHERE id = ?', [...array_values($test_set), $test['id']]);
    if ($test && $cells) {
        $del = db()->prepare('DELETE FROM measurement WHERE test_id = ? AND section = ? AND temp = ? AND pos = ? AND metric = ?');
        $ins = db()->prepare('INSERT INTO measurement (test_id, section, temp, pos, metric, value_num, value_err, value_text)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        foreach ($cells as [$c, $v]) {
            $del->execute([$test['id'], ...$c]);
            if ($v !== null)
                $ins->execute([$test['id'], ...$c, ...$v]);
        }
        q('UPDATE test_info SET updated_at = NOW() WHERE id = ?', [$test['id']]);
    }
    db()->commit();
    return $msgs;
}

// Modules: the die at each position (pos => die row)
function module_dies(int $module_id): array {
    $dies = [];
    foreach (all('SELECT * FROM die WHERE module_id = ?', [$module_id]) as $d)
        $dies[$d['module_pos']] = $d;
    return $dies;
}

// Saves the module layout: die[pos], amp[pos], channel[pos]. A die already in
// another module must be taken out there first.
function save_layout(int $module_id, array $positions, array $post): array {
    $want = [];
    foreach ($positions as $p) {
        $die = trim((string)($post['die'][$p] ?? ''));
        if ($die === '')
            continue;
        if (!ctype_digit($die))
            return ["Position $p: \"$die\" is not a die id. Nothing saved."];
        $d = one('SELECT id, module_id, module_pos FROM die WHERE id = ?', [(int)$die]);
        if (!$d)
            return ["Position $p: there is no die $die. Nothing saved."];
        if ($d['module_id'] !== null && (int)$d['module_id'] !== $module_id) {
            $m = one('SELECT name FROM module WHERE id = ?', [$d['module_id']])['name'];
            return ["Die $die is already in module $m (position {$d['module_pos']}). Take it out there first. Nothing saved."];
        }
        if (in_array((int)$die, array_column($want, 'id'), true))
            return ["Die $die is entered at two positions. Nothing saved."];
        $want[$p] = ['id' => (int)$die, 'amp' => trim((string)($post['amp'][$p] ?? '')) ?: null,
                     'channel' => trim((string)($post['channel'][$p] ?? '')) ?: null];
    }
    db()->beginTransaction();
    q('UPDATE die SET module_id = NULL, module_pos = NULL, amp = NULL, channel = NULL WHERE module_id = ?', [$module_id]);
    foreach ($want as $p => $w)
        q('UPDATE die SET module_id = ?, module_pos = ?, amp = ?, channel = ? WHERE id = ?',
          [$module_id, $p, $w['amp'], $w['channel'], $w['id']]);
    q('UPDATE module SET updated_at = NOW() WHERE id = ?', [$module_id]);
    db()->commit();
    return [];
}

// Everything list.php shows for one stage, in a few queries: one row per item
// that has a test of this stage or no test yet (a new item), by id:
// ['item' => row, 'test' => row|null, 'values' => "section|temp|pos|metric" => row,
//  'location' => current location, 'dies' => pos => die row (modules)]
function list_rows(string $stage, array $sections): array {
    $type = item_table($stage);
    $tests = [];                              // the latest test of this stage per item
    foreach (all('SELECT * FROM test_info WHERE item_type = ? AND stage = ? ORDER BY test_number', [$type, $stage]) as $t)
        $tests[$t['item_id']] = $t;
    $tested = array_flip(array_column(all('SELECT DISTINCT item_id FROM test_info WHERE item_type = ?', [$type]), 'item_id'));

    $values = [];
    if ($sections) {
        $in = implode(',', array_fill(0, count($sections), '?'));
        foreach (all("SELECT m.* FROM measurement m JOIN test_info t ON t.id = m.test_id
                      WHERE t.item_type = ? AND t.stage = ? AND m.section IN ($in)", [$type, $stage, ...$sections]) as $m)
            $values[$m['test_id']]["{$m['section']}|{$m['temp']}|{$m['pos']}|{$m['metric']}"] = $m;
    }
    $locations = [];                          // same order as current_location(): the first row per item wins
    foreach (all("SELECT sub_id, location FROM history WHERE type = ?
                  ORDER BY sub_id, (date IS NULL OR date = '') DESC, date DESC, entry DESC", [$type]) as $h)
        $locations[$h['sub_id']] ??= (string)$h['location'];
    $dies = [];
    if ($type === 'module')
        foreach (all('SELECT * FROM die WHERE module_id IS NOT NULL') as $d)
            $dies[$d['module_id']][$d['module_pos']] = $d;

    $rows = [];
    foreach (all("SELECT * FROM `$type` ORDER BY id") as $item) {
        $id = $item['id'];
        $test = $tests[$id] ?? null;
        if (!$test && isset($tested[$id]))   // tested, but only at other stages
            continue;
        $rows[] = ['item' => $item, 'test' => $test, 'values' => $test ? ($values[$test['id']] ?? []) : [],
                   'location' => $locations[$id] ?? '', 'dies' => $dies[$id] ?? []];
    }
    return $rows;
}
