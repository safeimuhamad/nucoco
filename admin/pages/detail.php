<?php
$page = 'pages';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM pages WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('pages/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Pages', admin_url('pages/'), 'Page Detail', $item['page_name'] ?? '-', [
        ['label' => 'Edit', 'url' => admin_url('pages/edit?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('pages/delete?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this page?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Page', 'value' => $item['page_name'] ?? '-', 'icon' => 'description', 'meta' => $item['slug'] ?? '-'],
        ['label' => 'Language', 'value' => strtoupper($item['language_code'] ?? '-'), 'icon' => 'translate', 'meta' => $item['status'] ?? '-'],
    ]);
    detail_card_open('Page Information', 'description');
    detail_fields([
        'Page Name' => detail_text($item['page_name'] ?? '-'),
        'Slug' => detail_text($item['slug'] ?? '-'),
        'Language' => detail_text(strtoupper($item['language_code'] ?? '-')),
        'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
        'Translation Group' => detail_text((string) ($item['translation_group_id'] ?? '-')),
    ]);
    detail_card_close();
    ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
