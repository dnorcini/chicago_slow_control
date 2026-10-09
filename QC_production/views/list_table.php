<?php
// views/list_table.php
// The page of list.php: tabs, then the summary or the table of items.
// Variables from list.php: $stage, $P, $L, $view, $positions, $rows (each with 'tally').

$label = $P['item_label'];
$unit  = $L['unit'] ?? '';
$count = fn(callable $f) => count(array_filter($rows, $f));

// One cell of the items table (config/pages.php 'list' 'columns')
$cell = function (string $ref, array $r) use ($stage): string {
    if ($ref === 'id') {
        $id = (int)$r['item']['id'];
        return has_priv('full') ? '<a href="' . h("details.php?stage=$stage&id=$id") . '" style="font-size: 14pt">' . $id . '</a>' : (string)$id;
    }
    if ($ref === 'tally')    return h($r['tally']);
    if ($ref === 'location') return h($r['location']);
    if (substr_count($ref, '/') === 3) {
        $c = explode('/', $ref);
        return h(format_value($r['values'][implode('|', $c)] ?? null, METRICS[$c[3]]['type']));
    }
    [$kind, $col] = explode('.', $ref);
    return h($kind === 'item' ? $r['item'][$col] : ($r['test'][$col] ?? ''));
};

// Rows shown in red (config 'red')
$red = fn(array $r) => match ($L['red'] ?? '') {
    'unchecked'  => count(array_filter($positions, fn($p) => !isset($r['values']["amp||$p|check"]))) > 0,
    'no_channel' => count(array_filter($positions, fn($p) => empty($r['dies'][$p]['channel']))) > 0,
    default      => false,
};
?>
<table border="1" cellpadding="2" width="100%"><tr>
<?php foreach (['summary' => 'Summary', 'items' => $L['tab']] as $v => $text): ?>
  <th><?php if ($v === $view): ?><b><?= h($text) ?></b><?php else: ?>
      <a href="<?= h("list.php?stage=$stage" . ($v === 'items' ? '&view=items' : '')) ?>"><?= h($text) ?></a><?php endif ?></th>
<?php endforeach ?>
<?php if (has_priv('full')): ?>
  <th>Download CSV:
      <a href="<?= h("export.php?stage=$stage") ?>" title="One row per test, with the old column names (compatible with the old schema)">wide (old columns)</a>
      &middot; <a href="<?= h("export.php?stage=$stage&format=long") ?>" title="One row per value: easiest for pandas and similar tools">long (one row per value)</a></th>
<?php endif ?>
</tr></table>

<?php if ($view === 'summary'): ?>
<br>
<?php foreach ($L['summary'] as $part): ?>
<?php if ($part === 'tally'):
    $good = 0; ?>
<b><?= $unit === 'amp' ? 'Amplifier' : h($unit) ?> grades</b>
<table border="1" cellpadding="2" width="100%">
  <tr><?php foreach (TALLIES as $t => $d): ?><th align="left" style="background-color: <?= $d['color'] ?>;"><?= h("$t ({$d['note']} {$unit}s)") ?></th><?php endforeach ?></tr>
  <tr><?php foreach (TALLIES as $t => $d):
      $n = $count(fn($r) => $r['tally'] === $t);
      if ($t !== 'Geodude') $good += $n; ?><td align="left" style="background-color: <?= $d['color'] ?>;"><?= $n ?></td><?php endforeach ?></tr>
</table>
<?php elseif (str_starts_with($part, 'grades:')):
    $p = substr($part, 7); ?>
<table border="1" cellpadding="2" width="100%">
  <tr><?php foreach (['Science', 'Engineering', 'Operational', 'Failed'] as $g): ?><th align="left"><?= h("$p $g") ?></th><?php endforeach ?></tr>
  <tr><?php foreach (['Science', 'Engineering', 'Operational', 'Failed'] as $g): ?><td align="left"><?= $count(fn($r) => ($r['values']["amp||$p|grade"]['value_text'] ?? '') === $g) ?></td><?php endforeach ?></tr>
</table>
<?php elseif ($part === 'totals'):
    $total = count($rows); ?>
<br><b>Totals</b>
<table border="1" cellpadding="2" width="100%">
  <tr><th align="left">Number of Total <?= h($L['tab']) ?></th><td align="left"><?= $total ?></td>
      <th align="left">Yield (at least 1 Science grade <?= h($unit) ?>)</th>
      <td align="left"><?= number_format($total ? ($good ?? 0) / $total * 100 : 0, 2) ?>%</td></tr>
</table>
<?php elseif ($part === 'ccd'):
    $has = fn($r, $word) => stripos((string)$r['item']['ccd_type'], $word) !== false;
    $skipper = fn($status) => $count(fn($r) => $has($r, 'Skipper') && $r['item']['status'] === $status); ?>
<table border="1" cellpadding="2" width="100%">
  <tr><th align="left">Number of Total CCDs</th><th align="left">Number of DES-capable CCDs</th><th align="left">Number of Skipper-capable CCDs</th></tr>
  <tr><td><?= count($rows) ?></td><td><?= $count(fn($r) => $has($r, 'DES')) ?></td><td><?= $count(fn($r) => $has($r, 'Skipper')) ?></td></tr>
  <tr><th align="left">Number of Science-grade Skipper-capable CCDs</th><th align="left">Number of Operation-grade Skipper-capable CCDs</th><th align="left">Number of Failed Skipper-capable CCDs</th></tr>
  <tr><td><?= $skipper('Science-grade') ?></td><td><?= $skipper('Operation-grade') ?></td><td><?= $skipper('Failed') ?></td></tr>
</table>
<?php endif ?>
<?php endforeach ?>

<?php else: ?>
<table border="1" cellpadding="2" width="100%">
  <tr><?php foreach (array_keys($L['columns']) as $heading): ?><th align="left"><?= h($heading) ?></th><?php endforeach ?></tr>
<?php foreach ($rows as $r): ?>
  <tr<?= $red($r) ? ' style="color: red;"' : '' ?>>
<?php foreach ($L['columns'] as $ref): ?>
    <td align="left"<?= $ref === 'tally' && $r['tally'] !== '' ? ' style="background-color: ' . TALLIES[$r['tally']]['color'] . ';"' : '' ?>><?= $cell($ref, $r) ?></td>
<?php endforeach ?>
  </tr>
<?php endforeach ?>
</table>
<?php endif ?>
