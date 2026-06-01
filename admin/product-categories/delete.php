<?php
$page = 'product-categories';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$category = db_select_one("SELECT * FROM product_categories WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$category) {
    header('Location: ' . admin_url('product-categories/?error=not_found'));
    exit;
}

$used = db_select_one("SELECT COUNT(*) AS total FROM products WHERE category = ?", 's', [$category['category_key']]);
if ((int) ($used['total'] ?? 0) > 0) {
    db_update("UPDATE product_categories SET status = 'inactive' WHERE id = ?", 'i', [$id]);
    header('Location: ' . admin_url('product-categories/detail.php?id=' . $id . '&success=disabled'));
    exit;
}

db_delete("DELETE FROM product_categories WHERE id = ?", 'i', [$id]);
header('Location: ' . admin_url('product-categories/?success=deleted'));
exit;
