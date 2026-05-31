<?php
$page = 'team';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header('Location: /admin/team/?error=invalid_id');
    exit;
}

/*
|--------------------------------------------------------------------------
| Ambil data member dulu, untuk dapat nama photo
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "SELECT photo FROM team_members WHERE id = ? LIMIT 1");
if (!$stmt) {
    header('Location: /admin/team/?error=prepare_failed');
    exit;
}

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$member = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$member) {
    header('Location: /admin/team/?error=not_found');
    exit;
}

$photo = $member['photo'] ?? '';

/*
|--------------------------------------------------------------------------
| Delete member data
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "DELETE FROM team_members WHERE id = ?");
if (!$stmt) {
    header('Location: /admin/team/?error=prepare_failed');
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
        $photo_path = __DIR__ . '/../../uploads/team/' . $photo;

        if (file_exists($photo_path) && is_file($photo_path)) {
            @unlink($photo_path);
        }
    }

    header('Location: /admin/team/?success=deleted');
    exit;
}

header('Location: /admin/team/?error=delete_failed');
exit;
