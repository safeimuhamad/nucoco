<?php
$page = 'users';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/mailer.php';
require_once __DIR__ . '/auth-functions.php';

$roles = db_select_all("SELECT role_key, role_name FROM user_roles WHERE status = 'active' ORDER BY role_name ASC");
$error = '';
$notice = '';
$user = [
    'name' => '',
    'email' => '',
    'username' => '',
    'phone' => '',
    'role' => $roles[0]['role_key'] ?? 'editor',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    foreach ($user as $key => $value) {
        $user[$key] = trim($_POST[$key] ?? '');
    }

    if ($user['name'] === '') {
        $error = 'Name is required.';
    } elseif ($user['email'] === '' || !filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
        $error = 'Valid email is required.';
    } elseif ($user['username'] === '') {
        $error = 'Username is required.';
    } elseif (!in_array($user['role'], array_column($roles, 'role_key'), true)) {
        $error = 'Invalid role.';
    } else {
        $exists = db_select_one("SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1", 'ss', [$user['email'], $user['username']]);
        if ($exists) {
            $error = 'Email or username already exists.';
        } else {
            $token = user_auth_token();
            $token_hash = user_auth_token_hash($token);
            $password_hash = password_hash(user_auth_token(), PASSWORD_DEFAULT);

            $user_id = db_insert(
                "INSERT INTO users
                 (name, email, username, password, phone, role, status, activation_token_hash, activation_expires_at, invited_at)
                 VALUES (?, ?, ?, ?, ?, ?, 'inactive', ?, DATE_ADD(NOW(), INTERVAL 24 HOUR), NOW())",
                'sssssss',
                [$user['name'], $user['email'], $user['username'], $password_hash, $user['phone'], $user['role'], $token_hash]
            );

            try {
                send_user_invitation_email($user, $token);
                header('Location: ' . admin_url('users/detail.php?id=' . $user_id . '&success=invited'));
                exit;
            } catch (Throwable $e) {
                $notice = 'User was created, but invitation email failed: ' . $e->getMessage();
            }
        }
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>

<div class="main-content-container overflow-hidden">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4 mt-1">
        <h3 class="mb-0">Create User</h3>
        <a href="<?= admin_url('users/') ?>" class="btn btn-outline-primary">Back</a>
    </div>

    <?php if ($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if ($notice): ?><div class="alert alert-warning"><?= htmlspecialchars($notice) ?></div><?php endif; ?>

    <form method="POST" class="card bg-white rounded-10 border border-white p-20">
        <?= csrf_field() ?>
        <div class="row">
            <div class="col-md-6 mb-20">
                <label class="label fs-16 mb-2">Name</label>
                <input class="form-control" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="col-md-6 mb-20">
                <label class="label fs-16 mb-2">Email</label>
                <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>
            <div class="col-md-6 mb-20">
                <label class="label fs-16 mb-2">Username</label>
                <input class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
            </div>
            <div class="col-md-6 mb-20">
                <label class="label fs-16 mb-2">Phone</label>
                <input class="form-control" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">
            </div>
            <div class="col-md-6 mb-20">
                <label class="label fs-16 mb-2">Role</label>
                <select name="role" class="form-select" required>
                    <?php foreach ($roles as $role): ?>
                        <option value="<?= htmlspecialchars($role['role_key']) ?>" <?= $user['role'] === $role['role_key'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($role['role_name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <a href="<?= admin_url('users/') ?>" class="btn btn-danger text-white">Cancel</a>
            <button class="btn btn-primary text-white" type="submit">
                <span class="material-symbols-outlined">send</span>
                <span>Create & Send Invite</span>
            </button>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
