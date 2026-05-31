<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'pages';

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ./index.php?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Load existing page
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM pages WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$page_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$page_data) {
    header('Location: ./index.php?error=not_found');
    exit;
}

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$page_name = $page_data['page_name'] ?? '';
$slug = $page_data['slug'] ?? '';
$slug_input = $page_data['slug'] ?? '';
$status = $page_data['status'] ?? 'draft';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $page_name = trim($_POST['page_name'] ?? '');
    $slug_input = trim($_POST['slug'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');

    $allowed_status = ['publish', 'draft'];

    // kalau slug tidak diubah user / masih sama dengan slug lama, generate dari page name terbaru
    if ($slug_input === '' || $slug_input === ($page_data['slug'] ?? '')) {
        $slug = generate_slug($page_name);
    } else {
        $slug = generate_slug($slug_input);
    }

    if ($page_name === '') {
        $error = 'Page name is required.';
    } elseif ($slug === '') {
        $error = 'Page slug is required.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid status.';
    } else {
        $check_stmt = mysqli_prepare($conn, "SELECT id FROM pages WHERE slug = ? AND id != ? LIMIT 1");

        if (!$check_stmt) {
            die('Prepare failed: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($check_stmt, "si", $slug, $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $existing_page = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);

        if ($existing_page) {
            $error = 'Slug already exists. Please use another slug.';
        } else {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE pages
                 SET page_name = ?, slug = ?, status = ?
                 WHERE id = ?"
            );

            if (!$stmt) {
                die('Prepare failed: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param($stmt, "sssi", $page_name, $slug, $status, $id);

            if (mysqli_stmt_execute($stmt)) {
                header('Location: ./index.php?success=updated');
                exit;
            } else {
                $error = 'Failed to update page: ' . mysqli_error($conn);
            }

            mysqli_stmt_close($stmt);
        }
    }
}
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Edit Page</h3>
    </div>

    <div class="card bg-white p-20 rounded-10 border border-white mb-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="row">
                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Page Name</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        name="page_name" 
                        value="<?= htmlspecialchars($page_name) ?>" 
                        required
                    >
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Slug</label>
                    <input 
                        type="text" 
                        class="form-control" 
                        name="slug" 
                        value="<?= htmlspecialchars($slug_input) ?>"
                        placeholder="Auto generate from page name if empty"
                    >
                </div>

                <div class="col-md-4 mb-20">
                    <label class="label fs-16 mb-2">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="publish" <?= ($status === 'publish') ? 'selected' : '' ?>>Publish</option>
                    </select>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary text-white">Update Page</button>
                    <a href="./index.php" class="btn btn-danger text-white">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>