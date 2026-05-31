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
$category = '';
$status = 'draft';
$seo_keywords = '';
$content = '';
$language = 'id';
$parent_id = '';

// Ambil artikel Indonesia untuk dropdown parent
$parent_articles = [];
$parent_query = mysqli_query($conn, "
    SELECT n.id, n.title, n.translation_group_id
    FROM news n
    WHERE n.language = 'id'
    AND NOT EXISTS (
      SELECT 1
      FROM news en
      WHERE en.parent_id = n.id
      AND en.language = 'en'
      )
    ORDER BY n.created_at DESC
    ");

if ($parent_query) {
    while ($row = mysqli_fetch_assoc($parent_query)) {
        $parent_articles[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $slug = generate_slug($title);
    $category = trim($_POST['category'] ?? '');
    $seo_keywords = trim($_POST['seo_keywords'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');
    $content = trim($_POST['content'] ?? '');
    $language = trim($_POST['language'] ?? 'id');
    $parent_id = trim($_POST['parent_id'] ?? '');

    $allowed_status = ['publish', 'draft'];
    $allowed_language = ['id', 'en'];

    if (!isset($_FILES['image']) || $_FILES['image']['error'] === UPLOAD_ERR_NO_FILE) {
        $error = 'News image is required.';
    } elseif ($category === '') {
        $error = 'Category is required.';
    } elseif ($title === '') {
        $error = 'News title is required.';
    } elseif ($slug === '') {
        $error = 'Slug is required.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid news status.';
    } elseif (!in_array($language, $allowed_language, true)) {
        $error = 'Invalid language.';
    } elseif ($language === 'en' && $parent_id === '') {
        $error = 'Parent article (Indonesia) is required for English version.';
    } else {
        // Validasi slug unik per bahasa
        $check_slug = mysqli_prepare($conn, "
            SELECT id
            FROM news
            WHERE slug = ? AND language = ?
            LIMIT 1
            ");

        if ($check_slug) {
            mysqli_stmt_bind_param($check_slug, "ss", $slug, $language);
            mysqli_stmt_execute($check_slug);
            $check_slug_result = mysqli_stmt_get_result($check_slug);

            if (mysqli_fetch_assoc($check_slug_result)) {
                $error = 'Slug already exists for this language.';
            }

            mysqli_stmt_close($check_slug);
        } else {
            $error = 'Failed to validate slug: ' . mysqli_error($conn);
        }

        $translation_group_id = null;
        $final_parent_id = null;

        // Kalau English, ambil data parent Indonesia
        if ($error === '' && $language === 'en') {
            $parent_id_int = (int) $parent_id;

            $parent_stmt = mysqli_prepare($conn, "
                SELECT id, language, translation_group_id
                FROM news
                WHERE id = ?
                LIMIT 1
                ");

            if ($parent_stmt) {
                mysqli_stmt_bind_param($parent_stmt, "i", $parent_id_int);
                mysqli_stmt_execute($parent_stmt);
                $parent_result = mysqli_stmt_get_result($parent_stmt);
                $parent_data = mysqli_fetch_assoc($parent_result);
                mysqli_stmt_close($parent_stmt);

                if (!$parent_data) {
                    $error = 'Selected parent article not found.';
                } elseif ($parent_data['language'] !== 'id') {
                    $error = 'Parent article must be Indonesian version.';
                } else {
                    $translation_group_id = !empty($parent_data['translation_group_id'])
                    ? (int) $parent_data['translation_group_id']
                    : (int) $parent_data['id'];

                    $final_parent_id = (int) $parent_data['id'];

                    // Cek apakah versi English untuk parent ini sudah ada
                    $check_en_stmt = mysqli_prepare($conn, "
                        SELECT id
                        FROM news
                        WHERE parent_id = ? AND language = 'en'
                        LIMIT 1
                        ");

                    if ($check_en_stmt) {
                        mysqli_stmt_bind_param($check_en_stmt, "i", $final_parent_id);
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

        $image_name = '';

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
            if ($language === 'id') {
                // Insert dulu tanpa translation_group_id final
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO news (
                        language, title, category, slug, content, image, status, seo_keywords,
                        translation_group_id, parent_id, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NULL, NULL, NOW(), NOW())"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssssss",
                        $language,
                        $title,
                        $category,
                        $slug,
                        $content,
                        $image_name,
                        $status,
                        $seo_keywords
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        $new_id = mysqli_insert_id($conn);
                        mysqli_stmt_close($stmt);

                        // Jadikan dirinya sendiri sebagai induk + translation group
                        $update_stmt = mysqli_prepare($conn, "
                            UPDATE news
                            SET translation_group_id = ?, parent_id = ?, updated_at = NOW()
                            WHERE id = ?
                            ");

                        if ($update_stmt) {
                            mysqli_stmt_bind_param($update_stmt, "iii", $new_id, $new_id, $new_id);

                            if (mysqli_stmt_execute($update_stmt)) {
                                mysqli_stmt_close($update_stmt);
                                header('Location: ./?success=created');
                                exit;
                            } else {
                                $error = 'Failed to update translation group: ' . mysqli_error($conn);
                            }

                            mysqli_stmt_close($update_stmt);
                        } else {
                            $error = 'Failed to prepare update query: ' . mysqli_error($conn);
                        }
                    } else {
                        $error = 'Failed to save news: ' . mysqli_error($conn);
                        mysqli_stmt_close($stmt);
                    }
                } else {
                    $error = 'Failed to prepare insert query: ' . mysqli_error($conn);
                }
            } else {
                // Insert English version
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO news (
                        language, title, category, slug, content, image, status, seo_keywords,
                        translation_group_id, parent_id, created_at, updated_at
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())"
                );

                if ($stmt) {
                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssssssii",
                        $language,
                        $title,
                        $category,
                        $slug,
                        $content,
                        $image_name,
                        $status,
                        $seo_keywords,
                        $translation_group_id,
                        $final_parent_id
                    );

                    if (mysqli_stmt_execute($stmt)) {
                        mysqli_stmt_close($stmt);
                        header('Location: ./?success=created');
                        exit;
                    } else {
                        $error = 'Failed to save news: ' . mysqli_error($conn);
                    }

                    mysqli_stmt_close($stmt);
                } else {
                    $error = 'Failed to prepare insert query: ' . mysqli_error($conn);
                }
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
                    <a href="./" class="d-flex align-items-center text-decoration-none">
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

                <div class="col-lg-8">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">News ID</label>
                                <div class="form-floating">
                                    <input type="text" class="form-control" value="Auto Generate" readonly>
                                    <label>News ID</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="mb-20">
                                <label class="label fs-16 mb-2">Title</label>
                                <div class="form-floating">
                                    <input
                                    type="text"
                                    class="form-control"
                                    name="title"
                                    value="<?= htmlspecialchars($title) ?>"
                                    required
                                    >
                                    <label>Title</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

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
                </div>

                <div class="col-lg-4" id="parent-wrapper" style="<?= ($language === 'en') ? '' : 'display:none;' ?>">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Parent Article (Indonesia)</label>
                        <div class="form-floating">
                            <select class="form-select" name="parent_id" id="parent_id">
                                <option value="">-- Select Parent Article --</option>
                                <?php foreach ($parent_articles as $article): ?>
                                    <option value="<?= (int) $article['id']; ?>" <?= ((string)$parent_id === (string)$article['id']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($article['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <label>Parent Article</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Category</label>
                        <div class="form-floating">
                            <select class="form-select" name="category" required>
                                <option value="">-- Select Category --</option>
                                <option value="Health & Benefits" <?= ($category == 'Health & Benefits') ? 'selected' : '' ?>>Health & Benefits</option>
                                <option value="Coconut Products" <?= ($category == 'Coconut Products') ? 'selected' : '' ?>>Coconut Products</option>
                                <option value="Recipes & Usage" <?= ($category == 'Recipes & Usage') ? 'selected' : '' ?>>Recipes & Usage</option>
                                <option value="Nutrition & Facts" <?= ($category == 'Nutrition & Facts') ? 'selected' : '' ?>>Nutrition & Facts</option>
                                <option value="Export & Supply" <?= ($category == 'Export & Supply') ? 'selected' : '' ?>>Export & Supply</option>
                                <option value="Farming & Sustainability" <?= ($category == 'Farming & Sustainability') ? 'selected' : '' ?>>Farming & Sustainability</option>
                                <option value="Industry & Business" <?= ($category == 'Industry & Business') ? 'selected' : '' ?>>Industry & Business</option>
                                <option value="Company News" <?= ($category == 'Company News') ? 'selected' : '' ?>>Company News</option>
                            </select>
                            <label>Category</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Status</label>
                        <div class="form-floating">
                            <select class="form-select" name="status" required>
                                <option value="draft" <?= ($status == 'draft') ? 'selected' : '' ?>>Draft</option>
                                <option value="publish" <?= ($status == 'publish') ? 'selected' : '' ?>>Publish</option>
                            </select>
                            <label>Status</label>
                        </div>
                    </div>
                </div>

                <div class="col-8">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Content</label>
                        <textarea class="form-control" name="content" rows="6"><?= htmlspecialchars($content) ?></textarea>
                    </div>
                </div>

                <div class="col-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">SEO Keywords</label>
                        <textarea class="form-control" name="seo_keywords" rows="5"><?= htmlspecialchars($seo_keywords) ?></textarea>
                    </div>
                </div>

                <div class="col-4">
                    <div class="mb-20">
                        <label class="label fs-16 mb-2">Upload Image</label>
                        <input type="file" class="form-control" name="image" required>
                    </div>
                </div>

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