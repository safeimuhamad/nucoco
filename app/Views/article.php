<?= $this->include('layout/header') ?>

<h1><?= $locale === 'id' ? 'Artikel' : 'Article'; ?></h1>
<p>
    <?= $locale === 'id'
        ? 'Ini adalah halaman Artikel versi Indonesia.'
        : 'This is the Article page in English.'; ?>
</p>

<?= $this->include('layout/footer') ?>