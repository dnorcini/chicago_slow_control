<?php
// lib/uploads.php
// Uploaded files stay on disk where the old pages put them:
//   UPLOAD_DIR/<stage 'upload_dir', e.g. edit_die/die_5>/<slot>.<ext>
// A slot "has a file" when such a file exists. There is no DB table for files.

// Allowed extensions of a slot, by its name (see config/pages.php 'files')
function slot_exts(string $slot): array {
    if (str_ends_with($slot, '_log')) return ['log'];
    if (str_starts_with($slot, 'seq_')) return ['seq'];
    if (str_starts_with($slot, 'bcf_')) return ['bcf'];
    return ['png', 'jpg', 'jpeg', 'pdf'];
}

function upload_folder(string $stage, int $id): string {
    return UPLOAD_DIR . '/' . sprintf(STAGES[$stage]['upload_dir'], $id);
}

// Path of the slot's file, or null. A slot name is only ever taken from config/pages.php.
function find_upload(string $stage, int $id, string $slot): ?string {
    if (!isset(STAGES[$stage]['upload_dir']))
        return null;
    foreach (slot_exts($slot) as $ext)
        foreach ([$ext, strtoupper($ext)] as $e)
            if (is_file($path = upload_folder($stage, $id) . "/$slot.$e"))
                return $path;
    return null;
}

// Every upload slot of a block: its own 'files', its tables' 'files', and the
// per-position 'pos_files' (slot prefix_A, prefix_B, ...)
function block_slots(string $stage, array $block): array {
    $slots = $block['files'] ?? [];
    if (isset($block['grid']))
        foreach (block_tables($stage, $block) as $t)
            $slots += $t['files'];
    foreach ($block['pos_files'] ?? [] as $prefix => $label)
        foreach (STAGES[$stage]['positions'] as $p)
            $slots["{$prefix}_$p"] = "$label $p";
    return $slots;
}

// Saves the files posted as f[slot] for the given slots. Returns messages.
function save_uploads(string $stage, int $id, array $slots, array $files): array {
    $msgs = [];
    foreach ($slots as $slot => $label) {
        $err = $files['error'][$slot] ?? UPLOAD_ERR_NO_FILE;
        if ($err === UPLOAD_ERR_NO_FILE)
            continue;
        $name = $files['name'][$slot];
        if ($err !== UPLOAD_ERR_OK) {
            $msgs[] = "$label: upload of \"$name\" failed (error $err).";
            continue;
        }
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if (!in_array($ext, slot_exts($slot), true)) {
            $msgs[] = "$label: \"$name\" is not a ." . implode('/.', slot_exts($slot)) . ' file, not saved.';
            continue;
        }
        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($files['tmp_name'][$slot]);
        $ok = match ($ext) {
            'png'         => $mime === 'image/png',
            'jpg', 'jpeg' => $mime === 'image/jpeg',
            'pdf'         => $mime === 'application/pdf',
            default       => !str_starts_with($mime, 'image/') && $mime !== 'text/html',
        };
        if (!$ok) {
            $msgs[] = "$label: \"$name\" does not look like a .$ext file ($mime), not saved.";
            continue;
        }
        $dir = upload_folder($stage, $id);
        if (!is_dir($dir) && !mkdir($dir, 0750, true))
            throw new RuntimeException("Could not create $dir");
        while ($old = find_upload($stage, $id, $slot))   // the slot keeps one file, whatever its extension
            unlink($old);
        if (!move_uploaded_file($files['tmp_name'][$slot], "$dir/$slot.$ext"))
            throw new RuntimeException("Could not save $dir/$slot.$ext");
    }
    return $msgs;
}
