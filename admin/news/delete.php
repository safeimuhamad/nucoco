<?php
$page = 'news';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /admin/news/?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data news dulu, untuk dapat nama image
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT image FROM news WHERE id = ? LIMIT 1");
if (!$stmt) {
    header('Location: /admin/news/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$news = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$news) {
    header('Location: /admin/news/?error=not_found');
    exit;
}

$image = $news['image'] ?? '';

/*
|--------------------------------------------------------------------------
| Delete news data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM news WHERE id = ?");
if (!$stmt) {
    header('Location: /admin/news/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {
    /*
    |--------------------------------------------------------------------------
    | Delete image file when available
    |--------------------------------------------------------------------------
    */
    if (!empty($image)) {
        $image_path = __DIR__ . '/../../uploads/' . $image;

        if (file_exists($image_path) && is_file($image_path)) {
            @unlink($image_path);
        }
    }

    header('Location: /admin/news/?success=deleted');
    exit;
}

header('Location: /admin/news/?error=delete_failed');
exit;
