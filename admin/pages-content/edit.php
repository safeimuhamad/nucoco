<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$page = 'page_contents';

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
| Load pages for dropdown
|--------------------------------------------------------------------------
*/
$pages_result = mysqli_query($conn, "SELECT id, page_name, slug FROM pages ORDER BY page_name ASC");
if (!$pages_result) {
    die('Failed to load pages: ' . mysqli_error($conn));
}

/*
|--------------------------------------------------------------------------
| Load existing page content
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT * FROM page_contents WHERE id = ? LIMIT 1");
if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$page_content = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$page_content) {
    header('Location: ./index.php?error=not_found');
    exit;
}

/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/
$page_id = $page_content['page_id'] ?? '';
$language_code = $page_content['language_code'] ?? 'id';
$seo_title = $page_content['seo_title'] ?? '';
$meta_title = $page_content['meta_title'] ?? '';
$meta_description = $page_content['meta_description'] ?? '';
$meta_keywords = $page_content['meta_keywords'] ?? '';
$focus_keyword = $page_content['focus_keyword'] ?? '';
$meta_robots = $page_content['meta_robots'] ?? 'index, follow';
$canonical_url = $page_content['canonical_url'] ?? '';
$og_title = $page_content['og_title'] ?? '';
$og_description = $page_content['og_description'] ?? '';
$og_image = $page_content['og_image'] ?? '';
$hero_subtitle = $page_content['hero_subtitle'] ?? '';
$hero_title = $page_content['hero_title'] ?? '';
$hero_description = $page_content['hero_description'] ?? '';
$button_1_text = $page_content['button_1_text'] ?? '';
$button_1_link = $page_content['button_1_link'] ?? '';
$button_2_text = $page_content['button_2_text'] ?? '';
$button_2_link = $page_content['button_2_link'] ?? '';
$hero_image = $page_content['hero_image'] ?? '';
$content = $page_content['content'] ?? '';
$schema_markup = $page_content['schema_markup'] ?? '';
$status = $page_content['status'] ?? 'draft';

/*
|--------------------------------------------------------------------------
| Handle submit
|--------------------------------------------------------------------------
*/
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $page_id = (int) ($_POST['page_id'] ?? 0);
    $language_code = trim($_POST['language_code'] ?? 'id');
    $seo_title = trim($_POST['seo_title'] ?? '');
    $meta_title = trim($_POST['meta_title'] ?? '');
    $meta_description = trim($_POST['meta_description'] ?? '');
    $meta_keywords = trim($_POST['meta_keywords'] ?? '');
    $focus_keyword = trim($_POST['focus_keyword'] ?? '');
    $meta_robots = trim($_POST['meta_robots'] ?? 'index, follow');
    $canonical_url = trim($_POST['canonical_url'] ?? '');
    $og_title = trim($_POST['og_title'] ?? '');
    $og_description = trim($_POST['og_description'] ?? '');
    $og_image = trim($_POST['og_image'] ?? '');
    $hero_subtitle = trim($_POST['hero_subtitle'] ?? '');
    $hero_title = trim($_POST['hero_title'] ?? '');
    $hero_description = trim($_POST['hero_description'] ?? '');
    $button_1_text = trim($_POST['button_1_text'] ?? '');
    $button_1_link = trim($_POST['button_1_link'] ?? '');
    $button_2_text = trim($_POST['button_2_text'] ?? '');
    $button_2_link = trim($_POST['button_2_link'] ?? '');
    $hero_image = trim($_POST['hero_image'] ?? '');
    $content = trim($_POST['content'] ?? '');
    $schema_markup = trim($_POST['schema_markup'] ?? '');
    $status = trim($_POST['status'] ?? 'draft');

    $allowed_languages = ['id', 'en'];
    $allowed_status = ['publish', 'draft'];
    $allowed_robots = ['index, follow', 'noindex, follow', 'index, nofollow', 'noindex, nofollow'];

    if ($page_id <= 0) {
        $error = 'Page is required.';
    } elseif (!in_array($language_code, $allowed_languages, true)) {
        $error = 'Invalid language code.';
    } elseif (!in_array($status, $allowed_status, true)) {
        $error = 'Invalid status.';
    } elseif (!in_array($meta_robots, $allowed_robots, true)) {
        $error = 'Invalid meta robots value.';
    } else {

        /*
        |--------------------------------------------------------------------------
        | Check duplicate page + language except current ID
        |--------------------------------------------------------------------------
        */
        $check_stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM page_contents WHERE page_id = ? AND language_code = ? AND id != ? LIMIT 1"
        );

        if (!$check_stmt) {
            die('Prepare failed: ' . mysqli_error($conn));
        }

        mysqli_stmt_bind_param($check_stmt, "isi", $page_id, $language_code, $id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);
        $existing_content = mysqli_fetch_assoc($check_result);
        mysqli_stmt_close($check_stmt);

        if ($existing_content) {
            $error = 'This page content for the selected language already exists.';
        } else {

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE page_contents SET
                    page_id = ?,
                    language_code = ?,
                    seo_title = ?,
                    meta_title = ?,
                    meta_description = ?,
                    meta_keywords = ?,
                    focus_keyword = ?,
                    meta_robots = ?,
                    canonical_url = ?,
                    og_title = ?,
                    og_description = ?,
                    og_image = ?,
                    hero_subtitle = ?,
                    hero_title = ?,
                    hero_description = ?,
                    button_1_text = ?,
                    button_1_link = ?,
                    button_2_text = ?,
                    button_2_link = ?,
                    hero_image = ?,
                    content = ?,
                    schema_markup = ?,
                    status = ?
                 WHERE id = ?"
            );

            if (!$stmt) {
                die('Prepare failed: ' . mysqli_error($conn));
            }

            mysqli_stmt_bind_param(
                $stmt,
                "issssssssssssssssssssssi",
                $page_id,
                $language_code,
                $seo_title,
                $meta_title,
                $meta_description,
                $meta_keywords,
                $focus_keyword,
                $meta_robots,
                $canonical_url,
                $og_title,
                $og_description,
                $og_image,
                $hero_subtitle,
                $hero_title,
                $hero_description,
                $button_1_text,
                $button_1_link,
                $button_2_text,
                $button_2_link,
                $hero_image,
                $content,
                $schema_markup,
                $status,
                $id
            );

            if (mysqli_stmt_execute($stmt)) {
                header('Location: ./index.php?success=updated');
                exit;
            } else {
                $error = 'Failed to update page content: ' . mysqli_error($conn);
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
        <h3 class="mb-0">Edit Page Content</h3>
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
                    <label class="label fs-16 mb-2">Page</label>
                    <select class="form-select" name="page_id" required>
                        <option value="">Select Page</option>
                        <?php mysqli_data_seek($pages_result, 0); ?>
                        <?php while ($row = mysqli_fetch_assoc($pages_result)): ?>
                            <option value="<?= (int) $row['id'] ?>" <?= ((string)$page_id === (string)$row['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($row['page_name']) ?> (<?= htmlspecialchars($row['slug']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-3 mb-20">
                    <label class="label fs-16 mb-2">Language</label>
                    <select class="form-select" name="language_code" required>
                        <option value="id" <?= ($language_code === 'id') ? 'selected' : '' ?>>Indonesia</option>
                        <option value="en" <?= ($language_code === 'en') ? 'selected' : '' ?>>English</option>
                    </select>
                </div>

                <div class="col-md-3 mb-20">
                    <label class="label fs-16 mb-2">Status</label>
                    <select class="form-select" name="status" required>
                        <option value="draft" <?= ($status === 'draft') ? 'selected' : '' ?>>Draft</option>
                        <option value="publish" <?= ($status === 'publish') ? 'selected' : '' ?>>Publish</option>
                    </select>
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">SEO Title</label>
                    <input type="text" class="form-control" name="seo_title" value="<?= htmlspecialchars($seo_title) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Meta Title</label>
                    <input type="text" class="form-control" name="meta_title" value="<?= htmlspecialchars($meta_title) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Focus Keyword</label>
                    <input type="text" class="form-control" name="focus_keyword" value="<?= htmlspecialchars($focus_keyword) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Meta Robots</label>
                    <select class="form-select" name="meta_robots">
                        <option value="index, follow" <?= ($meta_robots === 'index, follow') ? 'selected' : '' ?>>index, follow</option>
                        <option value="noindex, follow" <?= ($meta_robots === 'noindex, follow') ? 'selected' : '' ?>>noindex, follow</option>
                        <option value="index, nofollow" <?= ($meta_robots === 'index, nofollow') ? 'selected' : '' ?>>index, nofollow</option>
                        <option value="noindex, nofollow" <?= ($meta_robots === 'noindex, nofollow') ? 'selected' : '' ?>>noindex, nofollow</option>
                    </select>
                </div>

                <div class="col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Meta Description</label>
                    <textarea class="form-control" name="meta_description" rows="4"><?= htmlspecialchars($meta_description) ?></textarea>
                </div>

                <div class="col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Meta Keywords</label>
                    <textarea class="form-control" name="meta_keywords" rows="3"><?= htmlspecialchars($meta_keywords) ?></textarea>
                </div>

                <div class="col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Canonical URL</label>
                    <input type="text" class="form-control" name="canonical_url" value="<?= htmlspecialchars($canonical_url) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">OG Title</label>
                    <input type="text" class="form-control" name="og_title" value="<?= htmlspecialchars($og_title) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">OG Image</label>
                    <input type="text" class="form-control" name="og_image" value="<?= htmlspecialchars($og_image) ?>">
                </div>

                <div class="col-md-12 mb-20">
                    <label class="label fs-16 mb-2">OG Description</label>
                    <textarea class="form-control" name="og_description" rows="4"><?= htmlspecialchars($og_description) ?></textarea>
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Hero Subtitle</label>
                    <input type="text" class="form-control" name="hero_subtitle" value="<?= htmlspecialchars($hero_subtitle) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Hero Title</label>
                    <input type="text" class="form-control" name="hero_title" value="<?= htmlspecialchars($hero_title) ?>">
                </div>

                <div class="col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Hero Description</label>
                    <textarea class="form-control" name="hero_description" rows="4"><?= htmlspecialchars($hero_description) ?></textarea>
                </div>

                <div class="col-md-4 mb-20">
                    <label class="label fs-16 mb-2">Button 1 Text</label>
                    <input type="text" class="form-control" name="button_1_text" value="<?= htmlspecialchars($button_1_text) ?>">
                </div>

                <div class="col-md-4 mb-20">
                    <label class="label fs-16 mb-2">Button 1 Link</label>
                    <input type="text" class="form-control" name="button_1_link" value="<?= htmlspecialchars($button_1_link) ?>">
                </div>

                <div class="col-md-4 mb-20">
                    <label class="label fs-16 mb-2">Hero Image</label>
                    <input type="text" class="form-control" name="hero_image" value="<?= htmlspecialchars($hero_image) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Button 2 Text</label>
                    <input type="text" class="form-control" name="button_2_text" value="<?= htmlspecialchars($button_2_text) ?>">
                </div>

                <div class="col-md-6 mb-20">
                    <label class="label fs-16 mb-2">Button 2 Link</label>
                    <input type="text" class="form-control" name="button_2_link" value="<?= htmlspecialchars($button_2_link) ?>">
                </div>

                <div class="col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Content</label>
                    <textarea class="form-control" name="content" rows="8"><?= htmlspecialchars($content) ?></textarea>
                </div>

                <div class="col-md-12 mb-20">
                    <label class="label fs-16 mb-2">Schema Markup (JSON-LD)</label>
                    <textarea class="form-control" name="schema_markup" rows="8"><?= htmlspecialchars($schema_markup) ?></textarea>
                </div>

                <div class="col-12">
                    <button type="submit" class="btn btn-primary text-white">Update Page Content</button>
                    <a href="./index.php" class="btn btn-danger text-white">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>