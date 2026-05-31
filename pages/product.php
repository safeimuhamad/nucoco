<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../admin/includes/db.php';
}
$page_slug = 'product';

if (!isset($base_url)) {
    $base_url = '/';
}

if (!function_exists('current_lang')) {
    require_once __DIR__ . '/../includes/helpers.php';
}

if (file_exists(__DIR__ . '/../admin/includes/helpers.php')) {
    require_once __DIR__ . '/../admin/includes/helpers.php';
}

$is_en = current_lang() === 'en';
$lang  = $is_en ? 'en' : 'id';

$product_detail_base = $is_en ? 'en/product' : 'produk';

/*
|--------------------------------------------------------------------------
| Ambil kategori produk sesuai bahasa
|--------------------------------------------------------------------------
*/
$categories = [];

$category_stmt = mysqli_prepare($conn, "
    SELECT category
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
");

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
<div class="ltn__utilize-overlay"></div>
<!-- BREADCRUMB AREA START -->
<div class="ltn__breadcrumb-area ltn__breadcrumb-area-2 ltn__breadcrumb-color-white bg-overlay-theme-black-90 bg-image" data-bg="<?= $base_url ?>img/bg/background-header.webp">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__breadcrumb-inner ltn__breadcrumb-inner-2 justify-content-between">

                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">
                            // <?= current_lang() === 'en' ? 'Welcome to our company' : 'Selamat datang di perusahaan kami' ?>
                        </h6>
                        <h6 class="section-title white-color">
                            <?= page_label($page_slug) ?>
                        </h6>
                    </div>

                    <div class="ltn__breadcrumb-list">
                        <ul>
                            <li>
                                <a href="<?= url() ?>">
                                    <?= page_label('home') ?>
                                </a>
                            </li>
                            <li><?= page_label($page_slug) ?></li>
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
                    <h1 class="section-title">
                        <?= $is_en ? 'Our Products' : 'Produk Kami' ?>
                    </h1>
                </div>

                <?php if (!empty($categories)): ?>
                    <div class="ltn__tab-menu ltn__tab-menu-2 text-uppercase text-center">
                        <div class="nav">
                            <?php foreach ($categories as $index => $cat): ?>
                                <a class="<?= $index === 0 ? 'active show' : '' ?>"
                                   data-bs-toggle="tab"
                                   href="#product_tab_<?= $index + 1 ?>">
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
                                SELECT name, slug, image, price, language
                                FROM products
                                WHERE status = 'publish'
                                  AND language = ?
                                  AND category = ?
                                ORDER BY created_at DESC
                            ");

                            $products_result = false;

                            if ($stmt) {
                                mysqli_stmt_bind_param($stmt, "ss", $lang, $category_name);
                                mysqli_stmt_execute($stmt);
                                $products_result = mysqli_stmt_get_result($stmt);
                            }
                            ?>

                            <div class="tab-pane fade <?= $index === 0 ? 'active show' : '' ?>" id="product_tab_<?= $index + 1 ?>">
                                <div class="row">

                                    <?php if ($products_result && mysqli_num_rows($products_result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($products_result)): ?>
                                            <?php
                                            $detail_url = $base_url . $product_detail_base . '/' . urlencode($row['slug']);
                                            $image = !empty($row['image'])
                                                ? $base_url . 'uploads/' . htmlspecialchars($row['image'])
                                                : $base_url . 'img/product/1.png';
                                            ?>
                                            <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                                                <div class="ltn__product-item ltn__product-item-3 text-center">

                                                    <div class="product-img">
                                                        <a href="<?= $detail_url ?>">
                                                            <img src="<?= $image ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                                        </a>
                                                    </div>

                                                    <div class="product-info">
                                                        <h2 class="product-title">
                                                            <a href="<?= $detail_url ?>">
                                                                <?= htmlspecialchars($row['name']) ?>
                                                            </a>
                                                        </h2>
                                                        <?php if (!empty($row['price']) && (float) $row['price'] > 0): ?>
                                                            <div class="product-price">
                                                                <span><?= htmlspecialchars(product_format_price($row['price'], $row['language'] ?? $lang)) ?></span>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>

                                                </div>
                                            </div>
                                        <?php endwhile; ?>

                                    <?php else: ?>
                                        <div class="col-12 text-center">
                                            <p>
                                                <?= $is_en 
                                                    ? 'No products found in this category.' 
                                                    : 'Tidak ada produk dalam kategori ini.' ?>
                                            </p>
                                        </div>
                                    <?php endif; ?>

                                </div>
                            </div>

                            <?php if ($stmt) mysqli_stmt_close($stmt); ?>
                        <?php endforeach; ?>
                    </div>

                <?php else: ?>
                    <div class="text-center">
                        <p>
                            <?= $is_en 
                                ? 'No product categories available.' 
                                : 'Belum ada kategori produk tersedia.' ?>
                        </p>
                    </div>
                <?php endif; ?>

            </div>
        </div>
    </div>
</div>
<!-- PRODUCT TAB AREA END -->

<?php
include 'includes/call-to-action.php';
include __DIR__ . '/../section_new_article.php';
include __DIR__ . '/../section_choose.php';
?>
