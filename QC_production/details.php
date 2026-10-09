<?php
// details.php
// The details (edit) page of one item at one stage, laid out by config/pages.php:
//   details.php?stage=ccd|die|surface|underground&id=N    (no id: the last item)
// Each block of the page is its own form; saving one block changes only what
// that block shows, then comes back to it (#block).
require __DIR__ . '/bootstrap.php';
require_priv('full');

$stage = stage_or_404($_GET['stage'] ?? '');
$P = PAGES[$stage];
$id = (int)($_GET['id'] ?? 0);
if ($id === 0)
    $id = (int)(neighbours($stage, 0)[3] ?? 0);
$self = "details.php?stage=$stage&id=$id";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['new']))
        redirect("details.php?stage=$stage&id=" . new_item($stage));
    $item = find_item($stage, $id);
    $key = (string)($_POST['block'] ?? '');
    $block = array_values(array_filter($P['blocks'], fn($b) => $b['key'] === $key))[0] ?? null;
    if (!$item || !$block) {
        http_response_code(400);
        exit('Unknown item or form.');
    }
    $msgs = match ($block['special'] ?? '') {
        'history' => history_save(STAGES[$stage]['item'], $id, (int)($_POST['entry'] ?? 0), (array)($_POST['h'] ?? [])),
        'layout'  => save_layout($id, STAGES[$stage]['positions'], $_POST),
        default   => save_block($stage, $item, find_test($stage, $id), $block, $_POST),
    };
    $msgs = array_merge($msgs, save_uploads($stage, $id, block_slots($stage, $block), $_FILES['f'] ?? []));
    if ($msgs)
        $_SESSION['block_msgs'] = [$key => $msgs];
    redirect("$self#$key");
}

$item   = find_item($stage, $id);
$test   = $item ? find_test($stage, $id) : null;
$values = load_values($test);
$nav    = neighbours($stage, $id);
$msgs   = $_SESSION['block_msgs'] ?? [];
unset($_SESSION['block_msgs']);
$edit_history = (int)($_GET['edit_history'] ?? 0);

page_start($P['item_label'] . " $id");
require __DIR__ . '/views/test_form.php';
page_end();
