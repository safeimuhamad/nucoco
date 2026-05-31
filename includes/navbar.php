        <?php
        if (!isset($conn)) {
            require_once __DIR__ . '/../admin/includes/db.php';
        }

        require_once __DIR__ . '/helpers.php';
        ?>
        <body>
            <!--[if lte IE 9]>
                <p class="browserupgrade">You are using an <strong>outdated</strong> browser. Please <a href="https://browsehappy.com/">upgrade your browser</a> to improve your experience and security.</p>
            <![endif]-->

                <!-- Add your site or application content here -->

                <!-- Body main wrapper start -->
                <div class="body-wrapper">

                    <!-- HEADER AREA START (header-5) -->
                    <header class="ltn__header-area ltn__header-5 ltn__header-transparent-- gradient-color-4---">
                        <!-- ltn__header-top-area start -->
                        <div class="ltn__header-top-area">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-7">
                                        <div class="ltn__top-bar-menu">
                                            <ul>
                                                <li><a href="#"><i class="icon-placeholder"></i> Bellezza BSA 1st Floor</a></li>
                                                <li><a href="mailto:info@floria.id?Subject=Hallo%20Floria.id,"><i class="icon-mail"></i>info@nucoco.id</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="top-bar-right text-right text-end">
                                            <div class="ltn__top-bar-menu">
                                                <ul>
                                                    <li>
                                                        <div class="ltn__drop-menu ltn__currency-menu ltn__language-menu">
                                                            <ul>
                                                                <li>
                                                                    <a href="#" class="dropdown-toggle">
                                                                        <span class="active-currency">
                                                                            <?= current_lang() === 'en' ? '🇬🇧 English' : '🇮🇩 Indonesia' ?>
                                                                        </span>
                                                                    </a>
                                                                    <ul>
                                                                        <?php if (current_lang() !== 'id'): ?>
                                                                            <li>
                                                                                <a href="<?= htmlspecialchars(switch_lang_seo_url($conn, 'id')) ?>">
                                                                                    🇮🇩 Indonesia
                                                                                </a>
                                                                            </li>
                                                                        <?php else: ?>
                                                                            <li>
                                                                                <a href="#" onclick="return false;">
                                                                                    🇮🇩 Indonesia
                                                                                </a>
                                                                            </li>
                                                                        <?php endif; ?>

                                                                        <?php if (current_lang() !== 'en'): ?>
                                                                            <li>
                                                                                <a href="<?= htmlspecialchars(switch_lang_seo_url($conn, 'en')) ?>">
                                                                                    🇬🇧 English
                                                                                </a>
                                                                            </li>
                                                                        <?php else: ?>
                                                                            <li>
                                                                                <a href="#" onclick="return false;">
                                                                                    🇬🇧 English
                                                                                </a>
                                                                            </li>
                                                                        <?php endif; ?>
                                                                    </ul>
                                                                </li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <!-- ltn__social-media -->
                                                        <div class="ltn__social-media">
                                                            <ul>
                                                                <li><a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a></li>
                                                                <li><a href="#" title="Twitter"><i class="fab fa-twitter"></i></a></li>
                                                                
                                                                <li><a href="#" title="Instagram"><i class="fab fa-instagram"></i></a></li>
                                                                <li><a href="#" title="Dribbble"><i class="fab fa-dribbble"></i></a></li>
                                                            </ul>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- ltn__header-top-area end -->

                        <!-- ltn__header-middle-area start -->
                        <div class="ltn__header-middle-area ltn__header-sticky ltn__sticky-bg-white sticky-active-into-mobile ltn__logo-right-menu-option plr--9---">
                            <div class="container">
                                <div class="row">
                                    <div class="col">
                                        <div class="site-logo-wrap">
                                            <div class="site-logo">
                                                <a href="<?= url() ?>"><img 
                                                    src="<?= $base_url ?>img/logo-nucoco.webp"
                                                    srcset="<?= $base_url ?>img/logo-nucoco.webp 1x, <?= $base_url ?>img/logo-nucoco@2x.webp 2x"
                                                    alt="Logo Nucoco"
                                                    width="231"
                                                    height="73"
                                                    style="max-width:100%; height:auto;"
                                                    fetchpriority="high"></a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col header-menu-column menu-color-white---">
                                            <div class="header-menu d-none d-xl-block">
                                                <nav>
                                                    <div class="ltn__main-menu">
                                                        <ul>
                                                            <?php $menus = get_menu_pages($conn); ?>

                                                            <?php foreach ($menus as $menu): ?>
                                                                <li class="<?= is_active_menu($menu['slug']) ? 'active current-menu-item' : '' ?>">
                                                                    <a href="<?= ($menu['slug'] === 'home' || $menu['slug'] === 'beranda') ? url() : url($menu['slug']) ?>">
                                                                        <?= htmlspecialchars($menu['page_name']) ?>
                                                                    </a>
                                                                </li>
                                                            <?php endforeach; ?>

                                                            <li class="special-link">
                                                                <a href="<?= url(current_lang() === 'en' ? 'contact' : 'kontak') ?>">
                                                                    <?= current_lang() === 'en' ? 'GET A QUOTE' : 'DAPATKAN PENAWARAN' ?>
                                                                </a>
                                                            </li>
                                                        </ul>
                                                    </div>
                                                </nav>
                                            </div>
                                        </div>
                                        <div class="ltn__header-options ltn__header-options-2 mb-sm-20">
                                            <!-- Mobile Menu Button -->
                                            <div class="mobile-menu-toggle d-xl-none">
                                                <a href="#ltn__utilize-mobile-menu" class="ltn__utilize-toggle" aria-label="Open menu">
                                                    <svg viewBox="0 0 800 600">
                                                        <path d="M300,220 C300,220 520,220 540,220 C740,220 640,540 520,420 C440,340 300,200 300,200" id="top"></path>
                                                        <path d="M300,320 L540,320" id="middle"></path>
                                                        <path d="M300,210 C300,210 520,210 540,210 C740,210 640,530 520,410 C440,330 300,190 300,190" id="bottom" transform="translate(480, 320) scale(1, -1) translate(-480, -318) "></path>
                                                    </svg>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- ltn__header-middle-area end -->
                        </header>
                        <!-- HEADER AREA END -->

                        <!-- Utilize Mobile Menu Start -->
                        <div id="ltn__utilize-mobile-menu" class="ltn__utilize ltn__utilize-mobile-menu">
                            <div class="ltn__utilize-menu-inner ltn__scrollbar">
                                <div class="ltn__utilize-menu-head">
                                    <div class="site-logo">
                                        <a href="<?= url() ?>"><img src="<?= $base_url ?>img/logo-mobile.webp" alt="Logo" width="231" height="73" alt="Logo Nucoco" fetchpriority="high"></a>
                                    </div>
                                    <button class="ltn__utilize-close" aria-label="close">×</button>
                                </div>

                                <div class="ltn__utilize-menu-search-form">
                                    <form action="#">
                                        <input type="text" placeholder="<?= current_lang() === 'en' ? 'Search...' : 'Cari...' ?>">
                                        <button aria-label="search"><i class="fas fa-search"></i></button>
                                    </form>
                                </div>

                                <div class="ltn__utilize-menu">
                                    <ul>
                                        <?php $menus = get_menu_pages($conn); ?>

                                        <?php foreach ($menus as $menu): ?>
                                            <li class="<?= is_active_menu($menu['slug']) ? 'active current-menu-item' : '' ?>">
                                                <a href="<?= ($menu['slug'] === 'home' || $menu['slug'] === 'beranda') ? url() : url($menu['slug']) ?>">
                                                    <?= htmlspecialchars($menu['page_name']) ?>
                                                </a>
                                            </li>
                                        <?php endforeach; ?>

                                        <li class="special-link">
                                            <a href="<?= url(current_lang() === 'en' ? 'contact' : 'kontak') ?>">
                                                <?= current_lang() === 'en' ? 'GET A QUOTE' : 'DAPATKAN PENAWARAN' ?>
                                            </a>
                                        </li>
                                    </ul>
                                </div>

                                <div class="ltn__social-media-2">
                                    <ul>
                                <li><a href="#" aria-label="facebook"><i class="fab fa-facebook-f"></i></a></li>
                                <li><a href="#" aria-label="twitter"><i class="fab fa-twitter"></i></a></li>
                                <li><a href="#" aria-label="linkedin"><i class="fab fa-linkedin"></i></a></li>
                                <li><a href="#" aria-label="fa-youtube"><i class="fab fa-youtube"></i></a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <!-- Utilize Mobile Menu End -->

                        <div class="ltn__utilize-overlay"></div>