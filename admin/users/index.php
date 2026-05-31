<?php
$page = 'users';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/pagination.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$page_num = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;
$row = db_select_one("SELECT COUNT(*) AS total FROM users");
$total_data = (int) ($row['total'] ?? 0);
$pagination = admin_pagination_meta($total_data, $page_num, $limit);
$users = db_select_all(
    "SELECT id, name, email, username, role, status, last_login, created_at FROM users ORDER BY id DESC LIMIT ?, ?",
    'ii',
    [$pagination['offset'], $pagination['limit']]
);
?>
<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Users</h3>
        <a href="<?= admin_url('users/create.php') ?>" class="btn btn-primary text-white">Create User</a>
    </div>
    <div class="card bg-white rounded-10 border border-white mb-4">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Last Login</th></tr></thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <a href="detail.php?id=<?= (int) $user['id'] ?>" class="text-primary text-decoration-none">
                                    <?= htmlspecialchars($user['name']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($user['username']) ?></td>
                            <td><?= htmlspecialchars($user['email']) ?></td>
                            <td><?= htmlspecialchars($user['role']) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars($user['status']) ?></span></td>
                            <td><?= htmlspecialchars($user['last_login'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php render_admin_pagination($pagination['page'], $pagination['total_pages'], $total_data, $pagination['start_data'], $pagination['end_data']); ?>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
