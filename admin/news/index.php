        <!-- Start Main Content Area -->
        <?php
        $page = 'news';
        include __DIR__ . '/../includes/auth.php';
        require_once __DIR__ . '/../includes/db.php';

        ini_set('display_errors', 1);
        error_reporting(E_ALL);
        mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../includes/sidebar.php';

        $limit = 10;
        $page_num = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $page_num = max($page_num, 1);

        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $search_like = '%' . $search . '%';
        $language = trim($_GET['language'] ?? 'id');
        $parent_id = trim($_GET['parent_id'] ?? '');

/*
|--------------------------------------------------------------------------
| Summary counts
|--------------------------------------------------------------------------
*/
$total = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM news"))[0];
$publish = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM news WHERE status='publish'"))[0];
$draft = (int) mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM news WHERE status='draft'"))[0];

/*
|--------------------------------------------------------------------------
| Count filtered data
|--------------------------------------------------------------------------
*/
if ($search !== '') {
    $count_sql = "
    SELECT COUNT(*) as total
    FROM news
    WHERE title LIKE ?
    OR content LIKE ?
    OR category LIKE ?
    OR slug LIKE ?
    ";
    $count_stmt = mysqli_prepare($conn, $count_sql);

    if (!$count_stmt) {
        die('Count prepare failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($count_stmt, "ssss", $search_like, $search_like, $search_like, $search_like);
    mysqli_stmt_execute($count_stmt);
    $count_result = mysqli_stmt_get_result($count_stmt);
    $count_row = mysqli_fetch_assoc($count_result);
    mysqli_stmt_close($count_stmt);

    $total_data = (int) ($count_row['total'] ?? 0);
} else {
    $total_data = $total;
}

$total_pages = ($total_data > 0) ? (int) ceil($total_data / $limit) : 1;

if ($page_num > $total_pages) {
    $page_num = $total_pages;
}

$start = ($page_num - 1) * $limit;

/*
|--------------------------------------------------------------------------
| Get news data with translation info
|--------------------------------------------------------------------------
*/
$sql = "
SELECT
n.*,
p.id AS parent_news_id,
p.title AS parent_title,
EXISTS (
    SELECT 1
    FROM news child_en
    WHERE child_en.parent_id = n.id
    AND child_en.language = 'en'
    ) AS has_en_translation
FROM news n
LEFT JOIN news p ON p.id = n.parent_id
";

if ($search !== '') {
    $sql .= "
    WHERE n.title LIKE ?
    OR n.content LIKE ?
    OR n.category LIKE ?
    OR n.slug LIKE ?
    ";
}

$sql .= " ORDER BY n.id DESC LIMIT ?, ?";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die('List prepare failed: ' . mysqli_error($conn));
}

if ($search !== '') {
    mysqli_stmt_bind_param($stmt, "ssssii", $search_like, $search_like, $search_like, $search_like, $start, $limit);
} else {
    mysqli_stmt_bind_param($stmt, "ii", $start, $limit);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die('List query failed: ' . mysqli_error($conn));
}

$start_data = ($total_data > 0) ? $start + 1 : 0;
$end_data = ($total_data > 0) ? min($start + $limit, $total_data) : 0;
?>
<?php if (isset($_GET['success']) && $_GET['success'] === 'deleted'): ?>
    <div class="alert alert-success mb-3">
        News deleted successfully.
    </div>
<?php endif; ?>

<?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger mb-3">
        <?php
        switch ($_GET['error']) {
            case 'invalid_id':
            echo 'Invalid news ID.';
            break;
            case 'not_found':
            echo 'News not found.';
            break;
            case 'delete_failed':
            echo 'Failed to delete news.';
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
                    All news <span class="text-primary">(<?= number_format($total) ?>)</span>
                </li>
                <li class="fs-16">
                    Published news <span class="text-primary">(<?= number_format($publish) ?>)</span>
                </li>
                <li class="fs-16">
                    Drafts news <span class="text-primary">(<?= number_format($draft) ?>)</span>
                </li>
            </ul>
        </div>

        <a href="create" class="text-primary fs-16 text-decoration-none"> + Add News </a>
    </div>
    <div class="default-table-area mx-minus-1 table-News-list">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th scope="col" class="fw-medium">
                            <div class="form-check position-relative" style="top: -3px;">
                                <input class="form-check-input" type="checkbox" id="flexCheckDefault1">
                            </div>
                        </th>
                        <th scope="col" class="fw-medium">Language</th>
                        <th scope="col" class="fw-medium">Title</th>
                        <th scope="col" class="fw-medium">Category</th>
                        <th scope="col" class="fw-medium">Translation</th>
                        <th scope="col" class="fw-medium">Linked Article</th>
                        <th scope="col" class="fw-medium">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total_data > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $translation_status = 'Unknown';
                            $translation_badge_class = 'bg-secondary bg-opacity-10 text-secondary';
                            $linked_article = '-';
                            $can_add_en = false;

                            if ($row['language'] === 'id') {
                                if ((int) $row['has_en_translation'] === 1) {
                                    $translation_status = 'Complete';
                                    $translation_badge_class = 'bg-success bg-opacity-10 text-success';
                                } else {
                                    $translation_status = 'Missing EN';
                                    $translation_badge_class = 'bg-warning bg-opacity-10 text-warning';
                                    $can_add_en = true;
                                }

                                $linked_article = 'Root';
                            } elseif ($row['language'] === 'en') {
                                if (!empty($row['parent_news_id'])) {
                                    $translation_status = 'Linked';
                                    $translation_badge_class = 'bg-primary bg-opacity-10 text-primary';
                                    $linked_article = '#' . (int) $row['parent_news_id'] . ' - ' . htmlspecialchars($row['parent_title']);
                                } else {
                                    $translation_status = 'Broken Pair';
                                    $translation_badge_class = 'bg-danger bg-opacity-10 text-danger';
                                    $linked_article = 'Broken Pair';
                                }
                            }
                            ?>
                            <tr>
                                <td><input type="checkbox"></td>

                                <td>
                                    <span class="badge bg-light text-dark text-uppercase">
                                        <?= htmlspecialchars($row['language'] ?? '-') ?>
                                    </span>
                                </td>

                                <td>
                                    <div style="display:flex; align-items:center; gap:10px; min-width:260px;">
                                        <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($row['image']) ?>" width="40" alt="">
                                        <a href="<?= $admin_base_url ?>news/detail.php?id=<?= (int) $row['id'] ?>" class="text-primary text-decoration-none">
                                            <?= htmlspecialchars($row['title']) ?>
                                        </a>
                                    </div>
                                </td>

                                <td><?= htmlspecialchars($row['category'] ?? '-') ?></td>

                                <td>
                                    <span class="badge <?= $translation_badge_class ?>">
                                        <?= $translation_status ?>
                                    </span>
                                </td>

                                <td><?= $linked_article ?></td>

                                <td><?= htmlspecialchars($row['status']) ?></td>

                       </tr>
                   <?php endwhile; ?>
               <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center py-4">
                        No news found. Please add a new news to get started.
                    </td>
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

        <?php if ($total_pages > 1): ?>
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
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>
</div>
</div>
<div class="flex-grow-1"></div>
<?php
include __DIR__ . '/../includes/footer.php';
?>        <!-- Start Main Content Area -->
