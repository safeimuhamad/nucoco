<?php
require_once __DIR__ . '/../includes/bootstrap.php';
start_app_session();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/users/auth-functions.php';

$token = trim($_GET['token'] ?? ($_POST['token'] ?? ''));
$token_hash = $token !== '' ? user_auth_token_hash($token) : '';
$user = $token_hash !== ''
    ? db_select_one("SELECT id, name, email FROM users WHERE password_reset_token_hash = ? AND password_reset_expires_at >= NOW() LIMIT 1", 's', [$token_hash])
    : null;
$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if (!$user) {
        $error = 'Reset link is invalid or expired.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Password confirmation does not match.';
    } else {
        db_update(
            "UPDATE users SET password = ?, password_reset_token_hash = NULL, password_reset_expires_at = NULL WHERE id = ?",
            'si',
            [password_hash($password, PASSWORD_DEFAULT), (int) $user['id']]
        );
        $success = true;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password - Nucoco</title>
    <link rel="stylesheet" href="<?= admin_url('assets/css/style.css') ?>">
</head>
<body class="bg-body-bg">
    <div class="container-fluid">
        <div class="main-content d-flex flex-column p-0">
            <div class="m-lg-auto my-auto w-930 py-4">
                <div class="card bg-white border rounded-10 border-white py-5 px-5">
                    <div class="p-md-5 p-4">
                        <h3 class="fs-26 fw-medium mb-2">Reset Password</h3>
                        <?php if ($success): ?>
                            <div class="alert alert-success">Your password has been updated.</div>
                            <a href="<?= admin_url('login') ?>" class="btn btn-primary text-white">Sign In</a>
                        <?php elseif (!$user): ?>
                            <div class="alert alert-danger">Reset link is invalid or expired.</div>
                            <a href="<?= admin_url('forgot-password.php') ?>" class="btn btn-primary text-white">Request New Link</a>
                        <?php else: ?>
                            <p class="text-secondary mb-4">Hello <?= htmlspecialchars($user['name']) ?>, please create your new password.</p>
                            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                            <form method="POST">
                                <?= csrf_field() ?>
                                <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                                <div class="mb-20">
                                    <label class="label fs-16 mb-2">New Password</label>
                                    <input type="password" name="password" class="form-control" required minlength="8">
                                </div>
                                <div class="mb-20">
                                    <label class="label fs-16 mb-2">Confirm Password</label>
                                    <input type="password" name="password_confirm" class="form-control" required minlength="8">
                                </div>
                                <button class="btn btn-primary text-white w-100" type="submit">Update Password</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
