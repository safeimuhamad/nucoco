<?php
$currentPath = trim(service('uri')->getPath(), '/');
$currentPath = str_replace('index.php/', '', $currentPath);
$currentPath = str_replace('index.php', '', $currentPath);

$alternatePath = match ($currentPath) {
    'id' => 'en',
    'en' => 'id',
    'id/tentang-kami' => 'en/about-us',
    'en/about-us' => 'id/tentang-kami',
    'id/produk' => 'en/product',
    'en/product' => 'id/produk',
    'id/artikel' => 'en/article',
    'en/article' => 'id/artikel',
    'id/kontak' => 'en/contact',
    'en/contact' => 'id/kontak',
    default => 'id',
};
?>
<head>
    <link rel="alternate" hreflang="id" href="<?= site_url('id') ?>">
    <link rel="alternate" hreflang="en" href="<?= site_url('en') ?>">
    <link rel="alternate" hreflang="x-default" href="<?= site_url('id') ?>">
</head>

<header>
    <a href="<?= site_url($alternatePath) ?>">
        <?= $locale === 'id' ? 'English' : 'Indonesia' ?>
    </a>

    <nav style="margin-top: 20px;">
        <a href="<?= site_url($locale) ?>"><?= lang('App.home') ?></a> |
        <a href="<?= site_url($locale === 'id' ? 'id/tentang-kami' : 'en/about-us') ?>">
            <?= lang('App.about') ?>
        </a> |
        <a href="<?= site_url($locale === 'id' ? 'id/produk' : 'en/product') ?>">
            <?= lang('App.product') ?>
        </a> |
        <a href="<?= site_url($locale === 'id' ? 'id/artikel' : 'en/article') ?>">
            <?= lang('App.article') ?>
        </a> |
        <a href="<?= site_url($locale === 'id' ? 'id/kontak' : 'en/contact') ?>">
            <?= lang('App.contact') ?>
        </a>
    </nav>

    <hr>
</header>