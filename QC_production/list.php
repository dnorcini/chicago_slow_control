<?php
// list.php
// The list of one stage, with a summary tab (tallies, totals, yield):
//   list.php?stage=die                (summary)
//   list.php?stage=die&view=items     (one row per item)
// Columns, red rows and summary parts are set per stage in config/pages.php ('list').
require __DIR__ . '/bootstrap.php';
require_priv('basic');

$stage = stage_or_404($_GET['stage'] ?? '');
$P = PAGES[$stage];
$L = $P['list'];
$view = ($_GET['view'] ?? '') === 'items' ? 'items' : 'summary';
$positions = STAGES[$stage]['positions'];

// Sections whose values the columns or the tally need
$sections = ['amp'];
foreach ($L['columns'] as $ref)
    if (substr_count($ref, '/') === 3) $sections[] = explode('/', $ref)[0];
$rows = list_rows($stage, array_values(array_unique($sections)));
foreach ($rows as &$r) {
    $grades = array_map(fn($p) => $r['values']["amp||$p|grade"]['value_text'] ?? '', $positions);
    $r['tally'] = grade_tally($grades);
}
unset($r);

page_start($P['item_label'] . ' list');
require __DIR__ . '/views/list_table.php';
page_end();
