<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

/*
|--------------------------------------------------------------------------
| Ambil kategori produk yang aktif
|--------------------------------------------------------------------------
*/
$categories = [];
$category_query = mysqli_query($conn, "
    SELECT category, COUNT(*) AS total
    FROM products
    WHERE status = 'publish'
      AND category IS NOT NULL
      AND category != ''
    GROUP BY category
    ORDER BY 
        CASE 
            WHEN category = 'Fresh Coconut' THEN 0
            ELSE 1
        END,
        category ASC
");

while ($cat = mysqli_fetch_assoc($category_query)) {
    $categories[] = $cat;
}
?>

<div class="ltn__utilize-overlay"></div>

<!-- BREADCRUMB AREA START -->
<div class="ltn__breadcrumb-area ltn__breadcrumb-area-2 ltn__breadcrumb-color-white bg-overlay-theme-black-90 bg-image" data-bg="img/bg/background-header.webp">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__breadcrumb-inner ltn__breadcrumb-inner-2 justify-content-between">
                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">// Welcome to our company</h6>
                        <h1 class="section-title white-color">Product</h1>
                    </div>
                    <div class="ltn__breadcrumb-list">
                        <ul>
                            <li><a href="<?= $base_url ?>">Home</a></li>
                            <li>Product</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BREADCRUMB AREA END -->

<!-- PRODUCT TAB AREA START -->
<div class="ltn__product-tab-area ltn__product-gutter pt-115 pb-70">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title">Our Products</h1>
                </div>

                <?php if (!empty($categories)): ?>
                    <div class="ltn__tab-menu ltn__tab-menu-2 ltn__tab-menu-top-right-- text-uppercase text-center">
                        <div class="nav">
                            <?php foreach ($categories as $index => $cat): ?>
                                <a
                                    class="<?= $index === 0 ? 'active show' : '' ?>"
                                    data-bs-toggle="tab"
                                    href="#product_tab_<?= $index + 1 ?>"
                                >
                                    <?= htmlspecialchars($cat['category']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div class="tab-content">
                        <?php foreach ($categories as $index => $cat): ?>
                            <?php
                            $category_name = $cat['category'];

                            $stmt = mysqli_prepare($conn, "
                                SELECT id, name, slug, category, price, image, description, created_at
                                FROM products
                                WHERE status = 'publish' AND category = ?
                                ORDER BY created_at DESC
                                LIMIT 8
                            ");
                            mysqli_stmt_bind_param($stmt, "s", $category_name);
                            mysqli_stmt_execute($stmt);
                            $products_result = mysqli_stmt_get_result($stmt);
                            ?>

                            <div class="tab-pane fade <?= $index === 0 ? 'active show' : '' ?>" id="product_tab_<?= $index + 1 ?>">
                                <div class="ltn__product-tab-content-inner">
                                    <div class="row">
                                        <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($products_result)): ?>
                                                <div class="col-lg-4 col-md-6 col-12">
                                                    <div class="ltn__product-item ltn__product-item-3 text-center">
                                                        <div class="product-img">
                                                            <a href="<?= $base_url ?>product/<?= urlencode($row['slug']) ?>">
                                                                <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                                            </a>
                                                        </div>

                                                        <div class="product-info">
                                                            <h2 class="product-title">
                                                                <a href="<?= $base_url ?>product/<?= urlencode($row['slug']) ?>">
                                                                    <?= htmlspecialchars($row['name']) ?>
                                                                </a>
                                                            </h2>

                                                            <div class="product-price">
                                                                <!-- <span>$<?= number_format((float)$row['price'], 2) ?></span> -->
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <div class="col-12">
                                                <p class="text-center">No products found in this category.</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php mysqli_stmt_close($stmt); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p>No product categories available.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- PRODUCT TAB AREA END -->

<?php
include 'includes/call-to-action.php';
$section_title = 'Latest Blog';
$limit = 6;
include __DIR__ . '/../section_new_article.php';
?>