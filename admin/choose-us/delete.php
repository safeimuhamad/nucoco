<?php
$page = 'choose_us';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /admin/choose-us/?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data dulu (icon)
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT icon FROM why_choose_us WHERE id = ? LIMIT 1");

if (!$stmt) {
    header('Location: /admin/choose-us/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$item) {
    header('Location: /admin/choose-us/?error=not_found');
    exit;
}

$icon = $item['icon'] ?? '';

/*
|--------------------------------------------------------------------------
| Delete data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM why_choose_us WHERE id = ?");

if (!$stmt) {
    header('Location: /admin/choose-us/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {

    /*
    |--------------------------------------------------------------------------
    | Delete icon file when available
    |--------------------------------------------------------------------------
    */
    if (!empty($icon)) {

        $upload_dir = realpath(__DIR__ . '/../../uploads/why_choose_us/');
        $icon_path  = $upload_dir . '/' . $icon;

        // extra safety: pastikan file benar-benar di folder upload
        if ($upload_dir && file_exists($icon_path) && is_file($icon_path)) {
            @unlink($icon_path);
        }
    }

    header('Location: /admin/choose-us/?success=deleted');
    exit;
}

/*
|--------------------------------------------------------------------------
| Fallback error
|--------------------------------------------------------------------------
*/
header('Location: /admin/choose-us/?error=delete_failed');
exit;
