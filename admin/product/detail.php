<?php
$page = 'product';
include __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/detail-ui.php';
require_once __DIR__ . '/../includes/helpers.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$item = db_select_one("SELECT * FROM products WHERE id = ? LIMIT 1", 'i', [$id]);
if (!$item) {
    header('Location: ' . admin_url('product/?error=not_found'));
    exit;
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/sidebar.php';
?>
<div class="main-content-container overflow-hidden">
    <?php
    detail_page_header('Products', admin_url('product/'), 'Product Detail', $item['name'] ?? '-', [
        ['label' => 'Edit', 'url' => admin_url('product/edit?id=' . $id), 'icon' => 'edit', 'class' => 'btn detail-btn detail-btn-primary'],
        ['label' => 'Delete', 'url' => admin_url('product/delete?id=' . $id), 'icon' => 'delete', 'confirm' => 'Delete this product?', 'class' => 'btn detail-btn detail-btn-danger'],
    ]);
    detail_summary([
        ['label' => 'Product', 'value' => $item['name'] ?? '-', 'icon' => 'inventory_2', 'meta' => product_category_label($item['category'] ?? '', $item['language'] ?? 'en')],
        ['label' => 'Language', 'value' => strtoupper($item['language'] ?? '-'), 'icon' => 'translate', 'meta' => $item['status'] ?? '-'],
        ['label' => 'Price', 'value' => product_format_price($item['price'] ?? 0, $item['language'] ?? 'en'), 'icon' => 'sell', 'meta' => 'Item price'],
    ]);
    ?>
    <?php detail_card_open('Product Information', 'inventory_2'); ?>
        <div class="row">
            <div class="col-md-4">
                <?php if (!empty($item['image'])): ?><img src="<?= asset_url('uploads/' . $item['image']) ?>" class="detail-media mb-3" alt=""><?php endif; ?>
            </div>
            <div class="col-md-8">
                <?php detail_fields([
                    'Product Name' => detail_text($item['name'] ?? '-'),
                    'Language' => detail_text(strtoupper($item['language'] ?? '-')),
                    'Category' => detail_text(product_category_label($item['category'] ?? '', $item['language'] ?? 'en')),
                    'Status' => '<span class="' . detail_badge_class($item['status'] ?? '') . '">' . detail_text($item['status'] ?? '-') . '</span>',
                    'Price' => detail_text(product_format_price($item['price'] ?? 0, $item['language'] ?? 'en')),
                ]); ?>
                <hr>
                <h5 class="fs-15 fw-bold mb-2">Web Description</h5>
                <div class="detail-body-text mb-3"><?= nl2br(detail_text($item['description'] ?? '')) ?></div>
                <h5 class="fs-15 fw-bold mb-2">Quotation Description</h5>
                <div class="detail-body-text"><?= nl2br(detail_text($item['quotation_description'] ?? '')) ?></div>
            </div>
        </div>
    <?php detail_card_close(); ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
