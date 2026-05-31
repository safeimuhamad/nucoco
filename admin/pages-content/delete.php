<?php
$page = 'pages-content';

include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$admin_base_url = admin_base_url();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . $admin_base_url . 'pages-content/?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Cek data page content dulu
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT id FROM page_contents WHERE id = ? LIMIT 1");
if (!$stmt) {
    header('Location: ' . $admin_base_url . 'pages-content/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$page_content = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$page_content) {
    header('Location: ' . $admin_base_url . 'pages-content/?error=not_found');
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete page content data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM page_contents WHERE id = ?");
if (!$stmt) {
    header('Location: ' . $admin_base_url . 'pages-content/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {
    header('Location: ' . $admin_base_url . 'pages-content/?success=deleted');
    exit;
}

header('Location: ' . $admin_base_url . 'pages-content/?error=delete_failed');
exit;
