<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

$db = database_config();

$conn = new mysqli(
    $db['host'] ?? 'localhost',
    $db['username'] ?? 'root',
    $db['password'] ?? '',
    $db['database'] ?? '',
    (int) ($db['port'] ?? 3306)
);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

$conn->set_charset($db['charset'] ?? 'utf8mb4');

require_once dirname(__DIR__, 2) . '/includes/database.php';
?>
