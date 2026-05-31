<?php
$page = 'invoices';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id > 0) {
    db_delete("DELETE FROM invoice_items WHERE invoice_id = ?", 'i', [$id]);
    db_delete("DELETE FROM invoices WHERE id = ?", 'i', [$id]);
}

header('Location: ' . admin_url('invoices/?success=deleted'));
exit;
