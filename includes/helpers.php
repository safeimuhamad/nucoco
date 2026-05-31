<?php

require_once __DIR__ . '/bootstrap.php';


function get_menu_pages($conn)
{
    $data = [];
    $lang = current_lang();

    if ($lang === 'en') {
        $allowed_slugs = ['home', 'about', 'product', 'services', 'news', 'contact'];
    } else {
        $allowed_slugs = ['beranda', 'tentang-kami', 'produk', 'layanan', 'berita', 'kontak'];
    }

    $placeholders = implode(',', array_fill(0, count($allowed_slugs), '?'));
    $types = str_repeat('s', count($allowed_slugs));

    $sql = "SELECT id, page_name, slug
            FROM pages
            WHERE status = 'publish'
            AND slug IN ($placeholders)";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die('Menu query prepare error: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, $types, ...$allowed_slugs);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[$row['slug']] = $row;
    }

    mysqli_stmt_close($stmt);

    foreach ($allowed_slugs as $slug) {
        if (isset($rows[$slug])) {
            $data[] = $rows[$slug];
        }
    }

    return $data;
}



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_lang()
{
    $segments = array_values(array_filter(explode('/', site_request_path())));

    // Prioritas 1: language dari path URL
    if (!empty($segments) && $segments[0] === 'en') {
        $_SESSION['site_lang'] = 'en';
        return 'en';
    }

    // Prioritas 2: query string lama (fallback)
    if (isset($_GET['lang']) && in_array($_GET['lang'], ['id', 'en'], true)) {
        $_SESSION['site_lang'] = $_GET['lang'];
        return $_GET['lang'];
    }

    // Prioritas 3: default
    $_SESSION['site_lang'] = 'id';
    return 'id';
}


function lang_label($lang = null)
{
    $lang = $lang ?: current_lang();

    return $lang === 'en' ? 'English' : 'Indonesia';
}

function get_page_content($conn, $slug, $lang = 'id')
{
    $stmt = mysqli_prepare($conn, "
        SELECT 
            p.id AS page_id,
            p.page_name,
            p.slug,
            p.status AS page_status,
            pc.*
        FROM pages p
        LEFT JOIN page_contents pc 
            ON pc.page_id = p.id 
            AND pc.language_code = ?
        WHERE p.slug = ?
        AND p.status = 'publish'
        LIMIT 1
    ");

    if (!$stmt) {
        die('Prepare failed: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "ss", $lang, $slug);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);

    if ((!$data || empty($data['language_code'])) && $lang !== 'id') {
        return get_page_content($conn, $slug, 'id');
    }

    return $data;
}

function url($slug = '')
{
    $lang = current_lang();
    $base_url = site_base_url();

    $slug = trim($slug, '/');

    if ($slug === '' || $slug === 'home' || $slug === 'beranda') {
        return $lang === 'en'
            ? $base_url . 'en'
            : $base_url;
    }

    return $lang === 'en'
        ? $base_url . 'en/' . $slug
        : $base_url . $slug;
}

function switch_lang_seo_url($conn, $target_lang)
{
    $segments = array_values(array_filter(explode('/', site_request_path())));
    $base_url = site_base_url();

    $current_lang = current_lang();

    if (!in_array($target_lang, ['id', 'en'], true)) {
        $target_lang = 'id';
    }

    if (!empty($segments) && $segments[0] === 'en') {
        array_shift($segments);
    }

    $current_slug = $segments[0] ?? '';

    if ($current_slug === '') {
        return $target_lang === 'en'
            ? $base_url . 'en'
            : $base_url;
    }

    $map = [
        'beranda' => ['id' => 'beranda', 'en' => 'home'],
        'home' => ['id' => 'beranda', 'en' => 'home'],
        'tentang-kami' => ['id' => 'tentang-kami', 'en' => 'about'],
        'about' => ['id' => 'tentang-kami', 'en' => 'about'],
        'produk' => ['id' => 'produk', 'en' => 'product'],
        'product' => ['id' => 'produk', 'en' => 'product'],
        'layanan' => ['id' => 'layanan', 'en' => 'services'],
        'services' => ['id' => 'layanan', 'en' => 'services'],
        'berita' => ['id' => 'berita', 'en' => 'news'],
        'news' => ['id' => 'berita', 'en' => 'news'],
        'kontak' => ['id' => 'kontak', 'en' => 'contact'],
        'contact' => ['id' => 'kontak', 'en' => 'contact'],
    ];

    if (!isset($map[$current_slug])) {
        return $target_lang === 'en'
            ? $base_url . 'en'
            : $base_url;
    }

    $target_slug = $map[$current_slug][$target_lang];

    if ($target_lang === 'en') {
        return $target_slug === 'home'
            ? $base_url . 'en'
            : $base_url . 'en/' . $target_slug;
    }

    return $target_slug === 'beranda'
        ? $base_url
        : $base_url . $target_slug;
}

function contact_slug()
{
    return current_lang() === 'en' ? 'contact' : 'kontak';
}

function page_label($slug)
{
    $lang = current_lang();

    $map = [
        'home' => ['id' => 'Beranda', 'en' => 'Home'],
        'beranda' => ['id' => 'Beranda', 'en' => 'Home'],

        'about' => ['id' => 'Tentang Kami', 'en' => 'About'],
        'tentang-kami' => ['id' => 'Tentang Kami', 'en' => 'About'],

        'product' => ['id' => 'Produk', 'en' => 'Product'],
        'produk' => ['id' => 'Produk', 'en' => 'Product'],

        'services' => ['id' => 'Layanan', 'en' => 'Services'],
        'layanan' => ['id' => 'Layanan', 'en' => 'Services'],

        'news' => ['id' => 'Berita', 'en' => 'News'],
        'berita' => ['id' => 'Berita', 'en' => 'News'],

        'contact' => ['id' => 'Kontak', 'en' => 'Contact'],
        'kontak' => ['id' => 'Kontak', 'en' => 'Contact'],
    ];

    return $map[$slug][$lang] ?? ucfirst($slug);
}

function is_active_menu($menu_slug)
{
    $segments = array_values(array_filter(explode('/', site_request_path())));

    $lang = current_lang();

    // slug aktif dari URL
    if ($lang === 'en') {
        $current_slug = $segments[1] ?? 'home';
    } else {
        $current_slug = $segments[0] ?? 'beranda';
    }

    // mapping pasangan slug ID <-> EN
    $slug_map = [
        'beranda' => ['id' => 'beranda', 'en' => 'home'],
        'home' => ['id' => 'beranda', 'en' => 'home'],

        'tentang-kami' => ['id' => 'tentang-kami', 'en' => 'about'],
        'about' => ['id' => 'tentang-kami', 'en' => 'about'],

        'produk' => ['id' => 'produk', 'en' => 'product'],
        'product' => ['id' => 'produk', 'en' => 'product'],

        'layanan' => ['id' => 'layanan', 'en' => 'services'],
        'services' => ['id' => 'layanan', 'en' => 'services'],

        'berita' => ['id' => 'berita', 'en' => 'news'],
        'news' => ['id' => 'berita', 'en' => 'news'],

        'kontak' => ['id' => 'kontak', 'en' => 'contact'],
        'contact' => ['id' => 'kontak', 'en' => 'contact'],
    ];

    if (!isset($slug_map[$menu_slug])) {
        return $current_slug === $menu_slug;
    }

    $active_slug = $slug_map[$menu_slug][$lang] ?? $menu_slug;

    return $current_slug === $active_slug;
}

function lang_url($en, $id) {
    return current_lang() === 'en' ? $en : $id;
}

function buildSeoUrls($data, $pair, $base_url, $prefix_id = '', $prefix_en = '')
{
    $slug = trim($data['slug'] ?? '', '/');
    $pair_slug = trim($pair['slug'] ?? '', '/');

    $lang = $data['language']
        ?? $data['language_code']
        ?? 'id';

    $prefix_id = trim($prefix_id, '/');
    $prefix_en = trim($prefix_en, '/');

    $makeIdUrl = function ($slug) use ($base_url, $prefix_id) {
        if ($slug === '' || $slug === 'beranda') {
            return $base_url;
        }

        return $base_url . ($prefix_id !== '' ? $prefix_id . '/' : '') . $slug;
    };

    $makeEnUrl = function ($slug) use ($base_url, $prefix_en) {
        if ($slug === '' || $slug === 'home') {
            return $base_url . 'en';
        }

        return $base_url . 'en/' . ($prefix_en !== '' ? $prefix_en . '/' : '') . $slug;
    };

    if ($lang === 'en') {
        $canonical = $makeEnUrl($slug);
        $alt_en = $canonical;
        $alt_id = $pair_slug !== '' ? $makeIdUrl($pair_slug) : $base_url;
    } else {
        $canonical = $makeIdUrl($slug);
        $alt_id = $canonical;
        $alt_en = $pair_slug !== '' ? $makeEnUrl($pair_slug) : $base_url . 'en';
    }

    return [
        'canonical' => $canonical,
        'alt_id'    => $alt_id,
        'alt_en'    => $alt_en,
        'x_default' => $alt_id
    ];
}
