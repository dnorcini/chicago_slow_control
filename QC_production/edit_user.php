<?php
// edit_user.php
// Edit one user: edit_user.php?user=NAME (default: yourself). Non-admins can
// only edit themselves and change their own password (old + new twice);
// admins can edit anyone, set a new password directly and change privileges.
// Adding a user (admin, from users.php) creates it and opens it here.
// From the astro slow control, James Nikkel, Yale University, 2006, 2010.
require __DIR__ . '/bootstrap.php';
require_priv('full');

$admin = has_priv('admin');
$edit_user = $admin ? (string)($_GET['user'] ?? current_user()) : current_user();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_user_name']) && $admin) {
    $new = (string)$_POST['new_user_name'];
    $err = add_user($new);
    if ($err === '')
        redirect('edit_user.php?user=' . urlencode($new));
    $_SESSION['flash_users'] = $err;
    redirect('users.php');
}

$u = find_user($edit_user);
if (!$u) {
    http_response_code(404);
    page_start('Users');
    echo '<br><p>There is no user ' . h($edit_user) . '.</p>';
    page_end();
    exit;
}
$self = $edit_user === current_user();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change'])) {
    $fields = ['full_name' => $_POST['realname'] ?? '', 'affiliation' => $_POST['affiliation'] ?? '',
               'email' => $_POST['email'] ?? ''];
    $password = '';
    if ($self) {                              // own password: old one, then the new one twice
        $old = (string)($_POST['old_password'] ?? '');
        $new1 = (string)($_POST['new_password1'] ?? '');
        if ($old !== '' || $new1 !== '') {
            if (!password_ok($u['password'], $old))
                $errors[] = 'Old password incorrect.';
            elseif ($new1 === '' || $new1 !== (string)($_POST['new_password2'] ?? ''))
                $errors[] = 'The new passwords are empty or not identical.';
            else
                $password = $new1;
        }
    } else {                                  // admin setting someone else's password
        $password = (string)($_POST['password'] ?? '');
    }
    if ($admin) {
        $known = privilege_names();
        $fields['privileges'] = implode(',', array_values(array_intersect($known, (array)($_POST['privileges'] ?? []))));
    }
    if (!$errors) {
        update_user($edit_user, $fields, $password);
        if ($self && $admin)                  // your own privileges change right away
            $_SESSION['privileges'] = (string)find_user($edit_user)['privileges'];
        redirect('users.php');
    }
}

$have = array_map('trim', explode(',', (string)$u['privileges']));
page_start('Users');
?>
<br>
<?php foreach ($errors as $e): ?><p style="color:red"><?= h($e) ?> Nothing was saved.</p><?php endforeach ?>
<form action="<?= h('edit_user.php?user=' . urlencode($edit_user)) ?>" method="post"><?= csrf_field() ?>
<table border="1" cellpadding="4" cellspacing="2">
  <tr><th align="left">User</th><th align="left">Full Name</th><th align="left">Affiliation</th><th align="left">Password</th>
      <th align="left">Email address</th><?php if ($admin): ?><th align="left">Privileges</th><?php endif ?><th></th></tr>
  <tr>
    <td align="left"><?= h($edit_user) ?></td>
    <td align="left"><input type="text" name="realname" value="<?= h($u['full_name']) ?>" size="16" autocomplete="off"></td>
    <td align="left"><input type="text" name="affiliation" value="<?= h($u['affiliation']) ?>" size="16" autocomplete="off"></td>
<?php if ($self): ?>
    <th align="left">Old: <input type="password" name="old_password" size="12" autocomplete="off"><br>
        New: <input type="password" name="new_password1" size="12" autocomplete="new-password"><br>
        New: <input type="password" name="new_password2" size="12" autocomplete="new-password"></th>
<?php else: ?>
    <th align="left"><input type="password" name="password" size="12" autocomplete="new-password"
        title="Leave empty to keep the current password"></th>
<?php endif ?>
    <td align="left"><input type="text" name="email" value="<?= h($u['email']) ?>" size="16" autocomplete="off"></td>
<?php if ($admin): ?>
    <td align="left"><select name="privileges[]" style="font-size: 12pt" multiple size="4">
<?php foreach (privilege_names() as $p): ?>
        <option value="<?= h($p) ?>"<?= in_array($p, $have, true) ? ' selected' : '' ?>><?= h($p) ?></option>
<?php endforeach ?>
    </select></td>
<?php endif ?>
    <td align="left"><input type="submit" name="change" value="Change"></td>
  </tr>
</table>
</form>
<?php page_end();
