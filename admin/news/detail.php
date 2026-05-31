<?php
$page = 'news';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM news WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('news/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    $actions = [];
    if (($item['language'] ?? '') === 'id') {
        $actions[] = ['label' => 'Add EN', 'url' => admin_url('news/create?language=en&parent_id=' . $id), 'icon' => 'translate', 'class' => 'btn detail-btn detail-btn-outline-success'];
    }
    $actions[] = ['label' => 'Edit', 'url' => admin_url('news/edit?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'];
    $actions[] = ['label' => 'Delete', 'url' => admin_url('news/delete?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this news?', 'class' => 'btn detail-btn detail-btn-danger'];
    detail_page_header('News', admin_url('news/'), 'News Detail', $item['title'] ?? '-', $actions);
    detail_summary([
        ['label' => 'News', 'value' => $item['title'] ?? '-', 'icon' => 'newspaper', 'meta' => $item['category'] ?? '-'],
        ['label' => 'Language', 'value' => strtoupper($item['language'] ?? '-'), 'icon' => 'translate', 'meta' => $item['status'] ?? '-'],
    ]);
    detail_card_open('News Information', 'newspaper');
        ?>
        <?php if (!empty($item['image'])): ?><img src="<?= asset_url('uploads/' . $item['image']) ?>" class="detail-media mb-3" alt=""><?php endif; ?>
        <?php detail_fields([
            'Title' => detail_text($item['title'] ?? '-'),
            'Language' => detail_text(strtoupper($item['language'] ?? '-')),
            'Category' => detail_text($item['category'] ?? '-'),
            'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
        ]); ?>
        <hr>
        <div class="detail-body-text"><?= $item['content'] ?? '' ?></div>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
