<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$page = 'choose-us';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';
$success = '';

$title = '';
$description = '';
$sort_order = '0';
$is_active = '1';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order  = trim($_POST['sort_order'] ?? '0');
    $is_active   = trim($_POST['is_active'] ?? '1');

    $allowed_status = ['1', '0'];

    if (!isset($_FILES['icon']) || $_FILES['icon']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'Icon is required.';
    }

    if ($title === '') {
        $error = 'Title is required.';
    } elseif ($description === '') {
        $error = 'Description is required.';
    } elseif ($sort_order !== '' && !is_numeric($sort_order)) {
        $error = 'Sort order must be a valid number.';
    } elseif (!in_array($is_active, $allowed_status, true)) {
        $error = 'Invalid status.';
    } else {
        $icon_name = '';

        if (isset($_FILES['icon']) && $_FILES['icon']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['icon']['error'] === UPLOAD_ERR_OK) {
                $original_name = $_FILES['icon']['name'];
                $tmp_name      = $_FILES['icon']['tmp_name'];
                $file_size     = $_FILES['icon']['size'];

                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if ($ext === '') {
                    $error = 'File extension could not be detected.';
                } elseif (!in_array($ext, $allowed_ext, true)) {
                    $error = 'Only JPG, JPEG, PNG, and WEBP files are allowed.';
                } elseif ($file_size > 2 * 1024 * 1024) {
                    $error = 'Image size must not exceed 2MB.';
                } else {
                    $upload_dir = __DIR__ . '/../../uploads/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $icon_name = uniqid('', true) . '.webp';
                    $target_file = $upload_dir . $icon_name;

                    if (!convert_to_webp($tmp_name, $target_file, 80)) {
                        $error = 'Failed to convert image to WebP.';
                    }
                }
            } else {
                $error = 'An error occurred while uploading the icon.';
            }
        }

        if ($error === '') {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO why_choose_us (title, description, icon, sort_order, is_active, created_at)
                 VALUES (?, ?, ?, ?, ?, NOW())"
            );

            if ($stmt) {
                $sort_order_int = (int) $sort_order;
                $is_active_int = (int) $is_active;

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssii",
                    $title,
                    $description,
                    $icon_name,
                    $sort_order_int,
                    $is_active_int
                );

                if (mysqli_stmt_execute($stmt)) {
                    header('Location: ./?success=created');
                    exit;
                } else {
                    $error = 'Failed to save data: ' . mysqli_error($conn);
                }

                mysqli_stmt_close($stmt);
            } else {
                $error = 'Failed to prepare query: ' . mysqli_error($conn);
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Create Why Choose Us</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Why Choose Us</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Create Why Choose Us</span>
                </li>
            </ol>
        </nav>
    </div>

    <div class="card bg-white p-20 rounded-10 border border-white mb-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="row">

                <!-- LEFT -->
                <div class="col-lg-8">
                    <div class="row">

                        <!-- ID -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">ID</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="Auto Generate" readonly>
                                    <label>ID</label>
                                </div>
                            </div>
                        </div>

                        <!-- TITLE -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Title</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="title"
                                        value="<?= htmlspecialchars($title ?? '') ?>" required>
                                    <label>Title</label>
                                </div>
                            </div>
                        </div>

                        <!-- SORT ORDER -->
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Sort Order</label>
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="sort_order"
                                        value="<?= htmlspecialchars($sort_order ?? '0') ?>" required>
                                    <label>Sort Order</label>
                                </div>
                                <small class="text-secondary">Smaller number will appear first</small>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-4">

                    <!-- STATUS -->
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Status</label>
                        <div class="form-floating">
                            <select class="form-select" name="is_active" required>
                                <option value="1" <?= ($is_active === '1') ? 'selected' : '' ?>>Active</option>
                                <option value="0" <?= ($is_active === '0') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>

                    <!-- ICON -->
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Upload Icon</label>
                        <input type="file" class="form-control" name="icon" accept=".jpg,.jpeg,.png,.webp" required>
                    </div>

                </div>

                <!-- DESCRIPTION -->
                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Description</label>
                        <textarea class="form-control" name="description" rows="6" required><?= htmlspecialchars($description ?? '') ?></textarea>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">Save Data</button>
                        <a href="./" class="btn btn-danger text-white">Cancel</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="flex-grow-1"></div>

<?php include __DIR__ . '/../includes/footer.php'; ?>