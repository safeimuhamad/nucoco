<?php
if (!isset($conn)) {
    require_once __DIR__ . '/admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

$lang = current_lang() === 'en' ? 'English' : 'Indonesia';

$testimonials = [];

$stmt = mysqli_prepare($conn, "
    SELECT id, name, position, content, photo
    FROM testimonials
    WHERE status = 'active' AND language = ?
    ORDER BY display_order ASC, id DESC
    LIMIT 10
");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $lang);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $testimonials[] = $row;
    }

    mysqli_stmt_close($stmt);
}
?>

<!-- TESTIMONIAL AREA START -->
<div class="ltn__testimonial-area section-bg-1 pt-115 pb-70">
    <div class="container-fluid">

        <!-- TITLE -->
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h6 class="section-subtitle ltn__secondary-color">
                        // <?= current_lang() === 'en' ? 'Testimonials' : 'Testimoni' ?>
                    </h6>
                    <h1 class="section-title">
                        <?= current_lang() === 'en' ? 'Clients Feedbacks' : 'Ulasan Klien' ?><span>.</span>
                    </h1>
                </div>
            </div>
        </div>

        <!-- SLIDER -->
        <div class="row ltn__testimonial-slider-3-active slick-arrow-1 slick-arrow-1-inner">

            <?php if (!empty($testimonials)): ?>
                <?php foreach ($testimonials as $row): ?>

                    <?php
                    $name = htmlspecialchars($row['name'] ?? '');
                    $position = htmlspecialchars($row['position'] ?? '');
                    $content = htmlspecialchars(mb_strimwidth(strip_tags($row['content'] ?? ''), 0, 180, '...'));
                    $photo = !empty($row['photo'])
                        ? $base_url . 'uploads/testimonials/' . htmlspecialchars($row['photo'])
                        : $base_url . 'img/testimonial/1.jpg';
                    ?>

                    <div class="col-lg-12">
                        <div class="ltn__testimonial-item ltn__testimonial-item-4">

                            <!-- PHOTO -->
                            <div class="ltn__testimoni-img">
                                <img src="<?= $photo ?>" alt="<?= $name ?>">
                            </div>

                            <!-- CONTENT -->
                            <div class="ltn__testimoni-info">
                                <p dir="ltr"><?= $content ?></p>
                                <h4 dir="ltr"><?= $name ?></h4>
                                <h6 dir="ltr"><?= $position ?></h6>
                            </div>

                            <!-- ICON -->
                            <div class="ltn__testimoni-bg-icon">
                                <i class="far fa-comments"></i>
                            </div>

                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>

                <!-- FALLBACK -->
                <div class="col-lg-12 text-center">
                    <p>
                        <?= current_lang() === 'en' ? 'No testimonials available right now.' : 'Belum ada testimoni saat ini.' ?>
                    </p>
                </div>

            <?php endif; ?>

        </div>

    </div>
</div>
<!-- TESTIMONIAL AREA END -->