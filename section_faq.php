<?php
if (!isset($conn)) {
    require_once __DIR__ . '/admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

$lang = current_lang() === 'en' ? 'English' : 'Indonesia';

$faqs = [];

$stmt = mysqli_prepare($conn, "
    SELECT id, question, answer
    FROM faqs
    WHERE status = 'active' AND language = ?
    ORDER BY display_order ASC, id ASC
    LIMIT 10
");

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "s", $lang);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $faqs[] = $row;
    }

    mysqli_stmt_close($stmt);
}
?>

<!-- FAQ AREA START (faq-2) (ID > accordion_2) -->
<div class="ltn__faq-area pt-115 pb-120">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title white-color---">
                        <?= current_lang() === 'en' ? 'Frequently Asked Questions' : 'Pertanyaan yang Sering Diajukan' ?>
                    </h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6">
                <div class="ltn__faq-inner ltn__faq-inner-2">
                    <div id="accordion_2">

                        <?php if (!empty($faqs)): ?>
                            <?php foreach ($faqs as $index => $row): ?>
                                <?php
                                $faq_id = (int) ($row['id'] ?? 0);
                                $question = $row['question'] ?? '';
                                $answer = $row['answer'] ?? '';
                                $is_open = ($index === 0);
                                ?>
                                <div class="card">
                                    <h6 class="<?= $is_open ? 'ltn__card-title' : 'collapsed ltn__card-title' ?>"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#faq-item-<?= $faq_id ?>"
                                        aria-expanded="<?= $is_open ? 'true' : 'false' ?>"
                                        dir="<?= current_lang() === 'en' ? 'ltr' : 'ltr' ?>">
                                        <?= htmlspecialchars($question) ?>
                                    </h6>

                                    <div id="faq-item-<?= $faq_id ?>"
                                         class="collapse <?= $is_open ? 'show' : '' ?>"
                                         data-parent="#accordion_2">
                                        <div class="card-body">
                                            <p dir="<?= current_lang() === 'en' ? 'ltr' : 'ltr' ?>">
                                                <?= nl2br(htmlspecialchars($answer)) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="card">
                                <h6 class="ltn__card-title"
                                    data-bs-toggle="collapse"
                                    data-bs-target="#faq-empty"
                                    aria-expanded="true">
                                    <?= current_lang() === 'en' ? 'No questions available right now' : 'Belum ada pertanyaan saat ini' ?>
                                </h6>
                                <div id="faq-empty" class="collapse show" data-parent="#accordion_2">
                                    <div class="card-body">
                                        <p>
                                            <?= current_lang() === 'en' ? 'Please check back again later.' : 'Silakan cek kembali nanti.' ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <aside class="sidebar-area ltn__right-sidebar mt-60">
                    <div class="widget ltn__banner-widget">
                            <a href="<?= $base_url . lang_url('en/contact', 'kontak') ?>">
                            <img src="<?= $base_url ?>img/bg/faq-image.webp" alt="FAQ Banner Image">
                        </a>
                    </div>
                </aside>
            </div>
        </div>
    </div>
</div>
<!-- FAQ AREA END -->