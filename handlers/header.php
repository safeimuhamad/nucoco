<!doctype html>
<html class="no-js" lang="zxx">
<?php
$base_url = 'https://nucoco.id/';

function url($path = '')
{
    global $base_url;
    return rtrim($base_url, '/') . ($path ? '/' . ltrim($path, '/') : '/');
}

$path = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$path = preg_replace('#^nucoco\.id/?#', '', $path);
$current_page = $path === '' ? '/' : $path;
?>


<head>
    <meta charset="utf-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <title><?= htmlspecialchars($page['meta_title'] ?: $page['hero_title']) ?></title>
    <meta name="description" content="<?= htmlspecialchars($page['meta_description'] ?? '') ?>">
    <meta name="keywords" content="<?= htmlspecialchars($page['meta_keywords'] ?? '') ?>">
    <meta name="robots" content="<?= htmlspecialchars($page['meta_robots'] ?: 'index, follow') ?>">

    <?php if (!empty($page['canonical_url'])): ?>
    <link rel="canonical" href="<?= htmlspecialchars($page['canonical_url']) ?>">
    <?php endif; ?>

    <meta property="og:title" content="<?= htmlspecialchars($page['og_title'] ?: $page['meta_title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($page['og_description'] ?: $page['meta_description']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($page['og_image'] ?? '') ?>">

    <?php if (!empty($page['schema_markup'])): ?>
    <script type="application/ld+json">
    <?= $page['schema_markup'] ?>
    </script>
    <?php endif; ?>
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <!-- Place favicon.png in the root directory -->
    <link rel="shortcut icon" href="<?= $base_url ?>img/favicon.png" type="image/x-icon" />
    <!-- Font Icons css -->
    <link rel="stylesheet" href="<?= $base_url ?>css/font-icons.css">
    <!-- plugins css -->
    <link rel="stylesheet" href="<?= $base_url ?>css/plugins.css">
    <!-- Main Stylesheet -->
    <link rel="stylesheet" href="<?= $base_url ?>css/style.css">
    <!-- Responsive css -->
    <link rel="stylesheet" href="<?= $base_url ?>css/responsive.css">
</head>
