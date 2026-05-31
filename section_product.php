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

if (file_exists(__DIR__ . '/admin/includes/helpers.php')) {
    require_once __DIR__ . '/admin/includes/helpers.php';
}

$is_en = current_lang() === 'en';
$lang  = $is_en ? 'en' : 'id';

$product_detail_base = $is_en ? 'en/product' : 'produk';
$product_list_base   = $is_en ? 'en/product' : 'produk';

$section_title = $is_en ? 'Our Products' : 'Produk Kami';
$limit = 3;

/*
|--------------------------------------------------------------------------
| Ambil kategori produk yang aktif sesuai bahasa
|--------------------------------------------------------------------------
*/
$categories = [];

$category_sql = "
    SELECT category, COUNT(*) AS total
    FROM products
    WHERE status = 'publish'
      AND language = ?
      AND category IS NOT NULL
      AND category != ''
    GROUP BY category
    ORDER BY 
        CASE 
            WHEN category = 'Fresh Coconut' THEN 0
            ELSE 1
        END,
        category ASC
";

$category_stmt = mysqli_prepare($conn, $category_sql);

if ($category_stmt) {
    mysqli_stmt_bind_param($category_stmt, "s", $lang);
    mysqli_stmt_execute($category_stmt);
    $category_result = mysqli_stmt_get_result($category_stmt);

    while ($cat = mysqli_fetch_assoc($category_result)) {
        $categories[] = $cat;
    }

    mysqli_stmt_close($category_stmt);
}
?>

<!-- PRODUCT TAB AREA START -->
<div class="ltn__product-tab-area ltn__product-gutter pt-115 pb-70">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title">
                        <?= htmlspecialchars($section_title) ?>
                    </h1>
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
                                SELECT name, slug, category, price, image, description, created_at
                                FROM products
                                WHERE status = 'publish'
                                  AND language = ?
                                  AND category = ?
                                ORDER BY created_at DESC
                                LIMIT ?
                            ");

                            $products_result = false;

                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, "ssi", $lang, $category_name, $limit);
                                mysqli_stmt_execute($stmt);
                                $products_result = mysqli_stmt_get_result($stmt);
                            }
                            ?>

                            <div class="tab-pane fade <?= $index === 0 ? 'active show' : '' ?>" id="product_tab_<?= $index + 1 ?>">
                                <div class="ltn__product-tab-content-inner">
                                    <div class="row">
                                        <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                                            <?php while ($row = mysqli_fetch_assoc($products_result)): ?>
                                                <?php
                                                $detail_url = $base_url . $product_detail_base . '/' . urlencode($row['slug']);
                                                $image = !empty($row['image'])
                                                    ? $base_url . 'uploads/' . htmlspecialchars($row['image'])
                                                    : $base_url . 'img/product/1.png';
                                                ?>
                                                <div class="col-lg-4 col-md-6 col-12">
                                                    <div class="ltn__product-item ltn__product-item-3 text-center">
                                                        <div class="product-img">
                                                            <a href="<?= $detail_url ?>">
                                                                <img src="<?= $image ?>" alt="<?= htmlspecialchars($row['name']) ?>" loading="lazy" decoding="async" fetchpriority="low">
                                                            </a>
                                                        </div>

                                                        <div class="product-info">
                                                            <div class="product-ratting"></div>

                                                            <h2 class="product-title">
                                                                <a href="<?= $detail_url ?>">
                                                                    <?= htmlspecialchars($row['name']) ?>
                                                                </a>
                                                            </h2>

                                                            <div class="product-price">
                                                                <?php if (!empty($row['price']) && (float) $row['price'] > 0): ?>
                                                                    <span><?= htmlspecialchars(product_format_price($row['price'], $lang)) ?></span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>

                                            <div class="col-12 text-center mt-4">
                                                <a 
                                                    href="<?= $base_url . $product_list_base . '?category=' . urlencode($category_name) ?>" 
                                                    class="theme-btn-1 btn btn-effect-1 text-uppercase"
                                                >
                                                    <?= $is_en ? 'Explore ' . htmlspecialchars($category_name) : 'Lihat ' . htmlspecialchars($category_name) ?>
                                                </a>
                                            </div>
                                        <?php else: ?>
                                            <div class="col-12">
                                                <p class="text-center">
                                                    <?= $is_en ? 'No products found in this category.' : 'Tidak ada produk dalam kategori ini.' ?>
                                                </p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>

                            <?php if ($stmt) mysqli_stmt_close($stmt); ?>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p><?= $is_en ? 'No product categories available.' : 'Belum ada kategori produk tersedia.' ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<!-- PRODUCT TAB AREA END -->
