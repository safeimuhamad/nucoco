<?php

function get_menu_pages($conn)
{
    $data = [];

    $sql = "SELECT id, page_name, slug
            FROM pages
            WHERE status = 'publish'
            ORDER BY id ASC";

    $result = mysqli_query($conn, $sql);

    if (!$result) {
        die('Menu query error: ' . mysqli_error($conn));
    }

    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }

    return $data;
}



if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function current_lang()
{
    $allowed = ['id', 'en'];

    if (isset($_GET['lang']) && in_array($_GET['lang'], $allowed, true)) {
        $_SESSION['site_lang'] = $_GET['lang'];
    }

    return $_SESSION['site_lang'] ?? 'id';
}

function switch_lang_url($lang)
{
    $allowed = ['id', 'en'];

    if (!in_array($lang, $allowed, true)) {
        $lang = 'id';
    }

    $query = $_GET;
    $query['lang'] = $lang;

    $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    return $path . '?' . http_build_query($query);
}

function lang_label($lang = null)
{
    $lang = $lang ?: current_lang();

    return $lang === 'en' ? 'English' : 'Indonesia';
}