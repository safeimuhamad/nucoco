<?php
$is_en = current_lang() === 'en';
?>

<!-- FOOTER AREA START -->
<footer class="ltn__footer-area">
    <div class="footer-top-area section-bg-2 plr--5">
        <div class="container-fluid">
            <div class="row">

                <!-- ABOUT -->
                <div class="col-xl-3 col-md-6 col-sm-6 col-12">
                    <div class="footer-widget footer-about-widget">
                        <div class="footer-logo">
                            <div class="site-logo">
                                <img src="<?= $base_url ?>img/logo-nucoco.webp" alt="Logo" width="231">
                            </div>
                        </div>
                        <p>
                            <?= $is_en 
                                ? 'Premium Coconut Products from Indonesia. Supplying fresh, processed, and industrial coconut solutions for global markets with reliable quality and competitive pricing.'
                                : 'Produk kelapa premium dari Indonesia. Menyediakan solusi kelapa segar, olahan, dan industri untuk pasar global dengan kualitas terjamin dan harga kompetitif.' ?>
                        </p>

                        <div class="footer-address">
                            <ul>
                                <li>
                                    <div class="footer-address-icon">
                                        <i class="icon-placeholder"></i>
                                    </div>
                                    <div class="footer-address-info">
                                        <p>Bellezza BSA 1st Floor, Jakarta</p>
                                    </div>
                                </li>
                                <li>
                                    <div class="footer-address-icon">
                                        <i class="icon-call"></i>
                                    </div>
                                    <div class="footer-address-info">
                                        <p dir="ltr"><a href="tel:+622189090882">+62 218 9090 882</a></p>
                                    </div>
                                </li>
                                <li>
                                    <div class="footer-address-icon">
                                        <i class="icon-mail"></i>
                                    </div>
                                    <div class="footer-address-info">
                                        <p><a href="mailto:info@nucoco.id">info@nucoco.id</a></p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="ltn__social-media mt-20">
                            <ul>
                                <li><a href="#" aria-label="facebook"><i class="fab fa-facebook-f"></i></a></li>
                                <li><a href="#" aria-label="twitter"><i class="fab fa-twitter"></i></a></li>
                                <li><a href="#" aria-label="linkedin"><i class="fab fa-linkedin"></i></a></li>
                                <li><a href="#" aria-label="fa-youtube"><i class="fab fa-youtube"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <!-- COMPANY -->
                <div class="col-xl-2 col-md-6 col-sm-6 col-12">
                    <div class="footer-widget footer-menu-widget clearfix">
                        <h3 class="footer-title">
                            <?= $is_en ? 'Company' : 'Perusahaan' ?>
                        </h3>
                        <div class="footer-menu">
                            <ul>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/about' : 'tentang-kami') ?>">
                                        <?= $is_en ? 'About' : 'Tentang Kami' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/news' : 'berita') ?>">
                                        <?= $is_en ? 'News' : 'Berita' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/product' : 'produk') ?>">
                                        <?= $is_en ? 'Products' : 'Produk' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/contact' : 'kontak') ?>">
                                        <?= $is_en ? 'Contact Us' : 'Hubungi Kami' ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- SERVICES -->
                <div class="col-xl-2 col-md-6 col-sm-6 col-12">
                    <div class="footer-widget footer-menu-widget clearfix">
                        <h3 class="footer-title">
                            <?= $is_en ? 'Services' : 'Layanan' ?>
                        </h3>
                        <div class="footer-menu">
                            <ul>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/services/industrial-bulk-supply' : 'layanan/pasokan-massal-industri') ?>">
                                        <?= $is_en ? 'Industrial Bulk Supply' : 'Pasokan Massal Industri' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/services/global-export-supply' : 'layanan/ekspor-pasokan-global') ?>">
                                        <?= $is_en ? 'Global Export Supply' : 'Ekspor Pasokan Global' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/services/oem-private-label' : 'layanan/maklon-merek-sendiri') ?>">
                                        <?= $is_en ? 'OEM Private Label' : 'Maklon Merek Sendiri' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/services/quality-certification-support' : 'layanan/dukungan-kualitas-sertifikasi') ?>">
                                        <?= $is_en ? 'Quality Certification Support' : 'Dukungan Kualitas Sertifikasi' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/services/sourcing-consolidation' : 'layanan/pengadaan-konsolidasi') ?>">
                                        <?= $is_en ? 'Sourcing Consolidation' : 'Pengadaan Konsolidasi' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/services/product-customization' : 'layanan/kostumisasi-produk') ?>">
                                        <?= $is_en ? 'Product Customization' : 'Kostumisasi Produk' ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- PRODUCTS CATEGORY -->
                <div class="col-xl-2 col-md-6 col-sm-6 col-12">
                    <div class="footer-widget footer-menu-widget clearfix">
                        <h3 class="footer-title">
                            <?= $is_en ? 'Product Categories' : 'Kategori Produk' ?>
                        </h3>
                        <div class="footer-menu">
                            <ul>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/product' : 'produk') ?>">
                                        <?= $is_en ? 'Fresh Coconut' : 'Kelapa Segar' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/product' : 'produk') ?>">
                                        <?= $is_en ? 'Coconut Ingredients' : 'Bahan Baku Kelapa' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/product' : 'produk') ?>">
                                        <?= $is_en ? 'Coconut Derivatives' : 'Turunan Kelapa' ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="<?= $base_url . ($is_en ? 'en/product' : 'produk') ?>">
                                        <?= $is_en ? 'Coconut Industrial Products' : 'Produk Industri Kelapa' ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- NEWSLETTER -->
                <div class="col-xl-3 col-md-6 col-sm-12 col-12">
                    <div class="footer-widget footer-newsletter-widget fix">
                        <h3 class="footer-title">Newsletter</h3>

                        <p>
                            <?= $is_en 
                                ? 'Subscribe to our newsletter and get the latest updates.'
                                : 'Berlangganan newsletter kami untuk mendapatkan update terbaru.' ?>
                        </p>

                        <div class="footer-newsletter">
                            <form action="<?= $base_url ?>subscribe" method="post">
                                <input type="email" name="email" required placeholder="<?= $is_en ? 'Email Address' : 'Alamat Email' ?>">
                                <button class="theme-btn-1 btn" type="submit">
                                    <?= $is_en ? 'Subscribe' : 'Langganan' ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- COPYRIGHT -->
    <div class="ltn__copyright-area ltn__copyright-2 section-bg-2 ltn__border-top-2 plr--5">
        <div class="container-fluid">
            <div class="row">

                <div class="col-md-6 col-12">
                    <div class="ltn__copyright-design clearfix">
                        <p>
                            <?= $is_en 
                                ? 'All Rights Reserved © Nucoco'
                                : 'Hak Cipta Dilindungi © Nucoco' ?>
                            <span class="current-year"></span>
                        </p>
                    </div>
                </div>

                <div class="col-md-6 col-12 align-self-center">
                    <div class="ltn__copyright-menu text-right text-end">
                        <ul>
                            <li><a href="#"><?= $is_en ? 'Terms & Conditions' : 'Syarat & Ketentuan' ?></a></li>
                            <li><a href="#"><?= $is_en ? 'Claim' : 'Klaim' ?></a></li>
                            <li><a href="#"><?= $is_en ? 'Privacy Policy' : 'Kebijakan Privasi' ?></a></li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>
</footer>
</div>
    <!-- FOOTER AREA END -->
        <!-- preloader area start -->
    <div class="preloader d-none" id="preloader">
        <div class="preloader-inner">
            <div class="spinner">
                <div class="dot1"></div>
                <div class="dot2"></div>
            </div>
        </div>
    </div>
    <script src="<?= $base_url ?>js/plugins.js?v=2.1" defer></script>
    <script src="<?= $base_url ?>js/main.js?v=2.1" defer></script>
</body>
</html>
