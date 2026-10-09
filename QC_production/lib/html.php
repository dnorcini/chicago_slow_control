<?php
// lib/html.php
// Small HTML helpers shared by every page.

// Escape a value for HTML. Use it on everything that comes from the DB or the user.
function h($s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES);
}

// The hidden field every POST form needs (checked in bootstrap.php).
function csrf_field(): string {
    return '<input type="hidden" name="csrf" value="' . h(csrf_token()) . '">';
}

// <select> with one option per value; $options = ['', 'Science', 'Engineering', ...].
// A stored value that isn't in the list is added, so saving doesn't lose it.
function dropdown(string $name, array $options, $selected): string {
    if (trim((string)$selected) !== '' && !in_array((string)$selected, array_map('strval', $options), true))
        $options[] = $selected;
    $html = '<select name="' . h($name) . '">';
    foreach ($options as $v)
        $html .= '<option value="' . h($v) . '"' . ((string)$v === (string)$selected ? ' selected' : '') . '>' . h($v) . '</option>';
    return $html . '</select>';
}

// The input for one field or metric, from its definition in config/pages.php
// FIELDS or config/protocol.php METRICS: check box, dropdown, text area or text box
function input_for(string $name, array $def, ?string $value): string {
    $type = $def['type'] ?? '';
    if ($type === 'check' || !empty($def['check']))      // item check boxes post 0 when unticked
        return ($type === 'check' ? '' : '<input type="hidden" name="' . h($name) . '" value="0">')
             . '<input type="checkbox" name="' . h($name) . '" value="1"' . ($value ? ' checked' : '') . '>';
    $options = $def['options'] ?? ($type === 'yes_no' ? 'yes_no' : null);
    if ($options)
        return dropdown($name, OPTIONS[$options], $value);
    if (!empty($def['rows']))
        return '<textarea name="' . h($name) . '" rows="' . (int)$def['rows'] . '" style="width:100%">' . h($value) . '</textarea>';
    $size = empty($def['size']) ? '' : ' size="' . (int)$def['size'] . '"';
    return '<input type="' . ($def['input'] ?? 'text') . '" name="' . h($name) . '" value="' . h($value) . '"' . $size . '>';
}

function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

// Everything up to and including the nav bar.
function page_start(string $title = ''): void {
    echo '<!DOCTYPE html>', "\n";
    echo '<html><head><meta charset="utf-8">';
    echo '<title>' . h(SITE_TITLE . ' QC system' . ($title === '' ? '' : " - $title")) . '</title>';
    echo '<link rel="stylesheet" href="style.css?v=' . h(APP_VERSION) . '">';
    // Keep the scroll position when a form posts back to the same page (skipped if the URL has an #anchor)
    echo '<script>
    window.addEventListener("beforeunload", () => localStorage.setItem("scrollY", window.scrollY));
    window.addEventListener("load", () => {
        const y = localStorage.getItem("scrollY");
        if (!window.location.hash && y !== null) window.scrollTo(0, parseInt(y, 10));
    });
    </script>';
    echo '</head>', "\n";
    echo '<body bgcolor="' . WEB_BG_COLOUR . '" text="' . WEB_TEXT_COLOUR . '">', "\n";
    require __DIR__ . '/../views/header.php';
}

// The footer (version, PHP, database), then the end of the page.
function page_end(): void {
    echo "\n" . '<div class="footer">' . h(SITE_TITLE . ' QC system ' . APP_VERSION) . ' &middot; PHP ' . h(PHP_VERSION)
       . ' &middot; database ' . h(db_settings()['database']) . '</div>';
    echo "\n</body></html>\n";
}
