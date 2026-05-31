<?php
$page = 'user-roles';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
$role = ['role_key' => '', 'role_name' => '', 'description' => '', 'status' => 'active'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $role = [
        'role_key' => strtolower(trim($_POST['role_key'] ?? '')),
        'role_name' => trim($_POST['role_name'] ?? ''),
        'description' => trim($_POST['description'] ?? ''),
        'status' => in_array($_POST['status'] ?? '', ['active', 'inactive'], true) ? $_POST['status'] : 'active',
    ];

    if (!preg_match('/^[a-z0-9_.-]+$/', $role['role_key'])) {
        $error = 'Role key may only contain lowercase letters, numbers, dots, dashes, and underscores.';
    } elseif ($role['role_name'] === '') {
        $error = 'Role name is required.';
    } elseif (db_select_one("SELECT id FROM user_roles WHERE role_key = ? LIMIT 1", 's', [$role['role_key']])) {
        $error = 'Role key already exists.';
    } else {
        $role_id = db_insert(
            "INSERT INTO user_roles (role_key, role_name, description, status) VALUES (?, ?, ?, ?)",
            'ssss',
            [$role['role_key'], $role['role_name'], $role['description'], $role['status']]
        );

        $permissions = db_select_all("SELECT permission_key, MAX(permission_name) AS permission_name FROM user_access GROUP BY permission_key ORDER BY permission_key ASC");
        foreach ($permissions as $permission) {
            db_insert(
                "INSERT INTO user_access (role_key, permission_key, permission_name, allowed) VALUES (?, ?, ?, 0)",
                'sss',
                [$role['role_key'], $permission['permission_key'], $permission['permission_name']]
            );
        }

        header('Location: ' . admin_url('user-roles/detail.php?id=' . $role_id . '&success=created'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Create Role</h3>
        <a href="<?= admin_url('user-roles/') ?>" class="btn btn-outline-primary">Back</a>
    </div>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php include __DIR__ . '/form.php'; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
