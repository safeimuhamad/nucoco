<?= $this->include('layout/header') ?>

<h1><?= $locale === 'id' ? 'Tentang Kami' : 'About Us'; ?></h1>
<p>
    <?= $locale === 'id'
        ? 'Ini adalah halaman Tentang Kami versi Indonesia.'
        : 'This is the About Us page in English.'; ?>
</p>

<?= $this->include('layout/footer') ?>