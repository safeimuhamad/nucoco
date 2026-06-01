<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'services';

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

$error = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid service ID.');
}

/*
|--------------------------------------------------------------------------
| Load existing service
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM services WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$service = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$service) {
    die('Service not found.');
}

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$title = $service['title'] ?? '';
$category = $service['category'] ?? '';
$language = $service['language'] ?? 'id';
$status = $service['status'] ?? 'draft';
$seo_keywords = $service['seo_keywords'] ?? '';
$content = $service['content'] ?? '';
$quotation_description = $service['quotation_description'] ?? '';
$current_image = $service['image'] ?? '';
$current_image = $service['image'] ?? '';
$current_image_mobile  = $service['image_mobile'] ?? '';
$current_image_tablet  = $service['image_tablet'] ?? '';
$current_image_desktop = $service['image_desktop'] ?? '';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = generate_slug($title);
    $category = trim($_POST['category'] ?? '');
    $language = trim($_POST['language'] ?? 'id');
    $seo_keywords = trim($_POST['seo_keywords'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $content = trim($_POST['content'] ?? '');
    $quotation_description = trim($_POST['quotation_description'] ?? '');
    if ($quotation_description === '' && $title !== '') {
        $quotation_description = product_default_quotation_description($title, $language, 'service');
    }

    $allowed_status = ['publish', 'draft'];
    $allowed_language = ['id', 'en'];

    if ($title === '') {
        $error = 'Service title is required.';
    } elseif ($category === '') {
        $error = 'Category is required.';
    } elseif (!in_array($language, $allowed_language, true)) {
        $error = 'Invalid language.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid status.';
    } else {
        $image_name = $current_image;
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $tmp_name = $_FILES['image']['tmp_name'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed_ext, true)) {
                $error = 'Invalid image format.';
            } else {
                $upload_dir = __DIR__ . '/../../uploads/services/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $base_name = uniqid('', true);

                $image_name         = $base_name . '.webp';
                $image_mobile_name  = $base_name . '-480.webp';
                $image_tablet_name  = $base_name . '-768.webp';
                $image_desktop_name = $base_name . '-1200.webp';

                $target_original = $upload_dir . $image_name;
                $target_mobile   = $upload_dir . $image_mobile_name;
                $target_tablet   = $upload_dir . $image_tablet_name;
                $target_desktop  = $upload_dir . $image_desktop_name;

                if (
                    !resize_to_webp($tmp_name, $target_mobile, 480, 60) ||
                    !resize_to_webp($tmp_name, $target_tablet, 768, 65) ||
                    !resize_to_webp($tmp_name, $target_desktop, 1200, 68)
                ) {
                    $error = 'Failed to upload image.';
                } else {

    // hapus semua file lama
                    $old_files = [
                        $current_image,
                        $current_image_mobile,
                        $current_image_tablet,
                        $current_image_desktop
                    ];

                    foreach ($old_files as $old) {
                        if (!empty($old)) {
                            $path = $upload_dir . $old;
                            if (file_exists($path)) {
                                @unlink($path);
                            }
                        }
                    }

    // original = desktop copy
                    
                    if (!copy($target_desktop, $target_original)) {
                        $error = 'Failed to finalize image.';
                    }
                }
            }
        }

        if ($error === '') {

            // ✅ FIX: kalau tidak upload gambar baru
            if (empty($image_mobile_name)) {
                $image_name         = $current_image;
                $image_mobile_name  = $current_image_mobile;
                $image_tablet_name  = $current_image_tablet;
                $image_desktop_name = $current_image_desktop;
            }
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE services 
                SET title = ?, slug = ?, category = ?, language = ?, 
                image = ?, image_mobile = ?, image_tablet = ?, image_desktop = ?, 
                content = ?, quotation_description = ?, status = ?, seo_keywords = ?
                WHERE id = ?"
            );

            if (!$stmt) {
                die('Prepare failed: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "ssssssssssssi",
                $title,
                $slug,
                $category,
                $language,
                $image_name,
                $image_mobile_name,
                $image_tablet_name,
                $image_desktop_name,
                $content,
                $quotation_description,
                $status,
                $seo_keywords,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                header('Location: ./?success=updated');
                clearstatcache();
                exit;
            } else {
                $error = 'Failed to update service: ' . mysqli_error($conn);
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
        <h3 class="mb-0">Edit Service</h3>
    </div>

    <div class="card bg-white p-20 rounded-10 border border-white mb-4">
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mb-3">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form method="POST" enctype="multipart/form-data">
            <div class="row">
                <div class="col-md-8 mb-20">
                    <label class="label fs-16 mb-2">Service Title</label>
                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($title) ?>" required>
                </div>
                <div class="col-md-4 mb-20">
                    <label class="label fs-16 mb-2">Category</label>
                    <select class="form-select" name="category" required>
                        <option value="">Select Category</option>
                        <option value="OEM & Private Label" <?= ($category == 'OEM & Private Label') ? 'selected' : '' ?>>OEM & Private Label</option>
                        <option value="Global Export & Supply" <?= ($category == 'Global Export & Supply') ? 'selected' : '' ?>>Global Export & Supply</option>
                        <option value="Industrial Bulk Supply" <?= ($category == 'Industrial Bulk Supply') ? 'selected' : '' ?>>Industrial Bulk Supply</option>
                        <option value="Product Customization" <?= ($category == 'Product Customization') ? 'selected' : '' ?>>Product Customization</option>
                        <option value="Sourcing & Consolidation" <?= ($category == 'Sourcing & Consolidation') ? 'selected' : '' ?>>Sourcing & Consolidation</option>
                        <option value="Quality & Certification Support" <?= ($category == 'Quality & Certification Support') ? 'selected' : '' ?>>Quality & Certification Support</option>
                    </select>
                </div>
                <div class="col-md-8 mb-20">
                    <label class="label fs-16 mb-2">Web Content</label>
                    <textarea class="form-control" name="content" rows="6"><?= htmlspecialchars($content) ?></textarea>
                </div>
                <div class="col-md-8 mb-20">
                    <label class="label fs-16 mb-2">Quotation Description</label>
                    <textarea class="form-control" name="quotation_description" rows="4" placeholder="Short description shown in quotations"><?= htmlspecialchars($quotation_description) ?></textarea>
                </div>
                <div class="col-md-4 mb-20">
                    <label class="label fs-16 mb-2">Language</label>
                    <select class="form-select" name="language" required>
                        <option value="id" <?= ($language === 'id') ? 'selected' : '' ?>>Indonesia</option>
                        <option value="en" <?= ($language === 'en') ? 'selected' : '' ?>>English</option>
                    </select>
                    <label class="label fs-16 mb-2">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="publish" <?= ($status === 'publish') ? 'selected' : '' ?>>Publish</option>
                    </select>
                    <label class="label fs-16 mb-2">Upload Image</label>

                    <?php if (!empty($current_image)): ?>
                        <div class="mb-2">
                            <img src="<?= $base_url ?>uploads/services/<?= htmlspecialchars($current_image_desktop ?: $current_image) ?>" width="150">
                        </div>
                    <?php endif; ?>

                    <input type="file" class="form-control" name="image">
                    <small class="text-secondary">Leave empty if you do not want to change the image.</small>
                </div>
                <div class="col-md-8 mb-20">
                    <label class="label fs-16 mb-2">SEO Keywords</label>
                    <textarea class="form-control" name="seo_keywords" rows="6"><?= htmlspecialchars($seo_keywords) ?></textarea>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary text-white">Update Service</button>
                    <a href="./" class="btn btn-danger text-white">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
