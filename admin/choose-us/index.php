<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'choose-us';
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
$total_query = mysqli_query($conn, "SELECT COUNT(*) FROM why_choose_us");
if (!$total_query) {
    die('Total query error: ' . mysqli_error($conn));
}
$total = (int) mysqli_fetch_row($total_query)[0];

$active_query = mysqli_query($conn, "SELECT COUNT(*) FROM why_choose_us WHERE is_active = 1");
if (!$active_query) {
    die('Active query error: ' . mysqli_error($conn));
}
$active = (int) mysqli_fetch_row($active_query)[0];

$inactive_query = mysqli_query($conn, "SELECT COUNT(*) FROM why_choose_us WHERE is_active = 0");
if (!$inactive_query) {
    die('Inactive query error: ' . mysqli_error($conn));
}
$inactive = (int) mysqli_fetch_row($inactive_query)[0];

/*
|--------------------------------------------------------------------------
| COUNT FILTERED DATA
|--------------------------------------------------------------------------
*/
if ($search !== '') {
    $count_sql = "SELECT COUNT(*) as total 
                  FROM why_choose_us 
                  WHERE title LIKE ? OR description LIKE ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);

    if (!$count_stmt) {
        die('Prepare count failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($count_stmt, "ss", $search_like, $search_like);
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
    $sql = "SELECT * FROM why_choose_us
            WHERE title LIKE ? OR description LIKE ?
            ORDER BY sort_order ASC, id DESC
            LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die('Prepare data failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ssii", $search_like, $search_like, $start, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM why_choose_us ORDER BY sort_order ASC, id DESC LIMIT $start, $limit";
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
        <h3 class="mb-0">Why Choose Us</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="#" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Why Choose Us</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Why Choose Us List</span>
                </li>
            </ol>
        </nav>
    </div>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
        <div class="alert alert-success mb-3">
            Why Choose Us deleted successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'created'): ?>
        <div class="alert alert-success mb-3">
            Why Choose Us created successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && $_GET['success'] === 'updated'): ?>
        <div class="alert alert-success mb-3">
            Why Choose Us updated successfully.
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger mb-3">
            <?php
            switch ($_GET['error']) {
                case 'invalid_id':
                    echo 'Invalid Why Choose Us ID.';
                    break;
                case 'not_found':
                    echo 'Why Choose Us data not found.';
                    break;
                case 'prepare_failed':
                    echo 'Failed to prepare query.';
                    break;
                case 'delete_failed':
                    echo 'Failed to delete Why Choose Us data.';
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
                    <li class="fs-16">
                        All Data <span class="text-primary">(<?= number_format($total) ?>)</span>
                    </li>
                    <li class="fs-16">
                        Active <span class="text-primary">(<?= number_format($active) ?>)</span>
                    </li>
                    <li class="fs-16">
                        Inactive <span class="text-primary">(<?= number_format($inactive) ?>)</span>
                    </li>
                </ul>
            </div>

            <a href="create.php" class="text-primary fs-16 text-decoration-none"> + Add New Why Choose Us</a>
        </div>

        <div class="default-table-area mx-minus-1 table-product-list">
            <div class="table-responsive">

                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>language</th>
                            <th>Icon</th>
                            <th>Description</th>
                            <th>Order</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php if ($total_data > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <a href="detail.php?id=<?= (int) $row['id'] ?>" class="text-primary text-decoration-none">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </a>
                                    </td>
                                    <td><?= htmlspecialchars($row['language']) ?></td>

                                    <td>
                                        <?php if (!empty($row['icon'])): ?>
                                            <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($row['icon']) ?>" width="50" style="border-radius:6px;">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td><?= htmlspecialchars(mb_strimwidth(strip_tags($row['description']), 0, 80, '...')) ?></td>

                                    <td><?= (int) $row['sort_order'] ?></td>

                                    <td>
                                        <?php if ((int)$row['is_active'] === 1): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center py-4">
                                    No data found.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>

            </div>

            <!-- PAGINATION -->
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
