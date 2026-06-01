<?php
$page = 'product-categories';
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
        "SELECT COUNT(*) AS total FROM product_categories WHERE category_key LIKE ? OR label_en LIKE ? OR label_id LIKE ?",
        'sss',
        [$search_like, $search_like, $search_like]
    );
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $categories = db_select_all(
        "SELECT * FROM product_categories
         WHERE category_key LIKE ? OR label_en LIKE ? OR label_id LIKE ?
         ORDER BY sort_order ASC, id ASC LIMIT ?, ?",
        'sssii',
        [$search_like, $search_like, $search_like, $pagination['offset'], $pagination['limit']]
    );
} else {
    $row = db_select_one("SELECT COUNT(*) AS total FROM product_categories");
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $categories = db_select_all("SELECT * FROM product_categories ORDER BY sort_order ASC, id ASC LIMIT ?, ?", 'ii', [$pagination['offset'], $pagination['limit']]);
}
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Product Categories</h3>
        <a href="<?= admin_url('product-categories/create.php') ?>" class="btn btn-primary text-white">Create Category</a>
    </div>

    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Category <?= htmlspecialchars($_GET['success']) ?> successfully.</div><?php endif; ?>

    <div class="card bg-white rounded-10 border border-white mb-4">
        <div class="p-20">
            <form class="table-src-form position-relative m-0" method="GET">
                <input type="text" name="search" class="form-control w-340" placeholder="Search category..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Category</th>
                        <th>Indonesia Name</th>
                        <th>Key</th>
                        <th>Sort</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($categories): foreach ($categories as $item): ?>
                        <tr>
                            <td>
                                <a href="detail.php?id=<?= (int) $item['id'] ?>" class="text-primary text-decoration-none">
                                    <?= htmlspecialchars($item['label_en']) ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($item['label_id']) ?></td>
                            <td><?= htmlspecialchars($item['category_key']) ?></td>
                            <td><?= (int) $item['sort_order'] ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars(ucfirst($item['status'])) ?></span></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="5" class="text-center py-4">No category found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php render_admin_pagination($pagination['page'], $pagination['total_pages'], $total_data, $pagination['start_data'], $pagination['end_data'], ['search' => $search]); ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
