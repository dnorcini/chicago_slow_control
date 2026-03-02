<?php
  // userguide.php – front-end page for User Guide, content from userguide.md
  // Renders markdown on each request so edits to userguide.md are reflected immediately.
  // Sidebar shows a table of contents from ## and ### headings.

session_start();
$req_priv = "full";
$never_ref = 1;
include("db_login.php");
include("slow_control_page_setup.php");

$md_file = __DIR__ . '/userguide.md';
if (!is_readable($md_file)) {
    echo '<p>User guide (userguide.md) is not available.</p>';
    echo('</body></html>');
    exit;
}

$markdown = file_get_contents($md_file);

/**
 * Make an HTML-safe id slug from a heading line (for anchor links).
 */
function heading_to_id($text) {
    $t = trim($text);
    $t = preg_replace('/[^\p{L}\p{N}\s\-]/u', '', $t);
    $t = preg_replace('/\s+/', '-', $t);
    $t = strtolower($t);
    return $t ?: 'section';
}

/**
 * Extract table-of-contents from markdown: ## and ### lines only.
 * Returns array of [ level => 2|3, text => string, id => string ]
 */
function extract_toc($md) {
    $toc = [];
    $lines = preg_split('/\r\n|\r|\n/', $md);
    foreach ($lines as $line) {
        if (preg_match('/^(#{2,3})\s+(.+)$/', $line, $m)) {
            $level = strlen($m[1]);
            $text = trim($m[2]);
            $text = preg_replace('/\*+/', '', $text); // strip bold markers
            $id = heading_to_id($text);
            $toc[] = ['level' => $level, 'text' => $text, 'id' => $id];
        }
    }
    return $toc;
}

/**
 * Simple markdown-to-HTML conversion for this guide (headings, bold, code, lists, blockquote).
 */
function markdown_to_html($md) {
    $lines = preg_split('/\r\n|\r|\n/', $md);
    $out = '';
    $in_list = false;
    $list_level = -1;  // current nesting depth (0 = top-level ul)
    $in_blockquote = false;
    $buffer = '';

    $flush_para = function () use (&$buffer, &$out) {
        if ($buffer !== '') {
            $out .= '<p>' . $buffer . "</p>\n";
            $buffer = '';
        }
    };

    // Close all open list items and uls down to level 0, then close the list.
    $close_list = function () use (&$out, &$in_list, &$list_level) {
        if (!$in_list) return;
        $out .= "</li>\n";
        for ($i = 0; $i < $list_level; $i++) {
            $out .= "</ul>\n";
        }
        $out .= "</ul>\n";
        $in_list = false;
        $list_level = -1;
    };

    $render_inline = function ($s) {
        $s = htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
        $s = preg_replace('/\*\*\*\*(.+?)\*\*\*\*/s', '<strong>$1</strong>', $s);
        $s = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $s);
        $s = preg_replace('/`([^`]+)`/', '<code>$1</code>', $s);
        return $s;
    };

    foreach ($lines as $i => $line) {
        $trimmed = trim($line);

        if (preg_match('/^(#{1,3})\s+(.+)$/', $line, $m)) {
            $flush_para();
            $close_list();
            if ($in_blockquote) {
                $out .= "</blockquote>\n";
                $in_blockquote = false;
            }
            $lvl = strlen($m[1]);
            $text = $m[2];
            $text = preg_replace('/\*+/', '', $text);
            $id = heading_to_id($text);
            $text = $render_inline($m[2]);
            $out .= "<h{$lvl} id=\"" . htmlspecialchars($id) . "\">{$text}</h{$lvl}>\n";
            continue;
        }

        if (preg_match('/^>\s?(.*)$/', $line, $m)) {
            $flush_para();
            $close_list();
            if (!$in_blockquote) {
                $out .= '<blockquote>';
                $in_blockquote = true;
            }
            $out .= $render_inline($m[1]) . "\n";
            continue;
        }

        // List item: capture leading spaces to determine nesting (2 spaces = 1 level)
        if (preg_match('/^(\s*)[\-\*]\s+(.*)$/', $line, $m)) {
            $flush_para();
            if ($in_blockquote) {
                $out .= "</blockquote>\n";
                $in_blockquote = false;
            }
            $indent = strlen($m[1]);
            $level = (int)($indent / 2);  // 0 spaces -> 0, 2 spaces -> 1, 4 spaces -> 2
            $item_content = $m[2];

            if (!$in_list) {
                $out .= "<ul>\n";
                $in_list = true;
                $list_level = 0;
            }

            if ($level > $list_level) {
                // Go deeper: open new ul(s). Parent li stays open.
                for ($i = $list_level; $i < $level; $i++) {
                    $out .= "<ul>\n";
                }
                $list_level = $level;
                $out .= '<li>' . $render_inline($item_content) . "\n";
            } elseif ($level < $list_level) {
                // Go shallower: close current li, then for each level up close </ul> and the parent </li>.
                $out .= "</li>\n";
                for ($i = $level; $i < $list_level; $i++) {
                    $out .= "</ul>\n</li>\n";
                }
                $list_level = $level;
                $out .= '<li>' . $render_inline($item_content) . "\n";
            } else {
                // Same level: close previous li, open new li.
                $out .= "</li>\n";
                $out .= '<li>' . $render_inline($item_content) . "\n";
            }
            continue;
        }

        if ($trimmed === '') {
            $flush_para();
            $close_list();
            if ($in_blockquote) {
                $out .= "</blockquote>\n";
                $in_blockquote = false;
            }
            continue;
        }

        $close_list();
        if ($in_blockquote) {
            $out .= "</blockquote>\n";
            $in_blockquote = false;
        }
        $buffer .= ($buffer === '' ? '' : ' ') . $render_inline($trimmed);
    }

    $flush_para();
    $close_list();
    if ($in_blockquote) {
        $out .= "</blockquote>\n";
    }

    return $out;
}

$toc = extract_toc($markdown);
$body_html = markdown_to_html($markdown);
?>
<style>
  .userguide-wrap { max-width: 1200px; margin: 1em auto; font-size: 1.15rem; }
  .userguide-layout { display: table; width: 100%; border-collapse: collapse; }
  .userguide-sidebar { display: table-cell; width: 220px; vertical-align: top; padding-right: 1.5em; border-right: 1px solid #ccc; }
  .userguide-content { display: table-cell; vertical-align: top; padding-left: 1.5em; }
  .userguide-toc { position: sticky; top: 1em; font-size: 1em; }
  .userguide-toc ul { list-style: none; margin: 0; padding: 0; }
  .userguide-toc li { margin: 0.25em 0; }
  .userguide-toc li.toc-h3 { padding-left: 1em; }
  .userguide-toc a { color: #336; text-decoration: none; }
  .userguide-toc a:hover { text-decoration: underline; }
  .userguide-content h1 { margin-top: 0; border-bottom: 1px solid #ccc; font-size: 1.6em; }
  .userguide-content h2 { margin-top: 1.2em; font-size: 1.35em; }
  .userguide-content h3 { margin-top: 1em; font-size: 1.15em; }
  .userguide-content p, .userguide-content li { line-height: 1.5; }
  .userguide-content blockquote { margin: 0.5em 0; padding-left: 1em; border-left: 3px solid #999; color: #555; }
  .userguide-content code { background: #f5f5f5; padding: 0.1em 0.3em; border-radius: 2px; font-size: 1em; }
</style>

<div class="userguide-wrap">
  <div class="userguide-layout">
    <nav class="userguide-sidebar">
      <div class="userguide-toc">
        <strong>Contents</strong>
        <ul>
<?php
foreach ($toc as $item) {
    $cls = $item['level'] === 3 ? ' class="toc-h3"' : '';
    echo '          <li' . $cls . '><a href="#' . htmlspecialchars($item['id']) . '">' . htmlspecialchars($item['text']) . "</a></li>\n";
}
?>
        </ul>
      </div>
    </nav>
    <main class="userguide-content">
      <?php echo $body_html; ?>
    </main>
  </div>
</div>

<?php
echo('</body>');
echo('</html>');
?>
