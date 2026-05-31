<?php
$page = 'users';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = db_select_one("SELECT id, name, email, username, phone, role, status, activation_expires_at, invited_at, activated_at, last_login, created_at, updated_at FROM users WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$user) {
    header('Location: ' . admin_url('users/?error=not_found'));
    exit;
}
$actions = [];
$invite_expired = $user['status'] === 'inactive'
    && !empty($user['activation_expires_at'])
    && strtotime($user['activation_expires_at']) < time();
if ($invite_expired) {
    $actions[] = [
        'label' => 'Resend Invite',
        'url' => admin_url('users/resend-invite.php?id=' . (int) $user['id']),
        'icon' => 'send',
        'class' => 'btn detail-btn detail-btn-outline-success',
    ];
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Users', admin_url('users/'), 'User Detail', $user['name'], $actions);
    if (isset($_GET['success'])) {
        echo '<div class="alert alert-success">User ' . htmlspecialchars($_GET['success']) . ' successfully.</div>';
    }
    detail_summary([
        ['label' => 'Name', 'value' => $user['name'], 'icon' => 'person', 'meta' => $user['username']],
        ['label' => 'Contact', 'value' => $user['email'], 'icon' => 'mail', 'meta' => $user['phone'] ?: '-'],
        ['label' => 'Role', 'value' => ucfirst($user['role']), 'icon' => 'admin_panel_settings', 'meta' => $user['status']],
    ]);
    detail_card_open('User Information', 'person');
    detail_fields([
        'Name' => detail_text($user['name']),
        'Username' => detail_text($user['username']),
        'Email' => detail_text($user['email']),
        'Phone' => detail_text($user['phone']),
        'Role' => detail_text($user['role']),
        'Status' => '<span class="' . detail_badge_class($user['status']) . '">' . detail_text($user['status']) . '</span>',
        'Invitation Expires' => detail_text($user['activation_expires_at']),
        'Activated At' => detail_text($user['activated_at']),
        'Last Login' => detail_text($user['last_login']),
    ]);
    detail_card_close();
    ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
