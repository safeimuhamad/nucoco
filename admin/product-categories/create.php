<?php
$page = 'product-categories';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/helpers.php';
product_categories_ensure_schema($conn);

$mode = 'create';
$error = '';
$category = ['category_key' => '', 'label_en' => '', 'label_id' => '', 'sort_order' => 0, 'status' => 'active'];

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

    if ($category['label_en'] === '' || $category['label_id'] === '') {
        $error = 'English and Indonesia names are required.';
    } elseif (!in_array($category['status'], ['active', 'inactive'], true)) {
        $error = 'Invalid category status.';
    } elseif (db_select_one("SELECT id FROM product_categories WHERE category_key = ? LIMIT 1", 's', [$category['category_key']])) {
        $error = 'Category key already exists.';
    } else {
        $id = db_insert(
            "INSERT INTO product_categories (category_key, label_en, label_id, sort_order, status) VALUES (?, ?, ?, ?, ?)",
            'sssis',
            [$category['category_key'], $category['label_en'], $category['label_id'], $category['sort_order'], $category['status']]
        );
        header('Location: ' . admin_url('product-categories/detail.php?id=' . $id . '&success=created'));
        exit;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
include __DIR__ . '/form.php';
include __DIR__ . '/../includes/footer.php';
