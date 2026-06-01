<?php
$page = 'leads';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/pagination.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$search = trim($_GET['search'] ?? '');
$search_like = '%' . $search . '%';
$page_num = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;

if ($search !== '') {
    $row = db_select_one(
        "SELECT COUNT(*) AS total FROM leads WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR company LIKE ? OR address LIKE ?",
        'sssss',
        [$search_like, $search_like, $search_like, $search_like, $search_like]
    );
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $leads = db_select_all(
        "SELECT * FROM leads WHERE name LIKE ? OR email LIKE ? OR phone LIKE ? OR company LIKE ? OR address LIKE ? ORDER BY id DESC LIMIT ?, ?",
        'sssssii',
        [$search_like, $search_like, $search_like, $search_like, $search_like, $pagination['offset'], $pagination['limit']]
    );
} else {
    $row = db_select_one("SELECT COUNT(*) AS total FROM leads");
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $leads = db_select_all("SELECT * FROM leads ORDER BY id DESC LIMIT ?, ?", 'ii', [$pagination['offset'], $pagination['limit']]);
}
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Leads</h3>
        <a href="<?= admin_url('leads/create.php') ?>" class="btn btn-primary text-white">Create Lead</a>
    </div>

    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Lead <?= htmlspecialchars($_GET['success']) ?> successfully.</div><?php endif; ?>

    <div class="card bg-white rounded-10 border border-white mb-4">
        <div class="p-20">
            <form class="table-src-form position-relative m-0" method="GET">
                <input type="text" name="search" class="form-control w-340" placeholder="Search leads..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Name</th><th>Contact</th><th>Address</th><th>Source</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>
                    <?php if ($leads): foreach ($leads as $lead): ?>
                        <tr>
                            <td>
                                <a href="detail.php?id=<?= (int) $lead['id'] ?>" class="text-primary text-decoration-none">
                                    <?= htmlspecialchars($lead['name']) ?>
                                </a><br>
                                <span class="text-secondary"><?= htmlspecialchars($lead['company'] ?: '-') ?></span>
                            </td>
                            <td><?= htmlspecialchars($lead['email'] ?: '-') ?><br><span class="text-secondary"><?= htmlspecialchars($lead['phone'] ?: '-') ?></span></td>
                            <td><?= nl2br(htmlspecialchars($lead['address'] ?: '-')) ?></td>
                            <td><?= htmlspecialchars(ucfirst($lead['source'])) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars(ucfirst($lead['status'])) ?></span></td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($lead['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-4">No leads found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php render_admin_pagination($pagination['page'], $pagination['total_pages'], $total_data, $pagination['start_data'], $pagination['end_data'], ['search' => $search]); ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
