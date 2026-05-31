        <!-- Start Sidebar Area -->
        <div class="sidebar-area admin-sidebar" id="sidebar-area">
            <div class="logo admin-sidebar-logo">
                <a href="<?= admin_url('dashboard') ?>" class="d-block text-decoration-none">
                    <img src="<?= $base_url ?>img/logo-nucoco.webp" alt="Nucoco">
                </a>
                <button class="sidebar-burger-menu-close bg-transparent border-0" id="sidebar-burger-menu-close" aria-label="Close menu">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
                <?php
                $admin_name = $_SESSION['name'] ?? 'John Doe';
                $admin_role = $_SESSION['role'] ?? 'Administrator';
                $menu_groups = [
                    '' => [
                        'dashboard' => ['title' => 'Dashboard', 'icon' => 'home', 'url' => 'dashboard', 'permission' => 'dashboard.view'],
                    ],
                    'Sales Management' => [
                        'leads' => ['title' => 'Leads', 'icon' => 'group', 'url' => 'leads', 'permission' => 'sales.leads.manage'],
                        'quotations' => ['title' => 'Quotations', 'icon' => 'request_quote', 'url' => 'quotations', 'permission' => 'sales.quotations.manage'],
                        'invoices' => ['title' => 'Invoices', 'icon' => 'receipt_long', 'url' => 'invoices', 'permission' => 'sales.invoices.manage'],
                    ],
                    'Web Admin' => [
                        'inbox' => ['title' => 'Inbox', 'icon' => 'email', 'url' => 'inbox', 'permission' => 'webadmin.inbox.manage'],
                        'pages' => ['title' => 'Pages', 'icon' => 'description', 'url' => 'pages', 'permission' => 'webadmin.pages.manage'],
                        'pages-content' => ['title' => 'Page Content', 'icon' => 'article', 'url' => 'pages-content', 'permission' => 'webadmin.page_content.manage'],
                        'product' => ['title' => 'Products', 'icon' => 'inventory_2', 'url' => 'product', 'permission' => 'webadmin.products.manage'],
                        'services' => ['title' => 'Services', 'icon' => 'support_agent', 'url' => 'services', 'permission' => 'webadmin.services.manage'],
                        'news' => ['title' => 'News', 'icon' => 'newspaper', 'url' => 'news', 'permission' => 'webadmin.news.manage'],
                        'team' => ['title' => 'Team', 'icon' => 'groups', 'url' => 'team', 'permission' => 'webadmin.team.manage'],
                        'testimonial' => ['title' => 'Testimonial', 'icon' => 'reviews', 'url' => 'testimonial', 'permission' => 'webadmin.testimonial.manage'],
                        'faq' => ['title' => 'FAQ', 'icon' => 'quiz', 'url' => 'faq', 'permission' => 'webadmin.faq.manage'],
                        'choose-us' => ['title' => 'Choose Us', 'icon' => 'task_alt', 'url' => 'choose-us', 'permission' => 'webadmin.choose_us.manage'],
                        'setting' => ['title' => 'Web Settings', 'icon' => 'settings', 'url' => 'setting', 'permission' => 'webadmin.settings.manage'],
                    ],
                    'User Management' => [
                        'users' => ['title' => 'Users', 'icon' => 'person', 'url' => 'users', 'permission' => 'users.users.manage'],
                        'user-roles' => ['title' => 'User Roles', 'icon' => 'admin_panel_settings', 'url' => 'user-roles', 'permission' => 'users.roles.manage'],
                        'user-access' => ['title' => 'User Access', 'icon' => 'lock_open', 'url' => 'user-access', 'permission' => 'users.access.manage'],
                    ],
                ];
                ?>

                <ul class="menu-inner">
                    <?php foreach ($menu_groups as $group_title => $menus): ?>
                        <?php
                        $visible_menus = array_filter($menus, function ($menu) {
                            return empty($menu['permission']) || can($menu['permission']);
                        });
                        if (!$visible_menus) {
                            continue;
                        }
                        ?>
                        <?php if ($group_title !== ''): ?>
                            <li class="menu-title"><?= htmlspecialchars($group_title) ?></li>
                        <?php endif; ?>

                        <?php foreach ($visible_menus as $key => $menu): ?>
                            <li class="menu-item <?= ($page == $key) ? 'open' : '' ?>">
                                <a href="<?= $admin_base_url . $menu['url'] ?>" class="menu-link <?= ($page == $key) ? 'active' : '' ?>">
                                    <span class="material-symbols-outlined menu-icon"><?= $menu['icon'] ?></span>
                                    <span class="title"><?= $menu['title'] ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </aside>

            <div class="admin-sidebar-user">
                <img src="<?= $admin_base_url ?>assets/images/admin.png" alt="Admin">
                <div>
                    <strong><?= htmlspecialchars($admin_name) ?></strong>
                    <span><?= htmlspecialchars(ucfirst($admin_role)) ?></span>
                </div>
                <a href="<?= admin_url('logout') ?>" aria-label="Logout">
                    <span class="material-symbols-outlined">expand_more</span>
                </a>
            </div>
        </div>
        <!-- End Sidebar Area -->
