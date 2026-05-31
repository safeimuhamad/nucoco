<?php
$page = 'team';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM team_members WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('team/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Team', admin_url('team/'), 'Team Member Detail', $item['name'] ?? '-', [
        ['label' => 'Edit', 'url' => admin_url('team/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('team/delete.php?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this member?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Name', 'value' => $item['name'] ?? '-', 'icon' => 'person', 'meta' => $item['position'] ?? '-'],
        ['label' => 'Status', 'value' => $item['status'] ?? '-', 'icon' => 'flag', 'meta' => 'Order ' . (int) ($item['display_order'] ?? 0)],
    ]);
    detail_card_open('Team Information', 'groups');
    ?>
        <?php if (!empty($item['photo'])): ?><img src="<?= asset_url('uploads/team/' . $item['photo']) ?>" class="detail-media mb-3" alt=""><?php endif; ?>
        <?php detail_fields([
            'Name' => detail_text($item['name'] ?? '-'),
            'Position' => detail_text($item['position'] ?? '-'),
            'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
            'Order' => detail_text((string) (int) ($item['display_order'] ?? 0)),
        ]); ?>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
