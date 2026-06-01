<?php
$page = 'product-categories';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$category = db_select_one("SELECT * FROM product_categories WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$category) {
    header('Location: ' . admin_url('product-categories/?error=not_found'));
    exit;
}

$products = db_select_all("SELECT id, name, language, status FROM products WHERE category = ? ORDER BY id DESC LIMIT 10", 's', [$category['category_key']]);

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Product Categories', admin_url('product-categories/'), 'Category Detail', $category['label_en'], [
        ['label' => 'Edit', 'url' => admin_url('product-categories/edit.php?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('product-categories/delete.php?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this category?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    ?>

    <?php if (isset($_GET['success'])): ?><div class="alert alert-success">Category <?= htmlspecialchars($_GET['success']) ?> successfully.</div><?php endif; ?>

    <?php
    detail_summary([
        ['label' => 'English Name', 'value' => $category['label_en'], 'icon' => 'category', 'meta' => $category['category_key']],
        ['label' => 'Indonesia Name', 'value' => $category['label_id'], 'icon' => 'translate', 'meta' => 'Product form label'],
        ['label' => 'Status', 'value' => ucfirst($category['status']), 'icon' => 'flag', 'meta' => 'Sort ' . (int) $category['sort_order']],
    ]);
    ?>

    <div class="row">
        <div class="col-lg-7">
            <?php detail_card_open('Category Information', 'category'); ?>
                <?php detail_fields([
                    'Category Key' => detail_text($category['category_key']),
                    'English Name' => detail_text($category['label_en']),
                    'Indonesia Name' => detail_text($category['label_id']),
                    'Sort Order' => detail_text((string) $category['sort_order']),
                    'Status' => detail_text(ucfirst($category['status'])),
                ]); ?>
            <?php detail_card_close(); ?>
        </div>
        <div class="col-lg-5">
            <?php detail_card_open('Related Products', 'inventory_2'); ?>
                <?php if ($products): foreach ($products as $product): ?>
                    <p class="d-flex justify-content-between align-items-center gap-3 mb-2">
                        <a class="text-primary" href="<?= admin_url('product/detail.php?id=' . (int) $product['id']) ?>"><?= htmlspecialchars($product['name']) ?></a>
                        <span class="text-secondary"><?= htmlspecialchars(strtoupper($product['language'])) ?> / <?= htmlspecialchars(ucfirst($product['status'])) ?></span>
                    </p>
                <?php endforeach; else: ?>
                    <p class="text-secondary">No product uses this category yet.</p>
                <?php endif; ?>
            <?php detail_card_close(); ?>
        </div>
    </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
