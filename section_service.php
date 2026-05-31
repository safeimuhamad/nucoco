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

$service_detail_base = $is_en ? 'en/services' : 'layanan';
$service_list_base   = $is_en ? 'en/services' : 'layanan';

$section_title = $is_en ? 'Our Services' : 'Layanan Kami';
$limit = 3;

$sql = "SELECT id, title, slug, image, content
        FROM services
        WHERE status = 'publish' AND language = ?
        ORDER BY created_at DESC
        LIMIT ?";

$stmt = mysqli_prepare($conn, $sql);

$has_services = false;
$result = false;

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "si", $lang, $limit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $has_services = ($result && mysqli_num_rows($result) > 0);
}
?>

<!-- SERVICE AREA START -->
<div class="ltn__service-area section-bg-1 pt-115 pb-70">
    <div class="container">

        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title white-color---">
                        <?= htmlspecialchars($section_title) ?>
                    </h1>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <?php if ($has_services): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <?php
                    $title = mb_strimwidth(trim($row['title'] ?? ''), 0, 45, '...');
                    $desc  = mb_strimwidth(trim(strip_tags($row['content'] ?? '')), 0, 120, '...');
                    $slug  = $row['slug'] ?? '';

                    $image = !empty($row['image'])
                        ? $base_url . 'uploads/services/' . htmlspecialchars($row['image'])
                        : $base_url . 'img/service/1.jpg';

                    $detail_url = $base_url . $service_detail_base . '/' . urlencode($slug);
                    ?>
                    <div class="col-lg-4 col-sm-6 mb-4 d-flex">
                        <div class="ltn__service-item-1 w-100" style="display:flex; flex-direction:column; height:100%;">

                            <div class="service-item-img" style="background:#fff; line-height:0;">
                                <a href="<?= $detail_url ?>" style="display:block;">
                                    <img 
                                        src="<?= $image ?>" 
                                        alt="<?= htmlspecialchars($row['title'] ?? '') ?>" width="370" height="240" style="width:100%;" loading="lazy" decoding="async" fetchpriority="low">
                                </a>
                            </div>

                            <div class="service-item-brief text-center" style="padding:30px 25px; min-height:170px;">
                                <h2 style="min-height:65px; margin-bottom:15px;">
                                    <a href="<?= $detail_url ?>">
                                        <?= htmlspecialchars($title) ?>
                                    </a>
                                </h2>

                                <p style="margin-bottom:0;" dir="ltr">
                                    <?= htmlspecialchars($desc) ?>
                                </p>
                            </div>

                        </div>
                    </div>
                <?php endwhile; ?>

                <div class="col-12 text-center mt-4">
                    <a 
                        href="<?= $base_url . $service_list_base ?>" 
                        class="theme-btn-1 btn btn-effect-1 text-uppercase"
                    >
                        <?= $is_en ? 'Explore Services' : 'Lihat Layanan' ?>
                    </a>
                </div>

            <?php else: ?>
                <div class="col-12 text-center">
                    <p><?= $is_en ? 'No services available right now.' : 'Belum ada layanan tersedia saat ini.' ?></p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>
<!-- SERVICE AREA END -->

<?php if ($stmt) mysqli_stmt_close($stmt); ?>