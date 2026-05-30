<?= $this->include('layout/header') ?>

<h1><?= $locale === 'id' ? 'Kontak' : 'Contact'; ?></h1>
<p>
    <?= $locale === 'id'
        ? 'Ini adalah halaman Kontak versi Indonesia.'
        : 'This is the Contact page in English.'; ?>
</p>

<?= $this->include('layout/footer') ?>