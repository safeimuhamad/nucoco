<?php
$page = 'quotations';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: ' . admin_url('quotations/?error=invalid_id'));
    exit;
}

db_delete("DELETE FROM quotations WHERE id = ?", 'i', [$id]);
header('Location: ' . admin_url('quotations/?success=deleted'));
exit;
