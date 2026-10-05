<?php
// lib/protocol.php
// Reads config/protocol.php. Used by migrate/migrate.php now, and by the pages
// and the CSV export in item 3.
require_once __DIR__ . '/../config/protocol.php';

// The metric's type in this stage.
function metric_type(string $stage, string $metric): string {
    return STAGES[$stage]['types'][$metric] ?? METRICS[$metric]['type'];
}

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
            $add = function (string $metric, string $pos, string $old) use (&$cells, $stage, $section, $temp) {
                $cells[] = ['section' => $section, 'temp' => (string)$temp, 'pos' => $pos, 'metric' => $metric,
                            'type' => metric_type($stage, $metric), 'old' => $old];
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
