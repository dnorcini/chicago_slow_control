<?php
// lib/users.php
// The users table, for users.php and edit_user.php. Passwords are stored with
// password_hash(); old MD5 hashes still work (lib/auth.php password_ok()).

function all_users(): array {
    return all('SELECT user_name, full_name, affiliation, email, privileges FROM users ORDER BY affiliation, user_name');
}

function find_user(string $user_name): ?array {
    return one('SELECT * FROM users WHERE user_name = ?', [$user_name]);
}

// guest, basic, full, admin
function privilege_names(): array {
    return array_column(all('SELECT DISTINCT name FROM user_privileges ORDER BY name'), 'name');
}

// Returns a message, or '' when the user was added
function add_user(string $user_name): string {
    if (trim($user_name) === '' || $user_name !== trim($user_name) || strlen($user_name) > 32)
        return 'A user name must be 1 to 32 characters, without spaces at the ends.';
    if (find_user($user_name))
        return "User $user_name already exists.";
    q('INSERT INTO users (user_name) VALUES (?)', [$user_name]);
    return '';
}

// $fields: full_name, affiliation, email, privileges (only the keys given are
// changed; blank = NULL); $password: new password or '' (unchanged)
function update_user(string $user_name, array $fields, string $password): void {
    foreach ($fields as $col => $v)
        q("UPDATE users SET `$col` = ? WHERE user_name = ?", [trim((string)$v) === '' ? null : $v, $user_name]);
    if ($password !== '')
        q('UPDATE users SET password = ? WHERE user_name = ?', [password_hash($password, PASSWORD_DEFAULT), $user_name]);
}

function delete_user(string $user_name): void {
    q('DELETE FROM users WHERE user_name = ?', [$user_name]);
}
