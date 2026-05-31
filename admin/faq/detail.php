<?php
$page = 'faq';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM faqs WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('faq/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('FAQ', admin_url('faq/'), 'FAQ Detail', '#' . $id, [
        ['label' => 'Edit', 'url' => admin_url('faq/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('faq/delete.php?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this FAQ?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Pertanyaan', 'value' => $item['question'] ?? '-', 'icon' => 'quiz', 'meta' => strtoupper($item['language'] ?? '-')],
        ['label' => 'Status', 'value' => $item['status'] ?? '-', 'icon' => 'flag', 'meta' => 'FAQ'],
    ]);
    detail_card_open('FAQ Information', 'quiz');
        detail_fields([
            'Language' => detail_text(strtoupper($item['language'] ?? '-')),
            'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
            'Pertanyaan' => detail_text($item['question'] ?? '-'),
        ]);
        ?>
        <hr>
        <div class="detail-body-text"><?= nl2br(detail_text($item['answer'] ?? '')) ?></div>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
