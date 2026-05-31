<?php
$page = 'pages';

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$admin_base_url = admin_base_url();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . $admin_base_url . 'pages/?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Cek data page dulu
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT id FROM pages WHERE id = ? LIMIT 1");
if (!$stmt) {
    header('Location: ' . $admin_base_url . 'pages/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$page_data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$page_data) {
    header('Location: ' . $admin_base_url . 'pages/?error=not_found');
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete page data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM pages WHERE id = ?");
if (!$stmt) {
    header('Location: ' . $admin_base_url . 'pages/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {
    header('Location: ' . $admin_base_url . 'pages/?success=deleted');
    exit;
}

header('Location: ' . $admin_base_url . 'pages/?error=delete_failed');
exit;
