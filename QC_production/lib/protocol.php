<?php
// lib/protocol.php
// Reads config/protocol.php (what is stored) and config/pages.php (how
// details.php shows it). Used by migrate/migrate.php, export.php and the pages.
require_once __DIR__ . '/../config/protocol.php';
require_once __DIR__ . '/../config/options.php';
require_once __DIR__ . '/../config/pages.php';

// Every value one test of this stage can hold, in form order:
// [['section' => 'img4', 'temp' => 'low', 'pos' => 'A', 'metric' => 'peak1',
//   'type' => 'num_err', 'old' => 'Image4_Low_Peak1_A'], ...]
function protocol_cells(string $stage): array {
    $S = STAGES[$stage];
    $positions = $S['positions'];
    $pairs = [];
    foreach ($positions as $a)
        foreach ($positions as $b)
            if ($a !== $b) $pairs[] = $a . $b;

    $cells = [];
    foreach ($S['sections'] as $section => $sec) {
        // 'temps' is a list (same metrics at each), a map (temp => own lists), or absent ('')
        $temps = $sec['temps'] ?? [''];
        $by_temp = array_is_list($temps) ? array_fill_keys($temps, $sec) : $temps;

        foreach ($by_temp as $temp => $lists) {
            $add = function (string $metric, string $pos, string $old) use (&$cells, $section, $temp) {
                $cells[] = ['section' => $section, 'temp' => (string)$temp, 'pos' => $pos, 'metric' => $metric,
                            'type' => METRICS[$metric]['type'], 'old' => $old];
            };
            $col = fn($metric, $pos) => old_column($stage, $sec['old'], (string)$temp, $metric, $pos);

            foreach ($lists['per_pos'] ?? [] as $m)
                foreach ($positions as $p) $add($m, $p, $col($m, $p));
            foreach ($lists['once'] ?? [] as $k => $v) {
                if (is_int($k)) $add($v, '', $col($v, $positions[0]));   // stored in the old _A column
                else            $add($k, '', $v);
            }
            foreach ($lists['pairs'] ?? [] as $m)
                foreach ($pairs as $p) $add($m, $p, $col($m, $p));
        }
    }
    return $cells;
}

// Old column name: prefix _ Temp _ Metric _ pos, leaving out the empty parts.
function old_column(string $stage, string $prefix, string $temp, string $metric, string $pos): string {
    $metric_col = STAGES[$stage]['cols'][$metric] ?? METRICS[$metric]['col'];
    $parts = [$prefix, $temp === '' ? '' : TEMPS[$temp]['col'], $metric_col, $pos];
    return implode('_', array_filter($parts, fn($p) => $p !== ''));
}

// The stage key from the URL, or a 404 if protocol.php doesn't know it.
function stage_or_404(string $stage): string {
    if (isset(STAGES[$stage]))
        return $stage;
    http_response_code(404);
    exit('Unknown stage "' . h($stage) . '". Known stages: ' . implode(', ', array_keys(STAGES)) . "\n");
}

// One measurement row as text, the way the old columns held it:
// num_err as "5.94848 +/- 0.00272591", a ticked check as 1 (unticked = no row = 0).
function format_value(?array $m, string $type): string {
    if ($m === null)
        return $type === 'check' ? '0' : '';
    if ($m['value_text'] !== null)
        return $m['value_text'];
    if ($type === 'num_err' && $m['value_err'] !== null)
        return $m['value_num'] . ' +/- ' . $m['value_err'];
    return (string)$m['value_num'];
}

// The metrics one section measures at one temperature:
// ['per_pos' => [...], 'once' => [...], 'pairs' => [...]]
function section_lists(string $stage, string $section, string $temp): array {
    $sec = STAGES[$stage]['sections'][$section];
    $temps = $sec['temps'] ?? [''];
    $lists = array_is_list($temps) ? $sec : $temps[$temp];
    $once = [];
    foreach ($lists['once'] ?? [] as $k => $v)
        $once[] = is_int($k) ? $v : $k;
    return ['per_pos' => $lists['per_pos'] ?? [], 'once' => $once, 'pairs' => $lists['pairs'] ?? []];
}

// Ordered pairs of positions: AB, AC, ... DC
function position_pairs(array $positions): array {
    $pairs = [];
    foreach ($positions as $a)
        foreach ($positions as $b)
            if ($a !== $b) $pairs[] = $a . $b;
    return $pairs;
}

function metric_label(string $stage, string $metric): string {
    return STAGES[$stage]['labels'][$metric] ?? METRICS[$metric]['label'];
}

// Row label of a position: a sprintf format (%1$s position, %2$d index from 0,
// %3$d index from 1) or a position => label map (config/pages.php 'pos_label')
function position_label(string|array $format, string $pos, int $i): string {
    return is_array($format) ? $format[$pos] : sprintf($format, $pos, $i, $i + 1);
}

// A form value turned into what is stored: [value_num, value_err, value_text],
// or null when blank (= no row). Text is kept exactly as typed; text in a
// number field is kept as text.
function parse_value(string $raw, string $type): ?array {
    $t = trim($raw);
    if ($t === '')
        return null;
    if ($type === 'check')
        return [1, null, null];
    if ($type === 'num' && is_numeric($t))
        return [(float)$t, null, null];
    if ($type === 'num_err') {
        if (is_numeric($t))
            return [(float)$t, null, null];
        if (preg_match('/^(\S+)\s*\+\/-\s*(\S+)$/', $t, $m) && is_numeric($m[1]) && is_numeric($m[2]))
            return [(float)$m[1], (float)$m[2], null];
    }
    return [null, null, $raw];
}

// Values shown on the page but never stored (METRICS 'derived'). $v = one
// position's values, metric => measurement row.
function derived_value(string $metric, array $v, ?float $divisor): string {
    $num = fn($m) => isset($v[$m]) && $v[$m]['value_num'] !== null ? $v[$m]['value_num'] : null;
    $gain = $num('gain');
    if (!$gain)
        return '';
    if ($metric === 'res_e' && $num('res') !== null)
        return (string)round($num('res') / $gain, 4);
    if ($metric === 'dc_day' && $num('dark_current') !== null && $divisor)
        return number_format($num('dark_current') / $gain * 86400 / $divisor, 2, '.', '');
    return '';
}

// The tables of a block's grid, each with its metrics, once-per-image metrics
// and pairs filled in (config/pages.php 'tables', or one table of the section).
function block_tables(string $stage, array $block): array {
    [$section, $temp] = explode('/', $block['grid']);
    $lists = section_lists($stage, $section, $temp);
    $tables = $block['tables'] ?? [['metrics' => $block['show'] ?? $lists['per_pos'], 'once' => $lists['once'],
                                    'pairs' => $lists['pairs']]];
    foreach ($tables as &$t)
        $t += ['title' => '', 'metrics' => [], 'once' => [], 'pairs' => [], 'files' => []];
    return $tables;
}

// One single field of config/pages.php ('item.name', 'test.tester',
// 'test/low/temp_b', 'updated', 'location', or [field, overrides...]) as
// ['kind' => item|test|value|updated|location, 'col' or 'cell', 'label', ...]
function field_def(string $stage, string|array $f): array {
    $over = is_array($f) ? array_slice($f, 1, null, true) : [];
    $ref = is_array($f) ? $f[0] : $f;
    if ($ref === 'updated')  return ['kind' => 'updated', 'label' => 'Entry Last updated'];
    if ($ref === 'location') return ['kind' => 'location', 'label' => 'Current Location'];
    if (str_contains($ref, '/')) {
        [$section, $temp, $metric] = explode('/', $ref);
        $def = METRICS[$metric];
        $label = metric_label($stage, $metric);
        return $over + ['kind' => 'value', 'cell' => [$section, $temp, '', $metric],
                        'label' => $temp === '' ? $label : ucfirst($temp) . ' ' . $label] + $def;
    }
    [$kind, $col] = explode('.', $ref);
    return $over + ['kind' => $kind, 'col' => $col] + FIELDS[$ref];
}

// Every measurement cell a block shows, as [section, temp, pos, metric] (not
// the derived ones). Saving the block replaces exactly these.
function block_cells(string $stage, array $block): array {
    $cells = [];
    foreach (array_merge($block['fields'] ?? [], $block['fields_after'] ?? []) as $row)
        foreach ($row as $f) {
            $d = field_def($stage, $f);
            if ($d['kind'] === 'value') $cells[] = $d['cell'];
        }
    if (isset($block['grid'])) {
        [$section, $temp] = explode('/', $block['grid']);
        $positions = STAGES[$stage]['positions'];
        foreach (block_tables($stage, $block) as $t) {
            foreach ($t['metrics'] as $m)
                if (empty(METRICS[$m]['derived']))
                    foreach ($positions as $p) $cells[] = [$section, $temp, $p, $m];
            foreach ($t['once'] as $m)
                $cells[] = [$section, $temp, '', $m];
            foreach ($t['pairs'] as $m)
                foreach (position_pairs($positions) as $p) $cells[] = [$section, $temp, $p, $m];
        }
    }
    return $cells;
}

// The tally of a die or module from its 4 amp/CCD grades (rule confirmed
// 2026-10-04): 4 Science = Charizard, 3 = Charmeleon, 1-2 = Charmander,
// 0 = Geodude; '' until all four grades are filled in.
function grade_tally(array $grades): string {
    if (count(array_filter($grades, fn($g) => $g !== null && $g !== '')) < 4)
        return '';
    $n = count(array_filter($grades, fn($g) => $g === 'Science'));
    return match (true) { $n === 4 => 'Charizard', $n === 3 => 'Charmeleon',
                          $n >= 1 => 'Charmander', default => 'Geodude' };
}

// Colour and summary note of each tally, as on the old list pages
const TALLIES = [
    'Charizard'  => ['color' => 'red',    'note' => '4 Science grade'],
    'Charmeleon' => ['color' => 'orange', 'note' => '3 Science grade'],
    'Charmander' => ['color' => 'yellow', 'note' => '1 or 2 Science grade'],
    'Geodude'    => ['color' => 'gray',   'note' => '0 Science grade'],
];
