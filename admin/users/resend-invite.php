<?php
$page = 'users';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/auth-functions.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$user = db_select_one("SELECT id, name, email, status, activation_expires_at FROM users WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$user) {
    header('Location: ' . admin_url('users/?error=not_found'));
    exit;
}

$expired = $user['status'] === 'inactive'
    && !empty($user['activation_expires_at'])
    && strtotime($user['activation_expires_at']) < time();

if (!$expired) {
    header('Location: ' . admin_url('users/detail.php?id=' . $id . '&error=invite_not_expired'));
    exit;
}

$token = user_auth_token();
db_update(
    "UPDATE users SET activation_token_hash = ?, activation_expires_at = DATE_ADD(NOW(), INTERVAL 24 HOUR), invited_at = NOW() WHERE id = ?",
    'si',
    [user_auth_token_hash($token), $id]
);

try {
    send_user_invitation_email($user, $token);
    header('Location: ' . admin_url('users/detail.php?id=' . $id . '&success=invite_resent'));
} catch (Throwable $e) {
    header('Location: ' . admin_url('users/detail.php?id=' . $id . '&error=mail_failed'));
}
exit;
