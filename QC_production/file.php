<?php
// file.php
// Serves an uploaded file of a details page, for logged-in users only:
//   file.php?stage=die&id=5&slot=image1_file
// Only slots listed in config/pages.php can be asked for.
require __DIR__ . '/bootstrap.php';
require_priv('full');

$stage = stage_or_404($_GET['stage'] ?? '');
$id = (int)($_GET['id'] ?? 0);
$slot = (string)($_GET['slot'] ?? '');
$known = [];
foreach (PAGES[$stage]['blocks'] as $b)
    $known += block_slots($stage, $b);
$path = isset($known[$slot]) ? find_upload($stage, $id, $slot) : null;
if ($path === null) {
    http_response_code(404);
    exit('No such file.');
}

$ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
$type = match ($ext) {
    'png' => 'image/png', 'jpg', 'jpeg' => 'image/jpeg', 'pdf' => 'application/pdf',
    'log' => 'text/plain; charset=utf-8', default => 'application/octet-stream',
};
header('Content-Type: ' . $type);
header('Content-Disposition: ' . ($type === 'application/octet-stream' ? 'attachment' : 'inline')
       . '; filename="' . $slot . '.' . $ext . '"');
header('Content-Length: ' . filesize($path));
header('X-Content-Type-Options: nosniff');
readfile($path);
