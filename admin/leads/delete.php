<?php
$page = 'leads';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    db_delete("DELETE FROM leads WHERE id = ?", 'i', [$id]);
}

header('Location: ' . admin_url('leads/?success=deleted'));
exit;
