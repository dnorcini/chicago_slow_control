<?php
// aux/inst_log_lib.php
// PHP 5.6 compatible helper library for instrument logs.

/**
 * Sanitize log content for safe HTML display when file may contain null bytes
 * or binary/non-UTF-8 data (avoids blank output or truncation with htmlspecialchars).
 */
function sc_sanitize_log_for_display($str) {
    if ($str === '' || $str === null) return '';
    $len = strlen($str);
    $out = '';
    for ($i = 0; $i < $len; $i++) {
        $ord = ord($str[$i]);
        if ($ord === 0) {
            $out .= '[NUL]';
        } elseif ($ord >= 32 && $ord <= 126) {
            $out .= $str[$i];
        } elseif ($ord === 9 || $ord === 10 || $ord === 13) {
            $out .= $str[$i];
        } elseif ($ord >= 128) {
            $out .= '.';
        } else {
            $out .= '[0x' . str_pad(dechex($ord), 2, '0', STR_PAD_LEFT) . ']';
        }
    }
    return $out;
}

function sc_read_inst_log($instrument, $stream, $lines, $inst_names) {
    $out = array('content' => '', 'error' => null, 'path' => null);

    if (!is_array($inst_names)) {
        $out['error'] = 'Instrument list not available.';
        return $out;
    }

    $lines = (int)$lines;
    if ($lines < 1) $lines = 1;
    if ($lines > 5000) $lines = 5000;

    if (!$instrument || !in_array($instrument, $inst_names, true)) {
        $out['error'] = 'Invalid or missing instrument.';
        return $out;
    }

    if ($stream !== 'stdout' && $stream !== 'stderr') {
        $out['error'] = 'Invalid stream.';
        return $out;
    }

    $path = '/dev/shm/' . $stream . '.' . $instrument;
    $out['path'] = $path;

    clearstatcache(true, $path);

    if (!file_exists($path)) {
        $out['error'] = 'No ' . $stream . ' file for this instrument.';
        return $out;
    }

    $resolved = @realpath($path);
    if ($resolved !== false && strpos($resolved, '/dev/shm/') !== 0) {
        $out['error'] = 'Path not under /dev/shm.';
        return $out;
    }

    if (!is_readable($path)) {
        $out['error'] = 'File not readable.';
        return $out;
    }

    $content = sc_tail_file($path, $lines);
    if ($content === null) {
        $out['error'] = 'Could not read file.';
        return $out;
    }

    $out['content'] = sc_sanitize_log_for_display($content);
    return $out;
}

function sc_tail_file($path, $n) {
    // Regular-file tail using fseek. Works for filetype=file.
    $fp = @fopen($path, 'r');
    if (!$fp) return null;

    clearstatcache(true, $path);
    $size = @filesize($path);

    if ($size === false || $size <= 0) {
        fclose($fp);
        return '';
    }

    $buf_size = 8192;
    $pos = $size - $buf_size;
    if ($pos < 0) $pos = 0;

    $data = '';
    while (true) {
        @fseek($fp, $pos);
        $chunk = @fread($fp, $buf_size);
        if ($chunk === false) {
            fclose($fp);
            return null;
        }
        $data = $chunk . $data;

        if (substr_count($data, "\n") >= $n || $pos === 0) break;

        $pos -= $buf_size;
        if ($pos < 0) $pos = 0;
    }
    fclose($fp);

    $arr = explode("\n", $data);
    $last = array_slice($arr, -$n);
    return implode("\n", $last);
}