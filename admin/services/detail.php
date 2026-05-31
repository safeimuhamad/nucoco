<?php
$page = 'services';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM services WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('services/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Services', admin_url('services/'), 'Service Detail', $item['title'] ?? '-', [
        ['label' => 'Edit', 'url' => admin_url('services/edit?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('services/delete?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this service?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Service', 'value' => $item['title'] ?? '-', 'icon' => 'support_agent', 'meta' => $item['category'] ?? '-'],
        ['label' => 'Language', 'value' => strtoupper($item['language'] ?? '-'), 'icon' => 'translate', 'meta' => $item['status'] ?? '-'],
    ]);
    ?>
    <?php detail_card_open('Service Information', 'support_agent'); ?>
        <?php if (!empty($item['image'])): ?><img src="<?= asset_url('uploads/services/' . $item['image']) ?>" class="detail-media mb-3" alt=""><?php endif; ?>
        <?php detail_fields([
            'Judul' => detail_text($item['title'] ?? '-'),
            'Language' => detail_text(strtoupper($item['language'] ?? '-')),
            'Category' => detail_text($item['category'] ?? '-'),
            'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
        ]); ?>
        <hr>
        <h5 class="fs-15 fw-bold mb-2">Quotation Description</h5>
        <div class="detail-body-text mb-3"><?= nl2br(detail_text($item['quotation_description'] ?? '')) ?></div>
        <h5 class="fs-15 fw-bold mb-2">Web Content</h5>
        <div class="detail-body-text"><?= $item['content'] ?? '' ?></div>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
