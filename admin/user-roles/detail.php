<?php
$page = 'user-roles';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$role = db_select_one("SELECT * FROM user_roles WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$role) {
    header('Location: ' . admin_url('user-roles/?error=not_found'));
    exit;
}
$access = db_select_all("SELECT * FROM user_access WHERE role_key = ? ORDER BY permission_key ASC", 's', [$role['role_key']]);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('User Roles', admin_url('user-roles/'), 'Role Detail', $role['role_name'], [
        ['label' => 'Edit', 'url' => admin_url('user-roles/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
    ]);
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">Role ' . htmlspecialchars($_GET['success']) . ' successfully.</div>';
    }
    detail_summary([
        ['label' => 'Role', 'value' => $role['role_name'], 'icon' => 'admin_panel_settings', 'meta' => $role['role_key']],
        ['label' => 'Status', 'value' => $role['status'], 'icon' => 'flag', 'meta' => count($access) . ' permissions'],
    ]);
    detail_card_open('Role Information', 'admin_panel_settings');
        detail_fields([
            'Role Name' => detail_text($role['role_name']),
            'Key' => detail_text($role['role_key']),
            'Status' => '<span class="' . detail_badge_class($role['status']) . '">' . detail_text($role['status']) . '</span>',
            'Description' => nl2br(detail_text($role['description'] ?: '')),
        ]);
        ?>
        <hr>
        <h5 class="fs-16 fw-bold mb-3">Access</h5>
        <?php if ($access): foreach ($access as $row): ?>
            <p class="d-flex justify-content-between align-items-center gap-3 mb-2">
                <span><?= htmlspecialchars($row['permission_key']) ?></span>
                <span class="<?= detail_badge_class($row['allowed'] ? 'allowed' : 'denied') ?>"><?= $row['allowed'] ? 'Allowed' : 'Denied' ?></span>
            </p>
        <?php endforeach; else: ?>
            <p class="text-secondary">No access rules yet.</p>
        <?php endif; ?>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
