<?php
// bootstrap.php
// Every page starts with:
//   require __DIR__ . '/bootstrap.php';
//   require_priv('basic');                 // guest | basic | full | admin
// This loads the config and the lib files, installs the error handler, starts
// the session (as the guest user if nobody is logged in), checks the CSRF
// token on every POST and handles the login/logout box.

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/lib/db.php';
require_once __DIR__ . '/lib/auth.php';
require_once __DIR__ . '/lib/html.php';
require_once __DIR__ . '/lib/protocol.php';

date_default_timezone_set(TIMEZONE);

// Uncaught errors: the details go to the server's error log, the user gets a
// short message with a reference to find them there. In dev (display_errors
// on) the details are shown on the page too.
set_exception_handler(function (Throwable $e) {
    if (PHP_SAPI === 'cli') {
        fwrite(STDERR, "$e\n");
        exit(1);
    }
    $ref = bin2hex(random_bytes(3));
    error_log("ccdqc ref #$ref: $e");
    if (!headers_sent())
        http_response_code(500);
    echo '<p><b>Something went wrong</b> (ref #' . $ref . '). Please tell an admin.</p>';
    if (ini_get('display_errors'))
        echo '<pre>' . h($e) . '</pre>';
});

if (PHP_SAPI === 'cli')                       // command-line scripts: no session, no login
    return;

session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
csrf_check();

if (isset($_POST['login'])) {
    if (!login((string)($_POST['user_name'] ?? ''), (string)($_POST['password'] ?? '')))
        $_SESSION['flash'] = 'Username or password incorrect.';
    redirect($_SERVER['REQUEST_URI']);
}
if (isset($_POST['logout'])) {
    logout();
    redirect($_SERVER['REQUEST_URI']);
}
if (current_user() === '')
    login_guest();
