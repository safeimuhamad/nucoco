<?php
$page = 'testimonial';
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid testimonial ID.');
}

/*
|--------------------------------------------------------------------------
| Load existing testimonial
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM testimonials WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Failed to prepare query: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$testimonial = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$testimonial) {
    die('Testimonial not found.');
}

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$name = $testimonial['name'] ?? '';
$language = $testimonial['language'] ?? 'English';
$position = $testimonial['position'] ?? '';
$content = $testimonial['content'] ?? '';
$display_order = $testimonial['display_order'] ?? 0;
$status = $testimonial['status'] ?? 'active';
$current_photo = $testimonial['photo'] ?? '';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = trim($_POST['name'] ?? '');
    $language      = trim($_POST['language'] ?? '');
    $position      = trim($_POST['position'] ?? '');
    $content       = trim($_POST['content'] ?? '');
    $display_order = trim($_POST['display_order'] ?? '0');
    $status        = trim($_POST['status'] ?? 'active');

    $allowed_status = ['active', 'inactive'];
    $allowed_languages = ['Indonesia', 'English'];

    if ($name === '') {
        $error = 'Client name is required.';
    } elseif ($language === '') {
        $error = 'Language is required.';
    } elseif (!in_array($language, $allowed_languages, true)) {
        $error = 'Invalid language.';
    } elseif ($content === '') {
        $error = 'Testimonial content is required.';
    } elseif ($display_order !== '' && !is_numeric($display_order)) {
        $error = 'Display order must be a valid number.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid testimonial status.';
    } else {
        $photo_name = $current_photo;

        /*
        |--------------------------------------------------------------------------
        | Upload new photo (optional)
        |--------------------------------------------------------------------------
        */
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {

            if ($_FILES['photo']['error'] === UPLOAD_ERR_OK) {

                $original_name = $_FILES['photo']['name'];
                $tmp_name      = $_FILES['photo']['tmp_name'];
                $file_size     = $_FILES['photo']['size'];

                $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

                if ($ext === '') {
                    $error = 'File extension could not be detected.';
                } elseif (!in_array($ext, $allowed_ext, true)) {
                    $error = 'Only JPG, JPEG, PNG, and WEBP files are allowed.';
                } elseif ($file_size > 2 * 1024 * 1024) {
                    $error = 'Image size must not exceed 2MB.';
                } else {

                    $upload_dir = __DIR__ . '/../../uploads/testimonials/';

                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }

                    $new_photo_name = uniqid('', true) . '.webp';
                    $target_file = $upload_dir . $new_photo_name;

                    if (!convert_to_webp($tmp_name, $target_file, 80)) {
                        $error = 'Failed to convert image to WebP.';
                    } else {

                        if (!empty($current_photo)) {
                            $old_photo_path = $upload_dir . $current_photo;
                            if (file_exists($old_photo_path)) {
                                @unlink($old_photo_path);
                            }
                        }

                        $photo_name = $new_photo_name;
                    }
                }
            } else {
                $error = 'An error occurred while uploading the image.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update testimonial
        |--------------------------------------------------------------------------
        */
        if ($error === '') {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE testimonials
                 SET name = ?, language = ?, position = ?, content = ?, photo = ?, display_order = ?, status = ?
                 WHERE id = ?"
            );

            if ($stmt) {

                $display_order_int = (int) $display_order;

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssisi",
                    $name,
                    $language,
                    $position,
                    $content,
                    $photo_name,
                    $display_order_int,
                    $status,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header('Location: ./?success=updated');
                    exit;
                } else {
                    $error = 'Failed to update testimonial: ' . mysqli_error($conn);
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
        <h3 class="mb-0">Edit Testimonial</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">Testimonial</span>
                    </a>
                </li>
                <li class="breadcrumb-item active">
                    <span class="text-secondary">Edit Testimonial</span>
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
                                <label class="label fs-16 mb-2">Client Name</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="name"
                                        value="<?= htmlspecialchars($name) ?>" required>
                                    <label>Client Name</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Language</label>
                                <div class="form-floating">
                                    <select class="form-select" name="language" required>
                                        <option value="Indonesia" <?= ($language === 'Indonesia') ? 'selected' : '' ?>>Indonesia</option>
                                        <option value="English" <?= ($language === 'English') ? 'selected' : '' ?>>English</option>
                                    </select>
                                    <label>Language</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Position</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="position"
                                        value="<?= htmlspecialchars($position) ?>">
                                    <label>Position</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-4">

                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Display Order</label>
                        <div class="form-floating">
                            <input type="number" class="form-control" name="display_order"
                                value="<?= htmlspecialchars((string)$display_order) ?>" required>
                            <label>Display Order</label>
                        </div>
                    </div>

                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Status</label>
                        <div class="form-floating">
                            <select class="form-select" name="status">
                                <option value="active" <?= ($status === 'active') ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= ($status === 'inactive') ? 'selected' : '' ?>>Inactive</option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>

                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Current Photo</label>

                        <?php if (!empty($current_photo)): ?>
                            <div class="mb-3">
                                <img src="<?= $base_url ?>uploads/testimonials/<?= htmlspecialchars($current_photo) ?>" width="100">
                            </div>
                        <?php endif; ?>

                        <input type="file" class="form-control" name="photo" accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-secondary">Leave empty if not changing</small>
                    </div>

                </div>

                <!-- CONTENT -->
                <div class="col-12">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Testimonial Content</label>
                        <textarea class="form-control" name="content" rows="6" required><?= htmlspecialchars($content) ?></textarea>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">
                            Update Testimonial
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