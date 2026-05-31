        <!-- Start Sidebar Area -->
        <div class="sidebar-area" id="sidebar-area">
            <div class="logo position-relative d-flex align-items-center justify-content-between">
                <a href="index.html" class="d-block text-decoration-none position-relative">
                    <img src="<?= $admin_base_url ?>assets/images/logo-22.webp" alt="logo-icon">
                    <span class="logo-text text-secondary fw-semibold"></span>
                </a> 
                <button class="sidebar-burger-menu-close bg-transparent py-3 border-0 opacity-0 z-n1 position-absolute top-50 end-0 translate-middle-y" id="sidebar-burger-menu-close">
                    <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px; transform: rotate(45deg);"></span>
                    <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px; transform: rotate(-45deg);"></span>
                </button>
                <button class="sidebar-burger-menu bg-transparent p-0 border-0" id="sidebar-burger-menu">
                    <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px;"></span>
                    <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px; margin: 6px 0;"></span>
                    <span class="border-1 d-block for-dark-burger" style="border-bottom: 1px solid #475569; height: 1px; width: 25px;"></span>
                </button>
            </div>
            <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
                <?php
                $menus = [
                    'dashboard' => ['title' => 'Dashboard', 'icon' => 'dashboard', 'url' => ''],
                    'pages'   => ['title' => 'Pages',   'icon' => 'ballot',   'url' => 'pages'],
                    'pages-content'   => ['title' => 'Pages Content',   'icon' => 'ballot',   'url' => 'pages-content'],
                    'product'   => ['title' => 'Product',   'icon' => 'ballot',   'url' => 'product'],
                    'services'  => ['title' => 'Services',  'icon' => 'ballot',   'url' => 'services'],
                    'news'      => ['title' => 'News',      'icon' => 'article',  'url' => 'news'],
                    'team'      => ['title' => 'Team',      'icon' => 'people',  'url' => 'team'],
                    'testimonial'=> ['title' => 'Testimonial',      'icon' => 'person',  'url' => 'testimonial'],
                    'faq'=> ['title' => 'FAQ',      'icon' => 'list',  'url' => 'faq'],
                    'choose-us'=> ['title' => 'Choose Us',      'icon' => 'list',  'url' => 'choose-us'],
                    'inbox'     => ['title' => 'Inbox',     'icon' => 'email',    'url' => 'inbox'],
                ];
                ?>

                <ul class="menu-inner">
                <?php foreach ($menus as $key => $menu): ?>
                    <li class="menu-item <?= ($page == $key) ? 'open' : '' ?>">
                        <a href="<?= $admin_base_url . $menu['url'] ?>" class="menu-link <?= ($page == $key) ? 'active' : '' ?>">
                            <span class="material-symbols-outlined menu-icon"><?= $menu['icon'] ?></span>
                            <span class="title"><?= $menu['title'] ?></span>
                        </a>
                    </li>
                <?php endforeach; ?>

                    <li class="menu-item">
                        <a href="<?= $admin_base_url ?>logout.php" class="menu-link">
                            <span class="material-symbols-outlined menu-icon">logout</span>
                            <span class="title">Logout</span>
                        </a>
                    </li>
                </ul>
            </aside>
        </div>
        <!-- End Sidebar Area -->