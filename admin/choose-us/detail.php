<?php
$page = 'choose-us';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM why_choose_us WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('choose-us/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    $status = ((int) ($item['is_active'] ?? 0) === 1) ? 'Active' : 'Inactive';
    detail_page_header('Choose Us', admin_url('choose-us/'), 'Choose Us Detail', $item['title'] ?? '-', [
        ['label' => 'Edit', 'url' => admin_url('choose-us/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('choose-us/delete.php?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this data?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Title', 'value' => $item['title'] ?? '-', 'icon' => 'task_alt', 'meta' => strtoupper($item['language'] ?? '-')],
        ['label' => 'Status', 'value' => $status, 'icon' => 'flag', 'meta' => 'Order ' . (int) ($item['sort_order'] ?? 0)],
    ]);
    detail_card_open('Choose Us Information', 'task_alt');
    ?>
        <?php if (!empty($item['icon'])): ?><img src="<?= asset_url('uploads/' . $item['icon']) ?>" class="detail-media mb-3" alt=""><?php endif; ?>
        <?php detail_fields([
            'Title' => detail_text($item['title'] ?? '-'),
            'Language' => detail_text(strtoupper($item['language'] ?? '-')),
            'Status' => '<span class="' . detail_badge_class($status) . '">' . detail_text($status) . '</span>',
            'Order' => detail_text((string) (int) ($item['sort_order'] ?? 0)),
        ]); ?>
        <hr>
        <div class="detail-body-text"><?= nl2br(detail_text($item['description'] ?? '')) ?></div>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
