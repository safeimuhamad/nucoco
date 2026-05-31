<?php
$page = 'choose_us';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid Why Choose Us ID.');
}

/*
|--------------------------------------------------------------------------
| Load existing data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM why_choose_us WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Failed to prepare query: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$item) {
    die('Why Choose Us data not found.');
}

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$title = $item['title'] ?? '';
$language = $item['language'] ?? '';
$description = $item['description'] ?? '';
$sort_order = $item['sort_order'] ?? 0;
$is_active = isset($item['is_active']) ? (string) $item['is_active'] : '1';
$current_icon = $item['icon'] ?? '';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $language    = trim($_POST['language'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $sort_order  = trim($_POST['sort_order'] ?? '0');
    $is_active   = trim($_POST['is_active'] ?? '1');

    $allowed_status = ['1', '0'];
    $allowed_languages = ['Indonesia', 'English'];

    if ($title === '') {
        $error = 'Title is required.';
    } elseif ($language === '') {
        $error = 'Language is required.';
    } elseif (!in_array($language, $allowed_languages, true)) {
        $error = 'Invalid language.';
    } elseif ($description === '') {
        $error = 'Description is required.';
    } elseif ($sort_order !== '' && !is_numeric($sort_order)) {
        $error = 'Sort order must be a valid number.';
    } elseif (!in_array($is_active, $allowed_status, true)) {
        $error = 'Invalid status.';
    } else {
        $icon_name = $current_icon;

        /*
        |--------------------------------------------------------------------------
        | Upload new icon (optional)
        |--------------------------------------------------------------------------
        */
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

                    $upload_dir = __DIR__ . '/../../uploads/why_choose_us/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $new_icon_name = uniqid('', true) . '.webp';
                    $target_file = $upload_dir . $new_icon_name;

                    if (!convert_to_webp($tmp_name, $target_file, 80)) {
                        $error = 'Failed to convert image to WebP.';
                    } else {

                        if (!empty($current_icon)) {
                            $old_icon_path = $upload_dir . $current_icon;
                            if (file_exists($old_icon_path)) {
                                @unlink($old_icon_path);
                            }
                        }

                        $icon_name = $new_icon_name;
                    }
                }
            } else {
                $error = 'An error occurred while uploading the icon.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update data
        |--------------------------------------------------------------------------
        */
        if ($error === '') {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE why_choose_us
                 SET title = ?, language = ?, description = ?, icon = ?, sort_order = ?, is_active = ?
                 WHERE id = ?"
            );

            if ($stmt) {

                $sort_order_int = (int) $sort_order;
                $is_active_int  = (int) $is_active;

                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssiii",
                    $title,
                    $language,
                    $description,
                    $icon_name,
                    $sort_order_int,
                    $is_active_int,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: ./?success=updated');
                    exit;
                } else {
                    $error = 'Failed to update data: ' . mysqli_error($conn);
                    mysqli_stmt_close($stmt);
                }
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
        <h3 class="mb-0">Edit Why Choose Us</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Why Choose Us</span>
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <span class="text-secondary">Edit Why Choose Us</span>
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

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Title</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="title"
                                        value="<?= htmlspecialchars($title) ?>" required>
                                    <label>Title</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Sort Order</label>
                                <div class="form-floating">
                                    <input type="number" class="form-control" name="sort_order"
                                        value="<?= htmlspecialchars((string)$sort_order) ?>" required>
                                    <label>Sort Order</label>
                                </div>
                                <small class="text-secondary">Smaller number will appear first</small>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Status</label>
                                <div class="form-floating">
                                    <select class="form-select" name="is_active">
                                        <option value="1" <?= ($is_active === '1') ? 'selected' : '' ?>>Active</option>
                                        <option value="0" <?= ($is_active === '0') ? 'selected' : '' ?>>Inactive</option>
                                    </select>
                                    <label>Status</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Language</label>
                                <div class="form-floating">
                                    <select class="form-select" name="language" required>
                                        <option value="">Select Language</option>
                                        <option value="Indonesia" <?= ($language === 'Indonesia') ? 'selected' : '' ?>>Indonesia</option>
                                        <option value="English" <?= ($language === 'English') ? 'selected' : '' ?>>English</option>
                                    </select>
                                    <label>Language</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-4">

                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Current Icon</label>

                        <?php if (!empty($current_icon)): ?>
                            <div class="mb-3">
                                <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($current_icon) ?>" width="100">
                            </div>
                        <?php else: ?>
                            <div class="mb-3 text-secondary">
                                No icon uploaded.
                            </div>
                        <?php endif; ?>

                        <input type="file" class="form-control" name="icon" accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-secondary">Leave empty if not changing</small>
                    </div>

                </div>

                <!-- DESCRIPTION -->
                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Description</label>
                        <textarea class="form-control" name="description" rows="6" required><?= htmlspecialchars($description) ?></textarea>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">
                            Update Why Choose Us
                        </button>
                        <a href="./" class="btn btn-danger text-white">
                            Cancel
                        </a>
                    </div>
                </div>

            </div>
        </form>

    </div>

</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>