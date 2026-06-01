<?php
$page = 'product-categories';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';

$mode = 'edit';
$error = '';
$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$category = db_select_one("SELECT * FROM product_categories WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$category) {
    header('Location: ' . admin_url('product-categories/?error=not_found'));
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_csrf_token();

    $category['label_en'] = trim($_POST['label_en'] ?? '');
    $category['label_id'] = trim($_POST['label_id'] ?? '');
    $category['category_key'] = trim($_POST['category_key'] ?? '');
    $category['sort_order'] = (int) ($_POST['sort_order'] ?? 0);
    $category['status'] = $_POST['status'] ?? 'active';

    if ($category['category_key'] === '') {
        $category['category_key'] = $category['label_en'];
    }

    $duplicate = db_select_one(
        "SELECT id FROM product_categories WHERE category_key = ? AND id <> ? LIMIT 1",
        'si',
        [$category['category_key'], $id]
    );

    if ($category['label_en'] === '' || $category['label_id'] === '') {
        $error = 'English and Indonesia names are required.';
    } elseif (!in_array($category['status'], ['active', 'inactive'], true)) {
        $error = 'Invalid category status.';
    } elseif ($duplicate) {
        $error = 'Category key already exists.';
    } else {
        db_update(
            "UPDATE product_categories SET category_key = ?, label_en = ?, label_id = ?, sort_order = ?, status = ? WHERE id = ?",
            'sssisi',
            [$category['category_key'], $category['label_en'], $category['label_id'], $category['sort_order'], $category['status'], $id]
        );
        header('Location: ' . admin_url('product-categories/detail.php?id=' . $id . '&success=updated'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/form.php';
include __DIR__ . '/../includes/footer.php';
