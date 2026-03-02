<?php
// get_inst_log.php
// Read last N lines from /dev/shm/<stream>.<instrument> (stdout or stderr).
// Must be included after get_inst_info.php; expects $inst_names, and receives
// $instrument (whitelisted name), $stream ('stdout' or 'stderr'), $lines (int, capped).
// Sets $inst_log_content (string) and $inst_log_error (string or null).

$inst_log_content = '';
$inst_log_error = null;

if (!isset($inst_names) || !is_array($inst_names)) {
    $inst_log_error = 'Instrument list not available.';
    return;
}

$lines = isset($lines) ? (int)$lines : 100;
$lines = min(max(1, $lines), 5000);

if (!isset($instrument) || !in_array($instrument, $inst_names, true)) {
    $inst_log_error = 'Invalid or missing instrument.';
    return;
}

if (!isset($stream) || ($stream !== 'stdout' && $stream !== 'stderr')) {
    $inst_log_error = 'Invalid stream.';
    return;
}

$path = '/dev/shm/' . $stream . '.' . $instrument;

if (!file_exists($path)) {
    $inst_log_error = 'No ' . $stream . ' file for this instrument.';
    return;
}

$resolved = @realpath($path);
if ($resolved !== false && strpos($resolved, '/dev/shm/') !== 0) {
    $inst_log_error = 'Path not under /dev/shm.';
    return;
}

if (!is_readable($path)) {
    $inst_log_error = 'File not readable.';
    return;
}

/**
 * Read last N lines from a file (PHP only, no shell).
 * Wrapped in function_exists so this file can be included twice without redeclare (PHP 5.6).
 */
if (!function_exists('_read_last_n_lines')) {
function _read_last_n_lines($path, $n) {
    $fp = @fopen($path, 'r');
    if (!$fp) return null;
    $size = @filesize($path);
    if ($size === 0) {
        fclose($fp);
        return '';
    }
    $buf_size = 8192;
    $pos = max(0, $size - $buf_size);
    $data = '';
    while (true) {
        @fseek($fp, $pos);
        $chunk = @fread($fp, $buf_size);
        if ($chunk === false) {
            fclose($fp);
            return null;
        }
        $data = $chunk . $data;
        $line_count = substr_count($data, "\n");
        if ($line_count >= $n || $pos <= 0) break;
        $pos = max(0, $pos - $buf_size);
    }
    fclose($fp);
    $arr = explode("\n", $data);
    $last = array_slice($arr, -$n);
    return implode("\n", $last);
}
}

$inst_log_content = _read_last_n_lines($path, $lines);
if ($inst_log_content === null) {
    $inst_log_error = 'Could not read file.';
}
