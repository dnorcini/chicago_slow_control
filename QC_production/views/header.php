<?php
// views/header.php
// Nav bar and login box, shown at the top of every page (by page_start()).
// From the old header.php: James Nikkel (Yale, 2016), D. Norcini (UChicago, 2020),
// Cinyu Zhu (JHU, 2025).

$here = $_SERVER['REQUEST_URI'] ?? '';
$nav = [                                      // label => [page, privilege needed, pages that belong to it]
    'CCD Quality Control' => ['summary.php', 'basic', ['summary.php', 'list.php', 'details.php']],
    'MATERIAL ASSAYS'     => ['list_material_assays.php', 'basic', ['list_material_assays.php', 'edit_materials.php']],
    'Users'               => ['users.php', 'full', ['users.php', 'edit_user.php']],
];
?>
<table class="nav" border="0" cellpadding="2" width="100%">
<tr valign="center">
  <th align="left" width="25"><a href="<?= h($here) ?>"><img src="pixmaps/reload.png" alt="Refresh" title="Refresh page" border="0"></a></th>
<?php foreach ($nav as $label => [$page, $priv, $pages]):
    if (!has_priv($priv)) continue; ?>
  <th><a href="<?= h($page) ?>"<?= in_array(basename($_SERVER['PHP_SELF']), $pages, true) ? ' class="current"' : '' ?>><?= h($label) ?></a></th>
<?php endforeach ?>
  <th align="right">
    You are logged in as <?= h(current_user()) ?> from <?= h($_SERVER['REMOTE_ADDR'] ?? '') ?>.
<?php if (!empty($_SESSION['flash'])): ?>
    <br><span style="color:red"><?= h($_SESSION['flash']) ?></span>
<?php unset($_SESSION['flash']); endif ?>
  </th>
<?php if (is_guest()): ?>
  <form action="<?= h($here) ?>" method="post">
  <?= csrf_field() ?><input type="hidden" name="login" value="1">
  <th width="200">Username: <input type="text" name="user_name" size="10"></th>
  <th width="168">Password: <input type="password" name="password" size="10"></th>
  <th width="20"><input type="image" src="pixmaps/login.png" alt="Log in" title="Log in"></th>
  </form>
<?php else: ?>
  <th>
  <form action="<?= h($here) ?>" method="post">
  <?= csrf_field() ?><input type="hidden" name="logout" value="1">
  <input type="image" src="pixmaps/logout.png" alt="Log out" title="Log out">
  </form>
  </th>
<?php endif ?>
</tr>
</table>
