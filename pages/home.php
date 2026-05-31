
 <div class="ltn__utilize-overlay"></div>
<div class="ltn__slider-area ltn__slider-3 section-bg-1"
    role="region"
    aria-label="<?= current_lang() === 'en' ? 'Main banner slider' : 'Slider banner utama' ?>">

    <div class="ltn__slide-one-active slick-slide-arrow-1 slick-slide-dots-1">

        <!-- SLIDE 1 -->
        <div class="ltn__slide-item ltn__slide-item-2 ltn__slide-item-3 ltn__slide-item-3-normal hero-slide-item hero-slide-first">
            <img
                src="<?= $base_url ?>img/slider/kelapa-muda-slider.webp"
                alt="<?= current_lang() === 'en' ? 'Premium Fresh Coconut For Healthy Living' : 'Kelapa Segar Premium untuk Hidup Sehat' ?>"
                class="hero-slide-bg"
                width="1600"
                height="720"
                fetchpriority="high"
                loading="eager"
                decoding="sync"
            >

            <div class="ltn__slide-item-inner text-right text-end">
                <div class="container">
                    <div class="slide-item-info-inner">

                        <h6 class="slide-sub-title ltn__secondary-color">
                            // <?= current_lang() === 'en' ? 'FRESH FROM NATURE' : 'SEGAR DARI ALAM' ?>
                        </h6>

                        <h1 class="slide-title">
                            <?= current_lang() === 'en'
                            ? 'Premium Fresh Coconut <br> For Healthy Living'
                            : 'Kelapa Segar Premium <br> untuk Hidup Sehat' ?>
                        </h1>

                        <div class="slide-brief" style="margin-top:20px; max-width:650px;">
                            <p style="margin:0;" dir="ltr">
                                <?= current_lang() === 'en'
                                ? 'Fresh young coconuts sourced directly from selected farms. Naturally hydrating, rich in nutrients, and ideal for a healthy lifestyle.'
                                : 'Kelapa muda segar yang dipilih langsung dari kebun terbaik. Kaya nutrisi, menyegarkan secara alami, dan cocok untuk gaya hidup sehat.' ?>
                            </p>
                        </div>

                        <div class="btn-wrapper" style="margin-top:25px;">
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" class="theme-btn-1 btn btn-effect-1 text-uppercase">
                                <?= current_lang() === 'en' ? 'Explore Products' : 'Lihat Produk' ?>
                            </a>

                            <a href="<?= $base_url . lang_url('en/about', 'tentang-kami') ?>" class="btn btn-transparent btn-effect-3">
                                <?= current_lang() === 'en' ? 'About Us' : 'Pelajari Lebih Lanjut' ?>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 2 -->
        <div class="ltn__slide-item ltn__slide-item-2 ltn__slide-item-3 ltn__slide-item-3-normal hero-slide-item">
            <img
                src="<?= $base_url ?>img/slider/kelapa-tua-slider.webp"
                alt="<?= current_lang() === 'en' ? 'Fresh Coconut Naturally Processed' : 'Kelapa Segar Diproses Secara Alami' ?>"
                class="hero-slide-bg"
                width="1600"
                height="720"
                loading="lazy"
                decoding="async"
            >

            <div class="ltn__slide-item-inner">
                <div class="container">
                    <div class="slide-item-info-inner">

                        <h6 class="slide-sub-title ltn__secondary-color">
                            <?= current_lang() === 'en' ? 'NUCOCO RAW MATERIALS //' : 'BAHAN BAKU NUCOCO //' ?>
                        </h6>

                        <h1 class="slide-title">
                            <?= current_lang() === 'en'
                            ? 'Fresh Coconut <br> Naturally Processed'
                            : 'Kelapa Segar <br> Diproses Secara Alami' ?>
                        </h1>

                        <div class="slide-brief" style="margin-top:20px; max-width:650px;">
                            <p style="margin:0;" dir="ltr">
                                <?= current_lang() === 'en'
                                ? 'Carefully selected coconuts processed hygienically to ensure freshness, quality, and consistency.'
                                : 'Kelapa pilihan diproses secara higienis untuk menjaga kesegaran, kualitas, dan konsistensi produk.' ?>
                            </p>
                        </div>

                        <div class="btn-wrapper" style="margin-top:25px;">
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" class="theme-btn-1 btn btn-effect-1 text-uppercase">
                                <?= current_lang() === 'en' ? 'Explore Products' : 'Lihat Produk' ?>
                            </a>

                            <a href="<?= $base_url . lang_url('en/about', 'tentang-kami') ?>" class="btn btn-transparent btn-effect-3">
                                <?= current_lang() === 'en' ? 'About Us' : 'Pelajari Lebih Lanjut' ?>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 3 -->
        <div class="ltn__slide-item ltn__slide-item-2 ltn__slide-item-3 ltn__slide-item-3-normal hero-slide-item">
            <img
                src="<?= $base_url ?>img/slider/nata-decoco-slider.webp"
                alt="<?= current_lang() === 'en' ? 'Naturally Delicious Coconut Creations' : 'Olahan Kelapa Lezat Alami' ?>"
                class="hero-slide-bg"
                width="1600"
                height="720"
                loading="lazy"
                decoding="async"
            >

            <div class="ltn__slide-item-inner text-right text-end">
                <div class="container">
                    <div class="slide-item-info-inner">

                        <h6 class="slide-sub-title">
                            <?= current_lang() === 'en' ? 'NUCOCO FOOD SERIES //' : 'PRODUK MAKANAN NUCOCO //' ?>
                        </h6>

                        <h1 class="slide-title">
                            <?= current_lang() === 'en'
                            ? 'Naturally Delicious <br> Coconut Creations'
                            : 'Olahan Kelapa <br> Lezat Alami' ?>
                        </h1>

                        <div class="slide-brief" style="margin-top:20px; max-width:650px;">
                            <p style="margin:0;" dir="ltr">
                                <?= current_lang() === 'en'
                                ? 'Crafted from selected coconuts to deliver natural taste, creamy texture, and refreshing goodness.'
                                : 'Dibuat dari kelapa pilihan untuk menghadirkan rasa alami, tekstur lembut, dan kesegaran di setiap sajian.' ?>
                            </p>
                        </div>

                        <div class="btn-wrapper" style="margin-top:25px;">
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" class="theme-btn-1 btn btn-effect-1 text-uppercase">
                                <?= current_lang() === 'en' ? 'Explore Products' : 'Lihat Produk' ?>
                            </a>

                            <a href="<?= $base_url . lang_url('en/about', 'tentang-kami') ?>" class="btn btn-transparent btn-effect-3">
                                <?= current_lang() === 'en' ? 'About Us' : 'Pelajari Lebih Lanjut' ?>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 4 -->
        <div class="ltn__slide-item ltn__slide-item-2 ltn__slide-item-3 ltn__slide-item-3-normal hero-slide-item">
            <img
                src="<?= $base_url ?>img/slider/gula-kelapa-slider.webp"
                alt="<?= current_lang() === 'en' ? 'Coconut Sugar Pure and Sustainable' : 'Gula Kelapa Alami dan Berkelanjutan' ?>"
                class="hero-slide-bg"
                width="1600"
                height="720"
                loading="lazy"
                decoding="async"
            >

            <div class="ltn__slide-item-inner">
                <div class="container">
                    <div class="slide-item-info-inner">

                        <h6 class="slide-sub-title">
                            <?= current_lang() === 'en' ? 'NUCOCO NATURAL SERIES //' : 'PRODUK ALAMI NUCOCO //' ?>
                        </h6>

                        <h1 class="slide-title">
                            <?= current_lang() === 'en'
                            ? 'Coconut Sugar <br> Pure & Sustainable'
                            : 'Gula Kelapa <br> Alami & Berkelanjutan' ?>
                        </h1>

                        <div class="slide-brief" style="margin-top:20px; max-width:650px;">
                            <p style="margin:0;" dir="ltr">
                                <?= current_lang() === 'en'
                                ? 'Sourced from selected farms and processed naturally to preserve authentic taste and nutrients.'
                                : 'Berasal dari kebun pilihan dan diproses secara alami untuk menjaga rasa asli dan kandungan nutrisinya.' ?>
                            </p>
                        </div>

                        <div class="btn-wrapper" style="margin-top:25px;">
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" class="theme-btn-1 btn btn-effect-1 text-uppercase">
                                <?= current_lang() === 'en' ? 'Explore Products' : 'Lihat Produk' ?>
                            </a>

                            <a href="<?= $base_url . lang_url('en/about', 'tentang-kami') ?>" class="btn btn-transparent btn-effect-3">
                                <?= current_lang() === 'en' ? 'About Us' : 'Pelajari Lebih Lanjut' ?>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- SLIDE 5 -->
        <div class="ltn__slide-item ltn__slide-item-2 ltn__slide-item-3 ltn__slide-item-3-normal hero-slide-item">
            <img
                src="<?= $base_url ?>img/slider/vco-kelapa-slider.webp"
                alt="<?= current_lang() === 'en' ? 'Natural Coconut Oil For Health and Wellness' : 'Minyak Kelapa Alami untuk Kesehatan dan Kesejahteraan' ?>"
                class="hero-slide-bg"
                width="1600"
                height="720"
                loading="lazy"
                decoding="async"
            >

            <div class="ltn__slide-item-inner text-right text-end">
                <div class="container">
                    <div class="slide-item-info-inner">

                        <h6 class="slide-sub-title ltn__secondary-color">
                            <?= current_lang() === 'en' ? 'PURE COCONUT OIL //' : 'MINYAK KELAPA MURNI //' ?>
                        </h6>

                        <h1 class="slide-title">
                            <?= current_lang() === 'en'
                            ? 'Natural Coconut Oil <br> For Health & Wellness'
                            : 'Minyak Kelapa Alami <br> untuk Kesehatan & Kesejahteraan' ?>
                        </h1>

                        <div class="slide-brief" style="margin-top:20px; max-width:650px;">
                            <p style="margin:0;" dir="ltr">
                                <?= current_lang() === 'en'
                                ? 'Premium coconut oil made from selected coconuts, rich in nutrients and ideal for cooking, skincare, and wellness.'
                                : 'Minyak kelapa premium dari bahan pilihan, kaya nutrisi dan cocok untuk memasak, perawatan tubuh, serta kesehatan harian.' ?>
                            </p>
                        </div>

                        <div class="btn-wrapper" style="margin-top:25px;">
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" class="theme-btn-1 btn btn-effect-1 text-uppercase">
                                <?= current_lang() === 'en' ? 'Explore Products' : 'Lihat Produk' ?>
                            </a>

                            <a href="<?= $base_url . lang_url('en/about', 'tentang-kami') ?>" class="btn btn-transparent btn-effect-3">
                                <?= current_lang() === 'en' ? 'About Us' : 'Pelajari Lebih Lanjut' ?>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- BANNER AREA START -->
<div class="ltn__banner-area mt-120 mb-90">
    <div class="container">
        <div class="row ltn__custom-gutter--- justify-content-center">
            <div class="col-lg-6 col-md-6">
                <div class="ltn__banner-item">
                    <div class="ltn__banner-img">
                        <a href="#"><img src="<?= $base_url ?>img/banner/coconut-charcoal.webp" alt="Banner Image" width="2400" height="1200" style="width:100%; height:auto;"></a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 col-md-6">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="ltn__banner-item">
                            <div class="ltn__banner-img">
                                <a href="#"><img src="<?= $base_url ?>img/banner/cocopeat.webp" alt="Banner Image" width="1200" height="600" style="width:100%; height:auto;"></a>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-12">
                        <div class="ltn__banner-item">
                            <div class="ltn__banner-img">
                                <a href="#"><img src="<?= $base_url ?>img/banner/cocoboard.webp" alt="Banner Image" width="1200" height="600" style="width:100%; height:auto;"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- BANNER AREA END -->

<!-- FEATURE AREA START ( Feature - 3) -->
<div class="ltn__feature-area mt-100 mt--65 d-none">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="ltn__feature-item-box-wrap ltn__feature-item-box-wrap-2 ltn__border section-bg-6">
                    <div class="ltn__feature-item ltn__feature-item-8">
                        <div class="ltn__feature-icon">
                            <img src="<?= $base_url ?>img/icons/svg/8-trolley.svg" alt="#">
                        </div>
                        <div class="ltn__feature-info">
                            <h4>Free shipping</h4>
                            <p>On all orders over $49.00</p>
                        </div>
                    </div>
                    <div class="ltn__feature-item ltn__feature-item-8">
                        <div class="ltn__feature-icon">
                            <img src="<?= $base_url ?>img/icons/svg/9-money.svg" alt="#">
                        </div>
                        <div class="ltn__feature-info">
                            <h4>15 days returns</h4>
                            <p>Moneyback guarantee</p>
                        </div>
                    </div>
                    <div class="ltn__feature-item ltn__feature-item-8">
                        <div class="ltn__feature-icon">
                            <img src="<?= $base_url ?>img/icons/svg/10-credit-card.svg" alt="#">
                        </div>
                        <div class="ltn__feature-info">
                            <h4>Secure checkout</h4>
                            <p>Protected by Paypal</p>
                        </div>
                    </div>
                    <div class="ltn__feature-item ltn__feature-item-8">
                        <div class="ltn__feature-icon">
                            <img src="<?= $base_url ?>img/icons/svg/11-gift-card.svg" alt="#">
                        </div>
                        <div class="ltn__feature-info">
                            <h4>Offer & gift here</h4>
                            <p>On all orders over</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FEATURE AREA END -->

<!-- ABOUT US AREA START -->
<div class="ltn__about-us-area pt-120 pb-120 d-none">
    <div class="container">
        <div class="row">
            <div class="col-lg-6 align-self-center">
                <div class="about-us-img-wrap about-img-left">
                    <img src="<?= $base_url ?>img/others/6.webp" alt="About Us Image">
                </div>
            </div>
            <div class="col-lg-6 align-self-center">
                <div class="about-us-info-wrap">
                    <div class="section-title-area ltn__section-title-2">
                        <h6 class="section-subtitle ltn__secondary-color">Know More About Shop</h6>
                        <h1 class="section-title">Trusted Organic <br class="d-none d-md-block">  Food  Store</h1>
                        <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore</p>
                    </div>
                    <p>sellers who aspire to be good, do good, and spread goodness. We
                        democratic, self-sustaining, two-sided marketplace which thrives
                    on trust and is built on community and quality content.</p>
                    <div class="about-author-info d-flex">
                        <div class="author-name-designation  align-self-center mr-30">
                            <h4 class="mb-0">Jerry Henson</h4>
                            <small>/ Shop Director</small>
                        </div>
                        <div class="author-sign  align-self-center">
                            <img src="<?= $base_url ?>img/icons/icon-img/author-sign.png" alt="#">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- ABOUT US AREA END -->

<!-- CATEGORY AREA START -->
<div class="ltn__category-area section-bg-1-- ltn__primary-bg before-bg-1 bg-image bg-overlay-theme-black-5--0 pt-115 pb-90" data-bg="<?= $base_url ?>img/bg/coconut-garden-background.webp">
    
    <div class="container">

        <!-- TITLE -->
        <div class="row">
            <div class="col-lg-12">
                <div class="section-title-area ltn__section-title-2 text-center">
                    <h1 class="section-title white-color">
                        <?= current_lang() === 'en' ? 'Product Categories' : 'Kategori Produk' ?>
                    </h1>
                </div>
            </div>
        </div>

        <!-- ITEMS -->
        <div class="row ltn__category-slider-active slick-arrow-1">

            <!-- ITEM 1 -->
            <div class="col-12">
                <div class="ltn__category-item ltn__category-item-3 text-center">
                    <div class="ltn__category-item-img">
                        <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Coconut Industrial Products' : 'Lihat Produk Industri Kelapa' ?>">
                            <img src="<?= $base_url ?>img/icons/icon-img/coconut-industrial-products.webp" alt="">
                        </a>
                    </div>
                    <div class="ltn__category-item-name">
                        <h2>
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Coconut Industrial Products' : 'Lihat Produk Industri Kelapa' ?>">
                                <?= current_lang() === 'en' ? 'Coconut Industrial Products' : 'Produk Industri Kelapa' ?>
                            </a>
                        </h2>
                    </div>
                </div>
            </div>

            <!-- ITEM 2 -->
            <div class="col-12">
                <div class="ltn__category-item ltn__category-item-3 text-center">
                    <div class="ltn__category-item-img">
                        <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Coconut Derivatives' : 'Lihat Turunan Kelapa' ?>">
                            <img src="<?= $base_url ?>img/icons/icon-img/coconut-derivatives.webp" alt="">
                        </a>
                    </div>
                    <div class="ltn__category-item-name">
                        <h2>
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Coconut Derivatives' : 'Lihat Turunan Kelapa' ?>">
                                <?= current_lang() === 'en' ? 'Coconut Derivatives' : 'Turunan Kelapa' ?>
                            </a>
                        </h2>
                    </div>
                </div>
            </div>

            <!-- ITEM 3 -->
            <div class="col-12">
                <div class="ltn__category-item ltn__category-item-3 text-center">
                    <div class="ltn__category-item-img">
                        <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Coconut Ingredients' : 'Lihat Bahan Baku Kelapa' ?>">
                            <img src="<?= $base_url ?>img/icons/icon-img/coconut-ingredients.webp" alt="">
                        </a>
                    </div>
                    <div class="ltn__category-item-name">
                        <h2>
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Coconut Ingredients' : 'Lihat Bahan Baku Kelapa' ?>">
                                <?= current_lang() === 'en' ? 'Coconut Ingredients' : 'Bahan Baku Kelapa' ?>
                            </a>
                        </h2>
                    </div>
                </div>
            </div>

            <!-- ITEM 4 -->
            <div class="col-12">
                <div class="ltn__category-item ltn__category-item-3 text-center">
                    <div class="ltn__category-item-img">
                        <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Fresh Coconut' : 'Lihat Kelapa Segar' ?>">
                            <img src="<?= $base_url ?>img/icons/icon-img/fresh-coconut.webp" alt="">
                        </a>
                    </div>
                    <div class="ltn__category-item-name">
                        <h2>
                            <a href="<?= $base_url . lang_url('en/product', 'produk') ?>" aria-label="<?= current_lang() === 'en' ? 'Explore Fresh Coconut' : 'Lihat Kelapa Segar' ?>">
                                <?= current_lang() === 'en' ? 'Fresh Coconut' : 'Kelapa Segar' ?>
                            </a>
                        </h2>
                    </div>
                </div>
            </div>

        </div>

    </div>
</div>
<!-- CATEGORY AREA END -->
<?php
include __DIR__ . '/../section_product.php';
include __DIR__ . '/../section_service.php';
?>
<!-- COUNTER UP AREA START -->
<div class="ltn__counterup-area bg-image bg-overlay-theme-black-80 pt-115 pb-70" data-bg="<?= $base_url ?>img/bg/coconut-garden-background.webp">
    
    <div class="container">
        <div class="row">

            <!-- ITEM 1 -->
            <div class="col-md-3 col-sm-6 align-self-center">
                <div class="ltn__counterup-item-3 text-color-white text-center">
                    <div class="counter-icon">
                        <img src="<?= $base_url ?>img/icons/icon-img/2.webp" alt="#">
                    </div>
                    <h1>
                        <span class="counter">100</span><span class="counterUp-icon">+</span>
                    </h1>
                    <h2>
                        <?= current_lang() === 'en' ? 'Satisfied Clients' : 'Klien Puas' ?>
                    </h2>
                </div>
            </div>

            <!-- ITEM 2 -->
            <div class="col-md-3 col-sm-6 align-self-center">
                <div class="ltn__counterup-item-3 text-color-white text-center">
                    <div class="counter-icon">
                        <img src="<?= $base_url ?>img/icons/icon-img/3.webp" alt="#">
                    </div>
                    <h1>
                        <span class="counter">15</span><span class="counterUp-letter">K</span><span class="counterUp-icon">+</span>
                    </h1>
                    <h2>
                        <?= current_lang() === 'en' ? 'Monthly Capacity' : 'Kapasitas Bulanan' ?>
                    </h2>
                </div>
            </div>

            <!-- ITEM 3 -->
            <div class="col-md-3 col-sm-6 align-self-center">
                <div class="ltn__counterup-item-3 text-color-white text-center">
                    <div class="counter-icon">
                        <img src="<?= $base_url ?>img/icons/icon-img/4.webp" alt="#">
                    </div>
                    <h1>
                        <span class="counter">50</span><span class="counterUp-icon">+</span>
                    </h1>
                    <h2>
                        <?= current_lang() === 'en' ? 'Coconut Products' : 'Produk Kelapa' ?>
                    </h2>
                </div>
            </div>

            <!-- ITEM 4 -->
            <div class="col-md-3 col-sm-6 align-self-center">
                <div class="ltn__counterup-item-3 text-color-white text-center">
                    <div class="counter-icon">
                        <img src="<?= $base_url ?>img/icons/icon-img/5.webp" alt="#">
                    </div>
                    <h1>
                        <span class="counter">21</span><span class="counterUp-icon">+</span>
                    </h1>
                    <h2>
                        <?= current_lang() === 'en' ? 'Global Markets' : 'Pasar Global' ?>
                    </h2>
                </div>
            </div>

        </div>
    </div>

</div>
<!-- COUNTER UP AREA END -->
<?php
include __DIR__ . '/../section_new_product.php';
include 'includes/call-to-action.php';
include __DIR__ . '/../section_new_article.php';
include __DIR__ . '/../section_choose.php';
?>

<!-- CALL TO ACTION END -->

<!-- BLOG AREA START (blog-3) -->

    <!-- BLOG AREA END -->