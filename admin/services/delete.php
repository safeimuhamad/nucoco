<?php
$page = 'services';

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$admin_base_url = admin_base_url();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . $admin_base_url . 'services/?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data service dulu, untuk dapat nama image
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT image FROM services WHERE id = ? LIMIT 1");
if (!$stmt) {
    header('Location: ' . $admin_base_url . 'services/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$service = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$service) {
    header('Location: ' . $admin_base_url . 'services/?error=not_found');
    exit;
}

$image = $service['image'] ?? '';

/*
|--------------------------------------------------------------------------
| Delete service data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM services WHERE id = ?");
if (!$stmt) {
    header('Location: ' . $admin_base_url . 'services/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {
    if (!empty($image)) {
        $image_path = __DIR__ . '/../../uploads/services/' . $image;

        if (file_exists($image_path) && is_file($image_path)) {
            @unlink($image_path);
        }
    }

    header('Location: ' . $admin_base_url . 'services/?success=deleted');
    exit;
}

header('Location: ' . $admin_base_url . 'services/?error=delete_failed');
exit;
