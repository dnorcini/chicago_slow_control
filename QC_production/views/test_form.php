<?php
// views/test_form.php
// The page of details.php: navigation, then one form per block of
// config/pages.php. Variables from details.php: $stage, $P, $id, $item, $test,
// $values, $nav ([first, prev, next, last]), $msgs (block key => messages),
// $edit_history (history entry being edited, or 0).

$label     = $P['item_label'];
$positions = STAGES[$stage]['positions'];
$url       = fn($to) => "details.php?stage=$stage&id=$to";
$val       = fn(array $cell) => format_value($values[implode('|', $cell)] ?? null, METRICS[$cell[3]]['type']);

// One single field (config/pages.php 'fields'): "Label: <input>"
$field = function (array $d) use ($stage, $item, $test, $val, $id): string {
    $text = match ($d['kind']) {
        'item'     => input_for("item[{$d['col']}]", $d, $item[$d['col']]),
        'test'     => input_for("test[{$d['col']}]", $d, $test[$d['col']] ?? null),
        'value'    => input_for('v[' . implode('/', $d['cell']) . ']', $d, $val($d['cell'])),
        'updated'  => h(max($item['updated_at'], $test['updated_at'] ?? '')),
        'location' => h(current_location(STAGES[$stage]['item'], $id)),
    };
    return h($d['label']) . ': ' . $text;
};

// The upload slots of a block or table: icon (if there is a file), label, file input
$files = function (array $slots) use ($stage, $id): string {
    $html = '';
    foreach ($slots as $slot => $text) {
        if (find_upload($stage, $id, $slot)) {
            $icon = slot_exts($slot) === ['png', 'jpg', 'jpeg', 'pdf'] ? 'icon.png' : 'icon2.png';
            $html .= '<a href="' . h("file.php?stage=$stage&id=$id&slot=$slot") . '" target="_blank">'
                   . '<img src="pixmaps/' . $icon . '" alt="' . h($text) . '" title="' . h($text) . '" style="height:20px"></a> ';
        }
        $accept = implode(',', array_map(fn($e) => ".$e", slot_exts($slot)));
        $html .= h($text) . ': <input type="file" name="f[' . h($slot) . ']" accept="' . $accept . '"> &nbsp; &nbsp; ';
    }
    return $html;
};
?>
<table border="1" cellpadding="2" width="100%">
<tr>
  <th><form action="<?= h($url($id)) ?>" method="post"><?= csrf_field() ?>
      <input type="submit" name="new" value="New <?= h($label) ?>" title="Generate a new <?= h($label) ?> entry" style="font-size: 10pt">
  </form></th>
  <th><form action="details.php" method="get"><input type="hidden" name="stage" value="<?= h($stage) ?>">
      &nbsp;&nbsp;&nbsp;&nbsp;Choose <?= h($label) ?> ID: <input type="text" name="id" size="6">
  </form></th>
  <th>
<?php foreach (['first' => 'First', 'prev' => 'Previous', 'next' => 'Next', 'last' => 'Last'] as $k => $word):
    $to = $nav[array_search($k, ['first', 'prev', 'next', 'last'])]; ?>
    <button type="button" id="btn-<?= $k ?>" style="font-size: 10pt"<?= $to === null ? ' disabled' : '' ?>
            onclick="location.href = this.dataset.href" data-href="<?= h($url($to)) ?>">Goto <?= $word ?> <?= h($label) ?></button>
<?php endforeach ?>
  </th>
</tr>
</table>
<br>
<?php if (!$item): ?>
<p>There is no <?= h($label) ?> with ID <?= (int)$id ?>.</p>
<?php return; endif ?>

<?php include __DIR__ . '/section_panel.php'; ?>

<p><b><?= h($label) ?> ID: <?= (int)$id ?> &nbsp; <?= h($item['name']) ?></b>
<?php if ($test): ?> &nbsp; <small>(test <?= (int)$test['test_number'] ?> of this <?= h(STAGES[$stage]['item']) ?>)</small><?php endif ?></p>

<?php foreach ($P['blocks'] as $b):
    $key = $b['key']; ?>
<div class="section-block" data-section="<?= h($key) ?>" data-label="<?= h($b['label']) ?>" id="<?= h($key) ?>">
<?php if (isset($b['title'])): ?><b><?= h($b['title']) ?></b><?php endif ?>
<?php foreach ($msgs[$key] ?? [] as $m): ?><div style="color:red"><?= h($m) ?></div><?php endforeach ?>

<?php if (($b['special'] ?? '') === 'history'):
    $type = STAGES[$stage]['item']; ?>
<br><strong>History</strong> &nbsp; <small><i><?= h($item['name']) ?></i></small>
<table border="1" cellpadding="4" width="100%">
  <tr><th align="left" width="12%">Date</th><th align="left" width="22%">Action</th>
      <th align="left" width="22%">Location</th><th align="left" width="36%">Reviewer</th><th width="8%"></th></tr>
<?php foreach (history_rows($type, $id) as $r):
    if ((int)$r['entry'] === $edit_history): ?>
  <tr><form action="<?= h($url($id)) ?>" method="post"><?= csrf_field() ?>
      <input type="hidden" name="block" value="history"><input type="hidden" name="entry" value="<?= (int)$r['entry'] ?>">
<?php foreach (['date', 'action', 'location', 'reviewer'] as $k): ?>
      <td><input type="<?= $k === 'date' ? 'date' : 'text' ?>" name="h[<?= $k ?>]" value="<?= h($r[$k]) ?>" style="width:100%;box-sizing:border-box"></td>
<?php endforeach ?>
      <td align="center"><input type="submit" value="Save"></td>
  </form></tr>
<?php else: ?>
  <tr><td><?= h($r['date']) ?></td><td><?= h($r['action']) ?></td><td><?= h($r['location']) ?></td><td><?= h($r['reviewer']) ?></td>
      <td align="center"><a href="<?= h($url($id) . '&edit_history=' . $r['entry'] . '#history') ?>">Edit</a></td></tr>
<?php endif; endforeach ?>
  <tr><form action="<?= h($url($id)) ?>" method="post"><?= csrf_field() ?>
      <input type="hidden" name="block" value="history"><input type="hidden" name="entry" value="0">
      <td><input type="date" name="h[date]" style="width:100%;box-sizing:border-box"></td>
<?php foreach (['action' => 'Action', 'location' => 'Location', 'reviewer' => 'Reviewer'] as $k => $ph): ?>
      <td><input type="text" name="h[<?= $k ?>]" placeholder="<?= $ph ?>" style="width:100%;box-sizing:border-box"></td>
<?php endforeach ?>
      <td align="center"><input type="submit" value="Add"></td>
  </form></tr>
</table>

<?php else:
    $slots = block_slots($stage, $b); ?>
<form action="<?= h($url($id)) ?>" method="post"<?= $slots ? ' enctype="multipart/form-data"' : '' ?>>
<?= csrf_field() ?><input type="hidden" name="block" value="<?= h($key) ?>">

<?php if (($b['special'] ?? '') === 'layout'):
    $dies = module_dies($id); ?>
<table border="1" cellpadding="2" width="100%"><tr>
<?php foreach ($positions as $p):
    $d = $dies[$p] ?? null; ?>
  <td><?= h($p) ?>: DIE ID <input type="text" name="die[<?= h($p) ?>]" value="<?= h($d['id'] ?? '') ?>" size="6">
      <?php if ($d): ?><a href="<?= h("details.php?stage=die&id={$d['id']}") ?>"><?= h($d['name'] ?: "die {$d['id']}") ?></a><?php endif ?>
      <br>Amp <?= dropdown("amp[$p]", OPTIONS['amp'], $d['amp'] ?? '') ?>
      &nbsp; Channel <?= dropdown("channel[$p]", OPTIONS['channel'], $d['channel'] ?? '') ?></td>
<?php endforeach ?>
</tr></table>
<?php endif ?>

<?php if (!empty($b['fields'])): ?>
<table border="1" cellpadding="2" width="100%">
<?php foreach ($b['fields'] as $row): ?>
  <tr><?php foreach ($row as $f): ?><td style="white-space: nowrap;"><?= $field(field_def($stage, $f)) ?></td><?php endforeach ?></tr>
<?php endforeach ?>
</table>
<?php endif ?>

<?php if (isset($b['grid'])):
    [$section, $temp] = explode('/', $b['grid']);
    $pos_label = $b['pos_label'] ?? $P['pos_label'];
    foreach (block_tables($stage, $b) as $t): ?>
<?php if ($t['title'] !== ''): ?><br><b><?= h($t['title']) ?></b><?php endif ?>
<table border="1" cellpadding="2">
<?php if ($t['metrics']): ?>
  <tr><td align="left" style="white-space: nowrap;">Amplifier</td>
<?php foreach ($t['metrics'] as $m): ?>      <td align="left" style="white-space: nowrap;"><?= h(metric_label($stage, $m)) ?></td>
<?php endforeach; foreach ($b['pos_files'] ?? [] as $text): ?>      <td align="left"><?= h($text) ?></td>
<?php endforeach ?>  </tr>
<?php foreach ($positions as $i => $p):
    $row = [];
    foreach ($t['metrics'] as $m) $row[$m] = $values["$section|$temp|$p|$m"] ?? null; ?>
  <tr><td style="white-space: nowrap;"><?= h(position_label($pos_label, $p, $i)) ?></td>
<?php foreach ($t['metrics'] as $m): ?>      <td<?= empty(METRICS[$m]['derived']) ? '' : ' align="center"' ?>><?= empty(METRICS[$m]['derived'])
          ? input_for("v[$section/$temp/$p/$m]", METRICS[$m], $val([$section, $temp, $p, $m]))
          : h(derived_value($m, array_filter($row), $b['divisor'] ?? null)) ?></td>
<?php endforeach;
      foreach ($b['pos_files'] ?? [] as $prefix => $text): ?>      <td><?= $files(["{$prefix}_$p" => $text]) ?></td>
<?php endforeach ?>  </tr>
<?php endforeach; endif ?>
<?php if ($t['pairs']):
    foreach ($t['pairs'] as $m): ?>
  <tr><td></td><?php foreach ($positions as $i => $p): ?><td><?= h(position_label($pos_label, $p, $i)) ?></td><?php endforeach ?></tr>
<?php foreach ($positions as $i => $a): ?>
  <tr><td><?= h(position_label($pos_label, $a, $i)) ?></td>
<?php foreach ($positions as $b2): ?>      <td><?= $a === $b2 ? 'N/A' : input_for("v[$section/$temp/$a$b2/$m]", METRICS[$m], $val([$section, $temp, $a . $b2, $m])) ?></td>
<?php endforeach ?>  </tr>
<?php endforeach; endforeach; endif ?>
<?php foreach ($t['once'] as $m): ?>
  <tr><td colspan="<?= max(count($t['metrics']), count($positions)) + 1 ?>"><?= h(metric_label($stage, $m)) ?>:
      <?= input_for("v[$section/$temp//$m]", ['size' => 100] + METRICS[$m], $val([$section, $temp, '', $m])) ?></td></tr>
<?php endforeach ?>
<?php if ($t['files']): ?>
  <tr><td colspan="<?= max(count($t['metrics']), count($positions)) + 1 ?>" style="border: none; white-space: nowrap;">
      <input type="submit" value="Submit"> &nbsp; &nbsp; <?= $files($t['files']) ?></td></tr>
<?php endif ?>
</table>
<?php endforeach; endif ?>

<?php if (!empty($b['fields_after'])): ?>
<table border="1" cellpadding="2" width="100%">
<?php foreach ($b['fields_after'] as $row): ?>
  <tr><?php foreach ($row as $f): ?><td><?= $field(field_def($stage, $f)) ?></td><?php endforeach ?></tr>
<?php endforeach ?>
</table>
<?php endif ?>

<p><input type="submit" value="Submit"> &nbsp; &nbsp; <?= $files($b['files'] ?? []) ?></p>
</form>
<?php endif ?>
</div>
<br>
<?php endforeach ?>

<div id="delete_test" style="border-top: 1px solid #999; padding-top: 6px;">
<?php $confirm = ($msgs['delete_test'] ?? []) === ['confirm']; ?>
<?php foreach ($confirm ? [] : $msgs['delete_test'] ?? [] as $m): ?><div style="color:red"><?= h($m) ?></div><?php endforeach ?>
<?php if ($test && $confirm): ?>
  <form action="<?= h($url($id)) ?>" method="post" style="display:inline"><?= csrf_field() ?>
    <input type="hidden" name="really_delete_test" value="<?= (int)$test['id'] ?>">
    <b style="color:red">Really delete the <?= h($stage) ?> test <?= (int)$test['test_number'] ?> of <?= h($item['name'] ?: "$label $id") ?>?
    This cannot be undone.</b> <input type="submit" value="Yes, delete">
  </form>
  <form action="<?= h($url($id)) ?>" method="get" style="display:inline">
    <input type="hidden" name="stage" value="<?= h($stage) ?>"><input type="hidden" name="id" value="<?= (int)$id ?>">
    <input type="submit" value="No"></form>
<?php elseif ($test): ?>
  <form action="<?= h($url($id)) ?>" method="post"><?= csrf_field() ?>
    <input type="submit" name="delete_test" value="Delete this <?= h($stage) ?> test"
           title="Deletes this page's test (not the <?= h($label) ?>). Only possible once all its fields are empty. Uploaded files are kept.">
    <small>Only possible once every field of this test is empty. The <?= h($label) ?> itself, its other tests and its uploaded files are kept.</small>
  </form>
<?php endif ?>
</div>

<?php include __DIR__ . '/table_navigation.php'; ?>
