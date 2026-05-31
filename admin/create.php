<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
$page = 'news';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';
$error = '';
$success = '';

$title = '';
$slug = '';
$category='';
$excerpt = '';
$status = 'draft';
$seo_keywords = '';
$content = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = generate_slug($title);
    $category = trim($_POST['category'] ?? '');
    $excerpt = trim($_POST['excerpt'] ?? '');
    $seo_keywords = trim($_POST['seo_keywords'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $content = trim($_POST['content'] ?? '');

    $allowed_status = ['publish', 'draft'];

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'News image is required.';
    }

    if ($title === '') {
        $error = 'News title is required.';
    } elseif ($slug === '') {
        $error = 'Slug is required.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid news status.';
    } else {
        $image_name = '';

        if (isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                $original_name = $_FILES['image']['name'];
                $tmp_name = $_FILES['image']['tmp_name'];
                $file_size = $_FILES['image']['size'];

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

                    $image_name = uniqid('', true) . '.webp';
                    $target_file = $upload_dir . $image_name;

                    if (!convert_to_webp($tmp_name, $target_file, 80)) {
                        $error = 'Failed to convert image to WebP.';
                    }
                }
            } else {
                $error = 'An error occurred while uploading the image.';
            }
        }

        if ($error === '') {
            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO news (title, slug, excerpt, content, image, status, seo_keywords, created_at)
                 VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
            );

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssss",
                    $title,
                    $category,
                    $slug,
                    $excerpt,
                    $content,
                    $image_name,
                    $status,
                    $seo_keywords
                );

                if (mysqli_stmt_execute($stmt)) {
                    header('Location: ./?success=created');
                    exit;
                } else {
                    $error = 'Failed to save news: ' . mysqli_error($conn);
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
        <h3 class="mb-0">Create News</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="index.html" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">News</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Create News</span>
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
                        <label class="label fs-16 mb-2">News ID</label>
                        <div class="form-floating">
                            <input type="text" class="form-control" value="Auto Generate" readonly>
                            <label>News ID</label>
                        </div>
                    </div>
                </div>

                <!-- NAME -->
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
            </div>
        </div>

        <!-- RIGHT -->
        <div class="col-lg-4">

            <!-- Category -->
            <div class="mb-20">
                <label class="label fs-16 mb-2">Category</label>
                <div class="form-floating">
                    <select class="form-select" name="$category," required>
                        <option value="Health & Benefits" <?= ($category=='Health & Benefits')?'selected':'' ?>>Health & Benefits</option>
                        <option value="Coconut Products" <?= ($category=='Coconut Products')?'selected':'' ?>>Coconut Products</option>
                        <option value="Recipes & Usage" <?= ($category=='Recipes & Usage')?'selected':'' ?>>Recipes & Usage</option>
                        <option value="Nutrition & Facts" <?= ($category=='Nutrition & Facts')?'selected':'' ?>>Nutrition & Facts</option>
                        <option value="Export & Supply" <?= ($category=='Export & Supply')?'selected':'' ?>>Export & Supply</option>
                        <option value="Farming & Sustainability" <?= ($category=='Farming & Sustainability')?'selected':'' ?>>Farming & Sustainability</option>
                        <option value="Industry & Business" <?= ($category=='Industry & Business')?'selected':'' ?>>Industry & Business</option>
                        <option value="Company News" <?= ($category=='Company News')?'selected':'' ?>>Company News</option>
                    </select>
                    <label>Category</label>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <!-- STATUS -->
            <div class="mb-20">
                <label class="label fs-16 mb-2">Status</label>
                <div class="form-floating">
                    <select class="form-select" name="status" required>
                        <option value="draft" <?= ($status=='draft')?'selected':'' ?>>Draft</option>
                        <option value="publish" <?= ($status=='publish')?'selected':'' ?>>Publish</option>
                    </select>
                    <label>Status</label>
                </div>
            </div>
        </div>
        <!-- DESCRIPTION -->
        <div class="col-8">
            <div class="mb-20">
                <label class="label fs-16 mb-2">Content</label>
                <textarea class="form-control" name="description" rows="6"><?= htmlspecialchars($content ?? '') ?></textarea>
            </div>
        </div>
        <div class="col-4">
            <div class="mb-20">
                <label class="label fs-16 mb-2">SEO Keywords</label>
                <textarea class="form-control" name="seo_keywords" rows="5"><?= htmlspecialchars($seo_keywords ?? '') ?></textarea>
            </div>
        </div>

        <!-- UPLOAD -->
        <div class="col-2">
            <div class="mb-20">
                <label class="label fs-16 mb-2">Upload Image</label>
                <input type="file" class="form-control" name="image">
            </div>
        </div>

        <!-- BUTTON -->
        <div class="col-12">
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary text-white">Save News</button>
                <a href="./" class="btn btn-danger text-white">Cancel</a>
            </div>
        </div>

    </div>
</form>
    </div>

    <div class="flex-grow-1"></div>
    <?php
    include __DIR__ . '/../includes/footer.php';
?>