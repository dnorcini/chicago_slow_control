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

// <select> with one option per value; $options = ['', 'Science', 'Engineering', ...]
function dropdown(string $name, array $options, $selected): string {
    $html = '<select name="' . h($name) . '">';
    foreach ($options as $v)
        $html .= '<option value="' . h($v) . '"' . ((string)$v === (string)$selected ? ' selected' : '') . '>' . h($v) . '</option>';
    return $html . '</select>';
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

function page_end(): void {
    echo "\n</body></html>\n";
}
