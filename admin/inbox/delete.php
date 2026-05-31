<?php
$page = 'inbox';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: ' . admin_url('inbox/?error=invalid_id'));
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data inquiry dulu, untuk memastikan data ada
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT id FROM inquiries WHERE id = ? LIMIT 1");
if (!$stmt) {
    header('Location: ' . admin_url('inbox/?error=prepare_failed'));
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$inquiry = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$inquiry) {
    header('Location: ' . admin_url('inbox/?error=not_found'));
    exit;
}

/*
|--------------------------------------------------------------------------
| Delete inquiry data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM inquiries WHERE id = ?");
if (!$stmt) {
    header('Location: ' . admin_url('inbox/?error=prepare_failed'));
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
$deleted = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($deleted) {
    header('Location: ' . admin_url('inbox/?success=deleted'));
    exit;
}

header('Location: ' . admin_url('inbox/?error=delete_failed'));
exit;
