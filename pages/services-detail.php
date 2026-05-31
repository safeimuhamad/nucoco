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

$pageTitle = 'Services';

if (!isset($base_url)) {
    $base_url = function_exists('site_base_url') ? site_base_url() : 'https://nucoco.id/';
}

$is_en = current_lang() === 'en';
$lang = $is_en ? 'en' : 'id';

$service_detail_base = $is_en ? 'en/services' : 'layanan';
$service_list_base   = $is_en ? 'en/services' : 'layanan';
$contact_url         = $base_url . lang_url('en/contact', 'kontak');

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    die($is_en ? 'Service not found.' : 'Layanan tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| DETAIL SERVICE
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "
    SELECT 
        id, title, slug, image, image_mobile, image_tablet, image_desktop,
        content, seo_keywords, language, status, created_at, translation_group_id
    FROM services
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
$service = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$service) {
    http_response_code(404);
    die($is_en ? 'Service not found.' : 'Layanan tidak ditemukan.');
}

/*
|--------------------------------------------------------------------------
| PAIR SERVICE (bahasa pasangan)
|--------------------------------------------------------------------------
*/
$pair_lang = $is_en ? 'id' : 'en';
$service_pair = null;

if (!empty($service['translation_group_id'])) {
$pair_stmt = mysqli_prepare($conn, "
    SELECT 
        id, title, slug, image, image_mobile, image_tablet, image_desktop,
        content, seo_keywords, language, status, created_at, translation_group_id
    FROM services
    WHERE translation_group_id = ?
    AND language = ?
    AND status = 'publish'
    LIMIT 1
");

    if ($pair_stmt) {
        mysqli_stmt_bind_param($pair_stmt, "is", $service['translation_group_id'], $pair_lang);
        mysqli_stmt_execute($pair_stmt);
        $pair_result = mysqli_stmt_get_result($pair_stmt);
        $service_pair = mysqli_fetch_assoc($pair_result);
        mysqli_stmt_close($pair_stmt);
    }
}

/*
|--------------------------------------------------------------------------
| SEO URL
|--------------------------------------------------------------------------
*/
$seo = buildSeoUrls($service, $service_pair, $base_url, 'layanan', 'services');

/*
|--------------------------------------------------------------------------
| PAGE META untuk header.php
|--------------------------------------------------------------------------
*/
$page = [
    'meta_title' => $service['title'] ?? ($is_en ? 'Service Detail' : 'Detail Layanan'),
    'hero_title' => $service['title'] ?? ($is_en ? 'Service Detail' : 'Detail Layanan'),
    'meta_description' => !empty($service['content'])
    ? mb_strimwidth(strip_tags($service['content']), 0, 160, '...')
    : ($is_en ? 'Professional services from Nucoco.' : 'Layanan profesional dari Nucoco.'),
    'meta_keywords' => $service['seo_keywords'] ?? '',
    'meta_robots' => 'index, follow',
    'canonical_url' => $seo['canonical'],
    'alternate_id_url' => $seo['alt_id'],
    'alternate_en_url' => $seo['alt_en'],
    'og_title' => $service['title'] ?? '',
    'og_description' => !empty($service['content'])
    ? mb_strimwidth(strip_tags($service['content']), 0, 160, '...')
    : ($is_en ? 'Professional services from Nucoco.' : 'Layanan profesional dari Nucoco.'),
    'og_image' => !empty($service['image'])
    ? $base_url . 'uploads/services/' . $service['image']
    : $base_url . 'img/service/1.jpg',
    'schema_markup' => ''
];

$current_page = 'services';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

/*
|--------------------------------------------------------------------------
| RELATED SERVICES
|--------------------------------------------------------------------------
*/
$related_services = [];

$stmt = mysqli_prepare($conn, "
    SELECT 
        id, title, slug, image, image_mobile, image_tablet, image_desktop,
        content, created_at, language, translation_group_id
    FROM services
    WHERE status = 'publish'
    AND language = ?
    AND id != ?
    ORDER BY created_at DESC
    LIMIT 5
");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $lang, $service['id']);
    mysqli_stmt_execute($stmt);
    $related_result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($related_result)) {
        $related_services[] = $row;
    }

    mysqli_stmt_close($stmt);
}

$service_image = !empty($service['image'])
? $base_url . 'uploads/services/' . htmlspecialchars($service['image'])
: $base_url . 'img/service/1.jpg';
?>
<main id="main-content" role="main">
    <div class="ltn__utilize-overlay"></div>
    <div class="ltn__breadcrumb-area ltn__breadcrumb-area-2 ltn__breadcrumb-color-white bg-overlay-theme-black-90 bg-image" data-bg="<?= $base_url ?>img/bg/background-header.webp">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__breadcrumb-inner ltn__breadcrumb-inner-2 justify-content-between">
                        <div class="section-title-area ltn__section-title-2">
                            <h6 class="section-subtitle ltn__secondary-color">
                                // <?= $is_en ? 'Our Services' : 'Layanan Kami' ?>
                            </h6>
                            <h1 class="section-title white-color"><?= htmlspecialchars($service['title']) ?></h1>
                        </div>
                        <div class="ltn__breadcrumb-list">
                            <ul>
                                <li><a href="<?= $base_url ?>"><?= $is_en ? 'Home' : 'Beranda' ?></a></li>
                                <li><a href="<?= $base_url . $service_list_base ?>"><?= $is_en ? 'Services' : 'Layanan' ?></a></li>
                                <li><?= htmlspecialchars($service['title']) ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="ltn__page-details-area ltn__service-details-area mb-105">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="ltn__page-details-inner ltn__service-details-inner">
                        <div class="ltn__blog-img">
                            <?php
                            $image_main = $service['image'] ?? '';

                            $image_mobile  = !empty($service['image_mobile']) ? $service['image_mobile'] : $image_main;
                            $image_tablet  = !empty($service['image_tablet']) ? $service['image_tablet'] : $image_main;
                            $image_desktop = !empty($service['image_desktop']) ? $service['image_desktop'] : $image_main;

                            $service_alt = htmlspecialchars($service['title'] ?? 'Service Image', ENT_QUOTES, 'UTF-8');
                            ?>
                            <img
                            src="<?= $base_url ?>uploads/services/<?= htmlspecialchars($image_mobile, ENT_QUOTES, 'UTF-8') ?>"
                            srcset="
                            <?= $base_url ?>uploads/services/<?= htmlspecialchars($image_mobile, ENT_QUOTES, 'UTF-8') ?> 480w,
                            <?= $base_url ?>uploads/services/<?= htmlspecialchars($image_tablet, ENT_QUOTES, 'UTF-8') ?> 768w,
                            <?= $base_url ?>uploads/services/<?= htmlspecialchars($image_desktop, ENT_QUOTES, 'UTF-8') ?> 1200w
                            "
                            sizes="(max-width: 768px) 100vw, (max-width: 1200px) 80vw, 770px"
                            width="770"
                            height="499"
                            loading="lazy"
                            decoding="async"
                            alt="<?= $service_alt ?>"
                            class="service-detail-image"
                            >
                        </div>
                        <h2 class="mt-4 mb-3"><?= htmlspecialchars($service['title']) ?></h2>
                        <div class="service-description">
                            <?php if (!empty($service['content'])): ?>
                                <?= nl2br(htmlspecialchars($service['content'])) ?>
                            <?php else: ?>
                                <p><?= $is_en ? 'No service description available.' : 'Deskripsi layanan belum tersedia.' ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="btn-wrapper mt-4">
                            <a href="<?= $contact_url ?>" class="theme-btn-1 btn btn-effect-1 text-uppercase">
                                <?= $is_en ? 'Request Consultation' : 'Konsultasi Sekarang' ?>
                            </a>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <aside class="sidebar-area ltn__right-sidebar">

                        <?php if (!empty($related_services)): ?>
                            <div class="widget-2 ltn__menu-widget ltn__menu-widget-2 text-uppercase">
                                <h3 class="ltn__widget-title ltn__widget-title-border">
                                    <?= $is_en ? 'Other Services' : 'Layanan Lainnya' ?>
                                </h3>
                                <ul>
                                    <?php foreach ($related_services as $item): ?>
                                        <li>
                                            <a href="<?= $base_url . $service_detail_base . '/' . urlencode($item['slug']) ?>">
                                                <?= htmlspecialchars($item['title']) ?>
                                                <span><i class="fas fa-arrow-right"></i></span>
                                            </a>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>

                        <div class="widget ltn__search-widget ltn__newsletter-widget">
                            <h3 class="ltn__widget-sub-title">
                                // <?= $is_en ? 'subscribe' : 'berlangganan' ?>
                            </h3>
                            <h4 class="ltn__widget-title">
                                <?= $is_en ? 'Get Newsletter' : 'Dapatkan Newsletter' ?>
                            </h4>
                            <form action="#">
                                <input type="mail" name="mail" placeholder="<?= $is_en ? 'Email address' : 'Alamat email' ?>">
                                <button type="submit" aria-label="subscribe"><i class="fas fa-location-arrow"></i></button>
                            </form>
                            <div class="ltn__newsletter-bg-icon">
                                <i class="fas fa-envelope-open-text"></i>
                            </div>
                        </div>

                        <div class="widget ltn__banner-widget">
                            <a href="<?= $base_url . lang_url('en/product/desiccated-coconut', 'produk/kelapa-kering') ?>">
                                <img src="<?= $base_url ?>uploads/69e0ba98082309.19725968.webp" alt="Contact Nucoco">
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
