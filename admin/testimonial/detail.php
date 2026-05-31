<?php
$page = 'testimonial';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM testimonials WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('testimonial/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Testimonial', admin_url('testimonial/'), 'Testimonial Detail', $item['name'] ?? '-', [
        ['label' => 'Edit', 'url' => admin_url('testimonial/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('testimonial/delete.php?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this testimonial?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Name', 'value' => $item['name'] ?? '-', 'icon' => 'reviews', 'meta' => $item['position'] ?? '-'],
        ['label' => 'Language', 'value' => strtoupper($item['language'] ?? '-'), 'icon' => 'translate', 'meta' => $item['status'] ?? '-'],
    ]);
    detail_card_open('Testimonial Information', 'reviews');
    ?>
        <?php if (!empty($item['photo'])): ?><img src="<?= asset_url('uploads/testimonials/' . $item['photo']) ?>" class="detail-media mb-3" alt=""><?php endif; ?>
        <?php detail_fields([
            'Name' => detail_text($item['name'] ?? '-'),
            'Language' => detail_text(strtoupper($item['language'] ?? '-')),
            'Position' => detail_text($item['position'] ?? '-'),
            'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
        ]); ?>
        <hr>
        <div class="detail-body-text"><?= nl2br(detail_text($item['content'] ?? '')) ?></div>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
