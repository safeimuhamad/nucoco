<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

start_app_session();

if (!function_exists('current_user_id')) {
    function current_user_id()
    {
        return $_SESSION['user_id'] ?? null;
    }
}

if (!function_exists('current_user_role')) {
    function current_user_role()
    {
        return $_SESSION['role'] ?? null;
    }
}

if (!function_exists('is_logged_in')) {
    function is_logged_in()
    {
        return !empty($_SESSION['user_id']);
    }
}

if (!function_exists('can')) {
    function can($permission)
    {
        $role = current_user_role();

        if ($role === 'admin') {
            return true;
        }

        if ($permission === '' || $permission === 'dashboard.view') {
            return true;
        }

        if (!$role || !function_exists('db_select_one')) {
            return false;
        }

        $access = db_select_one(
            "SELECT allowed FROM user_access WHERE role_key = ? AND permission_key = ? LIMIT 1",
            'ss',
            [$role, $permission]
        );

        return !empty($access) && (int) $access['allowed'] === 1;
    }
}

if (!function_exists('require_permission')) {
    function require_permission($permission)
    {
        if (!can($permission)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }
}

if (!is_logged_in()) {
    header('Location: ' . admin_url('login'));
    exit;
}

require_once __DIR__ . '/db.php';

if (!function_exists('page_permission')) {
    function page_permission($page)
    {
        $map = [
            'dashboard' => 'dashboard.view',
            'leads' => 'sales.leads.manage',
            'quotations' => 'sales.quotations.manage',
            'invoices' => 'sales.invoices.manage',
            'inbox' => 'webadmin.inbox.manage',
            'pages' => 'webadmin.pages.manage',
            'pages-content' => 'webadmin.page_content.manage',
            'product' => 'webadmin.products.manage',
            'product-categories' => 'webadmin.product_categories.manage',
            'services' => 'webadmin.services.manage',
            'news' => 'webadmin.news.manage',
            'team' => 'webadmin.team.manage',
            'testimonial' => 'webadmin.testimonial.manage',
            'faq' => 'webadmin.faq.manage',
            'choose-us' => 'webadmin.choose_us.manage',
            'setting' => 'webadmin.settings.manage',
            'users' => 'users.users.manage',
            'user-roles' => 'users.roles.manage',
            'user-access' => 'users.access.manage',
        ];

        return $map[$page] ?? '';
    }
}

if (!empty($page)) {
    $permission = page_permission($page);
    if ($permission !== '') {
        require_permission($permission);
    }
}
