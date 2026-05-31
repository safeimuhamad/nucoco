<section style="padding:60px 20px;">
    <div class="container">
        <h1><?= htmlspecialchars($page['hero_title'] ?? $page['page_name'] ?? 'Page') ?></h1>

        <?php if (!empty($page['hero_description'])): ?>
            <p><?= nl2br(htmlspecialchars($page['hero_description'])) ?></p>
        <?php endif; ?>

        <?php if (!empty($page['content'])): ?>
            <div>
                <?= $page['content'] ?>
            </div>
        <?php endif; ?>
    </div>
</section>