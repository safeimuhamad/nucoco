<?php
$page = 'testimonial';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /admin/testimonial/?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data testimonial dulu, untuk dapat nama photo
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT photo FROM testimonials WHERE id = ? LIMIT 1");
if (!$stmt) {
    header('Location: /admin/testimonial/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$testimonial = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$testimonial) {
    header('Location: /admin/testimonial/?error=not_found');
    exit;
}

$photo = $testimonial['photo'] ?? '';

/*
|--------------------------------------------------------------------------
| Delete testimonial data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM testimonials WHERE id = ?");
if (!$stmt) {
    header('Location: /admin/testimonial/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {
    /*
    |--------------------------------------------------------------------------
    | Delete photo file when available
    |--------------------------------------------------------------------------
    */
    if (!empty($photo)) {
        $photo_path = __DIR__ . '/../../uploads/testimonials/' . $photo;

        if (file_exists($photo_path) && is_file($photo_path)) {
            @unlink($photo_path);
        }
    }

    header('Location: /admin/testimonial/?success=deleted');
    exit;
}

header('Location: /admin/testimonial/?error=delete_failed');
exit;
