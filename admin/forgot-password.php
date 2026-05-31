<?php
require_once __DIR__ . '/../includes/bootstrap.php';
start_app_session();
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/mailer.php';
require_once __DIR__ . '/users/auth-functions.php';

$sent = false;
$error = '';
$email = trim($_POST['email'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email is required.';
    } else {
        $user = db_select_one("SELECT id, name, email FROM users WHERE email = ? LIMIT 1", 's', [$email]);
        if ($user) {
            $token = user_auth_token();
            db_update(
                "UPDATE users SET password_reset_token_hash = ?, password_reset_expires_at = DATE_ADD(NOW(), INTERVAL 2 HOUR) WHERE id = ?",
                'si',
                [user_auth_token_hash($token), (int) $user['id']]
            );
            try {
                send_password_reset_email($user, $token);
            } catch (Throwable $e) {
                $error = 'Reset email failed: ' . $e->getMessage();
            }
        }

        if ($error === '') {
            $sent = true;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password - Nucoco</title>
    <link rel="stylesheet" href="<?= admin_url('assets/css/style.css') ?>">
</head>
<body class="bg-body-bg">
    <div class="container-fluid">
        <div class="main-content d-flex flex-column p-0">
            <div class="m-lg-auto my-auto w-930 py-4">
                <div class="card bg-white border rounded-10 border-white py-5 px-5">
                    <div class="p-md-5 p-4">
                        <h3 class="fs-26 fw-medium mb-2">Forgot Password</h3>
                        <p class="text-secondary mb-4">Enter your email address and we will send a reset link.</p>
                        <?php if ($sent): ?>
                            <div class="alert alert-success">If the email exists, a reset link has been sent.</div>
                            <a href="<?= admin_url('login') ?>" class="btn btn-primary text-white">Back to Sign In</a>
                        <?php else: ?>
                            <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
                            <form method="POST">
                                <?= csrf_field() ?>
                                <div class="mb-20">
                                    <label class="label fs-16 mb-2">Email Address</label>
                                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>" required>
                                </div>
                                <button class="btn btn-primary text-white w-100" type="submit">Send Reset Link</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
