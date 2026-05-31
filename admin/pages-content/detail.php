<?php
$page = 'pages-content';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one(
    "SELECT pc.*, p.page_name, p.slug FROM page_contents pc LEFT JOIN pages p ON p.id = pc.page_id WHERE pc.id = ? LIMIT 1",
    'i',
    [$id]
);
if (!$item) {
    header('Location: ' . admin_url('pages-content/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Page Content', admin_url('pages-content/'), 'Page Content Detail', $item['page_name'] ?? '-', [
        ['label' => 'Edit', 'url' => admin_url('pages-content/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('pages-content/delete.php?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this page content?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Page', 'value' => $item['page_name'] ?? '-', 'icon' => 'article', 'meta' => $item['slug'] ?? '-'],
        ['label' => 'Language', 'value' => strtoupper($item['language_code'] ?? '-'), 'icon' => 'translate', 'meta' => $item['status'] ?? '-'],
    ]);
    detail_card_open('Content Information', 'article');
        detail_fields([
            'Page' => detail_text($item['page_name'] ?? '-'),
            'Slug' => detail_text($item['slug'] ?? '-'),
            'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
            'Meta Title' => detail_text($item['meta_title'] ?? '-'),
            'Hero Title' => detail_text($item['hero_title'] ?? '-'),
        ]);
        ?>
        <hr>
        <div class="detail-body-text"><?= $item['content'] ?? '' ?></div>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
