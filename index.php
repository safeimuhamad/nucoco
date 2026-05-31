<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/admin/includes/db.php';
require_once __DIR__ . '/includes/helpers.php';
$base_url = site_base_url();
$segments = array_values(array_filter(explode('/', site_request_path())));

$lang = 'id';

if (!empty($segments) && $segments[0] === 'en') {
    $lang = 'en';
    array_shift($segments);
}

$slug = $segments[0] ?? '';

/*
|--------------------------------------------------------------------------
| Handle homepage
|--------------------------------------------------------------------------
*/
if ($slug === '') {
    $slug = ($lang === 'en') ? 'home' : 'beranda';
}

/*
|--------------------------------------------------------------------------
| Load page by pages.slug + language
|--------------------------------------------------------------------------
*/
$stmt = mysqli_prepare($conn, "
    SELECT 
    p.id,
    p.page_name,
    p.slug,
    p.status,
    p.language_code,
    p.translation_group_id,
    pc.meta_title,
    pc.meta_description,
    pc.meta_keywords,
    pc.meta_robots,
    pc.og_title,
    pc.og_description,
    pc.og_image,
    pc.hero_title,
    pc.schema_markup
    FROM pages p
    LEFT JOIN page_contents pc 
    ON pc.page_id = p.id 
    WHERE p.slug = ?
    AND p.language_code = ?
    AND p.status = 'publish'
    LIMIT 1
    ");

if (!$stmt) {
    die('Prepare failed: ' . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ss", $slug, $lang);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$page = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);
$pair_lang = ($lang === 'en') ? 'id' : 'en';
$page_pair = null;

if (!empty($page['translation_group_id'])) {
    $pair_stmt = mysqli_prepare($conn, "
        SELECT 
        p.id,
        p.page_name,
        p.slug,
        p.status,
        p.language_code,
        p.translation_group_id
        FROM pages p
        WHERE p.translation_group_id = ?
        AND p.language_code = ?
        AND p.status = 'publish'
        LIMIT 1
        ");

    if ($pair_stmt) {
        mysqli_stmt_bind_param($pair_stmt, "is", $page['translation_group_id'], $pair_lang);
        mysqli_stmt_execute($pair_stmt);
        $pair_result = mysqli_stmt_get_result($pair_stmt);
        $page_pair = mysqli_fetch_assoc($pair_result);
        mysqli_stmt_close($pair_stmt);
    }
}

if (!$page) {
    http_response_code(404);
    die('Page not found');
}

$seo = buildSeoUrls($page, $page_pair, $base_url, '', '');
$page['canonical_url']   = $seo['canonical'];
$page['alternate_id_url'] = $seo['alt_id'];
$page['alternate_en_url'] = $seo['alt_en'];
$current_page = $page['slug'] ?? $slug;

include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';

/*
|--------------------------------------------------------------------------
| Load template file
|--------------------------------------------------------------------------
*/

$template_map = [
    'home' => 'home',
    'beranda' => 'home',

    'about' => 'about',
    'tentang-kami' => 'about',

    'product' => 'product',
    'produk' => 'product',

    'services' => 'services',
    'layanan' => 'services',

    'news' => 'news',
    'berita' => 'news',

    'contact' => 'contact',
    'kontak' => 'contact',
];

$template_slug = $template_map[$page['slug']] ?? 'default';
$page_file = __DIR__ . '/pages/' . $template_slug . '.php';
?>
<main id="main-content" role="main">
    <?php
    if (file_exists($page_file)) {
        include $page_file;
    } else {
        include __DIR__ . '/pages/default.php';
    }
    ?>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>
