<?php
require_once __DIR__ . '/../includes/bootstrap.php';

start_app_session();

$_SESSION = [];
session_destroy();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

header('Location: ' . admin_url('login'));
exit;
