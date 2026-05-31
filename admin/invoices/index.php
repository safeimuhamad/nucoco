<?php
$page = 'invoices';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
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
        "SELECT COUNT(*) AS total FROM invoices i
         LEFT JOIN quotations q ON q.id = i.quotation_id
         WHERE i.invoice_number LIKE ? OR i.customer_name LIKE ? OR i.customer_email LIKE ? OR q.quote_number LIKE ?",
        'ssss',
        [$search_like, $search_like, $search_like, $search_like]
    );
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $invoices = db_select_all(
        "SELECT i.*, q.quote_number FROM invoices i
         LEFT JOIN quotations q ON q.id = i.quotation_id
         WHERE i.invoice_number LIKE ? OR i.customer_name LIKE ? OR i.customer_email LIKE ? OR q.quote_number LIKE ?
         ORDER BY i.id DESC LIMIT ?, ?",
        'ssssii',
        [$search_like, $search_like, $search_like, $search_like, $pagination['offset'], $pagination['limit']]
    );
} else {
    $row = db_select_one("SELECT COUNT(*) AS total FROM invoices");
    $total_data = (int) ($row['total'] ?? 0);
    $pagination = admin_pagination_meta($total_data, $page_num, $limit);
    $invoices = db_select_all(
        "SELECT i.*, q.quote_number FROM invoices i
         LEFT JOIN quotations q ON q.id = i.quotation_id
         ORDER BY i.id DESC LIMIT ?, ?",
        'ii',
        [$pagination['offset'], $pagination['limit']]
    );
}
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <div>
            <h3 class="mb-0">Invoices</h3>
            <p class="text-secondary mb-0">Manage invoices created from quotations.</p>
        </div>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">Invoice <?= htmlspecialchars($_GET['success']) ?> successfully.</div>
    <?php endif; ?>

    <div class="card bg-white rounded-10 border border-white mb-4">
        <div class="p-20">
            <form class="table-src-form position-relative m-0" method="GET">
                <input type="text" name="search" class="form-control w-340" placeholder="Search invoice..." value="<?= htmlspecialchars($search) ?>">
                <button type="submit" class="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </form>
        </div>
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Invoice No.</th>
                        <th>Customer</th>
                        <th>Quotation</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Due Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($invoices): foreach ($invoices as $invoice): ?>
                        <tr>
                            <td>
                                <a href="detail.php?id=<?= (int) $invoice['id'] ?>" class="text-primary text-decoration-none">
                                    <?= htmlspecialchars($invoice['invoice_number']) ?>
                                </a>
                            </td>
                            <td>
                                <?= htmlspecialchars($invoice['customer_name']) ?><br>
                                <span class="text-secondary"><?= htmlspecialchars($invoice['customer_email'] ?: '-') ?></span>
                            </td>
                            <td><?= htmlspecialchars($invoice['quote_number'] ?: '-') ?></td>
                            <td><span class="<?= detail_badge_class($invoice['status']) ?>"><?= htmlspecialchars(invoice_status_label($invoice['status'])) ?></span></td>
                            <td><?= quotation_format_money($invoice['grand_total'], $invoice['currency']) ?></td>
                            <td><?= htmlspecialchars($invoice['due_date'] ?: '-') ?></td>
                        </tr>
                    <?php endforeach; else: ?>
                        <tr><td colspan="6" class="text-center py-4">No invoice found.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php render_admin_pagination($pagination['page'], $pagination['total_pages'], $total_data, $pagination['start_data'], $pagination['end_data'], ['search' => $search]); ?>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
