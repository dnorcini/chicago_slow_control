<?php
// Shim layer: ext/mysql was removed in PHP 7. This file re-implements the
// small subset of mysql_*() functions this app calls, backed by mysqli,
// so the ~70 call sites across QC_production don't need to change
// yet. It mirrors the old implicit-single-connection behavior: whichever
// link mysql_connect() last opened is used by every other mysql_*() call.
// A real conversion to mysqli_*()/PDO with per-call $link args is left
// for a later refactor pass.

if (!defined('MYSQL_ASSOC')) {
    define('MYSQL_ASSOC', MYSQLI_ASSOC);
    define('MYSQL_NUM', MYSQLI_NUM);
    define('MYSQL_BOTH', MYSQLI_BOTH);
}

if (!function_exists('mysql_connect')) {
    mysqli_report(MYSQLI_REPORT_OFF); // restore old "return false on error" behavior

    function mysql_connect($host, $user, $password) {
        global $__mysql_compat_link;
        $__mysql_compat_link = mysqli_connect($host, $user, $password);
        if ($__mysql_compat_link) {
            // Old ext/mysql and this server both negotiated utf8mb4 by default, but
            // almost every table/column here is declared latin1. Forcing latin1 makes
            // mysqli skip server-side transcoding, matching the old byte-for-byte
            // read/write behavior these tables' existing data already relies on
            // (otherwise UTF-8 names like "Pérez" come back double-mangled as "PÃ©rez").
            mysqli_set_charset($__mysql_compat_link, 'latin1');
        }
        return $__mysql_compat_link;
    }

    function mysql_select_db($db) {
        global $__mysql_compat_link;
        return mysqli_select_db($__mysql_compat_link, $db);
    }

    function mysql_error() {
        global $__mysql_compat_link;
        return mysqli_error($__mysql_compat_link);
    }

    function mysql_query($query) {
        global $__mysql_compat_link;
        return mysqli_query($__mysql_compat_link, $query);
    }

    function mysql_fetch_array($result, $type = MYSQLI_BOTH) {
        return mysqli_fetch_array($result, $type);
    }

    function mysql_fetch_assoc($result) {
        return mysqli_fetch_assoc($result);
    }

    function mysql_fetch_row($result) {
        return mysqli_fetch_row($result);
    }

    function mysql_num_rows($result) {
        return mysqli_num_rows($result);
    }

    function mysql_real_escape_string($str) {
        global $__mysql_compat_link;
        return mysqli_real_escape_string($__mysql_compat_link, $str);
    }

    function mysql_insert_id() {
        global $__mysql_compat_link;
        return mysqli_insert_id($__mysql_compat_link);
    }

    function mysql_close() {
        global $__mysql_compat_link;
        return mysqli_close($__mysql_compat_link);
    }
}
