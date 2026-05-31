<?php
if (!isset($conn)) {
    require_once __DIR__ . '/admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

if (!function_exists('current_lang')) {
    require_once __DIR__ . '/includes/helpers.php';
}

$is_en = current_lang() === 'en';
$lang  = $is_en ? 'en' : 'id';

$product_detail_base = $is_en ? 'en/product' : 'produk';

$section_title = $is_en ? 'New Products' : 'Produk Terbaru';

$new_products = [];

$stmt = mysqli_prepare($conn, "
    SELECT name, slug, category, price, image, created_at
    FROM products
    WHERE status = 'publish'
      AND language = ?
    ORDER BY created_at DESC
    LIMIT 8
");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $lang);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $new_products[] = $row;
    }

    mysqli_stmt_close($stmt);
}
?>

<!-- PRODUCT AREA START -->
<div class="ltn__product-area ltn__product-gutter pt-115 pb-70">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title">
                        <?= htmlspecialchars($section_title) ?>
                    </h1>
                </div>
            </div>
        </div>

        <div class="row">
            <?php if (!empty($new_products)): ?>
                <?php foreach ($new_products as $row): ?>
                    <?php
                    $detail_url = $base_url . $product_detail_base . '/' . urlencode($row['slug']);

                    $image = !empty($row['image'])
                        ? $base_url . 'uploads/' . htmlspecialchars($row['image'])
                        : $base_url . 'img/product/1.png';
                    ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                        <div class="ltn__product-item ltn__product-item-3 text-left">

                            <div class="product-img">
                                <a href="<?= $detail_url ?>">
                                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy" decoding="async" fetchpriority="low">
                                </a>

                                <div class="product-badge">
                                    <ul>
                                        <li class="sale-badge">
                                            <?= $is_en ? 'New' : 'Baru' ?>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            <div class="product-info text-center">
                                <h2 class="product-title">
                                    <a href="<?= $detail_url ?>">
                                        <?= htmlspecialchars($row['name']) ?>
                                    </a>
                                </h2>
                            </div>

                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="col-12 text-center">
                    <p>
                        <?= $is_en 
                            ? 'No products available right now.' 
                            : 'Belum ada produk tersedia saat ini.' ?>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<!-- PRODUCT AREA END -->