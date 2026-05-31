<?php
$request_path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?: '';
if (preg_match('#/admin/product(?:/|$)#', $request_path)) {
    require dirname(__DIR__) . '/product/index.php';
    exit;
}

$page = 'quotations';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/pagination.php';
require_once __DIR__ . '/helpers.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

$search = trim($_GET['search'] ?? '');
$search_like = '%' . $search . '%';
$page_num = max(1, (int) ($_GET['page'] ?? 1));
$limit = 10;

if ($search !== '') {
    $row = db_select_one(
        "SELECT COUNT(*) AS total FROM quotations WHERE quote_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?",
        'sss',
        [$search_like, $search_like, $search_like]
    );
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $quotations = db_select_all(
        "SELECT * FROM quotations
         WHERE quote_number LIKE ? OR customer_name LIKE ? OR customer_email LIKE ?
         ORDER BY id DESC
         LIMIT ?, ?",
        'sssii',
        [$search_like, $search_like, $search_like, $pagination['offset'], $pagination['limit']]
    );
} else {
    $row = db_select_one("SELECT COUNT(*) AS total FROM quotations");
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $quotations = db_select_all("SELECT * FROM quotations ORDER BY id DESC LIMIT ?, ?", 'ii', [$pagination['offset'], $pagination['limit']]);
}
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Quotations</h3>
        <a href="<?= admin_url('quotations/create.php') ?>" class="btn btn-primary text-white">Create Quotation</a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Quotation <?= htmlspecialchars($_GET['success']) ?> successfully.</div>
    <?php endif; ?>

    <div class="card bg-white rounded-10 border border-white mb-4">
        <div class="p-20">
            <form class="table-src-form position-relative m-0" method="GET">
                <input type="text" name="search" class="form-control w-340" placeholder="Search quotation..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Customer</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($quotations): foreach ($quotations as $quote): ?>
                        <tr>
                            <td>
                                <a href="detail.php?id=<?= (int) $quote['id'] ?>" class="text-primary text-decoration-none">
                                    <?= htmlspecialchars($quote['quote_number']) ?>
                                </a>
                            </td>
                            <td>
                                <?= htmlspecialchars($quote['customer_name']) ?><br>
                                <span class="text-secondary"><?= htmlspecialchars($quote['customer_email'] ?: '-') ?></span>
                            </td>
                            <td><?= htmlspecialchars(ucfirst($quote['quote_type'])) ?></td>
                            <td><span class="badge bg-primary"><?= htmlspecialchars(ucfirst($quote['status'])) ?></span></td>
                            <td><?= quotation_format_money($quote['grand_total'], $quote['currency']) ?></td>
                            <td><?= htmlspecialchars(date('d M Y', strtotime($quote['created_at']))) ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-4">No quotation found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php render_admin_pagination($pagination['page'], $pagination['total_pages'], $total_data, $pagination['start_data'], $pagination['end_data'], ['search' => $search]); ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
