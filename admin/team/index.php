<?php
$page = 'team';
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
| COUNT DATA
|--------------------------------------------------------------------------
*/
if ($search !== '') {
    $count_sql = "SELECT COUNT(*) as total FROM team_members WHERE name LIKE ? OR position LIKE ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "ss", $search_like, $search_like);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = mysqli_fetch_assoc($count_result);
    mysqli_stmt_close($count_stmt);

    $total_data = (int) ($count_row['total'] ?? 0);
} else {
    $total_data = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM team_members"))[0];
}

$total_pages = ($total_data > 0) ? ceil($total_data / $limit) : 1;

if ($page_num > $total_pages) {
    $page_num = $total_pages;
}

$start = ($page_num - 1) * $limit;
$start_data = $total_data > 0 ? $start + 1 : 0;
$end_data = min($start + $limit, $total_data);

/*
|--------------------------------------------------------------------------
| GET DATA
|--------------------------------------------------------------------------
*/
if ($search !== '') {
    $sql = "SELECT * FROM team_members 
    WHERE name LIKE ? OR position LIKE ? 
    ORDER BY display_order ASC, id DESC 
    LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssii", $search_like, $search_like, $start, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT * FROM team_members ORDER BY display_order ASC, id DESC LIMIT $start, $limit";
    $result = mysqli_query($conn, $sql);
}
?>

<div class="main-content-container overflow-hidden">

    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Team Members</h3>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="#" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Team</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Team List</span>
                </li>
            </ol>
        </nav>
    </div>

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
            </div>

            <a href="create" class="text-primary fs-16 text-decoration-none"> + Add New Member</a>
        </div>
        <div class="default-table-area mx-minus-1 table-product-list">
            <div class="table-responsive">

                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Photo</th>
                            <th>Position</th>
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
                                            <?= htmlspecialchars($row['name']) ?>
                                        </a>
                                    </td>

                                    <td>
                                        <?php if (!empty($row['photo'])): ?>
                                            <img src="<?= $base_url ?>uploads/team/<?= htmlspecialchars($row['photo']) ?>" width="50" style="border-radius:6px;">
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>

                                    <td><?= htmlspecialchars($row['position']) ?></td>

                                    <td><?= (int)$row['display_order'] ?></td>

                                    <td>
                                        <?php if ($row['status'] === 'active'): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </td>

                             </tr>
                         <?php endwhile; ?>
                     <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center py-4">
                                No team members found.
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
