<?php
// users.php
// The list of users. Everyone with 'full' can see it and edit themselves;
// admins can edit, add and delete users.
// From the CLEAN slow control, James Nikkel, Yale University, 2006.
require __DIR__ . '/bootstrap.php';
require_priv('full');

$admin = has_priv('admin');
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $admin) {
    $del = (string)($_POST['del_user'] ?? '');
    if (isset($_POST['really_del'])) {
        if ($del === current_user() || $del === 'guest')
            $msg = "The account $del can't be deleted here.";
        else {
            delete_user($del);
            $msg = "User $del deleted.";
        }
        $_SESSION['flash_users'] = $msg;
        redirect('users.php');
    }
}
$confirm = $admin && $_SERVER['REQUEST_METHOD'] === 'POST' ? (string)($_POST['del_user'] ?? '') : '';
$msg = $_SESSION['flash_users'] ?? '';
unset($_SESSION['flash_users']);

page_start('Users');
?>
<br>
<?php if ($msg !== ''): ?><p><?= h($msg) ?></p><?php endif ?>
<?php if ($confirm !== ''): ?>
<table border="0" cellpadding="2"><tr>
  <th align="left"><form action="users.php" method="post"><?= csrf_field() ?>
      <input type="hidden" name="del_user" value="<?= h($confirm) ?>">
      Really delete user: <?= h($confirm) ?>? <input type="submit" name="really_del" value="Yes"></form></th>
  <th align="center"><form action="users.php" method="get"><input type="submit" value="No"></form></th>
</tr></table>
<?php endif ?>

<br>
<table border="1" cellpadding="4" cellspacing="2">
  <tr><th align="left">Proper name</th><th align="left">Username</th><th align="left">Affiliation</th>
      <th align="left">Email address</th><th align="left">Edit</th><?php if ($admin): ?><th align="left">Delete</th><?php endif ?></tr>
<?php foreach (all_users() as $u): ?>
  <tr>
    <td align="left"><?= h($u['full_name']) ?></td>
    <td align="left"><?= h($u['user_name']) ?></td>
    <td align="left"><?= h($u['affiliation']) ?></td>
    <td align="left"><?php if (trim((string)$u['email']) !== ''): ?><a href="mailto:<?= h($u['email']) ?>"><?= h($u['email']) ?></a><?php endif ?></td>
    <td align="center"><?php if ($admin || $u['user_name'] === current_user()): ?>
        <a href="<?= h('edit_user.php?user=' . urlencode($u['user_name'])) ?>"><img src="pixmaps/edit.png" title="Edit User Information" alt="Edit" border="0"></a><?php endif ?></td>
<?php if ($admin): ?>
    <td align="center"><form action="users.php" method="post"><?= csrf_field() ?>
        <input type="hidden" name="del_user" value="<?= h($u['user_name']) ?>">
        <input type="image" src="pixmaps/drop.png" title="Delete User"></form></td>
<?php endif ?>
  </tr>
<?php endforeach ?>
</table>

<?php if ($admin): ?>
<br>
<form action="edit_user.php" method="post"><?= csrf_field() ?>
  Add new user: <input type="text" name="new_user_name" size="32">
  <button type="submit">New</button>
</form>
<?php endif ?>
<?php page_end();
