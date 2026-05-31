<?php
$is_en = current_lang() === 'en';
$lang = $is_en ? 'en' : 'id';

$news_detail_base = $is_en ? 'en/news' : 'berita';
$news_list_base   = $is_en ? 'en/news' : 'berita';

$limit = 3;
$page_num = isset($_GET['page']) ? (int) $_GET['page'] : 1;
$page_num = max($page_num, 1);
$start = ($page_num - 1) * $limit;

$selected_category = trim($_GET['category'] ?? '');
$page_slug = 'news';

/*
|--------------------------------------------------------------------------
| COUNT TOTAL DATA
|--------------------------------------------------------------------------
*/
if ($selected_category !== '') {
    $count_sql = "SELECT COUNT(*) as total
                  FROM news
                  WHERE status = 'publish'
                  AND language = ?
                  AND category = ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "ss", $lang, $selected_category);
} else {
    $count_sql = "SELECT COUNT(*) as total
                  FROM news
                  WHERE status = 'publish'
                  AND language = ?";
    $count_stmt = mysqli_prepare($conn, $count_sql);
    mysqli_stmt_bind_param($count_stmt, "s", $lang);
}

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
if ($selected_category !== '') {
    $query = "SELECT *
              FROM news
              WHERE status = 'publish'
              AND language = ?
              AND category = ?
              ORDER BY id DESC
              LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssii", $lang, $selected_category, $start, $limit);
} else {
    $query = "SELECT *
              FROM news
              WHERE status = 'publish'
              AND language = ?
              ORDER BY id DESC
              LIMIT ?, ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "sii", $lang, $start, $limit);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die('List query error: ' . mysqli_error($conn));
}

/*
|--------------------------------------------------------------------------
| CATEGORY DATA
|--------------------------------------------------------------------------
*/
$category_sql = "SELECT category, COUNT(*) as total
                 FROM news
                 WHERE status = 'publish'
                 AND language = ?
                 AND category IS NOT NULL
                 AND category != ''
                 GROUP BY category
                 ORDER BY category ASC";
$category_stmt = mysqli_prepare($conn, $category_sql);
mysqli_stmt_bind_param($category_stmt, "s", $lang);
mysqli_stmt_execute($category_stmt);
$category_query = mysqli_stmt_get_result($category_stmt);
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

<!-- BLOG AREA START -->
<div class="ltn__blog-area mb-120">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <div class="ltn__blog-list-wrap">

                    <?php if ($total_data > 0): ?>
                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <?php
                            $slug = $row['slug'] ?? '';
                            $image = $row['image'] ?? '';
                            $title = $row['title'] ?? '';
                            $category = $row['category'] ?? '';
                            $excerpt = $row['excerpt'] ?? '';
                            $created_at = $row['created_at'] ?? '';
                            $detail_url = $base_url . $news_detail_base . '/' . urlencode($slug);
                            ?>
                            
                            <div class="ltn__blog-item ltn__blog-item-5">
                                <div class="ltn__blog-img">
                                    <a href="<?= $detail_url ?>">
                                        <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>">
                                    </a>
                                </div>

                                <div class="ltn__blog-brief">
                                    <div class="ltn__blog-meta" dir="ltr">
                                        <ul>
                                            <li class="ltn__blog-category">
                                                <a href="<?= $base_url . $news_list_base . '?category=' . urlencode($category) ?>">
                                                    <?= htmlspecialchars($category) ?>
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                    <h3 class="ltn__blog-title" dir="ltr">
                                        <a href="<?= $detail_url ?>">
                                            <?= htmlspecialchars($title) ?>
                                        </a>
                                    </h3>

                                    <div class="ltn__blog-meta" dir="ltr">
                                        <ul>
                                            <li class="ltn__blog-date">
                                                <i class="far fa-calendar-alt"></i>
                                                <?= !empty($created_at) ? date('F d, Y', strtotime($created_at)) : '-' ?>
                                            </li>
                                        </ul>
                                    </div>

                                    <p><?= htmlspecialchars($excerpt) ?></p>

                                    <div class="ltn__blog-meta-btn">
                                        <div class="ltn__blog-meta">
                                            <ul>
                                                <li class="ltn__blog-author">
                                                    <a href="#"><img src="<?= $base_url ?>img/blog/admin_medium.webp" alt="#"><?= $is_en ? 'By: Admin' : 'Oleh: Admin' ?></a>
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="ltn__blog-btn">
                                            <a href="<?= $detail_url ?>">
                                                <i class="fas fa-arrow-right"></i><?= $is_en ? 'Read more' : 'Baca selengkapnya' ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="text-center">
                            <p><?= $is_en ? 'No news articles available at the moment.' : 'Belum ada artikel berita saat ini.' ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <?php if ($total_pages > 1): ?>
                            <div class="ltn__pagination-area text-center">
                                <div class="ltn__pagination" dir="ltr">
                                    <ul>
                                        <li class="<?= ($page_num <= 1) ? 'disabled' : '' ?>">
                                            <a href="<?= ($page_num > 1) ? ($base_url . $news_list_base . '?page=' . ($page_num - 1) . ($selected_category !== '' ? '&category=' . urlencode($selected_category) : '')) : '#' ?>">
                                                <i class="fas fa-angle-double-left"></i>
                                            </a>
                                        </li>

                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="<?= ($page_num == $i) ? 'active' : '' ?>">
                                                <a href="<?= $base_url . $news_list_base . '?page=' . $i . ($selected_category !== '' ? '&category=' . urlencode($selected_category) : '') ?>">
                                                    <?= $i ?>
                                                </a>
                                            </li>
                                        <?php endfor; ?>

                                        <li class="<?= ($page_num >= $total_pages) ? 'disabled' : '' ?>">
                                            <a href="<?= ($page_num < $total_pages) ? ($base_url . $news_list_base . '?page=' . ($page_num + 1) . ($selected_category !== '' ? '&category=' . urlencode($selected_category) : '')) : '#' ?>">
                                                <i class="fas fa-angle-double-right"></i>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <aside class="sidebar-area blog-sidebar ltn__right-sidebar">

                    <!-- Author Widget -->
                    <div class="widget ltn__author-widget">
                        <h4 class="ltn__widget-title ltn__widget-title-border">
                            <?= $is_en ? 'About Me' : 'Tentang Saya' ?>
                        </h4>

                        <div class="ltn__author-widget-inner text-center" dir="ltr">
                            <img src="<?= $base_url ?>img/blog/admin_medium.webp" alt="Image">
                            <h5>Sularto Abimanyu</h5>

                            <p>
                                <?= $is_en
                                    ? 'The team behind Nucoco’s website and content. We share insights about the coconut industry, update product information, and ensure everything runs smoothly for our users.'
                                    : 'Tim di balik website dan konten Nucoco. Kami membagikan wawasan tentang industri kelapa, memperbarui informasi produk, dan memastikan semuanya berjalan dengan baik bagi para pengunjung.' ?>
                            </p>

                            <p>
                                <?= $is_en
                                    ? 'From articles to industrial solutions, we help connect Nucoco with partners, clients, and the global market.'
                                    : 'Dari artikel hingga solusi industri, kami membantu menghubungkan Nucoco dengan mitra, klien, dan pasar global.' ?>
                            </p>

                            <div class="ltn__social-media">
                                <ul>
                                    <li><a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                    <li><a href="#" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                                    <li><a href="#" title="Linkedin"><i class="fab fa-linkedin"></i></a></li>
                                    <li><a href="#" title="Behance"><i class="fab fa-behance"></i></a></li>
                                    <li><a href="#" title="Youtube"><i class="fab fa-youtube"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- Category Widget -->
                    <div class="widget ltn__menu-widget ltn__menu-widget-2 ltn__menu-widget-2-color-2">
                        <h4 class="ltn__widget-title ltn__widget-title-border">
                            <?= $is_en ? 'Categories' : 'Kategori' ?>
                        </h4>
                        <ul>
                            <?php while ($cat = mysqli_fetch_assoc($category_query)): ?>
                                <li>
                                    <a href="<?= $base_url . $news_list_base . '?category=' . urlencode($cat['category']) ?>">
                                        <?= htmlspecialchars($cat['category']) ?>
                                        <span><?= $cat['total'] ?></span>
                                    </a>
                                </li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <!-- Banner -->
                    <div class="widget ltn__banner-widget">
                        <a href="<?= $base_url . lang_url('en/product', 'produk') ?>">
                            <img src="<?= $base_url ?>uploads/69eb72fc530bf1.94450928.webp" alt="Banner Image">
                        </a>
                    </div>

                </aside>
            </div>
        </div>
    </div>
</div>
<!-- BLOG AREA END -->

    <?php
    include __DIR__ . '/../section_choose.php';
    ?>

<?php
mysqli_stmt_close($stmt);
mysqli_stmt_close($category_stmt);
?>