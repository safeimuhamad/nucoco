<?php
if (!isset($conn)) {
    require_once __DIR__ . '/admin/includes/db.php';
}

if (!isset($base_url)) {
    $base_url = '/';
}

$lang = current_lang() === 'en' ? 'English' : 'Indonesia';

   $choose_us_query = mysqli_prepare(
    $conn,
    "SELECT title, description, icon
    FROM why_choose_us
    WHERE is_active = 1 AND language = ?
    ORDER BY sort_order ASC, id DESC
    LIMIT 4"
);

   $choose_us_items = [];

   if ($choose_us_query) {
    mysqli_stmt_bind_param($choose_us_query, "s", $lang);
    mysqli_stmt_execute($choose_us_query);
    $choose_us_result = mysqli_stmt_get_result($choose_us_query);

    while ($row = mysqli_fetch_assoc($choose_us_result)) {
        $choose_us_items[] = $row;
    }

    mysqli_stmt_close($choose_us_query);
}
?>

<!-- FEATURE AREA START ( Feature - 3) -->
<div class="ltn__feature-area before-bg-bottom-2-- mb--30--- plr--5 mb-120">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__feature-item-box-wrap ltn__border-between-column white-bg">
                    <div class="row">
                        <?php if (!empty($choose_us_items)): ?>
                            <?php foreach ($choose_us_items as $item): ?>
                               <div class="col-xl-3 col-md-6 col-12">
                                    <div class="ltn__feature-item ltn__feature-item-8">

                                        <div class="ltn__feature-info">

                                            <!-- HEADER (TITLE + ICON) -->
                                            <div style="display:flex; align-items:center; justify-content:center; gap:10px; margin-bottom:10px;">
                                                
                                                <h2 style="margin:0;">
                                                    <?= htmlspecialchars($item['title'] ?? '') ?>
                                                </h2>

                                                <?php if (!empty($item['icon'])): ?>
                                                    <img 
                                                        src="<?= $base_url ?>uploads/<?= htmlspecialchars($item['icon'] ?? '') ?>" 
                                                        alt="<?= htmlspecialchars($item['title'] ?? '') ?>"
                                                        style="width:26px; height:26px; object-fit:contain;"
                                                    >
                                                <?php endif; ?>

                                            </div>

                                            <!-- DESCRIPTION -->
                                            <p style="text-align:center;">
                                                <?= htmlspecialchars($item['description'] ?? '') ?>
                                            </p>

                                        </div>

                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div class="col-12 text-center">
                                <p>
                                    <?= current_lang() === 'en' ? 'No features available.' : 'Belum ada data fitur.' ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FEATURE AREA END -->