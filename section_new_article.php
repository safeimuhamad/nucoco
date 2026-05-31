<?php
if (!isset($conn)) {
    require_once __DIR__ . '/admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

$is_en = current_lang() === 'en';
$lang = $is_en ? 'en' : 'id';

$news_detail_base = $is_en ? 'en/news' : 'berita';
$news_list_base   = $is_en ? 'en/news' : 'berita';

$section_title = $section_title ?? ($is_en ? 'Latest Blog' : 'Artikel Terbaru');
$limit = $limit ?? 6;

$sql = "SELECT id, title, slug, category, image, created_at
        FROM news
        WHERE status = 'publish' AND language = ?
        ORDER BY created_at DESC
        LIMIT ?";

$stmt = mysqli_prepare($conn, $sql);

$has_blog = false;
$result = false;

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $lang, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $has_blog = ($result && mysqli_num_rows($result) > 0);
}
?>

<div class="ltn__blog-area pt-115 pb-70">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h2 class="section-title white-color---">
                        <?= $is_en ? 'Latest Blog' : 'Artikel Terbaru' ?>
                    </h2>
                </div>
            </div>
        </div>

        <div class="row ltn__blog-slider-one-active slick-arrow-1 ltn__blog-item-3-normal">
            <?php if ($has_blog): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $title = $row['title'] ?? '';
                    $slug = $row['slug'] ?? '';
                    $category = $row['category'] ?? '';
                    $image = $row['image'] ?? '';
                    $created_at = $row['created_at'] ?? '';

                    $detail_url = $base_url . $news_detail_base . '/' . urlencode($slug);
                    $category_url = $base_url . $news_list_base . '?category=' . urlencode($category);
                    ?>
                    <div class="col-lg-12">
                        <div class="ltn__blog-item ltn__blog-item-3" style="height: 100%; display: flex; flex-direction: column;">

                            <div class="ltn__blog-img">
                                <a href="<?= $detail_url ?>">
                                    <img src="<?= $base_url ?>uploads/<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($title) ?>" fetchpriority="low" loading="lazy" decoding="async">
                                </a>
                            </div>

                            <div class="ltn__blog-brief" style="flex: 1; display: flex; flex-direction: column;">

                                <div class="ltn__blog-meta">
                                    <ul>
                                        <li class="ltn__blog-author">
                                            <a href="#"><i class="far fa-user"></i><?= $is_en ? 'by: Admin' : 'oleh: Admin' ?></a>
                                        </li>
                                        <li class="ltn__blog-tags">
                                            <a href="<?= $category_url ?>">
                                                <i class="fas fa-tags"></i><?= htmlspecialchars($category !== '' ? $category : ($is_en ? 'News' : 'Artikel')) ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <h2 class="ltn__blog-title" style="min-height: 120px; display: flex; align-items: flex-start; justify-content: center; text-align: right;">
                                    <a href="<?= $detail_url ?>" dir="ltr">
                                        <?= htmlspecialchars($title) ?>
                                    </a>
                                </h2>

                                <div style="margin-top: auto;">
                                    <div class="ltn__blog-meta-btn">
                                        <div class="ltn__blog-meta">
                                            <ul>
                                                <li class="ltn__blog-date">
                                                    <i class="far fa-calendar-alt"></i>
                                                    <?= !empty($created_at) ? date('F d, Y', strtotime($created_at)) : '-' ?>
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="ltn__blog-btn">
                                            <a href="<?= $detail_url ?>">
                                                <?= $is_en ? 'Read News' : 'Baca selengkapnya' ?>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-lg-12">
                    <div class="text-center">
                        <p><?= $is_en ? 'No blog posts available.' : 'Belum ada artikel tersedia.' ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($has_blog): ?>
            <div class="row">
                <div class="col-12 text-center mt-4">
                    <a href="<?= $base_url . $news_list_base ?>" class="theme-btn-1 btn btn-effect-1 text-uppercase">
                        <?= $is_en ? 'Explore More' : 'Lihat Selengkapnya' ?>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($stmt) mysqli_stmt_close($stmt); ?>