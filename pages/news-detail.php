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

if (!isset($base_url)) {
    $base_url = function_exists('site_base_url') ? site_base_url() : 'https://nucoco.id/';
}

$is_en = current_lang() === 'en';
$lang = $is_en ? 'en' : 'id';

$news_detail_base = $is_en ? 'en/news' : 'berita';
$news_list_base   = $is_en ? 'en/news' : 'berita';

$slug = isset($_GET['slug']) ? trim($_GET['slug']) : '';

if ($slug === '') {
    http_response_code(404);
    die($is_en ? 'News not found.' : 'Artikel tidak ditemukan.');
}


/*
|--------------------------------------------------------------------------
| DETAIL NEWS
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "
    SELECT id, title, slug, category, image, content, seo_keywords, created_at, language, translation_group_id, parent_id
    FROM news
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
$news = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$news) {
    http_response_code(404);
    die($is_en ? 'News not found.' : 'Artikel tidak ditemukan.');
}
/*
|--------------------------------------------------------------------------
| PAIR NEWS (bahasa pasangan)
|--------------------------------------------------------------------------
*/
$pair_lang = $is_en ? 'id' : 'en';
$news_pair = null;

if (!empty($news['translation_group_id'])) {
    $pair_stmt = mysqli_prepare($conn, "
        SELECT id, title, slug, category, image, content, seo_keywords, created_at, language, translation_group_id, parent_id
        FROM news
        WHERE translation_group_id = ?
          AND language = ?
          AND status = 'publish'
        LIMIT 1
    ");

    if ($pair_stmt) {
        mysqli_stmt_bind_param($pair_stmt, "is", $news['translation_group_id'], $pair_lang);
        mysqli_stmt_execute($pair_stmt);
        $pair_result = mysqli_stmt_get_result($pair_stmt);
        $news_pair = mysqli_fetch_assoc($pair_result);
        mysqli_stmt_close($pair_stmt);
    }
}
/*
|--------------------------------------------------------------------------
| SEO URL
|--------------------------------------------------------------------------
*/
$seo = buildSeoUrls($news, $news_pair, $base_url, 'berita', 'news');

/*
|--------------------------------------------------------------------------
| PAGE META untuk header.php
|--------------------------------------------------------------------------
*/
$page = [
    'meta_title' => $news['title'] ?? ($is_en ? 'News Detail' : 'Detail Berita'),
    'hero_title' => $news['title'] ?? ($is_en ? 'News Detail' : 'Detail Berita'),
    'meta_description' => !empty($news['content'])
        ? mb_strimwidth(strip_tags($news['content']), 0, 160, '...')
        : ($is_en ? 'Latest news from Nucoco.' : 'Berita terbaru dari Nucoco.'),
    'meta_keywords' => $news['seo_keywords'] ?? '',
    'meta_robots' => 'index, follow',
    'canonical_url' => $seo['canonical'],
    'alternate_id_url' => $seo['alt_id'],
    'alternate_en_url' => $seo['alt_en'],
    'og_title' => $news['title'] ?? '',
    'og_description' => !empty($news['content'])
        ? mb_strimwidth(strip_tags($news['content']), 0, 160, '...')
        : ($is_en ? 'Latest news from Nucoco.' : 'Berita terbaru dari Nucoco.'),
    'og_image' => !empty($news['image'])
        ? $base_url . 'uploads/' . $news['image']
        : $base_url . 'img/blog/1.jpg',
    'schema_markup' => ''
];

$current_page = 'news';

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/navbar.php';

/*
|--------------------------------------------------------------------------
| RELATED NEWS
|--------------------------------------------------------------------------
*/
$related_news = [];

$stmt = mysqli_prepare($conn, "
    SELECT id, title, slug, category, image, content, seo_keywords, created_at, language, translation_group_id, parent_id
    FROM news
    WHERE status = 'publish'
      AND language = ?
      AND id != ?
    ORDER BY created_at DESC
    LIMIT 2
");
mysqli_stmt_bind_param($stmt, "si", $lang, $news['id']);
mysqli_stmt_execute($stmt);
$related_result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($related_result)) {
    $related_news[] = $row;
}
mysqli_stmt_close($stmt);

/*
|--------------------------------------------------------------------------
| CATEGORY DATA
|--------------------------------------------------------------------------
*/
$category_stmt = mysqli_prepare($conn, "
    SELECT category, COUNT(*) as total
    FROM news
    WHERE status = 'publish'
      AND language = ?
      AND category IS NOT NULL
      AND category != ''
    GROUP BY category
    ORDER BY category ASC
");
mysqli_stmt_bind_param($category_stmt, "s", $lang);
mysqli_stmt_execute($category_stmt);
$category_result = mysqli_stmt_get_result($category_stmt);

$news_image = !empty($news['image'])
    ? $base_url . 'uploads/' . htmlspecialchars($news['image'])
    : $base_url . 'img/blog/1.jpg';

$current_url = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

?>
<div class="ltn__utilize-overlay"></div>
<main id="main-content" role="main">
<div class="ltn__breadcrumb-area ltn__breadcrumb-area-2 ltn__breadcrumb-color-white bg-overlay-theme-black-90 bg-image" data-bg="<?= $base_url ?>img/bg/background-header.webp">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__breadcrumb-inner ltn__breadcrumb-inner-2 justify-content-between">
                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">
                            // <?= $is_en ? 'Latest News' : 'Berita Terbaru' ?>
                        </h6>
                        <h1 class="section-title white-color"><?= htmlspecialchars($news['title']) ?></h1>
                    </div>
                    <div class="ltn__breadcrumb-list">
                        <ul>
                            <li><a href="<?= $base_url ?>"><?= $is_en ? 'Home' : 'Beranda' ?></a></li>
                            <li><a href="<?= $base_url . $news_list_base ?>"><?= $is_en ? 'News' : 'Berita' ?></a></li>
                            <li><?= htmlspecialchars($news['title']) ?></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="ltn__page-details-area ltn__blog-details-area mb-120">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="ltn__blog-details-wrap">
                    <div class="ltn__page-details-inner ltn__blog-details-inner">
                        <img src="<?= $news_image ?>" alt="<?= htmlspecialchars($news['title']) ?>">
                        
                        <div class="ltn__blog-meta">
                            <ul>
                                <li class="ltn__blog-category">
                                    <a href="<?= $base_url . $news_list_base . '?category=' . urlencode($news['category']) ?>">
                                        <?= htmlspecialchars($news['category'] ?: ($is_en ? 'News' : 'Berita')) ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <h2 class="ltn__blog-title"><?= htmlspecialchars($news['title']) ?></h2>
                        <div class="news-content mt-4">
                            <?php if (!empty($news['content'])): ?>
                                <?= nl2br(htmlspecialchars($news['content'])) ?>
                            <?php else: ?>
                                <p><?= $is_en ? 'No content available.' : 'Konten belum tersedia.' ?></p>
                            <?php endif; ?>
                        </div>

                        <div class="ltn__blog-meta">
                            <ul>
                                <li class="ltn__blog-author">
                                    <a href="#"><img src="<?= $base_url ?>img/blog/author.jpg" alt="Author"><?= $is_en ? 'By: Admin' : 'Oleh: Admin' ?></a>
                                </li>
                                <li class="ltn__blog-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <?= !empty($news['created_at']) ? date('F d, Y', strtotime($news['created_at'])) : '-' ?>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <?php if (!empty($related_news)): ?>
                        <div class="related-post-area mb-50">
                            <h2 class="title-2"><?= $is_en ? 'Related Post' : 'Artikel Terkait' ?></h2>
                            <div class="row">
                                <?php foreach ($related_news as $item): ?>
                                    <?php
                                    $related_image = !empty($item['image'])
                                        ? $base_url . 'uploads/' . htmlspecialchars($item['image'])
                                        : $base_url . 'img/blog/1.jpg';
                                    $related_url = $base_url . $news_detail_base . '/' . urlencode($item['slug']);
                                    $related_excerpt = !empty($item['content'])
                                        ? mb_strimwidth(trim(strip_tags($item['content'])), 0, 100, '...')
                                        : ($is_en ? 'Read more about this article.' : 'Baca lebih lanjut artikel ini.');
                                    ?>
                                    <div class="col-md-6">
                                        <div class="ltn__blog-item ltn__blog-item-6">
                                            <div class="ltn__blog-img">
                                                <a href="<?= $related_url ?>">
                                                    <img src="<?= $related_image ?>" alt="<?= htmlspecialchars($item['title']) ?>">
                                                </a>
                                            </div>
                                            <div class="ltn__blog-brief">
                                                <div class="ltn__blog-meta">
                                                    <ul>
                                                        <li class="ltn__blog-date ltn__secondary-color">
                                                            <i class="far fa-calendar-alt"></i>
                                                            <?= !empty($item['created_at']) ? date('F d, Y', strtotime($item['created_at'])) : '-' ?>
                                                        </li>
                                                    </ul>
                                                </div>
                                                <h3 class="ltn__blog-title">
                                                    <a href="<?= $related_url ?>">
                                                        <?= htmlspecialchars($item['title']) ?>
                                                    </a>
                                                </h3>
                                                <p><?= htmlspecialchars($related_excerpt) ?></p>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-lg-4">
                <aside class="sidebar-area blog-sidebar ltn__right-sidebar">

                    <div class="widget ltn__author-widget">
                        <h4 class="ltn__widget-title ltn__widget-title-border">
                            <?= $is_en ? 'About Me' : 'Tentang Saya' ?>
                        </h4>
                        <div class="ltn__author-widget-inner text-center" dir="ltr">
                            <img src="<?= $base_url ?>img/blog/admin_medium.webp" alt="Image">
                            <h5>Sularto Abimanyu</h5>
                            <p><?= $is_en
                                ? 'The team behind Nucoco’s website and content. We share insights about the coconut industry, update product information, and ensure everything runs smoothly for our users.'
                                : 'Tim di balik website dan konten Nucoco. Kami membagikan wawasan tentang industri kelapa, memperbarui informasi produk, dan memastikan semuanya berjalan dengan baik bagi para pengunjung.' ?></p>
                            <p><?= $is_en
                                ? 'From articles to industrial solutions, we help connect Nucoco with partners, clients, and the global market.'
                                : 'Dari artikel hingga solusi industri, kami membantu menghubungkan Nucoco dengan mitra, klien, dan pasar global.' ?></p>
                        </div>
                    </div>

                    <div class="widget ltn__menu-widget ltn__menu-widget-2 ltn__menu-widget-2-color-2">
                        <h4 class="ltn__widget-title ltn__widget-title-border">
                            <?= $is_en ? 'Categories' : 'Kategori' ?>
                        </h4>
                        <ul>
                            <?php while ($cat = mysqli_fetch_assoc($category_result)): ?>
                                <li>
                                    <a href="<?= $base_url . $news_list_base . '?category=' . urlencode($cat['category']) ?>">
                                        <?= htmlspecialchars($cat['category']) ?>
                                        <span><?= $cat['total'] ?></span>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <div class="widget ltn__banner-widget">
                        <a href="<?= $base_url . lang_url('en/product/hybrida-young-coconut', 'produk/kelapa-tua-hibrida') ?>">
                            <img src="<?= $base_url ?>uploads/69eb72fc530bf1.94450928.webp" alt="Banner Image">
                        </a>
                    </div>

                </aside>
            </div>
        </div>
    </div>
</div>
<?php
include __DIR__ . '/../section_choose.php';
mysqli_stmt_close($category_stmt);
?>
</main>
<?php
include __DIR__ . '/../includes/footer.php';
?>
