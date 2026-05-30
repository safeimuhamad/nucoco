<?= $this->include('layout/header') ?>

<h1><?= $locale === 'id' ? 'Produk' : 'Product'; ?></h1>
<p>
    <?= $locale === 'id'
        ? 'Ini adalah halaman Produk versi Indonesia.'
        : 'This is the Product page in English.'; ?>
</p>

<?= $this->include('layout/footer') ?>