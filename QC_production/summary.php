<?php
// summary.php
// Hub page linking to the list and details page of each stage.
require __DIR__ . '/bootstrap.php';
require_priv('basic');

$rows = [                                     // stage => [table label, list label, details label]
    'ccd'         => ['Pre-Production CCD', 'Pre-Production List',     'Pre-Production Details'],
    'die'         => ['DAMIC-M Die',        'Die List',                'Die Details'],
    'surface'     => ['Module Surface',     'Module Surface List',     'Module Surface Details'],
    'underground' => ['Module Underground', 'Module Underground List', 'Module Underground Details'],
];

page_start();
?>
<br>
<h2>CCD Quality Control</h2>
<table border="1" cellpadding="10" cellspacing="0" style="border-collapse:collapse; width:70%;">
<tr style="background-color:#ddd;">
  <th align="left" width="25%">Table</th>
  <th align="left" width="37%">Summary / List</th>
  <th align="left" width="38%">Detail</th>
</tr>
<?php foreach ($rows as $stage => [$label, $list_label, $details_label]): ?>
<tr>
  <td><strong><?= h($label) ?></strong></td>
  <td><a href="list.php?stage=<?= h($stage) ?>"><?= h($list_label) ?></a>
      <?php if (has_priv('full')): ?>&nbsp;&middot;&nbsp;<a href="export.php?stage=<?= h($stage) ?>" title="Download all values as CSV, with the old column names">CSV</a><?php endif ?></td>
  <td><?= has_priv('full') ? '<a href="details.php?stage=' . h($stage) . '">' . h($details_label) . '</a>' : '&mdash;' ?></td>
</tr>
<?php endforeach ?>
</table>
<br>
<?php page_end();
