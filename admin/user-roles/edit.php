<?php
$page = 'user-roles';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$role = db_select_one("SELECT * FROM user_roles WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$role) {
    header('Location: ' . admin_url('user-roles/?error=not_found'));
    exit;
}

$error = '';
$old_key = $role['role_key'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $role = [
        'id' => $id,
        'role_key' => strtolower(trim($_POST['role_key'] ?? '')),
        'role_name' => trim($_POST['role_name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];

    if (!preg_match('/^[a-z0-9_.-]+$/', $role['role_key'])) {
        $error = 'Role key may only contain lowercase letters, numbers, dots, dashes, and underscores.';
    } elseif ($role['role_name'] === '') {
        $error = 'Role name is required.';
    } elseif (db_select_one("SELECT id FROM user_roles WHERE role_key = ? AND id != ? LIMIT 1", 'si', [$role['role_key'], $id])) {
        $error = 'Role key already exists.';
    } else {
        db_update(
            "UPDATE user_roles SET role_key = ?, role_name = ?, description = ?, status = ? WHERE id = ?",
            'ssssi',
            [$role['role_key'], $role['role_name'], $role['description'], $role['status'], $id]
        );
        if ($old_key !== $role['role_key']) {
            db_update("UPDATE users SET role = ? WHERE role = ?", 'ss', [$role['role_key'], $old_key]);
            db_update("UPDATE user_access SET role_key = ? WHERE role_key = ?", 'ss', [$role['role_key'], $old_key]);
        }
        header('Location: ' . admin_url('user-roles/detail.php?id=' . $id . '&success=updated'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Edit Role</h3>
        <a href="<?= admin_url('user-roles/detail.php?id=' . $id) ?>" class="btn btn-outline-primary">Back</a>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php include __DIR__ . '/form.php'; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
