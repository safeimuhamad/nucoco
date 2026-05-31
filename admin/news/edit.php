<?php
$page = 'news';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/db.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

$error = '';
$success = '';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    die('Invalid news ID.');
}

/*
|--------------------------------------------------------------------------
| Load existing news
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM news WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Failed to prepare query: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$news) {
    die('News not found.');
}

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$title = $news['title'] ?? '';
$slug = $news['slug'] ?? '';
$language = $news['language'] ?? 'id';
$category = $news['category'] ?? '';
$status = $news['status'] ?? 'draft';
$seo_keywords = $news['seo_keywords'] ?? '';
$content = $news['content'] ?? '';
$current_image = $news['image'] ?? '';
$translation_group_id = (int) ($news['translation_group_id'] ?? 0);
$parent_id = (int) ($news['parent_id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Load parent candidates
| - tampilkan artikel ID yang belum punya EN
| - plus parent yang sedang dipakai oleh article ini
|--------------------------------------------------------------------------
*/
$parent_articles = [];

$parent_sql = "
    SELECT n.id, n.title, n.translation_group_id
    FROM news n
    WHERE n.language = 'id'
      AND (
            NOT EXISTS (
                SELECT 1
                FROM news en
                WHERE en.parent_id = n.id
                  AND en.language = 'en'
                  AND en.id != ?
            )
            OR n.id = ?
      )
    ORDER BY n.created_at DESC
";

$parent_stmt = mysqli_prepare($conn, $parent_sql);
if ($parent_stmt) {
    mysqli_stmt_bind_param($parent_stmt, "ii", $id, $parent_id);
    mysqli_stmt_execute($parent_stmt);
    $parent_result = mysqli_stmt_get_result($parent_stmt);

    while ($row = mysqli_fetch_assoc($parent_result)) {
        $parent_articles[] = $row;
    }

    mysqli_stmt_close($parent_stmt);
}

/*
|--------------------------------------------------------------------------
| Handle update
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = generate_slug($title);
    $language = trim($_POST['language'] ?? 'id');
    $category = trim($_POST['category'] ?? '');
    $seo_keywords = trim($_POST['seo_keywords'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $content = trim($_POST['content'] ?? '');
    $parent_id = (int) ($_POST['parent_id'] ?? 0);

    $allowed_status = ['publish', 'draft'];
    $allowed_languages = ['id', 'en'];

    if ($title === '') {
        $error = 'News title is required.';
    } elseif ($slug === '') {
        $error = 'Slug is required.';
    } elseif (!in_array($language, $allowed_languages, true)) {
        $error = 'Invalid language.';
    } elseif ($category === '') {
        $error = 'Category is required.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid news status.';
    } elseif ($language === 'en' && $parent_id <= 0) {
        $error = 'Parent article (Indonesia) is required for English version.';
    } else {
        /*
        |--------------------------------------------------------------------------
        | Validate unique slug per language except current ID
        |--------------------------------------------------------------------------
        */
        $slug_stmt = mysqli_prepare($conn, "
            SELECT id
            FROM news
            WHERE slug = ? AND language = ? AND id != ?
            LIMIT 1
        ");

        if ($slug_stmt) {
            mysqli_stmt_bind_param($slug_stmt, "ssi", $slug, $language, $id);
            mysqli_stmt_execute($slug_stmt);
            $slug_result = mysqli_stmt_get_result($slug_stmt);

            if (mysqli_fetch_assoc($slug_result)) {
                $error = 'Slug already exists for this language.';
            }

            mysqli_stmt_close($slug_stmt);
        } else {
            $error = 'Failed to validate slug: ' . mysqli_error($conn);
        }

        $new_translation_group_id = $translation_group_id;
        $new_parent_id = $parent_id;

        /*
        |--------------------------------------------------------------------------
        | Validate parent for EN / normalize parent for ID
        |--------------------------------------------------------------------------
        */
        if ($error === '') {
            if ($language === 'id') {
                // artikel Indonesia selalu menjadi root
                $new_parent_id = $id;
                $new_translation_group_id = $translation_group_id > 0 ? $translation_group_id : $id;
            } else {
                $parent_stmt = mysqli_prepare($conn, "
                    SELECT id, language, translation_group_id
                    FROM news
                    WHERE id = ?
                    LIMIT 1
                ");

                if ($parent_stmt) {
                    mysqli_stmt_bind_param($parent_stmt, "i", $parent_id);
                    mysqli_stmt_execute($parent_stmt);
                    $parent_result = mysqli_stmt_get_result($parent_stmt);
                    $parent_data = mysqli_fetch_assoc($parent_result);
                    mysqli_stmt_close($parent_stmt);

                    if (!$parent_data) {
                        $error = 'Selected parent article not found.';
                    } elseif ($parent_data['language'] !== 'id') {
                        $error = 'Parent article must be Indonesian version.';
                    } else {
                        $new_parent_id = (int) $parent_data['id'];
                        $new_translation_group_id = !empty($parent_data['translation_group_id'])
                            ? (int) $parent_data['translation_group_id']
                            : (int) $parent_data['id'];

                        // Pastikan parent belum dipakai English lain selain current record
                        $check_en_stmt = mysqli_prepare($conn, "
                            SELECT id
                            FROM news
                            WHERE parent_id = ? AND language = 'en' AND id != ?
                            LIMIT 1
                        ");

                        if ($check_en_stmt) {
                            mysqli_stmt_bind_param($check_en_stmt, "ii", $new_parent_id, $id);
                            mysqli_stmt_execute($check_en_stmt);
                            $check_en_result = mysqli_stmt_get_result($check_en_stmt);

                            if (mysqli_fetch_assoc($check_en_result)) {
                                $error = 'English version for this Indonesian article already exists.';
                            }

                            mysqli_stmt_close($check_en_stmt);
                        } else {
                            $error = 'Failed to validate English pair: ' . mysqli_error($conn);
                        }
                    }
                } else {
                    $error = 'Failed to validate parent article: ' . mysqli_error($conn);
                }
            }
        }

        $image_name = $current_image;

        /*
        |--------------------------------------------------------------------------
        | Upload new image (optional)
        |--------------------------------------------------------------------------
        */
        if ($error === '' && isset($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
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

                    $new_image_name = uniqid('', true) . '.webp';
                    $target_file = $upload_dir . $new_image_name;

                    if (!convert_to_webp($tmp_name, $target_file, 80)) {
                        $error = 'Failed to convert image to WebP.';
                    } else {
                        if (!empty($current_image)) {
                            $old_image_path = $upload_dir . $current_image;
                            if (file_exists($old_image_path)) {
                                @unlink($old_image_path);
                            }
                        }

                        $image_name = $new_image_name;
                    }
                }
            } else {
                $error = 'An error occurred while uploading the image.';
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Update news
        |--------------------------------------------------------------------------
        */
        if ($error === '') {
            $stmt = mysqli_prepare(
                $conn,
                "UPDATE news
                 SET title = ?, slug = ?, language = ?, category = ?, content = ?, image = ?, status = ?, seo_keywords = ?, translation_group_id = ?, parent_id = ?, updated_at = NOW()
                 WHERE id = ?"
            );

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "ssssssssiii",
                    $title,
                    $slug,
                    $language,
                    $category,
                    $content,
                    $image_name,
                    $status,
                    $seo_keywords,
                    $new_translation_group_id,
                    $new_parent_id,
                    $id
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);

                    /*
                    |--------------------------------------------------------------------------
                    | Jika article ini Indonesia/root, pastikan translation_group_id = id sendiri
                    |--------------------------------------------------------------------------
                    */
                    if ($language === 'id') {
                        $sync_stmt = mysqli_prepare($conn, "
                            UPDATE news
                            SET translation_group_id = ?, parent_id = ?, updated_at = NOW()
                            WHERE id = ?
                        ");

                        if ($sync_stmt) {
                            mysqli_stmt_bind_param($sync_stmt, "iii", $id, $id, $id);
                            mysqli_stmt_execute($sync_stmt);
                            mysqli_stmt_close($sync_stmt);
                        }
                    }

                    header('Location: ./?success=updated');
                    exit;
                } else {
                    $error = 'Failed to update news: ' . mysqli_error($conn);
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
        <h3 class="mb-0">Edit News</h3>

        <nav aria-label="breadcrumb">
            <ol class="breadcrumb align-items-center mb-0 lh-1">
                <li class="breadcrumb-item">
                    <a href="./" class="d-flex align-items-center text-decoration-none">
                        <i class="ri-home-8-line fs-15 text-primary me-1"></i>
                        <span class="text-body fs-14 hover">News</span>
                    </a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">
                    <span class="text-secondary">Edit News</span>
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
                                <label class="label fs-16 mb-2">News ID</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="<?= (int) $id ?>" readonly>
                                    <label>News ID</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Title</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" name="title" value="<?= htmlspecialchars($title) ?>" required>
                                    <label>Title</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Category</label>
                                <div class="form-floating">
                                    <select class="form-select" name="category" required>
                                        <option value="">-- Select Category --</option>
                                        <option value="Health & Benefits" <?= ($category === 'Health & Benefits') ? 'selected' : '' ?>>Health & Benefits</option>
                                        <option value="Coconut Products" <?= ($category === 'Coconut Products') ? 'selected' : '' ?>>Coconut Products</option>
                                        <option value="Recipes & Usage" <?= ($category === 'Recipes & Usage') ? 'selected' : '' ?>>Recipes & Usage</option>
                                        <option value="Nutrition & Facts" <?= ($category === 'Nutrition & Facts') ? 'selected' : '' ?>>Nutrition & Facts</option>
                                        <option value="Export & Supply" <?= ($category === 'Export & Supply') ? 'selected' : '' ?>>Export & Supply</option>
                                        <option value="Farming & Sustainability" <?= ($category === 'Farming & Sustainability') ? 'selected' : '' ?>>Farming & Sustainability</option>
                                        <option value="Industry & Business" <?= ($category === 'Industry & Business') ? 'selected' : '' ?>>Industry & Business</option>
                                        <option value="Company News" <?= ($category === 'Company News') ? 'selected' : '' ?>>Company News</option>
                                    </select>
                                    <label>Category</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Status</label>
                                <div class="form-floating">
                                    <select class="form-select" name="status" required>
                                        <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
                                        <option value="publish" <?= ($status === 'publish') ? 'selected' : '' ?>>Publish</option>
                                    </select>
                                    <label>Status</label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- RIGHT -->
                <div class="col-lg-4">

                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Language</label>
                        <div class="form-floating">
                            <select class="form-select" name="language" id="language" required>
                                <option value="id" <?= ($language === 'id') ? 'selected' : '' ?>>Indonesia</option>
                                <option value="en" <?= ($language === 'en') ? 'selected' : '' ?>>English</option>
                            </select>
                            <label>Language</label>
                        </div>
                    </div>

                    <div class="mb-20" id="parent-wrapper" style="<?= ($language === 'en') ? '' : 'display:none;' ?>">
                        <label class="label fs-16 mb-2">Parent Article (Indonesia)</label>
                        <div class="form-floating">
                            <select class="form-select" name="parent_id" id="parent_id">
                                <option value="">-- Select Parent Article --</option>
                                <?php foreach ($parent_articles as $article): ?>
                                    <option value="<?= (int) $article['id']; ?>" <?= ($parent_id === (int) $article['id']) ? 'selected' : '' ?>>
                                        #<?= (int) $article['id']; ?> - <?= htmlspecialchars($article['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label>Parent Article</label>
                        </div>
                    </div>

                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Upload Image</label>

                        <?php if (!empty($current_image)): ?>
                            <div class="mb-3">
                                <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($current_image) ?>" width="120" alt="">
                            </div>
                        <?php endif; ?>

                        <input type="file" class="form-control" name="image" accept=".jpg,.jpeg,.png,.webp">
                        <small class="text-secondary">Leave empty if you do not want to change the image.</small>
                    </div>

                </div>

                <!-- CONTENT -->
                <div class="col-lg-8">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Content</label>
                        <textarea class="form-control" name="content" rows="8"><?= htmlspecialchars($content) ?></textarea>
                    </div>
                </div>

                <!-- SEO -->
                <div class="col-lg-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SEO Keywords</label>
                        <textarea class="form-control" name="seo_keywords" rows="8"><?= htmlspecialchars($seo_keywords) ?></textarea>
                    </div>
                </div>

                <!-- BUTTON -->
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary text-white">Update News</button>
                        <a href="./" class="btn btn-danger text-white">Cancel</a>
                    </div>
                </div>

            </div>
        </form>
    </div>

    <div class="flex-grow-1"></div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const language = document.getElementById('language');
    const parentWrapper = document.getElementById('parent-wrapper');
    const parentSelect = document.getElementById('parent_id');

    function toggleParentField() {
        if (language.value === 'en') {
            parentWrapper.style.display = '';
            parentSelect.setAttribute('required', 'required');
        } else {
            parentWrapper.style.display = 'none';
            parentSelect.removeAttribute('required');
            parentSelect.value = '';
        }
    }

    language.addEventListener('change', toggleParentField);
    toggleParentField();
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>