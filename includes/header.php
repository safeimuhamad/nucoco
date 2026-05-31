<!doctype html>
<html class="no-js" lang="<?= !empty($lang) ? htmlspecialchars($lang) : 'id' ?>">
<?php
$base_url = function_exists('site_base_url') ? site_base_url() : 'https://nucoco.id/';

$seo_request_path = function_exists('site_request_path')
    ? site_request_path()
    : trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');

$seo_canonical = !empty($page['canonical_url'])
    ? $page['canonical_url']
    : ($seo_request_path === '' ? $base_url : $base_url . $seo_request_path);

$seo_alt_id = !empty($page['alternate_id_url'])
    ? $page['alternate_id_url']
    : $base_url;

$seo_alt_en = !empty($page['alternate_en_url'])
    ? $page['alternate_en_url']
    : $base_url . 'en';

$seo_x_default = $seo_alt_id;
?>
<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= htmlspecialchars($page['meta_title'] ?: $page['hero_title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page['meta_description'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page['meta_keywords'] ?? '') ?>">
    <meta name="robots" content="<?= htmlspecialchars($page['meta_robots'] ?: 'index, follow') ?>">
    <link rel="canonical" href="<?= htmlspecialchars($seo_canonical) ?>">
    <link rel="alternate" hreflang="id" href="<?= htmlspecialchars($seo_alt_id) ?>">
    <link rel="alternate" hreflang="en" href="<?= htmlspecialchars($seo_alt_en) ?>">
    <link rel="alternate" hreflang="x-default" href="<?= htmlspecialchars($seo_x_default) ?>">
    <meta property="og:title" content="<?= htmlspecialchars($page['og_title'] ?: $page['meta_title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page['og_description'] ?: $page['meta_description']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($page['og_image'] ?? '') ?>">
    <meta property="og:url" content="<?= htmlspecialchars($seo_canonical) ?>">
    <meta property="og:type" content="website">
    <?php if (!empty($page['schema_markup'])): ?>
    <script type="application/ld+json">
    <?= $page['schema_markup'] ?>
    </script>
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="shortcut icon" href="<?= $base_url ?>img/favicon.png" type="image/x-icon" />
    <link rel="preload" href="<?= $base_url ?>css/font-icons.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?= $base_url ?>css/font-icons.css"></noscript>
    <link rel="preload" href="<?= $base_url ?>css/plugins.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?= $base_url ?>css/plugins.css"></noscript>
    <link rel="stylesheet" href="<?= $base_url ?>css/style.css">
    <link rel="preload" href="<?= $base_url ?>css/responsive.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="<?= $base_url ?>css/responsive.css"></noscript>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Rajdhani:wght@400;500;600;700&display=swap" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&family=Rajdhani:wght@400;500;600;700&display=swap"></noscript>
    <link rel="preload" href="<?= $base_url ?>webfonts/fa-regular-400.woff2" as="font" type="font/woff2" crossorigin>
    <link rel="preload" as="image" href="<?= $base_url ?>img/slider/kelapa-muda-slider.webp" fetchpriority="high">
    <script src="https://analytics.ahrefs.com/analytics.js" data-key="UShODIzN+ETmTnFcbW8E2Q" async></script>
</head>
