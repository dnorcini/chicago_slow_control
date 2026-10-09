<?php
// lib/history.php
// The history of an item (moves, packaging, shipping ...): table history, with
// type = ccd | die | module and sub_id = the item id.

function history_rows(string $type, int $id): array {
    return all("SELECT * FROM history WHERE type = ? AND sub_id = ?
                ORDER BY (date IS NULL OR date = '') ASC, date ASC, entry ASC", [$type, $id]);
}

// Location of the latest entry (entries without a date count as the latest)
function current_location(string $type, int $id): string {
    $r = one("SELECT location FROM history WHERE type = ? AND sub_id = ?
              ORDER BY (date IS NULL OR date = '') DESC, date DESC, entry DESC LIMIT 1", [$type, $id]);
    return (string)($r['location'] ?? '');
}

// Adds an entry ($entry = 0) or updates one of this item's entries.
// $h = ['date' => ..., 'action' => ..., 'location' => ..., 'reviewer' => ...]
// Returns messages for the user (nothing saved if there are any).
function history_save(string $type, int $id, int $entry, array $h): array {
    $v = [];
    foreach (['date', 'action', 'location', 'reviewer'] as $k) {
        $s = trim((string)($h[$k] ?? ''));
        $v[] = $s === '' ? null : $s;
    }
    if ($v[0] !== null && !(preg_match('/^(\d{4})-(\d\d)-(\d\d)$/', $v[0], $d) && checkdate($d[2], $d[3], $d[1])))
        return ["History date \"{$v[0]}\" is not a date (YYYY-MM-DD), not saved."];
    if ($entry === 0 && count(array_filter($v)) === 0)
        return [];
    if ($entry === 0)
        q('INSERT INTO history (type, sub_id, date, action, location, reviewer) VALUES (?, ?, ?, ?, ?, ?)',
          [$type, $id, ...$v]);
    else
        q('UPDATE history SET date = ?, action = ?, location = ?, reviewer = ? WHERE entry = ? AND type = ? AND sub_id = ?',
          [...$v, $entry, $type, $id]);
    return [];
}
