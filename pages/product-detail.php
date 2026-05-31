<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

if (!isset($conn)) {
    require_once __DIR__ . '/../admin/includes/db.php';
}

if (file_exists(__DIR__ . '/../includes/helpers.php')) {
    require_once __DIR__ . '/../includes/helpers.php';
}

if (file_exists(__DIR__ . '/../admin/includes/helpers.php')) {
    require_once __DIR__ . '/../admin/includes/helpers.php';
}

if (!function_exists('lang_url')) {
    function lang_url($en, $id) {
        return current_lang() === 'en' ? $en : $id;
    }
}

$pageTitle = 'Product';

if (!isset($base_url)) {
    $base_url = function_exists('site_base_url') ? site_base_url() : 'https://nucoco.id/';
}

$is_en = current_lang() === 'en';
$lang = $is_en ? 'en' : 'id';

$product_detail_base = $is_en ? 'en/product' : 'produk';
$product_list_base   = $is_en ? 'en/product' : 'produk';
$contact_url         = $base_url . lang_url('en/contact', 'kontak');

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    die($is_en ? 'Product not found.' : 'Produk tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| DETAIL PRODUCT
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "
    SELECT id, name, slug, category, image, description, price, seo_keywords, language, created_at, translation_group_id, parent_id
    FROM products
    WHERE slug = ?
      AND language = ?
      AND status = 'publish'
    LIMIT 1
");

if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ss", $slug, $lang);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$product) {
    http_response_code(404);
    die($is_en ? 'Product not found.' : 'Produk tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| PAIR PRODUCT (bahasa pasangan)
|--------------------------------------------------------------------------
*/
$pair_lang = $is_en ? 'id' : 'en';
$product_pair = null;

if (!empty($product['translation_group_id'])) {
    $pair_stmt = mysqli_prepare($conn, "
        SELECT id, name, slug, category, image, description, price, seo_keywords, language, created_at, translation_group_id, parent_id
        FROM products
        WHERE translation_group_id = ?
          AND language = ?
          AND status = 'publish'
        LIMIT 1
    ");

    if ($pair_stmt) {
        mysqli_stmt_bind_param($pair_stmt, "is", $product['translation_group_id'], $pair_lang);
        mysqli_stmt_execute($pair_stmt);
        $pair_result = mysqli_stmt_get_result($pair_stmt);
        $product_pair = mysqli_fetch_assoc($pair_result);
        mysqli_stmt_close($pair_stmt);
    }
}

/*
|--------------------------------------------------------------------------
| SEO URL
|--------------------------------------------------------------------------
*/
$seo = buildSeoUrls($product, $product_pair, $base_url, 'produk', 'product');

/*
|--------------------------------------------------------------------------
| PAGE META untuk header.php
|--------------------------------------------------------------------------
*/
$page = [
    'meta_title' => $product['name'] ?? ($is_en ? 'Product Detail' : 'Detail Produk'),
    'hero_title' => $product['name'] ?? ($is_en ? 'Product Detail' : 'Detail Produk'),
    'meta_description' => !empty($product['description'])
        ? mb_strimwidth(strip_tags($product['description']), 0, 160, '...')
        : ($is_en ? 'High quality coconut products from Nucoco.' : 'Produk kelapa berkualitas tinggi dari Nucoco.'),
    'meta_keywords' => $product['seo_keywords'] ?? '',
    'meta_robots' => 'index, follow',
    'canonical_url' => $seo['canonical'],
    'alternate_id_url' => $seo['alt_id'],
    'alternate_en_url' => $seo['alt_en'],
    'og_title' => $product['name'] ?? '',
    'og_description' => !empty($product['description'])
        ? mb_strimwidth(strip_tags($product['description']), 0, 160, '...')
        : ($is_en ? 'High quality coconut products from Nucoco.' : 'Produk kelapa berkualitas tinggi dari Nucoco.'),
    'og_image' => !empty($product['image'])
        ? $base_url . 'uploads/' . $product['image']
        : $base_url . 'img/product/1.png',
    'schema_markup' => ''
];

$current_page = 'product';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

/*
|--------------------------------------------------------------------------
| RELATED PRODUCTS
|--------------------------------------------------------------------------
*/
$related_products = [];

$stmt = mysqli_prepare($conn, "
    SELECT id, name, slug, category, image, price, description, created_at, language, translation_group_id, parent_id
    FROM products
    WHERE status = 'publish'
      AND language = ?
      AND category = ?
      AND id != ?
    ORDER BY created_at DESC
    LIMIT 3
");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ssi", $lang, $product['category'], $product['id']);
    mysqli_stmt_execute($stmt);
    $related_result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($related_result)) {
        $related_products[] = $row;
    }

    mysqli_stmt_close($stmt);
}

$product_image = !empty($product['image'])
    ? $base_url . 'uploads/' . htmlspecialchars($product['image'])
    : $base_url . 'img/product/1.png';

$current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
?>
<div class="ltn__utilize-overlay"></div>
<main id="main-content" role="main">
<!-- BREADCRUMB AREA START -->
<div class="ltn__breadcrumb-area ltn__breadcrumb-area-2 ltn__breadcrumb-color-white bg-overlay-theme-black-90 bg-image" data-bg="<?= $base_url ?>img/bg/background-header.webp">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__breadcrumb-inner ltn__breadcrumb-inner-2 justify-content-between">

                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">
                            // <?= $is_en ? 'Our Products' : 'Produk Kami' ?>
                        </h6>
                        <h1 class="section-title white-color">
                            <?= htmlspecialchars($product['name']) ?>
                        </h1>
                    </div>

                    <div class="ltn__breadcrumb-list">
                        <ul>
                            <li><a href="<?= $base_url ?>"><?= $is_en ? 'Home' : 'Beranda' ?></a></li>
                            <li><a href="<?= $base_url . $product_list_base ?>"><?= $is_en ? 'Product' : 'Produk' ?></a></li>
                            <li><?= htmlspecialchars($product['name']) ?></li>
                        </ul>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- BREADCRUMB AREA END -->

<div class="ltn__shop-details-area pb-85 pt-115">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="ltn__shop-details-inner mb-60">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="ltn__shop-details-img-gallery">
                                <div class="ltn__shop-details-large-img">
                                    <div class="single-large-img">
                                        <a href="<?= $product_image ?>" data-rel="lightcase:myCollection">
                                            <img src="<?= $product_image ?>" alt="<?= htmlspecialchars($product['name']) ?>">
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="modal-product-info shop-details-info pl-0">
                                <h2><?= htmlspecialchars($product['name']) ?></h2>

                                <?php if (!empty($product['price']) && (float) $product['price'] > 0): ?>
                                    <div class="product-price">
                                        <span>$<?= number_format((float) $product['price'], 2) ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="modal-product-meta ltn__product-details-menu-1">
                                    <ul>
                                        <li>
                                            <strong><?= $is_en ? 'Category:' : 'Kategori:' ?></strong>
                                            <span>
                                                <a href="<?= $base_url . $product_list_base ?>">
                                                    <?= htmlspecialchars($product['category']) ?>
                                                </a>
                                            </span>
                                        </li>
                                        <li>
                                            <strong><?= $is_en ? 'Availability:' : 'Ketersediaan:' ?></strong>
                                            <span><?= $is_en ? 'Available for Inquiry' : 'Tersedia untuk Permintaan' ?></span>
                                        </li>
                                    </ul>
                                </div>

                                <div class="ltn__product-details-menu-2 pt-30">
                                    <ul>
                                        <li>
                                            <a href="<?= $contact_url ?>" class="theme-btn-1 btn btn-effect-1" title="<?= $is_en ? 'Request Quote' : 'Minta Penawaran' ?>">
                                                <i class="fas fa-envelope"></i>
                                                <span><?= $is_en ? 'REQUEST QUOTE' : 'MINTA PENAWARAN' ?></span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="ltn__social-media">
                                    <ul>
                                        <li><?= $is_en ? 'Share:' : 'Bagikan:' ?></li>
                                        <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($current_url) ?>" target="_blank" rel="noopener" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                        <li><a href="https://twitter.com/intent/tweet?url=<?= urlencode($current_url) ?>" target="_blank" rel="noopener" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                                        <li><a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($current_url) ?>" target="_blank" rel="noopener" title="Linkedin"><i class="fab fa-linkedin"></i></a></li>
                                        <li><a href="https://wa.me/?text=<?= urlencode(($product['name'] ?? '') . ' - ' . $current_url) ?>" target="_blank" rel="noopener" title="WhatsApp"><i class="fab fa-whatsapp"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Shop Tab Start -->
                <div class="ltn__shop-details-tab-inner ltn__shop-details-tab-inner-2">
                    <div class="ltn__shop-details-tab-menu">
                        <div class="nav">
                            <a class="active show" data-bs-toggle="tab" href="#liton_tab_details_1_1">
                                <?= $is_en ? 'Description' : 'Deskripsi' ?>
                            </a>
                        </div>
                    </div>

                    <div class="tab-content">
                        <div class="tab-pane fade active show" id="liton_tab_details_1_1">
                            <div class="ltn__shop-details-tab-content-inner">
                                <h3 class="title-2"><?= htmlspecialchars($product['name']) ?></h3>
                                <p>
                                    <?= !empty($product['description'])
                                        ? nl2br(htmlspecialchars($product['description']))
                                        : ($is_en ? 'No detailed description available for this product yet.' : 'Belum ada deskripsi detail untuk produk ini.') ?>
                                </p>

                                <?php if (!empty($product['seo_keywords'])): ?>
                                    <div class="pt-20">
                                        <strong><?= $is_en ? 'Keywords:' : 'Kata Kunci:' ?></strong>
                                        <p><?= htmlspecialchars($product['seo_keywords']) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ltn__safe-checkout">
                        <h3><?= $is_en ? 'Need more details?' : 'Butuh detail lebih lanjut?' ?></h3>
                        <p>
                            <?= $is_en
                                ? 'Contact our team for product specifications, bulk pricing, and export support.'
                                : 'Hubungi tim kami untuk spesifikasi produk, harga grosir, dan dukungan ekspor.' ?>
                        </p>
                    </div>
                </div>
                <!-- Shop Tab End -->
            </div>

            <div class="col-lg-4">
                <aside class="sidebar ltn__shop-sidebar ltn__right-sidebar">
                    <?php if (!empty($related_products)): ?>
                        <div class="widget ltn__top-rated-product-widget">
                            <h3 class="ltn__widget-title ltn__widget-title-border">
                                <?= $is_en ? 'Related Products' : 'Produk Terkait' ?>
                            </h3>
                            <ul>
                                <?php foreach ($related_products as $item): ?>
                                    <?php
                                    $related_image = !empty($item['image'])
                                        ? $base_url . 'uploads/' . htmlspecialchars($item['image'])
                                        : $base_url . 'img/product/1.png';
                                    $related_url = $base_url . $product_detail_base . '/' . urlencode($item['slug']);
                                    ?>
                                    <li>
                                        <div class="top-rated-product-item clearfix">
                                            <div class="top-rated-product-img">
                                                <a href="<?= $related_url ?>">
                                                    <img src="<?= $related_image ?>" alt="<?= htmlspecialchars($item['name']) ?>">
                                                </a>
                                            </div>
                                            <div class="top-rated-product-info">
                                                <h3>
                                                    <a href="<?= $related_url ?>">
                                                        <?= htmlspecialchars($item['name']) ?>
                                                    </a>
                                                </h3>

                                                <?php if (!empty($item['price']) && (float) $item['price'] > 0): ?>
                                                    <div class="product-price">
                                                        <span>$<?= number_format((float) $item['price'], 2) ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <div class="widget ltn__banner-widget">
                        <a href="<?= $base_url . lang_url('en/services/product-customization', 'layanan/kostumisasi-produk') ?>">
                            <img src="<?= $base_url ?>uploads/services/69eb0e113b14d3.71691352.webp" alt="Contact Nucoco">
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>

<?php
include __DIR__ . '/../includes/call-to-action.php';
include __DIR__ . '/../section_choose.php';
?>
</main>
<?php
include __DIR__ . '/../includes/footer.php';
?>
