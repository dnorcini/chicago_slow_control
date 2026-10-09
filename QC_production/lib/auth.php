<?php
// lib/auth.php
// Who is logged in, and what they may see.
// A user's privileges are a comma list in users.privileges: basic, full, admin
// (and guest for the guest account). The session keeps user_name and
// privileges, the same keys the old pages use.

function current_user(): string {
    return $_SESSION['user_name'] ?? '';
}

function is_guest(): bool {
    return current_user() === 'guest';
}

// 'guest' means anyone: every visitor is at least logged in as guest.
function has_priv(string $priv): bool {
    if ($priv === 'guest')
        return true;
    $have = array_map('trim', explode(',', $_SESSION['privileges'] ?? ''));
    return in_array($priv, $have, true);
}

// At the top of a page: require_priv('full');
function require_priv(string $priv): void {
    if (has_priv($priv))
        return;
    page_start();
    echo '<br><br>You do not have clearance to view this page.<br>';
    page_end();
    exit;
}

// Whether $password matches a stored hash: password_hash(), or an old MD5 hash
function password_ok(?string $hash, string $password): bool {
    $hash = (string)$hash;
    if (preg_match('/^[0-9a-f]{32}$/', $hash))                // old MD5 hash
        return hash_equals($hash, md5($password));
    return $hash !== '' && password_verify($password, $hash);
}

// Checks the password and logs the user in. Old MD5 hashes are replaced by
// password_hash() the first time the user logs in with them.
function login(string $user_name, string $password): bool {
    $u = one('SELECT user_name, password, privileges FROM users WHERE user_name = ?', [$user_name]);
    if (!$u || !password_ok($u['password'], $password))
        return false;
    if (preg_match('/^[0-9a-f]{32}$/', (string)$u['password']))
        q('UPDATE users SET password = ? WHERE user_name = ?',
          [password_hash($password, PASSWORD_DEFAULT), $u['user_name']]);
    session_regenerate_id(true);
    $_SESSION['user_name']  = $u['user_name'];
    $_SESSION['privileges'] = (string)$u['privileges'];
    return true;
}

function login_guest(): void {
    if (!login('guest', 'guest'))
        exit('The guest account seems to be broken. Please contact the administrator.');
}

function logout(): void {
    $_SESSION = [];
    session_regenerate_id(true);
}

// One token per session, sent back by every POST form (csrf_field()).
function csrf_token(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_check(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST')
        return;
    if (empty($_POST) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        http_response_code(413);
        exit('The upload is too large (limit: ' . ini_get('post_max_size') . '). Go back and try a smaller file.');
    }
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('This form has expired. Go back, reload the page and try again.');
    }
}
