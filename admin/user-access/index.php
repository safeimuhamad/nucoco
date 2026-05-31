<?php
$page = 'user-access';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$message = '';
$error = '';
$default_permissions = [
    'sales.leads.manage' => ['name' => 'Leads', 'group' => 'Sales Management'],
    'sales.quotations.manage' => ['name' => 'Quotations', 'group' => 'Sales Management'],
    'sales.invoices.manage' => ['name' => 'Invoices', 'group' => 'Sales Management'],
    'webadmin.inbox.manage' => ['name' => 'Inbox', 'group' => 'Web Admin'],
    'webadmin.pages.manage' => ['name' => 'Pages', 'group' => 'Web Admin'],
    'webadmin.page_content.manage' => ['name' => 'Page Content', 'group' => 'Web Admin'],
    'webadmin.products.manage' => ['name' => 'Products', 'group' => 'Web Admin'],
    'webadmin.services.manage' => ['name' => 'Services', 'group' => 'Web Admin'],
    'webadmin.news.manage' => ['name' => 'News', 'group' => 'Web Admin'],
    'webadmin.team.manage' => ['name' => 'Team', 'group' => 'Web Admin'],
    'webadmin.testimonial.manage' => ['name' => 'Testimonial', 'group' => 'Web Admin'],
    'webadmin.faq.manage' => ['name' => 'FAQ', 'group' => 'Web Admin'],
    'webadmin.choose_us.manage' => ['name' => 'Choose Us', 'group' => 'Web Admin'],
    'webadmin.settings.manage' => ['name' => 'Web Settings', 'group' => 'Web Admin'],
    'users.users.manage' => ['name' => 'Users', 'group' => 'User Management'],
    'users.roles.manage' => ['name' => 'User Roles', 'group' => 'User Management'],
    'users.access.manage' => ['name' => 'User Access', 'group' => 'User Management'],
];

$roles_for_seed = db_select_all("SELECT role_key FROM user_roles ORDER BY role_key ASC");
foreach ($roles_for_seed as $role_row) {
    foreach ($default_permissions as $permission_key => $permission) {
        if (!db_select_one("SELECT id FROM user_access WHERE role_key = ? AND permission_key = ? LIMIT 1", 'ss', [$role_row['role_key'], $permission_key])) {
            $role_key = $role_row['role_key'];
            $allowed = ($role_key === 'admin')
                || ($role_key === 'sales' && str_starts_with($permission_key, 'sales.'))
                || ($role_key === 'editor' && str_starts_with($permission_key, 'webadmin.'));
            db_insert(
                "INSERT INTO user_access (role_key, permission_key, permission_name, allowed) VALUES (?, ?, ?, ?)",
                'sssi',
                [$role_key, $permission_key, $permission['name'], $allowed ? 1 : 0]
            );
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    $new_key = strtolower(trim($_POST['new_permission_key'] ?? ''));
    $new_name = trim($_POST['new_permission_name'] ?? '');
    if ($new_key !== '' && $new_name !== '') {
        if (preg_match('/^[a-z0-9_.-]+$/', $new_key)) {
            $roles_for_new = db_select_all("SELECT role_key FROM user_roles ORDER BY role_key ASC");
            foreach ($roles_for_new as $role_row) {
                if (!db_select_one("SELECT id FROM user_access WHERE role_key = ? AND permission_key = ? LIMIT 1", 'ss', [$role_row['role_key'], $new_key])) {
                    db_insert(
                        "INSERT INTO user_access (role_key, permission_key, permission_name, allowed) VALUES (?, ?, ?, 0)",
                        'sss',
                        [$role_row['role_key'], $new_key, $new_name]
                    );
                }
            }
        } else {
            $error = 'Permission key may only contain lowercase letters, numbers, dots, dashes, and underscores.';
        }
    }

    if ($error === '') {
        $allowed = $_POST['allowed'] ?? [];
        $permissions = db_select_all("SELECT permission_key, MAX(permission_name) AS permission_name FROM user_access GROUP BY permission_key ORDER BY permission_key ASC");
        $roles = db_select_all("SELECT role_key FROM user_roles ORDER BY role_key ASC");

        foreach ($roles as $role_row) {
            foreach ($permissions as $permission) {
                $role_key = $role_row['role_key'];
                $permission_key = $permission['permission_key'];
                $is_allowed = isset($allowed[$role_key][$permission_key]) ? 1 : 0;
                $existing = db_select_one("SELECT id FROM user_access WHERE role_key = ? AND permission_key = ? LIMIT 1", 'ss', [$role_key, $permission_key]);
                if ($existing) {
                    db_update(
                        "UPDATE user_access SET permission_name = ?, allowed = ? WHERE id = ?",
                        'sii',
                        [$permission['permission_name'], $is_allowed, (int) $existing['id']]
                    );
                } else {
                    db_insert(
                        "INSERT INTO user_access (role_key, permission_key, permission_name, allowed) VALUES (?, ?, ?, ?)",
                        'sssi',
                        [$role_key, $permission_key, $permission['permission_name'], $is_allowed]
                    );
                }
            }
        }
        $message = 'Access rules updated successfully.';
    }
}

$roles = db_select_all("SELECT role_key, role_name FROM user_roles ORDER BY role_name ASC");
$permissions = db_select_all("SELECT permission_key, MAX(permission_name) AS permission_name FROM user_access GROUP BY permission_key ORDER BY permission_key ASC");
$permission_groups = [];
foreach ($permissions as $permission) {
    $group = $default_permissions[$permission['permission_key']]['group'] ?? 'Custom Permissions';
    $permission_groups[$group][] = $permission;
}
$access_rows = db_select_all("SELECT role_key, permission_key, allowed FROM user_access");
$access_map = [];
foreach ($access_rows as $row) {
    $access_map[$row['role_key']][$row['permission_key']] = (int) $row['allowed'];
}
?>
<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">User Access</h3>
    </div>

    <?php if ($message): ?><div class="alert alert-success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <form method="POST" class="card bg-white p-20 rounded-10 border border-white mb-4">
        <?= csrf_field() ?>
        <div class="row mb-3">
            <div class="col-md-4 mb-20">
                <label class="label fs-16 mb-2">New Permission Key</label>
                <input class="form-control" name="new_permission_key" placeholder="module.action">
            </div>
            <div class="col-md-4 mb-20">
                <label class="label fs-16 mb-2">New Permission Name</label>
                <input class="form-control" name="new_permission_name" placeholder="Permission display name">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th style="min-width:240px;">Permission</th>
                        <?php foreach ($roles as $role): ?>
                            <th class="text-center"><?= htmlspecialchars($role['role_name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($permission_groups): foreach ($permission_groups as $group_name => $group_permissions): ?>
                        <tr>
                            <td colspan="<?= count($roles) + 1 ?>" class="bg-light fw-bold text-primary"><?= htmlspecialchars($group_name) ?></td>
                        </tr>
                        <?php foreach ($group_permissions as $permission): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($permission['permission_name']) ?></strong><br>
                                    <span class="text-secondary"><?= htmlspecialchars($permission['permission_key']) ?></span>
                                </td>
                                <?php foreach ($roles as $role): ?>
                                    <td class="text-center">
                                        <input
                                            class="form-check-input"
                                            type="checkbox"
                                            name="allowed[<?= htmlspecialchars($role['role_key']) ?>][<?= htmlspecialchars($permission['permission_key']) ?>]"
                                            value="1"
                                            <?= !empty($access_map[$role['role_key']][$permission['permission_key']]) ? 'checked' : '' ?>
                                        >
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    <?php endforeach; else: ?>
                        <tr><td colspan="<?= count($roles) + 1 ?>" class="text-center py-4">No permissions yet. Add a permission above.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="form-actions">
            <button class="btn btn-primary text-white" type="submit">Save Access</button>
        </div>
    </form>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
