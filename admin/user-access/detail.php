<?php
$page = 'user-access';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$access = db_select_one("SELECT * FROM user_access WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$access) {
    header('Location: ' . admin_url('user-access/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('User Access', admin_url('user-access/'), 'Access Detail', $access['permission_name'], []);
    detail_summary([
        ['label' => 'Permission', 'value' => $access['permission_name'], 'icon' => 'lock_open', 'meta' => $access['permission_key']],
        ['label' => 'Role', 'value' => $access['role_key'], 'icon' => 'admin_panel_settings', 'meta' => $access['allowed'] ? 'Allowed' : 'Denied'],
    ]);
    detail_card_open('Access Information', 'lock_open');
    detail_fields([
        'Permission Name' => detail_text($access['permission_name']),
        'Role' => detail_text($access['role_key']),
        'Permission Key' => detail_text($access['permission_key']),
        'Allowed' => '<span class="' . detail_badge_class($access['allowed'] ? 'allowed' : 'denied') . '">' . ($access['allowed'] ? 'Yes' : 'No') . '</span>',
    ]);
    detail_card_close();
    ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
