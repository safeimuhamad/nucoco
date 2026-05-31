<?php
$page = 'inbox';
include __DIR__ . '/../includes/auth.php';
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';

require_once __DIR__ . '/../includes/db.php';

$limit = 10;
$page_num = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page_num = max($page_num, 1);

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_like = '%' . $search . '%';

/*
|--------------------------------------------------------------------------
| SUMMARY COUNTS
|--------------------------------------------------------------------------
*/
$total = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM inquiries"))[0];
$new_count = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM inquiries WHERE status='new'"))[0];
$read_count = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM inquiries WHERE status='read'"))[0];
$replied_count = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM inquiries WHERE status='replied'"))[0];

/*
|--------------------------------------------------------------------------
| COUNT FILTERED DATA
|--------------------------------------------------------------------------
*/
if ($search !== '') {
    $count_sql = "SELECT COUNT(*) as total 
                  FROM inquiries 
                  WHERE name LIKE ? 
                     OR email LIKE ? 
                     OR phone LIKE ? 
                     OR service_type LIKE ? 
                     OR message LIKE ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "sssss", $search_like, $search_like, $search_like, $search_like, $search_like);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = mysqli_fetch_assoc($count_result);
    mysqli_stmt_close($count_stmt);

    $total_data = (int) ($count_row['total'] ?? 0);
} else {
    $total_data = $total;
}

$total_pages = ($total_data > 0) ? ceil($total_data / $limit) : 1;

if ($page_num > $total_pages) {
    $page_num = $total_pages;
}

$start = ($page_num - 1) * $limit;

/*
|--------------------------------------------------------------------------
| GET DATA
|--------------------------------------------------------------------------
*/
if ($search !== '') {
    $sql = "SELECT * FROM inquiries
            WHERE name LIKE ? 
               OR email LIKE ? 
               OR phone LIKE ? 
               OR service_type LIKE ? 
               OR message LIKE ?
            ORDER BY id DESC
            LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssii", $search_like, $search_like, $search_like, $search_like, $search_like, $start, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM inquiries ORDER BY id DESC LIMIT $start, $limit";
    $result = mysqli_query($conn, $sql);
}

if (!$result) {
    die('Query error: ' . mysqli_error($conn));
}

$start_data = ($total_data > 0) ? $start + 1 : 0;
$end_data = ($total_data > 0) ? min($start + $limit, $total_data) : 0;
?>

<div class="main-content-container overflow-hidden">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Inbox</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="#" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Inbox</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Inbox List</span>
                </li>
            </ol>
        </nav>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="alert alert-success mb-3">Contact deleted successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
        <div class="alert alert-success mb-3">Contact created successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
        <div class="alert alert-success mb-3">Contact updated successfully.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger mb-3">
            <?php
            switch ($_GET['error']) {
                case 'invalid_id':
                    echo 'Invalid Contact ID.';
                    break;
                case 'not_found':
                    echo 'Contact not found.';
                    break;
                case 'prepare_failed':
                    echo 'Failed to prepare query.';
                    break;
                case 'delete_failed':
                    echo 'Failed to delete Contact.';
                    break;
                default:
                    echo 'An error occurred while processing your request.';
                    break;
            }
            ?>
        </div>
    <?php endif; ?>

    <div class="card bg-white rounded-10 border border-white mb-4">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 p-20">
            <div class="d-flex flex-wrap gap-2 gap-xxl-5 align-items-center">
                <form class="table-src-form position-relative m-0" method="GET">
                    <input 
                        type="text" 
                        name="search" 
                        class="form-control w-340" 
                        placeholder="Search here..."
                        value="<?= htmlspecialchars($search) ?>"
                    >
                    <button type="submit" class="src-btn position-absolute top-50 start-0 translate-middle-y bg-transparent p-0 border-0">
                        <span class="material-symbols-outlined">search</span>
                    </button>
                </form>

                <ul class="p-0 mb-0 list-unstyled d-flex align-items-center flex-wrap" style="gap: 20px;">
                    <li class="fs-16">All <span class="text-primary">(<?= number_format($total) ?>)</span></li>
                    <li class="fs-16">New <span class="text-primary">(<?= number_format($new_count) ?>)</span></li>
                    <li class="fs-16">Read <span class="text-primary">(<?= number_format($read_count) ?>)</span></li>
                    <li class="fs-16">Replied <span class="text-primary">(<?= number_format($replied_count) ?>)</span></li>
                </ul>
            </div>
        </div>

        <div class="default-table-area mx-minus-1 table-product-list">
            <div class="table-responsive">

                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Service</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($total_data > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <a href="detail.php?id=<?= (int) $row['id'] ?>" class="text-primary text-decoration-none">
                                            <?= htmlspecialchars($row['name']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($row['email']) ?></td>
                                    <td><?= htmlspecialchars($row['phone'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars($row['service_type'] ?? '-') ?></td>
                                    <td><?= htmlspecialchars(mb_strimwidth(strip_tags($row['message']), 0, 80, '...')) ?></td>
                                    <td>
                                        <?php if ($row['status'] === 'new'): ?>
                                            <span class="badge bg-primary">New</span>
                                        <?php elseif ($row['status'] === 'read'): ?>
                                            <span class="badge bg-warning text-dark">Read</span>
                                        <?php else: ?>
                                            <span class="badge bg-success">Replied</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars(date('d M Y H:i', strtotime($row['created_at']))) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4">No inbox found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>

            <?php if ($total_data > 0): ?>
                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap pt-15 p-20">
                    <span class="fs-15">
                        Showing <?= $start_data ?> to <?= $end_data ?> of <?= $total_data ?> entries
                    </span>

                    <nav class="custom-pagination" aria-label="Page navigation example">
                        <ul class="pagination mb-0 justify-content-center">

                            <li class="page-item <?= ($page_num <= 1) ? 'disabled' : '' ?>">
                                <a class="page-link icon" href="?page=<?= $page_num - 1 ?>&search=<?= urlencode($search) ?>">
                                    <i class="material-symbols-outlined">west</i>
                                </a>
                            </li>

                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item">
                                    <a class="page-link <?= ($page_num == $i) ? 'active' : '' ?>" href="?page=<?= $i ?>&search=<?= urlencode($search) ?>">
                                        <?= $i ?>
                                    </a>
                                </li>
                            <?php endfor; ?>

                            <li class="page-item <?= ($page_num >= $total_pages) ? 'disabled' : '' ?>">
                                <a class="page-link icon" href="?page=<?= $page_num + 1 ?>&search=<?= urlencode($search) ?>">
                                    <i class="material-symbols-outlined">east</i>
                                </a>
                            </li>

                        </ul>
                    </nav>
                </div>
            <?php endif; ?>

        </div>
    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
