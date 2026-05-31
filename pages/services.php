<?php
if (!isset($conn)) {
    require_once __DIR__ . '/../admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

if (file_exists(__DIR__ . '/../includes/helpers.php')) {
    require_once __DIR__ . '/../includes/helpers.php';
}

if (file_exists(__DIR__ . '/../admin/includes/helpers.php')) {
    require_once __DIR__ . '/../admin/includes/helpers.php';
}

if (!function_exists('current_lang')) {
    function current_lang() {
        return (isset($_GET['lang']) && $_GET['lang'] === 'en') ? 'en' : 'id';
    }
}

if (!function_exists('lang_url')) {
    function lang_url($en, $id) {
        return current_lang() === 'en' ? $en : $id;
    }
}

$is_en = current_lang() === 'en';
$lang = $is_en ? 'en' : 'id';

$service_detail_base = $is_en ? 'en/services' : 'layanan';
$service_list_base   = $is_en ? 'en/services' : 'layanan';

$limit = 6;
$page_num = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page_num = max($page_num, 1);
$start = ($page_num - 1) * $limit;

$page_slug = 'services';

/*
|--------------------------------------------------------------------------
| COUNT TOTAL DATA
|--------------------------------------------------------------------------
*/
$count_sql = "SELECT COUNT(*) as total
              FROM services
              WHERE status = 'publish'
              AND language = ?";
$count_stmt = mysqli_prepare($conn, $count_sql);

if (!$count_stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($count_stmt, "s", $lang);
mysqli_stmt_execute($count_stmt);
$total_result = mysqli_stmt_get_result($count_stmt);

if (!$total_result) {
    die('Count query error: ' . mysqli_error($conn));
}

$total_row = mysqli_fetch_assoc($total_result);
$total_data = (int) ($total_row['total'] ?? 0);
$total_pages = ($total_data > 0) ? (int) ceil($total_data / $limit) : 1;
mysqli_stmt_close($count_stmt);

/*
|--------------------------------------------------------------------------
| LIST DATA
|--------------------------------------------------------------------------
*/
$query = "SELECT id, title, slug, image, content, created_at
          FROM services
          WHERE status = 'publish'
          AND language = ?
          ORDER BY id DESC
          LIMIT ?, ?";

$stmt = mysqli_prepare($conn, $query);

if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "sii", $lang, $start, $limit);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die('List query error: ' . mysqli_error($conn));
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
                            // <?= $is_en ? 'Welcome to our company' : 'Selamat datang di perusahaan kami' ?>
                        </h6>
                        <h1 class="section-title white-color">
                            <?= page_label($page_slug) ?>
                        </h1>
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

<!-- ABOUT US AREA START -->
<div class="ltn__about-us-area pb-115">
    <div class="container">
        <div class="row">
            <div class="col-lg-5 align-self-center">
                <div class="about-us-img-wrap ltn__img-shape-left about-img-left">
                    <img src="<?= $base_url ?>img/service/services_image.webp" alt="<?= $is_en ? 'Services Image' : 'Gambar Layanan' ?>">
                </div>
            </div>
            <div class="col-lg-7 align-self-center">
                <div class="about-us-info-wrap">
                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">
                            <?= $is_en ? 'RELIABLE COCONUT SERVICES //' : 'LAYANAN KELAPA TERPERCAYA //' ?>
                        </h6>
                        <h1 class="section-title">
                            <?= $is_en
                                ? 'Your Trusted Partner for Global Coconut Supply'
                                : 'Mitra Terpercaya Anda untuk Pasokan Kelapa Global' ?>
                        </h1>
                        <p dir="ltr">
                            <?= $is_en
                                ? 'We provide high-quality coconut-based products and integrated services for global markets. From sourcing and manufacturing to export and private labeling, Nucoco ensures reliable supply, consistent quality, and professional support for your business growth.'
                                : 'Kami menyediakan produk berbasis kelapa berkualitas tinggi dan layanan terintegrasi untuk pasar global. Mulai dari sourcing, manufaktur, ekspor, hingga private label, Nucoco memastikan pasokan yang andal, kualitas yang konsisten, dan dukungan profesional bagi pertumbuhan bisnis Anda.' ?>
                        </p>
                    </div>

                    <div class="about-us-info-wrap-inner about-us-info-devide">
                        <p dir="ltr">
                            <?= $is_en
                                ? 'Nucoco is committed to delivering premium coconut products with a strong supply chain across Indonesia. We support importers, manufacturers, and global brands with scalable solutions, flexible production, and export-ready services tailored to meet international standards.'
                                : 'Nucoco berkomitmen menghadirkan produk kelapa premium dengan rantai pasok yang kuat di seluruh Indonesia. Kami mendukung importir, manufaktur, dan brand global dengan solusi yang scalable, produksi yang fleksibel, serta layanan siap ekspor yang dirancang untuk memenuhi standar internasional.' ?>
                        </p>

                        <div class="list-item-with-icon">
                            <ul>
                                <li><a href="<?= $base_url . lang_url('en/contact', 'kontak') ?>"><?= $is_en ? 'Reliable Bulk Supply' : 'Pasokan Bulk Andal' ?></a></li>
                                <li><a href="<?= $base_url . lang_url('en/contact', 'kontak') ?>"><?= $is_en ? 'Global Export Expertise' : 'Keahlian Ekspor Global' ?></a></li>
                                <li><a href="<?= $base_url . $service_list_base ?>"><?= $is_en ? 'Custom Product Solutions' : 'Solusi Produk Kustom' ?></a></li>
                                <li><a href="<?= $base_url . lang_url('en/contact', 'kontak') ?>"><?= $is_en ? 'Certified Quality Standards' : 'Standar Kualitas Bersertifikat' ?></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ABOUT US AREA END -->

<!-- SERVICE AREA START -->
<div class="ltn__service-area section-bg-1 pt-115 pb-70">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title white-color---">
                        <?= $is_en ? 'Our Services' : 'Layanan Kami' ?>
                    </h1>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <?php if ($total_data > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $title = mb_strimwidth(trim($row['title'] ?? ''), 0, 45, '...');
                    $desc  = mb_strimwidth(trim(strip_tags($row['content'] ?? '')), 0, 120, '...');
                    $image = !empty($row['image'])
                        ? $base_url . 'uploads/services/' . htmlspecialchars($row['image'])
                        : $base_url . 'img/service/1.jpg';
                    $detail_url = $base_url . $service_detail_base . '/' . urlencode($row['slug'] ?? '');
                    ?>
                    <div class="col-lg-4 col-sm-6 mb-4 d-flex">
                        <div class="ltn__service-item-1 w-100" style="display:flex; flex-direction:column; height:100%; border:1px solid #ddd;">
                            <div class="service-item-img" style="background:#fff; line-height:0;">
                                <a href="<?= $detail_url ?>">
                                    <img 
                                        src="<?= $image ?>" 
                                        alt="<?= htmlspecialchars($row['title'] ?? '') ?>"
                                        style="width:100%; height:auto; display:block;"
                                    >
                                </a>
                            </div>

                            <div class="service-item-brief text-center" style="padding:30px 25px; min-height:170px;">
                                <h3 style="min-height:65px; margin-bottom:15px;">
                                    <a href="<?= $detail_url ?>">
                                        <?= htmlspecialchars($title) ?>
                                    </a>
                                </h3>
                                <p dir="ltr" style="margin-bottom:0;">
                                    <?= htmlspecialchars($desc) ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12 text-center">
                    <p dir="ltr"><?= $is_en ? 'No services available at the moment.' : 'Belum ada layanan saat ini.' ?></p>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="row">
                <div class="col-lg-12">
                    <div class="ltn__pagination-area text-center">
                        <div class="ltn__pagination" dir="ltr">
                            <ul>
                                <li class="<?= ($page_num <= 1) ? 'disabled' : '' ?>">
                                    <a href="<?= ($page_num > 1) ? ($base_url . $service_list_base . '?page=' . ($page_num - 1)) : '#' ?>">
                                        <i class="fas fa-angle-double-left"></i>
                                    </a>
                                </li>

                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="<?= ($page_num == $i) ? 'active' : '' ?>">
                                        <a href="<?= $base_url . $service_list_base . '?page=' . $i ?>">
                                            <?= $i ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>

                                <li class="<?= ($page_num >= $total_pages) ? 'disabled' : '' ?>">
                                    <a href="<?= ($page_num < $total_pages) ? ($base_url . $service_list_base . '?page=' . ($page_num + 1)) : '#' ?>">
                                        <i class="fas fa-angle-double-right"></i>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<!-- SERVICE AREA END -->

<!-- OUR JOURNEY AREA START -->
<div class="ltn__our-journey-area bg-image bg-overlay-theme-90 pt-280 pb-350 mb-35 plr--9" data-bg="<?= $base_url ?>img/bg/8.jpg">
    <div class="container-fluid">
        <div class="row"> 
            <div class="col-lg-12">
                <div class="ltn__our-journey-wrap">
                    <ul>
                        <li><span class="ltn__journey-icon">2010</span>
                            <ul>
                                <li>
                                    <div class="ltn__journey-history-item-info clearfix">
                                        <div class="ltn__journey-history-img">
                                            <img src="<?= $base_url ?>img/service/timeline-1.webp" alt="#">
                                        </div>
                                        <div class="ltn__journey-history-info">
                                            <h3><?= $is_en ? 'Company Established' : 'Perusahaan Didirikan' ?></h3>
                                            <p dir="ltr"><?= $is_en ? 'Nucoco was founded as a coconut-based product supplier in Indonesia.' : 'Nucoco didirikan sebagai pemasok produk berbasis kelapa di Indonesia.' ?></p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li class="active"><span class="ltn__journey-icon">2015</span>
                            <ul>
                                <li>
                                    <div class="ltn__journey-history-item-info clearfix">
                                        <div class="ltn__journey-history-img">
                                            <img src="<?= $base_url ?>img/service/timeline-2.webp" alt="#">
                                        </div>
                                        <div class="ltn__journey-history-info">
                                            <h3><?= $is_en ? 'Local Supply Expansion' : 'Ekspansi Pasokan Lokal' ?></h3>
                                            <p dir="ltr"><?= $is_en ? 'Expanded sourcing network across major coconut-producing regions in Indonesia.' : 'Memperluas jaringan sourcing di berbagai wilayah penghasil kelapa utama di Indonesia.' ?></p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li><span class="ltn__journey-icon">2020</span>
                            <ul>
                                <li>
                                    <div class="ltn__journey-history-item-info clearfix">
                                        <div class="ltn__journey-history-img">
                                            <img src="<?= $base_url ?>img/service/timeline-3.webp" alt="#">
                                        </div>
                                        <div class="ltn__journey-history-info">
                                            <h3><?= $is_en ? 'Industrial & Bulk Supply Development' : 'Pengembangan Pasokan Industri & Bulk' ?></h3>
                                            <p dir="ltr"><?= $is_en ? 'Started supplying coconut products for industrial and manufacturing sectors.' : 'Mulai memasok produk kelapa untuk sektor industri dan manufaktur.' ?></p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li><span class="ltn__journey-icon">2022</span>
                            <ul>
                                <li>
                                    <div class="ltn__journey-history-item-info clearfix">
                                        <div class="ltn__journey-history-img">
                                            <img src="<?= $base_url ?>img/service/timeline-4.webp" alt="#">
                                        </div>
                                        <div class="ltn__journey-history-info">
                                            <h3><?= $is_en ? 'Global Export Market Entry' : 'Masuk ke Pasar Ekspor Global' ?></h3>
                                            <p><?= $is_en ? 'Began exporting to international markets with full export compliance.' : 'Mulai mengekspor ke pasar internasional dengan kepatuhan ekspor yang lengkap.' ?></p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>

                        <li><span class="ltn__journey-icon">2025</span>
                            <ul>
                                <li>
                                    <div class="ltn__journey-history-item-info clearfix">
                                        <div class="ltn__journey-history-img">
                                            <img src="<?= $base_url ?>img/service/timeline-5.webp" alt="#">
                                        </div>
                                        <div class="ltn__journey-history-info">
                                            <h3><?= $is_en ? 'Integrated Coconut Solutions' : 'Solusi Kelapa Terintegrasi' ?></h3>
                                            <p dir="ltr"><?= $is_en ? 'Providing end-to-end services including OEM, customization, and global supply.' : 'Menyediakan layanan end-to-end termasuk OEM, kustomisasi, dan pasokan global.' ?></p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- OUR JOURNEY AREA END -->

<!-- VIDEO AREA START -->
<div class="ltn__video-popup-area ltn__video-popup-margin-2">
    <div class="container">
        <div class="row">
            <div class="col-lg-10 offset-lg-1">
                <div class="ltn__video-bg-img ltn__video-popup-height-600"
                     style="position: relative; overflow: hidden; background: #8fc31f;">

                    <img src="<?= $base_url ?>img/bg/bg_youtube.webp"
                         alt="Nucoco Video Thumbnail"
                         style="width: 100%; height: 100%; object-fit: contain; display: block;">

                    <a class="ltn__video-icon-2 ltn__video-icon-2-border---"
                       href="https://www.youtube.com/embed/1criic0Gsyg?si=yugqSO9EvCmSsecX"
                       data-rel="lightcase:myCollection"
                       style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 2;">
                        <i class="fa fa-play"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- VIDEO AREA END -->
<?php
include 'includes/call-to-action.php';
include __DIR__ . '/../section_choose.php';
mysqli_stmt_close($stmt);
?>