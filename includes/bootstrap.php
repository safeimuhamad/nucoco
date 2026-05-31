<?php

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

if (!function_exists('app_config')) {
    function app_config($key = null, $default = null)
    {
        static $config = null;

        if ($config === null) {
            $config_file = APP_ROOT . '/config/app.php';
            $config = file_exists($config_file) ? require $config_file : [];
        }

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}

if (!function_exists('database_config')) {
    function database_config($key = null, $default = null)
    {
        static $config = null;

        if ($config === null) {
            $config_file = APP_ROOT . '/config/database.php';
            $config = file_exists($config_file) ? require $config_file : [];
        }

        if ($key === null) {
            return $config;
        }

        return $config[$key] ?? $default;
    }
}

if (!function_exists('app_debug')) {
    function app_debug()
    {
        return (bool) app_config('debug', false);
    }
}

if (!function_exists('site_base_path')) {
    function site_base_path()
    {
        $document_root = realpath($_SERVER['DOCUMENT_ROOT'] ?? '');
        $project_root = realpath(APP_ROOT);

        if ($document_root && $project_root && str_starts_with($project_root, $document_root)) {
            $relative = trim(str_replace('\\', '/', substr($project_root, strlen($document_root))), '/');
            return $relative === '' ? '' : '/' . $relative;
        }

        $script_dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        return in_array($script_dir, ['/', '.', '\\'], true) ? '' : rtrim($script_dir, '/');
    }
}

if (!function_exists('site_request_path')) {
    function site_request_path()
    {
        $path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base_path = site_base_path();

        if ($base_path !== '' && ($path === $base_path || str_starts_with($path, $base_path . '/'))) {
            $path = substr($path, strlen($base_path));
        }

        return trim($path, '/');
    }
}

if (!function_exists('site_base_url')) {
    function site_base_url()
    {
        $configured_url = app_config('url');

        if (!empty($configured_url)) {
            return rtrim($configured_url, '/') . '/';
        }

        if (empty($_SERVER['HTTP_HOST'])) {
            return 'https://nucoco.id/';
        }

        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (($_SERVER['SERVER_PORT'] ?? '') === '443');
        $scheme = $is_https ? 'https' : 'http';

        return $scheme . '://' . $_SERVER['HTTP_HOST'] . site_base_path() . '/';
    }
}

if (!function_exists('admin_base_url')) {
    function admin_base_url()
    {
        return site_base_url() . trim(app_config('admin_path', 'admin'), '/') . '/';
    }
}

if (!function_exists('asset_url')) {
    function asset_url($path = '')
    {
        return site_base_url() . ltrim($path, '/');
    }
}

if (!function_exists('admin_url')) {
    function admin_url($path = '')
    {
        return admin_base_url() . ltrim($path, '/');
    }
}

if (!function_exists('start_app_session')) {
    function start_app_session()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token()
    {
        start_app_session();

        if (empty($_SESSION['_csrf_token'])) {
            $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['_csrf_token'];
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field()
    {
        return '<input type="hidden" name="_csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }
}

if (!function_exists('verify_csrf_token')) {
    function verify_csrf_token($token)
    {
        start_app_session();
        return isset($_SESSION['_csrf_token']) && is_string($token) && hash_equals($_SESSION['_csrf_token'], $token);
    }
}

if (!function_exists('require_csrf_token')) {
    function require_csrf_token()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && !verify_csrf_token($_POST['_csrf_token'] ?? '')) {
            http_response_code(419);
            exit('Invalid CSRF token');
        }
    }
}

date_default_timezone_set(app_config('timezone', 'Asia/Jakarta'));

if (app_debug()) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', '0');
}
