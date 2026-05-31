<?php
$page = 'user-roles';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/pagination.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$page_num = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;
$row = db_select_one("SELECT COUNT(*) AS total FROM user_roles");
$total_data = (int) ($row['total'] ?? 0);
$pagination = admin_pagination_meta($total_data, $page_num, $limit);
$roles = db_select_all("SELECT * FROM user_roles ORDER BY id ASC LIMIT ?, ?", 'ii', [$pagination['offset'], $pagination['limit']]);
?>
<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">User Roles</h3>
        <a href="<?= admin_url('user-roles/create.php') ?>" class="btn btn-primary text-white">Create Role</a>
    </div>
    <div class="card bg-white rounded-10 border border-white mb-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Key</th><th>Name</th><th>Description</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach ($roles as $role): ?>
                        <tr>
                            <td>
                                <a href="detail.php?id=<?= (int) $role['id'] ?>" class="text-primary text-decoration-none">
                                    <?= htmlspecialchars($role['role_key']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($role['role_name']) ?></td>
                            <td><?= htmlspecialchars($role['description'] ?: '-') ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($role['status']) ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php render_admin_pagination($pagination['page'], $pagination['total_pages'], $total_data, $pagination['start_data'], $pagination['end_data']); ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
