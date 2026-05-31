<?php
require_once __DIR__ . '/../includes/bootstrap.php';

start_app_session();
include __DIR__ . '/includes/db.php';

require_csrf_token();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error'] = "Email dan password wajib diisi";
    header("Location: login");
    exit;
}

// ambil user
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? OR email = ? LIMIT 1");
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$query = $stmt->get_result();

if($query->num_rows > 0){
    $user = $query->fetch_assoc();

    if(password_verify($password, $user['password'])){

        if($user['status'] != 'active'){
            $_SESSION['error'] = "Akun tidak aktif";
            header("Location: login.php");
            exit;
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name'] = $user['name'];
        $_SESSION['role'] = $user['role'];

        $update_stmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update_stmt->bind_param("i", $user['id']);
        $update_stmt->execute();

        header("Location: dashboard/");
        exit;

    } else {
        $_SESSION['error'] = "Password salah";
    }

} else {
    $_SESSION['error'] = "User tidak ditemukan";
}

header("Location: login");
exit;
