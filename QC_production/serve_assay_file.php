<?php
// serve_assay_file.php
// Serve uploaded assay attachments (any type). One URL: preview (inline); browser decides display vs download.
// Requires: db_login.php. Do NOT include page_setup.php or anything that echoes HTML.

session_start();
include("db_login.php");
mysql_select_db('assay_qc');

if (!isset($_GET['id'])) {
    http_response_code(400);
    exit("Missing id");
}
$file_id = (int)$_GET['id'];

// Preview = inline (browser may display or prompt download depending on type)
$disposition = (isset($_GET['disposition']) && $_GET['disposition'] === 'inline') ? 'inline' : 'attachment';

$q = "
  SELECT `ID`,`Assay_ID`,`Orig_Name`,`Stored_Name`,`Ext`,`Mime`,`Size_Bytes`,`Uploaded_At`
  FROM `assay_files`
  WHERE `ID` = $file_id
  LIMIT 1
";
$r = mysql_query($q);
if (!$r) {
    http_response_code(500);
    exit("DB error");
}
if (mysql_num_rows($r) !== 1) {
    http_response_code(404);
    exit("Not found");
}
$f = mysql_fetch_assoc($r);

$upload_dir = "/var/www/html/QC_production/uploads/edit_assay/";
$stored = (string)$f['Stored_Name'];

if ($stored === '' || $stored !== basename($stored) || strpos($stored, "\0") !== false) {
    http_response_code(400);
    exit("Bad stored filename");
}
if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9_.-]{0,250}$/', $stored)) {
    http_response_code(400);
    exit("Bad stored filename");
}

$path = $upload_dir . $stored;
if (!is_file($path)) {
    http_response_code(404);
    exit("File missing on disk");
}

// MIME: use file content when available, else fallback (do not trust browser-provided MIME from upload)
$content_type = 'application/octet-stream';
if (function_exists('finfo_open')) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    if ($finfo) {
        $detected = finfo_file($finfo, $path);
        finfo_close($finfo);
        if ($detected && preg_match('/^[a-zA-Z0-9][a-zA-Z0-9.+-]*\/[a-zA-Z0-9.+-]+$/', $detected)) {
            $content_type = $detected;
        }
    }
}

while (ob_get_level()) {
    ob_end_clean();
}

$orig = trim((string)$f['Orig_Name']);
$download_name = ($orig !== '') ? $orig : $stored;
$download_name = str_replace(array("\r", "\n"), ' ', $download_name);
$download_name = str_replace('"', "'", $download_name);

header('X-Content-Type-Options: nosniff');

header('Cache-Control: private, max-age=0, no-cache, no-store, must-revalidate');
header('Pragma: no-cache');

header('Content-Type: ' . $content_type);
header('Content-Disposition: ' . $disposition . '; filename="' . $download_name . '"');
header('Content-Length: ' . filesize($path));

readfile($path);
exit;
