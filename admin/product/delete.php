<?php
$page = 'product';

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';

/*
|--------------------------------------------------------------------------
| Base redirect ke list product admin
|--------------------------------------------------------------------------
*/
$redirect_url = '/admin/product/';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . $redirect_url . '?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data product dulu, untuk dapat nama image
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT image FROM products WHERE id = ? LIMIT 1");

if (!$stmt) {
    header('Location: ' . $redirect_url . '?error=delete_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    header('Location: ' . $redirect_url . '?error=not_found');
    exit;
}

$image = $product['image'] ?? '';

/*
|--------------------------------------------------------------------------
| Delete product data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");

if (!$stmt) {
    header('Location: ' . $redirect_url . '?error=delete_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    if (!empty($image)) {
        $image_path = __DIR__ . '/../../uploads/' . $image;
        if (file_exists($image_path)) {
            @unlink($image_path);
        }
    }

    mysqli_stmt_close($stmt);
    header('Location: ' . $redirect_url . '?success=deleted');
    exit;
}

mysqli_stmt_close($stmt);
header('Location: ' . $redirect_url . '?error=delete_failed');
exit;
